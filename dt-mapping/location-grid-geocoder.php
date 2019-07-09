<?php
/**
 * Location Grid Geocoder
 */

// geocodes longitude and latitude and returns json array of geoname record
if ( isset( $_GET['type'] ) && isset( $_GET['longitude'] ) && isset( $_GET['latitude'] ) ) :

    // return json grid_id result from longitude/latitude
    if ( $_GET['type'] === 'geocode' ) {

        $level = null;
        if ( isset( $_GET['level'] ) ) {
            $level = $_GET['level'];
        }
        $longitude = $_GET['longitude'];
        $latitude =  $_GET['latitude'];

        $geocoder = new Location_Grid_Geocoder();

        $response =  $geocoder->get_grid_id_by_lnglat( $longitude, $latitude, $level );

        header('Content-type: application/json');

        echo json_encode($response);

        return;
    }

    return;
endif; // html



class Location_Grid_Geocoder {

    public $geojson;
    public $con;
    public $geonames_table = 'locations_grid';
    public $mirror_source;

    public function __construct() {
        $this->geojson       = [];
        $this->mirror_source = 'https://storage.googleapis.com/location-grid-mirror/';

        $params    = json_decode( file_get_contents( "connect_params.json" ), true );
        $this->con = mysqli_connect( $params[ 'host' ], $params[ 'username' ], $params[ 'password' ], $params[ 'database' ] );
    }

    /**
     * @param      $longitude
     * @param      $latitude
     * @param null $level
     *
     * @return array|bool|null
     */
    public function get_grid_id_by_lnglat( $longitude, $latitude, $level = null ) {

        // get results
        if ( $level === 'admin5' ) { // get admin2 only
            $results = $this->query_admin5_by_lnglat( $longitude, $latitude );
        }
        else if ( $level === 'admin4' ) { // get admin2 only
            $results = $this->query_admin4_by_lnglat( $longitude, $latitude );
        }
        else if ( $level === 'admin3' ) { // get admin2 only
            $results = $this->query_admin3_by_lnglat( $longitude, $latitude );
        }
        else if ( $level === 'admin2' ) { // get admin2 only
            $results = $this->query_admin2_by_lnglat( $longitude, $latitude );
        }
        else if ( $level === 'admin1' ) { // get admin1 only
            $results = $this->query_admin1_by_lnglat( $longitude, $latitude );
        }
        else if ( $level === 'admin0' ) { // get country only
            $results = $this->query_country_by_lnglat( $longitude, $latitude );
        }
        else { // get nearest match
            $results = $this->query_admin2_by_lnglat( $longitude, $latitude ); // Query Admin2 Level first
            if ( empty( $results ) ) { // Escalate Query to Admin1 Level, if Admin2 is missing
                $results = $this->query_admin1_by_lnglat( $longitude, $latitude );
            }
        }

        // test results

        /** Test 1: Test for exact match and return results. */
        $test1 = $this->lnglat_test1( $results );
        if ( $test1 ) {
            return $test1;
        }

        /** Test 2: Point in Polygon test to find exact match */
        $test2 = $this->lnglat_test2( $results, $longitude, $latitude );
        if ( $test2 ) {
            return $test2;
        }

        /** Test 3: Nearest Perimeter Test */
        $test3 = $this->lnglat_test3( $results, $longitude, $latitude );
        if ( $test3 ) {
            return $test3;
        }

        /** Test 4 : Center Point Test */
        $test4 = $this->lnglat_test4( $results, $longitude, $latitude );
        if ( $test4 ) {
            return $test4;
        }

        return [];
    }

    /**
     * Test 1: Test for exact match and return results.
     *
     * @param $results
     *
     * @return bool
     */
    public function lnglat_test1( $results ) {
        if ( count( $results ) === 1 && ! empty( $results ) ) {
//            error_log( '1' );
            // return test 1 results
            foreach ( $results as $result ) {
                if ( ! isset( $result[ 'grid_id' ] ) ) {
                    $result = $result[ 0 ];
                }

                return $result;
            }
        }

        return false;
    }

    /**
     * Test 2: Point in Polygon test to find exact match within possible polygons.
     *
     * @param $results
     * @param $longitude
     * @param $latitude
     *
     * @return bool|array
     */
    public function lnglat_test2( $results, $longitude, $latitude ) {
        if ( count( $results ) > 1 && ! empty( $results ) ) {
//            error_log( '2' );
            foreach ( $results as $result ) {
                if ( $this->_this_grid_id( (int) $result[ 'grid_id' ], $longitude, $latitude ) ) {
                    // return test 2 results
                    if ( ! isset( $result[ 'grid_id' ] ) ) {
                        $result = $result[ 0 ];
                    }

                    return $result;
                }
            }
        }

        return false;
    }

