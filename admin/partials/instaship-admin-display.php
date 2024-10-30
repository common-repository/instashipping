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


if (!defined('WPINC')) {
    die;
}
$isLiveMode = get_option("live_mode");
$selected = ($isLiveMode === 1) ? "checked" : "";
?>

<div class="cls-loader">
    <div class="cls-loader-inner text-center">
        <div class="spinner-border" role="status">
            <span class="sr-only"></span>
        </div>
    </div>
</div>
<form action="<?php esc_html(admin_url('admin-post.php')); ?>" id="insta_ship_form" method="post">
    <?php wp_nonce_field('instadispatch-settings', 'insta-verify'); ?>
    <input type="checkbox" class="track_shipment_url insta_input insta_input_checkbox" value="1"
           name="live_mode" <?php echo $selected; ?>/> <?php esc_html_e("Enable Live Mode", "instaship"); ?><br/>

    <input type="text" class="track_shipment_url insta_input" placeholder="Track Shipment URL"
           value="<?php echo esc_attr(get_option("track_shipment_url")) ?>" name="track_shipment_url" required/><br/>
    <input type="text" class="quote_api_url insta_input" placeholder="Quote API URL"
           value="<?php echo esc_attr(get_option("quote_api_url")) ?>" name="quote_api_url"/><br/>
    <input type="text" class="booking_api_url insta_input" placeholder="Booking API URL"
           value="<?php echo esc_attr(get_option("booking_api_url")) ?>" name="booking_api_url"/><br/>
    <input type="text" class="authorization_key insta_input" placeholder="Authorization Key"
           value="<?php echo esc_attr(get_option("authorization_key")) ?>" name="authorization_key"/><br/>

    <input type="checkbox" class="track_shipment_url insta_input insta_input_checkbox" value="1"
           name="view_live_price"/>
    <?php esc_html_e("View live prices at checkout", "instaship"); ?><br/>
    <input type="checkbox" class="track_shipment_url insta_input insta_input_checkbox" value="1"
           name="auto_complete_order"/>
    <?php esc_html_e("Auto complete order", "instaship"); ?><br/>
    <input type="checkbox" class="track_shipment_url insta_input insta_input_checkbox" value="1"
           name="enable_multiple_packages"/>
    <?php esc_html_e("Enable Multiple Packages", "instaship"); ?><br/>

    <h4 class='default_label'><?php esc_html_e("Default Parcel Size", "instaship"); ?></h4>
    <table class="order_table_160 table table-condensed parceReadListTbl settings_parcel_table">
        <thead>
        <tr>
            <th><?php esc_html_e("Length", "instaship"); ?></th>
            <th><?php esc_html_e("Width", "instaship"); ?></th>
            <th><?php esc_html_e("Height", "instaship"); ?></th>
            <th><?php esc_html_e("Dimension Unit", "instaship"); ?></th>
            <th><?php esc_html_e("Weight", "instaship"); ?></th>
            <th><?php esc_html_e("Weight Unit", "instaship"); ?></th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>
                <input type="text" class="parcel_input" name="default_parcel_length"
                       value="<?php echo esc_attr(get_option("default_parcel_length")) ?>">
            </td>
            <td>
                <input type="text" class="parcel_input" name="default_parcel_width"
                       value="<?php echo esc_attr(get_option("default_parcel_width")) ?>">
            </td>
            <td>
                <input type="text" class="parcel_input" name="default_parcel_height"
                       value="<?php echo esc_attr(get_option("default_parcel_height")) ?>">
            </td>
            <td>
                <input type="text" class="parcel_input" name="default_parcel_unit"
                       value="<?php echo esc_attr(get_option("default_parcel_unit")) ?>">
            </td>
            <td>
                <input type="text" class="parcel_input" name="default_parcel_weight"
                       value="<?php echo esc_attr(get_option("default_parcel_weight")) ?>">
            </td>
            <td>
                <input type="text" class="parcel_input" name="default_parcel_weight_unit"
                       value="<?php echo esc_attr(get_option("default_parcel_weight_unit")) ?>">
            </td>
        </tr>
        </tbody>
    </table>
    <h4><?php esc_html_e("Active Carriers", "instaship"); ?></h4>
    <div id="enableCarriers"></div>

    <button type="button" id="saveSettings" class="btn btn-info">Save</button>
</form>