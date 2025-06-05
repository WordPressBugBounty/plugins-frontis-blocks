<?php

namespace FrontisBlocks\Core;

use FrontisBlocks\Traits\Singleton;

defined('ABSPATH') || exit;

class FbDatabase
{
    use Singleton;

    public static function get_option($option_name) {
    	global $wpdb;

    	$option_value = $wpdb->get_var(
            $wpdb->prepare("SELECT option_value FROM {$wpdb->options} WHERE option_name = %s", $option_name)
        );

        return $option_value;
    }
}
