<?php
namespace BM_Velobasar;

use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;


if ( !defined( 'BM_VELOBASAR_API_TOKEN' ) )
    define( 'BM_VELOBASAR_API_TOKEN', getenv('BM_VELOBASAR_API_TOKEN'));


class Api {

    private $namespace = 'bm/v1';
    private $base = 'velobasar';
    private $db;
    
    function __construct( $db ) {
        $this->db = $db;
        add_action( 'rest_api_init', array($this, 'rest_api_init') );
    }

    function rest_api_init() {
        register_rest_route( $this->namespace, '/' . $this->base, array(
            array(
                'callback' => array( $this, 'set' ),
                'methods' => WP_REST_Server::CREATABLE,
                'args' => array(
                    'accessid' => array( 'required' => true ),
                    'saletext' => array( 'required' => true ),
                )
            )
        ));
        register_rest_route( $this->namespace, '/' . $this->base . '/(?P<accessid>\w+)', array(
            array(
                'callback' => array( $this, 'get' ),
                'methods' => WP_REST_Server::READABLE,
                'args' => array(
                    'accessid' => array( 'required' => true ),
                )
            ),
            array(
                'callback' => array( $this, 'set' ),
                'methods' => WP_REST_Server::EDITABLE,
                'args' => array(
                    'accessid' => array( 'required' => true ),
                    'saletext' => array( 'required' => false ),
                )
            )
        ));
    }

    function validate_token() {
        return get_option( BM_VELOBASAR_API_TOKEN_OPTION, '' ) === $_SERVER['HTTP_BM_VELOBASAR_API_TOKEN'];
    }

    function get( WP_REST_Request $request ) {
        if( ! $this->validate_token() ) {
            return new WP_REST_Response('BM_Velobasar: Unauthorized access', 401);
        }
        $accessid = $request->get_param('accessid');
        return $this->db->get_saletext( $accessid );
    }

    function set(WP_REST_Request $request) {

        if( ! $this->validate_token() ) {
            return new WP_REST_Response('BM_Velobasar: Unauthorized access', 401);
        }
        $accessid = $request->get_param('accessid');
        $saletext = $request->get_param('saletext');
        if( ! $saletext ) { // try JSON body instead
            $saletext = $request->get_json_params();
        }
        if( ! $saletext ) {
            //  will delete sale entry from DB
            return $this->db->delete( $accessid );
        }
        return $this->db->insert_or_update( $accessid, $saletext );
    }
}
