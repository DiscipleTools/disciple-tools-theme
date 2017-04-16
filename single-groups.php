<?php if ((isset($_POST['dt_groups_noonce']) && wp_verify_nonce( $_POST['dt_groups_noonce'], 'update_dt_groups' ))) { dt_save_group($_POST); } // Catch and save update info ?>

<?php get_header(); ?>

    <div id="content">

        <div id="inner-content" class="row">

            <main id="main" class="large-8 medium-8 columns" role="main">

                <?php if (have_posts()) : while (have_posts()) : the_post(); ?>

                    <?php
                        if(isset($_GET['action']) && $_GET['action'] == 'edit') { // check if edit screen

                            get_template_part( 'parts/edit', 'group' );

                        } else {

                            get_template_part( 'parts/loop', 'single-group' );
                        }
                    ?>

                <?php endwhile; else : ?>

                    <?php get_template_part( 'parts/content', 'missing' ); ?>

                <?php endif; ?>

            </main> <!-- end #main -->

            <?php get_sidebar('groups'); ?>

        </div> <!-- end #inner-content -->

    </div> <!-- end #content -->

<?php get_footer(); ?>