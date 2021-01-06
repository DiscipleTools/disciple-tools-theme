<?php

add_filter( 'dt_post_type_modules', function( $modules ){
    $modules["groups_base"] = [
        "name" => "Groups",
        "enabled" => true,
        "locked" => true,
        "prerequisites" => [ "contacts_base" ],
        "post_type" => "groups",
        "description" => "Default group functionality"
    ];
    return $modules;
}, 20, 1 );

require_once 'base-setup.php';
DT_Groups_Base::instance();
