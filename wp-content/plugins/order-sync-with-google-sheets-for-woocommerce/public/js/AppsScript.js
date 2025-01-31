const baseURL = "{site_url}",
  accessToken = "{token}",
  order_statuses = "{order_statuses}",
  sheetTab = "{sheet_tab}";

  
  function RunOSGSW() {
    if (!osgsw_current_sheet()) return;
    osgsw_add_menus();
    osgsw_apply_styles();
}

function onOpen() {
    RunOSGSW();
}

function osgsw_current_sheet() {
    return SpreadsheetApp.getActiveSheet().getSheetName() === sheetTab;
}

function onEdit(e) {
    if (e == null || e == 'undefined' || e == '') return;
    if (e.triggerUid == null) return;
    if (!osgsw_current_sheet() || osgsw_current_row() == 1) return;
    const currentColumn = osgsw_current_column();
    var osgs_ui = SpreadsheetApp.getUi();
    var check_non_edit = false;
    if (["order_Id"].includes(currentColumn)) {
        osgs_ui.alert("This column is not editable");
        osgsw_toast2('The order is being processed again. Please do not close or interrupt the sync... Please wait!.', 'Loading!');
        osgsw_fetch_from_WordPress();
        return;
    } else if (currentColumn !== "order_status") {
        osgs_ui.alert("This column is not editable");
        var default_text = 'The order is being processed again. Please do not close or interrupt the sync... Please wait!.';
        check_non_edit = true;
    }
    if(!check_non_edit) {
      osgsw_toast2('Updating data... Please wait.', 'Loading!');
    } else {
      osgsw_toast2(default_text, 'Loading!');
    }

    let data = osgsw_get_edited_data(e);
    let key_value = osgsw_columns(currentColumn, true);
    if (typeof key_value === "undefined") {
        key_value = currentColumn;
    }
    let message = key_value + " updated successfully!";
    if (check_non_edit) {
      message = key_value + " back successfully!";
    }
    osgsw_sync_data(data, message, false, check_non_edit);
}

function osgsw_toast2(message = null, title = null) {
    SpreadsheetApp.getActiveSpreadsheet().toast(message, title, -1);
}

function osgsw_toast_for_background_process(message = null, title = null, time_count = 1) {
    SpreadsheetApp.getActiveSpreadsheet().toast(message, title, time_count);
}
function osgsw_add_menus() {
  try {
    SpreadsheetApp.getUi().createMenu("Order Sync").addItem("⟱ Fetch Order from WooCommerce", "osgsw_fetch_from_WordPress").addSeparator().addItem(" Format Styles", "osgsw_apply_styles").addItem(" About Order Sync", "osgsw_about_us").addToUi();
  }catch (e) {
        Logger.log('Already Setup');
  }

}

