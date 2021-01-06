<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * Class Disciple_Tools_Migration_0035
 * Add indexed to the postmeta table to customize for D.T list queries
 */
class Disciple_Tools_Migration_0036 extends Disciple_Tools_Migration {
    public function up(){

        $admin_role = get_role( 'administrator' );
        $admin_role->remove_cap( 'view_project_metrics' );

//        $admin_role->remove_cap( 'promote_users' );
//        $admin_role->remove_cap( 'edit_users' );
//        $admin_role->remove_cap( 'create_users' );
//        $admin_role->remove_cap( 'delete_users' );
//        $admin_role->remove_cap( 'list_users' );

        $admin_role->remove_cap( 'dt_list_users' );
        $admin_role->remove_cap( 'access_contacts' );
        $admin_role->remove_cap( 'create_contacts' );
        $admin_role->remove_cap( 'update_shared_contacts' );
        $admin_role->remove_cap( 'view_any_contacts' );
        $admin_role->remove_cap( 'assign_any_contacts' );
        $admin_role->remove_cap( 'update_any_contacts' );
        $admin_role->remove_cap( 'delete_any_contacts' );
        $admin_role->remove_cap( 'edit_contact' );
        $admin_role->remove_cap( 'read_contact' );
        $admin_role->remove_cap( 'delete_contact' );
        $admin_role->remove_cap( 'delete_others_contacts' );
        $admin_role->remove_cap( 'delete_contacts' );
        $admin_role->remove_cap( 'edit_contacts' );
        $admin_role->remove_cap( 'edit_team_contacts' );
        $admin_role->remove_cap( 'edit_others_contacts' );
        $admin_role->remove_cap( 'publish_contacts' );
        $admin_role->remove_cap( 'read_private_contacts' );
        $admin_role->remove_cap( 'access_groups' );
        $admin_role->remove_cap( 'create_groups' );
        $admin_role->remove_cap( 'view_any_groups' );
        $admin_role->remove_cap( 'assign_any_groups' );
        $admin_role->remove_cap( 'update_any_groups' );
        $admin_role->remove_cap( 'delete_any_groups' );
        $admin_role->remove_cap( 'edit_group' );
        $admin_role->remove_cap( 'read_group' );
        $admin_role->remove_cap( 'delete_group' );
        $admin_role->remove_cap( 'delete_others_groups' );
        $admin_role->remove_cap( 'delete_groups' );
        $admin_role->remove_cap( 'edit_groups' );
        $admin_role->remove_cap( 'edit_others_groups' );
        $admin_role->remove_cap( 'publish_groups' );
        $admin_role->remove_cap( 'read_private_groups' );
        $admin_role->remove_cap( 'read_location' );
        $admin_role->remove_cap( 'edit_location' );
        $admin_role->remove_cap( 'delete_location' );
        $admin_role->remove_cap( 'delete_others_locations' );
        $admin_role->remove_cap( 'delete_locations' );
        $admin_role->remove_cap( 'edit_locations' );
        $admin_role->remove_cap( 'edit_others_locations' );
        $admin_role->remove_cap( 'publish_locations' );
        $admin_role->remove_cap( 'read_private_locations' );
        $admin_role->remove_cap( 'delete_any_locations' );
        $admin_role->remove_cap( 'read_peoplegroup' );
        $admin_role->remove_cap( 'edit_peoplegroup' );
        $admin_role->remove_cap( 'delete_peoplegroup' );
        $admin_role->remove_cap( 'delete_others_peoplegroups' );
        $admin_role->remove_cap( 'delete_peoplegroups' );
        $admin_role->remove_cap( 'edit_peoplegroups' );
        $admin_role->remove_cap( 'edit_others_peoplegroups' );
        $admin_role->remove_cap( 'publish_peoplegroups' );
        $admin_role->remove_cap( 'read_private_peoplegroups' );
        $admin_role->remove_cap( 'delete_any_peoplegroup' );
        $admin_role->remove_cap( 'access_peoplegroups' );
        $admin_role->remove_cap( 'list_peoplegroups' );
        require_once( get_template_directory() . '/dt-core/setup-functions.php' );
        dt_setup_roles_and_permissions();
    }

    public function down() {
        global $wpdb;

    }

    public function test() {
//        $this->test_expected_tables();
    }


    public function get_expected_tables(): array {
        return [];
    }
}
