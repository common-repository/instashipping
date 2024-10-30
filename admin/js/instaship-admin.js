(function ($) {
    'use strict';

    /**
     * All of the code for your admin-facing JavaScript source
     * should reside in this file.
     *
     * Note: It has been assumed you will write jQuery code here, so the
     * $ function reference has been prepared for usage within the scope
     * of this function.
     *
     * This enables you to define handlers, for when the DOM is ready:
     *
     * $(function() {
     *
     * });
     *
     * When the window is loaded:
     *
     * $( window ).load(function() {
     *
     * });
     *
     * ...and/or other possibilities.
     *
     * Ideally, it is not considered best practise to attach more than a
     * single DOM-ready or window-load handler for a particular page.
     * Although scripts in the WordPress core, Plugins and Themes may be
     * practising this, we should strive to set a better example in our own work.
     */

    $(function () {
        $("#saveSettings").click(function () {
            var formData = $("#insta_ship_form").serialize() + '&action=save_settings&param=insta_settings';
            jQuery(".cls-loader").show();
            jQuery.post(insta_vars.insta_ajax_url, formData).done(function (data) {
                jQuery(".cls-loader").hide();
                var obj = jQuery.parseJSON(data);
                if (obj.status == "true") {
                    window.location.reload(true);
                }
                if (obj.status == "false") {
                    alert(obj.message);
                }
            }).fail(function (xhr, status, error) {
                alert(xhr.responseText);
            });
        });

        $.wait = function (ms) {
            let defer = $.Deferred();
            setTimeout(function () {
                defer.resolve();
            }, ms);
            return defer;
        };

        //======Book Shipment======//
        let order_id_arr = "";
        const isShipmentBooked = false;
        jQuery(".book-shipment").on('click', function () {

                let order_id = "";
                let flag = 0;
                let isBooked = false;
                if (jQuery(this).attr('order_id')) {
                    order_id = jQuery(this).attr('order_id');
                } else {

                    jQuery('input[name="post[]"]:checked').each(function () {
                            if (typeof insta_dispatch_batch_vars !== "undefined") {
                                let isAssign = jQuery("#post-" + this.value + " .insta-dispatch-batch").text();
                                if (isAssign !== '(not assigned)') {
                                    isBooked = true;
                                }
                            }
                            if (jQuery("#post-" + this.value + " .insta_shipping a").hasClass('insta-download-pdf')) {
                                isBooked = true;
                            }
                            if (!jQuery("#post-" + this.value + " .order_status mark").hasClass('status-processing')) {
                                flag = 1;
                            }
                            let carrier = jQuery("#post-" + this.value + " .insta_carrier").text();
                            jQuery('input:checkbox[name="chosenCarrier"][value="' + carrier + '"]').attr('checked', 'checked');

                            if (order_id == "") {
                                order_id = this.value;
                            } else {
                                order_id = order_id + ',' + this.value;
                            }

                        }
                    );
                }

                if (isBooked) {
                    alert("Please remove booked shipment from your selection.");
                    return false;
                }

                if (flag == 1) {
                    alert("Shipping can only be updated for processing orders.");
                    return false;
                }
                if (order_id == "") {
                    alert("Please select order to update.");
                    return false;
                }

                jQuery(".cls-loader").show();
                $.wait(2000).then(() => {


                    jQuery("#goToGetService").attr('all_orders', order_id);
                    order_id_arr = order_id.split(',');
                    jQuery("#noOfOrder").text(order_id_arr.length);
                    jQuery("#order_id").text(order_id);
                    let final_html = "";
                    const res = [];

                    for (const i in order_id_arr) {
                        const data = {
                            'order_id': order_id_arr[i],
                            'action': 'get_order_details',
                        };
                        jQuery.ajax({
                            type: "POST",
                            async: false,
                            url: insta_vars.insta_ajax_url,
                            dataType: "json",
                            data: data,
                            success: function (response) {
                                $("#bookShipmentModal").modal("show");
                                res[order_id_arr[i]] = response;
                            }
                        });

                        final_html += '<div class="main-panel orderDiv"><div class="headingPanel">Recipient: <br><span id="recipient_' + order_id_arr[i] + '">' + res[order_id_arr[i]].recipient + '</span></div><div class="main-padding"><h4>Parcels</h4><a href="#" order_id="' + order_id_arr[i] + '" class="addEmptyParcel">&#43;</a><div class="table-responsive"><table class="order_table_' + order_id_arr[i] + ' table table-condensed parceReadListTbl" style="border-collapse:collapse;"><thead><tr><th>Length</th><th>Width</th><th>Height</th><th>Dimension unit</th><th>Weight</th><th>Weight unit</th><th></th></tr></thead><tbody><tr><td><input type="text" class="parcel_input" name="tLen[]" value="' + jQuery("#default_parcel_length").val() + '"></td><td><input type="text" class="parcel_input" name="tWid[]" value="' + jQuery("#default_parcel_width").val() + '"></td><td><input type="text" class="parcel_input" name="tHei[]" value="' + jQuery("#default_parcel_height").val() + '"></td><td><input type="text" class="parcel_input" name="dunit[]" value="' + jQuery("#default_parcel_unit").val() + '"></td><td><input type="text" class="parcel_input" name="twei[]" value="' + jQuery("#default_parcel_weight").val() + '"></td><td><input type="text" class="parcel_input" name="unit[]" value="' + jQuery("#default_parcel_weight_unit").val() + '"></td><td><input type="hidden" name="rowIndex[]" value="0"><button type="button" class="btn btn-danger btn-xs deletepacco">Delete</button></td></tr></tbody></table></div><div class="emptyParcelContainer"><div class="newparceldiv_' + order_id_arr[i] + ' newparceldiv emptyParcelDiv" style="display:none;"><span order_id="' + order_id_arr[i] + '" class="closeParcelWindow">&#10006;</span><div class="form-group new-lab inputContainer"><label>Dimensions</label><br><input type="number" placeholder="Length" class="form-control col-width tempLength" id="length_' + order_id_arr[i] + '"> x <input type="number" placeholder="Width" id="width_' + order_id_arr[i] + '" class="form-control col-width tempWidth"> x <input type="number" placeholder="Height" class="form-control col-width tempHeight" id="height_' + order_id_arr[i] + '"><select class="form-control col-width-inc" id="unit_' + order_id_arr[i] + '"><option value="cm">cm</option></select> <input type="number" placeholder="Weight" class="form-control col-width tempWeight" id="weight_' + order_id_arr[i] + '" placeholder="Weight"></div><div class="form-group new-lab"><button type="button" class="btn btn-md btn-primary savetempparcel" style="float: right" order_id="' + order_id_arr[i] + '">Add a parcel</button></div></div><div class="new-bottom-section"><div class="form-group new-lab"><label for="item_description">Goods Description</label><input type="text" placeholder="" class="form-control orderParcelGoodDesc" id="description_' + order_id_arr[i] + '" value="' + res[order_id_arr[i]].item + '"><input type="text" placeholder="" class="form-control orderParcelGoodDesc" style="display:none;" id="item_quantity_' + order_id_arr[i] + '" value="' + res[order_id_arr[i]].item_quantity + '"><input type="text" placeholder="" class="form-control orderParcelGoodDesc" style="display:none;" id="item_value_' + order_id_arr[i] + '" value="' + res[order_id_arr[i]].item_value + '"></div><div class="row margin-top-10"><div class="col-md-6"><div class="form-group new-lab width-icon"><label>Insurance</label><br><input type="text" class="form-control col-width orderParcelInsurance" id="insurance_' + order_id_arr[i] + '" value="0"><span><i class="fa fa-eur" aria-hidden="true"></i></span></div></div>';
                        if (res[order_id_arr[i]].payment_method == "epoch" || res[order_id_arr[i]].payment_method == "cod") {
                            final_html += '<div class="col-md-6"><div class="form-group new-lab width-icon"><label for="cashondelivery">Cash on delivery</label><br><input type="text" class="form-control col-width orderParcelCOD" id="cod_' + order_id_arr[i] + '" value="' + res[order_id_arr[i]].total + '"><span><i class="fa fa-eur" aria-hidden="true"></i></span></div></div>';
                        }

                        final_html += '</div></div></div></div></div>';
                    }
                    jQuery(".cls-loader").hide();
                    //console.log(res);
                    //console.log(final_html);
                    jQuery(".ordersDiv").html(final_html);
                });
            }
        );

        jQuery(".cancel-booking").on("click", function () {
            $("#bookShipmentModal").modal("hide");
            location.reload();
        })

        jQuery('#bookShipmentModal').on('hidden.bs.modal', function () {
            location.reload();
        });
        jQuery(document).on("click", ".deletepacco", function () {
            jQuery(this).parents("tr").remove();
        });
        jQuery("body").on('click', ".savetempparcel", function () {
            var order_id = jQuery(this).attr('order_id');
            var length = jQuery("#length_" + order_id).val();
            var width = jQuery("#width_" + order_id).val();
            var height = jQuery("#height_" + order_id).val();
            var unit = jQuery("#unit_" + order_id).val();
            var weight = jQuery("#weight_" + order_id).val();
            if (length == "" || width == "" || height == "" || unit == "" || weight == "") {
                alert("Please fill all parcel dimensions.");
                return false;
            } else {
                jQuery(".parceReadListTbl.order_table_" + order_id + " tbody").append('<tr><td><input type="text" class="parcel_input" name="tLen[]" value="' + length + '"></td><td><input type="text" class="parcel_input" name="tWid[]" value="' + width + '"></td><td><input type="text" class="parcel_input" name="tHei[]" value="' + height + '"></td><td><input type="text" class="parcel_input" name="dunit[]" value="' + unit + '"></td><td><input type="text" class="parcel_input" name="twei[]" value="' + weight + '"></td><td><input type="text" class="parcel_input" name="unit[]" value="kg"></td><td><input type="hidden" name="rowIndex[]" value="0"><button type="button" class="btn btn-danger btn-xs deletepacco">Delete</button></td></tr>');
                jQuery("#length_" + order_id).val("");
                jQuery("#width_" + order_id).val("");
                jQuery("#height_" + order_id).val("");
                jQuery("#weight_" + order_id).val("");
            }
        });

        jQuery("body").on('click', '#goToGetService', function () {
            var isChecked = isCheckCarrier();
            if (!isChecked) {
                alert('Please choose carrier!!!');
                return false;
            }

            jQuery("#goToGetService").hide();
            jQuery(".loader-goToGetService").show();
            var all_orders = jQuery("#goToGetService").attr('all_orders');
            var order_id_arr = all_orders.split(',');
            //console.log(order_id_arr); return ;
            var final_html = "";
            //var chosenCarrier = jQuery('input[name="chosenCarrier"]:checked').val();
            let chosenCarrier = $('input[name="chosenCarrier"]:checked').map(function (_, el) {
                return $(el).val();
            }).get();
            //return false;
            jQuery("#orderSecondPage_div").append('<style>.set_for_all_div {margin-bottom: 20px;margin-top: 10px;} span.set_for_all{background: #0073aa; cursor:pointer; color: #fff;padding: 10px 20px;font-size: 14px;}</style><div class="set_for_all_div"><span class="set_for_all">Select this service for all</span></div>');
            for (var i in order_id_arr) {
                // alert(i);
                // alert(order_id_arr[i]);
                var length = jQuery(".order_table_" + order_id_arr[i] + " input[name='tLen[]']")
                    .map(function () {
                        return jQuery(this).val();
                    }).get();
                var width = jQuery(".order_table_" + order_id_arr[i] + " input[name='tWid[]']")
                    .map(function () {
                        return jQuery(this).val();
                    }).get();
                var height = jQuery(".order_table_" + order_id_arr[i] + " input[name='tHei[]']")
                    .map(function () {
                        return jQuery(this).val();
                    }).get();
                var weight = jQuery(".order_table_" + order_id_arr[i] + " input[name='twei[]']")
                    .map(function () {
                        return jQuery(this).val();
                    }).get();
                var insurance_amount = jQuery("#insurance_" + order_id_arr[i]).val();
                var item_description = jQuery("#description_" + order_id_arr[i]).val();
                var cod_amount = jQuery("#cod_" + order_id_arr[i]).val();
                var delivery_country = 'Slovenia';
                var delivery_county = 'Sevnica';
                var delivery_postcode = '8295';
                var delivery_city = 'Tržišče';
                var collection_country = 'Slovenia';
                var collection_county = 'Sevnica';
                var collection_postcode = '8295';
                var collection_city = 'Tržišče';

                var data = {
                    'order_id': order_id_arr[i],
                    'action': 'get_insta_dispatch_shipping',
                    'length': length,
                    'height': height,
                    'width': width,
                    'weight': weight,
                    'delivery_country': delivery_country,
                    'delivery_county': delivery_county,
                    'delivery_postcode': delivery_postcode,
                    'delivery_city': delivery_city,
                    'collection_country': collection_country,
                    'collection_county': collection_county,
                    'collection_postcode': collection_postcode,
                    'collection_city': collection_city,
                    'insurance_amount': insurance_amount,
                    'cod_amount': cod_amount,
                    'item_description': item_description,
                    'chosenCarrier': chosenCarrier,
                    's_no': i,
                };
                jQuery.ajax({
                    type: "POST",
                    url: insta_vars.insta_ajax_url,
                    dataType: "text",
                    data: data,
                    success: function (response) {
                        //alert(response);
                        //jQuery(this).text("Next Step");
                        jQuery(".orderFirstPage").hide();
                        jQuery("#orderSecondPage_div").append(response);
                        jQuery(".orderSecondPage").show();
                    }
                });
            }
            return false;
        });

        jQuery("body").on('click', '#sendToBook', function () {
            jQuery("#sendToBook").hide();
            jQuery(".loader-sendToBook").show();
            var all_orders = jQuery("#goToGetService").attr('all_orders');
            var order_id_arr = all_orders.split(',');
            //var chosenCarrier = jQuery('input[name="chosenCarrier"]:checked').val();
            const final_html = "";
            for (const i in order_id_arr) {
                var length = jQuery(".order_table_" + order_id_arr[i] + " input[name='tLen[]']")
                    .map(function () {
                        return jQuery(this).val();
                    }).get();
                var width = jQuery(".order_table_" + order_id_arr[i] + " input[name='tWid[]']")
                    .map(function () {
                        return jQuery(this).val();
                    }).get();
                var height = jQuery(".order_table_" + order_id_arr[i] + " input[name='tHei[]']")
                    .map(function () {
                        return jQuery(this).val();
                    }).get();
                var weight = jQuery(".order_table_" + order_id_arr[i] + " input[name='twei[]']")
                    .map(function () {
                        return jQuery(this).val();
                    }).get();
                var selected_code = jQuery("input[name='insta_service_" + order_id_arr[i] + "']:checked").val();
                var service_act_number = jQuery("input[name='insta_service_" + order_id_arr[i] + "']:checked").attr('act_number');
                var chosenCarrier = jQuery("input[name='insta_service_" + order_id_arr[i] + "']:checked").attr('carrier');
                //alert(service_act_number);
                var delivery_country = 'Slovenia';
                var delivery_county = 'Sevnica';
                var delivery_postcode = '8295';
                var delivery_city = 'Tržišče';
                var collection_country = 'Slovenia';
                var collection_county = 'Sevnica';
                var collection_postcode = '8295';
                var collection_city = 'Tržišče';
                var cod_amount = jQuery("#cod_" + order_id_arr[i]).val();
                var insurance_amount = jQuery("#insurance_" + order_id_arr[i]).val();
                var item_description = jQuery("#description_" + order_id_arr[i]).val();
                var item_quantity = jQuery("#item_quantity_" + order_id_arr[i]).val();
                var item_value = jQuery("#item_value_" + order_id_arr[i]).val();
                var item_weight = weight.reduce(function (a, b) {
                    return a + b;
                }, 0);

                var data = {
                    'order_id': order_id_arr[i],
                    'action': 'get_insta_dispatch_booking',
                    'quotation_ref': jQuery("#quotation_ref_" + order_id_arr[i]).val(),
                    'act_number': service_act_number,
                    'selected_code': selected_code,
                    'length': length,
                    'height': height,
                    'width': width,
                    'weight': weight,
                    'delivery_country': delivery_country,
                    'delivery_county': delivery_county,
                    'delivery_postcode': delivery_postcode,
                    'delivery_city': delivery_city,
                    'collection_country': collection_country,
                    'collection_county': collection_county,
                    'collection_postcode': collection_postcode,
                    'collection_city': collection_city,
                    'insurance_amount': insurance_amount,
                    'cod_amount': cod_amount,
                    'item_description': item_description,
                    'item_quantity': item_quantity,
                    'item_value': item_value,
                    'item_weight': item_weight,
                    'chosenCarrier': chosenCarrier,
                };
                jQuery.ajax({
                    type: "POST",
                    url: insta_vars.insta_ajax_url,
                    dataType: "text",
                    data: data,
                    success: function (response) {
                        jQuery(".orderFirstPage").hide();
                        jQuery(".orderSecondPage").hide();
                        jQuery("#orderThirdPage_div").append(response);
                        jQuery(".orderThirdPage").show();
                    }
                });
            }
            return false;
        });

        jQuery("body").on('click', '#cancel_shipment', function () {

            let isCancelShipment = confirm("Are you sure you want to cancel this shipment?");
            if (!isCancelShipment) {
                event.preventDefault();
                return false;
            }

            var data = {
                'order_id': jQuery(this).attr('order_id'),
                'action': 'get_insta_dispatch_cancel',
                'identity': jQuery(this).attr('identity'),
            };

            jQuery(".cls-loader").show();
            jQuery.ajax({
                type: "POST",
                url: insta_vars.insta_ajax_url,
                dataType: "text",
                data: data,
                success: function (response) {
                    jQuery(".cls-loader").hide();
                    alert(response);
                    location.reload();
                }
            });
            return false;
        });
        jQuery("body").on('click', ".addEmptyParcel", function () {
            jQuery(this).attr('order_id');
            jQuery(".newparceldiv_" + jQuery(this).attr('order_id')).show();
        });
        jQuery("body").on('click', ".closeParcelWindow", function () {
            jQuery(this).attr('order_id');
            jQuery(".newparceldiv_" + jQuery(this).attr('order_id')).hide();
        });

        jQuery("body").on('click', ".set_for_all", function () {
            var first_selected = jQuery(".insta_service_order:radio:checked:first").val();
            jQuery('input[class="insta_service_order"][value="' + first_selected + '"]').prop('checked', true);
        });

        //======End======//
        jQuery("body").on('change', ".insta_service_order", function () {
            let service_name = $(this).val();
            let order_id = $(this).attr('data-order-id');
            let preferred_service_name = $(`input[name="preferred_service_name_${order_id}"]`).val();
            if (service_name !== preferred_service_name) {
                let confirmMsg = confirm(`Do you want to change the customer's  preferred shipping selection?`);
                if (!confirmMsg) {
                    $("input[name='insta_service_" + order_id + "']").removeAttr('checked');
                    $("input[name='insta_service_" + order_id + "'][value=" + preferred_service_name + "]").prop('checked', true);
                }
            }
        });

        $(window).load(function () {
            if (!window.adminpage || 'toplevel_page_InstaShip' !== window.adminpage) {
                return;
            }
            userSelectedValues();
            getCarrierLists();
        });
    });

    var getCarrierLists = () => {
        var postObj = {
            "authKey": jQuery(".authorization_key").val(),
            "param": "getCarriers",
            "action": "enabled_carries"
        }
        jQuery(".cls-loader").show();
        jQuery.post(insta_vars.insta_ajax_url, postObj).done(function (data) {
            jQuery(".cls-loader").hide();
            var parseJson = jQuery.parseJSON(data);
            if (parseJson.status == "true") {
                jQuery(".cls-loader").hide();
                renderCarriers(parseJson.data);
            }
            if (parseJson.status !== "true") {
                alert(parseJson.message);
                let htmlStr = `<p style="color: red;font-size: 17px;font-weight: bold;">${parseJson.message}</p>`;
                jQuery("#enableCarriers").empty().html(htmlStr);
            }
        }).fail(function (xhr, status, error) {
            alert(xhr.responseText);
        });
    }

    var renderCarriers = (obj) => {
        var htmlStr = "";
        var selectedCarrier = "";
        $.each(obj, function (key, item) {
            selectedCarrier = (insta_vars.live_price === "1") ? "" : (item.code == insta_vars.enabled_carrier) ? "checked" : "";
            htmlStr += '<input type="radio" name="default_carrier" class="default_carrier" value="' + item.code + '" ' + selectedCarrier + '>' + item.name + '<br/>';
        });
        jQuery("#enableCarriers").empty().html(htmlStr);
    }

    var userSelectedValues = () => {
        $('input[name="view_live_price"][value="' + insta_vars.live_price.toString() + '"]').prop("checked", true);
        $('input[name="auto_complete_order"][value="' + insta_vars.auto_complete.toString() + '"]').prop("checked", true);
        $('input[name="enable_multiple_packages"][value="' + insta_vars.multiple_packages.toString() + '"]').prop("checked", true);
        $('input[name="live_mode"][value="' + insta_vars.isLiveMode.toString() + '"]').prop("checked", true);
    }

    const isCheckCarrier = () => {
        const checkboxes = Array.from(document.querySelectorAll(".chosenCarrier"));
        return checkboxes.reduce((acc, curr) => acc || curr.checked, false);
    }
})(jQuery);

function selectCarrier(evt) {
    return;
    var myCarrier = document.getElementsByName("chosenCarrier");
    Array.prototype.forEach.call(myCarrier, function (el) {
        el.checked = false;
    });
    evt.checked = true;
}
