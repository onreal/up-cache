Up Cache Aggressive Caching 
==================
Up Cache is an aggressive caching plugin for WordPress build with developers in mind. 
On it's core Up Cache is fully extensible in order to write your own rules by just implementing 
one small interface on your theme or plugin.

Up Cache have excellent performance, where many WordPress Caching plugins 
cannot afford, you just have to follow the wordpress developers 
guidelines when you work on your theme or plugin.

There are some known limitations with some themes and builders, 
but I'm working toward Up Cache rules in order to fix those compatibility issues.

How it works
------------------
From the moment that Up Cache plugin is installed and activated, 
all assets (css, js) are minified, unified to a single file and cached under the 
uploads dir `wp-content/uploads/up-cache/*`. 
Up Cache split scripts for footer & header import, 
and thous it will create two script files if enqueues on footer is found.

### Plugin lifecycle
  - Process rules
  - Minify & Unify assets
  - Gzip assets
  - Dequeue all assets based on rules
  - Enqueue required assets based on rules
  
In order to exclude assets from minification visit the plugin options under menu `Tools->UpCache` .

For advanced rules, well, you can create your own rules on your theme or plugin by just implementing one small interface.

By default, Up Cache have implemented 3 rules:

- Get hooked rules, check below
- Get options rules set by Admin `Tools->UpCache`
- Listen on Perfmatters plugin disabled assets per page rules

**GZIP -** Up Cache tries to understand by itself if your PHP installation supports 
gzip compression, if it is, then gzip is enabled by default. 
You can switch ON/OFF the gzip compression by hooking on filter `upio_uc_gzip_enable`

    add_filter('upio_uc_gzip_enable', '__return_true');

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

Hooked rules
------------------
You can add you own rules by hooking into rules filter, check below:

    $ignore_css = array( 'ignore' => array( 'skeleton', 'skeleton-css' ) );
    add_filter( 'upio_uc_set_css_rules', $ignore_css );

    $ignore_js = array( 'ignore' => array( 'gsap', 'elementor-js' ) );
    add_filter( 'upio_uc_set_js_rules', $ignore_js );

But also you can create your own advanced elegant solution by implementing `IUpCacheRules` interface. 
There are only 3 methods, for more info, check [how our rules are implemented](https://github.com/onreal/up-cache/tree/main/inc/Rules).

Hooks
------------------

#### Slug Transform

Enable text transform for slug, by default this is false and does not transform slug. 
Up Cache use slugs in order to create the filesystem path for the asset's files, 
when slug text is not Unicode ISO/IEC 8859-1 then most probably this will be an issue on unix systems.

Default transform once enabled is for Greek language. 
Example, if your slug is in Greek language _**ονομα-σελιδας**_ then caching will break, 
by activating slug transform, path will become to _**onoma-selidas**_ .

In order to enable transform

    add_filter( 'upio_uc_is_text_transform', '__return_true' );

Set your custom transform

    add_filter( 'upio_uc_text_transform', 'my_text_transform' );
    function my_text_transform ($slug) {
        // do whatever you want with the slug in order to transform into ISO/IEC 8859-1
        return $slug
    }

#### GZIP

Up Cache tries to understand by itself if your PHP installation supports
gzip compression, if it is, then gzip is enabled by default.

You can switch ON/OFF the gzip compression by hooking on filter `upio_uc_gzip_enable`

    add_filter( 'upio_uc_gzip_enable', '__return_true' );

For Apache servers we check against these modules `['mod_deflate', 'mod_gzip']` 
in order to decide if GZIP is enabled, if you use any other module then hook on `upio_uc_gzip_apache_mods`

    add_filter( 'upio_uc_gzip_apache_mods', [ 'your', 'modules' ] );

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
2. Multisite support -> In progress
3. Mobile support
4. CDN support
5. Convert all images to webp
6. Cache HTML page
7. Get global assets from all WordPress hooks, and then decide by rules what to enqueue and what not. Currently, we get global for all assets with `wp_enqueue_script` action hook. -> Completed
8. Separate scripts import and unification & caching into header and footer files -> Completed in v1.1.3

How to contribute ?
------------
If you want to contribute, then you are more than welcome! 
Currently, there is a TODO list with things that need to be done, you can hang out with any item you prefer. 
But what is most in need is this plugin to be tested on as much WordPress installations as possible, 
this will assure a bug free plugin on most environments.

If you found any bug and know the solution, then create a Merge Request and I would love to review and merge.

Support
--------------
It breaks your website ? 
Then most probably your theme is really messed up and assets are not properly declared.
One thing to do is to ignore one by one the scripts that breaks compatibility, 
until you have the needed results. 
Otherwise, you have to work with your theme or plugins 
in order to proper enqueue your assets.

But if you found any bug, please feel free to create an issue, 
I'll do my best to response with a solution.

If you want to see the full potentials of Up Cache,
I'm also available for micro contracts in order to speed up your WordPress installation.
`margarit[@]upio[.]gr`

Version change logs
--------------
### 1.3.0
  - Separate script assets on header and footer
  - Up Cache creates two unify/minified files for the script for header and footer, this increase stability even on builders like elementor, wp-bakery etc...
### 1.2.0
  - Add filter hook for assets caching globals. 
  - Rules management interface optimisation
  - Validate rule types 
  - New rule in order to set rules by filter hook.
### 1.1.0
  - Gzip compression for cached assets if it's supported by the environment
  - Default options on plugin activation
  - Refactor plugin codebase for better naming convention
### 1.0.0
Initial plugin version.
