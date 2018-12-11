<?php
( function() {
    ?>
    <?php
    $group = Disciple_Tools_Groups::get_group( get_the_ID(), true );

    $group_fields = Disciple_Tools_Groups_Post_Type::instance()->get_custom_fields_settings();


    function dt_contact_details_status( $id, $verified, $invalid ){
        ?>
    <img id="<?php echo esc_attr( $id . '-verified', 'disciple_tools' ); ?>" class="details-status" style="display: <?php echo esc_attr( $verified, 'disciple_tools' ); ?>" src="<?php echo esc_url( get_template_directory_uri() ) . '/dt-assets/images/verified.svg'; ?>"/>
    <img id="<?php echo esc_attr( $id . '-invalid', 'disciple_tools' ); ?>"  class="details-status" style="display: <?php echo esc_attr( $invalid, 'disciple_tools' ); ?>"  src="<?php echo esc_url( get_template_directory_uri() ) . '/dt-assets/images/broken.svg'; ?>" />
        <?php
    }
    ?>

<section class="bordered-box">

    <div class="item-details-header-row">
        <i class="fi-torsos-all large"></i>
        <span class="item-details-header details-list title" ><?php the_title_attribute(); ?></span>
        <select id="group_status" class="status select-field" style="width:fit-content;">
            <?php foreach ( $group_fields["group_status"]["default"] as $status_key => $option ) { ?>
                <option value="<?php echo esc_attr( $status_key, 'disciple_tools' ); ?>"
                <?php echo esc_attr( $status_key === $group['group_status']['key'] ? 'selected' : '', 'disciple_tools' ); ?>>
                    <?php echo esc_html( $option["label"] ?? "" ) ?>
                </option>
            <?php } ?>
        </select>
        <button class="help-button" data-section="group-status-help-text">
            <img class="help-icon" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/help.svg' ) ?>"/>
        </button>

        <button class="float-right" id="open-edit">
            <i class="fi-pencil"></i>
            <span><?php esc_html_e( 'Edit', 'disciple_tools' )?></span>
        </button>
    </div>

    <div class="display-fields grid-x grid-margin-x">
        <div class="medium-4 cell">

            <div class="section-subheader"><?php esc_html_e( 'Locations', 'disciple_tools' )?></div>
            <ul class="locations-list details-list">
                <?php
                foreach ($group["locations"] ?? [] as $value){
                    ?>
                    <li class="<?php echo intval( $value->ID ); ?>">
                        <?php echo esc_html( $value->post_title ); ?>
                    </li>
                    <?php
                }
                if (sizeof( $group["locations"] ) === 0){
                    ?> <li id="no-locations"><?php esc_html_e( "No location set", 'disciple_tools' ) ?></li><?php
                }
                ?>
            </ul>

            <div class="section-subheader"><?php esc_html_e( 'People Groups', 'disciple_tools' )?></div>
            <ul class="people_groups-list details-list">
                <?php
                foreach ($group["people_groups"] ?? [] as $value){
                    ?>
                    <li class="<?php echo esc_html( $value->ID )?>">
                        <a href="<?php echo esc_url( $value->permalink ) ?>"><?php echo esc_html( $value->post_title ) ?></a>
                    </li>
                <?php }
                if (sizeof( $group["people_groups"] ) === 0){
                    ?> <li id="no-people_groups"><?php esc_html_e( "No people group set", 'disciple_tools' ) ?></li><?php
                }
                ?>
            </ul>
        </div>


        <div class="medium-4 cell">
            <div class="section-subheader"><?php esc_html_e( 'Assigned to', 'disciple_tools' )?></div>
            <ul class="details-list assigned_to">
                <li class="current-assigned">
                    <?php
                    if ( isset( $groups["assigned_to"] ) ){
                        echo esc_html( $group["assigned_to"]["display"] );
                    } else {
                        esc_html_e( 'None Assigned', 'disciple_tools' );
                    }
                    ?>
                </li>
            </ul>


            <div class="section-subheader"><?php esc_html_e( 'Address', 'disciple_tools' )?>
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
                        <div class="address-text"><?php echo esc_html( $value["value"] );?></div>
                        <?php dt_contact_details_status( $value["key"], $verified, $invalid ) ?>
                    </li>
                <?php } ?>
            </ul>
        </div>

        <div class="medium-4 cell">
            <div class="section-subheader"><?php esc_html_e( 'Start Date', 'disciple_tools' )?></div>
            <ul class="date-list start_date details-list"><?php
            if ( isset( $group["start_date"] ) ) {
                echo esc_html( $group["start_date"]["formatted"] );
            } else {
                esc_html_e( "No start date", 'disciple_tools' );
            } ?>
            </ul>
            <div class="section-subheader"><?php esc_html_e( 'Church Start Date', 'disciple_tools' )?></div>
            <ul class="date-list church_start_date details-list"><?php
            if ( isset( $group["church_start_date"] ) ) {
                echo esc_html( $group["church_start_date"]["formatted"] );
            } else {
                esc_html_e( "No church start date", 'disciple_tools' );
            } ?>
            </ul>
            <div class="section-subheader"><?php esc_html_e( 'End Date', 'disciple_tools' )?></div>
            <ul class="date-list end_date details-list"><?php
            if ( isset( $group["end_date"] ) ) {
                echo esc_html( $group["end_date"]["formatted"] );
            } else {
                esc_html_e( "No end date", 'disciple_tools' );
            } ?>
            </ul>
        </div>


    </div>


</section> <!-- end article -->

<div class="reveal" id="group-details-edit" data-reveal>
    <h1><?php esc_html_e( "Edit Group", 'disciple_tools' ) ?></h1>
    <div class="display-fields">
        <div class="grid-x">
            <div class="cell section-subheader">
                <?php esc_html_e( 'Name', 'disciple_tools' ) ?>
            </div>
            <input type="text" id="title" class="edit-text-input" value="<?php the_title_attribute(); ?>">
        </div>


        <div class="grid-x">
            <div class="section-subheader cell">
                <?php esc_html_e( 'Assigned To', 'disciple_tools' )?>
            </div>
            <div class="assigned_to details">
                <var id="assigned_to-result-container" class="result-container assigned_to-result-container"></var>
                <div id="assigned_to_t" name="form-assigned_to">
                    <div class="typeahead__container">
                        <div class="typeahead__field">
                            <span class="typeahead__query">
                                <input class="js-typeahead-assigned_to input-height"
                                       name="assigned_to[query]" placeholder="<?php esc_html_e( "Search Users", 'disciple_tools' ) ?>"
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

        <div class="grix-x">
            <div class="section-subheader cell">
                <img src="<?php echo esc_url( get_template_directory_uri() ) . '/dt-assets/images/house.svg' ?>">
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
                <?php esc_html_e( 'Start Date', 'disciple_tools' )?>
            </div>
            <div class="start_date"><input type="text" class="date-picker" id="start_date"></div>
        </div>

        <div class="grix-x">
            <div class="section-subheader cell">
                <?php esc_html_e( 'Church Start Date', 'disciple_tools' )?>
            </div>
            <div class="church_start_date"><input type="text" class="date-picker" id="church_start_date"></div>
        </div>

        <div class="grix-x">
            <div class="section-subheader cell">
                <?php esc_html_e( 'End Date', 'disciple_tools' )?>
            </div>
            <div class="end_date"><input type="text" class="date-picker" id="end_date"></div>
        </div>


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
                                       name="people_groups[query]" placeholder="<?php esc_html_e( "Search People_groups", 'disciple_tools' ) ?>"
                                       autocomplete="off">
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>




        <div class="grix-x">
            <div class="section-subheader cell">
                <img src="<?php echo esc_url( get_template_directory_uri() ) . '/dt-assets/images/location.svg' ?>">
                <?php esc_html_e( 'Locations', 'disciple_tools' ) ?>
            </div>
            <div class="locations">
                <var id="locations-result-container" class="result-container"></var>
                <div id="locations_t" name="form-locations" class="scrollable-typeahead typeahead-margin-when-active">
                    <div class="typeahead__container">
                        <div class="typeahead__field">
                            <span class="typeahead__query">
                                <input class="js-typeahead-locations"
                                       name="locations[query]" placeholder="<?php esc_html_e( "Search Locations", 'disciple_tools' ) ?>"
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
            <?php esc_html_e( 'Cancel', 'disciple_tools' )?>
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
