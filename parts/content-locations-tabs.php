<ul class="tabs" data-tabs id="my-contact-tabs">
    <li class="tabs-title is-active"><a href="#panel1" aria-selected="true">My Locations</a></li>
    <li class="tabs-title"><a href="#panel2">Team Locations</a></li>
    <li class="tabs-title"><a href="#panel3">Project Locations</a></li>
    <li class="float-right"><a href="javascript:void(0)" onclick="jQuery('.search-tools').toggle();" class="maginifying-glass"><i class="fi-magnifying-glass large" ></i></a></li>
</ul>
<div class="tabs-content" data-tabs-content="my-contact-tabs">
    <div class="tabs-panel is-active" id="panel1">

        <div id="my-groups">
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
                    'post_type' => 'locations',
                    'nopaging' => true,
                );
                $query2 = new WP_Query( $args );
                ?>

                <?php p2p_type( 'contacts_to_locations' )->each_connected( $query2 ); // collect all the records for the connected contacts to locations ?>

                <?php if ( $query2->have_posts() ) : while ( $query2->have_posts() ) : $query2->the_post(); ?>

                    <!-- To see additional archive styles, visit the /parts directory -->
                    <li>

                        <span class="name">
                            <a href="<?php the_permalink() ?>" rel="link" title="<?php the_title_attribute(); ?>"><?php the_title_attribute(); ?> </a>
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
    </div> <!-- End tab panel -->

    <div class="tabs-panel" id="panel2">

        <div id="team-groups">
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
                    'post_type' => 'locations',
                    'nopaging' => true,
//                    'meta_query' => dt_get_team_contacts(get_current_user_id()),
                );
                $query2 = new WP_Query( $args );
                ?>
                <?php if ( $query2->have_posts() ) : while ( $query2->have_posts() ) : $query2->the_post(); ?>

                    <!-- To see additional archive styles, visit the /parts directory -->
                    <li><span class="name"><a href="<?php the_permalink() ?>" rel="link" title="<?php the_title_attribute(); ?>"><?php the_title_attribute(); ?> </a></span> <span class="float-right small grey team">(<?php dt_get_assigned_name( get_the_ID() ); ?>)</span> </li>


                <?php endwhile; ?>

                <?php else : ?>

                    <?php echo 'No records'; ?>

                <?php endif; ?>
            </ul>

            <ul class="pagination"></ul>
        </div> <!-- End my-contacts -->
    </div> <!-- End tab panel -->
    <div class="tabs-panel" id="panel3">


        <div id="location-groups">
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
                    'post_type' => 'locations',
                    'nopaging' => true,
                );
                $query2 = new WP_Query( $args );
                ?>

                <?php p2p_type( 'contacts_to_locations' )->each_connected( $query2 ); // collect all the records for the connected contacts to locations ?>

                <?php if ( $query2->have_posts() ) : while ( $query2->have_posts() ) : $query2->the_post(); ?>

                    <!-- To see additional archive styles, visit the /parts directory -->
                    <li>

                        <span class="name">
                            <a href="<?php the_permalink() ?>" rel="link" title="<?php the_title_attribute(); ?>"><?php the_title_attribute(); ?> </a>
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
<script type="text/javascript">
    jQuery(document).ready(function() {
        var myContacts = new List('my-groups', {
            valueNames: ['name', 'team'],
            page: 10,
            pagination: true
        });

        var teamContacts = new List('team-groups', {
            valueNames: ['name', 'team'],
            page: 10,
            pagination: true
        });

        var locationContacts = new List('location-groups', {
            valueNames: ['name'],
            page: 10,
            pagination: true
        });
    });
</script>
