<?php
/**
 * Plugin Name: WooCommerce Payment Gateway - AlliedWallet

 * 
 * @package WordPress

 */



/**
 * AW Commerce Class
 */
class WC_Aw {


	/**
	 * Constructor
	 */
	public function __construct(){
		define( 'WC_AW_VERSION', '2.0.0' );
		define( 'WC_AW_TEMPLATE_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) . '/templates/' );
		define( 'WC_AW_PLUGIN_URL', untrailingslashit( plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) ) );
		define( 'WC_AW_PLUGIN_DIR', plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) . '/' );
		define( 'WC_AW_MAIN_FILE', __FILE__ );
		define( 'GATEWAY_URL', 'https://api.alliedwallet.com/Merchants/');
		define( 'QUERY_URL', 'https://api.alliedwallet.com/Merchants/');

		// Actions
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_action_links' ) );
		add_action( 'plugins_loaded', array( $this, 'init' ), 0 );
		add_filter( 'woocommerce_payment_gateways', array( $this, 'register_gateway' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'add_aw_scripts' ) );

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
			'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=wc_gateway_aw' . $subscriptions ) . '">' . __( 'Settings', 'woocommerce-gateway-aw' ) . '</a>',
			'<a href="http://www.awcommerce.com/woocommerce/">' . __( 'Support', 'woocommerce-gateway-aw' ) . '</a>',
			'<a href="http://www.awcommerce.com/woocommerce/">' . __( 'Docs', 'woocommerce-gateway-aw' ) . '</a>',
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
		include_once( 'includes/class-wc-gateway-aw.php' );

		if ( class_exists( 'WC_Subscriptions_Order' ) ) {

			include_once( 'includes/class-wc-gateway-aw-subscriptions.php' );

		}

		// Localisation
		load_plugin_textdomain( 'woocommerce-gateway-aw', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

	}

	/**
	 * Register the gateway for use
	 */
	public function register_gateway( $methods ) {

		if ( class_exists( 'WC_Subscriptions_Order' ) ) {

			$methods[] = 'WC_Gateway_Aw_Subscriptions';

		} else {
			$methods[] = 'WC_Gateway_Aw';
		}

		return $methods;

	}


	/**
	 * Include jQuery and our scripts
	 */
	function add_aw_scripts() {

		if ( ! $this->user_has_stored_data( wp_get_current_user()->ID ) ) return;
		wp_enqueue_script( 'edit_billing_details', WC_AW_PLUGIN_DIR . 'js/edit_billing_details.js', array( 'jquery' ), WC_AW_VERSION );
		wp_enqueue_script( 'check_cvv', WC_AW_PLUGIN_DIR . 'js/check_cvv.js', array( 'jquery' ), WC_AW_VERSION );

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

new WC_Aw();