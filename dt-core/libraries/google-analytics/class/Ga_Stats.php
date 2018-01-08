<?php

/**
 * Ga_Stats class
 *
 * Preparing request and parsing response from Google Analytics Reporting Api
 *
 * @author wle@adips.com
 * @version 1.0
 */
class Ga_Stats {

	/**
	 * Preparing query to get Analytics data
	 *
	 * @param string $query query type
	 * @param int $id_view The Analytics view ID from which to retrieve data.
	 * @param string $date_range The start date for the query in the format YYYY-MM-DD or '7daysAgo'
	 * @param string $metric A metric expression
	 *
	 * @return array Request query
	 */
	public static function get_query( $query, $id_view, $date_range = null, $metric = null ) {
		if ( $query == 'main_chart' ) {
			return self::main_chart_query( $id_view, $date_range, $metric );
		} elseif ( $query == 'boxes' ) {
			return self::boxes_query( $id_view );
		} elseif ( $query == 'dashboard_boxes' ) {
			return self::dashboard_boxes_query( $id_view, $date_range );
		} elseif ( $query == 'sources' ) {
			return self::sources_query( $id_view );
		} elseif ( $query == 'report' ) {
			return self::report_query( $id_view, $date_range );
		} else {
			return array();
		}
	}

	/**
	 * Preparing query for top traffic sources table
	 *
	 * @param int $id_view The Analytics view ID from which to retrieve data.
	 *
	 * @return array Sources query
	 */
	public static function sources_query( $id_view ) {
		$reports_requests	 = array();
		$reports_requests[]	 = array(
			'viewId'			 => $id_view,
			'dateRanges'		 => self::set_date_ranges( '7daysAgo', 'yesterday' ),
			'metrics'			 => self::set_metrics( array( 'ga:pageviews' ) ),
			'includeEmptyRows'	 => true,
			'pageSize'			 => 5,
			'dimensions'		 => self::set_dimensions( 'ga:sourceMedium' ),
			'orderBys'			 => self::set_order_bys( 'ga:pageviews', 'DESCENDING' ),
		);
		$query				 = array(
			'reportRequests' => $reports_requests
		);

		return $query;
	}

	/**
	 * Preparing query for dashbord boxes
	 *
	 * @param int $id_view The Analytics view ID from which to retrieve data.
	 * @param string $date_range The start date for the query in the format YYYY-MM-DD or '7daysAgo'
	 *
	 * @return array Dashboard boxes query
	 */
	public static function dashboard_boxes_query( $id_view, $date_range ) {
		$reports_requests	 = array();
		$reports_requests[]	 = array(
			'viewId'			 => $id_view,
			'dateRanges'		 => self::set_date_ranges( $date_range, 'yesterday' ),
			'metrics'			 => self::set_metrics( array(
				'ga:sessions',
				'ga:pageviews',
				'ga:pageviewsPerSession',
				'ga:BounceRate',
				'ga:avgTimeOnPage',
				'ga:percentNewSessions',
			) ),
			'includeEmptyRows'	 => true,
			'dimensions'		 => self::set_dimensions( 'ga:date' )
		);
		$query				 = array(
			'reportRequests' => $reports_requests
		);

		return $query;
	}

	/**
	 * Preparing query for stats boxes
	 *
	 * @param int $id_view The Analytics view ID from which to retrieve data.
	 *
	 * @return array Boxes query
	 */
	public static function boxes_query( $id_view ) {
		$reports_requests	 = array();
		$reports_requests[]	 = array(
			'viewId'			 => $id_view,
			'dateRanges'		 => self::set_date_ranges( '7daysAgo', 'yesterday', '14daysAgo', '8daysAgo' ),
			'metrics'			 => self::set_metrics( array(
				'ga:users',
				'ga:pageviews',
				'ga:pageviewsPerSession',
				'ga:BounceRate'
			) ),
			'includeEmptyRows'	 => true,
			'dimensions'		 => self::set_dimensions( 'ga:date' )
		);
		$query				 = array(
			'reportRequests' => $reports_requests
		);

		return $query;
	}

