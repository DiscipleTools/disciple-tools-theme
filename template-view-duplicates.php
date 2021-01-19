<?php
/*
Template Name: View Duplicates
*/
dt_please_log_in();

if ( ! current_user_can( 'dt_all_access_contacts' ) || !dt_is_module_enabled( "access_module" ) ) {
    wp_safe_redirect( '/settings' );
    exit();
}
get_header();

?>

    <div id="content" class="template-view-duplicates duplicates-page">

        <div id="inner-content" class="grid-x grid-margin-x">

            <main id="main" class="large-12 medium-12 cell" role="main">
                <div class="bordered-box">
                    <h1><?php esc_html_e( 'Duplicate Access Contacts', 'disciple_tools' ) ?>
                        <span id="duplicates-spinner" class="loading-spinner"></span>
                        <button class="help-button float-right" data-section="duplicates-template-help-text">
                            <img class="help-icon" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/help.svg' ) ?>"/>
                        </button>
                    </h1>
                    <p><?php esc_html_e( 'Scanning for duplicates starting with the most recently modified contacts. Results are limited to ones that have less than 10 exact matches.', 'disciple_tools' ); ?></p>
                    <p>
                        <span class="loading-spinner active"></span>
                        <?php echo esc_html( _x( "Access contacts scanned:", 'Access contact scanned: 100', 'disciple_tools' ) ); ?> <span id="scanned_number">0</span>.
                        <?php echo esc_html( _x( "Duplicates found:", 'Duplicate contacts found: 100', 'disciple_tools' ) ); ?> <span id="found_text">0</span>.
                    </p>
                </div>
                <div id="duplicates-content" class="grid-y grid-margin-y" style="margin-top:50px">

                </div>
            </main> <!-- end #main -->
        </div> <!-- end #inner-content -->


    </div> <!-- end #content -->

<?php get_footer(); ?>
