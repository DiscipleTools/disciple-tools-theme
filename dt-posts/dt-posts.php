<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

class DT_Posts extends Disciple_Tools_Posts {
    public function __construct() {
        parent::__construct();
    }

    /**
     * Specifies which HTML tags are permissible in comments.
     */
    private static $allowable_comment_tags = array(
        'a' => array(
          'href' => array(),
          'title' => array()
        ),
        'br' => array(),
        'em' => array(),
        'strong' => array(),
    );

    public static function get_post_types(){
        return apply_filters( 'dt_registered_post_types', [] );
    }

    /**
     * Get settings on the post type
     *
     * @param string $post_type
     *
     * @return array|WP_Error
     */
    public static function get_post_settings( string $post_type ){
        $cached = wp_cache_get( $post_type . "_post_type_settings" );
        if ( $cached ){
            return $cached;
        }
        $settings = [];
        $settings["tiles"] = self::get_post_tiles( $post_type );
        $settings = apply_filters( "dt_get_post_type_settings", $settings, $post_type );
        wp_cache_set( $post_type . "_post_type_settings", $settings );
        return $settings;
    }

    /**
     * CRUD
     */

    /**
     * Create a post
     * For fields format See https://github.com/DiscipleTools/disciple-tools-theme/wiki/Contact-Fields-Format
     *
     * @param string $post_type
     * @param array $fields
     * @param bool $silent
     * @param bool $check_permissions
     *
     * @return array|WP_Error
     */
    public static function create_post( string $post_type, array $fields, bool $silent = false, bool $check_permissions = true ){
        if ( $check_permissions && !self::can_create( $post_type ) ){
            return new WP_Error( __FUNCTION__, "You do not have permission for this", [ 'status' => 403 ] );
        }
        $initial_fields = $fields;
        $post_settings = self::get_post_settings( $post_type );

        //check to see if we want to create this contact.
        //could be used to check for duplicates first
        $continue = apply_filters( "dt_create_post_check_proceed", true, $fields );
        if ( !$continue ){
            return new WP_Error( __FUNCTION__, "Could not create this post. Maybe it already exists", [ 'status' => 409 ] );
        }

        //get extra fields and defaults
        $fields = apply_filters( "dt_post_create_fields", $fields, $post_type );

        //set title
        if ( !isset( $fields["title"] ) && !isset( $fields["name"] ) ) {
            return new WP_Error( __FUNCTION__, "title needed", [ 'fields' => $fields ] );
        }
        $title = null;
        if ( isset( $fields["title"] ) ){
            $title = $fields["title"];
            unset( $fields["title"] );
        }
        if ( isset( $fields["name"] ) ){
            $title = $fields["name"];
            unset( $fields["name"] );
        }

        $post_date = null;
        if ( isset( $fields["post_date"] )){
            $post_date = $fields["post_date"];
            unset( $fields["post_date"] );
        }
        $initial_comment = null;
        if ( isset( $fields["initial_comment"] ) ) {
            $initial_comment = $fields["initial_comment"];
            unset( $fields["initial_comment"] );
        }
        $notes = null;
        if ( isset( $fields["notes"] ) ) {
            if ( is_array( $fields["notes"] ) ) {
                $notes = $fields["notes"];
                unset( $fields["notes"] );
            } else {
                return new WP_Error( __FUNCTION__, "'notes' field expected to be an array" );
            }
        }

        $allowed_fields = apply_filters( "dt_post_create_allow_fields", [], $post_type );
        $bad_fields = self::check_for_invalid_post_fields( $post_settings, $fields, $allowed_fields );
        if ( !empty( $bad_fields ) ) {
            return new WP_Error( __FUNCTION__, "One or more fields do not exist", [
                'bad_fields' => $bad_fields,
                'status' => 400
            ] );
        }

        if ( !isset( $fields["last_modified"] ) ){
            $fields["last_modified"] = time();
        }

        $contact_methods_and_connections = [];
        $multi_select_fields = [];
        $location_meta = [];
        $post_user_meta = [];
        foreach ( $fields as $field_key => $field_value ){
            if ( self::is_post_key_contact_method_or_connection( $post_settings, $field_key ) ) {
                $contact_methods_and_connections[$field_key] = $field_value;
                unset( $fields[$field_key] );
            }
            $field_type = $post_settings["fields"][$field_key]["type"] ?? '';
            if ( $field_type === "multi_select" ){
                $multi_select_fields[$field_key] = $field_value;
                unset( $fields[$field_key] );
            }
            if ( $field_type === "location_meta" || $field_type === "location" ){
                $location_meta[$field_key] = $field_value;
                unset( $fields[$field_key] );
            }
            if ( $field_type === "post_user_meta" ){
                $post_user_meta[$field_key] = $field_value;
                unset( $fields[ $field_key ] );
            }
            if ( $field_type === 'date' && !is_numeric( $field_value )){
                $fields[$field_key] = strtotime( $field_value );
            }
            if ( $field_type === 'key_select' && !is_string( $field_value ) ){
                return new WP_Error( __FUNCTION__, "key_select value must in string format: $field_key, received $field_value", [ 'status' => 400 ] );
            }
            if ( $field_type === 'user_select' ) {
                if ( is_numeric( $field_value ) ){
                    $fields[$field_key] = "user-" . $field_value;
                } else if ( !is_string( $field_value ) || strpos( $field_value, 'user-' ) !== 0 ){
                    return new WP_Error( __FUNCTION__, "incorrect format for user_select: $field_key, received $field_value", [ 'status' => 400 ] );
                }
            }
        }
        /**
         * Create the post
         */
        $post = [
            "post_title"  => $title,
            'post_type'   => $post_type,
            "post_status" => 'publish',
            "meta_input"  => $fields,
        ];
        if ( $post_date ){
            $post["post_date"] = $post_date;
        }
        $post_id = wp_insert_post( $post );

        $potential_error = self::update_post_contact_methods( $post_settings, $post_id, $contact_methods_and_connections );
        if ( is_wp_error( $potential_error )){
            return $potential_error;
        }

        $potential_error = self::update_connections( $post_settings, $post_id, $contact_methods_and_connections, null );
        if ( is_wp_error( $potential_error )){
            return $potential_error;
        }

        $potential_error = self::update_multi_select_fields( $post_settings["fields"], $post_id, $multi_select_fields, null );
        if ( is_wp_error( $potential_error )){
            return $potential_error;
        }

        $potential_error = self::update_location_grid_fields( $post_settings["fields"], $post_id, $location_meta, $post_type, null );
        if (is_wp_error( $potential_error )) {
            return $potential_error;
        }

        $potential_error = self::update_post_user_meta_fields( $post_settings["fields"], $post_id, $post_user_meta, [] );
        if ( is_wp_error( $potential_error )){
            return $potential_error;
        }

        if ( $initial_comment ) {
            $potential_error = self::add_post_comment( $post_type, $post_id, $initial_comment, "comment", [], false );
            if ( is_wp_error( $potential_error ) ) {
                return $potential_error;
            }
        }

        if ( $notes ) {
            if ( ! is_array( $notes ) ) {
                return new WP_Error( 'notes_not_array', 'Notes must be an array' );
            }
            $error = new WP_Error();
            foreach ( $notes as $note ) {
                $potential_error = self::add_post_comment( $post_type, $post_id, $note, "comment", [], false, true );
                if ( is_wp_error( $potential_error ) ) {
                    $error->add( 'comment_fail', $potential_error->get_error_message() );
                }
            }
            if ( count( $error->get_error_messages() ) > 0 ) {
                return $error;
            }
        }


        //hook for signaling that a post has been created and the initial fields
        if ( !is_wp_error( $post_id )){
            do_action( "dt_post_created", $post_type, $post_id, $initial_fields );
            if ( !$silent ){
                Disciple_Tools_Notifications::insert_notification_for_new_post( $post_type, $fields, $post_id );
            }
        }

        // share the record with the user that created it.
        if ( !empty( get_current_user_id() ) ){
            self::add_shared( $post_type, $post_id, get_current_user_id(), null, false, false, false );
        }

        if ( $check_permissions && !self::can_view( $post_type, $post_id ) ){
            return [ "ID" => $post_id ];
        } else {
            return self::get_post( $post_type, $post_id, true, $check_permissions );
        }
    }


