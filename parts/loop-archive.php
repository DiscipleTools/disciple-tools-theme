<article id="post-<?php the_ID(); ?>" <?php post_class(''); ?> role="article">					
	<header class="article-header">
		<?php get_template_part( 'parts/content', 'header' ); ?>
	</header> <!-- end article header -->
					
	<section class="entry-content" itemprop="articleBody">
		<?php the_post_thumbnail('full'); ?>
		<?php the_content('<button class="tiny">' . __( 'Read more...', 'disciple_tools' ) . '</button>'); ?>
	</section> <!-- end article section -->
						
	<footer class="article-footer">
        <?php get_template_part( 'parts/content', 'pray' ); ?>
	</footer> <!-- end article footer -->
</article> <!-- end article -->