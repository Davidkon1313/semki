<?php
/**
 * Step 2 template for setup.
 *
 * @package OrderSyncWithGoogleSheetForWooCommerce
 * @since 1.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit(); ?>
<div class="ssgs-tab__pane" :class="{'active' : isStep(2), 'bounceInRight' : state.doingNext, 'bounceInLeft' : state.doingPrev}">
	<div class="form-group">
		<label for="google_sheet_url" class="title title-secondary"><?php esc_html_e( 'Add Google Sheet URL', 'order-sync-with-google-sheets-for-woocommerce' ); ?> <span class="ssgs-tooltip bottom"><i class="ssgs-help"></i><span><img src="<?php echo esc_url( OSGSW_PUBLIC ) . '/images/tooltip/url.jpg'; ?>" alt="" /></span></span></label>
		<p class="description"><?php esc_html_e( 'Copy the URL of your Google Sheet and paste it here. So that, our system can add all your WooCommerce Orders into it.', 'order-sync-with-google-sheets-for-woocommerce' ); ?></p>

		<input type="url" id="google_sheet_url" class="ssgs-input" placeholder="<?php esc_html_e( 'Enter your google sheet URL', 'order-sync-with-google-sheets-for-woocommerce' ); ?>"  x-model="option.spreadsheet_url">
	</div>

	<div class="form-group">
		<label for="google_sheet_name" class="title title-secondary"><?php esc_html_e( 'Enter sheet tab name', 'order-sync-with-google-sheets-for-woocommerce' ); ?> <span class="ssgs-tooltip"><i class="ssgs-help"></i><span><img src="<?php echo esc_url( OSGSW_PUBLIC ) . '/images/tooltip/step2_enter-sheet-tab-name.png'; ?>" alt="" /></span></span></label>
		<p class="description"><?php esc_html_e( 'Copy the sheet tab name (ex: Sheet1) from your Google Sheet and paste it here. So that, our system can add your WooCommerce orders to the spreadsheet.', 'order-sync-with-google-sheets-for-woocommerce' ); ?></p>

		<input type="text" value="Sheet1" id="google_sheet_name" class="ssgs-input" placeholder="<?php esc_html_e( 'Enter your google sheet Name', 'order-sync-with-google-sheets-for-woocommerce' ); ?>"  x-model="option.sheet_tab">
	</div>
</div><!-- /Set URL -->
