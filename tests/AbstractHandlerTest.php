<?php

/**
 * @package ThemePlate
 */

namespace Tests;

use ThemePlate\Cache\Handlers\AbstractHandler;
use ThemePlate\Cache\Storages\OptionsStorage;
use WP_UnitTestCase;

class AbstractHandlerTest extends WP_UnitTestCase {
	// for mixed returns
	public const RANDOM_VALUES = array(
		true,
		false,
		1,
		'',
		array(),
		null,
	);

	public function test_static_update_for_tasks(): void {
		$tasks   = $this->createTestProxy( 'ThemePlate\Process\Tasks' );
		$storage = new OptionsStorage();
		$handler = new class( $storage, $tasks ) extends AbstractHandler {
			public function set( string $key, array $data ) {
				$values = AbstractHandlerTest::RANDOM_VALUES;

				// phpcs:ignore WordPress.WP.AlternativeFunctions.rand_rand
				$value = $values[ rand( 0, 3 ) ];

				$this->storage->set( $key, $value );

				return $value;
			}
		};
		$updated = $handler::update( get_class( $storage ), 0, 'random_wanted_key', array() );

		$this->assertTrue( in_array( $updated, self::RANDOM_VALUES, true ) );
		$this->assertSame( get_option( 'random_wanted_key' ), $updated );
	}
}
