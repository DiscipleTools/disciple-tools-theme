<?php
( function() {
    ?>
    <?php
    $group = Disciple_Tools_Groups::get_group( get_the_ID(), true, true );

    $group_fields = Disciple_Tools_Groups_Post_Type::instance()->get_custom_fields_settings();


    function dt_contact_details_status( $id, $verified, $invalid ){
        ?>
    <img id="<?php echo esc_attr( $id . '-verified', 'disciple_tools' ); ?>" class="details-status" style="display: <?php echo esc_attr( $verified, 'disciple_tools' ); ?>" src="<?php echo esc_url( get_template_directory_uri() ) . '/dt-assets/images/verified.svg'; ?>"/>
    <img id="<?php echo esc_attr( $id . '-invalid', 'disciple_tools' ); ?>"  class="details-status" style="display: <?php echo esc_attr( $invalid, 'disciple_tools' ); ?>"  src="<?php echo esc_url( get_template_directory_uri() ) . '/dt-assets/images/broken.svg'; ?>" />
        <?php
    }
    ?>

<section class="bordered-box">
       <div class="section-header">
            <?php esc_html_e( "Group Details", 'disciple_tools' ) ?>
            <button class="help-button" data-section="group-details-help-text">
                <img class="help-icon" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/help.svg' ) ?>"/>
            </button>
            <!-- <button class="section-chevron chevron_down">
                <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/chevron_down.svg' ) ?>"/>
            </button>
            <button class="section-chevron chevron_up">
                <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/chevron_up.svg' ) ?>"/>
            </button> -->
        </div>
    <div style="display: flex;">
        <div class="item-details-header" style="flex-grow:1">
            <i class="fi-torsos-all large" style="padding-bottom: 1.2rem"></i>
            <span class="title"><?php the_title_attribute(); ?></span>
        </div>
        <div>
            <button class="" id="open-edit">
                <i class="fi-pencil"></i>
                <span><?php esc_html_e( 'Edit', 'disciple_tools' )?></span>
            </button>
        </div>
    </div>
    <div class="section-body"><!-- start collapse -->
    <div class="grid-x grid-margin-x" style="margin-top: 20px">
        <div class="cell small-12 medium-4">
            <div class="section-subheader">
                <img src="<?php echo esc_url( get_template_directory_uri() ) . '/dt-assets/images/status.svg' ?>">
                <?php esc_html_e( "Status", 'disciple_tools' ) ?>
                <button class="help-button" data-section="group-status-help-text">
                    <img class="help-icon" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/help.svg' ) ?>"/>
                </button>
            </div>
            <?php
            $active_color = "#4CAF50";
            $current_key = $group["group_status"]["key"] ?? "";
            if ( isset( $group_fields["group_status"]["default"][ $current_key ]["color"] )){
                $active_color = $group_fields["group_status"]["default"][ $current_key ]["color"];
            }
            ?>
            <select id="group_status" class="status select-field color-select" style="background-color: <?php echo esc_html( $active_color ) ?>">
                <?php foreach ( $group_fields["group_status"]["default"] as $status_key => $option ) { ?>
                    <option value="<?php echo esc_attr( $status_key, 'disciple_tools' ); ?>"
                        <?php echo esc_attr( $status_key === $group['group_status']['key'] ? 'selected' : '', 'disciple_tools' ); ?>>
                        <?php echo esc_html( $option["label"] ?? "" ) ?>
                    </option>
                <?php } ?>
            </select>
        </div>
        <div class="cell small-12 medium-4">
            <div class="section-subheader">
                <img src="<?php echo esc_url( get_template_directory_uri() ) . '/dt-assets/images/assigned-to.svg' ?>">
                <?php esc_html_e( 'Assigned to', 'disciple_tools' )?>
                <button class="help-button" data-section="assigned-to-help-text">
                    <img class="help-icon" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/help.svg' ) ?>"/>
                </button>
            </div>

            <div class="assigned_to details">
                <var id="assigned_to-result-container" class="result-container assigned_to-result-container"></var>
                <div id="assigned_to_t" name="form-assigned_to">
                    <div class="typeahead__container">
                        <div class="typeahead__field">
                            <span class="typeahead__query">
                                <input class="js-typeahead-assigned_to input-height"
                                       name="assigned_to[query]" placeholder="<?php echo esc_html_x( "Search Users", 'input field placeholder', 'disciple_tools' ) ?>"
                                       autocomplete="off">
                            </span>
                            <span class="typeahead__button">
                                <button type="button" class="search_assigned_to typeahead__image_button input-height" data-id="assigned_to_t">
                                    <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/chevron_down.svg' ) ?>"/>
                                </button>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="cell small-12 medium-4">
            <div class="section-subheader">
            <img src="<?php echo esc_url( get_template_directory_uri() ) . '/dt-assets/images/coach.svg' ?>">
            <?php esc_html_e( "Group Coach / Church Planter", 'disciple_tools' ) ?>
            <button class="help-button" data-section="coaches-help-text">
                <img class="help-icon" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/help.svg' ) ?>"/>
            </button>
            </div>
            <div class="coaches">
                <var id="coaches-result-container" class="result-container"></var>
                <div id="coaches_t" name="form-coaches" class="scrollable-typeahead">
                    <div class="typeahead__container">
                        <div class="typeahead__field">
                                            <span class="typeahead__query">
                                                <input class="js-typeahead-coaches"
                                                       name="coaches[query]" placeholder="<?php echo esc_html_x( "Search Users and Contacts", 'input field placeholder', 'disciple_tools' ) ?>"
                                                       autocomplete="off">
                                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <hr />

    <div class="display-fields grid-x grid-margin-x">
        <!-- Locations -->
        <div class="xlarge-4 large-6 medium-6 small-12 cell">
            <div class="section-subheader">
                <img src="<?php echo esc_url( get_template_directory_uri() ) . '/dt-assets/images/location.svg' ?>">
                <?php esc_html_e( 'Locations', 'disciple_tools' ) ?>
            </div>
            <ul class="location_grid-list"></ul>
        </div>
        <div class="xlarge-4 large-6 medium-6 small-12 cell">
            <div class="section-subheader">
                <img src="<?php echo esc_url( get_template_directory_uri() ) . "/dt-assets/images/people-group.svg" ?>">
                <?php esc_html_e( 'People Groups', 'disciple_tools' )?>
            </div>
            <ul class="people_groups-list details-list">
                <?php
                foreach ($group["people_groups"] ?? [] as $value){
                    ?>
                    <li class="<?php echo esc_html( $value["ID"] )?>">
                        <a href="<?php echo esc_url( $value["permalink"] ) ?>"><?php echo esc_html( $value["post_title"] ) ?></a>
                    </li>
                <?php }
                if (sizeof( $group["people_groups"] ) === 0){
                    ?> <li id="no-people_groups"><?php esc_html_e( "No people group set", 'disciple_tools' ) ?></li><?php
                }
                ?>
            </ul>
        </div>


        <div class="xlarge-4 large-6 medium-6 small-12 cell">

            <div class="section-subheader">
                <img src="<?php echo esc_url( get_template_directory_uri() ) . '/dt-assets/images/address.svg' ?>">
                <?php esc_html_e( 'Address', 'disciple_tools' )?>
            </div>
            <ul class="address details-list">
                <?php
                if (sizeof( $group["contact_address"] ?? [] ) === 0 ){
                    ?> <li id="no-address"><?php esc_html_e( 'No address set', 'disciple_tools' )?></li> <?php
                }
                foreach ($group["contact_address"] ?? [] as $value){
                    $verified = isset( $value["verified"] ) && $value["verified"] === true ? "inline" :"none";
                    $invalid = isset( $value["invalid"] ) && $value["invalid"] === true ? "inline" :"none";
                    ?>
                    <li class="<?php echo esc_html( $value["key"] ) ?> address-row">
                        <div class="address-text" dir="auto"><?php echo esc_html( $value["value"] );?></div>
                        <?php dt_contact_details_status( $value["key"], $verified, $invalid ) ?>
                    </li>
                <?php } ?>
            </ul>
        </div>

        <div class="xlarge-4 large-6 medium-6 small-12 cell">
            <div class="section-subheader"><img src="<?php echo esc_url( get_template_directory_uri() ) . '/dt-assets/images/date-start.svg' ?>"> <?php esc_html_e( 'Start Date', 'disciple_tools' )?></div>
            <ul class="date-list start_date details-list"><?php
            if ( isset( $group["start_date"] ) ) {
                echo esc_html( $group["start_date"]["formatted"] );
            } else {
                esc_html_e( "No start date", 'disciple_tools' );
            } ?>
            </ul>
        </div>
        <div class="xlarge-4 large-6 medium-6 small-12 cell">
            <div class="section-subheader">
            <img src="<?php echo esc_url( get_template_directory_uri() ) . '/dt-assets/images/date-success.svg' ?>"> <?php esc_html_e( 'Church Start Date', 'disciple_tools' ) ?>
            </div>
            <ul class="date-list church_start_date details-list"><?php
            if ( isset( $group["church_start_date"] ) ) {
                echo esc_html( $group["church_start_date"]["formatted"] );
            } else {
                esc_html_e( "No church start date", 'disciple_tools' );
            } ?>
            </ul>
        </div>
        <div class="xlarge-4 large-6 medium-6 small-12 cell">
            <div class="section-subheader">
            <img src="<?php echo esc_url( get_template_directory_uri() ) . '/dt-assets/images/date-end.svg' ?>"> <?php esc_html_e( 'End Date', 'disciple_tools' ) ?>
            </div>
            <ul class="date-list end_date details-list"><?php
            if ( isset( $group["end_date"] ) ) {
                echo esc_html( $group["end_date"]["formatted"] );
            } else {
                esc_html_e( "No end date", 'disciple_tools' );
            } ?>
            </ul>
        </div>

    </div><!-- end collapse --></div>


</section> <!-- end article -->

<div class="reveal" id="group-details-edit-modal" data-reveal data-close-on-click="false">
    <h1><?php esc_html_e( "Edit Group", 'disciple_tools' ) ?></h1>
    <div class="display-fields">
        <div class="grid-x">
            <div class="cell section-subheader">
              <img src="<?php echo esc_url( get_template_directory_uri() ) . '/dt-assets/images/name.svg' ?>">
              <?php esc_html_e( 'Name', 'disciple_tools' ) ?>
            </div>
            <input type="text" id="title" class="edit-text-input" value="<?php the_title_attribute(); ?>">
        </div>

        <!-- Address -->
        <div class="grix-x">
            <div class="section-subheader cell">
                <img src="<?php echo esc_url( get_template_directory_uri() ) . '/dt-assets/images/address.svg' ?>">
                <?php esc_html_e( 'Address', 'disciple_tools' )?>
                <button id="add-new-address">
                    <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/small-add.svg' ) ?>"/>
                </button>
            </div>
            <ul id="edit-contact_address" class="cell">
            </ul>
        </div>

        <div class="grix-x">
            <div class="section-subheader cell">
                <img src="<?php echo esc_url( get_template_directory_uri() ) . '/dt-assets/images/location.svg' ?>">
                <?php esc_html_e( 'Locations', 'disciple_tools' ) ?>
            </div>
            <div class="location_grid">
                <var id="location_grid-result-container" class="result-container"></var>
                <div id="location_grid_t" name="form-location_grid" class="scrollable-typeahead typeahead-margin-when-active">
                    <div class="typeahead__container">
                        <div class="typeahead__field">
                            <span class="typeahead__query">
                                <input class="js-typeahead-location_grid"
                                       name="location_grid[query]" placeholder="<?php echo esc_html_x( "Search Locations", 'input field placeholder', 'disciple_tools' ) ?>"
                                       autocomplete="off">
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dates -->
        <div class="grix-x">
            <div class="section-subheader cell">
              <img src="<?php echo esc_url( get_template_directory_uri() ) . '/dt-assets/images/date-start.svg' ?>">
              <?php esc_html_e( 'Start Date', 'disciple_tools' )?>
            </div>
            <div class="start_date"><input type="text" class="date-picker" id="start_date" autocomplete="off"></div>
        </div>

        <div class="grix-x">
            <div class="section-subheader cell">
              <img src="<?php echo esc_url( get_template_directory_uri() ) . '/dt-assets/images/date-success.svg' ?>">
              <?php esc_html_e( 'Church Start Date', 'disciple_tools' )?>
            </div>
            <div class="church_start_date"><input type="text" class="date-picker" id="church_start_date" autocomplete="off"></div>
        </div>

        <div class="grix-x">
            <div class="section-subheader cell">
              <img src="<?php echo esc_url( get_template_directory_uri() ) . '/dt-assets/images/date-end.svg' ?>">
              <?php esc_html_e( 'End Date', 'disciple_tools' )?>
            </div>
            <div class="end_date"><input type="text" class="date-picker" id="end_date" autocomplete="off"></div>
        </div>

        <!-- People Groups -->
        <div class="grix-x">
            <div class="section-subheader cell">
                <img src="<?php echo esc_url( get_template_directory_uri() ) . "/dt-assets/images/people-group.svg" ?>">
                <?php esc_html_e( 'People Groups', 'disciple_tools' )?>
            </div>
            <div class="people_groups">
                <var id="people_groups-result-container" class="result-container"></var>
                <div id="people_groups_t" name="form-people_groups" class="scrollable-typeahead">
                    <div class="typeahead__container">
                        <div class="typeahead__field">
                            <span class="typeahead__query">
                                <input class="js-typeahead-people_groups"
                                       name="people_groups[query]" placeholder="<?php echo esc_html_x( "Search People Groups", 'input field placeholder', 'disciple_tools' ) ?>"
                                       autocomplete="off">
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <div>
        <button class="button button-cancel clear" data-close aria-label="Close reveal" type="button">
            <?php echo esc_html_x( 'Cancel', 'button', 'disciple_tools' )?>
        </button>
        <button class="button loader" type="button" id="save-edit-details">
            <?php esc_html_e( 'Save', 'disciple_tools' )?>
        </button>
        <button class="close-button" data-close aria-label="Close modal" type="button">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
</div>

    <?php
} )();
