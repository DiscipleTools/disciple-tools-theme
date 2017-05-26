<?php get_header(); ?>

    <div id="content">

        <div id="inner-content" class="row">

            <!-- Breadcrumb Navigation-->
            <nav aria-label="You are here:" role="navigation">
                <ul class="breadcrumbs">
                    <li><a href="/">Dashboard</a></li>
                    <li>
                        <span class="show-for-sr">Current: </span> Profile
                    </li>
                </ul>
            </nav>

            <aside class="large-4 medium-4 columns">

                    <ul class="vertical tabs" data-tabs id="example-tabs">
                        <li class="tabs-title is-active"><a href="#panel1v" aria-selected="true">Profile</a></li>
                        <li class="tabs-title"><a href="#panel2v">Edit Profile</a></li>
                        <li class="tabs-title"><a href="#panel3v">Vacation Settings</a></li>
                    </ul>

            </aside>

            <main id="main" class="large-8 medium-8 columns" role="main">
                    <div class="tabs-content" data-tabs-content="example-tabs">
                        <div class="tabs-panel is-active" id="panel1v">
                            <h2>Profile</h2>
                            <hr>
                            <p><strong>Username:</strong> Text</p>
                            <p><strong>First Name:</strong> Text</p>
                            <p><strong>Last Name:</strong> Text</p>
                            <p><strong>Nickname:</strong> Text</p>
                            <p><strong>Email:</strong> Text</p>
                            <p><strong>Phone:</strong> Text</p>
                            <p><strong>Contact ID:</strong> Text</p>
                            <p><strong>Biographical Info:</strong> Text</p>
                            <p><strong>Profile Picture:</strong> Text</p>
                            <p><strong>User Roles:</strong> Text</p>
                            <p><strong>Teams:</strong> Text</p>
                        </div>
                        <div class="tabs-panel" id="panel2v">
                            <h2>Edit Profile</h2>
                            <hr>
                                <input type="text" placeholder="Username" class="regular-text">
                                <input type="text" placeholder="First Name" class="regular-text">
                                <input type="text" placeholder="Last Name" class="regular-text">
                                <input type="text" placeholder="Nickname" class="regular-text">
                                <input type="text" placeholder="Email" class="regular-text">
                                <input type="text" placeholder="Phone" class="regular-text">
                                <input type="text" placeholder="Biographical Info" class="regular-text">
                                <input type="text" placeholder="Profile Picture" class="regular-text">
                                <button type="submit" value="Update" class="button">Update</button>
                        </div>
                        <div class="tabs-panel" id="panel3v">
                            <h2>Vacation Settings</h2>
                            <hr>
                            <div class="row column medium-6">


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
                                <div class="alert alert-box"  style="display:none;" id="alert">	<strong>Oh snap!</strong>
                                </div>
                            </div>
                            <script>
                                jQuery(document).ready(function($) {
                                    $(document).foundation();


                                    window.prettyPrint && prettyPrint();
                                    $('#dp1').fdatepicker({
                                        format: 'mm-dd-yyyy',
                                        disableDblClickSelection: true
                                    });
                                    $('#dp2').fdatepicker({
                                        closeButton: true
                                    });
                                    $('#dp3').fdatepicker();
                                    $('#dpt').fdatepicker({
                                        format: 'mm-dd-yyyy hh:ii',
                                        disableDblClickSelection: true,
                                        language: 'vi',
                                        pickTime: true
                                    });
                                    // datepicker limited to months
                                    $('#dpMonths').fdatepicker();
                                    // implementation of custom elements instead of inputs
                                    var startDate = new Date(2012, 1, 20);
                                    var endDate = new Date(2012, 1, 25);
                                    $('#dp4').fdatepicker()
                                        .on('changeDate', function (ev) {
                                            if (ev.date.valueOf() > endDate.valueOf()) {
                                                $('#alert').show().find('strong').text('The start date can not be greater then the end date');
                                            } else {
                                                $('#alert').hide();
                                                startDate = new Date(ev.date);
                                                $('#startDate').text($('#dp4').data('date'));
                                            }
                                            $('#dp4').fdatepicker('hide');
                                        });
                                    $('#dp5').fdatepicker()
                                        .on('changeDate', function (ev) {
                                            if (ev.date.valueOf() < startDate.valueOf()) {
                                                $('#alert').show().find('strong').text('The end date can not be less then the start date');
                                            } else {
                                                $('#alert').hide();
                                                endDate = new Date(ev.date);
                                                $('#endDate').text($('#dp5').data('date'));
                                            }
                                            $('#dp5').fdatepicker('hide');
                                        });
                                });



                            </script>
                        </div>
                    </div>
                </div>
            </main> <!-- end #main -->

        </div> <!-- end #inner-content -->

    </div> <!-- end #content -->

<?php get_footer(); ?>