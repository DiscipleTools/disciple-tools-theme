<?php
/**
 * Home Screen Training Videos Management
 *
 * Handles CRUD operations for training videos in the Home Screen.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class DT_Home_Training
 *
 * Manages training videos for the Home Screen.
 */
class DT_Home_Training {

    private static $_instance = null;
    private $option_name = 'dt_home_screen_training';

    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct() {
        // Initialize with default videos if none exist
        $this->initialize_default_videos();

        // Register filter hook for external code
        add_filter( 'dt_home_screen_training_videos', [ $this, 'filter_home_screen_training_videos' ] );
    }

    /**
     * Initialize default training videos if none exist
     */
    private function initialize_default_videos() {
        $videos = $this->get_all_videos();
        if ( empty( $videos ) ) {
            $default_videos = [];

            update_option( $this->option_name, $default_videos );
        }
    }

    /**
     * Get all training videos
     */
    public function get_all_videos() {
        $videos = get_option( $this->option_name, [] );
        return is_array( $videos ) ? $videos : [];
    }

    /**
     * Get enabled videos only
     */
    public function get_enabled_videos() {
        $videos = $this->get_all_videos();
        return array_filter( $videos, function( $video ) {
            return isset( $video['enabled'] ) && $video['enabled'] === true;
        });
    }

    /**
     * Get videos by category
     */
    public function get_videos_by_category( $category ) {
        $videos = $this->get_enabled_videos();
        return array_filter( $videos, function( $video ) use ( $category ) {
            return isset( $video['category'] ) && $video['category'] === $category;
        });
    }

    /**
     * Get video by ID
     */
    public function get_video( $video_id ) {
        $videos = $this->get_all_videos();
        foreach ( $videos as $video ) {
            if ( isset( $video['id'] ) && $video['id'] === $video_id ) {
                return $video;
            }
        }
        return null;
    }

    /**
     * Create new training video
     */
    public function create_video( $video_data ) {
        // Validate required fields
        if ( empty( $video_data['title'] ) ) {
            return new WP_Error( 'missing_title', __( 'Video title is required.', 'disciple_tools' ) );
        }

        if ( empty( $video_data['video_url'] ) ) {
            return new WP_Error( 'missing_url', __( 'Video URL is required.', 'disciple_tools' ) );
        }

        // Generate unique ID
        $video_id = sanitize_title( $video_data['title'] );
        $original_id = $video_id;
        $counter = 1;

        // Ensure unique ID
        while ( $this->get_video( $video_id ) !== null ) {
            $video_id = $original_id . '-' . $counter;
            $counter++;
        }

        // Extract video ID from YouTube URL if applicable
        $video_url = $video_data['video_url'];
        $thumbnail_url = $this->get_youtube_thumbnail( $video_url );

        // Prepare video data
        $new_video = [
            'id' => $video_id,
            'title' => sanitize_text_field( $video_data['title'] ),
            'description' => sanitize_textarea_field( $video_data['description'] ?? '' ),
            'video_url' => esc_url_raw( $video_url ),
            'thumbnail_url' => esc_url_raw( $thumbnail_url ),
            'duration' => sanitize_text_field( $video_data['duration'] ?? '' ),
            'category' => sanitize_text_field( $video_data['category'] ?? 'general' ),
            'enabled' => isset( $video_data['enabled'] ) ? (bool) $video_data['enabled'] : true,
            'order' => $this->get_next_order(),
            'created_at' => current_time( 'mysql' ),
            'updated_at' => current_time( 'mysql' )
        ];

        // Add to videos array
        $videos = $this->get_all_videos();
        $videos[] = $new_video;

        // Save
        $result = update_option( $this->option_name, $videos );

        if ( $result ) {
            return $new_video;
        } else {
            return new WP_Error( 'save_failed', __( 'Failed to save video.', 'disciple_tools' ) );
        }
    }

    /**
     * Update existing video
     */
    public function update_video( $video_id, $video_data ) {
        $videos = $this->get_all_videos();
        $video_index = $this->get_video_index( $video_id );

        if ( $video_index === false ) {
            return new WP_Error( 'video_not_found', __( 'Video not found.', 'disciple_tools' ) );
        }

        // Update fields
        if ( isset( $video_data['title'] ) ) {
            $videos[$video_index]['title'] = sanitize_text_field( $video_data['title'] );
        }
        if ( isset( $video_data['description'] ) ) {
            $videos[$video_index]['description'] = sanitize_textarea_field( $video_data['description'] );
        }
        if ( isset( $video_data['video_url'] ) ) {
            $videos[$video_index]['video_url'] = esc_url_raw( $video_data['video_url'] );
            // Update thumbnail if URL changed
            $videos[$video_index]['thumbnail_url'] = $this->get_youtube_thumbnail( $video_data['video_url'] );
        }
        if ( isset( $video_data['duration'] ) ) {
            $videos[$video_index]['duration'] = sanitize_text_field( $video_data['duration'] );
        }
        if ( isset( $video_data['category'] ) ) {
            $videos[$video_index]['category'] = sanitize_text_field( $video_data['category'] );
        }
        if ( isset( $video_data['enabled'] ) ) {
            $videos[$video_index]['enabled'] = (bool) $video_data['enabled'];
        }
        if ( isset( $video_data['order'] ) ) {
            $videos[$video_index]['order'] = (int) $video_data['order'];
        }

        $videos[$video_index]['updated_at'] = current_time( 'mysql' );

        // Save
        $result = update_option( $this->option_name, $videos );

        if ( $result ) {
            return $videos[$video_index];
        } else {
            return new WP_Error( 'save_failed', __( 'Failed to update video.', 'disciple_tools' ) );
        }
    }

