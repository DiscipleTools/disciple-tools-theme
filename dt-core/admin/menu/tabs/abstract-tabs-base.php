<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

/**
 * Empty class for now..
 * Class Disciple_Tools_Notifications_Hook_Base
 */
abstract class Disciple_Tools_Abstract_Menu_Base
{

    /**
     * Disciple_Tools_Notifications_Hook_Base constructor.
     */
    public function __construct() {
    }

    /**
     * Implementation of Template
     * The template is intended to reduce the HTML needed for repeatable WP admin page framework
     *
     * Two Column Implementation
            $this->template( 'begin' );
            $this->template( 'right_column' );
            $this->template( 'end' );
     *
     * One Column Implementation
            $this->template( 'begin' );
            $this->template( 'end' );
     *
     * @param     $section
     * @param int $columns
     */
    public function template( $section, $columns = 2 ) {
        switch ( $columns ) {

            case '1':
                switch ( $section ) {
                    case 'begin':
                        ?>
                        <div class="wrap">
                        <div id="poststuff">
                        <div id="post-body" class="metabox-holder columns-1">
                        <div id="post-body-content">
                        <!-- Main Column -->
                        <?php
                        break;


                    case 'end':
                        ?>
                        </div><!-- postbox-container 1 -->
                        </div><!-- post-body meta box container -->
                        </div><!--poststuff end -->
                        </div><!-- wrap end -->
                        <?php
                        break;
                }
                break; // end case 1

            case '2':
                switch ( $section ) {
                    case 'begin':
                        ?>
                        <div class="wrap">
                        <div id="poststuff">
                        <div id="post-body" class="metabox-holder columns-2">
                        <div id="post-body-content">
                        <!-- Main Column -->
                        <?php
                        break;

                    case 'right_column':
                        ?>
                        <!-- End Main Column -->
                        </div><!-- end post-body-content -->
                        <div id="postbox-container-1" class="postbox-container">
                        <!-- Right Column -->
                        <?php
                        break;

                    case 'end':
                        ?>
                        </div><!-- postbox-container 1 -->
                        </div><!-- post-body meta box container -->
                        </div><!--poststuff end -->
                        </div><!-- wrap end -->
                        <?php
                        break;
                }
                break; // end case 2
        }
    }

    /**
     * @param        $section
     * @param string $title
     * @param array  $args
     *                    row_container removes the default containing row
     *                    col_span sets the number of columns the header should span
     *                    striped can remove the striped class from the table
     */
    public function box( $section, $title = '', $args = [] ) {

        $args = wp_parse_args( $args, [
                'row_container' => true,
                'col_span' => 1,
                'striped' => true,
        ] );

        switch ( $section ) {
            case 'top':
                ?>
                <!-- Begin Box -->
                <table class="widefat <?php echo $args['striped'] ? 'striped' : '' ?>">
                <thead><th colspan="<?php echo esc_attr( $args['col_span'] ) ?>"><?php echo esc_html( $title ) ?></th></thead>
                <tbody>

                <?php
                echo $args['row_container'] ? '<tr><td>' : '';

                break;
            case 'bottom':

                echo $args['row_container'] ? '</tr></td>' : '';
                ?>
                </tbody></table><br>
                <!-- End Box -->
                <?php
                break;
        }
    }

}
