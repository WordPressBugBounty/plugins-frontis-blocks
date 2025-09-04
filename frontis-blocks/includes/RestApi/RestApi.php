<?php
namespace FrontisBlocks\RestApi;

use WP_REST_Response;
use WP_Error;
use WP_REST_Request;
use FrontisBlocks\Traits\Singleton;
use FrontisBlocks\Utils\Helper;
use FrontisBlocks\RestApi\Blocks\PostGrid;


class RestApi {
    use Singleton;

    /**
     * Constructor.
     */
    public function __construct() {
        add_action('init', [$this, 'fb_init_rest_api_blocks']);
    }

    /**
     * Initialize the REST API blocks.
     */
    public function fb_init_rest_api_blocks() {
        PostGrid::get_instance();
    }
}