<?php
/**
 * Contains create, update and delete functions for people groups, wrapping access to
 * the database
 *
 * @package  Disciple.Tools
 * @category Plugin
 * @author   Disciple.Tools
 * @since    0.1.0
 */
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly.

/**
 * Class Disciple_Tools_People_Groups
 */
class Disciple_Tools_People_Groups
{
    /**
     * Get JP csv file contents and return as array.
     * @return array
     */
    public static function get_jp_source() {
        $jp_csv = [];
        $handle = fopen( __DIR__ . '/csv/jp.csv', 'r' );
        if ( $handle !== false ) {
            while ( ( $data = fgetcsv( $handle, 0, ',' ) ) !== false ) {
                $jp_csv[] = $data;
            }
            fclose( $handle );
        }
        return $jp_csv;
    }

    /**
     * Get JP csv file contents and return as array.
     * @return array
     */
    public static function get_imb_source() {
        $imb_csv = [];
        $handle = fopen( __DIR__ . '/csv/imb.csv', 'r' );
        if ( $handle !== false ) {
            while ( ( $data = fgetcsv( $handle, 0, ',' ) ) !== false ) {
                $imb_csv[] = $data;
            }
            fclose( $handle );
        }
        return $imb_csv;
    }

    public static function search_csv( $search ) { // gets a list by country
        if ( ! current_user_can( 'manage_dt' ) ) {
            return new WP_Error( __METHOD__, 'Insufficient permissions', [] );
        }
        $data = self::get_jp_source();
        $result = [];
        foreach ( $data as $row ) {
            if ( $row[1] === $search ) {
                $row[] = ( self::duplicate_db_checker_by_rop3( $row[1], $row[3] ) > 0 );
                $result[] = $row;
            }
        }
        return $result;
    }

    public static function search_csv_by_rop3( $search ) { // gets a list by country
        if ( ! current_user_can( 'manage_dt' ) ) {
            return new WP_Error( __METHOD__, 'Insufficient permissions', [] );
        }
        $data = self::get_jp_source();
        $result = [];
        foreach ( $data as $row ) {
            if ( $row[3] === $search ) {
                $result[] = $row;
            }
        }
        return $result;
    }

