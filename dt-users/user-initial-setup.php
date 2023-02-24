<?php

/**
 * Show a modal prompting the user with the following:
 *  - Default language to be adopted following initial login.
 */

if ( !defined( 'ABSPATH' ) ){
    exit; // Exit if accessed directly
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

    $languages = dt_get_available_languages();
    $current_user = wp_get_current_user();
    $user_default_language = get_user_locale( $current_user->ID ) ?? get_option( 'dt_user_default_language', 'en_US' );

    // Proceed with modal display.
    ?>
    <script>
        jQuery(document).ready(function () {
            let content = jQuery('.off-canvas-content');
            let modal_html = `
                <div id='user_notify_modal' class='reveal medium' data-reveal>
                    <form method='post'>
                        <?php wp_nonce_field( 'user_' . $current_user->ID . '_update', 'user_update_nonce', false, true ); ?>
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

                                <select id="locale" name="locale">
                                    <?php foreach ( $languages as $language ) :?>
                                        <option value="<?php echo esc_html( $language['language'] ); ?>" <?php selected( $user_default_language === $language['language'] ) ?>>
                                            <?php echo esc_html( !empty( $language['flag'] ) ? $language['flag'] . ' ' : '' ); ?><?php echo esc_html( $language['native_name'] ); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>

                                <p>
                                    <a href='https://disciple.tools/translation/' target='_blank'>
                                       <img class='dt-icon' style='vertical-align:text-bottom' src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/open-link.svg' ) ?>"/>
                                       <?php esc_html_e( 'Translate Disciple.Tools into your language or help make a translation better.', 'disciple_tools' ) ?></a>
                                </p>

                            </div>
                        </div>

                        <hr>
                        <button type="button" class="button hollow" data-close>
                            <?php esc_html_e( 'Close', 'disciple_tools' ) ?>
                        </button>
                        <button type='submit' id='user_default_language_update' class='button' data-initial_locale='<?php echo esc_attr( $user_default_language ) ?>'>
                            <?php esc_html_e( 'Save', 'disciple_tools' ) ?>
                        </button>

                        <button class="close-button white-button" data-close aria-label="<?php esc_html_e( 'Close Modal', 'disciple_tools' ); ?>" type="button">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </form>
                </div>
            `;

            // Append modal content and display.
            content.append(modal_html);
            let div = jQuery('#user_notify_modal');
            new Foundation.Reveal(div);
            div.foundation('open');

            // Handle initial user language setup submissions.
            jQuery('#user_default_language_update').on('click', function (e) {
                e.preventDefault();

                let modal = jQuery('#user_notify_modal');
                let initial_locale = jQuery('#user_default_language_update').data('initial_locale');
                let updated_locale = jQuery('#locale').val();

                // Post updated language locale.
                makeRequest("POST", `user/update`, {
                    'locale': updated_locale
                }, 'dt/v1/')
                .done(response => {

                    // Assume all is well in the world, refresh page (if any changes) and close modal!
                    modal.foundation('close');

                    if ( initial_locale !== updated_locale ) {
                        location.reload();
                    }
                })
                .catch((e) => {
                    console.log(e);
                    modal.foundation('close');
                });
            });
        });
    </script>
    <?php
}

add_action( 'wp_head', 'dt_user_initial_setup_modal' );

