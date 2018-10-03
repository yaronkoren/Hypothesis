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

}
