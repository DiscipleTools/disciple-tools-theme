<section class="block">

    <article id="post-<?php the_ID(); ?>" <?php post_class(''); ?> role="article" >

        <header class="article-header">

            <h3><?php the_time('F j, Y') ?></h3>

        </header> <!-- end article header -->

        <section class="entry-content" itemprop="articleBody">

            <p><?php the_post_thumbnail('full'); ?></p>

            <?php the_content('<button class="tiny">' . __( 'Read more...', 'disciple_tools' ) . '</button>'); ?>

        </section> <!-- end article section -->

        <footer class="article-footer">

            <div class="row">

                <div class="columns">

                    <div class="expanded button-group">

                        <a href="#" class="button">Pray</a>
                        <a href="#" class="button">Comment</a>

                    </div>

                </div>

            </div>

        </footer> <!-- end article footer -->

    </article> <!-- end article -->

</section>