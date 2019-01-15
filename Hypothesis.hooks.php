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
		global $wgHypothesisClientID, $wgHypothesisClientSecret,
			$wgHypothesisJWTClientID, $wgHypothesisJWTClientSecret,
			$wgHypothesisAuthority, $wgHypothesisService;

		if ( !in_array( $wgTitle->getNamespace(), $wgHypothesisNamespaces ) ) {
			return true;
		}

		if ( ! $wgUser->isAllowed( 'hypothesis-annotate' ) ) {
			return true;
		}

		$hypClient = new HypothesisClient(
			$wgHypothesisClientID,
			$wgHypothesisClientSecret,
			$wgHypothesisJWTClientID,
			$wgHypothesisJWTClientSecret,
			$wgHypothesisAuthority,
			$wgHypothesisService
		);
		$username = $wgUser->getName();
		$email = $wgUser->getEmail();

		// Create a Hypothesis account for this user, if one didn't
		// exist already.
		$hypClient->create_account( $username, $email );

		$grantToken = $hypClient->grant_token( $username );
		$vars['hypothesisGrantToken'] = $grantToken;

		return true;
	}

	public static function addSiteModule( OutputPage &$out, ParserOutput $parserOutput ) {
		$out->addModules( 'ext.hypothesis.site' );
		return true;
	}
}
