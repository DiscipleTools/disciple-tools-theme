<?php

if ( !defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class Disciple_Tools_Utilities_Overview_Tab
 */
class Disciple_Tools_Utilities_Overview_Tab extends Disciple_Tools_Abstract_Menu_Base
{
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
        add_action( 'admin_menu', [ $this, 'add_submenu' ], 10 );
        add_action( 'dt_utilities_tab_menu', [ $this, 'add_tab' ], 20, 1 ); // use the priority setting to control load order
        add_action( 'dt_utilities_tab_content', [ $this, 'content' ], 10, 1 );


        parent::__construct();
    } // End __construct()


    public function add_submenu() {
        add_submenu_page( 'dt_utilities', __( 'Overview', 'disciple_tools' ), __( 'Overview', 'disciple_tools' ), 'manage_dt', 'dt_utilities&tab=overview', [ 'Disciple_Tools_Utilities_Menu', 'content' ] );
    }

    public function add_tab( $tab ) {
        echo '<a href="'. esc_url( admin_url() ).'admin.php?page=dt_utilities&tab=overview" class="nav-tab ';
        if ( $tab == 'overview' ) {
            echo 'nav-tab-active';
        }
        echo '">'. esc_attr__( 'Overview', 'disciple_tools' ) .'</a>';
    }

    public function content( $tab ) {
        if ( 'overview' == $tab ) {

            $this->reset_lock();

            self::template( 'begin' );

            $this->box_message();

            self::template( 'right_column' );

            self::template( 'end' );
        }
    }

    private function reset_lock(){
        if ( isset( $_POST['_wpnonce'] ) && wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'utilities_overview' ) ){
            if ( isset( $_POST['reset_lock'] ) ){
                $lock_name = sanitize_key( $_POST['reset_lock'] );
                delete_transient( $lock_name );
            }
        }
    }

    private function background_job_queue_counts() {
        global $wpdb;

        wp_queue_wpdb_init();

        return [
            'jobs' => $wpdb->get_var( "SELECT COUNT(id) FROM $wpdb->queue_jobs" ),
            'failures' => $wpdb->get_var( "SELECT COUNT(id) FROM $wpdb->queue_failures" )
        ];
    }

    public function box_message() {
        ?>
        <form method="post">
        <?php
        wp_nonce_field( 'utilities_overview' );
        $this->box( 'top', 'System Details', [ 'col_span' => 2 ] );
        ?>
        <tr>
            <td><?php echo esc_html( sprintf( __( 'WordPress version: %1$s | PHP version: %2$s' ), get_bloginfo( 'version' ), phpversion() ) ); ?></td>
            <td></td>
        </tr>
        <tr>
            <td>Server: <?php echo esc_html( isset( $_SERVER['SERVER_SOFTWARE'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ) : '' ); ?></td>
        </tr>
        <tr>
            <td>
                D.T Theme Version: <?php echo esc_html( wp_get_theme()->version ) ?>
            </td>
            <td></td>
        </tr>
        <tr>
            <td>Instance Url: <?php echo esc_html( get_site_url() ); ?></td>
        </tr>
        <tr>
            <td>Is multisite: <?php echo esc_html( is_multisite() ? 'True' : 'False' ); ?></td>
        </tr>
        <tr>
            <td>
                <?php
                $background_job_counts = $this->background_job_queue_counts();
                echo esc_html( sprintf( 'Background Jobs Queue: Jobs: %1$s, Failures: %2$s', $background_job_counts['jobs'], $background_job_counts['failures'] ) );
                ?>
            </td>
        </tr>
        <tr>
            <td><strong>Migrations</strong></td><td></td>
        </tr>
        <tr>
            <td>
                <?php echo esc_html( sprintf( 'D.T Migration version: %1$s of %2$s', Disciple_Tools_Migration_Engine::get_current_db_migration(), Disciple_Tools_Migration_Engine::$migration_number ) ); ?>.
                Lock: <?php echo esc_html( get_transient( 'dt_migration_lock' ) ?: 0 ) ?>
            </td>
            <td>
                <button name="reset_lock" value="dt_migration_lock">Reset Lock</button>
            </td>
        </tr>
        <tr>
            <td>
                <?php echo esc_html( sprintf( 'Mapping migration version: %1$s of %2$s', DT_Mapping_Module_Migration_Engine::get_current_db_migration(), DT_Mapping_Module_Migration_Engine::$migration_number ) ); ?>.
                Lock: <?php echo esc_html( get_transient( 'dt_mapping_module_migration_lock' ) ?: 0 ) ?>
            </td>
            <td>
                <button name="reset_lock" value="dt_mapping_module_migration_lock">Reset Lock</button>
            </td>
        </tr>


        <?php
        do_action( 'dt_utilities_system_details' );
        ?>
        <tr>
            <td><strong>Plugins</strong></td><td></td>
        </tr>
        <?php
        $plugins = get_plugins();
        $network_active_plugins = get_site_option( 'active_sitewide_plugins', [] );
        $active_plugins = get_option( 'active_plugins', [] );
        foreach ( $network_active_plugins as $plugin => $time ){
            $active_plugins[] = $plugin;
        }
        foreach ( $plugins as $i => $v ){
            if ( !isset( $v['Name'], $v['Version'] ) ){
                continue;
            }
            ?>
            <tr>
            <td><?php echo esc_html( $v['Name'] ); ?> version: <?php echo esc_html( $v['Version'] ); ?></td>
            <td>
            <?php if ( in_array( $i, $active_plugins ) ): ?>
                Plugin Active
            <?php endif; ?>

            </td>
            <tr>
            <?php
        }

        $this->box( 'bottom' );
    }
}
Disciple_Tools_Utilities_Overview_Tab::instance();
