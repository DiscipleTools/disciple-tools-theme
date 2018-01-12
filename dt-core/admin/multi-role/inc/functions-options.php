<?php
/**
 * Functions for handling plugin options.
 *
 * @package    Members
 * @subpackage Includes
 * @author     Justin Tadlock <justin@justintadlock.com>
 * @copyright  Copyright (c) 2009 - 2016, Justin Tadlock
 * @link       http://themehybrid.com/plugins/members
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */


/**
 * Conditional check to see if denied capabilities should overrule granted capabilities when
 * a user has multiple roles with conflicting cap definitions.
 *
 * @since  0.1.0
 * @access public
 * @return bool
 */
function dt_multi_role_explicitly_deny_caps() {
    return apply_filters( 'dt_multi_role_explicitly_deny_caps', false );
}

/**
 * Conditional check to see if the role manager is enabled.
 *
 * @since  0.1.0
 * @access public
 * @return bool
 */
function dt_multi_role_multiple_user_roles_enabled() {
    return apply_filters( 'dt_multi_role_multiple_roles_enabled', true );
}


