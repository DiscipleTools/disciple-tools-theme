<article id="post-<?php the_ID(); ?>" role="article" >

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



</article> <!-- end article -->

<ul class="tabs" data-tabs id="my-activity-tabs" data-options="deep_linking:true" data-options="scroll_to_content: false">

    <li class="tabs-title is-active"><a href="#panel1" aria-selected="true">Activity</a></li>
    <li class="tabs-title"><a href="#panel2">Comments</a></li>

</ul>

<div class="tabs-content" data-tabs-content="my-activity-tabs">

    <div class="tabs-panel is-active" id="panel1">

        <?php dt_activity_meta_box (get_the_ID()); ?>

    </div>
    <div class="tabs-panel" id="panel2">

        <?php comments_template(); ?>

    </div>

</div>