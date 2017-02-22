<?php
/**
 * Template part for displaying posts.
 *
 * @package SoSimple
 */

?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<header class="entry-header">
		<?php the_title( sprintf( '<h1 class="entry-title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h1>' ); ?>

		<?php if ( 'post' == get_post_type() ) : ?>
		<div class="entry-meta">
			<?php drm_posted_on(); ?>
		</div><!-- .entry-meta -->
		<?php endif; ?>
	</header><!-- .entry-header -->
	<?php if ( has_post_thumbnail() ) : ?>
		<div class="entry-thumbnail">
			<?php the_post_thumbnail( 'drm-featured' ); ?>
		</div>
	<?php endif; ?>
	<div class="entry-content">

	<?php 

	$ss_more_text = esc_attr(get_theme_mod( 'ss_more_text' ));
	if ( ! $ss_more_text ){
		$ss_more_text =  'Read More &raquo;';
	}

	$ss_more_position = esc_attr(get_theme_mod( 'ss_more_position' ));
	if ( ! $ss_more_position ){
		$ss_more_position =  'left';
	}

	if ( esc_attr(get_theme_mod( 'ss_excerpt_type' ) == 'option2') || esc_attr(get_theme_mod( 'ss_excerpt_type' ) == NULL) ) {
		the_excerpt();
	}
	else{
		
		if ( esc_attr(get_theme_mod( 'ss_more_type' ) == 'option1') || esc_attr(get_theme_mod( 'ss_more_type' ) == NULL) ) {
			the_content('',FALSE,'');
		}
		elseif( esc_attr(get_theme_mod( 'ss_more_type' ) == 'option2' )){
			the_content( $ss_more_text );
		}
		elseif( esc_attr(get_theme_mod( 'ss_more_type' ) == 'option3' )){
			
			if ( esc_attr(get_theme_mod( 'ss_more_button' ) == 'option1' )) {
				the_content("<span class='ss_button ss_fill ss_squared'"."style='margin-top: 30px; padding:4px 8px; background-color:".esc_attr(get_theme_mod( 'ss_button_bg' ))."; "."color:".esc_attr(get_theme_mod( 'ss_text_color' ))."; "."float:".$ss_more_position.";'>".$ss_more_text."</div>");
			}
			else{
				the_content("<span class='ss_button ss_fill ss_rounded'"."style='margin-top: 30px; padding:4px 8px; background-color:".esc_attr(get_theme_mod( 'ss_button_bg' ))."; "."color:".esc_attr(get_theme_mod( 'ss_text_color' ))."; "."float:".$ss_more_position.";'>".$ss_more_text."</div>");
			}
			
		}
	}
	?>
		<?php
			wp_link_pages( array(
				'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'drm' ),
				'after'  => '</div>',
			) );
		?>
	</div><!-- .entry-content -->

	<footer class="entry-footer">
<?php drm_entry_footer(); ?>
	</footer><!-- .entry-footer -->
</article><!-- #post-## -->
