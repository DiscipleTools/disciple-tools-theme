<?php

/**
 * Show a modal prompting the user with the following:
 *  - Default language to be adopted following initial login.
 */

if ( !defined( 'ABSPATH' ) ){
    exit; // Exit if accessed directly
}

/* Process $_POST content */
// We're not checking the nonce here because update_user_contact_info will
// phpcs:ignore
if ( isset( $_POST['user_update_nonce'] ) ){
    Disciple_Tools_Users::update_user_contact_info();
}

function dt_user_initial_setup_modal(): void{
    if ( !is_user_logged_in() ){
        return;
    }

    // Ensure currently within D.T front end page.
    $url = dt_get_url_path( true );
    if ( !is_archive() && !is_single() && !isset( apply_filters( 'desktop_navbar_menu_options', [] )[untrailingslashit( $url )] ) ){
        return;
    }

    // Determine if user has already setup default language.
    $default_language = get_user_meta( get_current_user_id(), 'dt_user_initial_setup_default_language', true );
    if ( $default_language !== '' ){
        return;
    }

    // Proceed with modal display.
    ?>
    <script>
        jQuery(document).ready(function () {
            let content = jQuery('.off-canvas-content');
            let modal_html = `
                <div id='user_notify_modal' class='reveal medium' data-reveal>
                    <div class="release-banner">
                        <div class="release-banner-text">
                            <h3><?php esc_html_e( 'Initial User Setup!', 'disciple_tools' ); ?></h3>
                            <br>
                        </div>
                    </div>

                    <div id="release-modal-content">

                        <div class="dt-tab-wrapper">
                            <a href="#" id="notify_modal_default_language_tab" data-content="default_language_content" class="dt-tab dt-tab-active"><?php esc_html_e( 'Default Language', 'disciple_tools' ); ?></a>
                        </div>

                        <div class="dt-tab-content" id="default_language_content">
                            <?php dt_user_default_language_html(); ?>
                        </div>

                    </div>

                    <hr>
                    <p class="center"><button type="button" class="button hollow" data-close>Close</button>
                    <button class="close-button white-button" data-close aria-label="<?php esc_html_e( 'Close Modal', 'disciple_tools' ); ?>" type="button">
                        <span aria-hidden="true">&times;</span>
                    </button>

                </div>
            `;

            // Append modal content and display.
            content.append(modal_html);
            let div = jQuery('#user_notify_modal');
            new Foundation.Reveal(div);
            div.foundation('open');
        });
    </script>
    <?php
}

add_action( 'wp_head', 'dt_user_initial_setup_modal' );

function dt_user_default_language_html(): void{
    $languages = dt_get_available_languages();
    $current_user = wp_get_current_user();
    $user_default_language = get_user_locale( $current_user->ID  ) ?? get_option( 'dt_user_default_language', 'en_US' );
    ?>
    <form method="post">
        <?php wp_nonce_field( 'user_' . $current_user->ID . '_update', 'user_update_nonce', false, true ); ?>

        <p>
            <select id="locale" name="locale">
                <?php
                foreach ( $languages as $language ){
                    ?>
                    <option
                        value="<?php echo esc_html( $language['language'] ); ?>" <?php selected( $user_default_language === $language['language'] ) ?>>
                        <?php echo esc_html( !empty( $language['flag'] ) ? $language['flag'] . ' ' : '' ); ?><?php echo esc_html( $language['native_name'] ); ?>
                    </option>
                    <?php
                }
                ?>
            </select>
            <table style="border: 0px;">
                <tbody style="border: 0px;">
                <tr style="border: 0px;">
                    <td style="vertical-align: top;">
                        <a href="https://disciple.tools/translation/"
                           target="_blank"><?php esc_html_e( 'Help translate or add new language', 'disciple_tools' ) ?></a>
                    </td>
                    <td>
                            <span style="float:right;"><button type="submit" id="user_default_language_update"
                                                               class="button float-right"><?php esc_html_e( 'Update', 'disciple_tools' ) ?></button></span>
                    </td>
                </tr>
                </tbody>
            </table>
        </p>
    </form>
    <?php
}
