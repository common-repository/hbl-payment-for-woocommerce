<?php

namespace HBLPaymentForWooCommerce;

defined( 'ABSPATH' ) || exit;

/**
 * Response from Prabhu Pay.
 *
 * @since 2.0.9
 */
class Response {

	/**
	 * Constructor.
	 *
	 * @since 2.0.9
	 *
	 * @param WC_Gateway_HBL_Payment $gateway Gateway class.
	 */
	public function __construct( $gateway ) { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh

		add_action( 'woocommerce_api_wc_gateway_hbl_payment', array( $this, 'handle' ) );
	}

	/**
	 * Handle Return.
	 *
	 * @since 2.0.9.1
	 */
	public function handle() {

		if ( $this->validate_ipn() ) {

			$gateway = new \WC_Gateway_HBL_Payment();

			if ( ! empty( $_REQUEST['failed'] ) ) {
				wc_add_notice( 'ERROR: Payment failed.', 'error' );
				wp_safe_redirect( wc_get_checkout_url() );
				exit();

			} elseif( ! empty( $_REQUEST['canceled'] ) ) {
				wc_add_notice( 'ERROR: Payment Canceled.', 'error' );
				wp_safe_redirect( wc_get_checkout_url() );
				exit();
			}

			// Only assuming success. @todo:: Needs to validate with the transactionCheck API.
			$orderId = ! empty( $_GET['orderNo'] ) ? wc_clean( wp_unslash( $_GET['orderNo'] ) ) : '';

			$order = new \WC_Order( $orderId );

			if ( ! empty( $order ) ) {
				$order->update_status( 'processing' );
			}

			wp_safe_redirect( $gateway->get_return_url( wc_get_order( $orderId ) ) );
			exit();
		}

		wp_die( 'HBL Payment Request Failure', 'HBL Payment IPN', array( 'response' => 500 ) );
	}

	/**
	 * Validate IPN.
	 *
	 * @since 2.0.9.1
	 */
	private function validate_ipn() {

		return true; // @todo:: handle later.

		\WC_Gateway_HBL_Payment::log( 'Checking IPN Response!' );

		if ( empty( $_REQUEST['orderNo'] ) || empty( $_REQUEST['controllerInternalId'] ) ) {
			return false;
		}

		$order_id = isset( $_REQUEST['orderNo'] ) ? wc_clean( wp_unslash( $_REQUEST['orderNo'] ) ) : '';
		// WPCS: input var ok, CSRF ok.
		$controllerInternalId = isset( $_REQUEST['controllerInternalId'] ) ? wc_clean( wp_unslash( $_REQUEST['controllerInternalId'] ) ) : '';
		// WPCS: input var ok, CSRF ok.

		return true;
	}
}
