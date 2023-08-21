<?php

declare( strict_types=1 );
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Launch after all required scripts have been loaded!
add_action( 'wp_enqueue_scripts', 'new_bulk_record_scripts', 2022 );
function new_bulk_record_scripts() {

    global $wp_scripts;

    // Obtain current data for given handle and reshape, if needed.
    $localized_data = $wp_scripts->get_data( 'new-record', 'data' );
    if ( ! is_array( $localized_data ) ) {
        $localized_data = json_decode( str_replace( 'var new_record_localized = ', '', substr( $localized_data, 0, - 1 ) ), true );
    }

    // Append bulk related elements.
    $post_type                                         = explode( '/', dt_get_url_path() )[0];
    $localized_data['bulk_copy_control_but_img_uri']   = esc_html( get_template_directory_uri() . '/dt-assets/images/duplicate.svg' );
    $localized_data['bulk_record_removal_but_img_uri'] = esc_html( get_template_directory_uri() . '/dt-assets/images/invalid.svg' );
    $localized_data['bulk_save_redirect_uri']          = esc_html( site_url( sprintf( '/%s/', $post_type ) ) );
    $localized_data['bulk_record_fields']              = get_rendered_new_bulk_record_fields_html( $post_type );
    $localized_data['bulk_mapbox_placeholder_txt']     = esc_attr( __( 'Search Location', 'disciple_tools' ) );

    // Save updated localized data.
    $wp_scripts->add_data( 'new-record', 'data', '' );
    wp_localize_script( 'new-record', 'new_record_localized', $localized_data );
}

/**
 * Render initial fields html to be displayed during bulk post type record creation.
 *
 * @param $post_type
 *
 * @return false|string
 */
function get_rendered_new_bulk_record_fields_html( $post_type ) {
    ob_start();

    render_new_bulk_record_fields( $post_type );

    $html = ob_get_contents();

    ob_end_clean();

    return $html;
}

dt_please_log_in();

$url          = dt_get_url_path();
$dt_post_type = explode( '/', $url )[0];

if ( ! current_user_can( 'create_' . $dt_post_type ) ) {
    wp_die( esc_html( 'You do not have permission to publish ' . $dt_post_type ), 'Permission denied', 403 );
}

get_header();
$post_settings = DT_Posts::get_post_settings( $dt_post_type );

