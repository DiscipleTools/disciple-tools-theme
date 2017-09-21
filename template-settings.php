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
            
                    <div class="bordered-box hide-for-small-only">
                    
                        <ul class="menu vertical expanded" data-smooth-scroll data-offset="100">
                            <li><a href="#profile">Profile</a></li>
                            <li><a href="#notifications">Notifications</a></li>
                            <li><a href="#availability">Availability</a></li>
                        </ul>
                    
                    </div>
        
                </section>
                
    
            </div>
    
            <div class="large-9 medium-12 small-12 cell ">
        
            
                    <div class="bordered-box" id="profile" data-magellan-target="profile">
                        
                        
                        <button class="float-right" onclick=""><i class="fi-pencil"></i> Edit</button>
                        <span class="section-header">Profile</span>
                        <hr size="1" />
    
    
                        <!--                            <div class="medium-6 cell">-->
                        <!--    -->
                        <!--                                --><?php
                        //                                print '<pre>';
                        //                                print_r( get_user_meta( get_current_user_id() ) );
                        //                                print '</pre>';
                        //                                ?>
                        <!--                            -->
                        <!--                            </div>-->
                        <!--                            <div class="medium-6 cell">-->
                        <!--                                --><?php
                        //                                // The Query
                        //                                $user_query = new WP_User_Query( [
                        //                                        'include' => [ get_current_user_id() ],
                        //                                    ] );
                        //                                $result     = $user_query->get_results();
                        //                                print '<pre>';
                        //                                print_r( $result[ 0 ]->data );
                        //                                print '</pre>';
                        //                                ?>
                        <!--                            </div>-->
                        
                        <div class="grid-x grid-margin-x grid-padding-x grid-padding-y ">
                            
                            <div class="medium-4 cell">
                                
                                <strong>Picture</strong>
                                <p><img src="http://via.placeholder.com/150x150" width="150px" height="150px" /></p>
                                
                                <strong>Email</strong>
                                    <p>test@test.com</p>
                                    <p>test@test.com</p>
                                    <p>test@test.com</p>
                                <strong>Phone</strong>
                                    <p>123-456-7890</p>
                                    <p>123-456-7890</p>
                                    <p>123-456-7890</p>
                                
                                <strong>Address</strong>
                                    <p>address single line</p>
                                    <p>address single line</p>
                                    <p>address single line</p>
                                
                                
                                <strong>Biography</strong>
                                    <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
                            </div>
                            <div class="medium-4 cell">
                                
                                <strong>Names</strong>
                                <p>Username</p>
                                <p>First Name</p>
                                <p>Last Name</p>
                                <p>Nickname</p>
                                <p>Display Your Name As</p>
                                
                                <strong>Social</strong>
                                <p>social profile</p>
                                <p>social profile</p>
                                <p>social profile</p>
                                <p>social profile</p>
                                <p>social profile</p>
                                
                            </div>
                            <div class="medium-4 cell">
                                
                                <strong>Contact Record</strong>
                                    <p><a href="#">Connected contact record</a></p>
                                <strong>Locations</strong>
                                    <p><a href="">Location Name</a></p>
                                    <p><a href="">Location Name</a></p>
                                    <p><a href="">Location Name</a></p>
                                    <p><a href="">Location Name</a></p>
                                <strong>Teams</strong>
                                    <p><a href="#">Team Name</a></p>
                                    <p><a href="#">Team Name</a></p>
                                    <p><a href="#">Team Name</a></p>
                                    <p><a href="#">Team Name</a></p>
                                
                            </div>
                        </div>
                
                </div> <!-- End Profile -->
                
                
                <?php
                // notifications query
                $notification_options = dt_get_notification_options();
                if($notification_options) : // check if notifications are not empty
                ?>
                
                <!-- Begin Notification-->
                <div class="bordered-box" id="notifications" data-magellan-target="notifications">
                    <span class="section-header">Notifications</span>
                    <hr size="1" />
    
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
                            <td>Newly Assigned Contact</td>
                            <td>
                                <div class="switch">
                                    <input class="switch-input" id="assignedWeb" type="checkbox" name="assignedWeb" <?php ($notification_options['new']['web']) ? print 'checked' : print ''; ?> disabled>
                                    <label class="switch-paddle" for="assignedWeb">
                                        <span class="show-for-sr">New Assignments</span>
                                    </label>
                                </div>
                            </td>
                            <td>
                                <div class="switch">
                                    <input class="switch-input" id="assignedEmail" type="checkbox" name="assignedEmail" <?php ($notification_options['new']['email']) ? print 'checked' : print ''; ?> disabled>
                                    <label class="switch-paddle" for="assignedEmail">
                                        <span class="show-for-sr">New Assignments</span>
                                    </label>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>@Mentions</td>
                            <td>
                                <div class="switch">
                                    <input class="switch-input" id="mentionWeb" type="checkbox" name="mentionWeb" <?php ($notification_options['mentions']['web']) ? print 'checked' : print ''; ?> disabled>
                                    <label class="switch-paddle" for="mentionWeb">
                                        <span class="show-for-sr">Mentions on Web</span>
                                    </label>
                                </div>
                            </td>
                            <td>
                                <div class="switch">
                                    <input class="switch-input" id="mentionEmail" type="checkbox" name="mentionEmail" <?php ($notification_options['mentions']['email']) ? print 'checked' : print ''; ?>>
                                    <label class="switch-paddle" for="mentionEmail">
                                        <span class="show-for-sr">Mentions on Email</span>
                                    </label>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>Update Needed</td>
                            <td>
                                <div class="switch">
                                    <input class="switch-input" id="updateWeb" type="checkbox" name="updateWeb" <?php ($notification_options['updates']['web']) ? print 'checked' : print ''; ?> disabled>
                                    <label class="switch-paddle" for="updateWeb">
                                        <span class="show-for-sr">Web Mentions</span>
                                    </label>
                                </div>
                            </td>
                            <td>
                                <div class="switch">
                                    <input class="switch-input" id="updateEmail" type="checkbox" name="updateEmail" <?php ($notification_options['updates']['email']) ? print 'checked' : print ''; ?> >
                                    <label class="switch-paddle" for="updateEmail">
                                        <span class="show-for-sr">Web Mentions</span>
                                    </label>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>Contact info changed</td>
                            <td>
                                <div class="switch">
                                    <input class="switch-input" id="changedWeb" type="checkbox" name="changedWeb" <?php ($notification_options['changes']['web']) ? print 'checked' : print ''; ?>>
                                    <label class="switch-paddle" for="changedWeb">
                                        <span class="show-for-sr">Web Mentions</span>
                                    </label>
                                </div>
                            </td>
                            <td>
                                <div class="switch">
                                    <input class="switch-input" id="changedEmail" type="checkbox" name="changedEmail" <?php ($notification_options['changes']['email']) ? print 'checked' : print ''; ?>>
                                    <label class="switch-paddle" for="changedEmail">
                                        <span class="show-for-sr">Web Mentions</span>
                                    </label>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>Milestones</td>
                            <td>
                                <div class="switch">
                                    <input class="switch-input" id="milestoneWeb" type="checkbox" name="milestoneWeb" <?php ($notification_options['milestones']['web']) ? print 'checked' : print ''; ?>>
                                    <label class="switch-paddle" for="milestoneWeb">
                                        <span class="show-for-sr">Milestones</span>
                                    </label>
                                </div>
                            </td>
                            <td>
                                <div class="switch">
                                    <input class="switch-input" id="milestoneEmail" type="checkbox" name="milestoneEmail" <?php ($notification_options['milestones']['email']) ? print 'checked' : print ''; ?>>
                                    <label class="switch-paddle" for="milestoneEmail">
                                        <span class="show-for-sr">Milestones</span>
                                    </label>
                                </div>
                            </td>
                        </tr>
                        
                        
                        
        
                        </tbody>
                    </table>
                    
                    <?php endif; // notifications options  ?>
                    
                </div> <!-- End Notifications -->
    
    
                <div class="bordered-box" id="availability" data-magellan-target="availability">
                    
                    <!-- section header-->
                    <button class="float-right" onclick="" data-open="add-vacation-notification"><i class="fi-pencil"></i> Add</button>
                    <span class="section-header">Availability</span>
                    <hr size="1" />
    
                    
                    <!-- Turn on Vacation Reminders -->
                    <p>
                        <strong>Enable Vacation Settings: </strong>
                    <div class="switch">
        
                        <input class="switch-input" id="switch0vac" type="checkbox" name="switch0vac">
                        <label class="switch-paddle" for="switch0vac">
                            <span class="show-for-sr">Enable</span>
                        </label>
                    </div>
                    </p>
                    
                    
                    <!-- List of past, present, and future vacations scheduled -->
                    <p>
                        <strong>Active Vacations Scheduled: </strong>
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
                            <td><button class="hollow button tiny alert"><i class="fi-x"></i> Delete</button> </td>
                        </tr>
                        <tr>
                            <td>2017-02-24</td>
                            <td>2017-03-04</td>
                            <td>Active</td>
                            <td><button class="hollow button tiny alert"><i class="fi-x"></i> Delete</button> </td>
                        </tr>
                        <tr>
                            <td>2017-02-24</td>
                            <td>2017-03-04</td>
                            <td>Completed</td>
                            <td><button class="hollow button tiny alert"><i class="fi-x"></i> Delete</button> </td>
                        </tr>
                        </tbody>
    
                    </table>
    
                </div> <!-- End Availability -->
                
    
            </div>
    
            <div class="reveal" id="add-vacation-notification" data-reveal>
                <button class="close-button" data-close aria-label="Close modal" type="button">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h2>Add</h2>
        
                <div class="row column medium-12">
            
            
                    <table class="table">
                        <thead>
                        <tr>
                            <th>Start date&nbsp;
                                <a href="#" class="button tiny" id="dp4" data-date-format="yyyy-mm-dd" data-date="2012-02-20">Change</a>
                            </th>
                            <th>End date&nbsp;
                                <a href="#" class="button tiny" id="dp5" data-date-format="yyyy-mm-dd" data-date="2012-02-25">Change</a>
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
                    <div class="alert alert-box"  style="display:none;" id="alert">    <strong>Oh snap!</strong>
                    </div>
                    <button class="button">Add New Vacation</button>
                </div>
    
            </div>
    
        </div> <!-- end #inner-content -->
    
    </div> <!-- end #content -->

<?php get_footer(); ?>
