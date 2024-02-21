<?php
declare(strict_types=1);

dt_please_log_in();

( function () {
    $post_type = dt_get_post_type();
    if ( !current_user_can( 'access_' . $post_type ) ) {
        wp_safe_redirect( '/settings' );
        exit();
    }
    $post_settings = DT_Posts::get_post_settings( $post_type );

    $field_options = $post_settings['fields'];

    get_header();
    ?>
    <div data-sticky-container class="hide-for-small-only" style="z-index: 9">
        <nav role="navigation"
             data-sticky data-options="marginTop:0;" data-top-anchor="1"
             class="second-bar list-actions-bar">
            <div class="container-width buttons-row" ><!--  /* DESKTOP VIEW BUTTON AREA */ -->
                <a class="button dt-green create-post-desktop" href="<?php echo esc_url( home_url( '/' ) . $post_type ) . '/new' ?>">
                    <img class="dt-white-icon" style="display: inline-block;" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/circle-add.svg' ) ?>"/>
                    <span class="hide-for-small-only"><?php echo esc_html( sprintf( _x( 'Create New %s', 'Create New record', 'disciple_tools' ), $post_settings['label_singular'] ?? $post_type ) ) ?></span>
                </a>
                <a class="button filter-posts-desktop" data-open="filter-modal">
                    <img class="dt-white-icon" style="display: inline-block;" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/filter.svg?v=2' ) ?>"/>
                    <span class="hide-for-small-only"><?php esc_html_e( 'Filters', 'disciple_tools' ) ?></span>
                </a>
                <?php do_action( 'archive_template_action_bar_buttons', $post_type ) ?>
                <div class="search-wrapper">
                    <span class="text-input-wrapper">
                        <input class="search-input search-input--desktop" style="margin-right: 0;"
                               type="text" id="search-query" name="search"
                               placeholder="<?php echo esc_html( sprintf( _x( 'Search %s', "Search 'something'", 'disciple_tools' ), $post_settings['label_plural'] ?? $post_type ) ) ?>">
                        <div class="search-input__clear-button" title="<?php echo esc_html( __( 'Clear', 'disciple_tools' ) ) ?>">
                            <span>&times;</span>
                        </div>
                    </span>
                    <a class="advanced_search" id="advanced_search" title="<?php esc_html_e( 'Advanced Search', 'disciple_tools' ) ?>">
                        <img class="dt-icon" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/options.svg' ) ?>" alt="<?php esc_html_e( 'Advanced Search', 'disciple_tools' ) ?>" />
                        <?php
                        $fields_to_search = [];
                        $all_searchable_fields = $post_settings['fields'];
                        $all_searchable_fields['comment'] = [ 'name' => __( 'Comments', 'disciple_tools' ), 'type' => 'text' ];
                        if ( isset( $_COOKIE['fields_to_search'] ) ) {
                            $fields_to_search = json_decode( stripslashes( sanitize_text_field( wp_unslash( $_COOKIE['fields_to_search'] ) ) ) );
                            if ( $fields_to_search ){
                                $fields_to_search = dt_recursive_sanitize_array( $fields_to_search );
                            }
                        }
                        //order fields alphabetically by Name
                        uasort( $all_searchable_fields, function ( $a, $b ){
                            return $a['name'] <=> $b['name'];
                        });
                        ?>
                        <span class="badge alert advancedSearch-count" style="<?php if ( !$fields_to_search ) { echo esc_html( 'display:none' ); } ?>"><?php if ( $fields_to_search ){ echo count( $fields_to_search ); } ?></span>
                    </a>
                </div>
                <a class="button" id="search">
                    <img class="dt-white-icon" style="display: inline-block;" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/search.svg' ) ?>"/>
                    <span><?php esc_html_e( 'Search', 'disciple_tools' ) ?></span>
                </a>
                <?php if ( class_exists( 'DT_Mapbox_API' ) && DT_Mapbox_API::get_key() ) : ?>
                    <button class="button export_map_list no-margin icon-button" style="font-size:large;padding: 0.1rem 0.75rem">
                        <i class="fi-map"></i>
                    </button>
                <?php endif; ?>
            </div>
            <div id="advanced_search_picker"  class="list_field_picker" style="display:none; padding:20px; border-radius:5px; background-color:#ecf5fc;">
                <p style="font-weight:bold"><?php esc_html_e( 'Choose which fields to search', 'disciple_tools' ); ?></p>
                <ul class="ul-no-bullets" style="">

                <li style="" class="">
                    <label style="margin-right:15px; cursor:pointer">
                        <input type="checkbox" value="all"
                                <?php echo esc_html( in_array( 'all', $fields_to_search ) ? 'checked' : '' ); ?>

                                style="margin:0">
                        <b><?php esc_html_e( 'Search All Fields', 'disciple_tools' ) ?></b>
                    </label>
                </li>

                <li style="" class="">
                    <label style="margin-right:15px; cursor:pointer">
                        <input type="checkbox" value="comms"
                            <?php echo esc_html( in_array( 'comms', $fields_to_search ) ? 'checked' : '' ); ?>

                               style="margin:0">
                        <?php esc_html_e( 'Communication Channels', 'disciple_tools' ) ?>
                    </label>
                </li>

                <?php foreach ( $all_searchable_fields as $field_key => $field_values ):
                    if ( !empty( $field_values['hidden'] ) || $field_values['type'] !== 'text' ){
                        continue;
                    }
                    ?>
                    <li style="" class="">
                        <label style="margin-right:15px; cursor:pointer">
                            <input type="checkbox" value="<?php echo esc_html( $field_key ); ?>"
                                    <?php echo esc_html( in_array( $field_key, $fields_to_search ) ? 'checked' : '' );
                                    ?>
                                    style="margin:0">
                                    <?php echo esc_html( $field_values['name'] ); ?>
                        </label>
                    </li>
                <?php endforeach; ?>
                </ul>
                <button class="button" id="save_advanced_search_choices" style="display: inline-block"><?php esc_html_e( 'Apply', 'disciple_tools' ); ?></button>
                <a class="button clear" id="advanced_search_reset" style="display: inline-block"><?php esc_html_e( 'reset to default', 'disciple_tools' ); ?></a>
            </div>
        </nav>
    </div>
    <nav  role="navigation" style="width:100%;"
          class="second-bar show-for-small-only center list-actions-bar"><!--  /* MOBILE VIEW BUTTON AREA */ -->
        <div class="buttons-row">
            <a class="button dt-green create-post-mobile" href="<?php echo esc_url( home_url( '/' ) . $post_type ) . '/new' ?>">
                <img class="dt-white-icon" style="display: inline-block;" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/circle-add.svg' ) ?>"/>
            </a>
            <a class="button filter-posts-mobile" data-open="filter-modal">
                <img class="dt-white-icon" style="display: inline-block;" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/filter.svg?v=2' ) ?>"/>
            </a>
            <a class="button" id="open-search">
                <img class="dt-white-icon" style="display: inline-block;" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/search.svg' ) ?>"/>
            </a>
            <?php if ( class_exists( 'DT_Mapbox_API' ) && DT_Mapbox_API::get_key() ) : ?>
                <button class="button export_map_list no-margin icon-button" style="font-size:large;padding: 0.1rem 0.75rem">
                    <i class="fi-map"></i>
                </button>
            <?php endif; ?>
        </div>
        <div class="hideable-search" style="display: none; margin-top:5px">
            <div class="search-wrapper">
                <span class="text-input-wrapper">
                    <input class="search-input search-input--mobile" name="search" type="text" id="search-query-mobile"
                        placeholder="<?php echo esc_html( sprintf( _x( 'Search %s', "Search 'something'", 'disciple_tools' ), $post_settings['label_plural'] ?? $post_type ) ) ?>">
                    <div class="search-input__clear-button" title="<?php echo esc_html( __( 'Clear', 'disciple_tools' ) ) ?>">
                        <span>&times;</span>
                    </div>
                </span>
                <a class="advanced_search" id="advanced_search_mobile" title="<?php esc_html_e( 'Advanced Search', 'disciple_tools' ) ?>">
                    <img class="dt-icon" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/options.svg' ) ?>" alt="<?php esc_html_e( 'Advanced Search', 'disciple_tools' ) ?>" />
                    <?php
                    $fields_to_search = [];
                    $all_searchable_fields = $post_settings['fields'];
                    $all_searchable_fields['comment'] = [ 'name' => 'Comments', 'type' => 'text' ];
                    if ( isset( $_COOKIE['fields_to_search'] ) ) {
                        $fields_to_search = json_decode( stripslashes( sanitize_text_field( wp_unslash( $_COOKIE['fields_to_search'] ) ) ) );
                        if ( $fields_to_search ){
                            $fields_to_search = dt_recursive_sanitize_array( $fields_to_search );
                        }
                    }
                    //order fields alphabetically by Name
                    uasort( $all_searchable_fields, function ( $a, $b ){
                        return $a['name'] <=> $b['name'];
                    });
                    ?>
                    <span class="badge alert advancedSearch-count" style="<?php if ( !$fields_to_search ) { echo esc_html( 'display:none' ); } ?>"><?php if ( $fields_to_search ){ echo count( $fields_to_search ); } ?></span>
                </a>
                <button class="button" style="margin-bottom:0" id="search-mobile"><?php esc_html_e( 'Search', 'disciple_tools' ) ?></button>
            </div>
        </div>

        <div id="advanced_search_picker_mobile"  class="list_field_picker" style="display:none; padding:20px; border-radius:5px; background-color:#ecf5fc;">
                <p style="font-weight:bold"><?php esc_html_e( 'Choose which fields to search', 'disciple_tools' ); ?></p>
                <ul class="ul-no-bullets" style="">

                <li style="" class="">
                    <label style="margin-right:15px; cursor:pointer">
                        <input type="checkbox" value="all"
                                <?php echo esc_html( in_array( 'all', $fields_to_search ) ? 'checked' : '' ); ?>

                                style="margin:0">
                                <b><?php esc_html_e( 'Search All Fields', 'disciple_tools' ) ?></b>
                    </label>
                </li>

                <li style="" class="">
                    <label style="margin-right:15px; cursor:pointer">
                        <input type="checkbox" value="comms"
                            <?php echo esc_html( in_array( 'comms', $fields_to_search ) ? 'checked' : '' ); ?>

                               style="margin:0">
                        <?php esc_html_e( 'Communication Channels', 'disciple_tools' ) ?>
                    </label>
                </li>

                <?php foreach ( $all_searchable_fields as $field_key => $field_values ):
                    if ( !empty( $field_values['hidden'] ) || $field_values['type'] !== 'text' ){
                        continue;
                    }
                    ?>
                    <li style="" class="">
                        <label style="margin-right:15px; cursor:pointer">
                            <input type="checkbox" value="<?php echo esc_html( $field_key ); ?>"
                                    <?php echo esc_html( in_array( $field_key, $fields_to_search ) ? 'checked' : '' );
                                    ?>
                                    style="margin:0">
                            <?php echo esc_html( $field_values['name'] ); ?>
                        </label>
                    </li>
                <?php endforeach; ?>
                </ul>
                <button class="button" id="save_advanced_search_choices_mobile" style="display: inline-block"><?php esc_html_e( 'Apply', 'disciple_tools' ); ?></button>
                <a class="button clear" id="advanced_search_reset_mobile" style="display: inline-block"><?php esc_html_e( 'reset to default', 'disciple_tools' ); ?></a>
            </div>
    </nav>
    <div id="content" class="archive-template">
        <div id="inner-content" class="grid-x grid-margin-x grid-margin-y">
            <aside class="cell large-3 xxlarge-2" id="list-filters">
                <div class="bordered-box">
                    <div class="section-header">
                        <?php echo esc_html( sprintf( _x( '%s Filters', 'Contacts Filters', 'disciple_tools' ), DT_Posts::get_post_settings( $post_type )['label_plural'] ) ) ?>
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
                            <a data-open="filter-modal"><img class="dt-blue-icon dt-icon" style="display: inline-block; margin-right:12px" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/circle-add.svg' ) ?>"/><?php esc_html_e( 'Create custom filter', 'disciple_tools' ) ?></a>
                        </div>
                        <div class="custom-filters"></div>
                    </div>
                </div>
                <br>
                <div class="bordered-box">
                    <div class="section-header">
                        <?php echo esc_html( _x( 'Split By', 'Split By', 'disciple_tools' ) ) ?>
                        <button class="section-chevron chevron_down">
                            <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/chevron_down.svg' ) ?>"/>
                        </button>
                        <button class="section-chevron chevron_up">
                            <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/chevron_up.svg' ) ?>"/>
                        </button>
                    </div>
                    <div class="section-body">
                        <table>
                            <tbody style="border: none;">
                            <tr style="border: none;">
                                <td style="padding:0">
                                    <select id="split_by_current_filter_select" style="margin-bottom: 0">
                                        <option value="" disabled selected><?php echo esc_html( _x( 'select split by field', 'disciple_tools' ) ) ?></option>
                                        <?php
                                        $split_by_fields = [];
                                        foreach ( DT_Posts::get_post_settings( $post_type )['fields'] ?? [] as $key => $field ){
                                            if ( in_array( $field['type'], [ 'multi_select', 'key_select', 'tags', 'user_select', 'location', 'boolean', 'connection' ] ) ){
                                                if ( !isset( $field['private'] ) || !$field['private'] ){
                                                    $split_by_fields[$key] = $field;
                                                }
                                            }
                                        }

                                        // Sort identified list of split by fields;
                                        uasort( $split_by_fields, function ( $a, $b ){
                                            if ( $a['name'] == $b['name'] ){
                                                return 0;
                                            }
                                            return ( $a['name'] < $b['name'] ) ? -1 : 1;
                                        } );

                                        // Display split by fields.
                                        foreach ( $split_by_fields as $split_by_field_key => $split_by_field ){
                                            ?>
                                            <option
                                                value="<?php echo esc_attr( $split_by_field_key ) ?>"><?php echo esc_attr( sprintf( _x( '%1$s - (%2$s)', 'disciple_tools' ), $split_by_field['name'], $split_by_field_key ) ) ?></option>
                                            <?php
                                        }
                                        ?>
                                    </select>
                                </td>
                                <td>
                                    <button id="split_by_current_filter_button" class="button loader" style='margin-bottom: 0'><?php echo esc_html( _x( 'Go', 'disciple_tools' ) ) ?></button>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                        <ul id="list-filter-tabs" class="accordion split-by-current-filter-accordion"
                            data-responsive-accordion-tabs="accordion medium-tabs large-accordion"
                            style="display: none;">
                            <li class="accordion-item is-active" data-accordion-item data-id="split_by">
                                <a href="#" class="accordion-title" data-id="split_by">
                                    <?php echo esc_attr( _x( 'Summary', 'disciple_tools' ) ) ?>
                                </a>
                                <div class="accordion-content" data-tab-content>
                                    <div id="split_by_current_filter_results" class="list-views"></div>
                                </div>
                            </li>
                        </ul>
                        <span id="split_by_current_filter_no_results_msg" style="display: none;margin-inline-start: 10px"><?php echo esc_attr( __( 'No results found', 'disciple_tools' ) ) ?></span>
                    </div>
                </div>
                <br>
                <div class="bordered-box">
                    <div class="section-header">
                        <?php echo esc_html( _x( 'List Exports', 'List Exports', 'disciple_tools' ) ) ?>
                        <button class="float-right" data-open="export_help_text">
                            <img class="help-icon"  style="padding-left: 5px;" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/help.svg' ) ?>" alt="help"/>
                        </button>
                        <button class="section-chevron chevron_down">
                            <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/chevron_down.svg' ) ?>"/>
                        </button>
                        <button class="section-chevron chevron_up">
                            <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/chevron_up.svg' ) ?>"/>
                        </button>
                    </div>
                    <div class="section-body" style="padding-top:1em;">
                        <a id="export_csv_list"><?php esc_html_e( 'CSV List', 'disciple_tools' ) ?></a><br>
                        <?php
                        if ( !empty( DT_Posts::get_field_settings_by_type( $post_type, 'communication_channel' ) ) ) {
                            ?>
                            <a id="export_bcc_email_list"><?php esc_html_e( 'BCC Email List', 'disciple_tools' ) ?></a><br>
                            <a id="export_phone_list"><?php esc_html_e( 'Phone List', 'disciple_tools' ) ?></a><br>
                            <?php
                        }
                        if ( class_exists( 'DT_Mapbox_API' ) && DT_Mapbox_API::get_key() ) {
                            ?>
                            <a class="export_map_list"><?php esc_html_e( 'Map List', 'disciple_tools' ) ?></a><br>
                            <?php
                        }
                        ?>
                        <?php do_action( 'dt_list_exports_menu_items', $post_type ); ?>

                    </div>
                </div>
                <div id="export_help_text" class="large reveal" data-reveal data-v-offset="10px">
                    <span class="section-header"><?php esc_html_e( 'List Exports Help', 'disciple_tools' ) ?></span>
                    <hr>
                    <div class="grid-x">
                        <div class="cell">
                            <p><strong><?php esc_html_e( 'CSV List', 'disciple_tools' ); ?></strong></p>
                            <p><?php esc_html_e( 'Using the current filter, this is a simple way to export basic information and use it in other applications.', 'disciple_tools' ); ?></p>
                        </div>
                        <div class="cell">
                            <p><strong><?php esc_html_e( 'BCC Email List', 'disciple_tools' ); ?></strong></p>
                            <p><?php esc_html_e( 'Using the current filter, the available emails are grouped by 50 and can be launched into your default email client by group. Many email providers put a limit of 50 on BCC emails. You can open all groups at once using the "Open All" button. If the list is too large, this might alert your email provider. The BCC email tool is meant to assist small group emails. Bulk email should be handled through bulk email providers.', 'disciple_tools' ); ?></p>
                        </div>
                        <div class="cell">
                            <p><strong><?php esc_html_e( 'Phone Number List', 'disciple_tools' ); ?></strong></p>
                            <p><?php esc_html_e( 'Using the current filter, this is intended for copy pasting a list of numbers into a messaging app, WhatsApp, Signal, etc. This is a quick way of starting a group conversation.', 'disciple_tools' ); ?></p>
                        </div>
                        <div class="cell">
                            <p><strong><?php esc_html_e( 'Map List', 'disciple_tools' ); ?></strong></p>
                            <p><?php esc_html_e( 'Using the current filter, this creates a basic points map of known locations of listed individuals.', 'disciple_tools' ); ?></p>
                        </div>
                        <?php
                        $list_exports_help = apply_filters( 'dt_post_list_exports_filters_sidebar_help_text', [] );
                        foreach ( $list_exports_help as $help ) {
                            ?>
                            <div class="cell">
                                <p><strong><?php echo esc_attr( $help['title'] ); ?></strong></p>
                                <p><?php echo esc_attr( $help['text'] ); ?></p>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                    <button class="close-button" data-close aria-label="Close modal" type="button">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <?php do_action( 'dt_post_list_filters_sidebar', $post_type ) ?>
            </aside>

            <main id="main" class="large-9 xxlarge-10 cell padding-bottom" role="main">
                <div class="bordered-box">
                    <div >
                        <span class="section-header posts-header" style="display: inline-block">
                            <?php echo esc_html( sprintf( _x( '%s List', 'Contacts List', 'disciple_tools' ), DT_Posts::get_post_settings( $post_type )['label_plural'] ) ) ?>
                        </span>
                        <span id="list-loading-spinner" style="display: inline-block" class="loading-spinner active"></span>
                        <span style="display: inline-block" class="filter-result-text"></span>
                        <div class="js-sort-dropdown" style="display: inline-block">
                            <ul class="dropdown menu" data-dropdown-menu>
                                <li>
                                    <a href="#"><?php esc_html_e( 'Sort', 'disciple_tools' ); ?></a>
                                    <ul class="menu is-dropdown-submenu" style="min-width:220px">
                                        <li>
                                            <a href="#" class="js-sort-by" data-column-index="6" data-order="desc" data-field="post_date">
                                            <i class="mdi mdi-sort-calendar-descending"></i>
                                            <?php esc_html_e( 'Newest', 'disciple_tools' ); ?></a>
                                        </li>
                                        <li>
                                            <a href="#" class="js-sort-by" data-column-index="6" data-order="asc" data-field="post_date">
                                            <i class="mdi mdi-sort-calendar-ascending"></i>
                                            <?php esc_html_e( 'Oldest', 'disciple_tools' ); ?></a>
                                        </li>
                                        <li>
                                            <a href="#" class="js-sort-by" data-column-index="6" data-order="desc" data-field="last_modified">
                                            <i class="mdi mdi-sort-clock-descending-outline"></i>
                                            <?php esc_html_e( 'Most recently modified', 'disciple_tools' ); ?></a>
                                        </li>
                                        <li>
                                            <a href="#" class="js-sort-by" data-column-index="6" data-order="asc" data-field="last_modified">
                                            <i class="mdi mdi-sort-clock-ascending-outline"></i>
                                            <?php esc_html_e( 'Least recently modified', 'disciple_tools' ); ?></a>
                                        </li>
                                    </ul>
                                </li>
                            </ul>
                        </div>
                        <?php
                        $dropdown_items = [
                            'options' =>
                                [
                                    'label' => __( 'Fields', 'disciple_tools' ),
                                    'icon' => get_template_directory_uri() . '/dt-assets/images/options.svg',
                                    'section_id' => 'list_column_picker',
                                    'show_list_checkboxes' => false,
                                ],
                            'bulk-edit' =>
                                [
                                    'label' => __( 'Bulk Edit', 'disciple_tools' ),
                                    'icon' => get_template_directory_uri() . '/dt-assets/images/bulk-edit.svg',
                                    'section_id' => 'bulk_edit_picker',
                                    'show_list_checkboxes' => true,
                                ],
                        ];

                        // Add custom menu items
                        $dropdown_items = apply_filters( 'dt_list_action_menu_items', $dropdown_items, $post_type );
                        ?>
                        <div class="js-sort-dropdown" style="display: inline-block">
                            <ul class="dropdown menu" data-dropdown-menu>
                                <li>
                                    <a href="#"><?php esc_html_e( 'More', 'disciple_tools' ) ?></a>
                                    <ul class="menu is-dropdown-submenu" id="dropdown-submenu-items-more">
                                    <?php foreach ( $dropdown_items as $key => $value ) : ?>
                                        <?php if ( isset( $key ) ) : ?>
                                            <?php $show_list_checkboxes = !empty( $value['show_list_checkboxes'] ) ? 'true' : 'false'; ?>
                                            <li>
                                                <a href="javascript:void(0);" data-modal="<?php echo esc_html( $value['section_id'] ); ?>" data-checkboxes="<?php echo esc_html( $show_list_checkboxes ); ?>" class="list-dropdown-submenu-item-link" id="submenu-more-<?php echo esc_html( $key ); ?>">
                                                    <img src="<?php echo esc_html( $value['icon'] ); ?>" class="list-dropdown-submenu-icon">
                                                    <?php echo esc_html( $value['label'] ); ?>
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                    </ul>
                                </li>
                            </ul>
                        </div>

                        <?php
                        $status_key = isset( $post_settings['status_field'] ) ? $post_settings['status_field']['status_key'] : null;
                        $archived_key = isset( $post_settings['status_field'] ) ? $post_settings['status_field']['archived_key'] : null;
                        $archived_text = __( 'Archived', 'disciple_tools' );
                        if ( $status_key && $archived_key && isset( $post_settings['fields'][$status_key]['default'][$archived_key]['label'] ) ){
                            $archived_text = $post_settings['fields'][$status_key]['default'][$archived_key]['label'];
                        }
                        $archived_label = sprintf( _x( 'Show %s', 'Show archived', 'disciple_tools' ), $archived_text );
                        ?>

                        <span style="display:<?php echo esc_html( !$status_key || !$archived_key ? 'none' : 'inline-block' ) ?>" class="show-closed-switch">
                            <?php echo esc_html( $archived_label ) ?>
                            <div class="switch tiny">
                                <input class="switch-input" id="archivedToggle" type="checkbox" name="archivedToggle">
                                <label class="switch-paddle" for="archivedToggle">
                                    <span class="show-for-sr"><?php echo esc_html( $archived_label ) ?></span>
                                </label>
                            </div>
                        </span>

                    </div>
                    <?php
                    // Add section UI for custom menus
                    do_action( 'dt_list_action_section', $post_type, $post_settings );
                    ?>

                    <div id="list_column_picker" class="list_field_picker list_action_section">
                        <button class="close-button list-action-close-button" data-close="list_column_picker" aria-label="<?php esc_html_e( 'Close', 'disciple_tools' ); ?>" type="button">
                            <span aria-hidden="true">×</span>
                        </button>
                        <p style="font-weight:bold"><?php esc_html_e( 'Choose which fields to display as columns in the list', 'disciple_tools' ); ?></p>
                        <?php
                        $fields_to_show_in_table = [];
                        if ( isset( $_COOKIE['fields_to_show_in_table'] ) ) {
                            $fields_to_show_in_table = json_decode( stripslashes( sanitize_text_field( wp_unslash( $_COOKIE['fields_to_show_in_table'] ) ) ) );
                            if ( $fields_to_show_in_table ){
                                $fields_to_show_in_table = dt_recursive_sanitize_array( $fields_to_show_in_table );
                            }
                        }

                        //order fields alphabetically by Name
                        uasort( $post_settings['fields'], function ( $a, $b ){
                            return $a['name'] <=> $b['name'];
                        });

                        ?>
                        <ul class="ul-no-bullets" style="">
                        <?php foreach ( $post_settings['fields'] as $field_key => $field_values ):
                            if ( !empty( $field_values['hidden'] ) ){
                                continue;
                            }
                            ?>
                            <li style="" class="">
                                <label style="margin-right:15px; cursor:pointer">
                                    <input type="checkbox" value="<?php echo esc_html( $field_key ); ?>"
                                           <?php echo esc_html( in_array( $field_key, $fields_to_show_in_table ) ? 'checked' : '' ); ?>
                                           <?php echo esc_html( ( empty( $fields_to_show_in_table ) && !empty( $field_values['show_in_table'] ) ) ? 'checked' : '' ); ?>
                                           style="margin:0">
                                    <?php dt_render_field_icon( $field_values );
                                    echo esc_html( $field_values['name'] ); ?>
                                </label>
                            </li>
                        <?php endforeach; ?>
                        </ul>
                        <button class="button" id="save_column_choices" style="display: inline-block"><?php esc_html_e( 'Apply', 'disciple_tools' ); ?></button>
                        <a class="button clear" id="reset_column_choices" style="display: inline-block"><?php esc_html_e( 'reset to default', 'disciple_tools' ); ?></a>
                    </div>

                    <div id="bulk_edit_picker" class="list_action_section">
                        <button class="close-button list-action-close-button" data-close="bulk_edit_picker" aria-label="<?php esc_html_e( 'Close', 'disciple_tools' ); ?>" type="button">
                            <span aria-hidden="true">×</span>
                        </button>
                        <p style="font-weight:bold"><?php
                        echo sprintf( esc_html__( 'Select all the  %1$s you want to update from the list, and update them below', 'disciple_tools' ), esc_html( $post_type ) );?></p>
                        <div class="grid-x grid-margin-x">
                            <?php if ( isset( $field_options['assigned_to'] ) ) : ?>
                            <div class="cell small-12 medium-4">
                            <div class="section-subheader">
                                <img src="<?php echo esc_url( get_template_directory_uri() ) . '/dt-assets/images/assigned-to.svg' ?>">
                                <?php echo esc_html( $field_options['assigned_to']['name'] ); ?>
                                <button class="help-button" data-section="assigned-to-help-text">
                                    <img class="help-icon" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/help.svg' ) ?>"/>
                                </button>
                            </div>
                            <div class="bulk_assigned_to details">
                                <var id="bulk_assigned_to-result-container" class="result-container bulk_assigned_to-result-container"></var>
                                <div id="bulk_assigned_to_t" name="form-bulk_assigned_to" class="scrollable-typeahead">
                                    <div class="typeahead__container" style="margin-bottom: 0">
                                        <div class="typeahead__field">
                                            <span class="typeahead__query">
                                                <input class="js-typeahead-bulk_assigned_to input-height" dir="auto"
                                                    name="bulk_assigned_to[query]" placeholder="<?php echo esc_html_x( 'Search Users', 'input field placeholder', 'disciple_tools' ) ?>"
                                                    autocomplete="off">
                                            </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                                <?php
                                if ( $post_type == 'contacts' ) {?>
                                    <?php if ( isset( $field_options['subassigned'] ) ) : ?>
                                    <div class="cell small-12 medium-4">
                                        <?php $field_options['subassigned']['custom_display'] = false ?>
                                        <?php render_field_for_display( 'subassigned', $field_options, null, false, false, 'bulk_' ); ?>
                                    </div>
                                    <?php endif; ?>
                                    <?php if ( isset( $field_options['overall_status'] ) ) : ?>
                                    <div class="cell small-12 medium-4">
                                        <div class="section-subheader">
                                            <img src="<?php echo esc_url( get_template_directory_uri() ) . '/dt-assets/images/status.svg' ?>">
                                            <?php if ( isset( $tiles['status']['label'] ) && !empty( $tiles['status']['label'] ) ) {
                                                echo esc_html( $tiles['status']['label'] );
                                            } else {
                                                echo esc_html__( 'Status', 'disciple_tools' );
                                            }?>
                                            <button class="help-button-field" data-section="overall_status-help-text">
                                                <img class="help-icon" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/help.svg' ) ?>"/>
                                            </button>
                                        </div>
                                        <select id="overall_status" class="select-field">
                                            <option></option>
                                            <?php foreach ( $field_options['overall_status']['default'] as $key => $option ){
                                                $value = $option['label'] ?? '';?>
                                                    <option value="<?php echo esc_html( $key ) ?>"><?php echo esc_html( $value ); ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                    <?php endif; ?>
                                    <?php if ( isset( $field_options['reason_paused'] ) ) : ?>
                                    <div class="cell small-12 medium-4" style="display:none">

                                        <div class="section-subheader">
                                                <img src="<?php echo esc_url( get_template_directory_uri() ) . '/dt-assets/images/status.svg' ?>">
                                                <?php echo esc_html( $field_options['reason_paused']['name'] ?? '' ) ?>
<!--                                                </button>-->
                                            </div>

                                        <select id="reason-paused-options">
                                            <option></option>
                                            <?php
                                            foreach ( $field_options['reason_paused']['default'] as $reason_key => $option ) {
                                                if ( $option['label'] ) {
                                                    ?>
                                                    <option value="<?php echo esc_attr( $reason_key ) ?>">
                                                        <?php echo esc_html( $option['label'] ?? '' ) ?>
                                                    </option>
                                                    <?php
                                                }
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <?php endif; ?>

                                <?php } elseif ( $post_type == 'groups' ) {?>
                                    <div class="cell small-12 medium-4">
                                    <?php $field_options['coaches']['custom_display'] = false ?>
                                    <?php
                                    render_field_for_display( 'coaches', $field_options, null, false, false, 'bulk_' ); ?>
                                    </div>
                                <?php } ?>
                            <div class="cell small-12 medium-4">
                                <div class="section-subheader">
                                  <?php esc_html_e( 'Share with:', 'disciple_tools' );?>
                                </div>
                                <div id="<?php echo esc_attr( 'bulk_share_connection' ) ?>" class="dt_typeahead">
                                    <span id="<?php echo esc_html( 'share' ); ?>-result-container" class="result-container"></span>
                                    <div id="<?php echo esc_html( 'share' ); ?>_t" name="form-<?php echo esc_html( 'share' ); ?>" class="scrollable-typeahead typeahead-margin-when-active">
                                        <div class="typeahead__container" style="margin-bottom: 0">
                                            <div class="typeahead__field">
                                                <span class="typeahead__query">
                                                    <input id = "bulk_share" class="input-height" data-field="<?php echo esc_html( 'share' ); ?>"
                                                        data-post_type="<?php echo esc_html( $post_type ) ?>"
                                                        data-field_type="connection"
                                                        name="share[query]"
                                                        placeholder="<?php echo esc_html( sprintf( _x( 'Search %s', "Search 'something'", 'disciple_tools' ), 'Users' ) )?>"
                                                        autocomplete="off">
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <label style="display: inline-block">
                                        <input type="checkbox" id="bulk_share_unshare">
                                        <?php esc_html_e( 'Unshare with selected user', 'disciple_tools' ); ?>
                                    </label>
                                </div>
                            </div>
                            <?php if ( isset( $field_options['requires_update'] ) ) : ?>
                            <div class="cell small-12 medium-4 center-items">
                            <span style="margin-right:5px"><?php echo esc_html( $field_options['requires_update']['name'] ); ?>:</span>
                                    <input type="checkbox" id="update-needed-bulk" class="dt-switch update-needed" data-bulk_key_requires_update=""/>
                                    <label class="dt-switch" for="update-needed-bulk" style="vertical-align: top;"></label>
                            </div>
                            <?php endif; ?>
                            <div class="cell small-12 medium-4 center-items">
                            <button class="button follow" data-value=""><?php echo esc_html( __( 'Follow', 'disciple_tools' ) ) ?></button>
                            </div>

                            <div class="cell small-12 medium-12 grid-y">
                                <div class="section-subheader">
                                    <?php esc_html_e( 'Comments and Activity', 'disciple_tools' ) ?>
                                </div>
                                <div class="cell" id="bulk_add-comment-section">
                                    <div class="auto cell">
                                        <textarea class="mention" dir="auto" id="bulk_comment-input"
                                                placeholder="<?php echo esc_html_x( 'Write your comment or note here', 'input field placeholder', 'disciple_tools' ) ?>"
                                        ></textarea>

                                        <?php if ( $post_type == 'contacts' ) :
                                            $sections = apply_filters( 'dt_comments_additional_sections', [], $post_type );?>

                                                <div class="grid-x">
                                                    <div class="section-subheader cell shrink">
                                                        <?php esc_html_e( 'Type:', 'disciple_tools' ) ?>
                                                    </div>
                                                    <select id="comment_type_selector" class="cell auto">
                                                        <?php
                                                        $section_keys = [ 'activity' ];
                                                        foreach ( $sections as $section ) {
                                                            if ( !in_array( $section['key'], $section_keys ) ) {
                                                                $section_keys[] = $section['key'] ?>
                                                                <option value="<?php echo esc_html( $section['key'] ); ?>">
                                                                <?php echo esc_html( $section['label'] );
                                                            }
                                                        } ?>
                                                    </select>
                                                </div>
                                                <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <span class="cell small-12 medium-12 center">
                                <a class="button" id="bulk_edit_seeMore">
                                    <span class="seeMoreText"><?php esc_html_e( 'See More Options', 'disciple_tools' ); ?></span>
                                    <span class="seeFewerText" style="display:none"><?php esc_html_e( 'See Fewer Options', 'disciple_tools' ); ?></span>
                                </a>
                            </span>
                            <div id="bulk_more" class="grid-x grid-margin-x" style="display:none;">

                                <?php
                                //custom display for location field.
                                if ( isset( $field_options['location_grid'] ) ){
                                    $modified_options = $field_options;
                                    $modified_options['location_grid']['hidden'] = false; ?>
                                    <div class="cell small-12 medium-4">
                                        <?php render_field_for_display( 'location_grid', $modified_options, null, false, false, 'bulk_' ); ?>
                                    </div>
                                    <?php
                                }
                                //move multi_select fields to the end
                                function multiselect_at_end( $a, $b ){
                                    return ( $a['type'] ?? '' === 'multi_select' && ( $a['display'] ?? '' ) !== 'typeahead' ) ? 1 : 0;
                                };
                                uasort( $field_options, 'multiselect_at_end' );
                                $already_done = [ 'subassigned', 'location_grid', 'assigned_to', 'overall_status' ];
                                $allowed_types = [ 'user_select', 'multi_select', 'key_select', 'date', 'datetime', 'location', 'location_meta', 'connection', 'tags', 'text', 'textarea', 'number' ];
                                foreach ( $field_options as $field_option => $value ) :
                                    if ( !in_array( $field_option, $already_done ) && array_key_exists( 'type', $value ) && in_array( $value['type'], $allowed_types )
                                        && $value['type'] != 'communication_channel' && empty( $value['hidden'] ) ) : ?>
                                        <div class="cell small-12 medium-<?php echo esc_attr( ( $value['type'] === 'multi_select' && ( $value['display'] ?? '' ) !== 'typeahead' ) ? '12' : '4' ) ?>">
                                            <?php $field_options[$field_option]['custom_display'] = false;
                                            render_field_for_display( $field_option, $field_options, null, false, false, 'bulk_' ); ?>
                                        </div>
                                    <?php endif;
                                endforeach;
                                ?>
                            </div>
                        </div>

                        <button class="button dt-green" id="bulk_edit_submit">
                            <span class="bulk_edit_submit_text" data-pretext="<?php echo esc_html__( 'Update', 'disciple_tools' ); ?>" data-posttext="<?php echo esc_html( $post_settings['label_plural'] ); ?>" style="text-transform:capitalize;">
                                <?php echo esc_html( __( 'Make Selections Below', 'disciple_tools' ) ); ?>
                            </span>
                            <span id="bulk_edit_submit-spinner" style="display: inline-block;" class="loading-spinner"></span>
                        </button>
                        <span class="list-action-event-buttons">
                            <?php if ( current_user_can( 'delete_any_' . $post_type ) ){ ?>
                                <button class="button" id="bulk_edit_delete_submit">
                                    <span class="bulk_edit_delete_submit_text"
                                          data-pretext="<?php echo esc_html__( 'Delete', 'disciple_tools' ); ?>"
                                          data-posttext="<?php echo esc_html( $post_settings['label_plural'] ); ?>"
                                          style="text-transform:capitalize;">
                                        <?php echo esc_html( __( 'Delete Selections Below', 'disciple_tools' ) ); ?>
                                    </span>
                                    <span id="bulk_edit_delete_submit-spinner" style="display: inline-block;"
                                          class="loading-spinner"></span>
                                </button>
                            <?php } ?>
                        </span>
                    </div>

                    <div style="display: flex; flex-wrap:wrap; margin: 10px 0" id="current-filters"></div>

                    <div class="table-container">
                        <table class="table-remove-top-border js-list stack striped" id="records-table">
                            <thead>
                                <tr class="table-headers dnd-moved sortable">
                                    <th id="bulk_edit_master" class="bulk_edit_checkbox" style="width:32px; background-image:none; cursor:default">
                                    <input type="checkbox" name="bulk_send_app_id" value="" id="bulk_edit_master_checkbox">
                                    </th>
                                    <th data-id="index" style="width:32px; background-image:none; cursor:default"></th>

                                    <?php $columns = [];
                                    if ( empty( $fields_to_show_in_table ) ){
                                        $columns = DT_Posts::get_default_list_column_order( $post_type );
                                    }
                                    $columns = array_unique( array_merge( $fields_to_show_in_table, $columns ) );
                                    if ( in_array( 'favorite', $columns ) ) {
                                        ?>
                                        <th style="width:36px; background-image:none; cursor:default"></th>
                                        <?php
                                    }
                                    foreach ( $columns as $field_key ):
                                        if ( ! in_array( $field_key, [ 'favorite' ] ) ):
                                            if ( isset( $post_settings['fields'][$field_key]['name'] ) ) : ?>
                                                <th class="all" data-id="<?php echo esc_html( $field_key ) ?>">
                                                    <?php echo esc_html( $post_settings['fields'][ $field_key ]['name'] ) ?>
                                                </th>
                                            <?php endif;
                                        endif;
                                    endforeach ?>
                                </tr>
                            </thead>
                            <tbody id="table-content">
                                <tr class="js-list-loading"><td colspan=7><?php esc_html_e( 'Loading...', 'disciple_tools' ); ?></td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="center">
                        <button id="load-more" class="button loader" style="display: none"><?php esc_html_e( 'Load More', 'disciple_tools' ) ?></button>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <div class="reveal" id="filter-modal" data-reveal>
        <div class="grid-container">
            <div class="grid-x">
                <div class="cell small-4" style="padding: 0 2px 5px 2px">
                    <input type="search" id="field-filter-name"
                           placeholder="<?php esc_html_e( 'Field Name', 'disciple_tools' )?>"
                           style="margin-bottom: 0"/>
                </div>
                <div class="cell small-7">
                    <div id="selected-filters"></div>
                </div>
            </div>

            <div class="grid-x">
                <div class="cell small-4 filter-modal-left">
                    <?php $fields = [];
                    $allowed_types = [ 'user_select', 'multi_select', 'key_select', 'boolean', 'date', 'datetime', 'location', 'location_meta', 'connection', 'tags', 'text', 'communication_channel' ];
                    //order fields alphabetically by Name
                    uasort( $field_options, function ( $a, $b ){
                        return strnatcmp( $a['name'] ?? 'z', $b['name'] ?? 'z' );
                    });
                    foreach ( $field_options as $field_key => $field ){
                        if ( $field_key && in_array( $field['type'] ?? '', $allowed_types ) && !in_array( $field_key, $fields ) && !( isset( $field['hidden'] ) && $field['hidden'] ) ){
                            $fields[] = $field_key;
                        }
                    }
                    ?>
                    <ul class="vertical tabs" data-tabs id="filter-tabs">
                        <?php foreach ( $fields as $index => $field ) :
                            if ( isset( $field_options[$field]['name'] ) ) : ?>
                                <li class="tabs-title <?php if ( $index === 0 ){ echo 'is-active'; } ?>" data-field="<?php echo esc_html( $field )?>">
                                    <a href="#<?php echo esc_html( $field )?>" <?php if ( $index === 0 ){ echo 'aria-selected="true"'; } ?>>
                                        <?php
                                        $rendered = dt_render_field_icon( $field_options[$field], 'tabs-title__icon' );
                                        if ( !$rendered ){
                                            ?><div class="tabs-title__icon"></div><?php
                                        }
                                        echo esc_html( $field_options[$field]['name'] ) ?>
                                    </a>
                                </li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div class="cell small-8 tabs-content filter-modal-right" data-tabs-content="filter-tabs">
                    <?php foreach ( $fields as $index => $field ) :
                        $is_multi_select = isset( $field_options[$field] ) && ( in_array( $field_options[$field]['type'], [ 'multi_select', 'tags' ] ) );
                        if ( isset( $field_options[$field] ) && ( $field_options[$field]['type'] === 'connection' || $field_options[$field]['type'] === 'location' || $field_options[$field]['type'] === 'location_meta' || $field_options[$field]['type'] === 'user_select' || $is_multi_select ) ) : ?>
                            <div class="tabs-panel <?php if ( $index === 0 ){ echo 'is-active'; } ?>" id="<?php echo esc_html( $field ) ?>">
                                <div class="section-header"><?php echo esc_html( $field_options[$field]['name'] ) ?></div>
                                <div class="<?php echo esc_html( $field );?>  <?php echo esc_html( $is_multi_select ? 'multi_select' : '' ) ?> details" >
                                    <var id="<?php echo esc_html( $field ) ?>-result-container" class="result-container <?php echo esc_html( $field ) ?>-result-container"></var>
                                    <div id="<?php echo esc_html( $field ) ?>_t" name="form-<?php echo esc_html( $field ) ?>" class="scrollable-typeahead typeahead-margin-when-active">
                                        <div class="typeahead__container">
                                            <div class="typeahead__field">
                                                <span class="typeahead__query">
                                                    <input class="js-typeahead-<?php echo esc_html( $field ) ?> input-height"
                                                           data-field="<?php echo esc_html( $field )?>"
                                                           data-type="<?php echo esc_html( $field_options[$field]['type'] ) ?>"
                                                           name="<?php echo esc_html( $field ) ?>[query]"
                                                           placeholder="<?php echo esc_html_x( 'Type to search', 'input field placeholder', 'disciple_tools' ) ?>"
                                                           autocomplete="off">
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php if ( $field_options[$field]['type'] === 'connection' ) : ?>
                                    <p>
                                        <label><?php echo esc_html( sprintf( _x( 'All %1$s with %2$s', 'All Contacts with Is Coaching', 'disciple_tools' ), $post_settings['label_plural'], $field_options[$field]['name'] ) ) ?>
                                            <input class="all-connections" type="checkbox" value="all-connections" />
                                        </label>
                                        <label><?php echo esc_html( sprintf( _x( 'All %1$s without %2$s', 'All Contacts without Is Coaching', 'disciple_tools' ), $post_settings['label_plural'], $field_options[$field]['name'] ) ) ?>
                                            <input class="all-without-connections" type="checkbox" value="all-without-connections" />
                                        </label>
                                    </p>
                                <?php endif; ?>
                                <?php if ( $field === 'subassigned' ): ?>
                                    <p>
                                        <label><?php esc_html_e( 'Filter for subassigned OR Assigned To', 'disciple_tools' ) ?>
                                            <input id="combine_subassigned" type="checkbox" value="combine_subassigned" />
                                        </label>
                                    </p>
                                <?php endif;?>
                            </div>

                        <?php else : ?>
                            <div class="tabs-panel <?php if ( $index === 0 ){ echo 'is-active'; } ?>"" id="<?php echo esc_html( $field ) ?>">
                                <div class="section-header"><?php echo esc_html( $field === 'post_date' ? __( 'Creation Date', 'disciple_tools' ) : $field_options[$field]['name'] ?? $field ) ?></div>
                                <div id="<?php echo esc_html( $field ) ?>-options">
                                    <?php if ( isset( $field_options[$field] ) && $field_options[$field]['type'] == 'key_select' ) :
                                        if ( !isset( $field_options[$field]['default']['none'] ) ) :?>
                                            <div class="key_select_options">
                                                <label style="cursor: pointer">
                                                    <input autocomplete="off" type="checkbox" data-field="<?php echo esc_html( $field ) ?>"
                                                           value="none"> <?php echo esc_html__( 'None Set', 'disciple_tools' ); ?>
                                                </label>
                                            </div>
                                        <?php endif;
                                        foreach ( $field_options[$field]['default'] as $option_key => $option_value ) :
                                            $label = $option_value['label'] ?? '';
                                            if ( empty( $label ) && ( $option_key === '' || $option_key === 'none' ) ){
                                                $label = esc_html__( 'None Set', 'disciple_tools' );
                                            }
                                            ?>
                                            <div class="key_select_options">
                                                <label style="cursor: pointer">
                                                    <input autocomplete="off" type="checkbox" data-field="<?php echo esc_html( $field ) ?>"
                                                           value="<?php echo esc_html( $option_key ) ?>"> <?php echo esc_html( $label ) ?>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php elseif ( isset( $field_options[$field] ) && $field_options[$field]['type'] == 'boolean' ) : ?>
                                        <div class="boolean_options">
                                            <label style="cursor: pointer">
                                                <input autocomplete="off" type="checkbox" data-field="<?php echo esc_html( $field ) ?>"
                                                       data-label="<?php esc_html_e( 'No', 'disciple_tools' ) ?>"
                                                       value="0"> <?php esc_html_e( 'No', 'disciple_tools' ) ?>
                                            </label>
                                        </div>
                                        <div class="boolean_options">
                                            <label style="cursor: pointer">
                                                <input autocomplete="off" type="checkbox" data-field="<?php echo esc_html( $field ) ?>"
                                                       data-label="<?php esc_html_e( 'Yes', 'disciple_tools' ) ?>"
                                                       value="1"> <?php esc_html_e( 'Yes', 'disciple_tools' ) ?>
                                            </label>
                                        </div>
                                    <?php elseif ( isset( $field_options[$field] ) && in_array( $field_options[$field]['type'], [ 'date', 'datetime' ] ) ) : ?>
                                        <strong><?php echo esc_html_x( 'Range Start', 'The start date of a date range', 'disciple_tools' ) ?></strong>
                                        <button class="clear-date-picker" style="color:firebrick"
                                                data-for="<?php echo esc_html( $field ) ?>_start">
                                            <?php echo esc_html_x( 'Clear', 'Clear/empty input', 'disciple_tools' ) ?></button>
                                        <input id="<?php echo esc_html( $field ) ?>_start"
                                               autocomplete="off"
                                               type="text" data-date-format='yy-mm-dd'
                                               class="dt_date_picker" data-delimit="start"
                                               data-field="<?php echo esc_html( $field ) ?>">
                                        <br>
                                        <strong><?php echo esc_html_x( 'Range End', 'The end date of a date range', 'disciple_tools' ) ?></strong>
                                        <button class="clear-date-picker"
                                                style="color:firebrick"
                                                data-for="<?php echo esc_html( $field ) ?>_end">
                                            <?php echo esc_html_x( 'Clear', 'Clear/empty input', 'disciple_tools' ) ?></button>
                                        <input id="<?php echo esc_html( $field ) ?>_end"
                                               autocomplete="off" type="text"
                                               data-date-format='yy-mm-dd'
                                               class="dt_date_picker" data-delimit="end"
                                               data-field="<?php echo esc_html( $field ) ?>">
                                    <?php elseif ( isset( $field_options[$field] ) && in_array( $field_options[$field]['type'], [ 'text', 'communication_channel' ] ) ) : ?>
                                        <input id="<?php echo esc_html( $field ) ?>_text_comms_filter"
                                               type="text"
                                               class="text-comms-filter-input"
                                               data-field="<?php echo esc_html( $field ) ?>"
                                               placeholder="<?php echo esc_html_x( 'Filter By Value', 'The value to be searched for', 'disciple_tools' ) ?>" />

                                        <p>
                                            <label>
                                                <input name="filter_by_text_comms_option" class="filter-by-text-comms-option" type="radio" value="all-with-filtered-value" data-field="<?php echo esc_html( $field ) ?>" />
                                                <?php echo esc_html( sprintf( _x( 'All %1$s with filtered value in %2$s', 'All Contacts with filtered value in Emails', 'disciple_tools' ), $post_settings['label_plural'], $field_options[$field]['name'] ) ) ?>
                                            </label>
                                            <label>
                                                <input name="filter_by_text_comms_option" class="filter-by-text-comms-option" type="radio" value="all-without-filtered-value" data-field="<?php echo esc_html( $field ) ?>" />
                                                <?php echo esc_html( sprintf( _x( 'All %1$s without filtered value in %2$s', 'All Contacts without filtered value in Emails', 'disciple_tools' ), $post_settings['label_plural'], $field_options[$field]['name'] ) ) ?>
                                            </label>
                                            <label>
                                                <input name="filter_by_text_comms_option" class="filter-by-text-comms-option" type="radio" value="all-with-set-value" data-field="<?php echo esc_html( $field ) ?>" />
                                                <?php echo esc_html( sprintf( _x( 'All %1$s with any value in %2$s', 'All Contacts with any value in field', 'disciple_tools' ), $post_settings['label_plural'], $field_options[$field]['name'] ) ) ?>
                                            </label>
                                            <label>
                                                <input name="filter_by_text_comms_option" class="filter-by-text-comms-option" type="radio" value="all-without-set-value" data-field="<?php echo esc_html( $field ) ?>" />
                                                <?php echo esc_html( sprintf( _x( 'All %1$s with no value in %2$s', 'All Contacts with no value in field', 'disciple_tools' ), $post_settings['label_plural'], $field_options[$field]['name'] ) ) ?>
                                            </label>
                                        </p>
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
                <button class="button button-cancel" data-close aria-label="Close reveal" type="button">
                    <?php esc_html_e( 'Cancel', 'disciple_tools' )?>
                </button>
            </div>
            <div class="cell small-8 filter-modal-right confirm-buttons">

                <div class="grid-x grid-padding-x">
                    <div class="cell small-7 filter-modal-right">
                        <input type="search" id="new-filter-name"
                               placeholder="<?php esc_html_e( 'Filter Name', 'disciple_tools' ) ?>" />
                    </div>
                    <div class="cell small-5 filter-modal-right">
                        <button style="display: inline-block;" class="button loader confirm-filter-records"
                                type="button" id="confirm-filter-records" data-close>
                            <?php esc_html_e( 'Filter Records', 'disciple_tools' ) ?>
                        </button>
                        <button class="button loader confirm-filter-records" type="button" id="save-filter-edits"
                                data-close style="display: none;">
                            <?php esc_html_e( 'Save', 'disciple_tools' ) ?>
                        </button>
                    </div>
                </div>

            </div>
        </div>
        <button class="close-button" data-close aria-label="<?php esc_html_e( 'Close', 'disciple_tools' ); ?>" type="button">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>

    <?php get_template_part( 'dt-assets/parts/modals/modal', 'filters' ); ?>

    <?php
    get_footer();
} )();
