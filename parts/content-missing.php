<div id="post-not-found" class="hentry">

	<?php if ( is_search() ) : ?>

        <header class="article-header">
        </header>

        <section class="entry-content">
            <p><?php esc_html_e( 'No Matches.', 'disciple_tools' );?></p>
        </section>

        <section class="search">
        </section> <!-- end search section -->

        <footer class="article-footer">
        </footer>

    <?php else: ?>

        <header class="article-header">
            <h1><?php esc_html_e( 'Oops, Post Not Found!', 'disciple_tools' ); ?></h1>
        </header>

        <section class="entry-content">
            <p><?php esc_html_e( 'Uh Oh. Something is missing. Try double checking things.', 'disciple_tools' ); ?></p>
        </section>

        <section class="search">
            <p><?php get_search_form(); ?></p>
        </section> <!-- end search section -->

        <footer class="article-footer">
          <p><?php esc_html_e( 'This is the error message in the parts/content-missing.php template.', 'disciple_tools' ); ?></p>
        </footer>

    <?php endif; ?>

</div>
