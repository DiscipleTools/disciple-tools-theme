<?php
declare(strict_types=1);

global $query_string;

$query_args = explode( "&", $query_string );
$search_query = array();

if( strlen( $query_string ) > 0 ) {
    foreach($query_args as $key => $string) {
        $query_split = explode( "=", $string );
        $search_query[$query_split[0]] = urldecode( $query_split[1] );
    } // foreach
} //if

$search_args1 = array(
    'post_type' => array( 'contacts' ),
    'nopaging' => true,
    'meta_query' => dt_get_user_associations(),
);

$search_args2 = array(
    'post_type' => array( 'groups' ),
    'nopaging' => true,
);

$args1 = array_merge( $search_args1, $search_query );

$args2 = array_merge( $search_args2, $search_query );

$search1 = new WP_Query( $args1 );
$search2 = new WP_Query( $args2 );

?>

<?php get_header(); ?>

<?php dt_print_breadcrumbs( null, __( "Search" ) ); ?>

    <div id="content">

        <div id="inner-content" class="row">

            <main id="main" class="large-8 medium-8 columns first" role="main">

                <section class="bordered-box">

                    <header>
                        <p>Results for: <?php echo esc_attr( get_search_query() ); ?></p><hr>
                    </header>


                    <?php if ( $search1->have_posts() ) : ?>

                        <h3>Contacts</h3>

                        <?php while ( $search1->have_posts() ) : $search1->the_post(); ?>

                            <li><a href="<?php the_permalink() ?>" rel="bookmark" title="<?php the_title_attribute(); ?>"><?php the_title_attribute(); ?></a></li>

                        <?php endwhile; ?>

                    <?php endif; ?>



                    <?php if ( $search2->have_posts() ) : ?>

                        <h3>Groups</h3>

                        <?php while ( $search2->have_posts() ) : $search2->the_post(); ?>

                            <li><a href="<?php the_permalink() ?>" rel="bookmark" title="<?php the_title_attribute(); ?>"><?php the_title_attribute(); ?></a></li>

                        <?php endwhile; ?>

                    <?php endif; ?>

                    <?php if ( ! $search1->have_posts() && ! $search2->have_posts() ) {  get_template_part( 'parts/content', 'missing' ); } ?>

                </section class="bordered-box">

            </main> <!-- end #main -->

            <aside class="large-4 medium-4 columns">



            </aside>

        </div> <!-- end #inner-content -->

    </div> <!-- end #content -->

<?php get_footer(); ?>
