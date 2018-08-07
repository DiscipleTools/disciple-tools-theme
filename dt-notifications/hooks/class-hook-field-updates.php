<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

/**
 * Class Disciple_Tools_Notifications_Hook_Field_Updates
 */
class Disciple_Tools_Notifications_Hook_Field_Updates extends Disciple_Tools_Notifications_Hook_Base
{
    /**
     * Disciple_Tools_Notifications_Hook_Field_Updates constructor.
     */
    public function __construct() {
//        add_action( "added_post_meta", [ &$this, 'hooks_added_post_meta' ], 10, 4 );
//        add_action( "updated_post_meta", [ &$this, 'hooks_updated_post_meta' ], 10, 4 );

        parent::__construct();
    }

    /**
     * Hook the add event of a post meta
     *
     * @param $mid
     * @param $object_id
     * @param $meta_key
     * @param $meta_value
     */
    public function hooks_added_post_meta( $mid, $object_id, $meta_key, $meta_value ) {

        return $this->hooks_updated_post_meta( $mid, $object_id, $meta_key, $meta_value );
    }

    /**
     * Process specific meta changes and creates notifications for them
     *
     * @param      $meta_id
     * @param      $object_id
     * @param      $meta_key
     * @param      $meta_value
     */
    public function hooks_updated_post_meta( $meta_id, $object_id, $meta_key, $meta_value ) {
        //move notifications to hook on post create/update
    }


    /**
     * Create notification activity
     *
     * @param int    $user_id        (This is the user that the notification is being assigned to)
     * @param int    $source_user_id (This is the user that the notification is coming from)
     * @param int    $post_id        (This is contacts, groups, locations post type id.)
     * @param int    $secondary_item_id
     * @param string $notification_name
     * @param string $notification_action
     * @param string $notification_note
     * @param        $date_notified
     * @param string $field_key
     * @param string $field_value
     */
    protected function add_notification( int $user_id, int $source_user_id, int $post_id, int $secondary_item_id, string $notification_name, string $notification_action, string $notification_note, $date_notified, string $field_key = '', string $field_value = '' ) {

        dt_notification_insert(
            [
                'user_id'             => $user_id,
                'source_user_id'      => $source_user_id,
                'post_id'             => $post_id,
                'secondary_item_id'   => $secondary_item_id,
                'notification_name'   => $notification_name,
                'notification_action' => $notification_action,
                'notification_note'   => $notification_note,
                'date_notified'       => $date_notified,
                'is_new'              => 1,
                'field_key'           => $field_key,
                'field_value'         => $field_value,
            ]
        );
    }

    /**
     * Delete single notification
     *
     * @param int    $user_id
     * @param int    $post_id
     * @param int    $secondary_item_id
     * @param string $notification_name
     * @param        $date_notified
     */
    protected function delete_single_notification( int $user_id, int $post_id, int $secondary_item_id, string $notification_name, $date_notified ) {

        dt_notification_delete(
            [
                'user_id'           => $user_id,
                'post_id'           => $post_id,
                'secondary_item_id' => $secondary_item_id,
                'notification_name' => $notification_name,
                'date_notified'     => $date_notified,
            ]
        );
    }

    /**
     * Delete all notifications by post and notification name (i.e. type)
     *
     * @param int    $post_id
     * @param string $notification_name
     */
    protected function delete_by_post( int $post_id, string $notification_name ) {

        dt_notification_delete_by_post(
            [
                'post_id'           => $post_id,
                'notification_name' => $notification_name,
            ]
        );
    }

}
