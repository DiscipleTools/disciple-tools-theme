<?php
/*
Template Name: Notifications
*/
?>

<?php get_header(); ?>

<?php
dt_print_breadcrumbs(
    null,
    __( "Notifications" ),
    false
); ?>
    
    <div id="content">
        
        <div id="inner-content" class="grid-x grid-margin-x">
    
            <div class="large-2 medium-12 small-12 cell "></div>
    
            <div class="large-8 medium-12 small-12 cell ">
        
            
                    <div class="bordered-box">
                        
                        
                        <div class="grid-x">
                            <div class="cell">
                                <div class="grid-x grid-margin-x " style="border-bottom: 1px solid #ccc;">
                                    <div class="small-4 medium-5 cell"><span class="badge alert notification-count" style="display:none;">&nbsp;</span> <strong>New</strong></div>
                                    <div class="small-4 medium-2 cell" >
                                        <div class="expanded small button-group" style="text-align:center;">
                                            <a href="#" class="button">All</a>
                                            <a href="#" class="button hollow">Unread</a>
                                        </div>
                                    </div>
                                    <div class="small-4 medium-5 cell" style="text-align:right;">
                                        <span class="hide-for-small-only">
                                            <a onclick="mark_all_viewed()" >Mark All as Read</a>  -
                                            <a href="<?php echo home_url( '/' ); ?>settings/">Settings</a>
                                        </span>
                                        <span class="show-for-small-only">
                                            <a onclick="mark_all_viewed()" >Mark All</a>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <?php
                            
                            // query to get new notifications
                            $notifications = Disciple_Tools_Notifications::get_notifications( ['user_id' => get_current_user_id() ] );
                            
                            // display new notifications
                            if ($notifications['status'] == true ) {
                                foreach($notifications['result'] as $notification) {
                                    ?>
                                    <div class="cell hover">
                                        <div class="grid-x grid-margin-x grid-padding-y bottom-border">
                                            <div class="small-1 cell"><img src="http://via.placeholder.com/50x50" width="50px" height="50px" /></div>
                                            <div class="auto cell">
                                                <?php echo $notification['notification_note']; ?>
                                                <br>
                                                <span class="grey"><?php echo $notification['pretty_time']; ?></span>
                                            </div>
                                            <div class="small-1 cell">
                                                <?php if( $notification['is_new'] ) : ?>
                                                    <a id="mark-viewed-<?php echo $notification['id']; ?>" class="mark-viewed" onclick="mark_viewed(<?php echo $notification['id']; ?>);">
                                                        <span class="badge " style="float:right;">&nbsp;</span>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <?php
                                }
                            }
                            
                            ?>
                            <?php
                            // TODO Add infinite scroll or a click loader for the next 50 records.
                            // TODO Add notification bubble to top bar
                            
                            ?>
                            <div class="cell">
                                <div class="grid-x grid-margin-x grid-padding-y">
                                    <div class="cell" style="text-align:center;">
                                        
                                        <a id="load_older_notifications" onclick="load_next_notifications(50);">load older notifications</a>
                                        
                                    </div>
                                </div>
                            </div>
                        
                        </div>
                    
                    </div>
        
    
            </div>
    
            <div class="large-2 medium-12 small-12 cell "></div>
        
        </div> <!-- end #inner-content -->
    
    </div> <!-- end #content -->

<?php get_footer(); ?>