    /**
     * Test 3: Nearest Perimeter Test
     * For rare points that fall just outside of the polygon lines on coasts. This test will find the nearest
     * longitude/latitude point from the previous list of polygons.
     *
     * @param $results
     * @param $longitude
     * @param $latitude
     *
     * @return bool
     */
    public function lnglat_test3( $results, $longitude, $latitude ) {
        if ( ! empty( $this->geojson ) && ! empty( $results ) ) {
//            error_log( '3' );
            $grid_id = $this->grid_id_from_nearest_polygon_line( $results, $longitude, $latitude );

            // return test 3 results
            foreach ( $results as $result ) {
                if ( (int) $result[ 'grid_id' ] === (int) $grid_id ) {
                    // return test 3 results
                    if ( ! isset( $result[ 'grid_id' ] ) ) {
                        $result = $result[ 0 ];
                    }

                    return $result;
                }
            }
        }

        return false;
    }

    /**
     * Test 4 : Center Point Test
     *
     * @param $results
     * @param $longitude
     * @param $latitude
     *
     * @return array|bool|null
     */
    public function lnglat_test4( $results, $longitude, $latitude ) {
        $con = $this->con;
//        error_log( '4' );
        /**
         * No bounding set results,
         * Lng/Lat is outside all boundingboxes for administrative units
         * These are often islands, etc.
         * Therefore find the nearest center point of admin1 and admin2 to this point.
         */
        $grid_id = $this->grid_id_by_nearest_centerpoint( $longitude, $latitude );
        if ( $grid_id === false ) {
            return false;
        }

        // Return
        $query  = mysqli_query( $con, "
            SELECT g.*, c.name as country_name, a1.name as admin1_name, a2.name as admin2_name, a3.name as admin3_name
            FROM {$this->geonames_table} as g
            LEFT JOIN {$this->geonames_table} as c ON g.country_grid_id=c.grid_id
            LEFT JOIN {$this->geonames_table} as a1 ON g.admin1_grid_id=a1.grid_id
            LEFT JOIN {$this->geonames_table} as a2 ON g.admin2_grid_id=a2.grid_id
            LEFT JOIN {$this->geonames_table} as a3 ON g.admin3_grid_id=a3.grid_id
            WHERE g.grid_id = {$grid_id};
        " );
        $result = mysqli_fetch_all( $query, MYSQLI_ASSOC );
        if ( $result ) {
            if ( ! isset( $result[ 'grid_id' ] ) ) {
                $result = $result[ 0 ];
            }

            return $result;
        }

        return false;
    }



    /**
     * @param $results
     * @param $longitude_x
     * @param $latitude_y
     *
     * @return bool|string
     */
    public function grid_id_from_nearest_polygon_line( $results, $longitude, $latitude ) {

        // get geoname geojson from test 2
        $geojson         = $this->geojson;
        $coordinate_list = [];

        // build flat associative array of all coordinates
        foreach ( $results as $result ) {
            $grid_id = $result[ 'grid_id' ];
            $features  = $geojson[ $grid_id ][ 'features' ];

            // handle Polygon and MultiPolygon geometries
            foreach ( $features as $feature ) {
                if ( $feature[ 'geometry' ][ 'type' ] === 'Polygon' ) {
                    foreach ( $feature[ 'geometry' ][ 'coordinates' ] as $coordinates ) { // select out the coordinate list

                        foreach ( $coordinates as $coordinate ) { // build flat associate array of $coordinates
                            $coordinate_list[ $grid_id ] = $coordinate;
                        }
                    }
                } else if ( $feature[ 'geometry' ][ 'type' ] === 'MultiPolygon' ) {
                    foreach ( $feature[ 'geometry' ][ 'coordinates' ] as $top_coordinates ) { // select out the multi polygons
                        foreach ( $top_coordinates as $coordinates ) { // select out the coordinate list

                            foreach ( $coordinates as $coordinate ) { // build flat associate array of $coordinates
                                $coordinate_list[ $grid_id ] = $coordinate;
                            }
                        }
                    }
                }
            }
        }

        // get distance between reference and all points
        $distance = [];
        foreach ( $coordinate_list as $key => $pair ) {
            $distance[ $key ] = $this->_distance( $pair[ 0 ], $pair[ 1 ], $longitude, $latitude );
        }

        asort( $distance ); // sort distances so smallest is on top
        $keys = array_keys( $distance ); // pull keys

        return $keys[ 0 ]; // return top key
    }

    /**
     * Get grid_id by matching the nearest centerpoint to provided longitude/latitude.
     *
     * @param $longitude
     * @param $latitude
     *
     * @return bool
     */
    public function grid_id_by_nearest_centerpoint( $longitude, $latitude ) {

        $con = $this->con;

        // create bounding box from longitude/latitude
        $north_latitude = ceil( $latitude ) + 1;
        $south_latitude = floor( $latitude ) - 1;
        $west_longitude = floor( $longitude ) - 1;
        $east_longitude = ceil( $longitude ) + 1;

        // calculate the nearest admin2 centerpoint.
        $query   = mysqli_query( $con, "
        SELECT grid_id, longitude, latitude
        FROM {$this->geonames_table}
        WHERE longitude < $east_longitude
        AND longitude > $west_longitude
        AND latitude < $north_latitude
        AND latitude > $south_latitude
        AND ( level = 'admin1' OR level = 'admin2' OR level = 'admin3' );
    " );
        $results = mysqli_fetch_all( $query, MYSQLI_ASSOC );

        if ( ! empty( $results ) ) {

            $distance = [];
            foreach ( $results as $result ) {
                $distance[ $result[ 'grid_id' ] ] = $this->_distance( $result[ 'longitude' ], $result[ 'latitude' ], $longitude, $latitude );
            }
            asort( $distance ); // sort distances so smallest is on top
            $keys = array_keys( $distance ); // pull keys

            return $keys[ 0 ]; // return top key
        }

        return false;
    }

    /**
     * Downloads GeoJSON polygons and parses through geometries trying to match lon/lat within the polygons
     *
     * @param $grid_id
     * @param $longitude_x
     * @param $latitude_y
     *
     * @return int|bool
     */
    public function _this_grid_id( $grid_id, $longitude_x, $latitude_y ) {

        // get geoname geojson
        // @todo potentially cache geojson to database and check first in db then call remotely.
        $raw_geojson = @file_get_contents( $this->mirror_source . $grid_id . '.geojson' );
        if ( $raw_geojson === false ) {
            return false;
        }
        $geojson                     = json_decode( $raw_geojson, true );
        $this->geojson[ $grid_id ] = $geojson; // save for 3 test if necessary
        $features                    = $geojson[ 'features' ];

        // handle Polygon and MultiPolygon geometries
        foreach ( $features as $feature ) {
            if ( $feature[ 'geometry' ][ 'type' ] === 'Polygon' ) {
                foreach ( $feature[ 'geometry' ][ 'coordinates' ] as $coordinates ) {

                    $data = $this->_split_polygon( $coordinates );

                    $vertices_x     = $data[ 'longitude' ];
                    $vertices_y     = $data[ 'latitude' ];
                    $points_polygon = count( $vertices_x );  // number vertices - zero-based array

                    if ( $this->_is_in_polygon( $points_polygon, $vertices_x, $vertices_y, $longitude_x, $latitude_y ) ) {
                        return $grid_id;
                    }
                }
            } else if ( $feature[ 'geometry' ][ 'type' ] === 'MultiPolygon' ) {
                foreach ( $feature[ 'geometry' ][ 'coordinates' ] as $top_coordinates ) {
                    foreach ( $top_coordinates as $coordinates ) {

                        $data = $this->_split_polygon( $coordinates );

                        $vertices_x     = $data[ 'longitude' ];
                        $vertices_y     = $data[ 'latitude' ];
                        $points_polygon = count( $vertices_x );  // number vertices - zero-based array

                        if ( $this->_is_in_polygon( $points_polygon, $vertices_x, $vertices_y, $longitude_x, $latitude_y ) ) {
                            return $grid_id;
                        }
                    }
                }
            }
        }

        return false;
    }

    /**
     * @param        $grid_id
     * @param string $type
     *
     * @return bool
     */
    public function _polygon_exists( $grid_id, $type = 'polygon' ) {
        if ( $type === 'polygon' ) {
            $ch = curl_init( $this->mirror_source . 'low/' . $grid_id . '.geojson' );
        } else if ( $type === 'polygon_collection' ) {
            $ch = curl_init( $this->mirror_source . 'collection/' . $grid_id . '.geojson' );
        } else {
            error_log( '_polygons_exists:: missing correct $type' );

            return false;
        }

        curl_setopt( $ch, CURLOPT_NOBODY, true );
        curl_setopt( $ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13' );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
        curl_exec( $ch );
        $retcode = curl_getinfo( $ch, CURLINFO_HTTP_CODE );

        curl_close( $ch );

        // $retcode >= 400 -> not found, $retcode = 200, found.
        if ( $retcode === 200 ) {
            return true;
        }

        return false;
    }

    /**
     * Takes a spilt list of lng/lats and compares with a single lng/lat to see if the single exists within the polygon
     *
     * @param $points_polygon
     * @param $vertices_x
     * @param $vertices_y
     * @param $longitude_x
     * @param $latitude_y
     *
     * @return bool|int
     */
    public function _is_in_polygon( $points_polygon, $vertices_x, $vertices_y, $longitude_x, $latitude_y ) {
        $i = $j = $c = 0;
        for ( $i = 0, $j = $points_polygon - 1; $i < $points_polygon; $j = $i ++ ) {
            if ( ( ( $vertices_y[ $i ] > $latitude_y != ( $vertices_y[ $j ] > $latitude_y ) ) && ( $longitude_x < ( $vertices_x[ $j ] - $vertices_x[ $i ] ) * ( $latitude_y - $vertices_y[ $i ] ) / ( $vertices_y[ $j ] - $vertices_y[ $i ] ) + $vertices_x[ $i ] ) ) ) {
                $c = ! $c;
            }
        }

        return $c;
    }

    /**
     * Takes the coordinates section of a geojson polygon and splits the lng/lat coordinates, so they can be used by _is_in_polygon
     *
     * @param array $polygon_geometry
     *
     * @return array
     */
    public function _split_polygon( array $polygon_geometry ) {
        $longitude = $latitude = $data = [];
        foreach ( $polygon_geometry as $vertices ) {
            $longitude[] = $vertices[ 0 ];
            $latitude[]  = $vertices[ 1 ];
        }
        $data = [
            'longitude' => $longitude,
            'latitude'  => $latitude
        ];

        return $data;
    }

    /**
     * @link https://stackoverflow.com/questions/9589130/find-closest-longitude-and-latitude-in-array
     *
     * @param $a
     * @param $b
     *
     * @return float
     */
    public function _distance( $lon1, $lat1, $lon2, $lat2 ) {
        $theta = $lon1 - $lon2;
        $dist  = sin( deg2rad( $lat1 ) ) * sin( deg2rad( $lat2 ) ) + cos( deg2rad( $lat1 ) ) * cos( deg2rad( $lat2 ) ) * cos( deg2rad( $theta ) );
        $dist  = acos( $dist );
        $dist  = rad2deg( $dist );
        $miles = $dist * 60 * 1.1515;

        return $miles;
    }

	public function query_admin5_by_lnglat( $longitude, $latitude ) {
		$con   = $this->con;
		$query = mysqli_query( $con, "
        SELECT g.*, c.name as country_name, a1.name as admin1_name, a2.name as admin2_name, a3.name as admin3_name, a4.name as admin4_name
        FROM {$this->geonames_table} as g
        LEFT JOIN {$this->geonames_table} as c ON g.country_grid_id=c.grid_id
        LEFT JOIN {$this->geonames_table} as a1 ON g.admin1_grid_id=a1.grid_id
        LEFT JOIN {$this->geonames_table} as a2 ON g.admin2_grid_id=a2.grid_id
        LEFT JOIN {$this->geonames_table} as a2 ON g.admin3_grid_id=a3.grid_id
        LEFT JOIN {$this->geonames_table} as a2 ON g.admin4_grid_id=a4.grid_id
        WHERE 
        g.north_latitude >= {$latitude} AND
        g.south_latitude <= {$latitude} AND
        g.west_longitude >= {$longitude} AND
        g.east_longitude <= {$longitude} AND
        g.level = 'admin5';
    " );
		if ( $query === false ) {
			return [];
		}

		return mysqli_fetch_all( $query, MYSQLI_ASSOC );
	}

	public function query_admin4_by_lnglat( $longitude, $latitude ) {
		$con   = $this->con;
		$query = mysqli_query( $con, "
        SELECT g.*, c.name as country_name, a1.name as admin1_name, a2.name as admin2_name, a3.name as admin3_name
        FROM {$this->geonames_table} as g
        LEFT JOIN {$this->geonames_table} as c ON g.country_grid_id=c.grid_id
        LEFT JOIN {$this->geonames_table} as a1 ON g.admin1_grid_id=a1.grid_id
        LEFT JOIN {$this->geonames_table} as a2 ON g.admin2_grid_id=a2.grid_id
        LEFT JOIN {$this->geonames_table} as a2 ON g.admin3_grid_id=a3.grid_id
        WHERE 
        g.north_latitude >= {$latitude} AND
        g.south_latitude <= {$latitude} AND
        g.west_longitude >= {$longitude} AND
        g.east_longitude <= {$longitude} AND
        g.level = 'admin4';
    " );
		if ( $query === false ) {
			return [];
		}

		return mysqli_fetch_all( $query, MYSQLI_ASSOC );
	}

    public function query_admin3_by_lnglat( $longitude, $latitude ) {
        $con   = $this->con;
        $query = mysqli_query( $con, "
        SELECT g.*, c.name as country_name, a1.name as admin1_name, a2.name as admin2_name
        FROM {$this->geonames_table} as g
        LEFT JOIN {$this->geonames_table} as c ON g.country_grid_id=c.grid_id
        LEFT JOIN {$this->geonames_table} as a1 ON g.admin1_grid_id=a1.grid_id
        LEFT JOIN {$this->geonames_table} as a2 ON g.admin2_grid_id=a2.grid_id
        WHERE 
        g.north_latitude >= {$latitude} AND
        g.south_latitude <= {$latitude} AND
        g.west_longitude >= {$longitude} AND
        g.east_longitude <= {$longitude} AND
        g.level = 'admin3';
    " );
        if ( $query === false ) {
            return [];
        }

        return mysqli_fetch_all( $query, MYSQLI_ASSOC );
    }

    public function query_admin2_by_lnglat( $longitude, $latitude ) {
        $con   = $this->con;
        $query = mysqli_query( $con, "
        SELECT g.*, c.name as country_name, a1.name as admin1_name
        FROM {$this->geonames_table} as g
        LEFT JOIN {$this->geonames_table} as c ON g.country_grid_id=c.grid_id
        LEFT JOIN {$this->geonames_table} as a1 ON g.admin1_grid_id=a1.grid_id
        WHERE 
        g.north_latitude >= {$latitude} AND
        g.south_latitude <= {$latitude} AND
        g.west_longitude >= {$longitude} AND
        g.east_longitude <= {$longitude} AND
        g.level = 'admin2';
    " );
        if ( $query === false ) {
            return [];
        }

        return mysqli_fetch_all( $query, MYSQLI_ASSOC );
    }

    public function query_admin1_by_lnglat( $longitude, $latitude ) {
        $con   = $this->con;
        $query = mysqli_query( $con, "
            SELECT g.*, c.name as country_name
            FROM {$this->geonames_table} as g
            LEFT JOIN {$this->geonames_table} as c ON g.country_grid_id=c.grid_id
            WHERE 
            g.north_latitude >= {$latitude} AND
            g.south_latitude <= {$latitude} AND
            g.west_longitude >= {$longitude} AND
            g.east_longitude <= {$longitude} AND
            g.level = 'admin1';
        " );
        if ( $query === false ) {
            return [];
        }

        return mysqli_fetch_all( $query, MYSQLI_ASSOC );
    }

    public function query_country_by_lnglat( $longitude, $latitude ) {
        $con   = $this->con;
        $query = mysqli_query( $con, "
            SELECT g.*
            FROM {$this->geonames_table} as g
            WHERE 
            g.north_latitude >= {$latitude} AND
            g.south_latitude <= {$latitude} AND
            g.west_longitude >= {$longitude} AND
            g.east_longitude <= {$longitude} AND
            g.level = 'country';
        " );
        if ( $query === false ) {
            return [];
        }

        return mysqli_fetch_all( $query, MYSQLI_ASSOC );
    }
}
















if ( ! function_exists( 'dt_write_log' ) ) {
    /**
     * A function to assist development only.
     * This function allows you to post a string, array, or object to the WP_DEBUG log.
     * It also prints elapsed time since the last call.
     *
     * @param $log
     */
    function dt_write_log( $log ) {
        global $dt_write_log_microtime;
        $now = microtime( true );
        if ( $dt_write_log_microtime > 0 ) {
            $elapsed_log = sprintf( "[elapsed:%5dms]", ( $now - $dt_write_log_microtime ) * 1000 );
        } else {
            $elapsed_log = "[elapsed:-------]";
        }
        $dt_write_log_microtime = $now;
        if ( is_array( $log ) || is_object( $log ) ) {
            error_log( $elapsed_log . " " . print_r( $log, true ) );
        } else {
            error_log( "$elapsed_log $log" );
        }
    }
}
