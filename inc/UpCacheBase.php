<?php

namespace Upio\UpCache;

require UP_CACHE_LIBS_PATH . '/loader.php';

use Upio\UpCache\Enums\AssetFileName;
use Upio\UpCache\Enums\LifecycleType;
use Upio\UpCache\Enums\AssetExtension;
use Upio\UpCache\Helpers\Validators;
use Upio\UpCache\Helpers;
use MatthiasMullie\Minify;

class UpCacheBase {
	private static array $scripts = array();
	private static array $styles = array();
	private static string $ruleName;
	private array $pluginOptions = array();
	private Helpers\Gzip $gzipHelper;

	public function __construct() {
		if ( Helpers\Gzip::isGzipEnabled() ) {
			$this->gzipHelper = new Helpers\Gzip();
		}
		$this->clearUnusedWPImports();
	}

	/**
	 * @return array
	 */
	protected function getPluginOptions(): array {
		if ( ! $this->pluginOptions ) {
			$this->pluginOptions = is_multisite()
				? get_blog_option( get_current_blog_id(), 'up_cache_options', array() )
				: get_option( 'up_cache_options', array() );
		}

		return $this->pluginOptions;
	}

	/**
	 * @param $option
	 *
	 * @return string|null|false
	 */
	public function getPluginOption( $option ): ?string {
		if ( ! array_key_exists( $option, $options = $this->getPluginOptions() ) ) {
			return false;
		}

		return $options[ $option ];
	}

	/**
	 * @return array
	 */
	protected static function getScripts(): array {
		return self::$scripts;
	}

	/**
	 * @param array $scripts
	 */
	protected static function setScripts( array $scripts ): void {
		if ( ! Validators::validateRule( $scripts ) ) {
			return;
		}

		$scripts       = apply_filters( 'up_cache_set_js', self::setResourceByType( $scripts, self::getScripts() ), $scripts );
		self::$scripts = $scripts;
	}

	/**
	 * @return array
	 */
	protected static function getStyles(): array {
		return self::$styles;
	}

	/**
	 * @param array $styles
	 */
	protected static function setStyles( array $styles ): void {
		if ( ! Validators::validateRule( $styles ) ) {
			return;
		}

		$styles       = self::setResourceByType( $styles, self::$styles );
		self::$styles = $styles;
	}

	/**
	 * @param $sources
	 * @param $type
	 *
	 * @return array
	 */
	protected static function getResourcesByType( $sources, $type ): array {
		if ( ! array_key_exists( $type, $sources ) ) {
			return array();
		}

		return $sources[ $type ];
	}

	/**
	 * @return string
	 */
	protected static function getRuleName(): string {
		return self::$ruleName;
	}

	/**
	 * @param string $ruleName
	 */
	protected static function setRuleName( string $ruleName ): void {
		self::$ruleName = $ruleName;
	}

	/**
	 * Set assets resources by type classification
	 *
	 * @param $resources
	 * @param $extension
	 *
	 * @return array
	 */
	private static function setResourceByType( $resources, $extension ): array {
		if ( empty( $resources ) ) {
			return $extension;
		}

		if ( empty( $extension ) ) {
			return $resources;
		}

		foreach ( $resources as $type => $items ) {

			if ( ! array_key_exists( $type, $extension ) || empty( $extension[ $type ] ) ) {
				$extension[ $type ] = array();
			}
			$extension[ $type ] = array_merge( $extension[ $type ], $items );
		}

		return $extension;
	}

	/**
	 * Return cache directory path, if dir doesn't exist then we create it and return full local path
	 *
	 * @param $post_slug
	 *
	 * @return string|null
	 */
	protected function getCacheDirectory(): ?string {
		$uploads_dir = wp_get_upload_dir();
		$cache_dir   = $uploads_dir['basedir'] . '/up-cache/';

		if ( ! is_dir( $cache_dir ) ) {
			// todo cache error
			mkdir( $cache_dir );
		}

		return $cache_dir;
	}

	/**
	 * @param $post_slug
	 * @param $assets_path
	 *
	 * @return string|null
	 */
	protected function getPostCacheDirectory( $post_slug, $assets_path ): ?string {
		$page_path = $this->getCacheDirectory() . $assets_path . $post_slug;
		if ( ! file_exists( $page_path ) ) {
			mkdir( $page_path, 0777, true );
		}

		return $page_path;
	}

	/**
	 * @return string|null
	 */
	protected static function getCacheDirectoryUri(): ?string {
		global $post;
		$uploads_dir = wp_get_upload_dir();

		return $uploads_dir['baseurl'] . '/up-cache/' . self::getAssetsPath() . $post->post_name;
	}

