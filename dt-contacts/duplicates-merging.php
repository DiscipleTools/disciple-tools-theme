<?php


class DT_Duplicate_Checker_And_Merging {
    private $version = 2;
    private $context = "dt-posts";
    private $namespace;

    public function __construct(){
        $this->namespace = $this->context . "/v" . intval( $this->version );
        add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );
        add_action( 'archive_template_action_bar_buttons', [ $this, 'archive_template_action_bar_buttons' ], 10, 1 );
    }
    public function add_api_routes(){
        $arg_schemas = [
            "post_type" => [
                "description" => "The post type",
                "type" => 'post_type',
                "required" => true,
                "validate_callback" => [ "Disciple_Tools_Posts_Endpoints", "prefix_validate_args_static" ]
            ],
            "id" => [
                "description" => "The id of the post",
                "type" => 'integer',
                "required" => true,
                "validate_callback" => [ "Disciple_Tools_Posts_Endpoints", "prefix_validate_args_static" ]
            ],
        ];
        //get duplicates
        register_rest_route(
            $this->namespace, '/(?P<post_type>\w+)/(?P<id>\d+)/duplicates', [
                [
                    "methods" => "GET",
                    "callback" => [ $this, 'get_ids_of_non_dismissed_duplicates_endpoint' ],
                    "args" => [
                        "post_type" => $arg_schemas["post_type"],
                    ]
                ]
            ]
        );
        //get all post duplicates
        register_rest_route(
            $this->namespace, '/(?P<post_type>\w+)/(?P<id>\d+)/all_duplicates', [
                [
                    "methods" => "GET",
                    "callback" => [ $this, 'get_all_duplicates_on_post_endpoint' ],
                    "args" => [
                        "post_type" => $arg_schemas["post_type"],
                    ]
                ]
            ]
        );
        //dismiss post duplicates
        register_rest_route(
            $this->namespace, '/(?P<post_type>\w+)/(?P<id>\d+)/dismiss-duplicates', [
                [
                    "methods" => "POST",
                    "callback" => [ $this, 'dismiss_post_duplicate_endpoint' ],
                    "args" => [
                        "post_type" => $arg_schemas["post_type"],
                    ]
                ]
            ]
        );
        //Merge Posts
        register_rest_route(
            $this->namespace, '/(?P<post_type>\w+)/merge', [
                "methods"  => "POST",
                "callback" => [ $this, 'merge_posts_endpoint' ],
            ]
        );
        //Merge Posts
        register_rest_route(
            $this->namespace, '/(?P<post_type>\w+)/all-duplicates', [
                "methods"  => "GET",
                "callback" => [ $this, 'get_access_duplicates' ],
            ]
        );
    }

    public function get_ids_of_non_dismissed_duplicates_endpoint( WP_REST_Request $request ){
        $params = $request->get_params();
        $post_id = $params["id"] ?? null;
        $post_type = $params["post_type"] ?? null;
        if ( $post_id ){
            return self::ids_of_non_dismissed_duplicates( $post_type, $post_id );
        } else {
            return new WP_Error( __FUNCTION__, "Missing field for request", [ 'status' => 400 ] );
        }
    }


    private static function query_for_duplicate_searches( $post_type, $post_id, $exact = true ){
        $post = DT_Posts::get_post( $post_type, $post_id );
        $fields = DT_Posts::get_post_field_settings( $post_type );
        $search_query = [];
        $exact_template = $exact ? "^" : "";
        $fields_with_values = [];
        foreach ( $post as $field_key => $field_value ){
            if ( ! isset( $fields[$field_key]["type"] ) || empty( $fields[$field_key]["type"] ) ){
                continue;
            }
            if ( $fields[$field_key]["type"] === "communication_channel" ){
                if ( !empty( $field_value ) ){
                    $channel_queries = [];
                    foreach ( $field_value as $value ){
                        if ( !empty( $value["value"] ) ){
                             $channel_queries[] = $exact_template . $value["value"];
                        }
                    }
                    if ( !empty( $channel_queries ) ){
                        $fields_with_values[] = $field_key;
                        $search_query[$field_key] = [];
                        $search_query[$field_key] = $channel_queries;
                    }
                }
            } else if ( $field_key === "name" && !empty( $field_value ) ){
                $fields_with_values[] = $field_key;
                $search_query[$field_key] = [ $exact_template . $field_value ];
            }
        }
        return [
            "query" => $search_query,
            "fields" => $fields_with_values,
        ];
    }

    /**
     * @param $post_type
     * @param $post_id
     * @param bool $exact //whether the field strings have to be exactly the same or if one can contain the other.
     * @return array|WP_Error
     */
    public static function ids_of_non_dismissed_duplicates( $post_type, $post_id, $exact = true ){
        $post = DT_Posts::get_post( $post_type, $post_id, true, true, true );
        if ( is_wp_error( $post ) ){
            return $post;
        }
        $search_query = self::query_for_duplicate_searches( $post_type, $post_id, $exact );
        $res = DT_Posts::search_viewable_post( "contacts", [ $search_query["query"] ] );
        if ( is_wp_error( $res ) ){
            return $res;
        }
        $ids = array_map( function ( $post ){
            return $post->ID;
        }, $res["posts"] );

        //already dismissed duplicates
        $dismissed = isset( $post["duplicate_data"]['override'] ) ? $post["duplicate_data"]['override'] : [];

        //exclude already dismissed duplicates and self
        $ids = array_values( array_diff( $ids, array_merge( $dismissed, [ $post_id ] ) ) );

        return [
            "ids" => $ids
        ];
    }

    public function get_all_duplicates_on_post_endpoint( WP_REST_Request $request ){
        $params = $request->get_params();
        $post_id = $params["id"] ?? null;
        $post_type = $params["post_type"] ?? null;
        if ( $post_id ){
            return self::get_all_duplicates_on_post( $post_type, $post_id );
        } else {
            return new WP_Error( 'get_duplicates_on_contact', "Missing field for request", [ 'status' => 400 ] );
        }
    }
    public static function get_all_duplicates_on_post( $post_type, $post_id ){
        if ( !DT_Posts::can_access( $post_type ) ) {
            return new WP_Error( __FUNCTION__, "You do not have permission for this", [ 'status' => 403 ] );
        }

        $post = DT_Posts::get_post( $post_type, $post_id );
        $field_settings = DT_Posts::get_post_field_settings( $post_type );

        $exact_query = self::query_for_duplicate_searches( $post_type, $post_id, true );
        $exact_query["fields"] = array_merge( $exact_query["fields"], [ "overall_status" ] );
        $search_query = [ $exact_query["query"], "fields_to_return" => $exact_query["fields"]];
        $exact_duplicates = DT_Posts::list_posts( $post_type, $search_query );

        $fuzzy_query = self::query_for_duplicate_searches( $post_type, $post_id, false );
        $fuzzy_query["fields"] = array_merge( $fuzzy_query["fields"], [ "overall_status" ] );

        $search_query = [ $fuzzy_query["query"], "fields_to_return" => $fuzzy_query["fields"]];
        $possible_duplicates = DT_Posts::list_posts( $post_type, $search_query );

        $possible_duplicates = array_merge( $exact_duplicates["posts"], $possible_duplicates["posts"] );


        $ordered = [];
        $ids = [];
        foreach ( $possible_duplicates as $possible_duplicate ){
            if ( $possible_duplicate["ID"] === $post_id || in_array( $possible_duplicate["ID"], $ids ) ){
                continue; // exclude self and records already processed
            }
            $ids[] = $possible_duplicate["ID"];
            $match_on = [];
            $points = 0;
            foreach ( $fuzzy_query["fields"] as $field_key ){
                if ( $field_settings[$field_key]["type"] === "text" ){
                    if ( $post[$field_key] === $possible_duplicate[$field_key] ){
                        $match_on[] = [ "field" => $field_key, "value" => $post[$field_key] ];
                        $points += 4;
                    } else if ( stripos( $post[$field_key], $possible_duplicate[$field_key] ) !== false || stripos( $possible_duplicate[$field_key], $post[$field_key] ) !== false ){
                        $match_on[] = [ "field" => $field_key, "value" => $post[$field_key] ];
                        $points++;
                    }
                }
                if ( $field_settings[$field_key]["type"] === "communication_channel" ){
                    foreach ( $post[$field_key] as $value ){
                        foreach ( $possible_duplicate[$field_key] as $dup_value ){
                            $points +=1;
                            if ( $value["value"] === $dup_value["value"] ){
                                $match_on[] = [ "field" => $field_key, "value" => $dup_value["value"] ];
                                $points += 4;
                            } else if ( stripos( $value["value"], $dup_value["value"] ) !== false || stripos( $dup_value["value"], $value["value"] ) !== false){
                                $match_on[] = [ "field" => $field_key, "value" => $dup_value["value"] ];
                                $points++;
                            }
                        }
                    }
                }
            }
            if ( !isset( $ordered[$possible_duplicate["ID"]] ) ) {
                $ordered[$possible_duplicate["ID"]] = [
                    "ID" => $possible_duplicate["ID"],
                    "points" => $points,
                    "fields" => $match_on,
                    "post" => $possible_duplicate
                ];
            }
        }

        $return = [];
        foreach ( $ordered as $id => $dup ) {
            $return[] = $dup;
        }
        return $return;
    }

    public function dismiss_post_duplicate_endpoint( WP_REST_Request $request ){
        $url_params = $request->get_url_params();
        $body = $request->get_json_params() ?? $request->get_body_params();
        $post_type = $url_params['post_type'];
        $post_id = $url_params["id"];
        $dup_id = $body["id"];
        if ( $post_id && $dup_id ){
            if ( $dup_id === "all" ){
                return self::dismiss_all_duplicates( $post_type, $post_id );
            } else {
                return self::dismiss_duplicate( $post_type, $post_id, $dup_id );
            }
        }
        return false;
    }

    public static function dismiss_duplicate( $post_type, int $post_id, int $dismiss_id ) {
        $post = DT_Posts::get_post( $post_type, $post_id );
        if ( is_wp_error( $post ) ){
            return $post;
        }
        $duplicate_data = isset( $post['duplicate_data'] ) ? ( is_array( $post['duplicate_data'] ) ? $post['duplicate_data'] : unserialize( $post['duplicate_data'] ) ) : array();
        if ( !in_array( $dismiss_id, $duplicate_data["override"] ) ) {
            $duplicate_data["override"][] = $dismiss_id;
        }
        update_post_meta( $post_id, "duplicate_data", $duplicate_data );
        return $duplicate_data;
    }
    public static function dismiss_all_duplicates( $post_type, int $post_id ) {
        $post = DT_Posts::get_post( $post_type, $post_id );
        if ( is_wp_error( $post ) ){
            return $post;
        }
        $duplicate_data = isset( $post['duplicate_data'] ) ? ( is_array( $post['duplicate_data'] ) ? $post['duplicate_data'] : unserialize( $post['duplicate_data'] ) ) : array();
        $possible_duplicates = self::ids_of_non_dismissed_duplicates( $post_type, $post_id, false );

        foreach ( $possible_duplicates["ids"] as $dup_id ){
            if ( !in_array( $dup_id, $duplicate_data["override"] ) ) {
                $duplicate_data["override"][] = $dup_id;
            }
        }
        update_post_meta( $post_id, "duplicate_data", $duplicate_data );
        return $duplicate_data;
    }

    /**
     * Merging
     */
    public function merge_posts_endpoint( WP_REST_Request $request ){
        $body = $request->get_json_params() ?? $request->get_body_params();
        $url_params = $request->get_url_params();
        $post_type = $url_params['post_type'];
        if ( isset( $body["contact1"], $body["contact2"] ) ) {
            return self::merge_posts( $post_type, $body["contact1"], $body["contact2"], $body );
        }
        return false;
    }

    public static function merge_posts( $post_type, $contact1, $contact2, $args ){
        $contact_fields = DT_Posts::get_post_field_settings( $post_type );
        $phones = $args["phone"] ?? [];
        $emails = $args["email"] ?? [];
        $addresses = $args["address"] ?? [];

        $master_id = $args["master-record"] ?? $contact1;
        $non_master_id = ( $master_id === $contact1 ) ? $contact2 : $contact1;
        $contact = DT_Posts::get_post( "contacts", $master_id );
        $non_master = DT_Posts::get_post( "contacts", $non_master_id );

        if ( is_wp_error( $contact ) ) { return $contact; }
        if ( is_wp_error( $non_master ) ) { return $non_master; }


        $current = array(
            'contact_phone' => array(),
            'contact_email' => array(),
            'contact_address' => array(),
            // 'contact_facebook' => array()
        );

        foreach ( $contact as $key => $fields ) {
            if ( strpos( $key, "contact_" ) === 0 ) {
                $split = explode( "_", $key );
                if ( !isset( $split[1] ) ) {
                    continue;
                }
                $new_key = $split[0] . "_" . $split[1];
                foreach ( $contact[ $new_key ] ?? array() as $values ) {
                    $current[ $new_key ][ $values['key'] ] = $values['value'];
                }
            }
        }

        $update = array(
            'contact_phone' => array( 'values' => array() ),
            'contact_email' => array( 'values' => array() ),
            'contact_address' => array( 'values' => array() ),
            // 'contact_facebook' => array( 'values' => array() )
        );

        $update_for_duplicate = [];

        $ignore_keys = array();

        foreach ($phones as $phone) {
            $index = array_search( $phone, $current['contact_phone'] );
            if ($index !== false) { $ignore_keys[] = $index;
                continue; }
            array_push( $update['contact_phone']['values'], [ 'value' => $phone ] );
        }
        foreach ($emails as $email) {
            $index = array_search( $email, $current['contact_email'] );
            if ($index !== false) { $ignore_keys[] = $index;
                continue; }
            array_push( $update['contact_email']['values'], [ 'value' => $email ] );
        }
        foreach ($addresses as $address) {
            $index = array_search( $address, $current['contact_address'] );
            if ($index !== false) { $ignore_keys[] = $index;
                continue; }
            array_push( $update['contact_address']['values'], [ 'value' => $address ] );
        }

        /*
            Merge social media + other contact data from the non master to master
        */
        foreach ( $non_master as $key => $fields ) {
            if ( isset( $contact_fields[$key] ) && $contact_fields[$key]["type"] === "multi_select" ){
                $update[$key]["values"] = [];
                foreach ( $fields as $field_value ){
                    $update[$key]["values"][] = [ "value" => $field_value ];
                }
            }
            if ( isset( $contact_fields[ $key ] ) && $contact_fields[ $key ]["type"] === "key_select" && ( !isset( $contact[ $key ] ) || $key === "none" || $key === "" ) ) {
                $update[$key] = $fields["key"];
            }
            if ( isset( $contact_fields[$key] ) && $contact_fields[$key]["type"] === "text" && ( !isset( $contact[$key] ) || empty( $contact[$key] ) )){
                $update[$key] = $fields;
            }
            if ( isset( $contact_fields[$key] ) && $contact_fields[$key]["type"] === "number" && ( !isset( $contact[$key] ) || empty( $contact[$key] ) )){
                $update[$key] = $fields;
            }
            if ( isset( $contact_fields[$key] ) && $contact_fields[$key]["type"] === "date" && ( !isset( $contact[$key] ) || empty( $contact[$key]["timestamp"] ) )){
                $update[$key] = $fields["timestamp"] ?? "";
            }
            if ( isset( $contact_fields[$key] ) && $contact_fields[$key]["type"] === "array" && ( !isset( $contact[$key] ) || empty( $contact[$key] ) )){
                if ( $key != "duplicate_data" ){
                    $update[$key] = $fields;
                }
            }
            if ( isset( $contact_fields[$key] ) && $contact_fields[$key]["type"] === "connection" && ( !isset( $contact[$key] ) || empty( $contact[$key] ) )){
                $update[$key]["values"] = [];
                $update_for_duplicate[$key]["values"] = [];
                foreach ( $fields as $field_value ){
                    $update[$key]["values"][] = [ "value" => $field_value["ID"] ];
                    $update_for_duplicate[$key]["values"][] = [
                        "value" => $field_value["ID"],
                        "delete" => true
                    ];
                }
            }


            if ( strpos( $key, "contact_" ) === 0 ) {
                $split = explode( "_", $key );
                if ( !isset( $split[1] ) ) {
                    continue;
                }
                $new_key = $split[0] . "_" . $split[1];
                if ( in_array( $new_key, array_keys( $update ) ) ) {
                    continue;
                }
                $update[ $new_key ] = array(
                    'values' => array()
                );
                foreach ( $non_master[ $new_key ] ?? array() as $values ) {
                    $index = array_search( $values['value'], $current[ $new_key ] ?? array() );
                    if ( $index !== false ) {
                        $ignore_keys[] = $index;
                        continue;
                    }
                    array_push( $update[ $new_key ]['values'], array(
                        'value' => $values['value']
                    ) );
                }
            }
        }

        $delete_fields = array();
        if ($update['contact_phone']['values']) { $delete_fields[] = 'contact_phone'; }
        if ($update['contact_email']['values']) { $delete_fields[] = 'contact_email'; }
        if ($update['contact_address']['values']) { $delete_fields[] = 'contact_address'; }

        if ( !empty( $delete_fields )) {
            self::remove_fields( $master_id, $delete_fields, $ignore_keys );
        }

        //copy over comments
        $comments = DT_Posts::get_post_comments( "contacts", $non_master_id );
        foreach ( $comments["comments"] as $comment ){
            $comment["comment_post_ID"] = $master_id;
            if ( $comment["comment_type"] === "comment" ){
                $comment["comment_content"] = sprintf( esc_html_x( '(From Duplicate): %s', 'duplicate comment', 'disciple_tools' ), $comment["comment_content"] );
            }
            if ( $comment["comment_type"] !== "duplicate" && !empty( $comment["comment_content"] ) ) {
                wp_insert_comment( $comment );
            }
        }


        // copy over users the contact is shared with.
        global $wpdb;
        $wpdb->query( $wpdb->prepare( "
            INSERT INTO $wpdb->dt_share (user_id, post_id )
            SELECT user_id, %d
            FROM $wpdb->dt_share
            WHERE post_id = %d
            AND user_id NOT IN ( SELECT user_id FROM $wpdb->dt_share WHERE post_id = %d )
        ", $master_id, $non_master_id, $master_id ) );

        //Keep duplicate data override info.
        $contact["duplicate_data"]["override"] = array_merge( $contact["duplicate_data"]["override"] ?? [], $non_master["duplicate_data"]["override"] ?? [] );
        $update["duplicate_data"] = $contact["duplicate_data"];

        $current_user_id = get_current_user_id();
        wp_set_current_user( 0 ); // to keep the merge activity from a specific user.
        $current_user = wp_get_current_user();
        $current_user->display_name = __( "Duplicate Checker", 'disciple_tools' );
        $update_return = DT_Posts::update_post( "contacts", $master_id, $update, true, false );
        if ( is_wp_error( $update_return ) ) { return $update_return; }
        $non_master_update_return = DT_Posts::update_post( "contacts", $non_master_id, $update_for_duplicate, true, false );
        if ( is_wp_error( $non_master_update_return ) ) { return $non_master_update_return; }
        wp_set_current_user( $current_user_id );

        self::dismiss_duplicate( $post_type, $master_id, $non_master_id );
        self::dismiss_duplicate( $post_type, $non_master_id, $master_id );
        self::close_duplicate_post( $post_type, $non_master_id, $master_id );

        do_action( "dt_contact_merged", $master_id, $non_master_id );
        return true;
    }

    private static function remove_fields( $contact_id, $fields = [], $ignore = [] ){
        global $wpdb;
        foreach ( $fields as $field ){
            $ignore_keys = preg_grep( "/$field/", $ignore );
            $sql = "delete
                from
                $wpdb->postmeta
                where
                post_id = %d and
                meta_key like %s";
            $params = array( $contact_id, "$field%" );
            if ( !empty( $ignore_keys ) ){
                foreach ( $ignore_keys as $key ){
                    $sql .= " and meta_key not like %s";
                }
                array_push( $params, ...$ignore_keys );
            }
            $wpdb->query( $wpdb->prepare( $sql, $params ) ); // @codingStandardsIgnoreLine
        }
    }

    public static function close_duplicate_post( string $post_type, int $duplicate_id, int $contact_id ) {
        $duplicate = DT_Posts::get_post( $post_type, $duplicate_id );
        $contact = DT_Posts::get_post( $post_type, $contact_id );

        DT_Posts::update_post( $post_type, $duplicate_id, [
            "overall_status" => "closed",
            "reason_closed" => "duplicate",
            "duplicate_of" => $contact_id
        ] );

        $link = "[" . $contact['title'] .  "](" .  $contact_id . ")";
        $comment = sprintf( esc_html_x( 'This record is a duplicate and was merged into %2$s', 'This record duplicated and was merged into Contact2', 'disciple_tools' ), $duplicate['title'], $link );

        $args = [
            "user_id" => 0,
            "comment_author" => __( "Duplicate Checker", 'disciple_tools' )
        ];

        DT_Posts::add_post_comment( $post_type, $duplicate_id, $comment, "duplicate", $args, true, true );
        self::dismiss_all_duplicates( $post_type, $duplicate_id );

        $user = wp_get_current_user();
        //comment on master
        $link = "[" . $duplicate['title'] .  "](" .  $duplicate_id . ")";
        $comment = sprintf( esc_html_x( '%1$s merged %2$s into this record', 'User1 merged Contact1 into this record', 'disciple_tools' ), $user->display_name, $link );
        DT_Posts::add_post_comment( $post_type, $contact_id, $comment, "duplicate", $args, true, true );
    }


    public function archive_template_action_bar_buttons( string $post_type ){
        if ( !dt_is_module_enabled( "access_module" ) ){
            return;
        }
        ?>
        <a class="button" href="<?php echo esc_url( site_url( '/view-duplicates' ) ); ?>">
            <img style="display: inline-block;" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/duplicate-white.svg' ) ?>"/>
            <span><?php esc_html_e( "View Duplicates", 'disciple_tools' ) ?></span>
        </a>
        <?php
    }


    /**
     * Rest endpoint for the View Duplicates page.
     * @param WP_REST_Request $request "limit" for how many records to skip
     * @return array|WP_Error
     */

    public static function get_access_duplicates( WP_REST_Request $request ){
        if ( !current_user_can( "dt_all_access_contacts" ) ){
            return new WP_Error( __FUNCTION__, "You do not have permission for this", [ 'status' => 403 ] );
        }
        if ( !dt_is_module_enabled( "access_module" ) ){
            return new WP_Error( __FUNCTION__, "Access Module is not enabled", [ 'status' => 403 ] );
        }
        $params = $request->get_params();
        $limit = $params["limit"] ?? 0;
        $field_settings = DT_Posts::get_post_field_settings( "contacts" );
        $return = [];

        //get all the most recently modified access contacts
        global $wpdb;
        $recent_contacts = $wpdb->get_results( $wpdb->prepare( "
            SELECT posts.post_title, pm.meta_value as last_modified, posts.ID, posts.post_date
            FROM $wpdb->posts posts
            INNER JOIN $wpdb->postmeta pm ON ( posts.ID = pm.post_id and pm.meta_key = 'last_modified' )
            INNER JOIN $wpdb->postmeta type ON ( posts.ID = type.post_id and type.meta_key = 'type' AND type.meta_value = 'access' )
            WHERE posts.post_type = 'contacts'
            ORDER BY posts.post_date DESC
            LIMIT %d, 100
        ", esc_sql( $limit ) ), ARRAY_A );


        //search for duplicates on each post
        foreach ( $recent_contacts as &$contact ){
            $dups = self::query_for_duplicate_searches_v2( "contacts", $contact["ID"] );
            $post = DT_Posts::get_post( "contacts", $contact["ID"] );
            $contact["dups"] = [];
            $contact["overall_status"] = $post["overall_status"];
            $contact["overall_status"]["color"] = isset( $field_settings["overall_status"]["default"][$post["overall_status"]["key"]]["color"] ) ? $field_settings["overall_status"]["default"][$post["overall_status"]["key"]]["color"] : "blue";
            $contact["info"] = [];
            foreach ( $field_settings as $field_key => $field_value ){
                if ( isset( $field_value["type"] ) && $field_value["type"] === "communication_channel" && isset( $post[$field_key] ) ){
                    foreach ( $post[$field_key] as $channel ){
                        $contact["info"][] = $channel;
                    }
                }
            }
            $fields = [];
            foreach ( $dups as $dup ){
                if ( !isset( $post["duplicate_data"]["override"] ) || !in_array( (int) $dup["ID"], $post["duplicate_data"]["override"] ) ){
                    $fields[$dup["field"]][] = $dup;
                }
            }
            foreach ( $fields as $field_key => $dups_on_field ){
                if ( count( $dups_on_field ) > 10 ){
                    unset( $fields[$field_key] );
                } else {
                    if ( $field_key === "post_title" ){
                        $field_key = "name";
                    }
                    $name = isset( $field_settings[$field_key]["name"] ) ? $field_settings[$field_key]["name"] : $field_key;
                    $contact["dups"][$name] = $dups_on_field;
                }
            }
            if ( !empty( $contact["dups"] ) ){
                $return[] = $contact;
            }
        }

        return [
            "scanned" => $limit + 100,
            "posts_with_matches" => $return
        ];
    }

    /**
     * Search for potential duplicates on a post
     *
     * @param $post_type
     * @param int $post_id the post to look for duplicates on
     * @param bool $exact search only for exact matches
     * @return array|object|null, the array of matches
     */
    private static function query_for_duplicate_searches_v2( $post_type, $post_id, bool $exact = true ){
        $post = DT_Posts::get_post( $post_type, $post_id );
        $fields = DT_Posts::get_post_field_settings( $post_type );
        $search_query = [];
        $exact_template = $exact ? "^" : "";
        $fields_with_values = [];
        global $wpdb;
        $all_sql = "";
        foreach ( $post as $field_key => $field_value ){
            if ( ! isset( $fields[$field_key]["type"] ) || empty( $fields[$field_key]["type"] ) ){
                continue;
            }
            $table_key = esc_sql( "field_" . $field_key );
            if ( $fields[$field_key]["type"] === "communication_channel" ){
                if ( !empty( $field_value ) ){
                    $sql_joins = "";
                    $where_sql = "";
                    $sql_joins .= " LEFT JOIN $wpdb->postmeta as $table_key ON ( $table_key.post_id = p.ID AND $table_key.meta_key LIKE '" . esc_sql( $field_key ) . "%' AND $table_key.meta_key NOT LIKE '%_details' )";
                    $sql_joins .= " INNER JOIN $wpdb->postmeta as type ON ( type.post_id = p.ID AND type.meta_key = 'type' AND type.meta_value = 'access' )";
                    $channel_queries = [];
                    foreach ( $field_value as $value ){
                        if ( !empty( $value["value"] ) ){
                            $where_sql .= ( empty( $where_sql ) ? "" : ' OR ' ) .  " $table_key.meta_value = '" . esc_sql( $value["value"] ) . "'";
                            $channel_queries[] = $exact_template . $value["value"];
                        }
                    }
                    if ( !empty( $channel_queries ) ){
                        if ( !empty( $all_sql ) ){
                            $all_sql .= " UNION ";
                        }
                        $all_sql .= "SELECT p.ID, p.post_title, '" . esc_sql( $field_key ) . "' as field, $table_key.meta_value as value
                            FROM $wpdb->posts p
                            $sql_joins
                            WHERE
                            ( $where_sql )
                            AND p.ID != " . esc_sql( $post_id ) . "
                        ";
                    }
                }
            } else if ( $field_key === "name" && !empty( $field_value ) ){
                if ( !empty( $all_sql ) ){
                    $all_sql .= " UNION ";
                }
                $all_sql .= "
                    SELECT
                    p.ID, p.post_title, 'post_title' as field, p.post_title as value
                    FROM $wpdb->posts p
                    JOIN $wpdb->postmeta pm ON ( p.ID = pm.post_id AND pm.meta_key = 'type' AND pm.meta_value = 'access' )
                    WHERE p.post_title = '" . esc_sql( $field_value ) . "'
                    AND p.post_type = 'contacts'
                    AND p.ID != " . esc_sql( $post_id ) . "
                ";

                $fields_with_values[] = $field_key;
                $search_query[$field_key] = [ $exact_template . $field_value ];
            }
        }
        $contacts = $wpdb->get_results( $all_sql, ARRAY_A ); // @phpcs:ignore
        return $contacts;
    }
}
