<?php

namespace FrontisBlocks\Forms;
use FrontisBlocks\Traits\Singleton;
use FrontisBlocks\Utils\Helper;
use FluentForm\App\Helpers\Helper as FluentFormHelper;

defined('ABSPATH') || exit;

class FluentForms
{
	use Singleton;

	public function __construct() {

	}

	/**
	 * Get form meta using FluentForm Helpers.
	 *
	 * @param mixed $id
	 *
	 * @return mixed
	 *
	 * @suppress PHP0413
	 */
	public static function get_form_meta( $id )
	{
		return FluentFormHelper::getFormMeta( $id, 'template_name' );
	}

	public static function check_enable()
	{
		$active_plugins = Helper::get_active_plugin_list();
		if ( in_array( 'fluentform/fluentform.php', $active_plugins ) ) {
			return true;
		}
		return false;
	}
	/**
	 * Get the list of FluentForm forms.
	 *
	 * @return array
	 */
	public static function form_list() {
		$cache_key = 'frontis_blocks_fluent_forms';
		$options = wp_cache_get($cache_key);
	
		if ($options === false) { // Cache miss, so we query the database
			$options = [];
			
			// Ensure FluentForm is active
			if (defined('FLUENTFORM')) {
				// Use FluentForm's internal API to fetch all forms
				$forms = \FluentForm\App\Models\Form::all(); // FluentForm API call
				
				// Default option for selecting a form
				$options[0]['value'] = '';
				$options[0]['label'] = __('Select a form', 'frontis-blocks');
				
				if ($forms) {
					foreach ($forms as $key => $form) {
						$options[$key + 1]['value'] = $form->id;
						$options[$key + 1]['label'] = $form->title;
						$options[$key + 1]['attr'] = self::get_form_meta($form->id);
					}
				}
	
				// Cache the results for 12 hours
				wp_cache_set($cache_key, $options, '', 12 * HOUR_IN_SECONDS);
			}
		}
	
		return $options;
	}	
	
}
