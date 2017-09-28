<?php
/*
Template Name: Settings
*/
?>

<?php get_header(); ?>

<?php
dt_print_breadcrumbs(
    [
        [ home_url( '/' ), __( "Dashboard" ) ],
        [ home_url( '/' ) . "settings", __( "Settings" ) ],
    ],
    get_the_title(),
    false
); ?>

<div id="content">
    
    <div id="inner-content" class="grid-x grid-margin-x">
        
        <div class="large-3 medium-12 small-12 cell ">
            
            <section id="" class="medium-12 cell sticky" data-sticky data-margin-top="6.5">
                
                <div class="bordered-box">
                    
                    <ul class="menu vertical expanded" data-smooth-scroll data-offset="100">
                        <li><a href="#profile">Profile</a></li>
                        <li><a href="#notifications">Notifications</a></li>
                        <li><a href="#availability">Availability</a></li>
                    </ul>
                
                </div>
                
                <br>
                
            </section>
        
        
        </div>
        
        <div class="large-9 medium-12 small-12 cell ">
            
            
            <div class="bordered-box" id="profile" data-magellan-target="profile">
                
                <?php
                // TODO: Section in progress.
                /**
                 * Build all user data for the profile section
                 */
                $dt_user = get_userdata( get_current_user_id() );
                $dt_usermeta = get_user_meta( get_current_user_id() );
                $dt_user_fields = dt_build_user_fields_display( $dt_usermeta );
                
                ?>
                
                <button class="float-right" onclick=""><i class="fi-pencil"></i> Edit</button>
                <span class="section-header">Your Profile</span>
                <hr size="1" style="max-width:100%"/>
                
                
                <div class="grid-x grid-margin-x grid-padding-x grid-padding-y ">
                    
                    <div class="small-12 medium-4 cell">
                        
                        <p><?php echo get_avatar( get_current_user_id(), '150' ); ?></p>
                        
                        <strong>Name</strong>
                        <ul>
                            <li>Username: <?php echo esc_html( $dt_user->user_login ); ?></li>
                            <li>First Name: <?php echo esc_html( $dt_user->first_name ); ?></li>
                            <li>Last Name: <?php echo esc_html( $dt_user->last_name ); ?></li>
                            <li>Nickname: <?php echo esc_html( $dt_user->nickname ); ?></li>
                        </ul>
                        
                        <p></p>
                        
                        <strong>Biography</strong>
                        <p><?php echo esc_html( $dt_user->user_description ); ?></p>
                    
                    </div>
                    <div class="small-12 medium-4 cell">
                        
                        <p><strong>Email</strong></p>
                        <ul>
                            <?php
                            foreach( $dt_user_fields as $field ) {
                                if( $field[ 'type' ] == 'email' && !empty( $field[ 'value' ] ) ) {
                                    echo '<li><a href="mailto:'.esc_html( $field[ 'value' ] ).'" target="_blank">' . esc_html( $field[ 'value' ] ) . '</a> (' . esc_html( $field[ 'label' ] ) . ')</li>';
                                }
                            }
                            ?>
                        </ul>
                        
                        <strong>Phone</strong>
                        <ul>
                            <?php
                            foreach( $dt_user_fields as $field ) {
                                if( $field[ 'type' ] == 'phone' && !empty( $field[ 'value' ] ) ) {
                                    echo '<li>' . esc_html( $field[ 'value' ] ) . ' (' . esc_html( $field[ 'label' ] ) . ')</li>';
                                }
                            }
                            ?>
                        </ul>
                        
                        <strong>Address</strong>
                        <ul>
                            <?php
                            foreach( $dt_user_fields as $field ) {
                                if( $field[ 'type' ] == 'address' && !empty( $field[ 'value' ] ) ) {
                                    echo '<li>' . esc_html( $field[ 'value' ] ) . ' (' . esc_html( $field[ 'label' ] ) . ')</li>';
                                }
                            }
                            ?>
                        </ul>
                        
                        <strong>Social</strong>
                        <ul>
                            <?php
                            foreach( $dt_user_fields as $field ) {
                                if( $field[ 'type' ] == 'social' && !empty( $field[ 'value' ] ) ) {
                                    echo '<li>' . esc_html( $field[ 'value' ] ) . ' (' . esc_html( $field[ 'label' ] ) . ')</li>';
                                }
                            }
                            ?>
                        </ul>
                        
                        <strong>Other</strong>
                        <ul>
                        <?php
                        foreach( $dt_user_fields as $field ) {
                            if( $field[ 'type' ] == 'other' && !empty( $field[ 'value' ] ) ) {
                                echo '<li>' . esc_html( $field[ 'value' ] ) . ' (' . esc_html( $field[ 'label' ] ) . ')</li>';
                            }
                        }
                        ?>
                        </ul>
                    
                    
                    </div>
                    <div class="small-12 medium-4 cell">
                        
                        <p><strong>Locations</strong></p>
                        <?php
                        $dt_user_locations_list = dt_get_user_locations_list( get_current_user_id() );
                        if( $dt_user_locations_list ) {
                            echo '<ul>';
                            foreach( $dt_user_locations_list as $locations_list ) {
                                echo '<li><a href="'. esc_url( $locations_list->guid ).'">' . esc_html( $locations_list->post_title ) . '</a></li>';
                            }
                            echo '</ul>';
                        }
                        ?>
                        
                        
                        <p><strong>Teams</strong></p>
                        <?php
                        $dt_user_team_members_list = dt_get_user_team_members_list( get_current_user_id() );
                        if( $dt_user_team_members_list ) {
                            foreach( $dt_user_team_members_list as $team_list ) {
                                echo $team_list[ 'team_name' ];
                                if( !empty( $team_list[ 'team_members' ] ) ) {
                                    echo '<ul>';
                                    foreach( $team_list[ 'team_members' ] as $member ) {
                                        echo '<li><a href="' . esc_url( $member[ 'user_url' ] ) . '">' . esc_html( $member[ 'display_name' ] ) . '</a></li>';
                                    }
                                    echo '</ul>';
                                }
                            }
                        }
                        ?>
                    
                    </div>
                </div>
            
            </div> <!-- End Profile -->
            
            
            <?php
            // notifications query
            $dt_notification_options = dt_get_user_notification_options();
            $dt_site_notification_defaults = dt_get_site_notification_defaults();
            ?>
            
            <!-- Begin Notification-->
            <div class="bordered-box" id="notifications" data-magellan-target="notifications">
                <span class="section-header">Notifications</span>
                <hr size="1" style="max-width:100%"/>
                
                <table class="form-table">
                    <thead>
                    <tr>
                        <td>Type of Notification</td>
                        <td>Web</td>
                        <td>Email</td>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td class="tall-4">Newly Assigned Contact</td>
                        <td>
                            <?php if( $dt_site_notification_defaults[ 'new_web' ] ) {
                                print '<div style="height:2em;">required</div>';
                            } else { ?>
                                <div class="switch">
                                    <input class="switch-input" id="new_web" type="checkbox"
                                           name="new_web" onclick="change_notification_preference('new_web');" <?php ( $dt_notification_options[ 'new_web' ] ) ? print esc_attr( 'checked' ) : print esc_attr( '' ); ?> >
                                    <label class="switch-paddle inactive" for="new_web">
                                        <span class="show-for-sr">Newly Assigned Contact</span>
                                    </label>
                                </div>
                            <?php } // end else ?>
                        </td>
                        <td>
                            <?php if( $dt_site_notification_defaults[ 'new_email' ] ) {
                                print 'required';
                            } else { ?>
                                <div class="switch">
                                    <input class="switch-input" id="new_email" type="checkbox"
                                           name="new_email" onclick="change_notification_preference('new_email');" <?php ( $dt_notification_options[ 'new_email' ] ) ? print esc_attr( 'checked' ) : print esc_attr( '' ); ?> >
                                    <label class="switch-paddle" for="new_email">
                                        <span class="show-for-sr">Newly Assigned Contact</span>
                                    </label>
                                </div>
                            <?php } // end else ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="tall-4">@Mentions</td>
                        <td>
                            <?php if( $dt_site_notification_defaults[ 'mentions_web' ] ) {
                                print 'required';
                            } else { ?>
                                <div class="switch">
                                    <input class="switch-input" id="mentions_web" type="checkbox"
                                            name="mentions_web" onclick="change_notification_preference('mentions_web');" <?php ( $dt_notification_options[ 'mentions_web' ] ) ? print esc_attr( 'checked' ) : print esc_attr( '' ); ?> >
                                    <label class="switch-paddle" for="mentions_web">
                                        <span class="show-for-sr">@Mentions</span>
                                    </label>
                                </div>
                            <?php } // end else ?>
                        </td>
                        <td>
                            <?php if( $dt_site_notification_defaults[ 'mentions_email' ] ) {
                                print 'required';
                            } else { ?>
                                <div class="switch">
                                    <input class="switch-input" id="mentions_email" type="checkbox"
                                           name="mentions_email" onclick="change_notification_preference('mentions_email');" <?php ( $dt_notification_options[ 'mentions_email' ] ) ? print esc_attr( 'checked' ) : print esc_attr( '' ); ?> >
                                    <label class="switch-paddle" for="mentions_email">
                                        <span class="show-for-sr">@Mentions</span>
                                    </label>
                                </div>
                            <?php } // end else ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="tall-4">Update Needed</td>
                        <td>
                            <?php if( $dt_site_notification_defaults[ 'updates_web' ] ) {
                                print 'required';
                            } else { ?>
                                <div class="switch">
                                    <input class="switch-input" id="updates_web" type="checkbox"
                                           name="updates_web" onclick="change_notification_preference('updates_web');" <?php ( $dt_notification_options[ 'updates_web' ] ) ? print esc_attr( 'checked' ) : print esc_attr( '' ); ?> >
                                    <label class="switch-paddle" for="updates_web">
                                        <span class="show-for-sr">Update Needed</span>
                                    </label>
                                </div>
                            <?php } // end else ?>
                        </td>
                        <td>
                            <?php if( $dt_site_notification_defaults[ 'updates_email' ] ) {
                                print 'required';
                            } else { ?>
                                <div class="switch">
                                    <input class="switch-input" id="updates_email" type="checkbox"
                                           name="updates_email" onclick="change_notification_preference('updates_email');" <?php ( $dt_notification_options[ 'updates_email' ] ) ? print esc_attr( 'checked' ) : print esc_attr( '' ); ?> >
                                    <label class="switch-paddle" for="updates_email">
                                        <span class="show-for-sr">Update Needed</span>
                                    </label>
                                </div>
                            <?php } // end else ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="tall-4">Contact Info Changed</td>
                        <td>
                            <?php if( $dt_site_notification_defaults[ 'changes_web' ] ) {
                                print 'required';
                            } else { ?>
                                <div class="switch">
                                    <input class="switch-input" id="changes_web" type="checkbox"
                                           name="changes_web" onclick="change_notification_preference('changes_web');" <?php ( $dt_notification_options[ 'changes_web' ] ) ? print esc_attr( 'checked' ) : print esc_attr( '' ); ?> >
                                    <label class="switch-paddle" for="changes_web">
                                        <span class="show-for-sr">Contact Info Changed</span>
                                    </label>
                                </div>
                            <?php } // end else ?>
                        </td>
                        <td>
                            <?php if( $dt_site_notification_defaults[ 'changes_email' ] ) {
                                print 'required';
                            } else { ?>
                                <div class="switch">
                                    <input class="switch-input" id="changes_email" type="checkbox"
                                           name="changes_email" onclick="change_notification_preference('changes_email');" <?php ( $dt_notification_options[ 'changes_email' ] ) ? print esc_attr( 'checked' ) : print esc_attr( '' ); ?> >
                                    <label class="switch-paddle" for="changes_email">
                                        <span class="show-for-sr">Contact Info Changed</span>
                                    </label>
                                </div>
                            <?php } // end else ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="tall-4">Contact Milestones</td>
                        <td>
                            <?php if( $dt_site_notification_defaults[ 'milestones_web' ] ) {
                                print 'required';
                            } else { ?>
                                <div class="switch">
                                    <input class="switch-input" id="milestones_web" type="checkbox"
                                           name="milestones_web" onclick="change_notification_preference('milestones_web');" <?php ( $dt_notification_options[ 'milestones_web' ] ) ? print esc_attr( 'checked' ) : print esc_attr( '' ); ?> >
                                    <label class="switch-paddle" for="milestones_web">
                                        <span class="show-for-sr">Milestones</span>
                                    </label>
                                </div>
                            <?php } // end else ?>
                        </td>
                        <td>
                            <?php if( $dt_site_notification_defaults[ 'milestones_email' ] ) {
                                print 'required';
                            } else { ?>
                                <div class="switch">
                                    <input class="switch-input" id="milestones_email" type="checkbox"
                                           name="milestones_email" onclick="change_notification_preference('milestones_email');" <?php ( $dt_notification_options[ 'milestones_email' ] ) ? print esc_attr( 'checked' ) : print esc_attr( '' ); ?> >
                                    <label class="switch-paddle" for="milestones_email">
                                        <span class="show-for-sr">Milestones</span>
                                    </label>
                                </div>
                            <?php } // end else ?>
                        </td>
                    </tr>
                    
                    </tbody>
                </table>
            
            
            </div> <!-- End Notifications -->
            
            <?php
            $dt_availability = dt_get_user_option_availability( get_current_user_id() );
            ?>
            <div class="bordered-box" id="availability" data-magellan-target="availability">
                
                <!-- section header-->
                <span class="section-header">Availability</span>
                <hr size="1" style="max-width:100%"/>
                
                
                <!-- Turn on Vacation Reminders -->
                <p>
                    <strong>Set Away:</strong>
                </p>
                <div class="switch large">
                    <input class="switch-input" id="switch0vac" type="checkbox" name="switch0vac" onclick="change_availability();" <?php ( $dt_availability ) ? print esc_attr( 'checked' ) : print esc_attr( '' ); ?>/>
                    <label class="switch-paddle" for="switch0vac">
                        <span class="show-for-sr">Enable</span>
                        <span class="switch-active" aria-hidden="true">On</span>
                        <span class="switch-inactive" aria-hidden="false">Off</span>
                    </label>
                </div>
                
                
                <?php // TODO: Add scheduling and history of availability ?>
                <!-- List of past, present, and future vacations scheduled
                <p>
                    <strong>Schedule Away: </strong>
                </p>
                <p>
                    <button class="button" onclick="" data-open="add-away"><i class="fi-pencil"></i>
                        Add
                    </button>
                </p>
                <table class="hover stack-for-small striped">
                    <thead>
                    <tr>
                        <td>Begin Date</td>
                        <td>End Date</td>
                        <td>Status</td>
                        <td></td>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td>2017-02-24</td>
                        <td>2017-03-04</td>
                        <td>Scheduled</td>
                        <td>
                            <button class="hollow button tiny alert"><i class="fi-x"></i> Delete</button>
                        </td>
                    </tr>
                    <tr>
                        <td>2017-02-24</td>
                        <td>2017-03-04</td>
                        <td>Active</td>
                        <td>
                            <button class="hollow button tiny alert"><i class="fi-x"></i> Delete</button>
                        </td>
                    </tr>
                    <tr>
                        <td>2017-02-24</td>
                        <td>2017-03-04</td>
                        <td>Completed</td>
                        <td>
                            <button class="hollow button tiny alert"><i class="fi-x"></i> Delete</button>
                        </td>
                    </tr>
                    </tbody>
                
                </table>
            
            </div> <!-- End Availability -->
        
        
        </div>
        
        <div class="reveal" id="add-away" data-reveal>
            <button class="close-button" data-close aria-label="Close modal" type="button">
                <span aria-hidden="true">&times;</span>
            </button>
            <h2>Add</h2>
            
            <div class="row column medium-12">
                
                
                <table class="table">
                    <thead>
                    <tr>
                        <th>Start date&nbsp;
                            <a href="#" class="button tiny" id="dp4" data-date-format="yyyy-mm-dd"
                               data-date="2012-02-20">Change</a>
                        </th>
                        <th>End date&nbsp;
                            <a href="#" class="button tiny" id="dp5" data-date-format="yyyy-mm-dd"
                               data-date="2012-02-25">Change</a>
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td id="startDate">2012-02-20</td>
                        <td id="endDate">2012-02-25</td>
                    </tr>
                    </tbody>
                </table>
                <div class="alert alert-box" style="display:none;" id="alert"><strong>Oh snap!</strong>
                </div>
                <button class="button">Add New Vacation</button>
            </div>
        
        </div>
    
    </div> <!-- end #inner-content -->

</div> <!-- end #content -->

<?php get_footer(); ?>
