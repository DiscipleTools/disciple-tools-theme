<div class="notice notice-<?php echo $type; ?> <?php echo( ! empty( $is_dismissable ) ? 'is-dismissible' : '' ); ?>">
    <p><?php echo $msg; ?>
		<?php if ( ! empty( $action ) ): ?>
            &nbsp;
            <button onclick="window.location.href='<?php echo $action['url']; ?>'"
                    class="button button-primary"><?php echo $action['label']; ?></button>
		<?php endif; ?>
    </p>
</div>
