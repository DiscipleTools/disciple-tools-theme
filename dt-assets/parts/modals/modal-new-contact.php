<div class="reveal" id="create-record-modal" data-reveal data-reset-on-close>

    <h3><?php esc_html_e( 'Create Record', 'disciple_tools' )?></h3>

    <form class="js-create-record hide-after-record-create">
        <label for="title">
            <?php esc_html_e( "Name", "disciple_tools" ); ?>
        </label>
        <input name="title" type="text" placeholder="<?php echo esc_html__( "Name", 'disciple_tools' ); ?>" required aria-describedby="name-help-text">

        <div>
            <button class="button loader js-create-record-button" type="submit"><?php echo esc_html__( "Create Record", 'disciple_tools' ); ?></button>
            <button class="button button-cancel clear hide-after-record-create" data-close aria-label="Close reveal" type="button">
                <?php echo esc_html__( 'Cancel', 'disciple_tools' )?>
            </button>
        </div>
        <p style="color: red" class="error-text"></p>
    </form>

    <p class="reveal-after-record-create" style="display: none"><?php esc_html_e( "Record Created", 'disciple_tools' ) ?>: <span id="new-record-link"></span></p>


    <hr class="reveal-after-group-create" style="display: none">
    <div class="grid-x">
        <a class="button reveal-after-record-create" id="go-to-record" style="display: none">
            <?php esc_html_e( 'Edit New Record', 'disciple_tools' )?>
        </a>
        <button class="button reveal-after-record-create button-cancel clear" data-close type="button" id="create-record-return" style="display: none">
            <?php
                echo esc_html( sprintf( _x( "Back to %s", "back to record", 'disciple_tools' ), DT_Posts::get_label_for_post_type( get_post_type( get_the_ID() ), true ) ) );
            ?>
        </button>
        <button class="close-button" data-close aria-label="Close modal" type="button">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
</div>
