<?php
/**
 * Plugin Name: FlexOrder
 * Plugin URI: https://wcordersync.com/
 * Description: Sync WooCommerce orders with Google Sheets. Perform WooCommerce order sync, e-commerce order management and sales order management from Google Sheets.
 * Version: 1.12.1
 * Author: WC Order Sync
 * Author URI: https://wcordersync.com/
 * Text Domain: order-sync-with-google-sheets-for-woocommerce
 * Domain Path: /languages
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package OrderSyncWithGoogleSheetForWooCommerce
 */

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

/**
 * Base File for the plugin
 */
define( 'OSGSW_FILE', __FILE__ );
define( 'OSGSW_VERSION', '1.12.1' );
/**
 * Loading base file
 * Load plugin
 * If you are a developer, please don't change this file location
 */
if ( file_exists( __DIR__ . '/includes/boot.php' ) ) {
	require_once __DIR__ . '/includes/boot.php';
}


/**
 * Loaded plugin text domain for translation
 *
 * @return mexed
 */
function osgsw_load_plugin_textdomain() {
	$domain = 'order-sync-with-google-sheets-for-woocommerce';
	$dir    = untrailingslashit( WP_LANG_DIR );
	$locale = apply_filters( 'plugin_locale', get_locale(), $domain );
	$exists = load_textdomain( $domain, $dir . '/plugins/' . $domain . '-' . $locale . '.mo' );
	if ( $exists ) {
		return $exists;
	} else {
		load_plugin_textdomain( $domain, false, basename( __DIR__ ) . '/languages/' );
	}
}
add_action('plugins_loaded', 'osgsw_load_plugin_textdomain');
/**
 * Manipulating the plugin code WILL NOT ALLOW you to use the premium features.
 * Please download the free version of the plugin from https://wordpress.org/plugins/order-sync-with-google-sheets-for-woocommerce/
 */
