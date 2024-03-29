<?php
/**
 * Class WSAL_Rep_HtmlReportGenerator
 * Provides utility methods to generate an html report
 *
 * @package wsal
 * @subpackage reports
 */

if ( ! class_exists( 'WSAL_Rep_Plugin' ) ) {
	exit( 'You are not allowed to view this page.' );
}

/**
 * Class WSAL_Rep_HtmlReportGenerator
 * Provides utility methods to generate an html report
 *
 * @package wsal
 * @subpackage reports
 */
class WSAL_Rep_HtmlReportGenerator extends WSAL_Rep_AbstractReportGenerator {

	/**
	 * Report Filters.
	 *
	 * @var array
	 */
	private $_filters = array();

	/**
	 * Generate the HTML report file
	 *
	 * @param array  $data - Data.
	 * @param array  $filters - Filters.
	 * @param string $uploads_dir_path - Uploads Directory Path.
	 * @param array  $_alert_groups - Alert Groups.
	 * @return int|string
	 */
	public function Generate( array $data, array $filters, $uploads_dir_path, array $_alert_groups = array() ) {
		if ( empty( $data ) ) {
			return 0;
		}
		if ( empty( $filters ) ) {
			return 0;
		}
		$this->_filters = $filters;

		// Split data by blog so we can display an organized report.
		$tempData = array();
		foreach ( $data as $k => $entry ) {
			$blogName              = $entry['blog_name'];
			$user                  = get_user_by( 'login', $entry['user_name'] );
			$entry['user_displayname'] = empty( $user ) ? '' : WSAL_Utilities_UsersUtils::get_display_label( WpSecurityAuditLog::GetInstance(), $user );
			if ( ! isset( $tempData[ $blogName ] ) ) {
				$tempData[ $blogName ] = array();
			}
			array_push( $tempData[ $blogName ], $entry );
		}

		if ( empty( $tempData ) ) {
			return 0;
		}

		// Check directory once more.
		if ( ! is_dir( $uploads_dir_path ) || ! is_readable( $uploads_dir_path ) || ! is_writable( $uploads_dir_path ) ) {
			return 1;
		}

		$report_filename = 'wsal_report_' . WSAL_Rep_Util_S::GenerateRandomString() . '.html';
		$report_filepath = $uploads_dir_path . $report_filename;

		$file = fopen( $report_filepath, 'w' );

		// Print styles for report.
		$print_styles = '<style>@media print { td.message { min-width: 400px; } }</style>';

		fwrite( $file, '<!DOCTYPE html><html><head>' );
		fwrite( $file, '<meta charset="utf-8">' );
		fwrite( $file, '<title>' . __( 'WP Activity Log Reporter', 'wp-security-audit-log' ) . '</title>' );
		fwrite( $file, $print_styles );
		fwrite( $file, '</head>' );
		fwrite( $file, '<body style=\'margin: 0 0;padding: 0 0;font-family: "Open Sans", sans-serif;font-size: 14px;color: #404040;\'><div class="wsal_report_wrap" style="margin: 20px 25px;">' );
		fwrite( $file, $this->_writeHeader( array_keys( $tempData ), $_alert_groups ) );

		foreach ( $tempData as $blogName => $alerts ) {
			if ( ! empty( $filters['number_logins'] ) ) {
				$this->_writeAlertsForBlog( $blogName, $alerts, $file, true );
			} else {
				if ( isset( $filters['type_statistics'] ) ) {
					$this->_writeAlertsStatistics( $blogName, $alerts, $file, $filters['type_statistics'] );
				} else {
					$this->_writeAlertsForBlog( $blogName, $alerts, $file );
				}
			}
		}

		fwrite( $file, '</div></body></html>' );
		fclose( $file );
		return $report_filename;
	}

