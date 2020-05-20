<div class="reveal" id="create-group-modal" data-reveal data-reset-on-close>

    <h3><?php esc_html_e( 'Create Group', 'disciple_tools' )?></h3>

    <form class="js-create-group hide-after-group-create">
        <label for="title">
            <?php esc_html_e( "Name", "disciple_tools" ); ?>
        </label>
        <input name="title" type="text" placeholder="<?php echo esc_html_x( "Name", 'input field placeholder', 'disciple_tools' ); ?>" required aria-describedby="name-help-text">

        <div>
            <button class="button loader js-create-group-button" type="submit"><?php echo esc_html__( "Create Group", 'disciple_tools' ); ?></button>
            <button class="button button-cancel clear hide-after-group-create" data-close aria-label="Close reveal" type="button">
                <?php echo esc_html__( 'Cancel', 'disciple_tools' )?>
            </button>
        </div>
    </form>

    <p class="reveal-after-group-create" style="display: none"><?php esc_html_e( "Group Created", 'disciple_tools' ) ?>: <span id="new-group-link"></span></p>


    <hr class="reveal-after-group-create" style="display: none">
    <div class="grid-x">
        <a class="button reveal-after-group-create" id="go-to-group" style="display: none">
            <?php esc_html_e( 'Edit New Group', 'disciple_tools' )?>
        </a>
        <button class="button reveal-after-group-create button-cancel clear" data-close type="button" id="create-group-return" style="display: none">
            <?php
            echo esc_html( sprintf( _x( "Back to %s", "back to record", 'disciple_tools' ), DT_Posts::get_label_for_post_type( "groups", true ) ) );
            ?>
        </button>
        <button class="close-button" data-close aria-label="Close modal" type="button">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
</div>
