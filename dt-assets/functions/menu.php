<?php
/**
 * This array is the primary structure for the Disciple.Tools navigation
 *
 * FILTER: dt_nav
 * This filter is the top filter for the navigation. If you add_filter and modify this array, you can change any link
 * icon or title. You can also prepend or append new links to the main or admin sections.
 *
 * Note: All sections are build with label, link, icon, and hidden (default false). You can add a link to any section by
 * adding an array element with these four components.
 *
 * `main`
 * The main section is the main navigation. Usually, contacts, groups, metrics. By adding a submenu element to any of these
 * you can add a dropdown to them, or you can add a new element by just prepending or appending a new array item. It is
 * recommended to add your new array item with an associative key, but it is not required.
 *
 * `admin`
 * This section drives the administrative section of the menu. Unlike the main section array order does not effect the
 * display order, except in the submenus.
 *
 * SUB SECTION FILTER: desktop_navbar_menu_options
 * This filter is a convenience filter allowing especially post types to add a menu item and using the add_filter load order
 * to order the menu array. The filter is used by post-types. This filter could also be used by plugins to add custom sections.
 * @see custom-post-type.php file for example usage
 * @link /dt-posts/custom-post-type.php:111
 *
 * SUB SECTION FILTER: dt_nav_add_post_menu
 * This filter adds menu items to the add new drop down.
 * @see custom-post-type.php for example usage
 * @link /dt-posts/custom-post-type.php:124
 *
 * @return array
 */
function dt_default_menu_array() : array {
    return apply_filters( "dt_nav", [
        'main' => apply_filters( 'desktop_navbar_menu_options', [] ),
        'admin' => [
            'menu' => [
                'label' => __( 'Menu', 'disciple_tools' ),
                'link' => '',
                'icon' => get_template_directory_uri() . '/dt-assets/images/hamburger.svg',
                'hidden' => false,
            ],
            'site' => [
                'label' => __( 'Disciple.Tools', 'disciple_tools' ),
                'link' => site_url(),
                'icon' => get_template_directory_uri() . "/dt-assets/images/disciple-tools-logo-white.png",
                'hidden' => false,
            ],
            'profile' => [
                'label' => wp_get_current_user()->display_name ?? __( "Profile", "disciple_tools" ),
                'link' => site_url( '/settings/' ),
                'icon' => get_template_directory_uri() . "/dt-assets/images/profile.svg",
                'hidden' => false,
            ],
            'add_new' => [
                'label' => __( "Add New", 'disciple_tools' ),
                'link' => '',
                'hidden' => false,
                'icon' => get_template_directory_uri() . "/dt-assets/images/circle-add-plus.svg",
                'submenu' => apply_filters( 'dt_nav_add_post_menu', [] ),
            ],
            'notifications' => [
                'label' => __( "Notifications", 'disciple_tools' ),
                'link' => site_url( '/notifications/' ),
                "icon" => get_template_directory_uri() . "/dt-assets/images/bell.svg",
                'hidden' => false,
            ],
            'settings' => [
                'label' => __( "Settings", 'disciple_tools' ),
                'link' => site_url( '/settings/' ),
                'icon' => get_template_directory_uri() . "/dt-assets/images/settings.svg" ,
                'hidden' => false,
                'submenu' => [
                    'settings' => [
                        'label' => __( "Settings", 'disciple_tools' ),
                        'link' => site_url( '/settings/' ),
                        'hidden' => false,
                    ],
                    'admin' => [
                        'label' => __( "Admin", 'disciple_tools' ),
                        'link' => get_admin_url(),
                        'hidden' => ( ! current_user_can( 'manage_dt' ) ),
                    ],
                    'user_management' => [
                        'label' => __( "Users", "disciple_tools" ),
                        'link' => site_url( '/user-management/users/' ),
                        'hidden' => ( ! ( current_user_can( 'manage_dt' ) || current_user_can( 'list_users' ) ) ),
                    ],
                    'help' => [
                        'label' => __( "Help", "disciple_tools" ),
                        'link' => 'https://disciple.tools/user-docs',
                        'hidden' => false,
                    ],
                ]
            ],
        ],
    ] );
}
