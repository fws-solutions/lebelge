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
 * Customer/Order XML Export Suite Database Stream Wrapper
 *
 * Wraps a database query for a given export in a php stream wrapper.
 *
 * @see http://php.net/manual/en/class.streamwrapper.php
 *
 * @since 2.4.0
 */
class WC_Customer_Order_XML_Export_Suite_Database_Stream_Wrapper {


	/** @var resource|null the current stream context or null if not set */
	public $context;

	/** @var \WC_Customer_Order_XML_Export_Suite_Export the export object */
	protected $export;

	/** @var \WC_Customer_Order_XML_Export_Suite_Data_Store_Database data store instance */
	protected $data_store;

	/** @var \WC_Customer_Order_XML_Export_Suite_Database_Stream_Iterator the stream iterator */
	protected $iterator;

	/** @var string current unread data buffer */
	private $buffer;

	/** @var int number of bytes read */
	private $bytes_read;

	/** @var int total bytes in this export */
	private $total_bytes;


	/**
	 * Constructs the stream wrapper.
	 *
	 * @since 2.4.0
	 */
	public function __construct() {

		$this->data_store = new WC_Customer_Order_XML_Export_Suite_Data_Store_Database();
	}


	/**
	 * Opens the stream.
	 *
	 * @since 2.4.0
	 *
	 * @param string $path the full path the stream was opened with, including the protocol
	 * @param string $mode the mode to open the stream with, 'r' and 'rb' accepted
	 * @param array $options unused
	 * @param string &$opened_path unused; path to the opened file
	 * @return bool whether the stream was opened successfully or not
	 * @throws \SV_WC_Plugin_Exception
	 */
	public function stream_open( $path, $mode, $options, &$opened_path ) {

		if ( ! in_array( $mode, array( 'r', 'rb' ), true ) ) {

			throw new SV_WC_Plugin_Exception( 'This protocol is read-only' );
		}

		$url = parse_url( $path );

		$export_id   = isset( $url['host'] ) ? $url['host'] : '';
		$export      = wc_customer_order_xml_export_suite_get_export( $export_id );

		if ( ! $export ) {
			throw new SV_WC_Plugin_Exception( sprintf( 'Invalid export ID: %s', $export_id ) );
		}

		$total_bytes = $this->data_store->get_file_size( $export );

		if ( 0 >= $total_bytes ) {
			return false;
		}

		$this->export      = $export;
		$this->total_bytes = $total_bytes;
		$this->iterator    = new WC_Customer_Order_XML_Export_Suite_Database_Stream_Iterator( $export );

		$this->iterator->rewind();

		return true;
	}


	/**
	 * Reads and returns a given number of bytes from the stream.
	 *
	 * If the bytes to read exceeds the available bytes, just returns what's available.
	 * Anything returned beyond the requested bytes is discarded.
	 *
	 * @since 2.4.0
	 *
	 * @param int $bytes_to_read number of bytes to read
	 * @return bool|string bytes from the stream, or false if unable to read
	 */
	public function stream_read( $bytes_to_read ) {

		$buffered_bytes = mb_strlen( $this->buffer, '8bit' );

		while ( $this->iterator->valid() && $buffered_bytes < $bytes_to_read ) {

			if ( $row = $this->iterator->get_next() ) {

				$this->buffer  .= $row;
				$buffered_bytes = mb_strlen( $this->buffer, '8bit' );
			}
		}

		$read_content = mb_substr( $this->buffer, 0, $bytes_to_read, '8bit' );
		$this->buffer = mb_substr( $this->buffer, $bytes_to_read, null, '8bit' ) . '';

		$this->bytes_read += mb_strlen( $read_content, '8bit' );

		return $read_content;
	}


	/**
	 * Closes the stream.
	 *
	 * Called whenever the stream is closed with fclose()
	 *
	 * @since 2.4.0
	 */
	public function stream_close() {

		if ( $this->iterator ) {

			$this->iterator->free_data_stream();

			$this->iterator = null;
		}
	}


	/**
	 * Indicates if all available data has been read.
	 *
	 * @since 2.4.0
	 *
	 * @return bool whether the stream has reached the end of the file
	 */
	public function stream_eof() {

		return $this->bytes_read >= $this->total_bytes;
	}


	/**
	 * Cleans up the stream upon destruction.
	 *
	 * @since 2.4.0
	 */
	public function __destruct() {

		$this->stream_close();
	}


	/**
	 * Returns the current position in the stream, in bytes.
	 *
	 * @since 2.4.0
	 *
	 * @return int position
	 */
	public function stream_tell() {

		return $this->bytes_read;
	}


}
