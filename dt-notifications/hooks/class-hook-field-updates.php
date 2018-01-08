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
        if ( empty( $meta_value ) ) {
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

        // Configure switch statement
        $original_meta_key = '';
        if ( strpos( $meta_key, "address" ) === 0 || strpos( $meta_key, "contact" ) === 0 ) {
            $original_meta_key = $meta_key;
            $meta_key = 'contact_info_update';
        } elseif ( strpos( $meta_key, "milestone" ) === 0 ) {
            $original_meta_key = $meta_key;
            $meta_key = 'milestone';
        }

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

                // get user or team assigned_to
                $meta_array = explode( '-', $meta_value ); // Separate the type and id
                $type = $meta_array[0]; // parse type
                $user_id = (int) $meta_array[1];

                // get source user id and check if same as notification target
                $source_user_id = get_current_user_id();
                if ( $source_user_id == $user_id || !$user_id ) {
                    return;
                }

                if ( $type == 'user' ) {

                    $user = get_userdata( $user_id );
                    $user_meta = get_user_meta( $user_id );

                    // web notification
                    if ( dt_user_notification_is_enabled( 'new_web', $user_meta, $user->ID ) ) {

                        $notification_note = 'You have been assigned <a href="' . home_url( '/' ) . get_post_type( $object_id ) . '/' . $object_id . '">' . strip_tags( get_the_title( $object_id ) ) . '</a>';

                        $this->add_notification(
                            $user_id,
                            $source_user_id,
                            $post_id = (int) $object_id,
                            $secondary_item_id = (int) $meta_id,
                            $notification_name,
                            $notification_action = 'alert',
                            $notification_note,
                            $date_notified = current_time( 'mysql' )
                        );

                        dt_write_log( '@new_web' ); // todo remove after dev
                    }

                    // email notification
                    if ( dt_user_notification_is_enabled( 'new_email', $user_meta, $user->ID ) ) {

                        $message = 'You have been assigned a new contact: '. strip_tags( get_the_title( $object_id ) ) .'. View the new contact at: ' . home_url( '/' ) . get_post_type( $object_id ) . '/' . $object_id;

                        dt_send_email(
                            $user->user_email,
                            'Disciple Tools: You have been assigned a new contact!',
                            $message
                        );

                        dt_write_log( '@new_email' ); // todo remove after dev
                    }
                } else { // if group, do nothing. Option for future development.
                    return;
                }

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
                    if ( $source_user_id == $user_id ) {
                        return;
                    }

                    if ( $type == 'user' ) {

                        $user = get_userdata( $user_id );
                        $user_meta = get_user_meta( $user_id );

                        // web notification
                        if ( dt_user_notification_is_enabled( 'updates_web', $user_meta, $user->ID ) ) {

                            $notification_note = 'An update on <a href="' . home_url( '/' ) . get_post_type( $object_id ) . '/' . $object_id . '">' . strip_tags( get_the_title( $object_id ) ) . '</a> is requested.';

                            $this->add_notification(
                                $user_id,
                                $source_user_id,
                                $post_id = (int) $object_id,
                                $secondary_item_id = (int) $meta_id,
                                $notification_name,
                                $notification_action = 'alert',
                                $notification_note,
                                $date_notified = current_time( 'mysql' )
                            );

                            dt_write_log( '@updates_web' ); // todo remove after dev
                        }

                        // email notification
                        if ( dt_user_notification_is_enabled( 'updates_email', $user_meta, $user->ID ) ) {

                            $message = 'You have an update requested for: '. strip_tags( get_the_title( $object_id ) ) .'. Link for updating contact: ' . home_url( '/' ) . get_post_type( $object_id ) . '/' . $object_id;

                            dt_send_email(
                                $user->user_email,
                                'Disciple Tools: Update requested!',
                                $message
                            );

                            dt_write_log( '@updates_email' ); // todo remove after dev
                        }
                    } else { // if group, do nothing. Option for future development.
                        return;
                    }
                } // end if requires update = yes

                break;

            case 'contact_info_update':

                $notification_name = 'contact_info_update';

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
                if ( $source_user_id == $user_id ) {
                    return;
                }

                // parse kind of details changed
                if ( strpos( $meta_key, "address" ) === 0 ) {
                    $element = 'Address';
                } elseif ( strpos( $meta_key, "contact" ) === 0 ) {
                    $element = 'Contact';
                } else {
                    $element = 'Contact';
                }

                if ( $type == 'user' ) {

                    $user = get_userdata( $user_id );
                    $user_meta = get_user_meta( $user_id );
                    $source_user = get_userdata( $source_user_id );

                    // web notification
                    if ( dt_user_notification_is_enabled( 'changes_web', $user_meta, $user->ID ) ) {

                        $notification_note = $element . ' details on <a href="' . home_url( '/' ) .
                            get_post_type( $object_id ) . '/' . $object_id . '">' .
                            strip_tags( get_the_title( $object_id ) ) . '</a> were modified by <strong>' .
                            $source_user->display_name . '</strong>';

                        $this->add_notification(
                            $user_id,
                            $source_user_id,
                            $post_id = (int) $object_id,
                            $secondary_item_id = (int) $meta_id,
                            $notification_name,
                            $notification_action = 'alert',
                            $notification_note,
                            $date_notified = current_time( 'mysql' )
                        );

                        dt_write_log( '@changes_web' ); // todo remove after dev
                    }

                    // email notification
                    if ( dt_user_notification_is_enabled( 'changes_email', $user_meta, $user->ID ) ) {

                        $message = 'There were changes made to: '. strip_tags( get_the_title( $object_id ) ) .'. Link for viewing contact: ' . home_url( '/' ) . get_post_type( $object_id ) . '/' . $object_id;

                        dt_send_email(
                            $user->user_email,
                            'Disciple Tools: Changes to your contact added.',
                            $message
                        );

                        dt_write_log( '@changes_email' ); // todo remove after dev
                    }
                } else { // if group, do nothing. Option for future development.
                    return;
                }

                break;

            case 'milestone':

                $notification_name = 'milestone';

                // get post meta assigned_to
                $assigned_to = get_post_meta( $object_id, $key = 'assigned_to', $single = true );
                if ( empty( $assigned_to ) ) { // if assigned_to is empty, there is contact owner to notify.
                    return;
                }

                // parse assigned to
                $meta_array = explode( '-', $assigned_to ); // Separate the type and id
                $type = $meta_array[0]; // parse type
                $user_id = (int) $meta_array[1];

                // get source user id and check if same as notification target
                $source_user_id = get_current_user_id();
                if ( $source_user_id == $user_id ) {
                    return;
                }

                switch ( $original_meta_key ) {
                    case 'milestone_belief':
                        $element = '"Belief" Milestone';
                        break;
                    case 'milestone_can_share':
                        $element = '"Can Share" Milestone';
                        break;
                    case 'milestone_sharing':
                        $element = '"Actively Sharing" Milestone';
                        break;
                    case 'milestone_baptized':
                        $element = '"Baptized" Milestone';
                        break;
                    case 'milestone_baptizing':
                        $element = '"Baptizing" Milestone';
                        break;
                    case 'milestone_in_group':
                        $element = '"Is in a group" Milestone';
                        break;
                    case 'milestone_planting':
                        $element = '"Planting a group" Milestone';
                        break;
                    default:
                        $element = 'A Milestone';
                        break;
                }

                $meta_value == 'yes' ? $value = 'added' : $value = 'removed';

                if ( $type == 'user' ) {

                    $user = get_userdata( $user_id );
                    $user_meta = get_user_meta( $user_id );
                    $source_user = get_userdata( $source_user_id );

                    // web notification
                    if ( dt_user_notification_is_enabled( 'milestones_web', $user_meta, $user->ID ) ) {

                        $notification_note = $element . ' has been ' . $value . ' for <a href="' . home_url( '/' ) .
                            get_post_type( $object_id ) . '/' . $object_id . '">' .
                            strip_tags( get_the_title( $object_id ) ) . '</a> by <strong>' .
                            $source_user->display_name . '</strong>';

                        $this->add_notification(
                            $user_id,
                            $source_user_id,
                            $post_id = (int) $object_id,
                            $secondary_item_id = (int) $meta_id,
                            $notification_name,
                            $notification_action = 'alert',
                            $notification_note,
                            $date_notified = current_time( 'mysql' )
                        );

                        dt_write_log( '@milestones_web' ); // todo remove after dev
                    }

                    // email notification
                    if ( dt_user_notification_is_enabled( 'milestones_email', $user_meta, $user->ID ) ) {

                        $message = 'A milestone was update for '. strip_tags( get_the_title( $object_id ) ) .'. Link to view contact: ' . home_url( '/' ) . get_post_type( $object_id ) . '/' . $object_id;

                        dt_send_email(
                            $user->user_email,
                            'Disciple Tools: Milestones update on ' . strip_tags( get_the_title( $object_id ) ),
                            $message
                        );

                        dt_write_log( '@milestones_email' ); // todo remove after dev
                    }
                } else { // if group, do nothing. Option for future development.
                    return;
                }

                break;

            default:
                break;
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
     */
    protected function add_notification( int $user_id, int $source_user_id, int $post_id, int $secondary_item_id, string $notification_name, string $notification_action, string $notification_note, $date_notified )
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
