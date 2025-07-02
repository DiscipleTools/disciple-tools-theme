<?php
/*
Template Name: Admin
*/
dt_please_log_in();

if ( !current_user_can( 'manage_dt' ) ) {
    wp_safe_redirect( '/registered' );
    exit();
}

// Parse URL to determine active section and subsection
$url_path = dt_get_url_path();
$path_parts = explode( '/', trim( $url_path, '/' ) );
// Skip the 'dt-admin' part and get the actual section/subsection
$active_section = isset( $path_parts[1] ) ? sanitize_text_field( $path_parts[1] ) : 'mapping';
$active_subsection = isset( $path_parts[2] ) ? sanitize_text_field( $path_parts[2] ) : 'overview';

?>

<?php get_header(); ?>

<div class="dt-admin-wrapper">
    <!-- Mobile Overlay -->
    <div class="mobile-overlay" id="mobile-overlay"></div>
    
    <div class="dt-admin-container">
        <!-- Left Sidebar -->
        <aside class="dt-admin-sidebar">
            <nav class="admin-nav">
                
                <!-- Mapping Section -->
                <div class="nav-section <?php echo $active_section === 'mapping' ? 'active' : ''; ?>">
                    <div class="nav-section-header">
                        <a href="<?php echo site_url( '/dt-admin/mapping/' ); ?>" class="nav-section-link">
                            <svg class="nav-icon" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                            </svg>
                            <span><?php esc_html_e( 'Mapping', 'disciple_tools' ); ?></span>
                        </a>
                        <button class="nav-toggle" type="button" aria-expanded="<?php echo $active_section === 'mapping' ? 'true' : 'false'; ?>">
                            <svg class="nav-toggle-icon" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M7 10l5 5 5-5z"/>
                            </svg>
                        </button>
                    </div>
                    <div class="nav-submenu <?php echo $active_section === 'mapping' ? 'expanded' : ''; ?>">
                        <a href="<?php echo site_url( '/dt-admin/mapping/overview' ); ?>" 
                           class="nav-submenu-link <?php echo $active_section === 'mapping' && $active_subsection === 'overview' ? 'active' : ''; ?>">
                            <?php esc_html_e( 'Overview', 'disciple_tools' ); ?>
                        </a>
                        <a href="<?php echo site_url( '/dt-admin/mapping/location-grid' ); ?>" 
                           class="nav-submenu-link <?php echo $active_section === 'mapping' && $active_subsection === 'location-grid' ? 'active' : ''; ?>">
                            <?php esc_html_e( 'Location Grid', 'disciple_tools' ); ?>
                        </a>
                        <a href="<?php echo site_url( '/dt-admin/mapping/geocoding' ); ?>" 
                           class="nav-submenu-link <?php echo $active_section === 'mapping' && $active_subsection === 'geocoding' ? 'active' : ''; ?>">
                            <?php esc_html_e( 'Geocoding', 'disciple_tools' ); ?>
                        </a>
                        <a href="<?php echo site_url( '/dt-admin/mapping/layers' ); ?>" 
                           class="nav-submenu-link <?php echo $active_section === 'mapping' && $active_subsection === 'layers' ? 'active' : ''; ?>">
                            <?php esc_html_e( 'Map Layers', 'disciple_tools' ); ?>
                        </a>
                    </div>
                </div>

                <!-- Settings Section -->
                <div class="nav-section <?php echo $active_section === 'settings' ? 'active' : ''; ?>">
                    <div class="nav-section-header">
                        <a href="<?php echo site_url( '/dt-admin/settings/' ); ?>" class="nav-section-link">
                            <svg class="nav-icon" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M19.14,12.94c0.04-0.3,0.06-0.61,0.06-0.94c0-0.32-0.02-0.64-0.07-0.94l2.03-1.58c0.18-0.14,0.23-0.41,0.12-0.61 l-1.92-3.32c-0.12-0.22-0.37-0.29-0.59-0.22l-2.39,0.96c-0.5-0.38-1.03-0.7-1.62-0.94L14.4,2.81c-0.04-0.24-0.24-0.41-0.48-0.41 h-3.84c-0.24,0-0.43,0.17-0.47,0.41L9.25,5.35C8.66,5.59,8.12,5.92,7.63,6.29L5.24,5.33c-0.22-0.08-0.47,0-0.59,0.22L2.74,8.87 C2.62,9.08,2.66,9.34,2.86,9.48l2.03,1.58C4.84,11.36,4.82,11.69,4.82,12s0.02,0.64,0.07,0.94l-2.03,1.58 c-0.18,0.14-0.23,0.41-0.12,0.61l1.92,3.32c0.12,0.22,0.37,0.29,0.59,0.22l2.39-0.96c0.5,0.38,1.03,0.7,1.62,0.94l0.36,2.54 c0.05,0.24,0.24,0.41,0.48,0.41h3.84c0.24,0,0.44-0.17,0.47-0.41l0.36-2.54c0.59-0.24,1.13-0.56,1.62-0.94l2.39,0.96 c0.22,0.08,0.47,0,0.59-0.22l1.92-3.32c0.12-0.22,0.07-0.47-0.12-0.61L19.14,12.94z M12,15.6c-1.98,0-3.6-1.62-3.6-3.6 s1.62-3.6,3.6-3.6s3.6,1.62,3.6,3.6S13.98,15.6,12,15.6z"/>
                            </svg>
                            <span><?php esc_html_e( 'Settings', 'disciple_tools' ); ?></span>
                        </a>
                        <button class="nav-toggle" type="button" aria-expanded="<?php echo $active_section === 'settings' ? 'true' : 'false'; ?>">
                            <svg class="nav-toggle-icon" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M7 10l5 5 5-5z"/>
                            </svg>
                        </button>
                    </div>
                    <div class="nav-submenu <?php echo $active_section === 'settings' ? 'expanded' : ''; ?>">
                        <a href="<?php echo site_url( '/dt-admin/settings/general' ); ?>" 
                           class="nav-submenu-link <?php echo $active_section === 'settings' && $active_subsection === 'general' ? 'active' : ''; ?>">
                            <?php esc_html_e( 'General', 'disciple_tools' ); ?>
                        </a>
                        <a href="<?php echo site_url( '/dt-admin/settings/custom-fields' ); ?>" 
                           class="nav-submenu-link <?php echo $active_section === 'settings' && $active_subsection === 'custom-fields' ? 'active' : ''; ?>">
                            <?php esc_html_e( 'Custom Fields', 'disciple_tools' ); ?>
                        </a>
                        <a href="<?php echo site_url( '/dt-admin/settings/custom-lists' ); ?>" 
                           class="nav-submenu-link <?php echo $active_section === 'settings' && $active_subsection === 'custom-lists' ? 'active' : ''; ?>">
                            <?php esc_html_e( 'Custom Lists', 'disciple_tools' ); ?>
                        </a>
                        <a href="<?php echo site_url( '/dt-admin/settings/roles' ); ?>" 
                           class="nav-submenu-link <?php echo $active_section === 'settings' && $active_subsection === 'roles' ? 'active' : ''; ?>">
                            <?php esc_html_e( 'Roles & Permissions', 'disciple_tools' ); ?>
                        </a>
                        <a href="<?php echo site_url( '/dt-admin/settings/security' ); ?>" 
                           class="nav-submenu-link <?php echo $active_section === 'settings' && $active_subsection === 'security' ? 'active' : ''; ?>">
                            <?php esc_html_e( 'Security', 'disciple_tools' ); ?>
                        </a>
                    </div>
                </div>

                <!-- Plugins Section -->
                <div class="nav-section <?php echo $active_section === 'plugins' ? 'active' : ''; ?>">
                    <div class="nav-section-header">
                        <a href="<?php echo site_url( '/dt-admin/plugins/' ); ?>" class="nav-section-link">
                            <svg class="nav-icon" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M20.5 11H19V7c0-1.1-.9-2-2-2h-4V3.5C13 2.12 11.88 1 10.5 1S8 2.12 8 3.5V5H4c-1.1 0-2 .9-2 2v3.8h1.5c1.1 0 2 .9 2 2s-.9 2-2 2H2V19c0 1.1.9 2 2 2h3.8v-1.5c0-1.1.9-2 2-2s2 .9 2 2V21H17c1.1 0 2-.9 2-2v-4h1.5c1.1 0 2-.9 2-2s-.9-2-2-2z"/>
                            </svg>
                            <span><?php esc_html_e( 'Plugins', 'disciple_tools' ); ?></span>
                        </a>
                        <button class="nav-toggle" type="button" aria-expanded="<?php echo $active_section === 'plugins' ? 'true' : 'false'; ?>">
                            <svg class="nav-toggle-icon" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M7 10l5 5 5-5z"/>
                            </svg>
                        </button>
                    </div>
                    <div class="nav-submenu <?php echo $active_section === 'plugins' ? 'expanded' : ''; ?>">
                        <a href="<?php echo site_url( '/dt-admin/plugins/installed' ); ?>" 
                           class="nav-submenu-link <?php echo $active_section === 'plugins' && $active_subsection === 'installed' ? 'active' : ''; ?>">
                            <?php esc_html_e( 'Installed', 'disciple_tools' ); ?>
                        </a>
                        <a href="<?php echo site_url( '/dt-admin/plugins/available' ); ?>" 
                           class="nav-submenu-link <?php echo $active_section === 'plugins' && $active_subsection === 'available' ? 'active' : ''; ?>">
                            <?php esc_html_e( 'Available', 'disciple_tools' ); ?>
                        </a>
                        <a href="<?php echo site_url( '/dt-admin/plugins/settings' ); ?>" 
                           class="nav-submenu-link <?php echo $active_section === 'plugins' && $active_subsection === 'settings' ? 'active' : ''; ?>">
                            <?php esc_html_e( 'Settings', 'disciple_tools' ); ?>
                        </a>
                    </div>
                </div>

                <!-- Tools Section -->
                <div class="nav-section <?php echo $active_section === 'tools' ? 'active' : ''; ?>">
                    <div class="nav-section-header">
                        <a href="<?php echo site_url( '/dt-admin/tools/' ); ?>" class="nav-section-link">
                            <svg class="nav-icon" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M22.7 19l-9.1-9.1c.9-2.3.4-5-1.5-6.9-2-2-5-2.4-7.4-1.3L9 6 6 9 1.6 4.7C.4 7.1.9 10.1 2.9 12.1c1.9 1.9 4.6 2.4 6.9 1.5l9.1 9.1c.4.4 1 .4 1.4 0l2.3-2.3c.5-.4.5-1.1.1-1.4z"/>
                            </svg>
                            <span><?php esc_html_e( 'Tools', 'disciple_tools' ); ?></span>
                        </a>
                        <button class="nav-toggle" type="button" aria-expanded="<?php echo $active_section === 'tools' ? 'true' : 'false'; ?>">
                            <svg class="nav-toggle-icon" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M7 10l5 5 5-5z"/>
                            </svg>
                        </button>
                    </div>
                    <div class="nav-submenu <?php echo $active_section === 'tools' ? 'expanded' : ''; ?>">
                        <a href="<?php echo site_url( '/dt-admin/tools/data' ); ?>" 
                           class="nav-submenu-link <?php echo $active_section === 'tools' && $active_subsection === 'data' ? 'active' : ''; ?>">
                            <?php esc_html_e( 'Data Management', 'disciple_tools' ); ?>
                        </a>
                        <a href="<?php echo site_url( '/dt-admin/tools/logs' ); ?>" 
                           class="nav-submenu-link <?php echo $active_section === 'tools' && $active_subsection === 'logs' ? 'active' : ''; ?>">
                            <?php esc_html_e( 'System Logs', 'disciple_tools' ); ?>
                        </a>
                        <a href="<?php echo site_url( '/dt-admin/tools/jobs' ); ?>" 
                           class="nav-submenu-link <?php echo $active_section === 'tools' && $active_subsection === 'jobs' ? 'active' : ''; ?>">
                            <?php esc_html_e( 'Background Jobs', 'disciple_tools' ); ?>
                        </a>
                        <a href="<?php echo site_url( '/dt-admin/tools/database' ); ?>" 
                           class="nav-submenu-link <?php echo $active_section === 'tools' && $active_subsection === 'database' ? 'active' : ''; ?>">
                            <?php esc_html_e( 'Database', 'disciple_tools' ); ?>
                        </a>
                    </div>
                </div>

            </nav>
        </aside>

        <!-- Main Content Area -->
        <main class="dt-admin-main">
            <!-- Mobile Menu Button -->
            <button class="mobile-menu-toggle" id="mobile-menu-toggle" aria-label="Toggle menu">
                <svg class="menu-icon" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M3 18h18v-2H3v2zm0-5h18v-2H3v2zm0-7v2h18V6H3z"/>
                </svg>
                <svg class="close-icon" viewBox="0 0 24 24" fill="currentColor" style="display: none;">
                    <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
                </svg>
            </button>

            <!-- Page Header -->
            <div class="page-header">
                <h1 class="page-title" id="page-title">
                    <?php echo esc_html( ucfirst( str_replace( '-', ' ', $active_section ) ) . ' - ' . ucfirst( str_replace( '-', ' ', $active_subsection ) ) ); ?>
                </h1>
                <p class="page-description" id="page-description">
                    <!-- Will be populated by JavaScript -->
                </p>
            </div>

            <!-- Loading State -->
            <div id="loading-state" class="loading-state">
                <div class="loading-spinner"></div>
                <p><?php esc_html_e( 'Loading...', 'disciple_tools' ); ?></p>
            </div>

            <!-- Content Area -->
            <div id="main-content" class="main-content" style="display: none;">
                
                <!-- Stats Cards -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-value">42</div>
                        <div class="stat-label"><?php esc_html_e( 'Total Items', 'disciple_tools' ); ?></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value">15</div>
                        <div class="stat-label"><?php esc_html_e( 'Active', 'disciple_tools' ); ?></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value">7</div>
                        <div class="stat-label"><?php esc_html_e( 'Pending', 'disciple_tools' ); ?></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value">98%</div>
                        <div class="stat-label"><?php esc_html_e( 'Uptime', 'disciple_tools' ); ?></div>
                    </div>
                </div>

                <!-- Action Cards -->
                <div class="content-grid">
                    <div class="content-card">
                        <h3><?php esc_html_e( 'Quick Actions', 'disciple_tools' ); ?></h3>
                        <p><?php esc_html_e( 'Common administrative tasks for this section.', 'disciple_tools' ); ?></p>
                        <div class="action-buttons">
                            <button class="btn btn-primary"><?php esc_html_e( 'Primary Action', 'disciple_tools' ); ?></button>
                            <button class="btn btn-secondary"><?php esc_html_e( 'Secondary Action', 'disciple_tools' ); ?></button>
                        </div>
                    </div>

                    <div class="content-card">
                        <h3><?php esc_html_e( 'Recent Activity', 'disciple_tools' ); ?></h3>
                        <div class="activity-list">
                            <div class="activity-item">
                                <div class="activity-icon">✓</div>
                                <div class="activity-content">
                                    <div class="activity-title"><?php esc_html_e( 'Configuration updated', 'disciple_tools' ); ?></div>
                                    <div class="activity-time"><?php esc_html_e( '2 hours ago', 'disciple_tools' ); ?></div>
                                </div>
                            </div>
                            <div class="activity-item">
                                <div class="activity-icon">⚠</div>
                                <div class="activity-content">
                                    <div class="activity-title"><?php esc_html_e( 'System warning logged', 'disciple_tools' ); ?></div>
                                    <div class="activity-time"><?php esc_html_e( '5 hours ago', 'disciple_tools' ); ?></div>
                                </div>
                            </div>
                            <div class="activity-item">
                                <div class="activity-icon">✓</div>
                                <div class="activity-content">
                                    <div class="activity-title"><?php esc_html_e( 'Backup completed', 'disciple_tools' ); ?></div>
                                    <div class="activity-time"><?php esc_html_e( '1 day ago', 'disciple_tools' ); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Data Table -->
                <div class="content-card">
                    <div class="card-header">
                        <h3><?php esc_html_e( 'Data Overview', 'disciple_tools' ); ?></h3>
                        <button class="btn btn-outline"><?php esc_html_e( 'Export', 'disciple_tools' ); ?></button>
                    </div>
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th><?php esc_html_e( 'Name', 'disciple_tools' ); ?></th>
                                    <th><?php esc_html_e( 'Status', 'disciple_tools' ); ?></th>
                                    <th><?php esc_html_e( 'Last Modified', 'disciple_tools' ); ?></th>
                                    <th><?php esc_html_e( 'Actions', 'disciple_tools' ); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Sample Item 1</td>
                                    <td><span class="status-badge status-active"><?php esc_html_e( 'Active', 'disciple_tools' ); ?></span></td>
                                    <td>2025-01-06</td>
                                    <td>
                                        <button class="btn btn-sm btn-outline"><?php esc_html_e( 'Edit', 'disciple_tools' ); ?></button>
                                        <button class="btn btn-sm btn-danger"><?php esc_html_e( 'Delete', 'disciple_tools' ); ?></button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Sample Item 2</td>
                                    <td><span class="status-badge status-inactive"><?php esc_html_e( 'Inactive', 'disciple_tools' ); ?></span></td>
                                    <td>2025-01-05</td>
                                    <td>
                                        <button class="btn btn-sm btn-outline"><?php esc_html_e( 'Edit', 'disciple_tools' ); ?></button>
                                        <button class="btn btn-sm btn-danger"><?php esc_html_e( 'Delete', 'disciple_tools' ); ?></button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Sample Item 3</td>
                                    <td><span class="status-badge status-pending"><?php esc_html_e( 'Pending', 'disciple_tools' ); ?></span></td>
                                    <td>2025-01-04</td>
                                    <td>
                                        <button class="btn btn-sm btn-outline"><?php esc_html_e( 'Edit', 'disciple_tools' ); ?></button>
                                        <button class="btn btn-sm btn-danger"><?php esc_html_e( 'Delete', 'disciple_tools' ); ?></button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </main>
    </div>
