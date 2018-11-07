<?php
/**
 * General class for GDPR functions
 */

function dt_redact_user_data( $data ) {
    $users = get_users( );
    dt_write_log( $users );

    $user_names = [];
    $login_names = [];
    $last_names = [];
    $emails = [];

    $serialized = maybe_serialize( $data );
    foreach ( $users as $user ) {
        if ( ! empty( $user->data->user_login ) ) {
            $serialized = str_replace( trim( $user->data->user_login ), '***', $serialized );
        }
        if ( ! empty( $user->data->user_login ) ) {
            $serialized = str_replace( trim( $user->data->user_login ), '***', $serialized );
        }

    }
    foreach ( $user_names as $name ) {
        $serialized = str_replace( $name, '***', $serialized );
    }
    foreach ( $login_names as $name ) {
        $serialized = str_replace( $name, '***', $serialized );
    }
    foreach ( $last_names as $name ) {
        $serialized = str_replace( $name, '***', $serialized );
    }
    foreach ( $emails as $name ) {
        $serialized = str_replace( $name, '***', $serialized );
    }
    $data = maybe_unserialize( $serialized );

    return $data;
}