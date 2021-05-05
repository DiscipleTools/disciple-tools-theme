<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

/**
 * Class Disciple_Tools_Hook_Posts
 */
class Disciple_Tools_Hook_Posts extends Disciple_Tools_Hook_Base {

    public function __construct() {
        add_action( 'wp_insert_post', [ &$this, 'hooks_new_post' ], 10, 3 );
        add_action( 'delete_post', [ &$this, 'hooks_delete_post' ] );

        add_action( "added_post_meta", [ &$this, 'hooks_added_post_meta' ], 10, 4 );
        add_action( "updated_post_meta", [ &$this, 'hooks_updated_post_meta' ], 10, 4 );
        add_action( "delete_post_meta", [ &$this, 'post_meta_deleted' ], 10, 4 );

        add_action( 'p2p_created_connection', [ &$this, 'hooks_p2p_created' ], 10, 1 );
        add_action( 'p2p_delete_connections', [ &$this, 'hooks_p2p_deleted' ], 10, 1 );

        add_action( 'wp_error_added', [ &$this, 'hooks_error_post' ], 10, 4 );

        parent::__construct();
    }


    protected function _draft_or_post_title( $post = 0 ) {
        $title = get_the_title( $post );

        if ( empty( $title ) ) {
            $title = __( '(no title)', 'disciple_tools' );
        }

        return $title;
    }

    /**
     * @param $post_ID
     * @param $post
     * @param $update
     *
     * @see /wp-includes/post.php:3684
     *
     */
    public function hooks_new_post( $post_ID, $post, $update ) {
        if ( ! $update ) {
            $activity = [
                'action'         => 'created',
                'object_type'    => $post->post_type,
                'object_subtype' => '',
                'object_id'      => $post->ID,
                'object_name'    => $this->_draft_or_post_title( $post->ID ),
                'meta_id'        => '',
                'meta_key'       => '',
                'meta_value'     => '',
                'meta_parent'    => '',
                'object_note'    => '',
                'hist_time'      => time() - 1,
            ];
            if ( ! get_current_user_id() ) {
                $user = wp_get_current_user();
                if ( $user->display_name ) {
                    $activity['object_note'] = "Created with site link: " . $user->display_name;
                    if ( isset( $user->site_key ) ) {
                        $activity["user_caps"] = $user->site_key;
                    }
                }
            }

            dt_activity_insert( $activity );
        }
    }


    public function hooks_delete_post( $post_id ) {
        if ( wp_is_post_revision( $post_id ) ) {
            return;
        }

        $post = get_post( $post_id );

        if ( in_array( $post->post_status, [ 'auto-draft', 'inherit' ] ) ) {
            return;
        }

        // Skip for menu items.
        if ( 'nav_menu_item' === get_post_type( $post->ID ) ) {
            return;
        }

        dt_activity_insert(
            [
                'action'         => 'deleted',
                'object_type'    => $post->post_type,
                'object_subtype' => '',
                'object_id'      => $post->ID,
                'object_name'    => $this->_draft_or_post_title( $post->ID ),
                'meta_id'        => ' ',
                'meta_key'       => ' ',
                'meta_value'     => ' ',
                'meta_parent'    => ' ',
                'object_note'    => ' ',
            ]
        );
    }

    public function hooks_added_post_meta( $mid, $object_id, $meta_key, $meta_value ) {

        return $this->hooks_updated_post_meta( $mid, $object_id, $meta_key, $meta_value, true );

    }


