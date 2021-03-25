<?php
/**
 * Show a modal with the latest release news when the user logs in.
 * These string are left untranslated.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

function dt_release_modal() {
    $current_release_version = 1; // increment this number with each new release modal

    $theme_version = wp_get_theme()->version;
    $last_release_notification = get_user_option( 'dt_release_notification', get_current_user_id() );
    if ( $last_release_notification >= $current_release_version || version_compare( $theme_version, '1.1', '>=' ) ){
        return;
    }

    update_user_option( get_current_user_id(), 'dt_release_notification', $current_release_version );
    ?>
    <script>
      jQuery(document).ready(function() {
        let content = jQuery('.off-canvas-content')

        content.append(`
            <div id='release-modal' class='reveal medium' data-reveal>

                <h3>Release Announcement!</h3>
                <h4>Disciple.Tools Theme Version 1.0.0</h4>
                <hr>
                <h5>The Disciple.Tools community is happy to announce some major updates:</h5>
                <ul>
                    <li><strong>Contact Types</strong>: Personal Contacts, Access Contacts and Connection Contacts.</li>
                    <li><strong>UI Upgrades</strong>: Upgraded Lists and Records Pages.</li>
                    <li><strong>Modular Roles and Permissions.</strong></li>
                    <li><strong>Enhanced Customization</strong>: New "modules" feature and the DMM and Access modules.</li>
                </ul>

                <p>See full list of changes <a href="https://disciple.tools/news/disciple-tools-theme-version-1-0-changes-and-new-features/" target="_blank">here</a>.</p>

                <hr>

                <h5>Subscribe <a href="http://eepurl.com/dP9kR5" target="_blank">here</a> for future news and announcements!</h5>
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
