<?php

/**
 * @package ThemePlate
 * @since   0.1.0
 */

namespace ThemePlate\Cache\Handlers;

class DataHandler extends AbstractHandler {

	public function get( string $key ) {

		$data = $this->storage->get( $key, true );

		if ( false === $data ) {
			return false;
		}

		$value = false;

		if ( ! $this->background_update() && time() > $data['timeout'] ) {
			$value = $this->action_update( $key, $data );
		}

		return $value ?: $this->storage->get( $key );

	}


	public function set( string $key, array $data ) {

		$value = $data['callback']();

		if ( ! is_wp_error( $value ) ) {
			if ( ! is_object( $data['callback'] ) ) {
				$data['timeout'] = time() + $data['expiration'];

				$this->storage->set( $key, $data, true );
			}

			$this->storage->set( $key, $value );
		}

		return $value;

	}

}
