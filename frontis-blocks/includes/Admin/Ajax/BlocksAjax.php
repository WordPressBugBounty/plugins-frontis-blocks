<?php

namespace FrontisBlocks\Admin\Ajax;

use FrontisBlocks\Traits\Singleton;
use FrontisBlocks\Utils\Helper;

/**
 * Return blocks settings content
 *
 * @package FrontisBlocks
 */
class BlocksAjax extends AjaxBase
{

	use Singleton;

	public function __construct()
	{

	}
	/**
	 * register_global_settings_events
	 *
	 * @return void
	 */
	public function register_blocks_ajax_events()
	{
		$events = [
			'get_post'
		];

		$this->init_ajax_events($events);
	}

	/**
	 * handle_option_update
	 *
	 * @param string $option_name
	 * @param string $type
	 * @return void
	 */
	private function check_authorized()
	{
		$this->validate_nonce();
		$this->validate_permissions();
	}

	/**
	 * validate_nonce
	 *
	 * @return void
	 */
	private function validate_nonce()
	{
		if (!wp_verify_nonce($_POST['security'], 'fb_block_nonce')) {
			wp_send_json_error('Invalid nonce');
		}
	}

	/**
	 * check permissions
	 *
	 * @return void
	 */
	private function validate_permissions()
	{
		if (!current_user_can('manage_options')) {
			wp_send_json_error('Insufficient permissions');
		}
	}

	public function get_post() {
		$this->check_authorized();

        // Data source is post type
        if ($_POST['data_source'] === 'post_type') {
            switch ($_POST['source_field']) {
                // Post group fields
                case 'post_title':
                    $post_title =  Helper::get_single_field($_POST['post_id'], 'post_title');
                    $data = array(
                        'post_title' => $post_title,
                    );
                    Helper::json_success($data);
                case 'post_excerpt':
                    $post_excerpt =  Helper::get_single_field($_POST['post_id'], 'post_excerpt');
                    $data = array(
                        'post_excerpt' => $post_excerpt,
                    );
                    Helper::json_success($data);
                case 'featured_image':
                    $thumbnail_id = get_post_meta($_POST['post_id'], '_thumbnail_id', true);
                    $attachment_url =  $thumbnail_id ? wp_get_attachment_url($thumbnail_id) : '';
                    $data = array(
                        'featured_image' => $attachment_url,
                    );
                    Helper::json_success($data);
                case 'post_custom_field':
                    $meta_key = !empty($_POST['meta_key']) ? sanitize_key($_POST['meta_key']) : '';
                    return $meta_key ? get_post_meta($_POST['post_id'], $meta_key, true) : '';
                case 'post_date':
                    $post_date = Helper::get_single_field($_POST['post_id'], 'post_date');
                    $data = array(
                        'post_date' => $post_date,
                    );
                    Helper::json_success($data);
                case 'post_time':
                    $post_date = Helper::get_single_field($_POST['post_id'], 'post_date');
                    $post_time = $post_date ? date('H:i:s', strtotime($post_date)) : '';
                    $data = array(
                        'post_time' => $post_time,
                    );
                    Helper::json_success($data);
                case 'post_terms':
                    $taxonomy = !empty($_POST['taxonomy']) ? sanitize_text_field($_POST['taxonomy']) : '';
                    if ($taxonomy) {
                        $terms = wp_get_post_terms($_POST['post_id'], $taxonomy, ['fields' => 'names']);
                        return is_array($terms) ? implode(', ', $terms) : '';
                    }
                    return '';
                case 'post_id':
                    return $_POST['post_id'];
                case 'comments_number':
                    return Helper::get_single_field($_POST['post_id'], 'comment_count')->comment_count ?: '0';

                // Author group fields
                case 'author_name':
                case 'author_first_name':
                case 'author_last_name':
                case 'author_nickname':
                case 'author_bio':
                case 'author_email':
                case 'author_website':
                    $field_map = [
                        'author_name' => 'display_name',
                        'author_first_name' => 'first_name',
                        'author_last_name' => 'last_name',
                        'author_nickname' => 'nickname',
                        'author_bio' => 'description',
                        'author_email' => 'user_email',
                        'author_website' => 'user_url'
                    ];
                    $meta_field = $field_map[$_POST['source_field']];
                    $author_id = Helper::get_single_field($_POST['post_id'], 'post_author')->post_author;
                    return $author_id ? get_the_author_meta($meta_field, $author_id) : '';
                case 'author_avatar_image_url':
                    $author_id = Helper::get_single_field($_POST['post_id'], 'post_author')->post_author;
                    return $author_id ? get_avatar_url($author_id) : '';
                case 'author_custom_field':
                    $meta_key = !empty($_POST['meta_key']) ? sanitize_key($_POST['meta_key']) : '';
                    $author_id = Helper::get_single_field($_POST['post_id'], 'post_author')->post_author;
                    return ($meta_key && $author_id) ? get_user_meta($author_id, $meta_key, true) : '';
                default:
                    return '';
            }
        }
	}
}
