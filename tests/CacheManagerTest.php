<?php

/**
 * @package ThemePlate
 */

namespace Tests;

use ThemePlate\Cache\CacheManager;
use WP_UnitTestCase;

class CacheManagerTest extends WP_UnitTestCase {
	private CacheManager $cache;

	protected function setUp(): void {
		if ( 0 === strpos( $this->getName(), 'test_with_tasks_remember' ) ) {
			$tasks = $this->getMockBuilder( 'ThemePlate\Process\Tasks' )->setMethods( array( 'add' ) )->getMock();

			$tasks->expects( self::atMost( 2 ) )->method( 'add' )->willReturnCallback(
				function ( $callback, $data ) {
					call_user_func_array( $callback, $data );
				}
			);
		}

		$this->cache   = new CacheManager( $tasks ?? null );
		$this->default = array(
			'type' => 'options',
			'ID'   => 0,
		);
	}

	public function test_assign_then_reset_fields(): void {
		$this->assertSame( $this->default, $this->cache->assignment() );
		$this->cache->assign( 'post_1' );
		$this->assertSame(
			array(
				'type' => 'postmeta',
				'ID'   => 1,
			),
			$this->cache->assignment()
		);
		$this->cache->reset();
		$this->assertSame( $this->default, $this->cache->assignment() );
	}

	public function test_reset_then_assign_fields(): void {
		$this->assertSame( $this->default, $this->cache->assignment() );
		$this->cache->reset();
		$this->assertSame( $this->default, $this->cache->assignment() );
		$this->cache->assign( 'term_2' );
		$this->assertSame(
			array(
				'type' => 'termmeta',
				'ID'   => 2,
			),
			$this->cache->assignment()
		);
	}

	public function test_without_tasks_remember(): void {
		$value = $this->cache->remember( $this->getName(), 'date_default_timezone_get', 10 );

		$this->assertSame( date_default_timezone_get(), $value );
	}

	public function test_with_tasks_remember(): void {
		$callback = $this->getMockBuilder( 'CacheTester' )->setMethods( array( 'soft_update' ) )->getMock();

		$callback->expects( self::atMost( 3 ) )->method( 'soft_update' )
			->willReturn( 'first', 'second', 'third' );

		$value = $this->cache->remember( $this->getName(), array( $callback, 'soft_update' ), 1 );
		// Initial value saved with no background then served
		$this->assertSame( 'first', $value );
		$this->assertSame( 'first', get_option( $this->getName() ) );
		sleep( 2 ); // intended sleep time greater than the expiration

		$value = $this->cache->remember( $this->getName(), array( $callback, 'soft_update' ), 1 );
		// New value saved in background, yet still serving cached
		$this->assertSame( 'first', $value );
		$this->assertSame( 'second', get_option( $this->getName() ) );
		sleep( 2 ); // intended sleep time greater than the expiration

		$value = $this->cache->remember( $this->getName(), array( $callback, 'soft_update' ), 1 );
		// New value saved in background, yet still serving cached
		$this->assertSame( 'first', $value );
		$this->assertSame( 'third', get_option( $this->getName() ) );
	}

	public function test_forget_unknown(): void {
		$value = $this->cache->forget( 'unknown' );

		$this->assertNull( $value );
	}

	public function test_forget_known(): void {
		$saved = get_option( 'cron' );
		$value = $this->cache->forget( 'cron' );
		// Saved value is served but removed in the background
		$this->assertSame( $saved, $value );

		$value = $this->cache->forget( 'cron' );
		// Nothing returned on succeeding calls
		$this->assertNull( $value );
	}

	public function test_file_method(): void {
		$path  = WP_CONTENT_DIR . '/index.php';
		$value = $this->cache->file( $this->getName(), $path, 10 );

		$this->assertSame( file_get_contents( $path ), $value ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	}
}

