<form role="search" method="get" class="search-form" action="<?php echo home_url( '/' ); ?>">
    <label>
        <span class="screen-reader-text"><?php echo _x( 'Search for:', 'label', 'disciple_tools' ) ?></span>
        <input type="search" class="search-field" placeholder="<?php echo esc_attr_x( 'Search...', 'disciple_tools' ) ?>" value="<?php echo get_search_query() ?>" name="s" title="<?php echo esc_attr_x( 'Search for:', 'disciple_tools' ) ?>" />
    </label>
    <input type="submit" class="search-submit button" value="<?php echo esc_attr_x( 'Search', 'disciple_tools' ) ?>" />
</form>