<?php

/**
 * Based very heavily on the code at
 * https://github.com/hypothesis/publisher-account-test-site/blob/master/hypothesis.py
 */
class HypothesisClient {

	function __construct( $client_id, $client_secret, $jwt_client_id,
			 $jwt_client_secret, $authority, $service) {
		$this->client_id = $client_id;
		$this->client_secret = $client_secret;
		$this->jwt_client_id = $jwt_client_id;
		$this->jwt_client_secret = $jwt_client_secret;
		$this->authority = $authority;
		$this->service = $service;
	}

	function extract_domain( $url ) {
		return parse_url( $url, PHP_URL_HOST );
	}

	/**
	 * Create an account on the Hypothesis service.
	 * This creates an account on the Hypothesis service, in the publisher's
	 * namespace, with the given `username` and `email`.
	 */
	function create_account( $username, $email, $display_name = null ) {
		$data = array(
			'authority' => $this->authority,
			'username' => $username,
			'email' => $email,
			'display_name' => $display_name
		);
		return $this->make_request( 'POST', 'users', $data );
	}

	function update_account( $username, $email = null, $display_name = null ) {
		$data = array();
		if ( !is_null( $email ) ) {
			$data['email'] = $email;
		}
		if ( ! is_null( $display_name ) ) {
			$data['display_name'] = $display_name;
		}
		return $this->make_request( 'PATCH', 'users/' . $username, $data );
	}

	function make_request( $method, $path, $data ) {
		$authInfo = array( $this->client_id, $this->client_secret );
		$contextOptions = array(
			'http' => array(
				'method' => $method,
				'header' => 'Content-type: application/x-www-form-urlencoded',
				'data' => http_build_query( $data ),
				'auth' => http_build_query( $authInfo )
			)
		);
		// Don't verify SSL certificates if posting to localhost.
		$domain = $this->extract_domain( $this->service );
		if ( $domain != 'localhost' ) {
			$contextOptions['ssl'] = array( 'verify_peer' => true );
		}
		
		$url = $this->service . '?' . $path;
		$data = file_get_contents( $url, false, stream_context_create( $contextOptions ) );
		return $data;
	}

	/**
	 * Create a grant token for the given `user`.
	 * This creates a grant token which can be passed to the Hypothesis client
	 * in order to enable it to view and create annotations as the given
	 * `username` within the publisher's accounts.
	 */
	function grant_token( $username ) {
		$now = time();
		$in5Minutes = strtotime( '+5 minutes', $now );
		$claims = array(
			'aud' => $this->extract_domain( $this->service ),
			'iss' => $this->jwt_client_id,
			'sub' => 'acct:' . $username . '@' . $this->authority,
			'nbf' => $now,
			'exp' => $in5Minutes
		);
		return HypothesisJWT::encode( $claims, $this->jwt_client_secret, $algorithm = 'HS256' );
	}

}
