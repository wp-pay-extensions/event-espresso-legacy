<?php

namespace Pronamic\WordPress\Pay\Extensions\EventEspressoLegacy;

use Pronamic\WordPress\Pay\Payments\PaymentData as Pay_PaymentData;
use Pronamic\WordPress\Pay\Payments\Item;
use Pronamic\WordPress\Pay\Payments\Items;

/**
 * Title: WordPress pay Event Espresso legacy payment data
 * Description:
 * Copyright: 2005-2019 Pronamic
 * Company: Pronamic
 *
 * @author  Remco Tolsma
 * @version 2.1.0
 * @since   1.0.0
 */
class PaymentData extends Pay_PaymentData {
	/**
	 * Data
	 *
	 * @var array
	 */
	private $data;

	/**
	 * Constructs and initializes an Event Espresso iDEAL data proxy
	 *
	 * @param array $data Data.
	 */
	public function __construct( $data ) {
		parent::__construct();

		$data = apply_filters( 'filter_hook_espresso_prepare_payment_data_for_gateways', $data );
		$data = apply_filters( 'filter_hook_espresso_get_total_cost', $data );

		$this->data = $data;
	}

	/**
	 * Get source indicator
	 *
	 * @see Pronamic_Pay_PaymentDataInterface::get_source()
	 * @return string
	 */
	public function get_source() {
		return 'event-espresso';
	}

	/**
	 * Get description
	 *
	 * @see Pronamic_Pay_PaymentDataInterface::get_description()
	 * @return string
	 */
	public function get_description() {
		/* translators: %s: attendee id */
		return sprintf( __( 'Attendee %s', 'pronamic_ideal' ), $this->data['attendee_id'] );
	}

	/**
	 * Get order ID
	 *
	 * @see Pronamic_Pay_PaymentDataInterface::get_order_id()
	 * @return string
	 */
	public function get_order_id() {
		return $this->data['attendee_id'];
	}

	/**
	 * Get items
	 *
	 * @see Pronamic_Pay_PaymentDataInterface::get_items()
	 * @return Items
	 */
	public function get_items() {
		// Items.
		$items = new Items();

		// Item
		// We only add one total item, because iDEAL cant work with negative price items (discount).
		$item = new Item();
		$item->set_number( $this->data['attendee_id'] );
		/* translators: %s: attendee id */
		$item->set_description( sprintf( __( 'Attendee %s', 'pronamic_ideal' ), $this->data['attendee_id'] ) );
		$item->set_price( $this->data['total_cost'] );
		$item->set_quantity( 1 );

		$items->addItem( $item );

		return $items;
	}

	/**
	 * Get currency
	 *
	 * @see Pronamic_Pay_PaymentDataInterface::get_currency_alphabetic_code()
	 * @return string
	 */
	public function get_currency_alphabetic_code() {
		return 'EUR';
	}

	/**
	 * Get email.
	 *
	 * @return mixed|string
	 */
	public function get_email() {
		return $this->data['email'];
	}

	/**
	 * Get customer name.
	 *
	 * @return string
	 */
	public function get_customer_name() {
		return $this->data['fname'] . ' ' . $this->data['lname'];
	}

	/**
	 * Get address.
	 *
	 * @return mixed|null
	 */
	public function get_address() {
		return $this->data['address'];
	}

	/**
	 * Get city.
	 *
	 * @return mixed|null
	 */
	public function get_city() {
		return $this->data['city'];
	}

	/**
	 * Get ZIP.
	 *
	 * @return mixed|null
	 */
	public function get_zip() {
		return $this->data['zip'];
	}

	/**
	 * Get notify URL.
	 *
	 * @return mixed
	 */
	public function get_notify_url() {
		global $org_options;

		return add_query_arg(
			array(
				'attendee_id'     => $this->data['attendee_id'],
				'registration_id' => $this->data['registration_id'],
				'event_id'        => $this->data['event_id'],
			),
			get_permalink( $org_options['notify_url'] )
		);
	}

	/**
	 * Get return URL.
	 *
	 * @return mixed
	 */
	private function get_return_url() {
		global $org_options;

		return add_query_arg(
			array(
				'attendee_id'     => $this->data['attendee_id'],
				'registration_id' => $this->data['registration_id'],
				'event_id'        => $this->data['event_id'],
			),
			get_permalink( $org_options['return_url'] )
		);
	}

	/**
	 * Get cancel return.
	 *
	 * @return mixed
	 */
	private function get_cancel_return() {
		global $org_options;

		return get_permalink( $org_options['cancel_return'] );
	}

	/**
	 * Get normal return URL.
	 *
	 * @return string
	 */
	public function get_normal_return_url() {
		return $this->get_return_url();
	}

	/**
	 * Get cancel URL.
	 *
	 * @return string
	 */
	public function get_cancel_url() {
		return $this->get_cancel_return();
	}

	/**
	 * Get success URL.
	 *
	 * @return string
	 */
	public function get_success_url() {
		return $this->get_return_url();
	}

	/**
	 * Get error URL.
	 *
	 * @return string
	 */
	public function get_error_url() {
		return $this->get_return_url();
	}
}
