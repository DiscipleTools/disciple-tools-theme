<?php get_header(); ?>

<div id="content">

    <div id="inner-content" class="row">

        <main id="main" class="large-8 medium-8 columns " role="main">

            <div class="show-for-small-only">
                <section class="block">
                    <?php include ('searchform.php'); ?>
                </section>
            </div>

            <!-- Begin Assigned Contacts -->
            <?php if ( ! empty($_POST['response'] )) { dt_update_overall_status($_POST); } ?>
            <?php
            /* Loop for the new assigned contacts */
            $assigned_to = 'user-' . get_current_user_id();
            $args = array(
                'post_type' => 'contacts',
                'nopaging' => true,
                'meta_query' =>  array(
                    'relation' => 'AND', // Optional, defaults to "AND"
                    array(
                        'key'     => 'assigned_to',
                        'value'   => $assigned_to,
                        'compare' => '='
                    ),
                    array(
                        'key'     => 'overall_status',
                        'value'   => 'Accepted',
                        'compare' => '!='
                    )
                )
            );
            $requires_update = new WP_Query( $args );
            ?>
            <?php if ( $requires_update->have_posts() ) : while ( $requires_update->have_posts() ) : $requires_update->the_post(); ?>

                <form method="post" action="">
                    <div class="callout alert" >
                        <i class="fi-plus"> New </i>
                        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        <span class="float-right">
                            <input type="hidden" name="post_id" value="<?php echo get_the_ID(); ?>" />
                            <button type="submit" name="response" value="accept" class="button small ">Accept</button>
                            <button type="submit" name="response" value="decline" class="button small ">Decline</button>
                        </span>
                    </div>
                </form>

            <?php endwhile; endif; ?>
            <!-- End Assigned Contacts -->

            <!-- Begin Updates Required Section -->
            <?php if ( ! empty($_POST['comment_content'] )) { dt_update_required_update($_POST); } ?>
            <?php
            /* Loop for the requires update contacts */
            $assigned_to = 'user-' . get_current_user_id();
            $args = array(
                'post_type' => 'contacts',
                'nopaging' => true,
                'meta_query' =>  array(
                    'relation' => 'AND', // Optional, defaults to "AND"
                    array(
                        'key'     => 'assigned_to',
                        'value'   => $assigned_to,
                        'compare' => '='
                    ),
                    array(
                        'key'     => 'requires_update',
                        'value'   => 'Yes',
                        'compare' => '='
                    )
                )
            );
            $requires_update = new WP_Query( $args );
            ?>
            <?php if ( $requires_update->have_posts() ) : while ( $requires_update->have_posts() ) : $requires_update->the_post(); ?>

                <form action="" method="post">
                    <div class="callout warning" >

                        <i class="fi-alert"> Update Needed </i>

                        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>

                        <span class="float-right">
                            <button type="button" class="button small update-<?php echo get_the_ID(); ?>" onclick="jQuery('.update-<?php echo get_the_ID(); ?>').toggle();">Update</button>
                        </span>

                        <p style="display:none;" class="update-<?php echo get_the_ID(); ?>" >

                            <input type="hidden" name="post_ID" value="<?php echo get_the_ID(); ?>" />
                            <input type="text" name="comment_content"  />

                        </p>

                    </div>
                </form>

            <?php endwhile; endif; ?>
            <!-- End Updates Required Section -->

            <!-- Begin Contacts Tabs Section -->
            <div class="row column padding-bottom">

                <?php include ('parts/content-contacts-tabs.php') ?>

            </div>
            <!-- End Contacts Tabs Section -->


        </main> <!-- end #main -->

        <aside class="large-4 medium-4 columns ">

            <section class="block">

                <!-- Project Stats -->
                <h4>Quick Update</h4>

                <form id="post-comment-form">
                    <div>
                        <select name="post-comment-id" id="post-comment-id" required aria-required="true">

                            <option disabled><?php _e( 'Select Contact', 'disciple_tools' ); ?></option>

                            <?php if ( $query1->have_posts() ) : while ( $query1->have_posts() ) : $query1->the_post(); ?>

                                <option value="<?php the_ID(); ?>"><?php the_title(); ?></option>

                            <?php endwhile; ?>

                            <?php else : ?>

                                <option disabled>No Contacts</option>

                            <?php endif; ?>

                        </select>
                    </div>

                    <div>

                        <textarea rows="3" cols="20" name="post-comment-content" id="post-comment-content" placeholder="<?php _e( 'Leave an update', 'disciple_tools' ); ?>" required aria-required="true"></textarea>
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
