<article id="post-<?php the_ID(); ?>" <?php post_class( '' ); ?> role="article" itemscope itemtype="http://schema.org/BlogPosting">

    <header class="article-header">

    </header> <!-- end article header -->

    <section class="entry-content" itemprop="articleBody">
        <form method="post" action="<?php echo get_permalink() ?>" id="contact-edit">


            <?php dt_get_group_edit_form();  ?>

            <input type="submit" name="Update" value="Update" class="button center" />

        </form>

    </section> <!-- end article section -->

    <footer class="article-footer">
    </footer> <!-- end article footer -->


</article> <!-- end article -->