<?php
declare( strict_types=1 );

( function () {


    if (isset( $_POST['unsure_all'] )) {
        $id = (int) $_POST['id'];
        Disciple_Tools_Contacts::unsure_all( $id );
        header( "location: " . site_url( '/contacts/' . get_the_ID() ) );
    }
    if (isset( $_POST['dismiss_all'] )) {
        $id = (int) $_POST['id'];
        Disciple_Tools_Contacts::dismiss_all( $id );
        header( "location: " . site_url( '/contacts/' . get_the_ID() ) );
    }
    if (isset( $_POST['merge-submit'] )){
        $contact_id = (int) $_POST["currentid"];
        $dupe_id = (int) $_POST['duplicateId'];
        $phones =$_POST['phone'] ?? array();
        $emails =$_POST['email'] ?? array();
        $addresses = $_POST['address'] ?? array();
        $master = $_POST['master-record'];

        $masterId = ( $master === 'contact1' ) ? $contact_id : $dupe_id;
        $nonMasterId = ( $masterId === $contact_id ) ? $dupe_id : $contact_id;
        $contact = Disciple_Tools_Contacts::get_contact( $masterId, true );
        $nonMaster = Disciple_Tools_Contacts::get_contact( $nonMasterId, true );

        $current = array(
            'contact_phone' => array(),
            'contact_email' => array(),
            'contact_address' => array()
        );

        foreach ($contact['contact_phone'] ?? array() as $arrPhone) {
            $current['contact_phone'][$arrPhone['key']] = $arrPhone['value'];
        }
        foreach ($contact['contact_email'] ?? array() as $arrEmail) {
            $current['contact_email'][$arrEmail['key']] = $arrEmail['value'];
        }
        foreach ($contact['contact_address'] ?? array() as $arrAddress) {
            $current['contact_address'][$arrAddress['key']] = $arrAddress['value'];
        }
        foreach ($contact['contact_facebook'] ?? array() as $arrFacebook) {
            $current['contact_facebook'][$arrFacebook['key']] = $arrFacebook['value'];
        }

        $update = array(
            'contact_phone' => array( 'values' => array() ),
            'contact_email' => array( 'values' => array() ),
            'contact_address' => array( 'values' => array() ),
            'contact_facebook' => array( 'values' => array() )
        );

        $ignore_keys = array();

        foreach ($phones as $phone) {
            $index = array_search( $phone, $current['contact_phone'] );
            if ($index !== false) { $ignore_keys[] = $index;
                continue; }
            array_push( $update['contact_phone']['values'], [ 'value' => $phone ] );
        }
        foreach ($emails as $email) {
            $index = array_search( $email, $current['contact_email'] );
            if ($index !== false) { $ignore_keys[] = $index;
                continue; }
            array_push( $update['contact_email']['values'], [ 'value' => $email ] );
        }
        foreach ($addresses as $address) {
            $index = array_search( $address, $current['contact_address'] );
            if ($index !== false) { $ignore_keys[] = $index;
                continue; }
            array_push( $update['contact_address']['values'], [ 'value' => $address ] );
        }

        foreach ($nonMaster['contact_facebook'] ?? array() as $arrFacebook) {
            $index = array_search( $arrFacebook['value'], $current['contact_facebook'] ?? array() );
            if ($index !== false) { $ignore_keys[] = $index;
                continue; }
            array_push($update['contact_facebook']['values'], array(
                'value' => $arrFacebook['value']
            ));
        }

        $deleteFields = array();
        if ($update['contact_phone']['values']) { $deleteFields[] = 'contact_phone'; }
        if ($update['contact_email']['values']) { $deleteFields[] = 'contact_email'; }
        if ($update['contact_address']['values']) { $deleteFields[] = 'contact_address'; }
        if ($update['contact_facebook']['values']) { $deleteFields[] = 'contact_facebook'; }

        if ( !empty( $deleteFields )) {
            Disciple_Tools_Contacts::remove_fields( $masterId, $deleteFields, $ignore_keys );
        }

        $closeId = ( $masterId === $contact_id ) ? $dupe_id : $contact_id;

        Disciple_Tools_Contacts::update_contact( $masterId, $update, true );
        Disciple_Tools_Contacts::merge_milestones( $masterId, $nonMasterId );
        Disciple_Tools_Contacts::merge_p2p( $masterId, $nonMasterId );
        ( new Disciple_Tools_Contacts() )->recheck_duplicates( $masterId );
        ( new Disciple_Tools_Contacts() )->dismiss_duplicate( $masterId, $nonMasterId );
        Disciple_Tools_Contacts::close_account( $closeId );
        //$contact=Disciple_Tools_Contacts::update_contact( $contact_id, $update, true);
        header( "location: " . site_url( '/contacts/' .get_the_ID() ) );

            // $update = [
            //     "overall_status" => 'active',
            //     "accepted" => 'yes'
            // ];
            // self::update_contact( $contact_id, $update, true );
            // return [ "overall_status" => self::$contact_fields["overall_status"]["default"]['active'] ];
        exit;
    }

    if ( !Disciple_Tools_Contacts::can_view( 'contacts', get_the_ID() )) {
        get_template_part( "403" );
        die();
    }
    Disciple_Tools_Notifications::process_new_notifications( get_the_ID() ); // removes new notifications for this post
    $contact = Disciple_Tools_Contacts::get_contact( get_the_ID(), true );
    $contact_fields = Disciple_Tools_Contacts::get_contact_fields();

    get_header(); ?>

    <?php
    $current_user_id = get_current_user_id();
    $following = Disciple_Tools_Posts::get_users_following_post( "contacts", get_the_ID() );
    dt_print_details_bar(
        true,
        true,
        current_user_can( "assign_any_contacts" ),
        isset( $contact["requires_update"] ) && $contact["requires_update"]["key"] === "yes",
        in_array( $current_user_id, $following ),
        isset( $contact["assigned_to"]["id"] ) ? $contact["assigned_to"]["id"] == $current_user_id : false
    ); ?>

    <div id="errors"></div>

    <div id="content">
        <span id="contact-id" style="display: none"><?php echo get_the_ID()?></span>
        <span id="post-id" style="display: none"><?php echo get_the_ID()?></span>
        <span id="post-type" style="display: none">contact</span>

        <div id="inner-content" class="grid-x grid-margin-x grid-margin-y">

            <div class="small-12 cell bordered-box grid-x grid-margin-x">
                <div class="cell small-12 medium-4">
                    <i class="fi-torso large"></i>
                    <span class="item-details-header title" ><?php the_title_attribute(); ?></span>
                </div>
                <div class="cell small-12 medium-2">
                    <div class="section-subheader">
                        <?php esc_html_e( "Status", 'disciple_tools' ) ?>
                        <button class="help-button" data-section="overall-status-help-text">
                            <img class="help-icon" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/help.svg' ) ?>"/>
                        </button>
                    </div>
                    <?php
                    $active_color = "#366184";
                    $current_key = $contact["overall_status"]["key"] ?? "";
                    if ( isset( $contact_fields["overall_status"]["colors"][ $current_key ] )){
                        $active_color = $contact_fields["overall_status"]["colors"][$current_key];
                    }
                    ?>
                    <select id="overall_status" class="select-field" style="width:fit-content; margin-bottom:0px; background-color: <?php echo esc_html( $active_color ) ?>">
                    <?php foreach ($contact_fields["overall_status"]["default"] as $key => $value){
                        if ( $contact["overall_status"]["key"] === $key ) {
                            ?>
                            <option value="<?php echo esc_html( $key ) ?>" selected><?php echo esc_html( $value ); ?></option>
                        <?php } else { ?>
                            <option value="<?php echo esc_html( $key ) ?>"><?php echo esc_html( $value ); ?></option>
                        <?php } ?>
                    <?php } ?>
                    </select>
                    <p>
                        <span id="reason">
                            <?php
                            $hide_edit_button = false;
                            if ( $contact["overall_status"]["key"] === "paused" &&
                                 isset( $contact["reason_paused"] )){
                                echo '(' . esc_html( $contact["reason_paused"]["label"] ) . ')';
                            } else if ( $contact["overall_status"]["key"] === "closed" &&
                                        isset( $contact["reason_closed"] )){
                                echo '(' . esc_html( $contact["reason_closed"]["label"] ) . ')';
                            } else if ( $contact["overall_status"]["key"] === "unassignable" &&
                                        isset( $contact["reason_unassignable"] )){
                                echo '(' . esc_html( $contact["reason_unassignable"]["label"] ) . ')';
                            } else {
                                $hide_edit_button = true;
                            }
                            ?>
                        </span>
                        <button id="edit-reason" <?php if ( $hide_edit_button ) : ?> style="display: none"<?php endif; ?> ><i class="fi-pencil"></i></button>
                    </p>
                </div>

                <div class="cell small-12 medium-3">
                    <!-- Assigned To -->
                    <div class="section-subheader">
                        <img src="<?php echo esc_url( get_template_directory_uri() ) . '/dt-assets/images/assigned-to.svg' ?>">
                        <?php esc_html_e( 'Assigned to', 'disciple_tools' )?>
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
                <div class="cell small-12 medium-3">
                    <div class="section-subheader">
                        <img src="<?php echo esc_url( get_template_directory_uri() ) . '/dt-assets/images/subassigned.svg' ?>">
                        <?php esc_html_e( 'Sub-assigned to', 'disciple_tools' )?>
                    </div>
                    <div class="subassigned details">
                        <var id="subassigned-result-container" class="result-container subassigned-result-container"></var>
                        <div id="subassigned_t" name="form-subassigned">
                            <div class="typeahead__container">
                                <div class="typeahead__field">
                                    <span class="typeahead__query">
                                        <input class="js-typeahead-subassigned input-height"
                                               name="subassigned[query]" placeholder="<?php esc_html_e( "Search multipliers and contacts", 'disciple_tools' ) ?>"
                                               autocomplete="off">
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
                <section class="hide-for-large small-12 cell">
                <div class="bordered-box">
                    <?php get_template_part( 'dt-assets/parts/contact', 'quick-buttons' ); ?>

                    <div style="text-align: center">
                        <a class="button small" href="#comment-activity-section" style="margin-bottom: 0">
                            <?php esc_html_e( 'View Comments', 'disciple_tools' ) ?>
                        </a>
                    </div>
                </div>
            </section>
            <main id="main" class="xlarge-7 large-7 medium-12 small-12 cell" role="main" style="padding:0">
              <div class="cell grid-y grid-margin-y" style="display: block">
                <?php
                $duplicate_post_meta = get_post_meta( get_the_Id(), 'duplicate_data' );
                $duplicates = false;
                foreach ($duplicate_post_meta[0] ?? [] as $key => $array) {
                    if ($key === 'override') { continue; }
                    if ( !empty( $array )) {
                        $duplicates = true;
                    }
                }
                if ($duplicates){
                    ?>
                <section id="duplicates" class="small-12 grid-y grid-margin-y cell">
                <!--                    <div class="bordered-box last-typeahead-in-section">-->
                    <div class="bordered-box" style="background-color:#ff9800; border:.2rem solid #30c2ff; text-align:center;">
                        <h3 class="section-header" style="color:white;"><?php esc_html_e( "This contact has possible duplicates.", 'disciple_tools' ) ?></h3>
                      <?php get_template_part( 'dt-assets/parts/merge', 'details' ); ?>
                        <button type="button" id="merge-dupe-modal" data-open="merge-dupe-modal" class="button">
                          <?php esc_html_e( "Go to duplicates", 'disciple_tools' ) ?>
                        </button>
                    </div>
                </section>
                <?php }
                ?>
                    <section id="contact-details" class="small-12 grid-y grid-margin-y cell ">
                        <?php get_template_part( 'dt-assets/parts/contact', 'details' ); ?>
                    </section>
                    <div class="cell small-12">
                        <div class="grid-x grid-margin-x grid-margin-y grid">
                            <section id="relationships" class="xlarge-6 large-12 medium-6 cell grid-item">
            <!--                    <div class="bordered-box last-typeahead-in-section">-->
                                <div class="bordered-box">
                                    <h3 class="section-header"><?php esc_html_e( "Connections", 'disciple_tools' ) ?></h3>
                                    <div class="section-subheader"><?php esc_html_e( "Groups", 'disciple_tools' ) ?></div>
                                    <var id="groups-result-container" class="result-container"></var>
                                    <div id="groups_t" name="form-groups" class="scrollable-typeahead typeahead-margin-when-active">
                                        <div class="typeahead__container">
                                            <div class="typeahead__field">
                                                <span class="typeahead__query">
                                                    <input class="js-typeahead-groups input-height"
                                                           name="groups[query]" placeholder="<?php esc_html_e( "Search groups", 'disciple_tools' ) ?>"
                                                           autocomplete="off">
                                                </span>
                                                <span class="typeahead__button">
                                                    <button type="button" data-open="create-group-modal" class="create-new-group typeahead__image_button input-height">
                                                        <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/add-group.svg' ) ?>"/>
                                                    </button>
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <?php
                                    $connections = [
                                        "baptized_by" => esc_html__( "Baptized By", 'disciple_tools' ),
                                        "baptized" => esc_html__( "Baptized", 'disciple_tools' ),
                                        "coached_by" => esc_html__( "Coached By", 'disciple_tools' ),
                                        "coaching" => esc_html__( "Coaching", 'disciple_tools' )
                                    ];
                                    foreach ( $connections as $connection => $connection_label ) {
                                        ?>
                                        <div class="section-subheader"><?php echo esc_html( $connection_label ) ?></div>
                                        <var id="<?php echo esc_html( $connection ) ?>-result-container" class="result-container"></var>
                                        <div id="<?php echo esc_html( $connection ) ?>_t" name="form-<?php echo esc_html( $connection ) ?>" class="scrollable-typeahead typeahead-margin-when-active">
                                            <div class="typeahead__container">
                                                <div class="typeahead__field">
                                                    <span class="typeahead__query">
                                                        <input class="js-typeahead-<?php echo esc_html( $connection ) ?>"
                                                               name="<?php echo esc_html( $connection ) ?>[query]" placeholder="<?php esc_html_e( "Search multipliers and contacts", 'disciple_tools' ) ?>"
                                                               autocomplete="off">
                                                    </span>
            <!--                                        <span class="typeahead__button">-->
            <!--                                            <button>-->
            <!--                                                <i class="typeahead__search-icon"></i>-->
            <!--                                            </button>-->
            <!--                                        </span>-->
                                                </div>
                                            </div>
                                        </div>
                                        <?php
                                    }
                                    ?>
                                </div>
                            </section>

                            <section id="faith" class="xlarge-6 large-12 medium-6 cell grid-item">
                                <div class="bordered-box">
                                    <label class="section-header"><?php esc_html_e( 'Progress', 'disciple_tools' )?>
            <!--                            <button class="help-button float-right" data-section="contact-progress-help-text">-->
            <!--                                <img class="help-icon" src="--><?php //echo esc_html( get_template_directory_uri() . '/dt-assets/images/help.svg' ) ?><!--"/>-->
            <!--                            </button>-->
                                    </label>
                                    <div class="section-subheader">
                                        <?php esc_html_e( 'Seeker Path', 'disciple_tools' )?>
                                        <button class="help-button" data-section="seeker-path-help-text">
                                            <img class="help-icon" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/help.svg' ) ?>"/>
                                        </button>
                                    </div>

                                    <select class="select-field" id="seeker_path" style="margin-bottom: 0px">
                                    <?php

                                    foreach ($contact_fields["seeker_path"]["default"] as $key => $value){
                                        if ( $contact["seeker_path"]["key"] === $key ) {
                                            ?>
                                            <option value="<?php echo esc_html( $key ) ?>" selected><?php echo esc_html( $value ); ?></option>
                                        <?php } else { ?>
                                            <option value="<?php echo esc_html( $key ) ?>"><?php echo esc_html( $value ); ?></option>
                                        <?php }
                                    }
                                    $keys = array_keys( $contact_fields["seeker_path"]["default"] );
                                    $path_index = array_search( $contact["seeker_path"]["key"], $keys ) ?? 0;
                                    $percentage = $path_index / ( sizeof( $keys ) -1 ) *100
                                    ?>
                                    </select>
                                    <div class="progress" role="progressbar" tabindex="0" aria-valuenow="<?php echo 4 ?>" aria-valuemin="0" aria-valuetext="50 percent" aria-valuemax="100">
                                        <div id="seeker-progress" class="progress-meter" style="width: <?php echo esc_html( $percentage ) ?>%"></div>
                                    </div>

                                    <div class="section-subheader">
                                        <?php esc_html_e( 'Faith Milestones', 'disciple_tools' )?>
                                        <button class="help-button" data-section="faith-milestones-help-text">
                                            <img class="help-icon" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/help.svg' ) ?>"/>
                                        </button>
                                    </div>
                                    <div class="small button-group" style="display: inline-block">

                                        <?php foreach ( $contact_fields as $field => $val ): ?>
                                            <?php
                                            if (strpos( $field, "milestone_" ) === 0) {
                                                $class = ( isset( $contact[ $field ] ) && $contact[ $field ]['key'] === 'yes' ) ?
                                                    "selected-select-button" : "empty-select-button";
                                                ?>
                                                <button onclick="save_seeker_milestones( <?php echo esc_html( get_the_ID() ) ?> , '<?php echo esc_html( $field ) ?>')"
                                                        id="<?php echo esc_html( $field ) ?>"
                                                        class="<?php echo esc_html( $class ) ?> select-button button ">
                                                    <?php echo esc_html( $contact_fields[ $field ]["name"] ) ?>
                                                </button>
                                            <?php }?>
                                        <?php endforeach; ?>
                                    </div>

                                    <div class="baptism_date">
                                        <div class="section-subheader"><?php esc_html_e( 'Baptism Date', 'disciple_tools' )?></div>
                                        <div class="baptism_date">
                                            <input type="text" data-date-format='yy-mm-dd' value="<?php echo esc_html( $contact["baptism_date"] ?? '' )?>" id="baptism-date-picker">
                                        </div>
                                    </div>

                                    <!-- custom sections -->
                                    <?php $custom_sections = dt_get_option( 'dt_site_custom_lists' );
                                    $custom_sections = $custom_sections["custom_dropdown_contact_options"];
                                    foreach ( $custom_sections as $key => $value ) :
                                        ?>
                                            <div class="custom_progress">
                                                <!-- drop down section -->
                                                <div class="section-subheader">
                                                    <?php echo esc_html( $value["label"] ); ?>
                                                </div>
                                                <!-- the id is what makes the blue progress bar go up -->
                                                <select class="select-field" id=<?php echo esc_html( "custom_dropdown_contact_" . $key ); ?> style="margin-bottom: 0px">
                                                <?php
                                                //this section fills the drop down with the data
                                                foreach ($value as $s_key => $s_value){
                                                    if ($s_key != "label") {
                                                        if ( isset( $contact["custom_dropdown_contact_" . $key]["key"] ) ) {
                                                            if ( $contact["custom_dropdown_contact_" . $key]["key"] === $s_value ) {
                                                                ?>
                                                                <option value="<?php echo esc_html( $s_value ) ?>" selected><?php echo esc_html( $s_value ); ?></option>
                                                            <?php }
                                                            else {
                                                                ?>
                                                                <option value="<?php echo esc_html( $s_value ) ?>"><?php echo esc_html( $s_value ); ?></option>
                                                            <?php }
                                                        } else { ?>
                                                                <option value="<?php echo esc_html( $s_value ) ?>"><?php echo esc_html( $s_value ); ?></option>
                                                            <?php }
                                                    }
                                                }
                                                ?>
                                                </select>
                                            </div>
                                    <?php endforeach; ?>

                                </div>
                            </section>

                            <section id="other" class="xlarge-6 large-12 medium-6 cell grid-item">
                                <div class="bordered-box">
                                    <label class="section-header"><?php esc_html_e( 'Other', 'disciple_tools' )?></label>

                                    <div class="section-subheader">
                                        <?php echo esc_html( $contact_fields["tags"]["name"] ) ?>
                                    </div>
                                    <div class="tags">
                                        <var id="tags-result-container" class="result-container"></var>
                                        <div id="tags_t" name="form-tags" class="scrollable-typeahead typeahead-margin-when-active">
                                            <div class="typeahead__container">
                                                <div class="typeahead__field">
                                                    <span class="typeahead__query">
                                                        <input class="js-typeahead-tags input-height"
                                                               name="tags[query]" placeholder="<?php esc_html_e( "Search Tags", 'disciple_tools' ) ?>"
                                                               autocomplete="off">
                                                    </span>
                                                    <span class="typeahead__button">
                                                        <button type="button" data-open="create-tag-modal" class="create-new-tag typeahead__image_button input-height">
                                                            <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/tag-add.svg' ) ?>"/>
                                                        </button>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </section>

                            <?php
                            $sections = apply_filters( 'dt_details_additional_section_ids', [], "contacts" );

                            foreach ( $sections as $section ){
                                ?>
                                <section id="<?php echo esc_html( $section ) ?>" class="xlarge-6 large-12 medium-6 cell grid-item">
                                    <div class="bordered-box">
                                        <?php
                                        do_action( "dt_details_additional_section", $section )
                                        ?>
                                    </div>
                                </section>
                                <?php
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </main> <!-- end #main -->

            <aside class="auto cell grid-x">
                <section class="bordered-box comment-activity-section cell"
                         id="comment-activity-section">
                    <?php get_template_part( 'dt-assets/parts/loop', 'activity-comment' ); ?>
                </section>
            </aside>

        </div> <!-- end #inner-content -->

    </div> <!-- end #content -->


    <?php get_template_part( 'dt-assets/parts/modals/modal', 'share' ); ?>
    <?php get_template_part( 'dt-assets/parts/modals/modal', 'new-group' ); ?>
    <?php get_template_part( 'dt-assets/parts/modals/modal', 'revert' ); ?>


    <div class="reveal" id="closed-contact-modal" data-reveal>
        <h1><?php esc_html_e( 'Close Contact', 'disciple_tools' )?></h1>
        <p class="lead"><?php esc_html_e( 'Why do you want to close this contact?', 'disciple_tools' )?></p>

        <select id="reason-closed-options">
            <?php
            foreach ( $contact_fields["reason_closed"]["default"] as $reason_key => $reason_label ) {
                $selected = ( $reason_key === ( $contact["reason_closed"]["key"] ?? "" ) ) ? "selected" : "";
                ?>
                <option value="<?php echo esc_attr( $reason_key )?>" <?php echo esc_html( $selected ) ?>> <?php echo esc_html( $reason_label )?></option>
                <?php
            }
            ?>
        </select>
        <button class="button button-cancel clear" data-close aria-label="Close reveal" type="button">
            <?php esc_html_e( 'Cancel', 'disciple_tools' )?>
        </button>
        <button class="button loader confirm-reason-button" type="button" id="confirm-close" data-field="closed">
            <?php esc_html_e( 'Confirm', 'disciple_tools' )?>
        </button>
        <button class="close-button" data-close aria-label="Close modal" type="button">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>

    <div class="reveal" id="paused-contact-modal" data-reveal>
        <h1><?php esc_html_e( 'Pause Contact', 'disciple_tools' )?></h1>
        <p class="lead"><?php esc_html_e( 'Why do you want to pause this contact?', 'disciple_tools' )?></p>

        <select id="reason-paused-options">
            <?php
            foreach ( $contact_fields["reason_paused"]["default"] as $reason_key => $reason_label ) {
                ?>
                <option value="<?php echo esc_attr( $reason_key )?>"
                    <?php if ( ( $contact["reason_paused"]["key"] ?? "" ) === $reason_key ){echo "selected";} ?>>
                    <?php echo esc_html( $reason_label )?>
                </option>
                <?php
            }
            ?>
        </select>
        <button class="button button-cancel clear" data-close aria-label="Close reveal" type="button">
            <?php esc_html_e( 'Cancel', 'disciple_tools' )?>
        </button>
        <button class="button loader confirm-reason-button" type="button" id="confirm-pause" data-field="paused">
            <?php esc_html_e( 'Confirm', 'disciple_tools' )?>
        </button>
        <button class="close-button" data-close aria-label="Close modal" type="button">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    <div class="reveal" id="unassignable-contact-modal" data-reveal>
        <h1><?php esc_html_e( 'Contact Unassignable', 'disciple_tools' )?></h1>
        <p class="lead"><?php esc_html_e( 'How is this contact unassignable', 'disciple_tools' )?></p>

        <select id="reason-unassignable-options">
            <?php
            foreach ( $contact_fields["reason_unassignable"]["default"] as $reason_key => $reason_label ) {
                ?>
                <option value="<?php echo esc_attr( $reason_key )?>"
                    <?php if ( ( $contact["unassignable_paused"]["key"] ?? "" ) === $reason_key ){echo "selected";} ?>>
                    <?php echo esc_html( $reason_label )?>
                </option>
                <?php
            }
            ?>
        </select>
        <button class="button button-cancel clear" data-close aria-label="Close reveal" type="button">
            <?php esc_html_e( 'Cancel', 'disciple_tools' )?>
        </button>
        <button class="button loader confirm-reason-button" type="button" id="confirm-unassignable" data-field="unassignable">
            <?php esc_html_e( 'Confirm', 'disciple_tools' )?>
        </button>
        <button class="close-button" data-close aria-label="Close modal" type="button">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>

    <div class="reveal" id="edit-reason-modal" data-reveal>


        <div class="medium-6 cell reason-field">
            <?php
            $status = $contact['overall_status']['key'] ?? '';
            $has_status = isset( $contact_fields["reason_$status"]['name'] );
            ?>
            <div class="section-subheader">
                <?php
                if ( $has_status ) {
                    echo esc_html( $contact_fields["reason_$status"]['name'] );
                }
                ?>
            </div>
            <?php
            $status_style = !$has_status ? 'display:none;' : '';
            $reason_field = $has_status ? "reason_$status" : '';
            ?>
            <select class="status-reason" style="<?php echo esc_html( $status_style ); ?>" data-field="<?php echo esc_html( $reason_field ) ?>">
                <?php
                if ( $has_status ) {
                    foreach ( $contact_fields["reason_$status"]['default'] as $reason_key => $reason_label ) { ?>
                        <option value="<?php echo esc_attr( $reason_key ) ?>"
                            <?php
                            $selected = $contact["reason_$status"]['key'] ?? '' === $reason_key ? 'selected' : '';
                            echo esc_html( $selected ); ?>>
                            <?php echo esc_html( $reason_label, 'disciple_tools' ); ?>
                        </option>
                        <?php
                    }
                }
                ?>
            </select>
        </div>
    </div>

    <div class="reveal" id="create-tag-modal" data-reveal data-reset-on-close>

        <p class="lead"><?php esc_html_e( 'Create Tag', 'disciple_tools' )?></p>

        <form class="js-create-tag">
            <label for="title">
                <?php esc_html_e( "Tag", "disciple_tools" ); ?>
            </label>
            <input name="title" id="new-tag" type="text" placeholder="<?php esc_html_e( "tag", "disciple_tools" ); ?>" required aria-describedby="name-help-text">
            <p class="help-text" id="name-help-text"><?php esc_html_e( "This is required", "disciple_tools" ); ?></p>
        </form>

        <div class="grid-x">
            <button class="button button-cancel clear" data-close aria-label="Close reveal" type="button">
                <?php esc_html_e( 'Cancel', 'disciple_tools' )?>
            </button>
            <button class="button" data-close type="button" id="create-tag-return">
                <?php esc_html_e( 'Create and apply tag', 'disciple_tools' ); ?>
            </button>
            <button class="close-button" data-close aria-label="Close modal" type="button">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    </div>

    <?php
} )();

if (isset( $_POST['merge'] )) {
    echo "<script type='text/javascript'>$(document).ready(function() { $('#merge-dupe-modal').click(); });</script>";
}

get_footer();
