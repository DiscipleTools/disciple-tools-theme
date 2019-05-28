<?php
declare(strict_types=1);

if ( ! current_user_can( 'create_groups' ) ) {
    wp_die( esc_html( "You do not have permission to publish groups" ), "Permission denied", 403 );
}

get_header();

$group_fields = Disciple_Tools_Groups_Post_Type::instance()->get_custom_fields_settings();
?>

<div id="content">
    <div id="inner-content" class="grid-x grid-margin-x">
        <div class="large-2 medium-12 small-12 cell"></div>

        <div class="large-8 medium-12 small-12 cell">
            <form class="js-create-group bordered-box">
                <label for="title">
                    <?php esc_html_e( "Name of group", "disciple_tools" ); ?>
                </label>
                <input name="title" type="text" placeholder="<?php esc_html_e( "Name", "disciple_tools" ); ?>" required aria-describedby="name-help-text">
                <p class="help-text" id="name-help-text"><?php esc_html_e( "This is required", "disciple_tools" ); ?></p>

                <div class="section-subheader">
                    <?php esc_html_e( 'Group Type', 'disciple_tools' )?>
                </div>
                <select class="select-field" id="group_type" name="group_name">
                    <?php
                    foreach ($group_fields["group_type"]["default"] as $key => $option){ ?>
                            <option value="<?php echo esc_html( $key ) ?>"><?php echo esc_html( $option["label"] ?? "" ); ?></option>
                    <?php } ?>
                </select>

                <div style="text-align: center">
                    <button class="button loader js-create-group-button" type="submit" disabled><?php esc_html_e( "Save and continue editing", "disciple_tools" ); ?></button>
                </div>
            </form>

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
        APIV2.create_post( 'groups', {
            title: $(".js-create-group input[name=title]").val(),
            group_type: $(`.js-create-group #group_type`).val()
        })
        .then(function(data) {
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
