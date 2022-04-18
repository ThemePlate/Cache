<?php

/**
 * @package ThemePlate
 * @since   0.1.0
 */

namespace ThemePlate\Cache\Storages;

abstract class AbstractStorage {

	public const PREFIX = 'tcs_';
	protected array $collection;


	abstract public function get( string $key, bool $data = false );


	abstract public function set( string $key, $value, bool $data = false ): bool;


	abstract public function delete( string $key, bool $data = false ): bool;


	abstract public function point( int $id ): void;


	abstract public function pointer(): int;


	protected function transform( string $field_key, bool $is_data ): string {

		return ( $is_data ? self::PREFIX : '' ) . $field_key;

	}

}
