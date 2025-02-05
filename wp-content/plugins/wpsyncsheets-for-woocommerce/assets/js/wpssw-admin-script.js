/**
 * Admin Enqueue Script
 *
 * @package     wpsyncsheets-for-woocommerce
 */

(function ($) {
	"use strict";
	$( document ).ready(
		function () {
			// Enable/Disable Header field input.
			$( 'input[name="spreadsheetselection"]' ).on(
				"change",
				function () {
					var newRequest = $( this ).val();
					if (String( newRequest ) === "new") {
						$( "#header_fields" ).prop( "disabled", false );
						$( "#productwise" ).removeClass( "disabled" );
						$( "#orderwise" ).removeClass( "disabled" );
						$( ".manage-row" ).prop( "disabled", false );
						$( "#newsheet" ).show();
						$( ".synctr" ).hide();
						$( ".ord_import_row" ).hide();
						$( "#view_spreadsheet" ).hide();
						$( "#clear_spreadsheet" ).hide();
						$( "#down_spreadsheet" ).hide();
						$( "#woocommerce_spreadsheet_container" ).hide();
						$( "#sortable" ).sortable(
							{
								disabled: false,
							}
						);
						$( ".headers_chk" ).attr( "disabled", false );
						$( "#wpssw-headers-notice" ).hide();
						$( "#product-sortable" ).sortable(
							{
								disabled: false,
							}
						);
						$( ".manage_order" ).prop( "disabled", false );
						$( "#asc_order" ).removeClass( "disabled" );
						$( "#desc_order" ).removeClass( "disabled" );
					} else {
						$( "#header_fields" ).prop( "disabled", "disabled" );
						$( "#productwise" ).addClass( "disabled" );
						$( "#orderwise" ).addClass( "disabled" );
						$( ".manage-row" ).prop( "disabled", true );
						$( "#newsheet" ).hide();
						$( "#view_spreadsheet" ).show();
						$( "#clear_spreadsheet" ).show();
						$( "#down_spreadsheet" ).show();
						$( "#woocommerce_spreadsheet_container" ).show();
						$( "#sortable" ).sortable(
							{
								disabled: true,
							}
						);
						$( "#product-sortable" ).sortable(
							{
								disabled: true,
							}
						);
						$( "#wpssw-headers-notice" ).show();
						$( ".manage_order" ).prop( "disabled", true );
						$( "#asc_order" ).addClass( "disabled" );
						$( "#desc_order" ).addClass( "disabled" );

						if (String( newRequest ) === "" || parseInt( newRequest ) === 0) {
							$( "#synctr" ).hide();
							$( ".ord_import_row" ).hide();
						} else {
							$( ".synctr" ).show();
							if (
							$( "#import_order_checkbox" ).is( ":checked" ) &&
							($( "#insert_order_checkbox" ).is( ":checked" ) ||
							$( "#update_order_checkbox" ).is( ":checked" ) ||
							$( "#delete_order_checkbox" ).is( ":checked" ))
							) {
								$( ".ord_import_row" ).show();
							}
						}
					}
				}
			);
			$( "input[type=radio][name=order_ascdesc]" ).on(
				"change",
				function () {
					var val = $( 'input[name="order_ascdesc"]:checked' ).val();
					if (String( val ) === "descorder") {
						$( "#asc_order" ).removeAttr( "checked" );
						$( "#desc_order" ).attr( "checked", true );
					} else {
						$( "#asc_order" ).attr( "checked", true );
						$( "#desc_order" ).removeAttr( "checked" );
					}
				}
			);
			$( "input[type=radio][name=header_format]" ).on(
				"change",
				function () {
					$( "#sortable li" ).remove();
					var selectedOpt = this.value.toString();
					if (String( selectedOpt ) === "productwise") {
						$( "#header_fields option" ).remove();
						var prdWise     = $( "#prdwise" ).val();
						var productWise = prdWise.split( "," );
						for (var p in productWise) {
							var labelId = productWise[p].replace( / /g, "_" ).toLowerCase();
							$( "#sortable" ).append(
								'<li class="default-order-sheet ui-state-default ui-sortable-handle"><label for="' +
								labelId +
								'"><span class="orderSheet-left"><span class="wootextfield">' +
								productWise[p] +
								'</span><span class="ui-icon ui-icon-pencil wpssw-tooltio-link"><span class="pencil-icon"><svg width="12" height="12" viewBox="0 0 13 13" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M1.33333 11.6668H2.26667L8.01667 5.91677L7.08333 4.98343L1.33333 10.7334V11.6668ZM10.8667 4.9501L8.03333 2.1501L8.96667 1.21677C9.22222 0.961213 9.53611 0.833435 9.90833 0.833435C10.2806 0.833435 10.5944 0.961213 10.85 1.21677L11.7833 2.1501C12.0389 2.40566 12.1722 2.71399 12.1833 3.0751C12.1944 3.43621 12.0722 3.74455 11.8167 4.0001L10.8667 4.9501ZM9.9 5.93343L2.83333 13.0001H0V10.1668L7.06667 3.1001L9.9 5.93343Z" fill="#64748B"/></svg></span><span class="check-icon"><svg xmlns="http://www.w3.org/2000/svg"  viewBox="0 0 26 26" width="12" height="12"><path d="M 22.566406 4.730469 L 20.773438 3.511719 C 20.277344 3.175781 19.597656 3.304688 19.265625 3.796875 L 10.476563 16.757813 L 6.4375 12.71875 C 6.015625 12.296875 5.328125 12.296875 4.90625 12.71875 L 3.371094 14.253906 C 2.949219 14.675781 2.949219 15.363281 3.371094 15.789063 L 9.582031 22 C 9.929688 22.347656 10.476563 22.613281 10.96875 22.613281 C 11.460938 22.613281 11.957031 22.304688 12.277344 21.839844 L 22.855469 6.234375 C 23.191406 5.742188 23.0625 5.066406 22.566406 4.730469 Z" fill="#64748B"/></svg></span><span class="tooltip-text edit-tooltip">Edit</span><span class="tooltip-text update-tooltip">Update</span></span></span><span class="orderSheet-right"><input type="checkbox" name="header_fields_custom[]" value="' +
								productWise[p] +
								'" class="headers_chk1" hidden="true" checked><input type="checkbox" name="header_fields[]" id="' +
								labelId +
								'" class="headers_chk" value="' +
								productWise[p] +
								'" checked><span class="checkbox-switch-new"></span><span class="ui-icon ui-icon-caret-2-n-s wpssw-tooltio-link"><svg width="17" height="16" viewBox="0 0 17 16" fill="none" xmlns="http://www.w3.org/2000/svg"><mask id="mask0_384_3228" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="0" y="0" width="17" height="16"><rect x="0.5" width="16" height="16" fill="#D9D9D9"/></mask><g mask="url(#mask0_384_3228)"><path d="M5.95875 13.67C5.55759 13.67 5.21418 13.5272 4.92851 13.2415C4.64284 12.9558 4.5 12.6124 4.5 12.2113C4.5 11.8101 4.64284 11.4667 4.92851 11.181C5.21418 10.8953 5.55759 10.7525 5.95875 10.7525C6.35991 10.7525 6.70332 10.8953 6.98899 11.181C7.27466 11.4667 7.4175 11.8101 7.4175 12.2113C7.4175 12.6124 7.27466 12.9558 6.98899 13.2415C6.70332 13.5272 6.35991 13.67 5.95875 13.67ZM10.335 13.67C9.93384 13.67 9.59043 13.5272 9.30476 13.2415C9.01909 12.9558 8.87625 12.6124 8.87625 12.2113C8.87625 11.8101 9.01909 11.4667 9.30476 11.181C9.59043 10.8953 9.93384 10.7525 10.335 10.7525C10.7362 10.7525 11.0796 10.8953 11.3652 11.181C11.6509 11.4667 11.7937 11.8101 11.7937 12.2113C11.7937 12.6124 11.6509 12.9558 11.3652 13.2415C11.0796 13.5272 10.7362 13.67 10.335 13.67ZM5.95875 9.29375C5.55759 9.29375 5.21418 9.15091 4.92851 8.86524C4.64284 8.57957 4.5 8.23616 4.5 7.835C4.5 7.43384 4.64284 7.09043 4.92851 6.80476C5.21418 6.51909 5.55759 6.37625 5.95875 6.37625C6.35991 6.37625 6.70332 6.51909 6.98899 6.80476C7.27466 7.09043 7.4175 7.43384 7.4175 7.835C7.4175 8.23616 7.27466 8.57957 6.98899 8.86524C6.70332 9.15091 6.35991 9.29375 5.95875 9.29375ZM10.335 9.29375C9.93384 9.29375 9.59043 9.15091 9.30476 8.86524C9.01909 8.57957 8.87625 8.23616 8.87625 7.835C8.87625 7.43384 9.01909 7.09043 9.30476 6.80476C9.59043 6.51909 9.93384 6.37625 10.335 6.37625C10.7362 6.37625 11.0796 6.51909 11.3652 6.80476C11.6509 7.09043 11.7937 7.43384 11.7937 7.835C11.7937 8.23616 11.6509 8.57957 11.3652 8.86524C11.0796 9.15091 10.7362 9.29375 10.335 9.29375ZM5.95875 4.9175C5.55759 4.9175 5.21418 4.77466 4.92851 4.48899C4.64284 4.20332 4.5 3.85991 4.5 3.45875C4.5 3.05759 4.64284 2.71418 4.92851 2.42851C5.21418 2.14284 5.55759 2 5.95875 2C6.35991 2 6.70332 2.14284 6.98899 2.42851C7.27466 2.71418 7.4175 3.05759 7.4175 3.45875C7.4175 3.85991 7.27466 4.20332 6.98899 4.48899C6.70332 4.77466 6.35991 4.9175 5.95875 4.9175ZM10.335 4.9175C9.93384 4.9175 9.59043 4.77466 9.30476 4.48899C9.01909 4.20332 8.87625 3.85991 8.87625 3.45875C8.87625 3.05759 9.01909 2.71418 9.30476 2.42851C9.59043 2.14284 9.93384 2 10.335 2C10.7362 2 11.0796 2.14284 11.3652 2.42851C11.6509 2.71418 11.7937 3.05759 11.7937 3.45875C11.7937 3.85991 11.6509 4.20332 11.3652 4.48899C11.0796 4.77466 10.7362 4.9175 10.335 4.9175Z" fill="#64748B"/></g></svg><span class="tooltip-text">Drag & Drop</span></span></span></label></li>'
							);
						}
						$( ".repeat_checkbox" ).show();
					} else if (String( selectedOpt ) === "orderwise") {
						$( "#header_fields option" ).remove();
						var ordWise   = $( "#ordwise" ).val();
						var orderWise = ordWise.split( "," );
						for (var p in orderWise) {
							var labelId = orderWise[p].replace( / /g, "_" ).toLowerCase();
							$( "#sortable" ).append(
								'<li class="default-order-sheet ui-state-default ui-sortable-handle"><label for="' +
								labelId +
								'"><span class="orderSheet-left"><span class="wootextfield">' +
								orderWise[p] +
								'</span><span class="ui-icon ui-icon-pencil wpssw-tooltio-link"><span class="pencil-icon"><svg width="12" height="12" viewBox="0 0 13 13" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M1.33333 11.6668H2.26667L8.01667 5.91677L7.08333 4.98343L1.33333 10.7334V11.6668ZM10.8667 4.9501L8.03333 2.1501L8.96667 1.21677C9.22222 0.961213 9.53611 0.833435 9.90833 0.833435C10.2806 0.833435 10.5944 0.961213 10.85 1.21677L11.7833 2.1501C12.0389 2.40566 12.1722 2.71399 12.1833 3.0751C12.1944 3.43621 12.0722 3.74455 11.8167 4.0001L10.8667 4.9501ZM9.9 5.93343L2.83333 13.0001H0V10.1668L7.06667 3.1001L9.9 5.93343Z" fill="#64748B"/></svg></span><span class="check-icon"><svg xmlns="http://www.w3.org/2000/svg"  viewBox="0 0 26 26" width="12" height="12"><path d="M 22.566406 4.730469 L 20.773438 3.511719 C 20.277344 3.175781 19.597656 3.304688 19.265625 3.796875 L 10.476563 16.757813 L 6.4375 12.71875 C 6.015625 12.296875 5.328125 12.296875 4.90625 12.71875 L 3.371094 14.253906 C 2.949219 14.675781 2.949219 15.363281 3.371094 15.789063 L 9.582031 22 C 9.929688 22.347656 10.476563 22.613281 10.96875 22.613281 C 11.460938 22.613281 11.957031 22.304688 12.277344 21.839844 L 22.855469 6.234375 C 23.191406 5.742188 23.0625 5.066406 22.566406 4.730469 Z" fill="#64748B"/></svg></span><span class="tooltip-text edit-tooltip">Edit</span><span class="tooltip-text update-tooltip">Update</span></span></span><span class="orderSheet-right"><input type="checkbox" name="header_fields_custom[]" value="' +
								orderWise[p] +
								'" class="headers_chk1" hidden="true" checked><input type="checkbox" name="header_fields[]" id="' +
								labelId +
								'" class="headers_chk" value="' +
								orderWise[p] +
								'" checked><span class="checkbox-switch-new"></span><span class="ui-icon ui-icon-caret-2-n-s wpssw-tooltio-link"><svg width="17" height="16" viewBox="0 0 17 16" fill="none" xmlns="http://www.w3.org/2000/svg"><mask id="mask0_384_3228" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="0" y="0" width="17" height="16"><rect x="0.5" width="16" height="16" fill="#D9D9D9"/></mask><g mask="url(#mask0_384_3228)"><path d="M5.95875 13.67C5.55759 13.67 5.21418 13.5272 4.92851 13.2415C4.64284 12.9558 4.5 12.6124 4.5 12.2113C4.5 11.8101 4.64284 11.4667 4.92851 11.181C5.21418 10.8953 5.55759 10.7525 5.95875 10.7525C6.35991 10.7525 6.70332 10.8953 6.98899 11.181C7.27466 11.4667 7.4175 11.8101 7.4175 12.2113C7.4175 12.6124 7.27466 12.9558 6.98899 13.2415C6.70332 13.5272 6.35991 13.67 5.95875 13.67ZM10.335 13.67C9.93384 13.67 9.59043 13.5272 9.30476 13.2415C9.01909 12.9558 8.87625 12.6124 8.87625 12.2113C8.87625 11.8101 9.01909 11.4667 9.30476 11.181C9.59043 10.8953 9.93384 10.7525 10.335 10.7525C10.7362 10.7525 11.0796 10.8953 11.3652 11.181C11.6509 11.4667 11.7937 11.8101 11.7937 12.2113C11.7937 12.6124 11.6509 12.9558 11.3652 13.2415C11.0796 13.5272 10.7362 13.67 10.335 13.67ZM5.95875 9.29375C5.55759 9.29375 5.21418 9.15091 4.92851 8.86524C4.64284 8.57957 4.5 8.23616 4.5 7.835C4.5 7.43384 4.64284 7.09043 4.92851 6.80476C5.21418 6.51909 5.55759 6.37625 5.95875 6.37625C6.35991 6.37625 6.70332 6.51909 6.98899 6.80476C7.27466 7.09043 7.4175 7.43384 7.4175 7.835C7.4175 8.23616 7.27466 8.57957 6.98899 8.86524C6.70332 9.15091 6.35991 9.29375 5.95875 9.29375ZM10.335 9.29375C9.93384 9.29375 9.59043 9.15091 9.30476 8.86524C9.01909 8.57957 8.87625 8.23616 8.87625 7.835C8.87625 7.43384 9.01909 7.09043 9.30476 6.80476C9.59043 6.51909 9.93384 6.37625 10.335 6.37625C10.7362 6.37625 11.0796 6.51909 11.3652 6.80476C11.6509 7.09043 11.7937 7.43384 11.7937 7.835C11.7937 8.23616 11.6509 8.57957 11.3652 8.86524C11.0796 9.15091 10.7362 9.29375 10.335 9.29375ZM5.95875 4.9175C5.55759 4.9175 5.21418 4.77466 4.92851 4.48899C4.64284 4.20332 4.5 3.85991 4.5 3.45875C4.5 3.05759 4.64284 2.71418 4.92851 2.42851C5.21418 2.14284 5.55759 2 5.95875 2C6.35991 2 6.70332 2.14284 6.98899 2.42851C7.27466 2.71418 7.4175 3.05759 7.4175 3.45875C7.4175 3.85991 7.27466 4.20332 6.98899 4.48899C6.70332 4.77466 6.35991 4.9175 5.95875 4.9175ZM10.335 4.9175C9.93384 4.9175 9.59043 4.77466 9.30476 4.48899C9.01909 4.20332 8.87625 3.85991 8.87625 3.45875C8.87625 3.05759 9.01909 2.71418 9.30476 2.42851C9.59043 2.14284 9.93384 2 10.335 2C10.7362 2 11.0796 2.14284 11.3652 2.42851C11.6509 2.71418 11.7937 3.05759 11.7937 3.45875C11.7937 3.85991 11.6509 4.20332 11.3652 4.48899C11.0796 4.77466 10.7362 4.9175 10.335 4.9175Z" fill="#64748B"/></g></svg><span class="tooltip-text">Drag & Drop</span></span></span></label></li>'
							);
						}
						$( ".repeat_checkbox" ).hide();
					}

					if ($( "#insert_order_checkbox" ).is( ":checked" )) {
						$( "#sortable" ).append(
							'<li class="default-order-sheet ui-state-default ui-sortable-handle insertorder">  <label> <span class="orderSheet-left">  <span class="wootextfield">Insert</span> </span> <span class="orderSheet-right"> <input type="checkbox" name="header_fields_custom[]" value="Insert" class="headers_chk1" hidden="true" checked><input type="checkbox" name="header_fields[]" id="" class="headers_chk" value="Insert" checked>  <span class="ui-icon ui-icon-caret-2-n-s wpssw-tooltio-link">   <svg width="17" height="16" viewBox="0 0 17 16" fill="none" xmlns="http://www.w3.org/2000/svg"> <mask id="mask0_384_3228" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="0" y="0" width="17" height="16"> <rect x="0.5" width="16" height="16" fill="#D9D9D9"/> </mask> <g mask="url(#mask0_384_3228)"> <path d="M5.95875 13.67C5.55759 13.67 5.21418 13.5272 4.92851 13.2415C4.64284 12.9558 4.5 12.6124 4.5 12.2113C4.5 11.8101 4.64284 11.4667 4.92851 11.181C5.21418 10.8953 5.55759 10.7525 5.95875 10.7525C6.35991 10.7525 6.70332 10.8953 6.98899 11.181C7.27466 11.4667 7.4175 11.8101 7.4175 12.2113C7.4175 12.6124 7.27466 12.9558 6.98899 13.2415C6.70332 13.5272 6.35991 13.67 5.95875 13.67ZM10.335 13.67C9.93384 13.67 9.59043 13.5272 9.30476 13.2415C9.01909 12.9558 8.87625 12.6124 8.87625 12.2113C8.87625 11.8101 9.01909 11.4667 9.30476 11.181C9.59043 10.8953 9.93384 10.7525 10.335 10.7525C10.7362 10.7525 11.0796 10.8953 11.3652 11.181C11.6509 11.4667 11.7937 11.8101 11.7937 12.2113C11.7937 12.6124 11.6509 12.9558 11.3652 13.2415C11.0796 13.5272 10.7362 13.67 10.335 13.67ZM5.95875 9.29375C5.55759 9.29375 5.21418 9.15091 4.92851 8.86524C4.64284 8.57957 4.5 8.23616 4.5 7.835C4.5 7.43384 4.64284 7.09043 4.92851 6.80476C5.21418 6.51909 5.55759 6.37625 5.95875 6.37625C6.35991 6.37625 6.70332 6.51909 6.98899 6.80476C7.27466 7.09043 7.4175 7.43384 7.4175 7.835C7.4175 8.23616 7.27466 8.57957 6.98899 8.86524C6.70332 9.15091 6.35991 9.29375 5.95875 9.29375ZM10.335 9.29375C9.93384 9.29375 9.59043 9.15091 9.30476 8.86524C9.01909 8.57957 8.87625 8.23616 8.87625 7.835C8.87625 7.43384 9.01909 7.09043 9.30476 6.80476C9.59043 6.51909 9.93384 6.37625 10.335 6.37625C10.7362 6.37625 11.0796 6.51909 11.3652 6.80476C11.6509 7.09043 11.7937 7.43384 11.7937 7.835C11.7937 8.23616 11.6509 8.57957 11.3652 8.86524C11.0796 9.15091 10.7362 9.29375 10.335 9.29375ZM5.95875 4.9175C5.55759 4.9175 5.21418 4.77466 4.92851 4.48899C4.64284 4.20332 4.5 3.85991 4.5 3.45875C4.5 3.05759 4.64284 2.71418 4.92851 2.42851C5.21418 2.14284 5.55759 2 5.95875 2C6.35991 2 6.70332 2.14284 6.98899 2.42851C7.27466 2.71418 7.4175 3.05759 7.4175 3.45875C7.4175 3.85991 7.27466 4.20332 6.98899 4.48899C6.70332 4.77466 6.35991 4.9175 5.95875 4.9175ZM10.335 4.9175C9.93384 4.9175 9.59043 4.77466 9.30476 4.48899C9.01909 4.20332 8.87625 3.85991 8.87625 3.45875C8.87625 3.05759 9.01909 2.71418 9.30476 2.42851C9.59043 2.14284 9.93384 2 10.335 2C10.7362 2 11.0796 2.14284 11.3652 2.42851C11.6509 2.71418 11.7937 3.05759 11.7937 3.45875C11.7937 3.85991 11.6509 4.20332 11.3652 4.48899C11.0796 4.77466 10.7362 4.9175 10.335 4.9175Z" fill="#64748B"/></g></svg></svg><span class="tooltip-text">Drag & Drop</span></span></span></label></li>'
						);
					}
					if ($( "#update_order_checkbox" ).is( ":checked" )) {
						$( "#sortable" ).append(
							'<li class="default-order-sheet ui-state-default ui-sortable-handle updateorder">  <label> <span class="orderSheet-left">  <span class="wootextfield">Update</span> </span> <span class="orderSheet-right"> <input type="checkbox" name="header_fields_custom[]" value="Update" class="headers_chk1" hidden="true" checked><input type="checkbox" name="header_fields[]" id="" class="headers_chk" value="Update" checked>  <span class="ui-icon ui-icon-caret-2-n-s wpssw-tooltio-link">   <svg width="17" height="16" viewBox="0 0 17 16" fill="none" xmlns="http://www.w3.org/2000/svg"> <mask id="mask0_384_3228" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="0" y="0" width="17" height="16"> <rect x="0.5" width="16" height="16" fill="#D9D9D9"/> </mask> <g mask="url(#mask0_384_3228)"> <path d="M5.95875 13.67C5.55759 13.67 5.21418 13.5272 4.92851 13.2415C4.64284 12.9558 4.5 12.6124 4.5 12.2113C4.5 11.8101 4.64284 11.4667 4.92851 11.181C5.21418 10.8953 5.55759 10.7525 5.95875 10.7525C6.35991 10.7525 6.70332 10.8953 6.98899 11.181C7.27466 11.4667 7.4175 11.8101 7.4175 12.2113C7.4175 12.6124 7.27466 12.9558 6.98899 13.2415C6.70332 13.5272 6.35991 13.67 5.95875 13.67ZM10.335 13.67C9.93384 13.67 9.59043 13.5272 9.30476 13.2415C9.01909 12.9558 8.87625 12.6124 8.87625 12.2113C8.87625 11.8101 9.01909 11.4667 9.30476 11.181C9.59043 10.8953 9.93384 10.7525 10.335 10.7525C10.7362 10.7525 11.0796 10.8953 11.3652 11.181C11.6509 11.4667 11.7937 11.8101 11.7937 12.2113C11.7937 12.6124 11.6509 12.9558 11.3652 13.2415C11.0796 13.5272 10.7362 13.67 10.335 13.67ZM5.95875 9.29375C5.55759 9.29375 5.21418 9.15091 4.92851 8.86524C4.64284 8.57957 4.5 8.23616 4.5 7.835C4.5 7.43384 4.64284 7.09043 4.92851 6.80476C5.21418 6.51909 5.55759 6.37625 5.95875 6.37625C6.35991 6.37625 6.70332 6.51909 6.98899 6.80476C7.27466 7.09043 7.4175 7.43384 7.4175 7.835C7.4175 8.23616 7.27466 8.57957 6.98899 8.86524C6.70332 9.15091 6.35991 9.29375 5.95875 9.29375ZM10.335 9.29375C9.93384 9.29375 9.59043 9.15091 9.30476 8.86524C9.01909 8.57957 8.87625 8.23616 8.87625 7.835C8.87625 7.43384 9.01909 7.09043 9.30476 6.80476C9.59043 6.51909 9.93384 6.37625 10.335 6.37625C10.7362 6.37625 11.0796 6.51909 11.3652 6.80476C11.6509 7.09043 11.7937 7.43384 11.7937 7.835C11.7937 8.23616 11.6509 8.57957 11.3652 8.86524C11.0796 9.15091 10.7362 9.29375 10.335 9.29375ZM5.95875 4.9175C5.55759 4.9175 5.21418 4.77466 4.92851 4.48899C4.64284 4.20332 4.5 3.85991 4.5 3.45875C4.5 3.05759 4.64284 2.71418 4.92851 2.42851C5.21418 2.14284 5.55759 2 5.95875 2C6.35991 2 6.70332 2.14284 6.98899 2.42851C7.27466 2.71418 7.4175 3.05759 7.4175 3.45875C7.4175 3.85991 7.27466 4.20332 6.98899 4.48899C6.70332 4.77466 6.35991 4.9175 5.95875 4.9175ZM10.335 4.9175C9.93384 4.9175 9.59043 4.77466 9.30476 4.48899C9.01909 4.20332 8.87625 3.85991 8.87625 3.45875C8.87625 3.05759 9.01909 2.71418 9.30476 2.42851C9.59043 2.14284 9.93384 2 10.335 2C10.7362 2 11.0796 2.14284 11.3652 2.42851C11.6509 2.71418 11.7937 3.05759 11.7937 3.45875C11.7937 3.85991 11.6509 4.20332 11.3652 4.48899C11.0796 4.77466 10.7362 4.9175 10.335 4.9175Z" fill="#64748B"/></g></svg></svg><span class="tooltip-text">Drag & Drop</span></span></span></label></li>'
						);
					}
					if ($( "#delete_order_checkbox" ).is( ":checked" )) {
						$( "#sortable" ).append(
							'<li class="default-order-sheet ui-state-default ui-sortable-handle deleteorder">  <label> <span class="orderSheet-left">  <span class="wootextfield">Delete</span> </span> <span class="orderSheet-right"> <input type="checkbox" name="header_fields_custom[]" value="Delete" class="headers_chk1" hidden="true" checked><input type="checkbox" name="header_fields[]" id="" class="headers_chk" value="Delete" checked>  <span class="ui-icon ui-icon-caret-2-n-s wpssw-tooltio-link">   <svg width="17" height="16" viewBox="0 0 17 16" fill="none" xmlns="http://www.w3.org/2000/svg"> <mask id="mask0_384_3228" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="0" y="0" width="17" height="16"> <rect x="0.5" width="16" height="16" fill="#D9D9D9"/> </mask> <g mask="url(#mask0_384_3228)"> <path d="M5.95875 13.67C5.55759 13.67 5.21418 13.5272 4.92851 13.2415C4.64284 12.9558 4.5 12.6124 4.5 12.2113C4.5 11.8101 4.64284 11.4667 4.92851 11.181C5.21418 10.8953 5.55759 10.7525 5.95875 10.7525C6.35991 10.7525 6.70332 10.8953 6.98899 11.181C7.27466 11.4667 7.4175 11.8101 7.4175 12.2113C7.4175 12.6124 7.27466 12.9558 6.98899 13.2415C6.70332 13.5272 6.35991 13.67 5.95875 13.67ZM10.335 13.67C9.93384 13.67 9.59043 13.5272 9.30476 13.2415C9.01909 12.9558 8.87625 12.6124 8.87625 12.2113C8.87625 11.8101 9.01909 11.4667 9.30476 11.181C9.59043 10.8953 9.93384 10.7525 10.335 10.7525C10.7362 10.7525 11.0796 10.8953 11.3652 11.181C11.6509 11.4667 11.7937 11.8101 11.7937 12.2113C11.7937 12.6124 11.6509 12.9558 11.3652 13.2415C11.0796 13.5272 10.7362 13.67 10.335 13.67ZM5.95875 9.29375C5.55759 9.29375 5.21418 9.15091 4.92851 8.86524C4.64284 8.57957 4.5 8.23616 4.5 7.835C4.5 7.43384 4.64284 7.09043 4.92851 6.80476C5.21418 6.51909 5.55759 6.37625 5.95875 6.37625C6.35991 6.37625 6.70332 6.51909 6.98899 6.80476C7.27466 7.09043 7.4175 7.43384 7.4175 7.835C7.4175 8.23616 7.27466 8.57957 6.98899 8.86524C6.70332 9.15091 6.35991 9.29375 5.95875 9.29375ZM10.335 9.29375C9.93384 9.29375 9.59043 9.15091 9.30476 8.86524C9.01909 8.57957 8.87625 8.23616 8.87625 7.835C8.87625 7.43384 9.01909 7.09043 9.30476 6.80476C9.59043 6.51909 9.93384 6.37625 10.335 6.37625C10.7362 6.37625 11.0796 6.51909 11.3652 6.80476C11.6509 7.09043 11.7937 7.43384 11.7937 7.835C11.7937 8.23616 11.6509 8.57957 11.3652 8.86524C11.0796 9.15091 10.7362 9.29375 10.335 9.29375ZM5.95875 4.9175C5.55759 4.9175 5.21418 4.77466 4.92851 4.48899C4.64284 4.20332 4.5 3.85991 4.5 3.45875C4.5 3.05759 4.64284 2.71418 4.92851 2.42851C5.21418 2.14284 5.55759 2 5.95875 2C6.35991 2 6.70332 2.14284 6.98899 2.42851C7.27466 2.71418 7.4175 3.05759 7.4175 3.45875C7.4175 3.85991 7.27466 4.20332 6.98899 4.48899C6.70332 4.77466 6.35991 4.9175 5.95875 4.9175ZM10.335 4.9175C9.93384 4.9175 9.59043 4.77466 9.30476 4.48899C9.01909 4.20332 8.87625 3.85991 8.87625 3.45875C8.87625 3.05759 9.01909 2.71418 9.30476 2.42851C9.59043 2.14284 9.93384 2 10.335 2C10.7362 2 11.0796 2.14284 11.3652 2.42851C11.6509 2.71418 11.7937 3.05759 11.7937 3.45875C11.7937 3.85991 11.6509 4.20332 11.3652 4.48899C11.0796 4.77466 10.7362 4.9175 10.335 4.9175Z" fill="#64748B"/></g></svg></svg><span class="tooltip-text">Drag & Drop</span></span></span></label></li>'
						);
					}
				}
			);
			// Select all headers.
			$( "#selectall" ).on(
				"click",
				function () {
					$( ".headers_chk" ).prop( "checked", true );
					$( ".headers_chk1" ).prop( "checked", true );
					if ($( 'input[name="header_fields_static[]"]' ).length > 0) {
						$( 'input[name="header_fields_static[]"]' ).prop( "checked", true );
					}
					if ($( 'input[name="wpssw_static_header_values[]"]' ).length > 0) {
						$( 'input[name="wpssw_static_header_values[]"]' ).prop( "checked", true );
					}
				}
			);
			// Select all headers.
			$( "#selectnone" ).on(
				"click",
				function () {
					$( ".headers_chk" ).prop( "checked", false );
					$( ".headers_chk1" ).prop( "checked", false );
					if ($( 'input[name="header_fields_static[]"]' ).length > 0) {
						$( 'input[name="header_fields_static[]"]' ).prop( "checked", false );
					}
					if ($( 'input[name="wpssw_static_header_values[]"]' ).length > 0) {
						$( 'input[name="wpssw_static_header_values[]"]' ).prop( "checked", false );
					}
					$( ".insertorder .headers_chk" ).prop( "checked", true );
					$( ".insertorder  .headers_chk1" ).prop( "checked", true );
					$( ".updateorder .headers_chk" ).prop( "checked", true );
					$( ".updateorder  .headers_chk1" ).prop( "checked", true );
					$( ".deleteorder .headers_chk" ).prop( "checked", true );
					$( ".deleteorder  .headers_chk1" ).prop( "checked", true );
				}
			);
			// Select all headers.
			$( "body" ).on(
				"click",
				"#prdselectall",
				function () {
					$( ".prdheaders_chk" ).prop( "checked", true );
					$( ".prdheaders_chk1" ).prop( "checked", true );
				}
			);
			// Deselect all headers.
			$( "body" ).on(
				"click",
				"#prdselectnone",
				function () {
					$( ".prdheaders_chk" ).prop( "checked", false );
					$( ".prdheaders_chk1" ).prop( "checked", false );
				}
			);
			// Select all category.
			$( "#prdcatselectall" ).on(
				"click",
				function () {
					$( ".prdcatheaders_chk" ).prop( "checked", true );
				}
			);
			$( "#prdcatselectnone" ).on(
				"click",
				function () {
					$( ".prdcatheaders_chk" ).prop( "checked", false );
				}
			);
			// Select all Product.
			$( "#woo-proselectall" ).on(
				"click",
				function () {
					$( ".woo-pro-headers-chk" ).prop( "checked", true );
					$( ".woo-pro-headers-chk1" ).prop( "checked", true );
					if ($( 'input[name="product_header_fields_static[]"]' ).length > 0) {
						$( 'input[name="product_header_fields_static[]"]' ).prop( "checked", true );
					}
					if ($( 'input[name="wpssw_static_product_header_values[]"]' ).length > 0) {
						$( 'input[name="wpssw_static_product_header_values[]"]' ).prop(
							"checked",
							true
						);
					}
				}
			);
			$( "#woo-proselectnone" ).on(
				"click",
				function () {
					$( ".woo-pro-headers-chk" ).prop( "checked", false );
					$( ".woo-pro-headers-chk1" ).prop( "checked", false );
					if ($( 'input[name="product_header_fields_static[]"]' ).length > 0) {
						$( 'input[name="product_header_fields_static[]"]' ).prop(
							"checked",
							false
						);
					}
					if ($( 'input[name="wpssw_static_product_header_values[]"]' ).length > 0) {
						$( 'input[name="wpssw_static_product_header_values[]"]' ).prop(
							"checked",
							false
						);
					}
					$( ".insertproduct .woo-pro-headers-chk" ).prop( "checked", true );
					$( ".insertproduct  .woo-pro-headers-chk1" ).prop( "checked", true );
					$( ".updateproduct .woo-pro-headers-chk" ).prop( "checked", true );
					$( ".updateproduct  .woo-pro-headers-chk1" ).prop( "checked", true );
					$( ".deleteproduct .woo-pro-headers-chk" ).prop( "checked", true );
					$( ".deleteproduct  .woo-pro-headers-chk1" ).prop( "checked", true );
				}
			);
			$( "#woo-custselectall" ).on(
				"click",
				function () {
					$( ".woo-cust-headers-chk" ).prop( "checked", true );
					$( ".woo-cust-headers-chk1" ).prop( "checked", true );
					if ($( 'input[name="customer_header_fields_static[]"]' ).length > 0) {
						$( 'input[name="customer_header_fields_static[]"]' ).prop(
							"checked",
							true
						);
					}
					if ($( 'input[name="wpssw_static_customer_header_values[]"]' ).length > 0) {
						$( 'input[name="wpssw_static_customer_header_values[]"]' ).prop(
							"checked",
							true
						);
					}
				}
			);
			$( "#woo-custselectnone" ).on(
				"click",
				function () {
					$( ".woo-cust-headers-chk" ).prop( "checked", false );
					$( ".woo-cust-headers-chk1" ).prop( "checked", false );
					if ($( 'input[name="customer_header_fields_static[]"]' ).length > 0) {
						$( 'input[name="customer_header_fields_static[]"]' ).prop(
							"checked",
							false
						);
					}
					if ($( 'input[name="wpssw_static_customer_header_values[]"]' ).length > 0) {
						$( 'input[name="wpssw_static_customer_header_values[]"]' ).prop(
							"checked",
							false
						);
					}
					$( ".insertcustomer .woo-cust-headers-chk" ).prop( "checked", true );
					$( ".insertcustomer  .woo-cust-headers-chk1" ).prop( "checked", true );
					$( ".updatecustomer .woo-cust-headers-chk" ).prop( "checked", true );
					$( ".updatecustomer  .woo-cust-headers-chk1" ).prop( "checked", true );
					$( ".deletecustomer .woo-cust-headers-chk" ).prop( "checked", true );
					$( ".deletecustomer  .woo-cust-headers-chk1" ).prop( "checked", true );
				}
			);
			$( "#woo-couponselectall" ).on(
				"click",
				function () {
					$( ".woo-coupon-headers-chk" ).prop( "checked", true );
					$( ".woo-coupon-headers-chk1" ).prop( "checked", true );
				}
			);
			$( "#woo-couponselectnone" ).on(
				"click",
				function () {
					$( ".woo-coupon-headers-chk" ).prop( "checked", false );
					$( ".woo-coupon-headers-chk1" ).prop( "checked", false );
					$( ".insertcoupon .woo-coupon-headers-chk" ).prop( "checked", true );
					$( ".insertcoupon  .woo-coupon-headers-chk1" ).prop( "checked", true );
					$( ".updatecoupon .woo-coupon-headers-chk" ).prop( "checked", true );
					$( ".updatecoupon  .woo-coupon-headers-chk1" ).prop( "checked", true );
					$( ".deletecoupon .woo-coupon-headers-chk" ).prop( "checked", true );
					$( ".deletecoupon  .woo-coupon-headers-chk1" ).prop( "checked", true );
				}
			);
			$( "#woo-eventselectall" ).on(
				"click",
				function () {
					$( ".woo-event-headers-chk" ).prop( "checked", true );
					$( ".woo-event-headers-chk1" ).prop( "checked", true );
				}
			);
			$( "#woo-eventselectnone" ).on(
				"click",
				function () {
					$( ".woo-event-headers-chk" ).prop( "checked", false );
					$( ".woo-event-headers-chk1" ).prop( "checked", false );
				}
			);

			// Select all product categories as sheet options.
			$( "body" ).on(
				"click",
				"#prdcat_sheets_selectall",
				function () {
					$( ".prdcat_sheets_chk" ).prop( "checked", true );
				}
			);
			// Deselect all product categories as sheet options.
			$( "body" ).on(
				"click",
				"#prdcat_sheets_selectnone",
				function () {
					$( ".prdcat_sheets_chk" ).prop( "checked", false );
				}
			);

			var existingSheets = "";
			existingSheets     = $( "#existing_sheets option" )
			.map(
				function () {
					return $( this ).val();
				}
			)
			.get();

			$( document ).on(
				"click",
				".ui-icon-pencil",
				function (e) {
					e.preventDefault();
					var $wpsswThis = $( this );

					if (
					$wpsswThis.hasClass( "edit-sheetname" ) ||
					$wpsswThis.hasClass( "edit-customsheetname" )
					) {
						var editNameEl    = "";
						var editClassName = "";
						var trRow         = $( this ).closest( "div.default-order-sheet" );
						var headerName    = $( trRow ).find( ".wootextfield" ).html();
						if ($wpsswThis.hasClass( "edit-sheetname" )) {
								editNameEl    = $( trRow ).find( ".editsheet" );
								editClassName = "editsheet";
						} else if ($wpsswThis.hasClass( "edit-customsheetname" )) {
							editNameEl    = $( trRow ).find( ".editcustomsheet" );
							editClassName = "editcustomsheet";
						}

						$( trRow )
						.find( ".wootextfield" )
						.html(
							'<span class="sheetheaders-edit"><input type="text" style="width: 145px;" class="' +
							editClassName +
							'" value="' +
							headerName +
							'"></span>'
						);
						$( editNameEl ).focus();
						$( editNameEl ).val( "" );
						$( editNameEl ).val( headerName );
						$( trRow ).addClass( "custom" );

						var index = jQuery.inArray( headerName, existingSheets );
						if (index !== -1) {
								existingSheets.splice( index, 1 );
						}
					} else {
						var headerName = $( this ).siblings( ".wootextfield" ).html();
						$( this )
						.siblings( ".wootextfield" )
						.html(
							'<span class="sheetheaders-edit"><input type="text" style="width: 145px;" class="editheader" value="' +
							headerName +
							'"></span>'
						);
						$( this ).parent().find( ".editheader" ).focus();
						$( this ).parent().find( ".editheader" ).val( "" );
						$( this ).parent().find( ".editheader" ).val( headerName );
						$( this ).parent().parent( "li" ).addClass( "custom" );
					}
					setTimeout(
						function () {
							$wpsswThis.removeClass( "ui-icon-pencil" ).addClass( "ui-icon-check" );
						},
						10
					);
				}
			);
			$( document ).on(
				"click",
				".ui-icon-check",
				function (e) {
					e.preventDefault();
					var $wpsswThis = $( this );
					if (
					$wpsswThis.hasClass( "edit-sheetname" ) ||
					$wpsswThis.hasClass( "edit-customsheetname" )
					) {
						var editNameEl   = "";
						var customNameEl = "";

						var trRow = $( this ).closest( "div.default-order-sheet" );
						if ($wpsswThis.hasClass( "edit-sheetname" )) {
								editNameEl   = $( trRow ).find( ".editsheet" );
								customNameEl = $( trRow ).find( ".sheets_chk1" );
						} else if ($wpsswThis.hasClass( "edit-customsheetname" )) {
							editNameEl   = $( trRow ).find( ".editcustomsheet" );
							customNameEl = $( trRow ).find( ".customsheets_chk1" );
						}
						var trVal = $( customNameEl ).val();
						var input = $( editNameEl ).val();
						var index = jQuery.inArray( $.trim( input ), existingSheets );
						if ( ! $.trim( input )) {
							alert( "Please enter Sheet Name" );
							$( editNameEl ).focus();
							return false;
						}
						if (index !== -1) {
							alert(
								'A sheet with the name "' +
								$.trim( input ) +
								'" already exists. Please enter another name.'
							);
							$( editNameEl ).val( trVal ).change();
							$( editNameEl ).focus();
							return false;
						} else {
							existingSheets.push( $.trim( input ) );
						}
						$( customNameEl ).attr( "value", $.trim( input ) );
						$( trRow ).find( ".wootextfield" ).html( input );
						$( trRow ).removeClass( "custom" );
					} else {
						$( ".editheader" ).trigger( "focusout" );
						var input = $( this ).parent().find( ".editheader" ).val();
						if ( ! $.trim( input )) {
							alert( "Please enter Header Name" );
							$( this ).parent().find( ".editheader" ).focus();
							return false;
						}
						$( this ).siblings( ".wootextfield" ).html( input );
						$( this ).parent().parent( "li" ).removeClass( "custom" );
					}
					setTimeout(
						function () {
							var input = $wpsswThis.siblings( "input" ).val();
							$wpsswThis.siblings( ".wootextfield" ).html( input );
							$wpsswThis.removeClass( "ui-icon-check" ).addClass( "ui-icon-pencil" );
						},
						10
					);
				}
			);
			$( document ).on(
				"focusout",
				".editheader",
				function (e) {
					e.preventDefault();
					if ( ! $.trim( $( this ).val() )) {
						return false;
					}
					var $wpsswThis = $( this );
					var liEl       = $( this ).closest( "li" );
					$( liEl ).find( ".headers_chk1" ).attr( "value", $( this ).val() );
					$( liEl ).find( ".prdheaders_chk1" ).attr( "value", $( this ).val() );
					$( liEl ).find( ".woo-pro-headers-chk1" ).attr( "value", $( this ).val() );
					$( liEl ).find( ".woo-cust-headers-chk1" ).attr( "value", $( this ).val() );
					$( liEl ).find( ".woo-coupon-headers-chk1" ).attr( "value", $( this ).val() );
					$( liEl ).find( ".woo-event-headers-chk1" ).attr( "value", $( this ).val() );
				}
			);

			// Select all category.
			$( "body" ).on(
				"click",
				"#producatselectall",
				function () {
					$( ".producatheaders_chk" ).prop( "checked", true );
				}
			);
			$( "body" ).on(
				"click",
				"#producatselectnone",
				function () {
					$( ".producatheaders_chk" ).prop( "checked", false );
				}
			);
			// Product Header.
			$( document ).on(
				"click",
				".ui-icon-pencil-prd",
				function (e) {
					e.preventDefault();
					var $wpsswThis = $( this );
					var headerName = $( this ).siblings( ".wootextfield" ).html();
					$( this )
					.siblings( ".wootextfield" )
					.html(
						'<input type="text" style="width: 145px;" class="prdeditheader" value="' +
						headerName +
						'">'
					);
					$( this ).parent().find( ".prdeditheader" ).focus();
					$( this ).parent().find( ".prdeditheader" ).val( "" );
					$( this ).parent().find( ".prdeditheader" ).val( headerName );
					$( this ).parent().parent( "li" ).addClass( "custom" );
					setTimeout(
						function () {
							$wpsswThis.removeClass( "ui-icon-pencil" ).addClass( "ui-icon-check" );
							$wpsswThis
							.removeClass( "ui-icon-pencil-prd" )
							.addClass( "ui-icon-check-prd" );
						},
						10
					);
				}
			);
			$( document ).on(
				"click",
				".ui-icon-check-prd",
				function (e) {
					e.preventDefault();
					var $wpsswThis = $( this );
					var input      = $( this ).parent().find( ".editheader" ).val();
					if ( ! $.trim( input )) {
						alert( "Please enter Header Name" );
						$( this ).parent().find( ".prdeditheader" ).focus();
						return false;
					}
					$( this ).siblings( ".wootextfield" ).html( input );
					$( this ).parent().parent( "li" ).removeClass( "custom" );
					setTimeout(
						function () {
							var input = $wpsswThis.siblings( "input" ).val();
							$wpsswThis.siblings( ".wootextfield" ).html( input );
							$wpsswThis.removeClass( "ui-icon-check" ).addClass( "ui-icon-pencil" );
							$wpsswThis
							.removeClass( "ui-icon-check-prd" )
							.addClass( "ui-icon-pencil-prd" );
						},
						10
					);
				}
			);
			$( document ).on(
				"focusout",
				".prdeditheader",
				function (e) {
					e.preventDefault();
					if ( ! $.trim( $( this ).val() )) {
						return false;
					}
					var $wpsswThis = $( this );
					$( this )
					.closest( "li" )
					.find( ".prdheaders_chk1" )
					.attr( "value", $( this ).val() );
				}
			);
			$( document ).on(
				"change",
				".headers_chk",
				function (e) {
					if ($( this ).is( ":checked" )) {
						$( this ).siblings( ":checkbox" ).prop( "checked", true );
					} else if ($( this ).is( ":not(:checked)" )) {
						$( this ).siblings( ":checkbox" ).prop( "checked", false );
					}
				}
			);
			$( document ).on(
				"change",
				".prdheaders_chk",
				function (e) {
					if ($( this ).is( ":checked" )) {
						$( this ).siblings( ":checkbox" ).prop( "checked", true );
					} else if ($( this ).is( ":not(:checked)" )) {
						$( this ).siblings( ":checkbox" ).prop( "checked", false );
					}
				}
			);
			$( document ).on(
				"change",
				".woo-pro-headers-chk",
				function (e) {
					if ($( this ).is( ":checked" )) {
						$( this ).siblings( ":checkbox" ).prop( "checked", true );
					} else if ($( this ).is( ":not(:checked)" )) {
						$( this ).siblings( ":checkbox" ).prop( "checked", false );
					}
				}
			);
			$( document ).on(
				"change",
				".woo-cust-headers-chk",
				function (e) {
					if ($( this ).is( ":checked" )) {
						$( this ).siblings( ":checkbox" ).prop( "checked", true );
					} else if ($( this ).is( ":not(:checked)" )) {
						$( this ).siblings( ":checkbox" ).prop( "checked", false );
					}
				}
			);
			$( document ).on(
				"change",
				".woo-coupon-headers-chk",
				function (e) {
					if ($( this ).is( ":checked" )) {
						$( this ).siblings( ":checkbox" ).prop( "checked", true );
					} else if ($( this ).is( ":not(:checked)" )) {
						$( this ).siblings( ":checkbox" ).prop( "checked", false );
					}
				}
			);
			$( document ).on(
				"change",
				".woo-event-headers-chk",
				function (e) {
					if ($( this ).is( ":checked" )) {
						$( this ).siblings( ":checkbox" ).prop( "checked", true );
					} else if ($( this ).is( ":not(:checked)" )) {
						$( this ).siblings( ":checkbox" ).prop( "checked", false );
					}
				}
			);
			$( document ).on(
				"change",
				".sheets_chk",
				function (e) {
					if ($( this ).is( ":checked" )) {
						$( this ).siblings( ":checkbox" ).prop( "checked", true );
					} else if ($( this ).is( ":not(:checked)" )) {
						$( this ).siblings( ":checkbox" ).prop( "checked", false );
					}
				}
			);
			$( document ).on(
				"change",
				".customsheets_chk",
				function (e) {
					if ($( this ).is( ":checked" )) {
						$( this ).siblings( ":checkbox" ).prop( "checked", true );
					} else if ($( this ).is( ":not(:checked)" )) {
						$( this ).siblings( ":checkbox" ).prop( "checked", false );
					}
				}
			);

			$( document ).on(
				"change",
				"#order_settings_checkbox",
				function (e) {
					if ($( this ).is( ":checked" )) {
						$( ".ord_spreadsheet_inputrow" ).removeClass( "ord_spreadsheet_row" );
						$( ".ord_spreadsheet_row" ).fadeIn();
						if ($( "#prdassheetheaders" ).is( ":checked" )) {
							$( ".td-prd-wpssw-headers" ).show();
							$( ".td-prd-append-after" ).show();
						} else {
							$( ".td-prd-wpssw-headers" ).hide();
							$( ".td-prd-append-after" ).hide();
						}
						if ($( "#product_category_select" ).is( ":checked" )) {
							$( ".td-producat-wpssw" ).show();
						} else {
							$( ".td-producat-wpssw" ).hide();
						}
						if (
						String( $( "input[type=radio][name=header_format]:checked" ).val() ) ===
						"productwise"
						) {
								$( ".repeat_checkbox" ).show();
						} else {
							$( ".repeat_checkbox" ).hide();
						}
						var order_spreadsheet = $( "#woocommerce_spreadsheet" ).val();
						if (String( order_spreadsheet ) === "new") {
							$( ".ord_spreadsheet_inputrow" ).show();
						} else if (String( order_spreadsheet ) === "") {
							$( ".ord_import_row.import_row" ).hide();
							if ($( "#import_order_checkbox" ).is( ":checked" )) {
								$( ".wpssw_crud_ord_row.wpssw_all_crud_row" ).show();
							} else {
								$( ".wpssw_crud_ord_row.wpssw_all_crud_row" ).hide();
							}
						} else {
							if ($( "#import_order_checkbox" ).is( ":checked" )) {
								$( ".wpssw_crud_ord_row.wpssw_all_crud_row" ).show();
								if (
								$( "#insert_order_checkbox" ).is( ":checked" ) ||
								$( "#update_order_checkbox" ).is( ":checked" ) ||
								$( "#delete_order_checkbox" ).is( ":checked" )
										) {
									$( ".ord_import_row.import_row" ).show();
								} else {
									$( ".ord_import_row.import_row" ).hide();
								}
							} else {
								$( ".ord_import_row.import_row" ).hide();
								$( ".wpssw_crud_ord_row.wpssw_all_crud_row" ).hide();
							}
						}
					} else if ($( this ).is( ":not(:checked)" )) {
						$( ".ord_spreadsheet_inputrow" ).addClass( "ord_spreadsheet_row" );
						$( ".ord_spreadsheet_row" ).fadeOut();
					}
				}
			);
			$( document ).on(
				"change",
				"#product_settings_checkbox",
				function (e) {
					if ($( this ).is( ":checked" )) {
						$( ".prd_spreadsheet_inputrow" ).removeClass( "prd_spreadsheet_row" );
						$( ".prd_spreadsheet_row" ).fadeIn();
						var product_spreadsheet = $( "#product_spreadsheet" ).val();
						if (String( product_spreadsheet ) === "new") {
							$( ".prd_spreadsheet_inputrow" ).show();
						} else if (String( product_spreadsheet ) === "") {
							$( ".prd_import_row.import_row" ).hide();
							if ($( "#import_checkbox" ).is( ":checked" )) {
								$( ".wpssw_crud_row.wpssw_all_crud_row" ).show();
							} else {
								$( ".wpssw_crud_row.wpssw_all_crud_row" ).hide();
							}
						} else {
							if ($( "#import_checkbox" ).is( ":checked" )) {
								$( ".wpssw_crud_row.wpssw_all_crud_row" ).show();
								if (
								$( "#insert_checkbox" ).is( ":checked" ) ||
								$( "#update_checkbox" ).is( ":checked" ) ||
								$( "#delete_checkbox" ).is( ":checked" )
								) {
									$( ".prd_import_row.import_row" ).show();
								} else {
									$( ".prd_import_row.import_row" ).hide();
								}
							} else {
								$( ".wpssw_crud_row.wpssw_all_crud_row" ).hide();
								$( ".prd_import_row.import_row" ).hide();
							}
						}
					} else if ($( this ).is( ":not(:checked)" )) {
						$( ".prd_spreadsheet_inputrow" ).addClass( "prd_spreadsheet_row" );
						$( ".prd_spreadsheet_row" ).fadeOut();
					}
				}
			);
			$( document ).on(
				"change",
				"#customer_settings_checkbox",
				function (e) {
					if ($( this ).is( ":checked" )) {
						$( ".cust_spreadsheet_inputrow" ).removeClass( "cust_spreadsheet_row" );
						$( ".cust_spreadsheet_row" ).fadeIn();
						var customer_spreadsheet = $( "#customer_spreadsheet" ).val();
						if (String( customer_spreadsheet ) === "new") {
							$( ".cust_spreadsheet_inputrow" ).show();
						} else if (String( customer_spreadsheet ) === "") {
							$( ".cust_import_row.import_row" ).hide();
							if ($( "#import_customer_checkbox" ).is( ":checked" )) {
								$( ".wpssw_crud_cust_row.wpssw_all_crud_row" ).show();
							} else {
								$( ".wpssw_crud_cust_row.wpssw_all_crud_row" ).hide();
							}
						} else {
							if ($( "#import_customer_checkbox" ).is( ":checked" )) {
								$( ".wpssw_crud_cust_row.wpssw_all_crud_row" ).show();
								if (
								$( "#insert_customer_checkbox" ).is( ":checked" ) ||
								$( "#update_customer_checkbox" ).is( ":checked" ) ||
								$( "#delete_customer_checkbox" ).is( ":checked" )
								) {
									$( ".cust_import_row.import_row" ).show();
								} else {
									$( ".cust_import_row.import_row" ).hide();
								}
							} else {
								$( ".wpssw_crud_cust_row.wpssw_all_crud_row" ).hide();
								$( ".cust_import_row.import_row" ).hide();
							}
						}
					} else if ($( this ).is( ":not(:checked)" )) {
						$( ".cust_spreadsheet_inputrow" ).addClass( "cust_spreadsheet_row" );
						$( ".cust_spreadsheet_row" ).fadeOut();
					}
				}
			);
			$( document ).on(
				"change",
				"#coupon_settings_checkbox",
				function (e) {
					if ($( this ).is( ":checked" )) {
						$( ".coupon_spreadsheet_inputrow" ).removeClass( "coupon_spreadsheet_row" );
						$( ".coupon_spreadsheet_row" ).fadeIn();
						var coupon_spreadsheet = $( "#coupon_spreadsheet" ).val();
						if (String( coupon_spreadsheet ) === "new") {
							$( ".coupon_spreadsheet_inputrow" ).show();
						} else if (String( coupon_spreadsheet ) === "") {
							$( ".coupon_import_row.import_row" ).hide();
							if ($( "#import_coupon_checkbox" ).is( ":checked" )) {
								$( ".wpssw_crud_coupon_row.wpssw_all_crud_row" ).show();
							} else {
								$( ".wpssw_crud_coupon_row.wpssw_all_crud_row" ).hide();
							}
						} else {
							if ($( "#import_coupon_checkbox" ).is( ":checked" )) {
								$( ".wpssw_crud_coupon_row.wpssw_all_crud_row" ).show();
								if (
								$( "#insert_coupon_checkbox" ).is( ":checked" ) ||
								$( "#update_coupon_checkbox" ).is( ":checked" ) ||
								$( "#delete_coupon_checkbox" ).is( ":checked" )
								) {
									$( ".coupon_import_row.import_row" ).show();
								} else {
									$( ".coupon_import_row.import_row" ).hide();
								}
							} else {
								$( ".wpssw_crud_coupon_row.wpssw_all_crud_row" ).hide();
								$( ".coupon_import_row.import_row" ).hide();
							}
						}
					} else if ($( this ).is( ":not(:checked)" )) {
						$( ".coupon_spreadsheet_inputrow" ).addClass( "coupon_spreadsheet_row" );
						$( ".coupon_spreadsheet_row" ).fadeOut();
					}
				}
			);
			$( document ).on(
				"change",
				"#event_settings_checkbox",
				function (e) {
					if ($( this ).is( ":checked" )) {
						$( ".event_spreadsheet_row" ).fadeIn();
					} else if ($( this ).is( ":not(:checked)" )) {
						$( ".event_spreadsheet_row" ).fadeOut();
					}
				}
			);
			$( "#category_select" ).on(
				"change",
				function () {
					if (this.checked) {
						$( ".td-prdcat-wpssw" ).fadeIn();
					} else {
						$( ".td-prdcat-wpssw" ).fadeOut();
					}
				}
			);
			$( "#product_category_select" ).on(
				"change",
				function () {
					if (this.checked) {
						$( ".td-producat-wpssw" ).fadeIn();
					} else {
						$( ".td-producat-wpssw" ).fadeOut();
					}
				}
			);
			$( document ).on(
				"change",
				"#color_code",
				function (e) {
					if (this.checked) {
						$( ".bg-color-oddeven" ).fadeIn();
					} else {
						$( ".bg-color-oddeven" ).fadeOut();
					}
				}
			);
			// Validate newsheet name.
			$( "#mainform" ).on(
				"submit",
				function () {
					var is_enable = $( "#order_settings_checkbox" ).is( ":checked" );
					if ( ! is_enable) {
					} else {
						var isFormValid = true;
						var newRequest  = $( "#woocommerce_spreadsheet" ).val();

						var sheetSelection = $(
							'input[name="spreadsheetselection"]:checked'
						).val();

						if (String( sheetSelection ) === "new") {
							if (parseInt( $( "#spreadsheetname" ).val().length ) === 0) {
								$( "#newsheet" ).addClass( "highlight" );
								isFormValid = false;
							} else {
								$( this ).removeClass( "highlight" );
							}
							if ( ! isFormValid) {
								alert( "Please Enter Spreadsheet Name" );
								$( "html, body" ).animate(
									{
										scrollTop: 0,
									},
									1200
								);
								$( "#spreadsheetname" ).focus();
							}
							return isFormValid;
						} else {
							if (String( newRequest ) === "" || parseInt( newRequest ) === 0) {
								alert( "Please Select Spreadsheet." );
								$( "html, body" ).animate(
									{
										scrollTop: 0,
									},
									1200
								);
								$( "#woocommerce_spreadsheet" ).focus();
								return false;
							}
						}
						var headerformat = $(
							"input[type=radio][name=header_format]:checked"
						).val();
						if (
						$( "input[type=radio][name=header_format]" ).length > 0 &&
						(String( headerformat ) === "" || typeof headerformat === "undefined")
						) {
							alert( "Please select header format." );
							$( "html, body" ).animate(
								{
									scrollTop: $( "#header_format" ).first().offset().top - 140,
								},
								1200
							);
							$( "#header_format" ).focus();
							return false;
						}

						if (
						parseInt(
							$( ".default-order-sheet-section" ).find(
								"input[type=checkbox]:checked"
							).length
						) === 0
						) {
							var orderSheetPresent = false;
							if (
							$( ".custom-order-sheet-section" ).length > 0 &&
							parseInt(
								$( ".custom-order-sheet-section" ).find(
									"input[type=checkbox]:checked"
								).length
							) > 0
							) {
								orderSheetPresent = true;
							}
							if (Boolean( orderSheetPresent ) === false) {
								alert(
									"Please select at least one order status to get it work in spreadsheet"
								);
								$( "html, body" ).animate(
									{
										scrollTop:
										$( ".default-order-sheet-section" ).first().offset().top - 140,
									},
									1200
								);
								setTimeout(
									function () {
										$( ".default-order-sheet-section" )
										.first()
										.css( "border", "1px solid #ff5859" );
									},
									1000
								);
								$( ".default-order-sheet-section" ).first().focus();
								return false;
							}
						}
						if (
						$( "#sortable" ).length > 0 &&
						parseInt(
							$( "#sortable" ).find( "input[type=checkbox]:checked" ).length
						) <= 6
						) {
							var count = 0;
							if ($( "#insert_order_checkbox" ).is( ":checked" )) {
								count = count + 2;
							}
							if ($( "#update_order_checkbox" ).is( ":checked" )) {
								count = count + 2;
							}
							if ($( "#delete_order_checkbox" ).is( ":checked" )) {
								count = count + 2;
							}
							var length = parseInt(
								$( "#sortable" ).find( "input[type=checkbox]:checked" ).length
							);
							if (length == count) {
								alert(
									"Please select at least one sheet headers to get it work in spreadsheet"
								);
								$( "html, body" ).animate(
									{
										scrollTop: $( "#sortable" ).offset().top - 140,
									},
									1200
								);
								setTimeout(
									function () {
										$( "#sortable" ).css( "border", "1px solid #ff5859" );
									},
									1000
								);
								$( "#sortable" ).focus();
								return false;
							}
						}

						if (false == check_schedule_settings( "", "Sync" )) {
								return false;
						}
						if ($( "#import_order_checkbox" ).is( ":checked" )) {
							if (
							$( "#insert_order_checkbox" ).is( ":checked" ) ||
							$( "#update_order_checkbox" ).is( ":checked" ) ||
							$( "#delete_order_checkbox" ).is( ":checked" )
							) {
							} else {
								alert(
									"Please make sure you have enable one of these options, insert order , update order or delete order"
								);
								return false;
							}
						}
					}
				}
			);
			// Validate newsheet name.
			$( "#productform" ).on(
				"submit",
				function () {
					var is_enable = $( "#product_settings_checkbox" ).is( ":checked" );
					if ( ! is_enable) {
					} else {
						var isFormValid       = true;
						var newRequest        = $( "#product_spreadsheet" ).val();
						var spreadsheetOption = $(
							'input[name="prdsheetselection"]:checked'
						).val();

						if (String( spreadsheetOption ) === "new") {
							if (parseInt( $( "#product_spreadsheet_name" ).val().length ) === 0) {
								$( ".prd_spreadsheet_inputrow" ).addClass( "highlight" );
								isFormValid = false;
							} else {
								$( this ).removeClass( "highlight" );
							}
							if ( ! isFormValid) {
								alert( "Please Enter Spreadsheet Name" );
								$( "html, body" ).animate(
									{
										scrollTop: 0,
									},
									1200
								);
								$( "#product_spreadsheet_name" ).focus();
							}
							return isFormValid;
						} else {
							if (String( newRequest ) === "" || parseInt( newRequest ) === 0) {
								alert( "Please Select Spreadsheet." );
								$( "html, body" ).animate(
									{
										scrollTop: 0,
									},
									1200
								);
								$( "#product_spreadsheet" ).focus();
								return false;
							}
						}
						if (
						$( "#woo-product-sortable" ).length > 0 &&
						parseInt(
							$( "#woo-product-sortable" ).find( "input[type=checkbox]:checked" )
							.length
						) <= 6
						) {
							var count = 0;
							if ($( "#insert_checkbox" ).is( ":checked" )) {
								count = count + 2;
							}
							if ($( "#update_checkbox" ).is( ":checked" )) {
								count = count + 2;
							}
							if ($( "#delete_checkbox" ).is( ":checked" )) {
								count = count + 2;
							}
							var length = parseInt(
								$( "#woo-product-sortable" ).find( "input[type=checkbox]:checked" )
								.length
							);
							if (length == count) {
								alert(
									"Please select at least one sheet headers to get it work in spreadsheet"
								);
								$( "html, body" ).animate(
									{
										scrollTop: $( "#woo-product-sortable" ).offset().top - 140,
									},
									1200
								);
								setTimeout(
									function () {
										$( "#woo-product-sortable" ).css( "border", "1px solid #ff5859" );
									},
									1000
								);
								$( "#woo-product-sortable" ).focus();
								return false;
							}
						}
						if ($( "#import_checkbox" ).is( ":checked" )) {
							if (
								$( "#insert_checkbox" ).is( ":checked" ) ||
								$( "#update_checkbox" ).is( ":checked" ) ||
								$( "#delete_checkbox" ).is( ":checked" )
							) {
							} else {
								alert(
									"Please make sure you have enable one of these options, insert product, update product or delete product"
								);
								return false;
							}
							if ( ! $( "#woo-product_name" ).is( ":checked" )) {
								var productNameEl = $( "#woo-product_name" ).siblings( "input.woo-pro-headers-chk1" ).val();
								alert( "Please select '" + productNameEl + "' header." );
								$( "html, body" ).animate(
									{
										scrollTop: $( "#woo-product-sortable" ).offset().top - 140,
										},
									1200
								);
								setTimeout(
									function () {
												$( "#woo-product-sortable" ).css( "border", "1px solid #ff5859" );
									},
									1000
								);
								$( "#woo-product-sortable" ).focus();
								return false;
							}
						}

						if (false == check_schedule_settings( "prd_autosync_", "Sync" )) {
							return false;
						}
						if (false == check_schedule_settings( "product_", "Import" )) {
							return false;
						}
						if (
						1 === parseInt( $( "#wpssw_wpml_is_active" ).val() ) &&
						parseInt( $( "#wpssw_wpml_total_langs" ).val() ) > 0 &&
						false === Boolean( $( "#prd_sync_lang_all" ).is( ":checked" ) )
						) {
							var prdsyncCustomLangs = $(
								"input:checkbox[name='productlang_filter[]']:checked"
							)
								.map(
									function () {
										return $( this ).val();
									}
								).get();
							if ( ! prdsyncCustomLangs || prdsyncCustomLangs.length === 0) {
								alert( "Please select the language." );
								$( "html, body" ).animate(
									{
										scrollTop: $( "#prd_wpml_language_options" ).offset().top - 160,
									},
									1200
								);
								setTimeout(
									function () {
												$( "#prd_wpml_language_options" ).css(
													"border",
													"1px solid #ff5859"
												);
									},
									1000
								);
								$( "#prd_wpml_language_options" ).focus();
								return false;
							}
						}
					}
				}
			);
			// Validate newsheet name.
			$( "#customerform" ).on(
				"submit",
				function () {
					var is_enable = $( "#customer_settings_checkbox" ).is( ":checked" );
					if ( ! is_enable) {
					} else {
						var isFormValid = true;
						var newRequest  = $( "#customer_spreadsheet" ).val();

						var spreadsheetOption = $(
							'input[name="custsheetselection"]:checked'
						).val();
						if (String( spreadsheetOption ) === "new") {
							if (parseInt( $( "#customer_spreadsheet_name" ).val().length ) === 0) {
								$( ".cust_spreadsheet_inputrow" ).addClass( "highlight" );
								isFormValid = false;
							} else {
								$( this ).removeClass( "highlight" );
							}
							if ( ! isFormValid) {
								alert( "Please Enter Spreadsheet Name" );
								$( "html, body" ).animate(
									{
										scrollTop: 0,
									},
									1200
								);
								$( "#customer_spreadsheet_name" ).focus();
								return false;
							}
						} else {
							if (String( newRequest ) === "" || parseInt( newRequest ) === 0) {
								alert( "Please Select Spreadsheet." );
								$( "html, body" ).animate(
									{
										scrollTop: 0,
									},
									1200
								);
								$( "#customer_spreadsheet" ).focus();
								return false;
							}
						}
						if (
						$( "#woo-customer-sortable" ).length > 0 &&
						parseInt(
							$( "#woo-customer-sortable" ).find( "input[type=checkbox]:checked" )
							.length
						) <= 6
						) {
							var count = 0;
							if ($( "#insert_customer_checkbox" ).is( ":checked" )) {
								count = count + 2;
							}
							if ($( "#update_customer_checkbox" ).is( ":checked" )) {
								count = count + 2;
							}
							if ($( "#delete_customer_checkbox" ).is( ":checked" )) {
								count = count + 2;
							}
							var length = parseInt(
								$( "#woo-customer-sortable" ).find( "input[type=checkbox]:checked" )
								.length
							);
							if (length == count) {
								alert(
									"Please select at least one sheet headers to get it work in spreadsheet"
								);
								$( "html, body" ).animate(
									{
										scrollTop: $( "#woo-customer-sortable" ).offset().top - 140,
									},
									1200
								);
								setTimeout(
									function () {
										$( "#woo-customer-sortable" ).css( "border", "1px solid #ff5859" );
									},
									1000
								);
								$( "#woo-customer-sortable" ).focus();
								return false;
							}
						}

						if ($( "#import_customer_checkbox" ).is( ":checked" )) {
							if (
								$( "#insert_customer_checkbox" ).is( ":checked" ) ||
								$( "#update_customer_checkbox" ).is( ":checked" ) ||
								$( "#delete_customer_checkbox" ).is( ":checked" )
							) {
							} else {
								alert(
									"Please make sure you have enable one of these options, insert customer, update customer or delete customer"
								);
								return false;
							}
						}
						if (false == check_schedule_settings( "cust_autosync_", "Sync" )) {
							return false;
						}
						if (false == check_schedule_settings( "cust_autoimport_", "Import" )) {
							return false;
						}
						return isFormValid;
					}
				}
			);
			// Validate newsheet name.
			$( "#couponform" ).on(
				"submit",
				function () {
					var is_enable = $( "#coupon_settings_checkbox" ).is( ":checked" );
					if ( ! is_enable) {
					} else {
						var isFormValid       = true;
						var newRequest        = $( "#coupon_spreadsheet" ).val();
						var spreadsheetOption = $(
							'input[name="couponsheetselection"]:checked'
						).val();

						if (String( spreadsheetOption ) === "new") {
							if (parseInt( $( "#coupon_spreadsheet_name" ).val().length ) === 0) {
								$( ".coupon_spreadsheet_inputrow" ).addClass( "highlight" );
								isFormValid = false;
							} else {
								$( this ).removeClass( "highlight" );
							}
							if ( ! isFormValid) {
								alert( "Please Enter Spreadsheet Name" );
								$( "html, body" ).animate(
									{
										scrollTop: 0,
									},
									1200
								);
								$( "#coupon_spreadsheet_name" ).focus();
								return false;
							}
						} else {
							if (String( newRequest ) === "" || parseInt( newRequest ) === 0) {
								alert( "Please Select Spreadsheet." );
								$( "html, body" ).animate(
									{
										scrollTop: 0,
									},
									1200
								);
								$( "#coupon_spreadsheet" ).focus();
								return false;
							}
						}
						if (
						$( "#woo-coupon-sortable" ).length > 0 &&
						parseInt(
							$( "#woo-coupon-sortable" ).find( "input[type=checkbox]:checked" )
							.length
						) <= 6
						) {
							var count = 0;
							if ($( "#insert_coupon_checkbox" ).is( ":checked" )) {
								count = count + 2;
							}
							if ($( "#update_coupon_checkbox" ).is( ":checked" )) {
								count = count + 2;
							}
							if ($( "#delete_coupon_checkbox" ).is( ":checked" )) {
								count = count + 2;
							}
							var length = parseInt(
								$( "#woo-coupon-sortable" ).find( "input[type=checkbox]:checked" )
								.length
							);
							if (length == count) {
								alert(
									"Please select at least one sheet headers to get it work in spreadsheet"
								);
								$( "html, body" ).animate(
									{
										scrollTop: $( "#woo-coupon-sortable" ).offset().top - 140,
									},
									1200
								);
								setTimeout(
									function () {
										$( "#woo-coupon-sortable" ).css( "border", "1px solid #ff5859" );
									},
									1000
								);
								$( "#woo-coupon-sortable" ).focus();
								return false;
							}
						}
						if ($( "#import_coupon_checkbox" ).is( ":checked" )) {
							if (
								$( "#insert_coupon_checkbox" ).is( ":checked" ) ||
								$( "#update_coupon_checkbox" ).is( ":checked" ) ||
								$( "#delete_coupon_checkbox" ).is( ":checked" )
							) {
							} else {
								alert(
									"Please make sure you have enable one of these options, insert coupon, update coupon or delete coupon."
								);
								return false;
							}
						}
						if (false == check_schedule_settings( "coupon_autosync_", "Sync" )) {
							return false;
						}
						if (false == check_schedule_settings( "coupon_autoimport_", "Import" )) {
							return false;
						}
						return isFormValid;
					}
				}
			);
			$( "#eventform" ).on(
				"submit",
				function () {
					var isEnable = $( "#event_settings_checkbox" ).is( ":checked" );
					if ( ! isEnable) {
					} else {
						var isFormValid   = true;
						var newRequest    = $( "#woocommerce_spreadsheet" ).val();
						var isOrderEnable = $( "#order_settings_checkbox" ).is( ":checked" );
						if ( ! isOrderEnable) {
							alert( "Please enable order settings first and try again later." );
							return false;
						}
						if (String( newRequest ) === "" || parseInt( newRequest ) === 0) {
							alert( "Please Select Spreadsheet in order settings." );
							$( "html, body" ).animate(
								{
									scrollTop: 0,
								},
								1200
							);
							return false;
						}
						if (
						$( "#woo-event-sortable" ).length > 0 &&
						parseInt(
							$( "#woo-event-sortable" ).find( "input[type=checkbox]:checked" ).length
						) === 0
						) {
							alert(
								"Please select at least one sheet headers to get it work in spreadsheet"
							);
										$( "html, body" ).animate(
											{
												scrollTop: $( "#woo-event-sortable" ).offset().top - 140,
											},
											1200
										);
										setTimeout(
											function () {
												$( "#woo-event-sortable" ).css( "border", "1px solid #ff5859" );
											},
											1000
										);
										$( "#woo-event-sortable" ).focus();
										return false;
						}
						if (false == check_schedule_settings( "event_autosync_", "Sync" )) {
								return false;
						}
					}
				}
			);
			$( 'input[name="prdsheetselection"]' ).on(
				"change",
				function () {
					var newrequest = $( this ).val();
					if (newrequest == "new") {
						$( ".prd_spreadsheet_inputrow" ).show();
						$( "#product_spreadsheet_container" ).hide();
						$( "#prodsynctr" ).hide();
						$( ".prd_import_row.import_row" ).hide();
					} else {
						$( ".prd_spreadsheet_inputrow" ).hide();
						$( "#product_spreadsheet_container" ).show();
						var prdSheetId = $( "#product_spreadsheet" ).val();
						if (
						String( newrequest ) === "" ||
						parseInt( newrequest ) === 0 ||
						String( prdSheetId ) === "" ||
						parseInt( prdSheetId ) === 0 ||
						String( prdSheetId ) === "new"
						) {
							$( "#prodsynctr" ).hide();
							$( ".prd_import_row.import_row" ).hide();
						} else {
							$( "#prodsynctr" ).show();
							if (
							$( "#import_checkbox" ).is( ":checked" ) &&
							($( "#insert_checkbox" ).is( ":checked" ) ||
							$( "#update_checkbox" ).is( ":checked" ) ||
							$( "#delete_checkbox" ).is( ":checked" ))
							) {
								$( ".prd_import_row.import_row" ).show();
							} else {
								$( ".prd_import_row.import_row" ).hide();
							}
						}
					}
				}
			);

			$( 'input[name="custsheetselection"]' ).on(
				"change",
				function () {
					var newrequest = $( this ).val();
					if (newrequest == "new") {
						$( ".cust_spreadsheet_inputrow" ).show();
						$( "#customer_spreadsheet_container" ).hide();
						$( "#custsynctr" ).hide();
						$( ".cust_import_row" ).hide();
					} else {
						$( ".cust_spreadsheet_inputrow" ).hide();
						$( "#customer_spreadsheet_container" ).show();
						var sheetId = $( "#customer_spreadsheet" ).val();
						if (
						String( newrequest ) === "" ||
						parseInt( newrequest ) === 0 ||
						String( sheetId ) === "" ||
						parseInt( sheetId ) === 0 ||
						String( sheetId ) === "new"
						) {
							$( "#custsynctr" ).hide();
							$( ".cust_import_row" ).hide();
						} else {
							$( "#custsynctr" ).show();
							if (
							$( "#import_customer_checkbox" ).is( ":checked" ) &&
							($( "#insert_customer_checkbox" ).is( ":checked" ) ||
							$( "#update_customer_checkbox" ).is( ":checked" ) ||
							$( "#delete_customer_checkbox" ).is( ":checked" ))
							) {
								$( ".cust_import_row" ).show();
							} else {
								$( ".cust_import_row" ).hide();
							}
						}
					}
				}
			);
			$( "#event_spreadsheet" ).on(
				"change",
				function () {
					var newrequest = $( "#event_spreadsheet" ).val();
					if (newrequest == "new") {
						$( ".event_spreadsheet_inputrow" ).fadeIn();
					} else {
						$( ".event_spreadsheet_inputrow" ).fadeOut();
					}
				}
			);
			$( 'input[name="couponsheetselection"]' ).on(
				"change",
				function () {
					var newrequest = $( this ).val();
					if (newrequest == "new") {
						$( ".coupon_spreadsheet_inputrow" ).show();
						$( "#coupon_spreadsheet_container" ).hide();
						$( "#couponsynctr" ).hide();
						$( ".coupon_import_row" ).hide();
					} else {
						$( ".coupon_spreadsheet_inputrow" ).hide();
						$( "#coupon_spreadsheet_container" ).show();
						var sheetId = $( "#coupon_spreadsheet" ).val();
						if (
						String( newrequest ) === "" ||
						parseInt( newrequest ) === 0 ||
						String( sheetId ) === "" ||
						parseInt( sheetId ) === 0 ||
						String( sheetId ) === "new"
						) {
							$( "#couponsynctr" ).hide();
							$( ".coupon_import_row" ).hide();
						} else {
							$( "#couponsynctr" ).show();
							if (
							$( "#import_coupon_checkbox" ).is( ":checked" ) &&
							($( "#insert_coupon_checkbox" ).is( ":checked" ) ||
							$( "#update_coupon_checkbox" ).is( ":checked" ) ||
							$( "#delete_coupon_checkbox" ).is( ":checked" ))
							) {
								$( ".coupon_import_row" ).show();
							} else {
								$( ".coupon_import_row" ).hide();
							}
						}
					}
				}
			);
		}
	);
	$( document ).ready(
		function () {
			$( "#reset_settings" ).on(
				"click",
				function (e) {
					e.preventDefault();
					jQuery.ajax(
						{
							url: admin_ajax_object.ajaxurl,
							type: "post",
							data: "action=wpssw_reset_settings",
							beforeSend: function () {
								if (
								confirm(
									"It will unselect all spreadsheets from all settings tabs, so you need to set them up again. Are you sure you want to reset settings?"
								)
								) {
								} else {
									return false;
								}
							},
							success: function (response) {
								if (String( response ) === "successful") {
									location.reload();
								} else {
									alert( response );
								}
							},
							error: function (s) {
								alert( "Error" );
							},
						}
					);
				}
			);
			$( "#sync" ).on(
				"click",
				function (e) {
					doAjax();
				}
			);
			$( "#prodsync" ).on(
				"click",
				function (e) {
					doProductAjax();
				}
			);
			$( "#custsync" ).on(
				"click",
				function (e) {
					doCustomerAjax();
				}
			);
			$( "#couponsync" ).on(
				"click",
				function (e) {
					doCouponAjax();
				}
			);
			$( "#sync_event" ).on(
				"click",
				function (e) {
					doEventAjax();
				}
			);
			$( "#attrsync" ).on(
				"click",
				function (e) {
					doAttributeAjax();
				}
			);
			$( "#importsync" ).on(
				"click",
				function (e) {
					if (confirm( "Are you sure?" )) {
						$( "#importsyncloader" ).show();
						$( "#importsynctext" ).html( "Checking..." );
						$( "#importsynctext" ).show();
						$( this ).hide();
						doImportAjax();
					} else {
						return false;
					}
				}
			);
			$( "#importattrsync" ).on(
				"click",
				function (e) {
					if (confirm( "Are you sure?" )) {
						$( "#importattrsyncloader" ).show();
						$( "#importattrsynctext" ).html( "Checking..." );
						$( "#importattrsynctext" ).show();
						$( this ).hide();
						doImportAttributeAjax();
					} else {
						return false;
					}
				}
			);
			$( "#importsyncbtm" ).on(
				"click",
				function (e) {
					$( "#importsyncloader" ).show();
					$( "#importsynctext" ).html( "Importing..." );
					$( "#importsynctext" ).show();
					$( "#cancelsyncbtm" ).hide();
					$( this ).hide();
					doImportproductsAjax();
				}
			);
			$( "#importattrsyncbtm" ).on(
				"click",
				function (e) {
					$( "#importattrsyncloader" ).show();
					$( "#importattrsynctext" ).html( "Importing..." );
					$( "#importattrsynctext" ).show();
					$( "#cancelattrsyncbtm" ).hide();
					$( this ).hide();
					doImportattributesAjax();
				}
			);
			$( "#cancelsyncbtm" ).on(
				"click",
				function (e) {
					$( this ).hide();
					$( "#importsyncbt" ).hide();
					$( "#importsynctext" ).html( "Checking..." );
					$( "#importsynctext" ).hide();
					$( "#importsyncbtm" ).hide();
					document.getElementById( "importsync" ).style.display = "inline-block";
				}
			);
			$( "#cancelattrsyncbtm" ).on(
				"click",
				function (e) {
					$( this ).hide();
					$( "#importattrsyncbtm" ).hide();
					$( "#importattrsynctext" ).html( "Checking..." );
					$( "#importattrsynctext" ).hide();
					document.getElementById( "importattrsync" ).style.display = "inline-block";
				}
			);
			$( "#ordimportsync" ).on(
				"click",
				function (e) {
					if (confirm( "Are you sure?" )) {
						$( "#ordimportsyncloader" ).show();
						$( "#ordimportsynctext" ).html( "Checking..." );
						$( "#ordimportsynctext" ).show();
						$( this ).hide();
						doOrderAjax();
					} else {
						return false;
					}
				}
			);
			$( "#ordimportsyncbtm" ).on(
				"click",
				function (e) {
					$( "#ordimportsyncloader" ).show();
					$( "#ordimportsynctext" ).html( "Importing..." );
					$( "#ordimportsynctext" ).show();
					$( "#ordcancelsyncbtm" ).hide();
					$( this ).hide();
					doImportordersAjax();
				}
			);
			$( "#ordcancelsyncbtm" ).on(
				"click",
				function (e) {
					$( this ).hide();
					$( "#ordimportsyncbt" ).hide();
					$( "#ordimportsynctext" ).html( "Checking..." );
					$( "#ordimportsynctext" ).hide();
					$( "#ordimportsyncbtm" ).hide();
					document.getElementById( "ordimportsync" ).style.display = "inline-block";
				}
			);
			$( "#custimportsync" ).on(
				"click",
				function (e) {
					if (confirm( "Are you sure?" )) {
						$( "#custimportsyncloader" ).show();
						$( "#custimportsynctext" ).html( "Checking..." );
						$( "#custimportsynctext" ).show();
						$( this ).hide();
						doImportCustomerCountAjax();
					} else {
						return false;
					}
				}
			);
			$( "#custimportsyncbtm" ).on(
				"click",
				function (e) {
					$( "#custimportsyncloader" ).show();
					$( "#custimportsynctext" ).html( "Importing..." );
					$( "#custimportsynctext" ).show();
					$( "#custcancelsyncbtm" ).hide();
					$( this ).hide();
					doImportCustomersAjax();
				}
			);
			$( "#custcancelsyncbtm" ).on(
				"click",
				function (e) {
					$( this ).hide();
					$( "#custimportsyncbt" ).hide();
					$( "#custimportsynctext" ).html( "Checking..." );
					$( "#custimportsynctext" ).hide();
					$( "#custimportsyncbtm" ).hide();
					document.getElementById( "custimportsync" ).style.display = "inline-block";
				}
			);
			$( "#couponimportsync" ).on(
				"click",
				function (e) {
					if (confirm( "Are you sure?" )) {
						$( "#couponimportsyncloader" ).show();
						$( "#couponimportsynctext" ).html( "Checking..." );
						$( "#couponimportsynctext" ).show();
						$( this ).hide();
						doImportCouponCountAjax();
					} else {
						return false;
					}
				}
			);
			$( "#couponimportsyncbtm" ).on(
				"click",
				function (e) {
					$( "#couponimportsyncloader" ).show();
					$( "#couponimportsynctext" ).html( "Importing..." );
					$( "#couponimportsynctext" ).show();
					$( "#couponcancelsyncbtm" ).hide();
					$( this ).hide();
					doImportCouponsAjax();
				}
			);
			$( "#couponcancelsyncbtm" ).on(
				"click",
				function (e) {
					$( this ).hide();
					$( "#couponimportsyncbt" ).hide();
					$( "#couponimportsynctext" ).html( "Checking..." );
					$( "#couponimportsynctext" ).hide();
					$( "#couponimportsyncbtm" ).hide();
					document.getElementById( "couponimportsync" ).style.display =
					"inline-block";
				}
			);
			$( "#regenerate_total_graph" ).on(
				"click",
				function (e) {
					var id            = $( this ).attr( "id" );
					var chartLoaderId = $( "#" + id )
					.next()
					.attr( "id" );
					var graphType     = $( 'input[name="total_orders_graph_type"]:checked' ).val();
					addRegenerateGraph( id, chartLoaderId, graphType );
				}
			);
			$( "#regenerate_sales_graph" ).on(
				"click",
				function (e) {
					var id            = $( this ).attr( "id" );
					var chartLoaderId = $( "#" + id )
					.next()
					.attr( "id" );
					var graphType     = $( 'input[name="sales_orders_graph_type"]:checked' ).val();
					addRegenerateGraph( id, chartLoaderId, graphType );
				}
			);
			$( "#regenerate_product_graph" ).on(
				"click",
				function (e) {
					var id            = $( this ).attr( "id" );
					var chartLoaderId = $( "#" + id )
					.next()
					.attr( "id" );
					var graphType     = $( 'input[name="products_sold_graph_type"]:checked' ).val();
					addRegenerateGraph( id, chartLoaderId, graphType );
				}
			);
			$( "#regenerate_customers_graph" ).on(
				"click",
				function (e) {
					var id            = $( this ).attr( "id" );
					var chartLoaderId = $( "#" + id )
					.next()
					.attr( "id" );
					var graphType     = $(
						'input[name="total_customers_graph_type"]:checked'
					).val();
					addRegenerateGraph( id, chartLoaderId, graphType );
				}
			);
			$( "#regenerate_used_coupons_graph" ).on(
				"click",
				function (e) {
					var id            = $( this ).attr( "id" );
					var chartLoaderId = $( "#" + id )
					.next()
					.attr( "id" );
					var graphType     = $(
						'input[name="total_used_coupons_graph_type"]:checked'
					).val();
					addRegenerateGraph( id, chartLoaderId, graphType );
				}
			);
			if ($( "#sales_orders_graph" ).is( ":checked" )) {
				$( ".sales-tdclass" ).removeClass( "disabled-regenerate-graph" );
				$( "input[type=radio][name=sales_orders_graph_type]" ).prop(
					"required",
					true
				);
			} else {
				$( ".sales-tdclass" ).addClass( "disabled-regenerate-graph" );
			}
			if ($( "#total_orders_graph" ).is( ":checked" )) {
				$( ".total-tdclass" ).removeClass( "disabled-regenerate-graph" );
				$( "input[type=radio][name=total_orders_graph_type]" ).prop(
					"required",
					true
				);
			} else {
				$( ".total-tdclass" ).addClass( "disabled-regenerate-graph" );
			}
			if ($( "#products_sold_graph" ).is( ":checked" )) {
				$( ".product-tdclass" ).removeClass( "disabled-regenerate-graph" );
				$( "input[type=radio][name=products_sold_graph_type]" ).prop(
					"required",
					true
				);
			} else {
				$( ".product-tdclass" ).addClass( "disabled-regenerate-graph" );
			}
			if ($( "#total_customers_graph" ).is( ":checked" )) {
				$( ".customers-tdclass" ).removeClass( "disabled-regenerate-graph" );
				$( "input[type=radio][name=total_customers_graph_type]" ).prop(
					"required",
					true
				);
			} else {
				$( ".customers-tdclass" ).addClass( "disabled-regenerate-graph" );
			}
			if ($( "#total_used_coupons_graph" ).is( ":checked" )) {
				$( ".used-coupons-tdclass" ).removeClass( "disabled-regenerate-graph" );
				$( "input[type=radio][name=total_used_coupons_graph_type]" ).prop(
					"required",
					true
				);
			} else {
				$( ".used-coupons-tdclass" ).addClass( "disabled-regenerate-graph" );
			}
			$( 'input[name="graphsheets_list[]"][type=checkbox]' ).change(
				function () {
					var graphId   = $( this ).attr( "id" );
					var name      = $( this ).attr( "class" );
					var className = "" + name + "-tdclass";
					if ($( "#" + graphId ).is( ":checked" )) {
						$( "." + className ).removeClass( "disabled-regenerate-graph" );
						$( "input[type=radio][name=" + graphId + "_type]" ).prop(
							"required",
							true
						);
					} else {
						$( "." + className ).addClass( "disabled-regenerate-graph" );
						$( "input[type=radio][name=" + graphId + "_type]" ).prop(
							"required",
							false
						);
					}
				}
			);
			var productLimit  = 500;
			var productCount  = 0;
			var totalPrdSheet = 0;
			var syncPrdSheet  = 0;
			var nextPrdSheet  = 1;

			function doProductAjax(args) {
				var productnonce = $( "#wpssw_product_settings" ).val();
				var prdsyncAll   = $( 'input[name="prd_sync_all_checkbox"]:checked' ).val();

				var prdsyncAllFromdate = $( "#prd_sync_all_fromdate" ).val();
				var prdsyncAllTodate   = $( "#prd_sync_all_todate" ).val();

				var prdsyncAllSaleFromdate = $( "#prd_sync_all_sale_fromdate" ).val();
				var prdsyncAllSaleTodate   = $( "#prd_sync_all_sale_todate" ).val();

				var prdsyncAllLangs    = true;
				var prdsyncCustomLangs = [];

				if (
				1 === parseInt( $( "#wpssw_wpml_is_active" ).val() ) &&
				parseInt( $( "#wpssw_wpml_total_langs" ).val() ) > 0
				) {
					prdsyncAllLangs = $( "#prd_sync_lang_all" ).is( ":checked" );

					prdsyncCustomLangs = $(
						"input:checkbox[name='productlang_filter[]']:checked"
					)
					.map(
						function () {
									return $( this ).val();
						}
					)
					.get();
				}

				if (
				parseInt( prdsyncAll ) === 0 &&
				( String( prdsyncAllFromdate ) === "" || String( prdsyncAllTodate ) === "")
				) {
					alert( "From Date and To Date should not be blank." );
					return false;
				} else if ( parseInt( prdsyncAll ) === 0 && ( prdsyncAllFromdate > prdsyncAllTodate ) ) {
					alert( "From Date should not be greater than To Date." );
					return false;
				} else if (
				prdsyncAll === "sale-date-range" &&
				( String( prdsyncAllSaleFromdate ) === "" || String( prdsyncAllSaleTodate ) === "")
				) {
					alert( "Product Sale From Date and Product Sale To Date should not be blank." );
					return false;
				} else if ( prdsyncAll === "sale-date-range" && ( prdsyncAllSaleFromdate > prdsyncAllSaleTodate ) ) {
					alert( "Product Sale From Date should not be greater than Product Sale To Date." );
					return false;
				} else if (
				Boolean( prdsyncAllLangs ) === false &&
				( ! prdsyncCustomLangs || prdsyncCustomLangs.length === 0)
				) {
					alert( "Please select the language." );
					return false;
				} else {
					$( "#prodsyncloader" ).show();
					$( "#prodsynctext" ).show();
					$( "#prodsync" ).hide();
					$.ajax(
						{
							url: admin_ajax_object.ajaxurl,
							type: "post",
							data:
								"action=wpssw_get_product_count&wpssw_product_settings=" +
								productnonce +
								"&prd_sync_all_fromdate=" +
								prdsyncAllFromdate +
								"&prd_sync_all_todate=" +
								prdsyncAllTodate +
								"&prd_sync_all=" +
								prdsyncAll +
								"&prd_sync_all_langs=" +
								prdsyncAllLangs +
								"&prd_sync_custom_langs=" +
								prdsyncCustomLangs +
								"&prd_sync_all_sale_fromdate=" +
								prdsyncAllSaleFromdate +
								"&prd_sync_all_sale_todate=" +
								prdsyncAllSaleTodate,

							success: function (response) {
								obj           = JSON.parse( response );
								totalPrdSheet = Object.keys( obj ).length;
								if (totalPrdSheet > 0) {
									var sheetName     = obj[syncPrdSheet]["sheet_name"];
									var sheetSlug     = obj[syncPrdSheet]["sheet_slug"];
									var totalproducts = obj[syncPrdSheet]["totalproducts"];
									productLimit      = obj[syncPrdSheet]["productlimit"];
									if (parseInt( totalproducts ) < 2000) {
										productLimit = 200;
										if (parseInt( totalproducts ) < 200) {
											productLimit = parseInt( totalproducts );
										}
									}
									syncProductData(
										totalproducts,
										productLimit,
										prdsyncAll,
										prdsyncAllFromdate,
										prdsyncAllTodate,
										prdsyncAllLangs,
										prdsyncCustomLangs,
										sheetName,
										sheetSlug,
										prdsyncAllSaleFromdate,
										prdsyncAllSaleTodate,
									);
								} else {
									alert( "All Products are synchronize successfully." );
									displayproductsync();
									resetproductsync();
								}
							},
						}
					).fail(
						function () {
							alert( "Error" );
							displayproductsync();
							resetproductsync();
						}
					);
				}
			}

			var attributeLimit = 500;
			var attributeCount = 0;
			function doAttributeAjax(args) {
				var attributenonce = $( "#wpssw_product_settings" ).val();
				$( "#attrsyncloader" ).show();
				$( "#attrsynctext" ).show();
				$( "#attrsync" ).hide();
				$.ajax(
					{
						url: admin_ajax_object.ajaxurl,
						type: "post",
						data:
						"action=wpssw_get_attribute_count&wpssw_attribute_settings=" +
						attributenonce,
						success: function (response) {
							try {
								obj                 = JSON.parse( response );
								var totalattributes = obj.totalattributes;
								attributeLimit      = obj.attributelimit;
								if (totalattributes > 0) {
									if (parseInt( totalattributes ) < 2000) {
												attributeLimit = 200;
										if (parseInt( totalattributes ) < 200) {
											attributeLimit = parseInt( totalattributes );
										}
									}
									syncAttributeData( totalattributes, attributeLimit );
								} else {
									alert( "All Attributes are synchronize successfully" );
									displayattributesync();
								}
							} catch (e) {
								alert( response );
								displayattributesync();
							}
						},
					}
				).fail(
					function () {
						alert( "Error" );
						displayattributesync();
					}
				);
			}

			function displayattributesync() {
				$( "#attrsyncloader" ).hide();
				$( "#attrsynctext" ).hide();
				$( "#attrsynctext" ).html( "Synchronizing..." );
				document.getElementById( "attrsync" ).style.display = "inline-block";
			}

			var product_import_obj;
			var importProductLimit = 500;
			var importProductCount = 0;
			function doImportAjax(args) {
				var productnonce = $( "#wpssw_product_settings" ).val();
				$.ajax(
					{
						url: admin_ajax_object.ajaxurl,
						type: "post",
						data:
						"action=wpssw_get_product_import_count&wpssw_product_settings=" +
						productnonce,
						success: function (response) {
							if (response == "notexist") {
								alert(
									"Product ID and Product Variation ID must have 1st and 2nd columns of the spreadsheet."
								);
								displayproductimport();
							} else {
								product_import_obj      = JSON.parse( response );
								var totalimportproducts = product_import_obj.totalimportproducts;
								var msg                 = [];
								if ("insertproducts" in product_import_obj) {
									msg.push( "Insert Product - " + product_import_obj.insertproducts );
								}
								if ("updateproducts" in product_import_obj) {
									msg.push( "Update Product - " + product_import_obj.updateproducts );
								}
								if ("deleteproducts" in product_import_obj) {
									msg.push( "Delete Product - " + product_import_obj.deleteproducts );
								}
								if (msg.length === 0) {
									alert(
										"To perform the import function, make sure you have added 1 to the respective columns of the spreadsheet as per the documentation."
									);
									displayproductimport();
								} else {
									if (parseInt( totalimportproducts ) < 2000) {
										importProductLimit = 200;
										if (parseInt( totalimportproducts ) < 200) {
											importProductLimit = parseInt( totalimportproducts );
										}
									}
									document.getElementById( "importsyncbtm" ).style.display =
									"inline-block";
									document.getElementById( "cancelsyncbtm" ).style.display =
									"inline-block";
									$( "#importsyncloader" ).hide();
									$( "#importsynctext" ).html( "<b>" + msg.join( "<br>" ) + "</b>" );
								}
							}
						},
						error: function (s) {
							alert( "Error" );
							displayproductimport();
						},
					}
				).fail(
					function () {
						alert( "Error" );
						displayproductimport();
					}
				);
			}

			/*Attribute Import*/
			var attribute_import_obj;
			var importAttributeLimit = 500;
			var importAttributeCount = 0;
			function doImportAttributeAjax(args) {
				var productnonce = $( "#wpssw_product_settings" ).val();
				$.ajax(
					{
						url: admin_ajax_object.ajaxurl,
						type: "post",
						data:
						"action=wpssw_get_attribute_import_count&wpssw_product_settings=" +
						productnonce,
						success: function (response) {
							if (response == "notexist") {
								alert( "Attribute ID must have 1st column of the spreadsheet." );
								displayprdattrimport();
							} else {
								attribute_import_obj      = JSON.parse( response );
								var totalimportattributes =
								attribute_import_obj.totalimportattributes;
								var msg                   = [];
								if ("insertattributes" in attribute_import_obj) {
									msg.push(
										"Insert Attribute - " + attribute_import_obj.insertattributes
									);
								}
								if ("updateattributes" in attribute_import_obj) {
									msg.push(
										"Update Attribute - " + attribute_import_obj.updateattributes
									);
								}
								if ("deleteattributes" in attribute_import_obj) {
									msg.push(
										"Delete Attribute - " + attribute_import_obj.deleteattributes
									);
								}
								if (msg.length === 0) {
									alert(
										"To perform the import function, make sure you have added 1 to the respective columns of the spreadsheet as per the documentation."
									);
									displayprdattrimport();
								} else {
									if (parseInt( totalimportattributes ) < 2000) {
										importAttributeLimit = 200;
										if (parseInt( totalimportattributes ) < 200) {
											importAttributeLimit = parseInt( totalimportattributes );
										}
									}
									document.getElementById( "importattrsyncbtm" ).style.display =
									"inline-block";
									document.getElementById( "cancelattrsyncbtm" ).style.display =
									"inline-block";
									$( "#importattrsyncloader" ).hide();
									$( "#importattrsynctext" ).html( "<b>" + msg.join( "<br>" ) + "</b>" );
								}
							}
						},
						error: function (s) {
							alert( "Error" );
							displayprdattrimport();
						},
					}
				).fail(
					function () {
						alert( "Error" );
						displayprdattrimport();
					}
				);
			}
			function doImportattributesAjax() {
				var attributenonce        = $( "#wpssw_product_settings" ).val();
				var totalimportattributes = attribute_import_obj.totalimportattributes;
				if (totalimportattributes > importAttributeCount) {
					if (parseInt( totalimportattributes ) < 2000) {
						importAttributeLimit = 200;
						if (parseInt( totalimportattributes ) < 200) {
							importAttributeLimit = parseInt( totalimportattributes );
						}
					} else {
						importAttributeLimit = 500;
					}
					importAttributeCount = importAttributeCount + importAttributeLimit;
				} else if (totalimportattributes < importAttributeCount) {
					importAttributeCount = importAttributeCount + importAttributeLimit;
				}
				$.ajax(
					{
						url: admin_ajax_object.ajaxurl,
						type: "post",
						data:
						"action=wpssw_attribute_import&importattributelimit=" +
						importAttributeLimit +
						"&importattributecount=" +
						importAttributeCount +
						"&wpssw_attribute_settings=" +
						attributenonce,
						success: function (response) {
							if (response == "successful") {
								if (totalimportattributes <= importAttributeCount) {
									alert( "All attributes are imported successfully" );
									displayprdattrimport();
									resetprdattrimportparams();
								}
							} else if (response == "addattributename") {
								alert( "Please add attribute name." );
							} else if (response == "addattributenamecolumn") {
								alert(
									"Please enable attribute Name header and add attribute name to insert attribute"
								);
								$( "html, body" ).animate(
									{
										scrollTop: $( "#woo-attribute-sortable" ).offset().top - 140,
									},
									1200
								);
								setTimeout(
									function () {
										$( "#woo-attribute-sortable" ).css( "border", "1px solid #ff5859" );
									},
									1000
								);
								$( "#woo-attribute-sortable" ).focus();
							} else if (response == "attributeIdexist") {
								alert(
									"Please make sure you have removed attribute Id from 1st column of spreadsheet. For more details check FAQ no. 9"
								);
							} else if (response == "addattributeId") {
								alert(
									"Please make sure you have added attribute Id in 1st column of the spreadsheet."
								);
							} else if (response == "attributenamenotexist") {
								alert( "The attribute does not exists." );
							} else if (response == "attributenameexist") {
								alert(
									"Attribute with similar name already exists so please use different attribute name to insert a new attribute"
								);
							} else {
								alert(
									"Your Google Sheets API limit has been reached. Please take a look at our FAQ."
								);
							}
							if (response != "successful") {
								displayprdattrimport();
								resetprdattrimportparams();
								return false;
							}
						},
						complete: function () {
							if (Object.keys( attribute_import_obj ).length > 0) {
								var totalimportattributes =
								attribute_import_obj.totalimportattributes;
								if (parseInt( totalimportattributes ) < 2000) {
									importAttributeLimit = 200;
									if (parseInt( totalimportattributes ) < 200) {
										importAttributeLimit = parseInt( totalimportattributes );
									}
								} else {
									importAttributeLimit = 500;
								}
								if (totalimportattributes > importAttributeCount) {
									setTimeout(
										function () {
											doImportattributesAjax();
										},
										2000
									);
								}
							}
						},
					}
				).fail(
					function () {
						alert( "Error" );
						displayprdattrimport();
						resetprdattrimportparams();
					}
				);
			}

			function displayprdattrimport() {
				$( "#importattrsyncloader" ).hide();
				$( "#importattrsynctext" ).hide();
				$( "#importattrsynctext" ).html( "Checking..." );
				document.getElementById( "importattrsync" ).style.display = "inline-block";
			}
			function resetprdattrimportparams() {
				importAttributeLimit = 500;
				importAttributeCount = 0;
				attribute_import_obj = {};
			}

			function doImportproductsAjax() {
				var productnonce        = $( "#wpssw_product_settings" ).val();
				var totalimportproducts = product_import_obj.totalimportproducts;
				if (totalimportproducts > importProductCount) {
					if (parseInt( totalimportproducts ) < 2000) {
						importProductLimit = 200;
						if (parseInt( totalimportproducts ) < 200) {
							importProductLimit = parseInt( totalimportproducts );
						}
					} else {
						importProductLimit = 500;
					}
					importProductCount = importProductCount + importProductLimit;
				} else if (totalimportproducts < importProductCount) {
					importProductCount = importProductCount + importProductLimit;
				}
				$.ajax(
					{
						url: admin_ajax_object.ajaxurl,
						type: "post",
						data:
						"action=wpssw_product_import&importproductlimit=" +
						importProductLimit +
						"&importproductcount=" +
						importProductCount +
						"&wpssw_product_settings=" +
						productnonce,
						success: function (response) {
							if (response == "successful") {
								if (totalimportproducts <= importProductCount) {
									importProductLimit = 500;
									importProductCount = 0;
									product_import_obj = {};
									alert( "All products are imported successfully" );
									displayproductimport();
								}
							} else if (response == "addproductname") {
								alert( "Please add product name to insert product" );
							} else if (response == "addproductnamecolumn") {
								alert(
									"Please enable Product Name header and add product name to insert product"
								);
								$( "html, body" ).animate(
									{
										scrollTop: $( "#woo-product-sortable" ).offset().top - 140,
									},
									1200
								);
								setTimeout(
									function () {
										$( "#woo-product-sortable" ).css( "border", "1px solid #ff5859" );
									},
									1000
								);
								$( "#woo-product-sortable" ).focus();
							} else if (response == "productIdexist") {
								alert(
									"Please make sure you have removed Product Id from 1st column of spreadsheet. For more details check FAQ no. 9"
								);
							} else if (response == "addproductId") {
								alert(
									"Please make sure you have added Product Id in 1st column of spreadsheet."
								);
							} else if (response == "productnameexist") {
								alert(
									"Product with similar name is already exists so please use different Product Name to insert new product"
								);
							} else if (response == "productnotexists") {
								alert( "The product does not exists." );
							} else {
								alert(
									"Your Google Sheets API limit has been reached. Please take a look at our FAQ."
								);
							}
							if (response != "successful") {
								displayproductimport();
								resetproductimportparams();
								return false;
							}
						},
						complete: function () {
							if (Object.keys( product_import_obj ).length > 0) {
								var totalimportproducts = product_import_obj.totalimportproducts;
								if (parseInt( totalimportproducts ) < 2000) {
									importProductLimit = 200;
									if (parseInt( totalimportproducts ) < 200) {
										importProductLimit = parseInt( totalimportproducts );
									}
								} else {
									importProductLimit = 500;
								}
								if (totalimportproducts > importProductCount) {
									setTimeout(
										function () {
											doImportproductsAjax();
										},
										2000
									);
								}
							}
						},
					}
				).fail(
					function () {
						alert( "Error" );
						displayproductimport();
						resetproductimportparams();
					}
				);
			}

			function displayproductimport() {
				$( "#importsyncloader" ).hide();
				$( "#importsynctext" ).hide();
				$( "#importsynctext" ).html( "Checking..." );
				document.getElementById( "importsync" ).style.display = "inline-block";
			}
			function resetproductimportparams() {
				importProductLimit = 500;
				importProductCount = 0;
				product_import_obj = {};
			}
			var order_import_obj;
			var order_import_sheet = 0;
			var importOrderLimit   = 200;
			var importOrderCount   = 0;

			var totalImportSheet = 0;
			var nextImportSheet  = 1;

			function doOrderAjax(args) {
				$.ajax(
					{
						url: admin_ajax_object.ajaxurl,
						type: "post",
						data: "action=wpssw_get_order_import_count",
						success: function (response) {
							if (String( response ) === "spreadsheetnotexist") {
								alert( "Please save your settings first and try again." );
								displayimportorder();
								$( "html, body" ).animate(
									{
										scrollTop: $( "html, body" ).get( 0 ).scrollHeight,
									},
									2000
								);
								return false;
							} else if (String( response ) === "sheetnotexist") {
								alert(
									"Selected Order status sheet is not present in your spreadsheet so to import orders first save your settings and try again."
								);
								displayimportorder();
								$( "html, body" ).animate(
									{
										scrollTop: $( "html, body" ).get( 0 ).scrollHeight,
									},
									2000
								);
								return false;
							}

							order_import_obj = JSON.parse( response );
							totalImportSheet = Object.keys( order_import_obj ).length;
							var msg          = [];
							if (totalImportSheet > 0) {
								$.each(
									order_import_obj,
									function (key, value) {
										var sheetName = value["sheet_name"];

										if (
										"insertorders" in value ||
										"updateorders" in value ||
										"deleteorders" in value
										) {
											msg.push( sheetName );
										}
										if ("insertorders" in value) {
											msg.push( "Insert Order - " + value.insertorders );
										}
										if ("updateorders" in value) {
											msg.push( "Update Order - " + value.updateorders );
										}
										if ("deleteorders" in value) {
											msg.push( "Delete Order - " + value.deleteorders );
										}
									}
								);
								if (msg.length === 0) {
										alert(
											"To perform the import function, make sure you have added 1 to the respective columns of the spreadsheet as per the documentation."
										);
										displayimportorder();
								} else {
									document.getElementById( "ordimportsyncbtm" ).style.display =
									"inline-block";
									document.getElementById( "ordcancelsyncbtm" ).style.display =
									"inline-block";
									$( "#ordimportsyncloader" ).hide();
									$( "#ordimportsynctext" ).html( "<b>" + msg.join( "<br>" ) + "</b>" );
								}
							} else {
								alert(
									"To perform the import function, make sure you have added 1 to the respective columns of the spreadsheet as per the documentation."
								);
								displayimportorder();
							}
						},
						error: function (s) {
							alert( "Error" );
							displayimportorder();
						},
					}
				).fail(
					function () {
						alert( "Error" );
						displayimportorder();
					}
				);
			}

			function doImportordersAjax() {
				totalImportSheet = Object.keys( order_import_obj ).length;
				if (totalImportSheet > 0) {
					var sheetName = order_import_obj[order_import_sheet]["sheet_name"];
					importOrderData( sheetName );
				} else {
					alert( "All Orders are imported successfully" );
					displayimportorder();
				}
			}

			function importOrderData(sheetName) {
				var wpsswGeneralSettings = $( "#wpssw_general_settings" ).val();
				var totalimportorders    =
				order_import_obj[order_import_sheet]["totalimportorders"];

				if (totalimportorders > importOrderCount) {
					if (parseInt( totalimportorders ) < 2000) {
						importOrderLimit = 100;
						if (parseInt( totalimportorders ) < 100) {
							importOrderLimit = parseInt( totalimportorders );
						}
					} else {
						importOrderLimit = 200;
					}
					importOrderCount = importOrderCount + importOrderLimit;
				} else if (
				totalimportorders < importOrderCount &&
				parseInt( nextImportSheet ) === 1 &&
				totalImportSheet != order_import_sheet + 1
				) {
						importOrderCount = importOrderCount + importOrderLimit;
						nextImportSheet  = 0;
				}

				$.ajax(
					{
						url: admin_ajax_object.ajaxurl,
						type: "post",
						data:
						"action=wpssw_order_import&sheetname=" +
						sheetName +
						"&wpssw_general_settings=" +
						wpsswGeneralSettings +
						"&importorderlimit=" +
						importOrderLimit +
						"&importordercount=" +
						importOrderCount,
						success: function (response) {
							if (parseInt( totalImportSheet ) === order_import_sheet + 1) {
								if (totalimportorders <= importOrderCount) {
									if (String( response ) === "successful") {
										alert( "All Orders are imported successfully" );
										displayimportorder();
										resetimportorderparams();
									}
								}
								if (response == "ordernotexists") {
									alert( "This order is not exists." );
								} else if (response == "addorderId") {
									alert( "Please add order id to update order." );
								} else if (response == "orderintrash") {
									alert(
										"You can’t edit this order because it is in the Trash. Please restore it and try again."
									);
								} else if (response == "addproductnamecolumn") {
									alert(
										"Please enable Product name(QTY)(SKU) header and add product name to insert order."
									);
								} else if (response == "addproductidcolumn") {
									alert(
										"Please enable Product ID header and add product id to insert order."
									);
								} else if (response == "addproductname") {
									alert( "Please add product name to insert order." );
								} else if (response == "addproductid") {
									alert( "Please add product id to insert order." );
								} else if (response == "orderIdexist") {
									alert(
										"Please make sure you have removed Order Id from 1st column of spreadsheet."
									);
								} else if (response !== "successful") {
									alert(
										"Your Google Sheets API limit has been reached. Please take a look at our FAQ."
									);
								}

								if (response !== "successful") {
									displayimportorder();
									resetimportorderparams();
									return false;
								}
							}
						},
						complete: function () {
							if (0 !== Object.keys( order_import_obj ).length) {
								var sheetName         = order_import_obj[order_import_sheet]["sheet_name"];
								var totalimportorders =
								order_import_obj[order_import_sheet]["totalimportorders"];

								if (totalimportorders > importOrderCount) {
									setTimeout(
										function () {
											importOrderData( sheetName );
										},
										2000
									);
								} else if (
								totalimportorders < importOrderCount &&
								parseInt( nextImportSheet ) === 1 &&
								totalImportSheet != order_import_sheet + 1
								) {
										setTimeout(
											function () {
													importOrderData( sheetName );
											},
											2000
										);
								} else {
									if (totalImportSheet > order_import_sheet + 1) {
										order_import_sheet = order_import_sheet + 1;
										importOrderCount   = 0;
										nextImportSheet    = 1;
										var sheetName      =
										order_import_obj[order_import_sheet]["sheet_name"];

										setTimeout(
											function () {
												importOrderData( sheetName );
											},
											2000
										);
									}
								}
							}
						},
					}
				).fail(
					function () {
						alert( "Error" );
						displayimportorder();
						resetimportorderparams();
					}
				);
			}
			function displayimportorder() {
				$( "#ordimportsyncloader" ).hide();
				$( "#ordimportsynctext" ).hide();
				$( "#ordimportsynctext" ).html( "Checking..." );
				document.getElementById( "ordimportsync" ).style.display = "inline-block";
			}
			function resetimportorderparams() {
				order_import_obj   = {};
				order_import_sheet = 0;
				totalImportSheet   = 0;
				nextImportSheet    = 1;
				importOrderLimit   = 200;
				importOrderCount   = 0;
			}

			var customerLimit = 500;
			var customerCount = 0;
			function doCustomerAjax(args) {
				var customernonce       = $( "#wpssw_customer_settings" ).val();
				var custsyncAll         = $( 'input[name="cust_sync_all_checkbox"]:checked' ).val();
				var custsyncAllFromdate = $( "#cust_sync_all_fromdate" ).val();
				var custsyncAllTodate   = $( "#cust_sync_all_todate" ).val();
				if (
				parseInt( custsyncAll ) === 0 &&
				(String( custsyncAllFromdate ) === "" || String( custsyncAllTodate ) === "")
				) {
					alert( "From Date and To Date should not be blank." );
					return false;
				} else if (custsyncAllFromdate > custsyncAllTodate) {
					alert( "From Date should not be greater than To Date." );
					return false;
				} else {
					$( "#custsyncloader" ).show();
					$( "#custsynctext" ).show();
					$( "#custsync" ).hide();
					$.ajax(
						{
							url: admin_ajax_object.ajaxurl,
							type: "post",
							data:
							"action=wpssw_get_customer_count&wpssw_customer_settings=" +
							customernonce +
							"&cust_sync_all_fromdate=" +
							custsyncAllFromdate +
							"&cust_sync_all_todate=" +
							custsyncAllTodate +
							"&cust_sync_all=" +
							custsyncAll,
							success: function (response) {
								try {
									if (String( response ) === "notfound") {
										alert( "No customers found for synchronization." );
										displaycustomersync();
									} else if (String( response ) === "error") {
										alert( "Sorry, your nonce did not verify." );
										displaycustomersync();
									} else {
										obj                = JSON.parse( response );
										var totalcustomers = obj.totalcustomers;
										customerLimit      = obj.customerlimit;
										if (totalcustomers > 0) {
											if (parseInt( totalcustomers ) < 2000) {
												customerLimit = 200;
												if (parseInt( totalcustomers ) < 200) {
													customerLimit = parseInt( totalcustomers );
												}
											}
											syncCustomerData(
												totalcustomers,
												customerLimit,
												custsyncAll,
												custsyncAllFromdate,
												custsyncAllTodate
											);
										} else {
											alert( "All Customers data are synchronize successfully" );
											displaycustomersync();
										}
									}
								} catch (e) {
									alert( response );
									displaycustomersync();
								}
							},
						}
					).fail(
						function () {
								alert( "Error" );
								displaycustomersync();
						}
					);
				}
			}
			var customer_import_obj;
			var importCustomerLimit = 500;
			var importCustomerCount = 0;
			function doImportCustomerCountAjax(args) {
				var customernonce = $( "#wpssw_customer_settings" ).val();
				$.ajax(
					{
						url: admin_ajax_object.ajaxurl,
						type: "post",
						data:
						"action=wpssw_get_customer_import_count&wpssw_customer_settings=" +
						customernonce,
						success: function (response) {
							customer_import_obj = JSON.parse( response );
							var msg             = [];
							if ("insertcustomers" in customer_import_obj) {
								msg.push(
									"Insert Customer - " + customer_import_obj.insertcustomers
								);
							}
							if ("updatecustomers" in customer_import_obj) {
								msg.push(
									"Update Customer - " + customer_import_obj.updatecustomers
								);
							}
							if ("deletecustomers" in customer_import_obj) {
								msg.push(
									"Delete Customer - " + customer_import_obj.deletecustomers
								);
							}
							if (msg.length === 0) {
								alert(
									"To perform the import function, make sure you have added 1 to the respective columns of the spreadsheet as per the documentation."
								);
								displaycustomerimport();
							} else {
								document.getElementById( "custimportsyncbtm" ).style.display =
								"inline-block";
								document.getElementById( "custcancelsyncbtm" ).style.display =
								"inline-block";
								$( "#custimportsyncloader" ).hide();
								$( "#custimportsynctext" ).html( "<b>" + msg.join( "<br>" ) + "</b>" );
							}
						},
						error: function (s) {
							alert( "Error" );
							displaycustomerimport();
						},
					}
				).fail(
					function () {
						alert( "Error" );
						displaycustomerimport();
					}
				);
			}
			function doImportCustomersAjax() {
				var customernonce        = $( "#wpssw_customer_settings" ).val();
				var totalimportcustomers = customer_import_obj.totalimportcustomers;
				if (totalimportcustomers > importCustomerCount) {
					if (parseInt( totalimportcustomers ) < 2000) {
						importCustomerLimit = 200;
						if (parseInt( totalimportcustomers ) < 200) {
							importCustomerLimit = parseInt( totalimportcustomers );
						}
					} else {
						importCustomerLimit = 500;
					}
					importCustomerCount = importCustomerCount + importCustomerLimit;
				} else if (totalimportcustomers < importCustomerCount) {
					importCustomerCount = importCustomerCount + importCustomerLimit;
				}
				$.ajax(
					{
						url: admin_ajax_object.ajaxurl,
						type: "post",
						data:
						"action=wpssw_customer_import&importcustomerlimit=" +
						importCustomerLimit +
						"&importcustomercount=" +
						importCustomerCount +
						"&wpssw_customer_settings=" +
						customernonce,
						success: function (response) {
							if (response == "successful") {
								if (totalimportcustomers <= importCustomerCount) {
									alert( "All Customers are imported successfully" );
									displaycustomerimport();
									resetcustomerimportparams();
								}
							} else if (response == "addcustomername") {
								alert( "Please add customer username to insert customer" );
								customer_import_obj = {};
								return false;
							} else if (response == "addcustomernamecolumn") {
								alert(
									"Please enable Customer Username header and add customer username to insert customer"
								);
								$( "html, body" ).animate(
									{
										scrollTop: $( "#woo-customer-sortable" ).offset().top - 140,
									},
									1200
								);
								setTimeout(
									function () {
										$( "#woo-customer-sortable" ).css( "border", "1px solid #ff5859" );
									},
									1000
								);
								$( "#woo-customer-sortable" ).focus();
							} else if (response == "addcustomeremail") {
								alert( "Please add customer email to insert customer" );
							} else if (response == "addcustomeremailcolumn") {
								alert(
									"Please enable Customer EmailID header and add customer email to insert customer"
								);
								$( "html, body" ).animate(
									{
										scrollTop: $( "#woo-customer-sortable" ).offset().top - 140,
									},
									1200
								);
								setTimeout(
									function () {
										$( "#woo-customer-sortable" ).css( "border", "1px solid #ff5859" );
									},
									1000
								);
								$( "#woo-customer-sortable" ).focus();
							} else if (response == "customerIdexist") {
								alert(
									"Please make sure you have removed Customer Id from 1st column of spreadsheet. For more details check FAQ no. 9"
								);
							} else if (response == "addcustomerId") {
								alert(
									"Please make sure you have added Customer Id in 1st column of spreadsheet."
								);
							} else if (response == "customernameexist") {
								alert(
									"This Username is already registered so please use different Username to insert new customer"
								);
							} else if (response == "customernotexists") {
								alert( "The customer is not exists." );
							} else if (response == "customeremailexist") {
								alert(
									"This EmailID is already registered so please use different EmailID to insert new customer."
								);
							} else if (response == "addcustomernickname") {
								alert( "Please enter a nickname." );
							} else {
								alert(
									"Your Google Sheets API limit has been reached. Please take a look at our FAQ."
								);
							}

							if (response != "successful") {
								displaycustomerimport();
								resetcustomerimportparams();
								return false;
							}
						},
						complete: function () {
							if (Object.keys( customer_import_obj ).length > 0) {
								var totalimportcustomers = customer_import_obj.totalimportcustomers;
								if (parseInt( totalimportcustomers ) < 2000) {
									importCustomerLimit = 200;
									if (parseInt( totalimportcustomers ) < 200) {
										importCustomerLimit = parseInt( totalimportcustomers );
									}
								} else {
									importCustomerLimit = 500;
								}
								if (totalimportcustomers > importCustomerCount) {
									setTimeout(
										function () {
											doImportCustomersAjax();
										},
										2000
									);
								}
							}
						},
					}
				).fail(
					function () {
						alert( "Error" );
						displaycustomerimport();
						resetcustomerimportparams();
					}
				);
			}

			function displaycustomerimport() {
				$( "#custimportsyncloader" ).hide();
				$( "#custimportsynctext" ).hide();
				$( "#custimportsynctext" ).html( "Checking..." );
				document.getElementById( "custimportsync" ).style.display = "inline-block";
			}
			function resetcustomerimportparams() {
				importCustomerLimit = 100;
				importCustomerCount = 0;
				customer_import_obj = {};
			}

			var coupon_import_obj;
			var importCouponLimit = 500;
			var importCouponCount = 0;
			function doImportCouponCountAjax(args) {
				var couponnonce = $( "#wpssw_coupon_settings" ).val();
				$.ajax(
					{
						url: admin_ajax_object.ajaxurl,
						type: "post",
						data:
						"action=wpssw_get_coupon_import_count&wpssw_coupon_settings=" +
						couponnonce,
						success: function (response) {
							coupon_import_obj = JSON.parse( response );
							var msg           = [];
							if ("insertcoupons" in coupon_import_obj) {
								msg.push( "Insert Coupon - " + coupon_import_obj.insertcoupons );
							}
							if ("updatecoupons" in coupon_import_obj) {
								msg.push( "Update Coupon - " + coupon_import_obj.updatecoupons );
							}
							if ("deletecoupons" in coupon_import_obj) {
								msg.push( "Delete Coupon - " + coupon_import_obj.deletecoupons );
							}
							if (msg.length === 0) {
								alert(
									"To perform the import function, make sure you have added 1 to the respective columns of the spreadsheet as per the documentation."
								);
								displaycouponimport();
							} else {
								document.getElementById( "couponimportsyncbtm" ).style.display =
								"inline-block";
								document.getElementById( "couponcancelsyncbtm" ).style.display =
								"inline-block";
								$( "#couponimportsyncloader" ).hide();
								$( "#couponimportsynctext" ).html( "<b>" + msg.join( "<br>" ) + "</b>" );
							}
						},
						error: function (s) {
							alert( "Error" );
							displaycouponimport();
						},
					}
				).fail(
					function () {
						alert( "Error" );
						displaycouponimport();
					}
				);
			}
			function doImportCouponsAjax() {
				var couponnonce        = $( "#wpssw_coupon_settings" ).val();
				var totalimportcoupons = coupon_import_obj.totalimportcoupons;
				if (totalimportcoupons > importCouponCount) {
					if (parseInt( totalimportcoupons ) < 2000) {
						importCouponLimit = 200;
						if (parseInt( totalimportcoupons ) < 200) {
							importCouponLimit = parseInt( totalimportcoupons );
						}
					} else {
						importCouponLimit = 500;
					}
					importCouponCount = importCouponCount + importCouponLimit;
				} else if (totalimportcoupons < importCouponCount) {
					importCouponCount = importCouponCount + importCouponLimit;
				}

				$.ajax(
					{
						url: admin_ajax_object.ajaxurl,
						type: "post",
						data:
						"action=wpssw_coupon_import&importcouponlimit=" +
						importCouponLimit +
						"&importcouponcount=" +
						importCouponCount +
						"&wpssw_coupon_settings=" +
						couponnonce,
						success: function (response) {
							if (response == "successful") {
								if (totalimportcoupons <= importCouponCount) {
									alert( "All Coupons are imported successfully" );
									displaycouponimport();
									resetcouponimportparams();
								}
							} else if (response == "addcouponcode") {
								alert( "Please add coupon code to insert coupon" );
							} else if (response == "addcouponcodecolumn") {
								alert(
									"Please enable Coupon Code header and add coupon code to insert coupon"
								);
								$( "html, body" ).animate(
									{
										scrollTop: $( "#woo-coupon-sortable" ).offset().top - 140,
									},
									1200
								);
								setTimeout(
									function () {
										$( "#woo-coupon-sortable" ).css( "border", "1px solid #ff5859" );
									},
									1000
								);
								$( "#woo-coupon-sortable" ).focus();
							} else if (response == "couponIdexist") {
								alert(
									"Please make sure you have removed Coupon Id from 1st column of spreadsheet. For more details check FAQ no. 9"
								);
							} else if (response == "addcouponId") {
								alert(
									"Please make sure you have added Coupon Id in 1st column of spreadsheet."
								);
							} else if (response == "couponnotexists") {
								alert( "The coupon is not exists." );
							} else {
								alert(
									"Your Google Sheets API limit has been reached. Please take a look at our FAQ."
								);
							}

							if (response != "successful") {
								displaycouponimport();
								resetcouponimportparams();
								return false;
							}
						},
						complete: function () {
							if (Object.keys( coupon_import_obj ).length > 0) {
								var totalimportcoupons = coupon_import_obj.totalimportcoupons;
								if (parseInt( totalimportcoupons ) < 2000) {
									importCouponLimit = 200;
									if (parseInt( totalimportcoupons ) < 200) {
										importCouponLimit = parseInt( totalimportcoupons );
									}
								} else {
									importCouponLimit = 500;
								}
								if (totalimportcoupons > importCouponCount) {
									setTimeout(
										function () {
											doImportCouponsAjax();
										},
										2000
									);
								}
							}
						},
					}
				).fail(
					function () {
						alert( "Error" );
						displaycouponimport();
						resetcouponimportparams();
					}
				);
			}
			function displaycouponimport() {
				$( "#couponimportsyncloader" ).hide();
				$( "#couponimportsynctext" ).hide();
				$( "#couponimportsynctext" ).html( "Checking..." );
				document.getElementById( "couponimportsync" ).style.display =
				"inline-block";
			}
			function resetcouponimportparams() {
				importCouponLimit = 500;
				importCouponCount = 0;
				coupon_import_obj = {};
			}
			var couponLimit = 500;
			var couponCount = 0;
			function doCouponAjax(args) {
				var couponnonce           = $( "#wpssw_coupon_settings" ).val();
				var couponsyncAll         = $(
					'input[name="coupon_sync_all_checkbox"]:checked'
				).val();
				var couponsyncAllFromdate = $( "#coupon_sync_all_fromdate" ).val();
				var couponsyncAllTodate   = $( "#coupon_sync_all_todate" ).val();
				if (
				parseInt( couponsyncAll ) === 0 &&
				(String( couponsyncAllFromdate ) === "" ||
				String( couponsyncAllTodate ) === "")
				) {
					alert( "From Date and To Date should not be blank." );
					return false;
				} else if (couponsyncAllFromdate > couponsyncAllTodate) {
					alert( "From Date should not be greater than To Date." );
					return false;
				} else {
					$( "#couponsyncloader" ).show();
					$( "#couponsynctext" ).show();
					$( "#couponsync" ).hide();

					$.ajax(
						{
							url: admin_ajax_object.ajaxurl,
							type: "post",
							data:
							"action=wpssw_get_coupon_count&wpssw_coupon_settings=" +
							couponnonce +
							"&coupon_sync_all_fromdate=" +
							couponsyncAllFromdate +
							"&coupon_sync_all_todate=" +
							couponsyncAllTodate +
							"&coupon_sync_all=" +
							couponsyncAll,
							success: function (response) {
								try {
									obj              = JSON.parse( response );
									var totalcoupons = obj.totalcoupons;
									couponLimit      = obj.couponlimit;
									if (totalcoupons > 0) {
										if (parseInt( totalcoupons ) < 2000) {
											couponLimit = 200;
											if (parseInt( totalcoupons ) < 200) {
												couponLimit = parseInt( totalcoupons );
											}
										}
										syncCouponData(
											totalcoupons,
											couponLimit,
											couponsyncAll,
											couponsyncAllFromdate,
											couponsyncAllTodate
										);
									} else {
										alert( "All Coupons data are synchronize successfully" );
										displaycouponsync();
									}
								} catch (e) {
									alert( response );
									displaycouponsync();
								}
							},
						}
					).fail(
						function () {
							alert( "Error" );
							displaycouponsync();
						}
					);
				}
			}

			function displaycouponsync() {
				$( "#couponsyncloader" ).hide();
				$( "#couponsynctext" ).hide();
				$( "#couponsynctext" ).html( "Synchronizing..." );
				document.getElementById( "couponsync" ).style.display = "inline-block";
			}
			var totalSheet = 0;
			var syncSheet  = 0;
			var orderLimit = 500;
			var orderCount = 0;
			var nextSheet  = 1;
			var obj;
			function doAjax(args) {
				var wpsswGeneralSettings = $( "#wpssw_general_settings" ).val();
				var syncAll              = $( 'input[name="sync_range"]:checked' ).val();
				var syncAllFromdate      = $( "#sync_all_fromdate" ).val();
				var syncAllTodate        = $( "#sync_all_todate" ).val();
				if (
				parseInt( syncAll ) === 0 &&
				(String( syncAllFromdate ) === "" || String( syncAllTodate ) === "")
				) {
					alert( "From Date and To Date should not be blank." );
					return false;
				} else if (syncAllFromdate > syncAllTodate) {
					alert( "From Date should not be greater than To Date." );
					return false;
				} else {
					$( "#syncloader" ).show();
					$( "#synctext" ).show();
					$( "#sync" ).hide();
					$.ajax(
						{
							url: admin_ajax_object.ajaxurl,
							type: "post",
							data:
							"action=wpssw_get_orders_count&wpssw_general_settings=" +
							wpsswGeneralSettings +
							"&sync_all_fromdate=" +
							syncAllFromdate +
							"&sync_all_todate=" +
							syncAllTodate +
							"&sync_all=" +
							syncAll,
							success: function (response) {
								if (String( response ) === "error") {
									alert( "Sorry, your nonce did not verify." );
									displayordersync();
									resetordersync();
								} else if (String( response ) === "spreadsheetnotexist") {
									alert( "Please save your settings first and try again." );
									displayordersync();
									resetordersync();
									$( "html, body" ).animate(
										{
											scrollTop: $( "html, body" ).get( 0 ).scrollHeight,
										},
										2000
									);
									return false;
								} else if (String( response ) === "sheetnotexist") {
									alert(
										"Selected Order status sheet is not present in your spreadsheet so to sync orders first save your settings and try again."
									);
									displayordersync();
									resetordersync();
									$( "html, body" ).animate(
										{
											scrollTop: $( "html, body" ).get( 0 ).scrollHeight,
										},
										2000
									);
									return false;
								} else {
									obj        = JSON.parse( response );
									totalSheet = Object.keys( obj ).length;
									if (totalSheet > 0) {
										var sheetName   = obj[syncSheet]["sheet_name"];
										var sheetSlug   = obj[syncSheet]["sheet_slug"];
										var totalOrders = obj[syncSheet]["totalorders"];
										orderLimit      = obj[syncSheet]["orderlimit"];
										if (parseInt( totalOrders ) < 2000) {
											orderLimit = 200;
											if (parseInt( totalOrders ) < 200) {
												orderLimit = parseInt( totalOrders );
											}
										}
										syncData(
											sheetName,
											sheetSlug,
											totalOrders,
											orderLimit,
											syncAll,
											syncAllFromdate,
											syncAllTodate
										);
									} else {
										alert( "All Orders are synchronize successfully" );
										displayordersync();
										resetordersync();
									}
								}
							},
						}
					).fail(
						function () {
							alert( "Error" );
							displayordersync();
							resetordersync();
						}
					);
				}
			}
			function syncData(
			sheetName,
			sheetSlug,
			totalOrders,
			orderLimit,
			syncAll,
			syncAllFromdate,
			syncAllTodate
			) {
				if (parseInt( totalOrders ) < 2000) {
					orderLimit = 200;
					if (parseInt( totalOrders ) < 200) {
						orderLimit = parseInt( totalOrders );
					}
				}
				if (totalOrders > orderCount) {
					orderCount = orderCount + orderLimit;
				} else if (
				totalOrders < orderCount &&
				parseInt( nextSheet ) === 1 &&
				totalSheet != syncSheet + 1
				) {
						orderCount = orderCount + orderLimit;
						nextSheet  = 0;
				}
				if (totalOrders > orderLimit && orderCount < totalOrders) {
					$( "#synctext" ).html(
						"Synchronizing : " +
						orderCount +
						" / " +
						totalOrders +
						" " +
						sheetName
					);
				} else {
					$( "#synctext" ).html(
						"Synchronizing : " +
						totalOrders +
						" / " +
						totalOrders +
						" " +
						sheetName
					);
				}
				var sync_nonce_token;
				sync_nonce_token = admin_ajax_object.sync_nonce_token;
				$.ajax(
					{
						url: admin_ajax_object.ajaxurl,
						type: "post",
						data:
						"action=wpssw_sync_sheetswise&sheetslug=" +
						sheetSlug +
						"&sheetname=" +
						sheetName +
						"&orderlimit=" +
						orderLimit +
						"&ordercount=" +
						orderCount +
						"&sync_all_fromdate=" +
						syncAllFromdate +
						"&sync_all_todate=" +
						syncAllTodate +
						"&sync_all=" +
						syncAll +
						"&sync_nonce_token=" +
						sync_nonce_token,
						success: function (response) {
							if (
							parseInt( totalSheet ) === syncSheet + 1 &&
							totalOrders <= orderCount
							) {
								totalSheet = 0;
								syncSheet  = 0;
								orderLimit = 500;
								orderCount = 0;
								nextSheet  = 1;
								obj        = {};
								if (String( response ) === "successful") {
									alert( "All Orders are synchronize successfully" );
									displayordersync();
									resetordersync();
								} else {
									alert(
										"Your Google Sheets API limit has been reached. Please take a look at our FAQ."
									);
									displayordersync();
									resetordersync();
								}
							}
						},
						complete: function () {
							var objLength = Object.keys( obj ).length;
							if (0 !== objLength) {
								var sheetName   = obj[syncSheet]["sheet_name"];
								var sheetSlug   = obj[syncSheet]["sheet_slug"];
								var totalOrders = obj[syncSheet]["totalorders"];
								orderLimit      = obj[syncSheet]["orderlimit"];
								if (totalOrders > orderCount) {
									setTimeout(
										function () {
											syncData(
												sheetName,
												sheetSlug,
												totalOrders,
												orderLimit,
												syncAll,
												syncAllFromdate,
												syncAllTodate
											);
										},
										2000
									);
								} else if (
								totalOrders < orderCount &&
								parseInt( nextSheet ) === 1 &&
								totalSheet != syncSheet + 1
								) {
										setTimeout(
											function () {
													syncData(
														sheetName,
														sheetSlug,
														totalOrders,
														orderLimit,
														syncAll,
														syncAllFromdate,
														syncAllTodate
													);
											},
											2000
										);
								} else {
									if (totalSheet > syncSheet + 1) {
										syncSheet       = syncSheet + 1;
										orderCount      = 0;
										nextSheet       = 1;
										var sheetName   = obj[syncSheet]["sheet_name"];
										var sheetSlug   = obj[syncSheet]["sheet_slug"];
										var totalOrders = obj[syncSheet]["totalorders"];
										orderLimit      = obj[syncSheet]["orderlimit"];
										setTimeout(
											function () {
												syncData(
													sheetName,
													sheetSlug,
													totalOrders,
													orderLimit,
													syncAll,
													syncAllFromdate,
													syncAllTodate
												);
											},
											2000
										);
									}
								}
							}
						},
					}
				).fail(
					function () {
						alert( "Error" );
						/*totalSheet = 0;
						syncSheet  = 0;
						orderLimit = 500;
						orderCount = 0;
						nextSheet  = 1;
						obj        = {};*/
						displayordersync();
						resetordersync();
					}
				);
			}
			function displayordersync() {
				$( "#syncloader" ).hide();
				$( "#synctext" ).hide();
				$( "#synctext" ).html( "Synchronizing..." );
				document.getElementById( "sync" ).style.display = "inline-block";
			}
			function resetordersync(){
				totalSheet = 0;
				syncSheet  = 0;
				orderLimit = 500;
				orderCount = 0;
				nextSheet  = 1;
				obj        = {};
			}

			function syncProductData(
			totalproducts,
			productLimit,
			prdsyncAll,
			prdsyncAllFromdate,
			prdsyncAllTodate,
			prdsyncAllLangs,
			prdsyncCustomLangs,
			sheetName,
			sheetSlug,
			prdsyncAllSaleFromdate,
			prdsyncAllSaleTodate
			) {
				var productnonce = $( "#wpssw_product_settings" ).val();

				if (parseInt( totalproducts ) < 2000) {
					productLimit = 200;
					if (parseInt( totalproducts ) < 200) {
						productLimit = parseInt( totalproducts );
					}
				}
				if (totalproducts > productCount) {
					productCount = productCount + productLimit;
				} else if (totalproducts < productCount &&
				parseInt( nextPrdSheet ) === 1 &&
				totalPrdSheet != syncPrdSheet + 1) {
					productCount = productCount + productLimit;
					nextPrdSheet = 0;
				}

				if (totalproducts > productLimit && productCount < totalproducts) {
					$( "#prodsynctext" ).html(
						"Synchronizing : " + productCount + " / " + totalproducts +
						" " +
						sheetName
					);
				} else {
					$( "#prodsynctext" ).html(
						"Synchronizing : " + totalproducts + " / " + totalproducts +
						" " +
						sheetName
					);
				}
				$.ajax(
					{
						url: admin_ajax_object.ajaxurl,
						type: "post",
						data:
						"action=wpssw_sync_products&sheetslug=" +
						sheetSlug +
						"&sheetname=" +
						sheetName +
						"&productlimit=" +
						productLimit +
						"&productcount=" +
						productCount +
						"&wpssw_product_settings=" +
						productnonce +
						"&prd_sync_all_fromdate=" +
						prdsyncAllFromdate +
						"&prd_sync_all_todate=" +
						prdsyncAllTodate +
						"&prd_sync_all=" +
						prdsyncAll +
						"&prd_sync_all_langs=" +
						prdsyncAllLangs +
						"&prd_sync_custom_langs=" +
						prdsyncCustomLangs +
						"&prd_sync_all_sale_fromdate=" +
						prdsyncAllSaleFromdate +
						"&prd_sync_all_sale_todate=" +
						prdsyncAllSaleTodate,
						success: function (response) {
							if (
							parseInt( totalPrdSheet ) === syncPrdSheet + 1 && totalproducts <= productCount) {
								/*productLimit = 500;
								productCount = 0;
								obj          = {};

								totalPrdSheet = 0;
								syncPrdSheet  = 0;
								nextPrdSheet  = 1;*/

								if (response == "successful") {
									alert( "All Products are synchronize successfully" );
								} else {
									alert(
										"Your Google Sheets API limit has been reached. Please take a look at our FAQ."
									);
								}
								displayproductsync();
								resetproductsync();
							}
						},
						complete: function () {

							var objLength = Object.keys( obj ).length;
							if (0 !== objLength) {
								var sheetName     = obj[syncPrdSheet]["sheet_name"];
								var sheetSlug     = obj[syncPrdSheet]["sheet_slug"];
								var totalproducts = obj[syncPrdSheet]["totalproducts"];
								productLimit      = obj[syncPrdSheet]["productlimit"];
								if (totalproducts > productCount) {
									setTimeout(
										function () {
											syncProductData(
												totalproducts,
												productLimit,
												prdsyncAll,
												prdsyncAllFromdate,
												prdsyncAllTodate,
												prdsyncAllLangs,
												prdsyncCustomLangs,
												sheetName,
												sheetSlug,
												prdsyncAllSaleFromdate,
												prdsyncAllSaleTodate,
											);
										},
										2000
									);
								} else if (
								totalproducts < productCount &&
								parseInt( nextPrdSheet ) === 1 &&
								totalPrdSheet != syncPrdSheet + 1
								) {
										setTimeout(
											function () {
												syncProductData(
													totalproducts,
													productLimit,
													prdsyncAll,
													prdsyncAllFromdate,
													prdsyncAllTodate,
													prdsyncAllLangs,
													prdsyncCustomLangs,
													sheetName,
													sheetSlug,
													prdsyncAllSaleFromdate,
													prdsyncAllSaleTodate,
												);
											},
											2000
										);
								} else {
									if (totalPrdSheet > syncPrdSheet + 1) {
										syncPrdSheet      = syncPrdSheet + 1;
										productCount      = 0;
										nextPrdSheet      = 1;
										var sheetName     = obj[syncPrdSheet]["sheet_name"];
										var sheetSlug     = obj[syncPrdSheet]["sheet_slug"];
										var totalproducts = obj[syncPrdSheet]["totalproducts"];
										productLimit      = obj[syncPrdSheet]["orderlimit"];
										setTimeout(
											function () {
												syncProductData(
													totalproducts,
													productLimit,
													prdsyncAll,
													prdsyncAllFromdate,
													prdsyncAllTodate,
													prdsyncAllLangs,
													prdsyncCustomLangs,
													sheetName,
													sheetSlug,
													prdsyncAllSaleFromdate,
													prdsyncAllSaleTodate,
												);
											},
											2000
										);
									}
								}
							}
						},
					}
				).fail(
					function () {
						alert( "Error" );
						/*productLimit = 500;
						productCount = 0;
						obj          = {};*/
						displayproductsync();
						resetproductsync();

						/*totalPrdSheet = 0;
						syncPrdSheet  = 0;
						nextPrdSheet  = 1;*/

					}
				);
			}
			function displayproductsync() {
				$( "#prodsyncloader" ).hide();
				$( "#prodsynctext" ).hide();
				$( "#prodsynctext" ).html( "Synchronizing..." );
				document.getElementById( "prodsync" ).style.display = "inline-block";
			}
			function resetproductsync(){
				productLimit  = 500;
				productCount  = 0;
				obj           = {};
				totalPrdSheet = 0;
				syncPrdSheet  = 0;
				nextPrdSheet  = 1;
			}

			function syncAttributeData(totalattributes, attributeLimit) {
				var attributenonce = $( "#wpssw_product_settings" ).val();

				if (totalattributes > attributeCount) {
					if (parseInt( totalattributes ) < 2000) {
						attributeLimit = 200;
						if (parseInt( totalattributes ) < 200) {
							attributeLimit = parseInt( totalattributes );
						}
					}
					attributeCount = attributeCount + attributeLimit;
				} else if (totalattributes < attributeCount) {
					attributeCount = attributeCount + attributeLimit;
				}
				if (
				totalattributes > attributeLimit &&
				attributeCount < totalattributes
				) {
						$( "#attrsynctext" ).html(
							"Synchronizing : " + attributeCount + " / " + totalattributes
						);
				} else {
					$( "#attrsynctext" ).html(
						"Synchronizing : " + totalattributes + " / " + totalattributes
					);
				}
				$.ajax(
					{
						url: admin_ajax_object.ajaxurl,
						type: "post",
						data:
						"action=wpssw_sync_attributes&attributelimit=" +
						attributeLimit +
						"&attributecount=" +
						attributeCount +
						"&wpssw_attributes_settings=" +
						attributenonce,
						success: function (response) {
							if (totalattributes <= attributeCount) {
								attributeLimit = 500;
								attributeCount = 0;
								obj            = {};
								if (response == "successful") {
									alert( "All Attributes are synchronize successfully" );
									displayattributesync();
								} else {
									alert(
										"Your Google Sheets API limit has been reached. Please take a look at our FAQ."
									);
									displayattributesync();
								}
							}
						},
						complete: function () {
							if (0 !== obj.length) {
								var totalattributes = obj.totalattributes;
								attributeLimit      = obj.attributelimit;
								if (parseInt( totalattributes ) < 2000) {
									attributeLimit = 200;
									if (parseInt( totalattributes ) < 200) {
										attributeLimit = parseInt( totalattributes );
									}
								}
								if (totalattributes > attributeCount) {
									setTimeout(
										function () {
											syncAttributeData( totalattributes, attributeLimit );
										},
										2000
									);
								}
							}
						},
					}
				).fail(
					function () {
						alert( "Error" );
						attributeLimit = 500;
						attributeCount = 0;
						obj            = {};
						displayattributesync();
					}
				);
			}
			function displayattributesync() {
				$( "#attrsyncloader" ).hide();
				$( "#attrsynctext" ).hide();
				$( "#attrsynctext" ).html( "Synchronizing..." );
				document.getElementById( "attrsync" ).style.display = "inline-block";
			}
			function syncCustomerData(
			totalcustomers,
			customerLimit,
			custsyncAll,
			custsyncAllFromdate,
			custsyncAllTodate
			) {
				if (totalcustomers > customerCount) {
					if (parseInt( totalcustomers ) < 2000) {
						customerLimit = 200;
						if (parseInt( totalcustomers ) < 200) {
							customerLimit = parseInt( totalcustomers );
						}
					}
					customerCount = customerCount + customerLimit;
				} else if (totalcustomers < customerCount) {
					customerCount = customerCount + customerLimit;
				}
				if (totalcustomers > customerLimit && customerCount < totalcustomers) {
					$( "#custsynctext" ).html(
						"Synchronizing : " + customerCount + " / " + totalcustomers
					);
				} else {
					$( "#custsynctext" ).html(
						"Synchronizing : " + totalcustomers + " / " + totalcustomers
					);
				}
				var customernonce = $( "#wpssw_customer_settings" ).val();
				$.ajax(
					{
						url: admin_ajax_object.ajaxurl,
						type: "post",
						data:
						"action=wpssw_sync_customers&customerlimit=" +
						customerLimit +
						"&customercount=" +
						customerCount +
						"&wpssw_customer_settings=" +
						customernonce +
						"&cust_sync_all_fromdate=" +
						custsyncAllFromdate +
						"&cust_sync_all_todate=" +
						custsyncAllTodate +
						"&cust_sync_all=" +
						custsyncAll,
						success: function (response) {
							if (totalcustomers <= customerCount) {
								customerLimit = 500;
								customerCount = 0;
								obj           = {};
								if (response == "successful") {
									alert( "All Customers data are synchronize successfully" );
									displaycustomersync();
								} else {
									alert(
										"Your Google Sheets API limit has been reached. Please take a look at our FAQ."
									);
									displaycustomersync();
								}
							}
						},
						complete: function () {
							if (0 !== obj.length) {
								var totalcustomers = obj.totalcustomers;
								customerLimit      = obj.customerlimit;
								if (parseInt( totalcustomers ) < 2000) {
									customerLimit = 200;
									if (parseInt( totalcustomers ) < 200) {
										customerLimit = parseInt( totalcustomers );
									}
								}
								if (totalcustomers > customerCount) {
									setTimeout(
										function () {
											syncCustomerData(
												totalcustomers,
												customerLimit,
												custsyncAll,
												custsyncAllFromdate,
												custsyncAllTodate
											);
										},
										2000
									);
								}
							}
						},
					}
				).fail(
					function () {
						alert( "Error" );
						customerLimit = 500;
						customerCount = 0;
						obj           = {};
						displaycustomersync();
					}
				);
			}
			function displaycustomersync() {
				$( "#custsyncloader" ).hide();
				$( "#custsynctext" ).hide();
				$( "#custsynctext" ).html( "Synchronizing..." );
				document.getElementById( "custsync" ).style.display = "inline-block";
			}
			function syncCouponData(
			totalcoupons,
			couponLimit,
			couponsyncAll,
			couponsyncAllFromdate,
			couponsyncAllTodate
			) {
				if (totalcoupons > couponCount) {
					if (parseInt( totalcoupons ) < 2000) {
						couponLimit = 200;
						if (parseInt( totalcoupons ) < 200) {
							couponLimit = parseInt( totalcoupons );
						}
					}
					couponCount = couponCount + couponLimit;
				} else if (totalcoupons < couponCount) {
					couponCount = couponCount + couponLimit;
				}
				if (totalcoupons > couponLimit && couponCount < totalcoupons) {
					$( "#couponsynctext" ).html(
						"Synchronizing : " + couponCount + " / " + totalcoupons
					);
				} else {
					$( "#couponsynctext" ).html(
						"Synchronizing : " + totalcoupons + " / " + totalcoupons
					);
				}
				var couponnonce = $( "#wpssw_coupon_settings" ).val();
				$.ajax(
					{
						url: admin_ajax_object.ajaxurl,
						type: "post",
						data:
						"action=wpssw_sync_coupons&couponlimit=" +
						couponLimit +
						"&couponcount=" +
						couponCount +
						"&couponnonce=" +
						couponnonce +
						"&coupon_sync_all_fromdate=" +
						couponsyncAllFromdate +
						"&coupon_sync_all_todate=" +
						couponsyncAllTodate +
						"&coupon_sync_all=" +
						couponsyncAll,
						success: function (response) {
							if (totalcoupons <= couponCount) {
								couponLimit = 500;
								couponCount = 0;
								obj         = {};
								if (response == "successful") {
									alert( "All Coupons data are synchronize successfully" );
									displaycouponsync();
								} else {
									alert(
										"Your Google Sheets API limit has been reached. Please take a look at our FAQ."
									);
									displaycouponsync();
								}
							}
						},
						complete: function () {
							if (0 !== obj.length) {
								var totalcoupons = obj.totalcoupons;
								couponLimit      = obj.couponlimit;
								if (parseInt( totalcoupons ) < 2000) {
									couponLimit = 200;
									if (parseInt( totalcoupons ) < 200) {
										couponLimit = parseInt( totalcoupons );
									}
								}
								if (totalcoupons > couponCount) {
									setTimeout(
										function () {
											syncCouponData(
												totalcoupons,
												couponLimit,
												couponsyncAll,
												couponsyncAllFromdate,
												couponsyncAllTodate
											);
										},
										2000
									);
								}
							}
						},
					}
				).fail(
					function () {
						alert( "Error" );
						couponLimit = 500;
						couponCount = 0;
						obj         = {};
						displaycouponsync();
					}
				);
			}
			var totalEventSheet = 0;
			var syncEventSheet  = 0;
			var eventOrderLimit = 500;
			var eventOrderCount = 0;
			var eventNextSheet  = 1;
			var eventobj;
			function doEventAjax(args) {
				var eventnonce           = $( "#wpssw_event_settings" ).val();
				var eventsyncAll         = $(
					'input[name="event_sync_all_checkbox"]:checked'
				).val();
				var eventsyncAllFromdate = $( "#event_sync_all_fromdate" ).val();
				var eventsyncAllTodate   = $( "#event_sync_all_todate" ).val();
				if (
				parseInt( eventsyncAll ) === 0 &&
				(String( eventsyncAllFromdate ) === "" ||
				String( eventsyncAllTodate ) === "")
				) {
					alert( "From Date and To Date should not be blank." );
					return false;
				} else if (eventsyncAllFromdate > eventsyncAllTodate) {
					alert( "From Date should not be greater than To Date." );
					return false;
				} else {
					$( "#eventsyncloader" ).show();
					$( "#eventsynctest" ).show();
					$( "#sync_event" ).hide();

					$.ajax(
						{
							url: admin_ajax_object.ajaxurl,
							type: "post",
							data:
							"action=wpssw_get_events_count&wpssw_event_settings=" +
							eventnonce +
							"&event_sync_all_fromdate=" +
							eventsyncAllFromdate +
							"&event_sync_all_todate=" +
							eventsyncAllTodate +
							"&event_sync_all=" +
							eventsyncAll,
							success: function (response) {
								if (String( response ) === "error") {
									alert( "Sorry, your nonce did not verify." );
									displayeventsync();
								} else if (String( response ) === "sheetnotexist") {
									alert(
										"Selected Event Category sheet is not present in your spreadsheet so to sync events first save your settings and try again."
									);
									displayeventsync();
									return false;
								} else {
									eventobj        = JSON.parse( response );
									totalEventSheet = Object.keys( eventobj ).length;
									if (totalEventSheet > 0) {
										var eventSheetName   = eventobj[syncEventSheet]["sheet_name"];
										var totalEventOrders = eventobj[syncEventSheet]["totalorders"];
										eventOrderLimit      = eventobj[syncEventSheet]["eventlimit"];
										if (parseInt( totalEventOrders ) < 2000) {
											eventOrderLimit = 200;

											if (parseInt( totalEventOrders ) < 200) {
												eventOrderLimit = parseInt( totalEventOrders );
											}
										}
										syncEventData(
											eventSheetName,
											totalEventOrders,
											eventOrderLimit,
											eventsyncAll,
											eventsyncAllFromdate,
											eventsyncAllTodate
										);
									} else {
										alert( "All Orders are synchronize successfully" );
										displayeventsync();
									}
								}
							},
						}
					).fail(
						function () {
							alert( "Error" );
							displayeventsync();
						}
					);
				}
			}
			function syncEventData(
			eventSheetName,
			totalEventOrders,
			eventOrderLimit,
			eventsyncAll,
			eventsyncAllFromdate,
			eventsyncAllTodate
			) {
				if (parseInt( totalEventOrders ) < 2000) {
					eventOrderLimit = 200;
					if (parseInt( totalEventOrders ) < 200) {
						eventOrderLimit = parseInt( totalEventOrders );
					}
				}
				if (totalEventOrders > eventOrderCount) {
					eventOrderCount = eventOrderCount + eventOrderLimit;
				} else if (
				totalEventOrders < eventOrderCount &&
				parseInt( eventNextSheet ) === 1 &&
				totalEventSheet != syncEventSheet + 1
				) {
						eventOrderCount = eventOrderCount + eventOrderLimit;
						eventNextSheet  = 0;
				}
				if (
				totalEventOrders > eventOrderLimit &&
				eventOrderCount < totalEventOrders
				) {
						$( "#eventsynctest" ).html(
							"Synchronizing : " +
							eventOrderCount +
							" / " +
							totalEventOrders +
							" " +
							eventSheetName
						);
				} else {
					$( "#eventsynctest" ).html(
						"Synchronizing : " +
						totalEventOrders +
						" / " +
						totalEventOrders +
						" " +
						eventSheetName
					);
				}
				var eventnonce = $( "#wpssw_event_settings" ).val();
				$.ajax(
					{
						url: admin_ajax_object.ajaxurl,
						type: "post",
						data:
						"action=wpssw_sync_events&sheetname=" +
						eventSheetName +
						"&orderlimit=" +
						eventOrderLimit +
						"&ordercount=" +
						eventOrderCount +
						"&wpssw_event_settings=" +
						eventnonce +
						"&event_sync_all_fromdate=" +
						eventsyncAllFromdate +
						"&event_sync_all_todate=" +
						eventsyncAllTodate +
						"&event_sync_all=" +
						eventsyncAll,
						success: function (response) {
							if (
							parseInt( totalEventSheet ) === syncEventSheet + 1 &&
							totalEventOrders <= eventOrderCount
							) {
								totalEventSheet = 0;
								syncEventSheet  = 0;
								eventOrderLimit = 500;
								eventOrderCount = 0;
								eventNextSheet  = 1;
								eventobj        = {};
								if (String( response ) === "successful") {
									alert( "All Orders are synchronize successfully" );
									displayeventsync();
								} else {
									alert(
										"Your Google Sheets API limit has been reached. Please take a look at our FAQ."
									);
									displayeventsync();
								}
							}
						},
						complete: function () {
							var eventobjLength = Object.keys( eventobj ).length;
							if (0 !== eventobjLength) {
								var eventSheetName   = eventobj[syncEventSheet]["sheet_name"];
								var totalEventOrders = eventobj[syncEventSheet]["totalorders"];
								eventOrderLimit      = eventobj[syncEventSheet]["eventlimit"];
								if (totalEventOrders > eventOrderCount) {
									setTimeout(
										function () {
											syncEventData(
												eventSheetName,
												totalEventOrders,
												eventOrderLimit,
												eventsyncAll,
												eventsyncAllFromdate,
												eventsyncAllTodate
											);
										},
										2000
									);
								} else if (
								totalEventOrders < eventOrderCount &&
								parseInt( eventNextSheet ) === 1 &&
								totalEventSheet != syncEventSheet + 1
								) {
										setTimeout(
											function () {
													syncEventData(
														eventSheetName,
														totalEventOrders,
														eventOrderLimit,
														eventsyncAll,
														eventsyncAllFromdate,
														eventsyncAllTodate
													);
											},
											2000
										);
								} else {
									if (totalEventSheet > syncEventSheet + 1) {
										syncEventSheet       = syncEventSheet + 1;
										eventOrderCount      = 0;
										eventNextSheet       = 1;
										var eventSheetName   = eventobj[syncEventSheet]["sheet_name"];
										var totalEventOrders = eventobj[syncEventSheet]["totalorders"];
										eventOrderLimit      = eventobj[syncEventSheet]["eventlimit"];
										setTimeout(
											function () {
												syncEventData(
													eventSheetName,
													totalEventOrders,
													eventOrderLimit,
													eventsyncAll,
													eventsyncAllFromdate,
													eventsyncAllTodate
												);
											},
											2000
										);
									}
								}
							}
						},
					}
				).fail(
					function () {
						alert( "Error" );
						totalEventSheet = 0;
						syncEventSheet  = 0;
						eventOrderLimit = 500;
						eventOrderCount = 0;
						eventNextSheet  = 1;
						eventobj        = {};
						displayeventsync();
					}
				);
			}
			function displayeventsync() {
				$( "#eventsyncloader" ).hide();
				$( "#eventsynctest" ).hide();
				$( "#eventsynctest" ).html( "Synchronizing..." );
				document.getElementById( "sync_event" ).style.display = "inline-block";
			}
			function addRegenerateGraph(id, chartId, graphType) {
				var sheetId       = id;
				var chartLoaderId = chartId;
				var sync_nonce_token;
				sync_nonce_token = admin_ajax_object.sync_nonce_token;
				$( "#" + chartLoaderId ).show();
				$.ajax(
					{
						url: admin_ajax_object.ajaxurl,
						type: "post",
						data:
						"action=wpssw_regenerate_graph&sheet_id=" +
						sheetId +
						"&graph_type=" +
						graphType +
						"&sync_nonce_token=" +
						sync_nonce_token,
						success: function (response) {
							$( "#" + chartLoaderId ).hide();
							if (String( response ) === "successful") {
								alert( "Graph Regenerated successfully" );
							} else if (String( response ) === "spreadsheetnotexist") {
								alert( "Please save your settings first and try again." );
								return false;
							} else if (String( response ) === "sheetnotexist") {
								alert(
									"Selected Graph sheet is not present in your spreadsheet so to regenerate graph first save your settings and try again."
								);
								$( "html, body" ).animate(
									{
										scrollTop: $( "html, body" ).get( 0 ).scrollHeight,
									},
									3000
								);
								return false;
							} else {
								alert( response );
							}
						},
					}
				).fail(
					function () {
						$( "#" + chartLoaderId ).hide();
						alert( "Error" );
					}
				);
			}
			$( document ).on(
				"click",
				".wpssw_new_token",
				function (e) {
					e.preventDefault();
					$( ".tablinks.googleapi-settings" ).addClass( "active" );
				}
			);

			$( document ).on(
				"click",
				"#clear_spreadsheet",
				function (e) {
					e.preventDefault();
					var wpsswGeneralSettings = $( "#wpssw_general_settings" ).val();
					$.ajax(
						{
							url: admin_ajax_object.ajaxurl,
							type: "post",
							data:
							"action=wpssw_clear_all_sheet&wpssw_general_settings=" +
							wpsswGeneralSettings,

							beforeSend: function () {
								if (
								confirm(
									"Are you sure you want to enable Clear Spreadsheet? If you do, it will remove all your orders from the spreadsheet, and you'll be left only with the headers of the sheet."
								)
								) {
									$( "#clearloader" ).attr( "src", "images/spinner.gif" );
									$( "#clearloader" ).show();
								} else {
									return false;
								}
							},
							success: function (response) {
								$( "#clearloader" ).hide();
								if (String( response ) === "successful") {
									alert( "Spreadsheet Cleared successfully" );
								} else if (String( response ) === "spreadsheetnotexist") {
									alert( "Please save your settings first and try again." );
									return false;
								} else if (String( response ) === "sheetnotexist") {
									alert(
										"Selected Order status sheet is not present in your spreadsheet so to clear spreadsheet first save your settings and try again."
									);
									$( "html, body" ).animate(
										{
											scrollTop: $( "html, body" ).get( 0 ).scrollHeight,
										},
										3000
									);
									return false;
								} else {
									alert( response );
								}
							},
							error: function (s) {
								alert( "Error" );
								$( "#clearloader" ).hide();
							},
						}
					);
				}
			);
			$( document ).on(
				"click",
				"#clear_productsheet",
				function (e) {
					e.preventDefault();
					$.ajax(
						{
							url: admin_ajax_object.ajaxurl,
							type: "post",
							data: "action=wpssw_clear_productsheet",
							beforeSend: function () {
								if (
								confirm(
									"Are you sure? It will clear all your product data within the spreadsheet and you would have remained only with sheet headers."
								)
								) {
									$( "#clearprdloader" ).attr( "src", "images/spinner.gif" );
									$( "#clearprdloader" ).show();
								} else {
									return false;
								}
							},
							success: function (response) {
								if (response == "successful") {
									alert( "Spreadsheet Cleared successfully" );
									$( "#clearprdloader" ).hide();
								} else {
									alert( response );
									$( "#clearprdloader" ).hide();
								}
							},
							error: function (s) {
								alert( "Error" );
								$( "#clearprdloader" ).hide();
							},
						}
					);
				}
			);
			$( document ).on(
				"click",
				"#clear_customersheet",
				function (e) {
					e.preventDefault();
					$.ajax(
						{
							url: admin_ajax_object.ajaxurl,
							type: "post",
							data: "action=wpssw_clear_customersheet",
							beforeSend: function () {
								if (
								confirm(
									"Are you sure? You want to enable Clear Spreadsheet this is clear all your customer data within the spreadsheet and you would be remained only with sheet headers."
								)
								) {
									$( "#clearcustloader" ).attr( "src", "images/spinner.gif" );
									$( "#clearcustloader" ).show();
								} else {
									return false;
								}
							},
							success: function (response) {
								if (response == "successful") {
									alert( "Spreadsheet Cleared successfully" );
									$( "#clearcustloader" ).hide();
								} else {
									alert( response );
									$( "#clearcustloader" ).hide();
								}
							},
							error: function (s) {
								alert( "Error" );
								$( "#clearcustloader" ).hide();
							},
						}
					);
				}
			);
			$( document ).on(
				"click",
				"#clear_eventsheet",
				function (e) {
					e.preventDefault();
					$.ajax(
						{
							url: admin_ajax_object.ajaxurl,
							type: "post",
							data: "action=wpssw_clear_eventsheet",
							beforeSend: function () {
								if (
								confirm(
									"Are you sure? You want to enable Clear Spreadsheet this is clear all your event data within the spreadsheet and you would be remained only with sheet headers."
								)
								) {
									$( "#cleareventloader" ).show();
									$( "#clear_eventsheet" ).hide();
								} else {
									return false;
								}
							},
							success: function (response) {
								if (response == "successful") {
									alert( "Spreadsheet Cleared successfully" );
									$( "#cleareventloader" ).hide();
									$( "#clear_eventsheet" ).show();
								} else if (String( response ) === "sheetnotexist") {
									alert(
										"Selected Event Category sheet is not present in your spreadsheet so to clear spreadsheet first save your settings and try again."
									);
									$( "#cleareventloader" ).hide();
									$( "#clear_eventsheet" ).show();
									return false;
								} else {
									alert( response );
									$( "#cleareventloader" ).hide();
									$( "#clear_eventsheet" ).show();
								}
							},
							error: function (s) {
								alert( "Error" );
								$( "#cleareventloader" ).hide();
								$( "#clear_eventsheet" ).show();
							},
						}
					);
				}
			);
			$( document ).on(
				"click",
				"#clear_couponsheet",
				function (e) {
					e.preventDefault();
					$.ajax(
						{
							url: admin_ajax_object.ajaxurl,
							type: "post",
							data: "action=wpssw_clear_couponsheet",
							beforeSend: function () {
								if (
								confirm(
									"Are you sure? You want to enable Clear Spreadsheet this is clear all your coupon data within the spreadsheet and you would be remained only with sheet headers."
								)
								) {
									$( "#clearcouponloader" ).attr( "src", "images/spinner.gif" );
									$( "#clearcouponloader" ).show();
								} else {
									return false;
								}
							},
							success: function (response) {
								if (response == "successful") {
									alert( "Spreadsheet Cleared successfully" );
									$( "#clearcouponloader" ).hide();
								} else {
									alert( response );
									$( "#clearcouponloader" ).hide();
								}
							},
							error: function (s) {
								alert( "Error" );
								$( "#clearcouponloader" ).hide();
							},
						}
					);
				}
			);
		}
	);
	// Check for existing sheets.
	$( document ).ready(
		function () {
			var prevSheetId = $( "#woocommerce_spreadsheet" ).val();
			$( "#woocommerce_spreadsheet" ).on(
				"change",
				function () {
					var sheetId = $( this ).val();
					if (String( sheetId ) === prevSheetId) {
						$( "#header_fields" ).prop( "disabled", "disabled" );
						$( "#productwise" ).addClass( "disabled" );
						$( "#orderwise" ).addClass( "disabled" );
						$( ".manage-row" ).prop( "disabled", "disabled" );
						$( "#newsheet" ).hide();
						$( "#view_spreadsheet" ).show();
						$( "#clear_spreadsheet" ).show();
						$( "#down_spreadsheet" ).show();
						$( "#woocommerce_spreadsheet_container" ).show();
						$( "#sortable" ).sortable(
							{
								disabled: true,
							}
						);
						$( "#product-sortable" ).sortable(
							{
								disabled: true,
							}
						);
						$( "#wpssw-headers-notice" ).show();
						$( ".manage_order" ).prop( "disabled", true );
						$( "#asc_order" ).addClass( "disabled" );
						$( "#desc_order" ).addClass( "disabled" );
						$( ".synctr" ).show();
						if (
						$( "#import_order_checkbox" ).is( ":checked" ) &&
						($( "#insert_order_checkbox" ).is( ":checked" ) ||
							$( "#update_order_checkbox" ).is( ":checked" ) ||
							$( "#delete_order_checkbox" ).is( ":checked" ))
						) {
								$( ".ord_import_row" ).show();
						}
						return true;
					} else {
						$( "#productwise" ).removeClass( "disabled" );
						$( "#orderwise" ).removeClass( "disabled" );
						$( ".manage_order" ).prop( "disabled", false );
						$( "#desc_order" ).removeClass( "disabled" );
						$( "#asc_order" ).removeClass( "disabled" );
					}

					var sync_nonce_token;
					sync_nonce_token = admin_ajax_object.sync_nonce_token;
					if (sheetId != null && sheetId != "" && sheetId != "new") {
						$.ajax(
							{
								url: admin_ajax_object.ajaxurl,
								type: "post",
								data: {
									action: "wpssw_check_existing_sheet",
									id: sheetId,
									sync_nonce_token: sync_nonce_token,
								},
								success: function (response) {
									if (String( response ) === "successful") {
										alert(
											"Selected spreadsheet will be mismatch match your order data with respect to the sheet headers so please create new spreadsheet or select different spreadsheet."
										);
										$( ".manage_order" ).prop( "disabled", true );
										$( "#asc_order" ).addClass( "disabled" );
										$( "#desc_order" ).addClass( "disabled" );
										$( "#productwise" ).addClass( "disabled" );
										$( "#orderwise" ).addClass( "disabled" );
										$( "#woocommerce_spreadsheet" ).val( prevSheetId );
									} else if (String( response )) {
										alert( response );
										$( ".manage_order" ).prop( "disabled", true );
										$( "#asc_order" ).addClass( "disabled" );
										$( "#desc_order" ).addClass( "disabled" );
										$( "#productwise" ).addClass( "disabled" );
										$( "#orderwise" ).addClass( "disabled" );
										$( "#woocommerce_spreadsheet" ).val( prevSheetId );
									} else {
										$( "#header_fields" ).prop( "disabled", false );
										$( "#productwise" ).removeClass( "disabled" );
										$( "#orderwise" ).removeClass( "disabled" );
										$( ".synctr" ).css( "display", "none" );
										$( ".ord_import_row" ).css( "display", "none" );
										$( ".manage_order" ).prop( "disabled", false );
										$( "#desc_order" ).removeClass( "disabled" );
										$( "#asc_order" ).removeClass( "disabled" );
									}
								},
							}
						);
					}
				}
			);

			var prevPrdSheetId = $( "#product_spreadsheet" ).val();
			$( "#product_spreadsheet" ).on(
				"change",
				function () {
					var sheetId = $( this ).val();
					if (String( sheetId ) === prevPrdSheetId) {
						$( "#prd_view_spreadsheet" ).show();
						$( "#clear_productsheet" ).show();
						$( "#prd_down_spreadsheet" ).show();
						$( "#prodsynctr" ).show();
						if (
						$( "#import_checkbox" ).is( ":checked" ) &&
						($( "#insert_checkbox" ).is( ":checked" ) ||
						$( "#update_checkbox" ).is( ":checked" ) ||
						$( "#delete_checkbox" ).is( ":checked" ))
						) {
							$( ".prd_import_row.import_row" ).show();
						} else {
							$( ".prd_import_row.import_row" ).hide();
						}
					} else {
						$( "#prd_view_spreadsheet" ).hide();
						$( "#clear_productsheet" ).hide();
						$( "#prd_down_spreadsheet" ).hide();
						$( "#prodsynctr" ).hide();
						$( ".prd_import_row.import_row" ).hide();
					}
				}
			);
			var prevCustSheetId = $( "#customer_spreadsheet" ).val();
			$( "#customer_spreadsheet" ).on(
				"change",
				function () {
					var sheetId = $( this ).val();
					if (String( sheetId ) === prevCustSheetId) {
						$( "#cust_view_spreadsheet" ).show();
						$( "#clear_customersheet" ).show();
						$( "#cust_down_spreadsheet" ).show();
						$( "#custsynctr" ).show();
						if (
						$( "#import_customer_checkbox" ).is( ":checked" ) &&
						($( "#insert_customer_checkbox" ).is( ":checked" ) ||
						$( "#update_customer_checkbox" ).is( ":checked" ) ||
						$( "#delete_customer_checkbox" ).is( ":checked" ))
						) {
							$( ".cust_import_row" ).show();
						} else {
							$( ".cust_import_row" ).hide();
						}
					} else {
						$( "#cust_view_spreadsheet" ).hide();
						$( "#clear_customersheet" ).hide();
						$( "#cust_down_spreadsheet" ).hide();
						$( "#custsynctr" ).hide();
						$( ".cust_import_row" ).hide();
					}
				}
			);
			var prevCouponSheetId = $( "#coupon_spreadsheet" ).val();
			$( "#coupon_spreadsheet" ).on(
				"change",
				function () {
					var sheetId = $( this ).val();
					if (String( sheetId ) === prevCouponSheetId) {
						$( "#coupon_view_spreadsheet" ).show();
						$( "#clear_couponsheet" ).show();
						$( "#coupon_down_spreadsheet" ).show();
						$( "#couponsynctr" ).show();
						if (
						$( "#import_coupon_checkbox" ).is( ":checked" ) &&
						($( "#insert_coupon_checkbox" ).is( ":checked" ) ||
						$( "#update_coupon_checkbox" ).is( ":checked" ) ||
						$( "#delete_coupon_checkbox" ).is( ":checked" ))
						) {
							$( ".coupon_import_row" ).show();
						} else {
							$( ".coupon_import_row" ).hide();
						}
					} else {
						$( "#coupon_view_spreadsheet" ).hide();
						$( "#clear_couponsheet" ).hide();
						$( "#coupon_down_spreadsheet" ).hide();
						$( "#couponsynctr" ).hide();
						$( ".coupon_import_row" ).hide();
					}
				}
			);
		}
	);
	$( document ).ready(
		function () {
			$( "#authlink" ).on(
				"click",
				function (e) {
					$( "#authbtn" ).hide();
					document.getElementById( "authtext" ).style.display = "inline-block";
				}
			);
			$( "#revoke" ).on(
				"click",
				function (e) {
					document.getElementById( "authtext" ).style.display     = "none";
					document.getElementById( "client_token" ).style.display = "none";
				}
			);
		}
	);
	$( document ).ready(
		function () {
			// Get the input fields.
			var licenseKeyInput = $( "#wpssw_license_key" );
			var activateButton  = $( "[name='wpssw_license_activate']" );

			// Add event listener to the activate button.
			activateButton.on(
				"click",
				function (event) {
					// Check if the license key input is empty.
					if (licenseKeyInput.val().trim() === "") {
						event.preventDefault(); // Prevent form submission.
						alert( "Please enter a license key." ); // Display an error message or take any desired action.
					}
				}
			);
		}
	);
	$( document ).ready(
		function () {
			var licensekey   = $( "#wpssw_license_key" ).val();
			var client_token = "";
			if ($( "#client_token" ).length > 0) {
				client_token = $( "#client_token" ).val();
			}
			var isactive = false;
			if ($( '[name="wpssw_license_activate"]' ).length > 0) {
				isactive = true;
			}
			var tokenError = $( "#token-error" ).val();

			if (licensekey == "" || isactive) {
				wpsswNavTab( event, "wpssw-nav-license" );
				$( ".wpssw-nav-license" ).addClass( "active" );
				$( ".wpssw-nav-googleapi" ).prop( "disabled", true );
				$( ".wpssw-nav-settings" ).prop( "disabled", true );
			} else if (client_token == "" || 1 === parseInt( tokenError )) {
				wpsswNavTab( event, "wpssw-nav-googleapi" );
				$( ".wpssw-nav-googleapi" ).addClass( "active" );
			} else {
				wpsswNavTab( event, "wpssw-nav-settings" );
				$( ".wpssw-nav-settings" ).addClass( "active" );
				var activeTab = getParameterByName( "tab" );
				if (activeTab != null) {
					var currenttab = activeTab;
					if ("wpssw-nav-license" == activeTab) {
						activeTab = "order-settings";
					}
					wpsswTab( event, activeTab );
					if ("wpssw-nav-license" == currenttab) {
						$( "button.order-settings" ).addClass( "active" );
					} else {
						$( "button." + currenttab ).addClass( "active" );
					}
				} else {
					var classNm = "button.order-settings";
					$( classNm ).addClass( "active" );
				}
			}
			if ($( "#error-message" ).length > 0) {
				var message = $( "#error-message" ).val();
				$( ".alert-messages .container" ).append(
					'<div class="alert alert-danger fade in alert-dismissible" role="alert"><svg width="32" height="32" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><defs><style>.cls-1{fill:none;stroke:#721c24;stroke-linecap:round;stroke-linejoin:round;stroke-width:20px;}</style></defs><g data-name="Layer 2" id="Layer_2"><g data-name="E410, Error, Media, media player, multimedia" id="E410_Error_Media_media_player_multimedia"><circle class="cls-1" cx="256" cy="256" r="246"/><line class="cls-1" x1="371.47" x2="140.53" y1="140.53" y2="371.47"/><line class="cls-1" x1="371.47" x2="140.53" y1="371.47" y2="140.53"/></g></g></svg>' +
					message +
					'<a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a></div>'
				);
			}
			if ($( "#success-message" ).length > 0) {
				var message = $( "#success-message" ).val();
				$( ".alert-messages .container" ).append(
					'<div class="alert alert-success fade in alert-dismissible" role="alert"><svg fill="#000000" height="32" width="32" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"  viewBox="0 0 52 52" xml:space="preserve"><g><path d="M26,0C11.664,0,0,11.663,0,26s11.664,26,26,26s26-11.663,26-26S40.336,0,26,0z M26,50C12.767,50,2,39.233,2,26S12.767,2,26,2s24,10.767,24,24S39.233,50,26,50z"/><path d="M38.252,15.336l-15.369,17.29l-9.259-7.407c-0.43-0.345-1.061-0.274-1.405,0.156c-0.345,0.432-0.275,1.061,0.156,1.406l10,8C22.559,34.928,22.78,35,23,35c0.276,0,0.551-0.114,0.748-0.336l16-18c0.367-0.412,0.33-1.045-0.083-1.411C39.251,14.885,38.62,14.922,38.252,15.336z"/></g></svg>' +
					message +
					'<a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a></div>'
				);
			}
			if ($( "#info-message" ).length > 0) {
				var message = $( "#info-message" ).val();
				$( ".alert-messages .container" ).append(
					'<div class="alert alert-info fade in alert-dismissible"><svg fill="#000000" width="32" height="32" viewBox="0 0 1920 1920" xmlns="http://www.w3.org/2000/svg"><path d="M960 0c530.193 0 960 429.807 960 960s-429.807 960-960 960S0 1490.193 0 960 429.807 0 960 0Zm0 101.053c-474.384 0-858.947 384.563-858.947 858.947S485.616 1818.947 960 1818.947 1818.947 1434.384 1818.947 960 1434.384 101.053 960 101.053Zm-42.074 626.795c-85.075 39.632-157.432 107.975-229.844 207.898-10.327 14.249-10.744 22.907-.135 30.565 7.458 5.384 11.792 3.662 22.656-7.928 1.453-1.562 1.453-1.562 2.94-3.174 9.391-10.17 16.956-18.8 33.115-37.565 53.392-62.005 79.472-87.526 120.003-110.867 35.075-20.198 65.9 9.485 60.03 47.471-1.647 10.664-4.483 18.534-11.791 35.432-2.907 6.722-4.133 9.646-5.496 13.23-13.173 34.63-24.269 63.518-47.519 123.85l-1.112 2.886c-7.03 18.242-7.03 18.242-14.053 36.48-30.45 79.138-48.927 127.666-67.991 178.988l-1.118 3.008a10180.575 10180.575 0 0 0-10.189 27.469c-21.844 59.238-34.337 97.729-43.838 138.668-1.484 6.37-1.484 6.37-2.988 12.845-5.353 23.158-8.218 38.081-9.82 53.42-2.77 26.522-.543 48.24 7.792 66.493 9.432 20.655 29.697 35.43 52.819 38.786 38.518 5.592 75.683 5.194 107.515-2.048 17.914-4.073 35.638-9.405 53.03-15.942 50.352-18.932 98.861-48.472 145.846-87.52 41.11-34.26 80.008-76 120.788-127.872 3.555-4.492 3.555-4.492 7.098-8.976 12.318-15.707 18.352-25.908 20.605-36.683 2.45-11.698-7.439-23.554-15.343-19.587-3.907 1.96-7.993 6.018-14.22 13.872-4.454 5.715-6.875 8.77-9.298 11.514-9.671 10.95-19.883 22.157-30.947 33.998-18.241 19.513-36.775 38.608-63.656 65.789-13.69 13.844-30.908 25.947-49.42 35.046-29.63 14.559-56.358-3.792-53.148-36.635 2.118-21.681 7.37-44.096 15.224-65.767 17.156-47.367 31.183-85.659 62.216-170.048 13.459-36.6 19.27-52.41 26.528-72.201 21.518-58.652 38.696-105.868 55.04-151.425 20.19-56.275 31.596-98.224 36.877-141.543 3.987-32.673-5.103-63.922-25.834-85.405-22.986-23.816-55.68-34.787-96.399-34.305-45.053.535-97.607 15.256-145.963 37.783Zm308.381-388.422c-80.963-31.5-178.114 22.616-194.382 108.33-11.795 62.124 11.412 115.76 58.78 138.225 93.898 44.531 206.587-26.823 206.592-130.826.005-57.855-24.705-97.718-70.99-115.729Z" fill-rule="evenodd"/></svg>' +
					message +
					'<a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a></div>'
				);
			}
		}
	);
	$( window ).load(
		function () {
			$( ".synctr" ).hide();
			$( ".ord_import_row" ).hide();
			$( ".wpssw_crud_ord_row" ).hide();
			$( ".cust_import_row" ).hide();
			$( ".wpssw_crud_cust_row" ).hide();
			$( "#wpssw-headers-notice" ).hide();

			$( "#sortable" ).sortable(
				{
					disabled: false,
				}
			);
			$( "#product-sortable" ).sortable(
				{
					disabled: true,
				}
			);
			$( "#productcategory-sortable" ).sortable(
				{
					disabled: true,
				}
			);
			$( "#woo-product-sortable" ).sortable(
				{
					disabled: false,
				}
			);
			$( "#woo-customer-sortable" ).sortable(
				{
					disabled: false,
				}
			);
			$( "#woo-coupon-sortable" ).sortable(
				{
					disabled: false,
				}
			);
			$( "#woo-event-sortable" ).sortable(
				{
					disabled: false,
				}
			);
			if ($( "#order_settings_checkbox" ).is( ":checked" )) {
				$( ".ord_spreadsheet_row" ).show();
			} else {
				$( ".ord_spreadsheet_row" ).hide();
			}
			if ($( "#product_settings_checkbox" ).is( ":checked" )) {
				$( ".prd_spreadsheet_row" ).show();
			} else {
				$( ".prd_spreadsheet_row" ).hide();
			}
			if ($( "#customer_settings_checkbox" ).is( ":checked" )) {
				$( ".cust_spreadsheet_row" ).show();
			} else {
				$( ".cust_spreadsheet_row" ).hide();
			}
			if ($( "#coupon_settings_checkbox" ).is( ":checked" )) {
				$( ".coupon_spreadsheet_row" ).show();
			} else {
				$( ".coupon_spreadsheet_row" ).hide();
			}
			if ($( "#event_settings_checkbox" ).is( ":checked" )) {
				$( ".event_spreadsheet_row" ).show();
			} else {
				$( ".event_spreadsheet_row" ).hide();
			}

			if ($( "#prdassheetheaders" ).is( ":checked" )) {
				$( ".td-prd-wpssw-headers" ).show();
				$( ".td-prd-append-after" ).show();
			} else {
				$( ".td-prd-wpssw-headers" ).hide();
				$( ".td-prd-append-after" ).hide();
			}
			if ($( "#product_category_select" ).is( ":checked" )) {
				$( ".td-producat-wpssw" ).show();
			} else {
				$( ".td-producat-wpssw" ).hide();
			}
			if ($( "#color_code" ).is( ":checked" )) {
				$( ".bg-color-oddeven" ).show();
			} else {
				$( ".bg-color-oddeven" ).hide();
			}
			var prevSheetId = $( "#woocommerce_spreadsheet" ).val();
			var rowData     = String(
				$( "input[type=radio][name=header_format]:checked" ).val()
			);
			if (String( rowData ) === "productwise" && $( "#order_settings_checkbox" ).is( ":checked" ) ) {
				$( ".repeat_checkbox" ).show();
			} else {
				$( ".repeat_checkbox" ).hide();
			}
			if (prevSheetId != "") {
				$( ".synctr" ).show();
				$( "#wpssw-headers-notice" ).show();
			}

			if ($( "#import_checkbox" ).is( ":checked" )) {
				$( ".wpssw_crud_row.wpssw_all_crud_row" ).show();
				if (
				$( "#insert_checkbox" ).is( ":checked" ) ||
				$( "#update_checkbox" ).is( ":checked" ) ||
				$( "#delete_checkbox" ).is( ":checked" )
				) {
					$( ".prd_import_row.import_row" ).show();
				} else {
					$( ".prd_import_row.import_row" ).hide();
				}
			} else {
				$( ".wpssw_crud_row" ).hide();
			}
			if ($( "#import_order_checkbox" ).is( ":checked" )) {
				$( ".wpssw_crud_ord_row" ).show();
				if (
				$( "#insert_order_checkbox" ).is( ":checked" ) ||
				$( "#update_order_checkbox" ).is( ":checked" ) ||
				$( "#delete_order_checkbox" ).is( ":checked" )
				) {
					$( ".ord_import_row" ).show();
				} else {
					$( ".ord_import_row" ).hide();
				}
			} else {
				$( ".ord_import_row" ).hide();
				$( ".wpssw_crud_ord_row" ).hide();
			}
			if ($( "#import_customer_checkbox" ).is( ":checked" )) {
				$( ".wpssw_crud_cust_row.wpssw_all_crud_row" ).show();
				if (
				$( "#insert_customer_checkbox" ).is( ":checked" ) ||
				$( "#update_customer_checkbox" ).is( ":checked" ) ||
				$( "#delete_customer_checkbox" ).is( ":checked" )
				) {
					$( ".cust_import_row.import_row" ).show();
				} else {
					$( ".cust_import_row.import_row" ).hide();
				}
			} else {
				$( ".wpssw_crud_cust_row.wpssw_all_crud_row" ).hide();
				$( ".cust_import_row.import_row" ).hide();
			}
			if ($( "#import_coupon_checkbox" ).is( ":checked" )) {
				$( ".wpssw_crud_coupon_row.wpssw_all_crud_row" ).show();
				if (
				$( "#insert_coupon_checkbox" ).is( ":checked" ) ||
				$( "#update_coupon_checkbox" ).is( ":checked" ) ||
				$( "#delete_coupon_checkbox" ).is( ":checked" )
				) {
					$( ".coupon_import_row.import_row" ).show();
				} else {
					$( ".coupon_import_row.import_row" ).hide();
				}
			} else {
				$( ".wpssw_crud_coupon_row.wpssw_all_crud_row" ).hide();
				$( ".coupon_import_row.import_row" ).hide();
			}

			if (
			$( "#customer_settings_checkbox" ).is( ":checked" ) &&
			$( "#import_customer_checkbox" ).is( ":checked" )
			) {
				$( ".wpssw_crud_cust_row" ).show();
			} else {
				$( ".wpssw_crud_cust_row" ).hide();
			}
			if (
			$( "#coupon_settings_checkbox" ).is( ":checked" ) &&
			$( "#import_coupon_checkbox" ).is( ":checked" )
			) {
				$( ".wpssw_crud_coupon_row" ).show();
			} else {
				$( ".wpssw_crud_coupon_row" ).hide();
			}

			var i        = 1;
			var temp     = 0;
			var tblCount = $( "#mainform > table" ).length;
			$( "#mainform table" ).each(
				function () {
					if (parseInt( tblCount ) === i) {
						$( this ).addClass( "wpssw-section-last" );
					} else {
						if (tblCount > 5 && parseInt( i ) === 3) {
							$( this ).addClass( "wpssw-section-2" );
							temp = 1;
						} else {
							$( this ).addClass( "wpssw-section-" + (i - temp) );
						}
					}
					i++;
				}
			);
			$(
				".wpssw-section-4 label input[type='checkbox'],.wpssw-section-last label input[type='checkbox'],#product_names_as_sheet"
			).after( "<span class='checkbox-switch'></span>" );
		}
	);
	$( "#licence_submit" ).on(
		"click",
		function (e) {
			e.preventDefault();
			$( ".wpssw-license-result" ).html( "" );
			$( "#licence_submit" ).hide();
			$( "#licenceloader" ).show();
			$( "#licencetext" ).show();
			wpsswLicenseCheck( "activate" );
		}
	);
	$( ".tm-deactivate-license" ).on(
		"click",
		function (e) {
			e.preventDefault();
			wpsswLicenseCheck( "deactivate" );
		}
	);
	$( "#add_ctm_val" ).on(
		"click",
		function (e) {
			e.preventDefault();
			var cstVal = $( "#custom_headers_val" ).val();
			if (String( cstVal ) === "") {
				alert( "Please enter header name." );
				$( "#custom_headers_val" ).focus();
				return false;
			}
			var labelId = cstVal.replace( / /g, "_" ).toLowerCase();
			var Val     = $( "#custom_headers_val_dropdown option:selected" ).val();
			var cstVal1 = cstVal + ",(static_header)," + Val;
			$( "#sortable" ).append(
				'<li class="default-order-sheet ui-state-default ui-sortable-handle"><label for="' +
				labelId +
				'"><span class="orderSheet-left"><span class="wootextfield">' +
				cstVal +
				'</span><span class="ui-icon ui-icon-pencil wpssw-tooltio-link"><span class="pencil-icon"><svg width="12" height="12" viewBox="0 0 13 13" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M1.33333 11.6668H2.26667L8.01667 5.91677L7.08333 4.98343L1.33333 10.7334V11.6668ZM10.8667 4.9501L8.03333 2.1501L8.96667 1.21677C9.22222 0.961213 9.53611 0.833435 9.90833 0.833435C10.2806 0.833435 10.5944 0.961213 10.85 1.21677L11.7833 2.1501C12.0389 2.40566 12.1722 2.71399 12.1833 3.0751C12.1944 3.43621 12.0722 3.74455 11.8167 4.0001L10.8667 4.9501ZM9.9 5.93343L2.83333 13.0001H0V10.1668L7.06667 3.1001L9.9 5.93343Z" fill="#64748B"/></svg></span><span class="check-icon"><svg xmlns="http://www.w3.org/2000/svg"  viewBox="0 0 26 26" width="12" height="12"><path d="M 22.566406 4.730469 L 20.773438 3.511719 C 20.277344 3.175781 19.597656 3.304688 19.265625 3.796875 L 10.476563 16.757813 L 6.4375 12.71875 C 6.015625 12.296875 5.328125 12.296875 4.90625 12.71875 L 3.371094 14.253906 C 2.949219 14.675781 2.949219 15.363281 3.371094 15.789063 L 9.582031 22 C 9.929688 22.347656 10.476563 22.613281 10.96875 22.613281 C 11.460938 22.613281 11.957031 22.304688 12.277344 21.839844 L 22.855469 6.234375 C 23.191406 5.742188 23.0625 5.066406 22.566406 4.730469 Z" fill="#64748B"/></svg></span><span class="tooltip-text edit-tooltip">Edit</span><span class="tooltip-text update-tooltip">Update</span></span></span><span class="orderSheet-right"><input type="checkbox" name="header_fields_custom[]" value="' +
				cstVal +
				'" class="headers_chk1" hidden="true" checked><input type="checkbox" name="header_fields_static[]" value="' +
				cstVal +
				'" hidden="true" checked><input type="checkbox" name="header_fields[]" id="' +
				labelId +
				'" class="headers_chk" value="' +
				cstVal +
				'" checked><input type="checkbox" name="wpssw_static_header_values[]" value="' +
				cstVal1 +
				'" hidden="true" checked><span class="checkbox-switch-new"></span><span class="ui-icon ui-icon-caret-2-n-s wpssw-tooltio-link"><svg width="17" height="16" viewBox="0 0 17 16" fill="none" xmlns="http://www.w3.org/2000/svg"><mask id="mask0_384_3228" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="0" y="0" width="17" height="16"><rect x="0.5" width="16" height="16" fill="#D9D9D9"/></mask><g mask="url(#mask0_384_3228)"><path d="M5.95875 13.67C5.55759 13.67 5.21418 13.5272 4.92851 13.2415C4.64284 12.9558 4.5 12.6124 4.5 12.2113C4.5 11.8101 4.64284 11.4667 4.92851 11.181C5.21418 10.8953 5.55759 10.7525 5.95875 10.7525C6.35991 10.7525 6.70332 10.8953 6.98899 11.181C7.27466 11.4667 7.4175 11.8101 7.4175 12.2113C7.4175 12.6124 7.27466 12.9558 6.98899 13.2415C6.70332 13.5272 6.35991 13.67 5.95875 13.67ZM10.335 13.67C9.93384 13.67 9.59043 13.5272 9.30476 13.2415C9.01909 12.9558 8.87625 12.6124 8.87625 12.2113C8.87625 11.8101 9.01909 11.4667 9.30476 11.181C9.59043 10.8953 9.93384 10.7525 10.335 10.7525C10.7362 10.7525 11.0796 10.8953 11.3652 11.181C11.6509 11.4667 11.7937 11.8101 11.7937 12.2113C11.7937 12.6124 11.6509 12.9558 11.3652 13.2415C11.0796 13.5272 10.7362 13.67 10.335 13.67ZM5.95875 9.29375C5.55759 9.29375 5.21418 9.15091 4.92851 8.86524C4.64284 8.57957 4.5 8.23616 4.5 7.835C4.5 7.43384 4.64284 7.09043 4.92851 6.80476C5.21418 6.51909 5.55759 6.37625 5.95875 6.37625C6.35991 6.37625 6.70332 6.51909 6.98899 6.80476C7.27466 7.09043 7.4175 7.43384 7.4175 7.835C7.4175 8.23616 7.27466 8.57957 6.98899 8.86524C6.70332 9.15091 6.35991 9.29375 5.95875 9.29375ZM10.335 9.29375C9.93384 9.29375 9.59043 9.15091 9.30476 8.86524C9.01909 8.57957 8.87625 8.23616 8.87625 7.835C8.87625 7.43384 9.01909 7.09043 9.30476 6.80476C9.59043 6.51909 9.93384 6.37625 10.335 6.37625C10.7362 6.37625 11.0796 6.51909 11.3652 6.80476C11.6509 7.09043 11.7937 7.43384 11.7937 7.835C11.7937 8.23616 11.6509 8.57957 11.3652 8.86524C11.0796 9.15091 10.7362 9.29375 10.335 9.29375ZM5.95875 4.9175C5.55759 4.9175 5.21418 4.77466 4.92851 4.48899C4.64284 4.20332 4.5 3.85991 4.5 3.45875C4.5 3.05759 4.64284 2.71418 4.92851 2.42851C5.21418 2.14284 5.55759 2 5.95875 2C6.35991 2 6.70332 2.14284 6.98899 2.42851C7.27466 2.71418 7.4175 3.05759 7.4175 3.45875C7.4175 3.85991 7.27466 4.20332 6.98899 4.48899C6.70332 4.77466 6.35991 4.9175 5.95875 4.9175ZM10.335 4.9175C9.93384 4.9175 9.59043 4.77466 9.30476 4.48899C9.01909 4.20332 8.87625 3.85991 8.87625 3.45875C8.87625 3.05759 9.01909 2.71418 9.30476 2.42851C9.59043 2.14284 9.93384 2 10.335 2C10.7362 2 11.0796 2.14284 11.3652 2.42851C11.6509 2.71418 11.7937 3.05759 11.7937 3.45875C11.7937 3.85991 11.6509 4.20332 11.3652 4.48899C11.0796 4.77466 10.7362 4.9175 10.335 4.9175Z" fill="#64748B"/></g></svg><span class="tooltip-text">Drag & Drop</span></span></span></label></li>'
			);
			$( "#custom_headers_val" ).val( "" );
		}
	);
	$( "#add_product_ctm_val" ).on(
		"click",
		function (e) {
			e.preventDefault();
			var cstVal = $( "#custom_product_headers_val" ).val();
			if (String( cstVal ) === "") {
				alert( "Please enter header name." );
				$( "#custom_product_headers_val" ).focus();
				return false;
			}
			var labelId = cstVal.replace( / /g, "_" ).toLowerCase();
			var Val     = $( "#custom_product_headers_val_dropdown option:selected" ).val();
			var cstVal1 = cstVal + ",(static_header)," + Val;
			$( "#woo-product-sortable" ).append(
				'<li class="default-order-sheet ui-state-default ui-sortable-handle"><label for="' +
				labelId +
				'"><span class="orderSheet-left"> <span class="wootextfield">' +
				cstVal +
				'</span><span class="ui-icon ui-icon-pencil wpssw-tooltio-link"><span class="pencil-icon"><svg width="12" height="12" viewBox="0 0 13 13" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M1.33333 11.6668H2.26667L8.01667 5.91677L7.08333 4.98343L1.33333 10.7334V11.6668ZM10.8667 4.9501L8.03333 2.1501L8.96667 1.21677C9.22222 0.961213 9.53611 0.833435 9.90833 0.833435C10.2806 0.833435 10.5944 0.961213 10.85 1.21677L11.7833 2.1501C12.0389 2.40566 12.1722 2.71399 12.1833 3.0751C12.1944 3.43621 12.0722 3.74455 11.8167 4.0001L10.8667 4.9501ZM9.9 5.93343L2.83333 13.0001H0V10.1668L7.06667 3.1001L9.9 5.93343Z" fill="#64748B"/></svg></span><span class="check-icon"><svg xmlns="http://www.w3.org/2000/svg"  viewBox="0 0 26 26" width="12" height="12"><path d="M 22.566406 4.730469 L 20.773438 3.511719 C 20.277344 3.175781 19.597656 3.304688 19.265625 3.796875 L 10.476563 16.757813 L 6.4375 12.71875 C 6.015625 12.296875 5.328125 12.296875 4.90625 12.71875 L 3.371094 14.253906 C 2.949219 14.675781 2.949219 15.363281 3.371094 15.789063 L 9.582031 22 C 9.929688 22.347656 10.476563 22.613281 10.96875 22.613281 C 11.460938 22.613281 11.957031 22.304688 12.277344 21.839844 L 22.855469 6.234375 C 23.191406 5.742188 23.0625 5.066406 22.566406 4.730469 Z" fill="#64748B"/></svg></span><span class="tooltip-text edit-tooltip">Edit</span><span class="tooltip-text update-tooltip">Update</span></span></span> <span class="orderSheet-right"><input type="checkbox" name="wooproduct_custom[]" value="' +
				cstVal +
				'" class="woo-pro-headers-chk1" hidden="true" checked><input type="checkbox" name="product_header_fields_static[]" value="' +
				cstVal +
				'" hidden="true" checked><input type="checkbox" name="wooproduct_header_list[]" id="' +
				labelId +
				'" class="woo-pro-headers-chk" value="' +
				cstVal +
				'" checked><input type="checkbox" name="wpssw_static_product_header_values[]" value="' +
				cstVal1 +
				'" hidden="true" checked><span class="checkbox-switch-new"></span><span class="ui-icon ui-icon-caret-2-n-s wpssw-tooltio-link"><svg width="17" height="16" viewBox="0 0 17 16" fill="none" xmlns="http://www.w3.org/2000/svg"><mask id="mask0_384_3228" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="0" y="0" width="17" height="16"><rect x="0.5" width="16" height="16" fill="#D9D9D9"/></mask><g mask="url(#mask0_384_3228)"><path d="M5.95875 13.67C5.55759 13.67 5.21418 13.5272 4.92851 13.2415C4.64284 12.9558 4.5 12.6124 4.5 12.2113C4.5 11.8101 4.64284 11.4667 4.92851 11.181C5.21418 10.8953 5.55759 10.7525 5.95875 10.7525C6.35991 10.7525 6.70332 10.8953 6.98899 11.181C7.27466 11.4667 7.4175 11.8101 7.4175 12.2113C7.4175 12.6124 7.27466 12.9558 6.98899 13.2415C6.70332 13.5272 6.35991 13.67 5.95875 13.67ZM10.335 13.67C9.93384 13.67 9.59043 13.5272 9.30476 13.2415C9.01909 12.9558 8.87625 12.6124 8.87625 12.2113C8.87625 11.8101 9.01909 11.4667 9.30476 11.181C9.59043 10.8953 9.93384 10.7525 10.335 10.7525C10.7362 10.7525 11.0796 10.8953 11.3652 11.181C11.6509 11.4667 11.7937 11.8101 11.7937 12.2113C11.7937 12.6124 11.6509 12.9558 11.3652 13.2415C11.0796 13.5272 10.7362 13.67 10.335 13.67ZM5.95875 9.29375C5.55759 9.29375 5.21418 9.15091 4.92851 8.86524C4.64284 8.57957 4.5 8.23616 4.5 7.835C4.5 7.43384 4.64284 7.09043 4.92851 6.80476C5.21418 6.51909 5.55759 6.37625 5.95875 6.37625C6.35991 6.37625 6.70332 6.51909 6.98899 6.80476C7.27466 7.09043 7.4175 7.43384 7.4175 7.835C7.4175 8.23616 7.27466 8.57957 6.98899 8.86524C6.70332 9.15091 6.35991 9.29375 5.95875 9.29375ZM10.335 9.29375C9.93384 9.29375 9.59043 9.15091 9.30476 8.86524C9.01909 8.57957 8.87625 8.23616 8.87625 7.835C8.87625 7.43384 9.01909 7.09043 9.30476 6.80476C9.59043 6.51909 9.93384 6.37625 10.335 6.37625C10.7362 6.37625 11.0796 6.51909 11.3652 6.80476C11.6509 7.09043 11.7937 7.43384 11.7937 7.835C11.7937 8.23616 11.6509 8.57957 11.3652 8.86524C11.0796 9.15091 10.7362 9.29375 10.335 9.29375ZM5.95875 4.9175C5.55759 4.9175 5.21418 4.77466 4.92851 4.48899C4.64284 4.20332 4.5 3.85991 4.5 3.45875C4.5 3.05759 4.64284 2.71418 4.92851 2.42851C5.21418 2.14284 5.55759 2 5.95875 2C6.35991 2 6.70332 2.14284 6.98899 2.42851C7.27466 2.71418 7.4175 3.05759 7.4175 3.45875C7.4175 3.85991 7.27466 4.20332 6.98899 4.48899C6.70332 4.77466 6.35991 4.9175 5.95875 4.9175ZM10.335 4.9175C9.93384 4.9175 9.59043 4.77466 9.30476 4.48899C9.01909 4.20332 8.87625 3.85991 8.87625 3.45875C8.87625 3.05759 9.01909 2.71418 9.30476 2.42851C9.59043 2.14284 9.93384 2 10.335 2C10.7362 2 11.0796 2.14284 11.3652 2.42851C11.6509 2.71418 11.7937 3.05759 11.7937 3.45875C11.7937 3.85991 11.6509 4.20332 11.3652 4.48899C11.0796 4.77466 10.7362 4.9175 10.335 4.9175Z" fill="#64748B"/></g></svg><span class="tooltip-text">Drag & Drop</span></span></span></label></li>'
			);
			$( "#custom_product_headers_val" ).val( "" );
		}
	);
	$( "#add_customer_ctm_val" ).on(
		"click",
		function (e) {
			e.preventDefault();
			var cstVal = $( "#custom_customer_headers_val" ).val();
			if (String( cstVal ) === "") {
				alert( "Please enter header name." );
				$( "#custom_customer_headers_val" ).focus();
				return false;
			}
			var labelId = cstVal.replace( / /g, "_" ).toLowerCase();
			var Val     = $( "#custom_customer_headers_val_dropdown option:selected" ).val();
			var cstVal1 = cstVal + ",(static_header)," + Val;
			$( "#woo-customer-sortable" ).append(
				'<li class="default-order-sheet ui-state-default ui-sortable-handle"><label for="' +
				labelId +
				'"><span class="orderSheet-left"><span class="wootextfield">' +
				cstVal +
				'</span><span class="ui-icon ui-icon-pencil wpssw-tooltio-link"><span class="pencil-icon"><svg width="12" height="12" viewBox="0 0 13 13" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M1.33333 11.6668H2.26667L8.01667 5.91677L7.08333 4.98343L1.33333 10.7334V11.6668ZM10.8667 4.9501L8.03333 2.1501L8.96667 1.21677C9.22222 0.961213 9.53611 0.833435 9.90833 0.833435C10.2806 0.833435 10.5944 0.961213 10.85 1.21677L11.7833 2.1501C12.0389 2.40566 12.1722 2.71399 12.1833 3.0751C12.1944 3.43621 12.0722 3.74455 11.8167 4.0001L10.8667 4.9501ZM9.9 5.93343L2.83333 13.0001H0V10.1668L7.06667 3.1001L9.9 5.93343Z" fill="#64748B"/></svg></span><span class="check-icon"><svg xmlns="http://www.w3.org/2000/svg"  viewBox="0 0 26 26" width="12" height="12"><path d="M 22.566406 4.730469 L 20.773438 3.511719 C 20.277344 3.175781 19.597656 3.304688 19.265625 3.796875 L 10.476563 16.757813 L 6.4375 12.71875 C 6.015625 12.296875 5.328125 12.296875 4.90625 12.71875 L 3.371094 14.253906 C 2.949219 14.675781 2.949219 15.363281 3.371094 15.789063 L 9.582031 22 C 9.929688 22.347656 10.476563 22.613281 10.96875 22.613281 C 11.460938 22.613281 11.957031 22.304688 12.277344 21.839844 L 22.855469 6.234375 C 23.191406 5.742188 23.0625 5.066406 22.566406 4.730469 Z" fill="#64748B"/></svg></span><span class="tooltip-text edit-tooltip">Edit</span><span class="tooltip-text update-tooltip">Update</span></span></span><span class="orderSheet-right"><input type="checkbox" name="woocustomer_custom[]" value="' +
				cstVal +
				'" class="woo-cust-headers-chk1" hidden="true" checked><input type="checkbox" name="customer_header_fields_static[]" value="' +
				cstVal +
				'" hidden="true" checked><input type="checkbox" name="woocustomer_header_list[]" id="' +
				labelId +
				'" class="woo-cust-headers-chk" value="' +
				cstVal +
				'" checked><input type="checkbox" name="wpssw_static_customer_header_values[]" value="' +
				cstVal1 +
				'" hidden="true" checked><span class="checkbox-switch-new"></span><span class="ui-icon ui-icon-caret-2-n-s wpssw-tooltio-link"><svg width="17" height="16" viewBox="0 0 17 16" fill="none" xmlns="http://www.w3.org/2000/svg"><mask id="mask0_384_3228" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="0" y="0" width="17" height="16"><rect x="0.5" width="16" height="16" fill="#D9D9D9"/></mask><g mask="url(#mask0_384_3228)"><path d="M5.95875 13.67C5.55759 13.67 5.21418 13.5272 4.92851 13.2415C4.64284 12.9558 4.5 12.6124 4.5 12.2113C4.5 11.8101 4.64284 11.4667 4.92851 11.181C5.21418 10.8953 5.55759 10.7525 5.95875 10.7525C6.35991 10.7525 6.70332 10.8953 6.98899 11.181C7.27466 11.4667 7.4175 11.8101 7.4175 12.2113C7.4175 12.6124 7.27466 12.9558 6.98899 13.2415C6.70332 13.5272 6.35991 13.67 5.95875 13.67ZM10.335 13.67C9.93384 13.67 9.59043 13.5272 9.30476 13.2415C9.01909 12.9558 8.87625 12.6124 8.87625 12.2113C8.87625 11.8101 9.01909 11.4667 9.30476 11.181C9.59043 10.8953 9.93384 10.7525 10.335 10.7525C10.7362 10.7525 11.0796 10.8953 11.3652 11.181C11.6509 11.4667 11.7937 11.8101 11.7937 12.2113C11.7937 12.6124 11.6509 12.9558 11.3652 13.2415C11.0796 13.5272 10.7362 13.67 10.335 13.67ZM5.95875 9.29375C5.55759 9.29375 5.21418 9.15091 4.92851 8.86524C4.64284 8.57957 4.5 8.23616 4.5 7.835C4.5 7.43384 4.64284 7.09043 4.92851 6.80476C5.21418 6.51909 5.55759 6.37625 5.95875 6.37625C6.35991 6.37625 6.70332 6.51909 6.98899 6.80476C7.27466 7.09043 7.4175 7.43384 7.4175 7.835C7.4175 8.23616 7.27466 8.57957 6.98899 8.86524C6.70332 9.15091 6.35991 9.29375 5.95875 9.29375ZM10.335 9.29375C9.93384 9.29375 9.59043 9.15091 9.30476 8.86524C9.01909 8.57957 8.87625 8.23616 8.87625 7.835C8.87625 7.43384 9.01909 7.09043 9.30476 6.80476C9.59043 6.51909 9.93384 6.37625 10.335 6.37625C10.7362 6.37625 11.0796 6.51909 11.3652 6.80476C11.6509 7.09043 11.7937 7.43384 11.7937 7.835C11.7937 8.23616 11.6509 8.57957 11.3652 8.86524C11.0796 9.15091 10.7362 9.29375 10.335 9.29375ZM5.95875 4.9175C5.55759 4.9175 5.21418 4.77466 4.92851 4.48899C4.64284 4.20332 4.5 3.85991 4.5 3.45875C4.5 3.05759 4.64284 2.71418 4.92851 2.42851C5.21418 2.14284 5.55759 2 5.95875 2C6.35991 2 6.70332 2.14284 6.98899 2.42851C7.27466 2.71418 7.4175 3.05759 7.4175 3.45875C7.4175 3.85991 7.27466 4.20332 6.98899 4.48899C6.70332 4.77466 6.35991 4.9175 5.95875 4.9175ZM10.335 4.9175C9.93384 4.9175 9.59043 4.77466 9.30476 4.48899C9.01909 4.20332 8.87625 3.85991 8.87625 3.45875C8.87625 3.05759 9.01909 2.71418 9.30476 2.42851C9.59043 2.14284 9.93384 2 10.335 2C10.7362 2 11.0796 2.14284 11.3652 2.42851C11.6509 2.71418 11.7937 3.05759 11.7937 3.45875C11.7937 3.85991 11.6509 4.20332 11.3652 4.48899C11.0796 4.77466 10.7362 4.9175 10.335 4.9175Z" fill="#64748B"/></g></svg><span class="tooltip-text">Drag & Drop</span></span></span></label></li>'
			);
			$( "#custom_customer_headers_val" ).val( "" );
		}
	);
	$( document ).ready(
		function () {
			$( ".custom-input-div" ).hide();
			$( "#custom_header_action" ).on(
				"change",
				function () {
					if (this.checked) {
						$( ".custom-input-div" ).fadeIn();
					} else {
						$( ".custom-input-div" ).fadeOut();
					}
				}
			);
			$( ".custom-product-input-div" ).hide();
			$( "#custom_product_header_action" ).on(
				"change",
				function () {
					if (this.checked) {
						$( ".custom-product-input-div" ).fadeIn();
					} else {
						$( ".custom-product-input-div" ).fadeOut();
					}
				}
			);
			$( ".custom-customer-input-div" ).hide();
			$( "#custom_customer_header_action" ).on(
				"change",
				function () {
					if (this.checked) {
						$( ".custom-customer-input-div" ).fadeIn();
					} else {
						$( ".custom-customer-input-div" ).fadeOut();
					}
				}
			);
			$( "#import_checkbox" ).on(
				"change",
				function () {
					if (this.checked) {
						$( ".wpssw_crud_row.wpssw_all_crud_row" ).fadeIn();
						if (
						$( "#insert_checkbox" ).is( ":checked" ) ||
						$( "#update_checkbox" ).is( ":checked" ) ||
						$( "#delete_checkbox" ).is( ":checked" )
						) {
							$( ".prd_import_row.import_row" ).fadeIn();
						} else {
							$( ".prd_import_row.import_row" ).fadeOut();
						}
					} else {
						$( ".wpssw_crud_row" ).fadeOut();
						$( "#insert_checkbox" ).prop( "checked", false ); // Unchecks it.
						$( "#update_checkbox" ).prop( "checked", false ); // Unchecks it.
						$( "#delete_checkbox" ).prop( "checked", false ); // Unchecks it.
						$( "li.insertproduct" ).remove();
						$( "li.updateproduct" ).remove();
						$( "li.deleteproduct" ).remove();
					}
				}
			);
			$( "#insert_checkbox" ).on(
				"change",
				function () {
					if (this.checked) {
						var inserttext = "Insert";
						$( "#woo-product-sortable" ).append(
							'<li class="default-order-sheet ui-state-default ui-sortable-handle insertproduct"><label><span class="orderSheet-left"> <span class="wootextfield">' +
							inserttext +
							'</span> </span> <span class="orderSheet-right"><input type="checkbox" name="wooproduct_custom[]" value="' +
							inserttext +
							'" class="woo-pro-headers-chk1" hidden="true" checked><input type="checkbox" name="wooproduct_header_list[]" value="' +
							inserttext +
							'" id="woo-Insert" class="woo-pro-headers-chk" checked><span class="ui-icon ui-icon-caret-2-n-s wpssw-tooltio-link">   <svg width="17" height="16" viewBox="0 0 17 16" fill="none" xmlns="http://www.w3.org/2000/svg"> <mask id="mask0_384_3228" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="0" y="0" width="17" height="16"> <rect x="0.5" width="16" height="16" fill="#D9D9D9"/> </mask> <g mask="url(#mask0_384_3228)"> <path d="M5.95875 13.67C5.55759 13.67 5.21418 13.5272 4.92851 13.2415C4.64284 12.9558 4.5 12.6124 4.5 12.2113C4.5 11.8101 4.64284 11.4667 4.92851 11.181C5.21418 10.8953 5.55759 10.7525 5.95875 10.7525C6.35991 10.7525 6.70332 10.8953 6.98899 11.181C7.27466 11.4667 7.4175 11.8101 7.4175 12.2113C7.4175 12.6124 7.27466 12.9558 6.98899 13.2415C6.70332 13.5272 6.35991 13.67 5.95875 13.67ZM10.335 13.67C9.93384 13.67 9.59043 13.5272 9.30476 13.2415C9.01909 12.9558 8.87625 12.6124 8.87625 12.2113C8.87625 11.8101 9.01909 11.4667 9.30476 11.181C9.59043 10.8953 9.93384 10.7525 10.335 10.7525C10.7362 10.7525 11.0796 10.8953 11.3652 11.181C11.6509 11.4667 11.7937 11.8101 11.7937 12.2113C11.7937 12.6124 11.6509 12.9558 11.3652 13.2415C11.0796 13.5272 10.7362 13.67 10.335 13.67ZM5.95875 9.29375C5.55759 9.29375 5.21418 9.15091 4.92851 8.86524C4.64284 8.57957 4.5 8.23616 4.5 7.835C4.5 7.43384 4.64284 7.09043 4.92851 6.80476C5.21418 6.51909 5.55759 6.37625 5.95875 6.37625C6.35991 6.37625 6.70332 6.51909 6.98899 6.80476C7.27466 7.09043 7.4175 7.43384 7.4175 7.835C7.4175 8.23616 7.27466 8.57957 6.98899 8.86524C6.70332 9.15091 6.35991 9.29375 5.95875 9.29375ZM10.335 9.29375C9.93384 9.29375 9.59043 9.15091 9.30476 8.86524C9.01909 8.57957 8.87625 8.23616 8.87625 7.835C8.87625 7.43384 9.01909 7.09043 9.30476 6.80476C9.59043 6.51909 9.93384 6.37625 10.335 6.37625C10.7362 6.37625 11.0796 6.51909 11.3652 6.80476C11.6509 7.09043 11.7937 7.43384 11.7937 7.835C11.7937 8.23616 11.6509 8.57957 11.3652 8.86524C11.0796 9.15091 10.7362 9.29375 10.335 9.29375ZM5.95875 4.9175C5.55759 4.9175 5.21418 4.77466 4.92851 4.48899C4.64284 4.20332 4.5 3.85991 4.5 3.45875C4.5 3.05759 4.64284 2.71418 4.92851 2.42851C5.21418 2.14284 5.55759 2 5.95875 2C6.35991 2 6.70332 2.14284 6.98899 2.42851C7.27466 2.71418 7.4175 3.05759 7.4175 3.45875C7.4175 3.85991 7.27466 4.20332 6.98899 4.48899C6.70332 4.77466 6.35991 4.9175 5.95875 4.9175ZM10.335 4.9175C9.93384 4.9175 9.59043 4.77466 9.30476 4.48899C9.01909 4.20332 8.87625 3.85991 8.87625 3.45875C8.87625 3.05759 9.01909 2.71418 9.30476 2.42851C9.59043 2.14284 9.93384 2 10.335 2C10.7362 2 11.0796 2.14284 11.3652 2.42851C11.6509 2.71418 11.7937 3.05759 11.7937 3.45875C11.7937 3.85991 11.6509 4.20332 11.3652 4.48899C11.0796 4.77466 10.7362 4.9175 10.335 4.9175Z" fill="#64748B"/></g></svg></svg><span class="tooltip-text">Drag & Drop</span></span></span></label></li>'
						);
					} else {
						$( "li.insertproduct" ).remove();
					}
				}
			);
			$( "#update_checkbox" ).on(
				"change",
				function () {
					if (this.checked) {
						var updatetext = "Update";
						$( "#woo-product-sortable" ).append(
							'<li class="default-order-sheet ui-state-default ui-sortable-handle updateproduct"><label><span class="orderSheet-left"> <span class="wootextfield">' +
							updatetext +
							'</span> </span> <span class="orderSheet-right"><input type="checkbox" name="wooproduct_custom[]" value="' +
							updatetext +
							'" class="woo-pro-headers-chk1" hidden="true" checked><input type="checkbox" name="wooproduct_header_list[]" value="' +
							updatetext +
							'" id="woo-Insert" class="woo-pro-headers-chk" checked><span class="ui-icon ui-icon-caret-2-n-s wpssw-tooltio-link">   <svg width="17" height="16" viewBox="0 0 17 16" fill="none" xmlns="http://www.w3.org/2000/svg"> <mask id="mask0_384_3228" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="0" y="0" width="17" height="16"> <rect x="0.5" width="16" height="16" fill="#D9D9D9"/> </mask> <g mask="url(#mask0_384_3228)"> <path d="M5.95875 13.67C5.55759 13.67 5.21418 13.5272 4.92851 13.2415C4.64284 12.9558 4.5 12.6124 4.5 12.2113C4.5 11.8101 4.64284 11.4667 4.92851 11.181C5.21418 10.8953 5.55759 10.7525 5.95875 10.7525C6.35991 10.7525 6.70332 10.8953 6.98899 11.181C7.27466 11.4667 7.4175 11.8101 7.4175 12.2113C7.4175 12.6124 7.27466 12.9558 6.98899 13.2415C6.70332 13.5272 6.35991 13.67 5.95875 13.67ZM10.335 13.67C9.93384 13.67 9.59043 13.5272 9.30476 13.2415C9.01909 12.9558 8.87625 12.6124 8.87625 12.2113C8.87625 11.8101 9.01909 11.4667 9.30476 11.181C9.59043 10.8953 9.93384 10.7525 10.335 10.7525C10.7362 10.7525 11.0796 10.8953 11.3652 11.181C11.6509 11.4667 11.7937 11.8101 11.7937 12.2113C11.7937 12.6124 11.6509 12.9558 11.3652 13.2415C11.0796 13.5272 10.7362 13.67 10.335 13.67ZM5.95875 9.29375C5.55759 9.29375 5.21418 9.15091 4.92851 8.86524C4.64284 8.57957 4.5 8.23616 4.5 7.835C4.5 7.43384 4.64284 7.09043 4.92851 6.80476C5.21418 6.51909 5.55759 6.37625 5.95875 6.37625C6.35991 6.37625 6.70332 6.51909 6.98899 6.80476C7.27466 7.09043 7.4175 7.43384 7.4175 7.835C7.4175 8.23616 7.27466 8.57957 6.98899 8.86524C6.70332 9.15091 6.35991 9.29375 5.95875 9.29375ZM10.335 9.29375C9.93384 9.29375 9.59043 9.15091 9.30476 8.86524C9.01909 8.57957 8.87625 8.23616 8.87625 7.835C8.87625 7.43384 9.01909 7.09043 9.30476 6.80476C9.59043 6.51909 9.93384 6.37625 10.335 6.37625C10.7362 6.37625 11.0796 6.51909 11.3652 6.80476C11.6509 7.09043 11.7937 7.43384 11.7937 7.835C11.7937 8.23616 11.6509 8.57957 11.3652 8.86524C11.0796 9.15091 10.7362 9.29375 10.335 9.29375ZM5.95875 4.9175C5.55759 4.9175 5.21418 4.77466 4.92851 4.48899C4.64284 4.20332 4.5 3.85991 4.5 3.45875C4.5 3.05759 4.64284 2.71418 4.92851 2.42851C5.21418 2.14284 5.55759 2 5.95875 2C6.35991 2 6.70332 2.14284 6.98899 2.42851C7.27466 2.71418 7.4175 3.05759 7.4175 3.45875C7.4175 3.85991 7.27466 4.20332 6.98899 4.48899C6.70332 4.77466 6.35991 4.9175 5.95875 4.9175ZM10.335 4.9175C9.93384 4.9175 9.59043 4.77466 9.30476 4.48899C9.01909 4.20332 8.87625 3.85991 8.87625 3.45875C8.87625 3.05759 9.01909 2.71418 9.30476 2.42851C9.59043 2.14284 9.93384 2 10.335 2C10.7362 2 11.0796 2.14284 11.3652 2.42851C11.6509 2.71418 11.7937 3.05759 11.7937 3.45875C11.7937 3.85991 11.6509 4.20332 11.3652 4.48899C11.0796 4.77466 10.7362 4.9175 10.335 4.9175Z" fill="#64748B"/></g></svg></svg><span class="tooltip-text">Drag & Drop</span></span></span></label></li>'
						);
					} else {
						$( "li.updateproduct" ).remove();
					}
				}
			);
			$( "#delete_checkbox" ).on(
				"change",
				function () {
					if (this.checked) {
						var deletetext = "Delete";
						$( "#woo-product-sortable" ).append(
							'<li class="default-order-sheet ui-state-default ui-sortable-handle deleteproduct"><label><span class="orderSheet-left"> <span class="wootextfield">' +
							deletetext +
							'</span> </span> <span class="orderSheet-right"><input type="checkbox" name="wooproduct_custom[]" value="' +
							deletetext +
							'" class="woo-pro-headers-chk1" hidden="true" checked><input type="checkbox" name="wooproduct_header_list[]" value="' +
							deletetext +
							'" id="woo-Insert" class="woo-pro-headers-chk" checked><span class="ui-icon ui-icon-caret-2-n-s wpssw-tooltio-link">   <svg width="17" height="16" viewBox="0 0 17 16" fill="none" xmlns="http://www.w3.org/2000/svg"> <mask id="mask0_384_3228" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="0" y="0" width="17" height="16"> <rect x="0.5" width="16" height="16" fill="#D9D9D9"/> </mask> <g mask="url(#mask0_384_3228)"> <path d="M5.95875 13.67C5.55759 13.67 5.21418 13.5272 4.92851 13.2415C4.64284 12.9558 4.5 12.6124 4.5 12.2113C4.5 11.8101 4.64284 11.4667 4.92851 11.181C5.21418 10.8953 5.55759 10.7525 5.95875 10.7525C6.35991 10.7525 6.70332 10.8953 6.98899 11.181C7.27466 11.4667 7.4175 11.8101 7.4175 12.2113C7.4175 12.6124 7.27466 12.9558 6.98899 13.2415C6.70332 13.5272 6.35991 13.67 5.95875 13.67ZM10.335 13.67C9.93384 13.67 9.59043 13.5272 9.30476 13.2415C9.01909 12.9558 8.87625 12.6124 8.87625 12.2113C8.87625 11.8101 9.01909 11.4667 9.30476 11.181C9.59043 10.8953 9.93384 10.7525 10.335 10.7525C10.7362 10.7525 11.0796 10.8953 11.3652 11.181C11.6509 11.4667 11.7937 11.8101 11.7937 12.2113C11.7937 12.6124 11.6509 12.9558 11.3652 13.2415C11.0796 13.5272 10.7362 13.67 10.335 13.67ZM5.95875 9.29375C5.55759 9.29375 5.21418 9.15091 4.92851 8.86524C4.64284 8.57957 4.5 8.23616 4.5 7.835C4.5 7.43384 4.64284 7.09043 4.92851 6.80476C5.21418 6.51909 5.55759 6.37625 5.95875 6.37625C6.35991 6.37625 6.70332 6.51909 6.98899 6.80476C7.27466 7.09043 7.4175 7.43384 7.4175 7.835C7.4175 8.23616 7.27466 8.57957 6.98899 8.86524C6.70332 9.15091 6.35991 9.29375 5.95875 9.29375ZM10.335 9.29375C9.93384 9.29375 9.59043 9.15091 9.30476 8.86524C9.01909 8.57957 8.87625 8.23616 8.87625 7.835C8.87625 7.43384 9.01909 7.09043 9.30476 6.80476C9.59043 6.51909 9.93384 6.37625 10.335 6.37625C10.7362 6.37625 11.0796 6.51909 11.3652 6.80476C11.6509 7.09043 11.7937 7.43384 11.7937 7.835C11.7937 8.23616 11.6509 8.57957 11.3652 8.86524C11.0796 9.15091 10.7362 9.29375 10.335 9.29375ZM5.95875 4.9175C5.55759 4.9175 5.21418 4.77466 4.92851 4.48899C4.64284 4.20332 4.5 3.85991 4.5 3.45875C4.5 3.05759 4.64284 2.71418 4.92851 2.42851C5.21418 2.14284 5.55759 2 5.95875 2C6.35991 2 6.70332 2.14284 6.98899 2.42851C7.27466 2.71418 7.4175 3.05759 7.4175 3.45875C7.4175 3.85991 7.27466 4.20332 6.98899 4.48899C6.70332 4.77466 6.35991 4.9175 5.95875 4.9175ZM10.335 4.9175C9.93384 4.9175 9.59043 4.77466 9.30476 4.48899C9.01909 4.20332 8.87625 3.85991 8.87625 3.45875C8.87625 3.05759 9.01909 2.71418 9.30476 2.42851C9.59043 2.14284 9.93384 2 10.335 2C10.7362 2 11.0796 2.14284 11.3652 2.42851C11.6509 2.71418 11.7937 3.05759 11.7937 3.45875C11.7937 3.85991 11.6509 4.20332 11.3652 4.48899C11.0796 4.77466 10.7362 4.9175 10.335 4.9175Z" fill="#64748B"/></g></svg></svg><span class="tooltip-text">Drag & Drop</span></span></span></label></li>'
						);
					} else {
						$( "li.deleteproduct" ).remove();
					}
				}
			);

			$( "#import_order_checkbox" ).on(
				"change",
				function () {
					if (this.checked) {
						$( ".wpssw_crud_ord_row" ).fadeIn();
						if (
						$( "#insert_order_checkbox" ).is( ":checked" ) ||
						$( "#update_order_checkbox" ).is( ":checked" ) ||
						$( "#delete_order_checkbox" ).is( ":checked" )
						) {
							$( ".ord_import_row" ).fadeIn();
						} else {
							$( ".ord_import_row" ).fadeOut();
						}
					} else {
						$( ".wpssw_crud_ord_row" ).fadeOut();
						$( ".ord_import_row" ).fadeOut();
						$( "#insert_order_checkbox" ).prop( "checked", false ); // Unchecks it.
						$( "#update_order_checkbox" ).prop( "checked", false ); // Unchecks it.
						$( "#delete_order_checkbox" ).prop( "checked", false ); // Unchecks it.
						$( "li.insertorder" ).remove();
						$( "li.updateorder" ).remove();
						$( "li.deleteorder" ).remove();
					}
				}
			);
			$( "#insert_order_checkbox" ).on(
				"change",
				function () {
					if (this.checked) {
						var inserttext = "Insert";
						$( "#sortable" ).append(
							'<li class="default-order-sheet ui-state-default ui-sortable-handle insertorder">  <label> <span class="orderSheet-left">  <span class="wootextfield">' +
							inserttext +
							'</span> </span> <span class="orderSheet-right"> <input type="checkbox" name="header_fields_custom[]" value="' +
							inserttext +
							'" class="headers_chk1" hidden="true" checked><input type="checkbox" name="header_fields[]" id="" class="headers_chk" value="' +
							inserttext +
							'" checked>  <span class="ui-icon ui-icon-caret-2-n-s wpssw-tooltio-link">   <svg width="17" height="16" viewBox="0 0 17 16" fill="none" xmlns="http://www.w3.org/2000/svg"> <mask id="mask0_384_3228" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="0" y="0" width="17" height="16"> <rect x="0.5" width="16" height="16" fill="#D9D9D9"/> </mask> <g mask="url(#mask0_384_3228)"> <path d="M5.95875 13.67C5.55759 13.67 5.21418 13.5272 4.92851 13.2415C4.64284 12.9558 4.5 12.6124 4.5 12.2113C4.5 11.8101 4.64284 11.4667 4.92851 11.181C5.21418 10.8953 5.55759 10.7525 5.95875 10.7525C6.35991 10.7525 6.70332 10.8953 6.98899 11.181C7.27466 11.4667 7.4175 11.8101 7.4175 12.2113C7.4175 12.6124 7.27466 12.9558 6.98899 13.2415C6.70332 13.5272 6.35991 13.67 5.95875 13.67ZM10.335 13.67C9.93384 13.67 9.59043 13.5272 9.30476 13.2415C9.01909 12.9558 8.87625 12.6124 8.87625 12.2113C8.87625 11.8101 9.01909 11.4667 9.30476 11.181C9.59043 10.8953 9.93384 10.7525 10.335 10.7525C10.7362 10.7525 11.0796 10.8953 11.3652 11.181C11.6509 11.4667 11.7937 11.8101 11.7937 12.2113C11.7937 12.6124 11.6509 12.9558 11.3652 13.2415C11.0796 13.5272 10.7362 13.67 10.335 13.67ZM5.95875 9.29375C5.55759 9.29375 5.21418 9.15091 4.92851 8.86524C4.64284 8.57957 4.5 8.23616 4.5 7.835C4.5 7.43384 4.64284 7.09043 4.92851 6.80476C5.21418 6.51909 5.55759 6.37625 5.95875 6.37625C6.35991 6.37625 6.70332 6.51909 6.98899 6.80476C7.27466 7.09043 7.4175 7.43384 7.4175 7.835C7.4175 8.23616 7.27466 8.57957 6.98899 8.86524C6.70332 9.15091 6.35991 9.29375 5.95875 9.29375ZM10.335 9.29375C9.93384 9.29375 9.59043 9.15091 9.30476 8.86524C9.01909 8.57957 8.87625 8.23616 8.87625 7.835C8.87625 7.43384 9.01909 7.09043 9.30476 6.80476C9.59043 6.51909 9.93384 6.37625 10.335 6.37625C10.7362 6.37625 11.0796 6.51909 11.3652 6.80476C11.6509 7.09043 11.7937 7.43384 11.7937 7.835C11.7937 8.23616 11.6509 8.57957 11.3652 8.86524C11.0796 9.15091 10.7362 9.29375 10.335 9.29375ZM5.95875 4.9175C5.55759 4.9175 5.21418 4.77466 4.92851 4.48899C4.64284 4.20332 4.5 3.85991 4.5 3.45875C4.5 3.05759 4.64284 2.71418 4.92851 2.42851C5.21418 2.14284 5.55759 2 5.95875 2C6.35991 2 6.70332 2.14284 6.98899 2.42851C7.27466 2.71418 7.4175 3.05759 7.4175 3.45875C7.4175 3.85991 7.27466 4.20332 6.98899 4.48899C6.70332 4.77466 6.35991 4.9175 5.95875 4.9175ZM10.335 4.9175C9.93384 4.9175 9.59043 4.77466 9.30476 4.48899C9.01909 4.20332 8.87625 3.85991 8.87625 3.45875C8.87625 3.05759 9.01909 2.71418 9.30476 2.42851C9.59043 2.14284 9.93384 2 10.335 2C10.7362 2 11.0796 2.14284 11.3652 2.42851C11.6509 2.71418 11.7937 3.05759 11.7937 3.45875C11.7937 3.85991 11.6509 4.20332 11.3652 4.48899C11.0796 4.77466 10.7362 4.9175 10.335 4.9175Z" fill="#64748B"/></g></svg></svg><span class="tooltip-text">Drag & Drop</span></span></span></label></li>'
						);
					} else {
						$( "li.insertorder" ).remove();
					}
				}
			);
			$( "#update_order_checkbox" ).on(
				"change",
				function () {
					if (this.checked) {
						var updatetext = "Update";
						$( "#sortable" ).append(
							'<li class="default-order-sheet ui-state-default ui-sortable-handle updateorder">  <label> <span class="orderSheet-left">  <span class="wootextfield">' +
							updatetext +
							'</span> </span> <span class="orderSheet-right"> <input type="checkbox" name="header_fields_custom[]" value="' +
							updatetext +
							'" class="headers_chk1" hidden="true" checked><input type="checkbox" name="header_fields[]" id="" class="headers_chk" value="' +
							updatetext +
							'" checked>  <span class="ui-icon ui-icon-caret-2-n-s wpssw-tooltio-link">   <svg width="17" height="16" viewBox="0 0 17 16" fill="none" xmlns="http://www.w3.org/2000/svg"> <mask id="mask0_384_3228" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="0" y="0" width="17" height="16"> <rect x="0.5" width="16" height="16" fill="#D9D9D9"/> </mask> <g mask="url(#mask0_384_3228)"> <path d="M5.95875 13.67C5.55759 13.67 5.21418 13.5272 4.92851 13.2415C4.64284 12.9558 4.5 12.6124 4.5 12.2113C4.5 11.8101 4.64284 11.4667 4.92851 11.181C5.21418 10.8953 5.55759 10.7525 5.95875 10.7525C6.35991 10.7525 6.70332 10.8953 6.98899 11.181C7.27466 11.4667 7.4175 11.8101 7.4175 12.2113C7.4175 12.6124 7.27466 12.9558 6.98899 13.2415C6.70332 13.5272 6.35991 13.67 5.95875 13.67ZM10.335 13.67C9.93384 13.67 9.59043 13.5272 9.30476 13.2415C9.01909 12.9558 8.87625 12.6124 8.87625 12.2113C8.87625 11.8101 9.01909 11.4667 9.30476 11.181C9.59043 10.8953 9.93384 10.7525 10.335 10.7525C10.7362 10.7525 11.0796 10.8953 11.3652 11.181C11.6509 11.4667 11.7937 11.8101 11.7937 12.2113C11.7937 12.6124 11.6509 12.9558 11.3652 13.2415C11.0796 13.5272 10.7362 13.67 10.335 13.67ZM5.95875 9.29375C5.55759 9.29375 5.21418 9.15091 4.92851 8.86524C4.64284 8.57957 4.5 8.23616 4.5 7.835C4.5 7.43384 4.64284 7.09043 4.92851 6.80476C5.21418 6.51909 5.55759 6.37625 5.95875 6.37625C6.35991 6.37625 6.70332 6.51909 6.98899 6.80476C7.27466 7.09043 7.4175 7.43384 7.4175 7.835C7.4175 8.23616 7.27466 8.57957 6.98899 8.86524C6.70332 9.15091 6.35991 9.29375 5.95875 9.29375ZM10.335 9.29375C9.93384 9.29375 9.59043 9.15091 9.30476 8.86524C9.01909 8.57957 8.87625 8.23616 8.87625 7.835C8.87625 7.43384 9.01909 7.09043 9.30476 6.80476C9.59043 6.51909 9.93384 6.37625 10.335 6.37625C10.7362 6.37625 11.0796 6.51909 11.3652 6.80476C11.6509 7.09043 11.7937 7.43384 11.7937 7.835C11.7937 8.23616 11.6509 8.57957 11.3652 8.86524C11.0796 9.15091 10.7362 9.29375 10.335 9.29375ZM5.95875 4.9175C5.55759 4.9175 5.21418 4.77466 4.92851 4.48899C4.64284 4.20332 4.5 3.85991 4.5 3.45875C4.5 3.05759 4.64284 2.71418 4.92851 2.42851C5.21418 2.14284 5.55759 2 5.95875 2C6.35991 2 6.70332 2.14284 6.98899 2.42851C7.27466 2.71418 7.4175 3.05759 7.4175 3.45875C7.4175 3.85991 7.27466 4.20332 6.98899 4.48899C6.70332 4.77466 6.35991 4.9175 5.95875 4.9175ZM10.335 4.9175C9.93384 4.9175 9.59043 4.77466 9.30476 4.48899C9.01909 4.20332 8.87625 3.85991 8.87625 3.45875C8.87625 3.05759 9.01909 2.71418 9.30476 2.42851C9.59043 2.14284 9.93384 2 10.335 2C10.7362 2 11.0796 2.14284 11.3652 2.42851C11.6509 2.71418 11.7937 3.05759 11.7937 3.45875C11.7937 3.85991 11.6509 4.20332 11.3652 4.48899C11.0796 4.77466 10.7362 4.9175 10.335 4.9175Z" fill="#64748B"/></g></svg></svg><span class="tooltip-text">Drag & Drop</span></span></span></label></li>'
						);
					} else {
						$( "li.updateorder" ).remove();
					}
				}
			);
			$( "#delete_order_checkbox" ).on(
				"change",
				function () {
					if (this.checked) {
						var deletetext = "Delete";
						$( "#sortable" ).append(
							'<li class="default-order-sheet ui-state-default ui-sortable-handle deleteorder">  <label> <span class="orderSheet-left">  <span class="wootextfield">' +
							deletetext +
							'</span> </span> <span class="orderSheet-right"> <input type="checkbox" name="header_fields_custom[]" value="' +
							deletetext +
							'" class="headers_chk1" hidden="true" checked><input type="checkbox" name="header_fields[]" id="" class="headers_chk" value="' +
							deletetext +
							'" checked>  <span class="ui-icon ui-icon-caret-2-n-s wpssw-tooltio-link">   <svg width="17" height="16" viewBox="0 0 17 16" fill="none" xmlns="http://www.w3.org/2000/svg"> <mask id="mask0_384_3228" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="0" y="0" width="17" height="16"> <rect x="0.5" width="16" height="16" fill="#D9D9D9"/> </mask> <g mask="url(#mask0_384_3228)"> <path d="M5.95875 13.67C5.55759 13.67 5.21418 13.5272 4.92851 13.2415C4.64284 12.9558 4.5 12.6124 4.5 12.2113C4.5 11.8101 4.64284 11.4667 4.92851 11.181C5.21418 10.8953 5.55759 10.7525 5.95875 10.7525C6.35991 10.7525 6.70332 10.8953 6.98899 11.181C7.27466 11.4667 7.4175 11.8101 7.4175 12.2113C7.4175 12.6124 7.27466 12.9558 6.98899 13.2415C6.70332 13.5272 6.35991 13.67 5.95875 13.67ZM10.335 13.67C9.93384 13.67 9.59043 13.5272 9.30476 13.2415C9.01909 12.9558 8.87625 12.6124 8.87625 12.2113C8.87625 11.8101 9.01909 11.4667 9.30476 11.181C9.59043 10.8953 9.93384 10.7525 10.335 10.7525C10.7362 10.7525 11.0796 10.8953 11.3652 11.181C11.6509 11.4667 11.7937 11.8101 11.7937 12.2113C11.7937 12.6124 11.6509 12.9558 11.3652 13.2415C11.0796 13.5272 10.7362 13.67 10.335 13.67ZM5.95875 9.29375C5.55759 9.29375 5.21418 9.15091 4.92851 8.86524C4.64284 8.57957 4.5 8.23616 4.5 7.835C4.5 7.43384 4.64284 7.09043 4.92851 6.80476C5.21418 6.51909 5.55759 6.37625 5.95875 6.37625C6.35991 6.37625 6.70332 6.51909 6.98899 6.80476C7.27466 7.09043 7.4175 7.43384 7.4175 7.835C7.4175 8.23616 7.27466 8.57957 6.98899 8.86524C6.70332 9.15091 6.35991 9.29375 5.95875 9.29375ZM10.335 9.29375C9.93384 9.29375 9.59043 9.15091 9.30476 8.86524C9.01909 8.57957 8.87625 8.23616 8.87625 7.835C8.87625 7.43384 9.01909 7.09043 9.30476 6.80476C9.59043 6.51909 9.93384 6.37625 10.335 6.37625C10.7362 6.37625 11.0796 6.51909 11.3652 6.80476C11.6509 7.09043 11.7937 7.43384 11.7937 7.835C11.7937 8.23616 11.6509 8.57957 11.3652 8.86524C11.0796 9.15091 10.7362 9.29375 10.335 9.29375ZM5.95875 4.9175C5.55759 4.9175 5.21418 4.77466 4.92851 4.48899C4.64284 4.20332 4.5 3.85991 4.5 3.45875C4.5 3.05759 4.64284 2.71418 4.92851 2.42851C5.21418 2.14284 5.55759 2 5.95875 2C6.35991 2 6.70332 2.14284 6.98899 2.42851C7.27466 2.71418 7.4175 3.05759 7.4175 3.45875C7.4175 3.85991 7.27466 4.20332 6.98899 4.48899C6.70332 4.77466 6.35991 4.9175 5.95875 4.9175ZM10.335 4.9175C9.93384 4.9175 9.59043 4.77466 9.30476 4.48899C9.01909 4.20332 8.87625 3.85991 8.87625 3.45875C8.87625 3.05759 9.01909 2.71418 9.30476 2.42851C9.59043 2.14284 9.93384 2 10.335 2C10.7362 2 11.0796 2.14284 11.3652 2.42851C11.6509 2.71418 11.7937 3.05759 11.7937 3.45875C11.7937 3.85991 11.6509 4.20332 11.3652 4.48899C11.0796 4.77466 10.7362 4.9175 10.335 4.9175Z" fill="#64748B"/></g></svg></svg><span class="tooltip-text">Drag & Drop</span></span></span></label></li>'
						);
					} else {
						$( "li.deleteorder" ).remove();
					}
				}
			);
			$( "#import_customer_checkbox" ).on(
				"change",
				function () {
					if (this.checked) {
						$( ".wpssw_crud_cust_row" ).fadeIn();
					} else {
						$( ".wpssw_crud_cust_row" ).fadeOut();
						$( "#insert_customer_checkbox" ).prop( "checked", false );
						$( "#update_customer_checkbox" ).prop( "checked", false ); // Unchecks it.
						$( "#delete_customer_checkbox" ).prop( "checked", false ); // Unchecks it.
						$( "li.insertcustomer" ).remove();
						$( "li.updatecustomer" ).remove();
						$( "li.deletecustomer" ).remove();
					}
				}
			);
			$( "#insert_customer_checkbox" ).on(
				"change",
				function () {
					if (this.checked) {
						var inserttext = "Insert";
						$( "#woo-customer-sortable" ).append(
							'<li class="default-order-sheet ui-state-default ui-sortable-handle insertcustomer"><label><span class="orderSheet-left">  <span class="wootextfield">' +
							inserttext +
							'</span> </span> <span class="orderSheet-right"> <input type="checkbox" name="woocustomer_custom[]" value="' +
							inserttext +
							'" class="woo-cust-headers-chk1" checked="" hidden="true"><input type="checkbox" name="woocustomer_header_list[]" value="' +
							inserttext +
							'" id="woo-Insert" class="woo-cust-headers-chk" checked="">  <span class="ui-icon ui-icon-caret-2-n-s wpssw-tooltio-link">   <svg width="17" height="16" viewBox="0 0 17 16" fill="none" xmlns="http://www.w3.org/2000/svg"> <mask id="mask0_384_3228" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="0" y="0" width="17" height="16"> <rect x="0.5" width="16" height="16" fill="#D9D9D9"/> </mask> <g mask="url(#mask0_384_3228)"> <path d="M5.95875 13.67C5.55759 13.67 5.21418 13.5272 4.92851 13.2415C4.64284 12.9558 4.5 12.6124 4.5 12.2113C4.5 11.8101 4.64284 11.4667 4.92851 11.181C5.21418 10.8953 5.55759 10.7525 5.95875 10.7525C6.35991 10.7525 6.70332 10.8953 6.98899 11.181C7.27466 11.4667 7.4175 11.8101 7.4175 12.2113C7.4175 12.6124 7.27466 12.9558 6.98899 13.2415C6.70332 13.5272 6.35991 13.67 5.95875 13.67ZM10.335 13.67C9.93384 13.67 9.59043 13.5272 9.30476 13.2415C9.01909 12.9558 8.87625 12.6124 8.87625 12.2113C8.87625 11.8101 9.01909 11.4667 9.30476 11.181C9.59043 10.8953 9.93384 10.7525 10.335 10.7525C10.7362 10.7525 11.0796 10.8953 11.3652 11.181C11.6509 11.4667 11.7937 11.8101 11.7937 12.2113C11.7937 12.6124 11.6509 12.9558 11.3652 13.2415C11.0796 13.5272 10.7362 13.67 10.335 13.67ZM5.95875 9.29375C5.55759 9.29375 5.21418 9.15091 4.92851 8.86524C4.64284 8.57957 4.5 8.23616 4.5 7.835C4.5 7.43384 4.64284 7.09043 4.92851 6.80476C5.21418 6.51909 5.55759 6.37625 5.95875 6.37625C6.35991 6.37625 6.70332 6.51909 6.98899 6.80476C7.27466 7.09043 7.4175 7.43384 7.4175 7.835C7.4175 8.23616 7.27466 8.57957 6.98899 8.86524C6.70332 9.15091 6.35991 9.29375 5.95875 9.29375ZM10.335 9.29375C9.93384 9.29375 9.59043 9.15091 9.30476 8.86524C9.01909 8.57957 8.87625 8.23616 8.87625 7.835C8.87625 7.43384 9.01909 7.09043 9.30476 6.80476C9.59043 6.51909 9.93384 6.37625 10.335 6.37625C10.7362 6.37625 11.0796 6.51909 11.3652 6.80476C11.6509 7.09043 11.7937 7.43384 11.7937 7.835C11.7937 8.23616 11.6509 8.57957 11.3652 8.86524C11.0796 9.15091 10.7362 9.29375 10.335 9.29375ZM5.95875 4.9175C5.55759 4.9175 5.21418 4.77466 4.92851 4.48899C4.64284 4.20332 4.5 3.85991 4.5 3.45875C4.5 3.05759 4.64284 2.71418 4.92851 2.42851C5.21418 2.14284 5.55759 2 5.95875 2C6.35991 2 6.70332 2.14284 6.98899 2.42851C7.27466 2.71418 7.4175 3.05759 7.4175 3.45875C7.4175 3.85991 7.27466 4.20332 6.98899 4.48899C6.70332 4.77466 6.35991 4.9175 5.95875 4.9175ZM10.335 4.9175C9.93384 4.9175 9.59043 4.77466 9.30476 4.48899C9.01909 4.20332 8.87625 3.85991 8.87625 3.45875C8.87625 3.05759 9.01909 2.71418 9.30476 2.42851C9.59043 2.14284 9.93384 2 10.335 2C10.7362 2 11.0796 2.14284 11.3652 2.42851C11.6509 2.71418 11.7937 3.05759 11.7937 3.45875C11.7937 3.85991 11.6509 4.20332 11.3652 4.48899C11.0796 4.77466 10.7362 4.9175 10.335 4.9175Z" fill="#64748B"/></g></svg></svg><span class="tooltip-text">Drag & Drop</span></span></span></label></li>'
						);
					} else {
						$( "li.insertcustomer" ).remove();
					}
				}
			);
			$( "#update_customer_checkbox" ).on(
				"change",
				function () {
					if (this.checked) {
						var updatetext = "Update";
						$( "#woo-customer-sortable" ).append(
							'<li class="default-order-sheet ui-state-default ui-sortable-handle updatecustomer"><label><span class="orderSheet-left">  <span class="wootextfield">' +
							updatetext +
							'</span> </span> <span class="orderSheet-right"> <input type="checkbox" name="woocustomer_custom[]" value="' +
							updatetext +
							'" class="woo-cust-headers-chk1" hidden="true" checked><input type="checkbox" name="woocustomer_header_list[]" id="" class="woo-cust-headers-chk" value="' +
							updatetext +
							'" checked>  <span class="ui-icon ui-icon-caret-2-n-s wpssw-tooltio-link">   <svg width="17" height="16" viewBox="0 0 17 16" fill="none" xmlns="http://www.w3.org/2000/svg"> <mask id="mask0_384_3228" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="0" y="0" width="17" height="16"> <rect x="0.5" width="16" height="16" fill="#D9D9D9"/> </mask> <g mask="url(#mask0_384_3228)"> <path d="M5.95875 13.67C5.55759 13.67 5.21418 13.5272 4.92851 13.2415C4.64284 12.9558 4.5 12.6124 4.5 12.2113C4.5 11.8101 4.64284 11.4667 4.92851 11.181C5.21418 10.8953 5.55759 10.7525 5.95875 10.7525C6.35991 10.7525 6.70332 10.8953 6.98899 11.181C7.27466 11.4667 7.4175 11.8101 7.4175 12.2113C7.4175 12.6124 7.27466 12.9558 6.98899 13.2415C6.70332 13.5272 6.35991 13.67 5.95875 13.67ZM10.335 13.67C9.93384 13.67 9.59043 13.5272 9.30476 13.2415C9.01909 12.9558 8.87625 12.6124 8.87625 12.2113C8.87625 11.8101 9.01909 11.4667 9.30476 11.181C9.59043 10.8953 9.93384 10.7525 10.335 10.7525C10.7362 10.7525 11.0796 10.8953 11.3652 11.181C11.6509 11.4667 11.7937 11.8101 11.7937 12.2113C11.7937 12.6124 11.6509 12.9558 11.3652 13.2415C11.0796 13.5272 10.7362 13.67 10.335 13.67ZM5.95875 9.29375C5.55759 9.29375 5.21418 9.15091 4.92851 8.86524C4.64284 8.57957 4.5 8.23616 4.5 7.835C4.5 7.43384 4.64284 7.09043 4.92851 6.80476C5.21418 6.51909 5.55759 6.37625 5.95875 6.37625C6.35991 6.37625 6.70332 6.51909 6.98899 6.80476C7.27466 7.09043 7.4175 7.43384 7.4175 7.835C7.4175 8.23616 7.27466 8.57957 6.98899 8.86524C6.70332 9.15091 6.35991 9.29375 5.95875 9.29375ZM10.335 9.29375C9.93384 9.29375 9.59043 9.15091 9.30476 8.86524C9.01909 8.57957 8.87625 8.23616 8.87625 7.835C8.87625 7.43384 9.01909 7.09043 9.30476 6.80476C9.59043 6.51909 9.93384 6.37625 10.335 6.37625C10.7362 6.37625 11.0796 6.51909 11.3652 6.80476C11.6509 7.09043 11.7937 7.43384 11.7937 7.835C11.7937 8.23616 11.6509 8.57957 11.3652 8.86524C11.0796 9.15091 10.7362 9.29375 10.335 9.29375ZM5.95875 4.9175C5.55759 4.9175 5.21418 4.77466 4.92851 4.48899C4.64284 4.20332 4.5 3.85991 4.5 3.45875C4.5 3.05759 4.64284 2.71418 4.92851 2.42851C5.21418 2.14284 5.55759 2 5.95875 2C6.35991 2 6.70332 2.14284 6.98899 2.42851C7.27466 2.71418 7.4175 3.05759 7.4175 3.45875C7.4175 3.85991 7.27466 4.20332 6.98899 4.48899C6.70332 4.77466 6.35991 4.9175 5.95875 4.9175ZM10.335 4.9175C9.93384 4.9175 9.59043 4.77466 9.30476 4.48899C9.01909 4.20332 8.87625 3.85991 8.87625 3.45875C8.87625 3.05759 9.01909 2.71418 9.30476 2.42851C9.59043 2.14284 9.93384 2 10.335 2C10.7362 2 11.0796 2.14284 11.3652 2.42851C11.6509 2.71418 11.7937 3.05759 11.7937 3.45875C11.7937 3.85991 11.6509 4.20332 11.3652 4.48899C11.0796 4.77466 10.7362 4.9175 10.335 4.9175Z" fill="#64748B"/></g></svg></svg><span class="tooltip-text">Drag & Drop</span></span></span></label></li>'
						);
					} else {
						$( "li.updatecustomer" ).remove();
					}
				}
			);
			$( "#delete_customer_checkbox" ).on(
				"change",
				function () {
					if (this.checked) {
						var deletetext = "Delete";
						$( "#woo-customer-sortable" ).append(
							'<li class="default-order-sheet ui-state-default ui-sortable-handle deletecustomer"><label><span class="orderSheet-left">  <span class="wootextfield">' +
							deletetext +
							'</span> </span> <span class="orderSheet-right"> <input type="checkbox" name="woocustomer_custom[]" value="' +
							deletetext +
							'" class="woo-cust-headers-chk1" hidden="true" checked><input type="checkbox" name="woocustomer_header_list[]" id="" class="woo-cust-headers-chk" value="' +
							deletetext +
							'" checked>  <span class="ui-icon ui-icon-caret-2-n-s wpssw-tooltio-link">   <svg width="17" height="16" viewBox="0 0 17 16" fill="none" xmlns="http://www.w3.org/2000/svg"> <mask id="mask0_384_3228" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="0" y="0" width="17" height="16"> <rect x="0.5" width="16" height="16" fill="#D9D9D9"/> </mask> <g mask="url(#mask0_384_3228)"> <path d="M5.95875 13.67C5.55759 13.67 5.21418 13.5272 4.92851 13.2415C4.64284 12.9558 4.5 12.6124 4.5 12.2113C4.5 11.8101 4.64284 11.4667 4.92851 11.181C5.21418 10.8953 5.55759 10.7525 5.95875 10.7525C6.35991 10.7525 6.70332 10.8953 6.98899 11.181C7.27466 11.4667 7.4175 11.8101 7.4175 12.2113C7.4175 12.6124 7.27466 12.9558 6.98899 13.2415C6.70332 13.5272 6.35991 13.67 5.95875 13.67ZM10.335 13.67C9.93384 13.67 9.59043 13.5272 9.30476 13.2415C9.01909 12.9558 8.87625 12.6124 8.87625 12.2113C8.87625 11.8101 9.01909 11.4667 9.30476 11.181C9.59043 10.8953 9.93384 10.7525 10.335 10.7525C10.7362 10.7525 11.0796 10.8953 11.3652 11.181C11.6509 11.4667 11.7937 11.8101 11.7937 12.2113C11.7937 12.6124 11.6509 12.9558 11.3652 13.2415C11.0796 13.5272 10.7362 13.67 10.335 13.67ZM5.95875 9.29375C5.55759 9.29375 5.21418 9.15091 4.92851 8.86524C4.64284 8.57957 4.5 8.23616 4.5 7.835C4.5 7.43384 4.64284 7.09043 4.92851 6.80476C5.21418 6.51909 5.55759 6.37625 5.95875 6.37625C6.35991 6.37625 6.70332 6.51909 6.98899 6.80476C7.27466 7.09043 7.4175 7.43384 7.4175 7.835C7.4175 8.23616 7.27466 8.57957 6.98899 8.86524C6.70332 9.15091 6.35991 9.29375 5.95875 9.29375ZM10.335 9.29375C9.93384 9.29375 9.59043 9.15091 9.30476 8.86524C9.01909 8.57957 8.87625 8.23616 8.87625 7.835C8.87625 7.43384 9.01909 7.09043 9.30476 6.80476C9.59043 6.51909 9.93384 6.37625 10.335 6.37625C10.7362 6.37625 11.0796 6.51909 11.3652 6.80476C11.6509 7.09043 11.7937 7.43384 11.7937 7.835C11.7937 8.23616 11.6509 8.57957 11.3652 8.86524C11.0796 9.15091 10.7362 9.29375 10.335 9.29375ZM5.95875 4.9175C5.55759 4.9175 5.21418 4.77466 4.92851 4.48899C4.64284 4.20332 4.5 3.85991 4.5 3.45875C4.5 3.05759 4.64284 2.71418 4.92851 2.42851C5.21418 2.14284 5.55759 2 5.95875 2C6.35991 2 6.70332 2.14284 6.98899 2.42851C7.27466 2.71418 7.4175 3.05759 7.4175 3.45875C7.4175 3.85991 7.27466 4.20332 6.98899 4.48899C6.70332 4.77466 6.35991 4.9175 5.95875 4.9175ZM10.335 4.9175C9.93384 4.9175 9.59043 4.77466 9.30476 4.48899C9.01909 4.20332 8.87625 3.85991 8.87625 3.45875C8.87625 3.05759 9.01909 2.71418 9.30476 2.42851C9.59043 2.14284 9.93384 2 10.335 2C10.7362 2 11.0796 2.14284 11.3652 2.42851C11.6509 2.71418 11.7937 3.05759 11.7937 3.45875C11.7937 3.85991 11.6509 4.20332 11.3652 4.48899C11.0796 4.77466 10.7362 4.9175 10.335 4.9175Z" fill="#64748B"/></g></svg></svg><span class="tooltip-text">Drag & Drop</span></span></span></label></li>'
						);
					} else {
						$( "li.deletecustomer" ).remove();
					}
				}
			);
			$( "#import_coupon_checkbox" ).on(
				"change",
				function () {
					if (this.checked) {
						$( ".wpssw_crud_coupon_row" ).fadeIn();
					} else {
						$( ".wpssw_crud_coupon_row" ).fadeOut();
						$( "#insert_coupon_checkbox" ).prop( "checked", false );
						$( "#update_coupon_checkbox" ).prop( "checked", false ); // Unchecks it.
						$( "#delete_coupon_checkbox" ).prop( "checked", false ); // Unchecks it.
						$( "li.insertcoupon" ).remove();
						$( "li.updatecoupon" ).remove();
						$( "li.deletecoupon" ).remove();
					}
				}
			);
			$( "#insert_coupon_checkbox" ).on(
				"change",
				function () {
					if (this.checked) {
						var inserttext = "Insert";
						$( "#woo-coupon-sortable" ).append(
							'<li class="default-order-sheet ui-state-default ui-sortable-handle insertcoupon"><label><span class="orderSheet-left">  <span class="wootextfield">' +
							inserttext +
							'</span> </span> <span class="orderSheet-right"> <input type="checkbox" name="woocoupon_custom[]" value="' +
							inserttext +
							'" class="woo-cust-headers-chk1" checked="" hidden="true"><input type="checkbox" name="woocoupon_header_list[]" value="' +
							inserttext +
							'" id="woo-Insert" class="woo-cust-headers-chk" checked="">  <span class="ui-icon ui-icon-caret-2-n-s wpssw-tooltio-link">   <svg width="17" height="16" viewBox="0 0 17 16" fill="none" xmlns="http://www.w3.org/2000/svg"> <mask id="mask0_384_3228" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="0" y="0" width="17" height="16"> <rect x="0.5" width="16" height="16" fill="#D9D9D9"/> </mask> <g mask="url(#mask0_384_3228)"> <path d="M5.95875 13.67C5.55759 13.67 5.21418 13.5272 4.92851 13.2415C4.64284 12.9558 4.5 12.6124 4.5 12.2113C4.5 11.8101 4.64284 11.4667 4.92851 11.181C5.21418 10.8953 5.55759 10.7525 5.95875 10.7525C6.35991 10.7525 6.70332 10.8953 6.98899 11.181C7.27466 11.4667 7.4175 11.8101 7.4175 12.2113C7.4175 12.6124 7.27466 12.9558 6.98899 13.2415C6.70332 13.5272 6.35991 13.67 5.95875 13.67ZM10.335 13.67C9.93384 13.67 9.59043 13.5272 9.30476 13.2415C9.01909 12.9558 8.87625 12.6124 8.87625 12.2113C8.87625 11.8101 9.01909 11.4667 9.30476 11.181C9.59043 10.8953 9.93384 10.7525 10.335 10.7525C10.7362 10.7525 11.0796 10.8953 11.3652 11.181C11.6509 11.4667 11.7937 11.8101 11.7937 12.2113C11.7937 12.6124 11.6509 12.9558 11.3652 13.2415C11.0796 13.5272 10.7362 13.67 10.335 13.67ZM5.95875 9.29375C5.55759 9.29375 5.21418 9.15091 4.92851 8.86524C4.64284 8.57957 4.5 8.23616 4.5 7.835C4.5 7.43384 4.64284 7.09043 4.92851 6.80476C5.21418 6.51909 5.55759 6.37625 5.95875 6.37625C6.35991 6.37625 6.70332 6.51909 6.98899 6.80476C7.27466 7.09043 7.4175 7.43384 7.4175 7.835C7.4175 8.23616 7.27466 8.57957 6.98899 8.86524C6.70332 9.15091 6.35991 9.29375 5.95875 9.29375ZM10.335 9.29375C9.93384 9.29375 9.59043 9.15091 9.30476 8.86524C9.01909 8.57957 8.87625 8.23616 8.87625 7.835C8.87625 7.43384 9.01909 7.09043 9.30476 6.80476C9.59043 6.51909 9.93384 6.37625 10.335 6.37625C10.7362 6.37625 11.0796 6.51909 11.3652 6.80476C11.6509 7.09043 11.7937 7.43384 11.7937 7.835C11.7937 8.23616 11.6509 8.57957 11.3652 8.86524C11.0796 9.15091 10.7362 9.29375 10.335 9.29375ZM5.95875 4.9175C5.55759 4.9175 5.21418 4.77466 4.92851 4.48899C4.64284 4.20332 4.5 3.85991 4.5 3.45875C4.5 3.05759 4.64284 2.71418 4.92851 2.42851C5.21418 2.14284 5.55759 2 5.95875 2C6.35991 2 6.70332 2.14284 6.98899 2.42851C7.27466 2.71418 7.4175 3.05759 7.4175 3.45875C7.4175 3.85991 7.27466 4.20332 6.98899 4.48899C6.70332 4.77466 6.35991 4.9175 5.95875 4.9175ZM10.335 4.9175C9.93384 4.9175 9.59043 4.77466 9.30476 4.48899C9.01909 4.20332 8.87625 3.85991 8.87625 3.45875C8.87625 3.05759 9.01909 2.71418 9.30476 2.42851C9.59043 2.14284 9.93384 2 10.335 2C10.7362 2 11.0796 2.14284 11.3652 2.42851C11.6509 2.71418 11.7937 3.05759 11.7937 3.45875C11.7937 3.85991 11.6509 4.20332 11.3652 4.48899C11.0796 4.77466 10.7362 4.9175 10.335 4.9175Z" fill="#64748B"/></g></svg></svg><span class="tooltip-text">Drag & Drop</span></span></span></label></li>'
						);
					} else {
						$( "li.insertcoupon" ).remove();
					}
				}
			);
			$( "#update_coupon_checkbox" ).on(
				"change",
				function () {
					if (this.checked) {
						var updatetext = "Update";
						$( "#woo-coupon-sortable" ).append(
							'<li class="default-order-sheet ui-state-default ui-sortable-handle updatecoupon"><label><span class="orderSheet-left">  <span class="wootextfield">' +
							updatetext +
							'</span> </span> <span class="orderSheet-right"> <input type="checkbox" name="woocoupon_custom[]" value="' +
							updatetext +
							'" class="woo-cust-headers-chk1" hidden="true" checked><input type="checkbox" name="woocoupon_header_list[]" id="" class="woo-cust-headers-chk" value="' +
							updatetext +
							'" checked>  <span class="ui-icon ui-icon-caret-2-n-s wpssw-tooltio-link">   <svg width="17" height="16" viewBox="0 0 17 16" fill="none" xmlns="http://www.w3.org/2000/svg"> <mask id="mask0_384_3228" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="0" y="0" width="17" height="16"> <rect x="0.5" width="16" height="16" fill="#D9D9D9"/> </mask> <g mask="url(#mask0_384_3228)"> <path d="M5.95875 13.67C5.55759 13.67 5.21418 13.5272 4.92851 13.2415C4.64284 12.9558 4.5 12.6124 4.5 12.2113C4.5 11.8101 4.64284 11.4667 4.92851 11.181C5.21418 10.8953 5.55759 10.7525 5.95875 10.7525C6.35991 10.7525 6.70332 10.8953 6.98899 11.181C7.27466 11.4667 7.4175 11.8101 7.4175 12.2113C7.4175 12.6124 7.27466 12.9558 6.98899 13.2415C6.70332 13.5272 6.35991 13.67 5.95875 13.67ZM10.335 13.67C9.93384 13.67 9.59043 13.5272 9.30476 13.2415C9.01909 12.9558 8.87625 12.6124 8.87625 12.2113C8.87625 11.8101 9.01909 11.4667 9.30476 11.181C9.59043 10.8953 9.93384 10.7525 10.335 10.7525C10.7362 10.7525 11.0796 10.8953 11.3652 11.181C11.6509 11.4667 11.7937 11.8101 11.7937 12.2113C11.7937 12.6124 11.6509 12.9558 11.3652 13.2415C11.0796 13.5272 10.7362 13.67 10.335 13.67ZM5.95875 9.29375C5.55759 9.29375 5.21418 9.15091 4.92851 8.86524C4.64284 8.57957 4.5 8.23616 4.5 7.835C4.5 7.43384 4.64284 7.09043 4.92851 6.80476C5.21418 6.51909 5.55759 6.37625 5.95875 6.37625C6.35991 6.37625 6.70332 6.51909 6.98899 6.80476C7.27466 7.09043 7.4175 7.43384 7.4175 7.835C7.4175 8.23616 7.27466 8.57957 6.98899 8.86524C6.70332 9.15091 6.35991 9.29375 5.95875 9.29375ZM10.335 9.29375C9.93384 9.29375 9.59043 9.15091 9.30476 8.86524C9.01909 8.57957 8.87625 8.23616 8.87625 7.835C8.87625 7.43384 9.01909 7.09043 9.30476 6.80476C9.59043 6.51909 9.93384 6.37625 10.335 6.37625C10.7362 6.37625 11.0796 6.51909 11.3652 6.80476C11.6509 7.09043 11.7937 7.43384 11.7937 7.835C11.7937 8.23616 11.6509 8.57957 11.3652 8.86524C11.0796 9.15091 10.7362 9.29375 10.335 9.29375ZM5.95875 4.9175C5.55759 4.9175 5.21418 4.77466 4.92851 4.48899C4.64284 4.20332 4.5 3.85991 4.5 3.45875C4.5 3.05759 4.64284 2.71418 4.92851 2.42851C5.21418 2.14284 5.55759 2 5.95875 2C6.35991 2 6.70332 2.14284 6.98899 2.42851C7.27466 2.71418 7.4175 3.05759 7.4175 3.45875C7.4175 3.85991 7.27466 4.20332 6.98899 4.48899C6.70332 4.77466 6.35991 4.9175 5.95875 4.9175ZM10.335 4.9175C9.93384 4.9175 9.59043 4.77466 9.30476 4.48899C9.01909 4.20332 8.87625 3.85991 8.87625 3.45875C8.87625 3.05759 9.01909 2.71418 9.30476 2.42851C9.59043 2.14284 9.93384 2 10.335 2C10.7362 2 11.0796 2.14284 11.3652 2.42851C11.6509 2.71418 11.7937 3.05759 11.7937 3.45875C11.7937 3.85991 11.6509 4.20332 11.3652 4.48899C11.0796 4.77466 10.7362 4.9175 10.335 4.9175Z" fill="#64748B"/></g></svg></svg><span class="tooltip-text">Drag & Drop</span></span></span></label></li>'
						);
					} else {
						$( "li.updatecoupon" ).remove();
					}
				}
			);
			$( "#delete_coupon_checkbox" ).on(
				"change",
				function () {
					if (this.checked) {
						var deletetext = "Delete";
						$( "#woo-coupon-sortable" ).append(
							'<li class="default-order-sheet ui-state-default ui-sortable-handle deletecoupon"><label><span class="orderSheet-left">  <span class="wootextfield">' +
							deletetext +
							'</span> </span> <span class="orderSheet-right"> <input type="checkbox" name="woocoupon_custom[]" value="' +
							deletetext +
							'" class="woo-cust-headers-chk1" hidden="true" checked><input type="checkbox" name="woocoupon_header_list[]" id="" class="woo-cust-headers-chk" value="' +
							deletetext +
							'" checked>  <span class="ui-icon ui-icon-caret-2-n-s wpssw-tooltio-link">   <svg width="17" height="16" viewBox="0 0 17 16" fill="none" xmlns="http://www.w3.org/2000/svg"> <mask id="mask0_384_3228" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="0" y="0" width="17" height="16"> <rect x="0.5" width="16" height="16" fill="#D9D9D9"/> </mask> <g mask="url(#mask0_384_3228)"> <path d="M5.95875 13.67C5.55759 13.67 5.21418 13.5272 4.92851 13.2415C4.64284 12.9558 4.5 12.6124 4.5 12.2113C4.5 11.8101 4.64284 11.4667 4.92851 11.181C5.21418 10.8953 5.55759 10.7525 5.95875 10.7525C6.35991 10.7525 6.70332 10.8953 6.98899 11.181C7.27466 11.4667 7.4175 11.8101 7.4175 12.2113C7.4175 12.6124 7.27466 12.9558 6.98899 13.2415C6.70332 13.5272 6.35991 13.67 5.95875 13.67ZM10.335 13.67C9.93384 13.67 9.59043 13.5272 9.30476 13.2415C9.01909 12.9558 8.87625 12.6124 8.87625 12.2113C8.87625 11.8101 9.01909 11.4667 9.30476 11.181C9.59043 10.8953 9.93384 10.7525 10.335 10.7525C10.7362 10.7525 11.0796 10.8953 11.3652 11.181C11.6509 11.4667 11.7937 11.8101 11.7937 12.2113C11.7937 12.6124 11.6509 12.9558 11.3652 13.2415C11.0796 13.5272 10.7362 13.67 10.335 13.67ZM5.95875 9.29375C5.55759 9.29375 5.21418 9.15091 4.92851 8.86524C4.64284 8.57957 4.5 8.23616 4.5 7.835C4.5 7.43384 4.64284 7.09043 4.92851 6.80476C5.21418 6.51909 5.55759 6.37625 5.95875 6.37625C6.35991 6.37625 6.70332 6.51909 6.98899 6.80476C7.27466 7.09043 7.4175 7.43384 7.4175 7.835C7.4175 8.23616 7.27466 8.57957 6.98899 8.86524C6.70332 9.15091 6.35991 9.29375 5.95875 9.29375ZM10.335 9.29375C9.93384 9.29375 9.59043 9.15091 9.30476 8.86524C9.01909 8.57957 8.87625 8.23616 8.87625 7.835C8.87625 7.43384 9.01909 7.09043 9.30476 6.80476C9.59043 6.51909 9.93384 6.37625 10.335 6.37625C10.7362 6.37625 11.0796 6.51909 11.3652 6.80476C11.6509 7.09043 11.7937 7.43384 11.7937 7.835C11.7937 8.23616 11.6509 8.57957 11.3652 8.86524C11.0796 9.15091 10.7362 9.29375 10.335 9.29375ZM5.95875 4.9175C5.55759 4.9175 5.21418 4.77466 4.92851 4.48899C4.64284 4.20332 4.5 3.85991 4.5 3.45875C4.5 3.05759 4.64284 2.71418 4.92851 2.42851C5.21418 2.14284 5.55759 2 5.95875 2C6.35991 2 6.70332 2.14284 6.98899 2.42851C7.27466 2.71418 7.4175 3.05759 7.4175 3.45875C7.4175 3.85991 7.27466 4.20332 6.98899 4.48899C6.70332 4.77466 6.35991 4.9175 5.95875 4.9175ZM10.335 4.9175C9.93384 4.9175 9.59043 4.77466 9.30476 4.48899C9.01909 4.20332 8.87625 3.85991 8.87625 3.45875C8.87625 3.05759 9.01909 2.71418 9.30476 2.42851C9.59043 2.14284 9.93384 2 10.335 2C10.7362 2 11.0796 2.14284 11.3652 2.42851C11.6509 2.71418 11.7937 3.05759 11.7937 3.45875C11.7937 3.85991 11.6509 4.20332 11.3652 4.48899C11.0796 4.77466 10.7362 4.9175 10.335 4.9175Z" fill="#64748B"/></g></svg></svg><span class="tooltip-text">Drag & Drop</span></span></span></label></li>'
						);
					} else {
						$( "li.deletecoupon" ).remove();
					}
				}
			);
			$( "#prdassheetheaders" ).on(
				"change",
				function () {
					if (this.checked) {
						$( "#loaderprdheader" ).fadeIn();
						$.ajax(
							{
								url: admin_ajax_object.ajaxurl,
								type: "post",
								data: "action=wpssw_get_product_list",
								data: { action: "wpssw_get_product_list" },
								success: function (response) {
									$( "#loaderprdheader" ).fadeOut();
									$( ".td-prd-wpssw-headers" ).replaceWith( response ).fadeIn();
									$( ".td-prd-append-after" ).fadeIn();
								},
							}
						).fail(
							function () {
								alert( "Error" );
							}
						);
					} else {
						$( ".td-prd-wpssw-headers" ).fadeOut();
						$( ".td-prd-append-after" ).fadeOut();
					}
				}
			);
			$( "#product_categories_as_sheet" ).on(
				"change",
				function () {
					if (this.checked) {
						$( ".include-child-categories-row" ).fadeIn();
						var loaderEl = $( this ).siblings( ".wpssw-loader" );
						$( loaderEl ).fadeIn();
						$.ajax(
							{
								url: admin_ajax_object.ajaxurl,
								type: "post",
								data: "action=wpssw_get_product_categories_as_sheet",
								success: function (response) {
									$( loaderEl ).fadeOut();
									$( ".td-prd-cat-sheets-wpssw" ).replaceWith( response ).fadeIn();
								},
							}
						).fail(
							function () {
								alert( "Error" );
								$( loaderEl ).fadeOut();
							}
						);
					} else {
						$( ".include-child-categories-row" ).fadeOut();
						$( ".td-prd-cat-sheets-wpssw" ).fadeOut();
						$( loaderEl ).fadeOut();
					}
				}
			);
			if ($( "#product_categories_as_sheet" ).is( ":checked" )) {
				$( ".td-prd-cat-sheets-wpssw" ).show();
			} else {
				$( ".td-prd-cat-sheets-wpssw" ).hide();
			}
			if ($( "input[name='graph_sheet']" ).is( ":checked" )) {
				$( ".graph_spreadsheet_row" ).show();
			} else {
				$( ".graph_spreadsheet_row" ).hide();
			}
			$( "input[name='graph_sheet']" ).on(
				"change",
				function () {
					if ($( this ).is( ":checked" )) {
						$( ".graph_spreadsheet_row" ).fadeIn();
					} else {
						$( ".graph_spreadsheet_row" ).fadeOut();
					}
				}
			);
		}
	);
	$( document ).ready(
		function () {
			add_schedule_weekly_days();
			add_schedule_weekly_days( "prd_autosync_" );
			add_schedule_weekly_days( "product_" );
			add_schedule_weekly_days( "coupon_autosync_" );
			add_schedule_weekly_days( "cust_autosync_" );
			add_schedule_weekly_days( "event_autosync_" );
			add_schedule_weekly_days( "cust_autoimport_" );
			add_schedule_weekly_days( "coupon_autoimport_" );

			$( 'input[name="scheduling_run_on"]' ).change(
				function () {
					var val = $( 'input[name="scheduling_run_on"]:checked' ).val();
					showinterval( val, "order", "auto_sync" );
				}
			);
			$( 'input[name="prd_autosync_scheduling_run_on"]' ).change(
				function () {
					var val = $( 'input[name="prd_autosync_scheduling_run_on"]:checked' ).val();
					showinterval( val, "product", "auto_sync" );
				}
			);
			$( 'input[name="product_scheduling_run_on"]' ).change(
				function () {
					var val = $( 'input[name="product_scheduling_run_on"]:checked' ).val();
					showinterval( val, "product", "import_products" );
				}
			);

			$( 'input[name="coupon_autosync_scheduling_run_on"]' ).change(
				function () {
					var val = $(
						'input[name="coupon_autosync_scheduling_run_on"]:checked'
					).val();
					showinterval( val, "coupon", "auto_sync" );
				}
			);
			$( 'input[name="cust_autosync_scheduling_run_on"]' ).change(
				function () {
					var val = $(
						'input[name="cust_autosync_scheduling_run_on"]:checked'
					).val();
					showinterval( val, "customer", "auto_sync" );
				}
			);
			$( 'input[name="event_autosync_scheduling_run_on"]' ).change(
				function () {
					var val = $(
						'input[name="event_autosync_scheduling_run_on"]:checked'
					).val();
					showinterval( val, "event", "auto_sync" );
				}
			);
			$( 'input[name="cust_autoimport_scheduling_run_on"]' ).change(
				function () {
					var val = $(
						'input[name="cust_autoimport_scheduling_run_on"]:checked'
					).val();
					showinterval( val, "customer", "auto_import" );
				}
			);
			$( 'input[name="coupon_autoimport_scheduling_run_on"]' ).change(
				function () {
					var val = $(
						'input[name="coupon_autoimport_scheduling_run_on"]:checked'
					).val();
					showinterval( val, "coupon", "auto_import" );
				}
			);
			$( 'input[name="scheduling_enable"]' ).change(
				function () {
					var val = $( 'input[name="scheduling_enable"]:checked' ).val();
					showschedule( val, "order", "auto_sync" );
				}
			);
			$( 'input[name="prd_autosync_scheduling_enable"]' ).change(
				function () {
					var val = $( 'input[name="prd_autosync_scheduling_enable"]:checked' ).val();
					showschedule( val, "product", "auto_sync" );
				}
			);
			$( 'input[name="product_scheduling_enable"]' ).change(
				function () {
					var val = $( 'input[name="product_scheduling_enable"]:checked' ).val();
					showschedule( val, "product", "import_products" );
				}
			);

			$( 'input[name="coupon_autosync_scheduling_enable"]' ).change(
				function () {
					var val = $(
						'input[name="coupon_autosync_scheduling_enable"]:checked'
					).val();
					showschedule( val, "coupon", "auto_sync" );
				}
			);
			$( 'input[name="cust_autosync_scheduling_enable"]' ).change(
				function () {
					var val = $(
						'input[name="cust_autosync_scheduling_enable"]:checked'
					).val();
					showschedule( val, "customer", "auto_sync" );
				}
			);
			$( 'input[name="event_autosync_scheduling_enable"]' ).change(
				function () {
					var val = $(
						'input[name="event_autosync_scheduling_enable"]:checked'
					).val();
					showschedule( val, "event", "auto_sync" );
				}
			);
			$( 'input[name="coupon_autoimport_scheduling_enable"]' ).change(
				function () {
					var val = $(
						'input[name="coupon_autoimport_scheduling_enable"]:checked'
					).val();
					showschedule( val, "coupon", "auto_import" );
				}
			);
			$( 'input[name="cust_autoimport_scheduling_enable"]' ).change(
				function () {
					var val = $(
						'input[name="cust_autoimport_scheduling_enable"]:checked'
					).val();
					showschedule( val, "customer", "auto_import" );
				}
			);

			var val = $( 'input[name="scheduling_enable"]:checked' ).val();
			showschedule( val, "order", "auto_sync" );
			var val = $( 'input[name="prd_autosync_scheduling_enable"]:checked' ).val();
			showschedule( val, "product", "auto_sync" );
			var val = $( 'input[name="product_scheduling_enable"]:checked' ).val();
			showschedule( val, "product", "import_products" );

			var val = $(
				'input[name="coupon_autosync_scheduling_enable"]:checked'
			).val();
			showschedule( val, "coupon", "auto_sync" );
			var val = $( 'input[name="cust_autosync_scheduling_enable"]:checked' ).val();
			showschedule( val, "customer", "auto_sync" );
			var val = $( 'input[name="event_autosync_scheduling_enable"]:checked' ).val();
			showschedule( val, "event", "auto_sync" );
			var val = $(
				'input[name="coupon_autoimport_scheduling_enable"]:checked'
			).val();
			showschedule( val, "coupon", "auto_import" );
			var val = $(
				'input[name="cust_autoimport_scheduling_enable"]:checked'
			).val();
			showschedule( val, "customer", "auto_import" );
		}
	);
	$( document ).ready(
		function () {
			$( ".sync_all_fromtodate" ).hide();
			$( ".prd_sync_all_fromtodate" ).hide()
			$( ".prd_sync_all_sale_fromtodate" ).hide();
			$( ".cust_sync_all_fromtodate" ).hide();
			$( ".coupon_sync_all_fromtodate" ).hide();
			$( ".event_sync_all_fromtodate" ).hide();
			$( "#expsyncloader" ).hide();
			$( "#expsynctext" ).hide();
			$( "#spreadsheet_url" ).hide();
			$( ".prd_sync_language_options" ).hide();
			if (false === Boolean( $( "#prd_sync_lang_all" ).is( ":checked" ) )) {
				$( ".prd_sync_language_options" ).show();
			}
			$( "#exportform" ).on(
				"submit",
				function () {
					$( "#spreadsheet_url" ).hide();
					$( "#spreadsheet_xslxurl" ).hide();
					var ordFromDate           = $( "#ordfromdate" ).val();
					var wpssw_export_settings = $( "#wpssw_export_settings" ).val();
					var ordToDate             = $( "#ordtodate" ).val();

					var spreadSheetName = $( "#expspreadsheetname" ).val();
					var chkArray        = [];
					/* look for all checkboes that have a class 'chk' attached to it and check if it was checked */
					$( ".prdcatheaders_chk:checked" ).each(
						function () {
							chkArray.push( $( this ).val() );
						}
					);
					if (ordFromDate > ordToDate) {
						alert( "From Date should not be greater than To Date." );
					} else {
						$( "#expsyncloader" ).show();
						$( "#expsynctext" ).show();
						$( "#exportsubmit" ).attr( "disabled", true );
						if ($( "#exportall" ).is( ":checked" )) {
							var exportAll = "yes";
						} else {
							var exportAll = "no";
						}
						if ($( "#category_select" ).is( ":checked" )) {
							var categorySelect = "yes";
						} else {
							var categorySelect = "no";
						}
						$.ajax(
							{
								url: admin_ajax_object.ajaxurl,
								type: "post",
								data: "action=wpssw_export_order",
								data: {
									action: "wpssw_export_order",
									from_date: ordFromDate,
									to_date: ordToDate,
									spreadsheetname: spreadSheetName,
									exportall: exportAll,
									category_select: categorySelect,
									category_ids: chkArray,
									wpssw_export_settings: wpssw_export_settings,
								},
								success: function (response) {
									if (String( response ) === "error") {
										$( "#expsyncloader" ).hide();
										$( "#expsynctext" ).hide();
										$( "#exportsubmit" ).attr( "disabled", false );
										alert( "Sorry, your nonce did not verify." );
										return false;
									} else {
										var res = JSON.parse( response );
										if (String( res.result ) === "successful") {
											var sheetId =
											"https://docs.google.com/spreadsheets/d/" + res.spreadsheetid;
											var xlsxurl =
											"https://docs.google.com/spreadsheets/u/0/d/" +
											res.spreadsheetid +
											"/export?exportFormat=xlsx";
											$( "#spreadsheet_url" ).attr( "href", sheetId );
											$( "#spreadsheet_xslxurl" ).attr( "href", xlsxurl );
											$( "#expsyncloader" ).hide();
											$( "#expsynctext" ).hide();
											$( "#exportsubmit" ).attr( "disabled", false );
											alert( "Export All Orders Successfully" );
											$( "#spreadsheet_url" ).show();
											$( "#spreadsheet_xslxurl" ).show();
											$( "#spreadsheet_csvurl" ).show();
										} else {
											$( "#expsyncloader" ).hide();
											$( "#expsynctext" ).hide();
											$( "#exportsubmit" ).attr( "disabled", false );
											alert(
												"Your Google Sheets API limit has been reached. Please take a look at our FAQ."
											);
										}
									}
								},
							}
						).fail(
							function () {
								$( "#expsyncloader" ).hide();
								$( "#expsynctext" ).hide();
								$( "#exportsubmit" ).attr( "disabled", false );
								alert( "Error" );
							}
						);
					}
					return false;
				}
			);

			$( 'input[name="exportall_checkbox"]' ).on(
				"change",
				function () {
					var syncAll = $( this ).val();
					if (1 === parseInt( syncAll )) {
						$( ".export_all_fromtodate input" ).attr( "disabled", true );
						$( ".export_all_fromtodate input" ).attr( "required", false );
						$( ".export_all_fromtodate" ).fadeOut();
					} else {
						$( ".export_all_fromtodate input" ).attr( "disabled", false );
						$( ".export_all_fromtodate input" ).attr( "required", true );
						$( ".export_all_fromtodate" ).fadeIn();
					}
				}
			);
			$( 'input[name="sync_range"]' ).on(
				"change",
				function () {
					var syncAll = $( this ).val();
					if (1 === parseInt( syncAll )) {
						$( ".sync_all_fromtodate" ).fadeOut();
						$( "#sync_all_fromdate" ).removeAttr( "required" );
						$( "#sync_all_todate" ).removeAttr( "required" );
					} else {
						$( ".sync_all_fromtodate" ).fadeIn();
						$( "#sync_all_fromdate" ).attr( "required", "required" );
						$( "#sync_all_todate" ).attr( "required", "required" );
					}
				}
			);
			$( 'input[name="prd_sync_all_checkbox"]' ).on(
				"change",
				function () {
					var prdsyncAll = $( this ).val();
					if (1 === parseInt( prdsyncAll )) {
						$( ".prd_sync_all_fromtodate" ).fadeOut();
						$( "#prd_sync_all_fromdate" ).removeAttr( "required" );
						$( "#prd_sync_all_todate" ).removeAttr( "required" );

						$( "#prd_sync_all_fromdate" ).val( '' );
						$( "#prd_sync_all_todate" ).val( '' );

						$( ".prd_sync_all_sale_fromtodate" ).fadeOut();
						$( "#prd_sync_all_sale_fromdate" ).removeAttr( "required" );
						$( "#prd_sync_all_sale_todate" ).removeAttr( "required" );

						$( "#prd_sync_all_sale_fromdate" ).val( '' );
						$( "#prd_sync_all_sale_todate" ).val( '' );

					} else if ('sale-date-range' === prdsyncAll ) {
						$( ".prd_sync_all_fromtodate" ).hide();
						$( "#prd_sync_all_fromdate" ).removeAttr( "required" );
						$( "#prd_sync_all_todate" ).removeAttr( "required" );

						$( "#prd_sync_all_fromdate" ).val( '' );
						$( "#prd_sync_all_todate" ).val( '' );

						$( ".prd_sync_all_sale_fromtodate" ).fadeIn();
						$( "#prd_sync_all_sale_fromdate" ).attr( "required", "required" );
						$( "#prd_sync_all_sale_todate" ).attr( "required", "required" );
					} else {
						$( ".prd_sync_all_sale_fromtodate" ).hide();
						$( "#prd_sync_all_sale_fromdate" ).removeAttr( "required" );
						$( "#prd_sync_all_sale_todate" ).removeAttr( "required" );

						$( "#prd_sync_all_sale_fromdate" ).val( '' );
						$( "#prd_sync_all_sale_todate" ).val( '' );

						$( ".prd_sync_all_fromtodate" ).fadeIn();
						$( "#prd_sync_all_fromdate" ).attr( "required", "required" );
						$( "#prd_sync_all_todate" ).attr( "required", "required" );
					}
				}
			);
			$( 'input[name="cust_sync_all_checkbox"]' ).on(
				"change",
				function () {
					var custsyncAll = $( this ).val();
					if (1 === parseInt( custsyncAll )) {
						$( ".cust_sync_all_fromtodate" ).fadeOut();
						$( "#cust_sync_all_fromdate" ).removeAttr( "required" );
						$( "#cust_sync_all_todate" ).removeAttr( "required" );
					} else {
						$( ".cust_sync_all_fromtodate" ).fadeIn();
						$( "#cust_sync_all_fromdate" ).attr( "required", "required" );
						$( "#cust_sync_all_todate" ).attr( "required", "required" );
					}
				}
			);
			$( 'input[name="coupon_sync_all_checkbox"]' ).on(
				"change",
				function () {
					var couponsyncAll = $( this ).val();
					if (1 === parseInt( couponsyncAll )) {
						$( ".coupon_sync_all_fromtodate" ).fadeOut();
						$( "#coupon_sync_all_fromdate" ).removeAttr( "required" );
						$( "#coupon_sync_all_todate" ).removeAttr( "required" );
					} else {
						$( ".coupon_sync_all_fromtodate" ).fadeIn();
						$( "#coupon_sync_all_fromdate" ).attr( "required", "required" );
						$( "#coupon_sync_all_todate" ).attr( "required", "required" );
					}
				}
			);
			$( 'input[name="event_sync_all_checkbox"]' ).on(
				"change",
				function () {
					var eventsyncAll = $( this ).val();
					if (1 === parseInt( eventsyncAll )) {
						$( ".event_sync_all_fromtodate" ).fadeOut();
						$( "#event_sync_all_fromdate" ).removeAttr( "required" );
						$( "#event_sync_all_todate" ).removeAttr( "required" );
					} else {
						$( ".event_sync_all_fromtodate" ).fadeIn();
						$( "#event_sync_all_fromdate" ).attr( "required", "required" );
						$( "#event_sync_all_todate" ).attr( "required", "required" );
					}
				}
			);
			$( 'input[name="prd_sync_lang_all"]' ).on(
				"change",
				function () {
					var prdlangsyncAll = $( this ).val();
					if (1 === parseInt( prdlangsyncAll )) {
						$( ".prd_sync_language_options" ).fadeOut();
					} else {
						$( ".prd_sync_language_options" ).fadeIn();
					}
				}
			);
		}
	);
	$( document ).ready(
		function () {
			var newRequest = $( "#woocommerce_spreadsheet" ).val();
			if (newRequest != "new" && newRequest != "" && newRequest != 0) {
				var slink =
				'<a id="view_spreadsheet" target="_blank" href="https://docs.google.com/spreadsheets/d/' +
				newRequest +
				'" class="wpssw-button wpssw-tooltio-link view_spreadsheet"><svg width="19" height="14" viewBox="0 0 19 14" fill="none" xmlns="http://www.w3.org/2000/svg"> <path d="M9.49967 10.8334C10.5413 10.8334 11.4268 10.4688 12.1559 9.73962C12.8851 9.01046 13.2497 8.12504 13.2497 7.08337C13.2497 6.04171 12.8851 5.15629 12.1559 4.42712C11.4268 3.69796 10.5413 3.33337 9.49967 3.33337C8.45801 3.33337 7.57259 3.69796 6.84342 4.42712C6.11426 5.15629 5.74967 6.04171 5.74967 7.08337C5.74967 8.12504 6.11426 9.01046 6.84342 9.73962C7.57259 10.4688 8.45801 10.8334 9.49967 10.8334ZM9.49967 9.33337C8.87467 9.33337 8.34343 9.11462 7.90593 8.67712C7.46843 8.23962 7.24967 7.70837 7.24967 7.08337C7.24967 6.45837 7.46843 5.92712 7.90593 5.48962C8.34343 5.05212 8.87467 4.83337 9.49967 4.83337C10.1247 4.83337 10.6559 5.05212 11.0934 5.48962C11.5309 5.92712 11.7497 6.45837 11.7497 7.08337C11.7497 7.70837 11.5309 8.23962 11.0934 8.67712C10.6559 9.11462 10.1247 9.33337 9.49967 9.33337ZM9.49967 13.3334C7.4719 13.3334 5.62467 12.7674 3.95801 11.6355C2.29134 10.5035 1.08301 8.98615 0.333008 7.08337C1.08301 5.1806 2.29134 3.66324 3.95801 2.53129C5.62467 1.39935 7.4719 0.833374 9.49967 0.833374C11.5275 0.833374 13.3747 1.39935 15.0413 2.53129C16.708 3.66324 17.9163 5.1806 18.6663 7.08337C17.9163 8.98615 16.708 10.5035 15.0413 11.6355C13.3747 12.7674 11.5275 13.3334 9.49967 13.3334ZM9.49967 11.6667C11.0691 11.6667 12.5101 11.2535 13.8226 10.4271C15.1351 9.60073 16.1386 8.48615 16.833 7.08337C16.1386 5.6806 15.1351 4.56601 13.8226 3.73962C12.5101 2.91323 11.0691 2.50004 9.49967 2.50004C7.93023 2.50004 6.48926 2.91323 5.17676 3.73962C3.86426 4.56601 2.86079 5.6806 2.16634 7.08337C2.86079 8.48615 3.86426 9.60073 5.17676 10.4271C6.48926 11.2535 7.93023 11.6667 9.49967 11.6667Z" fill="#383E46"/> </svg><span class="tooltip-text">View Spreadsheet</span></a><a id="clear_spreadsheet" href="" class="wpssw-button wpssw-tooltio-link"><svg width="19" height="19" viewBox="0 0 19 19" fill="none" xmlns="http://www.w3.org/2000/svg"> <path d="M8.66674 8.66671H10.3334V2.83337C10.3334 2.59726 10.2535 2.39935 10.0938 2.23962C9.9341 2.0799 9.73619 2.00004 9.50008 2.00004C9.26397 2.00004 9.06605 2.0799 8.90633 2.23962C8.7466 2.39935 8.66674 2.59726 8.66674 2.83337V8.66671ZM3.66674 12H15.3334V10.3334H3.66674V12ZM2.45841 17H4.50008V15.3334C4.50008 15.0973 4.57994 14.8993 4.73966 14.7396C4.89938 14.5799 5.0973 14.5 5.33341 14.5C5.56952 14.5 5.76744 14.5799 5.92716 14.7396C6.08688 14.8993 6.16674 15.0973 6.16674 15.3334V17H8.66674V15.3334C8.66674 15.0973 8.7466 14.8993 8.90633 14.7396C9.06605 14.5799 9.26397 14.5 9.50008 14.5C9.73619 14.5 9.9341 14.5799 10.0938 14.7396C10.2535 14.8993 10.3334 15.0973 10.3334 15.3334V17H12.8334V15.3334C12.8334 15.0973 12.9133 14.8993 13.073 14.7396C13.2327 14.5799 13.4306 14.5 13.6667 14.5C13.9029 14.5 14.1008 14.5799 14.2605 14.7396C14.4202 14.8993 14.5001 15.0973 14.5001 15.3334V17H16.5417L15.7084 13.6667H3.29174L2.45841 17ZM16.5417 18.6667H2.45841C1.91674 18.6667 1.47924 18.4514 1.14591 18.0209C0.812577 17.5903 0.715354 17.1112 0.854243 16.5834L2.00008 12V10.3334C2.00008 9.87504 2.16327 9.48268 2.48966 9.15629C2.81605 8.8299 3.20841 8.66671 3.66674 8.66671H7.00008V2.83337C7.00008 2.13893 7.24313 1.54865 7.72924 1.06254C8.21535 0.57643 8.80563 0.333374 9.50008 0.333374C10.1945 0.333374 10.7848 0.57643 11.2709 1.06254C11.757 1.54865 12.0001 2.13893 12.0001 2.83337V8.66671H15.3334C15.7917 8.66671 16.1841 8.8299 16.5105 9.15629C16.8369 9.48268 17.0001 9.87504 17.0001 10.3334V12L18.1459 16.5834C18.3265 17.1112 18.2466 17.5903 17.9063 18.0209C17.566 18.4514 17.1112 18.6667 16.5417 18.6667Z" fill="#383E46"/> </svg><span class="tooltip-text">Clear Spreadsheet</span> </a><img src="" id="clearloader"><a id="down_spreadsheet" target="_blank" href="https://docs.google.com/spreadsheets/d/' +
				newRequest +
				'/export?format=xlsx" class="wpssw-button wpssw-tooltio-link down_spreadsheet"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"><path fill="none" stroke="#383E46" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 3.33c2.98 1.72 5 4.96 5 8.66 0 5.52-4.48 10-10 10 -5.53 0-10-4.48-10-10 0-3.71 2.01-6.94 5-8.67m1 8.66l4 4m0 0l4-4m-4 4v-14"/></svg><span class="tooltip-text">Download Spreadsheet</span></a> ';
				$( "#woocommerce_spreadsheet" ).after( slink );
			}
			var product_spreadsheet = $( "#product_spreadsheet" ).val();
			if (
			product_spreadsheet != "new" &&
			product_spreadsheet != "" &&
			product_spreadsheet != 0
			) {
				var slink =
				'<a id="prd_view_spreadsheet" target="_blank" href="https://docs.google.com/spreadsheets/d/' +
				product_spreadsheet +
				'" class="wpssw-button wpssw-tooltio-link view_spreadsheet"><svg width="19" height="14" viewBox="0 0 19 14" fill="none" xmlns="http://www.w3.org/2000/svg"> <path d="M9.49967 10.8334C10.5413 10.8334 11.4268 10.4688 12.1559 9.73962C12.8851 9.01046 13.2497 8.12504 13.2497 7.08337C13.2497 6.04171 12.8851 5.15629 12.1559 4.42712C11.4268 3.69796 10.5413 3.33337 9.49967 3.33337C8.45801 3.33337 7.57259 3.69796 6.84342 4.42712C6.11426 5.15629 5.74967 6.04171 5.74967 7.08337C5.74967 8.12504 6.11426 9.01046 6.84342 9.73962C7.57259 10.4688 8.45801 10.8334 9.49967 10.8334ZM9.49967 9.33337C8.87467 9.33337 8.34343 9.11462 7.90593 8.67712C7.46843 8.23962 7.24967 7.70837 7.24967 7.08337C7.24967 6.45837 7.46843 5.92712 7.90593 5.48962C8.34343 5.05212 8.87467 4.83337 9.49967 4.83337C10.1247 4.83337 10.6559 5.05212 11.0934 5.48962C11.5309 5.92712 11.7497 6.45837 11.7497 7.08337C11.7497 7.70837 11.5309 8.23962 11.0934 8.67712C10.6559 9.11462 10.1247 9.33337 9.49967 9.33337ZM9.49967 13.3334C7.4719 13.3334 5.62467 12.7674 3.95801 11.6355C2.29134 10.5035 1.08301 8.98615 0.333008 7.08337C1.08301 5.1806 2.29134 3.66324 3.95801 2.53129C5.62467 1.39935 7.4719 0.833374 9.49967 0.833374C11.5275 0.833374 13.3747 1.39935 15.0413 2.53129C16.708 3.66324 17.9163 5.1806 18.6663 7.08337C17.9163 8.98615 16.708 10.5035 15.0413 11.6355C13.3747 12.7674 11.5275 13.3334 9.49967 13.3334ZM9.49967 11.6667C11.0691 11.6667 12.5101 11.2535 13.8226 10.4271C15.1351 9.60073 16.1386 8.48615 16.833 7.08337C16.1386 5.6806 15.1351 4.56601 13.8226 3.73962C12.5101 2.91323 11.0691 2.50004 9.49967 2.50004C7.93023 2.50004 6.48926 2.91323 5.17676 3.73962C3.86426 4.56601 2.86079 5.6806 2.16634 7.08337C2.86079 8.48615 3.86426 9.60073 5.17676 10.4271C6.48926 11.2535 7.93023 11.6667 9.49967 11.6667Z" fill="#383E46"/> </svg><span class="tooltip-text">View Spreadsheet</span> </a><a id="clear_productsheet" href="" class="wpssw-button wpssw-tooltio-link"><svg width="19" height="19" viewBox="0 0 19 19" fill="none" xmlns="http://www.w3.org/2000/svg"> <path d="M8.66674 8.66671H10.3334V2.83337C10.3334 2.59726 10.2535 2.39935 10.0938 2.23962C9.9341 2.0799 9.73619 2.00004 9.50008 2.00004C9.26397 2.00004 9.06605 2.0799 8.90633 2.23962C8.7466 2.39935 8.66674 2.59726 8.66674 2.83337V8.66671ZM3.66674 12H15.3334V10.3334H3.66674V12ZM2.45841 17H4.50008V15.3334C4.50008 15.0973 4.57994 14.8993 4.73966 14.7396C4.89938 14.5799 5.0973 14.5 5.33341 14.5C5.56952 14.5 5.76744 14.5799 5.92716 14.7396C6.08688 14.8993 6.16674 15.0973 6.16674 15.3334V17H8.66674V15.3334C8.66674 15.0973 8.7466 14.8993 8.90633 14.7396C9.06605 14.5799 9.26397 14.5 9.50008 14.5C9.73619 14.5 9.9341 14.5799 10.0938 14.7396C10.2535 14.8993 10.3334 15.0973 10.3334 15.3334V17H12.8334V15.3334C12.8334 15.0973 12.9133 14.8993 13.073 14.7396C13.2327 14.5799 13.4306 14.5 13.6667 14.5C13.9029 14.5 14.1008 14.5799 14.2605 14.7396C14.4202 14.8993 14.5001 15.0973 14.5001 15.3334V17H16.5417L15.7084 13.6667H3.29174L2.45841 17ZM16.5417 18.6667H2.45841C1.91674 18.6667 1.47924 18.4514 1.14591 18.0209C0.812577 17.5903 0.715354 17.1112 0.854243 16.5834L2.00008 12V10.3334C2.00008 9.87504 2.16327 9.48268 2.48966 9.15629C2.81605 8.8299 3.20841 8.66671 3.66674 8.66671H7.00008V2.83337C7.00008 2.13893 7.24313 1.54865 7.72924 1.06254C8.21535 0.57643 8.80563 0.333374 9.50008 0.333374C10.1945 0.333374 10.7848 0.57643 11.2709 1.06254C11.757 1.54865 12.0001 2.13893 12.0001 2.83337V8.66671H15.3334C15.7917 8.66671 16.1841 8.8299 16.5105 9.15629C16.8369 9.48268 17.0001 9.87504 17.0001 10.3334V12L18.1459 16.5834C18.3265 17.1112 18.2466 17.5903 17.9063 18.0209C17.566 18.4514 17.1112 18.6667 16.5417 18.6667Z" fill="#383E46"/> </svg><span class="tooltip-text">Clear Spreadsheet</span></a>   <img src="" id="clearprdloader"><a id="prd_down_spreadsheet" target="_blank" href="https://docs.google.com/spreadsheets/d/' +
				product_spreadsheet +
				'/export?format=xlsx" class="wpssw-button wpssw-tooltio-link down_spreadsheet"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"><path fill="none" stroke="#383E46" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 3.33c2.98 1.72 5 4.96 5 8.66 0 5.52-4.48 10-10 10 -5.53 0-10-4.48-10-10 0-3.71 2.01-6.94 5-8.67m1 8.66l4 4m0 0l4-4m-4 4v-14"/></svg><span class="tooltip-text">Download Spreadsheet</span></a> ';
				$( "#product_spreadsheet" ).after( slink );
			}
			var customer_spreadsheet = $( "#customer_spreadsheet" ).val();
			if (
			customer_spreadsheet != "new" &&
			customer_spreadsheet != "" &&
			customer_spreadsheet != 0
			) {
				var slink =
				'<a id="cust_view_spreadsheet" target="_blank" href="https://docs.google.com/spreadsheets/d/' +
				customer_spreadsheet +
				'" class="wpssw-button wpssw-tooltio-link view_spreadsheet"><svg width="19" height="14" viewBox="0 0 19 14" fill="none" xmlns="http://www.w3.org/2000/svg"> <path d="M9.49967 10.8334C10.5413 10.8334 11.4268 10.4688 12.1559 9.73962C12.8851 9.01046 13.2497 8.12504 13.2497 7.08337C13.2497 6.04171 12.8851 5.15629 12.1559 4.42712C11.4268 3.69796 10.5413 3.33337 9.49967 3.33337C8.45801 3.33337 7.57259 3.69796 6.84342 4.42712C6.11426 5.15629 5.74967 6.04171 5.74967 7.08337C5.74967 8.12504 6.11426 9.01046 6.84342 9.73962C7.57259 10.4688 8.45801 10.8334 9.49967 10.8334ZM9.49967 9.33337C8.87467 9.33337 8.34343 9.11462 7.90593 8.67712C7.46843 8.23962 7.24967 7.70837 7.24967 7.08337C7.24967 6.45837 7.46843 5.92712 7.90593 5.48962C8.34343 5.05212 8.87467 4.83337 9.49967 4.83337C10.1247 4.83337 10.6559 5.05212 11.0934 5.48962C11.5309 5.92712 11.7497 6.45837 11.7497 7.08337C11.7497 7.70837 11.5309 8.23962 11.0934 8.67712C10.6559 9.11462 10.1247 9.33337 9.49967 9.33337ZM9.49967 13.3334C7.4719 13.3334 5.62467 12.7674 3.95801 11.6355C2.29134 10.5035 1.08301 8.98615 0.333008 7.08337C1.08301 5.1806 2.29134 3.66324 3.95801 2.53129C5.62467 1.39935 7.4719 0.833374 9.49967 0.833374C11.5275 0.833374 13.3747 1.39935 15.0413 2.53129C16.708 3.66324 17.9163 5.1806 18.6663 7.08337C17.9163 8.98615 16.708 10.5035 15.0413 11.6355C13.3747 12.7674 11.5275 13.3334 9.49967 13.3334ZM9.49967 11.6667C11.0691 11.6667 12.5101 11.2535 13.8226 10.4271C15.1351 9.60073 16.1386 8.48615 16.833 7.08337C16.1386 5.6806 15.1351 4.56601 13.8226 3.73962C12.5101 2.91323 11.0691 2.50004 9.49967 2.50004C7.93023 2.50004 6.48926 2.91323 5.17676 3.73962C3.86426 4.56601 2.86079 5.6806 2.16634 7.08337C2.86079 8.48615 3.86426 9.60073 5.17676 10.4271C6.48926 11.2535 7.93023 11.6667 9.49967 11.6667Z" fill="#383E46"/> </svg><span class="tooltip-text">View Spreadsheet</span></a> <a id="clear_customersheet" href="" class="wpssw-button  wpssw-tooltio-link"><svg width="19" height="19" viewBox="0 0 19 19" fill="none" xmlns="http://www.w3.org/2000/svg"> <path d="M8.66674 8.66671H10.3334V2.83337C10.3334 2.59726 10.2535 2.39935 10.0938 2.23962C9.9341 2.0799 9.73619 2.00004 9.50008 2.00004C9.26397 2.00004 9.06605 2.0799 8.90633 2.23962C8.7466 2.39935 8.66674 2.59726 8.66674 2.83337V8.66671ZM3.66674 12H15.3334V10.3334H3.66674V12ZM2.45841 17H4.50008V15.3334C4.50008 15.0973 4.57994 14.8993 4.73966 14.7396C4.89938 14.5799 5.0973 14.5 5.33341 14.5C5.56952 14.5 5.76744 14.5799 5.92716 14.7396C6.08688 14.8993 6.16674 15.0973 6.16674 15.3334V17H8.66674V15.3334C8.66674 15.0973 8.7466 14.8993 8.90633 14.7396C9.06605 14.5799 9.26397 14.5 9.50008 14.5C9.73619 14.5 9.9341 14.5799 10.0938 14.7396C10.2535 14.8993 10.3334 15.0973 10.3334 15.3334V17H12.8334V15.3334C12.8334 15.0973 12.9133 14.8993 13.073 14.7396C13.2327 14.5799 13.4306 14.5 13.6667 14.5C13.9029 14.5 14.1008 14.5799 14.2605 14.7396C14.4202 14.8993 14.5001 15.0973 14.5001 15.3334V17H16.5417L15.7084 13.6667H3.29174L2.45841 17ZM16.5417 18.6667H2.45841C1.91674 18.6667 1.47924 18.4514 1.14591 18.0209C0.812577 17.5903 0.715354 17.1112 0.854243 16.5834L2.00008 12V10.3334C2.00008 9.87504 2.16327 9.48268 2.48966 9.15629C2.81605 8.8299 3.20841 8.66671 3.66674 8.66671H7.00008V2.83337C7.00008 2.13893 7.24313 1.54865 7.72924 1.06254C8.21535 0.57643 8.80563 0.333374 9.50008 0.333374C10.1945 0.333374 10.7848 0.57643 11.2709 1.06254C11.757 1.54865 12.0001 2.13893 12.0001 2.83337V8.66671H15.3334C15.7917 8.66671 16.1841 8.8299 16.5105 9.15629C16.8369 9.48268 17.0001 9.87504 17.0001 10.3334V12L18.1459 16.5834C18.3265 17.1112 18.2466 17.5903 17.9063 18.0209C17.566 18.4514 17.1112 18.6667 16.5417 18.6667Z" fill="#383E46"/> </svg><span class="tooltip-text">Clear Spreadsheet</span></a>   <img src="" id="clearcustloader"><a id="cust_down_spreadsheet" target="_blank" href="https://docs.google.com/spreadsheets/d/' +
				customer_spreadsheet +
				'/export?format=xlsx" class="wpssw-button wpssw-tooltio-link down_spreadsheet"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"><path fill="none" stroke="#383E46" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 3.33c2.98 1.72 5 4.96 5 8.66 0 5.52-4.48 10-10 10 -5.53 0-10-4.48-10-10 0-3.71 2.01-6.94 5-8.67m1 8.66l4 4m0 0l4-4m-4 4v-14"/></svg><span class="tooltip-text">Download Spreadsheet</span></a> ';
				$( "#customer_spreadsheet" ).after( slink );
			}
			var coupon_spreadsheet = $( "#coupon_spreadsheet" ).val();
			if (
			coupon_spreadsheet != "new" &&
			coupon_spreadsheet != "" &&
			coupon_spreadsheet != 0
			) {
				var slink =
				'<a id="coupon_view_spreadsheet" target="_blank" href="https://docs.google.com/spreadsheets/d/' +
				coupon_spreadsheet +
				'" class="wpssw-button wpssw-tooltio-link view_spreadsheet"><svg width="19" height="14" viewBox="0 0 19 14" fill="none" xmlns="http://www.w3.org/2000/svg"> <path d="M9.49967 10.8334C10.5413 10.8334 11.4268 10.4688 12.1559 9.73962C12.8851 9.01046 13.2497 8.12504 13.2497 7.08337C13.2497 6.04171 12.8851 5.15629 12.1559 4.42712C11.4268 3.69796 10.5413 3.33337 9.49967 3.33337C8.45801 3.33337 7.57259 3.69796 6.84342 4.42712C6.11426 5.15629 5.74967 6.04171 5.74967 7.08337C5.74967 8.12504 6.11426 9.01046 6.84342 9.73962C7.57259 10.4688 8.45801 10.8334 9.49967 10.8334ZM9.49967 9.33337C8.87467 9.33337 8.34343 9.11462 7.90593 8.67712C7.46843 8.23962 7.24967 7.70837 7.24967 7.08337C7.24967 6.45837 7.46843 5.92712 7.90593 5.48962C8.34343 5.05212 8.87467 4.83337 9.49967 4.83337C10.1247 4.83337 10.6559 5.05212 11.0934 5.48962C11.5309 5.92712 11.7497 6.45837 11.7497 7.08337C11.7497 7.70837 11.5309 8.23962 11.0934 8.67712C10.6559 9.11462 10.1247 9.33337 9.49967 9.33337ZM9.49967 13.3334C7.4719 13.3334 5.62467 12.7674 3.95801 11.6355C2.29134 10.5035 1.08301 8.98615 0.333008 7.08337C1.08301 5.1806 2.29134 3.66324 3.95801 2.53129C5.62467 1.39935 7.4719 0.833374 9.49967 0.833374C11.5275 0.833374 13.3747 1.39935 15.0413 2.53129C16.708 3.66324 17.9163 5.1806 18.6663 7.08337C17.9163 8.98615 16.708 10.5035 15.0413 11.6355C13.3747 12.7674 11.5275 13.3334 9.49967 13.3334ZM9.49967 11.6667C11.0691 11.6667 12.5101 11.2535 13.8226 10.4271C15.1351 9.60073 16.1386 8.48615 16.833 7.08337C16.1386 5.6806 15.1351 4.56601 13.8226 3.73962C12.5101 2.91323 11.0691 2.50004 9.49967 2.50004C7.93023 2.50004 6.48926 2.91323 5.17676 3.73962C3.86426 4.56601 2.86079 5.6806 2.16634 7.08337C2.86079 8.48615 3.86426 9.60073 5.17676 10.4271C6.48926 11.2535 7.93023 11.6667 9.49967 11.6667Z" fill="#383E46"/> </svg><span class="tooltip-text">View Spreadsheet</span></a> <a id="clear_couponsheet" href="" class="wpssw-button wpssw-tooltio-link"><svg width="19" height="19" viewBox="0 0 19 19" fill="none" xmlns="http://www.w3.org/2000/svg"> <path d="M8.66674 8.66671H10.3334V2.83337C10.3334 2.59726 10.2535 2.39935 10.0938 2.23962C9.9341 2.0799 9.73619 2.00004 9.50008 2.00004C9.26397 2.00004 9.06605 2.0799 8.90633 2.23962C8.7466 2.39935 8.66674 2.59726 8.66674 2.83337V8.66671ZM3.66674 12H15.3334V10.3334H3.66674V12ZM2.45841 17H4.50008V15.3334C4.50008 15.0973 4.57994 14.8993 4.73966 14.7396C4.89938 14.5799 5.0973 14.5 5.33341 14.5C5.56952 14.5 5.76744 14.5799 5.92716 14.7396C6.08688 14.8993 6.16674 15.0973 6.16674 15.3334V17H8.66674V15.3334C8.66674 15.0973 8.7466 14.8993 8.90633 14.7396C9.06605 14.5799 9.26397 14.5 9.50008 14.5C9.73619 14.5 9.9341 14.5799 10.0938 14.7396C10.2535 14.8993 10.3334 15.0973 10.3334 15.3334V17H12.8334V15.3334C12.8334 15.0973 12.9133 14.8993 13.073 14.7396C13.2327 14.5799 13.4306 14.5 13.6667 14.5C13.9029 14.5 14.1008 14.5799 14.2605 14.7396C14.4202 14.8993 14.5001 15.0973 14.5001 15.3334V17H16.5417L15.7084 13.6667H3.29174L2.45841 17ZM16.5417 18.6667H2.45841C1.91674 18.6667 1.47924 18.4514 1.14591 18.0209C0.812577 17.5903 0.715354 17.1112 0.854243 16.5834L2.00008 12V10.3334C2.00008 9.87504 2.16327 9.48268 2.48966 9.15629C2.81605 8.8299 3.20841 8.66671 3.66674 8.66671H7.00008V2.83337C7.00008 2.13893 7.24313 1.54865 7.72924 1.06254C8.21535 0.57643 8.80563 0.333374 9.50008 0.333374C10.1945 0.333374 10.7848 0.57643 11.2709 1.06254C11.757 1.54865 12.0001 2.13893 12.0001 2.83337V8.66671H15.3334C15.7917 8.66671 16.1841 8.8299 16.5105 9.15629C16.8369 9.48268 17.0001 9.87504 17.0001 10.3334V12L18.1459 16.5834C18.3265 17.1112 18.2466 17.5903 17.9063 18.0209C17.566 18.4514 17.1112 18.6667 16.5417 18.6667Z" fill="#383E46"/> </svg><span class="tooltip-text">Clear Spreadsheet</span></a>   <img src="" id="clearcouponloader"><a id="coupon_down_spreadsheet" target="_blank" href="https://docs.google.com/spreadsheets/d/' +
				coupon_spreadsheet +
				'/export?format=xlsx" class="wpssw-button wpssw-tooltio-link down_spreadsheet"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"><path fill="none" stroke="#383E46" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 3.33c2.98 1.72 5 4.96 5 8.66 0 5.52-4.48 10-10 10 -5.53 0-10-4.48-10-10 0-3.71 2.01-6.94 5-8.67m1 8.66l4 4m0 0l4-4m-4 4v-14"/></svg><span class="tooltip-text">Download Spreadsheet</span></a> ';
				$( "#coupon_spreadsheet" ).after( slink );
			}
			var eventSpreadsheet = $( "#event_spreadsheet" ).val();
			if (
			eventSpreadsheet != "new" &&
			eventSpreadsheet != "" &&
			eventSpreadsheet != 0
			) {
				var slink =
				'<a id="event_view_spreadsheet" target="_blank" href="https://docs.google.com/spreadsheets/d/' +
				eventSpreadsheet +
				'" class="wpssw-button wpssw-tooltio-link view_spreadsheet"><svg width="19" height="14" viewBox="0 0 19 14" fill="none" xmlns="http://www.w3.org/2000/svg"> <path d="M9.49967 10.8334C10.5413 10.8334 11.4268 10.4688 12.1559 9.73962C12.8851 9.01046 13.2497 8.12504 13.2497 7.08337C13.2497 6.04171 12.8851 5.15629 12.1559 4.42712C11.4268 3.69796 10.5413 3.33337 9.49967 3.33337C8.45801 3.33337 7.57259 3.69796 6.84342 4.42712C6.11426 5.15629 5.74967 6.04171 5.74967 7.08337C5.74967 8.12504 6.11426 9.01046 6.84342 9.73962C7.57259 10.4688 8.45801 10.8334 9.49967 10.8334ZM9.49967 9.33337C8.87467 9.33337 8.34343 9.11462 7.90593 8.67712C7.46843 8.23962 7.24967 7.70837 7.24967 7.08337C7.24967 6.45837 7.46843 5.92712 7.90593 5.48962C8.34343 5.05212 8.87467 4.83337 9.49967 4.83337C10.1247 4.83337 10.6559 5.05212 11.0934 5.48962C11.5309 5.92712 11.7497 6.45837 11.7497 7.08337C11.7497 7.70837 11.5309 8.23962 11.0934 8.67712C10.6559 9.11462 10.1247 9.33337 9.49967 9.33337ZM9.49967 13.3334C7.4719 13.3334 5.62467 12.7674 3.95801 11.6355C2.29134 10.5035 1.08301 8.98615 0.333008 7.08337C1.08301 5.1806 2.29134 3.66324 3.95801 2.53129C5.62467 1.39935 7.4719 0.833374 9.49967 0.833374C11.5275 0.833374 13.3747 1.39935 15.0413 2.53129C16.708 3.66324 17.9163 5.1806 18.6663 7.08337C17.9163 8.98615 16.708 10.5035 15.0413 11.6355C13.3747 12.7674 11.5275 13.3334 9.49967 13.3334ZM9.49967 11.6667C11.0691 11.6667 12.5101 11.2535 13.8226 10.4271C15.1351 9.60073 16.1386 8.48615 16.833 7.08337C16.1386 5.6806 15.1351 4.56601 13.8226 3.73962C12.5101 2.91323 11.0691 2.50004 9.49967 2.50004C7.93023 2.50004 6.48926 2.91323 5.17676 3.73962C3.86426 4.56601 2.86079 5.6806 2.16634 7.08337C2.86079 8.48615 3.86426 9.60073 5.17676 10.4271C6.48926 11.2535 7.93023 11.6667 9.49967 11.6667Z" fill="#383E46"/> </svg><span class="tooltip-text">View Spreadsheet</span></a> ';
				$( "#event_spreadsheet" ).after( slink );
			}
		}
	);
})( jQuery );

function showschedule(val, setting, schedule_type) {
	var idPrefix   = "";
	var namePrefix = "";
	if ("product" == setting) {
		if ("auto_sync" == schedule_type) {
			idPrefix   = "prd-autosync-";
			namePrefix = "prd_autosync_";
		} else if ("import_products" == schedule_type) {
			idPrefix   = "product-";
			namePrefix = "product_";
		}
	} else if ("coupon" == setting) {
		if ("auto_sync" == schedule_type) {
			idPrefix   = "coupon-autosync-";
			namePrefix = "coupon_autosync_";
		} else if ("auto_import" == schedule_type) {
			idPrefix   = "coupon-autoimport-";
			namePrefix = "coupon_autoimport_";
		}
	} else if ("customer" == setting) {
		if ("auto_sync" == schedule_type) {
			idPrefix   = "cust-autosync-";
			namePrefix = "cust_autosync_";
		} else if ("auto_import" == schedule_type) {
			idPrefix   = "cust-autoimport-";
			namePrefix = "cust_autoimport_";
		}
	} else if ("event" == setting) {
		if ("auto_sync" == schedule_type) {
			idPrefix   = "event-autosync-";
			namePrefix = "event_autosync_";
		}
	}

	var intervalval = jQuery(
		'input[name="' + namePrefix + 'scheduling_run_on"]:checked'
	).val();
	showinterval( intervalval, setting, schedule_type );
	if (val == "0") {
		jQuery( "#" + idPrefix + "automatic-scheduling" ).slideUp();
	} else if (val == "1") {
		jQuery( "#" + idPrefix + "automatic-scheduling" ).slideDown();
	}
}
function showinterval(val, setting, schedule_type) {
	var idPrefix = "";
	if ("product" == setting) {
		if ("import_products" == schedule_type) {
			idPrefix = "product_";
		} else if ("auto_sync" == schedule_type) {
			idPrefix = "prd_autosync_";
		}
	} else if ("coupon" == setting) {
		if ("auto_sync" == schedule_type) {
			idPrefix = "coupon_autosync_";
		} else if ("auto_import" == schedule_type) {
			idPrefix = "coupon_autoimport_";
		}
	} else if ("customer" == setting) {
		if ("auto_sync" == schedule_type) {
			idPrefix = "cust_autosync_";
		} else if ("auto_import" == schedule_type) {
			idPrefix = "cust_autoimport_";
		}
	} else if ("event" == setting) {
		if ("auto_sync" == schedule_type) {
			idPrefix = "event_autosync_";
		}
	}

	if (val == "weekly") {
		jQuery( "#" + idPrefix + "weekly" ).slideDown( 200 );
		jQuery( "#" + idPrefix + "scheduling_date" ).slideUp( 175 );
		jQuery( "#" + idPrefix + "schedule_recurrence" ).slideUp( 175 );
	} else if (val == "onetime") {
		jQuery( "#" + idPrefix + "scheduling_date" ).slideDown( 200 );
		jQuery( "#" + idPrefix + "weekly" ).slideUp( 175 );
		jQuery( "#" + idPrefix + "schedule_recurrence" ).slideUp( 175 );
	} else if (val == "recurrence") {
		jQuery( "#" + idPrefix + "schedule_recurrence" ).slideDown( 200 );
		jQuery( "#" + idPrefix + "weekly" ).slideUp( 175 );
		jQuery( "#" + idPrefix + "scheduling_date" ).slideUp( 175 );
	}
}

function wpsswTab(evt, tabName) {
	"use strict";
	var i, tabContent, tabLinks;
	tabContent           = document.getElementsByClassName( "tabcontent" );
	var tabContentlength = tabContent.length;
	for (i = 0; i < tabContentlength; i++) {
		tabContent[i].style.display = "none";
	}
	tabLinks      = document.getElementsByClassName( "tablinks" );
	var tablength = tabLinks.length;
	for (i = 0; i < tablength; i++) {
		tabLinks[i].className = tabLinks[i].className.replace( " active", "" );
	}
	document.getElementById( tabName ).style.display = "block";
	var type = typeof event;
	if (type !== "undefined") {
		evt.currentTarget.className += " active";
	}
}
function wpsswNavTab(evt, tabName) {
	"use strict";
	var i, tabContent, tabLinks;
	tabContent           = document.getElementsByClassName( "navtabcontent" );
	var tabContentlength = tabContent.length;
	for (i = 0; i < tabContentlength; i++) {
		tabContent[i].style.display = "none";
	}
	tabLinks      = document.getElementsByClassName( "navtablinks" );
	var tablength = tabLinks.length;
	for (i = 0; i < tablength; i++) {
		tabLinks[i].className = tabLinks[i].className.replace( " active", "" );
	}
	document.getElementById( tabName ).style.display = "block";
	var type = typeof event;
	if (type !== "undefined") {
		evt.currentTarget.className += " active";
	}
}
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
function wpsswLicenseCheck(action) {
	"use strict";
	if (String( jQuery( "#ws_envato" ).val() ) === "") {
		jQuery( ".wpssw-license-result" ).html(
			'<div class="error"><p>Please enter Envato API Token</p></div>'
		);
		jQuery( "#licenceloader" ).hide();
		jQuery( "#licencetext" ).hide();
		jQuery( "#licence_submit" ).show();
		return false;
	}
	var data = {
		action: "wpssw_" + action + "_license",
		username: jQuery( "#ws_username" ).val(),
		key: jQuery( "#ws_purchase" ).val(),
		api_key: jQuery( "#ws_envato" ).val(),
		agree_transmit: jQuery( "#agree_transmit:checked" ).val(),
		wpnonce: jQuery( "#_wpnonce" ).val(),
	};
	jQuery
	.post(
		admin_ajax_object.ajaxurl,
		data,
		function (response) {
			var html;
			if ( ! response || parseInt( response ) === -1) {
				html =
				'<div class="error"><p>Please enter valid Envato API Token</p></div>';
			} else if (
			response &&
			response.message &&
			response.result &&
			(String( response.result ) === "-3" ||
			String( response.result ) === "-2" ||
			String( response.result ) === "wp_error" ||
			String( response.result ) === "server_error")
			) {
				html = response.message;
			} else if (
			response &&
			response.message &&
			response.result &&
			String( response.result ) === "4"
			) {
				html = response.message;
			} else {
				html = "";
			}
			jQuery( ".wpssw-license-result" ).html( html );
			jQuery( "#licenceloader" ).hide();
			jQuery( "#licencetext" ).hide();
			jQuery( "#licence_submit" ).show();
		},
		"json"
	)
	.always( function (response) {} );
}
function wpsswCopy(id, targetid) {
	var copyText   = document.getElementById( id );
	var textArea   = document.createElement( "textarea" );
	textArea.value = copyText.textContent;
	document.body.appendChild( textArea );
	textArea.select();
	document.execCommand( "Copy" );
	textArea.remove();
	if ( ! jQuery( "#" + targetid ).hasClass( "tooltip-click" )) {
		jQuery( "#" + targetid ).addClass( "tooltip-click" );
		setTimeout(
			function () {
				jQuery( "#" + targetid ).removeClass( "tooltip-click" );
			},
			1000
		);
	}
}

function check_schedule_settings(idPrefix, scheduleFor) {
	if ( ! scheduleFor) {
		scheduleFor = "Sync";
	}
	var scheduling_enable = jQuery(
		'input[name="' + idPrefix + 'scheduling_enable"]:checked'
	).val();
	if (scheduling_enable == "1") {
		var scheduling_run_on = jQuery(
			'input[name="' + idPrefix + 'scheduling_run_on"]:checked'
		).val();
		if (scheduling_run_on == "weekly") {
			var weekly_days = jQuery( "#" + idPrefix + "weekly_days" ).val();
			if (weekly_days == "") {
				alert( "Schedule Auto " + scheduleFor + ":  Please select week day." );
				jQuery( "html, body" ).animate(
					{
						scrollTop: jQuery( "#" + idPrefix + "weekly" ).offset().top - 160,
					},
					1200
				);
				return false;
			}
		}
		if (scheduling_run_on == "onetime") {
			var scheduling_date = jQuery(
				'input[name="' + idPrefix + 'scheduling_date"]'
			).val();
			if ( ! scheduling_date) {
				jQuery( "html, body" ).animate(
					{
						scrollTop: jQuery( "#" + idPrefix + "onetime" ).offset().top - 160,
					},
					1200
				);
				alert( "Schedule Auto " + scheduleFor + ":  Please select date." );
				return false;
			}
			var scheduling_time = jQuery(
				'input[name="' + idPrefix + 'scheduling_time"]'
			).val();
			if ( ! scheduling_time) {
				jQuery( "html, body" ).animate(
					{
						scrollTop: jQuery( "#" + idPrefix + "onetime" ).offset().top - 160,
					},
					1200
				);
				alert( "Schedule Auto " + scheduleFor + ":  Please select time." );
				return false;
			}
		}
	}
}

function add_schedule_weekly_days(idPrefix = "") {
	jQuery( "#" + idPrefix + "weekly li" ).click(
		function () {
			if (jQuery( this ).hasClass( "selected" )) {
				jQuery( this ).removeClass( "selected" );
			} else {
				jQuery( this ).addClass( "selected" );
			}
			jQuery( "#" + idPrefix + "weekly_days" ).val( "" );

			jQuery( "#" + idPrefix + "weekly li.selected" ).each(
				function () {
					var val = jQuery( this ).data( "day" );
					jQuery( "#" + idPrefix + "weekly_days" ).val(
						jQuery( "#" + idPrefix + "weekly_days" ).val() + val + ","
					);
				}
			);
			jQuery( "#" + idPrefix + "weekly_days" ).val(
				jQuery( "#" + idPrefix + "weekly_days" )
				.val()
				.slice( 0, -1 )
			);
		}
	);
}