    public function hooks_updated_post_meta( $meta_id, $object_id, $meta_key, $meta_value, $new = false, $deleted = false ) {
        global $wpdb;
        $parent_post = get_post( $object_id, ARRAY_A ); // get object info
        if ( empty( $parent_post ) ) {
            return;
        }

        $ignore_fields = [ '_edit_lock', '_edit_last', "last_modified", "follow", "unfollow" ];

        if ( in_array( $meta_key, $ignore_fields ) ) {
            return;
        }
        if ( $parent_post["post_status"] === "auto-draft" ) {
            return;
        }

        if ( 'nav_menu_item' == $parent_post['post_type'] || 'attachment' == $parent_post['post_type'] ) { // ignore nav items
            return;
        }


        // get the previous value
        $prev       = '';
        $prev_value = '';
        if ( ! $new ) {
            $prev = $wpdb->get_results( $wpdb->prepare(
                "SELECT
                    *
                FROM
                    `$wpdb->dt_activity_log`
                WHERE
                    `object_type` = %s
                    AND `object_id` = %s
                    AND `meta_id` = %s
                ORDER BY
                    `hist_time` DESC
                LIMIT
                    0,1;",
                $parent_post['post_type'],
                $object_id,
                $meta_id
            ) );

        }
        if ( ! empty( $prev ) ) {
            if ( is_array( $meta_value ) ) {
                $prev_value = maybe_unserialize( $prev[0]->meta_value );
            } else {
                $prev_value = $prev[0]->meta_value;
            }
        }
        $field_type  = "";
        $object_note = '';
        $fields      = DT_Posts::get_post_field_settings( $parent_post['post_type'] );

        //build message for verifying and invalidating contact information fields.
        if ( strpos( $meta_key, "_details" ) !== false && is_array( $meta_value ) ) {
            $original_key = str_replace( "_details", "", $meta_key );
            $original     = get_post_meta( $object_id, $original_key, true );
            $object_note  = $this->_key_name( $original_key, $fields ) . ' "' . $original . '" ';
            $field_type   = "details";
            foreach ( $meta_value as $k => $v ) {
                if ( is_array( $prev_value ) && isset( $prev_value[ $k ] ) && $prev_value[ $k ] == $v ) {
                    continue;
                }
                if ( $k === "verified" ) {
                    $object_note .= $v ? "verified" : "not verified";
                }
                if ( $k === "invalid" ) {
                    $object_note .= $v ? "invalidated" : "not invalidated";
                }
                $object_note .= ', ';
            }
            $object_note = chop( $object_note, ', ' );
        }

        if ( $meta_key == "title" ) {
            $object_note = "Name changed to: " . $meta_value;
        }
        if ( strpos( $meta_key, "assigned_to" ) !== false ) {
            $meta_array = explode( '-', $meta_value ); // Separate the type and id
            if ( isset( $meta_array[1] ) ) {
                $user        = get_user_by( "ID", $meta_array[1] );
                $object_note = "Assigned to: " . ( $user ? $user->display_name : "Nobody" );
            }
        }


        if ( ! empty( $fields ) && isset( $fields[ $meta_key ]["type"] ) ) {
            $field_type = $fields[ $meta_key ]["type"];
        }

        if ( ! empty( $fields ) && ! $object_note ) { // Build object note if contact, group, location, else ignore object note
            if ( $new ) {
                if ( $meta_key === 'location_grid_meta' ) {
                    $object_note = Disciple_Tools_Mapping_Queries::get_location_grid_meta_label( (int) $meta_value );
                } else {
                    $object_note = 'Added ' . $this->_key_name( $meta_key, $fields ) . ': ' . $this->_value_name( $meta_key, $meta_value, $fields );
                }
            } else if ( $deleted ) {
                if ( $meta_key === 'location_grid_meta' ) {
                    $object_note = $prev[0]->object_note ?? '';
                } else {
                    $object_note = $this->_key_name( $meta_key, $fields ) . ' "' . ( $this->_value_name( $meta_key, empty( $prev_value ) ? $meta_value : $prev_value, $fields ) ) . '" deleted ';
                }
            } else {
                $object_note = $this->_key_name( $meta_key, $fields ) . ' changed ' .
                               ( isset( $prev_value ) ? 'from "' . $this->_value_name( $meta_key, $prev_value, $fields ) . '"' : '' ) .
                               ' to "' . $this->_value_name( $meta_key, $meta_value, $fields ) . '"';

            }
        }

        if ( $deleted ) {
            $prev_value = empty( $prev_value ) ? ( is_array( $meta_value ) ? serialize( $meta_value ) : $meta_value ) : $prev_value;
            $meta_value = "value_deleted";
        }

        dt_activity_insert( // insert activity record
            [
                'action'         => 'field_update',
                'object_type'    => ( empty( $parent_post['post_type'] ) ) ? 'unknown' : $parent_post['post_type'],
                'object_subtype' => $meta_key,
                'object_id'      => $object_id,
                'object_name'    => ( empty( $parent_post['post_title'] ) ) ? 'unknown' : $parent_post['post_title'],
                'meta_id'        => $meta_id,
                'meta_key'       => $meta_key,
                'meta_value'     => ( is_array( $meta_value ) ? serialize( $meta_value ) : $meta_value ) ?? "",
                'meta_parent'    => ( empty( $parent_post['post_parent'] ) ) ? 'unknown' : $parent_post['post_parent'],
                'object_note'    => $object_note,
                'old_value'      => is_array( $prev_value ) ? serialize( $prev_value ) : $prev_value,
                'field_type'     => $field_type,
            ]
        );
    }

