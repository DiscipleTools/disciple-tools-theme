<?php
declare(strict_types=1);
?>
<div class="bordered-box">
    <h5><?php esc_html_e( "Locations", "disciple_tools" ); ?></h5>
    <div class="reveal js-list-sort-by-modal" data-reveal>
        <h1><?php esc_html_e( "Sort by:", "disciple_tools" ); ?></h1>
        <div>
            <button class="js-sort-by button small hollow" data-column-index="1"><?php esc_html_e( "Name", "disciple_tools" ); ?></button>
            <button class="js-sort-by button small hollow" data-column-index="2"><?php esc_html_e( "Status", "disciple_tools" ); ?></button>
            <button class="js-sort-by button small hollow" data-column-index="3"><?php esc_html_e( "Members", "disciple_tools" ); ?></button>
            <button class="js-sort-by button small hollow" data-column-index="4"><?php esc_html_e( "Groups", "disciple_tools" ); ?></button>
            <button class="js-sort-by button small hollow" data-column-index="6"><?php esc_html_e( "Last modified", "disciple_tools" ); ?></button>
        </div>
        <button class="close-button" data-close aria-label="<?php esc_html_e( "Close", "disciple_tools" ); ?>" type="button"><!--
            --><span aria-hidden="true">&times;</span><!--
        --></button>
    </div>
    <table class="js-list">
        <thead><tr>
            <th></th>
            <th><?php esc_html_e( "Name", "disciple_tools" ); ?></th>
            <th><?php esc_html_e( "Status", "disciple_tools" ); ?></th>
            <th><?php esc_html_e( "Members", "disciple_tools" ); ?></th>
            <th><?php esc_html_e( "Groups", "disciple_tools" ); ?></th>
            <!--<th><?php esc_html_e( "Last modified", "disciple_tools" ); ?></th>-->
        </tr></thead>
        <tbody>
        <tr class="js-list-loading"><td colspan=6><?php esc_html_e( "Loading...", "disciple_tools" ); ?></td></tr>
        </tbody>
    </table>
</div>
