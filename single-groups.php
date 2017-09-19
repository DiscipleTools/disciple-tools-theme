<?php
declare(strict_types=1);
$group = Disciple_Tools_Groups::get_group( get_the_ID(), true );
if( !Disciple_Tools_Contacts::can_view( 'groups', get_the_ID() )){
    return wp_redirect( "not-found" );
}
$shared_with = Disciple_Tools_Contacts::get_shared_with_on_contact( get_the_id() );
$users = Disciple_Tools_Users::get_assignable_users_compact( );
get_header();?>

<?php
dt_print_breadcrumbs(
    [
        [ home_url( '/' ), __( "Dashboard" ) ],
        [ home_url( '/' ) . 'groups/', __( "Groups" ) ],
    ],
    get_the_title(),
    true
); ?>

<div id="errors"> </div>

<div id="content">

    <div id="inner-content" class="grid-x grid-margin-x">
        <span id="group-id" style="display: none"><?php echo get_the_ID()?></span>

        <main id="main" class="large-7 medium-12 small-12 cell grid-x grid-margin-x" role="main" style="padding:0">

            <section id="contact-details" class="medium-12 cell">
                <?php get_template_part( 'parts/group', 'details' ); ?>
            </section>

            <section id="relationships" class="medium-6 cell">
                <div class="bordered-box">
                    <span class="section-header">Members</span>
