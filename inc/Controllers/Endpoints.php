<?php

namespace Upio\UpCache\Controllers;

use WP_REST_Response;

/**
 * Endpoints for managing caching, currently supports only clear cache
 * @todo : add endpoints -> set cache options | set json caching rules
 * @author : Margarit Koka (UPIO)
 */
class Endpoints {

	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'endpoints' ) );
	}

	public function endpoints() {
		/**
		 * Authorization : Basic base64(username:password)
		 * Method : GET
		 * URL : {site_url}/wp-json/upio/up-cache/cc
		 */
		register_rest_route( 'upio/up-cache', '/cc', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'clearCache' ),
			'permission_callback' => array( $this, 'permissionsCallback' )
		) );
	}

	public function clearCache(): WP_REST_Response {
		$resp = array( 'message' => 'OK' );

		return new WP_REST_Response( $resp, 200 );
	}

	public function permissionsCallback(): bool {
		return current_user_can( 'manage_options' );
	}
}