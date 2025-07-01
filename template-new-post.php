<?php
declare(strict_types=1);
if ( !defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

dt_please_log_in();

$url = dt_get_url_path();
$dt_post_type = explode( '/', $url )[0];

if ( ! current_user_can( 'create_' . $dt_post_type ) ) {
    wp_die( esc_html( 'You do not have permission to publish ' . $dt_post_type ), 'Permission denied', 403 );
}

get_header();
$post_settings = DT_Posts::get_post_settings( $dt_post_type );

$type_choice_present = false;
$selected_type = null;
if ( isset( $post_settings['fields']['type']['default'] ) ){
    $non_hidden_choices = array_filter( $post_settings['fields']['type']['default'], function ( $type_option ){
        return empty( $type_option['hidden'] ) && !empty( $type_option['in_create_form'] );
    } );
    if ( count( $non_hidden_choices ) > 1 ){
        $type_choice_present = true;
    }
}
?>

    <div id="content" class="template-new-post">
        <div id="inner-content" class="grid-x grid-margin-x">
            <div class="large-2 medium-12 small-12 cell"></div>

            <span class="large-8 medium-12 small-12 cell">
                <form class="js-create-post bordered-box display-fields">

                    <table>
                        <tbody style="border: none;">
                            <tr>
                                <td>
                                    <h3 class="section-header">
                                        <?php echo esc_html( sprintf( __( 'New %s', 'disciple_tools' ), $post_settings['label_singular'] ) ) ?>
                                    </h3>
                                </td>
                                <td>
                                    <span style="float: right;">
                                        <a href="<?php echo esc_html( get_site_url() . '/' . $dt_post_type . '/new-bulk' ) ?>"
                                           class="button"
                                           style="margin:0px 0px 0px 0px; padding:5px 5px 5px 5px;"><?php echo esc_html__( 'Add Bulk Records?', 'disciple_tools' ) ?>
                                        </a>
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- choose the record type -->
                    <?php if ( $type_choice_present ){ ?>
                    <div class="type-control-field" style="margin-top:20px">
                        <strong>
                            <?php echo esc_html( sprintf( __( 'Select the %s type:', 'disciple_tools' ), $post_settings['label_singular'] ) ) ?>
                            <?php if ( $dt_post_type === 'contacts' ) : ?>
                            <button class="help-button-field" type="button" data-section="type-help-text">
                                <img class="help-icon" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/help.svg' ) ?>"/>
                            </button>
                            <?php endif; ?>
                        </strong>
                    </div>
                    <div class="type-options">
                        <?php if ( isset( $post_settings['fields']['type']['default'] ) ) {
                            uasort( $post_settings['fields']['type']['default'], function ( $a, $b ){
                                return ( $a['order'] ?? 100 ) <=> ( $b['order'] ?? 100 );
                            });
                        }
                        foreach ( $post_settings['fields']['type']['default'] as $option_key => $type_option ) {
                            $selected_type = !empty( $type_option['default'] ) ? $option_key : $selected_type;
                            //order fields alphabetically by Name
                            if ( empty( $type_option['hidden'] ) && ( !isset( $type_option['in_create_form'] ) || $type_option['in_create_form'] !== false ) ){ ?>
                                <div class="type-option <?php echo esc_html( !empty( $type_option['default'] ) ? 'selected' : '' ); ?>" id="<?php echo esc_html( $option_key ); ?>">
                                    <div class="type-option-border">
                                        <input type="radio" name="type" value="<?php echo esc_html( $option_key ); ?>" style="display: none">
                                        <div class="type-option-rows">
                                            <div>
                                                <?php dt_render_field_icon( $type_option ) ?>
                                                <strong class="type-option-title"><?php echo esc_html( $type_option['label'] ); ?></strong>
                                            </div>
                                            <div>
                                                <img class="dt-icon" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/visibility.svg' ) ?>"/>
                                                 <?php echo esc_html( $type_option['visibility'] ?? '' ); ?>
                                            </div>
                                            <div>
                                                <img class="dt-icon" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/help.svg' ) ?>"/>
                                                <?php echo esc_html( $type_option['description'] ?? '' ); ?>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            <?php }
                        } ?>
                    </div>
                    <?php } ?>


                    <div class="form-fields">
                        <?php foreach ( $post_settings['fields'] as $field_key => $field_settings ) {
                            if ( !empty( $field_settings['hidden'] ) && empty( $field_settings['custom_display'] ) ){
                                continue;
                            }
                            if ( isset( $field_settings['in_create_form'] ) && $field_settings['in_create_form'] === false ){
                                continue;
                            }
                            if ( !isset( $field_settings['tile'] ) || empty( $field_settings['tile'] ) || $field_settings['tile'] === 'no_tile' ){
                                continue;
                            }
                            $classes = '';
                            $show_field = false;
                            //add types the field should show up on as classes
                            if ( !empty( $field_settings['in_create_form'] ) ){
                                if ( is_array( $field_settings['in_create_form'] ) ){
                                    foreach ( $field_settings['in_create_form'] as $type_key ){
                                        $classes .= $type_key . ' ';
                                        if ( $type_key === $selected_type ){
                                            $show_field = true;
                                        }
                                    }
                                } elseif ( $field_settings['in_create_form'] === true ){
                                    $classes = 'all';
                                    $show_field = true;
                                }
                            } else {
                                $classes = 'other-fields';
                            }

                            ?>
                            <!-- hide the fields that were not selected to be displayed by default in the create form -->
                            <div <?php echo esc_html( !$show_field ? 'style=display:none' : '' ); ?>
                                class="form-field <?php echo esc_html( $classes ); ?>">
                                <?php
                                render_field_for_display( $field_key, $post_settings['fields'], [] );
                                if ( isset( $field_settings['required'] ) && $field_settings['required'] === true ) { ?>
                                    <p class="help-text" id="name-help-text"><?php esc_html_e( 'This is required', 'disciple_tools' ); ?></p>
                                <?php } ?>
                            </div>
                        <?php } ?>
                        <div id="show-shield-banner" style="text-align: center; background-color:rgb(236, 245, 252);margin: 3px -15px 15px -15px;">
                            <a class="button clear" id="show-hidden-fields" style="margin:0;padding:3px 0; width:100%">
                                <?php esc_html_e( 'Show all fields', 'disciple_tools' ); ?>
                            </a>
                            <a class="button clear" id="hide-hidden-fields" style="margin:0;padding:3px 0; width:100%; display: none;">
                                <?php esc_html_e( 'Hide fields', 'disciple_tools' ); ?>
                            </a>
                        </div>


                        <div style="text-align: center">
                            <a href="<?php echo esc_html( get_site_url() . '/' . $dt_post_type )?>" class="button small clear"><?php echo esc_html__( 'Cancel', 'disciple_tools' )?></a>
                            <button class="button loader js-create-post-button dt-green" type="submit" disabled><?php esc_html_e( 'Save and continue editing', 'disciple_tools' ); ?></button>
                        </div>
                        <div class="error-text"></div>
                    </div>
                </form>

            </div>

            <div class="large-2 medium-12 small-12 cell"></div>
        </div>
    </div>

    <div class="reveal" id="create-tag-modal" data-reveal data-reset-on-close>
        <h3><?php esc_html_e( 'Create Tag', 'disciple_tools' )?></h3>
        <p><?php esc_html_e( 'Create a tag and apply it to this record.', 'disciple_tools' )?></p>

        <form class="js-create-tag">
            <label for="title">
                <?php esc_html_e( 'Tag', 'disciple_tools' ); ?>
            </label>
            <input name="title" id="new-tag" type="text" placeholder="<?php esc_html_e( 'Tag', 'disciple_tools' ); ?>" required aria-describedby="name-help-text">
            <p class="help-text" id="name-help-text"><?php esc_html_e( 'This is required', 'disciple_tools' ); ?></p>
        </form>

        <div class="grid-x">
            <button class="button button-cancel clear" data-close aria-label="Close reveal" type="button">
                <?php echo esc_html__( 'Cancel', 'disciple_tools' )?>
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
