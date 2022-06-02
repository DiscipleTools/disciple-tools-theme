<?php
/*
 *  Name: Merge Post Type Details
*/

dt_please_log_in();

// Redirect if unable to determine post type
// phpcs:disable
$post_type = dt_get_post_type();
// phpcs:enable
if ( empty( $post_type ) ) {
    return wp_redirect( '/' );
}

// Determine permission status for given post type
if ( ! current_user_can( 'access_' . $post_type ) ) {
    wp_die( esc_html( sprintf( 'You do not have permission to access %s', $post_type ) ), "Permission denied", 403 );
}

// Ensure required parameter ids are present
if ( ! isset( $_GET['currentid'], $_GET['dupeid'] ) ) {
    return wp_redirect( '/' . $post_type );
}

// Extract post ids
$dt_current_id = sanitize_text_field( wp_unslash( $_GET['currentid'] ) );
$dt_dupe_id    = sanitize_text_field( wp_unslash( $_GET['dupeid'] ) );

// Ensure this is not a self-merge
if ( $dt_current_id === $dt_dupe_id ) {
    wp_die( esc_html( 'Self-merges not allowed!' ), "Self-merge denied", 403 );
}

// Grab handles to various objects and test validity
$dt_current_post   = DT_Posts::get_post( $post_type, $dt_current_id );
$dt_duplicate_post = DT_Posts::get_post( $post_type, $dt_dupe_id );
if ( is_wp_error( $dt_current_post ) || is_wp_error( $dt_duplicate_post ) ) {
    get_template_part( "403", null, is_wp_error( $dt_current_post ) ? $dt_current_post : $dt_duplicate_post );
    die();
}

$post_settings        = DT_Posts::get_post_settings( $post_type );
$post_settings_fields = DT_Posts::get_post_field_settings( $post_type, false );

// Determine fields to be displayed
$fields_to_display = determine_post_fields_to_display( $post_settings_fields, $dt_current_post, $dt_duplicate_post );

// Load supporting merge scripts
function merge_post_details_scripts( $args ) {

    $dependencies = [ 'jquery', 'lodash', 'shared-functions', 'typeahead-jquery' ];
    if ( DT_Mapbox_API::get_key() ) {
        DT_Mapbox_API::load_mapbox_header_scripts();
        DT_Mapbox_API::load_mapbox_search_widget();
        $dependencies[] = 'mapbox-cookie';
        $dependencies[] = 'mapbox-search-widget';
        $dependencies[] = 'mapbox-gl';
    }

    dt_theme_enqueue_script( 'merge-post-details', 'dt-assets/js/merge-post-details.js', $dependencies, true );
    wp_localize_script( 'merge-post-details', 'merge_post_details', $args );
}

add_action( 'wp_enqueue_scripts', function () use ( $dt_current_post, $dt_duplicate_post, $post_settings, $fields_to_display ) {
    merge_post_details_scripts( [
        'posts'                    => [
            $dt_current_post['ID']   => [
                'record' => $dt_current_post,
                'html'   => render_post_fields_html( $dt_current_post, $fields_to_display, $post_settings['fields'], $dt_current_post['ID'] . '_', true )
            ],
            $dt_duplicate_post['ID'] => [
                'record' => $dt_duplicate_post,
                'html'   => render_post_fields_html( $dt_duplicate_post, $fields_to_display, $post_settings['fields'], $dt_duplicate_post['ID'] . '_', true )
            ]
        ],
        'post_settings'            => $post_settings,
        'post_fields_default_html' => render_post_fields_html( $dt_current_post, $fields_to_display, $post_settings['fields'], '', false ),
        'site_url'                 => esc_url( site_url( '/' ) ),
        'url_root'                 => esc_url_raw( rest_url() ),
        'nonce'                    => wp_create_nonce( 'wp_rest' ),
        'mapbox'                   => [
            'map_key'        => DT_Mapbox_API::get_key(),
            'google_map_key' => Disciple_Tools_Google_Geocode_API::get_key(),
            'translations'   => [
                'search_location' => __( 'Search Location', 'disciple_tools' ),
                'delete_location' => __( 'Delete Location', 'disciple_tools' ),
                'use'             => __( 'Use', 'disciple_tools' ),
                'open_modal'      => __( 'Open Modal', 'disciple_tools' )
            ]
        ],
        'translations'             => [
            'regions_of_focus' => __( 'Regions of Focus', 'disciple_tools' ),
            'all_locations'    => __( 'All Locations', 'disciple_tools' ),
            'error_msg'        => __( 'Sorry, something went wrong', 'disciple_tools' )
        ]
    ] );
}, 10 );

