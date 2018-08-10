<?php
declare(strict_types=1);

$dt_contact_field_options = Disciple_Tools_Contact_Post_Type::instance()->get_custom_fields_settings( false );
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
            <a class="button" style="margin-bottom:0" href="/view-duplicates">
                <img style="display: inline-block;" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/duplicate.svg' ) ?>"/>
                <span class="hide-for-small-only"><?php esc_html_e( "View Duplicates", 'disciple_tools' ) ?></span>
            </a>
            <input class="search-input" style="max-width:200px;display: inline-block;margin-bottom:0" type="search" id="search-query" placeholder="search">
            <button class="button" style="margin-bottom:0" id="search">
                <img style="display: inline-block;" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/search-white.svg' ) ?>"/>
                <?php esc_html_e( "Search", 'disciple_tools' ) ?>
            </button>
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
        <input class="search-input-mobile" style="max-width:200px;display: inline-block;margin-bottom:0" type="search" id="search-query-mobile" placeholder="search">
        <button class="button" style="margin-bottom:0" id="search-mobile"><?php esc_html_e( "Search", 'disciple_tools' ) ?></button>
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
                <h5 class="hide-for-small-only"><?php esc_html_e( 'Filters', "disciple_tools" ); ?></h5>
                <div class="list-views">
                    <label class="list-view">
                        <input type="radio" name="view" value="all" class="js-list-view" autocomplete="off">
                        <?php esc_html_e( "All contacts", "disciple_tools" ); ?>
                        <span class="list-view__count js-list-view-count" data-value="all_contacts">.</span>
                    </label>
                    <label class="list-view">
                        <input type="radio" name="view" value="my" class="js-list-view" checked autocomplete="off">
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
                            <?php esc_html_e( "Contact attempt needed", "disciple_tools" ); ?>
                            <span class="list-view__count js-list-view-count" data-value="contact_unattempted">.</span>
                        </label>
                    </div>
                    <label class="list-view">
                        <input type="radio" name="view" value="shared_with_me" class="js-list-view" autocomplete="off">
                        <?php esc_html_e( "Contacts shared with me", "disciple_tools" ); ?>
                        <span class="list-view__count js-list-view-count" data-value="contacts_shared_with_me">.</span>
                    </label>

                </div>
                <h5><?php esc_html_e( 'Custom Filters', "disciple_tools" ); ?></h5>
                <div style="margin-bottom: 5px">
                    <a data-open="filter-modal"><img style="display: inline-block; margin-right:12px" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/circle-add-blue.svg' ) ?>"/><?php esc_html_e( "Add new filter", 'disciple_tools' ) ?></a>
                </div>
                <div class="custom-filters">

                </div>
                <div id="saved-filters"></div>

            </div>
        </div>

        <main id="main" class="large-9 cell padding-bottom" role="main">

            <?php get_template_part( '/dt-assets/parts/content', 'contacts' ); ?>

        </main> <!-- end #main -->


    </div> <!-- end #inner-content -->

</div> <!-- end #content -->


<div class="reveal" id="filter-modal" data-reveal>
    <div class="grid-container">
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
                    <li class="tabs-title"><a href="#subassigned"><?php esc_html_e( "Sub-assigned To", 'disciple_tools' ) ?></a></li>
                    <li class="tabs-title"><a href="#locations"><?php esc_html_e( "Locations", 'disciple_tools' ) ?></a></li>
                    <li class="tabs-title"><a href="#overall_status"><?php esc_html_e( "Status", 'disciple_tools' ) ?></a></li>
                    <li class="tabs-title"><a href="#seeker_path"><?php esc_html_e( "Seeker Path", 'disciple_tools' ) ?></a></li>
                    <li class="tabs-title"><a href="#faith_milestones"><?php esc_html_e( "Faith Milestones", 'disciple_tools' ) ?></a></li>
                    <li class="tabs-title"><a href="#requires_update"><?php esc_html_e( "Update Needed", 'disciple_tools' ) ?></a></li>
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
                <div class="tabs-panel" id="subassigned">
                    <div class="subassigned details">
                        <var id="subassigned-result-container" class="result-container subassigned-result-container"></var>
                        <div id="subassigned_t" name="form-subassigned" class="scrollable-typeahead typeahead-margin-when-active">
                            <div class="typeahead__container">
                                <div class="typeahead__field">
                                <span class="typeahead__query">
                                    <input class="js-typeahead-subassigned input-height"
                                           name="subassigned[query]" placeholder="<?php esc_html_e( "Search Contacts", 'disciple_tools' ) ?>"
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
                <div class="tabs-panel" id="overall_status">
                    <div id="overall_status-options"></div>
                </div>
                <div class="tabs-panel" id="seeker_path">
                    <div id="seeker_path-options"></div>
                </div>
                <div class="tabs-panel" id="faith_milestones">
                    <div id="faith_milestones-options">
                        <?php foreach ( $dt_contact_field_options as $dt_field_key => $dt_field_value ) :
                            if ( strpos( $dt_field_key, "milestone_" ) === 0 ) : ?>
                                <div>
                                    <label style="cursor: pointer;">
                                        <input type="checkbox" value="<?php echo esc_html( $dt_field_key ) ?>" class="milestone-filter" autocomplete="off">
                                        <?php echo esc_html( $dt_field_value["name"] ) ?>
                                    </label>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="tabs-panel" id="requires_update">
                    <div id="requires_update-options"></div>
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
                <?php esc_html_e( 'Filter Contacts', 'disciple_tools' )?>
            </button>
        </div>
    </div>
    <button class="close-button" data-close aria-label="Close modal" type="button">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<?php get_template_part( 'dt-assets/parts/modals/modal', 'filters' ); ?>


<?php get_footer(); ?>
