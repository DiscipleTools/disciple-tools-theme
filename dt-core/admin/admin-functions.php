<?php

function dt_display_translation_dialog() {
    echo '<dialog id="dt_translation_dialog"></dialog>';
}

/**
 * Append addition information to WordPress Export Form.
 */

add_action( 'export_filters', 'export_filters' );
function export_filters() {
    ?>
    <h2><?php esc_html_e( 'Disciple.Tools Notices' ); ?></h2>
    <ul style="list-style-type: circle !important; margin-left: 20px;">
        <li>See <a href="https://disciple.tools/user-docs/features/wp-export-and-import-contacts/">instructions</a> on exporting and importing.</li>
        <li><img class="dt-icon" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/broken.svg' ) ?>"/>Not all fields can be exported an imported. See <a href="https://disciple.tools/user-docs/features/wp-export-and-import-contacts/">details</a>. You may consider using the csv export and import instead.</li>

        <li>You will need to set up any custom fields on your new Disciple.Tools instance. Here is how to <a href="https://disciple.tools/user-docs/getting-started-info/admin/utilities-dt/exporting-importing-settings/">export and import settings</a>.</li>
        <li>Also, set up custom posts types on your new Disciple.Tools instance before importing. See <a href="https://disciple.tools/user-docs/getting-started-info/admin/utilities-dt/exporting-importing-settings/">export and import settings</a>.</li>

    </ul>
    <?php
}
