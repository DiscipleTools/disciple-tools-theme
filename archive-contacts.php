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
            <a class="button dt-green" style="margin-bottom:0" href="<?php echo esc_url( home_url( '/' ) ) . "contacts/new" ?>">
                <img style="display: inline-block;" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/circle-add.svg' ) ?>"/>
                <span class="hide-for-small-only"><?php esc_html_e( "Create new contact", "disciple_tools" ); ?></span>
            </a>
            <a class="button" style="margin-bottom:0" data-open="filter-modal">
                <img style="display: inline-block;" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/filter.svg' ) ?>"/>
                <span class="hide-for-small-only"><?php esc_html_e( "Filter contacts", 'disciple_tools' ) ?></span>
            </a>
            <input style="max-width:200px;display: inline-block;margin-bottom:0" type="search" id="search-query" placeholder="search">
            <button class="button" style="margin-bottom:0" id="search-contacts"><?php esc_html_e( "Search", 'disciple_tools' ) ?></button>
        </div>
    </nav>
</div>
<nav  role="navigation" style="width:100%;"
      class="second-bar show-for-small-only center">
    <a class="button dt-green" style="margin-bottom:0" href="<?php echo esc_url( home_url( '/' ) ) . "contacts/new" ?>">
        <img style="display: inline-block;" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/circle-add.svg' ) ?>"/>
        <span class="hide-for-small-only"><?php esc_html_e( "Create new contact", "disciple_tools" ); ?></span>
    </a>
    <a class="button" style="margin-bottom:0" data-open="filter-modal">
        <img style="display: inline-block;" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/filter.svg' ) ?>"/>
        <span class="hide-for-small-only"><?php esc_html_e( "Filter contacts", 'disciple_tools' ) ?></span>
    </a>
    <a class="button" style="margin-bottom:0" id="open-search">
        <img style="display: inline-block;" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/search.svg' ) ?>"/>
        <span class="hide-for-small-only"><?php esc_html_e( "Search contacts", 'disciple_tools' ) ?></span>
    </a>
    <div class="hideable-search" style="display: none; margin-top:5px">
        <input style="max-width:200px;display: inline-block;margin-bottom:0" type="search" id="search-query-mobile" placeholder="search">
        <button class="button" style="margin-bottom:0" id="search-contacts-mobile"><?php esc_html_e( "Search", 'disciple_tools' ) ?></button>
    </div>


