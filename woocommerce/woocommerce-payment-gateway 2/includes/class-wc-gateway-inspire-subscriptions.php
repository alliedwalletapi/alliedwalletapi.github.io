<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC_Gateway_Inspire_Subscriptions class.
 *
 * @since 2.0.0
 * @extends WC_Payment_Gateway
 */
class WC_Gateway_Inspire_Subscriptions extends WC_Gateway_Inspire {

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct();

		if ( class_exists( 'WC_Subscriptions_Order' ) ) {

			add_action( 'scheduled_subscription_payment_' . $this->id, array( $this, 'scheduled_subscription_payment' ), 10, 2 );

		}

	}

	/**
	 * Process the payment
	 *
	 * @param  int $order_id
	 * @return array
	 */
	public function process_payment( $order_id ) {
		// Processing subscription
		if ( function_exists( 'wcs_order_contains_subscription' ) && ( wcs_order_contains_subscription( $order_id ) || wcs_is_subscription( $order_id ) || wcs_order_contains_renewal( $order_id ) ) ) {
			return $this->process_subscription( $order_id );
		} else {
			return parent::process_payment( $order_id );
		}
	}

	/**
	 * Process the subscription payment and set the appropriate recurring flags.
	 *
	 * @param $order_id
	 * @return array
	 */
	public function process_subscription( $order_id ) {

		$new_customer_vault_id = '';
		$order = new WC_Order( $order_id );
		$user = new WP_User( $order->get_user_id() );
		$this->check_payment_method_conversion( $user->user_login, $user->ID );

		// Convert CC expiration date from (M)M-YYYY to MMYY
		$expmonth = parent::get_post( 'expmonth' );
		$expyear = '';
		if ( $expmonth < 10 )
			$expmonth = '0' . $expmonth;
		if ( $this->get_post( 'expyear' ) != null )
			$expyear = substr( $this->get_post( 'expyear' ), -2 );


		// Create server request using stored or new payment details
		if ( $this->get_post( 'inspire-use-stored-payment-info' ) == 'yes' ) {

			// Short request, use stored billing details
			$customer_vault_ids = get_user_meta( $user->ID, 'customer_vault_ids', true );
			$id = $customer_vault_ids[ $this->get_post( 'inspire-payment-method' ) ];
			if( substr( $id, 0, 1 ) !== '_' ) $base_request['customer_vault_id'] = $id;
			else {
				$base_request['customer_vault_id'] = $user->user_login;
				$base_request['billing_id']        = substr( $id , 1 );
				$base_request['ver']               = 2;
			}

		} else {

			// Full request, new customer or new information
			$base_request = array (
				'ccnumber' 	=> $this->get_post( 'ccnum' ),
				'cvv' 		=> $this->get_post( 'cvv' ),
				'ccexp' 	=> $expmonth . $expyear,
				'firstname' => $order->billing_first_name,
				'lastname' 	=> $order->billing_last_name,
				'address1' 	=> $order->billing_address_1,
				'city' 	    => $order->billing_city,
				'state' 	=> $order->billing_state,
				'zip' 		=> $order->billing_postcode,
				'country' 	=> $order->billing_country,
				'phone' 	=> $order->billing_phone,
				'email'     => $order->billing_email,
			);

			$base_request['customer_vault'] = 'add_customer';

			// Generate a new customer vault id for the payment method
			$new_customer_vault_id = $this->random_key();

			// Set customer ID for new record
			$base_request['customer_vault_id'] = $new_customer_vault_id;

			// Set 'recurring' flag for subscriptions
			$base_request['billing_method'] = 'recurring';

		}

		// Add transaction-specific details to the request
		$transaction_details = array (
			'username'  => $this->username,
			'password'  => $this->password,
			'amount' 	=> $order->order_total,
			'type' 		=> $this->salemethod,
			'payment' 	=> 'creditcard',
			'orderid' 	=> $order->get_order_number(),
			'ipaddress' => $_SERVER['REMOTE_ADDR'],
		);

		// Send request and get response from server
		$response = $this->post_and_get_response( array_merge( $base_request, $transaction_details ) );

		// Check response
		if ( $response['response'] == 1 ) {

			// Success
			$order->add_order_note( __( 'Inspire Commerce payment completed. Transaction ID: ' , 'woocommerce-gateway-inspire' ) . $response['transactionid'] );
			$order->payment_complete();

			// Store the payment method number/customer vault ID translation table in the user's metadata
			$customer_vault_ids = get_user_meta( $user->ID, 'customer_vault_ids', true );
			$customer_vault_ids[] = $new_customer_vault_id;
			update_user_meta( $user->ID, 'customer_vault_ids', $customer_vault_ids );

			// Store payment method number for future subscription payments
			update_post_meta( $order->id, 'payment_method_number', count( $customer_vault_ids ) - 1 );
			update_post_meta( $order->id, 'transactionid', $response['transactionid'] );

			// Return thank you redirect
			return array (
				'result'   => 'success',
				'redirect' => $this->get_return_url( $order ),
			);

		} else if ( $response['response'] == 2 ) {

			// Decline
			$order->add_order_note( __( 'Inspire Commerce payment failed. Payment declined.', 'woocommerce-gateway-inspire' ) );
			wc_add_notice( __( 'Sorry, the transaction was declined.', 'woocommerce-gateway-inspire' ), $notice_type = 'error' );

		} else if ( $response['response'] == 3 ) {

			// Other transaction error
			$order->add_order_note( __( 'Inspire Commerce payment failed. Error: ', 'woocommerce-gateway-inspire' ) . $response['responsetext'] );
			wc_add_notice( __( 'Sorry, there was an error: ', 'woocommerce-gateway-inspire' ) . $response['responsetext'], $notice_type = 'error' );

		} else {

			// No response or unexpected response
			$order->add_order_note( __( "Inspire Commerce payment failed. Couldn't connect to gateway server.", 'woocommerce-gateway-inspire' ) );
			wc_add_notice( __( 'No response from payment gateway server. Try again later or contact the site administrator.', 'woocommerce-gateway-inspire' ), $notice_type = 'error' );

		}

		return array();

	}

	/**
	 * scheduled_subscription_payment function.
	 *
	 * @param float $amount_to_charge  The amount to charge.
	 * @param WC_Order $renewal_order A WC_Order object created to record the renewal payment.
	 * @access public
	 * @return void
	 */
	public function scheduled_subscription_payment( $amount_to_charge, $renewal_order ) {
		$response = $this->process_subscription_payment( $renewal_order, $amount_to_charge );

		if ( is_wp_error( $response ) ) {
			$renewal_order->update_status( 'failed', sprintf( __( 'Inspire Transaction Failed (%s)', 'woocommerce-gateway-inspire' ), $response->get_error_message() ) );
		}
	}

	/**
	 * process_subscription_payment function.
	 *
	 * @access public
	 * @param WC_Order $order
	 * @param int $amount (default: 0)
	 * @return string|WP_Error
	 */
	public function process_subscription_payment( $order, $amount = 0 ) {

		$user = new WP_User( $order->get_user_id() );
		$this->check_payment_method_conversion( $user->user_login, $user->ID );
		$customer_vault_ids = get_user_meta( $user->ID, 'customer_vault_ids', true );
		$payment_method_number = get_post_meta( $order->id, 'payment_method_number', true );

		$inspire_request = array (
			'username' 		    => $this->username,
			'password' 	      	=> $this->password,
			'amount' 		    => $amount,
			'type' 			    => $this->salemethod,
			'billing_method'    => 'recurring',
		);

		$id = $customer_vault_ids[ $payment_method_number ];
		if( substr( $id, 0, 1 ) !== '_' ) {
			$inspire_request['customer_vault_id'] = $id;
		} else {
			$inspire_request['customer_vault_id'] = $user->user_login;
			$inspire_request['billing_id']        = substr( $id , 1 );
			$inspire_request['ver']               = 2;
		}

		$response = $this->post_and_get_response( $inspire_request );

		if ( $response['response'] == 1 ) {
			// Success
			$order->add_order_note( __( 'Inspire Commerce scheduled subscription payment completed. Transaction ID: ' , 'woocommerce-gateway-inspire' ) . $response['transactionid'] );
			WC_Subscriptions_Manager::process_subscription_payments_on_order( $order );

		} else if ( $response['response'] == 2 ) {
			// Decline
			$order->add_order_note( __( 'Inspire Commerce scheduled subscription payment failed. Payment declined.', 'woocommerce-gateway-inspire') );
			WC_Subscriptions_Manager::process_subscription_payment_failure_on_order( $order );

		} else if ( $response['response'] == 3 ) {
			// Other transaction error
			$order->add_order_note( __( 'Inspire Commerce scheduled subscription payment failed. Error: ', 'woocommerce-gateway-inspire') . $response['responsetext'] );
			WC_Subscriptions_Manager::process_subscription_payment_failure_on_order( $order );

		} else {
			// No response or unexpected response
			$order->add_order_note( __('Inspire Commerce scheduled subscription payment failed. Couldn\'t connect to gateway server.', 'woocommerce-gateway-inspire') );

		}
	}

}
