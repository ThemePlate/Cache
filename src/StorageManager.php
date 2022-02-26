<?php

/**
 * @package ThemePlate
 * @since   0.1.0
 */

namespace ThemePlate\Cache;

use ThemePlate\Cache\Storages\AbstractStorage;
use ThemePlate\Cache\Storages\OptionsStorage;
use ThemePlate\Cache\Storages\PostMetaStorage;
use ThemePlate\Cache\Storages\TermMetaStorage;
use ThemePlate\Cache\Storages\UserMetaStorage;

class StorageManager {

	private string $type = 'options';
	private PostMetaStorage $postmeta;
	private TermMetaStorage $termmeta;
	private UserMetaStorage $usermeta;
	private OptionsStorage $options;


	public function __construct() {

		$this->postmeta = new PostMetaStorage();
		$this->termmeta = new TermMetaStorage();
		$this->usermeta = new UserMetaStorage();
		$this->options  = new OptionsStorage();

	}

	public function get(): AbstractStorage {

		return $this->{$this->type};

	}


	public function set( $field ): void {

		$decoded    = $this->decode( $field );
		$this->type = $decoded['type'];

		$this->{$decoded['type']}->point( $decoded['id'] );

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
