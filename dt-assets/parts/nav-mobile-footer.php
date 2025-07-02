<?php
/**
 * Mobile Footer Toolbar
 * Fixed bottom toolbar with quick access to filters, exports, and create actions
 */

// Determine current post type
$url = dt_get_url_path();
$url_parts = explode( '/', trim( $url, '/' ) );
$current_post_type = $url_parts[0] ?? 'contacts';

// Debug: Always show footer for now, but get correct post type
$post_types = DT_Posts::get_post_types();

// If not a valid post type, default to contacts
if ( ! in_array( $current_post_type, $post_types ) ) {
    $current_post_type = 'contacts';
}

$post_settings = DT_Posts::get_post_settings( $current_post_type );

?>

<!-- MOBILE FOOTER TOOLBAR -->
<div class="dt-mobile-footer fixed bottom-0 left-0 w-full bg-blue-600 shadow-xl z-50 border-t-2 border-blue-700">
    <div class="flex items-center justify-between px-2 py-3">
        
        <!-- Left group: Main action buttons -->
        <div class="flex items-center justify-evenly flex-1 max-w-xs">
            <!-- Record Filters Button -->
            <button id="mobile-filters-btn" class="flex flex-col items-center px-3 py-2 text-white hover:bg-blue-700 rounded-lg transition-colors" type="button">
                <svg class="w-5 h-5 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.207A1 1 0 013 6.5V4z"></path>
                </svg>
                <span class="text-xs"><?php esc_html_e( 'Filters', 'disciple_tools' ); ?></span>
            </button>

            <!-- Split By Button -->
            <button id="mobile-split-by-btn" class="flex flex-col items-center px-3 py-2 text-white hover:bg-blue-700 rounded-lg transition-colors" type="button">
                <svg class="w-5 h-5 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 0a2 2 0 012-2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"></path>
                </svg>
                <span class="text-xs"><?php esc_html_e( 'Split By', 'disciple_tools' ); ?></span>
            </button>

            <!-- List Exports Button -->
            <button id="mobile-exports-btn" class="flex flex-col items-center px-3 py-2 text-white hover:bg-blue-700 rounded-lg transition-colors" type="button">
                <svg class="w-5 h-5 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <span class="text-xs"><?php esc_html_e( 'Exports', 'disciple_tools' ); ?></span>
            </button>
        </div>

        <!-- Right: Add New Button -->
        <div class="flex-shrink-0">
            <button id="mobile-add-new-footer-btn" class="flex flex-col items-center px-4 py-2 text-white bg-green-500 hover:bg-green-600 rounded-lg transition-colors" type="button">
                <svg class="w-5 h-5 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                <span class="text-xs"><?php esc_html_e( 'Add New', 'disciple_tools' ); ?></span>
            </button>
        </div>
    </div>
</div>

<!-- MOBILE FILTERS MODAL -->
<div id="mobile-filters-modal" class="fixed inset-0 bg-black bg-opacity-50 z-60 hidden">
    <div class="fixed bottom-0 left-0 w-full bg-white rounded-t-xl shadow-xl max-h-80 overflow-y-auto">
        <div class="flex items-center justify-between p-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">
                <?php echo esc_html( sprintf( _x( '%s Filters', 'Contacts Filters', 'disciple_tools' ), $post_settings['label_plural'] ) ); ?>
            </h3>
            <button id="close-filters-modal" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div id="mobile-filters-content" class="p-4">
            <!-- Filters content will be populated here -->
        </div>
    </div>
</div>

<!-- MOBILE SPLIT BY MODAL -->
<div id="mobile-split-by-modal" class="fixed inset-0 bg-black bg-opacity-50 z-60 hidden">
    <div class="fixed bottom-0 left-0 w-full bg-white rounded-t-xl shadow-xl max-h-80 overflow-y-auto">
        <div class="flex items-center justify-between p-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">
                <?php echo esc_html( _x( 'Split By', 'Split By', 'disciple_tools' ) ); ?>
            </h3>
            <button id="close-split-by-modal" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div id="mobile-split-by-content" class="p-4">
            <div class="mb-4">
                <label for="mobile-split-by-select" class="block text-sm font-medium text-gray-700 mb-2">
                    <?php esc_html_e( 'Select field to split by:', 'disciple_tools' ); ?>
                </label>
                <select id="mobile-split-by-select" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="" disabled selected><?php echo esc_html( _x( 'Select split by field', 'disciple_tools' ) ); ?></option>
                    <?php
                    $split_by_fields = [];
                    foreach ( DT_Posts::get_post_settings( $current_post_type )['fields'] ?? [] as $key => $field ){
                        if ( in_array( $field['type'], [ 'multi_select', 'key_select', 'tags', 'user_select', 'location', 'boolean', 'connection' ] ) ){
                            if ( !isset( $field['private'] ) || !$field['private'] ){
                                $split_by_fields[$key] = $field;
                            }
                        }
                    }
                    // Sort split by fields
                    uasort( $split_by_fields, function ( $a, $b ){
                        return ( $a['name'] < $b['name'] ) ? -1 : 1;
                    } );
                    // Display split by fields
                    foreach ( $split_by_fields as $split_by_field_key => $split_by_field ){
                        ?>
                        <option value="<?php echo esc_attr( $split_by_field_key ); ?>">
                            <?php echo esc_attr( sprintf( _x( '%1$s - (%2$s)', 'disciple_tools' ), $split_by_field['name'], $split_by_field_key ) ); ?>
                        </option>
                        <?php
                    }
                    ?>
                </select>
            </div>
            <button id="mobile-split-by-go" class="w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                <?php echo esc_html( _x( 'Go', 'disciple_tools' ) ); ?>
            </button>
            <div id="mobile-split-by-results" class="mt-4 hidden">
                <!-- Split by results will be populated here -->
            </div>
        </div>
    </div>
