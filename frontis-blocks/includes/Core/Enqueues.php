<?php

namespace FrontisBlocks\Core;

use FrontisBlocks\Traits\Singleton;
use FrontisBlocks\Utils\Helper;

/**
 * Class Enqueues
 *
 * @package FrontisBlocks\Enqueues
 */
class Enqueues {

    use Singleton;

    /**
	 * Constructor
	 */
	public function __construct() {
		add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
		add_action('enqueue_block_editor_assets', [$this, 'enqueue_editor_scripts']);
		add_action('wp_head', [$this, 'add_preconnect_to_head']);
	}

    public function enqueue_scripts() {
		$page_name = Helper::get_page_name();
		$upload_dir = wp_upload_dir();
		$post = get_post( get_the_ID() );
		$frontis_used = get_post_meta( get_the_ID(), 'frontis_blocks_used', true );

		if (is_home()) {
			$page_name = 'home';
		}

		if (is_single()) {
			$page_name = 'single';
		}

		if (is_archive()) {
			$page_name = 'archive';
		}

		if (is_search()) {
			$page_name = 'search';
		}

		if (is_404()) {
			$page_name = '404';
		}

		if($frontis_used) {
			if(Helper::is_custom_post_type(get_the_ID()) || (get_post_type(get_the_ID()) === 'post' && !in_array($page_name, ['home', 'archive']))) {
				$page_name = $post->post_name;
			}
		} else {
			if(get_the_ID() && (Helper::is_custom_post_type(get_the_ID()) || (get_post_type(get_the_ID()) === 'post' && !in_array($page_name, ['home', 'archive'])))) {
				$assigned_template = get_post_meta(get_the_ID(), '_wp_page_template', true);
				if($assigned_template !== '' && $assigned_template !== 'default') {
					$page_name = $assigned_template;
				}
			}
		}

		$frontend_dir = $upload_dir['basedir'] . '/frontis-blocks/' . $page_name . '/';
		$frontend_url = $upload_dir['baseurl'] . '/frontis-blocks/' . $page_name . '/';

		// Css file
		$css_file_system_path = $frontend_dir . $page_name . '.min.css';
		$css_file_url = $frontend_url . $page_name . '.min.css';

		// Js file
		$js_file_system_path = $frontend_dir . $page_name . '.js';
		$js_file_url = $frontend_url . $page_name . '.js';

		// Enqueue frontend css and js
		if (file_exists($css_file_system_path)) {
			$version = filemtime($css_file_system_path);
			wp_enqueue_style('frontis-blocks-frontend', $css_file_url, [], $version);
		}

		if (file_exists($js_file_system_path)) {
			$version = filemtime($js_file_system_path);
			wp_enqueue_script('frontis-blocks-frontend', $js_file_url, [], $version, true);
		}

		$this->enqueue_header_footer();

        /**
         * Localize script for frontend
         */
        $localize_array = [
            'site_url' => site_url(),
        ];

        wp_localize_script('frontis-blocks-frontend', 'FrontisBlocksData', $localize_array);
        wp_localize_script('frontis-blocks-frontend-header', 'FrontisBlocksData', $localize_array);
        wp_localize_script('frontis-blocks-frontend-footer', 'FrontisBlocksData', $localize_array);

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

		// pagination localize
		$frontis_pagination_localize = [
			'ajaxUrl' => admin_url('admin-ajax.php'),
			'nonce'   => wp_create_nonce('frontis_loop_nonce'),
		];

		wp_localize_script('frontis-blocks-frontend', 'frontisPagination', $frontis_pagination_localize);
		wp_localize_script('frontis-blocks-frontend-header', 'frontisPagination', $frontis_pagination_localize);
		wp_localize_script('frontis-blocks-frontend-footer', 'frontisPagination', $frontis_pagination_localize);

		// Slick CSS
		wp_enqueue_style( 'frontis-slick-style', FB_PLUGIN_URL . 'assets/css/frontend/slick/slick.css', [], FB_VERSION );
		wp_enqueue_style( 'frontis-slick-theme-style', FB_PLUGIN_URL . 'assets/css/frontend/slick/slick-theme.css', [], FB_VERSION );
		
		// Slick JS
		wp_enqueue_script( 'frontis-slick-script', FB_PLUGIN_URL . 'assets/js/frontend/slick/slick.min.js', ['jquery'], FB_VERSION, true );

	}

