<?php

/**
 * Class Install
 * includes/class/Install.php
 * Executes the installation process  
 */

namespace OrderSyncWithGoogleSheetForWooCommerceUltimate;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

if ( ! class_exists( "\OrderSyncWithGoogleSheetForWooCommerceUltimate\App" ) ) {
    class App {
        /**
		 * Is license active
		 *
		 * @var bool
		 */
		public $license_active = null;
        /**
         * Free version path
         */
        public $free_version = "order-sync-with-google-sheets-for-woocommerce/order-sync-with-google-sheets-for-woocommerce.php";
        /**
         * Is the main plugin installed
         */
        public function is_plugin_installed() {
           // Check if WooCommerce is installed in plugin folder.
           if ( file_exists( WP_PLUGIN_DIR . '/' .  $this->free_version ) ) {
               return true;
            }
            return false; 
        }
        /**
         * Check if the license is activated
         */
        public function is_license_active() {
            return apply_filters('is_osgsw_license_active', false );
        }
        /**
		 * Get Appsero Client
		 *
		 * @return \Appsero\Client|void
		 */
		public function get_appsero_client() {

			if ( ! class_exists( '\OrderSyncWithGoogleSheetForWooCommerceUltimate\Appsero\Client' ) ) {
                require_once OSGSW_ULTIMATE_INCLUDES . '/appsero/Client.php';
			}
            require_once OSGSW_ULTIMATE_INCLUDES . '/appsero/Updater.php';
			// appsero_is_local FALSE.
			// add_filter( 'appsero_is_local', '__return_false' );

			return new \OrderSyncWithGoogleSheetForWooCommerceUltimate\Appsero\Client(
				'2cc581a5-2990-48d5-8c06-8e8b6cdf654d',
				'Order Sync with Google Sheets for WooCommerce Ultimate',
				OSGSW_ULTIMATE
			);
		}
        /**
		 * Is License Activated
		 *
		 * @return bool
		 */
		public function is_license_actived_value() {
			global $osgsw_license;

			if ( is_null( $this->license_active ) ) {
				return false;
			}

			return $osgsw_license->is_valid();
		}
    }
}
