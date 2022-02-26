<?php

/**
 * @package ThemePlate
 * @since   0.1.0
 */

namespace ThemePlate\Cache;

use ThemePlate\Cache\Storages\AbstractStorage;
use ThemePlate\Cache\Storages\MetadataStorage;
use ThemePlate\Cache\Storages\OptionsStorage;
use ThemePlate\Cache\Storages\PostMetaStorage;
use ThemePlate\Cache\Storages\TermMetaStorage;
use ThemePlate\Cache\Storages\UserMetaStorage;

class StorageManager {

	private string $type = 'options';
	private MetadataStorage $metadata;
	private PostMetaStorage $postmeta;
	private TermMetaStorage $termmeta;
	private UserMetaStorage $usermeta;
	private OptionsStorage $options;


	public function __construct() {

		$this->postmeta = new PostMetaStorage();
		$this->termmeta = new TermMetaStorage();
		$this->usermeta = new UserMetaStorage();
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