	/**
	 * Generate the HTML head of the Report.
	 *
	 * @param array $blog_names — Array of site names.
	 * @param array $_alert_groups – Group of alerts.
	 *
	 * @return string
	 */
	private function _writeHeader( array $blog_names, $_alert_groups ) {
		$str  = '<div id="section-1" style="margin: 0 0; padding: 0 0;text-align: center;">';
		$str .= '<h1 style="color: rgb(54,95, 145);">' . __( 'Report from', 'wp-security-audit-log' ) . ' ' . get_bloginfo( 'name' ) . ' ' . __( 'website', 'wp-security-audit-log' ) . '</h1>';
		$str .= '</div>';

		$date = WSAL_Utilities_DateTimeFormatter::instance()->getFormattedDateTime( current_time( 'timestamp', true ) );

		$user = wp_get_current_user();
		$str .= '<div id="section-2" style="margin: 0 0; padding: 0 0;">';
		$str .= '<p style="margin: 0 0; padding: 5px 0; font-size: 13px;border-bottom: solid 1px #333;"><strong>' . __( 'Report Details:', 'wp-security-audit-log' ) . '</strong></p>';
		$str .= '<p style="margin: 0 0; padding: 5px 0; font-size: 13px;"><strong>' . __( 'Generated on:', 'wp-security-audit-log' ) . '</strong> ' . $date . '</p>';
		$str .= '<p style="margin: 0 0; padding: 5px 0; font-size: 13px;"><strong>' . __( 'Generated by:', 'wp-security-audit-log' ) . '</strong> ' . $user->user_login . ' — ' . $user->user_email . '</p>';

		$filters = $this->_filters;

		$theader  = '<table class="wsal_report_table" style="border: solid 1px #333333;border-spacing:5px;border-collapse: collapse;margin: 0 0;width: 100%;font-size: 14px;">';
		$theader .= '<thead style="background-color: #555555;border: 1px solid #555555;color: #ffffff;padding: 0 0;text-align: left;vertical-align: top;"><tr>';
		$theader .= '<td style="padding: 16px 7px;"><p style="margin: 0">' . __( 'Site(s)', 'wp-security-audit-log' ) . '</p></td>';
		$theader .= '<td style="padding: 16px 7px;"><p style="margin: 0">' . __( 'User(s)', 'wp-security-audit-log' ) . '</p></td>';
		$theader .= '<td style="padding: 16px 7px;"><p style="margin: 0">' . __( 'Role(s)', 'wp-security-audit-log' ) . '</p></td>';
		$theader .= '<td style="padding: 16px 7px;"><p style="margin: 0">' . __( 'IP address(es)', 'wp-security-audit-log' ) . '</p></td>';
		$theader .= '<td style="padding: 16px 7px;"><p style="margin: 0">' . __( 'Alert Groups', 'wp-security-audit-log' ) . '</p></td>';
		$theader .= '<td style="padding: 16px 7px;"><p style="margin: 0">' . __( 'Alert Code(s)', 'wp-security-audit-log' ) . '</p></td>';
		$theader .= '<td style="padding: 16px 7px;"><p style="margin: 0">' . __( 'Start date', 'wp-security-audit-log' ) . '</p></td>';
		$theader .= '<td style="padding: 16px 7px;"><p style="margin: 0">' . __( 'End date', 'wp-security-audit-log' ) . '</p></td>';
		$theader .= '<td style="padding: 16px 7px;"><p style="margin: 0">' . __( 'Post Types', 'wp-security-audit-log' ) . '</p></td>';
		$theader .= '<td style="padding: 16px 7px;"><p style="margin: 0">' . __( 'Post Status', 'wp-security-audit-log' ) . '</p></td>';
		$theader .= '</tr></thead>';

		$v1  =
		$v2  =
		$v3  =
		$v4  =
		$v5  =
		$v6  = __( 'All', 'wp-security-audit-log' );
		$v7  = __( 'From the beginning', 'wp-security-audit-log' );
		$v8  = $this->getFormattedDate( current_time( 'timestamp' ) );
		$v9  =
		$v10 = __( 'All', 'wp-security-audit-log' );

		if ( ! empty( $filters['sites'] ) ) {
			$v1 = implode( ', ', $blog_names );
		}
		if ( ! empty( $filters['users'] ) ) {
			$tmp = array();
			foreach ( $filters['users'] as $user_id ) {
				$u = get_user_by( 'id', $user_id );
				array_push( $tmp, $u->user_login . ' — ' . $u->user_email );
			}
			$v2 = implode( ',<br>', $tmp );
		}
		if ( ! empty( $filters['roles'] ) ) {
			$v3 = implode( ', ', $filters['roles'] );
		}
		if ( ! empty( $filters['ip-addresses'] ) ) {
			$v4 = implode( ', ', $filters['ip-addresses'] );
		}
		if ( ! empty( $filters['alert_codes']['groups'] ) ) {
			if ( count( $_alert_groups ) <> count( $filters['alert_codes']['groups'] ) ) {
				$v5 = implode( ', ', $filters['alert_codes']['groups'] );
			}
		}
		if ( ! empty( $filters['alert_codes']['alerts'] ) ) {
			$v6 = implode( ', ', $filters['alert_codes']['alerts'] );
		}
		if ( ! empty( $filters['date_range']['start'] ) ) {
			$v7 = $filters['date_range']['start'];
		}
		if ( ! empty( $filters['date_range']['end'] ) ) {
			$v8 = $filters['date_range']['end'];
		}
		if ( ! empty( $filters['alert_codes']['post_types'] ) ) {
			$post_types = array_map( 'ucfirst', $filters['alert_codes']['post_types'] );
			$v9         = implode( ', ', $post_types );
		}
		if ( ! empty( $filters['alert_codes']['post_statuses'] ) ) {
			$post_statuses = array_map( 'ucfirst', $filters['alert_codes']['post_statuses'] );
			$v10           = implode( ', ', $post_statuses );
		}

		$str .= '<p><strong>' . __( 'Criteria', 'wp-security-audit-log' ) . ':</strong></p>';

		$tbody  = '<tbody><tr>';
		$tbody .= '<td style="padding: 16px 7px;">' . $v1 . '</td>';
		$tbody .= '<td style="padding: 16px 7px;">' . $v2 . '</td>';
		$tbody .= '<td style="padding: 16px 7px;">' . $v3 . '</td>';
		$tbody .= '<td style="padding: 16px 7px;">' . $v4 . '</td>';
		$tbody .= '<td style="padding: 16px 7px;">' . $v5 . '</td>';
		$tbody .= '<td style="padding: 16px 7px;">' . $v6 . '</td>';
		$tbody .= '<td style="padding: 16px 7px;">' . $v7 . '</td>';
		$tbody .= '<td style="padding: 16px 7px;">' . $v8 . '</td>';
		$tbody .= '<td style="padding: 16px 7px;">' . $v9 . '</td>';
		$tbody .= '<td style="padding: 16px 7px;">' . $v10 . '</td>';
		$tbody .= '</tr></tbody>';
		$tbody .= '</table>';

		$str .= $theader . $tbody;

		$str .= '</div>';
		return $str;
	}

