<?php
/**
 * Show a modal with the latest release news when the user logs in.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

function dt_release_modal() {
    if ( !is_user_logged_in() ){
        return;
    }
    $url = dt_get_url_path();
    //bail if not an a D.T front end page.
    if ( !is_archive() && !is_single() && !isset( apply_filters( "desktop_navbar_menu_options", [] )[str_replace( '/', '', $url )] ) ){
        return;
    }
    $show_notification_for_theme_version = '1.14.0'; // increment this number with each new release modal

    $theme_version = wp_get_theme()->version;
    $last_release_notification = get_user_meta( get_current_user_id(), 'dt_release_notification', true );

    if ( empty( $last_release_notification ) ) {
        $last_release_notification = '1.0.0';
    }

    if ( version_compare( $last_release_notification, $show_notification_for_theme_version, '>=' ) ){
        return;
    }
    require_once( get_template_directory().'/dt-core/libraries/parsedown/Parsedown.php' );

    update_user_meta( get_current_user_id(), 'dt_release_notification', $show_notification_for_theme_version );
    ?>
    <script>
      jQuery(document).ready(function() {
        let content = jQuery('.off-canvas-content')

        content.append(`
            <div id='release-modal' class='reveal medium' data-reveal>

                <h3>Release Announcement!</h3>
                <h4>Disciple.Tools Theme Version <?php echo esc_html( $show_notification_for_theme_version ); ?></h4>
                <hr>
                <?php echo wp_kses_post( dt_load_github_release_markdown( $show_notification_for_theme_version ) ); ?>
                <hr>
                <h5>See all D.T News <a href="https://disciple.tools/news" target="_blank">here</a></h5>
                <br>

                <p class="center"><button type="button" class="button hollow" data-close>Close</button>

                <button class="close-button" data-close aria-label="Close Accessible Modal" type="button">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

        `);

        let div = jQuery('#release-modal');
        new Foundation.Reveal( div );
        div.foundation('open');
      })
    </script>
    <?php
}
add_action( 'wp_head', 'dt_release_modal' );

function dt_load_github_release_markdown( $tag, $repo = "DiscipleTools/disciple-tools-theme" ){

    if ( empty( $repo ) || empty( $tag ) ){
        return false;
    }
    $release = get_transient( 'dt_release_notification_' . $tag );
    if ( !empty( $release ) ){
        return $release;
    }

    $url = "https://api.github.com/repos/" . esc_attr( $repo ) . "/releases/tags/" . esc_attr( $tag );
    $response = wp_remote_get( $url );

    $data_result = wp_remote_retrieve_body( $response );

    if ( ! $data_result ) {
        return false;
    }
    $release = json_decode( $data_result, true );

    // end check on readme existence
    if ( !empty( $release["body"] ) ){
        $parsedown = new Parsedown();
        $release = $parsedown->text( $release["body"] );
        set_transient( 'dt_release_notification_' . $tag, $release, DAY_IN_SECONDS );
        return $release;
    }
}
