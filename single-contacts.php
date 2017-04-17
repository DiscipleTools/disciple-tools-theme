<?php if ((isset($_POST['dt_contacts_noonce']) && wp_verify_nonce( $_POST['dt_contacts_noonce'], 'update_dt_contacts' ))) { dt_save_contact($_POST); } // Catch and save update info ?>

<?php get_header(); ?>

    <div id="content">

        <div id="inner-content" class="row">

            <!-- Breadcrumb Navigation-->
            <nav aria-label="You are here:" role="navigation">
                <ul class="breadcrumbs">
                    <li><a href="/">Dashboard</a></li>
                    <li><a href="/contacts/">Contacts</a></li>
                    <li>
                        <span class="show-for-sr">Current: </span> Name <!-- TODO: Query the contact name and replace this with the contact title.-->
                    </li>
                </ul>
            </nav>

            <main id="main" class="large-8 medium-8 columns" role="main">



                    <?php if (have_posts()) : while (have_posts()) : the_post(); ?>

                        <?php

                            if(isset($_GET['action']) && $_GET['action'] == 'edit') { // check if edit screen

                               get_template_part( 'parts/edit', 'contact' );

                            } else {

                                get_template_part( 'parts/loop', 'single-contact' );
                            }

                        ?>

                    <?php endwhile; else : ?>

                        <?php get_template_part( 'parts/content', 'missing' ); ?>

                    <?php endif; ?>

            </main> <!-- end #main -->

            <aside class="large-4 medium-4 columns ">

                <section class="block">

                    <p>Sidebar</p>

                </section>

            </aside> <!-- end #aside -->

        </div> <!-- end #inner-content -->

    </div> <!-- end #content -->

<?php get_footer(); ?>