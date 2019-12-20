<?php
declare(strict_types=1);

if ( ! current_user_can( 'access_groups' ) ) {
    wp_safe_redirect( '/settings' );
}

( function() {

    if ( !Disciple_Tools_Groups::can_view( 'groups', get_the_ID() )){
        get_template_part( "403" );
        die();
    }

    Disciple_Tools_Notifications::process_new_notifications( get_the_ID() ); // removes new notifications for this post
    $following = DT_Posts::get_users_following_post( "groups", get_the_ID() );
    $group = Disciple_Tools_Groups::get_group( get_the_ID(), true, true );
    $group_fields = Disciple_Tools_Groups_Post_Type::instance()->get_custom_fields_settings();
    $group_preferences = dt_get_option( 'group_preferences' );
    $current_user_id = get_current_user_id();
    get_header();?>

    <?php
    dt_print_details_bar(
        true,
        true,
        true,
        isset( $group["requires_update"] ) && $group["requires_update"] === true,
        in_array( $current_user_id, $following ),
        isset( $group["assigned_to"]["id"] ) ? $group["assigned_to"]["id"] == $current_user_id : false
    ); ?>

<!--<div id="errors"> </div>-->
<!-- <div><a class="button small" id="backlink" href="../" style="margin:.9rem 0 0 1.3em"><?php esc_html_e( 'Back to Groups List', 'disciple_tools' )?></a></div> -->
<div id="content" class="single-groups">
    <span id="group-id" style="display: none"><?php echo get_the_ID()?></span>
    <span id="post-id" style="display: none"><?php echo get_the_ID()?></span>
    <span id="post-type" style="display: none">group</span>

    <div id="inner-content" class="grid-x grid-margin-x grid-margin-y">

        <main id="main" class="large-7 medium-12 small-12 cell" role="main" style="padding:0">
            <div class="cell grid-y grid-margin-y" style="display: block">

                <!-- Requires update block -->
                <section class="cell small-12 update-needed-notification"
                         style="display: <?php echo esc_html( ( isset( $group['requires_update'] ) && $group['requires_update'] === true ) ? "block" : "none" ) ?> ">
                    <div class="bordered-box detail-notification-box" style="background-color:#F43636">
                        <h4><img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/alert-circle-exc.svg' ) ?>"/><?php esc_html_e( 'This group needs an update', 'disciple_tools' ) ?>.</h4>
                        <p><?php esc_html_e( 'Please provide an update by posting a comment.', 'disciple_tools' )?></p>
                    </div>
                </section>
                <section id="contact-details" class="cell small-12 grid-margin-y">
                    <div class="cell">
                        <?php get_template_part( 'dt-assets/parts/group', 'details' ); ?>
                    </div>
                </section>
                <div class="cell small-12">
                    <div class="grid-x grid-margin-x grid-margin-y grid">

                        <!-- MEMBERS TILE -->
                        <section id="relationships" class="xlarge-6 large-12 medium-6 cell grid-item" >
                            <div class="bordered-box" id="members-tile">
                                <h3 class="section-header"><?php esc_html_e( 'Members', 'disciple_tools' )?>
                                    <button class="help-button" data-section="members-help-text">
                                        <img class="help-icon" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/help.svg' ) ?>"/>
                                    </button>
                                    <button class="section-chevron chevron_down">
                                        <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/chevron_down.svg' ) ?>"/>
                                    </button>
                                    <button class="section-chevron chevron_up">
                                        <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/chevron_up.svg' ) ?>"/>
                                    </button>
                                </h3>
                                <div class="section-body"><!-- start collapse -->
                                <div class="section-subheader"><?php esc_html_e( "Member Count", 'disciple_tools' ) ?></div>
                                <input id="member_count"
                                       class="number-input" type="number" min="0"
                                       placeholder="<?php echo esc_html( sizeof( $group["members"] ) )?>"
                                       value="<?php echo esc_html( $group["member_count"] ?? "" ) ?>">


                                <div class="section-subheader members-header" style="padding-top: 10px">
                                    <div style="padding-bottom: 5px; margin-right:10px; display: inline-block">
                                        <?php esc_html_e( "Member List", 'disciple_tools' ) ?>
                                    </div>
                                    <button type="button" class="create-new-contact" style="height: 36px;">
                                        <?php echo esc_html_x( 'Create', 'button', 'disciple_tools' )?>
                                        <img style="height: 14px; width: 14px" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/small-add.svg' ) ?>"/>
                                    </button>
                                    <button type="button"
                                            class="add-new-member">
                                        <?php echo esc_html_x( 'Select', 'button', 'disciple_tools' )?>
                                        <img style="height: 16px; width: 16px" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/add-group.svg' ) ?>"/>
                                    </button>
                                </div>
                                <div class="members-section">
                                &nbsp;<!--<p class="small"><?php esc_html_e( "No group members yet. Create or Select new members to add them to this group.", 'disciple_tools' ) ?></p>-->
                                    <div class="member-list">

                                    </div>
                                </div>
                            </div><!-- end collapse --></div>
                        </section>

                        <!-- GROUPS TILE -->
                        <section id="groups" class="xlarge-6 large-12 medium-6 cell grid-item">
                            <div class="bordered-box" id="groups-tile">
                                <h3 class="section-header"><?php esc_html_e( "Groups", 'disciple_tools' ) ?>
                                    <button class="help-button" data-section="group-connections-help-text">
                                        <img class="help-icon" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/help.svg' ) ?>"/>
                                    </button>
                                    <button class="section-chevron chevron_down">
                                        <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/chevron_down.svg' ) ?>"/>
                                    </button>
                                    <button class="section-chevron chevron_up">
                                        <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/chevron_up.svg' ) ?>"/>
                                    </button>
                                </h3>
                                <div class="section-body"><!-- start collapse -->
                                <div class="section-subheader">
                                    <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/group-type.svg' ) ?>"/>
                                    <?php echo esc_html( $group_fields["group_type"]["name"] )?>
                                </div>
                                <select class="select-field" id="group_type">
                                    <?php
                                    foreach ($group_fields["group_type"]["default"] as $key => $option){
                                        $value = $option["label"] ?? "";
                                        if ( $group["group_type"]["key"] === $key ) {
                                            ?>
                                            <option value="<?php echo esc_html( $key ) ?>" selected><?php echo esc_html( $value ); ?></option>
                                        <?php } else { ?>
                                            <option value="<?php echo esc_html( $key ) ?>"><?php echo esc_html( $value ); ?></option>
                                        <?php }
                                    }
                                    ?>
                                </select>
                                <div class="section-subheader">
                                <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/group-parent.svg' ) ?>"/>
                                <?php esc_html_e( "Parent Group", 'disciple_tools' ) ?></div>
                                <var id="parent_groups-result-container" class="result-container"></var>
                                <div id="parent_groups_t" name="form-groups" class="scrollable-typeahead typeahead-margin-when-active">
                                    <div class="typeahead__container">
                                        <div class="typeahead__field">
                                        <span class="typeahead__query">
                                            <input class="js-typeahead-parent_groups input-height"
                                                    name="groups[query]" placeholder="<?php echo esc_html_x( "Search Groups", 'input field placeholder', 'disciple_tools' ) ?>"
                                                    autocomplete="off">
                                        </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="section-subheader">
                                <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/group-peer.svg' ) ?>"/>
                                <?php esc_html_e( "Peer Group", 'disciple_tools' ) ?></div>
                                <var id="peer_groups-result-container" class="result-container"></var>
                                <div id="peer_groups_t" name="form-groups" class="scrollable-typeahead typeahead-margin-when-active">
                                    <div class="typeahead__container">
                                        <div class="typeahead__field">
                                        <span class="typeahead__query">
                                            <input class="js-typeahead-peer_groups input-height"
                                                   name="groups[query]" placeholder="<?php echo esc_html_x( "Search Groups", 'input field placeholder', 'disciple_tools' ) ?>"
                                                   autocomplete="off">
                                        </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="section-subheader">
                                <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/group-child.svg' ) ?>"/>
                                <?php esc_html_e( "Child Group", 'disciple_tools' ) ?></div>
                                <var id="child_groups-result-container" class="result-container"></var>
                                <div id="child_groups_t" name="form-child_groups" class="scrollable-typeahead typeahead-margin-when-active">
                                    <div class="typeahead__container">
                                        <div class="typeahead__field">
                                        <span class="typeahead__query">
                                            <input class="js-typeahead-child_groups input-height"
                                                    name="groups[query]" placeholder="<?php echo esc_html_x( "Search Groups", 'input field placeholder', 'disciple_tools' ) ?>"
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
                            </div><!-- end collapse --></div>
                        </section>

                        <!-- Health Metrics-->
                        <?php if ( ! empty( $group_preferences['church_metrics'] ) ) : ?>
                            <section id="health-metrics" class="xlarge-6 large-12 medium-6 cell grid-item">
                                <div class="bordered-box js-progress-bordered-box half-opacity" id="health-tile">

                                    <h3 class="section-header"><?php echo esc_html( $group_fields["health_metrics"]["name"] )?>
                                        <button class="help-button" data-section="health-metrics-help-text">
                                            <img class="help-icon" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/help.svg' ) ?>"/>
                                        </button>
                                        <button class="section-chevron chevron_down">
                                            <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/chevron_down.svg' ) ?>"/>
                                        </button>
                                        <button class="section-chevron chevron_up">
                                            <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/chevron_up.svg' ) ?>"/>
                                        </button>
                                    </h3>
                                    <div class="section-body"><!-- start collapse -->

                                    <div class="grid-x">
                                        <div style="margin-right:auto; margin-left:auto;min-height:302px">
                                            <object id="church-svg-wrapper" type="image/svg+xml" data="<?php echo esc_attr( get_template_directory_uri() . '/dt-assets/images/groups/church-wheel.svg', 'disciple_tools' ); ?>"></object>
                                        </div>
                                    </div>
                                    <div style="display:flex;flex-wrap:wrap;margin-top:10px">
                                        <?php foreach ( $group_fields["health_metrics"]["default"] as $key => $option ) : ?>
                                            <div class="group-progress-button-wrapper">
                                                <button  class="group-progress-button" id="<?php echo esc_html( $key ) ?>">
                                                    <img src="<?php echo esc_html( $option["image"] ?? "" ) ?>">
                                                </button>
                                                <p><?php echo esc_html( $option["label"] ) ?> </p>
                                            </div>
                                        <?php endforeach; ?>

                                    </div>

                                </div><!-- end collapse --></div>
                            </section>
                        <?php endif; ?>


                        <!-- Four Fields -->
                        <?php if ( ! empty( $group_preferences['four_fields'] ) ) : ?>
                            <section id="four-fields" class="xlarge-6 large-12 medium-6 cell grid-item">
                                <div class="bordered-box js-progress-bordered-box" id="four-fields-tile">

                                    <h3 class="section-header"><?php esc_html_e( 'Four Fields', 'disciple_tools' ) ?>
                                        <button class="help-button" data-section="four-fields-help-text">
                                            <img class="help-icon" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/help.svg' ) ?>"/>
                                        </button>
                                        <button class="section-chevron chevron_down">
                                            <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/chevron_down.svg' ) ?>"/>
                                        </button>
                                        <button class="section-chevron chevron_up">
                                            <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/chevron_up.svg' ) ?>"/>
                                        </button>
                                    </h3>
                                    <div class="section-body"><!-- start collapse -->

                                    <div class="grid-x" id="four-fields-inputs">
                                        <div style="width:100%; height:375px;background-image:url('<?php echo esc_attr( get_template_directory_uri() . '/dt-assets/images/four-fields.svg', 'disciple_tools' ); ?>');background-repeat:no-repeat;"></div>
                                    </div>
                                </div><!-- end collapse --></div>
                            </section>
                        <?php endif; ?>


                        <?php
                        //get sections added by plugins
                        $sections = apply_filters( 'dt_details_additional_section_ids', [], "groups" );
                        //get custom sections
                        $custom_tiles = dt_get_option( "dt_custom_tiles" );
                        foreach ( $custom_tiles["groups"] as $tile_key => $tile_options ){
                            if ( !in_array( $tile_key, $sections ) ){
                                $sections[] = $tile_key;
                            }
                            //remove section if hidden
                            if ( isset( $tile_options["hidden"] ) && $tile_options["hidden"] == true ){
                                $index = array_search( $tile_key, $sections );
                                if ( $index !== false) {
                                    unset( $sections[ $index ] );
                                }
                            }
                        }
                        foreach ( $sections as $section ){
                            ?>
                            <section id="<?php echo esc_html( $section ) ?>" class="xlarge-6 large-12 medium-6 cell grid-item">
                                <div class="bordered-box" id="<?php echo esc_html( $section ); ?>-tile">
                                    <?php
                                    // let the plugin add section content
                                    do_action( "dt_details_additional_section", $section, 'groups' );
                                    //setup tile label if see by customizations
                                    if ( isset( $custom_tiles["groups"][$section]["label"] ) ){ ?>
                                        <label class="section-header">
                                            <?php echo esc_html( $custom_tiles["groups"][$section]["label"] )?>
                                        </label>
                                    <?php }
                                    //setup the order of the tile fields
                                    $order = $custom_tiles["groups"][$section]["order"] ?? [];
                                    foreach ( $group_fields as $key => $option ){
                                        if ( isset( $option["tile"] ) && $option["tile"] === $section ){
                                            if ( !in_array( $key, $order )){
                                                $order[] = $key;
                                            }
                                        }
                                    }
                                    foreach ( $order as $field_key ) {
                                        if ( !isset( $group_fields[$field_key] ) ){
                                            continue;
                                        }
                                        $field = $group_fields[$field_key];
                                        if ( isset( $field["tile"] ) && $field["tile"] === $section ){ ?>
                                            <div class="section-subheader">
                                                <?php echo esc_html( $field["name"] )?>
                                            </div>
                                            <?php
                                            /**
                                             * Key Select
                                             */
                                            if ( $field["type"] === "key_select" ) : ?>
                                                <select class="select-field" id="<?php echo esc_html( $field_key ); ?>">
                                                    <?php foreach ($field["default"] as $option_key => $option_value):
                                                        $selected = isset( $group[$field_key]["key"] ) && $group[$field_key]["key"] === $option_key; ?>
                                                        <option value="<?php echo esc_html( $option_key )?>" <?php echo esc_html( $selected ? "selected" : "" )?>>
                                                            <?php echo esc_html( $option_value["label"] ) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            <?php elseif ( $field["type"] === "multi_select" ) : ?>
                                                <div class="small button-group" style="display: inline-block">
                                                    <?php foreach ( $group_fields[$field_key]["default"] as $option_key => $option_value ): ?>
                                                        <?php
                                                        $class = ( in_array( $option_key, $group[$field_key] ?? [] ) ) ?
                                                            "selected-select-button" : "empty-select-button"; ?>
                                                        <button id="<?php echo esc_html( $option_key ) ?>" data-field-key="<?php echo esc_html( $field_key ) ?>"
                                                                class="dt_multi_select <?php echo esc_html( $class ) ?> select-button button ">
                                                            <?php echo esc_html( $group_fields[$field_key]["default"][$option_key]["label"] ) ?>
                                                        </button>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php elseif ( $field["type"] === "text" ) :?>
                                                <input id="<?php echo esc_html( $field_key ) ?>" type="text"
                                                       class="text-input"
                                                       value="<?php echo esc_html( $group[$field_key] ?? "" ) ?>"/>
                                            <?php elseif ( $field["type"] === "date" ) :?>
                                                <input type="text" class="date-picker dt_date_picker"
                                                       id="<?php echo esc_html( $field_key ) ?>"
                                                       value="<?php echo esc_html( $group[$field_key]["formatted"] ?? '' )?>">
                                            <?php endif;
                                        }
                                    }
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
            <section class="comment-activity-section cell"
                     id="comment-activity-section">
                <?php get_template_part( 'dt-assets/parts/loop', 'activity-comment' ); ?>
            </section>
        </aside>

    </div> <!-- end #inner-content -->

</div> <!-- end #content -->


<!--    Modals-->
    <?php get_template_part( 'dt-assets/parts/modals/modal', 'share' ); ?>
    <?php get_template_part( 'dt-assets/parts/modals/modal', 'new-group' ); ?>
    <?php get_template_part( 'dt-assets/parts/modals/modal', 'new-contact' ); ?>

    <div class="reveal" id="add-new-group-member" data-reveal style="min-height:500px">
        <h3><?php echo esc_html_x( "Add members from existing contacts", 'Add members modal', 'disciple_tools' )?></h3>
        <p><?php echo esc_html_x( "In the 'Members List' field, type the name of an existing contact to add them to this group.", 'Add members modal', 'disciple_tools' )?></p>
        <div class="section-subheader"><?php esc_html_e( "Members List", 'disciple_tools' ) ?></div>
        <div class="members">
            <var id="members-result-container" class="result-container"></var>
            <div id="members_t" name="form-members" class="scrollable-typeahead typeahead-margin-when-active">
                <div class="typeahead__container">
                    <div class="typeahead__field">
                        <span class="typeahead__query">
                            <input class="js-typeahead-members"
                                   name="members[query]" placeholder="<?php echo esc_html_x( "Search Contacts", 'input field placeholder', 'disciple_tools' ) ?>"
                                   autocomplete="off">
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="grid-x pin-to-bottom">
            <div class="cell">
                <hr size="1px">
                <span style="float:right; bottom: 0;">
                    <button class="button" data-close aria-label="Close reveal" type="button">
                        <?php echo esc_html_x( 'Close', 'button', 'disciple_tools' )?>
                    </button>
                </span>
            </div>
        </div>
        <button class="close-button" data-close aria-label="Close modal" type="button">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>



    <?php
} )();

get_footer();
