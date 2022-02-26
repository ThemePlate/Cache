<?php

/**
 * @package ThemePlate
 * @since   0.1.0
 */

namespace ThemePlate\Cache\Handlers;

class FileHandler extends AbstractHandler {

	public function get( string $key, string $path ) {

		$value = $this->storage->get( $key );

		if ( false !== $value && ! $this->background_update() ) {
			$time = @filemtime( $path ); // phpcs:ignore WordPress.PHP.NoSilencedErrors

			if ( $this->storage->get( $key, true ) < $time ) {
				$value = $this->action_update( $key, compact( 'path', 'time' ) ) ?: $value;
			}
		}

		return $value;

	}


	public function set( string $key, array $data ) {

		$value = @file_get_contents( $data['path'] ); // phpcs:ignore WordPress.PHP.NoSilencedErrors

		if ( false !== $value ) {
			$this->storage->set( $key, $data['time'], true );
			$this->storage->set( $key, $value );
		}

		return $value;

	}

}
