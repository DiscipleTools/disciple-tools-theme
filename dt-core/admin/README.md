# DT-Core/Admin
The core/admin folder contains much of the files to support the wp-admin interface, activation elements, 
configuration of the system, security configurations, privacy files, roles, etc. These are all the basic 
configureation and setup system files.

## Core Folders and Files
Folders
1. /css/    _(This folder holds the admin panel css files.)_
1. /img/    _(This folder holds the admin panel image files.)_
1. /js/     _(This folder holds the admin panel javascript files.)_
1. /menu/   _()_
1. /metaboxes/  _(This folder holds a number of shared metaboxes that are used in the admin panels on the post type pages.)_
1. /multi-role/     _()_
1. /tables/     _()_
1. /user-groups/    _()_

Files
1. admin-theme-design.php _()_
1. class-activator.php _()_
1. class-deactivator.php _()_
1. class-better-author-metabox.php _()_
1. class-roles.php _()_
1. class-runonce.php _()_
1. config-dashboard.php _()_
1. config-options-settings.php _()_
1. config-profile.php _()_
1. config-site-defaults.php _()_
1. enqueue-scripts.php _()_
1. private-site.php _()_
1. restrict-record-access-in-admin.php _()_
1. restrict-rest-api.php _()_
1. restrict-xml-rpc-pingback.php _()_
1. three-column-screen-layout.php _()_


## Roles and Capabilities

Roles contain capabilities. The Disciple Tools System uses both the default Wordpress pattern of capabilities, and a custom 
pattern of capabilities to manage types of people in the Followup System.

```
'list_users'                 => true,
'delete_others_posts'        => true,
'delete_pages'               => true,
'delete_posts'               => true,
'delete_private_pages'       => true,
'delete_private_posts'       => true,
'delete_published_pages'     => true,
'delete_published_posts'     => true,
'edit_others_pages'          => true,
'edit_others_posts'          => true,
'edit_pages'                 => true,
'edit_posts'                 => true,
'edit_private_pages'         => true,
'edit_private_posts'         => true,
'edit_published_pages'       => true,
'edit_published_posts'       => true,
'manage_categories'          => true,
'manage_links'               => true,
'moderate_comments'          => true,
'publish_pages'              => true,
'publish_posts'              => true,
'read'                       => true,
'read_private_pages'         => true,
'read_private_posts'         => true,
'upload_files'               => true,
'level_0'                    => true,

/* See all contacts */
'manage_contacts'            => true,

/* Add custom caps for contacts */
'create_contacts'            => true,  //create a new contact
'update_shared_contacts'     => true,
'view_any_contacts'          => true,    //view any contacts
'assign_any_contacts'        => true,  //assign contacts to others
'update_any_contacts'        => true,  //update any contacts
'delete_any_contacts'        => true,  //delete any contacts

/* Add custom caps for groups */
'access_groups'              => true,
'create_groups'              => true,
'view_any_groups'            => true,  //view any groups
'assign_any_groups'          => true,  //assign groups to others
'update_any_groups'          => true,  //update any groups
'delete_any_groups'          => true,  //delete any groups

/* Add custom caps for prayer updates */
'read_prayer'                => true,
'edit_prayer'                => true,
'delete_prayer'              => true,
'delete_others_prayers'      => true,
'delete_prayers'             => true,
'edit_prayers'               => true,
'edit_others_prayers'        => true,
'publish_prayers'            => true,
'read_private_prayers'       => true,

/* Add custom caps for locations */
'read_location'              => true,
'edit_location'              => true,
'delete_location'            => true,
'delete_others_locations'    => true,
'delete_locations'           => true,
'edit_locations'             => true,
'edit_others_locations'      => true,
'publish_locations'          => true,
'read_private_locations'     => true,

/* Add custom caps for progresss */
'read_progress'              => true,
'edit_progress'              => true,
'delete_progress'            => true,
'delete_others_progresss'    => true,
'delete_progresss'           => true,
'edit_progresss'             => true,
'edit_others_progresss'      => true,
'publish_progresss'          => true,
'read_private_progresss'     => true,

/* Add custom caps for assets */
'read_assetmapping'          => true,
'edit_assetmapping'          => true,
'delete_assetmapping'        => true,
'delete_others_assetmapping' => true,
'delete_assetmappings'       => true,
'edit_assetmappings'         => true,
'edit_others_assetmapping'   => true,
'publish_assetmapping'       => true,
'read_private_assetmappings' => true,

/* Add custom caps for resources */
'read_resource'              => true,
'edit_resource'              => true,
'delete_resource'            => true,
'delete_others_resource'     => true,
'delete_resources'           => true,
'edit_resources'             => true,
'edit_others_resource'       => true,
'publish_resource'           => true,
'read_private_resources'     => true,

/* Add custom caps for people groups */
'read_peoplegroup'           => true,
'edit_peoplegroup'           => true,
'delete_peoplegroup'         => true,
'delete_others_peoplegroup'  => true,
'delete_peoplegroups'        => true,
'edit_peoplegroups'          => true,
'edit_others_peoplegroup'    => true,
'publish_peoplegroup'        => true,
'read_private_peoplegroups'  => true,
```

