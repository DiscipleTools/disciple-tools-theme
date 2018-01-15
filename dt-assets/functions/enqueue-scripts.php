<?php
declare(strict_types=1);

/**
 * Load scripts
 *
 * @param string $handle
 * @param string $rel_src
 * @param array  $deps
 * @param bool   $in_footer
 *
 * @throws \Error Dt_theme_enqueue_script took $rel_src argument which unexpectedly started with /.
 */
function dt_theme_enqueue_script( string $handle, string $rel_src, array $deps = array(), bool $in_footer = false ) {
    if ( $rel_src[0] === "/" ) {
        throw new Error( "dt_theme_enqueue_script took \$rel_src argument which unexpectedly started with /" );
    }
    wp_enqueue_script( $handle, get_template_directory_uri() . "/$rel_src", $deps, filemtime( get_template_directory() . "/$rel_src" ), $in_footer );
}

/**
 * Load styles
 *
 * @param string $handle
 * @param string $rel_src
 * @param array  $deps
 * @param string $media
 *
 * @throws \Error Dt_theme_enqueue_style took $rel_src argument which unexpectedly started with /.
 */
function dt_theme_enqueue_style( string $handle, string $rel_src, array $deps = array(), string $media = 'all' ) {
    if ( $rel_src[0] === "/" ) {
        throw new Error( "dt_theme_enqueue_style took \$rel_src argument which unexpectedly started with /" );
    }
    wp_enqueue_style( $handle, get_template_directory_uri() . "/$rel_src", $deps, filemtime( get_template_directory() . "/$rel_src" ), $media );
}

/**
 * Primary site script loader
 */
