<?php

namespace Upio\UpCache\Rules;

//require UP_CACHE_INC_PATH . '/types/LifecycleTypes.php';
//require UP_CACHE_PATH . 'UpCacheBase.php';

use Upio\UpCache\Inc\Types\LifecycleTypes;
use Upio\UpCache\UpCacheBase;

class UpCacheOptionsExclude extends UpCacheBase implements IUpCacheRules {
	public static function getType(): string {
		return LifecycleTypes::Ignore;
	}

	public function setCss(): void {
		$excluded = $this->getPluginOption( 'ignore_css_files_min' );
		self::setStyles( array( self::getType() => $excluded ) );
	}

	public function setJs(): void {
		$excluded = $this->getPluginOption( 'ignore_js_files_min' );
		self::setScripts( array( self::getType() => $excluded ) );
	}

	public function setIntegrationName(): void {
		self::setSupportName( 'up_cache_options' );
	}
}