<?php declare(strict_types=1); ?>
<?php get_header(); ?>

<?php dt_print_breadcrumbs( null, __( "Locations" ) ); ?>

<div id="content">

    <div id="inner-content" class="row">

        <main id="main" class="large-8 medium-8 columns" role="main">

            <?php get_template_part( 'parts/content', 'locations-tabs.php' ); ?>

        </main> <!-- end #main -->

        <aside class="large-4 medium-4 columns ">

            <section class="bordered-box">

                <p>Links</p>

            </section>

        </aside> <!-- end #aside -->

    </div> <!-- end #inner-content -->

</div> <!-- end #content -->

<?php get_footer(); ?>
