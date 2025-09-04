<?php

namespace FrontisBlocks\Admin\Ajax;

use FrontisBlocks\Traits\Singleton;
use FrontisBlocks\Config\BlockList;
use FrontisBlocks\Assets\AssetsGenerator;
use FrontisBlocks\Utils\Helper;

/**
 * Settings
 *
 * @package FrontisBlocks
 */
class Settings extends AjaxBase {

	use Singleton;

	/**
	 * register_ajax_events
	 *
	 * @return void
	 */
	public function register_ajax_events() {
		$ajax_events = [
			'get_options',
			'default_content_width',
			'container_column_gap',
			'container_row_gap',
			'container_padding',
			'custom_css',
			'automatic_block_recovery',
			'copy_paste_style',
			'file_generation',
			'generate_assets',
			'version_control',
			'enable_quick_action_bar',
			'collapse_panel',
			'enable_templates_button',
			'save_blocks',
			'get_blocks',
			'recaptcha_v2_site_key',
			'recaptcha_v2_secret_key',
			'recaptcha_v3_site_key',
			'recaptcha_v3_secret_key',
			'google_maps_api_key',
			'instagram_access_token',
			'coming_soon_mode',
			'maintenance_mode',
			'coming_soon_page_id',
			'maintenance_page_id',
			'upload_custom_icons',
			'get_custom_icons_category',
			'update_allowed_blocks',
			'get_allowed_blocks'
		];

		$this->init_ajax_events($ajax_events);
	}

	public function get_options() {
		if (!check_ajax_referer('fb_settings_nonce', 'security', false)) {
			wp_send_json_error(['message' => 'Invalid nonce'], 400);
			return;
		}

		if (!current_user_can('manage_options')) {
			wp_send_json_error(['message' => 'Insufficient permissions'], 403);
			return;
		}

		$keys = isset($_POST['keys']) ? $_POST['keys'] : [];
		$options = [];

		foreach ($keys as $key) {
			$options[$key] = get_option("fb_$key");
		}

		wp_send_json_success($options);
	}

	// Individual option handlers
	public function default_content_width() {
		$this->handle_option_update('default_content_width', 'number');
	}

	public function container_column_gap() {
		$this->handle_option_update('container_column_gap', 'number');
	}

	public function container_row_gap() {
		$this->handle_option_update('container_row_gap', 'number');
	}

	public function container_padding() {
		$this->handle_option_update('container_padding', 'number');
	}

	public function custom_css() {
		$this->handle_option_update('custom_css', 'boolean');
	}
    public function automatic_block_recovery() {
        $this->handle_option_update('automatic_block_recovery', 'boolean');
    }

	public function copy_paste_style() {
		$this->handle_option_update('copy_paste_style', 'boolean');
	}

	public function file_generation() {
		if (!check_ajax_referer('fb_settings_nonce', 'security', false)) {
			wp_send_json_error(['message' => 'Invalid nonce'], 400);
			return;
		}

		if (!current_user_can('manage_options')) {
			wp_send_json_error(['message' => 'Insufficient permissions'], 403);
		}
	}

	/**
	 * generate_assets
	 *
	 * @return void
	 */
	public function generate_assets() {
		if (!check_ajax_referer('fb_settings_nonce', 'security', false)) {
			wp_send_json_error(['message' => 'Invalid nonce'], 400);
		}

		if (!current_user_can('manage_options')) {
			wp_send_json_error(['message' => 'Insufficient permissions'], 403);
		}

		// Upload directory path
		$response = $this->generate_assets_for_post();

		if($response) {
			wp_send_json_success(['message' => 'CSS generated successfully']);
		}

		wp_send_json_error(['message' => 'Failed to generate css.'], 500);
	}


