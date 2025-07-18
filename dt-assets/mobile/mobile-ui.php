<?php
// Enqueue Foundation CSS (required for modals)
wp_enqueue_style( 'foundation-css', 'https://cdnjs.cloudflare.com/ajax/libs/foundicons/3.0.0/foundation-icons.css', [], '3.0.0' );

// Enqueue mobile UI CSS
wp_enqueue_style(
    'dt-mobile-ui',
    get_template_directory_uri() . '/dt-assets/mobile/mobile-ui.css',
    [],
    filemtime( get_template_directory() . '/dt-assets/mobile/mobile-ui.css' )
);

// Get current post type from URL
$post_type = dt_get_post_type();

// Check permissions
if ( !current_user_can( 'access_' . $post_type ) ) {
    wp_safe_redirect( '/settings' );
    exit();
}

// Get post type settings
$post_settings = DT_Posts::get_post_settings( $post_type );
$post_type_label = $post_settings['label_plural'] ?? ucfirst( $post_type );

// Enqueue jQuery (WordPress core)
wp_enqueue_script('jquery');

// Enqueue Foundation JS (from CDN)
wp_enqueue_script(
    'foundation-js',
    'https://cdnjs.cloudflare.com/ajax/libs/foundation/6.7.5/js/foundation.min.js',
    ['jquery'],
    '6.7.5',
    true
);

// Enqueue shared-functions.js (for window.API and window.SHAREDFUNCTIONS)
wp_enqueue_script(
    'shared-functions',
    get_template_directory_uri() . '/dt-assets/js/shared-functions.js',
    ['jquery'],
    filemtime(get_template_directory() . '/dt-assets/js/shared-functions.js'),
    true
);

// Enqueue advanced-search.js (for modal logic)
wp_enqueue_script(
    'advanced-search',
    get_template_directory_uri() . '/dt-assets/js/advanced-search.js',
    ['jquery', 'shared-functions', 'foundation-js'],
    filemtime(get_template_directory() . '/dt-assets/js/advanced-search.js'),
    true
);

// Localize advanced-search.js
wp_localize_script(
    'advanced-search',
    'advanced_search_settings',
    [
        'template_dir_uri' => get_template_directory_uri(),
        'fetch_more_text' => __('Fetch More', 'disciple_tools'),
    ]
);

// Get all available post types for the menu
$available_post_types = [];
$all_post_types = DT_Posts::get_post_types();
foreach ( $all_post_types as $pt ) {
    if ( current_user_can( 'access_' . $pt ) ) {
        $pt_settings = DT_Posts::get_post_settings( $pt );
        $available_post_types[$pt] = $pt_settings['label_plural'] ?? ucfirst( $pt );
    }
}

// Get post types user can create
$creatable_post_types = [];
foreach ( $all_post_types as $pt ) {
    if ( current_user_can( 'create_' . $pt ) ) {
        $pt_settings = DT_Posts::get_post_settings( $pt );
        $creatable_post_types[$pt] = $pt_settings['label_singular'] ?? ucfirst( $pt );
    }
}

// Fetch records
$records = DT_Posts::list_posts( $post_type, [
    'offset' => 0,
    'limit' => 50,
    'sort' => 'name'
] );

$has_records = !is_wp_error( $records ) && !empty( $records['posts'] );
$total_records = is_wp_error( $records ) ? 0 : ( $records['total'] ?? 0 );

wp_head();

