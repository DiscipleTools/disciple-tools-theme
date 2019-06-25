<?php
/*
Template Name: Search
*/

if ( ! current_user_can( 'access_contacts' ) ) {
    wp_safe_redirect( '/settings' );
}

get_header(); ?>

<div id="content">

    <div id="inner-content" class="grid-x grid-margin-x">

        <div class="large-3 medium-4 small-12 cell">
            <div class="bordered-box">
                <div class="grid-x">
                    <?php get_search_form(); ?>
                </div>
            </div>
        </div>

        <div class="large-9 medium-8 small-12 cell">
            <div class="bordered-box">
                Results <?php echo get_query_var('s', 'something else'); ?>
            </div>
        </div>

    </div> <!-- end #inner-content -->

</div> <!-- end #content -->

<?php get_footer(); ?>
