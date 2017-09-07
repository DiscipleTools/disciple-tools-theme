<?php $contact = Disciple_Tools_Contacts::get_contact( get_the_ID(), true );
$contact_fields = Disciple_Tools_Contacts::get_contact_fields();
if( !Disciple_Tools_Contacts::can_view_contact( get_the_ID() )){
    return wp_redirect( "not-found" );
}
$shared_with = Disciple_Tools_Contacts::get_shared_with( get_the_id() );
$users = Disciple_Tools_Contacts::get_assignable_users( get_the_ID() );
get_header(); ?>



<div data-sticky-container>
    <nav aria-label="You are here:" role="navigation"
         data-sticky data-options="marginTop:3;" style="width:100%" data-sticky-on="medium"
         class="second-bar hide-for-small-only">

        <div class="grid-x">
            <div class="small-offset-4 cell small-4 center-items">
                <ul class="breadcrumbs">

                    <li><a href="<?php echo home_url( '/' ); ?>">Dashboard</a></li>
                    <li><a href="<?php echo home_url( '/' ); ?>contacts/">Contacts</a></li>
                    <li>
                        <span class="show-for-sr">Current: </span> <?php the_title_attribute(); ?>
                    </li>
                </ul>
            </div>
            <div class="cell small-4 align-right grid-x">
                <div class="cell shrink ">
                    <button data-open="share-contact-modal" class="center-items">
                        <img src="<?php echo get_template_directory_uri() . "/assets/images/share.svg" ?>">
                        <span style="margin:0 10px 0 10px">Share</span>
                    </button>
                </div>
            </div>
        </div>
    </nav>
</div>
<span id="group-id" style="display: none"><?php echo get_the_ID()?></span>
<div id="errors"> </div>

<!-- Breadcrumb Navigation-->
<div id="content">

    <div id="inner-content" class="grid-x grid-margin-x">


        <section class="hide-for-large small-12 cell">
            <div class="bordered-box">
                <div class="contact-quick-buttons">
                    <?php foreach( $contact_fields as $field => $val ){
                        if ( strpos( $field, "quick_button" ) === 0){
                            $current_value = 0;
                            if ( isset( $contact->fields[$field] ) ){
                                $current_value = $contact->fields[$field];
                            }?>

                            <button class="contact-quick-button <?php echo $field ?>"
                                    onclick="save_quick_action(<?php echo get_the_ID() ?>, '<?php echo $field?>')">
                                <img src="<?php echo get_template_directory_uri() . "/assets/images/" . $val['icon'] ?>">
                                <span class="contact-quick-button-number"><?php echo $current_value ?></span>
                                <p><?php echo $val["name"] ?></p>
                            </button>
                        <?php }}
                    ?>

                </div>
                <div style="text-align: center">
                    <a class="button small" href="#comment-activity-section" style="margin-bottom: 0" >View Comments</a>
                </div>
            </div>
        </section>

        <main id="main" class="large-7 medium-12 small-12 cell grid-x grid-margin-x" role="main" style="padding:0">

            <section id="contact-details" class="medium-12 cell">
                <?php get_template_part( 'parts/group', 'details' ); ?>
            </section>

            <section id="relationships" class="medium-6 cell">
                <div class="bordered-box">
                    <span class="section-header">Members</span>
                    <ul class="member-list">
                        <?php
                        $ids = [];
                        foreach( $contact->fields["groups"] as $value){
                            $ids[] = $value->ID;
                            ?>
                            <li class="<?php echo $value->ID ?>">
                                <a href="<?php echo $value->permalink ?>"><?php echo esc_html( $value->post_title )?></a>
                                <button class="details-remove-button connections-edit" onclick="remove_item(<?php echo get_the_ID()?>,  'groups', <?php echo $value->ID ?>)">Remove</button>
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

                    <div class="grid-x">
                        <div class="cell auto group-progress-button">
                            <img class="group-progress-img" src="<?php echo get_template_directory_uri() . '/assets/images/groups/heart.svg' ?>">
                            <p>Fellowship</p>
                        </div>
                        <div class="cell auto group-progress-button">
                            <img class="group-progress-img" src="<?php echo get_template_directory_uri() . '/assets/images/groups/giving.svg' ?>">
                            <p>Giving</p>
                        </div>
                        <div class="cell auto group-progress-button">
                            <img class="group-progress-img" src="<?php echo get_template_directory_uri() . '/assets/images/groups/communion.svg' ?>">
                            <p>Communion</p>
                        </div>
                        <div class="cell auto group-progress-button">
                            <img class="group-progress-img" src="<?php echo get_template_directory_uri() . '/assets/images/groups/baptism.svg' ?>">
                            <p>Baptism</p>
                        </div>
                        <button class="cell auto group-progress-button">
                            <img class="group-progress-img" src="<?php echo get_template_directory_uri() . '/assets/images/groups/prayer.svg' ?>">
                            <p>Prayer</p>
                        </button>
                    </div>
                    <div class="grid-x">
                        <div class="cell auto group-progress-button">
                            <img class="group-progress-img" src="<?php echo get_template_directory_uri() . '/assets/images/groups/leadership.svg' ?>">
                            <p>Leaders</p>
                        </div>
                        <div class="cell auto group-progress-button">
                            <img class="group-progress-img" src="<?php echo get_template_directory_uri() . '/assets/images/groups/word.svg' ?>">
                            <p>Word</p>
                        </div>
                        <div class="cell auto group-progress-button">
                            <img class="group-progress-img" src="<?php echo get_template_directory_uri() . '/assets/images/groups/praise.svg' ?>">
                            <p>Praise</p>
                        </div>
                        <div class="cell auto group-progress-button">
                            <img class="group-progress-img" src="<?php echo get_template_directory_uri() . '/assets/images/groups/evangelism.svg' ?>">
                            <p>Evangelism</p>
                        </div>
                        <button class="cell auto group-progress-button">
                            <img class="group-progress-img" src="<?php echo get_template_directory_uri() . '/assets/images/groups/covenant.svg' ?>">
                            <p>Covenant</p>
                        </button>
                    </div>
                    <div class="grid-x">
                        <div style="margin-right:auto; margin-left:auto">
                        <img class="" src="<?php echo get_template_directory_uri() . '/assets/images/groups/component.svg' ?>">
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
            <?php get_template_part( 'parts/loop', 'activity-comment' ); ?>
        </aside>

    </div> <!-- end #inner-content -->

</div> <!-- end #content -->


<div class="reveal" id="share-contact-modal" data-reveal>

    <p class="lead">Share settings</p>
    <h6>Already sharing with</h6>

    <ul id="shared-with-list">
        <?php
        $shared_with = dt_get_contacts_shared_with( get_the_ID() );
        foreach( $shared_with as $contact ) { ?>
            <li class="<?php echo $contact['user_id'] ?>"> <?php echo $contact['display_name'] ?>
                <button class="details-remove-button"
                        onclick="remove_shared(<?php echo get_the_ID()?>,  <?php echo $contact['user_id'] ?>)">
                    Unshare
                </button>
            </li>
        <?php } ?>
    </ul>

    <p>
        <label>Share this contact with the following user:
            <select class="share-with-select" id="share-with">
                <option value="0"></option>
                <?php
                foreach( $users as $user ){
                    echo '<option value="' . $user->ID. '">' . $user->display_name . '</option>';
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
                id="confirm-pause"
                onclick="add_shared(<?php echo get_the_ID();?>, 'share-with')">
            Share
        </button>
        <button class="close-button" data-close aria-label="Close modal" type="button">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
</div>



<?php get_footer(); ?>
