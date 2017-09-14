<?php
declare(strict_types=1);
?>
<div class="bordered-box">
    <h5><?php _e( "Groups" ); ?></h5>

    <div class="js-sort-dropdown" hidden>
        <ul class="dropdown menu" data-dropdown-menu>
            <li>
                <a href="#"><?php _e( "Sort" ); ?></a>
                <ul class="menu">
                    <li><a href="#" class="js-sort-by" data-column-index="6" data-order="desc">
                        <?php _e( "Most recent" ); ?>
                    </a></li>
                    <li><a href="#" class="js-sort-by" data-column-index="6" data-order="asc">
                        <?php _e( "Least recent" ); ?>
                    </a></li>
                </ul>
            </li>
        </ul>
    </div>

    <table class="table-remove-top-border js-list">
        <thead><tr>
            <th data-priority="1"></th>
            <th data-priority="2"><?php _e( "Name" ); ?></th>
            <th><?php _e( "Status" ); ?></th>
            <th><?php _e( "Members" ); ?></th>
            <th><?php _e( "Leader" ); ?></th>
            <th><?php _e( "Location" ); ?></th>
            <!--<th><?php _e( "Last modified" ); ?></th>-->
        </tr></thead>
        <tbody>
            <tr class="js-list-loading"><td colspan=6><?php _e( "Loading..." ); ?></td></tr>
        </tbody>
    </table>
</div>