// Ensure Foundation is initialized after all scripts are loaded
add_action('wp_footer', function() {
    echo "<script>jQuery(document).foundation();</script>";
}, 100);
?>
<div id="dt-mobile-ui-root">
    <header class="dt-mobile-header">
        <div class="dt-mobile-header-content">
            <!-- Logo/Title -->
            <span class="dt-mobile-logo">
                <img src="<?php echo get_template_directory_uri(); ?>/dt-assets/images/disciple-tools-logo-white.png" alt="Disciple Tools Logo" />
            </span>
            <!-- Navigation icons -->
            <nav class="dt-mobile-nav-icons">
                <button class="dt-mobile-hamburger-btn" id="dt-mobile-hamburger-btn" aria-label="Menu">
                    <span class="dt-mobile-hamburger-line"></span>
                    <span class="dt-mobile-hamburger-line"></span>
                    <span class="dt-mobile-hamburger-line"></span>
                </button>
            </nav>
        </div>
    </header>
    
    <!-- Mobile Menu Overlay -->
    <div class="dt-mobile-menu-overlay" id="dt-mobile-menu-overlay">
        <div class="dt-mobile-menu-backdrop" id="dt-mobile-menu-backdrop"></div>
        <div class="dt-mobile-menu-panel">
            <div class="dt-mobile-menu-header">
                <h2 class="dt-mobile-menu-title"><?php esc_html_e( 'Menu', 'disciple_tools' ); ?></h2>
                <button class="dt-mobile-menu-close" id="dt-mobile-menu-close" aria-label="Close menu">
                    <span class="dt-mobile-menu-close-icon">×</span>
                </button>
            </div>
            <nav class="dt-mobile-menu-nav">
                <ul class="dt-mobile-menu-list">
                    <?php foreach ( $available_post_types as $pt_key => $pt_label ): ?>
                        <li class="dt-mobile-menu-item">
                            <a href="<?php echo esc_url( home_url( '/' . $pt_key . '/?is_mobile=1' ) ); ?>" 
                               class="dt-mobile-menu-link <?php echo $pt_key === $post_type ? 'dt-mobile-menu-link-active' : ''; ?>">
                                <span class="dt-mobile-menu-link-text"><?php echo esc_html( $pt_label ); ?></span>
                                <?php if ( $pt_key === $post_type ): ?>
                                    <span class="dt-mobile-menu-link-indicator">●</span>
                                <?php endif; ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </nav>
        </div>
    </div>
    
    <main class="dt-mobile-main-content">
        <!-- Page Title -->
        <div class="dt-mobile-page-header">
            <h1 class="dt-mobile-page-title"><?php echo esc_html( $post_type_label ); ?></h1>
            <?php if ( $total_records > 0 ): ?>
                <span class="dt-mobile-record-count"><?php echo esc_html( sprintf( _n( '%d record', '%d records', $total_records, 'disciple_tools' ), $total_records ) ); ?></span>
            <?php endif; ?>
            <!-- Filters Accordion (now inside header) -->
            <section class="dt-mobile-filters-accordion" id="dt-mobile-filters-accordion">
                <button class="dt-mobile-filters-accordion-toggle" id="dt-mobile-filters-accordion-toggle" aria-expanded="false">
                    <span class="dt-mobile-filters-accordion-label"><i class="mdi mdi-filter-outline" style="font-size:20px;vertical-align:middle;margin-right:6px;"></i><?php esc_html_e('Filters', 'disciple_tools'); ?></span>
                    <span class="dt-mobile-filters-accordion-chevron"><i class="mdi mdi-chevron-down"></i></span>
                </button>
                <div class="dt-mobile-filters-accordion-content" id="dt-mobile-filters-accordion-content" style="display:none;">
                    <form id="dt-mobile-filters-form">
                        <?php
                        // Get filters for this post type (default tab only for now)
                        $filters = apply_filters( 'dt_user_list_filters', [ 'tabs' => [], 'filters' => [] ], $post_type );
                        $default_tab = null;
                        foreach ( $filters['tabs'] as $tab ) {
                            if ( in_array( strtolower( $tab['key'] ), [ 'default', 'all' ] ) ) {
                                $default_tab = $tab['key'];
                                break;
                            }
                        }
                        if ( $default_tab ) {
                            foreach ( $filters['filters'] as $filter ) {
                                if ( $filter['tab'] === $default_tab ) {
                                    $filter_id = esc_attr( $filter['ID'] );
                                    $filter_name = esc_html( $filter['name'] );
                                    $filter_count = isset($filter['count']) && $filter['count'] !== '' ? '('.intval($filter['count']).')' : '';
                                    echo "<label class='dt-mobile-filter-option'><input type='radio' name='dt-mobile-filter' value='{$filter_id}'> <span class='dt-mobile-filter-name'>{$filter_name}</span> <span class='dt-mobile-filter-count'>{$filter_count}</span></label>";
                                }
                            }
                        }
                        ?>
                    </form>
                    <button class="dt-mobile-create-custom-filter-btn" id="dt-mobile-create-custom-filter-btn" type="button">
                        <i class="mdi mdi-plus-circle-outline" style="font-size:18px;vertical-align:middle;margin-right:4px;"></i><?php esc_html_e('Create custom filter', 'disciple_tools'); ?>
                    </button>
                </div>
            </section>
        </div>

        <!-- Records List -->
        <div class="dt-mobile-records-list">
            <?php if ( is_wp_error( $records ) ): ?>
                <!-- Error State -->
                <div class="dt-mobile-error-state">
                    <div class="dt-mobile-error-icon">⚠️</div>
                    <p class="dt-mobile-error-message"><?php esc_html_e( 'Unable to load records', 'disciple_tools' ); ?></p>
                    <button class="dt-mobile-retry-button" onclick="location.reload()"><?php esc_html_e( 'Retry', 'disciple_tools' ); ?></button>
                </div>
            <?php elseif ( !$has_records ): ?>
                <!-- Empty State -->
                <div class="dt-mobile-empty-state">
                    <div class="dt-mobile-empty-icon">📋</div>
                    <p class="dt-mobile-empty-message"><?php echo esc_html( sprintf( __( 'No %s found', 'disciple_tools' ), strtolower( $post_type_label ) ) ); ?></p>
                </div>
            <?php else: ?>
                <!-- Records Cards -->
                <?php foreach ( $records['posts'] as $record ): ?>
                    <div class="dt-mobile-record-card" data-record-id="<?php echo esc_attr( $record['ID'] ); ?>">
                        <div class="dt-mobile-record-avatar">
                            <?php if ( !empty( $record['record_picture'] ) && !empty( $record['record_picture']['sizes']['thumbnail'] ) ): ?>
                                <img src="<?php echo esc_url( $record['record_picture']['sizes']['thumbnail'] ); ?>" alt="<?php echo esc_attr( $record['name'] ); ?>" />
                            <?php else: ?>
                                <div class="dt-mobile-record-avatar-placeholder">
                                    <?php echo esc_html( strtoupper( substr( $record['name'], 0, 1 ) ) ); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="dt-mobile-record-content">
                            <h3 class="dt-mobile-record-name">
                                <a href="<?php echo esc_url( $record['permalink'] ); ?>"><?php echo esc_html( $record['name'] ); ?></a>
                            </h3>
                            <?php if ( !empty( $record['overall_status'] ) ): ?>
                                <span class="dt-mobile-record-status"><?php echo esc_html( $record['overall_status']['label'] ?? $record['overall_status']['name'] ?? '' ); ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="dt-mobile-record-actions">
                            <button class="dt-mobile-record-action-btn" onclick="window.location.href='<?php echo esc_url( $record['permalink'] ); ?>'">
                                <span class="dt-mobile-action-icon">→</span>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>
    
    <footer class="dt-mobile-footer">
        <div class="dt-mobile-footer-content">
            <!-- Footer Actions -->
            <nav class="dt-mobile-footer-actions">
                <button class="dt-mobile-footer-action-btn advanced-search-nav-button" id="dt-mobile-search-btn" aria-label="Search">
                    <img class="dt-white-icon" title="Advanced Search" src="<?php echo get_template_directory_uri(); ?>/dt-assets/images/search.svg">
                </button>
                <button class="dt-mobile-footer-action-btn" id="dt-mobile-export-btn" aria-label="Export Menu">
                    <i class="mdi mdi-share-outline" style="font-size:28px;line-height:1;vertical-align:middle;color:#fff;"></i>
                </button>
                <button class="dt-mobile-footer-action-btn" id="dt-mobile-add-btn" aria-label="Add Record">
                    <i class="mdi mdi-plus-circle-outline" style="font-size:28px;line-height:1;vertical-align:middle;color:#fff;"></i>
                </button>
            </nav>
        </div>
    </footer>
    
    <!-- Add Record Menu Overlay -->
    <div class="dt-mobile-add-menu-overlay" id="dt-mobile-add-menu-overlay">
        <div class="dt-mobile-add-menu-backdrop" id="dt-mobile-add-menu-backdrop"></div>
        <div class="dt-mobile-add-menu-panel">
            <div class="dt-mobile-add-menu-header">
                <h2 class="dt-mobile-add-menu-title"><?php esc_html_e( 'Add Record', 'disciple_tools' ); ?></h2>
                <button class="dt-mobile-add-menu-close" id="dt-mobile-add-menu-close" aria-label="Close menu">
                    <span class="dt-mobile-add-menu-close-icon">×</span>
                </button>
            </div>
            <nav class="dt-mobile-add-menu-nav">
                <ul class="dt-mobile-add-menu-list">
                    <?php foreach ( $creatable_post_types as $pt_key => $pt_label ): ?>
                        <li class="dt-mobile-add-menu-item">
                            <a href="<?php echo esc_url( home_url( '/' . $pt_key . '/new/?is_mobile=1' ) ); ?>" 
                               class="dt-mobile-add-menu-link">
                                <span class="dt-mobile-add-menu-link-text"><?php echo esc_html( $pt_label ); ?></span>
                                <span class="dt-mobile-add-menu-link-arrow">→</span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </nav>
        </div>
    </div>

