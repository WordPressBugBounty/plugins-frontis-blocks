<?php

namespace FrontisBlocks\Admin\Ajax;

use FrontisBlocks\Traits\Singleton;

/**
 * GlobalSettings
 *
 * @package FrontisBlocks
 */
class GlobalSettings extends AjaxBase
{

    use Singleton;

    public function __construct()
    {
        // Other initialization code...
        add_action('wp_head', array($this, 'output_global_styles'), 999);
        add_action('rest_api_init', array($this, 'typography_init'), 999);
    }
    /**
     * register_global_settings_events
     *
     * @return void
     */
    public function register_global_settings_events()
    {
        $events = [
            'update_global_colors',
            'update_gradient_colors',
            'update_custom_colors',
            'update_custom_gradient_colors',
            'update_typography',
            'update_custom_typography',
            'update_globaltypo',
            'update_fontfamilies',
            'updated_global_asset',
            'get_global_colors',
            'get_gradient_colors',
            'get_custom_colors',
            'get_custom_gradient_colors',
            'get_typography',
            'get_custom_typography',
            'get_globaltypo',
            'get_custom_css',
            'get_fontfamilies',
            'import_demo_data',
            'import_full_site',
            'toggle_post_like',
        ];

        $this->init_ajax_events($events);
    }

    public function update_global_colors()
    {
        $this->handle_option_update('global_colors', 'array');
    }

    public function update_gradient_colors()
    {
        $this->handle_option_update('gradient_colors', 'array');
    }

    public function update_custom_colors()
    {
        $this->handle_option_update('custom_colors', 'array');
    }

    public function update_custom_gradient_colors()
    {
        $this->handle_option_update('custom_gradient_colors', 'array');
    }

    public function update_typography()
    {
        $this->handle_option_update('typography', 'array');
    }

    public function update_custom_typography()
    {
        $this->handle_option_update('custom_typography', 'array');
    }

    public function update_globaltypo()
    {
        $this->handle_option_update('globaltypo', 'array');
    }

    public function update_fontfamilies()
    {
        $this->handle_option_update('fontfamilies', 'array');
    }

    public function updated_global_asset()
    {
        $this->validate_nonce();
        $this->validate_permissions();
        update_option('fb_global_asset_updated', true);
    }

    public function get_global_colors()
    {
        $global_colors = get_option('fb_global_colors');

        if ($global_colors) {
            wp_send_json_success($global_colors);
        }

        wp_send_json_error('Global color not found.');
    }

    public function get_gradient_colors()
    {
        $global_gradient_colors = get_option('fb_gradient_colors');
        if ($global_gradient_colors) {
            wp_send_json_success($global_gradient_colors);
        }
        wp_send_json_error('Gradient color not found.');
    }

    public function get_custom_colors()
    {
        $custom_colors = get_option('fb_custom_colors');
        if ($custom_colors) {
            wp_send_json_success($custom_colors);
        }

        wp_send_json_error('Custom color not found.');

    }

    public function get_custom_gradient_colors()
    {
        $custom_gradient_colors = get_option('fb_custom_gradient_colors');
        if ($custom_gradient_colors) {
            wp_send_json_success($custom_gradient_colors);
        }

        wp_send_json_error('Custom gradient color not found.');
    }

    public function get_typography()
    {
        $typography = get_option('fb_typography');
        if ($typography) {
            wp_send_json_success($typography);
        }

        wp_send_json_error('Typography not found.');
    }

    public function get_custom_typography()
    {
        $typography = get_option('fb_custom_typography');
        if ($typography) {
            wp_send_json_success($typography);
        }

        wp_send_json_error('Typography not found.');
    }

    public function get_globaltypo()
    {
        $global_typography = get_option('fb_globaltypo');
        if ($global_typography) {
            wp_send_json_success($global_typography);
        }

        wp_send_json_error('Global Typography not found.');
    }

    public function get_fontfamilies()
    {
        $font_families = get_option('fb_fontfamilies');
        if ($font_families) {
            wp_send_json_success($font_families);
        }

        wp_send_json_error('Font families not found.');
    }

