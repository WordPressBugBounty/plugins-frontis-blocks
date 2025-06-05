<?php

namespace FrontisBlocks;

use FrontisBlocks\Admin\Admin;
use FrontisBlocks\Core\Blocks;
use FrontisBlocks\Utils\Enqueue;
use FrontisBlocks\Core\UsedBlocks;
use FrontisBlocks\Config\BlockList;
use FrontisBlocks\Admin\Ajax\Settings;
use FrontisBlocks\Admin\Ajax\GlobalSettings;
use FrontisBlocks\Assets\GenerateFrontendCss;
use FrontisBlocks\Core\FontLoader;
use FrontisBlocks\Core\InitialActivatedBlocks;
use FrontisBlocks\Core\SupportSVG;
use FrontisBlocks\Utils\Helper;
use FrontisBlocks\Utils\UpdateNotifier;

final class Plugin
{
	/**
	 * Plugin version
	 *
	 * @var string
	 */
	const VERSION = '1.0.8';

	/**
	 * Plugin slug
	 *
	 * @var string
	 */
	const SLUG = 'frontis-blocks';

	/**
	 * Plugin prefix
	 *
	 * @var string
	 */
	const PREFIX = 'fb_';

	/**
	 * Plugin text domain
	 *
	 * @var string
	 */
	const TEXT_DOMAIN = 'frontis-blocks';

	/**
	 * Admin instance
	 *
	 * @var Admin
	 */
	public $admin;

	/**
	 * Asset instance
	 *
	 * @var Asset
	 */
	public $asset;

	/**
	 * Plugin settings
	 *
	 * @var array|null
	 */
	public static $settings = null;

	/**
	 * Loaded blocks
	 *
	 * @var array
	 */
	public static $blocks = [];

	/**
	 * Plugin instance
	 *
	 * @var Plugin|null
	 */
	private static $instance = null;

	/**
	 * Constructor
	 */
	private function __construct()
	{
		$this->define_constants();
		$this->set_locale();
		$this->init_classes();
		$this->init_hooks();

		add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
		add_action('enqueue_block_editor_assets', [$this, 'enqueue_editor_scripts']);
        register_activation_hook(FB_PLUGIN_FILE, [$this, 'set_activation_redirect']);
        add_action('admin_init', [$this, 'handle_activation_redirect'], 999);
		add_action('wp_head', [$this, 'add_preconnect_to_head']);
	}
	
	function add_preconnect_to_head() {

		// Enqueue google fonts
		$google_font_url = get_option('fb_google_fonts_url');
		if ($google_font_url) {
    		echo '<link rel="preconnect" href="https://fonts.googleapis.com">';
    		echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>';
    		echo '<link rel="preload" as="style" href="'.$google_font_url.'" onload="this.onload=null;this.rel=\'stylesheet\'">';
    		echo '<noscript><link rel="stylesheet" href="'.$google_font_url.'"></noscript>';
		}
	}

	public function enqueue_header_footer() {
		$upload_dir = wp_upload_dir();

		// Header
		$frontend_dir = $upload_dir['basedir'] . '/frontis-blocks/header/';
		$frontend_url = $upload_dir['baseurl'] . '/frontis-blocks/header/';

		// Css file
		$css_file_system_path = $frontend_dir .'header.min.css';
		$css_file_url = $frontend_url .'header.min.css';

		if(file_exists($css_file_system_path)) {	
			$version = filemtime($css_file_system_path);
			wp_enqueue_style('frontis-blocks-frontend-header', $css_file_url, [], $version);
		}

		// Footer
		$footer_frontend_dir = $upload_dir['basedir'] . '/frontis-blocks/footer/';
		$footer_frontend_url = $upload_dir['baseurl'] . '/frontis-blocks/footer/';

		// Css file
		$css_file_system_path = $footer_frontend_dir .'footer.min.css';
		$css_file_url = $footer_frontend_url .'footer.min.css';
		if(file_exists($css_file_system_path)) {	
			$version = filemtime($css_file_system_path);
			wp_enqueue_style('frontis-blocks-frontend-footer', $css_file_url, [], $version);
		}

		$header_js_system_path = $frontend_dir .'header.js';
		$js_file_url = $frontend_url . 'header.js';
		if(file_exists($header_js_system_path)) {	
			$version = filemtime($header_js_system_path);
			wp_enqueue_script('frontis-blocks-frontend-header', $js_file_url, [], time(), true);
		}

		$footer_js_system_path = $frontend_dir .'footer.js';
		$footer_js_file_url = $frontend_url . 'footer.js';
		if(file_exists($footer_js_system_path)) {	
			$version = filemtime($footer_js_system_path);
			wp_enqueue_script('frontis-blocks-frontend-footer', $footer_js_file_url, [], time(), true);
		}
	}

