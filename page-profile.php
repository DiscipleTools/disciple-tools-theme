<?php declare(strict_types=1); ?>
<?php get_header(); ?>

<?php dt_print_breadcrumbs( null, __( "Profile" ) ); ?>

    <div id="content">

        <div id="inner-content" class="row">

            <aside class="large-4 medium-4 columns padding-bottom">

                <ul class="vertical tabs" data-tabs id="profile-nav" data-deep-link="true" data-update-history="true" data-deep-link-smudge="true" data-deep-link-smudge="500">
                    <li class="tabs-title is-active"><a href="#profile-panel2v">Profile</a></li>
                    <li class="tabs-title"><a href="#profile-panel3v">Vacation</a></li>
                    <li class="tabs-title"><a href="#profile-panel4v">Team</a></li>
                    <li class="tabs-title"><a href="#profile-panel5v">Dashboard</a></li>
                    <li class="tabs-title"><a href="#profile-panel6v">Notifications</a></li>
                    <li class="tabs-title"><a href="#profile-panel7v">Reports</a></li>
                </ul>


            </aside>

            <main id="main" class="large-8 medium-8 columns" role="main">

                <div class="tabs-content" data-tabs-content="profile-nav">

                    <!-- Panel 2 -->

                    <div class="tabs-panel" id="profile-panel2v" style="padding:0;">


                        <ul class="tabs" data-tabs id="edit-profile-tabs">
                            <li class="tabs-title is-active"><a href="#edit-profile-panel1" aria-selected="true">Profile</a></li>
                            <li class="tabs-title"><a href="#edit-profile-panel2">Edit</a></li>
                        </ul>
                        <div class="tabs-content" data-tabs-content="edit-profile-tabs">
                            <div class="tabs-panel is-active" id="edit-profile-panel1">

                                <h2>Profile</h2>

                                <table class="form-table">
                                    <thead>
                                        <tr>
                                            <td></td>
                                            <td></td>
                                        </tr>
                                    </thead>

                                    <?php $current_user = get_user_by( 'id', get_current_user_id() ); ?>

                                    <tbody>
                                        <tr>
                                            <td>Username</td>
                                            <td><?php echo $current_user->display_name; ?></td>
                                        </tr>
                                        <tr>
                                            <td>First Name</td>
                                            <td><?php echo $current_user->user_firstname; ?></td>
                                        </tr>
                                        <tr>
                                            <td>Last Name</td>
                                            <td><?php echo $current_user->user_lastname; ?></td>
                                        </tr>
                                        <tr>
                                            <td>Nickname</td>
                                            <td><?php echo $current_user->nickname; ?></td>
                                        </tr>
                                        <tr>
                                            <td>Email</td>
                                            <td><?php echo $current_user->user_email; ?></td>
                                        </tr>
                                        <tr>
                                            <td>Phone</td>
                                            <td>x</td>
                                        </tr
                                        <tr>
                                            <td>Twitter</td>
                                            <td>x</td>
                                        </tr>
                                        <tr>
                                            <td>Biographical Info</td>
                                            <td><?php echo $current_user->description; ?></td>
                                        </tr>
                                        <tr>
                                            <td>Profile Picture</td>
                                            <td><?php echo get_avatar( $current_user->ID ); ?></td>
                                        </tr>

                                    </tbody>
                                </table>




                            </div>
                            <div class="tabs-panel" id="edit-profile-panel2">

                                Username:<br>
                                <input type="text" placeholder="Username" class="regular-text">
                                First Name:<br>
                                <input type="text" placeholder="First Name" class="regular-text">
                                Last Name:<br>
                                <input type="text" placeholder="Last Name" class="regular-text">
                                Nickname:<br>
                                <input type="text" placeholder="Nickname" class="regular-text">
                                Email:<br>
                                <input type="text" placeholder="Email" class="regular-text">
                                Phone:<br>
                                <input type="text" placeholder="Phone" class="regular-text">
                                Biographical Info:<br>
                                <input type="text" placeholder="Biographical Info" class="regular-text">
                                Profile Picture:<br>
                                <label for="exampleFileUpload" class="button">Upload File</label>
                                <input type="file" id="exampleFileUpload" class="show-for-sr"><br>
                                <button type="submit" value="Update" class="button">Update</button>

                            </div>
                        </div>

                    </div> <!-- end panel 2-->

                    <!-- Panel 3 -->

                    <div class="tabs-panel" id="profile-panel3v" style="padding:0;">

                        <ul class="tabs" data-tabs id="vacation-tabs">
                            <li class="tabs-title is-active"><a href="#vacation-panel1" aria-selected="true">Vacation</a></li>
                            <li class="tabs-title"><a href="#vacation-panel2" aria-selected="true">Add</a></li>
                        </ul>

                        <div class="tabs-content" data-tabs-content="vacation-tabs">
                            <div class="tabs-panel is-active" id="vacation-panel1">

                                <h2>Vacation Settings</h2>

                                <p>
                                    <strong>Enable Vacation Settings: </strong>
                                <div class="switch">

                                    <input class="switch-input" id="switch0vac" type="checkbox" name="switch0vac">
                                    <label class="switch-paddle" for="switch0vac">
                                        <span class="show-for-sr">Enable</span>
                                    </label>
                                </div>
                                </p>
                                <p>
                                    <strong>Active Vacations Scheduled: </strong>
                                </p>
                                <table class="form-table">
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


                            </div>
                            <div class="tabs-panel" id="vacation-panel2">

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
                        </div>



                    </div> <!-- end panel 3-->

                    <!-- Panel 4 -->

                    <div class="tabs-panel" id="profile-panel4v" style="padding:0;">

                        <ul class="tabs" data-tabs id="team-tabs">
                            <li class="tabs-title is-active"><a href="#team-panel1" aria-selected="true">Team</a></li>
                        </ul>

                        <div class="tabs-content" data-tabs-content="team-tabs">
                            <div class="tabs-panel is-active" id="team-panel1">

                                <h2>Team</h2>
                                <p>Team Name: Team 1</p>
                                <div class="callout" >
                                    <img src="http://placehold.it/150x150/1779ba/ffffff" />
                                    <span>Chris Wynn</span>
                                    <button class="button float-right">Send Message</button>
                                </div>
                                <div class="callout" >
                                    <img src="http://placehold.it/150x150/1779ba/ffffff" />
                                    <span>Chris Wynn</span>
                                    <button class="button float-right">Send Message</button>
                                </div>
                                <div class="callout" >
                                    <img src="http://placehold.it/150x150/1779ba/ffffff" />
                                    <span>Chris Wynn</span>
                                    <button class="button float-right">Send Message</button>
                                </div>

                            </div>
                        </div>



                    </div><!-- end panel 4 -->

                    <!-- Panel 5 -->

                    <div class="tabs-panel" id="profile-panel5v" style="padding:0;" >

                        <ul class="tabs" data-tabs id="dashboard-tabs" data-deep-link="true" >
                            <li class="tabs-title is-active"><a href="#dashboard-panel1" aria-selected="true">Dashboard</a></li>
                        </ul>

                        <div class="tabs-content" data-tabs-content="dashboard-tabs">
                            <div class="tabs-panel is-active" id="dashboard-panel1">

                                <h3>Elements</h3>
                                <p>You can add or remove elements from your dashboard.</p>
                                <table class="form-table">
                                    <thead>
                                    <tr>
                                        <td>Element</td>
                                        <td>Description</td>
                                        <td>Enable/Disable</td>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr>
                                        <td>Quick Update</td>
                                        <td>Quick update box to add notes from the dashboard to contacts.</td>
                                        <td>
                                            <div class="switch">
                                                <input class="switch-input" id="e-switch1" type="checkbox" name="switch1" checked>
                                                <label class="switch-paddle" for="e-switch1">
                                                    <span class="show-for-sr">Enable</span>
                                                </label>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Critical Path</td>
                                        <td>Gives high level summary for the critical path of the project.</td>
                                        <td>
                                            <div class="switch">
                                                <input class="switch-input" id="e-switch2" type="checkbox" name="switch2" checked>
                                                <label class="switch-paddle" for="e-switch2">
                                                    <span class="show-for-sr">Enable</span>
                                                </label>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Team List</td>
                                        <td>List of team members.</td>
                                        <td>
                                            <div class="switch">
                                                <input class="switch-input" id="e-switch3" type="checkbox" name="switch3">
                                                <label class="switch-paddle" for="e-switch3">
                                                    <span class="show-for-sr">Enable</span>
                                                </label>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Activity History</td>
                                        <td>Your most recent activty</td>
                                        <td>
                                            <div class="switch">
                                                <input class="switch-input" id="e-switch4" type="checkbox" name="switch4" checked>
                                                <label class="switch-paddle" for="e-switch4">
                                                    <span class="show-for-sr">Enable</span>
                                                </label>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Recent Prayer Posts</td>
                                        <td>Recent prayer posts.</td>
                                        <td>
                                            <div class="switch">
                                                <input class="switch-input" id="e-switch5" type="checkbox" name="switch5" checked>
                                                <label class="switch-paddle" for="e-switch5">
                                                    <span class="show-for-sr">Enable</span>
                                                </label>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Recent Project Posts</td>
                                        <td>Recent posts about the project.</td>
                                        <td>
                                            <div class="switch">
                                                <input class="switch-input" id="e-switch6" type="checkbox" name="switch6">
                                                <label class="switch-paddle" for="e-switch6">
                                                    <span class="show-for-sr">Enable</span>
                                                </label>
                                            </div>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>

                                <hr>

                                <h3>Views</h3>
                                <p>You can choose the views available in the dashboard to filter your contact lists.</p>
                                <table class="form-table">
                                    <thead>
                                    <tr>
                                        <td>View Dropdown</td>
                                        <td>Description</td>
                                        <td>Enable/Disable</td>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr>
                                        <td>All My Contacts</td>
                                        <td>Shows all contacts that are assigned to me.</td>
                                        <td>
                                            <div class="switch">
                                                <input class="switch-input" id="switch1" type="checkbox" name="switch1" checked>
                                                <label class="switch-paddle" for="switch1">
                                                    <span class="show-for-sr">Enable</span>
                                                </label>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>All Team Contacts</td>
                                        <td>Shows all contacts that are assigned to me.</td>
                                        <td>
                                            <div class="switch">
                                                <input class="switch-input" id="switch1b" type="checkbox" name="switch1b" checked>
                                                <label class="switch-paddle" for="switch1b">
                                                    <span class="show-for-sr">Enable</span>
                                                </label>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>By Location</td>
                                        <td>Shows all contacts by location</td>
                                        <td>
                                            <div class="switch">
                                                <input class="switch-input" id="switch2" type="checkbox" name="switch2" checked>
                                                <label class="switch-paddle" for="switch2">
                                                    <span class="show-for-sr">Enable</span>
                                                </label>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Needs Update</td>
                                        <td>Contacts that need updates</td>
                                        <td>
                                            <div class="switch">
                                                <input class="switch-input" id="switch3" type="checkbox" name="switch3" checked>
                                                <label class="switch-paddle" for="switch3">
                                                    <span class="show-for-sr">Enable</span>
                                                </label>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Active Contacts</td>
                                        <td>Shows most active contacts</td>
                                        <td>
                                            <div class="switch">
                                                <input class="switch-input" id="switch4" type="checkbox" name="switch4" checked>
                                                <label class="switch-paddle" for="switch4">
                                                    <span class="show-for-sr">Enable</span>
                                                </label>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Recent Contacts</td>
                                        <td>List of most recently modified contacts.</td>
                                        <td>
                                            <div class="switch">
                                                <input class="switch-input" id="switch5" type="checkbox" name="switch5" checked>
                                                <label class="switch-paddle" for="switch5">
                                                    <span class="show-for-sr">Enable</span>
                                                </label>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Strong</td>
                                        <td>Contacts your have actively been engaging</td>
                                        <td>
                                            <div class="switch">
                                                <input class="switch-input" id="switch6" type="checkbox" name="switch6" checked>
                                                <label class="switch-paddle" for="switch6">
                                                    <span class="show-for-sr">Enable</span>
                                                </label>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Fading</td>
                                        <td>Contacts who are slipping</td>
                                        <td>
                                            <div class="switch">
                                                <input class="switch-input" id="switch6b" type="checkbox" name="switch6b" checked>
                                                <label class="switch-paddle" for="switch6b">
                                                    <span class="show-for-sr">Enable</span>
                                                </label>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Weak</td>
                                        <td>Contacts that have gone stale with no activity.</td>
                                        <td>
                                            <div class="switch">
                                                <input class="switch-input" id="switch6c" type="checkbox" name="switch6c">
                                                <label class="switch-paddle" for="switch6c">
                                                    <span class="show-for-sr">Enable</span>
                                                </label>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Today's Top Needs</td>
                                        <td>Contacts that have "New Assigned", "Update Needed", or "Fading".</td>
                                        <td>
                                            <div class="switch">
                                                <input class="switch-input" id="switch6d" type="checkbox" name="switch6d" checked>
                                                <label class="switch-paddle" for="switch6d">
                                                    <span class="show-for-sr">Enable</span>
                                                </label>
                                            </div>
                                        </td>
                                    </tr>

                                    </tbody>
                                </table>

                            </div>



                        </div>

                    </div> <!-- end panel 5 -->

                    <!-- Panel 6 -->

                    <div class="tabs-panel" id="profile-panel6v" style="padding:0;" >

                        <ul class="tabs" data-tabs id="notifications-tabs" data-deep-link="true" >
                            <li class="tabs-title is-active"><a href="#notifications-panel1" aria-selected="true">Notifications</a></li>
                        </ul>

                        <div class="tabs-content" data-tabs-content="notifications-tabs">

                            <div class="tabs-panel is-active" id="notifications-panel1">

                                <h2>Notifications</h2>
                                <table class="form-table">
                                    <thead>
                                    <tr>
                                        <td>Method</td>
                                        <td>Timing</td>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr>
                                        <td>New Contacts</td>
                                        <td>
                                            <fieldset>
                                                <label for="new-contacts-none">None</label>
                                                <input type="radio" name="new-contacts" id="new-contacts-none">
                                                <label for="new-contacts-immediately">Immediately</label>
                                                <input type="radio" name="new-contacts" id="new-contacts-immediately" checked>
                                                <label for="new-contacts-daily">Daily</label>
                                                <input type="radio" name="new-contacts" id="new-contacts-daily">
                                                <label for="new-contacts-weekly">Weekly</label>
                                                <input type="radio" name="new-contacts" id="new-contacts-weekly">
                                            </fieldset>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>New Messages</td>
                                        <td>
                                            <fieldset>
                                                <label for="new-messages-none">None</label>
                                                <input type="radio" name="new-messages" id="new-messages-none">
                                                <label for="new-messages-immediately">Immediately</label>
                                                <input type="radio" name="new-messages" id="new-messages-immediately" checked>
                                                <label for="new-messages-daily">Daily</label>
                                                <input type="radio" name="new-messages" id="new-messages-daily">
                                                <label for="new-messages-weekly">Weekly</label>
                                                <input type="radio" name="new-messages" id="new-messages-weekly">
                                            </fieldset>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Activity on my Contacts</td>
                                        <td>
                                            <fieldset>
                                                <label for="activity-none">None</label>
                                                <input type="radio" name="activity" id="activity-none">
                                                <label for="activity-immediately">Immediately</label>
                                                <input type="radio" name="activity" id="activity-immediately" checked>
                                                <label for="activity-daily">Daily</label>
                                                <input type="radio" name="activity" id="activity-daily">
                                                <label for="activity-weekly">Weekly</label>
                                                <input type="radio" name="activity" id="activity-weekly">
                                            </fieldset>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Notifications</td>
                                        <td>
                                            <fieldset>
                                                <label for="notifications-none">None</label>
                                                <input type="radio" name="notifications" id="notifications-none">
                                                <label for="notifications-immediately">Immediately</label>
                                                <input type="radio" name="notifications" id="notifications-immediately" checked>
                                                <label for="notifications-daily">Daily</label>
                                                <input type="radio" name="notifications" id="notifications-daily">
                                                <label for="notifications-weekly">Weekly</label>
                                                <input type="radio" name="notifications" id="notifications-weekly">
                                            </fieldset>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>New Project Updates</td>
                                        <td>
                                            <fieldset>
                                                <label for="project-updates-none">None</label>
                                                <input type="radio" name="project-updates" id="project-updates-none">
                                                <label for="project-updates-immediately">Immediately</label>
                                                <input type="radio" name="project-updates" id="project-updates-immediately">
                                                <label for="project-updates-daily">Daily</label>
                                                <input type="radio" name="project-updates" id="project-updates-daily">
                                                <label for="project-updates-weekly">Weekly</label>
                                                <input type="radio" name="project-updates" id="project-updates-weekly" checked>
                                            </fieldset>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Prayer Posts</td>
                                        <td>
                                            <fieldset>
                                                <label for="prayer-posts-none">None</label>
                                                <input type="radio" name="prayer-posts" id="prayer-posts-none">
                                                <label for="prayer-posts-immediately">Immediately</label>
                                                <input type="radio" name="prayer-posts" id="prayer-posts-immediately">
                                                <label for="prayer-posts-daily">Daily</label>
                                                <input type="radio" name="prayer-posts" id="prayer-posts-daily">
                                                <label for="prayer-posts-weekly">Weekly</label>
                                                <input type="radio" name="prayer-posts" id="prayer-posts-weekly" checked>
                                            </fieldset>
                                        </td>
                                    </tr>

                                    </tbody>
                                </table>

                            </div>


                        </div>

                    </div> <!-- end panel 6 -->

                    <!-- Panel 7 -->

                    <div class="tabs-panel" id="profile-panel7v" style="padding:0;" >

                        <ul class="tabs" data-tabs id="preferences-tabs" data-deep-link="true" >
                            <li class="tabs-title is-active"><a href="#reports-panel1" aria-selected="true">Reports</a></li>
                        </ul>

                        <div class="tabs-content" data-tabs-content="reports-tabs">

                            <div class="tabs-panel is-active" id="reports-panel1">

                                <h2>Reports</h2>
                                <table class="form-table">
                                    <thead>
                                    <tr>
                                        <td>Name</td>
                                        <td>Description</td>
                                        <td>Enable/Disable</td>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr>
                                        <td>My Activity</td>
                                        <td>A chart of my activity with contacts over time.</td>
                                        <td>
                                            <div class="switch">
                                                <input class="switch-input" id="switch14" type="checkbox" name="switch14">
                                                <label class="switch-paddle" for="switch14">
                                                    <span class="show-for-sr">Enable</span>
                                                </label>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Project Overview</td>
                                        <td>The high-level critical path stats for the project.</td>
                                        <td>
                                            <div class="switch">
                                                <input class="switch-input" id="switch15" type="checkbox" name="switch15">
                                                <label class="switch-paddle" for="switch15">
                                                    <span class="show-for-sr">Enable</span>
                                                </label>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Media Overview</td>
                                        <td>Stats for the project media campaign.</td>
                                        <td>
                                            <div class="switch">
                                                <input class="switch-input" id="switch16" type="checkbox" name="switch16">
                                                <label class="switch-paddle" for="switch16">
                                                    <span class="show-for-sr">Enable</span>
                                                </label>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Engagement Overview</td>
                                        <td>Stats for the face to face engagement.</td>
                                        <td>
                                            <div class="switch">
                                                <input class="switch-input" id="switch17" type="checkbox" name="switch17">
                                                <label class="switch-paddle" for="switch17">
                                                    <span class="show-for-sr">Enable</span>
                                                </label>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Groups and Churches Overview</td>
                                        <td>Stats for the multiplication of groups and churches.</td>
                                        <td>
                                            <div class="switch">
                                                <input class="switch-input" id="switch18" type="checkbox" name="switch18" checked>
                                                <label class="switch-paddle" for="switch18">
                                                    <span class="show-for-sr">Enable</span>
                                                </label>
                                            </div>
                                        </td>
                                    </tr>

                                    </tbody>
                                </table>

                            </div>

                        </div>

                    </div> <!-- end panel 7 -->
                </div>

            </main> <!-- end #main -->

        </div> <!-- end #inner-content -->

    </div> <!-- end #content -->

<script>
    jQuery(document).ready(function($) {
        jQuery( "fieldset" ).controlgroup();
        jQuery(':radio').checkboxradio({icon: false});
    });
</script>


<?php get_footer(); ?>
