<?php
/**
 * WooCommerce template for setup.
 *
 * @package OrderSyncWithGoogleSheetForWooCommerce
 * @since 1.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit(); ?>
<!-- when woocommerce is not installed or activated  -->
<div class="ssgs-popup bg-none" style="display: none" x-show="!is_woocommerce_activated">
	<div class="popup-overlay">
	</div>
	<div class="content text-center" style="position: fixed;">

		<figure class="media">
			<img src="<?php echo esc_url( OSGSW_PUBLIC ) . 'images/woo.svg'; ?>" alt="">
		</figure>

		<h3 x-show="!state.activatingWooCommerce" class="title" x-text="(is_woocommerce_installed ? '<?php esc_html_e( 'Activate', 'order-sync-with-google-sheets-for-woocommerce' ); ?>' : '<?php esc_html_e( 'Install and activate', 'order-sync-with-google-sheets-for-woocommerce' ); ?>') + ' WooCommerce'"><?php esc_html_e( 'Install and activate WooCommerce', 'order-sync-with-google-sheets-for-woocommerce' ); ?></h3>

		<div class="text" x-show="!state.activatingWooCommerce">
			<p><?php esc_html_e( 'The plugin only works when WooCommerce is', 'order-sync-with-google-sheets-for-woocommerce' ); ?> <span x-show="!is_woocommerce_installed"><strong><?php esc_html_e( 'installed and', 'order-sync-with-google-sheets-for-woocommerce' ); ?></strong></span>
			<strong><?php esc_html_e( 'activated', 'order-sync-with-google-sheets-for-woocommerce' ); ?></strong></p>
		</div>
		<div x-show="state.activatingWooCommerce" class="osgsw_flex_loader">
			<div>
				<div class="loader"></div>
			</div><?php esc_html_e( 'Activating WooCommerce... This may take a couple of seconds.', 'order-sync-with-google-sheets-for-woocommerce' ); ?><span x-show="is_woocommerce_activated">✔</span>
		</div>
		<a x-show="!state.activatingWooCommerce" href="#" @click.prevent="activateWooCommerce()" class="ssgs-btn flex-button" x-html="(is_woocommerce_installed ? '<?php esc_html_e( 'Activate', 'order-sync-with-google-sheets-for-woocommerce' ); ?>' : '<?php esc_html_e( 'Install & activate', 'order-sync-with-google-sheets-for-woocommerce' ); ?>')"></a>
	</div>
</div>
