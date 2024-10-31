<?php

/**
 * Plugin Name: Photo Feed
 * Version: 2.2.1
 * Description: A simple and lightweight plugin to display Instagram Feed within website.
 * Author: Photo Feed
 * Author URI: https://photofeed.ga/
 * Plugin URI: https://photofeed.ga/
 * Requires at least: 5.6
 * Requires PHP: 7.1
 * Tested up to: 6.1
 * Text Domain: photo-feed
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

defined('ABSPATH') || die('403');

// Abort if WordPress is upgrading
if (defined('WP_INSTALLING') && WP_INSTALLING) {
    return;
}

// global constants
if (! defined('PHOTOFEED_VER')) {
    define('PHOTOFEED_VER', '2.2.1');
}
if (! defined('PHOTOFEED_PATH')) {
    define('PHOTOFEED_PATH', plugin_dir_path(__FILE__));
}
if (! defined('PHOTOFEED_URL')) {
    define('PHOTOFEED_URL', plugins_url('', __FILE__));
}

// plugin entry class
if (! class_exists('PHOTOFEED')) {
    class PHOTOFEED
    {
        public function __construct()
        {
            include_once (PHOTOFEED_PATH . 'app/load.php');

            if (is_admin()) {
                add_action('admin_menu', [
                    $this,
                    'admin_menu'
                ]);

                add_filter('plugin_action_links', [
                    $this,
                    'plugin_action_links'
                ], 10, 5);

                add_action('admin_enqueue_scripts', [
                    $this,
                    'admin_enqueue_scripts'
                ]);
            }
            include_once (PHOTOFEED_PATH . 'app/front.php');

            register_deactivation_hook(__FILE__, [
                $this,
                'deactivate'
            ]);

            add_action('pf_cron_hook', 'pf_refresh_tokens');
            add_action('widgets_init', function () {
                register_widget('PHOTOFEED_Widget');
            });
        }

        // add assets in plugin panel page(s)
        function admin_enqueue_scripts($hook)
        {
            if ($hook != 'settings_page_photo-feed') {
                return;
            }
            wp_enqueue_style('wp-color-picker');
            wp_enqueue_script('wp-color-picker');

            wp_enqueue_style('photo-feed-admin', PHOTOFEED_URL . '/assets/admin/style.css', [], PHOTOFEED_VER);

            // for preview gallery
            wp_enqueue_style('photo-feed', PHOTOFEED_URL . '/assets/style.css', [], PHOTOFEED_VER);
            wp_enqueue_style('swiper', PHOTOFEED_URL . '/assets/swiper/swiper.min.css', [], PHOTOFEED_VER);
            wp_enqueue_script('swiper', PHOTOFEED_URL . '/assets/swiper/swiper.min.js', [
                'jquery'
            ], PHOTOFEED_VER, true);
        }

        // Dashboard menu
        function admin_menu()
        {
            add_options_page(__('Photo Feed', 'photo-feed'), __('Photo Feed', 'photo-feed'), 'manage_options', 'photo-feed', [
                $this,
                'adminPanel'
            ]);
        }

        // plugin panel page
        function adminPanel()
        {
            require_once (PHOTOFEED_PATH . 'app/panel.php');
        }

        // add settings page link in plugins list
        function plugin_action_links($actions, $plugin_file)
        {
            static $plugin;

            if (! isset($plugin)) {
                $plugin = plugin_basename(__FILE__);
            }

            if (($plugin == $plugin_file) && current_user_can('administrator')) {
                $settings = [
                    'settings' => '<a href="options-general.php?page=photo-feed">' . __('Settings', 'photo-feed') . '</a>'
                ];

                $actions = array_merge($settings, $actions);
            }

            return $actions;
        }

        public function deactivate()
        {
            $timestamp = wp_next_scheduled('pf_cron_hook');
            if ($timestamp) {
                wp_unschedule_event($timestamp, 'pf_cron_hook');
            }
        }
    }

    new PHOTOFEED();
} else {
    add_action('admin_notices', function () {
        global $pagenow;
        if ($pagenow && ($pagenow == 'plugins.php')) {
            echo '<div class="notice notice-error is-dismissible"> 
			<p><strong>' . __('Multiple instances of Photo Feed plugin are active.', 'photo-feed') . '</strong></p>
			<button type="button" class="notice-dismiss">
				<span class="screen-reader-text">' . __('Dismiss this notice.', 'photo-feed') . '</span>
			</button>
		</div>';
        }
    });
}
