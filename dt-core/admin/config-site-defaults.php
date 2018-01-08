<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly
/**
 * Default Structure
 * This is for default structure settings.
 *
 * @author  Chasm Solutions
 * @package Disciple_Tools
 */

/*********************************************************************************************
 * Action and Filters
 */

add_action( 'init', 'dt_set_permalink_structure' );
add_action( 'permalink_structure_changed', 'dt_permalink_structure_changed_callback' );
//unconditionally allow duplicate comments
add_filter( 'duplicate_comment_id', '__return_false' );
//allow multiple comments in quick succession
add_filter( 'comment_flood_filter', '__return_false' );
add_filter( 'pre_comment_approved' , 'dt_filter_handler' , '99', 2 );
add_filter( 'comment_notification_recipients', 'dt_override_comment_notice_recipients' , 10, 2 );

/*********************************************************************************************
 * Functions
 */

/**
 * Set default premalink structure
 * Needed for the rest api url structure (for wp-json to work)
 */
function dt_set_permalink_structure()
{
    global $wp_rewrite;
    $wp_rewrite->set_permalink_structure( '/%postname%/' );
    flush_rewrite_rules();
}

/**
 *
 */
function dt_warn_user_about_permalink_settings()
{
    ?>
    <div class="error notices">
        <p><?php esc_html_e( 'You may only set your permalink settings to "Post name"' ); ?></p>
    </div>
    <?php
}

/**
 * Notification that 'posttype' is the only permalink structure available.
 *
 * @param $permalink_structure
 */
function dt_permalink_structure_changed_callback( $permalink_structure )
{
    global $wp_rewrite;
    if ( $permalink_structure !== '/%postname%/' ) {
        add_action( 'admin_notices', 'dt_warn_user_about_permalink_settings' );
    }
}

function dt_override_comment_notice_recipients() {
    return [];
}

/**
 * Admin panel svg icon for disciple tools.
 *
 * @return string
 */
