<?php

/**
 * Helper for convinient fragment caching
 *
 * @package ThemePlate
 * @since 0.1.0
 */

namespace ThemePlate;

class Cache {

	private static $prefix = 'tpc_';


	public static function get( $key ) {

		return get_transient( self::$prefix . $key );

	}


	public static function set( $key, $value, $expiration ) {

		return set_transient( self::$prefix . $key, $value, $expiration );

	}


	public static function delete( $key ) {

		return delete_transient( self::$prefix . $key );

	}

}
