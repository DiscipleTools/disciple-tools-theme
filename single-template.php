<?php
declare(strict_types=1);

dt_please_log_in();

$dt_post_type = get_post_type();
if ( !current_user_can( 'access_' . $dt_post_type ) ) {
    wp_safe_redirect( '/settings' );
    exit();
}
//template for tile info on edit page (?)
function get_help_modal_html() {
    ob_start();
    ?>
    <p id="help-modal-field-description " class="make-links-clickable dt-test" style="white-space: pre-line"></p>
    <div class="help-modal-field-body field-body--custom"></div>
    <div id="tile-help-section-apps" class="help-section help-modal-icon" style="">
        <ul>
            <li>
                <img alt="show"
                    src="<?php echo esc_url( get_template_directory_uri() . '/dt-assets/images/visibility.svg' ); ?>" />
                <strong><?php esc_html_e( 'Open the link', 'disciple_tools' ); ?></strong>
            </li>
            <li>
                <img alt="copy"
                    src="<?php echo esc_url( get_template_directory_uri() . '/dt-assets/images/duplicate.svg' ); ?>" />
                <strong><?php esc_html_e( 'Copy the link to the clipboard', 'disciple_tools' ); ?></strong>
            </li>
            <li>
                <img alt="send"
                    src="<?php echo esc_url( get_template_directory_uri() . '/dt-assets/images/send.svg' ); ?>" />
                <strong><?php esc_html_e( 'Send the link via email.', 'disciple_tools' ); ?></strong>
            </li>
            <li>
                <img alt="qrcode"
                    src="<?php echo esc_url( get_template_directory_uri() . '/dt-assets/images/qrcode-solid.svg' ); ?>" />
                <strong><?php esc_html_e( 'Scan the QR code to open the magic link on a mobile device.', 'disciple_tools' ); ?></strong>
            </li>
            <li>
                <img alt="undo"
                    src="<?php echo esc_url( get_template_directory_uri() . '/dt-assets/images/undo.svg' ); ?>" />
                <strong><?php esc_html_e( 'Reset the security code. No data is removed. Only access. The previous link will be disabled and another one created.', 'disciple_tools' ); ?></strong>
            </li>
        </ul>
    </div>
    <?php
    return ob_get_clean();
}

function dt_display_tile( $tile, $post ): bool
{

    // If nothing, display by default!
    if ( empty( $tile['display_conditions'] ) || ( isset( $tile['display_conditions']['visibility'] ) && $tile['display_conditions']['visibility'] == 'visible' ) ) {
        return true;
    }

    if ( !is_array( $tile['display_conditions'] ) ) {
        return true;
    }

    if ( $tile['display_conditions']['visibility'] == 'hidden' ) {
        return false;
    }

    // Determine if all specified fields must be present & iterate.
    $field_presence = [];
    $all_fields_required = isset( $tile['display_conditions']['operator'] ) && $tile['display_conditions']['operator'] == 'and';
    $field_settings = DT_Posts::get_post_field_settings( get_post_type() );
    foreach ( $tile['display_conditions']['conditions'] ?? [] as $condition ) {

        // Extract tile display condition options.
        $field_id = $condition['key'];
        $option_id = $condition['value'];

        // Determine if post contains field and corresponding option.
        if ( isset( $post[$field_id], $field_settings[$field_id] ) ) {
            switch ( $field_settings[$field_id]['type'] ) {
                case 'key_select':
                    $field_presence[] = $post[$field_id]['key'] == $option_id;
                    break;

                case 'tags':
                case 'multi_select':
                    $field_presence[] = in_array( $option_id, $post[$field_id] );
                    break;
            }
        }
    }

    // Determine if fields required conditions were met.
    if ( $all_fields_required && !in_array( false, $field_presence ) ) {
        return true;

    } elseif ( !$all_fields_required && in_array( true, $field_presence ) ) {
        return true;
    }

    return false;
}

