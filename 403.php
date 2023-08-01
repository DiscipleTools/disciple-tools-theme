<?php
dt_please_log_in();

$dt_post_type = get_post_type();
if ( empty( $dt_post_type ) ) {
    $dt_post_type = 'contacts';
}
if ( ! current_user_can( 'access_' . $dt_post_type ) ) {
    wp_safe_redirect( apply_filters( 'dt_403_redirect', home_url( '/registered' ) ) );
    exit();
}

get_header();
?>

    <div id="content" class="template-error">

        <div id="inner-content" class="grid-x grid-padding-x">

            <div class="cell bordered-box">
                <header class="article-header">
                    <h1><?php esc_html_e( 'Permission denied', 'disciple_tools' ); ?></h1>

                </header> <!-- end article header -->

                <section class="entry-content">
                    <p>
                        <?php
                        if ( isset( $args ) && is_wp_error( $args ) ){
                            echo esc_html( $args->get_error_message() );
                        } else {
                            $post_settings = DT_Posts::get_post_settings( get_post_type() ?: 'contacts' );
                            $dt_post_type = $post_settings['label_singular'] ?: 'item';
                            $dt_id = GET_THE_ID();
                            echo sprintf( esc_html__( 'Sorry, you don\'t have permission to view the %1$s with id %2$s.', 'disciple_tools' ), esc_html( $dt_post_type ), esc_html( $dt_id ) ) . ' ';
                            echo esc_html__( 'Request permission from your administrator.', 'disciple_tools' );
                            echo '<br><br><button id="request-record-access-button" class="button loader open-request-record-access-button-modal">' . esc_html__( 'Request Record Access Permission', 'disciple_tools' ) . '</button>';
                        }
                        echo '<br><br><a href="javascript:history.back(1);">' . esc_html__( 'Back', 'disciple_tools' ) . '</a>';
                        echo '<div id="request-record-access-error" class="error">';
                        ?>
                    </p>
                </section> <!-- end article section -->
                <div class="reveal" id="request-record-access-modal" data-reveal xmlns="http://www.w3.org/1999/html">

                    <h3><?php echo esc_html__( 'Request Record Access', 'disciple_tools' ) ?></h3>

                    <form class="request-record-access-form">
                        <p><?php echo esc_html__( 'Record owner to be notified of access request. Are you happy to proceed?', 'disciple_tools' ); ?></p>

                        <button class="button loader" type="submit" id="request-record-access-modal-button">
                            <?php echo esc_html__( 'Submit Request', 'disciple_tools' ); ?>
                        </button>
                        <button class="button loader" data-close aria-label="Close reveal" type="button">
                            <?php echo esc_html__( 'Cancel', 'disciple_tools' ) ?>
                        </button>
                    </form>

                    <button class="close-button" data-close aria-label="<?php esc_html_e( 'Close', 'disciple_tools' ); ?>" type="button">
                        <span aria-hidden="true">&times;</span>
                    </button>

                </div>
            </div>

        </div> <!-- end #inner-content -->

    </div> <!-- end #content -->

<?php get_footer(); ?>
