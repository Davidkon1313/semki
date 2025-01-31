<?php

/**
 * Settings template.
 *
 * @package OrderSyncWithGoogleSheetForWooCommerce
 * @since 1.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit(); ?>
<div class="ssgs-dashboard__tab bounceInRight" :class="{'active': isTab('settings')}">
	<div class="ssgs-admin">
		<div class="ssgs-dashboard__block">
			<h4 class="title">
				<?php
				esc_html_e(
					'Preference',
					'order-sync-with-google-sheets-for-woocommerce'
				);
				?>
			</h4>
			<div class="form-group" >
				<label>
					<div class="ssgs-check">
						<input type="checkbox" name="bulk_edit_option2" class="check check2"  x-model="option.bulk_edit_option2" :checked="option.bulk_edit_option2 == 1" @change="save_change++;show_disable_popup2 = true" >
						<span class="switch"></span>
					</div>

					<span class="label-text">
						<?php
						esc_html_e(
							'Bulk edit on Google Sheet',
							'order-sync-with-google-sheets-for-woocommerce'
						);
						?>
					</span>
					<span class="ssgs-badge green" >
					<?php
					esc_html_e(
						'New',
						'order-sync-with-google-sheets-for-woocommerce'
					);
					?>
					</span>

				</label>

				<div class="description">
					<p>
						<?php
						esc_html_e(
							'Enable this feature to bulk edit WooCommerce order data from Google Sheets',
							'order-sync-with-google-sheets-for-woocommerce'
						);
						?>
					</p>
				</div>
			</div>
			<div class="form-group">
				<label :class="forUltimate">
					<div class="ssgs-check">
						<input :readonly="!isPro" type="checkbox" name="custom_order_status_bolean" class="check" x-model="option.custom_order_status_bolean" :checked="option.custom_order_status_bolean == '1'" @change="save_change++">
						<span class="switch"></span>
					</div>
					<span class="label-text">
					<?php
					esc_html_e(
						'Sync Custom order status',
						'order-sync-with-google-sheets-for-woocommerce'
					);
					?>
					</span>
					<span x-show="!isPro" class="ssgs-badge purple">
					<?php
					esc_html_e(
						'Ultimate',
						'order-sync-with-google-sheets-for-woocommerce'
					);
					?>
					</span>
					<span class="ssgs-badge green" >
					<?php
					esc_html_e(
						'New',
						'order-sync-with-google-sheets-for-woocommerce'
					);
					?>
					</span>
				</label>
				<div class="description">
					<p>
					<?php
					esc_html_e(
						'Enable this to sync custom order status of the order on Google Sheet',
						'order-sync-with-google-sheets-for-woocommerce'
					);
					?>
					</p>
				</div>
			</div>
			<div class="form-group">
				<label :class="forUltimate">
					<div class="ssgs-check">
						<input :readonly="!isPro" type="checkbox" name="multiple_itmes" class="check" x-model="option.multiple_itmes" :checked="option.multiple_itmes == '1'" @change="save_change++">
						<span class="switch"></span>
					</div>
					<span class="label-text">
					<?php
					esc_html_e(
						'Use separate rows to show multiple products of an order',
						'order-sync-with-google-sheets-for-woocommerce'
					);
					?>
					</span>
					<span x-show="!isPro" class="ssgs-badge purple">
					<?php
					esc_html_e(
						'Ultimate',
						'order-sync-with-google-sheets-for-woocommerce'
					);
					?>
					</span>
					<span class="ssgs-badge green" >
					<?php
					esc_html_e(
						'New',
						'order-sync-with-google-sheets-for-woocommerce'
					);
					?>
					</span>
				</label>
				<div class="description">
					<p>
					<?php
					esc_html_e(
						'If an order has multiple products, enabling this feature will display the products in separate rows',
						'order-sync-with-google-sheets-for-woocommerce'
					);
					?>
					</p>
				</div>
			</div>

		</div>
		
	
		<div class="ssgs-dashboard__block">
			<h4 class="title">
				<?php
				esc_html_e(
					'Google Sheet columns',
					'order-sync-with-google-sheets-for-woocommerce'
				);
				?>
			</h4>
			<div class="form-group">
				<label>
					<div class="ssgs-check">
						<input  type="checkbox" name="sync_total_items" class="check" x-model="option.sync_total_items" :checked="option.sync_total_items == 1" @change="save_change++">
						<span class="switch"></span>
					</div>
					<span class="label-text">
					<?php
					esc_html_e(
						'Display Total Items',
						'order-sync-with-google-sheets-for-woocommerce'
					);
					?>
					</span>
				</label>

				<div class="description">
					<p>
					<?php
					esc_html_e(
						'Enable to this to show the number of total ordered items in a column on Google Sheet',
						'order-sync-with-google-sheets-for-woocommerce'
					);
					?>
					</p>
				</div>
			</div>
			<div class="form-group">
				<label :class="forUltimate">
					<div class="ssgs-check">
						<input :readonly="!isPro" type="checkbox" name="product_sku_sync" class="check" x-model="option.product_sku_sync" :checked="option.product_sku_sync == '1'" @change="save_change++">
						<span class="switch"></span>
					</div>
					<span class="label-text">
					<?php
					esc_html_e(
						'Sync product SKU',
						'order-sync-with-google-sheets-for-woocommerce'
					);
					?>
					</span>
					<span x-show="!isPro" class="ssgs-badge purple">
					<?php
					esc_html_e(
						'Ultimate',
						'order-sync-with-google-sheets-for-woocommerce'
					);
					?>
					</span>
					<span class="ssgs-badge green" >
					<?php
					esc_html_e(
						'New',
						'order-sync-with-google-sheets-for-woocommerce'
					);
					?>
					</span>
				</label>
				<div class="description">
					<p>
					<?php
					esc_html_e(
						'Enable to this to show the product sku in a column on Google Sheet',
						'order-sync-with-google-sheets-for-woocommerce'
					);
					?>
					</p>
				</div>
			</div>
			<div class="form-group">
				<label>
					<div class="ssgs-check">
						<input type="checkbox" name="sync_total_price" class="check" x-model="option.sync_total_price" :checked="option.sync_total_price == 1" @change="save_change++">
						<span class="switch"></span>
					</div>
					<span class="label-text">
					<?php
					esc_html_e(
						'Display Total Price',
						'order-sync-with-google-sheets-for-woocommerce'
					);
					?>
					</span>
				</label>

				<div class="description">
					<p>
					<?php
					esc_html_e(
						'Enable this to show the total price of the order in a column on Google Sheet',
						'order-sync-with-google-sheets-for-woocommerce'
					);
					?>
					</p>
				</div>
			</div>
			<div class="form-group">
				<label :class="forUltimate">
					<div class="ssgs-check">
						<input :readonly="!isPro" type="checkbox" name="total_discount" class="check" x-model="option.total_discount" :checked="option.total_discount == '1'" @change="save_change++">
						<span class="switch"></span>
					</div>
					<span class="label-text">
					<?php
					esc_html_e(
						'Display Total Discount',
						'order-sync-with-google-sheets-for-woocommerce'
					);
					?>
					</span>
					<span x-show="!isPro" class="ssgs-badge purple">
					<?php
					esc_html_e(
						'Ultimate',
						'order-sync-with-google-sheets-for-woocommerce'
					);
					?>
					</span>
				</label>
				<div class="description">
					<p>
					<?php
					esc_html_e(
						'Enable this to show the total discount of the order in a column on Google Sheet',
						'order-sync-with-google-sheets-for-woocommerce'
					);
					?>
					</p>
				</div>
			</div>
			<div class="form-group">
				<label :class="forUltimate">
					<div class="ssgs-check">
						<input :readonly="!isPro" type="checkbox" name="show_product_qt" class="check" x-model="option.show_product_qt" :checked="option.show_product_qt == 1" @change="save_change++">
						<span class="switch"></span>
					</div>
					<span class="label-text">
					<?php
					esc_html_e(
						'Show individual product quantity',
						'order-sync-with-google-sheets-for-woocommerce'
					);
					?>
					</span>

					<span x-show="!isPro" class="ssgs-badge purple">
					<?php
					esc_html_e(
						'Ultimate',
						'order-sync-with-google-sheets-for-woocommerce'
					);
					?>
					</span>
					
				</label>

				<div class="description">
					<p>
					<?php
					esc_html_e(
						'Enable this to show the quantity of each ordered items beside their names in the Product Names column on Google Sheet',
						'order-sync-with-google-sheets-for-woocommerce'
					);
					?>
					</p>
				</div>
			</div>
			<div class="form-group">
				<label :class="forUltimate">
					<div class="ssgs-check">
						<input :readonly="!isPro" type="checkbox" name="show_billing_details" class="check" x-model="option.show_billing_details" :checked="option.show_billing_details == 1" @change="save_change++">
						<span class="switch"></span>
					</div>
					<span class="label-text">
					<?php
					esc_html_e(
						'Display Billing Address',
						'order-sync-with-google-sheets-for-woocommerce'
					);
					?>
					</span>
					
					<span x-show="!isPro" class="ssgs-badge purple">
					<?php
					esc_html_e(
						'Ultimate',
						'order-sync-with-google-sheets-for-woocommerce'
					);
					?>
					</span>
					
				</label>

				<div class="description">
					<p>
					<?php
					esc_html_e(
						'Enable this to show the billing address in a column on Google Sheet',
						'order-sync-with-google-sheets-for-woocommerce'
					);
					?>
					</p>
				</div>
			</div>
			<div class="form-group" :class="forUltimate">
				<label>
					<div class="ssgs-check" :class="forUltimate">
						<input :readonly="!isPro" type="checkbox" name="add_shipping_details_sheet" class="check" x-model="option.add_shipping_details_sheet" :checked="option.add_shipping_details_sheet == 1" @change="save_change++">
						<span class="switch"></span>
					</div>

					<span class="label-text">
						<?php
						esc_html_e(
							'Display Shipping Address',
							'order-sync-with-google-sheets-for-woocommerce'
						);
						?>
					</span>

					<span x-show="!isPro" class="ssgs-badge purple osgsw-promo">
						<?php esc_html_e( 'Ultimate', 'order-sync-with-google-sheets-for-woocommerce' ); ?>
					</span>

				</label>

				<div class="description">
					<p>
						<?php
						esc_html_e(
							'Enable this to show the shipping address in a column on Google Sheet',
							'order-sync-with-google-sheets-for-woocommerce'
						);
						?>
					</p>
				</div>
			</div>
			<div class="form-group">
				<label :class="forUltimate">
					<div class="ssgs-check">
						<input :readonly="!isPro" type="checkbox" name="show_order_date" class="check" x-model="option.show_order_date" :checked="option.show_order_date == 1" @change="save_change++">
						<span class="switch"></span>
					</div>
					<span class="label-text">
					<?php
					esc_html_e(
						'Display Order Date',
						'order-sync-with-google-sheets-for-woocommerce'
					);
					?>
					</span>
					<span x-show="!isPro" class="ssgs-badge purple">
					<?php
					esc_html_e(
						'Ultimate',
						'order-sync-with-google-sheets-for-woocommerce'
					);
					?>
					</span>
				</label>

				<div class="description">
					<p>
					<?php
					esc_html_e(
						'Enable this to show the order date in a column on Google Sheet',
						'order-sync-with-google-sheets-for-woocommerce'
					);
					?>
					</p>
				</div>
			</div>
			<div class="form-group">
				<label :class="forUltimate">
					<div class="ssgs-check">
						<input :readonly="!isPro" type="checkbox" name="show_payment_method" class="check" x-model="option.show_payment_method" :checked="option.show_payment_method == 1" @change="save_change++">
						<span class="switch"></span>
					</div>
					<span class="label-text">
					<?php
					esc_html_e(
						'Display Payment Method',
						'order-sync-with-google-sheets-for-woocommerce'
					);
					?>
					</span>
					<span x-show="!isPro" class="ssgs-badge purple">
					<?php
					esc_html_e(
						'Ultimate',
						'order-sync-with-google-sheets-for-woocommerce'
					);
					?>
					</span>
				</label>

				<div class="description">
					<p>
					<?php
					esc_html_e(
						'Enable this to show the payment method in a column on Google Sheet',
						'order-sync-with-google-sheets-for-woocommerce'
					);
					?>
					</p>
				</div>
			</div>
			<div class="form-group">
				<label :class="forUltimate">
					<div class="ssgs-check">
						<input :readonly="!isPro" type="checkbox" name="show_customer_note" class="check" x-model="option.show_customer_note" :checked="option.show_customer_note == 1" @change="save_change++">
						<span class="switch"></span>
					</div>
					<span class="label-text">
					<?php
					esc_html_e(
						'Display Customer Note',
						'order-sync-with-google-sheets-for-woocommerce'
					);
					?>
					</span>
					<span x-show="!isPro" class="ssgs-badge purple">
					<?php
					esc_html_e(
						'Ultimate',
						'order-sync-with-google-sheets-for-woocommerce'
					);
					?>
					</span>
				</label>

				<div class="description">
					<p>
					<?php
					esc_html_e(
						'Enable this to show the customer note in a column on Google Sheet',
						'order-sync-with-google-sheets-for-woocommerce'
					);
					?>
					</p>
				</div>
			</div>
			<div class="form-group">
				<label :class="forUltimate">
					<div class="ssgs-check">
						<input :readonly="!isPro" type="checkbox" name="show_order_note" class="check" x-model="option.show_order_note" :checked="option.show_order_note == 1" @change="save_change++">
						<span class="switch"></span>
					</div>
					<span class="label-text">
					<?php
					esc_html_e(
						'Display Order Note',
						'order-sync-with-google-sheets-for-woocommerce'
					);
					?>
					</span>
					<span x-show="!isPro" class="ssgs-badge purple">
					<?php
					esc_html_e(
						'Ultimate',
						'order-sync-with-google-sheets-for-woocommerce'
					);
					?>
					</span>
					<span class="ssgs-badge green" >
						<?php
						esc_html_e(
							'New',
							'order-sync-with-google-sheets-for-woocommerce'
						);
						?>
					</span>

				</label>


				<div class="description">
					<p>
					<?php
					esc_html_e(
						'Enable this to show the order note in a column on Google Sheet',
						'order-sync-with-google-sheets-for-woocommerce'
					);
					?>
					</p>
				</div>
			</div>
			<div class="form-group">
				<label :class="forUltimate">
					<div class="ssgs-check">
						<input :readonly="!isPro" type="checkbox" name="who_place_order" class="check" x-model="option.who_place_order" :checked="option.who_place_order == 1" @change="save_change++">
						<span class="switch"></span>
					</div>
					<span class="label-text">
					<?php
					esc_html_e(
						'Display order placement information',
						'order-sync-with-google-sheets-for-woocommerce'
					);
					?>
					</span>

					<span x-show="!isPro" class="ssgs-badge purple">
					<?php
					esc_html_e(
						'Ultimate',
						'order-sync-with-google-sheets-for-woocommerce'
					);
					?>
					</span>
					
				</label>

				<div class="description">
					<p>
					<?php
					esc_html_e(
						'Enable this to show the information of the user who placed the order in a column on Google Sheet',
						'order-sync-with-google-sheets-for-woocommerce'
					);
					?>
					</p>
				</div>
			</div>
			<div class="form-group">
				<label :class="forUltimate">
					<div class="ssgs-check">
						<input :readonly="!isPro" type="checkbox" name="show_order_url" class="check" x-model="option.show_order_url" :checked="option.show_order_url == 1" @change="save_change++">
						<span class="switch"></span>
					</div>
					<span class="label-text">
					<?php
					esc_html_e(
						'Display Order URL',
						'order-sync-with-google-sheets-for-woocommerce'
					);
					?>
					</span>
					
					<span x-show="!isPro" class="ssgs-badge purple">
					<?php
					esc_html_e(
						'Ultimate',
						'order-sync-with-google-sheets-for-woocommerce'
					);
					?>
					</span>
				</label>

				<div class="description">
					<p>
					<?php
					esc_html_e(
						'Enable this to show the order URL in a column on Google Sheet',
						'order-sync-with-google-sheets-for-woocommerce'
					);
					?>
					</p>
				</div>
			</div>

			<div class="form-group">
				<label :class="forUltimate">
					<div class="ssgs-check">
						<input :readonly="!isPro" type="checkbox" name="show_custom_meta_fields" class="check" x-model="option.show_custom_meta_fields" :checked="option.show_custom_meta_fields == 1" @change="save_change++">
						<span class="switch"></span>
					</div>
					<span class="label-text ssgs_custom_fileds">
					<?php
					esc_html_e(
						'Sync Order Custom Fields',
						'order-sync-with-google-sheets-for-woocommerce'
					);
					?>
					</span>
					<span x-show="!isPro" class="ssgs-badge purple"> 
					<?php
					esc_html_e(
						'Ultimate',
						'order-sync-with-google-sheets-for-woocommerce'
					);
					?>
					</span>
					
				</label> 
				<div class="description">
					<p>
					<?php
					esc_html_e(
						"Enter or search your product's custom field (meta data) and enable them to display on the Spreadsheet as columns.",
						'order-sync-with-google-sheets-for-woocommerce'
					);
					?>
					</p>
				</div>
				<div class="osgsw_description" x-show="option.show_custom_meta_fields == 1" x-init="select2Alpine">
				<?php
					$check_box_values = osgsw_get_product_custom_fields();
					$checked_value  = osgsw_get_option( 'show_custom_fields' );
				?>
					<select x-ref="select" class="osgsw_custom_field form-control" multiple="multiple" name="osgsw_custom_fields[]">
						<?php
						foreach ( $check_box_values as $key => $value ) {
							$check_type = check_osgsw_file_type( $key );
							$key_word = osgsw_reserved_keyword( $key );
							if ( 'not_suported' === $check_type ) {
								printf( '<option value="%s" disabled>%s</option>', esc_html( $key ), esc_html( $value ) );
							} else if ( 'yes' === $key_word ) {
								printf( '<option value="%s" disabled>%s</option>', esc_html( $key ), esc_html( '(Custom field with reserved words are not supported yet)' ) );
							} elseif ( ! empty( $checked_value ) ) {

								if ( in_array( $key, $checked_value ) ) {
									printf( '<option value="%s" selected>%s</option>', esc_html( $key ), esc_html( $value ) );
								} else {
									printf( '<option value="%s">%s</option>', esc_html( $key ), esc_html( $value ) );
								}
							} else {
								printf( '<option value="%s">%s</option>', esc_html( $key ), esc_html( $value ) );
							}
						}
						?>
					</select>
					<div class="osgsw_show_selected_options fixed-bottom" x-bind:class="{ 'osgsw_show_selected_option2': option.show_custom_fields && option.show_custom_fields.length > 0 }">
						<p style="color:green"><?php echo esc_html__('The custom fields columns will appear in the following sequence on the sheet:','order-sync-with-google-sheets-for-woocommerce'); ?></p>
						  <ul>
						  <template x-if="option.show_custom_fields && option.show_custom_fields.length > 0">
							<template x-for="name in option.show_custom_fields" :key="name">
								<li>
									<input type="checkbox" disabled checked="checked" x-text="name" value="name"><span for="label_demo" x-text="name"></span>
								</li>
							</template>
						  </template>
						</ul>
					</div>
				</div>
			</div>
		</div>
		<div class="ssgsw_button_container" x-show="save_change" style="transition: opacity 300ms ease-in-out 0ms;">
			<button x-on:click="save_checkbox_settings('');isLoading = true" :disabled="isLoading" class="ssgsw_save_button">
				<span x-show="!isLoading"><?php esc_html_e( 'Save Changes','order-sync-with-google-sheets-for-woocommerce' ); ?></span>
				<span x-show="isLoading"><?php esc_html_e( 'Saving...','order-sync-with-google-sheets-for-woocommerce' ); ?></span>
			</button>
			<button type="button" class="ssgsw_save_close"  x-on:click="show_discrad = true"><?php esc_html_e( 'Discard Changes','order-sync-with-google-sheets-for-woocommerce' ); ?></button>
		</div>
	</div>
</div>
<?php
/**
 * Show Popup
 */
