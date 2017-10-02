<?php
/*
Template Name: Notifications
*/

// query to get new notifications
$dt_notifications = Disciple_Tools_Notifications::get_notifications();
$dt_user = wp_get_current_user();
?>

<?php get_header(); ?>

<?php
dt_print_breadcrumbs(
    null,
    __( "Notifications" ),
    false
);
?>

    <div id="content">

        <div id="inner-content" class="grid-x grid-margin-x">

            <div class="large-2 medium-12 small-12 cell "></div>

            <div class="large-8 medium-12 small-12 cell ">

                <div class="bordered-box">

                    <div id="notification-list" class="grid-x">
                        <div class="cell">
                            <div class="grid-x grid-margin-x " style="border-bottom: 1px solid #ccc;">
                                <div class="small-4 medium-5 cell"><span class="badge alert notification-count"
                                                                         style="display:none;">&nbsp;</span>
                                    <strong>New</strong></div>
                                <div class="small-4 medium-2 cell">
                                    <div class="expanded small button-group" style="text-align:center;">
                                        <a onclick="get_notifications()" class="button">All</a>
                                        <a onclick="get_notifications()" class="button hollow">Unread</a>
                                    </div>
                                </div>
                                <div class="small-4 medium-5 cell" style="text-align:right;">
                                    <span class="hide-for-small-only">
                                        <a onclick="mark_all_viewed()">Mark All as Read</a>  -
                                        <a href="<?php echo esc_url( home_url( '/' ) ); ?>settings/">Settings</a>
                                    </span>
                                    <span class="show-for-small-only">
                                        <a onclick="mark_all_viewed()">Mark All</a>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <?php
                        // display new notifications
                        if( $dt_notifications[ 'status' ] == true ) {
                            foreach( $dt_notifications[ 'result' ] as $notification ) {
                                ?>
                                <div class="cell">
                                    <div class="grid-x grid-margin-x grid-padding-y bottom-border">
                                        <div class="cell medium-1 hide-for-small-only">
                                            <img src="http://via.placeholder.com/50x50?text=icon" width="50px"
                                                 height="50px"/>
                                        </div>
                                        <div class="auto cell">
                                            <?php echo wp_kses( $notification[ 'notification_note' ], wp_kses_allowed_html( 'post' ) ); ?>
                                            <br>
                                            <span
                                                class="grey"><?php echo esc_html( $notification[ 'pretty_time' ] ); ?></span>
                                        </div>
                                        <div class="small-2 medium-1 cell padding-5">
                                            <?php if( $notification[ 'is_new' ] ) : ?>
                                                <a id="mark-viewed-<?php echo esc_attr( $notification[ 'id' ] ); ?>"
                                                   class="mark-viewed button small"
                                                   style="border-radius:100px; margin: .7em 0 0;"
                                                   onclick="mark_viewed(<?php echo esc_attr( $notification[ 'id' ] ); ?>);">
                                                    <i class="fi-check"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <?php
                            }
                        }

                        ?>
                    </div>

                </div>


            </div>

            <div class="large-2 medium-12 small-12 cell "></div>

        </div> <!-- end #inner-content -->

    </div> <!-- end #content -->

<?php

get_footer();
