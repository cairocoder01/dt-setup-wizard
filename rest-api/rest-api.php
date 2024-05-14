<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

class Disciple_Tools_Setup_Wizard_Endpoints
{
    public $permissions = array( 'manage_dt' );

    public function add_api_routes() {
        $namespace = 'disciple-tools-setup-wizard/v1';

        $this->register_route( $namespace, '/user', array( $this, 'user_create' ) );
        $this->register_route( $namespace, '/option', array( $this, 'option_set' ) );
    }

    public function register_route( $namespace, $route, $callback ) {
        register_rest_route(
            $namespace, $route, array(
                'methods'  => WP_REST_Server::CREATABLE,
                'callback' => $callback,
                'permission_callback' => function ( WP_REST_Request $request ) {
                    return $this->has_permission();
                },
            )
        );
    }


    public function user_create( WP_REST_Request $request ) {
        try {
            $user = $request->get_params();

            return Disciple_Tools_Users::create_user(
                $user['username'],
                $user['email'],
                $user['displayName'],
                $user['roles'],
                $user['corresponds_to_contact'] ?? null,
                $user['locale'] ?? null,
                false,
                $user['password'] ?? null,
                $user['optionalFields'] ?? array(),
                false
            );
        } catch ( Exception $ex ) {
            return $ex;
        }
    }

    public function option_set( WP_REST_Request $request ) {
        try {
            $params = $request->get_params();
            $key = sanitize_text_field( wp_unslash( $params['key'] ) );
            $value = $params['value'];
            $overwrite = key_exists( 'overwrite', $params )
                ? filter_var( sanitize_key( wp_unslash( $params['overwrite'] ) ), FILTER_VALIDATE_BOOLEAN )
                : false;

            // For arrays/objects, we need to know if we should merge the new value with the old
            // or overwrite the old value with the new
            if ( is_array( $value ) ) {
                $previous_value = get_option( $key );
                if ( $overwrite || !is_array( $previous_value ) ) {
                    return update_option( $key, $value );
                } else {
                    // default: merge new with the old, with new values taking priority
                    $merged_value = array_replace_recursive( $previous_value, $value );
                    return update_option( $key, $merged_value );
                }
            }
            return update_option( $key, $value );
        } catch ( Exception $ex ) {
            return $ex;
        }
    }

    private static $_instance = null;
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()
    public function __construct() {
        add_action( 'rest_api_init', array( $this, 'add_api_routes' ) );
    }
    public function has_permission(){
        $pass = false;
        foreach ( $this->permissions as $permission ){
            if ( current_user_can( $permission ) ){
                $pass = true;
            }
        }
        return $pass;
    }
}
Disciple_Tools_Setup_Wizard_Endpoints::instance();
