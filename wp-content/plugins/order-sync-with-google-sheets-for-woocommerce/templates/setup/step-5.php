<?php
/**
 * Step 5 template for setup.
 *
 * @package OrderSyncWithGoogleSheetForWooCommerce
 * @since 1.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit(); ?>
 <div class="ssgs-tab__pane" :class="{'active' : isStep(5), 'bounceInRight' : state.doingNext, 'bounceInLeft' : state.doingPrev}">
	<div class="sync-google-sheet" :class="{'active' : isFirstScreen}">
		<div class="content text-center">
			<figure class="media">
				<img src="<?php echo esc_url( OSGSW_PUBLIC ) . '/images/recycling.svg'; ?>" alt="">
			</figure>

			<h3 class="title"><?php esc_html_e( 'Sync orders on Google Sheet', 'order-sync-with-google-sheets-for-woocommerce' ); ?></h3>

			<div class="description">
				<p><?php esc_html_e( 'You’re almost ready. Press this button to sync your WooCommerce orders with Google Sheet.', 'order-sync-with-google-sheets-for-woocommerce' ); ?></p>
			</div>

			<a :class="{'disabled' : state.syncingGoogleSheet}" href="javascript:;" @click.prevent="syncOnGoogleSheet" class="ssgs-btn flex-button" x-html="state.syncingGoogleSheet ? '<div class=\'loader small\'></div><?php esc_html_e( 'Syncing', 'order-sync-with-google-sheets-for-woocommerce' ); ?>..' : '<?php esc_html_e( 'Sync orders on Google Sheet', 'order-sync-with-google-sheets-for-woocommerce' ); ?>'"></a>
		</div>
	</div><!-- /First Step - Sync Google Sheet -->

	<!-- after completing syncing  -->
	<div class="congratulations" x-show="!isFirstScreen" x-transition.50ms>
		<div class="content text-center">
			<figure class="media">
				<img src="<?php echo esc_url( OSGSW_PUBLIC ) . '/images/congratulations.svg'; ?>" alt="">
			</figure>

			<h3 class="title"><?php esc_html_e( 'Congratulations', 'order-sync-with-google-sheets-for-woocommerce' ); ?></h3>
			<div class="description" x-show="isNoOrder" x-transition.50ms>
				<p><?php esc_html_e( 'Your orders have been successfully synced to Google Sheet', 'order-sync-with-google-sheets-for-woocommerce' ); ?></p>
			</div>
			<div class="description" x-show="isNoOrder" x-transition.50ms style="background-color: antiquewhite;width: 249px;border-radius: 2px;">
				<p><span class="dashicons dashicons-warning" style="font-size: 15px;color: #FFA500;margin-top: 4px;"></span><?php esc_html_e( 'Currently you have no order to sync', 'order-sync-with-google-sheets-for-woocommerce' ); ?></p>
			</div>
			<div class="description" x-show="!isNoOrder" x-transition.50ms>
				<p><?php esc_html_e( 'Your orders have been successfully synced to Google Sheet🎉', 'order-sync-with-google-sheets-for-woocommerce' ); ?></p>
			</div>
			<div class="ssgs-btn-group">
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=osgsw-admin' ) ); ?>" class="ssgs-btn border"><?php esc_html_e( 'Go to Dashboard', 'order-sync-with-google-sheets-for-woocommerce' ); ?></a>
				<a target="_blank" href="javascript:;" @click.prevent="viewGoogleSheet" class="ssgs-btn"><?php esc_html_e( 'View on Google Sheet', 'order-sync-with-google-sheets-for-woocommerce' ); ?></a>
			</div>
			<div class="profeatures"> 
				<p x-show="!isPro"><img src="<?php echo esc_url( OSGSW_PUBLIC ) . '/images/gift.svg'; ?>" alt=""><a href="javascript:;" class="osgsw-promo"><?php esc_html_e( 'Sync more order information (payment, shipping, and more) with a premium plan.', 'order-sync-with-google-sheets-for-woocommerce' ); ?></a></p>
			</div>
		</div>
	</div><!-- /Second Step - Congratulations -->
</div><!-- /Done -->
