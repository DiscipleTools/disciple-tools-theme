<?php
/**
 * Launcher Bottom Navigation Partial
 *
 * Displays the bottom navigation bar for app-type magic link apps.
 * Only shown on app-type pages (not home screen or link-type apps).
 *
 * @var array $apps Array of all apps for the apps selector
 * @var bool $is_wrapper_context Optional. If true, uses wrapper-specific class names to avoid conflicts.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Use wrapper-specific class names if in wrapper context
$nav_class = isset( $is_wrapper_context ) && $is_wrapper_context ? 'dt-launcher-bottom-nav' : 'launcher-bottom-nav';
$selector_class = isset( $is_wrapper_context ) && $is_wrapper_context ? 'dt-launcher-apps-selector' : 'launcher-apps-selector';

// Include helper functions
require_once get_template_directory() . '/dt-apps/dt-home/includes/class-home-helpers.php';

// Filter apps to only show app-type apps
// Include both coded apps (with magic_link_meta) and custom apps (without magic_link_meta)
$filtered_apps = array_filter( $apps, function ( $app ) {
    // Must be app-type
    if ( ! isset( $app['type'] ) || $app['type'] !== 'app' ) {
        return false;
    }

    // For coded apps, must have magic_link_meta
    if ( isset( $app['creation_type'] ) && $app['creation_type'] === 'coded' ) {
        return isset( $app['magic_link_meta'] ) && ! empty( $app['magic_link_meta'] );
    }

    // For custom apps, just need to be app-type (they may not have magic_link_meta)
    return true;
});

// Debug logging
error_log( 'DT Home Launcher Nav: Total apps: ' . count( $apps ) );
error_log( 'DT Home Launcher Nav: Filtered app-type apps: ' . count( $filtered_apps ) );
?>
<script type="application/javascript">
    console.log('DT Home Launcher Nav: Script loaded');
    console.log('DT Home Launcher Nav: Filtered apps count:', <?php echo count( $filtered_apps ); ?>);
    
    function toggleAppsSelector() {
        var selector = document.querySelector('<?php echo '.' . esc_js( $selector_class ); ?>');
        if (selector) {
            selector.classList.toggle('open');
        }
    }
    
    // Check if launcher nav exists after DOM is ready
    function checkLauncherNav() {
        var launcherNav = document.querySelector('<?php echo '.' . esc_js( $nav_class ); ?>');
        var appsSelector = document.querySelector('<?php echo '.' . esc_js( $selector_class ); ?>');
        console.log('DT Home Launcher Nav: Launcher nav element exists:', launcherNav !== null);
        console.log('DT Home Launcher Nav: Apps selector exists:', appsSelector !== null);
        if (launcherNav) {
            console.log('DT Home Launcher Nav: Launcher nav is in DOM');
        } else {
            console.log('DT Home Launcher Nav: WARNING - Launcher nav NOT found in DOM!');
        }
    }
    
    // Run check immediately and after DOM ready
    checkLauncherNav();
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', checkLauncherNav);
    } else {
        setTimeout(checkLauncherNav, 100);
    }
    
    // Close apps selector when clicking outside
    document.addEventListener('click', function(event) {
        var selector = document.querySelector('<?php echo '.' . esc_js( $selector_class ); ?>');
        var appsButton = document.querySelector('<?php echo '.' . esc_js( $nav_class ); ?> .nav-item[onclick*="toggleAppsSelector"]');
        
        if (selector && appsButton) {
            var isClickInsideSelector = selector.contains(event.target);
            var isClickOnAppsButton = appsButton.contains(event.target);
            
            if (!isClickInsideSelector && !isClickOnAppsButton && selector.classList.contains('open')) {
                selector.classList.remove('open');
            }
        }
    });
</script>
<div class="<?php echo esc_attr( $nav_class ); ?>">
    <div class="nav-container">
        <button class="nav-item" onclick="toggleAppsSelector()" aria-label="<?php esc_attr_e( 'Apps', 'disciple_tools' ); ?>">
            <i class="mdi mdi-apps"></i>
            <?php esc_html_e( 'Apps', 'disciple_tools' ); ?>
        </button>
        <a href="<?php echo esc_url( dt_home_magic_url( '' ) ); ?>" class="nav-item nav-item-home" aria-label="<?php esc_attr_e( 'Home', 'disciple_tools' ); ?>">
            <i class="mdi mdi-home"></i>
        </a>
        <a href="<?php echo esc_url( dt_home_magic_url( 'logout' ) ); ?>" class="nav-item" aria-label="<?php esc_attr_e( 'Log Out', 'disciple_tools' ); ?>">
            <i class="mdi mdi-logout"></i>
            <?php esc_html_e( 'Log Out', 'disciple_tools' ); ?>
        </a>
    </div>
</div>

<div class="<?php echo esc_attr( $selector_class ); ?>">
    <ul>
    <?php foreach ( $filtered_apps as $app ): ?>
        <?php
        // Get the app URL - use magic URL helper for coded apps, otherwise use stored URL
        // Add launcher=1 parameter for app-type apps
        $app_url = '';
        if ( isset( $app['creation_type'] ) && $app['creation_type'] === 'coded' ) {
            $app_url = dt_home_get_app_magic_url( $app, '', true ); // Add launcher parameter
        } else {
            $app_url = $app['url'] ?? '#';
            // Add launcher parameter to non-coded app URLs too
            if ( $app_url !== '#' ) {
                $separator = strpos( $app_url, '?' ) !== false ? '&' : '?';
                $app_url .= $separator . 'launcher=1';
            }
        }

        // Get app name/title
        $app_name = $app['title'] ?? $app['name'] ?? 'App';
        $app_icon = $app['icon'] ?? 'mdi mdi-apps';
        $app_color = $app['color'] ?? '#667eea';
        ?>
        <li>
            <a href="<?php echo esc_url( $app_url ); ?>" 
               class="launcher-app-link" 
               data-app-url="<?php echo esc_attr( $app_url ); ?>"
               onclick="event.preventDefault(); event.stopPropagation(); var selector = document.querySelector('<?php echo '.' . esc_js( $selector_class ); ?>'); if(selector) selector.classList.remove('open'); window.location.href = '<?php echo esc_js( $app_url ); ?>'; return false;">
                <?php if ( str_starts_with( $app_icon, 'http' ) || str_starts_with( $app_icon, '/' ) ): ?>
                    <img src="<?php echo esc_url( $app_icon ); ?>" alt="<?php echo esc_attr( $app_name ); ?>" style="color: <?php echo esc_attr( $app_color ); ?>;" />
                <?php else : ?>
                    <i class="<?php echo esc_attr( $app_icon ); ?>" 
                       aria-hidden="true"
                       style="color: <?php echo esc_attr( $app_color ); ?>;"></i>
                <?php endif; ?>
                <span class="name"><?php echo esc_html( $app_name ); ?></span>
            </a>
        </li>
    <?php endforeach; ?>
    </ul>
</div>

