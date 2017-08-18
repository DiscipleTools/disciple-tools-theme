<?php get_header(); ?>

<div id="content">

    <div id="inner-content" class="row">

        <main id="main" class="large-8 medium-8 columns " role="main">

            <div class="show-for-small-only">
                <section class="bordered-box">
                    <?php include( 'searchform.php' ); ?>
                </section>
            </div>

            <?php get_template_part( 'parts/content', 'assigned-to' ); ?>

            <?php get_template_part( 'parts/content', 'required-updates' ); ?>

            <!-- Begin Contacts Tabs Section -->
            <div class="row column padding-bottom">

                <?php get_template_part( 'parts/content', 'contacts' ); ?>

            </div>
            <!-- End Contacts Tabs Section -->

        </main> <!-- end #main -->

    </div> <!-- end #inner-content -->


</div> <!-- end #content -->

<?php get_footer(); ?>
