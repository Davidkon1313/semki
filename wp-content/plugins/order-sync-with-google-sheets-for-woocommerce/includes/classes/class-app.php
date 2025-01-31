<?php
/**
 * Class App
 * includes/class/App.php
 * Holds the main application logic
 *
 * @package OrderSyncWithGoogleSheetForWooCommerce
 */

namespace OrderSyncWithGoogleSheetForWooCommerce;

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );
if ( ! class_exists( '\OrderSyncWithGoogleSheetForWooCommerce\App' ) ) {
	/**
	 * Class App
	 *
	 * @version 1.0.0
	 */
	class App {
		/**
		 * Utilities Trait to use in all classes globally
		 */
		use Utilities;

		/**
		 * Woocommerce file store
		 *
		 * @var string
		 */
		public $woocommerce = 'woocommerce/woocommerce.php';
		/**
		 * WooCommerce Unlimited Active
		 *
		 * @var string
		 */
		public $ultimate = 'order-sync-with-google-sheets-for-woocommerce-ultimate/order-sync-with-google-sheets-for-woocommerce-ultimate.php';
		/**
		 * Default options
		 *
		 * @version 1.0.0
		 * @return array
		 */
		public function get_default_options() {

			$options = [
				'credentials'                => [],
				'credential_file'            => '',
				'spreadsheet_url'            => '',
				'spreadsheet_id'             => '',
				'sheet_tab'                  => 'Sheet1',
				'sheet_id'                   => '',
				'show_custom_fields'         => [],
				'setup_step'                 => 0,
				'add_order_form_sheet'       => false,
				'freeze_headers'             => false,
				'order_total'                => false,
				'payment_method'             => false,
				'show_total_sales'           => false,
				'customer_note'              => false,
				'order_url'                  => false,
				'add_shipping_details_sheet' => false,
				'total_discount'             => false,
				'show_order_date'            => false,
				'show_payment_method'        => false,
				'show_customer_note'         => false,
				'show_order_url'             => false,
				'who_place_order'            => false,
				'show_product_qt'            => false,
				'show_custom_meta_fields'    => false,
				'bulk_edit_option2'           => true,
				'sync_order_status'          => true,
				'sync_order_id'              => true,
				'sync_order_products'        => true,
				'sync_total_items'           => true,
				'sync_total_price'           => true,
				'custom_order_status_bolean' => false,
				'show_billing_details'       => false,
				'show_order_note'            => false,
				'multiple_items_enable_first' => false,
				'multiple_itmes'             => false,
				'product_sku_sync'           => false,
				'token'                      => '',
				'save_and_sync'              => false,
			];

			return apply_filters( 'osgsw_options', $options );
		}

		/**
		 * Get options all options form database
		 *
		 * @version 1.0.0
		 * @return object
		 */
		public function get_options() {
			$osgsw_options = [];
			foreach ( $this->get_default_options() as $key => $value ) {
				$osgsw_options[ $key ] = osgsw_get_option( $key );
			}
			$osgsw_options = (object) $osgsw_options;
			return $osgsw_options;
		}

		/**
		 * Is WooCommerce installed?
		 *
		 * @versopm 1.0.0
		 * @return bool
		 */
		public function is_woocommerce_installed() {
			// Check if WooCommerce is installed in plugin folder.
			if ( file_exists( WP_PLUGIN_DIR . '/' . $this->woocommerce ) ) {
				return true;
			}
			return false;
		}

		/**
		 * Is WooCommerce activated?
		 *
		 * @version 1.0.0
		 * @return bool
		 */
		public function is_woocommerce_activated() {
			// Check if WooCommerce is activated.
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
			if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
				return true;
			} elseif ( is_plugin_active_for_network( 'woocommerce/woocommerce.php' ) ) {
				return true;
			}

			return false;
		}

		/**
		 * Is Ultimate version installed
		 */
		public function is_ultimate_installed() {
			// Check if Ultimate is installed in plugin folder.
			if ( file_exists( WP_PLUGIN_DIR . '/' . $this->ultimate ) ) {
				return true;
			}

			return false;
		}

		/**
		 * Is Ultimate activated?
		 *
		 * @version 1.0.0
		 * @return bool
		 */
		public function is_ultimate_activated() {
			if ( ! function_exists( 'is_plugin_active' ) ) {
				include_once ABSPATH . 'wp-admin/includes/plugin.php';
			}
			// Check if Ultimate is activated.
			if ( is_plugin_active( $this->ultimate ) ) {
				return true;
			} else {
				return false;
			}
		}
		/**
		 * Is Ultimate License activated?
		 *
		 * @return bool
		 * @version 1.0.0
		 */
		public function is_ultimate_license_activated() {
			$ultimate_license = get_option( 'osgsw_license_active' );
			if ( true == $ultimate_license ) { //phpcs:ignore
				return true;
			}
			return false;
		}
		/**
		 * Is License Activated
		 *
		 * @return bool
		 */
		public function is_license_actived_value() {
			global $osgsw_license;
			// print_r($osgsw_license);

			if ( ! $osgsw_license ) {
				return false;
			}

			return $osgsw_license->is_valid();
		}
		/**
		 * IF Setup Complete
		 *
		 * @version 1.0.0
		 * @return mixed
		 */
		public function is_setup_complete() {
			$options = $this->get_options();
			return $options->setup_step >= 5;
		}
		/**
		 * If Plugin Ready to use
		 *
		 * @version 1.0.0
		 * @return bool
		 */
		public function is_plugin_ready() {
			$options = $this->get_options();
			if ( ! $options ) {
				return new \Exception( __( 'Setup not complete yet', 'order-sync-with-google-sheets-for-woocommerce' ) );
			}

			if ( empty( $options->credentials ) ) {
				return new \Exception( __( 'Credentials not set', 'order-sync-with-google-sheets-for-woocommerce' ) );
			}

			if ( empty( $options->spreadsheet_url ) ) {
				return new \Exception( __( 'Spreadsheet URL not set', 'order-sync-with-google-sheets-for-woocommerce' ) );
			}

			if ( empty( $options->sheet_tab ) ) {
				return new \Exception( __( 'Sheet tab not set', 'order-sync-with-google-sheets-for-woocommerce' ) );
			}
			if ( ! $this->is_woocommerce_activated() ) {
				return new \Exception( __( 'WooCommerce not activated', 'order-sync-with-google-sheets-for-woocommerce' ) );
			}

			return true;
		}

		/**
		 * Localized Scripts
		 *
		 * @version 1.0.0
		 * @return array
		 */
		public function localized_script() {
			$script_file = plugin_dir_path( OSGSW_FILE ) . '/public/js/AppsScript.js';
			require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php';
			require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php';
			$script = '';

			if ( file_exists( $script_file ) ) {
				$wp_filesystem = new \WP_Filesystem_Direct( null );
				$script        = $wp_filesystem->get_contents( $script_file );
			}

			$page_name = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';
			$keys = [
				'ajax_url'                      => admin_url( 'admin-ajax.php' ),
				'site_url'                      => site_url(),
				'nonce'                         => wp_create_nonce( 'osgsw_nonce' ),
				'is_ultimate_license_activated' => $this->is_ultimate_license_activated(),
				'is_ultimate_installed'         => $this->is_ultimate_installed(),
				'is_woocommerce_activated'      => $this->is_woocommerce_activated(),
				'is_woocommerce_installed'      => $this->is_woocommerce_installed(),
				'is_setup_complete'             => $this->is_setup_complete(),
				'is_plugin_ready'               => $this->is_plugin_ready(),
				'options'                       => $this->get_options(),
				'public_url'                    => OSGSW_PUBLIC,
				'order_statuses'                => $this->all_order_statuses(),
				'apps_script'                   => $script,
				'currentScreen'                 => get_current_screen(),
				'page_name'                     => $page_name,
				'limit'                         => apply_filters( 'osgsw_order_limit', 20 ),
				'osgsw_license_trigger'         => get_option('osgsw_license_sync', false),
				'is_debug'                      => defined( 'WP_DEBUG' ) && WP_DEBUG,
			];

			return apply_filters( 'osgsw_localized_script', $keys );
		}
		/**
		 * Get all order statuses
		 *
		 * @return mixed
		 * @version 1.6.1
		 */
		public function all_order_statuses() {
			if ( $this->is_woocommerce_activated() ) {
				$statuses = wc_get_order_statuses();
				$keys = array_keys($statuses);
				return wp_json_encode($keys);
			}
		}

		/**
		 * Reset all options
		 *
		 * @param mixed $force force reset all options.
		 *
		 * @version 1.0.0
		 * @return bool
		 */
		public function reset_options( $force = false ) {
			$options = $this->get_default_options();
			foreach ( $options as $key => $value ) {
				if ( $force ) {
					delete_option( OSGSW_PREFIX . $key );
				}
				add_option( OSGSW_PREFIX . $key, $value );
			}
			return true;
		}
		/**
		 * All order columns
		 *
		 * @return array
		 *
		 * @version 1.0.0
		 */
		public function get_order_columns() {
			$columns = [
				'order_date'           => 'Order Date',
				'order_id'             => 'Order ID',
				'products_name'        => 'Orders Name',
				'order_status'         => 'Order Status',
				'order_quoantity'      => 'Order Quoantity',
				'total_price'          => 'Total Price',
				'discount_total'       => 'Total Discount',
				'payment_method'       => 'Payment Method',
				'shipping_information' => 'Shipping Information',
				'order_url'            => 'Order URL',
				'customer_note'        => 'Customer Note',
			];

			return apply_filters( 'osgsw_order_columns', $columns );
		}
		/**
		 * Check WooCommerce Active or not and active manually.
		 *
		 * @version 1.0.0
		 *
		 * @return bool
		 */
		public function activate_woocommerce() {
			if ( ! defined( 'WP_ADMIN' ) ) {
				define( 'WP_ADMIN', true );
			}
			if ( ! defined( 'WP_USER_ADMIN' ) ) {
				define( 'WP_USER_ADMIN', true );
			}
			if ( ! defined( 'WP_NETWORK_ADMIN' ) ) {
				define( 'WP_NETWORK_ADMIN', true );
			}
			$woocoomerce = ABSPATH . 'wp-content/plugins/woocommerce/woocommerce.php';

			if ( file_exists( $woocoomerce ) ) {
				require_once ABSPATH . 'wp-admin/includes/admin.php';
				require_once ABSPATH . 'wp-admin/includes/upgrade.php';
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
				activate_plugin( $woocoomerce );
			} else {
				require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
				require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
				require_once ABSPATH . 'wp-admin/includes/class-wp-ajax-upgrader-skin.php';
				require_once ABSPATH . 'wp-admin/includes/class-plugin-upgrader.php';
				// Get Plugin Info.
				$api = plugins_api(
					'plugin_information',
					[
						'slug' => 'woocommerce',
						'fields' => [
							'short_description' => false,
							'sections'          => false,
							'requires'          => false,
							'rating'            => false,
							'ratings'           => false,
							'downloaded'        => false,
							'last_updated'      => false,
							'added'             => false,
							'tags'              => false,
							'compatibility'     => false,
							'homepage'          => false,
							'donate_link'       => false,
						],
					]
				);
				$skin     = new \WP_Ajax_Upgrader_Skin();
				$upgrader = new \Plugin_Upgrader( $skin );
				$upgrader->install( $api->download_link );
				activate_plugin( $woocoomerce );
			}

			return 1;
		}


		/**
		 * Appscript update notice after order status update
		 */
		public function appscript_update_notice() {
			$already_updated = get_option('osgsw_already_update');
			$new_user_activate = get_option('osgsw_new_user_activate');
			$update_flag = get_option('osgsw_update_flag') ? get_option('osgsw_update_flag') : '0';

			if ( ( '0' === $update_flag ) && ( '0' !== $already_updated || '0' !== $new_user_activate ) ) {
				update_option('osgsw_already_update', '0');
				update_option('osgsw_new_user_activate', '0');
				update_option('osgsw_update_flag', '1');
			}
		}
	}
}
