<?php

namespace Upio\UpCache;

require UP_CACHE_LIBS_PATH . '/loader.php';

use Upio\UpCache\Types\LifecycleTypes;
use Upio\UpCache\Types\ResourceTypes;
use Upio\UpCache\Rules;
use MatthiasMullie\Minify;

class UpCacheBase {
    private static array $scripts = array();
    private static array $styles = array();
    private static string $supportName;
    private array $pluginOptions = array();

    /**
     * @return array
     */
    protected function getPluginOptions(): array {
        if ( ! $this->pluginOptions ) {
            $this->pluginOptions = is_multisite()
                ? get_blog_option( get_current_blog_id(), 'up_cache_options' )
                : get_option( 'up_cache_options' );
        }

        return $this->pluginOptions;
    }

    /**
     * @param $option
     *
     * @return string|null
     */
    public function getPluginOption( $option ): ?string {
        return $this->getPluginOptions()[ $option ];
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
        if ( empty( $scripts ) ) {
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
    public static function setStyles( array $styles ): void {
        if ( empty( $styles ) ) {
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
    protected static function getSupportName(): string {
        return self::$supportName;
    }

    /**
     * @param string $supportName
     */
    protected static function setSupportName( string $supportName ): void {
        self::$supportName = $supportName;
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

            if ( !array_key_exists( $type, $extension ) || empty( $extension[ $type ] ) ) {
                $extension[ $type ] = array();
            }

            array_push( $extension[ $type ], $items );
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
     * @param $src
     * @return bool
     */
    private static function validateSourceOrigin ($src ): bool
    {
        return strpos( $src, get_site_url() ) !== false;
    }

    /**
     * @return void
     */
    private function redeclareStyles(): void {
        global $wp_styles;
        $styles = self::redeclareResources( $wp_styles, self::getStyles() );
        self::setStyles( array( LifecycleTypes::Required => $styles ) );
    }

    /**
     * @return void
     */
    private function redeclareScripts(): void {
        // if ( !self::getScripts() ) { return; }
        global $wp_scripts;
        $scripts = self::redeclareResources( $wp_scripts, self::getScripts() );
        self::setScripts( array( LifecycleTypes::Required => $scripts ) );
    }

    /**
     * @param $wp_resource
     * @param $sources
     *
     * @return array
     */
    private static function redeclareResources( $wp_resource, $sources ): array {
        $removed   = self::getResourcesByType( $sources, LifecycleTypes::Removed );
        $resources = array();
        foreach ( $wp_resource->queue as $key ) {
            if ( empty( $wp_resource->registered[ $key ]->src ) ) {
                continue;
            }

            if ( self::validateSourceOrigin( $wp_resource->registered[ $key ]->src ) ) {
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
    private static function getAssetsPath (): string
    {
        $assets_path = '';
        $query_obj = get_queried_object();
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
    private function getPath (): ?string
    {
        global $post;
        return $this->getPostCacheDirectory( $post->post_name, self::getAssetsPath() );
    }

    /**
     * @return void
     */
    public function startCaching(): void {
        add_action( 'wp_enqueue_scripts', array( $this, 'runCaching' ), 101 );
    }

    /**
     * @return void
     */
    public function runCaching(): void {
        // get all caching rules
        $this->runCacheRules();
        // redeclare assets by rules
        $this->redeclareStyles();
        $this->redeclareScripts();
        // set assets path
        $page_path = $this->getPath();
        // minify the resources that need to
        self::minify( $page_path );
        // dequeue all resources resources
        self::dequeue();
        // enqueue as one again
        self::enqueue();
    }

    /**
     * @param $page_path
     *
     * @return void
     */
    private static function minify( $page_path ): void {
        self::minifySources( self::getStyles(), $page_path, new Minify\CSS(), 'up-cache.css' );
        self::minifySources( self::getScripts(), $page_path, new Minify\JS(), 'up-cache.js' );
    }

    /**
     * @return void
     */
    private static function dequeue(): void {
        self::dequeueResources( self::getStyles(), ResourceTypes::CSS );
        self::dequeueResources( self::getScripts(), ResourceTypes::JS );
    }

    /**
     * @return void
     */
    private static function enqueue(): void {
        wp_enqueue_style( 'up-cache-styles', self::getCacheDirectoryUri() . '/up-cache.css' );
        wp_enqueue_script( 'up-cache-scripts', self::getCacheDirectoryUri() . '/up-cache.js',
            array( 'jquery' ), null, true );
    }

    /**
     * @param $sources
     * @param $page_path
     * @param $minifier
     * @param $name
     * @return void
     */
    private static function minifySources( $sources, $page_path, $minifier, $name ): void {
        $required = self::getResourcesByType( $sources, LifecycleTypes::Required )[0];

        $ignored  = self::getResourcesByType( $sources, LifecycleTypes::Ignored );

        foreach ( $required as $key => $src ) {
            if ( in_array( $key, $ignored ) ) {
                continue;
            }
            $minifier->add( str_replace( get_site_url() . '/', ABSPATH, $src ) );
        }

        $res_path = $page_path . '/' . $name;
        $minifier->minify( $res_path );
    }

    /**
     * @param $sources
     * @param $source_type
     *
     * @areturn void
     */
    private static function dequeueResources( $sources, $source_type ): void {
        $ignored  = self::getResourcesByType( $sources, LifecycleTypes::Ignored );
        $required  = self::getResourcesByType( $sources, LifecycleTypes::Required )[0];
        foreach ( $required as $key => $value ) {
            if ( in_array( $key, $ignored ) ) { continue; }
            if ( $source_type == ResourceTypes::CSS ) {
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
        $rule_options = new Rules\UpCacheOptionsExclude();
        //$rule_perfmatter = new Rules\UpCachePerfmatters();
        $rules = array_filter( get_declared_classes(), function ( $className ) {
            return in_array( 'Upio\UpCache\Rules\IUpCacheRules', class_implements( $className ) );
        } );

        foreach ( $rules as $key => $rule ) {
            $class = new $rule();
            $class->setCss();
            $class->setJs();
            $class->setIntegrationName();
        }
    }
}
