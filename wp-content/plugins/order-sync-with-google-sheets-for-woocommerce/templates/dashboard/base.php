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

<section class="osgsw-wrapper" x-data="dashboard">
	<?php
	$active_new_user  = get_option('osgsw_new_user_activate_trigger_1_11_4', '0' );
	$already_update   = get_option('osgsw_already_update_trigger_1_11_4', '0' );
	$new_trigger_notice = false;
	if ( '1' != $active_new_user && '1' != $already_update ) { //phpcs:ignore
		?>
	<style>
		.osgsw-wrapper .ssgs-dashboard__header {
			display: flex;
			align-items: center;
			justify-content: space-between;
			line-height: 40px;
			padding-top: 20px;
		}
		.ssgsw_notice_container2 {
			display: inline-block;
			position: relative;
			top: -23px;
			right: -46px;
		}
		p.ossgw_setup_now_button {
			position: absolute;
			top: -56px;
			right: -61px;
		}
	</style>
		<div class="ssgsw_appscript_trigger ssgsw_appscript_notice233">
			<div class="ssgsw_notice_container">
				<p> <?php esc_html_e('Hey! 👋 We’ve updated our Apps Script Please','order-sync-with-google-sheets-for-woocommerce'); ?> <strong class="ssgsw_extra_strong"> <?php esc_html_e('update your Apps Script','order-sync-with-google-sheets-for-woocommerce'); ?> </strong> <?php esc_html_e('on Google sheets ','order-sync-with-google-sheets-for-woocommerce'); ?> <?php esc_html_e('to enjoy all the new changes.','order-sync-with-google-sheets-for-woocommerce'); ?></p>
				<div class="ossgsw_inner_class">
					<p class="ossgw_setup_now_button"><span class="osgsw_remove_text_dec osgsw_remove_text_dec23"><?php esc_html_e('Setup Now →','order-sync-with-google-sheets-for-woocommerce'); ?></span></p>
				</div>	
			</div>
			<div class="ssgsw_notice_container2">
				<span class="dashicons dashicons-dismiss osgsw_notice_off" x-on:click="show_notice_popup = true"></span>
			</div>	
		</div>
		<?php
	}
	?>
	<?php osgsw()->load_template( 'dashboard/header' ); ?>
	<?php osgsw()->load_template( 'dashboard/overview' ); ?>
	<?php osgsw()->load_template( 'dashboard/settings' ); ?>
</section> 
