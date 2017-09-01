<?php $contact = Disciple_Tools_Contacts::get_contact( get_the_ID(), true );
$contact_fields = Disciple_Tools_Contacts::get_contact_fields();
if( !Disciple_Tools_Contacts::can_view_contact( get_the_ID() )){
    return wp_redirect( "not-found" );
}
$groups = Disciple_Tools_Groups::get_groups();
//@todo get restricted options
$contacts = Disciple_Tools_Contacts::get_viewable_contacts( true );
$connection_fields = ["groups" => $groups, "contacts" => $contacts->posts];
$shared_with = Disciple_Tools_Contacts::get_shared_with( get_the_id() );
$users = Disciple_Tools_Contacts::get_assignable_users( get_the_ID() );
 get_header(); ?>
<?php //var_dump($contact_fields['quick_button_no_answer'])?>
<?php //var_dump($contact->fields["quick_button_no_answer"] ?? "test")?>

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
                    <?php get_template_part( 'parts/loop', 'single-contact' ); ?>
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
                            <select id="groups" onchange="add_input_item( <?php echo get_the_ID();?>, 'groups')">
                                <?php
                                echo '<option value="0"></option>';
                                foreach( $groups as $value ){
                                    if ( !in_array( $value->ID, $ids )){
                                        echo '<option value="' . $value->ID. '">' . esc_html( $value->post_title ) . '</option>';
                                    }
                                }
                                ?>
                            </select>
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
                            <select id="<?php echo $connection ?>" onchange="add_input_item( <?php echo get_the_ID();?>, '<?php echo $connection ?>')">
                                <?php
                                echo '<option value="0"></option>';
                                foreach( $connection_fields["contacts"] as $value ){
                                    if ( !in_array( $value->ID, $ids )){
                                        echo '<option value="' . $value->ID. '">' . esc_html( $value->post_title ) . '</option>';
                                    }
                                }
                                ?>
                            </select>
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
