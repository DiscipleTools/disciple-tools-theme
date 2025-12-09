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
    function getLauncherSelector() {
        return document.querySelector('<?php echo '.' . esc_js( $selector_class ); ?>');
    }
    
    function toggleAppsSelector() {
        var selector = getLauncherSelector();
        if (selector) {
            selector.classList.toggle('open');
        }
    }
    
    function closeAppsSelector() {
        var selector = getLauncherSelector();
        if (selector) {
            selector.classList.remove('open');
        }
    }
    
    // Check if launcher nav exists after DOM is ready
    function checkLauncherNav() {
        var launcherNav = document.querySelector('<?php echo '.' . esc_js( $nav_class ); ?>');
        var appsSelector = document.querySelector('<?php echo '.' . esc_js( $selector_class ); ?>');
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
        var selector = getLauncherSelector();
        var appsButton = document.querySelector('<?php echo '.' . esc_js( $nav_class ); ?> .nav-item[onclick*="toggleAppsSelector"]');
        
        if (selector && appsButton) {
            var isClickInsideSelector = selector.contains(event.target);
            var isClickOnAppsButton = appsButton.contains(event.target);
            
            if (!isClickInsideSelector && !isClickOnAppsButton && selector.classList.contains('open')) {
                closeAppsSelector();
            }
        }
    });
    
    document.addEventListener('click', function(event) {
        var link = event.target.closest('.launcher-app-link');
        if (!link) {
            return;
        }
        
        event.preventDefault();
        event.stopPropagation();
        closeAppsSelector();
        
        var iframe = document.getElementById('launcher-app-iframe');
        var iframeUrl = link.getAttribute('data-app-url') || link.getAttribute('href');
        
        if (iframe && iframeUrl && iframeUrl !== '#') {
            iframe.setAttribute('src', iframeUrl);
            return false;
        }
        
        var fallbackUrl = link.getAttribute('data-launcher-url') || link.getAttribute('href');
        if (fallbackUrl && fallbackUrl !== '#') {
            window.location.href = fallbackUrl;
        }
        
        return false;
    });
    
    /**
     * Apply theme-aware icon colors to launcher navigation icons
     * Matches the logic used in home screen app icons
     * Only initialize once (check if function already exists)
     */
    if (typeof applyLauncherNavIconColors === 'undefined') {
        window.applyLauncherNavIconColors = function() {
            const launcherIcons = document.querySelectorAll('.launcher-apps-selector .launcher-app-link i, .dt-launcher-apps-selector .launcher-app-link i');
            
            launcherIcons.forEach(function(icon) {
                const hasCustomColor = icon.getAttribute('data-has-custom-color') === 'true';
                
                if (!hasCustomColor) {
                    // Apply theme-aware default (matches home screen logic)
                    const isDarkMode = document.body.classList.contains('theme-dark') ||
                                     document.documentElement.classList.contains('theme-dark') ||
                                     document.body.classList.contains('dark') ||
                                     document.documentElement.classList.contains('dark');
                    const defaultColor = isDarkMode ? '#ffffff' : '#0a0a0a';
                    icon.style.setProperty('color', defaultColor, 'important');
                }
                // If has custom color, it's already set via inline style from PHP
            });
        };
        
        // Run on page load
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', window.applyLauncherNavIconColors);
        } else {
            // DOM already loaded
            setTimeout(window.applyLauncherNavIconColors, 100);
        }
        
        // Re-run when theme changes (listen for theme toggle events)
        document.addEventListener('themeChanged', window.applyLauncherNavIconColors);
        
        // Also listen for class changes on body/html (fallback for theme changes)
        if (typeof MutationObserver !== 'undefined') {
            const themeObserver = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'attributes' && 
                        (mutation.attributeName === 'class' || mutation.target === document.body || mutation.target === document.documentElement)) {
                        window.applyLauncherNavIconColors();
                    }
                });
            });
            
            // Observe body and html for class changes
            if (document.body) {
                themeObserver.observe(document.body, { attributes: true, attributeFilter: ['class'] });
            }
            themeObserver.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
        }
    }
    
    // Run immediately for icons already in DOM
    if (typeof window.applyLauncherNavIconColors !== 'undefined') {
        setTimeout(window.applyLauncherNavIconColors, 50);
    }
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
        <a href="<?php echo esc_url( dt_home_get_logout_url() ); ?>" class="nav-item nav-item-logout" aria-label="<?php esc_attr_e( 'Log Out', 'disciple_tools' ); ?>">
            <i class="mdi mdi-logout"></i>
            <?php esc_html_e( 'Log Out', 'disciple_tools' ); ?>
        </a>
    </div>
</div>

