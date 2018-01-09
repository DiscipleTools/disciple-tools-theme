<?php
/**
 * Test for minimum required PHP version
 */
if ( version_compare( phpversion(), '7.0', '<' ) ) {

    /* We only support PHP >= 7.0, however, we want to support allowing users
     * to install this theme even on old versions of PHP, without showing a
     * horrible message, but instead a friendly notice.
     *
     * For this to work, this file must be compatible with old PHP versions.
     * Feel free to use PHP 7 features in other files, but not in this one.
     */

    new WP_Error( 'php_version_fail', 'Disciple Tools theme requires PHP version 7.0 or greater, please upgrade PHP or uninstall this theme' );
    add_action( 'admin_notices', 'dt_theme_admin_notice_required_php_version' );
    add_action( 'after_switch_theme', 'dt_theme_after_switch_theme_switch_back' );

    return;
}
/**
 * Error handler for PHP version fail
 *
 * @return bool
 */
function dt_theme_after_switch_theme_switch_back()
{
    switch_theme( get_option( 'theme_switched' ) );

    return false;
}
/**
 * Php Version Alert
 */
function dt_theme_admin_notice_required_php_version()
{
    ?>
    <div class="notice notice-error">
        <p><?php esc_html_e( "The Disciple Tools theme requires PHP 7.0 or greater before it will have any effect. Please upgrade your PHP version or uninstall this theme.", "disciple_tools" ); ?></p>
    </div>
    <?php
}