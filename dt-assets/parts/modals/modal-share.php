<?php
global $post;
?>

<div class="reveal" id="share-contact-modal" data-reveal style="min-height:500px">

    <h3><?php esc_html_e( 'Share settings', 'disciple_tools' )?></h3>
    
    <h6>
        <?php
        if ( is_singular( "groups" ) ){
            esc_html_e( 'This group is shared with:', 'disciple_tools' );
        } else if ( is_singular( "contacts" ) ) {
            esc_html_e( 'This contact is shared with:', 'disciple_tools' );
        }
        ?>
    </h6>

    <div class="share details">
        <var id="share-result-container" class="result-container share-result-container"></var>
        <div id="share_t" name="form-share">
            <div class="typeahead__container">
                <div class="typeahead__field">
                    <span class="typeahead__query">
                        <input class="js-typeahead-share input-height"
                               name="share[query]" placeholder="<?php echo esc_html_x( "Search Users", 'input field placeholder', 'disciple_tools' ) ?>"
                               autocomplete="off">
                    </span>
                </div>
            </div>
        </div>
    </div>



    <?php
        /**
         * This fires below the share section, and can add additional share based elements.
         */
        do_action( 'dt_share_panel', $post );
    ?>


    <div class="grid-x pin-to-bottom">
        <div class="cell">
            <hr size="1px">
            <span style="float:right; bottom: 0;">
            <button class="button" data-close aria-label="Close reveal" type="button">
                <?php echo esc_html_x( 'Close', 'button', 'disciple_tools' )?>
            </button>
        </span>
        </div>
    </div>

    <button class="close-button" data-close aria-label="Close modal" type="button">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
