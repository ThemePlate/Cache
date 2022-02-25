<?php

/**
 * @package ThemePlate
 * @since   0.1.0
 */

namespace ThemePlate\Cache;

class OptionsStorage extends AbstractStorage {

	public function get( string $key, bool $data = false ) {

		if ( $data ) {
			$key = self::PREFIX . $key;
		}

		return $this->collection[ $key ] ?? get_option( $key, false );

	}


	public function set( string $key, $value, bool $data = false ): bool {

		$autoload = 'yes';

		if ( $data ) {
			$key      = self::PREFIX . $key;
			$autoload = 'no';
		}

		$this->collection[ $key ] = $value;

		return update_option( $key, $value, $autoload );

	}


	public function delete( string $key, bool $data = false ): bool {

		if ( $data ) {
			$key = self::PREFIX . $key;
		}

		if ( array_key_exists( $key, $this->collection ) ) {
			unset( $this->collection[ $key ] );
		}

		return delete_option( $key );

	}

}
