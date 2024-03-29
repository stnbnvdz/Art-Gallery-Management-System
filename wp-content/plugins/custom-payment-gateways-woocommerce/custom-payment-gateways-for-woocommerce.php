<?php
/*
Plugin Name: Custom Payment Gateways for WooCommerce
Plugin URI: https://wpfactory.com/item/custom-payment-gateways-woocommerce/
Description: Custom payment gateways for WooCommerce
Version: 1.2.0
Author: Algoritmika Ltd
Author URI: http://www.algoritmika.com
Text Domain: custom-payment-gateways-for-woocommerce
Domain Path: /langs
Copyright: � 2018 Algoritmika Ltd.
WC tested up to: 3.5
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Check if WooCommerce is active
$plugin = 'woocommerce/woocommerce.php';
if (
	! in_array( $plugin, apply_filters( 'active_plugins', get_option( 'active_plugins', array() ) ) ) &&
	! ( is_multisite() && array_key_exists( $plugin, get_site_option( 'active_sitewide_plugins', array() ) ) )
) {
	return;
}

if ( 'custom-payment-gateways-for-woocommerce.php' === basename( __FILE__ ) ) {
	// Check if Pro is active, if so then return
	$plugin = 'custom-payment-gateways-for-woocommerce-pro/custom-payment-gateways-for-woocommerce-pro.php';
	if (
		in_array( $plugin, apply_filters( 'active_plugins', get_option( 'active_plugins', array() ) ) ) ||
		( is_multisite() && array_key_exists( $plugin, get_site_option( 'active_sitewide_plugins', array() ) ) )
	) {
		return;
	}
}

if ( ! class_exists( 'Alg_WC_Custom_Payment_Gateways' ) ) :

/**
 * Main Alg_WC_Custom_Payment_Gateways Class
 *
 * @class   Alg_WC_Custom_Payment_Gateways
 * @version 1.2.0
 * @since   1.0.0
 */
final class Alg_WC_Custom_Payment_Gateways {

	/**
	 * Plugin version.
	 *
	 * @var   string
	 * @since 1.0.0
	 */
	public $version = '1.2.0';

	/**
	 * @var   Alg_WC_Custom_Payment_Gateways The single instance of the class
	 * @since 1.0.0
	 */
	protected static $_instance = null;

	/**
	 * Main Alg_WC_Custom_Payment_Gateways Instance
	 *
	 * Ensures only one instance of Alg_WC_Custom_Payment_Gateways is loaded or can be loaded.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 * @static
	 * @return  Alg_WC_Custom_Payment_Gateways - Main instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Alg_WC_Custom_Payment_Gateways Constructor.
	 *
	 * @version 1.2.0
	 * @since   1.0.0
	 * @access  public
	 */
	function __construct() {

		// Set up localisation
		load_plugin_textdomain( 'custom-payment-gateways-for-woocommerce', false, dirname( plugin_basename( __FILE__ ) ) . '/langs/' );

		// Include required files
		$this->includes();

		// Admin
		if ( is_admin() ) {
			$this->admin();
		}
	}

	/**
	 * Show action links on the plugin screen.
	 *
	 * @version 1.1.1
	 * @since   1.0.0
	 * @param   mixed $links
	 * @return  array
	 */
	function action_links( $links ) {
		$custom_links = array();
		$custom_links[] = '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=alg_wc_custom_payment_gateways' ) . '">' . __( 'Settings', 'woocommerce' ) . '</a>';
		if ( 'custom-payment-gateways-for-woocommerce.php' === basename( __FILE__ ) ) {
			$custom_links[] = '<a target="_blank" href="https://wpfactory.com/item/custom-payment-gateways-woocommerce/">' .
				__( 'Unlock All', 'custom-payment-gateways-for-woocommerce' ) . '</a>';
		}
		return array_merge( $custom_links, $links );
	}

	/**
	 * Include required core files used in admin and on the frontend.
	 *
	 * @version 1.2.0
	 * @since   1.0.0
	 */
	function includes() {
		// Functions
		require_once( 'includes/alg-wc-custom-payment-gateways-functions.php' );
		// Core
		$this->core = require_once( 'includes/class-alg-wc-custom-payment-gateways-core.php' );
	}

	/**
	 * admin.
	 *
	 * @version 1.2.0
	 * @since   1.2.0
	 */
	function admin() {
		// Action links
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'action_links' ) );
		// Settings
		add_filter( 'woocommerce_get_settings_pages', array( $this, 'add_woocommerce_settings_tab' ) );
		require_once( 'includes/settings/class-alg-wc-custom-payment-gateways-settings-section.php' );
		$this->settings = array();
		$this->settings['general'] = require_once( 'includes/settings/class-alg-wc-custom-payment-gateways-settings-general.php' );
		// Version updated
		if ( get_option( 'alg_wc_custom_payment_gateways_version', '' ) !== $this->version ) {
			add_action( 'admin_init', array( $this, 'version_updated' ) );
		}
	}

	/**
	 * version_updated.
	 *
	 * @version 1.2.0
	 * @since   1.2.0
	 */
	function version_updated() {
		update_option( 'alg_wc_custom_payment_gateways_version', $this->version );
	}

	/**
	 * Add Custom Payment Gateways settings tab to WooCommerce settings.
	 *
	 * @version 1.2.0
	 * @since   1.0.0
	 */
	function add_woocommerce_settings_tab( $settings ) {
		$settings[] = require_once( 'includes/settings/class-alg-wc-settings-custom-payment-gateways.php' );
		return $settings;
	}

	/**
	 * Get the plugin url.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 * @return  string
	 */
	function plugin_url() {
		return untrailingslashit( plugin_dir_url( __FILE__ ) );
	}

	/**
	 * Get the plugin path.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 * @return  string
	 */
	function plugin_path() {
		return untrailingslashit( plugin_dir_path( __FILE__ ) );
	}

}

endif;

if ( ! function_exists( 'alg_wc_custom_payment_gateways' ) ) {
	/**
	 * Returns the main instance of Alg_WC_Custom_Payment_Gateways to prevent the need to use globals.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 * @return  Alg_WC_Custom_Payment_Gateways
	 */
	function alg_wc_custom_payment_gateways() {
		return Alg_WC_Custom_Payment_Gateways::instance();
	}
}

alg_wc_custom_payment_gateways();