( function () {
    $post_type = get_post_type();
    $post_id = get_the_ID();
    if ( !DT_Posts::can_view( $post_type, $post_id ) ) {
        get_template_part( '403' );
        die();
    }
    $current_user_id = get_current_user_id();
    $post_settings = DT_Posts::get_post_settings( $post_type );
    $dt_post = DT_Posts::get_post( $post_type, $post_id );
    $tiles = DT_Posts::get_post_tiles( $post_type );

    Disciple_Tools_Notifications::process_new_notifications( get_the_ID() ); // removes new notifications for this post
    add_action('dt_nav_add_after', function ( $desktop = true ) {
        dt_print_details_bar( $desktop );
    }, 10, 1);
    get_header();

    ?>
    <div id="content" class="single-template">
        <div id="inner-content" class="grid-x grid-margin-x grid-margin-y">

            <?php do_action( 'dt_record_top_full_with', $post_type, $dt_post ) ?>

            <main id="main" class="large-7 medium-12 small-12 cell" role="main" style="padding:0">

                <div class="cell grid-y grid-margin-y">


                    <!-- Requires update block -->
                    <section class="cell small-12 update-needed-notification"
                        style="display: <?php echo esc_html( ( isset( $dt_post['requires_update'] ) && $dt_post['requires_update'] === true ) ? 'block' : 'none' ) ?> ">
                        <a href="#comment-activity-section" class="hide-for-large">
                            <div class="bordered-box detail-notification-box" style="background-color:#F43636">
                                <h4>
                                    <img class="dt-white-icon"
                                        src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/alert-circle-exc.svg?v=2' ) ?>" />
                                    <?php echo esc_html( sprintf( __( 'This %s needs an update.', 'disciple_tools' ), strtolower( $post_settings['label_singular'] ) ) ) ?>
                                </h4>
                                <p><?php esc_html_e( 'Please provide an update by posting a comment.', 'disciple_tools' ) ?>
                                </p>
                            </div>
                        </a>
                        <div class="bordered-box detail-notification-box show-for-large" style="background-color:#F43636">
                            <h4>
                                <img class="dt-white-icon"
                                    src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/alert-circle-exc.svg?v=2' ) ?>" />
                                <?php echo esc_html( sprintf( __( 'This %s needs an update.', 'disciple_tools' ), strtolower( $post_settings['label_singular'] ) ) ) ?>
                            </h4>
                            <p><?php esc_html_e( 'Please provide an update by posting a comment.', 'disciple_tools' ) ?></p>
                        </div>
                    </section>

                    <?php do_action( 'dt_record_notifications_section', $post_type, $dt_post ); ?>


                    <?php do_action( 'dt_record_top_above_details', $post_type, $dt_post ); ?>

                    <!--
                        Status section
                    -->
                    <?php if ( isset( $tiles['status'] ) && empty( $tiles['status']['hidden'] ) ): ?>
                        <section id="contact-status" class="small-12 cell bordered-box">
                            <h3 class="section-header">
                                <?php if ( isset( $tiles['status']['label'] ) && !empty( $tiles['status']['label'] ) ) {
                                    echo esc_html( $tiles['status']['label'] );
                                } else {
                                    echo esc_html__( 'Status', 'disciple_tools' );
                                } ?>

                                <div class="reveal" id="help-modal-field" data-reveal></div>
                                <dt-modal data-tile="status" class="help-button-tile" title="status" context="alert" ishelp=""
                                    buttonClass={help-button-tile} buttonStyle={"background":"none","border":"0px"}
                                    imageSrc="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/help.svg' ) ?>"
                                    imageStyle={"height":"15px"} tileLabel="status">
                                    <span slot="content">
                                        <?php
                                        $html = get_help_modal_html();
                                        echo wp_kses_post( $html );
                                        ?>
                                    </span>
                                </dt-modal>

                            </h3>

                            <div class="grid-x grid-margin-x">
                                <?php
                                //setup the order of the tile fields
                                $order = $tiles['status']['order'] ?? [];
                                foreach ( $post_settings['fields'] as $key => $option ) {
                                    if ( isset( $option['tile'] ) && $option['tile'] === 'status' ) {
                                        if ( !in_array( $key, $order ) ) {
                                            $order[] = $key;
                                        }
                                    }
                                }
                                foreach ( $order as $field_key ) {
                                    if ( !isset( $post_settings['fields'][$field_key] ) ){
                                        continue;
                                    }
                                    $field = $post_settings['fields'][$field_key];
                                    $enabled_for_type = dt_field_enabled_for_record_type( $field, $dt_post );
                                    if ( ( isset( $post_settings['fields'][$field_key]['hidden'] ) && true === $post_settings['fields'][$field_key]['hidden'] )
                                        || !$enabled_for_type ){
                                        continue;
                                    }

                                    $field = $post_settings['fields'][$field_key];
                                    $enabled_for_type = dt_field_enabled_for_record_type( $field, $dt_post );
                                    if ( isset( $field['tile'] ) && $field['tile'] === 'status' && $enabled_for_type && empty( $field['hidden'] ) ) {
                                        ?>
                                        <div class="cell small-12 medium-4">
                                            <?php render_field_for_display( $field_key, $post_settings['fields'], $dt_post, true ); ?>
                                        </div>
                                    <?php }
                                }
                                ?>
                                <?php do_action( 'dt_details_additional_section', 'status', $post_type, $post_id ); ?>
                            </div>
                        </section>
                    <?php endif; ?>

                    <!--
                        Main details section
                    -->
                    <?php if ( isset( $tiles['details'] ) && empty( $tiles['details']['hidden'] ) ): ?>
                        <section id="details-tile" class="small-12 cell bordered-box collapsed">
                            <h3 class="section-header">
                                <?php if ( isset( $tiles['details']['label'] ) && !empty( $tiles['details']['label'] ) ) {
                                    echo esc_html( $tiles['details']['label'] );
                                } else {
                                    echo esc_html__( 'Details', 'disciple_tools' );
                                } ?>
                                <dt-modal class="help-button-tile" data-tile="details" title="details" context="alert" ishelp=""
                                    buttonClass={"alert":true} buttonStyle={"background":"none","border":"0px"}
                                    imageSrc="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/help.svg' ) ?>"
                                    imageStyle={"height":"15px"} tileLabel="details">
                                    <span slot="content">
                                        <?php
                                        $html = get_help_modal_html();
                                        echo wp_kses_post( $html );
                                        ?>
                                    </span>
                                </dt-modal>
                                <!-- <div class="details-title-section"></div> -->
                                <?php
                                $post_type = dt_get_post_type();
                                $post_settings = DT_Posts::get_post_settings( $post_type );
                                ?>
                                <!-- duplicate check button integration -->
                                <dt-modal class="duplicate-detected duplicates-detected-notice"
                                    closeButton
                                    headerClass={"header-blue-bg":true}
                                    buttonClass={"duplicates-detected-button":true} buttonLabel="Duplicate Detected"
                                    buttonStyle={"background-color":"transparent","border":"1px","color":"#3f729b","border-style":"solid","font-weight":"600","border-color":"#3f729b","display":""}
                                    imageStyle={"filter":"hue-rotate(0deg)"}
                                    imageSrc="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/broken.svg' ) ?>"
                                    title="Duplicate Contacts"
                                    id="see-duplicates">
                                    <span slot="content">

                                        <h4 class="merge-modal-subheading">
                                            <?php esc_html_e( 'Original Contact', 'disciple_tools' ) ?></h4>
                                        <div id="original-contact" class="display-fields"></div>
                                        <div class="center">
                                            <h4 class="merge-modal-subheading">
                                                <?php esc_html_e( 'Possible Duplicates', 'disciple_tools' ) ?></h4>
                                            <a
                                                id="dismiss_all_duplicates" style="font-size: .9375rem"><?php esc_html_e( 'Dismiss All', 'disciple_tools' ); ?></a>
                                            <div id="duplicates-spinner" class="loading-spinner active merge-modal-spinner">
                                            </div>
                                        </div>
                                        <div>
                                            <p id="no_dups_message" class="merge-modal-no-dups-message">
                                                <?php esc_html_e( 'No duplicates found.', 'disciple_tools' ); ?></p>
                                            <div id="duplicates_list"></div>
                                        </div>
                                        <div class="reveal" id="merge-with-post-modal" data-reveal style="min-height:500px">
                                            <h3><?php echo esc_html( sprintf( _x( 'Merge %s', 'Merge Contacts', 'disciple_tools' ), $post_settings['label_plural'] ?? $post_type ) ) ?>
                                            </h3>
                                            <p><?php echo esc_html( sprintf( _x( 'Merge this %1$s with another %2$s', 'Merge this contact with another contact', 'disciple_tools' ), $post_settings['label_singular'] ?? $post_type, $post_settings['label_singular'] ?? $post_type ) ) ?>
                                            </p>
                                            <div class="merge_with details">
                                                <var id="merge_with-result-container"
                                                    class="result-container merge_with-result-container"></var>
                                                <div id="merge_with_t" name="form-merge_with">
                                                    <div class="typeahead__container">
                                                        <div class="typeahead__field">
                                                            <span class="typeahead__query">
                                                                <input class="js-typeahead-merge_with input-height"
                                                                    name="merge_with[query]"
                                                                    placeholder="<?php echo esc_html( sprintf( _x( 'Search %s', "Search 'something'", 'disciple_tools' ), $post_settings['label_plural'] ?? $post_type ) ) ?>"
                                                                    autocomplete="off">
                                                            </span>
                                                            <span class="typeahead__button">
                                                                <button type="button"
                                                                    class="search_merge_with typeahead__image_button input-height"
                                                                    data-id="user-select_t">
                                                                    <img
                                                                        src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/chevron_down.svg' ) ?>" />
                                                                </button>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <br>
                                            <div class="confirm-merge-with-post" style="display: none">
                                                <p><span id="name-of-post-to-merge"></span>
                                                    <?php echo esc_html_x( 'selected.', 'added to the end of a sentence', 'disciple_tools' ); ?>
                                                </p>
                                                <p><?php esc_html_e( 'Click merge to continue.', 'disciple_tools' ); ?></p>
                                            </div>
                                            <div class="grid-x">
                                                <button class="button button-cancel clear" data-close aria-label="Close reveal"
                                                    type="button">
                                                    <?php esc_html_e( 'Cancel', 'disciple_tools' ); ?>
                                                </button>
                                                <form
                                                    action="<?php echo esc_url( site_url() ); ?>/<?php echo esc_html( dt_get_post_type() ); ?>/mergedetails"
                                                    method="get">
                                                    <input type="hidden" name="currentid"
                                                        value="<?php echo esc_html( get_the_ID() ); ?>" />
                                                    <input id="confirm-merge-with-post-id" type="hidden" name="dupeid"
                                                        value="" />
                                                    <button type="submit" class="button confirm-merge-with-post"
                                                        style="display: none;">
                                                        <?php esc_html_e( 'Merge', 'disciple_tools' ); ?>
                                                    </button>
                                                </form>

                                            </div>
                                        </div>

                                    </span>

                                </dt-modal>

                                <button class="section-chevron chevron_down show-details-section">
                                    <img
                                        src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/chevron_down.svg' ) ?>" />
                                </button>
                                <button class="section-chevron chevron_up show-details-section">
                                    <img
                                        src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/chevron_up.svg' ) ?>" />
                                </button>
                            </h3>

                            <!--
                            Details section
                        -->
                            <div class="collapsed-details-section">

                                <!-- row for communication channel elements -->
                                <div class="detail-snippet-row">
                                    <?php
                                    //setup the order of the tile fields
                                    $order = $tiles['details']['order'] ?? [];
                                    foreach ( $post_settings['fields'] as $key => $option ) {
                                        if ( isset( $option['tile'] ) && $option['tile'] === 'details' && ( $option['type'] === 'communication_channel' || $key === 'name' ) ) {
                                            if ( !in_array( $key, $order ) ) {
                                                $order[] = $key;
                                            }
                                        }
                                    }
                                    foreach ( $order as $field_key ) {
                                        if ( !isset( $post_settings['fields'][$field_key] ) ) {
                                            continue;
                                        }

                                        $field = $post_settings['fields'][$field_key];
                                        if ( isset( $field['tile'] ) && $field['tile'] === 'details' && ( $field['type'] === 'communication_channel' || $field_key === 'name' ) ) {
                                            ?>
                                            <div class="detail-snippet" id="collapsed-detail-<?php echo esc_html( $field_key ); ?>">
                                                <?php dt_render_field_icon( $field, 'dt-icon', true ); ?>
                                                <span class="collapsed-items" dir="auto"></span>
                                            </div>
                                            <?php
                                        }
                                    }
                                    ?>
                                </div>

                                <!-- row for misc elements -->
                                <div class="detail-snippet-row">
                                    <?php
                                    $order = $tiles['details']['order'] ?? [];
                                    foreach ( $post_settings['fields'] as $key => $option ) {
                                        if ( isset( $option['tile'] ) && $option['tile'] === 'details' && $option['type'] !== 'communication_channel' ) {
                                            if ( !in_array( $key, $order ) ) {
                                                $order[] = $key;
                                            }
                                        }
                                    }
                                    foreach ( $order as $field_key ) {
                                        if ( !isset( $post_settings['fields'][$field_key] ) ) {
                                            continue;
                                        }

                                        $field = $post_settings['fields'][$field_key];
                                        if ( !dt_field_enabled_for_record_type( $field, $dt_post ) ) {
                                            continue;
                                        }

                                        if ( isset( $field['tile'] ) && $field['tile'] === 'details' && $field['type'] !== 'communication_channel' && $field_key !== 'name' ) {
                                            ?>
                                            <div class="detail-snippet" id="collapsed-detail-<?php echo esc_html( $field_key ); ?>">
                                                <?php dt_render_field_icon( $field, 'dt-icon', true ); ?>
                                                <span class="collapsed-items" dir="auto"></span>
                                            </div>
                                            <?php
                                        }
                                    }
                                    ?>
                                </div>
                            </div> <!-- end collapse details section -->
                            <div id="show-details-edit-button" class="show-details-section"
                                style="text-align: center; background-color:rgb(236, 245, 252);margin: 3px -15px -15px -15px; border-radius: 0 0 10px 10px;">
                                <a class="button clear " style="margin:0;padding:3px 0; width:100%">
                                    <?php esc_html_e( 'Edit all details fields', 'disciple_tools' ); ?>
                                </a>
                            </div>

                            <div id="details-section" class="display-fields" style="display: none; margin-top:20px">
                                <div class="grid-x grid-margin-x">
                                    <?php
                                    //setup the order of the tile fields
                                    $order = $tiles['details']['order'] ?? [];
                                    foreach ( $post_settings['fields'] as $key => $option ) {
                                        if ( isset( $option['tile'] ) && $option['tile'] === 'details' ) {
                                            if ( !in_array( $key, $order ) ) {
                                                $order[] = $key;
                                            }
                                        }
                                    }
                                    foreach ( $order as $field_key ) {
                                        if ( !isset( $post_settings['fields'][$field_key] ) ) {
                                            continue;
                                        }
                                        $field = $post_settings['fields'][$field_key];
                                        $enabled_for_type = dt_field_enabled_for_record_type( $field, $dt_post );
                                        if (
                                            ( isset( $post_settings['fields'][$field_key]['hidden'] ) && true === $post_settings['fields'][$field_key]['hidden'] )
                                            || !$enabled_for_type
                                        ) {
                                            continue;
                                        }

                                        if ( isset( $field['tile'] ) && $field['tile'] === 'details' ) { ?>
                                            <div class="cell small-12 medium-6">
                                                <?php render_field_for_display( $field_key, $post_settings['fields'], $dt_post, true ); ?>
                                            </div>
                                        <?php }
                                    }
                                    // let the plugin add section content
                                    do_action( 'dt_details_additional_section', 'details', $post_type, $post_id );
                                    ?>
                                </div>
                            </div>
                        </section>
                    <?php endif; ?>

                    <?php do_action( 'dt_record_after_details_section', $post_type, $dt_post ); ?>

                    <!--
                        Tiles Section
                    -->
                    <div class="cell small-12">
                        <div class="grid-x grid-margin-x grid-margin-y grid">
                            <?php
                            foreach ( $tiles as $tile_key => $tile_options ) {
                                $class = '';
                                if ( in_array( $tile_key, [ 'details', 'status' ] ) ) {
                                    continue;
                                }
                                if ( ( isset( $tile_options['hidden'] ) && $tile_options['hidden'] ) ) {
                                    $class = 'hidden-grid-item';
                                }
                                if ( !dt_display_tile( $tile_options, $dt_post ) ) {
                                    $class = 'hidden-grid-item';
                                }
                                if ( isset( $tile_options['display_for']['type'], $dt_post['type']['key'] ) && !in_array( $dt_post['type']['key'], $tile_options['display_for']['type'] ) ) {
                                    $class = 'hidden-grid-item';
                                }
                                if ( !isset( $tile_options['label'] ) ) {
                                    $class = 'hidden-grid-item';
                                }
                                ?>
                                <section id="<?php echo esc_html( $tile_key ) ?>"
                                    class="custom-tile-section xlarge-6 large-12 medium-6 cell grid-item <?php echo esc_html( $class ); ?>">
                                    <div class="bordered-box" id="<?php echo esc_html( $tile_key ) ?>-tile">
                                        <?php
                                        //setup tile label if see by customizations
                                        $tile_label = 'Default Tile Label';
                                        if ( isset( $tile_options['label'] ) ) {
                                            $tile_label = esc_html( $tile_options['label'] );
                                            ?>
                                            <h3 class="section-header">
                                                <?php echo esc_html( $tile_options['label'] ) ?>
                                                <dt-modal data-tile="<?php echo esc_html( $tile_key ) ?>" class="help-button-tile"
                                                    title=<?php echo esc_html( $tile_key ) ?> context="alert" ishelp=""
                                                    buttonClass={"alert":true} buttonStyle={"background":"none","border":"0px"}
                                                    imageSrc="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/help.svg' ) ?>"
                                                    imageStyle={"height":"15px"} tileLabel="<?php echo esc_html( $tile_label ) ?>">
                                                    <span slot="content">
                                                        <?php
                                                        $html = get_help_modal_html();
                                                        echo wp_kses_post( $html );
                                                        ?>
                                                    </span>
                                                </dt-modal>
                                                <button class="section-chevron chevron_down">
                                                    <img
                                                        src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/chevron_down.svg' ) ?>" />
                                                </button>
                                                <button class="section-chevron chevron_up">
                                                    <img
                                                        src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/chevron_up.svg' ) ?>" />
                                                </button>
                                            </h3>
                                        <?php } ?>

                                        <div class="section-body">
                                            <?php
                                            // let the plugin add section content
                                            add_action('dt_details_additional_section', function ( $t_key, $pt ) use ( $post_type, $tile_key, $post_settings, $dt_post, $tile_options ) {
                                                if ( $pt !== $post_type || $tile_key !== $t_key ) {
                                                    return;
                                                }
                                                //setup the order of the tile fields
                                                $order = $tile_options['order'] ?? [];
                                                foreach ( $post_settings['fields'] as $key => $option ) {
                                                    if ( isset( $option['tile'] ) && $option['tile'] === $tile_key && !in_array( $key, $order ) ) {
                                                        $order[] = $key;
                                                    }
                                                }
                                                foreach ( $order as $field_key ) {
                                                    if ( !isset( $post_settings['fields'][$field_key] ) ) {
                                                        continue;
                                                    }

                                                    $field = $post_settings['fields'][$field_key];
                                                    if ( isset( $field['tile'] ) && $field['tile'] === $tile_key ) {
                                                        render_field_for_display( $field_key, $post_settings['fields'], $dt_post, true );
                                                    }
                                                }
                                            }, 20, 2);
                                            do_action( 'dt_details_additional_section', $tile_key, $post_type, $post_id );
                                            ?>
                                        </div>
                                    </div>
                                </section>
                            <?php }
                            do_action( 'dt_record_bottom_after_tiles', $post_type, $dt_post ); ?>
                        </div>
                    </div>
                    <?php do_action( 'dt_record_bottom_below_tiles', $post_type, $dt_post ); ?>

                    <!--
                       Hidden Tiles Section
                   -->
                    <section id="hidden_tiles_section" class="small-12 cell bordered-box"
                        style="display: none; text-align: center;">
                        <a id="hidden_tiles_section_show_but"><?php echo esc_html( __( 'Show Hidden Tiles', 'disciple_tools' ) ) ?>
                            (<span id="hidden_tiles_section_count"></span>)</a>
                    </section>
                </div>
            </main>

            <aside class="auto cell grid-x">
                <section class="comment-activity-section cell" id="comment-activity-section">
                    <?php get_template_part( 'dt-assets/parts/loop', 'activity-comment' ); ?>
                </section>
            </aside>

        </div>
    </div>

    <?php get_template_part( 'dt-assets/parts/modals/modal', 'share' ); ?>
    <?php get_template_part( 'dt-assets/parts/modals/modal', 'tasks' ); ?>
    <?php get_template_part( 'dt-assets/parts/modals/modal', 'new-contact' ); ?>

    <div class="reveal" id="create-tag-modal" data-reveal data-reset-on-close>
        <h3><?php esc_html_e( 'Create Tag', 'disciple_tools' ) ?></h3>
        <p><?php esc_html_e( 'Create a tag and apply it to this record.', 'disciple_tools' ) ?></p>

        <form class="js-create-tag">
            <label for="title">
                <?php esc_html_e( 'Tag', 'disciple_tools' ); ?>
            </label>
            <input name="title" id="new-tag" type="text" placeholder="<?php esc_html_e( 'Tag', 'disciple_tools' ); ?>"
                required aria-describedby="name-help-text">
            <p class="help-text" id="name-help-text"><?php esc_html_e( 'This is required', 'disciple_tools' ); ?></p>
        </form>

        <div class="grid-x">
            <button class="button button-cancel clear" data-close aria-label="Close reveal" type="button">
                <?php echo esc_html__( 'Cancel', 'disciple_tools' ) ?>
            </button>
            <button class="button" data-close type="button" id="create-tag-return">
                <?php esc_html_e( 'Create and apply tag', 'disciple_tools' ); ?>
            </button>
            <button class="close-button" data-close aria-label="<?php esc_html_e( 'Close', 'disciple_tools' ); ?>"
                type="button">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    </div>

    <div class="reveal" id="delete-record-modal" data-reveal data-reset-on-close>
        <h3><?php echo esc_html( sprintf( _x( 'Delete %s', 'Delete Contact', 'disciple_tools' ), DT_Posts::get_post_settings( $post_type )['label_singular'] ) ) ?>
        </h3>
        <p><?php echo esc_html( sprintf( _x( 'Are you sure you want to delete %s?', 'Are you sure you want to delete name?', 'disciple_tools' ), $dt_post['name'] ) ) ?>
        </p>

        <div class="grid-x">
            <button class="button button-cancel clear" data-close aria-label="Close reveal" type="button">
                <?php echo esc_html__( 'Cancel', 'disciple_tools' ) ?>
            </button>
            <button class="button alert loader" type="button" id="delete-record">
                <?php esc_html_e( 'Delete', 'disciple_tools' ); ?>
            </button>
            <button class="close-button" data-close aria-label="<?php esc_html_e( 'Close', 'disciple_tools' ); ?>"
                type="button">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    </div>

    <?php get_template_part( 'dt-assets/parts/modals/modal', 'record-history' ); ?>

    <?php do_action( 'dt_record_footer', $post_type, $post_id ) ?>

    <?php get_footer();
} )();
