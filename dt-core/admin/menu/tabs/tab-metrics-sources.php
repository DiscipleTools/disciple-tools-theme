<?php

if ( !defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class Disciple_Tools_Metric_Sources_Tab
 */
class Disciple_Tools_Metric_Sources_Tab extends Disciple_Tools_Abstract_Menu_Base
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
        add_action( 'admin_menu', [ $this, 'add_submenu' ], 99 );
        add_action( 'dt_metrics_tab_menu', [ $this, 'add_tab' ], 99, 1 ); // use the priority setting to control load order
        add_action( 'dt_metrics_tab_content', [ $this, 'content' ], 99, 1 );


        parent::__construct();
    } // End __construct()


    public function add_submenu() {
        add_submenu_page( 'dt_metrics', __( 'Sources and Fields', 'disciple_tools' ), __( 'Sources and Fields', 'disciple_tools' ), 'manage_dt', 'dt_metrics&tab=sources', [ 'Disciple_Tools_Metrics_Menu', 'content' ] );
    }

    public function add_tab( $tab ) {
        echo '<a href="'. esc_url( admin_url() ).'admin.php?page=dt_metrics&tab=sources" class="nav-tab ';
        if ( $tab == 'sources' ) {
            echo 'nav-tab-active';
        }
        echo '">'. esc_attr__( 'Sources and Fields', 'disciple_tools' ) .'</a>';
    }

    public function content( $tab ) {
        if ( 'sources' == $tab ) {


            self::template( 'begin' );

            $this->save_settings();
            $this->table();

            self::template( 'right_column' );


            self::template( 'end' );
        }
    }

    public function table() {
        $this->box( 'top', 'Add or Edit Custom metrics to track' );
        $sources = get_option( 'dt_critical_path_sources', [] );
        ?>
        <p></p>

        <form method="POST" action="">
            <?php wp_nonce_field( 'sources_edit', 'sources_edit_nonce' ); ?>
            <table class="widefat striped">
                <thead>
                <tr>
                    <th>Key</th>
                    <th>Label</th>
                    <th>Description</th>
                    <th>Critical Path Section</th>
                    <th>Order</th>
                    <th></th>
                    <th></th>
                </tr>
                </thead>
                <?php foreach ( $sources as $index => $source ): ?>
                <tr>
                    <td><?php echo esc_html( $source["key"] ) ?></td>
                    <td>
                        <input name="label[<?php echo esc_html( $source["key"] ) ?>]" value="<?php echo esc_html( $source["label"] ) ?>">
                    </td>
                    <td>
                        <textarea name="description[<?php echo esc_html( $source["key"] ) ?>]"><?php echo esc_html( $source["description"] ?? '' ) ?></textarea>
                    </td>
                    <td>
                        <select name="section[<?php echo esc_html( $source["key"] ) ?>]">
                            <option value="outreach" <?php echo esc_html( ( $source["section"] ?? '' ) === 'outreach' ? 'selected' : '' ) ?>>Outreach Section</option>
                            <option value="movement" <?php echo esc_html( ( $source["section"] ?? '' ) === 'movement' ? 'selected' : '' ) ?>>Movement Section</option>
                        </select>
                    </td>
                    <td>
                        <?php if ( $index ) :?>
                        <button type="submit" class="button" name="order_up" value="<?php echo esc_html( $source["key"] ) ?>">Up</button>
                        <?php endif;
                        if ( $index != sizeof( $sources ) -1 ) : ?>
                        <button type="submit" class="button" name="order_down" value="<?php echo esc_html( $source["key"] ) ?>">Down</button>
                        <?php endif; ?>
                    </td>
                    <td>
                        <button type="submit" class="button button-primary" name="save_changes" value="<?php echo esc_html( $source["key"] ) ?>">Save Changes</button>
                    </td>
                    <td>
                        <button type="submit" class="button button-secondary" name="delete_source" value="<?php echo esc_html( $source["key"] ) ?>">Delete</button>
                    </td>
                </tr>
                <?php endforeach; ?>

            </table>


            <h2 style="margin-top:30px">Add New Source</h2>
            <table class="widefat striped">
                <thead>
                <tr>
                    <th>Label</th>
                    <th>Description</th>
                    <th>Section</th>
                    <th></th>
                </tr>
                </thead>
                    <tr>
                        <td>
                            <input value="" name="new_label">
                        </td>
                        <td>
                            <textarea value="" name="new_description"></textarea>
                        </td>
                        <td>
                            <select name="new_section">
                                <option value="outreach">Outreach Section</option>
                                <option value="movement">Movement Section</option>
                            </select>
                        </td>

                        <td>
                            <button name="add_source" type="submit" class="button button-primary">Add Source</button>
                        </td>
                    </tr>
            </table>
        </form>



        <?php
        $this->box( 'bottom' );
    }

    public function save_settings(){
        if ( !empty( $_POST ) ){
            if ( isset( $_POST['sources_edit_nonce'] ) && wp_verify_nonce( sanitize_key( $_POST['sources_edit_nonce'] ), 'sources_edit' ) ) {
                if ( isset( $_POST["add_source"], $_POST["new_label"], $_POST["new_description"], $_POST["new_section"] ) ){
                    $label = sanitize_text_field( wp_unslash( $_POST["new_label"] ) );
                    $sources = get_option( 'dt_critical_path_sources', [] );
                    $key = dt_create_field_key( $label );
                    $cols = array_column( $sources, "key" );
                    //if the key is not unique, add a hash
                    if ( in_array( $key, $cols ) ){
                        $key = dt_create_field_key( $label, true );
                    }
                    $sources[] = [
                        'label' => $label,
                        'key' => $key,
                        'description' => sanitize_text_field( wp_unslash( $_POST["new_description"] ) ),
                        'section' => sanitize_key( wp_unslash( $_POST["new_section"] ) )
                    ];
                    update_option( 'dt_critical_path_sources', $sources );
                } elseif ( isset( $_POST["save_changes"], $_POST["label"], $_POST["description"], $_POST["section"] ) ){
                    $sources = get_option( 'dt_critical_path_sources', [] );
                    $key = sanitize_key( wp_unslash( $_POST["save_changes"] ) );
                    $index = array_search( $key, array_column( $sources, 'key' ) );
                    if ( isset( $_POST["label"][ $key ], $_POST["description"][ $key ], $_POST["section"][ $key ] ) ){
                        $sources[ $index ]["label"] = sanitize_text_field( wp_unslash( $_POST["label"][ $key ] ) );
                        $sources[ $index ]["description"] = sanitize_text_field( wp_unslash( $_POST["description"][ $key ] ) );
                        $sources[ $index ]["section"] = sanitize_text_field( wp_unslash( $_POST["section"][ $key ] ) );
                    }
                    update_option( 'dt_critical_path_sources', $sources );

                } elseif ( isset( $_POST["delete_source"] ) ){

                    $sources = get_option( 'dt_critical_path_sources', [] );
                    $key = sanitize_key( wp_unslash( $_POST["delete_source"] ) );
                    $index = array_search( $key, array_column( $sources, 'key' ) );
                    array_splice( $sources, $index, 1 );
                    update_option( 'dt_critical_path_sources', $sources );
                } elseif ( isset( $_POST["order_up"] ) || isset( $_POST["order_down"] ) ){
                    //move option  up or down
                    $up = isset( $_POST["order_up"] );
                    $option_key = $up ? sanitize_text_field( wp_unslash( $_POST["order_up"] ) ) : sanitize_text_field( wp_unslash( $_POST["order_down"] ) );
                    $direction = $up ? -1 : 1;
                    $sources = get_option( 'dt_critical_path_sources', [] );
                    $index = array_search( $option_key, array_column( $sources, 'key' ) );
                    $out = array_splice( $sources, $index, 1 );
                    array_splice( $sources, $index + $direction, 0, $out );
                    update_option( 'dt_critical_path_sources', $sources );
                }
            }
        }
    }


}
Disciple_Tools_Metric_Sources_Tab::instance();
