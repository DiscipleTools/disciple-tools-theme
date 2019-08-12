<div class="reveal" id="help-modal" data-reveal>


    <!--    Contact Status-->
    <div class="help-section" id="overall-status-help-text" style="display: none">
        <h3><?php esc_html_e( "Contact Status", 'disciple_tools' ) ?></h3>
        <p><?php esc_html_e( "This is where you set the current status of the contact.", 'disciple_tools' ) ?></p>
        <ul>
            <?php
            $status_options = Disciple_Tools_Contact_Post_Type::instance()->get_custom_fields_settings()["overall_status"]["default"];
            foreach ( $status_options as $option ): ?>
                <li><strong><?php echo esc_html( $option["label"] ) ?></strong> - <?php echo esc_html( $option["description"] ?? "" ) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>


    <div class="help-section" id="quick-action-help-text" style="display: none">
        <h3 class="lead">Quick action buttons</h3>
        <p>These quick action buttons are here to aid you in updating the contact record.
        They track how many times each one has been used.</p>
        <p>They also update the "Seeker Path" below. For example,
            If you click the "No Answer" button 4 times, a number will be added to "No Answer" meaning that you have
            attempted to call the contact 4 times, but they didn't answer.
            This will also change the "Seeker Path" below to "Contact Attempted".
        </p>
    </div>
    <div class="help-section" id="contact-progress-help-text" style="display: none">
        <h3 class="lead"><?php esc_html_e( "Contact Progress", 'disciple_tools' ) ?></h3>
        <p>Here you can track the progress of a contact's faith journey.</p>
    </div>
    <div class="help-section" id="seeker-path-help-text" style="display: none">
        <h3 class="lead"><?php esc_html_e( "Seeker Path", 'disciple_tools' ) ?></h3>
        <p>This is where you set the status of your progression with the contact.</p>
    </div>
    <div class="help-section" id="faith-milestones-help-text" style="display: none">
        <h3 class="lead"><?php esc_html_e( "Faith Milestones", 'disciple_tools' ) ?></h3>
        <p>This is where you set which milestones the contact has reached in their faith journey.</p>
    </div>

    <!--  Health Metrics  -->
    <div class="help-section" id="health-metrics-help-text" style="display: none">
        <h3 class="lead"><?php esc_html_e( "Health Metrics", 'disciple_tools' ) ?></h3>
        <p> Here you can track the progress of a group/church.</p>
        <p>If the group has committed to be a church, click the "Covenant" button to make the dotted line circle solid.</p>
        <p>If the group/church regularly practices any of the following elements then click
            each element to add them inside the circle.</p>
    </div>

    <!--  Group type  -->
    <div class="help-section" id="group-type-help-text" style="display: none">
        <h3 class="lead"><?php esc_html_e( "Group type", 'disciple_tools' ) ?></h3>
        <p>Here you can select whether the group is a pre-group, group or church.</p>
        <p>We define a pre-group as having x people. We define a group as having x people.</p>
        <p>We define a church as having 3 or more believers.</p>
    </div>

    <!--  Group Status  -->
    <div class="help-section" id="group-status-help-text" style="display: none">
        <h3 class="lead"><?php esc_html_e( "Group Status", 'disciple_tools' ) ?></h3>
        <p>This is where you set the current status of the group. </p>
        <ul>
            <li>
                Active - the group is actively meeting and is continually being updated.
            </li>
            <li>
                Inactive - The group is no longer meeting at this time.
            </li>
        </ul>
    </div>

    <!--  Group Parents and Children  -->
    <div class="help-section" id="group-connections-help-text" style="display: none">
        <h3 class="lead"><?php esc_html_e( "Group Connections. Parent and Child Groups", 'disciple_tools' ) ?></h3>
        <p>If this group has multiplied from another group, you can add that group here (Parent Group).</p>
        <p>If this group has multiplied into another group, you can add that here (Child Groups).</p>
    </div>

    <!--  Four Fields  -->
    <div class="help-section" id="four-fields-help-text" style="display: none">
        <h3 class="lead"><?php esc_html_e( "Four Fields", 'disciple_tools' ) ?></h3>
        <ul>
            <li>Unbeliever field: Unbelievers in this group.</li>
            <li>Believer field: Believers in this group.</li>
            <li>Accountable field.</li>
            <li>Church field: Is this a church?</li>
            <li>Multiply field: How many members of multiplying?</li>
        </ul>
    </div>

    <div class="grid-x">
        <button class="button" data-close aria-label="Close reveal" type="button">
            <?php esc_html_e( 'Close', 'disciple_tools' )?>
        </button>
        <button class="close-button" data-close aria-label="Close modal" type="button">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
</div>
