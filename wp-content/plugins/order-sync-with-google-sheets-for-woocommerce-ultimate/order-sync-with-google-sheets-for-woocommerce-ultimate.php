<?php 
/**
 * Plugin Name: Order Sync With Google Sheets For Woocommerce Ultimate
 * Plugin URI: https://wcordersync.com/
 * Description: Best order sync plugin for WooCommerce. Perform e-commerce order management, sales order management, and WooCommerce order sync from Google Sheets.
 * Version: 1.1.6
 * Author: WC Order Sync
 * Author URI: https://wcordersync.com/
 * Text Domain: order-sync-with-google-sheets-for-woocommerce-ultimate
 * Domain Path: /languages
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package OrderSyncWithGoogleSheetForWooCommerce
 */

defined('ABSPATH') or die('No script kiddies please!');

/**
 * Base File for the plugin
 */
define( 'OSGSW_ULTIMATE', __FILE__ );
define( 'OSGSW_ULTIMATE_VERSION', '1.1.6' );

function osgsw_ultimate_plugin_load() {
    /**
     * Loading base file
     * If you are a developer, please don't change this file location
     */
    if ( file_exists( dirname( __FILE__ ) . '/includes/boot.php' ) ) {
        require_once dirname( __FILE__ ) . '/includes/boot.php';
    }
}
add_action( 'plugins_loaded', 'osgsw_ultimate_plugin_load' );

register_deactivation_hook( OSGSW_ULTIMATE, 'osgsw_deactivate' );

function osgsw_deactivate() {
    update_option( 'osgsw_redirect_to_license_page', 0 );
    update_option( 'osgsw_license_active_first_times', 0 );
    update_option( 'osgsw_license_active', 0 );
    $items = [ 'total_discount', 'add_shipping_details_sheet', 'show_order_date', 'show_payment_method', 'show_customer_note', 'show_order_url', 'who_place_order', 'show_product_qt', 'show_billing_details', 'show_custom_meta_fields', 'custom_order_status_bolean', 'show_order_note', 'multiple_itmes', 'product_sku_sync' ];
    $value = false;
    foreach ( $items as $item ) {
        update_option( 'osgsw_' . $item, $value );
    }
    if (class_exists('OrderSyncWithGoogleSheetForWooCommerce\Order')) {
        // Use the fully qualified class name
        $order = new OrderSyncWithGoogleSheetForWooCommerce\Order();
        $order->sync_all();
        delete_option('osgsw_synced');
    } 
}
register_activation_hook( OSGSW_ULTIMATE, 'osgsw_activate' );
function osgsw_activate() {
    update_option( 'osgsw_ultimate_active', 1 );
    $items = [ 'total_discount', 'add_shipping_details_sheet', 'show_order_date', 'show_payment_method', 'show_customer_note', 'show_order_url', 'who_place_order', 'show_product_qt', 'show_billing_details', 'show_custom_meta_fields', 'custom_order_status_bolean' ,'show_order_note', 'product_sku_sync' ];
    $get_license_active = get_option('osgsw_license_active', false);
    $value = false;
    if($get_license_active) {
        $value = true;
    }
    foreach ( $items as $item ) {
        update_option( 'osgsw_' . $item, $value );
    }
    if (class_exists('OrderSyncWithGoogleSheetForWooCommerce\Order')) {
        // Use the fully qualified class name
        $order = new OrderSyncWithGoogleSheetForWooCommerce\Order();
        $order->sync_all();
        delete_option('osgsw_synced');
    } 
}

/**
 * Manipulating the plugin code WILL NOT ALLOW you to use the premium features.
 * Please download the free version of the plugin from https://wordpress.org/plugins/order-sync-with-google-sheets-for-woocommerce/
 * Powered by WPPOOL
 * https://wppool.dev/
 */