<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class WC_Advanced_Shipment_Tracking_Api_Call {
	
	public function __construct() {
		
	}
	
	/*
	* check if string is json or not
	*/
	public function isJson( $string ) {
		json_decode($string);
		return ( json_last_error() == JSON_ERROR_NONE );
	}
	
	/*
	* get trackship shipment status and update in order meta
	*/
	public function get_trackship_apicall( $order_id ) {
		
		$logger = wc_get_logger();
		$context = array( 'source' => 'wc_ast_trackship' );
		$array = array();
		$order = wc_get_order( $order_id );
		
		$tracking_items = ast_get_tracking_items( $order_id );
		$shipment_status = get_post_meta( $order_id, 'shipment_status', true);

		if ( $tracking_items ) {
			foreach ( ( array ) $tracking_items as $key => $val ) {
				
				if ( isset( $shipment_status[$key]['status'] ) && 'delivered' == $shipment_status[$key]['status'] ) {
					continue;
				}	
				
				$tracking_number = trim($val['tracking_number']);
				
				$tracking_provider = isset( $val['tracking_provider'] ) ? $val['tracking_provider'] : $val['custom_tracking_provider'];
				$tracking_provider = apply_filters( 'convert_provider_name_to_slug', $tracking_provider );
				
				if ( isset( $tracking_number ) ) {
					
					//do api call
					$response = $this->get_trackship_data( $order, $tracking_number, $tracking_provider );
										
					if ( is_wp_error( $response ) ) {
						$error_message = $response->get_error_message();
						
						$logger = wc_get_logger();
						$context = array( 'source' => 'Trackship_apicall_is_wp_error' );
						$logger->error( "Something went wrong: {$error_message} For Order id :" . $order->get_id(), $context );
						
						//error like 403 500 502 
						$timestamp = time() + 5*60;
						$args = array( $order->get_id() );
						$hook = 'wcast_retry_trackship_apicall';
						wp_schedule_single_event( $timestamp, $hook, $args );
						
						$shipment_status = get_post_meta( $order->get_id(), 'shipment_status', true);
						$ts_shipment_status = get_post_meta( $order->get_id(), 'ts_shipment_status', true);
						
						if ( is_string( $shipment_status ) ) {
							$shipment_status = array();
						}	
						
						if ( is_string( $ts_shipment_status ) ) {
							$ts_shipment_status = array();
						}	
						
						$shipment_status[$key]['status'] = "Something went wrong: {$error_message}";
						$shipment_status[$key]['status_date'] = gmdate( 'Y-m-d H:i:s' );
						
						$ts_shipment_status[$key]['status'] = $shipment_status[$key]['status'];			
			
						update_post_meta( $order_id, 'ts_shipment_status', $ts_shipment_status);
						update_post_meta( $order->get_id(), 'shipment_status', $shipment_status);
						
					} else {
						
						$code = $response['response']['code'];

						if ( 200 == $code ) {
							
							//update trackers_balance, status_msg
							if ( !$this->isJson( $response['body'] ) ) {
								return;
							}
							
							$body = json_decode( $response['body'], true );
							
							$shipment_status = get_post_meta( $order->get_id(), 'shipment_status', true);
							$ts_shipment_status = get_post_meta( $order->get_id(), 'ts_shipment_status', true);
							
							if ( is_string( $shipment_status ) ) {
								$shipment_status = array();
							}	
							
							if ( is_string( $ts_shipment_status ) ) {
								$ts_shipment_status = array();
							}	
							
							$shipment_status[$key]['pending_status'] = $body['status_msg'];
														
							$shipment_status[$key]['status_date'] = gmdate( 'Y-m-d H:i:s' );
							$shipment_status[$key]['est_delivery_date'] = '';														
							
							$ts_shipment_status[$key]['status'] = $shipment_status[$key]['pending_status'];			
			
							update_post_meta( $order_id, 'ts_shipment_status', $ts_shipment_status);
							update_post_meta( $order->get_id(), 'shipment_status', $shipment_status);
							
							if ( isset( $body['trackers_balance'] ) ) {
								update_option('trackers_balance', $body['trackers_balance'] );
							}														
						} else {
							//error like 400
							$body = json_decode($response['body'], true);															
							
							$shipment_status = get_post_meta( $order->get_id(), 'shipment_status', true);
							$ts_shipment_status = get_post_meta( $order->get_id(), 'ts_shipment_status', true);
							
							if ( is_string( $shipment_status ) ) {
								$shipment_status = array();
							}
							
							if ( is_string( $ts_shipment_status ) ) {
								$ts_shipment_status = array();
							}	
							
							$shipment_status[$key]['status'] = 'Error message : ' . $body['message'];
							$shipment_status[$key]['status_date'] = gmdate( 'Y-m-d H:i:s' );
							$shipment_status[$key]['est_delivery_date'] = '';
							
							$ts_shipment_status[$key]['status'] = $shipment_status[$key]['status'];			
			
							update_post_meta( $order_id, 'ts_shipment_status', $ts_shipment_status);
							update_post_meta( $order->get_id(), 'shipment_status', $shipment_status);
							
							$logger = wc_get_logger();
							$context = array( 'source' => 'Trackship_apicall_error' );
							$logger->error( 'Error code : ' . $code . ' For Order id :' . $order->get_id(), $context );
							$logger->error( 'Body : ' . $response['body'], $context );
						}						
					}					
				}
			}
		}
		return $array;
	}
	
	/*
	* get trackship shipment data
	*/
	public function get_trackship_data( $order, $tracking_number, $tracking_provider ) {
		
		$user_key = get_option( 'wc_ast_api_key' );
		
		$domain = get_home_url();
		if ( class_exists( 'SitePress' ) ) {
			global $sitepress;
			$language_negotiation_type	= $sitepress->get_setting( 'language_negotiation_type' );
			$urlSettings				= $sitepress->get_setting( 'urls' );
			$dir_for_default_language	= isset( $urlSettings['directory_for_default_language'] ) && $urlSettings['directory_for_default_language'];
			
			if ( 1 != $language_negotiation_type || !$dir_for_default_language ) {
				$domain = get_site_url();
			}
		}
		$domain = apply_filters( 'ts_api_domain', $domain );
		
		$order_id = $order->get_id();
		$order_number = $order->get_order_number();
		
		if ( null != $order->get_shipping_country() ) {
			$shipping_country = $order->get_shipping_country();	
		} else {
			$shipping_country = $order->get_billing_country();	
		}
		
		if ( null != $order->get_shipping_postcode() ) {
			$shipping_postal_code = $order->get_shipping_postcode();	
		} else {
			$shipping_postal_code = $order->get_billing_postcode();
		}
		
		$url = 'https://trackship.info/wp-json/tracking/create';
		
		$args['body'] = array(
			'user_key' => $user_key,
			'order_id' => $order_id,
			'custom_order_id' => $order_number,
			'domain' => $domain,
			'tracking_number' => $tracking_number,
			'tracking_provider' => $tracking_provider,
			'postal_code' => $shipping_postal_code,
			'destination_country' => $shipping_country,
		);

		$args['headers'] = array(
			'user_key' => $user_key
		);	
		$args['timeout'] = 10;
		$response = wp_remote_post( $url, $args );
		return $response;
	}
	
	/*
	* delete tracking number from trackship
	*/
	public function delete_tracking_number_from_trackship( $order_id, $tracking_number, $tracking_provider ) {
		
		$user_key = get_option( 'wc_ast_api_key' );
		$domain = get_site_url();		
		
		$url = 'https://trackship.info/wp-json/tracking/delete';
		
		$args['body'] = array(
			'user_key' => $user_key,
			'order_id' => $order_id,
			'domain' => $domain,
			'tracking_number' => $tracking_number,
			'tracking_provider' => $tracking_provider,
		);

		$args['headers'] = array(
			'user_key' => $user_key
		);	
		$args['timeout'] = 10;
		$response = wp_remote_post( $url, $args );		
		return $response;
	}
}
