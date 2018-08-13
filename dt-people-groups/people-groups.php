<?php
/**
 * Contains create, update and delete functions for people groups, wrapping access to
 * the database
 *
 * @package  Disciple_Tools
 * @category Plugin
 * @author   Chasm.Solutions & Kingdom.Training
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
        if (( $handle = fopen( __DIR__ . "/csv/jp.csv", "r" ) ) !== false) {
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
        if (( $handle = fopen( __DIR__ . "/csv/imb.csv", "r" ) ) !== false) {
            while (( $data = fgetcsv( $handle, 0, "," ) ) !== false) {
                $imb_csv[] = $data;
            }
            fclose( $handle );
        }
        return $imb_csv;
    }

    public static function search_csv( $search ) { // gets a list by country
        $data = self::get_jp_source();
        $result = [];
        foreach ( $data as $row ) {
            if ( $row[1] === $search ) {
                $result[] = $row;
            }
        }
        return $result;
    }

    public static function get_country_dropdown() {
        $data = self::get_jp_source();
        $all_names = array_column( $data, 1 );
        $unique_names = array_unique( $all_names );
        unset( $unique_names[0] );

        return $unique_names;
    }

    public static function add_single_people_group( $rop3 ) {
        // get matching rop3 row
        $data = self::get_jp_source();
        $columns = $data[0];
        $rop3_row = '';
        foreach ( $data as $row ) {
            if ( $row[3] == $rop3 ) {
                $rop3_row = $row;
                break;
            }
        }
        if ( empty( $rop3_row ) || ! is_array( $rop3_row ) ) {
            return [
                    'status' => 'Fail',
                    'message' => 'ROP3 number not found'
            ];
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
                'message' => 'Duplicate found'
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
              'post_title' => $rop3_row[4],
              'post_type' => 'peoplegroups',
              'post_status' => 'publish'
        ];
        foreach ( $rop3_row as $key => $value ) {
            $post['meta_input'][$columns[$key]] = $value;
        }
        dt_write_log( $post );
        $post_id = wp_insert_post( $post );

        // return success
        if ( ! is_wp_error( $post_id ) ) {
            return [
                'status' => 'Success',
                'message' => 'New people group id is ' . $post_id,
            ];
        } else {
            return [
                'status' => 'Fail',
                'message' => 'Unable to insert ' . $rop3_row[4],
            ];
        }


    }


    public static function admin_tab_table() {
        $names = self::get_country_dropdown();
        ?>
        <select id="group-search">
            <?php foreach ( $names as $name ) {
                echo '<option value="'.esc_attr( $name ).'">'.esc_attr( $name ).'</option>';
} ?>
        </select>
        <button class="button" onclick="group_search()">Get List</button>
        <br><br>
        <div id="results"></div>
        <?php
    }

    /**
     * @param $search
     *
     * @return array
     */
    public static function get_people_groups_compact( $search ) {
        //        @todo check permissions
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
            $list[] = [
            "ID" => $post->ID,
            "name" => $post->post_title
            ];
        }

        return [
        "total" => $query->found_posts,
        "posts" => $list
        ];
    }


}
