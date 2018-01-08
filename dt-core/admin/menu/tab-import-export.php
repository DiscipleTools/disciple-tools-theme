<?php

/**
 * Disciple Tools
 *
 * @class      Disciple_Tools_
 * @version    0.1.0
 * @since      0.1.0
 * @package    Disciple_Tools
 * @author     Chasm.Solutions & Kingdom.Training
 */

if ( !defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class Disciple_Tools_Import_Export_Tab
 */
class Disciple_Tools_Import_Export_Tab
{
    /**
     * Packages and prints tab page
     */
    public function content()
    {
        echo '<div class="wrap"><div id="poststuff"><div id="post-body" class="metabox-holder columns-2">';
        echo '<div id="post-body-content">';
        /* Main Column */

        /* Box */
        echo '<table class="widefat striped">
                    <thead><th>Import Disciple Tools Data</th></thead>
                    <tbody><tr><td>';

        echo '(Planned)';

        echo '</td></tr></tbody></table><br>';
        /* End Box */

        /* Box */
        echo '<table class="widefat striped">
                    <thead><th>Export Disciple Tools Data</th></thead>
                    <tbody><tr><td>';

        echo '(Planned)';

        echo '</td></tr></tbody></table><br>';
        /* End Box */

        /* Box */
        echo '<table class="widefat striped">
                    <thead><th>Delete Disciple Tools Data</th></thead>
                    <tbody><tr><td>';

        echo '(Planned)';

        echo '</td></tr></tbody></table><br>';
        /* End Box */

        /* End Main Column */
        echo '</div><!-- end post-body-content --><div id="postbox-container-1" class="postbox-container">';
        /* Right Column */

        /* Box */
        echo '<table class="widefat striped">
                    <thead><th>Instructions</th></thead>
                    <tbody><tr><td>';

        echo '(Planned)';

        echo '</td></tr></tbody></table><br>';
        /* End Box */

        /* End Right Column*/
        echo '</div><!-- postbox-container 1 --><div id="postbox-container-2" class="postbox-container">';
        echo '</div><!-- postbox-container 2 --></div><!-- post-body meta box container --></div><!--poststuff end --></div><!-- wrap end -->';
    }

}
