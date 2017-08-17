<?php $contact = Disciple_Tools_Contacts::get_contact( get_the_ID(), true ); ?>
<?php $contact_fields = Disciple_Tools_Contacts::get_contact_fields(); ?>

<section class="comment-activity-section bordered-box" id="comment-activity-section">
    <div>
        <div class="contact-quick-buttons">
            <?php foreach( $contact_fields as $field => $val ){
                if ( strpos( $field, "quick_button" ) === 0){
                    $current_value = 0;
                    if ( isset( $contact->fields[$field] ) ){
                        $current_value = $contact->fields[$field];
                    }?>

            <button class="contact-quick-button <?php echo $field ?>"
                    onclick="save_quick_action(<?php echo get_the_ID() ?>, '<?php echo $field?>')">
                <img src="<?php echo get_template_directory_uri() . "/assets/images/" . $val['icon'] ?>">
                <span class="contact-quick-button-number"><?php echo $current_value ?></span>
                <p><?php echo $val["name"] ?></p>
            </button>
            <?php }}
            ?>

        </div>
    </div>
    <div>
        <textarea id="comment-input"></textarea>

        <button id="add-comment-button" class="button" onclick="post_comment( <?php echo get_the_ID()?>)">Add</button>
    </div>
    <div style="text-align: center">
            <button onclick="display_activity_comment('all')">ALL | </button>
            <button onclick="display_activity_comment('comments')">COMMENTS | </button>
            <button onclick="display_activity_comment('activity')">ACTIVITY</button>
            <br>
    <hr>
    </div>
    <div style="overflow-y:scroll" id="comments-wrapper">

    </div>
</section>