function dt_svg_icon()
{
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
function dt_get_option( string $name )
{

    switch ( $name ) {
        case 'dt_site_options':
            $site_options = dt_get_site_options_defaults();

            if ( !get_option( 'dt_site_options' ) ) { // options doesn't exist, create new.
                $add = add_option( 'dt_site_options', $site_options, '', true );
                if ( !$add ) {
                    return false;
                }

                return get_option( 'dt_site_options' );
            }
            elseif ( get_option( 'dt_site_options' )['version'] < $site_options['version'] ) { // option exists but version is behind
                $upgrade = dt_site_options_upgrade_version( 'dt_site_options' );
                if ( !$upgrade ) {
                    return false;
                }

                return get_option( 'dt_site_options' );
            }
            else {
                return get_option( 'dt_site_options' );
            }

            break;

        case 'dt_site_custom_lists':
            $custom_lists = dt_get_site_custom_lists();

            if ( !get_option( 'dt_site_custom_lists' ) ) { // options doen't exist, create new.
                add_option( 'dt_site_custom_lists', $custom_lists, '', true );

                return get_option( 'dt_site_custom_lists' );
            }
            elseif ( get_option( 'dt_site_custom_lists' )['version'] < $custom_lists['version'] ) { // option exists but version is behind
                $upgrade = dt_site_options_upgrade_version( 'dt_site_custom_lists' );
                if ( !$upgrade ) {
                    return false;
                }

                return get_option( 'dt_site_custom_lists' );
            }
            else {
                return get_option( 'dt_site_custom_lists' );
            }

            break;

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
                $add = add_option( 'dt_base_user', $user_id, '', false );
                if ( ! $add ) {
                    return false;
                }

                return get_option( 'dt_base_user' );
            }
            else {
                return get_option( 'dt_base_user' );
            }
            break;

        case 'map_key':
            if ( ! get_option( 'dt_map_key' ) || empty( get_option( 'dt_map_key' ) ) ) { // options doesn't exist, create new.
                // disciple.tools default map key
                $key = 'AIzaSyCcddCscCo-Uyfa3HJQVe0JdBaMCORA9eY';

                $update = update_option( 'dt_map_key', $key, true );
                if ( ! $update ) {
                    return false;
                }

                return get_option( 'dt_map_key' );
            } else {
                return get_option( 'dt_map_key' );
            }
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
function dt_get_site_options_defaults()
{
    $fields = [];

    $fields['version'] = '1.0';

    $fields['user_notifications'] = [
        'new_web'          => true,
        'new_email'        => true,
        'mentions_web'     => true,
        'mentions_email'   => true,
        'updates_web'      => true,
        'updates_email'    => false,
        'changes_web'      => false,
        'changes_email'    => false,
        'milestones_web'   => false,
        'milestones_email' => false,
    ];

    $fields['extension_modules'] = [
        'add_people_groups' => true,
        'add_assetmapping'  => true,
        'add_prayer'        => true,
        'add_worker'        => true,
    ];

    $fields['clear_data_on_deactivate'] = false; // todo need to add this option wrapper to the deactivate.php file for table deletes

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

    return $fields;
}

/**
 * Gets site configured custom lists
 * Versioning allows for additive changes. Removal of fields here in defaults will not delete the value in current installations.
 *
 * @param string|null $list_title
 *
 * @return array|mixed
 */
function dt_get_site_custom_lists( string $list_title = null )
{
    $fields = [];

    $fields['version'] = '1.0';

    // the prefix dt_user_ assists db meta queries on the user
    $fields['user_fields'] = [
        'dt_user_personal_phone'   => [
            'label'       => 'Personal Phone',
            'key'         => 'dt_user_personal_phone',
            'type'        => 'phone',
            'description' => 'Personal phone is private to the team, not for distribution.',
            'enabled'     => true,
        ],
        'dt_user_personal_email'   => [
            'label'       => 'Personal Email',
            'key'         => 'dt_user_personal_email',
            'type'        => 'email',
            'description' => 'Personal email is private to the team, not for distribution.',
            'enabled'     => true,
        ],
        'dt_user_personal_address' => [
            'label'       => 'Personal Address',
            'key'         => 'dt_user_personal_address',
            'type'        => 'address',
            'description' => 'Personal address is private to the team, not for distribution.',
            'enabled'     => true,
        ],
        'dt_user_work_phone'       => [
            'label'       => 'Work Phone',
            'key'         => 'dt_user_work_phone',
            'type'        => 'phone',
            'description' => 'Work phone is for distribution to contacts and seekers.',
            'enabled'     => true,
        ],
        'dt_user_work_email'       => [
            'label'       => 'Work Email',
            'key'         => 'dt_user_work_email',
            'type'        => 'email',
            'description' => 'Work email is for distribution to contacts and seekers.',
            'enabled'     => true,
        ],
        'dt_user_work_facebook'    => [
            'label'       => 'Work Facebook',
            'key'         => 'dt_user_work_facebook',
            'type'        => 'social',
            'description' => 'Work Facebook is for distribution to contacts and seekers.',
            'enabled'     => true,
        ],
        'dt_user_work_whatsapp'    => [
            'label'       => 'Work WhatsApp',
            'key'         => 'dt_user_work_whatsapp',
            'type'        => 'other',
            'description' => 'Work Facebook is for distribution to contacts and seekers.',
            'enabled'     => true,
        ],
    ];

    $fields['user_fields_types'] = [
        'phone'   => [
            'label' => 'Phone',
            'key'   => 'phone',
        ],
        'email'   => [
            'label' => 'Email',
            'key'   => 'email',
        ],
        'social'  => [
            'label' => 'Social Media',
            'key'   => 'social',
        ],
        'address' => [
            'label' => 'Address',
            'key'   => 'address',
        ],
        'other'   => [
            'label' => 'Other',
            'key'   => 'other',
        ],
    ];

    $fields['sources'] = [
        'web'           => [
            'label'       => 'Web',
            'key'         => 'web',
            'description' => 'Contacts coming from the website.',
            'enabled'     => true,
        ],
        'phone'         => [
            'label'       => 'Phone',
            'key'         => 'phone',
            'description' => 'Contacts coming from phone.',
            'enabled'     => true,
        ],
        'facebook'      => [
            'label'       => 'Facebook',
            'key'         => 'facebook',
            'description' => 'Contacts coming from Facebook.',
            'enabled'     => true,
        ],
        'twitter'       => [
            'label'       => 'Twitter',
            'key'         => 'twitter',
            'description' => 'Contacts coming from Twitter.',
            'enabled'     => true,
        ],
        'linkedin'      => [
            'label'       => 'LinkedIn',
            'key'         => 'linkedin',
            'description' => 'Contacts coming from the LinkedIn.',
            'enabled'     => true,
        ],
        'referral'      => [
            'label'       => 'Referral',
            'key'         => 'referral',
            'description' => 'Contacts coming from relational network.',
            'enabled'     => true,
        ],
        'advertisement' => [
            'label'       => 'Advertisement',
            'key'         => 'advertisement',
            'description' => 'Contacts coming an advertisement campaign.',
            'enabled'     => true,
        ],
    ];

    // $fields = apply_filters( 'dt_site_custom_lists', $fields );

    if ( is_null( $list_title ) ) {
        return $fields;
    } else {
        return $fields[ $list_title ];
    }
}

/**
 * Processes the current configurations and upgrades the site options to the new version with persistent configuration settings.
 *
 * @return bool
 */
function dt_site_options_upgrade_version( string $name )
{
    $site_options_current = get_option( $name );
    $site_options_defaults = dt_get_site_options_defaults();

    $new_version_number = $site_options_defaults['version'];

    if ( !is_array( $site_options_current ) ) {
        return false;
    }

    $new_options = array_replace_recursive( $site_options_defaults, $site_options_current );
    $new_options['version'] = $new_version_number;

    return update_option( $name, $new_options, true );
}

/**
 * Prepare input "type" from custom list types
 *
 * @param $type
 *
 * @return string
 */
function dt_prepare_user_fields_types_for_input( $type ) {
    switch ( $type ) {
        case 'phone':
            return 'tel';
            break;
        case 'email':
            return 'email';
            break;
        case 'social':
            return 'text';
            break;
        case 'address':
            return 'text';
            break;
        case 'other':
            return 'text';
            break;
        default:
            return 'text';
            break;
    }
}

/**
 * @param $approved
 * @param $commentdata
 *
 * @return int
 */
function dt_filter_handler( $approved, $commentdata ){
    // inspect $commentdata to determine approval, disapproval, or spam status
    //approve all comments.
    return 1;
}

