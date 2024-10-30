(function ($) {
    $(function () {
        //'use strict';
        let [service_name, carrier_name, total_price, service_code] = ["", "", "", ""];
        let [shipping_country, shipping_city, shipping_postcode] = ["", "", ""];
        let recalculateFlag = false;

        $("body").on('click', "#place_order", async function (event) {

            if ("0" === insta_dispatch_vars.live_price) {
                return true;
            }

            if (isValidateCheckout()) {
                $(".woocommerce-checkout").submit();
                return;
            }

            let checkout_obj = {};
            $(".woocommerce-checkout").serializeArray().map(function (x) {
                checkout_obj[x.name] = x.value;
            });

            (async () => {

                let isDifferentShipping = checkShippingInfo(checkout_obj);
                if (isDifferentShipping) {
                    event.preventDefault();
                    event.stopPropagation();
                    let status = await reCalculateShipping(checkout_obj);
                    if (status) {
                        return;
                    }
                }

                let isRateFound = $("input[name='insta_rate']").val();
                if (isRateFound == "rate_not_found") {
                    event.preventDefault();
                    alert("Sorry, we do not ship to this location.");
                    return false;
                }

                const insta_shipping = jQuery('input[name="insta_shipping"]:checked').val();
                if (!insta_shipping) {
                    event.preventDefault();
                    alert("Please select one of the options from insta shipping");
                    return;
                }

                const data = {
                    'action': 'set_insta_shipping',
                    'insta_shipping': insta_shipping,
                    'total_price': total_price,
                    'carrier_name': carrier_name,
                    'service_name': service_name,
                    'service_code': service_code
                };

                jQuery.ajax({
                    type: "POST",
                    url: insta_dispatch_vars.ajax_url,
                    dataType: "json",
                    data: data,
                    success: function (response) {
                        $(".woocommerce-checkout").submit();
                        return true;
                    }
                });
            })().catch((error) => console.error(error));
        });


        $("body").on("click", ".service-type", function () {
            total_price = $(this).attr('data-price');
            carrier_name = $(this).val();
            service_name = $(this).attr('data-service-name');
            service_code = $(this).attr('data-service-code');
        });

        $(window).load(function () {
            shipping_country = $("#shipping_country").val();
            shipping_postcode = $("#shipping_postcode").val();
            shipping_city = $("#shipping_city").val();
        });

        const checkShippingInfo = (data) => {
            let errorFlag = false;
            if (data.shipping_country !== shipping_country) {
                errorFlag = true;
            }
            if (data.shipping_postcode !== shipping_postcode) {
                errorFlag = true;
            }
            if (data.shipping_city !== shipping_city) {
                errorFlag = true;
            }
            return errorFlag;
        }

        const isValidateCheckout = () => {
            let any_invalid = false;
            const ship_to_diff = $('#ship-to-different-address input').is(':checked');
            $('.validate-required').each(function () {
                let $this = $(this).find('input[type=checkbox],select,.input-text'),
                    $parent = $this.closest('.form-row'),
                    validated = true,
                    validate_required = $parent.is('.validate-required'),
                    validate_email = $parent.is('.validate-email');

                if ($this.val() === '') {
                    any_invalid = true;
                }
            });

            return any_invalid;
        }

        async function reCalculateShipping(dataObj) {

            dataObj.action = "recalculate_rate";
            recalculateFlag = false;
            try {
                await jQuery.ajax({
                    type: "POST",
                    url: insta_dispatch_vars.ajax_url,
                    dataType: "json",
                    data: dataObj,
                    beforeSend: function () {
                        $(".recalculate").show();
                    },
                    success: function (response) {
                        $(".recalculate").hide();
                        recalculateFlag = true;
                        shipping_country = dataObj.shipping_country
                        shipping_postcode = dataObj.shipping_postcode;
                        shipping_city = dataObj.shipping_city;
                        document.getElementById("insta_shipping").innerHTML = response;
                    },
                    error: function (err) {
                        console.log(err);
                    }
                });

            } catch (error) {
                console.log(error);
            }
            return recalculateFlag;

        }
    });
})(jQuery);