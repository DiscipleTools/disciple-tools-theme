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
        "af" => [ "label" => "Afrikaans", "native_name" => "Afrikaans", "flag" => "🇿🇦", "rtl" => false ],
        "af_NA" => [ "label" => "Afrikaans (Namibia)", "native_name" => "Afrikáans Namibië", "flag" => "🇳🇦", "rtl" => false ],
        "af_ZA" => [ "label" => "Afrikaans (South Africa)", "native_name" => "Afrikaans Suid-Afrika", "flag" => "🇿🇦", "rtl" => false ],
        "ak" => [ "label" => "Akan", "native_name" => "Akan", "flag" => "🇬🇭", "rtl" => false ],
        "ak_GH" => [ "label" => "Akan (Ghana)", "native_name" => "Akan (Ghana)", "flag" => "🇬🇭", "rtl" => false ],
        "am" => [ "label" => "Amharic", "native_name" => "አማርኛ (AmarəÑña)", "flag" => "🇪🇹", "rtl" => false ],
        "am_ET" => [ "label" => "Amharic (Ethiopia)", "native_name" => "አማርኛ (AmarəÑña)", "flag" => "🇪🇹", "rtl" => false ],
        "ar" => [ "label" => "Arabic", "native_name" => "العربية", "flag" => "🇦🇪", "rtl" => true ],
        "ar_AE" => [ "label" => "Arabic (United Arab Emirates)", "native_name" => "العربية‎ / Al-ʻArabiyyah, ʻArabī الإمارات العربية المتحدة", "flag" => "🇦🇪", "rtl" => true ],
        "ar_BH" => [ "label" => "Arabic (Bahrain)", "native_name" => "العربية البحرانية", "flag" => "🇧🇭", "rtl" => true ],
        "ar_DZ" => [ "label" => "Arabic (Algeria)", "native_name" => "دزيريةالجزائر", "flag" => "🇩🇿", "rtl" => true ],
        "ar_EG" => [ "label" => "Arabic (Egypt)", "native_name" => "مصرى", "flag" => "🇪🇬", "rtl" => true ],
        "ar_IQ" => [ "label" => "Arabic (Iraq)", "native_name" => "اللهجة العراقية", "flag" => "🇮🇶", "rtl" => true ],
        "ar_JO" => [ "label" => "Arabic (Jordan)", "native_name" => "اللهجة الأردنية", "flag" => "🇯🇴", "rtl" => true ],
        "ar_KW" => [ "label" => "Arabic (Kuwait)", "native_name" => "كويتي", "flag" => "🇰🇼", "rtl" => true ],
        "ar_LB" => [ "label" => "Arabic (Lebanon)", "native_name" => "اللهجة اللبنانية", "flag" => "🇱🇧", "rtl" => true ],
        "ar_LY" => [ "label" => "Arabic (Libya)", "native_name" => "ليبي", "flag" => "🇱🇾", "rtl" => true ],
        "ar_MA" => [ "label" => "Arabic (Morocco)", "native_name" => "الدارجة اللهجة المغربية", "flag" => "🇲🇦", "rtl" => true ],
        "ar_OM" => [ "label" => "Arabic (Oman)", "native_name" => "اللهجة العمانية", "flag" => "🇴🇲", "rtl" => true ],
        "ar_QA" => [ "label" => "Arabic (Qatar)", "native_name" => "العربية (قطر)", "flag" => "🇶🇦", "rtl" => true ],
        "ar_SA" => [ "label" => "Arabic (Saudi Arabia)", "native_name" => "شبه جزيرة 'العربية", "flag" => "🇸🇦", "rtl" => true ],
        "ar_SD" => [ "label" => "Arabic (Sudan)", "native_name" => "لهجة سودانية", "flag" => "🇸🇩", "rtl" => true ],
        "ar_SY" => [ "label" => "Arabic (Syria)", "native_name" => "شامي", "flag" => "🇸🇾", "rtl" => true ],
        "ar_TN" => [ "label" => "Arabic (Tunisia)", "native_name" => "تونسي", "flag" => "🇹🇳", "rtl" => true ],
        "ar_YE" => [ "label" => "Arabic (Yemen)", "native_name" => "لهجة يمنية", "flag" => "🇾🇪", "rtl" => true ],
        "as" => [ "label" => "Assamese", "native_name" => "অসমীয়া / Ôxômiya", "flag" => "🇮🇳", "rtl" => false ],
        "as_IN" => [ "label" => "Assamese (India)", "native_name" => "অসমীয়া / Ôxômiya (India)", "flag" => "🇮🇳", "rtl" => false ],
        "asa" => [ "label" => "Asu", "native_name" => "Kipare, Casu", "flag" => "🇹🇿", "rtl" => false ],
        "asa_TZ" => [ "label" => "Asu (Tanzania)", "native_name" => "Kipare, Casu (Tanzania)", "flag" => "🇹🇿", "rtl" => false ],
        "az" => [ "label" => "Azerbaijani", "native_name" => "AzəRbaycan Dili", "flag" => "🇦🇿", "rtl" => true ],
        "az_Cyrl" => [ "label" => "Azerbaijani (Cyrillic)", "native_name" => "Азәрбајҹан Дили (Kiril)", "flag" => "🇷🇺", "rtl" => false ],
        "az_Cyrl_AZ" => [ "label" => "Azerbaijani (Cyrillic, Azerbaijan)", "native_name" => "Азәрбајҹан Дили (Kiril)", "flag" => "🇦🇿", "rtl" => false ],
        "az_Latn" => [ "label" => "Azerbaijani (Latin)", "native_name" => "AzəRbaycan (Latın) (Latın Dili)", "flag" => "🇦🇿", "rtl" => false ],
        "az_Latn_AZ" => [ "label" => "Azerbaijani (Latin, Azerbaijan)", "native_name" => "AzəRbaycan (Latın, AzəRbaycan) ()", "flag" => "🇦🇿", "rtl" => false ],
        "be" => [ "label" => "Belarusian", "native_name" => "Беларуская Мова", "flag" => "🇧🇾", "rtl" => false ],
        "be_BY" => [ "label" => "Belarusian (Belarus)", "native_name" => "Беларуская (Беларусь) (Беларус)", "flag" => "🇧🇾", "rtl" => false ],
        "bem" => [ "label" => "Bemba", "native_name" => "Βemba", "flag" => "🇿🇲", "rtl" => false ],
        "bem_ZM" => [ "label" => "Bemba (Zambia)", "native_name" => "Βemba (Zambia)", "flag" => "🇿🇲", "rtl" => false ],
        "bez" => [ "label" => "Bena", "native_name" => "Ekibena", "flag" => "🇹🇿", "rtl" => false ],
        "bez_TZ" => [ "label" => "Bena (Tanzania)", "native_name" => "Ekibena (Tanzania)", "flag" => "🇹🇿", "rtl" => false ],
        "bg" => [ "label" => "Bulgarian", "native_name" => "Български", "flag" => "🇧🇬", "rtl" => false ],
        "bg_BG" => [ "label" => "Bulgarian (Bulgaria)", "native_name" => "Български (България)", "flag" => "🇧🇬", "rtl" => false ],
        "bm" => [ "label" => "Bambara", "native_name" => "Bamanankan", "flag" => "🇲🇱", "rtl" => false ],
        "bm_ML" => [ "label" => "Bambara (Mali)", "native_name" => "Bamanankan (Mali)", "flag" => "🇲🇱", "rtl" => false ],
        "bn" => [ "label" => "Bengali", "native_name" => "বাংলা, Bangla", "flag" => "🇧🇩", "rtl" => false ],
        "bn_BD" => [ "label" => "Bengali (Bangladesh)", "native_name" => "বাংলা, Bangla (বাংলাদেশ)", "flag" => "🇧🇩", "rtl" => false ],
        "bn_IN" => [ "label" => "Bengali (India)", "native_name" => "বাংলা Bānlā (ভারত)", "flag" => "🇮🇳", "rtl" => false ],
        "bo" => [ "label" => "Tibetan", "native_name" => "བོད་སྐད་", "flag" => "🏳️", "rtl" => false ],
        "bo_CN" => [ "label" => "Tibetan (China)", "native_name" => "བོད་སྐད (China)", "flag" => "🇨🇳", "rtl" => false ],
        "bo_IN" => [ "label" => "Tibetan (India)", "native_name" => "བོད་སྐད་ (India)", "flag" => "🇮🇳", "rtl" => false ],
        "bs" => [ "label" => "Bosnian", "native_name" => "Bosanski", "flag" => "🇧🇦", "rtl" => false ],
        "bs_BA" => [ "label" => "Bosnian (Bosnia and Herzegovina)", "native_name" => "Bosanski (Bosna I Hercegovina)", "flag" => "🇧🇦", "rtl" => false ],
        "ca" => [ "label" => "Catalan", "native_name" => "Català", "flag" => "🇪🇸", "rtl" => false ],
        "ca_ES" => [ "label" => "Catalan (Spain)", "native_name" => "Català (Espanyola)", "flag" => "🇪🇸", "rtl" => false ],
        "cgg" => [ "label" => "Chiga", "native_name" => "Orukiga", "flag" => "🇺🇬", "rtl" => false ],
        "cgg_UG" => [ "label" => "Chiga (Uganda)", "native_name" => "Orukiga (Uganda)", "flag" => "🇺🇬", "rtl" => false ],
        "chr" => [ "label" => "Cherokee", "native_name" => "ᏣᎳᎩ ᎦᏬᏂᎯᏍᏗ", "flag" => "🇺🇸", "rtl" => false ],
        "chr_US" => [ "label" => "Cherokee (United States)", "native_name" => "ᏣᎳᎩ ᎦᏬᏂᎯᏍᏗ (United States)", "flag" => "🇺🇸", "rtl" => false ],
        "cs" => [ "label" => "Czech", "native_name" => "Český Jazyk", "flag" => "🇨🇿", "rtl" => false ],
        "cs_CZ" => [ "label" => "Czech (Czech Republic)", "native_name" => "Čeština (Česká Republika)", "flag" => "🇨🇿", "rtl" => false ],
        "cy" => [ "label" => "Welsh", "native_name" => "Gymraeg", "flag" => "🏴󠁧󠁢󠁥󠁮󠁧󠁿", "rtl" => false ],
        "cy_GB" => [ "label" => "Welsh (United Kingdom)", "native_name" => "Gymraeg (Y Deyrnas Unedig)", "flag" => "🇬🇧", "rtl" => false ],
        "da" => [ "label" => "Danish", "native_name" => "Dansk", "flag" => "🇩🇰", "rtl" => false ],
        "da_DK" => [ "label" => "Danish (Denmark)", "native_name" => "Dansk (Danmark)", "flag" => "🇩🇰", "rtl" => false ],
        "dav" => [ "label" => "Taita", "native_name" => "Taita", "flag" => "🇰🇪", "rtl" => false ],
        "dav_KE" => [ "label" => "Taita (Kenya)", "native_name" => "Taita (Kenya)", "flag" => "🇰🇪", "rtl" => false ],
        "de" => [ "label" => "German", "native_name" => "Deutsch", "flag" => "🇩🇪", "rtl" => false ],
        "de_AT" => [ "label" => "German (Austria)", "native_name" => "Österreichisches (Österreich)", "flag" => "🇦🇹", "rtl" => false ],
        "de_BE" => [ "label" => "German (Belgium)", "native_name" => "Deutschsprachige (Belgien)", "flag" => "🇧🇪", "rtl" => false ],
        "de_CH" => [ "label" => "German (Switzerland)", "native_name" => "Schwiizerdütsch (Schweiz)", "flag" => "🇨🇭", "rtl" => false ],
        "de_DE" => [ "label" => "German (Germany)", "native_name" => "Deutsch (Deutschland)", "flag" => "🇩🇪", "rtl" => false ],
        "de_LI" => [ "label" => "German (Liechtenstein)", "native_name" => "Alemannisch (Liechtenstein)", "flag" => "🇱🇮", "rtl" => false ],
        "de_LU" => [ "label" => "German (Luxembourg)", "native_name" => "Lëtzebuergesch (Luxemburg)", "flag" => "🇱🇺", "rtl" => false ],
        "ebu" => [ "label" => "Embu", "native_name" => "Kiembu", "flag" => "🇰🇪", "rtl" => false ],
        "ebu_KE" => [ "label" => "Embu (Kenya)", "native_name" => "Kiembu (Kenya)", "flag" => "🇰🇪", "rtl" => false ],
        "ee" => [ "label" => "Ewe", "native_name" => "EʋEgbe", "flag" => "🇹🇬", "rtl" => false ],
        "ee_GH" => [ "label" => "Ewe (Ghana)", "native_name" => "EʋEgbe (Ghana)", "flag" => "🇬🇭", "rtl" => false ],
        "ee_TG" => [ "label" => "Ewe (Togo)", "native_name" => "EʋEgbe (Togo)", "flag" => "🇹🇬", "rtl" => false ],
        "el" => [ "label" => "Greek", "native_name" => "Ελληνικά", "flag" => "🇬🇷", "rtl" => false ],
        "el_CY" => [ "label" => "Greek (Cyprus)", "native_name" => "Ελληνοκύπριοι (Κύπρος)", "flag" => "🇨🇾", "rtl" => false ],
        "el_GR" => [ "label" => "Greek (Greece)", "native_name" => "Ελληνικά (Ελλάδα) (Ελλάδα)", "flag" => "🇬🇷", "rtl" => false ],
        "en" => [ "label" => "English", "native_name" => "English", "flag" => "🇺🇸", "rtl" => false ],
        "en_AS" => [ "label" => "English (American Samoa)", "native_name" => "English (American Samoa)", "flag" => "🇦🇸", "rtl" => false ],
        "en_AU" => [ "label" => "English (Australia)", "native_name" => "English (Australia)", "flag" => "🇦🇺", "rtl" => false ],
        "en_BE" => [ "label" => "English (Belgium)", "native_name" => "English (Belgium)", "flag" => "🇧🇪", "rtl" => false ],
        "en_BW" => [ "label" => "English (Botswana)", "native_name" => "English (Botswana)", "flag" => "🇧🇼", "rtl" => false ],
        "en_BZ" => [ "label" => "English (Belize)", "native_name" => "English (Belize)", "flag" => "🇧🇿", "rtl" => false ],
        "en_CA" => [ "label" => "English (Canada)", "native_name" => "English (Canada)", "flag" => "🇨🇦", "rtl" => false ],
        "en_GB" => [ "label" => "English (United Kingdom)", "native_name" => "English (United Kingdom)", "flag" => "🇬🇧", "rtl" => false ],
        "en_GU" => [ "label" => "English (Guam)", "native_name" => "English (Guam)", "flag" => "🇬🇺", "rtl" => false ],
        "en_HK" => [ "label" => "English (Hong Kong SAR China)", "native_name" => "English (Hong Kong Sar China)", "flag" => "🇭🇰", "rtl" => false ],
        "en_IE" => [ "label" => "English (Ireland)", "native_name" => "English (Ireland)", "flag" => "🇮🇪", "rtl" => false ],
        "en_IL" => [ "label" => "English (Israel)", "native_name" => "English (Israel)", "flag" => "🇮🇱", "rtl" => false ],
        "en_IN" => [ "label" => "English (India)", "native_name" => "English (India)", "flag" => "🇮🇳", "rtl" => false ],
        "en_JM" => [ "label" => "English (Jamaica)", "native_name" => "English (Jamaica)", "flag" => "🇯🇲", "rtl" => false ],
        "en_MH" => [ "label" => "English (Marshall Islands)", "native_name" => "English (Marshall Islands)", "flag" => "🇲🇭", "rtl" => false ],
        "en_MP" => [ "label" => "English (Northern Mariana Islands)", "native_name" => "English (Northern Mariana Islands)", "flag" => "🇲🇵", "rtl" => false ],
        "en_MT" => [ "label" => "English (Malta)", "native_name" => "English (Malta)", "flag" => "🇲🇹", "rtl" => false ],
        "en_MU" => [ "label" => "English (Mauritius)", "native_name" => "English (Mauritius)", "flag" => "🇲🇺", "rtl" => false ],
        "en_NA" => [ "label" => "English (Namibia)", "native_name" => "English (Namibia)", "flag" => "🇳🇦", "rtl" => false ],
        "en_NZ" => [ "label" => "English (New Zealand)", "native_name" => "English (New Zealand)", "flag" => "🇳🇿", "rtl" => false ],
        "en_PH" => [ "label" => "English (Philippines)", "native_name" => "English (Philippines)", "flag" => "🇵🇭", "rtl" => false ],
        "en_PK" => [ "label" => "English (Pakistan)", "native_name" => "English (Pakistan)", "flag" => "🇵🇰", "rtl" => false ],
        "en_SG" => [ "label" => "English (Singapore)", "native_name" => "English (Singapore)", "flag" => "🇸🇬", "rtl" => false ],
        "en_TT" => [ "label" => "English (Trinidad and Tobago)", "native_name" => "English (Trinidad And Tobago)", "flag" => "🇹🇹", "rtl" => false ],
        "en_UM" => [ "label" => "English (U.S. Minor Outlying Islands)", "native_name" => "English (U.S. Minor Outlying Islands)", "flag" => "🇺🇸", "rtl" => false ],
        "en_US" => [ "label" => "English (United States)", "native_name" => "English (United States)", "flag" => "🇺🇸", "rtl" => false ],
        "en_VI" => [ "label" => "English (U.S. Virgin Islands)", "native_name" => "English (U.S. Virgin Islands)", "flag" => "🇻🇮", "rtl" => false ],
        "en_ZA" => [ "label" => "English (South Africa)", "native_name" => "English (South Africa)", "flag" => "🇿🇦", "rtl" => false ],
        "en_ZW" => [ "label" => "English (Zimbabwe)", "native_name" => "English (Zimbabwe)", "flag" => "🇿🇼", "rtl" => false ],
        "eo" => [ "label" => "Esperanto", "native_name" => "Esperanto", "flag" => "🇪🇺", "rtl" => false ],
        "es" => [ "label" => "Spanish", "native_name" => "Español", "flag" => "🇪🇸", "rtl" => false ],
        "es_419" => [ "label" => "Spanish (Latin America)", "native_name" => "Español (America Latina)", "flag" => "🇨🇴", "rtl" => false ],
        "es_AR" => [ "label" => "Spanish (Argentina)", "native_name" => "Español (Argentina)", "flag" => "🇦🇷", "rtl" => false ],
        "es_BO" => [ "label" => "Spanish (Bolivia)", "native_name" => "Español (Bolivia)", "flag" => "🇧🇴", "rtl" => false ],
        "es_CL" => [ "label" => "Spanish (Chile)", "native_name" => "Español (Chile)", "flag" => "🇨🇱", "rtl" => false ],
        "es_CO" => [ "label" => "Spanish (Colombia)", "native_name" => "Español (Colombia)", "flag" => "🇨🇴", "rtl" => false ],
        "es_CR" => [ "label" => "Spanish (Costa Rica)", "native_name" => "Español (Costa Rica)", "flag" => "🇨🇷", "rtl" => false ],
        "es_DO" => [ "label" => "Spanish (Dominican Republic)", "native_name" => "Español (República Dominicana)", "flag" => "🇩🇴", "rtl" => false ],
        "es_EC" => [ "label" => "Spanish (Ecuador)", "native_name" => "Español (Ecuador)", "flag" => "🇪🇨", "rtl" => false ],
        "es_ES" => [ "label" => "Spanish (Spain)", "native_name" => "Español (España)", "flag" => "🇪🇸", "rtl" => false ],
        "es_GQ" => [ "label" => "Spanish (Equatorial Guinea)", "native_name" => "Español (Guinea Ecuatorial)", "flag" => "🇬🇶", "rtl" => false ],
        "es_GT" => [ "label" => "Spanish (Guatemala)", "native_name" => "Español (Guatemala)", "flag" => "🇬🇹", "rtl" => false ],
        "es_HN" => [ "label" => "Spanish (Honduras)", "native_name" => "Español (Honduras)", "flag" => "🇭🇳", "rtl" => false ],
        "es_MX" => [ "label" => "Spanish (Mexico)", "native_name" => "Español (México)", "flag" => "🇲🇽", "rtl" => false ],
        "es_NI" => [ "label" => "Spanish (Nicaragua)", "native_name" => "Español (Nicaragua)", "flag" => "🇳🇮", "rtl" => false ],
        "es_PA" => [ "label" => "Spanish (Panama)", "native_name" => "Español (Panamá)", "flag" => "🇵🇦", "rtl" => false ],
        "es_PE" => [ "label" => "Spanish (Peru)", "native_name" => "Español (Perú)", "flag" => "🇵🇪", "rtl" => false ],
        "es_PR" => [ "label" => "Spanish (Puerto Rico)", "native_name" => "Español (Puerto Rico)", "flag" => "🇵🇷", "rtl" => false ],
        "es_PY" => [ "label" => "Spanish (Paraguay)", "native_name" => "Español (Paraguay)", "flag" => "🇵🇾", "rtl" => false ],
        "es_SV" => [ "label" => "Spanish (El Salvador)", "native_name" => "Español (El Salvador)", "flag" => "🇸🇻", "rtl" => false ],
        "es_US" => [ "label" => "Spanish (United States)", "native_name" => "Español (Estados Unidos)", "flag" => "🇺🇸", "rtl" => false ],
        "es_UY" => [ "label" => "Spanish (Uruguay)", "native_name" => "Español (Uruguay)", "flag" => "🇺🇾", "rtl" => false ],
        "es_VE" => [ "label" => "Spanish (Venezuela)", "native_name" => "Español (Venezuela)", "flag" => "🇻🇪", "rtl" => false ],
        "et" => [ "label" => "Estonian", "native_name" => "Eesti Keel", "flag" => "🇪🇪", "rtl" => false ],
        "et_EE" => [ "label" => "Estonian (Estonia)", "native_name" => "Eesti Keel (Eesti)", "flag" => "🇪🇪", "rtl" => false ],
        "eu" => [ "label" => "Basque", "native_name" => "Euskara", "flag" => "🏳️", "rtl" => false ],
        "eu_ES" => [ "label" => "Basque (Spain)", "native_name" => "Euskara (Jaio)", "flag" => "🏳️", "rtl" => false ],
        "fa" => [ "label" => "Persian", "native_name" => "فارسی (Fārsi)", "flag" => "🇮🇷", "rtl" => true ],
        "fa_AF" => [ "label" => "Persian (Afghanistan)", "native_name" => "فارسی دری (افغانستان)", "flag" => "🇦🇫", "rtl" => true ],
        "fa_IR" => [ "label" => "Persian (Iran)", "native_name" => "فارسی (Fārsi) (ایران)", "flag" => "🇮🇷", "rtl" => true ],
        "ff" => [ "label" => "Fulah", "native_name" => "الفولاني", "flag" => "🇸🇳", "rtl" => true ],
        "ff_SN" => [ "label" => "Fulah (Senegal)", "native_name" => "𞤆𞤵𞥄𞤼𞤢", "flag" => "🇸🇳", "rtl" => true ],
        "fi" => [ "label" => "Finnish", "native_name" => "Suomen Kieli", "flag" => "🇫🇮", "rtl" => false ],
        "fi_FI" => [ "label" => "Finnish (Finland)", "native_name" => "Suomen Kieli (Suomi)", "flag" => "🇫🇮", "rtl" => false ],
        "fil" => [ "label" => "Filipino", "native_name" => "Wikang Filipino", "flag" => "🇵🇭", "rtl" => false ],
        "fil_PH" => [ "label" => "Filipino (Philippines)", "native_name" => "Wikang Filipino (Pilipinas)", "flag" => "🇵🇭", "rtl" => false ],
        "fo" => [ "label" => "Faroese", "native_name" => "Føroyskt Mál", "flag" => "🇫🇴", "rtl" => false ],
        "fo_FO" => [ "label" => "Faroese (Faroe Islands)", "native_name" => "Føroyskt Mál (Faroe Islands)", "flag" => "🇫🇴", "rtl" => false ],
        "fr" => [ "label" => "French", "native_name" => "Français", "flag" => "🇫🇷", "rtl" => false ],
        "fr_BE" => [ "label" => "French (Belgium)", "native_name" => "Français (Belgique)", "flag" => "🇧🇪", "rtl" => false ],
        "fr_BF" => [ "label" => "French (Burkina Faso)", "native_name" => "Français (Burkina Faso)", "flag" => "🇧🇫", "rtl" => false ],
        "fr_BI" => [ "label" => "French (Burundi)", "native_name" => "Français (Burundi)", "flag" => "🇧🇮", "rtl" => false ],
        "fr_BJ" => [ "label" => "French (Benin)", "native_name" => "Français (Bénin)", "flag" => "🇧🇯", "rtl" => false ],
        "fr_BL" => [ "label" => "French (Saint Barthélemy)", "native_name" => "Français (Saint Barthélemy)", "flag" => "🇧🇱", "rtl" => false ],
        "fr_CA" => [ "label" => "French (Canada)", "native_name" => "Français (Canada)", "flag" => "🇨🇦", "rtl" => false ],
        "fr_CD" => [ "label" => "French (Congo - Kinshasa)", "native_name" => "Français (Congo - Kinshasa)", "flag" => "🇨🇩", "rtl" => false ],
        "fr_CF" => [ "label" => "French (Central African Republic)", "native_name" => "Français (République Centrafricaine)", "flag" => "🇨🇫", "rtl" => false ],
        "fr_CG" => [ "label" => "French (Congo - Brazzaville)", "native_name" => "Français (Congo - Brazzaville)", "flag" => "🇨🇬", "rtl" => false ],
        "fr_CH" => [ "label" => "French (Switzerland)", "native_name" => "Français (Suisse)", "flag" => "🇨🇭", "rtl" => false ],
        "fr_CI" => [ "label" => "French (Côte d'Ivoire)", "native_name" => "Français (Côte D'Ivoire)", "flag" => "🇨🇮", "rtl" => false ],
        "fr_CM" => [ "label" => "French (Cameroon)", "native_name" => "Français (Cameroun)", "flag" => "🇨🇲", "rtl" => false ],
        "fr_DJ" => [ "label" => "French (Djibouti)", "native_name" => "Français (Djibouti)", "flag" => "🇩🇯", "rtl" => false ],
        "fr_FR" => [ "label" => "French (France)", "native_name" => "Français (France)", "flag" => "🇫🇷", "rtl" => false ],
        "fr_GA" => [ "label" => "French (Gabon)", "native_name" => "Français (Gabon)", "flag" => "🇬🇦", "rtl" => false ],
        "fr_GN" => [ "label" => "French (Guinea)", "native_name" => "Français (Guinée)", "flag" => "🇬🇳", "rtl" => false ],
        "fr_GP" => [ "label" => "French (Guadeloupe)", "native_name" => "Français (Guadeloup)", "flag" => "🇬🇵", "rtl" => false ],
        "fr_GQ" => [ "label" => "French (Equatorial Guinea)", "native_name" => "Français (Guinée Équatoriale)", "flag" => "🇬🇶", "rtl" => false ],
        "fr_KM" => [ "label" => "French (Comoros)", "native_name" => "Français (Comores)", "flag" => "🇰🇲", "rtl" => false ],
        "fr_LU" => [ "label" => "French (Luxembourg)", "native_name" => "Français (Luxembourg)", "flag" => "🇱🇺", "rtl" => false ],
        "fr_MC" => [ "label" => "French (Monaco)", "native_name" => "Français (Monaco)", "flag" => "🇲🇨", "rtl" => false ],
        "fr_MF" => [ "label" => "French (Saint Martin)", "native_name" => "Français (Saint Martin)", "flag" => "🇲🇫", "rtl" => false ],
        "fr_MG" => [ "label" => "French (Madagascar)", "native_name" => "Français (Madagascar)", "flag" => "🇲🇬", "rtl" => false ],
        "fr_ML" => [ "label" => "French (Mali)", "native_name" => "Français (Mali)", "flag" => "🇲🇱", "rtl" => false ],
        "fr_MQ" => [ "label" => "French (Martinique)", "native_name" => "Français (Martinique)", "flag" => "🇲🇶", "rtl" => false ],
        "fr_NE" => [ "label" => "French (Niger)", "native_name" => "Français (Niger)", "flag" => "🇳🇪", "rtl" => false ],
        "fr_RE" => [ "label" => "French (Réunion)", "native_name" => "Français (Réunion)", "flag" => "🇷🇪", "rtl" => false ],
        "fr_RW" => [ "label" => "French (Rwanda)", "native_name" => "Français (Rwanda)", "flag" => "🇷🇼", "rtl" => false ],
        "fr_SN" => [ "label" => "French (Senegal)", "native_name" => "Français (Sénégal)", "flag" => "🇸🇳", "rtl" => false ],
        "fr_TD" => [ "label" => "French (Chad)", "native_name" => "Français (Tchad)", "flag" => "🇹🇩", "rtl" => false ],
        "fr_TG" => [ "label" => "French (Togo)", "native_name" => "Français (Aller)", "flag" => "🇹🇬", "rtl" => false ],
        "ga" => [ "label" => "Irish", "native_name" => "Gaeilge", "flag" => "🇮🇪", "rtl" => false ],
        "ga_IE" => [ "label" => "Irish (Ireland)", "native_name" => "Gaeilge (Éireann)", "flag" => "🇮🇪", "rtl" => false ],
        "gl" => [ "label" => "Galician", "native_name" => "Galego", "flag" => "🇪🇸", "rtl" => false ],
        "gl_ES" => [ "label" => "Galician (Spain)", "native_name" => "Galego (España)", "flag" => "🇪🇸", "rtl" => false ],
        "gsw" => [ "label" => "Swiss German", "native_name" => "Schwiizerdütsch", "flag" => "🇨🇭", "rtl" => false ],
        "gsw_CH" => [ "label" => "Swiss German (Switzerland)", "native_name" => "Schwiizerdütsch", "flag" => "🇨🇭", "rtl" => false ],
        "gu" => [ "label" => "Gujarati", "native_name" => "ગુજરાતી", "flag" => "🇮🇳", "rtl" => false ],
        "gu_IN" => [ "label" => "Gujarati (India)", "native_name" => "ગુજરાતી (ભારત)", "flag" => "🇮🇳", "rtl" => false ],
        "guz" => [ "label" => "Gusii", "native_name" => "Ekegusii", "flag" => "🇰🇪", "rtl" => false ],
        "guz_KE" => [ "label" => "Gusii (Kenya)", "native_name" => "Ekegusii (Kenya)", "flag" => "🇰🇪", "rtl" => false ],
        "gv" => [ "label" => "Manx", "native_name" => "Gaelg, Gailck", "flag" => "🇮🇲", "rtl" => false ],
        "gv_GB" => [ "label" => "Manx (United Kingdom)", "native_name" => "Gaelg, Gailck (United Kingdom)", "flag" => "🇬🇧", "rtl" => false ],
        "ha" => [ "label" => "Hausa", "native_name" => "هَرْشَن هَوْسَ", "flag" => "🇳🇬", "rtl" => true ],
        "ha_Latn" => [ "label" => "Hausa (Latin)", "native_name" => "Halshen Hausa (Na Latin)", "flag" => "🇳🇬", "rtl" => false ],
        "ha_Latn_GH" => [ "label" => "Hausa (Latin, Ghana)", "native_name" => "Halshen Hausa (Latin Ghana)", "flag" => "🇬🇭", "rtl" => false ],
        "ha_Latn_NE" => [ "label" => "Hausa (Latin, Niger)", "native_name" => "Halshen Hausa (Latin Niger)", "flag" => "🇳🇪", "rtl" => false ],
        "ha_Latn_NG" => [ "label" => "Hausa (Latin, Nigeria)", "native_name" => "Halshen Hausa (Latin Nigeria)", "flag" => "🇳🇬", "rtl" => false ],
        "haw" => [ "label" => "Hawaiian", "native_name" => "ʻŌlelo HawaiʻI", "flag" => "🇺🇸", "rtl" => false ],
        "haw_US" => [ "label" => "Hawaiian (United States)", "native_name" => "ʻŌlelo HawaiʻI (ʻAmelika Hui Pū ʻIa)", "flag" => "🇺🇸", "rtl" => false ],
        "he" => [ "label" => "Hebrew", "native_name" => "עִבְרִית", "flag" => "🇮🇱", "rtl" => true ],
        "he_IL" => [ "label" => "Hebrew (Israel)", "native_name" => "עברית (ישראל)", "flag" => "🇮🇱", "rtl" => true ],
        "hi" => [ "label" => "Hindi", "native_name" => "हिन्दी", "flag" => "🇮🇳", "rtl" => false ],
        "hi_IN" => [ "label" => "Hindi (India)", "native_name" => "हिन्दी (भारत)", "flag" => "🇮🇳", "rtl" => false ],
        "hr" => [ "label" => "Croatian", "native_name" => "Hrvatski", "flag" => "🇭🇷", "rtl" => false ],
        "hr_HR" => [ "label" => "Croatian (Croatia)", "native_name" => "Hrvatski (Hrvatska)", "flag" => "🇭🇷", "rtl" => false ],
        "hu" => [ "label" => "Hungarian", "native_name" => "Magyar Nyelv", "flag" => "🇭🇺", "rtl" => false ],
        "hu_HU" => [ "label" => "Hungarian (Hungary)", "native_name" => "Magyar Nyelv (Magyarország)", "flag" => "🇭🇺", "rtl" => false ],
        "hy" => [ "label" => "Armenian", "native_name" => "Հայերէն/Հայերեն", "flag" => "🇦🇲", "rtl" => false ],
        "hy_AM" => [ "label" => "Armenian (Armenia)", "native_name" => "Հայերէն/Հայերեն (Հայաստան)", "flag" => "🇦🇲", "rtl" => false ],
        "id" => [ "label" => "Indonesian", "native_name" => "Bahasa Indonesia", "flag" => "🇮🇩", "rtl" => false ],
        "id_ID" => [ "label" => "Indonesian (Indonesia)", "native_name" => "Bahasa Indonesia (Indonesia)", "flag" => "🇮🇩", "rtl" => false ],
        "ig" => [ "label" => "Igbo", "native_name" => "Ásụ̀Sụ́ Ìgbò", "flag" => "🇳🇬", "rtl" => false ],
        "ig_NG" => [ "label" => "Igbo (Nigeria)", "native_name" => "Ásụ̀Sụ́ Ìgbò (Nigeria)", "flag" => "🇳🇬", "rtl" => false ],
        "ii" => [ "label" => "Sichuan Yi", "native_name" => "ꆈꌠꉙ", "flag" => "🇨🇳", "rtl" => false ],
        "ii_CN" => [ "label" => "Sichuan Yi (China)", "native_name" => "ꆈꌠꉙ (China)", "flag" => "🇨🇳", "rtl" => false ],
        "is" => [ "label" => "Icelandic", "native_name" => "Íslenska", "flag" => "🇮🇸", "rtl" => false ],
        "is_IS" => [ "label" => "Icelandic (Iceland)", "native_name" => "Íslenska (Ísland)", "flag" => "🇮🇸", "rtl" => false ],
        "it" => [ "label" => "Italian", "native_name" => "Italiano", "flag" => "🇮🇹", "rtl" => false ],
        "it_CH" => [ "label" => "Italian (Switzerland)", "native_name" => "Italiano (Svizzera)", "flag" => "🇨🇭", "rtl" => false ],
        "it_IT" => [ "label" => "Italian (Italy)", "native_name" => "Italiano (Italia)", "flag" => "🇮🇹", "rtl" => false ],
        "ja" => [ "label" => "Japanese", "native_name" => "日本語", "flag" => "🇯🇵", "rtl" => false ],
        "ja_JP" => [ "label" => "Japanese (Japan)", "native_name" => "日本語 (日本)", "flag" => "🇯🇵", "rtl" => false ],
        "jmc" => [ "label" => "Machame", "native_name" => "West Chaga", "flag" => "🇹🇿", "rtl" => false ],
        "jmc_TZ" => [ "label" => "Machame (Tanzania)", "native_name" => "West Chaga (Tanzania)", "flag" => "🇹🇿", "rtl" => false ],
        "ka" => [ "label" => "Georgian", "native_name" => "ᲥᲐᲠᲗᲣᲚᲘ ᲔᲜᲐ", "flag" => "🇬🇪", "rtl" => false ],
        "ka_GE" => [ "label" => "Georgian (Georgia)", "native_name" => "ᲥᲐᲠᲗᲣᲚᲘ ᲔᲜᲐ (ᲡᲐᲥᲐᲠᲗᲕᲔᲚᲝ)", "flag" => "🇬🇪", "rtl" => false ],
        "kab" => [ "label" => "Kabyle", "native_name" => "ⵜⴰⵇⴱⴰⵢⵍⵉⵜ", "flag" => "🇩🇿", "rtl" => false ],
        "kab_DZ" => [ "label" => "Kabyle (Algeria)", "native_name" => "ⵜⴰⵇⴱⴰⵢⵍⵉⵜ (Algeria)", "flag" => "🇩🇿", "rtl" => false ],
        "kam" => [ "label" => "Kamba", "native_name" => "Kikamba", "flag" => "🇰🇪", "rtl" => false ],
        "kam_KE" => [ "label" => "Kamba (Kenya)", "native_name" => "Kikamba (Kenya)", "flag" => "🇰🇪", "rtl" => false ],
        "kde" => [ "label" => "Makonde", "native_name" => "Chi(Ni)Makonde", "flag" => "🇹🇿", "rtl" => false ],
        "kde_TZ" => [ "label" => "Makonde (Tanzania)", "native_name" => "Chi(Ni)Makonde (Tanzania)", "flag" => "🇹🇿", "rtl" => false ],
        "kea" => [ "label" => "Kabuverdianu", "native_name" => "Kriolu, Kriol", "flag" => "🇨🇻", "rtl" => false ],
        "kea_CV" => [ "label" => "Kabuverdianu (Cape Verde)", "native_name" => "Kriolu, Kriol (Cape Verde)", "flag" => "🇨🇻", "rtl" => false ],
        "khq" => [ "label" => "Koyra Chiini", "native_name" => "Koyra Chiini", "flag" => "🇲🇱", "rtl" => false ],
        "khq_ML" => [ "label" => "Koyra Chiini (Mali)", "native_name" => "Koyra Chiini (Mali)", "flag" => "🇲🇱", "rtl" => false ],
        "ki" => [ "label" => "Kikuyu", "native_name" => "Gĩkũyũ", "flag" => "🇰🇪", "rtl" => false ],
        "ki_KE" => [ "label" => "Kikuyu (Kenya)", "native_name" => "Gĩkũyũ (Kenya)", "flag" => "🇰🇪", "rtl" => false ],
        "kk" => [ "label" => "Kazakh", "native_name" => "قازاقشا Or قازاق ٴتىلى", "flag" => "🇰🇿", "rtl" => true ],
        "kk_Cyrl" => [ "label" => "Kazakh (Cyrillic)", "native_name" => "Қазақша Or Қазақ Тілі (Кириллица)", "flag" => "🇷🇺", "rtl" => false ],
        "kk_Cyrl_KZ" => [ "label" => "Kazakh (Cyrillic, Kazakhstan)", "native_name" => "Қазақша Or Қазақ Тілі (Кириллица)", "flag" => "🇰🇿", "rtl" => false ],
        "kl" => [ "label" => "Kalaallisut", "native_name" => "Kalaallisut", "flag" => "🇬🇱", "rtl" => false ],
        "kl_GL" => [ "label" => "Kalaallisut (Greenland)", "native_name" => "Kalaallisut (Greenland)", "flag" => "🇬🇱", "rtl" => false ],
        "kln" => [ "label" => "Kalenjin", "native_name" => "Kalenjin", "flag" => "🇰🇪", "rtl" => false ],
        "kln_KE" => [ "label" => "Kalenjin (Kenya)", "native_name" => "Kalenjin (Kenya)", "flag" => "🇰🇪", "rtl" => false ],
        "km" => [ "label" => "Khmer", "native_name" => "ភាសាខ្មែរ", "flag" => "🇰🇭", "rtl" => false ],
        "km_KH" => [ "label" => "Khmer (Cambodia)", "native_name" => "ភាសាខ្មែរ (ខេសកម្ពុជា)", "flag" => "🇰🇭", "rtl" => false ],
        "kn" => [ "label" => "Kannada", "native_name" => "ಕನ್ನಡ", "flag" => "🇮🇳", "rtl" => false ],
        "kn_IN" => [ "label" => "Kannada (India)", "native_name" => "ಕನ್ನಡ (ಭಾರತ)", "flag" => "🇮🇳", "rtl" => false ],
        "ko" => [ "label" => "Korean", "native_name" => "한국어", "flag" => "🇰🇷", "rtl" => false ],
        "ko_KR" => [ "label" => "Korean (South Korea)", "native_name" => "한국어 (대한민국)", "flag" => "🇰🇷", "rtl" => false ],
        "kok" => [ "label" => "Konkani", "native_name" => "कोंकणी", "flag" => "🇮🇳", "rtl" => false ],
        "kok_IN" => [ "label" => "Konkani (India)", "native_name" => "कोंकणी (India)", "flag" => "🇮🇳", "rtl" => false ],
        "kw" => [ "label" => "Cornish", "native_name" => "Kernewek, Kernowek", "flag" => "🇬🇧", "rtl" => false ],
        "kw_GB" => [ "label" => "Cornish (United Kingdom)", "native_name" => "Kernewek, Kernowek (United Kingdom)", "flag" => "🇬🇧", "rtl" => false ],
        "lag" => [ "label" => "Langi", "native_name" => "Lëblaŋo", "flag" => "🇺🇬", "rtl" => false ],
        "lag_TZ" => [ "label" => "Langi (Tanzania)", "native_name" => "Kilaangi (Tanzania)", "flag" => "🇹🇿", "rtl" => false ],
        "lg" => [ "label" => "Ganda", "native_name" => "Ganda", "flag" => "🇺🇬", "rtl" => false ],
        "lg_UG" => [ "label" => "Ganda (Uganda)", "native_name" => "Ganda (Uganda)", "flag" => "🇺🇬", "rtl" => false ],
        "lt" => [ "label" => "Lithuanian", "native_name" => "Lietuvių Kalba", "flag" => "🇱🇹", "rtl" => false ],
        "lt_LT" => [ "label" => "Lithuanian (Lithuania)", "native_name" => "Lietuvių Kalba (Lietuva)", "flag" => "🇱🇹", "rtl" => false ],
        "luo" => [ "label" => "Luo", "native_name" => "Lwo", "flag" => "🇰🇪", "rtl" => false ],
        "luo_KE" => [ "label" => "Luo (Kenya)", "native_name" => "Dholuo (Kenya)", "flag" => "🇰🇪", "rtl" => false ],
        "luy" => [ "label" => "Luyia", "native_name" => "Oluluhya", "flag" => "🇰🇪", "rtl" => false ],
        "luy_KE" => [ "label" => "Luyia (Kenya)", "native_name" => "Oluluhya (Kenya)", "flag" => "🇰🇪", "rtl" => false ],
        "lv" => [ "label" => "Latvian", "native_name" => "Latviešu Valoda", "flag" => "🇱🇻", "rtl" => false ],
        "lv_LV" => [ "label" => "Latvian (Latvia)", "native_name" => "Latviešu Valoda (Latvija)", "flag" => "🇱🇻", "rtl" => false ],
        "mas" => [ "label" => "Masai", "native_name" => "ƆL Maa", "flag" => "🇰🇪", "rtl" => false ],
        "mas_KE" => [ "label" => "Masai (Kenya)", "native_name" => "ƆL Maa (Kenya)", "flag" => "🇰🇪", "rtl" => false ],
        "mas_TZ" => [ "label" => "Masai (Tanzania)", "native_name" => "ƆL Maa (Tanzania)", "flag" => "🇹🇿", "rtl" => false ],
        "mer" => [ "label" => "Meru", "native_name" => "Kĩmĩĩrũ", "flag" => "🇰🇪", "rtl" => false ],
        "mer_KE" => [ "label" => "Meru (Kenya)", "native_name" => "Kĩmĩĩrũ (Kenya)", "flag" => "🇰🇪", "rtl" => false ],
        "mfe" => [ "label" => "Morisyen", "native_name" => "Kreol Morisien", "flag" => "🇲🇺", "rtl" => false ],
        "mfe_MU" => [ "label" => "Morisyen (Mauritius)", "native_name" => "Kreol Morisien (Mauritius)", "flag" => "🇲🇺", "rtl" => false ],
        "mg" => [ "label" => "Malagasy", "native_name" => "Malagasy", "flag" => "🇲🇬", "rtl" => false ],
        "mg_MG" => [ "label" => "Malagasy (Madagascar)", "native_name" => "Malagasy (Madagaskar)", "flag" => "🇲🇬", "rtl" => false ],
        "mk" => [ "label" => "Macedonian", "native_name" => "Македонски", "flag" => "🇲🇰", "rtl" => false ],
        "mk_MK" => [ "label" => "Macedonian (Macedonia)", "native_name" => "Македонски, Makedonski (Македонија)", "flag" => "🇲🇰", "rtl" => false ],
        "ml" => [ "label" => "Malayalam", "native_name" => "മലയാളം", "flag" => "🇮🇳", "rtl" => false ],
        "ml_IN" => [ "label" => "Malayalam (India)", "native_name" => "മലയാളം (ഇന്ത്യ)", "flag" => "🇮🇳", "rtl" => false ],
        "mr" => [ "label" => "Marathi", "native_name" => "मराठी", "flag" => "🇮🇳", "rtl" => false ],
        "mr_IN" => [ "label" => "Marathi (India)", "native_name" => "मराठी (भारत)", "flag" => "🇮🇳", "rtl" => false ],
        "ms" => [ "label" => "Malay", "native_name" => "Bahasa Melayu", "flag" => "🇲🇾", "rtl" => false ],
        "ms_BN" => [ "label" => "Malay (Brunei)", "native_name" => "Bahasa Melayu Brunei", "flag" => "🇧🇳", "rtl" => false ],
        "ms_MY" => [ "label" => "Malay (Malaysia)", "native_name" => "Bahasa Melayu (Malaysia)", "flag" => "🇲🇾", "rtl" => false ],
        "mt" => [ "label" => "Maltese", "native_name" => "Malti", "flag" => "🇲🇹", "rtl" => false ],
        "mt_MT" => [ "label" => "Maltese (Malta)", "native_name" => "Malti (Malta)", "flag" => "🇲🇹", "rtl" => false ],
        "my" => [ "label" => "Burmese", "native_name" => "မြန်မာစာ", "flag" => "🇲🇲", "rtl" => false ],
        "my_MM" => [ "label" => "Burmese (Myanmar [Burma])", "native_name" => "မြန်မာစာ (မြန်မာ [Burma])", "flag" => "🇲🇲", "rtl" => false ],
        "naq" => [ "label" => "Nama", "native_name" => "Khoekhoegowab", "flag" => "🇳🇦", "rtl" => false ],
        "naq_NA" => [ "label" => "Nama (Namibia)", "native_name" => "Khoekhoegowab (Nambia)", "flag" => "🇳🇦", "rtl" => false ],
        "nb" => [ "label" => "Norwegian Bokmål", "native_name" => "Bokmål", "flag" => "🇳🇴", "rtl" => false ],
        "nb_NO" => [ "label" => "Norwegian Bokmål (Norway)", "native_name" => "Bokmål (Norge)", "flag" => "🇳🇴", "rtl" => false ],
        "nd" => [ "label" => "North Ndebele", "native_name" => "Isindebele Sasenyakatho", "flag" => "🇿🇼", "rtl" => false ],
        "nd_ZW" => [ "label" => "North Ndebele (Zimbabwe)", "native_name" => "Isindebele Sasenyakatho (Zimbawe)", "flag" => "🇿🇼", "rtl" => false ],
        "ne" => [ "label" => "Nepali", "native_name" => "नेपाली", "flag" => "🇳🇵", "rtl" => false ],
        "ne_IN" => [ "label" => "Nepali (India)", "native_name" => "नेपाली (भारत)", "flag" => "🇮🇳", "rtl" => false ],
        "ne_NP" => [ "label" => "Nepali (Nepal)", "native_name" => "नेपाली (नेपाल)", "flag" => "🇳🇵", "rtl" => false ],
        "nl" => [ "label" => "Dutch", "native_name" => "Nederlands", "flag" => "🇧🇶", "rtl" => false ],
        "nl_BE" => [ "label" => "Dutch (Belgium)", "native_name" => "Nederlands (België)", "flag" => "🇧🇪", "rtl" => false ],
        "nl_NL" => [ "label" => "Dutch (Netherlands)", "native_name" => "Nederlands (Nederland)", "flag" => "🇧🇶", "rtl" => false ],
        "nn" => [ "label" => "Norwegian Nynorsk", "native_name" => "Norsk", "flag" => "🇳🇴", "rtl" => false ],
        "nn_NO" => [ "label" => "Norwegian Nynorsk (Norway)", "native_name" => "Norsk (Norway)", "flag" => "🇳🇴", "rtl" => false ],
        "nyn" => [ "label" => "Nyankole", "native_name" => "Orunyankore", "flag" => "🇺🇬", "rtl" => false ],
        "nyn_UG" => [ "label" => "Nyankole (Uganda)", "native_name" => "Orunyankore (Uganda)", "flag" => "🇺🇬", "rtl" => false ],
        "om" => [ "label" => "Oromo", "native_name" => "Afaan Oromoo", "flag" => "🇪🇹", "rtl" => false ],
        "om_ET" => [ "label" => "Oromo (Ethiopia)", "native_name" => "Afaan Oromoo (Ethiopia)", "flag" => "🇪🇹", "rtl" => false ],
        "om_KE" => [ "label" => "Oromo (Kenya)", "native_name" => "Afaan Oromoo (Kenya)", "flag" => "🇰🇪", "rtl" => false ],
        "or" => [ "label" => "Oriya", "native_name" => "ଓଡ଼ିଆ", "flag" => "🇮🇳", "rtl" => false ],
        "or_IN" => [ "label" => "Oriya (India)", "native_name" => "ଓଡ଼ିଆ (ଭାରତ)", "flag" => "🇮🇳", "rtl" => false ],
        "pa" => [ "label" => "Punjabi", "native_name" => "ਪੰਜਾਬੀ", "flag" => "🇮🇳", "rtl" => true ],
        "pa_Arab" => [ "label" => "Punjabi (Arabic)", "native_name" => "پن٘جابی (ਅਰਬੀ)", "flag" => "🇶🇦", "rtl" => true ],
        "pa_Arab_PK" => [ "label" => "Punjabi (Arabic, Pakistan)", "native_name" => "پن٘جابی(Arabic, Pakistan)", "flag" => "🇵🇰", "rtl" => true ],
        "pa_Guru" => [ "label" => "Punjabi (Gurmukhi)", "native_name" => "ਪੰਜਾਬੀ (ਗੁਰਮੁਖੀ)", "flag" => "🇵🇰", "rtl" => false ],
        "pa_Guru_IN" => [ "label" => "Punjabi (Gurmukhi, India)", "native_name" => "ਪੰਜਾਬੀ (Gurmukhi, India)", "flag" => "🇮🇳", "rtl" => false ],
        "pa_IN" => [ "label" => "Punjabi (India)", "native_name" => "ਪੰਜਾਬੀ (India)", "flag" => "🇮🇳", "rtl" => false ],
        "pl" => [ "label" => "Polish", "native_name" => "Polski", "flag" => "🇵🇱", "rtl" => false ],
        "pl_PL" => [ "label" => "Polish (Poland)", "native_name" => "Polski (Polska)", "flag" => "🇵🇱", "rtl" => false ],
        "ps" => [ "label" => "Pashto", "native_name" => "پښتو", "flag" => "🇦🇫", "rtl" => true ],
        "ps_AF" => [ "label" => "Pashto (Afghanistan)", "native_name" => "پښتو (افغانستان)", "flag" => "🇦🇫", "rtl" => true ],
        "pt" => [ "label" => "Portuguese", "native_name" => "Português", "flag" => "🇧🇷", "rtl" => false ],
        "pt_BR" => [ "label" => "Portuguese (Brazil)", "native_name" => "Português (Brasil)", "flag" => "🇧🇷", "rtl" => false ],
        "pt_GW" => [ "label" => "Portuguese (Guinea-Bissau)", "native_name" => "Português (Guiné-Bissau)", "flag" => "🇬🇼", "rtl" => false ],
        "pt_MZ" => [ "label" => "Portuguese (Mozambique)", "native_name" => "Português (Moçambique)", "flag" => "🇲🇿", "rtl" => false ],
        "pt_PT" => [ "label" => "Portuguese (Portugal)", "native_name" => "Português (Portugal)", "flag" => "🇵🇹", "rtl" => false ],
        "rm" => [ "label" => "Romansh", "native_name" => "Romontsch", "flag" => "🇨🇭", "rtl" => false ],
        "rm_CH" => [ "label" => "Romansh (Switzerland)", "native_name" => "Romontsch (Switzerland)", "flag" => "🇨🇭", "rtl" => false ],
        "ro" => [ "label" => "Romanian", "native_name" => "Limba Română", "flag" => "🇷🇴", "rtl" => false ],
        "ro_MD" => [ "label" => "Romanian (Moldova)", "native_name" => "Лимба Молдовеняскэ (Moldova)", "flag" => "🇲🇩", "rtl" => false ],
        "ro_RO" => [ "label" => "Romanian (Romania)", "native_name" => "Лимба Молдовенѣскъ (România)", "flag" => "🇷🇴", "rtl" => false ],
        "rof" => [ "label" => "Rombo", "native_name" => "Kirombo", "flag" => "🇹🇿", "rtl" => false ],
        "rof_TZ" => [ "label" => "Rombo (Tanzania)", "native_name" => "Kirombo (Tanzania)", "flag" => "🇹🇿", "rtl" => false ],
        "ru" => [ "label" => "Russian", "native_name" => "Русский Язык", "flag" => "🇷🇺", "rtl" => false ],
        "ru_MD" => [ "label" => "Russian (Moldova)", "native_name" => "Русский Язык (Молдова)", "flag" => "🇲🇩", "rtl" => false ],
        "ru_RU" => [ "label" => "Russian (Russia)", "native_name" => "Русский Язык (Россия)", "flag" => "🇷🇺", "rtl" => false ],
        "ru_UA" => [ "label" => "Russian (Ukraine)", "native_name" => "Російська Мова (Украина)", "flag" => "🇺🇦", "rtl" => false ],
        "rw" => [ "label" => "Kinyarwanda", "native_name" => "Ikinyarwanda", "flag" => "🇷🇼", "rtl" => false ],
        "rw_RW" => [ "label" => "Kinyarwanda (Rwanda)", "native_name" => "Ikinyarwanda (U Rwanda)", "flag" => "🇷🇼", "rtl" => false ],
        "rwk" => [ "label" => "Rwa", "native_name" => "Rwa", "flag" => "🇷🇼", "rtl" => false ],
        "rwk_TZ" => [ "label" => "Rwa (Tanzania)", "native_name" => "Rwa", "flag" => "🇹🇿", "rtl" => false ],
        "saq" => [ "label" => "Samburu", "native_name" => "Sampur, ƆL Maa", "flag" => "🇰🇪", "rtl" => false ],
        "saq_KE" => [ "label" => "Samburu (Kenya)", "native_name" => "Sampur, ƆL Maa (Kenya)", "flag" => "🇰🇪", "rtl" => false ],
        "seh" => [ "label" => "Sena", "native_name" => "Sena", "flag" => "🇲🇿", "rtl" => false ],
        "seh_MZ" => [ "label" => "Sena (Mozambique)", "native_name" => "Sena (Mozambique)", "flag" => "🇲🇿", "rtl" => false ],
        "ses" => [ "label" => "Koyraboro Senni", "native_name" => "Koyraboro Senni", "flag" => "🇲🇱", "rtl" => false ],
        "ses_ML" => [ "label" => "Koyraboro Senni (Mali)", "native_name" => "Koyraboro Senni (Mali)", "flag" => "🇲🇱", "rtl" => false ],
        "sg" => [ "label" => "Sango", "native_name" => "Yângâ Tî Sängö", "flag" => "🇨🇫", "rtl" => false ],
        "sg_CF" => [ "label" => "Sango (Central African Republic)", "native_name" => "Yângâ Tî Sängö (Central African Republic)", "flag" => "🇨🇫", "rtl" => false ],
        "shi" => [ "label" => "Tachelhit", "native_name" => "TacelḥIt", "flag" => "🇲🇦", "rtl" => false ],
        "shi_Latn" => [ "label" => "Tachelhit (Latin)", "native_name" => "TacelḥIt (Latin)", "flag" => "🇲🇦", "rtl" => false ],
        "shi_Latn_MA" => [ "label" => "Tachelhit (Latin, Morocco)", "native_name" => "TaclḥIyt (Latin, Morocco)", "flag" => "🇲🇦", "rtl" => false ],
        "shi_Tfng" => [ "label" => "Tachelhit (Tifinagh)", "native_name" => "ⵜⴰⵛⵍⵃⵉⵜ (Tifinagh)", "flag" => "🇲🇦", "rtl" => false ],
        "shi_Tfng_MA" => [ "label" => "Tachelhit (Tifinagh, Morocco)", "native_name" => "ⵜⴰⵛⵍⵃⵉⵜ (Tifinagh, Morocco)", "flag" => "🇲🇦", "rtl" => false ],
        "si" => [ "label" => "Sinhala", "native_name" => "සිංහල", "flag" => "🇱🇰", "rtl" => false ],
        "si_LK" => [ "label" => "Sinhala (Sri Lanka)", "native_name" => "සිංහල (ශ්රී ලංකාව)", "flag" => "🇱🇰", "rtl" => false ],
        "sk" => [ "label" => "Slovak", "native_name" => "Slovenčina, Slovenský Jazyk", "flag" => "🇸🇰", "rtl" => false ],
        "sk_SK" => [ "label" => "Slovak (Slovakia)", "native_name" => "Slovenčina, Slovenský Jazyk (Slovensko)", "flag" => "🇸🇰", "rtl" => false ],
        "sl" => [ "label" => "Slovenian", "native_name" => "Slovenčina, Slovenský Jazyk", "flag" => "🇸🇮", "rtl" => false ],
        "sl_SI" => [ "label" => "Slovenian (Slovenia)", "native_name" => "Slovenčina, Slovenský Jazyk (Slovenija)", "flag" => "🇸🇮", "rtl" => false ],
        "sn" => [ "label" => "Shona", "native_name" => "Chishona", "flag" => "🇿🇼", "rtl" => false ],
        "sn_ZW" => [ "label" => "Shona (Zimbabwe)", "native_name" => "Chishona (Zimbabwe)", "flag" => "🇿🇼", "rtl" => false ],
        "so" => [ "label" => "Somali", "native_name" => "Af Soomaali", "flag" => "🇸🇴", "rtl" => false ],
        "so_DJ" => [ "label" => "Somali (Djibouti)", "native_name" => "اف صومالي (Jabuuti)", "flag" => "🇩🇯", "rtl" => true ],
        "so_ET" => [ "label" => "Somali (Ethiopia)", "native_name" => "𐒖𐒍 𐒈𐒝𐒑𐒛𐒐𐒘, 𐒈𐒝𐒑𐒛𐒐𐒘 (Ethiopia)", "flag" => "🇪🇹", "rtl" => false ],
        "so_KE" => [ "label" => "Somali (Kenya)", "native_name" => "Af Soomaali (Kenya)", "flag" => "🇰🇪", "rtl" => false ],
        "so_SO" => [ "label" => "Somali (Somalia)", "native_name" => "Af Soomaali (Soomaaliya)", "flag" => "🇸🇴", "rtl" => false ],
        "sq" => [ "label" => "Albanian", "native_name" => "Gjuha Shqipe", "flag" => "🇦🇱", "rtl" => false ],
        "sq_AL" => [ "label" => "Albanian (Albania)", "native_name" => "Gjuha Shqipe (Shqipëri)", "flag" => "🇦🇱", "rtl" => false ],
        "sr" => [ "label" => "Serbian", "native_name" => "Srpski Jezik", "flag" => "🇷🇸", "rtl" => false ],
        "sr_BA" => [ "label" => "Serbian (Cyrillic)", "native_name" => "Cрпски Језик (Ћирилица)", "flag" => "🇷🇸", "rtl" => false ],
        "sr_Cyrl" => [ "label" => "Serbian (Cyrillic)", "native_name" => "Cрпски Језик (Ћирилица)", "flag" => "🇷🇺", "rtl" => false ],
        "sr_Cyrl_BA" => [ "label" => "Serbian (Cyrillic, Bosnia and Herzegovina)", "native_name" => "Cрпски Језик (Cyrillic, Bosnia And Herzegovina)", "flag" => "🇧🇦", "rtl" => false ],
        "sr_Cyrl_ME" => [ "label" => "Serbian (Cyrillic, Montenegro)", "native_name" => "Cрпски Језик (Cyrillic, Montenegro)", "flag" => "🇲🇪", "rtl" => false ],
        "sr_Cyrl_RS" => [ "label" => "Serbian (Cyrillic, Serbia)", "native_name" => "Cрпски Језик (Cyrillic, Serbia)", "flag" => "🇷🇸", "rtl" => false ],
        "sr_Latn" => [ "label" => "Serbian (Latin)", "native_name" => "Srpski Jezik (Латински Језик)", "flag" => "🇷🇸", "rtl" => false ],
        "sr_Latn_BA" => [ "label" => "Serbian (Latin, Bosnia and Herzegovina)", "native_name" => "Srpski Jezik (Latin, Bosnia And Herzegovina)", "flag" => "🇧🇦", "rtl" => false ],
        "sr_Latn_ME" => [ "label" => "Serbian (Latin, Montenegro)", "native_name" => "Srpski Jezik (Latin, Montenegro)", "flag" => "🇲🇪", "rtl" => false ],
        "sr_Latn_RS" => [ "label" => "Serbian (Latin, Serbia)", "native_name" => "Srpski Jezik (Latin, Serbia)", "flag" => "🇷🇸", "rtl" => false ],
        "sv" => [ "label" => "Swedish", "native_name" => "Svenska", "flag" => "🇸🇪", "rtl" => false ],
        "sv_FI" => [ "label" => "Swedish (Finland)", "native_name" => "Finlandssvenska (Finland)", "flag" => "🇫🇮", "rtl" => false ],
        "sv_SE" => [ "label" => "Swedish (Sweden)", "native_name" => "Svenska (Sverige)", "flag" => "🇸🇪", "rtl" => false ],
        "sw" => [ "label" => "Swahili", "native_name" => "Kiswahili", "flag" => "🇹🇿", "rtl" => false ],
        "sw_KE" => [ "label" => "Swahili (Kenya)", "native_name" => "Kiswahili (Kenya)", "flag" => "🇰🇪", "rtl" => false ],
        "sw_TZ" => [ "label" => "Swahili (Tanzania)", "native_name" => "Kiswahili (Tanzania)", "flag" => "🇹🇿", "rtl" => false ],
        "ta" => [ "label" => "Tamil", "native_name" => "தமிழ்", "flag" => "🇮🇳", "rtl" => false ],
        "ta_IN" => [ "label" => "Tamil (India)", "native_name" => "தமிழ் (இந்தியா)", "flag" => "🇮🇳", "rtl" => false ],
        "ta_LK" => [ "label" => "Tamil (Sri Lanka)", "native_name" => "ஈழத் தமிழ் (இலங்கை)", "flag" => "🇱🇰", "rtl" => false ],
        "te" => [ "label" => "Telugu", "native_name" => "తెలుగు", "flag" => "🇮🇳", "rtl" => false ],
        "te_IN" => [ "label" => "Telugu (India)", "native_name" => "తెలుగు (భారతదేశం)", "flag" => "🇮🇳", "rtl" => false ],
        "teo" => [ "label" => "Teso", "native_name" => "Ateso", "flag" => "🇺🇬", "rtl" => false ],
        "teo_KE" => [ "label" => "Teso (Kenya)", "native_name" => "Ateso (Kenya)", "flag" => "🇰🇪", "rtl" => false ],
        "teo_UG" => [ "label" => "Teso (Uganda)", "native_name" => "Ateso (Uganda)", "flag" => "🇺🇬", "rtl" => false ],
        "th" => [ "label" => "Thai", "native_name" => "ภาษาไทย", "flag" => "🇹🇭", "rtl" => false ],
        "th_TH" => [ "label" => "Thai (Thailand)", "native_name" => "ภาษาไทย (ประเทศไทย)", "flag" => "🇹🇭", "rtl" => false ],
        "ti" => [ "label" => "Tigrinya", "native_name" => "ትግርኛ", "flag" => "🇪🇹", "rtl" => false ],
        "ti_ER" => [ "label" => "Tigrinya (Eritrea)", "native_name" => "ትግርኛ (Eritrea)", "flag" => "🇪🇷", "rtl" => false ],
        "ti_ET" => [ "label" => "Tigrinya (Ethiopia)", "native_name" => "ትግርኛ (Ethiopia)", "flag" => "🇪🇹", "rtl" => false ],
        "tl" => [ "label" => "Tagalog", "native_name" => "Tagalog", "flag" => "🇵🇭", "rtl" => false ],
        "to" => [ "label" => "Tonga", "native_name" => "Lea Faka", "flag" => "🇹🇴", "rtl" => false ],
        "to_TO" => [ "label" => "Tonga (Tonga)", "native_name" => "Lea Faka (Tonga)", "flag" => "🇹🇴", "rtl" => false ],
        "tr" => [ "label" => "Turkish", "native_name" => "Türk Dili", "flag" => "🇹🇷", "rtl" => false ],
        "tr_TR" => [ "label" => "Turkish (Turkey)", "native_name" => "Türk Dili (Türkiye)", "flag" => "🇹🇷", "rtl" => false ],
        "tzm" => [ "label" => "Central Morocco Tamazight", "native_name" => "ⵜⴰⵎⴰⵣⵉⵖⵜ", "flag" => "🇲🇦", "rtl" => false ],
        "tzm_Latn" => [ "label" => "Central Morocco Tamazight (Latin)", "native_name" => "TamaziɣT (Latin)", "flag" => "🇲🇦", "rtl" => false ],
        "tzm_Latn_MA" => [ "label" => "Central Morocco Tamazight (Latin, Morocco)", "native_name" => "TamaziɣT (Latin, Morocco)", "flag" => "🇲🇦", "rtl" => false ],
        "uk" => [ "label" => "Ukrainian", "native_name" => "Українська Мова", "flag" => "🇺🇦", "rtl" => false ],
        "uk_UA" => [ "label" => "Ukrainian (Ukraine)", "native_name" => "Українська Мова (Україна)", "flag" => "🇺🇦", "rtl" => false ],
        "ur" => [ "label" => "Urdu", "native_name" => "اُردُو", "flag" => "🇵🇰", "rtl" => true ],
        "ur_IN" => [ "label" => "Urdu (India)", "native_name" => "اُردُو (ہندوستان)", "flag" => "🇮🇳", "rtl" => true ],
        "ur_PK" => [ "label" => "Urdu (Pakistan)", "native_name" => "اُردُو (پاکستان)", "flag" => "🇵🇰", "rtl" => true ],
        "uz" => [ "label" => "Uzbek", "native_name" => "اۉزبېکچه, اۉزبېک تیلی", "flag" => "🇺🇿", "rtl" => true ],
        "uz_Arab" => [ "label" => "Uzbek (Arabic)", "native_name" => "اۉزبېکچه, اۉزبېک تیلی (Arabparast)", "flag" => "🇶🇦", "rtl" => true ],
        "uz_Arab_AF" => [ "label" => "Uzbek (Arabic, Afghanistan)", "native_name" => "اۉزبېکچه, اۉزبېک تیلی (Arabic, Afghanistan)", "flag" => "🇦🇫", "rtl" => true ],
        "uz_Cyrl" => [ "label" => "Uzbek (Cyrillic)", "native_name" => "Ўзбекча, Ўзбек Тили (Kirillcha)", "flag" => "🇷🇺", "rtl" => false ],
        "uz_Cyrl_UZ" => [ "label" => "Uzbek (Cyrillic, Uzbekistan)", "native_name" => "Ўзбекча, Ўзбек Тили (Kirillcha Uzbekistan)", "flag" => "🇺🇿", "rtl" => false ],
        "uz_Latn" => [ "label" => "Uzbek (Latin)", "native_name" => "OʻZbekcha, OʻZbek Tili, (Lotin)", "flag" => "🇺🇿", "rtl" => false ],
        "uz_Latn_UZ" => [ "label" => "Uzbek (Latin, Uzbekistan)", "native_name" => "OʻZbekcha, OʻZbek Tili, (Lotin Uzbekistan)", "flag" => "🇺🇿", "rtl" => false ],
        "vi" => [ "label" => "Vietlabelse", "native_name" => "OʻZbekcha, OʻZbek Tili,", "flag" => "🇻🇳", "rtl" => false ],
        "vi_VN" => [ "label" => "Vietlabelse (Vietnam)", "native_name" => "TiếNg ViệT (ViệT Nam)", "flag" => "🇻🇳", "rtl" => false ],
        "vun" => [ "label" => "Vunjo", "native_name" => "Wunjo", "flag" => "🇹🇿", "rtl" => false ],
        "vun_TZ" => [ "label" => "Vunjo (Tanzania)", "native_name" => "Wunjo (Tanzania)", "flag" => "🇹🇿", "rtl" => false ],
        "xog" => [ "label" => "Soga", "native_name" => "Lusoga", "flag" => "🇺🇬", "rtl" => false ],
        "xog_UG" => [ "label" => "Soga (Uganda)", "native_name" => "Lusoga (Uganda)", "flag" => "🇺🇬", "rtl" => false ],
        "yo" => [ "label" => "Yoruba", "native_name" => "Èdè Yorùbá", "flag" => "🇳🇬", "rtl" => false ],
        "yo_NG" => [ "label" => "Yoruba (Nigeria)", "native_name" => "Èdè Yorùbá (Orilẹ-Ede Nigeria)", "flag" => "🇳🇬", "rtl" => false ],
        "yue_Hant_HK" => [ "label" => "Cantonese (Traditional, Hong Kong SAR China)", "native_name" => "香港粵語", "flag" => "🇭🇰", "rtl" => false ],
        "zh" => [ "label" => "Chinese", "native_name" => "中文简体", "flag" => "🇨🇳", "rtl" => false ],
        "zh_Hans" => [ "label" => "Chinese (Simplified Han)", "native_name" => "中文简体 (简化的汉)", "flag" => "🇨🇳", "rtl" => false ],
        "zh_CN" => [ "label" => "Chinese (Simplified Han, China)", "native_name" => "中文简体 (简化的汉，中国)", "flag" => "🇨🇳", "rtl" => false ],
        "zh_Hans_CN" => [ "label" => "Chinese (Simplified Han, China)", "native_name" => "中文简体 (简化的汉，中国)", "flag" => "🇨🇳", "rtl" => false ],
        "zh_Hans_HK" => [ "label" => "Chinese (Simplified Han, Hong Kong SAR China)", "native_name" => "簡體中文（香港） (简化的汉，香港中国)", "flag" => "🇭🇰", "rtl" => false ],
        "zh_Hans_MO" => [ "label" => "Chinese (Simplified Han, Macau SAR China)", "native_name" => "简体中文 (澳门) (简化的汉，澳门)", "flag" => "🇲🇴", "rtl" => false ],
        "zh_Hans_SG" => [ "label" => "Chinese (Simplified Han, Singapore)", "native_name" => "简体中文(新加坡） (简化的汉，新加坡)", "flag" => "🇸🇬", "rtl" => false ],
        "zh_Hant" => [ "label" => "Chinese (Traditional Han)", "native_name" => "中文（繁體） (传统汉)", "flag" => "🇹🇼", "rtl" => false ],
        "zh_Hant_HK" => [ "label" => "Chinese (Traditional Han, Hong Kong SAR China)", "native_name" => "中國繁體漢，（香港） (傳統的漢，香港中國)", "flag" => "🇭🇰", "rtl" => false ],
        "zh_Hant_MO" => [ "label" => "Chinese (Traditional Han, Macau SAR China)", "native_name" => "中文（繁體漢、澳門） (傳統漢，澳門)", "flag" => "🇲🇴", "rtl" => false ],
        "zh_TW" => [ "label" => "Chinese (Traditional Han, Taiwan)", "native_name" => "中文（繁體漢，台灣） (台灣傳統漢)", "flag" => "🇹🇼", "rtl" => false ],
        "zh_Hant_TW" => [ "label" => "Chinese (Traditional Han, Taiwan)", "native_name" => "中文（繁體漢，台灣） (台灣傳統漢)", "flag" => "🇹🇼", "rtl" => false ],
        "zu" => [ "label" => "Zulu", "native_name" => "Isizulu", "flag" => "🇿🇦", "rtl" => false ],
        "zu_ZA" => [ "label" => "Zulu (South Africa)", "native_name" => "Isizulu (Iningizimu Afrika)", "flag" => "🇿🇦", "rtl" => false ],
        ];
    return apply_filters( "dt_global_languages_list", $global_languages_list );
}
