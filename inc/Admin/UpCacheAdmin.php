<?php

namespace Upio\UpCache\Admin;

class UpCacheAdmin
{
    private $upcache_options;

    public function __construct()
    {
        add_action('admin_menu', array($this, 'upCacheAddPluginPage'));
        add_action('admin_init', array($this, 'upCachePageInit'));
        add_action('admin_bar_menu', array($this, 'clearCacheButton'), 999);
        add_action('admin_post_clear_cache', array($this, 'clearCache'));
    }

    public function upCacheAddPluginPage()
    {
        add_management_page(
            'UpCache', // page_title
            'UpCache', // menu_title
            'manage_options', // capability
            'upcache', // menu_slug
            array($this, 'upCacheCreateAdminPage') // function
        );
    }

    public function upCacheCreateAdminPage()
    {
        $this->upcache_options = get_option('up_cache_options'); ?>

        <div class="wrap">
            <h2>UpCache</h2>
            <p>UpCache Settings</p>
            <?php settings_errors(); ?>

            <form method="post" action="options.php">
                <?php
                settings_fields('upcache_option_group');
                do_settings_sections('up-cache-admin');
                submit_button();
                ?>
            </form>
        </div>
    <?php }

    public function upCachePageInit()
    {
        register_setting(
            'upcache_option_group', // option_group
            'up_cache_options', // option_name
            array($this, 'upCacheSanitize') // sanitize_callback
        );

        add_settings_section(
            'up_cache_setting_section', // id
            'Settings', // title
            array($this, 'upCacheSectionInfo'), // callback
            'up-cache-admin' // page
        );

        add_settings_field(
            'is_active', // id
            'Enable Caching', // title
            array($this, 'isActiveCallback'), // callback
            'up-cache-admin', // page
            'up_cache_setting_section' // section
        );

        add_settings_field(
            'is_perfmatters_active', // id
            'Support Perfmatters Disable Rules', // title
            array($this, 'isPerfmattersActiveCallback'), // callback
            'up-cache-admin', // page
            'up_cache_setting_section' // section
        );

        add_settings_field(
            'ignore_css_files_min', // id
            'Ignore CSS files during minify (one file per line)', // title
            array($this, 'ignoreCssFilesMinCallback'), // callback
            'up-cache-admin', // page
            'up_cache_setting_section' // section
        );

        add_settings_field(
            'ignore_js_files_min', // id
            'Ignore JS files during minify (one file per line)', // title
            array($this, 'ignoreJsFilesMinCallback'), // callback
            'up-cache-admin', // page
            'up_cache_setting_section' // section
        );

        add_settings_field(
            'is_buddyboss_active', // id
            'Enable BuddyBoss theme support (experimental)', // title
            array($this, 'isBuddybossActiveCallback'), // callback
            'up-cache-admin', // page
            'up_cache_setting_section' // section
        );
    }

    public function upCacheSanitize($input)
    {
        $sanitary_values = array();
        if (isset($input['is_active'])) {
            $sanitary_values['is_active'] = $input['is_active'];
        }

        if (isset($input['is_perfmatters_active'])) {
            $sanitary_values['is_perfmatters_active'] = $input['is_perfmatters_active'];
        }

        if (isset($input['ignore_css_files_min'])) {
            $sanitary_values['ignore_css_files_min'] = esc_textarea($input['ignore_css_files_min']);
        }

        if (isset($input['ignore_js_files_min'])) {
            $sanitary_values['ignore_js_files_min'] = esc_textarea($input['ignore_js_files_min']);
        }

        if (isset($input['is_buddyboss_active'])) {
            $sanitary_values['is_buddyboss_active'] = $input['is_buddyboss_active'];
        }

        return $sanitary_values;
    }

    public function upCacheSectionInfo()
    {

    }

    public function isActiveCallback()
    {
        ?>
        <fieldset><?php $checked = (isset($this->upcache_options['is_active']) && $this->upcache_options['is_active'] === '0') ? 'checked' : ''; ?>
            <label for="is_active-0"><input type="radio" name="up_cache_options[is_active]" id="is_active-0"
                                            value="0" <?php echo $checked; ?>> No</label><br>
            <?php $checked = (isset($this->upcache_options['is_active']) && $this->upcache_options['is_active'] === '1') ? 'checked' : ''; ?>
            <label for="is_active-1"><input type="radio" name="up_cache_options[is_active]" id="is_active-1"
                                            value="1" <?php echo $checked; ?>> Yes</label></fieldset> <?php
    }

