<?php

add_action('wp_enqueue_scripts', 'dt_chart_enqueue');

function dt_chart_enqueue () {
    wp_register_script( 'dt_google_chart', 'https://www.gstatic.com/charts/loader.js' );
    wp_enqueue_script( 'dt_google_chart', 'https://www.gstatic.com/charts/loader.js' );
}

?>

<?php get_header(); ?>

    <div id="content">

        <div id="inner-content" class="row">

            <main id="main" class="large-12 medium-12 columns" role="main">

                <?php if (have_posts()) : while (have_posts()) : the_post(); ?>

                    <?php dt_chart_wordtree (); ?>
                    <?php dt_chart_bargraph (); ?>

                <?php endwhile; endif; ?>

            </main> <!-- end #main -->

        </div> <!-- end #inner-content -->

    </div> <!-- end #content -->

<?php get_footer(); ?>