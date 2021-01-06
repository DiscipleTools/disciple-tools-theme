<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

/**
 * Setting and lists to be used in D.T
 *
 * @author  Chasm Solutions
 * @package Disciple_Tools
 */

/*********************************************************************************************
 * Action and Filters
 */
add_filter( 'language_attributes', 'dt_custom_dir_attr' );

/*********************************************************************************************
 * Functions
 */



/**
 * Admin panel svg icon for disciple tools.
 *
 * @return string
 */
function dt_svg_icon() {
    return 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyMS40IDIwLjMyIj48ZGVmcz48c3R5bGU+LmF7ZmlsbDojMmQyZDJkO308L3N0eWxlPjwvZGVmcz48dGl0bGU+ZGlzY2lwbGUtdG9vbHM8L3RpdGxlPjxwb2x5Z29uIGNsYXNzPSJhIiBwb2ludHM9IjIxLjQgMjAuMzIgOS4zIDAgMi44NiAxMC44MSA4LjUyIDIwLjMyIDIxLjQgMjAuMzIiLz48cG9seWdvbiBjbGFzcz0iYSIgcG9pbnRzPSIwLjAyIDE1LjU4IDAgMTUuNjEgMi44MyAyMC4zMiA1LjUxIDE1LjM0IDAuMDIgMTUuNTgiLz48L3N2Zz4=';
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
            $subject_base = get_option( "dt_email_base_subject", "Disciple Tools" );
            if ( empty( $subject_base )){
                update_option( "dt_email_base_subject", "Disciple Tools" );
            }
            return $subject_base;
            break;

        case 'group_type':
            $site_options = dt_get_option( "dt_site_custom_lists" );
            return $site_options["group_type"];

        case 'group_preferences':
            $site_options = dt_get_option( "dt_site_options" );
            return $site_options["group_preferences"];

        case 'dt_working_languages':
            $languages = get_option( 'dt_working_languages', [] );
            if ( empty( $languages )){
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
            'enabled'     => true,
        ],
        'dt_user_work_whatsapp'    => [
            'label'       => __( 'Work WhatsApp', 'disciple_tools' ),
            'key'         => 'dt_user_work_whatsapp',
            'type'        => 'other',
            'description' => __( 'Work WhatsApp is for distribution to contacts and seekers.', 'disciple_tools' ),
            'enabled'     => true,
        ],
    ];

    $fields['user_fields_types'] = [
        'phone'   => [
            'label' => __( 'Phone', 'disciple_tools' ),
            'key'   => 'phone',
        ],
        'email'   => [
            'label' => __( 'Email', 'disciple_tools' ),
            'key'   => 'email',
        ],
        'social'  => [
            'label' => __( 'Social Media', 'disciple_tools' ),
            'key'   => 'social',
        ],
        'address' => [
            'label' => __( 'Address', 'disciple_tools' ),
            'key'   => 'address',
        ],
        'other'   => [
            'label' => __( 'Other', 'disciple_tools' ),
            'key'   => 'other',
        ],
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

function dt_get_global_languages_list(){
    $global_languages_list = [
        "af_NA" => [ "label" => "Afrikaans (Namibia)" ],
        "af_ZA" => [ "label" => "Afrikaans (South Africa)" ],
        "af" => [ "label" => "Afrikaans" ],
        "ak_GH" => [ "label" => "Akan (Ghana)" ],
        "ak" => [ "label" => "Akan" ],
        "sq_AL" => [ "label" => "Albanian (Albania)" ],
        "sq" => [ "label" => "Albanian" ],
        "am_ET" => [ "label" => "Amharic (Ethiopia)" ],
        "am" => [ "label" => "Amharic" ],
        "ar_DZ" => [ "label" => "Arabic (Algeria)" ],
        "ar_BH" => [ "label" => "Arabic (Bahrain)" ],
        "ar_EG" => [ "label" => "Arabic (Egypt)" ],
        "ar_IQ" => [ "label" => "Arabic (Iraq)" ],
        "ar_JO" => [ "label" => "Arabic (Jordan)" ],
        "ar_KW" => [ "label" => "Arabic (Kuwait)" ],
        "ar_LB" => [ "label" => "Arabic (Lebanon)" ],
        "ar_LY" => [ "label" => "Arabic (Libya)" ],
        "ar_MA" => [ "label" => "Arabic (Morocco)" ],
        "ar_OM" => [ "label" => "Arabic (Oman)" ],
        "ar_QA" => [ "label" => "Arabic (Qatar)" ],
        "ar_SA" => [ "label" => "Arabic (Saudi Arabia)" ],
        "ar_SD" => [ "label" => "Arabic (Sudan)" ],
        "ar_SY" => [ "label" => "Arabic (Syria)" ],
        "ar_TN" => [ "label" => "Arabic (Tunisia)" ],
        "ar_AE" => [ "label" => "Arabic (United Arab Emirates)" ],
        "ar_YE" => [ "label" => "Arabic (Yemen)" ],
        "ar" => [ "label" => "Arabic" ],
        "hy_AM" => [ "label" => "Armenian (Armenia)" ],
        "hy" => [ "label" => "Armenian" ],
        "as_IN" => [ "label" => "Assamese (India)" ],
        "as" => [ "label" => "Assamese" ],
        "asa_TZ" => [ "label" => "Asu (Tanzania)" ],
        "asa" => [ "label" => "Asu" ],
        "az_Cyrl" => [ "label" => "Azerbaijani (Cyrillic)" ],
        "az_Cyrl_AZ" => [ "label" => "Azerbaijani (Cyrillic, Azerbaijan)" ],
        "az_Latn" => [ "label" => "Azerbaijani (Latin)" ],
        "az_Latn_AZ" => [ "label" => "Azerbaijani (Latin, Azerbaijan)" ],
        "az" => [ "label" => "Azerbaijani" ],
        "bm_ML" => [ "label" => "Bambara (Mali)" ],
        "bm" => [ "label" => "Bambara" ],
        "eu_ES" => [ "label" => "Basque (Spain)" ],
        "eu" => [ "label" => "Basque" ],
        "be_BY" => [ "label" => "Belarusian (Belarus)" ],
        "be" => [ "label" => "Belarusian" ],
        "bem_ZM" => [ "label" => "Bemba (Zambia)" ],
        "bem" => [ "label" => "Bemba" ],
        "bez_TZ" => [ "label" => "Bena (Tanzania)" ],
        "bez" => [ "label" => "Bena" ],
        "bn_BD" => [ "label" => "Bengali (Bangladesh)" ],
        "bn_IN" => [ "label" => "Bengali (India)" ],
        "bn" => [ "label" => "Bengali" ],
        "bs_BA" => [ "label" => "Bosnian (Bosnia and Herzegovina)" ],
        "bs" => [ "label" => "Bosnian" ],
        "bg_BG" => [ "label" => "Bulgarian (Bulgaria)" ],
        "bg" => [ "label" => "Bulgarian" ],
        "my_MM" => [ "label" => "Burmese (Myanmar [Burma])" ],
        "my" => [ "label" => "Burmese" ],
        "yue_Hant_HK" => [ "label" => "Cantonese (Traditional, Hong Kong SAR China)" ],
        "ca_ES" => [ "label" => "Catalan (Spain)" ],
        "ca" => [ "label" => "Catalan" ],
        "tzm_Latn" => [ "label" => "Central Morocco Tamazight (Latin)" ],
        "tzm_Latn_MA" => [ "label" => "Central Morocco Tamazight (Latin, Morocco)" ],
        "tzm" => [ "label" => "Central Morocco Tamazight" ],
        "chr_US" => [ "label" => "Cherokee (United States)" ],
        "chr" => [ "label" => "Cherokee" ],
        "cgg_UG" => [ "label" => "Chiga (Uganda)" ],
        "cgg" => [ "label" => "Chiga" ],
        "zh_Hans" => [ "label" => "Chinese (Simplified Han)" ],
        "zh_Hans_CN" => [ "label" => "Chinese (Simplified Han, China)" ],
        "zh_Hans_HK" => [ "label" => "Chinese (Simplified Han, Hong Kong SAR China)" ],
        "zh_Hans_MO" => [ "label" => "Chinese (Simplified Han, Macau SAR China)" ],
        "zh_Hans_SG" => [ "label" => "Chinese (Simplified Han, Singapore)" ],
        "zh_Hant" => [ "label" => "Chinese (Traditional Han)" ],
        "zh_Hant_HK" => [ "label" => "Chinese (Traditional Han, Hong Kong SAR China)" ],
        "zh_Hant_MO" => [ "label" => "Chinese (Traditional Han, Macau SAR China)" ],
        "zh_Hant_TW" => [ "label" => "Chinese (Traditional Han, Taiwan)" ],
        "zh" => [ "label" => "Chinese" ],
        "kw_GB" => [ "label" => "Cornish (United Kingdom)" ],
        "kw" => [ "label" => "Cornish" ],
        "hr_HR" => [ "label" => "Croatian (Croatia)" ],
        "hr" => [ "label" => "Croatian" ],
        "cs_CZ" => [ "label" => "Czech (Czech Republic)" ],
        "cs" => [ "label" => "Czech" ],
        "da_DK" => [ "label" => "Danish (Denmark)" ],
        "da" => [ "label" => "Danish" ],
        "nl_BE" => [ "label" => "Dutch (Belgium)" ],
        "nl_NL" => [ "label" => "Dutch (Netherlands)" ],
        "nl" => [ "label" => "Dutch" ],
        "ebu_KE" => [ "label" => "Embu (Kenya)" ],
        "ebu" => [ "label" => "Embu" ],
        "en_AS" => [ "label" => "English (American Samoa)" ],
        "en_AU" => [ "label" => "English (Australia)" ],
        "en_BE" => [ "label" => "English (Belgium)" ],
        "en_BZ" => [ "label" => "English (Belize)" ],
        "en_BW" => [ "label" => "English (Botswana)" ],
        "en_CA" => [ "label" => "English (Canada)" ],
        "en_GU" => [ "label" => "English (Guam)" ],
        "en_HK" => [ "label" => "English (Hong Kong SAR China)" ],
        "en_IN" => [ "label" => "English (India)" ],
        "en_IE" => [ "label" => "English (Ireland)" ],
        "en_IL" => [ "label" => "English (Israel)" ],
        "en_JM" => [ "label" => "English (Jamaica)" ],
        "en_MT" => [ "label" => "English (Malta)" ],
        "en_MH" => [ "label" => "English (Marshall Islands)" ],
        "en_MU" => [ "label" => "English (Mauritius)" ],
        "en_NA" => [ "label" => "English (Namibia)" ],
        "en_NZ" => [ "label" => "English (New Zealand)" ],
        "en_MP" => [ "label" => "English (Northern Mariana Islands)" ],
        "en_PK" => [ "label" => "English (Pakistan)" ],
        "en_PH" => [ "label" => "English (Philippines)" ],
        "en_SG" => [ "label" => "English (Singapore)" ],
        "en_ZA" => [ "label" => "English (South Africa)" ],
        "en_TT" => [ "label" => "English (Trinidad and Tobago)" ],
        "en_UM" => [ "label" => "English (U.S. Minor Outlying Islands)" ],
        "en_VI" => [ "label" => "English (U.S. Virgin Islands)" ],
        "en_GB" => [ "label" => "English (United Kingdom)" ],
        "en_US" => [ "label" => "English (United States)" ],
        "en_ZW" => [ "label" => "English (Zimbabwe)" ],
        "en" => [ "label" => "English" ],
        "eo" => [ "label" => "Esperanto" ],
        "et_EE" => [ "label" => "Estonian (Estonia)" ],
        "et" => [ "label" => "Estonian" ],
        "ee_GH" => [ "label" => "Ewe (Ghana)" ],
        "ee_TG" => [ "label" => "Ewe (Togo)" ],
        "ee" => [ "label" => "Ewe" ],
        "fo_FO" => [ "label" => "Faroese (Faroe Islands)" ],
        "fo" => [ "label" => "Faroese" ],
        "fil_PH" => [ "label" => "Filipino (Philippines)" ],
        "fil" => [ "label" => "Filipino" ],
        "fi_FI" => [ "label" => "Finnish (Finland)" ],
        "fi" => [ "label" => "Finnish" ],
        "fr_BE" => [ "label" => "French (Belgium)" ],
        "fr_BJ" => [ "label" => "French (Benin)" ],
        "fr_BF" => [ "label" => "French (Burkina Faso)" ],
        "fr_BI" => [ "label" => "French (Burundi)" ],
        "fr_CM" => [ "label" => "French (Cameroon)" ],
        "fr_CA" => [ "label" => "French (Canada)" ],
        "fr_CF" => [ "label" => "French (Central African Republic)" ],
        "fr_TD" => [ "label" => "French (Chad)" ],
        "fr_KM" => [ "label" => "French (Comoros)" ],
        "fr_CG" => [ "label" => "French (Congo - Brazzaville)" ],
        "fr_CD" => [ "label" => "French (Congo - Kinshasa)" ],
        "fr_CI" => [ "label" => "French (Côte d’Ivoire)" ],
        "fr_DJ" => [ "label" => "French (Djibouti)" ],
        "fr_GQ" => [ "label" => "French (Equatorial Guinea)" ],
        "fr_FR" => [ "label" => "French (France)" ],
        "fr_GA" => [ "label" => "French (Gabon)" ],
        "fr_GP" => [ "label" => "French (Guadeloupe)" ],
        "fr_GN" => [ "label" => "French (Guinea)" ],
        "fr_LU" => [ "label" => "French (Luxembourg)" ],
        "fr_MG" => [ "label" => "French (Madagascar)" ],
        "fr_ML" => [ "label" => "French (Mali)" ],
        "fr_MQ" => [ "label" => "French (Martinique)" ],
        "fr_MC" => [ "label" => "French (Monaco)" ],
        "fr_NE" => [ "label" => "French (Niger)" ],
        "fr_RW" => [ "label" => "French (Rwanda)" ],
        "fr_RE" => [ "label" => "French (Réunion)" ],
        "fr_BL" => [ "label" => "French (Saint Barthélemy)" ],
        "fr_MF" => [ "label" => "French (Saint Martin)" ],
        "fr_SN" => [ "label" => "French (Senegal)" ],
        "fr_CH" => [ "label" => "French (Switzerland)" ],
        "fr_TG" => [ "label" => "French (Togo)" ],
        "fr" => [ "label" => "French" ],
        "ff_SN" => [ "label" => "Fulah (Senegal)" ],
        "ff" => [ "label" => "Fulah" ],
        "gl_ES" => [ "label" => "Galician (Spain)" ],
        "gl" => [ "label" => "Galician" ],
        "lg_UG" => [ "label" => "Ganda (Uganda)" ],
        "lg" => [ "label" => "Ganda" ],
        "ka_GE" => [ "label" => "Georgian (Georgia)" ],
        "ka" => [ "label" => "Georgian" ],
        "de_AT" => [ "label" => "German (Austria)" ],
        "de_BE" => [ "label" => "German (Belgium)" ],
        "de_DE" => [ "label" => "German (Germany)" ],
        "de_LI" => [ "label" => "German (Liechtenstein)" ],
        "de_LU" => [ "label" => "German (Luxembourg)" ],
        "de_CH" => [ "label" => "German (Switzerland)" ],
        "de" => [ "label" => "German" ],
        "el_CY" => [ "label" => "Greek (Cyprus)" ],
        "el_GR" => [ "label" => "Greek (Greece)" ],
        "el" => [ "label" => "Greek" ],
        "gu_IN" => [ "label" => "Gujarati (India)" ],
        "gu" => [ "label" => "Gujarati" ],
        "guz_KE" => [ "label" => "Gusii (Kenya)" ],
        "guz" => [ "label" => "Gusii" ],
        "ha_Latn" => [ "label" => "Hausa (Latin)" ],
        "ha_Latn_GH" => [ "label" => "Hausa (Latin, Ghana)" ],
        "ha_Latn_NE" => [ "label" => "Hausa (Latin, Niger)" ],
        "ha_Latn_NG" => [ "label" => "Hausa (Latin, Nigeria)" ],
        "ha" => [ "label" => "Hausa" ],
        "haw_US" => [ "label" => "Hawaiian (United States)" ],
        "haw" => [ "label" => "Hawaiian" ],
        "he_IL" => [ "label" => "Hebrew (Israel)" ],
        "he" => [ "label" => "Hebrew" ],
        "hi_IN" => [ "label" => "Hindi (India)" ],
        "hi" => [ "label" => "Hindi" ],
        "hu_HU" => [ "label" => "Hungarian (Hungary)" ],
        "hu" => [ "label" => "Hungarian" ],
        "is_IS" => [ "label" => "Icelandic (Iceland)" ],
        "is" => [ "label" => "Icelandic" ],
        "ig_NG" => [ "label" => "Igbo (Nigeria)" ],
        "ig" => [ "label" => "Igbo" ],
        "id_ID" => [ "label" => "Indonesian (Indonesia)" ],
        "id" => [ "label" => "Indonesian" ],
        "ga_IE" => [ "label" => "Irish (Ireland)" ],
        "ga" => [ "label" => "Irish" ],
        "it_IT" => [ "label" => "Italian (Italy)" ],
        "it_CH" => [ "label" => "Italian (Switzerland)" ],
        "it" => [ "label" => "Italian" ],
        "ja_JP" => [ "label" => "Japanese (Japan)" ],
        "ja" => [ "label" => "Japanese" ],
        "kea_CV" => [ "label" => "Kabuverdianu (Cape Verde)" ],
        "kea" => [ "label" => "Kabuverdianu" ],
        "kab_DZ" => [ "label" => "Kabyle (Algeria)" ],
        "kab" => [ "label" => "Kabyle" ],
        "kl_GL" => [ "label" => "Kalaallisut (Greenland)" ],
        "kl" => [ "label" => "Kalaallisut" ],
        "kln_KE" => [ "label" => "Kalenjin (Kenya)" ],
        "kln" => [ "label" => "Kalenjin" ],
        "kam_KE" => [ "label" => "Kamba (Kenya)" ],
        "kam" => [ "label" => "Kamba" ],
        "kn_IN" => [ "label" => "Kannada (India)" ],
        "kn" => [ "label" => "Kannada" ],
        "kk_Cyrl" => [ "label" => "Kazakh (Cyrillic)" ],
        "kk_Cyrl_KZ" => [ "label" => "Kazakh (Cyrillic, Kazakhstan)" ],
        "kk" => [ "label" => "Kazakh" ],
        "km_KH" => [ "label" => "Khmer (Cambodia)" ],
        "km" => [ "label" => "Khmer" ],
        "ki_KE" => [ "label" => "Kikuyu (Kenya)" ],
        "ki" => [ "label" => "Kikuyu" ],
        "rw_RW" => [ "label" => "Kinyarwanda (Rwanda)" ],
        "rw" => [ "label" => "Kinyarwanda" ],
        "kok_IN" => [ "label" => "Konkani (India)" ],
        "kok" => [ "label" => "Konkani" ],
        "ko_KR" => [ "label" => "Korean (South Korea)" ],
        "ko" => [ "label" => "Korean" ],
        "khq_ML" => [ "label" => "Koyra Chiini (Mali)" ],
        "khq" => [ "label" => "Koyra Chiini" ],
        "ses_ML" => [ "label" => "Koyraboro Senni (Mali)" ],
        "ses" => [ "label" => "Koyraboro Senni" ],
        "lag_TZ" => [ "label" => "Langi (Tanzania)" ],
        "lag" => [ "label" => "Langi" ],
        "lv_LV" => [ "label" => "Latvian (Latvia)" ],
        "lv" => [ "label" => "Latvian" ],
        "lt_LT" => [ "label" => "Lithuanian (Lithuania)" ],
        "lt" => [ "label" => "Lithuanian" ],
        "luo_KE" => [ "label" => "Luo (Kenya)" ],
        "luo" => [ "label" => "Luo" ],
        "luy_KE" => [ "label" => "Luyia (Kenya)" ],
        "luy" => [ "label" => "Luyia" ],
        "mk_MK" => [ "label" => "Macedonian (Macedonia)" ],
        "mk" => [ "label" => "Macedonian" ],
        "jmc_TZ" => [ "label" => "Machame (Tanzania)" ],
        "jmc" => [ "label" => "Machame" ],
        "kde_TZ" => [ "label" => "Makonde (Tanzania)" ],
        "kde" => [ "label" => "Makonde" ],
        "mg_MG" => [ "label" => "Malagasy (Madagascar)" ],
        "mg" => [ "label" => "Malagasy" ],
        "ms_BN" => [ "label" => "Malay (Brunei)" ],
        "ms_MY" => [ "label" => "Malay (Malaysia)" ],
        "ms" => [ "label" => "Malay" ],
        "ml_IN" => [ "label" => "Malayalam (India)" ],
        "ml" => [ "label" => "Malayalam" ],
        "mt_MT" => [ "label" => "Maltese (Malta)" ],
        "mt" => [ "label" => "Maltese" ],
        "gv_GB" => [ "label" => "Manx (United Kingdom)" ],
        "gv" => [ "label" => "Manx" ],
        "mr_IN" => [ "label" => "Marathi (India)" ],
        "mr" => [ "label" => "Marathi" ],
        "mas_KE" => [ "label" => "Masai (Kenya)" ],
        "mas_TZ" => [ "label" => "Masai (Tanzania)" ],
        "mas" => [ "label" => "Masai" ],
        "mer_KE" => [ "label" => "Meru (Kenya)" ],
        "mer" => [ "label" => "Meru" ],
        "mfe_MU" => [ "label" => "Morisyen (Mauritius)" ],
        "mfe" => [ "label" => "Morisyen" ],
        "naq_NA" => [ "label" => "Nama (Namibia)" ],
        "naq" => [ "label" => "Nama" ],
        "ne_IN" => [ "label" => "Nepali (India)" ],
        "ne_NP" => [ "label" => "Nepali (Nepal)" ],
        "ne" => [ "label" => "Nepali" ],
        "nd_ZW" => [ "label" => "North Ndebele (Zimbabwe)" ],
        "nd" => [ "label" => "North Ndebele" ],
        "nb_NO" => [ "label" => "Norwegian Bokmål (Norway)" ],
        "nb" => [ "label" => "Norwegian Bokmål" ],
        "nn_NO" => [ "label" => "Norwegian Nynorsk (Norway)" ],
        "nn" => [ "label" => "Norwegian Nynorsk" ],
        "nyn_UG" => [ "label" => "Nyankole (Uganda)" ],
        "nyn" => [ "label" => "Nyankole" ],
        "or_IN" => [ "label" => "Oriya (India)" ],
        "or" => [ "label" => "Oriya" ],
        "om_ET" => [ "label" => "Oromo (Ethiopia)" ],
        "om_KE" => [ "label" => "Oromo (Kenya)" ],
        "om" => [ "label" => "Oromo" ],
        "ps_AF" => [ "label" => "Pashto (Afghanistan)" ],
        "ps" => [ "label" => "Pashto" ],
        "fa_AF" => [ "label" => "Persian (Afghanistan)" ],
        "fa_IR" => [ "label" => "Persian (Iran)" ],
        "fa" => [ "label" => "Persian" ],
        "pl_PL" => [ "label" => "Polish (Poland)" ],
        "pl" => [ "label" => "Polish" ],
        "pt_BR" => [ "label" => "Portuguese (Brazil)" ],
        "pt_GW" => [ "label" => "Portuguese (Guinea-Bissau)" ],
        "pt_MZ" => [ "label" => "Portuguese (Mozambique)" ],
        "pt_PT" => [ "label" => "Portuguese (Portugal)" ],
        "pt" => [ "label" => "Portuguese" ],
        "pa_Arab" => [ "label" => "Punjabi (Arabic)" ],
        "pa_Arab_PK" => [ "label" => "Punjabi (Arabic, Pakistan)" ],
        "pa_Guru" => [ "label" => "Punjabi (Gurmukhi)" ],
        "pa_Guru_IN" => [ "label" => "Punjabi (Gurmukhi, India)" ],
        "pa" => [ "label" => "Punjabi" ],
        "ro_MD" => [ "label" => "Romanian (Moldova)" ],
        "ro_RO" => [ "label" => "Romanian (Romania)" ],
        "ro" => [ "label" => "Romanian" ],
        "rm_CH" => [ "label" => "Romansh (Switzerland)" ],
        "rm" => [ "label" => "Romansh" ],
        "rof_TZ" => [ "label" => "Rombo (Tanzania)" ],
        "rof" => [ "label" => "Rombo" ],
        "ru_MD" => [ "label" => "Russian (Moldova)" ],
        "ru_RU" => [ "label" => "Russian (Russia)" ],
        "ru_UA" => [ "label" => "Russian (Ukraine)" ],
        "ru" => [ "label" => "Russian" ],
        "rwk_TZ" => [ "label" => "Rwa (Tanzania)" ],
        "rwk" => [ "label" => "Rwa" ],
        "saq_KE" => [ "label" => "Samburu (Kenya)" ],
        "saq" => [ "label" => "Samburu" ],
        "sg_CF" => [ "label" => "Sango (Central African Republic)" ],
        "sg" => [ "label" => "Sango" ],
        "seh_MZ" => [ "label" => "Sena (Mozambique)" ],
        "seh" => [ "label" => "Sena" ],
        "sr_Cyrl" => [ "label" => "Serbian (Cyrillic)" ],
        "sr_Cyrl_BA" => [ "label" => "Serbian (Cyrillic, Bosnia and Herzegovina)" ],
        "sr_Cyrl_ME" => [ "label" => "Serbian (Cyrillic, Montenegro)" ],
        "sr_Cyrl_RS" => [ "label" => "Serbian (Cyrillic, Serbia)" ],
        "sr_Latn" => [ "label" => "Serbian (Latin)" ],
        "sr_Latn_BA" => [ "label" => "Serbian (Latin, Bosnia and Herzegovina)" ],
        "sr_Latn_ME" => [ "label" => "Serbian (Latin, Montenegro)" ],
        "sr_Latn_RS" => [ "label" => "Serbian (Latin, Serbia)" ],
        "sr" => [ "label" => "Serbian" ],
        "sn_ZW" => [ "label" => "Shona (Zimbabwe)" ],
        "sn" => [ "label" => "Shona" ],
        "ii_CN" => [ "label" => "Sichuan Yi (China)" ],
        "ii" => [ "label" => "Sichuan Yi" ],
        "si_LK" => [ "label" => "Sinhala (Sri Lanka)" ],
        "si" => [ "label" => "Sinhala" ],
        "sk_SK" => [ "label" => "Slovak (Slovakia)" ],
        "sk" => [ "label" => "Slovak" ],
        "sl_SI" => [ "label" => "Slovenian (Slovenia)" ],
        "sl" => [ "label" => "Slovenian" ],
        "xog_UG" => [ "label" => "Soga (Uganda)" ],
        "xog" => [ "label" => "Soga" ],
        "so_DJ" => [ "label" => "Somali (Djibouti)" ],
        "so_ET" => [ "label" => "Somali (Ethiopia)" ],
        "so_KE" => [ "label" => "Somali (Kenya)" ],
        "so_SO" => [ "label" => "Somali (Somalia)" ],
        "so" => [ "label" => "Somali" ],
        "es_AR" => [ "label" => "Spanish (Argentina)" ],
        "es_BO" => [ "label" => "Spanish (Bolivia)" ],
        "es_CL" => [ "label" => "Spanish (Chile)" ],
        "es_CO" => [ "label" => "Spanish (Colombia)" ],
        "es_CR" => [ "label" => "Spanish (Costa Rica)" ],
        "es_DO" => [ "label" => "Spanish (Dominican Republic)" ],
        "es_EC" => [ "label" => "Spanish (Ecuador)" ],
        "es_SV" => [ "label" => "Spanish (El Salvador)" ],
        "es_GQ" => [ "label" => "Spanish (Equatorial Guinea)" ],
        "es_GT" => [ "label" => "Spanish (Guatemala)" ],
        "es_HN" => [ "label" => "Spanish (Honduras)" ],
        "es_419" => [ "label" => "Spanish (Latin America)" ],
        "es_MX" => [ "label" => "Spanish (Mexico)" ],
        "es_NI" => [ "label" => "Spanish (Nicaragua)" ],
        "es_PA" => [ "label" => "Spanish (Panama)" ],
        "es_PY" => [ "label" => "Spanish (Paraguay)" ],
        "es_PE" => [ "label" => "Spanish (Peru)" ],
        "es_PR" => [ "label" => "Spanish (Puerto Rico)" ],
        "es_ES" => [ "label" => "Spanish (Spain)" ],
        "es_US" => [ "label" => "Spanish (United States)" ],
        "es_UY" => [ "label" => "Spanish (Uruguay)" ],
        "es_VE" => [ "label" => "Spanish (Venezuela)" ],
        "es" => [ "label" => "Spanish" ],
        "sw_KE" => [ "label" => "Swahili (Kenya)" ],
        "sw_TZ" => [ "label" => "Swahili (Tanzania)" ],
        "sw" => [ "label" => "Swahili" ],
        "sv_FI" => [ "label" => "Swedish (Finland)" ],
        "sv_SE" => [ "label" => "Swedish (Sweden)" ],
        "sv" => [ "label" => "Swedish" ],
        "gsw_CH" => [ "label" => "Swiss German (Switzerland)" ],
        "gsw" => [ "label" => "Swiss German" ],
        "shi_Latn" => [ "label" => "Tachelhit (Latin)" ],
        "shi_Latn_MA" => [ "label" => "Tachelhit (Latin, Morocco)" ],
        "shi_Tfng" => [ "label" => "Tachelhit (Tifinagh)" ],
        "shi_Tfng_MA" => [ "label" => "Tachelhit (Tifinagh, Morocco)" ],
        "shi" => [ "label" => "Tachelhit" ],
        "dav_KE" => [ "label" => "Taita (Kenya)" ],
        "dav" => [ "label" => "Taita" ],
        "ta_IN" => [ "label" => "Tamil (India)" ],
        "ta_LK" => [ "label" => "Tamil (Sri Lanka)" ],
        "ta" => [ "label" => "Tamil" ],
        "te_IN" => [ "label" => "Telugu (India)" ],
        "te" => [ "label" => "Telugu" ],
        "teo_KE" => [ "label" => "Teso (Kenya)" ],
        "teo_UG" => [ "label" => "Teso (Uganda)" ],
        "teo" => [ "label" => "Teso" ],
        "th_TH" => [ "label" => "Thai (Thailand)" ],
        "th" => [ "label" => "Thai" ],
        "bo_CN" => [ "label" => "Tibetan (China)" ],
        "bo_IN" => [ "label" => "Tibetan (India)" ],
        "bo" => [ "label" => "Tibetan" ],
        "ti_ER" => [ "label" => "Tigrinya (Eritrea)" ],
        "ti_ET" => [ "label" => "Tigrinya (Ethiopia)" ],
        "ti" => [ "label" => "Tigrinya" ],
        "to_TO" => [ "label" => "Tonga (Tonga)" ],
        "to" => [ "label" => "Tonga" ],
        "tr_TR" => [ "label" => "Turkish (Turkey)" ],
        "tr" => [ "label" => "Turkish" ],
        "uk_UA" => [ "label" => "Ukrainian (Ukraine)" ],
        "uk" => [ "label" => "Ukrainian" ],
        "ur_IN" => [ "label" => "Urdu (India)" ],
        "ur_PK" => [ "label" => "Urdu (Pakistan)" ],
        "ur" => [ "label" => "Urdu" ],
        "uz_Arab" => [ "label" => "Uzbek (Arabic)" ],
        "uz_Arab_AF" => [ "label" => "Uzbek (Arabic, Afghanistan)" ],
        "uz_Cyrl" => [ "label" => "Uzbek (Cyrillic)" ],
        "uz_Cyrl_UZ" => [ "label" => "Uzbek (Cyrillic, Uzbekistan)" ],
        "uz_Latn" => [ "label" => "Uzbek (Latin)" ],
        "uz_Latn_UZ" => [ "label" => "Uzbek (Latin, Uzbekistan)" ],
        "uz" => [ "label" => "Uzbek" ],
        "vi_VN" => [ "label" => "Vietlabelse (Vietnam)" ],
        "vi" => [ "label" => "Vietlabelse" ],
        "vun_TZ" => [ "label" => "Vunjo (Tanzania)" ],
        "vun" => [ "label" => "Vunjo" ],
        "cy_GB" => [ "label" => "Welsh (United Kingdom)" ],
        "cy" => [ "label" => "Welsh" ],
        "yo_NG" => [ "label" => "Yoruba (Nigeria)" ],
        "yo" => [ "label" => "Yoruba" ],
        "zu_ZA" => [ "label" => "Zulu (South Africa)" ],
        "zu" => [ "label" => "Zulu" ],
    ];

    return apply_filters( "dt_global_languages_list", $global_languages_list );
}