    public static function duplicate_db_checker_by_rop3( $country, $rop3 ){
        global $wpdb;
        return $wpdb->get_var( $wpdb->prepare( "
            SELECT COUNT(rop3.meta_id)
            FROM $wpdb->postmeta AS rop3
            INNER JOIN $wpdb->postmeta AS country_meta ON ( country_meta.post_id = rop3.post_id AND country_meta.meta_key = 'jp_Ctry' AND ( ( country_meta.meta_value LIKE %s ) OR ( country_meta.meta_value LIKE %s ) ) )
            WHERE rop3.meta_key = 'jp_ROP3' AND
            rop3.post_id IN ( SELECT ID FROM $wpdb->posts WHERE post_type = 'peoplegroups' ) AND
            rop3.meta_value = %s", '%'. esc_sql( $country ) .'%', '%'. esc_sql( str_replace( "'", '-', $country ) ) .'%', $rop3 ) );
    }

    public static function find_post_ids_by_rop3( $rop3 ){
        global $wpdb;
        return $wpdb->get_results( $wpdb->prepare( "
            SELECT rop3.post_id
            FROM $wpdb->postmeta AS rop3
            WHERE rop3.meta_key = 'jp_ROP3' AND
            rop3.post_id IN ( SELECT ID FROM $wpdb->posts WHERE post_type = 'peoplegroups' ) AND
            rop3.meta_value = %s
            ORDER BY rop3.post_id ASC", $rop3 ), ARRAY_A );
    }

    public static function get_post_id_meta_value( $post_id, $meta_key ){
        global $wpdb;
        return $wpdb->get_var( $wpdb->prepare( "
            SELECT meta_value
            FROM $wpdb->postmeta
            WHERE meta_key = %s AND post_id = %d", esc_sql( $meta_key ), $post_id ) );
    }

    public static function update_post_id_meta_value( $post_id, $meta_key, $meta_value ){
        global $wpdb;
        return $wpdb->query( $wpdb->prepare( "
            UPDATE $wpdb->postmeta
            SET meta_value = %s
            WHERE meta_key = %s AND post_id = %d", esc_sql( $meta_value ), esc_sql( $meta_key ), $post_id ) );
    }

    public static function add_location_grid_meta( $post_type, $post_id, $grid_id ){
        if ( !empty( $post_type ) && !empty( $post_id ) && !empty( $grid_id ) ){
            $geocoder = new Location_Grid_Geocoder();
            $grid = $geocoder->query_by_grid_id( $grid_id );
            if ( $grid ) {
                $location_meta_grid = [];

                // creates the full record from the grid_id
                Location_Grid_Meta::validate_location_grid_meta( $location_meta_grid );
                $location_meta_grid['post_id'] = $post_id;
                $location_meta_grid['post_type'] = $post_type;
                $location_meta_grid['grid_id'] = $grid['grid_id'];
                $location_meta_grid['lng'] = $grid['longitude'];
                $location_meta_grid['lat'] = $grid['latitude'];
                $location_meta_grid['level'] = $grid['level_name'];
                $location_meta_grid['label'] = $grid['name'];

                return Location_Grid_Meta::add_location_grid_meta( $post_id, $location_meta_grid );
            }
        }

        return false;
    }

    public static function get_country_dropdown() {
        if ( ! current_user_can( 'manage_dt' ) ) {
            return new WP_Error( __METHOD__, 'Insufficient permissions', [] );
        }
        $data = self::get_jp_source();
        $all_names = array_column( $data, 1 );
        $unique_names = array_unique( $all_names );
        unset( $unique_names[0] );

        return $unique_names;
    }

    /**
     * Add Single People Group
     *
     * @param $rop3
     * @param $country
     * @param $location_grid
     *
     * @return array|WP_Error
     */
    public static function add_single_people_group( $rop3, $country, $location_grid ) {
        if ( ! current_user_can( 'manage_dt' ) ) {
            return new WP_Error( __METHOD__, 'Insufficient permissions', [] );
        }

        // get matching rop3 row for JP
        $data = self::get_jp_source();
        $columns = $data[0];
        $rop3_row = '';
        foreach ( $data as $row ) {
            if ( $row[3] == $rop3 && $row[1] === $country ) {
                $rop3_row = $row;
                break;
            }
        }
        if ( empty( $rop3_row ) || ! is_array( $rop3_row ) ) {
            return [
                    'status' => 'Fail',
                    'message' => 'ROP3 number not found in JP data.'
            ];
        }

        // get matching IMB data
        $imb_data = self::get_imb_source();
        $imb_columns = $imb_data[0];
        $imb_rop3_row = '';
        foreach ( $imb_data as $imb_row ) {
            if ( $imb_row[32] == $rop3 && $imb_row[5] === $country ) {
                $imb_rop3_row = $imb_row;
                break;
            }
        }


        // get current people groups
        // check for duplicate and return fail install because of duplicate.
        if ( self::duplicate_db_checker_by_rop3( $country, $rop3 ) > 0 ) {
            return [
                'status' => 'Duplicate',
                'message' => 'Duplicate found. Already installed.'
            ];
        }

        if ( ! isset( $rop3_row[4] ) ) {
            return [
                'status' => 'Fail',
                'message' => 'ROP3 title not found.',
            ];
        }

        // If no duplicates, then attempt to locate any corresponding post ids.
        $post_ids = self::find_post_ids_by_rop3( $rop3 );
        if ( !empty( $post_ids ) && count( $post_ids ) > 0 ) {

            // Merge into first record from results set.
            $post_id = $post_ids[0]['post_id'];

            // Fetch existing country and population values.
            $jp_ctry = self::get_post_id_meta_value( $post_id, 'jp_Ctry' );
            $jp_population = self::get_post_id_meta_value( $post_id, 'jp_Population' );

            // Update existing values.
            $updated_jp_ctry = !empty( $jp_ctry ) ? $jp_ctry . ', ' . $country : $country;
            $updated_jp_population = !empty( $jp_population ) ? ( intval( $jp_population ) + intval( $rop3_row[6] ) ) : intval( $jp_population );

            // Update corresponding parent record.
            self::update_post_id_meta_value( $post_id, 'jp_Ctry', str_replace( "'", '-', str_replace( '\\', '', $updated_jp_ctry ) ) );
            self::update_post_id_meta_value( $post_id, 'jp_Population', $updated_jp_population );

            // Add new location grid meta value.
            self::add_location_grid_meta( 'peoplegroups', $post_id, $location_grid );

            // Ensure post title adopts correct shape.
            $post = [
                'ID' => $post_id,
                'post_title' => sprintf( '%s (%s)', $rop3_row[4], $rop3_row[3] )
            ];
            wp_update_post( $post );

            return [
                'status' => 'Success',
                'message' => 'New people group has been added! ( <a href="' . admin_url() . 'post.php?post=' . $post_id . '&action=edit">View updated record</a> )'
            ];
        } else {

            // If no duplicate or parent record; then install full people group as new record.
            $post = [
                'post_title' => $rop3_row[4] . ' (' . $rop3_row[1] . ' | ' . $rop3_row[3] . ')',
                'post_type' => 'peoplegroups',
                'post_status' => 'publish',
                'comment_status' => 'closed',
                'ping_status' => 'closed',
            ];
            foreach ( $rop3_row as $key => $value ) {
                $post['meta_input']['jp_'.$columns[$key]] = $value;
            }
            if ( ! empty( $imb_rop3_row ) ) { // adds only if match is found
                foreach ( $imb_rop3_row as $imb_key => $imb_value ) {
                    $post['meta_input']['imb_'.$imb_columns[$imb_key]] = $imb_value;
                }
            }
            $post_id = wp_insert_post( $post );

            // Return success
            if ( ! is_wp_error( $post_id ) ) {

                // Capture corresponding location_grid_meta to above location_grid for newly created post id.
                self::add_location_grid_meta( 'peoplegroups', $post_id, $location_grid );

                return [
                    'status' => 'Success',
                    'message' => 'New people group has been added! ( <a href="'.admin_url() . 'post.php?post=' . $post_id . '&action=edit">View new record</a> )',
                ];
            } else {
                return [
                    'status' => 'Fail',
                    'message' => 'Unable to insert ' . $rop3_row[4],
                ];
            }
        }
    }

    /**
     * Bulk Add People Groups
     *
     * @param $groups
     *
     * @return array|WP_Error
     */
    public static function add_bulk_people_groups( $groups ){
        if ( !current_user_can( 'manage_dt' ) ){
            return new WP_Error( __METHOD__, 'Insufficient permissions', [] );
        }

        global $wpdb;

        $group_results = [];
        $posts_tb_values = [];
        $postmeta_tb_values = [];
        $location_grid_meta_tb_values = [];
        $posts_tb_updates_count = 0;

        // Determine sql id starting points + sql assets.
        $last_posts_tb_id = $wpdb->get_var( $wpdb->prepare( "SELECT MAX(ID) FROM `{$wpdb->posts}`" ) );
        $last_postmeta_tb_id = $wpdb->get_var( $wpdb->prepare( "SELECT MAX(meta_id) FROM `{$wpdb->postmeta}`" ) );
        $last_location_grid_meta_tb_id = $wpdb->get_var( $wpdb->prepare( "SELECT MAX(grid_meta_id) FROM `{$wpdb->dt_location_grid_meta}`" ) );

        // Load jp and imb csv data.
        $jp_data = self::get_jp_source();
        $jp_columns = $jp_data[0];
        $imb_data = self::get_imb_source();
        $imb_columns = $imb_data[0];

        // Proceed with people group installation.
        foreach ( $groups ?? [] as $group ){
            if ( !empty( $group['rop3'] ) && !empty( $group['country'] ) ){
                $rop3 = $group['rop3'];
                $country = $group['country'];
                $group_results[$rop3][$country] = $group;

                // Attempt to locate corresponding jp csv row.
                $jp_data_rop3_row = '';
                foreach ( $jp_data as $row ){
                    if ( $row[3] == $rop3 && $row[1] === $country ){
                        $jp_data_rop3_row = $row;
                        break;
                    }
                }

                // Attempt to locate corresponding imb csv row.
                $imb_data_rop3_row = '';
                foreach ( $imb_data as $row ){
                    if ( $row[32] == $rop3 && $row[5] === $country ){
                        $imb_data_rop3_row = $row;
                        break;
                    }
                }

                // Ensure a corresponding jp csv row is located.
                if ( empty( $jp_data_rop3_row ) || !is_array( $jp_data_rop3_row ) ) {
                    $group_results[$rop3][$country]['status'] = 'fail';
                    $group_results[$rop3][$country]['message'] = 'ROP3 number not found in JP data.';

                // Ensure group has no duplicates already installed.
                } elseif ( self::duplicate_db_checker_by_rop3( $country, $rop3 ) > 0 ) {
                    $group_results[$rop3][$country]['status'] = 'duplicate';
                    $group_results[$rop3][$country]['message'] = 'Duplicate found. Already installed.';

                } else {

                    // If no duplicates, then attempt to locate any corresponding post ids.
                    $post_ids = self::find_post_ids_by_rop3( $rop3 );
                    if ( !empty( $post_ids ) && count( $post_ids ) > 0 ) {

                        // Merge into first record from results set.
                        $post_id = $post_ids[0]['post_id'];

                        // Fetch existing country and population values.
                        $jp_ctry = self::get_post_id_meta_value( $post_id, 'jp_Ctry' );
                        $jp_population = self::get_post_id_meta_value( $post_id, 'jp_Population' );

                        // Update existing values.
                        $updated_jp_ctry = !empty( $jp_ctry ) ? $jp_ctry . ', ' . $country : $country;
                        $updated_jp_population = !empty( $jp_population ) ? ( intval( $jp_population ) + intval( $jp_data_rop3_row[6] ) ) : intval( $jp_population );

                        // Update corresponding parent record.
                        self::update_post_id_meta_value( $post_id, 'jp_Ctry', str_replace( "'", '-', str_replace( '\\', '', $updated_jp_ctry ) ) );
                        self::update_post_id_meta_value( $post_id, 'jp_Population', $updated_jp_population );

                        // Capture new location grid meta value.
                        if ( !empty( $jp_data_rop3_row[33] ) ) {
                            $location_grid_meta_tb_values[] = [
                                'post_type' => 'peoplegroups',
                                'post_id' => $post_id,
                                'grid_id' => $jp_data_rop3_row[33]
                            ];
                        }

                        // Ensure post title adopts correct shape.
                        $post = [
                            'ID' => $post_id,
                            'post_title' => sprintf( '%s (%s)', $jp_data_rop3_row[4], $jp_data_rop3_row[3] )
                        ];
                        wp_update_post( $post );

                        $group_results[$rop3][$country]['status'] = 'update';
                        $group_results[$rop3][$country]['message'] = 'New people group has been added! ( <a href="' . admin_url() . 'post.php?post=' . $post_id . '&action=edit">View updated record</a> )';

                        $posts_tb_updates_count++;

                    } else {

                        // Create posts table values for direct sql inserts.
                        $posts_tb_id = ++$last_posts_tb_id;
                        $posts_tb_post_author = get_current_user_id();
                        $posts_tb_post_date = gmdate( 'Y-m-d H:i:s' );
                        $posts_tb_post_date_gmt = gmdate( 'Y-m-d H:i:s' );
                        $posts_tb_post_content = '';
                        $posts_tb_post_title = str_replace( "'", '-', $jp_data_rop3_row[4] . ' (' . $jp_data_rop3_row[1] . ' | ' . $jp_data_rop3_row[3] . ')' );
                        $posts_tb_post_excerpt = '';
                        $posts_tb_post_status = 'publish';
                        $posts_tb_comment_status = 'closed';
                        $posts_tb_ping_status = 'closed';
                        $posts_tb_post_password = '';
                        $posts_tb_post_name = str_replace( "'", '-', strtolower( $jp_data_rop3_row[1] . '-' . $jp_data_rop3_row[3] ) );
                        $posts_tb_to_ping = '';
                        $posts_tb_pinged = '';
                        $posts_tb_post_modified = gmdate( 'Y-m-d H:i:s' );
                        $posts_tb_post_modified_gmt = gmdate( 'Y-m-d H:i:s' );
                        $posts_tb_post_content_filtered = '';
                        $posts_tb_post_parent = 0;
                        $posts_tb_guid = site_url( '/peoplegroups/' . $posts_tb_id . '/' );
                        $posts_tb_menu_order = 0;
                        $posts_tb_post_type = 'peoplegroups';
                        $posts_tb_post_mime_type = '';
                        $posts_tb_comment_count = 0;
                        $posts_tb_values[] = "( {$posts_tb_id},{$posts_tb_post_author},'{$posts_tb_post_date}','{$posts_tb_post_date_gmt}','{$posts_tb_post_content}','{$posts_tb_post_title}','{$posts_tb_post_excerpt}','{$posts_tb_post_status}','{$posts_tb_comment_status}','{$posts_tb_ping_status}','{$posts_tb_post_password}','{$posts_tb_post_name}','{$posts_tb_to_ping}','{$posts_tb_pinged}','{$posts_tb_post_modified}','{$posts_tb_post_modified_gmt}','{$posts_tb_post_content_filtered}',{$posts_tb_post_parent},'{$posts_tb_guid}',{$posts_tb_menu_order},'{$posts_tb_post_type}','{$posts_tb_post_mime_type}',{$posts_tb_comment_count} )";

                        // Create post_meta table values for direct sql inserts.
                        foreach ( $jp_data_rop3_row as $key => $value ){
                            $postmeta_tb_meta_id = ++$last_postmeta_tb_id;
                            $postmeta_tb_post_id = $posts_tb_id;
                            $postmeta_tb_meta_key = str_replace( "'", '-', 'jp_' . $jp_columns[$key] );
                            $postmeta_tb_meta_value = str_replace( "'", '-', $value );
                            $postmeta_tb_values[] = "( {$postmeta_tb_meta_id},{$postmeta_tb_post_id},'{$postmeta_tb_meta_key}','{$postmeta_tb_meta_value}' )";
                        }

                        if ( !empty( $imb_data_rop3_row ) ){
                            foreach ( $imb_data_rop3_row as $imb_key => $imb_value ){
                                $postmeta_tb_meta_id = ++$last_postmeta_tb_id;
                                $postmeta_tb_post_id = $posts_tb_id;
                                $postmeta_tb_meta_key = str_replace( "'", '-', 'imb_' . $imb_columns[$imb_key] );
                                $postmeta_tb_meta_value = str_replace( "'", '-', $imb_value );
                                $postmeta_tb_values[] = "( {$postmeta_tb_meta_id},{$postmeta_tb_post_id},'{$postmeta_tb_meta_key}','{$postmeta_tb_meta_value}' )";
                            }
                        }

                        // Capture location grid meta references.
                        if ( !empty( $jp_data_rop3_row[33] ) ){
                            $location_grid_meta_tb_values[] = [
                                'post_type' => 'peoplegroups',
                                'post_id' => $posts_tb_id,
                                'grid_id' => $jp_data_rop3_row[33]
                            ];
                        }

                        // Work on the assumption all will be/has been well, if this point is reached..!
                        $group_results[$rop3][$country]['status'] = 'success';
                        $group_results[$rop3][$country]['message'] = 'New people group has been added! ( <a href="' . admin_url() . 'post.php?post=' . $posts_tb_id . '&action=edit">View new record</a> )';
                    }
                }
            }
        }

        $total_groups_count = count( $groups );
        $posts_tb_insert_count = 0;
        $postmeta_tb_insert_count = 0;

        // Execute post creation sql inserts, ensure to only process all, if valid post ids have been generated.
        if ( !empty( $posts_tb_values ) ){
            $posts_tb_insert_sql = "INSERT INTO `{$wpdb->posts}` (
                ID,
                post_author,
                post_date,
                post_date_gmt,
                post_content,
                post_title,
                post_excerpt,
                post_status,
                comment_status,
                ping_status,
                post_password,
                post_name,
                to_ping,
                pinged,
                post_modified,
                post_modified_gmt,
                post_content_filtered,
                post_parent,
                guid,
                menu_order,
                post_type,
                post_mime_type,
                comment_count
            ) VALUES " . implode( ',', $posts_tb_values );

            // Insert new post records.
            // phpcs:disable
            $posts_tb_insert_count = $wpdb->query( $wpdb->prepare( $posts_tb_insert_sql ) );
            // phpcs:enable

            // Next, insert associated post meta records; assuming post creation went well
            if ( !empty( $postmeta_tb_values ) && ( $posts_tb_insert_count > 0 ) ){
                $postmeta_tb_insert_sql = "INSERT INTO `{$wpdb->postmeta}` (
                    meta_id,
                    post_id,
                    meta_key,
                    meta_value
                ) VALUES " . implode( ',', $postmeta_tb_values );

                // Insert new post meta records.
                // phpcs:disable
                $postmeta_tb_insert_count = $wpdb->query( $wpdb->prepare( $postmeta_tb_insert_sql ) );
                // phpcs:enable
            }
        }

        // Finally, insert location grid meta records; which simultaneously works across 3 tables!
        foreach ( $location_grid_meta_tb_values as $grid ) {
            self::add_location_grid_meta( $grid['post_type'], $grid['post_id'], $grid['grid_id'] );
        }

        return [
            'total_groups_count' => $total_groups_count,
            'total_groups_insert_success' => $posts_tb_insert_count,
            'total_groups_insert_updates' => $posts_tb_updates_count,
            'total_groups_insert_fail' => ( $total_groups_count - ( $posts_tb_insert_count + $posts_tb_updates_count ) ),
            'groups' => $group_results
        ];
    }

    /**
     * Fetch Bulk People Groups Import Batches
     *
     * @return array|WP_Error
     */
    public static function get_bulk_people_groups_import_batches(){
        $batches = [];

        // Load jp csv data, removing heading.
        $jp_data = self::get_jp_source();
        unset( $jp_data[0] );

        // Start populating batches response, ensuring to skip already imported people groups.
        $total_records = 0;
        foreach ( $jp_data as $row ){
            $country = $row[1];
            $rop3 = $row[3];
            if ( isset( $country, $rop3 ) && ( self::duplicate_db_checker_by_rop3( $country, $rop3 ) == 0 ) ){
                $total_records++;

                // Instantiate if need be.
                if ( !isset( $batches[$country] ) ){
                    $batches[$country] = [];
                }

                $batches[$country][] = [
                    'country' => $country,
                    'rop3' => $rop3
                ];
            }
        }

        return [
            'total_batches' => count( $batches ),
            'total_records' => $total_records,
            'batches' => $batches
        ];
    }

    /**
     * Update current people group
     *
     * @param $rop3
     * @param $country
     * @param $post_id
     *
     * @return array|WP_Error
     */
    public static function link_or_update( $rop3, $country, $post_id ) {
        if ( ! current_user_can( 'manage_dt' ) ) {
            return new WP_Error( __METHOD__, 'Insufficient permissions', [] );
        }

        // get matching rop3 row for JP
        $data = self::get_jp_source();
        $columns = $data[0];
        $rop3_row = '';
        foreach ( $data as $row ) {
            if ( $row[3] == $rop3 && $row[1] === $country ) {
                $rop3_row = $row;
                break;
            }
        }
        if ( empty( $rop3_row ) || ! is_array( $rop3_row ) ) {
            return [
                'status' => 'Fail',
                'message' => 'ROP3 number not found in JP data.'
            ];
        }

        // get matching IMB data
        $imb_data = self::get_imb_source();
        $imb_columns = $imb_data[0];
        $imb_rop3_row = '';
        foreach ( $imb_data as $imb_row ) {
            if ( $imb_row[32] == $rop3 && $imb_row[5] === $country ) {
                $imb_rop3_row = $imb_row;
                break;
            }
        }

        // remove previous metadata
        global $wpdb;
        $wpdb->delete( $wpdb->postmeta, [ 'post_id' => $post_id ] );

        // if no duplicate, then install full people group
        $post = [
            'ID' => $post_id,
            'post_status' => 'publish',
            'comment_status' => 'closed',
            'ping_status' => 'closed',
        ];
        foreach ( $rop3_row as $key => $value ) {
            $post['meta_input']['jp_'.$columns[$key]] = $value;
        }
        if ( ! empty( $imb_rop3_row ) ) { // adds only if match is found
            foreach ( $imb_rop3_row as $imb_key => $imb_value ) {
                $post['meta_input']['imb_'.$imb_columns[$imb_key]] = $imb_value;
            }
        }
        $post_id = wp_update_post( $post );

        // return success
        if ( ! is_wp_error( $post_id ) ) {
            return [
                'status' => 'Success',
                'message' => 'The current people group data has been updated with this info! <a href="">Refresh to see data</a>',
            ];
        } else {
            return [
                'status' => 'Fail',
                'message' => 'Unable to update ' . $rop3_row[4],
            ];
        }
    }

    public static function admin_tab_table() {
        $names = self::get_country_dropdown();
        ?>
        <br><br>
        <table class="widefat striped" id="import_people_group_table">
            <thead>
            <tr>
                <th colspan="1">Import People Group</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td>
                    <button class="button" id="import_all_button" onclick="import_all_people_groups()">Import All Country People Groups</button>
                    <hr><br>
                    <select id="group-search">
                        <?php foreach ( $names as $name ) :
                            echo '<option value="'.esc_attr( $name ).'">'.esc_attr( $name ).'</option>';
                        endforeach; ?>
                    </select>
                    <button class="button" id="search_button" onclick="group_search()">Get List</button>
                    <br><br>
                    <a id="add_all_groups" href="javascript:void(0)" style="display:none;">add all groups</a>
                    <script>
                        function add_all(){
                            jQuery.each(jQuery('#results button'), function(i,v){
                                console.log(v.id)
                                task(v.id);
                            })
                            function task(i) {
                                setTimeout(function() {
                                    console.log(i);
                                    jQuery('#'+i).click()
                                }, 4000 * i);
                            }
                        }
                    </script>
                    <div id="results"></div>
                </td>
            </tr>
            </tbody>
        </table>
        <?php
    }

    /**
     * @param $search
     *
     * @return array|WP_Error
     */
    public static function get_people_groups_compact( $search ) {
        if ( !current_user_can( 'access_contacts' ) ){
            return new WP_Error( __FUNCTION__, 'You do not have permission for this', [ 'status' => 403 ] );
        }
        $locale = get_user_locale();
        $query_args = [
            'post_type' => 'peoplegroups',
            'orderby'   => 'title',
            'order' => 'ASC',
            'nopaging'  => true,
            's'         => $search,
        ];
        $query = new WP_Query( $query_args );

        $list = [];
        foreach ( $query->posts as $post ) {
            $translation = get_post_meta( $post->ID, $locale, true );
            if ( $translation !== '' ) {
                $label = $translation;
            } else {
                $label = $post->post_title;
            }

            $list[] = [
            'ID' => $post->ID,
            'name' => $post->post_title,
            'label' => $label
            ];
        }
        $meta_query_args = [
            'post_type' => 'peoplegroups',
            'orderby'   => 'title',
            'order' => 'ASC',
            'nopaging'  => true,
            'meta_query' => array(
                array(
                    'key' => $locale,
                    'value' => $search,
                    'compare' => 'LIKE'
                )
            ),
        ];

        $meta_query = new WP_Query( $meta_query_args );
        foreach ( $meta_query->posts as $post ) {
            $translation = get_post_meta( $post->ID, $locale, true );
            if ( $translation !== '' ) {
                $label = $translation;
            } else {
                $label = $post->post_title;
            }
            $list[] = [
            'ID' => $post->ID,
            'name' => $post->post_title,
            'label' => $label
            ];
        }

        $total_found_posts = $query->found_posts + $meta_query->found_posts;

        $list = array_intersect_key($list, array_unique( array_map( function ( $el ) {
            return $el['ID'];
        }, $list ) ) );

        return [
        'total' => $total_found_posts,
        'posts' => $list
        ];
    }
}
