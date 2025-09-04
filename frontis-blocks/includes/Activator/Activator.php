<?php
namespace FrontisBlocks\Activator;

use FrontisBlocks\Traits\Singleton;
use FrontisBlocks\Admin\Ajax\Settings;

/**
 * Class Activator
 *
 * @package FrontisBlocks\Activator
 */
class Activator {

    const VERSION = '1.0.9';
    const SLUG = 'frontis-blocks';

    use Singleton;

    public function __construct() {
        $this->appsero_init_tracker_frontis_blocks();
        $this->init_hooks();
    }

    public function init_hooks() {
        add_action('plugins_loaded', [$this, 'fb_set_initial_version']);
        add_action('upgrader_process_complete', [$this, 'fb_on_update'], 10, 2);
        add_action('admin_init', [$this, 'handle_activation_redirect'], 999);
        register_activation_hook(FB_PLUGIN_FILE, [$this, 'set_activation_redirect']);
    }

    /**
	 * Set flag to trigger redirect on activation
	 */
	public function set_activation_redirect()
	{
		add_option('frontis_blocks_do_activation_redirect', true);
		$this->fb_on_activate_or_update();
	}

    /**
	 * Handle plugin activation and update
	 */
	public function fb_on_activate_or_update()
	{
		update_option('frontis_blocks_version', self::VERSION);
		Settings::get_instance()->generate_assets_on_activation_update();
	}

    /**
	 * Set the version if it was never saved before
	 */
	public function fb_set_initial_version()
	{
		if (get_option('frontis_blocks_version') === false) {
			update_option('frontis_blocks_version', self::VERSION);
		}
	}

	/**
	 * Handle plugin update via WordPress plugin updater
	 */
	public function fb_on_update($upgrader_object, $options)
	{
		if (
			isset($options['type']) && $options['type'] === 'plugin' &&
			in_array($options['action'], ['update', 'install'], true)
		) {
			// Try both plugin list or single plugin
			$plugin_slug = defined('FB_PLUGIN_BASENAME') ? FB_PLUGIN_BASENAME : 'frontis-blocks/frontis-blocks.php';

			$is_target_plugin = false;

			// Bulk update path (update action)
			if (!empty($options['plugins']) && is_array($options['plugins'])) {
				$is_target_plugin = in_array($plugin_slug, $options['plugins'], true);
			}

			// Upload path (install action)
			if (!$is_target_plugin && !empty($options['plugin'])) {
				$is_target_plugin = $options['plugin'] === $plugin_slug;
			}

			if ($is_target_plugin) {
				$this->fb_on_activate_or_update();
			}
		}
	}

    /**
	 * Handle redirect to settings page after activation
	 */
	public function handle_activation_redirect()
	{
		if (
			get_option('frontis_blocks_do_activation_redirect', false)
			&& current_user_can('manage_options')
			&& !isset($_GET['activate-multi'])
		) {
			delete_option('frontis_blocks_do_activation_redirect');

			wp_safe_redirect(admin_url('admin.php?page=frontis-blocks'));
			exit;
		}
	}

    /**
	 * Initialize the plugin tracker
	 *
	 * @return void
	 */
	public function appsero_init_tracker_frontis_blocks()
	{
		$client = new \Appsero\Client(
			'27f84c83-a5a3-4a41-9cef-48b83ca6c546',
			'Frontis Blocks – The Ultimate WordPress Block Plugin',
			FB_PLUGIN_FILE
		);

		// Active insights
		$client->insights()->init();
	}
}
