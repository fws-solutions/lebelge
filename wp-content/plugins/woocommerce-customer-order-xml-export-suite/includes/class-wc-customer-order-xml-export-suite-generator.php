<?php
/**
 * WooCommerce Customer/Order XML Export Suite
 *
 * This source file is subject to the GNU General Public License v3.0
 * that is bundled with this package in the file license.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@skyverge.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade WooCommerce Customer/Order XML Export Suite to newer
 * versions in the future. If you wish to customize WooCommerce Customer/Order XML Export Suite for your
 * needs please refer to http://docs.woocommerce.com/document/woocommerce-customer-order-xml-export-suite/
 *
 * @author      SkyVerge
 * @copyright   Copyright (c) 2013-2019, SkyVerge, Inc.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

/**
 * Customer/Order XML Export Suite Generator
 *
 * Converts customer/order data into XML
 *
 * In 2.0.0 renamed from \WC_Customer_Order_XML_Export_Suite_Writer to
 * \WC_Customer_Order_XML_Export_Suite_Generator, does not extend XMLWriter
 * anymore, but creates an instance of it as a member.
 *
 * @since 1.0.0
 */
class WC_Customer_Order_XML_Export_Suite_Generator {


	/** @var string export type */
	public $export_type;

	/** @var string export format */
	public $export_format;

	/** @var array format definition */
	public $format_definition;

	/** @var bool whether to indent XML or not */
	public $indent;

	/** @var string XML version */
	public $xml_version;

	/** @var string XML encoding */
	public $xml_encoding;

	/** @var string XML standalone */
	public $xml_standalone;

	/** @var string XML root element */
	public $root_element;

	/** @var \XMLWriter instance */
	private $writer;


	/**
	 * Initialize the generator
	 *
	 * In 2.0.0 replaced $ids param with $export_type param
	 *
	 * @since 1.0.0
	 * @param string $export_type export type, one of `orders` or `customers`
	 */
	public function __construct( $export_type ) {

		$this->export_type = $export_type;

		$export_format = get_option( 'wc_customer_order_xml_export_suite_' . $export_type . '_format', 'default' );

		/**
		 * Allow actors to change the export format for the given export type
		 *
		 * @since 2.0.0
		 * @param string $format
		 * @param \WC_Customer_Order_XML_Export_Suite_Generator $this, generator instance
		 */
		$this->export_format = apply_filters( 'wc_customer_order_xml_export_suite_format', $export_format, $this );

		// get format definition
		$this->format_definition = wc_customer_order_xml_export_suite()->get_formats_instance()->get_format( $export_type, $this->export_format );

		/**
		 * Toggle XML file indentation on/off
		 *
		 * @since 1.3.0
		 * @param bool $indent
		 */
		$this->indent = apply_filters( 'wc_customer_order_xml_export_suite_xml_indent', $this->format_definition['indent'] );

		/**
		 * Set the XML version declaration
		 *
		 * @since 1.0.0
		 * @param string $xml_version
		 */
		$this->xml_version = apply_filters( 'wc_customer_order_xml_export_suite_xml_version', $this->format_definition['xml_version'] );

		/**
		 * Set the XML encoding declaration
		 *
		 * @since 1.0.0
		 * @param string $xml_encoding
		 */
		$this->xml_encoding = apply_filters( 'wc_customer_order_xml_export_suite_xml_encoding', $this->format_definition['xml_encoding'] );

		/**
		 * Set the XML standalone declaration
		 *
		 * @since 1.0.0
		 * @param string $xml_standalone
		 */
		$this->xml_standalone = apply_filters( 'wc_customer_order_xml_export_suite_xml_standalone', $this->format_definition['xml_standalone'] );

		/**
		 * Set the XML root element
		 *
		 * @since 2.0.0
		 * @param string $xml_standalone
		 */
		$this->root_element = apply_filters( 'wc_customer_order_xml_export_suite_xml_root_element', ucfirst( $export_type ) );

		$this->writer = new XMLWriter();
	}


	/**
	 * Deprecated get_orders_xml()
	 *
	 * @since 1.0.0
	 * @param array $order_ids
	 * @deprecated 1.1.0
	 * @return string generated XML
	 */
	public function get_order_export_xml( $order_ids ) {

		_deprecated_function( __METHOD__, '1.1.0', 'WC_Customer_Order_XML_Export_Suite_Generator::get_orders_xml' );
		return $this->get_orders_xml( $order_ids );
	}


	/**
	 * Build XML for orders in the default format
	 *
	 * In 2.0.0 added $ids param
	 *
	 * @since 1.0.0
	 * @param array $ids Order ID(s) to export
	 * @return string generated XML
	 */
	public function get_orders_xml( $ids ) {

		$orders = $this->get_orders( $ids );

		/**
		 * Allow actors to change the XML array format for orders
		 *
		 * In 2.0.0 renamed from `wc_customer_order_xml_export_suite_order_export_format`
		 * to `wc_customer_order_xml_export_suite_orders_xml_data`, removed root element
		 * from XML data array. Use the new `wc_customer_order_xml_export_suite_xml_root_element`
		 * filter instead.
		 *
		 * @since 1.0.0
		 * @param array XML data array
		 * @param array $orders
		 */
		$xml_array = apply_filters( 'wc_customer_order_xml_export_suite_orders_xml_data', array( 'Order' => $orders ), $orders );

		/**
		 * Filter the generated orders XML
		 *
		 * @since 2.0.0
		 * @param string $xml XML string
		 * @param array $xml_array XML data as array
		 * @param array $orders An array of the order data to write to to the XML
		 * @param array $order_ids The order ids.
		 * @param string $export_format The customer export format.
		 */
		return apply_filters( 'wc_customer_order_xml_export_suite_orders_xml', $this->get_xml( $xml_array ), $xml_array, $orders, $ids, $this->export_format );
	}


