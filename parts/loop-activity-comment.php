<div>
    <textarea dir="auto" rows="4" id="comment-input"></textarea>

    <button id="add-comment-button" class="button loader">Add</button>
</div>
<div class="section-selector">
        <button class="section-button" onclick="display_activity_comment('all')">ALL | </button>
        <button class="section-button" onclick="display_activity_comment('comments')">COMMENTS | </button>
        <button class="section-button" onclick="display_activity_comment('activity')">ACTIVITY</button>
        <br>
<hr>
</div>
<div style="overflow-y:scroll" id="comments-wrapper">

</div>
