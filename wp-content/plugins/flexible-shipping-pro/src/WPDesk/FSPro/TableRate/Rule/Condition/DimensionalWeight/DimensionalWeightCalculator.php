<?php
/**
 * Trait DimensionalWeightCalculator
 *
 * @package WPDesk\FSPro\TableRate\Rule\Condition\DimensionalWeight
 */

namespace WPDesk\FSPro\TableRate\Rule\Condition\DimensionalWeight;

use WPDesk\FS\TableRate\Rule\ShippingContents\ShippingContents;

/**
 * Can calculate dimensional weight.
 *
 * @codeCoverageIgnore
 */
trait DimensionalWeightCalculator {
	/**
	 * @param ShippingContents $contents     .
	 * @param float|int        $weight_ratio .
	 *
	 * @return float
	 */
	private function get_contents_weight( $contents, $weight_ratio ) {
		$weight = array( 0, $contents->get_contents_weight() );

		if ( $weight_ratio > 0 ) {
			$weight[] = $this->calculate_contents_dimensional_weight( $contents, $weight_ratio );
		}

		return max( $weight );
	}

	/**
	 * @param ShippingContents $contents     .
	 * @param float|int        $weight_ratio .
	 *
	 * @return float
	 */
	private function calculate_contents_dimensional_weight( $contents, $weight_ratio ) {
		$weight = 0.0;

		foreach ( $contents->get_contents() as $item ) {
			$weight += $this->get_item_dimensional_weight( $item, $weight_ratio );
		}

		return $weight;
	}

	/**
	 * @param array     $item         .
	 * @param float|int $weight_ratio .
	 *
	 * @return float
	 */
	private function get_item_dimensional_weight( $item, $weight_ratio ) {
		/** @var WC_Product $product */

		$product = $item['data'];

		$width    = (float) $product->get_width();
		$height   = (float) $product->get_height();
		$length   = (float) $product->get_length();
		$quantity = $item['quantity'];

		return (float) ( $width * $height * $length / $weight_ratio ) * $quantity;
	}
}