	/**
	 * Preparing query for chart
	 *
	 * @param int $id_view The Analytics view ID from which to retrieve data.
	 * @param string $date_range The start date for the query in the format YYYY-MM-DD or '7daysAgo'
	 * @param string $metric A metric expression
	 *
	 * @return array Chart query
	 */
	public static function main_chart_query( $id_view, $date_range = null, $metric = null ) {
		if ( empty( $date_range ) ) {
			$date_ranges = self::set_date_ranges( '7daysAgo', 'yesterday', '14daysAgo', '8daysAgo' );
		} else {
			$date_ranges = self::set_date_ranges( $date_range, 'yesterday' );
		}

		if ( empty( $metric ) ) {
			$metric = 'ga:pageviews';
		} else {
			$metric = 'ga:' . $metric;
		}

		$reports_requests	 = array();
		$reports_requests[]	 = array(
			'viewId'			 => $id_view,
			'dateRanges'		 => $date_ranges,
			'metrics'			 => self::set_metrics( $metric ),
			'includeEmptyRows'	 => true,
			'dimensions'		 => self::set_dimensions( 'ga:date' )
		);
		$query				 = array(
			'reportRequests' => $reports_requests
		);

		return $query;
	}

	public static function report_query($id_view, $last_saved_date = null, $metric = null){
		if (!$last_saved_date){
			$last_saved_date = 'yesterday';
		}
		$date_ranges = self::set_date_ranges( $last_saved_date, 'yesterday' );

		if ( empty( $metric ) ) {
			$metric = 'ga:users';
		} else {
			$metric = 'ga:' . $metric;
		}
		$reports_requests	 = array();
		$reports_requests[]	 = array(
			'viewId'			 => $id_view,
			'dateRanges'		 => $date_ranges,
			'metrics'			 => self::set_metrics( $metric ),
			'includeEmptyRows'	 => true,
			'dimensions'		 => self::set_dimensions( 'ga:date' )
		);
		$query				 = array(
			'reportRequests' => $reports_requests
		);

		return $query;

	}

	/**
	 * Setting order for requests
	 *
	 * @param string $name The field which to sort by. The default sort order is ascending. Example: ga:browser.
	 * @param string $sort The sorting order for the field. 'ASCENDING' or 'DESCENDING'
	 *
	 * @return array OrderBys
	 */
	public static function set_order_bys( $name, $sort ) {
		$order	 = array();
		$order[] = array(
			'fieldName'	 => $name,
			'sortOrder'	 => $sort,
		);

		return $order;
	}

	/**
	 * Setting metrics for requests
	 *
	 * @param mixed $expression A metric expression or array of expressions
	 *
	 * @return array Metrics
	 */
	public static function set_metrics( $expression ) {
		$metrics = array();
		if ( is_array( $expression ) ) {
			foreach ( $expression as $exp ) {
				$metrics[] = array(
					'expression' => $exp
				);
			}
		} else {
			$metrics[] = array(
				'expression' => $expression
			);
		}

		return $metrics;
	}

	/**
	 * Setting dimensions for requests
	 *
	 * @param string $name Name of the dimension to fetch, for example ga:browser.
	 *
	 * @return array Dimensions
	 */
	public static function set_dimensions( $name ) {
		$dimensions		 = array();
		$dimensions[]	 = array(
			'name' => $name
		);

		return $dimensions;
	}

	/**
	 * Setting date ranges for requests
	 *
	 * @param string $start_date The start date for the query in the format YYYY-MM-DD.
	 * @param string $end_date The end date for the query in the format YYYY-MM-DD.
	 * @param string $prev_start_date The start date (second range) for the query in the format YYYY-MM-DD.
	 * @param string $prev_end_date The start date (second range) for the query in the format YYYY-MM-DD.
	 *
	 * @return array Date ranges
	 */
	public static function set_date_ranges( $start_date, $end_date, $prev_start_date = '', $prev_end_date = '' ) {
		$date_danges	 = array();
		$date_danges[]	 = array(
			'startDate'	 => $start_date,
			'endDate'	 => $end_date
		);
		if ( !empty( $prev_start_date ) and ! empty( $prev_end_date ) ) {
			$date_danges[] = array(
				'startDate'	 => $prev_start_date,
				'endDate'	 => $prev_end_date
			);
		}

		return $date_danges;
	}

