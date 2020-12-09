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
			\__( 'Attendee %s', 'pronamic_ideal' ),
			$attendee_id
		);
	}

	/**
	 * Get customer from data.
	 */
	public static function get_name_from_data( $data ) {
		$name  = self::get_name_from_data( $data );
		$email = $data['email'];

		$customer_data = array(
			$name,
			$email,
		);

		$customer_data = \array_filter( $customer_data );

		if ( empty( $customer_data ) ) {
			return null;
		}

		$customer = new Customer();

		$customer->set_name( $name );

		if ( ! empty( $email ) ) {
			$customer->set_email( $email );
		}

		return $customer;
	}

	/**
	 * Get name from data.
	 */
	public static function get_name_from_data( $data ) {
		$first_name = $data['fname'];
		$last_name  = $data['lname'];

		$name_data = array(
			$first_name,
			$last_name,
		);

		$name_data = \array_filter( $name_data );

		if ( empty( $name_data ) ) {
			return null;
		}

		$name = new ContactName();

		if ( ! empty( $first_name ) ) {
			$name->set_first_name( $first_name );
		}

		if ( ! empty( $last_name ) ) {
			$name->set_last_name( $last_name );
		}
		
		return $name;
	}

	/**
	 * Get address from data.
	 */
	public static function get_address_from_data( $data ) {
		$name        = self::get_name_from_data( $data );
		$line_1      = $data['address'];
		$postal_code = $data['zip'];
		$city        = $data['city'];

		$address_data = array(
			$name,
			$line_1,
			$postal_code,
			$city,
		);

		$address_data = \array_filter( $address_data );

		if ( empty( $address_data ) ) {
			return;
		}

		$address = new Address();

		if ( ! empty( $name ) ) {
			$address->set_name( $name );
		}

		if ( ! empty( $line_1 ) ) {
			$address->set_line_1( $line_1 );
		}

		if ( ! empty( $postal_code ) ) {
			$address->set_postal_code( $postal_code );
		}

		if ( ! empty( $city ) ) {
			$address->set_city( $city );
		}

		return $address;
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
