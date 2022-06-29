<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

/**
 * Setting and lists to be used in D.T
 *
 * @author  Disciple.Tools
 * @package Disciple.Tools
 */

/*********************************************************************************************
 * Action and Filters
 */
add_filter( 'language_attributes', 'dt_custom_dir_attr' );

/*********************************************************************************************
 * Functions
 */



/**
 * Admin panel svg icon for Disciple.Tools.
 *
 * @return string
 */
function dt_svg_icon() {
    return 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyMS40IDIwLjMyIj48ZGVmcz48c3R5bGU+LmF7ZmlsbDojMmQyZDJkO308L3N0eWxlPjwvZGVmcz48dGl0bGU+ZGlzY2lwbGUtdG9vbHM8L3RpdGxlPjxwb2x5Z29uIGNsYXNzPSJhIiBwb2ludHM9IjIxLjQgMjAuMzIgOS4zIDAgMi44NiAxMC44MSA4LjUyIDIwLjMyIDIxLjQgMjAuMzIiLz48cG9seWdvbiBjbGFzcz0iYSIgcG9pbnRzPSIwLjAyIDE1LjU4IDAgMTUuNjEgMi44MyAyMC4zMiA1LjUxIDE1LjM0IDAuMDIgMTUuNTgiLz48L3N2Zz4=';
}

/**
 * Capture pre-existing path options; created outside of update flow
 *
 * @param $site_options
 *
 * @return array
 */
function dt_seeker_path_triggers_capture_pre_existing_options( $site_options ): array {
    if ( ! empty( $site_options ) && isset( $site_options['update_required'] ) ) {
        $options      = DT_Posts::get_post_field_settings( 'contacts', false, true )['seeker_path']['default'];
        $deltas       = dt_seeker_path_trigger_deltas( $site_options['update_required']['options'], $options );
        $site_options = dt_seeker_path_triggers_update_by_deltas( $site_options, $deltas );
    }

    return $site_options;
}

/**
 * Add new options to existing seeker path triggers list
 *
 * @param $options
 *
 * @return void
 */
function dt_seeker_path_triggers_update( $options ): void {
    $site_options = dt_get_option( 'dt_site_options' );
    if ( ! empty( $options ) && isset( $site_options['update_required'] ) ) {

        // Fetch any/all available deltas
        $deltas = dt_seeker_path_trigger_deltas( $site_options['update_required']['options'], $options );

        // Assign identified deltas and update option
        dt_seeker_path_triggers_update_by_deltas( $site_options, $deltas );
    }
}

function dt_seeker_path_trigger_deltas( $update_required_options, $options ): array {
    $deltas = [];

    foreach ( $options ?? [] as $opt_key => $opt_val ) {
        $found = false;
        foreach ( $update_required_options ?? [] as $required ) {

            // Is there already a trigger specified?
            if ( $required['seeker_path'] === $opt_key ) {
                $found = true;
            }
        }

        // If not, then assign as new delta
        if ( ! $found ) {
            $deltas[] = [
                'status'      => 'active',
                'seeker_path' => $opt_key,
                'days'        => 30,
                'comment'     => __( "We haven't heard about this person in a while. Do you have an update for this contact?", 'disciple_tool' )
            ];
        }
    }

    return $deltas;
}

function dt_seeker_path_triggers_update_by_deltas( $site_options, $deltas ): array {
    if ( ! empty( $deltas ) ) {
        foreach ( $deltas as $delta ) {
            $site_options['update_required']['options'][] = $delta;
        }
        update_option( 'dt_site_options', $site_options, true );

        // Reload....
        $site_options = dt_get_option( 'dt_site_options' );
    }

    return $site_options;
}

/**
 * Using the dt_get_option guarantees the existence of the option and upgrades to the current plugin version defaults,
 * while returning the options array.
 *
 * @param string $name
 *
 * @return array|false
 */
function dt_get_option( string $name ) {

    switch ( $name ) {
        case 'dt_site_options':
            $site_options = dt_get_site_options_defaults();

            if ( !get_option( 'dt_site_options' ) ) { // options doesn't exist, create new.
                $add = add_option( 'dt_site_options', $site_options, '', true );
                if ( !$add ) {
                    return false;
                }
            }
            elseif ( get_option( 'dt_site_options' )['version'] < $site_options['version'] ) { // option exists but version is behind
                $upgrade = dt_site_options_upgrade_version( 'dt_site_options' );
                if ( !$upgrade ) {
                    return false;
                }
            }
            return get_option( 'dt_site_options' );

            break;

        case 'dt_site_custom_lists':
            $default_custom_lists = dt_get_site_custom_lists();

            if ( !get_option( 'dt_site_custom_lists' ) ) { // options doesn't exist, create new.
                add_option( 'dt_site_custom_lists', $default_custom_lists, '', true );
            }
            else {
                if ( (int) get_option( 'dt_site_custom_lists' )['version'] < $default_custom_lists['version'] ) { // option exists but version is behind
                    $upgrade = dt_site_options_upgrade_version( 'dt_site_custom_lists' );
//                    updating the option is not always working right away, return the non updated option instead of failing.
                    if ( !$upgrade ) {
                        return $default_custom_lists;
                    }
                }
            }
            //return apply_filters( "dt_site_custom_lists", get_option( 'dt_site_custom_lists' ) );
            return get_option( 'dt_site_custom_lists' );
            break;

        case 'dt_field_customizations':
            return get_option( 'dt_field_customizations', [
                "contacts" => [],
                "groups" => []
            ]);
        case 'dt_custom_tiles':

            $custom_tiles = get_option( 'dt_custom_tiles', [
                "contacts" => [],
                "groups" => []
            ]);

             $custom_tiles_with_translations = apply_filters( 'options_dt_custom_tiles', $custom_tiles );

             return $custom_tiles_with_translations;

        case 'base_user':
            if ( ! get_option( 'dt_base_user' ) ) { // options doesn't exist, create new.
                // set base users to system admin
                $users = get_users( [ 'role' => 'dispatcher' ] );
                if ( empty( $users ) ) {
                    $users = get_users( [ 'role' => 'administrator' ] );
                }
                if ( empty( $users ) ) {
                    return false;
                }

                $user_id = $users[0]->ID;

                // set as base user
                $add = update_option( 'dt_base_user', $user_id, false );
                if ( ! $add ) {
                    return false;
                }

                return get_option( 'dt_base_user' );
            }
            else {
                return get_option( 'dt_base_user' );
            }
            break;


        case 'location_levels':
            $default_levels = dt_get_location_levels();
            $levels = get_option( 'dt_location_levels' );
            if ( ! $levels || empty( $levels ) ) { // options doesn't exist, create new.
                $update = update_option( 'dt_location_levels', $default_levels, true );
                if ( ! $update ) {
                    return false;
                }
                $levels = get_option( 'dt_location_levels' );
            }
            elseif ( $levels['version'] < $default_levels['version'] ) { // option exists but version is behind

                unset( $levels['version'] );
                $location_levels = wp_parse_args( $levels, $default_levels );
                $update = update_option( 'dt_location_levels', $location_levels, true );
                if ( ! $update ) {
                    return false;
                }
                $levels = get_option( 'dt_location_levels' );
            }
            return $levels['location_levels'];
            break;
        case 'auto_location':
            $setting = get_option( 'dt_auto_location' );
            if ( false === $setting ) {
                update_option( 'dt_auto_location', '1', false );
                $setting = get_option( 'dt_auto_location' );
            }
            return $setting;
            break;

        case 'dt_email_base_subject':
            $subject_base = get_option( "dt_email_base_subject", "Disciple.Tools" );
            if ( empty( $subject_base ) ){
                update_option( "dt_email_base_subject", "Disciple.Tools" );
            }
            return $subject_base;
            break;

        case 'dt_email_base_address':
            $address_base = get_option( "dt_email_base_address", "" );
            if ( empty( $address_base ) ){
                update_option( "dt_email_base_address", "" );
            }
            return $address_base;
            break;

        case 'dt_email_base_name':
            $name_base = get_option( "dt_email_base_name", "" );
            if ( empty( $name_base ) ){
                update_option( "dt_email_base_name", "" );
            }
            return $name_base;
            break;

        case 'group_type':
            $site_options = dt_get_option( "dt_site_custom_lists" );
            return $site_options["group_type"];

        case 'group_preferences':
            $site_options = dt_get_option( "dt_site_options" );
            return $site_options["group_preferences"];

        case 'dt_working_languages':
            $languages = get_option( 'dt_working_languages', [] );
            if ( empty( $languages ) ){
                $languages = [
                    "en" => [ "label" => "English" ],
                    "fr" => [ "label" => "French" ],
                    "es" => [ "label" => "Spanish" ]
                ];
            }
            $languages = DT_Posts_Hooks::dt_get_field_options_translation( $languages );
            return apply_filters( 'dt_working_languages', $languages );

        case 'dt_post_type_modules':
            $modules = apply_filters( 'dt_post_type_modules', [] );
            $module_options = get_option( 'dt_post_type_modules', [] );
            // remove modules not present
            foreach ( $module_options as $key => $module ){
                if ( ! isset( $modules[$key] ) ) {
                    unset( $module_options[$key] );
                }
            }
            // merge distinct
            $modules = dt_array_merge_recursive_distinct( $modules, $module_options );
            return apply_filters( 'dt_post_type_modules_after', $modules );

        default:
            return false;
            break;
    }
}

/**
 * Supports the complex array structure of versioned arrays
 *
 * @param      $name
 * @param      $value
 * @param bool $autoload
 *
 * @return bool
 */
function dt_update_option( $name, $value, $autoload = false ) {

    if ( empty( $name ) ) {
        return false;
    }

    switch ( $name ) {
        case 'location_levels':
            if ( ! is_array( $value ) ) {
                return false;
            }
            $levels = maybe_unserialize( get_option( 'dt_location_levels' ) );
            $levels['location_levels'] = $value;

            $default_levels = dt_get_location_levels();
            $levels = wp_parse_args( $levels, $default_levels );

            return update_option( 'dt_location_levels', $levels, $autoload );

            break;
        case 'auto_location':
            return update_option( 'dt_auto_location', $value, $autoload );
            break;

        default:
            return false;
            break;
    }
}

/**
 * Returns the default master array of site options
 * Versioning allows for additive changes. Removal of fields here in defaults will not delete the value in current installations.
 *
 * @return array
 */