	/**
	 * Generate the HTML body of the standard Report.
	 */
	private function _writeAlertsForBlog( $blogName, array $data, $file, $isNumLogin = false ) {
		fwrite( $file, '<h3 style="font-size: 20px; margin: 25px 0;">' . $blogName . '</h3>' );
		fwrite( $file, '<table class="wsal_report_table" style="border: solid 1px #333333;border-spacing:5px;border-collapse: collapse;margin: 0 0;width: 100%;font-size: 14px;">' );
		if ( $isNumLogin ) {
			$columns  = array(
				esc_html__('Username', 'wp-security-audit-log'),
				esc_html__('User', 'wp-security-audit-log'),
				esc_html__('Role', 'wp-security-audit-log'),
				esc_html__('Logins', 'wp-security-audit-log'),
			);
			$tempData = array();
			foreach ( $data as $k => $entry ) {
				$user_name = $entry['user_name'];
				if ( ! isset( $tempData[ $user_name ] ) ) {
					$tempData[ $user_name ] = array(
						'counter'   => 1,
						'user_name' => $user_name, // Username of the user.
						'user'      => $entry['user_displayname'],
						'role'      => $entry['role'],
					);
				} else {
					$tempData[ $user_name ]['counter']++;
				}
			}
		} else {
			$columns = array(
				esc_html__('Code', 'wp-security-audit-log'),
				esc_html__('Type', 'wp-security-audit-log'),
				esc_html__('Date', 'wp-security-audit-log'),
				esc_html__('Username', 'wp-security-audit-log'),
				esc_html__('User', 'wp-security-audit-log'),
				esc_html__('Role', 'wp-security-audit-log'),
				esc_html__('Source IP', 'wp-security-audit-log'),
				esc_html__('Object Type', 'wp-security-audit-log'),
				esc_html__('Event Type', 'wp-security-audit-log'),
				esc_html__('Message', 'wp-security-audit-log'),
			);
		}

		$h = '';
		foreach ( $columns as $item ) {
			$h .= '<td style="padding: 16px 7px;"><p style="margin: 0">' . $item . '</p></td>';
		}
		fwrite( $file, '<thead style="background-color: #555555;border: 1px solid #555555;color: #ffffff;padding: 0 0;text-align: left;vertical-align: top;"><tr>' . $h . '</tr></thead>' );
		fwrite( $file, '<tbody>' );

		if ( $isNumLogin ) {
			$i = 0;
			foreach ( $tempData as $alert ) {
				$r  = ( $i % 2 != 0 ) ? '<tr style="background-color: #f1f1f1;">' : '<tr style="background-color: #ffffff;">';
				$r .= '<td style="padding: 16px 7px;"><p style="margin: 0">' . $alert['user_name'] . '</p></td>';
				$r .= '<td style="padding: 16px 7px;"><p style="margin: 0">' . $alert['user'] . '</p></td>';
				$r .= '<td style="padding: 16px 7px;"><p style="margin: 0">' . $alert['role'] . '</p></td>';
				$r .= '<td style="padding: 16px 7px;"><p style="margin: 0">' . $alert['counter'] . '</p></td>';
				$r .= '</tr>';
				fwrite( $file, $r );
				$i++;
			}
		} else {
			foreach ( $data as $i => $alert ) {
				$date = WSAL_Utilities_DateTimeFormatter::instance()->getFormattedDateTime( $alert['timestamp'] );
				$r  = ( 0 !== $i % 2 ) ? '<tr style="background-color: #f1f1f1;">' : '<tr style="background-color: #ffffff;">';
				$r .= '<td style="padding: 16px 7px; text-align: center; font-weight: 700;">' . $alert['alert_id'] . '</td>';
				$r .= '<td style="padding: 16px 7px;">' . $alert['code'] . '</td>';
				$r .= '<td style="padding: 16px 7px;">' . $date . '</td>';
				$r .= '<td style="padding: 16px 7px;">' . $alert['user_name'] . '</td>';
				$r .= '<td style="padding: 16px 7px; min-width: 100px;">' . $alert['user_displayname'] . '</td>';
				$r .= '<td style="padding: 16px 7px;">' . $alert['role'] . '</td>';
				$r .= '<td style="padding: 16px 7px;">' . $alert['user_ip'] . '</td>';
				$r .= '<td style="padding: 16px 7px;">' . $alert['object'] . '</td>';
				$r .= '<td style="padding: 16px 7px;">' . $alert['event_type'] . '</td>';
				$r .= '<td style="padding: 16px 7px; word-break: break-all; line-height: 1.5;" class="message">' . $alert['message'] . '</td>';
				$r .= '</tr>';
				fwrite( $file, $r );
			}
		}
		fwrite( $file, '</tbody></table>' );
	}

