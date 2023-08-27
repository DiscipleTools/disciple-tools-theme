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
    $deltas = array();

    foreach ( $options ?? array() as $opt_key => $opt_val ) {
        $found = false;
        foreach ( $update_required_options ?? array() as $required ) {

            // Is there already a trigger specified?
            if ( $required['seeker_path'] === $opt_key ) {
                $found = true;
            }
        }

        // If not, then assign as new delta
        if ( ! $found ) {
            $deltas[] = array(
                'status'      => 'active',
                'seeker_path' => $opt_key,
                'days'        => 30,
                'comment'     => __( "We haven't heard about this person in a while. Do you have an update for this contact?", 'disciple_tool' ),
            );
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
            elseif ( (int) get_option( 'dt_site_custom_lists' )['version'] < $default_custom_lists['version'] ) {
                // option exists but version is behind
                    $upgrade = dt_site_options_upgrade_version( 'dt_site_custom_lists' );
//                    updating the option is not always working right away, return the non updated option instead of failing.
                if ( !$upgrade ) {
                    return $default_custom_lists;
                }
            }
            //return apply_filters( "dt_site_custom_lists", get_option( 'dt_site_custom_lists' ) );
            return get_option( 'dt_site_custom_lists' );
            break;

        case 'dt_field_customizations':
            return get_option( 'dt_field_customizations', array(
                'contacts' => array(),
                'groups' => array(),
            ));
        case 'dt_custom_tiles':

            $custom_tiles = get_option( 'dt_custom_tiles', array(
                'contacts' => array(),
                'groups' => array(),
            ));

             $custom_tiles_with_translations = apply_filters( 'options_dt_custom_tiles', $custom_tiles );

             return $custom_tiles_with_translations;

        case 'base_user':
            if ( ! get_option( 'dt_base_user' ) ) { // options doesn't exist, create new.
                // set base users to system admin
                $users = get_users( array( 'role' => 'dispatcher' ) );
                if ( empty( $users ) ) {
                    $users = get_users( array( 'role' => 'administrator' ) );
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
            $subject_base = get_option( 'dt_email_base_subject', 'Disciple.Tools' );
            if ( empty( $subject_base ) ){
                update_option( 'dt_email_base_subject', 'Disciple.Tools' );
            }
            return $subject_base;
            break;

        case 'dt_email_base_address':
            return get_option( 'dt_email_base_address', '' );
        case 'dt_email_base_name':
            return get_option( 'dt_email_base_name', '' );

        case 'group_type':
            $site_options = dt_get_option( 'dt_site_custom_lists' );
            return $site_options['group_type'];

        case 'group_preferences':
            $site_options = dt_get_option( 'dt_site_options' );
            return $site_options['group_preferences'];

        case 'dt_working_languages':
            $languages = get_option( 'dt_working_languages', array() );
            if ( empty( $languages ) ){
                $languages = array(
                    'en' => array( 'label' => 'English' ),
                    'fr' => array( 'label' => 'French' ),
                    'es' => array( 'label' => 'Spanish' ),
                );
            }
            $languages = DT_Posts_Hooks::dt_get_field_options_translation( $languages );
            return apply_filters( 'dt_working_languages', $languages );

        case 'dt_post_type_modules':
            $modules = apply_filters( 'dt_post_type_modules', array() );
            $module_options = get_option( 'dt_post_type_modules', array() );
            // remove modules not present
            foreach ( $module_options as $key => $module ){
                if ( ! isset( $modules[$key] ) ) {
                    unset( $module_options[$key] );
                }
            }
            // merge distinct
            $modules = dt_array_merge_recursive_distinct( $modules, $module_options );
            return apply_filters( 'dt_post_type_modules_after', $modules );

        case 'dt_comment_types':
            return get_option( 'dt_comment_types', array() );

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
    $fields = array();

    $fields['version'] = '9';

    $fields['notifications'] = array(
        'channels' => array(
            'email' => array(
                'label' => __( 'Email', 'disciple_tools' ),
            ),
            'web' => array(
                'label' => __( 'Web', 'disciple_tools' ),
            ),
        ),
        'types' => array(
            'new_assigned' => array(
                'label' => __( 'Newly Assigned Contact', 'disciple_tools' ),
                'web'   => true,
                'email' => true,
            ),
            'mentions' => array(
                'label' => __( '@Mentions', 'disciple_tools' ),
                'web'   => true,
                'email' => true,
            ),
            'comments' => array(
                'label' => __( 'New Comments', 'disciple_tools' ),
                'web'   => false,
                'email' => false,
            ),
            'updates' => array(
                'label' => __( 'Update Needed', 'disciple_tools' ),
                'web'   => true,
                'email' => true,
            ),
            'changes' => array(
                'label' => __( 'Contact Info Changed', 'disciple_tools' ),
                'web'   => false,
                'email' => false,
            ),
            'milestones' => array(
                'label' => __( 'Contact Milestones and Group Health metrics', 'disciple_tools' ),
                'web'   => false,
                'email' => false,
            ),
        ),
    );

    $fields['daily_reports'] = array(
        'build_report_for_contacts'  => true,
        'build_report_for_groups'    => true,
        'build_report_for_facebook'  => false,
        'build_report_for_twitter'   => false,
        'build_report_for_analytics' => false,
        'build_report_for_adwords'   => false,
        'build_report_for_mailchimp' => false,
        'build_report_for_youtube'   => false,
    );

    $fields['update_required'] = array(
        'enabled' => true,
        'options' => array(
            array(
                'status'      => 'active',
                'seeker_path' => 'none',
                'days'        => 3,
                'comment'     => __( 'This contact is active but there is no record of anybody contacting them. Please do contact them.', 'disciple_tools' ),
            ),
            array(
                'status'      => 'active',
                'seeker_path' => 'attempted',
                'days'        => 7,
                'comment'     => __( 'Please try connecting with this contact again.', 'disciple_tools' ),
            ),
            array(
                'status'      => 'active',
                'seeker_path' => 'established',
                'days'        => 30,
                'comment'     => __( "We haven't heard about this person in a while. Do you have an update for this contact?", 'disciple_tools' ),
            ),
            array(
                'status'      => 'active',
                'seeker_path' => 'scheduled',
                'days'        => 30,
                'comment'     => __( "We haven't heard about this person in a while. Do you have an update for this contact?", 'disciple_tools' ),
            ),
            array(
                'status'      => 'active',
                'seeker_path' => 'met',
                'days'        => 30,
                'comment'     => __( "We haven't heard about this person in a while. Do you have an update for this contact?", 'disciple_tools' ),
            ),
            array(
                'status'      => 'active',
                'seeker_path' => 'ongoing',
                'days'        => 30,
                'comment'     => __( "We haven't heard about this person in a while. Do you have an update for this contact?", 'disciple_tools' ),
            ),
            array(
                'status'      => 'active',
                'seeker_path' => 'coaching',
                'days'        => 30,
                'comment'     => __( "We haven't heard about this person in a while. Do you have an update for this contact?", 'disciple_tools' ),
            ),
        ),
    );
    $fields['group_update_required'] = array(
        'enabled' => true,
        'options' => array(
            array(
                'status'      => 'active',
                'days'        => 30,
                'comment'     => __( "We haven't heard about this group in a while. Do you have an update?", 'disciple_tools' ),
            ),
        ),
    );
    $fields['group_preferences'] = array(
        'church_metrics' => true,
        'four_fields' => false,
    );

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
    $fields = array();

    $fields['version'] = 10;

    // the prefix dt_user_ assists db meta queries on the user
    $fields['user_fields'] = array(
        'dt_user_personal_phone'   => array(
            'label'       => __( 'Personal Phone', 'disciple_tools' ),
            'key'         => 'dt_user_personal_phone',
            'type'        => 'phone',
            'description' => __( 'Personal phone is private to the team, not for distribution.', 'disciple_tools' ),
            'enabled'     => true,
        ),
        'dt_user_personal_email'   => array(
            'label'       => __( 'Personal Email', 'disciple_tools' ),
            'key'         => 'dt_user_personal_email',
            'type'        => 'email',
            'description' => __( 'Personal email is private to the team, not for distribution.', 'disciple_tools' ),
            'enabled'     => true,
        ),
        'dt_user_personal_address' => array(
            'label'       => __( 'Personal Address', 'disciple_tools' ),
            'key'         => 'dt_user_personal_address',
            'type'        => 'address',
            'description' => __( 'Personal address is private to the team, not for distribution.', 'disciple_tools' ),
            'enabled'     => true,
        ),
        'dt_user_work_phone'       => array(
            'label'       => __( 'Work Phone', 'disciple_tools' ),
            'key'         => 'dt_user_work_phone',
            'type'        => 'phone',
            'description' => __( 'Work phone is for distribution to contacts and seekers.', 'disciple_tools' ),
            'enabled'     => true,
        ),
        'dt_user_work_email'       => array(
            'label'       => __( 'Work Email', 'disciple_tools' ),
            'key'         => 'dt_user_work_email',
            'type'        => 'email',
            'description' => __( 'Work email is for distribution to contacts and seekers.', 'disciple_tools' ),
            'enabled'     => true,
        ),
        'dt_user_work_facebook'    => array(
            'label'       => __( 'Work Facebook', 'disciple_tools' ),
            'key'         => 'dt_user_work_facebook',
            'type'        => 'social',
            'description' => __( 'Work Facebook is for distribution to contacts and seekers.', 'disciple_tools' ),
            'enabled'     => false,
        ),
        'dt_user_work_whatsapp'    => array(
            'label'       => __( 'Work WhatsApp', 'disciple_tools' ),
            'key'         => 'dt_user_work_whatsapp',
            'type'        => 'other',
            'description' => __( 'Work WhatsApp is for distribution to contacts and seekers.', 'disciple_tools' ),
            'enabled'     => false,
        ),
    );

    // alias's must be lower case with no spaces
    $fields['comment_reaction_options'] = array(
            'thumbs_up' => array( 'name' => __( 'thumbs up', 'disciple_tools' ), 'path' => 'https://github.githubassets.com/images/icons/emoji/unicode/1f44d.png', 'emoji' => '👍' ),
            'heart' => array( 'name' => __( 'heart', 'disciple_tools' ), 'path' => 'https://github.githubassets.com/images/icons/emoji/unicode/2764.png', 'emoji' => '❤️' ),
            'laugh' => array( 'name' => __( 'laugh', 'disciple_tools' ), 'path' => 'https://github.githubassets.com/images/icons/emoji/unicode/1f604.png', 'emoji' => '😄' ),
            'wow' => array( 'name' => __( 'wow', 'disciple_tools' ), 'path' => 'https://github.githubassets.com/images/icons/emoji/unicode/1f62e.png', 'emoji' => '😮' ),
            'sad' => array( 'name' => __( 'sad', 'disciple_tools' ), 'path' => 'https://github.githubassets.com/images/icons/emoji/unicode/1f615.png', 'emoji' => '😟' ),
            'prayer' => array( 'name' => __( 'prayer', 'disciple_tools' ), 'path' => 'https://github.githubassets.com/images/icons/emoji/unicode/1f64f.png', 'emoji' => '🙏' ),
            //"praise" => [ 'name' => __( "praise", "disciple_tools" ), 'path' => 'https://github.githubassets.com/images/icons/emoji/unicode/1f64c.png', 'emoji' => '🙌' ],
            //"angry" => [ 'name' => __( "angry", "disciple_tools" ), 'path' => 'https://github.githubassets.com/images/icons/emoji/unicode/1f620.png', 'emoji' => '😠' ],
        );

    $fields['sources'] = array();

    $fields['contact_address_types'] = array(
        'home'  => array( 'label' => __( 'Home', 'disciple_tools' ) ),
        'work'  => array( 'label' => __( 'Work', 'disciple_tools' ) ),
        'other' => array( 'label' => __( 'Other', 'disciple_tools' ) ),
    );
    $fields['group_preferences'] = array(
        'church_metrics' => true,
        'four_fields' => false,
    );

    $fields['user_workload_status'] = array(
        'active' => array(
            'label' => __( 'Accepting new contacts', 'disciple_tools' ),
            'color' => '#4caf50',
        ),
        'existing' => array(
            'label' => __( "I'm only investing in existing contacts", 'disciple_tools' ),
            'color' => '#ff9800',
        ),
        'too_many' => array(
            'label' => __( 'I have too many contacts', 'disciple_tools' ),
            'color' => '#F43636',
        ),
    );


    // $fields = apply_filters( 'dt_site_custom_lists', $fields );

    return $fields[ $list_title ] ?? $fields;
}

function dt_get_location_levels() {
    $fields = array();

    $fields['version'] = 3;

    $fields['location_levels'] = array(
        'country' => 1,
        'administrative_area_level_1' => 1,
        'administrative_area_level_2' => 1,
        'administrative_area_level_3' => 0,
        'administrative_area_level_4' => 0,
        'locality' => 0,
        'neighborhood' => 0,
    );

    $fields['location_levels_labels'] = array(
        'country' => 'Country',
        'administrative_area_level_1' => 'Admin Level 1 (ex. state / province) ',
        'administrative_area_level_2' => 'Admin Level 2 (ex. county)',
        'administrative_area_level_3' => 'Admin Level 3',
        'administrative_area_level_4' => 'Admin Level 4',
        'locality' => 'Locality (ex. city name)',
        'neighborhood' => 'Neighborhood',
    );

    return $fields;
}

/**
 * Processes the current configurations and upgrades the site options to the new version with persistent configuration settings.
 *
 * @return bool
 */
function dt_site_options_upgrade_version( string $name ) {
    $site_options_current = get_option( $name );
    if ( $name === 'dt_site_custom_lists' ){
        $site_options_defaults = dt_get_site_custom_lists();
    } else if ( $name === 'dt_site_options' ){
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

    return update_option( $name, $new_options, 'no' );
}


function dt_get_initial_install_meta( $arg = '' ){
    $at_install = get_option( 'dt_initial_install_meta', array() );
    if ( !isset( $at_install['time'] ) ){
        $at_install['time'] = 0;
    }
    if ( !isset( $at_install['migration_number'] ) ){
        $at_install['migration_number'] = 0;
    }
    if ( !isset( $at_install['theme_version'] ) ){
        $at_install['theme_version'] = 0;
    }

    if ( !empty( $arg ) ){
        if ( isset( $at_install[$arg] ) ){
            return $at_install[$arg];
        } else {
            return 0;
        }
    }

    return $at_install;
}

function dt_header_icon_and_meta(){
    ?>
    <meta charset="utf-8">
    <!-- Force IE to use the latest rendering engine available -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- Mobile Meta -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <meta class="foundation-mq">
    <?php

    /**
     * Default colors and mobile icons are provided, but can be overridden using this filter
     * These default settings are sufficient, unless you are building a progressing web app
     * then you can override them.
     */
    $dt_override_header_meta = apply_filters( 'dt_override_header_meta', false );
    if ( $dt_override_header_meta ){
        return;
    }
    //if a custom icon is set, don't override it
    if ( function_exists( 'has_site_icon' ) && has_site_icon() ){
        return;
    }
    ?>
    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo esc_url( get_template_directory_uri() ); ?>/dt-assets/favicons/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo esc_url( get_template_directory_uri() ); ?>/dt-assets/favicons/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="<?php echo esc_url( get_template_directory_uri() ); ?>/dt-assets/favicons/favicon-16x16.png">
    <link rel="manifest" href="<?php echo esc_url( get_template_directory_uri() ); ?>/dt-assets/favicons/site.webmanifest">
    <link rel="shortcut icon" href="<?php echo esc_url( get_template_directory_uri() ); ?>/dt-assets/favicons/favicon.ico">
    <meta name="msapplication-TileColor" content="#3f729b">
    <meta name="msapplication-TileImage" content="<?php echo esc_url( get_template_directory_uri() ); ?>/dt-assets/favicons/mstile-144x144.png">
    <meta name="msapplication-config" content="<?php echo esc_url( get_template_directory_uri() ); ?>/dt-assets/favicons/browserconfig.xml">
    <meta name="theme-color" content="#3f729b">
    <?php
}