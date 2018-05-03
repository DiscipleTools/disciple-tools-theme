<?php
declare(strict_types=1);
?>
<div class="bordered-box list-box" >
    <h5 class="hide-for-small-only" style="display: inline-block"><?php esc_html_e( "Contacts", "disciple_tools" ); ?></h5>
    <div style="display: inline-block" class="loading-spinner active"></div>
    <!--    <img style="display: inline-block;height: 25px;margin-left:10px" src="--><?php //echo esc_html( get_template_directory_uri() . '/dt-assets/images/funnel.svg' ) ?><!--"/>-->
    <span style="display: inline-block" class="filter-result-text"></span>
    <div style="display: inline-block" id="current-filters"></div>
    <div class="js-sort-dropdown" hidden>
        <ul class="dropdown menu" data-dropdown-menu>
            <li>
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

    <table class="table-remove-top-border js-list stack striped">
        <thead>
            <tr class="sortable">
                <th class="all" data-id="name" data-priority="2"><?php esc_html_e( "Name" ); ?></th>
                <th class="not-mobile" data-id="overall_status" data-sort="asc"><?php esc_html_e( "Status", "disciple_tools" ); ?></th>
                <th class="not-mobile" data-id="seeker_path"><?php esc_html_e( "Seeker Path", "disciple_tools" ); ?></th>
                <th class="desktop" data-id="faith_milestones"><?php esc_html_e( "Faith Milestones", "disciple_tools" ); ?></th>
                <th class="desktop" data-id="assigned_to" data-priority="4"><?php esc_html_e( "Assigned to", "disciple_tools" ); ?></th>
                <th class="not-mobile" data-id="locations"><?php esc_html_e( "Location", "disciple_tools" ); ?></th>
                <th class="not-mobile" data-id="groups" data-priority="3"><?php esc_html_e( "Group", "disciple_tools" ); ?></th>
            </tr>
        </thead>
        <tbody>
        <tr class="js-list-loading"><td colspan=7><?php esc_html_e( "Loading...", "disciple_tools" ); ?></td></tr>
        </tbody>
    </table>
    <div class="center">
        <button id="load-more" class="button"><?php esc_html_e( "Load more contacts", 'disciple_tools' ) ?></button>
    </div>

</div>
