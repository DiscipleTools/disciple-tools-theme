<?php
/*
Template Name: Settings
*/
?>

<?php get_header(); ?>

<?php
dt_print_breadcrumbs(
    [
        [ home_url( '/' ), __( "Dashboard" ) ],
        [ home_url( '/' ) . "settings", __( "Settings" ) ],
    ],
    get_the_title(),
    false
); ?>
    
    <div id="content">
        
        <div id="inner-content" class="grid-x grid-margin-x">
    
            <div class="large-3 medium-12 small-12 cell ">
        
                <section id="" class="medium-12 cell sticky" data-sticky data-margin-top="6.5">
            
                    <div class="bordered-box hide-for-small-only">
                    
                        <ul class="menu vertical expanded" data-smooth-scroll data-offset="100">
                            <li><a href="#profile">Profile</a></li>
                            <li><a href="#availability">Availability</a></li>
                            <li><a href="#notifications">Notifications</a></li>
                        </ul>
                    
                    </div>
        
                </section>
                
    
            </div>
    
            <div class="large-9 medium-12 small-12 cell ">
        
            
                    <div class="bordered-box" id="profile" data-magellan-target="profile">
                        <button class="float-right" onclick=""><i class="fi-pencil"></i> Edit</button>
                        <span class="section-header">Profile</span>
    
                        <div class="grid-x grid-margin-x">
                            
                            <div class="medium-6 cell">
                                test
                            </div>
                            <div class="medium-6 cell">
                                test
                            </div>
                            
        
                                        <?php
                    
                                        // query to get new notifications
                                        $notifications = Disciple_Tools_Notifications::get_notifications( ['user_id' => get_current_user_id() ] );
                    
                                        // display new notifications
                                        if ($notifications['status'] == true ) {
                                            foreach($notifications['result'] as $notification) {
                                                ?>
                                                <div class="cell hover">
                                                    <div class="grid-x grid-margin-x grid-padding-y" style="border-top: 1px solid #ccc;">
                                                        <div class="small-1 cell"><img src="http://via.placeholder.com/50x50" width="50px" height="50px" /></div>
                                                        <div class="auto cell">
                                                            <?php echo $notification['notification_note']; ?>
                                                            <br>
                                                            <span class="grey"><?php echo $notification['pretty_time']; ?></span>
                                                        </div>
                                                        <div class="small-1 cell">
                                                            <?php if( $notification['is_new'] ) : ?>
                                                                <a id="mark-viewed-<?php echo $notification['id']; ?>" class="mark-viewed" onclick="mark_viewed(<?php echo $notification['id']; ?>);">
                                                                    <span class="badge " style="vertical-align: middle; float:right;">&nbsp;</span>
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
                    
                    <div class="bordered-box" id="availability" data-magellan-target="availability">
                        <button class="float-right" onclick=""><i class="fi-pencil"></i> Edit</button>
                        <span class="section-header">Availability</span>
                       
                    </div>
                    
                    <div class="bordered-box" id="notifications" data-magellan-target="notifications">
                        <button class="float-right" onclick=""><i class="fi-pencil"></i> Edit</button>
                        <span class="section-header">Notifications</span>
                        
                    </div>
                    
    
            </div>
    
        </div> <!-- end #inner-content -->
    
    </div> <!-- end #content -->

<?php get_footer(); ?>
