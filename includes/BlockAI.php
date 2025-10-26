<?php

namespace MediaWiki\Extension\BlockAI;

use Config;
use Exception;
use MediaWiki\Extension\BlockAI\Evals\ExpensiveActions;
use MediaWiki\Extension\BlockAI\Evals\ForeignPosts;
use MediaWiki\Extension\BlockAI\Evals\IEval;
use MediaWiki\Extension\BlockAI\Evals\InvalidRequest;
use MediaWiki\Extension\BlockAI\Evals\QueryParamsOrder;
use MediaWiki\Extension\BlockAI\Evals\SpecialPageLock;
use MediaWiki\Request\WebRequest;
use MediaWiki\Title\Title;
use MediaWiki\User\User;
use Psr\Log\LoggerInterface;

class BlockAI {

	private Config $config;
	private LoggerInterface $logger;

	/**
	 * @var IEval[]
	 */
	private array $evals;

	private float $threshold;

	public function __construct( Config $config, LoggerInterface $logger ) {
		$this->config = $config;
		$this->evals = [
			new InvalidRequest(),
			new SpecialPageLock(),
			new ExpensiveActions(),
			new QueryParamsOrder(),
			new ForeignPosts()
		];
		$this->logger = $logger;
		$this->threshold = $this->config->get( 'BlockAIThreshold');
	}

	/**
	 * Determines if a request should be blocked based on the request score
	 */
	public final function shouldBlock( WebRequest $request, Title $title, User $user ): bool {
		$reqip = $this->getReqIpSafe( $request );
		$this->logger->info( "[$reqip] Evaluating request score for {$request->getRequestURL()}" );
		$score = $this->getRequestScore( $request, $title, $user );
		if ( $score < $this->threshold ) {
			$this->logger->info( "[$reqip] Request score is below threshold $score < {$this->threshold}, blocking" );
			return true;
		}
		return false;
	}

	/**
	 * Calculates a request score based on evaluations and thresholds
	 *
	 * @param WebRequest $request The web request being evaluated
	 * @param Title $title The title relevant to the evaluation
	 * @param User $user The user related to the evaluation
	 * @param bool $quick Whether to skip expensive evaluations when score below a threshold
	 *
	 * @return float
	 */
	public final function getRequestScore( WebRequest $request, Title $title, User $user, bool $quick = true ): float {
		$reqip = $this->getReqIpSafe( $request );
		$score = 1;
		foreach ( $this->evals as $eval ) {
			$this->logger->info( "[$reqip] Evaluating {$eval->name()} with weight {$eval->weight()}" );
			$r = $eval->evaluate( $request, $title, $user );
			if ( !$r ) {
				$this->logger->info( "[$reqip] {$eval->name()} returned false, reducing score by applying weight of {$eval->weight()}" );
				$score *= 1 - $eval->weight();
				$this->logger->info( "[$reqip] New score is $score" );
			}
			// if the score is below the threshold, return it immediately to avoid spending time
			// on extra evaluations as we already know that the request is not eligible
			if ( $quick && $score < $this->threshold ) {
				$this->logger->info( "[$reqip] Score is below threshold, quickly returning $score" );
				return $score;
			}
		}
		return $score;
	}

	private function getReqIpSafe( WebRequest $request ): string {
		try {
			return $request->getIP();
		} catch ( Exception $e ) {
			return 'unknown';
		}
	}

	private function getReqHashFast( WebRequest $request ): string {
		$data = implode( '', [
			$this->getReqIpSafe( $request ),
			$request->getRequestURL(),
			$request->getRawQueryString(),
			$request->getHeader( 'Cookie' ),
		] );
		return hash('xxh3', $data);
	}

}
