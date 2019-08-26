<?php

namespace Pronamic\WordPress\Pay\Extensions\EventEspressoLegacy;

use Pronamic\WordPress\Pay\Admin\AdminModule;
use Pronamic\WordPress\Pay\Core\PaymentMethods;
use Pronamic\WordPress\Pay\Core\Statuses;
use Pronamic\WordPress\Pay\Payments\Payment;
use Pronamic\WordPress\Pay\Plugin;

/**
 * Title: WordPress pay Event Espresso legacy extension
 * Description:
 * Copyright: 2005-2019 Pronamic
 * Company: Pronamic
 *
 * @author  Remco Tolsma
 * @version 2.1.0
 * @since   1.0.0
 */
class Extension {
	/**
	 * Slug
	 *
	 * @var string
	 */
	const SLUG = 'event-espresso';

	/**
	 * Option for config ID
	 *
	 * @var string
	 */
	const OPTION_CONFIG_ID = 'pronamic_pay_ideal_event_espreso_config_id';

	/**
	 * Bootstrap
	 */
	public static function bootstrap() {
		if ( ! EventEspresso::is_active() ) {
			return;
		}

		add_action( 'init', array( __CLASS__, 'init' ) );
	}

	/**
	 * Initialize
	 */
	public static function init() {
		add_filter( 'action_hook_espresso_display_gateway_settings', array( __CLASS__, 'display_gateway_settings' ) );

		add_action( 'action_hook_espresso_display_onsite_payment_header', 'espresso_display_onsite_payment_header' );
		add_action( 'action_hook_espresso_display_onsite_payment_footer', 'espresso_display_onsite_payment_footer' );
		add_action( 'action_hook_espresso_display_onsite_payment_gateway', array( __CLASS__, 'display_gateway' ) );

		add_filter( 'filter_hook_espresso_transactions_get_attendee_id', array( __CLASS__, 'transactions_get_attendee_id' ) );

		add_action( 'template_redirect', array( __CLASS__, 'process_gateway' ) );

		add_filter( 'pronamic_payment_redirect_url_' . self::SLUG, array( __CLASS__, 'redirect_url' ), 10, 2 );
		add_action( 'pronamic_payment_status_update_' . self::SLUG, array( __CLASS__, 'update_status' ), 10, 1 );

		add_filter( 'pronamic_payment_source_text_' . self::SLUG, array( __CLASS__, 'source_text' ), 10, 2 );
		add_filter( 'pronamic_payment_source_description_' . self::SLUG, array( __CLASS__, 'source_description' ), 10, 2 );
		add_filter( 'pronamic_payment_source_url_' . self::SLUG, array( __CLASS__, 'source_url' ), 10, 2 );

		// Fix fatal error since Event Espresso 3.1.29.1.P.
		if ( defined( 'EVENT_ESPRESSO_GATEWAY_DIR' ) ) {
			$gateway_dir  = EVENT_ESPRESSO_GATEWAY_DIR . 'pronamic_ideal';
			$gateway_init = $gateway_dir . '/init.php';

			if ( ! is_readable( $gateway_init ) ) {
				$created = wp_mkdir_p( $gateway_dir );

				if ( $created ) {
					touch( $gateway_init );
				}
			}
		}
	}

	/**
	 * Process gateway
	 */
	public static function process_gateway() {
		if ( ! filter_has_var( INPUT_POST, 'event_espresso_pronamic_ideal' ) ) {
			return;
		}

		$config_id = get_option( self::OPTION_CONFIG_ID );

		$gateway = Plugin::get_gateway( $config_id );

		if ( ! $gateway ) {
			return;
		}

		$payment_data = array(
			'attendee_id' => apply_filters( 'filter_hook_espresso_transactions_get_attendee_id', '' ),
		);

		$data = new PaymentData( $payment_data );

		$payment = Plugin::start( $config_id, $gateway, $data );

		$error = $gateway->get_error();

		if ( is_wp_error( $error ) ) {
			Plugin::render_errors( $error );
		} else {
			$gateway->redirect( $payment );
		}
	}