</div>

<!-- MOBILE EXPORTS MODAL -->
<div id="mobile-exports-modal" class="fixed inset-0 bg-black bg-opacity-50 z-60 hidden">
    <div class="fixed bottom-0 left-0 w-full bg-white rounded-t-xl shadow-xl max-h-80 overflow-y-auto">
        <div class="flex items-center justify-between p-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">
                <?php echo esc_html( _x( 'List Exports', 'List Exports', 'disciple_tools' ) ); ?>
            </h3>
            <button id="close-exports-modal" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div id="mobile-exports-content" class="p-4 space-y-3">
            <button id="mobile-export-csv" class="flex items-center w-full px-4 py-3 text-left bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors">
                <svg class="w-5 h-5 mr-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <div>
                    <div class="font-medium text-gray-900"><?php esc_html_e( 'CSV List', 'disciple_tools' ); ?></div>
                    <div class="text-sm text-gray-500"><?php esc_html_e( 'Export as spreadsheet', 'disciple_tools' ); ?></div>
                </div>
            </button>

            <?php if ( !empty( DT_Posts::get_field_settings_by_type( $current_post_type, 'communication_channel' ) ) ) : ?>
                <button id="mobile-export-email" class="flex items-center w-full px-4 py-3 text-left bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors">
                    <svg class="w-5 h-5 mr-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                    <div>
                        <div class="font-medium text-gray-900"><?php esc_html_e( 'BCC Email List', 'disciple_tools' ); ?></div>
                        <div class="text-sm text-gray-500"><?php esc_html_e( 'Email list for group messaging', 'disciple_tools' ); ?></div>
                    </div>
                </button>

                <button id="mobile-export-phone" class="flex items-center w-full px-4 py-3 text-left bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors">
                    <svg class="w-5 h-5 mr-3 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                    </svg>
                    <div>
                        <div class="font-medium text-gray-900"><?php esc_html_e( 'Phone List', 'disciple_tools' ); ?></div>
                        <div class="text-sm text-gray-500"><?php esc_html_e( 'Phone numbers for messaging apps', 'disciple_tools' ); ?></div>
                    </div>
                </button>
            <?php endif; ?>

            <?php if ( class_exists( 'DT_Mapbox_API' ) && DT_Mapbox_API::get_key() ) : ?>
                <button id="mobile-export-map" class="flex items-center w-full px-4 py-3 text-left bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors">
                    <svg class="w-5 h-5 mr-3 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    <div>
                        <div class="font-medium text-gray-900"><?php esc_html_e( 'Map List', 'disciple_tools' ); ?></div>
                        <div class="text-sm text-gray-500"><?php esc_html_e( 'Location map of records', 'disciple_tools' ); ?></div>
                    </div>
                </button>
            <?php endif; ?>

            <?php do_action( 'dt_mobile_footer_exports_menu_items', $current_post_type ); ?>
        </div>
    </div>
</div>

<!-- MOBILE ADD NEW MODAL -->
<div id="mobile-add-new-modal" class="fixed inset-0 bg-black bg-opacity-50 z-60 hidden" style="z-index: 9999 !important;">
    <div class="fixed bottom-0 left-0 w-full bg-white rounded-t-xl shadow-xl max-h-80 overflow-y-auto" style="z-index: 9999 !important;">
        <div class="flex items-center justify-between p-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">
                <?php esc_html_e( 'Create New Record', 'disciple_tools' ); ?>
            </h3>
            <button id="close-add-new-modal" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div id="mobile-add-new-content" class="p-4 space-y-3">
            <?php
            // Get current post type create URL
            $current_create_url = site_url( '/' . $current_post_type . '/new' );
            ?>
            <a href="<?php echo esc_url( $current_create_url ); ?>" class="flex items-center w-full px-4 py-3 text-left bg-green-50 hover:bg-green-100 rounded-lg transition-colors border border-green-200">
                <svg class="w-5 h-5 mr-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                <div>
                    <div class="font-medium text-gray-900">
                        <?php echo esc_html( sprintf( __( 'New %s', 'disciple_tools' ), $post_settings['label_singular'] ) ); ?>
                    </div>
                    <div class="text-sm text-gray-500">
                        <?php echo esc_html( sprintf( __( 'Create a new %s record', 'disciple_tools' ), strtolower( $post_settings['label_singular'] ) ) ); ?>
                    </div>
                </div>
            </a>

            <?php
            // Show other post types if user has access
            foreach ( $post_types as $post_type_key ) {
                if ( $post_type_key === $current_post_type ) continue;
                
                if ( current_user_can( 'create_' . $post_type_key ) ) {
                    $other_post_settings = DT_Posts::get_post_settings( $post_type_key );
                    $create_url = site_url( '/' . $post_type_key . '/new' );
                    ?>
                    <a href="<?php echo esc_url( $create_url ); ?>" class="flex items-center w-full px-4 py-3 text-left bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors">
                        <svg class="w-5 h-5 mr-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        <div>
                            <div class="font-medium text-gray-900">
                                <?php echo esc_html( sprintf( __( 'New %s', 'disciple_tools' ), $other_post_settings['label_singular'] ) ); ?>
                            </div>
                            <div class="text-sm text-gray-500">
                                <?php echo esc_html( sprintf( __( 'Create a new %s record', 'disciple_tools' ), strtolower( $other_post_settings['label_singular'] ) ) ); ?>
                            </div>
                        </div>
                    </a>
                    <?php
                }
            }
            ?>
        </div>
    </div>
</div> 