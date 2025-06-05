<?php

namespace FrontisBlocks\Core;

use FrontisBlocks\Traits\Singleton;
use FrontisBlocks\Config\BlockList;

defined('ABSPATH') || exit;

class InitialActivatedBlocks
{
    use Singleton;

    public static function blocks() {
        $blocks = BlockList::get_instance()->get_blocks();

        if($blocks) {
            $initial_active_blocks = [];
            foreach($blocks as $key => $single_block) {
                $initial_active_blocks[$key] = true;
            }

            $already_activated_blocks = get_option('fb_active_blocks');
            if(empty($already_activated_blocks)) {
                update_option('fb_active_blocks', $initial_active_blocks, true);
            }
        }
    }
}