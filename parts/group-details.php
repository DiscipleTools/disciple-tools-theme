<?php
$group = Disciple_Tools_Groups::get_group( get_the_ID(), true );
$locations = Disciple_Tools_Locations::get_locations();
$current_user = wp_get_current_user();
$group_fields = Disciple_Tools_Groups_Post_Type::instance()->get_custom_fields_settings();


function contact_details_status( $id, $verified, $invalid ){
    $buttons = '<img id="'. $id .'-verified" class="details-status" style="display:' . $verified . '" src="'.get_template_directory_uri() . '/assets/images/verified.svg"/>';
    $buttons .= '<img id="'. $id .'-invalid" class="details-status" style="display:' . $invalid . '" src="'.get_template_directory_uri() . '/assets/images/broken.svg" />';
    return $buttons;
}

?>

<section class="bordered-box">

    <div class="item-details-header-row">
        <i class="fi-torsos-all large"></i>
        <span class="item-details-header"><?php the_title_attribute(); ?></span>
        <span id="group-status-label" class="button alert label details-list status">Status: <?php echo $group['group_status']['label'] ?? ''; ?></span>
          <select id="group-status-select" class="status details-edit" style="width:fit-content; display:none">
            <?php foreach( $group_fields["group_status"]["default"] as $status_key => $status_label ) { ?>
            <option value="<?php echo $status_key; ?>"
                <?php echo $status_key === $group['group_status']['key'] ? 'selected': '' ?>>
                <?php echo esc_html( $status_label ) ?>
            </option>
            <?php } ?>

        </select>

        <button class=" float-right" id="edit-details">
            <i class="fi-pencil"></i>
            <span id="edit-button-label">Edit</span>
        </button>
    </div>

    <div class="display-fields grid-x grid-margin-x">
        <div class="medium-4 cell">
            <strong>Address</strong>
            <button class="address details-edit add-button">
                <img src="<?php echo get_template_directory_uri() . '/assets/images/small-add.svg' ?>"/>
            </button>
            <ul class="address details-list">
                <?php
                foreach($group[ "address" ]  ?? [] as $value){
                    $verified = isset( $value["verified"] ) && $value["verified"] === true ? "inline" :"none";
                    $invalid = isset( $value["invalid"] ) && $value["invalid"] === true ? "inline" :"none";
                    echo  '<li class="'. $value["key"] .'">' . esc_html( $value["value"] ) .
                        contact_details_status( $value["key"], $verified, $invalid ) .
                        '</li>';
                }?>
            </ul>
            <?php
            if ( isset( $group["address"] ) ){
                $type_label = "Address";
                $type = "address";
                $new_input_id = "new-" . $type;
                $list_id = $type . "-list";
                ?>

                <ul class="details-edit address-list">
                    <?php
                    foreach($group[ "address" ] ?? [] as $value){
                        $verified = isset( $value["verified"] ) && $value["verified"] === true;
                        $invalid = isset( $value["invalid"] ) && $value["invalid"] === true;
                        $html = '<li>';
                        if ( !$verified ){
                            $html .= '<button class="details-status-button verify" id="' . esc_attr( $value["key"] ) . '-verify" onclick="verify_contact_method(' . get_the_ID() . ', \'' . esc_attr( $value["key"] ) . '\')">Verify</button>';
                        }
                        if ( !$invalid ){
                            $html .= '<button class="details-status-button invalid" id="' . esc_attr( $value["key"] ) . '-invalidate" onclick="invalidate_contact_method(' . get_the_ID() . ', \'' . esc_attr( $value["key"] ) . '\')">Invalidate</button>';
                        }
                        $html .= '<textarea id="' . esc_attr( $value["key"] ) . '">'
                            . esc_html( $value["value"] ) .
                            '</textarea>
                </li>';
                        echo $html;
                    }?>
                </ul>
                <?php
            }
            ?>
        </div>

        <div class="medium-4 cell">
            <strong>Locations</strong>
            <ul class="locations-list">
                <?php
                foreach($group[ "locations" ] ?? [] as $value){
                    echo '<li class="'. $value->ID .'">
                        <a href="' . esc_url( $value->permalink ) . '">'. esc_html( $value->post_title ) .'</a>
                        <button class="details-remove-button details-edit"
                                data-field="locations" data-id="'. $value->ID . '"
                                data-name="'. $value->post_title .'"
                        >Remove</button>
                    </li>';
                }
                if (sizeof( $group["locations"] ) === 0){
                    echo '<li id="no-location">No location set</li>';
                }
                ?>
            </ul>
            <div class="locations details-edit">
                <input class="typeahead" type="text" placeholder="Select a new location">
            </div>
        </div>
        <div class="medium-4 cell">
            <strong>Start Date</strong>
            <div class="start_date details-list"><?php echo $group["start_date"] ?? "No start date" ?> </div>
            <div class="start_date details-edit"><input type="text" id="start-date-picker"></div>
        </div>
        <div class="medium-4 cell">
            <strong>End Date</strong>
            <div class="end_date details-list"><?php echo $group["end_date"] ?? "No end date" ?> </div>
            <div class="end_date details-edit"><input type="text" id="end-date-picker"></div>
        </div>
        <div class="medium-4 cell">
            <strong>Assigned to
                <span class="assigned_to details-edit">:
                </span> <span class="assigned_to details-edit current-assigned">:</span> </strong>
            <ul class="details-list assigned_to">
                <li class="current-assigned">
                    <?php
                    if ( isset( $group["assigned_to"] ) ){
                        echo esc_html( $group["assigned_to"]["display"] );
                    } else {
                        echo "None Assigned";
                    }
                    ?>
                </li>
            </ul>
            <div class="assigned_to details-edit">
                <input class="typeahead" type="text" placeholder="Select a new user">
            </div>
        </div>


    </div>


</section> <!-- end article -->