	/**
	 * Display gateway
	 *
	 * @param array $payment_data Payment data.
	 */
	public static function display_gateway( $payment_data ) {
		$config_id = get_option( self::OPTION_CONFIG_ID );

		$gateway = Plugin::get_gateway( $config_id );

		if ( $gateway ) {
			$data = new PaymentData( $payment_data );

			?>
			<div id="pronamic-payment-option-dv" class="payment-option-dv">
				<a id="pronamic-payment-option-lnk" class="pronamic-option-lnk display-the-hidden" rel="pronamic-payment-option-form" style="cursor:pointer;">
					<?php

					printf(
						'<img alt="%s" src="%s" />',
						esc_attr__( 'Pay with iDEAL', 'pronamic_ideal' ),
						esc_attr( plugins_url( 'images/ideal.nl/iDEAL-Payoff-2-klein.gif', Plugin::$file ) )
					);

					?>
				</a>

				<div id="pronamic-payment-option-form-dv" class="hide-if-js">
					<h3 class="payment_header">
						<?php esc_html_e( 'iDEAL', 'pronamic_ideal' ); ?>
					</h3>

					<div class="event_espresso_form_wrapper">
						<form method="post" action="<?php echo esc_attr( $data->get_notify_url() ); ?>">
							<?php

							// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							echo $gateway->get_input_html();

							?>

							<p>
								<?php

								printf(
									'<input class="ideal-button allow-leave-page" type="submit" name="event_espresso_pronamic_ideal" value="%s" />',
									esc_html__( 'Pay with iDEAL', 'pronamic_ideal' )
								);

								?>
							</p>
						</form>
					</div>

					<p class="choose-diff-pay-option-pg">
						<a class="hide-the-displayed" rel="pronamic-payment-option-form" style="cursor:pointer;">
							<?php esc_html_e( 'Choose a different payment option', 'pronamic_ideal' ); ?>
						</a>
					</p>
				</div>
			</div>

			<?php
		}
	}

	/**
	 * Transaction get attendee ID
	 *
	 * @return string
	 */
	public static function transactions_get_attendee_id() {
		return filter_input( INPUT_GET, 'attendee_id', FILTER_SANITIZE_STRING );
	}