function osgsw_apply_styles() {
  try {
    const StaticColumns = osgsw_column_index(["order_date", "order_Id", "shipping_information", "product_names", "payment_method", "customer_note", "order_url", "order_quantity", "order_totals", "discount_total", "billing_details", "order_placed_by", ]).filter((index) => index >= 0).map(osgsw_column_char);
    const OrderColumn = osgsw_column_index(["order_status"]).map((index) => osgsw_column_char(index))[0];
    const StaticColumnHeaders = StaticColumns.map((column) => column + 1);
    const StaticColumnValues = StaticColumns.map((column) => column + 2 + ":" + column);
    const OrderColumnValues = OrderColumn + 2 + ":" + OrderColumn;
    const CenterableColumns = osgsw_column_index(["order_status"]).filter((index) => index >= 0).map((char) => osgsw_column_char(char) + "1:" + osgsw_column_char(char));
    const Color = {
        primary: "#686de0",
        white: "white",
        black: "black",
        grey: "#dedede",
        success: "green",
        error: "indianred",
        info: "purple",
        warning: "orange",
    };
    const dynamicColumnRange = osgsw_getDynamicColumnRange(StaticColumnHeaders.length);
    const dynamic_values = dynamicColumnRange[0];
    const dynamic_header = dynamicColumnRange[1];
    const CurrentSheet = SpreadsheetApp.getActive().getSheetByName(sheetTab);
    CurrentSheet.getRangeList(['C1']).setFontWeight("bold").setBackground(Color.primary).setFontColor(Color.white);
    CurrentSheet.autoResizeColumns(1, osgsw_max_column());
    Logger.log(StaticColumnHeaders);
    CurrentSheet.getRangeList(StaticColumnHeaders).setBackground(Color.error).setFontWeight("bold");
    if (dynamic_values.length > 0) {
        CurrentSheet.getRangeList(dynamic_values).setBackground(Color.grey).setFontColor(Color.black).setFontWeight("normal");
        CurrentSheet.getRangeList(dynamic_header).setBackground(Color.error).setFontWeight("bold");
    }
    CurrentSheet.getRangeList(['C2:C']).setBackground(Color.white);
    CurrentSheet.getRangeList(StaticColumnValues).setBackground(Color.grey).setFontColor(Color.black).setFontWeight("normal");;
    CurrentSheet.getRangeList(CenterableColumns).setHorizontalAlignment("left");
    let rules = [];
    rules.push(SpreadsheetApp.newConditionalFormatRule().whenTextEqualTo("wc-completed").setBackground("#f7fff9").setFontColor("green").setRanges([SpreadsheetApp.getActiveSheet().getRange(OrderColumnValues)]).build());
    rules.push(SpreadsheetApp.newConditionalFormatRule().whenTextEqualTo("wc-failed").setBackground("#fff8f7").setFontColor(Color.error).setRanges([SpreadsheetApp.getActiveSheet().getRange(OrderColumnValues)]).build());
    rules.push(SpreadsheetApp.newConditionalFormatRule().whenTextEqualTo("'wc-refunded'").setBackground("#fff8f7").setFontColor(Color.error).setRanges([SpreadsheetApp.getActiveSheet().getRange(OrderColumnValues)]).build());
    rules.push(SpreadsheetApp.newConditionalFormatRule().whenTextEqualTo("wc-checkout-draft").setBackground("#fff8f7").setFontColor(Color.error).setRanges([SpreadsheetApp.getActiveSheet().getRange(OrderColumnValues)]).build());
    rules.push(SpreadsheetApp.newConditionalFormatRule().whenTextEqualTo("wc-cancelled").setBackground("#fff8f7").setFontColor(Color.error).setRanges([SpreadsheetApp.getActiveSheet().getRange(OrderColumnValues)]).build());
    rules.push(SpreadsheetApp.newConditionalFormatRule().whenTextEqualTo("wc-processing").setBackground("#fffdf7").setFontColor("orange").setRanges([SpreadsheetApp.getActiveSheet().getRange(OrderColumnValues)]).build());
    rules.push(SpreadsheetApp.newConditionalFormatRule().whenTextEqualTo("wc-pending").setBackground("#fffdf7").setFontColor("orange").setRanges([SpreadsheetApp.getActiveSheet().getRange(OrderColumnValues)]).build());
    rules.push(SpreadsheetApp.newConditionalFormatRule().whenTextEqualTo("wc-on-hold").setBackground("#fffdf7").setFontColor("orange").setRanges([SpreadsheetApp.getActiveSheet().getRange(OrderColumnValues)]).build());
    rules.push(SpreadsheetApp.newConditionalFormatRule().whenNumberGreaterThan(0).setBackground("#f7fff9").setFontColor(Color.success).setRanges([SpreadsheetApp.getActiveSheet().getRange(OrderColumnValues)]).build());
    rules.push(SpreadsheetApp.newConditionalFormatRule().whenNumberLessThan(1).setBackground("#fff8f7").setFontColor(Color.error).setRanges([SpreadsheetApp.getActiveSheet().getRange(OrderColumnValues)]).build());
    rules.push(SpreadsheetApp.newConditionalFormatRule().whenFormulaSatisfied('=LEFT(C2, 3) = "wc-"').setBackground("#fffdf7").setFontColor("orange").setRanges([SpreadsheetApp.getActiveSheet().getRange(OrderColumnValues)]).build());
    CurrentSheet.setConditionalFormatRules(rules);
    Logger.log("Order Sync Installed!");
    } catch (e) {
        Logger.log('Already Style');
   }
}

function osgsw_about_us() {
    let htmlOutput = HtmlService.createHtmlOutput(`<h3>FlexOrder</h3> <p>Sync your WooCommerce Order with Google Sheets.</p> <p><a href="https://wordpress.org/plugins/order-sync-with-google-sheets-for-woocommerce/" target="_blank">Download Free</a> version from WordPress.org</p> <p><a href="https://wcordersync.com/pricing/" target="_blank">Get Ultimate</a> version to enjoy all premium features and official updates.</p> `).setWidth(550).setHeight(200);
    SpreadsheetApp.getUi().showModalDialog(htmlOutput, "FlexOrder");
}