	/**
	 * Creates array of given orders in standard format
	 *
	 * Filter in get_order_export_xml() allow modification of parent array
	 * Filter in method allows modification of individual order array format
	 *
	 * @since 1.0.0
	 * @param array $order_ids order IDs to generate array from
	 * @return array orders in array format required by array_to_xml()
	 */
	private function get_orders( $order_ids ) {

		$orders = array();

		// loop through each order
		foreach ( $order_ids as $order_id ) {

			// instantiate WC_Order object
			$order = wc_get_order( $order_id );

			// skip invalid orders
			if ( ! $order instanceof WC_Order ) {
				continue;
			}

			list( $shipping_items, $shipping_methods, $shipping_methods_ids ) = $this->get_shipping_items( $order );
			list( $fee_items, $fee_total, $fee_tax_total )                    = $this->get_fee_items( $order );

			$download_permissions_granted = SV_WC_Order_Compatibility::get_meta( $order, '_download_permissions_granted' );

			if ( SV_WC_Plugin_Compatibility::is_wc_version_gte_3_0() ) {
				$completed_date = is_callable( array( $order->get_date_completed(), 'date' ) ) ? $order->get_date_completed()->date( 'Y-m-d H:i:s' ) : null;
			} else {
				$completed_date = $order->completed_date;
			}

			$data = array(
				'OrderId'                    => SV_WC_Order_Compatibility::get_prop( $order, 'id' ),
				'OrderNumber'                => SV_WC_Order_Compatibility::get_meta( $order, '_order_number', true ),
				'OrderNumberFormatted'       => SV_WC_Order_Compatibility::get_meta( $order, '_order_number_formatted', true ),
				'OrderDate'                  => $this->format_date( SV_WC_Order_Compatibility::get_date_created( $order )->date( 'Y-m-d H:i:s' ) ),
				'OrderStatus'                => $order->get_status(),
				'OrderCurrency'              => SV_WC_Order_Compatibility::get_prop( $order, 'currency', 'view' ),
				'BillingFirstName'           => SV_WC_Order_Compatibility::get_prop( $order, 'billing_first_name' ),
				'BillingLastName'            => SV_WC_Order_Compatibility::get_prop( $order, 'billing_last_name' ),
				'BillingFullName'            => $order->get_formatted_billing_full_name(),
				'BillingCompany'             => SV_WC_Order_Compatibility::get_prop( $order, 'billing_company' ),
				'BillingAddress1'            => SV_WC_Order_Compatibility::get_prop( $order, 'billing_address_1' ),
				'BillingAddress2'            => SV_WC_Order_Compatibility::get_prop( $order, 'billing_address_2' ),
				'BillingCity'                => SV_WC_Order_Compatibility::get_prop( $order, 'billing_city' ),
				'BillingState'               => $this->get_localized_state_for_order( $order, 'billing' ),
				'BillingStateCode'           => SV_WC_Order_Compatibility::get_prop( $order, 'billing_state' ),
				'BillingPostcode'            => SV_WC_Order_Compatibility::get_prop( $order, 'billing_postcode' ),
				'BillingCountry'             => SV_WC_Order_Compatibility::get_prop( $order, 'billing_country' ),
				'BillingPhone'               => SV_WC_Order_Compatibility::get_prop( $order, 'billing_phone' ),
				'BillingEmail'               => SV_WC_Order_Compatibility::get_prop( $order, 'billing_email' ),
				'ShippingFirstName'          => SV_WC_Order_Compatibility::get_prop( $order, 'shipping_first_name' ),
				'ShippingLastName'           => SV_WC_Order_Compatibility::get_prop( $order, 'shipping_last_name' ),
				'ShippingFullName'           => $order->get_formatted_shipping_full_name(),
				'ShippingCompany'            => SV_WC_Order_Compatibility::get_prop( $order, 'shipping_company' ),
				'ShippingAddress1'           => SV_WC_Order_Compatibility::get_prop( $order, 'shipping_address_1' ),
				'ShippingAddress2'           => SV_WC_Order_Compatibility::get_prop( $order, 'shipping_address_2' ),
				'ShippingCity'               => SV_WC_Order_Compatibility::get_prop( $order, 'shipping_city' ),
				'ShippingState'              => $this->get_localized_state_for_order( $order, 'shipping' ),
				'ShippingStateCode'          => SV_WC_Order_Compatibility::get_prop( $order, 'shipping_state' ),
				'ShippingPostcode'           => SV_WC_Order_Compatibility::get_prop( $order, 'shipping_postcode' ),
				'ShippingCountry'            => SV_WC_Order_Compatibility::get_prop( $order, 'shipping_country' ),
				'ShippingMethodId'           => implode( ',', $shipping_methods_ids ),
				'ShippingMethod'             => implode( ', ', $shipping_methods ),
				'PaymentMethodId'            => SV_WC_Order_Compatibility::get_prop( $order, 'payment_method' ),
				'PaymentMethod'              => SV_WC_Order_Compatibility::get_prop( $order, 'payment_method_title' ),
				'DiscountTotal'              => $order->get_total_discount(),
				'ShippingTotal'              => $order->get_total_shipping(),
				'ShippingTaxTotal'           => wc_format_decimal( $order->get_shipping_tax(), 2 ),
				'OrderTotal'                 => $order->get_total(),
				'FeeTotal'                   => $fee_total,
				'FeeTaxTotal'                => wc_format_decimal( $fee_tax_total, 2 ),
				'TaxTotal'                   => wc_format_decimal( $order->get_total_tax(), 2 ),
				'RefundedTotal'              => $order->get_total_refunded(),
				'CompletedDate'              => $this->format_date( $completed_date ),
				'CustomerNote'               => SV_WC_Order_Compatibility::get_prop( $order, 'customer_note' ),
				'CustomerId'                 => $order->get_user_id(),
				'OrderLineItems'             => $this->get_line_items( $order ),
				'FeeItems'                   => $fee_items,
				'ShippingItems'              => $shipping_items,
				'CouponItems'                => $this->get_coupon_items( $order ),
				'TaxItems'                   => $this->get_tax_items( $order ),
				'Refunds'                    => $this->get_refunds( $order ),
				'OrderNotes'                 => $this->get_formatted_order_notes( $order ),
				'DownloadPermissionsGranted' => $download_permissions_granted ? 1 : 0,
			);

			// support custom order numbers beyond Sequential Order Numbers Free / Pro - but since we can't
			// distinguish between the underlying number and the formatted number, only set the formatted number
			if ( ! $data['OrderNumberFormatted'] ) {
				$data['OrderNumberFormatted'] = $order->get_order_number();
			}

			if ( ! empty( $this->format_definition ) && ! empty( $this->format_definition['fields'] ) ) {

				foreach ( $this->format_definition['fields'] as $key => $field ) {
					$order_data[ $field ] = isset( $data[ $key ] ) ? $data[ $key ] : '';
				}

				if ( 'custom' === $this->export_format ) {
					$order_data = $this->get_order_custom_data( $order_data, $order );
				}

			} else {
				$order_data = $data;
			}

			// Adjust output for legacy formats
			if ( 'legacy' === $this->export_format ) {

				// ensure order number output is unchanged
				if ( ! $data['OrderNumber'] ) {
					$order_data['OrderNumber'] = $order->get_order_number();
				}

				// OrderLineItems were not wrapped in OrderLineItem pre 2.0.0
				$order_data['OrderLineItems'] = $order_data['OrderLineItems']['OrderLineItem'];
			}

			/**
			 * Allow actors to modify order data for XML
			 *
			 * In 2.0.0 renamed from `wc_customer_order_xml_export_suite_order_export_order_list_format`
			 * to `wc_customer_order_xml_export_suite_order_data`
			 *
			 * @since 1.0.0
			 * @param array $order_data
			 * @param \WC_Order $order
			 */
			$orders[] = apply_filters( 'wc_customer_order_xml_export_suite_order_data', $order_data, $order );

		}

		return $orders;
	}


