
<div class="reveal" id="revert-modal" data-reveal>

    <p class="lead"><?php esc_html_e( 'Revert Activity', 'disciple_tools' )?></p>
    <h6><?php esc_html_e( "Field", 'disciple_tools' ) ?>:
        <span class="revert-field"></span>
    </h6>
    <label><?php esc_html_e( "Current Value", 'disciple_tools' ) ?>:</label>
    <p class="revert-current-value"></p>
    <label><?php esc_html_e( "Old Value", 'disciple_tools' ) ?>:</label>
    <p class="revert-old-value"></p>

    <div class="grid-x">
        <button class="button button-cancel clear" data-close aria-label="Close reveal" type="button">
            <?php esc_html_e( 'Close', 'disciple_tools' )?>
        </button>
        <button class="button" aria-label="confirm" type="button" id="confirm-revert">
            <?php esc_html_e( 'Revert to old value?', 'disciple_tools' )?>
        </button>
        <button class="close-button" data-close aria-label="Close modal" type="button">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
</div>
