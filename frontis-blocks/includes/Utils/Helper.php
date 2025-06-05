<?php

namespace FrontisBlocks\Utils;

class Helper
{
    /**
     * Check if the given block is a Frontis block
     *
     * @param string $block_content The block content
     * @param array $parsed_block The parsed block
     * @param string $class_attr The class attribute to check
     * @return bool
     */
    public static function is_frontis_block($block_content, $parsed_block, $class_attr = 'blockClass')
    {
        return isset($parsed_block['attrs'][$class_attr]) && strpos($parsed_block['attrs'][$class_attr], 'frontis-block') !== false;
    }

    /**
     * Generate a unique ID
     *
     * @return string
     */
    public static function generate_unique_id()
    {
        return 'fb-' . uniqid();
    }

    /**
     * Get block assets URL
     *
     * @param string $path The relative path to the asset
     * @return string
     */
    public static function get_block_asset_url($path)
    {
        return plugin_dir_url(FB_PLUGIN_PATH) . 'build/blocks/' . $path;
    }

    /**
     * Get plugin settings
     *
     * @param string $key The setting key
     * @param mixed $default The default value if the setting doesn't exist
     * @return mixed
     */
    public static function get_plugin_setting($key, $default = null)
    {
        $settings = get_option('frontis_blocks_settings', []);
        return isset($settings[$key]) ? $settings[$key] : $default;
    }

    /**
     * Check if the current page is a Gutenberg editor page
     *
     * @return bool
     */
    public static function is_gutenberg_editor()
    {
        global $pagenow;
        return in_array($pagenow, ['post.php', 'post-new.php', 'site-editor.php', 'widgets.php']);
    }

    /**
     * Get size information for all currently-registered image sizes.
     *
     * @global $_wp_additional_image_sizes
     * @uses   get_intermediate_image_sizes()
     * @link   https://codex.wordpress.org/Function_Reference/get_intermediate_image_sizes
     * @since  1.9.0
     * @return array $sizes Data for all currently-registered image sizes.
     */
    public static function get_image_sizes()
    {

        global $_wp_additional_image_sizes;

        // Define essential sizes
        $essential_sizes = array('full', 'thumbnail', 'medium', 'medium_large', 'large');

        $sizes = get_intermediate_image_sizes();
        $image_sizes = array();

        // Add the 'Full' size option
        $image_sizes[] = array(
            'value' => 'full',
            'label' => esc_html__('Full', 'frontis-blocks'),
        );

        foreach ($sizes as $size) {
            // Include only essential sizes
            if (in_array($size, $essential_sizes, true)) {
                $image_sizes[] = array(
                    'value' => $size,
                    'label' => ucwords(trim(str_replace(array('-', '_'), array(' ', ' '), $size))),
                );
            } elseif (isset($_wp_additional_image_sizes[$size])) {
                // Include additional sizes if they are essential
                if (in_array($size, $essential_sizes, true)) {
                    $image_sizes[] = array(
                        'value' => $size,
                        'label' => sprintf(
                            '%1$s (%2$sx%3$s)',
                            ucwords(trim(str_replace(array('-', '_'), array(' ', ' '), $size))),
                            $_wp_additional_image_sizes[$size]['width'],
                            $_wp_additional_image_sizes[$size]['height']
                        ),
                    );
                }
            }
        }

        // Apply the filter for additional customization
        $image_sizes = apply_filters('fb_post_featured_image_sizes', $image_sizes);

        return $image_sizes;
    }

    /**
     * Get all taxonomies.
     *
     * @since 1.11.0
     * @access public
     */
    public static function get_related_taxonomy()
    {


        $post_types = self::get_post_types();

        $return_array = array();

        foreach ($post_types as $key => $value) {
            $post_type = $value['value'];

            $taxonomies = get_object_taxonomies($post_type, 'objects');
            $data = array();

            foreach ($taxonomies as $tax_slug => $tax) {
                if (!$tax->public || !$tax->show_ui || !$tax->show_in_rest) {
                    continue;
                }

                $data[$tax_slug] = $tax;

                $terms = get_terms($tax_slug);

                $related_tax = array();

                if (!empty($terms)) {
                    foreach ($terms as $t_index => $t_obj) {
                        $related_tax[] = array(
                            'id' => $t_obj->term_id,
                            'name' => $t_obj->name,
                            'child' => get_term_children($t_obj->term_id, $tax_slug),
                        );
                    }
                    $return_array[$post_type]['terms'][$tax_slug] = $related_tax;
                }
            }

            $return_array[$post_type]['taxonomy'] = $data;

        }

        return apply_filters('fb_post_loop_taxonomies', $return_array);
    }

    public static function get_all_authors_with_posts()
    {
        $args = array(
            'has_published_posts' => true,
            'fields' => 'all_with_meta',
        );
        return get_users($args);
    }

