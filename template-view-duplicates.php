<?php
/*
Template Name: View Duplicates
*/
dt_please_log_in();

if ( ! current_user_can( 'dt_all_access_contacts' ) ) {
    wp_safe_redirect( '/settings' );
    exit();
}

get_header();

?>

    <div id="content" class="template-view-duplicates duplicates-page">

        <div id="inner-content" class="grid-x grid-margin-x">

            <main id="main" class="large-12 medium-12 cell" role="main">
                <div class="bordered-box">
                    This page is coming soon.
                </div>
            </main> <!-- end #main -->
        </div> <!-- end #inner-content -->

    </div> <!-- end #content -->

<?php get_footer(); ?>
