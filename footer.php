                <footer class="footer" role="contentinfo">
                    <div id="inner-footer" class="row">
                        <div class="grid-x grid-padding-x">
                            <div class="large-12 medium-12 cell grid-x grid-padding-x">
                                <nav role="navigation">
                                    <?php disciple_tools_footer_links(); ?>
                                </nav>
                            </div>
                            <div class="large-12 medium-12 cell grid-x grid-padding-x">

                            </div>
                        </div>
                    </div> <!-- end #inner-footer -->
                </footer> <!-- end .footer -->
            </div>  <!-- end .main-content -->
        </div> <!-- end .off-canvas-wrapper -->
        <?php get_template_part( 'dt-assets/parts/modals/modal', 'help' ); ?>
        <?php get_template_part( 'dt-assets/parts/modals/modal', 'support' ); ?>
        <?php wp_footer(); ?>
    </body>
</html> <!-- end page -->
