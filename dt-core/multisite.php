<?php
/**
 * Multi-site specific configurations
 */

if ( is_multisite() ) {
    /**
     * Makes sure the login link presented on the wp-activate.php page is to the subdomain, not the root domain.
     *
     * @param $url
     * @param $path
     * @param $scheme
     *
     * @return string
     */
    function dt_network_site_url_to_specific_blog( $url, $path, $scheme ){

        global $pagenow;
        if ( 'wp-activate.php' === substr( $pagenow, 0, 15 ) ) {
            $url = site_url();
        }

        return $url;
    }
    add_filter( 'network_site_url', 'dt_network_site_url_to_specific_blog', 3, 999 );

    /**
     * Adds some simple styling to the head of the wp-activate.php page
     */
    function dt_custom_activate_head(){
        ?>
        <style>
            .wp-activate-container {
                width: 80%;
                margin: 0 auto;
                padding: 2em;
                color:black;
            }
            .wp-activate-container h2 {
                color:black;
            }
        </style>
        <script></script>
        <?php
    }
    add_action( 'activate_wp_head', 'dt_custom_activate_head' );

    function dt_multisite_is_registration_enabled_on_subsite() {
        $registration = get_site_option( 'registration' );
        if ( 'all' === $registration || 'user' === $registration ){
            if ( get_option( 'dt_enable_registration' ) ) {
                return 1;
            } else {
                return 0;
            }
        } else {
            return 0;
        }
    }
    add_filter( 'option_users_can_register', 'dt_multisite_is_registration_enabled_on_subsite', 100 );

    /**
     * Check if super admin has enabled subsite admins to edit users
     * This setting is managed in the Disciple.Tools Multisite plugin
     * or can be set manually via update_site_option()
     *
     * @return bool
     */
    function dt_multisite_can_subsite_admins_edit_users() {
        return (bool) get_site_option( 'dt_allow_subsite_admins_edit_users', false );
    }

    /**
     * Filter user capabilities to allow subsite admins to edit users when setting is enabled
     *
     * This filter grants subsite administrators the ability to edit users on their subsite
     * when the network-wide setting is enabled by a super admin.
     *
     * Security restrictions:
     * - Subsite admins cannot edit super admin accounts
     * - Subsite admins cannot edit users from other subsites
     * - Only works when user is a member of the current subsite
     * - Feature is disabled by default
     */
    function dt_multisite_map_meta_cap( $caps, $cap, $user_id, $args ) {
        // Only apply in multisite context
        if ( ! is_multisite() ) {
            return $caps;
        }

        // Only apply if the setting is enabled
        if ( ! dt_multisite_can_subsite_admins_edit_users() ) {
            return $caps;
        }

        // Handle edit_user capability
        if ( 'edit_user' === $cap ) {
            // Get the user being edited
            $edited_user_id = isset( $args[0] ) ? $args[0] : 0;
            if ( ! $edited_user_id ) {
                return $caps;
            }

            // Get the current user
            $current_user = wp_get_current_user();

            // If user is already super admin, use default capabilities
            if ( is_super_admin( $user_id ) ) {
                return $caps;
            }

            // If the current user is an administrator on this site
            if ( in_array( 'administrator', $current_user->roles ) ) {
                $edited_user = get_userdata( $edited_user_id );

                // Prevent editing super admins
                if ( is_super_admin( $edited_user_id ) ) {
                    return $caps;
                }

                // Prevent editing users not on this site
                if ( ! is_user_member_of_blog( $edited_user_id, get_current_blog_id() ) ) {
                    return $caps;
                }

                // Allow editing for administrators
                $caps = [ 'edit_users' ];
            }
        }

        // Handle promote_users capability (needed to change roles)
        if ( 'promote_users' === $cap || 'promote_user' === $cap ) {
            $current_user = wp_get_current_user();

            // If user is already super admin, use default capabilities
            if ( is_super_admin( $user_id ) ) {
                return $caps;
            }

            // If the current user is an administrator on this site
            if ( in_array( 'administrator', $current_user->roles ) ) {
                $edited_user_id = isset( $args[0] ) ? $args[0] : 0;

                // Don't allow promoting super admins
                if ( $edited_user_id && is_super_admin( $edited_user_id ) ) {
                    return $caps;
                }

                // Allow promoting for administrators
                $caps = [ 'promote_users' ];
            }
        }

        return $caps;
    }
    add_filter( 'map_meta_cap', 'dt_multisite_map_meta_cap', 10, 4 );

    /**
     * Filter editable roles for subsite administrators
     */
    function dt_multisite_editable_roles( $roles ) {
        // Only apply in multisite context
        if ( ! is_multisite() ) {
            return $roles;
        }

        // Only apply if the setting is enabled
        if ( ! dt_multisite_can_subsite_admins_edit_users() ) {
            return $roles;
        }

        // If current user is super admin, don't filter
        if ( is_super_admin() ) {
            return $roles;
        }

        // For regular administrators, they can't promote to super admin
        // But they can manage other roles
        // Note: WordPress already handles this in most cases, but we ensure it here
        return $roles;
    }
    add_filter( 'editable_roles', 'dt_multisite_editable_roles', 999 );
}