<!-- Export Menu Overlay -->
<div class="dt-mobile-export-menu-overlay" id="dt-mobile-export-menu-overlay">
    <div class="dt-mobile-export-menu-backdrop" id="dt-mobile-export-menu-backdrop"></div>
    <div class="dt-mobile-export-menu-panel">
        <div class="dt-mobile-export-menu-header">
            <h2 class="dt-mobile-export-menu-title"><?php esc_html_e( 'Export List', 'disciple_tools' ); ?></h2>
            <button class="dt-mobile-export-menu-close" id="dt-mobile-export-menu-close" aria-label="Close export menu">
                <span class="dt-mobile-export-menu-close-icon">×</span>
            </button>
        </div>
        <nav class="dt-mobile-export-menu-nav">
            <ul class="dt-mobile-export-menu-list">
                <li class="dt-mobile-export-menu-item">
                    <button class="dt-mobile-export-menu-link" id="dt-mobile-export-csv">
                        <span class="dt-mobile-export-menu-link-text"><?php esc_html_e( 'CSV List', 'disciple_tools' ); ?></span>
                        <span class="dt-mobile-export-menu-link-arrow">→</span>
                    </button>
                </li>
                <li class="dt-mobile-export-menu-item">
                    <button class="dt-mobile-export-menu-link" id="dt-mobile-export-bcc-email">
                        <span class="dt-mobile-export-menu-link-text"><?php esc_html_e( 'BCC Email List', 'disciple_tools' ); ?></span>
                        <span class="dt-mobile-export-menu-link-arrow">→</span>
                    </button>
                </li>
                <li class="dt-mobile-export-menu-item">
                    <button class="dt-mobile-export-menu-link" id="dt-mobile-export-phone">
                        <span class="dt-mobile-export-menu-link-text"><?php esc_html_e( 'Phone List', 'disciple_tools' ); ?></span>
                        <span class="dt-mobile-export-menu-link-arrow">→</span>
                    </button>
                </li>
                <li class="dt-mobile-export-menu-item">
                    <button class="dt-mobile-export-menu-link" id="dt-mobile-export-map">
                        <span class="dt-mobile-export-menu-link-text"><?php esc_html_e( 'Map List', 'disciple_tools' ); ?></span>
                        <span class="dt-mobile-export-menu-link-arrow">→</span>
                    </button>
                </li>
            </ul>
        </nav>
    </div>
