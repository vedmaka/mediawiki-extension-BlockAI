<?php

namespace MediaWiki\Extension\BlockAI\Evals;

use MediaWiki\Request\WebRequest;
use MediaWiki\Title\Title;
use MediaWiki\User\User;

/**
 * Pretty dump eval that verified that the order of the query parameters is native
 */
class QueryParamsOrder implements IEval {

	/**
	 * This is the expected order of the query parameters. The parameters are
	 * ordered by their expected appearance in the URL. The missing parameters
	 * are not considered when a request query is being processed.
	 */

	// NOTE: this was empirically collected and if you notice something has broken
	// after you installed the extension, this must be the cause of it ><
	private array $queryOrderTemplate = [
		'type',
		'user',
		'target',
		'namespace',
		'search',
		'hidebots',
		'days',
		'title',
		'printable',
		'url',
		'page',
		'diff',
		'oldid',
		'action',
		'veaction',
	];

	public function __construct() {
	}

	public final function evaluate( WebRequest $request, Title $title, User $user ): bool {
		if ( !$request->getRawQueryString() ) {
			return true;
		}
		return $this->verifyKeyOrder(
			$request->getQueryValues(),
			$this->queryOrderTemplate
		);
	}

	private function verifyKeyOrder( array $data, array $expectedOrder ): bool {
		// Map expected keys to their order index for O(1) lookup
		$orderMap = array_flip( $expectedOrder );

		$lastIndex = -1;
		foreach ( $data as $key => $_ ) {
			// Skip keys not in the expected list
			if ( !isset( $orderMap[$key] ) ) {
				continue;
			}

			$index = $orderMap[$key];
			if ( $index < $lastIndex ) {
				// Order broken
				return false;
			}

			$lastIndex = $index;
		}

		return true;
	}

	public final function name() : string {
		return self::class;
	}

	public final function description() : string {
		return 'Evaluates query string parameters order against the expected order';
	}

	public final function weight() : float {
		return 1;
	}
}
