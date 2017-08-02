<div id="sidebar-general" class="sidebar large-4 medium-4 columns" role="complementary">

	<?php if ( is_active_sidebar( 'general' ) ) : ?>

        <?php dynamic_sidebar( 'general' ); ?>

    <?php else : ?>

    <!-- This content shows up if there are no widgets defined in the backend. -->
                        
    <div class="alert help">
        <p><?php _e( 'Please activate some Widgets.', 'disciple_tools' );  ?></p>
    </div>

    <?php endif; ?>

</div>