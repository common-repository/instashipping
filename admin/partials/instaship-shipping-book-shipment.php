<?php

/**
 * Provide admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://www.instadispatch.com/
 * @since      1.0.0
 *
 * @package    Instaship
 * @subpackage Instaship/admin/partials
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

$obj             = new Instaship_common_functions();
$carriers        = $obj->get_enabled_carriers();
$enable_carriers = json_decode( json_encode( $carriers ), false );

$default_parcel_length = get_option( 'default_parcel_length' );
if ( ! $default_parcel_length ) {
	$default_parcel_length = 10;
}

$default_parcel_width = get_option( 'default_parcel_width' );
if ( ! $default_parcel_width ) {
	$default_parcel_width = 10;
}

$default_parcel_height = get_option( 'default_parcel_height' );
if ( ! $default_parcel_height ) {
	$default_parcel_height = 10;
}

$default_parcel_weight = get_option( 'default_parcel_weight' );
if ( ! $default_parcel_weight ) {
	$default_parcel_weight = 1;
}

$default_parcel_weight_unit = get_option( 'default_parcel_weight_unit' );
if ( ! $default_parcel_weight_unit ) {
	$default_parcel_weight_unit = 'kg';
}

$default_parcel_unit = get_option( 'default_parcel_unit' );
if ( ! $default_parcel_unit ) {
	$default_parcel_unit = 'cm';
}
?>
<div class="cls-loader">
    <div class="cls-loader-inner text-center">
        <div class="spinner-border" role="status">
            <span class="visually-hidden"></span>
        </div>
    </div>
</div>

<input type="hidden" id="default_parcel_length" value="<?php echo esc_attr( $default_parcel_length ); ?>">
<input type="hidden" id="default_parcel_width" value="<?php echo esc_attr( $default_parcel_width ); ?>">
<input type="hidden" id="default_parcel_height" value="<?php echo esc_attr( $default_parcel_height ); ?>">
<input type="hidden" id="default_parcel_weight" value="<?php echo esc_attr( $default_parcel_weight ); ?>">
<input type="hidden" id="default_parcel_unit" value="<?php echo esc_attr( $default_parcel_unit ); ?>">
<input type="hidden" id="default_parcel_weight_unit" value="<?php echo esc_attr( $default_parcel_weight_unit ); ?>">
<div class="modal instadispatch_modal fade" id="bookShipmentModal" tabindex="-1" role="dialog" style="display: none;">

    <div class="modal-dialog modal-lg">

        <!-- Modal content-->
        <div class="modal-content" id="orderModelContent">
            <div class="modal-header orderFirstPage" id="orderModelHeader">
                <button type="button" class="close" data-bs-dismiss="modal">&times;</button>
                <h4 class="modal-title">Create Shipment for <span id="noOfOrder">1</span> orders</h4>
            </div>
            <div class="modal-body-new orderFirstPage">
				<?php
				if ( ! get_option( 'enable_multiple_packages' ) ) {
					?>
                    <style>
                        a.addEmptyParcel {
                            display: none;
                        }
                    </style>
					<?php
				}
				?>
                <div class="ordersDiv">

                </div>
				<?php if ( $enable_carriers ) { ?>
                    <div class="chooseCarrierSection">
                        <div class="checkLebel">
                            <div class="footer-top-headline">
                                <div class="col-md-8">
                                    <h4><?php esc_html_e( "Choose carriers preferences", "instaship" ); ?></h4></div>
                                <div class="col-md-4"></div>
                            </div>

                            <table id="carriertable" style="width: 100%">
                                <tbody>
                                </tbody>
                                <tbody>
								<?php
								foreach ( $enable_carriers as $result ) {
									$checked = "";
									if ( esc_attr( get_option( 'default_carrier' ) ) === $result->code && esc_attr( get_option( 'view_live_price' ) ) === "0" ) {
										$checked = "checked";
									}
									?>
                                    <tr>
                                        <td>
											<?php echo esc_attr( $result->name ); ?>
                                        </td>
                                        <td>
                                            <div style="float: right;margin-top: 7px;padding-right: 15px;"
                                                 class="switches"><input type="checkbox"
                                                                         class="chosenCarrier"
                                                                         name="chosenCarrier"
                                                                         value="<?php echo esc_attr( $result->code ); ?>" <?php echo $checked; ?>
                                                                         onclick="selectCarrier(this)">
                                            </div>
                                        </td>
                                    </tr>
									<?php
								}
								?>
                                </tbody>
                            </table>
                        </div>
                    </div>
					<?php
				}
				?>
                <div class="modal-footer">
                    <div class="button-align">
                        <button type="button" all_orders="" class="btn blue" id="goToGetService">Next Step
                        </button>
                        <div class="loader-goToGetService"></div>
                    </div>
                </div>
            </div>

            <div class="modal-body-new orderServiceList orderSecondPage" style="display:none;">
                <div id="orderSecondPage_div"></div>
                <div class="modal-footer">
                    <div class="button-align">
                        <button type="button" class="btn btn-default cancel-booking"
                                data-dismiss="modal"><?php echo esc_html_e( 'Cancel', 'Instaship' ) ?></button>
                        <button type="button" class="btn blue sendToBook"
                                id="sendToBook"><?php echo esc_html_e( 'Book', 'Instaship' ) ?></button>
                        <div class="loader-sendToBook"></div>
                    </div>
                </div>
            </div>
            <div class="modal-body-new orderServiceList orderThirdPage" style="display:none;">
                <div id="orderThirdPage_div"></div>
                <div class="modal-footer">
                    <div class="button-align">
                        <button type="button" onClick="window.location.reload();" class="btn btn-default">Ok
                        </button>
                    </div>
                </div>
            </div>

        </div>

    </div>
</div>