<?php
declare(strict_types=1);
$contact = Disciple_Tools_Contacts::get_contact( get_the_ID(), true );
$contact_fields = Disciple_Tools_Contacts::get_contact_fields();
if( !Disciple_Tools_Contacts::can_view( 'contacts',  get_the_ID() )){
    return wp_redirect( "not-found" );
}
$shared_with = Disciple_Tools_Contacts::get_shared_with_on_contact( get_the_ID() );
$users = Disciple_Tools_Users::get_assignable_users_compact( );
get_header(); ?>

<?php
dt_print_breadcrumbs(
    [
        [ home_url( '/' ), __( "Dashboard" ) ],
        [ home_url( '/' ) . "contacts/", __( "Contacts" ) ],
    ],
    get_the_title(),
    true
); ?>

<div id="errors"> </div>

    <!-- I'm not sure why this is indented -->
    <div id="content">

        <div id="inner-content" class="grid-x grid-margin-x">


            <section class="hide-for-large small-12 cell">
                <div class="bordered-box">
                    <?php get_template_part( 'parts/contact', 'quick-buttons' ); ?>

                    <div style="text-align: center">
                        <a class="button small" href="#comment-activity-section" style="margin-bottom: 0" >View Comments</a>
                    </div>
                </div>
            </section>

            <main id="main" class="large-7 medium-12 small-12 cell grid-x grid-margin-x" role="main" style="padding:0">

                <section id="contact-details" class="medium-12 cell">
                    <?php get_template_part( 'parts/contact', 'details' ); ?>
                </section>

                <section id="relationships" class="medium-6 cell">
                    <div class="bordered-box">
                        <button class=" float-right" onclick="edit_connections()"><i class="fi-pencil"></i> Edit</button>
                        <span class="section-header">Groups</span>
                        <ul class="groups-list">
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
                        <div class="connections-edit" >
                            <label for="groups">Add Group:</label>
                            <div id="groups">
                                <input class="typeahead" type="text" placeholder="Select a Group">
                            </div>
                        </div>


                        <?php
                        $connections = [
                            "baptized_by" => "Baptized By",
                            "baptized" => "Baptized",
                            "coached_by" => "Coached By",
                            "coaching" => "Coaching"
                        ];
                        foreach($connections as $connection => $connection_label){
                        ?>



                        <span class="section-header"><?php echo $connection_label ?></span>
                        <ul class="<?php echo $connection ?>-list">
                            <?php
                            $ids = [];
                            foreach( $contact->fields[$connection] as $value){
                                $ids[] = $value->ID;
                                ?>
                                <li class="<?php echo $value->ID ?>">
                                    <a href="<?php echo $value->permalink ?>"><?php echo esc_html( $value->post_title )?></a>
                                    <button class="details-remove-button connections-edit" onclick="remove_item(<?php echo get_the_ID()?>,  '<?php echo $connection ?>', <?php echo $value->ID ?>)">Remove</button>
                                </li>
                            <?php } ?>
                        </ul>
                        <div class="connections-edit">
                            <label for="<?php echo $connection ?>">Add <?php echo $connection_label ?>:</label>
                            <div id="<?php echo $connection ?>">
                                <input class="typeahead" type="text" placeholder="Select <?php echo $connection_label ?>">
                            </div>
                        </div>


                        <?php
                        }
                        ?>


                    </div>
                </section>

                <section id="faith" class="medium-6 cell">
                    <div class="bordered-box">
                        <label class="section-header">Progress</label>
                        <strong>Seeker Path</strong>
                        <div class="row">
                            <div class="small-6 columns">
                              <p>Current: <span id="current_seeker_path"><?php echo $contact->fields["seeker_path"]["label"] ?? ""?></span></p>
                            </div>
                            <div class="small-6 columns">
                                <p>Next: <span id="next_seeker_path">
                                <?php
                                $keys = array_keys( $contact_fields["seeker_path"]["default"] );
                                $path_index = array_search( $contact->fields["seeker_path"]["key"], $keys ) ?? 0;
                                if ( isset( $keys[$path_index+1] ) ){
                                    echo $contact_fields["seeker_path"]["default"][$keys[$path_index+1]];
                                }
                                ?>
                                </span>
                                </p>

                            </div>
                        </div>
                        <strong>Faith Milestones</strong>
                        <div class="small button-group" style="display: inline-block">

                            <?php foreach($contact_fields as $field => $val): ?>
                                <?php
                                if (strpos( $field, "milestone_" ) === 0){
                                    $class = (isset( $contact->fields[$field] ) && $contact->fields[$field]['key'] === 'yes') ?
                                        "selected-select-button" : "empty-select-button";
                                    $html = '<button onclick="save_seeker_milestones('. get_the_ID() . ", '$field')\"";
                                    $html .= 'id="'.$field .'"';
                                    $html .= 'class="' . $class . ' select-button button ">' . $contact_fields[$field]["name"] . '</a>';
                                    echo  $html;

                                }
                                ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </section>

                <section id="availability" class="medium-6 cell" style="display: none">
                    <div class="bordered-box">
                        <label class="section-header">Availability</label>
                        <div class="row" style="display: flex; justify-content: center">
                            <div style="flex: 0 1 13%">Sun</div>
                            <div style="flex: 0 1 13%">Mon</div>
                            <div style="flex: 0 1 13%">Tue</div>
                            <div style="flex: 0 1 13%">Wed</div>
                            <div style="flex: 0 1 13%">Thu</div>
                            <div style="flex: 0 1 13%">Fri</div>
                            <div style="flex: 0 1 13%">Sat</div>
                        </div>
                        <div class="row" style="display: flex; justify-content: center">
                            <div style="flex: 0 1 13%">Morn</div>
                            <div style="flex: 0 1 13%">Morn</div>
                            <div style="flex: 0 1 13%">Morn</div>
                            <div style="flex: 0 1 13%">Morn</div>
                            <div style="flex: 0 1 13%">Morn</div>
                            <div style="flex: 0 1 13%">Morn</div>
                            <div style="flex: 0 1 13%">Morn</div>
                        </div>
                        <div class="row" style="display: flex; justify-content: center">
                            <div style="flex: 0 1 13%">Lunch</div>
                            <div style="flex: 0 1 13%">Lunch</div>
                            <div style="flex: 0 1 13%">Lunch</div>
                            <div style="flex: 0 1 13%">Lunch</div>
                            <div style="flex: 0 1 13%">Lunch</div>
                            <div style="flex: 0 1 13%">Lunch</div>
                            <div style="flex: 0 1 13%">Lunch</div>
                        </div>
                        <div class="row" style="display: flex; justify-content: center">
                            <div style="flex: 0 1 13%">Aftr</div>
                            <div style="flex: 0 1 13%">Aftr</div>
                            <div style="flex: 0 1 13%">Aftr</div>
                            <div style="flex: 0 1 13%">Aftr</div>
                            <div style="flex: 0 1 13%">Aftr</div>
                            <div style="flex: 0 1 13%">Aftr</div>
                            <div style="flex: 0 1 13%">Aftr</div>
                        </div>
                        <div class="row" style="display: flex; justify-content: center">
                            <div style="flex: 0 1 13%">Night</div>
                            <div style="flex: 0 1 13%">Night</div>
                            <div style="flex: 0 1 13%">Night</div>
                            <div style="flex: 0 1 13%">Night</div>
                            <div style="flex: 0 1 13%">Night</div>
                            <div style="flex: 0 1 13%">Night</div>
                            <div style="flex: 0 1 13%">Night</div>
                        </div>
                    </div>
                </section>

            </main> <!-- end #main -->

            <aside class="large-5 medium-12 small-12 cell">
                <section class="bordered-box comment-activity-section" id="comment-activity-section">
                    <?php get_template_part( 'parts/contact', 'quick-buttons' ); ?>
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
                <button class="details-remove-button"
                        onclick="remove_shared(<?php echo get_the_ID()?>,  <?php echo $user['user_id'] ?>)">
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
                    echo '<option value="' . $user["ID"]. '">' . $user['name'] . '</option>';
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
