<section class="bordered-box">
    
    <h3><a href="<?php the_permalink() ?>" rel="bookmark" title="<?php the_title_attribute(); ?>"><?php the_title_attribute(); ?></a></h3>
    
    <span class="small grey float-right"><?php the_time( 'F j, Y' ) ?></span>
    <p><?php the_excerpt(); ?></p>

</section>
