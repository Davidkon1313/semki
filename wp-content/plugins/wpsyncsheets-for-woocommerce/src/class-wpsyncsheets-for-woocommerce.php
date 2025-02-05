<?php
	/**
	 * Main WPSyncSheets_For_WooCommerce namespace.
	 *
	 * @since 1.0.0
	 * @package wpsyncsheets-for-woocommerce
	 */

namespace WPSyncSheets_For_WooCommerce {
	/**
	 * Main WPSyncSheets_For_WooCommerce class.
	 *
	 * @since 1.0.0
	 * @package wpsyncsheets-for-woocommerce
	 */
	final class WPSyncSheets_For_WooCommerce {
		/**
		 * Instance of this class.
		 *
		 * @since 1.0.0
		 *
		 * @var \WPSyncSheets_For_WooCommerce\WPSyncSheets_For_WooCommerce
		 */
		private static $instance;
		/**
		 * Plugin version for enqueueing, etc.
		 *
		 * @since 1.0.0
		 *
		 * @var string
		 */
		public $version = '';
		/**
		 * Main WPSyncSheets_For_WooCommerce Instance.
		 * Only one instance of WPSyncSheets_For_WooCommerce exists in memory at any one time.
		 * Also prevent the need to define globals all over the place.
		 *
		 * @since 1.0.0
		 *
		 * @return WPSyncSheets_For_WooCommerce
		 */
		public static function instance() {
			if ( null === self::$instance || ! self::$instance instanceof self ) {
				self::$instance = new self();
				self::$instance->constants();
				self::$instance->includes();
				add_action( 'init', array( self::$instance, 'load_textdomain' ), 10 );
			}
			return self::$instance;
		}

		/**
		 * Setup plugin constants.
		 * All the path/URL related constants are defined in main plugin file.
		 *
		 * @since 1.0.0
		 */
		private function constants() {
			$this->version = WPSSW_VERSION;
		}
		/**
		 * Load the plugin language files.
		 *
		 * @since 1.0.0
		 */
		public function load_textdomain() {
			// If the user is logged in, unset the current text-domains before loading our text domain.
			// This feels hacky, but this way a user's set language in their profile will be used,
			// rather than the site-specific language.
			if ( is_user_logged_in() ) {
				unload_textdomain( 'wpssw' );
			}
			load_plugin_textdomain( 'wpssw', false, WPSSW_DIRECTORY . '/languages/' );
		}
		/**
		 * Include files.
		 *
		 * @since 1.0.0
		 */
		private function includes() {
			// Global Includes.
			require_once WPSSW_PLUGIN_PATH . '/includes/class-wpssw-include-action.php';
			require_once WPSSW_PLUGIN_PATH . '/includes/class-wpssw-google-api.php';
			require_once WPSSW_PLUGIN_PATH . '/includes/class-wpssw-google-api-functions.php';
			require_once WPSSW_PLUGIN_PATH . '/includes/class-wpssw-plugin-update.php';
			require_once WPSSW_PLUGIN_PATH . '/includes/class-wpssw-license.php';
			require_once WPSSW_PLUGIN_PATH . '/includes/class-wpssw-setting.php';
			$this->wpssw_include_module_files();
			$this->wpssw_include_import_files();
		}

		/**
		 * Include Module files.
		 *
		 * @since 1.0.0
		 */
		private function wpssw_include_module_files() {
			foreach ( glob( WPSSW_PLUGIN_PATH . '/includes/modules/*.php' ) as $filename ) {
				include $filename;
			}

		}
		/**
		 * Include import files.
		 */
		private function wpssw_include_import_files() {
			foreach ( glob( WPSSW_PLUGIN_PATH . '/includes/product/import/*.php' ) as $filename ) {
				include $filename;
			}
			foreach ( glob( WPSSW_PLUGIN_PATH . '/includes/order/import/*.php' ) as $filename ) {
				include $filename;
			}
			foreach ( glob( WPSSW_PLUGIN_PATH . '/includes/customer/import/*.php' ) as $filename ) {
				include $filename;
			}
			foreach ( glob( WPSSW_PLUGIN_PATH . '/includes/coupon/import/*.php' ) as $filename ) {
				include $filename;
			}
		}
	}
}

namespace {
	/**
	 * The function which returns the one WPSSW instance.
	 *
	 * @since 1.0.0
	 *
	 * @return WPSSW\wpssw
	 */
	function wpssw() {
		return WPSyncSheets_For_WooCommerce\WPSyncSheets_For_WooCommerce::instance();
	}
	class_alias( 'WPSyncSheets_For_WooCommerce\WPSyncSheets_For_WooCommerce', 'WPSyncSheets_For_WooCommerce' );
}
