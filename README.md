Up Cache Aggressive Caching 
==================
Up Cache is an aggressive caching plugin for WordPress build with developers in mind. 
On it's core Up Cache is fully extensible in order to write your own rules by just implementing 
one small interface on your theme or plugin.

Up Cache have excellent performance, where many WordPress Caching plugins 
cannot afford, you just have to follow the wordpress developers 
guidelines when you work on your theme or plugin.

There are some known limitations with some themes and builders, but I'm working toward Up Cache to fix those issues by itself.
Example:
 - Elementor builder unification support
 - BuddyBoss Theme support. Implemented by ignoring by default BuddyBoss assets, but this is not what we want to. Our goal is to unify and minify everything. Still in experimental mode

How it works
------------------
From the moment that Up Cache plugin is installed and activated, 
all assets (css, js) are minified, unified to a single file and cached under the uploads dir `wp-content/uploads/up-cache/*`. 
Then Up Cache dequeue all WordPress assets and enqueue the minified ones. 

In order to exclude assets from minification visit the plugin options under menu `Tools->UpCache` .

For advanced rules, well, you can create your own rules on your theme or plugin by just implementing one small interface.

By default, Up Cache have implemented 3 rules:

- Get hooked rules, check below
- Get options rules set by Admin `Tools->UpCache`
- Listen on Perfmatters plugin disabled assets per page rules

**GZIP -** Up Cache tries to understand by itself if your PHP installation supports 
gzip compression, if it is, then gzip is enabled by default. 
You can switch ON/OFF the gzip compression by hooking on filter `upio_uc_gzip_enable`

> Please note, this plugin is aggressive,
> this means that you SHOULD NOT activate in on a production environment without testing,
> but in case you did it and everything broke, don't worry, just deactivate the plugin.

How rules work
------------------
There are three type of rules for each asset:
- Ignore
- Remove
- Include

**Ignored rules:** this type assets are ignored during the minification/unification, but enqueue on page load as a separate import

**Removed rules:** those assets are removed from minification/unification, also removed from page load

**Included rules:** those assets are included on minification/unification

How hook rules work
------------------
You can add you own rules by hooking into rules filter, check below:

`$ignore_css = array( 'ignore' => array( 'skeleton', 'skeleton-css' ) );
add_filter( 'upio_uc_set_css_rules', $ignore_css );
`

`$ignore_js = array( 'ignore' => array( 'gsap', 'elementor-js' ) );
add_filter( 'upio_uc_set_js_rules', $ignore_js );
`

But also you can create your own advanced elegant solution by implementing `IUpCacheRules` interface. 
There are only 3 methods, for more info, check how our rules are implemented.

Cache manager controller
------------------
There is also a REST endpoint where you can clean the site cache everytime is requested.

        /**
         * Authorization : Basic base64(username:password)
         * Method : GET
         * URL : {site_url}/wp-json/upio/up-cache/cc
         */

Installation
------------
To install and configure...

1. Download the plugin repository as a zip file.
2. Upload the plugin through your WordPress admin. `Plugins->Add New->Upload`
3. Activate the plugin under the `Plugins` admin menu.
4. Manage caching under `Tools->UpCache`

TODO
------------
1. Test plugin on more builders and WordPress installations -> In progress
2. Separate scripts import and unification & caching into header and footer files -> In progress
3. Mobile support
4. CDN support
5. Get rendered HTML page, and the second time page is loading skip WordPress rendering by providing the cached on.
6. Get global assets from all WordPress hooks, and then decide by rules what to enqueue and what not. Currently, we get global for all assets with `wp_enqueue_script` action hook. -> Completed
7. Convert all page images to webp 

How to contribute ?
------------
If you want to contribute, then you are more than welcome! 
Currently, there is a TODO list with things that need to be done, you can hang out with any item you prefer. 
But what is most in need is this plugin to be tested on as much WordPress installations as possible, 
this will assure a bug free plugin on most environments.

Version change logs
--------------
### 1.0.0
Initial plugin version.
### 1.1.0
Gzip compression for cached assets if it's supported by the environment - Default options on plugin activation - Refactor plugin codebase for better naming convention
### 1.1.1
Rules management interface optimisation, validate rule types  - New rule in order to set rules by filter hook.
### 1.1.2
Add filter hook for assets caching globals.
