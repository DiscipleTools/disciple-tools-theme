<?php

/**
 * Disciple_Tools_Add_Core_Pages
 * Class for creating core pages
 *
 * @package dt_demo
 */

if (!defined( 'ABSPATH' )) { exit; // Exit if accessed directly
}

class Disciple_Tools_Add_Core_Pages
{

    /**
     * Disciple_Tools_Add_Core_Pages The single instance of Disciple_Tools_Add_Core_Pages.
     * @var    object
     * @access  private
     * @since    1.0.0
     */
    private static $_instance = null;

    /**
     * Access plugin instance. You can create further instances by calling
     * the constructor directly.
     * @since 0.1
     * @static
     * @return Disciple_Tools_Add_Core_Pages instance
     */
    public static function instance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self;
        }
        return self::$_instance;
    }

    // Constructor class
    public function __construct() {
        if (get_option( 'dt_add_core_pages' ) !== '1') {
        
            $this->add_core_pages();
        
            $option = 'dt_add_core_pages';
            $value = '1';
            $deprecated = '';
            $autoload = false;
        
            add_option( $option, $value, $deprecated, $autoload );
        }
    }

    /**
     * Add core pages main function
     * @return string
     */
    protected function add_core_pages ()
    {
        
        require_once( ABSPATH . 'wp-admin/includes/post.php' );
        

        if ( true == get_post_status( 2 ) ) {    wp_delete_post( 2 );  } // Delete default page

        $postarr = array(
            array(
                'post_title'    =>  'Reports',
                'post_name'     =>  'reports',
                'post_content'  =>  'The content of the page is controlled by the Disciple Tools plugin, but this page is required by the plugin to display the dashboard.',
                'post_status'   =>  'Publish',
                'comment_status'    =>  'closed',
                'ping_status'   =>  'closed',
                'menu_order'    =>  '4',
                'post_type'     =>  'page',
            ),
            array(
                'post_title'    =>  'Profile',
                'post_name'     =>  'profile',
                'post_content'  =>  'The content of the page is controlled by the Disciple Tools plugin, but this page is required by the plugin to display the dashboard.',
                'post_status'   =>  'Publish',
                'comment_status'    =>  'closed',
                'ping_status'   =>  'closed',
                'menu_order'    =>  '4',
                'post_type'     =>  'page',
            ),
            array(
                'post_title'    =>  'About Us',
                'post_name'     =>  'about-us',
                'post_content'  =>  'The content of the page is controlled by the Disciple Tools plugin, but this page is required by the plugin to display the dashboard.',
                'post_status'   =>  'Publish',
                'comment_status'    =>  'closed',
                'ping_status'   =>  'closed',
                'menu_order'    =>  '4',
                'post_type'     =>  'page',
            ),
            array(
                'post_title'    =>  'Media',
                'post_name'     =>  'media',
                'post_content'  =>  'The content of the page is controlled by the Disciple Tools plugin, but this page is required by the plugin to display the dashboard.',
                'post_status'   =>  'Publish',
                'comment_status'    =>  'closed',
                'ping_status'   =>  'closed',
                'menu_order'    =>  '5',
                'post_type'     =>  'page',
            ),
        );

        foreach ($postarr as $item) {
            if (! post_exists( $item['post_title'] ) ) {
                wp_insert_post( $item, false );
            } else {
                $page = get_page_by_title( $item['post_title'] );
                wp_delete_post( $page->ID );
                wp_insert_post( $item, false );
            }
        }

        flush_rewrite_rules();

        return true;
    }

}
