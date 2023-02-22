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

/**
 * @method static false|mixed remember( string $key, callable $callback, int $expiration = 0 )
 * @method static mixed|null forget( string $key, $default = null )
 * @method static false|string file( string $key, string $path )
 * @method static CacheManager assign( $field )
 * @method static CacheManager reset()
 */
class Cache {

	private static ?CacheManager $manager = null;
	private static ?Tasks $tasks          = null;

	public static function __callStatic( string $name, array $arguments ) {

		if ( ! self::$manager instanceof CacheManager ) {
			self::$manager = new CacheManager( self::$tasks );
		}

		if ( method_exists( self::$manager, $name ) ) {
			return call_user_func_array( array( self::$manager, $name ), $arguments );
		}

		throw new Error( 'Call to undefined method ' . __CLASS__ . '::' . $name . '()' );

	}


	/**
	 * Support for soft-expiration; `Cache::remember`* and `Cache::file` updates in the background
	 * >\**Except for using anonymous function as callback (closure)*
	 */
	public static function processor( Tasks $tasks = null ): ?Tasks {

		if ( ! self::$tasks instanceof Tasks && class_exists( Tasks::class ) ) {
			self::$tasks = $tasks ?? new Tasks( __CLASS__ );
		}

		return self::$tasks;

	}

}
