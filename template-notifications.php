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
                                    <div class="small-5 cell"><span class="badge alert">7</span> <strong>Notifications</strong></div>
                                    <div class="small-2 cell" >
                                        <div class="expanded small button-group" style="text-align:center;">
                                            <a href="#" class="button">All</a>
                                            <a href="#" class="button hollow">Unread</a>
                                        </div>
                                    </div>
                                    <div class="small-5 cell" style="text-align:right;"><a href="#" >Mark All as Read</a>  - <a href="">Settings</a></div>
                                </div>
                            </div>
                            
                            <?php
                                
                                // query to get new notifications
                                $notifications = [
                                    [
                                        "title" => "Name",
                                        "type" => "mention",
                                        "comment" => "This is the comment you left me @chrischasm",
                                        "notification_id" => "2"
                                    ],
                                    [
                                        "title" => "Name",
                                        "type" => "mention",
                                        "comment" => "This is the comment you left me @chrischasm",
                                        "notification_id" => "2"
                                    ],
                                    [
                                        "title" => "Name",
                                        "type" => "mention",
                                        "comment" => "This is the comment you left me @chrischasm",
                                        "notification_id" => "2"
                                    ],
                                    [
                                        "title" => "Name",
                                        "type" => "mention",
                                        "comment" => "This is the comment you left me @chrischasm",
                                        "notification_id" => "2"
                                    ]
                                ];
                            
                                // display new notifications
                                foreach($notifications as $notification) {
                                    ?>
                                    <div class="cell">
                                        <div class="grid-x grid-margin-x grid-padding-y" style="border-bottom: 1px solid #ccc;">
                                            <div class="small-1 cell"><img src="http://via.placeholder.com/50x50" width="50px" height="50px" /></div>
                                            <div class="auto cell">Joe mentioned you on Contact137 '@janedoe Lorem ipsum dolor sit amet, consectetur adipiscing elit.'</div>
                                            <div class="small-1 cell"><a href="#"><span class="badge" style="vertical-align: middle; float:right;">&nbsp;</span></a></div>
                                        </div>
                                    </div>
                                    <?php
                                }
                            
                            ?>
                            
                            
                            <div class="cell">
                                <div class="grid-x grid-margin-x grid-padding-y">
                                    <div class="cell">
                                        <ul class="pagination text-center small" role="navigation" aria-label="Pagination">
                                            <li class="pagination-previous disabled">Previous</li>
                                            <li class="current"><span class="show-for-sr">You're on page</span> 1</li>
                                            <li><a href="#" aria-label="Page 2">2</a></li>
                                            <li><a href="#" aria-label="Page 3">3</a></li>
                                            <li><a href="#" aria-label="Page 4">4</a></li>
                                            <li class="ellipsis"></li>
                                            <li><a href="#" aria-label="Page 12">12</a></li>
                                            <li><a href="#" aria-label="Page 13">13</a></li>
                                            <li class="pagination-next"><a href="#" aria-label="Next page">Next</a></li>
                                        </ul>
                                    </div>
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