	/**
	 * Preparing response for data received from analytics
	 *
	 * @param array $data Analytics response
	 *
	 * @return array Response rows
	 */
	public static function prepare_response( $data ) {
		$data		 = self::get_reports_from_response( $data );
		self::handle_more_reports( $data );
		$report		 = self::get_single_report( $data );
		self::get_report_column_header( $report );
		$report_data = self::get_report_data( $report );
		self::get_totals( $report_data );
		self::get_row_count( $report_data );
		$rows		 = self::get_rows( $report_data );

		return $rows;
	}

	/**
	 * Get dimensions from response row
	 *
	 * @param array $row Analytics response row
	 *
	 * @return array Dimensions
	 */
	public static function get_dimensions( $row ) {
		if ( !empty( $row[ 'dimensions' ] ) ) {
			return $row[ 'dimensions' ];
		}

		return false;
	}

	/**
	 * Get metrics from response row
	 *
	 * @param array $row Analytics response row
	 *
	 * @return array Metrics
	 */
	public static function get_metrics( $row ) {
		if ( !empty( $row[ 'metrics' ] ) ) {
			return $row[ 'metrics' ];
		}

		return false;
	}

	/**
	 * Get row from response report data
	 *
	 * @param array $report_data Analytics response report data
	 *
	 * @return array Rows
	 */
	public static function get_rows( $report_data ) {
		if ( !empty( $report_data[ 'rows' ] ) ) {
			return $report_data[ 'rows' ];
		}

		return false;
	}

	/**
	 * Get row count from response report data
	 *
	 * @param array $report_data Analytics response report data
	 *
	 * @return array Row count
	 */
	public static function get_row_count( $report_data ) {
		if ( !empty( $report_data[ 'rowCount' ] ) ) {
			return $report_data[ 'rowCount' ];
		}

		return false;
	}

	/**
	 * Get totals from response report data
	 *
	 * @param array $report_data Analytics response report data
	 *
	 * @return array Totals
	 */
	public static function get_totals( $report_data ) {
		if ( !empty( $report_data[ 'totals' ] ) ) {
			return $report_data[ 'totals' ];
		}

		return false;
	}

	/**
	 * Get reports from response data
	 *
	 * @param array $data Analytics response data
	 *
	 * @return array Reports
	 */
	public static function get_reports_from_response( $data ) {
		if ( !empty( $data[ 'reports' ] ) ) {
			return $data[ 'reports' ];
		}

		return false;
	}

	/**
	 * Show info for multiple data
	 *
	 * @param array $data Analytics response data
	 *
	 */
	public static function handle_more_reports( $data ) {
		if ( count( $data ) > 1 ) {
			echo 'more than one report';
		}
	}

	/**
	 * Show info for multiple rows
	 *
	 * @param array $rows Analytics response rows
	 *
	 */
	public static function handle_more_rows( $rows ) {
		if ( count( $rows ) > 1 ) {
			echo 'more than one row';
		}
	}

	/**
	 * Get single report from response data
	 *
	 * @param array $data Analytics response data
	 *
	 * @return array Report
	 */
	public static function get_single_report( $data ) {
		if ( !empty( $data ) ) {
			foreach ( $data as $report ) {
				if ( !empty( $report ) ) {
					return $report;
				}
			}
		}

		return false;
	}

	/**
	 * Get single row from response data rows
	 *
	 * @param array $rows Analytics response data rows
	 *
	 * @return array Row
	 */
	public static function get_single_row( $rows ) {
		if ( !empty( $rows ) ) {
			foreach ( $rows as $row ) {
				if ( !empty( $row ) ) {
					return $row;
				}
			}
		}

		return false;
	}

	/**
	 * Get column header from response data
	 *
	 * @param array $data Analytics response data
	 *
	 * @return array Column header
	 */
	public static function get_report_column_header( $data ) {
		if ( !empty( $data[ 'columnHeader' ] ) ) {
			return $data[ 'columnHeader' ];
		}

		return false;
	}

	/**
	 * Get report data from response data
	 *
	 * @param array $data Analytics response data
	 *
	 * @return array data
	 */
	public static function get_report_data( $data ) {
		if ( !empty( $data[ 'data' ] ) ) {
			return $data[ 'data' ];
		}

		return false;
	}

