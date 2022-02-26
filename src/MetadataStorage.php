<?php

/**
 * @package ThemePlate
 * @since   0.1.0
 */

namespace ThemePlate\Cache;

class MetadataStorage extends AbstractStorage {

	protected string $meta_type;
	protected int $object_id = 0;


	public function __construct( string $meta_type ) {

		$this->meta_type = $meta_type;

	}


	public function get( string $key, bool $data = false ) {

		$key   = $this->transform( $key, $data );
		$value = get_metadata( $this->meta_type, $this->object_id, $key, true );
		$value = empty( $value ) ? false : $value;

		return $this->collection[ $key ] ?? $value;

	}


	public function set( string $key, $value, bool $data = false ): bool {

		$key = $this->transform( $key, $data );

		$this->collection[ $key ] = $value;

		return update_metadata( $this->meta_type, $this->object_id, $key, $value );

	}


	public function delete( string $key, bool $data = false ): bool {

		$key = $this->transform( $key, $data );

		if ( array_key_exists( $key, $this->collection ) ) {
			unset( $this->collection[ $key ] );
		}

		return delete_metadata( $this->meta_type, $this->object_id, $key );

	}

}
