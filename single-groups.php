<?php
declare(strict_types=1);

( function() {

    if ( !Disciple_Tools_Groups::can_view( 'groups', get_the_ID() )){
        get_template_part( "403" );
        die();
    }

    Disciple_Tools_Notifications::process_new_notifications( get_the_ID() ); // removes new notifications for this post
    $following = Disciple_Tools_Posts::get_users_following_post( "contacts", get_the_ID() );
    $group = Disciple_Tools_Groups::get_group( get_the_ID(), true );
    $group_fields = Disciple_Tools_Groups_Post_Type::instance()->get_custom_fields_settings();
    $current_user_id = get_current_user_id();
    get_header();?>

<?php
dt_print_details_bar(
    true,
    true,
    false,
    true,
    in_array( $current_user_id, $following )
); ?>

<div id="errors"> </div>

<div id="content">
    <span id="group-id" style="display: none"><?php echo get_the_ID()?></span>
    <span id="post-id" style="display: none"><?php echo get_the_ID()?></span>
    <span id="post-type" style="display: none">group</span>

    <div id="inner-content" class="grid-x grid-margin-x grid-margin-y">

        <main id="main" class="large-7 medium-12 small-12 cell" role="main" style="padding:0">
            <div class="cell grid-y grid-margin-y" style="display: block">
                <section id="contact-details" class="cell small-12  grid-margin-y">
                    <div class="cell">
                        <?php get_template_part( 'dt-assets/parts/group', 'details' ); ?>
                    </div>
                </section>
                <div class="cell small-12">
                    <div class="grid-x grid-margin-x grid-margin-y grid">
                        <section id="relationships" class="xlarge-6 large-12 medium-6 cell grid-item" >
                            <div class="bordered-box">
                                <span class="section-header"><?php esc_html_e( 'Members', 'disciple_tools' )?></span>
                                <div class="members">
                                    <var id="members-result-container" class="result-container"></var>
                                    <div id="members_t" name="form-members" class="scrollable-typeahead">
                                        <div class="typeahead__container">
                                            <div class="typeahead__field">
                                                <span class="typeahead__query">
                                                    <input class="js-typeahead-members"
                                                           name="members[query]" placeholder="Search Members"
                                                           autocomplete="off">
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section id="faith" class="xlarge-6 large-12 medium-6 cell grid-item">
                            <div class="bordered-box js-progress-bordered-box half-opacity">


                                <label class="section-header" ><?php esc_html_e( 'Progress', 'disciple_tools' )?></label>
                                <div class="section-subheader">
                                    <?php esc_html_e( 'Group Type', 'disciple_tools' )?>
<!--                                    <button class="help-button" data-section="group-type-help-text">-->
<!--                                        <img class="help-icon" src="--><?php //echo esc_html( get_template_directory_uri() . '/dt-assets/images/help.svg' ) ?><!--"/>-->
<!--                                    </button>-->
                                </div>

                                <select class="select-field" id="group_type">
                                    <?php

                                    foreach ($group_fields["group_type"]["default"] as $key => $value){
                                        if ( $group["group_type"]["key"] === $key ) {
                                            ?>
                                            <option value="<?php echo esc_html( $key ) ?>" selected><?php echo esc_html( $value ); ?></option>
                                        <?php } else { ?>
                                            <option value="<?php echo esc_html( $key ) ?>"><?php echo esc_html( $value ); ?></option>
                                        <?php }
                                    }
                                    ?>
                                </select>

                                <div class="section-subheader ">
                                    <?php esc_html_e( 'Health Metrics', 'disciple_tools' )?>
                                    <button class="help-button" data-section="health-metrics-help-text">
                                        <img class="help-icon" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/help.svg' ) ?>"/>
                                    </button>
                                </div>
                                <div style="display:flex;flex-wrap:wrap;margin-top:10px">
                                    <div class="group-progress-button-wrapper">
                                        <button  class="group-progress-button" id="church_fellowship">
                                            <img src="<?php echo esc_attr( get_template_directory_uri() . '/dt-assets/images/groups/heart.svg', 'disciple_tools' ); ?>">
                                        </button>
                                        <p><?php esc_html_e( 'Fellowship', 'disciple_tools' )?> </p>
                                    </div>
                                    <div class="group-progress-button-wrapper">
                                        <button class="group-progress-button" id="church_giving">
                                            <img src="<?php echo esc_attr( get_template_directory_uri() . '/dt-assets/images/groups/giving.svg', 'disciple_tools' ); ?>">
                                        </button>
                                        <p><?php esc_html_e( 'Giving', 'disciple_tools' )?></p>
                                    </div>
                                    <div class="group-progress-button-wrapper">
                                        <button class="group-progress-button" id="church_communion">
                                            <img src="<?php echo esc_attr( get_template_directory_uri() . '/dt-assets/images/groups/communion.svg', 'disciple_tools' ); ?>">
                                        </button>
                                        <p><?php esc_html_e( 'Communion', 'disciple_tools' )?></p>
                                    </div>
                                    <div class="group-progress-button-wrapper">
                                        <button class="group-progress-button" id="church_baptism">
                                            <img src="<?php echo esc_attr( get_template_directory_uri() . '/dt-assets/images/groups/baptism.svg', 'disciple_tools' ); ?>">
                                        </button>
                                        <p><?php esc_html_e( 'Baptism', 'disciple_tools' )?></p>

                                    </div>
                                    <div class="group-progress-button-wrapper">
                                        <button class="cell auto group-progress-button" id="church_prayer">
                                            <img src="<?php echo esc_attr( get_template_directory_uri() . '/dt-assets/images/groups/prayer.svg', 'disciple_tools' ); ?>">
                                        </button>
                                        <p><?php esc_html_e( 'Prayer', 'disciple_tools' )?></p>
                                    </div>
                                    <div class="group-progress-button-wrapper">
                                        <button class="group-progress-button" id="church_leaders">
                                            <img src="<?php echo esc_attr( get_template_directory_uri() . '/dt-assets/images/groups/leadership.svg', 'disciple_tools' ); ?>">
                                        </button>
                                        <p><?php esc_html_e( 'Leaders', 'disciple_tools' )?></p>
                                    </div>
                                    <div class="group-progress-button-wrapper">
                                        <button class="group-progress-button" id="church_bible">
                                            <img src="<?php echo esc_attr( get_template_directory_uri() . '/dt-assets/images/groups/word.svg', 'disciple_tools' ); ?>">
                                        </button>
                                        <p><?php esc_html_e( 'Word', 'disciple_tools' )?></p>
                                    </div>
                                    <div class="group-progress-button-wrapper">
                                        <button class="group-progress-button" id="church_praise">
                                            <img src="<?php echo esc_attr( get_template_directory_uri() . '/dt-assets/images/groups/praise.svg', 'disciple_tools' ); ?>">
                                        </button>
                                        <p><?php esc_html_e( 'Praise', 'disciple_tools' )?> </p>
                                    </div>
                                    <div class="group-progress-button-wrapper">
                                        <button class="group-progress-button" id="church_sharing">
                                            <img src="<?php echo esc_attr( get_template_directory_uri() . '/dt-assets/images/groups/evangelism.svg', 'disciple_tools' ); ?>">
                                        </button>
                                        <p><?php esc_html_e( 'Evangelism', 'disciple_tools' )?></p>
                                    </div>
                                    <div class="group-progress-button-wrapper">
                                        <button class="group-progress-button" id="church_commitment">
                                            <img src="<?php echo esc_attr( get_template_directory_uri() . '/dt-assets/images/groups/covenant.svg', 'disciple_tools' ); ?>">
                                        </button>
                                        <p><?php esc_html_e( 'Covenant', 'disciple_tools' )?></p>
                                    </div>
                                </div>
                                <div class="grid-x">
                                    <div style="margin-right:auto; margin-left:auto;min-height:302px">
                                        <object id="church-svg-wrapper" type="image/svg+xml" data="<?php echo esc_attr( get_template_directory_uri() . '/dt-assets/images/groups/church-wheel.svg', 'disciple_tools' ); ?>"></object>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section id="groups" class="xlarge-6 large-12 medium-6 cell grid-item">
                            <div class="bordered-box">
                                <label class="section-header"><?php esc_html_e( "Groups", 'disciple_tools' ) ?>
                                    <button class="help-button" data-section="group-connections-help-text">
                                        <img class="help-icon" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/help.svg' ) ?>"/>
                                    </button>
                                </label>
                                <div class="section-subheader"><?php esc_html_e( "Parent Group", 'disciple_tools' ) ?></div>
                                <var id="parent_groups-result-container" class="result-container"></var>
                                <div id="parent_groups_t" name="form-groups" class="scrollable-typeahead typeahead-margin-when-active">
                                    <div class="typeahead__container">
                                        <div class="typeahead__field">
                                        <span class="typeahead__query">
                                            <input class="js-typeahead-parent_groups input-height"
                                                   name="groups[query]" placeholder="<?php esc_html_e( "Search Groups", 'disciple_tools' ) ?>"
                                                   autocomplete="off">
                                        </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="section-subheader"><?php esc_html_e( "Child Groups", 'disciple_tools' ) ?></div>
                                <var id="child_groups-result-container" class="result-container"></var>
                                <div id="child_groups_t" name="form-child_groups" class="scrollable-typeahead typeahead-margin-when-active">
                                    <div class="typeahead__container">
                                        <div class="typeahead__field">
                                        <span class="typeahead__query">
                                            <input class="js-typeahead-child_groups input-height"
                                                   name="child_groups[query]" placeholder="<?php esc_html_e( "Search groups", 'disciple_tools' ) ?>"
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
                            </div>
                        </section>


                        <?php
                        $sections = apply_filters( 'dt_details_additional_section_ids', [], "groups" );

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


<!--    Modals-->
    <?php get_template_part( 'dt-assets/parts/modals/modal', 'share' ); ?>
    <?php get_template_part( 'dt-assets/parts/modals/modal', 'new-group' ); ?>





<?php
} )();

get_footer();
