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


            <div class="callout warning" >
                <i class="fi-alert"> Update Needed </i>
                <a href="#">Mohammed Kali</a>
                <span class="float-right">
                    <button type="submit" name="Update" value="Update" class="button small ">Update</button>
                </span>
            </div>


            <div class="row column padding-bottom">

                <?php include ('parts/content-contacts-tabs.php') ?>

            </div>



        </main> <!-- end #main -->

        <aside class="large-4 medium-4 columns ">



            <section class="block">
                <!-- Project Stats -->
                <h4>Quick Update</h4>
                <form id="post-comment-form">
                    <div>
                        <input type="hidden" value="<?php ?>" name="comment-" />
                        <input type="hidden" value="<?php ?>" name="<?php ?>" />
                        <input type="hidden" value="<?php ?>" name="<?php ?>" />
                        <input type="hidden" value="<?php ?>" name="<?php ?>" />

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
