<?php
dt_please_log_in();

$dt_post_type = get_post_type();
if ( empty( $dt_post_type ) ) {
    $dt_post_type = 'contacts';
}
if ( ! current_user_can( 'access_' . $dt_post_type ) ) {
    wp_safe_redirect( apply_filters( 'dt_404_redirect', home_url( '/registered' ) ) );
    exit();
}

get_header();
?>

    <div id="content" class="template-error">

        <div id="inner-content" class="row">

            <main id="main" class="large-8 medium-8 columns" role="main">

                <article id="content-not-found">

                    <header class="article-header">
                        <h1><?php esc_html_e( 'Epic 404 - Not Found', 'disciple_tools' ); ?></h1>
                    </header> <!-- end article header -->

                    <section class="entry-content">
                        <p><?php esc_html_e( 'The page you were looking for was not found or was deleted', 'disciple_tools' ); ?></p>
                    </section> <!-- end article section -->

                </article> <!-- end article -->

            </main> <!-- end #main -->

        </div> <!-- end #inner-content -->

    </div> <!-- end #content -->

<?php get_footer(); ?>
