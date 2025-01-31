<?php


/**
 * Handles the POPUP after plugin acitvation
 *
 * @package OrderSyncWithGoogleSheetForWooCommerce
 * @since 1.3.2
 */

// Namespace.
namespace OrderSyncWithGoogleSheetForWooCommerce;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( '\OrderSyncWithGoogleSheetForWooCommerce\Popup' ) ) {

	/**
	 * Class Popup.
	 * Handles the plugin activation and deactivation process and admin notices for FlexOrder.
	 *
	 * @param int    $osgsw_install_time time of banner install.
	 * @param string $current_dir directory to access.
	 * @package OrderSyncWithGoogleSheetForWooCommerce
	 **/
	class Popup {

		/**
		 * Time of banner install.
		 *
		 * @var int
		 */
		protected $osgsw_install_time;

		/**
		 * Time of notice update.
		 *
		 * @var int
		 */
		protected $osgsw_update_notice;

		/**
		 * Directory to access.
		 *
		 * @var string
		 */
		protected $current_dir;

		/**
		 * Initialize the constructor class.
		 *
		 * @return void
		 */
		public function __construct() {
			$this->osgsw_install_time = get_option( 'osgsw_install_times' );
			$this->osgsw_update_notice = get_option( 'osgsw_update_notice' );
			$this->current_dir = dirname( __DIR__ );
			// add_action( 'admin_init', [ $this, 'osgsw_show_popup' ] );
			add_action( 'wp_ajax_osgsw_popup_handle', [ $this, 'handle_popup' ] );
		}

		/**
		 * Handle review rating popup
		 *
		 * @return void
		 */
		public function handle_popup() {
			if ( isset( $_POST ) ) {
				$security = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
				if ( ! isset( $security ) || ! wp_verify_nonce( $security, 'osgsw_nonce2' ) ) {
					wp_die( -1, 403 );
				}
				$value = isset( $_POST['value'] ) ? sanitize_text_field( wp_unslash( $_POST['value'] ) ) : '0';
				update_option( 'osgsw_days_count', $value );
				update_option( 'osgsw_install_times', time() );
				wp_send_json_success(
					[
						'days_count' => esc_attr( $value ),
						'time' => time(),
					]
				);
			}
			wp_die();
		}
		/**
		 * OSGS rating popup
		 *
		 * @return void
		 */
		public function osgsw_show_popup() {
			$days_count = get_option( 'osgsw_days_count' );

			if ( empty( $this->osgsw_install_time ) ) {
				update_option( 'osgsw_install_times', time() );
				update_option( 'osgsw_update_notice', time() );
				update_option( 'osgsw_days_count', 7 );
			} else {
				$days_elapsed = floor( ( time() - $this->osgsw_install_time ) / ( 60 * 60 * 24 ) );

				$days_update_elapsed = floor( ( time() - absint( $this->osgsw_update_notice ) ) / ( 60 * 60 * 24 ) );
				if ( ( $days_elapsed >= intval( $days_count ) && ( '0' === $days_count || '7' === $days_count || '14' === $days_count ) ) ) {
					add_action( 'admin_footer', [ $this, 'osgsw_rating_popup' ] );
				}

				if ( ( $days_update_elapsed >= 10 ) ) {
					add_action( 'admin_footer', [ $this, 'osgsw_upgrade_banner' ] );
				}

				if ( ( $days_update_elapsed >= 14 ) ) {
					add_action( 'admin_footer', [ $this, 'osgsw_influencer_banner' ] );
				}
			}
		}

		/**
		 * Show rating popup
		 *
		 * @return void
		 */
		public function osgsw_rating_popup() {
			?>
			<div class="osgs-rating-banner">
				<img class="osgs-image-icon" src="<?php echo esc_url( plugins_url( 'public/images/top-banner/rating-left-star.svg', $this->current_dir ) ); ?>" alt="">
				<img class="osgs-image-icon-mobile" src="<?php echo esc_url( plugins_url( 'public/images/top-banner/message-mobile.svg', $this->current_dir ) ); ?>" alt="">
				<span class="osgs-rating-close"></span>
				<span class="osgs-already-rated"><?php esc_html_e( 'I already did it', 'order-sync-with-google-sheets-for-woocommerce' ); ?></span>
				<div class="osgs-rating-wrapper">
					<h3><?php esc_html_e( 'Seems like ', 'order-sync-with-google-sheets-for-woocommerce' ); ?> <span class="osgs-upgrade-span"><?php esc_html_e( 'FlexOrder ', 'order-sync-with-google-sheets-for-woocommerce' ); ?></span><?php esc_html_e( 'is bringing you value 🥳', 'order-sync-with-google-sheets-for-woocommerce' ); ?></h3>
					<p><?php esc_html_e( 'Hi there! You\'ve been using FlexOrder for a while. Would you consider leaving us a 😍 5-star review?', 'order-sync-with-google-sheets-for-woocommerce' ); ?></br>
					<?php esc_html_e( 'Your feedback will help us to develop better features and spread the word.', 'order-sync-with-google-sheets-for-woocommerce' ); ?></p>
					<span><?php esc_html_e( 'Please Rate Us:', 'order-sync-with-google-sheets-for-woocommerce' ); ?></span>
					<div class="rating-container">
						<span class="osgs-yellow-icon"></span>
						<span class="osgs-yellow-icon"></span>
						<span class="osgs-yellow-icon"></span>
						<span class="osgs-yellow-icon"></span>
						<span class="osgs-yellow-icon"></span>
					</div>
				</div>
			</div>
			<div id="popup1" class="osgsw_popup-container" style="display: none;">
				<div class="osgsw_popup-content" style="display: none;">
					<a href="#" target="_blank" class="close osgsw_close_button">&times;</a>
					<div class="osgsw_first_section2" style="display:none">
						<div class="osgsw_popup_wrap">
							<h4>Would you like to be remind in the future?</h4>
						</div>
						<div class="osgsw_select-wrapper">
							<span class="remind-title">Remind Me After: </span>
							<div class="osgsw-days-dropdown">
								<div class="selected-option" data-days="7"><?php esc_html_e( '7 Days', 'order-sync-with-google-sheets-for-woocommerce' ); ?></div>
								<ul class="options">
									<li data-value="7"><?php esc_html_e( '7 Days', 'order-sync-with-google-sheets-for-woocommerce' ); ?></li>
									<li data-value="14"><?php esc_html_e( '14 Days', 'order-sync-with-google-sheets-for-woocommerce' ); ?></li>
									<li data-value="1"><?php esc_html_e( 'Remind me never', 'order-sync-with-google-sheets-for-woocommerce' ); ?></li>
								</ul>
							</div>
							<div class="osgsw_button-wrapper">
								<button class="osgsw_custom-button osgsw_submit_button2"><?php esc_html_e( 'Ok', 'order-sync-with-google-sheets-for-woocommerce' ); ?></button>
							</div>
						</div>
					</div>
				</div>
			</div>
			<?php
		}