	/**
	 * Creates array of order line items in format required for xml_to_array()
	 *
	 * Filter in method allows modification of individual line items array format
	 *
	 * @since 1.0.0
	 * @param object $order
	 * @return array|null - line items in array format required by array_to_xml(), or null if no line items
	 */
	private function get_line_items( $order ) {

		$items = array();

		// loop through each item in order
		foreach ( $order->get_items( 'line_item' ) as $item_id => $item ) {

			$is_order_item_product = $item instanceof WC_Order_Item_Product;

			// get the product with compatibility
			$product = SV_WC_Plugin_Compatibility::is_wc_version_gte_3_1() && $is_order_item_product ? $item->get_product() : $order->get_product_from_item( $item );

			// instantiate line item meta with compatibility
			if ( SV_WC_Plugin_Compatibility::is_wc_version_gte_3_1() && $is_order_item_product ) {

				$meta_data    = $item->get_formatted_meta_data( '_', true );
				$display_meta = array();

				foreach ( $meta_data as $meta ) {
					$display_meta[] = "{$meta->display_key}: {$meta->display_value}";
				}

				$item_meta = implode( ', ', $display_meta );

			} else {

				$item_meta = new WC_Order_Item_Meta( $item );
				$item_meta = $item_meta->display( true, true );
			}

			// remove all HTML
			$item_meta = wp_strip_all_tags( $item_meta );

			// strip HTML in legacy format - note: in modern formats,
			// SV_WC_Helper::array_to_xml will automatically escape HTML and newlines by wrapping
			// the contents of the tag in CDATA when necessary
			if ( 'legacy' == $this->export_format ) {
				// remove control characters
				$item_meta = str_replace( array( "\r", "\n", "\t" ), '', $item_meta );
			}

			// remove any html entities
			$item_meta = preg_replace( '/\&(?:[a-z,A-Z,0-9]+|#\d+|#x[0-9a-f]+);/', '', $item_meta );

			$item_data = array();

			$item_data['Id']               = $item_id;
			$item_data['Name']             = html_entity_decode( $product ? $product->get_title() : $item['name'], ENT_NOQUOTES, 'UTF-8' );
			$item_data['ProductId']        = $product ? $product->get_id() : '';  // handling for permanently deleted product
			$item_data['SKU']              = $product ? $product->get_sku() : '';  // handling for permanently deleted product
			$item_data['Quantity']         = $item['qty'];
			$item_data['Price']            = wc_format_decimal( $order->get_item_total( $item ), 2 );
			$item_data['Subtotal']         = wc_format_decimal( $order->get_line_subtotal( $item ), 2 );
			$item_data['SubtotalTax']      = wc_format_decimal( $item['line_subtotal_tax'], 2 );
			$item_data['Total']            = wc_format_decimal( $order->get_line_total( $item ), 2 );
			$item_data['TotalTax']         = wc_format_decimal( $order->get_line_tax( $item ), 2 );
			$item_data['Refunded']         = wc_format_decimal( $order->get_total_refunded_for_item( $item ), 2 );
			$item_data['RefundedQuantity'] = $order->get_qty_refunded_for_item( $item_id );

			if ( 'yes' === get_option( 'woocommerce_calc_taxes' ) && 'yes' === get_option( 'woocommerce_prices_include_tax' ) ) {
				$item_data['PriceInclTax'] = wc_format_decimal( $order->get_item_total( $item, true ), 2 );
				$item_data['TotalInclTax'] = wc_format_decimal( $order->get_line_total( $item, true ), 2 );
			}

			$item_data['Meta'] = $item_meta;

			$item_data['Taxes'] = $this->get_tax_details( $item );


			// Keep order items backwards-compatible with legacy version
			if ( 'legacy' == $this->export_format ) {

				// rename fields to be compatible with pre 2.0.0
				$item_data['ItemName']         = $item_data['Name'];
				$item_data['LineTotal']        = $item_data['Total'];

				if ( 'yes' === get_option( 'woocommerce_calc_taxes' ) && 'yes' === get_option( 'woocommerce_prices_include_tax' ) ) {
					$item_data['LineTotalInclTax'] = $item_data['TotalInclTax'];
				}

				// remove data that wasn't present pre 2.0.0
				unset( $item_data['Id'], $item_data['Name'], $item_data['ProductId'], $item_data['Subtotal'], $item_data['SubtotalTax'], $item_data['Total'], $item_data['TotalTax'], $item_data['Refunded'], $item_data['RefundedQuantity'], $item_data['TotalInclTax'], $item_data['Taxes'] );
			}

			/**
			 * Allow actors to modify the line item data / format
			 *
			 * In 2.0.0 renamed from `wc_customer_order_xml_export_suite_order_export_line_item_format`
			 * to `wc_customer_order_xml_export_suite_order_line_item`
			 *
			 * @since 1.0.0
			 * @param array $item_data
			 * @param \WC_Order $order Related order
			 * @param array $item Order line item
			 */
			$items['OrderLineItem'][] = apply_filters( 'wc_customer_order_xml_export_suite_order_line_item', $item_data, $order, $item );
		}

		return ! empty( $items ) ? $items : null;
	}


	/**
	 * Creates array of order shipping items in format required for xml_to_array()
	 *
	 * Filter in method allows modification of individual shipping item array format
	 *
	 * @since 2.0.0
	 * @param WC_Order $order
	 * @return array - shipping items, methods and method ids. Values will be null if order has no shipping methods.
	 */
	private function get_shipping_items( $order ) {

		$shipping_items = $shipping_methods = $shipping_methods_ids = array();

		foreach ( $order->get_shipping_methods() as $shipping_item_id => $shipping ) {

			$shipping_methods[]     = $shipping['name'];
			$shipping_methods_ids[] = $shipping['method_id'];

			$shipping_item = array(
				'Id'         => $shipping_item_id,
				'MethodId'   => $shipping['method_id'],
				'MethodName' => $shipping['name'],
				'Total'      => wc_format_decimal( $shipping['cost'], 2 ),
				'Taxes'      => $this->get_tax_details( $shipping ),
			);

			/**
			 * XML Order Export Shipping Line Item.
			 *
			 * Filter the individual shipping line item entry
			 *
			 * @since 2.0.0
			 * @param array $shipping_item {
			 *     line item data in key => value format
			 * }
			 *
			 * @param array $shipping WC order shipping item data
			 * @param WC_Order $order the order
			 */
			$shipping_items['ShippingItem'][] = apply_filters( 'wc_customer_order_xml_export_suite_order_shipping_item', $shipping_item, $shipping, $order );
		}

		return array( ( ! empty( $shipping_items ) ? $shipping_items : null ), $shipping_methods, $shipping_methods_ids );
	}


