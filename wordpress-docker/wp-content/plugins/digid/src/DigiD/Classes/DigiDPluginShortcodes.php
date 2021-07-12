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
        add_shortcode('digid-inlog', [$this, 'digid_inlog_shortcode']);
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
     * Callback for shortcode [digid-inlog].
     *
     * @return string
     */
    public function digid_inlog_shortcode(): string
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
            return '<form action="' . $url . '"><input type="submit" value="Inloggen met DigiD"></form>';
        }

        return '<button style="margin-right: 15px"><a href="' . $url . "?" . http_build_query($query) . '">Inloggen met DigiD</a></button>';
    }


    /**
     * Callback for shortcode [digid-inlog].
     *
     * @return string
     */
    public function getSAMLRequest(): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>
            <samlp:AuthnRequest
            xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
            xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
            ID="_1330416073" Version="2.0" IssueInstant="2012-02-28T09:01:13Z" AssertionConsumerServiceIndex="0" AssertionConsumerServiceURL="'.get_bloginfo('url').'" ProviderName="'.get_bloginfo('name').'">
                <saml:Issuer>'.get_bloginfo('url').'</saml:Issuer>
                <samlp:RequestedAuthnContext Comparison="minimum">
                    <saml:AuthnContextClassRef>
                        urn:oasis:names:tc:SAML:2.0:ac:classes:PasswordProtectedTransport
                    </saml:AuthnContextClassRef>
                </samlp:RequestedAuthnContext>
            </samlp:AuthnRequest>';
    }


}
