<?php

namespace Pronamic\WordPress\Pay\Extensions\EventEspressoLegacy;

use PHPUnit_Framework_TestCase;

/**
 * Title: WordPress pay AppThemes test
 * Description:
 * Copyright: Copyright (c) 2005 - 2018
 * Company: Pronamic
 *
 * @author  Reüel van der Steege
 * @version unreleased
 * @since   unreleased
 */
class EventEspressoTest extends PHPUnit_Framework_TestCase {
	/**
	 * Test class.
	 */
	public function test_class() {
		$this->assertTrue( class_exists( 'Pronamic\WordPress\Pay\Extensions\EventEspressoLegacy\EventEspresso' ) );
	}
}