function dt_get_site_options_defaults() {
    $fields = [];

    $fields['version'] = '9';

    $fields['notifications'] = [
        'channels' => [
            'email' => [
                "label" => __( 'Email', 'disciple_tools' )
            ],
            'web' => [
                "label" => __( 'Web', 'disciple_tools' )
            ]
        ],
        'types' => [
            'new_assigned' => [
                'label' => __( 'Newly Assigned Contact', 'disciple_tools' ),
                'web'   => true,
                'email' => true
            ],
            'mentions' => [
                'label' => __( '@Mentions', 'disciple_tools' ),
                'web'   => true,
                'email' => true
            ],
            'comments' => [
                'label' => __( 'New Comments', 'disciple_tools' ),
                'web'   => false,
                'email' => false
            ],
            'updates' => [
                'label' => __( 'Update Needed', 'disciple_tools' ),
                'web'   => true,
                'email' => true
            ],
            'changes' => [
                'label' => __( 'Contact Info Changed', 'disciple_tools' ),
                'web'   => false,
                'email' => false
            ],
            'milestones' => [
                'label' => __( 'Contact Milestones and Group Health metrics', 'disciple_tools' ),
                'web'   => false,
                'email' => false
            ]
        ]
    ];

    $fields['daily_reports'] = [
        'build_report_for_contacts'  => true,
        'build_report_for_groups'    => true,
        'build_report_for_facebook'  => false,
        'build_report_for_twitter'   => false,
        'build_report_for_analytics' => false,
        'build_report_for_adwords'   => false,
        'build_report_for_mailchimp' => false,
        'build_report_for_youtube'   => false,
    ];

    $fields['update_required'] = [
        "enabled" => true,
        "options" => [
            [
                "status"      => "active",
                "seeker_path" => "none",
                "days"        => 3,
                "comment"     => __( "This contact is active but there is no record of anybody contacting them. Please do contact them.", 'disciple_tools' )
            ],
            [
                "status"      => "active",
                "seeker_path" => "attempted",
                "days"        => 7,
                "comment"     => __( "Please try connecting with this contact again.", 'disciple_tools' )
            ],
            [
                "status"      => "active",
                "seeker_path" => "established",
                "days"        => 30,
                "comment"     => __( "We haven't heard about this person in a while. Do you have an update for this contact?", 'disciple_tools' )
            ],
            [
                "status"      => "active",
                "seeker_path" => "scheduled",
                "days"        => 30,
                "comment"     => __( "We haven't heard about this person in a while. Do you have an update for this contact?", 'disciple_tools' )
            ],
            [
                "status"      => "active",
                "seeker_path" => "met",
                "days"        => 30,
                "comment"     => __( "We haven't heard about this person in a while. Do you have an update for this contact?", 'disciple_tools' )
            ],
            [
                "status"      => "active",
                "seeker_path" => "ongoing",
                "days"        => 30,
                "comment"     => __( "We haven't heard about this person in a while. Do you have an update for this contact?", 'disciple_tools' )
            ],
            [
                "status"      => "active",
                "seeker_path" => "coaching",
                "days"        => 30,
                "comment"     => __( "We haven't heard about this person in a while. Do you have an update for this contact?", 'disciple_tools' )
            ]
        ]
    ];
    $fields["group_update_required"] = [
        "enabled" => true,
        "options" => [
            [
                "status"      => "active",
                "days"        => 30,
                "comment"     => __( "We haven't heard about this group in a while. Do you have an update?", 'disciple_tools' )
            ]
        ]
    ];
    $fields["group_preferences"] = [
        "church_metrics" => true,
        "four_fields" => false,
    ];

    return $fields;
}

/**
 * Gets site configured custom lists
 * Versioning allows for additive changes. Removal of fields here in defaults will not delete the value in current installations.
 *
 * @param string|null $list_title
 *
 * @version 1 - initialized
 *          9 - added "transfer" to source list
 *
 * @return array|mixed
 */
function dt_get_site_custom_lists( string $list_title = null ) {
    $fields = [];

    $fields['version'] = 10;

    // the prefix dt_user_ assists db meta queries on the user
    $fields['user_fields'] = [
        'dt_user_personal_phone'   => [
            'label'       => __( 'Personal Phone', 'disciple_tools' ),
            'key'         => 'dt_user_personal_phone',
            'type'        => 'phone',
            'description' => __( 'Personal phone is private to the team, not for distribution.', 'disciple_tools' ),
            'enabled'     => true,
        ],
        'dt_user_personal_email'   => [
            'label'       => __( 'Personal Email', 'disciple_tools' ),
            'key'         => 'dt_user_personal_email',
            'type'        => 'email',
            'description' => __( 'Personal email is private to the team, not for distribution.', 'disciple_tools' ),
            'enabled'     => true,
        ],
        'dt_user_personal_address' => [
            'label'       => __( 'Personal Address', 'disciple_tools' ),
            'key'         => 'dt_user_personal_address',
            'type'        => 'address',
            'description' => __( 'Personal address is private to the team, not for distribution.', 'disciple_tools' ),
            'enabled'     => true,
        ],
        'dt_user_work_phone'       => [
            'label'       => __( 'Work Phone', 'disciple_tools' ),
            'key'         => 'dt_user_work_phone',
            'type'        => 'phone',
            'description' => __( 'Work phone is for distribution to contacts and seekers.', 'disciple_tools' ),
            'enabled'     => true,
        ],
        'dt_user_work_email'       => [
            'label'       => __( 'Work Email', 'disciple_tools' ),
            'key'         => 'dt_user_work_email',
            'type'        => 'email',
            'description' => __( 'Work email is for distribution to contacts and seekers.', 'disciple_tools' ),
            'enabled'     => true,
        ],
        'dt_user_work_facebook'    => [
            'label'       => __( 'Work Facebook', 'disciple_tools' ),
            'key'         => 'dt_user_work_facebook',
            'type'        => 'social',
            'description' => __( 'Work Facebook is for distribution to contacts and seekers.', 'disciple_tools' ),
            'enabled'     => false,
        ],
        'dt_user_work_whatsapp'    => [
            'label'       => __( 'Work WhatsApp', 'disciple_tools' ),
            'key'         => 'dt_user_work_whatsapp',
            'type'        => 'other',
            'description' => __( 'Work WhatsApp is for distribution to contacts and seekers.', 'disciple_tools' ),
            'enabled'     => false,
        ],
    ];

    // alias's must be lower case with no spaces
    $fields['comment_reaction_options'] = [
            "thumbs_up" => [ 'name' => __( "thumbs up", "disciple_tools" ), 'path' => 'https://github.githubassets.com/images/icons/emoji/unicode/1f44d.png', 'emoji' => '👍' ],
            "heart" => [ 'name' => __( "heart", "disciple_tools" ), 'path' => 'https://github.githubassets.com/images/icons/emoji/unicode/2764.png', 'emoji' => '❤️'],
            "laugh" => [ 'name' => __( "laugh", "disciple_tools" ), 'path' => 'https://github.githubassets.com/images/icons/emoji/unicode/1f604.png', 'emoji' => '😄' ],
            "wow" => [ 'name' => __( "wow", "disciple_tools" ), 'path' => 'https://github.githubassets.com/images/icons/emoji/unicode/1f62e.png', 'emoji' => '😮' ],
            "sad" => [ 'name' => __( "sad", "disciple_tools" ), 'path' => 'https://github.githubassets.com/images/icons/emoji/unicode/1f615.png', 'emoji' => '😟' ],
            "prayer" => [ 'name' => __( "prayer", "disciple_tools" ), 'path' => 'https://github.githubassets.com/images/icons/emoji/unicode/1f64f.png', 'emoji' => '🙏' ],
            //"praise" => [ 'name' => __( "praise", "disciple_tools" ), 'path' => 'https://github.githubassets.com/images/icons/emoji/unicode/1f64c.png', 'emoji' => '🙌' ],
            //"angry" => [ 'name' => __( "angry", "disciple_tools" ), 'path' => 'https://github.githubassets.com/images/icons/emoji/unicode/1f620.png', 'emoji' => '😠' ],
        ];

    $fields['sources'] = [];

    $fields["contact_address_types"] = [
        "home"  => [ "label" => __( 'Home', 'disciple_tools' ) ],
        "work"  => [ "label" => __( 'Work', 'disciple_tools' ) ],
        "other" => [ "label" => __( 'Other', 'disciple_tools' ) ],
    ];
    $fields["group_preferences"] = [
        "church_metrics" => true,
        "four_fields" => false,
    ];

    $fields["user_workload_status"] = [
        "active" => [
            "label" => __( "Accepting new contacts", 'disciple_tools' ),
            "color" => "#4caf50"
        ],
        "existing" => [
            "label" => __( "I'm only investing in existing contacts", 'disciple_tools' ),
            "color" => "#ff9800"
        ],
        "too_many" => [
            "label" => __( "I have too many contacts", 'disciple_tools' ),
            "color" => "#F43636"
        ]
    ];


    // $fields = apply_filters( 'dt_site_custom_lists', $fields );

    return $fields[ $list_title ] ?? $fields;
}

function dt_get_location_levels() {
    $fields = [];

    $fields['version'] = 3;

    $fields['location_levels'] = [
        'country' => 1,
        'administrative_area_level_1' => 1,
        'administrative_area_level_2' => 1,
        'administrative_area_level_3' => 0,
        'administrative_area_level_4' => 0,
        'locality' => 0,
        'neighborhood' => 0,
    ];

    $fields['location_levels_labels'] = [
        'country' => 'Country',
        'administrative_area_level_1' => 'Admin Level 1 (ex. state / province) ',
        'administrative_area_level_2' => 'Admin Level 2 (ex. county)',
        'administrative_area_level_3' => 'Admin Level 3',
        'administrative_area_level_4' => 'Admin Level 4',
        'locality' => 'Locality (ex. city name)',
        'neighborhood' => 'Neighborhood',
    ];

    return $fields;
}

/**
 * Processes the current configurations and upgrades the site options to the new version with persistent configuration settings.
 *
 * @return bool
 */
function dt_site_options_upgrade_version( string $name ) {
    $site_options_current = get_option( $name );
    if ( $name === "dt_site_custom_lists" ){
        $site_options_defaults = dt_get_site_custom_lists();
    } else if ( $name === "dt_site_options" ){
        $site_options_defaults = dt_get_site_options_defaults();
    } else {
        return false;
    }

    $new_version_number = $site_options_defaults['version'];

    if ( !is_array( $site_options_current ) ) {
        return false;
    }

    $new_options = array_replace_recursive( $site_options_defaults, $site_options_current );
    $new_options['version'] = $new_version_number;

    return update_option( $name, $new_options, "no" );
}

