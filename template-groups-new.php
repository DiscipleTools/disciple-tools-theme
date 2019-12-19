<?php
declare(strict_types=1);

if ( ! current_user_can( 'create_groups' ) ) {
    wp_die( esc_html( "You do not have permission to publish groups" ), "Permission denied", 403 );
}

get_header();

$group_fields = Disciple_Tools_Groups_Post_Type::instance()->get_custom_fields_settings();
?>

<div id="content" class="template-groups-new">
    <div id="inner-content" class="grid-x grid-margin-x">
        <div class="large-2 medium-12 small-12 cell"></div>

        <div class="large-8 medium-12 small-12 cell">
            <form class="js-create-group bordered-box">
                <h3 class="section-header"><?php esc_html_e( "Create new group", "disciple_tools" ); ?><button class="help-button float-right" data-section="new-group-help-text">
                    <img class="help-icon" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/help.svg' ) ?>"/>
                </button></h3>
                <label for="title">
                    <img src="<?php echo esc_url( get_template_directory_uri() ) . '/dt-assets/images/name.svg' ?>">
                    <?php esc_html_e( "Name of group", "disciple_tools" ); ?>
                    <button class="help-button" data-section="group-name-help-text">
                        <img class="help-icon" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/help.svg' ) ?>"/>
                    </button>
                </label>
                <input name="title" type="text" placeholder="<?php echo esc_html_x( "Name", 'input field placeholder', 'disciple_tools' ); ?>" required aria-describedby="name-help-text">
                <p class="help-text" id="name-help-text"><?php esc_html_e( "This is required", "disciple_tools" ); ?></p>

                <div class="section-subheader">
                    <?php esc_html_e( 'Group Type', 'disciple_tools' )?>
                    <button class="help-button" data-section="group-type-help-text">
                        <img class="help-icon" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/help.svg' ) ?>"/>
                    </button>
                </div>
                <select class="select-field" id="group_type" name="group_name">
                    <?php
                    foreach ($group_fields["group_type"]["default"] as $key => $option){ ?>
                            <option value="<?php echo esc_html( $key ) ?>"><?php echo esc_html( $option["label"] ?? "" ); ?></option>
                    <?php } ?>
                </select>

<!--
                <div class="section-subheader">
                    <?php esc_html_e( 'Group Status', 'disciple_tools' )?>
                    <button class="help-button" data-section="group-status-help-text">
                        <img class="help-icon" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/help.svg' ) ?>"/>
                    </button>
                </div>

                <select class="select-field" id="group_status" name="group_name">
                    <?php
                    foreach ($group_fields["group_status"]["default"] as $key => $option){ ?>
                            <option value="<?php echo esc_html( $key ) ?>"><?php echo esc_html( $option["label"] ?? "" ); ?></option>
                    <?php } ?>
                </select>
                <label>
                    <?php esc_html_e( "Initial comment", "disciple_tools" ); ?>
                    <button class="help-button" data-section="initial-comment-help-text">
                        <img class="help-icon" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/help.svg' ) ?>"/>
                    </button>

                    <textarea name="initial_comment" dir="auto" placeholder="<?php echo esc_html_x( "Initial comment", 'input field placeholder', 'disciple_tools' ); ?>"></textarea>
                </label>
-->
                <div style="text-align: center">
                    <a href="/groups/" class="button small" title="<?php esc_html_e( 'Cancel and return to the Groups List page', 'disciple_tools' )?>"><?php echo esc_html_x( 'Cancel', 'button', 'disciple_tools' )?></a>
                    <button class="button loader js-create-group-button dt-green" type="submit" disabled title="<?php esc_html_e( 'Save and continue editing the new group', 'disciple_tools' )?>"><?php esc_html_e( "Save and continue editing", "disciple_tools" ); ?></button>
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
        API.create_post( 'groups', {
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
