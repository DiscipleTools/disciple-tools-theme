<?php

( function () {

    ?>
    <div class="reveal" id="merge-dupe-edit-modal" style="border-radius:10px; padding:0px; padding-bottom:20px; border: 1px solid #3f729b;;" data-reveal>
        <div class="merge-modal-header" style="background-color:#3f729b; color:white; text-align:center;">
            <h1 style="font-size:1.5rem; padding:10px 0px;"><?php esc_html_e( "Duplicate Contacts", 'disciple_tools' ) ?></h1>
        </div>
        <h4 style='text-align: center; font-size: 1.25rem; font-weight: bold; padding:10px 0px 0px; margin-bottom: 0px;'><?php esc_html_e( "Original Contact", 'disciple_tools' ) ?></h4>
        <div id="original-contact" class="display-fields"></div>

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

    <div class="reveal" id="merge-with-contact-modal" data-reveal style="min-height:500px">
        <h3><?php esc_html_e( "Merge Contact", 'disciple_tools' )?></h3>
        <p><?php esc_html_e( "Merge this contact with another contact.", 'disciple_tools' )?></p>

        <div class="merge_with details">
            <var id="merge_with-result-container" class="result-container merge_with-result-container"></var>
            <div id="merge_with_t" name="form-merge_with">
                <div class="typeahead__container">
                    <div class="typeahead__field">
                            <span class="typeahead__query">
                                <input class="js-typeahead-merge_with input-height"
                                       name="merge_with[query]" placeholder="<?php echo esc_html_x( "Search multipliers and contacts", 'input field placeholder', 'disciple_tools' ) ?>"
                                       autocomplete="off">
                            </span>
                        <span class="typeahead__button">
                            <button type="button" class="search_merge_with typeahead__image_button input-height" data-id="user-select_t">
                                <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/chevron_down.svg' ) ?>"/>
                            </button>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <br>
        <div class="confirm-merge-with-contact" style="display: none">
            <p><span  id="name-of-contact-to-merge"></span> <?php echo esc_html_x( "selected.", 'added to the end of a sentence', 'disciple_tools' ) ?></p>
            <p><?php esc_html_e( "Click merge to continue.", 'disciple_tools' ) ?></p>
        </div>

        <div class="grid-x">
            <button class="button button-cancel clear" data-close aria-label="Close reveal" type="button">
                <?php echo esc_html__( 'Cancel', 'disciple_tools' )?>
            </button>
            <form action='<?php echo esc_url( site_url() );?>/contacts/mergedetails' method='get'>
                <input type='hidden' name='currentid' value='<?php echo esc_html( GET_THE_ID() );?>'/>
                <input id="confirm-merge-with-contact-id" type='hidden' name='dupeid' value=''/>
                <button type='submit' class="button confirm-merge-with-contact" style="display: none">
                    <?php echo esc_html__( 'Merge', 'disciple_tools' )?>
                </button>
            </form>
            <button class="close-button" data-close aria-label="Close modal" type="button">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    </div>

<?php } )(); ?>
