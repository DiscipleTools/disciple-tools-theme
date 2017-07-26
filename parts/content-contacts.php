<div id="my-contacts" class="bordered-box">
    <div class="row search-tools" style="display:none;">
        <div class="medium-6 columns">
            <input type="text" class="search"  />
        </div>
        <div class="medium-6 columns">
            <button class="sort button small" data-sort="name">Sort by name</button> <button class="sort button small" data-sort="team">Sort by team</button>
        </div>

    </div>

    <ul class="list">

        <?php
        $args = array(
            'post_type' => 'contacts',
            'nopaging' => true,
            'meta_query' => array (
                'relation' => 'AND', // Optional, defaults to "AND"
                array(
                    'key'     => 'assigned_to',
                    'value'   => 'user-'. get_current_user_id(),
                    'compare' => '='
                )
            ),
        );
        $query1 = new WP_Query( $args );
        ?>
        <?php if ( $query1->have_posts() ) : while ( $query1->have_posts() ) : $query1->the_post(); ?>

            <!-- To see additional archive styles, visit the /parts directory -->
            <?php get_template_part( 'parts/loop', 'contacts' ); ?>


        <?php endwhile; ?>

        <?php else : ?>

            <?php echo 'No records'; ?>

        <?php endif; ?>
    </ul>

    <ul class="pagination"></ul>
</div> <!-- End my-contacts -->
