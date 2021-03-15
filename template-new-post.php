<?php
declare(strict_types=1);
if ( !defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

dt_please_log_in();

$url = dt_get_url_path();
$dt_post_type = explode( "/", $url )[0];

if ( ! current_user_can( 'create_' . $dt_post_type ) ) {
    wp_die( esc_html( "You do not have permission to publish " . $dt_post_type ), "Permission denied", 403 );
}

get_header();
$post_settings = DT_Posts::get_post_settings( $dt_post_type );

$force_type_choice = false;
if ( isset( $post_settings["fields"]["type"] ) && sizeof( $post_settings["fields"]["type"]["default"] ) > 1 ){
    $force_type_choice = true;
}
?>

    <div id="content" class="template-new-post">
        <div id="inner-content" class="grid-x grid-margin-x">
            <div class="large-2 medium-12 small-12 cell"></div>

            <div class="large-8 medium-12 small-12 cell">
                <form class="js-create-post bordered-box display-fields">
                    <h3 class="section-header">
                        <?php echo esc_html( sprintf( __( 'New %s', 'disciple_tools' ), $post_settings["label_singular"] ) ) ?>
                    </h3>

                    <!-- choose the record type -->
                    <?php if ( $force_type_choice ){ ?>
                    <div class="type-control-field" style="margin:20px 0">
                        <strong>
                        <?php echo esc_html( sprintf( __( 'Select the %s type:', 'disciple_tools' ), $post_settings["label_singular"] ) ) ?>
                        </strong>
                    </div>
                    <div class="type-options">
                        <?php if ( isset( $post_settings["fields"]["type"]["default"] ) ) {
                            uasort( $post_settings["fields"]["type"]["default"], function ( $a, $b ){
                                return ( $a['order'] ?? 100 ) <=> ( $b['order'] ?? 100 );
                            });
                        }
                        foreach ( $post_settings["fields"]["type"]["default"] as $option_key => $type_option ) {
                            //order fields alphabetically by Name
                            if ( empty( $type_option["hidden"] ) ){ ?>
                                <div class="type-option" id="<?php echo esc_html( $option_key ); ?>">
                                    <div class="type-option-border">
                                        <input type="radio" name="type" value="<?php echo esc_html( $option_key ); ?>" style="display: none">
                                        <div class="type-option-rows">
                                            <div>
                                                <?php if ( isset( $type_option["icon"] ) ) : ?>
                                                <img class="dt-icon" src="<?php echo esc_url( $type_option["icon"] ) ?>">
                                                <?php endif; ?>
                                                <strong class="type-option-title"><?php echo esc_html( $type_option["label"] ); ?></strong>
                                            </div>
                                            <div>
                                                <img class="dt-icon" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/visibility.svg' ) ?>"/>
                                                 <?php echo esc_html( $type_option["visibility"] ?? "" ); ?>
                                            </div>
                                            <div>
                                                <img class="dt-icon" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/help.svg' ) ?>"/>
                                                <?php echo esc_html( $type_option["description"] ?? "" ); ?>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            <?php }
                        } ?>
                    </div>
                    <?php } ?>


                    <div class="form-fields" <?php echo esc_html( $force_type_choice ? "style=display:none" : "" ); ?>>
                        <?php foreach ( $post_settings["fields"] as $field_key => $field_settings ) {
                            if ( !empty( $field_settings["hidden"] ) && empty( $field_settings["custom_display"] ) ){
                                continue;
                            }
                            if ( isset( $field_settings["in_create_form"] ) && $field_settings["in_create_form"] === false ){
                                continue;
                            }
                            if ( !isset( $field_settings["tile"] ) ){
                                continue;
                            }
                            $classes = "";
                            //add types the field should show up on as classes
                            if ( !empty( $field_settings['in_create_form'] ) ){
                                if ( is_array( $field_settings['in_create_form'] ) ){
                                    foreach ( $field_settings['in_create_form'] as $type_key ){
                                        $classes .= $type_key . " ";
                                    }
                                } elseif ( $field_settings['in_create_form'] === true ){
                                    $classes = "all";
                                }
                            } else {
                                $classes = "other-fields";
                            }

                            ?>
                            <!-- hide fields until the post type is chosen. hide the fields that were not selected to be displayed by default in the create form -->
                            <div <?php echo esc_html( ( $force_type_choice || $classes === "other-fields" ) ? "style=display:none" : "" ); ?>
                                class="form-field <?php echo esc_html( $classes ); ?>">
                            <?php
                            render_field_for_display( $field_key, $post_settings['fields'], [] );
                            if ( isset( $field_settings["required"] ) && $field_settings["required"] === true ) { ?>
                                <p class="help-text" id="name-help-text"><?php esc_html_e( "This is required", "disciple_tools" ); ?></p>
                            <?php } ?>
                            </div>
                        <?php } ?>
                        <div id="show-shield-banner" style="text-align: center; background-color:rgb(236, 245, 252);margin: 3px -15px 15px -15px;">
                            <a class="button clear" id="show-hidden-fields" style="margin:0;padding:3px 0; width:100%">
                                <?php esc_html_e( 'Show all fields', 'disciple_tools' ); ?>
                            </a>
                        </div>


                        <div style="text-align: center">
                            <a href="<?php echo esc_html( get_site_url() . "/" . $dt_post_type )?>" class="button small clear"><?php echo esc_html__( 'Cancel', 'disciple_tools' )?></a>
                            <button class="button loader js-create-post-button dt-green" type="submit" disabled><?php esc_html_e( "Save and continue editing", "disciple_tools" ); ?></button>
                        </div>
                    </div>
                </form>

            </div>

            <div class="large-2 medium-12 small-12 cell"></div>
        </div>
    </div>

<?php
get_footer();
