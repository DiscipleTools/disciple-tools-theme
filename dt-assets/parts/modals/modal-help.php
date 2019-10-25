<div class="reveal" id="help-modal" data-reveal>


    <!--    Contact Status  -->
    <div class="help-section" id="overall-status-help-text" style="display: none">
        <?php
        $field = Disciple_Tools_Contact_Post_Type::instance()->get_custom_fields_settings()["overall_status"]; ?>
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
        <?php
        $field = Disciple_Tools_Contact_Post_Type::instance()->get_custom_fields_settings()["assigned_to"]; ?>
        <h3><?php echo esc_html( $field["name"] )?></h3>
        <p><?php echo esc_html( $field["description"] ) ?></p>
        <?php if ( current_user_can( "view_any_contacts" ) ) : ?>
            <p><strong><?php esc_html_e( "Icons legend", 'disciple_tools' ) ?></strong></p>
            <ul style="list-style-type:none">
            <?php $workload_status_options = dt_get_site_custom_lists()["user_workload_status"] ?? [];
            foreach ( $workload_status_options as $option_key =>$option_val ): ?>
                <li><span style="background-color: <?php echo esc_html( $option_val["color"] ) ?>; height:10px; padding: 0 5px; border-radius: 2px">&nbsp;</span> <?php echo esc_html( $option_val["label"] ) ?></li>
            <?php endforeach ?>
                <li><img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/broken.svg' )?>" /> 2: <?php esc_html_e( "2 contacts need an update", 'disciple_tools' ) ?> </li>
            </ul>
        <?php endif; ?>
    </div>

    <!--    Subassigned to  -->
    <div class="help-section" id="subassigned-to-help-text" style="display: none">
        <h3 class="lead"><?php esc_html_e( "Sub-assigned to", 'disciple_tools' ) ?></h3>
        <p><?php esc_html_e( "This is someone working alongside of the main person assigned to the contact. You may find that you are partnering with others in your discipleship relationships. Only one person can be assigned while multiple people can be sub-assigned.", 'disciple_tools' ) ?></p>
        <?php
        $field = Disciple_Tools_Contact_Post_Type::instance()->get_custom_fields_settings()["subassigned_to"]; ?>
        <h3><?php echo esc_html( $field["name"] ) ?></h3>
        <p><?php echo esc_html( $field["description"] ) ?></p>
    </div>

    <!--    Quick Actions   -->
    <div class="help-section" id="quick-action-help-text" style="display: none">
        <h3 class="lead"><?php esc_html_e( "Quick action buttons", 'disciple_tools' ) ?></h3>
        <?php esc_html_e( '<p>These quick action buttons are here to aid you in updating the contact record.
        They track how many times each one has been used.</p>', 'disciple_tools' ) ?>
        <p><?php esc_html_e( 'They also update the "Seeker Path" below. For example,
            If you click the "No Answer" button 4 times, a number will be added to "No Answer" meaning that you have
            attempted to call the contact 4 times, but they did not answer.
            This will also change the "Seeker Path" below to "Contact Attempted".', 'disciple_tools' ) ?>
        </p>
    </div>

    <!-- Contact Progress -->
    <div class="help-section" id="contact-progress-help-text" style="display: none">
        <h3 class="lead"><?php esc_html_e( "Contact Progress", 'disciple_tools' ) ?></h3>
        <p><?php esc_html_e( "Here you can track the progress of a contact's faith journey.", 'disciple_tools' ) ?></p>
    </div>

    <!-- Seeker Path -->
    <div class="help-section" id="seeker-path-help-text" style="display: none">
        <h3 class="lead"><?php esc_html_e( "Seeker Path", 'disciple_tools' ) ?></h3>
        <p><?php esc_html_e( "This is where you set the status of your progression with the contact.", 'disciple_tools' ) ?></p>
    </div>

    <!-- Faith Milestones -->
    <div class="help-section" id="faith-milestones-help-text" style="display: none">
        <h3 class="lead"><?php esc_html_e( "Faith Milestones", 'disciple_tools' ) ?></h3>
        <p><?php esc_html_e( "This is where you set which milestones the contact has reached in their faith journey.", 'disciple_tools' ) ?></p>
    </div>

    <!-- Baptism Date -->
    <div class="help-section" id="baptism-date-help-text" style="display: none">
        <h3 class="lead"><?php esc_html_e( "Baptism Date", 'disciple_tools' ) ?></h3>
        <p><?php esc_html_e( "This is where you set the date of when this contact was baptised.", 'disciple_tools' ) ?></p>
    </div>

    <!--  Health Metrics  -->
    <div class="help-section" id="health-metrics-help-text" style="display: none">
        <h3 class="lead"><?php esc_html_e( "Health Metrics", 'disciple_tools' ) ?></h3>
        <p><?php esc_html_e( "Here you can track the progress of a group/church.", 'disciple_tools' ) ?></p>
        <p><?php esc_html_e( "If the group has committed to be a church, click the \"Covenant\" button to make the dotted line circle solid.", 'disciple_tools' ) ?></p>
        <p><?php esc_html_e( "If the group/church regularly practices any of the following elements then click each element to add them inside the circle.", 'disciple_tools' ) ?></p>
    </div>

    <!--  Group type  -->
    <div class="help-section" id="group-type-help-text" style="display: none">
        <h3 class="lead"><?php esc_html_e( "Group type", 'disciple_tools' ) ?></h3>
        <p><?php esc_html_e( "Here you can select whether the group is a pre-group, group, church or team.", 'disciple_tools' ) ?></p>
        <p><?php esc_html_e( "We define a pre-group as having x people. We define a group as having x people.", 'disciple_tools' ) ?></p>
        <p><?php esc_html_e( "We define a church as having 3 or more believers.", 'disciple_tools' ) ?></p>
    </div>

    <!--  Group Status  -->
    <div class="help-section" id="group-status-help-text" style="display: none">
        <h3 class="lead"><?php esc_html_e( "Group Status", 'disciple_tools' ) ?></h3>
        <p><?php esc_html_e( "This is where you set the current status of the group.", 'disciple_tools' ) ?></p>
        <ul>
            <li>
                <?php esc_html_e( "Active - the group is actively meeting and is continually being updated.", 'disciple_tools' ) ?>
            </li>
            <li>
                <?php esc_html_e( "Inactive - The group is no longer meeting at this time.", 'disciple_tools' ) ?>
            </li>
        </ul>
    </div>

    <!--  Group Parents, Peers and Children  -->
    <div class="help-section" id="group-connections-help-text" style="display: none">
        <h3 class="lead"><?php esc_html_e( "Group Connections. Parent, Peer and Child Groups", 'disciple_tools' ) ?></h3>
        <p><?php esc_html_e( "Here you can select whether the group is a pre-group, group, church or team.", 'disciple_tools' ) ?></p>
        <h4><?php esc_html_e( "Group Type:", 'disciple_tools' ) ?></h4>
        <ul>
            <li><?php esc_html_e( "Pre-group - a predominantly a non-believers group", 'disciple_tools' ) ?></li>
            <li><?php esc_html_e( "Group - having 3 or more believers but not identifying as church", 'disciple_tools' ) ?></li>
            <li><?php esc_html_e( "Church - having 3 or more believers and identifying as church", 'disciple_tools' ) ?></li>
            <li><?php esc_html_e( "Team - a special group that is not meeting for or trying to become church).", 'disciple_tools' ) ?></li>
        </ul>
        <h4><?php esc_html_e( "Group Connections. Parent, Peer and Child Groups", 'disciple_tools' ) ?></h4>
        <ul>
            <li><?php esc_html_e( "Parent Group: The group that founded this group.", 'disciple_tools' ) ?></li>
            <li><?php esc_html_e( "Peer Group: Related groups that aren’t parent/child in relationship. It might indicate groups that collaborate, are about to merge, recently split, etc.", 'disciple_tools' ) ?></li>
            <li><?php esc_html_e( "Child Groups: A group that has been birthed out of this group.", 'disciple_tools' ) ?></li>
        </ul>
    </div>

    <!--  Groups  -->
    <div class="help-section" id="groups-help-text" style="display: none">
        <h3 class="lead"><?php esc_html_e( "Groups", 'disciple_tools' ) ?></h3>
        <p><?php esc_html_e( "Here you can connect this contact with a group by either searching for a group or creating a new group that they will then be added to.", 'disciple_tools' ) ?></p>
    </div>

    <!--  Connections  -->
    <div class="help-section" id="connections-help-text" style="display: none">
        <h3 class="lead"><?php esc_html_e( "Connections", 'disciple_tools' ) ?></h3>
        <p><?php esc_html_e( "Here you can make connections with this contact with group/s  or other people in this system.", 'disciple_tools' ) ?></p>
    </div>


    <!--  Four Fields  -->
    <div class="help-section" id="four-fields-help-text" style="display: none">
        <h3 class="lead"><?php esc_html_e( "Four Fields", 'disciple_tools' ) ?></h3>
        <ul>
            <li><?php esc_html_e( "Unbeliever field: Unbelievers in this group.", 'disciple_tools' ) ?></li>
            <li><?php esc_html_e( "Believer field: Believers in this group.", 'disciple_tools' ) ?></li>
            <li><?php esc_html_e( "Accountable field.", 'disciple_tools' ) ?></li>
            <li><?php esc_html_e( "Church field: Is this a church?", 'disciple_tools' ) ?></li>
            <li><?php esc_html_e( "Multiply field: How many members are multiplying?", 'disciple_tools' ) ?></li>
        </ul>
    </div>

    <!-- close -->
    <div class="grid-x">
        <button class="button" data-close aria-label="Close reveal" type="button">
            <?php esc_html_e( 'Close', 'disciple_tools' )?>
        </button>
        <button class="close-button" data-close aria-label="Close modal" type="button">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
</div>
