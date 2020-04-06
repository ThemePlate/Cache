<?php

/**
 * Convenient fragment caching methods
 *
 * @package ThemePlate
 * @since 0.1.0
 */

namespace ThemePlate;

class Cache {

	private static $storage = array();


	public static function get( $key ) {

		return self::$storage[ $key ] ?? get_transient( $key );

	}


	public static function set( $key, $value, $expiration = 0 ) {

		self::$storage[ $key ] = $value;

		return set_transient( $key, $value, (int) $expiration );

	}


	public static function delete( $key ) {

		if ( array_key_exists( $key, self::$storage ) ) {
			unset( self::$storage[ $key ] );
		}

		if ( array_key_exists( $key . '_saved', self::$storage ) ) {
			unset( self::$storage[ $key . '_saved' ] );
		}

		return (bool) ( delete_transient( $key ) | delete_transient( $key . '_saved' ) );

	}


	public static function remember( $key, $callback, $expiration = 0 ) {

		$value = self::get( $key );

		if ( false !== $value ) {
			return $value;
		}

		$value = $callback();

		if ( ! is_wp_error( $value ) ) {
			self::set( $key, $value, $expiration );
		}

		return $value;

	}


	public static function forget( $key, $default = null ) {

		$value = self::get( $key );

		if ( false !== $value ) {
			self::delete( $key );

			return $value;
		}

		return $default;

	}


	public static function file( $key, $path ) {

		$serve = true;
		$value = self::get( $key );

		if ( false === $value ) {
			$serve = false;
		}

		$s_time = self::get( $key . '_saved' );
		$f_time = @filemtime( $path );

		if ( $s_time < $f_time ) {
			$serve = false;
		}

		if ( ! $serve ) {
			$value = @file_get_contents( $path );

			if ( $value ) {
				self::set( $key . '_saved', $f_time );
				self::set( $key, $value );
			}
		}

		return $value;

	}

}
