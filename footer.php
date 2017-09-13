				<footer class="footer" role="contentinfo">
					<div id="inner-footer" class="row">
                        <div class="grid-x grid-padding-x">
                            <div class="large-12 medium-12 cell grid-x grid-padding-x">
                                <nav role="navigation">
    
                                    <?php disciple_tools_footer_links(); ?>
                                </nav>
                            </div>
                            <div class="large-12 medium-12 cell grid-x grid-padding-x">
                                <p class="source-org copyright">&copy; <?php echo date( 'Y' ); ?> <?php bloginfo( 'name' ); ?>.  <?php if( user_can( get_current_user_id(), 'read' )) { echo '<a href="'.home_url().'/wp-admin">Admin Panel</a>';} ?> | <a href="<?php echo home_url( '/' ); ?>about">About Us</a> | <a href="<?php echo wp_logout_url(); ?>">Logout</a></p>
                            </div>
                        </div>
                    </div> <!-- end #inner-footer -->
                </footer> <!-- end .footer -->
            </div>  <!-- end .main-content -->
        </div> <!-- end .off-canvas-wrapper -->
        <?php wp_footer(); ?>
    </body>
</html> <!-- end page -->
