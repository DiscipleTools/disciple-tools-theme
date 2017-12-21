<?php
(function() {
?>
<?php
$group = Disciple_Tools_Groups::get_group( get_the_ID(), true );
$locations = Disciple_Tools_Locations::get_locations();
$current_user = wp_get_current_user();
$group_fields = Disciple_Tools_Groups_Post_Type::instance()->get_custom_fields_settings();


function dt_contact_details_status( $id, $verified, $invalid ){
    ?>
    <img id="<?php echo esc_attr( $id . '-verified', 'disciple_tools' ); ?>" class="details-status" style="display: <?php echo esc_attr( $verified, 'disciple_tools' ); ?>" src="<?php echo esc_url( get_template_directory_uri() ) . '/assets/images/verified.svg'; ?>"/>
    <img id="<?php echo esc_attr( $id . '-invalid', 'disciple_tools' ); ?>"  class="details-status" style="display: <?php echo esc_attr( $invalid, 'disciple_tools' ); ?>"  src="<?php echo esc_url( get_template_directory_uri() ) . '/assets/images/broken.svg'; ?>" />
    <?php
}
function dt_contact_details_edit( $id, $remove = false ){
    ?>
    <ul class='dropdown menu' data-click-open='true'
        data-dropdown-menu data-disable-hover='true'
        style='display:inline-block'>
        <li>
            <button class="social-details-options-button">
                <img src="<?php esc_html_e( get_template_directory_uri() . '/assets/images/menu-dots.svg', 'disciple_tools'  )?>" style='padding:3px 3px'>
            </button>
            <ul class='menu'>
                <li>
                    <button class='details-status-button field-status verify'
                            data-status='valid'
                            data-id='<?php esc_html_e( $id, 'disciple_tools'  ) ?>'>
                        <?php esc_html_e( 'Valid', 'disciple_tools' )?>
                    </button>
                </li>
                <li>
                    <button class='details-status-button field-status invalid'
                            data-status="invalid"
                            data-id='<?php esc_html_e( $id, 'disciple_tools'  ) ?>'>
                        <?php esc_html_e( 'Invalid', 'disciple_tools' )?>
                    </button>
                </li>
                <li>
                    <button class='details-status-button field-status'
                            data-status="reset"
                            data-id='<?php esc_html_e( $id, 'disciple_tools'  ) ?>'>
                        <?php esc_html_e( 'Unconfirmed', 'disciple_tools' )?>
                    </button>
                </li>
                <?php if ($remove){ ?>
                    <li>
                        <button class='details-remove-button delete-method'
                                data-id='<?php esc_html_e( $id, 'disciple_tools'  ) ?>'>
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
        <span id="group-status-label" class="button alert label details-list status">Status: <?php esc_html_e( $group['group_status']['label'] ?? '', 'disciple_tools'  ); ?></span>
        <select id="group-status-select" class="status details-edit" style="width:fit-content; display:none">
            <?php foreach ( $group_fields["group_status"]["default"] as $status_key => $status_label ) { ?>
                <option value="<?php echo esc_attr( $status_key, 'disciple_tools' ); ?>"
                <?php echo esc_attr( $status_key === $group['group_status']['key'] ? 'selected' : '', 'disciple_tools' ); ?>>
                    <?php esc_html_e( $status_label, 'disciple_tools'  ) ?>
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
            <ul class="locations-list">
                <?php
                foreach ($group["locations"] ?? [] as $value){
                    ?>
                    <li class="<?php echo intval( $value->ID ); ?>">
                        <a href="<?php echo esc_url( $value->permalink ); ?>"><?php esc_html_e( $value->post_title, 'disciple_tools'  ); ?></a>
                        <button class="details-remove-button details-edit"
                                data-field="locations" data-id="<?php echo esc_attr( $value->ID, 'disciple_tools' ); ?>"
                                data-name="<?php echo esc_attr( $value->post_title, 'disciple_tools' ); ?>">
                            <?php esc_html_e( 'Remove', 'disciple_tools' )?>
                        </button>
                    </li>
                    <?php
                }
                if (sizeof( $group["locations"] ) === 0){
                    echo '<li id="no-location">No location set</li>';
                }
                ?>
            </ul>
            <div class="locations details-edit">
                <input class="typeahead" type="text" placeholder="Type to search locations">
            </div>

            <div class="section-subheader"><?php esc_html_e( 'People Groups', 'disciple_tools' )?></div>
            <ul class="people_groups-list">
                <?php
                foreach ($group["people_groups"] ?? [] as $value){
                    ?>
                    <li class="<?php esc_html_e( $value->ID, 'disciple_tools'  )?>">
                        <a href="<?php echo esc_url( $value->permalink ) ?>"><?php esc_html_e( $value->post_title, 'disciple_tools'  ) ?></a>
                        <button class="details-remove-button connection details-edit"
                                data-field="people_groups" data-id="<?php esc_html_e( $value->ID, 'disciple_tools'  ) ?>"
                                data-name="<?php esc_html_e( $value->post_title, 'disciple_tools'  ) ?>">
                            <?php esc_html_e( 'Remove', 'disciple_tools' )?>
                        </button>
                    </li>
                <?php }
                if (sizeof( $group["people_groups"] ) === 0){
                    echo '<li id="no-people-group">No people group set</li>';
                }
                ?>
            </ul>
            <div class="people-groups details-edit">
                <input class="typeahead" type="text" placeholder="Type to search people groups">
            </div>
        </div>


        <div class="medium-4 cell">
            <div class="section-subheader"><?php esc_html_e( 'Assigned to', 'disciple_tools' )?>
                <span class="assigned_to details-edit">:
                </span> <span class="assigned_to details-edit current-assigned">:</span> </div>
            <ul class="details-list assigned_to">
                <li class="current-assigned">
                    <?php
                    if ( isset( $group["assigned_to"] ) ){
                        esc_html_e( $group["assigned_to"]["display"], 'disciple_tools'  );
                    } else {
                        echo "None Assigned";
                    }
                    ?>
                </li>
            </ul>
            <div class="assigned_to details-edit">
                <input class="typeahead" type="text" placeholder="Type to search users">
            </div>

            <div class="section-subheader"><?php esc_html_e( 'Address', 'disciple_tools' )?>
                <button id="add-new-address" class="details-edit">
                    <img src="<?php esc_html_e( get_template_directory_uri() . '/assets/images/small-add.svg', 'disciple_tools'  ) ?>"/>
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
                    <li class="<?php esc_html_e( $value["key"], 'disciple_tools'  ) ?> address-row">
                        <div class="address-text"><?php esc_html_e( $value["value"], 'disciple_tools'  );?></div>
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
            <div class="start_date details-list"><?php esc_html_e( $group["start_date"] ?? "No start date", 'disciple_tools'  ); ?> </div>
            <div class="start_date details-edit"><input type="text" id="start-date-picker"></div>
            <div class="section-subheader"><?php esc_html_e( 'End Date', 'disciple_tools' )?></div>
            <div class="end_date details-list"><?php esc_html_e( $group["end_date"] ?? "No end date", 'disciple_tools'  ); ?> </div>
            <div class="end_date details-edit"><input type="text" id="end-date-picker"></div>
        </div>


    </div>


</section> <!-- end article -->

<?php
})();
