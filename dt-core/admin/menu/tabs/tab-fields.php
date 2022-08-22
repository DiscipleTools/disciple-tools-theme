<?php

if ( !defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class Disciple_Tools_Utilities_Fields_Tab
 */
class Disciple_Tools_Utilities_Fields_Tab extends Disciple_Tools_Abstract_Menu_Base
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
        add_submenu_page( 'dt_utilities', __( 'Field Explorer', 'disciple_tools' ), __( 'Fields Explorer', 'disciple_tools' ), 'manage_dt', 'dt_utilities&tab=fields', [ 'Disciple_Tools_Utilities_Menu', 'content' ] );
    }

    public function add_tab( $tab ) {
        echo '<a href="'. esc_url( admin_url() ).'admin.php?page=dt_utilities&tab=fields" class="nav-tab ';
        if ( $tab == 'fields' ) {
            echo 'nav-tab-active';
        }
        echo '">'. esc_attr__( 'Field Explorer', 'disciple_tools' ) .'</a>';
    }

    public function content( $tab ) {
        if ( 'fields' == $tab ) {

            $type = 'contacts';
            if ( isset( $_POST['post_type'], $_POST['post_type_select_nonce'] ) && ! empty( $_POST['post_type_select_nonce'] )
                 && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['post_type_select_nonce'] ) ), 'post_type_select' ) ) {
                $type = sanitize_text_field( wp_unslash( $_POST['post_type'] ) );
            }

            self::template( 'begin' );

            $this->post_type_select( $type );

            $this->box_message( $type );

            self::template( 'right_column' );

            self::template( 'end' );
        }
    }

    public function post_type_select( $type ){
        $this->box( 'top', 'Select Post type' );
        global $wp_post_types;
        $post_types = DT_Posts::get_post_types();
        ?>
        <form method="post" action="">
            <?php wp_nonce_field( 'post_type_select', 'post_type_select_nonce', false, true ) ?>
            <select name="post_type">
            <?php foreach ( $post_types as $post_type ) : ?>
                <option value="<?php echo esc_html( $post_type ); ?>" <?php echo $post_type === $type ? "selected" : "" ?> ><?php echo esc_html( $wp_post_types[$post_type]->label ); ?></option>
            <?php endforeach; ?>
            </select>
            <button class="button" type="submit">Select</button>
        </form>
        <?php
        $this->box( 'bottom' );
    }

    public function box_message( $post_type ) {
        $post_settings = DT_Posts::get_post_settings( $post_type );
        $this->box( 'top', $post_settings['label_plural'] . ' Fields on this Instance' );

        ?>
        <p>Note: Here are this fields available on this Instance. Some are default fields, some are installed by plugins or in the settings page.</p>
        <?php

        $fields = $post_settings["fields"];

        /* breadcrumb: new-field-type Add field type to field explorer */
        $types = [ "text", "textarea", "date", 'boolean', 'key_select', 'multi_select', 'array', 'connection', 'number', 'link', 'communication_channel', 'tags', 'user_select', 'task', 'location', 'location_meta' ];
        foreach ( $types as $type ){
            ?>
            <h3>Field type: <?php echo esc_html( $type ) ?></h3>

            <table class="widefat striped">
                <tr>
                    <th style="width:5%"></th>
                    <th style="width:20%">Name</th>
                    <th style="width:20%">Key</th>
                    <th style="width:10%">Type</th>
                    <th style="width:5%; text-align: center;">Icon</th>
                    <th style="width:40%">Details</th>
                </tr>
            <?php
            foreach ( $fields as $field_key => $field_value ){
                if ( $type === $field_value["type"] ){
                    ?>
                    <tr>
                        <td>
                            <form method="get">
                                <input type="hidden" name="field_select_nonce" id="field_select_nonce" value="<?php echo esc_attr( wp_create_nonce( 'field_select' ) ) ?>" />
                                <input type="hidden" name="page" value="dt_options" />
                                <input type="hidden" name="tab" value="custom-fields" />
                                <input type="hidden" name="post_type" value="<?php echo esc_attr( $post_type ); ?>" />
                                <input type="hidden" name="field-select" value="<?php echo esc_html( $post_type . '_' . $field_key ) ?>" />

                                <button type="submit" class="button" name="field_selected"><?php esc_html_e( 'Edit', 'disciple-tools' ) ?></button>
                            </form>
                        </td>
                        <td><?php echo esc_html( $field_value["name"] ) ?></td>
                        <td><?php echo esc_html( $field_key ) ?></td>
                        <td><?php echo esc_html( $field_value["type"] ) ?></td>
                        <td style="text-align: center;">
                            <?php if ( isset( $field_value["icon"] ) ): ?>

                                <img style="max-height: 15px; max-width: 15px;" src="<?php echo esc_html( $field_value["icon"] ) ?>" alt="">

                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ( ( $field_value['type'] === "key_select" || $field_value["type"] === "multi_select" || $field_value['type'] === "link" ) && !empty( $field_value["default"] ) ) : ?>
                            Options:
                            <ul style="margin-top:0; list-style: circle; padding-inline-start: 40px;">
                                <?php foreach ( $field_value["default"] as $option_key => $option_value ) :
                                    if ( isset( $option_value["label"] ) ) : ?>
                                    <li>
                                        <?php if ( isset( $option_value["icon"] ) ) : ?>

                                            <img style="max-height: 15px; max-width: 15px;" src="<?php echo esc_html( $option_value["icon"] ) ?>" alt="">

                                        <?php endif; ?>
                                        <?php echo esc_html( $option_key ) ?> => <?php echo esc_html( $option_value["label"] ) ?></li>
                                    <?php endif;
                                endforeach; ?>
                            </ul>
                            <?php elseif ( $field_value['type'] === 'connection' ): ?>
                                p2p_key: <?php echo esc_html( $field_value["p2p_key"] ) ?> <br>
                                p2p_direction: <?php echo esc_html( $field_value["p2p_direction"] ) ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php
                }
            }
            ?>
            </table>
            <?php
        }

        $this->box( 'bottom' );
    }
}
Disciple_Tools_Utilities_Fields_Tab::instance();
