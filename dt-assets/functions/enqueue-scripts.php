<?php
declare(strict_types=1);

/**
 * Load scripts, in a way that implements cache-busting
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
 * Register scripts, in a way that implements cache-busting
 *
 */
function dt_theme_register_script( string $handle, string $rel_src, array $deps = array(), bool $in_footer = false ) {
    if ( $rel_src[0] === "/" ) {
        throw new Error( "dt_theme_register_script took \$rel_src argument which unexpectedly started with /" );
    }
    return wp_register_script( $handle, get_template_directory_uri() . "/$rel_src", $deps, filemtime( get_template_directory() . "/$rel_src" ), $in_footer );
}


/**
 * Load styles, in a way that implements cache-busting
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
 * Register styles, in a way that implements cache-busting
 */
function dt_theme_register_style( string $handle, string $rel_src, array $deps = array(), string $media = 'all' ) {
    if ( $rel_src[0] === "/" ) {
        throw new Error( "dt_theme_register_style took \$rel_src argument which unexpectedly started with /" );
    }
    return wp_register_style( $handle, get_template_directory_uri() . "/$rel_src", $deps, filemtime( get_template_directory() . "/$rel_src" ), $media );
}


/**
 * Primary site script loader
 */
function dt_site_scripts() {

    dt_theme_enqueue_script( 'modernizr-custom', 'dt-assets/js/modernizr-custom.js', [], true );
    dt_theme_enqueue_script( 'check-browser-version', 'dt-assets/js/check-browser-version.js', [ 'modernizr-custom' ], true );

    // phpcs:ignore WordPress.WP.EnqueuedResourceParameters
    wp_enqueue_style( 'foundation-css', 'https://cdnjs.cloudflare.com/ajax/libs/foundicons/3.0.0/foundation-icons.css' );

    // phpcs:ignore WordPress.WP.EnqueuedResourceParameters
    wp_enqueue_style( 'jquery-ui-site-css', 'https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.css', array(), '', 'all' );
    wp_deregister_script( 'jquery' );
    wp_register_script( 'jquery', 'https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js', false, '3.2.1' );
    wp_enqueue_script( 'jquery' );
    wp_register_script( 'jquery-ui', 'https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js', false, '1.12.1' );
    wp_enqueue_script( 'jquery-ui' );

//    wp_register_script( 'moment-js', 'https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.19.1/moment.min.js', false, '2.19.1' );
//    wp_enqueue_script( 'moment-js' );
//    wp_register_script( 'lodash', 'https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.11/lodash.min.js', false, '4.17.11' );
//    wp_enqueue_script( 'lodash' );


    dt_theme_enqueue_script( 'site-js', 'dt-assets/build/js/scripts.min.js', array( 'jquery' ), true );

    // Register main stylesheet
    dt_theme_enqueue_style( 'site-css', 'dt-assets/build/css/style.min.css', array() );

    // Comment reply script for threaded comments
    if ( is_singular() && comments_open() && ( get_option( 'thread_comments' ) == 1 )) {
        wp_enqueue_script( 'comment-reply' );
    }


    global $pagenow;
    if ( is_multisite() && 'wp-activate.php' === $pagenow ) {
        return;
    }

    dt_theme_enqueue_script( 'shared-functions', 'dt-assets/js/shared-functions.js', array( 'jquery', 'lodash', 'wp-i18n' ) );
    wp_localize_script(
        'shared-functions', 'wpApiShare', array(
            'root' => esc_url_raw( rest_url() ),
            'nonce' => wp_create_nonce( 'wp_rest' ),
            'site_url' => get_site_url(),
            'template_dir' => get_template_directory_uri()
        )
    );

    dt_theme_enqueue_script( 'dt-notifications', 'dt-assets/js/notifications.js', array( 'jquery' ) );
    wp_localize_script(
        'dt-notifications', 'wpApiNotifications', array(
            'root' => esc_url_raw( rest_url() ),
            'nonce' => wp_create_nonce( 'wp_rest' ),
            'current_user_login' => wp_get_current_user()->user_login,
            'current_user_id' => get_current_user_id(),
            'translations' => [
                "no-unread" => __( "You don't have any unread notifications", "disciple_tools" ),
                "no-notifications" => __( "You don't have any notifications", "disciple_tools" )
            ]
        )
    );

    dt_theme_enqueue_script( 'typeahead-jquery', 'dt-core/dependencies/typeahead/dist/jquery.typeahead.min.js', array( 'jquery' ), true );
    dt_theme_enqueue_style( 'typeahead-jquery-css', 'dt-core/dependencies/typeahead/dist/jquery.typeahead.min.css', array() );

    $post_types = apply_filters( 'dt_registered_post_types', [ 'contacts', 'groups' ] );
    if ( is_singular( $post_types ) ) {
        $post_type = get_post_type();
        if ( is_singular( "contacts" )){
            $post = Disciple_Tools_Contacts::get_contact( get_the_ID(), true, true );
        } elseif ( is_singular( "groups" ) ){
            $post = Disciple_Tools_Groups::get_group( get_the_ID(), true, true );
        } else {
            $post = DT_Posts::get_post( get_post_type(), get_the_ID() );
        }
        if ( !is_wp_error( $post )){
            $post_settings = apply_filters( "dt_get_post_type_settings", [], $post_type );
            dt_theme_enqueue_script( 'jquery-mentions', 'dt-core/dependencies/jquery-mentions-input/jquery.mentionsInput.min.js', array( 'jquery' ), true );
            dt_theme_enqueue_script( 'jquery-mentions-elastic', 'dt-core/dependencies/jquery-mentions-input/lib/jquery.elastic.min.js', array( 'jquery' ), true );
            dt_theme_enqueue_style( 'jquery-mentions-css', 'dt-core/dependencies/jquery-mentions-input/jquery.mentionsInput.css', array() );
            dt_theme_enqueue_script( 'comments', 'dt-assets/js/comments.js', array(
                'jquery',
                'lodash',
                'shared-functions',
                'moment',
                'jquery-mentions',
                'jquery-mentions-elastic',
                'wp-i18n'
            ) );
            wp_localize_script(
                'comments', 'commentsSettings', [
                    "post" => get_post(),
                    'post_with_fields' => $post,
                    'txt_created' => __( "Created record at {}" ),
                    'template_dir' => get_template_directory_uri(),
                    'contact_author_name' => isset( $post->post_author ) && (int) $post->post_author > 0 ? get_user_by( 'id', intval( $post->post_author ) )->display_name : "",
                    'translations' => [
                        "edit" => __( "edit", "disciple_tools" ),
                        "delete" => __( "delete", "disciple_tools" )
                    ],
                    'current_user_id' => get_current_user_id(),
                    'additional_sections' => apply_filters( 'dt_comments_additional_sections', [], $post_type ),
                    'comments' => DT_Posts::get_post_comments( $post_type, $post["ID"] ),
                    'activity' => DT_Posts::get_post_activity( $post_type, $post["ID"] )
                ]
            );
            dt_theme_enqueue_script( 'details', 'dt-assets/js/details.js', array(
                'jquery',
                'lodash',
                'shared-functions',
            ) );
            wp_localize_script( 'details', 'detailsSettings', [
                'post_type' => $post_type,
                'post_id' => get_the_ID(),
                'post_settings' => $post_settings,
                'current_user_id' => get_current_user_id(),
                'post_fields' => $post
            ]);


            $translations = [
                "not-set"     => [
                    "source"     => __( 'No source set', 'disciple_tools' ),
                    "location_grid"     => __( 'No location set', 'disciple_tools' ),
                    "leaders"     => __( 'No leaders set', 'disciple_tools' ),
                    "people_groups" => __( 'No people group set', 'disciple_tools' ),
                    "email"        => __( 'No email set', 'disciple_tools' ),
                    "phone"        => __( 'No phone set', 'disciple_tools' ),
                    "address"      => __( 'No address set', 'disciple_tools' ),
                    "social"       => __( 'None set', 'disciple_tools' ),
                    "subassigned"  => __( 'No sub-assigned set', 'disciple_tools' ),
                    "age" => __( 'No age set', 'disciple_tools' ),
                    "gender" => __( 'No gender set', 'disciple_tools' ),
                ],
                "valid"       => __( 'Valid', 'disciple_tools' ),
                "invalid"     => __( 'Invalid', 'disciple_tools' ),
                "unconfirmed" => __( 'Unconfirmed', 'disciple_tools' ),
                'delete'      => __( 'Delete item', 'disciple_tools' ),
                'email'       => __( 'email' ),
                'transfer_error' => __( 'Transfer failed. Check site-to-site configuration.', 'disciple_tools' )
            ];
            if ( is_singular( "contacts" ) ) {
                dt_theme_enqueue_script( 'contact-details', 'dt-assets/js/contact-details.js', array(
                    'jquery',
                    'lodash',
                    'shared-functions',
                    'typeahead-jquery',
                    'comments'
                ) );
                wp_localize_script(
                    'contact-details', 'contactsDetailsWpApiSettings', array(
                        'contact'                         => $post,
                        'root'                            => esc_url_raw( rest_url() ),
                        'nonce'                           => wp_create_nonce( 'wp_rest' ),
                        'contacts_custom_fields_settings' => Disciple_Tools_Contact_Post_Type::instance()->get_custom_fields_settings( false ),
                        'sources'                         => Disciple_Tools_Contact_Post_Type::instance()->get_custom_fields_settings( false, null, true )['sources']["default"],
                        'channels'                        => Disciple_Tools_Contacts::get_channel_list(),
                        'template_dir'                    => get_template_directory_uri(),
                        'can_view_all'                    => user_can( get_current_user_id(), 'view_any_contacts' ),
                        'current_user_id'                 => get_current_user_id(),
                        'spinner_url'                     => get_template_directory_uri() . '/dt-assets/images/ajax-loader.gif',
                        'translations'                    => apply_filters( 'dt_contacts_js_translations', $translations ),
                        'custom_data'                     => apply_filters( 'dt_contacts_js_data', [] ), // nest associated array
                    )
                );
            } elseif ( is_singular( "groups" ) ) {
                dt_theme_enqueue_script( 'group-details', 'dt-assets/js/group-details.js', array(
                    'jquery',
                    'lodash',
                    'typeahead-jquery',
                    'shared-functions'
                ) );
                wp_localize_script(
                    'group-details', 'wpApiGroupsSettings', array(
                        'group'             => $post,
                        'groups_custom_fields_settings' => Disciple_Tools_Groups_Post_Type::instance()->get_custom_fields_settings( false ),
                        'group_author_name' => isset( $post->post_author ) && (int) $post->post_author > 0 ? get_user_by( 'id', intval( $post->post_author ) )->display_name : "",
                        'root'              => esc_url_raw( rest_url() ),
                        'nonce'             => wp_create_nonce( 'wp_rest' ),
                        'template_dir'      => get_template_directory_uri(),
                        'current_user_id'   => get_current_user_id(),
                        'translations'      => apply_filters( 'dt_groups_js_translations', $translations ),
                        'custom_data'       => apply_filters( 'dt_groups_js_data', [] ), // nest associated array
                    )
                );
            }
        }
    }

    $url_path = dt_get_url_path();
    if ( 'settings' === $url_path ) {
        DT_Mapping_Module::instance()->drilldown_script();
        dt_theme_enqueue_script( 'dt-settings', 'dt-assets/js/settings.js', array( 'jquery', 'jquery-ui', 'lodash', 'mapping-drill-down', 'moment' ), true );
        wp_localize_script(
            'dt-settings', 'wpApiSettingsPage', array(
                'root'                  => esc_url_raw( rest_url() ),
                'nonce'                 => wp_create_nonce( 'wp_rest' ),
                'current_user_login'    => wp_get_current_user()->user_login,
                'current_user_id'       => get_current_user_id(),
                'template_dir'          => get_template_directory_uri(),
                'associated_contact_id' => dt_get_associated_user_id( get_current_user_id(), 'user' ),
                'translations'          => apply_filters( 'dt_settings_js_translations', [
                    'delete' => __( 'delete', 'disciple_tools' ),
                    'responsible_for_locations' => __( "Locations you are responsible for", 'disciple_tools' )
                ] ),
                'custom_data'           => apply_filters( 'dt_settings_js_data', [] ), // nest associated array
            )
        );
    }

    if ( is_post_type_archive( "contacts" ) || is_post_type_archive( "groups" ) ) {
        $post_type = null;
        $custom_field_settings = [];
        if (is_post_type_archive( "contacts" )) {
            $post_type = "contacts";
            $custom_field_settings = Disciple_Tools_Contact_Post_Type::instance()->get_custom_fields_settings( false );
            dt_theme_enqueue_script( 'list-js', 'dt-assets/js/list.js', array( 'jquery', 'lodash', 'shared-functions', 'typeahead-jquery', 'site-js' ), true );
        } elseif (is_post_type_archive( "groups" )) {
            dt_theme_enqueue_script( 'list-js', 'dt-assets/js/list.js', array( 'jquery', 'lodash', 'shared-functions', 'site-js' ), true );
            $post_type = "groups";
            $custom_field_settings = Disciple_Tools_Groups_Post_type::instance()->get_custom_fields_settings();
        }
        $translations = [
            'save' => __( 'Save', 'disciple_tools' ),
            'edit' => __( 'Edit', 'disciple_tools' ),
            'delete' => __( 'Delete', 'disciple_tools' ),
            'txt_info' => _x( 'Showing _START_ of _TOTAL_', 'just copy as they are: _START_ and _TOTAL_', 'disciple_tools' ),
            'filter_my' => __( 'Assigned to me', 'disciple_tools' ),
            'filter_subassigned' => __( 'Subassigned to me', 'disciple_tools' ),
            'filter_shared' => __( 'Shared with me', 'disciple_tools' ),
            'filter_all' => sprintf( _x( 'All %s', 'Contacts or Groups', 'disciple_tools' ), Disciple_Tools_Posts::get_label_for_post_type( $post_type ) ),
            'filter_needs_accepted' => __( 'Waiting to be accepted', 'disciple_tools' ),
            'filter_unassigned' => __( 'Dispatch needed', 'disciple_tools' ),
            'filter_unassignable' => __( 'Not Ready', 'disciple_tools' ),
            'filter_update_needed' => __( 'Update needed', 'disciple_tools' ),
            'filter_meeting_scheduled' => __( 'Meeting scheduled', 'disciple_tools' ),
            'filter_contact_unattempted' => __( 'Contact attempt needed', 'disciple_tools' ),
            'filter_assignment_needed' => __( 'Dispatch needed', 'disciple_tools' ),
            'filter_new' => __( 'New', 'disciple_tools' ),
            'filter_active' => __( 'Active', 'disciple_tools' ),
            'range_start' => __( 'start', 'disciple_tools' ),
            'range_end' => __( 'end', 'disciple_tools' ),
            'sorting_by' => __( 'Sorting By', 'disciple_tools' ),
            'creation_date' => __( 'Creation Date', 'disciple_tools' ),
            'date_modified' => __( 'Date Modified', 'disciple_tools' ),
        ];
        wp_localize_script( 'list-js', 'wpApiListSettings', array(
            'root' => esc_url_raw( rest_url() ),
            'nonce' => wp_create_nonce( 'wp_rest' ),
            'txt_infoEmpty' => __( 'Showing 0 to 0 of 0 entries', 'disciple_tools' ),
            'txt_infoFiltered' => _x( '(filtered from _MAX_ total entries)', 'just copy `_MAX_`', 'disciple_tools' ),
            'custom_fields_settings' => $custom_field_settings,
            'template_directory_uri' => get_template_directory_uri(),
            'current_user_login' => wp_get_current_user()->user_login,
            'current_user_roles' => wp_get_current_user()->roles,
            'current_user_contact_id' => Disciple_Tools_Users::get_contact_for_user( get_current_user_id() ),
            'current_post_type' => $post_type,
            'access_all_contacts' => user_can( get_current_user_id(), 'view_any_contacts' ),
            'filters' => Disciple_Tools_Users::get_user_filters(),
            'additional_filter_options' => apply_filters( 'dt_filters_additional_fields', [], $post_type ),
            'connection_types' => Disciple_Tools_Posts::$connection_types,
            'translations' => apply_filters( 'dt_list_js_translations', $translations ),
            'custom_data' => apply_filters( 'dt_list_js_data', [] ), // nest associated array
        ) );
    } elseif ( in_array( $url_path, $post_types ) ){
        $post_type = $url_path;
        $post_settings = apply_filters( "dt_get_post_type_settings", [], $post_type );
        dt_theme_enqueue_script( 'modular-list-js', 'dt-assets/js/modular-list.js', array( 'jquery', 'lodash', 'shared-functions', 'typeahead-jquery', 'site-js' ), true );
        wp_localize_script( 'modular-list-js', 'listSettings', array(
            'post_type' => $post_type,
            'post_type_settings' => $post_settings,
        ) );
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


}
add_action( 'wp_enqueue_scripts', 'dt_site_scripts', 999 );
