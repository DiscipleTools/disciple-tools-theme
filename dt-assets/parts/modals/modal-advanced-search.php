<div class="reveal" id="advanced-search-modal" data-reveal data-reset-on-close>
    <h3><?php esc_html_e( 'Advanced Search', 'disciple_tools' ) ?></h3>

    <form class="advanced-search-modal-form">
        <table>
            <tbody>
            <tr>
                <td>
                    <input class="advanced-search-modal-form-input"
                           type="search" id="advanced-search-modal-form-query"
                           placeholder="<?php esc_html_e( 'Search Query...', 'disciple_tools' ) ?>">
                </td>
                <td>
                    <a class="advanced-search-modal-form-button button" id="advanced-search-modal-form-button">
                        <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/search-white.svg' ) ?>">
                    </a>
                </td>
            </tr>
            </tbody>
        </table>
    </form>

    <div class="advanced-search-modal-results-div">
        <table>
            <tbody>
            <tr>
                <td colspan="2">
                    <div class="advanced-search-modal-results-post-types-view-at-top">
                        <a class="advanced-search-modal-results-post-types-view-at-top-collapsible-button button hollow"><?php esc_html_e( 'Search Settings', 'disciple_tools' ); ?>
                            <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/chevron_down.svg' ) ?>">
                        </a>
                        <div class="advanced-search-modal-results-post-types-view-at-top-collapsible-content">
                            <?php build_post_types_option_list_html( "advanced-search-modal-post-types-at-top" ); ?>
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
                <td colspan="2"><?php esc_html_e( 'Record Hits:', 'disciple_tools' ); ?> <span class="advanced-search-modal-results-total"></span></td>
            </tr>
            <tr>
                <td class="advanced-search-modal-results-div-col-results-list">
                    <div class="advanced-search-modal-results"></div>
                </td>
                <td class="advanced-search-modal-results-div-col-post-type">
                    <div class="advanced-search-modal-results-post-types-view-at-side">
                        <?php build_post_types_option_list_html( "advanced-search-modal-post-types-at-side" ); ?>
                    </div>
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

<?php

function build_post_types_option_list_html( $group_name ) {
    echo '<input id="all" type="radio" class="advanced-search-modal-post-types" name="' . esc_html( $group_name ) . '" value="all" checked>';
    echo '<label for="all">' . esc_html__( 'All', 'disciple_tools' ) .'</label><br>';

    $search_post_types = DT_Posts::get_post_types();
    foreach ( $search_post_types as $search_post_type ) {
        $post_settings = DT_Posts::get_post_settings( $search_post_type );
        $name          = $post_settings['label_plural'];
        if ( ! empty( $name ) && ( $search_post_type !== 'peoplegroups' ) ) {
            echo '<input id="' . esc_html( $search_post_type ) . '" type="radio" class="advanced-search-modal-post-types" name="' . esc_html( $group_name ) . '" value="' . esc_html( $search_post_type ) . '"><label for="' . esc_html( $search_post_type ) . '">' . esc_html( $name ) . '</label><br>';
        }
    }
}

?>
