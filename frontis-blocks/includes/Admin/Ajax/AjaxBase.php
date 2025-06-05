<?php 

namespace FrontisBlocks\Admin\Ajax;

/**
 * AjaxBase
 *
 * @package FrontisBlocks
 */
abstract class AjaxBase {
    /**
     * Ajax events prefix.
     *
     * @var string
     */
    protected $prefix = 'fb';

    /**
     * Error messages.
     *
     * @var array
     */
    protected $errors = [
        'invalid_nonce' => 'Invalid security token sent.',
        'no_permission' => 'You do not have permission to do that.',
        'invalid_data'  => 'Invalid data provided.',
    ];

    /**
     * Constructor.
     */
    public function __construct() {

    }

    /**
     * Initialize Ajax events.
     *
     * @return void
     */
    public function init_ajax_events($ajax_events) {
        if (!empty($ajax_events)) {
            foreach ($ajax_events as $ajax_event) {
                add_action('wp_ajax_' . $this->prefix . '_' . $ajax_event, array($this, $ajax_event));
            }
        }
    }

    /**
     * Get error message.
     *
     * @param string $key Error key.
     * @return string
     */
    protected function get_error_msg($key) {
        return isset($this->errors[$key]) ? $this->errors[$key] : __('An unknown error occurred.', 'frontis-blocks');
    }
}
