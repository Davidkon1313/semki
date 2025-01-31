<?php
/**
 * Class Hook
 * includes/class/Hook.php
 * Handle the actions and filters
 *
 * @package OrderSyncWithGoogleSheetForWooCommerce
 */

namespace OrderSyncWithGoogleSheetForWooCommerce;

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

if ( ! class_exists( '\OrderSyncWithGoogleSheetForWooCommerce\Hooks' ) ) {
	/**
	 * Handle all of the hooks
	 *
	 * @version 1.0.0
	 */
	class Hooks extends Base {
		/**
		 * Singleton mode
		 *
		 * @var null
		 */
		public static $instance = null;

		/**
		 * Manipulating the plugin activation will NOT unlock the premium features
		 *
		 *  @var null
		 */
		protected $license_active2 = false;
		/**
		 * Hook init
		 *
		 * @version 1.0.0
		 * @return void
		 */
		public static function init() {
			if ( ! self::$instance ) {
				self::$instance = new self();
			}
			self::$instance->add_filters();
			self::$instance->add_actions();
		}
		/**
		 * Add all actions
		 *
		 * @return void
		 * @version 1.0.0
		 */
		public function add_actions() {

			$this->order_sync_with_google_sheet_for_woocommerce_appsero();

			// Admin menu.
			add_action( 'admin_menu', [ $this, 'add_admin_menu' ] );
			// Redirect to admin page.
			add_action( 'admin_init', [ $this, 'redirect_to_admin_page' ], 1 );
			/**
			 * Scheduled Sync
			 */

			// Footer CSS for admin menu icon.
			add_action( 'admin_head', [ $this, 'admin_menu_icon_css' ] );

			// Admin enqueue scripts.
			add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ] );

			// Parse ID from Sheet URL and save for later use.
			add_action( 'osgsw_updated_spreadsheet_url', [ $this, 'updated_spreadsheet_url_callback' ] );
			// add_action('osgsw_updated_save_and_sync', [ $this, 'osgsw_sync_sheet_callback' ]);

			/**
			 * Insert order to sheet
			 */
			add_action( 'woocommerce_trash_order', [ $this, 'save_post_callback_trash' ], 10, 1 );
			add_action( 'woocommerce_update_order', [ $this, 'update_order_callback' ], 10, 2 );
			// add_action( 'woocommerce_untrash_order', [ $this, 'save_post_callback_untrash' ], 10, 1 );
			add_action( 'admin_notices', [ $this, 'bulk_action_notices' ] );
			// End insert order action hook fire.

			add_action( 'admin_footer', [ $this, 'improve_logo_size' ] );
			// Parse ID from Sheet URL and save for later use.

			add_action('wp_ajax_ossgw_appscript_improved', [ $this, 'ossgw_appscript_improved' ] );

			add_action('wp_ajax_ossgw_notice_skip', [ $this, 'ossgw_notice_skip' ] );
			add_action('wp_ajax_ossgw_already_updated', [ $this, 'osgsw_already_update_bulkd' ] );

			add_action('wp_ajax_ossgw_already_updated_trigger', [ $this, 'ossgw_already_updated_trigger' ] );

			add_action('woocommerce_thankyou', [ $this,'woocommerce_thankyou' ], 10, 1 );

			add_action('admin_init', [ $this, 'custom_order_status_sync' ] );
			add_action( "after_plugin_row_{$this->app->ultimate}",[ $this,'show_ult_update_notice' ], 10, 2);
		}
		/**
		 * Show Ult update notice
		 */
		public function show_ult_update_notice( $args, $response ) {
			if ( $this->app->is_ultimate_installed() && $this->app->is_ultimate_activated() && $this->get_ult_version() ) {
				?>
				<tr class="plugin-update-tr active">
					<td colspan="4" class="plugin-update colspanchange">
						<div class="update-message notice inline notice-warning notice-alt" style="padding: 8px;">
						<span class="dashicons dashicons-update" style="color:#d63638; margin-right:5px"></span><?php echo esc_html__('There is a new version of FlexOrder Ultimate available.','order-sync-with-google-sheets-for-woocommerce'); ?>
							<a target="_blank"  href="<?php echo esc_url('https://wppool.dev/my-account/?tab=downloads'); ?>" class="update-link" aria-label="<?php echo esc_html__('Update FlexOrder Ultimate now','order-sync-with-google-sheets-for-woocommerce'); ?>">
								<?php echo esc_html__('Download 1.1.0 version','order-sync-with-google-sheets-for-woocommerce'); ?>
							</a>
						</div>
					</td>
				</tr>
				<?php
			}
		}
		/**
		 * Get Ultimate Version
		 */
		public function get_ult_version() {
			$plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $this->app->ultimate );
			if ( $plugin_data ) {
				 $plugin_version = $plugin_data['Version'];
				if ( $plugin_version < '1.0.6' ) {
					return true;
				} else {
					return false;
				}
			}
			return true;
		}

		/**
		 * Sync customer order status in google sheet
		 */
		public function custom_order_status_sync() {

			$sheet_url = get_option( 'osgsw_spreadsheet_url', '' );
			if ( empty( $sheet_url ) ) {
				update_option( 'osgsw_new_user_activate_bulk', '1' );
				update_option( 'osgsw_new_user_activate_trigger_1_11_4', '1' );
			}

			if ( $this->app->is_plugin_ready() !== true ) {
				/**
				 * Reason for commenting this code is It was giving a fatal error when someone trying to create a new order after installing our plugin but without
				 * completing the setup.
				 *
				 * @todo: I think It's alright to return for now. later we can handle this exception.
				 */
				// throw new \Exception( 'Configure the plugin first.' );.
				return false;
			}
			$get_custom_order_status = ossgsw_get_order_statuses();
			$status_count = count( $get_custom_order_status );
			$get_preves_status = get_option('ssgsw_prev_status', 0);
			if ( $status_count != $get_preves_status) { //phpcs:ignore
				$sheet = new Sheet();
				$sheet->update_google_sheet_dropdowns();
				update_option('ssgsw_prev_status', $status_count);
			}
		}
		/**
		 * Update google sheet using by bulk edit
		 */
		public function bulk_action_notices() {
			if ( empty( $_REQUEST['bulk_action'] ) ) {
				return;
			}
			$order = new Order();
			$page = isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : '';
			$post_type = isset( $_REQUEST['post_type'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['post_type'] ) ) : '';
			$bulk_action    = isset( $_REQUEST['bulk_action'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['bulk_action'] ) ) : '';
			$sheet = new Sheet();
			if ( 'wc-orders' === $page || 'shop_order' === $post_type ) {
				if ( $bulk_action ) {
					$edit_ids = isset( $_REQUEST['ids'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['ids'] ) ) : '';
					if ( $edit_ids ) {

						$sheets_info = $sheet->get_first_columns();
						if ( empty($sheets_info) ) {
							$sheets_info = $sheet->get_first_columns();
						}
						$sheet_index = osgsw_format_index_number($sheets_info);
						$new_ids = explode(',', $edit_ids);
						if ( is_array($new_ids) ) {
							if ('untrashed' == $bulk_action) { //phpcs:ignore
								$first_id = isset($new_ids[0]) ? $new_ids[0] : false;
								if ( $first_id ) {
									$find_index_from_sheet = osgsw_get_index_number_option($first_id, $sheet_index);
									if ( ! $find_index_from_sheet ) {
										$get_row_values = $order->get_multiple_order_informations($new_ids);
										$appned = $sheet->append_batch_product_to_sheet($get_row_values, 1);
										if ( ! $appned ) {
											$sheet->append_batch_product_to_sheet($get_row_values, 1);
										}
									}
								}
} else if ('trashed' == $bulk_action) { //phpcs:ignore
								$new_index_info = [];
								foreach ( $new_ids as $new_id ) {
									$find_index_from_sheet = osgsw_get_index_number_option($new_id, $sheet_index);
									if ( $find_index_from_sheet ) {
										$get_prev_values = osgsw_get_previous_value($sheet_index, $new_id);
										if ( $get_prev_values ) {
											$start_index = $get_prev_values + 2;
											if ( $start_index !== $find_index_from_sheet ) {
												for ( $i = $start_index; $i <= $find_index_from_sheet; $i++ ) {
													$new_index_info[] = $i;
												}
											} else {
												$new_index_info[] = $start_index;
											}
										}
									}
								}

								$deleted = $sheet->delete_batch_rows($new_index_info);
								if ( ! $deleted ) {
									$sheet->delete_batch_rows($new_index_info);
								}
							} else {
								$get_row_values = $order->get_multiple_order_informations($new_ids);
								$batch_update = $sheet->update_batch_rows($get_row_values, $sheet_index);
								if ( ! $batch_update ) {
									$sheet->update_batch_rows($get_row_values, $sheet_index);
								}
							}
						}
					}
				}
			}
		}
		/**
		 * AppScript setup again
		 */
		public function ossgw_appscript_improved() {
			if ( isset( $_POST ) ) {
				$security = isset($_POST['nonce']) ? sanitize_text_field( wp_unslash($_POST['nonce']) ) : '';
				if ( ! isset( $security ) || ! wp_verify_nonce( $security, 'osgsw_nonce2' ) ) {
					wp_die( -1, 403 );
				}
				if ( ! current_user_can( 'manage_options' ) ) {
					return false;
				}
				if ( ! is_user_logged_in() ) {
					return false;
				}
				update_option('osgsw_setup_step', 4 );
				wp_send_json([
					'url' => admin_url('admin.php?page=osgsw-admin'),
				]);
			}
			die();
		}
		/**
		 * Notice skip
		 */
		public function ossgw_notice_skip() {
			if ( isset( $_POST ) ) {
				$security = isset($_POST['nonce']) ? sanitize_text_field( wp_unslash($_POST['nonce']) ) : '';
				if ( ! isset( $security ) || ! wp_verify_nonce( $security, 'osgsw_nonce2' ) ) {
					wp_die( -1, 403 );
				}
				if ( ! current_user_can( 'manage_options' ) ) {
					return false;
				}
				if ( ! is_user_logged_in() ) {
					return false;
				}
				update_option('osgsw_new_user_activate_bulk', '2' );
				wp_send_json([
					'success' => true,
				]);
			}
			die();
		}
		/**
		 * Hide Notice if already updated
		 */
		public function osgsw_already_update_bulkd() {
			if ( isset( $_POST ) ) {
				$security = isset($_POST['nonce']) ? sanitize_text_field( wp_unslash($_POST['nonce']) ) : '';
				if ( ! isset( $security ) || ! wp_verify_nonce( $security, 'osgsw_nonce2' ) ) {
					wp_die( -1, 403 );
				}
				if ( ! current_user_can( 'manage_options' ) ) {
					return false;
				}
				if ( ! is_user_logged_in() ) {
					return false;
				}
				update_option('osgsw_new_user_activate_bulk', '1' );
				update_option('osgsw_already_update_bulk', '1' );
				wp_send_json([
					'success' => true,
				]);
			}

			die();
		}
		/**
		 * Hide Notice if already updated
		 */
		public function ossgw_already_updated_trigger() {
			if ( isset( $_POST ) ) {
				$security = isset($_POST['nonce']) ? sanitize_text_field( wp_unslash($_POST['nonce']) ) : '';
				if ( ! isset( $security ) || ! wp_verify_nonce( $security, 'osgsw_nonce2' ) ) {
					wp_die( -1, 403 );
				}
				if ( ! current_user_can( 'manage_options' ) ) {
					return false;
				}
				if ( ! is_user_logged_in() ) {
					return false;
				}
				update_option('osgsw_new_user_activate_trigger_1_11_4', '1' );
				update_option('osgsw_already_update_trigger_1_11_4', '1' );
				wp_send_json([
					'success' => true,
				]);
			}

			die();
		}

		/**
		 * Initialize the plugin tracker
		 *
		 * @return void
		 */
		public function order_sync_with_google_sheet_for_woocommerce_appsero() {
			if ( ! class_exists( '\OrderSyncWithGoogleSheetForWooCommerce\Appsero\Client' ) ) {
				require_once OSGSW_INCLUDES . '/appsero/src/Client.php';
			}
			// appsero_is_local NOT.
			// add_filter( 'appsero_is_local', '__return_false' );.
			$clients = new \OrderSyncWithGoogleSheetForWooCommerce\Appsero\Client( '484c9ccb-8a17-46ba-ad67-6cf933cecdec', 'FlexOrder', OSGSW_FILE );
			// Active insights.
			$clients->insights()->init();
			// Active automatic updater.
			$this->is_license_active();
			// Init WPPOOL Plugin.
			if ( function_exists( 'wppool_plugin_init' ) ) {
				$default_image = OSGSW_URL . '/includes/ordersync-sdk/background-image.png';
				$osgs_plugin = wppool_plugin_init( 'order_sync_with_google_sheets_for_woocommerce', $default_image );
				$image = OSGSW_URL . '/includes/ordersync-sdk/OSGS.png';
				$from = '2024-11-21';
				$to = '2024-12-4';
				$cta_text = esc_html__( 'Grab Your Treat!', 'sheetstowptable' );
				if ( $osgs_plugin && is_object( $osgs_plugin ) && method_exists( $osgs_plugin, 'set_campaign' ) ) {
					$osgs_plugin->set_campaign($image, $to, $from, $cta_text );
				}
			}
		}

		/**
		 * Initialize license activation
		 *
		 * @return void
		 */
		public function is_license_active() {
			$ultimate = $this->app->is_ultimate_installed();
			$activated = $this->app->is_ultimate_activated();
			$license  = $this->app->is_ultimate_license_activated();
			$get_option_delete = get_option('ossgs_first_time_action', 0);
			$items = [ 'total_discount', 'add_shipping_details_sheet', 'show_order_date', 'show_payment_method', 'show_customer_note', 'show_order_url', 'who_place_order', 'show_product_qt', 'show_billing_details', 'show_custom_meta_fields', 'custom_order_status_bolean', 'license_active', 'show_order_note' ];
			if ( ! $ultimate || ! $license || ! $activated ) {
				if ( ! $get_option_delete ) {
					$value = false;
					foreach ( $items as $item ) {
						update_option( OSGSW_PREFIX . $item, $value );
					}
					update_option('ossgs_first_time_action',1);
				}
			} else {
				delete_option('ossgs_first_time_action');
			}
		}
		/**
		 * Improve the logo size
		 *
		 * @since 1.0.0
		 */
		public function improve_logo_size() {
			?>
				<style>
					#adminmenu .toplevel_page_osgsw-admin div.wp-menu-image img {
						width: 25px !important;
						height: 25px !important;
						opacity: 100%;
					}
					.osgsw-wrapper .ssgs-welcome__right .pro-link {
						margin-top: -30px !important;
					}
					.siz-modal.position-top-right {
						top: 65px !important;
						right: 0;
						transform: translateX(0);
					}
					.select2-container .select2-selection {
						height: auto !important;
					}
				</style>
			<?php
		}

		/**
		 * Is Ultimate License activated?
		 *
		 * @return bool
		 * @version 1.0.0
		 */
		public function is_ultimate_licensesss_activated() {
			$ultimate_license = get_option( 'osgsw_license_active' );
			if ( true == $ultimate_license ) { //phpcs:ignore
				return true;
			}
			return false;
		}
		/**
		 * Convart status google sheet format.
		 *
		 * @param string $status Order status.
		 *
		 * @return string
		 */
		public function status_convart( $status ) {
			$status = strtolower( $status );
			if ( 'on-hold' === $status ) {
				return 'wc-on-hold';
			} else if ( 'completed' === $status || 'complete' === $status ) {
				return 'wc-completed';
			} else if ( 'processing' === $status ) {
				return 'wc-processing';
			} else if ( 'cancelled' === $status || 'cancel' === $status ) {
				return 'wc-cancelled';
			} else if ( 'failed' === $status ) {
				return 'wc-failed';
			} else if ( 'pending' === $status || 'pending payment' === $status ) {
				return 'wc-pending';
			} else if ( 'refunded' === $status || 'refund' === $status ) {
				return 'wc-refunded';
			} else if ( 'draft' === $status ) {
				return 'wc-checkout-draft';
			} else {
				return 'wc-' . $status;
			}
		}
		/**
		 * Add all filters
		 *
		 * @version 1.0
		 * returns void
		 */
		public function add_filters() {
			// Add promotional link to plugin action links.
			add_filter( 'plugin_action_links_' . plugin_basename( OSGSW_FILE ), [ $this, 'add_plugin_action_links' ] );

			// Add promotional link to plugin meta links
			// add_filter('plugin_row_meta', array($this, 'add_plugin_meta_links'), 10, 2);.
			// osgsw_save_option_credentials_callback.
			// add_filter('osgsw_update_credentials', 'json_encode');.
			add_filter( 'osgsw_get_credentials', [ $this, 'osgsw_get_credentials_callback' ] );
			add_filter( 'osgsw_order_limit', [ $this, 'no_limit' ] );
		}
		/**
		 * Set limit for order sync
		 */
		public function no_limit() {
			return false;
		}
		/**
		 * Add admin menu
		 */
		public function add_admin_menu() {
			add_menu_page(
				__( 'FlexOrder', 'order-sync-with-google-sheets-for-woocommerce' ),
				__( 'FlexOrder', 'order-sync-with-google-sheets-for-woocommerce' ),
				'manage_options',
				'osgsw-admin',
				[ $this, $this->app->is_setup_complete() ? 'render_admin_page' : 'render_setup_page' ],
				OSGSW_PUBLIC . 'images/logos.svg',
				56
			);

			if ( ! $this->app->is_setup_complete() ) {
				add_submenu_page(
					'osgsw-admin',
					__( 'FlexOrder', 'order-sync-with-google-sheets-for-woocommerce' ),
					__( 'Setup', 'order-sync-with-google-sheets-for-woocommerce' ),
					'manage_options',
					'osgsw-admin',
					[ $this, 'render_setup_page' ],
					0
				);
			} else {
				add_submenu_page(
					'osgsw-admin',
					__( 'FlexOrder', 'order-sync-with-google-sheets-for-woocommerce' ),
					__( 'Settings', 'order-sync-with-google-sheets-for-woocommerce' ),
					'manage_options',
					'osgsw-admin',
					[ $this, 'render_admin_page' ],
					99
				);
			}
		}

		/**
		 * Render admin page
		 */
		public function render_admin_page() {
			$this->load_template( 'dashboard/base' );
		}
		/**
		 * Render setup page
		 */
		public function render_setup_page() {
			$this->app->reset_options( false );
			$this->load_template( 'setup/base' );
		}
		/**
		 * Redirect to admin page
		 */
		public function redirect_to_admin_page() {

			$redirect_to_admin_page = osgsw_get_option( 'redirect_to_admin_page', 0 );
			if ( 1 == $redirect_to_admin_page ) { //phpcs:ignore
				osgsw_update_option( 'redirect_to_admin_page', 0 );
				wp_redirect( admin_url( 'admin.php?page=osgsw-admin' ) );
				exit;
			}
		}

		/**
		 * Add settings link
		 *
		 * @param array $links plgin links.
		 * @return array
		 */
		public function add_plugin_action_links( $links ) {
			if ( $this->app->is_setup_complete() ) {
				$links[] = '<a href="' . admin_url( 'admin.php?page=osgsw-admin' ) . '">' . __( 'Settings', 'order-sync-with-google-sheets-for-woocommerce' ) . '</a>';
			} else {
				$links[] = '<a href="' . admin_url( 'admin.php?page=osgsw-admin' ) . '">' . __( 'Setup', 'order-sync-with-google-sheets-for-woocommerce' ) . '</a>';
			}

			if ( ! $this->is_ultimate_licensesss_activated() ) {
				$links[] = '<a class="osgsw-promo osgsw-ultimate-button small" href="javascript:;"' . __( '<span class="osgsw-ultimate-button">Get Ultimate</span>', 'order-sync-with-google-sheets-for-woocommerce' ) . '</a>';
			}
			return $links;
		}
		/**
		 * Add settings link
		 *
		 * @param array  $links all meta links.
		 * @param string $file current file.
		 * @return array
		 */
		public function add_plugin_meta_links( $links, $file ) {
			if ( plugin_basename( OSGSW_FILE ) === $file ) {
				$links[] = '<a target="_blank" href="#"> <span class="dashicons dashicons-media-document" aria-hidden="true" style="font-size:16px;line-height:1.2"></span>' . __( 'Docs', 'order-sync-with-google-sheets-for-woocommerce' ) . '</a>';
				$links[] = '<a target="_blank" href="#"> <span class="dashicons dashicons-editor-help" aria-hidden="true" style="font-size:16px;line-height:1.2"></span>' . __( 'Support', 'order-sync-with-google-sheets-for-woocommerce' ) . '</a>';
			}
			return $links;
		}

		/**
		 * Add admin menu icon
		 */
		public function admin_menu_icon_css() {
			echo '<style>                
				#adminmenu .toplevel_page_osgsw-admin div.wp-menu-image img { 
					width: 18px;
					height: 18px;
				}
			</style>';
		}
		/**
		 * Admin enqueue scripts
		 *
		 * @param array $hook hook.
		 */
		public function admin_enqueue_scripts( $hook ) {
			wp_enqueue_style( 'osgsw-global-css', OSGSW_PUBLIC . 'css/global.css', [], microtime(), 'all' );
			$pages = [ 'toplevel_page_osgsw-admin', 'order-sync-with-google-sheets_page_osgsw-settings', 'edit.php', 'plugins.php', 'index.php', 'woocommerce_page_wc-orders' ];

			if ( in_array($hook, $pages) ) {
				wp_enqueue_script('osgsw-notice-js', OSGSW_PUBLIC . 'js/notice.js', [ 'jquery' ], time(), true);
				wp_localize_script('osgsw-notice-js', 'osgsw_notice_data', [
					'ajax_url' => admin_url('admin-ajax.php'),
					'nonce'    => wp_create_nonce('osgsw_nonce2'),
				]);
			}

			if ( ! in_array( $hook, $pages ) ) {
				return;
			}
			wp_enqueue_style( 'osgsw-select2', OSGSW_PUBLIC . 'css/select2.css', [], time(), 'all' );
			wp_enqueue_style( 'osgsw-admin-css', OSGSW_PUBLIC . 'css/admin.min.css', [], microtime(), false );

			wp_enqueue_script( 'osgsw-select2', OSGSW_PUBLIC . 'js/select2.js', [ 'jquery' ], time(), true );
			wp_enqueue_script( 'osgsw-admin-js', OSGSW_PUBLIC . 'js/admin.min.js', [ 'jquery' ], microtime(), true );
			wp_enqueue_script( 'osgsw-headway','//cdn.headwayapp.co/widget.js', [], microtime(), true );
			// localize script.
			wp_localize_script( 'osgsw-admin-js', 'osgsw_script', $this->app->localized_script() );
		}
		/**
		 * Updated spreadsheet url callback.
		 *
		 * @param string $spreadsheet_url Spreadsheet url.
		 * @since 1.0.0
		 */
		public function updated_spreadsheet_url_callback( $spreadsheet_url ) {
			/**
			 * Get Sheet ID from Sheet URL Regex
			 */
			$sheet_id = preg_replace( '/^.*\/d\/(.*)\/.*$/', '$1', $spreadsheet_url );

			/**
			 * Get Sheet ID from Sheet URL
			 */

			if ( empty( $sheet_id ) ) {
				$sheet_id = preg_replace( '/^.*\/d\/(.*)$/', '$1', $spreadsheet_url );

				if ( empty( $sheet_id ) ) {
					$sheet_id = preg_replace( '/^.*\/(.*)$/', '$1', $spreadsheet_url );
				}
			}

			osgsw_update_option( 'spreadsheet_id', $sheet_id );
		}

		/**
		 * Get credentials callback.
		 *
		 * @param string $credentials Credentials.
		 * @return array
		 */
		public function osgsw_get_credentials_callback( $credentials ) {
			if ( is_array($credentials) ) {
				// If $credentials is already an array, no need to decode it.
				return array_map('wp_unslash', $credentials);
			} else {
				// If $credentials is a string, decode it and then apply array_map.
				$credentials = json_decode($credentials, true);
				return is_array($credentials) ? array_map('wp_unslash', $credentials) : [];
			}
		}
		/**
		 * Osgsw save option freeze headers callback
		 *
		 * @param mixed $value value.
		 * @version 1.0.0
		 * @return void
		 */
		public function osgsw_save_option_freeze_headers_callback( $value ) {

			$sheet = new Sheet();
			$updated = $sheet->freeze_headers( true === $value || 1 === $value );
			$this->send_json( $updated );
		}

		/**
		 * Osgsw Sync sheet callback
		 *
		 * @version 1.0.0
		 * @return bool
		 */
		public function osgsw_sync_sheet_callback() {
			if ( isset( $GLOBALS['osgs_sync_all_orders'] ) && true === $GLOBALS['osgs_sync_all_orders'] ) {
				return false;
			}
			$this->custom_order_status_sync();
			$order = new Order();
			$order->sync_all();
		}

		/**
		 * Update post callback.
		 *
		 * @param int      $post_id Post ID.
		 * @param \WP_Post $post Post object.
		 * @return void
		 */
		public function update_order_callback( $post_id, $post ) {
			$bulk_action    = isset( $_REQUEST['bulk_action'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['bulk_action'] ) ) : '';
			if ( $bulk_action ) {
				return;
			}
			if ( isset($_SERVER['HTTP_REFERER']) ) {
				$referer_request = esc_url($_SERVER['HTTP_REFERER']);
			}
			if ( ! ssgsw_is_barcode_scanner_plugin_active() ) {
				if ( is_admin() ) {
					$status = $this->get_order_status($post_id);
					if ( 'trash' !== $status && 'untrash' !== $status && 'auto-draft' !== $status ) {
						$order = new Order();
						$sheet = new Sheet();
						$sheets_info = $sheet->get_first_columns();
						if ( empty($sheets_info) ) {
							$sheets_info = $sheet->get_first_columns();
						}
						$order->batch_update_delete_and_append($post_id, 'update', null, $sheets_info );
					}
				}
			} else {
				$cart_page_id = wc_get_page_id('cart');
				$cart_slug = get_post_field('post_name', $cart_page_id);
				$checkout_page_id = wc_get_page_id('checkout');
				$checkout_slug = get_post_field('post_name', $checkout_page_id);
				if ( strpos($referer_request, $cart_slug ) === false && strpos($referer_request, $checkout_slug ) === false ) {
					$status = $this->get_order_status($post_id);
					if ( 'trash' !== $status && 'untrash' !== $status && 'auto-draft' !== $status ) {
						$order = new Order();
						$sheet = new Sheet();
						$sheets_info = $sheet->get_first_columns();
						if ( empty($sheets_info) ) {
							$sheets_info = $sheet->get_first_columns();
						}
						$order->batch_update_delete_and_append($post_id, 'update', null, $sheets_info );
					}
				}
			}
		}
		/**
		 * Get Order status by order identifier
		 *
		 * @param int $order_id Order identifier.
		 * @return string Order status.
		 */
		public function get_order_status( $order_id ) {
			global $wpdb;
			$custom_order_table = get_option( 'woocommerce_custom_orders_table_enabled' );
			if ( isset( $custom_order_table ) && 'yes' === $custom_order_table ) {
				return $wpdb->get_var($wpdb->prepare("SELECT status FROM {$wpdb->prefix}wc_orders WHERE id = %d AND type = 'shop_order'", $order_id));
			} else {
				return $wpdb->get_var($wpdb->prepare("SELECT post_status FROM {$wpdb->prefix}posts WHERE ID = %d AND post_type = 'shop_order'", $order_id));
			}
		}
		/**
		 * Append woocommerce order data to sheet.
		 *
		 * @param int $post_id order id.
		 *
		 * @return void
		 */
		public function woocommerce_thankyou( $post_id ) {
			$order = new Order();
			$order->batch_update_delete_and_append($post_id, 'append', null, [] );
		}
		/**
		 * Saves post callback.
		 *
		 * @param int $post_id Post ID.
		 *
		 * @return void
		 */
		public function save_post_callback_trash( $post_id ) {
			$bulk_action    = isset( $_REQUEST['bulk_action'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['bulk_action'] ) ) : '';
			if ( $bulk_action ) {
				return;
			}
			$order = new Order();
			$sheet = new Sheet();
			$sheets_info = $sheet->get_first_columns();
			if ( is_array($sheets_info) && empty($sheets_info) ) {
				$sheets_info = $sheet->get_first_columns();
			}

			$order->batch_update_delete_and_append($post_id, 'trash', null, $sheets_info );
		}
		/**
		 * Untrash callback.
		 *
		 * @param int $post_id Post ID.
		 *
		 * @return void
		 */
		public function save_post_callback_untrash( $post_id ) {
			$order = new Order();

			$order->batch_update_delete_and_append($post_id, 'untrash', null, [] );
		}
	}
	/**
	 * Initialize Hook
	 */

	Hooks::init();
}