	/**
	 * Creates array of order fee items in format required for xml_to_array()
	 *
	 * Filter in method allows modification of individual fee item array format
	 *
	 * @since 2.0.0
	 * @param object $order
	 * @return array - fee items, total and tax total. Values will be null or 0 if order has no fees.
	 */
	private function get_fee_items( $order ) {

		$fee_items = array();
		$fee_total = $fee_tax_total = 0;

		foreach ( $order->get_fees() as $fee_item_id => $fee ) {

			$fee_item = array(
				'Id'       => $fee_item_id,
				'Title'    => $fee['name'],
				'TaxClass' => ( ! empty( $fee['tax_class'] ) ) ? $fee['tax_class'] : null,
				'Total'    => wc_format_decimal( $order->get_line_total( $fee ), 2 ),
				'TaxTotal' => wc_format_decimal( $order->get_line_tax( $fee ), 2 ),
				'Taxes'    => $this->get_tax_details( $fee ),
			);

			$fee_item['Taxable'] = null !== $fee_item['TaxClass'];

			/**
			 * XML Export Fee Line Item.
			 *
			 * Filter the individual fee line item entry
			 *
			 * @since 2.0.0
			 * @param array $fee_item {
			 *     line item data in key => value format
			 * }
			 *
			 * @param array $fee WC order fee item data
			 * @param \WC_Order $order the order
			 */
			$fee_items['FeeItem'][] = apply_filters( 'wc_customer_order_xml_export_suite_order_fee_item', $fee_item, $fee, $order );

			$fee_total     += $fee['line_total'];
			$fee_tax_total += $fee['line_tax'];
		}

		return array( ( ! empty( $fee_items ) ? $fee_items : null ), $fee_total, $fee_tax_total );
	}


	/**
	 * Creates array of order tax items in format required for xml_to_array()
	 *
	 * Filter in method allows modification of individual tax item array format
	 *
	 * @since 2.0.0
	 * @param object $order
	 * @return array|null - tax items in array format required by array_to_xml(), or null if no taxes
	 */
	private function get_tax_items( $order ) {

		$tax_items = array();

		foreach ( $order->get_tax_totals() as $tax_code => $tax ) {

			$tax_item = array(
				'Id'       => $tax->id,
				'RateId'   => $tax->rate_id,
				'Code'     => $tax_code,
				'Title'    => $tax->label,
				'Total'    => wc_format_decimal( $tax->amount, 2 ),
				'Compound' => (bool) $tax->is_compound,
			);

			/**
			 * XML Order Export Tax Line Item.
			 *
			 * Filter the individual tax line item entry
			 *
			 * @since 2.0.0
			 * @param array $tax_item {
			 *     line item data in key => value format
			 * }
			 *
			 * @param object $tax WC order tax item
			 * @param WC_Order $order the order
			 */
			$tax_items['TaxItem'][] = apply_filters( 'wc_customer_order_xml_export_suite_order_tax_item', $tax_item, $tax, $order );
		}

		return ! empty( $tax_items ) ? $tax_items : null;
	}


	/**
	 * Creates array of order coupons in format required for xml_to_array()
	 *
	 * Filter in method allows modification of individual coupons array format
	 *
	 * @since 2.0.0
	 * @param \WC_Order $order
	 * @return array|null - coupons in array format required by array_to_xml(), or null if no coupons
	 */
	private function get_coupon_items( $order ) {

		$coupon_items = array();

		foreach ( $order->get_items( 'coupon' ) as $coupon_item_id => $coupon ) {

			$_coupon     = new WC_Coupon( $coupon['name'] );
			$coupon_post = get_post( SV_WC_Data_Compatibility::get_prop( $_coupon, 'id' ) );

			$coupon_item = array(
				'Id'          => $coupon_item_id,
				'Code'        => $coupon['name'],
				'Amount'      => wc_format_decimal( $coupon['discount_amount'], 2 ),
				'Description' => is_object( $coupon_post ) ? $coupon_post->post_excerpt : '',
			);

			/**
			 * XML Order Export Coupon Line Item.
			 *
			 * Filter the individual coupon line item entry
			 *
			 * @since 2.0.0
			 * @param array $coupon_item {
			 *     line item data in key => value format
			 *     the keys are for convenience and not necessarily used for exporting. Make
			 *     sure to prefix the values with the desired refund line item entry name
			 * }
			 *
			 * @param array $coupon WC order coupon item
			 * @param WC_Order $order the order
			 */
			$coupon_items['CouponItem'][] = apply_filters( 'wc_customer_order_xml_export_suite_order_coupon_item', $coupon_item, $coupon, $order );
		}

		return ! empty( $coupon_items ) ? $coupon_items : null;
	}


	/**
	 * Creates array of order refunds in format required for xml_to_array()
	 *
	 * Filter in method allows modification of individual refunds array format
	 *
	 * @since 2.0.0
	 * @param \WC_Order $order
	 * @return array|null - refunds in array format required by array_to_xml(), or null if no refunds
	 */
	private function get_refunds( $order ) {

		$refunds = array();

		foreach ( $order->get_refunds() as $refund ) {

			if ( SV_WC_Plugin_Compatibility::is_wc_version_gte_3_0() ) {

				$refund_data = array(
					'Id'     => $refund->get_id(),
					'Date'   => $this->format_date( $refund->get_date_created()->date( 'Y-m-d H:i:s' ) ),
					'Amount' => wc_format_decimal( $refund->get_amount(), 2 ),
					'Reason' => $refund->get_reason(),
				);

			} else {

				$refund_data = array(
					'Id'     => $refund->id,
					'Date'   => $this->format_date( $refund->date ),
					'Amount' => wc_format_decimal( $refund->get_refund_amount(), 2 ),
					'Reason' => $refund->get_refund_reason(),
				);
			}

			$refunded_items = array();

			// add line items
			foreach ( $refund->get_items( array( 'line_item', 'fee', 'shipping' ) ) as $item_id => $item ) {

				$refund_amount = abs( isset( $item['line_total'] ) ? $item['line_total'] : ( isset( $item['cost'] ) ? $item['cost'] : null ) );

				// skip empty refund lines
				if ( ! $refund_amount ) {
					continue;
				}

				$refunded_item = array(
					'RefundedItemId' => $item['refunded_item_id'],
					'RefundedTotal'  => $refund_amount,
					'RefundedTaxes'  => $this->get_tax_details( $item, 'RefundedTax' ),
				);

				if ( isset( $item['qty'] ) ) {
					$refunded_item['Quantity'] = abs( $item['qty'] );
				}

				$refunded_items[] = $refunded_item;
			}

			$refund_data['RefundedItems'] = ! empty( $refunded_items ) ? array( 'RefundedItem' => $refunded_items ) : null;

			/**
			 * XML Order Export Refund.
			 *
			 * Filter the individual refund entry
			 *
			 * @since 2.0.0
			 * @param array $refund {
			 *     line item data in key => value format
			 *     the keys are for convenience and not necessarily used for exporting. Make
			 *     sure to prefix the values with the desired refund entry name
			 * }
			 *
			 * @param \WC_Order_Refund $refund WC order refund instance
			 * @param WC_Order $order the order
			 */
			$refunds['Refund'][] = apply_filters( 'wc_customer_order_xml_export_suite_order_refund', $refund_data, $refund, $order );
		}

		return ! empty( $refunds ) ? $refunds : null;
	}


