<?php
/**
 * Mapping Module Admin Section Elements
 * These elements are designed to be included into the admin areas of other themes and plugins.
 * Example:
 *      DT_Mapping_Module_Admin::instance()->population_metabox();
 */

if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

if ( ! class_exists( 'DT_Mapping_Module_Admin' ) ) {

    /**
     * Class DT_Mapping_Module_Admin
     */
    class DT_Mapping_Module_Admin
    {
        public $token = 'dt_mapping_module';

        // Singleton
        private static $_instance = null;

        public static function instance() {
            if ( is_null( self::$_instance ) ) {
                self::$_instance = new self();
            }

            return self::$_instance;
        }

        public $spinner;
        public $nonce;
        public $current_user_id;

        public function __construct() {
            /**
             * If allowed, this class will load into every admin the header scripts and rest endpoints. It is best
             * practice to add a filter to a config file in the plugin or theme using this module that filters for the
             * specific option page that these metaboxes are to be loaded, for the overall weight of the admin area.
             * Example:
             *  function dt_load_mapping_admin_class( $approved ) {
             *      global $pagenow;
             *      if ( 'my_admin_page' === $pagenow ) {
             *          return true;
             *      }
             *      return false;
             *  }
             *  add_filter('dt_mapping_module_admin_load_approved', 'dt_load_only_on_options_page' )
             */
            if ( ! apply_filters( 'dt_mapping_module_admin_load_approved', true ) ) {
                return; // this allows you to control what environments the admin loads.
            }

            add_action( "admin_menu", [ $this, 'register_menu' ] );
            add_action( 'admin_notices', [ $this, 'dt_locations_migration_admin_notice' ] );
            if ( is_admin() && isset( $_GET['page'] ) && 'dt_mapping_module' === $_GET['page'] ) {
                if ( function_exists( "spinner" ) ){
                    $this->spinner = spinner();
                }
                $this->nonce = wp_create_nonce( 'wp_rest' );
                $this->current_user_id = get_current_user_id();

                add_action( 'admin_head', [ $this, 'scripts' ] );
                add_action( "admin_enqueue_scripts", [ $this, 'enqueue_drilldown_script' ] );
            }
        }

        /**
         * Admin Page Elements
         */
        public function scripts() {
            ?>
            <script>
                let _ = window.lodash
                function send_update(data) {
                    let options = {
                        type: 'POST',
                        contentType: 'application/json; charset=utf-8',
                        dataType: 'json',
                        url: `<?php echo esc_url_raw( rest_url() )  ?>dt/v1/mapping_module/modify_location`,
                        beforeSend: xhr => {
                            xhr.setRequestHeader('X-WP-Nonce', '<?php echo esc_attr( wp_create_nonce( 'wp_rest' ) ) ?>');
                        }
                    }

                    if (data) {
                        options.data = JSON.stringify(data)
                    }

                    return jQuery.ajax(options)
                }

                function update(geonameid, value, key) {
                    if (value) {
                        jQuery('#button-' + geonameid).append(`<span><img src="<?php echo esc_url_raw( spinner() ) ?>" width="20px" /></span>`)

                        let update = send_update({key: key, value: value, geonameid: geonameid})

                        update.done(function (data) {
                            if (data) {
                                jQuery('#label-' + geonameid).html(`${value}`)
                                jQuery('#input-' + geonameid).val('')
                                jQuery('#button-' + geonameid + ' span').remove()
                            }
                        })
                    }
                }

                function reset(geonameid, key) {
                    jQuery('#reset-' + geonameid).append(`<span><img src="<?php echo esc_url_raw( spinner() ) ?>" width="20px" /></span>`)

                    let update = send_update({key: key, reset: true, geonameid: geonameid})

                    update.done(function (data) {
                        if (data.status === 'OK') {
                            jQuery('#label-' + geonameid).html(`${data.value}`)
                            jQuery('#input-' + geonameid).val('')
                            jQuery('#reset-' + geonameid + ' span').remove()
                        }
                    })
                    update.fail(function (e) {
                        jQuery('#reset-' + geonameid + ' span').remove()
                        console.log(e)
                    })
                }
            </script>
            <style>
                a.pointer {
                    cursor: pointer;
                }

                .drill_down {
                    margin-bottom: 0;
                    list-style-type: none;
                }

                .drill_down li {
                    display: inline;
                    margin-right: 3px;
                }

                .drill_down li select {
                    width: 150px;
                }
            </style>
            <?php
        }

        public function register_menu() {
            add_menu_page( __( 'Mapping', 'disciple_tools' ),
                __( 'Mapping', 'disciple_tools' ),
                'manage_dt',
                $this->token,
                [ $this, 'content' ],
                'dashicons-admin-site',
                7
            );
        }

        public function enqueue_drilldown_script( $hook ) {
            if ( 'admin.php' === $hook ) {
                return;
            }
            // Drill Down Tool
            wp_enqueue_script( 'mapping-drill-down', get_template_directory_uri() . '/dt-mapping/drill-down.js', [ 'jquery','lodash' ], '1.1' );
            wp_localize_script(
                'mapping-drill-down', 'mappingModule', array(
                    'mapping_module' => DT_Mapping_Module::instance()->localize_script(),
                )
            );
        }

        public function process_rest_edits( $params ) {
            if ( isset( $params['key'] ) && isset( $params['geonameid'] ) ) {
                $geonameid = (int) sanitize_key( wp_unslash( $params['geonameid'] ) );
                $value = false;
                if ( isset( $params['value'] ) ) {
                    $value = sanitize_text_field( wp_unslash( $params['value'] ) );
                }

                global $wpdb;

                switch ( $params['key'] ) {
                    case 'name':
                        if ( isset( $params['reset'] ) && $params['reset'] === true ) {
                            // get the original name for the geonameid
                            $wpdb->query( $wpdb->prepare( "
                                UPDATE $wpdb->dt_geonames
                                SET alt_name=name
                                WHERE geonameid = %d
                            ", $geonameid ) );

                            $name = $wpdb->get_var( $wpdb->prepare( "
                                SELECT alt_name as name FROM $wpdb->dt_geonames WHERE geonameid = %d
                            ", $geonameid ) );

                            return [
                                'status' => 'OK',
                                'value'  => $name,
                            ];
                        } elseif ( $value ) {
                            $update_id = $wpdb->update(
                                $wpdb->dt_geonames,
                                [ 'alt_name' => $value ],
                                [ 'geonameid' => $geonameid ],
                                [ '%s' ],
                                [ '%d' ]
                            );
                            if ( $update_id ) {
                                return true;
                            } else {
                                return new WP_Error( 'insert_fail', 'Failed to insert record' );
                            }
                        }
                        break;
                    case 'population':

                        if ( isset( $params['reset'] ) && $params['reset'] === true ) {
                            // get the original name for the geonameid
                            $wpdb->query( $wpdb->prepare( "
                                UPDATE $wpdb->dt_geonames
                                SET alt_population=NULL
                                WHERE geonameid = %d
                            ", $geonameid ) );

                            $population = $wpdb->get_var( $wpdb->prepare( "
                                SELECT population FROM $wpdb->dt_geonames WHERE geonameid = %d
                            ", $geonameid ) );

                            return [
                                'status' => 'OK',
                                'value'  => $population,
                            ];
                        } elseif ( $value ) {
                            $update_id = $wpdb->update(
                                $wpdb->dt_geonames,
                                [ 'alt_population' => $value ],
                                [ 'geonameid' => $geonameid ],
                                [ '%d' ],
                                [ '%d' ]
                            );
                            if ( $update_id ) {
                                return true;
                            } else {
                                return new WP_Error( 'update_fail', 'Failed to update population' );
                            }
                        }
                        break;

                    case 'sub_location':

                        if ( isset( $params['value']['name'] ) ) {
                            $name = sanitize_text_field( wp_unslash( $params['value']['name'] ) );
                        } else {
                            return new WP_Error( 'missing_param', 'Missing name parameter' );
                        }

                        if ( !empty( $params['value']['population'] ) ) {
                            $population = sanitize_text_field( wp_unslash( $params['value']['population'] ) );
                        } else {
                            $population = 0;
                        }

                        $custom_geonameid = $this->add_sublocation_under_geoname( $geonameid, $name, $population );

                        return [
                                'name' => $name,
                                'geonameid' => $custom_geonameid
                        ];
                        break;
                    default:
                        return new WP_Error( __METHOD__, 'Missing parameters.', [ 'status' => 400 ] );
                        break;
                }
            }

            return new WP_Error( __METHOD__, 'Missing parameters.', [ 'status' => 400 ] );
        }

        public function content() {

            if ( ! current_user_can( 'manage_dt' ) ) { // manage dt is a permission that is specific to Disciple Tools and allows admins, strategists and dispatchers into the wp-admin
                wp_die( esc_attr__( 'You do not have sufficient permissions to access this page.' ) );
            }
            if ( (int) get_option( 'dt_mapping_module_migration_lock', 0 ) ) {
                $last_migration_error = get_option( 'dt_mapping_module_migrate_last_error', false );
                if ( isset( $_POST['reset_mapping_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['reset_mapping_nonce'] ) ), 'reset_mapping' ) ) {
                    $this->migrations_reset_and_rerun();
                }
            }
            if ( (int) get_option( 'dt_mapping_module_migration_lock', 0 ) ) {
                ?>
                <h3>Something went wrong with the mapping system.</h3>
                <form method="post">
                    <?php wp_nonce_field( 'reset_mapping', 'reset_mapping_nonce' ) ?>
                    Retry setting up the mapping system:
                    <button type="submit" name="reset" value="1">Retry</button>
                </form>
                <br>
                <strong>Error message:</strong>

                <?php if ( !empty( $last_migration_error ) ) {
                    if ( isset( $last_migration_error["message"] ) ) : ?>
                        <p>Cannot migrate, as migration lock is held. This is the last error: <strong><?php echo esc_html( $last_migration_error["message"] ); ?></strong></p>
                    <?php else :
                        var_dump( "Cannot migrate, as migration lock is held. This is the previous stored migration error: " . var_export( $last_migration_error, true ) );
                    endif;
                }
                die();
            }

            if ( isset( $_GET['tab'] ) ) {
                $tab = sanitize_key( wp_unslash( $_GET['tab'] ) );
            } else {
                $tab = 'general';
            }

            $link = 'admin.php?page=' . $this->token . '&tab=';

            ?>
            <div class="wrap">
                <h2><?php esc_attr_e( 'Mapping', 'disciple_tools' ) ?></h2>
                <h2 class="nav-tab-wrapper">

                    <!-- General Tab -->
                    <a href="<?php echo esc_attr( $link ) . 'general' ?>" class="nav-tab
                        <?php echo esc_attr( ( $tab == 'general' || ! isset( $tab ) ) ? 'nav-tab-active' : '' ); ?>">
                        <?php esc_attr_e( 'General Settings', 'disciple_tools' ) ?>
                    </a>

                    <!-- Starting Map -->
                    <a href="<?php echo esc_attr( $link ) . 'focus' ?>" class="nav-tab
                        <?php echo esc_attr( ( $tab == 'focus' ) ? 'nav-tab-active' : '' ); ?>">
                        <?php esc_attr_e( 'Mapping Focus', 'disciple_tools' ) ?>
                    </a>
                        <!-- Location Migration Tab -->
<!--                    --><?php //if ( !get_option( "dt_locations_migrated_to_geonames" ) ) : ?>
                        <a href="<?php echo esc_attr( $link ) . 'location-migration' ?>" class="nav-tab
                            <?php echo esc_attr( ( $tab == 'location-migration' || ! isset( $tab ) ) ? 'nav-tab-active' : '' ); ?>">
                            <?php esc_attr_e( 'Migrating From Locations', 'disciple_tools' ) ?>
                        </a>
<!--                    --><?php //endif; ?>
                    <!-- Polygon -->
                    <a href="<?php echo esc_attr( $link ) . 'polygons' ?>" class="nav-tab
                        <?php echo esc_attr( ( $tab == 'polygons' ) ? 'nav-tab-active' : '' ); ?>">
                        <?php esc_attr_e( 'Polygons', 'disciple_tools' ) ?>
                    </a>
                    <!-- Geocoding -->
                    <a href="<?php echo esc_attr( $link ) . 'geocoding' ?>" class="nav-tab
                        <?php echo esc_attr( ( $tab == 'geocoding' ) ? 'nav-tab-active' : '' ); ?>">
                        <?php esc_attr_e( 'Geocoding', 'disciple_tools' ) ?>
                    </a>
                    <!-- Names Tab -->
                    <a href="<?php echo esc_attr( $link ) . 'names' ?>" class="nav-tab
                        <?php echo esc_attr( ( $tab == 'names' ) ? 'nav-tab-active' : '' ); ?>">
                        <?php esc_attr_e( 'Names and Geoname IDs', 'disciple_tools' ) ?>
                    </a>
                    <!-- Population Tab -->
                    <a href="<?php echo esc_attr( $link ) . 'population' ?>" class="nav-tab
                        <?php echo esc_attr( ( $tab == 'population' ) ? 'nav-tab-active' : '' ); ?>">
                        <?php esc_attr_e( 'Population', 'disciple_tools' ) ?>
                    </a>
                    <!-- Add Sub-Locations -->
                    <a href="<?php echo esc_attr( $link ) . 'sub_locations' ?>" class="nav-tab
                        <?php echo esc_attr( ( $tab == 'sub_locations' ) ? 'nav-tab-active' : '' ); ?>">
                        <?php esc_attr_e( 'Sub-Locations', 'disciple_tools' ) ?>
                    </a>
                    <!-- Add Migration -->
                    <a href="<?php echo esc_attr( $link ) . 'migration' ?>" class="nav-tab
                        <?php echo esc_attr( ( $tab == 'migration' ) ? 'nav-tab-active' : '' ); ?>">
                        <?php esc_attr_e( 'Migration', 'disciple_tools' ) ?>
                    </a>
                    <!-- Add Locations Explorer -->
                    <a href="<?php echo esc_attr( $link ) . 'explore' ?>" class="nav-tab
                        <?php echo esc_attr( ( $tab == 'explore' ) ? 'nav-tab-active' : '' ); ?>">
                        <?php esc_attr_e( 'Credits', 'disciple_tools' ) ?>
                    </a>

                </h2>

                <?php
                switch ( $tab ) {
                    case "general":
                        $this->general_tab();
                        break;
                    case "location-migration":
                        $this->migration_from_locations_tab();
                        break;
                    case "focus":
                        $this->focus_tab();
                        break;
                    case "polygons":
                        $this->polygon_tab();
                        break;
                    case "geocoding":
                        $this->geocoding_tab();
                        break;
                    case "names":
                        $this->alternate_name_tab();
                        break;
                    case "population":
                        $this->population_tab();
                        break;
                    case "sub_locations":
                        $this->sub_locations_tab();
                        break;
                    case "migration":
                        $this->migration_tab();
                        break;
                    case "explore":
                        $this->explore_tab();
                        break;
                    default:
                        break;
                }
                ?>
            </div><!-- End wrap -->
            <?php
        }

        public function general_tab() {
            ?>
            <div class="wrap">
                <div id="poststuff">
                    <div id="post-body" class="metabox-holder columns-2">
                        <div id="post-body-content">
                            <!-- Main Column -->

                            <?php $this->summary_metabox() ?>


                            <!-- End Main Column -->
                        </div><!-- end post-body-content -->
                        <div id="postbox-container-1" class="postbox-container">
                            <!-- Right Column -->

                            <!-- End Right Column -->
                        </div><!-- postbox-container 1 -->
                        <div id="postbox-container-2" class="postbox-container">
                        </div><!-- postbox-container 2 -->
                    </div><!-- post-body meta box container -->
                </div><!--poststuff end -->
            </div><!-- wrap end -->
            <?php
        }

        public function migration_from_locations_tab() {
            ?>
            <div class="wrap">
                <div id="poststuff">
                    <div id="post-body" class="metabox-holder columns-2">
                        <div id="post-body-content">
                            <!-- Main Column -->

                            <?php $this->migration_from_locations_meta_box() ?>


                            <!-- End Main Column -->
                        </div><!-- end post-body-content -->
                        <div id="postbox-container-1" class="postbox-container">
                            <!-- Right Column -->

                            <!-- End Right Column -->
                        </div><!-- postbox-container 1 -->
                        <div id="postbox-container-2" class="postbox-container">
                        </div><!-- postbox-container 2 -->
                    </div><!-- post-body meta box container -->
                </div><!--poststuff end -->
            </div><!-- wrap end -->
            <?php
        }

        public function focus_tab() {
            ?>
            <div class="wrap">
                <div id="poststuff">
                    <div id="post-body" class="metabox-holder columns-2">
                        <div id="post-body-content">
                            <!-- Main Column -->

                            <?php $this->starting_map_level_metabox(); ?>

                            <!-- End Main Column -->
                        </div><!-- end post-body-content -->
                        <div id="postbox-container-1" class="postbox-container">
                            <!-- Right Column -->

                            <?php $this->mapping_focus_instructions_metabox() ?>

                            <!-- End Right Column -->
                        </div><!-- postbox-container 1 -->
                        <div id="postbox-container-2" class="postbox-container">
                        </div><!-- postbox-container 2 -->
                    </div><!-- post-body meta box container -->
                </div><!--poststuff end -->
            </div><!-- wrap end -->
            <?php
        }

        public function polygon_tab() {
            ?>
            <div class="wrap">
                <div id="poststuff">
                    <div id="post-body" class="metabox-holder columns-1">
                        <div id="post-body-content">
                            <!-- Main Column -->

                            <?php $this->set_polygon_mirror_metabox(); ?>

                            <!-- End Main Column -->
                        </div><!-- end post-body-content -->
                        <div id="postbox-container-1" class="postbox-container">
                            <!-- Right Column -->

                            <!-- End Right Column -->
                        </div><!-- postbox-container 1 -->
                        <div id="postbox-container-2" class="postbox-container">
                        </div><!-- postbox-container 2 -->
                    </div><!-- post-body meta box container -->
                </div><!--poststuff end -->
            </div><!-- wrap end -->
            <?php
        }

        public function geocoding_tab() {
            ?>
            <div class="wrap">
                <div id="poststuff">
                    <div id="post-body" class="metabox-holder columns-1">
                        <div id="post-body-content">
                            <!-- Main Column -->

                            <?php $this->set_geocoding_source_metabox(); ?>

                            <!-- End Main Column -->
                        </div><!-- end post-body-content -->
                        <div id="postbox-container-1" class="postbox-container">
                            <!-- Right Column -->

                            <!-- End Right Column -->
                        </div><!-- postbox-container 1 -->
                        <div id="postbox-container-2" class="postbox-container">
                        </div><!-- postbox-container 2 -->
                    </div><!-- post-body meta box container -->
                </div><!--poststuff end -->
            </div><!-- wrap end -->
            <?php
        }

        public function alternate_name_tab() {
            ?>
            <div class="wrap">
                <div id="poststuff">
                    <div id="post-body" class="metabox-holder columns-1">
                        <div id="post-body-content">
                            <!-- Main Column -->

                            <?php $this->alternate_name_metabox() ?>

                            <!-- End Main Column -->
                        </div><!-- end post-body-content -->
                        <div id="postbox-container-1" class="postbox-container">
                            <!-- Right Column -->

                            <!-- End Right Column -->
                        </div><!-- postbox-container 1 -->
                        <div id="postbox-container-2" class="postbox-container">
                        </div><!-- postbox-container 2 -->
                    </div><!-- post-body meta box container -->
                </div><!--poststuff end -->
            </div><!-- wrap end -->
            <?php
        }

        public function population_tab() {
            ?>
            <div class="wrap">
                <div id="poststuff">
                    <div id="post-body" class="metabox-holder columns-1">
                        <div id="post-body-content">
                            <!-- Main Column -->

                            <?php $this->global_population_division_metabox(); ?>

                            <?php $this->edit_populations_metabox(); ?>

                            <!-- End Main Column -->
                        </div><!-- end post-body-content -->
                        <div id="postbox-container-1" class="postbox-container">
                            <!-- Right Column -->

                            <!-- End Right Column -->
                        </div><!-- postbox-container 1 -->
                        <div id="postbox-container-2" class="postbox-container">
                        </div><!-- postbox-container 2 -->
                    </div><!-- post-body meta box container -->
                </div><!--poststuff end -->
            </div><!-- wrap end -->
            <?php
        }

        public function sub_locations_tab() {
            ?>
            <div class="wrap">
                <div id="poststuff">
                    <div id="post-body" class="metabox-holder columns-1">
                        <div id="post-body-content">
                            <!-- Main Column -->

                            <?php $this->sub_locations_metabox() ?>

                            <!-- End Main Column -->
                        </div><!-- end post-body-content -->
                        <div id="postbox-container-1" class="postbox-container">
                            <!-- Right Column -->

                            <!-- End Right Column -->
                        </div><!-- postbox-container 1 -->
                        <div id="postbox-container-2" class="postbox-container">
                        </div><!-- postbox-container 2 -->
                    </div><!-- post-body meta box container -->
                </div><!--poststuff end -->
            </div><!-- wrap end -->
            <?php
        }

        public function migration_tab() {
            ?>
            <div class="wrap">
                <div id="poststuff">
                    <div id="post-body" class="metabox-holder columns-1">
                        <div id="post-body-content">
                            <!-- Main Column -->

                            <?php $this->migration_status_metabox() ?>
                            <br>
                            <?php $this->migration_rebuild_geonames() ?>

                            <!-- End Main Column -->
                        </div><!-- end post-body-content -->
                        <div id="postbox-container-1" class="postbox-container">
                            <!-- Right Column -->

                            <!-- End Right Column -->
                        </div><!-- postbox-container 1 -->
                        <div id="postbox-container-2" class="postbox-container">
                        </div><!-- postbox-container 2 -->
                    </div><!-- post-body meta box container -->
                </div><!--poststuff end -->
            </div><!-- wrap end -->
            <?php
        }

        public function explore_tab() {
            ?>
            <div class="wrap">
                <div id="poststuff">
                    <div id="post-body" class="metabox-holder columns-1">
                        <div id="post-body-content">

                            <!-- Main Column -->
                            <table class="widefat striped">
                                <thead>
                                    <tr><th>Mapping Data Credits</th></tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <p><strong><a href="https://github.com/DiscipleTools/saturation-grid-project">Saturation Grid Project</a></strong></p>
                                            <p>
                                                The Saturation Grid Project hopes to offer a cross-referenced grid for reporting on movement progress across the planet,
                                                while at the same time is location sensitive for activity in dangerous or anti-christian locations and compliance with
                                                increasing privacy laws like GDPR.</p>
                                            <p>
                                                The project serves to support the vision of consistently tracking church planting movement efforts globally in a way
                                                that allows networks and different organizations to share location sensitive reports to visualize and respond to
                                                areas of disciple making movement and areas where there is no disciple making movement.
                                            </p>
                                            <p>
                                                The project offers a global grid of unique location ids for countries, states, and counties,
                                                longitude/latitude, populations for those administrative areas, and the supporting geojson polygon files for
                                                lightweight application display.
                                            </p>
                                            <p><em>This is an open source project, so if something is missing that matters to you, help us add it!</em></p>
                                            <p>
                                                <a onclick="show_totals()">Show Grid Totals</a><br>
                                                <a onclick="show_list()">Show Grid Hierarchy</a><br>
                                                <a onclick="show_license()">Show Grid License</a><br>
                                            </p>

                                            <div id="hierarchy_list" style="display:none; padding: 15px; border: solid 2px #ccc;">
                                                <img src="<?php echo esc_html( spinner() ) ?>" width="30px" />
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <p><strong><a href="https://www.geonames.org/">Geonames</a></strong></p>
                                            <p>The GeoNames database contains over 25,000,000 geographical names corresponding
                                                to over 11,800,000 unique features.[1] All features are categorized into one
                                                of nine feature classes and further subcategorized into one of 645 feature codes.
                                                Beyond names of places in various languages, data stored include latitude, longitude,
                                                elevation, population, administrative subdivision and postal codes. All
                                                coordinates use the World Geodetic System 1984 (WGS84).
                                                <a href="https://en.wikipedia.org/wiki/GeoNames">Wikipedia Article</a>
                                            </p>
                                            <p>This work is licensed under a Creative Commons Attribution 4.0 License,
                                                see https://creativecommons.org/licenses/by/4.0/</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <p><strong><a href="https://www.openstreetmap.org">OpenStreetMap</a></strong></p>
                                            <p>OpenStreetMap (OSM) is a collaborative project to create a free editable
                                                map of the world. Rather than the map itself, the data generated by the
                                                project is considered its primary output. The creation and growth of OSM
                                                has been motivated by restrictions on use or availability of map information
                                                across much of the world, and the advent of inexpensive portable satellite
                                                navigation devices.[6] OSM is considered a prominent example of volunteered
                                                geographic information.
                                                <a href="https://en.wikipedia.org/wiki/OpenStreetMap">Wikipedia Article</a>
                                            </p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <!-- End Main Column -->

                            <div id="hierarchy_list" style="display:none; padding: 15px; border: solid 2px #ccc;">
                                <img src="<?php echo esc_html( spinner() ) ?>" width="30px" />
                            </div>

                        </div><!-- end post-body-content -->
                    </div><!-- post-body meta box container -->
                </div><!--poststuff end -->
            </div><!-- wrap end -->

            <script>
                function show_license() {
                    let hl = jQuery("#hierarchy_list")
                    hl.show().empty().html('<img src="<?php echo esc_html( spinner() ) ?>" width="30px" />')
                    jQuery.ajax({
                        url: "https://raw.githubusercontent.com/DiscipleTools/saturation-grid-project/master/LICENSE",
                        dataType: "text",
                        success: function( data ) {
                            hl.html( '<br clear="all"><pre>\n' + data + '</pre>')
                        }
                    })
                }
                function show_list() {
                    let hl = jQuery("#hierarchy_list")
                    hl.show().empty().html('<img src="<?php echo esc_html( spinner() ) ?>" width="30px" />')
                    jQuery.ajax({
                        url: "https://raw.githubusercontent.com/DiscipleTools/saturation-grid-project/master/hierarchy.txt",
                        dataType: "text",
                        success: function( data ) {
                            hl.html( '<br clear="all"><pre>\n' + data + '</pre>')
                        }
                    })
                }
                function show_totals() {
                    let hl = jQuery("#hierarchy_list")
                    hl.show().empty().html('<img src="<?php echo esc_html( spinner() ) ?>" width="30px" />')
                    jQuery.ajax({
                        url: "https://raw.githubusercontent.com/DiscipleTools/saturation-grid-project/master/totals.txt",
                        dataType: "text",
                        success: function( data ) {
                            hl.html( '<br clear="all"><pre>\n' + data + '</pre>')
                        }
                    })
                }
            </script>

            <?php
        }

        /**
         * Admin Page Metaboxes
         */

        public function summary_metabox() {
            ?>
            <!-- Box -->
            <table class="widefat striped">
                <thead>
                <tr>
                <th>Name</th>
                <th>Current Setting</th>
                <th></th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>
                        Mapping Focus
                    </td>
                    <td>
                        <?php
                        $list = DT_Mapping_Module::instance()->default_map_short_list();
                        if ( is_array( $list ) ) {
                            foreach ( $list as $key => $value ) {
                                echo esc_html( $value ) . '<br>';
                            }
                        }
                        ?>
                    </td>
                    <td>
                        <a href="admin.php?page=dt_mapping_module&tab=focus">Edit</a>
                    </td>
                </tr>
                <tr>
                    <td>
                        Polygon Mirror Source
                    </td>
                    <td>
                        <?php
                        $mirror = dt_get_saturation_mapping_mirror();
                        echo esc_attr( $mirror['label'] ) ?? '';
                        ?>
                    </td>
                    <td>
                        <a href="admin.php?page=dt_mapping_module&tab=polygons">Edit</a>
                    </td>
                </tr>
                <tr>
                    <td>
                        Geocoding Source
                    </td>
                    <td>
                    </td>
                    <td>
                        <a href="admin.php?page=dt_mapping_module&tab=geocoding">Edit</a>
                    </td>
                </tr>
                <tr>
                    <td>
                        Population
                    </td>
                    <td>
                        <?php
                        echo esc_attr( get_option( 'dt_mapping_module_population' ) );
                        ?>
                    </td>
                    <td>
                        <a href="admin.php?page=dt_mapping_module&tab=population">Edit</a>
                    </td>
                </tr>
                </tbody>
            </table>
            <?php
        }

        public function migration_from_locations_meta_box(){
            if ( isset( $_POST["location_migrate_nonce"] ) && wp_verify_nonce( sanitize_key( $_POST['location_migrate_nonce'] ), 'save' ) ) {
                if ( isset( $_POST["run-migration"], $_POST["selected_geonames"] ) ){
                    $select_geonames = dt_sanitize_array_html( $_POST["selected_geonames"] ); //phpcs:ignore
                    $saved_for_migration = get_option( "dt_mapping_migration_list", [] );
                    foreach ( $select_geonames as $location_id => $migration_values ){
                        if ( !empty( $location_id ) && !empty( $migration_values["migration_type"] ) ) {
                            $location_id = sanitize_text_field( wp_unslash( $location_id ) );
                            $selected_geoname = sanitize_text_field( wp_unslash( $migration_values["geoid"] ) );
                            $migration_type = sanitize_text_field( wp_unslash( $migration_values["migration_type"] ) );
                            $location = get_post( $location_id );
                            if ( empty( $selected_geoname )){
                                $selected_geoname = '6295630';
                            }
                            $geoname = Disciple_Tools_Mapping_Queries::get_by_geonameid( $selected_geoname );
                            if ( $migration_type === "sublocation" ){
                                $selected_geoname = $this->add_sublocation_under_geoname( $selected_geoname, $location->post_title, 0 );
                            }
                            $this->convert_location_to_geoname( $location_id, $selected_geoname );

                            $message = $migration_type === "convert" ?
                                "Converted $location->post_title to " . $geoname["name"] :
                                "Created $location->post_title as sub-location under " . $geoname["name"];
                            ?>
                            <div class="notice notice-success is-dismissible">
                                <p>Successfully ran action: <?php echo esc_html( $message )?></p>
                            </div>
                            <?php
                            $saved_for_migration[$location_id] = [
                                "message" => $message,
                                "migration_type" => $migration_type,
                                "location_id" => $location_id,
                                "selected_geoname" => $selected_geoname
                            ];
                        }
                    }
                    update_option( "dt_mapping_migration_list", $saved_for_migration, false );
                }
            }

            global $wpdb;
            $locations_with_records = $wpdb->get_results( "
                SELECT DISTINCT( posts.ID ), post_title, post_parent, COUNT( p2p.p2p_from ) as count
                FROM $wpdb->posts as posts
                JOIN $wpdb->p2p as p2p on (p2p.p2p_to = posts.ID)
                WHERE posts.post_type = 'locations' 
                GROUP BY posts.ID
            ", ARRAY_A );
            $saved_for_migration = get_option( "dt_mapping_migration_list", [] );
            if ( sizeof( $locations_with_records ) === 0 ) {
                $migration_done = get_option( "dt_locations_migrated_to_geonames", false );
                if ( !$migration_done ){
                    $this->migrate_user_filters_to_geonames();
                    update_option( "dt_locations_migrated_to_geonames", true );
                }
            } else {
                ?>

                <h1>About</h1>
                <p>Thank you for completing this important step in using D.T.</p>
                <p>This tool is to help you migrate from the old locations system, to the new one that uses <a target="_blank" href="https://www.geonames.org/about.html">GeoNames</a>  as it's base. GeoNames is a free database of countries and regions and will help us achieve better collaborate across instances. </p>
                <p>You may wish to select a <a href="<?php echo esc_html( admin_url( 'admin.php?page=dt_mapping_module&tab=focus' ) ) ?>">mapping focus</a> to narrow the options given.</p>
                <p>Click <a target="_blank" href="https://disciple-tools.readthedocs.io/en/latest/Disciple_Tools_Theme/getting_started/admin.html#mapping">here</a> for a detailed explanation on the locations system and instructions on how to use this tool</p>
                <h1>Instructions</h1>
                <p>1. Select the corresponding GeoNames location for the old location. If you choose a wrong location, click "World" to undo it.</p>
                <p>2. Then click click one of the two options:</p>
                <ul style="list-style: disc; padding-inline-start: 40px">
                    <li><strong style="color: green;" >Convert (recommended)</strong> means the selected new location is the same as the old location.</li>
                    <li><strong style="color: orange;">Create as a sub-location</strong> means that the old location is found within the selected new location.</li>
                </ul>
                <p>3. Click the "Run migration" button. Hint: You can select a few location and run the migration.</p>

                <form method="post" action="">
                    <?php wp_nonce_field( 'save', 'location_migrate_nonce', true, true ) ?>
                    <h3>Locations to Migrate ( <?php echo esc_html( sizeof( $locations_with_records ) ) ?> )</h3>

                    <p>
                        <button style="background-color: red; color: white; border-radius: 5px;" type="submit" class="button" name="run-migration">
                            <strong>Run migration</strong>
                        </button>
                        <strong>Careful, this cannot be undone.</strong>
                    </p>

                    <table class="widefat striped">
                        <thead>
                        <tr>
                            <th>Old Location Name</th>
                            <th>Select a Location</th>
                            <th>Select option</th>
                            <th>Selected Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ( $locations_with_records as $location ) : ?>
                            <tr>
                                <td> <?php echo esc_html( $location["post_title"] ) ?>
                                    ( <?php echo esc_html( $location["count"] ) ?> )
                                </td>
                                <td id="<?php echo esc_html( $location["ID"] ) ?>_sublocation" class="to-location">
                                    <input name="selected_geonames[<?php echo esc_html( $location["ID"] ) ?>][geoid]" class="convert-input" type="hidden">
                                    <div class="drilldown">
                                        <?php DT_Mapping_Module::instance()->drill_down_widget( esc_html( $location["ID"] ) . "_sublocation .drilldown" ) ?>
                                    </div>
                                </td>
                                <td id="<?php echo esc_html( $location["ID"] ) ?>_buttons">
                                    <select name="selected_geonames[<?php echo esc_html( $location["ID"] ) ?>][migration_type]" data-location_id="<?php echo esc_html( $location["ID"] ) ?>" class="migration-type">
                                        <option></option>
                                        <option value="convert">Convert (recommended) </option>
                                        <option value="sublocation">Create as a sub-location</option>
                                    </select>
                                </td>
                                <td id="<?php echo esc_html( $location["ID"] ) ?>_actions">
                                    <span class="convert" style="display: none;"><strong style="color: green;">Convert</strong> <?php echo esc_html( $location["post_title"] ) ?> to <span class="selected-geoname-label">World</span></span>
                                    <span class="sublocation" style="display: none;"><strong style="color: orange">Create</strong> <?php echo esc_html( $location["post_title"] ) ?> <strong style="color: orange">as a sub-location</strong> under <span class="selected-geoname-label">World</span></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <p>
                        <button style="background-color: red; color: white; border-radius: 5px;" type="submit" class="button" name="run-migration">
                            <strong>Run migration</strong>
                        </button>
                        <strong>Careful, this cannot be undone.</strong>
                    </p>
                </form>
                <script>
                    jQuery(".to-location").each((a, b)=>{
                        let id = jQuery(b).attr('id')
                        window.DRILLDOWN[`${ id } .drilldown`] = function (geonameid, label) {
                            jQuery(`#${id} .convert-input`).val(geonameid)
                            console.log(id);
                            jQuery(`#${id.replace("sublocation", "actions")} .selected-geoname-label`).text(label)
                        }
                    })
                    jQuery('.migration-type').on( "change", function () {
                        let val = this.value
                        let location_id = jQuery(this).data('location_id')
                        jQuery(`#${location_id}_actions .${ val === 'convert' ? 'sublocation' : 'convert' }`).hide()
                        jQuery(`#${location_id}_actions .${val}`).show()
                    })
                </script>
            <?php } ?>
            <h3>Migrated Locations ( <?php echo esc_html( sizeof( $saved_for_migration ) ) ?>)</h3>
            <ul style="list-style: disc">
                <?php foreach ( $saved_for_migration as $location_id => $migration_values ) : ?>
                    <li style="margin-inline-start: 40px"><?php echo esc_html( $migration_values["message"] ) ?></li>
                <?php endforeach; ?>
            </ul>
            <?php
        }

        public function migration_status_metabox() {
            if ( isset( $_POST['unlock'] )
                && ( isset( $_POST['_wpnonce'] )
                    && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'migration_status' . get_current_user_id() ) ) ) {

                delete_option( 'dt_mapping_module_migration_number' );
                delete_option( 'dt_mapping_module_migration_lock' );
                delete_option( 'dt_mapping_module_migrate_last_error' );
            }

            ?>
            <!-- Box -->
            <form method="post">
                <?php wp_nonce_field( 'migration_status' . get_current_user_id() ); ?>
                <table class="widefat striped">
                    <thead>
                    <tr><th>Migration Status</th></tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td>
                            Migrations
                            Available: <?php echo esc_attr( DT_Mapping_Module_Migration_Engine::$migration_number ) ?>
                            <br>
                            Current
                            Migration: <?php echo esc_attr( get_option( 'dt_mapping_module_migration_number', true ) ) ?>
                            <br>
                            Locked Status: <?php
                            if ( get_option( 'dt_mapping_module_migration_lock', true ) ) {
                                ?>
                                Locked!
                                <a onclick="jQuery('#error-message-raw').toggle();" class="alert">Show error message</a>
                                <div style="display:none;" id="error-message-raw">
                                    <hr>
                                    <?php echo '<pre>';
                                    print_r( get_option( 'dt_mapping_module_migrate_last_error', true ) );
                                    echo '</pre>'; ?>
                                </div>
                                <hr>
                                <p>
                                    <button type="submit" name="unlock" value="1">Unlock and Rerun Migrations</button>
                                </p>
                                <?php
                            } else {
                                echo 'Not Locked';
                            }
                            ?><br>
                            Current Geoname
                            Records: <?php echo esc_attr( Disciple_Tools_Mapping_Queries::get_total_record_count_in_geonames_database() ) ?>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </form>
            <?php

        }

        public function global_population_division_metabox() {
            // process post action
            if ( isset( $_POST['population_division'] )
                && ( isset( $_POST['_wpnonce'] )
                    && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'population_division' . get_current_user_id() ) ) ) {
                $new = (int) sanitize_text_field( wp_unslash( $_POST['population_division'] ) );
                update_option( 'dt_mapping_module_population', $new, false );
            }
            $population_division = get_option( 'dt_mapping_module_population' );
            if ( empty( $population_division ) ) {
                update_option( 'dt_mapping_module_population', 5000, false );
                $population_division = 5000;
            }
            ?>
            <!-- Box -->
            <form method="post">
                <table class="widefat striped">
                    <thead>
                    <tr><th>Groups Per Population</th></tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td>
                            <?php wp_nonce_field( 'population_division' . get_current_user_id() ); ?>
                            <label for="population_division">Size of population for each group: </label>
                            <input type="number" class="text" id="population_division" name="population_division"
                                   value="<?php echo esc_attr( $population_division ); ?>"/>
                            <button type="submit" class="button">Update</button>
                            <p><em>Default is a population of 5,000 for each group. This must be a number and must not
                                    be blank. </em></p>

                        </td>
                    </tr>
                    </tbody>
                </table>
            </form>
            <br>
            <!-- End Box -->
            <?php
        }

        public function edit_populations_metabox() {
            ?>
            <table class="widefat striped">
                <thead>
                    <tr><th>Select Population List to Edit</th></tr>
                </thead>
                <tbody>
                <tr>
                    <td id="population_edit"></td>
                </tr>
                </tbody>
            </table>

            <table class="widefat striped">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Population</th>
                        <th>New Population (no commas)</th>
                        <th></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="list_results">
                </tbody>
            </table>

            <script>
                window.DRILLDOWNDATA.settings.hide_final_drill_down = false
                window.DRILLDOWN.get_drill_down('population_edit')
                window.DRILLDOWN.population_edit = function (geonameid) {
                    let list_results = jQuery('#list_results')
                    let div = 'list_results'

                    // Find data source before build
                    if ( geonameid === 'top_map_level' ) {
                        let default_map_settings = DRILLDOWNDATA.settings.default_map_settings

                        // Initialize Location Data
                        let map_data = DRILLDOWNDATA.data[default_map_settings.parent]
                        if ( map_data === undefined ) {
                            console.log('error getting map_data')
                            return;
                        }

                        build_list( div, map_data )
                    }
                    else if ( DRILLDOWNDATA.data[geonameid] === undefined ) {
                        let rest = DRILLDOWNDATA.settings.endpoints.get_map_by_geonameid_endpoint

                        jQuery.ajax({
                            type: rest.method,
                            contentType: "application/json; charset=utf-8",
                            data: JSON.stringify( { 'geonameid': geonameid } ),
                            dataType: "json",
                            url: DRILLDOWNDATA.settings.root + rest.namespace + rest.route,
                            beforeSend: function(xhr) {
                                xhr.setRequestHeader('X-WP-Nonce', rest.nonce );
                            },
                        })
                            .done( function( response ) {
                                DRILLDOWNDATA.data[geonameid] = response
                                build_list( div, DRILLDOWNDATA.data[geonameid] )
                            })
                            .fail(function (err) {
                                console.log("error")
                                console.log(err)
                                DRILLDOWN.hide_spinner()
                            })

                    } else {
                        build_list( div, DRILLDOWNDATA.data[geonameid] )
                    }

                    function build_list( div, map_data ) {
                        list_results.empty()
                        jQuery.each( map_data.children, function (i, v) {
                            list_results.append(`<tr>
                                <td>${_.escape( v.name )}</td>
                                <td id="label-${_.escape( v.geonameid )}">${_.escape( v.population_formatted )}</td>
                                <td><input type="number" id="input-${_.escape( v.geonameid )}" value=""></td>
                                <td id="button-${_.escape( v.geonameid )}"><a class="button" onclick="update( ${_.escape( v.geonameid )}, jQuery('#input-'+${_.escape( v.geonameid )}).val(), 'population' )">Update</a></td>
                                <td id="reset-${_.escape( v.geonameid )}"><a class="button" onclick="reset( ${_.escape( v.geonameid )}, 'population' )">Reset</a></td>
                            </tr>`)
                        })
                    }
                }

            </script>

            <?php
        }

        public function alternate_name_metabox() {
            ?>
            <table class="widefat striped">
                <thead>
                    <tr><th>Edit Default Location Names</th></tr>
                </thead>
                <tbody>
                <tr>
                    <td id="name_select"></td>
                </tr>
                </tbody>
            </table>

            <table class="widefat striped">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>New Name</th>
                        <th></th>
                        <th></th>
                        <th>ID</th>
                    </tr>
                </thead>
                <tbody id="list_results"></tbody>
            </table>

            <script>
                window.DRILLDOWNDATA.settings.hide_final_drill_down = false
                window.DRILLDOWN.get_drill_down('name_select')
                window.DRILLDOWN.name_select = function (geonameid) {
                    let list_results = jQuery('#list_results')
                    let div = 'list_results'

                    // Find data source before build
                    if (geonameid === 'top_map_level') {
                        let default_map_settings = DRILLDOWNDATA.settings.default_map_settings

                        // Initialize Location Data
                        let map_data = DRILLDOWNDATA.data[default_map_settings.parent]
                        if (map_data === undefined) {
                            console.log('error getting map_data')
                            return;
                        }

                        build_list(div, map_data)
                    }
                    else if (DRILLDOWNDATA.data[geonameid] === undefined) {
                        let rest = DRILLDOWNDATA.settings.endpoints.get_map_by_geonameid_endpoint

                        jQuery.ajax({
                            type: rest.method,
                            contentType: "application/json; charset=utf-8",
                            data: JSON.stringify({'geonameid': geonameid}),
                            dataType: "json",
                            url: DRILLDOWNDATA.settings.root + rest.namespace + rest.route,
                            beforeSend: function (xhr) {
                                xhr.setRequestHeader('X-WP-Nonce', rest.nonce);
                            },
                        })
                            .done(function (response) {
                                DRILLDOWNDATA.data[geonameid] = response
                                build_list(div, DRILLDOWNDATA.data[geonameid])
                            })
                            .fail(function (err) {
                                console.log("error")
                                console.log(err)
                                DRILLDOWN.hide_spinner()
                            })

                    } else {
                        build_list(div, DRILLDOWNDATA.data[geonameid])
                    }

                    function build_list(div, map_data) {
                        list_results.empty()
                        jQuery.each(map_data.children, function (i, v) {
                            list_results.append(`<tr>
                                <td id="label-${_.escape( v.geonameid )}">${_.escape( v.name )}</td>
                                <td><input type="text" id="input-${_.escape( v.geonameid )}" value=""></td>
                                <td id="button-${_.escape( v.geonameid )}"><a class="button" onclick="update( ${_.escape( v.geonameid )}, jQuery('#input-'+${_.escape( v.geonameid )}).val(), 'name' )">Update</a></td>
                                <td id="reset-${_.escape( v.geonameid )}"><a class="button" onclick="reset( ${_.escape( v.geonameid )}, 'name' )">Reset</a></td>
                                <td>${_.escape( v.geonameid )}</td>
                            </tr>`)
                        })
                    }
                }
            </script>

            <?php

        }

        public function sub_locations_metabox() {
            ?>
            <table class="widefat striped">
                <thead>
                <tr><th>Select the Location</th></tr>
                </thead>
                <tbody>
                <tr>
                    <td id="sublocation"></td>
                </tr>
                </tbody>
            </table>
            <table class="widefat striped" style="display:none;" id="current_subs">
                <thead>
                <tr>
                <th>Current Sub-Locations (use these if possible):</th>
                <th style="width:20px;"></th>
                </tr>
                </thead>
                <tbody id="other_list">
                </tbody>
            </table>
            <br>
            <table class="widefat striped">
                <tbody id="list_results"></tbody>
            </table>

            <script>
                jQuery(document).on('click', '.open_next_drilldown', function(){
                    let gnid = jQuery(this).data('geonameid')
                    DRILLDOWN.get_drill_down( 'sublocation', gnid  );

                })

                window.DRILLDOWNDATA.settings.hide_final_drill_down = false
                window.DRILLDOWN.get_drill_down('sublocation')
                window.DRILLDOWN.sublocation = function (geonameid) {

                    let list_results = jQuery('#list_results')
                    let div = 'list_results'
                    let current_subs = jQuery('#current_subs')
                    let other_list = jQuery('#other_list')

                    list_results.empty()
                    other_list.empty()
                    current_subs.hide()

                    // Find data source before build
                    if (geonameid === 'top_map_level') {
                        let default_map_settings = DRILLDOWNDATA.settings.default_map_settings

                        // Initialize Location Data
                        let map_data = DRILLDOWNDATA.data[default_map_settings.parent]
                        if (map_data === undefined) {
                            console.log('error getting map_data')
                            return;
                        }

                        build_list(div, map_data)
                    }
                    else if ( DRILLDOWNDATA.data[geonameid] === undefined) {
                        let rest = DRILLDOWNDATA.settings.endpoints.get_map_by_geonameid_endpoint

                        jQuery.ajax({
                            type: rest.method,
                            contentType: "application/json; charset=utf-8",
                            data: JSON.stringify({'geonameid': geonameid}),
                            dataType: "json",
                            url: DRILLDOWNDATA.settings.root + rest.namespace + rest.route,
                            beforeSend: function (xhr) {
                                xhr.setRequestHeader('X-WP-Nonce', rest.nonce);
                            },
                        })
                            .done(function (response) {
                                DRILLDOWNDATA.data[geonameid] = response
                                build_list(div, DRILLDOWNDATA.data[geonameid])
                            })
                            .fail(function (err) {
                                console.log("error")
                                console.log(err)
                                DRILLDOWN.hide_spinner()
                            })

                    } else {
                        build_list(div, DRILLDOWNDATA.data[geonameid])
                    }

                    function build_list(div, map_data) {

                        if (!window.DRILLDOWN.isEmpty(map_data.children)) { // empty children for geonameid
                            jQuery.each(map_data.children, function (gnid, data) {
                                other_list.append(`
                                    <tr><td>
                                        <a class="open_next_drilldown" data-parent="${_.escape( data.parent_id )}" data-geonameid="${_.escape( data.geonameid )}" style="cursor: pointer;">${_.escape( data.name )}</a>
                                    </td><td></td></tr>`)
                            })
                            current_subs.show()
                        }

                        list_results.empty().append(`
                                <tr><td colspan="2">Add New Location under ${_.escape( map_data.self.name )}</td></tr>
                                <tr><td style="width:150px;">Name</td><td><input id="new_name" value="" /></td></tr>
                                <tr><td>Population</td><td><input id="new_population" value="" /></td></tr>
                                <tr><td colspan="2"><button type="button" id="save-button" class="button" onclick="update_location( ${_.escape( map_data.self.geonameid )} )" >Save</a></td></tr>`)
                    }
                }

                function update_location(geonameid) {
                    jQuery('#save-button').prop('disabled', true)

                    let data = {}
                    data.key = 'sub_location'
                    data.geonameid = geonameid
                    data.value = {}
                    data.value.name = jQuery('#new_name').val()
                    data.value.population = jQuery('#new_population').val()

                    console.log(data)

                    let update = send_update(data)

                    update.done(function (data) {
                        console.log(data)
                        if (data) {
                            jQuery('#other_list').append(`
                                <tr><td><a class="open_next_drilldown" data-parent="${_.escape( geonameid )}" data-geonameid="${_.escape( data.geonameid )}" style="cursor: pointer;">${_.escape(data.name)}</a></td></tr>`)
                            jQuery('#new_name').val('')
                            jQuery('#new_population').val('')
                            jQuery('#current_subs').show()
                        }
                        jQuery('#save-button').removeProp('disabled')
                    })

                    console.log(geonameid)
                }
            </script>

            <?php

        }

        public function starting_map_level_metabox() {
            dt_write_log( 'BEGIN' );

            // load mapping class
            $mm = DT_Mapping_Module::instance();

            // set variables
            $default_map_settings = $mm->default_map_settings();

            /*******************************
             * PROCESS POST
             ******************************/
            if ( isset( $_POST['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'starting_map_level' . get_current_user_id() ) ) {
                dt_write_log( $_POST );
                $option = [];

                // set focus
                if ( isset( $_POST['focus_type'] ) && ! empty( $_POST['focus_type'] ) ) {
                    $option['type'] = sanitize_key( wp_unslash( $_POST['focus_type'] ) );
                    if ( $option['type'] !== $default_map_settings['type'] ) { // if focus changed, reset elements
                        $_POST['parent'] = 'world';
                        $_POST['children'] = [];
                    }
                } else {
                    $option['type'] = $default_map_settings['type'];
                }

                // set parent
                if ( $option['type'] === 'world' || $option['type'] === 'country' || empty( $_POST['parent'] ) ) {
                    $option['parent'] = 'world';
                } else {
                    $option['parent'] = (int) sanitize_key( wp_unslash( $_POST['parent'] ) );
                }

                // set children
                if ( $option['type'] === 'world' ) {
                    $option['children'] = [];
                }
                else if ( $option['type'] === 'country' && empty( $_POST['children'] ) ) {
                    $option['children'] = Disciple_Tools_Mapping_Queries::get_countries( true );
                }
                else if ( $option['type'] === 'state' && empty( $_POST['children'] && ! empty( $_POST['parent'] ) ) ) {
                    $list = Disciple_Tools_Mapping_Queries::get_children_by_geonameid( $option['parent'] );
                    foreach ( $list as $item ) {
                        $option['children'][] = $item['geonameid'];
                    }
                }
                else {
                    // Code check does not recognize the array_filter sanitization, even though it runs sanitization.
                    // @codingStandardsIgnoreLine
                    $option['children'] = array_filter( wp_unslash( $_POST['children'] ), 'sanitize_key' );
                }

                // @codingStandardIgnoreLine
                update_option( 'dt_mapping_module_starting_map_level', $option, false );
                $default_map_settings = $mm->default_map_settings();
            }
            dt_write_log( $default_map_settings );

            /*******************************
             * FOCUS SELECTION
             ******************************/

            /* End focus select */

            ?>
            <form method="post"> <!-- Begin form -->
            <?php wp_nonce_field( 'starting_map_level' . get_current_user_id() ); ?>

            <!-- Box -->
            <table class="widefat striped">
                <thead>
                <tr><th>Starting Map Level</th></tr>
                </thead>
                <tbody>
                <tr>
                    <td>
                        <select name="focus_type">
                            <option value="world" <?php echo ( $default_map_settings['type'] === 'world' ) ? "selected" : "" ?>>
                                World
                            </option>
                            <option value="country" <?php echo ( $default_map_settings['type'] === 'country' ) ? "selected" : ""; ?>>
                                Country
                            </option>
                            <option value="state" <?php echo ( $default_map_settings['type'] === 'state' ) ? "selected" : ""; ?>>
                                State
                            </option>
                        </select>
                        <button type="submit" class="button">Select</button>
                    </td>
                </tr>
                </tbody>
            </table>
            <br>
            <!-- End Box -->
            <script>
                function check_region(ids) {
                    jQuery.each(ids, function (i, v) {
                        jQuery('#' + v).attr('checked', 'checked')
                    })
                }

                function uncheck_all() {
                    jQuery('.country-item').removeAttr('checked')
                }

                function check_all() {
                    jQuery('.country-item').attr('checked', 'checked')
                }
            </script>

            <?php

            /*******************************
             * COUNTRY TYPE
             ******************************/
            if ( $default_map_settings['type'] === 'country' ) :

                $country_list = Disciple_Tools_Mapping_Queries::get_countries();

                ?>
                <!-- Box -->
                <table class="widefat striped">
                    <thead>
                    <tr><th colspan="2">Select Country or Countries of Focus</th></tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td>
                            <table class="widefat striped">
                                <tr>
                                    <td>
                                        <span style="float: right;">
                                            <a class="button" style="cursor:pointer;" onclick="uncheck_all()">Uncheck All</a>
                                            <a class="button" style="cursor:pointer;"
                                               onclick="check_all()">Check All</a>
                                            <button type="submit" class="button">Save</button>
                                        </span>
                                        <strong>Select Countries</strong><br><br>
                                        <hr clear="all"/>

                                        <input type="hidden" name="type" value="country"/>
                                        <input type="hidden" name="parent" value="0"/>
                                        <fieldset>
                                            <?php
                                            foreach ( $country_list as $country ) {
                                                echo '<input id="' . esc_attr( $country['geonameid'] ) . '" class="country-item" type="checkbox" name="children[]" value="' . esc_attr( $country['geonameid'] ) . '"';
                                                if ( array_search( $country['geonameid'], $default_map_settings['children'] ) !== false ) {
                                                    echo 'checked';
                                                }
                                                echo '>' . esc_html( $country['name'] ) . '<br>';
                                            }
                                            ?>
                                            <hr clear="all">
                                            <span style="float: right;">
                                                <a class="button" style="cursor:pointer;" onclick="uncheck_all()">Uncheck All</a>
                                                <a class="button" style="cursor:pointer;" onclick="check_all()">Check All</a>
                                                <button type="submit" class="button">Save</button>
                                            </span>
                                        </fieldset>
                                    </td>
                                </tr>
                            </table>
                        </td>
                        <td>
                            <p>Presets</p>

                            <hr>
                            <?php
                            $regions = $mm->get_countries_grouped_by_region();
                            foreach ( $regions as $key => $value ) {
                                $country_ids = '';
                                foreach ( $value['countries'] as $country ) {
                                    if ( ! empty( $country_ids ) ) {
                                        $country_ids .= ',';
                                    }
                                    $country_ids .= $country['geonameid'];
                                }
                                echo '<a id="' . esc_attr( $key ) . '" style="cursor:pointer;" onclick="check_region([' . esc_attr( $country_ids ) . ']);jQuery(this).append(\' &#x2714;\');">' . esc_html( $value['name'] ) . '</a><br>';
                            }

                            ?>
                        </td>
                    </tr>
                    </tbody>
                </table>

                <br>
                <!-- End Box -->
            <?php endif; // end country selection

            /*******************************
             * STATE TYPE
             ******************************/
            if ( $default_map_settings['type'] === 'state' ) :

                // create select
                $country_list = Disciple_Tools_Mapping_Queries::get_countries();

                ?>
                <table class="widefat striped">
                    <thead>
                    <tr><th colspan="2">Select Country</th></tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td>
                            <select name="parent"><option></option><option>-------------</option>
                                <?php
                                foreach ( $country_list as $result ) {
                                    echo '<option value="' . esc_attr( $result['geonameid'] ) . '" ';
                                    if ( $default_map_settings['parent'] === (int) $result['geonameid'] ) {
                                        echo 'selected';
                                    }
                                    echo '>' . esc_html( $result['name'] ) . '</option>';
                                }
                                ?>
                            </select>
                            <button type="submit" class="button">Select</button>
                        </td>
                    </tr>
                    </tbody>
                </table>
                <br>


                <?php
                // if country selection is made
                if ( $default_map_settings['parent'] ) :

                    $country_id = $default_map_settings['parent'];
                    $parent = Disciple_Tools_Mapping_Queries::get_by_geonameid( $country_id );
                    $state_list = Disciple_Tools_Mapping_Queries::get_children_by_geonameid( $country_id );

                    ?>
                    <!-- Box -->
                    <table class="widefat striped">
                        <thead>
                        <tr>
                        <th colspan="2">
                            <strong>Select States for <?php echo esc_html( $parent['name'] ) ?? '?' ?></strong>
                            <span style="float: right;">
                                <a class="button" style="cursor:pointer;" onclick="uncheck_all()">Uncheck All</a>
                                <a class="button" style="cursor:pointer;" onclick="check_all()">Check All</a>
                                <button type="submit" class="button">Save</button>
                            </span>
                        </th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td>
                                <fieldset>
                                    <?php
                                    foreach ( $state_list as $value ) {
                                        echo '<input id="' . esc_attr( $value['geonameid'] ) . '" class="country-item" type="checkbox" name="children[]" value="' . esc_attr( $value['geonameid'] ) . '"';
                                        if ( array_search( $value['geonameid'], $default_map_settings['children'] ) !== false ) {
                                            echo 'checked';
                                        }
                                        echo '>' . esc_html( $value['name'] ) . '<br>';
                                    }
                                    ?>
                                </fieldset>
                            </td>
                        </tr>
                        </tbody>
                        <tfoot>
                        <tr>
                            <td><span style="float: right;"><button type="submit" class="button">Save</button></span>
                            </td>
                        </tr>
                        </tfoot>
                    </table>
                    <br>
                    <!-- End Box -->
                <?php endif; ?>
                <?php endif; ?>
            </form>
            <?php // End form

            dt_write_log( 'END' );
        }

        public function mapping_focus_instructions_metabox() {

            $list = DT_Mapping_Module::instance()->default_map_short_list();

            ?>
            <!-- Box -->
            <table class="widefat striped">
                <thead>
                <tr><th>Current Selection</th></tr>
                </thead>
                <tbody>
                <tr>
                    <td>
                        <?php
                        if ( is_array( $list ) ) {
                            foreach ( $list as $key => $value ) {
                                echo esc_attr( $value ) . '<br>';
                            }
                        }
                        ?>
                    </td>
                </tr>
                </tbody>
            </table>
            <!-- End Box -->
            <br>
            <!-- Box -->
            <table class="widefat striped">
                <thead>
                <tr><th>Instructions</th></tr>
                </thead>
                <tbody>
                <tr>
                    <td>
                        <p>You can select World, Country, or State level focus for the mapping. By selecting the most
                            specific region of focus, you optimize the performance of the site load and various drop
                            down
                            lists throughout the site.
                        </p>
                    </td>
                </tr>
                </tbody>
            </table>
            <!-- End Box -->
            <br>

            <?php
        }

        public function set_polygon_mirror_metabox() {

            /**
             * https://storage.googleapis.com/disciple-tools-maps/
             * https://raw.githubusercontent.com/DiscipleTools/saturation-grid-project/master/
             * https://s3.amazonaws.com/mapping-source/
             */
            $mirror_list = [
                'github' => [
                    'key'   => 'github',
                    'label' => 'GitHub',
                    'url'   => 'https://raw.githubusercontent.com/DiscipleTools/saturation-grid-project/master/',
                ],
                'google' => [
                    'key'   => 'google',
                    'label' => 'Google',
                    'url'   => 'https://storage.googleapis.com/saturation-grid-project/',
                ],
//                'amazon' => [
//                    'key'   => 'amazon',
//                    'label' => 'Amazon',
//                    'url'   => 'https://s3.amazonaws.com/mapping-source/',
//                ],
                'other'  => [
                    'key'   => 'other',
                    'label' => 'Other',
                    'url'   => '',
                ],
            ];

            // process post action
            if ( isset( $_POST['source'] )
                && ( isset( $_POST['_wpnonce'] )
                    && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'set_polygon_mirror' . get_current_user_id() ) )
            ) {

                $selection_key = sanitize_text_field( wp_unslash( $_POST['source'] ) );

                if ( $selection_key === 'other' && ! empty( $_POST['other_value'] ) ) { // must be set to other and have a url
                    $url = trailingslashit( sanitize_text_field( wp_unslash( $_POST['other_value'] ) ) );
                    if ( 'https://' === substr( $url, 0, 8 ) ) { // must begin with https
                        $array = [
                            'key'   => 'other',
                            'label' => 'Other',
                            'url'   => $url,
                        ];
                        update_option( 'dt_saturation_mapping_mirror', $array, true );
                    }
                } elseif ( $selection_key !== 'other' ) {
                    $array = [
                        'key'   => $selection_key,
                        'label' => $mirror_list[$selection_key]['label'],
                        'url'   => $mirror_list[$selection_key]['url'],
                    ];
                    update_option( 'dt_saturation_mapping_mirror', $array, true );
                }
            }

            $mirror = dt_get_saturation_mapping_mirror();

            set_error_handler( [ $this, "warning_handler" ], E_WARNING );
            $list = file_get_contents( $mirror['url'] . 'polygon/available_polygons.json' );
            restore_error_handler();

            if ( $list ) {
                $status_class = 'connected';
                $message = 'Successfully connected to selected source.';
            } else {
                $status_class = 'not-connected';
                $message = 'MIRROR SOURCE NOT AVAILABLE';
            }

            ?>
            <!-- Box -->
            <style>
                .connected {
                    padding: 10px;
                    background-color: lightgreen;
                }

                .not-connected {
                    padding: 10px;
                    background-color: red;
                }
            </style>
            <form method="post">
                <table class="widefat striped">
                    <thead>
                    <th>Set the Mirror Source for Mapping Polygons</th>
                    </thead>
                    <tbody>
                    <tr>
                        <td>
                            <?php wp_nonce_field( 'set_polygon_mirror' . get_current_user_id() ); ?>

                            <p><input type="radio" id="github" name="source"
                                      value="github" <?php echo ( isset( $mirror['key'] ) && $mirror['key'] === 'github' ) ? 'checked' : '' ?>><label
                                        for="github"><?php echo esc_html( $mirror_list['github']['label'] ) ?></label>
                            </p>
                            <p><input type="radio" id="google" name="source"
                                      value="google" <?php echo ( isset( $mirror['key'] ) && $mirror['key'] === 'google' ) ? 'checked' : '' ?>><label
                                        for="google"><?php echo esc_html( $mirror_list['google']['label'] ) ?></label>
                            </p>
<!--                            <p><input type="radio" id="amazon" name="source"-->
<!--                                      value="amazon" --><?php //echo ( isset( $mirror['key'] ) && $mirror['key'] === 'amazon' ) ? 'checked' : '' ?><!--><label-->
<!--                                        for="amazon">--><?php //echo esc_html( $mirror_list['amazon']['label'] ) ?><!--</label>-->
<!--                            </p>-->
                            <p><input type="radio" id="other" name="source"
                                      value="other" <?php echo ( isset( $mirror['key'] ) && $mirror['key'] === 'other' ) ? 'checked' : '' ?>>
                                <input type="text" style="width:50em;"
                                       placeholder="add full url of your custom mirror. Must begin with https."
                                       name="other_value"
                                       value="<?php echo ( $mirror['key'] === 'other' ) ? esc_url_raw( $mirror['url'] ) : ''; ?>"/>
                                (see Custom Mirror Note below)

                            </p>
                            <p>
                                <button type="submit" class="button">Update</button>
                            </p>

                            <p id="reachable_source" class="<?php echo esc_attr( $status_class ) ?>">
                                <?php echo esc_html( $message ); ?>
                            </p>

                            <p>
                                <strong>Custom Mirror Note:</strong>
                                <em>
                                    Note: The custom mirror option allows you to download the polygon source repo (<a
                                            href="https://github.com/DiscipleTools/saturation-grid-project/archive/master.zip">Download
                                        source</a>) and install
                                    this folder to your own mirror. You will be responsible for syncing occasional
                                    updates to
                                    the folder. But this allows you to obscure traffic to these default mirrors, if you
                                    have
                                    security concerns with from your country.
                                </em>
                            </p>
                            <p>
                                <strong>Other Notes:</strong><br>
                                <em>The polygons that make up of the boarders for each country, state, and county are a
                                    significant
                                    amount of data. Mapping has broken these up into individual files that are stored at
                                    various
                                    mirror locations. You can choose the mirror that works for you and your country, or
                                    you can host your own mirror
                                    for security reasons.
                                </em>
                            </p>

                        </td>
                    </tr>
                    </tbody>
                </table>
            </form>
            <br>
            <!-- End Box -->
            <?php
        }

        public function set_geocoding_source_metabox() {
            ?>
            <form method="post">
                <table class="widefat striped">
                    <thead>
                    <tr><th>Geocoding Provider Setup</th></tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td>
                            <?php wp_nonce_field( 'population_division' . get_current_user_id() ); ?>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </form>
            <?php
        }

        public function geocode_metabox() {

            global $post, $pagenow;
            if ( ! ( 'post-new.php' == $pagenow ) ) :
                $post_meta = get_post_meta( $post->ID );

                echo '<input type="hidden" name="dt_locations_noonce" id="dt_locations_noonce" value="' . esc_attr( wp_create_nonce( 'update_location_info' ) ) . '" />';
                ?>
                <table class="widefat striped">
                    <tr>
                        <td><label for="search_location_address">Address:</label></td>
                        <td><input type="text" id="search_location_address"
                                   value="<?php isset( $post_meta['location_address'][0] ) ? print esc_attr( $post_meta['location_address'][0] ) : print esc_attr( '' ); ?>"/>
                            <button type="button" class="button" name="validate_address_button"
                                    id="validate_address_button"
                                    onclick="validate_address( jQuery('#search_location_address').val() );">Validate
                            </button>
                            <button type="submit" name="delete" value="1" class="button">Delete</button>
                            <br>
                            <span id="errors"><?php echo ( ! empty( $this->error ) ) ? esc_html( $this->error ) : ''; ?></span>
                            <p id="possible-results">

                                <input type="hidden" id="location_address" name="location_address"
                                       value="<?php isset( $post_meta['location_address'][0] ) ? print esc_attr( $post_meta['location_address'][0] ) : print esc_attr( '' ); ?>"/>
                            </p>
                        </td>
                    </tr>
                </table>

            <?php else :
                echo esc_html__( 'You must save post before geocoding.' );
            endif;
        }

        public function warning_handler( $errno, $errstr ) {
            ?>
            <div class="notice notice-error notice-dt-mapping-source" data-notice="dt-demo">
                <p><?php echo "MIRROR SOURCE NOT AVAILABLE" ?></p>
                <p><?php echo "Error Message: " . esc_attr( $errstr ) ?></p>
            </div>
            <?php
        }

        public function migration_rebuild_geonames() {
            if ( isset( $_POST['reset_geonames'] )
                && ( isset( $_POST['_wpnonce'] )
                    && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'rebuild_geonames' . get_current_user_id() ) ) ) {

                $this->migrations_reset_and_rerun();
            }
            ?>
            <!-- Box -->
            <form method="post">
                <?php wp_nonce_field( 'rebuild_geonames' . get_current_user_id() ); ?>
                <table class="widefat striped">
                    <thead>
                    <tr><th>Clean and Reinstall Mapping Resources (does not effect Contacts or Group data.)</th></tr>
                    </thead>
                    <tbody>

                    <tr>
                        <td>
                            <p>
                                <button type="button" class="button"
                                        onclick="jQuery('#reset_geonames').show();jQuery(this).prop('disabled', 'disabled')">
                                    Reset Geonames Table and Install Geonames
                                </button>
                            </p>
                            <span id="reset_geonames" style="display:none;">
                                <button type="submit" class="button" name="reset_geonames" value="1">Are you sure you want to empty the table and to add geonames?</button>
                            </span>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </form>
            <?php
        }

        public function migrations_reset_and_rerun() {
            global $wpdb;
            // drop tables
            $wpdb->dt_geonames = $wpdb->prefix . 'dt_geonames';
            $wpdb->query( "DROP TABLE IF EXISTS $wpdb->dt_geonames" );

            // delete
            delete_option( 'dt_mapping_module_migration_lock' );
            delete_option( 'dt_mapping_module_migrate_last_error' );
            delete_option( 'dt_mapping_module_migration_number' );

            // delete folder and downloads
            $dir = wp_upload_dir();
            $uploads_dir = trailingslashit( $dir['basedir'] );
            if ( file_exists( $uploads_dir . 'geonames/geonames.tsv.zip' ) ) {
                unlink( $uploads_dir . 'geonames/geonames.tsv.zip' );
            }
            if ( file_exists( $uploads_dir . 'geonames/geonames.tsv' ) ) {
                unlink( $uploads_dir . 'geonames/geonames.tsv' );
            }

            // trigger migration engine
            require_once( 'class-migration-engine.php' );
            try {
                DT_Mapping_Module_Migration_Engine::migrate( DT_Mapping_Module_Migration_Engine::$migration_number );
            } catch ( Throwable $e ) {
                $migration_error = new WP_Error( 'migration_error', 'Migration engine for mapping module failed to migrate.', [ 'error' => $e ] );
                dt_write_log( $migration_error );
            }
        }

        public function rebuild_geonames( $reset = false ) {
            global $wpdb;

            // clear previous installation
            $dir = wp_upload_dir();
            $uploads_dir = trailingslashit( $dir['basedir'] );
            $file = 'geonames.tsv';
            $file_location = $uploads_dir . "geonames/" . $file;

            // TEST for presence of source files
            if ( ! file_exists( $uploads_dir . "geonames/" . $file ) ) {
                require_once( get_template_directory() . '/dt-mapping/migrations/0001-prepare-geonames-data.php' );
                $download = new DT_Mapping_Module_Migration_0001();
                $download->up();

                if ( ! file_exists( $uploads_dir . "geonames/" . $file ) ) {
                    error_log( 'Failed to find ' . $file );

                    return;
                }
            }

            // TEST for expected tables and clear it
            $wpdb->query( "SHOW TABLES LIKE '$wpdb->dt_geonames'" );
            if ( $wpdb->num_rows < 1 ) {
                require_once( get_template_directory() . '/dt-mapping/migrations/0000-initial.php' );
                $download = new DT_Mapping_Module_Migration_0000();
                $download->up();

                $wpdb->query( "SHOW TABLES LIKE '$wpdb->dt_geonames'" );
                if ( $wpdb->num_rows < 1 ) {
                    error_log( 'Failed to find ' . $wpdb->dt_geonames );
                    dt_write_log( $wpdb->num_rows );
                    dt_write_log( $wpdb );

                    return;
                }
            }
            if ( $reset ) {
                $wpdb->query( "TRUNCATE $wpdb->dt_geonames" );
            }

            // LOAD geonames data
            dt_write_log( 'begin geonames install: ' . microtime() );

            $fp = fopen( $file_location, 'r' );

            $query = "INSERT IGNORE INTO $wpdb->dt_geonames VALUES";
            $count = 0;
            while ( ! feof( $fp ) ) {
                $line = fgets( $fp, 2048 );
                $count++;

                $data = str_getcsv( $line, "\t" );
                $data_sql = dt_array_to_sql( $data );
                if ( isset( $data[29] ) ) {
                    $query .= " ( $data_sql ), ";
                }
                if ( $count === 500 ) {
                    $query .= ';';
                    $query = str_replace( ", ;", ";", $query ); //remove last comma
                    $wpdb->query( $query );  //phpcs:ignore
                    $query = "INSERT IGNORE INTO $wpdb->dt_geonames VALUES";
                    $count = 0;
                }
            }
            //add the last queries
            $query .= ';';
            $query = str_replace( ", ;", ";", $query ); //remove last comma
            $wpdb->query( $query );  //phpcs:ignore

            dt_write_log( 'end geonames install: ' . microtime() );

            fclose( $fp );
        }

        /**
         * Add a sublocation under a geoname (or other sublocation) parent
         */

        /**
         * @param $parent_geoname_id
         * @param $name
         * @param $population
         *
         * @return int|WP_Error, the id of the new sublocation
         */
        public function add_sublocation_under_geoname( $parent_geoname_id, $name, $population ){
            global $wpdb;
            $parent_geoname = $wpdb->get_row( $wpdb->prepare( "
                SELECT * FROM $wpdb->dt_geonames WHERE geonameid = %d
            ", $parent_geoname_id ), ARRAY_A );
            if ( empty( $parent_geoname ) ) {
                return new WP_Error( 'missing_param', 'Missing or incorrect geoname parent.' );
            }

            $max_id = (int) $wpdb->get_var( "SELECT MAX(geonameid) FROM $wpdb->dt_geonames" );
            $max_id = max( $max_id, 1000000000 );
            $custom_geonameid = $max_id + 1;


            // get level
            if ( isset( $parent_geoname['level'] ) ) {
                switch ( $parent_geoname['level'] ) {
                    case 'country':
                        $level = 'admin1c';
                        $parent_geoname['admin1_geonameid'] = $custom_geonameid;
                        $parent_geoname['feature_code'] = 'ADM1';
                        break;
                    case 'admin1':
                        $level = 'admin2c';
                        $parent_geoname['admin2_geonameid'] = $custom_geonameid;
                        $parent_geoname['feature_code'] = 'ADM2';
                        break;
                    case 'admin2':
                        $level = 'admin3c';
                        $parent_geoname['admin3_geonameid'] = $custom_geonameid;
                        $parent_geoname['feature_code'] = 'ADM3';
                        break;
                    case 'admin3':
                        $level = 'admin4c';
                        $parent_geoname['admin4_geonameid'] = $custom_geonameid;
                        $parent_geoname['feature_code'] = 'ADM4';
                        break;
                    case 'admin4':
                    default:
                        $level = 'place';
                        $parent_geoname['feature_class'] = 'P';
                        $parent_geoname['feature_code'] = 'PPL';
                        break;
                }
            } else {
                $level = 'place';
                $parent_geoname['feature_class'] = 'P';
                $parent_geoname['feature_code'] = 'PPL';
            }

            // save new record
            $result = $wpdb->insert(
                $wpdb->dt_geonames,
                [
                    'geonameid' => $custom_geonameid,
                    'name' => $name,
                    'latitude' => $parent_geoname['latitude'],
                    'longitude' => $parent_geoname['longitude'],
                    'feature_class' => $parent_geoname['feature_class'],
                    'feature_code' => $parent_geoname['feature_code'],
                    'country_code' => $parent_geoname['country_code'],
                    'cc2' => $parent_geoname['cc2'],
                    'admin1_code' => $parent_geoname['admin1_code'],
                    'admin2_code' => $parent_geoname['admin2_code'],
                    'admin3_code' => $parent_geoname['admin3_code'],
                    'admin4_code' => $parent_geoname['admin4_code'],
                    'population' => $population,
                    'elevation' => $parent_geoname['elevation'],
                    'dem' => $parent_geoname['dem'],
                    'timezone' => $parent_geoname['timezone'],
                    'modification_date' => current_time( 'mysql' ),
                    'parent_id' => $parent_geoname_id,
                    'country_geonameid' => $parent_geoname['country_geonameid'],
                    'admin1_geonameid' => $parent_geoname['admin1_geonameid'],
                    'admin2_geonameid' => $parent_geoname['admin2_geonameid'],
                    'admin3_geonameid' => $parent_geoname['admin3_geonameid'],
                    'level' => $level,
                    'alt_name' => $name,
                    'is_custom_location' => 1,
                ],
                [
                    '%d', // geonameid
                    '%s',
                    '%d', // latitude
                    '%d', // longitude
                    '%s',
                    '%s',
                    '%s', // country code
                    '%s',
                    '%s', // admin1 code
                    '%s',
                    '%s',
                    '%s',
                    '%d', // population
                    '%d',
                    '%d',
                    '%s', // timezone
                    '%s', // modification date
                    '%d', // parent id
                    '%d',
                    '%d',
                    '%d',
                    '%d',
                    '%s', // level
                    '%s',
                    '%d' //is custom location
                ]
            );
            if ( !$result ){
                return new WP_Error( __FUNCTION__, 'Error creating sublocation' );
            } else {
                return $custom_geonameid;
            }
        }

        public function convert_location_to_geoname( $location_id, $geoname_id ){

            global $wpdb;
            $wpdb->query( $wpdb->prepare(
                "INSERT INTO $wpdb->postmeta
                (
                    post_id,
                    meta_key,
                    meta_value
                )
                SELECT p2p_from, 'geonames', %s
                FROM $wpdb->p2p as p2p
                WHERE p2p_to = %s
                ",
                esc_sql( $geoname_id ),
                esc_sql( $location_id )
            ));
            // delete location connections
            $wpdb->query( $wpdb->prepare(
                "DELETE FROM $wpdb->p2p
                  WHERE p2p_to = %s
            ", esc_sql( $location_id ) ) );

            wp_trash_post( $location_id );

            $wpdb->query(  $wpdb->prepare(" 
                UPDATE $wpdb->dt_activity_log
                SET 
                    action = 'field_update', 
                    object_subtype = 'geonames',
                    meta_key = 'geonames',
                    meta_value = %s,
                    field_type = 'location'
                WHERE meta_key = 'contacts_to_locations' OR meta_key = 'groups_to_locations'
                AND meta_value = %s
                ",
                $geoname_id,
                $location_id
            ));
        }

        public function migrate_user_filters_to_geonames(){
            //get migrations
            $migrated = get_option( "dt_mapping_migration_list", [] );
//            get users with that have filters
//            check for locations
//            try converting to geonames
            global $wpdb;

            $users = get_users( [ "meta_key" => $wpdb->get_blog_prefix() . "saved_filters" ] );
            foreach ( $users as $user ){
                $save_filters = get_user_option( "saved_filters", $user->ID );
                foreach ( $save_filters as $post_type => &$filters ){
                    foreach ( $filters as &$filter ){
                        if ( !empty( $filter["query"]["locations"] ) ){
                            $geonames = [];
                            foreach ( $filter["query"]["locations"] as $location ){
                                if ( isset( $migrated[$location]["selected_geoname"] ) ){
                                    $geonames[] = $migrated[$location]["selected_geoname"];
                                }
                            }
                            $filter['query']['geonames'] = $geonames;
                            unset( $filter["query"]["locations"] );
                            foreach ( $filter["labels"] as &$label ){
                                if ( $label["field"] === "locations" ){
                                    if ( isset( $migrated[$label["id"]]["selected_geoname"] )){
                                        $label["field"] = "geonames";
                                        $label["id"] = $migrated[$label["id"]]["selected_geoname"];
                                    }
                                }
                            }
                        }
                    }
                }
                update_user_option( $user->ID, "saved_filters", $save_filters );
            }
        }

        public function dt_locations_migration_admin_notice() {
            $current_migration_number = get_option( 'dt_mapping_module_migration_number' );
            if ( $current_migration_number < 3 ){ ?>
                <div class="notice notice-error notice-dt-locations-migration is-dismissible" data-notice="dt-locations-migration">
                    <p>We tried upgrading the locations system to the new version, but something went wrong. Please contact your system administrator</p>
                </div>
            <?php }
            if ( ! get_option( 'dt_locations_migrated_to_geonames', false ) ) { ?>
                <div class="notice notice-error notice-dt-locations-migration is-dismissible" data-notice="dt-locations-migration">
                    <p>We have updated Disciple.Tools locations system. Please use the migration tool to make sure all you locations are carried over:
                        <a href="<?php echo esc_html( admin_url( 'admin.php?page=dt_mapping_module&tab=location-migration' ) ) ?>">Migration Tool</a></p>
                </div>
            <?php }
        }
    }

    DT_Mapping_Module_Admin::instance();
}
