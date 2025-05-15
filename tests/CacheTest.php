<?php

/**
 * @package ThemePlate
 */

namespace Tests;

use ThemePlate\Cache;
use WP_UnitTestCase;

class CacheTest extends WP_UnitTestCase {
	public function test_throws_an_error_if_calling_undefined_method(): void {
		$this->expectExceptionMessage( 'Call to undefined method ' . Cache::class . '::force()' );

		// @phpstan-ignore-next-line
		Cache::force( 'test', 'time' );
	}
}
