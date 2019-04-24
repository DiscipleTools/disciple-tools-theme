<?php
/**
 * Mapping Module Admin Section Elements
 * These elements are designed to be included into the admin areas of other themes and plugins.
 * Example:
 *      DT_Mapping_Module_Admin::instance()->population_metabox();
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

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
        public $map_key;

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

            if ( is_admin() && isset( $_GET['page'] ) && 'dt_mapping_module' === $_GET['page'] ) {
                $this->spinner = spinner();
                $this->nonce = wp_create_nonce( 'wp_rest' );
                $this->current_user_id = get_current_user_id();

                add_action( 'admin_head', [ $this, 'scripts' ] );
                add_action( "admin_enqueue_scripts", [ $this, 'enqueue_scripts' ] );
            }
        }

        /**
         * Admin Page Elements
         */
        public function scripts() {
            ?>
            <script>
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
                            console.log(data)
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
                        console.log(data)
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

                #drill_down {
                    margin-bottom: 0;
                    list-style-type: none;
                }

                #drill_down li {
                    display: inline;
                    margin-right: 3px;
                }

                #drill_down li select {
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

        public function enqueue_scripts( $hook ) {
            if ( 'admin.php' === $hook ) {
                return;
            }
            // drill down tool
            wp_enqueue_script( 'typeahead-jquery', get_template_directory_uri() . '/dt-core/dependencies/typeahead/dist/jquery.typeahead.min.js', [ 'jquery' ], true );
            wp_enqueue_style( 'typeahead-jquery-css', get_template_directory_uri() . '/dt-core/dependencies/typeahead/dist/jquery.typeahead.min.css', [] );

            wp_register_script( 'lodash', 'https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.11/lodash.min.js', false, '4.17.11' );
            wp_enqueue_script( 'lodash' );
            wp_enqueue_script( 'mapping-drill-down', get_template_directory_uri() . '/dt-mapping/drill-down.js', [ 'jquery', 'lodash' ], '1.1' );
            wp_localize_script(
                'mapping-drill-down', 'mappingModule', [
                    'mapping_module' => DT_Mapping_Module::instance()->localize_script(),
                ]
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

                        dt_write_log( $params['value'] );

                        if ( isset( $params['value']['name'] ) ) {
                            $name = sanitize_text_field( wp_unslash( $params['value']['name'] ) );
                        } else {
                            return new WP_Error( 'missing_param', 'Missing name parameter' );
                        }

                        if ( isset( $params['value']['population'] ) ) {
                            $population = sanitize_text_field( wp_unslash( $params['value']['population'] ) );
                        } else {
                            $population = 0;
                        }

                        $parent_geoname = $wpdb->get_row( $wpdb->prepare( "
                            SELECT * FROM $wpdb->dt_geonames WHERE geonameid = %d
                        ", $geonameid ), ARRAY_A );
                        if ( empty( $parent_geoname ) ) {
                            return new WP_Error( 'missing_param', 'Missing geoname parent.' );
                        }

                        // make unique geonameid
                        // 999 + increment + 999 + geonameid
                        // 999 (3) + 999 (3) + 999 (3) + 12031544 (8)
                        $current_custom_locations = $wpdb->get_col( $wpdb->prepare( "
                            SELECT * FROM $wpdb->dt_geonames WHERE geonameid LIKE %s AND geonameid LIKE %s
                         ",
                            '%' . $wpdb->esc_like( $geonameid ),
                            $wpdb->esc_like( '999' ) . '%'
                        ) );

                        $i = 1;
                        $custom_geonameid = '999' . $i . '999' . $geonameid;
                        if ( array_search( $custom_geonameid, $current_custom_locations ) !== false ) {
                            while ( array_search( $custom_geonameid, $current_custom_locations ) !== false ) {
                                $i++;
                                $custom_geonameid = '999' . $i . '999' . $geonameid;
                            }
                        }
                        dt_write_log( $custom_geonameid );

                        // get level
                        if ( isset( $parent_geoname['level'] ) ) {
                            switch ( $parent_geoname['level'] ) {
                                case 'country':
                                    $level = 'admin1';
                                    $parent_geoname['admin1_geonameid'] = $custom_geonameid;
                                    $parent_geoname['feature_code'] = 'ADM1';
                                    break;
                                case 'admin1':
                                    $level = 'admin2';
                                    $parent_geoname['admin2_geonameid'] = $custom_geonameid;
                                    $parent_geoname['feature_code'] = 'ADM2';
                                    break;
                                case 'admin2':
                                    $level = 'admin3';
                                    $parent_geoname['admin3_geonameid'] = $custom_geonameid;
                                    $parent_geoname['feature_code'] = 'ADM3';
                                    break;
                                case 'admin3':
                                    $level = 'admin4';
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
                                      'parent_id' => $geonameid,
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

                        dt_write_log( $result );

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
                        <?php ( $tab == 'general' || ! isset( $tab ) ) ? esc_attr_e( 'nav-tab-active', 'disciple_tools' ) : print ''; ?>">
                        <?php esc_attr_e( 'General Settings', 'disciple_tools' ) ?>
                    </a>
                    <!-- Starting Map -->
                    <a href="<?php echo esc_attr( $link ) . 'focus' ?>" class="nav-tab
                        <?php ( $tab == 'focus' ) ? esc_attr_e( 'nav-tab-active', 'disciple_tools' ) : print ''; ?>">
                        <?php esc_attr_e( 'Mapping Focus', 'disciple_tools' ) ?>
                    </a>
                    <!-- Polygon -->
                    <a href="<?php echo esc_attr( $link ) . 'polygons' ?>" class="nav-tab
                        <?php ( $tab == 'polygons' ) ? esc_attr_e( 'nav-tab-active', 'disciple_tools' ) : print ''; ?>">
                        <?php esc_attr_e( 'Polygons', 'disciple_tools' ) ?>
                    </a>
                    <!-- Geocoding -->
                    <a href="<?php echo esc_attr( $link ) . 'geocoding' ?>" class="nav-tab
                        <?php ( $tab == 'geocoding' ) ? esc_attr_e( 'nav-tab-active', 'disciple_tools' ) : print ''; ?>">
                        <?php esc_attr_e( 'Geocoding', 'disciple_tools' ) ?>
                    </a>
                    <!-- Names Tab -->
                    <a href="<?php echo esc_attr( $link ) . 'names' ?>" class="nav-tab
                        <?php ( $tab == 'names' ) ? esc_attr_e( 'nav-tab-active', 'disciple_tools' ) : print ''; ?>">
                        <?php esc_attr_e( 'Names', 'disciple_tools' ) ?>
                    </a>
                    <!-- Population Tab -->
                    <a href="<?php echo esc_attr( $link ) . 'population' ?>" class="nav-tab
                        <?php ( $tab == 'population' ) ? esc_attr_e( 'nav-tab-active', 'disciple_tools' ) : print ''; ?>">
                        <?php esc_attr_e( 'Population', 'disciple_tools' ) ?>
                    </a>
                    <!-- Add Sub-Locations -->
                    <a href="<?php echo esc_attr( $link ) . 'sub_locations' ?>" class="nav-tab
                        <?php ( $tab == 'sub_locations' ) ? esc_attr_e( 'nav-tab-active', 'disciple_tools' ) : print ''; ?>">
                        <?php esc_attr_e( 'Sub-Locations', 'disciple_tools' ) ?>
                    </a>
                    <!-- Add Migration -->
                    <a href="<?php echo esc_attr( $link ) . 'migration' ?>" class="nav-tab
                        <?php ( $tab == 'migration' ) ? esc_attr_e( 'nav-tab-active', 'disciple_tools' ) : print ''; ?>">
                        <?php esc_attr_e( 'Migration', 'disciple_tools' ) ?>
                    </a>
                    <!-- Add Locations Explorer -->
                    <a href="<?php echo esc_attr( $link ) . 'explore' ?>" class="nav-tab
                        <?php ( $tab == 'explore' ) ? esc_attr_e( 'nav-tab-active', 'disciple_tools' ) : print ''; ?>">
                        <?php esc_attr_e( 'Explore', 'disciple_tools' ) ?>
                    </a>

                </h2>

                <?php
                switch ( $tab ) {
                    case "general":
                        $this->general_tab();
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

                            <!-- Drill Down Box -->
                            <form method="post">
                                <table class="widefat striped">
                                    <thead>
                                    <th>Drill-Down</th>
                                    </thead>
                                    <tbody id="drill_down_body"></tbody>
                                </table>
                            </form>

                            <br>

                            <!-- Results Box-->
                            <table class="widefat striped">
                                <thead>
                                <th>List</th>
                                </thead>
                                <tbody>
                                <tr>
                                    <td id="results_body"><img src="<?php echo esc_url( $this->spinner ); ?>"
                                                               style="width:20px; padding-top:5px;"/></td>
                                </tr>
                                </tbody>
                            </table>

                            <!-- End Main Column -->
                        </div><!-- end post-body-content -->
                    </div><!-- post-body meta box container -->
                </div><!--poststuff end -->
            </div><!-- wrap end -->
            <script>
                jQuery(document).ready(function () {
                    reset_drill_down()
                })

                function reset_drill_down() {
                    jQuery('#drill_down_body').empty().append(`<tr><td>World</td></tr><tr><td><select id="6295630" onchange="get_children( this.value );jQuery(this).parent().parent().nextAll().remove();"><option>Select</option></select> <span id="spinner_6295630"><img src="<?php echo esc_url( $this->spinner ) ?>" style="width:20px; padding-top:5px;" /></span></td></tr>`)
                    jQuery.ajax({
                        type: "POST",
                        contentType: "application/json; charset=utf-8",
                        data: JSON.stringify({'geonameid': 6295630}),
                        dataType: "json",
                        url: "<?php echo esc_url_raw( rest_url() ) ?>dt/v1/mapping_module/get_children",
                        beforeSend: function (xhr) {
                            xhr.setRequestHeader('X-WP-Nonce', '<?php echo esc_attr( wp_create_nonce( 'wp_rest' ) ) ?>');
                        },
                    })
                        .done(function (response) {
                            console.log(response)

                            jQuery('#spinner_6295630').empty()
                            jQuery('#results_body').empty()

                            if (response) {
                                jQuery.each(response.list, function (i, v) {
                                    jQuery('#6295630').append(`<option value="${v.id}">${v.name}</option>`)
                                })
                            }

                        }) // end success statement
                        .fail(function (err) {
                            console.log("error")
                            console.log(err)
                        })
                }

                function get_children(id) {
                    let drill_down = jQuery('#drill_down_body')
                    console.log(id)
                    console.log(drill_down)

                    let spinner_span = jQuery('#spinner_' + id)
                    let results_box = jQuery('#results_body')
                    let spinner = `<img src="<?php echo esc_url( $this->spinner ) ?>" style="width:20px; padding-top:5px;" />`

                    results_box.empty().append(spinner)

                    jQuery.ajax({
                        type: "POST",
                        contentType: "application/json; charset=utf-8",
                        data: JSON.stringify({'geonameid': id}),
                        dataType: "json",
                        url: "<?php echo esc_url_raw( rest_url() ) ?>dt/v1/mapping_module/get_children",
                        beforeSend: function (xhr) {
                            xhr.setRequestHeader('X-WP-Nonce', '<?php echo esc_attr( wp_create_nonce( 'wp_rest' ) ) ?>');
                        },
                    })
                        .done(function (response) {
                            console.log(response)

                            spinner_span.empty()
                            results_box.empty()

                            if (response.list.length === 0) {
                                results_box.append(`<tr><td>No Children Locations</td></tr>`)
                            }
                            else {
                                jQuery('#drill_down_body').append(`<tr><td><select id="${id}" onchange="get_children( this.value );jQuery(this).parent().parent().nextAll().remove()"><option>Select</option></select> <span id="spinner_${id}"></span></td></tr>`)
                                jQuery.each(response.list, function (i, v) {
                                    jQuery('#' + id).append(`<option value="${v.id}">${v.name}</option>`)
                                })
                                results_box.append(response.html)
                            }

                        }) // end success statement
                        .fail(function (err) {
                            console.log("error")
                            console.log(err)
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
                <th>Name</th>
                <th>Current Setting</th>
                <th></th>
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
                        $mirror = dt_get_mapping_polygon_mirror();
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
                    <th>Migration Status</th>
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
                    <th>Groups Per Population</th>
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
                <th>Select Population List to Edit</th>
                </thead>
                <tbody>
                <tr>
                    <td>
                        <?php DT_Mapping_Module::instance()->drill_down_input( 'population_edit' ) ?>
                    </td>
                </tr>
                </tbody>
            </table>

            <table class="widefat striped">
                <thead>
                <th>Name</th>
                <th>Population</th>
                <th>New Population (no commas)</th>
                <th></th>
                <th></th>
                </thead>
                <tbody id="list_results">
                </tbody>
            </table>

            <script>
                window.DRILLDOWNDATA.settings.hide_final_drill_down = true
                window.DRILLDOWN.population_edit = function (geonameid) {
                    if (geonameid === 'top_map_list') { // top level multi-list
                        let list_results = jQuery('#list_results')
                        let gn = []
                        list_results.empty()
                        jQuery.each(window.DRILLDOWNDATA.data.top_map_list, function (i, v) {
                            gn = window.DRILLDOWNDATA.data[i]
                            if (gn !== undefined) {
                                list_results.append(`<tr>
                                        <td>${gn.self.name}</td>
                                        <td id="label-${gn.self.geonameid}">${gn.self.population_formatted}</td>
                                        <td><input type="number" id="input-${gn.self.geonameid}" value=""></td>
                                        <td id="button-${gn.self.geonameid}"><a class="button" onclick="update( ${gn.self.geonameid}, jQuery('#input-'+${gn.self.geonameid}).val(), 'population' )">Update</a></td>
                                        <td id="reset-${gn.self.geonameid}"><a class="button" onclick="reset( ${gn.self.geonameid}, 'population' )">Reset</a></td>
                                        </tr>`)
                            }
                        })
                    }
                    else if (window.DRILLDOWN.isEmpty(window.DRILLDOWNDATA.data[geonameid].children)) { // empty children for geonameid
                        jQuery('#drill_down').append(`<li><em>deepest level reached!</em></li>`)
                    }
                    else { // children available
                        let list_results = jQuery('#list_results')
                        list_results.empty()
                        jQuery.each(window.DRILLDOWNDATA.data[geonameid].children, function (i, v) {
                            list_results.append(`<tr>
                                        <td>${v.name}</td>
                                        <td id="label-${v.geonameid}">${v.population_formatted}</td>
                                        <td><input type="number" id="input-${v.geonameid}" value=""></td>
                                        <td id="button-${v.geonameid}"><a class="button" onclick="update( ${v.geonameid}, jQuery('#input-'+${v.geonameid}).val(), 'population' )">Update</a></td>
                                        <td id="reset-${v.geonameid}"><a class="button" onclick="reset( ${v.geonameid}, 'population' )">Reset</a></td>
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
                <th>Edit Default Location Names</th>
                </thead>
                <tbody>
                <tr>
                    <td>
                        <?php DT_Mapping_Module::instance()->drill_down_input( 'name_select' ) ?>
                    </td>
                </tr>
                </tbody>
            </table>

            <table class="widefat striped">
                <thead>
                <th>Name</th>
                <th>New Name</th>
                <th></th>
                <th></th>
                </thead>
                <tbody id="list_results"></tbody>
            </table>

            <script>
                window.DRILLDOWNDATA.settings.hide_final_drill_down = true
                window.DRILLDOWN.name_select = function (geonameid) {
                    if (geonameid === 'top_map_list') { // top level multi-list
                        let list_results = jQuery('#list_results')
                        let gn = []
                        list_results.empty()
                        jQuery.each(window.DRILLDOWNDATA.data.top_map_list, function (i, v) {
                            gn = window.DRILLDOWNDATA.data[i]
                            if (gn !== undefined) {
                                list_results.append(`<tr><td id="label-${gn.self.geonameid}">${gn.self.name}</td>
                                    <td><input type="text" id="input-${gn.self.geonameid}" value="" /></td>
                                    <td id="button-${gn.self.geonameid}"><a class="button" onclick="update( ${gn.self.geonameid}, jQuery('#input-'+${gn.self.geonameid}).val(), 'name' )">Update</a></td>
                                    <td id="reset-${gn.self.geonameid}"><a class="button" onclick="reset( ${gn.self.geonameid}, 'name'  )">Reset</a></td>
                                    </tr>`)
                            }
                        })
                    }
                    else if (window.DRILLDOWN.isEmpty(window.DRILLDOWNDATA.data[geonameid].children)) { // empty children for geonameid
                        jQuery('#drill_down').append(`<li><em>deepest level reached!</em></li>`)
                    }
                    else { // children available
                        let list_results = jQuery('#list_results')
                        list_results.empty()
                        jQuery.each(window.DRILLDOWNDATA.data[geonameid].children, function (i, v) {
                            list_results.append(`<tr><td id="label-${v.geonameid}">${v.name}</td><td><input type="text" id="input-${v.geonameid}" value=""></td>
                                    <td id="button-${v.geonameid}"><a class="button" onclick="update( ${v.geonameid}, jQuery('#input-'+${v.geonameid}).val(), 'name' )">Update</a></td>
                                    <td id="reset-${v.geonameid}"><a class="button" onclick="reset( ${v.geonameid}, 'name' )">Reset</a></td>
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
                <th>Select the Location</th>
                </thead>
                <tbody>
                <tr>
                    <td>
                        <?php DT_Mapping_Module::instance()->drill_down_input( 'sublocation' ) ?>
                    </td>
                </tr>
                </tbody>
            </table>
            <table class="widefat striped" style="display:none;" id="current_subs">
                <thead>
                <th>Current Sub-Locations (use these if possible):</th>
                <th style="width:20px;"></th>
                </thead>
                <tbody id="other_list">
                </tbody>
            </table>
            <br>
            <table class="widefat striped">
                <tbody id="list_results"></tbody>
            </table>

            <script>
                window.DRILLDOWN.sublocation = function (geonameid) {
                    let list_results = jQuery('#list_results')
                    let current_subs = jQuery('#current_subs')
                    let other_list = jQuery('#other_list')

                    list_results.empty()
                    other_list.empty()
                    current_subs.hide()

                    if (geonameid === 'top_map_list') { // top level multi-list
                        list_results.append(`Select one single location`)
                    }
                    else { // children available
                        if (!window.DRILLDOWN.isEmpty(window.DRILLDOWNDATA.data[geonameid].children)) { // empty children for geonameid
                            jQuery.each(window.DRILLDOWNDATA.data[geonameid].children, function (gnid, data) {
                                other_list.append(`<tr><td><a onclick="DRILLDOWN.geoname_drill_down( ${gnid}, 'sublocation' );jQuery('#'+${gnid}).parent().nextAll().remove();jQuery('#${geonameid} option[value=${gnid}]').attr('selected', 'selected');">${data.name}</a></td><td></td></tr>`)
                            })
                            current_subs.show()
                        }

                        list_results.append(`
                                <tr><td colspan="2">Add New Location to ${window.DRILLDOWNDATA.data[geonameid].self.name}</td></tr>
                                <tr><td style="width:150px;">Name</td><td><input id="new_name" value="" /></td></tr>
                                <tr><td>Population</td><td><input id="new_population" value="" /></td></tr>
                                <tr><td colspan="2"><button type="button" id="save-button" class="button" onclick="update_location( ${geonameid} )" >Save</a></td></tr>`)
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
                            jQuery('#other_list').append(`<tr><td>${data.name}</td></tr>`)

                            jQuery('#new_name').val('')
                            jQuery('#new_population').val('')
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
                    $option['children'] = $mm->query( 'get_countries', [ 'ids_only' => true ] );
                }
                else if ( $option['type'] === 'state' && empty( $_POST['children'] && ! empty( $_POST['parent'] ) ) ) {
                    $list = $mm->query( 'get_children_by_geonameid', [ 'geonameid' => $option['parent'] ] );
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
                <th>Starting Map Level</th>
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

                $country_list = $mm->query( 'get_countries' );

                ?>
                <!-- Box -->
                <table class="widefat striped">
                    <thead>
                    <th colspan="2">Select Country or Countries of Focus</th>
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
                $country_list = $mm->query( 'get_countries' );

                ?>
                <table class="widefat striped">
                    <thead>
                    <th colspan="2">Select Country</th>
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
                    $parent = $mm->query( 'get_by_geonameid', [ 'geonameid' => $country_id ] );
                    $state_list = $mm->query( 'get_children_by_geonameid', [ 'geonameid' => $country_id ] );

                    ?>
                    <!-- Box -->
                    <table class="widefat striped">
                        <thead>
                        <th colspan="2">
                            <strong>Select States for <?php echo esc_html( $parent['name'] ) ?? '?' ?></strong>
                            <span style="float: right;">
                                <a class="button" style="cursor:pointer;" onclick="uncheck_all()">Uncheck All</a>
                                <a class="button" style="cursor:pointer;" onclick="check_all()">Check All</a>
                                <button type="submit" class="button">Save</button>
                            </span>
                        </th>
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
                <th>Current Selection</th>
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
                <th>Instructions</th>
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
             * https://raw.githubusercontent.com/DiscipleTools/dt-mapping-data/master/
             * https://s3.amazonaws.com/mapping-source/
             */
            $mirror_list = [
                'github' => [
                    'key'   => 'github',
                    'label' => 'GitHub',
                    'url'   => 'https://raw.githubusercontent.com/DiscipleTools/dt-mapping-data/master/',
                ],
                'google' => [
                    'key'   => 'google',
                    'label' => 'Google',
                    'url'   => 'https://storage.googleapis.com/mapping-source/',
                ],
                'amazon' => [
                    'key'   => 'amazon',
                    'label' => 'Amazon',
                    'url'   => 'https://s3.amazonaws.com/mapping-source/',
                ],
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
                        update_option( 'dt_mapping_module_polygon_mirror', $array, true );
                    }
                } elseif ( $selection_key !== 'other' ) {
                    $array = [
                        'key'   => $selection_key,
                        'label' => $mirror_list[$selection_key]['label'],
                        'url'   => $mirror_list[$selection_key]['url'],
                    ];
                    update_option( 'dt_mapping_module_polygon_mirror', $array, true );
                }
            }

            $mirror = dt_get_mapping_polygon_mirror();

            set_error_handler( [ $this, "warning_handler" ], E_WARNING );
            $list = file_get_contents( $mirror['url'] . 'available_locations.json' );
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
                            <p><input type="radio" id="amazon" name="source"
                                      value="amazon" <?php echo ( isset( $mirror['key'] ) && $mirror['key'] === 'amazon' ) ? 'checked' : '' ?>><label
                                        for="amazon"><?php echo esc_html( $mirror_list['amazon']['label'] ) ?></label>
                            </p>
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
                                            href="https://github.com/DiscipleTools/dt-mapping-data/archive/master.zip">Download
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
                    <th>Geocoding Provider Setup</th>
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

            <?php else : ?>
                <?php esc_html__( 'You must save post before geocoding.' ) ?>
            <?php
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
            if ( isset( $_POST['rebuild_geonames'] )
                && ( isset( $_POST['_wpnonce'] )
                    && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'rebuild_geonames' . get_current_user_id() ) ) ) {

                $this->rebuild_geonames();
            }

            if ( isset( $_POST['reset_geonames'] )
                && ( isset( $_POST['_wpnonce'] )
                    && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'rebuild_geonames' . get_current_user_id() ) ) ) {

                $this->rebuild_geonames( true );
            }
            ?>
            <!-- Box -->
            <form method="post">
                <?php wp_nonce_field( 'rebuild_geonames' . get_current_user_id() ); ?>
                <table class="widefat striped">
                    <thead>
                    <th>Alternative Install for Geonames</th>
                    </thead>
                    <tbody>
                    <tr>
                        <td>
                            Current Geoname
                            Records: <?php echo esc_attr( DT_Mapping_Module::instance()->query( 'count_geonames' ) ) ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <p>
                                <button type="button" class="button"
                                        onclick="jQuery('#check_rebuild').show();jQuery(this).prop('disabled', 'disabled')">
                                    Add Geonames
                                </button>
                            </p>
                            <span id="check_rebuild" style="display:none;">
                                <button type="submit" class="button" name="rebuild_geonames" value="1">Are you sure you want to add geonames?</button>
                            </span>
                        </td>
                    </tr>
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

        public function rebuild_geonames( $reset = false ) {
            global $wpdb;

            $dir = wp_upload_dir();
            $uploads_dir = trailingslashit( $dir['basedir'] );
            $file = 'dt_geonames.tsv';
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
            $wpdb->query( "SHOW TABLES LIKE $wpdb->dt_geonames" );
            if ( $wpdb->num_rows < 1 ) {
                require_once( get_template_directory() . '/dt-mapping/migrations/0000-initial.php' );
                $download = new DT_Mapping_Module_Migration_0000();
                $download->up();

                $wpdb->query( "SHOW TABLES LIKE $wpdb->dt_geonames" );
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
            while ( ! feof( $fp ) ) {
                $line = fgets( $fp, 2048 );

                $data = str_getcsv( $line, "\t" );

                if ( isset( $data[28] ) ) {
                    $wpdb->query( $wpdb->prepare( "
                        INSERT IGNORE INTO $wpdb->dt_geonames
                        VALUES (%d,%s,%s,%s,%d,%d,%s,%s,%s,%s,%s,%s,%s,%s,%d,%d,%s,%s,%s,%d,%d,%d,%d,%d,%s,%s,%d,%d,%d)",
                        $data[0],
                        $data[1],
                        $data[2],
                        $data[3],
                        $data[4],
                        $data[5],
                        $data[6],
                        $data[7],
                        $data[8],
                        $data[9],
                        $data[10],
                        $data[11],
                        $data[12],
                        $data[13],
                        $data[14],
                        $data[15],
                        $data[16],
                        $data[17],
                        $data[18],
                        $data[19],
                        $data[20],
                        $data[21],
                        $data[22],
                        $data[23],
                        $data[24],
                        $data[25],
                        $data[26],
                        $data[27],
                        $data[28]
                    ) );
                }
            }

            dt_write_log( 'end geonames install: ' . microtime() );

            fclose( $fp );
        }
    }

    DT_Mapping_Module_Admin::instance();
}