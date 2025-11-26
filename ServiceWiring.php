<?php

use MediaWiki\Extension\BlockAI\BlockAI;
use MediaWiki\Logger\LoggerFactory;
use MediaWiki\MediaWikiServices;

return [
	'BlockAI' => static function ( MediaWikiServices $services ): BlockAI {
		return new BlockAI(
			$services->getMainConfig(),
			LoggerFactory::getInstance( 'BlockAI' ),
			ExtensionRegistry::getInstance()
		);
	},
];
