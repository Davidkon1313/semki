<?php

/**
 * Base file for Order Sync With Google Sheet For WooCommerce
 * Since 1.2.2
 */
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );


/**
 * Required constants for the plugin
 */ 
define( 'OSGSW_ULTIMATE_TEXT_DOMAIN', 'order-sync-with-google-sheets-for-woocommerce-ultimate');

define( 'OSGSW_ULTIMATE_PATH', plugin_dir_path( OSGSW_ULTIMATE ) );

define( 'OSGSW_ULTIMATE_INCLUDES', OSGSW_ULTIMATE_PATH . 'includes/' );  

if ( file_exists( OSGSW_ULTIMATE_INCLUDES . 'classes/class-app.php' ) ) {
    require_once OSGSW_ULTIMATE_INCLUDES . 'classes/class-app.php';
}

if ( file_exists( OSGSW_ULTIMATE_INCLUDES . 'classes/class-hooks.php' ) ) {
    require_once OSGSW_ULTIMATE_INCLUDES . 'classes/class-hooks.php';
}
