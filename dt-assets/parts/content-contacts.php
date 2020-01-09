<?php
declare(strict_types=1);
?>
<div class="bordered-box list-box" >
    <!-- content-contacts -->
    <div class="section-header">
        <span>
            <?php esc_html_e( 'Contacts List', 'disciple_tools' )?>
            <span id="list-loading-spinner" style="display: inline-block" class="loading-spinner active"></span>
        </span>
        <button class="help-button float-right" data-section="contacts-list-help-text">
            <img class="help-icon" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/help.svg' ) ?>"/>
        </button>
    </div>
    <span style="display: inline-block" class="filter-result-text"></span>
    <div style="display: inline-block" id="current-filters"></div>
    <div class="js-sort-dropdown" style="display: inline-block">
        <ul class="dropdown menu" data-dropdown-menu>
            <li>
                <a href="#"><?php esc_html_e( "Sort", "disciple_tools" ); ?></a>
                <ul class="menu">
                    <li><a href="#" class="js-sort-by" data-column-index="6" data-order="desc" data-field="post_date">
                        <?php esc_html_e( "Newest", "disciple_tools" ); ?>
                    </a></li>
                    <li><a href="#" class="js-sort-by" data-column-index="6" data-order="asc" data-field="post_date">
                        <?php esc_html_e( "Oldest", "disciple_tools" ); ?>
                    </a></li>
                    <li><a href="#" class="js-sort-by" data-column-index="6" data-order="desc" data-field="last_modified">
                        <?php esc_html_e( "Most recently modified", "disciple_tools" ); ?>
                    </a></li>
                    <li><a href="#" class="js-sort-by" data-column-index="6" data-order="asc" data-field="last_modified">
                        <?php esc_html_e( "Least recently modified", "disciple_tools" ); ?>
                    </a></li>
                </ul>
            </li>
        </ul>
    </div>
    <div class="show-closed-switch">
        <?php esc_html_e( "Closed Contacts", 'disciple_tools' ) ?>
        <div class="switch tiny">
            <input class="switch-input" id="show_closed" type="checkbox" name="testGroup">
            <label class="switch-paddle" for="show_closed"></label>
        </div>
        <button class="help-button float-right" data-section="contacts-switch-help-text">
            <img class="help-icon" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/help.svg' ) ?>"/>
        </button>
    </div>
    <table class="table-remove-top-border js-list stack striped">
        <thead>
            <tr class="sortable">
                <th class="all" data-id="name"><?php esc_html_e( "Name", "disciple_tools" ); ?></th>
                <th class="not-mobile" data-id="overall_status" data-sort="asc"><?php esc_html_e( "Status", "disciple_tools" ); ?></th>
                <th class="not-mobile" data-id="seeker_path"><?php esc_html_e( "Seeker Path", "disciple_tools" ); ?></th>
                <th class="desktop" data-id="faith_milestones"><?php esc_html_e( "Faith Milestones", "disciple_tools" ); ?></th>
                <th class="desktop" data-id="assigned_to"><?php esc_html_e( "Assigned to", "disciple_tools" ); ?></th>
                <th class="not-mobile" data-id="location_grid"><?php esc_html_e( "Location", "disciple_tools" ); ?></th>
                <th class="not-mobile" data-id="groups"><?php esc_html_e( "Group", "disciple_tools" ); ?></th>
                <th class="not-mobile" data-id="last_modified"><?php esc_html_e( "Last Modified", "disciple_tools" ); ?></th>
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