	/**
	 * @return void
	 */
	private function redeclareStyles(): void {
		global $wp_styles;
		$styles = self::redeclareResources( $wp_styles, self::getStyles() );
		self::setStyles( array( LifecycleType::Require => $styles ) );
	}

	/**
	 * @return void
	 */
	private function redeclareScripts(): void {
		global $wp_scripts;
		$scripts = self::redeclareResources( $wp_scripts, self::getScripts() );
		self::setScripts( array( LifecycleType::Require => $scripts ) );
	}

	/**
	 * @param $wp_resource
	 * @param $sources
	 *
	 * @return array
	 */
	private static function redeclareResources( $wp_resource, $sources ): array {
		$removed   = self::getResourcesByType( $sources, LifecycleType::Remove );
		$resources = array();
		foreach ( $wp_resource->queue as $key ) {
			if ( empty( $wp_resource->registered[ $key ]->src ) ) {
				continue;
			}

			if ( ! Validators::validateSourceOrigin( $wp_resource->registered[ $key ]->src ) ) {
				continue;
			}

			if ( in_array( $key, $removed ) ) {
				continue;
			}
			$resources[ $key ] = $wp_resource->registered[ $key ]->src;
		}

		return $resources;
	}

	/**
	 * @return string
	 */
	private static function getAssetsPath(): string {
		$assets_path = '';
		$query_obj   = get_queried_object();
		// set cache directory for post
		if ( is_page() ) {
			$assets_path = 'page/';
		} elseif ( is_tax() || is_category() ) {
			$assets_path = 'tax/' . $query_obj->taxonomy . '/';
		} elseif ( is_single() ) {
			$assets_path = 'post/' . $query_obj->post_type . '/';
		}

		return $assets_path;
	}

	/**
	 * @return string|null
	 */
	private function getPath(): ?string {
		global $post;
		$dir = Helpers\Beautifiers::transform( $post->post_name );

		return $this->getPostCacheDirectory( $dir, self::getAssetsPath() );
	}

