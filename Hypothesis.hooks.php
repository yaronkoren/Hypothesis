<?php

class HypothesisHooks {

	static function addHypothesisScript( $skin, &$text ) {
		global $wgHypothesisNamespaces;

		if ( !in_array( $skin->getTitle()->getNamespace(), $wgHypothesisNamespaces ) ) {
			return true;
		}

		if ( ! $skin->getUser()->isAllowed( 'hypothesis-annotate' ) ) {
			return true;
		}

		$text .= '<script type="application/json" class="js-hypothesis-config"></script>';
		$text .= '<script src="https://hypothes.is/embed.js" async></script>';
		return true;
	}

	static function setGlobalJSVariables( &$vars ) {
		global $wgTitle, $wgUser, $wgHypothesisNamespaces;

		if ( !in_array( $wgTitle->getNamespace(), $wgHypothesisNamespaces ) ) {
			return true;
		}

		if ( ! $wgUser->isAllowed( 'hypothesis-annotate' ) ) {
			return true;
		}

		$username = $wgUser->getName();
		$hypothesis_service = getenv('HYPOTHESIS_SERVICE');
		if ( $hypothesis_service == null ) {
			$hypothesis_service = 'http://localhost:5000';
		}
		$hypClient = new HypothesisClient(
			$authority = getenv('HYPOTHESIS_AUTHORITY'),
			$client_id = getenv('HYPOTHESIS_CLIENT_ID'),
			$client_secret = getenv('HYPOTHESIS_CLIENT_SECRET'),
			$jwt_client_id = getenv('HYPOTHESIS_JWT_CLIENT_ID'),
			$jwt_client_secret = getenv('HYPOTHESIS_JWT_CLIENT_SECRET'),
			$service = $hypothesis_service
		);
		$grantToken = $hypClient->grant_token( $username );
		$vars['hypothesisGrantToken'] = $grantToken;

		return true;
	}
}
