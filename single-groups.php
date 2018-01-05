<?php
declare(strict_types=1);

(function() {

    global $post;

    Disciple_Tools_Notifications::process_new_notifications( get_the_ID() ); // removes new notifications for this post

    $group = Disciple_Tools_Groups::get_group( get_the_ID(), true );
    if ( !Disciple_Tools_Contacts::can_view( 'groups', get_the_ID() )){
        return wp_redirect( "not-found" );
    }
    $shared_with = Disciple_Tools_Contacts::get_shared_with_on_contact( get_the_id() );
    $users = Disciple_Tools_Users::get_assignable_users_compact();
    get_header();?>

<?php
dt_print_breadcrumbs(
    [
        [ home_url( '/' ), __( "Dashboard" ) ],
        [ home_url( '/' ) . 'groups/', __( "Groups" ) ],
    ],
    get_the_title(),
    true,
    true
); ?>

<div id="errors"> </div>

<div id="content">

    <div id="inner-content" class="grid-x grid-margin-x grid-margin-y">
        <span id="group-id" style="display: none"><?php echo get_the_ID()?></span>

        <main id="main" class="large-7 medium-12 small-12 cell grid-x grid-margin-x grid-margin-y" role="main" style="padding:0">

            <section id="contact-details" class="small-12 cell">
                <?php get_template_part( 'parts/group', 'details' ); ?>
            </section>

            <section id="relationships" class="xlarge-6 large-12 medium-6 cell">
                <div class="bordered-box">
                    <span class="section-header"><?php esc_html_e( 'Members', 'disciple_tools' )?></span>
<!--                    <button class=" float-right" id="members-edit"><i class="fi-pencil"></i> Edit</button>-->
                    <ul class="members-list">
                        <?php
                        $ids = [];
                        foreach ( $group["members"] as $member){
                            $ids[] = $member->ID;
                            ?>
                            <li class="<?php echo esc_attr( $member->ID, 'disciple_tools' ); ?>">
                                <a href="<?php echo esc_url( $member->permalink ); ?>"><?php echo esc_html( $member->post_title )?></a>
                                <button class="details-remove-button members-edit"
                                        data-field="members" data-id="<?php echo esc_attr( $member->ID, 'disciple_tools' ); ?>"
                                        data-name="<?php echo esc_html( $member->post_title )?>">
                                    <?php esc_html_e( 'Remove', 'disciple_tools' )?>
                                </button>
                            </li>
                        <?php } ?>
                    </ul>
                    <div>
                        <label for="members"><?php esc_html_e( 'Add a Member', 'disciple_tools' )?>:</label>
                        <div id="members">
                            <input class="typeahead" type="text" placeholder="Type to search contacts">
                        </div>
                    </div>
                </div>
            </section>

            <section id="faith" class="xlarge-6 large-12 medium-6 cell">
                <div class="bordered-box js-progress-bordered-box half-opacity">
                    <label class="section-header" ><?php esc_html_e( 'Progress', 'disciple_tools' )?></label>

                    <div style="display:flex;flex-wrap:wrap;margin-top:10px">
                        <div class="group-progress-button-wrapper">
                            <button  class="group-progress-button" id="church_fellowship">
                                <img src="<?php echo esc_attr( get_template_directory_uri() . '/assets/images/groups/heart.svg', 'disciple_tools' ); ?>">
                            </button>
                            <p><?php esc_html_e( 'Fellowship', 'disciple_tools' )?> </p>
                        </div>
                        <div class="group-progress-button-wrapper">
                            <button class="group-progress-button" id="church_giving">
                                <img src="<?php echo esc_attr( get_template_directory_uri() . '/assets/images/groups/giving.svg', 'disciple_tools' ); ?>">
                            </button>
                            <p><?php esc_html_e( 'Giving', 'disciple_tools' )?></p>
                        </div>
                        <div class="group-progress-button-wrapper">
                            <button class="group-progress-button" id="church_communion">
                                <img src="<?php echo esc_attr( get_template_directory_uri() . '/assets/images/groups/communion.svg', 'disciple_tools' ); ?>">
                            </button>
                            <p><?php esc_html_e( 'Communion', 'disciple_tools' )?></p>
                        </div>
                        <div class="group-progress-button-wrapper">
                            <button class="group-progress-button" id="church_baptism">
                                <img src="<?php echo esc_attr( get_template_directory_uri() . '/assets/images/groups/baptism.svg', 'disciple_tools' ); ?>">
                            </button>
                            <p><?php esc_html_e( 'Baptism', 'disciple_tools' )?></p>

                        </div>
                        <div class="group-progress-button-wrapper">
                            <button class="cell auto group-progress-button" id="church_prayer">
                                <img src="<?php echo esc_attr( get_template_directory_uri() . '/assets/images/groups/prayer.svg', 'disciple_tools' ); ?>">
                            </button>
                            <p><?php esc_html_e( 'Prayer', 'disciple_tools' )?></p>
                        </div>
                        <div class="group-progress-button-wrapper">
                            <button class="group-progress-button" id="church_leaders">
                                <img src="<?php echo esc_attr( get_template_directory_uri() . '/assets/images/groups/leadership.svg', 'disciple_tools' ); ?>">
                            </button>
                            <p><?php esc_html_e( 'Leaders', 'disciple_tools' )?></p>
                        </div>
                        <div class="group-progress-button-wrapper">
                            <button class="group-progress-button" id="church_bible">
                                <img src="<?php echo esc_attr( get_template_directory_uri() . '/assets/images/groups/word.svg', 'disciple_tools' ); ?>">
                            </button>
                            <p><?php esc_html_e( 'Word', 'disciple_tools' )?></p>
                        </div>
                        <div class="group-progress-button-wrapper">
                            <button class="group-progress-button" id="church_praise">
                                <img src="<?php echo esc_attr( get_template_directory_uri() . '/assets/images/groups/praise.svg', 'disciple_tools' ); ?>">
                            </button>
                            <p><?php esc_html_e( 'Praise', 'disciple_tools' )?> </p>
                        </div>
                        <div class="group-progress-button-wrapper">
                            <button class="group-progress-button" id="church_sharing">
                                <img src="<?php echo esc_attr( get_template_directory_uri() . '/assets/images/groups/evangelism.svg', 'disciple_tools' ); ?>">
                            </button>
                            <p><?php esc_html_e( 'Evangelism', 'disciple_tools' )?></p>
                        </div>
                        <div class="group-progress-button-wrapper">
                            <button class="group-progress-button" id="church_commitment">
                                <img src="<?php echo esc_attr( get_template_directory_uri() . '/assets/images/groups/covenant.svg', 'disciple_tools' ); ?>">
                            </button>
                            <p><?php esc_html_e( 'Covenant', 'disciple_tools' )?></p>
                        </div>
                    </div>
                    <div class="grid-x">
                        <div style="margin-right:auto; margin-left:auto">
                            <object id="church-svg-wrapper" type="image/svg+xml" data="<?php echo esc_attr( get_template_directory_uri() . '/assets/images/groups/church-wheel.svg', 'disciple_tools' ); ?>"></object>
                        </div>
                    </div>
                </div>
            </section>

<!--            <section id="groups" class="medium-6 cell">-->
<!--                <div class="bordered-box">-->
<!--                    <label class="section-header">Groups</label>-->
<!--                    <strong>Parent Group</strong>-->
<!--                    <strong>Child Groups</strong>-->
<!--                </div>-->
<!--            </section>-->


        </main> <!-- end #main -->

        <aside class="auto cell grid-x grid-margin-x">
            <section class="xlarge-5 large-5 medium-12 small-12 cell bordered-box comment-activity-section" id="comment-activity-section">
                <?php get_template_part( 'parts/loop', 'activity-comment' ); ?>
            </section>
        </aside>

    </div> <!-- end #inner-content -->

</div> <!-- end #content -->


<div class="reveal" id="share-contact-modal" data-reveal>

    <p class="lead"><?php esc_html_e( 'Share settings', 'disciple_tools' )?></p>
    <h6><?php esc_html_e( 'Already sharing with', 'disciple_tools' )?></h6>

    <ul id="shared-with-list">
        <?php
        foreach ( $shared_with as $user) { ?>
            <li class="<?php echo esc_attr( $user['user_id'], 'disciple_tools' ); ?>"> <?php echo esc_html( $user['display_name'] )?>
                <button class="details-remove-button share" data-id="<?php echo esc_attr( $user['user_id'], 'disciple_tools' ); ?>">
                    <?php esc_html_e( 'Unshare', 'disciple_tools' )?>
                </button>
            </li>
        <?php } ?>
    </ul>

    <p>
        <label><?php esc_html_e( 'Share this group with the following user', 'disciple_tools' )?>:
            <select class="share-with-select" id="share-with">
                <option value="0"></option>
                <?php foreach ( $users as $user ) { ?>
                    <option value="<?php echo esc_html( $user["ID"] )?>"><?php echo esc_html( $user['name'] )?></option>
                <?php } ?>
            </select>
        </label>
    </p>

    <div class="grid-x">
        <button class="button button-cancel clear" data-close aria-label="Close reveal" type="button">
            <?php esc_html_e( 'Cancel', 'disciple_tools' )?>
        </button>
        <button class="button" type="button" id="add-shared-button">
            <?php esc_html_e( 'Share', 'disciple_tools' )?>
        </button>
        <button class="close-button" data-close aria-label="Close modal" type="button">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
</div>





<?php
})();

get_footer();