    /**
     * Update post
     * For fields format See https://github.com/DiscipleTools/disciple-tools-theme/wiki/Contact-Fields-Format
     *
     * @param string $post_type
     * @param int $post_id
     * @param array $fields
     * @param bool $silent
     * @param bool $check_permissions
     *
     * @return array|WP_Error
     */
    public static function update_post( string $post_type, int $post_id, array $fields, bool $silent = false, bool $check_permissions = true ){
        $post_types = self::get_post_types();
        if ( !in_array( $post_type, $post_types ) ){
            return new WP_Error( __FUNCTION__, "Post type does not exist", [ 'status' => 403 ] );
        }
        if ( $check_permissions && !self::can_update( $post_type, $post_id ) ){
            return new WP_Error( __FUNCTION__, "You do not have permission to update $post_type with ID $post_id", [ 'status' => 403 ] );
        }
        $post_settings = self::get_post_settings( $post_type );
        $initial_fields = $fields;
        $post = get_post( $post_id );
        if ( !$post ) {
            return new WP_Error( __FUNCTION__, "post does not exist" );
        }

        $existing_post = self::get_post( $post_type, $post_id, false, false );
        //get extra fields and defaults
        $fields = apply_filters( "dt_post_update_fields", $fields, $post_type, $post_id, $existing_post );
        if ( is_wp_error( $fields ) ){
            return $fields;
        }

        $allowed_fields = apply_filters( "dt_post_update_allow_fields", [], $post_type );
        $bad_fields = self::check_for_invalid_post_fields( $post_settings, $fields, $allowed_fields );
        if ( !empty( $bad_fields ) ) {
            return new WP_Error( __FUNCTION__, "One or more fields do not exist", [
                'bad_fields' => $bad_fields,
                'status' => 400
            ] );
        }

        //set title
        if ( isset( $fields["title"] ) || isset( $fields["name"] ) ) {
            $title = $fields["title"] ?? $fields["name"];
            if ( $existing_post["name"] != $title ) {
                wp_update_post( [
                    'ID' => $post_id,
                    'post_title' => $title
                ] );
                dt_activity_insert( [
                    'action'            => 'field_update',
                    'object_type'       => $post_type,
                    'object_subtype'    => 'title',
                    'object_id'         => $post_id,
                    'object_name'       => $title,
                    'meta_key'          => 'title',
                    'meta_value'        => $title,
                    'old_value'         => $existing_post['name'],
                ] );
            }
            if ( isset( $fields["name"] ) ){
                unset( $fields["name"] );
            }
        }

        $potential_error = self::update_post_contact_methods( $post_settings, $post_id, $fields, $existing_post );
        if ( is_wp_error( $potential_error )){
            return $potential_error;
        }

        $potential_error = self::update_connections( $post_settings, $post_id, $fields, $existing_post );
        if ( is_wp_error( $potential_error )){
            return $potential_error;
        }

        $potential_error = self::update_multi_select_fields( $post_settings["fields"], $post_id, $fields, $existing_post );
        if ( is_wp_error( $potential_error )){
            return $potential_error;
        }

        $potential_error = self::update_location_grid_fields( $post_settings["fields"], $post_id, $fields, $post_type, $existing_post );
        if (is_wp_error( $potential_error )) {
            return $potential_error;
        }

        $potential_error = self::update_post_user_meta_fields( $post_settings["fields"], $post_id, $fields, $existing_post );
        if ( is_wp_error( $potential_error )){
            return $potential_error;
        }

        $fields["last_modified"] = time(); //make sure the last modified field is updated.
        foreach ( $fields as $field_key => $field_value ){
            if ( !self::is_post_key_contact_method_or_connection( $post_settings, $field_key ) ) {
                $field_type = $post_settings["fields"][ $field_key ]["type"] ?? '';
                if ( $field_type === 'date' && !is_numeric( $field_value ) ) {
                    if ( empty( $field_value ) ) { // remove date
                        delete_post_meta( $post_id, $field_key );
                        continue;
                    }
                    $field_value = strtotime( $field_value );
                }
                if ( $field_type === 'key_select' && !is_string( $field_value ) ){
                    return new WP_Error( __FUNCTION__, "key_select value must in string format: $field_key, received $field_value", [ 'status' => 400 ] );
                }
                if ( $field_type === 'user_select' ) {
                    if ( is_numeric( $field_value ) ){
                        $field_value = "user-" . $field_value;
                    } else if ( !is_string( $field_value ) || strpos( $field_value, 'user-' ) !== 0 ){
                        return new WP_Error( __FUNCTION__, "incorrect format for user_select: $field_key, received $field_value", [ 'status' => 400 ] );
                    }
                }
                /**
                 * Custom Handled Meta
                 *
                 * This filter includes the types of fields handled in the above section, but can have a new
                 * field type included, so that it can be skipped here and handled later through the
                 * dt_post_updated action.
                 */
                $already_handled = apply_filters( 'dt_post_updated_custom_handled_meta', [ "multi_select", "post_user_meta", "location", "location_meta", "communication_channel" ], $post_type );
                if ( $field_type && !in_array( $field_type, $already_handled ) ) {
                    update_post_meta( $post_id, $field_key, $field_value );
                }
            }
        }

        $post = self::get_post( $post_type, $post_id, false, false ); // get post to add to action hook
        do_action( "dt_post_updated", $post_type, $post_id, $initial_fields, $existing_post, $post );
        $post = self::get_post( $post_type, $post_id, false, false ); // get post with fields updated by action hook
        if ( !$silent ){
            Disciple_Tools_Notifications::insert_notification_for_post_update( $post_type, $post, $existing_post, array_keys( $fields ) );
        }

        if ( $check_permissions && !self::can_view( $post_type, $post_id ) ){
            return [ "ID" => $post_id ];
        } else {
            return $post;
        }
    }


