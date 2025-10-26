<?php

namespace MediaWiki\Extension\BlockAI\Evals;

use MediaWiki\Request\WebRequest;
use MediaWiki\Title\Title;
use MediaWiki\User\User;

/**
 * Pretty dump eval that verified that the order of the query parameters is native
 */
class ForeignPosts implements IEval {

	public function __construct() {
	}

	public final function evaluate( WebRequest $request, Title $title, User $user ): bool {
		if ( $request->getMethod() === 'POST' && !$request->getHeader( 'Referer' ) ) {
			return false;
		}
		return true;
	}

	public final function name() : string {
		return self::class;
	}

	public final function description() : string {
		return 'Drops posts that has no referrer';
	}

	public final function weight() : float {
		return 1;
	}
}
