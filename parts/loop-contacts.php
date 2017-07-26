<?php
/* The classes post_title, permalink and team are used by our code in
List.js, they are not stylistic classes. */
?>

<li><a href="<?php the_permalink() ?>" rel="bookmark"
       class="post_title permalink"><?php the_title(); ?></a><span
      class="float-right grey team"><?php dt_get_assigned_name( get_the_ID() ) ?></span></li>
