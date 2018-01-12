<?php

/**
 * Disciple_Tools_Config_Contacts
 * This class serves as master configuration and modification class to the contacts post type within the admin screens.
 *
 * @class   Disciple_Tools_Config_Contacts
 * @version 0.1.0
 * @since   0.1.0
 * @package Disciple_Tools
 *
 */

if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * Class Disciple_Tools_Config_Contacts
 */
class Disciple_Tools_Config_Contacts {

    /**
     * Disciple_Tools_Config_Contacts The single instance of Disciple_Tools_Config_Contacts.
     *
     * @var    object
     * @access private
     * @since  0.1.0
     */
    private static $_instance = null;

    /**
     * Main Disciple_Tools_Config_Contacts Instance
     *
     * Ensures only one instance of Disciple_Tools_Config_Contacts is loaded or can be loaded.
     *
     * @since  0.1.0
     * @static
     * @return Disciple_Tools_Config_Contacts instance
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()

    /**
     * Constructor function.
     *
     * @access public
     * @since  0.1.0
     */
    public function __construct() {

        // Config columns
        add_filter( 'manage_contacts_posts_columns', [ $this, 'contacts_table_head' ] );
        add_action( 'manage_contacts_posts_custom_column', [ $this, 'contacts_table_content' ], 10, 2 );

        add_action( 'admin_menu', [ $this, 'remove_default_meta_boxes' ] );

    } // End __construct()

    /**
     * Configure Contacts column header
     *
     * @param  $defaults
     * @return mixed
     */
    public function contacts_table_head( $defaults ) {
        $defaults['assigned_to']  = 'Assigned To';
        $defaults['seeker_path']    = 'Seeker Path';
        $defaults['seeker_milestones']    = 'Seeker Milestone';
        return $defaults;
    }

    /**
     * Configure Contacts column content
     *
     * @param $column_name
     * @param $post_id
     */
    public function contacts_table_content( $column_name, $post_id ) {
        if ($column_name == 'assigned_to') {
            echo esc_html( get_post_meta( $post_id, 'assigned_to', true ) );
        }
        if ($column_name == 'seeker_path') {
            $status = get_post_meta( $post_id, 'seeker_path', true );
            echo esc_html( $status );
        }

        if ($column_name == 'seeker_milestones') {
            echo esc_html( get_post_meta( $post_id, 'seeker_milestones', true ) );
        }

    }

    /**
     * Removes default metaboxes
     *
     * @see https://codex.wordpress.org/Function_Reference/remove_meta_box
     */
    public function remove_default_meta_boxes() {

        remove_meta_box( 'linktargetdiv', 'link', 'normal' );
        remove_meta_box( 'linkxfndiv', 'link', 'normal' );
        remove_meta_box( 'linkadvanceddiv', 'link', 'normal' );
        remove_meta_box( 'postexcerpt', 'contacts', 'normal' );
        remove_meta_box( 'trackbacksdiv', 'contacts', 'normal' );
        remove_meta_box( 'postcustom', 'contacts', 'normal' );
        remove_meta_box( 'commentstatusdiv', 'contacts', 'normal' );
        remove_meta_box( 'revisionsdiv', 'contacts', 'normal' );
        remove_meta_box( 'slugdiv', 'contacts', 'normal' );
        remove_meta_box( 'authordiv', 'contacts', 'normal' );
        remove_meta_box( 'sqpt-meta-tags', 'contacts', 'normal' );
    }

}