    /**
     * Delete video
     */
    public function delete_video( $video_id ) {
        $videos = $this->get_all_videos();
        $video_index = $this->get_video_index( $video_id );

        if ( $video_index === false ) {
            return new WP_Error( 'video_not_found', __( 'Video not found.', 'disciple_tools' ) );
        }

        // Remove video from array
        unset( $videos[$video_index] );
        $videos = array_values( $videos ); // Re-index array

        // Save
        $result = update_option( $this->option_name, $videos );

        if ( $result ) {
            return true;
        } else {
            return new WP_Error( 'save_failed', __( 'Failed to delete video.', 'disciple_tools' ) );
        }
    }

    /**
     * Reorder videos
     */
    public function reorder_videos( $ordered_ids ) {
        $videos = $this->get_all_videos();

        // Create a lookup array for existing data
        $videos_lookup = [];
        foreach ( $videos as $video ) {
            if ( isset( $video['id'] ) ) {
                $videos_lookup[$video['id']] = $video;
            }
        }

        // Reorder based on the provided IDs and update order values
        $reordered_videos = [];
        $processed_ids = [];

        foreach ( $ordered_ids as $index => $video_id ) {
            if ( isset( $videos_lookup[$video_id] ) ) {
                $video = $videos_lookup[$video_id];
                $video['order'] = $index + 1;
                $video['updated_at'] = current_time( 'mysql' );
                $reordered_videos[] = $video;
                $processed_ids[] = $video_id;
            }
        }

        // Add any missing items to the end to prevent data loss
        foreach ( $videos as $video ) {
            if ( isset( $video['id'] ) && !in_array( $video['id'], $processed_ids ) ) {
                $video['order'] = count( $reordered_videos ) + 1;
                $video['updated_at'] = current_time( 'mysql' );
                $reordered_videos[] = $video;
            }
        }

        // Save
        $result = update_option( $this->option_name, $reordered_videos );

        if ( $result ) {
            return true;
        } else {
            return new WP_Error( 'save_failed', __( 'Failed to reorder videos.', 'disciple_tools' ) );
        }
    }

    /**
     * Get video index in array
     */
    private function get_video_index( $video_id ) {
        $videos = $this->get_all_videos();
        foreach ( $videos as $index => $video ) {
            if ( isset( $video['id'] ) && $video['id'] === $video_id ) {
                return $index;
            }
        }
        return false;
    }

    /**
     * Get next order number
     */
    private function get_next_order() {
        $videos = $this->get_all_videos();
        $max_order = 0;
        foreach ( $videos as $video ) {
            if ( isset( $video['order'] ) && $video['order'] > $max_order ) {
                $max_order = $video['order'];
            }
        }
        return $max_order + 1;
    }

    /**
     * Get videos for frontend display
     */
    public function get_videos_for_frontend() {
        $videos = $this->get_enabled_videos();

        // Sort by order
        usort( $videos, function( $a, $b ) {
            return $a['order'] <=> $b['order'];
        });

        return $videos;
    }

    /**
     * Filter callback for dt_home_screen_training_videos
     *
     * Returns all training videos when external code calls apply_filters('dt_home_screen_training_videos', []).
     *
     * @param array $default_value Default value passed to apply_filters
     * @return array All training videos
     */
    public function filter_home_screen_training_videos( $default_value ) {
        return $this->get_videos_for_frontend();
    }

    /**
     * Get YouTube thumbnail URL
     */
    private function get_youtube_thumbnail( $video_url ) {
        if ( strpos( $video_url, 'youtube.com' ) !== false || strpos( $video_url, 'youtu.be' ) !== false ) {
            // Extract video ID from YouTube URL
            preg_match( '/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $video_url, $matches );
            if ( isset( $matches[1] ) ) {
                return 'https://img.youtube.com/vi/' . $matches[1] . '/maxresdefault.jpg';
            }
        }
        return '';
    }

    /**
     * Get video categories
     */
    public function get_categories() {
        $videos = $this->get_all_videos();
        $categories = [];

        foreach ( $videos as $video ) {
            if ( isset( $video['category'] ) && ! empty( $video['category'] ) ) {
                $categories[] = $video['category'];
            }
        }

        return array_unique( $categories );
    }

    /**
     * Validate video data
     */
    public function validate_video_data( $video_data ) {
        $errors = [];

        if ( empty( $video_data['title'] ) ) {
            $errors[] = __( 'Video title is required.', 'disciple_tools' );
        }

        if ( empty( $video_data['video_url'] ) ) {
            $errors[] = __( 'Video URL is required.', 'disciple_tools' );
        } elseif ( ! filter_var( $video_data['video_url'], FILTER_VALIDATE_URL ) ) {
            $errors[] = __( 'Video URL must be a valid URL.', 'disciple_tools' );
        }

        return $errors;
    }
}

DT_Home_Training::instance();
