<?php

namespace FrontisBlocks\Admin;


if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Settings {

    private static $instance;

    public static function get_instance()
    {
        if ( null === static::$instance ) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    public static function save( $key, $value = '' )
    {
        return update_option( $key, $value );
    }

    public static function get( $key, $default = '' )
    {
        return get_option( $key, $default );
    }

    public static function save_fb_settings($key, $value = '')
    {
        $settings = get_option('frontis_blocks_settings', []);

        $prev_value = null;
        if ( isset( $settings[ $key ] ) ) {
            $prev_value = $settings[ $key ];
        }
        if ( empty( $value ) ) {
            unset( $settings[ $key ] );
        } else {
            $settings[ $key ] = $value;
        }

        return update_option( 'frontis_blocks_settings', $settings );
    }

    public static function reset_settings($key)
    {
        $settings = get_option('frontis_blocks_settings', []);
        $prev_value = null;
        if (isset($settings[$key])) {
            $prev_value = $settings[$key];
            unset($settings[$key]);
        }
        
        return update_option('frontis_blocks_settings', $settings);
    }

    public static function set_transient($key, $value, $expiration = null)
    {
        if ($expiration === null) {
            $expiration = HOUR_IN_SECONDS * 6;
        }
        return set_transient($key, $value, $expiration);
    }

    public static function get_transient($key, $default = false)
    {
        $value = get_transient($key);
        return $value !== false ? $value : $default;
    }

    public static function save_blocks($blocks)
    {
        return update_option('frontis_blocks_active_blocks', $blocks);
    }

    public static function get_blocks()
    {
        return get_option('frontis_blocks_active_blocks', []);
    }

    public static function add_block($slug)
    {
        $blocks = self::get_blocks();
        if (!in_array($slug, $blocks)) {
            $blocks[] = $slug;
            return self::save_blocks($blocks);
        }
        return false;
    }

    public static function remove_block($slug)
    {
        $blocks = self::get_blocks();
        $key = array_search($slug, $blocks);
        if ($key !== false) {
            unset($blocks[$key]);
            return self::save_blocks(array_values($blocks));
        }
        return false;
    }

}
