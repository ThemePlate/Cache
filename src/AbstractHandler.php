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


	public function background_update(): bool {

		if ( ! $this->tasks instanceof Tasks ) {
			return false;
		}

		// phpcs:ignore WordPress.Security.NonceVerification
		return isset( $_REQUEST['action'] ) && ( $this->tasks->get_identifier() === $_REQUEST['action'] );

	}


	public function action_update( string $method, array $args ) {

		if ( $this->tasks instanceof Tasks ) {
			$this->tasks->add( array( $this, $method ), $args );
		} else {
			return call_user_func_array( array( $this, $method ), $args );
		}

		return null;

	}

}
