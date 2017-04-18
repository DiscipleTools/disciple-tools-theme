<?php get_header(); ?>

<div id="content">

    <div id="inner-content" class="row">

            <main id="main" class="large-8 medium-8 columns " role="main">

                <div class="show-for-small-only">
                    <section class="block">
                        <?php include ('searchform.php'); ?>
                    </section>
                </div>


                <div class="callout alert" >
                    <i class="fi-plus"> New </i>
                    <a href="#">Mohammed Kali</a>
                    <span class="float-right">
                        <button type="submit" name="Accept" value="Accept" class="button small ">Accept</button>
                        <button type="submit" name="Decline" value="Decline" class="button small ">Decline</button>
                    </span>


                </div>

                <div class="callout alert" >
                    <i class="fi-plus"> New </i>
                    <a href="#">Mohammed Kali</a>
                    <span class="float-right">
                        <button type="submit" name="Accept" value="Accept" class="button small">Accept</button>
                        <button type="submit" name="Decline" value="Decline" class="button small ">Decline</button>
                    </span>
                </div>

                <div class="callout warning" >
                    <i class="fi-alert"> Update Needed </i>
                    <a href="#">Mohammed Kali</a>
                    <span class="float-right">
                        <button type="submit" name="Update" value="Update" class="button small ">Update</button>
                    </span>

                </div>

                <div class="callout warning" >
                    <i class="fi-alert"> Update Needed </i>
                    <a href="#">Mohammed Kali</a>
                    <span class="float-right">
                        <button type="submit" name="Update" value="Update" class="button small ">Update</button>
                    </span>
                </div>

                <div class="row column padding-bottom">

                    <ul class="tabs" data-tabs id="my-contact-tabs">
                        <li class="tabs-title is-active"><a href="#panel1" aria-selected="true">My Contacts</a></li>
                        <li class="tabs-title"><a href="#panel2">Team Contacts</a></li>
                        <li class="tabs-title"><a href="#panel3">Contacts By Location</a></li>
                    </ul>
                    <div class="tabs-content" data-tabs-content="my-contact-tabs">
                        <div class="tabs-panel is-active" id="panel1">

                            <div id="my-contacts">
                                <div class="row columns"><span class="float-right "><a href="javascript:void(0)" onclick="jQuery('.search-tools').toggle();" class="small grey">search tools</a></span></div>
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
                                <div class="row columns"><span class="float-right "><a href="javascript:void(0)" onclick="jQuery('.search-tools').toggle();" class="small grey">search tools</a></span></div>
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
                                <div class="row columns"><span class="float-right "><a href="javascript:void(0)" onclick="jQuery('.search-tools').toggle();" class="small grey">search tools</a></span></div>
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

                </div>

                <div class="row">

                    <div class="medium-6 columns">
                        <section class="block">
                            <?php dt_chart_dounut(); ?>
                            <div id="chart_dounut_div" style="width: 100%; " ></div>
                        </section>
                    </div>

                    <div class="medium-6 columns">
                        <section class="block">
                            <h4>Project News</h4>
                        </section>
                    </div>

                </div>

            </main> <!-- end #main -->

            <aside class="large-4 medium-4 columns ">



                <section class="block">
                    <!-- Project Stats -->
                    <h4>Quick Update</h4>
                    <form id="post-comment-form">
                        <div>
                            <label for="post-submission-title">
                                <?php _e( 'Select Contact', 'disciple_tools' ); ?>
                            </label>
                            <select name="post-comment-id" id="post-comment-id" required aria-required="true">
                                    <option value="65">Abe New</option>
                            </select>
                        </div>

                        <div>
                            <label for="post-submission-content">
                                <?php _e( 'Content', 'disciple_tools' ); ?>
                            </label>
                            <textarea rows="3" cols="20" name="post-comment-content" id="post-comment-content"></textarea>
                        </div>
                        <input type="submit" value="<?php esc_attr_e( 'Submit', 'disciple_tools'); ?>" class="button small">
                    </form>
                </section>




                <section class="block">
                <!-- Project Stats -->
                    <h4>Critical Path</h4>
                    <?php  if (class_exists('Disciple_Tools')) {
                        require_once ( DISCIPLE_TOOLS_DIR. '/includes/admin/reports-funnel.php');
                        require_once( DISCIPLE_TOOLS_DIR. '/includes/factories/class-page-factory.php'); // Factory class for page building
                        $reports = Disciple_Tools_Funnel_Reports::instance();
                        echo $reports->critical_path_stats() ;
                    } ?>
                </section>


            </aside> <!-- end #aside -->

    </div> <!-- end #inner-content -->

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

</div> <!-- end #content -->

<?php get_footer(); ?>
