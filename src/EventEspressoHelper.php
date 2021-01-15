<?php
/**
 * Event Espresso Helper
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2020 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Extensions\EventEspressoLegacy
 */

namespace Pronamic\WordPress\Pay\Extensions\EventEspressoLegacy;

use Pronamic\WordPress\Pay\Address;
use Pronamic\WordPress\Pay\AddressHelper;
use Pronamic\WordPress\Pay\ContactName;
use Pronamic\WordPress\Pay\ContactNameHelper;
use Pronamic\WordPress\Pay\Customer;
use Pronamic\WordPress\Pay\CustomerHelper;

/**
 * Event Espresso Helper
 *
 * @author  Remco Tolsma
 * @version 2.3.0
 * @since   2.3.0
 */
class EventEspressoHelper {
	/**
	 * Get description.
	 *
	 * @return string
	 */
	public static function get_description( $attendee_id ) {
		/* translators: %s: attendee id */
		return \sprintf(
			/* translators: %s: attendee ID */
			\__( 'Attendee %s', 'pronamic_ideal' ),
			$attendee_id
		);
	}

	/**
	 * Get customer from data.
	 *
	 * @return Customer|null
	 */
	public static function get_customer_from_data( $data ) {
		return CustomerHelper::from_array(
			array(
				'name'  => self::get_name_from_data( $data ),
				'email' => $data['email'],
			)
		);
	}

	/**
	 * Get name from data.
	 *
	 * @return ContactName|null
	 */
	public static function get_name_from_data( $data ) {
		return ContactNameHelper::from_array(
			array(
				'first_name' => $data['fname'],
				'last_name'  => $data['lname'],
			)
		);
	}

	/**
	 * Get address from data.
	 *
	 * @return Address|null
	 */
	public static function get_address_from_data( $data ) {
		return AddressHelper::from_array(
			array(
				'name'        => self::get_name_from_data( $data ),
				'line_1'      => $data['address'],
				'postal_code' => $data['zip'],
				'city'        => $data['city'],
			)
		);
	}

	/**
	 * Get notify URL.
	 *
	 * @return mixed
	 */
	public static function get_notify_url( $data ) {
		global $org_options;

		return add_query_arg(
			array(
				'attendee_id'     => $data['attendee_id'],
				'registration_id' => $data['registration_id'],
				'event_id'        => $data['event_id'],
			),
			get_permalink( $org_options['notify_url'] )
		);
	}

	/**
	 * Get return URL.
	 *
	 * @return mixed
	 */
	public static function get_return_url( $data ) {
		global $org_options;

		return add_query_arg(
			array(
				'attendee_id'     => $data['attendee_id'],
				'registration_id' => $data['registration_id'],
				'event_id'        => $data['event_id'],
			),
			get_permalink( $org_options['return_url'] )
		);
	}

	/**
	 * Get cancel return.
	 *
	 * @return mixed
	 */
	public static function get_cancel_return() {
		global $org_options;

		return \get_permalink( $org_options['cancel_return'] );
	}
}
