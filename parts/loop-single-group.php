<article id="post-<?php the_ID(); ?>" <?php post_class(''); ?> role="article" itemscope itemtype="http://schema.org/BlogPosting">

    <section class="block">

        <header class="article-header">
            <h1 class="entry-title single-title" itemprop="headline"><?php the_title(); ?></h1>
        </header> <!-- end article header -->

        <section class="entry-content" itemprop="articleBody">
            <?php the_meta(); ?>
        </section> <!-- end article section -->

        <footer class="article-footer">
            <form method="get" action="<?php echo get_permalink(); ?>"><input type="hidden" name="action" value="edit"/> <input type="submit" value="Edit" class="button" /> </form>
        </footer> <!-- end article footer -->

    </section>

    <section class="block">

        <?php comments_template(); ?>

    </section>

</article> <!-- end article -->