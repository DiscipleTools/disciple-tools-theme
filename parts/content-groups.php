<?php
declare(strict_types=1);
?>
<div class="bordered-box">
    <h5><?php esc_html_e( "Groups" ); ?></h5>

    <div class="js-sort-dropdown" hidden>
        <ul class="dropdown menu" data-dropdown-menu>
            <li>
                <a href="<?php echo esc_url( home_url( '/' ) ) . "/groups/new" ?>"><?php esc_html_e( "Create new group" ); ?></a>
            </li>
            <li>
                <a href="#"><?php esc_html_e( "Sort" ); ?></a>
                <ul class="menu">
                    <li><a href="#" class="js-sort-by" data-column-index="6" data-order="desc">
                        <?php esc_html_e( "Most recent" ); ?>
                    </a></li>
                    <li><a href="#" class="js-sort-by" data-column-index="6" data-order="asc">
                        <?php esc_html_e( "Least recent" ); ?>
                    </a></li>
                </ul>
            </li>
        </ul>
    </div>

    <table class="table-remove-top-border js-list">
        <thead><tr>
            <th data-priority="1"></th>
            <th data-priority="2"><?php esc_html_e( "Name" ); ?></th>
            <th><?php esc_html_e( "Status" ); ?></th>
            <th><?php esc_html_e( "Members" ); ?></th>
            <th><?php esc_html_e( "Leader" ); ?></th>
            <th><?php esc_html_e( "Location" ); ?></th>
            <!--<th><?php esc_html_e( "Last modified" ); ?></th>-->
        </tr></thead>
        <tbody>
            <tr class="js-list-loading"><td colspan=6><?php esc_html_e( "Loading..." ); ?></td></tr>
        </tbody>
    </table>
</div>

