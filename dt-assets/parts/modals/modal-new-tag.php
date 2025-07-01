<div class="reveal" id="create-tag-modal" data-reveal data-reset-on-close>
    <h3><?php esc_html_e( 'Create Tag', 'disciple_tools' )?></h3>
    <p><?php esc_html_e( 'Create a tag and apply it to this record.', 'disciple_tools' )?></p>

    <form class="js-create-tag">
        <label for="title">
            <?php esc_html_e( 'Tag', 'disciple_tools' ); ?>
        </label>
        <input name="title" id="new-tag" type="text" placeholder="<?php esc_html_e( 'Tag', 'disciple_tools' ); ?>" required aria-describedby="name-help-text">
        <p class="help-text" id="name-help-text"><?php esc_html_e( 'This is required', 'disciple_tools' ); ?></p>
        <button class="button button-cancel clear" data-close aria-label="Close reveal" type="button">
            <?php echo esc_html__( 'Cancel', 'disciple_tools' )?>
        </button>
        <button class="button" data-close type="submit" id="create-tag-return">
            <?php esc_html_e( 'Create and apply tag', 'disciple_tools' ); ?>
        </button>
    </form>
</div>