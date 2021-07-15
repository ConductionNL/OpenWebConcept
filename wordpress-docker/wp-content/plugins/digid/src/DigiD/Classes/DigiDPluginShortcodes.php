<?php

namespace OWC\DigiD\Classes;

use OWC\DigiD\Foundation\Plugin;
use SimpleXMLElement;

class DigiDPluginShortcodes
{
    /** @var Plugin */
    protected $plugin;

    public function __construct(Plugin $plugin)
    {
        ob_clean();
        ob_start();
        if (!session_id()) {
            session_start();
        }
        $this->plugin = $plugin;
        $this->add_shortcode();
        // Add query param to wp so it is usable
        add_filter('query_vars', [$this, 'add_samlart_var']);
    }

    private function add_shortcode(): void
    {
        add_shortcode('digid-button', [$this, 'digid_button_shortcode']);
        add_shortcode('digid-login', [$this, 'digid_login_shortcode']);
        add_shortcode('digid-return', [$this, 'digid_return_shortcode']);
//        add_shortcode('digid-logout', [$this, 'digid_logout_shortcode']);
    }

    public function add_samlart_var($public_query_vars)
    {
        $public_query_vars[] = 'SAMLArt';
        return $public_query_vars;
    }

    /**
     * Handles post from this Gravity Form that uses the advanced fields Waardepapier Person and Waardepapier Type.
     *
     * @param array $data should contain an array with a person, type and organization value.
     *
     * @return void
     */
    public function getDigiDXML(array $data): void
    {
        $key = get_option('waardepapieren_api_key', '');
        $endpoint = get_option('waardepapieren_api_domain', '') . '/api/v1/waar/certificates';

        if (empty($key) || empty($endpoint)) {
            return;
        }

        // unset any existing session.
        unset($_SESSION['certificate']);

        $data = wp_remote_post($endpoint, [
            'headers' => ['Content-Type' => 'application/json; charset=utf-8', 'Authorization' => $key],
            'body' => json_encode($data),
            'method' => 'POST',
            'data_format' => 'body',
        ]);

        if (is_wp_error($data)) {
            return;
        }

        $responseBody = wp_remote_retrieve_body($data);

        if (is_wp_error($responseBody)) {
            return;
        }

        $decodedBody = json_decode($responseBody, true);

        $_SESSION['certificate'] = $decodedBody;
    }

    function encode($string)
    {
        $string = base64_encode(gzdeflate(utf8_encode($string)));

        $entities = array('%21', '%2A', '%27', '%28', '%29', '%3B', '%3A', '%40', '%26', '%3D', '%2B', '%24', '%2C', '%2F', '%3F', '%25', '%23', '%5B', '%5D');
        $replacements = array('!', '*', "'", "(", ")", ";", ":", "@", "&", "=", "+", "$", ",", "/", "?", "%", "#", "[", "]");
        return str_replace($entities, $replacements, urlencode($string));
    }


    /**
     * Callback for shortcode [digid-button].
     *
     * @param mixed $atts Array with style or classes for button
     * @return string Returns html button that links to digid
     */
    public function digid_button_shortcode($atts): string
    {
        $type = get_option('digid_type', '');
        $url = get_option('digid_domain', 'https://digispoof.demodam.nl'); /*@todo why doesn't this pick the propper value */
        $type = get_option('digid_certificate', '');

        if (isset($_SESSION['username'])) {
            return  $_SESSION['username'];
        }

        //get params from query string
        $query = [
            "RelayState" => "",
            "SigAlg" => "",
            "Signature" => ""
        ];
        if (isset($atts['returnpath'])) {
            $atts['returnpath'] = '/' . $atts['returnpath'];
            $query['SAMLRequest'] = $this->encode($this->getSAMLRequest($atts['returnpath']));
        } else {
            $query['SAMLRequest'] = $this->encode($this->getSAMLRequest());
        }

        // String samlrequest = getQueryParam("SAMLRequest");
        //String relaystate = getQueryParam("RelayState");
        //String sigalg = getQueryParam("SigAlg");
        //String signature = getQueryParam("Signature");

        if ($type == "form") {
            return '<form action="' . $url . '"><input type="submit" value="Login with DigiD"></form>';
        }

        $button = "<button";
        if (isset($atts['class'])) {
            $button .= " class=\"" . $atts['class'] . "\"";
        }
        if (isset($atts['style'])) {
            $button .= " style=\"" . $atts['style'] . "\"";
        }
        $button .= ">";
        $ahref = "<a href=\"" . $url . "?" . http_build_query($query) . "\"";

        if (isset($atts['ahrefstyle'])) {
            $ahref .= " style=\"" . $atts['ahrefstyle'] . "\"";
        }
        $ahref .= " >Inloggen met DigiD</a>";
        $button .= $ahref . "</button>";

        return $button;
    }