<!--                    <button class=" float-right" id="members-edit"><i class="fi-pencil"></i> Edit</button>-->
                    <ul class="members-list">
                        <?php
                        $ids = [];
                        foreach( $group["members"] as $member){
                            $ids[] = $member->ID;
                            ?>
                            <li class="<?php echo $member->ID ?>">
                                <a href="<?php echo $member->permalink ?>"><?php echo esc_html( $member->post_title )?></a>
                                <button class="details-remove-button members-edit"
                                        data-field="members" data-id="<?php echo $member->ID ?>"
                                        data-name="<?php echo esc_html( $member->post_title ) ?>"
                                >Remove</button>
                            </li>
                        <?php } ?>
                    </ul>
                    <div>
                        <label for="members">Add a Member:</label>
                        <div id="members">
                            <input class="typeahead" type="text" placeholder="Select a Member">
                        </div>
                    </div>
                </div>
            </section>

            <section id="faith" class="medium-6 cell">
                <div class="bordered-box">
                    <label class="section-header">Progress</label>

                    <div style="display:flex;flex-wrap:wrap">
                        <div class="group-progress-button-wrapper">
                            <button  class="group-progress-button" id="church_fellowship">
                                <img src="<?php echo get_template_directory_uri() . '/assets/images/groups/heart.svg' ?>">
                            </button>
                            <p><?php _e( 'Fellowship', 'disciple_tools' )?> </p>
                        </div>
                        <div class="group-progress-button-wrapper">
                            <button class="group-progress-button" id="church_giving">
                                <img src="<?php echo get_template_directory_uri() . '/assets/images/groups/giving.svg' ?>">
                            </button>
                            <p><?php _e( 'Giving', 'disciple_tools' )?></p>
                        </div>
                        <div class="group-progress-button-wrapper">
                            <button class="group-progress-button" id="church_communion">
                                <img src="<?php echo get_template_directory_uri() . '/assets/images/groups/communion.svg' ?>">
                            </button>
                            <p><?php _e( 'Communion', 'disciple_tools' )?></p>
                        </div>
                        <div class="group-progress-button-wrapper">
                            <button class="group-progress-button" id="church_baptism">
                                <img src="<?php echo get_template_directory_uri() . '/assets/images/groups/baptism.svg' ?>">
                            </button>
                            <p><?php _e( 'Baptism', 'disciple_tools' )?></p>

                        </div>
                        <div class="group-progress-button-wrapper">
                            <button class="cell auto group-progress-button" id="church_prayer">
                                <img src="<?php echo get_template_directory_uri() . '/assets/images/groups/prayer.svg' ?>">
                            </button>
                            <p><?php _e( 'Prayer', 'disciple_tools' )?></p>
                        </div>
                        <div class="group-progress-button-wrapper">
                            <button class="group-progress-button" id="church_leaders">
                                <img src="<?php echo get_template_directory_uri() . '/assets/images/groups/leadership.svg' ?>">
                            </button>
                            <p><?php _e( 'Leaders', 'disciple_tools' )?></p>
                        </div>
                        <div class="group-progress-button-wrapper">
                            <button class="group-progress-button" id="church_bible">
                                <img src="<?php echo get_template_directory_uri() . '/assets/images/groups/word.svg' ?>">
                            </button>
                            <p><?php _e( 'Word', 'disciple_tools' )?></p>
                        </div>
                        <div class="group-progress-button-wrapper">
                            <button class="group-progress-button" id="church_praise">
                                <img src="<?php echo get_template_directory_uri() . '/assets/images/groups/praise.svg' ?>">
                            </button>
                            <p><?php _e( 'Praise', 'disciple_tools' )?> </p>
                        </div>
                        <div class="group-progress-button-wrapper">
                            <button class="group-progress-button" id="church_sharing">
                                <img src="<?php echo get_template_directory_uri() . '/assets/images/groups/evangelism.svg' ?>">
                            </button>
                            <p><?php _e( 'Evangelism', 'disciple_tools' )?></p>
                        </div>
                        <div class="group-progress-button-wrapper">
                            <button class="group-progress-button" id="church_commitment">
                                <img src="<?php echo get_template_directory_uri() . '/assets/images/groups/covenant.svg' ?>">
                            </button>
                            <p><?php _e( 'Covenant', 'disciple_tools' )?></p>
                        </div>
                    </div>
                    <div class="grid-x">
                        <div style="margin-right:auto; margin-left:auto">
                            <object id="church-svg-wrapper" type="image/svg+xml" data="<?php echo get_template_directory_uri() . '/assets/images/groups/church-wheel.svg' ?>"></object>
                        </div>
                    </div>
                </div>
            </section>

            <section id="groups" class="medium-6 cell">
                <div class="bordered-box">
                    <label class="section-header">Groups</label>
                    <strong>Parent Group</strong>
                    <strong>Child Groups</strong>
                </div>
            </section>


        </main> <!-- end #main -->

        <aside class="large-5 medium-12 small-12 cell">
            <section class="bordered-box comment-activity-section" id="comment-activity-section">
                <?php get_template_part( 'parts/loop', 'activity-comment' ); ?>
            </section>
        </aside>

    </div> <!-- end #inner-content -->

</div> <!-- end #content -->


<div class="reveal" id="share-contact-modal" data-reveal>

    <p class="lead">Share settings</p>
    <h6>Already sharing with</h6>

    <ul id="shared-with-list">
        <?php
        foreach( $shared_with as $user) { ?>
            <li class="<?php echo $user['user_id'] ?>"> <?php echo $user['display_name'] ?>
                <button class="details-remove-button share"
                        data-id="<?php echo $user['user_id'] ?>">
                    Unshare
                </button>
            </li>
        <?php } ?>
    </ul>

    <p>
        <label>Share this group with the following user:
            <select class="share-with-select" id="share-with">
                <option value="0"></option>
                <?php
                foreach( $users as $user ){
                    echo '<option value="' . $user['ID']. '">' . $user['name'] . '</option>';
                }
                ?>
            </select>
        </label>
    </p>

    <div class="grid-x">
        <button class="button button-cancel clear"
                data-close aria-label="Close reveal" type="button">
            Cancel
        </button>
        <button class="button" type="button"
                id="add-shared-button">
            Share
        </button>
        <button class="close-button" data-close aria-label="Close modal" type="button">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
</div>





<?php get_footer(); ?>
