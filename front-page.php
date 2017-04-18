<?php require_once (get_template_directory() . '/assets/functions/page-front-page.php'); ?>

<?php get_header(); ?>

<div id="content">

    <div id="inner-content" class="row">

            <main id="main" class="large-8 medium-8 columns " role="main">

                <div class="show-for-small-only">
                    <section class="block">
                        <?php include ('searchform.php'); ?>
                    </section>
                </div>


                <div class="callout alert" >
                    <i class="fi-plus"> New </i>
                    <a href="#">Mohammed Kali</a>
                    <span class="float-right">
                        <button type="submit" name="Accept" value="Accept" class="button small ">Accept</button>
                        <button type="submit" name="Decline" value="Decline" class="button small ">Decline</button>
                    </span>


                </div>

                <div class="callout alert" >
                    <i class="fi-plus"> New </i>
                    <a href="#">Mohammed Kali</a>
                    <span class="float-right">
                        <button type="submit" name="Accept" value="Accept" class="button small">Accept</button>
                        <button type="submit" name="Decline" value="Decline" class="button small ">Decline</button>
                    </span>
                </div>

                <div class="callout warning" >
                    <i class="fi-alert"> Update Needed </i>
                    <a href="#">Mohammed Kali</a>
                    <span class="float-right">
                        <button type="submit" name="Update" value="Update" class="button small ">Update</button>
                    </span>

                </div>

                <div class="callout warning" >
                    <i class="fi-alert"> Update Needed </i>
                    <a href="#">Mohammed Kali</a>
                    <span class="float-right">
                        <button type="submit" name="Update" value="Update" class="button small ">Update</button>
                    </span>
                </div>


                <section class="block">
                    <h4>Contacts</h4>
                    <ul class="tabs" data-tabs id="example-tabs">
                        <li class="tabs-title is-active"><a href="#panel1" aria-selected="true">Mine</a></li>
                        <li class="tabs-title"><a href="#panel2">My Team</a></li>
                        <li class="tabs-title"><a href="#panel3">By Location</a></li>
                    </ul>
                    <div class="tabs-content" data-tabs-content="example-tabs">
                        <div class="tabs-panel is-active" id="panel1">

                            <?php
                            $args = array(
                                'post_type' => 'contacts',
                                'posts_per_page' => 5,
                                'meta_key' => 'assigned_to',
                                'meta_value' => 'user-'. get_current_user_id(),
                            );
                            $query = new WP_Query( $args );
                            ?>
                            <?php if ( $query->have_posts() ) : while ( $query->have_posts() ) : $query->the_post(); ?>

                                <!-- To see additional archive styles, visit the /parts directory -->
                                <?php get_template_part( 'parts/loop', 'contacts' ); ?>


                            <?php endwhile; ?>

                                <?php disciple_tools_page_navi(); ?>

                            <?php else : ?>

                                <?php get_template_part( 'parts/content', 'missing' ); ?>

                            <?php endif; ?>

                        </div>
                        <div class="tabs-panel" id="panel2">

                            <?php
                            $args = array(
                                'post_type' => 'contacts',
                                'posts_per_page' => 5,
                                'meta_query' => dt_get_team_contacts(get_current_user_id()),
                            );
                            $query2 = new WP_Query( $args );
                            ?>
                            <?php if ( $query2->have_posts() ) : while ( $query2->have_posts() ) : $query2->the_post(); ?>

                                <!-- To see additional archive styles, visit the /parts directory -->
                                <li><a href="<?php the_permalink() ?>" rel="link" title="<?php the_title_attribute(); ?>"><?php the_title(); ?> </a> <span class="float-right small grey">(<?php dt_get_assigned_name(get_the_ID() ); ?>)</span> </li>


                            <?php endwhile; ?>

                                <?php disciple_tools_page_navi(); ?>

                            <?php else : ?>

                                <?php get_template_part( 'parts/content', 'missing' ); ?>

                            <?php endif; ?>

                        </div>
                        <div class="tabs-panel" id="panel2">
                            list of contacts by location
                        </div>
                    </div>
                </section>

                <div class="row">

                    <div class="medium-6 columns">
                        <section class="block">
                            <?php dt_chart_dounut(); ?>
                            <div id="chart_dounut_div" style="width: 100%; " ></div>
                        </section>
                    </div>

                    <div class="medium-6 columns">
                        <section class="block">
                            <h4>Project News</h4>
                        </section>
                    </div>

                </div>

            </main> <!-- end #main -->

            <aside class="large-4 medium-4 columns ">



                <section class="block">
                    <!-- Project Stats -->
                    <h4>Quick Update</h4>
                    <form id="post-comment-form">
                        <div>
                            <label for="post-submission-title">
                                <?php _e( 'Select Contact', 'disciple_tools' ); ?>
                            </label>
                            <select name="post-comment-id" id="post-comment-id" required aria-required="true">
                                    <option value="65">Abe New</option>
                            </select>
                        </div>

                        <div>
                            <label for="post-submission-content">
                                <?php _e( 'Content', 'disciple_tools' ); ?>
                            </label>
                            <textarea rows="3" cols="20" name="post-comment-content" id="post-comment-content"></textarea>
                        </div>
                        <input type="submit" value="<?php esc_attr_e( 'Submit', 'disciple_tools'); ?>" class="button small">
                    </form>
                </section>




                <section class="block">
                <!-- Project Stats -->
                    <h4>Critical Path</h4>
                    <?php  if (class_exists('Disciple_Tools')) {
                        require_once ( DISCIPLE_TOOLS_DIR. '/includes/admin/reports-funnel.php');
                        require_once( DISCIPLE_TOOLS_DIR. '/includes/factories/class-page-factory.php'); // Factory class for page building
                        $reports = Disciple_Tools_Funnel_Reports::instance();
                        echo $reports->critical_path_stats() ;
                    } ?>
                </section>


            </aside> <!-- end #aside -->

    </div> <!-- end #inner-content -->

</div> <!-- end #content -->

<?php get_footer(); ?>
