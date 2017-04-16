<form role="search" method="get" class="search-form" action="<?php echo home_url( '/' ); ?>">
    <input type="search" class="small" placeholder="<?php echo esc_attr_x( 'Search...', 'disciple_tools' ) ?>" value="<?php echo get_search_query() ?>" name="s" title="<?php echo esc_attr_x( 'Search for:', 'disciple_tools' ) ?>" />
    <input type="hidden" class=" button small" value="<?php echo esc_attr_x( 'Search', 'disciple_tools' ) ?>" />
</form>