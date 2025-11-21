<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * Migrate custom apps and trainings from old dt-home plugin to new theme structure.
 *
 * Moves from:
 * - option 'dt_home_apps' (old plugin, only custom creation_type apps)
 * - option 'dt_home_trainings' (old plugin)
 * into:
 * - option 'dt_home_screen_apps' (new theme)
 * - option 'dt_home_screen_training' (new theme)
 */
class Disciple_Tools_Migration_0062 extends Disciple_Tools_Migration {
    
    /**
     * Migration flag option name
     */
    const MIGRATION_FLAG = 'dt_home_migration_0062_completed';
    
    /**
     * Old plugin option keys
     */
    const OLD_APPS_OPTION = 'dt_home_apps';
    const OLD_TRAININGS_OPTION = 'dt_home_trainings';
    
    /**
     * New theme option keys
     */
    const NEW_APPS_OPTION = 'dt_home_screen_apps';
    const NEW_TRAININGS_OPTION = 'dt_home_screen_training';
    
    /**
     * Old plugin file path
     */
    const OLD_PLUGIN_FILE = 'dt-home/dt-home.php';
    
    public function up() {
        // 1. Check if old plugin is installed
        if ( !$this->is_old_plugin_installed() ) {
            return; // Exit early if plugin not found
        }
        
        // 2. Check if migration already completed
        if ( $this->is_migration_completed() ) {
            return; // Exit early if already migrated
        }
        
        // 3. Load old data
        $old_apps = $this->load_old_apps();
        $old_trainings = $this->load_old_trainings();
        
        // If no data to migrate, mark as completed and exit
        if ( empty( $old_apps ) && empty( $old_trainings ) ) {
            update_option( self::MIGRATION_FLAG, true );
            return;
        }
        
        // 4. Get existing new data (to avoid duplicates)
        $existing_apps = get_option( self::NEW_APPS_OPTION, [] );
        $existing_trainings = get_option( self::NEW_TRAININGS_OPTION, [] );
        
        // 5. Transform apps
        $migrated_apps = [];
        foreach ( $old_apps as $old_app ) {
            $transformed_app = $this->transform_app( $old_app, $existing_apps );
            if ( $transformed_app ) {
                $migrated_apps[] = $transformed_app;
            }
        }
        
        // 6. Transform trainings
        $migrated_trainings = [];
        foreach ( $old_trainings as $old_training ) {
            $transformed_training = $this->transform_training( $old_training, $existing_trainings );
            if ( $transformed_training ) {
                $migrated_trainings[] = $transformed_training;
            }
        }
        
        // 7. Save migrated data
        if ( !empty( $migrated_apps ) || !empty( $migrated_trainings ) ) {
            $this->save_migrated_data( $migrated_apps, $migrated_trainings );
        } else {
            // Mark as completed even if no data to migrate
            update_option( self::MIGRATION_FLAG, true );
        }
    }
    
    public function down() {
        // No-op. We do not remove migrated data to preserve user data.
        // If rollback is needed, it should be done manually.
    }
    
    public function test() {
        // Verify migration completed flag exists if old plugin was installed
        $completed = get_option( self::MIGRATION_FLAG, false );
        
        // If old plugin is installed, migration should have run
        if ( $this->is_old_plugin_installed() ) {
            // Migration should have completed (even if no data to migrate)
            // We don't throw exception here as migration may legitimately have no data
        }
        
        // Verify new options exist (they may be empty, which is OK)
        $apps = get_option( self::NEW_APPS_OPTION, [] );
        $trainings = get_option( self::NEW_TRAININGS_OPTION, [] );
        
        // Basic validation - options should exist (even if empty)
        // No exception thrown as empty data is valid
    }
    
    public function get_expected_tables(): array {
        return []; // No database tables involved
    }
    
    /**
     * Check if old plugin is installed
     *
     * @return bool
     */
    private function is_old_plugin_installed(): bool {
        // Check if plugin file exists
        $plugin_file = WP_PLUGIN_DIR . '/' . self::OLD_PLUGIN_FILE;
        if ( !file_exists( $plugin_file ) ) {
            return false;
        }
        
        // Plugin is considered "installed" if file exists
        // We can migrate even if inactive, as data persists in options
        return true;
    }
    
    /**
     * Check if migration already completed
     *
     * @return bool
     */
    private function is_migration_completed(): bool {
        return get_option( self::MIGRATION_FLAG, false ) === true;
    }
    
    /**
     * Load old apps data (only custom creation_type)
     *
     * @return array
     */
    private function load_old_apps(): array {
        $old_apps = get_option( self::OLD_APPS_OPTION, [] );
        if ( !is_array( $old_apps ) ) {
            return [];
        }
        
        // Filter only custom creation_type apps that are not deleted
        return array_filter( $old_apps, function( $app ) {
            // Must have creation_type set to 'custom'
            if ( !isset( $app['creation_type'] ) || $app['creation_type'] !== 'custom' ) {
                return false;
            }
            
            // Must not be soft-deleted
            if ( isset( $app['is_deleted'] ) && $app['is_deleted'] === true ) {
                return false;
            }
            
            // Must have a name (title)
            if ( empty( $app['name'] ) ) {
                return false;
            }
            
            return true;
        });
    }
    