// Render dt header
get_header();

?>

    <div id="content" class="template-merge-post-details">
        <div id="inner-content" class="grid-x grid-margin-x">

            <!-- Merge Header-->
            <main id="main_header" class="large-12 medium-12 cell" role="main">
                <div class="bordered-box">
                    <h2 class="center"><?php echo esc_html( sprintf( "Merge Duplicate %s", $post_settings['label_plural'] ), 'disciple_tools' ) ?></h2>
                    <p class="center"
                       style="max-width: 75%; margin-left:auto; margin-right:auto;"><?php esc_html_e( "When you merge, the primary record is updated with the values you choose, and relationships to other items are shifted to the primary record; which can be switched below.", 'disciple_tools' ) ?></p>

                    <label>
                        <strong><?php esc_html_e( 'Copy comments to updated primary record', 'disciple_tools' ); ?></strong>
                        <input type="checkbox" id="merge_comments"
                               name="merge_comments" <?php checked( ! isset( $_GET["comments"] ) ) ?>>
                    </label>
                    <button class='button loader submit-merge' type='button'
                            value='Merge'><?php esc_html_e( 'Merge', 'disciple_tools' ); ?></button>

                    <br>
                    <span id="merge_errors"></span>
                </div>
            </main>
            <br>

            <!-- Archiving Post Record -->
            <main id="main_archiving" class="large-4 medium-4 cell" role="main">
                <div class="bordered-box">
                    <h2 class="center"><?php esc_html_e( "Archiving", 'disciple_tools' ) ?> - #<a
                            id="main_archiving_post_id_title_link" target="_blank"><span
                                id="main_archiving_post_id_title"></span></a>
                    </h2>

                    <div class="main-archiving-primary-switch-but-div">
                        <button id="main_archiving_primary_switch_but"
                                style="text-align: center;"
                                class="button center"><?php esc_html_e( "Use as Primary", 'disciple_tools' ) ?>
                        </button>
                    </div>

                    <input type="hidden" id="main_archiving_current_post_id"
                           value="<?php echo esc_html( $dt_current_post['ID'] ) ?>"/>
                    <div id="main_archiving_fields_div"></div>
                </div>
            </main>

            <!-- Primary Post Record -->
            <main id="main_primary" class="large-4 medium-4 cell" role="main">
                <div class="bordered-box">
                    <h2 class="center"><?php esc_html_e( "Primary", 'disciple_tools' ) ?> - #<a
                            id="main_primary_post_id_title_link" target="_blank"><span
                                id="main_primary_post_id_title"></span></a>
                    </h2>

                    <input type="hidden" id="main_primary_current_post_id"
                           value="<?php echo esc_html( $dt_duplicate_post['ID'] ) ?>"/>
                    <div id="main_primary_fields_div" style="margin-top: 60px;"></div>
                </div>
            </main>

            <!-- Updated Post Record -->
            <main id="main_updated" class="large-4 medium-4 cell" role="main">
                <div class="bordered-box">
                    <h2 class="center"><?php esc_html_e( "Updated", 'disciple_tools' ) ?> - #<span
                            id="main_updated_post_id_title"></span></h2>

                    <div id="main_updated_fields_div" style="margin-top: 60px;"></div>
                </div>
            </main>

            <!-- Merge Footer -->
            <main id="main_footer" class="large-12 medium-12 cell" role="main">
                <div class="bordered-box">
                    <button class='button loader submit-merge' type='button'
                            value='Merge'><?php esc_html_e( 'Merge', 'disciple_tools' ); ?></button>
                </div>
            </main>

        </div>
    </div>

