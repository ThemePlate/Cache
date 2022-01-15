<?php

/**
 * @package ThemePlate
 * @since   0.1.0
 */

namespace ThemePlate\Cache;

use ThemePlate\Tasks;

abstract class AbstractHandler {

	protected Storage $storage;
	protected ?Tasks $tasks;


	public function __construct( Storage $storage, Tasks $tasks = null ) {

		$this->storage = $storage;
		$this->tasks   = $tasks;

	}


	abstract public function set( string $key, array $data );


	public function background_update(): bool {

		if ( ! $this->tasks instanceof Tasks ) {
			return false;
		}

		// phpcs:ignore WordPress.Security.NonceVerification
		return isset( $_REQUEST['action'] ) && ( $this->tasks->get_identifier() === $_REQUEST['action'] );

	}


	public function action_update( string $key, array $data ): bool {

		if ( ! $this->tasks instanceof Tasks ) {
			return $this->set( $key, $data );
		}

		$this->tasks->add( array( $this, 'set' ), array( $key, $data ) );

		return false;

	}

}
