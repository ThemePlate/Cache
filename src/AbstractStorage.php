<?php

/**
 * @package ThemePlate
 * @since   0.1.0
 */

namespace ThemePlate\Cache;

abstract class AbstractStorage {

	public const PREFIX = 'tpc_';
	protected array $collection;


	abstract public function get( string $key, bool $data = false );


	abstract public function set( string $key, $value, bool $data = false ): bool;


	abstract public function delete( string $key, bool $data = false ): bool;

}