	// Get all pages, posts, custom post, etc post ids and get post content
	public function generate_assets_for_post()
	{
		global $wpdb;
		$upload_directory = WP_CONTENT_DIR . '/uploads/frontis-blocks/';

		if (!is_dir($upload_directory)) {
			if (!mkdir($upload_directory, 0755, true)) {
				wp_send_json_error(['message' => 'Failed to create upload directory']);
				return;
			}
		}

		// Generate global assets
		AssetsGenerator::generate_global_assets();

		// Query to get all post IDs
		$query = "SELECT ID, post_content, post_name
          FROM {$wpdb->posts}
          WHERE post_status = 'publish'
          AND post_name != 'wp-global-styles-frontis-theme'
          AND post_type NOT IN ('wp_font_family', 'wp_navigation', 'wp_global_styles', 'wp_font_face', 'attachment', 'mc4wp-form', 'acf-post-type')
          ORDER BY post_date DESC";
		$results = $wpdb->get_results($query);
// 		$query = "SELECT ID, post_content, post_name
//           FROM {$wpdb->posts}
//           WHERE post_status = 'publish'
//           AND post_name != 'wp-global-styles-frontis-theme'
//           AND post_type NOT IN ('wp_font_family', 'wp_navigation', 'wp_global_styles', 'wp_font_face', 'attachment', 'mc4wp-form', 'acf-post-type')
//           AND ID IN (3742)
//           ORDER BY post_date DESC";
// $results = $wpdb->get_results($query);

		$generatedPages = [];
		
		if(!empty($results)) {
			foreach ($results as $post) {
				$modified_post = $post;
				$post_content = $post->post_content;
	
				// Get synced pattern contents
				$pattern_contents = Helper::check_patterns_used($post->post_content);
	
				// Append pattern contents to post_content
				if (!empty($pattern_contents)) {
					foreach ($pattern_contents as $pattern_content) {
						$post_content .= "\n" . $pattern_content; // Append each pattern content
					}
	
					// Pass modified post object to generate_page_assets
					$modified_post->post_content = $post_content; // Update post object
				}
	
				// if($post->post_name == 'home-2-2') {
					$myfile = fopen("assetsgeneration.txt", "w") or die("Unable to open file!");
					$txt = $post->post_content;
					fwrite($myfile, $txt);
					fclose($myfile);
				// }
	
				$pattern = "/frontis-blocks/i";
				update_post_meta($post->ID, 'frontis_blocks_used', false);
	
				if(preg_match_all($pattern, $post->post_content) > 0) {
					update_post_meta($post->ID, 'frontis_blocks_used', true);
				}
	
				AssetsGenerator::get_instance()->generate_page_assets($post->ID, $modified_post);
				$generatedPages[] = $post->post_name;
			}
		}
		
		$upload_directory = WP_CONTENT_DIR . '/uploads/frontis-blocks/';
		$files = glob($upload_directory . "/*");

		if ($files){
			foreach ($files as $file) {
				if (is_dir($file)) { // Check if it's a directory
					if(in_array(basename($file), $generatedPages)){
						return true;
					}
				}
			}
		} else {
			return false;
		}

		return false;
	}