    public function enqueue_header_footer() {
		$upload_dir = wp_upload_dir();

		// Header
		$frontend_dir = $upload_dir['basedir'] . '/frontis-blocks/header/';
		$frontend_url = $upload_dir['baseurl'] . '/frontis-blocks/header/';

		// Css file
		$css_file_system_path = $frontend_dir . 'header.min.css';
		$css_file_url = $frontend_url . 'header.min.css';

		if (file_exists($css_file_system_path)) {
			$version = filemtime($css_file_system_path);
			wp_enqueue_style('frontis-blocks-frontend-header', $css_file_url, [], $version);
		}

		// Footer
		$footer_frontend_dir = $upload_dir['basedir'] . '/frontis-blocks/footer/';
		$footer_frontend_url = $upload_dir['baseurl'] . '/frontis-blocks/footer/';

		// Css file
		$css_file_system_path = $footer_frontend_dir . 'footer.min.css';
		$css_file_url = $footer_frontend_url . 'footer.min.css';
		if (file_exists($css_file_system_path)) {
			$version = filemtime($css_file_system_path);
			wp_enqueue_style('frontis-blocks-frontend-footer', $css_file_url, [], $version);
		}

		$header_js_system_path = $frontend_dir . 'header.js';
		$js_file_url = $frontend_url . 'header.js';
		if (file_exists($header_js_system_path)) {
			$version = filemtime($header_js_system_path);
			wp_enqueue_script('frontis-blocks-frontend-header', $js_file_url, [], time(), true);
		}

		$footer_js_system_path = $footer_frontend_dir . 'footer.js';
		$footer_js_file_url = $footer_frontend_url . 'footer.js';
		if (file_exists($footer_js_system_path)) {
			$version = filemtime($footer_js_system_path);
			wp_enqueue_script('frontis-blocks-frontend-footer', $footer_js_file_url, [], time(), true);
		}
	}

    public function enqueue_editor_scripts() {
		wp_enqueue_script('frontis-responsive-css', FB_PLUGIN_URL . 'assets/js/backend/responsive-css.js', [], FB_VERSION, true);
		wp_register_style('frontis-swiper-style', FB_PLUGIN_URL . 'assets/css/backend/swiper/swiper-bundle.min.css', [], FB_VERSION);
		wp_enqueue_script('frontis-swiper-script', FB_PLUGIN_URL . 'assets/js/backend/swiper/swiper-bundle.min.js', [], FB_VERSION, true);

		wp_register_style('frontis-slick-style', FB_PLUGIN_URL . 'assets/css/backend/slick/slick.css', [], FB_VERSION);
		wp_register_style('frontis-slick-theme-style', FB_PLUGIN_URL . 'assets/css/backend/slick/slick-theme.css', [], FB_VERSION);
		wp_register_script('frontis-slick-script', FB_PLUGIN_URL . 'assets/js/backend/slick/slick.min.js', [], FB_VERSION, true);

	}

    function add_preconnect_to_head(){
		// Enqueue google fonts
		$google_font_url = get_option('fb_google_fonts_url');
		if ($google_font_url) {
			echo '<link rel="preconnect" href="https://fonts.googleapis.com">';
			echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>';
			echo '<link rel="preload" as="style" href="' . $google_font_url . '" onload="this.onload=null;this.rel=\'stylesheet\'">';
			echo '<noscript><link rel="stylesheet" href="' . $google_font_url . '"></noscript>';
		}
	}
}
