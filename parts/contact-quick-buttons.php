<?php
(function() {
    $contact = Disciple_Tools_Contacts::get_contact( get_the_ID(), true );
    $contact_fields = Disciple_Tools_Contacts::get_contact_fields();
?>


<div class="contact-quick-buttons">
    <?php
    foreach ( $contact_fields as $field => $val ) {
        if ( strpos( $field, "quick_button" ) === 0 ) {
            $current_value = 0;
            if ( isset( $contact->fields[ $field ] ) ) {
                $current_value = $contact->fields[ $field ];
            } ?>

            <button class="contact-quick-button <?php echo esc_attr( $field, 'disciple_tools' ) ?>"
                    onclick="save_quick_action(<?php echo intval( get_the_ID() ); ?>, '<?php echo esc_js( $field ) ?>')">
                <img src="<?php echo esc_url( get_template_directory_uri() . "/assets/images/" . $val['icon'] ); ?>">
                <span class="contact-quick-button-number"><?php esc_html_e( $current_value, 'disciple_tools'  ); ?></span>
                <p><?php esc_html_e( $val["name"], 'disciple_tools'  ); ?></p>
            </button>
        <?php
        }
    }
    ?>
</div>

<?php
})();
