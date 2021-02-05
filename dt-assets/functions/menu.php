<?php

function dt_default_menu_array(){
    return [
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
                'icon' => get_template_directory_uri() . "/dt-assets/images/gear.svg",
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
                        'hidden' => false,
                    ],
                    'user_management' => [
                        'label' => __( "Users", "disciple_tools" ),
                        'link' => site_url( '/user-management/users/' ),
                        'hidden' => false,
                    ],
                    'help' => [
                        'label' => __( "Help", "disciple_tools" ),
                        'link' => 'https://disciple.tools/user-docs',
                        'hidden' => false,
                    ],
                    'logoff' => [
                        'label' => __( "Log Off", 'disciple_tools' ),
                        'link' => wp_logout_url(),
                        'hidden' => false,
                    ],
                ]
            ],
        ],
    ];
}
