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
    $url = dt_get_url_path( true );
    //bail if not an a D.T front end page.
    if ( !is_archive() && !is_single() && !isset( apply_filters( 'desktop_navbar_menu_options', [] )[untrailingslashit( $url )] ) ){
        return;
    }
    $show_notification_for_theme_version = '1.33.0'; // increment this number with each new release modal

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
                <div class="release-banner">
                    <div class="release-banner-text">
                        <h3><?php esc_html_e( 'Release Announcement!', 'disciple_tools' ); ?></h3>
                        <h4><?php echo sprintf( esc_html__( 'Disciple.Tools Theme Version %1$s', 'disciple_tools' ), esc_html( $show_notification_for_theme_version ) ); ?></h4>
                    </div>
                </div>
                <div id="release-modal-content">
                    <div class="dt-tab-wrapper">
                        <a href="#" id="release-modal-theme-news-tab" data-content="theme-news-content" class="dt-tab dt-tab-active"><?php esc_html_e( 'Theme News', 'disciple_tools' ); ?></a>
                        <a href="#" id="release-modal-plugin-new-tab" data-content="plugin-news-content" class="dt-tab"><?php esc_html_e( 'Other News', 'disciple_tools' ); ?></a>
                        <a href="#" id="release-modal-get-involved-tab" data-content="get-involved-content" class="dt-tab"><?php esc_html_e( 'Get Involved', 'disciple_tools' ); ?></a>
                    </div>
                    <div class="dt-tab-content" id="theme-news-content">
                        <p>
                            <?php echo wp_kses_post( dt_load_github_release_markdown( $show_notification_for_theme_version ) ); ?>
                        </p>
                    </div>
                    <div class="dt-tab-content dt-hide-content" id="plugin-news-content">
                        <h3 class="dt-tab-content-heading"><?php esc_html_e( 'Other News', 'disciple_tools' ); ?></h3>
                        <p>
                            <?php $plugin_news_links = dt_get_plugins_news_links(); ?>
                            <?php if ( !$plugin_news_links ) : ?>
                                <i><?php esc_html_e( 'Other news not available.', 'disciple_tools' ); ?></i>
                            <?php else : ?>
                                <?php foreach ( $plugin_news_links as $plugin_news_link ) : ?>
                                    <li><a href="<?php echo esc_attr( $plugin_news_link['url'] ); ?>" target="_blank"><?php echo esc_html( $plugin_news_link['title'] ); ?></a></li>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </p>
                        <p>
                            <a href="https://disciple.tools/news-categories/dt-plugin-releases/" target="_blank"><?php echo esc_html( 'Go to plugin news page', 'disciple_tools' ); ?></a>
                        </p>
                    </div>
                    <div class="dt-tab-content dt-hide-content" id="get-involved-content">
                        <p>
                            <h5>
                                <svg style="width:25px;" viewBox="0 0 24 24"><path fill="currentColor" d="M20 17Q20.86 17 21.45 17.6T22.03 19L14 22L7 20V11H8.95L16.22 13.69Q17 14 17 14.81 17 15.28 16.66 15.63T15.8 16H13L11.25 15.33L10.92 16.27L13 17H20M16 3.23Q17.06 2 18.7 2 20.06 2 21 3T22 5.3Q22 6.33 21 7.76T19.03 10.15 16 13Q13.92 11.11 12.94 10.15T10.97 7.76 10 5.3Q10 3.94 10.97 3T13.31 2Q14.91 2 16 3.23M.984 11H5V22H.984V11Z" /></svg>
                                <b style="vertical-align:text-bottom;"><?php esc_html_e( 'Give', 'disciple_tools' ); ?></b>
                            </h5>
                            <p><?php esc_html_e( 'Make Disciple.Tools better by joining us financially.', 'disciple_tools' ); ?><br><a href="https://disciple.tools/give/" target="_blank"><?php esc_html_e( 'Join us here', 'disciple_tools' ); ?></a></p>
                        </p>
                        <br>
                        <p>
                            <h5>
                                <svg style="width:25px;" viewBox="0 0 24 24"><path fill="currentColor" d="M12.87,15.07L10.33,12.56L10.36,12.53C12.1,10.59 13.34,8.36 14.07,6H17V4H10V2H8V4H1V6H12.17C11.5,7.92 10.44,9.75 9,11.35C8.07,10.32 7.3,9.19 6.69,8H4.69C5.42,9.63 6.42,11.17 7.67,12.56L2.58,17.58L4,19L9,14L12.11,17.11L12.87,15.07M18.5,10H16.5L12,22H14L15.12,19H19.87L21,22H23L18.5,10M15.88,17L17.5,12.67L19.12,17H15.88Z" /></svg>
                                <b style="vertical-align:text-bottom;"><?php esc_html_e( 'Translate', 'disciple_tools' ); ?></b>
                            </h5>
                            <p><?php esc_html_e( 'Translate Disciple.Tools into your language or help make a translation better.', 'disciple_tools' ); ?><br><a href="https://poeditor.com/join/project/KcPvw3oaKD" target="_blank"><?php esc_html_e( 'Click here', 'disciple_tools' ); ?></a></p>
                        </p>
                        <br>
                        <p>
                            <h5>
                                <svg style="width:24px;height:24px" viewBox="0 0 24 24"><path fill="currentColor" d="M12.89,3L14.85,3.4L11.11,21L9.15,20.6L12.89,3M19.59,12L16,8.41V5.58L22.42,12L16,18.41V15.58L19.59,12M1.58,12L8,5.58V8.41L4.41,12L8,15.58V18.41L1.58,12Z" /></svg>
                                <b style="vertical-align:text-bottom;"><?php esc_html_e( 'Build or Document', 'disciple_tools' ); ?></b>
                            </h5>
                            <p><?php esc_html_e( 'Make Disciple.Tools better by building a feature or helping us make the documentation better.', 'disciple_tools' ); ?><br><a href="https://disciple.tools/join-the-community/" target="_blank"><?php esc_html_e( 'Find out more', 'disciple_tools' ); ?></a></p>
                        </p>
                    </div>
                </div>
                <br>
                <p class="center"><button type="button" class="button hollow" data-close>Close</button>
                <button class="close-button white-button" data-close aria-label="Close Accessible Modal" type="button">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        `);

        jQuery('.dt-tab').on('click', function() {
            var currentVisibleTabName = $('.dt-tab-active').data('content');
            $('#' + currentVisibleTabName ).addClass('dt-hide-content');
            $('.dt-tab-active').removeClass('dt-tab-active');

            var desiredVisibleTabName = $(this).data('content');
            $(this).addClass('dt-tab-active');
            $( '#' + desiredVisibleTabName ).removeClass('dt-hide-content');
        });

        let div = jQuery('#release-modal');
        new Foundation.Reveal( div );
        div.foundation('open');
      })
    </script>
    <?php
}
add_action( 'wp_head', 'dt_release_modal' );

function dt_get_plugins_news_links() {
    $plugin_news_url = 'https://disciple.tools/news-categories/other/feed/';
    if ( !function_exists( 'fetch_feed' ) ) {
        return;
    }
    $plugin_news_items = get_transient( 'dt_plugin_news_items' );
    if ( !empty( $plugin_news_items ) ) {
        return $plugin_news_items;
    }
    $feed = fetch_feed( $plugin_news_url );
    if ( is_wp_error( $feed ) ) {
        return;
    }
    $feed->set_output_encoding( 'UTF-8' );
    $feed->handle_content_type();
    $feed->set_cache_duration( 86400 );
    $limit = $feed->get_item_quantity( 10 );
    $feed_items = $feed->get_items( 0, $limit );
    $plugin_news_items = [];
    foreach ( $feed_items as $feed_item ) {
        $plugin_news_items[] = [
            'url' => $feed_item->get_permalink(),
            'title' => $feed_item->get_title(),
        ];
    }
    set_transient( 'dt_plugin_news_items', $plugin_news_items, DAY_IN_SECONDS );
    return $plugin_news_items;
}
function dt_load_github_release_markdown( $tag, $repo = 'DiscipleTools/disciple-tools-theme' ){
    if ( empty( $repo ) || empty( $tag ) ){
        return false;
    }
    $release = get_transient( 'dt_release_notification_' . $tag );
    if ( !empty( $release ) ){
        return $release;
    }

    $url = 'https://api.github.com/repos/' . esc_attr( $repo ) . '/releases/tags/' . esc_attr( $tag );
    $response = wp_remote_get( $url );

    $data_result = wp_remote_retrieve_body( $response );

    if ( !$data_result ) {
        return false;
    }
    $release = json_decode( $data_result, true );

    // end check on readme existence
    if ( !empty( $release['body'] ) ){
        $parsedown = new Parsedown();
        $release = $parsedown->text( $release['body'] );
        set_transient( 'dt_release_notification_' . $tag, $release, DAY_IN_SECONDS );
        return $release;
    }
}