	/**
	 * Get tax details for an order item
	 *
	 * @since 2.0.0
	 * @param array $item
	 * @param string $field optional. Tag name to wrap tax details in. Defaults to `Tax`
	 * @return array|null
	 */
	private function get_tax_details( $item, $field = 'Tax' ) {

		if ( empty( $item ) ) {
			return null;
		}

		$taxes = array();

		if ( isset( $item['taxes'] ) ) {

			$taxes = maybe_unserialize( $item['taxes'] );

		} elseif ( isset( $item['line_tax_data'] ) ) {

			$tax_data = maybe_unserialize( $item['line_tax_data'] );
			$taxes    = $tax_data['total'];
		}

		if ( empty( $taxes ) ) {
			return null;
		}

		$tax_details = array();

		foreach ( $taxes as $rate_id => $amount ) {

			// refunds have negative amounts, but we want them - absolutely, positively - we do
			$tax_data = array( 'RateId' => $rate_id, 'Amount' => abs( $amount ) );

			/**
			 * Allow actors to modify the tax details data/format
			 *
			 * @since 2.0.0
			 * @param array $tax_data
			 * @param array $item related order item (line item, fee item or shipping item)
			 */
			$tax_details[ $field ][] = apply_filters( 'wc_customer_order_xml_export_suite_order_item_tax_data', $tax_data, $item );
		}

		return $tax_details;
	}


	/**
	 * Creates array of order notes in format required for xml_to_array()
	 *
	 * Filter in method allows modification of individual order notes array format
	 *
	 * @since 1.7.0
	 * @param object $order
	 * @return array|null - order notes in array format required by array_to_xml() or null if not notes
	 */
	private function get_formatted_order_notes( $order ) {

		$order_notes = $this->get_order_notes( $order );

		$order_note = array();

		if ( ! empty( $order_notes ) ) {

			foreach ( $order_notes as $note ) {

				$note_content = $note->comment_content;

				// strip newlines in legacy format - note: in modern formats,
				// SV_WC_Helper::array_to_xml will automatically escape HTML and newlines by wrapping
				// the contents of the tag in CDATA when necessary
				if ( 'legacy' == $this->export_format ) {
					$note_content = str_replace( array( "\r", "\n" ), ' ', $note_content );
				}

				/**
				 * Filters the format of order notes in the order XML export
				 *
				 * @since 1.7.0
				 * @param array - the data included for each order note
				 * @param \WC_Order $order
				 * @param object $note the order note comment object
				 */
				$order_note['OrderNote'][] = apply_filters( 'wc_customer_order_xml_export_suite_order_note', array(
					'Date'    => $this->format_date( $note->comment_date ),
					'Author'  => $note->comment_author,
					'Content' => $note_content,
				), $note, $order );
			}

		}

		return ! empty( $order_note ) ? $order_note : null;
	}


	/**
	 * Function to get an array of order note comment objects
	 *
	 * @since 1.7.0
	 * @param \WC_Order $order
	 * @return array - order notes as array of comment objects
	 */
	private function get_order_notes( $order ) {

		$callback = array( 'WC_Comments', 'exclude_order_comments' );

		$args = array(
			'post_id' => SV_WC_Order_Compatibility::get_prop( $order, 'id' ),
			'approve' => 'approve',
			'type'    => 'order_note',
		);

		remove_filter( 'comments_clauses', $callback );

		$order_notes = get_comments( $args );

		add_filter( 'comments_clauses', $callback );

		return $order_notes;
	}


	/**
	 * Get meta keys that should be included in the custom export format
	 *
	 * @since 2.0.0
	 * @param string $export_type
	 * @return array
	 */
	private function get_custom_format_meta_keys( $export_type ) {

		$meta = array();

		// Include all meta
		if ( 'yes' === get_option( 'wc_customer_order_xml_export_suite_' . $export_type . '_custom_format_include_all_meta' ) ) {

			$all_meta = wc_customer_order_xml_export_suite()->get_formats_instance()->get_all_meta_keys( $export_type );

			if ( ! empty( $all_meta ) ) {

				foreach ( $all_meta as $meta_key ) {

					$meta[ $meta_key ] = $meta_key;
				}
			}

		// Include some meta only, if defined
		} else {

			$column_mapping = (array) get_option( "wc_customer_order_xml_export_suite_{$export_type}_custom_format_mapping" );

			foreach ( $column_mapping as $column ) {

				if ( isset( $column['source'] ) && 'meta' === $column['source'] ) {
					$meta[ $column['meta_key'] ] = $column['name'];
				}
			}

		}

		return $meta;
	}


	/**
	 * Returns static columns that should be included in the custom export format.
	 *
	 * @since 2.2.0
	 * @param string $export_type
	 * @return array
	 */
	private function get_custom_format_static_fields( $export_type ) {

		$statics = array();

		$column_mapping = (array) get_option( 'wc_customer_order_xml_export_suite_' . $export_type . '_custom_format_mapping' );

		foreach ( $column_mapping as $column ) {

			if ( isset( $column['source'] ) && 'static' === $column['source'] ) {
				$statics[ $column['name'] ] = $column['static_value'];
			}
		}

		return $statics;
	}


	/**
	 * Get order data for the custom format
	 *
	 * @since 2.0.0
	 * @param array $order_data an array of order data for the given order
	 * @param \WC_Order $order the WC_Order object
	 * @return array modified order data
	 */
	private function get_order_custom_data( $order_data, WC_Order $order ) {

		$meta = $this->get_custom_format_meta_keys( 'orders' );

		// Fetch meta
		if ( ! empty( $meta ) ) {

			foreach ( $meta as $meta_key => $column_name ) {

				// remove the old field so we can prefix it instead
				unset( $order_data[ $column_name ] );

				$order_data[ "Meta-{$column_name}" ] = maybe_serialize( get_post_meta( SV_WC_Order_Compatibility::get_prop( $order, 'id' ), $meta_key, true ) );
			}
		}

		$order_data = array_merge( $order_data, $this->get_custom_format_static_fields( 'orders' ) );

		return $order_data;
	}


	/**
	 * Get customer data for the custom format
	 *
	 * @since 2.0.0
	 * @param array $customer_data an array of customer data for the given customer
	 * @param WP_User $user the WP_User user
	 * @return array modified customer data
	 */
	private function get_customer_custom_data( $customer_data, $user ) {

		$meta = $this->get_custom_format_meta_keys( 'customers' );

		// Fetch meta
		if ( ! empty( $meta ) && $user instanceof WP_User ) {

			foreach ( $meta as $meta_key => $column_name ) {

				// remove the old field so we can prefix it instead
				unset( $customer_data[ $column_name ] );

				$customer_data[ "Meta-{$column_name}" ] = maybe_serialize( get_user_meta( $user->ID, $meta_key, true ) );
			}
		}

		$customer_data = array_merge( $customer_data, $this->get_custom_format_static_fields( 'customers' ) );

		return $customer_data;
	}


