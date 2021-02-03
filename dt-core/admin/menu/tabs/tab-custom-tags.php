<?php

if ( !defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Disciple Tools
 *
 * @class      Disciple_Tools_Tab_Custom_Fields
 * @version    0.1.0
 * @since      0.1.0
 * @package    Disciple_Tools
 * @author     Prykon
 */


/**
 * Class Disciple_Tools_Tab_Custom_Fields
 */
class Disciple_Tools_Tab_Custom_Tags extends Disciple_Tools_Abstract_Menu_Base
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
        add_action( 'dt_settings_tab_menu', [ $this, 'add_tab' ], 10, 1 );
        add_action( 'dt_settings_tab_content', [ $this, 'content' ], 99, 1 );

        parent::__construct();
    } // End __construct()

    public function add_submenu() {
        add_submenu_page( 'dt_options', __( 'Tags', 'disciple_tools' ), __( 'Tags', 'disciple_tools' ), 'manage_dt', 'dt_options&tab=tags', [ 'Disciple_Tools_Settings_Menu', 'content' ] );
    }

    public function add_tab( $tab ) {
        ?>
        <a href="<?php echo esc_url( admin_url() ) ?>admin.php?page=dt_options&tab=tags"
           class="nav-tab <?php echo esc_html( $tab == 'tags' ? 'nav-tab-active' : '' ) ?>">
            <?php echo esc_html__( 'Tags' ) ?>
        </a>
        <?php
    }

    /** Sanitizes each element in an array */
    private static function dt_recursive_sanitize_array_field( $post, $key ) {
        if ( !isset( $post[$key] ) ){
            return false;
        }
        $post[$key] = dt_recursive_sanitize_array( $post[$key] );
        return $post[$key];
    }

    /**
     * Packages and prints tab page
     *
     * @param $tab
     */
    public function content( $tab ) {
        if ( $tab == 'tags' ) :
            $this->template( 'begin' );

            /*
             * Save tag changes to database
             */
            if ( isset( $_POST['tag_edit_nonce'] ) ) {
                if ( !wp_verify_nonce( sanitize_key( $_POST['tag_edit_nonce'] ), 'tag_edit' ) ) {
                    return;
                }

                /** Checks to see if tag has both an old and new name. */
                if ( isset( $_POST['tags'] ) && isset( $_POST['select_action'] ) ) {
                    if ( sanitize_text_field( wp_unslash( $_POST['select_action'] ) ) == 'Bulk actions' ) {
                        /*
                         * Checks to see if new tag already exists and if so, skips it.
                         * Also skips updating tags that don't have any edits made on them.
                         */
                        $tags = self::dt_recursive_sanitize_array_field( $_POST, 'tags' );
                        foreach ( $tags as $tag ) {
                            if ( empty( $tag['new'] ) ) {
                                continue;
                            }

                            $tag_old = $tag['old'];
                            $tag_new = $tag['new'];

                            $retval = self::process_edit_tag( $tag_old, $tag_new );

                            if ( $retval ) {
                                self::admin_notice( "Tag '$tag_old' is now called '$tag_new'.", 'success' );
                                continue;
                            }
                        }
                    }

                    /** Checks if Delete option was selected in dropdown menu */
                    if ( $_POST['select_action'] == 'Delete' ) {

                         /** If dropdown is delete but no checkboxes were selected, do nothing. */
                        if ( !isset( $_POST['checkbox_delete_tag'] ) ) {
                            return;
                        }
                        foreach ( self::dt_recursive_sanitize_array_field( $_POST, 'checkbox_delete_tag' ) as $delete_tag ) {
                            self::process_delete_tag( esc_html( $delete_tag ) );
                        }
                    }
                }
            }

            $this->box( 'top', __( 'Edit, merge or delete tags', 'disciple_tools' ) );
            $this->tag_select();
            $this->box( 'bottom' );

            /** Right Column */
            $this->template( 'right_column' );
            $this->box( 'top', 'Help' );
            $this->add_help_box();
            $this->box( 'bottom' );

            /** End */
            $this->template( 'end' );
    endif;
    }


    public function add_help_box() {
        ?>
        <form method="post">
            <dl>
                <dt>
                    <p>
                    Bulk edit your tags from a single page.
                    <br>
                    <br>
                    <b>Edit:</b><br> Write the new name for the tag and click 'Apply changes'.
                    <br>
                    <br>
                    <b>Merge:</b><br> Write the name of the tag you want to merge into and click 'Apply changes'.
                    <br>
                    <br>
                    <b>Delete:</b><br> Check all the tags you no longer need, select 'Delete' from the 'Bulk actions' dropdown and click 'Apply changes'.
                    </p>
                </dt>
            </dl>
        </form>
        <?php
    }


    /*
     * Get all created tags
     */
    private function get_all_tags() {
        global $wpdb;

        $results = $wpdb->get_col("
            SELECT DISTINCT meta_value
            FROM $wpdb->postmeta
            WHERE meta_key = 'tags'
            ORDER BY meta_value ASC;" );

        return $results;
    }


    /*
     * Delete tag from database
     */
    private function process_delete_tag( string $tag_delete ) {
        global $wpdb;

            $retval = $wpdb->delete( $wpdb->postmeta, [ 'meta_value' => esc_sql( $tag_delete ) ] );

        if ( $retval ) {
                self::admin_notice( "Tag '" . esc_html( $tag_delete ) . "' deleted successfully ", 'success' );
        } else {
                self::admin_notice( "Error deleting tag '" . esc_html( $tag_delete ) . "'", 'error' );
        }

            return $tag_delete;
    }


    /*
     * Displays a table with all created tags
     */
    private function tag_select() {
        ?>
        <form method="post" name="tag_select" id="tag-select">
            <input type="hidden" name="tag_edit_nonce" id="tag-edit-nonce" value="<?php echo esc_attr( wp_create_nonce( 'tag_edit' ) ) ?>" />
            <table>
                <thead>
            <tr>
                <td>
                    <select name="select_action" id="select-action">
                        <option>Bulk actions</option>
                        <option>Delete</option>
                    </select></td>
                <td><?php esc_html_e( "Tag name", 'disciple_tools' ) ?></td>
                <td><?php esc_html_e( "New name", 'disciple_tools' ) ?></td>
            </td>
            </tr>
            </thead>
            <?php

            $tags = self::get_all_tags();
            $tags_amount = count( $tags );

            for ( $i = 0; $i <= $tags_amount - 1; $i++): ?>
                <tr>
                    <td style="vertical-align: middle">
                        <input type="checkbox" name="checkbox_delete_tag[<?php echo esc_html( $tags[$i]['old'] ?? $tags[$i] ); ?>]" value="<?php echo esc_html( $tags[$i]['old'] ?? $tags[$i] ); ?>">
                    </td>
                    <td>
                        <input type="text" name="<?php echo esc_html( "tags[$i][old]" ?? "tags[$i]" ); ?>" value="<?php echo esc_html( $tags[$i]['old'] ?? $tags[$i] ); ?>" readonly>
                    </td>
                    <td>
                        <input type="text" name="<?php echo esc_html( "tags[$i][new]" ?? '' ); ?>">
                    </td>
                </tr>
            <?php endfor; ?>
                <tr>
                    <td colspan="3" align="right"><button type="submit" class="button"><?php esc_html_e( "Apply changes", 'disciple_tools' ) ?></button></td>
                </tr>
            </table>
            <br>
        </form>

    <?php }

    /*
     * Save tag changes to database
     */
    private function process_edit_tag( string $tag_old, string $tag_new ) {
        if ( !isset( $_POST['tag_edit_nonce'] ) || !wp_verify_nonce( sanitize_key( $_POST['tag_edit_nonce'] ), 'tag_edit' ) ) {
            return;
        }

        if ( !empty( $tag_new ) ) {
            global $wpdb;

            $retval = $wpdb->update(
                $wpdb->postmeta,
                [
                    'meta_value' => esc_sql( $tag_new ),
                ],
                [
                    'meta_key' => 'tags',
                    'meta_value' => esc_sql( $tag_old ),
                ]
            );

            if ( $retval ) {
                self::admin_notice( "Tag edited successfully: '$tag_old' -> '$tag_new'", 'success' );
            } else {
                self::admin_notice( "Error editing tag $tag_old into $tag_new", 'error' );
            }
        }
        return;
    }

    /**
     * Display admin notice
     *
     * @param $notice string
     * @param $type string error|success|warning
     */
    public static function admin_notice( string $notice, string $type ) {
        ?>
        <div class="notice notice-<?php echo esc_attr( $type ) ?> is-dismissible">
            <p><?php echo esc_html( $notice ); ?></p>
        </div>
        <?php
    }
}
Disciple_Tools_Tab_Custom_Tags::instance();
