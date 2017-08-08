<!-- Begin Updates Required Section -->
<?php if ( ! empty( $_POST['comment_content'] )) { dt_update_required_update( $_POST ); } ?>
<?php
/* Loop for the requires update contacts */
$assigned_to = 'user-' . get_current_user_id();
$args = array(
    'post_type' => 'contacts',
    'nopaging'               => false,
    'posts_per_page'         => '5',
    'meta_query' =>  array(
        'relation' => 'AND', // Optional, defaults to "AND"
        array(
            'key'     => 'assigned_to',
            'value'   => $assigned_to,
            'compare' => '='
        ),
        array(
            'key'     => 'requires_update',
            'value'   => 'Yes',
            'compare' => '='
        )
    )
);
$requires_update = new WP_Query( $args );
?>
<?php if ( $requires_update->have_posts() ) : while ( $requires_update->have_posts() ) : $requires_update->the_post(); ?>

    <form action="" method="post">
        <div class="callout warning" >

            <i class="fi-alert"> Update Needed </i>

            <a href="<?php the_permalink(); ?>"><?php the_title_attribute(); ?></a>

            <span class="float-right">
                            <button type="button" class="button small update-<?php echo get_the_ID(); ?>" onclick="jQuery('.update-<?php echo get_the_ID(); ?>').toggle();">Update</button>
                        </span>

            <p style="display:none;" class="update-<?php echo get_the_ID(); ?>" >

                <input type="hidden" name="post_ID" value="<?php echo get_the_ID(); ?>" />
                <input type="text" name="comment_content"  />

            </p>

        </div>
    </form>

<?php endwhile; ?>
<?php endif; ?>
<!-- End Updates Required Section -->
