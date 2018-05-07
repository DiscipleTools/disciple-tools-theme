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

                        <ul id="metrics-sidemenu" class="vertical menu accordion-menu" data-accordion-menu data-multi-open>

                            <?php
                            // @codingStandardsIgnoreLine
                            echo apply_filters( 'dt_metrics_top_menu', '' );
                            ?>

                            <li >
                                <a href=""><?php esc_html_e( 'My Contacts', 'disciple_tools' )?></a>
                                <ul class="menu vertical nested">
                                    <?php
                                    // @codingStandardsIgnoreLine
                                    echo apply_filters( 'dt_metrics_menu_my_contacts', '' );
                                    ?>
                                </ul>
                            </li>
                            <li>
                                <a href=""><?php esc_html_e( 'My Groups', 'disciple_tools' )?></a>
                                <ul class="menu vertical nested">
                                    <?php
                                    // @codingStandardsIgnoreLine
                                    echo apply_filters( 'dt_metrics_menu_my_groups', '' );
                                    ?>
                                </ul>
                            </li>

                            <?php
                            // @codingStandardsIgnoreLine
                            echo apply_filters( 'dt_metrics_bottom_menu', '' );
                            ?>

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

