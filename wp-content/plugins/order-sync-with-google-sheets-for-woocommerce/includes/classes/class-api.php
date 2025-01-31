<?php
/**
 * Class API
 * includes/class/API.php
 * Handles the API requests
 *
 * @package OrderSyncWithGoogleSheetForWooCommerce
 */

namespace OrderSyncWithGoogleSheetForWooCommerce;

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

if ( ! class_exists( '\OrderSyncWithGoogleSheetForWooCommerce\API' ) ) {
	/**
	 * API Class Handles the API requests
	 *
	 * @version 1.0.0
	 */
	class API extends Base {

		/**
		 * Singleton mode
		 *
		 * @var bool
		 */
		public static $instance = null;
		/**
		 * API init for singleton instance
		 *
		 * @var bool
		 * @version 1.0.0
		 * @return void
		 */
		public static function init() {
			if ( ! self::$instance ) {
				self::$instance = new self();
			}
			self::$instance->register_routes();
		}

		/**
		 * Register API  routes
		 *
		 * @version 1.0.0
		 * @return void
		 */
		public function register_routes() {
			add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );
		}
		/**
		 * Add API routes
		 *
		 * @version 1.0.0
		 * @return void
		 */
		public function add_api_routes() {
			$routes = [
				'action' => [
					'methods'             => [ 'GET', 'POST' ],
					'callback'            => [ $this, 'action_api_callback' ],
					'permission_callback' => [ $this, 'permission_callback' ],
				],

				'update' => [
					'methods'             => [ 'GET', 'POST' ],
					'callback'            => [ $this, 'update_api_callback' ],
					'permission_callback' => [ $this, 'permission_callback' ],
				],
			];
			foreach ( $routes as $route => $args ) {
				register_rest_route( 'osgsw/v1', $route, $args );
			}
		}


		/**
		 * Permission callback
		 *
		 * @version 1.0.0
		 * @return bool
		 */
		public function permission_callback() {
			if ( defined( 'OSGSW_DEBUG' ) && OSGSW_DEBUG ) {
				return true;
			}
			$bearer2 = isset($_SERVER['HTTP_AUTHORIZATION']) ? sanitize_text_field( wp_unslash($_SERVER['HTTP_AUTHORIZATION'] ) ) : '';
			$bearer3 = isset($_SERVER['HTTP_OSGSWKEY']) ? sanitize_text_field( wp_unslash($_SERVER['HTTP_OSGSWKEY'] ) ) : '';
			$bearer = '';
			if ( empty($bearer3) ) {
				$bearer = $bearer2;
			}
			if ( empty($bearer2) ) {
				$bearer = $bearer3;
			}
			if ( empty($bearer) ) {
				return false;
			}

			$bearer      = str_replace('Bearer ', '', $bearer);
			$saved_token = osgsw_get_option('token');

			if ( empty($saved_token) ) {
				return false;
			}

			if ( $bearer !== $saved_token ) {
				return false;
			}

			return true;
		}
		/**
		 * Responses for API
		 *
		 * @param bool  $success Success status.
		 * @param mixed $data Data to return.
		 * @return \WP_REST_Response
		 */
		public function response( $success = true, $data = null ) {
			$response = [
				'success' => $success,
			];

			if ( $data ) {
				if ( is_object( $success ) || is_array( $success ) ) {
					$response['data'] = $data;
				} else {
					$response['message'] = $data;
				}
			}

			return new \WP_REST_Response( $response );
		}

		/**
		 * Request callback
		 *
		 * @param object $request response.
		 *
		 * @return array
		 * @version 1.0.0
		 */
		public function action_api_callback( $request ) {
			$params = $request->get_params();
			$action = $params['action'] ?? null;
			if ( ! $action ) {
				return $this->response(
					false,
					__( 'No action specified', 'order-sync-with-google-sheets-for-woocommerce' )
				);
			}

			$action = strtolower( $action );

			if ( ! method_exists( $this, 'action_' . $action ) ) {
				return $this->response(
					false,
					__( 'Action not found', 'order-sync-with-google-sheets-for-woocommerce' )
				);
			}
			try {
				return $this->{'action_' . $action}( $request );
			} catch ( \Exception $e ) {
				return $this->response( false, $e->getMessage() );
			}
		}
		/**
		 * Callback for action sync.
		 *
		 * @param \WP_REST_Request $request Request.
		 * @return mixed Response.
		 */
		public function action_sync( $request ) {
			$params  = $request->get_params();
			$message = $params['message'] ?? __('Order synced successfully', 'order-sync-with-google-sheet-for-woocommerce');
			$osgsw_items = isset( $params['osgsw_items'] ) ? $params['osgsw_items'] : false;
			$osgsw_offset = isset( $params['osgsw_offset'] ) ? $params['osgsw_offset'] : 0;
			$osgsw_batch_size = isset( $params['osgsw_batch_size'] ) ? $params['osgsw_batch_size'] : 0;
			$order = new Order();
			try {
				if ( isset($params['osgsw_items']) ) {
					if ( $osgsw_items ) {
						$response = osgsw_order_count();
						if ( $response ) {
							return $this->response(
								true,
								$response
							);
						} else {
							return $this->response(
								false,
								__('Something went wrong', 'order-sync-with-google-sheet-for-woocommerce')
							);
						}
					} else {
						update_option('osgsw_license_sync', false);
						$update = $order->sync_batch_all($osgsw_offset, $osgsw_batch_size);
						if ( $update ) {
							return $this->response(
								true,
								$message
							);
						} else {
							return $this->response(
								true,
								$message
							);
						}
					}
				} else {
					$order = new Order();
					$response = $order->sync_all( true );
					if ( $response ) {
						return $this->response(
							true,
							$message
						);
					} else {
						return $this->response(
							false,
							__( 'Something went wrong', 'order-sync-with-google-sheets-for-woocommerce' )
						);
					}
				}
			} catch ( \Exception $e ) {
				return $this->response(false, $e->getMessage());
			}
		}
		/**
		 * Callback for action update.
		 *
		 * @param \WP_REST_Request $request Request.
		 * @return mixed Response.
		 */
		public function update_api_callback( $request ) {
			$body = $request->get_params();
			$orders = $body['orders'] ?? null;
			$order_length = isset( $body['arrayLength'] ) ? $body['arrayLength'] : 1;
			$first_index = isset( $body['firstIndex'] ) ? $body['firstIndex'] : 0;
			$last_index = isset( $body['lastIndex'] ) ? $body['lastIndex'] : 0;
			$new_app = isset( $body['newapp'] ) ? $body['newapp'] : 0;

			if ( $orders && is_array( $orders ) && ! empty( $orders ) ) {
				try {
					$order = new Order();
					if ( $new_app ) {
						$response = $order->bulk_chank_update( $orders, $first_index, $last_index );
						return $this->response(
							true,
							$response,
							__( 'Order synced with WooCommerce successfully', 'order-sync-with-google-sheets-for-woocommerce' )
						);
					} else {
						$response = $order->bulk_update( $orders );
						return $this->response(
							true,
							$response,
							__( 'Order synced with WooCommerce successfully', 'order-sync-with-google-sheets-for-woocommerce' )
						);
					}
				} catch ( \Exception $e ) {
					return $this->response( false, $e->getMessage() );
				}
			} else {
				return $this->response(
					false,
					__( 'No orders specified', 'order-sync-with-google-sheets-for-woocommerce' )
				);
			}

			return $this->response( true, $orders );
		}
	}
	/**
	 * Kick out the API Class
	 *
	 * @version 1.0.0
	 */
	API::init();
}
