<!-- By default, this menu will use off-canvas for small
	 and a topbar for medium-up -->

<div class="top-bar" id="top-bar-menu">
  <div class="show-for-medium">

    <div class="top-bar-left">
      <ul class="menu">
        <li><a data-toggle="off-canvas"><i class="fi-list"></i></a></li>
        <?php disciple_tools_top_nav_desktop(); ?>
      </ul>
    </div>
    <div class="top-bar-right">
      <ul class="dropdown menu" data-dropdown-menu>
        <li><input style="margin: 0" type="search" placeholder="Search"></li>
        <li><button type="button" class="button">Search</button></li>
        <li><a href="#">Notifications</a></li>
        <li>
          <a href="#"><i class="fi-widget"></i></a></a>
          <ul class="menu vertical">
            <li><a href="#">Account</a></li>
            <li><a href="#">Log Off</a></li>
          </ul>
        </li>
      </ul>
    </div>
  </div>

  <div class="show-for-small-only" style="text-align: center">
    <div class="top-bar-left">
      <ul class="menu">
        <li><a data-toggle="off-canvas"><i class="fi-list"></i></a></li>
          <?php disciple_tools_top_nav_mobile(); ?>
      </ul>
    </div>
    <div class="top-bar-right">
      <ul class="menu">
        <li><a data-toggle="off-canvas"><i class="fi-alert"></i></a></li>
        <li><a data-toggle="off-canvas"><i class="fi-magnifying-glass"></i></a></li>
      </ul>
    </div>
  </div>
</div>



</div>
