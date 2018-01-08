<script>
    jQuery(document).ready(function () {
		jQuery.post('<?php echo esc_attr( $ajaxurl ); ?>', {action: 'googleanalytics_get_script'}, function(response) {
			var F = new Function ( response );
			return( F() );
		});
    });
</script>
