Up Cache Aggressive Caching 
==================

Up Cache is an aggressive caching plugin for WordPress build with developers in mind. 
On it's core Up Cache is fully extensible in order to write your own rules by just implementing 
one small interface on your theme or plugin.

How it works
------------------
From the moment that Up Cache plugin is installed and activated, 
all assets (css, js) are minified, unified to a single file and cached under the uploads dir `wp-content/uploads/up-cache/*`. 
Then Up Cache dequeue all WordPress assets and enqueue the minified ones. 

In order to exclude assets from minification visit the plugin options under menu `Tools->UpCache` .

For advanced rules, well, you can create your own rules on your theme or plugin by just implementing one small interface.

By default, Up Cache have implemented 2 rules:
 
- Get from options the user added ignore assets
- Listen on Perfmatters plugin disabled assets per page rules

**GZIP -** Up Cache tries to understand by itself if your PHP installation supports 
gzip compression, if it is, then gzip is enabled by default. 
You can switch ON/OFF the gzip compression by hooking on filter `upio_uc_gzip_enable`

How rules works
------------------
There are three type of rules for each asset:
- Ignore
- Remove
- Include

**Ignored rules:** this type assets are ignored during the minification/unification, but enqueue on page load as a separate import

**Removed rules:** those assets are removed from minification/unification, also removed from page load

**Included rules:** those assets are included on minification/unification

How rules really works
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

> Please note, this plugin is aggressive, 
> this means that you shouldn't activate in on a production environment without being sure,
> but in case you did it and everything broke, then just deactivate. 

Installation
------------

To install and configure...

1. Download the plugin repository as a zip file.
2. Upload the plugin through your WordPress admin. `Plugins->Add New->Upload`
3. Activate the plugin under the `Plugins` admin menu.
4. Manage caching under `Tools->UpCache`

Version change logs
--------------

### 1.0.0
Initial plugin version, WYSWYG
### 1.1.0
Gzip compression for cached assets if it's supported by the environment - Default options on plugin activation - Refactor plugin codebase for better naming convention
### 1.1.1
Rules management interface optimisation, validate rule types  - New rule in order to set rules by filter hook.
