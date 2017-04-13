<div id="sidebar-groups" class="sidebar large-4 medium-4 columns" role="complementary">

    <?php if ( is_active_sidebar( 'groups' ) ) : ?>

        <?php dynamic_sidebar( 'groups' ); ?>

    <?php else : ?>

        <!-- This content shows up if there are no widgets defined in the backend. -->

        <div class="alert help">
            <p><?php _e( 'Please activate some Widgets.', 'disciple_tools' );  ?></p>
        </div>

    <?php endif; ?>

</div>