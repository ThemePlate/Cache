<?php

/**
 * Convenient fragment caching methods
 *
 * @package ThemePlate
 * @since 0.1.0
 */

namespace ThemePlate;

use Error;
use ThemePlate\Cache\CacheManager;
use ThemePlate\Process\Tasks;

class Cache {

	private static ?CacheManager $manager = null;
	private static ?Tasks $tasks          = null;

	public static function __callStatic( $name, $arguments ) {

		if ( ! self::$manager instanceof CacheManager ) {
			self::$manager = new CacheManager( self::$tasks );
		}

		if ( method_exists( self::$manager, $name ) ) {
			return call_user_func_array( array( self::$manager, $name ), $arguments );
		}

		throw new Error( 'Call to undefined method ' . __CLASS__ . '::$' . $name );

	}


	public static function processor( Tasks $tasks = null ): Tasks {

		if ( ! self::$tasks instanceof Tasks ) {
			self::$tasks = $tasks ?? new Tasks( __CLASS__ );
		}

		if ( ! wp_doing_ajax() ) {
			add_action( 'shutdown', array( self::$tasks, 'execute' ) );
		}

		return self::$tasks;

	}

}
