<?php
/*
Plugin Name: WooCommerce Allied Wallet Nextgen Gateway
Author URI: http://alliedwallet.com
Version: 1.0
*/

add_action('plugins_loaded', 'woocommerce_gateway_alliedwallet_init', 0);

function woocommerce_gateway_alliedwallet_init() {

	if ( ! class_exists( 'WC_Payment_Gateway' ) ) return;
	
	/**
	 * Localisation
	 */
	load_plugin_textdomain( 'wc-gateway-alliedwallet', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

	define('ALLIEDWALLET_DIR', WP_PLUGIN_DIR . "/" . plugin_basename( dirname(__FILE__) ) . '/');

	/**
	 * Allied Wallet Gateway Class
	 **/
	class WC_Gateway_Allied_Wallet extends WC_Payment_Gateway {
			
		public function __construct() {

	        $this->id					= 'alliedwallet';
	        $this->method_title 		= __( 'Allied Wallet-NextGen', 'wc-gateway-alliedwallet' );
	        $this->method_description 	= __( 'Allied Wallet allows customers to checkout using an Allied Wallet account or credit card', 'wc-gateway-alliedwallet');
	        $this->icon 				= apply_filters('woocommerce_alliedwallet_icon', plugin_dir_url(__FILE__) . '/images/alliedwallet.png');
	        $this->has_fields 			= false;
	        //$this->liveurl 				= 'https://sale.alliedwallet.com/quickpay.aspx';
	        $this->liveurl 				= 'https://quickpay.alliedwallet.com';
			
			// Load the form fields.
			$this->init_form_fields();
			
			// Load the settings.
			$this->init_settings();
			
			// Define user set variables
			$this->enabled 			= $this->settings['enabled'];
			$this->title 			= $this->settings['title'];
			$this->description 		= $this->settings['description'];
			$this->QuickPayToken		= $this->settings['QuickPayToken'];
			$this->site_id			= $this->settings['site_id'];
			$this -> userid = $this -> settings['userid'];
			$this -> password = $this -> settings['password'];
			$this->debug			= $this->settings['debug'];
						
			// Log file
			if ($this->debug=='yes') $this->log = new WC_Logger();

			// Actions
			add_action( 'init', array( $this, 'check_pingback_response') );
			//add_action('valid-alliedwallet-pingback-request', array( $this, 'successful_request' ) );
					add_action('woocommerce_api_'.strtolower(get_class($this)), array(&$this, 'successful_request'));
			add_action('woocommerce_receipt_alliedwallet', array( $this, 'receipt_page' ) );
			add_action('woocommerce_update_options_payment_gateways', array( $this, 'process_admin_options' ) );
			add_action('woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options'));
			
			if ( !$this->is_valid_for_use() ) $this->enabled = false;
	    } 
	    
	     /**
		  * Check if this gateway is enabled and available in the user's country
		  *
		  * @since 1.0.0
		  * @return bool
		  */
	    function is_valid_for_use() {
	        if (!in_array(get_woocommerce_currency(), array('AUD', 'BRL', 'CAD', 'MXN', 'NZD', 'HKD', 'SGD', 'USD', 'EUR', 'JPY', 'TRY', 'NOK', 'CZK', 'DKK', 'HUF', 'ILS', 'MYR', 'PHP', 'PLN', 'SEK', 'CHF', 'TWD', 'THB', 'GBP', 'RMB'))) return false;
	
	        return true;
	    }
	    
		/**
		 * Admin Panel Options 
		 * Options for bits like 'title' and availability on a country-by-country basis
		 *
		 * @since 1.0.0
		 */
		public function admin_options() {
	    	?>
	    	<h3><?php _e('Allied Wallet QuickPay', 'woocommerce'); ?></h3>
	    	<p><?php _e('Allied Wallet QuickPay works by sending the user to Allied Wallet to enter their payment information.', 'woocommerce'); ?></p>
	    	<table class="form-table">
	    	<?php
	    		if ( $this->is_valid_for_use() ) :
	    	
	    			// Generate the HTML For the settings form.
	    			$this->generate_settings_html();
	    		
	    		else :
	    		
	    			?>
	            		<div class="inline error"><p><strong><?php _e( 'Gateway Disabled', 'woocommerce' ); ?></strong>: <?php _e( 'Allied Wallet does not support your store currency.', 'woocommerce' ); ?></p></div>
	        		<?php
	        		
	    		endif;
	    	?>
			</table><!--/.form-table-->
	    	<?php
	    } // End admin_options()
	    
		/**
	     * Initialise Gateway Settings Form Fields
	     */
	    function init_form_fields() {
	    
	    	$this->form_fields = array(
				'enabled' => array(
								'title' => __( 'Enable/Disable', 'wc-gateway-alliedwallet' ), 
								'type' => 'checkbox', 
								'label' => __( 'Enable Allied Wallet QuickPay', 'wc-gateway-alliedwallet' ), 
								'default' => 'yes'
							), 
				'title' => array(
								'title' => __( 'Title', 'wc-gateway-alliedwallet' ), 
								'type' => 'text', 
								'description' => __( 'This controls the title which the user sees during checkout.', 'wc-gateway-alliedwallet' ), 
								'default' => __( 'Allied Wallet', 'wc-gateway-alliedwallet' )
							),
				'description' => array(
								'title' => __( 'Description', 'wc-gateway-alliedwallet' ), 
								'type' => 'textarea', 
								'description' => __( 'This controls the description which the user sees during checkout.', 'wc-gateway-alliedwallet' ), 
								'default' => __("Pay via Allied Wallet; you can pay with your credit card if you don't have an Allied Wallet account", 'wc-gateway-alliedwallet')
							),
							
							
				'QuickPayToken' => array(
								'title' => __( 'Allied Wallet QuickPayToken', 'wc-gateway-alliedwallet' ), 
								'type' => 'textarea', 
								'description' => __( 'Your QuickPayToken from Allied Wallet.', 'wc-gateway-alliedwallet' ), 
								'default' => ''
							),
				'site_id' => array(
								'title' => __( 'Allied Wallet SiteID', 'wc-gateway-alliedwallet' ), 
								'type' => 'text', 
								'description' => __( 'Your SiteID from Allied Wallet.', 'wc-gateway-alliedwallet' ), 
								'default' => ''
							),
				'debug' => array(
								'title' => __( 'Debug Log', 'wc-gateway-alliedwallet' ), 
								'type' => 'checkbox', 
								'label' => __( 'Enable logging', 'wc-gateway-alliedwallet' ), 
								'default' => 'no',
								'description' => __( 'Log Allied Wallet events, such as pingback requests, inside <code>woocommerce/logs/alliedwallet.txt</code>', 'wc-gateway-alliedwallet' ),
							)
				);
	    
	    } // End init_form_fields()
	    
	    /**
		 * There are no payment fields for Allied Wallet, but we want to show the description if set.
		 **/
	    function payment_fields() {
	    	if ($this->description) echo wpautop(wptexturize($this->description));
	    }
	    
	    /**
		 * Get args for passing to Allied Wallet
		 *
		 * @param WC_Order $order
		 * @return array
		 **/
		function get_allied_wallet_args( $order ) {

			if ($this->debug=='yes') $this->log->add( 'alliedwallet', 'Generating payment form for order #' . $order->id . '. Confirm URL: ' . trailingslashit(home_url()).'?alliedwalletListener=alliedwallet_pingback');

			error_log( 'Generating payment form for order #' . $order->id . '. Confirm URL: ' . trailingslashit(home_url()).'?alliedwalletListener=alliedwallet_pingback');
			// Allied Wallet Args
			$allied_args = array(
								'QuickPayToken'					=> $this->QuickPayToken,
								'SiteID'						=> $this->site_id,
								'CurrencyID'					=> get_woocommerce_currency(),
								'ShippingRequired'				=> 'true',
								'NoMembership'					=> '1',
								'FirstName'						=> isset($order->billing_first_name) 	? $order->billing_first_name : '',
								'LastName'						=> isset($order->billing_last_name) 	? $order->billing_last_name : '',
								'Email'							=> isset($order->billing_email) 		? $order->billing_email : '',
								'Address'						=> isset($order->billing_address_1) 	? $order->billing_address_1 : '',
								'Address2'						=> isset($order->billing_address_2) 	? $order->billing_address_2 : '',
								'City'							=> isset($order->billing_city) 			? $order->billing_city : '',
								'Country'						=> isset($order->billing_country) 		? $order->billing_country : '',
								'PostalCode'					=> isset($order->billing_postcode) 		? $order->billing_postcode : '',
								'Phone'							=> isset($order->billing_phone) 		? $order->billing_phone : '',
								'State'							=> isset($order->billing_state) 		? $order->billing_state : '',
								'ApprovedURL'						=> $this->get_return_url( $order ),
								'DeclinedURL'					=> $order->get_cancel_order_url(),
								'ConfirmURL'					=> trailingslashit(home_url()).'?wc-api=WC_Gateway_Allied_Wallet',
								'MerchantReference'				=> $order->id . ":" . $order->order_key	,
								'AmountShipping'				=> $order->get_total_shipping(),
								'AmountTotal'					=> $order->get_total(),
							);

				// Cart Contents
				$item_loop = 0;
				if (sizeof($order->get_items())>0) : foreach ($order->get_items() as $item) :
					if ($item['qty']) :
						
						$item_name 	= $item['name'];
						$item_meta = new WC_Order_Item_Meta( $item['item_meta'] );

						if ($meta = $item_meta->display( true, true )) :
							$item_name .= ' ('.$meta.')';
						endif;

						$allied_args['ItemName['.$item_loop.']'] = $item_name;
						$allied_args['ItemDesc['.$item_loop.']'] = '';
						$allied_args['ItemQuantity['.$item_loop.']'] = $item['qty'];
						$allied_args['ItemAmount['.$item_loop.']'] = $order->get_line_subtotal( $item, false );

						$item_loop++;
						
					endif;
				endforeach; endif;
				
				// Tax
				if ($this->debug=='yes') $this->log->add( 'alliedwallet', 'TOTAL TAX: ' . $order->get_total_tax() );
				if ( $order->get_total_tax() > 0 ) {
					// add tax as line item 

					$allied_args['ItemName['.$item_loop.']'] = 'Order Tax';
					$allied_args['ItemDesc['.$item_loop.']'] = '';
					$allied_args['ItemQuantity['.$item_loop.']'] = 1;
					$allied_args['ItemAmount['.$item_loop.']'] = number_format($order->get_total_tax(), 2, '.', '');

					$item_loop++;
				}
				
				// Discount
				if ($this->debug=='yes') $this->log->add( 'alliedwallet', 'TOTAL DISCOUNT: ' . $order->get_total_discount() );
				if ( $order->get_total_discount() > 0 ) {
				 	// add discount as line item

					$allied_args['ItemName['.$item_loop.']'] = 'Order Discount';
					$allied_args['ItemDesc['.$item_loop.']'] = '';
					$allied_args['ItemQuantity['.$item_loop.']'] = 1;
					$allied_args['ItemAmount['.$item_loop.']'] = '-' . number_format($order->get_total_discount(), 4, '.', '');
				
				}
			
			if ($this->debug=='yes') $this->log->add( 'alliedwallet', "Sending Order Details:\n" . print_r( $allied_args, true ));
			
			$allied_args = apply_filters( 'woocommerce_alliedwallet_args', $allied_args );
			
			return $allied_args;
		}

		/**
		 * Generate the Allied Wallet button link
		 *
		 * @param $order_id
		 * @return string
		 */
	    function generate_alliedwallet_form( $order_id ) {

			$order = new WC_Order( $order_id );
			$allied_adr = $this->liveurl;		
			$allied_args = $this->get_allied_wallet_args( $order );
			$allied_args_array = array();
	
			foreach ($allied_args as $key => $value) {
				$allied_args_array[] = '<input type="hidden" name="'.esc_attr( $key ).'" value="'.esc_attr( $value ).'" />';
			}

			wc_enqueue_js('
				jQuery("body").block({ 
						message: "<img src=\"'.esc_url( WC()->plugin_url() ).'/assets/images/ajax-loader.gif\" alt=\"Redirecting&hellip;\" style=\"float:left; margin-right: 10px;\" />'.__('Thank you for your order. We are now redirecting you to Allied Wallet to make payment.', 'wc-gateway-alliedwallet').'",
						overlayCSS: 
						{ 
							background: "#fff", 
							opacity: 0.6 
						},
						css: { 
					        padding:        20, 
					        textAlign:      "center", 
					        color:          "#555", 
					        border:         "3px solid #aaa", 
					        backgroundColor:"#fff", 
					        cursor:         "wait",
					        lineHeight:		"32px"
					    } 
					});
				jQuery("#submit_allied_payment_form").click();
			');
			
			return '<form action="'.esc_url( $allied_adr ).'" method="post" id="allied_payment_form" target="_top">
					' . implode('', $allied_args_array) . '
					<input type="submit" class="button-alt" id="submit_allied_payment_form" value="'.__('Pay via Allied Wallet', 'woocommerce').'" /> <a class="button cancel" href="'.esc_url( $order->get_cancel_order_url() ).'">'.__('Cancel order &amp; restore cart', 'wc-gateway-alliedwallet').'</a>
				</form>';
			
		}
		
		/**
		 * Process the payment and return the result
		 **/
		function process_payment( $order_id ) {
			
			$order = new WC_Order( $order_id );

			return array(
				'result' 	=> 'success',
				'redirect'	=> $order->get_checkout_payment_url( true )
			//'redirect'	=> add_query_arg('order', $order->id, add_query_arg('key', $order->order_key, get_permalink(woocommerce_get_page_id('pay'))))
			);

		}
		
		/**
		 * receipt_page
		 **/
		function receipt_page( $order ) {
			
			$order_tmp = new WC_Order( $order );
			$order_tmp->add_order_note( __('Customer redirected to Allied Wallet. Waiting on Allied Wallet order pingback response.', 'wc-gateway-alliedwallet') );			
			
			echo '<p>'.__('Thank you for your order, please click the button below to pay with Allied Wallet.', 'wc-gateway-alliedwallet').'</p>';

			echo $this->generate_alliedwallet_form( $order );
	        
		}
		
		/**
		 * Check Allied Wallet Post back validity
		 *
		 * @return bool
		 **/
		function check_postback_request_is_valid() {

	    	// Get recieved values from post data
			$response = (array) stripslashes_deep( $_POST );
	        
	        if ($this->debug=='yes') $this->log->add( 'alliedwallet', 'Pingback Response: ' . print_r($response, true) );

			error_log('Pingback Response: ' . print_r($response, true));
	        
	        // check to see if the request was valid
	        if ( !is_wp_error($response) && isset($response['TransactionID']) && isset($response['MerchantReference']) ) {
	            if ($this->debug=='yes') $this->log->add( 'alliedwallet', 'Received valid response from Allied Wallet' );
	            return true;
	        }  
	        
	        if ($this->debug=='yes') :
	        	$this->log->add( 'alliedwallet', 'Received invalid response from Allied Wallet' );
	        	if (is_wp_error($response)) :
	        		$this->log->add( 'alliedwallet', 'Error response: ' . $response->get_error_message() );
	        	endif;
	        endif;
	        
	        return false;
	    }
		
		/**
		 * Check for Allied Wallet Postback Response
		 **/
		function check_pingback_response() {
				$order = new WC_Order( $order_id );
    $order->update_status( 'completed' );
	
			//if (isset($_GET['alliedwalletListener']) && $_GET['alliedwalletListener'] == 'alliedwallet_pingback'):
				
				@ob_clean();
				
	        	$_POST = stripslashes_deep($_POST);
	        
	        	//if ($this->check_postback_request_is_valid()) :
	        		
	        		//header('HTTP/1.1 200 OK');
	        		
	            	do_action("valid-alliedwallet-pingback-request", $_POST);
				
				/*else :

			        if ($this->debug=='yes') $this->log->add( 'alliedwallet', 'Allied Wallet Pingback Failure' );
					wp_die("Allied Wallet Pingback Request Failure");
				
	       		endif;

	       	endif;*/
	       	
				
		}
		
		/**
		 * Successful Payment!
		 **/
		function successful_request( $posted ) {
			$response = (array) stripslashes_deep( $_POST );
			// Custom holds post ID
		    if ( !empty($response['TrackingId']) && !empty($response['Amount']) ) {
		    
		    	$reference = split(":", $response['TrackingId']);
		    	
		        if ($this->debug=='yes') $this->log->add( 'aw', 'Processing Pingback for Merchant Reference: ' . print_r($reference, true));		
		        $order_id	= (int) $reference[0];
		        $order_key	= $reference[1];
		        	
				$order = new WC_Order( $order_id );
				
		        if ($order->order_key !== $order_key) :
		        	if ($this->debug=='yes') $this->log->add( 'aw', 'Error: Order Key does not match invoice.' );
		        	exit;
		        endif;
		        
		        // If we have a PayReferenceID and a TransactionID then the payment was successful
				if ( isset($response['TrackingId']) && isset($response['TransactionID']) && $response['TransactionStatus']=="Successful") {

	            	// Check order not already completed
	            	if ($order->status == 'completed') :
	            		 if ($this->debug=='yes') $this->log->add( 'aw', 'Aborting, Order #' . $order_id . ' is already complete.' );
	            		 exit;
	            	endif;
	            	
					 // Store Order details
	                
	                if ( ! empty( $posted['TransactionID'] ) ) 
	                	update_post_meta( $order_id, 'Allied Wallet Transaction ID', $response['TransactionID'] );
                    
                

	            	
	            	// Payment completed
	                $order->add_order_note( __('Allied Wallet payment completed', 'woocommerce') );
                    $order->update_status('completed', 'order_note');
	                $order->payment_complete();
	                
	                if ($this->debug=='yes') $this->log->add( 'aw', 'Payment complete.' );
	                
			
				} else {
				
	                // Order failed
	                $order->update_status('failed', sprintf(__('Payment %s via Allied Wallet pingback, payment declined.', 'woocommerce'), strtolower($posted['payment_status']) ) );
				
				
				} 
				
				
			
		    }
		}
	
	}
}	

add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'wc_allied_wallet_plugin_links' );

/**
 * Add the gateway to WooCommerce
 **/
function add_alliedwallet_gateway( $methods ) {
	$methods[] = 'WC_Gateway_Allied_Wallet'; return $methods;
}
add_filter('woocommerce_payment_gateways', 'add_alliedwallet_gateway' );