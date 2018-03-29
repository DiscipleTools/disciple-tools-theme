<?php
declare(strict_types=1);
?>
<div class="bordered-box">
    <h5><?php esc_html_e( "Groups" ); ?></h5>
    <div class="loading-list-progress progress" role="progressbar" tabindex="0" aria-valuenow="50" aria-valuemin="0" aria-valuetext="50 percent" aria-valuemax="100" style="display: none">
        <div class="progress-meter" style="width: 50%">
            <p class="progress-meter-text">25%</p>
        </div>
    </div>

    <div class="js-sort-dropdown" hidden>
        <ul class="dropdown menu" data-dropdown-menu>
            <li>
                <a class="button dt-green" href="<?php echo esc_url( home_url( '/' ) ) . "groups/new" ?>"><?php esc_html_e( "Create new group", "disciple_tools" ); ?></a>
            </li>
            <li>
                <a href="#"><?php esc_html_e( "Sort", "disciple_tools" ); ?></a>
                <ul class="menu">
                    <li><a href="#" class="js-sort-by" data-column-index="6" data-order="desc">
                        <?php esc_html_e( "Most recent", "disciple_tools" ); ?>
                    </a></li>
                    <li><a href="#" class="js-sort-by" data-column-index="6" data-order="asc">
                        <?php esc_html_e( "Least recent", "disciple_tools" ); ?>
                    </a></li>
                </ul>
            </li>
        </ul>
    </div>

    <table class="table-remove-top-border js-list">
        <thead><tr>
            <th data-priority="2"><?php esc_html_e( "Name", "disciple_tools" ); ?></th>
            <th><?php esc_html_e( "Status", "disciple_tools" ); ?></th>
            <th><?php esc_html_e( "Type", "disciple_tools" ); ?></th>
            <th><?php esc_html_e( "Members", "disciple_tools" ); ?></th>
            <th><?php esc_html_e( "Leader", "disciple_tools" ); ?></th>
            <th><?php esc_html_e( "Location", "disciple_tools" ); ?></th>
        </tr></thead>
        <tbody>
            <tr class="js-list-loading"><td colspan=7><?php esc_html_e( "Loading...", "disciple_tools" ); ?></td></tr>
        </tbody>
    </table>
</div>

