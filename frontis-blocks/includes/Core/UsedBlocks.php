<?php

namespace FrontisBlocks\Core;

use FrontisBlocks\Traits\Singleton;

class UsedBlocks {

    use Singleton;

    public function get_used_frontis_blocks() {

		$args = [
			'numberposts' => -1,
			'post_type'   => ['post', 'page', 'wp_block', 'wp_area']
		];


		$postc = get_posts($args);
		$frontis_blocks = [];


		foreach ($postc as $post) {

			if (has_blocks($post->post_content)) {

				$blocks = parse_blocks($post->post_content);
				foreach ($blocks as $block) {

					if (strpos($block['blockName'], 'frontis') === 0) {

						$frontis_blocks[] = $block['blockName'];
					}
					// Check inner blocks recursively
					$frontis_blocks = array_merge($frontis_blocks, $this->get_inner_frontis_blocks($block['innerBlocks']));
				}
			}
		}

		// Return unique frontis block names
		// return array_unique($frontis_blocks);
		error_log(print_r(array_unique($frontis_blocks), true));
	}

    // Function to get inner blocks that start with "frontis/"
    public function get_inner_frontis_blocks($innerBlocks) {
        $frontis_blocks = [];
        foreach ($innerBlocks as $innerBlock) {
			if (strpos($innerBlock['blockName'], 'frontis/') === 0) {
				$frontis_blocks[] = $innerBlock['blockName'];
			}
			// Recursively check for inner blocks
			$frontis_blocks = array_merge($frontis_blocks, $this->get_inner_frontis_blocks($innerBlock['innerBlocks']));
		}
		return $frontis_blocks;
	}
}
