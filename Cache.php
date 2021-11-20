<?php

/**
 * Convenient fragment caching methods
 *
 * @package ThemePlate
 * @since 0.1.0
 */

namespace ThemePlate;

class Cache {

	private static string $prefix = 'tpc_';
	private static array $storage = array();
	private static ?Tasks $tasks  = null;


	public static function get( string $key ) {

		return self::$storage[ $key ] ?? get_transient( $key );

	}


	public static function set( string $key, $value, int $expiration = 0 ): bool {

		self::$storage[ $key ] = $value;

		return set_transient( $key, $value, $expiration );

	}


	public static function delete( string $key ): bool {

		if ( array_key_exists( $key, self::$storage ) ) {
			unset( self::$storage[ $key ] );
		}

		if ( array_key_exists( $key . '_saved', self::$storage ) ) {
			unset( self::$storage[ $key . '_saved' ] );
		}

		return (bool) ( delete_transient( $key ) | delete_transient( $key . '_saved' ) );

	}


	public static function remember( string $key, callable $callback, int $expiration = 0 ) {

		$value = self::get_data( $key );

		if ( false === $value ) {
			$value = self::set_data( $key, compact( 'expiration', 'callback' ) );
		}

		return $value;

	}


	public static function forget( string $key, $default = null ) {

		$value = self::get( $key );

		if ( false !== $value ) {
			delete_option( self::$prefix . $key );
			self::delete( $key );

			return $value;
		}

		return $default;

	}


	public static function file( string $key, string $path ) {

		$value = self::get_file( $key, $path );

		if ( false === $value ) {
			$time  = @filemtime( $path ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
			$value = self::set_file( $key, compact( 'path', 'time' ) );
		}

		return $value;

	}


	public static function processor(): Tasks {

		if ( ! self::$tasks instanceof Tasks ) {
			self::$tasks = new Tasks( __CLASS__ );
		}

		if ( ! defined( 'DOING_AJAX' ) ) {
			add_action( 'shutdown', array( self::$tasks, 'execute' ) );
		}

		return self::$tasks;

	}


	private static function get_data( string $key ) {

		$data = get_option( self::$prefix . $key );

		if ( false !== $data && ! self::background_update() && time() > $data['timeout'] ) {
			$data['value'] = self::action_update( 'set_data', array( $key, $data ) ) ?? $data['value'];
		}

		return $data['value'] ?? self::get( $key );

	}


	public static function set_data( string $key, array $data ) {

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


	private static function get_file( string $key, string $path ) {

		$value = self::get( $key );

		if ( false !== $value && ! self::background_update() ) {
			$time = @filemtime( $path ); // phpcs:ignore WordPress.PHP.NoSilencedErrors

			if ( self::get( $key . '_saved' ) < $time ) {
				$value = self::action_update( 'set_file', array( $key, compact( 'path', 'time' ) ) ) ?? $value;
			}
		}

		return $value;

	}


	public static function set_file( string $key, array $file ) {

		$value = @file_get_contents( $file['path'] ); // phpcs:ignore WordPress.PHP.NoSilencedErrors

		if ( $value ) {
			self::set( $key . '_saved', $file['time'] );
			self::set( $key, $value );
		}

		return $value;

	}


	private static function background_update(): bool {

		if ( ! self::$tasks instanceof Tasks ) {
			return false;
		}

		// phpcs:ignore WordPress.Security.NonceVerification
		return isset( $_REQUEST['action'] ) && self::$tasks->get_identifier() === $_REQUEST['action'];

	}


	private static function action_update( string $method, array $args ) {

		if ( self::$tasks instanceof Tasks ) {
			self::$tasks->add( array( __CLASS__, $method ), $args );
		} else {
			return call_user_func_array( array( __CLASS__, $method ), $args );
		}

		return null;

	}

}
