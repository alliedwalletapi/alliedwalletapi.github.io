<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC_Gateway_Aw class.
 *
 * @since 2.0.0
 * @extends WC_Payment_Gateway
 */
class WC_Gateway_Aw extends WC_Payment_Gateway {
	
	/** @var string user name */
	var $username;

	/** @var string password */
	var $password;

	/** @var string require CVV at checkout */
	var $cvv;

	/** @var string sale method */
	var $salemethod;

	/** @var  string save debug information */
	var $debug;

	/**
	 * Constructor
	 */
	public function __construct() {
		// Register plugin information
		$this->id         = 'aw';
		$this->has_fields = true;
		$this->supports   = array(
			'products',
			'subscriptions',
			'subscription_cancellation',
			'subscription_suspension',
			'subscription_reactivation',
			'subscription_amount_changes',
			'subscription_date_changes',
			'subscription_payment_method_change',
			'refunds'
		);

		// Create plugin fields and settings
		$this->init_form_fields();
		$this->init_settings();

		// Get setting values
		foreach ( $this->settings as $key => $val ) $this->$key = $val;

		// Load plugin checkout icon
		$this->icon = WC_AW_PLUGIN_URL . '/images/cards.png';

		// Add hooks
		add_action( 'admin_notices',                                            array( $this, 'aw_commerce_ssl_check' ) );
		add_action( 'woocommerce_before_my_account',                            array( $this, 'add_payment_method_options' ) );
		add_action( 'woocommerce_receipt_aw',                              array( $this, 'receipt_page' ) );
		add_action( 'woocommerce_update_options_payment_gateways',              array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

	}

	/**
	 * Process a refund if supported
	 *
	 * @param  int $order_id
	 * @param  float $amount
	 * @param  string $reason
	 * @return  bool|wp_error True or false based on success, or a WP_Error object
	 */
	public function process_refund( $order_id, $amount = null, $reason = '' ) {

		$order          = wc_get_order( $order_id );
		$transaction_id = get_post_meta( $order_id, 'transactionid', true );

		if ( ! $transaction_id ) {

			// pre 2.0.0 order, so get transaction id from the order notes
			$args = array(
				'post_id' => $order->id,
				'approve' => 'approve',
				'type'    => ''
			);

			remove_filter( 'comments_clauses', array( 'WC_Comments', 'exclude_order_comments' ) );

			$comments = get_comments( $args );

			foreach ( $comments as $comment ) {
				if ( strpos( $comment->comment_content, 'Transaction ID: ' ) !== false ) {
					$exploded_comment = explode( ": ", $comment->comment_content );
					$transaction_id   = $exploded_comment[1];
				}
			}

			add_filter( 'comments_clauses', array( 'WC_Comments', 'exclude_order_comments' ) );

		}

		if ( ! $order || ! $transaction_id ) {
			return false;
		}

		// Add transaction-specific details to the request
		$transaction_details = array (
			'username'      => $this->username,
			'password'      => $this->password,
			'type'          => 'refund',
			'transactionid' => $transaction_id,
			'ipaddress'     => $_SERVER['REMOTE_ADDR'],
		);

		if ( ! is_null( $amount ) ) {
			$transaction_details['amount'] = number_format( $amount, 2, '.', '' );
		}

		// Send request and get response from server
		$response = $this->post_and_get_response( $transaction_details );

		// Check response
		if ( $response['response'] == 1 ) {
			// Success
			$order->add_order_note( __( 'Aw Commerce refund completed. Refund Transaction ID: ' , 'woocommerce-gateway-aw' ) . $response['transactionid'] );
			return true;
		} else {
			// Failure
			$order->add_order_note( __( 'Aw Commerce refund error. Response data: ' , 'woocommerce-gateway-aw' ) . http_build_query($response));
			return false;
		}
	}


	/**
	 * Check if SSL is enabled and notify the user.
	 */
	function aw_commerce_ssl_check() {
		if ( get_option( 'woocommerce_force_ssl_checkout' ) == 'no' && $this->enabled == 'yes' ) {
			$admin_url = admin_url( 'admin.php?page=wc-settings&tab=checkout' );
			echo '<div class="error"><p>' . sprintf( __('Aw Commerce is enabled and the <a href="%s">force SSL option</a> is disabled; your checkout is not secure! Please enable SSL and ensure your server has a valid SSL certificate.', 'woocommerce-gateway-aw' ), $admin_url ) . '</p></div>';
		}
	}

	/**
	 * Initialize Gateway Settings Form Fields.
	 */
	function init_form_fields() {

		$this->form_fields = array(
			'enabled'     => array(
				'title'       => __( 'Enable/Disable', 'woocommerce-gateway-aw' ),
				'label'       => __( 'Enable Aw Commerce', 'woocommerce-gateway-aw' ),
				'type'        => 'checkbox',
				'description' => '',
				'default'     => 'no'
			),
			'title'       => array(
				'title'       => __( 'Title', 'woocommerce-gateway-aw' ),
				'type'        => 'text',
				'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce-gateway-aw' ),
				'default'     => __( 'Credit Card (Allied Wallet)', 'woocommerce-gateway-aw' )
			),
			'description' => array(
				'title'       => __( 'Description', 'woocommerce-gateway-aw' ),
				'type'        => 'textarea',
				'description' => __( 'This controls the description which the user sees during checkout.', 'woocommerce-gateway-aw' ),
				'default'     => 'Pay with your credit card via AlliedWallet.'
			),
			'mid'    => array(
				'title'       => __( 'MID', 'woocommerce-gateway-aw' ),
				'type'        => 'text',
				'description' => __( 'This is the API MerchantID generated within the AlliedWallet gateway.', 'woocommerce-gateway-aw' ),
				'default'     => ''
			),
            'token'    => array(
				'title'       => __( 'Token', 'woocommerce-gateway-aw' ),
				'type'        => 'text',
				'description' => __( 'This is the API Token generated within the AlliedWallet gateway.', 'woocommerce-gateway-aw' ),
				'default'     => ''
			),
			'sid'    => array(
				'title'       => __( 'SID', 'woocommerce-gateway-aw' ),
				'type'        => 'text',
				'description' => __( 'This is the API SiteID generated within the AlliedWallet gateway.', 'woocommerce-gateway-aw' ),
				'default'     => ''
			),
			
			'cardtypes'   => array(
				'title'       => __( 'Accepted Cards', 'woocommerce-gateway-aw' ),
				'type'        => 'multiselect',
				'description' => __( 'Select which card types to accept.', 'woocommerce-gateway-aw' ),
				'default'     => '',
				'options'     => array(
					'MasterCard' => 'MasterCard',
					'Visa' => 'Visa',
					'Discover' => 'Discover',
					'American Express' => 'American Express'
				),
			
			'debug'    => array(
				'title'       => __( 'Debug', 'woocommerce-gateway-aw' ),
				'type'        => 'checkbox',
				'label'       => __( 'Write information to a debug log.', 'woocommerce-gateway-aw' ),
				'description' => __( 'The log will be available via WooCommerce > System Status on the Logs tab with a name starting with \'aw\'', 'woocommerce-gateway-aw' ),
				'default'     => 'no'
			),

		);
	}


	/**
	 * UI - Admin Panel Options
	 */
	function admin_options() { ?>
		<h3><?php _e( 'Aw Commerce','woocommerce-gateway-aw' ); ?></h3>
		<p><?php _e( 'AlliedWallet.', 'woocommerce-gateway-aw' ); ?></p>
		<table class="form-table">
			<?php $this->generate_settings_html(); ?>
		</table>
		<?php
	}

	/**
	 * UI - Payment page fields for Aw Commerce.
	 */
	function payment_fields() {
		// Description of payment method from settings
		if ( $this->description ) { ?>
			<p><?php echo $this->description; ?></p>
		<?php } ?>
		<fieldset  style="padding-left: 40px;">
			<?php
			$user = wp_get_current_user();
			$this->check_payment_method_conversion( $user->user_login, $user->ID );
			if ( $this->user_has_stored_data( $user->ID ) ) { ?>
				<fieldset>
					<input type="radio" name="aw-use-stored-payment-info" id="aw-use-stored-payment-info-yes" value="yes" checked="checked" onclick="document.getElementById('aw-new-info').style.display='none'; document.getElementById('aw-stored-info').style.display='block'"; /><label for="aw-use-stored-payment-info-yes" style="display: inline;"><?php _e( 'Use a stored credit card', 'woocommerce-gateway-aw' ) ?></label>
					<div id="aw-stored-info" style="padding: 10px 0 0 40px; clear: both;">
						<?php
						$i = 0;
						$method = $this->get_payment_method( $i );
						while( $method != null ) {
							?>
							<p>
								<input type="radio" name="aw-payment-method" id="<?php echo $i; ?>" value="<?php echo $i; ?>" <?php if($i == 0){echo 'checked';}?>/> &nbsp;
								<?php echo $method->cc_number; ?> (<?php
								$exp = $method->cc_exp;
								echo substr( $exp, 0, 2 ) . '/' . substr( $exp, -2 );
								?>)
								<br />
							</p>
							<?php
							$method = $this->get_payment_method( ++$i );
						} ?>
				</fieldset>
				<fieldset>
					<p>
						<input type="radio" name="aw-use-stored-payment-info" id="aw-use-stored-payment-info-no" value="no" onclick="document.getElementById('aw-stored-info').style.display='none'; document.getElementById('aw-new-info').style.display='block'"; />
						<label for="aw-use-stored-payment-info-no"  style="display: inline;"><?php _e( 'Use a new payment method', 'woocommerce-gateway-aw' ) ?></label>
					</p>
					<div id="aw-new-info" style="display:none">
				</fieldset>
			<?php } else { ?>
			<fieldset>
				<!-- Show input boxes for new data -->
				<div id="aw-new-info">
					<?php } ?>
					<!-- Credit card number -->
					<p class="form-row form-row-first">
						<label for="ccnum"><?php echo __( 'Credit Card number', 'woocommerce-gateway-aw' ) ?> <span class="required">*</span></label>
						<input type="text" class="input-text" id="ccnum" name="ccnum" maxlength="16" />
					</p>
					<!-- Credit card type -->
					<p class="form-row form-row-last">
						<label for="cardtype"><?php echo __( 'Card type', 'woocommerce-gateway-aw' ) ?> <span class="required">*</span></label>
						<select name="cardtype" id="cardtype" class="woocommerce-select">
							<?php  foreach( $this->cardtypes as $type ) { ?>
								<option value="<?php echo $type ?>"><?php _e( $type, 'woocommerce-gateway-aw' ); ?></option>
							<?php } ?>
						</select>
					</p>
					<div class="clear"></div>
					<!-- Credit card expiration -->
					<p class="form-row form-row-first">
						<label for="expmonth"><?php echo __( 'Expiration date', 'woocommerce-gateway-aw') ?> <span class="required">*</span></label>
						<select name="expmonth" id="expmonth" class="woocommerce-select woocommerce-cc-month">
							<option value=""><?php _e( 'Month', 'woocommerce-gateway-aw' ) ?></option><?php
							$months = array();
							for ( $i = 1; $i <= 12; $i ++ ) {
								$timestamp = mktime( 0, 0, 0, $i, 1 );
								$months[ date( 'n', $timestamp ) ] = date( 'F', $timestamp );
							}
							foreach ( $months as $num => $name ) {
								printf( '<option value="%u">%s</option>', $num, $name );
							} ?>
						</select>
						<select name="expyear" id="expyear" class="woocommerce-select woocommerce-cc-year">
							<option value=""><?php _e( 'Year', 'woocommerce-gateway-aw' ) ?></option><?php
							$years = array();
							for ( $i = date( 'y' ); $i <= date( 'y' ) + 15; $i ++ ) {
								printf( '<option value="20%u">20%u</option>', $i, $i );
							} ?>
						</select>
					</p>
					<?php

					// Credit card security code
					if ( $this->cvv == 'yes' ) { ?>
						<p class="form-row form-row-last">
						<label for="cvv"><?php _e( 'Card security code', 'woocommerce-gateway-aw' ) ?> <span class="required">*</span></label>
						<input oninput="validate_cvv(this.value)" type="text" class="input-text" id="cvv" name="cvv" maxlength="4" style="width:45px" />
						<span class="help"><?php _e( '3 or 4 digits usually found on the signature strip.', 'woocommerce-gateway-aw' ) ?></span>
						</p><?php
					}

					// Option to store credit card data
					if ( $this->saveinfo == 'yes' && ! ( class_exists( 'WC_Subscriptions_Cart' ) && WC_Subscriptions_Cart::cart_contains_subscription() ) ) { ?>
						<div style="clear: both;"></div>
						<p>
							<label for="saveinfo"><?php _e( 'Save this billing method?', 'woocommerce-gateway-aw' ) ?></label>
							<input type="checkbox" class="input-checkbox" id="saveinfo" name="saveinfo" />
							<span class="help"><?php _e( 'Select to store your billing information for future use.', 'woocommerce-gateway-aw' ) ?></span>
						</p>
					<?php  } ?>
			</fieldset>
		</fieldset>
		<?php
	}

	/**
	 * Process the payment and return the result.
	 *
	 * @param int @order_id
	 * @return array
	 */
	function process_payment( $order_id ) {

		$new_customer_vault_id = '';
		$order = new WC_Order( $order_id );
		$user = new WP_User( $order->get_user_id() );
		$this->check_payment_method_conversion( $user->user_login, $user->ID );

		// Convert CC expiration date from (M)M-YYYY to MMYY
		$expmonth = $this->get_post( 'expmonth' );
		$expyear  = '';
		if ( $expmonth < 10 )
			$expmonth = '0' . $expmonth;
		if ( $this->get_post( 'expyear' ) != null )
			$expyear = substr( $this->get_post( 'expyear' ), -2 );


		// Create server request using stored or new payment details
		if ( $this->get_post( 'aw-use-stored-payment-info' ) == 'yes' ) {

			// Short request, use stored billing details
			$customer_vault_ids = get_user_meta( $user->ID, 'customer_vault_ids', true );
			$id = $customer_vault_ids[ $this->get_post( 'aw-payment-method' ) ];
			if( substr( $id, 0, 1 ) !== '_' ) {
				$base_request['customer_vault_id'] = $id;
			} else {
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

			// If "save billing data" box is checked or order is a subscription, also request storage of customer payment information.
			if ( $this->get_post( 'saveinfo' ) ) {

				$base_request['customer_vault'] = 'add_customer';

				// Generate a new customer vault id for the payment method
				$new_customer_vault_id = $this->random_key();

				// Set customer ID for new record
				$base_request['customer_vault_id'] = $new_customer_vault_id;

			}
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
			$order->add_order_note( __( 'Aw Commerce payment completed. Transaction ID: ' , 'woocommerce-gateway-aw' ) . $response['transactionid'] );
			$order->payment_complete();

			update_post_meta( $order->id, 'transactionid', $response['transactionid'] );

			if ( $this->get_post( 'saveinfo' ) ) {

				// Store the payment method number/customer vault ID translation table in the user's metadata
				$customer_vault_ids = get_user_meta( $user->ID, 'customer_vault_ids', true );
				$customer_vault_ids[] = $new_customer_vault_id;
				update_user_meta( $user->ID, 'customer_vault_ids', $customer_vault_ids );

			}

			// Return thank you redirect
			return array (
				'result'   => 'success',
				'redirect' => $this->get_return_url( $order ),
			);

		} else if ( $response['response'] == 2 ) {
			// Decline
			$order->add_order_note( __( 'Aw Commerce payment failed. Payment declined.', 'woocommerce-gateway-aw' ) );
			wc_add_notice( __( 'Sorry, the transaction was declined.', 'woocommerce-gateway-aw' ), $notice_type = 'error' );

		} else if ( $response['response'] == 3 ) {
			// Other transaction error
			$order->add_order_note( __( 'Aw Commerce payment failed. Error: ', 'woocommerce-gateway-aw' ) . $response['responsetext'] );
			wc_add_notice( __( 'Sorry, there was an error: ', 'woocommerce-gateway-aw' ) . $response['responsetext'], $notice_type = 'error' );

		} else {
			// No response or unexpected response
			$order->add_order_note( __( "Aw Commerce payment failed. Couldn't connect to gateway server.", 'woocommerce-gateway-aw' ) );
			wc_add_notice( __( 'No response from payment gateway server. Try again later or contact the site administrator.', 'woocommerce-gateway-aw' ), $notice_type = 'error' );

		}

		return array();

	}



	/**
	 * Get details of a payment method for the current user from the Customer Vault
	 *
	 * @param $payment_method_number
	 *
	 * @return null
	 */
	function get_payment_method( $payment_method_number ) {

		if( $payment_method_number < 0 ) die( 'Invalid payment method: ' . $payment_method_number );

		$user = wp_get_current_user();
		$customer_vault_ids = get_user_meta( $user->ID, 'customer_vault_ids', true );
		if( $payment_method_number >= count( $customer_vault_ids ) ) return null;

		$query = array (
			'username' 		      => $this->username,
			'password' 	      	=> $this->password,
			'report_type'       => 'customer_vault',
		);

		$id = $customer_vault_ids[ $payment_method_number ];
		if( substr( $id, 0, 1 ) !== '_' ) $query['customer_vault_id'] = $id;
		else {
			$query['customer_vault_id'] = $user->user_login;
			$query['billing_id']        = substr( $id , 1 );
			$query['ver']               = 2;
		}
		$response = wp_remote_post( QUERY_URL, array(
				'body'  => $query,
				'timeout' => 45,
				'redirection' => 5,
				'httpversion' => '1.0',
				'blocking' => true,
				'headers' => array(),
				'cookies' => array(),
				'ssl_verify' => false
			)
		);

		//Do we have an error?
		if( is_wp_error( $response ) ) return null;

		// Check for empty response, which means method does not exist
		if ( trim( strip_tags( $response['body'] ) ) == '' ) return null;

		// Format result
		$content = simplexml_load_string( $response['body'] )->customer_vault->customer;
		if( substr( $id, 0, 1 ) === '_' ) $content = $content->billing;

		return $content;
	}

	/**
	 * Check if a user's stored billing records have been converted to Single Billing. If not, do it now.
	 *
	 * @param $user_login
	 * @param $user_id
	 */
	function check_payment_method_conversion( $user_login, $user_id ) {
		if( ! $this->user_has_stored_data( $user_id ) && $this->get_mb_payment_methods( $user_login ) != null ) $this->convert_mb_payment_methods( $user_login, $user_id );
	}

	/**
	 * Convert any Multiple Billing records stored by the user into Single Billing records
	 *
	 * @param $user_login
	 * @param $user_id
	 */
	function convert_mb_payment_methods( $user_login, $user_id ) {

		$mb_methods = $this->get_mb_payment_methods( $user_login );
		foreach ( $mb_methods->billing as $method ) $customer_vault_ids[] = '_' . ( (string) $method['id'] );
		// Store the payment method number/customer vault ID translation table in the user's metadata
		add_user_meta( $user_id, 'customer_vault_ids', $customer_vault_ids );

		// Update subscriptions to reference the new records
		if( class_exists( 'WC_Subscriptions_Manager' ) ) {

			$payment_method_numbers = array_flip( $customer_vault_ids );
			foreach( (array) ( WC_Subscriptions_Manager::get_users_subscriptions( $user_id ) ) as $subscription ) {
				update_post_meta( $subscription['order_id'], 'payment_method_number', $payment_method_numbers[ '_' . get_post_meta( $subscription['order_id'], 'billing_id', true ) ] );
				delete_post_meta( $subscription['order_id'], 'billing_id' );
			}

		}
	}

	/**
	 * Get the user's Multiple Billing records from the Customer Vault
	 *
	 * @param $user_login
	 *
	 * @return null
	 */
	function get_mb_payment_methods( $user_login ) {

		if( $user_login == null ) return null;

		$query = array (
			'username' 		      => $this->username,
			'password' 	      	=> $this->password,
			'report_type'       => 'customer_vault',
			'customer_vault_id' => $user_login,
			'ver'               => '2',
		);
		$content = wp_remote_post( QUERY_URL, array(
				'body'  => $query,
				'timeout' => 45,
				'redirection' => 5,
				'httpversion' => '1.0',
				'blocking' => true,
				'headers' => array(),
				'cookies' => array(),
				'ssl_verify' => false
			)
		);

		if ( trim( strip_tags( $content['body'] ) ) == '' ) return null;
		return simplexml_load_string( $content['body'] )->customer_vault->customer;

	}

	/**
	 * Check payment details for valid format
	 *
	 * @return bool
	 */
	function validate_fields() {

		if ( $this->get_post( 'aw-use-stored-payment-info' ) == 'yes' ) return true;

		global $woocommerce;

		// Check for saving payment info without having or creating an account
		if ( $this->get_post( 'saveinfo' )  && ! is_user_logged_in() && ! $this->get_post( 'createaccount' ) ) {
			wc_add_notice( __( 'Sorry, you need to create an account in order for us to save your payment information.', 'woocommerce-gateway-aw'), $notice_type = 'error' );
			return false;
		}

		$cardType            = $this->get_post( 'cardtype' );
		$cardNumber          = $this->get_post( 'ccnum' );
		$cardCSC             = $this->get_post( 'cvv' );
		$cardExpirationMonth = $this->get_post( 'expmonth' );
		$cardExpirationYear  = $this->get_post( 'expyear' );

		// Check card number
		if ( empty( $cardNumber ) || ! ctype_digit( $cardNumber ) ) {
			wc_add_notice( __( 'Card number is invalid.', 'woocommerce-gateway-aw' ), $notice_type = 'error' );
			return false;
		}

		if ( $this->cvv == 'yes' ){
			// Check security code
			if ( ! ctype_digit( $cardCSC ) ) {
				wc_add_notice( __( 'Card security code is invalid (only digits are allowed).', 'woocommerce-gateway-aw' ), $notice_type = 'error' );
				return false;
			}
			if ( ( strlen( $cardCSC ) != 3 && in_array( $cardType, array( 'Visa', 'MasterCard', 'Discover' ) ) ) || ( strlen( $cardCSC ) != 4 && $cardType == 'American Express' ) ) {
				wc_add_notice( __( 'Card security code is invalid (wrong length).', 'woocommerce-gateway-aw' ), $notice_type = 'error' );
				return false;
			}
		}

		// Check expiration data
		$currentYear = date( 'Y' );

		if ( ! ctype_digit( $cardExpirationMonth ) || ! ctype_digit( $cardExpirationYear ) ||
		     $cardExpirationMonth > 12 ||
		     $cardExpirationMonth < 1 ||
		     $cardExpirationYear < $currentYear ||
		     $cardExpirationYear > $currentYear + 20
		) {
			wc_add_notice( __( 'Card expiration date is invalid', 'woocommerce-gateway-aw' ), $notice_type = 'error' );
			return false;
		}

		// Strip spaces and dashes
		$cardNumber = str_replace( array( ' ', '-' ), '', $cardNumber );

		return true;

	}

	/**
	 * Send the payment data to the gateway server and return the response.
	 *
	 * @param $request
	 *
	 * @return null
	 */
	protected function post_and_get_response( $request ) {

		// Encode request
		$post = http_build_query( $request, '', '&' );

		if ( $this->debug == 'yes' ) {
			$log = new WC_Logger();
			if ( isset( $request['ccnumber'] ) ) {
				$request['ccnumber'] = 'xxxx' . substr( $request['ccnumber'], - 4 );
			}
			$log->add( 'aw', "Request: " . print_r( $request, true ) );
		}

		// Send request
		$content = wp_remote_post( GATEWAY_URL, array(
				'body'  => $post,
				'timeout' => 45,
				'redirection' => 5,
				'httpversion' => '1.0',
				'blocking' => true,
				'headers' => array(),
				'cookies' => array(),
				'ssl_verify' => false
			)
		);
		if ( $this->debug == 'yes' ){
			$log = new WC_Logger();
			$log->add( 'aw', "Response: " . print_r( $content, true ) );
		}

		// Quit if it didn't work
		if ( is_wp_error( $content ) ) {
			wc_add_notice( __( 'Problem connecting to server at ', 'woocommerce-gateway-aw' ) . GATEWAY_URL . ' ( ' . $content->get_error_message() . ' )', $notice_type = 'error' );
			return null;
		}

		// Convert response string to array
		$vars = explode( '&', $content['body'] );
		foreach ( $vars as $key => $val ) {
			$var = explode( '=', $val );
			$data[ $var[0] ] = $var[1];
		}

		// Return response array
		return $data;

	}

	/**
	 * Add ability to view and edit payment details on the My Account page.(The WooCommerce 'force ssl' option also secures the My Account
	 * page, so we don't need to do that.)
	 */
	function add_payment_method_options() {

		$user = wp_get_current_user();
		$this->check_payment_method_conversion( $user->user_login, $user->ID );
		$method_to_update = '';
		$method_to_delete = '';

		if ( ! $this->user_has_stored_data( $user->ID ) ) return;

		if( $this->get_post( 'delete' ) != null ) {

			$method_to_delete = $this->get_post( 'delete' );
			$response = $this->delete_payment_method( $method_to_delete );

		} else if( $this->get_post( 'update' ) != null ) {

			$method_to_update = $this->get_post( 'update' );
			$ccnumber = $this->get_post( 'edit-cc-number-' . $method_to_update );

			if ( empty( $ccnumber ) || ! ctype_digit( $ccnumber ) ) {

				global $woocommerce;
				wc_add_notice( __( 'Card number is invalid.', 'woocommerce-gateway-aw' ), $notice_type = 'error' );

			} else {

				$ccexp = $this->get_post( 'edit-cc-exp-' . $method_to_update );
				$expmonth = substr( $ccexp, 0, 2 );
				$expyear = substr( $ccexp, -2 );
				$currentYear = substr( date( 'Y' ), -2);

				if( empty( $ccexp ) || ! ctype_digit( str_replace( '/', '', $ccexp ) ) ||
				    $expmonth > 12 || $expmonth < 1 ||
				    $expyear < $currentYear || $expyear > $currentYear + 20 )
				{

					global $woocommerce;
					wc_add_notice( __( 'Card expiration date is invalid', 'woocommerce-gateway-aw' ), $notice_type = 'error' );

				} else {

					$response = $this->update_payment_method( $method_to_update, $ccnumber, $ccexp );

				}
			}
		}

		?>

		<h2><?php echo __('Saved Payment Methods', 'woocommerce-gateway-aw' ); ?></h2>
		<p><?php echo __('This information is stored to save time at the checkout and to pay for subscriptions.', 'woocommerce-gateway-aw' ); ?></p>

		<?php $i = 0;
		$current_method = $this->get_payment_method( $i );
		while( $current_method != null ) {

			if( $method_to_delete === $i && $response['response'] == 1 ) { $method_to_delete = null; continue; } // Skip over a deleted entry ?>

			<header class="title">

			<h3><?php echo __('Payment Method', 'woocommerce-gateway-aw'); ?> <?php echo $i + 1; ?></h3>
			<p>

				<button style="float:right" class="button" id="unlock-delete-button-<?php echo $i; ?>"><?php _e( 'Delete', 'woocommerce-gateway-aw' ); ?></button>

				<button style="float:right; display:none" class="button" id="cancel-delete-button-<?php echo $i; ?>"><?php _e( 'No', 'woocommerce-gateway-aw' ); ?></button>
				<form action="<?php echo get_permalink( wc_get_page_id( 'myaccount' ) ) ?>" method="post" style="float:right" >
					<input type="submit" value="<?php _e( 'Yes', 'woocommerce-gateway-aw' ); ?>" class="button alt" id="delete-button-<?php echo $i; ?>" style="display:none">
					<input type="hidden" name="delete" value="<?php echo $i ?>">
				</form>
				<span id="delete-confirm-msg-<?php echo $i; ?>" style="float:left; display:none"><?php echo __('Are you sure? (Subscriptions purchased with this card will be canceled.)', 'woocommerce-gateway-aw'); ?>&nbsp;</span>

				<button style="float:right" class="button" id="edit-button-<?php echo $i; ?>" ><?php _e( 'Edit', 'woocommerce-gateway-aw' ); ?></button>
				<button style="float:right; display:none" class="button" id="cancel-button-<?php echo $i; ?>" ><?php _e( 'Cancel', 'woocommerce-gateway-aw' ); ?></button>

				<form action="<?php echo get_permalink( wc_get_page_id( 'myaccount' ) ) ?>" method="post" >

					<input type="submit" value="<?php _e( 'Save', 'woocommerce-gateway-aw' ); ?>" class="button alt" id="save-button-<?php echo $i; ?>" style="float:right; display:none" >

					<span style="float:left"><?php echo __('Credit card:', 'woocommerce-gateway-aw'); ?>&nbsp;</span>
					<input type="text" style="display:none" id="edit-cc-number-<?php echo $i; ?>" name="edit-cc-number-<?php echo $i; ?>" maxlength="16" />
					<span id="cc-number-<?php echo $i; ?>">
                        <?php echo ( $method_to_update === $i && $response['response'] == 1 ) ? ( '<b>' . $ccnumber . '</b>' ) : $current_method->cc_number; ?>
                    </span>
					<br />

					<span style="float:left"><?php __('Expiration:', 'woocommerce-gateway-aw'); ?>&nbsp;</span>
					<input type="text" style="float:left; display:none" id="edit-cc-exp-<?php echo $i; ?>" name="edit-cc-exp-<?php echo $i; ?>" maxlength="5" value="MM/YY" />
					<span id="cc-exp-<?php echo $i; ?>">
					<?php echo ( $method_to_update === $i && $response['response'] == 1 ) ? ( '<b>' . $ccexp . '</b>' ) : substr( $current_method->cc_exp, 0, 2 ) . '/' . substr( $current_method->cc_exp, -2 ); ?>
					</span>

					<input type="hidden" name="update" value="<?php echo $i ?>">

				</form>

			</p>

			</header><?php

			$current_method = $this->get_payment_method( ++$i );

		}

	}

	/**
	 * Display information on the Thank You page
	 *
	 * @param $order
	 */
	function receipt_page( $order ) {
		echo '<p>' . __( 'Thank you for your order.', 'woocommerce-gateway-aw' ) . '</p>';
	}

	/**
	 * Update a stored billing record with new CC number and expiration
	 *
	 * @param $payment_method
	 * @param $ccnumber
	 * @param $ccexp
	 */
	function update_payment_method( $payment_method, $ccnumber, $ccexp ) {

		global $woocommerce;
		$user =  wp_get_current_user();
		$customer_vault_ids = get_user_meta( $user->ID, 'customer_vault_ids', true );
		$new_customer_vault_id = '';

		$id = $customer_vault_ids[ $payment_method ];
		if( substr( $id, 0, 1 ) == '_' ) {
			// Copy all fields from the Multiple Billing record
			$mb_method = $this->get_payment_method( $payment_method );
			$aw_request = (array) $mb_method[0];
			// Make sure values are strings
			foreach( $aw_request as $key => $val ) {
				$aw_request[ $key ] = "$val";
			}
			// Add a new record with the updated details
			$aw_request['customer_vault'] = 'add_customer';
			$new_customer_vault_id = $this->random_key();
			$aw_request['customer_vault_id'] = $new_customer_vault_id;
		} else {
			// Update existing record
			$aw_request['customer_vault'] = 'update_customer';
			$aw_request['customer_vault_id'] = $id;
		}

		$aw_request['username'] = $this->username;
		$aw_request['password'] = $this->password;
		// Overwrite updated fields
		$aw_request['cc_number'] = $ccnumber;
		$aw_request['cc_exp'] = $ccexp;

		$response = $this->post_and_get_response( $aw_request );

		if( $response ['response'] == 1 ) {
			if( substr( $id, 0, 1 ) === '_' ) {
				// Update references
				$customer_vault_ids[ $payment_method ] = $new_customer_vault_id;
				update_user_meta( $user->ID, 'customer_vault_ids', $customer_vault_ids );
			}
			wc_add_notice( __('Successfully updated your information!', 'woocommerce-gateway-aw'), $notice_type = 'success' );
		} else wc_add_notice( __( 'Sorry, there was an error: ', 'woocommerce-gateway-aw') . $response['responsetext'], $notice_type = 'error' );

	}

	/**
	 * Delete a stored billing method
	 *
	 * @param $payment_method
	 */
	function delete_payment_method( $payment_method ) {

		global $woocommerce;
		$user = wp_get_current_user();
		$customer_vault_ids = get_user_meta( $user->ID, 'customer_vault_ids', true );

		$id = $customer_vault_ids[ $payment_method ];
		// If method is Single Billing, actually delete the record
		if( substr( $id, 0, 1 ) !== '_' ) {

			$aw_request = array (
				'username' 		      => $this->username,
				'password' 	      	=> $this->password,
				'customer_vault'    => 'delete_customer',
				'customer_vault_id' => $id,
			);
			$response = $this->post_and_get_response( $aw_request );
			if( $response['response'] != 1 ) {
				wc_add_notice( __( 'Sorry, there was an error: ', 'woocommerce-gateway-aw') . $response['responsetext'], $notice_type = 'error' );
				return;
			}

		}

		$last_method = count( $customer_vault_ids ) - 1;

		// Update subscription references
		if( class_exists( 'WC_Subscriptions_Manager' ) ) {
			foreach( (array) ( wcs_get_users_subscriptions( $user->ID ) ) as $subscription ) {
				$subscription_payment_method = get_post_meta( $subscription->post->post_parent, 'payment_method_number', true );
				// Cancel subscriptions that were purchased with the deleted method
				if( $subscription_payment_method == $payment_method ) {
					delete_post_meta( $subscription->id, 'payment_method_number' );

					WC_Subscriptions_Manager::cancel_subscription( $user->ID, WC_Subscriptions_Manager::get_subscription_key( $subscription->id) );
				}
				else if( $subscription_payment_method == $last_method && $subscription->status != 'cancelled') {
					update_post_meta( $subscription->id, 'payment_method_number', $payment_method );
				}
			}
		}

		// Delete the reference by replacing it with the last method in the array
		if( $payment_method < $last_method ) $customer_vault_ids[ $payment_method ] = $customer_vault_ids[ $last_method ];
		unset( $customer_vault_ids[ $last_method ] );
		update_user_meta( $user->ID, 'customer_vault_ids', $customer_vault_ids );

		wc_add_notice( __('Successfully deleted your information!', 'woocommerce-gateway-aw'), $notice_type = 'success' );

	}

	/**
	 * Check if the user has any billing records in the Customer Vault
	 *
	 * @param $user_id
	 *
	 * @return bool
	 */
	function user_has_stored_data( $user_id ) {
		return get_user_meta( $user_id, 'customer_vault_ids', true ) != null;
	}

	/**
	 * Get post data if set
	 *
	 * @param string $name
	 * @return string|null
	 */
	protected function get_post( $name ) {
		if ( isset( $_POST[ $name ] ) ) {
			return $_POST[ $name ];
		}
		return null;
	}

	/**
	 * Generate a string of 36 alphanumeric characters to associate with each saved billing method.
	 *
	 * @return string
	 */
	function random_key() {

		$valid_chars = array( 'a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z','A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','0','1','2','3','4','5','6','7','8','9' );
		$key = '';
		for( $i = 0; $i < 36; $i ++ ) {
			$key .= $valid_chars[ mt_rand( 0, 61 ) ];
		}
		return $key;

	}

}
