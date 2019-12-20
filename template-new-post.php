<?php
declare(strict_types=1);

$url = dt_get_url_path();
$dt_post_type = explode( "/", $url )[0];

if ( ! current_user_can( 'create_' . $dt_post_type ) ) {
    wp_die( esc_html( "You do not have permission to publish " . $dt_post_type ), "Permission denied", 403 );
}

get_header();

?>

    <div id="content" class="template-new-post">
        <div id="inner-content" class="grid-x grid-margin-x">
            <div class="large-2 medium-12 small-12 cell"></div>

            <div class="large-8 medium-12 small-12 cell">
                <form class="js-create-post bordered-box">
                    <label for="title">
                        <?php esc_html_e( "Name", "disciple_tools" ); ?>
                    </label>
                    <input name="title" type="text" placeholder="<?php echo esc_html_x( "Name", 'input field placeholder', 'disciple_tools' ); ?>" required aria-describedby="name-help-text">
                    <p class="help-text" id="name-help-text"><?php esc_html_e( "This is required", "disciple_tools" ); ?></p>

                    <div style="text-align: center">
                        <button class="button loader js-create-post-button" type="submit" disabled><?php esc_html_e( "Save and continue editing", "disciple_tools" ); ?></button>
                    </div>
                </form>

            </div>

            <div class="large-2 medium-12 small-12 cell"></div>
        </div>
    </div>

    <script>jQuery(function($) {
        $(".js-create-post-button").removeAttr("disabled");
        $(".js-create-post").on("submit", function() {
            $(".js-create-post-button")
                .attr("disabled", true)
                .addClass("loading");
            API.create_post( '<?php echo esc_html( $dt_post_type ) ?>', {
                title: $(".js-create-post input[name=title]").val(),
            }).promise().then(function(data) {
                window.location = data.permalink;
            }).catch(function(error) {
                $(".js-create-post-button").removeClass("loading").addClass("alert");
                $(".js-create-post").append(
                    $("<div>").html(error.responseText)
                );
                console.error(error);
            });
            return false;
        });
    });</script>


<?php
get_footer();
