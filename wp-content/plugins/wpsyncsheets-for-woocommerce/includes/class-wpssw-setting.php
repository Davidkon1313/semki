<?php
/**
 * Main WPSyncSheets_For_WooCommerce namespace.
 *
 * @since 1.0.0
 * @package wpsyncsheets-for-woocommerce
 */

use Automattic\WooCommerce\Utilities\OrderUtil;

/**
 * Class WPSSW_Setting.
 */
class WPSSW_Setting {
	/**
	 * Plugin documentation URL
	 *
	 * @var $documentation
	 */
	protected static $documentation = 'https://docs.wpsyncsheets.com/wpssw-introduction/';
	/**
	 * Plugin sheet setting URL
	 *
	 * @var $doc_sheet_setting
	 */
	protected static $doc_sheet_setting = 'https://docs.wpsyncsheets.com/wpssw-google-sheets-api-settings/';
	/**
	 * Plugin sheet allowtocopy setting URL
	 *
	 * @var $doc_sheet_setting_allowtocopy
	 */
	protected static $doc_sheet_setting_allowtocopy = 'https://docs.wpsyncsheets.com/wpssw-plugin-settings/#allowtocopy';
	/**
	 * Plugin support URL
	 *
	 * @var $submit_ticket
	 */
	protected static $submit_ticket = 'https://www.wpsyncsheets.com/support-tickets/';
	/**
	 * Default status of post
	 *
	 * @var $wpssw_default_status
	 */
	protected static $wpssw_default_status = array( 'pending', 'processing', 'on-hold', 'completed', 'cancelled', 'refunded', 'failed' );
	/**
	 * Default sheets
	 *
	 * @var $wpssw_default_sheets
	 */
	protected static $wpssw_default_sheets = array( 'Pending Orders', 'Processing Orders', 'On Hold Orders', 'Completed Orders', 'Cancelled Orders', 'Refunded Orders', 'Failed Orders', 'Trash Orders', 'All Orders' );
	/**
	 * Plugin License Page URL
	 *
	 * @var $wpssw_license_page
	 */
	protected static $wpssw_license_page = 'wpsyncsheets-for-woocommerce&tab=wpssw-nav-license';
	/**
	 * Plugin Store URL
	 *
	 * @var $wpssw_store_url
	 */
	protected static $wpssw_store_url = WPSSW_STORE_URL;
	/**
	 * Default status slug of post
	 *
	 * @var $wpssw_default_status_slug
	 */
	protected static $wpssw_default_status_slug = array( 'wc-pending', 'wc-processing', 'wc-on-hold', 'wc-completed', 'wc-cancelled', 'wc-refunded', 'wc-failed' );
	/**
	 * Global headers
	 *
	 * @var $wpssw_global_headers
	 */
	protected static $wpssw_global_headers = array();
	/**
	 * Instance of WPSSW_Google_API_Functions
	 *
	 * @var $instance_api
	 */
	protected static $instance_api = null;
	/**
	 * Initialization
	 */
	public static function init() {
		$wpssw_include = new WPSSW_Include_Action();
		$wpssw_include->wpssw_include_plugin_hook();
		self::wpssw_google_api();
		self::wpssw_license()->init();
	}
	/**
	 * LICENSE functions
	 */
	public static function wpssw_license() {
		return WPSSW_License::instance();
	}
	/**
	 * UPDATE functions
	 */
	public static function wpssw_updater() {
		return new WPSSW_Plugin_Update();
	}

	/**
	 * Plugin Activation Hook
	 */
	public static function wpssw_activation() {
		self::wpssw_update_option( 'active_wpssw', 1 );
	}

	/**
	 * Plugin Deactivation Hook
	 */
	public static function wpssw_deactivation() {
		self::wpssw_update_option( 'active_wpssw', '' );
	}
	/**
	 * Register a plugin menu page.
	 */
	public static function wpssw_menu_page() {
		if ( ! current_user_can( 'administrator' ) ) {
			if ( ! current_user_can( 'edit_wpssw_settings' ) && class_exists( 'User_Role_Editor' ) ) {
				return;
			}
		}
		global $admin_page_hooks, $_parent_pages;
		if ( ! isset( $admin_page_hooks['wpsyncsheets'] ) ) {
			$wpssw_page = add_menu_page(
				esc_attr__( 'WPSyncSheets', 'wpssw' ),
				'WPSyncSheets',
				'manage_options',
				'wpsyncsheets',
				'',
				WPSSW_URL . 'assets/images/dashicons-wpsyncsheets.svg',
				90
			);
		}
		add_submenu_page( 'wpsyncsheets', 'WPSyncSheets For WooCommerce', 'For WooCommerce', 'manage_options', 'wpsyncsheets-for-woocommerce', __CLASS__ . '::wpssw_plugin_page' );
		if ( ! isset( $_parent_pages['documentation'] ) ) {
			add_submenu_page( 'wpsyncsheets', 'Documentation', '<div class="wpssw-support">Documentation</div>', 'manage_options', 'documentation', __CLASS__ . '::wppsw_handle_external_redirects', 99 );
		}
		if ( ! isset( $_parent_pages['support'] ) ) {
			add_submenu_page( 'wpsyncsheets', 'Support', '<div class="wpssw-support">Support</div>', 'manage_options', 'support', __CLASS__ . '::wppsw_handle_external_redirects', 99 );
		}
		self::remove_duplicate_submenu_page();
	}

	/**
	 * License Key admin notice.
	 */
	public static function wpssw_license_notice() {
		$enable = self::wpssw_check_license_key();
		if ( ! $enable ) {
			echo '
			<div class="notice notice-info wpss-notice">
				<p><strong>' . esc_html__( 'Welcome to WPSyncSheets', 'wpssw' ) . '</strong></p>
				<p>' . esc_html__( 'Please', 'wpssw' ) . ' <a href="' . esc_url( 'admin.php?page=' . self::$wpssw_license_page ) . '">' . esc_html__( 'activate', 'wpssw' ) . '</a> ' . esc_html__( 'this version of plugin to get an access for auto updates.', 'wpssw' ) . '</p>
				<p><strong>' . esc_html__( 'Important!', 'wpssw' ) . '</strong> ' . esc_html__( 'One', 'wpssw' ) . ' <a target="_blank" href="https://www.wpsyncsheets.com/terms-and-conditions/">' . esc_html__( 'starter license', 'wpssw' ) . '</a> ' . esc_html__( 'is valid only for', 'wpssw' ) . ' <strong>' . esc_html__( '1 website', 'wpssw' ) . '</strong>. ' . esc_html__( 'Running multiple websites on a single license is a copyright violation.', 'wpssw' ) . '</p>
			</div>
			';
		}
	}
	/**
	 * Documentation and Support Page Link.
	 *
	 * Redirect the documentation and support page.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public static function wppsw_handle_external_redirects() {
		// phpcs:ignore
		if ( empty( $_GET['page'] ) ) {
			return;
		}
		// phpcs:ignore
		if ( 'documentation' === $_GET['page'] ) {
			// phpcs:ignore
			wp_redirect( WPSSW_DOC_MENU_URL );
			die;
		}
		// phpcs:ignore
		if ( 'support' === $_GET['page'] ) {
			// phpcs:ignore
			wp_redirect( WPSSW_SUPPORT_MENU_URL );
			die;
		}
	}
	/**
	 * Create Google Api Instance.
	 */
	public static function wpssw_google_api() {
		if ( null === self::$instance_api ) {
			self::$instance_api = new \WPSSW_Google_API_Functions();
		}
		return self::$instance_api;
	}
	/**
	 * Remove duplicate submenu
	 * Submenu page hack: Remove the duplicate WPSyncSheets Plugin link on subpages
	 */
	public static function remove_duplicate_submenu_page() {
		remove_submenu_page( 'wpsyncsheets', 'wpsyncsheets' );
	}
	/**
	 * Loads the plugin language files.
	 * Load only the wpssw translation.
	 */
	public static function wpssw_load_textdomain() {
		load_plugin_textdomain( 'wpssw', false, WPSSW_DIRECTORY . '/languages/' );
	}
	/**
	 * Enqueue CSS and JavaScript files
	 */
	public static function wpssw_load_custom_wp_admin_style() {
		// phpcs:ignore
		if ( isset( $_GET['page'] ) && ( 'wpsyncsheets-for-woocommerce' === (string) sanitize_text_field( wp_unslash( $_GET['page'] ) ) ) ) {
			wp_register_script( 'wpssw-wp-admin-ui', WPSSW_URL . 'assets/js/wpssw-ui.js', array(), WPSSW_VERSION, true );
			wp_enqueue_script( 'wpssw-wp-admin-ui' );
			wp_register_script( 'wpssw-wp-admin', WPSSW_URL . 'assets/js/wpssw-admin-script.js', array(), WPSSW_VERSION, true );
			wp_localize_script(
				'wpssw-wp-admin',
				'admin_ajax_object',
				array(
					'ajaxurl'          => admin_url( 'admin-ajax.php' ),
					'sync_nonce_token' => wp_create_nonce( 'sync_nonce' ),
				)
			);
			wp_enqueue_script( 'wpssw-wp-admin' );

			wp_register_script( 'wpssw-bootstrap', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js', array(), '3.4.1', true );
			wp_enqueue_script( 'wpssw-bootstrap' );

			wp_register_style( 'wpssw-wp-admin-style', WPSSW_URL . 'assets/css/wpssw-admin-style.css', false, WPSSW_VERSION );
			wp_enqueue_style( 'wpssw-wp-admin-style' );
			wp_register_style( 'wpssw-wp-admin-ui', WPSSW_URL . 'assets/css/wpssw-ui.css', false, WPSSW_VERSION );
			wp_enqueue_style( 'wpssw-wp-admin-ui' );

		}

		$include_files = false;
		// phpcs:ignore
		if ( isset( $_GET['page'] ) && 'wc-orders' === (string) sanitize_text_field( wp_unslash( $_GET['page'] ) ) ){
			$include_files = true;
		} else {
			global $post_type;
			$wpssw_post_type = is_object( $post_type ) ? $post_type->name : $post_type;
			// phpcs:ignore
			if ( ( isset( $_GET['post'] ) && ( 'shop_order' === (string) $wpssw_post_type || 'product' === (string) $wpssw_post_type ) || ( isset( $_GET['post_type'] ) && ( 'shop_order' === (string) sanitize_text_field( wp_unslash( $_GET['post_type'] ) ) || 'product' === (string) sanitize_text_field( wp_unslash( $_GET['post_type'] ) ) ) ) ) ) {
				$include_files = true;
			}
		}

		if ( $include_files ) {
			wp_register_script( 'wpssw-wp-general', WPSSW_URL . 'assets/js/wpssw-general-script.js', array(), WPSSW_VERSION, true );
			wp_localize_script(
				'wpssw-wp-general',
				'admin_ajax_object',
				array(
					'ajaxurl'          => admin_url( 'admin-ajax.php' ),
					'sync_nonce_token' => wp_create_nonce( 'sync_nonce' ),
				)
			);
			wp_enqueue_script( 'wpssw-wp-general' );
			wp_register_style( 'wpssw-wp-general-style', WPSSW_URL . 'assets/css/wpssw-general-style.css', false, WPSSW_VERSION );
			wp_enqueue_style( 'wpssw-wp-general-style' );
		}
		wp_register_script( 'wpssw-general', WPSSW_URL . 'assets/js/wpssw-general.js', array(), WPSSW_VERSION, true );
		wp_enqueue_script( 'wpssw-general' );

		wp_add_inline_style(
	        'wp-admin',
	        '#toplevel_page_wpsyncsheets .wp-menu-image img {
	            width: 20px;
	            height: auto;
	            filter: brightness(100);
	        }
	        #toplevel_page_wpsyncsheets:hover .wp-menu-image img {
	            filter: unset;
	        }
	        #toplevel_page_wpsyncsheets.wp-has-current-submenu .wp-menu-image img {
	            filter: brightness(100) !important;
	        }'
	    );
	}
	/**
	 * Show row meta on the plugin screen.
	 *
	 * @param mixed $wpssw_links Plugin Row Meta.
	 * @param mixed $wpssw_file  Plugin Base file.
	 * @return  array
	 */
	public static function wpssw_plugin_row_meta( $wpssw_links, $wpssw_file ) {
		if ( 'wpsyncsheets-for-woocommerce/wpsyncsheets-for-woocommerce.php' === (string) $wpssw_file ) {
			$wpssw_row_meta = array(
				'docs' => '<a href="' . self::$documentation . '" title="' . esc_attr( __( 'View Documentation', 'wpssw' ) ) . '" target="_blank">' . __( 'View Documentation', 'wpssw' ) . '</a>',
			);
			return array_merge( $wpssw_links, $wpssw_row_meta );
		}
		return (array) $wpssw_links;
	}
	/**
	 * Show wpssw plugin screen.
	 */
	public static function wpssw_plugin_page() {
		$wpssw_error           = '';
		$wpssw_token_error     = false;
		$wpssw_error_general   = '';
		$wpssw_apisettings     = '';
		$wpssw_generalsettings = '';
		$wpssw_emsettings      = '';
		$wpssw_supportsettings = '';
		if ( ! isset( $_GET['tab'] ) ) {
			// Google API Settings Tab.
			if ( isset( $_POST['submit'] ) || isset( $_POST['revoke'] ) ) {
				if ( ! isset( $_POST['wpssw_api_settings'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wpssw_api_settings'] ) ), 'save_api_settings' ) ) {
					$wpssw_error = '<div class="error token_error"><p><strong class="err-msg">Error: Sorry, your nonce did not verify.</strong></p></div>';
				} else {
					if ( isset( $_POST['client_token'] ) ) {
						$wpssw_clienttoken = sanitize_text_field( wp_unslash( $_POST['client_token'] ) );
					} else {
						$wpssw_clienttoken = '';
					}
					if ( isset( $_POST['client_id'] ) && isset( $_POST['client_secret'] ) ) {
						$wpssw_google_settings = array( sanitize_text_field( wp_unslash( $_POST['client_id'] ) ), sanitize_text_field( wp_unslash( $_POST['client_secret'] ) ), $wpssw_clienttoken );
					} else {
						$wpssw_google_settings_value = self::wpssw_option( 'wpssw_google_settings' );
						$wpssw_google_settings       = array( $wpssw_google_settings_value[0], $wpssw_google_settings_value[1], $wpssw_clienttoken );
					}
					self::wpssw_update_option( 'wpssw_google_settings', $wpssw_google_settings );
					if ( isset( $_POST['revoke'] ) ) {
						$wpssw_google_settings    = self::wpssw_option( 'wpssw_google_settings' );
						$wpssw_google_settings[2] = '';
						self::wpssw_update_option( 'wpssw_google_settings', $wpssw_google_settings );
						self::wpssw_update_option( 'wpssw_google_accessToken', '' );
					}
				}
			}
		}
		if ( isset( $_GET['code'] ) && ! empty( sanitize_text_field( wp_unslash( $_GET['code'] ) ) ) ) {
			$wpssw_code               = sanitize_text_field( wp_unslash( $_GET['code'] ) );
			$wpssw_token_value        = $wpssw_code;
			$wpssw_google_settings    = self::wpssw_option( 'wpssw_google_settings' );
			$wpssw_google_settings[2] = $wpssw_code;
			self::wpssw_update_option( 'wpssw_google_settings', $wpssw_google_settings );
		}

		$enable = self::wpssw_check_license_key();

		if ( ! $enable || ( isset( $_GET['tab'] ) && ! $enable && 'wpssw-nav-license' !== (string) sanitize_text_field( wp_unslash( $_GET['tab'] ) ) ) ) {
			$_GET['tab'] = 'wpssw-nav-license';
		}

		// General Settings Tab.
		if ( isset( $_GET['tab'] ) && 'order-settings' === (string) sanitize_text_field( wp_unslash( $_GET['tab'] ) ) ) {
			if ( isset( $_POST['submit'] ) ) {
				if ( ! isset( $_POST['wpssw_general_settings'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wpssw_general_settings'] ) ), 'save_general_settings' ) ) {
					$wpssw_error_general = '<div class="error token_error"><p><strong class="err-msg">Error: Sorry, your nonce did not verify.</strong>';
				} else {
					WPSSW_Order::wpssw_update_settings();
				}
			}
		} elseif ( isset( $_GET['tab'] ) && 'product-settings' === (string) sanitize_text_field( wp_unslash( $_GET['tab'] ) ) ) {
			if ( isset( $_POST['submit'] ) ) {
				if ( ! isset( $_POST['wpssw_product_settings'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wpssw_product_settings'] ) ), 'save_product_settings' ) ) {
					$wpssw_error_general = '<div class="error token_error"><p><strong class="err-msg">Error: Sorry, your nonce did not verify.</strong>';
				} else {
					WPSSW_Product::wpssw_update_product_settings();
				}
			}
		} elseif ( isset( $_GET['tab'] ) && 'customer-settings' === (string) sanitize_text_field( wp_unslash( $_GET['tab'] ) ) ) {
			if ( isset( $_POST['submit'] ) ) {
				if ( ! isset( $_POST['wpssw_customer_settings'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wpssw_customer_settings'] ) ), 'save_customer_settings' ) ) {
					$wpssw_error_general = '<div class="error token_error"><p><strong class="err-msg">Error: Sorry, your nonce did not verify.</strong>';
				} else {
					WPSSW_Customer::wpssw_update_customer_settings();
				}
			}
		} elseif ( isset( $_GET['tab'] ) && 'coupon-settings' === (string) sanitize_text_field( wp_unslash( $_GET['tab'] ) ) ) {
			if ( isset( $_POST['submit'] ) ) {
				if ( ! isset( $_POST['wpssw_coupon_settings'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wpssw_coupon_settings'] ) ), 'save_coupon_settings' ) ) {
					$wpssw_error_general = '<div class="error token_error"><p><strong class="err-msg">Error: Sorry, your nonce did not verify.</strong>';
				} else {
					WPSSW_Coupon::wpssw_update_coupon_settings();
				}
			}
		} elseif ( isset( $_GET['tab'] ) && 'event-settings' === (string) sanitize_text_field( wp_unslash( $_GET['tab'] ) ) && self::wpssw_is_event_calender_ticket_active() ) {
			if ( isset( $_POST['submit'] ) ) {
				if ( ! isset( $_POST['wpssw_event_settings'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wpssw_event_settings'] ) ), 'save_event_settings' ) ) {
					$wpssw_error_general = '<div class="error token_error"><p><strong class="err-msg">Error: Sorry, your nonce did not verify.</strong>';
				} else {
					WPSSW_Event::wpssw_update_event_settings();
				}
			}
		} elseif ( isset( $_GET['tab'] ) && 'general-settings' === (string) sanitize_text_field( wp_unslash( $_GET['tab'] ) ) ) {
			if ( isset( $_POST['submit'] ) ) {
				if ( ! isset( $_POST['wpssw_general_settings'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wpssw_general_settings'] ) ), 'save_general_settings' ) ) {
					$wpssw_error_general = '<div class="error token_error"><p><strong class="err-msg">Error: Sorry, your nonce did not verify.</strong>';
				} else {
					WPSSW_General::wpssw_update_general_settings();
				}
			}
		} elseif ( isset( $_GET['tab'] ) && 'wpssw-nav-license' === (string) sanitize_text_field( wp_unslash( $_GET['tab'] ) ) ) {

			if ( isset( $_POST['wpssw_license_key'] ) ) {
				$wpssw_lince = sanitize_text_field( wp_unslash( $_POST['wpssw_license_key'] ) );
				self::wpssw_update_option( 'wpssw_license_key', $wpssw_lince );
			}
			if ( isset( $_POST['wpssw_license_activate'] ) ) {
				self::wpssw_activate_license( $_POST );
			}
			if ( isset( $_POST['wpssw_license_deactivate'] ) ) {
				self::wpssw_deactivate_license();
			}
			if ( isset( $_POST['reset_license_key_settings'] ) ) {
				try {
					$wpssw_license_check = self::wpssw_check_license_key();
					if ( true === $wpssw_license_check ) {
						self::wpssw_deactivate_license( true );
					}
					self::wpssw_update_option( 'wpssw_license_key', '' );
				} catch ( Exception $e ) {
					$wpssw_error = $e->getMessage();
				}
			}
		}
		$wpssw_license = self::wpssw_option( 'wpssw_license_key' );
		$wpssw_status  = self::wpssw_option( 'wpssw_license_status' );

		$wpssw_google_settings_value = self::wpssw_option( 'wpssw_google_settings' );

		if ( ! empty( $wpssw_google_settings_value[2] ) ) {
			if ( ! self::$instance_api->checkcredenatials() ) {
				$wpssw_error = self::$instance_api->getClient( 1 );
				if ( 'Invalid token format' === (string) $wpssw_error ) {
					$wpssw_error = '<div class="error token_error"><p><strong class="err-msg">Error: Invalid Token - Revoke Token with below settings and try again.</strong></p></div>';
				} else {
					$wpssw_error = '<div class="error token_error"><p><strong class="err-msg">Error: ' . $wpssw_error . '</strong></p></div>';
				}
				$wpssw_token_error = true;
			}
		}
		$licence_activated = '';
		if ( false !== $enable && false !== $wpssw_status && 'valid' === (string) $wpssw_status && ! empty( $wpssw_license ) ) {
			$licence_activated = 'activated';
		}
		if ( empty( $wpssw_error ) && $enable && 'activated' === (string) $licence_activated && ! empty( $wpssw_google_settings_value[2] ) ) {
			try {
				$spreadsheets_list = self::$instance_api->get_spreadsheet_listing();

				$wpssw_order_spreadsheetid     = self::wpssw_option( 'wpssw_woocommerce_spreadsheet' );
				$wpssw_product_spreadsheet_id  = self::wpssw_option( 'wpssw_product_spreadsheet_id' );
				$wpssw_coupon_spreadsheet_id   = self::wpssw_option( 'wpssw_coupon_spreadsheet_id' );
				$wpssw_customer_spreadsheet_id = self::wpssw_option( 'wpssw_customer_spreadsheet_id' );
				$wpssw_event_spreadsheet_id    = self::wpssw_option( 'wpssw_event_spreadsheet_id' );
				$spreadsheetid                 = '';
				if ( ! empty( $wpssw_order_spreadsheetid ) && array_key_exists( $wpssw_order_spreadsheetid, $spreadsheets_list ) ) {
					$spreadsheetid = $wpssw_order_spreadsheetid;
				} elseif ( ! empty( $wpssw_product_spreadsheet_id ) && array_key_exists( $wpssw_product_spreadsheet_id, $spreadsheets_list ) ) {
					$spreadsheetid = $wpssw_product_spreadsheet_id;
				} elseif ( ! empty( $wpssw_coupon_spreadsheet_id ) && array_key_exists( $wpssw_coupon_spreadsheet_id, $spreadsheets_list ) ) {
					$spreadsheetid = $wpssw_coupon_spreadsheet_id;
				} elseif ( ! empty( $wpssw_customer_spreadsheet_id ) && array_key_exists( $wpssw_customer_spreadsheet_id, $spreadsheets_list ) ) {
					$spreadsheetid = $wpssw_customer_spreadsheet_id;
				} elseif ( ! empty( $wpssw_event_spreadsheet_id ) && array_key_exists( $wpssw_event_spreadsheet_id, $spreadsheets_list ) ) {
					$spreadsheetid = $wpssw_event_spreadsheet_id;
				} else {
					$spreadsheetid = '';
				}
				if ( ! empty( $spreadsheetid ) ) {
					$wpssw_response            = self::$instance_api->get_sheet_listing( $spreadsheetid );
					$wpssw_existingsheetsnames = self::$instance_api->get_sheet_list( $wpssw_response );
				}
			} catch ( Exception $e ) {

				$error  = json_decode( $e->getMessage(), true );
				$reason = '';
				if ( isset( $error['error'] ) && is_array( $error['error'] ) ) {
					$errors = isset( $error['error']['errors'] ) ? $error['error']['errors'] : array();
					foreach ( $errors as $err ) {
						$reason = isset( $err['reason'] ) ? $err['reason'] : '';
					}
				} elseif ( isset( $error['error'] ) ) {
					$reason = $error['error'];
				}
				if ( 'insufficientPermissions' === (string) $reason ) {
					$wpssw_error = '<div class="error token_error"><p><strong class="err-msg">Error: Insufficient Permissions - Revoke Token with below settings and when generating access token select all the permissions.</strong></p></div>';
				}
				if ( 'accessNotConfigured' === (string) $reason ) {
					$wpssw_error = '<div class="error token_error"><p><strong class="err-msg">Error: Access not Configured - Please enable Google Sheets API and Google Drive API. Follow the <a target="_blank" href="https://docs.wpsyncsheets.com/wpssw-google-sheets-api-settings/" style="color:#000;">Google Sheets API Settings documentation</a> to enable APIs.</strong></p></div>';
				}
				if ( empty( $wpssw_error ) && '' !== (string) $reason ) {
					$wpssw_error = '<div class="error token_error"><p><strong class="err-msg">Error: Invalid Credentials - Reset settings and try again.</strong></p></div>';
				}
				$wpssw_token_error = true;
			}
		}
		$show_settings = false;
		if ( false === (bool) $wpssw_token_error && $enable && 'activated' === (string) $licence_activated && ! empty( $wpssw_google_settings_value[2] ) ) {
			$show_settings = true;
		}

		$disbledbtn = '';
		if ( ! $enable ) {
			$disbledbtn = 'disabled';
		}
		$inherit_style_options = array( 'yes', 'no', 'none' );
		?>
		<!-- .wrap -->
	<div class="wpssw-main-wrap">
		<div class="wpssw-header-main">
		<div class="container">
			<div class="wpssw-header-section">
			<div class="wpssw-header-left">
				<div class="wpssw-logo-section">
					<img src="<?php echo esc_url( WPSSW_URL . 'assets/images/logo.svg?ver=' ); ?><?php echo esc_attr( WPSSW_VERSION ); ?>">
				</div>
				<div class="wpssw-nav-top">
					<ul>
						<li>
							<button class="navtablinks wpssw-nav-googleapi" onclick="wpsswNavTab(event, 'wpssw-nav-googleapi')">
								<svg width="21" height="19" viewBox="0 0 21 19" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M5.21777 19C3.83444 19 2.65527 18.5125 1.68027 17.5375C0.705273 16.5625 0.217773 15.3833 0.217773 14C0.217773 12.7833 0.59694 11.7208 1.35527 10.8125C2.11361 9.90417 3.06777 9.33333 4.21777 9.1V11.175C3.63444 11.375 3.15527 11.7333 2.78027 12.25C2.40527 12.7667 2.21777 13.35 2.21777 14C2.21777 14.8333 2.50944 15.5417 3.09277 16.125C3.67611 16.7083 4.38444 17 5.21777 17C6.05111 17 6.75944 16.7083 7.34277 16.125C7.92611 15.5417 8.21777 14.8333 8.21777 14V13H14.0928C14.2261 12.85 14.3886 12.7292 14.5803 12.6375C14.7719 12.5458 14.9844 12.5 15.2178 12.5C15.6344 12.5 15.9886 12.6458 16.2803 12.9375C16.5719 13.2292 16.7178 13.5833 16.7178 14C16.7178 14.4167 16.5719 14.7708 16.2803 15.0625C15.9886 15.3542 15.6344 15.5 15.2178 15.5C14.9844 15.5 14.7719 15.4542 14.5803 15.3625C14.3886 15.2708 14.2261 15.15 14.0928 15H10.1178C9.88444 16.15 9.31361 17.1042 8.40527 17.8625C7.49694 18.6208 6.43444 19 5.21777 19ZM15.2178 19C14.2844 19 13.4386 18.7708 12.6803 18.3125C11.9219 17.8542 11.3261 17.25 10.8928 16.5H13.5678C13.8011 16.6667 14.0594 16.7917 14.3428 16.875C14.6261 16.9583 14.9178 17 15.2178 17C16.0511 17 16.7594 16.7083 17.3428 16.125C17.9261 15.5417 18.2178 14.8333 18.2178 14C18.2178 13.1667 17.9261 12.4583 17.3428 11.875C16.7594 11.2917 16.0511 11 15.2178 11C14.8844 11 14.5761 11.0458 14.2928 11.1375C14.0094 11.2292 13.7428 11.3667 13.4928 11.55L10.4428 6.475C10.0928 6.40833 9.80111 6.24167 9.56777 5.975C9.33444 5.70833 9.21777 5.38333 9.21777 5C9.21777 4.58333 9.36361 4.22917 9.65527 3.9375C9.94694 3.64583 10.3011 3.5 10.7178 3.5C11.1344 3.5 11.4886 3.64583 11.7803 3.9375C12.0719 4.22917 12.2178 4.58333 12.2178 5V5.2125C12.2178 5.27083 12.2011 5.34167 12.1678 5.425L14.3428 9.075C14.4761 9.04167 14.6178 9.02083 14.7678 9.0125C14.9178 9.00417 15.0678 9 15.2178 9C16.6011 9 17.7803 9.4875 18.7553 10.4625C19.7303 11.4375 20.2178 12.6167 20.2178 14C20.2178 15.3833 19.7303 16.5625 18.7553 17.5375C17.7803 18.5125 16.6011 19 15.2178 19ZM5.21777 15.5C4.80111 15.5 4.44694 15.3542 4.15527 15.0625C3.86361 14.7708 3.71777 14.4167 3.71777 14C3.71777 13.6333 3.83444 13.3167 4.06777 13.05C4.30111 12.7833 4.58444 12.6083 4.91777 12.525L7.26777 8.625C6.78444 8.175 6.40527 7.6375 6.13027 7.0125C5.85527 6.3875 5.71777 5.71667 5.71777 5C5.71777 3.61667 6.20527 2.4375 7.18027 1.4625C8.15527 0.4875 9.33444 0 10.7178 0C12.1011 0 13.2803 0.4875 14.2553 1.4625C15.2303 2.4375 15.7178 3.61667 15.7178 5H13.7178C13.7178 4.16667 13.4261 3.45833 12.8428 2.875C12.2594 2.29167 11.5511 2 10.7178 2C9.88444 2 9.17611 2.29167 8.59277 2.875C8.00944 3.45833 7.71777 4.16667 7.71777 5C7.71777 5.71667 7.93444 6.34583 8.36777 6.8875C8.80111 7.42917 9.35111 7.775 10.0178 7.925L6.64277 13.55C6.67611 13.6333 6.69694 13.7083 6.70527 13.775C6.71361 13.8417 6.71777 13.9167 6.71777 14C6.71777 14.4167 6.57194 14.7708 6.28027 15.0625C5.98861 15.3542 5.63444 15.5 5.21777 15.5Z" fill="#64748B"/>
</svg>

								API Integration
							</button>
						</li>
						<?php if ( true === (bool) $show_settings ) { ?>
						<li>
							<button class="navtablinks wpssw-nav-settings active" onclick="wpsswNavTab(event, 'wpssw-nav-settings')">
								<svg width="21" height="20" viewBox="0 0 21 20" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M7.46797 20L7.06797 16.8C6.8513 16.7167 6.64714 16.6167 6.45547 16.5C6.2638 16.3833 6.0763 16.2583 5.89297 16.125L2.91797 17.375L0.167969 12.625L2.74297 10.675C2.7263 10.5583 2.71797 10.4458 2.71797 10.3375V9.6625C2.71797 9.55417 2.7263 9.44167 2.74297 9.325L0.167969 7.375L2.91797 2.625L5.89297 3.875C6.0763 3.74167 6.26797 3.61667 6.46797 3.5C6.66797 3.38333 6.86797 3.28333 7.06797 3.2L7.46797 0H12.968L13.368 3.2C13.5846 3.28333 13.7888 3.38333 13.9805 3.5C14.1721 3.61667 14.3596 3.74167 14.543 3.875L17.518 2.625L20.268 7.375L17.693 9.325C17.7096 9.44167 17.718 9.55417 17.718 9.6625V10.3375C17.718 10.4458 17.7013 10.5583 17.668 10.675L20.243 12.625L17.493 17.375L14.543 16.125C14.3596 16.2583 14.168 16.3833 13.968 16.5C13.768 16.6167 13.568 16.7167 13.368 16.8L12.968 20H7.46797ZM10.268 13.5C11.2346 13.5 12.0596 13.1583 12.743 12.475C13.4263 11.7917 13.768 10.9667 13.768 10C13.768 9.03333 13.4263 8.20833 12.743 7.525C12.0596 6.84167 11.2346 6.5 10.268 6.5C9.28464 6.5 8.45547 6.84167 7.78047 7.525C7.10547 8.20833 6.76797 9.03333 6.76797 10C6.76797 10.9667 7.10547 11.7917 7.78047 12.475C8.45547 13.1583 9.28464 13.5 10.268 13.5Z" fill="#64748B"/>
</svg>
								Settings
							</button>
						</li>
						<?php } ?>
						<li>
							<button class="navtablinks wpssw-nav-license" onclick="wpsswNavTab(event, 'wpssw-nav-license')">
								<svg width="21" height="18" viewBox="0 0 21 18" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M4.85938 18V16L5.85938 15H2.85938C2.30938 15 1.83854 14.8042 1.44688 14.4125C1.05521 14.0208 0.859375 13.55 0.859375 13V2C0.859375 1.45 1.05521 0.979167 1.44688 0.5875C1.83854 0.195833 2.30938 0 2.85938 0H10.8594V2H2.85938V13H18.8594V10H20.8594V13C20.8594 13.55 20.6635 14.0208 20.2719 14.4125C19.8802 14.8042 19.4094 15 18.8594 15H15.8594L16.8594 16V18H4.85938ZM13.8594 12L8.85938 7L10.2594 5.6L12.8594 8.175V0H14.8594V8.175L17.4594 5.6L18.8594 7L13.8594 12Z" fill="#64748B"/>
</svg>
									License
							</button>
						</li>
					</ul>
				</div>
			</div>
			<div class="wpssw-header-right">
					<ul class="wpssw-header-links">
						<li class="version"><span>V<?php echo esc_html( WPSSW_VERSION ); ?></span></li>
						<li id="wpsswBtn">
							<a target="_blank" href="https://docs.wpsyncsheets.com/wpssw-introduction/">
								<svg width="16" height="20" viewBox="0 0 15 19" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M4.61806 7.6H5.54167C5.78662 7.6 6.02155 7.49991 6.19476 7.32175C6.36797 7.14359 6.46528 6.90196 6.46528 6.65C6.46528 6.39804 6.36797 6.15641 6.19476 5.97825C6.02155 5.80009 5.78662 5.7 5.54167 5.7H4.61806C4.3731 5.7 4.13817 5.80009 3.96496 5.97825C3.79175 6.15641 3.69444 6.39804 3.69444 6.65C3.69444 6.90196 3.79175 7.14359 3.96496 7.32175C4.13817 7.49991 4.3731 7.6 4.61806 7.6ZM4.61806 9.5C4.3731 9.5 4.13817 9.60009 3.96496 9.77825C3.79175 9.95641 3.69444 10.198 3.69444 10.45C3.69444 10.702 3.79175 10.9436 3.96496 11.1218C4.13817 11.2999 4.3731 11.4 4.61806 11.4H10.1597C10.4047 11.4 10.6396 11.2999 10.8128 11.1218C10.986 10.9436 11.0833 10.702 11.0833 10.45C11.0833 10.198 10.986 9.95641 10.8128 9.77825C10.6396 9.60009 10.4047 9.5 10.1597 9.5H4.61806ZM14.7778 6.593C14.7682 6.50573 14.7496 6.41975 14.7224 6.3365V6.251C14.678 6.15332 14.6187 6.06353 14.5469 5.985L9.00521 0.285C8.92886 0.211105 8.84156 0.150177 8.7466 0.1045C8.71903 0.100472 8.69104 0.100472 8.66347 0.1045C8.56965 0.0491542 8.46603 0.013627 8.35868 0H2.77083C2.03596 0 1.33119 0.300267 0.811558 0.834746C0.291926 1.36922 0 2.09413 0 2.85V16.15C0 16.9059 0.291926 17.6308 0.811558 18.1653C1.33119 18.6997 2.03596 19 2.77083 19H12.0069C12.7418 19 13.4466 18.6997 13.9662 18.1653C14.4859 17.6308 14.7778 16.9059 14.7778 16.15V6.65C14.7778 6.65 14.7778 6.65 14.7778 6.593ZM9.23611 3.2395L11.6283 5.7H10.1597C9.91477 5.7 9.67984 5.59991 9.50663 5.42175C9.33342 5.24359 9.23611 5.00196 9.23611 4.75V3.2395ZM12.9306 16.15C12.9306 16.402 12.8332 16.6436 12.66 16.8218C12.4868 16.9999 12.2519 17.1 12.0069 17.1H2.77083C2.52588 17.1 2.29095 16.9999 2.11774 16.8218C1.94453 16.6436 1.84722 16.402 1.84722 16.15V2.85C1.84722 2.59804 1.94453 2.35641 2.11774 2.17825C2.29095 2.00009 2.52588 1.9 2.77083 1.9H7.38889V4.75C7.38889 5.50587 7.68081 6.23078 8.20045 6.76525C8.72008 7.29973 9.42485 7.6 10.1597 7.6H12.9306V16.15ZM10.1597 13.3H4.61806C4.3731 13.3 4.13817 13.4001 3.96496 13.5782C3.79175 13.7564 3.69444 13.998 3.69444 14.25C3.69444 14.502 3.79175 14.7436 3.96496 14.9218C4.13817 15.0999 4.3731 15.2 4.61806 15.2H10.1597C10.4047 15.2 10.6396 15.0999 10.8128 14.9218C10.986 14.7436 11.0833 14.502 11.0833 14.25C11.0833 13.998 10.986 13.7564 10.8128 13.5782C10.6396 13.4001 10.4047 13.3 10.1597 13.3Z" fill="#464D58"/>
</svg>
<span class="tooltip-text">Documentation</span>
							</a>

						</li>
						<li>
							<a target="_blank" href="https://www.wpsyncsheets.com/support-tickets/">
								<svg width="20" height="20" viewBox="0 0 19 19" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M13.2708 2.87C13.5005 3.00037 13.7724 3.03464 14.0273 2.96532C14.2821 2.896 14.4992 2.72873 14.6311 2.5C14.7403 2.30736 14.9104 2.15639 15.1146 2.07078C15.3188 1.98517 15.5457 1.96975 15.7597 2.02694C15.9736 2.08414 16.1626 2.21071 16.2968 2.38681C16.4311 2.56291 16.5031 2.77858 16.5015 3C16.5015 3.26522 16.3961 3.51957 16.2086 3.70711C16.021 3.89464 15.7666 4 15.5013 4C15.236 4 14.9816 4.10536 14.794 4.29289C14.6065 4.48043 14.5011 4.73478 14.5011 5C14.5011 5.26522 14.6065 5.51957 14.794 5.70711C14.9816 5.89464 15.236 6 15.5013 6C16.0279 5.99966 16.5452 5.86075 17.0011 5.59723C17.4571 5.33371 17.8356 4.95486 18.0987 4.49875C18.3618 4.04264 18.5002 3.52533 18.5 2.9988C18.4998 2.47227 18.361 1.95507 18.0975 1.49917C17.834 1.04326 17.4552 0.664718 16.9991 0.401563C16.5429 0.138409 16.0255 -8.42941e-05 15.4989 3.84915e-08C14.9723 8.43711e-05 14.4549 0.138743 13.9989 0.402044C13.5428 0.665344 13.1641 1.04401 12.9008 1.5C12.8346 1.61413 12.7917 1.74022 12.7745 1.871C12.7574 2.00178 12.7662 2.13466 12.8006 2.262C12.835 2.38934 12.8943 2.50862 12.975 2.61297C13.0557 2.71732 13.1562 2.80467 13.2708 2.87ZM17.5717 10C17.3092 9.96593 17.0438 10.0373 16.8338 10.1985C16.6239 10.3597 16.4864 10.5976 16.4515 10.86C16.2416 12.5552 15.419 14.1151 14.1386 15.246C12.8583 16.3769 11.2085 17.0007 9.50006 17H3.90889L4.55903 16.35C4.74532 16.1626 4.84988 15.9092 4.84988 15.645C4.84988 15.3808 4.74532 15.1274 4.55903 14.94C3.58378 13.9611 2.92007 12.7156 2.65153 11.3603C2.38298 10.005 2.52159 8.60052 3.04992 7.32383C3.57824 6.04714 4.47263 4.95532 5.62044 4.18589C6.76825 3.41646 8.11813 3.00384 9.50006 3C9.76533 3 10.0197 2.89464 10.2073 2.70711C10.3949 2.51957 10.5003 2.26522 10.5003 2C10.5003 1.73478 10.3949 1.48043 10.2073 1.29289C10.0197 1.10536 9.76533 0.999999 9.50006 0.999999C7.80894 1.00705 6.15401 1.49024 4.72486 2.39419C3.29571 3.29815 2.15016 4.58632 1.41944 6.11112C0.688721 7.63592 0.40239 9.33568 0.593251 11.0157C0.784113 12.6956 1.44445 14.2879 2.4986 15.61L0.788246 17.29C0.64946 17.4306 0.555444 17.6092 0.518062 17.8032C0.48068 17.9972 0.501607 18.1979 0.578203 18.38C0.653238 18.5626 0.780663 18.7189 0.944417 18.8293C1.10817 18.9396 1.30093 18.999 1.49839 19H9.50006C11.692 19.0003 13.8087 18.201 15.4531 16.7521C17.0975 15.3031 18.1567 13.3041 18.4319 11.13C18.4502 10.9993 18.4424 10.8662 18.409 10.7385C18.3756 10.6107 18.3172 10.4909 18.2373 10.3858C18.1573 10.2808 18.0573 10.1926 17.9431 10.1264C17.8289 10.0602 17.7026 10.0172 17.5717 10ZM15.8814 7.07C15.6993 6.98945 15.4973 6.96508 15.3013 7L15.1212 7.06L14.9412 7.15L14.7912 7.28C14.7012 7.37215 14.6299 7.48081 14.5811 7.6C14.522 7.72473 14.4945 7.86212 14.5011 8C14.4982 8.13337 14.522 8.26597 14.5711 8.39C14.6228 8.51002 14.6976 8.61873 14.7912 8.71C14.8846 8.80268 14.9955 8.87601 15.1173 8.92577C15.2392 8.97553 15.3697 9.00076 15.5013 9C15.7666 9 16.021 8.89464 16.2086 8.70711C16.3961 8.51957 16.5015 8.26522 16.5015 8C16.5049 7.86882 16.4775 7.73868 16.4215 7.62C16.314 7.37971 16.1217 7.18745 15.8814 7.08V7.07Z" fill="#464D58"/>
</svg>
<span class="tooltip-text">Support</span>
							</a>
						</li>
						<li>
							<a class="whatsNew-toggle" href="#">
								<svg width="17" height="18" viewBox="0 0 17 18" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M15.6111 1.5157e-07C15.4944 -6.7285e-05 15.3787 0.0231658 15.2709 0.0683712C15.163 0.113577 15.065 0.179867 14.9824 0.263454C14.8999 0.34704 14.8344 0.446282 14.7897 0.555505C14.7451 0.664729 14.7222 0.781791 14.7222 0.9V1.47305C13.9725 2.41418 13.025 3.17422 11.9487 3.69785C10.8723 4.22147 9.69415 4.4955 8.5 4.5H3.16667C2.45966 4.50078 1.78183 4.7855 1.2819 5.29167C0.781971 5.79785 0.500772 6.48415 0.5 7.2V9C0.500772 9.71584 0.781971 10.4021 1.2819 10.9083C1.78183 11.4145 2.45966 11.6992 3.16667 11.7H3.5967L1.46094 16.7458C1.4029 16.8826 1.37934 17.0319 1.39237 17.1803C1.40539 17.3286 1.45461 17.4714 1.53558 17.5957C1.61656 17.7201 1.72677 17.8221 1.85631 17.8927C1.98585 17.9632 2.13068 18.0001 2.27778 18H5.83333C6.00734 18.0001 6.17753 17.9484 6.32276 17.8514C6.46799 17.7543 6.58185 17.6162 6.65018 17.4542L9.07129 11.7342C10.1652 11.8155 11.231 12.123 12.203 12.6377C13.1749 13.1524 14.0323 13.8634 14.7222 14.7268V15.3C14.7222 15.5387 14.8159 15.7676 14.9826 15.9364C15.1493 16.1052 15.3754 16.2 15.6111 16.2C15.8469 16.2 16.073 16.1052 16.2396 15.9364C16.4063 15.7676 16.5 15.5387 16.5 15.3V0.9C16.5001 0.781791 16.4771 0.664728 16.4325 0.555504C16.3878 0.44628 16.3224 0.347037 16.2398 0.263451C16.1572 0.179865 16.0592 0.113574 15.9514 0.0683688C15.8435 0.0231639 15.7279 -6.84694e-05 15.6111 1.5157e-07ZM3.16667 9.9C2.93097 9.89984 2.70497 9.80497 2.5383 9.63622C2.37164 9.46747 2.27794 9.23864 2.27778 9V7.2C2.27794 6.96135 2.37164 6.73253 2.5383 6.56378C2.70497 6.39504 2.93097 6.30016 3.16667 6.3H4.05556V9.9H3.16667ZM5.2474 16.2H3.62587L5.53038 11.7H7.15191L5.2474 16.2ZM14.7222 12.1696C12.9696 10.7077 10.7708 9.90565 8.5 9.89995H5.83333V6.29995H8.5C10.7709 6.29408 12.9696 5.4919 14.7222 4.02985V12.1696Z" fill="#464D58"/>
</svg>
<span class="tooltip-text">Change Log</span>
							</a>
						</li>
					</ul>
					<div class="whatsNew-block">
						<div class="block-header">
							<h3>Change Log</h3>
							<button type="button" class="close-block">
								<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true" class="h-6 w-6"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg>
							</button>
						</div>	
						<div class="wpssw-block-content changeLog-conntent">
							<?php
							$response = wp_remote_get( self::$wpssw_store_url . 'downloads/wpsyncsheets-for-woocommerce/?changelog=1' );
							if ( ! is_wp_error( $response ) && isset( $response['body'] ) ) {
								// phpcs:ignore
								echo $response['body'];
							}
							?>
						</div>
					</div>
			</div>
			</div>
		</div>
		</div>
		<!-- messages html -->
		<div class="alert-messages">
			<div class="container">
			</div>
		</div>
		<!-- messages html end-->		
		<div class="wpssw-tabs-main">
			<div id="wpssw-nav-googleapi" class="navtabcontent vertical-tabs" 
			<?php
			if ( $show_settings ) {
				echo "style='display:none;'"; }
			?>
			>
				<div id="googleapi-settings" class="wpassw-navtabcontent">
					<div class="wpssw-top-subtext generalSetting-section">
						<div class="generalSetting-left">
						<h4><?php echo esc_html__( 'Google API Settings', 'wpssw' ); ?></h4>
						<p><?php echo esc_html__( 'Google APIs allow you to embed Google Services in your online site. To connect Google Drive and Google Sheets with your WordPress website, you need to generate dedicated API keys. To begin the process, kindly log in to your Gmail Account and follow the link to initiate the setup ', 'wpssw' ); ?><a href="<?php echo esc_url( self::$doc_sheet_setting ); ?>" target="_blank"><?php echo esc_html__( 'click here', 'wpssw' ); ?>.</a></p>
						</div>
					</div>
					<form method="post" action="<?php echo esc_html( admin_url( 'admin.php?page=wpsyncsheets-for-woocommerce' ) ); ?>">
					<?php wp_nonce_field( 'save_api_settings', 'wpssw_api_settings' ); ?>
						<div id="universal-message-container woocommerce">
						<div class="generalSetting-section">
						<div class="integrationtabform">
							<div class="options">
								<ul class="form-table">
									<li>
										<label> <?php echo esc_html__( 'Client ID', 'wpssw' ); ?> </label>
										<div class="forminp forminp-text">
											<input type="text" id="client_id" name="client_id" value="<?php echo isset( $wpssw_google_settings_value[0] ) ? esc_attr( $wpssw_google_settings_value[0] ) : ''; ?>" size="80" class = "googlesettinginput" placeholder="Enter Client Id" 
												<?php
												if ( ! empty( $wpssw_google_settings_value[0] ) ) {
													echo 'readonly';
												}
												?>
											/>
										</div>
									</li>
									<li>
										<label> <?php echo esc_html__( 'Client Secret Key', 'wpssw' ); ?> </label>
										<div class="forminp forminp-text">
											<input type="text" id="client_secret" name="client_secret" value="<?php echo isset( $wpssw_google_settings_value[1] ) ? esc_attr( $wpssw_google_settings_value[1] ) : ''; ?>" size="80" class = "googlesettinginput" placeholder="Enter Client Secret" 
												<?php
												if ( ! empty( $wpssw_google_settings_value[1] ) ) {
													echo 'readonly';
												}
												?>
											/>
										</div>
									</li>
									<?php
									if ( ! empty( $wpssw_google_settings_value[0] ) && ! empty( $wpssw_google_settings_value[1] ) ) {
											$wpssw_token_value = $wpssw_google_settings_value[2];
										?>
									<li>
										<label><?php echo esc_html__( 'Client Token', 'wpssw' ); ?></label>
										<?php
										if ( empty( $wpssw_token_value ) && ! isset( $_GET['code'] ) ) {
											$wpssw_auth_url = self::$instance_api->getClient();
											self::wpssw_update_option( 'wpssw_share_enable', true );
											?>
											<div id="authbtn">
												<a href="<?php echo esc_url( $wpssw_auth_url ); ?>" id="authlink" target="_blank" ><div class="wpssw-button wpssw-button-secondary"><?php echo esc_html__( 'Click here to generate an Authentication Token', 'wpssw' ); ?></div></a>
											</div>
											<?php
										}
										$wpssw_code                  = '';
										$wpssw_google_settings_value = self::wpssw_option( 'wpssw_google_settings' );
										?>
										<div  id="authtext" 
										<?php
										if ( ! empty( $wpssw_token_value ) || $wpssw_code ) {
											echo 'class = "forminp forminp-text wpssw-authtext" ';
										} else {
											echo 'class="forminp forminp-text"'; }
										?>
											><input type="text" name="client_token" value="<?php echo $wpssw_token_value ? esc_attr( $wpssw_token_value ) : esc_attr( $wpssw_code ); ?>" size="80" placeholder="Please enter authentication code" id="client_token" class="googlesettinginput" 
												<?php
												if ( ! empty( $wpssw_google_settings_value[2] ) ) {
													echo 'readonly';
												}
												?>
											/>
										</div>
									</li>
									<?php } if ( ! empty( $wpssw_token_value ) ) { ?>
									<li>
										<label></label>
										<div><input type="submit" name="revoke" id="revoke" value = "Revoke Token" class="wpssw-button wpssw-button-secondary"/></div>
									</li>
									<?php } ?>
								</ul>
							</div>
							<?php
							$site_url = isset( $_SERVER['SERVER_NAME'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_NAME'] ) ) : '';
							$site_url = str_replace( 'www.', '', $site_url );
							?>
						<div class="submit-section">
							<p class="submit">
								<input type="submit" name="submit" id="submit" class="wpssw-button wpssw-button-primary" value="Save">
								<?php
								if ( ! empty( $wpssw_token_value ) || ! empty( $wpssw_google_settings_value[0] ) || ! empty( $wpssw_google_settings_value[1] ) ) {
									?>
										<input type="submit" name="reset_settings" id="reset_settings" value = "Reset" class="wpssw-button wpssw-button-primary reset_settings"/>
									<?php } ?>
							</p>
						</div>
						</div>
						</div>
							<div class="generalSetting-section copy-url-table">
								<ul>
									<li>
										<label><?php echo esc_html__( 'Authorized Domain : ', 'wpssw' ); ?></label>
										<div class="copy-url-text"><span id="authorized_domain"><?php echo esc_html( $site_url ); ?></span>
											<span class="copy-icon wpssw-button" id="a_domain" onclick="wpsswCopy('authorized_domain','a_domain');">
											<svg width="15" height="18" viewBox="0 0 15 18" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M2.16667 17.3334C1.70833 17.3334 1.31597 17.1702 0.989583 16.8438C0.663194 16.5174 0.5 16.1251 0.5 15.6667V4.00008H2.16667V15.6667H11.3333V17.3334H2.16667ZM5.5 14.0001C5.04167 14.0001 4.64931 13.8369 4.32292 13.5105C3.99653 13.1841 3.83333 12.7917 3.83333 12.3334V2.33341C3.83333 1.87508 3.99653 1.48272 4.32292 1.15633C4.64931 0.829942 5.04167 0.666748 5.5 0.666748H13C13.4583 0.666748 13.8507 0.829942 14.1771 1.15633C14.5035 1.48272 14.6667 1.87508 14.6667 2.33341V12.3334C14.6667 12.7917 14.5035 13.1841 14.1771 13.5105C13.8507 13.8369 13.4583 14.0001 13 14.0001H5.5ZM5.5 12.3334H13V2.33341H5.5V12.3334Z" fill="#383E46"/>
</svg>
<span class="tooltip-text">Copied</span>
										</span>
										</div>
									</li>
									<li>
										<label><?php echo esc_html__( 'Authorised redirect URIs : ', 'wpssw' ); ?></label>
										<div class="copy-url-text">
											<span id="authorized_uri"><?php echo esc_html( admin_url( 'admin.php?page=wpsyncsheets-for-woocommerce' ) ); ?></span>
											<span class="copy-icon wpssw-button tooltip-click1" onclick="wpsswCopy('authorized_uri','a_uri');" id="a_uri">
												<svg width="15" height="18" viewBox="0 0 15 18" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M2.16667 17.3334C1.70833 17.3334 1.31597 17.1702 0.989583 16.8438C0.663194 16.5174 0.5 16.1251 0.5 15.6667V4.00008H2.16667V15.6667H11.3333V17.3334H2.16667ZM5.5 14.0001C5.04167 14.0001 4.64931 13.8369 4.32292 13.5105C3.99653 13.1841 3.83333 12.7917 3.83333 12.3334V2.33341C3.83333 1.87508 3.99653 1.48272 4.32292 1.15633C4.64931 0.829942 5.04167 0.666748 5.5 0.666748H13C13.4583 0.666748 13.8507 0.829942 14.1771 1.15633C14.5035 1.48272 14.6667 1.87508 14.6667 2.33341V12.3334C14.6667 12.7917 14.5035 13.1841 14.1771 13.5105C13.8507 13.8369 13.4583 14.0001 13 14.0001H5.5ZM5.5 12.3334H13V2.33341H5.5V12.3334Z" fill="#383E46"/>
</svg>
<span class="tooltip-text">Copied</span>
											</span>
										</div>
									</li>
								</ul>
							</div>
						</div>
					</form>
				</div>
			</div>
			<?php if ( true === (bool) $show_settings ) { ?>
			<div id="wpssw-nav-settings" class="navtabcontent wpssw-nav-settings-content" style="display:block;">
				<div class="vertical-tabs">
					<div class="tab">
				<button class="tablinks order-settings" onclick="wpsswTab(event, 'order-settings')" 
				<?php
				if ( ! empty( $wpssw_error ) || ! empty( $disbledbtn ) ) {
					echo 'disabled="disabled"'; }
				?>
				> <span class="tabicon">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
<mask id="mask0_428_2610" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="0" y="0" width="24" height="24">
<rect width="24" height="24" fill="#D9D9D9"/>
</mask>
<g mask="url(#mask0_428_2610)">
<path d="M5.00033 21C4.717 21 4.45866 20.9125 4.22533 20.7375C3.992 20.5625 3.83366 20.3333 3.75033 20.05L0.95033 9.95C0.866997 9.71667 0.904497 9.5 1.06283 9.3C1.22116 9.1 1.43366 9 1.70033 9H6.75033L11.1503 2.45C11.2337 2.31667 11.3503 2.20833 11.5003 2.125C11.6503 2.04167 11.8087 2 11.9753 2C12.142 2 12.3003 2.04167 12.4503 2.125C12.6003 2.20833 12.717 2.31667 12.8003 2.45L17.2003 9H22.3003C22.567 9 22.7795 9.1 22.9378 9.3C23.0962 9.5 23.1337 9.71667 23.0503 9.95L20.2503 20.05C20.167 20.3333 20.0087 20.5625 19.7753 20.7375C19.542 20.9125 19.2837 21 19.0003 21H5.00033ZM5.50033 19H18.5003L20.7003 11H3.30033L5.50033 19ZM12.0003 17C12.5503 17 13.0212 16.8042 13.4128 16.4125C13.8045 16.0208 14.0003 15.55 14.0003 15C14.0003 14.45 13.8045 13.9792 13.4128 13.5875C13.0212 13.1958 12.5503 13 12.0003 13C11.4503 13 10.9795 13.1958 10.5878 13.5875C10.1962 13.9792 10.0003 14.45 10.0003 15C10.0003 15.55 10.1962 16.0208 10.5878 16.4125C10.9795 16.8042 11.4503 17 12.0003 17ZM9.17533 9H14.8003L11.9753 4.8L9.17533 9Z" fill="#1C1B1F"/>
</g>
</svg>

				</span>
				<?php echo esc_html__( 'Order', 'wpssw' ); ?> <br><?php echo esc_html__( 'Settings', 'wpssw' ); ?></button>
				<button class="tablinks product-settings" onclick="wpsswTab(event, 'product-settings')" 
				<?php
				if ( ! empty( $wpssw_error ) || ! empty( $disbledbtn ) ) {
					echo 'disabled="disabled"'; }
				?>
				> <span class="tabicon">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
<mask id="mask0_428_2622" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="0" y="0" width="24" height="24">
<rect width="24" height="24" fill="#D9D9D9"/>
</mask>
<g mask="url(#mask0_428_2622)">
<path d="M5 8V19H19V8H16V14.375C16 14.7583 15.8417 15.0458 15.525 15.2375C15.2083 15.4292 14.8833 15.4417 14.55 15.275L12 14L9.45 15.275C9.11667 15.4417 8.79167 15.4292 8.475 15.2375C8.15833 15.0458 8 14.7583 8 14.375V8H5ZM5 21C4.45 21 3.97917 20.8042 3.5875 20.4125C3.19583 20.0208 3 19.55 3 19V6.525C3 6.29167 3.0375 6.06667 3.1125 5.85C3.1875 5.63333 3.3 5.43333 3.45 5.25L4.7 3.725C4.88333 3.49167 5.1125 3.3125 5.3875 3.1875C5.6625 3.0625 5.95 3 6.25 3H17.75C18.05 3 18.3375 3.0625 18.6125 3.1875C18.8875 3.3125 19.1167 3.49167 19.3 3.725L20.55 5.25C20.7 5.43333 20.8125 5.63333 20.8875 5.85C20.9625 6.06667 21 6.29167 21 6.525V19C21 19.55 20.8042 20.0208 20.4125 20.4125C20.0208 20.8042 19.55 21 19 21H5ZM5.4 6H18.6L17.75 5H6.25L5.4 6ZM10 8V12.75L12 11.75L14 12.75V8H10Z" fill="#1C1B1F"/>
</g>
</svg>
				</span>
				<?php echo esc_html__( 'Product', 'wpssw' ); ?> <br><?php echo esc_html__( 'Settings', 'wpssw' ); ?></button>
				<button class="tablinks customer-settings" onclick="wpsswTab(event, 'customer-settings')" 
				<?php
				if ( ! empty( $wpssw_error ) || ! empty( $disbledbtn ) ) {
					echo 'disabled="disabled"'; }
				?>
				> <span class="tabicon">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
<mask id="mask0_423_2547" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="0" y="0" width="24" height="24">
<rect width="24" height="24" fill="#D9D9D9"/>
</mask>
<g mask="url(#mask0_423_2547)">
<path d="M10 12C8.9 12 7.95833 11.6083 7.175 10.825C6.39167 10.0417 6 9.1 6 8C6 6.9 6.39167 5.95833 7.175 5.175C7.95833 4.39167 8.9 4 10 4C11.1 4 12.0417 4.39167 12.825 5.175C13.6083 5.95833 14 6.9 14 8C14 9.1 13.6083 10.0417 12.825 10.825C12.0417 11.6083 11.1 12 10 12ZM3 20C2.71667 20 2.47917 19.9042 2.2875 19.7125C2.09583 19.5208 2 19.2833 2 19V17.2C2 16.65 2.14167 16.1333 2.425 15.65C2.70833 15.1667 3.1 14.8 3.6 14.55C4.45 14.1167 5.40833 13.75 6.475 13.45C7.54167 13.15 8.71667 13 10 13H10.35C10.45 13 10.55 13.0167 10.65 13.05C10.5167 13.35 10.4042 13.6625 10.3125 13.9875C10.2208 14.3125 10.15 14.65 10.1 15H10C8.81667 15 7.75417 15.15 6.8125 15.45C5.87083 15.75 5.1 16.05 4.5 16.35C4.35 16.4333 4.22917 16.55 4.1375 16.7C4.04583 16.85 4 17.0167 4 17.2V18H10.3C10.4 18.35 10.5333 18.6958 10.7 19.0375C10.8667 19.3792 11.05 19.7 11.25 20H3ZM10 10C10.55 10 11.0208 9.80417 11.4125 9.4125C11.8042 9.02083 12 8.55 12 8C12 7.45 11.8042 6.97917 11.4125 6.5875C11.0208 6.19583 10.55 6 10 6C9.45 6 8.97917 6.19583 8.5875 6.5875C8.19583 6.97917 8 7.45 8 8C8 8.55 8.19583 9.02083 8.5875 9.4125C8.97917 9.80417 9.45 10 10 10ZM17 18C17.55 18 18.0208 17.8042 18.4125 17.4125C18.8042 17.0208 19 16.55 19 16C19 15.45 18.8042 14.9792 18.4125 14.5875C18.0208 14.1958 17.55 14 17 14C16.45 14 15.9792 14.1958 15.5875 14.5875C15.1958 14.9792 15 15.45 15 16C15 16.55 15.1958 17.0208 15.5875 17.4125C15.9792 17.8042 16.45 18 17 18ZM15.7 19.5C15.5 19.4167 15.3125 19.3292 15.1375 19.2375C14.9625 19.1458 14.7833 19.0333 14.6 18.9L13.525 19.225C13.4083 19.2583 13.3 19.2542 13.2 19.2125C13.1 19.1708 13.0167 19.1 12.95 19L12.35 18C12.2833 17.9 12.2625 17.7917 12.2875 17.675C12.3125 17.5583 12.375 17.4583 12.475 17.375L13.3 16.65C13.2667 16.4167 13.25 16.2 13.25 16C13.25 15.8 13.2667 15.5833 13.3 15.35L12.475 14.625C12.375 14.5417 12.3125 14.4417 12.2875 14.325C12.2625 14.2083 12.2833 14.1 12.35 14L12.95 13C13.0167 12.9 13.1 12.8292 13.2 12.7875C13.3 12.7458 13.4083 12.7417 13.525 12.775L14.6 13.1C14.7833 12.9667 14.9625 12.8542 15.1375 12.7625C15.3125 12.6708 15.5 12.5833 15.7 12.5L15.925 11.4C15.9583 11.2833 16.0167 11.1875 16.1 11.1125C16.1833 11.0375 16.2833 11 16.4 11H17.6C17.7167 11 17.8167 11.0375 17.9 11.1125C17.9833 11.1875 18.0417 11.2833 18.075 11.4L18.3 12.5C18.5 12.5833 18.6875 12.675 18.8625 12.775C19.0375 12.875 19.2167 13 19.4 13.15L20.45 12.775C20.5667 12.725 20.6792 12.725 20.7875 12.775C20.8958 12.825 20.9833 12.9 21.05 13L21.65 14.05C21.7167 14.15 21.7417 14.2583 21.725 14.375C21.7083 14.4917 21.65 14.5917 21.55 14.675L20.7 15.4C20.7333 15.6 20.75 15.8083 20.75 16.025C20.75 16.2417 20.7333 16.45 20.7 16.65L21.525 17.375C21.625 17.4583 21.6875 17.5583 21.7125 17.675C21.7375 17.7917 21.7167 17.9 21.65 18L21.05 19C20.9833 19.1 20.9 19.1708 20.8 19.2125C20.7 19.2542 20.5917 19.2583 20.475 19.225L19.4 18.9C19.2167 19.0333 19.0375 19.1458 18.8625 19.2375C18.6875 19.3292 18.5 19.4167 18.3 19.5L18.075 20.6C18.0417 20.7167 17.9833 20.8125 17.9 20.8875C17.8167 20.9625 17.7167 21 17.6 21H16.4C16.2833 21 16.1833 20.9625 16.1 20.8875C16.0167 20.8125 15.9583 20.7167 15.925 20.6L15.7 19.5Z" fill="#222222"/>
</g>
</svg>
				</span>
				<?php echo esc_html__( 'Customer', 'wpssw' ); ?> <br><?php echo esc_html__( 'Settings', 'wpssw' ); ?></button>
				<button class="tablinks coupon-settings" onclick="wpsswTab(event, 'coupon-settings')" 
				<?php
				if ( ! empty( $wpssw_error ) || ! empty( $disbledbtn ) ) {
					echo 'disabled="disabled"'; }
				?>
				> <span class="tabicon">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
<mask id="mask0_423_2559" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="0" y="0" width="24" height="24">
<rect width="24" height="24" fill="#D9D9D9"/>
</mask>
<g mask="url(#mask0_423_2559)">
<path d="M12 13.9L13.5 15.05C13.6833 15.2 13.8833 15.2042 14.1 15.0625C14.3167 14.9208 14.3833 14.7333 14.3 14.5L13.7 12.6L15.375 11.3C15.5583 11.15 15.6083 10.9625 15.525 10.7375C15.4417 10.5125 15.2833 10.4 15.05 10.4H13.1L12.475 8.475C12.3917 8.24167 12.2333 8.125 12 8.125C11.7667 8.125 11.6083 8.24167 11.525 8.475L10.9 10.4H8.925C8.69167 10.4 8.5375 10.5125 8.4625 10.7375C8.3875 10.9625 8.44167 11.15 8.625 11.3L10.25 12.6L9.65 14.525C9.56667 14.7583 9.625 14.9458 9.825 15.0875C10.025 15.2292 10.225 15.225 10.425 15.075L12 13.9ZM4 20C3.45 20 2.97917 19.8042 2.5875 19.4125C2.19583 19.0208 2 18.55 2 18V14.625C2 14.4417 2.05833 14.2833 2.175 14.15C2.29167 14.0167 2.44167 13.9333 2.625 13.9C3.025 13.7667 3.35417 13.525 3.6125 13.175C3.87083 12.825 4 12.4333 4 12C4 11.5667 3.87083 11.175 3.6125 10.825C3.35417 10.475 3.025 10.2333 2.625 10.1C2.44167 10.0667 2.29167 9.98333 2.175 9.85C2.05833 9.71667 2 9.55833 2 9.375V6C2 5.45 2.19583 4.97917 2.5875 4.5875C2.97917 4.19583 3.45 4 4 4H20C20.55 4 21.0208 4.19583 21.4125 4.5875C21.8042 4.97917 22 5.45 22 6V9.375C22 9.55833 21.9417 9.71667 21.825 9.85C21.7083 9.98333 21.5583 10.0667 21.375 10.1C20.975 10.2333 20.6458 10.475 20.3875 10.825C20.1292 11.175 20 11.5667 20 12C20 12.4333 20.1292 12.825 20.3875 13.175C20.6458 13.525 20.975 13.7667 21.375 13.9C21.5583 13.9333 21.7083 14.0167 21.825 14.15C21.9417 14.2833 22 14.4417 22 14.625V18C22 18.55 21.8042 19.0208 21.4125 19.4125C21.0208 19.8042 20.55 20 20 20H4ZM4 18H20V15.45C19.3833 15.0833 18.8958 14.5958 18.5375 13.9875C18.1792 13.3792 18 12.7167 18 12C18 11.2833 18.1792 10.6208 18.5375 10.0125C18.8958 9.40417 19.3833 8.91667 20 8.55V6H4V8.55C4.61667 8.91667 5.10417 9.40417 5.4625 10.0125C5.82083 10.6208 6 11.2833 6 12C6 12.7167 5.82083 13.3792 5.4625 13.9875C5.10417 14.5958 4.61667 15.0833 4 15.45V18Z" fill="#222222"/>
</g>
</svg>
				</span> 
				<?php echo esc_html__( 'Coupon', 'wpssw' ); ?> <br><?php echo esc_html__( 'Settings', 'wpssw' ); ?></button>
				<?php if ( self::wpssw_is_event_calender_ticket_active() ) { ?>
					<button class="tablinks event-settings" onclick="wpsswTab(event, 'event-settings')" 
					<?php
					if ( ! empty( $wpssw_error ) || ! empty( $disbledbtn ) ) {
						echo 'disabled="disabled"'; }
					?>
					> <span class="tabicon">
						<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
<mask id="mask0_423_2471" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="0" y="0" width="24" height="24">
<rect width="24" height="24" fill="#D9D9D9"/>
</mask>
<g mask="url(#mask0_423_2471)">
<path d="M13.8753 22H10.1253C9.87534 22 9.65867 21.9167 9.47534 21.75C9.292 21.5833 9.18367 21.375 9.15034 21.125L8.85034 18.8C8.63367 18.7167 8.4295 18.6167 8.23784 18.5C8.04617 18.3833 7.85867 18.2583 7.67534 18.125L5.50034 19.025C5.267 19.1083 5.03367 19.1167 4.80034 19.05C4.567 18.9833 4.38367 18.8417 4.25034 18.625L2.40034 15.4C2.267 15.1833 2.22534 14.95 2.27534 14.7C2.32534 14.45 2.45034 14.25 2.65034 14.1L4.52534 12.675C4.50867 12.5583 4.50034 12.4458 4.50034 12.3375V11.6625C4.50034 11.5542 4.50867 11.4417 4.52534 11.325L2.65034 9.9C2.45034 9.75 2.32534 9.55 2.27534 9.3C2.22534 9.05 2.267 8.81667 2.40034 8.6L4.25034 5.375C4.367 5.14167 4.54617 4.99583 4.78784 4.9375C5.0295 4.87917 5.267 4.89167 5.50034 4.975L7.67534 5.875C7.85867 5.74167 8.05034 5.61667 8.25034 5.5C8.45034 5.38333 8.65034 5.28333 8.85034 5.2L9.15034 2.875C9.18367 2.625 9.292 2.41667 9.47534 2.25C9.65867 2.08333 9.87534 2 10.1253 2H13.8753C14.1253 2 14.342 2.08333 14.5253 2.25C14.7087 2.41667 14.817 2.625 14.8503 2.875L15.1503 5.2C15.367 5.28333 15.5712 5.38333 15.7628 5.5C15.9545 5.61667 16.142 5.74167 16.3253 5.875L18.5003 4.975C18.7337 4.89167 18.967 4.88333 19.2003 4.95C19.4337 5.01667 19.617 5.15833 19.7503 5.375L21.6003 8.6C21.7337 8.81667 21.7753 9.05 21.7253 9.3C21.6753 9.55 21.5503 9.75 21.3503 9.9L19.4753 11.325C19.492 11.4417 19.5003 11.5542 19.5003 11.6625V12.3375C19.5003 12.4458 19.4837 12.5583 19.4503 12.675L21.3253 14.1C21.5253 14.25 21.6503 14.45 21.7003 14.7C21.7503 14.95 21.7087 15.1833 21.5753 15.4L19.7253 18.6C19.592 18.8167 19.4045 18.9625 19.1628 19.0375C18.9212 19.1125 18.6837 19.1083 18.4503 19.025L16.3253 18.125C16.142 18.2583 15.9503 18.3833 15.7503 18.5C15.5503 18.6167 15.3503 18.7167 15.1503 18.8L14.8503 21.125C14.817 21.375 14.7087 21.5833 14.5253 21.75C14.342 21.9167 14.1253 22 13.8753 22ZM12.0503 15.5C13.017 15.5 13.842 15.1583 14.5253 14.475C15.2087 13.7917 15.5503 12.9667 15.5503 12C15.5503 11.0333 15.2087 10.2083 14.5253 9.525C13.842 8.84167 13.017 8.5 12.0503 8.5C11.067 8.5 10.2378 8.84167 9.56284 9.525C8.88784 10.2083 8.55034 11.0333 8.55034 12C8.55034 12.9667 8.88784 13.7917 9.56284 14.475C10.2378 15.1583 11.067 15.5 12.0503 15.5ZM12.0503 13.5C11.6337 13.5 11.2795 13.3542 10.9878 13.0625C10.6962 12.7708 10.5503 12.4167 10.5503 12C10.5503 11.5833 10.6962 11.2292 10.9878 10.9375C11.2795 10.6458 11.6337 10.5 12.0503 10.5C12.467 10.5 12.8212 10.6458 13.1128 10.9375C13.4045 11.2292 13.5503 11.5833 13.5503 12C13.5503 12.4167 13.4045 12.7708 13.1128 13.0625C12.8212 13.3542 12.467 13.5 12.0503 13.5ZM11.0003 20H12.9753L13.3253 17.35C13.842 17.2167 14.3212 17.0208 14.7628 16.7625C15.2045 16.5042 15.6087 16.1917 15.9753 15.825L18.4503 16.85L19.4253 15.15L17.2753 13.525C17.3587 13.2917 17.417 13.0458 17.4503 12.7875C17.4837 12.5292 17.5003 12.2667 17.5003 12C17.5003 11.7333 17.4837 11.4708 17.4503 11.2125C17.417 10.9542 17.3587 10.7083 17.2753 10.475L19.4253 8.85L18.4503 7.15L15.9753 8.2C15.6087 7.81667 15.2045 7.49583 14.7628 7.2375C14.3212 6.97917 13.842 6.78333 13.3253 6.65L13.0003 4H11.0253L10.6753 6.65C10.1587 6.78333 9.6795 6.97917 9.23784 7.2375C8.79617 7.49583 8.392 7.80833 8.02534 8.175L5.55034 7.15L4.57534 8.85L6.72534 10.45C6.642 10.7 6.58367 10.95 6.55034 11.2C6.517 11.45 6.50034 11.7167 6.50034 12C6.50034 12.2667 6.517 12.525 6.55034 12.775C6.58367 13.025 6.642 13.275 6.72534 13.525L4.57534 15.15L5.55034 16.85L8.02534 15.8C8.392 16.1833 8.79617 16.5042 9.23784 16.7625C9.6795 17.0208 10.1587 17.2167 10.6753 17.35L11.0003 20Z" fill="#222222"/>
</g>
</svg>

					</span> 

					<?php echo esc_html__( 'Event', 'wpssw' ); ?> <br><?php echo esc_html__( 'Settings', 'wpssw' ); ?></button>
				<?php } ?>
				<button class="tablinks export" onclick="wpsswTab(event, 'export')" 
				<?php
				if ( ! empty( $wpssw_error ) || ! empty( $disbledbtn ) ) {
					echo 'disabled="disabled"'; }
				?>
				> <span class="tabicon">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
<mask id="mask0_423_2565" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="0" y="0" width="24" height="24">
<rect width="24" height="24" fill="#D9D9D9"/>
</mask>
<g mask="url(#mask0_423_2565)">
<path d="M8 18C7.45 18 6.97917 17.8042 6.5875 17.4125C6.19583 17.0208 6 16.55 6 16V15C6 14.7167 6.09583 14.4792 6.2875 14.2875C6.47917 14.0958 6.71667 14 7 14C7.28333 14 7.52083 14.0958 7.7125 14.2875C7.90417 14.4792 8 14.7167 8 15V16H20V6H8V7C8 7.28333 7.90417 7.52083 7.7125 7.7125C7.52083 7.90417 7.28333 8 7 8C6.71667 8 6.47917 7.90417 6.2875 7.7125C6.09583 7.52083 6 7.28333 6 7V4C6 3.45 6.19583 2.97917 6.5875 2.5875C6.97917 2.19583 7.45 2 8 2H20C20.55 2 21.0208 2.19583 21.4125 2.5875C21.8042 2.97917 22 3.45 22 4V16C22 16.55 21.8042 17.0208 21.4125 17.4125C21.0208 17.8042 20.55 18 20 18H8ZM4 22C3.45 22 2.97917 21.8042 2.5875 21.4125C2.19583 21.0208 2 20.55 2 20V7C2 6.71667 2.09583 6.47917 2.2875 6.2875C2.47917 6.09583 2.71667 6 3 6C3.28333 6 3.52083 6.09583 3.7125 6.2875C3.90417 6.47917 4 6.71667 4 7V20H17C17.2833 20 17.5208 20.0958 17.7125 20.2875C17.9042 20.4792 18 20.7167 18 21C18 21.2833 17.9042 21.5208 17.7125 21.7125C17.5208 21.9042 17.2833 22 17 22H4ZM13.175 12H7C6.71667 12 6.47917 11.9042 6.2875 11.7125C6.09583 11.5208 6 11.2833 6 11C6 10.7167 6.09583 10.4792 6.2875 10.2875C6.47917 10.0958 6.71667 10 7 10H13.175L12.275 9.1C12.0917 8.91667 12 8.6875 12 8.4125C12 8.1375 12.1 7.9 12.3 7.7C12.4833 7.51667 12.7167 7.425 13 7.425C13.2833 7.425 13.5167 7.51667 13.7 7.7L16.3 10.3C16.5 10.5 16.6 10.7333 16.6 11C16.6 11.2667 16.5 11.5 16.3 11.7L13.7 14.3C13.5167 14.4833 13.2875 14.5792 13.0125 14.5875C12.7375 14.5958 12.5 14.5 12.3 14.3C12.1167 14.1167 12.025 13.8833 12.025 13.6C12.025 13.3167 12.1167 13.0833 12.3 12.9L13.175 12Z" fill="#222222"/>
</g>
</svg>

				</span> 
				<?php echo esc_html__( 'Export Orders', 'wpssw' ); ?></button>
				<?php
					$wpssw_tabs = apply_filters( 'wpsyncsheets_settings_tabs', array() );
				if ( is_array( $wpssw_tabs ) && ! empty( $wpssw_tabs ) ) {
					foreach ( $wpssw_tabs as $tabkey => $tabname ) {
						?>
							<button class="tablinks <?php echo esc_html( $tabkey ); ?>" onclick="wpsswTab(event, '<?php echo esc_html( $tabkey ); ?>')" 
							<?php
							if ( ! empty( $wpssw_error ) || ! empty( $disbledbtn ) ) {
								echo 'disabled="disabled"'; }
							?>
							> <span class="tabicon">
								<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
<mask id="mask0_423_2471" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="0" y="0" width="24" height="24">
<rect width="24" height="24" fill="#D9D9D9"/>
</mask>
<g mask="url(#mask0_423_2471)">
<path d="M13.8753 22H10.1253C9.87534 22 9.65867 21.9167 9.47534 21.75C9.292 21.5833 9.18367 21.375 9.15034 21.125L8.85034 18.8C8.63367 18.7167 8.4295 18.6167 8.23784 18.5C8.04617 18.3833 7.85867 18.2583 7.67534 18.125L5.50034 19.025C5.267 19.1083 5.03367 19.1167 4.80034 19.05C4.567 18.9833 4.38367 18.8417 4.25034 18.625L2.40034 15.4C2.267 15.1833 2.22534 14.95 2.27534 14.7C2.32534 14.45 2.45034 14.25 2.65034 14.1L4.52534 12.675C4.50867 12.5583 4.50034 12.4458 4.50034 12.3375V11.6625C4.50034 11.5542 4.50867 11.4417 4.52534 11.325L2.65034 9.9C2.45034 9.75 2.32534 9.55 2.27534 9.3C2.22534 9.05 2.267 8.81667 2.40034 8.6L4.25034 5.375C4.367 5.14167 4.54617 4.99583 4.78784 4.9375C5.0295 4.87917 5.267 4.89167 5.50034 4.975L7.67534 5.875C7.85867 5.74167 8.05034 5.61667 8.25034 5.5C8.45034 5.38333 8.65034 5.28333 8.85034 5.2L9.15034 2.875C9.18367 2.625 9.292 2.41667 9.47534 2.25C9.65867 2.08333 9.87534 2 10.1253 2H13.8753C14.1253 2 14.342 2.08333 14.5253 2.25C14.7087 2.41667 14.817 2.625 14.8503 2.875L15.1503 5.2C15.367 5.28333 15.5712 5.38333 15.7628 5.5C15.9545 5.61667 16.142 5.74167 16.3253 5.875L18.5003 4.975C18.7337 4.89167 18.967 4.88333 19.2003 4.95C19.4337 5.01667 19.617 5.15833 19.7503 5.375L21.6003 8.6C21.7337 8.81667 21.7753 9.05 21.7253 9.3C21.6753 9.55 21.5503 9.75 21.3503 9.9L19.4753 11.325C19.492 11.4417 19.5003 11.5542 19.5003 11.6625V12.3375C19.5003 12.4458 19.4837 12.5583 19.4503 12.675L21.3253 14.1C21.5253 14.25 21.6503 14.45 21.7003 14.7C21.7503 14.95 21.7087 15.1833 21.5753 15.4L19.7253 18.6C19.592 18.8167 19.4045 18.9625 19.1628 19.0375C18.9212 19.1125 18.6837 19.1083 18.4503 19.025L16.3253 18.125C16.142 18.2583 15.9503 18.3833 15.7503 18.5C15.5503 18.6167 15.3503 18.7167 15.1503 18.8L14.8503 21.125C14.817 21.375 14.7087 21.5833 14.5253 21.75C14.342 21.9167 14.1253 22 13.8753 22ZM12.0503 15.5C13.017 15.5 13.842 15.1583 14.5253 14.475C15.2087 13.7917 15.5503 12.9667 15.5503 12C15.5503 11.0333 15.2087 10.2083 14.5253 9.525C13.842 8.84167 13.017 8.5 12.0503 8.5C11.067 8.5 10.2378 8.84167 9.56284 9.525C8.88784 10.2083 8.55034 11.0333 8.55034 12C8.55034 12.9667 8.88784 13.7917 9.56284 14.475C10.2378 15.1583 11.067 15.5 12.0503 15.5ZM12.0503 13.5C11.6337 13.5 11.2795 13.3542 10.9878 13.0625C10.6962 12.7708 10.5503 12.4167 10.5503 12C10.5503 11.5833 10.6962 11.2292 10.9878 10.9375C11.2795 10.6458 11.6337 10.5 12.0503 10.5C12.467 10.5 12.8212 10.6458 13.1128 10.9375C13.4045 11.2292 13.5503 11.5833 13.5503 12C13.5503 12.4167 13.4045 12.7708 13.1128 13.0625C12.8212 13.3542 12.467 13.5 12.0503 13.5ZM11.0003 20H12.9753L13.3253 17.35C13.842 17.2167 14.3212 17.0208 14.7628 16.7625C15.2045 16.5042 15.6087 16.1917 15.9753 15.825L18.4503 16.85L19.4253 15.15L17.2753 13.525C17.3587 13.2917 17.417 13.0458 17.4503 12.7875C17.4837 12.5292 17.5003 12.2667 17.5003 12C17.5003 11.7333 17.4837 11.4708 17.4503 11.2125C17.417 10.9542 17.3587 10.7083 17.2753 10.475L19.4253 8.85L18.4503 7.15L15.9753 8.2C15.6087 7.81667 15.2045 7.49583 14.7628 7.2375C14.3212 6.97917 13.842 6.78333 13.3253 6.65L13.0003 4H11.0253L10.6753 6.65C10.1587 6.78333 9.6795 6.97917 9.23784 7.2375C8.79617 7.49583 8.392 7.80833 8.02534 8.175L5.55034 7.15L4.57534 8.85L6.72534 10.45C6.642 10.7 6.58367 10.95 6.55034 11.2C6.517 11.45 6.50034 11.7167 6.50034 12C6.50034 12.2667 6.517 12.525 6.55034 12.775C6.58367 13.025 6.642 13.275 6.72534 13.525L4.57534 15.15L5.55034 16.85L8.02534 15.8C8.392 16.1833 8.79617 16.5042 9.23784 16.7625C9.6795 17.0208 10.1587 17.2167 10.6753 17.35L11.0003 20Z" fill="#222222"/>
</g>
</svg>

							</span> 
							<?php echo esc_html( $tabname ); ?></button>
							<?php
					}
				}
				?>
				<button class="tablinks general-settings" onclick="wpsswTab(event, 'general-settings')" 
				<?php
				if ( ! empty( $wpssw_error ) || ! empty( $disbledbtn ) ) {
					echo 'disabled="disabled"'; }
				?>
				> <span class="tabicon">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
<mask id="mask0_423_2471" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="0" y="0" width="24" height="24">
<rect width="24" height="24" fill="#D9D9D9"/>
</mask>
<g mask="url(#mask0_423_2471)">
<path d="M13.8753 22H10.1253C9.87534 22 9.65867 21.9167 9.47534 21.75C9.292 21.5833 9.18367 21.375 9.15034 21.125L8.85034 18.8C8.63367 18.7167 8.4295 18.6167 8.23784 18.5C8.04617 18.3833 7.85867 18.2583 7.67534 18.125L5.50034 19.025C5.267 19.1083 5.03367 19.1167 4.80034 19.05C4.567 18.9833 4.38367 18.8417 4.25034 18.625L2.40034 15.4C2.267 15.1833 2.22534 14.95 2.27534 14.7C2.32534 14.45 2.45034 14.25 2.65034 14.1L4.52534 12.675C4.50867 12.5583 4.50034 12.4458 4.50034 12.3375V11.6625C4.50034 11.5542 4.50867 11.4417 4.52534 11.325L2.65034 9.9C2.45034 9.75 2.32534 9.55 2.27534 9.3C2.22534 9.05 2.267 8.81667 2.40034 8.6L4.25034 5.375C4.367 5.14167 4.54617 4.99583 4.78784 4.9375C5.0295 4.87917 5.267 4.89167 5.50034 4.975L7.67534 5.875C7.85867 5.74167 8.05034 5.61667 8.25034 5.5C8.45034 5.38333 8.65034 5.28333 8.85034 5.2L9.15034 2.875C9.18367 2.625 9.292 2.41667 9.47534 2.25C9.65867 2.08333 9.87534 2 10.1253 2H13.8753C14.1253 2 14.342 2.08333 14.5253 2.25C14.7087 2.41667 14.817 2.625 14.8503 2.875L15.1503 5.2C15.367 5.28333 15.5712 5.38333 15.7628 5.5C15.9545 5.61667 16.142 5.74167 16.3253 5.875L18.5003 4.975C18.7337 4.89167 18.967 4.88333 19.2003 4.95C19.4337 5.01667 19.617 5.15833 19.7503 5.375L21.6003 8.6C21.7337 8.81667 21.7753 9.05 21.7253 9.3C21.6753 9.55 21.5503 9.75 21.3503 9.9L19.4753 11.325C19.492 11.4417 19.5003 11.5542 19.5003 11.6625V12.3375C19.5003 12.4458 19.4837 12.5583 19.4503 12.675L21.3253 14.1C21.5253 14.25 21.6503 14.45 21.7003 14.7C21.7503 14.95 21.7087 15.1833 21.5753 15.4L19.7253 18.6C19.592 18.8167 19.4045 18.9625 19.1628 19.0375C18.9212 19.1125 18.6837 19.1083 18.4503 19.025L16.3253 18.125C16.142 18.2583 15.9503 18.3833 15.7503 18.5C15.5503 18.6167 15.3503 18.7167 15.1503 18.8L14.8503 21.125C14.817 21.375 14.7087 21.5833 14.5253 21.75C14.342 21.9167 14.1253 22 13.8753 22ZM12.0503 15.5C13.017 15.5 13.842 15.1583 14.5253 14.475C15.2087 13.7917 15.5503 12.9667 15.5503 12C15.5503 11.0333 15.2087 10.2083 14.5253 9.525C13.842 8.84167 13.017 8.5 12.0503 8.5C11.067 8.5 10.2378 8.84167 9.56284 9.525C8.88784 10.2083 8.55034 11.0333 8.55034 12C8.55034 12.9667 8.88784 13.7917 9.56284 14.475C10.2378 15.1583 11.067 15.5 12.0503 15.5ZM12.0503 13.5C11.6337 13.5 11.2795 13.3542 10.9878 13.0625C10.6962 12.7708 10.5503 12.4167 10.5503 12C10.5503 11.5833 10.6962 11.2292 10.9878 10.9375C11.2795 10.6458 11.6337 10.5 12.0503 10.5C12.467 10.5 12.8212 10.6458 13.1128 10.9375C13.4045 11.2292 13.5503 11.5833 13.5503 12C13.5503 12.4167 13.4045 12.7708 13.1128 13.0625C12.8212 13.3542 12.467 13.5 12.0503 13.5ZM11.0003 20H12.9753L13.3253 17.35C13.842 17.2167 14.3212 17.0208 14.7628 16.7625C15.2045 16.5042 15.6087 16.1917 15.9753 15.825L18.4503 16.85L19.4253 15.15L17.2753 13.525C17.3587 13.2917 17.417 13.0458 17.4503 12.7875C17.4837 12.5292 17.5003 12.2667 17.5003 12C17.5003 11.7333 17.4837 11.4708 17.4503 11.2125C17.417 10.9542 17.3587 10.7083 17.2753 10.475L19.4253 8.85L18.4503 7.15L15.9753 8.2C15.6087 7.81667 15.2045 7.49583 14.7628 7.2375C14.3212 6.97917 13.842 6.78333 13.3253 6.65L13.0003 4H11.0253L10.6753 6.65C10.1587 6.78333 9.6795 6.97917 9.23784 7.2375C8.79617 7.49583 8.392 7.80833 8.02534 8.175L5.55034 7.15L4.57534 8.85L6.72534 10.45C6.642 10.7 6.58367 10.95 6.55034 11.2C6.517 11.45 6.50034 11.7167 6.50034 12C6.50034 12.2667 6.517 12.525 6.55034 12.775C6.58367 13.025 6.642 13.275 6.72534 13.525L4.57534 15.15L5.55034 16.85L8.02534 15.8C8.392 16.1833 8.79617 16.5042 9.23784 16.7625C9.6795 17.0208 10.1587 17.2167 10.6753 17.35L11.0003 20Z" fill="#222222"/>
</g>
</svg>

				</span>
				<?php echo esc_html__( 'General', 'wpssw' ); ?> <br><?php echo esc_html__( 'Settings', 'wpssw' ); ?></button>
				</div>
				<div id="order-settings" class="tabcontent active">
				<?php
				$wpssw_google_settings = self::wpssw_option( 'wpssw_google_settings' );
				if ( ! empty( $wpssw_google_settings[2] ) ) {
					?>
					<form method="post" action="<?php echo esc_html( admin_url( 'admin.php?page=wpsyncsheets-for-woocommerce&tab=order-settings' ) ); ?>" id="mainform">					
					<?php
					wp_nonce_field( 'save_general_settings', 'wpssw_general_settings' );
					if ( \WPSSW_Dependencies::wpssw_is_woocommerce_active() ) {
						if ( self::$instance_api->checkcredenatials() ) {
							woocommerce_admin_fields( WPSSW_Order::wpssw_get_settings() );
							?>
						<div class="submit-section">
							<p class="submit"><input type="submit" name="submit" id="submit" class="wpssw-button wpssw-button-primary" value="Save"></p>
						</div>
							<?php
						}
					} else {
						?>
						<div class="generalSetting-section generalSetting-section-message">
							<h4><?php echo 'WPSyncSheets For WooCommerce plugin requires <a href="http://wordpress.org/extend/plugins/woocommerce/">WooCommerce</a> plugin to be active!'; ?></h4>
						</div>
					<?php } ?>
					</form>
					<?php
				} else {
					?>
					<div class="generalSetting-section generalSetting-section-message">
						<h4><?php echo esc_html__( 'Please genearate authentication code from', 'wpssw' ); ?>
							<strong><?php echo esc_html__( 'Google API Setting', 'wpssw' ); ?></strong>
							<a href='<?php echo esc_url( 'admin.php?page=wpsyncsheets-for-woocommerce' ); ?>'> <?php echo esc_html__( 'Click', 'wpssw' ); ?></a>
						</h4>
					</div>
				<?php } ?>
				</div>
				<!-- Event settings block start -->
				<?php if ( self::wpssw_is_event_calender_ticket_active() ) { ?>
				<div id="event-settings" class="tabcontent">
					<?php
					$wpssw_event_spreadsheet_setting = self::wpssw_option( 'wpssw_event_spreadsheet_setting' );
					$wpssw_event_spreadsheet_id      = self::wpssw_option( 'wpssw_event_spreadsheet_id' );
					$wpssw_spreadsheet_id            = self::wpssw_option( 'wpssw_woocommerce_spreadsheet' );
					$wpssw_eventsheet_id             = $wpssw_event_spreadsheet_id;
					if ( empty( $wpssw_event_spreadsheet_id ) ) {
						$wpssw_event_spreadsheet_id = $wpssw_spreadsheet_id;
					}
					$wpssw_checked = '';
					if ( 'yes' === (string) $wpssw_event_spreadsheet_setting ) {
						$wpssw_checked = 'checked';
					}
					$spreadsheets_list = self::$instance_api->get_spreadsheet_listing();
					?>
					<?php
					if ( ! empty( $wpssw_google_settings[2] ) ) {
						if ( ! empty( $wpssw_event_spreadsheet_id ) && array_key_exists( $wpssw_event_spreadsheet_id, $spreadsheets_list ) ) {
							?>
				<form method="post" action="<?php echo esc_html( admin_url( 'admin.php?page=wpsyncsheets-for-woocommerce&tab=event-settings' ) ); ?>" id="eventform">
							<?php wp_nonce_field( 'save_event_settings', 'wpssw_event_settings' ); ?>
						<div valign="top" class="checkbox_margin generalSetting-section">
							<div scope="row" class="titledesc generalSetting-left">
								<h4><?php echo esc_html__( 'Event Settings', 'wpssw' ); ?></h4>
								<p><?php echo esc_html__( 'Enable this setting to configure event categories sheets within the same spreadsheet as we have assign into the order settings.', 'wpssw' ); ?></p>
							</div>
							<div class="forminp event-settings-checkbox generalSetting-right"> 
								<label for="event_settings_checkbox">
									<input name="event_settings_checkbox" id="event_settings_checkbox" type="checkbox" class="" value="1" <?php echo esc_attr( $wpssw_checked ); ?>><span class="checkbox-switch"></span><span class="checkbox-switch"></span> 							
								</label>
							</div>
						</div>
							<?php
							$wpssw_events_cat        = array();
							$wpssw_events_cat        = get_terms(
								array(
									'taxonomy'   => 'tribe_events_cat',
									'hide_empty' => 0,
								)
							);
							$wpssw_events_categories = array();
							if ( ! empty( $wpssw_events_cat ) ) {
								foreach ( $wpssw_events_cat as $cat ) {
									$wpssw_events_categories[] = $cat->name;
								}
							}
							?>
						<div class="wpssw-section-last generalSetting-section googleSpreadsheet-section event_spreadsheet_row">
							<div class="generalSetting-left">
								<h4><?php echo esc_html__( 'Events Categories', 'wpssw' ); ?></h4>
								<p><?php echo esc_html__( "Here's a list of the event categories. These selected sheets will be created in assigned Google Spreadsheets of order settings and will neatly organize orders according to their items event category whenever order updated or synced.", 'wpssw' ); ?></p>
								<?php
								if ( empty( $wpssw_events_categories ) ) {
									?>
									<p><?php echo esc_html__( "All the event sheet headers will be append within order setting's active sheets. If you want to categorize your events orders then create new category save event settings, it will automatically create sheet within your existing order's spreadsheet.", 'wpssw' ); ?>
								<?php } else { ?>
								<div class="default-order-sheet-box">
									<?php
									$i = 0;
									foreach ( $wpssw_events_categories as $category ) {
										$category_id            = strtolower( str_replace( ' ', '_', $category ) );
										$category_id            = 'event_category_' . $category_id;
										$wpssw_event_ischecked  = '';
										$wpssw_eventsheets_list = array();
										if ( is_array( self::wpssw_option( 'wpssw_eventsheets_list' ) ) ) {
											$wpssw_eventsheets_list = self::wpssw_option( 'wpssw_eventsheets_list' );
										}
										if ( in_array( $category, $wpssw_eventsheets_list, true ) ) {
											$wpssw_event_ischecked = 'checked';
										}
										?>

							<div class="default-order-sheet">
								<div class="orderSheet-left">
									<label for="<?php echo esc_attr( $category ); ?>">
										<?php echo esc_html( $category ); ?>
									</label>
								</div>
								<div class="orderSheet-right forminp forminp-checkbox inside-checkbox">
									<fieldset>
										<label for="<?php echo esc_attr( $category_id ); ?>">
										<input name="event_categories_sheet[]" type="checkbox" id="<?php echo esc_attr( $category_id ); ?>" value="<?php echo esc_attr( $category ); ?>" <?php echo esc_html( $wpssw_event_ischecked ); ?>>
										<span class="checkbox-switch-new"></span>
										</label>
									</fieldset>
								</div>
							</div>

									<?php $i++;} ?>
								</div>
							<?php } ?>
							</div>
						</div>
						<div class="generalSetting-section sheetHeaders-section event_spreadsheet_row">
							<div class="td-wpssw-headers">
								<div class='wpssw_headers'>
									<div class="generalSetting-sheet-headers-row">
										<div class="generalSetting-left">
											<h4><?php echo esc_html__( 'Sheet Headers', 'wpssw' ); ?></h4>
											<p><?php echo esc_html__( 'Any disabled sheet headers will be automatically removed from the order settings sheets and event categories sheets. You can re-enable the desired headers, save the settings and to update the spreadsheet with the most recent data, clear the existing spreadsheet, and then proceed to click the "Click to Sync" button.', 'wpssw' ); ?></p>
										</div>
									</div>
									<div class="selectall-btnset">
										<button type="button" class="wpssw-button wpssw-button-primary" id="woo-eventselectall"><?php echo esc_html__( 'Select All', 'wpssw' ); ?></button>                
										<button type="button" class="wpssw-button wpssw-button-secondary" id="woo-eventselectnone"><?php echo esc_html__( 'Select None', 'wpssw' ); ?></button>
									</div>
									<div class="sheetHeaders-main">
										<ul id="woo-event-sortable" class="sheetHeaders-box">
												<?php
												$wpssw_woo_is_checked = '';
												$wpssw_woo_selections = stripslashes_deep( self::wpssw_option( 'wpssw_woo_event_headers' ) );
												if ( ! $wpssw_woo_selections ) {
													$wpssw_woo_selections = array();
												}
												$wpssw_woo_selections_custom = stripslashes_deep( self::wpssw_option( 'wpssw_woo_event_headers_custom' ) );
												if ( ! $wpssw_woo_selections_custom ) {
													$wpssw_woo_selections_custom = array();
												}
												$wpssw_include = new WPSSW_Include_Action();
												$wpssw_include->wpssw_include_event_compatibility_files();
												$wpssw_wooevent_headers = apply_filters( 'wpsyncsheets_event_headers', array() );
												$wpssw_wooevent_headers = self::wpssw_array_flatten( $wpssw_wooevent_headers );
												if ( ! empty( $wpssw_woo_selections ) ) {
													foreach ( $wpssw_woo_selections as $wpssw_key => $wpssw_val ) {
														$wpssw_woo_is_checked = 'checked';
														$wpssw_labelid        = strtolower( str_replace( ' ', '_', $wpssw_val ) );
														?>
											<li class="default-order-sheet ui-state-default">
	<label for="woo-<?php echo esc_attr( $wpssw_labelid ); ?>">
												<span class="orderSheet-left">
													<span class="wootextfield"><?php echo isset( $wpssw_woo_selections_custom[ $wpssw_key ] ) ? esc_attr( $wpssw_woo_selections_custom[ $wpssw_key ] ) : esc_attr( $wpssw_val ); ?></span>
													<span class="ui-icon ui-icon-pencil wpssw-tooltio-link">
														<span class="pencil-icon">
															<svg width="12" height="12" viewBox="0 0 13 13" fill="none" xmlns="http://www.w3.org/2000/svg">
															<path d="M1.33333 11.6668H2.26667L8.01667 5.91677L7.08333 4.98343L1.33333 10.7334V11.6668ZM10.8667 4.9501L8.03333 2.1501L8.96667 1.21677C9.22222 0.961213 9.53611 0.833435 9.90833 0.833435C10.2806 0.833435 10.5944 0.961213 10.85 1.21677L11.7833 2.1501C12.0389 2.40566 12.1722 2.71399 12.1833 3.0751C12.1944 3.43621 12.0722 3.74455 11.8167 4.0001L10.8667 4.9501ZM9.9 5.93343L2.83333 13.0001H0V10.1668L7.06667 3.1001L9.9 5.93343Z" fill="#64748B"/>
															</svg>
															</span>
															<span class="check-icon">
																<svg xmlns="http://www.w3.org/2000/svg"  viewBox="0 0 26 26" width="12" height="12">
																	<path d="M 22.566406 4.730469 L 20.773438 3.511719 C 20.277344 3.175781 19.597656 3.304688 19.265625 3.796875 L 10.476563 16.757813 L 6.4375 12.71875 C 6.015625 12.296875 5.328125 12.296875 4.90625 12.71875 L 3.371094 14.253906 C 2.949219 14.675781 2.949219 15.363281 3.371094 15.789063 L 9.582031 22 C 9.929688 22.347656 10.476563 22.613281 10.96875 22.613281 C 11.460938 22.613281 11.957031 22.304688 12.277344 21.839844 L 22.855469 6.234375 C 23.191406 5.742188 23.0625 5.066406 22.566406 4.730469 Z" fill="#64748B"/></svg>
															</span>
															<span class="tooltip-text edit-tooltip">Edit</span>
														<span class="tooltip-text update-tooltip">Update</span>
													</span>
												</span>
												<span class="orderSheet-right">
													<input type="checkbox" name="wooevent_custom[]" value="<?php echo isset( $wpssw_woo_selections_custom[ $wpssw_key ] ) ? esc_attr( $wpssw_woo_selections_custom[ $wpssw_key ] ) : esc_attr( $wpssw_val ); ?>" class="woo-event-headers-chk1" <?php echo esc_attr( $wpssw_woo_is_checked ); ?> hidden="true">
													<input type="checkbox" name="wooevent_header_list[]" value="<?php echo esc_attr( $wpssw_val ); ?>" id="woo-<?php echo esc_attr( $wpssw_labelid ); ?>" class="woo-event-headers-chk" <?php echo esc_html( $wpssw_woo_is_checked ); ?>>
													<span class="checkbox-switch-new"></span>
													<span class="ui-icon ui-icon-caret-2-n-s wpssw-tooltio-link">
														<svg width="17" height="16" viewBox="0 0 17 16" fill="none" xmlns="http://www.w3.org/2000/svg">
														<mask id="mask0_384_3228" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="0" y="0" width="17" height="16">
														<rect x="0.5" width="16" height="16" fill="#D9D9D9"/>
														</mask>
														<g mask="url(#mask0_384_3228)">
														<path d="M5.95875 13.67C5.55759 13.67 5.21418 13.5272 4.92851 13.2415C4.64284 12.9558 4.5 12.6124 4.5 12.2113C4.5 11.8101 4.64284 11.4667 4.92851 11.181C5.21418 10.8953 5.55759 10.7525 5.95875 10.7525C6.35991 10.7525 6.70332 10.8953 6.98899 11.181C7.27466 11.4667 7.4175 11.8101 7.4175 12.2113C7.4175 12.6124 7.27466 12.9558 6.98899 13.2415C6.70332 13.5272 6.35991 13.67 5.95875 13.67ZM10.335 13.67C9.93384 13.67 9.59043 13.5272 9.30476 13.2415C9.01909 12.9558 8.87625 12.6124 8.87625 12.2113C8.87625 11.8101 9.01909 11.4667 9.30476 11.181C9.59043 10.8953 9.93384 10.7525 10.335 10.7525C10.7362 10.7525 11.0796 10.8953 11.3652 11.181C11.6509 11.4667 11.7937 11.8101 11.7937 12.2113C11.7937 12.6124 11.6509 12.9558 11.3652 13.2415C11.0796 13.5272 10.7362 13.67 10.335 13.67ZM5.95875 9.29375C5.55759 9.29375 5.21418 9.15091 4.92851 8.86524C4.64284 8.57957 4.5 8.23616 4.5 7.835C4.5 7.43384 4.64284 7.09043 4.92851 6.80476C5.21418 6.51909 5.55759 6.37625 5.95875 6.37625C6.35991 6.37625 6.70332 6.51909 6.98899 6.80476C7.27466 7.09043 7.4175 7.43384 7.4175 7.835C7.4175 8.23616 7.27466 8.57957 6.98899 8.86524C6.70332 9.15091 6.35991 9.29375 5.95875 9.29375ZM10.335 9.29375C9.93384 9.29375 9.59043 9.15091 9.30476 8.86524C9.01909 8.57957 8.87625 8.23616 8.87625 7.835C8.87625 7.43384 9.01909 7.09043 9.30476 6.80476C9.59043 6.51909 9.93384 6.37625 10.335 6.37625C10.7362 6.37625 11.0796 6.51909 11.3652 6.80476C11.6509 7.09043 11.7937 7.43384 11.7937 7.835C11.7937 8.23616 11.6509 8.57957 11.3652 8.86524C11.0796 9.15091 10.7362 9.29375 10.335 9.29375ZM5.95875 4.9175C5.55759 4.9175 5.21418 4.77466 4.92851 4.48899C4.64284 4.20332 4.5 3.85991 4.5 3.45875C4.5 3.05759 4.64284 2.71418 4.92851 2.42851C5.21418 2.14284 5.55759 2 5.95875 2C6.35991 2 6.70332 2.14284 6.98899 2.42851C7.27466 2.71418 7.4175 3.05759 7.4175 3.45875C7.4175 3.85991 7.27466 4.20332 6.98899 4.48899C6.70332 4.77466 6.35991 4.9175 5.95875 4.9175ZM10.335 4.9175C9.93384 4.9175 9.59043 4.77466 9.30476 4.48899C9.01909 4.20332 8.87625 3.85991 8.87625 3.45875C8.87625 3.05759 9.01909 2.71418 9.30476 2.42851C9.59043 2.14284 9.93384 2 10.335 2C10.7362 2 11.0796 2.14284 11.3652 2.42851C11.6509 2.71418 11.7937 3.05759 11.7937 3.45875C11.7937 3.85991 11.6509 4.20332 11.3652 4.48899C11.0796 4.77466 10.7362 4.9175 10.335 4.9175Z" fill="#64748B"/>
														</g>
														</svg>
														<span class="tooltip-text">Drag & Drop</span>
													</span>
												</span>
											</label>
										</li>
														<?php
													}
												}
												if ( ! empty( $wpssw_wooevent_headers ) ) {
													foreach ( $wpssw_wooevent_headers as $wpssw_key => $wpssw_val ) {
														$wpssw_woo_is_checked = '';
														if ( in_array( $wpssw_val, $wpssw_woo_selections, true ) ) {
															continue;
														}
														$wpssw_labelid = strtolower( str_replace( ' ', '_', $wpssw_val ) );
														?>
														<li class="default-order-sheet ui-state-default">
	<label for="woo-<?php echo esc_attr( $wpssw_labelid ); ?>">
															<span class="orderSheet-left">
																<span class="wootextfield"><?php echo esc_html( $wpssw_val ); ?></span>
																<span class="ui-icon ui-icon-pencil wpssw-tooltio-link">
																	<span class="pencil-icon">
																		<svg width="12" height="12" viewBox="0 0 13 13" fill="none" xmlns="http://www.w3.org/2000/svg">
																		<path d="M1.33333 11.6668H2.26667L8.01667 5.91677L7.08333 4.98343L1.33333 10.7334V11.6668ZM10.8667 4.9501L8.03333 2.1501L8.96667 1.21677C9.22222 0.961213 9.53611 0.833435 9.90833 0.833435C10.2806 0.833435 10.5944 0.961213 10.85 1.21677L11.7833 2.1501C12.0389 2.40566 12.1722 2.71399 12.1833 3.0751C12.1944 3.43621 12.0722 3.74455 11.8167 4.0001L10.8667 4.9501ZM9.9 5.93343L2.83333 13.0001H0V10.1668L7.06667 3.1001L9.9 5.93343Z" fill="#64748B"/>
																		</svg>
																		</span>
																		<span class="check-icon">
																			<svg xmlns="http://www.w3.org/2000/svg"  viewBox="0 0 26 26" width="12" height="12">
																				<path d="M 22.566406 4.730469 L 20.773438 3.511719 C 20.277344 3.175781 19.597656 3.304688 19.265625 3.796875 L 10.476563 16.757813 L 6.4375 12.71875 C 6.015625 12.296875 5.328125 12.296875 4.90625 12.71875 L 3.371094 14.253906 C 2.949219 14.675781 2.949219 15.363281 3.371094 15.789063 L 9.582031 22 C 9.929688 22.347656 10.476563 22.613281 10.96875 22.613281 C 11.460938 22.613281 11.957031 22.304688 12.277344 21.839844 L 22.855469 6.234375 C 23.191406 5.742188 23.0625 5.066406 22.566406 4.730469 Z" fill="#64748B"/></svg>
																		</span>
																		<span class="tooltip-text edit-tooltip">Edit</span>
																		<span class="tooltip-text update-tooltip">Update</span>
																</span>
																</span>
															</span>
															<span class="orderSheet-right">
																<input type="checkbox" name="wooevent_custom[]" value="<?php echo esc_attr( $wpssw_val ); ?>" class="woo-event-headers-chk1" <?php echo esc_attr( $wpssw_woo_is_checked ); ?> hidden="true">
																<input type="checkbox" name="wooevent_header_list[]" value="<?php echo esc_attr( $wpssw_val ); ?>" id="woo-<?php echo esc_attr( $wpssw_labelid ); ?>" class="woo-event-headers-chk" <?php echo esc_attr( $wpssw_woo_is_checked ); ?>>
																<span class="checkbox-switch-new"></span>
																<span class="ui-icon ui-icon-caret-2-n-s wpssw-tooltio-link">
																	<svg width="17" height="16" viewBox="0 0 17 16" fill="none" xmlns="http://www.w3.org/2000/svg">
																	<mask id="mask0_384_3228" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="0" y="0" width="17" height="16">
																	<rect x="0.5" width="16" height="16" fill="#D9D9D9"/>
																	</mask>
																	<g mask="url(#mask0_384_3228)">
																	<path d="M5.95875 13.67C5.55759 13.67 5.21418 13.5272 4.92851 13.2415C4.64284 12.9558 4.5 12.6124 4.5 12.2113C4.5 11.8101 4.64284 11.4667 4.92851 11.181C5.21418 10.8953 5.55759 10.7525 5.95875 10.7525C6.35991 10.7525 6.70332 10.8953 6.98899 11.181C7.27466 11.4667 7.4175 11.8101 7.4175 12.2113C7.4175 12.6124 7.27466 12.9558 6.98899 13.2415C6.70332 13.5272 6.35991 13.67 5.95875 13.67ZM10.335 13.67C9.93384 13.67 9.59043 13.5272 9.30476 13.2415C9.01909 12.9558 8.87625 12.6124 8.87625 12.2113C8.87625 11.8101 9.01909 11.4667 9.30476 11.181C9.59043 10.8953 9.93384 10.7525 10.335 10.7525C10.7362 10.7525 11.0796 10.8953 11.3652 11.181C11.6509 11.4667 11.7937 11.8101 11.7937 12.2113C11.7937 12.6124 11.6509 12.9558 11.3652 13.2415C11.0796 13.5272 10.7362 13.67 10.335 13.67ZM5.95875 9.29375C5.55759 9.29375 5.21418 9.15091 4.92851 8.86524C4.64284 8.57957 4.5 8.23616 4.5 7.835C4.5 7.43384 4.64284 7.09043 4.92851 6.80476C5.21418 6.51909 5.55759 6.37625 5.95875 6.37625C6.35991 6.37625 6.70332 6.51909 6.98899 6.80476C7.27466 7.09043 7.4175 7.43384 7.4175 7.835C7.4175 8.23616 7.27466 8.57957 6.98899 8.86524C6.70332 9.15091 6.35991 9.29375 5.95875 9.29375ZM10.335 9.29375C9.93384 9.29375 9.59043 9.15091 9.30476 8.86524C9.01909 8.57957 8.87625 8.23616 8.87625 7.835C8.87625 7.43384 9.01909 7.09043 9.30476 6.80476C9.59043 6.51909 9.93384 6.37625 10.335 6.37625C10.7362 6.37625 11.0796 6.51909 11.3652 6.80476C11.6509 7.09043 11.7937 7.43384 11.7937 7.835C11.7937 8.23616 11.6509 8.57957 11.3652 8.86524C11.0796 9.15091 10.7362 9.29375 10.335 9.29375ZM5.95875 4.9175C5.55759 4.9175 5.21418 4.77466 4.92851 4.48899C4.64284 4.20332 4.5 3.85991 4.5 3.45875C4.5 3.05759 4.64284 2.71418 4.92851 2.42851C5.21418 2.14284 5.55759 2 5.95875 2C6.35991 2 6.70332 2.14284 6.98899 2.42851C7.27466 2.71418 7.4175 3.05759 7.4175 3.45875C7.4175 3.85991 7.27466 4.20332 6.98899 4.48899C6.70332 4.77466 6.35991 4.9175 5.95875 4.9175ZM10.335 4.9175C9.93384 4.9175 9.59043 4.77466 9.30476 4.48899C9.01909 4.20332 8.87625 3.85991 8.87625 3.45875C8.87625 3.05759 9.01909 2.71418 9.30476 2.42851C9.59043 2.14284 9.93384 2 10.335 2C10.7362 2 11.0796 2.14284 11.3652 2.42851C11.6509 2.71418 11.7937 3.05759 11.7937 3.45875C11.7937 3.85991 11.6509 4.20332 11.3652 4.48899C11.0796 4.77466 10.7362 4.9175 10.335 4.9175Z" fill="#64748B"/>
																	</g>
																	</svg>
																	<span class="tooltip-text">Drag & Drop</span>
																</span>
															</span>
														</label>
													</li>
														<?php
													}
												}
												?>
										</ul>
									</div>
								</div>
							</div>
						</div>
							<?php
							$wpssw_eventsheets_list = array();
							if ( is_array( self::wpssw_option( 'wpssw_eventsheets_list' ) ) ) {
								$wpssw_eventsheets_list = self::wpssw_option( 'wpssw_eventsheets_list' );
							}
							if ( ! empty( $wpssw_eventsheet_id ) && ! empty( $wpssw_events_categories ) && ! empty( $wpssw_eventsheets_list ) ) {
								?>
						<div valign="top" class="event_spreadsheet_row checkbox_margin generalSetting-section">
							<div scope="row" class="titledesc generalSetting-left">
								<h4>
									<span class="wpssw-tooltio-link tooltip-right">
										<?php echo esc_html__( 'Sync Events', 'wpssw' ); ?>
										<span class="tooltip-text">Export</span>
									</span>
								</h4>
								<p><?php echo esc_html__( 'The "Click to Sync" button will append all/custom date range event orders that are not present in the sheet, without updating the existing orders.', 'wpssw' ); ?></p>
								<div class="sync_all_fromtodate-main">
									<div class="syncall-radio">
										<div class="syncall-radio-box">
											<input type="radio" name="event_sync_all_checkbox" value="1" id="event_sync_all" checked="checked">
											<label for="event_sync_all"><?php echo esc_html__( 'All Events', 'wpssw' ); ?></label>
										</div>
										<div class="syncall-radio-box">
											<input type="radio" name="event_sync_all_checkbox" value="0" id="event_sync_daterange">
											<label for="event_sync_daterange"><?php echo esc_html__( 'Date Range', 'wpssw' ); ?></label>
										</div>
									</div>
									<div class="forminp forminp-select event_sync_all_fromtodate generalSetting-right"> 
										<label for="event_sync_all_fromdate" >
										<?php echo esc_html__( 'From :', 'wpssw' ); ?>   <input name="event_sync_all_fromdate" id="event_sync_all_fromdate" class="event_sync_all_fromdate" type="date" >
										</label>
										<label for="event_sync_all_todate" class="event_sync_todate_label">
										<?php echo esc_html__( 'To :', 'wpssw' ); ?> <input name="event_sync_all_todate" id="event_sync_all_todate" class="event_sync_all_todate" type="date" >
										</label>
									</div>
								</div>
								<div class="sync-button-box">
									<img src="images/spinner.gif" id="eventsyncloader">
									<span id="eventsynctest"><?php echo esc_html__( 'Synchronizing...', 'wpssw' ); ?></span>
									<a class="wpssw-button wpssw-button-secondary" href="javascript:void(0)" id="sync_event"><?php echo esc_html__( 'Click to Sync', 'wpssw' ); ?></a>
								</div>
							</div>
						</div>
								<?php
								self::wpssw_auto_sync_setting(
									'event',
									array(
										'id'    => '',
										'class' => 'event_spreadsheet_row',
									)
								);
								?>
						<div valign="top" class="event_spreadsheet_row generalSetting-section">
							<div scope="row" class="titledesc generalSetting-left">
								<h4><?php echo esc_html__( 'Clear Spreadsheet', 'wpssw' ); ?></h4>
								<p><?php echo esc_html__( 'This setting will clear the event categories sheets.', 'wpssw' ); ?>
							</div>
							<div class="forminp generalSetting-right">              
								<img src="images/spinner.gif" id="cleareventloader">
								<a class="wpssw-button wpssw-button-secondary" href="javascript:void(0)" id="clear_eventsheet"><?php echo esc_html__( 'Click to Clear Spreadsheet', 'wpssw' ); ?></a> 
							</div>
						</div>
						<?php } ?>
							<?php
							if ( WPSSW_Dependencies::wpssw_is_woocommerce_active() ) {
								?>
					<div class="submit-section">
						<p class="submit"><input type="submit" name="submit" id="submit" class="wpssw-button wpssw-button-primary" value="Save"></p>
					</div>
								<?php
							} else {
								?>
							<div class="generalSetting-section generalSetting-section-message">
								<h4><?php echo 'WPSyncSheets For WooCommerce plugin requires <a href="http://wordpress.org/extend/plugins/woocommerce/">WooCommerce</a> plugin to be active!'; ?></h4>
							</div>
							<?php } ?>
				</form>
							<?php
						} else {
							?>
						<div class="generalSetting-section generalSetting-section-message">
							<h4><?php echo esc_html__( 'Please select spreadsheet from ', 'wpssw' ); ?>
								<strong><?php echo esc_html__( 'Order Settings', 'wpssw' ); ?></strong>
								<a href='<?php echo esc_url( 'admin.php?page=wpsyncsheets-for-woocommerce&tab=order-settings' ); ?>'> <?php echo esc_html__( 'Click', 'wpssw' ); ?></a>
							</h4>
						</div>
							<?php
						}
					} else {
						?>
					<div class="generalSetting-section generalSetting-section-message">
						<h4><?php echo esc_html__( 'Please genearate authentication code from', 'wpssw' ); ?>
							<strong><?php echo esc_html__( 'Google API Setting', 'wpssw' ); ?></strong>
							<a href='<?php echo esc_url( 'admin.php?page=wpsyncsheets-for-woocommerce' ); ?>'> <?php echo esc_html__( 'Click', 'wpssw' ); ?></a>
						</h4>
					</div>
						<?php } ?>
				</div>
				<?php } ?>
				<!-- Event settings block end -->
				<div id="export" class="tabcontent">
				<?php
				$wpssw_google_settings = self::wpssw_option( 'wpssw_google_settings' );
				if ( ! empty( $wpssw_google_settings[2] ) && WPSSW_Dependencies::wpssw_is_woocommerce_active() && self::$instance_api->checkcredenatials() ) {
					$wpssw_product_category_list = array();
					$args                        = array(
						'taxonomy' => 'product_cat',
						'orderby'  => 'name',
					);
					$product_categories          = get_terms( $args );
					foreach ( $product_categories as $prd_cat ) {
						$wpssw_product_category_list[ $prd_cat->term_id ] = $prd_cat->name;
					}

					$wpssw_spreadsheetid             = self::wpssw_option( 'wpssw_woocommerce_spreadsheet' );
					$wpssw_spreadsheets_list         = self::$instance_api->get_spreadsheet_listing();
					$wpssw_headers_name              = self::wpssw_option( 'wpssw_sheet_headers_list' );
					$wpssw_order_spreadsheet_setting = WPSSW_Order::wpssw_check_order_spreadsheet_setting();

					if ( 'yes' === (string) $wpssw_order_spreadsheet_setting && ! empty( $wpssw_spreadsheetid ) && array_key_exists( $wpssw_spreadsheetid, $wpssw_spreadsheets_list ) && ! empty( $wpssw_headers_name ) ) {
						?>
					<form method="post" action="" id="exportform">
						<?php wp_nonce_field( 'save_export_settings', 'wpssw_export_settings' ); ?>
						<div class="wpssw-section-1">
							<div class="generalSetting-section">
								<div class="generalSetting-left">
									<h4><?php echo esc_html__( 'Export Orders', 'wpssw' ); ?></h4>
									<p><?php echo esc_html__( 'Effortlessly export your orders by choosing between two convenient settings. You can export all orders or select a custom order date range based on your preferences. The exported data will be organized into a new spreadsheet on your Google Drive.', 'wpssw' ); ?></p>
								</div>
							</div>
							<div class="generalSetting-section">
								<div scope="row" class="titledesc generalSetting-left">
									<h4 for="woocommerce_spreadsheet"><?php echo esc_html__( 'Enter Spreadsheet Name', 'wpssw' ); ?> <span class="woocommerce-help-tip" ></span></h4>
									<p><?php echo esc_html__( 'Specify the desired name for the exported spreadsheet through this setting. The value you enter here will serve as the name of the newly created spreadsheet.', 'wpssw' ); ?></p>
									<div class="forminp forminp-select export_newsheetinput"><input name="expspreadsheetname" id="expspreadsheetname" type="text" placeholder="<?php echo esc_attr__( 'Enter Spreadsheet Name', 'wpssw' ); ?>" required class="input-text"></div>
								</div>
							</div>
							<div class="generalSetting-section sheetHeaders-section export-order-setting-producat-filter-section-row">
								<div>
									<div class="export-order-setting-producat-filter-section">
										<div class="titledesc generalSetting-left">
											<h4 for="category_select"><?php echo esc_html__( 'Select Category', 'wpssw' ); ?></h4>
											<p><?php echo esc_html__( 'To streamline your export process, you can select orders from specific product categories you wish to include in the spreadsheet.', 'wpssw' ); ?></p>
										</div>
										<div class="exportrow generalSetting-right">              
											<label for="category_select">
												<input name="category_select" id="category_select" type="checkbox" class="" value="1"><span class="checkbox-switch"></span> 							
											</label>
										</div>
									</div>
									<div class="td-prdcat-wpssw" >
										<div class="td-wpssw-headers">
											<div class='wpssw_headers'>								
												<div class="selectall-btnset">
													<?php
													if ( ! empty( $wpssw_selections ) ) {
														$wpssw_class = 'wpssw-prdcatselect';
													} else {
														$wpssw_class = '';
													}
													?>
													<button type="button" id="prdcatselectall" class="wpssw-button wpssw-button-primary <?php echo esc_attr( $wpssw_class ); ?>"> <?php echo esc_html__( 'Select All', 'wpssw' ); ?></button> 
													<button type="button" id="prdcatselectnone" class="wpssw-button wpssw-button-secondary <?php echo esc_attr( $wpssw_class ); ?>"> <?php echo esc_html__( 'Select None', 'wpssw' ); ?></button>
												</div>
												<div class="sheetHeaders-main">
													<ul id="product-sortable" class="sheetHeaders-box">
														<?php
														foreach ( $wpssw_product_category_list as $wpssw_key => $wpssw_val ) {
															?>
															<li class="default-order-sheet ui-state-default">
																<label for="<?php echo esc_attr( $wpssw_val ); ?>">
																	<span class="orderSheet-left">
																		<?php echo esc_html( $wpssw_val ); ?>
																	</span>
																	<span class="orderSheet-right">
																		<input type="checkbox" name="productcat_header[]" value="<?php echo esc_attr( $wpssw_key ); ?>" id="<?php echo esc_attr( $wpssw_val ); ?>" class="prdcatheaders_chk">
																		<span class="checkbox-switch-new"></span>
																	</span>
																</label>
															</li>
															<?php
														}
														?>
													</ul>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="generalSetting-section">
								<div scope="row" class="titledesc generalSetting-left">
									<h4 for="woocommerce_spreadsheet"><?php echo esc_html__( 'Order Date Range', 'wpssw' ); ?> <span class="woocommerce-help-tip" data-tip="Please select Google Spreadsheet."></span></h4>
									<p><?php echo esc_html__( 'Customize the export further by selecting all orders or specifying a custom date range to be included in the exported data.', 'wpssw' ); ?></p>
									<div class="sync_all_fromtodate-main">
										<div class="syncall-radio">
											<div class="syncall-radio-box">
												<input type="radio" name="exportall_checkbox" value="1" id="exportall" checked="checked">
												<label for="exportall"><?php echo esc_html__( 'All Orders', 'wpssw' ); ?></label>
											</div>
											<div class="syncall-radio-box">
												<input type="radio" name="exportall_checkbox" value="0" id="export_daterange">
												<label for="export_daterange"><?php echo esc_html__( 'Date Range', 'wpssw' ); ?></label>
											</div>
										</div>
										<div class="forminp forminp-select sync_all_fromtodate export_all_fromtodate "> 
											<label for="sync_all_fromdate" >
												<?php echo esc_html__( 'From :', 'wpssw' ); ?> <input name="expspreadsheetname" id="ordfromdate" type="date">
											</label>
											<label for="sync_all_todate" class="sync_todate_label">
												<?php echo esc_html__( 'To :', 'wpssw' ); ?> <input name="expspreadsheetname" id="ordtodate" type="date">
											</label>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="submit-section">
							<p class="submit"><input type="submit" name="submit" id="exportsubmit" class="wpssw-button wpssw-button-primary" value="Export"><span class='processbar'><img src="<?php dirname( __FILE__ ); ?>images/spinner.gif" id="expsyncloader"><span id="expsynctext"><?php echo esc_html__( 'Please wait...', 'wpssw' ); ?></span></span><a target='_blank' class="wpssw-button wpssw-button-secondary" href="" id='spreadsheet_url'><?php echo esc_html__( 'View Spreadsheet', 'wpssw' ); ?></a><a target='_blank' class="wpssw-button wpssw-button-secondary" href="" id='spreadsheet_xslxurl'><?php echo esc_html__( 'Download Spreadsheet (.xlsx)', 'wpssw' ); ?></a>
							</p>
						</div>
						<?php
						$xlsxurl = "https://docs.google.com/spreadsheets/u/0/d/{$wpssw_spreadsheetid}/export?exportFormat=xlsx";
						?>
						<div class="downloadall generalSetting-section">
							<div class="titledesc  generalSetting-left">
								<h4><?php echo esc_html__( 'Download Spreadsheet', 'wpssw' ); ?></h4>
								<p><?php echo esc_html__( 'If you need to access the existing order spreadsheet, you can easily download it with a single click.', 'wpssw' ); ?></p>
							</div>
							<div class="exportrow generalSetting-right">              
								<a id="view_spreadsheet" download="" target="_blank" href="<?php echo esc_url( $xlsxurl ); ?>" class="wpssw-button wpssw-button-secondary"><?php echo esc_html__( 'Download Spreadsheet (.xlsx)', 'wpssw' ); ?></a>
							</div>
						</div>
					</form>
						<?php
					} elseif ( 'yes' !== (string) $wpssw_order_spreadsheet_setting ) {
						?>
						<div class="generalSetting-section generalSetting-section-message">
							<h4><?php echo esc_html__( 'Please save your order settings first and try again later.', 'wpssw' ); ?></h4>
						</div>
					<?php } else { ?>
						<div class="generalSetting-section generalSetting-section-message">
							<h4><?php echo esc_html__( 'Please select Spreadsheet from ', 'wpssw' ); ?>
								<strong><?php echo esc_html__( 'Order Settings ', 'wpssw' ); ?></strong>
								<a href='<?php echo esc_url( 'admin.php?page=wpsyncsheets-for-woocommerce&tab=order-settings' ); ?>'> <?php echo esc_html__( 'Click', 'wpssw' ); ?></a>
							</h4>
						</div>
						<?php
					}
				} else {
					if ( ! WPSSW_Dependencies::wpssw_is_woocommerce_active() ) {
						?>
						<div class="generalSetting-section generalSetting-section-message">
							<h4><?php echo 'WPSyncSheets For WooCommerce plugin requires <a href="http://wordpress.org/extend/plugins/woocommerce/">WooCommerce</a> plugin to be active!'; ?></h4>
						</div>
					<?php } elseif ( empty( $wpssw_google_settings[2] ) ) { ?>
						<div class="generalSetting-section generalSetting-section-message">
							<h4><?php echo esc_html__( 'Please genearate authentication code from', 'wpssw' ); ?>
								<strong><?php echo esc_html__( 'Google API Setting ', 'wpssw' ); ?></strong>
								<a href='<?php echo esc_url( 'admin.php?page=wpsyncsheets-for-woocommerce' ); ?>'> <?php echo esc_html__( 'Click', 'wpssw' ); ?></a>
							</h4>
						</div>
						<?php
					}
				}
				?>
				</div>
				<?php
				if ( is_array( $wpssw_tabs ) && ! empty( $wpssw_tabs ) ) {
					foreach ( $wpssw_tabs as $tabkey => $tabname ) {
						?>
							<div id="<?php echo esc_attr( $tabkey ); ?>" class="tabcontent">
							<?php do_action( 'wpsyncsheets_tab_' . $tabkey ); ?>
							</div>
							<?php
					}
				}
				?>
				<div id="product-settings" class="tabcontent">
				<?php
				$wpssw_product_import              = self::wpssw_option( 'wpssw_product_import' );
				$wpssw_product_insert              = self::wpssw_option( 'wpssw_product_insert' );
				$wpssw_product_update              = self::wpssw_option( 'wpssw_product_update' );
				$wpssw_product_delete              = self::wpssw_option( 'wpssw_product_delete' );
				$wpssw_product_spreadsheet_setting = self::wpssw_option( 'wpssw_product_spreadsheet_setting' );
				$wpssw_product_spreadsheet_id      = self::wpssw_option( 'wpssw_product_spreadsheet_id' );
				$wpssw_spreadsheet_id              = self::wpssw_option( 'wpssw_woocommerce_spreadsheet' );
				$wpssw_prdsheet_id                 = $wpssw_product_spreadsheet_id;
				$wpssw_import_attribute_checkbox   = self::wpssw_option( 'wpssw_import_attribute_checkbox' );

				$wpssw_product_categories_as_sheet = self::wpssw_option( 'product_categories_as_sheet' );
				$wpssw_include_child_categories    = self::wpssw_option( 'wpssw_include_child_categories' );

					$wpssw_productsheet_name = '';
				if ( self::wpssw_check_sheet_exist( $wpssw_prdsheet_id, 'All Products' ) ) {
					$wpssw_productsheet_name = 'All Products';
				}

				if ( empty( $wpssw_product_spreadsheet_id ) ) {
					$wpssw_product_spreadsheet_id = $wpssw_spreadsheet_id;
				}
					$wpssw_checked = '';
				if ( 'yes' === (string) $wpssw_product_spreadsheet_setting ) {
					$wpssw_checked = 'checked';
				}
				$wpssw_static_product_header        = stripslashes_deep( self::wpssw_option( 'wpssw_product_static_header' ) );
				$wpssw_static_product_header_values = stripslashes_deep( self::wpssw_option( 'wpssw_static_product_header_values' ) );
				if ( ! $wpssw_static_product_header ) {
					$wpssw_static_product_header = array();
				}
				$wpssw_product_custom_value = array();
				if ( ! $wpssw_static_product_header_values ) {
					$wpssw_static_product_header_values = array();
				}
				if ( ! empty( $wpssw_static_product_header_values ) ) {
					foreach ( $wpssw_static_product_header_values as $wpssw_static_product_header_value ) {
						if ( strpos( $wpssw_static_product_header_value, ',(static_header),' ) ) {
							$wpssw_static_product_header_value = str_replace( ',(static_header),', ',', $wpssw_static_product_header_value );
							$wpssw_product_custom_value[]      = explode( ',', $wpssw_static_product_header_value );
						}
					}
				}
				?>
				<?php
				if ( ! empty( $wpssw_google_settings[2] ) ) {
					?>
				<form method="post" action="<?php echo esc_html( admin_url( 'admin.php?page=wpsyncsheets-for-woocommerce&tab=product-settings' ) ); ?>" id="productform">
					<?php wp_nonce_field( 'save_product_settings', 'wpssw_product_settings' ); ?>
					<input name="product_sheet" id="product_sheet" type="hidden" class="" value="<?php echo esc_html( $wpssw_productsheet_name ); ?>">					
						<div valign="top" class="checkbox_margin generalSetting-section">
							<div scope="row" class="titledesc generalSetting-left">
								<h4><?php echo esc_html__( 'Product Settings', 'wpssw' ); ?></h4>
								<p><?php echo esc_html__( 'Enable this option to automatically generate customized spreadsheets, including the essential "All Products" sheet.', 'wpssw' ); ?></p>
							</div>
							<div class="forminp generalSetting-right">              
								<label for="product_settings_checkbox">
									<input name="product_settings_checkbox" id="product_settings_checkbox" type="checkbox" class="" value="1" <?php echo esc_attr( $wpssw_checked ); ?>><span class="checkbox-switch"></span><span class="checkbox-switch"></span> 		
								</label>
							</div>
						</div>
							<?php
							$spreadsheets_list = self::$instance_api->get_spreadsheet_listing();
							?>
						<div class="generalSetting-section prd_spreadsheet_row googleSpreadsheet-section">
							<div class="generalSetting-left">
								<h4><?php echo esc_html__( 'Google Spreadsheet Settings', 'wpssw' ); ?></h4>
								<p><?php echo esc_html__( 'Upon assigning a Google Spreadsheet, it will automatically generate the "All Products" sheet, populated with specified sheet headers. Additionally, whenever new products are created, WPSyncSheets add new rows dynamically to keep everything up to date.', 'wpssw' ); ?></p>
								<div class="createanew-radio">
									<div class="createanew-radio-box">
										<input type="radio" name="prdsheetselection" value="new" id="prdcreateanew">
										<label for="prdcreateanew"><?php echo esc_html__( 'Create New Spreadsheet', 'wpssw' ); ?></label>
									</div>
									<div class="createanew-radio-box">
										<input type="radio" name="prdsheetselection" value="existing" id="prdexisting" checked="checked">
										<label for="prdexisting"><?php echo esc_html__( 'Select Existing Spreadsheet', 'wpssw' ); ?></label>
									</div>
								</div>
								<div id="product_spreadsheet_container" class="spreadsheet-form">
									<select name="product_spreadsheet" id="product_spreadsheet" style="min-width:150px;" class="">
										<?php
										$selected = '';
										foreach ( $spreadsheets_list as $spreadsheetid => $spreadsheetname ) {
											if ( (string) $wpssw_product_spreadsheet_id === $spreadsheetid ) {
												$selected = 'selected="selected"';
											}
											?>
										<option value="<?php echo esc_attr( $spreadsheetid ); ?>" <?php echo esc_attr( $selected ); ?>><?php echo esc_html( $spreadsheetname ); ?></option>
										<?php $selected = ''; } ?>
									</select>
								</div>
								<div valign="top" id="product_newsheet" class="newsheetinput spreadsheet-form prd_spreadsheet_inputrow">
									<input name="product_spreadsheet_name" id="product_spreadsheet_name" type="text" class="input-text" value="" placeholder="<?php echo esc_html__( 'Enter Spreadsheet Name', 'wpssw' ); ?>"><span class="checkbox-switch"></span><span class="checkbox-switch"></span>
								</div>
							</div>
						</div>
						<div class="prd_spreadsheet_row generalSetting-section sheetHeaders-section">
							<div class="td-wpssw-headers">
								<div class="wpssw_headers">
									<div class="generalSetting-sheet-headers-row">
										<div class="generalSetting-left">
											<h4><?php echo esc_html__( 'Sheet Headers', 'wpssw' ); ?></h4>
											<p><?php echo esc_html__( 'Any disabled sheet headers will be automatically removed from the current spreadsheet. Re-enable the desired headers, save the settings, and your spreadsheet will be updated with the latest data with a single click of the "Click to Sync" button.', 'wpssw' ); ?></p>
										</div>
									</div>
										<div class="selectall-btnset">
											<button type="button" class="wpssw-button wpssw-button-primary" id="woo-proselectall"><?php echo esc_html__( 'Select All', 'wpssw' ); ?></button>
											<button type="button" class="wpssw-button wpssw-button-secondary" id="woo-proselectnone"><?php echo esc_html__( 'Select None', 'wpssw' ); ?></button>
										</div>
									<div class="sheetHeaders-main">
										<ul id="woo-product-sortable" class="sheetHeaders-box">
											<?php
											$wpssw_woo_is_checked = '';
											$wpssw_woo_selections = stripslashes_deep( self::wpssw_option( 'wpssw_woo_product_headers' ) );
											if ( ! $wpssw_woo_selections ) {
												$wpssw_woo_selections = array();
											}
											$wpssw_woo_selections_custom = stripslashes_deep( self::wpssw_option( 'wpssw_woo_product_headers_custom' ) );
											if ( ! $wpssw_woo_selections_custom ) {
												$wpssw_woo_selections_custom = array();
											}

											$wpssw_include = new WPSSW_Include_Action();
											$wpssw_include->wpssw_include_product_compatibility_files();
											$wpssw_wooproduct_headers = apply_filters( 'wpsyncsheets_product_headers', array() );
											$wpssw_wooproduct_headers = self::wpssw_array_flatten( $wpssw_wooproduct_headers );
											$wpssw_operation          = array( 'Insert', 'Update', 'Delete' );
											if ( ! empty( $wpssw_woo_selections ) ) {
												foreach ( $wpssw_woo_selections as $wpssw_key => $wpssw_val ) {
													$wpssw_woo_is_checked = 'checked';
													$wpssw_labelid        = strtolower( str_replace( ' ', '_', $wpssw_val ) );
													$wpssw_display        = true;
													$wpssw_classname      = '';
													if ( in_array( $wpssw_val, $wpssw_operation, true ) ) {
														$wpssw_display   = false;
														$wpssw_labelid   = '';
														$wpssw_classname = strtolower( $wpssw_val ) . 'product';
													}
													?>
											<li class="default-order-sheet ui-state-default <?php echo esc_html( $wpssw_classname ); ?>">
												<label for="woo-<?php echo esc_attr( $wpssw_labelid ); ?>">
												<span class="orderSheet-left">
													<span class="wootextfield"><?php echo isset( $wpssw_woo_selections_custom[ $wpssw_key ] ) ? esc_attr( $wpssw_woo_selections_custom[ $wpssw_key ] ) : esc_attr( $wpssw_val ); ?></span>
													<?php if ( $wpssw_display ) { ?>
													<span class="ui-icon ui-icon-pencil wpssw-tooltio-link">
														<span class="pencil-icon">
														<svg width="12" height="12" viewBox="0 0 13 13" fill="none" xmlns="http://www.w3.org/2000/svg">
														<path d="M1.33333 11.6668H2.26667L8.01667 5.91677L7.08333 4.98343L1.33333 10.7334V11.6668ZM10.8667 4.9501L8.03333 2.1501L8.96667 1.21677C9.22222 0.961213 9.53611 0.833435 9.90833 0.833435C10.2806 0.833435 10.5944 0.961213 10.85 1.21677L11.7833 2.1501C12.0389 2.40566 12.1722 2.71399 12.1833 3.0751C12.1944 3.43621 12.0722 3.74455 11.8167 4.0001L10.8667 4.9501ZM9.9 5.93343L2.83333 13.0001H0V10.1668L7.06667 3.1001L9.9 5.93343Z" fill="#64748B"/>
														</svg>
														</span>
														<span class="check-icon">
															<svg xmlns="http://www.w3.org/2000/svg"  viewBox="0 0 26 26" width="12" height="12">
																<path d="M 22.566406 4.730469 L 20.773438 3.511719 C 20.277344 3.175781 19.597656 3.304688 19.265625 3.796875 L 10.476563 16.757813 L 6.4375 12.71875 C 6.015625 12.296875 5.328125 12.296875 4.90625 12.71875 L 3.371094 14.253906 C 2.949219 14.675781 2.949219 15.363281 3.371094 15.789063 L 9.582031 22 C 9.929688 22.347656 10.476563 22.613281 10.96875 22.613281 C 11.460938 22.613281 11.957031 22.304688 12.277344 21.839844 L 22.855469 6.234375 C 23.191406 5.742188 23.0625 5.066406 22.566406 4.730469 Z" fill="#64748B"/></svg>
														</span>
														<span class="tooltip-text edit-tooltip">Edit</span>
														<span class="tooltip-text update-tooltip">Update</span>
													</span>
													<?php } ?>
													</span>
													<span class="orderSheet-right">
													<input type="checkbox" name="wooproduct_custom[]" value="<?php echo isset( $wpssw_woo_selections_custom[ $wpssw_key ] ) ? esc_attr( $wpssw_woo_selections_custom[ $wpssw_key ] ) : esc_attr( $wpssw_val ); ?>" class="woo-pro-headers-chk1" <?php echo esc_attr( $wpssw_woo_is_checked ); ?> hidden="true">

													<?php
													if ( in_array( $wpssw_val, $wpssw_static_product_header, true ) ) {
														?>
														<input type="checkbox" name="product_header_fields_static[]" value="<?php echo esc_attr( $wpssw_val ); ?>" hidden="true" checked>
															<?php
													}
													if ( in_array( $wpssw_val, array_column( $wpssw_product_custom_value, 0 ), true ) ) {
														$search_key          = array_search( $wpssw_val, array_column( $wpssw_product_custom_value, 0 ), true );
														$wpssw_search_array  = $wpssw_product_custom_value[ $search_key ];
														$wpssw_search_keyval = implode( ',(static_header),', $wpssw_search_array );
														?>
														<input type="checkbox" name="wpssw_static_product_header_values[]" value="<?php echo esc_attr( $wpssw_search_keyval ); ?>" hidden="true" checked>
															<?php
													}
													?>
													<input type="checkbox" name="wooproduct_header_list[]" value="<?php echo esc_attr( $wpssw_val ); ?>" id="woo-<?php echo esc_attr( $wpssw_labelid ); ?>" class="woo-pro-headers-chk" <?php echo esc_attr( $wpssw_woo_is_checked ); ?>>
													<?php if ( $wpssw_display ) { ?>
													<span class="checkbox-switch-new"></span>
													<?php } ?>
													<span class="ui-icon ui-icon-caret-2-n-s wpssw-tooltio-link">
														<svg width="8" height="12" viewBox="0 0 8 12" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M1.95875 11.67C1.55759 11.67 1.21418 11.5272 0.928508 11.2415C0.642836 10.9558 0.5 10.6124 0.5 10.2113C0.5 9.81009 0.642836 9.46668 0.928508 9.18101C1.21418 8.89534 1.55759 8.7525 1.95875 8.7525C2.35991 8.7525 2.70332 8.89534 2.98899 9.18101C3.27466 9.46668 3.4175 9.81009 3.4175 10.2113C3.4175 10.6124 3.27466 10.9558 2.98899 11.2415C2.70332 11.5272 2.35991 11.67 1.95875 11.67ZM6.335 11.67C5.93384 11.67 5.59043 11.5272 5.30476 11.2415C5.01909 10.9558 4.87625 10.6124 4.87625 10.2113C4.87625 9.81009 5.01909 9.46668 5.30476 9.18101C5.59043 8.89534 5.93384 8.7525 6.335 8.7525C6.73616 8.7525 7.07957 8.89534 7.36524 9.18101C7.65091 9.46668 7.79375 9.81009 7.79375 10.2113C7.79375 10.6124 7.65091 10.9558 7.36524 11.2415C7.07957 11.5272 6.73616 11.67 6.335 11.67ZM1.95875 7.29375C1.55759 7.29375 1.21418 7.15091 0.928508 6.86524C0.642836 6.57957 0.5 6.23616 0.5 5.835C0.5 5.43384 0.642836 5.09043 0.928508 4.80476C1.21418 4.51909 1.55759 4.37625 1.95875 4.37625C2.35991 4.37625 2.70332 4.51909 2.98899 4.80476C3.27466 5.09043 3.4175 5.43384 3.4175 5.835C3.4175 6.23616 3.27466 6.57957 2.98899 6.86524C2.70332 7.15091 2.35991 7.29375 1.95875 7.29375ZM6.335 7.29375C5.93384 7.29375 5.59043 7.15091 5.30476 6.86524C5.01909 6.57957 4.87625 6.23616 4.87625 5.835C4.87625 5.43384 5.01909 5.09043 5.30476 4.80476C5.59043 4.51909 5.93384 4.37625 6.335 4.37625C6.73616 4.37625 7.07957 4.51909 7.36524 4.80476C7.65091 5.09043 7.79375 5.43384 7.79375 5.835C7.79375 6.23616 7.65091 6.57957 7.36524 6.86524C7.07957 7.15091 6.73616 7.29375 6.335 7.29375ZM1.95875 2.9175C1.55759 2.9175 1.21418 2.77466 0.928508 2.48899C0.642836 2.20332 0.5 1.85991 0.5 1.45875C0.5 1.05759 0.642836 0.71418 0.928508 0.428508C1.21418 0.142836 1.55759 0 1.95875 0C2.35991 0 2.70332 0.142836 2.98899 0.428508C3.27466 0.71418 3.4175 1.05759 3.4175 1.45875C3.4175 1.85991 3.27466 2.20332 2.98899 2.48899C2.70332 2.77466 2.35991 2.9175 1.95875 2.9175ZM6.335 2.9175C5.93384 2.9175 5.59043 2.77466 5.30476 2.48899C5.01909 2.20332 4.87625 1.85991 4.87625 1.45875C4.87625 1.05759 5.01909 0.71418 5.30476 0.428508C5.59043 0.142836 5.93384 0 6.335 0C6.73616 0 7.07957 0.142836 7.36524 0.428508C7.65091 0.71418 7.79375 1.05759 7.79375 1.45875C7.79375 1.85991 7.65091 2.20332 7.36524 2.48899C7.07957 2.77466 6.73616 2.9175 6.335 2.9175Z" fill="#64748B"/>
</svg>
<span class="tooltip-text">Drag & Drop</span>

													</span>
												</span>
												</label>
											</li>
													<?php
												}
											}
											if ( ! empty( $wpssw_wooproduct_headers ) ) {
												foreach ( $wpssw_wooproduct_headers as $wpssw_key => $wpssw_val ) {
													$wpssw_woo_is_checked = '';
													if ( in_array( $wpssw_val, $wpssw_woo_selections, true ) ) {
														continue;
													}
													$wpssw_labelid = strtolower( str_replace( ' ', '_', $wpssw_val ) );
													?>
													<li class="default-order-sheet ui-state-default">
														<label for="woo-<?php echo esc_attr( $wpssw_labelid ); ?>">
														<span class="orderSheet-left">
															<span class="wootextfield"><?php echo esc_attr( $wpssw_val ); ?></span>
															<?php if ( $wpssw_display ) { ?>
															<span class="ui-icon ui-icon-pencil wpssw-tooltio-link">
																<span class="pencil-icon">
																<svg width="12" height="12" viewBox="0 0 13 13" fill="none" xmlns="http://www.w3.org/2000/svg">
																<path d="M1.33333 11.6668H2.26667L8.01667 5.91677L7.08333 4.98343L1.33333 10.7334V11.6668ZM10.8667 4.9501L8.03333 2.1501L8.96667 1.21677C9.22222 0.961213 9.53611 0.833435 9.90833 0.833435C10.2806 0.833435 10.5944 0.961213 10.85 1.21677L11.7833 2.1501C12.0389 2.40566 12.1722 2.71399 12.1833 3.0751C12.1944 3.43621 12.0722 3.74455 11.8167 4.0001L10.8667 4.9501ZM9.9 5.93343L2.83333 13.0001H0V10.1668L7.06667 3.1001L9.9 5.93343Z" fill="#64748B"/>
																</svg>
																</span>
																<span class="check-icon">
																	<svg xmlns="http://www.w3.org/2000/svg"  viewBox="0 0 26 26" width="12" height="12">
																		<path d="M 22.566406 4.730469 L 20.773438 3.511719 C 20.277344 3.175781 19.597656 3.304688 19.265625 3.796875 L 10.476563 16.757813 L 6.4375 12.71875 C 6.015625 12.296875 5.328125 12.296875 4.90625 12.71875 L 3.371094 14.253906 C 2.949219 14.675781 2.949219 15.363281 3.371094 15.789063 L 9.582031 22 C 9.929688 22.347656 10.476563 22.613281 10.96875 22.613281 C 11.460938 22.613281 11.957031 22.304688 12.277344 21.839844 L 22.855469 6.234375 C 23.191406 5.742188 23.0625 5.066406 22.566406 4.730469 Z" fill="#64748B"/></svg>
																</span>
																<span class="tooltip-text edit-tooltip">Edit</span>
																<span class="tooltip-text update-tooltip">Update</span>
															</span>
															<?php } ?>
															</span>
															<span class="orderSheet-right">
															<input type="checkbox" name="wooproduct_custom[]" value="<?php echo esc_attr( $wpssw_val ); ?>" class="woo-pro-headers-chk1" <?php echo esc_attr( $wpssw_woo_is_checked ); ?> hidden="true">
															<input type="checkbox" name="wooproduct_header_list[]" value="<?php echo esc_attr( $wpssw_val ); ?>" id="woo-<?php echo esc_attr( $wpssw_labelid ); ?>" class="woo-pro-headers-chk" <?php echo esc_attr( $wpssw_woo_is_checked ); ?>>
															<span class="checkbox-switch-new"></span>
															<span class="ui-icon ui-icon-caret-2-n-s wpssw-tooltio-link">
																<svg width="8" height="12" viewBox="0 0 8 12" fill="none" xmlns="http://www.w3.org/2000/svg">
																	<path d="M1.95875 11.67C1.55759 11.67 1.21418 11.5272 0.928508 11.2415C0.642836 10.9558 0.5 10.6124 0.5 10.2113C0.5 9.81009 0.642836 9.46668 0.928508 9.18101C1.21418 8.89534 1.55759 8.7525 1.95875 8.7525C2.35991 8.7525 2.70332 8.89534 2.98899 9.18101C3.27466 9.46668 3.4175 9.81009 3.4175 10.2113C3.4175 10.6124 3.27466 10.9558 2.98899 11.2415C2.70332 11.5272 2.35991 11.67 1.95875 11.67ZM6.335 11.67C5.93384 11.67 5.59043 11.5272 5.30476 11.2415C5.01909 10.9558 4.87625 10.6124 4.87625 10.2113C4.87625 9.81009 5.01909 9.46668 5.30476 9.18101C5.59043 8.89534 5.93384 8.7525 6.335 8.7525C6.73616 8.7525 7.07957 8.89534 7.36524 9.18101C7.65091 9.46668 7.79375 9.81009 7.79375 10.2113C7.79375 10.6124 7.65091 10.9558 7.36524 11.2415C7.07957 11.5272 6.73616 11.67 6.335 11.67ZM1.95875 7.29375C1.55759 7.29375 1.21418 7.15091 0.928508 6.86524C0.642836 6.57957 0.5 6.23616 0.5 5.835C0.5 5.43384 0.642836 5.09043 0.928508 4.80476C1.21418 4.51909 1.55759 4.37625 1.95875 4.37625C2.35991 4.37625 2.70332 4.51909 2.98899 4.80476C3.27466 5.09043 3.4175 5.43384 3.4175 5.835C3.4175 6.23616 3.27466 6.57957 2.98899 6.86524C2.70332 7.15091 2.35991 7.29375 1.95875 7.29375ZM6.335 7.29375C5.93384 7.29375 5.59043 7.15091 5.30476 6.86524C5.01909 6.57957 4.87625 6.23616 4.87625 5.835C4.87625 5.43384 5.01909 5.09043 5.30476 4.80476C5.59043 4.51909 5.93384 4.37625 6.335 4.37625C6.73616 4.37625 7.07957 4.51909 7.36524 4.80476C7.65091 5.09043 7.79375 5.43384 7.79375 5.835C7.79375 6.23616 7.65091 6.57957 7.36524 6.86524C7.07957 7.15091 6.73616 7.29375 6.335 7.29375ZM1.95875 2.9175C1.55759 2.9175 1.21418 2.77466 0.928508 2.48899C0.642836 2.20332 0.5 1.85991 0.5 1.45875C0.5 1.05759 0.642836 0.71418 0.928508 0.428508C1.21418 0.142836 1.55759 0 1.95875 0C2.35991 0 2.70332 0.142836 2.98899 0.428508C3.27466 0.71418 3.4175 1.05759 3.4175 1.45875C3.4175 1.85991 3.27466 2.20332 2.98899 2.48899C2.70332 2.77466 2.35991 2.9175 1.95875 2.9175ZM6.335 2.9175C5.93384 2.9175 5.59043 2.77466 5.30476 2.48899C5.01909 2.20332 4.87625 1.85991 4.87625 1.45875C4.87625 1.05759 5.01909 0.71418 5.30476 0.428508C5.59043 0.142836 5.93384 0 6.335 0C6.73616 0 7.07957 0.142836 7.36524 0.428508C7.65091 0.71418 7.79375 1.05759 7.79375 1.45875C7.79375 1.85991 7.65091 2.20332 7.36524 2.48899C7.07957 2.77466 6.73616 2.9175 6.335 2.9175Z" fill="#64748B"/>
																</svg>
																<span class="tooltip-text">Drag & Drop</span>
															</span>
														</span>
														</label>
													</li>
													<?php
												}
											}
											?>
										</ul>
									</div>
								</div>
							</div>
						</div>
						<div class="generalSetting-section prd_spreadsheet_row">
							<div scope="row" class="titledesc generalSetting-left">
								<h4 for="custom_product_header_action"><?php echo esc_html__( 'Custom Static Headers', 'wpssw' ); ?></h4>
								<p><?php echo esc_html__( 'You can enable this option to include your preferred custom headers for further customization. With the "Add" button, you can effortlessly add static header-value pairs to the list.', 'wpssw' ); ?></p>
								<div class="custom-product-input-div custom-input-div-all">
									<input class="input-text" type="text" name="custom_product_headers_val" id="custom_product_headers_val" placeholder="Enter Header Name"><br><br>
									<select name="custom_product_headers_val_dropdown" id="custom_product_headers_val_dropdown">
										<option value="blank">Blank</option>
									</select>
									<button class="wpssw-button wpssw-button-secondary" type="button" id='add_product_ctm_val'><?php echo esc_html__( 'Add', 'wpssw' ); ?></button>
								</div> 
							</div>
							<div class="forminp wpsswpd15 generalSetting-right">              
								<label for="custom_product_header_action">
									<input name="custom_product_header_action" id="custom_product_header_action" type="checkbox" class="" value="1"><span class="checkbox-switch"></span> 							
								</label>
							</div>
						</div>
						<div valign="top" id="product_categories_as_sheet_row" class="prd_spreadsheet_row checkbox_margin generalSetting-section" >
							<div>
								<div class="checkbox-setting-row">
									<div scope="row" class="titledesc generalSetting-left">
										<h4><?php esc_html_e( 'Product Categories as Sheets', 'wpssw' ); ?></h4>
										<p><?php esc_html_e( 'This setting lets you create product categories as sheet within your existing spreadsheet, where you can manage all your store product data.', 'wpssw' ); ?></p>
									</div>
									<div class="forminp generalSetting-right">      
										<label for="product_categories_as_sheet" class="wpssw-ajax-checkbox-row">
											<img class="wpssw-loader" src="<?php dirname( __FILE__ ); ?>images/spinner.gif"  style="display: none;">
											<input name="product_categories_as_sheet" id="product_categories_as_sheet" type="checkbox" class="" value="1" 
											<?php
											if ( 'yes' === (string) $wpssw_product_categories_as_sheet ) {
												echo 'checked="checked"';}
											?>
											><span class="checkbox-switch"></span><span class="checkbox-switch"></span> 
										</label> 
									</div>
								</div>
								<br>
								<div class="checkbox-setting-row include-child-categories-row" 
								<?php
								if ( 'yes' !== (string) $wpssw_product_categories_as_sheet ) {
									echo "style='display:none;'"; }
								?>
								>
									<div scope="row" class="titledesc generalSetting-left">
										<h4><?php esc_html_e( 'Include Child Categories', 'wpssw' ); ?></h4>
										<p><?php esc_html_e( 'This setting will include child categories product data to the parent category sheet on sync.', 'wpssw' ); ?></p>
									</div>
									<div class="forminp generalSetting-right">      
										<label for="include_child_categories" class="wpssw-ajax-checkbox-row">
											<input name="include_child_categories" id="include_child_categories" type="checkbox" class="" value="1" 
											<?php
											if ( 'yes' === (string) $wpssw_include_child_categories ) {
												echo 'checked="checked"';}
											?>
											><span class="checkbox-switch"></span><span class="checkbox-switch"></span> 
										</label> 
									</div>
								</div>
								<?php WPSSW_Product::wpssw_get_product_categories_as_sheet( 1 ); ?>
							</div>
						</div>
						<?php
						if ( ! empty( $wpssw_prdsheet_id ) && ! empty( $wpssw_productsheet_name ) ) {
							?>
							<div valign="top" id="prodsynctr" class="prd_spreadsheet_row checkbox_margin generalSetting-section" >
								<div class="titledesc generalSetting-left">
									<h4>
										<span class="wpssw-tooltio-link tooltip-right">
											<?php echo esc_html__( 'Sync Products', 'wpssw' ); ?>
											<span class="tooltip-text">Export</span>
										</span>
									</h4>
									<p><?php echo esc_html__( 'Add new products to your spreadsheet without modifying existing ones by clicking the "Click to Sync" button. This will automatically append all products in the selected date range not already in the sheet.', 'wpssw' ); ?></p>
									<div class="prd-lang-sync-section">
										<?php
											$is_wpml_sitepress_active = WPSSW_Product::wpssw_is_wpml_sitepress_active();
										?>

										<input type="hidden" name="wpssw_wpml_is_active" value="<?php echo esc_attr( $is_wpml_sitepress_active ); ?>" id="wpssw_wpml_is_active">
										<?php
										if ( $is_wpml_sitepress_active ) {

											$languages   = apply_filters( 'wpml_active_languages', null, 'orderby=id&order=desc' );
											$total_langs = ( is_null( $languages ) || empty( $languages ) ) ? 0 : ( is_array( $languages ) ? count( $languages ) : 0 );
											?>
										<input type="hidden" name="wpssw_wpml_total_langs" value="<?php echo esc_attr( $total_langs ); ?>" id="wpssw_wpml_total_langs">
											<?php
											if ( $total_langs > 0 ) {
													$wpssw_prd_sync_lang_all     = self::wpssw_option( 'wpssw_prd_sync_lang_all' );
													$wpssw_prd_sync_custom_langs = self::wpssw_option( 'wpssw_prd_sync_custom_langs' );
												if ( ! $wpssw_prd_sync_custom_langs ) {
													$wpssw_prd_sync_custom_langs = array();
												}
												?>
									<div valign="top" id="prodlangsynctr" class="prd_spreadsheet_row checkbox_margin">
										<p><strong><?php echo esc_html__( 'Languages', 'wpssw' ); ?></strong> 
												<?php echo esc_html__( ' - Select option to sync all or specific languages products to the sheet.', 'wpssw' ); ?></p>
										<div class="lang-syncall-radio td-radio-btn-row all_languages-radio">
											<div class="lang-syncall-radio-box">
												<input type="radio" name="prd_sync_lang_all" value="1" id="prd_sync_lang_all" 
												<?php
												if ( 1 === (int) $wpssw_prd_sync_lang_all || ( ! is_numeric( $wpssw_prd_sync_lang_all ) && '' === (string) $wpssw_prd_sync_lang_all ) ) {
													echo 'checked';}
												?>
												>
												<label for="prd_sync_lang_all"><?php echo esc_html__( 'All Languages', 'wpssw' ); ?></label>
											</div>
											<div class="lang-syncall-radio-box">
												<input type="radio" name="prd_sync_lang_all" value="0" id="prd_sync_lang_custom" 
												<?php
												if ( is_numeric( $wpssw_prd_sync_lang_all ) && 0 === (int) $wpssw_prd_sync_lang_all ) {
													echo 'checked';}
												?>
												>
												<label for="prd_sync_lang_custom"><?php echo esc_html__( 'Custom', 'wpssw' ); ?></label>
											</div>
										</div>
									</div>
									<div class="sheetHeaders-section">
										<div class="prd_sync_language_options">
												<ul id="prd_wpml_language_options" class="sheetHeaders-box">
													<?php
													foreach ( $languages as $lang_code => $lang ) {
														$check_lang = '';
														if ( in_array( $lang_code, $wpssw_prd_sync_custom_langs, true ) ) {
															$check_lang = 'checked';
														}
														?>
													<li class="default-order-sheet ui-state-default">
														<label for="<?php echo esc_html( 'wpssw_wpml_' . $lang_code ); ?>">
															<span class="orderSheet-left">
															<?php echo esc_html( $lang['translated_name'] ); ?>
														</span>
															<span class="orderSheet-right">
															<input type="checkbox" name="productlang_filter[]" value="<?php echo esc_attr( $lang_code ); ?>" id="<?php echo esc_attr( 'wpssw_wpml_' . $lang_code ); ?>" class="sync_custom_langs" <?php echo esc_attr( $check_lang ); ?>>
															<span class="checkbox-switch-new"></span>
															</span>
															</span>
														</label>
													</li>
												<?php } ?>
												</ul>
										</div>
										</div>
												<?php
											}
										}
										?>


									</div>
									<div class="sync_all_fromtodate-main">
										<div class="syncall-radio">
											<div class="syncall-radio-box syncall-radio-allProducts">
												<input type="radio" name="prd_sync_all_checkbox" value="1" id="prd_sync_all" checked="checked">
												<label for="prd_sync_all"><?php echo esc_html__( 'All Products', 'wpssw' ); ?></label>
											</div>
											<div class="syncall-radio-box">
												<input type="radio" name="prd_sync_all_checkbox" value="0" id="prd_sync_daterange">
												<label for="prd_sync_daterange"><?php echo esc_html__( 'Date Range', 'wpssw' ); ?></label>
											</div>
											<div class="syncall-radio-box">
												<input type="radio" name="prd_sync_all_checkbox" value="sale-date-range" id="prd_sync_sale_daterange">
												<label for="prd_sync_sale_daterange"><?php echo esc_html__( 'Product Sale Date Range', 'wpssw' ); ?></label>
											</div>
										</div>
									<div class="forminp forminp-select prd_sync_all_fromtodate"> 
										<label for="prd_sync_all_fromdate" >
											<?php echo esc_html__( 'From :', 'wpssw' ); ?>   <input name="prd_sync_all_fromdate" id="prd_sync_all_fromdate" class="prd_sync_all_fromdate" type="date" >
										</label>
										<label for="prd_sync_all_todate" class="prd_sync_todate_label sync_todate_label_all">
											<?php echo esc_html__( 'To :', 'wpssw' ); ?> <input name="prd_sync_all_todate" id="prd_sync_all_todate" class="prd_sync_all_todate" type="date" >
										</label>
									</div>
									<div class="forminp forminp-select prd_sync_all_sale_fromtodate"> 
										<label for="prd_sync_all_sale_fromdate" >
											<?php echo esc_html__( 'Product Sale From Date:', 'wpssw' ); ?>   <input name="prd_sync_all_sale_fromdate" id="prd_sync_all_sale_fromdate" class="prd_sync_all_sale_fromdate" type="date" >
										</label>
										<label for="prd_sync_all_sale_todate" class="prd_sync_todate_label sync_todate_label_all">
											<?php echo esc_html__( 'Product Sale To Date:', 'wpssw' ); ?> <input name="prd_sync_all_sale_todate" id="prd_sync_all_sale_todate" class="prd_sync_all_sale_todate" type="date" >
										</label>
									</div>
									</div>
									<div class="sync-button-box">
										<img src="images/spinner.gif" id="prodsyncloader">
										<span id="prodsynctext"><?php echo esc_html__( 'Synchronizing...', 'wpssw' ); ?></span>
										<a class="wpssw-button wpssw-button-secondary" href="javascript:void(0)" id="prodsync">
											<?php echo esc_html__( 'Click to Sync', 'wpssw' ); ?>
										</a>
									</div>
						</div>
						</div>
							<?php
						}
							self::wpssw_auto_sync_setting(
								'product',
								array(
									'id'    => 'prodautosynctr',
									'class' => 'prd_spreadsheet_row',
								)
							);
					?>
						<div valign="top" id="import_checkbox_row" class="prd_spreadsheet_row prd-import-section-row import-section-row checkbox_margin generalSetting-section" >
							<div>
								<div class="ord-import-section import-section">
									<div class="titledesc generalSetting-left">
										<h4>
											<span class="wpssw-tooltio-link tooltip-right">
												<?php esc_html_e( 'Import Products', 'wpssw' ); ?>
												<span class="tooltip-text">Import</span>
											</span>
										</h4>
										<p>
										<?php
										esc_html_e(
											'Easily import products from your spreadsheet to this site. Remember to take a database backup before clicking the import button as a precautionary measure. Also, use the "m/d/Y" date format while importing. Preview:-',
											'wpssw'
										);
										$timezone = wp_timezone_string();
											echo esc_html( date_format( date_create( 'now', timezone_open( $timezone ) ), 'm/d/Y' ) );
										?>
										<a href="<?php echo esc_url( 'https://docs.wpsyncsheets.com/how-to-import-products/' ); ?>" target="_blank"><?php echo esc_html__( 'How to Import Products?', 'wpssw' ); ?></a></p>
									</div>
									<div class="forminp generalSetting-right">      
										<label for="import_checkbox">
											<input name="import_checkbox" id="import_checkbox" type="checkbox" class="" value="1" 
											<?php
											if ( $wpssw_product_import ) {
												echo 'checked="checked"';}
											?>
											><span class="checkbox-switch"></span><span class="checkbox-switch"></span> 		
										</label> 
									</div>
								</div>
								<div>
								<div class="generalSetting-section prd_import_options_row import_options_row">
									<div valign="top" id="insert_checkbox_row" class="prd_spreadsheet_row wpssw_crud_row checkbox_margin wpssw_all_crud_row" >
										<div class="titledesc generalSetting-left">
											<label><?php esc_html_e( 'Insert Product', 'wpssw' ); ?></label>
										</div>
										<div class="forminp generalSetting-right inside-checkbox">    
											<label for="insert_checkbox">
												<input name="insert_checkbox" id="insert_checkbox" type="checkbox" class="" value="1" 
												<?php
												if ( $wpssw_product_insert ) {
													echo 'checked="checked"';}
												?>
												/><span class="checkbox-switch-new"></span>
											</label> 
										</div>
									</div>
									<div valign="top" id="update_checkbox_row" class="prd_spreadsheet_row wpssw_crud_row checkbox_margin wpssw_all_crud_row" >
										<div class="titledesc generalSetting-left">
											<label><?php esc_html_e( 'Update Product', 'wpssw' ); ?></label>
										</div>
										<div class="forminp generalSetting-right inside-checkbox">    
											<label for="update_checkbox">
												<input name="update_checkbox" id="update_checkbox" type="checkbox" class="" value="1" 
												<?php
												if ( $wpssw_product_update ) {
													echo 'checked="checked"';}
												?>
												><span class="checkbox-switch-new"></span>
											</label> 
										</div>
									</div>
									<div valign="top" id="delete_checkbox_row" class="prd_spreadsheet_row wpssw_crud_row checkbox_margin wpssw_all_crud_row" >
										<div scope="row" class="titledesc generalSetting-left">
											<label><?php esc_html_e( 'Delete Product', 'wpssw' ); ?></label>
										</div>
										<div class="forminp generalSetting-right inside-checkbox">     
											<label for="delete_checkbox">
												<input name="delete_checkbox" id="delete_checkbox" type="checkbox" class="" value="1" 
												<?php
												if ( $wpssw_product_delete ) {
													echo 'checked="checked"';}
												?>
												><span class="checkbox-switch-new"></span>
											</label> 
										</div>
									</div>
								</div>
									<?php if ( ( ! empty( $wpssw_prdsheet_id ) && ! empty( $wpssw_productsheet_name ) ) && ( 1 === (int) $wpssw_product_insert || 1 === (int) $wpssw_product_update || 1 === (int) $wpssw_product_delete ) ) { ?>
								<div valign="top" class="wpssw_crud_row prd_spreadsheet_row generalSetting-section prd_import_row import_row">
									<div scope="row" class="titledesc generalSetting-left"> 
										<img src="images/spinner.gif" id="importsyncloader">
										<span id="importsynctext"><?php esc_html_e( 'Checking...', 'wpssw' ); ?></span>
										<a class="wpssw-button wpssw-button-secondary" href="javascript:void(0)" id="importsync"><?php echo esc_html__( 'Import Product', 'wpssw' ); ?></a>
										<br />
										<a class="wpssw-button wpssw-button-secondary" href="javascript:void(0)" id="importsyncbtm"><?php esc_html_e( 'Proceed', 'wpssw' ); ?></a>
										<a class="wpssw-button wpssw-button-secondary" href="javascript:void(0)" id="cancelsyncbtm"><?php esc_html_e( 'Cancel', 'wpssw' ); ?></a>
									</div>
								</div>
								<?php } ?>
								</div>
							</div>
						</div>
							<?php
							if ( 1 === (int) $wpssw_product_insert || 1 === (int) $wpssw_product_update || 1 === (int) $wpssw_product_delete ) {

								self::wpssw_auto_sync_setting(
									'product',
									array(
										'id'    => 'prodimporttr',
										'class' => 'wpssw_crud_row prd_spreadsheet_row',
									),
									'import'
								);
							}
							?>
						<!-- Attribute -->
						<div valign="top" id="import_attribute_checkbox_row" class="prd_spreadsheet_row checkbox_margin generalSetting-section" >
							<div scope="row" class="titledesc generalSetting-left">
								<h4><?php esc_html_e( 'Import Product Attributes', 'wpssw' ); ?></h4>
								<p><?php esc_html_e( 'This feature will create a dedicated "Product Attributes" sheet within your current spreadsheet, where you can conveniently store product attribute data.', 'wpssw' ); ?></p>
							</div>
							<div class="forminp generalSetting-right">      
								<label for="import_attribute_checkbox">
									<input name="import_attribute_checkbox" id="import_attribute_checkbox" type="checkbox" class="" value="1" 
									<?php
									if ( $wpssw_import_attribute_checkbox ) {
										echo 'checked="checked"';}
									?>
									><span class="checkbox-switch"></span><span class="checkbox-switch"></span> 		
								</label> 
							</div>
						</div>
						<?php if ( ( ! empty( $wpssw_prdsheet_id ) && ! empty( $wpssw_productsheet_name ) ) && $wpssw_import_attribute_checkbox ) { ?>
						<div valign="top" id="attrsynctr" class="prd_spreadsheet_row generalSetting-section" >
							<div scope="row" class="titledesc generalSetting-left">
								<h4><?php echo esc_html__( 'Sync Attributes', 'wpssw' ); ?></h4>
								<p><?php echo esc_html__( 'The "Click to Sync" button will append all product attributess that are not present in the sheet, without updating the existing attributes.', 'wpssw' ); ?></p>
							</div>
							<div class="forminp generalSetting-right right-buttons-section">              
								<img src="images/spinner.gif" id="attrsyncloader">
								<span id="attrsynctext"><?php echo esc_html__( 'Synchronizing...', 'wpssw' ); ?></span>
								<a class="wpssw-button  wpssw-button-secondary" href="javascript:void(0)" id="attrsync">
									<?php echo esc_html__( 'Click to Sync', 'wpssw' ); ?>
								</a>
							</div>
						</div>
						<div valign="top" id="attrsynctr" class="prd_spreadsheet_row generalSetting-section" >
							<div scope="row" class="titledesc generalSetting-left">
								<h4><?php echo esc_html__( 'Import Attributes', 'wpssw' ); ?></h4>
								<p><?php echo esc_html__( 'The "Import Attribute" button will import your attributes from sheet to this site.', 'wpssw' ); ?> <a href="<?php echo esc_url( 'https://docs.wpsyncsheets.com/how-to-import-attributes/' ); ?>" target="_blank"><?php echo esc_html__( 'How to Import Attributes?', 'wpssw' ); ?></a></p>
							</div>
							<div class="forminp generalSetting-right right-buttons-section">              
								<img src="images/spinner.gif" id="importattrsyncloader">
								<span id="importattrsynctext"><?php echo esc_html__( 'Importing...', 'wpssw' ); ?></span>
								<a class="wpssw-button  wpssw-button-secondary" href="javascript:void(0)" id="importattrsync">
									<?php echo esc_html__( 'Import Attributes', 'wpssw' ); ?></a>
								<br>
								<a class="wpssw-button  wpssw-button-secondary" href="javascript:void(0)" id="importattrsyncbtm"><?php esc_html_e( 'Proceed', 'wpssw' ); ?></a>
								<a class="wpssw-button wpssw-button-secondary" href="javascript:void(0)" id="cancelattrsyncbtm"><?php esc_html_e( 'Cancel', 'wpssw' ); ?></a>
							</div>
						</div>
						<?php } ?>
						<!-- End Attribute -->
						<?php
						$freeze_header = self::wpssw_option( 'freeze_header' );
						// @codingStandardsIgnoreStart.
						if ( ! $freeze_header ) {
							/*
							$prd_freeze_header = self::wpssw_option( 'prd_freeze_header' );
							?>
							<div valign="top" id="prd_freeze_header_row" class="prd_spreadsheet_row checkbox_margin generalSetting-section" >
							<div scope="row" class="titledesc generalSetting-left">
								<h4><?php echo esc_html__( 'Freeze Header', 'wpssw' ); ?></h4>
								<p><?php echo esc_html__( 'Enable this option to freeze the first row i.e. the header (title) row which will remain on top when scrolling down on the sheet.', 'wpssw' ); ?></p>
							</div>
							<div class="generalSetting-right">
								<label for="prd_freeze_header">
									<input name="prd_freeze_header" id="prd_freeze_header" type="checkbox" class="" value="yes"
									<?php
									if ( 'yes' === (string) $prd_freeze_header ) {
										echo 'checked';}
									?>
									><span class="checkbox-switch"></span>
								</label>
							</div>
							</div>
							<?php
							*/
						}
						?>
						<?php
						$wpssw_inputoption = self::wpssw_option( 'wpssw_inputoption' );
						if ( ! $wpssw_inputoption ) {
							/*
							$wpssw_prd_inputoption = self::wpssw_option( 'wpssw_prd_inputoption' );

							$wpssw_user = '';
							$wpssw_raw  = '';
							if ( 'RAW' === (string) $wpssw_prd_inputoption ) {
								$wpssw_raw = 'selected';
							} else {
								$wpssw_user = 'selected';
							}
							?>
							<div valign="top" class="prd_spreadsheet_row checkbox_margin generalSetting-section">
							<div scope="row" class="titledesc generalSetting-left">
								<h4><?php echo esc_html__( 'Row Input Format Option', 'wpssw' ); ?></h4>
								<p><?php echo esc_html__( 'Determines how input data should be interpreted. For More Details', 'wpssw' ); ?><a href="<?php echo esc_url( 'https://developers.google.com/sheets/api/reference/rest/v4/ValueInputOption', 'wpssw' ); ?>" target="_blank"> <?php echo esc_html__( 'click here.', 'wpssw' ); ?></a></p>
							</div>
							<div class="generalSetting-right">
								<label for="prd_inputoption">
									<select name="prd_inputoption">
										<option value="USER_ENTERED" <?php echo esc_attr( $wpssw_user ); ?> >USER ENTERED</option>
										<option value="RAW" <?php echo esc_attr( $wpssw_raw ); ?>>RAW</option>
									</select>
								</label>
							</div>
							</div>
							<?php */
						}
						// @codingStandardsIgnoreEnd.
						$prd_inherit_style = self::wpssw_option( 'wpssw_product_inherit_style' );
						?>
						<div class="inheritStyles generalSetting-section prd_spreadsheet_row">
							<div class="titledesc generalSetting-left">
								<h4><?php echo esc_html__( 'Inherit Styles', 'wpssw' ); ?></h4>
								<p><?php echo esc_html__( 'This dropdown allows you to choose whether the new row should inherit the style from the row above it. The available options are "Yes" to inherit the style, "No" to not inherit the style, or "None" to leave the style inheritance unaffected.', 'wpssw' ); ?>  <a href="<?php echo esc_url( 'https://docs.wpsyncsheets.com/how-to-use-inherit-style-option/' ); ?>" target="_blank"><?php echo esc_html__( 'How to use inherit style option?', 'wpssw' ); ?></a></p>
							</div>
							<div class="forminp generalSetting-right">
								<label for="prd_inherit_style">
									<select name="prd_inherit_style">
										<?php foreach ( $inherit_style_options as $inherit_option ) { ?>
											<option value="<?php echo esc_attr( $inherit_option ); ?>" 
												<?php
												if ( $inherit_option === $prd_inherit_style || ( 'none' === (string) $inherit_option && '' === (string) $prd_inherit_style ) ) {
													echo ' selected';
												}
												?>
											><?php echo esc_html( ucfirst( $inherit_option ) ); ?></option>
										<?php } ?>
									</select>
								</label>
							</div>
						</div>
					<?php
					if ( WPSSW_Dependencies::wpssw_is_woocommerce_active() ) {
						?>
				<div class="submit-section">						
					<p class="submit"><input type="submit" name="submit" id="submit" class="wpssw-button wpssw-button-primary" value="Save"></p>
				</div>
						<?php
					} else {
						?>
						<div class="generalSetting-section generalSetting-section-message">
							<h4><?php echo 'WPSyncSheets For WooCommerce plugin requires <a href="http://wordpress.org/extend/plugins/woocommerce/">WooCommerce</a> plugin to be active!'; ?></h4>
						</div>
						<?php
					}
					?>
				</form>
					<?php
				} else {
					?>
					<div class="generalSetting-section generalSetting-section-message">
						<h4><?php echo esc_html__( 'Please genearate authentication code from', 'wpssw' ); ?>
							<strong><?php echo esc_html__( 'Google API Setting ', 'wpssw' ); ?></strong>
							<a href='<?php echo esc_url( 'admin.php?page=wpsyncsheets-for-woocommerce' ); ?>'> <?php echo esc_html__( 'Click', 'wpssw' ); ?></a>
						</h4>
					</div>
					<?php } ?>
				</div>

				<div id="customer-settings" class="tabcontent">
				<?php
					$wpssw_customer_import              = self::wpssw_option( 'wpssw_customer_import' );
					$wpssw_customer_insert              = self::wpssw_option( 'wpssw_customer_insert' );
					$wpssw_customer_update              = self::wpssw_option( 'wpssw_customer_update' );
					$wpssw_customer_delete              = self::wpssw_option( 'wpssw_customer_delete' );
					$wpssw_customer_spreadsheet_setting = self::wpssw_option( 'wpssw_customer_spreadsheet_setting' );
					$wpssw_customer_spreadsheet_id      = self::wpssw_option( 'wpssw_customer_spreadsheet_id' );
					$wpssw_spreadsheet_id               = self::wpssw_option( 'wpssw_woocommerce_spreadsheet' );
					$wpssw_custsheet_id                 = $wpssw_customer_spreadsheet_id;
					$wpssw_customersheet_name           = '';
					$wpssw_spreadsheets_list            = self::$instance_api->get_spreadsheet_listing();

				if ( self::wpssw_check_sheet_exist( $wpssw_custsheet_id, 'All Customers' ) ) {
					$wpssw_customersheet_name = 'All Customers';
				}

				if ( empty( $wpssw_customer_spreadsheet_id ) ) {
					$wpssw_customer_spreadsheet_id = $wpssw_spreadsheet_id;
				}
					$wpssw_checked = '';
				if ( 'yes' === (string) $wpssw_customer_spreadsheet_setting ) {
					$wpssw_checked = 'checked';
				}
				$wpssw_static_customer_header        = stripslashes_deep( self::wpssw_option( 'wpssw_customer_static_header' ) );
				$wpssw_static_customer_header_values = stripslashes_deep( self::wpssw_option( 'wpssw_static_customer_header_values' ) );
				if ( ! $wpssw_static_customer_header ) {
					$wpssw_static_customer_header = array();
				}
				$wpssw_customer_custom_value = array();
				if ( ! $wpssw_static_customer_header_values ) {
					$wpssw_static_customer_header_values = array();
				}
				if ( ! empty( $wpssw_static_customer_header_values ) ) {
					foreach ( $wpssw_static_customer_header_values as $wpssw_static_customer_header_value ) {
						if ( strpos( $wpssw_static_customer_header_value, ',(static_header),' ) ) {
							$wpssw_static_customer_header_value = str_replace( ',(static_header),', ',', $wpssw_static_customer_header_value );
							$wpssw_customer_custom_value[]      = explode( ',', $wpssw_static_customer_header_value );
						}
					}
				}
				?>
				<?php
				if ( ! empty( $wpssw_google_settings[2] ) ) {
					?>
				<form method="post" action="<?php echo esc_html( admin_url( 'admin.php?page=wpsyncsheets-for-woocommerce&tab=customer-settings' ) ); ?>" id="customerform">
					<?php wp_nonce_field( 'save_customer_settings', 'wpssw_customer_settings' ); ?>
					<input name="customer_sheet" id="customer_sheet" type="hidden" class="" value="<?php echo esc_html( $wpssw_customersheet_name ); ?>">
						<div valign="top" class="checkbox_margin generalSetting-section">
							<div scope="row" class="titledesc generalSetting-left">
								<h4><?php echo esc_html__( 'Customer Settings', 'wpssw' ); ?></h4>
								<p><?php echo esc_html__( 'Enable this option to streamline your customer management process with personalized spreadsheets and an "All Customers" sheet.', 'wpssw' ); ?></p>
							</div>
							<div class="forminp generalSetting-right">              
								<label for="customer_settings_checkbox">
									<input name="customer_settings_checkbox" id="customer_settings_checkbox" type="checkbox" class="" value="1" <?php echo esc_html( $wpssw_checked ); ?>><span class="checkbox-switch"></span><span class="checkbox-switch"></span> 							
								</label>
							</div>
						</div>
						<?php
						$spreadsheets_list = self::$instance_api->get_spreadsheet_listing();
						?>
						<div class="generalSetting-section googleSpreadsheet-section cust_spreadsheet_row">
							<div class="generalSetting-left">
								<h4><?php echo esc_html__( 'Google Spreadsheet Settings', 'wpssw' ); ?></h4>
								<p><?php echo esc_html__( "Once you've assigned a Google Spreadsheet, it will automatically create an 'All Customers' sheet and populate it with headers based on your settings. The spreadsheet will update with new rows whenever new customers are added.", 'wpssw' ); ?></p>
								<div class="createanew-radio">
									<div class="createanew-radio-box">
										<input type="radio" name="custsheetselection" value="new" id="custcreateanew">
										<label for="custcreateanew"><?php echo esc_html__( 'Create New Spreadsheet', 'wpssw' ); ?></label>
									</div>
									<div class="createanew-radio-box">
										<input type="radio" name="custsheetselection" value="existing" id="custexisting" checked="checked">
										<label for="custexisting"><?php echo esc_html__( 'Select Existing Spreadsheet', 'wpssw' ); ?></label>
									</div>
								</div>
								<div id="customer_spreadsheet_container" class="spreadsheet-form">
									<select name="customer_spreadsheet" id="customer_spreadsheet" style="min-width:150px;" class="">
									<?php
									$selected = '';
									foreach ( $spreadsheets_list as $spreadsheetid => $spreadsheetname ) {
										if ( (string) $wpssw_customer_spreadsheet_id === $spreadsheetid ) {
											$selected = 'selected="selected"';
										}
										?>
										<option value="<?php echo esc_attr( $spreadsheetid ); ?>" <?php echo esc_attr( $selected ); ?>><?php echo esc_html( $spreadsheetname ); ?></option>
										<?php
										$selected = '';
									}
									?>
									</select>
								</div>
								<div valign="top" id="cust_newsheet" class="newsheetinput spreadsheet-form cust_spreadsheet_inputrow">
									<input name="customer_spreadsheet_name" id="customer_spreadsheet_name" type="text" class="input-text" value="" placeholder="<?php echo esc_html__( 'Enter Spreadsheet Name', 'wpssw' ); ?>">
								</div>
							</div>
						</div>
						<div class="cust_spreadsheet_row generalSetting-section sheetHeaders-section">
							<div class="td-wpssw-headers">
								<div class="wpssw_headers">
									<div class="generalSetting-sheet-headers-row">
										<div class="generalSetting-left">
											<h4><?php echo esc_html__( 'Sheet Headers', 'wpssw' ); ?></h4>
											<p><?php echo esc_html__( 'Any disabled sheet headers will be automatically removed from the current spreadsheet. To include specific headers, enable them, save the settings, and click the "Click to Sync" button to update the spreadsheet with the latest data.', 'wpssw' ); ?></p>
										</div>
									</div>
									<div class="selectall-btnset">
										<button type="button" class="wpssw-button wpssw-button-primary" id="woo-custselectall"><?php echo esc_html__( 'Select All', 'wpssw' ); ?></button>                
										<button type="button" class="wpssw-button wpssw-button-secondary" id="woo-custselectnone"><?php echo esc_html__( 'Select None', 'wpssw' ); ?></button>
									</div>
									<div class="sheetHeaders-main">
										<ul id="woo-customer-sortable" class="sheetHeaders-box">
											<?php
											$wpssw_woo_is_checked = '';

											$wpssw_woo_selections = stripslashes_deep( self::wpssw_option( 'wpssw_woo_customer_headers' ) );
											if ( ! $wpssw_woo_selections ) {
												$wpssw_woo_selections = array();
											}
											$wpssw_woo_selections_custom = stripslashes_deep( self::wpssw_option( 'wpssw_woo_customer_headers_custom' ) );
											if ( ! $wpssw_woo_selections_custom ) {
												$wpssw_woo_selections_custom = array();
											}
											$wpssw_include = new WPSSW_Include_Action();
											$wpssw_include->wpssw_include_customer_compatibility_files();
											$wpssw_woocustomer_headers = apply_filters( 'wpsyncsheets_customer_headers', array() );
											$wpssw_woocustomer_headers = self::wpssw_array_flatten( $wpssw_woocustomer_headers );
											$wpssw_operation           = array( 'Insert', 'Update', 'Delete' );
											if ( ! empty( $wpssw_woo_selections ) ) {
												foreach ( $wpssw_woo_selections as $wpssw_key => $wpssw_val ) {
													$wpssw_woo_is_checked = 'checked';
													$wpssw_labelid        = strtolower( str_replace( ' ', '_', $wpssw_val ) );
													$wpssw_display        = true;
													$wpssw_classname      = '';
													if ( in_array( $wpssw_val, $wpssw_operation, true ) ) {
														$wpssw_display   = false;
														$wpssw_labelid   = '';
														$wpssw_classname = strtolower( $wpssw_val ) . 'customer';
													}
													?>
											<li class="default-order-sheet ui-state-default <?php echo esc_html( $wpssw_classname ); ?>">
												<label for="woo-<?php echo esc_attr( $wpssw_labelid ); ?>">
													<span class="orderSheet-left">
														<span class="wootextfield"><?php echo isset( $wpssw_woo_selections_custom[ $wpssw_key ] ) ? esc_attr( $wpssw_woo_selections_custom[ $wpssw_key ] ) : esc_attr( $wpssw_val ); ?></span>
														<?php if ( $wpssw_display ) { ?>
														<span class="ui-icon ui-icon-pencil wpssw-tooltio-link">
															<span class="pencil-icon">
																<svg width="12" height="12" viewBox="0 0 13 13" fill="none" xmlns="http://www.w3.org/2000/svg">
																	<path d="M1.33333 11.6668H2.26667L8.01667 5.91677L7.08333 4.98343L1.33333 10.7334V11.6668ZM10.8667 4.9501L8.03333 2.1501L8.96667 1.21677C9.22222 0.961213 9.53611 0.833435 9.90833 0.833435C10.2806 0.833435 10.5944 0.961213 10.85 1.21677L11.7833 2.1501C12.0389 2.40566 12.1722 2.71399 12.1833 3.0751C12.1944 3.43621 12.0722 3.74455 11.8167 4.0001L10.8667 4.9501ZM9.9 5.93343L2.83333 13.0001H0V10.1668L7.06667 3.1001L9.9 5.93343Z" fill="#64748B"/>
																</svg>
															</span>
															<span class="check-icon">
																	<svg xmlns="http://www.w3.org/2000/svg"  viewBox="0 0 26 26" width="12" height="12">
																		<path d="M 22.566406 4.730469 L 20.773438 3.511719 C 20.277344 3.175781 19.597656 3.304688 19.265625 3.796875 L 10.476563 16.757813 L 6.4375 12.71875 C 6.015625 12.296875 5.328125 12.296875 4.90625 12.71875 L 3.371094 14.253906 C 2.949219 14.675781 2.949219 15.363281 3.371094 15.789063 L 9.582031 22 C 9.929688 22.347656 10.476563 22.613281 10.96875 22.613281 C 11.460938 22.613281 11.957031 22.304688 12.277344 21.839844 L 22.855469 6.234375 C 23.191406 5.742188 23.0625 5.066406 22.566406 4.730469 Z" fill="#64748B"/>
																</svg>
															</span>
															<span class="tooltip-text edit-tooltip">Edit</span>
															<span class="tooltip-text update-tooltip">Update</span>
														</span>
														<?php } ?>
													</span>
													<span class="orderSheet-right">
													<input type="checkbox" name="woocustomer_custom[]" value="<?php echo isset( $wpssw_woo_selections_custom[ $wpssw_key ] ) ? esc_attr( $wpssw_woo_selections_custom[ $wpssw_key ] ) : esc_attr( $wpssw_val ); ?>" class="woo-cust-headers-chk1" <?php echo esc_html( $wpssw_woo_is_checked ); ?> hidden="true">

													<?php
													if ( in_array( $wpssw_val, $wpssw_static_customer_header, true ) ) {
														?>
														<input type="checkbox" name="customer_header_fields_static[]" value="<?php echo esc_attr( $wpssw_val ); ?>" hidden="true" checked>
															<?php
													}
													if ( in_array( $wpssw_val, array_column( $wpssw_customer_custom_value, 0 ), true ) ) {
														$search_key          = array_search( $wpssw_val, array_column( $wpssw_customer_custom_value, 0 ), true );
														$wpssw_search_array  = $wpssw_customer_custom_value[ $search_key ];
														$wpssw_search_keyval = implode( ',(static_header),', $wpssw_search_array );
														?>
														<input type="checkbox" name="wpssw_static_customer_header_values[]" value="<?php echo esc_attr( $wpssw_search_keyval ); ?>" hidden="true" checked>
															<?php
													}
													?>

													<input type="checkbox" name="woocustomer_header_list[]" value="<?php echo esc_attr( $wpssw_val ); ?>" id="woo-<?php echo esc_attr( $wpssw_labelid ); ?>" class="woo-cust-headers-chk" <?php echo esc_html( $wpssw_woo_is_checked ); ?>>
													<?php if ( $wpssw_display ) { ?>
													<span class="checkbox-switch-new"></span>
													<?php } ?>
													<span class="ui-icon ui-icon-caret-2-n-s wpssw-tooltio-link">
														<svg width="8" height="12" viewBox="0 0 8 12" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M1.95875 11.67C1.55759 11.67 1.21418 11.5272 0.928508 11.2415C0.642836 10.9558 0.5 10.6124 0.5 10.2113C0.5 9.81009 0.642836 9.46668 0.928508 9.18101C1.21418 8.89534 1.55759 8.7525 1.95875 8.7525C2.35991 8.7525 2.70332 8.89534 2.98899 9.18101C3.27466 9.46668 3.4175 9.81009 3.4175 10.2113C3.4175 10.6124 3.27466 10.9558 2.98899 11.2415C2.70332 11.5272 2.35991 11.67 1.95875 11.67ZM6.335 11.67C5.93384 11.67 5.59043 11.5272 5.30476 11.2415C5.01909 10.9558 4.87625 10.6124 4.87625 10.2113C4.87625 9.81009 5.01909 9.46668 5.30476 9.18101C5.59043 8.89534 5.93384 8.7525 6.335 8.7525C6.73616 8.7525 7.07957 8.89534 7.36524 9.18101C7.65091 9.46668 7.79375 9.81009 7.79375 10.2113C7.79375 10.6124 7.65091 10.9558 7.36524 11.2415C7.07957 11.5272 6.73616 11.67 6.335 11.67ZM1.95875 7.29375C1.55759 7.29375 1.21418 7.15091 0.928508 6.86524C0.642836 6.57957 0.5 6.23616 0.5 5.835C0.5 5.43384 0.642836 5.09043 0.928508 4.80476C1.21418 4.51909 1.55759 4.37625 1.95875 4.37625C2.35991 4.37625 2.70332 4.51909 2.98899 4.80476C3.27466 5.09043 3.4175 5.43384 3.4175 5.835C3.4175 6.23616 3.27466 6.57957 2.98899 6.86524C2.70332 7.15091 2.35991 7.29375 1.95875 7.29375ZM6.335 7.29375C5.93384 7.29375 5.59043 7.15091 5.30476 6.86524C5.01909 6.57957 4.87625 6.23616 4.87625 5.835C4.87625 5.43384 5.01909 5.09043 5.30476 4.80476C5.59043 4.51909 5.93384 4.37625 6.335 4.37625C6.73616 4.37625 7.07957 4.51909 7.36524 4.80476C7.65091 5.09043 7.79375 5.43384 7.79375 5.835C7.79375 6.23616 7.65091 6.57957 7.36524 6.86524C7.07957 7.15091 6.73616 7.29375 6.335 7.29375ZM1.95875 2.9175C1.55759 2.9175 1.21418 2.77466 0.928508 2.48899C0.642836 2.20332 0.5 1.85991 0.5 1.45875C0.5 1.05759 0.642836 0.71418 0.928508 0.428508C1.21418 0.142836 1.55759 0 1.95875 0C2.35991 0 2.70332 0.142836 2.98899 0.428508C3.27466 0.71418 3.4175 1.05759 3.4175 1.45875C3.4175 1.85991 3.27466 2.20332 2.98899 2.48899C2.70332 2.77466 2.35991 2.9175 1.95875 2.9175ZM6.335 2.9175C5.93384 2.9175 5.59043 2.77466 5.30476 2.48899C5.01909 2.20332 4.87625 1.85991 4.87625 1.45875C4.87625 1.05759 5.01909 0.71418 5.30476 0.428508C5.59043 0.142836 5.93384 0 6.335 0C6.73616 0 7.07957 0.142836 7.36524 0.428508C7.65091 0.71418 7.79375 1.05759 7.79375 1.45875C7.79375 1.85991 7.65091 2.20332 7.36524 2.48899C7.07957 2.77466 6.73616 2.9175 6.335 2.9175Z" fill="#64748B"/>
</svg>

														<span class="tooltip-text">Drag & Drop</span>
													</span>
												</span>
												</label>
											</li>
													<?php
												}
											}
											if ( ! empty( $wpssw_woocustomer_headers ) ) {
												foreach ( $wpssw_woocustomer_headers as $wpssw_key => $wpssw_val ) {
													$wpssw_woo_is_checked = '';
													if ( in_array( $wpssw_val, $wpssw_woo_selections, true ) ) {
														continue;
													}
													$wpssw_labelid = strtolower( str_replace( ' ', '_', $wpssw_val ) );
													?>
											<li class="default-order-sheet ui-state-default">
												<label for="woo-<?php echo esc_attr( $wpssw_labelid ); ?>">
													<span class="orderSheet-left">
														<span class="wootextfield"><?php echo esc_html( $wpssw_val ); ?></span>
														<span class="ui-icon ui-icon-pencil wpssw-tooltio-link">
															<span class="pencil-icon">
														<svg width="12" height="12" viewBox="0 0 13 13" fill="none" xmlns="http://www.w3.org/2000/svg">
														<path d="M1.33333 11.6668H2.26667L8.01667 5.91677L7.08333 4.98343L1.33333 10.7334V11.6668ZM10.8667 4.9501L8.03333 2.1501L8.96667 1.21677C9.22222 0.961213 9.53611 0.833435 9.90833 0.833435C10.2806 0.833435 10.5944 0.961213 10.85 1.21677L11.7833 2.1501C12.0389 2.40566 12.1722 2.71399 12.1833 3.0751C12.1944 3.43621 12.0722 3.74455 11.8167 4.0001L10.8667 4.9501ZM9.9 5.93343L2.83333 13.0001H0V10.1668L7.06667 3.1001L9.9 5.93343Z" fill="#64748B"/>
														</svg>
														</span>
														<span class="check-icon">
															<svg xmlns="http://www.w3.org/2000/svg"  viewBox="0 0 26 26" width="12" height="12">
																<path d="M 22.566406 4.730469 L 20.773438 3.511719 C 20.277344 3.175781 19.597656 3.304688 19.265625 3.796875 L 10.476563 16.757813 L 6.4375 12.71875 C 6.015625 12.296875 5.328125 12.296875 4.90625 12.71875 L 3.371094 14.253906 C 2.949219 14.675781 2.949219 15.363281 3.371094 15.789063 L 9.582031 22 C 9.929688 22.347656 10.476563 22.613281 10.96875 22.613281 C 11.460938 22.613281 11.957031 22.304688 12.277344 21.839844 L 22.855469 6.234375 C 23.191406 5.742188 23.0625 5.066406 22.566406 4.730469 Z" fill="#64748B"/></svg>
														</span>
														<span class="tooltip-text edit-tooltip">Edit</span>
														<span class="tooltip-text update-tooltip">Update</span>
														</span>
													</span>
													<span class="orderSheet-right">
													<input type="checkbox" name="woocustomer_custom[]" value="<?php echo esc_attr( $wpssw_val ); ?>" class="woo-cust-headers-chk1" <?php echo esc_html( $wpssw_woo_is_checked ); ?> hidden="true"><input type="checkbox" name="woocustomer_header_list[]" value="<?php echo esc_attr( $wpssw_val ); ?>" id="woo-<?php echo esc_attr( $wpssw_labelid ); ?>" class="woo-cust-headers-chk" <?php echo esc_attr( $wpssw_woo_is_checked ); ?>>
													<span class="checkbox-switch-new"></span>
														<span class="ui-icon ui-icon-caret-2-n-s wpssw-tooltio-link">
															<svg width="8" height="12" viewBox="0 0 8 12" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M1.95875 11.67C1.55759 11.67 1.21418 11.5272 0.928508 11.2415C0.642836 10.9558 0.5 10.6124 0.5 10.2113C0.5 9.81009 0.642836 9.46668 0.928508 9.18101C1.21418 8.89534 1.55759 8.7525 1.95875 8.7525C2.35991 8.7525 2.70332 8.89534 2.98899 9.18101C3.27466 9.46668 3.4175 9.81009 3.4175 10.2113C3.4175 10.6124 3.27466 10.9558 2.98899 11.2415C2.70332 11.5272 2.35991 11.67 1.95875 11.67ZM6.335 11.67C5.93384 11.67 5.59043 11.5272 5.30476 11.2415C5.01909 10.9558 4.87625 10.6124 4.87625 10.2113C4.87625 9.81009 5.01909 9.46668 5.30476 9.18101C5.59043 8.89534 5.93384 8.7525 6.335 8.7525C6.73616 8.7525 7.07957 8.89534 7.36524 9.18101C7.65091 9.46668 7.79375 9.81009 7.79375 10.2113C7.79375 10.6124 7.65091 10.9558 7.36524 11.2415C7.07957 11.5272 6.73616 11.67 6.335 11.67ZM1.95875 7.29375C1.55759 7.29375 1.21418 7.15091 0.928508 6.86524C0.642836 6.57957 0.5 6.23616 0.5 5.835C0.5 5.43384 0.642836 5.09043 0.928508 4.80476C1.21418 4.51909 1.55759 4.37625 1.95875 4.37625C2.35991 4.37625 2.70332 4.51909 2.98899 4.80476C3.27466 5.09043 3.4175 5.43384 3.4175 5.835C3.4175 6.23616 3.27466 6.57957 2.98899 6.86524C2.70332 7.15091 2.35991 7.29375 1.95875 7.29375ZM6.335 7.29375C5.93384 7.29375 5.59043 7.15091 5.30476 6.86524C5.01909 6.57957 4.87625 6.23616 4.87625 5.835C4.87625 5.43384 5.01909 5.09043 5.30476 4.80476C5.59043 4.51909 5.93384 4.37625 6.335 4.37625C6.73616 4.37625 7.07957 4.51909 7.36524 4.80476C7.65091 5.09043 7.79375 5.43384 7.79375 5.835C7.79375 6.23616 7.65091 6.57957 7.36524 6.86524C7.07957 7.15091 6.73616 7.29375 6.335 7.29375ZM1.95875 2.9175C1.55759 2.9175 1.21418 2.77466 0.928508 2.48899C0.642836 2.20332 0.5 1.85991 0.5 1.45875C0.5 1.05759 0.642836 0.71418 0.928508 0.428508C1.21418 0.142836 1.55759 0 1.95875 0C2.35991 0 2.70332 0.142836 2.98899 0.428508C3.27466 0.71418 3.4175 1.05759 3.4175 1.45875C3.4175 1.85991 3.27466 2.20332 2.98899 2.48899C2.70332 2.77466 2.35991 2.9175 1.95875 2.9175ZM6.335 2.9175C5.93384 2.9175 5.59043 2.77466 5.30476 2.48899C5.01909 2.20332 4.87625 1.85991 4.87625 1.45875C4.87625 1.05759 5.01909 0.71418 5.30476 0.428508C5.59043 0.142836 5.93384 0 6.335 0C6.73616 0 7.07957 0.142836 7.36524 0.428508C7.65091 0.71418 7.79375 1.05759 7.79375 1.45875C7.79375 1.85991 7.65091 2.20332 7.36524 2.48899C7.07957 2.77466 6.73616 2.9175 6.335 2.9175Z" fill="#64748B"/>
</svg>
<span class="tooltip-text">Drag & Drop</span>
														</span>
													</span>
												</label>
											</li>
													<?php
												}
											}
											?>
										</ul>
									</div>
								</div>
							</div>
						</div>
						<div class="generalSetting-section cust_spreadsheet_row">
							<div scope="row" class="titledesc generalSetting-left">
								<h4 for="custom_customer_header_action"><?php echo esc_html__( 'Custom Static Headers', 'wpssw' ); ?></h4>
								<p><?php echo esc_html__( 'Want to add your custom headers? Enable this button, and with a click of the "Add" button, you can create static header-value pairs to customize your header list further.', 'wpssw' ); ?></p>
								<div class="custom-customer-input-div custom-input-div-all">
									<input type="text" name="custom_customer_headers_val" class="input-text" id="custom_customer_headers_val" placeholder="Enter Header Name"><br><br>
									<select name="custom_customer_headers_val_dropdown" id="custom_customer_headers_val_dropdown">
										<option value="blank">Blank</option>
									</select>
									<button class="wpssw-button wpssw-button-secondary" type="button" id='add_customer_ctm_val'><?php echo esc_html__( 'Add', 'wpssw' ); ?></button>
								</div>
							</div>
							<div class="forminp wpsswpd15 generalSetting-right">              
								<label for="custom_customer_header_action">
									<input name="custom_customer_header_action" id="custom_customer_header_action" type="checkbox" class="" value="1"><span class="checkbox-switch"></span> 							
								</label>  
							</div>
						</div>
						<?php
						if ( ! empty( $wpssw_custsheet_id ) && ! empty( $wpssw_customersheet_name ) ) {
							?>
						<div valign="top" id="custsynctr" class="cust_spreadsheet_row checkbox_margin generalSetting-section" >
							<div scope="row" class="titledesc generalSetting-left">
								<h4>
									<span class="wpssw-tooltio-link tooltip-right">
										<?php echo esc_html__( 'Sync Customers', 'wpssw' ); ?>
										<span class="tooltip-text">Export</span>
									</span>
								</h4>
								<p><?php echo esc_html__( "Click the 'Click to Sync' button to append new customers (within a specified date range, if desired) not already present in the sheet. Rest assured; existing customer data won't be affected.", 'wpssw' ); ?></p>
								<div class="sync_all_fromtodate-main">
									<div class="syncall-radio">
										<div class="syncall-radio-box">
											<input type="radio" name="cust_sync_all_checkbox" value="1" id="cust_sync_all" checked="checked">
											<label for="cust_sync_all"><?php echo esc_html__( 'All Customers', 'wpssw' ); ?></label>
										</div>
										<div class="syncall-radio-box">
											<input type="radio" name="cust_sync_all_checkbox" value="0" id="cust_sync_daterange">
											<label for="cust_sync_daterange"><?php echo esc_html__( 'Date Range', 'wpssw' ); ?></label>
										</div>
									</div>
									<div class="forminp forminp-select cust_sync_all_fromtodate sync_all_fromtodate"> 
										<label for="cust_sync_all_fromdate" >
											<?php echo esc_html__( 'From :', 'wpssw' ); ?>   <input name="cust_sync_all_fromdate" id="cust_sync_all_fromdate" class="cust_sync_all_fromdate" type="date" >
										</label>
										<label for="cust_sync_all_todate" class="cust_sync_todate_label">
											<?php echo esc_html__( 'To :', 'wpssw' ); ?> <input name="cust_sync_all_todate" id="cust_sync_all_todate" class="cust_sync_all_todate" type="date" >
										</label>
									</div>
								</div>
								<div class="sync-button-box">  
									<img src="images/spinner.gif" id="custsyncloader">
									<span id="custsynctext"><?php echo esc_html__( 'Synchronizing...', 'wpssw' ); ?></span>
									<a class="wpssw-button wpssw-button-secondary" href="javascript:void(0)" id="custsync">
									<?php echo esc_html__( 'Click to Sync', 'wpssw' ); ?></a> 
								</div>
							</div>
						</div>
							<?php
						}
							self::wpssw_auto_sync_setting(
								'customer',
								array(
									'id'    => 'custautosynctr',
									'class' => 'cust_spreadsheet_row',
								)
							);
					?>
						<div valign="top" id="import_customer_checkbox_row" class="cust_spreadsheet_row checkbox_margin generalSetting-section import-section-row ">
							<div>	
								<div class="cust-import-section import-section">	
									<div class="titledesc generalSetting-left">
										<h4>
											<span class="wpssw-tooltio-link tooltip-right">
												<?php esc_html_e( 'Import Customers', 'wpssw' ); ?>
												<span class="tooltip-text">Import</span>
											</span>
										</h4>
										<p><?php echo esc_html__( "Need to import customers from another sheet? No worries! Use the 'Import' button, but don't forget to back up your database before doing so.", 'wpssw' ); ?> 
										<a href="<?php echo esc_url( 'https://docs.wpsyncsheets.com/how-to-import-customers/' ); ?>" target="_blank"><?php echo esc_html__( 'How to Import Customers?', 'wpssw' ); ?></a>  
									</div>
									<div class="forminp generalSetting-right">      
										<label for="import_customer_checkbox">
											<input name="import_customer_checkbox" id="import_customer_checkbox" type="checkbox" class="" value="1" 
											<?php
											if ( $wpssw_customer_import ) {
												echo 'checked="checked"';}
											?>
											><span class="checkbox-switch"></span><span class="checkbox-switch"></span> 		
										</label> 
									</div>
								</div>
								<div class="generalSetting-section cust_import_options_row import_options_row">
									<div valign="top" id="insert_customer_checkbox_row" class="cust_spreadsheet_row wpssw_crud_cust_row wpssw_all_crud_row">
										<div scope="row" class="titledesc generalSetting-left">
											<label><?php esc_html_e( 'Insert Customer', 'wpssw' ); ?></label>
										</div>
										<div class="forminp generalSetting-right inside-checkbox">    
											<label for="insert_customer_checkbox">
												<input name="insert_customer_checkbox" id="insert_customer_checkbox" type="checkbox" class="" value="1" 
												<?php
												if ( $wpssw_customer_insert ) {
													echo 'checked="checked"';}
												?>
												/><span class="checkbox-switch-new"></span>
											</label> 
										</div>
									</div>
									<div valign="top" id="update_customer_checkbox_row" class="cust_spreadsheet_row wpssw_crud_cust_row wpssw_all_crud_row">
										<div scope="row" class="titledesc generalSetting-left">
											<label><?php esc_html_e( 'Update Customer', 'wpssw' ); ?></label>
										</div>
										<div class="forminp generalSetting-right inside-checkbox">    
											<label for="update_customer_checkbox">
												<input name="update_customer_checkbox" id="update_customer_checkbox" type="checkbox" class="" value="1" 
												<?php
												if ( $wpssw_customer_update ) {
													echo 'checked="checked"';}
												?>
												><span class="checkbox-switch-new"></span>
											</label> 
										</div>
									</div>
									<div valign="top" id="delete_customer_checkbox_row" class="cust_spreadsheet_row wpssw_crud_cust_row wpssw_all_crud_row">
										<div scope="row" class="titledesc generalSetting-left">
											<label><?php esc_html_e( 'Delete Customer', 'wpssw' ); ?></label>
										</div>
										<div class="forminp generalSetting-right inside-checkbox">     
											<label for="delete_customer_checkbox">
												<input name="delete_customer_checkbox" id="delete_customer_checkbox" type="checkbox" class="" value="1" 
												<?php
												if ( $wpssw_customer_delete ) {
													echo 'checked="checked"';}
												?>
												><span class="checkbox-switch-new"></span>
											</label> 
										</div>
									</div>
								</div>
								<?php if ( ( ! empty( $wpssw_custsheet_id ) && ! empty( $wpssw_customersheet_name ) ) && ( 1 === (int) $wpssw_customer_insert || 1 === (int) $wpssw_customer_update || 1 === (int) $wpssw_customer_delete ) ) { ?>
								<div valign="top" class="cust_spreadsheet_row wpssw_crud_cust_row generalSetting-section cust_import_row import_row">
									<div scope="row" class="titledesc generalSetting-left">
									<img src="images/spinner.gif" id="custimportsyncloader">
									<span id="custimportsynctext"><?php esc_html_e( 'Checking...', 'wpssw' ); ?></span>
									<a class="wpssw-button wpssw-button-secondary" href="javascript:void(0)" id="custimportsync"><?php echo esc_html__( 'Import Customer', 'wpssw' ); ?></a>
									<br />
									<a class="wpssw-button wpssw-button-secondary" href="javascript:void(0)" id="custimportsyncbtm"><?php esc_html_e( 'Proceed', 'wpssw' ); ?></a>
									<a class="wpssw-button wpssw-button-secondary" href="javascript:void(0)" id="custcancelsyncbtm"><?php esc_html_e( 'Cancel', 'wpssw' ); ?></a> 
									</div>
								</div>
								<?php } ?>
							</div>
						</div>
						<?php if ( 1 === (int) $wpssw_customer_insert || 1 === (int) $wpssw_customer_update || 1 === (int) $wpssw_customer_delete ) { ?>					
							<?php

							self::wpssw_auto_sync_setting(
								'customer',
								array(
									'id'    => 'custimporttr',
									'class' => 'wpssw_crud_cust_row cust_spreadsheet_row',
								),
								'import'
							);
						}

						$cust_inherit_style = self::wpssw_option( 'wpssw_customer_inherit_style' );
						?>
						<div class="inheritStyles generalSetting-section cust_spreadsheet_row">
							<div class="titledesc generalSetting-left">
								<h4><?php echo esc_html__( 'Inherit Styles', 'wpssw' ); ?></h4>
								<p><?php echo esc_html__( 'This dropdown allows you to choose whether the new row should inherit the style from the row above it. The available options are "Yes" to inherit the style, "No" to not inherit the style, or "None" to leave the style inheritance unaffected.', 'wpssw' ); ?>  <a href="<?php echo esc_url( 'https://docs.wpsyncsheets.com/how-to-use-inherit-style-option/' ); ?>" target="_blank"><?php echo esc_html__( 'How to use inherit style option?', 'wpssw' ); ?></a></p>
							</div>
							<div class="forminp generalSetting-right">
								<label for="cust_inherit_style">
									<select name="cust_inherit_style">
										<?php foreach ( $inherit_style_options as $inherit_option ) { ?>
											<option value="<?php echo esc_attr( $inherit_option ); ?>" 
												<?php
												if ( $inherit_option === $cust_inherit_style || ( 'none' === (string) $inherit_option && '' === (string) $cust_inherit_style ) ) {
													echo ' selected';
												}
												?>
											><?php echo esc_html( ucfirst( $inherit_option ) ); ?></option>
										<?php } ?>
									</select>
								</label>
							</div>
						</div>
					<?php
					if ( WPSSW_Dependencies::wpssw_is_woocommerce_active() ) {
						?>
					<div class="submit-section">
						<p class="submit"><input type="submit" name="submit" id="submit" class="wpssw-button wpssw-button-primary" value="Save"></p>
					</div>
						<?php
					} else {
						?>
						<div class="generalSetting-section generalSetting-section-message">
							<h4><?php echo 'WPSyncSheets For WooCommerce plugin requires <a href="http://wordpress.org/extend/plugins/woocommerce/">WooCommerce</a> plugin to be active!'; ?></h4>
						</div>
					<?php } ?>
				</form>
					<?php
				} else {
					?>
					<div class="generalSetting-section generalSetting-section-message">
						<h4><?php echo esc_html__( 'Please genearate authentication code from', 'wpssw' ); ?>
							<strong><?php echo esc_html__( 'Google API Setting ', 'wpssw' ); ?></strong>
							<a href='<?php echo esc_url( 'admin.php?page=wpsyncsheets-for-woocommerce' ); ?>'> <?php echo esc_html__( 'Click', 'wpssw' ); ?></a>
						</h4>
					</div>
				<?php } ?> 
				</div>
				<div id="coupon-settings" class="tabcontent">
				<?php
				$wpssw_coupon_import              = self::wpssw_option( 'wpssw_coupon_import' );
				$wpssw_coupon_insert              = self::wpssw_option( 'wpssw_coupon_insert' );
				$wpssw_coupon_update              = self::wpssw_option( 'wpssw_coupon_update' );
				$wpssw_coupon_delete              = self::wpssw_option( 'wpssw_coupon_delete' );
				$wpssw_coupon_spreadsheet_setting = self::wpssw_option( 'wpssw_coupon_spreadsheet_setting' );
				$wpssw_coupon_spreadsheet_id      = self::wpssw_option( 'wpssw_coupon_spreadsheet_id' );
				$wpssw_spreadsheet_id             = self::wpssw_option( 'wpssw_woocommerce_spreadsheet' );
				$wpssw_couponsheet_id             = $wpssw_coupon_spreadsheet_id;
				$wpssw_couponsheet_name           = '';
				$wpssw_spreadsheets_list          = self::$instance_api->get_spreadsheet_listing();

				if ( self::wpssw_check_sheet_exist( $wpssw_couponsheet_id, 'All Coupons' ) ) {
					$wpssw_couponsheet_name = 'All Coupons';
				}
				if ( empty( $wpssw_coupon_spreadsheet_id ) ) {
					$wpssw_coupon_spreadsheet_id = $wpssw_spreadsheet_id;
				}
				$wpssw_checked = '';
				if ( 'yes' === (string) $wpssw_coupon_spreadsheet_setting ) {
					$wpssw_checked = 'checked';
				}
				?>
				<?php
				if ( ! empty( $wpssw_google_settings[2] ) ) {
					?>
				<form method="post" action="<?php echo esc_html( admin_url( 'admin.php?page=wpsyncsheets-for-woocommerce&tab=coupon-settings' ) ); ?>" id="couponform">
					<?php wp_nonce_field( 'save_coupon_settings', 'wpssw_coupon_settings' ); ?>
					<input name="coupon_sheet" id="coupon_sheet" type="hidden" class="" value="<?php echo esc_html( $wpssw_couponsheet_name ); ?>">
						<div valign="top" class="checkbox_margin generalSetting-section">
							<div scope="row" class="titledesc generalSetting-left">
								<h4><?php echo esc_html__( 'Coupon Settings', 'wpssw' ); ?></h4>
								<p><?php echo esc_html__( 'Enhance your coupon management process by enabling this option, automatically creating personalized spreadsheets and an "All Coupons" sheet, ensuring seamless functionality.', 'wpssw' ); ?></p>
							</div>
							<div class="forminp generalSetting-right">              
								<label for="coupon_settings_checkbox">
									<input name="coupon_settings_checkbox" id="coupon_settings_checkbox" type="checkbox" class="" value="1" <?php echo esc_html( $wpssw_checked ); ?>><span class="checkbox-switch"></span><span class="checkbox-switch"></span>
								</label>
							</div>
						</div>
						<?php
							$spreadsheets_list = self::$instance_api->get_spreadsheet_listing();
						?>
						<div class="generalSetting-section googleSpreadsheet-section coupon_spreadsheet_row">
							<div class="generalSetting-left">
								<h4><?php echo esc_html__( 'Google Spreadsheet Settings', 'wpssw' ); ?></h4>
								<p><?php echo esc_html__( "Once you've assigned a Google Spreadsheet, it will automatically generate an 'All Coupons' sheet with headers based on your settings. Additionally, new rows will be created whenever new coupons are created.", 'wpssw' ); ?></p>
								<div class="createanew-radio">
									<div class="createanew-radio-box">
										<input type="radio" name="couponsheetselection" value="new" id="couponcreateanew">
										<label for="couponcreateanew"><?php echo esc_html__( 'Create New Spreadsheet', 'wpssw' ); ?></label>
									</div>
									<div class="createanew-radio-box">
										<input type="radio" name="couponsheetselection" value="existing" id="couponexisting" checked="checked">
										<label for="couponexisting"><?php echo esc_html__( 'Select Existing Spreadsheet', 'wpssw' ); ?></label>
									</div>
								</div>
								<div id="coupon_spreadsheet_container" class="spreadsheet-form">
									<select name="coupon_spreadsheet" id="coupon_spreadsheet" style="min-width:150px;" class="">
										<?php
										$selected = '';
										foreach ( $spreadsheets_list as $spreadsheetid => $spreadsheetname ) {
											if ( (string) $wpssw_coupon_spreadsheet_id === $spreadsheetid ) {
												$selected = 'selected="selected"';
											}
											?>
										<option value="<?php echo esc_attr( $spreadsheetid ); ?>" <?php echo esc_attr( $selected ); ?>><?php echo esc_html( $spreadsheetname ); ?></option>
										<?php $selected = ''; } ?>
									</select>
								</div>
								<div valign="top" id="coupon_newsheet" class="newsheetinput spreadsheet-form coupon_spreadsheet_inputrow">
									<input name="coupon_spreadsheet_name" id="coupon_spreadsheet_name" type="text" class="input-text" value="" placeholder="<?php echo esc_html__( 'Enter Spreadsheet Name', 'wpssw' ); ?>">
								</div>
							</div>
						</div>
						<div class="coupon_spreadsheet_row generalSetting-section sheetHeaders-section ">
							<div class="td-wpssw-headers">
								<div class="wpssw_headers">
									<div class="generalSetting-sheet-headers-row">
										<div class="generalSetting-left">
											<h4 for="sheet_headers"><?php echo esc_html__( 'Sheet Headers', 'wpssw' ); ?></h4>
											<p><?php echo esc_html__( 'Effortlessly tailor your spreadsheet by turning specific sheet headers on or off. Disabled headers will be automatically removed from the current spreadsheet. To update the sheet with the latest data, save your desired header settings and click the "Click to Sync" button.', 'wpssw' ); ?></p>
										</div>
									</div>
									<div class="selectall-btnset">
										<button type="button" class="wpssw-button wpssw-button-primary" id="woo-couponselectall"><?php echo esc_html__( 'Select All', 'wpssw' ); ?></button>
										<button type="button" class="wpssw-button wpssw-button-secondary" id="woo-couponselectnone"><?php echo esc_html__( 'Select None', 'wpssw' ); ?></button>
									</div>
									<div class="sheetHeaders-main">
										<ul id="woo-coupon-sortable" class="sheetHeaders-box">
											<?php
											$wpssw_woo_is_checked = '';
											$wpssw_woo_selections = stripslashes_deep( self::wpssw_option( 'wpssw_woo_coupon_headers' ) );
											if ( ! $wpssw_woo_selections ) {
												$wpssw_woo_selections = array();
											}
											$wpssw_woo_selections_custom = stripslashes_deep( self::wpssw_option( 'wpssw_woo_coupon_headers_custom' ) );
											if ( ! $wpssw_woo_selections_custom ) {
												$wpssw_woo_selections_custom = array();
											}
											$wpssw_include = new WPSSW_Include_Action();
											$wpssw_include->wpssw_include_coupon_compatibility_files();
											$wpssw_woocoupon_headers = apply_filters( 'wpsyncsheets_coupon_headers', array() );
											$wpssw_woocoupon_headers = self::wpssw_array_flatten( $wpssw_woocoupon_headers );

											$wpssw_operation = array( 'Insert', 'Update', 'Delete' );

											if ( ! empty( $wpssw_woo_selections ) ) {
												foreach ( $wpssw_woo_selections as $wpssw_key => $wpssw_val ) {
													$wpssw_woo_is_checked = 'checked';
													$wpssw_labelid        = strtolower( str_replace( ' ', '_', $wpssw_val ) );
													$wpssw_display        = true;
													$wpssw_classname      = '';
													if ( in_array( $wpssw_val, $wpssw_operation, true ) ) {
														$wpssw_display   = false;
														$wpssw_labelid   = '';
														$wpssw_classname = strtolower( $wpssw_val ) . 'coupon';
													}
													?>
												<li class="default-order-sheet ui-state-default <?php echo esc_html( $wpssw_classname ); ?>">
<label for="woo-<?php echo esc_attr( $wpssw_labelid ); ?>">
													<span class="orderSheet-left">
													<span class="wootextfield"><?php echo isset( $wpssw_woo_selections_custom[ $wpssw_key ] ) ? esc_attr( $wpssw_woo_selections_custom[ $wpssw_key ] ) : esc_attr( $wpssw_val ); ?></span>
													<?php if ( $wpssw_display ) { ?>
												<span class="ui-icon ui-icon-pencil wpssw-tooltio-link">
													<span class="pencil-icon">
														<svg width="12" height="12" viewBox="0 0 13 13" fill="none" xmlns="http://www.w3.org/2000/svg">
														<path d="M1.33333 11.6668H2.26667L8.01667 5.91677L7.08333 4.98343L1.33333 10.7334V11.6668ZM10.8667 4.9501L8.03333 2.1501L8.96667 1.21677C9.22222 0.961213 9.53611 0.833435 9.90833 0.833435C10.2806 0.833435 10.5944 0.961213 10.85 1.21677L11.7833 2.1501C12.0389 2.40566 12.1722 2.71399 12.1833 3.0751C12.1944 3.43621 12.0722 3.74455 11.8167 4.0001L10.8667 4.9501ZM9.9 5.93343L2.83333 13.0001H0V10.1668L7.06667 3.1001L9.9 5.93343Z" fill="#64748B"/>
														</svg>
														</span>
														<span class="check-icon">
															<svg xmlns="http://www.w3.org/2000/svg"  viewBox="0 0 26 26" width="12" height="12">
																<path d="M 22.566406 4.730469 L 20.773438 3.511719 C 20.277344 3.175781 19.597656 3.304688 19.265625 3.796875 L 10.476563 16.757813 L 6.4375 12.71875 C 6.015625 12.296875 5.328125 12.296875 4.90625 12.71875 L 3.371094 14.253906 C 2.949219 14.675781 2.949219 15.363281 3.371094 15.789063 L 9.582031 22 C 9.929688 22.347656 10.476563 22.613281 10.96875 22.613281 C 11.460938 22.613281 11.957031 22.304688 12.277344 21.839844 L 22.855469 6.234375 C 23.191406 5.742188 23.0625 5.066406 22.566406 4.730469 Z" fill="#64748B"/></svg>
														</span>
														<span class="tooltip-text edit-tooltip">Edit</span>
														<span class="tooltip-text update-tooltip">Update</span>
												</span>
												<?php } ?>
											</span>
											<span class="orderSheet-right">
											<input type="checkbox" name="woocoupon_custom[]" value="<?php echo isset( $wpssw_woo_selections_custom[ $wpssw_key ] ) ? esc_attr( $wpssw_woo_selections_custom[ $wpssw_key ] ) : esc_attr( $wpssw_val ); ?>" class="woo-coupon-headers-chk1" <?php echo esc_html( $wpssw_woo_is_checked ); ?> hidden="true">
											<input type="checkbox" name="woocoupon_header_list[]" value="<?php echo esc_attr( $wpssw_val ); ?>" id="woo-<?php echo esc_attr( $wpssw_labelid ); ?>" class="woo-coupon-headers-chk" <?php echo esc_html( $wpssw_woo_is_checked ); ?>>
													<?php if ( $wpssw_display ) { ?>
												<span class="checkbox-switch-new"></span>
												<?php } ?>
												<span class="ui-icon ui-icon-caret-2-n-s wpssw-tooltio-link">
													<svg width="8" height="12" viewBox="0 0 8 12" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M1.95875 11.67C1.55759 11.67 1.21418 11.5272 0.928508 11.2415C0.642836 10.9558 0.5 10.6124 0.5 10.2113C0.5 9.81009 0.642836 9.46668 0.928508 9.18101C1.21418 8.89534 1.55759 8.7525 1.95875 8.7525C2.35991 8.7525 2.70332 8.89534 2.98899 9.18101C3.27466 9.46668 3.4175 9.81009 3.4175 10.2113C3.4175 10.6124 3.27466 10.9558 2.98899 11.2415C2.70332 11.5272 2.35991 11.67 1.95875 11.67ZM6.335 11.67C5.93384 11.67 5.59043 11.5272 5.30476 11.2415C5.01909 10.9558 4.87625 10.6124 4.87625 10.2113C4.87625 9.81009 5.01909 9.46668 5.30476 9.18101C5.59043 8.89534 5.93384 8.7525 6.335 8.7525C6.73616 8.7525 7.07957 8.89534 7.36524 9.18101C7.65091 9.46668 7.79375 9.81009 7.79375 10.2113C7.79375 10.6124 7.65091 10.9558 7.36524 11.2415C7.07957 11.5272 6.73616 11.67 6.335 11.67ZM1.95875 7.29375C1.55759 7.29375 1.21418 7.15091 0.928508 6.86524C0.642836 6.57957 0.5 6.23616 0.5 5.835C0.5 5.43384 0.642836 5.09043 0.928508 4.80476C1.21418 4.51909 1.55759 4.37625 1.95875 4.37625C2.35991 4.37625 2.70332 4.51909 2.98899 4.80476C3.27466 5.09043 3.4175 5.43384 3.4175 5.835C3.4175 6.23616 3.27466 6.57957 2.98899 6.86524C2.70332 7.15091 2.35991 7.29375 1.95875 7.29375ZM6.335 7.29375C5.93384 7.29375 5.59043 7.15091 5.30476 6.86524C5.01909 6.57957 4.87625 6.23616 4.87625 5.835C4.87625 5.43384 5.01909 5.09043 5.30476 4.80476C5.59043 4.51909 5.93384 4.37625 6.335 4.37625C6.73616 4.37625 7.07957 4.51909 7.36524 4.80476C7.65091 5.09043 7.79375 5.43384 7.79375 5.835C7.79375 6.23616 7.65091 6.57957 7.36524 6.86524C7.07957 7.15091 6.73616 7.29375 6.335 7.29375ZM1.95875 2.9175C1.55759 2.9175 1.21418 2.77466 0.928508 2.48899C0.642836 2.20332 0.5 1.85991 0.5 1.45875C0.5 1.05759 0.642836 0.71418 0.928508 0.428508C1.21418 0.142836 1.55759 0 1.95875 0C2.35991 0 2.70332 0.142836 2.98899 0.428508C3.27466 0.71418 3.4175 1.05759 3.4175 1.45875C3.4175 1.85991 3.27466 2.20332 2.98899 2.48899C2.70332 2.77466 2.35991 2.9175 1.95875 2.9175ZM6.335 2.9175C5.93384 2.9175 5.59043 2.77466 5.30476 2.48899C5.01909 2.20332 4.87625 1.85991 4.87625 1.45875C4.87625 1.05759 5.01909 0.71418 5.30476 0.428508C5.59043 0.142836 5.93384 0 6.335 0C6.73616 0 7.07957 0.142836 7.36524 0.428508C7.65091 0.71418 7.79375 1.05759 7.79375 1.45875C7.79375 1.85991 7.65091 2.20332 7.36524 2.48899C7.07957 2.77466 6.73616 2.9175 6.335 2.9175Z" fill="#64748B"/>
</svg>

<span class="tooltip-text">Drag & Drop</span>
												</span>
											</span>
												</label>
											</li>
													<?php
												}
											}
											if ( ! empty( $wpssw_woocoupon_headers ) ) {
												foreach ( $wpssw_woocoupon_headers as $wpssw_key => $wpssw_val ) {
													$wpssw_woo_is_checked = '';
													if ( in_array( $wpssw_val, $wpssw_woo_selections, true ) ) {
														continue;
													}
													$wpssw_labelid = strtolower( str_replace( ' ', '_', $wpssw_val ) );
													?>
													<li class="default-order-sheet ui-state-default">
<label for="woo-<?php echo esc_attr( $wpssw_labelid ); ?>">
														<span class="orderSheet-left">
															<span class="wootextfield"><?php echo esc_html( $wpssw_val ); ?></span>
															<span class="ui-icon ui-icon-pencil">
																<span class="pencil-icon">
															<svg width="12" height="12" viewBox="0 0 13 13" fill="none" xmlns="http://www.w3.org/2000/svg">
															<path d="M1.33333 11.6668H2.26667L8.01667 5.91677L7.08333 4.98343L1.33333 10.7334V11.6668ZM10.8667 4.9501L8.03333 2.1501L8.96667 1.21677C9.22222 0.961213 9.53611 0.833435 9.90833 0.833435C10.2806 0.833435 10.5944 0.961213 10.85 1.21677L11.7833 2.1501C12.0389 2.40566 12.1722 2.71399 12.1833 3.0751C12.1944 3.43621 12.0722 3.74455 11.8167 4.0001L10.8667 4.9501ZM9.9 5.93343L2.83333 13.0001H0V10.1668L7.06667 3.1001L9.9 5.93343Z" fill="#64748B"/>
															</svg>
															</span>
															<span class="check-icon">
																<svg xmlns="http://www.w3.org/2000/svg"  viewBox="0 0 26 26" width="12" height="12">
																	<path d="M 22.566406 4.730469 L 20.773438 3.511719 C 20.277344 3.175781 19.597656 3.304688 19.265625 3.796875 L 10.476563 16.757813 L 6.4375 12.71875 C 6.015625 12.296875 5.328125 12.296875 4.90625 12.71875 L 3.371094 14.253906 C 2.949219 14.675781 2.949219 15.363281 3.371094 15.789063 L 9.582031 22 C 9.929688 22.347656 10.476563 22.613281 10.96875 22.613281 C 11.460938 22.613281 11.957031 22.304688 12.277344 21.839844 L 22.855469 6.234375 C 23.191406 5.742188 23.0625 5.066406 22.566406 4.730469 Z" fill="#64748B"/></svg>
															</span>
															</span>
														</span>
														<span class="orderSheet-right">
														<input type="checkbox" name="woocoupon_custom[]" value="<?php echo esc_attr( $wpssw_val ); ?>" class="woo-coupon-headers-chk1" <?php echo esc_html( $wpssw_woo_is_checked ); ?> hidden="true">
														<input type="checkbox" name="woocoupon_header_list[]" value="<?php echo esc_attr( $wpssw_val ); ?>" id="woo-<?php echo esc_attr( $wpssw_labelid ); ?>" class="woo-coupon-headers-chk" <?php echo esc_html( $wpssw_woo_is_checked ); ?>>
															<span class="checkbox-switch-new"></span>
															<span class="ui-icon ui-icon-caret-2-n-s">
																<svg width="17" height="16" viewBox="0 0 17 16" fill="none" xmlns="http://www.w3.org/2000/svg">
						<mask id="mask0_384_3228" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="0" y="0" width="17" height="16">
						<rect x="0.5" width="16" height="16" fill="#D9D9D9"/>
						</mask>
						<g mask="url(#mask0_384_3228)">
						<path d="M5.95875 13.67C5.55759 13.67 5.21418 13.5272 4.92851 13.2415C4.64284 12.9558 4.5 12.6124 4.5 12.2113C4.5 11.8101 4.64284 11.4667 4.92851 11.181C5.21418 10.8953 5.55759 10.7525 5.95875 10.7525C6.35991 10.7525 6.70332 10.8953 6.98899 11.181C7.27466 11.4667 7.4175 11.8101 7.4175 12.2113C7.4175 12.6124 7.27466 12.9558 6.98899 13.2415C6.70332 13.5272 6.35991 13.67 5.95875 13.67ZM10.335 13.67C9.93384 13.67 9.59043 13.5272 9.30476 13.2415C9.01909 12.9558 8.87625 12.6124 8.87625 12.2113C8.87625 11.8101 9.01909 11.4667 9.30476 11.181C9.59043 10.8953 9.93384 10.7525 10.335 10.7525C10.7362 10.7525 11.0796 10.8953 11.3652 11.181C11.6509 11.4667 11.7937 11.8101 11.7937 12.2113C11.7937 12.6124 11.6509 12.9558 11.3652 13.2415C11.0796 13.5272 10.7362 13.67 10.335 13.67ZM5.95875 9.29375C5.55759 9.29375 5.21418 9.15091 4.92851 8.86524C4.64284 8.57957 4.5 8.23616 4.5 7.835C4.5 7.43384 4.64284 7.09043 4.92851 6.80476C5.21418 6.51909 5.55759 6.37625 5.95875 6.37625C6.35991 6.37625 6.70332 6.51909 6.98899 6.80476C7.27466 7.09043 7.4175 7.43384 7.4175 7.835C7.4175 8.23616 7.27466 8.57957 6.98899 8.86524C6.70332 9.15091 6.35991 9.29375 5.95875 9.29375ZM10.335 9.29375C9.93384 9.29375 9.59043 9.15091 9.30476 8.86524C9.01909 8.57957 8.87625 8.23616 8.87625 7.835C8.87625 7.43384 9.01909 7.09043 9.30476 6.80476C9.59043 6.51909 9.93384 6.37625 10.335 6.37625C10.7362 6.37625 11.0796 6.51909 11.3652 6.80476C11.6509 7.09043 11.7937 7.43384 11.7937 7.835C11.7937 8.23616 11.6509 8.57957 11.3652 8.86524C11.0796 9.15091 10.7362 9.29375 10.335 9.29375ZM5.95875 4.9175C5.55759 4.9175 5.21418 4.77466 4.92851 4.48899C4.64284 4.20332 4.5 3.85991 4.5 3.45875C4.5 3.05759 4.64284 2.71418 4.92851 2.42851C5.21418 2.14284 5.55759 2 5.95875 2C6.35991 2 6.70332 2.14284 6.98899 2.42851C7.27466 2.71418 7.4175 3.05759 7.4175 3.45875C7.4175 3.85991 7.27466 4.20332 6.98899 4.48899C6.70332 4.77466 6.35991 4.9175 5.95875 4.9175ZM10.335 4.9175C9.93384 4.9175 9.59043 4.77466 9.30476 4.48899C9.01909 4.20332 8.87625 3.85991 8.87625 3.45875C8.87625 3.05759 9.01909 2.71418 9.30476 2.42851C9.59043 2.14284 9.93384 2 10.335 2C10.7362 2 11.0796 2.14284 11.3652 2.42851C11.6509 2.71418 11.7937 3.05759 11.7937 3.45875C11.7937 3.85991 11.6509 4.20332 11.3652 4.48899C11.0796 4.77466 10.7362 4.9175 10.335 4.9175Z" fill="#64748B"/>
						</g>
						</svg>

															</span>
														</span>
													</label>
												</li>
													<?php
												}
											}
											?>
										</ul>
									</div>
								</div>
							</div>
						</div>
						<?php
						if ( ! empty( $wpssw_couponsheet_id ) && ! empty( $wpssw_couponsheet_name ) ) {
							?>
						<div valign="top" id="couponsynctr" class="coupon_spreadsheet_row checkbox_margin generalSetting-section" >
							<div scope="row" class="titledesc generalSetting-left">
								<h4>
									<span class="wpssw-tooltio-link tooltip-right">
										<?php echo esc_html__( 'Sync Coupons', 'wpssw' ); ?>
										<span class="tooltip-text">Export</span>
									</span>
								</h4>
								<p><?php echo esc_html__( 'The "Click to Sync" button makes it easy to append all or selected date range coupons not already present in the sheet. You can rest assured that existing coupons will not be affected during synchronization.', 'wpssw' ); ?></p>
								<div class="sync_all_fromtodate-main">
									<div class="syncall-radio">
										<div class="syncall-radio-box">
											<input type="radio" name="coupon_sync_all_checkbox" value="1" id="coupon_sync_all" checked="checked">
											<label for="coupon_sync_all"><?php echo esc_html__( 'All Coupons', 'wpssw' ); ?></label>
										</div>
										<div class="syncall-radio-box">
											<input type="radio" name="coupon_sync_all_checkbox" value="0" id="coupon_sync_daterange">
											<label for="coupon_sync_daterange"><?php echo esc_html__( 'Date Range', 'wpssw' ); ?></label>
										</div>
									</div>
									<div class="forminp forminp-select coupon_sync_all_fromtodate sync_all_fromtodate"> 
										<label for="coupon_sync_all_fromdate" >
											<?php echo esc_html__( 'From :', 'wpssw' ); ?>   <input name="coupon_sync_all_fromdate" id="coupon_sync_all_fromdate" class="coupon_sync_all_fromdate" type="date" >
										</label>
										<label for="coupon_sync_all_todate" class="coupon_sync_todate_label">
											<?php echo esc_html__( 'To :', 'wpssw' ); ?> <input name="coupon_sync_all_todate" id="coupon_sync_all_todate" class="coupon_sync_all_todate" type="date" >
										</label>
									</div>
								</div>
								<div class="sync-button-box">  
									<img src="images/spinner.gif" id="couponsyncloader">
									<span id="couponsynctext"><?php echo esc_html__( 'Synchronizing...', 'wpssw' ); ?></span>
									<a class="wpssw-button wpssw-button-secondary" href="javascript:void(0)" id="couponsync">
										<?php echo esc_html__( 'Click to Sync', 'wpssw' ); ?>
									</a> 
								</div>	
							</div>
						</div>
							<?php
						}
						self::wpssw_auto_sync_setting(
							'coupon',
							array(
								'id'    => 'couponautosynctr',
								'class' => 'coupon_spreadsheet_row',
							)
						);
					?>
						<div valign="top" id="import_coupon_checkbox_row" class="coupon_spreadsheet_row generalSetting-section coupon-import-section-row import-section-row ">
							<div>
								<div class="ord-import-section import-section">	
									<div scope="row" class="titledesc generalSetting-left">
										<h4>
											<span class="wpssw-tooltio-link tooltip-right">
												<?php esc_html_e( 'Import Coupons', 'wpssw' ); ?>
												<span class="tooltip-text">Import</span>
											</span>
										</h4>
										<p><?php echo esc_html__( 'Need to import coupons from another sheet? No problem! Use the "Import" button, but remember to back up your database before doing so to avoid any data loss.', 'wpssw' ); ?> 
										<a href="<?php echo esc_url( 'https://docs.wpsyncsheets.com/how-to-import-coupons/' ); ?>" target="_blank"><?php echo esc_html__( 'How to Import Coupons?', 'wpssw' ); ?></a> </p>
									</div>
									<div class="forminp generalSetting-right">      
										<label for="import_coupon_checkbox">
											<input name="import_coupon_checkbox" id="import_coupon_checkbox" type="checkbox" class="" value="1" 
											<?php
											if ( $wpssw_coupon_import ) {
												echo 'checked="checked"';}
											?>
											><span class="checkbox-switch"></span><span class="checkbox-switch"></span> 		
										</label> 
									</div>
								</div>
								<div class="generalSetting-section coupon_import_options_row import_options_row">
									<div valign="top" id="insert_coupon_checkbox_row" class="coupon_spreadsheet_row wpssw_crud_coupon_row wpssw_all_crud_row">
										<div scope="row" class="titledesc generalSetting-left">
											<label><?php esc_html_e( 'Insert Coupon', 'wpssw' ); ?></label>
										</div>
										<div class="forminp generalSetting-right inside-checkbox">    
											<label for="insert_coupon_checkbox">
												<input name="insert_coupon_checkbox" id="insert_coupon_checkbox" type="checkbox" class="" value="1" 
												<?php
												if ( $wpssw_coupon_insert ) {
													echo 'checked="checked"';}
												?>
												/><span class="checkbox-switch-new"></span>
											</label> 
										</div>
									</div>
									<div valign="top" id="update_coupon_checkbox_row" class="coupon_spreadsheet_row wpssw_crud_coupon_row wpssw_all_crud_row">
										<div class="generalSetting-left"> 
											<label><?php esc_html_e( 'Update Coupon', 'wpssw' ); ?></label>
										</div>
										<div class="generalSetting-right  inside-checkbox">     
											<label for="update_coupon_checkbox">
												<input name="update_coupon_checkbox" id="update_coupon_checkbox" type="checkbox" class="" value="1" 
												<?php
												if ( $wpssw_coupon_update ) {
													echo 'checked="checked"';}
												?>
												><span class="checkbox-switch-new"></span>
											</label> 
										</div>
									</div>
									<div valign="top" id="delete_coupon_checkbox_row" class="coupon_spreadsheet_row wpssw_crud_coupon_row wpssw_all_crud_row">
										<div class="generalSetting-left">
											<label><?php esc_html_e( 'Delete Coupon', 'wpssw' ); ?></label>
										</div>
										<div class="generalSetting-right inside-checkbox">   
											<label for="delete_coupon_checkbox">
												<input name="delete_coupon_checkbox" id="delete_coupon_checkbox" type="checkbox" class="" value="1" 
												<?php
												if ( $wpssw_coupon_delete ) {
													echo 'checked="checked"';}
												?>
												><span class="checkbox-switch-new"></span>
											</label> 
										</div>
									</div>
								</div>
								<?php if ( ( ! empty( $wpssw_couponsheet_id ) && ! empty( $wpssw_couponsheet_name ) ) && ( 1 === (int) $wpssw_coupon_insert || 1 === (int) $wpssw_coupon_update || 1 === (int) $wpssw_coupon_delete ) ) { ?>
								<div valign="top" class="coupon_spreadsheet_row wpssw_crud_coupon_row generalSetting-section import_row coupon_import_row">
									<div scope="row" class="titledesc generalSetting-left">
										<img src="images/spinner.gif" id="couponimportsyncloader">
										<span id="couponimportsynctext"><?php esc_html_e( 'Checking...', 'wpssw' ); ?></span>
										<a class="wpssw-button wpssw-button-secondary" href="javascript:void(0)" id="couponimportsync"><?php echo esc_html__( 'Import Coupon', 'wpssw' ); ?></a>
										<br />
										<a class="wpssw-button wpssw-button-secondary" href="javascript:void(0)" id="couponimportsyncbtm"><?php esc_html_e( 'Proceed', 'wpssw' ); ?></a>
										<a class="wpssw-button wpssw-button-secondary" href="javascript:void(0)" id="couponcancelsyncbtm"><?php esc_html_e( 'Cancel', 'wpssw' ); ?></a>
									</div>
								</div>
								<?php } ?>
							</div>
						</div>
						<?php if ( 1 === (int) $wpssw_coupon_insert || 1 === (int) $wpssw_coupon_update || 1 === (int) $wpssw_coupon_delete ) { ?>				
								<?php

								self::wpssw_auto_sync_setting(
									'coupon',
									array(
										'id'    => 'couponimporttr',
										'class' => 'wpssw_crud_coupon_row coupon_spreadsheet_row',
									),
									'import'
								);
						}

						$coupon_inherit_style = self::wpssw_option( 'wpssw_coupon_inherit_style' );
						?>
						<div class="inheritStyles generalSetting-section coupon_spreadsheet_row">
							<div class="titledesc generalSetting-left">
								<h4><?php echo esc_html__( 'Inherit Styles', 'wpssw' ); ?></h4>
								<p><?php echo esc_html__( 'This dropdown allows you to choose whether the new row should inherit the style from the row above it. The available options are "Yes" to inherit the style, "No" to not inherit the style, or "None" to leave the style inheritance unaffected.', 'wpssw' ); ?>  <a href="<?php echo esc_url( 'https://docs.wpsyncsheets.com/how-to-use-inherit-style-option/' ); ?>" target="_blank"><?php echo esc_html__( 'How to use inherit style option?', 'wpssw' ); ?></a></p>
							</div>
							<div class="forminp generalSetting-right">
								<label for="coupon_inherit_style">
									<select name="coupon_inherit_style">
										<?php foreach ( $inherit_style_options as $inherit_option ) { ?>
											<option value="<?php echo esc_attr( $inherit_option ); ?>" 
												<?php
												if ( $inherit_option === $coupon_inherit_style || ( 'none' === (string) $inherit_option && '' === (string) $coupon_inherit_style ) ) {
													echo ' selected';
												}
												?>
											><?php echo esc_html( ucfirst( $inherit_option ) ); ?></option>
										<?php } ?>
									</select>
								</label>
							</div>
						</div>
					<?php
					if ( WPSSW_Dependencies::wpssw_is_woocommerce_active() ) {
						?>
					<div class="submit-section">
						<p class="submit"><input type="submit" name="submit" id="submit" class="wpssw-button wpssw-button-primary" value="Save"></p>
					</div>
						<?php
					} else {
						?>
						<div class="generalSetting-section generalSetting-section-message">
							<h4><?php echo 'WPSyncSheets For WooCommerce plugin requires <a href="http://wordpress.org/extend/plugins/woocommerce/">WooCommerce</a> plugin to be active!'; ?></h4>
						</div>
						<?php
					}
					?>
				</form>
					<?php
				} else {
					?>
					<div class="generalSetting-section generalSetting-section-message">
						<h4><?php echo esc_html__( 'Please genearate authentication code from', 'wpssw' ); ?>
							<strong><?php echo esc_html__( 'Google API Setting ', 'wpssw' ); ?></strong>
							<a href='<?php echo esc_url( 'admin.php?page=wpsyncsheets-for-woocommerce' ); ?>'> <?php echo esc_html__( 'Click', 'wpssw' ); ?></a>
						</h4>
					</div>
				<?php } ?> 
				</div>
				<div id="general-settings" class="tabcontent">
					<form method="post" action="<?php echo esc_html( admin_url( 'admin.php?page=wpsyncsheets-for-woocommerce&tab=general-settings' ) ); ?>" id="mainform">
						<div class="generalSetting-section">
							<div class="generalSetting-left">
								<?php $freeze_header = self::wpssw_option( 'freeze_header' ); ?>
								<h4><?php echo esc_html__( 'Freeze Header', 'wpssw' ); ?></h4>
								<p><?php echo esc_html__( 'By enabling this feature, the first row containing the header (or title) information will remain fixed at the top of the sheet even while scrolling down, providing easy access to essential details.', 'wpssw' ); ?></p>
							</div>
							<div class="generalSetting-right">
								<label for="freeze_header">
									<input name="freeze_header" id="freeze_header" type="checkbox" class="" value="yes" 
									<?php
									if ( 'yes' === (string) $freeze_header ) {
										echo 'checked';}
									?>
									><span class="checkbox-switch"></span>
								</label>
							</div>
						</div>
						<div class="generalSetting-section">
							<div class="generalSetting-left">
								<?php
								wp_nonce_field( 'save_general_settings', 'wpssw_general_settings' );
								$wpssw_color_code_enable = self::wpssw_option( 'wpssw_color_code' );
								$wpssw_oddcolor          = self::wpssw_option( 'wpssw_oddcolor' );
								$wpssw_evencolor         = self::wpssw_option( 'wpssw_evencolor' );
								$wpssw_inputoption       = self::wpssw_option( 'wpssw_inputoption' );
								if ( ! $wpssw_inputoption ) {
									$wpssw_inputoption = 'USER_ENTERED';
								}
								$wpssw_user = '';
								$wpssw_raw  = '';
								if ( 'USER_ENTERED' === (string) $wpssw_inputoption ) {
									$wpssw_user = 'selected';
								} else {
									$wpssw_raw = 'selected';
								}
								?>
								<h4><?php echo esc_html__( 'Row Background Color', 'wpssw' ); ?></h4>
								<p><?php echo esc_html__( 'The background color of rows is automatically determined based on their respective ID. Alternating colors are assigned to create a visually pleasing and organized appearance.', 'wpssw' ); ?></p>
								<div class="bg-color-oddeven">
								<input type="color" id="color_code_odd" name="oddcolor" value="<?php echo esc_attr( $wpssw_oddcolor ); ?>"><label for="color_code_odd"><?php echo esc_html__( ' Odd Rows', 'wpssw' ); ?></label> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
								<input type="color" id="color_code_even" name="evencolor" value="<?php echo esc_attr( $wpssw_evencolor ); ?>"><label for="color_code_even"><?php echo esc_html__( ' Even Rows', 'wpssw' ); ?></label>
								</div>
							</div>
							<div class="generalSetting-right">				
								<label for="color_code">
									<input name="color_code" id="color_code" type="checkbox" class="" value="1" 
									<?php
									if ( $wpssw_color_code_enable ) {
										echo 'checked';}
									?>
									><span class="checkbox-switch"></span>
								</label>
							</div>
						</div>
						<div class="generalSetting-section">
							<div class="generalSetting-left">
								<h4><?php echo esc_html__( 'Row Input Format Option', 'wpssw' ); ?></h4>
								<p><?php echo esc_html__( 'This option allows you to specify how the input data should be interpreted. For further information, refer to the provided Link for more details,', 'wpssw' ); ?><a href="<?php echo esc_url( 'https://developers.google.com/sheets/api/reference/rest/v4/ValueInputOption', 'wpssw' ); ?>" target="_blank"> <?php echo esc_html__( 'click here.', 'wpssw' ); ?></a></p>
							</div>
							<div class="generalSetting-right">
								<select name="inputoption">
									<option value="USER_ENTERED" <?php echo esc_attr( $wpssw_user ); ?> >USER ENTERED</option>
									<option value="RAW" <?php echo esc_attr( $wpssw_raw ); ?>>RAW</option>
								</select>					
							</div>
						</div>
						<div class="generalSetting-section price-format-section">
							<div class="generalSetting-left">
								<?php
									$wpssw_price_format = self::wpssw_option( 'wpssw_price_format' );
								if ( ! $wpssw_price_format ) {
									$wpssw_price_format = 'plain';
								}
									$wpssw_plain     = '';
									$wpssw_formatted = '';
								if ( 'plain' === (string) $wpssw_price_format ) {
									$wpssw_plain = 'selected';
								} else {
									$wpssw_formatted = 'selected';
								}
								?>
								<h4><?php echo esc_html__( 'Price Format', 'wpssw' ); ?></h4>
								<p><?php echo esc_html__( 'Customize the format for displaying price values. If you choose the "Values with Symbol" option, a symbol will be appended to the price values, while the "Values" option will retain the entered values as they are.', 'wpssw' ); ?></p>
							</div>
							<div class="generalSetting-right">
								<select name="wpssw_price_format">
									<option value="plain" <?php echo esc_html( $wpssw_plain ); ?> ><?php echo esc_html__( 'Only Values', 'wpssw' ); ?></option>
									<option value="formatted" <?php echo esc_html( $wpssw_formatted ); ?>><?php echo esc_html__( 'Values with Symbol', 'wpssw' ); ?></option>
								</select>					
							</div>
						</div>
						<div class="generalSetting-section graphSheets-section">
							<div class="generalSetting-left">
								<?php $graph_sheet = self::wpssw_option( 'graph_sheet' ); ?>
								<h4><?php echo esc_html__( 'Graph Sheets', 'wpssw' ); ?></h4>
								<p><?php echo esc_html__( 'With the settings provided, Google Spreadsheet will automatically generate graph sheets according to your preferences. Clicking the "Regenerate" button will create a new graph with the latest data, keeping your visuals up-to-date.', 'wpssw' ); ?></p>
								<?php WPSSW_Order::wpssw_woocommerce_admin_field_add_graph_section(); ?>
							</div>
							<div class="generalSetting-right">				
								<label for="graph_sheet">
									<input name="graph_sheet" id="graph_sheet" type="checkbox" class="" value="yes" 
									<?php
									if ( 'yes' === (string) $graph_sheet ) {
										echo 'checked';}
									?>
									><span class="checkbox-switch"></span>
								</label>
							</div>
						</div>
						<div class="submit-section">
							<p class="submit"><input type="submit" name="submit" id="submit" class="wpssw-button wpssw-button-primary" value="Save"></p>	
						</div>
					</form>
				</div>
			</div>
	</div>
	<?php } ?>
	<div id="wpssw-nav-license" class="navtabcontent vertical-tabs">
		<div id="nav-license-content" class="wpassw-navtabcontent nav-license-content">
			<div class="generalSetting-section">
				<div class="generalSetting-left">
					<h4><?php echo esc_html__( 'License', 'wpssw' ); ?></h4>
					<p><?php echo esc_html__( 'WPSyncSheets does not collect any personal data. However, we gather some basic information about your website to validate your license key.', 'wpssw' ); ?></p>
				</div>
			</div>
			<div class="generalSetting-section">
				<div class="wpssw-license-content">
					<?php if ( ! $wpssw_license ) { ?>
					<p><?php echo esc_html__( "To unlock updates, please enter your license key below. If you don't have a licence key, please see ", 'wpssw' ); ?><a href="https://www.wpsyncsheets.com/pricing/" target="_blank"><?php echo esc_html__( 'details & pricing.', 'wpssw' ); ?></a></p>
					<?php } ?>
					<div class="integrationtabform">
						<form method="post" action="<?php echo esc_html( admin_url( 'admin.php?page=wpsyncsheets-for-woocommerce&tab=wpssw-nav-license' ) ); ?>">
							<?php
							if ( isset( $_GET['sl_activation'] ) && isset( $_GET['message'] ) && ! empty( sanitize_text_field( wp_unslash( $_GET['message'] ) ) ) ) {
								switch ( $_GET['sl_activation'] ) {
									case 'false':
										$message     = urldecode( sanitize_text_field( wp_unslash( $_GET['message'] ) ) );
										$wpssw_error = esc_html( $message );
										break;
									case 'true':
									default:
										break;
								}
							}
							?>
							<ul class="form-table">
									<li>
										<label>
											<?php echo esc_html__( 'License Key', 'wpssw' ); ?>
										</label>
										<div class="forminp-text">
											<?php
											$readonly = false;
											if ( 'activated' === (string) $licence_activated ) {
												$readonly = true;
											}
											?>
											<input id="wpssw_license_key" name="wpssw_license_key" type="text" class="regular-text" value="<?php echo esc_attr( $wpssw_license ); ?>" placeholder="<?php echo esc_html__( 'Enter your license key', 'wpssw' ); ?>" <?php echo $readonly ? 'readonly' : ''; ?>/>
										</div>
									</li>
									<li>
										<div class="active-btnset">
											<?php if ( 'activated' === (string) $licence_activated ) : ?>
												<span class="wpssw-license-status"><?php echo esc_html__( 'Active', 'wpssw' ); ?></span>
												<?php wp_nonce_field( 'wpssw_edd_nonce', 'wpssw_edd_nonce' ); ?>
												<input type="submit" class="wpssw-button wpssw-button-primary activate-toggle-btn" name="wpssw_license_deactivate" value="<?php echo esc_html__( 'Deactivate License', 'wpssw' ); ?>"/>
												<?php if ( ! empty( $wpssw_license ) ) { ?>
													<input type="submit" name="reset_license_key_settings" id="reset_license_key_settings" value = "Reset" class="wpssw-button wpssw-button-primary reset_settings"/>
												<?php } ?>
											<?php else : ?>
												<?php wp_nonce_field( 'wpssw_edd_nonce', 'wpssw_edd_nonce' ); ?>
												<input type="submit" class="wpssw-button wpssw-button-primary" name="wpssw_license_activate" value="<?php echo esc_html__( 'Activate License', 'wpssw' ); ?>"/>
											<?php endif; ?>
										</div>
									</li>
							</ul>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
		<?php

		if ( ! empty( $wpssw_error ) || ! empty( $wpssw_error_general ) || ! \WPSSW_Dependencies::wpssw_is_woocommerce_active() ) {
			$error_msg = '';
			if ( ! \WPSSW_Dependencies::wpssw_is_woocommerce_active() ) {
				$error_msg = 'WPSyncSheets For WooCommerce plugin requires <a href="http://wordpress.org/extend/plugins/woocommerce/">WooCommerce</a> plugin to be active!';
				?>
			<input type="hidden" id="error-message" value="<?php echo esc_attr( $error_msg ); ?>">
				<?php
			} else {
				if ( isset( $_GET['sl_activation'] ) && 'false' === sanitize_text_field( wp_unslash( $_GET['sl_activation'] ) ) && isset( $_GET['message'] ) && ! empty( sanitize_text_field( wp_unslash( $_GET['message'] ) ) ) ) {
					$error_msg = urldecode( sanitize_text_field( wp_unslash( $_GET['message'] ) ) );
				} elseif ( 'activated' === (string) $licence_activated ) {
					if ( ! empty( $wpssw_error_general ) ) {
						$error_msg = wp_strip_all_tags( $wpssw_error_general );
					} else {
						$error_msg = wp_strip_all_tags( $wpssw_error );
					}
				}
				if ( $error_msg ) {
					?>
				<input type="hidden" id="error-message" value="<?php echo esc_attr( $error_msg ); ?>">
					<?php
				}
			}
			?>
		<?php } elseif ( isset( $_POST['submit'] ) ) { ?>
		<input type="hidden" id="success-message" value="Settings are saved successfully.">
			<?php
		}
		$token_error_val = 0;
		if ( 'activated' !== (string) $licence_activated && ! isset( $_GET['sl_activation'] ) && ! isset( $_GET['message'] ) ) {
			?>
		<input type="hidden" id="info-message" value="Please activate your license key.">
			<?php
		} elseif ( 'activated' === (string) $licence_activated && $wpssw_token_error ) {
			$token_error_val = 1;
		}
		?>
	<input type="hidden" id="token-error" value="<?php echo esc_attr( $token_error_val ); ?>">
	<script type="text/javascript">
		jQuery.noConflict();
		jQuery(".wpssw-header-section .whatsNew-toggle, .wpssw-header-section .close-block").click(function(){
			jQuery(".wpssw-header-section .wpssw-header-right").toggleClass('active');
			jQuery(".wpssw-header-section .whatsNew-toggle").toggleClass('active');
		});
	</script>
		<?php
	}
	/**
	 *
	 * Move a post or product to the Trash
	 *
	 * @param int $wpssw_order_id .
	 */
	public static function wpssw_wcgs_trash( $wpssw_order_id ) {

		$wpssw_order   = wc_get_order( $wpssw_order_id );
		$wpssw_product = wc_get_product( $wpssw_order_id );
		$wpssw_coupon  = new WC_Coupon( $wpssw_order_id );
		$wpssw_rsync   = apply_filters( 'wpssw_realtime_sync', false );

		if ( isset( $wpssw_order ) && ! empty( $wpssw_order ) ) {
			global $post_type;
			$wpssw_post_type                 = is_object( $post_type ) ? $post_type->name : $post_type;
			$wpssw_order_spreadsheet_setting = WPSSW_Order::wpssw_check_order_spreadsheet_setting();
			if ( 'yes' !== (string) $wpssw_order_spreadsheet_setting || 'shop_order' !== (string) $wpssw_post_type ) {
				return;
			}
			// @codingStandardsIgnoreStart.
			if ( $wpssw_rsync && isset( $_GET['action'] ) && 'trash' === sanitize_text_field( wp_unslash( $_GET['action'] ) ) ) {
				// @codingStandardsIgnoreEnd.
				return;
			}

			$wpssw_sheets = array_filter( (array) self::wpssw_option( 'wpssw_sheets' ) );
			if ( ! $wpssw_sheets ) {
				$wpssw_sheets = WPSSW_Order::wpssw_prepare_sheets();
			}
			if ( $wpssw_order ) {
				$wpssw_old_status = $wpssw_order->get_status();

				/*Remove order detail from old status and move to Trash sheet*/
				WPSSW_Order::wpssw_woo_order_status_change_custom( $wpssw_order_id, $wpssw_old_status, 'trash' );
			}
			// phpcs:ignore.
			if ( array_key_exists( 'All Orders', $wpssw_sheets ) && isset( $_REQUEST['post'] ) && ! is_array( $_REQUEST['post'] ) && ! isset( $_REQUEST['paged'] ) ) {
				$wpssw_sheetname = $wpssw_sheets['All Orders'];
				WPSSW_Order::wpssw_all_orders( $wpssw_order_id, $wpssw_sheetname, 'trash' );
			}
		}
		if ( isset( $wpssw_product ) && ! empty( $wpssw_product ) ) {
			$wpssw_product_spreadsheet_setting = self::wpssw_option( 'wpssw_product_spreadsheet_setting' );
			if ( 'yes' !== (string) $wpssw_product_spreadsheet_setting ) {
				return;
			}

			// @codingStandardsIgnoreStart.
			if ( $wpssw_rsync && isset( $_GET['action'] ) && 'trash' === sanitize_text_field( wp_unslash( $_GET['action'] ) ) ) {
				// @codingStandardsIgnoreEnd.
				return;
			}

			$wpssw_spreadsheetid = self::wpssw_option( 'wpssw_product_spreadsheet_id' );
			$wpssw_sheetname     = 'All Products';

			// @codingStandardsIgnoreStart.
			if ( ( isset( $_REQUEST['post'] ) && is_array( $_REQUEST['post'] ) && count( $_REQUEST['post'] ) > 0 && isset( $_REQUEST['paged'] ) && ! empty( sanitize_text_field( wp_unslash( $_REQUEST['paged'] ) ) ) ) ) {
				$changed_posts = array_map( 'sanitize_text_field', wp_unslash( $_REQUEST['post'] ) );
				
				// @codingStandardsIgnoreEnd.
				if ( (int) $wpssw_order_id === (int) $changed_posts[ count( $changed_posts ) - 1 ] ) {

					sort( $changed_posts );
					$settings = array(
						'setting'        => 'product',
						'setting_enable' => $wpssw_product_spreadsheet_setting,
						'spreadsheet_id' => $wpssw_spreadsheetid,
						'sheetname'      => $wpssw_sheetname,
					);
					self::wpssw_multiple_update_data( $changed_posts, $settings, false, 'delete' );
				}
				return;
			}

			if ( ! empty( $wpssw_spreadsheetid ) ) {

				$wpssw_spreadsheets_list = self::$instance_api->get_spreadsheet_listing();
				if ( empty( $wpssw_spreadsheetid ) || ! array_key_exists( $wpssw_spreadsheetid, $wpssw_spreadsheets_list ) ) {
					return;
				}

				if ( ! empty( $wpssw_product->get_parent_id() ) && 'variation' === (string) $wpssw_product->get_type() ) {
					$wpssw_order_id = $wpssw_product->get_parent_id();
				}

				$response             = self::$instance_api->get_sheet_listing( $wpssw_spreadsheetid );
				$wpssw_existingsheets = self::$instance_api->get_sheet_list( $response );

				$wpssw_product_setting_sheets = array_filter( (array) self::wpssw_option( 'wpssw_product_setting_sheets' ) );
				if ( ! $wpssw_product_setting_sheets ) {
					$wpssw_product_setting_sheets = array( 'all_products' => 'All Products' );
				}

				$wpssw_product_categories_as_sheet = self::wpssw_option( 'product_categories_as_sheet' );
				$wpssw_productsheets_list          = array();

				$wpssw_activesheets = array();

				foreach ( $wpssw_product_setting_sheets as $sheet_slug => $sheet_name ) {
					if ( ! empty( $sheet_name ) && false !== array_key_exists( $sheet_name, $wpssw_existingsheets ) ) {
						$wpssw_activesheets[ $sheet_slug ] = $sheet_name;
					}
				}
				if ( 'yes' === (string) $wpssw_product_categories_as_sheet ) {
					$wpssw_productsheets_list = self::wpssw_get_product_category_saved_sheets();

					foreach ( $wpssw_productsheets_list as $cat_id => $cat_name ) {
						if ( ! empty( $cat_name ) && false !== array_key_exists( $cat_name, $wpssw_existingsheets ) ) {
							$wpssw_activesheets[ $cat_id ]           = $cat_name;
							$wpssw_product_setting_sheets[ $cat_id ] = $cat_name;
						}
					}
				}
				if ( count( $wpssw_activesheets ) > 1 ) {
					$settings_data = array(
						'setting'             => 'product',
						'setting_enable'      => $wpssw_product_spreadsheet_setting,
						'spreadsheet_id'      => $wpssw_spreadsheetid,
						'sheetname'           => 'All Products',
						'existingsheetsnames' => $wpssw_existingsheets,
						'activesheets'        => $wpssw_activesheets,
					);
					self::wpssw_multiple_sheets_update_data( array( $wpssw_order_id ), $settings_data, false, 'delete' );
					return;
				}

				if ( empty( $wpssw_sheetname ) || false === array_key_exists( $wpssw_sheetname, $wpssw_existingsheets ) ) {
					return;
				}
				$wpssw_total = self::$instance_api->get_row_list( $wpssw_spreadsheetid, $wpssw_sheetname );

				$wpssw_sheetid = $wpssw_existingsheets[ $wpssw_sheetname ];

				$wpssw_total_values      = $wpssw_total->getValues();
				$variation_product_id    = array_search( 'Product Id', $wpssw_total_values[0], true );
				$variation_product_index = array_column( $wpssw_total_values, $variation_product_id );
				foreach ( $variation_product_index as $index => $index_ids ) {
					if ( ! isset( $index_ids['0'] ) ) {
						unset( $variation_product_index[ $index ] );
					}
				}
				$product_keys = array_keys( self::wpssw_convert_int( $variation_product_index ), (int) $wpssw_order_id, true );
				if ( $wpssw_sheetid ) {
					$startindex           = $product_keys[0];
					$endindex             = $product_keys[0] + count( $product_keys );
					$param                = array();
					$param                = self::$instance_api->prepare_param( $wpssw_sheetid, $startindex, $endindex );
					$deleterequestarray[] = self::$instance_api->deleteDimensionrequests( $param, 'ROWS' );
				}
				try {
					if ( ! empty( $deleterequestarray ) ) {
						$param                  = array();
						$param['spreadsheetid'] = $wpssw_spreadsheetid;
						$param['requestarray']  = $deleterequestarray;
						self::$instance_api->updatebachrequests( $param );
					}
				} catch ( Exception $e ) {
					echo esc_html( 'Message: ' . $e->getMessage() );
				}
			}
		}
		if ( ! empty( $wpssw_coupon ) && empty( $wpssw_order ) && empty( $wpssw_product ) ) {
			$wpssw_spreadsheetid              = self::wpssw_option( 'wpssw_coupon_spreadsheet_id' );
			$wpssw_sheetname                  = 'All Coupons';
			$wpssw_coupon_spreadsheet_setting = self::wpssw_option( 'wpssw_coupon_spreadsheet_setting' );
			if ( 'yes' !== (string) $wpssw_coupon_spreadsheet_setting ) {
				return;
			}

			// @codingStandardsIgnoreStart.
			if ( ( isset( $_REQUEST['post'] ) && is_array( $_REQUEST['post'] ) && count( $_REQUEST['post'] ) > 0 && isset( $_REQUEST['paged'] ) && ! empty( sanitize_text_field( wp_unslash( $_REQUEST['paged'] ) ) ) ) ) {
				$changed_posts = array_map( 'sanitize_text_field', wp_unslash( $_REQUEST['post'] ) );
				
				// @codingStandardsIgnoreEnd.
				if ( (int) $wpssw_order_id === (int) $changed_posts[ count( $changed_posts ) - 1 ] ) {

					$settings = array(
						'setting'        => 'coupon',
						'setting_enable' => $wpssw_coupon_spreadsheet_setting,
						'spreadsheet_id' => $wpssw_spreadsheetid,
						'sheetname'      => $wpssw_sheetname,
					);
					self::wpssw_multiple_update_data( $changed_posts, $settings, false, 'delete' );

				}
				return;
			}

			if ( ! empty( $wpssw_spreadsheetid ) ) {
				if ( ! self::wpssw_check_sheet_exist( $wpssw_spreadsheetid, $wpssw_sheetname ) ) {
					return;
				}
				$wpssw_allentry            = self::$instance_api->get_row_list( $wpssw_spreadsheetid, $wpssw_sheetname );
				$wpssw_data                = $wpssw_allentry->getValues();
				$wpssw_data                = array_map(
					function( $wpssw_element ) {
						if ( isset( $wpssw_element['0'] ) ) {
							return $wpssw_element['0'];
						} else {
							return '';
						}
					},
					$wpssw_data
				);
				$response                  = self::$instance_api->get_sheet_listing( $wpssw_spreadsheetid );
				$wpssw_existingsheetsnames = self::$instance_api->get_sheet_list( $response );
				$wpssw_sheetid             = $wpssw_existingsheetsnames[ $wpssw_sheetname ];
				$wpssw_num                 = array_search( (int) $wpssw_order_id, self::wpssw_convert_int( $wpssw_data ), true );
				if ( $wpssw_num > 0 ) {
					$wpssw_startindex       = $wpssw_num;
					$wpssw_endindex         = $wpssw_num + 1;
					$param                  = array();
					$param                  = self::$instance_api->prepare_param( $wpssw_sheetid, $wpssw_startindex, $wpssw_endindex );
					$wpssw_requestbody      = self::$instance_api->deleteDimensionrequests( $param, 'ROWS' );
					$param                  = array();
					$param['spreadsheetid'] = $wpssw_spreadsheetid;
					$param['requestarray']  = $wpssw_requestbody;
					self::$instance_api->updatebachrequests( $param );
				}
			}
		}
	}
	/**
	 * Untrash a products from the Trash
	 *
	 * @param int    $post_id .
	 * @param string $previous_status .
	 */
	public static function wpssw_wcgs_untrash( $post_id, $previous_status ) {
		$wpssw_product = wc_get_product( $post_id );
		$wpssw_coupon  = new WC_Coupon( $post_id );
		$wpssw_rsync   = apply_filters( 'wpssw_realtime_sync', false );

		if ( isset( $wpssw_product ) && ! empty( $wpssw_product ) ) {

			global $post_type;
			$wpssw_post_type = is_object( $post_type ) ? $post_type->name : $post_type;
			// @codingStandardsIgnoreStart.
			if ( ( 'product' !== (string) $wpssw_post_type ) || ( isset( $_REQUEST['action'] ) && 'untrash' !== sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) ) ) {
				// @codingStandardsIgnoreEnd.
				return;
			}
			if ( 0 !== (int) $wpssw_product->get_parent_id() ) {
				return;
			}
			$wpssw_product_spreadsheet_setting = self::wpssw_option( 'wpssw_product_spreadsheet_setting' );
			if ( 'yes' !== (string) $wpssw_product_spreadsheet_setting ) {
				return;
			}

			// @codingStandardsIgnoreStart.
			if ( $wpssw_rsync && isset( $_GET['action'] ) && 'untrash' === sanitize_text_field( wp_unslash( $_GET['action'] ) ) ) {
				// @codingStandardsIgnoreEnd.
				return;
			}

			// @codingStandardsIgnoreStart.
			if ( ( isset( $_REQUEST['post'] ) && is_array( $_REQUEST['post'] ) && count( $_REQUEST['post'] ) > 0 && isset( $_REQUEST['paged'] ) && ! empty( sanitize_text_field( wp_unslash( $_REQUEST['paged'] ) ) ) ) || ( isset( $_REQUEST['doaction'] ) && 'undo' === sanitize_text_field( wp_unslash( $_REQUEST['doaction'] ) ) && isset( $_REQUEST['ids'] ) && ! empty( $_REQUEST['ids'] ) ) ) {
				if ( isset( $_REQUEST['doaction'] ) && 'undo' === sanitize_text_field( wp_unslash( $_REQUEST['doaction'] ) ) && isset( $_REQUEST['ids'] ) && ! empty( $_REQUEST['ids'] ) ) {
					$changed_posts = explode( ',', sanitize_text_field( wp_unslash( $_REQUEST['ids'] ) ) );
				} else {
					$changed_posts = array_map( 'sanitize_text_field', wp_unslash( $_REQUEST['post'] ) );
				}
				
				// @codingStandardsIgnoreEnd.
				if ( (int) $post_id === (int) $changed_posts[ count( $changed_posts ) - 1 ] ) {
					$wpssw_spreadsheetid = self::wpssw_option( 'wpssw_product_spreadsheet_id' );
					$wpssw_sheetname     = 'All Products';
					$settings            = array(
						'setting'        => 'product',
						'setting_enable' => $wpssw_product_spreadsheet_setting,
						'spreadsheet_id' => $wpssw_spreadsheetid,
						'sheetname'      => $wpssw_sheetname,
					);
					self::wpssw_multiple_update_data( $changed_posts, $settings, false, 'update' );
				}
				return;
			}
			$wpssw_product_setting_sheets = array_filter( (array) self::wpssw_option( 'wpssw_product_setting_sheets' ) );
			if ( ! $wpssw_product_setting_sheets ) {
				$wpssw_product_setting_sheets = array( 'all_products' => 'All Products' );
			}

			$wpssw_product_categories_as_sheet = self::wpssw_option( 'product_categories_as_sheet' );
			$wpssw_productsheets_list          = array();

			$wpssw_activesheets = array();

			foreach ( $wpssw_product_setting_sheets as $sheet_slug => $sheet_name ) {
				if ( ! empty( $sheet_name ) && false !== array_key_exists( $sheet_name, $wpssw_existingsheets ) ) {
					$wpssw_activesheets[ $sheet_slug ] = $sheet_name;
				}
			}
			if ( 'yes' === (string) $wpssw_product_categories_as_sheet ) {
				$wpssw_productsheets_list = self::wpssw_get_product_category_saved_sheets();
				foreach ( $wpssw_productsheets_list as $cat_id => $cat_name ) {
					if ( ! empty( $cat_name ) && false !== array_key_exists( $cat_name, $wpssw_existingsheets ) ) {
						$wpssw_activesheets[ $cat_id ]           = $cat_name;
						$wpssw_product_setting_sheets[ $cat_id ] = $cat_name;
					}
				}
			}
			if ( count( $wpssw_activesheets ) > 1 ) {
				$wpssw_spreadsheetid = self::wpssw_option( 'wpssw_product_spreadsheet_id' );

				$wpssw_spreadsheets_list = self::$instance_api->get_spreadsheet_listing();
				if ( empty( $wpssw_spreadsheetid ) || ! array_key_exists( $wpssw_spreadsheetid, $wpssw_spreadsheets_list ) ) {
					return;
				}

				$response             = self::$instance_api->get_sheet_listing( $wpssw_spreadsheetid );
				$wpssw_existingsheets = self::$instance_api->get_sheet_list( $response );
				$settings_data        = array(
					'setting'             => 'product',
					'setting_enable'      => $wpssw_product_spreadsheet_setting,
					'spreadsheet_id'      => $wpssw_spreadsheetid,
					'sheetname'           => 'All Products',
					'existingsheetsnames' => $wpssw_existingsheets,
					'activesheets'        => $wpssw_activesheets,
				);
				self::wpssw_multiple_sheets_update_data( array( $post_id ), $settings_data, false, 'update' );
				return;
			}
			WPSSW_Product::wpssw_woocommerce_update_product( $post_id, $wpssw_product );

		}
		if ( ! empty( $wpssw_coupon ) && empty( $wpssw_product ) ) {
			global $post_type;
			$wpssw_post_type = is_object( $post_type ) ? $post_type->name : $post_type;
			// @codingStandardsIgnoreStart.
			if ( ( 'shop_coupon' !== (string) $wpssw_post_type ) || ( isset( $_REQUEST['action'] ) && 'untrash' !== sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) ) ) {
				// @codingStandardsIgnoreEnd.
				return;
			}
			$wpssw_coupon_spreadsheet_setting = self::wpssw_option( 'wpssw_coupon_spreadsheet_setting' );
			if ( 'yes' !== (string) $wpssw_coupon_spreadsheet_setting ) {
				return;
			}

			$wpssw_spreadsheetid = self::wpssw_option( 'wpssw_coupon_spreadsheet_id' );
			$wpssw_sheetname     = 'All Coupons';

			// @codingStandardsIgnoreStart.
			if ( ( isset( $_REQUEST['post'] ) && is_array( $_REQUEST['post'] ) && count( $_REQUEST['post'] ) > 0 && isset( $_REQUEST['paged'] ) && ! empty( sanitize_text_field( wp_unslash( $_REQUEST['paged'] ) ) ) ) || ( isset( $_REQUEST['doaction'] ) && 'undo' === sanitize_text_field( wp_unslash( $_REQUEST['doaction'] ) ) && isset( $_REQUEST['ids'] ) && ! empty( $_REQUEST['ids'] ) ) ) {
				if ( isset( $_REQUEST['doaction'] ) && 'undo' === sanitize_text_field( wp_unslash( $_REQUEST['doaction'] ) ) && isset( $_REQUEST['ids'] ) && ! empty( $_REQUEST['ids'] ) ) {
					$changed_posts = explode( ',', sanitize_text_field( wp_unslash( $_REQUEST['ids'] ) ) );
				} else {
					$changed_posts = array_map( 'sanitize_text_field', wp_unslash( $_REQUEST['post'] ) );
				}
				
				// @codingStandardsIgnoreEnd.
				if ( (int) $post_id === (int) $changed_posts[ count( $changed_posts ) - 1 ] ) {

					$settings = array(
						'setting'        => 'coupon',
						'setting_enable' => $wpssw_coupon_spreadsheet_setting,
						'spreadsheet_id' => $wpssw_spreadsheetid,
						'sheetname'      => $wpssw_sheetname,
					);
					self::wpssw_multiple_update_data( $changed_posts, $settings, false, 'update' );

				}
				return;
			}
			if ( ! self::wpssw_check_sheet_exist( $wpssw_spreadsheetid, $wpssw_sheetname ) ) {
				return;
			}
			WPSSW_Coupon::wpssw_coupon_object_updated_props( $wpssw_coupon );
		}
	}
	/**
	 * Convert a multi-dimensional array into a single-dimensional array.
	 *
	 * @param array $wpssw_array .
	 * @return array
	 */
	public static function wpssw_array_flatten( $wpssw_array ) {
		if ( ! is_array( $wpssw_array ) ) {
			return false;
		}
		$wpssw_result = array();
		foreach ( $wpssw_array as $wpssw_key => $wpssw_value ) {
			if ( is_array( $wpssw_value ) ) {
				$wpssw_result = array_merge( $wpssw_result, self::wpssw_array_flatten( array_values( $wpssw_value ) ) );
			} else {
				$wpssw_result[ $wpssw_key ] = trim( $wpssw_value );
			}
		}
		return $wpssw_result;
	}

	/**
	 * Convert a multi-dimensional array into a single-dimensional array.
	 *
	 * @param array $wpssw_array .
	 * @return array
	 */
	public static function wpssw_productarray_flatten( $wpssw_array ) {
		if ( ! is_array( $wpssw_array ) ) {
			return false;
		}
		$wpssw_result = array();
		foreach ( $wpssw_array as $wpssw_key => $wpssw_value ) {
			if ( is_array( $wpssw_value ) ) {
				$wpssw_result = $wpssw_result + self::wpssw_array_flatten( $wpssw_value );
			} else {
				$wpssw_result[ $wpssw_key ] = trim( $wpssw_value );
			}
		}
		return $wpssw_result;
	}
	/**
	 * Function to check string starting with given substring or not
	 *
	 * @param string $wpssw_string .
	 * @param string $wpssw_startstring .
	 */
	public static function wpssw_startswith( $wpssw_string, $wpssw_startstring ) {
		$wpssw_len = strlen( $wpssw_startstring );
		return ( substr( $wpssw_string, 0, $wpssw_len ) === $wpssw_startstring );
	}
	/**
	 * Delete sheets from spreadsheet
	 *
	 * @param string $wpssw_spreadsheetid .
	 * @param array  $wpssw_remove_sheet .
	 * @param array  $wpssw_existingsheets .
	 */
	public static function wpssw_delete_sheet( $wpssw_spreadsheetid, $wpssw_remove_sheet = array(), $wpssw_existingsheets = array() ) {
		foreach ( $wpssw_remove_sheet as $wpssw_sheetname ) {
			$wpssw_sid = array_search( $wpssw_sheetname, $wpssw_existingsheets, true );
			try {
				$param                  = array();
				$param['spreadsheetid'] = $wpssw_spreadsheetid;
				$wpssw_response         = self::$instance_api->deletesheetobject( $param, $wpssw_sid );
			} catch ( Exception $e ) {
				echo esc_html( 'Message: ' . $e->getMessage() );
			}
		}
	}
	/**
	 * Freeze the headers of the sheet
	 *
	 * @param string $wpssw_spreadsheetid .
	 * @param int    $wpssw_freeze .
	 * @param string $oddcolor .
	 * @param string $evencolor .
	 * @param string $wpssw_color .
	 * @param int    $wpssw_freeze_header .
	 * @param bool   $rowcolor_disabled .
	 * @param array  $wpssw_sheets .
	 */
	public static function wpssw_freeze_header( $wpssw_spreadsheetid, $wpssw_freeze, $oddcolor, $evencolor, $wpssw_color, $wpssw_freeze_header, $rowcolor_disabled = true, $wpssw_sheets = array() ) {

		$wpssw_response    = self::$instance_api->get_sheet_listing( $wpssw_spreadsheetid );
		$wpssw_requestbody = array();
		foreach ( $wpssw_response->getSheets() as $wpssw_key => $wpssw_value ) {
			// TODO: Assign values to desired properties of `requestBody`.
			if ( isset( $wpssw_value['properties']['title'] ) && in_array( $wpssw_value['properties']['title'], $wpssw_sheets, true ) ) {
				if ( $wpssw_freeze_header ) {
					$wpssw_requestbody[] = self::$instance_api->freezeobject( $wpssw_value['properties']['sheetId'], $wpssw_freeze );
				}
			}
		}
		if ( ! empty( $wpssw_requestbody ) ) {
			try {

				$requestbody                    = new Google_Service_Sheets_BatchUpdateSpreadsheetRequest(
					array( 'requests' => $wpssw_requestbody )
				);
				$requestobject                  = array();
				$requestobject['spreadsheetid'] = $wpssw_spreadsheetid;
				$requestobject['requestbody']   = $requestbody;
				self::$instance_api->formatsheet( $requestobject );
			} catch ( Exception $e ) {
				echo esc_html( 'Message: ' . $e->getMessage() );
			}
		}
		if ( $wpssw_color ) {
			self::wpssw_change_row_background_color( $wpssw_response->getSheets(), $wpssw_spreadsheetid, $wpssw_value['properties']['sheetId'], $oddcolor, $evencolor, $rowcolor_disabled, $wpssw_sheets );
		}
		// phpcs:ignore
		return;
	}
	/**
	 * Change the row's background color
	 *
	 * @param array  $wpssw_response .
	 * @param string $wpssw_spreadsheetid .
	 * @param int    $sheetid .
	 * @param string $oddcolor .
	 * @param string $evencolor .
	 * @param bool   $rowcolor_disabled .
	 * @param array  $wpssw_sheets .
	 */
	public static function wpssw_change_row_background_color( $wpssw_response, $wpssw_spreadsheetid, $sheetid, $oddcolor, $evencolor, $rowcolor_disabled, $wpssw_sheets ) {
		if ( ! self::$instance_api->checkcredenatials() ) {
			return;
		}

		list($r, $g, $b)    = array_map(
			function( $c ) {
				return hexdec( str_pad( $c, 2, $c ) );
			},
			str_split( ltrim( $oddcolor, '#' ), strlen( $oddcolor ) > 4 ? 2 : 1 )
		);
		list($er, $eg, $eb) = array_map(
			function( $c ) {
				return hexdec( str_pad( $c, 2, $c ) );
			},
			str_split( ltrim( $evencolor, '#' ), strlen( $evencolor ) > 4 ? 2 : 1 )
		);

		$request       = array();
		$colorrequests = array();
		foreach ( $wpssw_response as $wpssw_key => $wpssw_value ) {
			if ( isset( $wpssw_value['properties']['title'] ) && ! in_array( $wpssw_value['properties']['title'], $wpssw_sheets, true ) ) {
				continue;
			}

			// For Add Conditional Formatting.
			$range                  = array(
				'sheetId' => $wpssw_value['properties']['sheetId'],
			);
			$param                  = array();
			$param['spreadsheetid'] = $wpssw_spreadsheetid;
			$param['range']         = $range;
			$param['r']             = $r;
			$param['g']             = $g;
			$param['b']             = $b;
			$param['er']            = $er;
			$param['eg']            = $eg;
			$param['eb']            = $eb;
			$colorrequests[]        = self::$instance_api->addconditionalformatruleobject( $param );
			// END.

			// For Remove Conditional Formatting.
			// phpcs:ignore
			if ( ! isset( $wpssw_value->conditionalFormats ) ) {
				continue;
			}
			// phpcs:ignore
			$rules = $wpssw_value->conditionalFormats;

			foreach ( $rules as $rulekey => $rule ) {
				// @codingStandardsIgnoreStart.
				if ( isset( $rule->booleanRule->condition->values ) ) {
					$condition = $rule->booleanRule->condition->values;
					// @codingStandardsIgnoreEnd.
					foreach ( $condition as $key => $value ) {
						// phpcs:ignore
						if ( isset( $value->userEnteredValue ) && ( '=$A:$A=""' === trim( $value->userEnteredValue ) || trim( '=MOD($A:$A,2)=0' ) === $value->userEnteredValue || trim( '=MOD($A:$A,2)=1' ) === $value->userEnteredValue ) ) {
							$request[] = new Google_Service_Sheets_Request(
								array(
									'deleteConditionalFormatRule' => array(
										'sheetId' => $wpssw_value['properties']['sheetId'],
										'index'   => $rulekey,
									),
								)
							);
						}
					}
				}
			}
			// End.
		}

		// Remove Old Color Code.
		if ( ! empty( $request ) ) {
			try {
				$request            = array_reverse( $request );
				$batchupdaterequest = new Google_Service_Sheets_BatchUpdateSpreadsheetRequest(
					array(
						'requests' => array( $request ),
					)
				);
				self::$instance_api->updatebachrequestsrowcolor( $wpssw_spreadsheetid, $batchupdaterequest );
			} catch ( Exception $e ) {
				echo esc_html( 'Message: ' . $e->getMessage() );
			}
		}

		if ( $rowcolor_disabled ) {
			return;
		}
		try {
			if ( ! empty( $colorrequests ) ) {
				$batchupdaterequest = new Google_Service_Sheets_BatchUpdateSpreadsheetRequest(
					array(
						'requests' => $colorrequests,
					)
				);
				self::$instance_api->addconditionalformatrule( $wpssw_spreadsheetid, $batchupdaterequest );
			}
		} catch ( Exception $e ) {
			echo esc_html( 'Message: ' . $e->getMessage() );
		}
		// phpcs:ignore
		return;
	}
	/**
	 * Create new Spreadsheet
	 *
	 * @param string $wpssw_spreadsheetname .
	 */
	public static function wpssw_create_spreadsheet( $wpssw_spreadsheetname = '' ) {
		if ( ! empty( $wpssw_spreadsheetname ) ) {
			$wpssw_newsheetname = trim( $wpssw_spreadsheetname );

			/*
			*Create new spreadsheet
			*/
			$wpssw_requestbody   = self::$instance_api->createspreadsheetobject( $wpssw_newsheetname );
			$wpssw_response      = self::$instance_api->createspreadsheet( $wpssw_requestbody );
			$wpssw_spreadsheetid = $wpssw_response['spreadsheetId'];
			return $wpssw_spreadsheetid;
		}
		return '';
	}
	/**
	 * Get Sheet's Column index from total headers
	 *
	 * @param int $number .
	 * @return string $letter
	 */
	public static function wpssw_get_column_index( $number ) {
		if ( $number <= 0 ) {
			return null;
		}
		$temp;
		$letter = '';
		while ( $number > 0 ) {
			$temp   = ( $number - 1 ) % 26;
			$letter = chr( $temp + 65 ) . $letter;
			$number = ( $number - $temp - 1 ) / 26;
		}
		return $letter;
	}
	/**
	 * Get wpssw options from database
	 *
	 * @param string $key .
	 * @param string $type .
	 * @return string
	 */
	public static function wpssw_option( $key = '', $type = '' ) {
		$value = self::$instance_api->wpssw_option( $key, $type );
		return $value;
	}
	/**
	 * Update wpssw options
	 *
	 * @param string $key .
	 * @param string $value .
	 */
	public static function wpssw_update_option( $key = '', $value = '' ) {
		self::$instance_api->wpssw_update_option( $key, $value );
	}
	/**
	 * Reset Google API Settings
	 */
	public static function wpssw_reset_settings() {
		try {
			$wpssw_google_settings = self::wpssw_option( 'wpssw_google_settings' );
			$settings              = array();
			foreach ( $wpssw_google_settings as $key => $value ) {
				$settings[ $key ] = '';
			}
			self::wpssw_update_option( 'wpssw_google_settings', $settings );
			self::wpssw_update_option( 'wpssw_google_accessToken', '' );
			self::wpssw_update_option( 'wpssw_woocommerce_spreadsheet', '' );
			self::wpssw_update_option( 'wpssw_coupon_spreadsheet_id', '' );
			self::wpssw_update_option( 'wpssw_product_spreadsheet_id', '' );
			self::wpssw_update_option( 'wpssw_customer_spreadsheet_id', '' );
			self::wpssw_update_option( 'wpssw_event_spreadsheet_id', '' );
		} catch ( Exception $e ) {
			echo esc_html( 'Message: ' . $e->getMessage() );
		}
		echo 'successful';
		die();
	}
	/**
	 * Insert blank row into sheet
	 *
	 * @param string $wpssw_spreadsheetid .
	 * @param string $wpssw_sheetid .
	 * @param int    $product_id .
	 * @param array  $wpssw_array_value .
	 * @param array  $wpssw_data .
	 * @param int    $wpssw_startindex .
	 * @param string $wpssw_setting .
	 */
	public static function wpssw_insert_blankrow( $wpssw_spreadsheetid = '', $wpssw_sheetid = '', $product_id = 0, $wpssw_array_value = array(), $wpssw_data = array(), $wpssw_startindex = 0, $wpssw_setting = 'order' ) {
		if ( ! self::$instance_api->checkcredenatials() ) {
			return;
		}
		if ( isset( $wpssw_array_value[0] ) && ! is_array( $wpssw_array_value[0] ) ) {
			$wpssw_array_value = array( $wpssw_array_value );
		}
		$wpssw_inherit_style = self::wpssw_get_inherit_style( $wpssw_setting );

		foreach ( $wpssw_data as $wpssw_key => $wpssw_value ) {
			if ( ! empty( $wpssw_value ) ) {
				if ( ( (int) $product_id < (int) $wpssw_value ) ) {
					$wpssw_varicount  = count( $wpssw_array_value );
					$wpssw_startindex = $wpssw_key;
					$wpssw_endindex   = $wpssw_key + $wpssw_varicount;
					$param            = array();
					$param            = self::$instance_api->prepare_param( $wpssw_sheetid, $wpssw_startindex, $wpssw_endindex );

					if ( 'no' === (string) $wpssw_inherit_style ) {
						$requestarray[] = self::$instance_api->insertdimensionrequests( $param, 'ROWS', false );
					} else {
						$requestarray[] = self::$instance_api->insertdimensionrequests( $param, 'ROWS', true );
					}
					$param                  = array();
					$param['spreadsheetid'] = $wpssw_spreadsheetid;
					$param['requestarray']  = $requestarray;
					$wpssw_response         = self::$instance_api->updatebachrequests( $param );
					break;
				}
			}
		}
		return $wpssw_startindex;
	}
	/**
	 * Added V5.7
	 * Check if The Events Calendar PRO and Event Tickets Plus plugins are active or not.
	 */
	public static function wpssw_is_event_calender_ticket_active() {
		if ( ! class_exists( 'Tribe__Tickets_Plus__Main' ) || ! class_exists( 'Tribe__Events__Pro__Main' ) || ! class_exists( 'Tribe__Tickets__Main' ) || ! class_exists( 'Tribe__Events__Main' ) ) {
			return false;
		}
		return true;
	}
	/**
	 * Convert to Integer.
	 *
	 * @param string $data Entry ids array.
	 */
	public static function wpssw_convert_string( $data ) {
		$data = array_map(
			function( $element ) {
				return ( is_string( $element ) ? (string) $element : $element );
			},
			$data
		);
		return $data;
	}
	/**
	 * Convert to Integer.
	 *
	 * @param string $data Entry ids array.
	 */
	public static function wpssw_convert_int( $data ) {
		$data = array_map(
			function( $element ) {
				return ( is_numeric( $element ) ? (int) $element : $element );
			},
			$data
		);
		return $data;
	}
	/**
	 * Find Related Class.
	 *
	 * @param array  $headers Headers array.
	 * @param string $headername Header Name.
	 */
	public static function wpssw_find_class( $headers, $headername ) {
		foreach ( $headers as $classname => $classheaders ) {
			if ( in_array( $headername, $classheaders, true ) ) {
				return $classname;
			}
		}
		return '';
	}
	/**
	 * Clean Order data array.
	 *
	 * @param array $wpssw_array Order data array.
	 * @param int   $wpssw_max max value.
	 * @return array $wpssw_array
	 */
	public static function wpssw_cleanarray( $wpssw_array, $wpssw_max ) {
		for ( $i = 0; $i < $wpssw_max; $i++ ) {
			if ( ! isset( $wpssw_array[ $i ] ) || is_null( $wpssw_array[ $i ] ) ) {
				$wpssw_array[ $i ] = '';
			} else {
				$wpssw_array[ $i ] = trim( $wpssw_array[ $i ] );
			}
		}
		ksort( $wpssw_array );
		return $wpssw_array;
	}
	/**
	 * Check that given spreadsheet and sheet exists or not.
	 *
	 * @param string $wpssw_spreadsheetid Spreadsheet ID.
	 * @param string $wpssw_sheetname Sheet Name.
	 */
	public static function wpssw_check_sheet_exist( $wpssw_spreadsheetid, $wpssw_sheetname ) {
		$wpssw_spreadsheets_list = self::$instance_api->get_spreadsheet_listing();
		if ( empty( $wpssw_spreadsheetid ) || empty( $wpssw_sheetname ) ) {
			return false;
		}
		if ( ! array_key_exists( $wpssw_spreadsheetid, $wpssw_spreadsheets_list ) ) {
			return false;
		} else {
			$wpssw_response            = self::$instance_api->get_sheet_listing( $wpssw_spreadsheetid );
			$wpssw_existingsheetsnames = self::$instance_api->get_sheet_list( $wpssw_response );
			$wpssw_existingsheets      = array_flip( $wpssw_existingsheetsnames );
			if ( ! in_array( $wpssw_sheetname, $wpssw_existingsheets, true ) ) {
				return false;
			}
		}
		return true;
	}
	/**
	 * Activate License for site.
	 *
	 * @param string $data Posted Data.
	 */
	public static function wpssw_activate_license( $data = array() ) {
		// listen for our activate button to be clicked.

		if ( isset( $data['wpssw_license_activate'] ) ) {

			// run a quick security check.
			if ( ! check_admin_referer( 'wpssw_edd_nonce', 'wpssw_edd_nonce' ) ) {
				return; // get out if we didn't click the Activate button.
			}

			// retrieve the license from the database.
			$license = trim( self::wpssw_option( 'wpssw_license_key' ) );

			// data to send in our API request.
			$api_params = array(
				'edd_action' => 'activate_license',
				'license'    => $license,
				'item_name'  => rawurlencode( WPSSW_PLUGIN_NAME ),
				'url'        => home_url(),
			);

			// Call the custom API.
			$response = wp_remote_post(
				self::$wpssw_store_url,
				array(
					'timeout'   => 15,
					'sslverify' => false,
					'body'      => $api_params,
				)
			);

			// make sure the response came back okay.
			if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {

				$message = ( is_wp_error( $response ) && ! empty( $response->get_error_message() ) ) ? $response->get_error_message() : __( 'An error occurred, please try again.' );

			} else {

				$license_data = json_decode( wp_remote_retrieve_body( $response ) );

				if ( false === $license_data->success ) {

					switch ( $license_data->error ) {

						case 'expired':
							$message = sprintf(
								/* translators: expiry date. */
								__( 'Your license key expired on %s.' ),
								date_i18n( self::wpssw_option( 'date_format' ), strtotime( $license_data->expires, time() ) )
							);
							break;

						case 'revoked':
							$message = __( 'Your license key has been disabled.' );
							break;

						case 'missing':
							$message = __( 'Invalid license.' );
							break;

						case 'invalid':
						case 'site_inactive':
							$message = __( 'Your license is not active for this URL.' );
							break;

						case 'item_name_mismatch':
							/* translators: the plugin name. */
							$message = sprintf( __( 'This appears to be an invalid license key for %s.' ), 'wpssw' );
							break;

						case 'no_activations_left':
							$message = __( 'Your license key has reached its activation limit.' );
							break;

						default:
							$message = __( 'An error occurred, please try again.' );
							break;
					}
				}
			}

			// Check if anything passed on a message constituting a failure.
			if ( ! empty( $message ) ) {
				$base_url = admin_url( 'admin.php?page=' . self::$wpssw_license_page );
				$redirect = add_query_arg(
					array(
						'sl_activation' => 'false',
						'message'       => rawurlencode( $message ),
					),
					$base_url
				);

				wp_safe_redirect( $redirect );
				exit();
			}
			self::wpssw_update_option( 'wpssw_license_status', $license_data->license );
			wp_safe_redirect( admin_url( 'admin.php?page=' . self::$wpssw_license_page ) );
			exit();
		}
	}
	/**
	 * Deactivate License for site.
	 *
	 * @param bool $reset_setting .
	 */
	public static function wpssw_deactivate_license( $reset_setting = false ) {

		$license = trim( self::wpssw_option( 'wpssw_license_key' ) );
		// run a quick security check.
		if ( ! check_admin_referer( 'wpssw_edd_nonce', 'wpssw_edd_nonce' ) ) {
			return; // get out if we didn't click the Deactivate button.
		}
		// data to send in our API request.
		$api_params = array(
			'edd_action'  => 'deactivate_license',
			'license'     => $license,
			'item_id'     => WPSSW_PLUGIN_ITEM_ID,
			'item_name'   => rawurlencode( WPSSW_PLUGIN_NAME ),
			'url'         => home_url(),
			'environment' => function_exists( 'wp_get_environment_type' ) ? wp_get_environment_type() : 'production',
		);

		$response = wp_remote_post(
			self::$wpssw_store_url,
			array(
				'timeout'   => 15,
				'sslverify' => false,
				'body'      => $api_params,
			)
		);

		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {

			if ( is_wp_error( $response ) ) {
				$message = $response->get_error_message();
			} else {
				$message = __( 'An error occurred, please try again.' );
			}

			$redirect = add_query_arg(
				array(
					'page'          => self::$wpssw_license_page,
					'sl_activation' => 'false',
					'message'       => rawurlencode( $message ),
				),
				admin_url( 'admin.php?page=' )
			);

			wp_safe_redirect( $redirect );
			exit();
		}

		// decode the license data.
		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		if ( 'deactivated' === $license_data->license ) {
			delete_option( 'wpssw_license_status' );
		}
		if ( true === $reset_setting ) {
			return true;
		}
		wp_safe_redirect( admin_url( 'admin.php?page=' . self::$wpssw_license_page ) );
		exit();
	}

	/**
	 * Check License key.
	 */
	public static function wpssw_check_license_key() {

		$wpssw_license = self::wpssw_option( 'wpssw_license_key' );
		$wpssw_status  = self::wpssw_option( 'wpssw_license_status' );
		// Check for transient. If none, then execute EDD License Check Status.
		$license_key_status = get_transient( 'wpssw_check_license_key_status' );
		if ( false === $license_key_status ) {

			$wpssw_activestatus = self::wpssw_check_license_key_status( $wpssw_license );
			if ( false !== $wpssw_activestatus && 'valid' === (string) $wpssw_activestatus && ! empty( $wpssw_license ) ) {
				// Put the results in a transient. Expire after 14 Days.
				set_transient( 'wpssw_check_license_key_status', $wpssw_activestatus, 60 * 60 * 24 * 14 );
				return true;
			} elseif ( 'valid' !== (string) $wpssw_status && ! empty( $wpssw_status ) ) {
				self::wpssw_update_option( 'wpssw_license_key', '' );
				self::wpssw_update_option( 'wpssw_license_status', '' );
				return false;
			} else {
				return false;
			}
		} else {
			// Depends on transient it will be true.
			return true;
		}
	}

	/**
	 * Check Remoate License key status.
	 *
	 * @param string $wpssw_license license key.
	 */
	public static function wpssw_check_license_key_status( $wpssw_license ) {

		// data to send in our API request.
		$api_params = array(
			'edd_action' => 'check_license',
			'license'    => $wpssw_license,
			'item_id'    => WPSSW_PLUGIN_ITEM_ID,
			'item_name'  => rawurlencode( WPSSW_PLUGIN_NAME ),
			'url'        => home_url(),
		);

		$response = wp_remote_post(
			self::$wpssw_store_url,
			array(
				'timeout'   => 15,
				'sslverify' => false,
				'body'      => $api_params,
			)
		);

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$license_data = json_decode( wp_remote_retrieve_body( $response ) );
		return $license_data->license;
	}

	/**
	 * Get all meta values for post
	 *
	 * @param string $post_type post type for which metas need to fetch.
	 * @return array $wpssw_post_metakeys meta keys array
	 */
	public static function wpssw_get_all_post_metas( $post_type = 'product' ) {
		global $wpdb;
		$wpssw_post_metas    = array();
		$wpssw_post_metakeys = array();
		// @codingStandardsIgnoreStart.
		$wpssw_querystr  = "SELECT {$wpdb->prefix}postmeta.* FROM {$wpdb->prefix}postmeta INNER JOIN {$wpdb->prefix}posts ON ( {$wpdb->prefix}posts.ID = {$wpdb->prefix}postmeta.post_id ) WHERE 1=1 AND ( {$wpdb->prefix}postmeta.meta_key LIKE 'wpsyncsheets_%' ) AND {$wpdb->prefix}posts.post_type='$post_type' group by {$wpdb->prefix}postmeta.meta_key";
   		$wpssw_post_metas = $wpdb->get_results( $wpssw_querystr );// db call ok.
		// @codingStandardsIgnoreEnd.
		if ( ! empty( $wpssw_post_metas ) ) {
			foreach ( $wpssw_post_metas as $meta ) {
				$metakey = $meta->meta_key;
				if ( false !== strpos( $metakey, 'wpsyncsheets_' ) && 1 === strpos( $metakey, 'psyncsheets_' ) ) {
					if ( ! array_key_exists( $metakey, $wpssw_post_metakeys ) ) {
						$metakey_value                   = ucwords( str_replace( array( '-', '_' ), ' ', str_replace( 'wpsyncsheets_', '', $metakey ) ) );
						$wpssw_post_metakeys[ $metakey ] = $metakey_value;
					}
				}
			}
			$wpssw_post_metakeys = array_unique( $wpssw_post_metakeys );
		}
		return $wpssw_post_metakeys;
	}
	/**
	 * Create Start time for cron functions.
	 *
	 * @param string $interval .
	 * @return string $time meta starttime.
	 */
	public static function wpssw_cron_timeintervals( $interval ) {
		$time           = time();
		$cron_schedules = apply_filters( 'cron_schedules', array() );

		if ( array_key_exists( $interval, $cron_schedules ) ) {
			$time = time() + $cron_schedules[ $interval ]['interval'];
		} elseif ( 'daily' === (string) $interval ) {
			$time = time() + ( 60 * 60 * 24 );
		} elseif ( 'twicedaily' === (string) $interval ) {
			$time = time() + ( 60 * 60 * 12 );
		}
		return $time;
	}
	/**
	 * Check token error.
	 *
	 * @param bool $settings_page is it a plugin settings page or not by default false.
	 * @return string $wpssw_error return error.
	 */
	public static function wpssw_check_token_error( $settings_page = false ) {
		$wpssw_error = '';
		if ( ! $settings_page ) {
			$wpssw_token_error = self::wpssw_option( 'wpssw_token_error' );
			if ( ! empty( $wpssw_token_error ) && 0 === (int) $wpssw_token_error ) {
				return '';
			} elseif ( 1 === (int) $wpssw_token_error ) {
				return 'Error';
			}
		}
		if ( $settings_page ) {
			$wpssw_google_settings_value = self::wpssw_option( 'wpssw_google_settings' );
			if ( ! empty( $wpssw_google_settings_value[2] ) && ! self::$instance_api->checkcredenatials() ) {
				$wpssw_error = self::$instance_api->getClient( 1 );
				if ( 'Invalid token format' === (string) $wpssw_error ) {
					$wpssw_error = 'Error: Invalid Token - Revoke Token with below settings and try again.';
				} else {
					$wpssw_error = 'Error: ' . $wpssw_error;
				}
			}
		} else {
			if ( ! self::$instance_api->checkcredenatials() ) {
				$wpssw_error = 'Error';
			}
		}
		if ( empty( $wpssw_error ) ) {
			try {
				$spreadsheets_list = self::$instance_api->get_spreadsheet_listing();

				$wpssw_order_spreadsheetid     = self::wpssw_option( 'wpssw_woocommerce_spreadsheet' );
				$wpssw_product_spreadsheet_id  = self::wpssw_option( 'wpssw_product_spreadsheet_id' );
				$wpssw_coupon_spreadsheet_id   = self::wpssw_option( 'wpssw_coupon_spreadsheet_id' );
				$wpssw_customer_spreadsheet_id = self::wpssw_option( 'wpssw_customer_spreadsheet_id' );
				$wpssw_event_spreadsheet_id    = self::wpssw_option( 'wpssw_event_spreadsheet_id' );
				$spreadsheetid                 = '';
				if ( ! empty( $wpssw_order_spreadsheetid ) && array_key_exists( $wpssw_order_spreadsheetid, $spreadsheets_list ) ) {
					$spreadsheetid = $wpssw_order_spreadsheetid;
				} elseif ( ! empty( $wpssw_product_spreadsheet_id ) && array_key_exists( $wpssw_product_spreadsheet_id, $spreadsheets_list ) ) {
					$spreadsheetid = $wpssw_product_spreadsheet_id;
				} elseif ( ! empty( $wpssw_coupon_spreadsheet_id ) && array_key_exists( $wpssw_coupon_spreadsheet_id, $spreadsheets_list ) ) {
					$spreadsheetid = $wpssw_coupon_spreadsheet_id;
				} elseif ( ! empty( $wpssw_customer_spreadsheet_id ) && array_key_exists( $wpssw_customer_spreadsheet_id, $spreadsheets_list ) ) {
					$spreadsheetid = $wpssw_customer_spreadsheet_id;
				} elseif ( ! empty( $wpssw_event_spreadsheet_id ) && array_key_exists( $wpssw_event_spreadsheet_id, $spreadsheets_list ) ) {
					$spreadsheetid = $wpssw_event_spreadsheet_id;
				} else {
					$spreadsheetid = '';
				}
				if ( ! empty( $spreadsheetid ) ) {
					$wpssw_response            = self::$instance_api->get_sheet_listing( $spreadsheetid );
					$wpssw_existingsheetsnames = self::$instance_api->get_sheet_list( $wpssw_response );
				}
			} catch ( Exception $e ) {

				$error  = json_decode( $e->getMessage(), true );
				$reason = '';
				if ( is_array( $error['error'] ) ) {
					$errors = isset( $error['error']['errors'] ) ? $error['error']['errors'] : array();
					foreach ( $errors as $err ) {
						$reason = isset( $err['reason'] ) ? $err['reason'] : '';
					}
				} else {
					$reason = $error['error'];
				}
				if ( 'insufficientPermissions' === (string) $reason ) {
					$wpssw_error = 'Error: Insufficient Permissions - Revoke Token with below settings and when generating access token select all the permissions.';
				}
				if ( 'accessNotConfigured' === (string) $reason ) {
					$wpssw_error = 'Error: Access not Configured - Please enable Google Sheets API and Google Drive API. Follow the <a target="_blank" href="https://docs.wpsyncsheets.com/wpssw-google-sheets-api-settings/" style="color:#000;">Google Sheets API Settings documentation</a> to enable APIs.';
				}
				if ( empty( $wpssw_error ) && '' !== (string) $reason ) {
					$wpssw_error = 'Error: Invalid Credentials - Reset settings and try again.';
				}
			}
		}
		if ( $wpssw_error ) {
			self::wpssw_update_option( 'wpssw_token_error', 1 );
		} else {
			self::wpssw_update_option( 'wpssw_token_error', 0 );
		}
		return $wpssw_error;
	}
	/**
	 * Auto Sync settings code.
	 *
	 * @param string $setting setting name in which ayto sync need to add.
	 * @param array  $row_attr row attributes like class and id etc.
	 * @param string $setting_type the type of setting need to add like sync or import.
	 */
	public static function wpssw_auto_sync_setting( $setting = '', $row_attr = array(), $setting_type = '' ) {
		$setting_class     = '';
		$setting_classdash = '';
		$row_id            = '';
		$row_class         = '';
		$option_prefix     = '';
		if ( $row_attr ) {
			$row_id    = isset( $row_attr['id'] ) ? $row_attr['id'] : '';
			$row_class = isset( $row_attr['class'] ) ? $row_attr['class'] : '';
		}
		if ( 'import' === (string) $setting_type ) {
			if ( 'product' === (string) $setting ) {
				$setting_class     = 'product_';
				$setting_classdash = 'product-';
				$option_prefix     = 'product';
			} elseif ( 'coupon' === (string) $setting ) {
				$setting_class     = 'coupon_autoimport_';
				$setting_classdash = 'coupon-autoimport-';
				$option_prefix     = 'coupon_autoimport';
			} elseif ( 'customer' === (string) $setting ) {
				$setting_class     = 'cust_autoimport_';
				$setting_classdash = 'cust-autoimport-';
				$option_prefix     = 'customer_autoimport';
			}
			$tr_row_class            = $row_class . ' ' . $setting_class . 'autoimporttr auto_import_scheduling';
			$schedulecontainer_class = 'auto_import_schedulecontainer';
			$label                   = esc_html__( 'Schedule Auto Import', 'wpssw' );
			$tooltip                 = esc_html__( 'Import', 'wpssw' );
			$description             = esc_html__( 'Enable this feature and allow automatic import at your preferred intervals.', 'wpssw' );
		} else {
			if ( 'product' === (string) $setting ) {
				$setting_class     = 'prd_autosync_';
				$setting_classdash = 'prd-autosync-';
				$option_prefix     = 'product_autosync';
			} elseif ( 'coupon' === (string) $setting ) {
				$setting_class     = 'coupon_autosync_';
				$setting_classdash = 'coupon-autosync-';
				$option_prefix     = 'coupon_autosync';
			} elseif ( 'customer' === (string) $setting ) {
				$setting_class     = 'cust_autosync_';
				$setting_classdash = 'cust-autosync-';
				$option_prefix     = 'customer_autosync';
			} elseif ( 'event' === (string) $setting ) {
				$setting_class     = 'event_autosync_';
				$setting_classdash = 'event-autosync-';
				$option_prefix     = 'event_autosync';
			}
			$tr_row_class            = $row_class . ' ' . $setting_class . 'autosynctr auto_sync_scheduling';
			$schedulecontainer_class = 'auto_sync_schedulecontainer';
			$label                   = esc_html__( 'Schedule Auto Sync', 'wpssw' );
			$tooltip                 = esc_html__( 'Export', 'wpssw' );
			$description             = esc_html__( 'Stay updated with automatic synchronization by setting specific intervals to sync the data effortlessly.', 'wpssw' );
		}

		$wpssw_scheduling_enable      = self::wpssw_option( 'wpssw_' . $option_prefix . '_scheduling_enable' );
		$wpssw_scheduling_run_on      = self::wpssw_option( 'wpssw_' . $option_prefix . '_scheduling_run_on' );
		$wpssw_schedule_recurrence    = self::wpssw_option( 'wpssw_' . $option_prefix . '_schedule_recurrence' );
		$wpssw_scheduling_weekly_days = self::wpssw_option( 'wpssw_' . $option_prefix . '_scheduling_weekly_days' );
		$wpssw_scheduling_date        = self::wpssw_option( 'wpssw_' . $option_prefix . '_scheduling_date' );
		$wpssw_scheduling_time        = self::wpssw_option( 'wpssw_' . $option_prefix . '_scheduling_time' );

		if ( 'onetime' === (string) $wpssw_scheduling_run_on && $wpssw_scheduling_date && $wpssw_scheduling_time ) {
			$starttime = strtotime( $wpssw_scheduling_date . ' ' . $wpssw_scheduling_time );
			if ( date_i18n( 'Y-m-d H:i' ) > date_i18n( 'Y-m-d H:i', $starttime ) ) {
				self::wpssw_update_option( 'wpssw_' . $option_prefix . '_scheduling_date', '' );
				self::wpssw_update_option( 'wpssw_' . $option_prefix . '_scheduling_time', '' );
				self::wpssw_update_option( 'wpssw_' . $option_prefix . '_scheduling_run_on', '' );
				self::wpssw_update_option( 'wpssw_' . $option_prefix . '_scheduling_enable', '' );

				$wpssw_scheduling_date   = '';
				$wpssw_scheduling_time   = '';
				$wpssw_scheduling_run_on = '';
				$wpssw_scheduling_enable = '';
			}
		}

		$wpssw_scheduling_weekly_days_array = array();
		if ( ! empty( $wpssw_scheduling_weekly_days ) ) {
			$wpssw_scheduling_weekly_days_array = explode( ',', $wpssw_scheduling_weekly_days );
		}
		$wpssw_active     = '';
		$wpssw_deactive   = '';
		$wpssw_recurrence = '';
		$wpssw_weekly     = '';
		$wpssw_onetime    = '';
		if ( 1 === (int) $wpssw_scheduling_enable ) {
			$wpssw_active = 'checked=checked';
		} else {
			$wpssw_deactive = 'checked=checked';
		}

		if ( 'recurrence' === (string) $wpssw_scheduling_run_on ) {
			$wpssw_recurrence = 'checked=checked';
			$wpssw_recurrence = 'checked=checked';
		}
		if ( 'onetime' === (string) $wpssw_scheduling_run_on ) {
			$wpssw_onetime = 'checked=checked';
			$wpssw_onetime = 'checked=checked';
		}
		if ( 'weekly' === (string) $wpssw_scheduling_run_on ) {
			$wpssw_weekly = 'checked=checked';
			$wpssw_weekly = 'checked=checked';
		}
		if ( empty( $wpssw_recurrence ) && empty( $wpssw_onetime ) && empty( $wpssw_weekly ) ) {
			$wpssw_recurrence = 'checked=checked';
		}

		$wpssw_interval = array(
			'ten_minutes'    => 'Every Ten Minutes',
			'thirty_minutes' => 'Every Thirty Minutes',
			'once_daily'     => 'Once Daily',
			'twice_daily'    => 'Twice Daily',
			'fifteen_days'   => 'Every Fifteen Days',
			'monthly'        => 'Monthly',
		);
		$wpssw_weekday  = array(
			'monday'    => 'Mon',
			'tuesday'   => 'Tue',
			'wednesday' => 'Wed',
			'thursday'  => 'Thu',
			'friday'    => 'Fri',
			'saturday'  => 'Sat',
			'sunday'    => 'Sun',
		);

		?>
		<div class="generalSetting-section <?php echo esc_attr( $tr_row_class ); ?>" id="<?php echo esc_html( $row_id ); ?>">
			<div class="generalSetting-left">
				<h4>
					<span class="wpssw-tooltio-link tooltip-right">
						<?php echo esc_html( $label ); ?>
						<span class="tooltip-text"><?php echo esc_html( $tooltip ); ?></span>
					</span>
				</h4>
				<p><?php echo esc_html( $description ); ?></p>
				<div class="bg-gray">
					<div class="generalSetting-autosynctr-row td-radio-btn-row mb-0">     
						<div class="<?php echo esc_attr( $schedulecontainer_class ); ?> <?php echo esc_html( $setting_class ); ?>schedulecontainer mb-0">
							<input type="radio" name="<?php echo esc_html( $setting_class ); ?>scheduling_enable" value="1" id="<?php echo esc_html( $setting_class ); ?>autoschedule" <?php echo esc_attr( $wpssw_active ); ?>>
							<label for="<?php echo esc_html( $setting_class ); ?>autoschedule"><?php echo esc_html__( 'Automatic Scheduling', 'wpssw' ); ?></label>
						</div>	
						<div class="<?php echo esc_html( $setting_class ); ?>schedulecontainer <?php echo esc_attr( $schedulecontainer_class ); ?> mb-0">
							<input type="radio" name="<?php echo esc_html( $setting_class ); ?>scheduling_enable" value="0" <?php echo esc_attr( $wpssw_deactive ); ?> id="<?php echo esc_html( $setting_class ); ?>donotschedule">
							<label for="<?php echo esc_html( $setting_class ); ?>donotschedule"><?php echo esc_html__( 'Do Not Schedule', 'wpssw' ); ?></label>
						</div>
					</div>
					<div id="<?php echo esc_html( $setting_classdash ); ?>automatic-scheduling" class="automatic_scheduling_container">
						<div class="<?php echo esc_attr( $schedulecontainer_class ); ?> <?php echo esc_html( $setting_class ); ?>schedulecontainer">
							<div class="input">
								<input type="radio" name="<?php echo esc_html( $setting_class ); ?>scheduling_run_on" value="recurrence" id="<?php echo esc_html( $setting_class ); ?>recurrenceradio" <?php echo esc_attr( $wpssw_recurrence ); ?> >
								<label for="<?php echo esc_html( $setting_class ); ?>recurrenceradio"><?php echo esc_html__( 'Schedule Recurrence', 'wpssw' ); ?></label>
							</div>
							<select name="<?php echo esc_html( $setting_class ); ?>schedule_recurrence" id="<?php echo esc_html( $setting_class ); ?>schedule_recurrence" class="schedule_recurrence">
								<?php
								foreach ( $wpssw_interval as $intkey => $intvalue ) {
									$selected = '';
									if ( (string) $intkey === (string) $wpssw_schedule_recurrence ) {
										$selected = 'selected="selected"';
									}
									?>
									<option value="<?php echo esc_attr( $intkey ); ?>" <?php echo esc_attr( $selected ); ?>><?php echo esc_html( $intvalue ); ?></option>
								<?php } ?>
							</select>
						</div>
						<div class="<?php echo esc_attr( $schedulecontainer_class ); ?> <?php echo esc_html( $setting_class ); ?>schedulecontainer">
							<div class="input">
								<input type="radio" name="<?php echo esc_html( $setting_class ); ?>scheduling_run_on" value="weekly" id="<?php echo esc_html( $setting_class ); ?>weeklyradio" <?php echo esc_attr( $wpssw_weekly ); ?>>
								<label for="<?php echo esc_html( $setting_class ); ?>weeklyradio"><?php echo esc_html__( 'Every week on...', 'wpssw' ); ?></label>
							</div>
							<input type="hidden" name="<?php echo esc_html( $setting_class ); ?>scheduling_weekly_days" value="<?php echo esc_attr( $wpssw_scheduling_weekly_days ); ?>" id="<?php echo esc_html( $setting_class ); ?>weekly_days">
							<ul class="days-of-week <?php echo esc_html( $setting_classdash ); ?>days-of-week" id="<?php echo esc_html( $setting_class ); ?>weekly">
								<?php
								foreach ( $wpssw_weekday as $weekname => $weekshortname ) {
									$selectedday = '';
									if ( in_array( $weekname, $wpssw_scheduling_weekly_days_array, true ) ) {
										$selectedday = 'selected';
									}
									?>
								<li data-day="<?php echo esc_attr( $weekname ); ?>" class="<?php echo esc_attr( $selectedday ); ?>"><?php echo esc_html( $weekshortname ); ?></li>
							<?php } ?>
							</ul>
						</div>
						<div class="<?php echo esc_attr( $schedulecontainer_class ); ?> <?php echo esc_html( $setting_class ); ?>schedulecontainer">
							<div class="input">
								<input type="radio" name="<?php echo esc_html( $setting_class ); ?>scheduling_run_on" value="onetime" id="<?php echo esc_html( $setting_class ); ?>onetime" <?php echo esc_attr( $wpssw_onetime ); ?>>
								<label for="<?php echo esc_html( $setting_class ); ?>onetime"><?php echo esc_html__( 'One time run at', 'wpssw' ); ?></label>
							</div>
							<div id="<?php echo esc_html( $setting_class ); ?>scheduling_date" class="scheduling_date">
							<?php
							$timezone = wp_timezone_string();
							if ( ! $wpssw_scheduling_date ) {
								$wpssw_scheduling_date = date_format( date_create( 'now', timezone_open( $timezone ) ), 'Y-m-d' );
							}
							if ( ! $wpssw_scheduling_time ) {
								$wpssw_scheduling_time = date_format( date_create( 'now', timezone_open( $timezone ) ), 'H:i' );
							}
							?>
								<input type="date" name="<?php echo esc_html( $setting_class ); ?>scheduling_date" autocomplete="off" value="<?php echo esc_attr( $wpssw_scheduling_date ); ?>">
								<input type="time" name="<?php echo esc_html( $setting_class ); ?>scheduling_time" autocomplete="off" value="<?php echo esc_attr( $wpssw_scheduling_time ); ?>">
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
	/**
	 * Update schedule data.
	 *
	 * @param string $setting setting name.
	 * @param string $schedule_type type of schedule.
	 * @param string $id_prefix prefix for Id.
	 * @param string $option_prefix prefix for option.
	 */
	public static function wpssw_schedule_data_update( $setting = '', $schedule_type = '', $id_prefix = '', $option_prefix = '' ) {

		if ( '' === (string) $setting ) {
			return;
		}
		// phpcs:ignore
		$wpssw_schedule_enable = isset( $_POST[ $id_prefix . 'scheduling_enable' ] ) ? sanitize_text_field( wp_unslash( $_POST[ $id_prefix . 'scheduling_enable' ] ) ) : '';
		self::wpssw_update_option( 'wpssw_' . $option_prefix . 'scheduling_enable', $wpssw_schedule_enable );
		if ( 1 === (int) $wpssw_schedule_enable ) {

			$wpssw_scheduling_run_on = self::wpssw_option( 'wpssw_' . $option_prefix . 'scheduling_run_on' );
			// phpcs:ignore
			$scheduling_run_on       = isset( $_POST[ $id_prefix . 'scheduling_run_on' ] ) ? sanitize_text_field( wp_unslash( $_POST[ $id_prefix . 'scheduling_run_on' ] ) ) : '';
			if ( 'recurrence' === (string) $scheduling_run_on ) {
				self::wpssw_update_option( 'wpssw_' . $option_prefix . 'scheduling_weekly_days', '' );
				self::wpssw_update_option( 'wpssw_' . $option_prefix . 'scheduling_date', '' );
				self::wpssw_update_option( 'wpssw_' . $option_prefix . 'scheduling_time', '' );
			} elseif ( 'weekly' === (string) $scheduling_run_on ) {
				self::wpssw_update_option( 'wpssw_' . $option_prefix . 'schedule_recurrence', '' );
				self::wpssw_update_option( 'wpssw_' . $option_prefix . 'scheduling_date', '' );
				self::wpssw_update_option( 'wpssw_' . $option_prefix . 'scheduling_time', '' );
			} elseif ( 'onetime' === (string) $scheduling_run_on ) {
				self::wpssw_update_option( 'wpssw_' . $option_prefix . 'scheduling_weekly_days', '' );
				self::wpssw_update_option( 'wpssw_' . $option_prefix . 'schedule_recurrence', '' );
			}
			if ( (string) $wpssw_scheduling_run_on !== (string) $scheduling_run_on ) {
				self::wpssw_remove_cron( $setting, $schedule_type );
			}

			self::wpssw_update_option( 'wpssw_' . $option_prefix . 'scheduling_run_on', $scheduling_run_on );
			if ( 'recurrence' === (string) $scheduling_run_on ) {
				$wpssw_interval = array(
					'ten_minutes'    => 'ten_minutes',
					'thirty_minutes' => 'thirty_minutes',
					'once_daily'     => 'daily',
					'twice_daily'    => 'twicedaily',
					'fifteen_days'   => 'fifteen_days',
					'monthly'        => 'monthly',
				);
				// phpcs:ignore
				$schedule_recurrence       = isset( $_POST[ $id_prefix . 'schedule_recurrence' ] ) ? sanitize_text_field( wp_unslash( $_POST[ $id_prefix . 'schedule_recurrence' ] ) ) : '';
				$wpssw_schedule_recurrence = self::wpssw_option( 'wpssw_' . $option_prefix . 'schedule_recurrence' );

				self::wpssw_update_option( 'wpssw_' . $option_prefix . 'schedule_recurrence', $schedule_recurrence );
				if ( isset( $wpssw_interval[ $schedule_recurrence ] ) ) {
					$cron_run = self::wpssw_get_cron_name( $setting, $schedule_type );
					if ( (string) $schedule_recurrence !== (string) $wpssw_schedule_recurrence || '' === (string) $cron_run || false === wp_next_scheduled( $cron_run ) ) {
						self::wpssw_remove_cron( $setting, $schedule_type );
						$startcron = self::wpssw_cron_timeintervals( $wpssw_interval[ $schedule_recurrence ] );
						self::wpssw_set_cron( $startcron, $wpssw_interval[ $schedule_recurrence ], false, $setting, $schedule_type );
					}
				}
			}
			if ( 'weekly' === (string) $scheduling_run_on ) {
				// phpcs:ignore
				$scheduling_weekly_days       = isset( $_POST[ $id_prefix . 'scheduling_weekly_days' ] ) ? sanitize_text_field( wp_unslash( $_POST[ $id_prefix . 'scheduling_weekly_days' ] ) ) : '';
				$wpssw_scheduling_weekly_days = self::wpssw_option( 'wpssw_' . $option_prefix . 'scheduling_weekly_days' );
				if ( ! empty( $scheduling_weekly_days ) ) {
					self::wpssw_remove_cron( $setting, $schedule_type );
					$wpssw_weekdays = explode( ',', $scheduling_weekly_days );
					if ( is_array( $wpssw_weekdays ) && ! empty( $wpssw_weekdays ) ) {
						self::wpssw_update_option( 'wpssw_' . $option_prefix . 'scheduling_weekly_days', $scheduling_weekly_days );
						foreach ( $wpssw_weekdays as $weekday ) {
							$startcron = strtotime( 'next ' . $weekday );
							self::wpssw_set_cron( $startcron, 'weekly', false, $setting, $schedule_type );
						}
					}
				}
			}
			if ( 'onetime' === (string) $scheduling_run_on ) {
				// phpcs:ignore
				$scheduling_date       = isset( $_POST[ $id_prefix . 'scheduling_date' ] ) ? sanitize_text_field( wp_unslash( $_POST[ $id_prefix . 'scheduling_date' ] ) ) : '';
				// phpcs:ignore
				$scheduling_time       = isset( $_POST[ $id_prefix . 'scheduling_time' ] ) ? sanitize_text_field( wp_unslash( $_POST[ $id_prefix . 'scheduling_time' ] ) ) : '';
				$wpssw_scheduling_date = self::wpssw_option( 'wpssw_' . $option_prefix . 'scheduling_date' );
				$wpssw_scheduling_time = self::wpssw_option( 'wpssw_' . $option_prefix . 'scheduling_time' );
				self::wpssw_update_option( 'wpssw_' . $option_prefix . 'scheduling_date', $scheduling_date );
				self::wpssw_update_option( 'wpssw_' . $option_prefix . 'scheduling_time', $scheduling_time );
				if ( ! empty( $scheduling_date ) && ! empty( $scheduling_time ) ) {
					$starttime = strtotime( $scheduling_date . ' ' . $scheduling_time );
					if ( ! empty( $wpssw_scheduling_date ) && ! empty( $wpssw_scheduling_time ) ) {
						$previousstarttime = strtotime( $wpssw_scheduling_date . ' ' . $wpssw_scheduling_time );

						if ( (int) $starttime !== (int) $previousstarttime ) {
							self::wpssw_remove_cron( $setting, $schedule_type );
						}
					}
					if ( date_i18n( 'Y-m-d H:i' ) < date_i18n( 'Y-m-d H:i', $starttime ) ) {
						self::wpssw_set_cron( $starttime, '', true, $setting, $schedule_type );
					} else {
						self::wpssw_update_option( 'wpssw_' . $option_prefix . 'scheduling_date', '' );
						self::wpssw_update_option( 'wpssw_' . $option_prefix . 'scheduling_time', '' );
						self::wpssw_update_option( 'wpssw_' . $option_prefix . 'scheduling_run_on', '' );
						self::wpssw_update_option( 'wpssw_' . $option_prefix . 'scheduling_enable', '' );
					}
				}
			}
		} else {
			self::wpssw_update_option( 'wpssw_' . $option_prefix . 'scheduling_run_on', '' );
			self::wpssw_update_option( 'wpssw_' . $option_prefix . 'scheduling_weekly_days', '' );
			self::wpssw_update_option( 'wpssw_' . $option_prefix . 'scheduling_date', '' );
			self::wpssw_update_option( 'wpssw_' . $option_prefix . 'scheduling_time', '' );
			self::wpssw_update_option( 'wpssw_' . $option_prefix . 'schedule_recurrence', '' );
			self::wpssw_remove_cron( $setting, $schedule_type );
		}
	}


	/**
	 * Set cron job.
	 *
	 * @param int    $startcron .
	 * @param int    $interval .
	 * @param bool   $once .
	 * @param string $setting setting name.
	 * @param string $schedule_type type of schedule.
	 */
	public static function wpssw_set_cron( $startcron, $interval, $once = false, $setting = '', $schedule_type = '' ) {
		if ( '' === (string) $setting ) {
			return;
		}
		$cron_run = self::wpssw_get_cron_name( $setting, $schedule_type );
		if ( '' === (string) $cron_run ) {
			return;
		}

		// phpcs:ignore.
		$date_time     = date( 'Y-m-d h:i A', $startcron );
		$timezone_from = wp_timezone_string();
		$new_date_time = new DateTime( $date_time, new DateTimeZone( $timezone_from ) );
		$new_date_time->setTimezone( new DateTimeZone( 'UTC' ) );
		$date_time_utc = $new_date_time->format( 'Y-m-d h:i A' );

		$startcron = strtotime( $date_time_utc );

		if ( $once ) {
			wp_schedule_single_event( $startcron, $cron_run );
		} else {
			wp_schedule_event( $startcron, $interval, $cron_run );
		}
	}
	/**
	 * Remove cron job.
	 *
	 * @param string $setting setting name.
	 * @param string $schedule_type type of schedule.
	 */
	public static function wpssw_remove_cron( $setting = '', $schedule_type = '' ) {
		if ( '' === (string) $setting ) {
			return;
		}
		$cron_run = self::wpssw_get_cron_name( $setting, $schedule_type );
		if ( '' === (string) $cron_run ) {
			return;
		}

		wp_clear_scheduled_hook( $cron_run );
	}
	/**
	 * Get cron name.
	 *
	 * @param string $setting setting name.
	 * @param string $schedule_type type of schedule.
	 */
	public static function wpssw_get_cron_name( $setting = '', $schedule_type = '' ) {
		$cron_run = '';
		if ( '' === (string) $setting ) {
			return $cron_run;
		} elseif ( 'order' === (string) $setting ) {
			$cron_run = 'wpssw_cron_run';
		} elseif ( 'product' === (string) $setting ) {
			if ( 'import_products' === (string) $schedule_type ) {
				$cron_run = 'wpssw_product_cron_run';
			} elseif ( 'bulk_insert_products' === (string) $schedule_type ) {
				$cron_run = 'wpssw_bulk_insert_products_cron_run';
			} elseif ( 'auto_sync' === (string) $schedule_type ) {
				$cron_run = 'wpssw_autosync_products_cron_run';
			}
		} elseif ( 'coupon' === (string) $setting ) {
			if ( 'auto_import' === (string) $schedule_type ) {
				$cron_run = 'wpssw_autoimport_coupons_cron_run';
			} else {
				$cron_run = 'wpssw_autosync_coupons_cron_run';
			}
		} elseif ( 'customer' === (string) $setting ) {
			if ( 'auto_import' === (string) $schedule_type ) {
				$cron_run = 'wpssw_autoimport_customers_cron_run';
			} else {
				$cron_run = 'wpssw_autosync_customers_cron_run';
			}
		} elseif ( 'event' === (string) $setting ) {
			$cron_run = 'wpssw_autosync_event_orders_cron_run';
		}
		return $cron_run;
	}
	/**
	 * Update multiple data to the sheet.
	 *
	 * @param array  $wpssw_multiple_update_data data to update.
	 * @param array  $settings_data settings data.
	 * @param bool   $settings_check whether the settings checked or not.
	 * @param string $oparation operation to perform on data.
	 */
	public static function wpssw_multiple_update_data( $wpssw_multiple_update_data, $settings_data = array(), $settings_check = false, $oparation = '' ) {

		if ( empty( $wpssw_multiple_update_data ) || empty( $settings_data ) ) {
			return;
		}

		$wpssw_spreadsheetid = isset( $settings_data['spreadsheet_id'] ) ? $settings_data['spreadsheet_id'] : '';
		$wpssw_sheetname     = isset( $settings_data['sheetname'] ) ? $settings_data['sheetname'] : '';
		$wpssw_setting       = isset( $settings_data['setting'] ) ? $settings_data['setting'] : '';

		if ( empty( $wpssw_spreadsheetid ) || empty( $wpssw_sheetname ) || empty( $wpssw_setting ) ) {
			return;
		}

		if ( false === $settings_check ) {
			$wpssw_spreadsheet_setting = isset( $settings_data['setting_enable'] ) ? $settings_data['setting_enable'] : '';

			if ( 'yes' !== (string) $wpssw_spreadsheet_setting ) {
				return;
			}
			$wpssw_spreadsheets_list = self::$instance_api->get_spreadsheet_listing();
			if ( ! array_key_exists( $wpssw_spreadsheetid, $wpssw_spreadsheets_list ) ) {
				return;
			}
		}
		$response                  = self::$instance_api->get_sheet_listing( $wpssw_spreadsheetid );
		$wpssw_existingsheetsnames = self::$instance_api->get_sheet_list( $response );

		if ( 'product' === (string) $wpssw_setting ) {
			$wpssw_product_setting_sheets = array_filter( (array) self::wpssw_option( 'wpssw_product_setting_sheets' ) );
			if ( ! $wpssw_product_setting_sheets ) {
				$wpssw_product_setting_sheets = array( 'all_products' => 'All Products' );
			}

			$wpssw_product_categories_as_sheet = self::wpssw_option( 'product_categories_as_sheet' );
			$wpssw_productsheets_list          = array();

			$wpssw_activesheets = array();

			foreach ( $wpssw_product_setting_sheets as $sheet_slug => $sheet_name ) {
				if ( ! empty( $sheet_name ) && false !== array_key_exists( $sheet_name, $wpssw_existingsheetsnames ) ) {
					$wpssw_activesheets[ $sheet_slug ] = $sheet_name;
				}
			}
			if ( 'yes' === (string) $wpssw_product_categories_as_sheet ) {
				$wpssw_productsheets_list = self::wpssw_get_product_category_saved_sheets();
				foreach ( $wpssw_productsheets_list as $cat_id => $cat_name ) {
					if ( ! empty( $cat_name ) && false !== array_key_exists( $cat_name, $wpssw_existingsheetsnames ) ) {
						$wpssw_activesheets[ $cat_id ]           = $cat_name;
						$wpssw_product_setting_sheets[ $cat_id ] = $cat_name;
					}
				}
			}
			if ( count( $wpssw_activesheets ) > 1 ) {
				$settings_data['existingsheetsnames'] = $wpssw_existingsheetsnames;
				$settings_data['activesheets']        = $wpssw_activesheets;
				self::wpssw_multiple_sheets_update_data( $wpssw_multiple_update_data, $settings_data, $settings_check, $oparation );
				return;
			}
		}

		$wpssw_existingsheetsnames = array_flip( $wpssw_existingsheetsnames );

		$sheet_id = array_search( $wpssw_sheetname, $wpssw_existingsheetsnames, true );
		if ( false === $sheet_id ) {
			return;
		}
		$wpssw_sheet    = "'" . $wpssw_sheetname . "'!A:Z";
		$wpssw_allentry = self::$instance_api->get_row_list( $wpssw_spreadsheetid, $wpssw_sheet );
		$wpssw_data     = $wpssw_allentry->getValues();
		$wpssw_allentry = null;
		$wpssw_data     = array_map(
			function( $wpssw_element ) {
				if ( isset( $wpssw_element['0'] ) ) {
					return (int) $wpssw_element['0'];
				} else {
					return '';
				}
			},
			$wpssw_data
		);

		$wpssw_inputoption = self::wpssw_option( 'wpssw_inputoption' );
		if ( ! $wpssw_inputoption ) {
			$wpssw_inputoption = 'USER_ENTERED';
		}

		if ( 'product' === (string) $wpssw_setting ) {
			$wpssw_inputoption = WPSSW_Product::wpssw_get_product_inputoption();
			if ( ! $wpssw_inputoption ) {
				$wpssw_inputoption = 'USER_ENTERED';
			}
		}

		if ( 'insert' === (string) $oparation ) {
			$wpssw_values_array = array();
			foreach ( $wpssw_multiple_update_data as $update_id ) {
				if ( 'product' === (string) $wpssw_setting ) {
					$product = wc_get_product( $update_id );
					if ( false === $product || null === $product && is_wp_error( $product ) ) {
						continue;
					}
					if ( ! empty( $product->get_children() ) && 'grouped' !== (string) $product->get_type() ) {
						$wpssw_values_array[] = WPSSW_Product::wpssw_make_product_value_array( 'insert', $product->get_id() );
						foreach ( $product->get_children() as $child ) {
							$wpssw_values_array[] = WPSSW_Product::wpssw_make_product_value_array( 'insert', $child, true );
						}
					} else {
						$wpssw_values_array[] = WPSSW_Product::wpssw_make_product_value_array( 'insert', $product->get_id() );
					}
					$product = null;
				} elseif ( 'customer' === (string) $wpssw_setting ) {
					$customer = new WP_User( $update_id );
					if ( false === $customer || null === $customer && is_wp_error( $customer ) ) {
						continue;
					}
					$wpssw_values_array = array_merge( $wpssw_values_array, WPSSW_Customer::wpssw_make_customer_value_array( 'insert', $customer ) );
					$customer           = null;
				} elseif ( 'coupon' === (string) $wpssw_setting ) {
					$coupon = new WC_Coupon( $update_id );
					if ( false === $coupon || null === $coupon && is_wp_error( $coupon ) ) {
						continue;
					}
					$wpssw_values_array = array_merge( $wpssw_values_array, WPSSW_Coupon::wpssw_make_coupon_value_array( 'insert', $update_id ) );
					$coupon             = null;
				}
			}
			$wpssw_values_array = array_filter( $wpssw_values_array );
			$rangetofind        = $wpssw_sheetname . '!A' . ( count( $wpssw_data ) + 1 );

			if ( ! empty( $wpssw_values_array ) ) {
				try {
					// If there is no sheet_id available, leave the sheet_id field blank. Otherwise, if there is a sheet_id, leave the sheet_name field blank.

					$wpssw_inherit_params = array(
						'sheet_id'       => $sheet_id,
						'sheet_name'     => '',
						'start_index'    => count( $wpssw_data ),
						'end_index'      => count( $wpssw_data ) + count( $wpssw_values_array ),
						'spreadsheet_id' => $wpssw_spreadsheetid,
					);
					self::wpssw_inherit_row_format_style( $wpssw_setting, $wpssw_inherit_params );
					$wpssw_params      = array();
					$wpssw_requestbody = self::$instance_api->valuerangeobject( $wpssw_values_array );
					$wpssw_params      = array( 'valueInputOption' => $wpssw_inputoption );
					$param             = self::$instance_api->setparamater( $wpssw_spreadsheetid, $rangetofind, $wpssw_requestbody, $wpssw_params );
					$wpssw_response    = self::$instance_api->appendentry( $param );

				} catch ( Exception $e ) {
					return;
				}
			}
			return;
		}

		$delete_row_indexes = array();
		$deleterequestarray = array();

		foreach ( $wpssw_multiple_update_data as $update_id ) {
			if ( in_array( (int) $update_id, $wpssw_data, true ) ) {
				$wpssw_num  = array_search( (int) $update_id, $wpssw_data, true );
				$item_count = count( array_keys( $wpssw_data, (int) $update_id, true ) );

				$delete_row_indexes[] = array(
					'start_index' => $wpssw_num,
					'end_index'   => $wpssw_num + $item_count,
				);
			}
		}

		if ( ! empty( $delete_row_indexes ) ) {
			$requests                 = array();
			$delete_row_indexes_count = count( $delete_row_indexes );
			array_multisort( array_column( $delete_row_indexes, 'start_index' ), SORT_ASC, $delete_row_indexes );
			for ( $i = 0;$i < $delete_row_indexes_count;$i++ ) {
				if ( 0 === (int) $i ) {
					$startindex = $delete_row_indexes[0]['start_index'];
				} elseif ( $delete_row_indexes[ $i ]['start_index'] - $delete_row_indexes[ $i - 1 ]['end_index'] > 0 ) {
					$requests[] = array(
						'startIndex' => $startindex,
						'endIndex'   => $delete_row_indexes[ $i - 1 ]['end_index'],
					);
					$startindex = $delete_row_indexes[ $i ]['start_index'];
				}
				if ( $delete_row_indexes_count - 1 === (int) $i ) {
					$requests[] = array(
						'startIndex' => $startindex,
						'endIndex'   => $delete_row_indexes[ $i ]['end_index'],
					);
				}
			}
			array_multisort( array_column( $requests, 'startIndex' ), SORT_DESC, $requests );
			foreach ( $requests as $request ) {
				$param                = array();
				$param                = self::$instance_api->prepare_param( $sheet_id, $request['startIndex'], $request['endIndex'] );
				$deleterequestarray[] = self::$instance_api->deleteDimensionrequests( $param, 'ROWS' );
			}
		}

		if ( ! empty( $deleterequestarray ) ) {
			$param                  = array();
			$param['spreadsheetid'] = $wpssw_spreadsheetid;
			$param['requestarray']  = $deleterequestarray;
			$wpssw_response         = self::$instance_api->updatebachrequests( $param );
		}

		if ( 'delete' === (string) $oparation ) {
			return;
		}

		if ( ! empty( $deleterequestarray ) ) {
			$delete_row_indexes = null;
			$deleterequestarray = null;
			$wpssw_response     = null;

			$wpssw_allentry = self::$instance_api->get_row_list( $wpssw_spreadsheetid, $wpssw_sheet );

			$wpssw_data     = $wpssw_allentry->getValues();
			$wpssw_allentry = null;
			$wpssw_data     = array_map(
				function( $wpssw_element ) {
					if ( isset( $wpssw_element['0'] ) ) {
						return (int) $wpssw_element['0'];
					} else {
						return '';
					}
				},
				$wpssw_data
			);

		}
		$wpssw_data_count = count( $wpssw_data );

		$highest_update_id = max( $wpssw_data );

		foreach ( $wpssw_multiple_update_data as $update_key => $update_id ) {
			$wpssw_append   = 0;
			$wpssw_prdcount = 1;
			if ( 'product' === (string) $wpssw_setting ) {
				$product = wc_get_product( $update_id );
				if ( false === $product || null === $product && is_wp_error( $product ) ) {
					continue;
				}
				if ( ! empty( $product->get_children() ) && 'grouped' !== (string) $product->get_type() ) {
					$wpssw_prdcount += count( $product->get_children() );
				}
				$product = null;
			} elseif ( 'customer' === (string) $wpssw_setting ) {
				$customer = new WP_User( $update_id );
				if ( false === $customer || null === $customer && is_wp_error( $customer ) ) {
					continue;
				}
				$customer = null;
			} elseif ( 'coupon' === (string) $wpssw_setting ) {
				$coupon = new WC_Coupon( $update_id );
				if ( false === $coupon || null === $coupon && is_wp_error( $coupon ) ) {
					continue;
				}
				$coupon = null;
			}

			if ( ( $highest_update_id ) && $update_id < $highest_update_id ) {
				foreach ( $wpssw_data as $wpssw_key => $wpssw_value ) {
					if ( ! empty( $wpssw_value ) ) {

						if ( ( (int) $update_id < (int) $wpssw_value ) ) {
							$wpssw_startindex = $wpssw_key + 1;

							$wpssw_append         = 1;
							$insert_row_indexes[] = array(
								'start_index' => $wpssw_key,
								'end_index'   => $wpssw_key + $wpssw_prdcount,
							);

							$update_row_indexes[] = array(
								'start_index'  => $wpssw_startindex,
								'end_index'    => $wpssw_startindex + $wpssw_prdcount,
								'update_id'    => $update_id,

								'values_count' => $wpssw_prdcount,
							);

							break;
						}
					}
				}
			}

			if ( 0 === (int) $wpssw_append ) {

				$wpssw_startindex = $wpssw_data_count + 1;

				$insert_row_indexes[] = array(
					'start_index' => $wpssw_data_count,
					'end_index'   => $wpssw_data_count + $wpssw_prdcount,
				);
				$update_row_indexes[] = array(
					'start_index'  => $wpssw_startindex,
					'end_index'    => $wpssw_startindex + $wpssw_prdcount,
					'update_id'    => $update_id,

					'values_count' => $wpssw_prdcount,
				);

			}
		}
		$wpssw_data          = null;
		$wpssw_inherit_style = self::wpssw_get_inherit_style( $wpssw_setting );

		if ( ! empty( $insert_row_indexes ) ) {
			$requests           = array();
			$insertrequestarray = array();

			array_multisort( array_column( $insert_row_indexes, 'start_index' ), SORT_DESC, $insert_row_indexes );
			foreach ( $insert_row_indexes as $request ) {
				$param = array();
				$param = self::$instance_api->prepare_param( $sheet_id, $request['start_index'], $request['end_index'] );
				if ( 'no' === (string) $wpssw_inherit_style ) {
					$insertrequestarray[] = self::$instance_api->insertdimensionrequests( $param, 'ROWS', false );
				} else {
					$insertrequestarray[] = self::$instance_api->insertdimensionrequests( $param, 'ROWS', true );
				}
			}

			if ( ! empty( $insertrequestarray ) ) {
				$param                  = array();
				$param['spreadsheetid'] = $wpssw_spreadsheetid;
				$param['requestarray']  = $insertrequestarray;
				$wpssw_response         = self::$instance_api->updatebachrequests( $param );

				$insert_row_indexes = null;
				$insertrequestarray = null;
				$wpssw_response     = null;
			}
		}

		$update_row_data = array();

		$update_row_indexes_new = array();

		if ( ! empty( $update_row_indexes ) ) {
			array_multisort( array_column( $update_row_indexes, 'start_index' ), SORT_ASC, $update_row_indexes );

			$requests                 = array();
			$update_row_indexes_count = count( $update_row_indexes );
			$dimension                = 'ROWS';
			for ( $i = 0;$i < $update_row_indexes_count;$i++ ) {
				$new_start               = $update_row_indexes[ $i ]['start_index'];
				$same_startindex_rowkeys = array_keys( self::wpssw_convert_int( array_column( $update_row_indexes, 'start_index' ) ), (int) $update_row_indexes[ $i ]['start_index'], true );

				if ( count( $same_startindex_rowkeys ) > 1 && $same_startindex_rowkeys[0] < $i ) {
					continue;
				}
				for ( $j = 0;$j < $update_row_indexes_count;$j++ ) {
					$wpssw_values_array = array();
					if ( $j < $i ) {
						$new_start = $new_start + $update_row_indexes[ $j ]['values_count'];
					}
					if ( ( 0 === (int) $i || ( $i === $update_row_indexes_count - 1 && $j === $i ) ) && count( $same_startindex_rowkeys ) < 2 ) {
						$param = array();
						if ( 0 === (int) $i ) {
							$param['range'] = $wpssw_sheetname . '!A' . $update_row_indexes[ $i ]['start_index'];
						} else {
							$param['range'] = $wpssw_sheetname . '!A' . $new_start;
						}
						if ( 'product' === (string) $wpssw_setting ) {
							$product = wc_get_product( $update_row_indexes[ $i ]['update_id'] );
							if ( ! empty( $product->get_children() ) && 'grouped' !== (string) $product->get_type() ) {
								$wpssw_values_array[] = WPSSW_Product::wpssw_make_product_value_array( 'insert', $product->get_id() );
								foreach ( $product->get_children() as $child ) {
									$wpssw_values_array[] = WPSSW_Product::wpssw_make_product_value_array( 'insert', $child, true );
								}
							} else {
								$wpssw_values_array[] = WPSSW_Product::wpssw_make_product_value_array( 'insert', $product->get_id() );
							}
							$product = null;
						} elseif ( 'customer' === (string) $wpssw_setting ) {
							$customer           = new WP_User( $update_row_indexes[ $i ]['update_id'] );
							$wpssw_values_array = WPSSW_Customer::wpssw_make_customer_value_array( 'insert', $customer );
							$customer           = null;
						} elseif ( 'coupon' === (string) $wpssw_setting ) {
							$wpssw_values_array = WPSSW_Coupon::wpssw_make_coupon_value_array( 'insert', $update_row_indexes[ $i ]['update_id'] );
						}
						$update_row_data[]  = new \Google_Service_Sheets_ValueRange(
							array(
								'range'          => $param['range'],
								'majorDimension' => $dimension,
								'values'         => $wpssw_values_array,
							)
						);
						$wpssw_values_array = null;
						break;
					}
					if ( (int) $update_row_indexes[ $i ]['start_index'] === (int) $update_row_indexes[ $j ]['start_index'] && $update_row_indexes[ $i ]['update_id'] !== $update_row_indexes[ $j ]['update_id'] ) {

						break;
					}

					if ( $update_row_indexes[ $i ]['start_index'] < $update_row_indexes[ $j ]['start_index'] ) {
						$param          = array();
						$param['range'] = $wpssw_sheetname . '!A' . $new_start;

						if ( 'product' === (string) $wpssw_setting ) {
							$product = wc_get_product( $update_row_indexes[ $i ]['update_id'] );
							if ( ! empty( $product->get_children() ) && 'grouped' !== (string) $product->get_type() ) {
								$wpssw_values_array[] = WPSSW_Product::wpssw_make_product_value_array( 'insert', $product->get_id() );
								foreach ( $product->get_children() as $child ) {
									$wpssw_values_array[] = WPSSW_Product::wpssw_make_product_value_array( 'insert', $child, true );
								}
							} else {
								$wpssw_values_array[] = WPSSW_Product::wpssw_make_product_value_array( 'insert', $product->get_id() );
							}
							$product = null;
						} elseif ( 'customer' === (string) $wpssw_setting ) {
							$customer           = new WP_User( $update_row_indexes[ $i ]['update_id'] );
							$wpssw_values_array = WPSSW_Customer::wpssw_make_customer_value_array( 'insert', $customer );
							$customer           = null;
						} elseif ( 'coupon' === (string) $wpssw_setting ) {
							$wpssw_values_array = WPSSW_Coupon::wpssw_make_coupon_value_array( 'insert', $update_row_indexes[ $i ]['update_id'] );
						}
						$update_row_data[]  = new \Google_Service_Sheets_ValueRange(
							array(
								'range'          => $param['range'],
								'majorDimension' => $dimension,
								'values'         => $wpssw_values_array,
							)
						);
						$wpssw_values_array = null;
						break;
					}
					if ( count( $same_startindex_rowkeys ) > 1 ) {
						$same_row_start = $update_row_indexes[ $i ]['start_index'];
						for ( $k = 0;$k < $i;$k++ ) {
							$same_row_start = $same_row_start + $update_row_indexes[ $k ]['values_count'];
						}
						$new_start = $same_row_start;

						$same_rows = array();
						foreach ( $same_startindex_rowkeys as $same_rowkey ) {
							$same_rows[ $update_row_indexes[ $same_rowkey ]['update_id'] ] = $update_row_indexes[ $same_rowkey ];
						}
						ksort( $same_rows );
						$same_startindex_rowkeys = null;

						$same_rows_value = array();
						foreach ( $same_rows as $same_row ) {
							$wpssw_values_array = array();
							if ( 'product' === (string) $wpssw_setting ) {

								$product = wc_get_product( $same_row['update_id'] );
								if ( ! empty( $product->get_children() ) && 'grouped' !== (string) $product->get_type() ) {
									$wpssw_values_array[] = WPSSW_Product::wpssw_make_product_value_array( 'insert', $product->get_id() );
									foreach ( $product->get_children() as $child ) {
										$wpssw_values_array[] = WPSSW_Product::wpssw_make_product_value_array( 'insert', $child, true );
									}
								} else {
									$wpssw_values_array[] = WPSSW_Product::wpssw_make_product_value_array( 'insert', $product->get_id() );
								}
								$product = null;
							} elseif ( 'customer' === (string) $wpssw_setting ) {

								$customer           = new WP_User( $same_row['update_id'] );
								$wpssw_values_array = WPSSW_Customer::wpssw_make_customer_value_array( 'insert', $customer );
								$customer           = null;
							} elseif ( 'coupon' === (string) $wpssw_setting ) {
								$wpssw_values_array = WPSSW_Coupon::wpssw_make_coupon_value_array( 'insert', $same_row['update_id'] );
							}

							$same_rows_value    = array_merge( $same_rows_value, $wpssw_values_array );
							$wpssw_values_array = null;
						}
						$wpssw_values_array = null;

						$param          = array();
						$param['range'] = $wpssw_sheetname . '!A' . $new_start;

						$update_row_data[] = new \Google_Service_Sheets_ValueRange(
							array(
								'range'          => $param['range'],
								'majorDimension' => $dimension,
								'values'         => $same_rows_value,
							)
						);
						$same_rows_value   = null;
						break;
					}
				}
			}
		}

		if ( ! empty( $update_row_data ) ) {

			$requestobject                  = array();
			$requestobject['spreadsheetid'] = $wpssw_spreadsheetid;

			$requestobject['requestbody'] = new Google_Service_Sheets_BatchUpdateValuesRequest(
				array(
					'valueInputOption' => $wpssw_inputoption,
					'data'             => $update_row_data,
				)
			);

			$wpssw_response = self::$instance_api->multirangevalueupdate( $requestobject );

		}

	}
	/**
	 * Update multiple data to given active sheets.
	 *
	 * @param array  $wpssw_multiple_update_data data to update.
	 * @param array  $settings_data settings data.
	 * @param bool   $settings_check whether the settings checked or not.
	 * @param string $oparation operation to perform on data.
	 */
	public static function wpssw_multiple_sheets_update_data( $wpssw_multiple_update_data, $settings_data = array(), $settings_check = false, $oparation = '' ) {

		if ( $wpssw_multiple_update_data ) {
			$wpssw_multiple_update_data = self::wpssw_convert_int( $wpssw_multiple_update_data );
		} else {
			return;
		}

		$wpssw_existingsheetsnames = isset( $settings_data['existingsheetsnames'] ) ? $settings_data['existingsheetsnames'] : array();

		$wpssw_spreadsheetid = isset( $settings_data['spreadsheet_id'] ) ? $settings_data['spreadsheet_id'] : '';
		$wpssw_sheetname     = isset( $settings_data['sheetname'] ) ? $settings_data['sheetname'] : '';
		$wpssw_setting       = isset( $settings_data['setting'] ) ? $settings_data['setting'] : '';

		if ( empty( $wpssw_spreadsheetid ) || empty( $wpssw_sheetname ) || empty( $wpssw_setting ) ) {
			return;
		}

		if ( ! $wpssw_existingsheetsnames ) {
			$response                  = self::$instance_api->get_sheet_listing( $wpssw_spreadsheetid );
			$wpssw_existingsheetsnames = self::$instance_api->get_sheet_list( $response );
		}
		$wpssw_existingsheetsnames = $wpssw_existingsheetsnames ? array_flip( $wpssw_existingsheetsnames ) : array();

		$wpssw_activesheets = isset( $settings_data['activesheets'] ) ? $settings_data['activesheets'] : array();

		if ( ! $wpssw_activesheets ) {
			return;
		}

		$wpssw_getactivesheets = array();
		foreach ( $wpssw_activesheets as $wpssw_key => $wpssw_val ) {
			$sheetid = array_search( $wpssw_val, $wpssw_existingsheetsnames, true );

			if ( false === (bool) $sheetid ) {
				unset( $wpssw_activesheets[ $wpssw_key ] );
				continue;
			}

			$wpssw_getactivesheets[] = "'" . $wpssw_val . "'!A:A";
		}

		/*Get First Column Value from all sheets*/
		try {
			$param                  = array();
			$param['spreadsheetid'] = $wpssw_spreadsheetid;
			$param['ranges']        = array( 'ranges' => $wpssw_getactivesheets );
			$wpssw_response         = self::$instance_api->getbatchvalues( $param );
		} catch ( Exception $e ) {
			echo esc_html( 'Message: ' . $e->getMessage() );
		}
		$wpssw_existingorders = array();
		foreach ( $wpssw_response->getValueRanges() as $wpssw_response_data ) {
			if ( strpos( $wpssw_response_data->range, "'!A" ) ) {
				$wpssw_rangetitle = explode( "'!A", $wpssw_response_data->range );
			} else {
				$wpssw_rangetitle = explode( '!A', $wpssw_response_data->range );
			}
			$wpssw_sheettitle = str_replace( "'", '', $wpssw_rangetitle[0] );
			$wpssw_data       = array_map(
				function( $wpssw_element ) {
					if ( isset( $wpssw_element['0'] ) ) {
						return (int) $wpssw_element['0'];
					} else {
						return '';
					}
				},
				$wpssw_response_data->values
			);

			$wpssw_existingorders[ $wpssw_sheettitle ] = $wpssw_data;
			$wpssw_data                                = null;
		}

		$wpssw_inputoption = self::wpssw_option( 'wpssw_inputoption' );
		if ( ! $wpssw_inputoption ) {
			$wpssw_inputoption = 'USER_ENTERED';
		}

		$wpssw_product_categories_as_sheet = self::wpssw_option( 'product_categories_as_sheet' );
		$wpssw_productsheets_list          = array();

		if ( 'product' === (string) $wpssw_setting ) {
			$wpssw_inputoption = WPSSW_Product::wpssw_get_product_inputoption();
			if ( ! $wpssw_inputoption ) {
				$wpssw_inputoption = 'USER_ENTERED';
			}
			if ( 'yes' === (string) $wpssw_product_categories_as_sheet ) {
				$wpssw_productsheets_list = self::wpssw_get_product_category_saved_sheets();
			}
		}

		$wpssw_response = null;

		$delete_row_indexes = array();
		$deleterequestarray = array();
		$order_ascdesc      = 'ascorder';
		foreach ( $wpssw_multiple_update_data as $update_id ) {

			foreach ( $wpssw_existingorders as $sheet_name => $existingorders_sheet ) {
				$sheet_id = array_search( $sheet_name, $wpssw_existingsheetsnames, true );

				if ( in_array( $update_id, $existingorders_sheet, true ) ) {
					$wpssw_num = array_search( (int) $update_id, self::wpssw_convert_int( $existingorders_sheet ), true );

					$start_index = $wpssw_num;

					$item_count = count( array_keys( $existingorders_sheet, (int) $update_id, true ) );
					$end_index  = $wpssw_num + $item_count;

					$delete_row_indexes[ $sheet_name ][] = array(
						'start_index' => $start_index,
						'end_index'   => $end_index,
					);
				}
			}
		}

		foreach ( $delete_row_indexes as $sheet_name => $delete_row ) {
			$requests                 = array();
			$delete_row_indexes_count = count( $delete_row );
			array_multisort( array_column( $delete_row, 'start_index' ), SORT_ASC, $delete_row );
			for ( $i = 0;$i < $delete_row_indexes_count;$i++ ) {
				if ( 0 === (int) $i ) {
					$startindex = $delete_row[0]['start_index'];
				} elseif ( $delete_row[ $i ]['start_index'] - $delete_row[ $i - 1 ]['end_index'] > 0 ) {
					$requests[] = array(
						'startIndex' => $startindex,
						'endIndex'   => $delete_row[ $i - 1 ]['end_index'],
					);
					$startindex = $delete_row[ $i ]['start_index'];
				}
				if ( $delete_row_indexes_count - 1 === (int) $i ) {
					$requests[] = array(
						'startIndex' => $startindex,
						'endIndex'   => $delete_row[ $i ]['end_index'],
					);
				}
			}
			array_multisort( array_column( $requests, 'startIndex' ), SORT_DESC, $requests );
			$sheet_id = array_search( $sheet_name, $wpssw_existingsheetsnames, true );
			foreach ( $requests as $request ) {
				$param                = array();
				$param                = self::$instance_api->prepare_param( $sheet_id, $request['startIndex'], $request['endIndex'] );
				$deleterequestarray[] = self::$instance_api->deleteDimensionrequests( $param, 'ROWS' );
			}
			$requests = null;
		}

		if ( ! empty( $deleterequestarray ) ) {

			$delete_row_indexes = null;

			$wpssw_response     = self::$instance_api->updatebachrequests(
				array(
					'spreadsheetid' => $wpssw_spreadsheetid,
					'requestarray'  => $deleterequestarray,
				)
			);
			$deleterequestarray = null;
			$wpssw_response     = null;
			/*Get First Column Value from all sheets*/
			$param                  = array();
			$wpssw_response         = array();
			$param['spreadsheetid'] = $wpssw_spreadsheetid;
			$param['ranges']        = array( 'ranges' => $wpssw_getactivesheets );
			$wpssw_response         = self::$instance_api->getbatchvalues( $param );

			$wpssw_existingorders = array();
			foreach ( $wpssw_response->getValueRanges() as $wpssw_response_data ) {
				if ( strpos( $wpssw_response_data->range, "'!A" ) ) {
					$wpssw_rangetitle = explode( "'!A", $wpssw_response_data->range );
				} else {
					$wpssw_rangetitle = explode( '!A', $wpssw_response_data->range );
				}
				$wpssw_sheettitle = str_replace( "'", '', $wpssw_rangetitle[0] );
				$wpssw_data       = array_map(
					function( $wpssw_element ) {
						if ( isset( $wpssw_element['0'] ) && is_numeric( $wpssw_element['0'] ) ) {
							return (int) $wpssw_element['0'];
						} else {
							return '';
						}
					},
					$wpssw_response_data->values
				);

				$wpssw_existingorders[ $wpssw_sheettitle ] = $wpssw_data;
				$wpssw_data                                = null;
			}
			$wpssw_response = null;
		}

		if ( 'delete' === (string) $oparation ) {
			return;
		}

		foreach ( $wpssw_multiple_update_data as $update_id ) {
			foreach ( $wpssw_existingorders as $sheet_name => $existingorders_sheet ) {

				$sheet_id          = array_search( $sheet_name, $wpssw_existingsheetsnames, true );
				$wpssw_append      = 0;
				$wpssw_desc_append = 0;

				$wpssw_prdcount = 1;

				if ( 'product' === (string) $wpssw_setting ) {

					$product = wc_get_product( $update_id );
					if ( false === $product || null === $product && is_wp_error( $product ) ) {
						continue;
					}
					if ( ! empty( $wpssw_productsheets_list ) && in_array( $sheet_name, $wpssw_productsheets_list, true ) ) {
						$cat_terms = wp_get_post_terms( $update_id, 'product_cat' );

						if ( ! empty( $cat_terms ) && ! is_wp_error( $cat_terms ) ) {
							$cat_terms_id_arr = wp_list_pluck( $cat_terms, 'term_id' );
							$cat_key          = array_search( $sheet_name, $wpssw_productsheets_list, true );
							if ( false === $cat_key || ! in_array( $cat_key, $cat_terms_id_arr, true ) ) {
								continue;
							}
						} else {
							continue;
						}
					}

					if ( ! empty( $product->get_children() ) && 'grouped' !== (string) $product->get_type() ) {
						$wpssw_prdcount += count( $product->get_children() );
					}
					$product = null;
				} elseif ( 'customer' === (string) $wpssw_setting ) {
					$customer = new WP_User( $update_id );
					if ( false === $customer || null === $customer && is_wp_error( $customer ) ) {
						continue;
					}
					$customer = null;
				} elseif ( 'coupon' === (string) $wpssw_setting ) {
					$coupon = new WC_Coupon( $update_id );
					if ( false === $coupon || null === $coupon && is_wp_error( $coupon ) ) {
						continue;
					}
					$coupon = null;
				}

				$highest_order_id = max( $existingorders_sheet );

				if ( ( $highest_order_id ) && $update_id < $highest_order_id ) {
					foreach ( $existingorders_sheet as $wpssw_key => $wpssw_value ) {
						if ( ! empty( $wpssw_value ) ) {

							$wpssw_startindex       = $wpssw_key + 1;
							$insert_row_start_index = $wpssw_key;

							$wpssw_endindex       = $wpssw_startindex + $wpssw_prdcount;
							$insert_row_end_index = $wpssw_key + $wpssw_prdcount;

							if ( ( (int) $update_id < (int) $wpssw_value ) && 'descorder' !== (string) $order_ascdesc ) {
								$wpssw_append                        = 1;
								$insert_row_indexes[ $sheet_name ][] = array(
									'start_index' => $insert_row_start_index,
									'end_index'   => $insert_row_end_index,
								);

								$update_row_indexes[ $sheet_name ][ $update_id ] = array(
									'start_index'  => $wpssw_startindex,
									'end_index'    => $wpssw_endindex,
									'order_id'     => $update_id,
									'values_count' => $wpssw_prdcount,

								);
								break;
							}
							if ( 'descorder' === (string) $order_ascdesc ) {
								if ( ( (int) $update_id > (int) $wpssw_value ) && (int) $wpssw_value > 0 ) {
									$wpssw_append                                    = 1;
									$wpssw_desc_append                               = 1;
									$insert_row_indexes[ $sheet_name ][]             = array(
										'start_index' => $insert_row_start_index,
										'end_index'   => $insert_row_end_index,
									);
									$update_row_indexes[ $sheet_name ][ $update_id ] = array(
										'start_index'  => $wpssw_startindex,
										'end_index'    => $wpssw_endindex,
										'order_id'     => $update_id,
										'values_count' => $wpssw_prdcount,
									);
									break;
								}
							}
						}
					}
				}
				if ( 0 === (int) $wpssw_desc_append && 'descorder' === (string) $order_ascdesc ) {
					if ( ! $highest_order_id || $update_id > $highest_order_id ) {
						$wpssw_append = 1;

						$end_index            = 2 + $wpssw_prdcount;
						$insert_row_end_index = 1 + $wpssw_prdcount;

						$insert_row_indexes[ $sheet_name ][]             = array(
							'start_index' => 1,
							'end_index'   => $insert_row_end_index,
						);
						$update_row_indexes[ $sheet_name ][ $update_id ] = array(
							'start_index'  => 2,
							'end_index'    => $end_index,
							'order_id'     => $update_id,
							'values_count' => $wpssw_prdcount,
						);
					}
				}
				if ( 0 === (int) $wpssw_append ) {

					$end_index            = count( $existingorders_sheet ) + 1 + $wpssw_prdcount;
					$insert_row_end_index = count( $existingorders_sheet ) + $wpssw_prdcount;

					$insert_row_indexes[ $sheet_name ][]             = array(
						'start_index' => count( $existingorders_sheet ),
						'end_index'   => $insert_row_end_index,
					);
					$update_row_indexes[ $sheet_name ][ $update_id ] = array(
						'start_index'  => count( $existingorders_sheet ) + 1,
						'end_index'    => $end_index,
						'order_id'     => $update_id,
						'values_count' => $wpssw_prdcount,
					);
				}
			}
		}

		$insertrequestarray  = array();
		$wpssw_inherit_style = self::wpssw_get_inherit_style( $wpssw_setting );
		foreach ( $insert_row_indexes as  $sheet_name => $insert_row ) {
			$requests                 = array();
			$insert_row_indexes_count = count( $insert_row );
			for ( $i = 0;$i < $insert_row_indexes_count;$i++ ) {
				$requests[] = array(
					'startIndex' => $insert_row[ $i ]['start_index'],
					'endIndex'   => $insert_row[ $i ]['end_index'],
				);
			}
			array_multisort( array_column( $requests, 'startIndex' ), SORT_DESC, $requests );
			$sheet_id = array_search( $sheet_name, $wpssw_existingsheetsnames, true );
			foreach ( $requests as $request ) {
				if ( false === (bool) $sheet_id ) {
					continue;
				}
				$param = array();
				$param = self::$instance_api->prepare_param( $sheet_id, $request['startIndex'], $request['endIndex'] );
				if ( 'no' === (string) $wpssw_inherit_style ) {
					$insertrequestarray[] = self::$instance_api->insertdimensionrequests( $param, 'ROWS', false );
				} else {
					$insertrequestarray[] = self::$instance_api->insertdimensionrequests( $param, 'ROWS', true );
				}
			}
			$requests = null;
		}

		if ( ! empty( $insertrequestarray ) ) {
			$insert_row_indexes = null;
			$wpssw_response     = self::$instance_api->updatebachrequests(
				array(
					'spreadsheetid' => $wpssw_spreadsheetid,
					'requestarray'  => $insertrequestarray,
				)
			);
			$wpssw_response     = null;
		}

		$update_row_data = array();
		$dimension       = 'ROWS';

		$update_row_indexes_new = array();

		foreach ( $update_row_indexes as  $sheet_name => $update_row ) {
			array_multisort( array_column( $update_row, 'start_index' ), SORT_ASC, $update_row );

			$requests                 = array();
			$update_row_indexes_count = count( $update_row );
			for ( $i = 0;$i < $update_row_indexes_count;$i++ ) {
				$new_start               = $update_row[ $i ]['start_index'];
				$same_startindex_rowkeys = array_keys( self::wpssw_convert_int( array_column( $update_row, 'start_index' ) ), (int) $update_row[ $i ]['start_index'], true );

				if ( count( $same_startindex_rowkeys ) > 1 && $same_startindex_rowkeys[0] < $i ) {
					continue;
				}

				for ( $j = 0;$j < $update_row_indexes_count;$j++ ) {
					if ( $j < $i ) {
						$new_start = $new_start + $update_row[ $j ]['values_count'];
					}
					if ( ( 0 === (int) $i || ( $i === $update_row_indexes_count - 1 && $j === $i ) ) && count( $same_startindex_rowkeys ) < 2 ) {
						$param = array();
						if ( 0 === (int) $i ) {
							$param['range'] = $sheet_name . '!A' . $update_row[ $i ]['start_index'];
						} else {
							$param['range'] = $sheet_name . '!A' . $new_start;
						}

						$orderid = $update_row[ $i ]['order_id'];

						if ( 'product' === (string) $wpssw_setting ) {
							$product = wc_get_product( $orderid );
							if ( false === $product || null === $product && is_wp_error( $product ) ) {
								continue;
							}
							if ( ! empty( $product->get_children() ) && 'grouped' !== (string) $product->get_type() ) {
								$wpssw_values_array[] = WPSSW_Product::wpssw_make_product_value_array( 'insert', $product->get_id() );
								foreach ( $product->get_children() as $child ) {
									$wpssw_values_array[] = WPSSW_Product::wpssw_make_product_value_array( 'insert', $child, true );
								}
							} else {
								$wpssw_values_array[] = WPSSW_Product::wpssw_make_product_value_array( 'insert', $product->get_id() );
							}
							$product = null;
						} elseif ( 'customer' === (string) $wpssw_setting ) {
							$customer = new WP_User( $orderid );
							if ( false === $customer || null === $customer && is_wp_error( $customer ) ) {
								continue;
							}
							$wpssw_values_array = array_merge( $wpssw_values_array, WPSSW_Customer::wpssw_make_customer_value_array( 'insert', $customer ) );
							$customer           = null;
						} elseif ( 'coupon' === (string) $wpssw_setting ) {
							$coupon = new WC_Coupon( $orderid );
							if ( false === $coupon || null === $coupon && is_wp_error( $coupon ) ) {
								continue;
							}
							$wpssw_values_array = array_merge( $wpssw_values_array, WPSSW_Coupon::wpssw_make_coupon_value_array( 'insert', $orderid ) );
							$coupon             = null;
						}

						$update_row_data[]  = new \Google_Service_Sheets_ValueRange(
							array(
								'range'          => $param['range'],
								'majorDimension' => $dimension,
								'values'         => $wpssw_values_array,
							)
						);
						$wpssw_values_array = null;
						break;
					}
					if ( (int) $update_row[ $i ]['start_index'] === (int) $update_row[ $j ]['start_index'] && $update_row[ $i ]['order_id'] !== $update_row[ $j ]['order_id'] ) {
						break;
					}

					if ( $update_row[ $i ]['start_index'] < $update_row[ $j ]['start_index'] ) {
						$param          = array();
						$param['range'] = $sheet_name . '!A' . $new_start;

						$orderid = $update_row[ $i ]['order_id'];

						if ( 'product' === (string) $wpssw_setting ) {
							$product = wc_get_product( $orderid );
							if ( false === $product || null === $product && is_wp_error( $product ) ) {
								continue;
							}
							if ( ! empty( $product->get_children() ) && 'grouped' !== (string) $product->get_type() ) {
								$wpssw_values_array[] = WPSSW_Product::wpssw_make_product_value_array( 'insert', $product->get_id() );
								foreach ( $product->get_children() as $child ) {
									$wpssw_values_array[] = WPSSW_Product::wpssw_make_product_value_array( 'insert', $child, true );
								}
							} else {
								$wpssw_values_array[] = WPSSW_Product::wpssw_make_product_value_array( 'insert', $product->get_id() );
							}
							$product = null;
						} elseif ( 'customer' === (string) $wpssw_setting ) {
							$customer = new WP_User( $orderid );
							if ( false === $customer || null === $customer && is_wp_error( $customer ) ) {
								continue;
							}
							$wpssw_values_array = array_merge( $wpssw_values_array, WPSSW_Customer::wpssw_make_customer_value_array( 'insert', $customer ) );
							$customer           = null;
						} elseif ( 'coupon' === (string) $wpssw_setting ) {
							$coupon = new WC_Coupon( $orderid );
							if ( false === $coupon || null === $coupon && is_wp_error( $coupon ) ) {
								continue;
							}
							$wpssw_values_array = array_merge( $wpssw_values_array, WPSSW_Coupon::wpssw_make_coupon_value_array( 'insert', $orderid ) );
							$coupon             = null;
						}

						$update_row_data[]  = new \Google_Service_Sheets_ValueRange(
							array(
								'range'          => $param['range'],
								'majorDimension' => $dimension,
								'values'         => $wpssw_values_array,
							)
						);
						$wpssw_values_array = null;
						break;
					}
					if ( count( $same_startindex_rowkeys ) > 1 ) {
						$same_row_start = $update_row[ $i ]['start_index'];
						for ( $k = 0;$k < $i;$k++ ) {
							$same_row_start = $same_row_start + $update_row[ $k ]['values_count'];
						}
						$new_start = $same_row_start;

						$same_rows = array();
						foreach ( $same_startindex_rowkeys as $same_rowkey ) {
							$same_rows[ $update_row[ $same_rowkey ]['order_id'] ] = $update_row[ $same_rowkey ];
						}
						$same_startindex_rowkeys = null;
						if ( 'descorder' === (string) $order_ascdesc ) {
							krsort( $same_rows );
						} else {
							ksort( $same_rows );
						}
						$same_rows_value = array();
						foreach ( $same_rows as $same_row ) {
							$orderid = $same_row['order_id'];

							if ( 'product' === (string) $wpssw_setting ) {
								$product = wc_get_product( $orderid );
								if ( false === $product || null === $product && is_wp_error( $product ) ) {
									continue;
								}
								if ( ! empty( $product->get_children() ) && 'grouped' !== (string) $product->get_type() ) {
									$wpssw_values_array[] = WPSSW_Product::wpssw_make_product_value_array( 'insert', $product->get_id() );
									foreach ( $product->get_children() as $child ) {
										$wpssw_values_array[] = WPSSW_Product::wpssw_make_product_value_array( 'insert', $child, true );
									}
								} else {
									$wpssw_values_array[] = WPSSW_Product::wpssw_make_product_value_array( 'insert', $product->get_id() );
								}
								$product = null;
							} elseif ( 'customer' === (string) $wpssw_setting ) {
								$customer = new WP_User( $orderid );
								if ( false === $customer || null === $customer && is_wp_error( $customer ) ) {
									continue;
								}
								$wpssw_values_array = array_merge( $wpssw_values_array, WPSSW_Customer::wpssw_make_customer_value_array( 'insert', $customer ) );
								$customer           = null;
							} elseif ( 'coupon' === (string) $wpssw_setting ) {
								$coupon = new WC_Coupon( $orderid );
								if ( false === $coupon || null === $coupon && is_wp_error( $coupon ) ) {
									continue;
								}
								$wpssw_values_array = array_merge( $wpssw_values_array, WPSSW_Coupon::wpssw_make_coupon_value_array( 'insert', $orderid ) );
								$coupon             = null;
							}

							$same_rows_value = array_merge( $same_rows_value, $wpssw_values_array );

							$wpssw_values_array = null;
						}

						$update_row_data[] = new \Google_Service_Sheets_ValueRange(
							array(
								'range'          => $sheet_name . '!A' . $new_start,
								'majorDimension' => $dimension,
								'values'         => $same_rows_value,
							)
						);
						$same_rows_value   = null;
						break;
					}
				}
			}
		}

		if ( ! empty( $update_row_data ) ) {
			$update_row_indexes = null;

			$requestobject                  = array();
			$requestobject['spreadsheetid'] = $wpssw_spreadsheetid;

			$requestobject['requestbody'] = new Google_Service_Sheets_BatchUpdateValuesRequest(
				array(
					'valueInputOption' => $wpssw_inputoption,
					'data'             => $update_row_data,
				)
			);
			$update_row_data              = null;
			$wpssw_response               = self::$instance_api->multirangevalueupdate( $requestobject );
			$wpssw_response               = null;
		}
	}
	/**
	 * Either add style format or remove it based on settings.
	 *
	 * @since 8.1
	 * @access public
	 * @param string $wpssw_setting name of the setting.
	 * @param array  $wpssw_params parameter values array.
	 */
	public static function wpssw_inherit_row_format_style( $wpssw_setting = 'order', $wpssw_params = array() ) {

		if ( empty( $wpssw_setting ) || empty( $wpssw_params ) ) {
			return;
		}
		$wpssw_inherit_style = self::wpssw_get_inherit_style( $wpssw_setting );
		if ( ( 'yes' === (string) $wpssw_inherit_style && (int) $wpssw_params['start_index'] <= 1 ) || 'none' === (string) $wpssw_inherit_style ) {
			return;
		}
		if ( isset( $wpssw_params['sheet_id'] ) && empty( $wpssw_params['sheet_id'] ) && isset( $wpssw_params['sheet_name'] ) && ! empty( $wpssw_params['sheet_name'] ) ) {

			$response                  = self::$instance_api->get_sheet_listing( $wpssw_params['spreadsheet_id'] );
			$wpssw_existingsheetsnames = self::$instance_api->get_sheet_list( $response );

			if ( isset( $wpssw_existingsheetsnames[ $wpssw_params['sheet_name'] ] ) ) {
				$wpssw_params['sheet_id'] = $wpssw_existingsheetsnames[ $wpssw_params['sheet_name'] ];
			} else {
				return;
			}
		}

		if ( 'yes' === (string) $wpssw_inherit_style ) {

			// Specify the request to inherit all formatting from the above row.
			$request = new Google_Service_Sheets_Request(
				array(
					'copyPaste' => array(
						'source'      => array(
							'sheetId'       => $wpssw_params['sheet_id'],
							'startRowIndex' => $wpssw_params['start_index'] - 1,
							'endRowIndex'   => $wpssw_params['start_index'],
						),
						'destination' => array(
							'sheetId'       => $wpssw_params['sheet_id'],
							'startRowIndex' => $wpssw_params['start_index'],
							'endRowIndex'   => $wpssw_params['end_index'],
						),
						'pasteType'   => 'PASTE_FORMAT',
					),
				)
			);
		} else {
			$dimension        = isset( $wpssw_params['dimension'] ) ? $wpssw_params['dimension'] : 'ROWS';
			$rowstartindex    = 0;
			$rowendindex      = 1;
			$columnstartindex = 0;
			$columnendindex   = 99;
			if ( 'ROWS' === (string) $dimension ) {
				$rowstartindex = $wpssw_params['start_index'];
				$rowendindex   = $wpssw_params['end_index'];
			} else {
				$columnstartindex = $wpssw_params['start_index'];
				$columnendindex   = $wpssw_params['end_index'];
			}
			// Remove formating from row.
			$request = array(
				new Google_Service_Sheets_Request(
					array(
						'updateCells' => array(
							'range'  => array(
								'sheetId'          => $wpssw_params['sheet_id'],
								'startRowIndex'    => $rowstartindex,  // Adjust the start row index accordingly.
								'endRowIndex'      => $rowendindex,    // Adjust the end row index accordingly.
								'startColumnIndex' => $columnstartindex,
								'endColumnIndex'   => $columnendindex,
							),
							'fields' => 'userEnteredFormat',
						),
					)
				),
			);
			if ( 'ROWS' === (string) $dimension ) {
				$request1 = array(
					new Google_Service_Sheets_Request(
						array(
							'autoResizeDimensions' => array(
								'dimensions' => array(
									'sheetId'    => $wpssw_params['sheet_id'],
									'dimension'  => $dimension,
									'startIndex' => $wpssw_params['start_index'],
									'endIndex'   => $wpssw_params['end_index'],
								),
							),
						)
					),
				);
				if ( ! is_null( $request ) && ! is_null( $request1 ) ) {
					$request = array_merge( $request, $request1 );
				}
			}
		}
		if ( ! is_null( $request ) ) {
			$param                  = array();
			$param['spreadsheetid'] = $wpssw_params['spreadsheet_id'];
			$param['requestarray']  = $request;
			$wpssw_response         = self::$instance_api->updatebachrequests( $param );
		}
	}
	/**
	 * Get inherit style option value for given setting.
	 *
	 * @since 8.1
	 * @access public
	 * @param string $wpssw_setting name of the setting.
	 * @return string inherit style option value for given setting.
	 */
	public static function wpssw_get_inherit_style( $wpssw_setting = 'order' ) {
		$wpssw_inherit_style = 'none';
		switch ( $wpssw_setting ) {
			case 'order':
			case 'event':
				$wpssw_inherit_style = self::wpssw_option( 'wpssw_order_inherit_style' );
				break;
			case 'product':
				$wpssw_inherit_style = self::wpssw_option( 'wpssw_product_inherit_style' );
				break;
			case 'coupon':
				$wpssw_inherit_style = self::wpssw_option( 'wpssw_coupon_inherit_style' );
				break;
			case 'customer':
				$wpssw_inherit_style = self::wpssw_option( 'wpssw_customer_inherit_style' );
				break;
			default:
				$wpssw_inherit_style = 'none';
		}
		return $wpssw_inherit_style;
	}
	/**
	 * Sanitize values.
	 *
	 * @since 8.1
	 * @access public
	 * @param mix $wpssw_values value to sanitize.
	 */
	public static function wpssw_sanitize_values( $wpssw_values ) {
		if ( is_array( $wpssw_values ) ) {
			foreach ( $wpssw_values as $key => $value ) {
				$wpssw_values[ $key ] = self::wpssw_sanitize_values( $value );
			}
		} else {
			$wpssw_values = sanitize_text_field( wp_unslash( $wpssw_values ) );
		}
		return $wpssw_values;
	}

	/**
	 * Check HPOS usage is enabled
	 */
	public static function wpssw_check_hpos_order_setting_enabled() {
		if ( class_exists( Automattic\WooCommerce\Utilities\OrderUtil::class ) ) {
			if ( OrderUtil::custom_orders_table_usage_is_enabled() ) {
				return true;// HPOS usage is enabled.
			} else {
				return false;// Traditional CPT-based orders are in use.
			}
		} else {
			return false;// Traditional CPT-based orders are in use.
		}
	}
	/**
	 * Get saved product category sheets option array value.
	 */
	public static function wpssw_get_product_category_saved_sheets() {
		$wpssw_productsheets_list = self::wpssw_option( 'wpssw_product_category_sheets' );

		if ( ! empty( $wpssw_productsheets_list ) && is_array( $wpssw_productsheets_list ) ) {
			$wpssw_productsheets_list = array_filter( array_unique( $wpssw_productsheets_list ) );
		} else {
			$wpssw_productsheets_list = array();
		}
		return $wpssw_productsheets_list;
	}
}
WPSSW_Setting::init();
