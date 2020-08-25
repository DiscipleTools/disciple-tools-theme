<?php
( function() {
    $contact = DT_Posts::get_post( "contacts", get_the_ID(), true, true );
    $contact_fields = apply_filters( "dt_get_post_type_settings", [], "contacts" )["fields"]
    ?>


<div style="width:100%">
    <div class="contact-quick-buttons" style="float:left">
    <?php
    foreach ( $contact_fields as $field => $val ) {
        if ( strpos( $field, "quick_button" ) === 0 ) {
            $current_value = 0;
            if ( isset( $contact[ $field ] ) ) {
                $current_value = $contact[ $field ];
            } ?>

            <button class="contact-quick-button <?php echo esc_attr( $field ) ?>"
                    onclick="save_quick_action(<?php echo intval( get_the_ID() ); ?>, '<?php echo esc_js( $field ) ?>')">
                <img src="<?php echo esc_url( $val['icon'] ); ?>">
                <span class="contact-quick-button-number"><?php echo esc_html( $current_value ); ?></span>
                <p><?php echo esc_html( $val["name"] ); ?></p>
            </button>
            <?php
        }
    }
    ?>
    </div>
</div>

    <?php
} )();