function osgsw_headers() {
    let header = SpreadsheetApp.getActive().getSheetByName(sheetTab).getRange("A1:Z1").getValues();
    header = header[0].filter((column) => column.length);
    return header;
}

function osgsw_columns($key = null, $reversed = false) {
    let columns = {
        "Order ID": "order_Id",
        "Product Names": "product_names",
        "Order Status": "order_status",
        "Total Items": "order_quantity",
        "Total Price": "order_totals",
        "Total Discount": "discount_total",
        "Billing Details": "billing_details",
        "Order Date": "order_date",
        "Shipping Details": "shipping_information",
        "Payment Method": "payment_method",
        "Customer Note": "customer_note",
        "Order Placed by": "order_placed_by",
        "Order URL": "order_url",
    };
    if ($key) {
        if (!$reversed) {
            return columns[$key];
        } else {
            let reverse = {};
            for (let key in columns) {
                reverse[columns[key]] = key;
            }
            return reverse[$key];
        }
    }
    return columns;
}

function osgsw_available_columns() {
    let columns = osgsw_columns();
    let headers = osgsw_headers();
    let keys = {};
    headers.forEach((header) => {
        if (Object.keys(columns).includes(header)) {
            keys[header] = columns[header];
        }
    });
    return keys;
}

function osgsw_max_column() {
    let maxColumn = SpreadsheetApp.getActive().getSheetByName(sheetTab).getLastColumn();
    return maxColumn;
}

function osgsw_column_char(index = 0) {
    const alphabet = "abcdefghijklmnopqrstuvwxyz".toUpperCase().split("");
    if (index > 26) {
        return getA1NotationForColumn(index);
    } else {
        return alphabet[index - 1] || null;
    }
}

function osgsw_current_row() {
    let currentCell = SpreadsheetApp.getActive().getSheetByName(sheetTab).getCurrentCell().getA1Notation();
    let row = currentCell.replace(/[^0-9]/g, "");
    return row;
}

function osgsw_current_column() {
    let currentCell = SpreadsheetApp.getActive().getSheetByName(sheetTab).getCurrentCell().getA1Notation();
    let rowNotation = currentCell.replace(/[0-9]/g, "");
    rowNotation = "abcdefghijklmnopqrstuvwxyz".toUpperCase().split("").indexOf(rowNotation);
    let column = Object.values(osgsw_available_columns())[rowNotation];
    return column;
}

function osgsw_column_index(columns) {
    let indexes = [];
    let available_columns = osgsw_available_columns();
    columns.forEach((column) => {
        let index = Object.values(available_columns).indexOf(column);
        if (index >= 0) index++;
        indexes.push(index);
    });
    return indexes;
}

function osgsw_format(data) {
    const deletables = ["order_url"];
    const keys = osgsw_ordered_keys();
    data = data.map((row) => {
        return Object.assign.apply({}, keys.map((v, i) => ({
            [v]: row[i],
        })));
    }).map((row) => {
        deletables.forEach((key) => {
            if (key in row) delete row[key];
        });
        return row;
    });
    return data;
}

function osgsw_new_getColumnLetter(columnIndex) {
  var columnLetter = '';
  while (columnIndex > 0) {
    var modulo = (columnIndex - 1) % 26;
    columnLetter = String.fromCharCode(modulo + 'A'.charCodeAt(0)) + columnLetter;
    columnIndex = Math.floor((columnIndex - modulo) / 26);
  }
  return columnLetter;
}

function osgsw_getDynamicColumnRange(staticColumnsEndIndex) {
    var ss = SpreadsheetApp.getActiveSpreadsheet();
    var sheet = ss.getActiveSheet();
    const startColumnIndex = staticColumnsEndIndex + 1;
    const startColumn = startColumnIndex;
    const endColumn = sheet.getLastColumn();
    var columns = [];
    var column_header = [];

    for (var col = startColumn; col <= endColumn; col++) {
      var columnLetter = osgsw_new_getColumnLetter(col);
      column_header.push(columnLetter + '1');
      columns.push(columnLetter + '2:' + columnLetter);
    }

  return [columns,column_header];
}

function getA1NotationForColumn(columnIndex) {
    const sheet = SpreadsheetApp.getActiveSpreadsheet().getActiveSheet();
    const cell = sheet.getRange(1, columnIndex);
    return cell.getA1Notation();
}

