<div class="reveal" id="advanced-search-modal" data-reveal data-reset-on-close>
    <h3><?php esc_html_e( 'Advanced Search', 'disciple_tools' ) ?></h3>

    <form class="advanced-search-modal-form">
        <table style="border: none;">
            <tbody style="border: none;">
            <tr style="border: none;">
                <td style="vertical-align: middle;">
                    <input class="advanced-search-modal-form-input"
                           style="max-width:540px;display:inline-block;margin-right:0;"
                           type="search" id="advanced-search-modal-form-query"
                           placeholder="<?php esc_html_e( 'Search Query...', 'disciple_tools' ) ?>">
                </td>
                <td style="vertical-align: middle;">
                    <a class="advanced-search-modal-form-button" id="advanced-search-modal-form-button"
                       title="<?php esc_html_e( 'Advanced Search', 'disciple_tools' ) ?>">
                        <img class="dt-icon"
                             src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/search.svg' ) ?>"
                             alt="<?php esc_html_e( 'Advanced Search', 'disciple_tools' ) ?>"/>
                    </a>
                </td>
            </tr>
            </tbody>
        </table>
    </form>

    <div class="advanced-search-modal-results-div" style="display: none;">
        <table style="border: none;">
            <tbody style="border: none;">
            <tr style="border: none;">
                <td colspan="2">Record Hits: <span class="advanced-search-modal-results-total"></span></td>
            </tr>
            <tr style="border: none;">
                <td style="min-width: 400px; vertical-align: top;">
                    <div class="advanced-search-modal-results"
                         style="height: 300px; overflow-x: scroll; overflow-y: scroll;"></div>
                </td>
                <td style="alignment: right; vertical-align: top;">
                    <img class="dt-icon"
                         src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/options.svg' ) ?>"
                         alt="<?php esc_html_e( 'Filter Search Results', 'disciple_tools' ) ?>"/> Filter Types
                    <br><br>
                    <ul>
                        <?php
                        $search_post_types = DT_Posts::get_post_types();
                        foreach ( $search_post_types as $search_post_type ) {
                            $post_settings = DT_Posts::get_post_settings( $search_post_type );
                            $name          = $post_settings['label_plural'];
                            if ( ! empty( $name ) ) {
                                echo '<li>' . esc_html( $name ) . '</li>';
                            }
                        }
                        ?>
                    </ul>
                </td>
            </tr>
            </tbody>
        </table>
    </div>

    <button class="button loader" data-close aria-label="Close reveal" type="button">
        <?php echo esc_html__( 'Cancel', 'disciple_tools' ) ?>
    </button>

    <button class="close-button" data-close aria-label="Close modal" type="button">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
