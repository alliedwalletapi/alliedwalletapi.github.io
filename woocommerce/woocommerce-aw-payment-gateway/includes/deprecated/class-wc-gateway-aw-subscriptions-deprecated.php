<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC_Gateway_Inspire_Subscriptions_Deprecated class.
 *
 * This class may be used to add support for WC Subscriptions 1.5.x
 *
 * @extends WC_Gateway_Inspire_Subscriptions
 */
class WC_Gateway_Inspire_Subscriptions_Deprecated extends WC_Gateway_Inspire_Subscriptions {

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct();

		if ( class_exists( 'WC_Subscriptions_Order' ) ) {

			add_action( 'scheduled_subscription_payment_inspire', array( $this, 'process_scheduled_subscription_payment'), 0, 3 );
			add_action( 'scheduled_subscription_payment_' . $this->id, array( $this, 'process_scheduled_subscription_payment' ), 10, 3 );

			//add_filter( 'woocommerce_subscriptions_renewal_order_meta_query', array( $this, 'remove_renewal_order_meta' ), 10, 4 );
			//add_action( 'woocommerce_subscriptions_changed_failing_payment_method_inspire', array( $this, 'change_failing_payment_method' ), 10, 3 );

			// display the current payment method used for a subscription in the "My Subscriptions" table
			//add_filter( 'woocommerce_my_subscriptions_recurring_payment_method', array( $this, 'my_subscriptions_recurring_payment_method' ), 10, 3 );
		}
	}


	/**
	 * Process the payment
	 *
	 * @param  int $order_id
	 * @param bool $retry
	 * @return array
	 */
	public function process_payment( $order_id, $retry = true ) {
		// Processing subscription
		if ( class_exists( 'WC_Subscriptions_Order' ) && WC_Subscriptions_Order::order_contains_subscription( $order_id ) ) {
			return $this->process_subscription( $order_id, $retry );

		} else {
			return parent::process_payment( $order_id, $retry );
		}
	}

	/**
	 * scheduled_subscription_payment function.
	 *
	 * @param $amount_to_charge float The amount to charge.
	 * @param $order WC_Order The WC_Order object of the order which the subscription was purchased in.
	 * @param $product_id int The ID of the subscription product for which this payment relates.
	 * @access public
	 * @return void
	 */
	public function process_scheduled_subscription_payment( $amount_to_charge, $order, $product_id ) {
		$result = $this->process_subscription_payment( $order, $amount_to_charge );

		if ( is_wp_error( $result ) ) {
			WC_Subscriptions_Manager::process_subscription_payment_failure_on_order( $order, $product_id );
		} else {
			WC_Subscriptions_Manager::process_subscription_payments_on_order( $order );
		}
	}

	/**
	 * process_subscription_payment function.
	 *
	 * @access public
	 * @param mixed $order
	 * @param int $amount (default: 0)
	 * @param  bool $initial_payment
	 * @return string|WP_Error
	 */
	public function process_subscription_payment( $order = '', $amount = 0, $initial_payment = false ) {

		// TODO

		return '';
	}


}
