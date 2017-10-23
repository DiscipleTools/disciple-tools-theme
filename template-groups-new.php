<?php
declare(strict_types=1);

if ( ! current_user_can( 'create_groups' ) ) {
    wp_die( esc_html__( "You do not have permission to publish groups" ), "Permission denied", 403 );
}

get_header();

dt_print_breadcrumbs(
    [
        [ home_url( '/' ), __( "Dashboard" ) ],
        [ home_url( '/' ) . "groups/", __( "Groups" ) ],
    ],
    __( "New group" )
);

?>

<div id="content">
    <div id="inner-content" class="grid-x grid-margin-x">
        <div class="large-2 medium-12 small-12 cell"></div>

        <div class="large-8 medium-12 small-12 cell">
            <form class="js-create-group bordered-box">
                <label>
                    <?php esc_html_e( "Name of group" ); ?>
                    <input name="title" type="text" placeholder="<?php esc_html_e( "Name" ); ?>" required aria-describedby="name-help-text">
                </label>
                <p class="help-text" id="name-help-text"><?php esc_html_e( "This is required" ); ?></p>

                <div style="text-align: center">
                    <button class="button loader js-create-group-button" type="submit" disabled><?php esc_html_e( "Save and continue editing" ); ?></button>
                </div>
            </div>

        </div>

        <div class="large-2 medium-12 small-12 cell"></div>
    </div>
</div>

<script>jQuery(function($) {
    $(".js-create-group-button").removeAttr("disabled");
    $(".js-create-group").on("submit", function() {
        $(".js-create-group-button")
            .attr("disabled", true)
            .addClass("loading");
        $.ajax({
            url: wpApiSettings.root + 'dt/v1/group/create',
            type: "POST",
            contentType: "application/json; charset=UTF-8",
            dataType: "json",
            data: JSON.stringify({
                title: $(".js-create-group input[name=title]").val(),
            }),
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', wpApiSettings.nonce);
            }
        }).promise().then(function(data) {
            window.location = data.permalink;
        }).catch(function(error) {
            $(".js-create-group-button").removeClass("loading").addClass("alert");
            $(".js-create-group").append(
                $("<div>").html(error.responseText)
            );
            console.error(error);
        });
        return false;
    });
});</script>


<?php
get_footer();
