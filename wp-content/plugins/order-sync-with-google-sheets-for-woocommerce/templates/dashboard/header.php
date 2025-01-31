<?php
/**
 * Base template for dashboard.
 *
 * @package OrderSyncWithGoogleSheetForWooCommerce
 * @since 1.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit();
?>

<!-- Tab header  -->
<div class="ssgs-admin">
	<div class="ssgs-dashboard__header">
		<ul class="ssgs-dashboard__nav">
			<li class="ssgs-dashboard__nav-link" :class="{'active' : 'dashboard' === state.currentTab }" @click.prevent="setTab('dashboard')"><a href="#"><i class="ssgs-dashboard"></i><?php esc_html_e( 'Dashboard', 'order-sync-with-google-sheets-for-woocommerce' ); ?></a></li>
			<li class="ssgs-dashboard__nav-link" 
				:class="{'active' : 'settings' === state.currentTab }" 
				@click.prevent="setTab('settings')">
				<a href="#">
					<i class="ssgs-settings"></i>
					 <?php esc_html_e( 'Settings', 'order-sync-with-google-sheets-for-woocommerce' ); ?> 
				</a>
			</li>
		</ul>
		<ul class="ssgs-dashboard-help">
			<li><a href="https://www.youtube.com/watch?v=NB4YW6S6bm0" target="_blank"><img class="ossgs_video_icons" src="<?php echo esc_url( OSGSW_PUBLIC ) . '/images/video_icon.svg'; ?>" alt=""><?php esc_html_e( 'Video tutorial', 'order-sync-with-google-sheets-for-woocommerce' ); ?></a></li>
			<li class="gradient force-flex" :class="{'no-padding' : isPro}">
				<a  href="javascript:;" class="osgsw_changelogs_trigger" 
					@click.prevent="toggleChangelogs">
					🤩 <?php esc_html_e( 'What\'s new?', 'order-sync-with-google-sheets-for-woocommerce' ); ?>
				</a>
				<div id="osgsw_changelogs"></div>
			</li>
		</ul>
	</div>
</div>


<div id="popup1" class="ssgs_popup-container" x-show="show_notice_popup" style="display: none">
	<div class="ssgs_popup-content" @click.away="show_notice_popup = false">
		<div class="profile-section">
			<div class="profile-details">
				<h3 class="ossgw_profile-title2"><?php esc_html_e('Are you sure to close?','order-sync-with-google-sheets-for-woocommerce'); ?></h3>
				<p class="ossgw_extra_class2"><?php esc_html_e('Please make sure that you have updated the Apps Script. Otherwise the plugin functionality may not work properly.','order-sync-with-google-sheets-for-woocommerce'); ?></p>
			</div>
		</div>
		<div class="ssgs_first_section">
			<div class="osgs_button_section">
				
				<button type="button" class="osgsw_save_changes231 button" x-on:click="show_notice_popup = false"><?php esc_html_e(' Later','order-sync-with-google-sheets-for-woocommerce'); ?></button>
				<button type="button" class="osgsw_save_close1 button" style="background-color:#005ae0; color:#fff"><?php esc_html_e('Confirm & close','order-sync-with-google-sheets-for-woocommerce'); ?></button>
			</div>
		</div>
	</div>
</div>
