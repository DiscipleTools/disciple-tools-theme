<?php get_header(); ?>

    <div id="content">

        <div id="inner-content" class="row">


                <!-- Breadcrumb Navigation-->
                <nav aria-label="You are here:" role="navigation">
                    <ul class="breadcrumbs">
                        <li><a href="/">Dashboard</a></li>
                        <li>
                            <span class="show-for-sr">Current: </span> Reports
                        </li>
                    </ul>
                </nav>


            <main id="main" class="large-12 medium-12 columns" role="main">

    <div class="padding-bottom">
                    <ul class="tabs" data-tab id="reports-tabs">
                        <li class="tab-title active"><a href="#panel11">Tab 1</a></li>
                        <li class="tab-title"><a href="#panel21">Tab 2</a></li>
                        <li class="tab-title"><a href="#panel31">Tab 3</a></li>
                        <li class="tab-title"><a href="#panel41">Tab 4</a></li>
                    </ul>
                    <div class="tabs-content" data-tabs-content="reports-tabs">
                        <div class="content active" id="panel11">
                            <p>This is the first panel of the basic tab example. You can place all sorts of content here including a grid.</p>
                        </div>
                        <div class="content" id="panel21">
                            <p>This is the second panel of the basic tab example. This is the second panel of the basic tab example.</p>
                        </div>
                        <div class="content" id="panel31">
                            <p>This is the third panel of the basic tab example. This is the third panel of the basic tab example.</p>
                        </div>
                        <div class="content" id="panel41">
                            <p>This is the fourth panel of the basic tab example. This is the fourth panel of the basic tab example.</p>
                        </div>
                    </div>
        </div>
            <section class="block">
                <?php dt_chart_bargraph (); ?>
            </section>

            </main>

        </div> <!-- end #inner-content -->

    </div> <!-- end #content -->

<?php get_footer(); ?>