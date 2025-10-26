<?php

namespace MediaWiki\Extension\BlockAI\Evals;

use MediaWiki\Request\WebRequest;
use MediaWiki\Title\Title;
use MediaWiki\User\User;

/**
 * Pretty dump eval that verified that the order of the query parameters is native
 */
class ExpensiveActions implements IEval {

	private array $allowedActions = [
		'view',
		'info'
	];

	public function __construct() {
	}

	public final function evaluate( WebRequest $request, Title $title, User $user ): bool {
		$qs = $request->getVal( 'action' );
		if ( !$qs ) {
			return true;
		}
		return in_array( $qs, $this->allowedActions );
	}

	public final function name() : string {
		return self::class;
	}

	public final function description() : string {
		return 'Locks non essential specials pages';
	}

	public final function weight() : float {
		return 1;
	}
}
