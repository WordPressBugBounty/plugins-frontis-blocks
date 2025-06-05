<?php

namespace FrontisBlocks\Core;

use FrontisBlocks\Traits\Singleton;
use FrontisBlocks\Config\BlockList;
use FrontisBlocks\Utils\Helper;

/**
 * Load google fonts.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FontLoader
{
	use Singleton;

	/**
	 * Google fonts to enqueue
	 *
	 * @access public
	 * @var array
	 */
	public static $gfonts      = [  ];
	private static $block_name = [  ];

	/**
	 * The Constructor.
	 */
	public function __construct()
	{
		// add_filter( 'render_block', [ $this, 'get_fonts_on_render_block' ], 10, 2 );
		add_action( 'wp_footer', [ $this, 'fonts_loader' ], 15 );
		// add_action( 'enqueue_block_assets', [ $this, 'fonts_loader' ], 10 );
	}

	/**
	 * Get Attributes on block render
	 *
	 * @since 4.0.2
	 * @access public
	 */
	public function get_fonts_on_render_block( $block_content, $block )
	{
		if ( isset( $block[ 'attrs' ] ) ) {
			$fonts        = self::get_fonts_family( $block[ 'attrs' ] );
			self::$gfonts = array_unique( array_merge( self::$gfonts, $fonts ) );
		}

		return $block_content;
	}

	/**
	 * Generate Font family from Attributes
	 *
	 * @since 4.0.0
	 * @access public
	 */
	public static function get_fonts_family( $attributes )
	{
		$keys             = preg_grep( '/^(\w+)FontFamily/i', array_keys( $attributes ), 0 );
		$googleFontFamily = [  ];
		foreach ( $keys as $key ) {
			$googleFontFamily[ $attributes[ $key ] ] = $attributes[ $key ];
		}
		return $googleFontFamily;
	}

	/**
	 * Load fonts.
	 *
	 * @since 4.0.0
	 * @access public
	 */
	public function fonts_loader()
	{
		$googleFont = true;

		if ( 'false' !== $googleFont ) {
			$fonts = self::$gfonts;
			Helper::load_google_font( $fonts );
		}
	}
}