$type_choice_present = false;
$selected_type       = null;
if ( isset( $post_settings['fields']['type'] ) && sizeof( $post_settings['fields']['type']['default'] ) > 1 ) {
    $type_choice_present = true;
}
?>

    <div id="content" class="template-new-post" style="margin-left: 0; margin-right: 0; min-width: 100% !important;">
        <div id="inner-content" class="grid-x grid-margin-x">
            <div class="large-0 medium-12 small-12 cell" style="display: none;"></div>

            <div class="large-12 medium-12 small-12 cell">
                <input type="hidden" id="bulk_records_current_layout" value="">
                <form class="js-create-post-bulk bordered-box display-fields">


                    <table>
                        <tbody style="border: none;">
                        <tr>
                            <td>
                                <h3 class="section-header">
                                    <?php echo esc_html( sprintf( __( 'New Bulk %s', 'disciple_tools' ), $post_settings['label_plural'] ) ) ?>
                                    <span style="display:inline-block;">
                                        <button class="button clear" id="choose_fields_to_show_in_records"
                                                style="margin:10px 10px 10px 20px; padding:0;">
                                            <?php esc_html_e( 'Fields', 'disciple_tools' ); ?>
                                            <img class="dt-icon"
                                                 src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/options.svg' ) ?>"/>
                                        </button>
                                    </span>
                                </h3>

                                <div id="list_fields_picker" class="list_field_picker"
                                     style="display:none; padding:20px; border-radius:5px; background-color:#ecf5fc; margin: 30px 0">
                                    <p style="font-weight:bold"><?php esc_html_e( 'Choose which fields to display across new bulk records', 'disciple_tools' ); ?></p>
                                    <?php

                                    //order fields alphabetically by Name
                                    uasort( $post_settings['fields'], function ( $a, $b ) {
                                        return $a['name'] <=> $b['name'];
                                    } );

                                    ?>
                                    <ul class="ul-no-bullets" style="">
                                        <?php foreach ( $post_settings['fields'] as $field_key => $field_values ):
                                            if ( ! empty( $field_values['hidden'] ) && empty( $field_values['custom_display'] ) ) {
                                                continue;
                                            }
                                            if ( isset( $field_values['in_create_form'] ) && $field_values['in_create_form'] === false ) {
                                                continue;
                                            }
                                            if ( ! isset( $field_values['tile'] ) ) {
                                                continue;
                                            }
                                            if ( ! ( isset( $field_values['type'] ) && empty( $field_values['custom_display'] ) && empty( $field_values['hidden'] ) ) ) {
                                                continue;
                                            }
                                            ?>
                                            <li style="" class="">
                                                <label style="margin-right:15px; cursor:pointer">
                                                    <input type="checkbox" value="<?php echo esc_html( $field_key ); ?>"
                                                           style="margin:0">
                                                    <?php echo esc_html( $field_values['name'] ); ?>
                                                </label>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                    <button class="button" id="save_fields_choices"
                                            style="display: inline-block"><?php esc_html_e( 'Apply', 'disciple_tools' ); ?></button>
                                    <a class="button clear" id="reset_fields_choices"
                                       style="display: inline-block"><?php esc_html_e( 'reset to default', 'disciple_tools' ); ?></a>
                                </div>
                            </td>
                        </tr>
                        </tbody>
                    </table>

                    <!-- choose the record type -->
                    <?php if ( $type_choice_present ) { ?>
                        <div class="type-control-field" style="margin-top:20px">
                            <strong>
                                <?php echo esc_html( sprintf( __( 'Select the %s type:', 'disciple_tools' ), $post_settings['label_singular'] ) ) ?>
                                <?php if ( $dt_post_type === 'contacts' ) : ?>
                                    <button class="help-button-field" type="button" data-section="type-help-text">
                                        <img class="help-icon"
                                             src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/help.svg' ) ?>"/>
                                    </button>
                                <?php endif; ?>
                            </strong>
                        </div>
                        <div class="type-options" style="margin-top: 25px !important; margin-bottom: 50px !important;">
                            <?php if ( isset( $post_settings['fields']['type']['default'] ) ) {
                                uasort( $post_settings['fields']['type']['default'], function ( $a, $b ) {
                                    return ( $a['order'] ?? 100 ) <=> ( $b['order'] ?? 100 );
                                } );
                            }
                            foreach ( $post_settings['fields']['type']['default'] as $option_key => $type_option ) {
                                $selected_type = ! empty( $type_option['default'] ) ? $option_key : $selected_type;
                                //order fields alphabetically by Name
                                if ( empty( $type_option['hidden'] ) && ( ! isset( $type_option['in_create_form'] ) || $type_option['in_create_form'] !== false ) ) { ?>
                                    <div
                                        class="type-option <?php echo esc_html( ! empty( $type_option['default'] ) ? 'selected' : '' ); ?>"
                                        id="<?php echo esc_html( $option_key ); ?>">
                                        <div class="type-option-border">
                                            <input type="radio" name="type"
                                                   value="<?php echo esc_html( $option_key ); ?>" style="display: none">
                                            <div class="type-option-rows">
                                                <div>
                                                    <?php if ( isset( $type_option['icon'] ) ) : ?>
                                                        <img class="dt-icon"
                                                             src="<?php echo esc_url( $type_option['icon'] ) ?>">
                                                    <?php endif; ?>
                                                    <strong
                                                        class="type-option-title"><?php echo esc_html( $type_option['label'] ); ?></strong>
                                                </div>
                                                <div>
                                                    <img class="dt-icon"
                                                         src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/visibility.svg' ) ?>"/>
                                                    <?php echo esc_html( $type_option['visibility'] ?? '' ); ?>
                                                </div>
                                                <div>
                                                    <img class="dt-icon"
                                                         src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/help.svg' ) ?>"/>
                                                    <?php echo esc_html( $type_option['description'] ?? '' ); ?>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                <?php }
                            } ?>
                        </div>
                    <?php } ?>

                    <?php
                    if ( DT_Mapbox_API::get_key() ) {
                        ?>
                        <div class="reveal" id="altered_mapbox_search_modal" data-reveal style="min-height: 400px;">

                            <h1><?php esc_attr_e( 'Search Location', 'disciple_tools' ); ?></h1>

                            <div id="mapbox-autocomplete" class="mapbox-autocomplete input-group"
                                 data-autosubmit="false">
                                <input id="mapbox-search" type="text" name="mapbox_search"
                                       placeholder="<?php esc_attr_e( 'Search Location', 'disciple_tools' ); ?>"
                                       autocomplete="off" dir="auto"/>
                                <div class="input-group-button">
                                    <button id="mapbox-spinner-button" class="button hollow" style="display:none;"><span
                                            class="loading-spinner active"></span></button>
                                    <button id="mapbox-clear-autocomplete"
                                            class="button alert input-height delete-button-style mapbox-delete-button"
                                            type="button"
                                            title="<?php esc_attr_e( 'Delete Location', 'disciple_tools' ); ?>">
                                        &times;
                                    </button>
                                </div>
                                <div id="mapbox-autocomplete-list" class="mapbox-autocomplete-items"></div>
                            </div>

                            <button class="close-button" data-close aria-label="<?php esc_html_e( 'Close', 'disciple_tools' ); ?>" type="button">
                                <span aria-hidden="true">&times;</span>
                            </button>

                            <span style="float: right; margin-top: 250px;">
                                <button id="altered_mapbox_search_modal_but_cancel" class="button" type="button">
                                    <?php esc_attr_e( 'Cancel', 'disciple_tools' ); ?>
                                </button>
                                <button id="altered_mapbox_search_modal_but_update" class="button" type="button">
                                    <?php esc_attr_e( 'Update', 'disciple_tools' ); ?>
                                </button>
                            </span>

                        </div>
                        <?php
                    }
                    ?>

                    <div class="form-fields">
                        <div id="form_fields_records" class="container">
                            <div class="form-fields-record">
                                <input type="hidden" id="bulk_record_id" value="1"/>
                                <span style="min-width: 25px;">&nbsp;</span>
                                <?php
                                render_new_bulk_record_fields( $dt_post_type );
                                ?>
                            </div>
                        </div>

                        <div id="add_new_bulk_record_div"
                             style="text-align: center; margin: 15px;">
                            <a class="button dt-green" id="add_new_bulk_record">
                                <?php esc_html_e( 'Add New Row', 'disciple_tools' ); ?>
                            </a>
                        </div>


                        <div style="text-align: center">
                            <a href="<?php echo esc_html( $dt_post_type ) ?>"
                               class="button small clear"><?php echo esc_html__( 'Cancel', 'disciple_tools' ) ?></a>
                            <button class="button loader js-create-post-bulk-button dt-green" type="submit"
                                    disabled><?php esc_html_e( 'Add Records', 'disciple_tools' ); ?></button>
                        </div>
                    </div>
                </form>

            </div>

            <div class="large-0 medium-12 small-12 cell"></div>
        </div>
    </div>

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
            <button class="close-button" data-close aria-label="<?php esc_html_e( 'Close', 'disciple_tools' ); ?>" type="button">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    </div>

<?php
get_footer();