	/**
	 * Gets coupon data for the custom format.
	 *
	 * @since 2.5.0
	 *
	 * @param array $coupon_data an array of coupon data for the given coupon
	 * @param \WC_Coupon $coupon the WC_Coupon object
	 * @return array modified coupon data
	 */
	private function get_coupon_custom_data( $coupon_data, $coupon ) {

		$meta = $this->get_custom_format_meta_keys( 'coupons' );

		// fetch meta
		if ( ! empty( $meta ) ) {

			foreach ( $meta as $meta_key => $column_name ) {

				// remove the old field so we can prefix it instead
				unset( $coupon_data[ $column_name ] );

				$coupon_data[ "Meta-{$column_name}" ] = maybe_serialize( get_post_meta( $coupon->get_id(), $meta_key, true ) );
			}
		}

		$coupon_data = array_merge( $coupon_data, $this->get_custom_format_static_fields( 'coupons' ) );

		return $coupon_data;
	}


	/**
	 * Get the XML for customers
	 *
	 * In 2.0.0 added $ids param
	 *
	 * @since 1.1.0
	 * @param array $ids customer IDs to export. also accepts an array of arrays with billing email and
	 *                   order Ids, for guest customers: array( $user_id, array( $billing_email, $order_id ) )
	 * @return string XML data
	 */
	public function get_customers_xml( $ids ) {

		$customers = $this->get_customers( $ids );

		/**
		 * Allow actors to change the XML array format for customers
		 *
		 * In 2.0.0 renamed from `wc_customer_order_xml_export_suite_customer_export_format`
		 * to `wc_customer_order_xml_export_suite_customers_xml_data`, removed root element
		 * from XML data array. Use the new `wc_customer_order_xml_export_suite_xml_root_element`
		 * filter instead.
		 *
		 * @since 1.0.0
		 * @param array XML data array
		 * @param array $customers
		 */
		$xml_array = apply_filters( 'wc_customer_order_xml_export_suite_customers_xml_data', array( 'Customer' => $customers ), $customers );

		/**
		 * Filter the generated customers XML
		 *
		 * @since 2.0.0
		 * @param string $xml XML string
		 * @param array $xml_array XML data as array
		 * @param array $customers An array of the customers data to write to to the XML
		 * @param array $customer_id The customer ids.
		 * @param string $export_format The customer export format.
		 */
		return apply_filters( 'wc_customer_order_xml_export_suite_customers_xml', $this->get_xml( $xml_array ), $xml_array, $customers, $ids, $this->export_format );
	}


	/**
	 * Get the customer data
	 *
	 * @since 1.1.0
	 * @param array $ids customer IDs to export. also accepts an array of arrays with billing email and
	 *                   order Ids, for guest customers: array( $user_id, array( $billing_email, $order_id ) )
	 * @return array customer data in the format key => content
	 */
	private function get_customers( $ids ) {

		$customers = array();

		foreach ( $ids as $customer_id ) {

			$order_id = null;

			if ( is_array( $customer_id ) ) {
				list( $customer_id, $order_id ) = $customer_id;
			}

			$user = is_numeric( $customer_id ) ? get_user_by( 'id', $customer_id ) : get_user_by( 'email', $customer_id );

			// guest, get info from order
			if ( ! $user && is_numeric( $order_id ) ) {

				$order = wc_get_order( $order_id );

				// create blank user
				$user = new stdClass();

				if ( $order ) {

					// set properties on user
					$user->ID                  = 0;
					$user->first_name          = SV_WC_Order_Compatibility::get_prop( $order, 'billing_first_name' );
					$user->last_name           = SV_WC_Order_Compatibility::get_prop( $order, 'billing_last_name' );
					$user->user_email          = SV_WC_Order_Compatibility::get_prop( $order, 'billing_email' );
					$user->user_login          = '';
					$user->user_pass           = '';
					$user->user_registered     = SV_WC_Order_Compatibility::get_date_created( $order )->date( 'Y-m-d H:i:s' );
					$user->billing_first_name  = SV_WC_Order_Compatibility::get_prop( $order, 'billing_first_name' );
					$user->billing_last_name   = SV_WC_Order_Compatibility::get_prop( $order, 'billing_last_name' );
					$user->billing_full_name   = $order->get_formatted_billing_full_name();
					$user->billing_company     = SV_WC_Order_Compatibility::get_prop( $order, 'billing_company' );
					$user->billing_email       = SV_WC_Order_Compatibility::get_prop( $order, 'billing_email' );
					$user->billing_phone       = SV_WC_Order_Compatibility::get_prop( $order, 'billing_phone' );
					$user->billing_address_1   = SV_WC_Order_Compatibility::get_prop( $order, 'billing_address_1' );
					$user->billing_address_2   = SV_WC_Order_Compatibility::get_prop( $order, 'billing_address_2' );
					$user->billing_postcode    = SV_WC_Order_Compatibility::get_prop( $order, 'billing_postcode' );
					$user->billing_city        = SV_WC_Order_Compatibility::get_prop( $order, 'billing_city' );
					$user->billing_state       = SV_WC_Order_Compatibility::get_prop( $order, 'billing_state' );
					$user->billing_country     = SV_WC_Order_Compatibility::get_prop( $order, 'billing_country' );
					$user->shipping_first_name = SV_WC_Order_Compatibility::get_prop( $order, 'shipping_first_name' );
					$user->shipping_last_name  = SV_WC_Order_Compatibility::get_prop( $order, 'shipping_last_name' );
					$user->shipping_full_name  = $order->get_formatted_shipping_full_name();
					$user->shipping_company    = SV_WC_Order_Compatibility::get_prop( $order, 'shipping_company' );
					$user->shipping_address_1  = SV_WC_Order_Compatibility::get_prop( $order, 'shipping_address_1' );
					$user->shipping_address_2  = SV_WC_Order_Compatibility::get_prop( $order, 'shipping_address_2' );
					$user->shipping_postcode   = SV_WC_Order_Compatibility::get_prop( $order, 'shipping_postcode' );
					$user->shipping_city       = SV_WC_Order_Compatibility::get_prop( $order, 'shipping_city' );
					$user->shipping_state      = SV_WC_Order_Compatibility::get_prop( $order, 'shipping_state' );
					$user->shipping_country    = SV_WC_Order_Compatibility::get_prop( $order, 'shipping_country' );
				}

			}

			// user not found, skip - this can occur when an invalid customer id or email was passed in
			if ( ! $user ) {
				continue;
			}

			$data = array(
				'CustomerId'        => $user->ID,
				'FirstName'         => $user->first_name,
				'LastName'          => $user->last_name,
				'Username'          => $user->user_login,
				'Email'             => $user->user_email,
				'Password'          => $user->user_pass,
				'DateRegistered'    => $this->format_date( $user->user_registered ),
				'BillingFirstName'  => $user->billing_first_name,
				'BillingLastName'   => $user->billing_last_name,
				/* translators: Placeholders: %1$s - first name; %2$s - last name / surname */
				'BillingFullName'   => ! empty( $user->billing_full_name ) ? $user->billing_full_name : sprintf( _x( '%1$s %2$s', 'full name', 'woocommerce-customer-order-xml-export-suite' ), $user->billing_first_name, $user->billing_last_name ),
				'BillingCompany'    => $user->billing_company,
				'BillingEmail'      => $user->billing_email,
				'BillingPhone'      => $user->billing_phone,
				'BillingAddress1'   => $user->billing_address_1,
				'BillingAddress2'   => $user->billing_address_2,
				'BillingPostcode'   => $user->billing_postcode,
				'BillingCity'       => $user->billing_city,
				'BillingState'      => $this->get_localized_state( $user->billing_country, $user->billing_state ),
				'BillingStateCode'  => $user->billing_state,
				'BillingCountry'    => $user->billing_country,
				'ShippingFirstName' => $user->shipping_first_name,
				'ShippingLastName'  => $user->shipping_last_name,
				/* translators: Placeholders: %1$s - first name; %2$s - last name / surname */
				'ShippingFullName'  => ! empty( $user->shipping_full_name ) ? $user->shipping_full_name : sprintf( _x( '%1$s %2$s', 'full name', 'woocommerce-customer-order-xml-export-suite' ), $user->shipping_first_name, $user->shipping_last_name ),
				'ShippingCompany'   => $user->shipping_company,
				'ShippingAddress1'  => $user->shipping_address_1,
				'ShippingAddress2'  => $user->shipping_address_2,
				'ShippingPostcode'  => $user->shipping_postcode,
				'ShippingCity'      => $user->shipping_city,
				'ShippingState'     => $this->get_localized_state( $user->shipping_country, $user->shipping_state ),
				'ShippingStateCode' => $user->shipping_state,
				'ShippingCountry'   => $user->shipping_country,
				'TotalSpent'        => wc_format_decimal( wc_get_customer_total_spent( $user->ID ), 2 ),
				'OrderCount'        => wc_get_customer_order_count( $user->ID ),
			);


			if ( ! empty( $this->format_definition ) && ! empty( $this->format_definition['fields'] ) ) {

				foreach ( $this->format_definition['fields'] as $key => $field ) {
					$customer_data[ $field ] = isset( $data[ $key ] ) ? $data[ $key ] : '';
				}

				if ( 'custom' === $this->export_format ) {
					$customer_data = $this->get_customer_custom_data( $customer_data, $user );
				}

			} else {
				$customer_data = $data;
			}

			/**
			 * XML Export Customer Data
			 *
			 * Filter the individual customer data
			 *
			 * @since 1.1.0
			 * @param array $customer_data
			 * @param \WP_User $user WP User object
			 * @param int|null $order_id an order ID for the customer. Null if registered customer.
			 * @param \WC_Customer_Order_XML_Export_Suite_Generator $this, generator instance
			 */
			$customers[] = apply_filters( 'wc_customer_order_xml_export_suite_customer_export_data', $customer_data, $user, $order_id, $this );
		}

		return $customers;
	}


