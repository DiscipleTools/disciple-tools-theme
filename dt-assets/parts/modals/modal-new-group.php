<div class="reveal" id="create-group-modal" data-reveal data-reset-on-close>

    <h3><?php esc_html_e( 'Create Group', 'disciple_tools' )?></h3>

    <form class="js-create-group hide-after-group-create">
        <label for="title">
            <?php esc_html_e( "Name of Group", "disciple_tools" ); ?>
        </label>
        <input name="title" type="text" placeholder="<?php echo esc_html_x( "Name", 'input field placeholder', 'disciple_tools' ); ?>" required aria-describedby="name-help-text">

        <div>
            <button class="button loader js-create-group-button" type="submit"><?php echo esc_html_x( "Create Group", 'button', 'disciple_tools' ); ?></button>
            <button class="button button-cancel clear hide-after-group-create" data-close aria-label="Close reveal" type="button">
                <?php echo esc_html_x( 'Cancel', 'button', 'disciple_tools' )?>
            </button>
        </div>
    </form>

    <p class="reveal-after-group-create" style="display: none"><?php esc_html_e( "Group Created", 'disciple_tools' ) ?>: <span id="new-group-link"></span></p>


    <hr class="reveal-after-group-create" style="display: none">
    <div class="grid-x">
        <a class="button reveal-after-group-create" id="go-to-group" style="display: none">
            <?php esc_html_e( 'Edit new Group', 'disciple_tools' )?>
        </a>
        <button class="button reveal-after-group-create button-cancel clear" data-close type="button" id="create-group-return" style="display: none">
            <?php
            if ( is_singular( "contacts" )){
                echo esc_html_x( 'Back to Contact', 'Link button', 'disciple_tools' );
            } elseif ( is_singular( "groups" )){
                echo esc_html_x( 'Back to Group', 'Link button', 'disciple_tools' );
            } else {
                echo esc_html_x( 'Back', 'Link button', 'disciple_tools' );
            }
            ?>
        </button>
        <button class="close-button" data-close aria-label="Close modal" type="button">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
</div>
