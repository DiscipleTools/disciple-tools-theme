<!-- Begin Assigned Contacts -->
<?php if ( ! empty( $_POST['response'] )) { dt_update_overall_status( $_POST ); } ?>
<?php

/* Loop for the new assigned contacts */
$assigned_to = 'user-' . get_current_user_id();
$args = array(
    'post_type' => 'contacts',
    'posts_per_page'         => '5',
    'meta_query' =>  array(
        'relation' => 'AND', // Optional, defaults to "AND"
        array(
            'key'     => 'assigned_to',
            'value'   => $assigned_to,
            'compare' => '='
        ),
        array(
            'key'     => 'overall_status',
            'value'   => '1',
            'compare' => '!='
        )
    )
);
$requires_update = new WP_Query( $args );

?>
<?php if ( $requires_update->have_posts() ) : while ( $requires_update->have_posts() ) : $requires_update->the_post(); ?>

    <form method="post" action="">
        <div class="callout alert" >
            <i class="fi-plus"> New </i>
            <a href="<?php the_permalink(); ?>"><?php the_title_attribute(); ?></a>
            <span class="float-right">
                <input type="hidden" name="post_id" value="<?php echo get_the_ID(); ?>" />
                <button type="submit" name="response" value="1" class="button small ">Accept</button>
                <button type="submit" name="response" value="0" class="button small ">Decline</button>
            </span>
        </div>
    </form>

<?php endwhile; ?>

<!--    --><?php //$pagination = get_the_posts_pagination( array(
//        'mid_size' => 2,
//        'prev_text' => __( 'Newer', 'textdomain' ),
//        'next_text' => __( 'Older', 'textdomain' ),
//    ) );
//    echo $pagination;?>

<?php else : ?>

<?php endif; ?>
