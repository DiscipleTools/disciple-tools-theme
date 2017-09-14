<?php
declare(strict_types=1);
?>
<div class="bordered-box">
    <h5><?php _e( "Locations" ); ?></h5>
    <div class="reveal js-list-sort-by-modal" data-reveal>
        <h1><?php _e( "Sort by:" ); ?></h1>
        <div>
            <button class="js-sort-by button small hollow" data-column-index="1"><?php _e( "Name" ); ?></button>
            <button class="js-sort-by button small hollow" data-column-index="2"><?php _e( "Status" ); ?></button>
            <button class="js-sort-by button small hollow" data-column-index="3"><?php _e( "Members" ); ?></button>
            <button class="js-sort-by button small hollow" data-column-index="4"><?php _e( "Groups" ); ?></button>
            <button class="js-sort-by button small hollow" data-column-index="6"><?php _e( "Last modified" ); ?></button>
        </div>
        <button class="close-button" data-close aria-label="<?php _e( "Close" ); ?>" type="button"><!--
            --><span aria-hidden="true">&times;</span><!--
        --></button>
    </div>
    <table class="js-list">
        <thead><tr>
            <th></th>
            <th><?php _e( "Name" ); ?></th>
            <th><?php _e( "Status" ); ?></th>
            <th><?php _e( "Members" ); ?></th>
            <th><?php _e( "Groups" ); ?></th>
            <!--<th><?php _e( "Last modified" ); ?></th>-->
        </tr></thead>
        <tbody>
        <tr class="js-list-loading"><td colspan=6><?php _e( "Loading..." ); ?></td></tr>
        </tbody>
    </table>
</div>