	/**
	 * Get chart from response data
	 *
	 * @param array $response_data Analytics response data
	 *
	 * @return array chart data
	 */
	public static function get_chart( $response_data ) {
		$chart_data = array();
		if ( !empty( $response_data ) ) {
			$data	 = (!empty( $response_data[ 'reports' ] ) && !empty( $response_data[ 'reports' ][ 0 ] ) && !empty( $response_data[ 'reports' ][ 0 ][ 'data' ] ) ) ? $response_data[ 'reports' ][ 0 ][ 'data' ] : array();
			$rows	 = (!empty( $data[ 'rows' ] ) ) ? $data[ 'rows' ] : array();
			if ( !empty( $rows ) ) {
				foreach ( $rows as $key => $row ) {
					if ( $key < 7 ) {
						$chart_data[ $key ][ 'previous' ]		 = !empty( $row[ 'metrics' ][ 1 ][ 'values' ][ 0 ] ) ? $row[ 'metrics' ][ 1 ][ 'values' ][ 0 ] : 0;
						$chart_data[ $key ][ 'previous-day' ]	 = date( 'M j', strtotime( $row[ 'dimensions' ][ 0 ] ) );
					} else {
						$chart_data[ $key - 7 ][ 'day' ]		 = date( 'M j', strtotime( $row[ 'dimensions' ][ 0 ] ) );
						$chart_data[ $key - 7 ][ 'current' ]	 = !empty( $row[ 'metrics' ][ 0 ][ 'values' ][ 0 ] ) ? $row[ 'metrics' ][ 0 ][ 'values' ][ 0 ] : 0;
						$chart_data[ 'date' ]					 = strtotime( $row[ 'dimensions' ][ 0 ] );
					}
				}
			}
		}

		return $chart_data;
	}

	/**
	 * Get report from response data
	 *
	 * @param array $response_data Analytics response data
	 *
	 * @return array report data
	 */
	public static function get_report( $response_data ) {
		$report_data = array();
		if ( !empty( $response_data ) ) {
			$data	 = (!empty( $response_data[ 'reports' ] ) && !empty( $response_data[ 'reports' ][ 0 ] ) && !empty( $response_data[ 'reports' ][ 0 ][ 'data' ] ) ) ? $response_data[ 'reports' ][ 0 ][ 'data' ] : array();
			$rows	 = (!empty( $data[ 'rows' ] ) ) ? $data[ 'rows' ] : array();
			if ( !empty( $rows ) ) {
				foreach ( $rows as $key => $row ) {
					$report_data[ $key ][ 'day' ]		= date( 'M j', strtotime( $row[ 'dimensions' ][ 0 ] ) );
					$report_data[ $key ][ 'value' ]	 	= !empty( $row[ 'metrics' ][ 0 ][ 'values' ][ 0 ] ) ? $row[ 'metrics' ][ 0 ][ 'values' ][ 0 ] : 0;
					$report_data[ $key ][ 'date' ]		= strtotime( $row[ 'dimensions' ][ 0 ] );
				}
			}
		}

		return $report_data;
	}



	/**
	 * Get dasboard chart from response data
	 *
	 * @param array $response_data Analytics response data
	 *
	 * @return array dashboard chart data
	 */
	public static function get_dashboard_chart( $response_data ) {
		$chart_data = array();
		if ( !empty( $response_data ) ) {
			$data	 = (!empty( $response_data[ 'reports' ] ) && !empty( $response_data[ 'reports' ][ 0 ] ) && !empty( $response_data[ 'reports' ][ 0 ][ 'data' ] ) ) ? $response_data[ 'reports' ][ 0 ][ 'data' ] : array();
			$rows	 = (!empty( $data[ 'rows' ] ) ) ? $data[ 'rows' ] : array();
			if ( !empty( $rows ) ) {
				foreach ( $rows as $row ) {
					$chart_data[] = array(
						'day'		 => date( 'M j', strtotime( $row[ 'dimensions' ][ 0 ] ) ),
						'current'	 => !empty( $row[ 'metrics' ][ 0 ][ 'values' ][ 0 ] ) ? $row[ 'metrics' ][ 0 ][ 'values' ][ 0 ] : 0
					);
				}
			}
		}

		return $chart_data;
	}