	/**
	 * Gets the XML for coupons.
	 *
	 * @since 2.5.0
	 *
	 * @param array $ids coupon IDs to export
	 *
	 * @return string XML data
	 */
	public function get_coupons_xml( $ids ) {

		$coupons = $this->get_coupons( $ids );

		/**
		 * Allow actors to change the XML array format for coupons.
		 *
		 * @since 2.5.0
		 *
		 * @param array XML data array
		 * @param array $coupons
		 */
		$xml_array = apply_filters( 'wc_customer_order_xml_export_suite_coupons_xml_data', array( 'Coupon' => $coupons ), $coupons );

		/**
		 * Filter the generated coupons XML.
		 *
		 * @since 2.5.0
		 *
		 * @param string $xml XML string
		 * @param array $xml_array XML data as array
		 * @param array $coupons An array of the customers data to write to to the XML
		 * @param array $coupon_id The coupon IDs
		 * @param string $export_format The coupon export format
		 */
		return apply_filters( 'wc_customer_order_xml_export_suite_coupons_xml', $this->get_xml( $xml_array ), $xml_array, $coupons, $ids, $this->export_format );
	}


	/**
	 * Gets the coupon data.
	 *
	 * @since 2.5.0
	 *
	 * @param array $ids coupon IDs to export
	 *
	 * @return array coupon data in the format key => content
	 */
	private function get_coupons( $ids ) {

		$coupons = array();

		foreach ( $ids as $coupon_id ) {

			// load the coupon
			$coupon = new WC_Coupon( $coupon_id );

			// skip if the coupon can't be loaded
			if ( ! $coupon ) {
				continue;
			}

			// get all category names to match against coupon category IDs
			$categories     = get_categories( array( 'taxonomy' => 'product_cat' ) );
			$category_names = array();

			foreach ( $categories as $category ) {
				$category_names[ $category->term_id ] = $category->category_nicename;
			}

			// get this coupon's included and excluded products
			$product_ids          = $coupon->get_product_ids();
			$excluded_product_ids = $coupon->get_excluded_product_ids();

			// get this coupon's included and excluded product categories
			$product_category_ids          = $coupon->get_product_categories();
			$excluded_product_category_ids = $coupon->get_excluded_product_categories();

			// create array of product SKUs allowed by this coupon
			$products = array();

			foreach ( $product_ids as $product_id ) {
				$product = wc_get_product( $product_id );

				// only include products with a valid SKU
				// all products will appear in 'ProductIDs'
				if ( ! empty( $product->get_sku() ) ) {
					$products[] = $product->get_sku();
				}
			}

			// create array of product SKUs excluded by this coupon
			$excluded_products = array();

			foreach ( $excluded_product_ids as $excluded_product_id ) {
				$product = wc_get_product( $excluded_product_id );

				// only include products with a valid SKU
				// all products will appear in 'ExcludeProductIDs'
				if ( ! empty( $product->get_sku() ) ) {
					$excluded_products[] = $product->get_sku();
				}
			}

			// create array of product category names allowed by this coupon
			$product_categories = array();

			foreach ( $product_category_ids as $product_category_id ) {
				$product_categories[] = $category_names[ $product_category_id ];
			}

			// create array of product category names excluded by this coupon
			$excluded_product_categories = array();

			foreach ( $excluded_product_category_ids as $excluded_product_category_id ) {
				$excluded_product_categories[] = $category_names[ $excluded_product_category_id ];
			}

			$expiry_date           = $coupon->get_date_expires();
			$formatted_expiry_date = ( !empty( $expiry_date ) ) ? $this->format_date( $expiry_date->date( 'Y-m-d' ) ) : '';

			// create array of email addresses of customers who have used this coupon
			$used_by_customer_emails = array();

			$used_by = $coupon->get_used_by();

			foreach ( $used_by as $user_id ) {

				// this value may be a user ID or an email address
				if ( is_email( $user_id ) ) {

					$used_by_customer_emails['User'][] = array( 'Email' => $user_id );
				} elseif ( $user = get_user_by( 'id', $user_id ) ) {

					$user = get_user_by( 'id', $user_id );
					$used_by_customer_emails['User'][] = array( 'Email' => $user->user_email );
				}
			}

			$data = array(
				'Code'                     => $coupon->get_code(),
				'Type'                     => $coupon->get_discount_type(),
				'Description'              => $coupon->get_description(),
				'Amount'                   => $coupon->get_amount(),
				'ExpiryDate'               => $formatted_expiry_date,
				'EnableFreeShipping'       => $coupon->get_free_shipping() ? 'yes' : 'no',
				'MinimumAmount'            => wc_format_decimal( $coupon->get_minimum_amount() ),
				'MaximumAmount'            => wc_format_decimal( $coupon->get_maximum_amount() ),
				'IndividualUse'            => $coupon->get_individual_use() ? 'yes' : 'no',
				'ExcludeSaleItems'         => $coupon->get_exclude_sale_items() ? 'yes' : 'no',
				'Products'                 => implode( ', ', $products ),
				'ExcludeProducts'          => implode( ', ', $excluded_products ),
				'ProductCategories'        => implode( ', ', $product_categories ),
				'ExcludeProductCategories' => implode( ', ', $excluded_product_categories ),
				'CustomerEmails'           => implode( ', ', $coupon->get_email_restrictions() ),
				'UsageLimit'               => $coupon->get_usage_limit(),
				'LimitUsageToXItems'       => $coupon->get_limit_usage_to_x_items(),
				'UsageLimitPerUser'        => $coupon->get_usage_limit_per_user(),
				'UsageCount'               => $coupon->get_usage_count(),
				'ProductIDs'               => implode( ', ', $product_ids ),
				'ExcludeProductIDs'        => implode( ', ', $excluded_product_ids ),
				'UsedBy'                   => $used_by_customer_emails,
			);

			if ( ! empty( $this->format_definition ) && ! empty( $this->format_definition['fields'] ) ) {

				foreach ( $this->format_definition['fields'] as $key => $field ) {
					$coupon_data[ $field ] = isset( $data[ $key ] ) ? $data[ $key ] : '';
				}

				if ( 'custom' === $this->export_format ) {
					$coupon_data = $this->get_coupon_custom_data( $coupon_data, $coupon );
				}

			} else {
				$coupon_data = $data;
			}

			/**
			 * XML Export Coupon Data.
			 *
			 * Filter the individual coupon data
			 *
			 * @since 2.5.0
			 *
			 * @param array $coupon_data
			 * @param \WC_Customer_Order_XML_Export_Suite_Generator $this, generator instance
			 */
			$coupons[] = apply_filters( 'wc_customer_order_xml_export_suite_coupon_export_data', $coupon_data, $this );
		}

		return $coupons;
	}


