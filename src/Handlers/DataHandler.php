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

		if ( ! $this->background_update() && time() > $data['timeout'] ) {
			$data['value'] = $this->action_update( $key, $data ) ?: $data['value'];
		}

		return $data['value'] ?? $this->storage->get( $key );

	}


	public function set( string $key, array $data ) {

		$data['value'] = $data['callback']();

		if ( ! is_wp_error( $data['value'] ) ) {
			if ( ! is_object( $data['callback'] ) ) {
				$data['timeout'] = time() + $data['expiration'];

				$this->storage->set( $key, $data, true );
			}

			$this->storage->set( $key, $data['value'] );
		}

		return $data['value'];

	}

}
