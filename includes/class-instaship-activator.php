<?php

/**
 * Fired during plugin activation
 *
 * @link       https://www.instadispatch.com/
 * @since      1.0.0
 *
 * @package    Instaship
 * @subpackage Instaship/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Instaship
 * @subpackage Instaship/includes
 * @author     InstaDispatch <admin@perceptive-solutions.com>
 */
class Instaship_Activator
{

    /**
     * Short Description. (use period)
     *
     * Long Description.
     *
     * @since    1.0.0
     */
    public static function activate()
    {
        //Check Parent Plugin
        if (!is_plugin_active('woocommerce/woocommerce.php') && current_user_can('activate_plugins')) {
            // Stop activation redirect and show error
            wp_die('This plugin requires <b>Wooommerce</b> Plugin to be installed and active. <br><a href="' . admin_url('plugins.php') . '">&laquo; Return to Plugins</a>');
        }

        //Check Mode
        $isLiveMode = get_option("live_mode");
        if (!$isLiveMode) {
            update_option("live_mode", 0);
        }
    }

}
