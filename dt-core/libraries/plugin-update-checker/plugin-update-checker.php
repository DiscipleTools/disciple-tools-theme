<?php
/**
 * Plugin Update Checker Library 5.1
 * http://w-shadow.com/
 *
 * Copyright 2022 Janis Elsts
 * Released under the MIT license. See license.txt for details.
 */

require dirname(__FILE__) . '/load-v5p1.php';


/**
 * A bunch of plugin Rely on Puc_v4. For our use it is the same as v5.
 */
if ( !class_exists('Puc_v4_Factory' ) ){
    class Puc_v4_Factory extends \YahnisElsts\PluginUpdateChecker\v5\PucFactory{

    }
}