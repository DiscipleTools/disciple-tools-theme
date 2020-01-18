<?php
declare( strict_types=1 );

$dt_post_type = get_post_type();
if ( ! current_user_can( 'access_' . $dt_post_type ) ) {
    wp_safe_redirect( '/settings' );
}

( function () {
    get_header();
    dt_print_details_bar(
        true,
        false,
        false,
        false,
        false,
        true,
        []
    );

    $post_type = get_post_type();
    $post_settings = apply_filters( "dt_get_post_type_settings", [], $post_type );
    $dt_post = DT_Posts::get_post( $post_type, get_the_ID() );
    ?>
    <div id="content" class="single-template">
        <div id="inner-content" class="grid-x grid-margin-x grid-margin-y">
            <main id="main" class="xlarge-7 large-7 medium-12 small-12 cell" role="main" style="padding:0">
                <div class="cell grid-y grid-margin-y" style="display: block">
<!--                        @todo notification section-->

                    <!--
                        Main details section
                    -->
                    <div id="contact-details" class="small-12 cell grid-margin-y">
                        <section class="cell bordered-box">
                            <!--
                                Name row
                            -->
                            <div id="name-row" style="display: flex;">
                                <div class="item-details-header" style="flex-grow:1; text-align: center">
                                    <span class="title"><?php the_title_attribute(); ?></span>
                                </div>
                            </div>

                            <!--
                                Status Bar
                            -->
                            <div class="grid-x grid-margin-x" style="margin-top: 20px">
                                <?php do_action( 'dt_post_status_bar' ); ?>
                            </div>

<!--                            <hr />-->

                            <!--
                                Details section
                            -->
                            <div id="details-section" class="display-fields" style="">
                                <?php
                                // let the plugin add section content
                                do_action( "dt_details_additional_section", 'details', $post_type );

                                ?>
                                <div class="section-body">
                                    <?php
                                    //setup the order of the tile fields
                                    $order = $custom_tiles[$post_type]['details']["order"] ?? [];
                                    foreach ( $post_settings["fields"] as $key => $option ){
                                        if ( isset( $option["tile"] ) && $option["tile"] === 'details' ){
                                            if ( !in_array( $key, $order )){
                                                $order[] = $key;
                                            }
                                        }
                                    }
                                    foreach ( $order as $field_key ) {
                                        if ( !isset( $post_settings["fields"][$field_key] ) ){
                                            continue;
                                        }

                                        $field = $post_settings["fields"][$field_key];
                                        if ( isset( $field["tile"] ) && $field["tile"] === 'details'){
                                            render_field_for_display( $field_key, $post_settings["fields"], $dt_post );
                                        }
                                    }
                                    ?>
                                </div>
                            </div>
                        </section>
                    </div>


                    <!--
                        Tiles Section
                    -->
                    <div class="cell small-12">
                        <div class="grid-x grid-margin-x grid-margin-y grid">
                            <?php
                            //get sections added by plugins
                            $sections = apply_filters( 'dt_details_additional_section_ids', [], $post_type );
                            //get custom sections
                            $custom_tiles = dt_get_option( "dt_custom_tiles" );
                            foreach ( $custom_tiles[$post_type] ?? [] as $tile_key => $tile_options ){
                                if ( !in_array( $tile_key, $sections ) ){
                                    $sections[] = $tile_key;
                                }
                                //remove section if hidden
                                if ( isset( $tile_options["hidden"] ) && $tile_options["hidden"] == true ){
                                    $index = array_search( $tile_key, $sections );
                                    if ( $index !== false) {
                                        unset( $sections[ $index ] );
                                    }
                                }
                            }

                            foreach ( $sections as $section ){
                                ?>
                                <section id="<?php echo esc_html( $section ) ?>" class="xlarge-6 large-12 medium-6 cell grid-item">
                                    <div class="bordered-box">
                                        <?php
                                        //setup tile label if see by customizations
                                        if ( isset( $custom_tiles[$post_type][$section]["label"] ) ){ ?>
                                            <h3 class="section-header">
                                                <?php echo esc_html( $custom_tiles[$post_type][$section]["label"] )?>
                                                <button class="section-chevron chevron_down">
                                                    <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/chevron_down.svg' ) ?>"/>
                                                </button>
                                                <button class="section-chevron chevron_up">
                                                    <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/chevron_up.svg' ) ?>"/>
                                                </button>

                                            </h3>
                                        <?php }
                                        // let the plugin add section content
                                        do_action( "dt_details_additional_section", $section, $post_type );

                                        ?>
                                        <div class="section-body">
                                            <?php
                                            //setup the order of the tile fields
                                            $order = $custom_tiles[$post_type][$section]["order"] ?? [];
                                            foreach ( $post_settings["fields"] as $key => $option ){
                                                if ( isset( $option["tile"] ) && $option["tile"] === $section ){
                                                    if ( !in_array( $key, $order )){
                                                        $order[] = $key;
                                                    }
                                                }
                                            }
                                            foreach ( $order as $field_key ) {
                                                if ( !isset( $post_settings["fields"][$field_key] ) ){
                                                    continue;
                                                }

                                                $field = $post_settings["fields"][$field_key];
                                                if ( isset( $field["tile"] ) && $field["tile"] === $section){
                                                    render_field_for_display( $field_key, $post_settings["fields"], $dt_post );
                                                }
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </section>
                                <?php
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </main>

            <aside class="auto cell grid-x">
                <section class="comment-activity-section cell"
                         id="comment-activity-section">
                    <?php get_template_part( 'dt-assets/parts/loop', 'activity-comment' ); ?>
                </section>
            </aside>

        </div>
    </div>

    <?php get_template_part( 'dt-assets/parts/modals/modal', 'share' ); ?>

    <?php get_footer();
} )();
