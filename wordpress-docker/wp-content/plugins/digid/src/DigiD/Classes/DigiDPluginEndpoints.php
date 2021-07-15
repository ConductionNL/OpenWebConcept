<?php

namespace OWC\DigiD\Classes;

use OWC\DigiD\Foundation\Plugin;
use WP_REST_Controller;

class DigiDPluginEndpoints extends WP_REST_Controller
{
    /** @var Plugin */
    protected $plugin;

    public function __construct(Plugin $plugin)
    {
        $this->plugin = $plugin;
        $this->add_routes();
    }

    private function add_routes(): void
    {
        add_action( 'rest_api_init', function () {
            register_rest_route( 'digid/v1', '/author/(?P<id>\d+)', array(
                'methods' => 'GET',
                'callback' =>  array($this, 'digid_auth'),
            ) );
        } );
    }

//    /**
//     * Register the routes for the objects of the controller.
//     */
//    public function register_routes()
//    {
//        $version = '1';
//        $namespace = 'digid/v' . $version;
//        $base = 'route';
//        register_rest_route($namespace, '/' . $base, array(
//            array(
//                'methods' => WP_REST_Server::READABLE,
//                'callback' => array($this, 'digid_auth'),
//                'permission_callback' => array($this, 'digid_auth'),
//                'args' => array(),
//            ),
//        ));
//    }



    /**
     * Test endpoint for digid!
     *
     * @param array $data Options for the function.
     * @return string|null Post title for the latest,  * or null if none.
     */
    public function digid_auth($data) {
        $posts = get_posts( array(
            'author' => $data['id'],
        ) );

        if ( empty( $posts ) ) {
            return 'data is leeg ?';
        }
        $response = new WP_REST_Response($posts);
        $response->set_status(200);

        return $response;
    }


}