    public static function get_page_name()
    {
        global $wp_query;
        if ($wp_query->queried_object) {
            return $wp_query->queried_object->post_name;
        }
    }

    public static function remove_css_files()
    {
        $upload_dir = wp_upload_dir();
        $css_dir = trailingslashit($upload_dir['basedir']) . 'frontis-blocks/';
        $css_files = glob($css_dir . '*.css');
        foreach ($css_files as $file) {
            if (basename($file) !== 'frontis-blocks.css') {
                unlink($file);
            }
        }
    }

    public static function get_current_page_name($post_id = null)
    {
        if ($post_id) {
            $post = get_post($post_id);
            return $post->post_name;
        } else {
            return self::get_page_name();
        }
    }

    public static function get_post_types()
    {
        $post_types = get_post_types(
            array(
                'public' => true,
                'show_in_rest' => true,
            ),
            'objects'
        );

        $options = array();

        foreach ($post_types as $post_type) {

            if ('attachment' === $post_type->name) {
                continue;
            }

            $options[] = array(
                'value' => $post_type->name,
                'label' => $post_type->label,
            );
        }

        return apply_filters('fb_loop_post_types', $options);
    }

    /**
     * Sanitize HTML classes
     *
     * @param string|array $classes The classes to sanitize
     * @return string
     */
    public static function sanitize_html_classes($classes)
    {
        if (!is_array($classes)) {
            $classes = explode(' ', $classes);
        }

        $classes = array_map('sanitize_html_class', $classes);
        return implode(' ', $classes);
    }

    /**
     * Get responsive breakpoints
     *
     * @return array
     */
    public static function get_responsive_breakpoints()
    {
        return [
            'mobile' => 767,
            'tablet' => 1024,
            'desktop' => 1200,
        ];
    }

