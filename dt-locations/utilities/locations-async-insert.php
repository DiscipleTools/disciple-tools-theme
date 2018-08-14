<?php
/**
 * Disciple Tools Async Import Location
 * This async file must be loaded from the functions.php file, or else weird things happen. :)
 */


/**
 * Class Disciple_Tools_Insert_Location
 */
class Disciple_Tools_Async_Insert_Location extends Disciple_Tools_Async_Task
{
    protected $action = 'insert_location';

    /**
     * Prepare data for the asynchronous request
     *
     * @throws Exception If for any reason the request should not happen.
     *
     * @param array $data An array of data sent to the hook
     *
     * @return array
     */
    protected function prepare_data( $data ) {
        return $data;
    }

    /**
     * Insert Locations
     */
    public function insert_location() {
        /**
         * Nonce validation is done through a custom nonce process inside Disciple_Tools_Async_Task
         * to allow for asynchronous processing. This is a valid nonce but is not recognized by the WP standards checker.
         *
         */
        // WordPress.CSRF.NonceVerification.NoNonceVerification
        // @phpcs:ignore
        if ( isset( $_POST['action'] ) && sanitize_key( wp_unslash( $_POST['action'] ) ) == 'dt_async_insert_location' && isset( $_POST['_nonce'] ) && $this->verify_async_nonce( sanitize_key( wp_unslash( $_POST['_nonce'] ) ) ) ) {

            // WordPress.CSRF.NonceVerification.NoNonceVerification
            // @phpcs:ignore
            $mapped_from_form = array_map( 'sanitize_text_field', wp_unslash( $_POST[0] ) );

            // prepare standard fields
            $args['post_title'] = wp_strip_all_tags( $mapped_from_form['post_title'] );
            $args['post_content'] = '';
            $args['post_type'] = $mapped_from_form['post_type'];
            $args['post_status'] = 'publish';

            if ( isset( $mapped_from_form['post_excerpt'] ) ) {
                $args['post_excerpt'] = $mapped_from_form['post_excerpt'];
            }

            if ( isset( $mapped_from_form['post_slug'] ) ) {
                $args['post_name'] = $mapped_from_form['post_slug'];
            }

            if ( isset( $mapped_from_form['post_date'] ) ) {
                $timestamp = strtotime( $mapped_from_form['post_date'] );
                if ( $timestamp !== false ) {
                    $args['post_date'] = date( 'Y-m-d H:i:s', $timestamp );
                }
            }

            switch ( $mapped_from_form['post_type'] ) {
                case 'locations':

                    // lookup post parent id
                    if ( isset( $mapped_from_form['post_parent'] ) ) {
                        $parent_id = get_page_by_title( $mapped_from_form['post_parent'], OBJECT, 'locations' );
                        if ( ! is_null( $parent_id ) ) {
                            $args['post_parent'] = $parent_id->ID;
                        }
                    }

                    // geocode address
                    if ( isset( $mapped_from_form['address'] ) ) {

                        if ( isset( $mapped_from_form['country'] ) ) {
                            $mapped_from_form['address'] .= ', ' . $mapped_from_form['country'];
                        }

                        $results = Disciple_Tools_Google_Geocode_API::query_google_api( $mapped_from_form['address'], 'all_points' );
                        if ( $results && isset( $results['lat'] ) ) {
                            $args['meta_input'] = [
                            'lat' => $results['lat'],
                            'lng' => $results['lng'],
                            'northeast_lat' => $results['northeast_lat'],
                            'northeast_lng' => $results['northeast_lng'],
                            'southwest_lat' => $results['southwest_lat'],
                            'southwest_lng' => $results['southwest_lng'],
                            'location_address' => $results['formatted_address'],
                            'location' => $results,
                            ];
                        }
                    }

                    if ( isset( $mapped_from_form['reference_id'] ) ) {
                        $args['meta_input']['reference_id'] = $mapped_from_form['reference_id'];
                    }

                    break;
                default:
                    break;
            }

            $id = wp_insert_post( $args ); // wp insert statement

            // Track the number of posts inserted
            if ( $id ) {
                $imported = get_transient( 'dt_import_finished_count' );
                ( $imported ) ? $imported++ : $imported = 1;
                set_transient( 'dt_import_finished_count', $imported, 1 * HOUR_IN_SECONDS );
            } else {
                $errors = get_transient( 'dt_import_finished_with_errors' );
                $errors[] = $args['post_title'];
                set_transient( 'dt_import_finished_with_errors', $errors, 1 * HOUR_IN_SECONDS );
            }
        }
    }

    /**
     * Run the async task action
     * Used when loading long running process with add_action
     * Not used when directly using launch().
     */
    protected function run_action() {

    }
}

/**
 * This hook function listens for the prepared async process on every page load.
 */
function dt_load_async_insert_location() {
    if ( isset( $_POST['_wp_nonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['_wp_nonce'] ) ) ) && isset( $_POST['action'] ) && sanitize_key( wp_unslash( $_POST['action'] ) ) == 'dt_async_insert_location' ) {
        try {
            $insert_location = new Disciple_Tools_Async_Insert_Location();
            $insert_location->insert_location();
        } catch ( Exception $e ) {
            dt_write_log( __METHOD__ . ': Failed to update locations' );
        }
    }
}
add_action( 'init', 'dt_load_async_insert_location' );
