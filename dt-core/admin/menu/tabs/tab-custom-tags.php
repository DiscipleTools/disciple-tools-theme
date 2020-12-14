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
                if ( isset( $_POST['tags'] ) ) {
                    if( $_POST['select_action'] == 'Bulk actions' ) {
                        /* 
                         * Checks to see if new tag already exists and if so, skips it.
                         * Also skips updating tags that don't have any edits made on them.
                         */
                        $tags = $_POST['tags'];
                        foreach( $tags as $tag ) {
                            if( empty( $tag['new'] ) ){
                                continue;
                            }
                            
                            $tag_old = $tag['old'];
                            $tag_new = $tag['new'];
                                                    
                            $retval = self::process_edit_tag( $tag_old, $tag_new);

                            if ( $retval ) {
                                self::admin_notice( __( "Tag '$tag_old' is now called '$tag_new'.", 'disciple_tools'), 'success');
                                continue;
                            }
                        }
                    }

                    /** Checks if Delete option was selected in dropdown menu */
                    if( $_POST['select_action'] == 'Delete' ) {
                        
                         /** If dropdown is delete but no checkboxes were selected, do nothing. */
                        if ( !isset($_POST['checkbox_delete_tag'] ) ) {
                            return;
                        }
                        foreach( $_POST['checkbox_delete_tag'] as $delete_tag ) {
                            self::process_delete_tag( esc_html( $delete_tag ) );
                        }
                    }
                }
            }

            $this->box( 'top', __( 'Edit or delete tags', 'disciple_tools' ) );
            $this->tag_select();
            $this->box( 'bottom' );
    endif;
    }

    /*
     * Get all created tags
     */
    private function get_all_tags() {
        global $wpdb;

        $query = "
            SELECT DISTINCT meta_value
            FROM $wpdb->postmeta
            WHERE meta_key = 'tags'
            ORDER BY meta_value ASC;";

        $results = $wpdb->get_col($query);
        return $results;
    }


    /*
     * Delete tag from database
     */
    private function process_delete_tag( string $tag_delete ) {        
        global $wpdb;
            $query = "
                DELETE FROM $wpdb->postmeta
                WHERE meta_key = 'tags'
                AND meta_value = %s;";

            $retval = $wpdb->query( $wpdb->prepare( $query, $tag_delete ) );
            if( $retval ) {
                self::admin_notice( __( "Tag '" . esc_html( $tag_delete ) . "' deleted successfully ", 'disciple_tools' ), 'success' );
            } else {
                self::admin_notice( __( "Error deleting tag '". esc_html( $tag_delete ) . "'", 'disciple_tools' ), 'error' );
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
            #$tags = self::get_all_tags();
            $tags = Disciple_Tools_Posts::get_single_select_options('tags');
            
            for( $i=0;$i<=count($tags)-1;$i++): ?>
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
        $tag_old = sanitize_text_field( wp_unslash( $tag_old ) );
        $tag_new = sanitize_text_field( wp_unslash( $tag_new ) );

        if ( !wp_verify_nonce( sanitize_key( $_POST['tag_edit_nonce'] ), 'tag_edit' ) ) {
                    return;
                }

        if ( !empty( $tag_new ) ) {
            global $wpdb;

        $query = "
            UPDATE $wpdb->postmeta
            SET meta_value = %s
            WHERE meta_value = %s
            AND meta_key = 'tags';";

            $retval = $wpdb->query( $wpdb->prepare( $query, $tag_new, $tag_old ) );
            if( $retval ) {
                self::admin_notice( __( "Tag edited successfully: '" . esc_html ( $tag_old ) . "' -> '" . esc_html( $tag_new ) ."'", 'disciple_tools' ), 'success' );
            } else {
                self::admin_notice( __( "Error editing tag $tag_old into $tag_new", 'disciple_tools' ), 'error' );
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