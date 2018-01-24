<?php get_header(); ?>

    <div id="content">

        <div id="inner-content" class="row">

            <main id="main" class="large-8 medium-8 columns" role="main">

                <article id="content-not-found">

                    <header class="article-header">
                        <h1><?php esc_html_e( 'Epic 403 - Permission denied', 'disciple_tools' ); ?></h1>

                    </header> <!-- end article header -->

                    <section class="entry-content">
                        <p>
                        <?php
                        $type = "item";
                        if ( is_singular( "contacts" )){
                            $type = __( "contact", "disciple_tools" );
                        } elseif ( is_singular( "groups" )){
                            $type = __( "group", "disciple_tools" );
                        }
                        $id = GET_THE_ID();
                        echo sprintf( esc_html__( 'You don\'t have permission to view the %1$s with id %2$s.', 'disciple_tools' ), esc_html( $type ), esc_html( $id ) ); ?>
                        </p>
                    </section> <!-- end article section -->


                </article> <!-- end article -->

            </main> <!-- end #main -->

        </div> <!-- end #inner-content -->

    </div> <!-- end #content -->

<?php get_footer(); ?>
