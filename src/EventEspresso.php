<?php

namespace Pronamic\WordPress\Pay\Extensions\EventEspressoLegacy;

/**
 * Title: Event Espresso
 * Description:
 * Copyright: 2005-2022 Pronamic
 * Company: Pronamic
 *
 * @author  Remco Tolsma
 * @version 2.0.0
 * @since   1.0.0
 */
class EventEspresso {
	/**
	 * Payment status incomplete indicator
	 *
	 * @var string
	 */
	const PAYMENT_STATUS_INCOMPLETE = 'Incomplete';

	/**
	 * Payment status pending indicator
	 *
	 * @var string
	 */
	const PAYMENT_STATUS_PENDING = 'Pending';

	/**
	 * Payment status completed indicator
	 *
	 * @var string
	 */
	const PAYMENT_STATUS_COMPLETED = 'Completed';

	/**
	 * Get payment data by attendee ID
	 *
	 * @param string $id Attendee ID.
	 *
	 * @return array
	 */
	public static function get_payment_data_by_attendee_id( $id ) {
		event_espresso_require_gateway( 'process_payments.php' );

		$data = array(
			'attendee_id' => $id,
			// The 'txn_details' key is not (always) filled in by the filters
			// below, to prevent unknown key notices we add it here.
			'txn_details' => '',
		);

		$data = apply_filters( 'filter_hook_espresso_prepare_payment_data_for_gateways', $data );
		$data = apply_filters( 'filter_hook_espresso_get_total_cost', $data );

		return $data;
	}

	/**
	 * Update the payment data
	 *
	 * @link https://github.com/eventespresso/event-espresso-legacy/blob/3.1.35.P/includes/process-registration/payment_page.php#L407
	 *
	 * @param array $payment_data Payment data.
	 */
	public static function update_payment( $payment_data ) {
		event_espresso_require_gateway( 'process_payments.php' );

		/*
		 * Apply filter to save payment data in database.
		 *
		 * @link https://github.com/eventespresso/event-espresso-core/blob/event-espresso.3.1.24.1.P/gateways/process_payments.php#L75
		 */
		$payment_data = apply_filters( 'filter_hook_espresso_update_attendee_payment_data_in_db', $payment_data );
	}

	/**
	 * E-mail after payment
	 *
	 * @link https://github.com/eventespresso/event-espresso-legacy/blob/3.1.35.P/includes/process-registration/payment_page.php#L407
	 *
	 * @param array $payment_data Payment data.
	 */
	public static function email_after_payment( $payment_data ) {
		event_espresso_require_gateway( 'process_payments.php' );

		/*
		 * Load the email.php functions file
		 *
		 * @link https://github.com/eventespresso/event-espresso-legacy/blob/3.1.35.P/espresso.php#L464
		 */
		$filename = EVENT_ESPRESSO_INCLUDES_DIR . 'functions/email.php';

		if ( is_readable( $filename ) ) {
			require_once $filename;
		}

		// Actions.
		add_action( 'action_hook_espresso_email_after_payment', 'espresso_email_after_payment' );

		do_action( 'action_hook_espresso_email_after_payment', $payment_data );
	}
}
