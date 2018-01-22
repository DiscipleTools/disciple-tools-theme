<?php
( function() {
?>
<?php
$group = Disciple_Tools_Groups::get_group( get_the_ID(), true );
$locations = Disciple_Tools_Locations::get_locations();
$current_user = wp_get_current_user();
$group_fields = Disciple_Tools_Groups_Post_Type::instance()->get_custom_fields_settings();


function dt_contact_details_status( $id, $verified, $invalid ){
    ?>
    <img id="<?php echo esc_attr( $id . '-verified', 'disciple_tools' ); ?>" class="details-status" style="display: <?php echo esc_attr( $verified, 'disciple_tools' ); ?>" src="<?php echo esc_url( get_template_directory_uri() ) . '/dt-assets/images/verified.svg'; ?>"/>
    <img id="<?php echo esc_attr( $id . '-invalid', 'disciple_tools' ); ?>"  class="details-status" style="display: <?php echo esc_attr( $invalid, 'disciple_tools' ); ?>"  src="<?php echo esc_url( get_template_directory_uri() ) . '/dt-assets/images/broken.svg'; ?>" />
    <?php
}
function dt_contact_details_edit( $id, $remove = false ){
    ?>
    <ul class='dropdown menu' data-click-open='true'
        data-dropdown-menu data-disable-hover='true'
        style='display:inline-block'>
        <li>
            <button class="social-details-options-button">
                <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/menu-dots.svg' )?>" style='padding:3px 3px'>
            </button>
            <ul class='menu'>
                <li>
                    <button class='details-status-button field-status verify'
                            data-status='valid'
                            data-id='<?php echo esc_html( $id ) ?>'>
                        <?php esc_html_e( 'Valid', 'disciple_tools' )?>
                    </button>
                </li>
                <li>
                    <button class='details-status-button field-status invalid'
                            data-status="invalid"
                            data-id='<?php echo esc_html( $id ) ?>'>
                        <?php esc_html_e( 'Invalid', 'disciple_tools' )?>
                    </button>
                </li>
                <li>
                    <button class='details-status-button field-status'
                            data-status="reset"
                            data-id='<?php echo esc_html( $id ) ?>'>
                        <?php esc_html_e( 'Unconfirmed', 'disciple_tools' )?>
                    </button>
                </li>
                <?php if ($remove){ ?>
                    <li>
                        <button class='details-remove-button delete-method'
                                data-id='<?php echo esc_html( $id ) ?>'>
                            <?php esc_html_e( 'Delete item', 'disciple_tools' )?>
                        </button>
                    </li>
                <?php } ?>
            </ul>
        </li>
    </ul>
<?php } ?>

<section class="bordered-box">

    <div class="item-details-header-row">
        <i class="fi-torsos-all large"></i>
        <span class="item-details-header details-list title" ><?php the_title_attribute(); ?></span>
        <input id="title" class="text-field details-edit" value="<?php the_title_attribute(); ?>">
        <select id="group_status" class="status select-field" style="width:fit-content;">
            <?php foreach ( $group_fields["group_status"]["default"] as $status_key => $status_label ) { ?>
                <option value="<?php echo esc_attr( $status_key, 'disciple_tools' ); ?>"
                <?php echo esc_attr( $status_key === $group['group_status']['key'] ? 'selected' : '', 'disciple_tools' ); ?>>
                    <?php echo esc_html( $status_label ) ?>
                </option>
            <?php } ?>
        </select>

        <button class=" float-right" id="edit-details">
            <i class="fi-pencil"></i>
            <span id="edit-button-label"><?php esc_html_e( 'Edit', 'disciple_tools' )?></span>
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
                        <a href="<?php echo esc_url( $value->permalink ); ?>"><?php echo esc_html( $value->post_title ); ?></a>
                    </li>
                    <?php
                }
                if (sizeof( $group["locations"] ) === 0){
                    ?> <li id="no-location"><?php esc_html_e( "No location set", 'disciple_tools' ) ?></li><?php
                }
                ?>
            </ul>
            <div class="locations">
                <var id="locations-result-container" class="result-container"></var>
                <div id="locations_t" name="form-locations" class="scrollable-typeahead">
                    <div class="typeahead__container">
                        <div class="typeahead__field">
                            <span class="typeahead__query">
                                <input class="js-typeahead-locations"
                                       name="locations[query]" placeholder="Search Locations"
                                       autocomplete="off">
                            </span>
                        </div>
                    </div>
                </div>
            </div>

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
                    ?> <li id="no-people-group"><?php esc_html_e( "No people group set", 'disciple_tools' ) ?></li><?php
                }
                ?>
            </ul>
            <div class="people_groups">
                <var id="people_groups-result-container" class="result-container"></var>
                <div id="people_groups_t" name="form-people_groups" class="scrollable-typeahead">
                    <div class="typeahead__container">
                        <div class="typeahead__field">
                            <span class="typeahead__query">
                                <input class="js-typeahead-people_groups"
                                       name="people_groups[query]" placeholder="Search People_groups"
                                       autocomplete="off">
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div class="medium-4 cell">
            <div class="section-subheader"><?php esc_html_e( 'Assigned to', 'disciple_tools' )?>
                <span class="assigned_to details-edit">:</span>
                <span class="assigned_to details-edit current-assigned"></span>
            </div>
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

            <div class="section-subheader"><?php esc_html_e( 'Address', 'disciple_tools' )?>
                <button id="add-new-address" class="details-edit">
                    <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/small-add.svg' ) ?>"/>
                </button>
            </div>
            <ul class="address details-list">
                <?php
                if (sizeof( $group["address"] ?? [] ) === 0 ){
                    ?> <li id="no-address"><?php esc_html_e( 'No address set', 'disciple_tools' )?></li> <?php
                }
                foreach ($group["address"] ?? [] as $value){
                    $verified = isset( $value["verified"] ) && $value["verified"] === true ? "inline" :"none";
                    $invalid = isset( $value["invalid"] ) && $value["invalid"] === true ? "inline" :"none";
                    ?>
                    <li class="<?php echo esc_html( $value["key"] ) ?> address-row">
                        <div class="address-text"><?php echo esc_html( $value["value"] );?></div>
                        <?php dt_contact_details_status( $value["key"], $verified, $invalid ) ?>
                    </li>
                <?php } ?>
            </ul>
            <ul id="address-list" class="details-edit">
                <?php
                if ( isset( $group["address"] )){
                    foreach ($group["address"] ?? [] as $value){
                        $verified = isset( $value["verified"] ) && $value["verified"] === true;
                        $invalid = isset( $value["invalid"] ) && $value["invalid"] === true;
                        ?>
                        <div class="<?php echo esc_attr( $value["key"], 'disciple_tools' )?>">
                            <textarea rows="3" id="<?php echo esc_attr( $value["key"], 'disciple_tools' )?>" class="contact-input"><?php echo esc_attr( $value["value"], 'disciple_tools' )?></textarea>
                            <?php dt_contact_details_edit( $value["key"], true ) ?>
                        </div>
                        <hr>

                    <?php }
                }?>
            </ul>
        </div>

        <div class="medium-4 cell">
            <div class="section-subheader"><?php esc_html_e( 'Start Date', 'disciple_tools' )?></div>
            <div class="start_date details-list"><?php
            if ( isset( $group["start_date"] ) ) {
                echo esc_html( $group["start_date"] );
            } else {
                esc_html_e( "No start date", 'disciple_tools' );
            } ?>
            </div>
            <div class="start_date details-edit"><input type="text" id="start-date-picker"></div>
            <div class="section-subheader"><?php esc_html_e( 'End Date', 'disciple_tools' )?></div>
            <div class="end_date details-list"><?php
            if ( isset( $group["end_date"] ) ) {
                echo esc_html( $group["end_date"] );
            } else {
                esc_html_e( "No end date", 'disciple_tools' );
            } ?>
            </div>
            <div class="end_date details-edit"><input type="text" id="end-date-picker"></div>
        </div>


    </div>


</section> <!-- end article -->

<?php
} )();
