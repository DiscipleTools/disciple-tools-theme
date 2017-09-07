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

            <div class="row column padding-bottom">

                <div class="bordered-box">
                    <p><?php _e( "Welcome to Disciple.Tools!" ); ?></p>
                </div>

            </div>

        </main> <!-- end #main -->

    </div> <!-- end #inner-content -->


</div> <!-- end #content -->

<?php get_footer(); ?>
