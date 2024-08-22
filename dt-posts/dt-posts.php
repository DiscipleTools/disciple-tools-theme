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
    public static $allowable_comment_tags = array(
        'a' => array(
          'href' => array(),
          'title' => array()
        ),
        'br' => array(),
        'em' => array(),
        'strong' => array(),
    );

    public static function get_post_types(){
        return array_unique( apply_filters( 'dt_registered_post_types', [ 'contacts', 'groups' ] ) );
    }

    /**
     * Get settings on the post type
     *
     * @param string $post_type
     *
     * @return array|WP_Error
     */
    public static function get_post_settings( string $post_type, $return_cache = true, $load_tags = false ){
        $cached = wp_cache_get( $post_type . '_post_type_settings' );
        if ( $return_cache && $cached ){
            return $cached;
        }
        $settings = [];
        $settings['tiles'] = self::get_post_tiles( $post_type );
        $settings = apply_filters( 'dt_get_post_type_settings', $settings, $post_type, $return_cache, $load_tags );
        wp_cache_set( $post_type . '_post_type_settings', $settings );
        return $settings;
    }

    /**
     * CRUD
     */

    /**
     * Create a post
     * For fields format See https://github.com/DiscipleTools/disciple-tools-theme/wiki/Contact-Fields-Format
     *
     * breadcrumb: new-field-type
     *
     * @param string $post_type
     * @param array $fields
     * @param bool $silent
     * @param bool $check_permissions
     *
     * @return array|WP_Error
     */
    public static function create_post( string $post_type, array $fields, bool $silent = false, bool $check_permissions = true, $args = [] ){
        if ( $check_permissions && !self::can_create( $post_type ) ){
            return new WP_Error( __FUNCTION__, 'You do not have permission for this', [ 'status' => 403 ] );
        }
        $initial_fields = $fields;
        $post_settings = self::get_post_settings( $post_type );

        //check to see if we want to create this contact.
        //could be used to check for duplicates first
        $continue = apply_filters( 'dt_create_post_check_proceed', true, $fields, $post_type );
        if ( is_wp_error( $continue ) ){
            return $continue;
        }
        if ( !$continue ){
            return new WP_Error( __FUNCTION__, 'Could not create this post. Maybe it already exists', [ 'status' => 409 ] );
        }

        $args = apply_filters( 'dt_create_post_args', $args, $post_type, $fields );

        //if specified, check for actual duplicates.
        if ( isset( $args['check_for_duplicates'] ) && is_array( $args['check_for_duplicates'] ) && ! empty( $args['check_for_duplicates'] ) ) {
            $duplicate_post_ids = apply_filters( 'dt_create_check_for_duplicate_posts', [], $post_type, $fields, $args['check_for_duplicates'], $check_permissions );
            if ( ! empty( $duplicate_post_ids ) && count( $duplicate_post_ids ) > 0 ) {

                $name = $fields['name'] ?? $fields['title'];

                $fields['notes'] = isset( $fields['notes'] ) ? $fields['notes'] : [];
                //No need to update title or name.
                unset( $fields['title'], $fields['name'] );

                //update most recently created matched post.
                $updated_post = self::update_post( $post_type, $duplicate_post_ids[0], $fields, $silent, false );
                if ( is_wp_error( $updated_post ) ){
                    return $updated_post;
                }
                //if update successful, comment and return.
                $update_comment = __( 'This record was automatically updated rather than creating a new record.', 'disciple_tools' );
                if ( !$silent ){
                    if ( isset( $updated_post['assigned_to']['id'], $updated_post['assigned_to']['display'] ) ) {
                        $update_comment = '@[' . $updated_post['assigned_to']['display'] . '](' . $updated_post['assigned_to']['id'] . ') ' . $update_comment;
                    }
                }
                //add a second to the comments so when we display logs activity shows first and then comments.
                $comment_date = dt_format_date( time() -1, 'Y-m-d H:i:s' );
                self::add_post_comment( $updated_post['post_type'], $updated_post['ID'], $update_comment, 'comment', [ 'comment_date' => $comment_date ], false, $silent );

                if ( $check_permissions && !self::can_view( $post_type, $updated_post['ID'] ) ){
                    return [ 'ID' => $updated_post['ID'] ];
                } else {
                    return $updated_post;
                }
            }
        }

        //get extra fields and defaults
        $fields = apply_filters( 'dt_post_create_fields', $fields, $post_type );
        $filtered_initial_fields = $fields;

        //set title
        if ( !isset( $fields['title'] ) && !isset( $fields['name'] ) ) {
            return new WP_Error( __FUNCTION__, 'title needed', [ 'fields' => $fields ] );
        }
        $title = null;
        if ( isset( $fields['title'] ) ){
            $title = $fields['title'];
            unset( $fields['title'] );
        }
        if ( isset( $fields['name'] ) ){
            $title = $fields['name'];
            unset( $fields['name'] );
        }
        if ( empty( $title ) ){
            return new WP_Error( __FUNCTION__, 'Name/Title field can not be empty', [ 'status' => 400 ] );
        }

        $post_date = null;
        if ( isset( $fields['post_date'] ) ){
            $post_date = $fields['post_date'];
            unset( $fields['post_date'] );
        }
        $initial_comment = null;
        if ( isset( $fields['initial_comment'] ) ) {
            $initial_comment = $fields['initial_comment'];
            unset( $fields['initial_comment'] );
        }
        $notes = null;
        if ( isset( $fields['notes'] ) ) {
            if ( is_array( $fields['notes'] ) ) {
                $notes = $fields['notes'];
                unset( $fields['notes'] );
            } else {
                return new WP_Error( __FUNCTION__, "'notes' field expected to be an array" );
            }
        }


        if ( isset( $fields['additional_meta'] ) ){
            if ( isset( $fields['additional_meta']['created_from'], $fields['additional_meta']['add_connection'] ) ){
                $created_from_post_type = get_post_type( $fields['additional_meta']['created_from'] );
                $created_from_field_settings = self::get_post_field_settings( $created_from_post_type );
                if ( isset( $created_from_field_settings[$fields['additional_meta']['add_connection']]['p2p_key'] ) ){
                    $connection_field = $fields['additional_meta']['add_connection'];
                    foreach ( $post_settings['fields'] as $field_key => $field_options ){
                        if ( $created_from_field_settings[$fields['additional_meta']['add_connection']]['p2p_key'] === ( $field_options['p2p_key'] ?? '' ) && $field_key !== $fields['additional_meta']['add_connection'] ){
                            $connection_field = $field_key;
                        }
                    }
                    $fields[$connection_field] = [ 'values' => [ [ 'value' => $fields['additional_meta']['created_from'] ] ] ];
                }
            }
            unset( $fields['additional_meta'] );
        }

        $allowed_fields = apply_filters( 'dt_post_create_allow_fields', [], $post_type );
        $bad_fields = self::check_for_invalid_post_fields( $post_settings, $fields, $allowed_fields );
        if ( !empty( $bad_fields ) ) {
            return new WP_Error( __FUNCTION__, 'One or more fields do not exist', [
                'bad_fields' => $bad_fields,
                'status' => 400
            ] );
        }

        if ( !isset( $fields['last_modified'] ) ){
            $fields['last_modified'] = time();
        }

        $contact_methods_and_connections = [];
        $multi_select_fields = [];
        $location_meta = [];
        $post_user_meta = [];
        $user_select_fields = [];
        $link_meta = [];
        foreach ( $fields as $field_key => $field_value ){
            if ( self::is_post_key_contact_method_or_connection( $post_settings, $field_key ) ) {
                $contact_methods_and_connections[$field_key] = $field_value;
                unset( $fields[$field_key] );
            }
            $field_type = $post_settings['fields'][$field_key]['type'] ?? '';
            $is_private = $post_settings['fields'][$field_key]['private'] ?? '';
            if ( $field_type === 'multi_select' ){
                $multi_select_fields[$field_key] = $field_value;
                unset( $fields[$field_key] );
            }
            if ( $field_type === 'link' && !$is_private ) {
                $link_meta[$field_key] = $field_value;
                unset( $fields[$field_key] );
            }
            if ( $field_type === 'tags' ){
                $multi_select_fields[$field_key] = $field_value;
                unset( $fields[$field_key] );
            }
            if ( $field_type === 'location_meta' || $field_type === 'location' ){
                $location_meta[$field_key] = $field_value;
                unset( $fields[$field_key] );
            }
            if ( $field_type === 'task' ){
                $post_user_meta[$field_key] = $field_value;
                unset( $fields[ $field_key ] );
            }
            if ( ( $field_type === 'date' || $field_type === 'datetime' ) && !is_numeric( $field_value ) ){
                if ( $is_private ) {
                    $post_user_meta[$field_key] = strtotime( $field_value );
                    unset( $fields[ $field_key ] );
                } else {
                    $fields[$field_key] = strtotime( $field_value );
                }
            }
            if ( $field_type === 'key_select' && !is_string( $field_value ) ){
                return new WP_Error( __FUNCTION__, "key_select value must in string format: $field_key, received $field_value", [ 'status' => 400 ] );
            }
            if ( $field_type === 'key_select' && $is_private ) {
                $post_user_meta[$field_key] = $field_value;
                unset( $fields[ $field_key ] );
            }
            if ( $field_type === 'user_select' ) {
                $user_select_fields[$field_key] = $field_value;
                unset( $fields[ $field_key ] );
            }
            if ( $field_type === 'boolean' && $is_private ) {
                $post_user_meta[$field_key] = $field_value;
                unset( $fields[ $field_key ] );
            }
            if ( $field_type === 'number' && $field_value !== '' && (
                isset( $post_settings['fields'][$field_key]['min_option'] ) && ( !empty( $post_settings['fields'][$field_key]['min_option'] ) || $post_settings['fields'][$field_key]['min_option'] === 0 ) &&
                $field_value < $post_settings['fields'][$field_key]['min_option'] ||
                isset( $post_settings['fields'][$field_key]['max_option'] ) && ( !empty( $post_settings['fields'][$field_key]['max_option'] ) || $post_settings['fields'][$field_key]['max_option'] === 0 ) &&
                $field_value > $post_settings['fields'][$field_key]['max_option']
                )
            ) {
                return new WP_Error( __FUNCTION__, "number value must be within min, max bounds: $field_key, received $field_value", [ 'status' => 400 ] );
            }
            if ( $field_type === 'number' && $is_private ) {
                $post_user_meta[$field_key] = $field_value;
                unset( $fields[ $field_key ] );
            }
            if ( $field_type === 'text' && $is_private ) {
                $post_user_meta[$field_key] = $field_value;
                unset( $fields[ $field_key ] );
            }
            if ( $field_type === 'textarea' && $is_private ) {
                $post_user_meta[$field_key] = $field_value;
                unset( $fields[ $field_key ] );
            }
            if ( $field_type === 'link' && $is_private ) {
                $post_user_meta[$field_key] = $field_value;
                unset( $fields[ $field_key ] );
            }
            if ( $is_private ) {
                unset( $fields[ $field_key ] );
            }
        }
        /**
         * Create the post
         */
        $post = [
            'post_title'  => $title,
            'post_type'   => $post_type,
            'post_status' => 'publish',
            'meta_input'  => $fields,
        ];
        if ( $post_date ){
            $post['post_date'] = $post_date;
        }
        $post_id = wp_insert_post( $post );
        if ( is_wp_error( $post_id ) ){
            return $post_id;
        }

        $potential_error = self::update_post_contact_methods( $post_settings, $post_id, $contact_methods_and_connections );
        if ( is_wp_error( $potential_error ) ){
            return $potential_error;
        }

        $potential_error = self::update_connections( $post_settings, $post_id, $contact_methods_and_connections, null );
        if ( is_wp_error( $potential_error ) ){
            return $potential_error;
        }

        $potential_error = self::update_multi_select_fields( $post_settings['fields'], $post_id, $multi_select_fields, null );
        if ( is_wp_error( $potential_error ) ){
            return $potential_error;
        }

        $potential_error = self::update_location_grid_fields( $post_settings['fields'], $post_id, $location_meta, $post_type, null );
        if ( is_wp_error( $potential_error ) ) {
            return $potential_error;
        }

        $potential_error = self::update_post_user_meta_fields( $post_settings['fields'], $post_id, $post_user_meta, [] );
        if ( is_wp_error( $potential_error ) ){
            return $potential_error;
        }

        $potential_error = self::update_post_user_select( $post_type, $post_id, $user_select_fields );
        if ( is_wp_error( $potential_error ) ){
            return $potential_error;
        }

        $potential_error = self::update_post_link_fields( $post_settings['fields'], $post_id, $link_meta );
        if ( is_wp_error( $potential_error ) ){
            return $potential_error;
        }

        if ( $initial_comment ) {
            $potential_error = self::add_post_comment( $post_type, $post_id, $initial_comment, 'comment', [], false );
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
                $potential_error = self::add_post_comment( $post_type, $post_id, $note, 'comment', [], false, true );
                if ( is_wp_error( $potential_error ) ) {
                    $error->add( 'comment_fail', $potential_error->get_error_message() );
                }
            }
            if ( count( $error->get_error_messages() ) > 0 ) {
                return $error;
            }
        }


        //hook for signaling that a post has been created and the initial fields
        do_action( 'dt_post_created', $post_type, $post_id, $initial_fields, $args );
        if ( !$silent ){
            Disciple_Tools_Notifications::insert_notification_for_new_post( $post_type, $filtered_initial_fields, $post_id );
        }

        // share the record with the user that created it.
        if ( !empty( get_current_user_id() ) ){
            self::add_shared( $post_type, $post_id, get_current_user_id(), null, false, false, false );
        } else {
            //a post should at least be shared with one person.
            $shared_with = self::get_shared_with( $post_type, $post_id, false );
            if ( empty( $shared_with ) ){
                $base_id = dt_get_base_user( true );
                self::add_shared( $post_type, $post_id, $base_id, null, !$silent, false, false );
            }
        }

        if ( $check_permissions && !self::can_view( $post_type, $post_id ) ){
            return [ 'ID' => $post_id ];
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
            return new WP_Error( __FUNCTION__, 'Post type does not exist', [ 'status' => 403 ] );
        }
        if ( $check_permissions && !self::can_update( $post_type, $post_id ) ){
            return new WP_Error( __FUNCTION__, "You do not have permission to update $post_type with ID $post_id", [ 'status' => 403 ] );
        }
        //check to see if we want to update this record.
        $continue = apply_filters( 'dt_update_post_check_proceed', true, $fields, $post_type );
        if ( is_wp_error( $continue ) ){
            return $continue;
        }
        if ( !$continue ){
            return new WP_Error( __FUNCTION__, 'Could not update this post.', [ 'status' => 409 ] );
        }

        $post_settings = self::get_post_settings( $post_type );
        $initial_fields = $fields;
        $post = get_post( $post_id );
        if ( !$post ) {
            return new WP_Error( __FUNCTION__, 'post does not exist', [ 'status' => 404 ] );
        }

        $existing_post = self::get_post( $post_type, $post_id, false, false );
        //get extra fields and defaults
        $fields = apply_filters( 'dt_post_update_fields', $fields, $post_type, $post_id, $existing_post );
        if ( is_wp_error( $fields ) ){
            return $fields;
        }

        $notes = null;
        if ( isset( $fields['notes'] ) ) {
            if ( is_array( $fields['notes'] ) ) {
                $notes = $fields['notes'];
                unset( $fields['notes'] );
            }
        }

        $allowed_fields = apply_filters( 'dt_post_update_allow_fields', [], $post_type );
        $bad_fields = self::check_for_invalid_post_fields( $post_settings, $fields, $allowed_fields );
        if ( !empty( $bad_fields ) ) {
            return new WP_Error( __FUNCTION__, 'One or more fields do not exist', [
                'bad_fields' => $bad_fields,
                'status' => 400
            ] );
        }

        //set title
        if ( isset( $fields['title'] ) || isset( $fields['name'] ) ) {
            $title = $fields['title'] ?? $fields['name'];
            if ( empty( $title ) ){
                return new WP_Error( __FUNCTION__, 'Name/Title field can not be empty', [ 'status' => 400 ] );
            }
            if ( $existing_post['name'] != $title ) {
                wp_update_post( [
                    'ID' => $post_id,
                    'post_title' => $title
                ] );
                dt_activity_insert( [
                    'action'            => 'field_update',
                    'object_type'       => $post_type,
                    'object_subtype'    => 'name',
                    'object_id'         => $post_id,
                    'object_name'       => $title,
                    'meta_key'          => 'name',
                    'meta_value'        => $title,
                    'old_value'         => $existing_post['name'],
                    'field_type' => $post_settings['fields']['name']['type'] ?? 'text' // Always default to text!
                ] );
            }
            if ( isset( $fields['name'] ) ){
                unset( $fields['name'] );
            }
        }

        /*
        breadcrumb: new-field-type
        If necessary deal with field types differently from the default way
        Functions are found in posts.php
        */

        $potential_error = self::update_post_contact_methods( $post_settings, $post_id, $fields, $existing_post );
        if ( is_wp_error( $potential_error ) ){
            return $potential_error;
        }

        $potential_error = self::update_connections( $post_settings, $post_id, $fields, $existing_post );
        if ( is_wp_error( $potential_error ) ){
            return $potential_error;
        }

        $potential_error = self::update_multi_select_fields( $post_settings['fields'], $post_id, $fields, $existing_post );
        if ( is_wp_error( $potential_error ) ){
            return $potential_error;
        }

        $potential_error = self::update_location_grid_fields( $post_settings['fields'], $post_id, $fields, $post_type, $existing_post );
        if ( is_wp_error( $potential_error ) ) {
            return $potential_error;
        }

        $potential_error = self::update_post_user_meta_fields( $post_settings['fields'], $post_id, $fields, $existing_post );
        if ( is_wp_error( $potential_error ) ){
            return $potential_error;
        }

        $potential_error = self::update_post_user_select( $post_type, $post_id, $fields );
        if ( is_wp_error( $potential_error ) ){
            return $potential_error;
        }

        $potential_error = self::update_post_link_fields( $post_settings['fields'], $post_id, $fields );
        if ( is_wp_error( $potential_error ) ){
            return $potential_error;
        }

        $fields['last_modified'] = time(); //make sure the last modified field is updated.
        foreach ( $fields as $field_key => $field_value ){
            if ( !self::is_post_key_contact_method_or_connection( $post_settings, $field_key ) ) {
                $field_type = $post_settings['fields'][ $field_key ]['type'] ?? '';
                if ( ( $field_type === 'date' || $field_type === 'datetime' ) && !is_numeric( $field_value ) ) {
                    if ( empty( $field_value ) ) { // remove date
                        delete_post_meta( $post_id, $field_key );
                        continue;
                    }
                    $field_value = strtotime( $field_value );
                }

                if ( $field_type === 'number' && $field_value !== '' && (
                    isset( $post_settings['fields'][$field_key]['min_option'] ) && ( !empty( $post_settings['fields'][$field_key]['min_option'] ) || $post_settings['fields'][$field_key]['min_option'] === 0 ) &&
                    $field_value < $post_settings['fields'][$field_key]['min_option'] ||
                    isset( $post_settings['fields'][$field_key]['max_option'] ) && ( !empty( $post_settings['fields'][$field_key]['max_option'] ) || $post_settings['fields'][$field_key]['max_option'] === 0 ) &&
                    $field_value > $post_settings['fields'][$field_key]['max_option']
                    )
                ) {
                     return new WP_Error( __FUNCTION__, "number value must be within min, max bounds: $field_key, received $field_value", [ 'status' => 400 ] );
                }

                if ( $field_type === 'key_select' && !is_string( $field_value ) ){
                    return new WP_Error( __FUNCTION__, "key_select value must in string format: $field_key, received $field_value", [ 'status' => 400 ] );
                }
                /**
                 * Custom Handled Meta
                 *
                 * This filter includes the types of fields handled in the above section, but can have a new
                 * field type included, so that it can be skipped here and handled later through the
                 * dt_post_updated action.
                 */
                $already_handled = apply_filters( 'dt_post_updated_custom_handled_meta', [ 'multi_select', 'post_user_meta', 'location', 'location_meta', 'communication_channel', 'tags', 'user_select', 'link' ], $post_type );
                if ( $field_type && !in_array( $field_type, $already_handled ) ) {
                    if ( !( isset( $post_settings['fields'][$field_key]['private'] ) && $post_settings['fields'][$field_key]['private'] ) ){
                        update_post_meta( $post_id, $field_key, $field_value );
                    }
                }
            }
        }

        if ( ! empty( $notes ) ) {
            if ( ! is_array( $notes ) ) {
                return new WP_Error( 'notes_not_array', 'Notes must be an array' );
            }
            $error = new WP_Error();
            foreach ( $notes as $note ) {
                $potential_error = self::add_post_comment( $post_type, $post_id, $note, 'comment', [], false, true );
                if ( is_wp_error( $potential_error ) ) {
                    $error->add( 'comment_fail', $potential_error->get_error_message() );
                }
            }
            if ( count( $error->get_error_messages() ) > 0 ) {
                return $error;
            }
        }

        $post = self::get_post( $post_type, $post_id, false, false ); // get post to add to action hook
        do_action( 'dt_post_updated', $post_type, $post_id, $initial_fields, $existing_post, $post );
        $post = self::get_post( $post_type, $post_id, false, false ); // get post with fields updated by action hook
        if ( !$silent ){
            Disciple_Tools_Notifications::insert_notification_for_post_update( $post_type, $post, $existing_post, array_keys( $fields ) );
        }

        if ( $check_permissions && !self::can_view( $post_type, $post_id ) ){
            return [ 'ID' => $post_id ];
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
        global $wpdb;

        if ( $check_permissions && !self::can_view( $post_type, $post_id ) ) {
            return new WP_Error( __FUNCTION__, "No permissions to read $post_type with ID $post_id", [ 'status' => 403 ] );
        }
        //@todo re-enable when load order is implemented.
        //$post_types = self::get_post_types();
        //if ( !in_array( $post_type, $post_types ) ){
        //    return new WP_Error( __FUNCTION__, "$post_type in not a valid post type", [ 'status' => 400 ] );
        //}
        $current_user_id = get_current_user_id();
        $cached = wp_cache_get( 'post_' . $current_user_id . '_' . $post_id );
        if ( $cached && $use_cache ){
            return $cached;
        }

        $wp_post = get_post( $post_id );
        $field_settings = self::get_post_field_settings( $post_type );
        if ( empty( $field_settings ) ){
            return new WP_Error( __FUNCTION__, 'post type not yet set up. Please load in a hook.', [ 'status' => 400 ] );
        }
        if ( !$wp_post ){
            return new WP_Error( __FUNCTION__, 'post does not exist', [ 'status' => 400 ] );
        }
        if ( $use_cache === true && $current_user_id && !$silent ){
            dt_activity_insert( [
                'action' => 'viewed',
                'object_type' => $post_type,
                'object_id' => $post_id,
                'object_name' => $wp_post->post_title
            ] );
        }

        /**
         * add connections
         */
        $p = [ [ 'ID' => $post_id ] ];
        self::get_all_connected_fields_on_list( $field_settings, $p );
        $fields = $p[0];
        $fields['ID'] = $post_id;
        $fields['post_date'] = [
            'timestamp' => is_numeric( $wp_post->post_date ) ? $wp_post->post_date : dt_format_date( $wp_post->post_date, 'U' ),
            'formatted' => dt_format_date( $wp_post->post_date )
        ];
        $fields['permalink'] = get_permalink( $post_id );
        $fields['post_type'] = $post_type;
        $fields['post_author'] = $wp_post->post_author;
        $author = get_user_by( 'ID', $wp_post->post_author );
        $fields['post_author_display_name'] = $author ? $author->display_name : '';

        $all_user_meta = $wpdb->get_results( $wpdb->prepare( "
            SELECT *
            FROM $wpdb->dt_post_user_meta um
            WHERE um.post_id = %s
            AND user_id = %s
        ", $post_id, $current_user_id ), ARRAY_A);

        $all_post_user_meta =[];

        foreach ( $all_user_meta as $index => $meta_row ){
            if ( !isset( $field_settings[$meta_row['meta_key']]['type'] ) ){
                continue;
            }
            if ( !isset( $all_post_user_meta[$meta_row['post_id']] ) ){
                $all_post_user_meta[$meta_row['post_id']] = [];
            }
            if ( $field_settings[$meta_row['meta_key']]['type'] === 'task' ) {
                $all_post_user_meta[$meta_row['post_id']][] = $meta_row;
            } else if ( isset( $field_settings[$meta_row['meta_key']]['private'] ) && $field_settings[$meta_row['meta_key']]['private'] ) {
                $all_post_user_meta[$meta_row['post_id']][] = $meta_row;
            }
        }

        self::adjust_post_custom_fields( $post_type, $post_id, $fields, [], null, $all_post_user_meta[ $post_id ] ?? null );
        $fields['name'] = wp_specialchars_decode( $wp_post->post_title );
        $fields['title'] = wp_specialchars_decode( $wp_post->post_title );

        $fields = apply_filters( 'dt_after_get_post_fields_filter', $fields, $post_type );
        wp_cache_set( 'post_' . $current_user_id . '_' . $post_id, $fields );

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
        if ( isset( $search_and_filter_query['fields_to_return'] ) ){
            $fields_to_return = $search_and_filter_query['fields_to_return'];
            unset( $search_and_filter_query['fields_to_return'] );
        }
        if ( isset( $search_and_filter_query['dt_recent'] ) ){
            $data = self::get_recently_viewed_posts( $post_type );
        } else {
            $data = self::search_viewable_post( $post_type, $search_and_filter_query, $check_permissions );
        }
        if ( is_wp_error( $data ) ) {
            return $data;
        }
        $post_settings = self::get_post_settings( $post_type );
        $records = $data['posts'];

        $ids = [];
        foreach ( $records as &$record ) {
            $record = (array) $record;
            $record['post_title'] = wp_specialchars_decode( $record['post_title'] );
            $ids[] = $record['ID'];
        }
        $ids_sql = dt_array_to_sql( $ids );

        $field_keys = [];
        if ( !in_array( 'all_fields', $fields_to_return ) ){
            $field_keys = empty( $fields_to_return ) ? array_keys( $post_settings['fields'] ) : $fields_to_return;
        }
        /* Insert link field combo keys into the $field_keys array */
        foreach ( $field_keys as $key ) {
            if ( isset( $post_settings['fields'][$key] ) && $post_settings['fields'][$key]['type'] === 'link' ) {
                unset( $field_keys[$key] );

                foreach ( $post_settings['fields'][$key]['default'] as $type => $_ ) {
                    $meta_key = Disciple_Tools_Posts::create_link_metakey( $key, $type );

                    $field_keys[] = $meta_key;
                }
            }
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
        ", ARRAY_A );
        $user_id = get_current_user_id();
        $all_user_meta = $wpdb->get_results( $wpdb->prepare( "
            SELECT *
            FROM $wpdb->dt_post_user_meta um
            WHERE um.post_id IN ( $ids_sql )
            AND user_id = %s
            AND um.meta_key IN ( $field_keys_sql )
        ", $user_id ), ARRAY_A);
        // phpcs:enable

        foreach ( $all_post_meta as $index => $meta_row ) {
            if ( !isset( $all_posts[$meta_row['post_id']] ) ) {
                $all_posts[$meta_row['post_id']] = [];
            }
            if ( !isset( $all_posts[$meta_row['post_id']][$meta_row['meta_key']] ) ) {
                $all_posts[$meta_row['post_id']][$meta_row['meta_key']] = [];
            }
            $all_posts[$meta_row['post_id']][$meta_row['meta_key']][] = $meta_row['meta_value'];
        }
        $all_post_user_meta = [];
        foreach ( $all_user_meta as $index => $meta_row ){
            if ( !isset( $all_post_user_meta[$meta_row['post_id']] ) ) {
                $all_post_user_meta[$meta_row['post_id']] = [];
            }
            $all_post_user_meta[$meta_row['post_id']][] = $meta_row;
        }

        self::get_all_connected_fields_on_list( $post_settings['fields'], $records, $fields_to_return );
        $site_url = site_url();
        $dt_storage_enabled = ( class_exists( 'DT_Storage' ) && DT_Storage::is_enabled() );
        foreach ( $records as  &$record ){

            self::adjust_post_custom_fields( $post_type, $record['ID'], $record, $fields_to_return, $all_posts[ $record['ID'] ] ?? [], $all_post_user_meta[ $record['ID'] ] ?? [] );
            $record['permalink'] = $site_url . '/' . $post_type . '/' . $record['ID'];
            $record['name'] = wp_specialchars_decode( $record['post_title'] );
            if ( !isset( $record['post_date']['timestamp'], $record['post_date']['formatted'] ) ){
                $record['post_date'] = [
                    'timestamp' => is_numeric( $record['post_date'] ) ? $record['post_date'] : dt_format_date( $record['post_date'], 'U' ),
                    'formatted' => dt_format_date( $record['post_date'] )
                ];
            }
        }
        $data['posts'] = $records;

        $data = apply_filters( 'dt_list_posts_custom_fields', $data, $post_type );

        return $data;
    }

    /**
     * Get a list of split by summaries for given field.
     *
     * @param string $post_type
     * @param array $args
     * @param bool $check_permissions
     *
     * @return array|WP_Error
     */
    public static function split_by( string $post_type, array $args, bool $check_permissions = true ){
        if ( $check_permissions && !self::can_access( $post_type ) ){
            return new WP_Error( __FUNCTION__, 'You do not have access to these', [ 'status' => 403 ] );
        }
        $post_types = self::get_post_types();
        if ( !in_array( $post_type, $post_types ) ){
            return new WP_Error( __FUNCTION__, "$post_type in not a valid post type", [ 'status' => 400 ] );
        }

        $field_key = $args['field_id'] ?? '';
        if ( empty( $field_key ) ){
            return new WP_Error( __FUNCTION__, 'Empty field id detected', [ 'status' => 400 ] );
        }

        $query = $args['filters'] ?? [];

        //filter in to add or remove query parameters.
        $query = apply_filters( 'dt_search_viewable_posts_query', $query );

        global $wpdb;

        $post_settings = self::get_post_settings( $post_type );
        if ( !isset( $post_settings['fields'] ) || empty( $post_settings['fields'] ) ){
            return new WP_Error( __FUNCTION__, "$post_type settings not yet loaded", [ 'status' => 400 ] );
        }
        $post_fields = $post_settings['fields'];

        $search = '';
        if ( isset( $query['text'] ) ){
            $search = sanitize_text_field( $query['text'] );
            unset( $query['text'] );
        }
        if ( isset( $query['offset'] ) ){
            unset( $query['offset'] );
        }
        if ( isset( $query['limit'] ) ){
            unset( $query['limit'] );
        }
        if ( isset( $query['sort'] ) ){
            unset( $query['sort'] );
        }
        $fields_to_search = [];
        if ( isset( $query['fields_to_search'] ) ){
            $fields_to_search = $query['fields_to_search'];
            unset( $query ['fields_to_search'] );
        }
        if ( isset( $query['combine'] ) ){
            unset( $query['combine'] ); //remove deprecated combine
        }
        if ( isset( $query['fields'] ) ){
            $query = $query['fields'];
        }
        if ( isset( $query['fields_to_return'] ) ){
            unset( $query['fields_to_return'] );
        }

        $joins = '';
        $post_query = '';

        if ( !empty( $search ) ){

            // Support wildcard searching between string tokens.
            if ( !is_numeric( $search ) ){
                $search = str_replace( ' ', '%', $search );
            }

            $other_search_fields = [];
            if ( empty( $fields_to_search ) ){
                $other_search_fields = apply_filters( 'dt_search_extra_post_meta_fields', [] );
                $post_query .= "AND ( ( p.post_title LIKE '%" . esc_sql( $search ) . "%' )
                    OR p.ID IN ( SELECT post_id
                                FROM $wpdb->postmeta
                                WHERE meta_key LIKE 'contact_%'
                                AND REPLACE( meta_value, ' ', '') LIKE '%" . esc_sql( str_replace( ' ', '', $search ) ) . "%'
                    )
                ";
            }
            if ( !empty( $fields_to_search ) ){
                if ( in_array( 'name', $fields_to_search ) ){
                    $post_query .= "AND ( ( p.post_title LIKE '%" . esc_sql( $search ) . "%' )";
                } else {
                    $post_query .= 'AND ( ';
                }

                if ( in_array( 'comms', $fields_to_search ) ){
                    if ( substr( $post_query, -6 ) !== 'AND ( ' ){
                        $post_query .= 'OR ';
                    }

                    $post_query .= "p.ID IN ( SELECT post_id
                                    FROM $wpdb->postmeta
                                    WHERE meta_key LIKE 'contact_%'
                                    AND REPLACE( meta_value, ' ', '') LIKE '%" . esc_sql( str_replace( ' ', '', $search ) ) . "%'
                        )
                    ";
                }

                if ( in_array( 'all', $fields_to_search ) ){
                    if ( substr( $post_query, -6 ) !== 'AND ( ' ){
                        $post_query .= 'OR ';
                    }
                    $user_id = get_current_user_id();
                    $post_query .= "p.ID IN ( SELECT comment_post_ID
                    FROM $wpdb->comments
                    WHERE comment_content LIKE '%" . esc_sql( $search ) . "%'
                    ) OR p.ID IN ( SELECT post_id
                    FROM $wpdb->postmeta
                    WHERE meta_value LIKE '%" . esc_sql( $search ) . "%'
                    ) OR p.ID IN ( SELECT post_id
                    FROM $wpdb->dt_post_user_meta
                    WHERE user_id = $user_id
                    AND meta_value LIKE '%" . esc_sql( $search ) . "%'
                    ) ";
                } else {
                    if ( in_array( 'comment', $fields_to_search ) ){
                        if ( substr( $post_query, -6 ) !== 'AND ( ' ){
                            $post_query .= 'OR ';
                        }
                        $post_query .= " p.ID IN ( SELECT comment_post_ID
                        FROM $wpdb->comments
                        WHERE comment_content LIKE '%" . esc_sql( $search ) . "%'
                        ) ";
                    }
                    foreach ( $fields_to_search as $field ){
                        if ( $field !== 'comment' ){
                            array_push( $other_search_fields, $field );
                        }
                    }
                }
            }
            foreach ( $other_search_fields as $field ){
                if ( substr( $post_query, -6 ) !== 'AND ( ' ){
                    $post_query .= 'OR ';
                }
                $post_query .= "p.ID IN ( SELECT post_id
                             FROM $wpdb->postmeta
                             WHERE meta_key LIKE '" . esc_sql( $field ) . "'
                             AND meta_value LIKE '%" . esc_sql( $search ) . "%'
                ) ";
            }
            $post_query .= ' ) ';
        }

        $permissions = [
            'shared_with' => [ 'me' ]
        ];
        $permissions = apply_filters( 'dt_filter_access_permissions', $permissions, $post_type );

        if ( $check_permissions && !empty( $permissions ) ){
            $query[] = $permissions;
        }

        $fields_sql = self::fields_to_sql( $post_type, $query );
        if ( is_wp_error( $fields_sql ) ){
            return $fields_sql;
        }

        $group_by_field_type = isset( $post_fields[$field_key]['type'] ) ? $post_fields[$field_key]['type'] : null;

        $posts = [];
        $initial_results = [];
        if ( $group_by_field_type === 'connection' ){
            $p2p_post_type = $post_fields[$field_key]['post_type'] ?? '';
            $p2p_key = $post_fields[$field_key]['p2p_key'] ?? '';
            $p2p_direction = $post_fields[$field_key]['p2p_direction'] ?? '';

            $group_by_join = "LEFT JOIN $wpdb->p2p group_by ON group_by." . ( ( ( $p2p_direction == 'from' ) || ( $p2p_direction == 'any' ) ) ? 'p2p_from' : 'p2p_to' ) . " = p.ID AND group_by.p2p_type = '" . esc_sql( $p2p_key ) . "'";
            if ( $p2p_direction === 'any' ){
                $group_by_join = "LEFT JOIN $wpdb->p2p group_by ON ( group_by.p2p_from = p.ID OR group_by.p2p_to = p.ID ) AND group_by.p2p_type = '" . esc_sql( $p2p_key ) . "'";
            }

            // phpcs:disable
            // WordPress.WP.PreparedSQL.NotPrepared
            $connections = $wpdb->get_results( "
                SELECT p.ID as id, group_by.p2p_from, group_by.p2p_to, group_by.p2p_type as value
                FROM $wpdb->posts p " . $fields_sql['joins_sql'] . ' ' . $joins . ' ' .
                $group_by_join . "
                WHERE " . $fields_sql['where_sql'] . ' ' . ( empty( $fields_sql['where_sql'] ) ? '' : ' AND ' ) . "
                (p.post_status = 'publish') AND p.post_type = '" . esc_sql( $post_type ) . "' " . $post_query . "
                GROUP BY p.ID, group_by.p2p_from, group_by.p2p_to, group_by.p2p_type"
            , ARRAY_A );

            // Collate records accordingly based on from/to id target.
            if ( !empty( $p2p_post_type ) ){
                foreach ( $connections as $connection ){
                    $p2p_target = ( ( $p2p_direction == 'from' ) || ( $p2p_direction == 'any' ) ) ? 'p2p_to' : 'p2p_from';
                    if ( empty( $connection['p2p_from'] ) && empty( $connection['p2p_to'] ) && empty( $connection['value'] ) ){
                        $initial_results[] = [
                            'id' => null,
                            'value' => null
                        ];
                    }elseif ( $p2p_direction === 'any' && !empty( $connection['p2p_from'] ) ){
                        $initial_results[] = [
                            'id' => $connection['p2p_from'],
                            'value' => $connection['p2p_from']
                        ];
                    }elseif ( !empty( $connection[$p2p_target] ) ){
                        $initial_results[] = [
                            'id' => $connection[$p2p_target],
                            'value' => $connection[$p2p_target]
                        ];
                    }
                }
            }
            // Reshape initial results findings.
            $reshaped_results = [];
            foreach ( $initial_results as $result ){
                $reshaped_keys = $result['value'] ?? 'NULL';
                if ( !isset( $reshaped_results[$reshaped_keys] ) ){
                    $reshaped_results[$reshaped_keys] = [
                        'value' => $reshaped_keys,
                        'count' => 0
                    ];
                }
                $reshaped_results[$reshaped_keys]['count']++;
            }

            // Now, reshape into required posts structure.
            foreach ( $reshaped_results as $result ){
                $posts[] = [
                    'value' => $result['value'],
                    'count' => $result['count']
                ];
            }
        } else {
            $group_by_join = "LEFT JOIN $wpdb->postmeta group_by ON group_by.post_id = p.ID AND group_by.meta_key = '" . esc_sql( $field_key ) . "'";

            // phpcs:disable
            // WordPress.WP.PreparedSQL.NotPrepared
            $posts = $wpdb->get_results( "
                SELECT COUNT( DISTINCT( p.ID) ) as count, group_by.meta_value as value
                FROM $wpdb->posts p " . $fields_sql['joins_sql'] . ' ' . $joins . ' ' .
                    $group_by_join . '
                WHERE ' . $fields_sql['where_sql'] . ' ' . ( empty( $fields_sql['where_sql'] ) ? '' : ' AND ' ) . "
                (p.post_status = 'publish') AND p.post_type = '" . esc_sql( $post_type ) . "' " . $post_query . '
                GROUP BY group_by.meta_value'
            , ARRAY_A );
            // phpcs:enable
        }

        // Determine appropriate labels to be used.
        $updated_posts = [];
        $geocoder = new Location_Grid_Geocoder();
        foreach ( $posts as $post ){
            if ( ( $post['value'] === 'NULL' ) || ( $post['value'] === null ) ){
                $post['value'] = 'NULL';
                $post['label'] = __( 'None Set', 'disciple_tools' );
                $updated_posts[] = $post;
            } elseif ( $group_by_field_type == 'location' ){
                $grid = $geocoder->query_by_grid_id( $post['value'] );
                $post['label'] = $grid['name'] ?? $post['value'];
                $updated_posts[] = $post;
            } elseif ( $group_by_field_type == 'location_meta' ){
                $post['label'] = Disciple_Tools_Mapping_Queries::get_location_grid_meta_label( $post['value'] ) ?? $post['value'];
                $updated_posts[] = $post;
            } elseif ( $group_by_field_type == 'user_select' ){
                $user_id = dt_get_user_id_from_assigned_to( $post['value'] );
                $post['label'] = dt_get_user_display_name( $user_id ) ?: $post['value'];
                $post['value'] = $user_id;
                $updated_posts[] = $post;
            } elseif ( $group_by_field_type == 'boolean' ){
                $post['label'] = $post['value'] ? _x( 'True', 'disciple_tools' ) : _x( 'False', 'disciple_tools' );
                if ( !$post['value'] && intval( $post['count'] ) > 0 ){
                    $post['value'] = '0';
                }
                $updated_posts[] = $post;
            } elseif ( $group_by_field_type === 'connection' ){
                $p2p_post_type = $post_fields[$field_key]['post_type'] ?? '';
                if ( !empty( $p2p_post_type ) ){
                    $wp_post = get_post( $post['value'] );
                    if ( !is_wp_error( $wp_post ) ){
                        $post['label'] = $wp_post->post_title ?? $post['label'];
                        $updated_posts[] = $post;
                    }
                }
            } else {
                $post['label'] = $post_fields[$field_key]['default'][$post['value']]['label'] ?? $post['value'];
                $updated_posts[] = $post;
            }
        }

        // Sort returning posts by count.
        usort( $updated_posts, function ( $a, $b ){
            if ( $a['count'] == $b['count'] ){
                return 0;
            }
            return ( $a['count'] > $b['count'] ) ? -1 : 1;
        } );

        // Return split by summary based on specified limit.
        return array_splice( $updated_posts, 0, 30 );
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
        if ( !self::can_access( $post_type ) && !self::can_list_all( $post_type ) ) {
            return new WP_Error( __FUNCTION__, sprintf( 'You do not have access to these %s', $post_type ), [ 'status' => 403 ] );
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
                    'ID' => (int) $post->ID,
                    'name' => $post->post_title,
                    'user' => false,
                    'status' => null
                ];
            }
        }

        /**
         * Empty Search String
         * Return the most recent posts viewed by the user
         * and recently chosen value for this field
         */
        $send_quick_results = false;
        if ( empty( $search_string ) ){
            $field_settings = self::get_post_field_settings( $post_type );
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
            if ( isset( $args['field_key'], $field_settings[$args['field_key']] ) && $field_settings[$args['field_key']]['type'] === 'connection' ){
                $action = 'connected to';
                $field_type = 'connection from';
                if ( $field_settings[$args['field_key']]['p2p_direction'] === 'from' ){
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
                            WHERE l.user_id = %s  AND l.action = %s AND l.object_type = %s AND l.meta_key = %s AND (l.field_type = %s OR l.object_note = %s)
                            group by l.object_id
                        ) x on log.histid = x.maxid
                    ORDER BY log.histid desc
                    LIMIT 5
                    ) as log
                    ON log.object_id = p.ID
                    WHERE p.post_type = %s AND (p.post_status = 'publish' OR p.post_status = 'private')

                ", $current_user->ID, $action, $post_type, $field_settings[$args['field_key']]['p2p_key'], $field_type, $field_type, $post_type ), OBJECT );

                $post_ids = array_map(
                    function ( $post ) { return (int) $post->ID; }, $posts
                );
                foreach ( $posts_2 as $p ){
                    if ( !in_array( (int) $p->ID, $post_ids, true ) ){
                        $posts[] = $p;
                    }
                }
            }
            if ( !empty( $posts ) && sizeof( $posts ) > 2 ){
                $send_quick_results = true;
            }
        }


        /**
         * Use the search string to find connections
         */
        if ( !$send_quick_results ){
            $query = [ 'limit' => 50 ];
            if ( !empty( $search_string ) ){
                $query['name'] = [ $search_string ];
            }
            $query['sort'] = '-last_modified';
            $query = apply_filters( 'dt_get_viewable_compact_search_query', $query, $post_type, $search_string, $args );
            // if user can't list_all_, check permissions so they don't get access to things they shouldn't
            $check_permissions = !self::can_list_all( $post_type );
            $posts_list = self::search_viewable_post( $post_type, $query, $check_permissions );
            if ( is_wp_error( $posts_list ) ){
                return $posts_list;
            }
            $posts = $posts_list['posts'];
        }

        if ( is_wp_error( $posts ) ) {
            return $posts;
        }

        $post_ids = array_map(
            function( $post ) {
                return (int) $post->ID;
            },
            $posts
        );

        //filter out users if requested.
        foreach ( $posts as $post ) {
            if ( isset( $args['include-users'] ) && $args['include-users'] === 'false' && property_exists( $post, 'corresponds_to_user' ) && $post->corresponds_to_user >= 1 ){
                continue;
            }
            $compact[] = [
                'ID' => (int) $post->ID,
                'name' => wp_specialchars_decode( $post->post_title )
            ];
        }

        //add in user results when searching contacts.
        if ( $post_type === 'contacts' && !self::can_view_all( $post_type )
             && ! ( isset( $args['include-users'] ) && $args['include-users'] === 'false' )
        ) {
            $users_interacted_with = Disciple_Tools_Users::get_assignable_users_compact( $search_string );
            $users_interacted_with = array_slice( $users_interacted_with, 0, 5 );
            if ( $current_user && ( empty( $search_string ) || strpos( strtolower( $current_user->display_name ), strtolower( $search_string ) ) !== false ) ){
                array_unshift( $users_interacted_with, [
                    'name' => $current_user->display_name,
                    'ID'   => $current_user->ID
                ] );
            }
            foreach ( $users_interacted_with as $user ) {
                $post_id = Disciple_Tools_Users::get_contact_for_user( $user['ID'] );
                if ( $post_id ){
                    if ( !in_array( $post_id, $post_ids, true ) ) {
                        $post_ids[] = $post_id;
                        $compact[] = [
                            'ID' => (int) $post_id,
                            'name' => $user['name'],
                            'user' => true
                        ];
                    }
                }
            }
        }


        //set user field if the contact is a user.
        if ( $post_type === 'contacts' ){
            $post_ids_sql = dt_array_to_sql( $post_ids );

            // phpcs:disable
            // WordPress.WP.PreparedSQL.NotPrepared
            $user_post_ids = $wpdb->get_results( "
                SELECT post_id, meta_value
                FROM $wpdb->postmeta pm
                WHERE pm.post_id in ( $post_ids_sql )
                AND meta_key = 'corresponds_to_user'
                ", ARRAY_A
            );
            // phpcs:enable

            foreach ( $user_post_ids as $res ){
                foreach ( $compact as $index => &$p ){
                    if ( $p['ID'] === (int) $res['post_id'] ){
                        $compact[$index]['user'] = true;
                    }
                }
            }
            if ( !empty( $search_string ) ){
                //place user records first, and then exact matches
                uasort( $compact, function ( $a, $b ) use ( $search_string ) {
                    if ( isset( $a['user'] ) && !empty( $a['user'] ) ){
                        return - 3;
                    } else if ( isset( $b['user'] ) && !empty( $b['user'] ) ){
                        return 2;
                    } elseif ( $a['name'] === $search_string ){
                        return - 2;
                    } else if ( $b['name'] === $search_string ){
                        return 1;
                    } else {
                        return 0;
                    }
                });
            }
        }

        if ( $post_type === 'peoplegroups' ){
            $list = [];
            $locale = get_user_locale();

            foreach ( $posts as $post ) {
                $translation = get_post_meta( $post->ID, $locale, true );
                if ( $translation !== '' ) {
                    $label = $translation;
                } else {
                    $label = $post->post_title;
                }
                foreach ( $compact as $index => &$p ){
                    if ( $compact[ $index ]['ID'] === $post->ID ) {
                        $compact[ $index ] = [
                            'ID'    => (int) $post->ID,
                            'name'  => $post->post_title,
                            'label' => $label
                            ];
                    }
                }
            }
        }

        // Capture corresponding post record statuses, apply filters and return
        return apply_filters( 'dt_get_viewable_compact', [
            'total' => sizeof( $compact ),
            'posts' => self::capture_viewable_compact_post_record_status( $post_type, array_slice( $compact, 0, 50 ) )
        ], $post_type, $search_string, $args );
    }

    /**
     * Capture and update viewable compact post record status
     *
     * @param string $post_type
     * @param array $posts
     *
     * @return array
     */
    public static function capture_viewable_compact_post_record_status( string $post_type, array $posts ): array {

        // Ensure there are valid posts to process
        if ( empty( $posts ) ) {
            return $posts;
        }

        // Collate all compact ids
        $compact_ids = [];
        foreach ( $posts as $compact ) {
            $compact_ids[] = $compact['ID'];
        }

        // Determine current status meta values for identified ids
        $compact_statuses = self::get_post_status( $compact_ids, $post_type );

        // Iterate over compact post records and update status reference, accordingly
        $updated_records = [];
        foreach ( $posts as $compact ) {
            if ( isset( $compact_statuses[ $compact['ID'] ] ) ) {
                $compact['status'] = $compact_statuses[ $compact['ID'] ];
            }

            // Capture updated compact record
            $updated_records[] = $compact;
        }

        return $updated_records;
    }

    /**
     * Comments
     */

    /**
     * @param string $post_type
     * @param int $post_id
     * @param string $comment_html
     * @param string $type      normally 'comment', different comment types can have their own section in the comments activity, use "dt_comments_additional_sections" to add custom comment types
     * @param array $args       [user_id, comment_date, comment_author etc]
     * @param bool $check_permissions
     * @param bool $silent
     *
     * @return false|int|WP_Error
     */
    public static function add_post_comment( string $post_type, int $post_id, string $comment_html, string $type = 'comment', array $args = [], bool $check_permissions = true, $silent = false ) {
        if ( $check_permissions && !self::can_update( $post_type, $post_id ) ) {
            return new WP_Error( __FUNCTION__, 'You do not have permission for this', [ 'status' => 403 ] );
        }

        // If present, ensure specified date format is correct
        if ( ! empty( $args['comment_date'] ) && ! dt_validate_date( $args['comment_date'] ) ) {
            return new WP_Error( __FUNCTION__, 'Invalid date! Correct format should be: Y-m-d H:i:s', [ 'status' => 403 ] );
        }

        //limit comment length to 5000
        $comments = str_split( $comment_html, 4999 );
        $user = wp_get_current_user();
        $user_id = $args['user_id'] ?? get_current_user_id();

        // Adhere to db comment_type 20 char constraint.
        if ( strlen( $type ) > 20 ) {
            $type = substr( $type, 0, 20 );
        }

        $created_comment_id = null;
        foreach ( $comments as $comment ){
            $comment_data = [
                'comment_post_ID'      => $post_id,
                'comment_content'      => wp_kses( $comment, self::$allowable_comment_tags ),
                'user_id'              => $user_id,
                'comment_author'       => $args['comment_author'] ?? $user->display_name,
                'comment_author_url'   => $args['comment_author_url'] ?? '',
                'comment_author_email' => $user->user_email,
                'comment_type'         => $type,
            ];
            if ( isset( $args['comment_date'] ) ){
                $comment_data['comment_date'] = $args['comment_date'];
                $comment_data['comment_date_gmt'] = $args['comment_date'];
            }
            $new_comment = wp_new_comment( $comment_data );
            if ( !$created_comment_id ){
                $created_comment_id = $new_comment;
            }

            if ( $new_comment && array_key_exists( 'comment_meta', $args ) ) {
                foreach ( $args['comment_meta'] as $key => $value ) {
                    if ( is_array( $value ) ) {
                        foreach ( $value as $meta_val ) {
                            add_comment_meta( $new_comment, $key, $meta_val );
                        }
                    } else {
                        add_comment_meta( $new_comment, $key, $value );
                    }
                }
            }
        }

        if ( !$silent && !is_wp_error( $created_comment_id ) ){
            Disciple_Tools_Notifications_Comments::insert_notification_for_comment( $created_comment_id );
        }
        if ( !is_wp_error( $created_comment_id ) ){
            do_action( 'dt_comment_created', $post_type, $post_id, $created_comment_id, $type );
        }
        return $created_comment_id;
    }

    public static function update_post_comment( int $comment_id, string $comment_content, bool $check_permissions = true, string $comment_type = 'comment', array $args = [] ){
        $comment = get_comment( $comment_id );
        if ( $check_permissions && ( ( isset( $comment->user_id ) && $comment->user_id != get_current_user_id() ) || !self::can_update( get_post_type( $comment->comment_post_ID ), $comment->comment_post_ID ?? 0 ) ) ) {
            return new WP_Error( __FUNCTION__, "You don't have permission to edit this comment", [ 'status' => 403 ] );
        }
        if ( !$comment ){
            return new WP_Error( __FUNCTION__, 'No comment found with id: ' . $comment_id, [ 'status' => 403 ] );
        }
        $comment = [
            'comment_content' => $comment_content,
            'comment_ID' => $comment_id,
            'comment_type' => $comment_type
        ];
        $update = wp_update_comment( $comment );
        if ( in_array( $update, [ 0, 1 ] ) ) {
            if ( key_exists( 'comment_meta', $args ) && $args['comment_meta'] ) {
                foreach ( $args['comment_meta'] as $key => $value ) {
                    update_comment_meta( $comment_id, $key, $value );
                }
            }
            return $comment_id;
        } else if ( is_wp_error( $update ) ) {
             return $update;
        } else {
            return new WP_Error( __FUNCTION__, 'Error updating comment with id: ' . $comment_id, [ 'status' => 500 ] );
        }
    }

    public static function delete_post_comment( int $comment_id, bool $check_permissions = true ){
        $comment = get_comment( $comment_id );
        if ( $check_permissions && ( ( isset( $comment->user_id ) && $comment->user_id != get_current_user_id() ) || !self::can_update( get_post_type( $comment->comment_post_ID ), $comment->comment_post_ID ?? 0 ) ) ) {
            return new WP_Error( __FUNCTION__, "You don't have permission to delete this comment", [ 'status' => 403 ] );
        }
        if ( !$comment ){
            return new WP_Error( __FUNCTION__, 'No comment found with id: ' . $comment_id, [ 'status' => 403 ] );
        }
        return wp_delete_comment( $comment_id );
    }

    public static function toggle_post_comment_reaction( string $post_type, int $post_id, int $comment_id, string $reaction ){
        if ( !self::can_update( $post_type, $post_id ) ) {
            return new WP_Error( __FUNCTION__, 'You do not have permission for this', [ 'status' => 403 ] );
        }
        $user_id = get_current_user_id();
        // If the reaction exists for this user, then delete it
        $reactions = get_comment_meta( $comment_id, $reaction );
        foreach ( $reactions as $reaction_user_id ) {
            if ( $reaction_user_id == $user_id ) {
                delete_comment_meta( $comment_id, $reaction, $reaction_user_id );
                return true;
            }
        }

        // otherwise add it.
        add_comment_meta( $comment_id, $reaction, $user_id );
        return $reactions;
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
    public static function get_post_comments( string $post_type, int $post_id, bool $check_permissions = true, string $type = 'all', array $args = [] ) {
        global $wpdb;
        if ( $check_permissions && !self::can_view( $post_type, $post_id ) ) {
            return new WP_Error( __FUNCTION__, 'No permissions to read post', [ 'status' => 403 ] );
        }
        //setting type to "comment" does not work.
        $comments_query = [
            'post_id' => $post_id,
            'type' => $type
        ];
        if ( isset( $args['offset'] ) || isset( $args['number'] ) ){
            $comments_query['offset'] = $args['offset'] ?? 0;
            $comments_query['number'] = $args['number'] ?? '';
        }
        $comments = get_comments( $comments_query );

        $comments_meta = self::get_post_comments_meta( $post_id );

        $response_body = [];
        foreach ( $comments as $comment ){
            $url = '';
            if ( $comment->comment_author_url ){
                $url = str_replace( '&amp;', '&', $comment->comment_author_url );
            } else if ( !empty( $comment->user_id ) ){
                $url = get_avatar_url( $comment->user_id, [ 'size' => '16', 'scheme' => 'https' ] );
            }
            $c = [
                'comment_ID' => $comment->comment_ID,
                'comment_author' => !empty( $display_name ) ? $display_name : wp_specialchars_decode( $comment->comment_author ),
                'comment_date' => $comment->comment_date,
                'comment_date_gmt' => $comment->comment_date_gmt,
                'gravatar' => preg_replace( '/^http:/i', 'https:', $url ),
                'comment_content' => $comment->comment_content,
                'user_id' => $comment->user_id,
                'comment_type' => $comment->comment_type,
                'comment_post_ID' => $comment->comment_post_ID,
                'comment_reactions' => array_key_exists( $comment->comment_ID, $comments_meta['reactions'] ) ? $comments_meta['reactions'][$comment->comment_ID] : [],
                'comment_meta' => array_key_exists( $comment->comment_ID, $comments_meta['meta'] ) ? $comments_meta['meta'][$comment->comment_ID] : [],
            ];
            $response_body[] = $c;
        }

        $response_body = apply_filters( 'dt_filter_post_comments', $response_body, $post_type, $post_id );

        foreach ( $response_body as &$comment ){
            $comment['comment_content'] = wp_kses( $comment['comment_content'], self::$allowable_comment_tags );
        }

        return [
            'comments' => $response_body,
            'total' => wp_count_comments( $post_id )->total_comments
        ];
    }

    /**
     * Get comment meta for a given post, split into reactions vs general meta data
     * @param int $post_id
     * @return array
     * @internal
     */
    private static function get_post_comments_meta( int $post_id ) {
        global $wpdb;

        // add in getting the meta data for the comments JOINed with the user table to get
        // the username
        // phpcs:disable
        // WordPress.WP.PreparedSQL.NotPrepared
        $comments_meta = $wpdb->get_results( $wpdb->prepare(
            "SELECT
                m.comment_id, m.meta_id, m.meta_key, m.meta_value, u.display_name, u.ID
            FROM
                `$wpdb->comments` AS c
            JOIN
                `$wpdb->commentmeta` AS m
            ON c.comment_ID = m.comment_id
            LEFT JOIN
                `$wpdb->users` AS u
            ON m.meta_value = u.ID
            WHERE
                c.comment_post_ID = %s",
            $post_id
        ) );
        // phpcs:enable

        $comments_reactions_dict = [];
        $comments_meta_dict = [];
        foreach ( $comments_meta as $meta ) {

            // if meta_key starts with "reaction"...
            if ( strpos( $meta->meta_key, 'reaction' ) === 0 ) {
                if ( !array_key_exists( $meta->comment_id, $comments_reactions_dict ) ) {
                    $comments_reactions_dict[$meta->comment_id] = [];
                }
                if ( !array_key_exists( $meta->meta_key, $comments_reactions_dict[$meta->comment_id] ) ) {
                    $comments_reactions_dict[$meta->comment_id][$meta->meta_key] = [];
                }

                // reaction meta data as a list of users who have reacted
                $comments_reactions_dict[$meta->comment_id][$meta->meta_key][] = [
                    'name' => $meta->display_name,
                    'user_id' => $meta->ID,
                ];
            } else {
                // all non-reaction meta data
                if ( !array_key_exists( $meta->comment_id, $comments_meta_dict ) ) {
                    $comments_meta_dict[$meta->comment_id] = [];
                }
                if ( !array_key_exists( $meta->meta_key, $comments_meta_dict[$meta->comment_id] ) ) {
                    $comments_meta_dict[$meta->comment_id][$meta->meta_key] = [];
                }
                $comments_meta_dict[$meta->comment_id][$meta->meta_key][] = [
                    'id' => $meta->meta_id,
                    'value' => $meta->meta_value,
                ];
            }
        }

        return [
            'reactions' => $comments_reactions_dict,
            'meta' => $comments_meta_dict,
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
            return new WP_Error( __FUNCTION__, 'No permissions to read: ' . $post_type, [ 'status' => 403 ] );
        }

        // Follow the appropriate activity retrieval path....
        if ( isset( $args['revert'] ) && $args['revert'] ){
            return self::list_revert_post_activity_history( $post_type, $post_id, $args );
        } else {
            $post_settings = self::get_post_settings( $post_type );
            $fields = $post_settings['fields'];
            $hidden_fields = [];
            foreach ( $fields as $field_key => $field ){
                if ( isset( $field['hidden'] ) && $field['hidden'] === true ){
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
            foreach ( $activity as $a ){
                $a->object_note = self::format_activity_message( $a, $post_settings );
                $a->object_note = sanitize_text_field( $a->object_note );
                if ( isset( $a->user_id ) && $a->user_id > 0 ){
                    $user = get_user_by( 'id', $a->user_id );
                    if ( $user ){
                        $a->name = $user->display_name;
                        $a->gravatar = get_avatar_url( $user->ID, [ 'size' => '16', 'scheme' => 'https' ] );
                    }
                } else if ( isset( $a->user_caps ) && strlen( $a->user_caps ) === 32 ){
                    //get site-link name
                    $site_link = Site_Link_System::get_post_id_by_site_key( $a->user_caps );
                    if ( $site_link ){
                        $a->name = get_the_title( $site_link );
                    }
                } else if ( isset( $a->user_caps ) && $a->user_caps === 'magic_link' ){
                    $a->name = sprintf( __( '%s Submission', 'disciple_tools' ), apply_filters( 'dt_magic_link_global_name', __( 'Magic Link', 'disciple_tools' ) ) );
                } else if ( isset( $a->user_caps ) && $a->user_caps === 'activity_revert' ){
                    $a->name = __( 'Revert Bot', 'disciple_tools' );
                }
                if ( !empty( $a->object_note ) ){
                    $activity_obj = [
                        'meta_key' => $a->meta_key,
                        'gravatar' => isset( $a->gravatar ) ? $a->gravatar : '',
                        'name' => isset( $a->name ) ? wp_specialchars_decode( $a->name ) : __( 'D.T System', 'disciple_tools' ),
                        'object_note' => $a->object_note,
                        'hist_time' => $a->hist_time,
                        'meta_id' => $a->meta_id,
                        'histid' => $a->histid,
                        'field_type' => $a->field_type,
                    ];

                    $activity_simple[] = apply_filters( 'dt_format_post_activity', $activity_obj, $a );
                }
            }

            $paged = array_slice( $activity_simple, $args['offset'] ?? 0, $args['number'] ?? 1000 );
            return [
                'activity' => $paged,
                'total' => sizeof( $activity_simple )
            ];
        }
    }

    /**
     * @param string $post_type
     * @param int $post_id
     * @param array $args
     *
     * @return array|null|object|WP_Error
     */
    private static function list_revert_post_activity_history( string $post_type, int $post_id, array $args = [] ) {
        global $wpdb;

        // Determine key query parameters
        $supported_actions         = ( ! empty( $args['actions'] ) ) ? $args['actions'] : [
            'field_update',
            'connected to',
            'disconnected from'
        ];
        $supported_actions_sql     = dt_array_to_sql( $supported_actions );
        $supported_field_types     = ( ! empty( $args['field_types'] ) ) ? $args['field_types'] : [
            'connection',
            'user_select',
            'multi_select',
            'tags',
            'link',
            'location',
            'location_meta',
            'key_select',
            'date',
            'datetime',
            'boolean',
            'communication_channel',
            'text',
            'textarea',
            'number',
            'connection to',
            'connection from',
            ''
        ];
        $supported_field_types_sql = dt_array_to_sql( $supported_field_types );
        $ts_start                  = ( ! empty( $args['ts_start'] ) ) ? $args['ts_start'] : 0;
        $ts_end                    = ( ! empty( $args['ts_end'] ) ) ? $args['ts_end'] : time();
        $result_order              = esc_sql( ( ! empty( $args['result_order'] ) ) ? $args['result_order'] : 'DESC' );
        $extra_meta                = ! empty( $args['extra_meta'] ) && $args['extra_meta'];

        // Fetch post activity history
        // phpcs:disable
        // WordPress.WP.PreparedSQL.NotPrepared
        $activities = $wpdb->get_results( $wpdb->prepare(
            "SELECT
                *
            FROM
                `$wpdb->dt_activity_log`
            WHERE
                `object_type` = %s
                AND `object_id` = %s
                AND `action` IN ( $supported_actions_sql )
                AND `field_type` IN ( $supported_field_types_sql )
                AND `hist_time` BETWEEN %d AND %d
            ORDER BY hist_time $result_order",
            $post_type,
            $post_id,
            $ts_start,
            $ts_end
        ) );
        //@phpcs:enable

        // Format activity message
        $post_settings = self::get_post_settings( $post_type );
        foreach ( $activities as &$activity ) {
            $activity->object_note_raw = $activity->object_note;
            $activity->object_note = sanitize_text_field( self::format_activity_message( $activity, $post_settings ) );
        }

        // Determine if extra metadata has been requested
        if ( $extra_meta ) {
            foreach ( $activities as &$activity ) {
                if ( isset( $activity->user_id ) && $activity->user_id > 0 ) {
                    $user = get_user_by( 'id', $activity->user_id );
                    if ( $user ) {
                        $activity->name     = sanitize_text_field( $user->display_name );
                        $activity->gravatar = get_avatar_url( $user->ID, [ 'size' => '16', 'scheme' => 'https' ] );
                    }
                }
            }
        }

        return $activities;
    }

    public static function revert_post_activity_history( string $post_type, int $post_id, array $args = [] ){
        if ( !self::can_view( $post_type, $post_id ) ){
            return new WP_Error( __FUNCTION__, 'No permissions to read: ' . $post_type, [ 'status' => 403 ] );
        }

        /**
         * Fetch all associated activities from current time to specified revert
         * date. Ensure most recent activities are first in line.
         */

        $args['result_order'] = 'DESC';
        $activities = self::list_revert_post_activity_history( $post_type, $post_id, $args );

        /**
         * March back in time to revert date, adjusting fields accordingly.
         */

        $reverted_start_ts_id = $args['ts_start_id'] ?? 0;
        $reverted_start_ts_found = false;

        $reverted_updates = [];
        $post_type_fields = self::get_post_field_settings( $post_type, false );
        foreach ( $activities ?? [] as &$activity ) {
            $activity_id = $activity->histid;
            $field_action = $activity->action;
            $field_type = $activity->field_type;
            $field_key = $activity->meta_key;
            $field_value = $activity->meta_value;
            $field_old_value = $activity->old_value;
            $field_note_raw = $activity->object_note_raw;
            $is_deleted = strtolower( trim( $field_value ) ) == 'value_deleted';

            // Ensure to accommodate special case field types.
            if ( in_array( $field_action, [ 'connected to', 'disconnected from' ] ) ) {

                // Determine actual field key to be used.
                $field_setting = self::get_post_field_settings_by_p2p( $post_type_fields, $field_key, ( $field_action == 'disconnected from' ) ? [ 'from', 'to', 'any' ] : [ 'to', 'from', 'any' ] );
                if ( ! empty( $field_setting ) ) {
                    $field_key = $field_setting['key'];
                    $field_type = $field_action;

                } else {
                    $field_key  = null;
                    $field_type = null;
                }
            } elseif ( ( empty( $field_type ) || $field_type === 'communication_channel' ) && substr( $field_key, 0, strlen( 'contact_' ) ) == 'contact_' ){
                $field_type = 'communication_channel';

                // Determine actual field key.
                $determined_field_key = null;
                foreach ( self::get_field_settings_by_type( $post_type, $field_type ) ?? [] as $potential_field_key ){
                    if ( strpos( $field_key, $potential_field_key ) !== false ){
                        $determined_field_key = $potential_field_key;
                    }
                }
                $field_key = $determined_field_key ?? substr( $field_key, 0, strpos( $field_key, '_', strlen( 'contact_' ) ) );

                // Void if key is empty.
                if ( empty( $field_key ) ){
                    $field_key = null;
                    $field_type = null;
                }
            } elseif ( $field_type === 'link' ) {
                foreach ( self::get_field_settings_by_type( $post_type, $field_type ) ?? [] as $link_field ) {
                    if ( strpos( $field_key, $link_field ) !== false ) {
                        $field_key = $link_field;
                    }
                }
            }

            /**
             * Ensure processing is halted once target start activity id has
             * been found.
             */

            if ( $reverted_start_ts_id === $activity_id ){
                $reverted_start_ts_found = true;
            }

            if ( !$reverted_start_ts_found ){

                // If needed, prepare reverted updates array element.
                if ( ! empty( $field_key ) && ! empty( $field_type ) && ! isset( $reverted_updates[ $field_key ] ) ) {
                    $reverted_updates[ $field_key ] = [
                        'field_type' => $field_type,
                        'values'     => []
                    ];
                }

                /**
                 * As we walk back in time, need to operate in the inverse; so, delete is
                 * actually an add and add, is actually, delete!
                 * Also, ensure inverse logic is not carried out once we've reached our
                 * specified revert start point.
                 */

                switch ( $field_type ){
                    case 'connected to':
                    case 'disconnected from':
                        $is_deleted = strtolower( trim( $field_action ) ) == 'disconnected from';

                        $reverted_updates[$field_key]['values'][$field_value] = [
                            'value' => $field_value,
                            'keep' => $is_deleted
                        ];
                        break;
                    case 'tags':
                    case 'date':
                    case 'datetime':
                    case 'link':
                    case 'location':
                    case 'multi_select':
                    case 'location_meta':
                    case 'communication_channel':

                        // Capture any additional metadata, by field type.
                        $meta = [];
                        if ( $field_type === 'communication_channel' ) {
                            $meta = [
                                'meta_key' => $activity->meta_key,
                                'value_key_prefix' => $activity->meta_key . '-'
                            ];
                        }elseif ( $field_type === 'link' ) {
                            $meta = [
                                'meta_id' => $activity->meta_id,
                                'value_key_prefix' => $activity->meta_id . '-'
                            ];
                        } elseif ( $field_type === 'location' ){
                            $meta = [
                                'meta_id' => $activity->meta_id,
                                'value_key_prefix' => $activity->meta_id . '-'
                            ];
                        }

                        // Proceed with capturing reverted updates.
                        $value = $is_deleted ? $field_old_value : $field_value;
                        $reverted_updates[$field_key]['values'][( $meta['value_key_prefix'] ?? '' ) . $value] = [
                            'value' => $value,
                            'keep' => $is_deleted,
                            'note' => $field_note_raw,
                            'meta' => $meta
                        ];

                        // Ensure any detected old values are reinstated!
                        if ( !$is_deleted && !empty( $field_old_value ) ){
                            unset( $reverted_updates[$field_key]['values'][( $meta['value_key_prefix'] ?? '' ) . $field_value] );

                            $reverted_updates[$field_key]['values'][( $meta['value_key_prefix'] ?? '' ) . $field_old_value] = [
                                'value' => $field_old_value,
                                'keep' => true,
                                'note' => $field_note_raw,
                                'meta' => $meta
                            ];
                        }
                        break;
                    case 'text':
                    case 'number':
                    case 'boolean':
                    case 'textarea':
                    case 'key_select':
                    case 'user_select':
                        $reverted_updates[$field_key]['values'][0] = $field_old_value;
                        break;
                }
            }
        }

        /**
         * Package revert findings ahead of final post update; ensuring to remove any
         * field values not present within reverted updates.
         */

        $post_updates = [];
        $post = self::get_post( $post_type, $post_id, false );
        foreach ( $reverted_updates as $field_key => $reverted ) {
            switch ( $reverted['field_type'] ){
                case 'connected to':
                case 'disconnected from':
                    $values = [];
                    foreach ( $reverted['values'] as $revert_key => $revert_obj ){

                        // Keep existing values or add if needed.
                        if ( $revert_obj['keep'] ){
                            $found_existing_option = false;
                            if ( isset( $post[$field_key] ) && is_array( $post[$field_key] ) ){
                                foreach ( $post[$field_key] as $option ){
                                    if ( $revert_key == $option['ID'] ){
                                        $found_existing_option = true;
                                    }
                                }
                            }

                            if ( !$found_existing_option ){
                                $values[] = [
                                    'value' => $revert_obj['value']
                                ];
                            }
                        }elseif ( isset( $post[$field_key] ) && is_array( $post[$field_key] ) ){

                            // Remove any flagged existing values.
                            foreach ( $post[$field_key] as $option ){
                                $id = $option['ID'];
                                if ( $revert_key == $id ){
                                    $values[] = [
                                        'value' => $id,
                                        'delete' => true
                                    ];
                                }
                            }
                        }
                    }

                    // Package any available values to be updated.
                    if ( !empty( $values ) ){
                        $post_updates[$field_key] = [
                            'values' => $values
                        ];
                    }
                    break;
                case 'tags':
                case 'link':
                case 'location':
                case 'multi_select':
                case 'location_meta':
                case 'communication_channel':
                    $values = [];
                    foreach ( $reverted['values'] as $revert_key => $revert_obj ){

                        // Remove any detected revert value key prefixes.
                        if ( isset( $revert_obj['meta'], $revert_obj['meta']['value_key_prefix'] ) && strpos( $revert_key, $revert_obj['meta']['value_key_prefix'] ) !== false ){
                            $revert_key = substr( $revert_key, strlen( $revert_obj['meta']['value_key_prefix'] ) );
                        }

                        // Keep existing values or add if needed.
                        if ( $revert_obj['keep'] ){
                            $found_existing_option = false;
                            if ( isset( $post[$field_key] ) && is_array( $post[$field_key] ) ){
                                foreach ( $post[$field_key] as $option ){

                                    // Determine id to be used, based on field type
                                    if ( $reverted['field_type'] == 'location' ) {
                                        $id = $option['id'];

                                    } elseif ( $reverted['field_type'] == 'location_meta' ) {
                                        $id = $option['grid_meta_id'];

                                    } elseif ( $reverted['field_type'] == 'communication_channel' ) {
                                        $id = $option['value'];

                                    } elseif ( $reverted['field_type'] == 'link' ) {
                                        $id = $option['value'];

                                    } else {
                                        $id = $option;
                                    }

                                    if ( $revert_key == $id ){
                                        $found_existing_option = true;
                                    }
                                }
                            }

                            if ( !$found_existing_option ){

                                // Structure value accordingly based on field type.
                                if ( $reverted['field_type'] == 'location_meta' ){

                                    /**
                                     * Assuming suitable mapping APIs are available, execute a lookup query, based on
                                     * specified location. Construct update value package based on returned hits.
                                     */

                                    if ( !empty( $revert_obj['note'] ) ){
                                        $note = $revert_obj['note'];

                                        if ( class_exists( 'Disciple_Tools_Google_Geocode_API' ) && !empty( Disciple_Tools_Google_Geocode_API::get_key() ) && Disciple_Tools_Google_Geocode_API::get_key() ){
                                            $location = Disciple_Tools_Google_Geocode_API::query_google_api( $note, 'coordinates_only' );
                                            if ( !empty( $location ) ){
                                                $values[] = [
                                                    'lng' => $location['lng'],
                                                    'lat' => $location['lat'],
                                                    'label' => $note
                                                ];
                                            }
                                        } elseif ( class_exists( 'DT_Mapbox_API' ) && !empty( DT_Mapbox_API::get_key() ) && DT_Mapbox_API::get_key() ){
                                            $location = DT_Mapbox_API::lookup( $note );
                                            if ( !empty( $location ) ){
                                                $values[] = [
                                                    'lng' => DT_Mapbox_API::parse_raw_result( $location, 'lng', true ),
                                                    'lat' => DT_Mapbox_API::parse_raw_result( $location, 'lat', true ),
                                                    'label' => DT_Mapbox_API::parse_raw_result( $location, 'place_name', true )
                                                ];
                                            }
                                        }
                                    }
                                } elseif ( $reverted['field_type'] == 'communication_channel' ){
                                    $values[] = [
                                        'key' => $revert_obj['meta']['meta_key'] ?? null,
                                        'value' => $revert_obj['value']
                                    ];
                                } elseif ( $reverted['field_type'] == 'link' ) {
                                    $values[] = [
                                        'type' => 'default',
                                        'value' => $revert_obj['value']
                                    ];
                                } else {
                                    $values[] = [
                                        'value' => $revert_obj['value']
                                    ];
                                }
                            }
                        }elseif ( isset( $post[$field_key] ) && is_array( $post[$field_key] ) ){

                            // Remove any flagged existing values.
                            foreach ( $post[$field_key] as $option ){

                                // Determine id to be used, based on field type
                                if ( $reverted['field_type'] == 'location' ) {
                                    $id = $option['id'];

                                } elseif ( $reverted['field_type'] == 'location_meta' ) {
                                    $id = $option['grid_meta_id'];

                                } elseif ( $reverted['field_type'] == 'communication_channel' ) {

                                    // Force a match if key is found.
                                    if ( $option['key'] === ( $revert_obj['meta']['meta_key'] ?? '' ) ){
                                        $id = $revert_key;
                                    } else {
                                        $id = '';
                                    }
                                } elseif ( $reverted['field_type'] == 'link' ) {

                                    // Force a match if key is found.
                                    if ( $option['meta_id'] === ( $revert_obj['meta']['meta_id'] ?? '' ) ){
                                        $id = $revert_key;
                                    } else {
                                        $id = '';
                                    }
                                } else {
                                    $id = $option;
                                }

                                if ( $revert_key == $id ){

                                    // Determine correct value label to be used.
                                    if ( $reverted['field_type'] == 'location_meta' ) {
                                        $key = 'grid_meta_id';

                                    } elseif ( $reverted['field_type'] == 'communication_channel' ) {
                                        $key = 'key';
                                        $id = $revert_obj['meta']['meta_key'] ?? $revert_key;
                                    } elseif ( $reverted['field_type'] == 'link' ) {
                                        $key = 'meta_id';
                                        $id = $revert_obj['meta']['meta_id'] ?? $revert_key;
                                    } else {
                                        $key = 'value';
                                    }

                                    // Package....
                                    $values[] = [
                                        $key => $id,
                                        'delete' => true
                                    ];
                                }
                            }
                        }
                    }

                    // Package any available values to be updated, accordingly; by field type.
                    if ( !empty( $values ) ){
                        if ( $reverted['field_type'] == 'communication_channel' ){
                            $post_updates[$field_key] = $values;
                        } else {
                            $post_updates[$field_key] = [
                                'values' => $values
                            ];
                        }
                    }
                    break;
                case 'date':
                case 'datetime':
                    $revert_obj = array_values( $reverted['values'] )[0] ?? null;
                    $post_updates[$field_key] = ( !empty( $revert_obj ) && $revert_obj['keep'] ) ? $revert_obj['value'] : '';
                    break;
                case 'number':
                    $number_update_allowed = true;

                    // Ensure to adhere with any min/max bounds, to avoid exceptions!
                    $number = !empty( $reverted['values'][0] ) ? $reverted['values'][0] : 0;
                    if ( isset( $post_type_fields[$field_key], $post_type_fields[$field_key]['min_option'] ) && $post_type_fields[$field_key]['min_option'] > $number ){
                        $number_update_allowed = false;
                    }
                    if ( isset( $post_type_fields[$field_key], $post_type_fields[$field_key]['max_option'] ) && $post_type_fields[$field_key]['max_option'] < $number ){
                        $number_update_allowed = false;
                    }

                    // Only update if number format is valid.
                    if ( $number_update_allowed ){
                        $post_updates[$field_key] = $number;
                    }
                    break;
                case 'user_select':
                    $user_select_value = $reverted['values'][0];
                    if ( $user_select_value != 'user-' ){
                        $post_updates[$field_key] = $user_select_value;
                    } else {
                        $post_updates[$field_key] = '';
                    }
                    break;
                case 'text':
                case 'boolean':
                case 'textarea':
                case 'key_select':
                    $post_updates[$field_key] = $reverted['values'][0] ?? '';
                    break;
            }
        }

        /**
         * Final home-straight - Submit updates to revert post back to specified state.
         * Simply return blank, if no updates are to be made.
         */

        if ( empty( $post_updates ) ){
            return [];
        }

        // Ensure revert activity carried out by Revert Bot, with sufficient permissions.
        $current_user_id = get_current_user_id();
        wp_set_current_user( 0 );
        $revert_bot = wp_get_current_user();
        $revert_bot->add_cap( 'activity_revert' );
        $revert_bot->display_name = __( 'Revert Bot', 'disciple_tools' );

        // Update post based on reverted values.
        $updated_post = self::update_post( $post_type, $post_id, $post_updates, true, false );

        // Revert back to previous user and return.
        wp_set_current_user( $current_user_id );
        return $updated_post;
    }

    public static function get_post_field_settings_by_p2p( $fields, $p2p_key, $p2p_direction ): array {
        foreach ( $fields as $key => $field ) {
            if ( isset( $field['p2p_key'], $field['p2p_direction'] ) && $field['p2p_key'] == $p2p_key && in_array( $field['p2p_direction'], $p2p_direction ) ){
                return [
                    'key' => $key,
                    'settings' => $field
                ];
            }
        }

        return [];
    }

    public static function get_post_single_activity( string $post_type, int $post_id, int $activity_id ) {
        global $wpdb;
        if ( !self::can_view( $post_type, $post_id ) ) {
            return new WP_Error( __FUNCTION__, 'No permissions to read group', [ 'status' => 403 ] );
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
                $user = get_user_by( 'id', $a->user_id );
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
            return new WP_Error( 'no_permission', 'You do not have permission for this', [ 'status' => 403 ] );
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
            $share['display_name'] = wp_specialchars_decode( $display_name );
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
            return new WP_Error( __FUNCTION__, 'You do not have permission to unshare', [ 'status' => 403 ] );
        }

        $assigned_to_meta = get_post_meta( $post_id, 'assigned_to', true );
        if ( !( self::can_update( $post_type, $post_id ) ||
                 get_current_user_id() === $user_id ||
                 dt_get_user_id_from_assigned_to( $assigned_to_meta ) === get_current_user_id() )
        ){
            $name = dt_get_user_display_name( $user_id );
            return new WP_Error( __FUNCTION__, 'You do not have permission to unshare with ' . $name, [ 'status' => 403 ] );
        }


        $table = $wpdb->dt_share;
        $where = [
            'user_id' => $user_id,
            'post_id' => $post_id
        ];
        $result = $wpdb->delete( $table, $where );

        if ( $result == false ) {
            return new WP_Error( 'remove_shared', 'Record not deleted.', [ 'status' => 418 ] );
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
            return new WP_Error( __FUNCTION__, 'You do not have permission for this', [ 'status' => 403 ] );
        }
        if ( $check_permissions && !Disciple_Tools_Users::can_list( $user_id ) ){
            return new WP_Error( __FUNCTION__, 'You do not have permission for this', [ 'status' => 403 ] );
        }
        // if the user we are sharing with does not existing or is not on this subsite
        if ( !Disciple_Tools_Users::is_instance_user( $user_id ) ){
            return false;
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
            return new WP_Error( 'add_shared', __( 'Post already shared with user.', 'disciple_tools' ), [ 'status' => 418 ] );
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
            return new WP_Error( __FUNCTION__, 'You do not have access to: ' . $post_type, [ 'status' => 403 ] );
        }
        $users = [];
        $assigned_to_meta = get_post_meta( $post_id, 'assigned_to', true );
        $assigned_to = dt_get_user_id_from_assigned_to( $assigned_to_meta );
        if ( $post_type === 'contacts' ){
            array_merge( $users, self::get_subassigned_users( $post_id ) );
        }
        $shared_with = self::get_shared_with( $post_type, $post_id, false );
        foreach ( $shared_with as $shared ){
            $users[] = (int) $shared['user_id'];
        }
        $users_follow = get_post_meta( $post_id, 'follow', false );
        foreach ( $users_follow as $follow ){
            if ( !in_array( $follow, $users ) && user_can( $follow, 'view_any_' . $post_type ) ){
                $users[] = $follow;
            }
        }
        $users_unfollow = get_post_meta( $post_id, 'unfollow', false );
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

    public static function get_post_meta_with_ids( $post_id ) {
        global $wpdb;

        $meta = $wpdb->get_results(
            $wpdb->prepare(
                "
                SELECT * FROM $wpdb->postmeta
                WHERE post_id = %d
                ", $post_id
            ), ARRAY_A
        );

        /* sort the meta by meta_key and include the value and id in the subarrays */

        $sorted_meta = [];
        foreach ( $meta as $row ) {
            if ( !isset( $sorted_meta[$row['meta_key']] ) ) {
                $sorted_meta[$row['meta_key']] = [];
            }
            $sorted_meta[$row['meta_key']][] = [
                'value' => $row['meta_value'],
                'meta_id' => $row['meta_id'],
            ];
        }

        return $sorted_meta;
    }

    public static function get_post_field_settings( $post_type, $load_from_cache = true, $with_deleted_options = false, $load_tags = false ){
        $cached = wp_cache_get( $post_type . '_field_settings' );
        if ( $load_from_cache && $cached ){
            return $cached;
        }
        $post_types = self::get_post_types();
        $fields = Disciple_Tools_Post_Type_Template::get_base_post_type_fields();
        $fields = apply_filters( 'dt_custom_fields_settings', $fields, $post_type );

        $langs = dt_get_available_languages();

        foreach ( $fields as $field_key => $field ){
            if ( $field['type'] === 'key_select' || $field['type'] === 'multi_select' ){
                foreach ( $field['default'] as $option_key => $option_value ){
                    if ( !is_array( $option_value ) ){
                        $fields[$field_key]['default'][$option_key] = [ 'label' => $option_value ];
                    }
                }
            }
        }
        $custom_field_options = dt_get_option( 'dt_field_customizations' );
        if ( isset( $custom_field_options[$post_type] ) ){
            foreach ( $custom_field_options[$post_type] as $key => $field ){
                $field_type = $field['type'] ?? $fields[$key]['type'] ?? '';
                if ( $field_type ) {
                    if ( ! isset( $fields[ $key ] ) ){
                        $fields[ $key ] = $field;
                    } else {
                        foreach ( $field as $custom_option_key => $custom_option_value ){
                            if ( !is_array( $custom_option_value ) && $custom_option_value !== '' ) {
                                $fields[$key][$custom_option_key] = $custom_option_value;
                            } else if ( is_array( $custom_option_value ) ){
                                if ( !isset( $fields[$key][$custom_option_key] ) ){
                                    $fields[$key][$custom_option_key] = [];
                                }
                                if ( is_array( $fields[$key][$custom_option_key] ) ){
                                    $fields[$key][$custom_option_key] = dt_array_merge_recursive_distinct( $fields[$key][$custom_option_key], $custom_option_value );
                                } else if ( !empty( $custom_option_value ) ){
                                    $fields[$key][$custom_option_key] = $custom_option_value;
                                }
                            }
                        }
                        if ( $field_type === 'key_select' || $field_type === 'multi_select' ) {
                            if ( isset( $field['default'] ) ) {
                                foreach ( $field['default'] as $custom_key => &$custom_value ) {
                                    if ( isset( $custom_value['label'] ) && empty( $custom_value['label'] ) ) {
                                        unset( $custom_value['label'] );
                                    }
                                }
                                $fields[ $key ]['default'] = array_replace_recursive( $fields[ $key ]['default'], $field['default'] );
                                foreach ( $fields[$key]['default'] as $option_key => $option_value ){
                                    if ( !isset( $option_value['label'] ) ){
                                        //fields without a label are not valid
                                        unset( $fields[$key]['default'][$option_key] );
                                    }
                                }
                            }
                        }
                        foreach ( $langs as $lang => $val ) {
                            if ( !empty( $field['translations'][$val['language']] ) ) {
                                $fields[ $key ]['translations'][$val['language']] = $field['translations'][$val['language']];
                            }
                        }
                    }
                    //set the order of key_select and multiselect fields
                    if ( $field_type === 'key_select' || $field_type === 'multi_select' ) {
                        if ( isset( $field['order'] ) ) {
                            $with_order = [];
                            foreach ( $field['order'] as $ordered_key ) {
                                if ( isset( $fields[$key]['default'][$ordered_key] ) ){
                                    $with_order[ $ordered_key ] = [];
                                }
                            }
                            foreach ( $fields[ $key ]['default'] as $option_key => $option_value ) {
                                $with_order[ $option_key ] = $option_value;
                            }
                            $fields[ $key ]['default'] = $with_order;
                        }
                    }
                    if ( $field_type === 'key_select' ){
                        if ( !isset( $fields[$key]['default']['none'] ) && empty( $fields[$key]['select_cannot_be_empty'] ) ){
                            $none = [ 'none' => [ 'label' => '' ] ];
                            $fields[$key]['default'] = dt_array_merge_recursive_distinct( $none, $fields[$key]['default'] );
                        }
                    }
                    if ( $field_type === 'connection' ){
                        // remove the field if the target post_type is not available
                        if ( isset( $fields[$key]['post_type'] ) && !in_array( $fields[$key]['post_type'], $post_types ) ){
                            unset( $fields[$key] );
                        }
                    }
                    if ( $field_type === 'location' ){
                        if ( isset( $fields[$key] ) ) {
                            $fields[$key]['mode'] = ! empty( DT_Mapbox_API::get_key() ) ? 'geolocation' : 'normal';
                        }
                    }
                }
            }
        }
        if ( $with_deleted_options === false ){
            foreach ( $fields as $field_key => $field ){
                if ( $field['type'] === 'key_select' || $field['type'] === 'multi_select' ){
                    foreach ( $field['default'] as $option_key => $option_value ){
                        if ( isset( $option_value['deleted'] ) && $option_value['deleted'] == true ){
                            unset( $fields[$field_key]['default'][$option_key] );
                        }
                    }
                }
            }
        }

        //load all tags on for each field
        if ( $load_tags ){
            global $wpdb;
            //get tag fields
            $tag_fields = array_keys( array_filter( $fields, function ( $field ){
                return $field['type'] === 'tags';
            } ) );

            $tags_sql = dt_array_to_sql( $tag_fields );

            //phpcs:disable
            //WordPress.WP.PreparedSQL.NotPrepared
            $tag_values = $wpdb->get_results("
                SELECT meta_key, meta_value
                FROM $wpdb->postmeta pm
                WHERE pm.meta_key IN ( $tags_sql )
                GROUP BY meta_key, meta_value
            ", ARRAY_A );
            //phpcs:enable

            foreach ( $tag_values as $tag_value ){
                $fields[$tag_value['meta_key']]['default'][] = $tag_value['meta_value'];
            }
        }

        foreach ( $fields as $field_key => $field ){
            //make sure each field has the name filed out
            if ( !isset( $field['name'] ) || empty( $field['name'] ) ){
                $fields[$field_key]['name'] = $field_key;
            }
        }

        $fields = apply_filters( 'dt_custom_fields_settings_after_combine', $fields, $post_type );
        wp_cache_set( $post_type . '_field_settings', $fields );
        return $fields;
    }

    public static function get_field_settings_by_type( $post_type, $field_key ) {
        $field_settings = self::get_post_field_settings( $post_type );
        $output = [];
        foreach ( $field_settings as $field_settings_key => $field_setting ) {
            if ( $field_setting['type'] === $field_key ) {
                $output[] = $field_settings_key;
            }
        }
        return $output;
    }

    public static function get_default_list_column_order( $post_type ){
        $fields = self::get_post_field_settings( $post_type );
        $columns = [];
        uasort( $fields, function ( $a, $b ){
            $a_order = 0;
            if ( isset( $a['show_in_table'] ) ){
                $a_order = is_numeric( $a['show_in_table'] ) ? $a['show_in_table'] : 90;
            }
            $b_order = 0;
            if ( isset( $b['show_in_table'] ) ){
                $b_order = is_numeric( $b['show_in_table'] ) ? $b['show_in_table'] : 90;
            }
            return $a_order <=> $b_order;
        });
        foreach ( $fields as $field_key => $field_value ){
            if ( ( isset( $field_value['show_in_table'] ) && $field_value['show_in_table'] ) && empty( $field_value['hidden'] ) ){
                $columns[] = $field_key;
            }
        }
        return $columns;
    }


    public static function get_post_tiles( $post_type, $return_cache = true, $translate = true ){
        $cached = wp_cache_get( $post_type . '_tile_options' );
        if ( $return_cache && $cached ){
            return $cached;
        }
        $tile_options = dt_get_option( 'dt_custom_tiles' );
        $default = [
            'status' => [ 'label' => __( 'Status', 'disciple_tools' ), 'tile_priority' => 10 ],
            'details' => [ 'label' => __( 'Details', 'disciple_tools' ), 'tile_priority' => 20 ]
        ];
        $sections = apply_filters( 'dt_details_additional_tiles', $default, $post_type );
        if ( !isset( $tile_options[$post_type] ) ){
            $tile_options[$post_type] = [];
        }
        $tile_options[$post_type] = dt_array_merge_recursive_distinct( $sections, $tile_options[$post_type] );
        $sections = apply_filters( 'dt_details_additional_section_ids', [], $post_type );
        foreach ( $sections as $section_id ){
            if ( ! isset( $tile_options[ $post_type ][ $section_id ] ) ) {
                $tile_options[$post_type][$section_id] = [];
            }
        }
        //translations
        if ( !is_admin() && $translate ) {
            $user_locale = get_user_locale();
            foreach ( $tile_options[$post_type] as $key => $value ) {
                if ( isset( $tile_options[$post_type][$key]['translations'][$user_locale] ) && !empty( $tile_options[$post_type][$key]['translations'][$user_locale] ) ) {
                    $tile_options[$post_type][$key]['label'] = $tile_options[$post_type][$key]['translations'][$user_locale];
                }
            }
        }

        uasort( $tile_options[ $post_type ], function ( $a, $b ){
            return ( $a['tile_priority'] ?? 100 ) <=> ( $b['tile_priority'] ?? 100 );
        });
        foreach ( $tile_options[$post_type] as $tile_key => &$tile_value ){
            if ( !isset( $tile_value['tile_priority'] ) ){
                $tile_options[$post_type][$tile_key]['tile_priority'] = ( array_search( $tile_key, array_keys( $tile_options[$post_type] ) ) + 1 ) * 10;
            }
            if ( isset( $tile_value['order'] ) ){
                $tile_value['order'] = array_values( $tile_value['order'] );
            }
        }

        $tile_options[$post_type] = apply_filters( 'dt_custom_tiles_after_combine', $tile_options[$post_type], $post_type );

        //there should not be a "No Tile" tile.
        if ( isset( $tile_options[$post_type]['no_tile'] ) ){
            unset( $tile_options[$post_type]['no_tile'] );
        }

        wp_cache_set( $post_type . '_tile_options', $tile_options[$post_type] );
        return $tile_options[$post_type];
    }

    /**
     * Request record access
     *
     * @param string $post_type
     * @param int $post_id
     *
     * @return false|int|WP_Error
     */
    public static function request_record_access( string $post_type, int $post_id ) {

        // Sanity checks
        if ( ! self::can_access( $post_type ) ) {
            return new WP_Error( __FUNCTION__, sprintf( 'You do not have access to these %s', $post_type ), [ 'status' => 403 ] );
        }

        $existing_post = self::get_post( $post_type, $post_id, false, false );
        if ( ! $existing_post ) {
            return new WP_Error( __FUNCTION__, 'post does not exist', [ 'status' => 404 ] );
        }

        // Fetch associated names
        $user_id = get_current_user_id();

        $requester_name = dt_get_user_display_name( $user_id );
        $post_settings  = self::get_post_settings( $post_type );

        $is_assigned_to = ( ! empty( get_post_meta( $post_id, 'assigned_to', true ) ) );
        $owner_id       = ( $is_assigned_to ) ? dt_get_user_id_from_assigned_to( get_post_meta( $post_id, 'assigned_to', true ) ) : intval( $existing_post['post_author'] );
        $owner_name     = ( $is_assigned_to ) ? ( dt_get_assigned_name( $post_id, true ) . ' ' ) ?? '' : ( $existing_post['post_author_display_name'] . ' ' ) ?? '';

        // Post comment
        $comment_html = sprintf(
            esc_html_x( '@[%1$s](%2$s) - User %3$s has requested access to %4$s [%5$s](%6$s). If desired, share this record with the user to grant access.', '@[user name][user_id] - User Fred has requested access to Contact [contact name][contact_id]. If desired, share this record with the user to grant access.', 'disciple_tools' ),
            esc_html( $owner_name ), esc_html( $owner_id ), esc_html( $requester_name ), esc_html( $post_settings['label_singular'] ), esc_html( $existing_post['name'] ), esc_html( $post_id )
        );

        return self::add_post_comment( $post_type, $post_id, $comment_html, 'comment', [
            'user_id'        => 0,
            'comment_author' => __( 'Access Request', 'disciple_tools' )
        ], false, false );
    }

    /**
     * Advanced Search
     *
     * @param string $query
     * @param string $post_type
     * @param int $offset
     *
     * @return array|WP_Error
     */

    public static function advanced_search( string $query, string $post_type, int $offset, array $filters = [], bool $check_permissions = true ): array {
        return self::advanced_search_query_exec( $query, $post_type, $offset, $filters, $check_permissions );
    }

    private static function advanced_search_query_exec( $query, $post_type, $offset, $filters, $check_permissions ): array {

        $query_results = array();
        $total_hits    = 0;

        // Search across post types based on incoming filter request
        $post_types = ( $post_type === 'all' ) ? self::get_post_types() : [ $post_type ];

        foreach ( $post_types as $post_type ) {
            try {
                if ( $post_type !== 'peoplegroups' ) {
                    $type_results = self::advanced_search_by_post( $post_type, [
                            'text'              => $query,
                            'offset'            => $offset
                        ],
                        $filters,
                        $check_permissions
                    );

                    if ( !empty( $type_results ) && !is_wp_error( $type_results ) && ( intval( $type_results['total'] ) > 0 ) ){
                        array_push( $query_results, $type_results );
                        $total_hits += intval( $type_results['total'] );
                    }
                }
            } catch ( Exception $e ) {
                $e->getMessage();
            }
        }

        return [
            'hits'       => $query_results,
            'total_hits' => $total_hits
        ];
    }

    private static function advanced_search_by_post( string $post_type, array $query, array $filters, bool $check_permissions ) {
        if ( $check_permissions && ! self::can_access( $post_type ) ) {
            return new WP_Error( __FUNCTION__, 'You do not have access to these', [ 'status' => 403 ] );
        }
        $post_types = self::get_post_types();
        if ( ! in_array( $post_type, $post_types ) ) {
            return new WP_Error( __FUNCTION__, "$post_type in not a valid post type", [ 'status' => 400 ] );
        }

        //filter in to add or remove query parameters.
        $query = apply_filters( 'dt_search_viewable_posts_query', $query );

        global $wpdb;

        $search = '';
        if ( isset( $query['text'] ) ) {
            $search = sanitize_text_field( $query['text'] );
            unset( $query['text'] );
        }
        $offset = 0;
        if ( isset( $query['offset'] ) ) {
            $offset = esc_sql( sanitize_text_field( $query['offset'] ) );
            unset( $query['offset'] );
        }
        $limit = 50;

        $permissions = [
            'shared_with' => [ 'me' ]
        ];

        $permissions = apply_filters( 'dt_filter_access_permissions', $permissions, $post_type );

        if ( ! empty( $permissions ) ) {
            $query[] = $permissions;
        }

        $fields_sql = self::fields_to_sql( $post_type, $query );
        if ( is_wp_error( $fields_sql ) ) {
            return $fields_sql;
        }

        // Prepare sql and execute search query
        $esc_like_search_sql = "'%" . str_replace( ' ', '%', esc_sql( $search ) ) . "%'";
        $extra_fields = '';
        $extra_joins = '';
        $extra_where = '';
        if ( $filters['post'] ){
            $extra_where .= 'p.post_title LIKE ' . $esc_like_search_sql;
        }
        if ( $filters['comment'] ){
            $extra_fields .= 'if( post_type_comments.comment_content LIKE ' . $esc_like_search_sql . ", 'Y', 'N' ) comment_hit,";
            $extra_fields .= 'if(post_type_comments.comment_content LIKE ' . $esc_like_search_sql . ", post_type_comments.comment_content, '') comment_hit_content,";
            $extra_joins .= "LEFT JOIN $wpdb->comments as post_type_comments ON ( post_type_comments.comment_post_ID = p.ID AND comment_content LIKE " . $esc_like_search_sql . ' )';
            $extra_where .= ( empty( $extra_where ) ? '' : ' OR ' ) . 'post_type_comments.comment_id IS NOT NULL';
        }
        if ( $filters['meta'] ){
            $extra_fields .= 'if(adv_search_post_meta.meta_value LIKE ' . $esc_like_search_sql . ", 'Y', 'N') meta_hit,";
            $extra_fields .= 'if(adv_search_post_meta.meta_value LIKE ' . $esc_like_search_sql . ", adv_search_post_meta.meta_value, '') meta_hit_value,";
            $extra_joins .= "LEFT JOIN $wpdb->postmeta as adv_search_post_meta ON ( adv_search_post_meta.post_id = p.ID AND ((adv_search_post_meta.meta_key LIKE 'contact_%') OR (adv_search_post_meta.meta_key LIKE 'nickname')) AND (adv_search_post_meta.meta_key NOT LIKE 'contact_%_details') ) ";
            $extra_where .= ( empty( $extra_where ) ? '' : ' OR ' ) . 'adv_search_post_meta.meta_value LIKE ' . $esc_like_search_sql;
        }

        // Ensure status filter is captured accordingly
        $post_settings = self::get_post_settings( $post_type, false );
        if ( ! empty( $filters['status'] ) && ! empty( $post_settings['status_field'] ) ) {
            $status_where_condition = ( $filters['status'] === 'all' ) ? 'IN (' . dt_array_to_sql( self::get_post_field_options_keys( $post_settings['fields'], $post_settings['status_field']['status_key'] ) ) . ')' : "= '" . $filters['status'] . "'";
            $extra_fields           .= 'if(adv_search_post_status.meta_value ' . $status_where_condition . ", 'Y', 'N') status_hit,";
            $extra_fields           .= 'if(adv_search_post_status.meta_value ' . $status_where_condition . ", adv_search_post_status.meta_value, '') status_hit_value,";
            $extra_joins            .= "LEFT JOIN $wpdb->postmeta as adv_search_post_status ON ( ( adv_search_post_status.post_id = p.ID ) AND ( adv_search_post_status.meta_key " . ( isset( $post_settings['status_field'] ) ? sprintf( "= '%s'", $post_settings['status_field']['status_key'] ) : "LIKE '%status%'" ) . ' ) )';
        }

        if ( empty( $extra_where ) ){
            $extra_where = '1=1';
        }

        $permissions_joins_sql = $fields_sql['joins_sql'];
        $permissions_where_sql = empty( $fields_sql['where_sql'] ) ? '' : ( $fields_sql['where_sql'] . ' AND ' );
        $sql = 'SELECT p.ID, p.post_title, p.post_type, ' . $extra_fields . ' p.post_date, if ( p.post_title LIKE ' . $esc_like_search_sql . ", 'Y', 'N') post_hit
            FROM $wpdb->posts p
            " . $extra_joins . '
            ' . $permissions_joins_sql . '
            WHERE ' . $permissions_where_sql . " (p.post_status = 'publish') AND p.post_type = '" . esc_sql( $post_type ) . "'
            AND ( " . $extra_where . " )
            GROUP BY p.ID, p.post_title, p.post_date
            ORDER BY ( p.post_title LIKE '" . esc_sql( $search ) . "%' ) desc, p.post_title asc
            LIMIT " . $offset . ', ' . $limit;
        // phpcs:disable
        // WordPress.WP.PreparedSQL.NotPrepared
        $posts = $wpdb->get_results( $sql, OBJECT );
        // phpcs:enable

        if ( empty( $posts ) && ! empty( $wpdb->last_error ) ) {
            return new WP_Error( __FUNCTION__, 'Sorry, we had a query issue.', [ 'status' => 500 ] );
        }

        //search by post_id
        if ( is_numeric( $search ) ) {
            $post = get_post( $search );
            if ( $post && self::can_view( $post_type, $post->ID ) ) {
                $posts[] = $post;
            }
        }

        $post_hits = array();

        //remove duplicated non-hits
        foreach ( $posts as $post ) {
            $add_post = false;
            if ( isset( $post->post_hit, $post->comment_hit, $post->meta_hit ) ) {
                if ( ! ( ( $post->post_hit === 'N' ) && ( $post->comment_hit === 'N' ) && ( $post->meta_hit === 'N' ) ) ) {
                    $add_post = true;
                }
            } else {
                $add_post = true;
            }

            // Add post accordingly, based on flag!
            if ( $add_post ) {
                $post_hits[] = $post;
            }
        }

        //decode special characters in post titles & determine status
        foreach ( $post_hits as $hit ) {
            $hit->post_title = wp_specialchars_decode( $hit->post_title );
            $hit->status     = self::get_post_field_option( $post_settings['fields'], $post_settings['status_field']['status_key'] ?? '', $hit->status_hit_value ?? '' );
        }

        //capture hits count and adjust future offsets
        $post_hits_count = count( $post_hits );
        return [
            'post_type' => $post_type,
            'posts'     => $post_hits,
            'total'     => $post_hits_count,
            'offset'    => intval( $offset ) + intval( $post_hits_count ) + 1
        ];
    }

    /**
     * Determine if post record contains specified field value.
     *
     * @param array $field_settings
     * @param array $post
     * @param string $field_id
     * @param mixed $value
     *
     * @return bool
     */

    public static function post_contains_field_value( $field_settings, $post, $field_id, $value ): bool {
        if ( empty( $post ) || is_wp_error( $post ) ) {
            return false;
        }

        // Determine if post contains specified field and value.
        if ( isset( $field_settings[ $field_id ], $post[ $field_id ] ) ) {
            $field_type = $field_settings[ $field_id ]['type'];
            switch ( $field_type ) {
                case 'text':
                case 'textarea':
                case 'boolean':
                case 'key_select':
                case 'date':
                case 'datetime':
                case 'user_select':
                case 'number':
                    return $post[ $field_id ] == $value;
                case 'multi_select':
                case 'links':
                case 'tags':
                case 'location':
                case 'location_meta':
                case 'connection':
                case 'communication_channel':
                    $value_array = $post[$field_id]['values'] ?? $post[$field_id];
                    foreach ( $value_array ?? [] as $entry ) {
                        if ( isset( $entry['value'] ) ) {

                            // Attempt to find match within incoming value array.
                            if ( ! empty( $value ) && is_array( $value ) ) {
                                foreach ( $value['values'] ?? $value as $val ) {
                                    if ( $entry['value'] == $val['value'] ) {
                                        return true;
                                    }
                                }
                            }
                        }
                    }
                    break;
            }
        }

        return false;
    }

    /**
     * Post Messaging
     *
     * @param string $post_type
     * @param int $post_id
     * @param array $args
     *
     * @return array|WP_Error
     */

    public static function post_messaging( string $post_type, int $post_id, array $args = [] ): array {
        if ( !self::can_view( $post_type, $post_id ) ) {
            return new WP_Error( __FUNCTION__, "No permissions to read $post_type with ID $post_id", [ 'status' => 403 ] );
        }
        $post = self::get_post( $post_type, $post_id );
        if ( empty( $post ) || is_wp_error( $post ) ) {
            return new WP_Error( __METHOD__, 'Invalid post record.' );
        }

        $is_sent = false;
        $send_method = $args['send_method'] ?? 'email';
        $message = $args['message'] ?? '';

        // Replace placeholder.
        $message = str_replace( '{{name}}', $post['title'], $message );

        // Dispatch accordingly, based on specified send method.
        if ( $send_method === 'email' && isset( $post['contact_email'] ) ) {

            // Extract post's to email addresses.
            $emails = [];
            foreach ( $post['contact_email'] as $post_email ) {
                $emails[] = $post_email['value'];
            }

            // Build email header and dispatch message.
            if ( !empty( $emails ) ) {
                $headers = [];

                $default_email = dt_default_email_address();
                $from_email = !empty( $args['reply_to'] ) ? $args['reply_to'] : $default_email;
                $from_name = !empty( $args['from_name'] ) ? $args['from_name'] : get_bloginfo( 'name' );
                $headers[] = 'From: ' . $from_name . ' <' . $default_email . '>';
                $headers[] = 'Reply-To: ' . $from_name . ' <' . $from_email . '>';

                // Send email or schedule for later dispatch.
                $subject = $args['subject'] ?? '';
                $is_sent = ( wp_queue()->push( new DT_Send_Email_Job( $emails, $subject, $message, $headers ) ) !== false );

                // Capture activity record.
                $activity = [
                    'action'            => 'sent_post_msg',
                    'object_type'       => $post_type,
                    'object_subtype'    => 'email',
                    'object_id'         => $post_id,
                    'object_name'       => $post['title'],
                    'object_note'       => implode( ', ', $emails )
                ];
                dt_activity_insert( $activity );
            }
        } elseif ( ( $send_method === 'sms' ) && class_exists( 'Disciple_Tools_Twilio_API', false ) && Disciple_Tools_Twilio_API::has_credentials() && Disciple_Tools_Twilio_API::is_enabled() ) {
            $is_sent = true;
            do_action( 'dt_twilio_send', $post_id, 'post', $message, [ 'service' => 'sms' ] );

            // Capture activity record.
            $activity = [
                'action'            => 'sent_post_msg',
                'object_type'       => $post_type,
                'object_subtype'    => 'sms',
                'object_id'         => $post_id,
                'object_name'       => $post['title'],
                'object_note'       => ''
            ];
            dt_activity_insert( $activity );
        }

        return [
            'post_id' => $post_id,
            'sent' => $is_sent
        ];
    }

    /**
     * Get available field types
     * See https://developers.disciple.tools/theme-core/customization/fields
     * @return array[]
     */
    public static function get_field_types(){
        return [
            'text' => [
                'label' => 'Text',
                'description' => 'A single line of text',
                'user_creatable' => true,
            ],
            'textarea' => [
                'label' => 'Textarea',
                'description' => 'A multi-line text area',
                'user_creatable' => true,
            ],
            'number' => [
                'label' => 'Number',
                'description' => 'A number',
                'user_creatable' => true,
            ],
            'date' => [
                'label' => 'Date',
                'description' => 'A date, like 2020-01-01',
                'user_creatable' => true,
            ],
            'datetime' => [
                'label' => 'Date with a time',
                'description' => 'A date, like August 9, 2023 at 4:10 PM',
                'user_creatable' => true,
            ],
            'key_select' => [
                'label' => 'Dropdown',
                'description' => 'A dropdown with a list of options',
                'user_creatable' => true,
            ],
            'multi_select' => [
                'label' => 'Multi Select',
                'description' => 'Button group to select multiple options',
                'user_creatable' => true,
            ],
            'boolean' => [
                'label' => 'Boolean',
                'description' => 'A checkbox for yes or no',
                'user_creatable' => false,
            ],
            'communication_channel' => [
                'label' => 'Communication Channel',
                'description' => 'Field for multiple contact info like email, phone, etc.',
                'user_creatable' => false,
            ],
            'connection' => [
                'label' => 'Connection',
                'description' => 'Connections to other records',
                'user_creatable' => true,
            ],
            'user_select' => [
                'label' => 'User Select',
                'description' => 'Field for selecting a user',
                'user_creatable' => false,
            ],
            'link' => [
                'label' => 'Links or Categories',
                'description' => 'Create categories and add values to them',
                'user_creatable' => true,
            ],
            'tags' => [
                'label' => 'Tags',
                'description' => 'Create and select tags',
                'user_creatable' => true,
            ],
            'location' => [
                'label' => 'Location',
                'description' => 'Location selected from the predefined location grid list',
                'user_creatable' => false,
            ],
            'location_meta' => [
                'label' => 'Location with Geocoding',
                'description' => 'Location selected with the help of a geocoder (mapbox, google)',
                'user_creatable' => false,
            ],
            'tasks' => [
                'label' => 'Tasks',
                'description' => 'Tasks assigned to a record',
                'user_creatable' => false,
            ],
            'array' => [
                'label' => 'Array',
                'description' => 'Array of data',
                'user_creatable' => false,
            ],
        ];
    }
}