    /**
     * Callback for shortcode [digid-return]. This short code handles the return of a user from digid
     *
     * @param array $atts Array with style or classes for button
     * @return string Returns html button that links to digid
     * @throws \Exception
     */
    public function digid_login_shortcode($atts): string
    {
        $digidUrl = get_option('digid_domain', 'https://digispoof.demodam.nl'); /*@todo why doesn't this pick the propper value */
        $haalcentraalUrl = get_option('digid_haalcentraal', 'https://digispoof.demodam.nl'); /*@todo why doesn't this pick the propper value */
        $haalcentraalKey = get_option('digid_haalcentraal_key', 'https://digispoof.demodam.nl'); /*@todo why doesn't this pick the propper value */

        // Get query parameter SAMLArt from user (contains the user id for the saml art token)
        if (!empty(get_query_var('SAMLArt'))) {
            $SAMLArt = get_query_var('SAMLArt');
            $xml = "
<SOAP-ENV:Envelope
    xmlns:SOAP-ENV='http://schemas.xmlsoap.org/soap/envelope/'>
    <SOAP-ENV:Body>
        <samlp:ArtifactResolve
            xmlns:samlp='urn:oasis:names:tc:SAML:2.0:protocol'
            xmlns='urn:oasis:names:tc:SAML:2.0:assertion'
            ID='_6c3a4f8b9c2d' Version='2.0'
            IssueInstant='2004-01-21T19:00:49Z'>
            <Issuer> " . get_bloginfo('url') . " </Issuer>
            <Artifact>
              " . $SAMLArt . "
            </Artifact>
        </samlp:ArtifactResolve>
    </SOAP-ENV:Body>
</SOAP-ENV:Envelope>";

            // Get the artifact from DigiD
            $data = wp_remote_post($digidUrl . '/artifact', [
                'headers' => [
                    'Content-Type' => 'application/xml',
                ],
                'method' => 'POST',
//                    'body' => $xml
                'body' => $xml,
            ]);

            if (is_wp_error($data)) {
                return 'error';
            }

            // Get the BSN from the soap envelope
            $returnedXml = new SimpleXMLElement($data['body']);
            $bsn = explode(':', $returnedXml->children('http://schemas.xmlsoap.org/soap/envelope/')->children('urn:oasis:names:tc:SAML:2.0:protocol')->ArtifactResponse->Response->children('urn:oasis:names:tc:SAML:2.0:assertion')->Subject->NameID)[1];

            // Get the person from haal centraal
            $data = wp_remote_post('https://vrij-brp.demodam.nl/haal-centraal-brp-bevragen/api/v1.3' . '/ingeschrevenpersonen/' . $bsn, [
                'headers' => [
                    'Content-Type' => 'application/json; charset=utf-8',
                    'Accept-Crs' => 'EPSG:4326',
                    'Content-Crs' => 'EPSG:4326',
                    'Authorization' => 'Basic ' . $haalcentraalKey],
                'method' => 'GET',
            ]);
            if (is_wp_error($data)) {
                return 'error';
            }

            // Use the person to login a user with worpress
            $person = json_decode($data['body'], true);
            // However wordpress logins users (mind you you dont have an email)
            $_SESSION['username'] = $person['naam']['aanschrijfwijze'];
            header("Location: " . get_bloginfo('wpurl'));
            exit;
        }
        return 'error';
    }


//    /**
//     * Callback for shortcode [digid-logout]. This short codes handels a logout actoin form digig
//     *
//     * @param array $atts Array with style or classes for button
//     * @return string Returns html button that links to digid
//     */
//    public function digid_logout_shortcode(array $atts): string
//    {
//
//    }


    /**
     * Creates a saml token for a login request.
     *
     * @param string $returnPath Path where to redirect to from digid
     * @return string
     */
    public function getSAMLRequest(string $returnPath = null): string
    {
        $loginPath = get_option('digid_loginpath', 'https://digispoof.demodam.nl'); /*@todo why doesn't this pick the propper value */

        if (isset($returnPath)) {
            return '<?xml version="1.0" encoding="UTF-8"?>
            <samlp:AuthnRequest
            xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
            xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
            ID="_1330416073"
            Version="2.0" 
            IssueInstant="2012-02-28T09:01:13Z" 
            AssertionConsumerServiceIndex="0"
            AssertionConsumerServiceURL="' . get_bloginfo('url') . $returnPath . '" 
            ProviderName="' . get_bloginfo('name') . '">
                <saml:Issuer>' . get_bloginfo('url') . '</saml:Issuer>
                <samlp:RequestedAuthnContext Comparison="minimum">
                    <saml:AuthnContextClassRef>
                        urn:oasis:names:tc:SAML:2.0:ac:classes:PasswordProtectedTransport
                    </saml:AuthnContextClassRef>
                </samlp:RequestedAuthnContext>F
            </samlp:AuthnRequest>';
        } else {
            return '<?xml version="1.0" encoding="UTF-8"?>
            <samlp:AuthnRequest
            xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
            xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
            ID="_1330416073" Version="2.0" IssueInstant="2012-02-28T09:01:13Z" AssertionConsumerServiceIndex="0" AssertionConsumerServiceURL="' . get_bloginfo('url') . '" ProviderName="' . get_bloginfo('name') . '">
                <saml:Issuer>' . get_bloginfo('url') . '</saml:Issuer>
                <samlp:RequestedAuthnContext Comparison="minimum">
                    <saml:AuthnContextClassRef>
                        urn:oasis:names:tc:SAML:2.0:ac:classes:PasswordProtectedTransport
                    </saml:AuthnContextClassRef>
                </samlp:RequestedAuthnContext>
            </samlp:AuthnRequest>';
        }
    }


}