    public function isPerfmattersActiveCallback()
    {
        ?>
        <fieldset><?php $checked = (isset($this->upcache_options['is_perfmatters_active']) && $this->upcache_options['is_perfmatters_active'] === '0') ? 'checked' : ''; ?>
            <label for="is_perfmatters_active-0"><input type="radio" name="up_cache_options[is_perfmatters_active]"
                                                        id="is_perfmatters_active-0" value="0" <?php echo $checked; ?>>
                No</label><br>
            <?php $checked = (isset($this->upcache_options['is_perfmatters_active']) && $this->upcache_options['is_perfmatters_active'] === '1') ? 'checked' : ''; ?>
            <label for="is_perfmatters_active-1"><input type="radio" name="up_cache_options[is_perfmatters_active]"
                                                        id="is_perfmatters_active-1" value="1" <?php echo $checked; ?>>
                Yes</label></fieldset> <?php
    }

    public function ignoreCssFilesMinCallback()
    {
        printf(
            '<textarea class="large-text" rows="5" name="up_cache_options[ignore_css_files_min]" id="ignore_css_files_min">%s</textarea>',
            isset($this->upcache_options['ignore_css_files_min']) ? esc_attr($this->upcache_options['ignore_css_files_min']) : ''
        );
    }

    public function ignoreJsFilesMinCallback()
    {
        printf(
            '<textarea class="large-text" rows="5" name="up_cache_options[ignore_js_files_min]" id="ignore_js_files_min">%s</textarea>',
            isset($this->upcache_options['ignore_js_files_min']) ? esc_attr($this->upcache_options['ignore_js_files_min']) : ''
        );
    }

    public function isBuddybossActiveCallback()
    {
        ?>
        <fieldset><?php $checked = (isset($this->upcache_options['is_buddyboss_active']) && $this->upcache_options['is_buddyboss_active'] === '0') ? 'checked' : ''; ?>
            <label for="is_buddyboss_active-0"><input type="radio" name="up_cache_options[is_buddyboss_active]"
                                                      id="is_buddyboss_active-0" value="0" <?php echo $checked; ?>>
                No</label><br>
            <?php $checked = (isset($this->upcache_options['is_buddyboss_active']) && $this->upcache_options['is_buddyboss_active'] === '1') ? 'checked' : ''; ?>
            <label for="is_buddyboss_active-1"><input type="radio" name="up_cache_options[is_buddyboss_active]"
                                                      id="is_buddyboss_active-1" value="1" <?php echo $checked; ?>> Yes</label>
        </fieldset> <?php
    }

    public function clearCacheButton($wp_admin_bar)
    {
        $args = array(
            'id' => 'clear-cache',
            'title' => 'Clear Cache',
            'href' => wp_nonce_url(admin_url('admin-post.php?action=clear_cache')),
            'meta' => array(
                'class' => 'clear-cache-button'
            )
        );
        $wp_admin_bar->add_node($args);
    }

    public function clearCache()
    {
        $uploads_directory_path = wp_get_upload_dir();

        if (file_exists($uploads_directory_path['basedir'] . '/up-cache/')) {
            $this->removeLocalResources($uploads_directory_path['basedir'] . '/up-cache/');
        }

        wp_safe_redirect(esc_url_raw(wp_get_referer()));
        exit;
    }

    public function removeLocalResources($dir)
    {
        foreach (glob($dir . '/*') as $file) {
            if (is_dir($file)) {
                $this->removeLocalResources($file);
            } else {
                unlink($file);
            }
        }
        rmdir($dir);
    }
}

/* 
 * Keep this as reference to play with values
 * $upcache_options = get_option( 'up_cache_options' ); // Array of All Options
 * $is_active = $upcache_options['is_active']; // Check if cache is enabled
 * $is_perfmatters_active = $upcache_options['is_perfmatters_active']; // Support Perfmatters Disable Rules
 * $ignore_css_files_min = $upcache_options['ignore_css_files_min']; // Ignore CSS files during minify (one file per line)
 * $ignore_js_files_min = $upcache_options['ignore_js_files_min']; // Ignore JS files during minify (one file per line)
 * $is_buddyboss_active = $upcache_options['is_buddyboss_active']; // Enable BuddyBoss theme support (experimental)
 */
