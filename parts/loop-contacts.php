<?php
/* The classes post_title, permalink and assigned_name are used by our code in
List.js, they are not stylistic classes. */
?>

<li><a href="<?php the_permalink() ?>" rel="bookmark"
       class="post_title permalink"><?php the_title(); ?></a><span
      class="float-right grey assigned_name"><?php dt_get_assigned_name( get_the_ID() ) ?></span></li>
