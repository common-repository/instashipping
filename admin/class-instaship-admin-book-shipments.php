<?php

use MailPoet\Form\Block\Html;


/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.instadispatch.com/
 * @since      1.0.0
 *
 * @package    Instaship
 * @subpackage Instaship Shipment/admin
 */
class Instaship_Admin_Book_Shipments {

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

	protected $insta_core;

	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		$this->insta_core  = new Instaship_common_functions();

		$this->setup_hooks();
	}

	public function setup_hooks() {
		add_action( 'woocommerce_checkout_order_review', array( $this, 'insta_order_review' ) );
		add_action( 'woocommerce_review_order_before_submit', array( $this, 'set_insta_loader' ) );
	}

	/**
	 * Get Order Details By Order ID
	 * @return void
	 */
	public function get_order_details() {
		$order_id = (int) $_POST['order_id'];
		$order    = wc_get_order( $order_id );

		$res['payment_method'] = $order->get_payment_method();
		$res['total']          = $order->get_total();
		$res['item']           = "";
		if ( $order->get_shipping_first_name() ) {
			$res['recipient'] = $order->get_shipping_first_name() . " " . $order->get_shipping_last_name() . ",<br />" . $order->get_shipping_city() . "," . $order->get_shipping_country() . "," . $order->get_shipping_postcode();
		} else {
			$res['recipient'] = $order->get_billing_first_name() . " " . $order->get_billing_last_name() . ",<br />" . $order->get_billing_city() . "," . $order->get_billing_country() . "," . $order->get_billing_postcode();
		}
		foreach ( $order->get_items() as $item_id => $item ) {
			$product_id = $item->get_product_id();
			$product    = wc_get_product( $product_id );
			$sku        = $product->get_sku();
			if ( $sku == "" ) {
				$sku = "No SKU";
			}
			if ( $res['item'] == "" ) {
				$res['item']          = $sku . "(" . $item->get_quantity() . ")";
				$res['item_quantity'] = $item->get_quantity();
				$res['item_value']    = $product->get_price();
			} else {
				$res['item']          .= "," . $sku . "(" . $item->get_quantity() . ")";
				$res['item_quantity'] = $res['item_quantity'] + $item->get_quantity();
				$res['item_value']    = $res['item_value'] + $product->get_price();
			}
		}

		wp_die( wp_json_encode( $res ) );
	}

	/**
	 * Get Shipment Rates
	 * @return void
	 */
	public function get_insta_dispatch_shipping() {

		$result_html   = "";
		$chosenCarrier = array_map( 'sanitize_text_field', $_POST["chosenCarrier"] ?? "" );
		$order_id      = (int) $_POST['order_id'];
		$order         = wc_get_order( $order_id );
		$service_code  = get_post_meta( $order_id, "insta_service_code", true );
		//$delivery_country = $order->get_billing_country();

		$collection_country_arr = explode( ":", get_option( 'woocommerce_default_country' ) );
		$collection_country     = WC()->countries->countries[ $collection_country_arr[0] ];

		$specialword = "(";
		if ( strpos( $collection_country, $specialword ) !== false ) {
			$collection_country = substr( $collection_country, 0, strpos( $collection_country, "(" ) );
			$collection_country = trim( $collection_country );
		}

		$collection_city     = get_option( 'woocommerce_store_city' );
		$collection_postcode = get_option( 'woocommerce_store_postcode' );
		$collection_county   = $collection_country_arr[1];

		$delivery_country = WC()->countries->countries[ $order->get_billing_country() ];
		if ( $order->get_shipping_country() ) {
			$delivery_country = WC()->countries->countries[ $order->get_shipping_country() ];
		}


		$specialword = "(";

		if ( strpos( $delivery_country, $specialword ) !== false ) {
			$delivery_country = substr( $delivery_country, 0, strpos( $delivery_country, "(" ) );
			$delivery_country = trim( $delivery_country );
		}

		$delivery_city = $order->get_billing_city();
		if ( $order->get_shipping_city() ) {
			$delivery_city = $order->get_shipping_city();
		}

		$delivery_postcode = $order->get_billing_postcode();
		if ( $order->get_shipping_postcode() ) {
			$delivery_postcode = $order->get_shipping_postcode();
		}

		$delivery_county = $order->get_billing_state();
		if ( $order->get_shipping_state() ) {
			$delivery_county = $order->get_shipping_state();
		}

		$insurance_amount = sanitize_text_field( $_POST['insurance_amount'] );
		$is_insured       = sanitize_text_field( "NO" );

		$cod_required = 0;
		if ( $insurance_amount > 0 ) {
			$is_insured = sanitize_text_field( "YES" );
		}

		$cod_amount = (int) $_POST['cod_amount'];
		if ( $cod_amount > 0 ) {
			$cod_required = 1;
		}

		$itme_description = sanitize_text_field( $_POST['itme_description'] );
		$length           = array_map( 'sanitize_text_field', $_POST['length'] );
		$width            = array_map( 'sanitize_text_field', $_POST['width'] );
		$height           = array_map( 'sanitize_text_field', $_POST['height'] );
		$weight           = array_map( 'sanitize_text_field', $_POST['weight'] );

		$parcels = array();
		for ( $i = 0, $iMax = count( $length ); $i < $iMax; $i ++ ) {
			$parcels[ $i ]['quantity']     = 1;
			$parcels[ $i ]['weight']       = $weight[ $i ];
			$parcels[ $i ]['length']       = $length[ $i ];
			$parcels[ $i ]['width']        = $width[ $i ];
			$parcels[ $i ]['height']       = $height[ $i ];
			$parcels[ $i ]['name']         = "order" . $i;
			$parcels[ $i ]['package_code'] = "CP";
			$parcels[ $i ]['is_document']  = "Y";
			$parcels[ $i ]['content']      = "test";
		}
		$cur_date = date( 'Y-m-d h:i' );

		$post_params = array(
			'delivery'         =>
				array(
					0 =>
						array(
							'address' =>
								array(
									'country'      => $delivery_country,
									'county'       => $delivery_county,
									'postcode'     => $delivery_postcode,
									'city'         => $delivery_city,
									'cod_required' => $cod_required,
									'cod_amount'   => $cod_amount,
								),
						),
				),
			'collection'       =>
				array(
					'address' =>
						array(
							'country'  => $collection_country,
							'county'   => $collection_county,
							'postcode' => $collection_postcode,
							'city'     => $collection_city,
						),
				),
			'service_date'     => $cur_date,
			'is_insured'       => $is_insured,
			'insurance_amount' => $insurance_amount,
			'collected_by'     => 'NONSELF',
			'parcel'           => $parcels,
			'carrier_code'     => $chosenCarrier
		);

		$response = $this->insta_core->instaApiHandler( INSTA_QUOTATION_API, $post_params );
		$result   = json_decode( json_encode( $response ), false );

		if ( $result->status == "fail" ) {
			wp_die( $result->message );
		}
		if ( $result->status == "success" ) {
			ob_start();
			$services    = $result->rate->services;
			$result_html = '<div class="panel-design-main-box-data">
        <div id="quote_response" style="display:none;">
        <input type="text" id="quotation_ref_' . $order_id . '" value="' . $result->rate->quotation_ref . '">
        <input type="text" id="act_number" value="' . $result->rate->services[0]->act_number . '">
        <input type="hidden" name="preferred_service_name_' . $order_id . '" value="' . $service_code . '">
        </div>
        <div class="main-panel">
            <div class="main-padding">
                <ul class="listing-data">';
			$result_html .= '<li> <span>Recipient:</span> </li>
                    <li> ' . $delivery_county . ',' . $delivery_city . ', ' . $delivery_country . ', ' . $delivery_postcode . '</li>
                    <li> <span>ORDER:</span> Order #' . $order_id . '</li>
                </ul>
            </div>
            <div class="devider"></div>
            <div class="table-responsive-group">
                <table cellpadding="10" cellspacing="0" width="100%" id="servicelist0">
                    <tbody>';
			foreach ( $services as $service ) {
				$price = "";
				if ( get_option( 'view_live_price' ) ) {
					$price = wc_price( $service->total );
				}
				$selected_service = ( $service->service_code === $service_code ) ? "checked" : "";
				$result_html      .= "<tr><td><input type='radio' $selected_service class='insta_service_order' data-order-id=" . $order_id . " carrier='" . $service->carrier . "' act_number='" . $service->act_number . "' name='insta_service_" . $order_id . "' style='margin: 0px 10px 0px 0px;' value='" . $service->service_code . "'>" . $service->service_name . $price . "</td></tr>";
			}
			$result_html .= '</tbody></table></div></div></div>';
			$result_html .= ob_get_clean();
		}

		update_post_meta( $order_id, "quote_result", $result );
		wp_die( $result_html );
	}

	/**
	 * Book Shipments
	 * @return void
	 */
	public function get_insta_dispatch_booking() {

		$result_html  = "";
		$carrier_code = sanitize_text_field( $_POST['chosenCarrier'] );

		$insurance_amount = sanitize_text_field( $_POST['insurance_amount'] );
		$order_id         = sanitize_text_field( $_POST["order_id"] );
		$is_insured       = sanitize_text_field( "NO" );
		$cod_required     = 0;
		if ( $insurance_amount > 0 ) {
			$is_insured = "YES";
		}
		$cod_required = 0;
		$cod_amount   = sanitize_text_field( $_POST['cod_amount'] );
		if ( $cod_amount > 0 ) {
			$cod_required = 1;
		}
		$quotation_ref = sanitize_text_field( $_POST['quotation_ref'] );
		$act_number    = sanitize_text_field( $_POST['act_number'] );
		$order         = wc_get_order( $order_id );

		$first_name = $order->get_billing_first_name();
		$last_name  = $order->get_billing_last_name();
		if ( $order->get_shipping_first_name() ) {
			$first_name = $order->get_shipping_first_name();
		}
		if ( $order->get_shipping_last_name() ) {
			$last_name = $order->get_shipping_last_name();
		}

		$name = $first_name . " " . $last_name;

		$phone = $order->get_billing_phone();

		$collection_country_arr = explode( ":", get_option( 'woocommerce_default_country' ) );
		$collection_country     = WC()->countries->countries[ $collection_country_arr[0] ];

		$specialword = "(";
		if ( strpos( $collection_country, $specialword ) !== false ) {
			$collection_country = substr( $collection_country, 0, strpos( $collection_country, "(" ) );
			$collection_country = trim( $collection_country );

		}

		$collection_city      = sanitize_text_field( get_option( 'woocommerce_store_city' ) );
		$collection_postcode  = sanitize_text_field( get_option( 'woocommerce_store_postcode' ) );
		$collection_county    = sanitize_text_field( $collection_country_arr[1] );
		$collection_address   = sanitize_text_field( get_option( 'woocommerce_store_address' ) );
		$collection_address_2 = sanitize_text_field( get_option( 'woocommerce_store_address_2' ) );


		$delivery_country = WC()->countries->countries[ $order->get_billing_country() ];
		if ( $order->get_shipping_country() ) {
			$delivery_country = WC()->countries->countries[ $order->get_shipping_country() ];
		}


		$specialword = "(";

		if ( strpos( $delivery_country, $specialword ) !== false ) {
			$delivery_country = substr( $delivery_country, 0, strpos( $delivery_country, "(" ) );
			$delivery_country = trim( $delivery_country );
		}


		$country_of_origin = $order->get_billing_country();

		if ( strpos( $country_of_origin, $specialword ) !== false ) {
			$country_of_origin = substr( $country_of_origin, 0, strpos( $country_of_origin, "(" ) );
			$country_of_origin = trim( $country_of_origin );
		}

		$delivery_citybilling = $order->get_billing_city();

		if ( $order->get_shipping_city() ) {
			$delivery_city = sanitize_text_field( $order->get_shipping_city() );
		}

		$delivery_postcode = $order->get_billing_postcode();
		if ( $order->get_shipping_postcode() ) {
			$delivery_postcode = sanitize_text_field( $order->get_shipping_postcode() );
		}

		$delivery_county = $order->get_billing_state();
		if ( $order->get_shipping_state() ) {
			$delivery_county = sanitize_text_field( $order->get_shipping_state() );
		}

		$billing_email = sanitize_email( get_post_meta( $order_id, '_billing_email', true ) );
		if ( get_post_meta( $order_id, '_shipping_email', true ) ) {
			$billing_email = sanitize_email( get_post_meta( $order_id, '_shipping_email', true ) );
		}

		$billing_address1 = sanitize_text_field( get_post_meta( $order_id, '_billing_address_1', true ) );
		if ( get_post_meta( $order_id, '_shipping_address_1', true ) ) {
			$billing_address1 = sanitize_text_field( get_post_meta( $order_id, '_shipping_address_1', true ) );
		}

		$billing_address2 = sanitize_text_field( get_post_meta( $order_id, '_billing_address_2', true ) );
		if ( get_post_meta( $order_id, '_shipping_address_2', true ) ) {
			$billing_address2 = sanitize_text_field( get_post_meta( $order_id, '_shipping_address_2', true ) );
		}

		$billing_company = sanitize_text_field( get_post_meta( $order_id, '_billing_company', true ) );
		if ( get_post_meta( $order_id, '_shipping_company', true ) ) {
			$billing_company = sanitize_text_field( get_post_meta( $order_id, '_shipping_company', true ) );
		}


		$order_notes = $order->get_customer_note();

		$item_description = esc_textarea( $_POST['item_description'] );
		$item_quantity    = sanitize_text_field( $_POST['item_quantity'] );
		$item_value       = sanitize_text_field( $_POST['item_value'] );
		$items            = array();

		$length  = array_map( 'sanitize_text_field', $_POST['length'] );
		$width   = array_map( 'sanitize_text_field', $_POST['width'] );
		$height  = array_map( 'sanitize_text_field', $_POST['height'] );
		$weight  = array_map( 'sanitize_text_field', $_POST['weight'] );
		$parcels = array();
		for ( $i = 0, $iMax = count( $length ); $i < $iMax; $i ++ ) {
			$parcels[ $i ]['quantity']     = 1;
			$parcels[ $i ]['weight']       = $weight[ $i ];
			$parcels[ $i ]['length']       = $length[ $i ];
			$parcels[ $i ]['width']        = $width[ $i ];
			$parcels[ $i ]['height']       = $height[ $i ];
			$parcels[ $i ]['name']         = "order" . $i;
			$parcels[ $i ]['package_code'] = sanitize_text_field( "CP" );
			$parcels[ $i ]['is_document']  = sanitize_text_field( "Y" );
			$parcels[ $i ]['content']      = sanitize_text_field( "test" );

			$items[ $i ]['item_description']    = $item_description;
			$items[ $i ]['item_quantity']       = $item_quantity;
			$items[ $i ]['item_value']          = $item_value;
			$items[ $i ]['item_weight']         = $weight[ $i ];
			$items[ $i ]['itam_value_currency'] = 2;
			$items[ $i ]['country_of_origin']   = esc_attr( $country_of_origin );
		}

		$blogname   = sanitize_text_field( get_option( 'blogname' ) );
		$adminemail = sanitize_email( get_option( 'admin_email' ) );


		$post_param = array(
			'delivery'            =>
				array(
					0 =>
						array(
							'address'   =>
								array(
									'country'          => $delivery_country,
									'county'           => $delivery_county,
									'postcode'         => $delivery_postcode,
									'city'             => $delivery_city,
									'company_name'     => $billing_company,
									'address_line1'    => $billing_address1,
									'address_line2'    => $billing_address2,
									'notes'            => $order_notes,
									'is_insured'       => $is_insured,
									'insurance_amount' => $insurance_amount,
									'cod_required'     => $cod_required,
									'cod_amount'       => $cod_amount
								),
							'consignee' =>
								array(
									'name'                  => $name,
									'phone'                 => $phone,
									'email'                 => $billing_email,
									'delivery_instruction'  => $order_notes,
									'delivery_notification' => 'true',
									'address_type'          => 'Residentail',
								),
						),
				),
			'collection'          =>
				array(
					'address'   =>
						array(
							'country'       => $collection_country,
							'county'        => $collection_county,
							'postcode'      => $collection_postcode,
							'city'          => $collection_city,
							'company_name'  => $blogname,
							'address_line1' => $collection_address_2,
							'address_line2' => $collection_address_2,
							'notes'         => $order_notes,
						),
					'consignee' =>
						array(
							'name'                => $blogname,
							'phone'               => "",
							'email'               => $adminemail,
							'address_type'        => 'Residential',
							'pickup_instruction'  => 'NO INSTRUCTION',
							'pickup_time_start'   => '15:00:00',
							'pickup_time_end'     => '17:00:00',
							'service_date'        => '2020-07-10',
							'pickup_location'     => 'FRONT DESK NN',
							'pickup_notification' => 'false',
						),
				),
			'carrier_code'        => $carrier_code,
			'act_number'          => $act_number,
			'service_code'        => sanitize_text_field( $_POST['selected_code'] ),
			'quation_reference'   => $quotation_ref,
			'customer_reference1' => $order_id,
			'customer_reference2' => $item_description,
			'terms_of_trade'      => 'DAP',
			'export_type'         => 'Permanent',
			'tax_status'          => 'Private Individual',
			'reason_for_export'   => 'Gift',
			'is_document'         => 'false',
			'is_insured'          => $is_insured,
			'insurance_amount'    => $insurance_amount,
			'items'               => $items,
			'callback_url'        => 'https://api-sandbox.noqu.delivery/callback/mydelivery',
		);

		$response = $this->insta_core->instaApiHandler( INSTA_BOOK_QUOTATION_API, $post_param );

		$result = json_decode( json_encode( $response ), false );
		if ( $result->status != "success" ) {
			wp_die( $result->message );
		}
		if ( $result->status == "success" ) {
			$success_message = $result->message;
			$file_path       = esc_url( $result->file_path );
			$result_html     = '<div class="panel-design-main-box-data">';
			$result_html     .= "<p>" . esc_html( $success_message ) . "</p>";
			if ( $file_path ) {
				$result_html .= "<p><a href='" . $file_path . "' target='_blank'>Download PDF</a></p>";
			}
			$result_html .= '</div>';
			$order       = new WC_Order( $order_id );
			if ( esc_attr( get_option( 'auto_complete_order' ) ) ) {
				$order->update_status( 'completed' );
			}
			update_post_meta( $order_id, 'insta_shipment_identity', esc_attr( $result->identity ) );
			update_post_meta( $order_id, "insta_shipment_pdf", $file_path );
			update_post_meta( $order_id, "insta_shipment_result", $result );
			update_post_meta( $order_id, 'insta_selected_service', sanitize_text_field( $_POST['selected_code'] ) );
			echo wp_kses_post( $result_html );
			wp_die();
		}

		wp_die( $response );
	}

	public function set_insta_shipping() {
		if ( ! session_id() ) {
			session_start();
		}
		$_SESSION['insta_shipping'] = sanitize_text_field( $_POST['insta_shipping'] );
		$_SESSION['service_name']   = sanitize_text_field( $_POST['service_name'] );
		$_SESSION['total_price']    = sanitize_text_field( $_POST['total_price'] );
		$_SESSION['service_code']   = sanitize_text_field( $_POST['service_code'] );
		wp_die();
	}

	public function insta_shipping_carrier( $order_id ) {
		if ( ! session_id() ) {
			session_start();
		}
		if ( isset( $_SESSION['insta_shipping'] ) ) {
			update_post_meta( $order_id, 'insta_shipping', sanitize_text_field( wp_unslash( $_SESSION['insta_shipping'] ) ) );
			update_post_meta( $order_id, 'insta_service_name', sanitize_text_field( wp_unslash( $_SESSION['service_name'] ) ) );
			update_post_meta( $order_id, 'insta_total_price', sanitize_text_field( wp_unslash( $_SESSION['total_price'] ) ) );
			update_post_meta( $order_id, 'insta_service_code', sanitize_text_field( wp_unslash( $_SESSION['service_code'] ) ) );

			unset( $_SESSION['insta_shipping'], $_SESSION['service_name'], $_SESSION['total_price'], $_SESSION['total_price'] );
		}
	}

	public function set_insta_loader() {
		echo wp_kses_post( '<div class="recalculate">Recalculate Shipping...</div><br>' );
	}

	public function insta_order_review() {
		if ( ! get_option( 'view_live_price' ) ) {
			return;
		}

		echo $this->insta_core->get_live_price();
	}

	public function recalculate_rate() {
		$html = $this->insta_core->get_live_price();

		wp_die( wp_json_encode( $html ) );
	}

	public function cancel_shipment() {

		$identity   = sanitize_text_field( $_POST['identity'] );
		$order_id   = sanitize_text_field( $_POST['order_id'] );
		$post_param = array( 'identity' => $identity, );
		$response   = $this->insta_core->instaApiHandler( INSTA_CANCEL_SHIPMENT, $post_param );
		if ( $response["status"] != "success" ) {
			echo esc_html( $response["message"] ?? "" );
		}
		if ( $response["status"] == "success" ) {
			$update_status = sanitize_text_field( "processing" );
			$order         = new WC_Order( $order_id );
			$order->update_status( $update_status );
			update_post_meta( $order_id, 'insta_shipment_identity', '' );
			update_post_meta( $order_id, 'insta_selected_service', '' );
			update_post_meta( $order_id, 'insta_shipment_pdf', '' );
			echo esc_html( $response["message"] ?? "" );
		}
		wp_die();
	}
}
