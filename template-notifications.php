<?php
/*
Template Name: Notifications
*/
?>

<?php get_header(); ?>

<?php
dt_print_breadcrumbs(
    [
        [ home_url( '/' ), __( "Dashboard" ) ],
        [ home_url( '/' ) . "notifications", __( "Notifications" ) ],
    ],
    get_the_title(),
    false
); ?>
    
    <div id="content">
        
        <div id="inner-content" class="grid-x grid-margin-x">
    
            <div class="large-2 medium-12 small-12 cell ">
        
            
    
            </div>
    
            <div class="large-8 medium-12 small-12 cell ">
        
                <section id="" class="medium-12 cell">
            
                    <div class="bordered-box">
                        
                        
                        <div class="grid-x">
                            <div class="cell">
                                <div class="grid-x grid-margin-x " style="border-bottom: 1px solid #ccc;">
                                    <div class="small-4 cell">Notifications</div>
                                    <div class="small-4 cell" style="text-align:center;"><button class="button">All</button><button class="button hollow">Unread</button></div>
                                    <div class="small-4 cell" style="text-align:right;">Mark All as Read - Settings</div>
                                </div>
                            </div>
                            <div class="cell">
                                <div class="grid-x grid-margin-x grid-padding-y" style="border-bottom: 1px solid #ccc;">
                                    <div class="small-1 cell"><img src="http://via.placeholder.com/50x50" width="50px" height="50px" /></div>
                                    <div class="auto cell">Joe mentioned you on Contact137 '@janedoe Lorem ipsum dolor sit amet, consectetur adipiscing elit.'</div>
                                    <div class="small-1 cell"><span class="badge" style="vertical-align: middle; float:right;">&nbsp;</span></div>
                                </div>
                            </div>
                            <div class="cell">
                                <div class="grid-x grid-margin-x grid-padding-y" style="border-bottom: 1px solid #ccc;">
                                    <div class="small-1 cell"><img src="http://via.placeholder.com/50x50" width="50px" height="50px" /></div>
                                    <div class="auto cell">Joe mentioned you on Contact137 '@janedoe Lorem ipsum dolor sit amet, consectetur adipiscing elit.'</div>
                                    <div class="small-1 cell"><span class="badge" style="vertical-align: middle; float:right;">&nbsp;</span></div>
                                </div>
                            </div>
                            <div class="cell">
                                <div class="grid-x grid-margin-x grid-padding-y" style="border-bottom: 1px solid #ccc;">
                                    <div class="small-1 cell"><img src="http://via.placeholder.com/50x50" width="50px" height="50px" /></div>
                                    <div class="auto cell">Joe mentioned you on Contact137 '@janedoe Lorem ipsum dolor sit amet, consectetur adipiscing elit.'</div>
                                    <div class="small-1 cell"><span class="badge" style="vertical-align: middle; float:right;">&nbsp;</span></div>
                                </div>
                            </div>
                            <div class="cell">
                                <div class="grid-x grid-margin-x grid-padding-y">
                                    <div class="auto cell"></div>
                                    <div class="small-6 cell" style="text-align: center;">
                                        << 1 2 3 4 5 6 >>
                                    </div>
                                    <div class="auto cell"></div>
                                </div>
                            </div>
                            
                        </div>
                    
                    </div>
        
                </section>
    
            </div>
    
            <div class="large-2 medium-12 small-12 cell ">
        
            
    
            </div>
        
        </div> <!-- end #inner-content -->
    
    </div> <!-- end #content -->

<?php get_footer(); ?>