?>
<div id="popup1" class="ssgs_popup-container" x-show="show_discrad" style="display: none">
	<div class="ssgs_popup-content" @click.away="show_discrad = false">
		<a href="#" class="close ssgs_close_button" x-on:click="show_discrad = false">&times;</a>
		<div class="profile-section">
			  <div class="profile-image ssgsw_logo_section_popup"><span class="dashicons dashicons-warning ssgs_warning"></span></div>
			<div class="profile-details">
				<h3 class="profile-title"><?php esc_html_e( 'Discard All Changes','order-sync-with-google-sheets-for-woocommerce' ); ?></h3>
				<p class="profile-description"><?php esc_html_e( 'You are about to discrad all unsaved changes. All of your settings will be reset to the point where you last saved. Are you sure you want to do this?','order-sync-with-google-sheets-for-woocommerce' ); ?></p>
			</div>
		</div>
		<div class="ssgs_first_section">
			<div class="ssgs_button_section">
				<button type="button" class="ssgsw_save_close1" x-on:click="show_discrad = false"><?php esc_html_e( 'No, continue editing','order-sync-with-google-sheets-for-woocommerce' ); ?></button>
				<button type="button" class="ssgsw_save_changes" x-on:click="reload_the_page();"><?php esc_html_e( 'Yes, discard changes','order-sync-with-google-sheets-for-woocommerce' ); ?></button>
			</div>
		</div>
	</div>
