
<div class="reveal" id="save-filter-modal" data-reveal>
    <h2><?php esc_html_e( "Save Filter", 'disciple_tools' ) ?></h2>
    <label><?php esc_html_e( "What do you want to call this filter?", 'disciple_tools' ) ?>
        <input id="filter-name">
    </label>
    <div style="margin-top:20px">
        <button class="button button-cancel clear" data-close aria-label="Close reveal" type="button">
            <?php esc_html_e( 'Cancel', 'disciple_tools' )?>
        </button>
        <button class="button loader confirm-filter-save" type="button" id="confirm-filter-save" data-close >
            <?php esc_html_e( 'Save Filter', 'disciple_tools' )?>
        </button>
        <button class="close-button" data-close aria-label="Close modal" type="button">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
</div>


<div class="reveal" id="delete-filter-modal" data-reveal>
    <h2><?php esc_html_e( "Delete Filter", 'disciple_tools' ) ?></h2>

    <p class="delete-filter-name"></p>

    <div style="margin-top:20px">
        <button class="button button-cancel clear" data-close aria-label="Close reveal" type="button">
            <?php esc_html_e( 'Cancel', 'disciple_tools' )?>
        </button>
        <button class="button loader confirm-filter-delete" type="button" id="confirm-filter-delete" data-close >
            <?php esc_html_e( 'Delete Filter', 'disciple_tools' )?>
        </button>
        <button class="close-button" data-close aria-label="Close modal" type="button">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
</div>
