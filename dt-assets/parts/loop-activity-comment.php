
<div class="grid-y">
    <div class="cell grid-x grid-margin-x" id="add-comment-section">
        <textarea class="auto cell" dir="auto" rows="4" id="comment-input" placeholder="<?php esc_html_e( "Write comment or note here", 'disciple_tools' ) ?>"></textarea>
        <div class="shrink cell">
            <button id="add-comment-button" class="button loader">
                <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/send.svg' ) ?>"/>
            </button>
        </div>
    </div>
    <div class="section-selector cell">
            <button class="section-button" onclick="display_activity_comment('all')"><?php esc_html_e( 'ALL', 'disciple_tools' )?> | </button>
            <button class="section-button" onclick="display_activity_comment('comments')"><?php esc_html_e( 'COMMENTS', 'disciple_tools' )?> | </button>
            <button class="section-button" onclick="display_activity_comment('activity')"><?php esc_html_e( 'ACTIVITY', 'disciple_tools' )?></button>
    </div>
    <div style="overflow-y:scroll" id="comments-wrapper" class="cell">

    </div>
</div>
