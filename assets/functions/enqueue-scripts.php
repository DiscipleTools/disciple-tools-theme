<?php
declare(strict_types=1);

function dt_theme_enqueue_script( string $handle, string $rel_src, array $deps = array(), bool $in_footer = false ) {
    if ( $rel_src[0] === "/" ) {
        throw new Error( "dt_theme_enqueue_script took \$rel_src argument which unexpectedly started with /" );
    }
    wp_enqueue_script( $handle, get_template_directory_uri() . "/$rel_src", $deps, filemtime( get_template_directory() . "/$rel_src" ), $in_footer );
}

function dt_theme_enqueue_style( string $handle, string $rel_src, array $deps = array(), string $media = 'all' ) {
    if ( $rel_src[0] === "/" ) {
        throw new Error( "dt_theme_enqueue_style took \$rel_src argument which unexpectedly started with /" );
    }
    wp_enqueue_style( $handle, get_template_directory_uri() . "/$rel_src", $deps, filemtime( get_template_directory() . "/$rel_src" ), $media );
}


function dt_site_scripts() {
    global $wp_styles; // Call global $wp_styles variable to add conditional wrapper around ie stylesheet the WordPress way

    wp_enqueue_style( 'foundation-css', 'https://cdnjs.cloudflare.com/ajax/libs/foundicons/3.0.0/foundation-icons.css' );

    /**
     * Force new version of jQuery.
     * Forcing newest version of jquery and jquery ui because of the themes use of controlgroups and checkboxradio widget. Once Wordpress core updates to 1.12, then
     * the next section could be removed.
     */

    /** jQuery UI custom theme styles. @see http://jqueryui.com/themeroller/  */
    wp_enqueue_style( 'jquery-ui-site-css', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/base/jquery-ui.css', array(), '', 'all' );

    // comment out the next two lines to load the local copy of jQuery
    wp_deregister_script( 'jquery' );
    wp_register_script( 'jquery', 'https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js', false, '1.12.4' );
    wp_enqueue_script( 'jquery' );

    // comment out the next two lines to load the local copy of jQuery
    wp_register_script( 'jquery-ui', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js', false, '1.12.1' );
    wp_enqueue_script( 'jquery-ui' );
    /**
     * End jQuery force new version
     */
    wp_register_script( 'moment', 'https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.19.1/moment.min.js', false, '2.19.1' );
    wp_enqueue_script( 'moment' );


    dt_theme_enqueue_script( 'lodash', 'dependencies/lodash/lodash.min.js', array() );

    dt_theme_enqueue_script( 'typeahead', 'dependencies/typeahead/typeahead.bundle.min.js', array( 'jquery' ), true );

    dt_theme_enqueue_script( 'site-js', 'build/js/scripts.min.js', array( 'jquery' ), true );


    // Register main stylesheet
    dt_theme_enqueue_style( 'site-css', 'build/css/style.min.css', array() );

    // Comment reply script for threaded comments
    if ( is_singular() && comments_open() && (get_option( 'thread_comments' ) == 1)) {
        wp_enqueue_script( 'comment-reply' );
    }


    dt_theme_enqueue_script( 'api-wrapper', 'assets/js/api-wrapper.js', array( 'jquery', 'lodash') );
    wp_localize_script(
        'api-wrapper', 'wpApiSettings', array(
            'root' => esc_url_raw( rest_url() ),
            'nonce' => wp_create_nonce( 'wp_rest' ),
        )
    );

    if (is_singular( "contacts" )){
        dt_theme_enqueue_script( 'contact-details', 'assets/js/contact-details.js', array( 'jquery', 'lodash', 'typeahead', 'api-wrapper', 'moment' ), true );
        wp_localize_script(
            'contact-details', 'contactsDetailsWpApiSettings', array(
                'contact' => Disciple_Tools_Contacts::get_contact( get_the_ID() ),
                'root' => esc_url_raw( rest_url() ),
                'nonce' => wp_create_nonce( 'wp_rest' ),
                'contacts_custom_fields_settings' => Disciple_Tools_Contact_Post_Type::instance()->get_custom_fields_settings( false ),
                'channels' => Disciple_Tools_Contacts::get_channel_list(),
                'template_dir' => get_template_directory_uri()
            )
        );
    }
    if (is_singular( "groups" )){
        dt_theme_enqueue_script( 'group-details', 'assets/js/group-details.js', array( 'jquery', 'lodash', 'typeahead', 'api-wrapper', 'moment' ) );
        wp_localize_script(
            'group-details', 'wpApiGroupsSettings', array(
                'group' => Disciple_Tools_Groups::get_group( get_the_ID() ),
                'root' => esc_url_raw( rest_url() ),
                'nonce' => wp_create_nonce( 'wp_rest' ),
            )
        );
    }

    /**
     * Enqueue for single utility pages
     */
    dt_theme_enqueue_script( 'dt-notifications', 'assets/js/notifications.js', array( 'jquery', 'lodash', 'typeahead' ),  true );
    wp_localize_script(
        'dt-notifications', 'wpApiNotifications', array(
            'root' => esc_url_raw( rest_url() ),
            'nonce' => wp_create_nonce( 'wp_rest' ),
            'current_user_login' => wp_get_current_user()->user_login,
            'current_user_id' => get_current_user_id(),
        )
    );

    $url_path = trim( parse_url( add_query_arg( array() ), PHP_URL_PATH ), '/' );
    if ( $url_path === 'settings' ) {
        dt_theme_enqueue_script( 'dt-settings', 'assets/js/settings.js', array( 'jquery', 'jquery-ui', 'lodash', 'typeahead' ),  true );
        wp_localize_script(
            'dt-settings', 'wpApiSettingsPage', array(
                'root' => esc_url_raw( rest_url() ),
                'nonce' => wp_create_nonce( 'wp_rest' ),
                'current_user_login' => wp_get_current_user()->user_login,
                'current_user_id' => get_current_user_id(),
            )
        );
    }
    if ( $url_path === 'metrics' ) {
        dt_theme_enqueue_script( 'dt-metrics', 'assets/js/metrics.js', array( 'jquery', 'jquery-ui' ),  true );
        wp_localize_script(
            'dt-metrics', 'wpApiMetricsPage', array(
                'root' => esc_url_raw( rest_url() ),
                'nonce' => wp_create_nonce( 'wp_rest' )
            )
        );
        wp_enqueue_script( 'google-charts', 'https://www.gstatic.com/charts/loader.js', array( ),  false );
    }

    if (is_post_type_archive( "contacts" ) || is_post_type_archive( "groups" )) {
        dt_theme_enqueue_script( 'data-tables', 'dependencies/DataTables/datatables.min.js',  array( 'jquery' ) );
        dt_theme_enqueue_style( 'data-tables', 'dependencies/DataTables/datatables.min.css', array() );
        dt_theme_enqueue_script( 'list-js', 'assets/js/list.js', array( 'jquery', 'lodash', 'data-tables', 'site-js' ), true );
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
            'txt_search' => __( 'Search' ),
            'contacts_custom_fields_settings' => Disciple_Tools_Contact_Post_Type::instance()->get_custom_fields_settings( false ),
            'groups_custom_fields_settings' => Disciple_Tools_Groups_Post_type::instance()->get_custom_fields_settings(),
            'template_directory_uri' => get_template_directory_uri(),
            'current_user_login' => wp_get_current_user()->user_login,
            'current_post_type' => $post_type,
        ) );
    }

}
add_action( 'wp_enqueue_scripts', 'dt_site_scripts', 999 );
