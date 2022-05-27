<?php

/**
 * @package ThemePlate
 */

namespace Tests;

use ThemePlate\Cache\StorageManager;
use WP_UnitTestCase;

class StorageManagerTest extends WP_UnitTestCase {
	private StorageManager $storage;

	protected function setUp(): void {
		$this->storage = new StorageManager();
	}

	public function test_default_type_and_id(): void {
		$this->assertSame( 'options', $this->storage->current() );
		$this->assertSame( 0, $this->storage->get()->pointer() );
	}

	public function for_setting_fields(): array {
		return array(
			'with random string' => array( 'random', 'options', 0 ),
			'with truthy ID'     => array( true, 'options', 0 ),
			'with falsy ID'      => array( false, 'options', 0 ),
			'with array ID'      => array( array(), 'options', 0 ),
			'with null ID'       => array( null, 'options', 0 ),
			'with string ID'     => array( '1', 'postmeta', 1 ),
			'with integer ID'    => array( 2, 'postmeta', 2 ),
			'with post and ID'   => array( 'post_4', 'postmeta', 4 ),
			'with term and ID'   => array( 'term_5', 'termmeta', 5 ),
			'with user and ID'   => array( 'user_6', 'usermeta', 6 ),
			'with unknown type'  => array( 'menu_7', 'options', 0 ),
			'with no separator'  => array( 'term8', 'options', 0 ),
			'with invalid field' => array( 'post.9', 'options', 0 ),
		);
	}

	/**
	 * @dataProvider for_setting_fields
	 */
	public function test_setting_fields( $field, string $type, int $id ): void {
		$this->storage = new StorageManager();

		$this->storage->set( $field );
		$this->assertSame( $type, $this->storage->current() );
		$this->assertSame( $id, $this->storage->get()->pointer() );
	}
}