</div>
</div>

<!-- Include Advanced Search Modal -->
<?php get_template_part( 'dt-assets/parts/modals/modal', 'advanced-search' ); ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Hamburger menu functionality
    const hamburgerBtn = document.getElementById('dt-mobile-hamburger-btn');
    const menuOverlay = document.getElementById('dt-mobile-menu-overlay');
    const menuBackdrop = document.getElementById('dt-mobile-menu-backdrop');
    const menuClose = document.getElementById('dt-mobile-menu-close');
    
    function openMenu() {
        menuOverlay.classList.add('dt-mobile-menu-open');
        document.body.style.overflow = 'hidden';
    }
    
    function closeMenu() {
        menuOverlay.classList.remove('dt-mobile-menu-open');
        document.body.style.overflow = '';
    }
    
    hamburgerBtn.addEventListener('click', openMenu);
    menuBackdrop.addEventListener('click', closeMenu);
    menuClose.addEventListener('click', closeMenu);
    
    // Add menu functionality
    const addBtn = document.getElementById('dt-mobile-add-btn');
    const addMenuOverlay = document.getElementById('dt-mobile-add-menu-overlay');
    const addMenuBackdrop = document.getElementById('dt-mobile-add-menu-backdrop');
    const addMenuClose = document.getElementById('dt-mobile-add-menu-close');
    
    function openAddMenu() {
        addMenuOverlay.classList.add('dt-mobile-add-menu-open');
        document.body.style.overflow = 'hidden';
    }
    
    function closeAddMenu() {
        addMenuOverlay.classList.remove('dt-mobile-add-menu-open');
        document.body.style.overflow = '';
    }
    
    addBtn.addEventListener('click', openAddMenu);
    addMenuBackdrop.addEventListener('click', closeAddMenu);
    addMenuClose.addEventListener('click', closeAddMenu);
    
    // Search functionality is now handled by advanced-search.js via the advanced-search-nav-button class
    
    // Close menus on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            if (menuOverlay.classList.contains('dt-mobile-menu-open')) {
                closeMenu();
            }
            if (addMenuOverlay.classList.contains('dt-mobile-add-menu-open')) {
                closeAddMenu();
            }
        }
    });

    // Export menu functionality
    const exportBtn = document.getElementById('dt-mobile-export-btn');
    const exportMenuOverlay = document.getElementById('dt-mobile-export-menu-overlay');
    const exportMenuBackdrop = document.getElementById('dt-mobile-export-menu-backdrop');
    const exportMenuClose = document.getElementById('dt-mobile-export-menu-close');

    function openExportMenu() {
        exportMenuOverlay.classList.add('dt-mobile-export-menu-open');
        document.body.style.overflow = 'hidden';
    }

    function closeExportMenu() {
        exportMenuOverlay.classList.remove('dt-mobile-export-menu-open');
        document.body.style.overflow = '';
    }

    exportBtn.addEventListener('click', openExportMenu);
    exportMenuBackdrop.addEventListener('click', closeExportMenu);
    exportMenuClose.addEventListener('click', closeExportMenu);

    // Export option click handlers (placeholders)
    document.getElementById('dt-mobile-export-csv').addEventListener('click', function() {
        alert('Export CSV List (to be implemented)');
        closeExportMenu();
    });
    document.getElementById('dt-mobile-export-bcc-email').addEventListener('click', function() {
        alert('Export BCC Email List (to be implemented)');
        closeExportMenu();
    });
    document.getElementById('dt-mobile-export-phone').addEventListener('click', function() {
        alert('Export Phone List (to be implemented)');
        closeExportMenu();
    });
    document.getElementById('dt-mobile-export-map').addEventListener('click', function() {
        alert('Export Map List (to be implemented)');
        closeExportMenu();
    });

    // Close export menu on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            if (exportMenuOverlay.classList.contains('dt-mobile-export-menu-open')) {
                closeExportMenu();
            }
        }
    });

    // Filters accordion functionality
    const filtersAccordionToggle = document.getElementById('dt-mobile-filters-accordion-toggle');
    const filtersAccordionContent = document.getElementById('dt-mobile-filters-accordion-content');
    const filtersAccordionChevron = filtersAccordionToggle.querySelector('.dt-mobile-filters-accordion-chevron i');
    filtersAccordionToggle.addEventListener('click', function() {
        const expanded = filtersAccordionToggle.getAttribute('aria-expanded') === 'true';
        if (expanded) {
            filtersAccordionContent.style.display = 'none';
            filtersAccordionToggle.setAttribute('aria-expanded', 'false');
            filtersAccordionChevron.classList.remove('mdi-chevron-up');
            filtersAccordionChevron.classList.add('mdi-chevron-down');
        } else {
            filtersAccordionContent.style.display = 'block';
            filtersAccordionToggle.setAttribute('aria-expanded', 'true');
            filtersAccordionChevron.classList.remove('mdi-chevron-down');
            filtersAccordionChevron.classList.add('mdi-chevron-up');
        }
    });
    // Placeholder for Create custom filter button
    document.getElementById('dt-mobile-create-custom-filter-btn').addEventListener('click', function() {
        alert('Create custom filter (to be implemented)');
    });
});
</script> 