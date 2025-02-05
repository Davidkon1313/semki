/**
 * Admin Enqueue Script
 *
 * @package     wpsyncsheets-for-woocommerce
 */

(function ($) {
	"use strict";
	$( document ).ready(
		function () {
			$( "#wpssw_metabox_sync_btn" ).on(
				"click",
				function (e) {
					e.preventDefault();
					if (1 === parseInt( $( "#wpssw_metabox_sync_btn" ).attr( "data-hpos" ) )) {
						var orderId = getParameterByName( "id" );
					} else {
						var orderId = getParameterByName( "post" );
					}
					$( "#wpssw_syncbtn_meta_box .syncbtnloader" ).show();
					$( "#wpssw_metabox_sync_btn" ).attr( "disabled", true );
					var sync_nonce_token;
					sync_nonce_token = admin_ajax_object.sync_nonce_token;
					$.ajax(
						{
							url: admin_ajax_object.ajaxurl,
							type: "post",
							data:
							"action=wpssw_sync_single_order_data&order_id=" +
							orderId +
							"&sync_nonce_token=" +
							sync_nonce_token, // sync_single_entry.
							success: function (response) {
								$( "#wpssw_syncbtn_meta_box .syncbtnloader" ).hide();
								$( "#wpssw_metabox_sync_btn" ).attr( "disabled", false );
								if (response === "successful") {
									alert( "Sync Successfully" );
								} else if (String( response ) === "statussheetnotexist") {
									alert(
										"This order's status sheet is deleted from your spreadsheet so first save your order settings and try again."
									);
									return false;
								} else if (String( response ) === "allordersheetnotexist") {
									alert(
										"All Orders sheet is deleted from your spreadsheet so first save your order settings and try again."
									);
									return false;
								} else if (String( response ) === "nonceerror") {
									alert( "Sorry, your nonce did not verify." );
								} else if (String( response ) === "reachedapilimit") {
									alert(
										"Your Google Sheets API limit has been reached. Please take a look at our FAQ."
									);
								} else if (String( response ) === "savesettings") {
									alert( "Please save your order settings first and try again later." );
									return false;
								} else {
									alert( "Error" );
								}
							},
						}
					).fail(
						function () {
							alert( "Error" );
							$( "#wpssw_syncbtn_meta_box .syncbtnloader" ).hide();
							$( "#wpssw_metabox_sync_btn" ).attr( "disabled", false );
						}
					);
				}
			);

			$( "#wpssw_product_metabox_sync_btn" ).on(
				"click",
				function (e) {
					e.preventDefault();
					var productId = getParameterByName( "post" );
					$( "#wpssw_product_syncbtn_meta_box .syncbtnloader" ).show();
					$( "#wpssw_product_metabox_sync_btn" ).attr( "disabled", true );
					var sync_nonce_token;
					sync_nonce_token = admin_ajax_object.sync_nonce_token;
					$.ajax(
						{
							url: admin_ajax_object.ajaxurl,
							type: "post",
							data:
							"action=wpssw_sync_single_product_data&product_id=" +
							productId +
							"&product_sync_nonce_token=" +
							sync_nonce_token, // sync_single_entry.
							success: function (response) {
								$( "#wpssw_product_syncbtn_meta_box .syncbtnloader" ).hide();
								$( "#wpssw_product_metabox_sync_btn" ).attr( "disabled", false );
								if (response === "successful") {
									alert( "Sync Successfully" );
								} else if (String( response ) === "savesettings") {
									alert(
										"Please save your product settings first and try again later."
									);
									return false;
								} else if (String( response ) === "reachedapilimit") {
									alert(
										"Your Google Sheets API limit has been reached. Please take a look at our FAQ."
									);
								} else if (String( response ) === "nonceerror") {
									alert( "Sorry, your nonce did not verify." );
								} else {
									alert( "Error" );
								}
							},
						}
					).fail(
						function () {
							alert( "Error" );
							$( "#wpssw_syncbtn_meta_box .syncbtnloader" ).hide();
							$( "#wpssw_product_metabox_sync_btn" ).attr( "disabled", false );
						}
					);
				}
			);
			$( ".wpssw_syncbtn_column .wpssw_single_order_sync_btn" ).on(
				"click",
				function (e) {
					e.preventDefault();
					var orderId = $( this ).closest( "tr" ).attr( "id" );
					syncData( orderId );
				}
			);
			$( ".wpssw_syncbtn_column .wpssw_single_product_sync_btn" ).on(
				"click",
				function (e) {
					e.preventDefault();
					var productId = $( this ).closest( "tr" ).attr( "id" );
					syncProductData( productId );
				}
			);
			function syncData(ID) {
				var trId = ID;
				var ID   = ID.slice( ID.indexOf( "-" ) + 1 );

				var sync_nonce_token;
				sync_nonce_token = admin_ajax_object.sync_nonce_token;
				$( "#" + trId + " .syncbtnloader" ).show();
				$( "#" + trId + " .wpssw_single_order_sync_btn" ).hide();
				$.ajax(
					{
						url: admin_ajax_object.ajaxurl,
						type: "post",
						data:
						"action=wpssw_sync_single_order_data&order_id=" +
						ID +
						"&sync_nonce_token=" +
						sync_nonce_token, // sync_single_entry.
						success: function (response) {
							$( "#" + trId + " .syncbtnloader" ).hide();
							$( "#" + trId + " .wpssw_single_order_sync_btn" ).css(
								"display",
								"inline-block"
							);
							if (response === "successful") {
								alert( "Sync Successfully" );
							} else if (String( response ) === "statussheetnotexist") {
								alert(
									"This order's status sheet is deleted from your spreadsheet so first save your order settings and try again."
								);
								return false;
							} else if (String( response ) === "allordersheetnotexist") {
								alert(
									"All Orders sheet is deleted from your spreadsheet so first save your order settings and try again."
								);
								return false;
							} else if (String( response ) === "nonceerror") {
								alert( "Sorry, your nonce did not verify." );
							} else if (String( response ) === "reachedapilimit") {
								alert(
									"Your Google Sheets API limit has been reached. Please take a look at our FAQ."
								);
							} else if (String( response ) === "savesettings") {
								alert( "Please save your order settings first and try again later." );
								return false;
							} else {
								alert( "Error" );
							}
						},
					}
				).fail(
					function () {
						alert( "Error" );
						$( "#" + trId + " .syncbtnloader" ).hide();
						$( "#" + trId + " .wpssw_single_order_sync_btn" ).css(
							"display",
							"inline-block"
						);
					}
				);
			}

			function syncProductData(ID) {
				var trId = ID;
				var ID   = ID.slice( ID.indexOf( "-" ) + 1 );

				var sync_nonce_token;
				sync_nonce_token = admin_ajax_object.sync_nonce_token;
				$( "#" + trId + " .syncbtnloader" ).show();
				$( "#" + trId + " .wpssw_single_product_sync_btn" ).hide();
				$.ajax(
					{
						url: admin_ajax_object.ajaxurl,
						type: "post",
						data:
						"action=wpssw_sync_single_product_data&product_id=" +
						ID +
						"&product_sync_nonce_token=" +
						sync_nonce_token, // sync_single_entry.
						success: function (response) {
							$( "#" + trId + " .syncbtnloader" ).hide();
							$( "#" + trId + " .wpssw_single_product_sync_btn" ).css(
								"display",
								"inline-block"
							);
							if (response === "successful") {
								alert( "Sync Successfully" );
							} else if (String( response ) === "savesettings") {
								alert(
									"Please save your product settings first and try again later."
								);
								return false;
							} else if (String( response ) === "reachedapilimit") {
								alert(
									"Your Google Sheets API limit has been reached. Please take a look at our FAQ."
								);
							} else if (String( response ) === "nonceerror") {
								alert( "Sorry, your nonce did not verify." );
							} else {
								alert( "Error" );
							}
						},
					}
				).fail(
					function () {
						alert( "Error" );
						$( "#" + trId + " .syncbtnloader" ).hide();
						$( "#" + trId + " .wpssw_single_product_sync_btn" ).css(
							"display",
							"inline-block"
						);
					}
				);
			}
		}
	);
})( jQuery );
function getParameterByName(name, url) {
	"use strict";
	if ( ! url) {
		url = window.location.href;
	}
	name      = name.replace( /[\[\]]/g, "\\jQuery&" );
	var regex = new RegExp( "[?&]" + name + "(=([^&#]*)|&|#|jQuery)" ),
	results   = regex.exec( url );
	if ( ! results) {
		return null;
	}
	if ( ! results[2]) {
		return "";
	}
	return decodeURIComponent( results[2].replace( /\+/g, " " ) );
}
