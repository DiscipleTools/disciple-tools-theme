
<div class="grid-y">
    <div class="cell grid-x grid-margin-x" id="add-comment-section">
        <div class="auto cell">
            <textarea class="mention" dir="auto" rows="4" id="comment-input" placeholder="<?php esc_html_e( "Write your comment or note here", 'disciple_tools' ) ?>"></textarea>
        </div>
        <div class="shrink cell" id="add-comment-button-container">
            <button id="add-comment-button" class="button loader">
                Submit
            </button>
        </div>
    </div>
    <div class="cell">
        <ul class="tabs" data-tabs id="comment-activity-tabs">
          <li class="tabs-title is-active" data-tab="all"><a href="#all" aria-selected="true"><?php esc_html_e( "All", 'disciple_tools' ) ?></a></li>
          <li class="tabs-title" data-tab="comments"><a href="#comments"><?php esc_html_e( "Comments", 'disciple_tools' ) ?></a></li>
          <li class="tabs-title" data-tab="activity"><a href="#activity"><?php esc_html_e( "Activity", 'disciple_tools' ) ?></a></li>
        </ul>
    </div>

    <div id="comments-wrapper" class="cell tabs-content">

    </div>
</div>