		/**
		 * OSGS upgrade banner
		 *
		 * @return void
		 */
		public function osgsw_upgrade_banner() {
			?>
			<div class="osgs-upgrade-banner">
				<img class="osgs-image-icon" src="<?php echo esc_url( plugins_url( 'public/images/top-banner/pink-diamond.svg', $this->current_dir ) ); ?>" alt="">
				<img class="osgs-image-icon-mobile" src="<?php echo esc_url( plugins_url( 'public/images/top-banner/diamond-mobile.svg', $this->current_dir ) ); ?>" alt="">
				<span class="osgs-upgrade-close"></span>
				<div class="content">
					<h3><?php esc_html_e( 'Supercharge your order management with ', 'order-sync-with-google-sheets-for-woocommerce' ); ?> <span><?php esc_html_e( 'FlexOrder Ultimate', 'order-sync-with-google-sheets-for-woocommerce' ); ?></span> 😍</h3>
					<div class="link-wrapper">
						<a href="<?php echo esc_url( 'https://go.wppool.dev/Zf3a' ); ?>" class="upgrade-button"><?php esc_html_e( 'Upgrade Now', 'order-sync-with-google-sheets-for-woocommerce' ); ?> <span></span></a>
					</div>
				</div>
			</div>
			<?php
		}

		/**
		 * OSGS influencer banner
		 *
		 * @return void
		 */
		public function osgsw_influencer_banner() {
			?>
			<div class="osgs-influencer-banner">
				<img class="osgs-image-icon" src="<?php echo esc_url( plugins_url( 'public/images/top-banner/purple-thumbs-up.svg', $this->current_dir ) ); ?>" alt="">
				<img class="osgs-image-icon-mobile" src="<?php echo esc_url( plugins_url( 'public/images/top-banner/thumbs-up-mobile.svg', $this->current_dir ) ); ?>" alt="">
				<span class="osgs-influencer-close"></span>
				<div class="osgs-influencer-wrapper">
					<h3><?php esc_html_e( 'Hey! Enjoying the FlexOrder plugin? 😍 Join our ', 'order-sync-with-google-sheets-for-woocommerce' ); ?>
					<span><?php printf( '<a href="%s" target="_blank">%s</a>', esc_url( 'https://go.wppool.dev/1fhP' ), esc_html( 'Influencer Program ', 'order-sync-with-google-sheets-for-woocommerce' ) ); ?></span>
					<?php esc_html_e( 'to make money from your social media content. You can also check our', 'order-sync-with-google-sheets-for-woocommerce' ); ?>
					<span><?php printf( '<a href="%s" target="_blank">%s</a>', esc_url( 'https://go.wppool.dev/gfgE' ), esc_html( 'Affiliate Program ', 'order-sync-with-google-sheets-for-woocommerce' ) ); ?></span> 
					<?php esc_html_e( 'to get a ', 'order-sync-with-google-sheets-for-woocommerce' ); ?>
					<span style="font-weight:600; font-size:inherit; color: #1f2937"><?php esc_html_e( '25% commission ', 'order-sync-with-google-sheets-for-woocommerce' ); ?></span>
					<?php esc_html_e( 'on every sale!', 'order-sync-with-google-sheets-for-woocommerce' ); ?>
				
				</h3>
					<div class="link-wrapper">
						<a href="<?php echo esc_url( 'https://go.wppool.dev/gfgE' ); ?>" target="_blank" class="affiliate-button"><?php esc_html_e( 'Affiliate Program', 'order-sync-with-google-sheets-for-woocommerce' ); ?></a>
						<a href="<?php echo esc_url( 'https://go.wppool.dev/1fhP' ); ?>" target="_blank" class="influencer-button" style=""><?php esc_html_e( 'Influencer Program', 'order-sync-with-google-sheets-for-woocommerce' ); ?> <span></span></a>
					</div>
				</div>
			</div>
			<?php
		}
	}
	new Popup();
}
