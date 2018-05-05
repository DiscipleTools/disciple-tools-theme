<?php
declare(strict_types=1);

?>

<?php get_header(); ?>

<div id="errors"> </div>
<div data-sticky-container class="hide-for-small-only" style="z-index: 9">
    <nav aria-label="<?php esc_attr_e( "You are here:" ); ?>" role="navigation"
         data-sticky data-options="marginTop:3;" style="width:100%" data-top-anchor="1"
         class="second-bar">
        <div class="container-width center">
            <a class="button dt-green" style="margin-bottom:0" href="<?php echo esc_url( home_url( '/' ) ) . "groups/new" ?>">
                <img style="display: inline-block;" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/circle-add.svg' ) ?>"/>
                <span class="hide-for-small-only"><?php esc_html_e( "Create new group", "disciple_tools" ); ?></span>
            </a>
            <a class="button" style="margin-bottom:0" data-open="filter-modal">
                <img style="display: inline-block;" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/filter.svg' ) ?>"/>
                <span class="hide-for-small-only"><?php esc_html_e( "Filter groups", 'disciple_tools' ) ?></span>
            </a>
            <input style="max-width:200px;display: inline-block;margin-bottom:0" type="search" id="search-query" placeholder="search">
            <button class="button" style="margin-bottom:0" id="search">
                <img style="display: inline-block;" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/search-white.svg' ) ?>"/>
                <?php esc_html_e( "Search", 'disciple_tools' ) ?>
            </button>
        </div>
    </nav>
</div>
<nav  role="navigation" style="width:100%;"
      class="second-bar show-for-small-only center">
    <a class="button dt-green" style="margin-bottom:0" href="<?php echo esc_url( home_url( '/' ) ) . "groups/new" ?>">
        <img style="display: inline-block;" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/circle-add.svg' ) ?>"/>
        <span class="hide-for-small-only"><?php esc_html_e( "Create new group", "disciple_tools" ); ?></span>
    </a>
    <a class="button" style="margin-bottom:0" data-open="filter-modal">
        <img style="display: inline-block;" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/filter.svg' ) ?>"/>
        <span class="hide-for-small-only"><?php esc_html_e( "Filter groups", 'disciple_tools' ) ?></span>
    </a>
    <a class="button" style="margin-bottom:0" id="open-search">
        <img style="display: inline-block;" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/search.svg' ) ?>"/>
        <span class="hide-for-small-only"><?php esc_html_e( "Search groups", 'disciple_tools' ) ?></span>
    </a>
    <div class="hideable-search" style="display: none; margin-top:5px">
        <input style="max-width:200px;display: inline-block;margin-bottom:0" type="search" id="search-query-mobile" placeholder="search">
        <button class="button" style="margin-bottom:0" id="search-mobile"><?php esc_html_e( "Search", 'disciple_tools' ) ?></button>
    </div>
</nav>


<div id="content">

    <div id="inner-content" class="grid-x grid-margin-x">

        <aside class="large-3 cell padding-bottom hide-for-small-only">
            <div class="bordered-box js-pane-filters">
                <?php /* Javascript my move .js-filters-modal-content to this location. */ ?>
            </div>
        </aside>

        <aside class="cell padding-bottom show-for-small-only">
            <div class="bordered-box" style="padding-top:5px;padding-bottom:5px">
                <div class="js-list-filter filter--closed">
                    <div class="filter__title js-list-filter-title" style="margin-bottom:0"><?php esc_html_e( "Filters" ); ?>
                        <div style="display: inline-block" class="loading-spinner active"></div>
                    </div>
                    <div class="js-filters-accordion"></div>
                </div>
            </div>
        </aside>

        <div class="reveal js-filters-modal" id="filters-modal">
            <div class="js-filters-modal-content">
                <h5 class="hide-for-small-only"><?php esc_html_e( "Filters", "disciple_tools" ); ?></h5>
                <div class="list-views">
                    <label class="list-view">
                        <input type="radio" name="view" value="all" class="js-list-view" autocomplete="off">
                        <?php esc_html_e( "All groups", "disciple_tools" ); ?>
                        <span class="list-view__count js-list-view-count" data-value="all_groups">.</span>
                    </label>
                    <label class="list-view">
                        <input type="radio" name="view" value="my" class="js-list-view" checked autocomplete="off">
                        <?php esc_html_e( "My groups", "disciple_tools" ); ?>
                        <span class="list-view__count js-list-view-count" data-value="my_groups">.</span>
                    </label>
                    <label class="list-view">
                        <input type="radio" name="view" value="shared_with_me" class="js-list-view" autocomplete="off">
                        <?php esc_html_e( "Groups shared with me", "disciple_tools" ); ?>
                        <span class="list-view__count js-list-view-count" data-value="groups_shared_with_me">.</span>
                    </label>
                </div>
                <h5><?php esc_html_e( 'Custom Filters', "disciple_tools" ); ?></h5>
                <div style="margin-bottom: 5px">
                    <a data-open="filter-modal"><img style="display: inline-block; margin-right:12px" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/circle-add-blue.svg' ) ?>"/><?php esc_html_e( "Add new filter", 'disciple_tools' ) ?></a>
                </div>
                <div class="custom-filters">
                </div>
                <div id="saved-filters">
                    <?php
                    $dt_saved_filters = Disciple_Tools_Users::get_user_filters();
                    if ( isset( $dt_saved_filters["groups"] )){
                        foreach ( $dt_saved_filters["groups"] as $filter ) { ?>
                            <div>
                                <label style="cursor:pointer" class="js-filter-checkbox-label">
                                    <input name="view" class="js-list-view" type="radio" autocomplete="off" data-id="<?php echo esc_html( $filter["ID"] ) ?>" value="saved-filters">
                                    <?php echo esc_html( $filter["name"] ) ?>
                                </label>
                            </div>
                        <?php }
                    }
                    ?>
                </div>
            </div>
        </div>

        <main id="main" class="large-9 cell padding-bottom" role="main">

            <?php get_template_part( 'dt-assets/parts/content', 'groups' ); ?>

        </main> <!-- end #main -->

    </div> <!-- end #inner-content -->

