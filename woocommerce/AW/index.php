<?php
/*
Plugin Name: WooCommerce AW Payment Gateway
Plugin URI: https://www.alliedwallet.com
Description: AlliedWallet gateway for woocommerce
Version: 0.1
Author: AlliedWallet
Author URI: http://www.alliedwallet.com
*/

add_action('plugins_loaded', 'woocommerce_aw_init', 0);
function woocommerce_aw_init(){
  if(!class_exists('WC_Payment_Gateway')) return;
 
  class WC_aw extends WC_Payment_Gateway{
    public function __construct(){
      $this -> id = 'aw';
      $this -> method_title = 'AlliedWallet - Nextgen';
      $this -> has_fields = false;
      
      $this -> init_form_fields();
      $this -> init_settings();
      $this -> title = $this -> settings['title'];
      $this -> description = $this -> settings['description'];
      $this -> merchant_id = $this -> settings['merchant_id'];
      $this -> site_id = $this -> settings['site_id'];
      $this -> qptoken = $this -> settings['qptoken'];
      $this -> redirect_page_id = $this -> settings['redirect_page_id'];
      $this -> icon = apply_filters('woocommerce_aw_icon', plugin_dir_url(__FILE__) . '/images/alliedwallet.png');
      $this -> liveurl = 'http://www.beevip.com/post.php';
      //$this -> liveurl = 'https://quickpay.alliedwallet.com';   
 
      $this -> msg['message'] = "";
      $this -> msg['class'] = "";

      add_action('woocommerce_api_'.strtolower(get_class($this)), array(&$this, 'handle_callback'));
      add_action('woocommerce_receipt_aw', array(&$this, 'receipt_page'));
      if ( version_compare( WOOCOMMERCE_VERSION, '2.0.0', '>=' ) ) {
                add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( &$this, 'process_admin_options' ) );
             } else {
                add_action( 'woocommerce_update_options_payment_gateways', array( &$this, 'process_admin_options' ) );
            }
   }
    function init_form_fields(){
 
       $this -> form_fields = array(
                'enabled' => array(
                    'title' => __('Enable/Disable', 'aw'),
                    'type' => 'checkbox',
                    'label' => __('Enable AlliedWallet Payment Module.', 'aw'),
                    'default' => 'no'),
                'title' => array(
                    'title' => __('Title:', 'aw'),
                    'type'=> 'text',
                    'description' => __('This controls the title which the user sees during checkout.', 'aw'),
                    'default' => __('aw', 'aw')),
                'description' => array(
                    'title' => __('Description:', 'aw'),
                    'type' => 'textarea',
                    'description' => __('This controls the description which the user sees during checkout.', 'aw'),
                    'default' => __('Pay securely by Credit or Debit card or internet banking through AlliedWallet.', 'aw')),
                'merchant_id' => array(
                    'title' => __('Merchant ID', 'aw'),
                    'type' => 'text',
                    'description' => __('This MID is issued from AW."')),
                'site_id' => array(
                    'title' => __('Site ID', 'aw'),
                    'type' => 'text',
                    'description' => __('This SID is issued from AW."')),
               'QuickPayToken' => array(
								'title' => __( 'Allied Wallet QuickPayToken', 'wc-gateway-alliedwallet' ), 
								'type' => 'textarea', 
								'description' => __( 'Your QuickPayToken from Allied Wallet.', 'wc-gateway-alliedwallet' ), 
								'default' => ''
							),
                'debug' => array(
								'title' => __( 'Debug Log', 'woocommerce' ), 
								'type' => 'checkbox', 
								'label' => __( 'Enable logging', 'woocommerce' ), 
								'default' => 'no',
								'description' => __( 'Log Allied Wallet events, such as pingback requests, inside <code>woocommerce/logs/aw.txt</code>' ),
							)
                
            );
    }
 
       public function admin_options(){
        echo '<h3>'.__('AW Payment Gateway', 'aw').'</h3>';
        echo '<p>'.__('AW is most popular payment gateway for online shopping.').'</p>';
        echo '<table class="form-table">';
        // Generate the HTML For the settings form.
        $this -> generate_settings_html();
        echo '</table>';
 
    }
      
      /**
	     * Check if this gateway is enabled and available in the user's country
	     */
	    function is_valid_for_use() {
	        if (!in_array(get_woocommerce_currency(), array('AUD', 'BRL', 'CAD', 'MXN', 'NZD', 'HKD', 'SGD', 'USD', 'EUR', 'JPY', 'TRY', 'NOK', 'CZK', 'DKK', 'HUF', 'ILS', 'MYR', 'PHP', 'PLN', 'SEK', 'CHF', 'TWD', 'THB', 'GBP', 'RMB'))) return false;
	
	        return true;
	    }
 
    /**
     *  There are no payment fields for aw, but we want to show the description if set.
     **/
    function payment_fields(){
        if($this -> description) echo wpautop(wptexturize($this -> description));
    }
    /**
     * Receipt Page
     **/
    function receipt_page($order){
        echo '<p>'.__('Thank you for your order, please click the button below to pay with AW.', 'aw').'</p>';
        echo $this -> generate_aw_form($order);
    }
    /**
     * Generate aw button link
     **/
    public function generate_aw_form($order_id){
 
        global $woocommerce;
 
        $order = new WC_Order($order_id);
        $txnid = $order_id.'_'.date("ymds");
 
        $redirect_url = ($this -> redirect_page_id=="" || $this -> redirect_page_id==0)?get_site_url() . "/":get_permalink($this -> redirect_page_id);
 
        $productinfo = "Order $order_id";
 
 
        $aw_args = array(
								'MerchantID'					=> $this->merchant_id,
								'SiteID'						=> $this->site_id,
								'QuickPayToken'					=> $this->qptoken,
								'CurrencyID'					=> get_woocommerce_currency(),
								'ShippingRequired'				=> 'true',
								'NoMembership'					=> '1',
								'FirstName'						=> $order->billing_first_name,
								'LastName'						=> $order->billing_last_name,
								'Email'							=> $order->billing_email,
								'Address'						=> $order->billing_address_1,
								'Address2'						=> $order->billing_address_2,
								'City'							=> $order->billing_city,
								'Country'						=> $order->billing_country,
								'PostalCode'					=> $order->billing_postcode,
								'Phone'							=> $order->billing_phone,
								'State'							=> $order->billing_state,
								'ApprovedURL'						=> $this->get_return_url( $order ),
								'DeclinedURL'				    => $order->get_cancel_order_url(),
								'ConfirmURL'					=> trailingslashit(home_url()).'?wc-api=wc_aw',
								'MerchantReference'				=> $order_id . ":" . $order->order_key,
								'AmountShipping'				=> $order->get_total_shipping(),
								'AmountTotal'					=> $order->get_total(),
							);
        
        // Cart Contents
				$item_loop = 0;
				if (sizeof($order->get_items())>0) : foreach ($order->get_items() as $item) :
					
						
						$product = $order->get_product_from_item($item);
						//$price = $product->get_price();
						$price = $product->get_price_including_tax();
						$item_name 	= $item['name'];
						
						/*$item_meta = new order_item_meta( $item['item_meta'] );					
						if ($meta = $item_meta->display( true, true )) :
							$item_name .= ' ('.$meta.')';
						endif;*/

						$aw_args['ItemName['.$item_loop.']'] = $item_name;
						$aw_args['ItemDesc['.$item_loop.']'] = '';
						$aw_args['ItemQuantity['.$item_loop.']'] = $item['qty'];
						$aw_args['ItemAmount['.$item_loop.']'] = $price;

						$item_loop++;
						
					
				endforeach; endif;
				
				 //Tax
				//$this->log->add( 'alliedwallet', 'TOTAL TAX: ' . $order->get_total_tax() );
				if ( $order->get_total_tax() > 0 ) {
					// add tax as line item 

					$allied_args['ItemName['.$item_loop.']'] = 'Order Tax';
					$allied_args['ItemDesc['.$item_loop.']'] = '';
					$allied_args['ItemQuantity['.$item_loop.']'] = 1;
					$allied_args['ItemAmount['.$item_loop.']'] = number_format($order->get_total_tax(), 2, '.', '');

					$item_loop++;
				}
				
				/* Discount
				$this->log->add( 'alliedwallet', 'TOTAL DISCOUNT: ' . $order->get_total_discount() );
				if ( $order->get_total_discount() > 0 ) {
				 	// add discount as line item

					$allied_args['ItemName['.$item_loop.']'] = 'Order Discount';
					$allied_args['ItemDesc['.$item_loop.']'] = '';
					$allied_args['ItemQuantity['.$item_loop.']'] = 1;
					$allied_args['ItemAmount['.$item_loop.']'] = '-' . number_format($order->get_total_discount(), 4, '.', '');
				 message: "<img src=\"'.$woocommerce->plugin_url().'/assets/images/ajax-loader.gif\" alt=\"Redirecting…\" style=\"float:left; margin-right: 10px;\" />'.__('Thank you for your order. We are now redirecting you to Payment Gateway to make payment.', 'aw').'",
                overlayCSS:
				}*/
			
 
        $aw_args_array = array();
        foreach($aw_args as $key => $value){
          $aw_args_array[] = "<input type='hidden' name='$key' value='$value'/>";
        }
        return '<form action="'.$this -> liveurl.'" method="post" id="aw_payment_form">
            ' . implode('', $aw_args_array) . '
            <input type="submit" class="button-alt" id="submit_aw_payment_form" value="'.__('Pay via AW', 'aw').'" /> <a class="button cancel" href="'.$order->get_cancel_order_url().'">'.__('Cancel order &amp; restore cart', 'aw').'</a>
            <script type="text/javascript">
jQuery(function(){
jQuery("body").block(
        {
        
        message: "<img src=\"'.WC()->plugin_url().'/assets/images/ajax-loader.gif\" alt=\"Redirecting…\" style=\"float:left; margin-right: 10px;\" />'.__('Thank you for your order. We are now redirecting you to Payment Gateway to make payment.', 'aw').'",
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
            lineHeight:"32px"
    }
    });
    jQuery("#submit_aw_payment_form").click();});</script>
            </form>';
 
 
    }
    /**
     * Process the payment and return the result
     **/
    function process_payment($order_id){
        $order = new WC_Order($order_id);
        return array('result' => 'success', 'redirect' => add_query_arg('order',
            $order->id, add_query_arg('key', $order->order_key, get_permalink(get_option('woocommerce_pay_page_id'))))
        );
    }
 /**
		 * Check Allied Wallet Postback validity
		 **/
		function check_postback_request_is_valid() {
			global $woocommerce;
			
	    	// Get recieved values from post data
			$response = (array) stripslashes_deep( $_POST );
	        
	        if ($this->debug=='yes') $this->log->add( 'aw', 'Pingback Response: ' . print_r($response, true) );
	        
	        // check to see if the request was valid
	        if ( !is_wp_error($response) && isset($response['TrackingId']) && isset($response['TransactionID']) ) {
	            if ($this->debug=='yes') $this->log->add( 'aw', 'Received valid response from Allied Wallet' );
	            return true;
	        }  
	        
	        if ($this->debug=='yes') :
	        	$this->log->add( 'aw', 'Received invalid response from Allied Wallet' );
	        	if (is_wp_error($response)) :
	        		$this->log->add( 'aw', 'Error response: ' . $result->get_error_message() );
	        	endif;
	        endif;
	        
	        return false;
	    }
    
 /**
		 * Check for Allied Wallet Postback Response
		 **/
		function check_pingback_response() {
				
			if (isset($_GET['awListener']) && $_GET['awListener'] == 'aw_pingback'):
				
				@ob_clean();
				
	        	$_POST = stripslashes_deep($_POST);
	        
	        	if ($this->check_postback_request_is_valid()) :
	        		
	        		header('HTTP/1.1 200 OK');
	        		
	            	do_action("valid-aw-pingback-request", $_POST);
				
				else :
				
			        if ($this->debug=='yes') $this->log->add( 'aw', 'Allied Wallet Pingback Failure' );
					wp_die("Allied Wallet Pingback Request Failure");
				
	       		endif;
	       		
	       	endif;
	       	
				
		}
      
     		/**
		 * Successful Payment!
		 **/
		function handle_callback( ) {
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

/**
     * Add the Gateway to WooCommerce
     **/
    function woocommerce_add_aw_gateway($methods) {
        $methods[] = 'WC_aw';
        return $methods;
    }
 
    add_filter('woocommerce_payment_gateways', 'woocommerce_add_aw_gateway' );
    
    }

?>