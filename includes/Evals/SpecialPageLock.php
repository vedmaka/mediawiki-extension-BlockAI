<?php

namespace MediaWiki\Extension\BlockAI\Evals;

use MediaWiki\Request\WebRequest;
use MediaWiki\Title\Title;
use MediaWiki\User\User;

/**
 * Pretty dump eval that verified that the order of the query parameters is native
 */
class SpecialPageLock implements IEval {

	private array $allowedSpecialPages = [
		'UserLogin',
		'CreateAccount',
		'Search',
		'Random'
	];

	public function __construct() {
	}

	public final function evaluate( WebRequest $request, Title $title, User $user ): bool {
		if ( $title->getNamespace() === NS_SPECIAL ) {
			if ( !$this->in_array_icase( $title->getBaseText(), $this->allowedSpecialPages ) ) {
				return false;
			}
		}
		return true;
	}

	private function in_array_icase( string $needle, array $haystack ): bool {
		$needle = strtolower( $needle );
		foreach ( $haystack as $item ) {
			if ( strtolower( $item ) === $needle ) {
				return true;
			}
		}
		return false;
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
