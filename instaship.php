<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.instadispatch.com/
 * @since             4.3.0
 * @package           Instaship
 *
 * @wordpress-plugin
 * Plugin Name:       instashipping
 * Plugin URI:        https://www.instadispatch.com/
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           4.3.0
 * Author:            InstaDispatch
 * Author URI:        https://www.instadispatch.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       instaship
 * Domain Path:       /languages
 * Requires at least: 5.6
 * Requires PHP: 7.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! defined( "INSTA_PATH" ) ) {
	define( 'INSTA_PATH', plugin_dir_path( __FILE__ ) );
}

if ( get_option( 'live_mode' ) === "1" ) {
	define( "INSTA_CARRIERS_API", "https://api.instadispatch.com/live/restservices/getEnableCarrierServices" );
	define( "INSTA_QUOTATION_API", "https://api.instadispatch.com/live/restservices/getQuotation" );
	define( "INSTA_BOOK_QUOTATION_API", "https://api.instadispatch.com/live/restservices/bookQuotation" );
	define( "INSTA_ENABLED_CARRIERS", "https://api.instadispatch.com/live/restservices/getCarriers" );
	define( "INSTA_CANCEL_SHIPMENT", "https://api.instadispatch.com/live/restservices/cancelJob" );
} else {
	define( "INSTA_CARRIERS_API", "https://api.instadispatch.com/app-allignment/parcel-api/restservices/getEnableCarrierServices" );
	define( "INSTA_QUOTATION_API", "https://api.instadispatch.com/app-allignment/parcel-api/restservices/getQuotation" );
	define( "INSTA_BOOK_QUOTATION_API", "https://api.instadispatch.com/app-allignment/parcel-api/restservices/bookQuotation" );
	define( "INSTA_ENABLED_CARRIERS", "https://api.instadispatch.com/app-allignment/parcel-api/restservices/getCarriers" );
	define( "INSTA_CANCEL_SHIPMENT", "https://api.instadispatch.com/app-allignment/parcel-api/restservices/cancelJob" );
}


/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'INSTASHIP_VERSION', '4.3.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-instaship-activator.php
 */
function activate_instaship() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-instaship-activator.php';
	Instaship_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-instaship-deactivator.php
 */
function deactivate_instaship() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-instaship-deactivator.php';
	Instaship_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_instaship' );
register_deactivation_hook( __FILE__, 'deactivate_instaship' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-instaship.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_instaship() {

	$plugin = new Instaship();
	$plugin->run();

}

run_instaship();