function osgsw_getDynamicColumnIndex() {
    const sheet = SpreadsheetApp.getActiveSpreadsheet().getActiveSheet();
    var endColumn = sheet.getLastColumn();
    const cell = sheet.getRange(1, endColumn);
    const enda1Notation = cell.getA1Notation();
    const alphabetPart = enda1Notation.match(/[A-Z]+/i)[0];
    return alphabetPart;
}

function osgsw_get_all_data() {
    var values = SpreadsheetApp.getActive().getSheetByName(sheetTab).getDataRange().getValues();
    values.shift();
    return osgsw_format(values);
}
function osgsw_save_temp_info(key = 'osgsw_time_storage', data = '') {
    const properties = PropertiesService.getScriptProperties();
    properties.setProperty(key, data);
    Logger.log("Saved" + key + " information");
}
function osgsw_get_edited_data(e) {
   var sheet = e.source.getSheetByName(sheetTab);
    var rowStart = e.range.rowStart;
    var rowEnd = e.range.rowEnd;
    var colStart = e.range.getColumn();
    var colEnd = colStart + e.range.getNumColumns() - 1;
    osgsw_save_temp_info('osgsw_start_colmun', colStart);
    osgsw_save_temp_info('osgsw_end_colmun', colEnd);
    var get_combined_range = sheet.getRange(rowStart, 1, rowEnd - rowStart + 1, sheet.getLastColumn()); 
    var data = get_combined_range.getValues();
    return osgsw_on_edit_format(data, rowStart, colStart, colEnd);
}

function osgsw_on_edit_format(data, rowStart, colStart, colEnd) {
    colStart = colStart- 1;
    colEnd = colEnd - 1;
   const keyss = osgsw_ordered_keys();
    return data.map((row, index) => {
        let formattedRow = {};
        let formattedRow_extra = {};
        formattedRow["index_number"] = rowStart + index;
        formattedRow_extra["index_number"] = rowStart + index;
        keyss.forEach((key, i) => {
          if (i == 0 || (i >= colStart && i <= colEnd) ) {
            formattedRow[key] = row[i];
            formattedRow_extra[key] = row[i];
          } else {
            formattedRow_extra[key] = row[i];
          }
        });
        formattedRow["osgsw_extra_data_info"] = JSON.stringify(formattedRow_extra);
        return formattedRow;
    });
}

function osgsw_ordered_keys() {
    let orderedKeys = [];
    osgsw_headers().forEach((header) => {
        orderedKeys.push(osgsw_available_columns()[header]);
    });
    return orderedKeys;
}

function osgsw_sync_all() {
    let data = osgsw_get_all_data();
    osgsw_sync_data(data);
}

function osgsw_toast(message = null, title = null) {
    SpreadsheetApp.getActiveSpreadsheet().toast(message, title);
}


function osgsw_get_temp_information(key = 'osgsw_time_storage') {
    var new_properties = PropertiesService.getScriptProperties();
    return new_properties.getProperty(key) ? new_properties.getProperty(key) : null;
}
function osgsw_get_dynamic_range_data(startIndex = 0, endIndex = 0, start_colmun = 0) {
    var sheet = SpreadsheetApp.getActive().getSheetByName(sheetTab);
    if (!sheet) {
        return;
    }
    if (endIndex < startIndex) {
        return;
    }
    if( start_colmun ) {
        var end_column = parseInt(osgsw_get_temp_information('osgsw_end_colmun'));
        var get_combined_range = sheet.getRange(startIndex, 1, endIndex - startIndex + 1, sheet.getLastColumn()); 
        var data = get_combined_range.getValues();
        return osgsw_on_edit_format(data, startIndex, start_colmun, end_column);
    }
    return;
}

