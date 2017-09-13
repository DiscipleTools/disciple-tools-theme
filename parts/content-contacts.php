<?php
declare(strict_types=1);
?>
<div class="bordered-box">
    <h5><?php _e( "Contacts" ); ?></h5>

    <div class="js-sort-dropdown" hidden>
        <ul class="dropdown menu" data-dropdown-menu>
            <li>
                <a href="#"><?php _e( "Sort by" ); ?></a>
                <ul class="menu">
                    <li><a href="#" class="js-sort-by" data-column-index="7" data-order="desc">
                        <?php _e( "Most recent" ); ?>
                    </a></li>
                    <li><a href="#" class="js-sort-by" data-column-index="7" data-order="asc">
                        <?php _e( "Least recent" ); ?>
                    </a></li>
                </ul>
            </li>
        </ul>
    </div>

    <table class="js-list">
        <thead><tr>
            <th></th>
            <th><?php _e( "Name" ); ?></th>
            <th><?php _e( "Status" ); ?></th>
            <th><?php _e( "Faith Milestones" ); ?></th>
            <th><?php _e( "Assigned to" ); ?></th>
            <th><?php _e( "Location" ); ?></th>
            <th><?php _e( "Group" ); ?></th>
            <!--<th><?php _e( "Last modified" ); ?></th>-->
        </tr></thead>
        <tbody>
            <tr class="js-list-contacts-loading"><td colspan=7><?php _e( "Loading..." ); ?></td></tr>
        </tbody>
    </table>

</div>
