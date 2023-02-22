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
		if ( 'test_get_with_tasks' === $this->getName() ) {
			$tasks = $this->getMockBuilder( 'ThemePlate\Process\Tasks' )->setMethods( array( 'add' ) )->getMock();

			$tasks->expects( self::once() )->method( 'add' )->willReturnCallback(
				function( ...$args ) {
					call_user_func_array( $args[0], $args[1] );
				}
			);
		}

		$this->storage = new class() extends OptionsStorage {
			public const PREFIX = 'tcs_fht_';
		};
		$this->handler = new FileHandler( $this->storage, $tasks ?? null );
	}

	public function test_get_with_nothing_known_yet(): void {
		$this->assertFalse( $this->handler->get( 'unknown', '' ) );
	}

	public function test_get_with_forced_refresh(): void {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		global $_REQUEST;

		$_REQUEST[ StorageInterface::PREFIX . 'refresh' ] = 'unknown';

		$this->assertFalse( $this->handler->get( 'unknown', '' ) );
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		unset( $_REQUEST[ StorageInterface::PREFIX . 'refresh' ] );
	}

	public function test_get_with_tasks(): void {
		$saved = get_option( 'blogname' );
		$value = $this->handler->get( 'blogname', WP_CONTENT_DIR . '/index.php' );

		// Returned value is from cache
		$this->assertSame( $saved, $value );
		// New value saved in background
		$this->assertNotSame( $value, get_option( 'blogname' ) );
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
