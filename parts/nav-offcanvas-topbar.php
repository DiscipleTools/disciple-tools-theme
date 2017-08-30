<!-- By default, this menu will use off-canvas for small
	 and a topbar for medium-up -->

<div data-sticky-container>
    <div class="top-bar" id="top-bar-menu" data-sticky data-options="marginTop:0;" style="width:100%" data-top-anchor="1" data-btm-anchor="content:bottom">

        <div class="top-bar-left">
            <ul class="menu">
                <li data-toggle="off-canvas"><i class="fi-list"></i></li>
                <?php disciple_tools_top_nav_desktop(); ?>
            </ul>
        </div>
        <div class="top-bar-right">
            <ul class="dropdown menu" data-dropdown-menu>
                <!--            <li><input style="margin: 0" type="search" placeholder="Search"></li>-->
                <!--            <li><button type="button" class="button">Search</button></li>-->
                <li><a href="#">Notifications</a></li>
                <li>
                    <a href="#"><i class="fi-widget"></i></a></a>
                    <ul class="menu vertical">
                        <li><a href="#">Account</a></li>
                        <li><a href="<?php echo get_admin_url(); ?>"><?php _e( "WordPress Admin" ); ?></a></li>
                        <li><a href="<?php echo wp_logout_url()?>">Log Off</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</div>
