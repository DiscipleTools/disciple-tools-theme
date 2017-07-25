<?php get_header(); ?>

<div id="content">

    <div id="inner-content" class="row">

        <main id="main" class="large-8 medium-8 columns " role="main">

            <div class="show-for-small-only">
                <section class="bordered-box">
                    <?php include ('searchform.php'); ?>
                </section>
            </div>

            <?php include ('parts/content-assigned-to.php'); ?>

            <?php include ('parts/content-required-updates.php'); ?>

            <!-- Begin Contacts Tabs Section -->
            <div class="row column padding-bottom">

                <?php include('parts/content-contacts.php'); ?>

            </div>
            <!-- End Contacts Tabs Section -->


        </main> <!-- end #main -->

        <aside class="large-4 medium-4 columns ">

            <section class="bordered-box">

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



        </aside> <!-- end #aside -->

    </div> <!-- end #inner-content -->



</div> <!-- end #content -->

<?php get_footer(); ?>