	/**
	 * Display gateway settings
	 */
	public static function display_gateway_settings() {
		global $espresso_premium, $active_gateways;

		// Handle request.
		if ( isset( $_REQUEST['activate_pronamic_ideal'] ) ) {
			$active_gateways['pronamic_ideal'] = dirname( __FILE__ );

			update_option( 'event_espresso_active_gateways', $active_gateways );
		}

		if ( isset( $_REQUEST['deactivate_pronamic_ideal'] ) ) {
			unset( $active_gateways['pronamic_ideal'] );

			update_option( 'event_espresso_active_gateways', $active_gateways );
		}

		// Config.
		$config_id = get_option( self::OPTION_CONFIG_ID );

		if ( filter_has_var( INPUT_POST, self::OPTION_CONFIG_ID ) ) {
			$config_id = filter_input( INPUT_POST, self::OPTION_CONFIG_ID, FILTER_VALIDATE_INT );

			update_option( self::OPTION_CONFIG_ID, $config_id );
		}

		// Active.
		$is_active = array_key_exists( 'pronamic_ideal', $active_gateways );

		// Postbox style.
		$postbox_style = '';
		if ( ! $is_active ) {
			$postbox_style = 'closed';
		}

		// URL.
		$url = add_query_arg( 'page', 'payment_gateways', admin_url( 'admin.php' ) );

		?>
		<div class="metabox-holder">
			<div class="postbox <?php echo esc_attr( $postbox_style ); ?>">
				<div title="<?php esc_attr_e( 'Click to toggle', 'pronamic_ideal' ); ?>" class="handlediv"><br/></div>

				<h3 class="hndle">
					<?php esc_html_e( 'Pronamic Pay', 'pronamic_ideal' ); ?>
				</h3>

				<div class="inside">
					<div class="padding">
						<ul>
							<?php if ( $is_active ) : ?>

								<li class="red_alert pointer"
									onclick="location.href='<?php echo esc_url( add_query_arg( 'deactivate_pronamic_ideal', true, $url ) ); ?>';"
									style="width:30%;">
									<strong><?php esc_html_e( 'Deactivate Pronamic Pay?', 'pronamic_ideal' ); ?></strong>
								</li>

								<form method="post" action="">
									<table width="99%" border="0" cellspacing="5" cellpadding="5">
										<tr>
											<td valign="top">
												<ul>
													<li>
														<label for="pronamic_pay_ideal_event_espresso_config_id">
															<?php esc_html_e( 'Configuration', 'pronamic_ideal' ); ?>
														</label>

														<br/>

														<?php

														AdminModule::dropdown_configs(
															array(
																'name'     => self::OPTION_CONFIG_ID,
																'selected' => $config_id,
															)
														);

														?>

														<br/>
													</li>
												</ul>
											</td>
										</tr>
									</table>

									<?php submit_button( __( 'Update Settings', 'pronamic_ideal' ) ); ?>
								</form>

							<?php else : ?>

								<li class="green_alert pointer"
									onclick="location.href='<?php echo esc_url( add_query_arg( 'activate_pronamic_ideal', true, $url ) ); ?>';"
									style="width:30%;">
									<strong><?php esc_html_e( 'Activate Pronamic Pay?', 'pronamic_ideal' ); ?></strong>
								</li>

							<?php endif; ?>
						</ul>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Payment redirect URL filter.
	 *
	 * @param string  $url     Redirect URL.
	 * @param Payment $payment Payment.
	 *
	 * @return string
	 */
	public static function redirect_url( $url, Payment $payment ) {
		$attendee_id = $payment->get_source_id();

		$payment_data = EventEspresso::get_payment_data_by_attendee_id( $attendee_id );

		$data = new PaymentData( $payment_data );

		$url = $data->get_normal_return_url();

		switch ( $payment->get_status() ) {
			case Statuses::CANCELLED:
				$url = $data->get_cancel_url();

				break;
			case Statuses::EXPIRED:
				break;
			case Statuses::FAILURE:
				break;
			case Statuses::SUCCESS:
				$url = $data->get_success_url();

				break;
			case Statuses::OPEN:
				break;
			default:
				break;
		}

		return $url;
	}

	/**
	 * Update lead status of the specified payment
	 *
	 * @param Payment $payment Payment.
	 */
	public static function update_status( Payment $payment ) {
		$attendee_id = $payment->get_source_id();

		$payment_data             = EventEspresso::get_payment_data_by_attendee_id( $attendee_id );
		$payment_data['txn_type'] = PaymentMethods::get_name( PaymentMethods::IDEAL );
		$payment_data['txn_id']   = $payment->get_transaction_id();

		switch ( $payment->get_status() ) {
			case Statuses::CANCELLED:
				$payment_data['payment_status'] = EventEspresso::PAYMENT_STATUS_INCOMPLETE;
				EventEspresso::update_payment( $payment_data );

				break;
			case Statuses::SUCCESS:
				$payment_data['payment_status'] = EventEspresso::PAYMENT_STATUS_COMPLETED;

				EventEspresso::update_payment( $payment_data );
				EventEspresso::email_after_payment( $payment_data );

				break;
		}
	}

	/**
	 * Source column
	 *
	 * @param string  $text    Source text.
	 * @param Payment $payment Payment.
	 *
	 * @return string
	 */
	public static function source_text( $text, Payment $payment ) {
		$url = add_query_arg(
			array(
				'page'                => 'events',
				'event_admin_reports' => 'event_list_attendees',
				'all_a'               => 'true',
			),
			admin_url( 'admin.php' )
		);

		$url = self::source_url( $url, $payment );

		$text = __( 'Event Espresso', 'pronamic_ideal' ) . '<br />';

		$text .= sprintf(
			'<a href="%s">%s</a>',
			esc_attr( $url ),
			/* translators: %s: payment source id */
			sprintf( __( 'Attendee #%s', 'pronamic_ideal' ), $payment->get_source_id() )
		);

		return $text;
	}

	/**
	 * Source description.
	 *
	 * @param string  $description Source description.
	 * @param Payment $payment     Pronamic payment.
	 *
	 * @return string
	 */
	public static function source_description( $description, Payment $payment ) {
		return __( 'Event Espresso Attendee', 'pronamic_ideal' );
	}

	/**
	 * Source URL.
	 *
	 * @param string  $url     Source URL.
	 * @param Payment $payment Pronamic payment.
	 *
	 * @return string
	 */
	public static function source_url( $url, Payment $payment ) {
		$attendee_id = $payment->get_source_id();

		$attendee = espresso_get_attendee_meta_value( $attendee_id, 'original_attendee_details' );

		$attendee = unserialize( $attendee );

		$url = add_query_arg(
			array(
				'page'                => 'events',
				'event_admin_reports' => 'edit_attendee_record',
				'event_id'            => $attendee['event_id'],
				'registration_id'     => $attendee['registration_id'],
				'form_action'         => 'edit_attendee',
				'id'                  => $attendee_id,
			),
			admin_url( 'admin.php' )
		);

		return $url;
	}
}
