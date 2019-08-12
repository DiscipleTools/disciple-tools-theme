<?php
declare(strict_types=1);

( function () {
    if ( ! current_user_can( 'access_groups' ) ) {
        wp_safe_redirect( '/settings' );
    }

    $dt_group_field_options = Disciple_Tools_Groups_Post_Type::instance()->get_custom_fields_settings( false );
    get_header();

    function print_filters(){ ?>
        <div class="list-views">
            <label class="list-view">
                <input type="radio" name="view" value="no_filter" class="js-list-view" autocomplete="off">
                <span id="total_filter_label"><?php esc_html_e( "All", "disciple_tools" ); ?></span>
                <span class="list-view__count js-list-view-count" data-value="total_count">.</span>
            </label>
        </div>
    <?php } ?>

    <div id="errors"> </div>
    <div data-sticky-container class="hide-for-small-only" style="z-index: 9">
        <nav role="navigation"
             data-sticky data-options="marginTop:3;" style="width:100%" data-top-anchor="1"
             class="second-bar">
            <div class="container-width center">
                <a class="button dt-green" style="margin-bottom:0" href="<?php echo esc_url( home_url( '/' ) ) . "groups/new" ?>">
                    <img style="display: inline-block;" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/circle-add.svg' ) ?>"/>
                    <span class="hide-for-small-only"><?php esc_html_e( "Create new group", "disciple_tools" ); ?></span>
                </a>
                <a class="button" style="margin-bottom:0" data-open="filter-modal">
                    <img style="display: inline-block;" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/filter.svg' ) ?>"/>
                    <span class="hide-for-small-only"><?php esc_html_e( "Filter groups", 'disciple_tools' ) ?></span>
                </a>
                <input class="search-input" style="max-width:200px;display: inline-block;margin-bottom:0" type="search" id="search-query" placeholder="search groups">
                <a class="button" style="margin-bottom:0" id="search">
                    <img style="display: inline-block;" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/search-white.svg' ) ?>"/>
                    <span><?php esc_html_e( "Search", 'disciple_tools' ) ?></span>
                </a>
            </div>
        </nav>
    </div>
    <nav  role="navigation" style="width:100%;"
          class="second-bar show-for-small-only center">
        <a class="button dt-green" style="margin-bottom:0" href="<?php echo esc_url( home_url( '/' ) ) . "groups/new" ?>">
            <img style="display: inline-block;" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/circle-add.svg' ) ?>"/>
            <span class="hide-for-small-only"><?php esc_html_e( "Create new group", "disciple_tools" ); ?></span>
        </a>
        <a class="button" style="margin-bottom:0" data-open="filter-modal">
            <img style="display: inline-block;" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/filter.svg' ) ?>"/>
            <span class="hide-for-small-only"><?php esc_html_e( "Filter groups", 'disciple_tools' ) ?></span>
        </a>
        <a class="button" style="margin-bottom:0" id="open-search">
            <img style="display: inline-block;" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/search.svg' ) ?>"/>
            <span class="hide-for-small-only"><?php esc_html_e( "Search groups", 'disciple_tools' ) ?></span>
        </a>
        <div class="hideable-search" style="display: none; margin-top:5px">
            <input class="search-input-mobile" style="max-width:200px;display: inline-block;margin-bottom:0" type="search" id="search-query-mobile" placeholder="search groups">
            <button class="button" style="margin-bottom:0" id="search-mobile"><?php esc_html_e( "Search", 'disciple_tools' ) ?></button>
        </div>
    </nav>


    <div id="content">

        <div id="inner-content" class="grid-x grid-margin-x">

            <aside class="large-3 cell padding-bottom hide-for-small-only">
                <div class="bordered-box js-pane-filters">
                    <?php /* Javascript my move .js-filters-modal-content to this location. */ ?>
                </div>
            </aside>

            <aside class="cell padding-bottom show-for-small-only">
                <div class="bordered-box" style="padding-top:5px;padding-bottom:5px">
                    <div class="js-list-filter filter--closed">
                        <div class="filter__title js-list-filter-title" style="margin-bottom:0"><?php esc_html_e( "Filters", "disciple_tools" ); ?>
                            <div style="display: inline-block" class="loading-spinner active"></div>
                        </div>
                        <div class="js-filters-accordion"></div>
                    </div>
                </div>
            </aside>

            <div class="reveal js-filters-modal" id="filters-modal">
                <div class="js-filters-modal-content">
                    <h5 class="hide-for-small-only" style="display: inline-block"><?php esc_html_e( "Group Filters", "disciple_tools" ); ?></h5>
                    <!-- @todo re-enable when we have more group filters-->
<!--                    <ul class="accordion" id="list-filter-tabs" data-responsive-accordion-tabs="accordion medium-tabs large-accordion">-->
<!--                        <li class="accordion-item" data-accordion-item data-id="my">-->
<!--                            <a href="#" class="accordion-title">-->
<!--                                --><?php //esc_html_e( "Groups Assigned to me", 'disciple_tools' ) ?>
<!--                                <span class="tab-count-span" data-tab="total_my"></span>-->
<!--                            </a>-->
<!--                            <div class="accordion-content" data-tab-content>-->
<!--                                --><?php //print_filters() ?>
<!--                            </div>-->
<!--                        </li>-->
<!--                        <li class="accordion-item" data-accordion-item data-id="shared">-->
<!--                            <a href="#" class="accordion-title">-->
<!--                                --><?php //esc_html_e( "Groups Shared with me", 'disciple_tools' ) ?>
<!--                                <span class="tab-count-span" data-tab="total_shared"></span>-->
<!--                            </a>-->
<!--                            <div class="accordion-content" data-tab-content>-->
<!--                                --><?php //print_filters() ?>
<!--                            </div>-->
<!--                        </li>-->
<!--                        <li class="accordion-item" data-accordion-item data-id="all">-->
<!--                            <a href="#" class="accordion-title">-->
<!--                                --><?php //esc_html_e( "All my groups", 'disciple_tools' ) ?>
<!--                                <span class="tab-count-span" data-tab="total_all"></span>-->
<!--                            </a>-->
<!--                            <div class="accordion-content" data-tab-content>-->
<!--                                --><?php //print_filters() ?>
<!--                            </div>-->
<!--                        </li>-->
<!--                    </ul>-->
                    <div class="list-views is-active">
                        <label class="list-view ">
                            <input type="radio" name="view" value="all" class="js-list-view" autocomplete="off">
                            <?php esc_html_e( "All groups", "disciple_tools" ); ?>
                            <span class="list-view__count js-list-view-count tab-count-span" data-value="all_groups" data-tab="total_all">.</span>
                        </label>
                        <label class="list-view">
                            <input type="radio" name="view" value="my" class="js-list-view" checked autocomplete="off">
                            <?php esc_html_e( "My groups", "disciple_tools" ); ?>
                            <span class="list-view__count js-list-view-count tab-count-span" data-value="my_groups" data-tab="total_my">.</span>
                        </label>
                        <label class="list-view">
                            <input type="radio" name="view" value="shared" class="js-list-view" autocomplete="off">
                            <?php esc_html_e( "Groups shared with me", "disciple_tools" ); ?>
                            <span class="list-view__count js-list-view-count tab-count-span" data-value="shared" data-tab="total_shared">.</span>
                        </label>
                    </div>

                    <h5><?php esc_html_e( 'Custom Filters', "disciple_tools" ); ?></h5>
                    <div style="margin-bottom: 5px">
                        <a data-open="filter-modal"><img style="display: inline-block; margin-right:12px" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/circle-add-blue.svg' ) ?>"/><?php esc_html_e( "Add new filter", 'disciple_tools' ) ?></a>
                    </div>
                    <div class="custom-filters">
                    </div>
                    <div id="saved-filters"></div>
                </div>
            </div>

            <main id="main" class="large-9 cell padding-bottom" role="main">

                <?php get_template_part( 'dt-assets/parts/content', 'groups' ); ?>

            </main> <!-- end #main -->

        </div> <!-- end #inner-content -->

    </div> <!-- end #content -->


    <div class="reveal" id="filter-modal" data-reveal>
        <div class="grid-container" >
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
                    <?php $fields = [ "assigned_to", "created_on", "group_status", "group_type", "location_grid" ];
                    $fields = apply_filters( 'dt_filters_additional_fields', $fields, "groups" );
                    $allowed_types = [ "multi_select", "key_select", "boolean", "date", "location" ];
                    foreach ( $dt_group_field_options as $field_key => $field){
                        if ( in_array( $field["type"], $allowed_types ) && !in_array( $field_key, $fields ) && !( isset( $field["hidden"] ) && $field["hidden"] )){
                            $fields[] = $field_key;
                        }
                    }
                    $connections = Disciple_Tools_Posts::$connection_types;
                    $connections["assigned_to"] = [ "name" => __( "Assigned To", 'disciple_tools' ) ];
                    $connections["location_grid"] = [ "name" => __( "Locations", 'disciple_tools' ) ];
                    ?>
                    <ul class="vertical tabs" data-tabs id="filter-tabs">
                        <?php foreach ( $fields as $index => $field ) :
                            if ( isset( $dt_group_field_options[$field]["name"] ) ) : ?>
                                <li class="tabs-title <?php if ( $index === 0 ){ echo "is-active"; } ?>" data-field="<?php echo esc_html( $field )?>">
                                    <a href="#<?php echo esc_html( $field )?>" <?php if ( $index === 0 ){ echo 'aria-selected="true"'; } ?>>
                                        <?php echo esc_html( $dt_group_field_options[$field]["name"] ) ?></a>
                                </li>
                            <?php elseif ( in_array( $field, array_keys( $connections ) ) ) : ?>
                                <li class="tabs-title" data-field="<?php echo esc_html( $field )?>">
                                    <a href="#<?php echo esc_html( $field )?>">
                                        <?php echo esc_html( $connections[$field]["name"] ) ?></a>
                                </li>
                            <?php elseif ( $field === "created_on" ) : ?>
                                <li class="tabs-title" data-field="<?php echo esc_html( $field )?>">
                                    <a href="#<?php echo esc_html( $field )?>">
                                        <?php esc_html_e( "Creation Date", 'disciple_tools' ) ?></a>
                                </li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div class="cell small-8 tabs-content filter-modal-right" data-tabs-content="filter-tabs">
                    <?php foreach ( $fields as $index => $field ) :
                        $is_multi_select = isset( $dt_group_field_options[$field] ) && $dt_group_field_options[$field]["type"] == "multi_select";
                        if ( in_array( $field, array_keys( $connections ) ) || $is_multi_select ) : ?>
                            <div class="tabs-panel <?php if ( $index === 0 ){ echo "is-active"; } ?>" id="<?php echo esc_html( $field ) ?>">
                                <div class="<?php echo esc_html( $field );?>  <?php echo esc_html( $is_multi_select ? "multi_select" : "" ) ?> details" >
                                    <var id="<?php echo esc_html( $field ) ?>-result-container" class="result-container <?php echo esc_html( $field ) ?>-result-container"></var>
                                    <div id="<?php echo esc_html( $field ) ?>_t" name="form-<?php echo esc_html( $field ) ?>" class="scrollable-typeahead typeahead-margin-when-active">
                                        <div class="typeahead__container">
                                            <div class="typeahead__field">
                                            <span class="typeahead__query">
                                                <input class="js-typeahead-<?php echo esc_html( $field ) ?>" data-field="<?php echo esc_html( $field ) ?>"
                                                       name="<?php echo esc_html( $field ) ?>[query]" placeholder="<?php esc_html_e( "Type to Search", 'disciple_tools' ) ?>"
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
                                    <?php if ( isset( $dt_group_field_options[$field] ) && $dt_group_field_options[$field]["type"] == "key_select" ) :
                                        foreach ( $dt_group_field_options[$field]["default"] as $option_key => $option_value ) :
                                            $label = $option_value["label"] ?>
                                            <div class="key_select_options">
                                                <label style="cursor: pointer">
                                                    <input autocomplete="off" type="checkbox" data-field="<?php echo esc_html( $field ) ?>"
                                                           value="<?php echo esc_html( $option_key ) ?>"> <?php echo esc_html( $label ) ?>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php elseif ( isset( $dt_group_field_options[$field] ) && $dt_group_field_options[$field]["type"] == "boolean" ) : ?>
                                        <div class="boolean_options">
                                            <label style="cursor: pointer">
                                                <input autocomplete="off" type="checkbox" data-field="<?php echo esc_html( $field ) ?>" data-label="<?php esc_html_e( "No", 'disciple_tools' ) ?>"
                                                       value="0"> <?php esc_html_e( "No", 'disciple_tools' ) ?>
                                            </label>
                                        </div>
                                        <div class="boolean_options">
                                            <label style="cursor: pointer">
                                                <input autocomplete="off" type="checkbox" data-field="<?php echo esc_html( $field ) ?>" data-label="<?php esc_html_e( "Yes", 'disciple_tools' ) ?>"
                                                       value="1"> <?php esc_html_e( "Yes", 'disciple_tools' ) ?>
                                            </label>
                                        </div>
                                    <?php elseif ( $field === "created_on" || isset( $dt_group_field_options[$field] ) && $dt_group_field_options[$field]["type"] == "date" ) : ?>
                                        <strong><?php esc_html_e( "Range Start", 'disciple_tools' ) ?></strong>
                                        <button class="clear-date-picker" style="color:firebrick"
                                                data-for="<?php echo esc_html( $field ) ?>_start">
                                            <?php echo esc_html_x( "Clear", 'Clear/empty input', 'disciple_tools' ) ?></button>
                                        <input id="<?php echo esc_html( $field ) ?>_start"
                                               autocomplete="off"
                                               type="text" data-date-format='yy-mm-dd'
                                               class="dt_date_picker" data-delimit="start"
                                               data-field="<?php echo esc_html( $field ) ?>">
                                        <br>
                                        <strong><?php esc_html_e( "Range end", 'disciple_tools' ) ?></strong>
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
                <button style="display: inline-block" class="button loader confirm-filter-contacts"
                        type="button" id="confirm-filter-contacts" data-close >
                    <?php esc_html_e( 'Filter Groups', 'disciple_tools' )?>
                </button>
                <button class="button loader confirm-filter-contacts"
                        type="button" id="save-filter-edits" data-close style="display: none">
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
} )();
get_footer(); ?>