    /**
     * Get Post
     *
     * @param string $post_type
     * @param int $post_id
     * @param bool $use_cache
     * @param bool $check_permissions
     * @param bool $silent create activity log for the view
     * @return array|WP_Error
     */
    public static function get_post( string $post_type, int $post_id, bool $use_cache = true, bool $check_permissions = true, bool $silent = false ){
        if ( $check_permissions && !self::can_view( $post_type, $post_id ) ) {
            return new WP_Error( __FUNCTION__, "No permissions to read $post_type with ID $post_id", [ 'status' => 403 ] );
        }
        $current_user_id = get_current_user_id();
        $cached = wp_cache_get( "post_" . $current_user_id . '_' . $post_id );
        if ( $cached && $use_cache ){
            return $cached;
        }

        $wp_post = get_post( $post_id );
        if ( $use_cache === true && $current_user_id && !$silent ){
            dt_activity_insert( [
                'action' => 'viewed',
                'object_type' => $post_type,
                'object_id' => $post_id,
                'object_name' => $wp_post->post_title
            ] );
        }
        $field_settings = self::get_post_field_settings( $post_type );
        if ( !$wp_post ){
            return new WP_Error( __FUNCTION__, "post does not exist", [ 'status' => 400 ] );
        }
        $fields = [];

        /**
         * add connections
         */
        self::get_all_connection_fields( $field_settings, $post_id, $fields );
        $fields["ID"] = $post_id;
        $fields["post_date"] = [
            "timestamp" => is_numeric( $wp_post->post_date ) ? $wp_post->post_date : dt_format_date( $wp_post->post_date, "U" ),
            "formatted" => dt_format_date( $wp_post->post_date )
        ];
        $fields["permalink"] = get_permalink( $post_id );
        $fields["post_type"] = $post_type;
        $fields["post_author"] = $wp_post->post_author;
        $author = get_user_by( "ID", $wp_post->post_author );
        $fields["post_author_display_name"] = $author ? $author->display_name : "";


        self::adjust_post_custom_fields( $post_type, $post_id, $fields );
        $fields["name"] = wp_specialchars_decode( $wp_post->post_title );
        $fields["title"] = wp_specialchars_decode( $wp_post->post_title );

        $fields = apply_filters( 'dt_after_get_post_fields_filter', $fields, $post_type );
        wp_cache_set( "post_" . $current_user_id . '_' . $post_id, $fields );
        return $fields;

    }