	/**
	 * Get boxes from response data
	 *
	 * @param array $data Analytics response data
	 *
	 * @return array boxes data
	 */
	public static function get_boxes( $data ) {
		if ( !empty( $data ) ) {
			$data		 = self::get_reports_from_response( $data );
			self::handle_more_reports( $data );
			$report		 = self::get_single_report( $data );
			self::get_report_column_header( $report );
			$report_data = self::get_report_data( $report );
			$totals		 = self::get_totals( $report_data );

			return self::get_boxes_from_totals( $totals );
		}
	}

	/**
	 * Get boxes from totals
	 *
	 * @param array $totals Analytics response totals
	 *
	 * @return array boxes data
	 */
	public static function get_boxes_from_totals( $totals ) {
		if ( !empty( $totals ) ) {
			$boxes_data = array();
			foreach ( $totals as $key => $total ) {
				if ( $key == 0 ) {
					$boxes_data[ 'Users' ][ 'current' ]					 = $total[ 'values' ][ 0 ];
					$boxes_data[ 'Pageviews' ][ 'current' ]				 = $total[ 'values' ][ 1 ];
					$boxes_data[ 'PageviewsPerSession' ][ 'current' ]	 = $total[ 'values' ][ 2 ];
					$boxes_data[ 'BounceRate' ][ 'current' ]			 = round( $total[ 'values' ][ 3 ], 2 );
				} else {
					$boxes_data[ 'Users' ][ 'previous' ]				 = $total[ 'values' ][ 0 ];
					$boxes_data[ 'Pageviews' ][ 'previous' ]			 = $total[ 'values' ][ 1 ];
					$boxes_data[ 'PageviewsPerSession' ][ 'previous' ]	 = $total[ 'values' ][ 2 ];
					$boxes_data[ 'BounceRate' ][ 'previous' ]			 = round( $total[ 'values' ][ 3 ], 2 );
				}
			}

			return self::prepare_boxes( $boxes_data );
		}

		return false;
	}

