# DT-Notifications
The Notifications module contains the utilities necessary for web notifications, email notifications, and alerts between workers.

## Files

1. `/hooks/`   
   _The Hooks folder contains the hook factory files that catch the appropriate activities and logs them to the notifications table._
1. `notifications.php`  
   _description_
1. `notifications-email-api.php`  
   _description_
1. `notifications-endpoints.php`   
   _description_
1. `notifications-hooks.php`  
   _description_
1. `notifications-template.php`  
   _description_

## DB Table Configuration

The primary notifications table is `$wpdb->dt_notifications`, i.e. `wp_dt_notifications` on default installations. The 
columns and their purposes are listed below.

### @mentions

| Column Name           | Description                                                               |
| ------------          |------------                                                               |
| `id`                  | Auto increment id field                                                   |
| `user_id`             | User id to be notified                                                    |
| `post_id`             | Contact, Group, Location, etc that the comment was logged against         |
| `secondary_item_id`   | Comment id where the @mention was discovered                              |
| `notification_name`   | `mention`                                                                 |
| `notification_action` | `mentioned`, `updated`, `untrashed`                                       |
| `notification_note`   | Description for the displayed notification                                |
| `date_notified`       | Date and time that this event occurred                                    |
| `is_new`              | Boolean status of whether the user has viewed the notfication or not      |


### Assigned To, Update Needed, Baptism Added

| Column Name           | Description                                                               |
| ------------          |------------                                                               |
| `id`                  | Auto increment id field                                                   |
| `user_id`             | User id to be notified                                                    |
| `post_id`             | Contact, Group, Location, etc that the comment was logged against         |
| `secondary_item_id`   | The `meta_id` of the record                                               |
| `notification_name`   | `assigned_to`, `requires_update`, `share`, `contact_info_update`          |
| `notification_action` | `alert`, `new_event`                                                      |
| `notification_note`   | Description for the displayed notification                                |
| `date_notified`       | Date and time that this event occurred                                    |
| `is_new`              | Boolean status of whether the user has viewed the notfication or not      |


### Recommended Email Plugins
The email 
