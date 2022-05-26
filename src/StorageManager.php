<?php

/**
 * @package ThemePlate
 * @since   0.1.0
 */

namespace ThemePlate\Cache;

use ThemePlate\Cache\Storages\AbstractStorage;
use ThemePlate\Cache\Storages\MetadataStorage;
use ThemePlate\Cache\Storages\OptionsStorage;

class StorageManager {

	private string $type;
	private MetadataStorage $postmeta;
	private MetadataStorage $termmeta;
	private MetadataStorage $usermeta;
	private OptionsStorage $options;


	public function __construct() {

		$this->postmeta = new MetadataStorage( 'post' );
		$this->termmeta = new MetadataStorage( 'term' );
		$this->usermeta = new MetadataStorage( 'user' );
		$this->options  = new OptionsStorage();

	}


	public function current(): string {

		return $this->type ?? 'options';

	}


	public function get(): AbstractStorage {

		return $this->{$this->current()};

	}


	public function set( $field ): void {

		$decoded    = $this->decode( $field );
		$this->type = $decoded['type'];

		/** @var AbstractStorage $storage */
		$storage = $this->{$decoded['type']};

		$storage->point( $decoded['id'] );

	}


	private function decode( $field ): array {

		$type = 'options';
		$id   = 0;

		if ( is_numeric( $field ) ) {
			$type = 'post';
			$id   = $field;
		} elseif ( is_string( $field ) ) {
			$i = strrpos( $field, '_' );

			if ( $i > 0 ) {
				$type = substr( $field, 0, $i );
				$id   = substr( $field, $i + 1 );
			}
		}

		if ( 'options' !== $type ) {
			$type .= 'meta';
		}

		if ( ! property_exists( $this, $type ) ) {
			$type = 'options';
		}

		$id = absint( $id );

		return compact( 'type', 'id' );

	}

}
