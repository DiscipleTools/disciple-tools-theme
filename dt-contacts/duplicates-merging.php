


public static function get_all_duplicates() {
    if ( !self::can_view_all( "contacts" ) ) {
        return new WP_Error( __FUNCTION__, "You do not have permission for this", [ 'status' => 403 ] );
    }

    global $wpdb;
    $post_settings = apply_filters( "dt_get_post_type_settings", [], "contacts" );
    $records = $wpdb->get_results( "
            SELECT pm.meta_value, pm.meta_key, pm.post_id, dd.meta_value as duplicate_data, rc.meta_value as reason_closed, p.post_title, status.meta_value as status
            FROM $wpdb->postmeta pm
            INNER JOIN (
                SELECT meta_value
                FROM $wpdb->postmeta
                WHERE meta_key LIKE 'contact_%' AND meta_key NOT LIKE '%details'
                GROUP BY meta_value
                HAVING count(meta_id) > 1 AND count(meta_id) < 10
            ) dup ON dup.meta_value = pm.meta_value
            INNER JOIN $wpdb->posts as p ON ( p.ID = pm.post_id AND p.post_type = 'contacts' )
            LEFT JOIN $wpdb->postmeta as dd ON ( dd.post_id = pm.post_id AND dd.meta_key = 'duplicate_data' )
            LEFT JOIN $wpdb->postmeta as rc ON ( rc.post_id = pm.post_id AND rc.meta_key = 'reason_closed' )
            LEFT JOIN $wpdb->postmeta as status ON ( status.post_id = pm.post_id AND status.meta_key = 'overall_status' )
            WHERE pm.meta_key LIKE 'contact_%' AND pm.meta_key NOT LIKE '%details' AND pm.meta_value NOT LIKE ''
            AND ( rc.meta_value != 'duplicate' OR rc.meta_value IS NULL )
        ", ARRAY_A );

    $dups = [];
    foreach ( $records as $duplicate ){
        $key = explode( '_', $duplicate["meta_key"] )[0] . '_' . explode( '_', $duplicate["meta_key"] )[1];
        $duplicate_data = maybe_unserialize( $duplicate["duplicate_data"] );
        if ( !isset( $dups[$key][$duplicate["meta_value"]] ) ) {
            $dups[$key][$duplicate["meta_value"]] = [
                "overrides" => [],
                "posts" => []
            ];
        }
        $dups[$key][$duplicate["meta_value"]]["overrides"] = array_merge( $dups[$key][$duplicate["meta_value"]]["overrides"], $duplicate_data["override"] ?? [] );
        if ( $duplicate["reason_closed"] !== "duplicate" ){
            $dups[$key][$duplicate["meta_value"]]["posts"][$duplicate["post_id"]] = [
                "name" => $duplicate['post_title'],
                "status" => $duplicate['status'],
                "reason_closed" => $duplicate["reason_closed"] ?? null,
            ];
        }
        foreach ( $dups[$key][$duplicate["meta_value"]]["overrides"] as $id ){
            if ( isset( $dups[$key][$duplicate["meta_value"]]["posts"][$id] ) ){
                unset( $dups[$key][$duplicate["meta_value"]]["posts"][$id] );
            }
        }
    }
    $return = [];
    foreach ( $dups as $channel => $channel_values ) {
        foreach ( $channel_values as $index => $duplicate ){
            if ( sizeof( $duplicate["posts"] ) < 2 ){
                unset( $dups[$channel][$index] );
            }
        }

        $channel_key = explode( '_', $channel )[1];
        $return[$channel] = [
            "name" => isset( $post_settings["channels"][$channel_key]['label'] ) ? $post_settings["channels"][$channel_key]['label'] : $channel,
            "dups" => $dups[$channel]
        ];
    }


    return $return;

}

public static function get_duplicates_on_contact( $contact_id, $include_contacts = true, $exact_match = false ){
    if ( !self::can_access( 'contacts' ) ) {
        return new WP_Error( __FUNCTION__, "You do not have permission for this", [ 'status' => 403 ] );
    }

    $contact = self::get_contact( $contact_id );

    $possible_duplicates = self::get_possible_duplicates( $contact_id, $contact, $exact_match );
    $ordered = [];

    $shared_with_ids = [];
    if ( !current_user_can( "view_any_contacts" ) ) {
        global $wpdb;
        $shared_with_ids_query = $wpdb->get_results( $wpdb->prepare( "
                SELECT post_id
                FROM $wpdb->dt_share
                WHERE user_id = %s
            ", get_current_user_id() ), ARRAY_A );
        foreach ( $shared_with_ids_query as $res ){
            $shared_with_ids[] = $res["post_id"];
        }
    }

    foreach ( $possible_duplicates as $field_key => $dups ){
        foreach ( $dups as $dup ){
            if ( current_user_can( "view_any_contacts" ) || in_array( $dup["ID"], $shared_with_ids ) ){
                if ( empty( $dup["ID"] ) ) {
                    continue;
                }
                if ( !isset( $ordered[$dup["ID"]] ) ) {
                    $ordered[$dup["ID"]] = [
                        "ID" => $dup["ID"],
                        "points" => 0,
                        "fields" => []
                    ];
                }
                if ( $field_key !== 'title' || ( $dup["post_title"] ?? '' ) === $contact["title"] ) {
                    $ordered[$dup["ID"]]["points"] += $field_key === 'title' ? 1 : 2; //increment for exact matches
                }
                $ordered[$dup["ID"]]["fields"][] = array_merge( [ "field" => $field_key ], $dup );
                if ( $include_contacts ){
                    $ordered[$dup["ID"]]["contact"] = DT_Posts::get_post( "contacts", $dup["ID"] );
                }
            }
        }
    }
    $return = [];
    foreach ( $ordered as $id => $dup ) {
        $return[] = $dup;
    }
    return $return;
}

public function check_for_duplicates( $contact_id, $fields ){
    $contact = DT_Posts::get_post( "contacts", $contact_id, true, false );
    if ( is_wp_error( $contact ) ){
        return $contact;
    }
    $duplicate_data = $contact["duplicate_data"] ?? [];
    $possible_duplicates = self::get_possible_duplicates( $contact_id, $contact, true, empty( $duplicate_data["check_dups"] ) ? $fields : [] );
    if ( !isset( $duplicate_data["override"] )){
        $duplicate_data["override"] = [];
    }
    $dup_ids = [];
    foreach ( $possible_duplicates as $field_key => $dups ){
        foreach ( $dups as $dup ){
            if ( !in_array( $dup["ID"], $dup_ids ) ) {
                $dup_ids[] = $dup["ID"];
            }
        }
    }

    if ( sizeof( $dup_ids ) > sizeof( $duplicate_data["override"] ) ){
        $duplicate_data["check_dups"] = true;
    } else {
        $duplicate_data["check_dups"] = false;
    }
    self::save_duplicate_data( $contact_id, $duplicate_data );
}

public static function get_possible_duplicates( $contact_id, $contact, $exact_match = false, $changed_fields = [] ){
    $fields_to_check = [ "contact_phone", "contact_email", "contact_address", "title" ];
    $fields_to_check = apply_filters( "dt_contact_duplicate_fields_to_check", $fields_to_check );
    $duplicates = [];
    $meta_query_fields = [];
    $query = '';
    foreach ( $fields_to_check as $field_id ){
        if ( !empty( $contact[$field_id] ) & ( empty( $changed_fields ) || in_array( $field_id, array_keys( $changed_fields ) ) ) ) {
            $field_value = $contact[$field_id];
            if ( $field_id == "title" ){
                $contacts = self::find_contacts_by_title( $field_value, $contact_id, $exact_match );
                $duplicates[$field_id] = $contacts;
            } else {
                if ( isset( $field_value["values"] ) ){
                    $values = $field_value["values"];
                } else {
                    $values = $field_value;
                }
                foreach ( $values as $val ){
                    if ( !empty( $val["value"] ) ){
                        $meta_query_fields[] = [ $field_id => $val["value"] ];
                        $query .= ( $query ? ' OR ' : ' ' );
                        $query .= " ( meta_key LIKE '" . esc_sql( $field_id ) . "%' AND meta_value LIKE '" . ( $exact_match ? esc_sql( $val["value"] ) : ( '%%' . trim( esc_sql( $val["value"] ) )  . '%%' ) ) . "' )";
                    }
                }
            }
        }
    }
    if ( !empty( $query ) ) {

        global $wpdb;
        //phpcs:disable
        $matches = $wpdb->get_results( $wpdb->prepare("
                SELECT post_id as ID, meta_key, meta_value
                FROM $wpdb->postmeta
                INNER JOIN $wpdb->posts posts ON ( posts.ID = post_id AND posts.post_type = 'contacts' AND posts.post_status = 'publish' )
                WHERE ( $query )
                AND post_id != %s
            ", esc_sql( $contact_id ) ), ARRAY_A );
        //phpcs:enable
        $by_value = [];
        foreach ( $matches as $match ){
            $key = explode( '_', $match["meta_key"] )[0] . '_' . explode( '_', $match["meta_key"] )[1];
            $by_value[$key][$match["meta_value"]][] = $match;
        }
        foreach ( $by_value as $key => $values ){
            foreach ( $values as $meta_value => $matched ) {
                // if there are more than 20, it is most likely not a duplicate
                if ( sizeof( $matched ) < 20 ){
                    foreach ( $matched as $match ){
                        $duplicates[$key][] = $match;
                    }
                }
            }
        }
    }

    return $duplicates;
}

public static function save_duplicate_data( int $contact_id, array $duplicates) {
    if (empty( $duplicates )) { return; }
    $duplicates["override"] = array_values( $duplicates["override"] );
    update_post_meta( $contact_id, "duplicate_data", $duplicates );
}

public static function dismiss_all( int $contact_id) {
    if ( !$contact_id) { return; }
    $contact = self::get_contact( $contact_id );
    $possible_duplicates = self::get_duplicates_on_contact( $contact_id, false );
    $data = isset( $contact['duplicate_data'] ) ? is_array( $contact['duplicate_data'] ) ? $contact['duplicate_data'] : unserialize( $contact['duplicate_data'] ) : array();
    foreach ( $possible_duplicates as $dup ){
        $data['override'][] = (int) $dup["ID"];
    }
    $data['override'] = array_values( array_unique( $data['override'] ) );
    $data["check_dups"] = false;
    self::save_duplicate_data( $contact_id, $data );
    return $data;
}

public static function dismiss_duplicate( int $contact_id, int $dismiss_id) {
    if ( !$contact_id || !$dismiss_id) { return; }
    $contact = self::get_contact( $contact_id );
    $duplicate_data = isset( $contact['duplicate_data'] ) ? is_array( $contact['duplicate_data'] ) ? $contact['duplicate_data'] : unserialize( $contact['duplicate_data'] ) : array();
    if ( !in_array( $dismiss_id, $duplicate_data["override"] ) ) {
        $duplicate_data["override"][] = $dismiss_id;
    }
    if ( !empty( $duplicate_data["check_dups"] ) ){
        $possible_dups = self::get_possible_duplicates( $contact_id, $contact, true );
        $ids = [];
        foreach ( $possible_dups as $field_key => $vals ){
            foreach ( $vals as $val ){
                $ids[] = $val["ID"];
            }
        }
        if ( sizeof( array_unique( $ids ) ) <= sizeof( $duplicate_data["override"] ) ){
            $duplicate_data["check_dups"] = false;
        }
    }
    self::save_duplicate_data( $contact_id, $duplicate_data );
    return $duplicate_data;
}

public static function close_duplicate_contact( int $duplicate_id, int $contact_id) {
    $duplicate = self::get_contact( $duplicate_id );
    $contact = self::get_contact( $contact_id );

    self::update_contact( $duplicate_id, [
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

    self::add_comment( $duplicate_id, $comment, "duplicate", $args, true, true );
    self::dismiss_all( $duplicate_id );

    $user = wp_get_current_user();
    //comment on master
    $link = "[" . $duplicate['title'] .  "](" .  $duplicate_id . ")";
    $comment = sprintf( esc_html_x( '%1$s merged %2$s into this record', 'User1 merged Contact1 into this record', 'disciple_tools' ), $user->display_name, $link );
    self::add_comment( $contact_id, $comment, "duplicate", $args, true, true );
}


public static function merge_posts( $contact1, $contact2, $args ){
    $contact_fields = self::get_contact_fields();
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
            AND user_id NOT IN ( SELECT user_id FROM wp_dt_share WHERE post_id = %d )
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

    self::dismiss_duplicate( $master_id, $non_master_id );
    self::dismiss_duplicate( $non_master_id, $master_id );
    self::close_duplicate_contact( $non_master_id, $master_id );

    do_action( "dt_contact_merged", $master_id, $non_master_id );
    return true;
}

public static function remove_fields( $contact_id, $fields = [], $ignore = []) {
global $wpdb;
foreach ($fields as $field) {
$ignore_keys = preg_grep( "/$field/", $ignore );
$sql = "delete
from
$wpdb->postmeta
where
post_id = %d and
meta_key like %s";
$params = array( $contact_id, "$field%" );
if ( !empty( $ignore_keys )) {
foreach ( $ignore_keys as $key ){
$sql .= " and meta_key not like %s";
}
array_push( $params, ...$ignore_keys );
}
$wpdb->query( $wpdb->prepare( $sql, $params ) ); // @codingStandardsIgnoreLine
}
}
