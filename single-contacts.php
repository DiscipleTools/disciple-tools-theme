<?php if ((isset( $_POST['dt_contacts_noonce'] ) && wp_verify_nonce( $_POST['dt_contacts_noonce'], 'update_dt_contacts' ))) { dt_save_contact( $_POST ); } // Catch and save update info ?>
<?php if ( ! empty( $_POST['response'] )) { dt_update_overall_status( $_POST ); } ?>
<?php if ( ! empty( $_POST['comment_content'] )) { dt_update_required_update( $_POST ); } ?>
<?php $contact = Disciple_Tools_Contacts::get_contact( get_the_ID(), true ); ?>
<?php $contact_fields = Disciple_Tools_Contacts::get_contact_fields(); ?>
<?php get_header(); ?>
<?php //var_dump($contact_fields['quick_button_no_answer'])?>
<?php //var_dump($contact->fields["quick_button_no_answer"] ?? "test")?>

<div id="errors"> </div>

    <div id="content">

        <!-- Breadcrumb Navigation-->
        <nav aria-label="You are here:" role="navigation" class="second-bar hide-for-small-only">
            <ul class="breadcrumbs">

                <li><a href="<?php echo home_url( '/' ); ?>">Dashboard</a></li>
                <li><a href="<?php echo home_url( '/' ); ?>contacts/">Contacts</a></li>
                <li>
                    <span class="show-for-sr">Current: </span> <?php the_title_attribute(); ?>
                </li>
            </ul>
        </nav>
        <div id="inner-content">



            <main id="main" class="large-8 medium-8 columns" role="main">

                <section id="contact-details" class="medium-12 columns">
                    <div class="bordered-box">
                        <?php get_template_part( 'parts/loop', 'single-contact' ); ?>

                    </div>
                </section>

                <section id="relationships" class="medium-6 columns">
                    <div class="bordered-box">
                        <label class="section-header">Groups</label>
                        <ul>
                        <?php foreach( $contact->fields["groups"] as $group){ ?>
                            <li><a href="<?php echo $group->permalink ?>"><?php echo esc_html( $group->post_title )?></a></li>
                        <?php } ?>
                        </ul>
                        <label class="section-header">Baptized By</label>
                        <?php foreach( $contact->fields["baptized_by"] as $baptized_by){ ?>
                            <li><a href="<?php echo $baptized_by->permalink ?>"><?php echo esc_html( $baptized_by->post_title )?></a></li>
                        <?php } ?>
                        <ul>
                        </ul>
                        <label class="section-header">Baptized</label>
                        <?php foreach( $contact->fields["baptized"] as $baptized){ ?>
                            <li><a href="<?php echo $baptized->permalink ?>"><?php echo esc_html( $baptized->post_title )?></a></li>
                        <?php } ?>
                        <ul>
                        </ul>
                        <label class="section-header">Coached By</label>
                        <?php foreach( $contact->fields["coached_by"] as $coached_by){ ?>
                            <li><a href="<?php echo $coached_by->permalink ?>"><?php echo esc_html( $coached_by->post_title )?></a></li>
                        <?php } ?>
                        <ul>
                        </ul>
                        <label class="section-header">Coaching</label>
                        <?php foreach( $contact->fields["coaching"] as $coaching){ ?>
                            <li><a href="<?php echo $coaching->permalink ?>"><?php echo esc_html( $coaching->post_title )?></a></li>
                        <?php } ?>
                        <ul>
                        </ul>
                    </div>
                </section>

                <section id="faith" class="medium-6 columns">
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
                        <div class="small button-group">

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

                <section id="availability" class="medium-6 columns">
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

            <aside class="medium-4 columns">
                <?php get_template_part( 'parts/loop', 'activity-comment' ); ?>
            </aside>

        </div> <!-- end #inner-content -->

    </div> <!-- end #content -->

<?php get_footer(); ?>
