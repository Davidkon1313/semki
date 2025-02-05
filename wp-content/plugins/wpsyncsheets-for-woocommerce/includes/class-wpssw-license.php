<?php
/**
 * Main WPSyncSheets_For_WooCommerce namespace.
 *
 * @since 1.0.0
 * @package wpsyncsheets-for-woocommerce
 */

// Direct access security.
if ( ! defined( 'WPSSW_PLUGIN_SECURITY' ) ) {
	die();
}
/**
 * Class WPSSW_License.
 */
final class WPSSW_License {
	/**
	 * Instance of this WPSSW_License class
	 *
	 * @var $instance
	 */
	protected static $instance = null;
	/**
	 * Create self Instance.
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'wpssw_plugin_updater' ) );
	}
	/**
	 * Initialization
	 */
	public function init() {
	}
	/**
	 * Initialize the updater. Hooked into `init` to work with the
	 * wp_version_check cron job, which allows auto-updates.
	 */
	public function wpssw_plugin_updater() {

		// To support auto-updates, this needs to run during the wp_version_check cron job for privileged users.
		$doing_cron = defined( 'DOING_CRON' ) && DOING_CRON;
		if ( ! current_user_can( 'manage_options' ) && ! $doing_cron ) {
			return;
		}

		// retrieve our license key from the DB.
		$license_key = trim( WPSSW_Setting::wpssw_option( 'wpssw_license_key' ) );
		// setup the updater.
		$edd_updater = new WPSSW_Plugin_Update(
			WPSSW_STORE_URL,
			WPSSW_BASE_FILE,
			array(
				'version' => WPSSW_VERSION,
				'license' => $license_key,
				'item_id' => WPSSW_PLUGIN_ITEM_ID,
				'author'  => 'Creative Werk Designs',
				'beta'    => false,
			)
		);
	}
}



