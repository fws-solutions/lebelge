<?php
/**
 * Filter: Post Type Filter
 *
 * Post type filter for search.
 *
 * @package wsal
 * @subpackage search
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WSAL_AS_Filters_PostTypeFilter' ) ) :

	/**
	 * WSAL_AS_Filters_PostTypeFilter.
	 *
	 * Post type filter class.
	 */
	class WSAL_AS_Filters_PostTypeFilter extends WSAL_AS_Filters_AbstractFilter {

		/**
		 * Instance of WpSecurityAuditLog.
		 *
		 * @var WpSecurityAuditLog
		 */
		public $wsal;

		/**
		 * Method: Constructor.
		 *
		 * @param object $search_wsal – Instance of main plugin.
		 * @since 3.1.0
		 */
		public function __construct( $search_wsal ) {
			$this->wsal = $search_wsal->wsal;
		}

		/**
		 * Method: Get Name.
		 */
		public function GetName() {
			return esc_html__( 'Post Type' );
		}

		/**
		 * Method: Get Prefixes.
		 */
		public function GetPrefixes() {
			return array( 'posttype' );
		}

		/**
		 * Method: Returns true if this filter has suggestions for this query.
		 *
		 * @param string $query - Part of query to check.
		 */
		public function IsApplicable( $query ) {
			$output     = 'names'; // names or objects, note names is the default
			$operator   = 'and';   // Conditions: and, or.
			$post_types = get_post_types( [], $output, $operator );

			// Search for the post type in query from available post types.
			$key = array_search( $query, $post_types );

			if ( ! empty( $key ) ) {
				return true;
			} else {
				return false;
			}

		}

		/**
		 * Method: Get Widgets.
		 */
		public function GetWidgets() {
			// Intialize single select widget class.
			$widget = new WSAL_AS_Filters_PostTypeWidget( $this, 'posttype', esc_html__( 'Post Type', 'wp-security-audit-log' ) );

			// Get the post types.
			$output     = 'names'; // names or objects, note names is the default
			$operator   = 'and'; // Conditions: and, or.
			$post_types = get_post_types( [], $output, $operator );

			// Search and remove attachment type.
			$key = array_search( 'attachment', $post_types, true );
			if ( false !== $key ) {
				unset( $post_types[ $key ] );
			}

			// Add select options to widget.
			foreach ( $post_types as $post_type ) {
				$widget->Add( ucwords( $post_type ), $post_type );
			}
			return array( $widget );
		}

		/**
		 * @inheritdoc
		 */
		public function ModifyQuery( $query, $prefix, $value ) {
			// Get DB connection array.
			$connection = $this->wsal->getConnector()->getAdapter( 'Occurrence' )->get_connection();
			$connection->set_charset( $connection->dbh, 'utf8mb4', 'utf8mb4_general_ci' );

			// Tables.
			$meta       = new WSAL_Adapters_MySQL_Meta( $connection );
			$table_meta = $meta->GetTable(); // Metadata.
			$occurrence = new WSAL_Adapters_MySQL_Occurrence( $connection );
			$table_occ  = $occurrence->GetTable(); // Occurrences.

			// Post type search condition.
			$sql = "$table_occ.id IN ( SELECT occurrence_id FROM $table_meta as meta WHERE meta.name='PostType' AND ( ";

			// Get the last post type.
			$last_type = end( $value );

			foreach ( $value as $post_type ) {
				if ( $last_type === $post_type ) {
					continue;
				} else {
					$sql .= "meta.value='$post_type' OR ";
				}
			}

			// Add placeholder for the last post type.
			$sql .= "meta.value='%s' ) )";

			// Check prefix.
			switch ( $prefix ) {
				case 'posttype':
					$query->addORCondition( array( $sql => $last_type ) );
					break;
				default:
					throw new Exception( 'Unsupported filter "' . $prefix . '".' );
			}
		}
	}

endif;
