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
    public function __construct()
    {
        add_action( "added_post_meta", [ &$this, 'hooks_added_post_meta' ], 10, 4 );
        add_action( "updated_post_meta", [ &$this, 'hooks_updated_post_meta' ], 10, 4 );

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
    public function hooks_added_post_meta( $mid, $object_id, $meta_key, $meta_value )
    {

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
    public function hooks_updated_post_meta( $meta_id, $object_id, $meta_key, $meta_value )
    {

        // check if $meta_value is empty
        if ( empty( $meta_value ) || $meta_key === "_edit_lock") {
            return;
        }
        // Don't fire off notifications when the contact represents a user.
        $contact_type = get_post_meta( $object_id, "type", true );
        if ($contact_type === "user"){
            return;
        }

        // Check for specific key or trigger
        if ( !( $meta_key == 'assigned_to'
            || $meta_key == 'requires_update'
            || strpos( $meta_key, "address" ) === 0
            || strpos( $meta_key, "contact" ) === 0
            || strpos( $meta_key, "milestone" ) === 0
        ) ) {
            return;
        }

        // get post meta assigned_to
        $assigned_to = get_post_meta( $object_id, $key = 'assigned_to', $single = true );
        if ( empty( $assigned_to ) ) { // if assigned_to is empty, there is no one to notify.
            return;
        }

        // parse assigned to
        $meta_array = explode( '-', $assigned_to ); // Separate the type and id
        $type = $meta_array[0]; // parse type
        $user_id = (int) $meta_array[1];

        // get source user id and check if same as notification target
        $source_user_id = get_current_user_id();
        if ( $source_user_id == $user_id || !$user_id ) {
            return;
        }

        $user = get_userdata( $user_id );
        $user_meta = get_user_meta( $user_id );



        // Configure switch statement
        $original_meta_key = '';
        if ( strpos( $meta_key, "address" ) === 0 || strpos( $meta_key, "contact" ) === 0 ) {
            $original_meta_key = $meta_key;
            $meta_key = 'contact_info_update';
        } elseif ( strpos( $meta_key, "milestone" ) === 0 ) {
            $original_meta_key = $meta_key;
            $meta_key = 'milestone';
        }

        $notification = [
            'user_id'             => $user_id,
            'source_user_id'      => $source_user_id,
            'post_id'             => (int) $object_id,
            'secondary_item_id'   => (int) $meta_id,
            'notification_name'   => $meta_key,
            'notification_action' => 'alert',
            'notification_note'   => '',
            'date_notified'       => current_time( 'mysql' ),
            'is_new'              => 1,
            'field_key'           => '',
            'field_value'         => '',
        ];
        $post = get_post( $object_id );
        $post_title = sanitize_text_field( $post->post_title );
        $subject = __( "Updates on contact", 'disciple_tools' );

        // Switch between types of notifications
        switch ( $meta_key ) {

            case 'assigned_to':

                $notification_name = 'assigned_to';

                /**
                 * Delete all notifications with matching post_id and notification_name
                 * This prevents an assigned_to notification remaining in another persons inbox, that has since been
                 * assigned to someone else. The Activity log keeps the historical data, but this notifications table
                 * only should keep real status data.
                 */
                $this->delete_by_post(
                    $object_id,
                    $notification_name
                );

                $subject = __( 'You have been assigned a new contact!', 'disciple_tools' );

                break;

            case 'requires_update':

                if ( $meta_value == 'yes' ) {

                    $notification_name = 'requires_update';

                    /**
                     * Delete all notifications with matching post_id and notification_name
                     * This prevents an assigned_to notification remaining in another persons inbox, that has since been
                     * assigned to someone else. The Activity log keeps the historical data, but this notifications table
                     * only should keep real status data.
                     */
                    $this->delete_by_post(
                        $object_id,
                        $notification_name
                    );

                    $subject = __( 'Update requested!', 'disciple_tools' );

                }

                break;

            case 'contact_info_update':
                $notification["field_key"] = $original_meta_key;
                $subject = __( 'Changes to your contact.', 'disciple_tools' );

                break;

            case 'milestone':
                $meta_value == 'yes' ? $value = 'added' : $value = 'removed';
                $notification["field_key"] = $original_meta_key;
                $notification["field_value"] = $value;
                $subject = __( 'Milestones update on', 'disciple_tools' ) . " " . $post_title;

                break;

            default:
                break;
        }
        $notification["notification_note"] = Disciple_Tools_Notifications::get_notification_message( $notification );

        if ( dt_user_notification_is_enabled( 'milestones_web', $user_meta, $user->ID ) ) {
            dt_notification_insert( $notification );
        }
        if ( dt_user_notification_is_enabled( 'milestones_email', $user_meta, $user->ID ) ) {
            $notification["notification_note"] .= "\r\n\r\n";
            $notification["notification_note"] .= 'Click here to reply: ' . home_url( '/' ) . get_post_type( $object_id ) . '/' . $object_id;

            dt_send_email(
                $user->user_email,
                $subject,
                $notification["notification_note"]
            );
        }

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
    protected function add_notification( int $user_id, int $source_user_id, int $post_id, int $secondary_item_id, string $notification_name, string $notification_action, string $notification_note, $date_notified, string $field_key = '', string $field_value = '' )
    {

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
    protected function delete_single_notification( int $user_id, int $post_id, int $secondary_item_id, string $notification_name, $date_notified )
    {

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
    protected function delete_by_post( int $post_id, string $notification_name )
    {

        dt_notification_delete_by_post(
            [
                'post_id'           => $post_id,
                'notification_name' => $notification_name,
            ]
        );
    }

}
