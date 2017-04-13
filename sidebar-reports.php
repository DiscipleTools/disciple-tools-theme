<div id="sidebar-reports" class="sidebar large-4 medium-4 columns" role="complementary">

    <?php if ( is_active_sidebar( 'reports' ) ) : ?>

        <?php dynamic_sidebar( 'reports' ); ?>

    <?php else : ?>

        <!-- This content shows up if there are no widgets defined in the backend. -->

        <div class="alert help">
            <p><?php _e( 'Please activate some Widgets.', 'disciple_tools' );  ?></p>
        </div>

    <?php endif; ?>

</div>