</div>

<script>
// Admin functionality
document.addEventListener('DOMContentLoaded', function() {
    // Navigation toggle functionality
    const navToggles = document.querySelectorAll('.nav-toggle');
    navToggles.forEach(toggle => {
        toggle.addEventListener('click', function() {
            const section = this.closest('.nav-section');
            const submenu = section.querySelector('.nav-submenu');
            const isExpanded = this.getAttribute('aria-expanded') === 'true';
            
            this.setAttribute('aria-expanded', !isExpanded);
            submenu.classList.toggle('expanded');
        });
    });

    // Mobile menu functionality
    const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
    const sidebar = document.querySelector('.dt-admin-sidebar');
    const overlay = document.getElementById('mobile-overlay');
    const menuIcon = mobileMenuToggle.querySelector('.menu-icon');
    const closeIcon = mobileMenuToggle.querySelector('.close-icon');

    function toggleMobileMenu() {
        const isOpen = sidebar.classList.contains('open');
        
        if (isOpen) {
            // Close menu
            sidebar.classList.remove('open');
            overlay.classList.remove('active');
            menuIcon.style.display = 'block';
            closeIcon.style.display = 'none';
            document.body.style.overflow = '';
        } else {
            // Open menu
            sidebar.classList.add('open');
            overlay.classList.add('active');
            menuIcon.style.display = 'none';
            closeIcon.style.display = 'block';
            document.body.style.overflow = 'hidden';
        }
    }

    mobileMenuToggle.addEventListener('click', toggleMobileMenu);
    overlay.addEventListener('click', toggleMobileMenu);

    // Close mobile menu when clicking sidebar links
    const sidebarLinks = sidebar.querySelectorAll('a');
    sidebarLinks.forEach(link => {
        link.addEventListener('click', () => {
            if (window.innerWidth <= 768) {
                toggleMobileMenu();
            }
        });
    });

    // Simulate loading
    setTimeout(function() {
        document.getElementById('loading-state').style.display = 'none';
        document.getElementById('main-content').style.display = 'block';
        
        // Update page description based on current section
        const urlPath = window.location.pathname;
        const pathParts = urlPath.split('/');
        const section = pathParts[2] || 'mapping';
        const subsection = pathParts[3] || 'overview';
        
        const descriptions = {
            'mapping': {
                'overview': 'Dashboard view of mapping statistics and system status.',
                'location-grid': 'Manage the location grid database and hierarchy.',
                'geocoding': 'Configure geocoding services and settings.',
                'layers': 'Configure map layers, styles, and data sources.'
            },
            'settings': {
                'general': 'General site-wide configuration options.',
                'custom-fields': 'Manage custom fields for post types.',
                'custom-lists': 'Configure custom dropdown lists and options.',
                'roles': 'Manage user roles and permissions.',
                'security': 'Security settings and configurations.'
            },
            'plugins': {
                'installed': 'View and manage installed plugins.',
                'available': 'Browse available extensions and add-ons.',
                'settings': 'Configure plugin-specific settings.'
            },
            'tools': {
                'data': 'Import, export, and manage data.',
                'logs': 'View system logs and error reports.',
                'jobs': 'Monitor background jobs and processes.',
                'database': 'Database maintenance and utilities.'
            }
        };
        
        const description = descriptions[section] && descriptions[section][subsection] 
            ? descriptions[section][subsection] 
            : 'Admin section for managing system configuration.';
            
        document.getElementById('page-description').textContent = description;
    }, 1000);
});
</script>

<?php get_footer(); ?> 