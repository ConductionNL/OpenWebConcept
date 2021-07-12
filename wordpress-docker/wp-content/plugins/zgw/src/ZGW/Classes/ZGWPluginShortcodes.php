<?php

namespace OWC\ZGW\Classes;

use OWC\ZGW\Foundation\Plugin;

class ZGWPluginShortcodes
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
        add_shortcode('zgw-casetable', [$this, 'zgw_casetable_shortcode']);
    }



    /**
     * Callback for shortcode [gw-casetable].
     *
     * @return string
     */
    public function zgw_casetable_shortcode(): string
    {
        $caseList = $this->getCases();
        $cases = $caseList['results'];

       // var_dump($cases);

        // Lets create a table
        $caseRows = [];
        foreach($cases as $case){
            $caseRows[] = "<tr>
                  <td>".$case['identificatie']."</td>
                  <!--<td>".$case['status']."</td>>-->
                  <!--<td>".$case['omschrijving']."</td>>-->
                  <td>".$case['zaaktype']."</td>
                  <td>".$case['startdatum']."</td>
                  <td>".$case['einddatum']."</td>
                </tr";
        }

        return "<table>
                  <thead>
                    <tr>
                      <th>Identificatie</th>
                      <!--<th>Status</th>-->
                      <!--<th>Omschrijving</th>>-->
                      <th>Zaaktype</th>
                      <th>Startdatum</th>
                      <th>Einddatum</th>
                    </tr>
                  </thead>
                  <tbody>
                    ".implode("\n",$caseRows)."
                  </tbody>
                  <tfoot>
                    <tr>
                      <th>Identificatie</th>
                      <!--<th>Status</th>-->
                      <!--<th>Omschrijving</th>>-->
                      <th>Zaaktype</th>
                      <th>Startdatum</th>
                      <th>Einddatum</th>
                    </tr>
                  </tfoot>
                </table>";
    }


    /**
     * Create the JWT token for ZGW
     *
     * @return string
     */
    public function createJWT()
    {
        $clientId = get_option('zgw_api_client_id', '');
        $clientSecret = get_option('zgw_api_client_secret', '');

        if (empty($clientId) || empty($clientSecret)) {
            return;
        }

        // Set the user reprecentation
        if($current_user = wp_get_current_user()){
            $userId = esc_html( $current_user->ID );
            $userRepresentation =  esc_html( $current_user->display_name );
        }
        else{
            $userId = '';
            $userRepresentation = '';
        }


        // Create token header as a JSON string
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256', 'client_identifier' => $clientId]);

        // Create token payload as a JSON string
        $payload = json_encode(['iss' => $clientId, 'client_id' => $clientId, 'user_id' => $userId, 'user_representation' => $userRepresentation, 'iat' => time()]);

        // Encode Header to Base64Url String
        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));

        // Encode Payload to Base64Url String
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));

        // Create Signature Hash
        $signature = hash_hmac('sha256', $base64UrlHeader.'.'.$base64UrlPayload, $clientSecret, true);

        // Encode Signature to Base64Url String
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        // Return JWT
        return $base64UrlHeader.'.'.$base64UrlPayload.'.'.$base64UrlSignature;

    }

    /**
     * Get the cases
     *
     * @return array
     */
    public function getCases()
    {
        $zakenUrl      = get_option('zgw_api_zaken_url', '');
        $catalogusUrl     = get_option('zgw_api_catalogus_url', '');
        $key = $this->createJWT();

        if (empty($zakenUrl) || empty($catalogusUrl)) {
            return;
        }

        // unset any existing session.
        unset($_SESSION['certificate']);

        $data = wp_remote_post($zakenUrl.'/zaken', [
            'headers'     => [
                'Content-Type' => 'application/json; charset=utf-8',
                'Accept-Crs'  => 'EPSG:4326',
                'Content-Crs' => 'EPSG:4326',
                'Authorization' => 'Bearer '.$key],
            'body'        => json_encode($data),
            'method'      => 'GET',
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

        // Lets check for errors
        if(!array_key_exists('status', $decodedBody) || !preg_match("/20[\d]/g", $decodedBody['status'])){
            //var_dump($decodedBody);
        }


        return $decodedBody;
    }

}
