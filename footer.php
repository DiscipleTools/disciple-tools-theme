				<footer class="footer" role="contentinfo">
					<div id="inner-footer" class="row">
                        <div class="grid-x grid-padding-x">
                            <div class="large-12 medium-12 cell grid-x grid-padding-x">
                                <nav role="navigation">

                                    <?php disciple_tools_footer_links(); ?>
                                </nav>
                            </div>
                            <div class="large-12 medium-12 cell grid-x grid-padding-x">
                                <p class="source-org copyright">
                                    &copy; <?php echo esc_html( date( 'Y' ) ); ?>
                                    <?php bloginfo( 'name' ); ?>.
                                    <?php if( user_can( get_current_user_id(), 'read' )) : ?>
                                        <a href="<?php echo esc_attr( home_url().'/wp-admin' ); ?>">Admin Panel</a>
                                    <?php endif; ?>
                                    | <a href="<?php echo esc_attr( home_url( '/' ) ); ?>about">About Us</a>
                                    | <a href="<?php echo esc_attr( wp_logout_url() ); ?>">Logout</a></p>
                            </div>
                        </div>
                    </div> <!-- end #inner-footer -->
                </footer> <!-- end .footer -->
            </div>  <!-- end .main-content -->
        </div> <!-- end .off-canvas-wrapper -->
        <?php wp_footer(); ?>
    </body>
</html> <!-- end page -->
