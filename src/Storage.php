<?php

/**
 * @package ThemePlate
 * @since   0.1.0
 */

namespace ThemePlate\Cache;

class Storage {

	public const PREFIX = 'tpc_';
	private array $collection;


	public function get( string $key, bool $data = false ) {

		if ( $data ) {
			$key = self::PREFIX . $key;
		}

		return $this->collection[ $key ] ?? get_option( $key );

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


	public function delete( string $key ): bool {

		if ( array_key_exists( $key, $this->collection ) ) {
			unset( $this->collection[ $key ] );
		}

		if ( array_key_exists( self::PREFIX . $key, $this->collection ) ) {
			unset( $this->collection[ self::PREFIX . $key ] );
		}

		return (bool) ( delete_option( $key ) | delete_option( self::PREFIX . $key ) );

	}

}
