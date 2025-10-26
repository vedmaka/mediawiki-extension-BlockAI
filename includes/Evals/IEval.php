<?php

namespace MediaWiki\Extension\BlockAI\Evals;

use MediaWiki\Request\WebRequest;
use MediaWiki\Title\Title;
use MediaWiki\User\User;

interface IEval {

	public function name(): string;

	public function description(): string;

	public function evaluate( WebRequest $request, Title $title, User $user ): bool;

	/**
	 * Weight of the evaluation in range [0,1]
	 * 1 = halts the request immediately
	 * <1 = reduces the request score, the higher the weight, the less the score
	 */
	public function weight(): float;

}