	/**
	 * Returns the localized state for the order.
	 *
	 * TODO: Remove once WC 3.0+ is required {MR 2017-02-25}
	 *
	 * @param \WC_Order $order
	 * @param string $type state type, either billing or shipping.
	 * @return string
	 */
	protected function get_localized_state_for_order( WC_Order $order, $type ) {
		$country = SV_WC_Order_Compatibility::get_prop( $order, "{$type}_country" );
		$state   = SV_WC_Order_Compatibility::get_prop( $order, "{$type}_state" );
		return $this->get_localized_state( $country, $state );
	}


	/**
	 * Helper to localize state names for countries with numeric state codes
	 *
	 * @since 2.0.3
	 * @param string $country the country for the current customer
	 * @param string $state the state code for the current customer
	 * @return string the localized state name
	 */
	protected function get_localized_state( $country, $state ) {

		// countries that have numeric state codes
		$countries_with_numeric_states = array(
			'JP',
			'BG',
			'CN',
			'TH',
			'TR',
		);

		// only proceed if we need to replace a numeric state code
		if ( ! in_array( $country, $countries_with_numeric_states, true ) ) {
			return $state;
		}

		$state_name = $state;

		// get a state list for states the store sells to
		$states = WC()->countries->get_states();

		if ( ! empty ( $states[ $country ] ) && isset( $states[ $country ][ $state ] ) ) {
			$state_name = $states[ $country ][ $state ];
		}

		return $state_name;
	}


	/**
	 * Allows actors to modify the date format for the XML output if desired.
	 * This method won't change the date format by default, but allows third-parties to modify the output of all dates.
	 *
	 * @since 2.0.1-2
	 * @param string $date the date output for the XML
	 * @param \WC_Customer_Order_XML_Export_Suite_Generator $generator generator instance
	 * @return string the formatted date output
	 */
	protected function format_date( $date ) {
		return apply_filters( 'wc_customer_order_xml_export_suite_format_date', $date, $this );
	}


	/**
	 * Get the XML output for an array
	 *
	 * @since 2.0.0
	 * @param array $xml_array
	 * @return string
	 */
	private function get_xml( $xml_array ) {

		$this->writer->openMemory();
		$this->writer->setIndent( $this->indent );

		// generate xml starting with the root element and recursively generating child elements
		SV_WC_Helper::array_to_xml( $this->writer, $this->root_element, $xml_array );

		$replace = array(
			"<{$this->root_element}>\r\n",
			"<{$this->root_element}>\r",
			"<{$this->root_element}>\n",
			"<{$this->root_element}>",
			"</{$this->root_element}>\r\n",
			"</{$this->root_element}>\r",
			"</{$this->root_element}>\n",
			"</{$this->root_element}>",
		);

		return str_replace( $replace, '', $this->writer->outputMemory() );
	}


	/**
	 * Get XML headers
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_header() {

		$this->writer->openMemory();
		$this->writer->setIndent( $this->indent );

		$this->writer->startDocument( $this->xml_version, $this->xml_encoding, $this->xml_standalone );

		$this->writer->text( "<$this->root_element>" . ( $this->indent ? "\n" : "" ) );

		/**
		 * Allow actors to modify XML header
		 *
		 * @since 2.0.0
		 * @param string $header
		 */
		$header = apply_filters( 'wc_customer_order_xml_export_suite_' . $this->export_type . '_header', $this->writer->outputMemory() );

		return $header;
	}


	/**
	 * Get XML footer
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_footer() {

		$this->writer->openMemory();
		$this->writer->setIndent( $this->indent );

		$this->writer->text( "</$this->root_element>" . ( $this->indent ? "\n" : "" ) );

		$this->writer->endDocument();

		/**
		 * Allow actors to modify XML footer
		 *
		 * @since 2.0.0
		 * @param string $footer
		 */
		$footer = apply_filters( 'wc_customer_order_xml_export_suite_' . $this->export_type . '_footer', $this->writer->outputMemory() );

		return $footer;
	}


	/**
	 * Get output for the provided export type
	 *
	 * @since 2.0.0
	 * @param array $ids
	 * @return string
	 */
	public function get_output( $ids ) {

		switch ( $this->export_type ) {

			case 'orders':
				return $this->get_orders_xml( $ids );

			case 'customers':
				return $this->get_customers_xml( $ids );

			case 'coupons':
				return $this->get_coupons_xml( $ids );

			default:
				/**
				 * Allow actors to provide output for custom export types
				 *
				 * @since 2.0.0
				 * @param string $output defaults to empty string
				 * @param array $ids object IDs to export
				 * @param string $export_format export format, if any
				 */
				return apply_filters( 'wc_customer_order_xml_export_suite_get_' . $this->export_type . '_xml', '', $ids, $this->export_format );
		}
	}


}
