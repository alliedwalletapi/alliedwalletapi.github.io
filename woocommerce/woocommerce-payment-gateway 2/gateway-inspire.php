<?php
/**
 * Plugin Name: WooCommerce Payment Gateway - Inspire
 * Plugin URI: http://www.inspirecommerce.com/woocommerce/
 * Description: Accept all major credit cards directly on your WooCommerce site in a seamless and secure checkout environment with Inspire Commerce.
 * Version: 2.0.0
 * Author: innerfire
 * Author URI: http://www.inspirecommerce.com/
 * License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * Text Domain: woocommerce-gateway-inspire
 * 
 * @package WordPress
 * @author innerfire
 * @since 1.0.0
 */



/**
 * Inspire Commerce Class
 */
class WC_Inspire {


	/**
	 * Constructor
	 */
	public function __construct(){
		define( 'WC_INSPIRE_VERSION', '2.0.0' );
		define( 'WC_INSPIRE_TEMPLATE_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) . '/templates/' );
		define( 'WC_INSPIRE_PLUGIN_URL', untrailingslashit( plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) ) );
		define( 'WC_INSPIRE_PLUGIN_DIR', plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) . '/' );
		define( 'WC_INSPIRE_MAIN_FILE', __FILE__ );
		define( 'GATEWAY_URL', 'https://secure.inspiregateway.net/api/transact.php');
		define( 'QUERY_URL', 'https://secure.inspiregateway.net/api/query.php');

		// Actions
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_action_links' ) );
		add_action( 'plugins_loaded', array( $this, 'init' ), 0 );
		add_filter( 'woocommerce_payment_gateways', array( $this, 'register_gateway' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'add_inspire_scripts' ) );

	}

	/**
	 * Add links to plugins page for settings and documentation
	 * @param  array $links
	 * @return array
	 */
	public function plugin_action_links( $links ) {
		$subscriptions = ( class_exists( 'WC_Subscriptions_Order' ) ) ? '_subscriptions' : '';
		if ( class_exists( 'WC_Subscriptions_Order' ) && ! function_exists( 'wcs_create_renewal_order' ) ) {
			$subscriptions = '_subscriptions_deprecated';
		}
		$plugin_links = array(
			'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=wc_gateway_inspire' . $subscriptions ) . '">' . __( 'Settings', 'woocommerce-gateway-inspire' ) . '</a>',
			'<a href="http://www.inspirecommerce.com/woocommerce/">' . __( 'Support', 'woocommerce-gateway-inspire' ) . '</a>',
			'<a href="http://www.inspirecommerce.com/woocommerce/">' . __( 'Docs', 'woocommerce-gateway-inspire' ) . '</a>',
		);
		return array_merge( $plugin_links, $links );
	}

	/**
	 * Init localisations and files
	 */
	public function init() {

		if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
			return;
		}

		// Includes
		include_once( 'includes/class-wc-gateway-inspire.php' );

		if ( class_exists( 'WC_Subscriptions_Order' ) ) {

			include_once( 'includes/class-wc-gateway-inspire-subscriptions.php' );

		}

		// Localisation
		load_plugin_textdomain( 'woocommerce-gateway-inspire', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

	}

	/**
	 * Register the gateway for use
	 */
	public function register_gateway( $methods ) {

		if ( class_exists( 'WC_Subscriptions_Order' ) ) {

			$methods[] = 'WC_Gateway_Inspire_Subscriptions';

		} else {
			$methods[] = 'WC_Gateway_Inspire';
		}

		return $methods;

	}


	/**
	 * Include jQuery and our scripts
	 */
	function add_inspire_scripts() {

		if ( ! $this->user_has_stored_data( wp_get_current_user()->ID ) ) return;
		wp_enqueue_script( 'edit_billing_details', WC_INSPIRE_PLUGIN_DIR . 'js/edit_billing_details.js', array( 'jquery' ), WC_INSPIRE_VERSION );
		wp_enqueue_script( 'check_cvv', WC_INSPIRE_PLUGIN_DIR . 'js/check_cvv.js', array( 'jquery' ), WC_INSPIRE_VERSION );

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


}

new WC_Inspire();