<?php
defined( 'ABSPATH' ) || exit();

/**
 *
 * @author Payment Plugins
 * @since 3.1.0
 * @package Stripe/Trait
 */
trait WC_Stripe_Payment_Intent_Trait {

	public function get_payment_object() {
		return WC_Stripe_Payment_Factory::load( 'payment_intent', $this, WC_Stripe_Gateway::load() );
	}

	public function get_payment_method_type() {
		return $this->payment_method_type;
	}

	/**
	 *
	 * @param WC_Order $order
	 */
	public function get_confirmation_method( $order ) {
		return 'manual';
	}

	/**
	 *
	 * @param \Stripe\PaymentIntent $intent
	 * @param WC_Order $order
	 */
	public function get_payment_intent_checkout_url( $intent, $order, $type = 'intent' ) {
		global $wp;

		// rand is used to generate some random entropy so that window hash events are triggered.
		$args = array(
			'type'          => $type,
			'client_secret' => $intent->client_secret,
			'order_id'      => $order->get_id(),
			'order_key'     => $order->get_order_key(),
			'gateway_id'    => $this->id,
			'status'        => $intent->status,
			'pm'            => $intent->payment_method,
			'entropy'       => rand( 0, 999999 )
		);
		if ( ! empty( $wp->query_vars['order-pay'] ) ) {
			$args['save_method'] = ! empty( $_POST[ $this->save_source_key ] );
		}

		return sprintf( '#response=%s', rawurlencode( base64_encode( wp_json_encode( $args ) ) ) );
	}
}

/**
 *
 * @author Payment Plugins
 * @since 3.1.0
 * @package Stripe/Trait
 */
trait WC_Stripe_Payment_Charge_Trait {

	public function get_payment_object() {
		return WC_Stripe_Payment_Factory::load( 'charge', $this, WC_Stripe_Gateway::load() );
	}
}

/**
 *
 * @author Payment Plugins
 * @since 3.1.0
 * @package Stripe/Trait
 */
trait WC_Stripe_Local_Payment_Charge_Trait {

	public function get_payment_object() {
		return WC_Stripe_Payment_Factory::load( 'local_charge', $this, WC_Stripe_Gateway::load() );
	}
}

/**
 *
 * @author Payment Plugins
 * @since 3.1.0
 * @package Stripe/Trait
 *
 */
trait WC_Stripe_Local_Payment_Intent_Trait {

	use WC_Stripe_Payment_Intent_Trait;

	/**
	 *
	 * @param \Stripe\PaymentIntent $secret
	 * @param WC_Order $order
	 */
	public function get_payment_intent_checkout_url( $intent, $order, $type = 'intent' ) {
		// rand is used to generate some random entropy so that window hash events are triggered.
		return sprintf(
			'#response=%s',
			rawurlencode( base64_encode(
				wp_json_encode(
					array(
						'client_secret'      => $intent->client_secret,
						'gateway_id'         => $this->id,
						'return_url'         => $this->get_local_payment_return_url( $order ),
						'order_received_url' => $this->get_return_url( $order ),
						'entropy'            => rand(
							0,
							999999
						),
					)
				)
			) )
		);
	}

	/**
	 *
	 * @param WC_Order $order
	 */
	public function get_confirmation_method( $order ) {
		return 'automatic';
	}
}