<?php

function determine_post_fields_to_display( $settings_fields, $current_post, $duplicate_post ) {
    $fields_to_display = [];
    foreach ( $settings_fields ?? [] as $key => $field ) {

        // Ignore/Hide fields not present within both post records
        if ( ! empty( $current_post[ $key ] ) || ! empty( $duplicate_post[ $key ] ) ) {
            $fields_to_display[] = $key;
        }
    }

    return $fields_to_display;
}

function render_post_fields_html( $post, $fields, $settings_fields, $field_id_prefix, $show_field_select ) {
    ob_start();
    render_post_fields( $post, $fields, $settings_fields, $field_id_prefix, $show_field_select );
    $rendered_post_fields_html = ob_get_contents();
    ob_end_clean();

    return $rendered_post_fields_html;
}

function render_post_fields( $post, $fields, $settings_fields, $field_id_prefix, $show_field_select = true ) {
    $merge_capable_field_types = list_merge_capable_field_types();
    ?>

    <table>
        <tbody>
        <?php
        foreach ( $fields as $field ) {

            // Capture rendered field html
            ob_start();
            render_field_for_display( $field, $settings_fields, $post, true, false, $field_id_prefix );
            $rendered_field_html = ob_get_contents();
            ob_end_clean();

            $merge_field_id   = $field_id_prefix . $field;
            $merge_field_type = $settings_fields[ $field ]['type'];

            // Only display if valid html content has been generated
            if ( ! empty( $rendered_field_html ) ) {
                ?>
                <tr>
                    <?php
                    if ( $show_field_select ) {
                        if ( in_array( $settings_fields[ $field ]['type'], $merge_capable_field_types ) ) {
                            ?>
                            <td class="td-field-select">
                                <input type="checkbox" class="field-select"
                                       data-merge_update_field_id="<?php echo esc_html( $field ) ?>"
                                       data-merge_field_id="<?php echo esc_html( $merge_field_id ) ?>"
                                       data-merge_field_type="<?php echo esc_html( $merge_field_type ) ?>">
                            </td>
                            <?php
                        } else {
                            ?>
                            <td class="td-field-select">
                                <input type="radio" class="field-select" name="<?php echo esc_html( $field ) ?>"
                                       data-merge_update_field_id="<?php echo esc_html( $field ) ?>"
                                       data-merge_field_id="<?php echo esc_html( $merge_field_id ) ?>"
                                       data-merge_field_type="<?php echo esc_html( $merge_field_type ) ?>">
                            </td>
                            <?php
                        }
                    }
                    ?>
                    <td class="td-field-input">
                        <input type="hidden" id="post_field_id"
                               value="<?php echo esc_html( $field ) ?>"/>
                        <input type="hidden" id="merge_field_id"
                               value="<?php echo esc_html( $merge_field_id ) ?>"/>
                        <input type="hidden" id="merge_field_type"
                               value="<?php echo esc_html( $merge_field_type ) ?>"/>
                        <input type="hidden" id="field_meta" value=""/>
                        <?php
                        // phpcs:disable
                        echo $rendered_field_html;
                        // phpcs:enable
                        ?>
                    </td>
                </tr>
                <?php
            }
        }
        ?>
        </tbody>
    </table>

    <?php
}

function list_merge_capable_field_types(): array {
    return [
        'communication_channel',
        'multi_select',
        'location_meta',
        'location',
        'tags',
        'connection'
    ];
}

// Render dt footer; which should contain the majority of scripts
get_footer();
