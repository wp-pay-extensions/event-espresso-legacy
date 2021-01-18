<?php

namespace Pronamic\WordPress\Pay\Extensions\EventEspressoLegacy;

use PHPUnit_Framework_TestCase;

/**
 * Title: WordPress pay AppThemes test
 * Description:
 * Copyright: 2005-2021 Pronamic
 * Company: Pronamic
 *
 * @author  Reüel van der Steege
 * @version 2.0.0
 * @since   2.0.0
 */
class EventEspressoTest extends PHPUnit_Framework_TestCase {
	/**
	 * Test class.
	 */
	public function test_class() {
		$this->assertTrue( class_exists( __NAMESPACE__ . '\EventEspresso' ) );
	}
}
