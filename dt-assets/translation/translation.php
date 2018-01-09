<?php

// Adding Translation Option
add_action( 'after_setup_theme', 'dt_load_translations' );
function dt_load_translations(){
    load_theme_textdomain( 'disciple_tools', get_template_directory() .'/dt-assets/translation' );
}

