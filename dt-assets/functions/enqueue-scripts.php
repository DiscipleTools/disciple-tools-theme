<?php
declare( strict_types=1 );

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
    if ( $rel_src[0] === '/' ) {
        throw new Error( 'dt_theme_enqueue_script took $rel_src argument which unexpectedly started with /' );
    }
    wp_enqueue_script( $handle, get_template_directory_uri() . "/$rel_src", $deps, filemtime( get_template_directory() . "/$rel_src" ), $in_footer );
}

/**
 * Register scripts, in a way that implements cache-busting
 *
 */
function dt_theme_register_script( string $handle, string $rel_src, array $deps = array(), bool $in_footer = false ) {
    if ( $rel_src[0] === '/' ) {
        throw new Error( 'dt_theme_register_script took $rel_src argument which unexpectedly started with /' );
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
    if ( $rel_src[0] === '/' ) {
        throw new Error( 'dt_theme_enqueue_style took $rel_src argument which unexpectedly started with /' );
    }
    wp_enqueue_style( $handle, get_template_directory_uri() . "/$rel_src", $deps, filemtime( get_template_directory() . "/$rel_src" ), $media );
}


/**
 * Register styles, in a way that implements cache-busting
 */
function dt_theme_register_style( string $handle, string $rel_src, array $deps = array(), string $media = 'all' ) {
    if ( $rel_src[0] === '/' ) {
        throw new Error( 'dt_theme_register_style took $rel_src argument which unexpectedly started with /' );
    }
    return wp_register_style( $handle, get_template_directory_uri() . "/$rel_src", $deps, filemtime( get_template_directory() . "/$rel_src" ), $media );
}


/**
 * Primary site script loader
 */
function dt_site_scripts() {

    dt_theme_enqueue_script( 'modernizr-custom', 'dt-assets/js/modernizr-custom.js', [], true );
    dt_theme_enqueue_script( 'check-browser-version', 'dt-assets/js/check-browser-version.js', [ 'modernizr-custom' ], true );

    wp_enqueue_style( 'foundation-css', 'https://cdnjs.cloudflare.com/ajax/libs/foundicons/3.0.0/foundation-icons.css', [], '3.0.0' );
    wp_enqueue_style( 'material-font-icons', 'https://cdn.jsdelivr.net/npm/@mdi/font@6.6.96/css/materialdesignicons.min.css', [], '6.6.96' );

    wp_enqueue_style( 'jquery-ui-site-css', 'https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.css', array(), '1.12.1', 'all' );
    wp_deregister_script( 'jquery' );
    wp_register_script( 'jquery', 'https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js', false, '3.5.1' );
    wp_enqueue_script( 'jquery' );
    wp_register_script( 'jquery-ui', 'https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js', false, '1.12.1' );
    wp_enqueue_script( 'jquery-ui' );


    dt_theme_enqueue_script( 'site-js', 'dt-assets/build/js/scripts.min.js', array( 'jquery' ), true );

    // Register main stylesheet
    dt_theme_enqueue_style( 'site-css', 'dt-assets/build/css/style.min.css', array() );

    // Comment reply script for threaded comments
    if ( is_singular() && comments_open() && ( get_option( 'thread_comments' ) == 1 ) ) {
        wp_enqueue_script( 'comment-reply' );
    }


    global $pagenow;
    if ( is_multisite() && 'wp-activate.php' === $pagenow ) {
        return;
    }

    wp_register_script( 'datepicker', 'https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js', false );
    wp_enqueue_style( 'datepicker-css', 'https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css', array() );

    $post_type = get_post_type();
    $url_path = dt_get_url_path();
    $post_type = $post_type ?: dt_get_post_type();

    dt_theme_enqueue_script( 'shared-functions', 'dt-assets/js/shared-functions.js', array( 'jquery', 'lodash', 'moment', 'datepicker' ) );
    wp_localize_script(
        'shared-functions', 'wpApiShare', array(
            'root' => esc_url_raw( rest_url() ),
            'nonce' => wp_create_nonce( 'wp_rest' ),
            'site_url' => get_site_url(),
            'template_dir' => get_template_directory_uri(),
            'translations' => [
                'regions_of_focus' => __( 'Regions of Focus', 'disciple_tools' ),
                'all_locations' => __( 'All Locations', 'disciple_tools' ),
                'used_locations' => __( 'Used Locations', 'disciple_tools' ),
                'no_records_found' => _x( 'No results found matching "{{query}}"', 'Empty list results. Keep {{query}} as is in english', 'disciple_tools' ),
                'showing_x_items' => _x( 'Showing %s items. Type to find more.', 'Showing 30 items', 'disciple_tools' ),
                'showing_x_items_matching' => _x( 'Showing %1$s items matching %2$s', 'Showing 30 items matching bob', 'disciple_tools' ),
                'edit' => __( 'Edit', 'disciple_tools' ),
                'copy' => __( 'Copy', 'disciple_tools' ),
                'copied_text' => __( 'Copied: %s', 'disciple_tools' ),
            ],
            'post_type' => $post_type,
            'url_path' => $url_path,
            'post_type_modules' => dt_get_option( 'dt_post_type_modules' ),
            'tiles' => DT_Posts::get_post_tiles( $post_type ),
            'can_manage_dt' => current_user_can( 'manage_dt' ),
            'features' => [
                'storage' => class_exists( 'DT_Storage' ) && DT_Storage::is_enabled(),
            ]
        )
    );

    dt_theme_enqueue_script( 'dt-notifications', 'dt-assets/js/notifications.js', array( 'jquery' ), true );
    wp_localize_script(
        'dt-notifications', 'wpApiNotifications', array(
            'root' => esc_url_raw( rest_url() ),
            'nonce' => wp_create_nonce( 'wp_rest' ),
            'current_user_login' => wp_get_current_user()->user_login,
            'current_user_id' => get_current_user_id(),
            'translations' => [
                'no-unread' => __( "You don't have any unread notifications", 'disciple_tools' ),
                'no-notifications' => __( "You don't have any notifications", 'disciple_tools' )
            ]
        )
    );

    dt_theme_enqueue_script( 'typeahead-jquery', 'dt-core/dependencies/typeahead/dist/jquery.typeahead.min.js', array( 'jquery' ), true );
    dt_theme_enqueue_style( 'typeahead-jquery-css', 'dt-core/dependencies/typeahead/dist/jquery.typeahead.min.css', array() );

    if ( DT_Mapbox_API::get_key() ) {
        DT_Mapbox_API::load_mapbox_header_scripts();
    }

    $post_types = DT_Posts::get_post_types();
    if ( is_singular( $post_types ) ) {
        $post = DT_Posts::get_post( get_post_type(), get_the_ID() );
        if ( !is_wp_error( $post ) ){
            $post_settings = DT_Posts::get_post_settings( $post_type );
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
                    'post' => get_post(),
                    'post_with_fields' => $post,
                    'template_dir' => get_template_directory_uri(),
                    'contact_author_name' => isset( $post['post_author'] ) && (int) $post['post_author'] > 0 ? get_user_by( 'id', intval( $post['post_author'] ) )->display_name : __( 'D.T System', 'disciple_tools' ),
                    'translations' => [
                        'edit' => strtolower( __( 'Edit', 'disciple_tools' ) ),
                        'delete' => strtolower( __( 'Delete', 'disciple_tools' ) ),
                        'translate' => __( 'Translate with Google Translate', 'disciple_tools' ),
                        'hide_translation' => __( 'Hide Translation', 'disciple_tools' ),
                        'reaction_title_1' => _x( '%1$s reacted with %2$s emoji', 'Bob reacted with heart emoji', 'disciple_tools' ),
                        'reaction_title_many' => _x( '%3$s and %1$s reacted with %2$s emoji', 'Bob, Bill and Ben reacted with heart emoji', 'disciple_tools' ),
                    ],
                    'current_user_id' => get_current_user_id(),
                    'additional_sections' => apply_filters( 'dt_comments_additional_sections', [], $post_type ),
                    /**
                     * Reaction aliases must be lowercase with no spaces.
                     * The emoji takes precedence if a path to an image is also given.
                     *
                     * Returned assosciative array must be of the form [ 'reaction_alias' => [ 'name' => 'reaction_translateable_name', 'path' => 'optional_path_to_reaction_image', 'emoji' => 'copy_and_pasted_text_emoji' ], ... ]
                     */
                    'reaction_options' => apply_filters( 'dt_comments_reaction_options', dt_get_site_custom_lists( 'comment_reaction_options' ) ),
                    'comments' => DT_Posts::get_post_comments( $post_type, $post['ID'] ),
                    'activity' => DT_Posts::get_post_activity( $post_type, $post['ID'] ),
                    'google_translate_key' => get_option( 'dt_googletranslate_api_key' ),
                ]
            );
            dt_theme_enqueue_script( 'details', 'dt-assets/js/details.js', array(
                'jquery',
                'lodash',
                'shared-functions',
                'typeahead-jquery',
                'jquery-masonry'
            ) );
            wp_localize_script( 'details', 'detailsSettings', [
                'post_type' => $post_type,
                'post_id' => get_the_ID(),
                'post_settings' => $post_settings,
                'current_user_id' => get_current_user_id(),
                'post_fields' => $post,
                'translations' => [
                    'remove' => __( 'Delete', 'disciple_tools' ),
                    'complete' => __( 'Mark as complete', 'disciple_tools' ),
                    'no_tasks' => __( 'No task created', 'disciple_tools' ),
                    'reminder' => __( 'Reminder', 'disciple_tools' ),
                    'no_note' => __( 'No note set', 'disciple_tools' ),
                    'duplicates_detected' => __( 'Duplicates Detected', 'disciple_tools' ),
                    'merge' => __( 'Merge', 'disciple_tools' ),
                    'dismiss' => __( 'Dismiss', 'disciple_tools' ),
                    'dismissed_duplicates' => __( 'Dismissed Duplicates', 'disciple_tools' ),
                    'duplicates_on' => __( 'Duplicates on: %s', 'disciple_tools' ),
                    'transfer_error' => __( 'Transfer failed. Check site-to-site configuration.', 'disciple_tools' ),
                    'transfer_update_error' => __( 'Transfer update failed. Check site-to-site configuration.', 'disciple_tools' ),
                    'transfer_update_success' => __( 'Transfer update successful.', 'disciple_tools' ),
                    'created_on' => _x( 'Created on %s', 'Created on the 21st of August', 'disciple_tools' ),
                    'Open_with' => __( 'Open With', 'disciple_tools' ),
                    'yes' => __( 'Yes', 'disciple_tools' ),
                    'no' => __( 'No', 'disciple_tools' ),
                ]
            ]);

            if ( DT_Mapbox_API::get_key() ) {
                DT_Mapbox_API::load_mapbox_search_widget();
            }

            dt_theme_enqueue_script( 'dt-record-history', 'dt-assets/js/record-history.js', [
                'jquery',
                'lodash',
                'moment'
            ], true );
            wp_localize_script( 'dt-record-history', 'record_history_settings', [
                'post'          => is_singular( $post_types ) ? DT_Posts::get_post( get_post_type(), get_the_ID() ) : [],
                'post_settings' => is_singular( $post_types ) ? DT_Posts::get_post_settings( get_post_type() ) : [],
                'site_url'      => get_site_url( null, '/' ),
                'translations'  => [
                    'revert_but_tooltip'  => __( 'Revert Record Back To This Point', 'disciple_tools' ),
                    'revert_confirm_text' => __( 'Are you sure you want to revert record state back to %s?', 'disciple_tools' )
                ]
            ] );
        }
    }


    if ( 'settings' === $url_path ) {

        $dependencies = [ 'jquery', 'jquery-ui', 'lodash', 'moment' ];
        $contact_id = Disciple_Tools_Users::get_contact_for_user( get_current_user_id() );
        if ( DT_Mapbox_API::get_key() ) {
            DT_Mapbox_API::load_mapbox_search_widget_users();
            $dependencies[] = 'mapbox-search-widget';
            $dependencies[] = 'mapbox-gl';
        } else {
            DT_Mapping_Module::instance()->drilldown_script();
            $dependencies[] = 'mapping-drill-down';
        }

        dt_theme_enqueue_script( 'dt-settings', 'dt-assets/js/settings.js', $dependencies, true );
        wp_localize_script(
            'dt-settings', 'wpApiSettingsPage', array(
                'root'                  => esc_url_raw( rest_url() ),
                'nonce'                 => wp_create_nonce( 'wp_rest' ),
                'current_user_login'    => wp_get_current_user()->user_login,
                'current_user_id'       => get_current_user_id(),
                'template_dir'          => get_template_directory_uri(),
                'associated_contact_id' => $contact_id,
                'translations'          => apply_filters( 'dt_settings_js_translations', [
                    'delete' => __( 'Delete', 'disciple_tools' ),
                    'responsible_for_locations' => __( 'Locations you are responsible for', 'disciple_tools' ),
                    'add' => __( 'Add', 'disciple_tools' ),
                    'save' => __( 'Save', 'disciple_tools' ),
                    'link' => __( 'link', 'disciple_tools' ),
                    'copy' => __( 'copy', 'disciple_tools' ),
                ] ),
                'google_translate_api_key' => get_option( 'dt_googletranslate_api_key' ),
                'custom_data'           => apply_filters( 'dt_settings_js_data', [] ), // nest associated array
                'workload_status'       => get_user_option( 'workload_status', get_current_user_id() ),
                'workload_status_options' => Disciple_Tools_Users::get_users_fields()['workload_status']['options'] ?? [],
                'user_people_groups' => DT_Posts::get_post_names_from_ids( get_user_option( 'user_people_groups', get_current_user_id() ) ?: [] ),
            )
        );
    }

    if ( 'view-duplicates' === $url_path ){
        dt_theme_enqueue_script( 'dt-settings', 'dt-assets/js/view-duplicates.js', [ 'jquery' ], true );
        wp_localize_script(
            'dt-settings', 'view_duplicates_settings', array(
                'translations'          => apply_filters( 'dt_settings_js_translations', [
                    'matches_found' => _x( 'Matches Found:', 'Matches for duplicate contacts found: 230', 'disciple_tools' ),
                    'dismiss_all' => _x( 'Dismiss all matches for %s', 'Dismiss all duplicate matches for Bob', 'disciple_tools' ),
                ] ),
                'fields' => DT_Posts::get_post_field_settings( 'contacts' ),
            )
        );
    }

    $is_new_post      = ( strpos( $url_path, '/new' ) !== false ) && in_array( str_replace( '/new', '', $url_path ), $post_types );
    $is_new_bulk_post = ( strpos( $url_path, '/new-bulk' ) !== false ) && in_array( str_replace( '/new-bulk', '', $url_path ), $post_types );

    $path_without_params = untrailingslashit( dt_get_url_path( true ) );
    //list page
    if ( !get_post_type() && in_array( $path_without_params, $post_types ) ){

        $post_settings = DT_Posts::get_post_settings( $post_type );
        $translations = [
            'save' => __( 'Save', 'disciple_tools' ),
            'edit' => __( 'Edit', 'disciple_tools' ),
            'delete' => __( 'Delete', 'disciple_tools' ),
            'txt_info' => _x( 'Showing _START_ of _TOTAL_', 'just copy as they are: _START_ and _TOTAL_', 'disciple_tools' ),
            'sorting_by' => __( 'Sorting By', 'disciple_tools' ),
            'creation_date' => __( 'Creation Date', 'disciple_tools' ),
            'date_modified' => __( 'Date Modified', 'disciple_tools' ),
            'empty_custom_filters' => __( 'No filters, create one below', 'disciple_tools' ),
            'empty_list' => __( 'No records found matching your filter.', 'disciple_tools' ),
            'filter_all' => sprintf( _x( 'All %s', 'All records', 'disciple_tools' ), $post_settings['label_plural'] ),
            'range_start' => __( 'start', 'disciple_tools' ),
            'range_end' => __( 'end', 'disciple_tools' ),
            'all' => __( 'All', 'disciple_tools' ),
            'without' => __( 'Without', 'disciple_tools' ),
            'make_selections_below' => __( 'Make Selections Below', 'disciple_tools' ),
            'delete_selections_below' => __( 'Delete Selections Below', 'disciple_tools' ),
            'sent' => _x( 'scheduled to be sent', 'Number of emails sent. i.e. 20 sent!', 'disciple_tools' ),
            'not_sent' => _x( 'not sent (likely missing valid email)', 'Preceded with number of emails not sent. i.e. 20 not sent!', 'disciple_tools' ),
            'see_queue' => _x( 'See queue', 'See queue of messages to be sent.', 'disciple_tools' ),
            'exclude_item' => __( 'Exclude Item', 'disciple_tools' ),
            'exports' => [
                'csv' => [
                    'fields_msg_all' => sprintf( _x( 'The exported csv data to contain all %2$s %1$s fields.', 'The exported csv data to contain all 64 Contact fields.', 'disciple_tools' ), $post_settings['label_singular'], '%2$s' ),
                    'fields_msg_visible' => sprintf( _x( 'The exported csv data to contain the %2$s visible %1$s field(s) shown below.', 'The exported csv data to contain the 6 visible Contact field(s) shown below.', 'disciple_tools' ), $post_settings['label_singular'], '%2$s' )
                ],
                'bcc' => [
                    'full_list' => __( 'Full List', 'disciple_tools' ),
                    'no_addr' => __( 'No Addresses', 'disciple_tools' ),
                    'with_addr' => __( 'With Additional Addresses', 'disciple_tools' ),
                    'open_email' => __( 'Open Email for Group', 'disciple_tools' ),
                    'open_all' => __( 'Open All', 'disciple_tools' ),
                    'show_group_addrs' => __( 'Show Group Addresses', 'disciple_tools' )
                ],
                'phone' => [
                    'full_list' => __( 'Full List', 'disciple_tools' ),
                    'no_phone' => __( 'Has No Phone Number', 'disciple_tools' ),
                    'with_phone' => __( 'Has Additional Phone Numbers', 'disciple_tools' )
                ],
                'map' => [
                    'mapbox_key' => ( class_exists( 'DT_Mapbox_API' ) && DT_Mapbox_API::get_key() ) ? esc_attr( DT_Mapbox_API::get_key() ) : null,
                    'mapped_locations' => __( 'Mapped Locations', 'disciple_tools' ),
                    'without_locations' => __( 'Contacts Without Locations', 'disciple_tools' ),
                    'records_on_zoomed_map' => __( 'Records on zoomed map', 'disciple_tools' ),
                    'no_records_on_zoomed_map_alert' => __( 'No Zoomed Map Records Found!', 'disciple_tools' ),
                    'open_zoomed_map' => __( 'Open Zoomed Map Records', 'disciple_tools' ),
                    'filter_name' => __( 'Filtered Map', 'disciple_tools' ),
                    'filter_label' => __( 'Filtered Map', 'disciple_tools' )
                ]
            ]
        ];
        $permissions = [
            'delete_any' => current_user_can( 'delete_any_' . $post_type )
        ];
        dt_theme_enqueue_script( 'drag-n-drop-table-columns', 'dt-core/dependencies/drag-n-drop-table-columns.js', array( 'jquery' ), true );
        dt_theme_enqueue_script( 'modular-list-js', 'dt-assets/js/modular-list.js', array( 'jquery', 'lodash', 'shared-functions', 'typeahead-jquery', 'site-js', 'drag-n-drop-table-columns' ), true );
        wp_localize_script( 'modular-list-js', 'list_settings', array(
            'post_type' => $post_type,
            'post_type_settings' => $post_settings,
            'permissions' => $permissions,
            'fields_to_show_in_table' => DT_Posts::get_default_list_column_order( $post_type ),
            'translations' => apply_filters( 'dt_list_js_translations', $translations ),
            'filters' => Disciple_Tools_Users::get_user_filters( $post_type ),
            'default_icon' => apply_filters( 'dt_record_icon', null, $post_type, null ),
        ) );
        if ( DT_Mapbox_API::get_key() ){
            DT_Mapbox_API::load_mapbox_search_widget();
            $dependencies[] = 'mapbox-search-widget';
            $dependencies[] = 'mapbox-gl';
        }
    }

    if ( $is_new_post || $is_new_bulk_post ){
        $post_settings = DT_Posts::get_post_settings( $post_type );
        $translations = [
            'contact' => __( 'Contact', 'disciple_tools' ),
            'value_already_exists' => _x( '%s already exists', 'Communication channel already exists', 'disciple_tools' ),
        ];
        $dependencies = [ 'jquery', 'lodash', 'shared-functions', 'typeahead-jquery' ];
        if ( DT_Mapbox_API::get_key() ){
            DT_Mapbox_API::load_mapbox_search_widget();
            $dependencies[] = 'mapbox-search-widget';
            $dependencies[] = 'mapbox-gl';
        }
        dt_theme_enqueue_script( 'new-record', 'dt-assets/js/new-record.js', $dependencies, true );
        wp_localize_script( 'new-record', 'new_record_localized', array(
            'post_type'          => $post_type,
            'post_type_settings' => $post_settings,
            'translations'       => $translations,
        ) );
    }

    dt_theme_enqueue_script( 'dt-advanced-search', 'dt-assets/js/advanced-search.js', array( 'jquery' ), true );
    wp_localize_script( 'dt-advanced-search', 'advanced_search_settings', array(
        'template_dir_uri' => esc_html( get_template_directory_uri() ),
        'fetch_more_text' => __( 'Load More', 'disciple_tools' ) // Support translations
    ) );

    if ( class_exists( 'DT_Storage' ) && DT_Storage::is_enabled() ) {
        dt_theme_enqueue_script( 'dt-storage', 'dt-assets/js/dt-storage.js', [ 'jquery' ], true );
        wp_localize_script( 'dt-storage', 'storage_settings',
            [
                'rest_url' => esc_url_raw( rest_url() ),
                'accepted_file_types' => [
                    'image/png',
                    'image/gif',
                    'image/jpeg',
                    'image/jpg',
                    'audio/*',
                    'video/*'
                ],
                'translations' => [
                    'modals' => [
                        'upload' => [
                            'title' => __( 'File Upload', 'disciple_tools' ),
                            'choose_file' => __( 'Choose a file', 'disciple_tools' ),
                            'or_drag_it' => __( 'or drag it here', 'disciple_tools' ),
                            'success' => __( 'Successfully Uploaded!', 'disciple_tools' ),
                            'error' => __( 'Error!', 'disciple_tools' ),
                            'error_msg' => __( 'Unable to upload, please try again', 'disciple_tools' ),
                            'but_upload' => __( 'Upload', 'disciple_tools' ),
                            'but_delete' => __( 'Delete Existing File', 'disciple_tools' ),
                            'delete_msg' => __( 'Are you sure you wish to delete existing file?', 'disciple_tools' ),
                            'delete_success_msg' => __( 'Successfully Deleted!', 'disciple_tools' ),
                            'delete_error_msg' => __( 'Delete failed, please try again', 'disciple_tools' ),
                            'but_close' => __( 'Close', 'disciple_tools' )
                        ]
                    ]
                ]
            ]
        );
    }
}
add_action( 'wp_enqueue_scripts', 'dt_site_scripts', 999 );

/**
 * Template script loader
 */
function dt_template_scripts( $slug, $name, $templates, $args ) {

    $post_type = get_post_type();
    $post_type = $post_type ?: dt_get_post_type();

    // 403
    if ( isset( $slug ) && ( $slug === '403' ) ) {
        dt_theme_enqueue_script( 'dt-request-record-access', 'dt-assets/js/request-record-access.js', array( 'jquery' ) );
        wp_localize_script( 'dt-request-record-access', 'detailsSettings', [
            'post_type'       => $post_type,
            'post_id'         => get_the_ID(),
            'current_user_id' => get_current_user_id()
        ] );
    }
}
add_action( 'get_template_part', 'dt_template_scripts', 999, 4 );
