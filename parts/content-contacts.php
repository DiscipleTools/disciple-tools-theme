<?php
declare(strict_types=1);
?>
<div class="bordered-box">
    <h3><?php _e( "Contacts" ); ?></h3>

    <table class="js-list-contacts">
        <thead><tr>
            <th></th>
            <th><?php _e( "Name" ); ?></th>
            <th><?php _e( "Status" ); ?></th>
            <th><?php _e( "Faith Milestones" ); ?></th>
            <th><?php _e( "Assigned to" ); ?></th>
            <th><?php _e( "Location" ); ?></th>
            <th><?php _e( "Group" ); ?></th>
        </tr></thead>
        <tbody>
            <tr class="js-list-contacts-loading"><td colspan=7><?php _e( "Loading..." ); ?></td></tr>
        </tbody>
    </table>

</div>
