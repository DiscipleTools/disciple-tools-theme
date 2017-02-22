<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the #content div and all content after
 *
 * @package drm
 */
?>

</div><!-- #content -->
	<!-- Some more link css -->

	<footer id="colophon" class="site-footer" role="contentinfo">
		<div class="site-info">
			<a href="<?php echo esc_url( __( 'http://chasm.solutions/dmm-crm', 'drm' ) ); ?>"><?php printf( esc_html__( 'Proudly powered by %s', 'drm' ), 'WordPress & DMM-CRM team' ); ?></a>
			<span class="sep"> | </span>
			
		</div><!-- .site-info -->
	</footer><!-- #colophon -->
</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
