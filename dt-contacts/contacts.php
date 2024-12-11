<?php

add_filter( 'dt_post_type_modules', function( $modules ){
    $modules['contacts_base'] = [
        'name' => 'Contacts',
        'enabled' => true,
        'locked' => true,
        'post_type' => 'contacts',
        'description' => 'Default contact functionality'
    ];
    $modules['dmm_module'] = [
        'name' => 'DMM Module',
        'enabled' => true,
        'prerequisites' => [ 'contacts_base' ],
        'post_type' => 'contacts',
        'description' => 'Field and workflows for Disciple Making Movements'
    ];
    $modules['access_module'] = [
        'name' => 'Access Module',
        'enabled' => true,
        'prerequisites' => [ 'dmm_module', 'contacts_base' ],
        'post_type' => 'contacts',
        'description' => 'Field and workflows for follow-up ministries'
    ];
    return $modules;
}, 10, 1 );


require_once 'base-setup.php';
DT_Contacts_Base::instance();

require_once 'duplicates-merging.php';
new DT_Duplicate_Checker_And_Merging();

require_once 'user-module.php';
DT_Contacts_User::instance();

require_once 'module-faith.php';
require_once 'module-followup.php';
require_once 'module-coaching.php';
require_once 'module-baptisms.php';

require_once 'access-module.php';
DT_Contacts_Access::instance();

require_once 'contacts-utils.php';
