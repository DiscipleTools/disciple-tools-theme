<?php

( function () {
    $post_type = dt_get_post_type();
    $post_settings = DT_Posts::get_post_settings( $post_type );
    ?>
    <div class="reveal merge-modal" id="merge-dupe-edit-modal" data-reveal>
        <div class="merge-modal-header">
            <h1 class="merge-modal-heading"><?php esc_html_e( 'Duplicate Contacts', 'disciple_tools' ) ?></h1>
        </div>
        <h4 class="merge-modal-subheading"><?php esc_html_e( 'Original Contact', 'disciple_tools' ) ?></h4>
        <div id="original-contact" class="display-fields"></div>
        <div class="center">
            <h4 class="merge-modal-subheading"><?php esc_html_e( 'Possible Duplicates', 'disciple_tools' ) ?></h4>
            <a id="dismiss_all_duplicates"><?php esc_html_e( 'Dismiss All', 'disciple_tools' ); ?></a>
            <div id="duplicates-spinner" class="loading-spinner active merge-modal-spinner"></div>
        </div>
        <div>
            <p id="no_dups_message" class="merge-modal-no-dups-message"><?php esc_html_e( 'No duplicates found.', 'disciple_tools' ); ?></p>
            <div id="duplicates_list"></div>
        </div>
        <div class="grid-x grid-padding-x">
            <div class="cell">
                <span>
                    <button class="button merge-modal-close" data-close aria-label="Close reveal" type="button">
                        <?php esc_html_e( 'Close', 'disciple_tools' ); ?>
                    </button>
                </span>
            </div>
        </div>
        <button class="close-button" data-close aria-label="Close modal" type="button">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    <div class="reveal" id="merge-with-post-modal" data-reveal style="min-height:500px">
        <h3><?php echo esc_html( sprintf( _x( 'Merge %s', 'Merge Contacts', 'disciple_tools' ), $post_settings['label_plural'] ?? $post_type ) )?></h3>
        <p><?php echo esc_html( sprintf( _x( 'Merge this %1$s with another %2$s', 'Merge this contact with another contact', 'disciple_tools' ), $post_settings['label_singular'] ?? $post_type, $post_settings['label_singular'] ?? $post_type ) )?></p>
        <div class="merge_with details">
            <var id="merge_with-result-container" class="result-container merge_with-result-container"></var>
            <div id="merge_with_t" name="form-merge_with">
                <div class="typeahead__container">
                    <div class="typeahead__field">
                        <span class="typeahead__query">
                            <input class="js-typeahead-merge_with input-height"
                                   name="merge_with[query]" placeholder="<?php echo esc_html( sprintf( _x( 'Search %s', "Search 'something'", 'disciple_tools' ), $post_settings['label_plural'] ?? $post_type ) ) ?>"
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
        <div class="confirm-merge-with-post" style="display: none">
            <p><span id="name-of-post-to-merge"></span> <?php echo esc_html_x( 'selected.', 'added to the end of a sentence', 'disciple_tools' ); ?></p>
            <p><?php esc_html_e( 'Click merge to continue.', 'disciple_tools' ); ?></p>
        </div>
        <div class="grid-x">
            <button class="button button-cancel clear" data-close aria-label="Close reveal" type="button">
                <?php esc_html_e( 'Cancel', 'disciple_tools' ); ?>
            </button>
            <form
                action="<?php echo esc_url( site_url() ); ?>/<?php echo esc_html( dt_get_post_type() ); ?>/mergedetails"
                method="get">
                <input type="hidden" name="currentid" value="<?php echo esc_html( get_the_ID() ); ?>"/>
                <input id="confirm-merge-with-post-id" type="hidden" name="dupeid" value=""/>
                <button type="submit" class="button confirm-merge-with-post" style="display: none;">
                    <?php esc_html_e( 'Merge', 'disciple_tools' ); ?>
                </button>
            </form>
            <button class="close-button" data-close aria-label="Close modal" type="button">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    </div>
<?php } )(); ?>
