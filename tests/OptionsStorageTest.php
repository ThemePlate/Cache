<?php

/**
 * @package ThemePlate
 */

namespace Tests;

use ThemePlate\Cache\Storages\OptionsStorage;
use WP_UnitTestCase;

class OptionsStorageTest extends WP_UnitTestCase {
	public function test_get(): void {
		$storage = new OptionsStorage();

		$this->assertSame( 0, $storage->pointer() );
		$this->assertFalse( $storage->get( 'unknown' ) );
	}

	public function test_set_then_delete(): void {
		$key     = 'random_option_name';
		$value   = 'wanted_data';
		$storage = new OptionsStorage();

		// Success on first save
		$this->assertTrue( $storage->set( $key, $value ) );

		// Fail on same value save
		$this->assertFalse( $storage->set( $key, $value ) );

		// Correctly saved data value
		$this->assertSame( $value, $storage->get( $key ) );

		// Success on first delete
		$this->assertTrue( $storage->delete( $key ) );

		// Fail on succeeding delete
		$this->assertFalse( $storage->delete( $key ) );
	}
}
