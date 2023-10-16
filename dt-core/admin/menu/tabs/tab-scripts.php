<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class Disciple_Tools_Tab_Logs
 */
class Disciple_Tools_Scripts extends Disciple_Tools_Abstract_Menu_Base {
    private static $_instance = null;

    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }

        return self::$_instance;
    } // End instance()

    /**
     * Constructor function.
     *
     * @access  public
     * @since   0.1.0
     */
    public function __construct() {
        add_action( 'admin_menu', [ $this, 'add_submenu' ], 125 );
        add_action( 'dt_utilities_tab_menu', [ $this, 'add_tab' ], 125, 1 );
        add_action( 'dt_utilities_tab_content', [ $this, 'content' ], 125, 1 );
        add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ] );

        // Allow SVG uploads
        add_filter( 'upload_mimes', function ( $mime_types ) {
            $mime_types['svg']  = 'image/svg+xml';
            $mime_types['svgz'] = 'image/svg+xml';

            return $mime_types;
        } );

        // Ensure WP is able to correctly identify SVGs
        add_filter( 'wp_check_filetype_and_ext', function ( $data = null, $file = null, $filename = null, $mimes = null ) {
            $ext = $data['ext'] ?? '';
            if ( strlen( $ext ) < 1 ) {
                $exploded = explode( '.', $filename );
                $ext      = strtolower( end( $exploded ) );
            }
            if ( $ext === 'svg' ) {
                $data['type'] = 'image/svg+xml';
                $data['ext']  = 'svg';
            } elseif ( $ext === 'svgz' ) {
                $data['type'] = 'image/svg+xml';
                $data['ext']  = 'svgz';
            }

            return $data;
        }, 75, 4 );

        parent::__construct();
    } // End __construct()

    public function add_submenu() {
        add_submenu_page( 'dt_utilities', __( 'Scripts', 'disciple_tools' ), __( 'Scripts', 'disciple_tools' ), 'manage_dt', 'dt_utilities&tab=scripts', [
            'Disciple_Tools_Utilities_Menu',
            'content'
        ] );
    }

    public function add_tab( $tab ) {
        ?>
        <a href="<?php echo esc_html( admin_url( 'admin.php?page=dt_utilities&tab=scripts' ) ); ?>"
           class="nav-tab <?php echo esc_html( $tab === 'scripts' ? 'nav-tab-active' : '' ); ?>">
            <?php echo esc_html__( 'Scripts' ); ?>
        </a>
        <?php
    }

    public function content( $tab ) {
        if ( 'scripts' == $tab ) :

            $this->template( 'begin' );

            $this->display_settings();

            $this->template( 'right_column' );

            $this->template( 'end' );

        endif;
    }

    private function display_settings() {

        $this->box( 'top', 'Scripts', [ 'col_span' => 4 ] );

        ?>
        <table class="widefat striped">
            <thead>
                <tr>
                    <th>Generation Counts</th>
                    <th>Field</th>
                    <th>Progress</th>
                </tr>
            </thead>
            <tbody>
        <?php
        foreach ( DT_Posts::get_post_types() as $post_type ) {
            $field_settings = DT_Posts::get_post_field_settings( $post_type );
            foreach ( $field_settings as $field_key => $field_value ){
                if ( isset( $field_value['connection_count_field']['post_type'] ) && $field_value['connection_count_field']['post_type'] === $post_type ){
                    $name = $field_settings[$field_value['connection_count_field']['field_key']]['name'];
                    ?>
                    <tr id="<?php echo esc_html( $post_type . '_' . $field_key ); ?>">
                        <td><?php echo esc_html( $name ); ?> on <?php echo esc_html( $post_type ); ?></td>
                        <td>
                            <button type="button" class="reset_count_button" data-key="<?php echo esc_html( $field_key ); ?>" data-post-type="<?php echo esc_html( $post_type ); ?>">
                                Reset Counts for <?php echo esc_html( $field_value['name'] ); ?>
                            </button>
                        </td>
                        <td class="progress">
                            <span class="current"></span><span class="total"></span>
                            <span class="loading-spinner"></span>
                        </td>
                    </tr>
                    <?php
                }
            }
        }
        ?>
            </tbody>
        </table>
        <br><br>

        <table class="widefat striped">
            <thead>
            <tr>
                <th>Activity Data</th>
                <th>Field</th>
                <th>Progress</th>
            </tr>
            </thead>
            <tbody>
            <?php
            foreach ( DT_Posts::get_post_types() as $post_type ) {
                $post_type_settings = DT_Posts::get_post_settings( $post_type, false );
                $delete_label = sprintf( 'Remove %s Deleted Fields Activity', $post_type_settings['label_plural'] );
                ?>
                <tr>
                    <td><?php echo esc_html( $post_type_settings['label_plural'] ); ?> <?php echo esc_html( __( 'Data Clean Up', 'disciple_tools' ) ); ?></td>
                    <td>
                        <button type="button" class="data-clean-up-button" data-post_type="<?php echo esc_html( $post_type ); ?>" data-delete_label="<?php echo esc_html( $delete_label ); ?>">
                            <?php echo esc_html( $delete_label ); ?>
                        </button>
                    </td>
                    <td class="progress">
                        <span class="current"></span><span class="total"></span>
                        <span class="loading-spinner"></span>
                    </td>
                </tr>
                <?php
            }
            ?>
            </tbody>
        </table>
        <?php

        $this->box( 'bottom' );
    }

    public function admin_enqueue_scripts() {
        wp_enqueue_media();
        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_script( 'dt_utilities_scripts_script', disciple_tools()->admin_js_url . 'dt-utilities-scripts.js', [
            'jquery',
            'wp-color-picker'
        ], filemtime( disciple_tools()->admin_js_path . 'dt-utilities-scripts.js' ), true );
        wp_localize_script(
            'dt_utilities_scripts_script', 'dt_admin_scripts', [
                'site_url'  => site_url(),
                'nonce'     => wp_create_nonce( 'wp_rest' ),
                'rest_root' => esc_url_raw( rest_url() ),
                'upload'    => [
                    'title'      => __( 'Upload Icon', 'disciple_tools' ),
                    'button_txt' => __( 'Upload', 'disciple_tools' )
                ]
            ]
        );
    }
}

Disciple_Tools_Scripts::instance();
