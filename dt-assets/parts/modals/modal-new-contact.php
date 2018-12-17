<div class="reveal" id="create-contact-modal" data-reveal data-reset-on-close>

    <p class="lead"><?php esc_html_e( 'Create contact', 'disciple_tools' )?></p>

    <form class="js-create-contact hide-after-contact-create">
        <label for="title">
            <?php esc_html_e( "Name of contact", "disciple_tools" ); ?>
        </label>
        <input name="title" type="text" placeholder="<?php esc_html_e( "Name", "disciple_tools" ); ?>" required aria-describedby="name-help-text">
        <p class="help-text" id="name-help-text"><?php esc_html_e( "This is required", "disciple_tools" ); ?></p>

        <div style="text-align: center">
            <button class="button loader js-create-contact-button" type="submit"><?php esc_html_e( "Create contact", "disciple_tools" ); ?></button>
        </div>
    </form>

    <p class="reveal-after-contact-create" style="display: none"><?php esc_html_e( "contact created", 'disciple_tools' ) ?>: <span id="new-contact-link"></span></p>


    <div class="grid-x">
        <button class="button button-cancel clear hide-after-contact-create" data-close aria-label="Close reveal" type="button">
            <?php esc_html_e( 'Cancel', 'disciple_tools' )?>
        </button>
        <button class="button reveal-after-contact-create button-cancel clear" data-close type="button" id="create-contact-return" style="display: none">
            <?php
            if ( is_singular( "contacts" )){
                esc_html_e( 'Return to Contact', 'disciple_tools' );
            } elseif ( is_singular( "contacts" )){
                esc_html_e( 'Return to contact', 'disciple_tools' );
            } else {
                esc_html_e( 'Return', 'disciple_tools' );
            }
            ?>
        </button>
        <a class="button reveal-after-contact-create" id="go-to-contact" style="display: none">
            <?php esc_html_e( 'Edit new contact', 'disciple_tools' )?>
        </a>
        <button class="close-button" data-close aria-label="Close modal" type="button">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
</div>
