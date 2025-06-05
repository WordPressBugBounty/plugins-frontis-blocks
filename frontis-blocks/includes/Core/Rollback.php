<?php

namespace Frontis\Blocks\Core;

use FrontisBlocks\Traits\Singleton;

class Rollback
{
    use Singleton;

    /**
     * Plugin slug.
     *
     * @var string
     */
    protected $plugin_slug;

    /**
     * Plugin name.
     *
     * @var string
     */
    protected $plugin_name;

    /**
     * Version to rollback to.
     *
     * @var string
     */
    protected $version;

    /**
     * Package URL.
     *
     * @var string
     */
    protected $package_url;

    /**
     * Initialize the rollback process.
     *
     * @param array $args Rollback arguments.
     */
    public function init($args = array())
    {
        foreach ($args as $key => $value) {
            $this->{$key} = $value;
        }

        $this->package_url = "https://downloads.wordpress.org/plugin/{$this->plugin_slug}.{$this->version}.zip";

        add_action('admin_post_frontis_rollback', array($this, 'perform_rollback'));
    }

    /**
     * Perform the rollback process.
     */
    public function perform_rollback()
    {
        check_admin_referer('frontis_rollback');

        if (!current_user_can('update_plugins')) {
            wp_die(esc_html__('You do not have sufficient permissions to perform this action.', 'frontis-blocks'));
        }

        $this->apply_package();
        $this->upgrade();

        wp_safe_redirect(admin_url('plugins.php'));
        exit;
    }

    /**
     * Apply package.
     *
     * Modify the plugin update data to use our specific version package.
     */
    protected function apply_package()
    {
        $update_plugins = get_site_transient('update_plugins');
        if (!is_object($update_plugins)) {
            $update_plugins = new \stdClass();
        }

        $plugin_info = new \stdClass();
        $plugin_info->new_version = $this->version;
        $plugin_info->slug = $this->plugin_slug;
        $plugin_info->package = $this->package_url;
        $plugin_info->url = 'https://frontisblocks.com/';

        $update_plugins->response[$this->plugin_name] = $plugin_info;

        set_site_transient('update_plugins', $update_plugins);
    }

    /**
     * Upgrade.
     *
     * Run WordPress upgrade to rollback to the previous version.
     */
    protected function upgrade()
    {
        require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

        $upgrader_args = array(
            'url'    => 'update.php?action=upgrade-plugin&plugin=' . rawurlencode($this->plugin_name),
            'plugin' => $this->plugin_name,
            'nonce'  => 'upgrade-plugin_' . $this->plugin_name,
            'title'  => __('Frontis Blocks <p>Rollback to Previous Version</p>', 'frontis-blocks'),
        );

        $this->print_inline_style();

        $upgrader = new \Plugin_Upgrader(new \Plugin_Upgrader_Skin($upgrader_args));
        $upgrader->upgrade($this->plugin_name);
    }

    /**
     * Print inline style.
     *
     * Add inline CSS to the rollback page.
     */
    private function print_inline_style()
    {
        ?>
        <style>
            .wrap {
                overflow: hidden;
                max-width: 850px;
                margin: auto;
                font-family: Courier, monospace;
            }

            h1 {
                background: #0073aa;
                text-align: center;
                color: #fff !important;
                padding: 70px !important;
                text-transform: uppercase;
                letter-spacing: 1px;
            }

            h1 img {
                max-width: 300px;
                display: block;
                margin: auto auto 50px;
            }
        </style>
        <?php
    }

    /**
     * Generate the rollback button HTML.
     *
     * @return string The rollback button HTML.
     */
    public function get_rollback_button()
    {
        $rollback_url = wp_nonce_url(
            add_query_arg(
                array(
                    'action' => 'frontis_rollback',
                ),
                admin_url('admin-post.php')
            ),
            'frontis_rollback'
        );

        return sprintf(
            '<a href="%s" class="button frontis-rollback-button" onclick="return confirm(\'%s\');">%s</a>',
            esc_url($rollback_url),
            esc_js(__('Are you sure you want to rollback to this version? The process is irreversible.', 'frontis-blocks')),
            /* translators: %s is the version number to rollback to */
            sprintf(__('Rollback to v%s', 'frontis-blocks'), $this->version)
        );
    }
}
