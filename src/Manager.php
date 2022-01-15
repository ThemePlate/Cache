<?php

/**
 * @package ThemePlate
 * @since   0.1.0
 */

namespace ThemePlate\Cache;

use ThemePlate\Tasks;

class Manager {

	private Storage $storage;
	private ?Tasks $tasks;


	public function __construct( Tasks $tasks = null ) {

		$this->storage = new Storage();
		$this->tasks   = $tasks;

	}


	public function remember( string $key, callable $callback, int $expiration = 0 ) {

		$handler = new DataHandler( $this->storage, $this->tasks );
		$value   = $handler->get( $key );

		if ( false === $value ) {
			$value = $handler->set( $key, compact( 'expiration', 'callback' ) );
		}

		return $value;

	}


	public function forget( string $key, $default = null ) {

		$value = $this->storage->get( $key );

		if ( false !== $value ) {
			$this->storage->delete( $key );
			$this->storage->delete( $key, true );

			return $value;
		}

		return $default;

	}


	public function file( string $key, string $path ) {

		$handler = new FileHandler( $this->storage, $this->tasks );
		$value   = $handler->get( $key, $path );

		if ( false === $value ) {
			$time  = @filemtime( $path ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
			$value = $handler->set( $key, compact( 'path', 'time' ) );
		}

		return $value;

	}

}
