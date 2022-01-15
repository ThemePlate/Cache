<?php

/**
 * @package ThemePlate
 * @since   0.1.0
 */

namespace ThemePlate\Cache;

class Storage {

	private string $prefix;
	private array $collection;


	public function __construct( string $prefix ) {

		$this->prefix = $prefix;

	}


	public function get( string $key, bool $data = false ) {

		if ( $data ) {
			$key = $this->prefix . $key;
		}

		return $this->collection[ $key ] ?? get_option( $key );

	}


	public function set( string $key, $value, bool $data = false ): bool {

		$autoload = 'yes';

		if ( $data ) {
			$key      = $this->prefix . $key;
			$autoload = 'no';
		}

		$this->collection[ $key ] = $value;

		return update_option( $key, $value, $autoload );

	}


	public function delete( string $key ): bool {

		if ( array_key_exists( $key, $this->collection ) ) {
			unset( $this->collection[ $key ] );
		}

		if ( array_key_exists( $this->prefix . $key, $this->collection ) ) {
			unset( $this->collection[ $this->prefix . $key ] );
		}

		return (bool) ( delete_option( $key ) | delete_option( $this->prefix . $key ) );

	}

}