	public function enqueue_scripts()
	{
		$page_name = Helper::get_page_name();
		$upload_dir = wp_upload_dir();

		$frntis_block_used = get_post_meta(get_the_ID(), 'page_frontis_block_used', true);

		if(is_home()) {
			$page_name = 'home';
		}

		if(is_single()) {
			$page_name = 'single';
		}

		if(is_archive()) {
		    $page_name = 'archive';
		}
		
		if(is_search()) {
		    $page_name = 'search';
		}

		// if(is_page()) {
		// 	$page_name = 'page';
		// }
		
		if(is_404()) {
		    $page_name = '404';
		}

		// Check page or post assigned by template
		$assigned_template = get_post_meta(get_the_ID(), '_wp_page_template', true);

		$frontend_dir = $upload_dir['basedir'] . '/frontis-blocks/' . $page_name . '/';
		$frontend_url = $upload_dir['baseurl'] . '/frontis-blocks/' . $page_name . '/';

		// Css file
		$css_file_system_path = $frontend_dir . $page_name .'.min.css';
		$css_file_url = $frontend_url . $page_name .'.min.css';

		// Js file
		$js_file_system_path = $frontend_dir . $page_name .'.js';
		$js_file_url = $frontend_url . $page_name .'.js';

		// Enqueue frontend css and js
		if(file_exists($css_file_system_path)) {
			$version = filemtime($css_file_system_path);
			wp_enqueue_style('frontis-blocks-frontend', $css_file_url, [], $version);
		}
		
		if(file_exists($js_file_system_path)) {
			$version = filemtime($js_file_system_path);
			wp_enqueue_script('frontis-blocks-frontend', $js_file_url, [], $version, true);
		}

		$this->enqueue_header_footer();

		wp_localize_script('frontis-blocks-frontend', 'FrontisBlocksData', [
			'site_url' => site_url()
		]);
		wp_localize_script('frontis-blocks-frontend', 'SocialShareData', [
			'permalink' => get_permalink(),
			'title' => get_the_title(),
			'network_labels' => [
				'facebook' => 'Facebook',
				'twitter' => 'Twitter',
				'linkedin' => 'LinkedIn',
				'whatsapp' => 'WhatsApp',
				'pinterest' => 'Pinterest',
				'reddit' => 'Reddit',
			],
			'share_urls' => [
				'facebook' => 'https://www.facebook.com/sharer/sharer.php?u=',
				'twitter' => 'https://twitter.com/intent/tweet?url=',
				'linkedin' => 'https://www.linkedin.com/sharing/share-offsite/?url=',
				'pinterest' => 'https://pinterest.com/pin/create/button/?url=',
				'reddit' => 'https://reddit.com/submit?url=',
				'whatsapp' => 'https://wa.me/?text=',
			]
		]);
	}

	public function enqueue_editor_scripts()
	{
		wp_enqueue_script('frontis-responsive-css', FB_PLUGIN_URL . 'assets/js/backend/responsive-css.js', [], FB_VERSION, true);
		wp_register_style('frontis-swiper-style', FB_PLUGIN_URL . 'assets/css/backend/swiper/swiper-bundle.min.css', [], FB_VERSION);
		wp_enqueue_script('frontis-swiper-script', FB_PLUGIN_URL . 'assets/js/backend/swiper/swiper-bundle.min.js', [], FB_VERSION, true);
	}

