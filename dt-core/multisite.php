<?php
/**
 * Multi-site specific configurations
 */

if ( is_multisite() ) {
    /**
     * Makes sure the login link presented on the wp-activate.php page is to the subdomain, not the root domain.
     *
     * @param $url
     * @param $path
     * @param $scheme
     *
     * @return string
     */
    function dt_network_site_url_to_specific_blog( $url, $path, $scheme ){

        global $pagenow;
        if ( 'wp-activate.php' === substr( $pagenow, 0, 15 ) ) {
            $url = site_url();
        }

        return $url;
    }
    add_filter( 'network_site_url', 'dt_network_site_url_to_specific_blog', 3, 999 );

    /**
     * Adds some simple styling to the head of the wp-activate.php page
     */
    function dt_custom_activate_head(){
        ?>
        <style>
            .wp-activate-container {
                width: 80%;
                margin: 0 auto;
                padding: 2em;
                color:black;
            }
            .wp-activate-container h2 {
                color:black;
            }
        </style>
        <script></script>
        <?php
    }
    add_action( 'activate_wp_head', 'dt_custom_activate_head' );
}