    /**
     * Get installed WordPress Plugin List
     *
     * @return array
     */
    public static function get_plugins()
    {
        if (!function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        return get_plugins();
    }

    /**
     * Get Active Plugins List
     */
    public static function get_active_plugin_list()
    {
        $active_plugins = get_option('active_plugins');
        if (is_multisite()) {
            $all = wp_get_active_network_plugins();
            if ($all) {
                $active_plugins = array_merge($active_plugins, array_map(function ($each) {
                    $arr = explode('/', $each);
                    return $arr[count($arr) - 2] . DIRECTORY_SEPARATOR . end($arr);
                }, $all));
            }
        }
        return $active_plugins;
    }

    /**
     * Parse block content for specific data
     *
     * @param string $content The block content
     * @param string $search The string to search for
     * @return string|null
     */
    public static function parse_block_content($content, $search)
    {
        preg_match('/' . preg_quote($search, '/') . '="([^"]*)"/', $content, $matches);
        return isset($matches[1]) ? $matches[1] : null;
    }

    /**
     * Check if a plugin is active
     *
     * @param string $plugin_path The path to the plugin file
     * @return bool
     */
    public static function is_plugin_active($plugin_path)
    {
        return in_array($plugin_path, (array) get_option('active_plugins', []), true) || self::is_plugin_active_for_network($plugin_path);
    }

    public static function generate_css($styles, $container_css)
    {
        // Generate CSS for the block ID
        $css = array_map(
            function ($selector, $styles) {
                $filtered_styles = implode("; ", array_map(
                    fn($key, $value) => (trim($value) !== "" && preg_match('/^\D+$/', $value) === 0) ? "$key: $value" : "",
                    array_keys($styles),
                    $styles
                ));

                // Return CSS if styles are valid
                return $filtered_styles ? "#$selector { $filtered_styles; }" : "";
            },
            array_keys($container_css),
            $container_css
        );

        // Filter out empty styles and combine
        return implode("\n", array_filter($css));
    }

    /**
     * Check if a plugin is active for the entire network
     *
     * @param string $plugin_path The path to the plugin file
     * @return bool
     */
    public static function is_plugin_active_for_network($plugin_path)
    {
        if (!is_multisite()) {
            return false;
        }

        $plugins = get_site_option('active_sitewide_plugins');
        if (isset($plugins[$plugin_path])) {
            return true;
        }

        return false;
    }

    public static function load_google_font($fonts)
    {
        if (is_array($fonts) && !empty($fonts)) {

            $font_url = "https://fonts.googleapis.com/css?family=";
            $font_url .= implode("|", array_map(function($font) {
                return str_replace(' ', '+', $font) . ":100,100italic,200,200italic,300,300italic,400,400italic,500,500italic,600,600italic,700,700italic,800,800italic,900,900italic";
            }, $fonts));

            echo $font_url;
            // $gfonts = '';
            // $gfonts_attr = ':100,100italic,200,200italic,300,300italic,400,400italic,500,500italic,600,600italic,700,700italic,800,800italic,900,900italic';
            
            // foreach ($fonts as $font) {
            //     $gfonts .= str_replace(' ', '+', trim($font)) . $gfonts_attr . '|';
            // }
            
            // $gfonts = rtrim($gfonts, '|');
            
            // if (!empty($gfonts)) {
            //     $query_args = [
            //         'family' => $gfonts,
            //         'display' => 'swap'
            //     ];
                
            //     wp_enqueue_style(
            //         'frontis-blocks-preconnect',
            //         'https://fonts.googleapis.com',
            //         [],
            //         null
            //     );
                
            //     wp_style_add_data('frontis-blocks-preconnect', 'crossorigin', 'anonymous');
                
            //     wp_register_style(
            //         'frontis-blocks-fonts',
            //         add_query_arg($query_args, 'https://fonts.googleapis.com/css2'),
            //         [],
            //         FB_VERSION
            //     );
                
            //     wp_enqueue_style('frontis-blocks-fonts');
            // }
            
            // Reset.
            $gfonts = '';
        }
    }

        /**
     * Generate Font family from Blocks Attributes
     *
     * @since 4.0.0
     * @access public
     */
    public static function get_fonts_family( $attributes )
	{
        $font_family_keys = preg_grep('/^(\w+)FontFamily/i', array_keys($attributes), 0);
        $font_weight_keys = preg_grep('/^(\w+)FontWeight/i', array_keys($attributes), 0);

        $font_data = [];

        foreach ($font_family_keys as $key) {
            $prefix = preg_replace('/FontFamily$/', '', $key); // Extract the prefix (e.g., 'heading', 'body')

            // Find the corresponding font weight key
            $weight_key = $prefix . 'FontWeight';
            $font_family = $attributes[$key] ?? '';
            $font_weight = $attributes[$weight_key] ?? '';

            if (!empty($font_family) && !isset($font_data[$font_family])) {
                $font_data[$font_family] = $font_weight;
            }
        }

        return $font_data;
	}

    // Get font families from global settings
    public static function extract_font_families($data) {
        $fontFamilies = array();
    
        // Convert the array to a string representation
        $dataString = var_export($data, true);
        
        // Pattern to match fontFamily and fontWeight pairs
        $pattern = '/\'fontFamily\'\s*=>\s*\'([^\']*)\',(?:.*?)\'fontWeight\'\s*=>\s*\'([^\']*)\'/s';
        
        // Perform the regex matching
        preg_match_all($pattern, $dataString, $matches, PREG_SET_ORDER);
        
        // Process matches
        foreach ($matches as $match) {
            $fontFamily = $match[1];
            $fontWeight = $match[2];

            if(array_key_exists($fontFamily, $fontFamilies)) {
                $fontFamilies[$fontFamily][] = $fontWeight;
            } else {
                $fontFamilies[$fontFamily][] = $fontWeight;
            }
        }
        
        return $fontFamilies;
    }

    public static function get_css_files($dir) {
		$cssFiles = [];
		$files = scandir($dir);
		foreach ($files as $file) {
			if ($file == '.' || $file == '..') continue;
			
			$path = $dir . '/' . $file;
			if (is_dir($path)) {
				// Recursively scan subdirectories
				$cssFiles = array_merge($cssFiles, self::get_css_files($path));
			} else if (pathinfo($path, PATHINFO_EXTENSION) == 'css') {
				$cssFiles[] = $path;
			}
		}
		
		return $cssFiles;
	}

    public static function get_js_files($dir) {
		$jsFiles = [];
		$files = scandir($dir);
		foreach ($files as $file) {
			if ($file == '.' || $file == '..') continue;    
			
			$path = $dir . '/' . $file;
			if (is_dir($path)) {
				$jsFiles = array_merge($jsFiles, self::get_js_files($path));
			} else if (pathinfo($path, PATHINFO_EXTENSION) == 'js') {
				$jsFiles[] = $path;
			}
		}   

		return $jsFiles;
	}


    public static function get_blocks_inside_template($block_name)
    {
        global $wpdb;
        $template_post = get_posts([
            'post_type' => $wpdb->prefix . 'template',
            'numberposts' => 1,
        ]);

        if (!empty($template_post)) {
            $template_content = $template_post[0]->post_content;
            return strpos($template_content, $block_name) !== false;
        }
        return false;
    }

    public static function create_value_with_unit($value, $unit) {
        return $value . $unit;
    }
    
}


https://fonts.googleapis.com/css2?family=Alegreya:ital,wght@0,400..900;1,400..900&family=Radio+Canada+Big:ital,wght@0,400..700;1,400..700&display=swap