    /**
     * Extract the pretty key name, if available
     *
     * @param  $meta_key
     * @param  $fields
     *
     * @return mixed
     */
    protected function _key_name( $meta_key, $fields ) {
        if ( isset( $fields[ $meta_key ]['name'] ) ) { // test if field exists
            return $fields[ $meta_key ]['name'];
        } else {
            return $meta_key;
        }

    }

    /**
     * Extract the pretty value name, if available
     *
     * @param  $meta_key
     * @param  $meta_value
     * @param  $fields
     *
     * @return mixed
     */
    protected function _value_name( $meta_key, $meta_value, $fields ) {
        if ( is_array( $meta_value ) ) {
            return serialize( $meta_value );
        }

        if ( isset( $fields[ $meta_key ]['default'][ $meta_value ] ) ) { // test if value exists

            if ( ! is_array( $fields[ $meta_key ]['default'] ) ) { // test if array
                return $meta_value;
            } else {
                if ( is_array( $fields[ $meta_key ]['default'][ $meta_value ] ) ) {
                    return $fields[ $meta_key ]['default'][ $meta_value ]["label"] ?? "";
                } else {
                    return $fields[ $meta_key ]['default'][ $meta_value ];
                }
            }
        } else { // if field not set
            return $meta_value;
        }
    }

    public function hooks_p2p_created( $p2p_id, $action = 'connected to' ) { // I need to create two records. One for each end of the connection.
        // Get p2p record
        $p2p_record = p2p_get_connection( $p2p_id ); // returns object
        $p2p_from   = get_post( $p2p_record->p2p_from, ARRAY_A );
        $p2p_to     = get_post( $p2p_record->p2p_to, ARRAY_A );
        $p2p_type   = $p2p_record->p2p_type;

        // Log for both records
        dt_activity_insert(
            [
                'action'         => $action,
                'object_type'    => $p2p_from['post_type'],
                'object_subtype' => 'p2p',
                'object_id'      => $p2p_from['ID'],
                'object_name'    => $p2p_from['post_title'],
                'meta_id'        => $p2p_id,
                'meta_key'       => $p2p_type,
                'meta_value'     => $p2p_to['ID'], // i.e. the opposite record of the object in the p2p
                'meta_parent'    => '',
                'object_note'    => '',
                'field_type'     => "connection from"
            ]
        );

        dt_activity_insert(
            [
                'action'         => $action,
                'object_type'    => $p2p_to['post_type'],
                'object_subtype' => 'p2p',
                'object_id'      => $p2p_to['ID'],
                'object_name'    => $p2p_to['post_title'],
                'meta_id'        => $p2p_id,
                'meta_key'       => $p2p_type,
                'meta_value'     => $p2p_from['ID'], // i.e. the opposite record of the object in the p2p
                'meta_parent'    => '',
                'object_note'    => '',
                'field_type'     => "connection to",
            ]
        );

    }

    //note: delete is given an array of ids
    public function hooks_p2p_deleted( $p2p_ids ) {
        $this->hooks_p2p_created( $p2p_ids[0], $action = 'disconnected from' );
    }

    public function post_meta_deleted( $meta_id, $object_id, $meta_key, $meta_value = '', $new = false ) {
        if ( strpos( $meta_key, "_details" ) === false ) {
            $this->hooks_updated_post_meta( $meta_id[0], $object_id, $meta_key, $meta_value, $new, true );
        }
    }

    /**
     * @param $code
     * @param $message
     * @param $data
     * @param $wp_error
     *
     * @see /wp-includes/class-wp-error.php:206
     *
     */
    public function hooks_error_post( $code, $message, $data, $wp_error ) {
        if ( ! empty( $wp_error ) ) {
            $current_migration = get_option( 'dt_migration_number' );
            if ( empty( $current_migration ) || intval( $current_migration ) < 40 ){
                return;
            }
            $activity = [
                'action'         => 'error_log',
                'object_type'    => '',
                'object_subtype' => '',
                'object_id'      => 0,
                'object_name'    => '',
                'object_note'    => $message,
                'meta_id'        => '',
                'meta_key'       => $code,
                'meta_value'     => maybe_serialize( $data ),
                'meta_parent'    => '',
                'hist_time'      => time() - 1,
            ];

            dt_activity_insert( $activity );
        }
    }

}
