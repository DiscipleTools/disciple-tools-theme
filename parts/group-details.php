<?php
$group = Disciple_Tools_Groups::get_group( get_the_ID(), true );
$channel_list = Disciple_Tools_Contacts::get_channel_list();
$users = Disciple_Tools_Contacts::get_assignable_users( get_the_ID() );
$locations = Disciple_Tools_Locations::get_locations();
$current_user = wp_get_current_user();





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
        <button class=" float-right" id="edit-details"><i class="fi-pencil"></i> Edit</button>
    </div>

    <div class="display-fields grid-x">
        <div class="medium-4 cell">
            <strong>Address</strong>
            <ul>
                <?php
                foreach($group[ "address" ]  ?? [] as $value){
                    $verified = isset( $value["verified"] ) && $value["verified"] === true ? "inline" :"none";
                    $invalid = isset( $value["invalid"] ) && $value["invalid"] === true ? "inline" :"none";
                    echo  '<li>' . esc_html( $value["value"] ) .
                        contact_details_status( $value["key"], $verified, $invalid ) .
                        '</li>';
                }?>
            </ul>
        </div>

        <div class="medium-4 cell">
            <strong>Locations</strong>
            <ul class="locations-list">
                <?php
                foreach($group[ "locations" ] ?? [] as $value){
                    echo '<li class="'. $value->ID .'">
                        <a href="' . esc_attr( $value->permalink ) . '">'. esc_html( $value->post_title ) .'</a>
                        <button class="details-remove-button details-edit" 
                                data-field="locations" data-id="'. $value->ID . '" data-name="'. $value->post_title .'">
                            Remove
                        </button>
                    </li>';
                }
                if (sizeof( $group["locations"] ) === 0){
                    echo '<li>No location set</li>';
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

