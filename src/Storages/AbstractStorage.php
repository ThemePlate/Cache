<?php

/**
 * @package ThemePlate
 * @since   0.1.0
 */

namespace ThemePlate\Cache\Storages;

abstract class AbstractStorage implements StorageInterface {

	protected array $collection = array();


	protected function transform( string $field_key, bool $is_data ): string {

		return ( $is_data ? self::PREFIX : '' ) . $field_key;

	}

}