</div> <!-- end #content -->


<div class="reveal" id="filter-modal" data-reveal>
    <div class="grid-container" >
        <div class="grid-x">
            <div class="cell small-4">
                <h3><?php esc_html_e( 'New Filter', 'disciple_tools' )?></h3>
            </div>
            <div class="cell small-8">
                <div id="selected-filters"></div>
            </div>
        </div>
        <div class="grid-x">
            <div class="cell small-4 filter-modal-left">
                <ul class="vertical tabs" data-tabs id="example-tabs">
                    <li class="tabs-title is-active"><a href="#assigned_to" aria-selected="true"><?php esc_html_e( "Assigned To", 'disciple_tools' ) ?></a></li>
                    <li class="tabs-title"><a href="#group_status"><?php esc_html_e( "Status", 'disciple_tools' ) ?></a></li>
                    <li class="tabs-title"><a href="#group_type"><?php esc_html_e( "Type", 'disciple_tools' ) ?></a></li>
                    <li class="tabs-title"><a href="#leaders"><?php esc_html_e( "Leaders", 'disciple_tools' ) ?></a></li>
                    <li class="tabs-title"><a href="#locations"><?php esc_html_e( "Locations", 'disciple_tools' ) ?></a></li>
                </ul>
            </div>

            <div class="cell small-8 tabs-content filter-modal-right" data-tabs-content="example-tabs">
                <div class="tabs-panel is-active" id="assigned_to">
                    <div class="assigned_to details">
                        <var id="assigned_to-result-container" class="result-container assigned_to-result-container"></var>
                        <div id="assigned_to_t" name="form-assigned_to" class="scrollable-typeahead typeahead-margin-when-active">
                            <div class="typeahead__container">
                                <div class="typeahead__field">
                                    <span class="typeahead__query">
                                        <input class="js-typeahead-assigned_to"
                                               name="assigned_to[query]" placeholder="<?php esc_html_e( "Search Users", 'disciple_tools' ) ?>"
                                               autocomplete="off">
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tabs-panel" id="leaders">
                    <div class="leaders details">
                        <var id="leaders-result-container" class="result-container leaders-result-container"></var>
                        <div id="leaders_t" name="form-leaders" class="scrollable-typeahead typeahead-margin-when-active">
                            <div class="typeahead__container">
                                <div class="typeahead__field">
                                <span class="typeahead__query">
                                    <input class="js-typeahead-leaders input-height"
                                           name="leaders[query]" placeholder="<?php esc_html_e( "Search Contacts", 'disciple_tools' ) ?>"
                                           autocomplete="off">
                                </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tabs-panel" id="locations">
                    <div class="locations">
                        <var id="locations-result-container" class="result-container"></var>
                        <div id="locations_t" name="form-locations" class="scrollable-typeahead typeahead-margin-when-active">
                            <div class="typeahead__container">
                                <div class="typeahead__field">
                                    <span class="typeahead__query">
                                        <input class="js-typeahead-locations"
                                               name="locations[query]" placeholder="<?php esc_html_e( "Search Locations", 'disciple_tools' ) ?>"
                                               autocomplete="off">
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tabs-panel" id="group_status">
                    <div id="group_status-options"></div>
                </div>
                <div class="tabs-panel" id="group_type">
                    <div id="group_type-options"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid-x grid-padding-x">
        <div class="cell small-4 filter-modal-left">
            <button class="button button-cancel clear" data-close aria-label="Close reveal" type="button">
                <?php esc_html_e( 'Cancel', 'disciple_tools' )?>
            </button>
        </div>
        <div class="cell small-8 filter-modal-right confirm-buttons">
            <button class="button loader confirm-filter-contacts" type="button" id="confirm-filter-contacts" data-close >
                <?php esc_html_e( 'Filter Groups', 'disciple_tools' )?>
            </button>
        </div>
    </div>
    <button class="close-button" data-close aria-label="Close modal" type="button">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<div class="reveal" id="save-filter-modal" data-reveal>
    <h2><?php esc_html_e( "Save Filter", 'disciple_tools' ) ?></h2>
    <label><?php esc_html_e( "What do you want to call this filter?", 'disciple_tools' ) ?>
        <input id="filter-name">
    </label>
    <div style="margin-top:20px">
        <button class="button button-cancel clear" data-close aria-label="Close reveal" type="button">
            <?php esc_html_e( 'Cancel', 'disciple_tools' )?>
        </button>
        <button class="button loader confirm-filter-save" type="button" id="confirm-filter-save" data-close >
            <?php esc_html_e( 'Save Filter', 'disciple_tools' )?>
        </button>
        <button class="close-button" data-close aria-label="Close modal" type="button">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
</div>

<?php get_footer(); ?>
