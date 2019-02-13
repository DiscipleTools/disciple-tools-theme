<?php
/*
Template Name: Notifications
*/

if ( ! current_user_can( 'access_contacts' ) ) {
    wp_safe_redirect( '/settings' );
}

$dt_user = wp_get_current_user(); // query to get new notifications

get_header(); ?>

<script>

    jQuery(document).ready(function () {
        get_notifications(false, true);
    });

</script>

<div id="content">

    <div id="inner-content" class="grid-x grid-margin-x">

        <div class="large-8 large-offset-2  small-12 cell ">

            <div class="bordered-box">

                <div class="grid-x">
                    <div class="cell">
                        <div class="grid-x grid-margin-x " style="border-bottom: 1px solid #ccc;">
                            <div class="small-4 medium-5 cell"><span class="badge alert notification-count"
                                                                     style="display:none;">&nbsp;</span>
                                <strong><?php esc_html_e( 'New', 'disciple_tools' )?></strong></div>
                            <div class="small-4 medium-2 cell">
                                <div class="expanded small button-group" style="text-align:center;">
                                    <button id="all" type="button"
                                            onclick="toggle_buttons('all'); get_notifications( all = true, true );"
                                            class="button hollow"><?php esc_html_e( 'All', 'disciple_tools' )?>
                                    </button>
                                    <button id="new" type="button"
                                            onclick="toggle_buttons('new'); get_notifications( all = false, true );"
                                            class="button"><?php esc_html_e( 'Unread', 'disciple_tools' )?>
                                    </button>
                                </div>
                            </div>
                            <div class="small-4 medium-5 cell" style="text-align:right;">
                                <span class="hide-for-small-only">
                                    <a onclick="mark_all_viewed()"><?php esc_html_e( 'Mark All as Read', 'disciple_tools' )?></a>  -
                                    <a href="<?php echo esc_url( home_url( '/' ) ); ?>settings/#notifications">
                                        <?php esc_html_e( 'Settings', 'disciple_tools' )?>
                                    </a>
                                </span>
                                <span class="show-for-small-only">
                                    <a onclick="mark_all_viewed()"><?php esc_html_e( 'Mark All', 'disciple_tools' )?></a>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="notification-list" class="grid-x">
                </div>

                <div class="grid-x">
                    <div class="cell">
                        <div class="grid-x grid-margin-x grid-margin-y">
                            <div class="small-12 medium-6 medium-offset-3 cell center">
                                <a id="next-all" onclick="get_notifications( true, false )">
                                    <?php esc_html_e( 'load more notifications', 'disciple_tools' )?>
                                </a>
                                <a id="next-new" onclick="get_notifications( false, false )" style="display:none;">
                                    <?php esc_html_e( 'load more notifications', 'disciple_tools' ) ?>
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
