<?php

namespace OWC\DigiD\Classes;

use OWC\DigiD\Foundation\Plugin;

class DigiDPluginShortcodes
{
    /** @var Plugin */
    protected $plugin;

    public function __construct(Plugin $plugin)
    {
        $this->plugin = $plugin;
        $this->add_shortcode();
    }

    private function add_shortcode(): void
    {
        add_shortcode('digid-login', [$this, 'digid_login_shortcode']);
        add_shortcode('digid-return', [$this, 'digid_return_shortcode']);
        add_shortcode('digid-logout', [$this, 'digid_logout_shortcode']);
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

    function encode($string) {

        $string = base64_encode(gzdeflate(utf8_encode($string)));

        $entities = array('%21', '%2A', '%27', '%28', '%29', '%3B', '%3A', '%40', '%26', '%3D', '%2B', '%24', '%2C', '%2F', '%3F', '%25', '%23', '%5B', '%5D');
        $replacements = array('!', '*', "'", "(", ")", ";", ":", "@", "&", "=", "+", "$", ",", "/", "?", "%", "#", "[", "]");
        return str_replace($entities, $replacements, urlencode($string));
    }


    /**
     * Callback for shortcode [digid-login].
     *
     * @param array $atts Array with style or classes for button
     * @return string Returns html button that links to digid
     */
    public function digid_login_shortcode(array $atts): string
    {
        $type = get_option('digid_type', '');
        $url = get_option('digid_domain', 'https://digispoof.demodam.nl'); /*@todo why doesn't this pick the propper value */
        $type = get_option('digid_certificate', '');

        //get params from query string
        $query = [
            "SAMLRequest"=> $this->encode($this->getSAMLRequest()),
            "RelayState"=>"",
            "SigAlg"=>"",
            "Signature"=>""
        ];
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
     */
    public function digid_login_shortcode(array $atts): string
    {
        $digidUrl = get_option('digid_domain', 'https://digispoof.demodam.nl'); /*@todo why doesn't this pick the propper value */
        $haalcentraalUrl = get_option('digid_haalcentraal', 'https://digispoof.demodam.nl'); /*@todo why doesn't this pick the propper value */
        $haalcentraalKey = get_option('digid_haalcentraal_key', 'https://digispoof.demodam.nl'); /*@todo why doesn't this pick the propper value */

        // Get query parameter SAMLArt from user (contains the user id for the saml art token)
        $SAMLArt = // howver wordpress passes quer paramters

        // Get the artifact from DigiD
        $data = wp_remote_post($digidUrl . '/artifact', [
            'headers' => [
                'Content-Type' => 'application/json; charset=utf-8',
            'method' => 'GET',
        ]);

        if (is_wp_error($data)) {
            return;
        }

        $artifact = wp_remote_retrieve_body($data);

        // Get the BSN from the soap envelope
        $artifact = new SimpleXMLElement($artifact);
        $bsn = $artifact->path->to->bsn;

        // Get the person from haal centraal
        $data = wp_remote_post($haalcentraalUrl . '/ingesvrevenpersonen/'.$bsn, [
            'headers' => [
                'Content-Type' => 'application/json; charset=utf-8',
                'Accept-Crs' => 'EPSG:4326',
                'Content-Crs' => 'EPSG:4326',
                'Authorization' => 'Bearer ' . $haalcentraalKey],
            'method' => 'GET',
        ]);

        if (is_wp_error($data)) {
            return;
        }

        $person = json_decode(wp_remote_retrieve_body($data));

        // Use the person to login a user with worpress

        // However wordpress logins users (mind you you dont have an email)


    }


    /**
     * Callback for shortcode [digid-logout]. This short codes handels a logout actoin form digig
     *
     * @param array $atts Array with style or classes for button
     * @return string Returns html button that links to digid
     */
    public function digid_logout_shortcode(array $atts): string
    {
    }


    /**
     * Creates a saml token for a login request.
     *
     * @return string
     */
    public function getSAMLRequest(): string
    {
        $loginPath = get_option('digid_loginpath', 'https://digispoof.demodam.nl'); /*@todo why doesn't this pick the propper value */

        return '<?xml version="1.0" encoding="UTF-8"?>
            <samlp:AuthnRequest
            xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
            xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
            ID="_1330416073" Version="2.0" IssueInstant="2012-02-28T09:01:13Z" AssertionConsumerServiceIndex="0" AssertionConsumerServiceURL="'.get_bloginfo('url').'/'.$loginPath.'" ProviderName="'.get_bloginfo('name').'">
                <saml:Issuer>'.get_bloginfo('url').'</saml:Issuer>
                <samlp:RequestedAuthnContext Comparison="minimum">
                    <saml:AuthnContextClassRef>
                        urn:oasis:names:tc:SAML:2.0:ac:classes:PasswordProtectedTransport
                    </saml:AuthnContextClassRef>
                </samlp:RequestedAuthnContext>
            </samlp:AuthnRequest>';
    }


}
