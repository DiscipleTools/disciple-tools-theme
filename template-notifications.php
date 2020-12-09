<?php
/*
Template Name: Notifications
*/
dt_please_log_in();

if ( ! current_user_can( 'access_contacts' ) ) {
    wp_safe_redirect( '/settings' );
    exit();
}

$dt_user = wp_get_current_user(); // query to get new notifications

get_header(); ?>

<script>

    jQuery(document).ready(function () {
        get_notifications(false, true);
    });

</script>


<div id="content" class="template-notifications notifications-page">

    <div id="inner-content" class="grid-x grid-margin-x">

        <div class="large-8 large-offset-2  small-12 cell ">

            <div class="bordered-box">

                <h3 class="section-header"><?php esc_html_e( "Notifications", "disciple_tools" ); ?><button class="help-button float-right" data-section="notifications-template-help-text">
                    <img class="help-icon" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/help.svg' ) ?>"/>
                </button></h3>

                <div class="grid-x">
                    <div class="cell">
                        <div class="grid-x grid-margin-x " style="border-bottom: 1px solid #ccc;">
                            <div class="small-4 medium-5 cell"><span class="badge alert notification-count"
                                                                     style="display:none;">&nbsp;</span>
                                <strong><?php esc_html_e( 'New', 'disciple_tools' )?></strong></div>
                            <div class="small-4 medium-2 cell">
                                <div class="expanded small button-group">
                                    <button id="all" type="button"
                                            onclick="toggle_buttons('all'); get_notifications( all = true, true, );"
                                            class="button hollow"><?php echo esc_html_x( 'All', 'List Filters', 'disciple_tools' ) ?>
                                    </button>
                                    <button id="new" type="button"
                                            onclick="toggle_buttons('new'); get_notifications( all = false, true );"
                                            class="button"><?php esc_html_e( 'Unread', 'disciple_tools' )?>
                                    </button>
                                </div>
                            </div>
                            <div class="small-4 medium-5 cell" style="text-align:right;">
                                <span class="hide-for-small-only">
                                    <a onclick="mark_all_viewed()"><?php esc_html_e( 'Mark all as read', 'disciple_tools' ) ?></a>  -
                                    <a href="<?php echo esc_url( home_url( '/' ) ); ?>settings/#notifications">
                                        <?php esc_html_e( 'Settings', 'disciple_tools' )?>
                                    </a>
                                </span>
                                <span class="show-for-small-only">
                                    <a onclick="mark_all_viewed()"><?php esc_html_e( 'Mark All', 'disciple_tools' ) ?></a>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="notification-list" class="grid-x"><span class="loading-spinner active" style="margin:1em;"></span></div>

                <div class="grid-x">
                    <div class="cell">
                        <div class="grid-x grid-margin-x grid-margin-y">
                            <div class="small-12 medium-6 medium-offset-3 cell center">
                                <a id="next-all" onclick="get_notifications( true, false )" style="display:none;">
                                    <?php esc_html_e( 'Load more notifications', 'disciple_tools' )?>
                                </a>
                                <a id="next-new" onclick="get_notifications( false, false )">
                                    <?php esc_html_e( 'Load more notifications', 'disciple_tools' ) ?>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </div> <!-- end #inner-content -->

</div> <!-- end #content -->

<?php get_footer(); ?>