function osgsw_sync_data_in_background_process() {
    var start_index = parseInt(osgsw_get_temp_information('osgsw_start_index'));
    var end_index = parseInt(osgsw_get_temp_information('osgsw_end_index'));
    var start_colmun = parseInt(osgsw_get_temp_information('osgsw_start_colmun'));
    var orders = osgsw_get_dynamic_range_data(start_index, end_index, start_colmun);
    if (Array.isArray(orders) && orders.length > 0) {
        var message = osgsw_get_temp_information('osgsw_save_message');
        if(start_colmun) {
          osgsw_sync_data(orders, message, true, false);
        }
    }
    return;
}
function osgsw_delete_trigger_by_id(triggerId) {
    var triggers = ScriptApp.getProjectTriggers();
    if (triggers.length === 0) {
        return;
    }
    for (var i = 0; i < triggers.length; i++) {
        var trigger = triggers[i];
        if (trigger.getUniqueId() === triggerId) {
            ScriptApp.deleteTrigger(trigger);
            return;
        }
    }
}
function osgsw_create_trigger() {
    var get_trigger_id = osgsw_get_temp_information('osgsw_trigger_id');
    if (get_trigger_id) {
        osgsw_delete_trigger_by_id(get_trigger_id);
    }
    var trigger = ScriptApp.newTrigger('osgsw_sync_data_in_background_process').timeBased().after(60000).create();
    var triggerId = trigger.getUniqueId();
    Logger.log(triggerId);
    osgsw_save_temp_info('osgsw_trigger_id', triggerId);
}
function osgsw_progress_bar(totalUpdated, totalOrders) {
    if (totalUpdated > totalOrders) {
        totalUpdated = totalOrders;
    }
    osgsw_toast2("Successfully updated " + totalUpdated + " out of " + totalOrders + " Orders! Please do not close or interfere with the sync process. This may take a moment!", 'Success');
}
function osgsw_remove_first_data_from_array(data, total_update_need) {
    return data.slice(total_update_need);
}
function osgsw_calculate_execution_time(totalOrders, excution_time, total_update_need) {
    var totalBatches_time = Math.ceil(totalOrders / total_update_need);
    var total_trigger = Math.ceil(totalOrders / total_update_need);
    var total_trigger_time = total_trigger * 60;
    var total_batch_time = totalBatches_time * excution_time;
    return total_batch_time + total_trigger_time;
}

function osgsw_sync_data(data, message = "Orders synced successfully", time_triven = false, non_edit = false ) {
    try {
        let orders = data.filter((row) => {
          return row[2] !== "" && row[0] !== "order_Id";
        });
        Logger.log(orders);
        if (!orders.length) return;
        var chunkSize = 5000;
        var total_update_need = 10000;
        let totalUpdated = 0;
        let total_orders = orders.length;
        if (!time_triven) {
            osgsw_save_temp_info('osgsw_total_of_orders', total_orders);
        }

        if (!non_edit) {
            osgsw_toast2("Updating " + total_orders + " orders... Please wait!", 'Loading...');
        }
        for (let i = 0; i < total_orders; i += chunkSize) {
            let orderChunk = orders.slice(i, i + chunkSize);
            const arrayLength = orderChunk.length;
            const firstIndex = orderChunk[0]['index_number'];
            const lastIndex = orderChunk[arrayLength - 1]['index_number'];
            const newapp = true;
            if (!time_triven) {
                var start_time = new Date().getTime();
            }
            let response = UrlFetchApp.fetch(baseURL + "/wp-json/osgsw/v1/update", {
                method: "POST",
                payload: JSON.stringify({
                    orders: orderChunk,
                    message,
                    arrayLength,
                    firstIndex,
                    lastIndex,
                    newapp
                }),
                contentType: "application/json",
                muteHttpExceptions: true,
                headers: {
                    OSGSWKEY: "Bearer " + accessToken,
                }
            });

            if (response.getResponseCode() == 200) {
                response = JSON.parse(response.getContentText());
                if (response.success) {
                    totalUpdated += arrayLength;

                    osgsw_progress_bar(totalUpdated, total_orders);
                    if (totalUpdated > total_update_need - 1 && total_update_need < total_orders) {
                        var new_data = osgsw_remove_first_data_from_array(orders, total_update_need);
                        var new_data_length = new_data.length;
                        if (new_data_length) {
                            osgsw_create_trigger();
                            var new_start_index = new_data[0]['index_number'];
                            var new_end_index = new_data[new_data_length - 1]['index_number'];
                            osgsw_save_temp_info('osgsw_start_index', new_start_index);
                            osgsw_save_temp_info('osgsw_end_index', new_end_index);
                            osgsw_save_temp_info('osgsw_save_message', message);
                            if (!time_triven) {
                                var end_time = new Date().getTime();
                                var execution_mili_second = end_time - start_time;
                                var excution_time = execution_mili_second / 1000;
                                var popup_stay_time = osgsw_calculate_execution_time(total_orders, excution_time, total_update_need);
                                osgsw_toast_for_background_process("We're processing your request. An email will be sent to the admin when complete.", '!Notice', popup_stay_time);
                            }
                        }
                        return;
                    }
                    
                } else {
                    osgsw_toast(response.message, "Ops error!");
                    return;
                }
            } else {
                if (response.getResponseCode() == 401 || response.getResponseCode() == 403) {
                    osgsw_toast('Authentication Failed: REST API is not supported on your system', "Ops!");
                    return;
                } else {
                    osgsw_toast("Something went wrong, please try again", "Oopss!");
                    return;
                }
            }
        }
    } catch (error) {
        Logger.log('something');
        osgsw_toast("Something went wrong, please try again", "Oopss!");
        return;
    }
    osgsw_toast(message, "Success!");
    if (time_triven) {
        osgsw_order_update_email();
    }
    return true;
}

