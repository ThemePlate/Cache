<?php

/**
 * @package ThemePlate
 * @since   0.1.0
 */

namespace ThemePlate\Cache\Handlers;

use ThemePlate\Cache\Storages\AbstractStorage;
use ThemePlate\Process\Tasks;

abstract class AbstractHandler {

	protected AbstractStorage $storage;
	protected ?Tasks $tasks;


	public function __construct( AbstractStorage $storage, Tasks $tasks = null ) {

		$this->storage = $storage;
		$this->tasks   = $tasks;

	}


	/**
	 * @return mixed
	 */
	abstract public function set( string $key, array $data );


	public function background_update(): bool {

		if ( ! $this->tasks instanceof Tasks ) {
			return false;
		}

		// phpcs:ignore WordPress.Security.NonceVerification
		return isset( $_REQUEST['action'] ) && ( $this->tasks->get_identifier() === $_REQUEST['action'] );

	}


	public function action_update( string $key, array $data ) {

		if ( ! $this->tasks instanceof Tasks ) {
			return $this->set( $key, $data );
		}

		$storage = get_class( $this->storage );
		$pointer = $this->storage->pointer();

		$this->tasks->add( array( static::class, 'update' ), array( $storage, $pointer, $key, $data ) );

		return false;

	}


	public static function update( string $storage, int $pointer, string $key, array $data ) {

		$handler = static::class;
		/** @var AbstractStorage $storage */
		$storage = new $storage();

		$storage->point( $pointer );

		return ( new $handler( $storage ) )->set( $key, $data );

	}

}
