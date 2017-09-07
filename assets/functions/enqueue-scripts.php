<?php
function site_scripts() {
    global $wp_styles; // Call global $wp_styles variable to add conditional wrapper around ie stylesheet the WordPress way

    /**
     * Force new version of jQuery.
     * Forcing newest version of jquery and jquery ui because of the themes use of controlgroups and checkboxradio widget. Once Wordpress core updates to 1.12, then
     * the next section could be removed.
     */

    /** jQuery UI custom theme styles. @see http://jqueryui.com/themeroller/  */
    wp_enqueue_style( 'jquery-ui-site-css', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/base/jquery-ui.css', array(), '', 'all' );

    // comment out the next two lines to load the local copy of jQuery
    wp_deregister_script( 'jquery' );
    wp_register_script( 'jquery', 'https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js', false, '1.12.4' );
    wp_enqueue_script( 'jquery' );

    // comment out the next two lines to load the local copy of jQuery
    wp_register_script( 'jquery-ui', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js', false, '1.12.1' );
    wp_enqueue_script( 'jquery-ui' );
    /**
     * End jQuery force new version
     */

    wp_enqueue_script( 'lodash', get_template_directory_uri() . '/vendor/lodash/lodash.min.js', array(), '4.17.4' );

    wp_enqueue_script( 'typeahead', get_template_directory_uri() . '/vendor/corejs-typeahead/dist/typeahead.bundle.min.js', array( 'jquery' ), filemtime( get_template_directory() . '/assets/scripts/' ), true );

    wp_enqueue_script( 'site-js', get_template_directory_uri() . '/assets/scripts/scripts.min.js', array( 'jquery'), filemtime( get_template_directory() . '/assets/scripts/' ), true );


    // Register main stylesheet
    wp_enqueue_style( 'site-css', get_template_directory_uri() . '/assets/css/style.min.css', array(), '', 'all' );

    // Comment reply script for threaded comments
    if ( is_singular() and comments_open() and (get_option( 'thread_comments' ) == 1)) {
        wp_enqueue_script( 'comment-reply' );
    }

    if (is_singular( "contacts" )){
        wp_enqueue_script( 'contact-details', get_template_directory_uri() . '/assets/js/contact-details.js', array( 'jquery', 'lodash', 'typeahead' ) );
        wp_localize_script(
            'contact-details', 'wpApiSettings', array(
                'root' => esc_url_raw( rest_url() ),
                'nonce' => wp_create_nonce( 'wp_rest' ),
                'contacts_custom_fields_settings' => Disciple_Tools_Contact_Post_Type::instance()->get_custom_fields_settings( false ),
            )
        );
    }


    if (is_post_type_archive( "contacts" ) || is_post_type_archive( "groups" )) {
        wp_enqueue_script( 'data-tables', get_template_directory_uri() . '/vendor/DataTables/datatables.min.js',  array( 'jquery' ), '1.10.15' );
        wp_enqueue_style( 'data-tables', get_template_directory_uri() . '/vendor/DataTables/datatables.min.css', array(), '', 'all' );
        wp_enqueue_script( 'list-js', get_template_directory_uri() . '/assets/js/list.js', array( 'jquery', 'lodash', 'data-tables' ) );
        $post_type = null;
        if (is_post_type_archive( "contacts" )) {
            $post_type = "contacts";
        } elseif (is_post_type_archive( "groups" )) {
            $post_type = "groups";
        }
        wp_localize_script( 'list-js', 'wpApiSettings', array(
            'root' => esc_url_raw( rest_url() ),
            'nonce' => wp_create_nonce( 'wp_rest' ),
            'txt_error' => __( 'An error occurred' ),
            'txt_no_filters' => __( 'No filters' ),
            'txt_yes' => __( 'Yes' ),
            'txt_no' => __( 'No' ),
            'contacts_custom_fields_settings' => Disciple_Tools_Contact_Post_Type::instance()->get_custom_fields_settings( false ),
            'groups_custom_fields_settings' => Disciple_Tools_Groups_Post_type::instance()->get_custom_fields_settings(),
            'template_directory_uri' => get_template_directory_uri(),
            'current_user_login' => wp_get_current_user()->user_login,
            'current_post_type' => $post_type,
        ) );
    }


}
add_action( 'wp_enqueue_scripts', 'site_scripts', 999 );



