                <footer class="footer" role="contentinfo">
                    <div id="inner-footer" class="row">

                    </div> <!-- end #inner-footer -->
                </footer> <!-- end .footer -->
            </div>  <!-- end .main-content -->
        </div> <!-- end .off-canvas-wrapper -->
        <?php get_template_part( 'dt-assets/parts/modals/modal', 'help' ); ?>
        <?php get_template_part( 'dt-assets/parts/modals/modal', 'support' ); ?>
        
        <!-- Mobile Footer Toolbar -->
        <?php if ( dt_is_mobile_request() ) : ?>
            <?php get_template_part( 'dt-assets/parts/nav-mobile-footer' ); ?>
        <?php endif; ?>
        
        <?php wp_footer(); ?>
    </body>
</html> <!-- end page -->
