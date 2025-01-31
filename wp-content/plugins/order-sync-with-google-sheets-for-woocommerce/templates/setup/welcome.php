<?php
/**
 * Welcome template for setup.
 *
 * @package OrderSyncWithGoogleSheetForWooCommerce
 * @since 1.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit(); ?>
<!-- welcome screen  -->
<div class="start-setup text-center" x-show="!state.setupStarted" x-transition.delay>
	<figure class="media">
		<img src="<?php echo esc_url( OSGSW_PUBLIC ) . '/images/welcome.png'; ?>" alt="">
	</figure>
	<div class="content">
		<h3 class="title"><?php esc_html_e( 'Welcome to setup page', 'order-sync-with-google-sheets-for-woocommerce' ); ?></h3>
		<p class="description"><?php esc_html_e( 'FlexOrder makes it easy to configure your Google Sheet. Press the button and follow the steps to sync your orders with Google Sheet', 'order-sync-with-google-sheets-for-woocommerce' ); ?></p>
		<button @click.prevent="state.setupStarted = true" class="ssgs-btn blue"><?php esc_html_e( 'Start setup', 'order-sync-with-google-sheets-for-woocommerce' ); ?></button>

		<div class="setup-video-link">
			<a target="_blank" href="https://www.youtube.com/watch?v=NB4YW6S6bm0"><?php esc_html_e( 'Watch video tutorial', 'order-sync-with-google-sheets-for-woocommerce' ); ?></a>
		</div>
	</div>
</div><!-- First Setup Widget -->
