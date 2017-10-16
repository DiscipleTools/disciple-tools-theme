<?php
/*
Thanks to the awesome work by JointsWP users, there
are many languages you can use to translate your theme.
*/

// Adding Translation Option
add_action( 'after_setup_theme', 'dt_load_translations' );
function dt_load_translations(){
    load_theme_textdomain( 'disciple_tools', get_template_directory() .'/assets/translation' );
}
?>