function dt_get_global_languages_list() {
    /* You can find flags with country codes here https://unpkg.com/country-flag-emoji@1.0.3/dist/country-flag-emoji.umd.js */
    /* Then you should be able to search for the country code e.g. af_NA NA -> Namibia to get the necessary flags */

    $global_languages_list = [
        "af_NA" => [ "label" => "Afrikaans (Namibia)", "native_name" => "Afrikaans (Namibia)", "flag" => "", "rtl" => false ],
        "af_ZA" => [ "label" => "Afrikaans (South Africa)", "native_name" => "Afrikaans (South Africa)", "flag" => "", "rtl" => false ],
        "af" => [ "label" => "Afrikaans", "native_name" => "Afrikaans", "flag" => "", "rtl" => false ],
        "ak_GH" => [ "label" => "Akan (Ghana)", "native_name" => "Akan (Ghana)", "flag" => "", "rtl" => false ],
        "ak" => [ "label" => "Akan", "native_name" => "Akan", "flag" => "", "rtl" => false ],
        "sq_AL" => [ "label" => "Albanian (Albania)", "native_name" => "Albanian (Albania)", "flag" => "", "rtl" => false ],
        "sq" => [ "label" => "Albanian", "native_name" => "Albanian", "flag" => "", "rtl" => false ],
        "am_ET" => [ "label" => "Amharic (Ethiopia)", "native_name" => "Amharic (Ethiopia)", "flag" => "", "rtl" => false ],
        "am" => [ "label" => "Amharic", "native_name" => "Amharic", "default_locale" => "am_ET", "flag" => "", "rtl" => false ],
        "ar_DZ" => [ "label" => "Arabic (Algeria)", "native_name" => "Arabic (Algeria)", "flag" => "", "rtl" => false ],
        "ar_BH" => [ "label" => "Arabic (Bahrain)", "native_name" => "Arabic (Bahrain)", "flag" => "", "rtl" => false ],
        "ar_EG" => [ "label" => "Arabic (Egypt)", "native_name" => "Arabic (Egypt)", "flag" => "", "rtl" => false ],
        "ar_IQ" => [ "label" => "Arabic (Iraq)", "native_name" => "Arabic (Iraq)", "flag" => "", "rtl" => false ],
        "ar_JO" => [ "label" => "Arabic (Jordan)", "native_name" => "Arabic (Jordan)", "flag" => "", "rtl" => false ],
        "ar_KW" => [ "label" => "Arabic (Kuwait)", "native_name" => "Arabic (Kuwait)", "flag" => "", "rtl" => false ],
        "ar_LB" => [ "label" => "Arabic (Lebanon)", "native_name" => "Arabic (Lebanon)", "flag" => "", "rtl" => false ],
        "ar_LY" => [ "label" => "Arabic (Libya)", "native_name" => "Arabic (Libya)", "flag" => "", "rtl" => false ],
        "ar_MA" => [ "label" => "Arabic (Morocco)", "native_name" => "Arabic (Morocco)", "flag" => "", "rtl" => false ],
        "ar_OM" => [ "label" => "Arabic (Oman)", "native_name" => "Arabic (Oman)", "flag" => "", "rtl" => false ],
        "ar_QA" => [ "label" => "Arabic (Qatar)", "native_name" => "Arabic (Qatar)", "flag" => "", "rtl" => false ],
        "ar_SA" => [ "label" => "Arabic (Saudi Arabia)", "native_name" => "Arabic (Saudi Arabia)", "flag" => "", "rtl" => false ],
        "ar_SD" => [ "label" => "Arabic (Sudan)", "native_name" => "Arabic (Sudan)", "flag" => "", "rtl" => false ],
        "ar_SY" => [ "label" => "Arabic (Syria)", "native_name" => "Arabic (Syria)", "flag" => "", "rtl" => false ],
        "ar_TN" => [ "label" => "Arabic (Tunisia)", "native_name" => "Arabic (Tunisia)", "flag" => "", "rtl" => false ],
        "ar_AE" => [ "label" => "Arabic (United Arab Emirates)", "native_name" => "Arabic (United Arab Emirates)", "flag" => "", "rtl" => false ],
        "ar_YE" => [ "label" => "Arabic (Yemen)", "native_name" => "Arabic (Yemen)", "flag" => "", "rtl" => false ],
        "ar" => [ "label" => "Arabic", "native_name" => "Arabic", "default_locale" => "ar", "flag" => "", "rtl" => false ],
        "hy_AM" => [ "label" => "Armenian (Armenia)", "native_name" => "Armenian (Armenia)", "flag" => "", "rtl" => false ],
        "hy" => [ "label" => "Armenian", "native_name" => "Armenian", "flag" => "", "rtl" => false ],
        "as_IN" => [ "label" => "Assamese (India)", "native_name" => "Assamese (India)", "flag" => "", "rtl" => false ],
        "as" => [ "label" => "Assamese", "native_name" => "Assamese", "flag" => "", "rtl" => false ],
        "asa_TZ" => [ "label" => "Asu (Tanzania)", "native_name" => "Asu (Tanzania)", "flag" => "", "rtl" => false ],
        "asa" => [ "label" => "Asu", "native_name" => "Asu", "flag" => "", "rtl" => false ],
        "az_Cyrl" => [ "label" => "Azerbaijani (Cyrillic)", "native_name" => "Azerbaijani (Cyrillic)", "flag" => "", "rtl" => false ],
        "az_Cyrl_AZ" => [ "label" => "Azerbaijani (Cyrillic, Azerbaijan)", "native_name" => "Azerbaijani (Cyrillic, Azerbaijan)", "flag" => "", "rtl" => false ],
        "az_Latn" => [ "label" => "Azerbaijani (Latin)", "native_name" => "Azerbaijani (Latin)", "flag" => "", "rtl" => false ],
        "az_Latn_AZ" => [ "label" => "Azerbaijani (Latin, Azerbaijan)", "native_name" => "Azerbaijani (Latin, Azerbaijan)", "flag" => "", "rtl" => false ],
        "az" => [ "label" => "Azerbaijani", "native_name" => "Azerbaijani", "flag" => "", "rtl" => false ],
        "bm_ML" => [ "label" => "Bambara (Mali)", "native_name" => "Bambara (Mali)", "flag" => "", "rtl" => false ],
        "bm" => [ "label" => "Bambara", "native_name" => "Bambara", "flag" => "", "rtl" => false ],
        "eu_ES" => [ "label" => "Basque (Spain)", "native_name" => "Basque (Spain)", "flag" => "", "rtl" => false ],
        "eu" => [ "label" => "Basque", "native_name" => "Basque", "flag" => "", "rtl" => false ],
        "be_BY" => [ "label" => "Belarusian (Belarus)", "native_name" => "Belarusian (Belarus)", "flag" => "", "rtl" => false ],
        "be" => [ "label" => "Belarusian", "native_name" => "Belarusian", "flag" => "", "rtl" => false ],
        "bem_ZM" => [ "label" => "Bemba (Zambia)", "native_name" => "Bemba (Zambia)", "flag" => "", "rtl" => false ],
        "bem" => [ "label" => "Bemba", "native_name" => "Bemba", "flag" => "", "rtl" => false ],
        "bez_TZ" => [ "label" => "Bena (Tanzania)", "native_name" => "Bena (Tanzania)", "flag" => "", "rtl" => false ],
        "bez" => [ "label" => "Bena", "native_name" => "Bena", "flag" => "", "rtl" => false ],
        "bn_BD" => [ "label" => "Bengali (Bangladesh)", "native_name" => "Bengali (Bangladesh)", "flag" => "", "rtl" => false ],
        "bn_IN" => [ "label" => "Bengali (India)", "native_name" => "Bengali (India)", "flag" => "", "rtl" => false ],
        "bn" => [ "label" => "Bengali", "native_name" => "Bengali", "default_locale" => "bn_BD", "flag" => "", "rtl" => false ],
        "bs_BA" => [ "label" => "Bosnian (Bosnia and Herzegovina)", "native_name" => "Bosnian (Bosnia and Herzegovina)", "flag" => "", "rtl" => false ],
        "bs" => [ "label" => "Bosnian", "native_name" => "Bosnian", "default_locale" => "bs_BA", "flag" => "", "rtl" => false ],
        "bg_BG" => [ "label" => "Bulgarian (Bulgaria)", "native_name" => "Bulgarian (Bulgaria)", "flag" => "", "rtl" => false ],
        "bg" => [ "label" => "Bulgarian", "native_name" => "Bulgarian", "default_locale" => "bg_BG", "flag" => "", "rtl" => false ],
        "my_MM" => [ "label" => "Burmese (Myanmar [Burma])", "native_name" => "Burmese (Myanmar [Burma])", "flag" => "", "rtl" => false ],
        "my" => [ "label" => "Burmese", "native_name" => "Burmese", "default_locale" => "my_MM", "flag" => "", "rtl" => false ],
        "yue_Hant_HK" => [ "label" => "Cantonese (Traditional, Hong Kong SAR China)", "native_name" => "Cantonese (Traditional, Hong Kong SAR China)", "flag" => "", "rtl" => false ],
        "ca_ES" => [ "label" => "Catalan (Spain)", "native_name" => "Catalan (Spain)", "flag" => "", "rtl" => false ],
        "ca" => [ "label" => "Catalan", "native_name" => "Catalan", "flag" => "", "rtl" => false ],
        "tzm_Latn" => [ "label" => "Central Morocco Tamazight (Latin)", "native_name" => "Central Morocco Tamazight (Latin)", "flag" => "", "rtl" => false ],
        "tzm_Latn_MA" => [ "label" => "Central Morocco Tamazight (Latin, Morocco)", "native_name" => "Central Morocco Tamazight (Latin, Morocco)", "flag" => "", "rtl" => false ],
        "tzm" => [ "label" => "Central Morocco Tamazight", "native_name" => "Central Morocco Tamazight", "flag" => "", "rtl" => false ],
        "chr_US" => [ "label" => "Cherokee (United States)", "native_name" => "Cherokee (United States)", "flag" => "", "rtl" => false ],
        "chr" => [ "label" => "Cherokee", "native_name" => "Cherokee", "flag" => "", "rtl" => false ],
        "cgg_UG" => [ "label" => "Chiga (Uganda)", "native_name" => "Chiga (Uganda)", "flag" => "", "rtl" => false ],
        "cgg" => [ "label" => "Chiga", "native_name" => "Chiga", "flag" => "", "rtl" => false ],
        "zh_Hans" => [ "label" => "Chinese (Simplified Han)", "native_name" => "Chinese (Simplified Han)", "flag" => "", "rtl" => false ],
        "zh_Hans_CN" => [ "label" => "Chinese (Simplified Han, China)", "native_name" => "Chinese (Simplified Han, China)", "flag" => "", "rtl" => false ],
        "zh_Hans_HK" => [ "label" => "Chinese (Simplified Han, Hong Kong SAR China)", "native_name" => "Chinese (Simplified Han, Hong Kong SAR China)", "flag" => "", "rtl" => false ],
        "zh_Hans_MO" => [ "label" => "Chinese (Simplified Han, Macau SAR China)", "native_name" => "Chinese (Simplified Han, Macau SAR China)", "flag" => "", "rtl" => false ],
        "zh_Hans_SG" => [ "label" => "Chinese (Simplified Han, Singapore)", "native_name" => "Chinese (Simplified Han, Singapore)", "flag" => "", "rtl" => false ],
        "zh_Hant" => [ "label" => "Chinese (Traditional Han)", "native_name" => "Chinese (Traditional Han)", "flag" => "", "rtl" => false ],
        "zh_Hant_HK" => [ "label" => "Chinese (Traditional Han, Hong Kong SAR China)", "native_name" => "Chinese (Traditional Han, Hong Kong SAR China)", "flag" => "", "rtl" => false ],
        "zh_Hant_MO" => [ "label" => "Chinese (Traditional Han, Macau SAR China)", "native_name" => "Chinese (Traditional Han, Macau SAR China)", "flag" => "", "rtl" => false ],
        "zh_Hant_TW" => [ "label" => "Chinese (Traditional Han, Taiwan)", "native_name" => "Chinese (Traditional Han, Taiwan)", "flag" => "", "rtl" => false ],
        "zh" => [ "label" => "Chinese", "native_name" => "Chinese", "default_locale" => "zh_CN", "flag" => "", "rtl" => false ],
        "kw_GB" => [ "label" => "Cornish (United Kingdom)", "native_name" => "Cornish (United Kingdom)", "flag" => "", "rtl" => false ],
        "kw" => [ "label" => "Cornish", "native_name" => "Cornish", "flag" => "", "rtl" => false ],
        "hr_HR" => [ "label" => "Croatian (Croatia)", "native_name" => "Croatian (Croatia)", "flag" => "", "rtl" => false ],
        "hr" => [ "label" => "Croatian", "native_name" => "Croatian", "default_locale" => "hr", "flag" => "", "rtl" => false ],
        "cs_CZ" => [ "label" => "Czech (Czech Republic)", "native_name" => "Czech (Czech Republic)", "flag" => "", "rtl" => false ],
        "cs" => [ "label" => "Czech", "native_name" => "Czech", "default_locale" => "cs", "flag" => "", "rtl" => false ],
        "da_DK" => [ "label" => "Danish (Denmark)", "native_name" => "Danish (Denmark)", "flag" => "", "rtl" => false ],
        "da" => [ "label" => "Danish", "native_name" => "Danish", "flag" => "", "rtl" => false ],
        "nl_BE" => [ "label" => "Dutch (Belgium)", "native_name" => "Dutch (Belgium)", "flag" => "", "rtl" => false ],
        "nl_NL" => [ "label" => "Dutch (Netherlands)", "native_name" => "Dutch (Netherlands)", "flag" => "", "rtl" => false ],
        "nl" => [ "label" => "Dutch", "native_name" => "Dutch", "default_locale" => "nl_NL", "flag" => "", "rtl" => false ],
        "ebu_KE" => [ "label" => "Embu (Kenya)", "native_name" => "Embu (Kenya)", "flag" => "", "rtl" => false ],
        "ebu" => [ "label" => "Embu", "native_name" => "Embu", "flag" => "", "rtl" => false ],
        "en_AS" => [ "label" => "English (American Samoa)", "native_name" => "English (American Samoa)", "flag" => "", "rtl" => false ],
        "en_AU" => [ "label" => "English (Australia)", "native_name" => "English (Australia)", "flag" => "", "rtl" => false ],
        "en_BE" => [ "label" => "English (Belgium)", "native_name" => "English (Belgium)", "flag" => "", "rtl" => false ],
        "en_BZ" => [ "label" => "English (Belize)", "native_name" => "English (Belize)", "flag" => "", "rtl" => false ],
        "en_BW" => [ "label" => "English (Botswana)", "native_name" => "English (Botswana)", "flag" => "", "rtl" => false ],
        "en_CA" => [ "label" => "English (Canada)", "native_name" => "English (Canada)", "flag" => "", "rtl" => false ],
        "en_GU" => [ "label" => "English (Guam)", "native_name" => "English (Guam)", "flag" => "", "rtl" => false ],
        "en_HK" => [ "label" => "English (Hong Kong SAR China)", "native_name" => "English (Hong Kong SAR China)", "flag" => "", "rtl" => false ],
        "en_IN" => [ "label" => "English (India)", "native_name" => "English (India)", "flag" => "", "rtl" => false ],
        "en_IE" => [ "label" => "English (Ireland)", "native_name" => "English (Ireland)", "flag" => "", "rtl" => false ],
        "en_IL" => [ "label" => "English (Israel)", "native_name" => "English (Israel)", "flag" => "", "rtl" => false ],
        "en_JM" => [ "label" => "English (Jamaica)", "native_name" => "English (Jamaica)", "flag" => "", "rtl" => false ],
        "en_MT" => [ "label" => "English (Malta)", "native_name" => "English (Malta)", "flag" => "", "rtl" => false ],
        "en_MH" => [ "label" => "English (Marshall Islands)", "native_name" => "English (Marshall Islands)", "flag" => "", "rtl" => false ],
        "en_MU" => [ "label" => "English (Mauritius)", "native_name" => "English (Mauritius)", "flag" => "", "rtl" => false ],
        "en_NA" => [ "label" => "English (Namibia)", "native_name" => "English (Namibia)", "flag" => "", "rtl" => false ],
        "en_NZ" => [ "label" => "English (New Zealand)", "native_name" => "English (New Zealand)", "flag" => "", "rtl" => false ],
        "en_MP" => [ "label" => "English (Northern Mariana Islands)", "native_name" => "English (Northern Mariana Islands)", "flag" => "", "rtl" => false ],
        "en_PK" => [ "label" => "English (Pakistan)", "native_name" => "English (Pakistan)", "flag" => "", "rtl" => false ],
        "en_PH" => [ "label" => "English (Philippines)", "native_name" => "English (Philippines)", "flag" => "", "rtl" => false ],
        "en_SG" => [ "label" => "English (Singapore)", "native_name" => "English (Singapore)", "flag" => "", "rtl" => false ],
        "en_ZA" => [ "label" => "English (South Africa)", "native_name" => "English (South Africa)", "flag" => "", "rtl" => false ],
        "en_TT" => [ "label" => "English (Trinidad and Tobago)", "native_name" => "English (Trinidad and Tobago)", "flag" => "", "rtl" => false ],
        "en_UM" => [ "label" => "English (U.S. Minor Outlying Islands)", "native_name" => "English (U.S. Minor Outlying Islands)", "flag" => "", "rtl" => false ],
        "en_VI" => [ "label" => "English (U.S. Virgin Islands)", "native_name" => "English (U.S. Virgin Islands)", "flag" => "", "rtl" => false ],
        "en_GB" => [ "label" => "English (United Kingdom)", "native_name" => "English (United Kingdom)", "flag" => "", "rtl" => false ],
        "en_US" => [ "label" => "English (United States)", "native_name" => "English (United States)", "flag" => "", "rtl" => false ],
        "en_ZW" => [ "label" => "English (Zimbabwe)", "native_name" => "English (Zimbabwe)", "flag" => "", "rtl" => false ],
        "en" => [ "label" => "English", "native_name" => "English", "flag" => "", "rtl" => false ],
        "eo" => [ "label" => "Esperanto", "native_name" => "Esperanto", "flag" => "", "rtl" => false ],
        "et_EE" => [ "label" => "Estonian (Estonia)", "native_name" => "Estonian (Estonia)", "flag" => "", "rtl" => false ],
        "et" => [ "label" => "Estonian", "native_name" => "Estonian", "flag" => "", "rtl" => false ],
        "ee_GH" => [ "label" => "Ewe (Ghana)", "native_name" => "Ewe (Ghana)", "flag" => "", "rtl" => false ],
        "ee_TG" => [ "label" => "Ewe (Togo)", "native_name" => "Ewe (Togo)", "flag" => "", "rtl" => false ],
        "ee" => [ "label" => "Ewe", "native_name" => "Ewe", "flag" => "", "rtl" => false ],
        "fo_FO" => [ "label" => "Faroese (Faroe Islands)", "native_name" => "Faroese (Faroe Islands)", "flag" => "", "rtl" => false ],
        "fo" => [ "label" => "Faroese", "native_name" => "Faroese", "flag" => "", "rtl" => false ],
        "fil_PH" => [ "label" => "Filipino (Philippines)", "native_name" => "Filipino (Philippines)", "flag" => "", "rtl" => false ],
        "fil" => [ "label" => "Filipino", "native_name" => "Filipino", "flag" => "", "rtl" => false ],
        "fi_FI" => [ "label" => "Finnish (Finland)", "native_name" => "Finnish (Finland)", "flag" => "", "rtl" => false ],
        "fi" => [ "label" => "Finnish", "native_name" => "Finnish", "flag" => "", "rtl" => false ],
        "fr_BE" => [ "label" => "French (Belgium)", "native_name" => "French (Belgium)", "flag" => "", "rtl" => false ],
        "fr_BJ" => [ "label" => "French (Benin)", "native_name" => "French (Benin)", "flag" => "", "rtl" => false ],
        "fr_BF" => [ "label" => "French (Burkina Faso)", "native_name" => "French (Burkina Faso)", "flag" => "", "rtl" => false ],
        "fr_BI" => [ "label" => "French (Burundi)", "native_name" => "French (Burundi)", "flag" => "", "rtl" => false ],
        "fr_CM" => [ "label" => "French (Cameroon)", "native_name" => "French (Cameroon)", "flag" => "", "rtl" => false ],
        "fr_CA" => [ "label" => "French (Canada)", "native_name" => "French (Canada)", "flag" => "", "rtl" => false ],
        "fr_CF" => [ "label" => "French (Central African Republic)", "native_name" => "French (Central African Republic)", "flag" => "", "rtl" => false ],
        "fr_TD" => [ "label" => "French (Chad)", "native_name" => "French (Chad)", "flag" => "", "rtl" => false ],
        "fr_KM" => [ "label" => "French (Comoros)", "native_name" => "French (Comoros)", "flag" => "", "rtl" => false ],
        "fr_CG" => [ "label" => "French (Congo - Brazzaville)", "native_name" => "French (Congo - Brazzaville)", "flag" => "", "rtl" => false ],
        "fr_CD" => [ "label" => "French (Congo - Kinshasa)", "native_name" => "French (Congo - Kinshasa)", "flag" => "", "rtl" => false ],
        "fr_CI" => [ "label" => "French (Côte d’Ivoire)", "native_name" => "French (Côte d’Ivoire)", "flag" => "", "rtl" => false ],
        "fr_DJ" => [ "label" => "French (Djibouti)", "native_name" => "French (Djibouti)", "flag" => "", "rtl" => false ],
        "fr_GQ" => [ "label" => "French (Equatorial Guinea)", "native_name" => "French (Equatorial Guinea)", "flag" => "", "rtl" => false ],
        "fr_FR" => [ "label" => "French (France)", "native_name" => "French (France)", "flag" => "", "rtl" => false ],
        "fr_GA" => [ "label" => "French (Gabon)", "native_name" => "French (Gabon)", "flag" => "", "rtl" => false ],
        "fr_GP" => [ "label" => "French (Guadeloupe)", "native_name" => "French (Guadeloupe)", "flag" => "", "rtl" => false ],
        "fr_GN" => [ "label" => "French (Guinea)", "native_name" => "French (Guinea)", "flag" => "", "rtl" => false ],
        "fr_LU" => [ "label" => "French (Luxembourg)", "native_name" => "French (Luxembourg)", "flag" => "", "rtl" => false ],
        "fr_MG" => [ "label" => "French (Madagascar)", "native_name" => "French (Madagascar)", "flag" => "", "rtl" => false ],
        "fr_ML" => [ "label" => "French (Mali)", "native_name" => "French (Mali)", "flag" => "", "rtl" => false ],
        "fr_MQ" => [ "label" => "French (Martinique)", "native_name" => "French (Martinique)", "flag" => "", "rtl" => false ],
        "fr_MC" => [ "label" => "French (Monaco)", "native_name" => "French (Monaco)", "flag" => "", "rtl" => false ],
        "fr_NE" => [ "label" => "French (Niger)", "native_name" => "French (Niger)", "flag" => "", "rtl" => false ],
        "fr_RW" => [ "label" => "French (Rwanda)", "native_name" => "French (Rwanda)", "flag" => "", "rtl" => false ],
        "fr_RE" => [ "label" => "French (Réunion)", "native_name" => "French (Réunion)", "flag" => "", "rtl" => false ],
        "fr_BL" => [ "label" => "French (Saint Barthélemy)", "native_name" => "French (Saint Barthélemy)", "flag" => "", "rtl" => false ],
        "fr_MF" => [ "label" => "French (Saint Martin)", "native_name" => "French (Saint Martin)", "flag" => "", "rtl" => false ],
        "fr_SN" => [ "label" => "French (Senegal)", "native_name" => "French (Senegal)", "flag" => "", "rtl" => false ],
        "fr_CH" => [ "label" => "French (Switzerland)", "native_name" => "French (Switzerland)", "flag" => "", "rtl" => false ],
        "fr_TG" => [ "label" => "French (Togo)", "native_name" => "French (Togo)", "flag" => "", "rtl" => false ],
        "fr" => [ "label" => "French", "native_name" => "French", "default_locale" => "fr_FR", "flag" => "", "rtl" => false ],
        "ff_SN" => [ "label" => "Fulah (Senegal)", "native_name" => "Fulah (Senegal)", "flag" => "", "rtl" => false ],
        "ff" => [ "label" => "Fulah", "native_name" => "Fulah", "flag" => "", "rtl" => false ],
        "gl_ES" => [ "label" => "Galician (Spain)", "native_name" => "Galician (Spain)", "flag" => "", "rtl" => false ],
        "gl" => [ "label" => "Galician", "native_name" => "Galician", "flag" => "", "rtl" => false ],
        "lg_UG" => [ "label" => "Ganda (Uganda)", "native_name" => "Ganda (Uganda)", "flag" => "", "rtl" => false ],
        "lg" => [ "label" => "Ganda", "native_name" => "Ganda", "flag" => "", "rtl" => false ],
        "ka_GE" => [ "label" => "Georgian (Georgia)", "native_name" => "Georgian (Georgia)", "flag" => "", "rtl" => false ],
        "ka" => [ "label" => "Georgian", "native_name" => "Georgian", "flag" => "", "rtl" => false ],
        "de_AT" => [ "label" => "German (Austria)", "native_name" => "German (Austria)", "flag" => "", "rtl" => false ],
        "de_BE" => [ "label" => "German (Belgium)", "native_name" => "German (Belgium)", "flag" => "", "rtl" => false ],
        "de_DE" => [ "label" => "German (Germany)", "native_name" => "German (Germany)", "flag" => "", "rtl" => false ],
        "de_LI" => [ "label" => "German (Liechtenstein)", "native_name" => "German (Liechtenstein)", "flag" => "", "rtl" => false ],
        "de_LU" => [ "label" => "German (Luxembourg)", "native_name" => "German (Luxembourg)", "flag" => "", "rtl" => false ],
        "de_CH" => [ "label" => "German (Switzerland)", "native_name" => "German (Switzerland)", "flag" => "", "rtl" => false ],
        "de" => [ "label" => "German", "native_name" => "German", "default_locale" => "de_DE", "flag" => "", "rtl" => false ],
        "el_CY" => [ "label" => "Greek (Cyprus)", "native_name" => "Greek (Cyprus)", "flag" => "", "rtl" => false ],
        "el_GR" => [ "label" => "Greek (Greece)", "native_name" => "Greek (Greece)", "flag" => "", "rtl" => false ],
        "el" => [ "label" => "Greek", "native_name" => "Greek", "flag" => "", "rtl" => false ],
        "gu_IN" => [ "label" => "Gujarati (India)", "native_name" => "Gujarati (India)", "flag" => "", "rtl" => false ],
        "gu" => [ "label" => "Gujarati", "native_name" => "Gujarati", "flag" => "", "rtl" => false ],
        "guz_KE" => [ "label" => "Gusii (Kenya)", "native_name" => "Gusii (Kenya)", "flag" => "", "rtl" => false ],
        "guz" => [ "label" => "Gusii", "native_name" => "Gusii", "flag" => "", "rtl" => false ],
        "ha_Latn" => [ "label" => "Hausa (Latin)", "native_name" => "Hausa (Latin)", "flag" => "", "rtl" => false ],
        "ha_Latn_GH" => [ "label" => "Hausa (Latin, Ghana)", "native_name" => "Hausa (Latin, Ghana)", "flag" => "", "rtl" => false ],
        "ha_Latn_NE" => [ "label" => "Hausa (Latin, Niger)", "native_name" => "Hausa (Latin, Niger)", "flag" => "", "rtl" => false ],
        "ha_Latn_NG" => [ "label" => "Hausa (Latin, Nigeria)", "native_name" => "Hausa (Latin, Nigeria)", "flag" => "", "rtl" => false ],
        "ha" => [ "label" => "Hausa", "native_name" => "Hausa", "flag" => "", "rtl" => false ],
        "haw_US" => [ "label" => "Hawaiian (United States)", "native_name" => "Hawaiian (United States)", "flag" => "", "rtl" => false ],
        "haw" => [ "label" => "Hawaiian", "native_name" => "Hawaiian", "flag" => "", "rtl" => false ],
        "he_IL" => [ "label" => "Hebrew (Israel)", "native_name" => "Hebrew (Israel)", "flag" => "", "rtl" => false ],
        "he" => [ "label" => "Hebrew", "native_name" => "Hebrew", "flag" => "", "rtl" => false ],
        "hi_IN" => [ "label" => "Hindi (India)", "native_name" => "Hindi (India)", "flag" => "", "rtl" => false ],
        "hi" => [ "label" => "Hindi", "native_name" => "Hindi", "default_locale" => "hi_IN", "flag" => "", "rtl" => false ],
        "hu_HU" => [ "label" => "Hungarian (Hungary)", "native_name" => "Hungarian (Hungary)", "flag" => "", "rtl" => false ],
        "hu" => [ "label" => "Hungarian", "native_name" => "Hungarian", "default_locale" => "hu_HU", "flag" => "", "rtl" => false ],
        "is_IS" => [ "label" => "Icelandic (Iceland)", "native_name" => "Icelandic (Iceland)", "flag" => "", "rtl" => false ],
        "is" => [ "label" => "Icelandic", "native_name" => "Icelandic", "flag" => "", "rtl" => false ],
        "ig_NG" => [ "label" => "Igbo (Nigeria)", "native_name" => "Igbo (Nigeria)", "flag" => "", "rtl" => false ],
        "ig" => [ "label" => "Igbo", "native_name" => "Igbo", "flag" => "", "rtl" => false ],
        "id_ID" => [ "label" => "Indonesian (Indonesia)", "native_name" => "Indonesian (Indonesia)", "flag" => "", "rtl" => false ],
        "id" => [ "label" => "Indonesian", "native_name" => "Indonesian", "default_locale" => "id_ID", "flag" => "", "rtl" => false ],
        "ga_IE" => [ "label" => "Irish (Ireland)", "native_name" => "Irish (Ireland)", "flag" => "", "rtl" => false ],
        "ga" => [ "label" => "Irish", "native_name" => "Irish", "flag" => "", "rtl" => false ],
        "it_IT" => [ "label" => "Italian (Italy)", "native_name" => "Italian (Italy)", "flag" => "", "rtl" => false ],
        "it_CH" => [ "label" => "Italian (Switzerland)", "native_name" => "Italian (Switzerland)", "flag" => "", "rtl" => false ],
        "it" => [ "label" => "Italian", "native_name" => "Italian", "default_locale" => "it_IT", "flag" => "", "rtl" => false ],
        "ja_JP" => [ "label" => "Japanese (Japan)", "native_name" => "Japanese (Japan)", "flag" => "", "rtl" => false ],
        "ja" => [ "label" => "Japanese", "native_name" => "Japanese", "default_locale" => "ja", "flag" => "", "rtl" => false ],
        "kea_CV" => [ "label" => "Kabuverdianu (Cape Verde)", "native_name" => "Kabuverdianu (Cape Verde)", "flag" => "", "rtl" => false ],
        "kea" => [ "label" => "Kabuverdianu", "native_name" => "Kabuverdianu", "flag" => "", "rtl" => false ],
        "kab_DZ" => [ "label" => "Kabyle (Algeria)", "native_name" => "Kabyle (Algeria)", "flag" => "", "rtl" => false ],
        "kab" => [ "label" => "Kabyle", "native_name" => "Kabyle", "flag" => "", "rtl" => false ],
        "kl_GL" => [ "label" => "Kalaallisut (Greenland)", "native_name" => "Kalaallisut (Greenland)", "flag" => "", "rtl" => false ],
        "kl" => [ "label" => "Kalaallisut", "native_name" => "Kalaallisut", "flag" => "", "rtl" => false ],
        "kln_KE" => [ "label" => "Kalenjin (Kenya)", "native_name" => "Kalenjin (Kenya)", "flag" => "", "rtl" => false ],
        "kln" => [ "label" => "Kalenjin", "native_name" => "Kalenjin", "flag" => "", "rtl" => false ],
        "kam_KE" => [ "label" => "Kamba (Kenya)", "native_name" => "Kamba (Kenya)", "flag" => "", "rtl" => false ],
        "kam" => [ "label" => "Kamba", "native_name" => "Kamba", "flag" => "", "rtl" => false ],
        "kn_IN" => [ "label" => "Kannada (India)", "native_name" => "Kannada (India)", "flag" => "", "rtl" => false ],
        "kn" => [ "label" => "Kannada", "native_name" => "Kannada", "flag" => "", "rtl" => false ],
        "kk_Cyrl" => [ "label" => "Kazakh (Cyrillic)", "native_name" => "Kazakh (Cyrillic)", "flag" => "", "rtl" => false ],
        "kk_Cyrl_KZ" => [ "label" => "Kazakh (Cyrillic, Kazakhstan)", "native_name" => "Kazakh (Cyrillic, Kazakhstan)", "flag" => "", "rtl" => false ],
        "kk" => [ "label" => "Kazakh", "native_name" => "Kazakh", "flag" => "", "rtl" => false ],
        "km_KH" => [ "label" => "Khmer (Cambodia)", "native_name" => "Khmer (Cambodia)", "flag" => "", "rtl" => false ],
        "km" => [ "label" => "Khmer", "native_name" => "Khmer", "flag" => "", "rtl" => false ],
        "ki_KE" => [ "label" => "Kikuyu (Kenya)", "native_name" => "Kikuyu (Kenya)", "flag" => "", "rtl" => false ],
        "ki" => [ "label" => "Kikuyu", "native_name" => "Kikuyu", "flag" => "", "rtl" => false ],
        "rw_RW" => [ "label" => "Kinyarwanda (Rwanda)", "native_name" => "Kinyarwanda (Rwanda)", "flag" => "", "rtl" => false ],
        "rw" => [ "label" => "Kinyarwanda", "native_name" => "Kinyarwanda", "flag" => "", "rtl" => false ],
        "kok_IN" => [ "label" => "Konkani (India)", "native_name" => "Konkani (India)", "flag" => "", "rtl" => false ],
        "kok" => [ "label" => "Konkani", "native_name" => "Konkani", "flag" => "", "rtl" => false ],
        "ko_KR" => [ "label" => "Korean (South Korea)", "native_name" => "Korean (South Korea)", "flag" => "", "rtl" => false ],
        "ko" => [ "label" => "Korean", "native_name" => "Korean", "default_locale" => "ko_KR", "flag" => "", "rtl" => false ],
        "khq_ML" => [ "label" => "Koyra Chiini (Mali)", "native_name" => "Koyra Chiini (Mali)", "flag" => "", "rtl" => false ],
        "khq" => [ "label" => "Koyra Chiini", "native_name" => "Koyra Chiini", "flag" => "", "rtl" => false ],
        "ses_ML" => [ "label" => "Koyraboro Senni (Mali)", "native_name" => "Koyraboro Senni (Mali)", "flag" => "", "rtl" => false ],
        "ses" => [ "label" => "Koyraboro Senni", "native_name" => "Koyraboro Senni", "flag" => "", "rtl" => false ],
        "lag_TZ" => [ "label" => "Langi (Tanzania)", "native_name" => "Langi (Tanzania)", "flag" => "", "rtl" => false ],
        "lag" => [ "label" => "Langi", "native_name" => "Langi", "flag" => "", "rtl" => false ],
        "lv_LV" => [ "label" => "Latvian (Latvia)", "native_name" => "Latvian (Latvia)", "flag" => "", "rtl" => false ],
        "lv" => [ "label" => "Latvian", "native_name" => "Latvian", "flag" => "", "rtl" => false ],
        "lt_LT" => [ "label" => "Lithuanian (Lithuania)", "native_name" => "Lithuanian (Lithuania)", "flag" => "", "rtl" => false ],
        "lt" => [ "label" => "Lithuanian", "native_name" => "Lithuanian", "flag" => "", "rtl" => false ],
        "luo_KE" => [ "label" => "Luo (Kenya)", "native_name" => "Luo (Kenya)", "flag" => "", "rtl" => false ],
        "luo" => [ "label" => "Luo", "native_name" => "Luo", "flag" => "", "rtl" => false ],
        "luy_KE" => [ "label" => "Luyia (Kenya)", "native_name" => "Luyia (Kenya)", "flag" => "", "rtl" => false ],
        "luy" => [ "label" => "Luyia", "native_name" => "Luyia", "flag" => "", "rtl" => false ],
        "mk_MK" => [ "label" => "Macedonian (Macedonia)", "native_name" => "Macedonian (Macedonia)", "flag" => "", "rtl" => false ],
        "mk" => [ "label" => "Macedonian", "native_name" => "Macedonian", "default_locale" => "mk_MK", "flag" => "", "rtl" => false ],
        "jmc_TZ" => [ "label" => "Machame (Tanzania)", "native_name" => "Machame (Tanzania)", "flag" => "", "rtl" => false ],
        "jmc" => [ "label" => "Machame", "native_name" => "Machame", "flag" => "", "rtl" => false ],
        "kde_TZ" => [ "label" => "Makonde (Tanzania)", "native_name" => "Makonde (Tanzania)", "flag" => "", "rtl" => false ],
        "kde" => [ "label" => "Makonde", "native_name" => "Makonde", "flag" => "", "rtl" => false ],
        "mg_MG" => [ "label" => "Malagasy (Madagascar)", "native_name" => "Malagasy (Madagascar)", "flag" => "", "rtl" => false ],
        "mg" => [ "label" => "Malagasy", "native_name" => "Malagasy", "flag" => "", "rtl" => false ],
        "ms_BN" => [ "label" => "Malay (Brunei)", "native_name" => "Malay (Brunei)", "flag" => "", "rtl" => false ],
        "ms_MY" => [ "label" => "Malay (Malaysia)", "native_name" => "Malay (Malaysia)", "flag" => "", "rtl" => false ],
        "ms" => [ "label" => "Malay", "native_name" => "Malay", "flag" => "", "rtl" => false ],
        "ml_IN" => [ "label" => "Malayalam (India)", "native_name" => "Malayalam (India)", "flag" => "", "rtl" => false ],
        "ml" => [ "label" => "Malayalam", "native_name" => "Malayalam", "flag" => "", "rtl" => false ],
        "mt_MT" => [ "label" => "Maltese (Malta)", "native_name" => "Maltese (Malta)", "flag" => "", "rtl" => false ],
        "mt" => [ "label" => "Maltese", "native_name" => "Maltese", "flag" => "", "rtl" => false ],
        "gv_GB" => [ "label" => "Manx (United Kingdom)", "native_name" => "Manx (United Kingdom)", "flag" => "", "rtl" => false ],
        "gv" => [ "label" => "Manx", "native_name" => "Manx", "flag" => "", "rtl" => false ],
        "mr_IN" => [ "label" => "Marathi (India)", "native_name" => "Marathi (India)", "flag" => "", "rtl" => false ],
        "mr" => [ "label" => "Marathi", "native_name" => "Marathi", "default_locale" => "mr", "flag" => "", "rtl" => false ],
        "mas_KE" => [ "label" => "Masai (Kenya)", "native_name" => "Masai (Kenya)", "flag" => "", "rtl" => false ],
        "mas_TZ" => [ "label" => "Masai (Tanzania)", "native_name" => "Masai (Tanzania)", "flag" => "", "rtl" => false ],
        "mas" => [ "label" => "Masai", "native_name" => "Masai", "flag" => "", "rtl" => false ],
        "mer_KE" => [ "label" => "Meru (Kenya)", "native_name" => "Meru (Kenya)", "flag" => "", "rtl" => false ],
        "mer" => [ "label" => "Meru", "native_name" => "Meru", "flag" => "", "rtl" => false ],
        "mfe_MU" => [ "label" => "Morisyen (Mauritius)", "native_name" => "Morisyen (Mauritius)", "flag" => "", "rtl" => false ],
        "mfe" => [ "label" => "Morisyen", "native_name" => "Morisyen", "flag" => "", "rtl" => false ],
        "naq_NA" => [ "label" => "Nama (Namibia)", "native_name" => "Nama (Namibia)", "flag" => "", "rtl" => false ],
        "naq" => [ "label" => "Nama", "native_name" => "Nama", "flag" => "", "rtl" => false ],
        "ne_IN" => [ "label" => "Nepali (India)", "native_name" => "Nepali (India)", "flag" => "", "rtl" => false ],
        "ne_NP" => [ "label" => "Nepali (Nepal)", "native_name" => "Nepali (Nepal)", "flag" => "", "rtl" => false ],
        "ne" => [ "label" => "Nepali", "native_name" => "Nepali", "default_locale" => "ne_NP", "flag" => "", "rtl" => false ],
        "nd_ZW" => [ "label" => "North Ndebele (Zimbabwe)", "native_name" => "North Ndebele (Zimbabwe)", "flag" => "", "rtl" => false ],
        "nd" => [ "label" => "North Ndebele", "native_name" => "North Ndebele", "flag" => "", "rtl" => false ],
        "nb_NO" => [ "label" => "Norwegian Bokmål (Norway)", "native_name" => "Norwegian Bokmål (Norway)", "flag" => "", "rtl" => false ],
        "nb" => [ "label" => "Norwegian Bokmål", "native_name" => "Norwegian Bokmål", "flag" => "", "rtl" => false ],
        "nn_NO" => [ "label" => "Norwegian Nynorsk (Norway)", "native_name" => "Norwegian Nynorsk (Norway)", "flag" => "", "rtl" => false ],
        "nn" => [ "label" => "Norwegian Nynorsk", "native_name" => "Norwegian Nynorsk", "flag" => "", "rtl" => false ],
        "nyn_UG" => [ "label" => "Nyankole (Uganda)", "native_name" => "Nyankole (Uganda)", "flag" => "", "rtl" => false ],
        "nyn" => [ "label" => "Nyankole", "native_name" => "Nyankole", "flag" => "", "rtl" => false ],
        "or_IN" => [ "label" => "Oriya (India)", "native_name" => "Oriya (India)", "flag" => "", "rtl" => false ],
        "or" => [ "label" => "Oriya", "native_name" => "Oriya", "flag" => "", "rtl" => false ],
        "om_ET" => [ "label" => "Oromo (Ethiopia)", "native_name" => "Oromo (Ethiopia)", "flag" => "", "rtl" => false ],
        "om_KE" => [ "label" => "Oromo (Kenya)", "native_name" => "Oromo (Kenya)", "flag" => "", "rtl" => false ],
        "om" => [ "label" => "Oromo", "native_name" => "Oromo", "flag" => "", "rtl" => false ],
        "ps_AF" => [ "label" => "Pashto (Afghanistan)", "native_name" => "Pashto (Afghanistan)", "flag" => "", "rtl" => false ],
        "ps" => [ "label" => "Pashto", "native_name" => "Pashto", "flag" => "", "rtl" => false ],
        "fa_AF" => [ "label" => "Persian (Afghanistan)", "native_name" => "Persian (Afghanistan)", "flag" => "", "rtl" => false ],
        "fa_IR" => [ "label" => "Persian (Iran)", "native_name" => "Persian (Iran)", "flag" => "", "rtl" => false ],
        "fa" => [ "label" => "Persian", "native_name" => "Persian", "default_locale" => "fa_IR", "flag" => "", "rtl" => false ],
        "pl_PL" => [ "label" => "Polish (Poland)", "native_name" => "Polish (Poland)", "flag" => "", "rtl" => false ],
        "pl" => [ "label" => "Polish", "native_name" => "Polish", "flag" => "", "rtl" => false ],
        "pt_BR" => [ "label" => "Portuguese (Brazil)", "native_name" => "Portuguese (Brazil)", "flag" => "", "rtl" => false ],
        "pt_GW" => [ "label" => "Portuguese (Guinea-Bissau)", "native_name" => "Portuguese (Guinea-Bissau)", "flag" => "", "rtl" => false ],
        "pt_MZ" => [ "label" => "Portuguese (Mozambique)", "native_name" => "Portuguese (Mozambique)", "flag" => "", "rtl" => false ],
        "pt_PT" => [ "label" => "Portuguese (Portugal)", "native_name" => "Portuguese (Portugal)", "flag" => "", "rtl" => false ],
        "pt" => [ "label" => "Portuguese", "native_name" => "Portuguese", "default_locale" => "pt_BR", "flag" => "", "rtl" => false ],
        "pa_Arab" => [ "label" => "Punjabi (Arabic)", "native_name" => "Punjabi (Arabic)", "flag" => "", "rtl" => false ],
        "pa_Arab_PK" => [ "label" => "Punjabi (Arabic, Pakistan)", "native_name" => "Punjabi (Arabic, Pakistan)", "flag" => "", "rtl" => false ],
        "pa_Guru" => [ "label" => "Punjabi (Gurmukhi)", "native_name" => "Punjabi (Gurmukhi)", "flag" => "", "rtl" => false ],
        "pa_Guru_IN" => [ "label" => "Punjabi (Gurmukhi, India)", "native_name" => "Punjabi (Gurmukhi, India)", "flag" => "", "rtl" => false ],
        "pa" => [ "label" => "Punjabi", "native_name" => "Punjabi", "default_locale" => "pa_IN", "flag" => "", "rtl" => false ],
        "ro_MD" => [ "label" => "Romanian (Moldova)", "native_name" => "Romanian (Moldova)", "flag" => "", "rtl" => false ],
        "ro_RO" => [ "label" => "Romanian (Romania)", "native_name" => "Romanian (Romania)", "flag" => "", "rtl" => false ],
        "ro" => [ "label" => "Romanian", "native_name" => "Romanian", "default_locale" => "ro_RO", "flag" => "", "rtl" => false ],
        "rm_CH" => [ "label" => "Romansh (Switzerland)", "native_name" => "Romansh (Switzerland)", "flag" => "", "rtl" => false ],
        "rm" => [ "label" => "Romansh", "native_name" => "Romansh", "flag" => "", "rtl" => false ],
        "rof_TZ" => [ "label" => "Rombo (Tanzania)", "native_name" => "Rombo (Tanzania)", "flag" => "", "rtl" => false ],
        "rof" => [ "label" => "Rombo", "native_name" => "Rombo", "flag" => "", "rtl" => false ],
        "ru_MD" => [ "label" => "Russian (Moldova)", "native_name" => "Russian (Moldova)", "flag" => "", "rtl" => false ],
        "ru_RU" => [ "label" => "Russian (Russia)", "native_name" => "Russian (Russia)", "flag" => "", "rtl" => false ],
        "ru_UA" => [ "label" => "Russian (Ukraine)", "native_name" => "Russian (Ukraine)", "flag" => "", "rtl" => false ],
        "ru" => [ "label" => "Russian", "native_name" => "Russian", "default_locale" => "ru_RU", "flag" => "", "rtl" => false ],
        "rwk_TZ" => [ "label" => "Rwa (Tanzania)", "native_name" => "Rwa (Tanzania)", "flag" => "", "rtl" => false ],
        "rwk" => [ "label" => "Rwa", "native_name" => "Rwa", "flag" => "", "rtl" => false ],
        "saq_KE" => [ "label" => "Samburu (Kenya)", "native_name" => "Samburu (Kenya)", "flag" => "", "rtl" => false ],
        "saq" => [ "label" => "Samburu", "native_name" => "Samburu", "flag" => "", "rtl" => false ],
        "sg_CF" => [ "label" => "Sango (Central African Republic)", "native_name" => "Sango (Central African Republic)", "flag" => "", "rtl" => false ],
        "sg" => [ "label" => "Sango", "native_name" => "Sango", "flag" => "", "rtl" => false ],
        "seh_MZ" => [ "label" => "Sena (Mozambique)", "native_name" => "Sena (Mozambique)", "flag" => "", "rtl" => false ],
        "seh" => [ "label" => "Sena", "native_name" => "Sena", "flag" => "", "rtl" => false ],
        "sr_Cyrl" => [ "label" => "Serbian (Cyrillic)", "native_name" => "Serbian (Cyrillic)", "flag" => "", "rtl" => false ],
        "sr_Cyrl_BA" => [ "label" => "Serbian (Cyrillic, Bosnia and Herzegovina)", "native_name" => "Serbian (Cyrillic, Bosnia and Herzegovina)", "flag" => "", "rtl" => false ],
        "sr_Cyrl_ME" => [ "label" => "Serbian (Cyrillic, Montenegro)", "native_name" => "Serbian (Cyrillic, Montenegro)", "flag" => "", "rtl" => false ],
        "sr_Cyrl_RS" => [ "label" => "Serbian (Cyrillic, Serbia)", "native_name" => "Serbian (Cyrillic, Serbia)", "flag" => "", "rtl" => false ],
        "sr_Latn" => [ "label" => "Serbian (Latin)", "native_name" => "Serbian (Latin)", "flag" => "", "rtl" => false ],
        "sr_Latn_BA" => [ "label" => "Serbian (Latin, Bosnia and Herzegovina)", "native_name" => "Serbian (Latin, Bosnia and Herzegovina)", "flag" => "", "rtl" => false ],
        "sr_Latn_ME" => [ "label" => "Serbian (Latin, Montenegro)", "native_name" => "Serbian (Latin, Montenegro)", "flag" => "", "rtl" => false ],
        "sr_Latn_RS" => [ "label" => "Serbian (Latin, Serbia)", "native_name" => "Serbian (Latin, Serbia)", "flag" => "", "rtl" => false ],
        "sr" => [ "label" => "Serbian", "native_name" => "Serbian", "default_locale" => "sr_BA", "flag" => "", "rtl" => false ],
        "sn_ZW" => [ "label" => "Shona (Zimbabwe)", "native_name" => "Shona (Zimbabwe)", "flag" => "", "rtl" => false ],
        "sn" => [ "label" => "Shona", "native_name" => "Shona", "flag" => "", "rtl" => false ],
        "ii_CN" => [ "label" => "Sichuan Yi (China)", "native_name" => "Sichuan Yi (China)", "flag" => "", "rtl" => false ],
        "ii" => [ "label" => "Sichuan Yi", "native_name" => "Sichuan Yi", "flag" => "", "rtl" => false ],
        "si_LK" => [ "label" => "Sinhala (Sri Lanka)", "native_name" => "Sinhala (Sri Lanka)", "flag" => "", "rtl" => false ],
        "si" => [ "label" => "Sinhala", "native_name" => "Sinhala", "flag" => "", "rtl" => false ],
        "sk_SK" => [ "label" => "Slovak (Slovakia)", "native_name" => "Slovak (Slovakia)", "flag" => "", "rtl" => false ],
        "sk" => [ "label" => "Slovak", "native_name" => "Slovak", "flag" => "", "rtl" => false ],
        "sl_SI" => [ "label" => "Slovenian (Slovenia)", "native_name" => "Slovenian (Slovenia)", "flag" => "", "rtl" => false ],
        "sl" => [ "label" => "Slovenian", "native_name" => "Slovenian", "default_locale" => "sl_SI", "flag" => "", "rtl" => false ],
        "xog_UG" => [ "label" => "Soga (Uganda)", "native_name" => "Soga (Uganda)", "flag" => "", "rtl" => false ],
        "xog" => [ "label" => "Soga", "native_name" => "Soga", "flag" => "", "rtl" => false ],
        "so_DJ" => [ "label" => "Somali (Djibouti)", "native_name" => "Somali (Djibouti)", "flag" => "", "rtl" => false ],
        "so_ET" => [ "label" => "Somali (Ethiopia)", "native_name" => "Somali (Ethiopia)", "flag" => "", "rtl" => false ],
        "so_KE" => [ "label" => "Somali (Kenya)", "native_name" => "Somali (Kenya)", "flag" => "", "rtl" => false ],
        "so_SO" => [ "label" => "Somali (Somalia)", "native_name" => "Somali (Somalia)", "flag" => "", "rtl" => false ],
        "so" => [ "label" => "Somali", "native_name" => "Somali", "flag" => "", "rtl" => false ],
        "es_AR" => [ "label" => "Spanish (Argentina)", "native_name" => "Spanish (Argentina)", "flag" => "", "rtl" => false ],
        "es_BO" => [ "label" => "Spanish (Bolivia)", "native_name" => "Spanish (Bolivia)", "flag" => "", "rtl" => false ],
        "es_CL" => [ "label" => "Spanish (Chile)", "native_name" => "Spanish (Chile)", "flag" => "", "rtl" => false ],
        "es_CO" => [ "label" => "Spanish (Colombia)", "native_name" => "Spanish (Colombia)", "flag" => "", "rtl" => false ],
        "es_CR" => [ "label" => "Spanish (Costa Rica)", "native_name" => "Spanish (Costa Rica)", "flag" => "", "rtl" => false ],
        "es_DO" => [ "label" => "Spanish (Dominican Republic)", "native_name" => "Spanish (Dominican Republic)", "flag" => "", "rtl" => false ],
        "es_EC" => [ "label" => "Spanish (Ecuador)", "native_name" => "Spanish (Ecuador)", "flag" => "", "rtl" => false ],
        "es_SV" => [ "label" => "Spanish (El Salvador)", "native_name" => "Spanish (El Salvador)", "flag" => "", "rtl" => false ],
        "es_GQ" => [ "label" => "Spanish (Equatorial Guinea)", "native_name" => "Spanish (Equatorial Guinea)", "flag" => "", "rtl" => false ],
        "es_GT" => [ "label" => "Spanish (Guatemala)", "native_name" => "Spanish (Guatemala)", "flag" => "", "rtl" => false ],
        "es_HN" => [ "label" => "Spanish (Honduras)", "native_name" => "Spanish (Honduras)", "flag" => "", "rtl" => false ],
        "es_419" => [ "label" => "Spanish (Latin America)", "native_name" => "Spanish (Latin America)", "flag" => "", "rtl" => false ],
        "es_MX" => [ "label" => "Spanish (Mexico)", "native_name" => "Spanish (Mexico)", "flag" => "", "rtl" => false ],
        "es_NI" => [ "label" => "Spanish (Nicaragua)", "native_name" => "Spanish (Nicaragua)", "flag" => "", "rtl" => false ],
        "es_PA" => [ "label" => "Spanish (Panama)", "native_name" => "Spanish (Panama)", "flag" => "", "rtl" => false ],
        "es_PY" => [ "label" => "Spanish (Paraguay)", "native_name" => "Spanish (Paraguay)", "flag" => "", "rtl" => false ],
        "es_PE" => [ "label" => "Spanish (Peru)", "native_name" => "Spanish (Peru)", "flag" => "", "rtl" => false ],
        "es_PR" => [ "label" => "Spanish (Puerto Rico)", "native_name" => "Spanish (Puerto Rico)", "flag" => "", "rtl" => false ],
        "es_ES" => [ "label" => "Spanish (Spain)", "native_name" => "Spanish (Spain)", "flag" => "", "rtl" => false ],
        "es_US" => [ "label" => "Spanish (United States)", "native_name" => "Spanish (United States)", "flag" => "", "rtl" => false ],
        "es_UY" => [ "label" => "Spanish (Uruguay)", "native_name" => "Spanish (Uruguay)", "flag" => "", "rtl" => false ],
        "es_VE" => [ "label" => "Spanish (Venezuela)", "native_name" => "Spanish (Venezuela)", "flag" => "", "rtl" => false ],
        "es" => [ "label" => "Spanish", "native_name" => "Spanish", "default_locale" => "es_ES", "flag" => "", "rtl" => false ],
        "sw_KE" => [ "label" => "Swahili (Kenya)", "native_name" => "Swahili (Kenya)", "flag" => "", "rtl" => false ],
        "sw_TZ" => [ "label" => "Swahili (Tanzania)", "native_name" => "Swahili (Tanzania)", "flag" => "", "rtl" => false ],
        "sw" => [ "label" => "Swahili", "native_name" => "Swahili", "default_locale" => "sw", "flag" => "", "rtl" => false ],
        "sv_FI" => [ "label" => "Swedish (Finland)", "native_name" => "Swedish (Finland)", "flag" => "", "rtl" => false ],
        "sv_SE" => [ "label" => "Swedish (Sweden)", "native_name" => "Swedish (Sweden)", "flag" => "", "rtl" => false ],
        "sv" => [ "label" => "Swedish", "native_name" => "Swedish", "flag" => "", "rtl" => false ],
        "gsw_CH" => [ "label" => "Swiss German (Switzerland)", "native_name" => "Swiss German (Switzerland)", "flag" => "", "rtl" => false ],
        "gsw" => [ "label" => "Swiss German", "native_name" => "Swiss German", "flag" => "", "rtl" => false ],
        "shi_Latn" => [ "label" => "Tachelhit (Latin)", "native_name" => "Tachelhit (Latin)", "flag" => "", "rtl" => false ],
        "shi_Latn_MA" => [ "label" => "Tachelhit (Latin, Morocco)", "native_name" => "Tachelhit (Latin, Morocco)", "flag" => "", "rtl" => false ],
        "shi_Tfng" => [ "label" => "Tachelhit (Tifinagh)", "native_name" => "Tachelhit (Tifinagh)", "flag" => "", "rtl" => false ],
        "shi_Tfng_MA" => [ "label" => "Tachelhit (Tifinagh, Morocco)", "native_name" => "Tachelhit (Tifinagh, Morocco)", "flag" => "", "rtl" => false ],
        "shi" => [ "label" => "Tachelhit", "native_name" => "Tachelhit", "flag" => "", "rtl" => false ],
        "dav_KE" => [ "label" => "Taita (Kenya)", "native_name" => "Taita (Kenya)", "flag" => "", "rtl" => false ],
        "dav" => [ "label" => "Taita", "native_name" => "Taita", "flag" => "", "rtl" => false ],
        "ta_IN" => [ "label" => "Tamil (India)", "native_name" => "Tamil (India)", "flag" => "", "rtl" => false ],
        "ta_LK" => [ "label" => "Tamil (Sri Lanka)", "native_name" => "Tamil (Sri Lanka)", "flag" => "", "rtl" => false ],
        "ta" => [ "label" => "Tamil", "native_name" => "Tamil", "flag" => "", "rtl" => false ],
        "te_IN" => [ "label" => "Telugu (India)", "native_name" => "Telugu (India)", "flag" => "", "rtl" => false ],
        "te" => [ "label" => "Telugu", "native_name" => "Telugu", "flag" => "", "rtl" => false ],
        "teo_KE" => [ "label" => "Teso (Kenya)", "native_name" => "Teso (Kenya)", "flag" => "", "rtl" => false ],
        "teo_UG" => [ "label" => "Teso (Uganda)", "native_name" => "Teso (Uganda)", "flag" => "", "rtl" => false ],
        "teo" => [ "label" => "Teso", "native_name" => "Teso", "flag" => "", "rtl" => false ],
        "th_TH" => [ "label" => "Thai (Thailand)", "native_name" => "Thai (Thailand)", "flag" => "", "rtl" => false ],
        "th" => [ "label" => "Thai", "native_name" => "Thai", "flag" => "", "rtl" => false ],
        "bo_CN" => [ "label" => "Tibetan (China)", "native_name" => "Tibetan (China)", "flag" => "", "rtl" => false ],
        "bo_IN" => [ "label" => "Tibetan (India)", "native_name" => "Tibetan (India)", "flag" => "", "rtl" => false ],
        "bo" => [ "label" => "Tibetan", "native_name" => "Tibetan", "flag" => "", "rtl" => false ],
        "ti_ER" => [ "label" => "Tigrinya (Eritrea)", "native_name" => "Tigrinya (Eritrea)", "flag" => "", "rtl" => false ],
        "ti_ET" => [ "label" => "Tigrinya (Ethiopia)", "native_name" => "Tigrinya (Ethiopia)", "flag" => "", "rtl" => false ],
        "ti" => [ "label" => "Tigrinya", "native_name" => "Tigrinya", "flag" => "", "rtl" => false ],
        "to_TO" => [ "label" => "Tonga (Tonga)", "native_name" => "Tonga (Tonga)", "flag" => "", "rtl" => false ],
        "to" => [ "label" => "Tonga", "native_name" => "Tonga", "flag" => "", "rtl" => false ],
        "tr_TR" => [ "label" => "Turkish (Turkey)", "native_name" => "Turkish (Turkey)", "flag" => "", "rtl" => false ],
        "tr" => [ "label" => "Turkish", "native_name" => "Turkish", "default_locale" => "tr_TR", "flag" => "", "rtl" => false ],
        "uk_UA" => [ "label" => "Ukrainian (Ukraine)", "native_name" => "Ukrainian (Ukraine)", "flag" => "", "rtl" => false ],
        "uk" => [ "label" => "Ukrainian", "native_name" => "Ukrainian", "flag" => "", "rtl" => false ],
        "ur_IN" => [ "label" => "Urdu (India)", "native_name" => "Urdu (India)", "flag" => "", "rtl" => false ],
        "ur_PK" => [ "label" => "Urdu (Pakistan)", "native_name" => "Urdu (Pakistan)", "flag" => "", "rtl" => false ],
        "ur" => [ "label" => "Urdu", "native_name" => "Urdu", "flag" => "", "rtl" => false ],
        "uz_Arab" => [ "label" => "Uzbek (Arabic)", "native_name" => "Uzbek (Arabic)", "flag" => "", "rtl" => false ],
        "uz_Arab_AF" => [ "label" => "Uzbek (Arabic, Afghanistan)", "native_name" => "Uzbek (Arabic, Afghanistan)", "flag" => "", "rtl" => false ],
        "uz_Cyrl" => [ "label" => "Uzbek (Cyrillic)", "native_name" => "Uzbek (Cyrillic)", "flag" => "", "rtl" => false ],
        "uz_Cyrl_UZ" => [ "label" => "Uzbek (Cyrillic, Uzbekistan)", "native_name" => "Uzbek (Cyrillic, Uzbekistan)", "flag" => "", "rtl" => false ],
        "uz_Latn" => [ "label" => "Uzbek (Latin)", "native_name" => "Uzbek (Latin)", "flag" => "", "rtl" => false ],
        "uz_Latn_UZ" => [ "label" => "Uzbek (Latin, Uzbekistan)", "native_name" => "Uzbek (Latin, Uzbekistan)", "flag" => "", "rtl" => false ],
        "uz" => [ "label" => "Uzbek", "native_name" => "Uzbek", "flag" => "", "rtl" => false ],
        "vi_VN" => [ "label" => "Vietlabelse (Vietnam)", "native_name" => "Vietlabelse (Vietnam)", "flag" => "", "rtl" => false ],
        "vi" => [ "label" => "Vietlabelse", "native_name" => "Vietlabelse", "default_locale" => "vi", "flag" => "", "rtl" => false ],
        "vun_TZ" => [ "label" => "Vunjo (Tanzania)", "native_name" => "Vunjo (Tanzania)", "flag" => "", "rtl" => false ],
        "vun" => [ "label" => "Vunjo", "native_name" => "Vunjo", "flag" => "", "rtl" => false ],
        "cy_GB" => [ "label" => "Welsh (United Kingdom)", "native_name" => "Welsh (United Kingdom)", "flag" => "", "rtl" => false ],
        "cy" => [ "label" => "Welsh", "native_name" => "Welsh", "flag" => "", "rtl" => false ],
        "yo_NG" => [ "label" => "Yoruba (Nigeria)", "native_name" => "Yoruba (Nigeria)", "flag" => "", "rtl" => false ],
        "yo" => [ "label" => "Yoruba", "native_name" => "Yoruba", "flag" => "", "rtl" => false ],
        "zu_ZA" => [ "label" => "Zulu (South Africa)", "native_name" => "Zulu (South Africa)", "flag" => "", "rtl" => false ],
        "zu" => [ "label" => "Zulu", "native_name" => "Zulu", "flag" => "", "rtl" => false ],
    ];

    return apply_filters( "dt_global_languages_list", $global_languages_list );
}
