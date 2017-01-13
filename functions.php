<?php
/**
 * DmmCrm functions and definitions
 *
 * @package DmmCrm
 */

/**
 * Load theme setup functions inherited from SoSimple regarding appearance, fonts, supports, etc.
 */
require get_template_directory() . '/inc/dmmcrm-theme-setup.php';

/**
 * Load admin panel functions to control the experience of the admin panel.
 */
require get_template_directory() . '/inc/dmmcrm-admin-setup.php';


/**
 * Load extended portal experience functions.
 */
require get_template_directory() . '/inc/dmmcrm-portal-setup.php';

/**
 * Load security modifications to site.
 */
require get_template_directory() . '/inc/dmmcrm-security-setup.php';

/**
 * Load roles.
 */
require get_template_directory() . '/inc/dmmcrm-roles.php';

/**
 * Load Custom Post Types.
 */
require get_template_directory() . '/inc/dmmcrm-post-types.php';

/**
 * Implement the Custom Header feature.
 */
require get_template_directory() . '/inc/custom-header.php';

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Custom functions that act independently of the theme templates.
 */
require get_template_directory() . '/inc/extras.php';

/**
 * Customizer additions.
 */
require get_template_directory() . '/inc/customizer.php';

/**
 * Load Jetpack compatibility file.
 */
require get_template_directory() . '/inc/jetpack.php';



