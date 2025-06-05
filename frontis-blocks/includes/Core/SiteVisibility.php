<?php

namespace FrontisBlocks\Core;

use FrontisBlocks\Utils\Helper;
use FrontisBlocks\Traits\Singleton;

/**
 * SiteVisibility
 *
 * @package FrontisBlocks
 */

 class SiteVisibility {
    use Singleton;

    /**
     * set_maintenance_mode
     *
     * @return void
     */
    public function set_maintenance_mode() {
        $maintenance_mode = Helper::get_option('fb_maintenance_mode', false);
        $maintenance_page_id = Helper::get_option('fb_maintenance_page_id', false);

        if ($maintenance_mode === true && $maintenance_page_id) {
            update_option('show_on_front', 'page');
            update_option('page_on_front', $maintenance_page_id);
        }
    }

    /**
     * set_coming_soon_mode
     *
     * @return void
     */
    public function set_coming_soon_mode() {
        $coming_soon_mode = Helper::get_option('fb_coming_soon_mode', false);
        $coming_soon_page_id = Helper::get_option('fb_coming_soon_page_id', false);

        if ($coming_soon_mode === true && $coming_soon_page_id) {
            update_option('show_on_front', 'page');
            update_option('page_on_front', $coming_soon_page_id);
        }
    }

 }