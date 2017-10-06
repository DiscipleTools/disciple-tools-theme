<?php
/*
Template Name: Metrics
*/
?>

<?php get_header(); ?>

<?php
dt_print_breadcrumbs(
    [
        [ home_url( '/' ), __( "Dashboard" ) ],
        [ home_url( '/' ) . "metrics", __( "Metrics" ) ],
    ],
    get_the_title(),
    false
); ?>

    <div id="content">

        <div id="inner-content" class="grid-x grid-margin-x">

            <div class="large-3 medium-3 small-12 cell ">

                <section id="" class="medium-12 cell">

                    <div class="bordered-box">

                        <ul id="metrics-sidemenu" class="vertical menu accordion-menu" data-accordion-menu>
                            <li>
                                <!-- // todo: temporary user of show_fake_char function while designing UI/UX-->
                                <a onclick="show_critical_path();">Critical Path</a>

                            </li>
                            <li class="top-border">
                                <a href="#">Pace</a>
                                <ul class="menu vertical nested">
                                    <li><a onclick="show_fake_chart('Contacts+Chart');">Contacts</a></li>
                                    <li><a onclick="show_fake_chart('Groups+Chart');">Groups</a></li>
                                </ul>
                            </li>
                            <li class="top-border">
                                <a onclick="show_fake_chart('Workers+Chart');">Workers</a>
                            </li>
                            <li class="top-border">
                                <a href="#">Groups</a>
                                <ul class="menu vertical nested">
                                    <li><a onclick="show_fake_chart('Bible+Studies+Chart');">Bible Studies</a></li>
                                    <li><a onclick="show_fake_chart('Churches+Chart');">Churches</a></li>
                                    <li><a onclick="show_fake_chart('Generations+Chart');">Generations</a></li>
                                </ul>
                            </li>
                            <li class="top-border">
                                <a href="#">Contacts</a>
                                <ul class="menu vertical nested">
                                    <li><a onclick="show_fake_chart('Seeking+Chart');">Seeking</a></li>
                                    <li><a onclick="show_fake_chart('Training+Chart');"">Training</a></li>
                                    <li><a onclick="show_fake_chart('Generations+Chart');">Generations</a></li>
                                </ul>
                            </li>
                            <li class="top-border">
                                <a href="#">Outreach</a>
                                <ul class="menu vertical nested">
                                    <li><a onclick="show_fake_chart('Media+by+Sources+Chart');">Media by Sources</a></li>
                                    <li><a onclick="show_fake_chart('Generations+Chart');">Generations</a></li>
                                </ul>
                            </li>
                            <li class="top-border">
                                <a href="#">Prayer</a>
                                <ul class="menu vertical nested">
                                    <li><a onclick="show_fake_chart('Prayer+Network+Chart');">Prayer Network</a></li>
                                    <li><a onclick="show_fake_chart('Prayer+Interactions+Chart');">Prayer Interactions</a></li>
                                    <li><a onclick="show_fake_chart('Channel+Chart');">by Channels</a></li>
                                </ul>
                            </li>
                            <li class="top-border">
                                <a onclick="show_fake_chart('Locations+Chart');">Locations</a>
                            </li>

                        </ul>

                    </div>

                </section>

            </div>

            <div class="large-9 medium-9 small-12 cell ">

                <section id="" class="medium-12 cell">

                    <div class="bordered-box">
                        <script>
                            jQuery(document).ready(function() {
                                show_critical_path()
                            })
                        </script>

                        <div id="chart"></div><!-- Container for charts -->

                    </div>

                </section>

            </div>

        </div> <!-- end #inner-content -->

    </div> <!-- end #content -->

<?php get_footer(); ?>
