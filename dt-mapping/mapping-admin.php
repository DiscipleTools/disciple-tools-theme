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

                function update(grid_id, value, key) {
                    if (value) {
                        jQuery('#button-' + grid_id).append(`<span><img src="<?php echo esc_url_raw( spinner() ) ?>" width="20px" /></span>`)

                        let update = send_update({key: key, value: value, grid_id: grid_id})

                        update.done(function (data) {
                            if (data) {
                                jQuery('#label-' + grid_id).html(`${value}`)
                                jQuery('#input-' + grid_id).val('')
                                jQuery('#button-' + grid_id + ' span').remove()
                            }
                        })
                    }
                }

                function reset(grid_id, key) {
                    jQuery('#reset-' + grid_id).append(`<span><img src="<?php echo esc_url_raw( spinner() ) ?>" width="20px" /></span>`)

                    let update = send_update({key: key, reset: true, grid_id: grid_id})

                    update.done(function (data) {
                        if (data.status === 'OK') {
                            jQuery('#label-' + grid_id).html(`${data.value}`)
                            jQuery('#input-' + grid_id).val('')
                            jQuery('#reset-' + grid_id + ' span').remove()
                        }
                    })
                    update.fail(function (e) {
                        jQuery('#reset-' + grid_id + ' span').remove()
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
                /* used in connection boxes, Polygons and Geocoding tabs */
                .connected {
                    padding: 10px;
                    background-color: lightgreen;
                }
                .not-connected {
                    padding: 10px;
                    background-color: red;
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
            // Mabox Mapping API
            wp_enqueue_script( 'mapbox-gl', 'https://api.mapbox.com/mapbox-gl-js/v1.1.0/mapbox-gl.js', [ 'jquery','lodash' ], '1.1.0', false );
            wp_enqueue_style( 'mapbox-gl-css', 'https://api.mapbox.com/mapbox-gl-js/v1.1.0/mapbox-gl.css', [], '1.1.0' );

            // Drill Down Tool
            wp_enqueue_script( 'mapping-drill-down', get_template_directory_uri() . '/dt-mapping/drill-down.js', [ 'jquery','lodash' ], '1.1' );
            wp_localize_script(
                'mapping-drill-down', 'mappingModule', array(
                    'mapping_module' => DT_Mapping_Module::instance()->localize_script(),
                )
            );
        }

        public function process_rest_edits( $params ) {
            if ( isset( $params['key'] ) && isset( $params['grid_id'] ) ) {
                $grid_id = (int) sanitize_key( wp_unslash( $params['grid_id'] ) );
                $value = false;
                if ( isset( $params['value'] ) ) {
                    $value = sanitize_text_field( wp_unslash( $params['value'] ) );
                }

                global $wpdb;

                switch ( $params['key'] ) {
                    case 'name':
                        if ( isset( $params['reset'] ) && $params['reset'] === true ) {
                            // get the original name for the grid_id
                            $wpdb->query( $wpdb->prepare( "
                                UPDATE $wpdb->dt_location_grid
                                SET alt_name=name
                                WHERE grid_id = %d
                            ", $grid_id ) );

                            $name = $wpdb->get_var( $wpdb->prepare( "
                                SELECT alt_name as name FROM $wpdb->dt_location_grid WHERE grid_id = %d
                            ", $grid_id ) );

                            return [
                                'status' => 'OK',
                                'value'  => $name,
                            ];
                        } elseif ( $value ) {
                            $update_id = $wpdb->update(
                                $wpdb->dt_location_grid,
                                [ 'alt_name' => $value ],
                                [ 'grid_id' => $grid_id ],
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
                            // get the original name for the grid_id
                            $wpdb->query( $wpdb->prepare( "
                                UPDATE $wpdb->dt_location_grid
                                SET alt_population=NULL
                                WHERE grid_id = %d
                            ", $grid_id ) );

                            $population = $wpdb->get_var( $wpdb->prepare( "
                                SELECT population FROM $wpdb->dt_location_grid WHERE grid_id = %d
                            ", $grid_id ) );

                            return [
                                'status' => 'OK',
                                'value'  => $population,
                            ];
                        } elseif ( $value ) {
                            $update_id = $wpdb->update(
                                $wpdb->dt_location_grid,
                                [ 'alt_population' => $value ],
                                [ 'grid_id' => $grid_id ],
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

                        dt_write_log( $params );

                        if ( isset( $params['value']['name'] ) && ! empty( $params['value']['name'] ) ) {
                            $name = sanitize_text_field( wp_unslash( $params['value']['name'] ) );
                        } else {
                            return new WP_Error( 'missing_param', 'Missing name parameter' );
                        }

                        if ( ! empty( $params['value']['population'] ) ) {
                            $population = sanitize_text_field( wp_unslash( $params['value']['population'] ) );
                        } else {
                            $population = 0;
                        }

                        $custom_grid_id = $this->add_sublocation_under_location_grid( $grid_id, $name, $population );

                        return [
                                'name' => $name,
                                'grid_id' => $custom_grid_id
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
            if ( (int) get_option( 'dt_mapping_module_migration_lock', 0 ) ) :
                $last_migration_error = get_option( 'dt_mapping_module_migrate_last_error', false ); ?>
                <form method="post">
                <?php if ( empty( $last_migration_error ) ) :?>
                    <h3>Could not open the mapping system</h3>

                    <p>The migration might still be in progress. Please wait a couple minutes and try <strong>refreshing</strong> this page.
                        If this problem persists click the retry button below.</p>
                    Click here to refresh <button type="submit" name="refresh" value="1">Refresh</button>
                <?php else : ?>
                    <h3>Something went wrong with the mapping system.</h3>
                <?php endif; ?>
                    <h4></h4>
                    <?php wp_nonce_field( 'reset_mapping', 'reset_mapping_nonce' ) ?>
                    Retry setting up the mapping system:
                    <button type="submit" name="reset" value="1">Retry</button>
                </form>
                <br>

                <?php if ( !empty( $last_migration_error ) ) {
                    if ( isset( $last_migration_error["message"] ) ) : ?>
                        <strong>Error message:</strong>
                        <p>Cannot migrate, as migration lock is held. This is the last error: <strong><?php echo esc_html( $last_migration_error["message"] ); ?></strong></p>
                    <?php else :
                        var_dump( "Cannot migrate, as migration lock is held. This is the previous stored migration error: " . var_export( $last_migration_error, true ) );
                    endif;
                }
                die();
            endif;

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

                    <!-- Polygon -->
                    <a href="<?php echo esc_attr( $link ) . 'polygons' ?>" class="nav-tab
                        <?php echo esc_attr( ( $tab == 'polygons' ) ? 'nav-tab-active' : '' ); ?>">
                        <?php esc_attr_e( 'Polygons', 'disciple_tools' ) ?>
                    </a>
                    <!-- Levels -->
                    <a href="<?php echo esc_attr( $link ) . 'levels' ?>" class="nav-tab
                        <?php echo esc_attr( ( $tab == 'levels' ) ? 'nav-tab-active' : '' ); ?>">
                        <?php esc_attr_e( 'Levels', 'disciple_tools' ) ?>
                    </a>
                    <!-- Geocoding -->
                    <a href="<?php echo esc_attr( $link ) . 'geocoding' ?>" class="nav-tab
                        <?php echo esc_attr( ( $tab == 'geocoding' ) ? 'nav-tab-active' : '' ); ?>">
                        <?php esc_attr_e( 'Geocoding', 'disciple_tools' ) ?>
                    </a>
                    <!-- Names Tab -->
                    <a href="<?php echo esc_attr( $link ) . 'names' ?>" class="nav-tab
                        <?php echo esc_attr( ( $tab == 'names' ) ? 'nav-tab-active' : '' ); ?>">
                        <?php esc_attr_e( 'Names and IDs', 'disciple_tools' ) ?>
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
                    <a href="<?php echo esc_attr( $link ) . 'credits' ?>" class="nav-tab
                        <?php echo esc_attr( ( $tab == 'credits' ) ? 'nav-tab-active' : '' ); ?>">
                        <?php esc_attr_e( 'Credits', 'disciple_tools' ) ?>
                    </a>

                </h2>

                <?php
                switch ( $tab ) {
                    case "general":
                        $this->tab_general_settings();
                        break;
                    case "focus":
                        $this->tab_mapping_focus();
                        break;
                    case "polygons":
                        $this->tab_polygons();
                        break;
                    case "levels":
                        $this->tab_levels();
                        break;
                    case "geocoding":
                        $this->tab_geocoding();
                        break;
                    case "names":
                        $this->tab_names();
                        break;
                    case "population":
                        $this->tab_population();
                        break;
                    case "sub_locations":
                        $this->tab_sub_locations();
                        break;
                    case "migration":
                        $this->tab_migration();
                        break;
                    case "credits":
                        $this->box_credits();
                        break;
                    default:
                        break;
                }
                ?>
            </div><!-- End wrap -->
            <?php
        }

        public function tab_general_settings() {
            ?>
            <div class="wrap">
                <div id="poststuff">
                    <div id="post-body" class="metabox-holder columns-2">
                        <div id="post-body-content">
                            <!-- Main Column -->

                            <?php $this->box_general_settings() ?>


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

        public function tab_mapping_focus() {
            ?>
            <div class="wrap">
                <div id="poststuff">
                    <div id="post-body" class="metabox-holder columns-2">
                        <div id="post-body-content">
                            <!-- Main Column -->

                            <?php $this->box_mapping_focus_start_level(); ?>

                            <!-- End Main Column -->
                        </div><!-- end post-body-content -->
                        <div id="postbox-container-1" class="postbox-container">
                            <!-- Right Column -->

                            <?php $this->box_mapping_focus_instructions() ?>

                            <!-- End Right Column -->
                        </div><!-- postbox-container 1 -->
                        <div id="postbox-container-2" class="postbox-container">
                        </div><!-- postbox-container 2 -->
                    </div><!-- post-body meta box container -->
                </div><!--poststuff end -->
            </div><!-- wrap end -->
            <?php
        }

        public function tab_polygons() {
            ?>
            <div class="wrap">
                <div id="poststuff">
                    <div id="post-body" class="metabox-holder columns-1">
                        <div id="post-body-content">
                            <!-- Main Column -->

                            <?php $this->box_polygons_select_mirror(); ?>

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

        public function tab_levels() {
            ?>
            <div class="wrap">
                <div id="poststuff">
                    <div id="post-body" class="metabox-holder columns-2">
                        <div id="post-body-content">
                            <!-- Main Column -->

                            <?php $this->box_levels(); ?>

                            <!-- End Main Column -->
                        </div><!-- end post-body-content -->
                        <div id="postbox-container-1" class="postbox-container">
                            <!-- Right Column -->

                            <?php $this->box_levels_instructions(); ?>

                            <!-- End Right Column -->
                        </div><!-- postbox-container 1 -->
                        <div id="postbox-container-2" class="postbox-container">
                        </div><!-- postbox-container 2 -->
                    </div><!-- post-body meta box container -->
                </div><!--poststuff end -->
            </div><!-- wrap end -->
            <?php
        }

        public function tab_geocoding() {
            ?>
            <div class="wrap">
                <div id="poststuff">
                    <div id="post-body" class="metabox-holder columns-1">
                        <div id="post-body-content">
                            <!-- Main Column -->

                            <?php $this->box_geocoding_source(); ?>

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

        public function tab_names() {
            ?>
            <div class="wrap">
                <div id="poststuff">
                    <div id="post-body" class="metabox-holder columns-1">
                        <div id="post-body-content">
                            <!-- Main Column -->

                            <?php $this->box_names_editor() ?>

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

        public function tab_population() {
            ?>
            <div class="wrap">
                <div id="poststuff">
                    <div id="post-body" class="metabox-holder columns-1">
                        <div id="post-body-content">
                            <!-- Main Column -->

                            <?php $this->box_population_division(); ?>

                            <?php $this->box_population_edit(); ?>

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

        public function tab_sub_locations() {
            ?>
            <div class="wrap">
                <div id="poststuff">
                    <div id="post-body" class="metabox-holder columns-1">
                        <div id="post-body-content">
                            <!-- Main Column -->

                            <?php $this->box_sub_locations_editor() ?>

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

        public function tab_migration() {
            ?>
            <div class="wrap">
                <div id="poststuff">
                    <div id="post-body" class="metabox-holder columns-1">
                        <div id="post-body-content">
                            <!-- Main Column -->

                            <?php $this->box_migration_status() ?>
                            <br>
                            <?php $this->box_migration_rebuild_location() ?>
                            <br>
                            <?php $this->box_migration_from_locations() ?>


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



        /**
         * Admin Page Metaboxes
         */

        public function box_general_settings() {
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
                        $mirror = dt_get_location_grid_mirror();
                        echo esc_attr( $mirror['label'] ) ?? '';
                        ?>
                    </td>
                    <td>
                        <a href="admin.php?page=dt_mapping_module&tab=polygons">Edit</a>
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

        public function box_mapping_focus_start_level() {
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
                else if ( $option['type'] === 'state' && ( !isset( $_POST['children'] ) || empty( $_POST['children'] ) && !empty( $_POST['parent'] ) ) ) {
                    $list = Disciple_Tools_Mapping_Queries::get_children_by_grid_id( $option['parent'] );
                    foreach ( $list as $item ) {
                        $option['children'][] = $item['grid_id'];
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
                                                    echo '<input id="' . esc_attr( $country['grid_id'] ) . '" class="country-item" type="checkbox" name="children[]" value="' . esc_attr( $country['grid_id'] ) . '"';
                                                    if ( array_search( $country['grid_id'], $default_map_settings['children'] ) !== false ) {
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
                                        $country_ids .= $country['grid_id'];
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
                                        echo '<option value="' . esc_attr( $result['grid_id'] ) . '" ';
                                        if ( $default_map_settings['parent'] === (int) $result['grid_id'] ) {
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
                        $parent = Disciple_Tools_Mapping_Queries::get_by_grid_id( $country_id );
                        $state_list = Disciple_Tools_Mapping_Queries::get_children_by_grid_id( $country_id );

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
                                            echo '<input id="' . esc_attr( $value['grid_id'] ) . '" class="country-item" type="checkbox" name="children[]" value="' . esc_attr( $value['grid_id'] ) . '"';
                                            if ( array_search( $value['grid_id'], $default_map_settings['children'] ) !== false ) {
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

        public function box_mapping_focus_instructions() {

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

        public function box_polygons_select_mirror() {

            /**
             * https://storage.googleapis.com/disciple-tools-maps/
             * https://raw.githubusercontent.com/DiscipleTools/location-grid-project/master/
             * https://s3.amazonaws.com/mapping-source/
             */
            $mirror_list = [
                'google' => [
                    'key'   => 'google',
                    'label' => 'Google',
                    'url'   => 'https://storage.googleapis.com/location-grid-mirror/',
                ],
                'amazon' => [
                    'key'   => 'amazon',
                    'label' => 'Amazon',
                    'url'   => 'https://location-grid-mirror.s3.amazonaws.com/',
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
                        update_option( 'dt_location_grid_mirror', $array, true );
                    }
                } elseif ( $selection_key !== 'other' ) {
                    $array = [
                        'key'   => $selection_key,
                        'label' => $mirror_list[$selection_key]['label'],
                        'url'   => $mirror_list[$selection_key]['url'],
                    ];
                    update_option( 'dt_location_grid_mirror', $array, true );
                }
            }

            $mirror = dt_get_location_grid_mirror();

            set_error_handler( [ $this, "warning_handler" ], E_WARNING );
            $list = file_get_contents( $mirror['url'] . 'low/1.geojson' );
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

            <form method="post">
                <table class="widefat striped">
                    <thead>
                    <th>Set the Mirror Source for Mapping Polygons</th>
                    </thead>
                    <tbody>
                    <tr>
                        <td>
                            <?php wp_nonce_field( 'set_polygon_mirror' . get_current_user_id() ); ?>

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
                                        href="https://github.com/DiscipleTools/location-grid-project/">GitHub Location Grid Project</a>) and install
                                    this folder to your own mirror. You will be responsible for syncing occasional
                                    updates to the folder. But this allows you to obscure traffic to these default mirrors, if you
                                    have security concerns with from your country.
                                </em>
                            </p>
                            <p>
                                <strong>Other Notes:</strong><br>
                                <em>The polygons that make up of the boarders for each country, state, and county are a
                                    significant amount of data. Mapping has broken these up into individual files that are stored at
                                    various mirror locations. You can choose the mirror that works for you and your country, or
                                    you can host your own mirror for security reasons.
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

        public function box_levels() {
            if ( isset( $_POST['install_level_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['install_level_nonce'] ) ), 'install_level' . get_current_user_id() ) ) {

                if ( isset( $_POST['country_select'] ) && ! empty( $_POST['country_select'] ) ) {
                    $admin0_code = sanitize_text_field( wp_unslash( $_POST['country_select'] ) );
                    $this->install_additional_levels( $admin0_code );
                }

                if ( isset( $_POST['remove'] ) && ! empty( $_POST['remove'] ) ) {
                    $concat = explode( '-', sanitize_text_field( wp_unslash( $_POST['remove'] ) ) );
                    $admin0_code = $concat[0];
                    $level = $concat[1];
                    if ( ! empty( $admin0_code ) && ! empty( $level ) ) {
                        $this->remove_additional_levels( $admin0_code, $level );
                    }
                }
            }

            $theme_data = dt_get_theme_data_url();
            $json = json_decode( file_get_contents( $theme_data . 'location_grid/countries_with_extended_levels.json' ), true );
            if ( empty( $json ) ) {
                ?>
                <div class="notice notice-error notice-dt-locations-migration is-dismissible" data-notice="dt-locations-migration">
                    <p>Source of extended levels not found. Check https://github.com/DiscipleTools/location-grid-theme-data</p>
                </div>
                <?php
                return;
            }
            asort( $json );

            // get installed levels
            global $wpdb;
            $installed_levels = $wpdb->get_results("
                SELECT l.admin0_code, 
                (SELECT lg.name FROM $wpdb->dt_location_grid as lg WHERE lg.admin0_code = l.admin0_code AND lg.level = 0 LIMIT 1) as name,
                l.level, 
                count(l.level) as records 
                FROM $wpdb->dt_location_grid as l
                WHERE l.level > 2 AND l.level < 10 GROUP BY l.admin0_code, l.level;", ARRAY_A );

            // trim list
            $list = [];
            foreach ( $installed_levels as $installed_level ) {
                $list[$installed_level['admin0_code']] = $installed_level['admin0_code'];
            }

            ?>
            <form method="post">
                <?php wp_nonce_field( 'install_level' . get_current_user_id(), 'install_level_nonce' ); ?>
                <table class="widefat striped">
                    <thead>
                        <th>Install Additional Administrative Records</th>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <select name="country_select">
                                    <option></option>
                                    <?php
                                    foreach ( $json as $index => $name ) {
                                        if ( array_search( $index, $list ) !== false ) {
                                            continue; // skip already installed countries
                                        }
                                        echo '<option value="'.esc_attr( $index ).'">';
                                        echo esc_html( $name );
                                        echo '</option>';
                                    }
                                    ?>
                                </select>
                                <button type="submit" class="button">Install Additional Records</button>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <table class="widefat striped">
                    <tbody>
                    <?php
                    if ( ! empty( $installed_levels ) ) :
                        foreach ( $installed_levels as $level ) :

                            ?>
                        <tr>
                            <td>
                                <?php echo '<span style="font-size:1.2em;">' . esc_html( $level['name'] ) . '</span> ' ?>
                            </td>
                            <td>
                                <a onclick="jQuery('#<?php echo esc_attr( $level['admin0_code'] ).'-'. esc_attr( $level['level'] ) ?>').show();">Remove Level <?php echo esc_attr( $level['level'] ) . ' (' . esc_html( $level['records'] ) . ' records) ' ?></a>
                                <?php echo '<br><button type="submit" id="'.esc_attr( $level['admin0_code'] ).'-'.esc_attr( $level['level'] ).'" style="display:none;" name="remove" value="'.esc_attr( $level['admin0_code'] ).'-'.esc_attr( $level['level'] ).'"> Confirm delete ' . esc_html( $level['records'] ) . ' records?</button>' ?>
                            </td>
                        </tr>

                            <?php
                        endforeach;
                        endif;
                    ?>
                    </tbody>
                </table>
            </form>
            <?php
        }

        public function box_levels_instructions() {
            ?>
                <table class="widefat striped">
                    <thead>
                    <th>Install Additional Administrative Levels</th>
                    </thead>
                    <tbody>
                    <tr>
                        <td>
                            <p>By default, administrative levels 0-2 are installed in the Disciple Tools system, if available for the country. This means that the country record (level 0), the state record (level 1),
                                the county or variously named second level administrative level (level 2) is installed. But some countries have administrative divisions 3 - 5. The drop down list below contains
                                those countries with extra administrative levels.
                            </p>
                            <p>
                                Warning: Installing sub-levels for a country or two should have no noticeable speed impact on most servers, but a full install of all countries administrative levels will increase the database
                                from 50k records to 380k records. Running all records for the entire world should be evaluated based on your use-case and weighed in regards to the strength of your hosted server.
                            </p>
                        </td>
                    </tr>

                    </tbody>
                </table>
            <?php
        }

        public function box_population_division() {
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

        public function box_population_edit() {
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
                window.DRILLDOWN.population_edit = function (grid_id) {
                    let list_results = jQuery('#list_results')
                    let div = 'list_results'

                    // Find data source before build
                    if ( grid_id === 'top_map_level' ) {
                        let default_map_settings = DRILLDOWNDATA.settings.default_map_settings

                        // Initialize Location Data
                        let map_data = DRILLDOWNDATA.data[default_map_settings.parent]
                        if ( map_data === undefined ) {
                            console.log('error getting map_data')
                            return;
                        }

                        build_list( div, map_data )
                    }
                    else if ( DRILLDOWNDATA.data[grid_id] === undefined ) {
                        let rest = DRILLDOWNDATA.settings.endpoints.get_map_by_grid_id_endpoint

                        jQuery.ajax({
                            type: rest.method,
                            contentType: "application/json; charset=utf-8",
                            data: JSON.stringify( { 'grid_id': grid_id } ),
                            dataType: "json",
                            url: DRILLDOWNDATA.settings.root + rest.namespace + rest.route,
                            beforeSend: function(xhr) {
                                xhr.setRequestHeader('X-WP-Nonce', rest.nonce );
                            },
                        })
                            .done( function( response ) {
                                DRILLDOWNDATA.data[grid_id] = response
                                build_list( div, DRILLDOWNDATA.data[grid_id] )
                            })
                            .fail(function (err) {
                                console.log("error")
                                console.log(err)
                                DRILLDOWN.hide_spinner()
                            })

                    } else {
                        build_list( div, DRILLDOWNDATA.data[grid_id] )
                    }

                    function build_list( div, map_data ) {
                        list_results.empty()
                        jQuery.each( map_data.children, function (i, v) {
                            list_results.append(`<tr>
                                <td>${_.escape( v.name )}</td>
                                <td id="label-${_.escape( v.grid_id )}">${_.escape( v.population_formatted )}</td>
                                <td><input type="number" id="input-${_.escape( v.grid_id )}" value=""></td>
                                <td id="button-${_.escape( v.grid_id )}"><a class="button" onclick="update( ${_.escape( v.grid_id )}, jQuery('#input-'+${_.escape( v.grid_id )}).val(), 'population' )">Update</a></td>
                                <td id="reset-${_.escape( v.grid_id )}"><a class="button" onclick="reset( ${_.escape( v.grid_id )}, 'population' )">Reset</a></td>
                            </tr>`)
                        })
                    }
                }

            </script>

            <?php
        }

        public function box_names_editor() {
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
                window.DRILLDOWN.name_select = function (grid_id) {
                    let list_results = jQuery('#list_results')
                    let div = 'list_results'

                    // Find data source before build
                    if (grid_id === 'top_map_level') {
                        let default_map_settings = DRILLDOWNDATA.settings.default_map_settings

                        // Initialize Location Data
                        let map_data = DRILLDOWNDATA.data[default_map_settings.parent]
                        if (map_data === undefined) {
                            console.log('error getting map_data')
                            return;
                        }

                        build_list(div, map_data)
                    }
                    else if (DRILLDOWNDATA.data[grid_id] === undefined) {
                        let rest = DRILLDOWNDATA.settings.endpoints.get_map_by_grid_id_endpoint

                        jQuery.ajax({
                            type: rest.method,
                            contentType: "application/json; charset=utf-8",
                            data: JSON.stringify({'grid_id': grid_id}),
                            dataType: "json",
                            url: DRILLDOWNDATA.settings.root + rest.namespace + rest.route,
                            beforeSend: function (xhr) {
                                xhr.setRequestHeader('X-WP-Nonce', rest.nonce);
                            },
                        })
                            .done(function (response) {
                                DRILLDOWNDATA.data[grid_id] = response
                                build_list(div, DRILLDOWNDATA.data[grid_id])
                            })
                            .fail(function (err) {
                                console.log("error")
                                console.log(err)
                                DRILLDOWN.hide_spinner()
                            })

                    } else {
                        build_list(div, DRILLDOWNDATA.data[grid_id])
                    }

                    function build_list(div, map_data) {
                        list_results.empty()
                        jQuery.each(map_data.children, function (i, v) {
                            list_results.append(`<tr>
                                <td id="label-${_.escape( v.grid_id )}">${_.escape( v.name )}</td>
                                <td><input type="text" id="input-${_.escape( v.grid_id )}" value=""></td>
                                <td id="button-${_.escape( v.grid_id )}"><a class="button" onclick="update( ${_.escape( v.grid_id )}, jQuery('#input-'+${_.escape( v.grid_id )}).val(), 'name' )">Update</a></td>
                                <td id="reset-${_.escape( v.grid_id )}"><a class="button" onclick="reset( ${_.escape( v.grid_id )}, 'name' )">Reset</a></td>
                                <td>${_.escape( v.grid_id )}</td>
                            </tr>`)
                        })
                    }
                }
            </script>

            <?php

        }

        public function box_sub_locations_editor() {
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
                    let gnid = jQuery(this).data('grid_id')
                    console.log(gnid)
                    DRILLDOWN.get_drill_down( 'sublocation', gnid  );
                })

                window.DRILLDOWNDATA.settings.hide_final_drill_down = false
                window.DRILLDOWN.get_drill_down('sublocation')
                window.DRILLDOWN.sublocation = function (grid_id) {

                    let list_results = jQuery('#list_results')
                    let div = 'list_results'
                    let current_subs = jQuery('#current_subs')
                    let other_list = jQuery('#other_list')

                    list_results.empty()
                    other_list.empty()
                    current_subs.hide()

                    // Find data source before build
                    if (grid_id === 'top_map_level') {
                        let default_map_settings = DRILLDOWNDATA.settings.default_map_settings

                        // Initialize Location Data
                        let map_data = DRILLDOWNDATA.data[default_map_settings.parent]
                        if (map_data === undefined) {
                            console.log('error getting map_data')
                            return;
                        }

                        build_list(div, map_data)
                    }
                    else if ( DRILLDOWNDATA.data[grid_id] === undefined) {
                        let rest = DRILLDOWNDATA.settings.endpoints.get_map_by_grid_id_endpoint

                        jQuery.ajax({
                            type: rest.method,
                            contentType: "application/json; charset=utf-8",
                            data: JSON.stringify({'grid_id': grid_id}),
                            dataType: "json",
                            url: DRILLDOWNDATA.settings.root + rest.namespace + rest.route,
                            beforeSend: function (xhr) {
                                xhr.setRequestHeader('X-WP-Nonce', rest.nonce);
                            },
                        })
                            .done(function (response) {
                                DRILLDOWNDATA.data[grid_id] = response
                                build_list(div, DRILLDOWNDATA.data[grid_id])
                            })
                            .fail(function (err) {
                                console.log("error")
                                console.log(err)
                                DRILLDOWN.hide_spinner()
                            })

                    } else {
                        build_list(div, DRILLDOWNDATA.data[grid_id])
                    }

                    function build_list(div, map_data) {

                        if (!window.DRILLDOWN.isEmpty(map_data.children)) { // empty children for grid_id
                            jQuery.each(map_data.children, function (gnid, data) {
                                other_list.append(`
                                    <tr><td>
                                        <a class="open_next_drilldown" data-parent="${_.escape( data.parent_id )}" data-grid_id="${_.escape( data.grid_id )}" style="cursor: pointer;">${_.escape( data.name )}</a>
                                    </td><td></td></tr>`)
                            })
                            current_subs.show()
                        }

                        list_results.empty().append(`
                                <tr><td colspan="2">Add New Location under ${_.escape( map_data.self.name )}</td></tr>
                                <tr><td style="width:150px;">Name</td><td><input id="new_name" value="" /></td></tr>
                                <tr><td>Longitude</td><td><input id="new_longitude" value="" /></td></tr>
                                <tr><td>Latitude</td><td><input id="new_latitude" value="" /></td></tr>
                                <tr><td>Population</td><td><input id="new_population" value="" /></td></tr>
                                <tr><td colspan="2"><button type="button" id="save-button" class="button" onclick="update_location( ${_.escape( map_data.self.grid_id )} )" >Save</a></td></tr>`)
                    }
                }

                function update_location(grid_id) {
                    jQuery('#save-button').prop('disabled', true)

                    let data = {}
                    data.key = 'sub_location'
                    data.grid_id = grid_id
                    data.value = {}
                    data.value.name = jQuery('#new_name').val()
                    data.value.population = jQuery('#new_population').val()

                    console.log(data)

                    let update = send_update(data)

                    update.done(function (data) {
                        console.log(data)
                        if (data) {
                            jQuery('#other_list').append(`
                                <tr><td><a class="open_next_drilldown" data-parent="${_.escape( grid_id )}" data-grid_id="${_.escape( data.grid_id )}" style="cursor: pointer;">${_.escape(data.name)}</a></td></tr>`)
                            jQuery('#new_name').val('')
                            jQuery('#new_population').val('')
                            jQuery('#current_subs').show()
                        }
                        jQuery('#save-button').removeProp('disabled')
                    })

                    console.log(grid_id)
                }
            </script>

            <?php

        }

        public function box_geocoding_source() {
            if ( isset( $_POST['mapbox_key'] )
                 && ( isset( $_POST['geocoding_key_nonce'] )
                      && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['geocoding_key_nonce'] ) ), 'geocoding_key' . get_current_user_id() ) ) ) {

                $key = sanitize_text_field( wp_unslash( $_POST['mapbox_key'] ) );
                if ( empty( $key ) ) {
                    delete_option( 'dt_mapbox_api_key' );
                } else {
                    update_option( 'dt_mapbox_api_key', $key, true );
                }
            }
            $key = get_option( 'dt_mapbox_api_key' );
            $hidden_key = '**************' . substr( $key, -5, 5 );

            set_error_handler( [ $this, "warning_handler" ], E_WARNING );
            $list = file_get_contents( 'https://api.mapbox.com/geocoding/v5/mapbox.places/Denver.json?access_token=' . $key );
            restore_error_handler();

            if ( $list ) {
                $status_class = 'connected';
                $message = 'Successfully connected to selected source.';
            } else {
                $status_class = 'not-connected';
                $message = 'API NOT AVAILABLE';
            }
            ?>
            <form method="post">
                <table class="widefat striped">
                    <thead>
                    <tr><th>MapBox.com</th></tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td>
                            <?php wp_nonce_field( 'geocoding_key' . get_current_user_id(), 'geocoding_key_nonce' ); ?>
                            Mapbox API Token: <input type="text" class="regular-text" name="mapbox_key" value="<?php echo ( $key ) ? esc_attr( $hidden_key ) : ''; ?>" /> <button type="submit" class="button">Update</button>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <p id="reachable_source" class="<?php echo esc_attr( $status_class ) ?>">
                                <?php echo esc_html( $message ); ?>
                            </p>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </form>
            <br>

            <?php if ( empty( get_option( 'dt_mapbox_api_key' ) ) ) : ?>
                <table class="widefat striped">
                    <thead>
                    <tr><th>MapBox.com Instructions</th></tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td>
                            <ol>
                                <li>
                                    Go to <a href="https://www.mapbox.com/">MapBox.com</a>.
                                </li>
                                <li>
                                    Register for a new account (<a href="https://account.mapbox.com/auth/signup/">MapBox.com</a>)<br>
                                    <em>(email required, no credit card required)</em>
                                </li>
                                <li>
                                    Once registered, go to your account home page. (<a href="https://account.mapbox.com/">Account Page</a>)<br>
                                </li>
                                <li>
                                    Inside the section labeled "Access Tokens", either create a new token or use the default token provided. Copy this token.
                                </li>
                                <li>
                                    Paste the token into the "Mapbox API Token" field in the box above.
                                </li>
                            </ol>
                        </td>
                    </tr>
                    </tbody>
                </table>
                <br>
            <?php endif; ?>

            <?php if ( ! empty( get_option( 'dt_mapbox_api_key' ) ) ) : ?>
                <table class="widefat striped">
                    <thead>
                    <tr><th>Geocoding Test</th></tr>
                    </thead>
                    <tbody>

                    <tr>
                        <td>
                            <!-- Geocoder Input Section -->
                            <?php // @codingStandardsIgnoreStart ?>
                            <script src='https://api.mapbox.com/mapbox-gl-js/plugins/mapbox-gl-geocoder/v4.4.0/mapbox-gl-geocoder.min.js'></script>
                            <link rel='stylesheet' href='https://api.mapbox.com/mapbox-gl-js/plugins/mapbox-gl-geocoder/v4.4.0/mapbox-gl-geocoder.css' type='text/css' />
                            <?php // @codingStandardsIgnoreEnd ?>
                            <style>
                                .mapboxgl-ctrl-geocoder {
                                    min-width:100%;
                                }
                                #geocoder {
                                    padding-bottom: 10px;
                                }
                                #map {
                                    width:66%;
                                    height:400px;
                                    float:left;
                                }
                                #list {
                                    width:33%;
                                    float:right;
                                }
                                #selected_values {
                                    width:66%;
                                    float:left;
                                }
                                .result_box {
                                    padding: 15px 10px;
                                    border: 1px solid lightgray;
                                    margin: 5px 0 0;
                                    font-weight: bold;
                                }
                                .add-column {
                                    width:10px;
                                }
                            </style>

                            <!-- Widget -->
                            <div id='geocoder' class='geocoder'></div>
                            <div>
                                <div id='map'></div>
                                <div id="list"></div>
                            </div>
                            <div id="selected_values"></div>

                            <!-- Mapbox script -->
                            <script>
                                mapboxgl.accessToken = '<?php echo esc_html( get_option( 'dt_mapbox_api_key' ) ) ?>';
                                var map = new mapboxgl.Map({
                                    container: 'map',
                                    style: 'mapbox://styles/mapbox/streets-v11',
                                    center: [-20, 30],
                                    zoom: 1
                                });

                                map.addControl(new mapboxgl.NavigationControl());

                                var geocoder = new MapboxGeocoder({
                                    accessToken: mapboxgl.accessToken,
                                    types: 'country region district postcode locality neighborhood address place', //'country region district postcode locality neighborhood address place',
                                    marker: {color: 'orange'},
                                    mapboxgl: mapboxgl
                                });

                                document.getElementById('geocoder').appendChild(geocoder.onAdd(map));

                                // After Search Result
                                geocoder.on('result', function(e) { // respond to search
                                    geocoder._removeMarker()
                                    console.log(e)
                                })


                                map.on('click', function (e) {
                                    console.log(e)

                                    let lng = e.lngLat.lng
                                    let lat = e.lngLat.lat
                                    window.active_lnglat = [lng,lat]

                                    // add marker
                                    if ( window.active_marker ) {
                                        window.active_marker.remove()
                                    }
                                    window.active_marker = new mapboxgl.Marker()
                                        .setLngLat(e.lngLat )
                                        .addTo(map);
                                    console.log(active_marker)

                                    // add polygon
                                    jQuery.get('<?php echo esc_url( trailingslashit( get_template_directory_uri() ) ) . 'dt-mapping/' ?>location-grid-list-api.php',
                                        {
                                            type: 'possible_matches',
                                            longitude: lng,
                                            latitude:  lat,
                                            nonce: '<?php echo esc_html( wp_create_nonce( 'location_grid' ) ) ?>'
                                        }, null, 'json' ).done(function(data) {

                                        console.log(data)
                                        if ( data !== undefined ) {
                                            print_click_results( data )
                                        }

                                    })
                                });


                                // User Personal Geocode Control
                                let userGeocode = new mapboxgl.GeolocateControl({
                                    positionOptions: {
                                        enableHighAccuracy: true
                                    },
                                    marker: {
                                        color: 'orange'
                                    },
                                    trackUserLocation: false
                                })
                                map.addControl(userGeocode);
                                userGeocode.on('geolocate', function(e) { // respond to search
                                    console.log(e)
                                    let lat = e.coords.latitude
                                    let lng = e.coords.longitude
                                    window.active_lnglat = [lng,lat]

                                    // add polygon
                                    jQuery.get('<?php echo esc_url( trailingslashit( get_template_directory_uri() ) ) . 'dt-mapping/' ?>location-grid-list-api.php',
                                        {
                                            type: 'possible_matches',
                                            longitude: lng,
                                            latitude:  lat,
                                            nonce: '<?php echo esc_html( wp_create_nonce( 'location_grid' ) ) ?>'
                                        }, null, 'json' ).done(function(data) {
                                        console.log(data)

                                        if ( data !== undefined ) {

                                            print_click_results(data)
                                        }
                                    })
                                })

                                jQuery(document).ready(function() {
                                    jQuery('input.mapboxgl-ctrl-geocoder--input').attr("placeholder", "Enter Country")
                                })


                                function print_click_results( data ) {
                                    if ( data !== undefined ) {

                                        // print click results
                                        window.MBresponse = data

                                        let print = jQuery('#list')
                                        print.empty();
                                        print.append('<strong>Click Results</strong><br><hr>')
                                        let table_body = ''
                                        jQuery.each( data, function(i,v) {
                                            let string = '<tr><td class="add-column">'
                                            string += '<button onclick="add_selection(' + v.grid_id +')">Add</button></td> '
                                            string += '<td><strong style="font-size:1.2em;">'+v.name+'</strong> <br>'
                                            if ( v.admin0_name !== v.name ) {
                                                string += v.admin0_name
                                            }
                                            if ( v.admin1_name !== null ) {
                                                string += ' > ' + v.admin1_name
                                            }
                                            if ( v.admin2_name !== null ) {
                                                string += ' > ' + v.admin2_name
                                            }
                                            if ( v.admin3_name !== null ) {
                                                string += ' > ' + v.admin3_name
                                            }
                                            if ( v.admin4_name !== null ) {
                                                string += ' > ' + v.admin4_name
                                            }
                                            if ( v.admin5_name !== null ) {
                                                string += ' > ' + v.admin5_name
                                            }
                                            string += '</td></tr>'
                                            table_body += string
                                        })
                                        print.append('<table>' + table_body + '</table>')
                                    }
                                }

                                function add_selection( grid_id ) {
                                    console.log(window.MBresponse[grid_id])

                                    let div = jQuery('#selected_values')
                                    let response = window.MBresponse[grid_id]

                                    if ( window.selected_locations === undefined ) {
                                        window.selected_locations = []
                                    }
                                    window.selected_locations[grid_id] = new mapboxgl.Marker()
                                        .setLngLat( [ window.active_lnglat[0], window.active_lnglat[1] ] )
                                        .addTo(map);

                                    let name = ''
                                    name += response.name
                                    if ( response.admin1_name !== undefined && response.level > '1' ) {
                                        name += ', ' + response.admin1_name
                                    }
                                    if ( response.admin0_name && response.level > '0' ) {
                                        name += ', ' + response.admin0_name
                                    }

                                    div.append('<div class="result_box" id="'+grid_id+'">' +
                                        '<span>'+name+'</span>' +
                                        '<span style="float:right;cursor:pointer;" onclick="remove_selection(\''+grid_id+'\')">X</span>' +
                                        '<input type="hidden" name="selected_grid_id['+grid_id+']" value="' + grid_id + '" />' +
                                        '<input type="hidden" name="selected_lnglat['+grid_id+']" value="' + window.active_lnglat[0] + ',' + window.active_lnglat[1] + '" />' +
                                        '</div>')

                                }

                                function remove_selection( grid_id ) {
                                    window.selected_locations[grid_id].remove()
                                    jQuery('#' + grid_id ).remove()
                                }


                            </script>
                        </td>
                    </tr>
                    </tbody>
                </table>
            <?php endif; ?>

            <?php
        }

        public function box_migration_from_locations( $return = false ){
            if ( isset( $_POST["location_migrate_nonce"] ) && wp_verify_nonce( sanitize_key( $_POST['location_migrate_nonce'] ), 'save' ) ) {
                if ( isset( $_POST["run-migration"], $_POST["selected_location_grid"] ) ){
                    $select_location_grid = dt_sanitize_array_html( $_POST["selected_location_grid"] ); //phpcs:ignore
                    $saved_for_migration = get_option( "dt_mapping_migration_list", [] );
                    foreach ( $select_location_grid as $location_id => $migration_values ){
                        if ( !empty( $location_id ) && !empty( $migration_values["migration_type"] ) ) {
                            $location_id = sanitize_text_field( wp_unslash( $location_id ) );
                            $selected_location_grid = sanitize_text_field( wp_unslash( $migration_values["geoid"] ) );
                            $migration_type = sanitize_text_field( wp_unslash( $migration_values["migration_type"] ) );
                            $location = get_post( $location_id );
                            if ( empty( $selected_location_grid )){
                                $selected_location_grid = '1';
                            }
                            $location_grid = Disciple_Tools_Mapping_Queries::get_by_grid_id( $selected_location_grid );
                            if ( $migration_type === "sublocation" ){
                                $selected_location_grid = $this->add_sublocation_under_location_grid( $selected_location_grid, $location->post_title, 0 );
                            }
                            $this->convert_location_to_location_grid( $location_id, $selected_location_grid );

                            $message = $migration_type === "convert" ?
                                "Converted $location->post_title to " . $location_grid["name"] :
                                "Created $location->post_title as sub-location under " . $location_grid["name"];
                            ?>
                            <div class="notice notice-success is-dismissible">
                                <p>Successfully ran action: <?php echo esc_html( $message )?></p>
                            </div>
                            <?php
                            $saved_for_migration[$location_id] = [
                                "message" => $message,
                                "migration_type" => $migration_type,
                                "location_id" => $location_id,
                                "selected_location_grid" => $selected_location_grid
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

            if ( $return ) {
                return [
                    'locations_with_records' => $locations_with_records ?: [],
                    'saved_for_migration' => $saved_for_migration ?: [],
                ];
            }


            if ( sizeof( $locations_with_records ) === 0 ) {
                $migration_done = get_option( "dt_locations_migrated_to_location_grid", false );
                if ( !$migration_done ){
                    $this->migrate_user_filters_to_location_grid();
                    update_option( "dt_locations_migrated_to_location_grid", true );
                }
            } else {
                ?>
                <!-- Conversion Report -->
                <table class="widefat striped" name="locations-completed">
                    <thead>
                    <tr>
                        <th>Migrated Locations ( <?php echo esc_html( sizeof( $saved_for_migration ) ) ?>)</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td>
                            <ul style="list-style: disc">
                                <?php foreach ( $saved_for_migration as $location_id => $migration_values ) : ?>
                                    <li style="margin-inline-start: 40px"><?php echo esc_html( $migration_values["message"] ) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </td>
                    </tr>
                    </tbody>
                    <tr></tr>
                </table>

                <!-- Migration Utility -->
                <span id="locations-remaining" name="locations-remaining"></span>
                    <div style="display:none;">
                        <h1>About</h1>
                        <p>Thank you for completing this important step in using D.T.</p>
                        <p>This tool is to help you migrate from the old locations system, to the new one that uses <a target="_blank" href="https://www.location_grid.org/about.html">GeoNames</a>  as it's base. GeoNames is a free database of countries and regions and will help us achieve better collaborate across instances. </p>
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
                    </div>
                    <form method="post" action="">
                        <?php wp_nonce_field( 'save', 'location_migrate_nonce', true, true ) ?>
                        <h3>Remaining Locations to Migrate ( <?php echo esc_html( sizeof( $locations_with_records ) ) ?> )</h3>

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
                                        <input name="selected_location_grid[<?php echo esc_html( $location["ID"] ) ?>][geoid]" class="convert-input" type="hidden">
                                        <div class="drilldown">
                                            <?php DT_Mapping_Module::instance()->drill_down_widget( esc_html( $location["ID"] ) . "_sublocation .drilldown" ) ?>
                                        </div>
                                    </td>
                                    <td id="<?php echo esc_html( $location["ID"] ) ?>_buttons">
                                        <select name="selected_location_grid[<?php echo esc_html( $location["ID"] ) ?>][migration_type]" data-location_id="<?php echo esc_html( $location["ID"] ) ?>" class="migration-type">
                                            <option></option>
                                            <option value="convert">Convert (recommended) </option>
                                            <option value="sublocation">Create as a sub-location</option>
                                        </select>
                                    </td>
                                    <td id="<?php echo esc_html( $location["ID"] ) ?>_actions">
                                        <span class="convert" style="display: none;"><strong style="color: green;">Convert</strong> <?php echo esc_html( $location["post_title"] ) ?> to <span class="selected-location_grid-label">World</span></span>
                                        <span class="sublocation" style="display: none;"><strong style="color: orange">Create</strong> <?php echo esc_html( $location["post_title"] ) ?> <strong style="color: orange">as a sub-location</strong> under <span class="selected-location_grid-label">World</span></span>
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
                        window.DRILLDOWN[`${ id } .drilldown`] = function (grid_id, label) {
                            jQuery(`#${id} .convert-input`).val(grid_id)
                            console.log(id);
                            jQuery(`#${id.replace("sublocation", "actions")} .selected-location_grid-label`).text(label)
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


            <?php
        }

        public function box_migration_rebuild_location() {
            if ( isset( $_POST['reset_location_grid'] )
                 && ( isset( $_POST['_wpnonce'] )
                      && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'rebuild_location_grid' . get_current_user_id() ) ) ) {

                $this->migrations_reset_and_rerun();
            }
            ?>
            <!-- Box -->
            <form method="post">
                <?php wp_nonce_field( 'rebuild_location_grid' . get_current_user_id() ); ?>
                <table class="widefat striped">
                    <thead>
                    <tr><th>Clean and Reinstall Mapping Resources (does not effect Contacts or Group data.)</th></tr>
                    </thead>
                    <tbody>

                    <tr>
                        <td>
                            <p>
                                <button type="button" class="button"
                                        onclick="jQuery('#reset_location_grid').show();jQuery(this).prop('disabled', 'disabled')">
                                    Reset Location Grid Table and Install Location Grid
                                </button>
                            </p>
                            <span id="reset_location_grid" style="display:none;">
                                <button type="submit" class="button" name="reset_location_grid" value="1">Are you sure you want to empty the table and to add location_grid?</button>
                            </span>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </form>
            <?php
        }

        public function box_migration_status() {

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
                            Current Location Grid  Records: <?php echo esc_attr( Disciple_Tools_Mapping_Queries::get_total_record_count_in_location_grid_database() ) ?>
                        </td>
                    </tr>
                    <!-- Migration -->
                    <?php
                    $migration = $this->box_migration_from_locations( $return = true )
                    ?>
                    <tr>
                        <td>
                            Location System Migration: <a href="#locations-remaining">Remaining Locations to Migrate (
                                <?php echo esc_html( sizeof( $migration['locations_with_records'] ) ) ?> )</a> |
                            <a href="#locations-migrated">Migrated Locations ( <?php echo esc_html( sizeof( $migration['saved_for_migration'] ) ) ?> )</a>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </form>
            <?php

        }

        public function box_credits() {
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
                                        <p><strong><a href="https://github.com/DiscipleTools/location-grid-project">Location Grid Project</a></strong></p>
                                        <p>
                                            The Location Grid Project hopes to offer a cross-referenced grid for reporting on movement progress across the planet,
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
<!--                                        <p>-->
<!--                                            <a onclick="show_totals()">Show Grid Totals</a><br>-->
<!--                                            <a onclick="show_list()">Show Grid Hierarchy</a><br>-->
<!--                                            <a onclick="show_license()">Show Grid License</a><br>-->
<!--                                        </p>-->

                                        <div id="hierarchy_list" style="display:none; padding: 15px; border: solid 2px #ccc;">
                                            <img src="<?php echo esc_html( spinner() ) ?>" width="30px" />
                                        </div>
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
                        url: "https://raw.githubusercontent.com/DiscipleTools/location-grid-project/master/LICENSE",
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
                        url: "https://raw.githubusercontent.com/DiscipleTools/location-grid-project/master/hierarchy.txt",
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
                        url: "https://raw.githubusercontent.com/DiscipleTools/location-grid-project/master/totals.txt",
                        dataType: "text",
                        success: function( data ) {
                            hl.html( '<br clear="all"><pre>\n' + data + '</pre>')
                        }
                    })
                }
            </script>

            <?php
        }

        public function warning_handler( $errno, $errstr ) {
            ?>
            <div class="notice notice-error notice-dt-mapping-source" data-notice="dt-demo">
                <p><?php echo "MIRROR SOURCE NOT AVAILABLE" ?></p>
                <p><?php echo "Error Message: " . esc_attr( $errstr ) ?></p>
            </div>
            <?php
        }

        public function install_additional_levels( $admin0_code ) {
            global $wpdb;

            // get uploads director
            $dir = wp_upload_dir();
            $uploads_dir = trailingslashit( $dir['basedir'] );

            // make folder
            if ( ! file_exists( $uploads_dir . 'location_grid_download' ) ) {
                mkdir( $uploads_dir . 'location_grid_download' );
            }
            if ( file_exists( $uploads_dir . "location_grid_download/'.$admin0_code.'.tsv.zip" ) ) {
                unlink( $uploads_dir . "location_grid_download/'.$admin0_code.'.tsv.zip" );
            }
            if ( file_exists( $uploads_dir . "location_grid_download/'.$admin0_code.'.tsv" ) ) {
                unlink( $uploads_dir . "location_grid_download/'.$admin0_code.'.tsv" );
            }

            // get mirror source file url
            require_once( get_template_directory() . '/dt-core/global-functions.php' );
            $mirror_source = dt_get_theme_data_url();

            $gn_source_url = $mirror_source . 'location_grid/'.$admin0_code.'.tsv.zip';

            $zip_file = $uploads_dir . "location_grid_download/'.$admin0_code.'.tsv.zip";


            $zip_resource = fopen( $zip_file, "w" );

            $ch_start = curl_init();
            curl_setopt( $ch_start, CURLOPT_URL, $gn_source_url );
            curl_setopt( $ch_start, CURLOPT_FAILONERROR, true );
            curl_setopt( $ch_start, CURLOPT_HEADER, 0 );
            curl_setopt( $ch_start, CURLOPT_FOLLOWLOCATION, true );
            curl_setopt( $ch_start, CURLOPT_AUTOREFERER, true );
            curl_setopt( $ch_start, CURLOPT_BINARYTRANSFER, true );
            curl_setopt( $ch_start, CURLOPT_TIMEOUT, 30 );
            curl_setopt( $ch_start, CURLOPT_SSL_VERIFYHOST, 0 );
            curl_setopt( $ch_start, CURLOPT_SSL_VERIFYPEER, 0 );
            curl_setopt( $ch_start, CURLOPT_FILE, $zip_resource );
            $page = curl_exec( $ch_start );
            if ( !$page)
            {
                error_log( "Error :- ".curl_error( $ch_start ) );
            }
            curl_close( $ch_start );

            if ( !class_exists( 'ZipArchive' )){
                error_log( "PHP ZipArchive is not installed or enabled." );
                return;
            }
            $zip = new ZipArchive();
            $extract_path = $uploads_dir . 'location_grid_download';
            if ($zip->open( $zip_file ) != "true")
            {
                error_log( "Error :- Unable to open the Zip File" );
            }

            $zip->extractTo( $extract_path );
            $zip->close();


            // TEST for presence of source files
            $file = $admin0_code . '.tsv';
            if ( ! file_exists( $uploads_dir . "location_grid_download/" . $file ) ) {
                error_log( 'Failed to find ' . $file );
                return;
            }

            $file_location = $uploads_dir . 'location_grid_download/' . $file;

            // LOAD location_grid data
            $fp = fopen( $file_location, 'r' );

            $query = "INSERT IGNORE INTO $wpdb->dt_location_grid VALUES ";

            $count = 0;
            while ( ! feof( $fp ) ) {
                $line = fgets( $fp, 2048 );
                $count++;

                $data = str_getcsv( $line, "\t" );

                $data_sql = dt_array_to_sql( $data );

                if ( isset( $data[24] ) ) {
                    $query .= " ( $data_sql ), ";
                }
                if ( $count === 500 ) {
                    $query .= ';';
                    $query = str_replace( ", ;", ";", $query ); //remove last comma

                    $wpdb->query( $query );  //phpcs:ignore
                    $query = "INSERT IGNORE INTO $wpdb->dt_location_grid VALUES ";
                    $count = 0;
                }
            }
            //add the last queries
            $query .= ';';
            $query = str_replace( ", ;", ";", $query ); //remove last comma
            $wpdb->query( $query );  //phpcs:ignore


        }

        public function remove_additional_levels( $admin0_code, $level ) {
            global $wpdb;
            // drop tables
            $result = $wpdb->query( $wpdb->prepare( "
                DELETE FROM $wpdb->dt_location_grid WHERE admin0_code = %s AND level >= %d
            ",
                $admin0_code,
            $level ) );
            dt_write_log( $result );
             return $result;

        }

        public function migrations_reset_and_rerun() {
            global $wpdb;
            // drop tables
            $wpdb->dt_location_grid = $wpdb->prefix . 'dt_location_grid';
            $wpdb->query( "DROP TABLE IF EXISTS $wpdb->dt_location_grid" );

            // delete
            delete_option( 'dt_mapping_module_migration_lock' );
            delete_option( 'dt_mapping_module_migrate_last_error' );
            delete_option( 'dt_mapping_module_migration_number' );

            // delete folder and downloads
            $dir = wp_upload_dir();
            $uploads_dir = trailingslashit( $dir['basedir'] );
            if ( file_exists( $uploads_dir . 'location_grid/location_grid.tsv.zip' ) ) {
                unlink( $uploads_dir . 'location_grid/location_grid.tsv.zip' );
            }
            if ( file_exists( $uploads_dir . 'location_grid/location_grid.tsv' ) ) {
                unlink( $uploads_dir . 'location_grid/location_grid.tsv' );
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

        public function rebuild_location_grid( $reset = false ) {
            global $wpdb;

            // clear previous installation
            $dir = wp_upload_dir();
            $uploads_dir = trailingslashit( $dir['basedir'] );
            $file = 'location_grid.tsv';
            $file_location = $uploads_dir . "location_grid/" . $file;

            // TEST for presence of source files
            if ( ! file_exists( $uploads_dir . "location_grid/" . $file ) ) {
                require_once( get_template_directory() . '/dt-mapping/migrations/0001-prepare-location_grid-data.php' );
                $download = new DT_Mapping_Module_Migration_0001();
                $download->up();

                if ( ! file_exists( $uploads_dir . "location_grid/" . $file ) ) {
                    error_log( 'Failed to find ' . $file );

                    return;
                }
            }

            // TEST for expected tables and clear it
            $wpdb->query( "SHOW TABLES LIKE '$wpdb->dt_location_grid'" );
            if ( $wpdb->num_rows < 1 ) {
                require_once( get_template_directory() . '/dt-mapping/migrations/0000-initial.php' );
                $download = new DT_Mapping_Module_Migration_0000();
                $download->up();

                $wpdb->query( "SHOW TABLES LIKE '$wpdb->dt_location_grid'" );
                if ( $wpdb->num_rows < 1 ) {
                    error_log( 'Failed to find ' . $wpdb->dt_location_grid );
                    dt_write_log( $wpdb->num_rows );
                    dt_write_log( $wpdb );

                    return;
                }
            }
            if ( $reset ) {
                $wpdb->query( "TRUNCATE $wpdb->dt_location_grid" );
            }

            // LOAD location_grid data
            dt_write_log( 'begin location_grid install: ' . microtime() );

            $fp = fopen( $file_location, 'r' );

            $query = "INSERT IGNORE INTO $wpdb->dt_location_grid VALUES";
            $count = 0;
            while ( ! feof( $fp ) ) {
                $line = fgets( $fp, 2048 );
                $count++;

                $data = str_getcsv( $line, "\t" );

                $data_sql = dt_array_to_sql( $data );

                if ( isset( $data[24] ) ) {
                    $query .= " ( $data_sql ), ";
                }
                if ( $count === 500 ) {
                    $query .= ';';
                    $query = str_replace( ", ;", ";", $query ); //remove last comma

                    $wpdb->query( $query );  //phpcs:ignore
                    $query = "INSERT IGNORE INTO $wpdb->dt_location_grid VALUES ";
                    $count = 0;
                }
            }
            //add the last queries
            $query .= ';';
            $query = str_replace( ", ;", ";", $query ); //remove last comma
            $wpdb->query( $query );  //phpcs:ignore

            dt_write_log( 'end location_grid install: ' . microtime() );

            fclose( $fp );
        }

        /**
         * Add a sublocation under a location_grid (or other sublocation) parent
         */

        /**
         * @param $parent_location_grid_id
         * @param $name
         * @param $population
         *
         * @return int|WP_Error, the id of the new sublocation
         */
        public function add_sublocation_under_location_grid( $parent_location_grid_id, $name, $population ){
            global $wpdb;
            $parent_grid_id = $wpdb->get_row( $wpdb->prepare( "
                SELECT * FROM $wpdb->dt_location_grid WHERE grid_id = %d
            ", $parent_location_grid_id ), ARRAY_A );
            if ( empty( $parent_grid_id ) ) {
                return new WP_Error( 'missing_param', 'Missing or incorrect location_grid parent.' );
            }

            $max_id = (int) $wpdb->get_var( "SELECT MAX(grid_id) FROM $wpdb->dt_location_grid" );
            $max_id = max( $max_id, 1000000000 );
            $custom_grid_id = $max_id + 1;

            // get level
            $parent_level = isset( $parent_grid_id["level"] ) ? (int) $parent_grid_id["level"] : 9;
            $level = $parent_level + 1;
            $level_name = 'place';

            // save new record
            $result = $wpdb->insert(
                $wpdb->dt_location_grid,
                [
                    'grid_id' => $custom_grid_id,
                    'name' => $name,
                    'level' => $level,
                    'level_name' => $level_name,
                    'country_code' => $parent_grid_id['country_code'],
                    'admin0_code' => $parent_grid_id['admin0_code'],
                    'parent_id' => $parent_location_grid_id,
                    'admin0_grid_id' => $parent_grid_id['admin0_grid_id'],
                    'admin1_grid_id' => $parent_grid_id['admin1_grid_id'],
                    'admin2_grid_id' => $parent_grid_id['admin2_grid_id'],
                    'admin3_grid_id' => $parent_grid_id['admin3_grid_id'],
                    'admin4_grid_id' => $parent_grid_id['admin4_grid_id'],
                    'admin5_grid_id' => $parent_grid_id['admin5_grid_id'],
                    'longitude' => $parent_grid_id['longitude'],
                    'latitude' => $parent_grid_id['latitude'],
                    'population' => $population,
                    'modification_date' => current_time( 'mysql' ),
                    'alt_name' => $name,
                    'alt_population' => $population,
                    'is_custom_location' => 1,
                ],
                [
                    '%d', // grid_id
                    '%s', // name
                    '%s', // level
                    '%s', // level_name
                    '%s', // country code
                    '%s', // admin0_code
                    '%d', // parent_id
                    '%d', // admin0_grid_id
                    '%d', // admin1_grid_id
                    '%d', // admin2_grid_id
                    '%d', // admin3_grid_id
                    '%d', // admin4_grid_id
                    '%d', // admin5_grid_id
                    '%s', // longitude
                    '%s', // latitude
                    '%d', // population
                    '%s', // modification_date
                    '%s', // alt_name
                    '%d', // alt_population
                    '%d' //is custom location
                ]
            );
            if ( ! $result ){
                dt_write_log( $wpdb->last_error );
                dt_write_log( $wpdb->last_query );
                return new WP_Error( __FUNCTION__, 'Error creating sublocation' );
            } else {
                return $custom_grid_id;
            }
        }

        public function convert_location_to_location_grid( $location_id, $location_grid_id ){

            global $wpdb;
            $wpdb->query( $wpdb->prepare(
                "INSERT INTO $wpdb->postmeta
                (
                    post_id,
                    meta_key,
                    meta_value
                )
                SELECT p2p_from, 'location_grid', %s
                FROM $wpdb->p2p as p2p
                WHERE p2p_to = %s
                ",
                esc_sql( $location_grid_id ),
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
                    object_subtype = 'location_grid',
                    meta_key = 'location_grid',
                    meta_value = %s,
                    field_type = 'location'
                WHERE meta_key = 'contacts_to_locations' OR meta_key = 'groups_to_locations'
                AND meta_value = %s
                ",
                $location_grid_id,
                $location_id
            ));
        }

        public function migrate_user_filters_to_location_grid(){
            //get migrations
            $migrated = get_option( "dt_mapping_migration_list", [] );
//            get users with that have filters
//            check for locations
//            try converting to location_grid
            global $wpdb;

            $users = get_users( [ "meta_key" => $wpdb->get_blog_prefix() . "saved_filters" ] );
            foreach ( $users as $user ){
                $save_filters = get_user_option( "saved_filters", $user->ID );
                foreach ( $save_filters as $post_type => &$filters ){
                    foreach ( $filters as &$filter ){
                        if ( !empty( $filter["query"]["locations"] ) ){
                            $location_grid = [];
                            foreach ( $filter["query"]["locations"] as $location ){
                                if ( isset( $migrated[$location]["selected_location_grid"] ) ){
                                    $location_grid[] = $migrated[$location]["selected_location_grid"];
                                }
                            }
                            $filter['query']['location_grid'] = $location_grid;
                            unset( $filter["query"]["locations"] );
                            foreach ( $filter["labels"] as &$label ){
                                if ( $label["field"] === "locations" ){
                                    if ( isset( $migrated[$label["id"]]["selected_location_grid"] )){
                                        $label["field"] = "location_grid";
                                        $label["id"] = $migrated[$label["id"]]["selected_location_grid"];
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
            if ( ! get_option( 'dt_locations_migrated_to_location_grid', false ) ) { ?>
                <div class="notice notice-error notice-dt-locations-migration is-dismissible" data-notice="dt-locations-migration">
                    <p>We have updated Disciple.Tools locations system. Please use the migration tool to make sure all you locations are carried over:
                        <a href="<?php echo esc_html( admin_url( 'admin.php?page=dt_mapping_module&tab=migration' ) ) ?>">Migration Tool</a></p>
                </div>
            <?php }
        }
    }

    DT_Mapping_Module_Admin::instance();
}
