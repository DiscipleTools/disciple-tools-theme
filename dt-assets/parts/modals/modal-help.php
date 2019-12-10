<div class="reveal" id="help-modal" data-reveal>

    <!--    Contact Details Tile  -->
    <div class="help-section" id="contact-details-help-text" style="display: none">
      <h3><?php esc_html_e( "Contact Details", 'disciple_tools' ) ?></h3>
      <p><?php esc_html_e( "This is the area where you can view and edit the contact details for this contact.", 'disciple_tools' ) ?></p>
      <ul>
            <?php //@// TODO: simplify this list by possibly using foreach statements ?>
            <?php //$field = Disciple_Tools_Contact_Post_Type::instance()->get_custom_fields_settings()["contact_name"]; ?>
            <li><strong><?php esc_html_e( "Contact Name", 'disciple_tools' ) ?></strong> - <?php esc_html_e( "The name of the contact is searchable and can be used to help you filter your contacts in the Contacts List page. The system uses this name to check for duplicate contacts.", 'disciple_tools' ) ?></li>
            <?php $field = Disciple_Tools_Contact_Post_Type::instance()->get_custom_fields_settings()["overall_status"]; ?>
            <li><strong><?php echo esc_html( $field["name"] ) ?></strong> - <?php echo esc_html( $field["description"] ) ?></li>
            <?php $field = Disciple_Tools_Contact_Post_Type::instance()->get_custom_fields_settings()["assigned_to"]; ?>
            <li><strong><?php echo esc_html( $field["name"] ) ?></strong> - <?php echo esc_html( $field["description"] ) ?></li>
            <?php $field = Disciple_Tools_Contact_Post_Type::instance()->get_custom_fields_settings()["subassigned"]; ?>
            <li><strong><?php echo esc_html( $field["name"] ) ?></strong> - <?php echo esc_html( $field["description"] ) ?></li>
            <?php //$field = Disciple_Tools_Contact_Post_Type::instance()->get_custom_fields_settings()["phone"]; ?>
            <li><strong><?php esc_html_e( "Contact Phone Number", 'disciple_tools' ) ?></strong> - <?php esc_html_e( "A phone number for this contact. The system uses this phone number to check for duplicate contacts.", 'disciple_tools' ) ?></li>
            <?php //$field = Disciple_Tools_Contact_Post_Type::instance()->get_custom_fields_settings()["email"]; ?>
            <li><strong><?php esc_html_e( "Contact Email", 'disciple_tools' ) ?></strong> - <?php esc_html_e( "An email for this contact. The system uses this email to check for duplicate contacts.", 'disciple_tools' ) ?></li>
            <?php //$field = Disciple_Tools_Contact_Post_Type::instance()->get_custom_fields_settings()["socialmedia"]; ?>
            <li><strong><?php esc_html_e( "Social Media", 'disciple_tools' ) ?></strong> - <?php esc_html_e( "Social media accounts for this contact.", 'disciple_tools' ) ?></li>
            <?php //$field = Disciple_Tools_Contact_Post_Type::instance()->get_custom_fields_settings()["address"]; ?>
            <li><strong><?php esc_html_e( "Contact Address", 'disciple_tools' ) ?></strong> - <?php esc_html_e( "A contact address for this contact. (e.g., 124 Market St or “Jon’s Famous Coffee Shop).", 'disciple_tools' ) ?></li>
            <?php //$field = Disciple_Tools_Contact_Post_Type::instance()->get_custom_fields_settings()["locations"]; ?>
            <li><strong><?php esc_html_e( "Locations", 'disciple_tools' ) ?></strong> - <?php esc_html_e( "The general area where this contact is located.", 'disciple_tools' ) ?></li>
            <?php $field = Disciple_Tools_Contact_Post_Type::instance()->get_custom_fields_settings()["people_groups"]; ?>
            <li><strong><?php echo esc_html( $field["name"] ) ?></strong> - <?php echo esc_html( $field["description"] ) ?></li>
            <?php $field = Disciple_Tools_Contact_Post_Type::instance()->get_custom_fields_settings()["age"]; ?>
            <li><strong><?php echo esc_html( $field["name"] ) ?></strong> - <?php echo esc_html( $field["description"] ) ?></li>
            <?php $field = Disciple_Tools_Contact_Post_Type::instance()->get_custom_fields_settings()["gender"]; ?>
            <li><strong><?php echo esc_html( $field["name"] ) ?></strong> - <?php echo esc_html( $field["description"] ) ?></li>
            <?php $field = Disciple_Tools_Contact_Post_Type::instance()->get_custom_fields_settings()["sources"]; ?>
            <li><strong><?php echo esc_html( $field["name"] ) ?></strong> - <?php echo esc_html( $field["description"] ) ?></li>
      </ul>
    <!-- end Contact Details modal -->
    </div>

    <!-- Group Details Tile -->
    <div class="help-section" id="group-details-help-text" style="display: none">
      <h3><?php esc_html_e( "Group Details", 'disciple_tools' ) ?></h3>
      <p><?php esc_html_e( "This is the area where you can view and edit the contact details for this group.", 'disciple_tools' ) ?></p>
      <ul>
        <?php //$field = Disciple_Tools_Groups_Post_Type::instance()->get_custom_fields_settings()["group_name"]; ?>
        <li><strong><?php esc_html_e( "Group Name", 'disciple_tools' ) ?></strong> - <?php esc_html_e( "The name of the group is searchable and can be used to help you filter your contacts in the Groups List page.", 'disciple_tools' ) ?></li>
        <?php $field = Disciple_Tools_Groups_Post_Type::instance()->get_custom_fields_settings()["group_status"]; ?>
        <li><strong><?php echo esc_html( $field["name"] ) ?></strong> - <?php echo esc_html( $field["description"] ) ?>
          <ul>
                <?php foreach ( $field["default"] as $option ): ?>
                  <li><strong><?php echo esc_html( $option["label"] ) ?></strong> - <?php echo esc_html( $option["description"] ) ?? "" ?></li>
                <?php endforeach; ?>
          </ul>
        </li>
            <?php $field = Disciple_Tools_Groups_Post_Type::instance()->get_custom_fields_settings()["assigned_to"]; ?>
        <li><strong><?php echo esc_html( $field["name"] ) ?></strong> - <?php echo esc_html( $field["description"] ) ?>
                <?php if ( current_user_can( "view_any_contacts" ) ) : ?>
            <p><strong><?php esc_html_e( "User workload status icons legend:", 'disciple_tools' ) ?></strong></p>
            <ul style="list-style-type:none">
                    <?php $workload_status_options = dt_get_site_custom_lists()["user_workload_status"] ?? [];
                    foreach ( $workload_status_options as $option_key =>$option_val ): ?>
                <li><span style="background-color: <?php echo esc_html( $option_val["color"] ) ?>; height:10px; padding: 0 5px; border-radius: 2px">&nbsp;</span> <?php echo esc_html( $option_val["label"] ) ?></li>
                    <?php endforeach ?>
                <li><img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/broken.svg' )?>" /> 2: <?php esc_html_e( "2 contacts need an update", 'disciple_tools' ) ?> </li>
            </ul>
                <?php endif; ?>
        </li>
        <?php $field = Disciple_Tools_Groups_Post_Type::instance()->get_custom_fields_settings()["coaches"]; ?>
        <li><strong><?php echo esc_html( $field["name"] ) ?></strong> - <?php echo esc_html( $field["description"] ) ?></li>
        <?php //$field = Disciple_Tools_Groups_Post_Type::instance()->get_custom_fields_settings()["locations"]; ?>
        <li><strong><?php esc_html_e( "Locations", 'disciple_tools' ) ?></strong> - <?php esc_html_e( "A general idea where this group is located.", 'disciple_tools' ) ?></li>
        <?php $field = Disciple_Tools_Groups_Post_Type::instance()->get_custom_fields_settings()["people_groups"]; ?>
        <li><strong><?php echo esc_html( $field["name"] ) ?></strong> - <?php echo esc_html( $field["description"] ) ?></li>
        <?php //$field = Disciple_Tools_Groups_Post_Type::instance()->get_custom_fields_settings()["address"]; ?>
        <li><strong><?php esc_html_e( "Address", 'disciple_tools' ) ?></strong> - <?php esc_html_e( "The address where this group meets. (e.g., 124 Market St or “Jon’s Famous Coffee Shop).", 'disciple_tools' ) ?></li>
        <?php $field = Disciple_Tools_Groups_Post_Type::instance()->get_custom_fields_settings()["start_date"]; ?>
        <li><strong><?php echo esc_html( $field["name"] ) ?></strong> - <?php echo esc_html( $field["description"] ) ?></li>
        <?php $field = Disciple_Tools_Groups_Post_Type::instance()->get_custom_fields_settings()["church_start_date"]; ?>
        <li><strong><?php echo esc_html( $field["name"] ) ?></strong> - <?php echo esc_html( $field["description"] ) ?></li>
        <?php $field = Disciple_Tools_Groups_Post_Type::instance()->get_custom_fields_settings()["end_date"]; ?>
        <li><strong><?php echo esc_html( $field["name"] ) ?></strong> - <?php echo esc_html( $field["description"] ) ?></li>
      </ul>
      <!-- end Group Details modal -->
    </div>

    <!--    Contact Status  -->
    <div class="help-section" id="overall-status-help-text" style="display: none">
        <?php $field = Disciple_Tools_Contact_Post_Type::instance()->get_custom_fields_settings()["overall_status"]; ?>
        <h3><?php echo esc_html( $field["name"] ) ?></h3>
        <p><?php echo esc_html( $field["description"] ) ?></p>
        <ul>
            <?php foreach ( $field["default"] as $option ): ?>
                <li><strong><?php echo esc_html( $option["label"] ) ?></strong> - <?php echo esc_html( $option["description"] ?? "" ) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>

    <!--    Assigned to -->
    <div class="help-section" id="assigned-to-help-text" style="display: none">
            <?php $field = Disciple_Tools_Contact_Post_Type::instance()->get_custom_fields_settings()["assigned_to"]; ?>
        <h3><?php echo esc_html( $field["name"] )?></h3>
        <p><?php echo esc_html( $field["description"] ) ?></p>
        <?php if ( current_user_can( "view_any_contacts" ) ) : ?>
            <p><strong><?php esc_html_e( "User workload status icons legend:", 'disciple_tools' ) ?></strong></p>
            <ul style="list-style-type:none">
            <?php $workload_status_options = dt_get_site_custom_lists()["user_workload_status"] ?? [];
            foreach ( $workload_status_options as $option_key =>$option_val ): ?>
                <li><span style="background-color: <?php echo esc_html( $option_val["color"] ) ?>; height:10px; padding: 0 5px; border-radius: 2px">&nbsp;</span> <?php echo esc_html( $option_val["label"] ) ?></li>
            <?php endforeach ?>
                <li><img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/broken.svg' )?>" /> 2: <?php esc_html_e( "2 contacts need an update.", 'disciple_tools' ) ?> </li>
            </ul>
        <?php endif; ?>
    </div>

    <!--    Subassigned to  -->
    <div class="help-section" id="subassigned-to-help-text" style="display: none">
            <?php $field = Disciple_Tools_Contact_Post_Type::instance()->get_custom_fields_settings()["subassigned"]; ?>
        <h3><?php echo esc_html( $field["name"] ) ?></h3>
        <p><?php echo esc_html( $field["description"] ) ?></p>
    </div>

    <!--    Group Coaches  -->
    <div class="help-section" id="coaches-help-text" style="display: none">
            <?php $field = Disciple_Tools_Groups_Post_Type::instance()->get_custom_fields_settings()["coaches"]; ?>
        <h3><?php echo esc_html( $field["name"] ) ?></h3>
        <p><?php echo esc_html( $field["description"] ) ?></p>
    </div>

    <!--    Quick Actions   -->
    <div class="help-section" id="quick-action-help-text" style="display: none">
        <h3><?php esc_html_e( "Quick action buttons", 'disciple_tools' ) ?></h3>
        <p><?php esc_html_e( 'These quick action buttons are here to aid you in updating the contact record faster.', 'disciple_tools' ) ?>
        <p><?php esc_html_e( 'They also track how many times each action has been used and also update the "Seeker Path". For example,
        If you click the "No Answer" button 4 times, the number 4 will be added to the "No Answer" action, meaning that you have attempted to call the contact 4 times, but they did not answer.
        This action will also change the "Seeker Path" to "Contact Attempted".', 'disciple_tools' ) ?>
        </p>
    </div>

    <!--    Comments and Activity - contact & group  -->
    <div class="help-section" id="comments-activity-help-text" style="display: none">
      <h3><?php esc_html_e( "Comments and Activity", 'disciple_tools' ) ?></h3>
      <p><?php esc_html_e( "This is where you will want to record important notes from meetings and conversations.", 'disciple_tools' ) ?></p>
      <p><?php esc_html_e( "Type @ and the name of a user to mention them in a comment. This user will then receive a notification.", 'disciple_tools' ) ?></p>
      <p><?php esc_html_e( "This section also includes the history of activity, such as when the contact or group status became active etc.", 'disciple_tools' ) ?></p>
      <p><?php esc_html_e( "You can filter this section either by `All`, `Comments`, or `Activity`.", 'disciple_tools' ) ?></p>
    </div>

    <!-- Contact Progress Tile -->
    <div class="help-section" id="contact-progress-help-text" style="display: none">
        <h3><?php esc_html_e( "Progress", 'disciple_tools' ) ?></h3>
        <p><?php esc_html_e( "Here you can track the progress of the contact's faith journey.", 'disciple_tools' ) ?></p>
        <ul>
                <?php
                $field = Disciple_Tools_Contact_Post_Type::instance()->get_custom_fields_settings()["seeker_path"]; ?>
          <li><strong><?php echo esc_html( $field["name"] ) ?></strong> - <?php echo esc_html( $field["description"] ) ?></li>
                <?php $field = Disciple_Tools_Contact_Post_Type::instance()->get_custom_fields_settings()["milestones"]; ?>
          <li><strong><?php echo esc_html( $field["name"] ) ?></strong> - <?php echo esc_html( $field["description"] ) ?></li>
                <?php $field = Disciple_Tools_Contact_Post_Type::instance()->get_custom_fields_settings()["baptism_date"]; ?>
          <li><strong><?php echo esc_html( $field["name"] ) ?></strong> - <?php echo esc_html( $field["description"] ); ?></li>
        </ul>
    </div>

    <!-- Seeker Path -->
    <div class="help-section" id="seeker-path-help-text" style="display: none">
        <?php $field = Disciple_Tools_Contact_Post_Type::instance()->get_custom_fields_settings()["seeker_path"]; ?>
        <h3><?php echo esc_html( $field["name"] ) ?></h3>
        <p><?php echo esc_html( $field["description"] ) ?></p>
        <!-- <?php echo esc_html( $field["name"] ) ?> list -->
        <ul>
            <?php foreach ( $field["default"] as $option ): ?>
                <li><strong><?php echo esc_html( $option["label"] ) ?></strong> - <?php echo esc_html( $option["description"] ) ?? "" ?></li>
            <?php endforeach; ?>
        </ul>
    </div>

    <!-- Faith Milestones -->
    <div class="help-section" id="faith-milestones-help-text" style="display: none">
        <?php $field = Disciple_Tools_Contact_Post_Type::instance()->get_custom_fields_settings()["milestones"]; ?>
        <h3><?php echo esc_html( $field["name"] ) ?></h3>
        <p><?php echo esc_html( $field["description"] ) ?></p>
        <!-- <?php echo esc_html( $field["name"] ) ?> list -->
        <ul>
            <?php foreach ( $field["default"] as $option ): ?>
                <li><strong><?php echo esc_html( $option["label"] ) ?></strong> - <?php echo esc_html( $option["description"] ) ?? "" ?></li>
            <?php endforeach; ?>
        </ul>
    </div>

    <!-- Baptism Date -->
    <div class="help-section" id="baptism-date-help-text" style="display: none">
        <h3><?php esc_html_e( "Baptism Date", 'disciple_tools' ) ?></h3>
        <p><?php esc_html_e( "This is where you set the date of when this contact was baptised.", 'disciple_tools' ) ?></p>
    </div>

    <!--  Group Health Metrics Tile  -->
    <div class="help-section" id="health-metrics-help-text" style="display: none">
            <?php
            $field = Disciple_Tools_Groups_Post_Type::instance()->get_custom_fields_settings()["health_metrics"]; ?>
            <h3><?php echo esc_html( $field["name"] ) ?></h3>
            <p><?php echo esc_html( $field["description"] ) ?></p>
            <ul>
                <?php foreach ( $field["default"] as $option ): ?>
                  <li><strong><?php echo esc_html( $option["label"] ) ?></strong> - <?php echo esc_html( $option["description"] ) ?? "" ?></li>
                <?php endforeach; ?>
            </ul>
            <p><?php esc_html_e( "If the group/church regularly practices any of the elements listed, then click each element to add them inside the circle.", 'disciple_tools' ) ?></p>
            <p><?php esc_html_e( "If the group has committed to be a church, indicate this by clicking the \"Church Commitment\" button to make the dotted line circle solid.", 'disciple_tools' ) ?></p>
    </div>

    <!-- Members Tile -->
    <div class="help-section" id="members-help-text" style="display: none">
        <h3><?php esc_html_e( "Members", 'disciple_tools' ) ?></h3>
        <p><?php esc_html_e( "This is the area where you list the number of contacts that are a part of the group.", 'disciple_tools' ) ?></p>
        <?php $field = Disciple_Tools_Groups_Post_Type::instance()->get_custom_fields_settings()["member_count"]; ?>
        <h4><?php echo esc_html( $field["name"] ) ?></h4>
        <p><?php echo esc_html( $field["description"] ) ?></p>
        <?php $field = Disciple_Tools_Groups_Post_Type::instance()->get_custom_fields_settings()["members"]; ?>
        <h4><?php echo esc_html( $field["name"] ) ?></h4>
        <p><?php echo esc_html( $field["description"] ) ?></p>
        <p><?php esc_html_e( "To add new members, click on the 'Create' or 'Select' and click on the name or search for them. You can also quickly navigate between the Group Records and the members’ Contact Records in this area.", 'disciple_tools' ) ?></p>
    </div>

    <!--  Group type  -->
    <div class="help-section" id="group-type-help-text" style="display: none">
        <?php $field = Disciple_Tools_Groups_Post_Type::instance()->get_custom_fields_settings()["group_type"]; ?>
        <h4><?php echo esc_html( $field["name"] ) ?></h4>
        <p><?php echo esc_html( $field["description"] ) ?></p>
        <!-- <?php echo esc_html( $field["name"] ) ?> list -->
        <ul>
            <?php foreach ( $field["default"] as $option ): ?>
                <li><strong><?php echo esc_html( $option["label"] ) ?></strong> - <?php echo esc_html( $option["description"] ?? "" ) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>

    <!--  Group Status  -->
    <div class="help-section" id="group-status-help-text" style="display: none">

        <?php
        $field = Disciple_Tools_Groups_Post_Type::instance()->get_custom_fields_settings()["group_status"]; ?>
      <h3><?php echo esc_html( $field["name"] ) ?></h3>
      <p><?php echo esc_html( $field["description"] ) ?></p>
        <!-- <?php echo esc_html( $field["name"] ) ?> list -->
        <ul>
            <?php foreach ( $field["default"] as $option ): ?>
                <li><strong><?php echo esc_html( $option["label"] ) ?></strong> - <?php echo esc_html( $option["description"] ) ?? "" ?></li>
            <?php endforeach; ?>
        </ul>
    </div>

    <!--  Groups Tile  -->
    <div class="help-section" id="group-connections-help-text" style="display: none">
        <h3><?php esc_html_e( "Group Connections", 'disciple_tools' ) ?></h3>
        <p><?php esc_html_e( "Here you can select what type of group this is and how it is related to other groups in the system.", 'disciple_tools' ) ?></p>

        <?php $field = Disciple_Tools_Groups_Post_Type::instance()->get_custom_fields_settings()["group_type"]; ?>
        <h4><?php echo esc_html( $field["name"] ) ?></h4>
        <p><?php echo esc_html( $field["description"] ) ?></p>
        <!-- <?php echo esc_html( $field["name"] ) ?> list -->
        <ul>
            <?php foreach ( $field["default"] as $option ): ?>
                <li><strong><?php echo esc_html( $option["label"] ) ?></strong> - <?php echo esc_html( $option["description"] ?? "" ) ?></li>
            <?php endforeach; ?>
        </ul>

        <?php //@// TODO: is there a better way to do this list  ?>
        <h4><?php esc_html_e( "Group Connections", 'disciple_tools' ) ?></h4>
        <p><?php esc_html_e( "Connections this group has with other groups in this system.", 'disciple_tools' ) ?></p>
        <!-- <?php //echo esc_html( $field["name"] ) ?> list -->
        <ul>
                <?php $field = Disciple_Tools_Groups_Post_Type::instance()->get_custom_fields_settings()["parent_groups"]; ?>
                <li><strong><?php echo esc_html( $field["name"] ) ?></strong> - <?php echo esc_html( $field["description"] ?? "" ) ?></li>
                <?php $field = Disciple_Tools_Groups_Post_Type::instance()->get_custom_fields_settings()["peer_groups"]; ?>
                <li><strong><?php echo esc_html( $field["name"] ) ?></strong> - <?php echo esc_html( $field["description"] ?? "" ) ?></li>
                <?php $field = Disciple_Tools_Groups_Post_Type::instance()->get_custom_fields_settings()["child_groups"]; ?>
                <li><strong><?php echo esc_html( $field["name"] ) ?></strong> - <?php echo esc_html( $field["description"] ?? "" ) ?></li>
        </ul>
    </div>

    <!--  Connections tile -->
    <div class="help-section" id="connections-help-text" style="display: none">
        <h3><?php esc_html_e( "Connections", 'disciple_tools' ) ?></h3>
        <p><?php esc_html_e( "Here you can make connections between this contact and other people in this system.", 'disciple_tools' ) ?></p>

        <?php //@// TODO: simplify the list to use foreach statements ?>
        <h4><?php esc_html_e( "Contact Connections", 'disciple_tools' ) ?></h4>
        <p><?php esc_html_e( "Connections this contact has with other users (and groups) in this system.", 'disciple_tools' ) ?></p>
        <ul>
                <li><strong><?php esc_html_e( "Groups", 'disciple_tools' ) ?></strong> - <?php esc_html_e( "The groups that this user is in. Search for a group or create a new group this user will be added to.", 'disciple_tools' ) ?></li>
                <?php $field = Disciple_Tools_Contact_Post_Type::instance()->get_custom_fields_settings()["relation"]; ?>
                <li><strong><?php echo esc_html( $field["name"] ) ?></strong> - <?php echo esc_html( $field["description"] ?? "" ) ?></li>
                <?php $field = Disciple_Tools_Contact_Post_Type::instance()->get_custom_fields_settings()["baptized_by"]; ?>
                <li><strong><?php echo esc_html( $field["name"] ) ?></strong> - <?php echo esc_html( $field["description"] ?? "" ) ?></li>
                <?php $field = Disciple_Tools_Contact_Post_Type::instance()->get_custom_fields_settings()["baptized"]; ?>
                <li><strong><?php echo esc_html( $field["name"] ) ?></strong> - <?php echo esc_html( $field["description"] ?? "" ) ?></li>
                <?php $field = Disciple_Tools_Contact_Post_Type::instance()->get_custom_fields_settings()["coached_by"]; ?>
                <li><strong><?php echo esc_html( $field["name"] ) ?></strong> - <?php echo esc_html( $field["description"] ?? "" ) ?></li>
                <?php $field = Disciple_Tools_Contact_Post_Type::instance()->get_custom_fields_settings()["coaching"]; ?>
                <li><strong><?php echo esc_html( $field["name"] ) ?></strong> - <?php echo esc_html( $field["description"] ?? "" ) ?></li>
        </ul>
    </div>

    <!--  Groups (depreciated) -->
    <div class="help-section" id="groups-help-text" style="display: none">
        <h3><?php esc_html_e( "Groups", 'disciple_tools' ) ?></h3>
        <p><?php esc_html_e( "Here you can connect this contact with a group by either searching for a group or creating a new group that they will then be added to.", 'disciple_tools' ) ?></p>
    </div>

    <!--  Other tile  -->
    <div class="help-section" id="other-tile-help-text" style="display: none">
        <h3><?php esc_html_e( "Other", 'disciple_tools' ) ?></h3>
        <p><?php esc_html_e( "In this tile, you can assign tags to help connect this contact with other contacts.", 'disciple_tools' ) ?></p>
        <h4><?php esc_html_e( "Tags", 'disciple_tools' ) ?></h4>
        <p><?php esc_html_e( "Using  tags can help group contacts associated with noteworthy characteristics. e.g. business owner, sports lover. The contacts can be filtered using these tags.", 'disciple_tools' ) ?></p>
    </div>

    <!--  Tags (depreciated) -->
    <div class="help-section" id="tags-help-text" style="display: none">
        <h3><?php esc_html_e( "Tags", 'disciple_tools' ) ?></h3>
        <p><?php esc_html_e( "Here you can assign tags to help connect this contact with other contacts. Using  tags can help group contacts associated with noteworthy characteristics. e.g. business owner, sports lover", 'disciple_tools' ) ?></p>
    </div>

    <!--  Four Fields Tile -->
    <div class="help-section" id="four-fields-help-text" style="display: none">
        <h3><?php esc_html_e( "Four Fields", 'disciple_tools' ) ?></h3>
        <p><?php esc_html_e( "There are 5 squares in the Four Fields diagram. Starting in the top left quadrant and going clockwise and the fifth being in the middle, they stand for:", 'disciple_tools' ) ?></p>

          <ul>
                    <?php $field = Disciple_Tools_Groups_Post_Type::instance()->get_custom_fields_settings()["four_fields_unbelievers"]; ?>
              <li><strong><?php echo esc_html( $field["name"] ) ?></strong> - <?php echo esc_html( $field["description"] ?? "" ) ?></li>
                    <?php $field = Disciple_Tools_Groups_Post_Type::instance()->get_custom_fields_settings()["four_fields_believers"]; ?>
              <li><strong><?php echo esc_html( $field["name"] ) ?></strong> - <?php echo esc_html( $field["description"] ?? "" ) ?></li>
                    <?php $field = Disciple_Tools_Groups_Post_Type::instance()->get_custom_fields_settings()["four_fields_accountable"]; ?>
              <li><strong><?php echo esc_html( $field["name"] ) ?></strong> - <?php echo esc_html( $field["description"] ?? "" ) ?></li>
                    <?php $field = Disciple_Tools_Groups_Post_Type::instance()->get_custom_fields_settings()["four_fields_church_commitment"]; ?>
              <li><strong><?php echo esc_html( $field["name"] ) ?></strong> - <?php echo esc_html( $field["description"] ?? "" ) ?></li>
                    <?php $field = Disciple_Tools_Groups_Post_Type::instance()->get_custom_fields_settings()["four_fields_multiplying"]; ?>
              <li><strong><?php echo esc_html( $field["name"] ) ?></strong> - <?php echo esc_html( $field["description"] ?? "" ) ?></li>
          </ul>
    </div>

    <!-- Source  -->
    <div class="help-section" id="source-help-text" style="display: none">
        <?php $field = Disciple_Tools_Contact_Post_Type::instance()->get_custom_fields_settings()["sources"]; ?>
        <h3><?php echo esc_html( $field["name"] ) ?></h3>
        <p><?php echo esc_html( $field["description"] ) ?></p>
        <ul>
            <?php foreach ( $field["default"] as $option ): ?>
                <li><strong><?php echo esc_html( $option["label"] ) ?></strong> - <?php echo esc_html( $option["description"] ?? "" ) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>

    <!--  Location  -->
    <div class="help-section" id="location-help-text" style="display: none">
        <h3><?php esc_html_e( "Location", 'disciple_tools' ) ?></h3>
        <p><?php esc_html_e( "This is the general location where the contact lives - and not their address. Clicking this will bring up a list of locations to choose from. Select 'Regions of Focus' or 'All Locations' to adjust the list of locations you can choose from.", 'disciple_tools' ) ?></p>
        <p><?php esc_html_e( "This locations list was previously created in the backend by an Admin Role user. You cannot add a new location here. If the location you are looking for is not in the list, then an Admin Role user will first have to add the new location in the backend admin area of this website instance.", 'disciple_tools' ) ?></p>
    </div>

    <!--  Initial Comment  -->
    <div class="help-section" id="initial-comment-help-text" style="display: none">
        <h3><?php esc_html_e( "Initial Comment", 'disciple_tools' ) ?></h3>
        <p><?php esc_html_e( "This is for any other info you need to put about the contact/group. It will be saved under the Activity and Comments Tile in the Contact's/Group's Record.", 'disciple_tools' ) ?></p>
    </div>

    <!--  Filters Tile - left side -->
    <div class="help-section" id="filters-help-text" style="display: none">
        <h3><?php esc_html_e( "Default and Custom Filters", 'disciple_tools' ) ?></h3>
        <p><?php esc_html_e( "Filters are a way to search for lists in either the Contacts page or groups in the Groups page. There are several default filters included by default. The filter options are located on the left of the page under the heading Filters. If the default filters do not fit your needs you can create your own custom filter.", 'disciple_tools' ) ?></p>
    </div>

    <!--  Contacts List Tile -->
    <div class="help-section" id="contacts-list-help-text" style="display: none">
        <h3><?php esc_html_e( "Contacts List Tile", 'disciple_tools' ) ?></h3>
        <p><?php esc_html_e( "Your list of contacts will show up here. Whenever you filter contacts, the list will also be changed in this section too. You can sort your contacts by newest, oldest, most recently modified, and least recently modified. If you have a long list of contacts they will not all load at once, so clicking the 'Load more contacts' button at the bottom of the list will allow you to load more. (This button will always be there even if you do not have any more contacts to load.)", 'disciple_tools' ) ?></p>
    </div>

    <!--  Groups List Tile -->
    <div class="help-section" id="groups-list-help-text" style="display: none">
        <h3><?php esc_html_e( "Groups List Tile", 'disciple_tools' ) ?></h3>
        <p><?php esc_html_e( "Your list of groups will show up here. Whenever you filter groups, the list will also be changed in this section too. You can sort your groups by newest, oldest, most recently modified, and least recently modified. If you have a long list of groups they will not all load at once, so clicking the 'Load more groups' button at the bottom of the list will allow you to load more. (This button will always be there even if you do not have any more groups to load.)", 'disciple_tools' ) ?></p>
    </div>

    <!--  Contacts list switch -->
    <div class="help-section" id="contacts-switch-help-text" style="display: none">
        <h3><?php esc_html_e( "Closed contacts switch", 'disciple_tools' ) ?></h3>
        <p><?php esc_html_e( "Use this toggle switch to either show or not show closed contacts in the list.", 'disciple_tools' ) ?></p>
    </div>

    <!--  Groups list switch -->
    <div class="help-section" id="groups-switch-help-text" style="display: none">
        <h3><?php esc_html_e( "Inactive Groups switch", 'disciple_tools' ) ?></h3>
        <p><?php esc_html_e( "Use this toggle switch to either show or not show inactive groups in the list.", 'disciple_tools' ) ?></p>
    </div>

    <!--  New Contact -->
    <div class="help-section" id="new-contact-help-text" style="display: none">
        <h3><?php esc_html_e( "Create new contact", 'disciple_tools' ) ?></h3>
        <p><?php esc_html_e( "After writing the name of the contact, complete as many other fields as you can, before clicking 'Save and continue editing'. On the next screen you can edit and add more information about this new contact has just been just created.", 'disciple_tools' ) ?></p>
    </div>

    <!--  Contact name -->
    <div class="help-section" id="contact-name-help-text" style="display: none">
        <h3><?php esc_html_e( "Name of contact", 'disciple_tools' ) ?></h3>
        <p><?php esc_html_e( "The name of the contact is searchable and can be used to help you filter your contacts in the Contacts List page.", 'disciple_tools' ) ?></p>
    </div>

    <!--  Contact phone -->
    <div class="help-section" id="phone-help-text" style="display: none">
        <h3><?php esc_html_e( "Phone", 'disciple_tools' ) ?></h3>
        <p><?php esc_html_e( "A contact phone number for this contact. The system uses this phone number to check for duplicate contacts.", 'disciple_tools' ) ?></p>
    </div>

    <!--  Contact email -->
    <div class="help-section" id="email-help-text" style="display: none">
        <h3><?php esc_html_e( "Email", 'disciple_tools' ) ?></h3>
        <p><?php esc_html_e( "A contact email address for this contact.", 'disciple_tools' ) ?></p>
    </div>

    <!--  New Group -->
    <div class="help-section" id="new-group-help-text" style="display: none">
        <h3><?php esc_html_e( "Create new group", 'disciple_tools' ) ?></h3>
        <p><?php esc_html_e( "After writing the name of the group, complete as many other fields as you can, before clicking 'Save and continue editing'. On the next screen you can edit and add more information about this new group has just been created.", 'disciple_tools' ) ?></p>
    </div>

    <!--  Group name -->
    <div class="help-section" id="group-name-help-text" style="display: none">
        <h3><?php esc_html_e( "Name of group", 'disciple_tools' ) ?></h3>
        <p><?php esc_html_e( "The name of the group is searchable and can be used to help you filter your contacts in the Groups List page.", 'disciple_tools' ) ?></p>
    </div>

    <!--  Your Profile -->
    <div class="help-section" id="profile-help-text" style="display: none">
        <h3><?php esc_html_e( "Your Profile", 'disciple_tools' ) ?></h3>
        <p><?php esc_html_e( "In this area you can see your user profile.", 'disciple_tools' ) ?></p>
        <p><?php esc_html_e( "You are not required to fill out any of these profile fields. They are optional to meet your team’s needs.", 'disciple_tools' ) ?></p>
        <p><?php esc_html_e( "By clicking 'Edit', you will be able adjust things like the language that this system operates in.", 'disciple_tools' ) ?></p>
    </div>

    <!--  Your Locations  -->
    <div class="help-section" id="locations-help-text" style="display: none">
        <h3><?php esc_html_e( "Locations", 'disciple_tools' ) ?></h3>
        <p><?php esc_html_e( "This is the general location where you live - and not your address. Clicking the 'Add' button will bring up a list of locations to choose from.", 'disciple_tools' ) ?></p>
        <p><?php esc_html_e( "This locations list was previously created in the backend by an Admin Role user. You cannot add a new location here. If the location you are looking for is not in the list, then an Admin Role user will first have to add the new location in the backend admin area of this website instance.", 'disciple_tools' ) ?></p>
    </div>

    <!--  Your notifications -->
    <div class="help-section" id="notifications-help-text" style="display: none">
        <h3><?php esc_html_e( "Your notifications", 'disciple_tools' ) ?></h3>
        <p><?php esc_html_e( "You will receive web notifications and email notifications based on your notification preferences. To change your preference, click the toggle buttons.", 'disciple_tools' ) ?></p>
        <ul>
        <li><?php esc_html_e( "Notifications Turned On: The toggle will appear blue.", 'disciple_tools' ) ?></li>
        <li><?php esc_html_e( "Notifications Turned Off: The toggle will appear grey.", 'disciple_tools' ) ?></li>
        </ul>
        <p><?php esc_html_e( "Some of the types of notifications cannot be adjusted because they are required by the system.", 'disciple_tools' ) ?></p>
        <p><?php esc_html_e( "Adjust whether you want to see notifications about new comments, contact information changes, Contact Milestones and Group Health metrics, and whether to you will receive a notification for any update that happens in the system.", 'disciple_tools' ) ?></p>
    </div>

    <!--  Your availability -->
    <div class="help-section" id="availability-help-text" style="display: none">
        <h3><?php esc_html_e( "Your availability", 'disciple_tools' ) ?></h3>
        <p><?php esc_html_e( "This availability feature allows you the user to set a start date and end date to schedule dates when you will be unavailable e.g. you are traveling or on vacation, so the Dispatcher will know your availability to receive new contacts.", 'disciple_tools' ) ?></p>
    </div>

    <!--   Notifications page -->
    <div class="help-section" id="notifications-template-help-text" style="display: none">
        <h3><?php esc_html_e( "Notifications Page", 'disciple_tools' ) ?></h3>
        <p><?php esc_html_e( "This Notifications Page is where you can read updates to users and groups. It displays notifications about activity on your records.", 'disciple_tools' ) ?></p>
        <h4><?php esc_html_e( "All / Unread", 'disciple_tools' ) ?></h4>
        <p><?php esc_html_e( "Click the 'All' button to show the full list of all of your notifications.", 'disciple_tools' ) ?></p>
        <p><?php esc_html_e( "Click the 'Unread' button to show the list of all of your unread notifications.", 'disciple_tools' ) ?></p>
        <h4><?php esc_html_e( "Mark All as Read", 'disciple_tools' ) ?></h4>
        <p><?php esc_html_e( "If you don't want to click each filled in circle on the right side of each row to indicate the notification has been read, then click the 'Mark All as Read' link at the top to quickly adjust all the messages that they have all been read.", 'disciple_tools' ) ?></p>
        <h4><?php esc_html_e( "Settings", 'disciple_tools' ) ?></h4>
        <p><?php esc_html_e( "Click the 'Settings' link to go to the notifications settings area to adjust whether you want to see notifications about new comments, contact information changes, Contact Milestones and Group Health metrics, and whether to you will receive a notification for any update that happens in the system.", 'disciple_tools' ) ?></p>
    </div>

    <!--   Duplicates view page -->
    <div class="help-section" id="duplicates-template-help-text" style="display: none">
        <h3><?php esc_html_e( "Duplicate Contact page", 'disciple_tools' ) ?></h3>
        <p><?php esc_html_e( "This Duplicate Contact Page is where you can review, decline or merge contacts that have been picked up by the system (checking against their name, email and phone) as possibly being duplicates of another already existing contact. ", 'disciple_tools' ) ?></p>
    </div>

    <!-- close -->
    <div class="grid-x grid-padding-x">
        <div class="cell small-4">
            <h5>&nbsp;</h5>
            <button class="button small" data-close aria-label="Close reveal" type="button">
                <?php esc_html_e( 'Close', 'disciple_tools' )?>
            </button>
        </div>
        <div class="cell small-8">
            <!-- documentation link -->
            <div class="help-more">
                <h5><?php esc_html_e( "Need more help?", 'disciple_tools' ) ?></h5>
                <a class="button small" id="docslink" href="https://disciple-tools.readthedocs.io/en/latest/index.html"><?php esc_html_e( 'Read the documentation', 'disciple_tools' )?></a>
            </div>
        </div>
        <button class="close-button" data-close aria-label="Close modal" type="button">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
</div>
