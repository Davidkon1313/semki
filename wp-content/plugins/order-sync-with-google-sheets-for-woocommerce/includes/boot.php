<?php
/**
 * Base file for FlexOrder
 * Since 1.2.2
 *
 * @package  OrderSyncWithGoogleSheetForWooCommerce
 */

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

/**
 * Optional development mode
 *
 * @version 1.0.0
 */
define( 'OSGSW_DEBUG', true );
/**
 * Required constants for the plugin
 */
define( 'OSGSW_PREFIX', 'osgsw_' );

define( 'OSGSW_PATH', plugin_dir_path( OSGSW_FILE ) );
define( 'OSGSW_URL', plugin_dir_url( OSGSW_FILE ) );
define( 'OSGSW_INCLUDES', OSGSW_PATH . 'includes/' );
define( 'OSGSW_TEMPLATES', OSGSW_PATH . 'templates/' );
define( 'OSGSW_PUBLIC', OSGSW_URL . 'public/' );


/**
 * Common functions outside OOP
 */
if ( file_exists( OSGSW_INCLUDES . 'helper/functions.php' ) ) {
	require_once OSGSW_INCLUDES . 'helper/functions.php';
}
if ( file_exists( OSGSW_INCLUDES . 'helper/trait-utilities.php' ) ) {
	require_once OSGSW_INCLUDES . 'helper/trait-utilities.php';
}

if ( file_exists( OSGSW_INCLUDES . 'ordersync-sdk/class-plugin.php' ) ) {
	require_once OSGSW_INCLUDES . 'ordersync-sdk/class-plugin.php';
}
/**
 * Required classes
 */
if ( file_exists( OSGSW_INCLUDES . 'classes/class-base.php' ) ) {
	require_once OSGSW_INCLUDES . 'classes/class-base.php';
}
if ( file_exists( OSGSW_INCLUDES . 'classes/class-app.php' ) ) {
	require_once OSGSW_INCLUDES . 'classes/class-app.php';
}
// Load models.
if ( file_exists(OSGSW_INCLUDES . 'models/class-column.php') ) {
	include_once OSGSW_INCLUDES . 'models/class-column.php';
}
if ( file_exists(OSGSW_INCLUDES . 'models/class-order.php') ) {
	include_once OSGSW_INCLUDES . 'models/class-order.php';
}
if ( file_exists(OSGSW_INCLUDES . 'models/class-sheet.php') ) {
	include_once OSGSW_INCLUDES . 'models/class-sheet.php';
}

if ( file_exists( OSGSW_INCLUDES . 'classes/class-install.php' ) ) {
	require_once OSGSW_INCLUDES . 'classes/class-install.php';
}
if ( file_exists( OSGSW_INCLUDES . 'classes/class-hooks.php' ) ) {
	require_once OSGSW_INCLUDES . 'classes/class-hooks.php';
}
if ( file_exists( OSGSW_INCLUDES . 'classes/class-api.php' ) ) {
	require_once OSGSW_INCLUDES . 'classes/class-api.php';
}
/**
 * Load ajax
 */
if ( file_exists( OSGSW_INCLUDES . 'classes/class-ajax.php' ) && wp_doing_ajax() ) {
	require_once OSGSW_INCLUDES . 'classes/class-ajax.php';
}

/**
 * Load Popup
 */
if ( file_exists( OSGSW_INCLUDES . 'classes/class-popup.php' ) ) {
	require_once OSGSW_INCLUDES . 'classes/class-popup.php';
}
