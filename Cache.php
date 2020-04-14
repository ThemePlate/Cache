<?php

/**
 * Convenient fragment caching methods
 *
 * @package ThemePlate
 * @since 0.1.0
 */

namespace ThemePlate;

class Cache {

	private static $prefix  = 'tpc_';
	private static $storage = array();
	private static $tasks;


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

		$value = self::get_data( $key );

		if ( false === $value ) {
			$value = self::set_data( $key, compact( 'expiration', 'callback' ) );
		}

		return $value;

	}


	public static function forget( $key, $default = null ) {

		$value = self::get( $key );

		if ( false !== $value ) {
			delete_option( self::$prefix . $key );
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
			self::set_file( $key, $value, compact( 'path', 'f_time' ) );
		}

		return $value;

	}


	public static function processor() {

		if ( ! self::$tasks instanceof Tasks ) {
			self::$tasks = new Tasks( __CLASS__ );
		}

		if ( ! defined( 'DOING_AJAX' ) ) {
			add_action( 'shutdown', array( self::$tasks, 'execute' ) );
		}

		return self::$tasks;

	}


	private static function get_data( $key ) {

		$data = get_option( self::$prefix . $key );

		if ( false !== $data && ! self::background_update() ) {
			if ( time() > $data['timeout'] ) {
				if ( self::$tasks instanceof Tasks ) {
					self::$tasks->add( array( Cache::class, 'set_data' ), array( $key, $data ) );
				} else {
					$data['value'] = self::set_data( $key, $data );
				}
			}
		}

		return $data['value'] ?? self::get( $key );

	}


	public static function set_data( $key, $data ) {

		$data['value'] = $data['callback']();

		if ( ! is_wp_error( $data['value'] ) ) {
			if ( ! is_object( $data['callback'] ) ) {
				$data['timeout'] = time() + $data['expiration'];

				update_option( self::$prefix . $key, $data, false );
			}

			self::set( $key, $data['value'], $data['expiration'] );
		}

		return $data['value'];

	}


	private static function set_file( $key, &$value, $file ) {

		if ( ! self::background_update() ) {
			if ( false === $value || ! self::$tasks instanceof Tasks ) {
				$value = self::update_file( $key, $file );
			} else {
				self::$tasks->add( array( Cache::class, 'update_file' ), array( $key, $file ) );
			}
		}

	}


	public static function update_file( $key, $file ) {

		$value = @file_get_contents( $file['path'] );

		if ( $value ) {
			self::set( $key . '_saved', $file['f_time'] );
			self::set( $key, $value );
		}

		return $value;

	}


	private static function background_update() {

		if ( ! self::$tasks instanceof Tasks ) {
			return false;
		}

		return isset( $_REQUEST['action'] ) && self::$tasks->get_identifier() === $_REQUEST['action'];

	}

}