function osgsw_order_update_email() {
    var totalOrders = osgsw_get_temp_information('osgsw_total_of_orders'); 
    var subject = "FlexOrder Notice: Your Order Data Update/Sync is Complete";
    var body = `
        <p>Hi,</p>
        <p>Your order data update and sync process has been successfully completed!</p>
        <p><strong>Summary of the Process:</strong></p>
        <ul>
            <li><strong>Orders Processed:</strong> ${totalOrders}</li>
        </ul>
        <p>You can review the updated data in WooCommerce or Google Sheets.</p>
        <p>We appreciate your patience while the batch processing was in progress.</p>
        <p>Thank you for using FlexOrder!</p>
        <p>Best regards,<br>FlexOrder Team</p>
    `;
    var email = Session.getActiveUser().getEmail();
    
    MailApp.sendEmail({
        to: email,
        subject: subject,
        htmlBody: body
    });

    Logger.log("Email sent to: " + email + " with total orders: " + totalOrders);
}
    

function osgsw_fetch_from_WordPress2( message = "Orders fetched from WordPress") {
    let response = UrlFetchApp.fetch(baseURL + "/wp-json/osgsw/v1/action/?action=sync", {
        method: "GET",
        contentType: "application/json",
        muteHttpExceptions: true,
        headers: {
            OSGSWKEY: "Bearer " + accessToken,
        },
    });
    if (response.getResponseCode() == 200) {
        response = JSON.parse(response.getContentText());
        if (response.success) {
            osgsw_toast(message, "Success!");
        } else if (response.message) {
            osgsw_toast(response.message, "Ops!");
        }
    } else {
        osgsw_toast('Authentication Failed: REST API is not supported on your system', "Ops!");
    }
}

function osgsw_fetch_from_WordPress(message = "Orders fetched from WordPress") {
    try {
        let response = UrlFetchApp.fetch(baseURL + "/wp-json/osgsw/v1/action/?action=sync", {
            method: "POST",
            contentType: "application/json",
            muteHttpExceptions: true,
            headers: {
                OSGSWKEY: "Bearer " + accessToken,
            },
            payload: JSON.stringify({
                osgsw_items: true,
            }),
        });
        if (response.getResponseCode() == 200) {
            response = JSON.parse(response.getContentText());
            if (response.success) {
                var batchSize = 1500;
                const totalOrders = response.message;
                let offset = 0;
                var error_count = 0;
                var success_count = 0;
                while (offset < totalOrders) {
                    try {
                        let new_response = UrlFetchApp.fetch(baseURL + "/wp-json/osgsw/v1/action/?action=sync", {
                            method: "POST",
                            contentType: "application/json",
                            muteHttpExceptions: true,
                            headers: {
                                OSGSWKEY: "Bearer " + accessToken,
                            },
                            payload: JSON.stringify({
                                osgsw_items: false,
                                osgsw_offset: offset,
                                osgsw_batch_size: batchSize,
                            }),
                        });
                        if (new_response.getResponseCode() == 200) {
                            new_response = JSON.parse(new_response.getContentText());
                            if (new_response.success) {
                                var uploadedProducts = offset + batchSize;
                                success_count++;
                                osgsw_progress_bar(uploadedProducts, totalOrders);
                                offset += batchSize;
                            } else {
                                error_count++;
                            }
                        }
                        if (error_count > 5) {
                            offset += batchSize;
                        }
                    } catch (error) {
                        error_count++;
                    }
                }
                if (success_count) {
                    osgsw_toast("Order fetched from WordPress", "Success!");
                }
            } else if (response.message) {
                osgsw_toast(response.message, "Ops!");
            }
        } else {
            if (response.getResponseCode() == 401 || response.getResponseCode() == 403) {
               osgsw_toast('Authentication Failed: REST API is not supported on your system', "Ops!");
            } else {
                osgsw_toast("Something went wrong, please try again", "Oopss!");
            }
        }
    } catch (error) {
        osgsw_toast("Something went wrong, please try again", "Oopss!");
    }
}