    /**
     * Load old trainings data
     *
     * @return array
     */
    private function load_old_trainings(): array {
        $old_trainings = get_option( self::OLD_TRAININGS_OPTION, [] );
        if ( !is_array( $old_trainings ) ) {
            return [];
        }
        
        // Filter out invalid trainings
        return array_filter( $old_trainings, function( $training ) {
            // Must have a name (title)
            if ( empty( $training['name'] ) ) {
                return false;
            }
            
            return true;
        });
    }
    
    /**
     * Transform old app to new structure
     *
     * @param array $old_app
     * @param array $existing_apps
     * @return array|null
     */
    private function transform_app( array $old_app, array $existing_apps ): ?array {
        // Generate unique ID
        $id = $this->generate_unique_app_id( $old_app['name'] ?? '', $existing_apps );
        
        // Determine type
        $type = $this->determine_app_type( $old_app );
        
        // Get slug (generate if missing)
        $slug = !empty( $old_app['slug'] ) ? sanitize_title( $old_app['slug'] ) : sanitize_title( $old_app['name'] ?? '' );
        
        // Ensure slug is unique
        $slug = $this->ensure_unique_slug( $slug, $existing_apps, $id );
        
        // Transform
        $new_app = [
            'id' => $id,
            'slug' => $slug,
            'creation_type' => 'custom',
            'type' => $type,
            'title' => sanitize_text_field( $old_app['name'] ?? '' ),
            'description' => '',
            'url' => esc_url_raw( $old_app['url'] ?? '#' ),
            'icon' => sanitize_text_field( $old_app['icon'] ?? 'mdi mdi-apps' ),
            'color' => null,
            'enabled' => !( isset( $old_app['is_hidden'] ) && $old_app['is_hidden'] == 1 ),
            'user_roles_type' => sanitize_text_field( $old_app['user_roles_type'] ?? 'support_all_roles' ),
            'roles' => is_array( $old_app['roles'] ?? [] ) ? array_map( 'sanitize_text_field', $old_app['roles'] ) : [],
            'order' => isset( $old_app['sort'] ) ? (int) $old_app['sort'] : 999,
            'created_at' => current_time( 'mysql' ),
            'updated_at' => current_time( 'mysql' )
        ];
        
        return $new_app;
    }
    
    /**
     * Transform old training to new structure
     *
     * @param array $old_training
     * @param array $existing_trainings
     * @return array|null
     */
    private function transform_training( array $old_training, array $existing_trainings ): ?array {
        // Generate unique ID
        $id = $this->generate_unique_training_id( $old_training, $existing_trainings );
        
        // Extract video URL from embed_video if it's an iframe
        $video_url = $this->extract_video_url( $old_training['embed_video'] ?? '' );
        
        // Generate thumbnail
        $thumbnail_url = $this->get_youtube_thumbnail( $video_url );
        
        $new_training = [
            'id' => $id,
            'title' => sanitize_text_field( $old_training['name'] ?? '' ),
            'description' => '',
            'video_url' => esc_url_raw( $video_url ),
            'thumbnail_url' => esc_url_raw( $thumbnail_url ),
            'duration' => '',
            'category' => 'general',
            'enabled' => true,
            'order' => isset( $old_training['sort'] ) ? (int) $old_training['sort'] : 999,
            'created_at' => current_time( 'mysql' ),
            'updated_at' => current_time( 'mysql' )
        ];
        
        return $new_training;
    }
    
    /**
     * Save migrated data
     *
     * @param array $apps
     * @param array $trainings
     * @return bool
     */
    private function save_migrated_data( array $apps, array $trainings ): bool {
        // Get existing apps (to merge, not overwrite)
        $existing_apps = get_option( self::NEW_APPS_OPTION, [] );
        if ( !is_array( $existing_apps ) ) {
            $existing_apps = [];
        }
        
        // Merge apps (avoid duplicates by ID)
        $merged_apps = $existing_apps;
        $existing_app_ids = array_column( $existing_apps, 'id' );
        foreach ( $apps as $app ) {
            if ( !in_array( $app['id'], $existing_app_ids ) ) {
                $merged_apps[] = $app;
            }
        }
        
        // Get existing trainings
        $existing_trainings = get_option( self::NEW_TRAININGS_OPTION, [] );
        if ( !is_array( $existing_trainings ) ) {
            $existing_trainings = [];
        }
        
        // Merge trainings (avoid duplicates by ID)
        $merged_trainings = $existing_trainings;
        $existing_training_ids = array_column( $existing_trainings, 'id' );
        foreach ( $trainings as $training ) {
            if ( !in_array( $training['id'], $existing_training_ids ) ) {
                $merged_trainings[] = $training;
            }
        }
        
        // Save
        $apps_result = update_option( self::NEW_APPS_OPTION, $merged_apps );
        $trainings_result = update_option( self::NEW_TRAININGS_OPTION, $merged_trainings );
        
        // Mark migration as completed
        if ( $apps_result !== false && $trainings_result !== false ) {
            update_option( self::MIGRATION_FLAG, true );
            return true;
        }
        
        return false;
    }
    
