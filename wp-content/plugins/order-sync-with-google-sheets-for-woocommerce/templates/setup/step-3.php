<?php
/**
 * Step 3 template for setup.
 *
 * @package OrderSyncWithGoogleSheetForWooCommerce
 * @since 1.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit(); ?>
<div class="ssgs-tab__pane" :class="{'active' : isStep(3), 'bounceInRight' : state.doingNext, 'bounceInLeft' : state.doingPrev}">
	<div class="access-email-id">
		<div class="entry-title text-center">
			<h3 class="title">
				<?php
					printf(
						'%1$s <span class="ssgs-badge gray">%2$s</span> %3$s <span class="ssgs-tooltip bottom left"><i class="ssgs-help"></i><span><img src="%4$s" alt="" /></span></span>',
						esc_html__( 'Give', 'order-sync-with-google-sheets-for-woocommerce' ),
						esc_html__( 'Editor', 'order-sync-with-google-sheets-for-woocommerce' ),
						esc_html__( 'access to the following ID', 'order-sync-with-google-sheets-for-woocommerce' ),
						esc_url( OSGSW_PUBLIC ) . '/images/tooltip/edits.jpg'
					);
					?>
			</h3>
		</div>

		<div class="ssgs-clipboard">
			<input type="text" readonly @click.prevent="copyServiceAccountEmail" :value="credentials ? credentials.client_email : false" class="ssgs-input" id="clipboard-input-id">
			<span class="ssgs-tooltip text">
				<button class="ssgs-btn" @click.prevent="copyServiceAccountEmail" @mouseover="state.copied_client_email = false"><?php esc_html_e( 'Copy', 'order-sync-with-google-sheets-for-woocommerce' ); ?></button>
				<span class="text" id="tooltiptext-id" x-text="state.copied_client_email ? '<?php esc_html_e( 'Copied to Clipboard', 'order-sync-with-google-sheets-for-woocommerce' ); ?>' : '<?php esc_html_e( 'Copy to clipboard', 'order-sync-with-google-sheets-for-woocommerce' ); ?>'"></span>
			</span>
		</div>

		<div class="ssgs-row align-items-center">
			<div class="ssgs-column">
				<div class="content">
					<ol>
						<li><?php esc_html_e( 'Copy the email ID from the box', 'order-sync-with-google-sheets-for-woocommerce' ); ?> <span class="ssgs-tooltip"><i class="ssgs-help"></i><span><img src="<?php echo esc_url( OSGSW_PUBLIC ) . '/images/tooltip/copy.jpg'; ?>" alt="" /></span></span></li>
						<li><?php esc_html_e( 'Go to your Google Sheet & click', 'order-sync-with-google-sheets-for-woocommerce' ); ?> <span class="ssgs-badge green"><i class="ssgs-link-share"></i> <?php esc_html_e( 'Share', 'order-sync-with-google-sheets-for-woocommerce' ); ?></span> <?php esc_html_e( 'button at the top-right position', 'order-sync-with-google-sheets-for-woocommerce' ); ?> <span class="ssgs-tooltip right"><i class="ssgs-help"></i><span><img src="<?php echo esc_url( OSGSW_PUBLIC ) . '/images/tooltip/step3_click-on-share-button.png'; ?>" alt="" /></span></span></li>
						<li><?php esc_html_e( 'Paste the Email ID that you copied and give', 'order-sync-with-google-sheets-for-woocommerce' ); ?> <span class="ssgs-badge gray"><?php esc_html_e( 'Editor', 'order-sync-with-google-sheets-for-woocommerce' ); ?></span> <?php esc_html_e( 'access', 'order-sync-with-google-sheets-for-woocommerce' ); ?> <span class="ssgs-tooltip"><i class="ssgs-help"></i><span><img src="<?php echo esc_url( OSGSW_PUBLIC ) . '/images/tooltip/edits.jpg'; ?>" alt="" /></span></span></li>
						<li><?php esc_html_e( 'Then, click the', 'order-sync-with-google-sheets-for-woocommerce' ); ?> <span class="ssgs-badge"><?php esc_html_e( 'Send', 'order-sync-with-google-sheets-for-woocommerce' ); ?></span> <?php esc_html_e( 'or', 'order-sync-with-google-sheets-for-woocommerce' ); ?> <span class="ssgs-badge" style="margin-left:0"><?php esc_html_e( 'Share', 'order-sync-with-google-sheets-for-woocommerce' ); ?></span> <?php esc_html_e( 'button to confirm', 'order-sync-with-google-sheets-for-woocommerce' ); ?> <span class="ssgs-tooltip"><i class="ssgs-help"></i><span><img src="<?php echo esc_url( OSGSW_PUBLIC ) . '/images/tooltip/edit2.jpg'; ?>" alt="" /></span></span></li>
					</ol>
				</div>
			</div>

			<div class="ssgs-column">
				<div class="ssgs-video-wrapper">
					<h4 class="title"><?php esc_html_e( 'How to set editor access?', 'order-sync-with-google-sheets-for-woocommerce' ); ?> <span class="ssgs-badge gray"> <?php esc_html_e( '0:22', 'order-sync-with-google-sheets-for-woocommerce' ); ?></span></h4>
					<!-- data-play="https://youtu.be/bUL4tJFc7kY" -->
					<div class="sgss-video play-icon" data-play="https://youtu.be/0u5UCzxHPfA">
					<img src="<?php echo esc_url( OSGSW_PUBLIC ) . '/images/thumbnails/2.editor.png'; ?>" alt="">
					</div>
				</div>
			</div>
		</div>

		<div class="form-group">
			<label for="access_email_id"><input type="checkbox" name="access_email_id" id="access_email_id" x-model="state.given_editor_access"><?php esc_html_e( "I've given", 'order-sync-with-google-sheets-for-woocommerce' ); ?> <span class="ssgs-badge gray"><?php esc_html_e( 'Editor', 'order-sync-with-google-sheets-for-woocommerce' ); ?></span> <?php esc_html_e( 'access to this email ID', 'order-sync-with-google-sheets-for-woocommerce' ); ?></label>
		</div>
	</div>
</div><!-- /Set ID -->