    public function get_custom_css()
    {
        $custom_css = get_option('fb_custom_css');

        if ($custom_css) {
            wp_send_json_success($custom_css);
        }

        wp_send_json_error('Custom css not found.');
    }

    /**
     * handle_option_update
     *
     * @param string $option_name
     * @param string $type
     * @return void
     */
    private function handle_option_update($option_name, $type)
    {
        $this->validate_nonce();
        $this->validate_permissions();
        $this->update_option("fb_$option_name", $type);
    }

    /**
     * validate_nonce
     *
     * @return void
     */
    private function validate_nonce()
    {
        if (!wp_verify_nonce($_POST['nonce'], 'fb_sidebar_nonce')) {
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

    /**
     * update_option
     *
     * @param string $option_name
     * @param string $type
     * @return void
     */
    private function update_option($option_name, $type)
    {
        $post_data = isset($_POST['data']) ? $_POST['data'] : [];
        $updated = update_option($option_name, $post_data, true);

        if ($updated) {
            $global_colors = get_option($option_name);
            $data = array(
                'option_name' => $option_name,
                'data' => $global_colors
            );

            wp_send_json_success($data);
        }

        wp_send_json_error('Update failed.');
    }
    /**
     * sanitize_option_value
     *
     * @param mixed $value
     * @param string $type
     * @return mixed
     */
    private function sanitize_option_value($value, $type)
    {
        switch ($type) {
            case 'array':
                return is_array($value) ? $value : [];
            default:
                return sanitize_text_field($value);
        }
    }

    public function toggle_post_like()
    {
        // Verify nonce for security
        if (!wp_verify_nonce($_POST['nonce'], 'fb_post_like_nonce')) {
            wp_send_json_error('Security check failed');
            return;
        }

        // Check if user is logged in
        if (!is_user_logged_in()) {
            wp_send_json_error('User must be logged in to like posts');
            return;
        }

        $post_id = intval($_POST['post_id']);
        $user_id = get_current_user_id();

        // Validate post exists
        if (!get_post($post_id)) {
            wp_send_json_error('Invalid post ID');
            return;
        }

        // Get current user's liked posts
        $user_likes = get_user_meta($user_id, 'liked_posts', true);
        if (!is_array($user_likes)) {
            $user_likes = array();
        }

        // Get current post likes count
        $current_likes = get_post_meta($post_id, 'post_likes_count', true);
        $current_likes = $current_likes ? intval($current_likes) : 0;

        $is_liked = in_array($post_id, $user_likes);
        $new_liked_status = !$is_liked;

        if ($new_liked_status) {
            $user_likes[] = $post_id;
            $new_likes_count = $current_likes + 1;
        } else {
            $user_likes = array_diff($user_likes, array($post_id));
            $new_likes_count = max(0, $current_likes - 1);
        }

        update_user_meta($user_id, 'liked_posts', array_values($user_likes));

        update_post_meta($post_id, 'post_likes_count', $new_likes_count);

        wp_send_json_success(array(
            'liked' => $new_liked_status,
            'count' => $new_likes_count,
            'formatted_count' => $this->format_metric_count($new_likes_count)
        ));
    }

    /**
     * Format metric count method for your plugin class
     */
    public function format_metric_count($count)
    {
        $num = intval($count);

        if ($num >= 1000000) {
            $formatted = round($num / 1000000, 2);
            return $formatted . 'M';
        } elseif ($num >= 1000) {
            $formatted = round($num / 1000, 2);
            return $formatted . 'k';
        } else {
            return strval($num);
        }
    }

    //Demo import for blocks
    // public function import_demo_data() {
    //     try {
    //         // Security check
    //         $nonce = $_POST['security'] ?? '';
    //         if (!wp_verify_nonce($nonce, 'fb_import_demo_data')) {
    //             wp_send_json_error('Invalid nonce.');
    //             return;
    //         }

    //         // Get block ID from POST
    //         $block_id = $_POST['block_id'] ?? '';
    //         if (empty($block_id)) {
    //             wp_send_json_error('Block ID is required.');
    //             return;
    //         }

    //         // Get demo data from JSON
    //         $json_file_path = WP_PLUGIN_DIR . '/frontis-blocks/testing-page.json';
    //         $json_data = file_get_contents($json_file_path);
    //         $demo_data = json_decode($json_data, true);

    //         // Update specific block's content
    //         $block_content = array(
    //             'blockId' => $block_id,
    //             'content' => $demo_data
    //         );

    //         // Store in transient for temporary use
    //         set_transient('fb_block_' . $block_id, $block_content, HOUR_IN_SECONDS);

    //         wp_send_json_success(array(
    //             'block_id' => $block_id,
    //             'content' => serialize_blocks($demo_data),
    //             'message' => 'Demo data imported successfully!'
    //         ));

    //     } catch (Exception $e) {
    //         wp_send_json_error('Error: ' . $e->getMessage());
    //     }
    // }

    //Demo import for pages
    // public function import_demo_data() {
    //     try {
    //         // Security check
    //         $nonce = $_POST['security'] ?? '';
    //         if (!wp_verify_nonce($nonce, 'fb_import_demo_data')) {
    //             wp_send_json_error('Invalid nonce.');
    //             return;
    //         }

    //         // Get block ID and post ID
    //         $block_id = $_POST['block_id'] ?? '';
    //         $post_id = $_POST['post_id'] ?? 0; // Get post ID from AJAX

    //         if (empty($post_id)) {
    //             wp_send_json_error('Post ID is required.');
    //             return;
    //         }

    //         // Get demo data from JSON
    //         $json_file_path = WP_PLUGIN_DIR . '/frontis-blocks/testing-page.json';
    //         $json_data = file_get_contents($json_file_path);
    //         $demo_data = json_decode($json_data, true);
    //         $serialized_content = serialize_blocks($demo_data);

    //         if (!empty($block_id)) {
    //             // Store block-specific content
    //             set_transient('fb_block_' . $block_id, [
    //                 'blockId' => $block_id,
    //                 'content' => $demo_data
    //             ], HOUR_IN_SECONDS);

    //             wp_send_json_success([
    //                 'block_id' => $block_id,
    //                 'content' => $serialized_content,
    //                 'message' => 'Demo data imported successfully!'
    //             ]);
    //         } else {
    //             // Update full page content
    //             $post_data = [
    //                 'ID'           => $post_id,
    //                 'post_content' => $serialized_content
    //             ];

    //             $update_result = wp_update_post($post_data, true);

    //             if (is_wp_error($update_result)) {
    //                 wp_send_json_error('Failed to update post: ' . $update_result->get_error_message());
    //             } else {
    //                 wp_send_json_success([
    //                     'content' => $serialized_content,
    //                     'message' => 'Page content updated with demo data!'
    //                 ]);
    //             }
    //         }
    //     } catch (Exception $e) {
    //         wp_send_json_error('Error: ' . $e->getMessage());
    //     }
    // }     

    //demo import for full site
    // public function import_demo_data() {
    //     try {
    //         // Security check
    //         $nonce = $_POST['security'] ?? '';
    //         if (!wp_verify_nonce($nonce, 'fb_import_demo_data')) {
    //             wp_send_json_error('Invalid nonce.');
    //             return;
    //         }

    //         // Get block ID and post ID
    //         $block_id = $_POST['block_id'] ?? '';
    //         $post_id = $_POST['post_id'] ?? 0; // Get post ID from AJAX

    //         if (empty($post_id)) {
    //             wp_send_json_error('Post ID is required.');
    //             return;
    //         }

    //         // Get demo data from JSON
    //         $json_file_path = WP_PLUGIN_DIR . '/frontis-blocks/testing-page.json';
    //         $json_data = file_get_contents($json_file_path);
    //         $demo_data = json_decode($json_data, true);
    //         $serialized_content = serialize_blocks($demo_data);

    //         if (!empty($block_id)) {
    //             // Store block-specific content
    //             set_transient('fb_block_' . $block_id, [
    //                 'blockId' => $block_id,
    //                 'content' => $demo_data
    //             ], HOUR_IN_SECONDS);

    //             wp_send_json_success([
    //                 'block_id' => $block_id,
    //                 'content' => $serialized_content,
    //                 'message' => 'Demo data imported successfully!'
    //             ]);
    //         } else {
    //             // Update full page content
    //             $post_data = [
    //                 'ID'           => $post_id,
    //                 'post_content' => $serialized_content
    //             ];

    //             $update_result = wp_update_post($post_data, true);

    //             if (is_wp_error($update_result)) {
    //                 wp_send_json_error('Failed to update post: ' . $update_result->get_error_message());
    //             } else {
    //                 wp_send_json_success([
    //                     'content' => $serialized_content,
    //                     'message' => 'Page content updated with demo data!'
    //                 ]);
    //             }
    //         }
    //     } catch (Exception $e) {
    //         wp_send_json_error('Error: ' . $e->getMessage());
    //     }
    // }  

    // PHP: Import Full Site
    public function import_full_site()
    {
        try {
            error_log('AJAX Data Received: ' . print_r($_POST, true));
            // Security check
            $nonce = $_POST['security'] ?? '';
            if (!wp_verify_nonce($nonce, 'fb_import_demo_data')) {
                wp_send_json_error('Invalid nonce.');
                return;
            }

            // Get data from AJAX request
            $config = $_POST['config'] ?? [];
            $block_id = sanitize_text_field($_POST['block_id'] ?? '');
            $post_id = intval($_POST['post_id'] ?? 0);

            if (empty($post_id)) {
                wp_send_json_error('Error: Post ID is required.');
                return;
            }

            // Handle global colors and typography
            if (!empty($config)) {
                $colors = $config['colors'] ?? [];
                $typography = $config['typography'] ?? [];

                // Update colors
                update_option('global_site_colors', $colors);

                // Update typography for each element
                if (!empty($typography)) {
                    foreach ($typography as $element => $settings) {
                        update_option("fb_typography_{$element}", $settings);
                    }
                }

                // Generate and save combined styles
                $custom_styles = $this->generate_color_css($colors) . $this->generate_typography_css($typography);
                update_option('fb_global_styles', $custom_styles);
            }

            // Validate demo content file existence
            $json_file_path = WP_PLUGIN_DIR . '/frontis-blocks/full-site.json';
            if (!file_exists($json_file_path)) {
                wp_send_json_error('Error: Demo data file not found.');
                return;
            }

            // Read and decode demo content
            $json_data = file_get_contents($json_file_path);
            $demo_data = json_decode($json_data, true);

            if (json_last_error() !== JSON_ERROR_NONE || empty($demo_data)) {
                wp_send_json_error('Error: Invalid or empty demo content.');
                return;
            }

            // Ensure proper block serialization
            $serialized_content = serialize_blocks($demo_data['blocks'] ?? $demo_data);

            if (!empty($block_id)) {
                // Store block-specific content using transient
                set_transient('fb_block_' . $block_id, [
                    'blockId' => $block_id,
                    'content' => $demo_data
                ], HOUR_IN_SECONDS);

                wp_send_json_success([
                    'block_id' => $block_id,
                    'content' => $serialized_content,
                    'message' => 'Block imported successfully with global settings!',
                ]);
            } else {
                // Update full post content
                $post_data = [
                    'ID' => $post_id,
                    'post_content' => $serialized_content,
                ];

                $update_result = wp_update_post($post_data, true);

                if (is_wp_error($update_result)) {
                    wp_send_json_error('Error: Failed to update post - ' . $update_result->get_error_message());
                } else {
                    wp_send_json_success([
                        'content' => $serialized_content,
                        'message' => 'Full site imported successfully with global settings!',
                    ]);
                }
            }
        } catch (Exception $e) {
            wp_send_json_error('Error: ' . $e->getMessage());
        }
    }


    private function generate_color_css($colors)
    {
        if (empty($colors) || !is_array($colors)) {
            return '';
        }

        $css = ':root {';
        $color_map = [];

        foreach ($colors as $index => $color) {
            $slug = sanitize_key($color['slug']);
            $hex = sanitize_hex_color($color['hex']);
            $css .= "--{$slug}: {$hex};\n";
            $color_map[$slug] = $hex;
        }

        $css .= "}\n";

        // Apply primary color to all text elements (p, span, h1-h6)
        if (!empty($colors[0])) {
            $primary_slug = sanitize_key($colors[0]['slug']);
            $css .= "body p, body span, body h1, body h2, body h3, body h4, body h5, body h6 { color: var(--{$primary_slug}); }\n";
        }

        // Apply secondary color to all anchor (a) elements
        if (!empty($colors[1])) {
            $secondary_slug = sanitize_key($colors[1]['slug']);
            $css .= "body a { color: var(--{$secondary_slug}); }\n";
        }

        // Apply remaining colors to other elements
        $extra_elements = ['body button', 'body input', 'body blockquote', 'body label', 'body code'];
        foreach (array_slice($colors, 2) as $index => $color) {
            $element = $extra_elements[$index % count($extra_elements)];
            $slug = sanitize_key($color['slug']);
            $css .= "{$element} { color: var(--{$slug}); }\n";
        }

        return $css;
    }


    private function generate_typography_css($typography)
    {
        if (empty($typography) || !is_array($typography)) {
            return '';
        }

        $css = ':root {';
        $elements = ['text', 'link', 'button', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6'];

        // First define all typography variables in root
        foreach ($elements as $element) {
            $option_name = "fb_typography_{$element}";

            // Check if typography settings exist in the input array
            $settings = $typography[$element] ?? get_option($option_name);

            // Ensure settings are in array format
            if (is_string($settings)) {
                $settings = json_decode($settings, true);
            }

            error_log("Processed {$element} typography: " . print_r($settings, true));

            if (empty($settings) || !is_array($settings)) {
                continue;
            }

            // Define variables for each property
            if (!empty($settings['fontSize']['Desktop'])) {
                $css .= "\n  --fb-{$element}-font-size: {$settings['fontSize']['Desktop']}{$settings['fontSizeUnit']['Desktop']};";
            }
            if (!empty($settings['fontFamily'])) {
                $css .= "\n  --fb-{$element}-font-family: {$settings['fontFamily']};";
            }
            if (!empty($settings['fontWeight'])) {
                $css .= "\n  --fb-{$element}-font-weight: {$settings['fontWeight']};";
            }
            if (!empty($settings['fontStyle'])) {
                $css .= "\n  --fb-{$element}-font-style: {$settings['fontStyle']};";
            }
            if (!empty($settings['textTransform'])) {
                $css .= "\n  --fb-{$element}-text-transform: {$settings['textTransform']};";
            }
            if (!empty($settings['textDecoration'])) {
                $css .= "\n  --fb-{$element}-text-decoration: {$settings['textDecoration']};";
            }
            if (!empty($settings['letterSpacing']['Desktop'])) {
                $css .= "\n  --fb-{$element}-letter-spacing: {$settings['letterSpacing']['Desktop']}{$settings['letterSpacingUnit']['Desktop']};";
            }
            if (!empty($settings['lineHeight']['Desktop'])) {
                $css .= "\n  --fb-{$element}-line-height: {$settings['lineHeight']['Desktop']}{$settings['lineHeightUnit']['Desktop']};";
            }
        }
        $css .= "\n}\n\n";

        // Define CSS selectors and apply typography variables
        foreach ($elements as $element) {
            $selector = match ($element) {
                'text' => 'body p, body span',
                'link' => 'a',
                'button' => 'button, .button',
                default => $element,
            };

            $css .= "{$selector} {\n";
            $css .= "  font-size: var(--fb-{$element}-font-size);\n";
            $css .= "  font-family: var(--fb-{$element}-font-family);\n";
            $css .= "  font-weight: var(--fb-{$element}-font-weight);\n";
            $css .= "  font-style: var(--fb-{$element}-font-style);\n";
            $css .= "  text-transform: var(--fb-{$element}-text-transform);\n";
            $css .= "  text-decoration: var(--fb-{$element}-text-decoration);\n";
            $css .= "  letter-spacing: var(--fb-{$element}-letter-spacing);\n";
            $css .= "  line-height: var(--fb-{$element}-line-height);\n";
            $css .= "}\n\n";
        }

        // Handle responsive styles (Tablet and Mobile)
        foreach (['Tablet' => '768px', 'Mobile' => '480px'] as $device => $breakpoint) {
            $css .= "@media (max-width: {$breakpoint}) {\n";
            $css .= "  :root {\n";

            foreach ($elements as $element) {
                $settings = $typography[$element] ?? get_option("fb_typography_{$element}");

                if (is_string($settings)) {
                    $settings = json_decode($settings, true);
                }

                if (empty($settings) || !is_array($settings)) {
                    continue;
                }

                if (!empty($settings['fontSize'][$device])) {
                    $css .= "    --fb-{$element}-font-size: {$settings['fontSize'][$device]}{$settings['fontSizeUnit'][$device]};\n";
                }
                if (!empty($settings['letterSpacing'][$device])) {
                    $css .= "    --fb-{$element}-letter-spacing: {$settings['letterSpacing'][$device]}{$settings['letterSpacingUnit'][$device]};\n";
                }
                if (!empty($settings['lineHeight'][$device])) {
                    $css .= "    --fb-{$element}-line-height: {$settings['lineHeight'][$device]}{$settings['lineHeightUnit'][$device]};\n";
                }
            }

            $css .= "  }\n";
            $css .= "}\n\n";
        }

        return $css;
    }

    private function add_responsive_typography($selector, $settings)
    {
        $css = '';

        // Tablet styles
        if (!empty($settings['fontSize']['tablet'])) {
            $css .= "@media (max-width: 768px) {\n";
            $css .= "  {$selector} {\n";
            $css .= "    font-size: {$settings['fontSize']['tablet']}{$settings['fontSizeUnit']};\n";

            if (!empty($settings['letterSpacing']['tablet'])) {
                $css .= "    letter-spacing: {$settings['letterSpacing']['tablet']}{$settings['letterSpacingUnit']};\n";
            }

            if (!empty($settings['lineHeight']['tablet'])) {
                $css .= "    line-height: {$settings['lineHeight']['tablet']}{$settings['lineHeightUnit']};\n";
            }

            $css .= "  }\n";
            $css .= "}\n\n";
        }

        // Mobile styles
        if (!empty($settings['fontSize']['mobile'])) {
            $css .= "@media (max-width: 480px) {\n";
            $css .= "  {$selector} {\n";
            $css .= "    font-size: {$settings['fontSize']['mobile']}{$settings['fontSizeUnit']};\n";

            if (!empty($settings['letterSpacing']['mobile'])) {
                $css .= "    letter-spacing: {$settings['letterSpacing']['mobile']}{$settings['letterSpacingUnit']};\n";
            }

            if (!empty($settings['lineHeight']['mobile'])) {
                $css .= "    line-height: {$settings['lineHeight']['mobile']}{$settings['lineHeightUnit']};\n";
            }

            $css .= "  }\n";
            $css .= "}\n";
        }

        return $css;
    }

    public function output_global_styles()
    {
        $styles = get_option('fb_global_styles', '');
        if (!empty($styles)) {
            // Add debugging comment
            echo "<!-- Typography styles begin -->\n";
            echo "<style id='fb-global-styles'>\n" . $styles . "\n</style>";
            echo "<!-- Typography styles end -->\n";
        } else {
            // Debug if no styles were found
            echo "<!-- No global styles found in database -->";
        }
    }

    public function typography_init()
    {
        $elements = ['text', 'link', 'button', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6'];

        foreach ($elements as $element) {
            register_setting(
                'fb_typography',
                'fb_typography_' . $element,
                [
                    'type' => 'string',
                    'show_in_rest' => true,
                    'default' => ''
                ]
            );
        }
    }

}