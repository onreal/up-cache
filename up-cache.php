<?php
/*
Plugin Name: UpCache
Description: UpCache is an aggressive assets caching with developers in mind. You can create your own advanced caching rules by using the interface, or add simple ignore rules from Tools->UpCache
Version: 1.0.0
Author: UPIO
Licence: GPLv2 or later
Text Domain: upio-up-cache
Author URI: https://upio.gr/
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
const UP_CACHE_RULES_PATH = UP_CACHE_PATH . 'rules';
const UP_CACHE_INC_PATH   = UP_CACHE_PATH . 'inc';
const UP_ADMIN_ADMIN_PATH = UP_CACHE_PATH . 'admin';

require UP_ADMIN_ADMIN_PATH . '/UpCacheAdmin.php';
require UP_CACHE_INC_PATH . '/controllers/Endpoints.php';
require UP_CACHE_PATH . 'UpCacheBase.php';

use Upio\UpCache\Admin\UpCacheAdmin;
use Upio\UpCache\Inc\Controllers\Endpoints;

class UpCache {
	// TODO
	// 1 Get all styles
	// 2 Perfmatter support, exclude everything that is inside of perfmatter (css and js)
	// 3 Buddyboss theme support
	// 4 dequeue all scripts and styles
	// 5 check file versions before proceed
	// 6 minify the allowed ones
	// 7 enqueue a single minified where contains everything
	// 8 set clear cache button
	// 9 create an option page with basic info only
	// Plugin options,
	// only logged out users ?
	// enable disable cache option
	// add perfmatter support
	// excluded scripts and styles
	// check CDN options
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
