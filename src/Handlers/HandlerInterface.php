<?php

/**
 * @package ThemePlate
 * @since   0.1.0
 */

namespace ThemePlate\Cache\Handlers;

use ThemePlate\Cache\Storages\AbstractStorage;
use ThemePlate\Process\Tasks;

interface HandlerInterface {

	public function __construct( AbstractStorage $storage, ?Tasks $tasks = null );


	/**
	 * @param array<string, mixed> $data
	 * @return mixed
	 */
	public function set( string $key, array $data );


	/**
	 * @param array<string, mixed> $data
	 *
	 * @return mixed
	 */
	public static function update( string $storage, int $pointer, string $key, array $data );

}
