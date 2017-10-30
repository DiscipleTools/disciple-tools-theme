<?php
declare(strict_types=1);

if ( ! current_user_can( 'create_contacts' ) ) {
    wp_die( esc_html__( "You do not have permission to publish contacts" ), "Permission denied", 403 );
}

get_header();

dt_print_breadcrumbs(
    [
        [ home_url( '/' ), __( "Dashboard" ) ],
        [ home_url( '/' ) . "contacts/", __( "Contacts" ) ],
    ],
    __( "New contact" )
);

(function() {

?>

<div id="content">
    <div id="inner-content" class="grid-x grid-margin-x">
        <div class="large-2 medium-12 small-12 cell"></div>

        <div class="large-8 medium-12 small-12 cell">
            <form class="js-create-contact bordered-box">
                <label>
                    <?php esc_html_e( "Name of contact" ); ?>
                    <input name="title" type="text" placeholder="<?php esc_html_e( "Name" ); ?>" required aria-describedby="name-help-text">
                </label>
                <p class="help-text" id="name-help-text"><?php esc_html_e( "This is required" ); ?></p>

                <label>
                    <?php esc_html_e( "Phone number" ); ?>
                    <input name="phone" type="text" type="tel" placeholder="<?php esc_html_e( "Phone number" ); ?>">
                </label>

                <label>
                    <?php esc_html_e( "Source" ); ?>
                    <select name="sources" required aria-describedby="source-help-text">
                        <?php foreach ( dt_get_option( 'dt_site_custom_lists' )['sources'] as $source_key => $source ): ?>
                            <option value="<?php echo esc_attr( $source_key ); ?>">
                                <?php echo esc_html( $source['label'] ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <p class="help-text" id="source-help-text"><?php esc_html_e( "This is required" ); ?></p>

                <label>
                    <?php esc_html_e( "Location" ); ?>
                    <select name="location">
                        <option value=""><?php esc_html_e( "(Not set)" ); ?></option>
                        <?php foreach ( Disciple_Tools_Locations::get_locations() as $location_post ): ?>
                            <option value="<?php echo intval( $location_post->ID ); ?>"><?php echo esc_html( $location_post->post_title ); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label>
                    <?php esc_html_e( "Initial comment" ); ?>
                    <textarea name="initial_comment" placeholder="<?php esc_html_e( "Initial comment" ); ?>"></textarea>
                </label>

                <div style="text-align: center">
                    <button class="button loader js-create-contact-button" type="submit" disabled><?php esc_html_e( "Save and continue editing" ); ?></button>
                </div>
            </div>

        </div>

        <div class="large-2 medium-12 small-12 cell"></div>
    </div>
</div>

<script>jQuery(function($) {
    $(".js-create-contact-button").removeAttr("disabled");
    $(".js-create-contact").on("submit", function() {
        $(".js-create-contact-button")
            .attr("disabled", true)
            .addClass("loading");
        var location_id = $(".js-create-contact select[name=location]").val();
        location_id = location_id ? parseInt(location_id) : undefined;
        $.ajax({
            url: wpApiSettings.root + 'dt/v1/contact/create',
            type: "POST",
            contentType: "application/json; charset=UTF-8",
            dataType: "json",
            data: JSON.stringify({
                title: $(".js-create-contact input[name=title]").val(),
                phone: $(".js-create-contact input[name=phone]").val(),
                sources: $(".js-create-contact select[name=sources]").val(),
                location_id: location_id,
                initial_comment: $(".js-create-contact textarea[name=initial_comment]").val(),
            }),
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', wpApiSettings.nonce);
            }
        }).promise().then(function(data) {
            window.location = data.permalink;
        }).catch(function(error) {
            $(".js-create-contact-button").removeClass("loading").addClass("alert");
            $(".js-create-contact").append(
                $("<div>").html(error.responseText)
            );
            console.error(error);
        });
        return false;
    });
});</script>


<?php

})();

get_footer();
