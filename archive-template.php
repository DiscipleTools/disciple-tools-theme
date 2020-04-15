<?php
declare(strict_types=1);

( function () {
    $post_type = dt_get_url_path();
    if ( !current_user_can( 'access_' . $post_type ) ) {
        wp_safe_redirect( '/settings' );
    }
    $post_settings = apply_filters( "dt_get_post_type_settings", [], $post_type );

    $field_options = $post_settings["fields"];

    get_header();
    ?>

    <div data-sticky-container class="hide-for-small-only" style="z-index: 9">
        <nav role="navigation"
             data-sticky data-options="marginTop:0;" data-top-anchor="1"
             class="second-bar list-actions-bar">
            <div class="container-width center"><!--  /* DESKTOP VIEW BUTTON AREA */ -->
                <a class="button dt-green" href="<?php echo esc_url( home_url( '/' ) . $post_type ) . "/new" ?>">
                    <img style="display: inline-block;" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/circle-add-white.svg' ) ?>"/>
                    <span class="hide-for-small-only"><?php echo esc_html( sprintf( _x( "Create New %s", "Create New record", 'disciple_tools' ), $post_settings["label_singular"] ?? $post_type ) ) ?></span>
                </a>
                <a class="button" data-open="filter-modal">
                    <img style="display: inline-block;" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/filter.svg' ) ?>"/>
                    <span class="hide-for-small-only"><?php esc_html_e( "Filters", 'disciple_tools' ) ?></span>
                </a>
                <input class="search-input" style="max-width:200px;display: inline-block;"
                       type="search" id="search-query"
                       placeholder="<?php echo esc_html( sprintf( _x( "Search %s", "Search 'something'", 'disciple_tools' ), $post_settings["label_plural"] ?? $post_type ) ) ?>">
                <a class="button" id="search">
                    <img style="display: inline-block;" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/search-white.svg' ) ?>"/>
                    <span><?php esc_html_e( "Search", 'disciple_tools' ) ?></span>
                </a>
            </div>
        </nav>
    </div>
    <nav  role="navigation" style="width:100%;"
          class="second-bar show-for-small-only center list-actions-bar"><!--  /* MOBILE VIEW BUTTON AREA */ -->
        <a class="button dt-green" href="<?php echo esc_url( home_url( '/' ) . $post_type ) . "/new" ?>">
            <img style="display: inline-block;" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/add-contact-white.svg' ) ?>"/>
        </a>
        <a class="button" data-open="filter-modal">
            <img style="display: inline-block;" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/filter.svg' ) ?>"/>
        </a>
        <a class="button" id="open-search">
            <img style="display: inline-block;" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/search-white.svg' ) ?>"/>
        </a>
        <div class="hideable-search" style="display: none; margin-top:5px">
            <input class="search-input-mobile" style="max-width:200px;display: inline-block;margin-bottom:0" type="search" id="search-query-mobile"
                   placeholder="<?php echo esc_html( sprintf( _x( "Search %s", "Search 'something'", 'disciple_tools' ), $post_settings["label_plural"] ?? $post_type ) ) ?>">
            <button class="button" style="margin-bottom:0" id="search-mobile"><?php esc_html_e( "Search", 'disciple_tools' ) ?></button>
        </div>
    </nav>
    <div id="content" class="archive-template">
        <div id="inner-content" class="grid-x grid-margin-x grid-margin-y">
            <aside class="cell large-3" id="list-filters">
                <div class="bordered-box">
                    <div class="section-header"><?php esc_html_e( 'Filters', 'disciple_tools' )?>
                        <button class="section-chevron chevron_down">
                            <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/chevron_down.svg' ) ?>"/>
                        </button>
                        <button class="section-chevron chevron_up">
                            <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/chevron_up.svg' ) ?>"/>
                        </button>
                    </div>
                    <div class="section-body">
                        <ul class="accordion" id="list-filter-tabs" data-responsive-accordion-tabs="accordion medium-tabs large-accordion"></ul>
                        <div style="margin-bottom: 5px">
                            <a data-open="filter-modal"><img style="display: inline-block; margin-right:12px" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/circle-add-blue.svg' ) ?>"/><?php esc_html_e( "Add new filter", 'disciple_tools' ) ?></a>
                        </div>
                        <div class="custom-filters"></div>
                    </div>
                </div>
            </aside>

            <main id="main" class="large-9 cell padding-bottom" role="main">
                <div class="bordered-box">
                    <div class="section-header" style="display: inline-block">
                        <span>
                            <?php esc_html_e( 'Records List', 'disciple_tools' )?>
                            <span id="list-loading-spinner" style="display: inline-block" class="loading-spinner active"></span>
                        </span>
                    </div>
                    <p style="display: inline-block" class="filter-result-text"></p>
                    <div style="display: inline-block" id="current-filters"></div>
                    <div class="js-sort-dropdown" style="display: inline-block">
                        <ul class="dropdown menu" data-dropdown-menu>
                            <li>
                                <a href="#"><?php esc_html_e( "Sort", "disciple_tools" ); ?></a>
                                <ul class="menu">
                                    <li>
                                        <a href="#" class="js-sort-by" data-column-index="6" data-order="desc" data-field="post_date">
                                            <?php esc_html_e( "Newest", "disciple_tools" ); ?></a>
                                    </li>
                                    <li>
                                        <a href="#" class="js-sort-by" data-column-index="6" data-order="asc" data-field="post_date">
                                            <?php esc_html_e( "Oldest", "disciple_tools" ); ?></a>
                                    </li>
                                    <li>
                                        <a href="#" class="js-sort-by" data-column-index="6" data-order="desc" data-field="last_modified">
                                            <?php esc_html_e( "Most recently modified", "disciple_tools" ); ?></a>
                                    </li>
                                    <li>
                                        <a href="#" class="js-sort-by" data-column-index="6" data-order="asc" data-field="last_modified">
                                            <?php esc_html_e( "Least recently modified", "disciple_tools" ); ?></a>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </div>

                    <div>
                        <table class="table-remove-top-border js-list stack striped" id="records-table">
                            <thead>
                                <tr class="sortable">
                                    <th></th>
                                    <th class="all" data-id="name"><?php esc_html_e( "Name", "disciple_tools" ); ?></th>
                                    <?php foreach ( $field_options as $field_key => $field_value ){
                                        if ( isset( $field_value["show_in_table"] ) && $field_value["show_in_table"] ) : ?>
                                            <th class="all" data-id="<?php echo esc_html( $field_key ) ?>">
                                                <?php echo esc_html( $field_value["name"] ) ?>
                                            </th>
                                        <?php endif;
                                    }
                                    ?>
                                </tr>
                            </thead>
                            <tbody id="table-content">
                                <tr class="js-list-loading"><td colspan=7><?php esc_html_e( "Loading...", "disciple_tools" ); ?></td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="center">
                        <button id="load-more" class="button" style="display: none"><?php esc_html_e( "Load More", 'disciple_tools' ) ?></button>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <div class="reveal" id="filter-modal" data-reveal>
        <div class="grid-container">
            <div class="grid-x">
                <div class="cell small-4" style="padding: 0 5px 5px 5px">
                    <input type="text" id="new-filter-name"
                           placeholder="<?php esc_html_e( 'Filter Name', 'disciple_tools' )?>"
                           style="margin-bottom: 0"/>
                </div>
                <div class="cell small-8">
                    <div id="selected-filters"></div>
                </div>
            </div>

            <div class="grid-x">
                <div class="cell small-4 filter-modal-left">
                    <?php $fields = [];
                    $allowed_types = [ "multi_select", "key_select", "boolean", "date", "location", "connection" ];
                    foreach ( $field_options as $field_key => $field){
                        if ( $field_key && in_array( $field["type"], $allowed_types ) && !in_array( $field_key, $fields ) && !( isset( $field["hidden"] ) && $field["hidden"] )){
                            $fields[] = $field_key;
                        }
                    }
                    $fields = apply_filters( 'dt_filters_additional_fields', $fields, $post_type ) ?? [];
                    ?>
                    <ul class="vertical tabs" data-tabs id="filter-tabs">
                        <?php foreach ( $fields as $index => $field ) :
                            if ( isset( $field_options[$field]["name"] ) ) : ?>
                                <li class="tabs-title <?php if ( $index === 0 ){ echo "is-active"; } ?>" data-field="<?php echo esc_html( $field )?>">
                                    <a href="#<?php echo esc_html( $field )?>" <?php if ( $index === 0 ){ echo 'aria-selected="true"'; } ?>>
                                        <?php echo esc_html( $field_options[$field]["name"] ) ?></a>
                                </li>
                            <?php elseif ( $field === "created_on" ) : ?>
                                <li class="tabs-title" data-field="<?php echo esc_html( $field )?>">
                                    <a href="#<?php echo esc_html( $field )?>">
                                        <?php esc_html_e( "Creation Date", 'disciple_tools' ) ?></a>
                                </li>
                            <?php else : ?>
                                <?php wp_die( "Cannot implement filter options for field " . esc_html( $field ) ); ?>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div class="cell small-8 tabs-content filter-modal-right" data-tabs-content="filter-tabs">
                    <?php foreach ( $fields as $index => $field ) :
                        $is_multi_select = isset( $field_options[$field] ) && $field_options[$field]["type"] == "multi_select";
                        if ( $field_options[$field]["type"] === "connection" || $field_options[$field]["type"] === "location" || $is_multi_select ) : ?>
                            <div class="tabs-panel <?php if ( $index === 0 ){ echo "is-active"; } ?>" id="<?php echo esc_html( $field ) ?>">
                                <div class="<?php echo esc_html( $field );?>  <?php echo esc_html( $is_multi_select ? "multi_select" : "" ) ?> details" >
                                    <var id="<?php echo esc_html( $field ) ?>-result-container" class="result-container <?php echo esc_html( $field ) ?>-result-container"></var>
                                    <div id="<?php echo esc_html( $field ) ?>_t" name="form-<?php echo esc_html( $field ) ?>" class="scrollable-typeahead typeahead-margin-when-active">
                                        <div class="typeahead__container">
                                            <div class="typeahead__field">
                                                <span class="typeahead__query">
                                                    <input class="js-typeahead-<?php echo esc_html( $field ) ?> input-height"
                                                           data-field="<?php echo esc_html( $field )?>"
                                                           data-type="<?php echo esc_html( $field_options[$field]["type"] ) ?>"
                                                           name="<?php echo esc_html( $field ) ?>[query]"
                                                           placeholder="<?php esc_html_e( "Type to Search", 'disciple_tools' ) ?>"
                                                           autocomplete="off">
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        <?php else : ?>
                            <div class="tabs-panel" id="<?php echo esc_html( $field ) ?>">
                                <div id="<?php echo esc_html( $field ) ?>-options">
                                    <?php if ( isset( $field_options[$field] ) && $field_options[$field]["type"] == "key_select" ) :
                                        foreach ( $field_options[$field]["default"] as $option_key => $option_value ) :
                                            $label = $option_value["label"] ?? ""?>
                                            <div class="key_select_options">
                                                <label style="cursor: pointer">
                                                    <input autocomplete="off" type="checkbox" data-field="<?php echo esc_html( $field ) ?>"
                                                           value="<?php echo esc_html( $option_key ) ?>"> <?php echo esc_html( $label ) ?>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php elseif ( isset( $field_options[$field] ) && $field_options[$field]["type"] == "boolean" ) : ?>
                                        <div class="boolean_options">
                                            <label style="cursor: pointer">
                                                <input autocomplete="off" type="checkbox" data-field="<?php echo esc_html( $field ) ?>"
                                                       data-label="<?php esc_html_e( "No", 'disciple_tools' ) ?>"
                                                       value="0"> <?php esc_html_e( "No", 'disciple_tools' ) ?>
                                            </label>
                                        </div>
                                        <div class="boolean_options">
                                            <label style="cursor: pointer">
                                                <input autocomplete="off" type="checkbox" data-field="<?php echo esc_html( $field ) ?>"
                                                       data-label="<?php esc_html_e( "Yes", 'disciple_tools' ) ?>"
                                                       value="1"> <?php esc_html_e( "Yes", 'disciple_tools' ) ?>
                                            </label>
                                        </div>
                                    <?php elseif ( $field === "created_on" || isset( $field_options[$field] ) && $field_options[$field]["type"] == "date" ) : ?>
                                        <strong><?php echo esc_html_x( "Range Start", 'The start date of a date range', 'disciple_tools' ) ?></strong>
                                        <button class="clear-date-picker" style="color:firebrick"
                                                data-for="<?php echo esc_html( $field ) ?>_start">
                                            <?php echo esc_html_x( "Clear", 'Clear/empty input', 'disciple_tools' ) ?></button>
                                        <input id="<?php echo esc_html( $field ) ?>_start"
                                               autocomplete="off"
                                               type="text" data-date-format='yy-mm-dd'
                                               class="dt_date_picker" data-delimit="start"
                                               data-field="<?php echo esc_html( $field ) ?>">
                                        <br>
                                        <strong><?php echo esc_html_x( "Range End", 'The end date of a date range', 'disciple_tools' ) ?></strong>
                                        <button class="clear-date-picker"
                                                style="color:firebrick"
                                                data-for="<?php echo esc_html( $field ) ?>_end">
                                            <?php echo esc_html_x( "Clear", 'Clear/empty input', 'disciple_tools' ) ?></button>
                                        <input id="<?php echo esc_html( $field ) ?>_end"
                                               autocomplete="off" type="text"
                                               data-date-format='yy-mm-dd'
                                               class="dt_date_picker" data-delimit="end"
                                               data-field="<?php echo esc_html( $field ) ?>">

                                    <?php endif; ?>
                                </div>
                            </div>

                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="grid-x grid-padding-x">
            <div class="cell small-4 filter-modal-left">
                <button class="button button-cancel clear" data-close aria-label="Close reveal" type="button">
                    <?php esc_html_e( 'Cancel', 'disciple_tools' )?>
                </button>
            </div>
            <div class="cell small-8 filter-modal-right confirm-buttons">
                <button style="display: inline-block" class="button loader confirm-filter-records" type="button" id="confirm-filter-records" data-close >
                    <?php esc_html_e( 'Filter Records', 'disciple_tools' )?>
                </button>
                <button class="button loader confirm-filter-records" type="button" id="save-filter-edits" data-close style="display: none">
                    <?php esc_html_e( 'Save', 'disciple_tools' )?>
                </button>
            </div>
        </div>
        <button class="close-button" data-close aria-label="Close modal" type="button">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>

    <?php get_template_part( 'dt-assets/parts/modals/modal', 'filters' ); ?>

    <?php
    get_footer();
} )();
