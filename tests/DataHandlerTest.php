<?php

/**
 * @package ThemePlate
 */

namespace Tests;

use ThemePlate\Cache\Handlers\DataHandler;
use ThemePlate\Cache\Storages\OptionsStorage;
use ThemePlate\Cache\Storages\StorageInterface;
use WP_Error;
use WP_UnitTestCase;

class DataHandlerTest extends WP_UnitTestCase {
	private DataHandler $handler;

	protected function setUp(): void {
		if ( 'test_get_with_tasks' === $this->getName() ) {
			$tasks = $this->getMockBuilder( 'ThemePlate\Process\Tasks' )->setMethods( array( 'add' ) )->getMock();

			$tasks->expects( self::once() )->method( 'add' )->willReturnSelf();
		}

		$this->storage = new OptionsStorage();
		$this->handler = new DataHandler( $this->storage, $tasks ?? null );
	}

	public function test_get_with_nothing_known_yet(): void {
		$this->assertFalse( $this->handler->get( 'unknown', array() ) );
	}

	public function test_get_with_forced_refresh(): void {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		global $_REQUEST;

		$_REQUEST[ StorageInterface::PREFIX . 'refresh' ] = 'unknown';

		$this->assertFalse( $this->handler->get( 'unknown', array() ) );
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		unset( $_REQUEST[ StorageInterface::PREFIX . 'refresh' ] );
	}

	public function test_get_with_tasks(): void {
		$saved      = get_option( 'blogname' );
		$callback   = 'uniqid';
		$expiration = 0;

		$value = $this->handler->get( 'blogname', compact( 'callback', 'expiration' ) );

		$this->assertSame( $saved, $value );
	}

	public function test_get_with_action_update(): void {
		$callback   = '__return_true';
		$expiration = 10;

		$this->assertTrue( $this->handler->get( 'users_can_register', compact( 'callback', 'expiration' ) ) );
	}

	public function test_set_with_callback_error(): void {
		$callback   = 'non_callable';
		$expiration = 20;

		$this->expectErrorMessage( 'Call to undefined function non_callable()' );

		$this->handler->set( 'random_key', compact( 'callback', 'expiration' ) );
	}

	public function test_set_with_wp_error(): void {
		$callback   = 'wp_create_user_request';
		$expiration = 30;

		$value = $this->handler->set( 'random_key', compact( 'callback', 'expiration' ) );

		$this->assertFalse( $this->storage->get( 'wanted_key', true ) );
		$this->assertInstanceOf( WP_Error::class, $value );
	}

	public function test_set_with_non_error_but_closure(): void {
		$callback   = function () {
			return 'yay!';
		};
		$expiration = 40;

		$value = $this->handler->set( 'wanted_key', compact( 'callback', 'expiration' ) );

		$this->assertFalse( $this->storage->get( 'wanted_key', true ) );
		$this->assertSame( $callback(), $value );
	}

	public function test_set_with_non_error_and_non_closure(): void {
		$callback   = 'date_default_timezone_get';
		$expiration = 50;

		$value = $this->handler->set( 'wanted_key', compact( 'callback', 'expiration' ) );

		$this->assertSame( time() + $expiration, $this->storage->get( 'wanted_key', true ) );
		$this->assertSame( date_default_timezone_get(), $value );
	}
}
