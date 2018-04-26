<?php

/*
Plugin Name: WooCommerce AlliedWallet Gateway
Plugin URI: https://www.alliedwallet.com.au/support/supported-carts
Description: Extends WooCommerce with AlliedWallet payment gateway along with WooCommerce subscriptions support.
Version: 1.5.12
Author: AlliedWallet
Author URI: https://www.alliedwallet.com.au
*/

/* Copyright (C) 2012 AlliedWallet Pty. Ltd.

  Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"),
  to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense,
  of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

  The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
  FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
  IN THE SOFTWARE.
*/

add_action('plugins_loaded', 'aw_init', 0);

function aw_init() {
  if (!class_exists('WC_Payment_Gateway')) {
    ?>
    <div id="message" class="error">
      <p><?php printf(__('%sWooCommerce AlliedWallet Extension is inactive.%s The %sWooCommerce plugin%s must be active for the WooCommerce AlliedWallet Extension to work. Please %sinstall & activate WooCommerce%s', 'wc_alliedwallet'), '<strong>', '</strong>', '<a href="http://wordpress.org/extend/plugins/woocommerce/">', '</a>', '<a href="' . admin_url('plugins.php') . '">', '&nbsp;&raquo;</a>'); ?></p>
    </div>
    <?php
    return;
  }

  global $woocommerce;
  // Check the WooCommerce version...
  if (!version_compare($woocommerce->version, '2.1', ">=")) {
    ?>
    <div id="message" class="error">
      <p><?php printf(__('%sWooCommerce AlliedWallet Extension is inactive.%s The version of WooCommerce you are using is not compatible with this verion of the AlliedWallet Extension. Please update WooCommerce to version 2.1 or greater, or remove this version of the AlliedWallet Extension and install an older version.', 'wc_alliedwallet'), '<strong>', '</strong>'); ?></p>
    </div>
    <?php
    return;
  }

  class WC_AlliedWallet extends WC_Payment_Gateway_CC {

    public function __construct() {
      $this->id = 'alliedwallet';
      $this->icon = apply_filters('woocommerce_alliedwallet_icon', '');
      $this->has_fields = true;
      $this->method_title = __('Allied Wallet Direct', 'woocommerce');
      $this->version = "1.5.12";

      $this->api_version = "1.0";
	  
      $this->live_url = "https://api.alliedwallet.com/";
      $this->supports = array('subscriptions', 'products', 'refunds', 'subscription_cancellation', 'subscription_reactivation', 'subscription_suspension', 'subscription_amount_changes', 'subscription_payment_method_change', 'subscription_date_changes');
      $this->params = array();
      //$this->country_map = array("AD" => "AND", "AE" => "ARE", "AF" => "AFG", "AG" => "ATG", "AI" => "AIA", "AL" => "ALB", "AM" => "ARM", "AN" => "ANT", "AO" => "AGO", "AQ" => "ATA", "AR" => "ARG", "AS" => "ASM", "AT" => "AUT", "AU" => "AUS", "AW" => "ABW", "AX" => "ALA", "AZ" => "AZE", "BA" => "BIH", "BB" => "BRB", "BD" => "BGD", "BE" => "BEL", "BF" => "BFA", "BG" => "BGR", "BH" => "BHR", "BI" => "BDI", "BJ" => "BEN", "BL" => "BLM", "BM" => "BMU", "BN" => "BRN", "BO" => "BOL", "BQ" => "BES", "BR" => "BRA", "BS" => "BHS", "BT" => "BTN", "BV" => "BVT", "BW" => "BWA", "BY" => "BLR", "BZ" => "BLZ", "CA" => "CAN", "CC" => "CCK", "CD" => "COD", "CF" => "CAF", "CG" => "COG", "CH" => "CHE", "CI" => "CIV", "CK" => "COK", "CL" => "CHL", "CM" => "CMR", "CN" => "CHN", "CO" => "COL", "CR" => "CRI", "CU" => "CUB", "CV" => "CPV", "CW" => "CUW", "CX" => "CXR", "CY" => "CYP", "CZ" => "CZE", "DE" => "DEU", "DJ" => "DJI", "DK" => "DNK", "DM" => "DMA", "DO" => "DOM", "DZ" => "DZA", "EC" => "ECU", "EE" => "EST", "EG" => "EGY", "EH" => "ESH", "ER" => "ERI", "ES" => "ESP", "ET" => "ETH", "FI" => "FIN", "FJ" => "FJI", "FK" => "FLK", "FM" => "FSM", "FO" => "FRO", "FR" => "FRA", "GA" => "GAB", "GB" => "GBR", "GD" => "GRD", "GE" => "GEO", "GF" => "GUF", "GG" => "GGY", "GH" => "GHA", "GI" => "GIB", "GL" => "GRL", "GM" => "GMB", "GN" => "GIN", "GP" => "GLP", "GQ" => "GNQ", "GR" => "GRC", "GS" => "SGS", "GT" => "GTM", "GU" => "GUM", "GW" => "GNB", "GY" => "GUY", "HK" => "HKG", "HM" => "HMD", "HN" => "HND", "HR" => "HRV", "HT" => "HTI", "HU" => "HUN", "ID" => "IDN", "IE" => "IRL", "IL" => "ISR", "IM" => "IMN", "IN" => "IND", "IO" => "IOT", "IQ" => "IRQ", "IR" => "IRN", "IS" => "ISL", "IT" => "ITA", "JE" => "JEY", "JM" => "JAM", "JO" => "JOR", "JP" => "JPN", "KE" => "KEN", "KG" => "KGZ", "KH" => "KHM", "KI" => "KIR", "KM" => "COM", "KN" => "KNA", "KP" => "PRK", "KR" => "KOR", "KW" => "KWT", "KY" => "CYM", "KZ" => "KAZ", "LA" => "LAO", "LB" => "LBN", "LC" => "LCA", "LI" => "LIE", "LK" => "LKA", "LR" => "LBR", "LS" => "LSO", "LT" => "LTU", "LU" => "LUX", "LV" => "LVA", "LY" => "LBY", "MA" => "MAR", "MC" => "MCO", "MD" => "MDA", "ME" => "MNE", "MF" => "MAF", "MG" => "MDG", "MH" => "MHL", "MK" => "MKD", "ML" => "MLI", "MM" => "MMR", "MN" => "MNG", "MO" => "MAC", "MP" => "MNP", "MQ" => "MTQ", "MR" => "MRT", "MS" => "MSR", "MT" => "MLT", "MU" => "MUS", "MV" => "MDV", "MW" => "MWI", "MX" => "MEX", "MY" => "MYS", "MZ" => "MOZ", "NA" => "NAM", "NC" => "NCL", "NE" => "NER", "NF" => "NFK", "NG" => "NGA", "NI" => "NIC", "NL" => "NLD", "NO" => "NOR", "NP" => "NPL", "NR" => "NRU", "NU" => "NIU", "NZ" => "NZL", "OM" => "OMN", "PA" => "PAN", "PE" => "PER", "PF" => "PYF", "PG" => "PNG", "PH" => "PHL", "PK" => "PAK", "PL" => "POL", "PM" => "SPM", "PN" => "PCN", "PR" => "PRI", "PS" => "PSE", "PT" => "PRT", "PW" => "PLW", "PY" => "PRY", "QA" => "QAT", "RE" => "REU", "RO" => "ROU", "RS" => "SRB", "RU" => "RUS", "RW" => "RWA", "SA" => "SAU", "SB" => "SLB", "SC" => "SYC", "SD" => "SDN", "SE" => "SWE", "SG" => "SGP", "SH" => "SHN", "SI" => "SVN", "SJ" => "SJM", "SK" => "SVK", "SL" => "SLE", "SM" => "SMR", "SN" => "SEN", "SO" => "SOM", "SR" => "SUR", "SS" => "SSD", "ST" => "STP", "SV" => "SLV", "SX" => "SXM", "SY" => "SYR", "SZ" => "SWZ", "TC" => "TCA", "TD" => "TCD", "TF" => "ATF", "TG" => "TGO", "TH" => "THA", "TJ" => "TJK", "TK" => "TKL", "TL" => "TLS", "TM" => "TKM", "TN" => "TUN", "TO" => "TON", "TR" => "TUR", "TT" => "TTO", "TV" => "TUV", "TW" => "TWN", "TZ" => "TZA", "UA" => "UKR", "UG" => "UGA", "UM" => "UMI", "US" => "USA", "UY" => "URY", "UZ" => "UZB", "VA" => "VAT", "VC" => "VCT", "VE" => "VEN", "VG" => "VGB", "VI" => "VIR", "VN" => "VNM", "VU" => "VUT", "WF" => "WLF", "WS" => "WSM", "YE" => "YEM", "YT" => "MYT", "ZA" => "ZAF", "ZM" => "ZMB", "ZW" => "ZWE");
	  
      // Define user set variables
      $this->title = "Credit Card";
      $this->description = in_array("description", $this->settings) ? $this->settings['description'] : "";

	  if($_POST['Action']=='Cancelled' || $_POST['Action']=='Rebill' || $_POST['Action']=='Inactive'){
		  $this->update_order2($_POST);
	  }

      // Load the form fields.
      $this->init_form_fields();

      // Load the settings.
      $this->init_settings();

      if ($this->direct_post_enabled()) {
        array_push($this->supports, 'tokenization');
      }

      // Actions
      add_action('woocommerce_update_options_payment_gateways', array(&$this, 'process_admin_options')); // < 2.0
      add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options')); //> 2.0
      add_action('scheduled_subscription_payment_alliedwallet', array(&$this, 'scheduled_subscription_payment'), 10, 3);
      // add_action('woocommerce_order_actions', array(&$this, 'add_process_deferred_payment_button'), 99, 1);
    }
	function update_order2($data){

		$order = new WC_Order($data['TrackingId']);
		if($data['Action']=='Rebill' || $data['Action']=='Inactive'){
		$order->add_order_note(__("AlliedWallet Order Rebilled. Reference: " . $data["TrackingId"]));
        $order->payment_complete($data['transaction_id']);
		update_post_meta($data['TrackingId'], 'AlliedWallet Transaction ID', $data['transaction_id']);
		}
		else{
			$order->add_order_note(__("AlliedWallet Order Subscription Cancelled. Reference: " . $data["TrackingId"]));
			$order->update_status('Cancelled', __('Payment has been cancelled.'));
			//$order->update_status("Cancelled");
			update_post_meta($data['TrackingId'], "AlliedWallet Transaction ID", $data["transaction_id"]);
		}
	}

    /**
     * Indicates if direct post is enabled/configured or not
     */
    function direct_post_enabled() {
      return $this->settings['use_direct_post'] == 'yes' && !is_null($this->settings['shared_secret']);
    }

    /**
     * Initialise Gateway Settings Form Fields
     */
    function init_form_fields()
    {

      $this->form_fields = array(
        'enabled' => array(
          'title' => __('Enable/Disable', 'woocommerce'),
          'type' => 'checkbox',
          'label' => __('Enable AlliedWallet', 'woocommerce'),
          'default' => 'yes'
        ),
        'merchant_id' => array(
                    'title' => __('Merchant ID', 'woocommerce'),
                    'type' => 'text',
                    'description' => __('This MID is issued from AW.')),
		'site_id' => array(
			'title' => __('Site ID', 'woocommerce'),
			'type' => 'text',
			'description' => __('This SID is issued from AW.')),
		'subscriptionId' => array(
			'title' => __('Subscription Plan ID', 'woocommerce'),
			'type' => 'text',
			'description' => __('This Subscription Plan ID is issued from AW.')),
		'token' => array(
			'title' => __('Direct Token', 'woocommerce'),
			'type' => 'textarea',
			'description' => __('This is the token issued from AW.')),
        'show_logo' => array(
          'title' => __("Show AlliedWallet Logo", 'woocommerce'),
          'type' => 'checkbox',
          'description' => __("Shows or hides the 'AlliedWallet Certified' logo on the payment form", "woocommerce"),
          'default' => "yes"
        ),
        'show_card_logos' => array(
          'title' => __("Show credit card logos", 'woocommerce'),
          'type' => 'multiselect',
          'description' => "Shows or hides the credit card icons (AMEX, Visa, Discover, JCB etc). <a href=\"http://www.iconshock.com/credit-card-icons/\">Credit Card Icons by iconshock</a>",
          'default' => array("visa", "mastercard"),
          "options" => array("visa" => "VISA", "mastercard" => "MasterCard", "american_express" => "AMEX", "jcb" => "JCB"), //, "diners_club" => "Diners", "discover" => "Discover")
        )
        
      );

    } // End init_form_fields()

    /**
     * Admin Panel Options
     * - Options for bits like 'title' and availability on a country-by-country basis
     *
     * @since 1.0.0
     */
    public function admin_options() {
      ?>
      <h3><?php _e('AlliedWallet', 'woocommerce'); ?></h3>
      <p><?php _e('Allows AlliedWallet Payments. ', 'woocommerce'); ?></p>
      <table class="form-table">
        <?php $this->generate_settings_html(); ?>
      </table>
    <?php
    } // End admin_options()

    function payment_fields() {
      if ($this->direct_post_enabled()) {
        // Register and enqueue direct post handling script
        $url = $this->live_url;

        $return_path = uniqid('alliedwallet-nonce-');
        $verification_value = hash_hmac('md5', $return_path, $this->settings["shared_secret"]);

        wp_register_script('aw-direct-post-handler', plugin_dir_url(__FILE__) . '/images/alliedwallet.js', array('jquery'), WC_VERSION, true);
        wp_localize_script('aw-direct-post-handler', 'alliedwallet', array(
          'url' => $url,
          'return_path' => $return_path,
          'verification_value' => $verification_value)
        );
        wp_enqueue_script('aw-direct-post-handler');
      }

      echo "<input type='hidden' name='alliedwallet-token' id='alliedwallet-token' /><span class='payment-errors required'></span>";

      $this->form(array('fields_have_names' => !$this->direct_post_enabled()), $extra_fields);

    }

    /**
     * Process the payment and return the result
     **/
    function process_payment($order_id) {
      global $woocommerce;
	  
      if ($this->direct_post_enabled()) {
	    $this->params["card_token"] = $_POST['alliedwallet-token'];
      } else {	
        $card_number = str_replace(' ', '', $_POST['alliedwallet-card-number']);
			if (!isset($_POST["alliedwallet-card-number"])) {
			  $card_number = $_POST['cardnumber'];
			}
			$cvv = $_POST["alliedwallet-card-cvc"];
			if (!isset($_POST['alliedwallet-card-cvc'])) {
			  $cvv = $_POST['card_cvv'];
			}
			if (isset($_POST['alliedwallet-card-expiry']) && !empty($_POST['alliedwallet-card-expiry'])) {
			  list($exp_month, $exp_year) = explode('/', $_POST['alliedwallet-card-expiry']);
			} else {
			  $exp_month = $_POST['card_expiry_month'];
			  $exp_year = $_POST['card_expiry_year'];
			}
      }
	  $exp_year = 2000 + $exp_year; 
	  
		$card_holder = $_POST['billing_first_name'] . " " . $_POST['billing_last_name'];
      //$defer_payment = false; //$this->settings["deferred_payments"] == "yes";

      $order = new WC_Order($order_id);
      //$this->params["currency"] = $order->get_order_currency();

      if (class_exists("WC_Subscriptions_Order") && wcs_order_contains_subscription($order)) {
        // No deferred payments for subscriptions.
        $defer_payment = false;
        // Charge sign up fee + first period here..
        // Periodic charging should happen via scheduled_subscription_payment_alliedwallet
        $amount = $this->convert_to_cents($order->get_total());
      } else {
        $amount = $this->convert_to_cents($order->order_total);
      }
	  $amount = "0.00";
			$this->params["SubscriptionPlanId"]= $this->settings["subscriptionId"];
			$this->params["amount"]=$amount;
			$this->params["Currency"]=$order->get_order_currency();
			$this->params["SiteId"]=$this->settings["site_id"];
			//$this->params["TokenId"]=$this->settings["token"];
			$this->params["FirstName"]=$_POST['billing_first_name'];
			$this->params["LastName"]=$_POST['billing_last_name'];
			$this->params["AddressLine1"]=$_POST['billing_address_1'];
			$this->params["AddressLine2"]='1';
			$this->params["City"]=$_POST['billing_city'];
			$this->params["CountryId"]=$_POST['billing_country'];
			$this->params["PostalCode"]=$_POST['billing_postcode'];
			$this->params["email"]=$_POST['billing_email'];
			$this->params["TrackingId"]=(string)$order_id;;
			$this->params["NameOnCard"]=$card_holder;
			$this->params["cardNumber"]=$card_number;
			$this->params["ExpirationMonth"]=$exp_month;
			$this->params["ExpirationYear"]=$exp_year;
			$this->params["CvvCode"]=$cvv;
			$this->params["Phone"]=$_POST['billing_phone'];
			//$this->params["TokenId"]=$_POST['tokenid'];
			$this->params["IpAddress"]=$this->get_customer_real_ip();
			//$sale=$_POST['sale'];
			
      if (trim($this->params["card_holder"]) == "") { // If the customer is updating their details the $_POST values for name will be missing, so fetch from the order
        $this->params["card_holder"] = $order->billing_first_name . " " . $order->billing_last_name;
      }
      $result = $this->do_payment($this->params);
      if (is_wp_error($result)) {
		  $order->add_order_note($result->get_error_message());
          wc_add_notice($result->get_error_message(), 'error');
	      return;
      }
	  else {
	  	  $order->add_order_note(__("AlliedWallet payment complete. Reference: " . $result["transaction_id"]));
          $order->payment_complete($result['transaction_id']);
		  update_post_meta($order_id, 'AlliedWallet Transaction ID', $result['transaction_id']);
        }
        $woocommerce->cart->empty_cart();
        return array('result' => 'success', 'redirect' => $this->get_return_url($order));
    }
	  


    function fetch_alliedwallet_transaction_id($order_id) {
      return false;
    }



        // Add the error details and return
        $order->add_order_note(__("Subscription Payment Failed: " . $error . ". Transaction ID: " . $txn_id));
        WC_Subscriptions_Manager::process_subscription_payment_failure_on_order($order, $product_id);

      } else { // Success! Returned is an array with the transaction ID etc
        // Update the subscription and return
        // Add a note to the order
        $order->add_order_note(__("Subscription Payment Successful. Transaction ID: " . $result["transaction_id"]));
        WC_Subscriptions_Manager::process_subscription_payments_on_order($order);
      }
    }

    /**
     *
     * @return mixed WP_Error or Array (result)
     */
    function do_payment($params) {
      $order_text = json_encode($params);
	  $mid = $this->settings["merchant_id"];
	  $action = 'saletransactions';
      $url = $this->live_url.'Merchants/'.$mid.'/'.$action;
		//Initiate cURL.
		$ch = curl_init($url);      
		curl_setopt($ch, CURLOPT_POST, 1);
		//Attach our encoded JSON string to the POST fields.
		curl_setopt($ch, CURLOPT_POSTFIELDS, $order_text);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		//Set the content type to application/json
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Authorization: Bearer '.$this->settings["token"],'Accept-Encoding: gzip,deflate',
				'Host: api.alliedwallet.com','Content-Length:'.strlen($order_text))); 
		//Execute the request
		$result = curl_exec($ch);
		//echo 'result'.$result;
		///Deocde Json
		$data = json_decode($result, true);
		//echo "<pre>"; var_dump($data); echo "</pre>";
		$trackingId = $data['trackingId'];
		$status = $data['status'];
		$tid = ($data['id']);
		$message = ($data['message']);
      try {
		if ($status=='Successful') {
          return array("transaction_id" => $tid, "status" => $status,'trackingId'=>$trackingId,'message'=>$message);
        }
		else{
		  $error = new WP_Error();
          $error->add(1, "Credit Card Payment failed");
          $error->add_data('Credit Card Payment failed');
          return $error;
        }

      } catch (Exception $e) {
        $error = new WP_Error();
        $error->add(4, "Unknown Error", $e);
        return $error;
      }
    }



    // Add the 'Charge Card' button if the order is on-hold
    function add_process_deferred_payment_button($order_id) {
      $order = new WC_Order($order_id);
      if ($order->status == "on-hold") {
        echo '<li><input type="submit" class="button tips" name="process" value="Charge Card" data-tip="Attempts to process a deferred payment" /></li>';
      }
    }

    function get_customer_real_ip() {
      $customer_ip = $_SERVER['REMOTE_ADDR'];
      if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $forwarded_ips = explode(', ', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $customer_ip = $forwarded_ips[0];
      }

      return $customer_ip;
    }


  /**
   * Add the gateway to WooCommerce
   **/
  function add_aw_gateway($methods) {
    $methods[] = 'WC_AlliedWallet';
    return $methods;
  }

  

  add_filter('woocommerce_payment_gateways', 'add_aw_gateway');
}
