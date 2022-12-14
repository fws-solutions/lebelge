<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class Klaviyo extends CompatibilityAbstract {
	function is_available(): bool {
		return class_exists( '\\WooCommerceKlaviyo' ) && version_compare( \WooCommerceKlaviyo::$version, '2.4.1', '>=' ) && function_exists( 'kl_checkbox_custom_checkout_field' );
	}

	function run() {
		remove_filter( 'woocommerce_checkout_fields', 'kl_sms_consent_checkout_field', 11 );
		remove_filter( 'woocommerce_checkout_fields', 'kl_checkbox_custom_checkout_field', 11 );

		$hook = apply_filters( 'cfw_klaviyo_output_hook', 'woocommerce_after_checkout_billing_form' );
		add_action( $hook, array( $this, 'output_checkbox' ), 11 );
	}

	function output_checkbox() {
		$settings = get_option( 'klaviyo_settings' );

		echo '<div style="margin-top: 0.8em;">';

		if ( ! empty( $settings['klaviyo_newsletter_list_id'] ) ) {
			$newsletter_field = kl_checkbox_custom_checkout_field( array() );

			cfw_form_field( 'kl_newsletter_checkbox', $newsletter_field['billing']['kl_newsletter_checkbox'] );
		}

		if ( isset( $settings['klaviyo_sms_subscribe_checkbox'] ) && $settings['klaviyo_sms_subscribe_checkbox'] && ! empty( $settings['klaviyo_sms_list_id'] ) ) {
			$sms_field = kl_sms_consent_checkout_field( array() );

			cfw_form_field( 'kl_newsletter_checkbox', $sms_field['billing']['kl_sms_consent_checkbox'] );
		}

		echo '</div>';
	}
}
