<?php
/*
Plugin Name: UpCache Aggressive Caching
Plugin URI: https://github.com/onreal/up-cache
Description: Up Cache is an aggressive caching plugin for WordPress build with developers in mind. On it's core Up Cache is fully extensible in order to write your own rules by just implementing one small interface on your theme or plugin.
Version: 1.1.2
Author: Margarit Koka <UPIO>
Author URI: https://upio.gr/
Update URI: https://github.com/onreal/up-cache
Text Domain: upio-up-cache
License: GPLv2
*/

/*
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

define( 'UP_CACHE_PATH', plugin_dir_path( __FILE__ ) );
const UP_CACHE_LIBS_PATH  = UP_CACHE_PATH . 'libs';
const UP_CACHE_INC_PATH   = UP_CACHE_PATH . 'inc/';
const UP_CACHE_RULES_PATH = UP_CACHE_INC_PATH . 'Rules';
const UP_CACHE_ADMIN_PATH = UP_CACHE_INC_PATH . 'Admin';
const UP_CACHE_CONTROLLERS_PATH = UP_CACHE_INC_PATH . 'Controllers';
const UP_CACHE_TYPES_PATH = UP_CACHE_INC_PATH . 'Types';
const UP_CACHE_GLOBAL_PREFIX = 'up-cache';

include_once UP_CACHE_PATH . 'Autoloader.php';

use Upio\UpCache\Admin\UpCacheAdmin;
use Upio\UpCache\Controllers\Endpoints;

class UpCache {
	private $cacheBase;

	public function enableAdmin() {
		if ( is_admin() ) {
			$upcache = new UpCacheAdmin();
		}
	}

	public function enableCache() {
		$this->cacheBase = new \Upio\UpCache\UpCacheBase();
		$is_active       = $this->cacheBase->getPluginOption( 'is_active' );
		if ( $is_active ) {
			$this->cacheBase->startCaching();
		}
	}

	public function enableEndpoints() {
		$endpoints = new Endpoints();
	}

	public function activate() {
        $options = array(
            'is_active' => '0',
            'is_loggedin_active' => '0',
            'is_perfmatters_active' => '0',
            'ignore_css_files_min' => '',
            'ignore_js_files_min' => '',
            'is_buddyboss_active' => '0',
            'site_status' => array(
                'is_gzip_enabled_on_server' => ''
            ),
            'config' => array(
                'keep_data_after_remove' => '1'
            ) );
        if ( is_multisite() )  {
            update_blog_option( get_current_blod_id(), 'up_cache_rules', array() );
            update_blog_option( get_current_blod_id(), 'up_cache_options', $options );
        } else {
            update_option( 'up_cache_rules', array() );
            update_option( 'up_cache_options', $options );
        }
	}

	public function deactivate() {
	}
}

if ( class_exists( 'UpCache' ) ) {
	// initiate plugin
	$upCache = new UpCache();
	$upCache->enableAdmin();
	$upCache->enableCache();
	$upCache->enableEndpoints();

	// set wp hooks for plugin lifecycle
	register_activation_hook( __FILE__, array( $upCache, 'activate' ) );
	register_deactivation_hook( __FILE__, array( $upCache, 'deactivate' ) );
}
