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

		if ( $data ) {
			$key = self::PREFIX . $key;
		}

		return $this->collection[ $key ] ?? get_metadata( $this->meta_type, $this->object_id, $key, true );

	}


	public function set( string $key, $value, bool $data = false ): bool {

		if ( $data ) {
			$key = self::PREFIX . $key;
		}

		$this->collection[ $key ] = $value;

		return update_metadata( $this->meta_type, $this->object_id, $key, $value );

	}


	public function delete( string $key, bool $data = false ): bool {

		if ( $data ) {
			$key = self::PREFIX . $key;
		}

		if ( array_key_exists( $key, $this->collection ) ) {
			unset( $this->collection[ $key ] );
		}

		return delete_metadata( $this->meta_type, $this->object_id, $key );

	}

}
