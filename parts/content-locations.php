<?php
declare(strict_types=1);
?>
<div class="bordered-box">
    <h5><?php esc_html_e( "Locations" ); ?></h5>
    <div class="reveal js-list-sort-by-modal" data-reveal>
        <h1><?php esc_html_e( "Sort by:" ); ?></h1>
        <div>
            <button class="js-sort-by button small hollow" data-column-index="1"><?php esc_html_e( "Name" ); ?></button>
            <button class="js-sort-by button small hollow" data-column-index="2"><?php esc_html_e( "Status" ); ?></button>
            <button class="js-sort-by button small hollow" data-column-index="3"><?php esc_html_e( "Members" ); ?></button>
            <button class="js-sort-by button small hollow" data-column-index="4"><?php esc_html_e( "Groups" ); ?></button>
            <button class="js-sort-by button small hollow" data-column-index="6"><?php esc_html_e( "Last modified" ); ?></button>
        </div>
        <button class="close-button" data-close aria-label="<?php esc_html_e( "Close" ); ?>" type="button"><!--
            --><span aria-hidden="true">&times;</span><!--
        --></button>
    </div>
    <table class="js-list">
        <thead><tr>
            <th></th>
            <th><?php esc_html_e( "Name" ); ?></th>
            <th><?php esc_html_e( "Status" ); ?></th>
            <th><?php esc_html_e( "Members" ); ?></th>
            <th><?php esc_html_e( "Groups" ); ?></th>
            <!--<th><?php esc_html_e( "Last modified" ); ?></th>-->
        </tr></thead>
        <tbody>
        <tr class="js-list-loading"><td colspan=6><?php esc_html_e( "Loading..." ); ?></td></tr>
        </tbody>
    </table>
</div>
