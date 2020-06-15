<?php

( function () {

    ?>
    <div class="reveal" id="merge-dupe-edit-modal" style="border-radius:10px; padding:0px; padding-bottom:20px; border: 1px solid #3f729b;;" data-reveal>
        <div class="merge-modal-header" style="background-color:#3f729b; color:white; text-align:center;">
            <h1 style="font-size:1.5rem; padding:10px 0px;"><?php esc_html_e( "Duplicate Contacts", 'disciple_tools' ) ?></h1>
        </div>
        <h4 style='text-align: center; font-size: 1.25rem; font-weight: bold; padding:10px 0px 0px; margin-bottom: 0px;'><?php esc_html_e( "Original Contact", 'disciple_tools' ) ?></h4>
        <div id="original-contact"></div>

        <div style="text-align: center">
            <h4 style='text-align: center; font-size: 1.25rem; font-weight: bold; padding:20px 0 0; margin-bottom: 0; display: inline-block'><?php esc_html_e( "Possible Duplicates", 'disciple_tools' ) ?></h4>
            <div style="display: inline-block; " id="duplicates-spinner" class="loading-spinner active"></div>
            <a id='dismiss_all_duplicates' style="position: absolute; padding-top:23px"><?php esc_html_e( 'Dismiss All', 'disciple_tools' ); ?></a>
        </div>
        <div class="display-fields" style="padding:10px;">
        </div>
    </div>

<?php } )(); ?>
