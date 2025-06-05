<?php
namespace FrontisBlocks\Utils;

use FrontisBlocks\Traits\Singleton;

defined('ABSPATH') || exit;

class UpdateNotifier {
    use Singleton;

    private static string $update_key     = 'frontis_blocks_show_update_notice';
    private static string $install_key    = 'frontis_blocks_show_install_notice';
    private static string $redirect_key   = 'frontis_blocks_do_update_redirect';

    /**
     * Register hooks
     */
    public static function register() {
        add_action('upgrader_process_complete', [self::class, 'on_plugin_update'], 10, 2);
        add_action('admin_init', [self::class, 'set_install_notice_flag']);
        add_action('admin_init', [self::class, 'handle_update_redirect'], 999);
        add_action('admin_notices', [self::class, 'frontis_blocks_show_notice']);
        add_action('wp_ajax_fb_dismiss_update_notice', [self::class, 'ajax_dismiss_update_notice']);
    }

    /**
     * Set flag when plugin is updated
     */
    // public static function on_plugin_update($upgrader, $options) {
    //     if (
    //         isset($options['action'], $options['type'], $options['plugins']) &&
    //         $options['action'] === 'update' &&
    //         $options['type'] === 'plugin' &&
    //         in_array(plugin_basename(FRONTIS_BLOCKS_FILE), $options['plugins'], true)
    //     ) {
    //         update_option(self::$update_key, true);
    //         update_option(self::$redirect_key, true);
    //     }
    // }


    /**
     * Set flag when plugin is updated or reinstalled
     */
    public static function on_plugin_update($upgrader, $options) {
        if (
            isset($options['type'], $options['action']) &&
            $options['type'] === 'plugin' &&
            in_array($options['action'], ['update', 'install'], true)
        ) {
            $plugin_slug = defined('FRONTIS_BLOCKS_FILE') ? plugin_basename(FRONTIS_BLOCKS_FILE) : 'frontis-blocks/frontis-blocks.php';

            $is_target_plugin = false;

            // Bulk update path (update action)
            if (!empty($options['plugins']) && is_array($options['plugins'])) {
                $is_target_plugin = in_array($plugin_slug, $options['plugins'], true);
            }

            // Single install/update path (install action)
            if (!$is_target_plugin && !empty($options['plugin'])) {
                $is_target_plugin = $options['plugin'] === $plugin_slug;
            }

            if ($is_target_plugin) {
                update_option(self::$update_key, true);
                update_option(self::$redirect_key, true);
            }
        }
    }


    /**
     * Set install-time notice if plugin is already active
     */
    public static function set_install_notice_flag() {
        if (
            is_admin() &&
            current_user_can('activate_plugins') &&
            is_plugin_active(FB_PLUGIN_BASENAME) &&
            !get_option(self::$update_key) &&
            !get_option(self::$install_key)
        ) {
            update_option(self::$install_key, true);
        }
    }

    /**
     * Redirect to settings tab after plugin update
     */
    public static function handle_update_redirect() {
        if (
            get_option(self::$redirect_key) &&
            current_user_can('manage_options') &&
            !isset($_GET['activate-multi']) &&
            !isset($_GET['page']) // prevent infinite loop
        ) {
            delete_option(self::$redirect_key);

            wp_safe_redirect(admin_url('admin.php?page=frontis-blocks&tab=settings'));
            exit;
        }
    }

    /**
     * Show update/install notice with regeneration instruction
     */
    public static function frontis_blocks_show_notice() {
        if (!current_user_can('manage_options')) {
            return;
        }

        // Only show notice on the settings tab page
        $page = $_GET['page'] ?? '';
        $tab  = $_GET['tab'] ?? '';
        if ($page !== 'frontis-blocks' || $tab !== 'settings') {
            return;
        }

        if (get_option(self::$update_key) || get_option(self::$install_key)) {
            $settings_url = add_query_arg([
                'page' => 'frontis-blocks',
                'tab'  => 'settings'
            ], admin_url('admin.php'));

            echo '<div class="notice notice-warning is-dismissible frontis-update-notice">';
            echo '<p><strong>🚨 Frontis Blocks updated successfully!</strong></p>';
            echo '<p>To make sure everything works properly, you <strong>must regenerate block assets</strong> right now.</p>';
            echo '<p>🚧 <strong>Without this step, your frontend blocks may not display correctly (CSS/JS might not load).</strong></p>';
            echo '<hr />';
            echo '<p><strong>✅ Follow these steps:</strong></p>';
            echo '<ol style="padding-left: 20px; list-style: decimal;">';
            echo '<li>You are now on the <strong>Settings → Assets Generation</strong> tab.</li>';
            echo '<li>Find the <strong>"Regenerate Asset"</strong> button on the right side.</li>';
            echo '<li>Click it once — assets will be regenerated automatically.</li>';
            echo '</ol>';
            echo '<p><a href="' . esc_url($settings_url) . '" class="button button-primary">Go to Asset Generation</a></p>';
            echo '</div>';

            // Add JS to handle dismiss
            echo '<script>
                document.addEventListener("DOMContentLoaded", function() {
                    const notice = document.querySelector(".frontis-update-notice");
                    if(notice) {
                        notice.querySelector(".notice-dismiss")?.addEventListener("click", function() {
                            fetch(ajaxurl, {
                                method: "POST",
                                credentials: "same-origin",
                                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                                body: "action=fb_dismiss_update_notice"
                            });
                        });
                    }
                });
            </script>';
        }
    }

    /**
     * Handle AJAX dismissal
     */
    public static function ajax_dismiss_update_notice() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized', 403);
        }

        delete_option(self::$update_key);
        delete_option(self::$install_key);

        wp_send_json_success('Notice dismissed');
    }
}
