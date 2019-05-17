<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

class DT_Posts extends Disciple_Tools_Posts {
    public function __construct() {
        parent::__construct();
    }

    /**
     * Create a post
     * For fields format See https://github.com/DiscipleTools/disciple-tools-theme/wiki/Contact-Fields-Format
     *
     * @param string $post_type
     * @param array $fields
     * @param bool $silent
     *
     * @return array|WP_Error
     */
    public static function create_post( string $post_type, array $fields, bool $silent = false ){
        if ( !self::can_create( $post_type ) ){
            return new WP_Error( __FUNCTION__, "You do not have permission for this", [ 'status' => 403 ] );
        }
        $initial_fields = $fields;
        $post_settings = apply_filters( "dt_get_post_type_settings", [], $post_type );
        $continue = apply_filters( "dt_create_post_check_proceed", true, $fields );
        if ( !$continue ){
            return new WP_Error( __FUNCTION__, "Could not create this post. Maybe it already exists", [ 'status' => 409 ] );
        }
        if ( !isset( $fields ["title"] ) ) {
            return new WP_Error( __FUNCTION__, "title needed", [ 'fields' => $fields ] );
        }

        $title = $fields["title"];
        unset( $fields["title"] );

        $create_date = null;
        if ( isset( $fields["create_date"] )){
            $create_date = $fields["create_date"];
            unset( $fields["create_date"] );
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
        //@todo or defaults
        $fields = apply_filters( "dt_create_post_fields_", $fields );
//        @todo assigned to


        $allowed_fields = apply_filters( "dt_post_create_allow_fields", [], $post_type );
        $bad_fields = self::check_for_invalid_post_fields( $post_settings, $fields, $allowed_fields );
        if ( !empty( $bad_fields ) ) {
            return new WP_Error( __FUNCTION__, "One or more fields do not exist", [
                'bad_fields' => $bad_fields,
                'status' => 400
            ] );
        }

        $defaults = apply_filters( "dt_post_create_defaults", [], $post_type );

        $fields = array_merge( $defaults, $fields );

        $contact_methods_and_connections = [];
        $multi_select_fields = [];
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
            if ( $field_type === 'date' && !is_numeric( $field_value )){
                $fields[$field_value] = strtotime( $field_value );
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
        if ( $create_date ){
            $post["post_date"] = $create_date;
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
            do_action( "dt_post_created", $post_id, $initial_fields );
            if ( !$silent ){
                Disciple_Tools_Notifications::insert_notification_for_new_post( $post_type, $fields, $post_id );
            }
        }
        return self::get_post( $post_type, $post_id );
    }


    /**
     * Update post
     * For fields format See https://github.com/DiscipleTools/disciple-tools-theme/wiki/Contact-Fields-Format
     *
     * @param string $post_type
     * @param int $post_id
     * @param array $fields
     * @param bool $silent
     *
     * @return array|WP_Error
     */
    public static function update_post( string $post_type, int $post_id, array $fields, bool $silent ){
        $post_types = apply_filters( 'dt_registered_post_types', [ 'contacts', 'groups' ] );
        if ( !in_array( $post_type, $post_types ) ){
            return new WP_Error( __FUNCTION__, "Post type does not exist", [ 'status' => 403 ] );
        }
        if ( !self::can_update( $post_type, $post_id ) ){
            return new WP_Error( __FUNCTION__, "You do not have permission for this", [ 'status' => 403 ] );
        }
        $post_settings = apply_filters( "dt_get_post_type_settings", [], $post_type );
        $initial_fields = $fields;
        $post = get_post( $post_id );
        if ( !$post ) {
            return new WP_Error( __FUNCTION__, "post does not exist" );
        }
        $allowed_fields = apply_filters( "dt_post_update_allow_fields", [], $post_type );
        $bad_fields = self::check_for_invalid_post_fields( $post_settings, $fields, $allowed_fields );
        if ( !empty( $bad_fields ) ) {
            return new WP_Error( __FUNCTION__, "One or more fields do not exist", [
                'bad_fields' => $bad_fields,
                'status' => 400
            ] );
        }
        $existing_contact = self::get_post( $post_type, $post_id );

        if ( isset( $fields['title'] ) && $existing_contact["title"] != $fields['title'] ) {
            wp_update_post( [
                'ID' => $post_id,
                'post_title' => $fields['title']
            ] );
            dt_activity_insert( [
                'action'            => 'field_update',
                'object_type'       => $post_type,
                'object_subtype'    => 'title',
                'object_id'         => $post_id,
                'object_name'       => $fields['title'],
                'meta_key'          => 'title',
                'meta_value'        => $fields['title'],
                'old_value'         => $existing_contact['title'],
            ] );
        }
        $potential_error = self::update_post_contact_methods( $post_settings, $post_id, $fields, $existing_contact );
        if ( is_wp_error( $potential_error )){
            return $potential_error;
        }

        $potential_error = self::update_connections( $post_settings, $post_id, $fields, $existing_contact );
        if ( is_wp_error( $potential_error )){
            return $potential_error;
        }

        $potential_error = self::update_multi_select_fields( $post_settings["fields"], $post_id, $fields, $existing_contact );
        if ( is_wp_error( $potential_error )){
            return $potential_error;
        }

//        @todo assigned to

        $fields["last_modified"] = time(); //make sure the last modified field is updated.
        foreach ( $fields as $field_key => $field_value ){
            if ( !self::is_post_key_contact_method_or_connection( $post_settings, $field_key ) ) {
                $field_type = $post_settings["fields"][ $field_key ]["type"] ?? '';
                if ( $field_type === 'date' && !is_numeric( $field_value ) ) {
                    $field_value = strtotime( $field_value );
                }
                if ( $field_type && $field_type !== "multi_select" ){
                    update_post_meta( $post_id, $field_key, $field_value );
                }
            }
        }

        $post = self::get_post( $post_type, $post_id, false );
        if ( !is_wp_error( $post )){
            do_action( "dt_post_updated", $post_id, $initial_fields );
            if ( !$silent ){
                Disciple_Tools_Notifications::insert_notification_for_new_post( $post_type, $fields, $post_id );
            }
        }

        return $post;
    }


    /**
     * Get Post
     *
     * @param $post_type
     * @param $post_id
     * @param bool $use_cache
     *
     * @return array|WP_Error
     */
    public static function get_post( $post_type, $post_id, $use_cache = true ){
        if ( !self::can_view( $post_type, $post_id ) ) {
            return new WP_Error( __FUNCTION__, "No permissions to read " . $post_type, [ 'status' => 403 ] );
        }
        $cached = wp_cache_get( "post_" . $post_id );
        if ( $cached && $use_cache ){
            return $cached;
        }

        $wp_post = get_post( $post_id );
        $post_settings = apply_filters( "dt_get_post_type_settings", [], $post_type );
        if ( !$wp_post ){
            return new WP_Error( __FUNCTION__, "post does not exist", [ 'status' => 400 ] );
        }
        $fields = [];

        /**
         * add connections
         */
        foreach ( $post_settings["connection_types"] as $connection_type ){
            $field = $post_settings["fields"][$connection_type];
            $args = [
                'connected_type'   => $field["p2p_key"],
                'connected_direction' => $field["p2p_direction"],
                'connected_items'  => $wp_post,
                'nopaging'         => true,
                'suppress_filters' => false,
            ];
            $connections = get_posts( $args );
            $fields[$connection_type] = [];
            foreach ( $connections as $c ){
                $fields[$connection_type][] = self::filter_wp_post_object_fields( $c );
            }
        }

        self::adjust_post_custom_fields( $post_settings, $post_id, $fields );


        $fields["ID"] = $post_id;
        $fields["title"] = $wp_post->post_title;
        $fields["created_date"] = $wp_post->post_date;
        $fields["permalink"] = get_permalink( $post_id );

        $fields = apply_filters( 'dt_after_get_post_fields_filter', $fields );
        wp_cache_set( "post_" . $post_id, $fields );
        return $fields;

    }


    /**
     * Get a list of posts
     * For query format see https://github.com/DiscipleTools/disciple-tools-theme/wiki/Filter-and-Search-Lists
     *
     * @param $post_type
     * @param $search_and_filter_query
     *
     * @return array|WP_Error
     */
    public static function list_posts( $post_type, $search_and_filter_query ){
        $data = self::search_viewable_post( $post_type, $search_and_filter_query );
        if ( is_wp_error( $data ) ) {
            return $data;
        }
        $post_settings = apply_filters( "dt_get_post_type_settings", [], $post_type );
        $records = $data["posts"];
        foreach ( $post_settings["connection_types"] as $connection_type ){
            $p2p_type = $post_settings["fields"][$connection_type]["p2p_key"];
            p2p_type( $p2p_type )->each_connected( $records, [], $connection_type );
        }

        foreach ( $records as  &$record ){
            foreach ( $post_settings["connection_types"] as $connection_type ){
                foreach ( $record->$connection_type as &$post ) {
                    $post = self::filter_wp_post_object_fields( $post );
                }
            }
            $record = (array) $record;
            self::adjust_post_custom_fields( $post_settings, $record["ID"], $record );
            $record["permalink"] = get_permalink( $record["ID"] );
        }
        $data["posts"] = $records;

        return $data;
    }

}


