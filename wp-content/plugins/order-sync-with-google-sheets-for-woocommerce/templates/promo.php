<?php
/**
 * Show  Popup
 *
 * @version 1.0
 * @package OrderSyncWithGoogleSheetForWooCommerce
 */

?>
<div class="osgsw-wrapper">
	<div id="osgsw-promo" class="ssgs-popup" style="display: none">
		<div class="popup-overlay close-promo"></div>
		<div class="content text-center">
			<button class="ssgs-close close-promo" @click="open = false"></button>

			<div class="special-offer"><?php esc_html_e( 'Special Offer', 'order-sync-with-google-sheets-for-woocommerce' ); ?></div>

			<h2 class="title"><?php esc_html_e( 'Unlock all features', 'order-sync-with-google-sheets-for-woocommerce' ); ?></h2>

			<figure class="popup">
				<div class="text">
					<span class="discount-text">
					<?php echo esc_html( $offer['discount'] ?? 85 ); ?>
					</span>
					<div class="additional-text">
						<span>%</span>
						<span><?php esc_html_e( 'OFF', 'order-sync-with-google-sheets-for-woocommerce' ); ?></span>
					</div>
				</div>
			</figure>

			<div id="osgsw_counter" data-validity="<?php echo esc_html( $offer['counter_time'] ); ?>"></div>

			<a href="#" target="_blank" class="ssgs-btn gradient"><?php esc_html_e( 'Get Ultimate', 'order-sync-with-google-sheets-for-woocommerce' ); ?></a>
		</div>
	</div>
</div>
