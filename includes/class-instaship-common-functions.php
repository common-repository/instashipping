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
 * Common Functions
 *
 * @since      1.0.0
 * @package    Instaship
 * @subpackage Instaship/includes
 * @author     Insta Dispatch
 */
class Instaship_common_functions {

	protected $insta_auth;

	public function __construct() {
		$this->insta_auth = get_option( "authorization_key" );
	}


	public function get_enabled_carriers() {
		$post_data          = array( 'isCarrier' => 'true', );
		$wp_request_headers = INSTA_ENABLED_CARRIERS;

		return $this->instaApiHandler( $wp_request_headers, $post_data );
	}

	public function get_live_price(): string {
		ob_start();
		$customer_data   = WC()->session->get( 'customer' );
		$rate_args       = $this->set_rate_request( $customer_data );
		$html            = "";
		$available_rates = $this->instaApiHandler( INSTA_QUOTATION_API, $rate_args );

		if ( ( isset( $available_rates["status"] ) && $available_rates["status"] != "success" ) || ( $available_rates === null ) ) {

			$html .= '<div id="insta_shipping" class="woocommerce-insta_shipping">';
			$html .= '<input type="hidden" name="insta_rate" value="rate_not_found">';
			$html .= '<h3>Insta Shipping</h3>';
			$html .= '<div class="woocommerce-error"><p>Sorry, we do not ship to this location</p></div>';
			$html .= '</div>';

		}

		if ( isset( $available_rates["status"] ) && $available_rates["status"] === "success" ) {

			$services = $available_rates["rate"]["services"];

			array_multisort( array_column( $services, "total" ), SORT_ASC, $services );
			$html .= '<div id="insta_shipping" class="woocommerce-insta_shipping">';
			$html .= '<h3>Insta Shipping</h3>';
			$html .= '<ul class="wc_insta_shipping_methods insta_shipping_methods" style="list-style-type:none;">';
			$html .= '<li><input type="hidden" name="insta_rate" value="rate_found"></li>';
			foreach ( $services as $service ) {

				$html .= '<li class="insta_shipping">';
				$html .= '<input type="radio" class="input-radio service-type" name="insta_shipping" data-service-code="' . $service["service_code"] . '" data-service-name="' . $service["service_name"] . '" data-price="' . $service["total"] . '" value="' . $service['carrier'] . '">
						&nbsp;&nbsp;' . $service["service_name"] . ' (' . $service["carrier"] . ') ' . wc_price( $service["total"] ) . ' ';
				$html .= '<p>' . $service["service_description"] . '</p>';
				$html .= '</li>';
			}
			$html .= '</ul>';
			$html .= '</div>';

		}
		echo $html;
		return ob_get_clean();
	}

	public function set_rate_request( $customer_data ): array {

		$cart_count         = WC()->cart->get_cart_contents_count();
		$billing_country    = WC()->countries->countries[ $customer_data["country"] ];
		$shipment_country   = WC()->countries->countries[ $customer_data["shipping_country"] ];
		$delivery_country   = substr( $shipment_country, 0, strpos( $shipment_country, "(" ) );
		$collection_country = substr( $billing_country, 0, strpos( $billing_country, "(" ) );

		return [
			"delivery"         => [
				[
					"address" => [
						"country"      => trim( $delivery_country ),
						"county"       => null,
						"postcode"     => $customer_data["shipping_postcode"],
						"city"         => $customer_data["shipping_city"],
						"cod_required" => false,
						"cod_amount"   => "00.00"
					]
				]
			],
			"collection"       => [
				"address" => [
					"country"  => trim( $collection_country ),
					"county"   => null,
					"postcode" => $customer_data["postcode"],
					"city"     => $customer_data["city"]
				]
			],
			"service_date"     => date( 'Y-m-d h:i' ),
			"is_insured"       => "NO",
			"insurance_amount" => "0",
			"collected_by"     => "NONSELF",
			"parcel"           => [
				[
					"quantity"     => $cart_count,
					"weight"       => get_option( "default_parcel_weight" ),
					"length"       => get_option( "default_parcel_length" ),
					"width"        => get_option( "default_parcel_width" ),
					"height"       => get_option( "default_parcel_height" ),
					"name"         => "order0",
					"package_code" => "CP",
					"is_document"  => "N",
					"content"      => "Custom Content"
				]
			]
		];
	}

	public function instaApiHandler( $apiUrl, $post_data ) {

		$timeout = 120;
		if ( ! ini_get( 'safe_mode' ) ) {
			set_time_limit( $timeout + 10 );
		}
		$wp_request_headers = [
			'Content-Type'  => 'application/json; charset=utf-8',
			'Authorization' => $this->insta_auth,
		];
		$response           = wp_remote_post( $apiUrl, array(
			'sslverify'   => false,
			'timeout'     => $timeout,
			'redirection' => 5,
			'httpversion' => '1.0',
			'headers'     => $wp_request_headers,
			'body'        => json_encode( $post_data ),
			'method'      => 'POST',
			'data_format' => 'body',
		) );

		if ( is_wp_error( $response ) ) {
			return [ "status" => "false", "errorMessage" => $response->get_error_message() ];
		}

		return json_decode( wp_remote_retrieve_body( $response ), true );
	}
}