    /**
     * Get a list of posts
     * For query format see https://github.com/DiscipleTools/disciple-tools-theme/wiki/Filter-and-Search-Lists
     *
     * @param string $post_type
     * @param array $search_and_filter_query
     * @param bool $check_permissions
     *
     * @return array|WP_Error
     */
    public static function list_posts( string $post_type, array $search_and_filter_query, bool $check_permissions = true ){
        $fields_to_return = [];
        if ( isset( $search_and_filter_query["fields_to_return"] ) ){
            $fields_to_return = $search_and_filter_query["fields_to_return"];
            unset( $search_and_filter_query["fields_to_return"] );
        }
        if ( isset( $search_and_filter_query["dt_recent"] ) ){
            $data = self::get_recently_viewed_posts( $post_type );
        } else {
            $data = self::search_viewable_post( $post_type, $search_and_filter_query, $check_permissions );
        }
        if ( is_wp_error( $data ) ) {
            return $data;
        }
        $post_settings = self::get_post_settings( $post_type );
        $records = $data["posts"];
        foreach ( $post_settings["connection_types"] as $connection_type ){
            if ( empty( $fields_to_return ) || in_array( $connection_type, $fields_to_return ) && !empty( $records ) ){
                $p2p_type = $post_settings["fields"][$connection_type]["p2p_key"];
                $p2p_direction = $post_settings["fields"][$connection_type]["p2p_direction"];
                $q = p2p_type( $p2p_type )->set_direction( $p2p_direction )->get_connected( $records, [ "nopaging" => true ], 'abstract' );
                $raw_connected = array();
                foreach ( $q->items as $item ){
                    $raw_connected[] = $item->get_object();
                }
                p2p_distribute_connected( $records, $raw_connected, $connection_type );
            }
        }

        $ids = [];
        foreach ( $records as $record ) {
            $record->post_title = wp_specialchars_decode( $record->post_title );
            $ids[] = $record->ID;
        }
        $ids_sql = dt_array_to_sql( $ids );
        $field_keys = [];
        if ( !in_array( 'all_fields', $fields_to_return ) ){
            $field_keys = empty( $fields_to_return ) ? array_keys( $post_settings["fields"] ) : $fields_to_return;
        }
        $field_keys_sql = dt_array_to_sql( $field_keys );

        global $wpdb;
        $all_posts = [];
        // phpcs:disable
        // WordPress.WP.PreparedSQL.NotPrepared
        $all_post_meta = $wpdb->get_results( "
            SELECT *
                FROM $wpdb->postmeta pm
                WHERE pm.post_id IN ( $ids_sql )
                AND pm.meta_key IN ( $field_keys_sql )
            UNION SELECT *
                FROM $wpdb->postmeta pm
                WHERE pm.post_id IN ( $ids_sql )
                AND pm.meta_key LIKE 'contact_%'
        ", ARRAY_A);
        $user_id = get_current_user_id();
        $all_user_meta = $wpdb->get_results( $wpdb->prepare( "
            SELECT *
            FROM $wpdb->dt_post_user_meta um
            WHERE um.post_id IN ( $ids_sql )
            AND user_id = %s
            AND um.meta_key IN ( $field_keys_sql )
        ", $user_id ), ARRAY_A);
        // phpcs:disable

        foreach ( $all_post_meta as $index => $meta_row ) {
            if ( !isset( $all_posts[$meta_row["post_id"]] ) ) {
                $all_posts[$meta_row["post_id"]] = [];
            }
            if ( !isset( $all_posts[$meta_row["post_id"]][$meta_row["meta_key"]] ) ) {
                $all_posts[$meta_row["post_id"]][$meta_row["meta_key"]] = [];
            }
            $all_posts[$meta_row["post_id"]][$meta_row["meta_key"]][] = $meta_row["meta_value"];
        }
        $all_post_user_meta =[];
        foreach ( $all_user_meta as $index => $meta_row ){
            if ( !isset( $all_post_user_meta[$meta_row["post_id"]] ) ) {
                $all_post_user_meta[$meta_row["post_id"]] = [];
            }
            $all_post_user_meta[$meta_row["post_id"]][] = $meta_row;
        }

        $site_url = site_url();
        foreach ( $records as  &$record ){
            foreach ( $post_settings["connection_types"] as $connection_type ){
                if ( empty( $fields_to_return ) || in_array( $connection_type, $fields_to_return ) ) {
                    foreach ( $record->$connection_type as &$post ) {
                        $post = self::filter_wp_post_object_fields( $post );
                    }
                }
            }
            $record = (array) $record;

            self::adjust_post_custom_fields( $post_type, $record["ID"], $record, $fields_to_return, $all_posts[$record["ID"]] ?? [], $all_post_user_meta[$record["ID"]] ?? [] );
            $record["permalink"] = $site_url . '/' . $post_type .'/' . $record["ID"];
            $record["name"] = wp_specialchars_decode( $record["post_title"] );
            $record["post_date"] = [
                "timestamp" => is_numeric( $record["post_date"] ) ? $record["post_date"] : dt_format_date( $record["post_date"], "U" ),
                "formatted" => dt_format_date( $record["post_date"] )
            ];
        }
        $data["posts"] = $records;

        $data = apply_filters( "dt_list_posts_custom_fields", $data, $post_type );

        return $data;
    }


    /**
     * Get viewable in compact form
     *
     * @param string $post_type
     * @param string $search_string
     * @param array $args
     *
     * @return array|WP_Error|WP_Query
     */
    public static function get_viewable_compact( string $post_type, string $search_string, array $args = [] ) {
        if ( !self::can_access( $post_type ) ) {
            return new WP_Error( __FUNCTION__, sprintf( "You do not have access to these %s", $post_type ), [ 'status' => 403 ] );
        }
        global $wpdb;
        $current_user = wp_get_current_user();
        $compact = [];
        $search_string = esc_sql( sanitize_text_field( $search_string ) );

        //search by post_id
        if ( is_numeric( $search_string ) ){
            $post = get_post( $search_string );
            if ( $post && self::can_view( $post_type, $post->ID ) ){
                $compact[] = [
                    "ID" => (string) $post->ID,
                    "name" => $post->post_title,
                    "user" => false,
                    "status" => null
                ];
            }
        }

        $send_quick_results = false;
        if ( empty( $search_string ) ){
            $field_settings = DT_Posts::get_post_field_settings( $post_type );
            //find the most recent posts viewed by the user from the activity log
            $posts = $wpdb->get_results( $wpdb->prepare( "
                SELECT *
                FROM $wpdb->posts p
                INNER JOIN (
                    SELECT log.object_id
                    FROM $wpdb->dt_activity_log log
                    INNER JOIN (
                        SELECT max(l.histid) as maxid FROM $wpdb->dt_activity_log l
                        WHERE l.user_id = %s  AND l.action = 'viewed' AND l.object_type = %s
                        group by l.object_id
                    ) x on log.histid = x.maxid
                ORDER BY log.histid desc
                LIMIT 5
                ) as log
                ON log.object_id = p.ID
                WHERE p.post_type = %s AND (p.post_status = 'publish' OR p.post_status = 'private')

            ", $current_user->ID, $post_type, $post_type ), OBJECT );

            //find what the user previously chose as values for this field.
            if ( isset( $args["field_key"], $field_settings[$args["field_key"]] ) && $field_settings[$args["field_key"]]["type"] === 'connection' ){
                $action = 'connected to';
                $field_type = 'connection from';
                if ( $field_settings[$args["field_key"]]["p2p_direction"] === "from" ){
                    $field_type = 'connection to';
                }
                //find the most recent posts interacted with by the user
                $posts_2 = $wpdb->get_results( $wpdb->prepare( "
                    SELECT *
                    FROM $wpdb->posts p
                    INNER JOIN (
                        SELECT log.object_id
                        FROM $wpdb->dt_activity_log log
                        INNER JOIN (
                            SELECT max(l.histid) as maxid FROM $wpdb->dt_activity_log l
                            WHERE l.user_id = %s  AND l.action = %s AND l.object_type = %s AND l.meta_key = %s AND l.field_type = %s
                            group by l.object_id
                        ) x on log.histid = x.maxid
                    ORDER BY log.histid desc
                    LIMIT 5
                    ) as log
                    ON log.object_id = p.ID
                    WHERE p.post_type = %s AND (p.post_status = 'publish' OR p.post_status = 'private')

                ", $current_user->ID, $action, $post_type, $field_settings[$args["field_key"]]["p2p_key"], $field_type, $post_type ), OBJECT );

                $post_ids = array_map( function( $post ) { return $post->ID; }, $posts );
                foreach ( $posts_2 as $p ){
                    if ( !in_array( $p->ID, $post_ids ) ){
                        $posts[] = $p;
                    }
                }
            }
            if ( !empty( $posts ) && sizeof( $posts ) > 2 ){
                $send_quick_results = true;
            }
        }

        if ( !$send_quick_results ){
            $query = [ "limit" => 50 ];
            if ( !empty( $search_string ) ){
                $query["name"] = [ $search_string ];
            }
            $posts_list = self::search_viewable_post( $post_type, $query );
            if ( is_wp_error( $posts_list ) ){
                return $posts_list;
            }
            $posts = $posts_list["posts"];
        }

        if ( is_wp_error( $posts ) ) {
            return $posts;
        }

        $post_ids = array_map(
            function( $post ) {
                return $post->ID;
            },
            $posts
        );

        //filter out users if requested.
        foreach ( $posts as $post ) {
            if ( isset( $args["include-users"] ) && $args["include-users"] === "false" && $post->corresponds_to_user >= 1 ){
                continue;
            }
            $compact[] = [
                "ID" => $post->ID,
                "name" => wp_specialchars_decode ( $post->post_title )
            ];
        }

        //add in user results when searching contacts.
        if ( $post_type === 'contacts' && !self::can_view_all( $post_type )
            && !( isset( $args["include-users"] ) && $args["include-users"] === "false" )
        ) {
            $users_interacted_with = Disciple_Tools_Users::get_assignable_users_compact( $search_string );
            $users_interacted_with = array_slice( $users_interacted_with, 0, 5 );
            if ( $current_user ){
                array_unshift( $users_interacted_with, [ "name" => $current_user->display_name, "ID" => $current_user->ID ] );
            }
            foreach ( $users_interacted_with as $user ) {
                $post_id = Disciple_Tools_Users::get_contact_for_user( $user["ID"] );
                if ( $post_id ){
                    if ( !in_array( $post_id, $post_ids ) ) {
                        $compact[] = [
                            "ID" => $post_id,
                            "name" => $user["name"],
                            "user" => true
                        ];
                    }
                }
            }
        }


        //set user field if the contact is a user.
        if ( $post_type === "contacts" ){
            $post_ids_sql = dt_array_to_sql( $post_ids );
            $user_post_ids = $wpdb->get_results( "
                SELECT post_id, meta_value
                FROM $wpdb->postmeta pm
                WHERE pm.post_id in ( $post_ids_sql )
                AND meta_key = 'corresponds_to_user'
                ", ARRAY_A
            );
            foreach( $user_post_ids as $res ){
                foreach( $compact as $index => &$p ){
                    if ( $p["ID"] === $res["post_id"] ){
                        $compact[$index]['user'] = true;
                    }
                }
            }
            if ( !empty( $search_string ) ){
                //place user records first, then sort by name.
                uasort( $compact, function ( $a, $b ) use ( $search_string ) {
                    if ( isset( $a['user'] ) && !empty( $a['user'] ) ){
                        return -3;
                    } else if ( isset( $b['user'] ) && !empty( $b['user'] ) ){
                        return 2;
                    } elseif ( $a["name"] === $search_string ){
                        return -2;
                    } else if ( $b["name"] === $search_string ){
                        return 1;
                    } else {
                        return $a['name'] <=> $b['name'];
                    }
                });
            }
        }

        if ( $post_type === "peoplegroups" ){
            $list = [];
            $locale = get_user_locale();

            foreach ( $posts as $post ) {
                $translation = get_post_meta( $post->ID, $locale, true );
                if ($translation !== "") {
                    $label = $translation;
                } else {
                    $label = $post->post_title;
                }
                foreach( $compact as $index => &$p ){
                    if ( $compact[$index]["ID"] === $post->ID ) {
                        $compact[$index] = [
                            "ID" => $post->ID,
                            "name" => $post->post_title,
                            "label" => $label
                            ];
                    }
                }
            }
        }

        $return = [
            "total" => sizeof( $compact ),
            "posts" => array_slice( $compact, 0, 50 )
        ];
        return apply_filters( 'dt_get_viewable_compact', $return, $post_type, $search_string, $args );
    }

    /**
     * Comments
     */

    /**
     * @param string $post_type
     * @param int $post_id
     * @param string $comment_html
     * @param string $type      normally 'comment', different comment types can have their own section in the comments activity
     * @param array $args       [user_id, comment_date, comment_author etc]
     * @param bool $check_permissions
     * @param bool $silent
     *
     * @return false|int|WP_Error
     */
    public static function add_post_comment( string $post_type, int $post_id, string $comment_html, string $type = "comment", array $args = [], bool $check_permissions = true, $silent = false ) {
        if ( $check_permissions && !self::can_update( $post_type, $post_id ) ) {
            return new WP_Error( __FUNCTION__, "You do not have permission for this", [ 'status' => 403 ] );
        }
        //limit comment length to 5000
        $comments = str_split( $comment_html, 4999 );
        $user = wp_get_current_user();
        $user_id = $args["user_id"] ?? get_current_user_id();

        $created_comment_id = null;
        foreach ( $comments as $comment ){
            $comment_data = [
                'comment_post_ID'      => $post_id,
                'comment_content'      => wp_kses($comment, self::$allowable_comment_tags),
                'user_id'              => $user_id,
                'comment_author'       => $args["comment_author"] ?? $user->display_name,
                'comment_author_url'   => $args["comment_author_url"] ?? "",
                'comment_author_email' => $user->user_email,
                'comment_type'         => $type,
            ];
            if ( isset( $args["comment_date"] ) ){
                $comment_data["comment_date"] = $args["comment_date"];
                $comment_data["comment_date_gmt"] = $args["comment_date"];
            }
            $new_comment = wp_new_comment( $comment_data );
            if ( !$created_comment_id ){
                $created_comment_id = $new_comment;
            }
        }

        if ( !$silent && !is_wp_error( $created_comment_id )){
            Disciple_Tools_Notifications_Comments::insert_notification_for_comment( $created_comment_id );
        }
        if ( !is_wp_error( $created_comment_id ) ){
            do_action( "dt_comment_created", $post_type, $post_id, $created_comment_id, $type );
        }
        return $created_comment_id;
    }

    public static function update_post_comment( int $comment_id, string $comment_content, bool $check_permissions = true, string $comment_type = "comment" ){
        $comment = get_comment( $comment_id );
        if ( $check_permissions && ( ( isset( $comment->user_id ) && $comment->user_id != get_current_user_id() ) || !self::can_update( get_post_type( $comment->comment_post_ID ), $comment->comment_post_ID ?? 0 ) ) ) {
            return new WP_Error( __FUNCTION__, "You don't have permission to edit this comment", [ 'status' => 403 ] );
        }
        if ( !$comment ){
            return new WP_Error( __FUNCTION__, "No comment found with id: " . $comment_id, [ 'status' => 403 ] );
        }
        $comment = [
            "comment_content" => $comment_content,
            "comment_ID" => $comment_id,
            "comment_type" => $comment_type
        ];
        $update = wp_update_comment( $comment );
        if ( $update === 1 ){
            return $comment_id;
        } else if ( is_wp_error( $update )) {
              return $update;
        } else {
            return new WP_Error( __FUNCTION__, "Error updating comment with id: " . $comment_id, [ 'status' => 500 ] );
        }
    }

    public static function delete_post_comment( int $comment_id, bool $check_permissions = true ){
        $comment = get_comment( $comment_id );
        if ( $check_permissions && ( ( isset( $comment->user_id ) && $comment->user_id != get_current_user_id() ) || !self::can_update( get_post_type( $comment->comment_post_ID ), $comment->comment_post_ID ?? 0 ) ) ) {
            return new WP_Error( __FUNCTION__, "You don't have permission to delete this comment", [ 'status' => 403 ] );
        }
        if ( !$comment ){
            return new WP_Error( __FUNCTION__, "No comment found with id: " . $comment_id, [ 'status' => 403 ] );
        }
        return wp_delete_comment( $comment_id );
    }

    /**
     * Get post comments
     *
     * @param string $post_type
     * @param int $post_id
     * @param bool $check_permissions
     * @param string $type
     * @param array $args
     *
     * @return array|int|WP_Error
     */
    public static function get_post_comments( string $post_type, int $post_id, bool $check_permissions = true, string $type = "all", array $args = [] ) {
        if ( $check_permissions && !self::can_view( $post_type, $post_id ) ) {
            return new WP_Error( __FUNCTION__, "No permissions to read post", [ 'status' => 403 ] );
        }
        //setting type to "comment" does not work.
        $comments_query = [
            'post_id' => $post_id,
            "type" => $type
        ];
        if ( isset( $args["offset"] ) || isset( $args["number"] ) ){
            $comments_query["offset"] = $args["offset"] ?? 0;
            $comments_query["number"] = $args["number"] ?? '';
        }
        $comments = get_comments( $comments_query );


        $response_body = [];
        foreach ( $comments as $comment ){
            if ( $comment->comment_author_url ){
                $url = str_replace( "&amp;", "&", $comment->comment_author_url );
            } else {
                $url = get_avatar_url( $comment->user_id, [ 'size' => '16' ] );
            }
            $c = [
                "comment_ID" => $comment->comment_ID,
                "comment_author" => !empty( $display_name ) ? $display_name : $comment->comment_author,
                "comment_author_email" => $comment->comment_author_email,
                "comment_date" => $comment->comment_date,
                "comment_date_gmt" => $comment->comment_date_gmt,
                "gravatar" => preg_replace( "/^http:/i", "https:", $url ),
                "comment_content" => $comment->comment_content,
                "user_id" => $comment->user_id,
                "comment_type" => $comment->comment_type,
                "comment_post_ID" => $comment->comment_post_ID
            ];
            $response_body[] = $c;
        }

        $response_body = apply_filters( "dt_filter_post_comments", $response_body, $post_type, $post_id );

        foreach ( $response_body as &$comment ){
            $comment["comment_content"] = wp_kses( $comment["comment_content"], self::$allowable_comment_tags);
        }

        return [
            "comments" => $response_body,
            "total" => wp_count_comments( $post_id )->total_comments
        ];
    }


    /**
     * Activity
     */

    /**
     * @param string $post_type
     * @param int $post_id
     * @param array $args
     *
     * @return array|null|object|WP_Error
     */
    public static function get_post_activity( string $post_type, int $post_id, array $args = [] ) {
        global $wpdb;
        if ( !self::can_view( $post_type, $post_id ) ) {
            return new WP_Error( __FUNCTION__, "No permissions to read: " . $post_type, [ 'status' => 403 ] );
        }
        $post_settings = self::get_post_settings( $post_type );
        $fields = $post_settings["fields"];
        $hidden_fields = [];
        foreach ( $fields as $field_key => $field ){
            if ( isset( $field["hidden"] ) && $field["hidden"] === true ){
                $hidden_fields[] = $field_key;
            }
        }

        $hidden_keys = empty( $hidden_fields ) ? "''" : dt_array_to_sql( $hidden_fields );
        // phpcs:disable
        // WordPress.WP.PreparedSQL.NotPrepared
        $activity = $wpdb->get_results( $wpdb->prepare(
            "SELECT
                *
            FROM
                `$wpdb->dt_activity_log`
            WHERE
                `object_type` = %s
                AND `object_id` = %s
                AND meta_key NOT IN ( $hidden_keys )
            ORDER BY hist_time DESC",
            $post_type,
            $post_id
        ) );
        //@phpcs:enable
        $activity_simple = [];
        foreach ( $activity as $a ) {
            $a->object_note = self::format_activity_message( $a, $post_settings );
            if ( isset( $a->user_id ) && $a->user_id > 0 ) {
                $user = get_user_by( "id", $a->user_id );
                if ( $user ){
                    $a->name =$user->display_name;
                    $a->gravatar = get_avatar_url( $user->ID, [ 'size' => '16' ] );
                }
            } else if ( isset( $a->user_caps ) && strlen( $a->user_caps ) === 32 ){
                //get site-link name
                $site_link = Site_Link_System::get_post_id_by_site_key( $a->user_caps );
                if ( $site_link ){
                    $a->name = get_the_title( $site_link );
                }
            }
            if ( !empty( $a->object_note ) ){
                $activity_simple[] = [
                    "meta_key" => $a->meta_key,
                    "gravatar" => isset( $a->gravatar ) ? $a->gravatar : "",
                    "name" => isset( $a->name ) ? $a->name : __( "D.T System", 'disciple_tools' ),
                    "object_note" => $a->object_note,
                    "hist_time" => $a->hist_time,
                    "meta_id" => $a->meta_id,
                    "histid" => $a->histid,
                ];
            }
        }

        $paged = array_slice( $activity_simple, $args["offset"] ?? 0, $args["number"] ?? 1000 );
        return [
            "activity" => $paged,
            "total" => sizeof( $activity_simple )
        ];
    }

    public static function get_post_single_activity( string $post_type, int $post_id, int $activity_id ){
        global $wpdb;
        if ( !self::can_view( $post_type, $post_id ) ) {
            return new WP_Error( __FUNCTION__, "No permissions to read group", [ 'status' => 403 ] );
        }
        $post_settings = self::get_post_settings( $post_type );
        $activity = $wpdb->get_results( $wpdb->prepare(
            "SELECT
                *
            FROM
                `$wpdb->dt_activity_log`
            WHERE
                `object_type` = %s
                AND `object_id` = %s
                AND `histid` = %s",
            $post_type,
            $post_id,
            $activity_id
        ) );
        foreach ( $activity as $a ) {
            $a->object_note = self::format_activity_message( $a, $post_settings );
            if ( isset( $a->user_id ) && $a->user_id > 0 ) {
                $user = get_user_by( "id", $a->user_id );
                if ( $user ) {
                    $a->name = $user->display_name;
                }
            }
        }
        if ( isset( $activity[0] ) ){
            return $activity[0];
        }
        return $activity;
    }

    /**
     * Sharing
     */

    /**
     * Gets an array of users whom the post is shared with.
     *
     * @param string $post_type
     * @param int $post_id
     *
     * @param bool $check_permissions
     *
     * @return array|mixed
     */
    public static function get_shared_with( string $post_type, int $post_id, bool $check_permissions = true ) {
        global $wpdb;

        if ( $check_permissions && !self::can_view( $post_type, $post_id ) ) {
            return new WP_Error( 'no_permission', "You do not have permission for this", [ 'status' => 403 ] );
        }

        $shared_with_list = [];
        $shares = $wpdb->get_results( $wpdb->prepare(
            "SELECT
                *
            FROM
                `$wpdb->dt_share`
            WHERE
                post_id = %s",
            $post_id
        ), ARRAY_A );

        // adds display name to the array
        foreach ( $shares as $share ) {
            $display_name = dt_get_user_display_name( $share['user_id'] );
            if ( is_wp_error( $display_name ) ) {
                $display_name = 'Not Found';
            }
            $share['display_name'] = $display_name;
            $shared_with_list[] = $share;
        }

        return $shared_with_list;
    }

    /**
     * Removes share record
     *
     * @param string $post_type
     * @param int    $post_id
     * @param int    $user_id
     *
     * @return false|int|WP_Error
     */
    public static function remove_shared( string $post_type, int $post_id, int $user_id ) {
        global $wpdb;

        if ( !self::can_update( $post_type, $post_id ) ) {
            return new WP_Error( __FUNCTION__, "You do not have permission to unshare", [ 'status' => 403 ] );
        }

        $assigned_to_meta = get_post_meta( $post_id, "assigned_to", true );
        if ( !( self::can_update( $post_type, $post_id ) ||
                get_current_user_id() === $user_id ||
                dt_get_user_id_from_assigned_to( $assigned_to_meta ) === get_current_user_id() )
        ){
            $name = dt_get_user_display_name( $user_id );
            return new WP_Error( __FUNCTION__, "You do not have permission to unshare with " . $name, [ 'status' => 403 ] );
        }


        $table = $wpdb->dt_share;
        $where = [
            'user_id' => $user_id,
            'post_id' => $post_id
        ];
        $result = $wpdb->delete( $table, $where );

        if ( $result == false ) {
            return new WP_Error( 'remove_shared', "Record not deleted.", [ 'status' => 418 ] );
        } else {

            // log share activity
            dt_activity_insert(
                [
                    'action'         => 'remove',
                    'object_type'    => get_post_type( $post_id ),
                    'object_subtype' => 'share',
                    'object_name'    => get_the_title( $post_id ),
                    'object_id'      => $post_id,
                    'meta_id'        => '', // id of the comment
                    'meta_key'       => '',
                    'meta_value'     => $user_id,
                    'meta_parent'    => '',
                    'object_note'    => 'Sharing of ' . get_the_title( $post_id ) . ' was removed for ' . dt_get_user_display_name( $user_id ),
                ]
            );

            return $result;
        }
    }

    /**
     * Adds a share record
     *
     * @param string $post_type
     * @param int $post_id
     * @param int $user_id
     * @param array $meta
     * @param bool $send_notifications
     * @param bool $check_permissions
     * @param bool $insert_activity
     *
     * @return false|int|WP_Error
     */
    public static function add_shared( string $post_type, int $post_id, int $user_id, $meta = null, bool $send_notifications = true, $check_permissions = true, bool $insert_activity = true ) {
        global $wpdb;

        if ( $check_permissions && !self::can_update( $post_type, $post_id ) ) {
            return new WP_Error( __FUNCTION__, "You do not have permission for this", [ 'status' => 403 ] );
        }

        $table = $wpdb->dt_share;
        $data = [
            'user_id' => $user_id,
            'post_id' => $post_id,
            'meta'    => $meta,
        ];
        $format = [
            '%d',
            '%d',
            '%s',
        ];

        $duplicate_check = $wpdb->get_row( $wpdb->prepare(
            "SELECT
                id
            FROM
                `$wpdb->dt_share`
            WHERE
                post_id = %s
                AND user_id = %s",
            $post_id,
            $user_id
        ), ARRAY_A );

        if ( is_null( $duplicate_check ) ) {

            // insert share record
            $results = $wpdb->insert( $table, $data, $format );

            if ( $insert_activity ){
                // log share activity
                dt_activity_insert(
                    [
                        'action'         => 'share',
                        'object_type'    => get_post_type( $post_id ),
                        'object_subtype' => 'share',
                        'object_name'    => get_the_title( $post_id ),
                        'object_id'      => $post_id,
                        'meta_id'        => '', // id of the comment
                        'meta_key'       => '',
                        'meta_value'     => $user_id,
                        'meta_parent'    => '',
                        'object_note'    => strip_tags( get_the_title( $post_id ) ) . ' was shared with ' . dt_get_user_display_name( $user_id ),
                    ]
                );
            }

            // Add share notification
            if ( $send_notifications ){
                Disciple_Tools_Notifications::insert_notification_for_share( $user_id, $post_id );
            }

            return $results;
        } else {
            return new WP_Error( 'add_shared', __( "Post already shared with user.", 'disciple_tools' ), [ 'status' => 418 ] );
        }
    }


    /**
     * Following
     */
    /**
     * @param $post_type
     * @param $post_id
     * @param bool $check_permissions
     *
     * @return array|WP_Error
     */
    public static function get_users_following_post( $post_type, $post_id, $check_permissions = true ){
        if ( $check_permissions && !self::can_access( $post_type ) ){
            return new WP_Error( __FUNCTION__, "You do not have access to: " . $post_type, [ 'status' => 403 ] );
        }
        $users = [];
        $assigned_to_meta = get_post_meta( $post_id, "assigned_to", true );
        $assigned_to = dt_get_user_id_from_assigned_to( $assigned_to_meta );
        if ( $post_type === "contacts" ){
            array_merge( $users, self::get_subassigned_users( $post_id ) );
        }
        $shared_with = self::get_shared_with( $post_type, $post_id, false );
        foreach ( $shared_with as $shared ){
            $users[] = (int) $shared["user_id"];
        }
        $users_follow = get_post_meta( $post_id, "follow", false );
        foreach ( $users_follow as $follow ){
            if ( !in_array( $follow, $users ) && user_can( $follow, "view_any_". $post_type ) ){
                $users[] = $follow;
            }
        }
        $users_unfollow = get_post_meta( $post_id, "unfollow", false );
        foreach ( $users_unfollow as $unfollower ){
            $key = array_search( $unfollower, $users );
            if ( $key !== false ){
                unset( $users[$key] );
            }
        }
        //you always follow a post if you are assigned to it.
        if ( $assigned_to ){
            $users[] = $assigned_to;
        }
        return array_unique( $users );
    }


    public static function get_post_names_from_ids( array $post_ids ){
        if ( empty( $post_ids ) ){
            return [];
        }
        global $wpdb;
        $ids_sql = dt_array_to_sql( $post_ids );

        //phpcs:disable
        return $wpdb->get_results( "
            SELECT ID, post_title
            FROM $wpdb->posts
            WHERE ID IN ( $ids_sql )
        ", ARRAY_A );
        //phpcs:enable

    }

    public static function get_post_field_settings( $post_type, $load_from_cache = true, $with_deleted_options = false ){
        $cached = wp_cache_get( $post_type . "_field_settings" );
        if ( $load_from_cache && $cached ){
            return $cached;
        }
        $fields = Disciple_Tools_Post_Type_Template::get_base_post_type_fields();
        $fields = apply_filters( 'dt_custom_fields_settings', $fields, $post_type );

        $langs = dt_get_available_languages();

        foreach ( $fields as $field_key => $field ){
            if ( $field["type"] === "key_select" || $field["type"] === "multi_select" ){
                foreach ( $field["default"] as $option_key => $option_value ){
                    if ( !is_array( $option_value )){
                        $fields[$field_key]["default"][$option_key] = [ "label" => $option_value ];
                    }
                }
            }
        }
        $custom_field_options = dt_get_option( "dt_field_customizations" );
        if ( isset( $custom_field_options[$post_type] )){
            foreach ( $custom_field_options[$post_type] as $key => $field ){
                $field_type = $field["type"] ?? $fields[$key]["type"] ?? "";
                if ( $field_type ) {
                    if ( !isset( $fields[ $key ] ) ) {
                        $fields[ $key ] = $field;
                    } else {
                        foreach ( $field as $custom_option_key => $custom_option_value ){
                            if ( !is_array( $custom_option_value ) && $custom_option_value !== "" ) {
                                $fields[$key][$custom_option_key] = $custom_option_value;
                            } else if ( is_array( $custom_option_value ) ){
                                if ( !isset( $fields[$key][$custom_option_key] ) ){
                                    $fields[$key][$custom_option_key] = [];
                                }
                                if ( is_array( $fields[$key][$custom_option_key] ) ){
                                    $fields[$key][$custom_option_key] = dt_array_merge_recursive_distinct( $fields[$key][$custom_option_key], $custom_option_value );
                                }
                            }
                        }
                        if ( $field_type === "key_select" || $field_type === "multi_select" ) {
                            if ( isset( $field["default"] ) ) {
                                foreach ( $field["default"] as $custom_key => &$custom_value ) {
                                    if ( isset( $custom_value["label"] ) && empty( $custom_value["label"] ) ) {
                                        unset( $custom_value["label"] );
                                    }
                                }
                                $fields[ $key ]["default"] = array_replace_recursive( $fields[ $key ]["default"], $field["default"] );
                            }
                        }
                        foreach ( $langs as $lang => $val ) {
                            if ( !empty( $field["translations"][$val['language']] ) ) {
                                $fields[ $key ]["translations"][$val['language']] = $field["translations"][$val['language']];
                            }
                        }
                    }
                    //set the order of key_select and multiselect fields
                    if ( $field_type === "key_select" || $field_type === "multi_select" ) {
                        if ( isset( $field["order"] ) ) {
                            $with_order = [];
                            foreach ( $field["order"] as $ordered_key ) {
                                $with_order[ $ordered_key ] = [];
                            }
                            foreach ( $fields[ $key ]["default"] as $option_key => $option_value ) {
                                $with_order[ $option_key ] = $option_value;
                            }
                            $fields[ $key ]["default"] = $with_order;
                        }
                    }
                }
            }
        }
        if ( $with_deleted_options === false ){
            foreach ( $fields as $field_key => $field ){
                if ( $field["type"] === "key_select" || $field["type"] === "multi_select" ){
                    foreach ( $field["default"] as $option_key => $option_value ){
                        if ( isset( $option_value["deleted"] ) && $option_value["deleted"] == true ){
                            unset( $fields[$field_key]["default"][$option_key] );
                        }
                    }
                }
            }
        }
        foreach ( $fields as $field_key => &$field ){
            if ( !isset( $field["name"] ) ){
                $field["name"] = $field_key; //set a field name so integration can depend on it.
            }
        }

        $fields = apply_filters( 'dt_custom_fields_settings_after_combine', $fields, $post_type );
        wp_cache_set( $post_type . "_field_settings", $fields );
        return $fields;
    }



    public static function get_post_tiles( $post_type, $return_cache = true ){
        $cached = wp_cache_get( $post_type . "_tile_options" );
        if ( $return_cache && $cached ){
            return $cached;
        }
        $tile_options = dt_get_option( "dt_custom_tiles" );
        $default = [
            "status" => [ "label" => __( "Status", 'disciple_tools' ), "tile_priority" => 10 ],
            "details" => [ "label" => __( "Details", 'disciple_tools' ), "tile_priority" => 20 ]
        ];
        $sections = apply_filters( 'dt_details_additional_tiles', $default, $post_type );
        if ( !isset( $tile_options[$post_type] ) ){
            $tile_options[$post_type] = [];
        }
        $tile_options[$post_type] = dt_array_merge_recursive_distinct( $sections, $tile_options[$post_type] );
        $sections = apply_filters( 'dt_details_additional_section_ids', [], $post_type );
        foreach ( $sections as $section_id ){
            if ( !isset( $tile_options[$post_type][$section_id] ) ) {
                $tile_options[$post_type][$section_id] = [];
            }
        }

        uasort($tile_options[$post_type], function( $a, $b) {
            return ( $a['tile_priority'] ?? 100 ) <=> ( $b['tile_priority'] ?? 100 );
        });
        foreach ( $tile_options[$post_type] as $tile_key => &$tile_value ){
            if ( !isset( $tile_value["tile_priority"] ) ){
                $tile_options[$post_type][$tile_key]["tile_priority"] = ( array_search( $tile_key, array_keys( $tile_options[$post_type] ) ) + 1 ) * 10;
            }
        }

        wp_cache_set( $post_type . "_tile_options", $tile_options[$post_type] );
        return $tile_options[$post_type];
    }

}


