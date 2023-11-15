<?php

class Gragrid_API {

	protected $mautic_username;
	protected $mautic_password;

	public function __construct( $mautic_username = '', $mautic_password = '') {
		$this->mautic_username = $mautic_username;
		$this->mautic_password = $mautic_password;
	}

	/**
	 * Get all Mautic segments.
	 *
	 * @since 1.0.0
	 * @since 2.2.2 Added the page_size parameter to Mautic's maximum.
	 *
	 * @access public
	 * @return array|WP_Error
	 */
	public function get_segments() {
		$response = $this->request(
			'segments',
			array( 'page_size' => 1000 )
		);

		if ( ! $this->is_valid_response( $response, 200 ) ) {
			return $this->set_error( $response );
		}

		return $response;
	}

	/**
	 * Get a Mautic segment.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param int $segment_id Mautic segment ID.
	 * @return array|WP_Error
	 */
	public function get_segment( $segment_id ) {
		$response = $this->request('segments/' . $segment_id);
	
		if (!$this->is_valid_response($response, 200)) {
			return $this->set_error($response);
		}
	
		return $response['body'];
	}

	/**
	 * Get Mautic custom fields.
	 *
	 * @since 2.1.0
	 *
	 * @access public
	 * @return array|WP_Error
	 */
	public function get_custom_fields() {
		$response = $this->request( '/marketing/field_definitions' );

		if ( ! $this->is_valid_response( $response, 200 ) ) {
			return $this->set_error( $response );
		}

		return $response['body'];
	}

	/**
	 * Add new contacts.
	 *
	 * @since 2.0.0
	 *
	 * @access public
	 * @param array $params Request parameters.
	 * @return array|WP_Error
	 */
	public function create_contact( $params ) {
		$response = $this->request( 'contacts/new', $params, 'POST' );

		if ( $this->is_valid_response( $response, 200 ) || $this->is_valid_response( $response, 201 ) ) {
			return $response['body'];
		}

		return $this->set_error( $response );
	}

	/**
	 * Validate the API key
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function valid_key() {
		return ! is_wp_error( $this->get_segments() );
	}

	/**
	 * Process Mautic API request.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @param string $path   Request path.
	 * @param array  $data   Request data.
	 * @param string $method Request method.
	 * @return array
	 */
	private function request( $path = '', $data = array(), $method = 'GET' ) {
		if ( rgblank( $this->mautic_username ) ) {
			return new WP_Error( __METHOD__, esc_html__( 'API key must be defined to process an API request.', 'gragrid' ) );
		}

		$request_url = 'https://hello.forkpoint.com/api/' . $path;

		// Add request URL parameters if needed.
		if ( 'GET' === $method && ! empty( $data ) ) {
			$request_url = add_query_arg( $data, $request_url );
		}

		// Request specification.
		$args = array(
			'method'   => $method,
			'headers'  => array(
				'Accept'        => 'application/json',
				'Authorization' => 'Basic ' . base64_encode($this->mautic_username . ':' . $this->mautic_password),
				'Content-Type'  => 'application/json',
			),
		);

		// Add data to arguments if needed.
		if ( 'GET' !== $method ) {
			$args['body'] = wp_json_encode( $data );
		}

		// Execute request.
		$response = wp_remote_request( $request_url, $args );

		// If request was not successful, return the error.
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$response['body'] = json_decode( $response['body'], true );

		return $response;
	}

	/**
	 * Check if the response is valid.
	 *
	 * @param array $response Request response.
	 * @param int   $code     Expected response code.
	 * @return bool
	 */
	private function is_valid_response( $response, $code ) {
		if ( is_wp_error( $response ) ) {
			return false;
		}

		if ( wp_remote_retrieve_response_code( $response ) !== $code ) {
			return false;
		}

		return true;
	}

	/**
	 * Set an standardized errror
	 *
	 * @since 1.0.0
	 *
	 * @param array $response API response.
	 * @return WP_Error
	 */
	private function set_error( $response ) {
		if ( is_wp_error( $response ) ) {
			return $response;
		} elseif ( isset( $response['body']['errors'][0]['message'] ) ) {
			return new WP_Error( __METHOD__, $response['body']['errors'][0]['message'] );
		} else {
			return new WP_Error( __METHOD__, $response );
		}
	}

	public function add_contact_to_segment( $segmentId, $contact_id) {
		$response = $this->request( 'segments/' . $segmentId . '/contact/' . $contact_id . '/add', array(), 'POST' );

		if ( ! $this->is_valid_response( $response, 200 ) ) {
			return $this->set_error( $response );
		}

		return $response['body'];
	}
}
