<?php

/**
 * Class Install
 * Includes/class/Install.php
 * Executes the installation process
 *
 * @package OrderSyncWithGoogleSheetForWooCommerce
 */

namespace OrderSyncWithGoogleSheetForWooCommerce;

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

if ( ! class_exists( '\OrderSyncWithGoogleSheetForWooCommerce\Install' ) ) {
	/**
	 * Class Install
	 *
	 * @version 1.0.0
	 */
	class Install extends Base {
		/**
		 * Singleton modes
		 *
		 * @var bool
		 */
		public static $instance = null;
		/**
		 * Install init single instance
		 *
		 * @var bool
		 */
		public static function init() {
			if ( ! self::$instance ) {
				self::$instance = new self();
			}
			register_activation_hook( OSGSW_FILE, [ self::$instance, 'activate' ] );
			register_deactivation_hook( OSGSW_FILE, [ self::$instance, 'deactivate' ] );

			add_action( 'pre_current_active_plugins', [ self::$instance, 'admin_notices' ] );
			self::$instance->app->reset_options( false );
		}

		/**
		 * Activate the plugin
		 */
		public function activate() {
			update_option( 'osgsw_free_active', 1 );
			$this->reset_auto_redirection();
			$this->initialize_authorization_token();
			update_option( OSGSW_PREFIX . 'sync_total_items', true );
			update_option( OSGSW_PREFIX . 'sync_total_price', true );
			$sheet_url = get_option( 'osgsw_spreadsheet_url', '' );
			if ( empty( $sheet_url ) ) {
				update_option( 'osgsw_new_user_activate_bulk', '1' );
				update_option( 'osgsw_new_user_activate_trigger_1_11_4', '1' );
			} else {
				$this->appscript_update_notice();
			}
		}
		/**
		 * Deactivate the plugin
		 */
		public function deactivate() {
			update_option( 'osgsw_free_active', 0 );
			$this->reset_auto_redirection();
			if ( get_option( 'osgsw_install_times' ) ) {
				delete_option( 'osgsw_install_times' );
			}
		}
		/**
		 * Reset the auto redirection
		 */
		public function reset_auto_redirection() {
			osgsw_update_option( 'redirect_to_admin_page', 1 );
		}

		/**
		 * Initialize the authorization token
		 */
		public function initialize_authorization_token() {
			$token = osgsw_get_option( 'token' );
			if ( empty( $token ) ) {
				$token = bin2hex( random_bytes( 14 ) );
				osgsw_update_option( 'token', $token );
			}
		}

		/**
		 * Appscript update notice after activation
		 */
		public function appscript_update_notice() {
			$already_updated = get_option('osgsw_already_update_bulk');
			$new_user_activate = get_option('osgsw_new_user_activate_bulk');

			$update_flag = get_option('osgsw_update_flag') ? get_option('osgsw_update_flag') : '0';

			if ( ( '0' === $update_flag ) && ( '0' !== $already_updated || '0' !== $new_user_activate ) ) {
				// The options are not equal to 0, so update them and set the flag.
				update_option('osgsw_already_update_bulk', '0');
				update_option('osgsw_new_user_activate_bulk', '0');
				// Now set a flag to indicate that the options have been updated.
				update_option('osgsw_update_flag', '1');
			}
		}

		/**
		 * Admin notices
		 */
		public function admin_notices() {

			if ( osgsw()->is_woocommerce_activated() ) {
				return;
			}

			if ( ! current_user_can( 'activate_plugins' ) ) {
				return;
			}
			$woocommerce = 'woocommerce/woocommerce.php';
			$plugin_name  = __( 'FlexOrder', 'order-sync-with-google-sheets-for-woocommerce' );

			if ( osgsw()->is_woocommerce_installed() ) {
				$activation_url = wp_nonce_url( 'plugins.php?action=activate&amp;plugin=' . $woocommerce . '&amp;plugin_status=all&amp;paged=1&amp;s', 'activate-plugin_' . $woocommerce );

				$message        = wp_sprintf( '<strong>%s</strong> requires <strong>WooCommerce</strong> plugin to be activated.', $plugin_name );
				$button_text    = __( 'Activate WooCommerce', 'order-sync-with-google-sheets-for-woocommerce' );
			} else {
				$activation_url = wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=woocommerce' ), 'install-plugin_woocommerce' );
				$message        = wp_sprintf( '<strong>%s</strong> requires <strong>WooCommerce</strong> plugin to be installed and activated.', $plugin_name );
				$button_text    = __( 'Install WooCommerce', 'order-sync-with-google-sheets-for-woocommerce' );
			}

			$button = '<p><a href="' . $activation_url . '" class="button-primary">' . $button_text . '</a></p>';
			printf( '<div class="error"><p>%1$s %2$s</p></div>', wp_kses_post( $message ), wp_kses_post( $button ) );
		}
	}
	/**
	 * Initialize the plugin
	 */
	Install::init();
}