</div>

<div id="popup1" class="ssgs_popup-container" x-show="option.bulk_edit_option2 != 1 && show_disable_popup2 == true" style="display: none">
	<div class="ssgs_popup-content" @click.away="show_disable_popup2 = false; option.bulk_edit_option2 = true">
		<a href="#" class="close ssgs_close_button" x-on:click="show_disable_popup2 = false;option.bulk_edit_option2 = true">&times;</a>
		<div class="profile-section">
			<div class="profile-details">
				<h3 class="profile-title"><?php esc_html_e('⚠️Wait','order-sync-with-google-sheets-for-woocommerce'); ?></h3>
				<p class="ssgsw_extra_class" style="font-size: 14px; marign-left:10px;"><?php esc_html_e('We recommend keeping this feature enabled at all times. It will help you to swiftly update your data and seamlessly sync it with WooCommerce. Disabling this feature may expose you to unintended changes while editing multiple orders on Google Sheets. Do you still want to disable it?','order-sync-with-google-sheets-for-woocommerce'); ?></p>
			</div>
		</div>
		<div class="ssgs_first_section">
			<div class="ssgs_button_section">
				
				<button type="button" class="ssgsw_save_changes ssgsw_save_changes23" x-on:click="option.bulk_edit_option2 = false;show_disable_popup2 = false;save_change++"><?php esc_html_e(' Disable at my risk','order-sync-with-google-sheets-for-woocommerce'); ?></button>
				<button type="button" class="ssgsw_save_close1" style="background-color:#005ae0; color:#fff" x-on:click="show_disable_popup2 = false; option.bulk_edit_option2 = true"><?php esc_html_e('Keep Enabled','order-sync-with-google-sheets-for-woocommerce'); ?></button>
			</div>
		</div>
	</div>
</div>
