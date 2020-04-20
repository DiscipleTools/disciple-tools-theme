<?php
( function() {
    ?>
    <?php
    $group = Disciple_Tools_Groups::get_group( get_the_ID(), true, true );

    $group_fields = Disciple_Tools_Groups_Post_Type::instance()->get_custom_fields_settings();


    function dt_contact_details_status( $id, $verified, $invalid ){
        ?>
        <img id="<?php echo esc_attr( $id . '-verified' ); ?>" class="details-status" style="display: <?php echo esc_attr( $verified ); ?>" src="<?php echo esc_url( get_template_directory_uri() ) . '/dt-assets/images/verified.svg'; ?>"/>
        <img id="<?php echo esc_attr( $id . '-invalid' ); ?>"  class="details-status" style="display: <?php echo esc_attr( $invalid ); ?>"  src="<?php echo esc_url( get_template_directory_uri() ) . '/dt-assets/images/broken.svg'; ?>" />
        <?php
    }
    ?>

    <section class="bordered-box">
    <div style="display: flex;">
        <div class="item-details-header" style="flex-grow:1">
            <i class="fi-torsos-all large" style="padding-bottom: 1.2rem"></i>
            <span class="title"><?php the_title_attribute(); ?></span>
            <button class="help-button" data-section="group-details-help-text">
                <img class="help-icon" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/help.svg' ) ?>"/>
            </button>
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
                <?php echo esc_html( $group_fields["group_status"]["name"] )?>
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
                    <option value="<?php echo esc_attr( $status_key ); ?>"
                        <?php echo esc_attr( $status_key === $group['group_status']['key'] ? 'selected' : '' ); ?>>
                        <?php echo esc_html( $option["label"] ?? "" ) ?>
                    </option>
                <?php } ?>
            </select>
        </div>
        <div class="cell small-12 medium-4">
            <div class="section-subheader">
                <img src="<?php echo esc_url( get_template_directory_uri() ) . '/dt-assets/images/assigned-to.svg' ?>">
                <?php echo esc_html( $group_fields["assigned_to"]["name"] )?>
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
            <?php echo esc_html( $group_fields["coaches"]["name"] )?>
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
                                       name="coaches[query]" placeholder="<?php echo esc_html_x( "Search multipliers and contacts", 'input field placeholder', 'disciple_tools' ) ?>"
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

        <!-- people groups -->
        <div class="xlarge-4 large-6 medium-6 small-12 cell">
            <div class="section-subheader">
                <img src="<?php echo esc_url( get_template_directory_uri() ) . "/dt-assets/images/people-group.svg" ?>">
                <?php echo esc_html( $group_fields["people_groups"]["name"] )?>
            </div>
            <ul class="people_groups-list details-list"></ul>
        </div>

        <!-- Mapbox enabled locations -->
        <?php if ( DT_Mapbox_API::get_key() ) : ?>

            <!-- Locations -->
            <div class="xlarge-8 large-12 medium-12 small-12 cell">
                <div class="section-subheader">
                    <img src="<?php echo esc_url( get_template_directory_uri() ) . '/dt-assets/images/location.svg' ?>">
                    <?php echo esc_html( $group_fields["location_grid"]["name"] )?>
                </div>
                <ul class="location_grid-list"></ul>
                <ul class="address details-list"></ul>
                <style>#no-address{display:none;}</style>
            </div>

        <?php else : ?>

            <!-- Locations -->
            <div class="xlarge-4 large-6 medium-6 small-12 cell">
                <div class="section-subheader">
                    <img src="<?php echo esc_url( get_template_directory_uri() ) . '/dt-assets/images/location.svg' ?>">
                    <?php echo esc_html( $group_fields["location_grid"]["name"] )?>
                </div>
                <ul class="location_grid-list"></ul>
            </div>

            <!-- Address -->
            <div class="xlarge-4 large-6 medium-6 small-12 cell">
                <div class="section-subheader">
                    <img src="<?php echo esc_url( get_template_directory_uri() ) . '/dt-assets/images/address.svg' ?>">
                    <?php echo esc_html( Disciple_Tools_Groups_Post_Type::instance()->get_channels_list()["address"]["label"] )?>
                </div>
                <ul class="address details-list"></ul>
            </div>

        <?php endif; ?>

        <!-- start date -->
        <div class="xlarge-4 large-6 medium-6 small-12 cell">
            <div class="section-subheader"><img src="<?php echo esc_url( get_template_directory_uri() ) . '/dt-assets/images/date-start.svg' ?>"> <?php echo esc_html( $group_fields["start_date"]["name"] )?></div>
            <ul class="date-list start_date details-list"></ul>
        </div>

        <!-- church start date-->
        <div class="xlarge-4 large-6 medium-6 small-12 cell">
            <div class="section-subheader">
            <img src="<?php echo esc_url( get_template_directory_uri() ) . '/dt-assets/images/date-success.svg' ?>"> <?php echo esc_html( $group_fields["church_start_date"]["name"] )?>
            </div>
            <ul class="date-list church_start_date details-list">
            <?php
            if ( isset( $group["church_start_date"] ) ) {
                echo esc_html( strftime( '%x', $group["church_start_date"]["timestamp"] ) );
            } else {
                esc_html_e( "No church start date", 'disciple_tools' );
            } ?>
            </ul>
        </div>

        <!-- end date -->
        <div class="xlarge-4 large-6 medium-6 small-12 cell">
            <div class="section-subheader">
            <img src="<?php echo esc_url( get_template_directory_uri() ) . '/dt-assets/images/date-end.svg' ?>"> <?php echo esc_html( $group_fields["end_date"]["name"] )?>
            </div>
            <ul class="date-list end_date details-list">
            <?php
            if ( isset( $group["end_date"] ) ) {
                echo esc_html( strftime( '%x', $group["end_date"]["timestamp"] ) );
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

        <?php if ( DT_Mapbox_API::get_key() ) : ?>

            <div class="grid-x">
                <div class="section-subheader cell" style="padding-bottom:10px;">
                    <img src="<?php echo esc_url( get_template_directory_uri() ) . '/dt-assets/images/location.svg' ?>" alt="location">
                    <?php echo esc_html( $group_fields["location_grid"]["name"] )?>
                    <button id="new-mapbox-search">
                        <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/small-add.svg' ) ?>" alt="add"/>
                    </button>
                </div>
                <div id="mapbox-wrapper" class="cell"></div>
            </div>

        <?php else : ?>

            <div class="grid-x">
                <div class="section-subheader cell">
                    <img src="<?php echo esc_url( get_template_directory_uri() ) . '/dt-assets/images/location.svg' ?>">
                    <?php echo esc_html( $group_fields["location_grid"]["name"] )?>
                </div>
                <div class="location_grid full-width">
                    <var id="location_grid-result-container" class="result-container"></var>
                    <div id="location_grid_t" name="form-location_grid" class="scrollable-typeahead typeahead-margin-when-active">
                        <div class="typeahead__container">
                            <div class="typeahead__field">
                                <span class="typeahead__query">
                                    <input class="js-typeahead-location_grid input-height"
                                           name="location_grid[query]" placeholder="<?php echo esc_html_x( "Search Locations", 'input field placeholder', 'disciple_tools' ) ?>"
                                           autocomplete="off">
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        <?php endif; ?>

        <!-- Address -->
        <div class="grid-x">
            <div class="section-subheader cell">
                <img src="<?php echo esc_url( get_template_directory_uri() ) . '/dt-assets/images/address.svg' ?>">
                <?php echo esc_html( Disciple_Tools_Groups_Post_Type::instance()->get_channels_list()["address"]["label"] )?>
                <button id="add-new-address">
                    <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/small-add.svg' ) ?>"/>
                </button>
            </div>
            <ul id="edit-contact_address" class="cell">
            </ul>
        </div>

        <!-- People Groups -->
        <div class="grid-x">
            <div class="section-subheader cell">
                <img src="<?php echo esc_url( get_template_directory_uri() ) . "/dt-assets/images/people-group.svg" ?>">
                <?php echo esc_html( $group_fields["people_groups"]["name"] )?>
            </div>
            <div class="people_groups full-width">
                <var id="people_groups-result-container" class="result-container"></var>
                <div id="people_groups_t" name="form-people_groups" class="scrollable-typeahead">
                    <div class="typeahead__container">
                        <div class="typeahead__field">
                            <span class="typeahead__query">
                                <input class="js-typeahead-people_groups"
                                       name="people_groups[query]"
                                       placeholder="<?php echo esc_html( sprintf( _x( "Search %s", "Search 'something'", 'disciple_tools' ), $group_fields["people_groups"]['name'] ) )?>"
                                       autocomplete="off">
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dates -->
        <div class="grid-x">
            <div class="section-subheader cell">
                <img src="<?php echo esc_url( get_template_directory_uri() ).'/dt-assets/images/date-start.svg'; ?>">
                <?php echo esc_html( $group_fields["start_date"]["name"] ); ?>
            </div>
            <div class="start_date input-group">
                <input id="start_date" class="input-group-field dt_date_picker date-picker" type="text" autocomplete="off" data-date-format='yy-mm-dd' value="<?php echo esc_html( $group["start_date"]["timestamp"] ?? '' ); ?>">

                <div class="input-group-button">
                    <button id="start_date_clear" class="button alert clear-date-button" data-inputid="start_date" title="Delete Date">x</button>
                </div>
            </div>
        </div>

        <div class="grid-x">
            <div class="section-subheader cell">
                <img src="<?php echo esc_url( get_template_directory_uri() ).'/dt-assets/images/date-success.svg'; ?>">
                <?php echo esc_html( $group_fields["church_start_date"]["name"] ); ?>
            </div>

            <div class="church_start_date input-group">
                <input id="church_start_date" class="input-group-field dt_date_picker date-picker" type="text" autocomplete="off" data-date-format='yy-mm-dd' value="<?php echo esc_html( $group["church_start_date"]["timestamp"] ?? '' ); ?>">

                <div class="input-group-button">
                    <button id="church_start_date_clear" class="button alert clear-date-button" data-inputid="church_start_date" title="Delete Date">x</button>
                </div>
            </div>
        </div>

        <div class="grid-x">
            <div class="section-subheader cell">
                <img src="<?php echo esc_url( get_template_directory_uri() ).'/dt-assets/images/date-end.svg'; ?>">
                <?php echo esc_html( $group_fields["end_date"]["name"] ); ?>
            </div>
            <div class="end_date input-group">
                <input id="end_date" class="input-group-field dt_date_picker date-picker" type="text" autocomplete="off" data-date-format='yy-mm-dd' value="<?php echo esc_html( $group["end_date"]["timestamp"] ?? '' ); ?>">

                <div class="input-group-button">
                    <button id="end_date_clear" class="button alert clear-date-button"
                    data-inputid="end_date" title="Delete Date">x</button>
                </div>
            </div>
        </div>



    </div>
    <div>
        <button class="button button-cancel clear" data-close aria-label="Close reveal" type="button">
            <?php echo esc_html__( 'Cancel', 'disciple_tools' )?>
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
