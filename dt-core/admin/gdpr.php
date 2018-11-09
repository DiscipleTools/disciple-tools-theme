<?php
/**
 * General class for GDPR functions
 */

/**
 * Scrub the user data from comment content
 *
 * @param      $post_id
 * @param bool $full_object true will return the entire comment object, all fields;
 *                          false will only return comment_content, comment_date
 *
 * @return array|int
 */
function dt_get_comments_with_redacted_user_data( $post_id ) {
    $comments = get_comments( [ 'post_id' => $post_id ] );
    if ( empty( $comments ) ) {
        return $comments;
    }
    $email_note = __('redacted email');
    $name_note = __('redacted name');
    $redacted_note = __('redacted');

    $users = get_users();

    foreach ( $comments as $index => $comment ) {
        $comment_content = $comment->comment_content;

        // replace @mentions with user number
        preg_match_all( '/\@\[(.*?)\]\((.+?)\)/', $comment_content, $matches );
        foreach ( $matches[0] as $match_key => $match ){
            $comment_content = str_replace( $match, '@' . $redacted_note . '_' . $matches[2][$match_key], $comment_content );
        }

        // replace non-@mention references to login names, display names, or user emails
        foreach ( $users as $user ) {
            if ( ! empty( $user->data->user_login ) ) {
                $comment_content = str_replace( $user->data->user_login, '(' . $name_note . ')', $comment_content );
            }
            if ( ! empty( $user->data->display_name ) ) {
                $comment_content = str_replace( $user->data->display_name, '(' . $name_note . ')', $comment_content );
            }
            if ( ! empty( $user->data->user_nicename ) ) {
                $comment_content = str_replace( $user->data->user_nicename, '(' . $name_note . ')', $comment_content );
            }
            if ( ! empty( $user->data->user_email ) ) {
                $comment_content = str_replace( $user->data->user_email, '(' . $email_note . ')', $comment_content );
            }
        }

        // replace duplicate notes
        $comment_content = str_replace( site_url(), '#', $comment_content );

        $comments[$index]->comment_content = $comment_content;
    }

    return $comments;
}

/**
 * wp_privacy_anonymize_data
 */