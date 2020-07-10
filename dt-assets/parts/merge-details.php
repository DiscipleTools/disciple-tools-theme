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
            <p id="no_dups_message" style="display: none; text-align: center; margin-top: 20px"><?php esc_html_e( 'No duplicates found.', 'disciple_tools' ); ?></p>
            <div id='duplicates_list'></div>
        </div>


        <div class="grid-x grid-padding-x">
            <div class="cell">
                <span style="float:right; bottom: 0;">
                    <button class="button" data-close aria-label="Close reveal" type="button">
                        <?php echo esc_html__( 'Close', 'disciple_tools' )?>
                    </button>
                </span>
            </div>
        </div>
        <button class="close-button" data-close aria-label="Close modal" type="button">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>

<?php } )(); ?>
