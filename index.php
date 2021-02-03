<?php

dt_please_log_in();

if ( is_single() ){
    locate_template( 'single-template.php', true );
} else {
    dt_route_front_page();
}