	/**
	 * Prepare boxes data
	 *
	 * @param array $boxes_data Boxes data
	 *
	 * @return array boxes data
	 */
	public static function prepare_boxes( $boxes_data ) {
		$boxes_data[ 'Users' ][ 'diff' ]					 = ( $boxes_data[ 'Users' ][ 'previous' ] > 0 ) ? round( ( $boxes_data[ 'Users' ][ 'current' ] - $boxes_data[ 'Users' ][ 'previous' ] ) / $boxes_data[ 'Users' ][ 'previous' ] * 100, 2 ) : 100;
		$boxes_data[ 'Pageviews' ][ 'diff' ]				 = ( $boxes_data[ 'Pageviews' ][ 'previous' ] > 0 ) ? round( ( $boxes_data[ 'Pageviews' ][ 'current' ] - $boxes_data[ 'Pageviews' ][ 'previous' ] ) / $boxes_data[ 'Pageviews' ][ 'previous' ] * 100, 2 ) : 100;
		$boxes_data[ 'PageviewsPerSession' ][ 'diff' ]		 = ( $boxes_data[ 'PageviewsPerSession' ][ 'previous' ] > 0 ) ? round( ( $boxes_data[ 'PageviewsPerSession' ][ 'current' ] - $boxes_data[ 'PageviewsPerSession' ][ 'previous' ] ) / $boxes_data[ 'PageviewsPerSession' ][ 'previous' ] * 100, 2 ) : 100;
		$boxes_data[ 'BounceRate' ][ 'diff' ]				 = ( $boxes_data[ 'BounceRate' ][ 'previous' ] > 0 ) ? round( ( $boxes_data[ 'BounceRate' ][ 'current' ] - $boxes_data[ 'BounceRate' ][ 'previous' ] ) / $boxes_data[ 'BounceRate' ][ 'previous' ] * 100, 2 ) : 100;
		$boxes_data[ 'Users' ][ 'diff' ]					 = ( $boxes_data[ 'Users' ][ 'previous' ] == 0 && $boxes_data[ 'Users' ][ 'current' ] == 0 ) ? 0 : $boxes_data[ 'Users' ][ 'diff' ];
		$boxes_data[ 'Pageviews' ][ 'diff' ]				 = ( $boxes_data[ 'Pageviews' ][ 'previous' ] == 0 && $boxes_data[ 'Pageviews' ][ 'current' ] == 0 ) ? 0 : $boxes_data[ 'Pageviews' ][ 'diff' ];
		$boxes_data[ 'PageviewsPerSession' ][ 'diff' ]		 = ( $boxes_data[ 'PageviewsPerSession' ][ 'previous' ] == 0 && $boxes_data[ 'PageviewsPerSession' ][ 'current' ] == 0 ) ? 0 : $boxes_data[ 'PageviewsPerSession' ][ 'diff' ];
		$boxes_data[ 'BounceRate' ][ 'diff' ]				 = ( $boxes_data[ 'BounceRate' ][ 'previous' ] == 0 && $boxes_data[ 'BounceRate' ][ 'current' ] == 0 ) ? 0 : $boxes_data[ 'BounceRate' ][ 'diff' ];
		$boxes_data[ 'Users' ][ 'label' ]					 = 'Users';
		$boxes_data[ 'Pageviews' ][ 'label' ]				 = 'Pageviews';
		$boxes_data[ 'PageviewsPerSession' ][ 'label' ]		 = 'Pages / Session';
		$boxes_data[ 'BounceRate' ][ 'label' ]				 = 'Bounce Rate';
		$boxes_data[ 'Users' ][ 'comparison' ]				 = $boxes_data[ 'Users' ][ 'current' ] . ' vs ' . $boxes_data[ 'Users' ][ 'previous' ];
		$boxes_data[ 'Pageviews' ][ 'comparison' ]			 = $boxes_data[ 'Pageviews' ][ 'current' ] . ' vs ' . $boxes_data[ 'Pageviews' ][ 'previous' ];
		$boxes_data[ 'PageviewsPerSession' ][ 'comparison' ] = self::number_format_clean( $boxes_data[ 'PageviewsPerSession' ][ 'current' ], 2, '.', ',' ) . ' vs ' . self::number_format_clean( $boxes_data[ 'PageviewsPerSession' ][ 'previous' ], 2, '.', ',' );
		$boxes_data[ 'BounceRate' ][ 'comparison' ]			 = self::number_format_clean( $boxes_data[ 'BounceRate' ][ 'current' ], 2, '.', ',' ) . '% vs ' . self::number_format_clean( $boxes_data[ 'BounceRate' ][ 'previous' ], 2, '.', ',' ) . '%';
		$boxes_data[ 'Users' ][ 'color' ]					 = ( $boxes_data[ 'Users' ][ 'diff' ] > 0 ) ? 'green' : 'red';
		$boxes_data[ 'Pageviews' ][ 'color' ]				 = ( $boxes_data[ 'Pageviews' ][ 'diff' ] > 0 ) ? 'green' : 'red';
		$boxes_data[ 'PageviewsPerSession' ][ 'color' ]		 = ( $boxes_data[ 'PageviewsPerSession' ][ 'diff' ] > 0 ) ? 'green' : 'red';
		$boxes_data[ 'BounceRate' ][ 'color' ]				 = ( $boxes_data[ 'BounceRate' ][ 'diff' ] > 0 ) ? 'red' : 'green';
		$boxes_data[ 'Users' ][ 'color' ]					 = ( $boxes_data[ 'Users' ][ 'diff' ] != 0 ) ? $boxes_data[ 'Users' ][ 'color' ] : 'black';
		$boxes_data[ 'Pageviews' ][ 'color' ]				 = ( $boxes_data[ 'Pageviews' ][ 'diff' ] != 0 ) ? $boxes_data[ 'Pageviews' ][ 'color' ] : 'black';
		$boxes_data[ 'PageviewsPerSession' ][ 'color' ]		 = ( $boxes_data[ 'PageviewsPerSession' ][ 'diff' ] != 0 ) ? $boxes_data[ 'PageviewsPerSession' ][ 'color' ] : 'black';
		$boxes_data[ 'BounceRate' ][ 'color' ]				 = ( $boxes_data[ 'BounceRate' ][ 'diff' ] != 0 ) ? $boxes_data[ 'BounceRate' ][ 'color' ] : 'black';

		return $boxes_data;
	}

	/**
	 * Number format for boxes
	 *
	 * @param float $number Number to format
	 * @param int $precision Precision
	 * @param string $dec_point Decimal point
	 * @param string $thousands_sep Thousands Separator
	 *
	 * @return string clean number format
	 */
	public static function number_format_clean( $number, $precision = 0, $dec_point = '.', $thousands_sep = ',' ) {
		if ( $number == 0 ) {
			return 0;
		} else {
			$format = number_format( $number, $precision, $dec_point, $thousands_sep );
			if ( substr( $format, 2 ) == '.00' ) {
				return substr( $format, 0, - 3 );
			}

			return $format;
		}
	}

