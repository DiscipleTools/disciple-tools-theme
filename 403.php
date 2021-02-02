<?php
dt_please_log_in();
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
                            $post_settings = DT_Posts::get_post_settings( get_post_type() ?: "contacts" );
                            $dt_post_type = $post_settings["label_singular"] ?: "item";
                            $dt_id = GET_THE_ID();
                            echo sprintf( esc_html__( 'Sorry, you don\'t have permission to view the %1$s with id %2$s.', 'disciple_tools' ), esc_html( $dt_post_type ), esc_html( $dt_id ) ) . ' ';
                            echo esc_html__( 'Request permission from your administrator.', 'disciple_tools' );
                            // @todo Add a request for permission form here, which could leave a comment on the record, and notify the owner of the record to share the record.
                        }
                        echo '<p><a href="javascript:history.back(1);">'. esc_html__( 'Back', 'disciple_tools' ) .'</a></p>';
                        ?>
                    </p>
                </section> <!-- end article section -->
            </div>

        </div> <!-- end #inner-content -->

    </div> <!-- end #content -->

<?php get_footer(); ?>
