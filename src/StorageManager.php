<?php

/**
 * @package ThemePlate
 * @since   0.1.0
 */

namespace ThemePlate\Cache;

class StorageManager {

	private string $type = 'options';
	private MetadataStorage $metadata;
	private OptionsStorage $options;


	public function __construct() {

		$this->metadata = new MetadataStorage( 'post' );
		$this->options  = new OptionsStorage();

	}

	public function get(): AbstractStorage {

		return $this->{$this->type};

	}


	public function set( $type ): void {

		if ( is_string( $type ) ) {
			$this->type = 'options';
		} else {
			$this->type = 'metadata';

			$this->metadata->point( $type );
		}

	}

}
