<?php

/**
 * @package ThemePlate
 * @since   0.1.0
 */

namespace ThemePlate\Cache;

use ThemePlate\Tasks;

class Manager {

	private string $prefix;
	private array $storage;
	private ?Tasks $tasks;


	public function __construct( string $prefix, Tasks $tasks = null ) {

		$this->prefix = $prefix;
		$this->tasks  = $tasks;

	}


	private function get( string $key ) {

		return $this->storage[ $key ] ?? get_option( $key );

	}


	private function set( string $key, $value ): bool {

		$this->storage[ $key ] = $value;

		return update_option( $key, $value );

	}


	private function delete( string $key ): bool {

		if ( array_key_exists( $key, $this->storage ) ) {
			unset( $this->storage[ $key ] );
		}

		if ( array_key_exists( $key . '_saved', $this->storage ) ) {
			unset( $this->storage[ $key . '_saved' ] );
		}

		return (bool) ( delete_option( $key ) | delete_option( $key . '_saved' ) );

	}


	public function remember( string $key, callable $callback, int $expiration = 0 ) {

		$value = $this->get_data( $key );

		if ( false === $value ) {
			$value = $this->set_data( $key, compact( 'expiration', 'callback' ) );
		}

		return $value;

	}


	public function forget( string $key, $default = null ) {

		$value = $this->get( $key );

		if ( false !== $value ) {
			$this->delete( $this->prefix . $key );
			$this->delete( $key );

			return $value;
		}

		return $default;

	}


	public function file( string $key, string $path ) {

		$value = $this->get_file( $key, $path );

		if ( false === $value ) {
			$time  = @filemtime( $path ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
			$value = $this->set_file( $key, compact( 'path', 'time' ) );
		}

		return $value;

	}


	private function get_data( string $key ) {

		$data = $this->get( $this->prefix . $key );

		if ( false !== $data && ! $this->background_update() && time() > $data['timeout'] ) {
			$data['value'] = $this->action_update( 'set_data', array( $key, $data ) ) ?? $data['value'];
		}

		return $data['value'] ?? $this->get( $key );

	}


	public function set_data( string $key, array $data ) {

		$data['value'] = $data['callback']();

		if ( ! is_wp_error( $data['value'] ) ) {
			if ( ! is_object( $data['callback'] ) ) {
				$data['timeout'] = time() + $data['expiration'];

				$this->set( $this->prefix . $key, $data );
			}

			$this->set( $key, $data['value'] );
		}

		return $data['value'];

	}


	private function get_file( string $key, string $path ) {

		$value = $this->get( $key );

		if ( false !== $value && ! $this->background_update() ) {
			$time = @filemtime( $path ); // phpcs:ignore WordPress.PHP.NoSilencedErrors

			if ( $this->get( $key . '_saved' ) < $time ) {
				$value = $this->action_update( 'set_file', array( $key, compact( 'path', 'time' ) ) ) ?? $value;
			}
		}

		return $value;

	}


	public function set_file( string $key, array $file ) {

		$value = @file_get_contents( $file['path'] ); // phpcs:ignore WordPress.PHP.NoSilencedErrors

		if ( $value ) {
			$this->set( $key . '_saved', $file['time'] );
			$this->set( $key, $value );
		}

		return $value;

	}


	private function background_update(): bool {

		if ( ! $this->tasks instanceof Tasks ) {
			return false;
		}

		// phpcs:ignore WordPress.Security.NonceVerification
		return isset( $_REQUEST['action'] ) && ( $this->tasks->get_identifier() === $_REQUEST['action'] );

	}


	private function action_update( string $method, array $args ) {

		if ( $this->tasks instanceof Tasks ) {
			$this->tasks->add( array( $this, $method ), $args );
		} else {
			return call_user_func_array( array( $this, $method ), $args );
		}

		return null;

	}
}
