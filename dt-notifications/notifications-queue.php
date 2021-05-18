<?php

/**
 * Disciple_Tools_Notifications_Queue
 *
 * @class   Disciple_Tools_Notifications_Queue
 * @version ?
 * @since   ?
 * @package Disciple_Tools
 *
 */

if ( !defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Disciple_Tools_Notifications_Queue {

    const ALLOWED_SCHEDULES = [
        'hourly',
        'daily',
    ];

    public function __construct() {
        #
    }

    private function add_to_queue( object $notification, string $type ) {
        global $wpdb;

        $wpdb->insert(
            $wpdb->dt_notifications_queue,
            [
                'notification_id' => $notification->id,
                'type' => $type,
            ],
            [ '%d', '%s' ]
        );
    }

    /**
     * Add a notification to the queue to be emailed with the $schedule_time batch email
     *
     * $schedule_time can be one of 'hourly' | 'daily
     *
     * @param object $notification
     * @param string $schedule_time
     */
    public function schedule_email( object $notification, $schedule_time ) {
        if ( ! $notification ) {
            throw new Exception( "Notification must be supplied" );
        }
        if ( ! in_array( $schedule_time, self::ALLOWED_SCHEDULES, true ) ) {
            throw new Exception( "Notification Queue schedule $schedule_time not allowed" );
        }
        $this->add_to_queue( $notification, "email_$schedule_time" );
    }
}
