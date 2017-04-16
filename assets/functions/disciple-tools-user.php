<?php


/**
 * Prepares the keys of user connections for WP_Query
 * This function builds the array for the meta_query used in WP_Query to retrieve only records associated with
 * the user or the teams the user is connected to.
 *
 * Example return:
 * Array
    (
        [relation] => OR
            [0] => Array
            (
                [key] => assigned_to
                [value] => user-1
            )

            [1] => Array
            (
                [key] => assigned_to
                [value] => group-1
            )
    )
 *
 * @return array
 */
function dt_get_user_associations () {

    // Set variables
    global $wpdb;
    $user_connections = array();

    // Set constructor
    $user_connections['relation'] = 'OR';

    // Get current user ID and build meta_key for current user
    $user_id = get_current_user_id();
    $user_key_value = 'user-' . $user_id;
    $user_connections[] = array('key' => 'assigned_to', 'value' => $user_key_value ) ;

    // Build arrays for current groups connected to user
    $sql = $wpdb->prepare(
        'SELECT %1$s.term_taxonomy_id 
          FROM %1$s
            WHERE object_id  = \'%2$d\'
            ',
            $wpdb->term_relationships,
            $user_id
        );
    $results = $wpdb->get_results( $sql, ARRAY_A );

    foreach ($results as $result) {
        $user_connections[] = array('key' => 'assigned_to', 'value' => 'group-' . $result['term_taxonomy_id']  );
    }
//    print '<pre>'; print_r($user_connections);

    // Return array to the meta_query
    return $user_connections;
}

/**
 * Gets the name of the Group or User
 * Used in the loop to get a friendly name of the 'assigned_to' field of the contact
 *
 * @return void
 */
function dt_get_assigned_name () {

    $metadata = get_post_meta( get_the_ID(), $key = 'assigned_to', true );

    $meta_array = explode('-', $metadata); // Separate the type and id
    $type = $meta_array[0]; // Build variables
    $id = $meta_array[1];

    // Build option for current value
    if ( $type == 'user') {

        if ($id == get_current_user_id()) {
            echo 'You';
        } else {
            echo 'Not you. Hey, wait a minute. Should you be seeing this?';
        }
    } else {

        $value = get_term( $id);
        echo $value->name;

    }
}

/**
 *
 * @return void
 */
function dt_get_contact_edit_form () {

    if(class_exists('Disciple_Tools')) {

        // Create the title field
        $html = '<table class="form-table">' . "\n";
        $html .= '<tbody>' . "\n";
        $html .= '<input type="hidden" name="dt_contacts_noonce" id="dt_contacts_noonce" value="' . wp_create_nonce( 'update_dt_contacts' ) . '" />';
        $html .= '<tr valign="top"><th scope="row"><label for="post_title">Title</label></th>
                                <td><input name="post_title" type="text" id="post_title" class="regular-text" value="'. get_the_title() .'" />' ;
        $html .= '</td><tr/></tbody></table>';
        echo $html;


        // Call the metadata fields
        $contact = new Disciple_Tools_Contact_Post_Type();

        echo $contact->meta_box_content('all');


    } // end if class exists

}

/**
 * Save contact
 *
 */
function dt_save_contact($post) {
    if(class_exists('Disciple_Tools')) {

        if($post['post_title'] != get_the_title()) {
            $my_post = array(
                'ID'           => get_the_ID(),
                'post_title'   => $post['post_title'],
            );
            wp_update_post( $my_post );
        }

        $contact = new Disciple_Tools_Contact_Post_Type();
        $contact->meta_box_save(get_the_ID());

        wp_redirect(get_permalink());
    }
}

/**
 *
 * @return void
 */
function dt_get_group_edit_form () {

    if(class_exists('Disciple_Tools')) {

        // Create the title field
        $html = '<table class="form-table">' . "\n";
        $html .= '<tbody>' . "\n";
        $html .= '<input type="hidden" name="dt_contacts_noonce" id="dt_contacts_noonce" value="' . wp_create_nonce( 'update_dt_groups' ) . '" />';
        $html .= '<tr valign="top"><th scope="row"><label for="post_title">Title</label></th>
                                <td><input name="post_title" type="text" id="post_title" class="regular-text" value="'. get_the_title() .'" />' ;
        $html .= '</td><tr/></tbody></table>';
        echo $html;


        // Call the metadata fields
        $group = new Disciple_Tools_Group_Post_Type();

        echo $group->meta_box_content();


    } // end if class exists

}

/**
 * Save contact
 *
 */
function dt_save_group($post) {
    if(class_exists('Disciple_Tools')) {

        if($post['post_title'] != get_the_title()) {
            $my_post = array(
                'ID'           => get_the_ID(),
                'post_title'   => $post['post_title'],
            );
            wp_update_post( $my_post );
        }

        $group = new Disciple_Tools_Group_Post_Type();
        $group->meta_box_save(get_the_ID());

        wp_redirect(get_permalink());
    }
}


































