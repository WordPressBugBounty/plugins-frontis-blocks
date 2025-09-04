<?php

namespace FrontisBlocks\BackgroundProcess;
use FrontisBlocks\Assets\AssetsGenerator;
use FrontisBlocks\Utils\Helper;

defined('ABSPATH') || exit;

class AssetsGenerationProcess extends \WP_Background_Process {

    /**
	 * @var string
	 */
	protected $action = 'assets_generation_process';

    protected function task( $post_id ) {

        $modified_post = get_post($post_id);
        $post_content = $modified_post->post_content;

        // Get synced pattern contents
        $pattern_contents = Helper::check_patterns_used($modified_post->post_content);

        // Append pattern contents to post_content
        if (!empty($pattern_contents)) {
        	foreach ($pattern_contents as $pattern_content) {
        		$post_content .= "\n" . $pattern_content; // Append each pattern content
        	}

        	// Pass modified post object to generate_page_assets
        	$modified_post->post_content = $post_content; // Update post object
        }

        // if($post->post_name == 'home-2-2') {
        	$myfile = fopen($modified_post->post_name.".txt", "w") or die("Unable to open file!");
        	$txt = $post_content;
        	fwrite($myfile, $txt);
        	fclose($myfile);
        // }

        $pattern = "/frontis-blocks/i";
        update_post_meta($post_id, 'frontis_blocks_used', false);

        if(preg_match_all($pattern, $post->post_content) > 0) {
        	update_post_meta($post_id, 'frontis_blocks_used', true);
        }

        AssetsGenerator::get_instance()->generate_page_assets($post_id, $modified_post);
        $generatedPages[] = $post->post_name;

        return false;
	}

    /**
	 * Complete
	 *
	 * Override if applicable, but ensure that the below actions are
	 * performed, or, call parent::complete().
	 */
	protected function complete() {
		parent::complete();

		// Show notice to user or perform some other arbitrary task...
	}
}