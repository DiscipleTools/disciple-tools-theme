<div>
    <textarea dir="auto" rows="4" id="comment-input"></textarea>

    <button id="add-comment-button" class="button loader"><?php esc_html_e( 'Add', 'disciple_tools' )?></button>
</div>
<div class="section-selector">
        <button class="section-button" onclick="display_activity_comment('all')"><?php esc_html_e( 'ALL', 'disciple_tools' )?> | </button>
        <button class="section-button" onclick="display_activity_comment('comments')"><?php esc_html_e( 'COMMENTS', 'disciple_tools' )?> | </button>
        <button class="section-button" onclick="display_activity_comment('activity')"><?php esc_html_e( 'ACTIVITY', 'disciple_tools' )?></button>
        <br>
<hr>
</div>
<div style="overflow-y:scroll" id="comments-wrapper">

</div>