	/**
	 * @param $path
	 *
	 * @return bool
	 */
	public static function isPageCached( $path ): bool {
		if ( file_exists( $path . '/' . AssetFileName::Style )
		     && file_exists( $path . '/' . AssetFileName::Script ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Please have in mind that first is executed wp_enqueue_scripts hook where does get only scripts & styles
	 * that are enqueued based on Wordpress recommended method @ref https://developer.wordpress.org/reference/hooks/wp_enqueue_scripts/.
	 * But some plugins are enqueueing their assets on wp_head & wp_footer hooks that are executed after wp_enqueue_scripts.
	 * This results to not get all the assets during caching and page to break.
	 * By default, we hook on WordPress recommended wp_enqueue_scripts.
	 * For a more Aggressive enqueue the set wp_footer enqueue to true and all assets will be cached.
	 * @todo check performance of wp_footer against wp_enqueue_scripts .
	 */
	private function enqueuedHook() {
		return apply_filters( 'upio_uc_enqueued_hook', 'wp_enqueue_scripts' );
	}

	/**
	 * @return void
	 * @todo check more filters wp_footer, wp_header
	 */
	public function startCaching(): void {
		add_action( $this->enqueuedHook(), array( $this, 'runCaching' ), PHP_INT_MAX );
	}

	/**
	 * @return void
	 */
	public function runCaching(): void {
		// set assets path
		$path = $this->getPath();
		// get all caching rules
		$this->runCacheRules();
		// redeclare assets by rules
		$this->redeclareStyles();
		$this->redeclareScripts();
		// check if needed to proceed into caching process
		if ( ! self::isPageCached( $path ) ) {
			// minify the resources that need to
			self::minify( $path );
			// make resources to gzip
			$this->gzip( $path );
		}
		// dequeue all resources
		self::dequeue();
		// enqueue as one again
		self::enqueue();
	}

	/**
	 * @param $path
	 *
	 * @return void
	 */
	private static function minify( $path ): void {
		self::minifySources( self::getStyles(), $path, new Minify\CSS(), AssetFileName::Style );
		self::minifySources( self::getScripts(), $path, new Minify\JS(), AssetFileName::Script );
	}

	/**
	 * @param $path
	 */
	private function gzip( $path ): void {
		if ( ! Helpers\Gzip::isGzipEnabled() ) {
			return;
		}
		$this->gzipSources( $path, AssetFileName::Style );
		$this->gzipSources( $path, AssetFileName::Script );
	}

	/**
	 * @return void
	 */
	private static function dequeue(): void {
		self::dequeueResources( self::getStyles(), AssetExtension::CSS );
		self::dequeueResources( self::getScripts(), AssetExtension::JS );
	}

	/**
	 * @return void
	 */
	private static function enqueue(): void {

		if ( Helpers\Gzip::isGzipEnabled() ) {
			global $compress_scripts, $concatenate_scripts;
			$compress_scripts    = 1;
			$concatenate_scripts = 1;
			define( 'ENFORCE_GZIP', true );
		}

		wp_enqueue_style( 'up-cache-styles', self::getCacheDirectoryUri() . '/' . AssetFileName::Style );
		wp_enqueue_script( 'up-cache-scripts', self::getCacheDirectoryUri() . '/' . AssetFileName::Script,
			array( 'jquery' ), null, false );
		wp_enqueue_script( 'up-cache-footer-scripts', self::getCacheDirectoryUri() . '/f-' . AssetFileName::Script,
			array( 'jquery' ), null, true );
	}

	/**
	 * @param $sources
	 * @param $path
	 * @param $minifier
	 * @param $name
	 *
	 * @return void
	 */
	private static function minifySources( $sources, $path, $minifier, $name ): void {
		$required = self::getResourcesByType( $sources, LifecycleType::Require );
		$ignored  = self::getResourcesByType( $sources, LifecycleType::Ignore );
		$footer = array();
		foreach ( $required as $key => $src ) {
			if ( in_array( $key, $ignored ) ) {
				continue;
			}
			if ( $name == AssetFileName::Script ) {
				if ( self::isFooterScript( $key ) ) {
					array_push( $footer, $src );
					continue;
				}
			}
			$minifier->add( str_replace( get_site_url() . '/', ABSPATH, $src ) );
		}

		// unify and minify footer scripts on a separate file.
		if ( !empty( $footer ) ) {
			$f_minifier = new Minify\JS();
			foreach ($footer as $foo) {

				$f_minifier->add( str_replace( get_site_url() . '/', ABSPATH, $foo ) );
			}
			$res_path = $path . '/f-' . $name;
			$f_minifier->minify( $res_path );
		}

		$res_path = $path . '/' . $name;
		$minifier->minify( $res_path );
	}

	/**
	 * @param $script
	 *
	 * @return bool
	 */
	private static function isFooterScript( $script ): bool {
		global $wp_scripts;
		$footer = $wp_scripts->do_footer_items();
		$header = $wp_scripts->do_head_items();
		if ( in_array( $script, $footer ) && ! in_array( $script, $header ) ) {
			return true;
		}

		return false;
	}

	/**
	 * @param $path
	 * @param $name
	 *
	 * @return false|string
	 */
	private function gzipSources( $path, $name ) {
		$res_path = $path . '/' . $name;

		return $this->gzipHelper->gzCompressFile( $res_path );
	}

	/**
	 * @param $sources
	 * @param $source_type
	 *
	 * @areturn void
	 */
	private static function dequeueResources( $sources, $source_type ): void {
		$ignored  = self::getResourcesByType( $sources, LifecycleType::Ignore );
		$required = self::getResourcesByType( $sources, LifecycleType::Require );
		foreach ( $required as $key => $value ) {
			if ( in_array( $key, $ignored ) ) {
				continue;
			}
			if ( $source_type == AssetExtension::CSS ) {
				wp_dequeue_style( $key );
				wp_deregister_style( $key );
			} else {
				wp_dequeue_script( $key );
				wp_deregister_script( $key );
			}
		}
	}

	/**
	 * @return void
	 */
	private function runCacheRules(): void {
		$rules = array_filter( get_declared_classes(), function ( $className ) {
			return in_array( 'Upio\UpCache\Rules\IUpCacheRules', class_implements( $className ) );
		} );

		foreach ( $rules as $key => $rule ) {
			try {
				$class = new $rule();
				if ( ! $class ) {
					continue;
				}
				$class->setCss();
				$class->setJs();
				$class->setName();
			} catch ( \Exception $exception ) {
				continue;
			}
		}
	}

	/**
	 * Remove all unwanted wp imports
	 * This idea is get from FASTESTCACHE plugin during reading their source code (couldn't find it on github).
	 */
	private function clearUnusedWPImports() {
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
		remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
		remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
		remove_action( 'admin_print_styles', 'print_emoji_styles' );
		remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
		add_filter( 'emoji_svg_url', '__return_false' );
	}
}
