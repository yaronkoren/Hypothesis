<?php

/**
 * Functions for encoding JWT ((JWON Web Tokens) data.
 * Based heavily on https://github.com/firebase/php-jwt/blob/master/src/JWT.php :
 */
class HypothesisJWT {

	/**
	 * Encode a string with URL-safe Base64.
	 *
	 * @param string $input The string you want encoded
	 *
	 * @return string The base64 encode of what you passed in
	 */
	public static function urlsafeB64Encode( $input ) {
		return str_replace( '=', '', strtr( base64_encode( $input ), '+/', '-_' ) );
	}

	/**
	 * Converts and signs a PHP object or array into a JWT string.
	 *
	 * @param object|array $payload PHP object or array
	 * @param string $key The secret key. If the algorithm used is asymmetric, this is the private key
	 * @param string$alg The signing algorithm. Supported algorithms are 'HS256', 'HS384', 'HS512' and 'RS256'
	 * @param mixed $keyId
	 * @param array $head An array with header elements to attach
	 *
	 * @return string A signed JWT
	 *
	 * @uses jsonEncode
	 * @uses urlsafeB64Encode
	 */
	public static function encode( $payload, $key, $alg = 'HS256', $keyId = null, $head = null ) {
		$header = array( 'typ' => 'JWT', 'alg' => $alg );
		if ( $keyId !== null ) {
			$header['kid'] = $keyId;
		}
		if ( isset( $head ) && is_array( $head ) ) {
			$header = array_merge( $head, $header );
		}
		$segments = array();
		$segments[] = static::urlsafeB64Encode( json_encode( $header ) );
		$segments[] = static::urlsafeB64Encode( json_encode( $payload ) );
		$signing_input = implode( '.', $segments );
		// Is this the right thing to do?
		if ( $alg == 'HS256' ) {
			$alg = 'sha256';
		}
		$signature = hash_hmac( $alg, $signing_input, $key, true );
		$segments[] = static::urlsafeB64Encode( $signature );
		return implode( '.', $segments );
	}
}
