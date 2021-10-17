<?php
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly
/**
 * Require plugins with the TGM library.
 *
 * This defines the required and suggested plugins.
 */


/**
 * Include the TGM_Plugin_Activation class. This class makes other plugins required for the Disciple_Tools system.
 * @see https://github.com/TGMPA/TGM-Plugin-Activation
 */


/**
 * Register the required plugins for this theme.
 *
// Example of array options:
//
//        array(
//        'name'               => 'REST API Console', // The plugin name.
//        'slug'               => 'rest-api-console', // The plugin slug (typically the folder name).
//        'source'             => dirname( __FILE__ ) . '/lib/plugins/rest-api-console.zip', // The plugin source.
//        'required'           => true, // If false, the plugin is only 'recommended' instead of required.
//        'version'            => '', // E.g. 1.0.0. If set, the active plugin must be this version or higher. If the plugin version is higher than the plugin version installed, the user will be notified to update the plugin.
//        'force_activation'   => false, // If true, plugin is activated upon theme activation and cannot be deactivated until theme switch.
//        'force_deactivation' => false, // If true, plugin is deactivated upon theme switch, useful for theme-specific plugins.
//        'external_url'       => '', // If set, overrides default API URL and points to an external URL.
//        'is_callable'        => '', // If set, this callable will be be checked for availability to determine if a plugin is active.
//        ),
//
 */
function dt_register_required_plugins() {
    /*
     * Array of plugin arrays. Required keys are name and slug.
     * If the source is NOT from the .org repo, then source is also required.
     */
    $plugins = [
        [
            'name'                  => 'iThemes Security',
            'slug'                  => 'better-wp-security',
            'required'              => false,
            'version'               => '7.2.0',
        ]
    ];
    if ( is_multisite() ){
        $plugins[] = [
            'name' => 'Disciple.Tools Multisite Helper',
            'slug' => 'disciple-tools-multisite',
            'source' => 'https://github.com/DiscipleTools/disciple-tools-multisite/releases/latest/download/disciple-tools-multisite.zip',
            'required' => false
        ];
    }

    /*
     * Array of configuration settings. Amend each line as needed.
     *
     * Only uncomment the strings in the config array if you want to customize the strings.
     */
    $config = array(
        'id'           => 'disciple_tools',                 // Unique ID for hashing notices for multiple instances of TGMPA.
        'default_path' => '/includes/plugins/',     // Default absolute path to bundled plugins.
        'menu'         => 'tgmpa-install-plugins', // Menu slug.
        'parent_slug'  => 'plugins.php',            // Parent menu slug.
        'capability'   => 'manage_options',    // Capability needed to view plugin install page, should be a capability associated with the parent menu used.
        'has_notices'  => true,                    // Show admin notices or not.
        'dismissable'  => true,                    // If false, a user cannot dismiss the nag message.
        'dismiss_msg'  => 'These are recommended plugins to complement your disciple tools system.',                      // If 'dismissable' is false, this message will be output at top of nag.
        'is_automatic' => true,                   // Automatically activate plugins after installation or not.
        'message'      => '',                      // Message to output right before the plugins table.
    );

    tgmpa( $plugins, $config );
}
add_action( 'tgmpa_register', 'dt_register_required_plugins' );
