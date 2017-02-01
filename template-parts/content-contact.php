<?php
/**
 * Template part for displaying single posts.
 *
 * @package SoSimple
 */

?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<header class="entry-header">
        <span style="float:right;"><?php dmmcrm_entry_footer(); ?></span>
        <h1 class="entry-title">
		    <?php the_title( ); ?>


        </h1>

		<div class="entry-meta">
			<!-- TODO: Add generation number -->
		</div><!-- .entry-meta -->
	</header><!-- .entry-header -->
	<?php if ( has_post_thumbnail() ) : ?>
		<div class="entry-thumbnail">
			<?php the_post_thumbnail( 'dmmcrm-featured' ); ?>
		</div>
	<?php endif; ?>
	<div class="entry-content">
		<?php the_content(); ?>
		<?php
			wp_link_pages( array(
				'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'dmmcrm' ),
				'after'  => '</div>',
			) );
		?>
        <table>
            <tr><td>Phone</td><td><a href="tel:<?php echo get_post_meta( get_the_ID(), 'phone', true ) ; ?>"><?php echo get_post_meta( get_the_ID(), 'phone', true ) ; ?></a></td></tr>
            <tr><td>Overall Status</td><td><?php echo get_post_meta( get_the_ID(), 'overall_status', true ) ; ?></td></tr>
            <tr><td>Seeker Path</td><td><?php echo get_post_meta( get_the_ID(), 'seeker_path', true ) ; ?></td></tr>
            <tr><td>Seeker Milestones</td><td><?php echo get_post_meta( get_the_ID(), 'seeker_milestones', true ); ?></td></tr>
            <tr><td>Preferred Contact</td><td><?php echo get_post_meta( get_the_ID(), 'preferred_contact_method', true ) ; ?></td></tr>
            <tr><td>Email</td><td><a href="mail:<?php echo get_post_meta( get_the_ID(), 'email', true ) ; ?>"><?php echo get_post_meta( get_the_ID(), 'email', true ) ; ?></a></td></tr>
        </table>

    </div><!-- .entry-content -->

	<footer class="entry-footer">

	</footer><!-- .entry-footer -->
</article><!-- #post-## -->

