<article id="post-<?php the_ID(); ?>" role="article" >

    <section class="block">

        <header class="article-header">
            <h1 class="entry-title single-title" itemprop="headline"><?php the_title(); ?></h1>
        </header> <!-- end article header -->

        <section class="entry-content" itemprop="articleBody">
            <?php the_meta(); ?>
        </section> <!-- end article section -->

        <footer class="article-footer">

        </footer> <!-- end article footer -->

    </section>

    <section class="block">

        <?php comments_template(); ?>

    </section>

</article> <!-- end article -->