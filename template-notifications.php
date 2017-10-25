<?php
/*
Template Name: Notifications
*/
dt_write_log( get_the_title( 58 ) );

$dt_user = wp_get_current_user(); // query to get new notifications
?>

<?php get_header(); ?>

<?php
dt_print_breadcrumbs(
    null,
    __( "Notifications" ),
    false
);
?>

<script>

    jQuery(document).ready(function () {
        get_notifications(true, true);
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
                                <strong>New</strong></div>
                            <div class="small-4 medium-2 cell">
                                <div class="expanded small button-group" style="text-align:center;">
                                    <button id="all" type="button"
                                            onclick="toggle_buttons('all'); get_notifications( all = true, true );"
                                            class="button">All
                                    </button>
                                    <button id="new" type="button"
                                            onclick="toggle_buttons('new'); get_notifications( all = false, true );"
                                            class="button hollow">Unread
                                    </button>
                                </div>
                            </div>
                            <div class="small-4 medium-5 cell" style="text-align:right;">
                                <span class="hide-for-small-only">
                                    <a onclick="mark_all_viewed()">Mark All as Read</a>  -
                                    <a href="<?php echo esc_url( home_url( '/' ) ); ?>settings/#notifications">Settings</a>
                                </span>
                                <span class="show-for-small-only">
                                    <a onclick="mark_all_viewed()">Mark All</a>
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
                                <a id="next-all" onclick="get_notifications( true, false )">load more
                                    notifications</a>
                                <a id="next-new" onclick="get_notifications( false, false )" style="display:none;">load
                                    more notifications</a>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </div> <!-- end #inner-content -->

</div> <!-- end #content -->

<?php get_footer(); ?>
