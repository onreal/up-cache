<?php

namespace Upio\UpCache\Rules;

use Upio\UpCache\Types\LifecycleTypes;
use Upio\UpCache\UpCacheBase;

class UpCacheOptionsExclude extends UpCacheBase implements IUpCacheRules {
	public static function getType(): string {
		return LifecycleTypes::Ignored;
	}

	public function setCss(): void {
		$excluded = $this->getPluginOption( 'ignore_css_files_min' );
		if ( !$excluded || empty( $excluded ) ) {
			return;
		}
		self::setStyles( array( self::getType() => explode( ',', $excluded ) ) );
	}

	public function setJs(): void {
		$excluded = $this->getPluginOption( 'ignore_js_files_min' );
		if ( !$excluded || empty( $excluded ) ) {
			return;
		}
		self::setStyles( array( self::getType() => explode( ',', $excluded ) ) );
	}

	public function setIntegrationName(): void {
		self::setSupportName( 'up_cache_options' );
	}
}