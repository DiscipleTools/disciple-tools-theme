<?php
declare(strict_types=1);
?>
<div class="bordered-box">
    <h5 style="display: inline-block"><?php esc_html_e( "Contacts", "disciple_tools" ); ?></h5>
    <div class="loading-list-progress progress" role="progressbar" tabindex="0" aria-valuenow="50" aria-valuemin="0" aria-valuetext="50 percent" aria-valuemax="100" style="display: none">
        <div class="progress-meter" style="width: 50%">
            <p class="progress-meter-text">25%</p>
        </div>
    </div>
    <img style="display: inline-block;height: 25px;margin-left:10px" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/funnel.svg' ) ?>"/>
    <div style="display: inline-block" id="current-filters"></div>
    <div class="js-sort-dropdown" hidden>
        <ul class="dropdown menu" data-dropdown-menu>
            <li>
                <a class="button dt-green" href="<?php echo esc_url( home_url( '/' ) ) . "contacts/new" ?>"><?php esc_html_e( "Create new contact", "disciple_tools" ); ?></a>
            </li>
            <li>
                <a href="#"><?php esc_html_e( "Sort" ); ?></a>
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
            <th class="all" data-priority="2"><?php esc_html_e( "Name" ); ?></th>
            <th class="not-mobile"><?php esc_html_e( "Status", "disciple_tools" ); ?></th>
            <th class="desktop"><?php esc_html_e( "Faith Milestones", "disciple_tools" ); ?></th>
            <th class="desktop" data-priority="4"><?php esc_html_e( "Assigned to", "disciple_tools" ); ?></th>
            <th class="not-mobile"><?php esc_html_e( "Location", "disciple_tools" ); ?></th>
            <th class="not-mobile" data-priority="3"><?php esc_html_e( "Group", "disciple_tools" ); ?></th>
        </tr></thead>
        <tbody>
            <tr class="js-list-loading"><td colspan=7><?php esc_html_e( "Loading...", "disciple_tools" ); ?></td></tr>
        </tbody>
    </table>

</div>