</nav>
<div id="content">

    <div id="inner-content" class="grid-x grid-margin-x">

        <aside class="large-3 cell padding-bottom hide-for-small-only">

            <div class="bordered-box js-pane-filters">
                <?php /* Javascript may move .js-filters-modal-content to this location. */ ?>
            </div>
        </aside>

        <aside class="cell padding-bottom show-for-small-only">
            <div class="bordered-box" style="padding-top:5px;padding-bottom:5px">
                <div class="js-list-filter filter--closed" data-filter="assigned_login">
                    <div class="filter__title js-list-filter-title" style="margin-bottom:0"><?php esc_html_e( "Filters" ); ?>
                        <div style="display: inline-block" class="loading-spinner active"></div>
                    </div>

                    <div class="js-filters-accordion"></div>
                </div>
            </div>
        </aside>

        <div class="reveal js-filters-modal" id="filters-modal" data-reveal>
            <div class="js-filters-modal-content">
                <h5 class="hide-for-small-only"><?php esc_html_e( 'Filters', "disciple_tools" ); ?></h5>
                <div class="list-views">
                    <!--                    --><?php //if (user_can( get_current_user_id(), 'view_any_contacts' ) ){ ?>

                    <label class="list-view">
                        <input type="radio" name="view" value="all_contacts" class="js-list-view" autocomplete="off">
                        <?php esc_html_e( "All contacts", "disciple_tools" ); ?>
                        <span class="list-view__count js-list-view-count" data-value="all_contacts">.</span>
                    </label>
                    <!--                    --><?php //} ?>

                    <label class="list-view">
                        <input type="radio" name="view" value="my_contacts" class="js-list-view" checked autocomplete="off">
                        <?php esc_html_e( "My contacts", "disciple_tools" ); ?>
                        <span class="list-view__count js-list-view-count" data-value="my_contacts">.</span>
                    </label>
                    <div class="list-views__sub">
                        <?php if (user_can( get_current_user_id(), 'view_any_contacts' ) ){ ?>

                            <label class="list-view">
                                <input type="radio" name="view" value="assignment_needed" class="js-list-view">
                                <?php esc_html_e( "Assignment needed", "disciple_tools" ); ?>
                                <span class="list-view__count js-list-view-count" data-value="assignment_needed">.</span>
                            </label>
                        <?php } ?>
                        <label class="list-view">
                            <input type="radio" name="view" value="update_needed" class="js-list-view" autocomplete="off">
                            <?php esc_html_e( "Update needed", "disciple_tools" ); ?>
                            <span class="list-view__count js-list-view-count" data-value="update_needed">.</span>
                        </label>
                        <label class="list-view">
                            <input type="radio" name="view" value="meeting_scheduled" class="js-list-view" autocomplete="off">
                            <?php esc_html_e( "Meeting scheduled", "disciple_tools" ); ?>
                            <span class="list-view__count js-list-view-count" data-value="meeting_scheduled">.</span>
                        </label>
                        <label class="list-view">
                            <input type="radio" name="view" value="contact_unattempted" class="js-list-view" autocomplete="off">
                            <?php esc_html_e( "Contact unattempted", "disciple_tools" ); ?>
                            <span class="list-view__count js-list-view-count" data-value="contact_unattempted">.</span>
                        </label>
                    </div>
                    <label class="list-view">
                        <input type="radio" name="view" value="contacts_shared_with_me" class="js-list-view" autocomplete="off">
                        <?php esc_html_e( "Contacts shared with me", "disciple_tools" ); ?>
                        <span class="list-view__count js-list-view-count" data-value="contacts_shared_with_me">.</span>
                    </label>

                </div>
                <h5><?php esc_html_e( 'Custom Filters', "disciple_tools" ); ?></h5>
                <div class="custom-filters">

                </div>
                <div id="saved-filters">
                    <?php
                    $dt_saved_filters = Disciple_Tools_Users::get_user_filters();
                    if ( isset( $dt_saved_filters["contacts"] )){
                        foreach ( $dt_saved_filters["contacts"] as $filter ) { ?>
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
            <button class="close-button" data-close aria-label="<?php esc_html_e( "Close modal" ); ?>" type="button">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>

        <main id="main" class="large-9 cell padding-bottom" role="main">
            <?php get_template_part( '/dt-assets/parts/content' , 'contacts' ); ?>

        </main> <!-- end #main -->


    </div> <!-- end #inner-content -->

</div> <!-- end #content -->


<div class="reveal large" id="filter-modal" data-reveal style="padding:10px 0">
    <!--        <p class="lead">--><?php //esc_html_e( 'Why do you want to pause this contact?', 'disciple_tools' )?><!--</p>-->
    <div class="grid-container" style="min-height:400px">
        <div class="grid-x grid-margin-x">
            <div class="cell small-4">
                <h2><?php esc_html_e( 'New Filter', 'disciple_tools' )?></h2>

            </div>
            <div class="cell small-8">
                <div id="selected-filters"></div>

            </div>
        </div>

        <div class="grid-x grid-margin-x">
            <div class="cell small-4">
                <ul class="vertical tabs" data-tabs id="example-tabs">
                    <li class="tabs-title is-active"><a href="#assigned" aria-selected="true"><?php esc_html_e( "Assigned To", 'disciple_tools' ) ?></a></li>
                    <li class="tabs-title"><a href="#locations"><?php esc_html_e( "Locations", 'disciple_tools' ) ?></a></li>
                    <li class="tabs-title"><a href="#overall_status"><?php esc_html_e( "Status", 'disciple_tools' ) ?></a></li>
                    <li class="tabs-title"><a href="#seeker_path"><?php esc_html_e( "Seeker Path", 'disciple_tools' ) ?></a></li>
                </ul>
            </div>


            <div class="cell small-8">
                <div class="tabs-content" data-tabs-content="example-tabs">
                    <div class="tabs-panel is-active" id="assigned">
                        <h4><?php esc_html_e( "Assigned To", 'disciple_tools' ) ?></h4>
                        <div class="assigned_to details">
                            <var id="assigned_to-result-container" class="result-container assigned_to-result-container"></var>
                            <div id="assigned_to_t" name="form-assigned_to">
                                <div class="typeahead__container">
                                    <div class="typeahead__field">
                      <span class="typeahead__query">
                          <input class="js-typeahead-assigned_to input-height"
                                 name="assigned_to[query]" placeholder="<?php esc_html_e( "Search Users", 'disciple_tools' ) ?>"
                                 autocomplete="off">
                      </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tabs-panel" id="locations">
                        <h4><?php esc_html_e( "Locations", 'disciple_tools' ) ?></h4>
                        <div class="locations">
                            <var id="locations-result-container" class="result-container"></var>
                            <div id="locations_t" name="form-locations" class="scrollable-typeahead">
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
                    <div class="tabs-panel" id="overall_status">
                        <h4><?php esc_html_e( "Overall Status", 'disciple_tools' ) ?></h4>
                        <div id="overall_status-options"></div>
                    </div>
                    <div class="tabs-panel" id="seeker_path">
                        <h4><?php esc_html_e( "Seeker Path", 'disciple_tools' ) ?></h4>
                        <div id="seeker_path-options"></div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div style="margin-top:20px">
        <button class="button button-cancel clear" data-close aria-label="Close reveal" type="button">
            <?php esc_html_e( 'Cancel', 'disciple_tools' )?>
        </button>
        <button class="button loader confirm-filter-contacts" type="button" id="confirm-filter-contacts" data-close >
            <?php esc_html_e( 'Filter Contacts', 'disciple_tools' )?>
        </button>
        <button class="close-button" data-close aria-label="Close modal" type="button">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
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
