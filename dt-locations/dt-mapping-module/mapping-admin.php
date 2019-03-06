<?php
/**
 * Mapping Module Admin Section Elements
 *
 * These elements are designed to be included into the admin areas of other themes and plugins.
 *
 * Example:
 *      DT_Mapping_Module_Admin::instance()->population_metabox();
 */

if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

if ( ! class_exists( 'DT_Mapping_Module_Admin' )  ) {

    /**
     * Class DT_Mapping_Module_Admin
     */
    class DT_Mapping_Module_Admin
    {
        public $token = 'dt_mapping_module';

        // Singleton
        private static $_instance = null;
        public static function instance()
        {
            if ( is_null( self::$_instance ) ) {
                self::$_instance = new self();
            }
            return self::$_instance;
        }

        public $spinner;
        public $nonce;
        public $current_user_id;
        public $map_key;

        public function __construct()
        {
            /**
             * If allowed, this class will load into every admin the header scripts and rest endpoints. It is best
             * practice to add a filter to a config file in the plugin or theme using this module that filters for the
             * specific option page that these metaboxes are to be loaded, for the overall weight of the admin area.
             *
             * Example:
             *  function dt_load_mapping_admin_class( $approved ) {
             *      global $pagenow;
             *      if ( 'my_admin_page' === $pagenow ) {
             *          return true;
             *      }
             *      return false;
             *  }
             *  add_filter('dt_mapping_module_admin_load_approved', 'dt_load_only_on_options_page' )
             *
             */
            if ( ! apply_filters( 'dt_mapping_module_admin_load_approved', true ) ) {
                return; // this allows you to control what environments the admin loads.
            }

            $this->spinner = plugin_dir_url( __FILE__ ) . '/spinner.svg';
            $this->nonce = wp_create_nonce( 'wp_rest' );
            $this->current_user_id = get_current_user_id();
            add_action( 'admin_head', [ $this, 'scripts' ] );

            add_action( "admin_menu", array( $this, "register_menu" ) );
            add_action( "admin_head", [ $this, 'header_script' ] );

            if ( is_admin() ) {
                // all other things to load when in the admin environment.
            }
        }

        /**
         * Admin Page Elements
         */
        public function scripts()
        {
            ?>
            <script>
                function install_geonames(type) {
                    let link = jQuery('#' + type)
                    let spinner = '<img src="<?php echo esc_url( $this->spinner ) ?>" width="16px" />'

                    link.attr("onclick", "")
                    link.append(spinner)

                    let data = {"type": type}
                    jQuery.ajax({
                        type: "POST",
                        data: JSON.stringify(data),
                        contentType: "application/json; charset=utf-8",
                        dataType: "json",
                        url: '<?php echo esc_url( rest_url() ) ?>dt/v1/network/import',
                        beforeSend: function (xhr) {
                            xhr.setRequestHeader('X-WP-Nonce', '<?php echo esc_attr( $this->nonce ) ?>');
                        },
                    })
                        .done(function (data) {
                            link.empty().append('All finished! &#9989;')
                            console.log(data)
                        })
                        .fail(function (err) {
                            link.empty().append("Oops. Something did not work. Maybe try again.")
                            console.log(err);
                        })
                }

                function test_download(country_code) {
                    let link = jQuery('#test_download')
                    let spinner = '<img src="<?php echo esc_url( $this->spinner ) ?>" width="16px" />'

                    link.attr("onclick", "")
                    link.append(spinner)

                    let data = {"country_code": country_code}
                    jQuery.ajax({
                        type: "POST",
                        data: JSON.stringify(data),
                        contentType: "application/json; charset=utf-8",
                        dataType: "json",
                        url: '<?php echo esc_url( rest_url() ) ?>dt/v1/network/download',
                        beforeSend: function (xhr) {
                            xhr.setRequestHeader('X-WP-Nonce', '<?php echo esc_attr( $this->nonce ) ?>');
                        },
                    })
                        .done(function (data) {
                            link.empty().append('All finished! &#9989;')
                            console.log(data)
                        })
                        .fail(function (err) {
                            link.empty().append("Oops. Something did not work. Maybe try again.")
                            console.log(err);
                        })
                }
            </script>
            <?php
        }

        public function register_menu() {
            add_menu_page( __( 'Mapping', 'disciple_tools' ),
                __( 'Mapping', 'disciple_tools' ),
                'manage_dt',
                $this->token,
                [ $this, 'content' ],
                'dashicons-admin-site',
                7 );
        }

        public function header_script() {
            ?>
            <style>
                a.pointer { cursor: pointer; }
            </style>
            <?php
        }

        public function content() {

            if ( !current_user_can( 'manage_dt' ) ) { // manage dt is a permission that is specific to Disciple Tools and allows admins, strategists and dispatchers into the wp-admin
                wp_die( esc_attr__( 'You do not have sufficient permissions to access this page.' ) );
            }

            if ( isset( $_GET["tab"] ) ) {
                $tab = sanitize_key( wp_unslash( $_GET["tab"] ) );
            } else {
                $tab = 'general';
            }

            $link = 'admin.php?page='.$this->token.'&tab=';

            ?>
            <div class="wrap">
                <h2><?php esc_attr_e( 'Mapping', $this->token ) ?></h2>
                <h2 class="nav-tab-wrapper">

                    <!-- General Tab -->
                    <a href="<?php echo esc_attr( $link ) . 'general' ?>" class="nav-tab
                        <?php ( $tab == 'general' || ! isset( $tab ) ) ? esc_attr_e( 'nav-tab-active', $this->token ) : print ''; ?>">
                        <?php esc_attr_e( 'General Settings', $this->token ) ?>
                    </a>
                    <!-- Geocoding -->
                    <a href="<?php echo esc_attr( $link ) . 'geocoding' ?>" class="nav-tab
                        <?php ( $tab == 'geocoding' ) ? esc_attr_e( 'nav-tab-active', $this->token ) : print ''; ?>">
                        <?php esc_attr_e( 'Geocoding', $this->token ) ?>
                    </a>
                    <!-- Names Tab -->
                    <a href="<?php echo esc_attr( $link ) . 'names' ?>" class="nav-tab
                        <?php ( $tab == 'names' ) ? esc_attr_e( 'nav-tab-active', $this->token ) : print ''; ?>">
                        <?php esc_attr_e( 'Names', $this->token ) ?>
                    </a>
                    <!-- Population Tab -->
                    <a href="<?php echo esc_attr( $link ) . 'population' ?>" class="nav-tab
                        <?php ( $tab == 'population' ) ? esc_attr_e( 'nav-tab-active', $this->token ) : print ''; ?>">
                        <?php esc_attr_e( 'Population', $this->token ) ?>
                    </a>
                    <!-- Add Sub-Locations -->
                    <a href="<?php echo esc_attr( $link ) . 'sub-locations' ?>" class="nav-tab
                        <?php ( $tab == 'sub-locations' ) ? esc_attr_e( 'nav-tab-active', $this->token ) : print ''; ?>">
                        <?php esc_attr_e( 'Sub-Locations', $this->token ) ?>
                    </a>
                    <!-- Add Migration -->
                    <a href="<?php echo esc_attr( $link ) . 'migration' ?>" class="nav-tab
                        <?php ( $tab == 'migration' ) ? esc_attr_e( 'nav-tab-active', $this->token ) : print ''; ?>">
                        <?php esc_attr_e( 'Migration', $this->token ) ?>
                    </a>
                    <!-- Add Locations Explorer -->
                    <a href="<?php echo esc_attr( $link ) . 'explore' ?>" class="nav-tab
                        <?php ( $tab == 'explore' ) ? esc_attr_e( 'nav-tab-active', $this->token ) : print ''; ?>">
                        <?php esc_attr_e( 'Explore', $this->token ) ?>
                    </a>

                </h2>

                <?php
                switch ($tab) {
                    case "general":
                        $this->general_tab();
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
                        $this->geocoding_tab();
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
                    <div id="post-body" class="metabox-holder columns-1">
                        <div id="post-body-content">
                            <!-- Main Column -->

                            <?php $this->starting_map_level_metabox(); ?>

                            <?php $this->global_population_division_metabox(); ?>

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

                            <?php ?>

                            <?php $this->migration_status_metabox() ?>

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
                                <tbody><tr><td id="results_body"><img src="<?php echo esc_url( DT_Mapping_Module::instance()->module_url )?>spinner.svg" style="width:20px; padding-top:5px;" /></td></tr>
                                </tbody>
                            </table>

                            <!-- End Main Column -->
                        </div><!-- end post-body-content -->
                    </div><!-- post-body meta box container -->
                </div><!--poststuff end -->
            </div><!-- wrap end -->
            <script>
                jQuery(document).ready(function() {
                    reset_drill_down()
                })

                function reset_drill_down() {
                    jQuery('#drill_down_body').empty().append(`<tr><td>World</td></tr><tr><td><select id="6295630" onchange="get_children( this.value );jQuery(this).parent().parent().nextAll().remove();"><option>Select</option></select> <span id="spinner_6295630"><img src="<?php echo esc_url( DT_Mapping_Module::instance()->module_url )?>spinner.svg" style="width:20px; padding-top:5px;" /></span></td></tr>`)
                    jQuery.ajax({
                        type: "POST",
                        contentType: "application/json; charset=utf-8",
                        data: JSON.stringify( { 'geonameid': 6295630 } ),
                        dataType: "json",
                        url: "<?php echo esc_url_raw( rest_url() ) ?>dt/v1/mapping_module/get_children",
                        beforeSend: function(xhr) {
                            xhr.setRequestHeader('X-WP-Nonce', '<?php echo wp_create_nonce( 'wp_rest' ) ?>');
                        },
                    })
                        .done( function( response ) {
                            console.log(response)

                            jQuery('#spinner_6295630').empty()
                            jQuery('#results_body').empty()

                            if ( response ) {
                                jQuery.each( response.list, function(i,v) {
                                    jQuery('#6295630').append(`<option value="${v.id}">${v.name}</option>`)
                                })
                            }

                        }) // end success statement
                        .fail(function (err) {
                            console.log("error")
                            console.log(err)
                        })
                }

                function get_children( id ) {
                    let drill_down = jQuery('#drill_down_body')
                    console.log(id)
                    console.log(drill_down)

                    let spinner_span = jQuery('#spinner_'+id)
                    let results_box = jQuery('#results_body')
                    let spinner = `<img src="<?php echo esc_url( DT_Mapping_Module::instance()->module_url )?>spinner.svg" style="width:20px; padding-top:5px;" />`

                    results_box.empty().append(spinner)

                    jQuery.ajax({
                        type: "POST",
                        contentType: "application/json; charset=utf-8",
                        data: JSON.stringify( { 'geonameid': id } ),
                        dataType: "json",
                        url: "<?php echo esc_url_raw( rest_url() ) ?>dt/v1/mapping_module/get_children",
                        beforeSend: function(xhr) {
                            xhr.setRequestHeader('X-WP-Nonce', '<?php echo wp_create_nonce( 'wp_rest' ) ?>');
                        },
                    })
                        .done( function( response ) {
                            console.log(response)

                            spinner_span.empty()
                            results_box.empty()

                            if ( response.list.length === 0 ) {
                                results_box.append(`<tr><td>No Children Locations</td></tr>`)
                            }
                            else {
                                jQuery('#drill_down_body').append(`<tr><td><select id="${id}" onchange="get_children( this.value );jQuery(this).parent().parent().nextAll().remove()"><option>Select</option></select> <span id="spinner_${id}"></span></td></tr>`)
                                jQuery.each( response.list, function(i,v) {
                                    jQuery('#'+id).append(`<option value="${v.id}">${v.name}</option>`)
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
        public function migration_status_metabox() {
            if ( isset( $_POST[ 'unlock' ] )
                && ( isset( $_POST[ '_wpnonce' ] )
                    && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ '_wpnonce' ] ) ), 'migration_status' . get_current_user_id() ) ) ) {

                delete_option('dt_mapping_module_migration_number' );
                delete_option('dt_mapping_module_migration_lock' );
                delete_option('dt_mapping_module_migrate_last_error' );
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
                            Migrations Available: <?php echo esc_attr( DT_Mapping_Module_Migration_Engine::$migration_number ) ?><br>
                            Current Migration: <?php echo get_option('dt_mapping_module_migration_number', true ) ?><br>
                            Locked Status: <?php
                                if ( get_option('dt_mapping_module_migration_lock', true ) ) {
                                    ?>
                                    Locked!
                                    <a onclick="jQuery('#error-message-raw').toggle();" class="alert">Show error message</a>
                                    <div style="display:none;" id="error-message-raw"><hr>
                                        <?php echo '<pre>'; print_r( get_option('dt_mapping_module_migrate_last_error', true ) ); echo '</pre>'; ?>
                                    </div>
                                    <hr>
                                    <p><button type="submit" name="unlock" value="1">Unlock and Rerun Migrations</button></p>
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

        public function global_population_division_metabox()
        {
            // process post action
            if ( isset( $_POST[ 'population_division' ] )
                && ( isset( $_POST[ '_wpnonce' ] )
                    && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ '_wpnonce' ] ) ), 'population_division' . get_current_user_id() ) ) ) {
                $new = (int) sanitize_text_field( wp_unslash( $_POST[ 'population_division' ] ) );
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
                                   value="<?php echo esc_attr( $population_division ); ?>"/> <button type="submit" class="button">Update</button>
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

        public function starting_map_level_metabox()
        {
            // get mapping class
            $mm = DT_Mapping_Module::instance();
            $top_level_maps = $mm->top_level_maps();

            // process post action
            if ( isset( $_POST[ 'locations' ] )
                && ( isset( $_POST[ '_wpnonce' ] )
                    && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ '_wpnonce' ] ) ), 'starting_map_level' . get_current_user_id() ) ) ) {
                $new = sanitize_text_field( wp_unslash( $_POST[ 'locations' ] ) );

                if ( array_key_exists( $new, $top_level_maps ) ) {
                    $array = [
                         'type' => 'top_level',
                         'geonameid' => $new,
                    ];
                    update_option( 'dt_mapping_module_starting_map_level', $array, false );
                } elseif ( is_numeric( $new ) ) {
                    $array = [
                        'type' => 'country',
                        'geonameid' => (int) $new,
                    ];
                    update_option( 'dt_mapping_module_starting_map_level', $array, false );
                }
            }
            $starting_map_level = $mm->initial_map_level();

            $select = '<select id="locations" name="locations">';

            // build default world
            $select .= '<option value="world"';
            if ( $starting_map_level['geonameid'] === 'world' ) {
                $select .= 'selected';
            }
            $select .= '>World</option>';
            $select .= '<option value="">-------</option>';


            // get continents
            foreach( $top_level_maps as $key => $result ) {
                if ( 'world' === $key ) {
                    continue;
                }
                $select .= '<option value="'.$key.'" ';
                if ( $starting_map_level['geonameid'] === $key ) {
                    $select .= 'selected';
                }
                $select .= '>'.$result['name'].'</option>';
            }

            $select .= '<option value="">-------</option>';

            // get countries
            $results = $mm->query( 'list_countries' );

            foreach( $results as $result ) {
                $select .= '<option value="'.$result['geonameid'].'" ';
                if ( $starting_map_level['geonameid'] === (int) $result['geonameid'] ) {
                    $select .= 'selected';
                }
                $select .= '>'.$result['name'].'</option>';
            }

            $select .= '</select>';

            ?>
            <!-- Box -->
            <form method="post">
                <table class="widefat striped">
                    <thead>
                        <th>Starting Map Level</th>
                    </thead>
                    <tbody>
                    <tr>
                        <td>
                            <?php wp_nonce_field( 'starting_map_level' . get_current_user_id() ); ?>
                            <?php echo $select ?> <button type="submit" class="button">Update</button>

                        </td>
                    </tr>
                    </tbody>
                </table>

            </form>

            <br>
            <!-- End Box -->
            <?php
        }

        public function set_polygon_mirror_metabox() {

            /**
             * https://storage.googleapis.com/disciple-tools-maps/
             * https://raw.githubusercontent.com/DiscipleTools/dt-geojson/master/
             * https://s3.amazonaws.com/mapping-source/
             */
            $mirror_list = [
                 'github' => [
                     'key' => 'github',
                     'label' => 'GitHub',
                     'url' => 'https://raw.githubusercontent.com/DiscipleTools/dt-geojson/master/'
                 ],
                 'google' => [
                     'key' => 'google',
                     'label' => 'Google',
                     'url' => 'https://storage.googleapis.com/mapping-source/'
                 ],
                 'amazon' => [
                     'key' => 'amazon',
                     'label' => 'Amazon',
                     'url' => 'https://s3.amazonaws.com/mapping-source/',
                 ],
                 'other' => [
                     'key' => 'other',
                     'label' => 'Other',
                     'url' => '',
                 ],
            ];

            // process post action
            if ( isset( $_POST[ 'source' ] )
                && ( isset( $_POST[ '_wpnonce' ] )
                && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ '_wpnonce' ] ) ), 'set_polygon_mirror' . get_current_user_id() ) )
                ) {

                $selection_key =  sanitize_text_field( wp_unslash( $_POST[ 'source' ] ) );

                if ( $selection_key === 'other' && ! empty( $_POST[ 'other_value' ] ) ) { // must be set to other and have a url
                    $url = trailingslashit( sanitize_text_field( $_POST[ 'other_value' ] ) );
                    if ( 'https://' === substr( $url, 0, 8 ) ) { // must begin with https
                        $array = [
                            'key' => 'other',
                            'label' => 'Other',
                            'url' => $url,
                        ];
                        update_option( 'dt_mapping_module_polygon_mirror', $array, true );
                    }

                } else if ( $selection_key !== 'other' ) {
                    $array = [
                        'key' => $selection_key,
                        'label' => $mirror_list[$selection_key]['label'],
                        'url' => $mirror_list[$selection_key]['url'],
                    ];
                    update_option( 'dt_mapping_module_polygon_mirror', $array, true );
                }
            }

            $mirror = dt_get_mapping_polygon_mirror();

            set_error_handler([$this, "warning_handler"], E_WARNING);
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

                            <p><input type="radio" id="github" name="source" value="github" <?php echo ( isset( $mirror['key'] ) && $mirror['key'] === 'github' ) ? 'checked': '' ?>><label for="github"><?php echo esc_html( $mirror_list['github']['label']) ?></label></p>
                            <p><input type="radio" id="google" name="source" value="google" <?php echo ( isset( $mirror['key'] ) && $mirror['key'] === 'google' ) ? 'checked': '' ?>><label for="google"><?php echo esc_html( $mirror_list['google']['label']) ?></label></p>
                            <p><input type="radio" id="amazon" name="source" value="amazon" <?php echo ( isset( $mirror['key'] ) && $mirror['key'] === 'amazon' ) ? 'checked': '' ?>><label for="amazon"><?php echo esc_html( $mirror_list['amazon']['label']) ?></label></p>
                            <p><input type="radio" id="other" name="source" value="other" <?php echo ( isset( $mirror['key'] ) && $mirror['key'] === 'other' ) ? 'checked': '' ?>>
                            <input type="text" style="width:50em;" placeholder="add full url of your custom mirror. Must begin with https." name="other_value" value="<?php echo ( $mirror['key'] === 'other' ) ? $mirror['url'] : '' ; ?>" /> (see Custom Mirror Note below)

                            </p>
                            <p>
                                <button type="submit" class="button">Update</button>
                            </p>

                            <p id="reachable_source" class="<?php echo $status_class ?>">
                                <?php echo $message; ?>
                            </p>

                            <p>
                                <strong>Custom Mirror Note:</strong>
                                <em>
                                    Note: The custom mirror option allows you to download the polygon source repo (<a href="https://github.com/DiscipleTools/dt-geojson/archive/master.zip">Download source</a>) and install
                                    this folder to your own mirror. You will be responsible for syncing occasional updates to
                                    the folder. But this allows you to obscure traffic to these default mirrors, if you have
                                    security concerns with from your country.
                                </em>
                            </p>
                            <p>
                                <strong>Other Notes:</strong><br>
                                <em>The polygons that make up of the boarders for each country, state, and county are a significant
                                amount of data. Mapping has broken these up into individual files that are stored at various
                                mirror locations. You can choose the mirror that works for you and your country, or you can host your own mirror
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

        public function warning_handler($errno, $errstr) {
            ?>
            <div class="notice notice-error notice-dt-mapping-source" data-notice="dt-demo">
                <p><?php echo "MIRROR SOURCE NOT AVAILABLE" ?></p>
                <p><?php echo "Error Message: " . $errstr  ?></p>
            </div>
            <?php
        }


    }
    DT_Mapping_Module_Admin::instance();


    /**
     * Best way to call for the mapping polygon
     * @return array
     */
    function dt_get_mapping_polygon_mirror( $url_only = false ) {
        $mirror = get_option('dt_mapping_module_polygon_mirror');
        if ( empty( $mirror ) ) {
            $array = [
                'key' => 'github',
                'label' => 'GitHub',
                'url' => 'https://raw.githubusercontent.com/DiscipleTools/dt-geojson/master/'
            ];
            update_option( 'dt_mapping_module_polygon_mirror', $array, true );
            $mirror = $array;
        }

        if ( $url_only ) {
            return $mirror['url'];
        }

        return $mirror;
    }
}

