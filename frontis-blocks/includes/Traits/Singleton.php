<?php


namespace FrontisBlocks\Traits;

trait Singleton{

    protected static $instances = array();


    public static function get_instance( ...$args ) {
		if ( ! isset( self::$instances[ static::class ] ) ) {
			self::$instances[ static::class ] = ! empty( $args ) ? new static( ...$args ) : new static();
		}

		return self::$instances[ static::class ];
	}

    protected function __construct( ...$args ) {}
}