	/**
	 * Get plugin instance
	 *
	 * @return Plugin
	 */
	public static function get_instance()
	{
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Define constants
	 */
	private function define_constants()
	{
		define('FB_VERSION', self::VERSION);
		define('FB_SLUG', self::SLUG);
		define('FB_PREFIX', self::PREFIX);
		define('FB_TEXT_DOMAIN', self::TEXT_DOMAIN);
		define('FB_PLUGIN_PATH', plugin_dir_path(FB_PLUGIN_FILE));
		define('FB_BLOCKS_DIR', FB_PLUGIN_PATH . 'build/blocks/');
		define('FB_PLUGIN_URL', plugin_dir_url(FB_PLUGIN_FILE));
		// define('SCRIPT_DEBUG', true);
		define('FB_PLUGIN_BASENAME', plugin_basename(FB_PLUGIN_FILE));
		define('FB_UPGRADE_PRO_URL', 'https://wpmessiah.com/');
		define('FB_IS_PRO_ACTIVE', class_exists('FrontisBlocks\Pro\Plugin') ? true : false);
	}

	/**
	 * Set locale
	 */
	private function set_locale()
	{
		add_action('plugins_loaded', function () {
			load_plugin_textdomain(self::TEXT_DOMAIN, false, dirname(plugin_basename(FB_PLUGIN_FILE)) . '/languages');
		});
	}

	/**
	 * Initialize classes
	 */
	private function init_classes()
	{
		$this->asset = Enqueue::get_instance(FB_PLUGIN_URL, FB_PLUGIN_PATH, self::VERSION);

		//load blocks
		self::$blocks = BlockList::get_instance()->get_blocks();

		//load blocks
		self::$blocks = BlockList::get_instance()->get_blocks();

		// Load google font
		FontLoader::get_instance();
	}

	/**
	 * Initialize hooks
	 */
	private function init_hooks()
	{
		add_action('plugins_loaded', [$this, 'init_plugin']);
		add_action('plugins_loaded', [$this, 'fb_set_initial_version']);
		add_action('upgrader_process_complete', [$this, 'fb_on_update'], 10, 2);
	}

	/**
	 * Initialize plugin
	 */
	public function init_plugin()
	{
		// Additional plugin initialization if needed

		Blocks::get_instance();

		Admin::get_instance();

		Settings::get_instance()->register_ajax_events();

		InitialActivatedBlocks::blocks();

		GlobalSettings::get_instance()->register_global_settings_events();

		SupportSVG::get_instance();

		UpdateNotifier::register();

		// UsedBlocks::get_instance()->get_used_frontis_blocks();

	}

	/**
     * Set flag to trigger redirect on activation
     */
    public function set_activation_redirect() {
        add_option('frontis_blocks_do_activation_redirect', true);
		$this->insert_fb_global_colors();
		$this->fb_on_activate_or_update();
    }

	/**
	 * Insert fb global colors when plugin is activated
	 */
	public function insert_fb_global_colors() {
		$option_name = 'fb_global_colors';
	
		// Get theme colors from wp_get_global_settings()
		$theme_colors = [];
		$theme_json = wp_get_global_settings();
	
		if ($theme_json && isset($theme_json['color']) && isset($theme_json['color']['palette'])) {
			$theme_json = $theme_json['color']['palette'];
	
			if (isset($theme_json['theme'])) {
				$theme_colors = $theme_json['theme'];
			}
		}
	
		// Slug Mapping
		$slug_mapping = [
			"Base"       => ["name" => "Primary Color", "slug" => "primary-color"],
			"Contrast"   => ["name" => "Secondary Color", "slug" => "secondary-color"],
			"Accent 1"   => ["name" => "Tertiary Color", "slug" => "tertiary-color"],
			"Accent 2"   => ["name" => "Accent Color", "slug" => "accent-color"],
			"Accent 3"   => ["name" => "Text Color", "slug" => "text-color"],
			"Accent 4"   => ["name" => "Link Color", "slug" => "link-color"],
			"Accent 5"   => ["name" => "Link Hover Color", "slug" => "link-hover-color"],
			"Accent 6"   => ["name" => "White Color", "slug" => "white-color"]
		];
	
		// Modify theme colors
		$modified_colors = [];
		foreach ($theme_colors as $color) {
			$name = $color["name"];
			if (isset($slug_mapping[$name])) {
				$modified_colors[] = [
					"name"  => $slug_mapping[$name]["name"],
					"color" => $color["color"],
					"slug"  => $slug_mapping[$name]["slug"]
				];
			}
		}
	
		// Insert or update option in database
		if (get_option($option_name) === false) {
			add_option($option_name, $modified_colors);
		} else {
			update_option($option_name, $modified_colors);
		}
	}



	/**
	 * Handle plugin activation and update
	 */
	public function fb_on_activate_or_update() {
		update_option('frontis_blocks_version', self::VERSION);
		Settings::get_instance()->generate_assets_on_activation_update();
	}

	/**
	 * Set the version if it was never saved before
	 */
	public function fb_set_initial_version() {
		if (get_option('frontis_blocks_version') === false) {
			update_option('frontis_blocks_version', self::VERSION);
		}
	}

	/**
	 * Handle plugin update via WordPress plugin updater
	 */
	public function fb_on_update($upgrader_object, $options) {
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
    public function handle_activation_redirect() { 
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
}
