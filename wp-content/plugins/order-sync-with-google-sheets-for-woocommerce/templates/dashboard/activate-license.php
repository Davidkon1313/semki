<?php
/**
 * Activate license template.
 *
 * @package OrderSyncWithGoogleSheetForWooCommerce
 * @since 1.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit();
?>
<div class="osgsw-license-notice">
	<?php esc_html_e( 'Please activate your license.', 'order-sync-with-google-sheets-for-woocommerce' ); ?> <a href="<?php echo esc_url( admin_url( 'admin.php?page=osgsw-license' ) ); ?>"><?php esc_html_e( 'Activate', 'order-sync-with-google-sheets-for-woocommerce' ); ?></a>
</div>
