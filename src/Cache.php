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
	public static $storage = array();


	public static function get( $key ) {

		$key = self::$prefix . $key;

		return self::$storage[ $key ] ?? get_transient( $key );

	}


	public static function set( $key, $value, $expiration = 0 ) {

		$key = self::$prefix . $key;

		self::$storage[ $key ] = $value . time();

		return set_transient( $key, $value, $expiration );

	}


	public static function delete( $key ) {

		$key = self::$prefix . $key;

		if ( array_key_exists( $key, self::$storage ) ) {
			unset( self::$storage[ $key ] );
		}

		return delete_transient( $key );

	}

}
