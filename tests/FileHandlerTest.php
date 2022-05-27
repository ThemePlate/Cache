<?php

/**
 * @package ThemePlate
 */

namespace Tests;

use ThemePlate\Cache\Handlers\FileHandler;
use ThemePlate\Cache\Storages\OptionsStorage;
use ThemePlate\Cache\Storages\StorageInterface;
use WP_UnitTestCase;

class FileHandlerTest extends WP_UnitTestCase {
	private FileHandler $handler;

	protected function setUp(): void {
		$this->storage = new OptionsStorage();
		$this->handler = new FileHandler( $this->storage );
	}

	public function test_get_with_nothing_known_yet(): void {
		$this->assertFalse( $this->handler->get( 'unknown', '' ) );
	}

	public function test_get_with_forced_refresh(): void {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		global $_REQUEST;

		$_REQUEST[ StorageInterface::PREFIX . 'refresh' ] = 'unknown';

		$this->assertFalse( $this->handler->get( 'unknown', '' ) );
	}

	public function test_get_with_action_update(): void {
		$this->assertIsString( $this->handler->get( 'blogdescription', WP_CONTENT_DIR . '/db.php' ) );
	}

	public function test_set_with_unreadable_content(): void {
		$path = ABSPATH . 'README.md';
		$time = @filemtime( $path ); // phpcs:ignore WordPress.PHP.NoSilencedErrors

		$this->assertFalse( $this->handler->set( 'random_key', compact( 'path', 'time' ) ) );
		$this->assertFalse( $this->storage->get( 'random_key', true ) );
		$this->assertFalse( $this->storage->get( 'random_key' ) );
	}

	public function test_set_with_readable_content(): void {
		$path = ABSPATH . 'license.txt';
		$time = @filemtime( $path ); // phpcs:ignore WordPress.PHP.NoSilencedErrors

		$this->assertIsString( $this->handler->set( 'random_key', compact( 'path', 'time' ) ) );
		$this->assertSame( file_get_contents( $path ), $this->storage->get( 'random_key' ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$this->assertSame( $time, $this->storage->get( 'random_key', true ) );
	}
}
