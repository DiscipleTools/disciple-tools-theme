<?php get_header(); ?>

    <div id="content">

        <div id="inner-content" class="row">

            <!-- Breadcrumb Navigation-->
            <nav aria-label="You are here:" role="navigation">
                <ul class="breadcrumbs">
                    <li><a href="/">Dashboard</a></li>
                    <li>
                        <span class="show-for-sr">Current: </span> Contacts
                    </li>
                </ul>
            </nav>

            <main id="main" class="large-8 medium-8 columns frame" role="main">

                <ul class="tabs" data-tabs id="my-contact-tabs">
                    <li class="tabs-title is-active"><a href="#panel1" aria-selected="true">My Contacts</a></li>
                    <li class="tabs-title"><a href="#panel2">Team Contacts</a></li>
                    <li class="tabs-title"><a href="#panel3">Contacts By Location</a></li>
                </ul>
                <div class="tabs-content" data-tabs-content="my-contact-tabs">
                    <div class="tabs-panel is-active" id="panel1">

                        <div id="my-contacts">
                            <div class="row">
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
                                    'meta_key' => 'assigned_to',
                                    'meta_value' => 'user-'. get_current_user_id(),
                                );
                                $query = new WP_Query( $args );
                                ?>
                                <?php if ( $query->have_posts() ) : while ( $query->have_posts() ) : $query->the_post(); ?>

                                    <!-- To see additional archive styles, visit the /parts directory -->
                                    <?php get_template_part( 'parts/loop', 'contacts' ); ?>


                                <?php endwhile; ?>

                                <?php else : ?>

                                    <?php echo 'No records'; ?>

                                <?php endif; ?>
                            </ul>

                            <ul class="pagination"></ul>
                        </div> <!-- End my-contacts -->
                    </div> <!-- End tab panel -->

                    <div class="tabs-panel" id="panel2">

                        <div id="team-contacts">
                            <div class="row">
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
                                        'meta_query' => dt_get_team_contacts(get_current_user_id()),
                                    );
                                    $query2 = new WP_Query( $args );
                                    ?>
                                    <?php if ( $query2->have_posts() ) : while ( $query2->have_posts() ) : $query2->the_post(); ?>

                                        <!-- To see additional archive styles, visit the /parts directory -->
                                        <li><span class="name"><a href="<?php the_permalink() ?>" rel="link" title="<?php the_title_attribute(); ?>"><?php the_title(); ?> </a></span> <span class="float-right small grey team">(<?php dt_get_assigned_name(get_the_ID() ); ?>)</span> </li>


                                    <?php endwhile; ?>

                                    <?php else : ?>

                                        <?php echo 'No records'; ?>

                                    <?php endif; ?>
                            </ul>

                            <ul class="pagination"></ul>
                        </div> <!-- End my-contacts -->
                    </div> <!-- End tab panel -->
                    <div class="tabs-panel" id="panel3">



                        <div id="location-contacts">
                            <div class="row">
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
                                    'post_type' => 'locations',
                                    'nopaging' => true,
                                );
                                $query2 = new WP_Query( $args );
                                ?>

                                <?php p2p_type( 'contacts_to_locations' )->each_connected( $query2 ); // collect all the records for the connected contacts to locations ?>

                                <?php if ( $query2->have_posts() ) : while ( $query2->have_posts() ) : $query2->the_post(); ?>

                                    <!-- To see additional archive styles, visit the /parts directory -->
                                    <li><span class="name">
                                            <a href="<?php the_permalink() ?>" rel="link" title="<?php the_title_attribute(); ?>"><?php the_title(); ?> </a>
                                        </span>
                                        <span class="float-right small grey team">
                                            (active:
                                            <?php
                                                // Display connected pages
                                                $i = 0;
                                                foreach ( $post->connected as $post ) : setup_postdata( $post );
                                                    $i++;
                                                endforeach;
                                                echo $i;
                                                wp_reset_postdata(); // set $post back to original post
                                            ?>
                                            )
                                        </span>
                                    </li>


                                <?php endwhile; ?>

                                <?php else : ?>

                                    <?php echo 'No records'; ?>

                                <?php endif; ?>
                            </ul>

                            <ul class="pagination"></ul>
                        </div> <!-- End my-contacts -->


                    </div>
                </div> <!-- End tab panel -->

            </main> <!-- end #main -->

            <aside class="large-4 medium-4 columns ">

                <section class="block">



                </section>

            </aside> <!-- end #aside -->

        </div> <!-- end #inner-content -->

    </div> <!-- end #content -->

    <script type="text/javascript">
        jQuery(document).ready(function() {
            var myContacts = new List('my-contacts', {
                valueNames: ['name', 'team'],
                page: 5,
                pagination: true
            });

            var teamContacts = new List('team-contacts', {
                valueNames: ['name', 'team'],
                page: 5,
                pagination: true
            });

            var locationContacts = new List('location-contacts', {
                valueNames: ['name'],
                page: 5,
                pagination: true
            });
        });
    </script>
    <style>
        .pagination li {
            display:inline-block;
            padding:5px;
        }

    </style>
<?php get_footer(); ?>