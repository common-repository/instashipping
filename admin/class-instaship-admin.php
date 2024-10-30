<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.instadispatch.com/
 * @since      1.0.0
 *
 * @package    Instaship
 * @subpackage Instaship/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Instaship
 * @subpackage Instaship/admin
 * @author     InstaDispatch <admin@perceptive-solutions.com>
 */
class Instaship_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	protected $instaCore;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version The version of this plugin.
	 *
	 * @since    1.0.0
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		$this->instaCore   = new Instaship_common_functions();
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Instaship_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Instaship_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		wp_enqueue_style( 'bootstrap', plugin_dir_url( __FILE__ ) . 'css/insta-bootstrap.min.css', array(), '', 'all' );
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/instaship-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Instaship_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Instaship_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( 'bootstrap-min', plugin_dir_url( __FILE__ ) . 'js/insta-bootstrap.min.js', array( 'jquery' ), '', false );
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/instaship-admin.js', array( 'jquery' ), $this->version, false );

		wp_localize_script( $this->plugin_name, "insta_vars", array(
			"insta_ajax_url"    => admin_url( "admin-ajax.php" ),
			"live_price"        => get_option( "view_live_price" ),
			"isLiveMode"        => get_option( "live_mode" ),
			"auto_complete"     => get_option( "auto_complete_order" ),
			"multiple_packages" => get_option( "enable_multiple_packages" ),
			"insta_auth_key"    => get_option( "authorization_key" ),
			"enabled_carrier"   => get_option( "default_carrier" )
		) );
	}

	public function insta_admin_menu() {
		add_menu_page( "InstaShip", "InstaShip", "edit_posts", "InstaShip", array(
			$this,
			"InstaShip_callback_function"
		), "dashicons-format-image" );
		add_submenu_page( "InstaShip", "InstaShip", "Settings", "manage_options", "InstaShip", array(
			$this,
			"InstaShip_callback_function"
		) );
	}

	public function InstaShip_callback_function() {
		include_once INSTA_PATH . 'admin/partials/instaship-admin-display.php';
	}

	public function insta_book_shipment() {
		global $pagenow, $typenow;
		if ( 'shop_order' === $typenow && ( 'edit.php' === $pagenow || 'post.php' === $pagenow ) ) {
			include_once INSTA_PATH . 'admin/partials/instaship-shipping-book-shipment.php';
		}
	}

	public function settings_handler() {
		$resp    = "";
		$param   = isset( $_REQUEST["param"] ) ? trim( sanitize_text_field( wp_unslash( $_REQUEST["param"] ) ) ) : "";
		$authKey = isset( $_REQUEST["authorization_key"] ) ? esc_textarea( $_REQUEST["authorization_key"] ) : "";
		if ( empty( $authKey ) ) {
			$res = wp_json_encode( [ "status" => "false", "message" => "Please enter valid Authorization Key" ] );
			wp_die( $res );
		}
		if ( $param == "insta_settings" ) {
			$option_args = [
				"live_mode"                  => ! empty( $_REQUEST["live_mode"] ) ? (int) $_REQUEST["live_mode"] : 0,
				"default_carrier"            => ! empty( $_REQUEST["default_carrier"] ) ? sanitize_text_field( $_REQUEST["default_carrier"] ) : 0,
				"enable_multiple_packages"   => ! empty( $_REQUEST["enable_multiple_packages"] ) ? (int) $_REQUEST["enable_multiple_packages"] : 0,
				"auto_complete_order"        => ! empty( $_REQUEST["auto_complete_order"] ) ? (int) $_REQUEST["auto_complete_order"] : 0,
				"view_live_price"            => ! empty( $_REQUEST["view_live_price"] ) ? (int) $_REQUEST["view_live_price"] : 0,
				"default_parcel_length"      => ! empty( $_REQUEST["default_parcel_length"] ) ? (int) $_REQUEST["default_parcel_length"] : 10,
				"default_parcel_width"       => ! empty( $_REQUEST["default_parcel_width"] ) ? (int) $_REQUEST["default_parcel_width"] : 10,
				"default_parcel_height"      => ! empty( $_REQUEST["default_parcel_height"] ) ? (int) $_REQUEST["default_parcel_height"] : 10,
				"default_parcel_weight"      => ! empty( $_REQUEST["default_parcel_weight"] ) ? (int) $_REQUEST["default_parcel_weight"] : 1,
				"default_parcel_weight_unit" => ! empty( $_REQUEST["default_parcel_weight_unit"] ) ? sanitize_text_field( $_REQUEST["default_parcel_weight_unit"] ) : "kg",
				"default_parcel_unit"        => ! empty( $_REQUEST["default_parcel_unit"] ) ? sanitize_text_field( $_REQUEST["default_parcel_unit"] ) : "cm",
				"track_shipment_url"         => ! empty( $_REQUEST["track_shipment_url"] ) ? sanitize_url( $_REQUEST["track_shipment_url"] ) : "",
				"quote_api_url"              => ! empty( $_REQUEST["quote_api_url"] ) ? sanitize_url( $_REQUEST["quote_api_url"] ) : "",
				"booking_api_url"            => ! empty( $_REQUEST["booking_api_url"] ) ? sanitize_url( $_REQUEST["booking_api_url"] ) : "",
				"authorization_key"          => $authKey
			];

			foreach ( $option_args as $key => $item ) {
				if ( $item !== "" ) {
					update_option( $key, $item );
				}
			}
			$resp = wp_json_encode( [ "status" => "true", "message" => "success" ] );
		}

		wp_die( $resp );
	}

	public function getInstaSettingsValues() {
		return [
			"live_mode"                  => esc_attr( get_option( "live_mode" ) ),
			"default_carrier"            => esc_attr( get_option( "default_carrier" ) ),
			"enable_multiple_packages"   => esc_attr( get_option( "enable_multiple_packages" ) ),
			"auto_complete_order"        => esc_attr( get_option( "auto_complete_order" ) ),
			"view_live_price"            => esc_attr( get_option( "view_live_price" ) ),
			"default_parcel_length"      => esc_attr( get_option( "default_parcel_length" ) ),
			"default_parcel_width"       => esc_attr( get_option( "default_parcel_width" ) ),
			"default_parcel_height"      => esc_attr( get_option( "default_parcel_height" ) ),
			"default_parcel_weight"      => esc_attr( get_option( "default_parcel_weight" ) ),
			"default_parcel_weight_unit" => esc_attr( get_option( "default_parcel_weight_unit" ) ),
			"default_parcel_unit"        => esc_attr( get_option( "default_parcel_unit" ) ),
			"track_shipment_url"         => esc_attr( get_option( "track_shipment_url" ) ),
			"quote_api_url"              => esc_attr( get_option( "quote_api_url" ) ),
			"booking_api_url"            => esc_attr( get_option( "booking_api_url" ) ),
			"authorization_key"          => esc_attr( get_option( "authorization_key" ) )
		];
	}

	/**
	 * @throws JsonException
	 */
	public function get_carrier_lists() {
		$response = $this->instaCore->get_enabled_carriers();
		if ( isset( $response["status"] ) && $response["status"] == "fail" ) {
			wp_die( wp_json_encode( $response ) );
		}
		echo wp_json_encode( [
			"status"  => "true",
			"data"    => $response,
			"options" => $this->getInstaSettingsValues()
		] );
		wp_die();
	}
}