function dt_site_scripts() {
    global $wp_styles; // Call global $wp_styles variable to add conditional wrapper around ie stylesheet the WordPress way

    dt_theme_enqueue_script( 'modernizr-custom', 'dt-assets/js/modernizr-custom.js', [], true );
    dt_theme_enqueue_script( 'check-browser-version', 'dt-assets/js/check-browser-version.js', [ 'modernizr-custom' ], true );

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


    dt_theme_enqueue_script( 'lodash', 'dt-core/dependencies/lodash/lodash.min.js', array() );

    dt_theme_enqueue_script( 'typeahead-jquery', 'dt-core/dependencies/jquery-typeahead-2.10.4/jquery.typeahead.min.js', array( 'jquery' ), true );
    dt_theme_enqueue_style( 'typeahead-jquery-css', 'dt-core/dependencies/jquery-typeahead-2.10.4/jquery.typeahead.min.css', array() );

    dt_theme_enqueue_script( 'site-js', 'dt-assets/build/js/scripts.min.js', array( 'jquery' ), true );


    // Register main stylesheet
    dt_theme_enqueue_style( 'site-css', 'dt-assets/build/css/style.min.css', array() );

    // Comment reply script for threaded comments
    if ( is_singular() && comments_open() && (get_option( 'thread_comments' ) == 1)) {
        wp_enqueue_script( 'comment-reply' );
    }

    add_action( 'wp_footer', function() {
        ?>
        <!-- BEGIN GROOVE WIDGET CODE -->
        <script id="grv-widget">
        /*<![CDATA[*/
        window.groove = window.groove || {}; groove.widget = function(){ groove._widgetQueue.push(Array.prototype.slice.call(arguments)); }; groove._widgetQueue = [];
        groove.widget('setWidgetId', 'fbdef482-8bc6-b65d-1f25-bef642edf597');
        <?php if (is_user_logged_in()): ?>
        groove.widget('setCustomer', {email: "<?php echo esc_js( wp_get_current_user()->user_email ); ?>"});
        <?php endif; ?>
        !function(g,r,v){var a,n,c=r.createElement("iframe");(c.frameElement||c).style.cssText="width: 0; height: 0; border: 0",c.title="",c.role="presentation",c.src="javascript:false",r.body.appendChild(c);try{a=c.contentWindow.document}catch(i){n=r.domain;var b=["javascript:document.write('<he","ad><scri","pt>document.domain=","\"",n,"\";</scri","pt></he","ad><bo","dy></bo","dy>')"];c.src=b.join(""),a=c.contentWindow.document}var d="https:"==r.location.protocol?"https://":"http://",s="http://groove-widget-production.s3.amazonaws.com".replace("http://",d);c.className="grv-widget-tag",a.open()._l=function(){n&&(this.domain=n);var t=this.createElement("script");t.type="text/javascript",t.charset="utf-8",t.async=!0,t.src=s+"/loader.js",this.body.appendChild(t)};var p=["<bo",'dy onload="document._l();">'];a.write(p.join("")),a.close()}(window,document)
        /*]]>*/
        </script>
        <!-- END GROOVE WIDGET CODE -->
        <?php
    } );


    dt_theme_enqueue_script( 'api-wrapper', 'dt-assets/js/api-wrapper.js', array( 'jquery', 'lodash' ) );
    wp_localize_script(
        'api-wrapper', 'wpApiSettings', array(
            'root' => esc_url_raw( rest_url() ),
            'nonce' => wp_create_nonce( 'wp_rest' ),
        )
    );

    dt_theme_enqueue_script( 'dt-notifications', 'dt-assets/js/notifications.js', array( 'jquery' ) );
    wp_localize_script(
        'dt-notifications', 'wpApiNotifications', array(
            'root' => esc_url_raw( rest_url() ),
            'nonce' => wp_create_nonce( 'wp_rest' ),
            'current_user_login' => wp_get_current_user()->user_login,
            'current_user_id' => get_current_user_id(),
        )
    );


    $translations = [
        "not-set" => [
            "location" => __( 'No location set', 'disciple_tools' ),
            "people-group" => __( 'No people group set', 'disciple_tools' ),
            "email" => __( 'No email set', 'disciple_tools' ),
            "phone" => __( 'No phone set', 'disciple_tools' ),
            "address" => __( 'No address set', 'disciple_tools' ),
            "social" => __( 'None set', 'disciple_tools' )
        ],
        "valid" => __( 'Valid', 'disciple_tools' ),
        "invalid" => __( 'Invalid', 'disciple_tools' ),
        "unconfirmed" => __( 'Unconfirmed', 'disciple_tools' ),
        'delete' => __( 'Delete item', 'disciple_tools' ),
        'email' => __( 'email' )
    ];
    if (is_singular( "contacts" )){
        dt_theme_enqueue_script( 'contact-details', 'dt-assets/js/contact-details.js', array( 'jquery', 'lodash', 'api-wrapper', 'moment', 'typeahead-jquery' ) );
        $contact = Disciple_Tools_Contacts::get_contact( get_the_ID() );
        wp_localize_script(
            'contact-details', 'contactsDetailsWpApiSettings', array(
                'contact' => $contact,
                'contact_author_name' => get_user_by( 'id', intval( $contact->post_author ) )->display_name,
                'root' => esc_url_raw( rest_url() ),
                'nonce' => wp_create_nonce( 'wp_rest' ),
                'contacts_custom_fields_settings' => Disciple_Tools_Contact_Post_Type::instance()->get_custom_fields_settings( false ),
                'channels' => Disciple_Tools_Contacts::get_channel_list(),
                'template_dir' => get_template_directory_uri(),
                'txt_created_contact' => __( "Created contact at {}" ),
                'translations' => $translations
            )
        );
    }
    if (is_singular( "groups" )){
        dt_theme_enqueue_script( 'group-details', 'dt-assets/js/group-details.js', array( 'jquery', 'lodash', 'typeahead-jquery', 'api-wrapper', 'moment' ) );
        $group = Disciple_Tools_Groups::get_group( get_the_ID() );
        $group_post = get_post( $group["ID"] );
        wp_localize_script(
            'group-details', 'wpApiGroupsSettings', array(
                'group' => $group,
                'group_post' => $group_post,
                'group_author_name' => get_user_by( 'id', intval( $group_post->post_author ) )->display_name,
                'root' => esc_url_raw( rest_url() ),
                'nonce' => wp_create_nonce( 'wp_rest' ),
                'template_dir' => get_template_directory_uri(),
                'txt_created_group' => __( "Created group at {}" ),
                'translations' => $translations
            )
        );
    }



    $url_path = trim( parse_url( add_query_arg( array() ), PHP_URL_PATH ), '/' );
    if ( 'settings' === $url_path ) {
        dt_theme_enqueue_script( 'dt-settings', 'dt-assets/js/settings.js', array( 'jquery', 'jquery-ui', 'lodash', 'typeahead' ),  true );
        wp_localize_script(
            'dt-settings', 'wpApiSettingsPage', array(
                'root' => esc_url_raw( rest_url() ),
                'nonce' => wp_create_nonce( 'wp_rest' ),
                'current_user_login' => wp_get_current_user()->user_login,
                'current_user_id' => get_current_user_id(),
            )
        );
    }
    if ( 'metrics' === $url_path ) {
        dt_theme_enqueue_script( 'dt-metrics', 'dt-assets/js/metrics.js', array( 'jquery', 'jquery-ui' ),  true );
        wp_localize_script(
            'dt-metrics', 'wpApiMetricsPage', array(
                'root' => esc_url_raw( rest_url() ),
                'nonce' => wp_create_nonce( 'wp_rest' )
            )
        );
        wp_enqueue_script( 'google-charts', 'https://www.gstatic.com/charts/loader.js', array(),  false );
    }
    if ( 'locations' === $url_path && ( is_archive() || is_singular( 'locations' ) ) ) {
        dt_theme_enqueue_script( 'dt-locations', 'dt-assets/js/locations.js', array( 'jquery', 'jquery-ui' ),  true );
        wp_localize_script(
            'dt-locations', 'wpApiLocationsPage', array(
                'root' => esc_url_raw( rest_url() ),
                'nonce' => wp_create_nonce( 'wp_rest' )
            )
        );
        wp_enqueue_script( 'google-maps', 'https://maps.googleapis.com/maps/api/js?key=' . dt_get_option( 'map_key' ), array(), null, true );

    }

    if (is_post_type_archive( "contacts" ) || is_post_type_archive( "groups" )) {
        dt_theme_enqueue_script( 'data-tables', 'dt-core/dependencies/DataTables/datatables.min.js', array( 'jquery' ) );
        dt_theme_enqueue_style( 'data-tables', 'dt-core/dependencies/DataTables/datatables.min.css', array() );
        dt_theme_enqueue_script( 'list-js', 'dt-assets/js/list.js', array( 'jquery', 'lodash', 'data-tables', 'site-js' ), true );
        $post_type = null;
        if (is_post_type_archive( "contacts" )) {
            $post_type = "contacts";
        } elseif (is_post_type_archive( "groups" )) {
            $post_type = "groups";
        }
        wp_localize_script( 'list-js', 'wpApiSettings', array(
            'root' => esc_url_raw( rest_url() ),
            'nonce' => wp_create_nonce( 'wp_rest' ),
            'txt_error' => __( 'An error occurred', 'disciple_tools' ),
            'txt_no_filters' => __( 'No filters', 'disciple_tools' ),
            'txt_yes' => __( 'Yes', 'disciple_tools' ),
            'txt_no' => __( 'No', 'disciple_tools' ),
            'txt_search' => __( 'Search', 'disciple_tools' ),
            'txt_first' => __( 'First', 'disciple_tools' ),
            'txt_last' => __( 'Last', 'disciple_tools' ),
            'txt_next' => __( 'Next', 'disciple_tools' ),
            'txt_previous' => __( 'Previous', 'disciple_tools' ),
            'txt_zeroRecords' => __( 'No matching records found', 'disciple_tools' ),
            'txt_info' => _x( 'Showing _START_ to _END_ of _TOTAL_ entries', 'just copy as they are: _START_ _END_ and _TOTAL_', 'disciple_tools' ),
            'txt_infoEmpty' => __( 'Showing 0 to 0 of 0 entries', 'disciple_tools' ),
            'txt_infoFiltered' => _x( '(filtered from _MAX_ total entries)', 'just copy `_MAX_`', 'disciple_tools' ),
            'contacts_custom_fields_settings' => Disciple_Tools_Contact_Post_Type::instance()->get_custom_fields_settings( false ),
            'groups_custom_fields_settings' => Disciple_Tools_Groups_Post_type::instance()->get_custom_fields_settings(),
            'template_directory_uri' => get_template_directory_uri(),
            'current_user_login' => wp_get_current_user()->user_login,
            'current_user_roles' => wp_get_current_user()->roles,
            'current_post_type' => $post_type,
        ) );
    }

}
add_action( 'wp_enqueue_scripts', 'dt_site_scripts', 999 );
