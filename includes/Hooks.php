<?php

namespace MediaWiki\Extension\BlockAI;

use JetBrains\PhpStorm\NoReturn;
use MediaWiki\Actions\ActionEntryPoint;
use MediaWiki\Hook\BeforeInitializeHook;
use MediaWiki\Output\OutputPage;
use MediaWiki\Request\WebRequest;
use MediaWiki\Title\Title;
use MediaWiki\User\User;

class Hooks implements BeforeInitializeHook {

	private BlockAI $blockAI;

	public function __construct( BlockAI $blockAI ) {
		$this->blockAI = $blockAI;
	}

	/**
	 * This hook is called before anything is initialized in ActionEntryPoint::performRequest().
	 *
	 * @since 1.35
	 *
	 * @param Title $title Title being used for request
	 * @param null $unused
	 * @param OutputPage $output
	 * @param User $user
	 * @param WebRequest $request
	 * @param ActionEntryPoint $mediaWikiEntryPoint
	 * @return bool|void True or no return value to continue or false to abort
	 */
	public function onBeforeInitialize( $title, $unused, $output, $user, $request, $mediaWikiEntryPoint ) {

		// bypass completely for logged-in users
		if ( !$user->isAnon() ) {
			return true;
		}

		// block if necessary
		if ( $this->blockAI->shouldBlock( $request, $title, $user ) ) {
			$this->teapot();
		}

	}

	/**
	 * Sends an HTTP 418 status code indicating that the server is a teapot.
	 *
	 * @return never
	 */
	#[NoReturn]
	private function teapot(): never {
		header( 'HTTP/1.0 418 Forbidden' );
		die( 'I am a teapot' . "\n" );
	}

}