	/**
	 * generate_assets_on_activation_update
	 */
	public function generate_assets_on_activation_update() {
		$response = $this->generate_assets_for_post();

		if ($response) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * delete_folder
	 *
	 * @param string $folder_path
	 * @return void
	 */
	private function delete_folder($folder_path) {
		if (!is_dir($folder_path)) {
			return false;
		}

		$files = array_diff(scandir($folder_path), ['.', '..']);
		foreach ($files as $file) {
			$file_path = $folder_path . DIRECTORY_SEPARATOR . $file;
			if (is_dir($file_path)) {
				$this->delete_folder($file_path); // Recursive call for subfolders
			} else {
				unlink($file_path); // Delete file
			}
		}

		return rmdir($folder_path); // Remove the directory itself
	}

	public function version_control() {
		$this->handle_option_update('version_control', 'text');
	}

	public function enable_quick_action_bar() {
		$this->handle_option_update('enable_quick_action_bar', 'boolean');
	}

	public function collapse_panel() {
		$this->handle_option_update('collapse_panel', 'boolean');
	}

	public function enable_templates_button() {
		$this->handle_option_update('enable_templates_button', 'boolean');
	}

	public function get_allowed_blocks()
	{
		if (!check_ajax_referer('fb_settings_nonce', 'security', false)) {
			wp_send_json_error(['message' => 'Invalid nonce'], 400);
			return;
		}

		if (!current_user_can('manage_options')) {
			wp_send_json_error(['message' => 'Insufficient permissions'], 403);
			return;
		}

		$allowed_blocks = get_option("fb_update_allowed_blocks");

		if($allowed_blocks) {
			$allowed_blocks = json_decode($allowed_blocks);
			wp_send_json_success($allowed_blocks);
		} else {
			wp_send_json_error([
				'message' => 'Allowed blocks not found.',
			]);
		}
	}

	public function update_allowed_blocks()
	{
		if (!check_ajax_referer('fb_settings_nonce', 'security', false)) {
			wp_send_json_error(['message' => 'Invalid nonce'], 400);
			return;
		}

		if (!current_user_can('manage_options')) {
			wp_send_json_error(['message' => 'Insufficient permissions'], 403);
			return;
		}

		$allowed_blocks = isset($_POST['data']) ? ($_POST['data']) : [];

		$value =  json_encode($allowed_blocks);

		$old_value = get_option("fb_update_allowed_blocks");

		$updated = update_option("fb_update_allowed_blocks", $value);

		if ($updated) {
			wp_send_json_success(['message' => 'Option updated successfully']);
		} else {
			$error_message = 'Failed to update option';
			if ($old_value === false) {
				$error_message .= ': Option does not exist';
			} elseif ($old_value === $value) {
				$error_message .= ': Value unchanged';
			} else {
				$error_message .= ': Unknown reason';
			}
			wp_send_json_error([
				'message' => $error_message,
				'old_value' => $old_value,
				'new_value' => $value,
				'option_name' => "fb_update_allowed_blocks"
			]);
		}
	}

	/**
	 * handle_option_update
	 *
	 * @param string $option_name
	 * @param string $type
	 * @return void
	 */
	private function handle_option_update($option_name, $type) {
		if (!check_ajax_referer('fb_settings_nonce', 'security', false)) {
			wp_send_json_error(['message' => 'Invalid nonce'], 400);
			return;
		}

		if (!current_user_can('manage_options')) {
			wp_send_json_error(['message' => 'Insufficient permissions'], 403);
			return;
		}

		$post_value = isset($_POST['value']) ? ($_POST['value']) : [];

		$value =  json_encode($post_value);

		$old_value = get_option("fb_$option_name");
		// Convert old_value to the correct type for comparison
		// $old_value = $this->sanitize_option_value($old_value, $type);

		if ($old_value === $value) {
			wp_send_json_success(['message' => 'No changes were made']);
			return;
		}

		$updated = update_option("fb_$option_name", $value);

		if ($updated) {
			wp_send_json_success(['message' => 'Option updated successfully']);
		} else {
			$error_message = 'Failed to update option';
			if ($old_value === false) {
				$error_message .= ': Option does not exist';
			} elseif ($old_value === $value) {
				$error_message .= ': Value unchanged';
			} else {
				$error_message .= ': Unknown reason';
			}
			wp_send_json_error([
				'message' => $error_message,
				'old_value' => $old_value,
				'new_value' => $value,
				'option_name' => "fb_$option_name"
			]);
		}
	}

	/**
	 * sanitize_option_value
	 *
	 * @param mixed $value
	 * @param string $type
	 * @return mixed
	 */
	private function sanitize_option_value($value, $type) {
		switch ($type) {
			case 'number':
				return intval($value);
			case 'boolean':
				return $value === '1' || $value === 'true' || $value === true;
			case 'text':
				return sanitize_text_field($value);
			default:
				return sanitize_text_field($value);
		}
	}

	// New method to save blocks
	public function save_blocks() {
		if (!check_ajax_referer('fb_settings_nonce', 'security', false)) {
			wp_send_json_error(['message' => 'Invalid nonce'], 400);
			return;
		}

		if (!current_user_can('manage_options')) {
			wp_send_json_error(['message' => 'Insufficient permissions'], 403);
			return;
		}

		$blocks = isset($_POST['blocks']) ? json_decode(stripslashes($_POST['blocks']), true) : [];
		$activeBlocks = get_option('fb_active_blocks');

		if (!is_array($blocks)) {
			wp_send_json_error(['message' => 'Invalid blocks data'], 400);
			return;
		}

		if($activeBlocks) {
			foreach ($blocks as $key => $value) {
				if ($value === true) {
					$activeBlocks[$key] = true;
				} else {
					$activeBlocks[$key] = false;
				}
			}

			$blocks = $activeBlocks;
		}

		$updated = update_option('fb_active_blocks', $blocks, true);

		if ($updated) {
			wp_send_json_success(['message' => 'Blocks updated successfully']);
		} else {
			wp_send_json_error(['message' => 'Failed to update blocks']);
		}
	}

	public function get_blocks() {
		if (!check_ajax_referer('fb_settings_nonce', 'security', false)) {
			error_log('Invalid nonce in get_blocks');
			wp_send_json_error(['message' => 'Invalid nonce'], 400);
			return;
		}

		if (!current_user_can('manage_options')) {
			error_log('Insufficient permissions in get_blocks');
			wp_send_json_error(['message' => 'Insufficient permissions'], 403);
			return;
		}

		$activeBlocks = get_option('fb_active_blocks', []);

		// Ensure all blocks from BlockList are included, defaulting to false if not in activeBlocks
		$allBlocks = BlockList::get_instance()->get_blocks();
		$completeBlocks = array_merge(
			array_fill_keys(array_keys($allBlocks), false),
			$activeBlocks
		);

		wp_send_json_success($completeBlocks);
	}

	public function recaptcha_v2_site_key() {
		$this->handle_option_update('recaptcha_v2_site_key', 'text');
	}

	public function recaptcha_v2_secret_key() {
		$this->handle_option_update('recaptcha_v2_secret_key', 'text');
	}

	public function recaptcha_v3_site_key() {
		$this->handle_option_update('recaptcha_v3_site_key', 'text');
	}

	public function recaptcha_v3_secret_key() {
		$this->handle_option_update('recaptcha_v3_secret_key', 'text');
	}

	public function google_maps_api_key() {
		$this->handle_option_update('google_maps_api_key', 'text');
	}

	public function instagram_access_token() {
		$this->handle_option_update('instagram_access_token', 'text');
	}

	public function coming_soon_mode() {
		$this->handle_option_update('coming_soon_mode', 'boolean');
	}

	public function maintenance_mode() {
		$this->handle_option_update('maintenance_mode', 'boolean');
	}

	public function coming_soon_page_id() {
		$this->handle_option_update('coming_soon_page_id', 'text');
	}

	public function maintenance_page_id() {
		$this->handle_option_update('maintenance_page_id', 'text');
	}

	public function get_custom_icons_category() {
		if (!check_ajax_referer('fb_settings_nonce', 'security', false)) {
			wp_send_json_error(['message' => 'Invalid nonce'], 400);
			return;
		}

		global $wpdb;
		$meta_key = 'fb_custom_icon';

		// Prepare the SQL query
		$query = $wpdb->prepare(
			"SELECT p.ID, p.post_title, p.post_name, pm.meta_value AS fb_custom_icon
			 FROM {$wpdb->prefix}posts p
			 JOIN {$wpdb->prefix}postmeta pm ON p.ID = pm.post_id
			 WHERE p.post_status = %s
			   AND p.post_type = %s
			   AND pm.meta_key = %s",
			'publish',   // Post status
			'post',      // Post type
			$meta_key    // Meta key
		);

		// Execute the query and get results
		$results = $wpdb->get_results($query);
		$categories = [];
		if($results) {
			foreach( $results as $key => $single_result) {
				$categories[] = array(
					'slug' => $single_result->post_name,
					'title' => $single_result->post_title
				);
			}
		}

		if($categories) {
			$icons_name = get_option('fb_custom_icons_name');
			$icons = get_option('fb_custom_icons');
			wp_send_json_success(['categories' => $categories, 'names' => $icons_name, 'icons' => $icons]);
		}else {
			wp_send_json_error(['message' => 'Categories not found']);
		}
	}

	public function upload_custom_icons() {
		if (!check_ajax_referer('fb_settings_nonce', 'security', false)) {
			wp_send_json_error(['message' => 'Invalid nonce'], 400);
			return;
		}

		if ( ! class_exists( 'PclZip' ) ) {
			require_once ABSPATH . 'wp-admin/includes/class-pclzip.php';
		}

		global $wpdb;
		$meta_key = 'fb_custom_icon';

		// Prepare the SQL query
		$query = $wpdb->prepare(
			"SELECT p.ID, p.post_title, p.post_name, pm.meta_value AS fb_custom_icon
			 FROM {$wpdb->prefix}posts p
			 JOIN {$wpdb->prefix}postmeta pm ON p.ID = pm.post_id
			 WHERE p.post_status = %s
			   AND p.post_type = %s
			   AND pm.meta_key = %s",
			'publish',   // Post status
			'post',      // Post type
			$meta_key    // Meta key
		);

		// Execute the query and get results
		$results = $wpdb->get_results($query);

		if($results) {
			$custom_icons = [];
			$custom_icons_name = [];
			foreach($results as $key => $single_icon) {
				$icon_url = $single_icon->fb_custom_icon;
				$fileName = pathinfo(basename($icon_url), PATHINFO_FILENAME);
				$upload_directory = WP_CONTENT_DIR . '/uploads/frontis-custom-icons/'.$fileName;
				if (!is_dir($upload_directory)) {
					mkdir($upload_directory, 0755, true);
				}

				$tempZipFile = tempnam(sys_get_temp_dir(), 'downloaded_zip_');

				$ch = curl_init($icon_url);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				$data = curl_exec($ch);
				file_put_contents($tempZipFile, $data);
				$zip = new \PclZip($tempZipFile);

				$extractResult = $zip->extract(PCLZIP_OPT_PATH, $upload_directory);

				if ($extractResult == 0) {
					header("Content-type: application/json; charset=utf-8");
					http_response_code(405);
					echo json_encode(
						array(
							"message" => "Failed to extract ZIP file.",
							"status" => true,
						)
					);
					return false;
				} else {
					$filesInDirectory = scandir($upload_directory);
					if($filesInDirectory) {
						foreach($filesInDirectory as $single_dir_icon) {
							// Skip special entries '.' and '..'
							if ($single_dir_icon === '.' || $single_dir_icon === '..') {
								continue;
							}

							$file = $upload_directory . '/' . $single_dir_icon;

							// Check if it's a file before attempting to read
							if (is_file($file) && file_exists($file)) {
								$svgContent = @file_get_contents($file); // Suppress warnings with '@'
								$icon_name = pathinfo(basename($file), PATHINFO_FILENAME);
								if ($svgContent === false) {
									error_log("Failed to read file: $file");
								} else {

									// Extract width, height, and viewBox from SVG tag
									preg_match('/width="([0-9]+)px"/', $svgContent, $widthMatch);
									preg_match('/height="([0-9]+)px"/', $svgContent, $heightMatch);
									preg_match('/viewBox="([^"]+)"/', $svgContent, $viewBoxMatch);

									$width = isset($widthMatch[1]) ? (int)$widthMatch[1] : 320;  // Default width if not found
									$height = isset($heightMatch[1]) ? (int)$heightMatch[1] : 512; // Default height if not found
									$viewBox = isset($viewBoxMatch[1]) ? $viewBoxMatch[1] : '0 0 24 24'; // Default viewBox if not found

									// Remove XML declaration and <svg> tag attributes, keeping only inner SVG elements
									$svgInnerContent = preg_replace('/<svg[^>]*>|<\/svg>/', '', $svgContent);

									// Extract individual SVG elements and their attributes dynamically
									$svgElements = [];
									preg_match_all('/<(\w+)([^>]*)>/', $svgInnerContent, $matches, PREG_SET_ORDER);

									foreach ($matches as $match) {
										$tag = $match[1];
										$attributes = [];

										// Match each attribute within the element
										preg_match_all('/(\w+)="([^"]*)"/', $match[2], $attrMatches, PREG_SET_ORDER);
										foreach ($attrMatches as $attrMatch) {
											$attributes[$attrMatch[1]] = $attrMatch[2];
										}

										$svgElements[] = [
											'type' => $tag,
											'attributes' => $attributes,
										];
									}

									$icon_name = str_replace(' ', '-', $icon_name);

									// Prepare the formatted array
									$custom_icons[$icon_name] = [
										"svg" => [
											"solid" => [
												"width" => $width,
												"height" => $height,
												"viewBox" => $viewBox,
												"elements" => $svgElements,
											],
										],
										"label" => str_replace(' ', ' ', ucwords(str_replace('-', ' ', $icon_name))),
										"custom_categories" => $single_icon->post_name,
										"custom_icons" => true
									];

									if(!in_array($icon_name, $custom_icons_name)) {
										$custom_icons_name[] = $icon_name;
									}
								}
							} else {
								error_log("File does not exist or is not a file: $file");
							}
						}
					}
				}

				unlink($tempZipFile);
			}

			var_dump($custom_icons);

			update_option('fb_custom_icons', $custom_icons, true);
			update_option('fb_custom_icons_name', $custom_icons_name, true);
		}
	}
}
