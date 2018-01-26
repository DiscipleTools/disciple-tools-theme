<?php
/*
Template Name: Metrics
*/
?>

<?php get_header(); ?>

    <div id="content">

        <div id="inner-content" class="grid-x grid-margin-x grid-margin-y">

            <div class="large-3 medium-3 small-12 cell ">

                <section id="" class="medium-12 cell">

                    <div class="bordered-box">

                        <ul id="metrics-sidemenu" class="vertical menu accordion-menu" data-accordion-menu data-submenu-toggle="true">
                            <li >
                                <a onclick="show_critical_path()"><?php esc_html_e( 'Critical Path', 'disciple_tools' )?></a>
                                <!--<ul class="menu vertical nested" id="critical-path-menu">
                                    <li class="top-border">
                                        <a onclick="show_critical_path_prayer()">Prayer</a>
                                    </li>
                                    <li class="top-border">
                                        <a onclick="show_critical_path_outreach()">Outreach</a>
                                    </li>
                                    <li class="top-border">
                                        <a onclick="show_critical_path_fup()">Follow-up</a>
                                    </li>
                                    <li class="top-border">
                                        <a onclick="show_critical_path_multiplication()">Multiplication</a>
                                    </li>
                                </ul>-->
                            </li>
                            <!--<li class="top-border">
                                <a onclick="show_contacts()">Contacts</a>
                            </li>
                            <li class="top-border">
                                <a onclick="show_groups()">Groups</a>
                            </li>
                            <li class="top-border">
                                <a onclick="show_workers()">Workers</a>
                            </li>
                            <li class="top-border">
                                <a onclick="show_locations()">Locations</a>
                            </li>
                            <li class="top-border">
                                <a onclick="show_pace()">Pace</a>
                                <ul class="menu vertical nested">
                                    <li><a onclick="show_fake_chart('Contacts+Chart');">Contacts</a></li>
                                    <li><a onclick="show_fake_chart('Groups+Chart');">Groups</a></li>
                                </ul>
                            </li>-->

                        </ul>

                    </div>

                </section>

            </div>

            <div class="large-9 medium-9 small-12 cell ">

                <section id="" class="medium-12 cell">

                    <div class="bordered-box">

                        <div id="chart"></div><!-- Container for charts -->

                    </div>

                </section>

            </div>

        </div> <!-- end #inner-content -->

    </div> <!-- end #content -->

<?php get_footer(); ?>
