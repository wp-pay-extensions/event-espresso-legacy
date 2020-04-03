<?php
/**
 * Event Espresso Dependency
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2020 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Extensions\EventEspressoLegacy
 */

namespace Pronamic\WordPress\Pay\Extensions\EventEspressoLegacy;

use Pronamic\WordPress\Pay\Dependencies\Dependency;

/**
 * Event Espresso Dependency
 *
 * @author  Reüel van der Steege
 * @version 2.2.1
 * @since   2.2.1
 */
class EventEspressoDependency extends Dependency {
	/**
	 * Is met.
	 *
	 * @link https://github.com/eventespresso/event-espresso-core/blob/master/espresso.php#L37
	 * @link https://plugins.trac.wordpress.org/browser/event-espresso-free/tags/3.1.35.L/espresso.php#L39
	 * @return bool True if dependency is met, false otherwise.
	 */
	public function is_met() {
		if ( ! \defined( '\EVENT_ESPRESSO_VERSION' ) ) {
			return false;
		}

		return \version_compare(
			\EVENT_ESPRESSO_VERSION,
			'4.0.0',
			'<'
		);
	}
}
