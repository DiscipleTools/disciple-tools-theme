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
        $handle = fopen( __DIR__ . "/csv/jp.csv", "r" );
        if ( $handle !== false ) {
            while (( $data = fgetcsv( $handle, 0, "," ) ) !== false) {
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
        $handle = fopen( __DIR__ . "/csv/imb.csv", "r" );
        if ( $handle !== false ) {
            while (( $data = fgetcsv( $handle, 0, "," ) ) !== false) {
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
     *
     * @return array|WP_Error
     */
    public static function add_single_people_group( $rop3, $country ) {
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
        global $wpdb;
        $duplicate = $wpdb->get_var( $wpdb->prepare( "
            SELECT count(meta_id)
            FROM $wpdb->postmeta
            WHERE meta_key = 'ROP3' AND
            post_id IN ( SELECT ID FROM $wpdb->posts WHERE post_type = 'peoplegroups' ) AND
            meta_value = %s",
        $rop3 ) );
        if ( $duplicate > 0 ) {
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


        // if no duplicate, then install full people group
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

        // return success
        if ( ! is_wp_error( $post_id ) ) {
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
        <?php
    }

    /**
     * @param $search
     *
     * @return array|WP_Error
     */
    public static function get_people_groups_compact( $search ) {
        if ( !current_user_can( "access_contacts" )){
            return new WP_Error( __FUNCTION__, "You do not have permission for this", [ 'status' => 403 ] );
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
            if ($translation !== "") {
                $label = $translation;
            } else {
                $label = $post->post_title;
            }

            $list[] = [
            "ID" => $post->ID,
            "name" => $post->post_title,
            "label" => $label
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
            if ($translation !== "") {
                $label = $translation;
            } else {
                $label = $post->post_title;
            }
            $list[] = [
            "ID" => $post->ID,
            "name" => $post->post_title,
            "label" => $label
            ];
        }

        $total_found_posts = $query->found_posts + $meta_query->found_posts;

        $list = array_intersect_key($list, array_unique( array_map( function ( $el ) {
            return $el['ID'];
        }, $list ) ) );

        return [
        "total" => $total_found_posts,
        "posts" => $list
        ];
    }


}