	/**
	 * Generate the HTML body of the Unique IP Report.
	 */
	public function GenerateUniqueIPS( $data, $uploads_dir_path, $dateStart, $dateEnd ) {
		if ( empty( $data ) ) {
			return 0;
		}
		// Check directory once more
		if ( ! is_dir( $uploads_dir_path ) || ! is_readable( $uploads_dir_path ) || ! is_writable( $uploads_dir_path ) ) {
			return 1;
		}

		$report_filename   = 'wsal_report_' . WSAL_Rep_Util_S::GenerateRandomString() . '.html';
		$report_filepath   = $uploads_dir_path . $report_filename;
		$date = WSAL_Utilities_DateTimeFormatter::instance()->getFormattedDateTime( current_time( 'timestamp', true ) );

		if ( ! empty( $dateStart ) ) {
			$v1 = $dateStart;
		} else {
			$v1 = __( 'From the beginning', 'wp-security-audit-log' );
		}
		if ( ! empty( $dateEnd ) ) {
			$v2 = $dateEnd;
		} else {
			$v2 = $this->getFormattedDate( current_time( 'timestamp' ) );
		}

		$user = wp_get_current_user();

		$file = fopen( $report_filepath, 'w' );

		fwrite( $file, '<!DOCTYPE html><html><head>' );
		fwrite( $file, '<meta charset="utf-8">' );
		fwrite( $file, '<title>' . __( 'WP Activity Log Reporter', 'wp-security-audit-log' ) . '</title>' );
		fwrite( $file, '</head>' );
		fwrite( $file, '<body style=\'margin: 0 0;padding: 0 0;font-family: "Open Sans", sans-serif;font-size: 14px;color: #404040;\'><div class="wsal_report_wrap" style="margin: 20px 25px;">' );
		$html  = '<p id="by" style="font-size: 13px; margin: 0 0; padding: 0 0; text-align: center;">' . esc_html__('Report generated with', 'wp-security-audit-log' ) . '</p>';
		$html .= '<div id="section-1" style="margin: 0 0; padding: 0 0;text-align: center;">';
		$html .= '<a href="https://www.wpwhitesecurity.com" target="_blank" style="text-decoration:none;"><h1 style="color: rgb(54,95, 145);">WP Activity Log</h1></a>';
		$html .= '<p id="dev" style="margin-top:10px;">' . esc_html__('WordPress Plugin', 'wp-security-audit-log' ) . '</p>';
		$html .= '</div>';
		$html .= '<div id="section-2" style="margin: 0 0; padding: 0 0;">';
		$html .= '<p style="margin: 0 0; padding: 5px 0; font-size: 13px;border-bottom: solid 1px #333;"><strong>' . __( 'Report Details:', 'wp-security-audit-log' ) . '</strong></p>';
		$html .= '<p style="margin: 0 0; padding: 5px 0; font-size: 13px;"><strong>' . __( 'Generated on:', 'wp-security-audit-log' ) . '</strong> ' . $date . '</p>';
		$html .= '<p style="margin: 0 0; padding: 5px 0; font-size: 13px;"><strong>' . __( 'Generated by:', 'wp-security-audit-log' ) . '</strong> ' . $user->user_login . '</p>';
		$html .= '<p><strong>' . __( 'Criteria', 'wp-security-audit-log' ) . ':</strong></p>';
		$html .= '<table class="wsal_report_table" style="border: solid 1px #333333;border-spacing:5px;border-collapse: collapse;margin: 0 0;width: 100%;font-size: 14px;">';
		$html .= '<thead style="background-color: #555555;border: 1px solid #555555;color: #ffffff;padding: 0 0;text-align: left;vertical-align: top;"><tr>';
		$html .= '<td style="padding: 16px 7px;"><p style="margin: 0">' . __( 'Alert Code(s)', 'wp-security-audit-log' ) . '</p></td>';
		$html .= '<td style="padding: 16px 7px;"><p style="margin: 0">' . __( 'Start date', 'wp-security-audit-log' ) . '</p></td>';
		$html .= '<td style="padding: 16px 7px;"><p style="margin: 0">' . __( 'End date', 'wp-security-audit-log' ) . '</p></td>';
		$html .= '</tr></thead>';
		$html .= '<tbody><tr>';
		$html .= '<td style="padding: 16px 7px;"><p style="margin: 0">' . __( 'List of unique IP addresses used by the same user', 'wp-security-audit-log' ) . '</p></td>';
		$html .= '<td style="padding: 16px 7px;"><p style="margin: 0">' . $v1 . '</p></td>';
		$html .= '<td style="padding: 16px 7px;"><p style="margin: 0">' . $v2 . '</p></td>';
		$html .= '</tr></tbody>';
		$html .= '</table>';
		$html .= '</table>';
		$html .= '</div>';
		fwrite( $file, $html );
		fwrite( $file, '<h4>' . __( 'Results', 'wp-security-audit-log' ) . ':</h4>' );
		fwrite( $file, '<table class="wsal_report_table" style="border: solid 1px #333333;border-spacing:5px;border-collapse: collapse;margin: 0 0;width: 100%;font-size: 14px;">' );
		$columns = array(
			esc_html__('Username', 'wp-security-audit-log'),
			esc_html__('Display name', 'wp-security-audit-log'),
			esc_html__('Unique IP', 'wp-security-audit-log'),
			esc_html__('List of IP addresses', 'wp-security-audit-log')
		);
		$h       = '';
		foreach ( $columns as $item ) {
			$h .= '<td style="padding: 16px 7px;"><p style="margin: 0">' . $item . '</p></td>';
		}
		fwrite( $file, '<thead style="background-color: #555555;border: 1px solid #555555;color: #ffffff;padding: 0 0;text-align: left;vertical-align: top;"><tr>' . $h . '</tr></thead>' );
		fwrite( $file, '<tbody>' );

		foreach ( $data as $i => $element ) {
			$r  = ( $i % 2 != 0 ) ? '<tr style="background-color: #f1f1f1;">' : '<tr style="background-color: #ffffff;">';
			$r .= '<td style="padding: 16px 7px;"><p style="margin: 0">' . $element['user_login'] . '</p></td>';
			$r .= '<td style="padding: 16px 7px;"><p style="margin: 0">' . $element['display_name'] . '</p></td>';
			$r .= '<td style="padding: 16px 7px;"><p style="margin: 0">' . count( $element['ips'] ) . '</p></td>';
			$r .= '<td style="padding: 16px 7px;"><p style="margin: 0">' . '<ul><li>' . implode( '</li><li>', $element['ips'] ) . '</li></ul>' . '</p></td>';
			$r .= '</tr>';
			fwrite( $file, $r );
		}
		fwrite( $file, '</tbody></table>' );
		fclose( $file );
		return $report_filename;
	}