<div class="<?php echo esc_attr( $selector_class ); ?>">
    <ul>
    <?php foreach ( $filtered_apps as $app ): ?>
        <?php
        // Build base app URL without launcher parameters
        $base_app_url = '';

        // Check if URL already contains a magic key (pattern: /templates/{type}/{64-char-hex-key} or similar)
        $has_magic_key = false;
        if ( isset( $app['url'] ) && ! empty( trim( $app['url'] ) ) && $app['url'] !== '#' ) {
            // Check if URL matches magic link pattern (contains root/type/key structure)
            $url_parts = parse_url( $app['url'] );
            $url_path = $url_parts['path'] ?? '';
            // Magic link URLs typically have at least 3 path segments: /root/type/key
            $path_segments = array_filter( explode( '/', trim( $url_path, '/' ) ) );
            if ( count( $path_segments ) >= 3 ) {
                // Likely has a magic key (third segment is usually 64+ char hash)
                $potential_key = end( $path_segments );
                if ( strlen( $potential_key ) >= 32 && ctype_xdigit( $potential_key ) ) {
                    $has_magic_key = true;
                }
            }
        }

        // Always prefer URL from get_apps_for_frontend() if it exists and has a magic key
        if ( $has_magic_key ) {
            $base_app_url = $app['url'];
        } elseif ( isset( $app['url'] ) && ! empty( trim( $app['url'] ) ) && $app['url'] !== '#' ) {
            // Use existing URL even if we can't confirm it has a magic key
            $base_app_url = $app['url'];
        } elseif ( isset( $app['creation_type'] ) && $app['creation_type'] === 'coded' ) {
            // Fallback: try to generate it for coded apps
            $generated_url = dt_home_get_app_magic_url( $app, '', false );
            if ( ! empty( $generated_url ) && $generated_url !== '#' ) {
                $base_app_url = $generated_url;
            } else {
                $base_app_url = $app['url'] ?? '#';
            }
        } else {
            $base_app_url = $app['url'] ?? '#';
        }

        // Fallback launcher URL (adds launcher=1 so page reload falls back to wrapper)
        $launcher_url = $base_app_url === '#'
            ? '#'
            : add_query_arg( 'launcher', '1', $base_app_url );

        // Iframe target URL (adds launcher_iframe=1 so nested request is detected)
        $iframe_app_url = $base_app_url === '#'
            ? '#'
            : add_query_arg( 'launcher_iframe', '1', $base_app_url );

        if ( $base_app_url === '#' ) {
            $launcher_url = '#';
            $iframe_app_url = '#';
        }

        // Get app name/title
        $app_name = $app['title'] ?? $app['name'] ?? 'App';
        $app_icon = $app['icon'] ?? 'mdi mdi-apps';

        // Check if app has a custom color (empty string or #cccccc means no custom color)
        // Match the logic used in home screen JavaScript: app.color && app.color.trim() !== ''
        $has_custom_color = !empty( $app['color'] ) &&
                           is_string( $app['color'] ) &&
                           trim( $app['color'] ) !== '' &&
                           trim( $app['color'] ) !== '#cccccc';

        if ( $has_custom_color ) {
            $app_color = trim( $app['color'] );
            $color_style = 'style="color: ' . esc_attr( $app_color ) . ';"';
        } else {
            // No custom color - don't set inline style, let JavaScript/CSS handle theme-aware defaults
            $app_color = null;
            $color_style = '';
        }
        ?>
        <li>
            <a href="<?php echo esc_url( $launcher_url ); ?>"
               class="launcher-app-link"
               data-app-url="<?php echo esc_attr( $iframe_app_url ); ?>"
               data-launcher-url="<?php echo esc_attr( $launcher_url ); ?>">
                <?php if ( str_starts_with( $app_icon, 'http' ) || str_starts_with( $app_icon, '/' ) ): ?>
                    <img src="<?php echo esc_url( $app_icon ); ?>"
                         alt="<?php echo esc_attr( $app_name ); ?>"
                         <?php echo wp_kses( $color_style, [ 'style' => [] ] ); ?>
                         data-has-custom-color="<?php echo $has_custom_color ? 'true' : 'false'; ?>" />
                <?php else : ?>
                    <i class="<?php echo esc_attr( $app_icon ); ?>"
                       aria-hidden="true"
                       <?php echo wp_kses( $color_style, [ 'style' => [] ] ); ?>
                       data-has-custom-color="<?php echo $has_custom_color ? 'true' : 'false'; ?>"></i>
                <?php endif; ?>
                <span class="name"><?php echo esc_html( $app_name ); ?></span>
            </a>
        </li>
    <?php endforeach; ?>
    </ul>
</div>

