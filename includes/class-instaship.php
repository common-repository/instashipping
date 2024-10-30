<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://www.instadispatch.com/
 * @since      1.0.0
 *
 * @package    Instaship
 * @subpackage Instaship/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      4.3.0
 * @package    Instaship
 * @subpackage Instaship/includes
 * @author     InstaDispatch <admin@perceptive-solutions.com>
 */
class Instaship {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    4.3.0
	 * @access   protected
	 * @var      Instaship_Loader $loader Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    4.3.0
	 * @access   protected
	 * @var      string $plugin_name The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    4.3.0
	 * @access   protected
	 * @var      string $version The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    4.3.0
	 */
	public function __construct() {
		if ( defined( 'INSTASHIP_VERSION' ) ) {
			$this->version = INSTASHIP_VERSION;
		} else {
			$this->version = '4.3.0';
		}
		$this->plugin_name = 'instaship';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Instaship_Loader. Orchestrates the hooks of the plugin.
	 * - Instaship_i18n. Defines internationalization functionality.
	 * - Instaship_Admin. Defines all hooks for the admin area.
	 * - Instaship_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    4.3.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-instaship-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-instaship-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-instaship-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-instaship-public.php';

		/**
		 * The class responsible for defining all the common functions in the plugin.
		 */
		require_once plugin_dir_path( __DIR__ ) . 'includes/class-instaship-common-functions.php';

		/**
		 * The class responsible for defining all booking actions.
		 */
		require_once plugin_dir_path( __DIR__ ) . 'admin/class-instaship-admin-book-shipments.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( __DIR__ ) . 'admin/class-instaship-admin.php';


		$this->loader = new Instaship_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Instaship_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    4.3.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Instaship_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    4.3.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin   = new Instaship_Admin( $this->get_plugin_name(), $this->get_version() );
		$book_shipments = new Instaship_Admin_Book_Shipments( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		//======Admin Menus======//
		//$this->loader->add_action( "admin_menu", $plugin_admin, 'insta_menus' );
		$this->loader->add_action( "admin_menu", $plugin_admin, 'insta_admin_menu' );
		$this->loader->add_action( "admin_footer", $plugin_admin, 'insta_book_shipment' );
		//======End======//

		//======Ajax Actions======//
		$this->loader->add_action( "wp_ajax_save_settings", $plugin_admin, 'settings_handler' );
		$this->loader->add_action( "wp_ajax_enabled_carries", $plugin_admin, 'get_carrier_lists' );
		$this->loader->add_action( "wp_ajax_get_order_details", $book_shipments, 'get_order_details' );
		$this->loader->add_action( "wp_ajax_get_insta_dispatch_shipping", $book_shipments, 'get_insta_dispatch_shipping' );
		$this->loader->add_action( "wp_ajax_get_insta_dispatch_booking", $book_shipments, 'get_insta_dispatch_booking' );
		$this->loader->add_action( "wp_ajax_set_insta_shipping", $book_shipments, 'set_insta_shipping' );
		$this->loader->add_action( "wp_ajax_get_insta_dispatch_cancel", $book_shipments, 'cancel_shipment' );
		$this->loader->add_action( "wp_ajax_recalculate_rate", $book_shipments, 'recalculate_rate' );
		//======End======//

		//====Custom Actions====//
		add_action( 'manage_posts_extra_tablenav', array( $this, 'add_book_shipment_button' ), 99, 1 );
		add_action( 'add_meta_boxes_shop_order', array( $this, 'mv_add_meta_boxes' ) );
		add_action( 'manage_shop_order_posts_custom_column', array( $this, 'add_order_profit_column_content' ) );
		add_action( 'save_post', array( $this, 'mv_save_wc_order_other_fields' ) );
		add_action( 'manage_edit-shop_order_columns', array( $this, 'customer_shipping_preference' ) );
		add_action( 'woocommerce_thankyou', array( $book_shipments, 'insta_shipping_carrier' ), 10, 1 );
		//====End====//

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    4.3.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Instaship_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    4.3.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @return    string    The name of the plugin.
	 * @since    4.3.0
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @return    Instaship_Loader    Orchestrates the hooks of the plugin.
	 * @since     4.3.0
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @return    string    The version number of the plugin.
	 * @since     4.3.0
	 */
	public function get_version() {
		return $this->version;
	}

	public function add_book_shipment_button() {
		global $typenow;
		global $post;
		if ( 'shop_order' === $typenow ) {
			// create some tooltip text to show on hover
			$tooltip = __( 'Book Your Shipment', 'instaShip' );
			// create a button label
			$label = __( 'Book Shipment', 'instaShip' );
			echo '<button type="button" name="book-shipments_" style="height:32px;" class="button book-shipment">' . esc_html( $label ) . '</button>';
		}
	}

	public function mv_add_meta_boxes() {
		add_meta_box( 'InstaShip', __( 'Insta Shipping', 'instaShip' ), array(
			$this,
			'mv_add_other_fields_for_insta_shipping'
		), 'shop_order', 'side', 'core' );
	}

	public function mv_add_other_fields_for_insta_shipping() {
		global $post;

		if ( get_post_meta( $post->ID, 'insta_shipment_identity', true ) ) {

			$insta_service           = get_post_meta( $post->ID, 'insta_selected_service', true );
			$shipment_pdf            = get_post_meta( $post->ID, 'insta_shipment_pdf', true );
			$insta_track_url         = get_option( 'track_shipment_url' );
			$insta_shipment_identity = get_post_meta( $post->ID, 'insta_shipment_identity', true );

			echo "<strong>" . esc_html( 'Service' ) . "</strong> :" . esc_html( $insta_service ) . "<br /><br />";

			echo "<a href='" . esc_url( $shipment_pdf ) . "' target='__blank' class='btn' style='background:#0779b3; color:#fff; cursor:pointer; text-decoration:none; padding:7px 15px; border:none;' id='download_pdf' download>" . esc_html( "Label" ) . "</a>
                &nbsp;&nbsp;<a class='btn' style='background:#0779b3; color:#fff; cursor:pointer; text-decoration:none; padding:7px 15px; border:none;' id='cancel_shipment' order_id='" . esc_attr( $post->ID ) . "' identity='" . esc_attr( $insta_shipment_identity ) . "'>" . esc_html( "Cancel" ) . " </a >
                &nbsp;&nbsp;<a class='btn' style = 'background:#0779b3; color:#fff; cursor:pointer; text-decoration:none; padding:7px 15px; border:none;' href = '" . esc_url( $insta_track_url ) . esc_attr( $insta_shipment_identity ) . "' target = '_blank' > " . esc_html( "Track" ) . "</a > ";

		} else {

			echo "<a href='#' class='update_shipping insta_edit_order book-shipment' data-bs-toggle='modal' data-bs-target='#bookShipmentModal' order_id = '" . esc_attr( $post->ID ) . "' > " . esc_html( "Update Shipping" ) . " </a > ";
		}
	}

	public function add_order_profit_column_content( $column ) {
		global $post;

		$insta_shipment_identity = get_post_meta( $post->ID, 'insta_shipment_identity', true );
		if ( 'insta_shipping' === $column ) {
			$order              = wc_get_order( $post->ID );
			$insta_shipment_pdf = get_post_meta( $post->ID, 'insta_shipment_pdf', true );
			if ( $insta_shipment_pdf ) {
				echo "<a href= '" . esc_url( $insta_shipment_pdf ) . "' target = '__blank' class='insta-download-pdf' title='Download Label' ><img src = '" . esc_url( plugins_url( '../admin/img/download-pdf.png', __FILE__ ) ) . "' ></a >";
			}
		}

		if ( ( "insta_cancel_shipping" === $column ) && $insta_shipment_identity ) {
			echo "<a class='insta-cancel-shipment' title = 'Cancel Shipment' id = 'cancel_shipment' order_id = '" . $post->ID . "' identity = '" . esc_attr( $insta_shipment_identity ) . "' >
			       <img src = '" . esc_url( plugins_url( '../admin/img/cancel.png', __FILE__ ) ) . "' ></a > ";
		}
		if ( 'insta_carrier' === $column ) {
			echo esc_attr( get_post_meta( $post->ID, 'insta_shipping', true ) );
		}

		if ( "insta_service_name" === $column ) {
			echo esc_attr( get_post_meta( $post->ID, 'insta_service_name', true ) );
		}

		if ( "insta_service_price" === $column ) {
			echo wc_price( get_post_meta( $post->ID, 'insta_total_price', true ) );
		}
	}

	public function mv_save_wc_order_other_fields( $post_id ) {
		if ( isset( $_POST['insta_service'] ) ) {
			update_post_meta( $post_id, 'insta_service', sanitize_text_field( $_POST['insta_service'] ) );
		}
	}

	public function customer_shipping_preference( $columns ) {

		$new_columns = array();

		foreach ( $columns as $column_name => $column_info ) {

			$new_columns[ $column_name ] = $column_info;

			if ( 'order_total' === $column_name ) {
				$new_columns['insta_carrier']         = __( 'Customer Shipping Preference', $this->plugin_name );
				$new_columns['insta_service_name']    = __( 'Service Name', $this->plugin_name );
				$new_columns['insta_service_price']   = __( 'Service Price', $this->plugin_name );
				$new_columns['insta_shipping']        = __( 'Shipping PDF', $this->plugin_name );
				$new_columns['insta_cancel_shipping'] = __( 'Cancel Shipment', $this->plugin_name );
			}
		}

		return $new_columns;
	}
}