	/**
	 * Generate the HTML body of the Statistics Report.
	 */
	private function _writeAlertsStatistics( $blogName, array $data, $file, $typeStatistics ) {
		fwrite( $file, '<h3 style="font-size: 20px; margin: 25px 0;">' . $blogName . '</h3>' );
		fwrite( $file, '<table class="wsal_report_table" style="border: solid 1px #333333;border-spacing:5px;border-collapse: collapse;margin: 0 0;width: 100%;font-size: 14px;">' );
		// Logins Report
		if ( $typeStatistics == WSAL_Rep_Common::LOGIN_BY_USER || $typeStatistics == WSAL_Rep_Common::LOGIN_BY_ROLE ) {
			$columns  = array(
				esc_html__('Date', 'wp-security-audit-log'),
				esc_html__('Number of Logins', 'wp-security-audit-log'),
			);
			$tempData = array();
			foreach ( $data as $k => $entry ) {
				$entry_date = $this->getFormattedDate( $entry['timestamp'] );
				if ( ! isset( $tempData[ $entry_date ] ) ) {
					$tempData[ $entry_date ] = array(
						'count' => 1,
					);
				} else {
					$tempData[ $entry_date ]['count']++;
				}
			}
		}
		// Views Report
		if ( $typeStatistics == WSAL_Rep_Common::VIEWS_BY_USER || $typeStatistics == WSAL_Rep_Common::VIEWS_BY_ROLE ) {
			$columns  = array(
				esc_html__('Date', 'wp-security-audit-log'),
				esc_html__('Views', 'wp-security-audit-log'),
			);
			$tempData = array();
			foreach ( $data as $k => $entry ) {
				$entry_date = $this->getFormattedDate( $entry['timestamp'] );
				switch ( $entry['alert_id'] ) {
					case '2101':
						if ( ! empty( $tempData[ $entry_date ]['posts'] ) ) {
							$tempData[ $entry_date ]['posts'] += 1;
						} else {
							$tempData[ $entry_date ]['posts'] = 1;
						}
						break;
					case '2103':
						if ( ! empty( $tempData[ $entry_date ]['pages'] ) ) {
							$tempData[ $entry_date ]['pages'] += 1;
						} else {
							$tempData[ $entry_date ]['pages'] = 1;
						}
						break;
					case '2105':
						if ( ! empty( $tempData[ $entry_date ]['custom'] ) ) {
							$tempData[ $entry_date ]['custom'] += 1;
						} else {
							$tempData[ $entry_date ]['custom'] = 1;
						}
						break;
					default:
						//  fallback for any other alerts would go here
						break;
				}
			}
		}
		// Published content Report
		if ( $typeStatistics == WSAL_Rep_Common::PUBLISHED_BY_USER || $typeStatistics == WSAL_Rep_Common::PUBLISHED_BY_ROLE ) {
			$columns  = array(
				esc_html__('Date', 'wp-security-audit-log'),
				esc_html__('Published', 'wp-security-audit-log'),
			);
			$tempData = array();
			foreach ( $data as $k => $entry ) {
				$entry_date = $this->getFormattedDate( $entry['timestamp'] );
				switch ( $entry['alert_id'] ) {
					case '2001':
						if ( ! empty( $tempData[ $entry_date ]['posts'] ) ) {
							$tempData[ $entry_date ]['posts'] += 1;
						} else {
							$tempData[ $entry_date ]['posts'] = 1;
						}
						break;
					case '2005':
						if ( ! empty( $tempData[ $entry_date ]['pages'] ) ) {
							$tempData[ $entry_date ]['pages'] += 1;
						} else {
							$tempData[ $entry_date ]['pages'] = 1;
						}
						break;
					case '2030':
						if ( ! empty( $tempData[ $entry_date ]['custom'] ) ) {
							$tempData[ $entry_date ]['custom'] += 1;
						} else {
							$tempData[ $entry_date ]['custom'] = 1;
						}
						break;
					case '9001':
						if ( ! empty( $tempData[ $entry_date ]['woocommerce'] ) ) {
							$tempData[ $entry_date ]['woocommerce'] += 1;
						} else {
							$tempData[ $entry_date ]['woocommerce'] = 1;
						}
						break;
					default:
						//  fallback for any other alerts would go here
						break;
				}
			}
		}

		$h = '';
		foreach ( $columns as $item ) {
			$h .= '<td style="padding: 16px 7px;"><p style="margin: 0">' . $item . '</p></td>';
		}
		fwrite( $file, '<thead style="background-color: #555555;border: 1px solid #555555;color: #ffffff;padding: 0 0;text-align: left;vertical-align: top;"><tr>' . $h . '</tr></thead>' );
		fwrite( $file, '<tbody>' );

		// Logins Report
		if ( $typeStatistics == WSAL_Rep_Common::LOGIN_BY_USER || $typeStatistics == WSAL_Rep_Common::LOGIN_BY_ROLE ) {
			$i     = 0;
			$total = 0;
			foreach ( $tempData as $date => $alert ) {
				$r  = ( $i % 2 != 0 ) ? '<tr style="background-color: #f1f1f1;">' : '<tr style="background-color: #ffffff;">';
				$r .= '<td style="padding: 16px 7px;"><p style="margin: 0">' . $date . '</p></td>';
				$r .= '<td style="padding: 16px 7px;"><p style="margin: 0">' . $alert['count'] . '</p></td>';
				$r .= '</tr>';
				fwrite( $file, $r );
				$total = ( $total + intval( $alert['count'] ) );
				$i++;
			}
			$r  = ( $i % 2 != 0 ) ? '<tr style="background-color: #f1f1f1;">' : '<tr style="background-color: #ffffff;">';
			$r .= '<td style="padding: 16px 7px;"><p style="margin: 0">' . esc_html__('TOTAL', 'wp-security-audit-log') . '</p></td>';
			$r .= '<td style="padding: 16px 7px;"><p style="margin: 0">' . $total . '</p></td>';
			$r .= '</tr>';
			fwrite( $file, $r );
		}
		// Views Report
		if ( $typeStatistics == WSAL_Rep_Common::VIEWS_BY_USER || $typeStatistics == WSAL_Rep_Common::VIEWS_BY_ROLE || $typeStatistics == WSAL_Rep_Common::PUBLISHED_BY_USER || $typeStatistics == WSAL_Rep_Common::PUBLISHED_BY_ROLE ) {
			$i = 0;
			foreach ( $tempData as $date => $alert ) {
				$posts         = ! empty( $alert['posts'] ) ? $alert['posts'] : 0;
				$pages         = ! empty( $alert['pages'] ) ? $alert['pages'] : 0;
				$custom        = ! empty( $alert['custom'] ) ? $alert['custom'] : 0;
				$woocommerce   = ! empty( $alert['woocommerce'] ) ? $alert['woocommerce'] : 0;

				$r           = ( $i % 2 != 0 ) ? '<tr style="background-color: #f1f1f1;">' : '<tr style="background-color: #ffffff;">';
				$r          .= '<td style="padding: 16px 7px;"><p style="margin: 0;font-weight:bold;">' . $date . '</p></td>';
				$r          .= '<td></td>';
				$r          .= '</tr>';
				$i++;
				$r .= ( $i % 2 != 0 ) ? '<tr style="background-color: #f1f1f1;">' : '<tr style="background-color: #ffffff;">';
				$r .= '<td style="padding: 16px 7px;"><p style="margin: 0">' . esc_html__('Posts', 'wp-security-audit-log') . '</p></td>';
				$r .= '<td style="padding: 16px 7px;"><p style="margin: 0">' . $posts . '</p></td>';
				$r .= '</tr>';
				$i++;
				$r .= ( $i % 2 != 0 ) ? '<tr style="background-color: #f1f1f1;">' : '<tr style="background-color: #ffffff;">';
				$r .= '<td style="padding: 16px 7px;"><p style="margin: 0">' . esc_html__('Pages', 'wp-security-audit-log') . '</p></td>';
				$r .= '<td style="padding: 16px 7px;"><p style="margin: 0">' . $pages . '</p></td>';
				$r .= '</tr>';
				$i++;
				$r .= ( $i % 2 != 0 ) ? '<tr style="background-color: #f1f1f1;">' : '<tr style="background-color: #ffffff;">';
				$r .= '<td style="padding: 16px 7px;"><p style="margin: 0">' . esc_html__('Custom Post Types', 'wp-security-audit-log') . '</p></td>';
				$r .= '<td style="padding: 16px 7px;"><p style="margin: 0">' . $custom . '</p></td>';
				$r .= '</tr>';
				$i++;
				if ( ! empty( $woocommerce ) ) {
					$r .= ( $i % 2 != 0 ) ? '<tr style="background-color: #f1f1f1;">' : '<tr style="background-color: #ffffff;">';
					$r .= '<td style="padding: 16px 7px;"><p style="margin: 0">WooCommerce</p></td>';
					$r .= '<td style="padding: 16px 7px;"><p style="margin: 0">' . $woocommerce . '</p></td>';
					$r .= '</tr>';
					$i++;
				}
				fwrite( $file, $r );
			}
		}

		fwrite( $file, '</tbody></table>' );
	}

}
