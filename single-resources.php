<?php declare(strict_types=1); ?>

<?php get_header(); ?>

<?php dt_print_breadcrumbs(
    [
        [ home_url( '/' ), __( "Dashboard" ) ],
        [ home_url( '/' ) . 'resources/', __( "Resources" ) ],
    ],
    get_the_title()
); ?>

<div id="content">
    
    <div id="inner-content" class="grid-x grid-margin-x">
        
        <main id="main" class="large-8 medium-8 cell" role="main" class="hide-for-small-only">
            
            <section class="bordered-box">
                
                <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
                    
                    <?php get_template_part( 'parts/loop', 'single' ); ?>
                
                <?php endwhile; else : ?>
                    
                    <?php get_template_part( 'parts/content', 'missing' ); ?>
                
                <?php endif; ?>
            
            </section>
        
        </main> <!-- end #main -->
        
        <aside class="large-4 medium-4 cell ">
            
            
            <section class="bordered-box">
                
                <?php include 'searchform.php'; ?>
            
            </section>
            
            <section class="bordered-box">
                
                <h4>Recent Posts</h4>
                
                <?php $args = array(
                    'numberposts' => 10,
                    'offset' => 0,
                    'category' => 0,
                    'orderby' => 'post_date',
                    'order' => 'DESC',
                    'include' => '',
                    'exclude' => '',
                    'meta_key' => '',
                    'meta_value' =>'',
                    'post_type' => 'resources',
                    'post_status' => 'draft, publish, future, pending, private',
                    'suppress_filters' => true
                );
                
                $recent_posts = wp_get_recent_posts( $args, ARRAY_A );
                
                echo '<ul>';
foreach ($recent_posts as $recent_post) {
    echo '<li><a href="'. $recent_post['guid'] .'">' . esc_attr( $recent_post['post_title'] ) . '</a></li>';
}
                echo '</ul>';
                
                //                    print_r($recent_posts);?>
            
            </section>
            
            <section class="bordered-box">
                
                <p>Archives</p>
                
                <?php
                $args = array(
                    'type'            => 'monthly',
                    'limit'           => '',
                    'format'          => 'html',
                    'before'          => '',
                    'after'           => '',
                    'show_post_count' => false,
                    'echo'            => 1,
                    'order'           => 'DESC',
                    'post_type'     => 'resources'
                );
                wp_get_archives( $args );
                
                ?>
            
            </section>
        
        </aside> <!-- end #aside -->
    
    </div> <!-- end #inner-content -->

</div> <!-- end #content -->

<?php get_footer(); ?>