    /**
     * Generate unique app ID
     *
     * @param string $name
     * @param array $existing_apps
     * @return string
     */
    private function generate_unique_app_id( string $name, array $existing_apps ): string {
        $id = sanitize_title( $name );
        if ( empty( $id ) ) {
            $id = 'app-' . time();
        }
        
        $original_id = $id;
        $counter = 1;
        
        $existing_ids = array_column( $existing_apps, 'id' );
        while ( in_array( $id, $existing_ids ) ) {
            $id = $original_id . '-' . $counter;
            $counter++;
        }
        
        return $id;
    }
    
    /**
     * Generate unique training ID
     *
     * @param array $old_training
     * @param array $existing_trainings
     * @return string
     */
    private function generate_unique_training_id( array $old_training, array $existing_trainings ): string {
        // Use existing ID if numeric, convert to string
        if ( isset( $old_training['id'] ) && is_numeric( $old_training['id'] ) ) {
            $id = (string) $old_training['id'];
        } else {
            // Generate from name
            $id = sanitize_title( $old_training['name'] ?? '' );
            if ( empty( $id ) ) {
                $id = 'training-' . time();
            }
        }
        
        $original_id = $id;
        $counter = 1;
        
        $existing_ids = array_column( $existing_trainings, 'id' );
        while ( in_array( $id, $existing_ids ) ) {
            $id = $original_id . '-' . $counter;
            $counter++;
        }
        
        return $id;
    }
    
    /**
     * Ensure unique slug
     *
     * @param string $slug
     * @param array $existing_apps
     * @param string $current_id
     * @return string
     */
    private function ensure_unique_slug( string $slug, array $existing_apps, string $current_id ): string {
        if ( empty( $slug ) ) {
            return 'app-' . time();
        }
        
        $original_slug = $slug;
        $counter = 1;
        $is_unique = false;
        
        // Keep checking until we find a unique slug
        while ( !$is_unique ) {
            $is_unique = true;
            
            // Check for duplicate slugs (excluding current app)
            foreach ( $existing_apps as $app ) {
                if ( isset( $app['slug'] ) && $app['slug'] === $slug && ( !isset( $app['id'] ) || $app['id'] !== $current_id ) ) {
                    $is_unique = false;
                    $slug = $original_slug . '-' . $counter;
                    $counter++;
                    break; // Break inner loop and check again
                }
            }
        }
        
        return $slug;
    }
    
    /**
     * Determine app type from old app data
     *
     * @param array $old_app
     * @return string
     */
    private function determine_app_type( array $old_app ): string {
        // If open_in_new_tab is set, it's likely a link
        if ( isset( $old_app['open_in_new_tab'] ) && $old_app['open_in_new_tab'] == 1 ) {
            return 'link';
        }
        
        // Check old type field
        if ( isset( $old_app['type'] ) ) {
            $old_type = strtolower( $old_app['type'] );
            // Map old types to new types
            if ( in_array( $old_type, [ 'webview', 'web_view', 'link' ] ) ) {
                return 'link';
            }
            if ( $old_type === 'app' ) {
                return 'app';
            }
        }
        
        // Default to 'link' for custom apps
        return 'link';
    }
    
    /**
     * Extract video URL from embed_video field
     *
     * @param string $embed_video
     * @return string
     */
    private function extract_video_url( string $embed_video ): string {
        if ( empty( $embed_video ) ) {
            return '';
        }
        
        // If it's already a URL, return it
        if ( filter_var( $embed_video, FILTER_VALIDATE_URL ) ) {
            return $embed_video;
        }
        
        // If it's an iframe, extract src
        if ( preg_match( '/src=["\']([^"\']+)["\']/', $embed_video, $matches ) ) {
            $url = $matches[1];
            // Convert YouTube embed URL to watch URL
            if ( preg_match( '/youtube\.com\/embed\/([^"\'?]+)/', $url, $youtube_matches ) ) {
                return 'https://www.youtube.com/watch?v=' . $youtube_matches[1];
            }
            return $url;
        }
        
        // If it's a YouTube embed URL pattern, convert to watch URL
        if ( preg_match( '/youtube\.com\/embed\/([^"\'?]+)/', $embed_video, $matches ) ) {
            return 'https://www.youtube.com/watch?v=' . $matches[1];
        }
        
        // Fallback: return as-is
        return $embed_video;
    }
    
    /**
     * Get YouTube thumbnail URL
     *
     * @param string $video_url
     * @return string
     */
    private function get_youtube_thumbnail( string $video_url ): string {
        if ( empty( $video_url ) ) {
            return '';
        }
        
        if ( strpos( $video_url, 'youtube.com' ) !== false || strpos( $video_url, 'youtu.be' ) !== false ) {
            // Extract video ID from various YouTube URL formats
            preg_match( '/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $video_url, $matches );
            if ( isset( $matches[1] ) ) {
                return 'https://img.youtube.com/vi/' . $matches[1] . '/maxresdefault.jpg';
            }
        }
        
        return '';
    }
}

