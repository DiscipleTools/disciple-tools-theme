<?php
/**
 * Text Domain: drm
 * Modified version of Plugin 'Three Column Screen Layout' (http://wordpress.org/plugins/three-column-screen-layout/)
 * Modification date: 19 January 2017
 * Description: Three, four and five column screen layouts for the post editor.
 * Default view for contacts admin page set to 2 equal width columns
 * Original Version: 4.2
 * Original Author: Chad Hovell
 * Original Author URI: http://www.chadhovell.com.au
 * License: GPLv2 or later
 * Copyright 2016 Chad Hovell (email: chadhovell@gmail.com)
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */

if( !class_exists( 'Disciple_Tools_Three_Column_Screen_Layout' ) ) {
    /**
     * Class Disciple_Tools_Three_Column_Screen_Layout
     */
    class Disciple_Tools_Three_Column_Screen_Layout
    {
        /**
         * Disciple_Tools_Three_Column_Screen_Layout constructor.
         */
        public function __construct()
        {
            register_activation_hook( __FILE__, [ $this, 'activate' ] );
            register_deactivation_hook( __FILE__, [ $this, 'deactivate' ] );
            if( function_exists( 'get_bloginfo' ) && version_compare( get_bloginfo( 'version' ), '3.4' ) >= 0 ) {
                global $pagenow;
                if( is_admin() && in_array( $pagenow, [ 'post.php', 'post-new.php', 'index.php?page=media-report' ] ) && in_array( 'contacts', [ 'contacts' ] ) ) {
                    add_action( 'admin_head', [ $this, 'admin_head' ] );
                    add_action( 'admin_footer', [ $this, 'admin_footer' ] );
                    add_action( 'admin_enqueue_scripts', [ $this, 'admin_scripts' ] );
                }
            }
        }

        public function activate()
        {
            global $wpdb;
            if( $wpdb->get_results( sprintf( "SELECT * FROM %s WHERE option_name = '_site_transient_update_plugins' AND option_value LIKE '%%three-column-screen-layout%%';", $wpdb->options ) ) ) {
                $wpdb->query( sprintf( "UPDATE %s SET meta_value = REPLACE(meta_value, 's:8:\"advanced\"', 's:5:\"side3\"') WHERE meta_key LIKE 'meta-box-order_%%';", $wpdb->usermeta ) );
            }
        }

        public function deactivate()
        {
            global $wpdb;
            $wpdb->query( sprintf( "UPDATE %s SET meta_value = REPLACE(meta_value, 's:5:\"side3\"', 's:6:\"normal\"'), meta_value = REPLACE(meta_value, 's:5:\"side4\"', 's:6:\"normal\"') WHERE meta_key LIKE 'meta-box-order_%%';", $wpdb->usermeta ) );
        }

        public function admin_head()
        {
            ob_start();
            add_screen_option( 'layout_columns', [ 'max' => 24, 'default' => 2 ] );
        }

        public function admin_footer()
        {
            $this->splice_columns( ob_get_clean() );
        }

        public function admin_scripts()
        {
            wp_enqueue_style( 'Disciple_Tools_Three_Column_Screen_Layout-style', disciple_tools()->plugin_css_url . 'three-column-screen-layout.min.css?v=4.2' );
        }

        /**
         * @param $i
         *
         * @return string
         */
        protected function create_metabox( $i )
        {
            global $post_type;
            global $post;
            ob_start();
            $name = sprintf( 'side%d', $i );
            // @codingStandardsIgnoreLine
            do_action( 'do_meta_boxes', $post_type, $name, $post );
            do_meta_boxes( $post_type, $name, $post );

            return sprintf( '<div id="postbox-container-%d" class="postbox-container">%s</div>', $i, ob_get_clean() );
        }

        /**
         * @param $content
         */
        protected function splice_columns( $content )
        {
            global $post_type;
            $pref_start = strpos( $content, 'class="screen-layout"' );
            $pref_end = strpos( $content, 'id="screenoptionnonce"', $pref_start );
            $postbody_start = strpos( $content, 'id="post-body"', $pref_start );
            $columns_start = strpos( $content, 'metabox-holder columns-', $postbody_start ) + 23;
            $container_start = strpos( $content, '<div id="postbox-container-2"', $postbody_start );

            if( $pref_start && $pref_end && $postbody_start && $columns_start && $container_start ) {
                $pref_old = substr( $content, $pref_start, $pref_end - $pref_start );

                // if we are on the contacts page, force the layout to be 2 equal width columns
                $pref_val = in_array( $post_type, [ 'contacts' ] ) ? 3 : ( preg_match( "/value='(\d+)'[\r\n\s]+checked/", $pref_old, $matches ) ? $matches[ 1 ] : 2 );
                $pref_new = preg_replace( '/(>)[^<]*(<label)/', '$1$2', $pref_old );
                $pref_new = preg_replace( '/(\/>)[\r\n\s]+[^<]*[\s]+(<\/label>)\s*/', '$1<span class="columns-prefs-icon"></span>$2', $pref_new );

                if( $pref_new != $pref_old ) {
                    $content = substr_replace( $content, $this->create_metabox( 3 ) . $this->create_metabox( 4 ), $container_start, 0 );
                    $content = substr_replace( $content, $pref_val, $columns_start, 1 );
                    $content = substr_replace( $content, $pref_new, $pref_start, $pref_end - $pref_start );
                }
            }

            echo $content;
        }
    }

    new Disciple_Tools_Three_Column_Screen_Layout();
}
?>
