<?php
declare(strict_types=1);

if ( ! current_user_can( 'create_contacts' ) ) {
    wp_die( __( "You do not have permission to publish contacts" ), "Permission denied", 403 );
}

get_header();

dt_print_breadcrumbs(
    [
        [ home_url( '/' ), __( "Dashboard" ) ],
        [ home_url( '/' ) . "contacts/", __( "Contacts" ) ],
    ],
    __( "New contact" )
);

?>

<div id="content">
    <div id="inner-content" class="grid-x grid-margin-x">
        <div class="large-2 medium-12 small-12 cell"></div>

        <div class="large-8 medium-12 small-12 cell">
            <form class="js-create-contact bordered-box">
                <label>
                    <?php _e( "Name of contact" ); ?>
                    <input name="title" type="text" placeholder="<?php _e( "Name" ); ?>" required aria-describedby="name-help-text">
                </label>
                <p class="help-text" id="name-help-text"><?php _e( "This is required" ); ?></p>

                <label>
                    <?php _e( "Phone number" ); ?>
                    <input name="phone" type="text" type="tel" placeholder="<?php _e( "Phone number" ); ?>">
                </label>

                <?php /* TODO: Create Source field */ ?>

                <label>
                    <?php _e( "Initial comment" ); ?>
                    <textarea name="initial_comment" placeholder="<?php _e( "Initial comment" ); ?>"></textarea>
                </label>

                <div style="text-align: center">
                    <button class="button loader js-create-contact-button" type="submit" disabled><?php _e( "Save and continue editing" ); ?></button>
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
        $.ajax({
            url: wpApiSettings.root + 'dt-hooks/v1/contact/create',
            type: "POST",
            contentType: "application/json; charset=UTF-8",
            dataType: "json",
            data: JSON.stringify({
                title: $(".js-create-contact input[name=title]").val(),
                phone: $(".js-create-contact input[name=phone]").val(),
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
get_footer();
