<?php

/**
 * Class Ajax
 * includes/class/Ajax.php
 * Handles the ajax requests
 *
 * @package OrderSyncWithGoogleSheetForWooCommerce
 */

namespace OrderSyncWithGoogleSheetForWooCommerce;

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

if ( ! class_exists( '\OrderSyncWithGoogleSheetForWooCommerce\Ajax' ) ) {
	/**
	 * Handle all ajax requests
	 *
	 * @version  1.0.0
	 */
	class Ajax extends Base {
		/**
		 * Singleton modeb
		 *
		 * @version 1.0.0
		 * @var bool
		 */
		public static $instance = null;
		/**
		 * Ajax init
		 *
		 * @version 1.0.0
		 * @return void
		 */
		public static function init() {
			if ( ! self::$instance ) {
				self::$instance = new self();
			}
			self::$instance->add_ajax_actions();
		}
		/**
		 * Add ajax actions
		 *
		 * @return void
		 * @version 1.0.0
		 */
		public function add_ajax_actions() {
			$actions = [
				'update_options'       => [ $this, 'update_options_callback' ],
				'reset_options'        => [ $this, 'reset_options_callback' ],
				'init_sheet'           => [ $this, 'init_sheet_callback' ],
				'sync_sheet'           => [ $this, 'sync_sheet_callback' ],
				'reset_sheet'          => [ $this, 'reset_sheet_callback' ],
				'sync_batch_to_sheet'  => [ $this, 'sync_batch_to_sheet_callback' ],
				'activate_woocommerce' => [ $this, 'activate_woocommerce_callback' ],
			];

			foreach ( $actions as $action => $callback ) {
				add_action( 'wp_ajax_' . OSGSW_PREFIX . $action, $callback );
				// For development mode.
				if ( $this->is_development_mode() ) {
					add_action( 'wp_ajax_nopriv_' . OSGSW_PREFIX . $action, $callback );
				}
			}
		}
		/**
		 * Sync batch processing data
		 */
		public function sync_batch_to_sheet_callback() {
			$this->check_nonce();
			$this->check_permission();
			$body = $this->get_body();
			$order = new Order();
			try {
				update_option('osgsw_license_sync', false);
				$update = $order->sync_batch_all($body->offset, $body->batchSize);
				if ( $update ) {
					$this->send_json(true, $update );
				} else {
					$this->send_json(false, $update);
				}
			} catch ( \Exception $e ) {
				$this->send_json(false, $e->getMessage());
			}
		}
		/**
		 * Check nonce
		 *
		 * @version 1.0.0
		 * @return bool
		 */
		public function check_nonce() {
			if ( defined( 'OSGSW_DEBUG' ) && OSGSW_DEBUG === true ) {
				return true;
			}

			$body = $this->get_body();
			$_wpnonce = $body->_wpnonce;
			if ( ! wp_verify_nonce( $_wpnonce, 'osgsw_none' ) ) {
				$this->send_json( false, 'Invalid nonce' );
			}
		}
		/**
		 * Check permission
		 *
		 * @version 1.0.0
		 * @return bool
		 */
		public function check_permission() {
			if ( defined( 'OSGSW_DEBUG' ) && OSGSW_DEBUG === true ) {
				return true;
			}

			if ( ! current_user_can( 'manage_options' ) ) {
				$this->send_json( false, 'You do not have permission to do this' );
			}
		}

		/**
		 * Save options callback
		 *
		 * @version 1.0.0
		 * @return void
		 */
		public function update_options_callback() {
			$this->check_nonce();
			$this->check_permission();
			/**
			 * Get body from request
			 */
			$body = $this->get_body();
			if ( ! isset( $body->options ) ) {
				$this->send_json( false, __( 'Options not set', 'order-sync-with-google-sheets-for-woocommerce' ) );
			}

			$option_keys = array_keys( $this->app->get_default_options() );
			foreach ( $option_keys as $key ) {
				$value = $body->options[ $key ] ?? null;
				if ( isset( $value ) ) {
					osgsw_update_option( $key, $value );
				}
			}
			$this->send_json( true, $body, __( 'Options saved', 'order-sync-with-google-sheets-for-woocommerce' ) );
		}

		/**
		 * Reset options callback
		 *
		 * @version 1.0.0
		 * @return void
		 */
		public function reset_options_callback() {
			$this->check_nonce();
			$this->check_permission();
			$this->app->reset_options( true );
			$this->send_json( true, __( 'Options reset', 'order-sync-with-google-sheets-for-woocommerce' ) );
		}

		/**
		 * Check sheet access callback
		 *
		 * @version 1.0.0
		 * @return void
		 */
		public function init_sheet_callback() {
			$this->check_nonce();
			$this->check_permission();
			try {
				$sheet = new Sheet();
				$sheet_initialized = $sheet->initialize();
				if ( $sheet_initialized ) {
					$this->send_json( true, __( 'Sheet initialized', 'order-sync-with-google-sheets-for-woocommerce' ) );
				} else {
					osgsw_update_option( 'setup_step', 3 );
					$this->send_json( false, $sheet_initialized );
				}
			} catch ( \Throwable $e ) {
				osgsw_update_option( 'setup_step', 3 );
				$this->send_json( false, $e->getMessage() );
			}
		}

		/**
		 * Update sheet callback
		 *
		 * @version 1.0.0
		 * @return void
		 */
		public function sync_sheet_callback() {
			$this->check_nonce();
			$this->check_permission();

			try {
				$update = osgsw_order_count();
				if ( $update ) {
					$this->send_json( true, $update );
				} else {
					$this->send_json( false, $update );
				}
			} catch ( \Exception $e ) {
				$this->send_json( false, $e->getMessage() );
			}
		}

		/**
		 * Reset sheet callback
		 *
		 * @version 1.0.0
		 * @return void
		 */
		public function reset_sheet_callback() {
			$this->check_nonce();
			$this->check_permission();
			$sheet = new Sheet();
			$token_value = $sheet->get_token();

			$reset = $sheet->reset_sheet( $token_value );

			$this->send_json( true, $reset );

			if ( true === $reset ) {
				$this->send_json( true, __( 'Sheet reset', 'order-sync-with-google-sheets-for-woocommerce' ) );
			} else {
				$this->send_json( false, $reset );
			}
		}

		/**
		 * Activate WooCommerce callback
		 *
		 * @version 1.0.0
		 * @return void
		 */
		public function activate_woocommerce_callback() {
			$response = $this->app->activate_woocommerce();
			$this->send_json( $response, $response ? __( 'WooCommerce activated', 'order-sync-with-google-sheets-for-woocommerce' ) : __( 'WooCommerce not activated', 'order-sync-with-google-sheets-for-woocommerce' ) );
		}
	}
	/**
	 * Initialize Ajax
	 *
	 * @version 1.0
	 */
	Ajax::init();
}