	/**
	 * Get sources from analytics response data
	 *
	 * @param array $data Analytics response data
	 *
	 * @return array sources data
	 */
	public static function get_sources( $data ) {
		if ( !empty( $data ) ) {
			$data		 = self::get_reports_from_response( $data );
			self::handle_more_reports( $data );
			$report		 = self::get_single_report( $data );
			self::get_report_column_header( $report );
			$report_data = self::get_report_data( $report );
			$rows		 = self::get_rows( $report_data );
			$totals		 = self::get_totals( $report_data );
			$totalCount	 = array();
			if ( !empty( $totals ) ) {
				foreach ( $totals as $key => $total ) {
					$totalCount = $total[ 'values' ][ 0 ];
				}
			}
			$sources = array(
				'total'	 => $totalCount,
				'sum'	 => 0,
				'rows'	 => array(),
			);
			if ( !empty( $rows ) ) {
				$i = 1;
				foreach ( $rows as $row ) {
					if ( !empty( $row ) ) {
						foreach ( $row as $key => $value ) {
							if ( $key == 'dimensions' ) {
								$sources[ 'rows' ][ $i ][ 'name' ]	 = $value[ 0 ];
								$sources[ 'rows' ][ $i ][ 'url' ]	 = 'http://' . substr( $value[ 0 ], 0, strpos( $value[ 0 ], '/' ) - 1 );
							} elseif ( $key == 'metrics' ) {
								$sources[ 'rows' ][ $i ][ 'number' ]	 = $value[ 0 ][ 'values' ][ 0 ];
								$sources[ 'rows' ][ $i ][ 'percent' ]	 = (!empty( $totalCount ) ) ? round( $value[ 0 ][ 'values' ][ 0 ] / $totalCount * 100, 2 ) : 0;
								$sources[ 'sum' ] += $value[ 0 ][ 'values' ][ 0 ];
							}
						}
						$i ++;
					}
				}
			}

			return $sources;
		}

		return false;
	}

	/**
	 * Get dashboard boxes data from analytics response data
	 *
	 * @param array $data Analytics response data
	 *
	 * @return array dashboard boxes data
	 */
	public static function get_dashboard_boxes_data( $data ) {
		if ( !empty( $data ) ) {
			$data								 = self::get_reports_from_response( $data );
			self::handle_more_reports( $data );
			$report								 = self::get_single_report( $data );
			self::get_report_column_header( $report );
			$report_data						 = self::get_report_data( $report );
			$totals								 = self::get_totals( $report_data );
			$boxes_data							 = array();
			$boxes_data[ 'Sessions' ]				 = array(
				'label'	 => 'Visits',
				'value'	 => $totals[ 0 ][ 'values' ][ 0 ],
			);
			$boxes_data[ 'Pageviews' ]			 = array(
				'label'	 => 'Pageviews',
				'value'	 => $totals[ 0 ][ 'values' ][ 1 ],
			);
			$boxes_data[ 'pageviewsPerSession' ]	 = array(
				'label'	 => 'Pages / Visit',
				'value'	 => self::number_format_clean( $totals[ 0 ][ 'values' ][ 2 ], 2, '.', ',' ),
			);
			$boxes_data[ 'BounceRate' ]			 = array(
				'label'	 => 'Bounce Rate',
				'value'	 => self::number_format_clean( $totals[ 0 ][ 'values' ][ 3 ], 2, '.', ',' ) . '%',
			);
			$boxes_data[ 'avgTimeOnPage' ]		 = array(
				'label'	 => 'Avg. Time on Site',
				'value'	 => gmdate( "H:i:s", $totals[ 0 ][ 'values' ][ 4 ] ),
			);
			$boxes_data[ 'percentNewSessions' ]	 = array(
				'label'	 => '% of New Visits',
				'value'	 => self::number_format_clean( $totals[ 0 ][ 'values' ][ 5 ], 2, '.', ',' ),
			);

			return $boxes_data;
		}
	}

}
