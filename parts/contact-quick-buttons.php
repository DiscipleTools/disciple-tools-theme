<?php
    $contact = Disciple_Tools_Contacts::get_contact( get_the_ID(), true );
    $contact_fields = Disciple_Tools_Contacts::get_contact_fields();
?>


<div class="contact-quick-buttons">
    <?php
    foreach( $contact_fields as $field => $val ) {
        if ( strpos( $field, "quick_button" ) === 0 ) {
            $current_value = 0;
            if ( isset( $contact->fields[$field] ) ) {
                $current_value = $contact->fields[$field];
            } ?>

            <button class="contact-quick-button <?php echo $field ?>"
                    onclick="save_quick_action(<?php echo get_the_ID() ?>, '<?php echo $field ?>')">
                <img src="<?php echo get_template_directory_uri() . "/assets/images/" . $val['icon'] ?>">
                <span class="contact-quick-button-number"><?php echo $current_value ?></span>
                <p><?php echo $val["name"] ?></p>
            </button>
        <?php
        }
    }
    ?>
</div>
