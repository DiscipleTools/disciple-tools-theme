<?php
declare(strict_types=1);

if ( ! current_user_can( 'create_contacts' ) ) {
    wp_die( esc_html( "You do not have permission to publish contacts" ), "Permission denied", 403 );
}

get_header();

( function() {
    $contact_fields = Disciple_Tools_Contact_Post_Type::instance()->get_custom_fields_settings();
    ?>

<div id="content" class="template-contacts-new">
    <div id="inner-content" class="grid-x grid-margin-x">
        <div class="large-2 medium-12 small-12 cell"></div>

        <div class="large-8 medium-12 small-12 cell">
            <form class="js-create-contact bordered-box" style="margin-bottom:200px">
                <h3 class="section-header"><?php esc_html_e( "Create New Contact", "disciple_tools" ); ?><button class="help-button float-right" data-section="new-contact-help-text">
                    <img class="help-icon" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/help.svg' ) ?>"/>
                </button></h3>
                <label for="name-field">
                    <img src="<?php echo esc_url( get_template_directory_uri() ) . '/dt-assets/images/name.svg' ?>">
                    <?php esc_html_e( "Name of Contact", "disciple_tools" ); ?>
                    <button class="help-button" type="button" data-section="contact-name-help-text">
                        <img class="help-icon" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/help.svg' ) ?>"/>
                    </button>
                    <input id="name-field" name="title" type="text" placeholder="<?php echo esc_html_x( "Name", 'input field placeholder', 'disciple_tools' ); ?>" required dir="auto" aria-describedby="name-help-text">
                </label>
                <p class="help-text" id="name-help-text"><?php esc_html_e( "This is required", "disciple_tools" ); ?></p>

                <label for="tel-input">
                    <img src="<?php echo esc_url( get_template_directory_uri() ) . '/dt-assets/images/phone.svg' ?>">
                    <?php esc_html_e( "Phone number", "disciple_tools" ); ?>
                    <button class="help-button" type="button" data-section="phone-help-text">
                        <img class="help-icon" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/help.svg' ) ?>"/>
                    </button>
                    <input id="tel-input" name="phone" type="tel" placeholder="<?php esc_html_e( "Phone number", 'disciple_tools' ); ?>">
                </label>

                <label for="email-input">
                    <img src="<?php echo esc_url( get_template_directory_uri() ) . '/dt-assets/images/email.svg' ?>">
                    <?php esc_html_e( "Email", "disciple_tools" ); ?>
                    <button class="help-button" type="button" data-section="email-help-text">
                        <img class="help-icon" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/help.svg' ) ?>"/>
                    </button>
                    <input id="email-input" name="email" type="text"  placeholder="<?php esc_html_e( "Email", "disciple_tools" ); ?>">
                </label>


                <?php if ( DT_Mapbox_API::get_key() ) :
                    DT_Mapbox_API::load_mapbox_header_scripts();
                    DT_Mapbox_API::load_mapbox_search_widget();
                    DT_Mapbox_API::mapbox_search_widget_css();
                    ?>
                    <!-- Locations -->
                    <label for="location-input">
                        <img src="<?php echo esc_url( get_template_directory_uri() ) . '/dt-assets/images/location.svg' ?>">
                        <?php echo esc_html( $contact_fields["location_grid"]["name"] ) ?>
                        <button class="help-button" type="button" data-section="location-help-text">
                            <img class="help-icon" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/help.svg' ) ?>"/>
                        </button>
                    </label>
                    <div class="grid-x">
                        <div id="mapbox-wrapper" class="cell">
                            <div id="mapbox-autocomplete" class="mapbox-autocomplete input-group" data-autosubmit="false">
                                <input id="mapbox-search" type="text" name="mapbox_search" placeholder="Search Location" />
                                <div class="input-group-button">
                                    <button class="button hollow" id="mapbox-spinner-button" style="display:none;"><img src="<?php echo esc_url( get_stylesheet_directory_uri() ) ?>/spinner.svg" alt="spinner" style="width: 18px;" /></button>
                                </div>
                                <div id="mapbox-autocomplete-list" class="mapbox-autocomplete-items"></div>
                            </div>
                        </div>
                    </div>
                    <script>
                        jQuery(document).ready(function() {
                            write_input_widget()
                        })
                    </script>

                <?php else : ?>

                    <!-- Locations -->
                    <label for="location-input">
                        <img src="<?php echo esc_url( get_template_directory_uri() ) . '/dt-assets/images/location.svg' ?>">
                        <?php echo esc_html( $contact_fields["location_grid"]["name"] ) ?>
                        <button class="help-button" type="button" data-section="location-help-text">
                            <img class="help-icon" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/help.svg' ) ?>"/>
                        </button>
                        <div class="location_grid">
                            <var id="location_grid-result-container" class="result-container"></var>
                            <div id="location_grid_t" name="form-location_grid" class="scrollable-typeahead typeahead-margin-when-active">
                                <div class="typeahead__container">
                                    <div class="typeahead__field">
                                    <span class="typeahead__query">
                                        <input id="location-input" class="js-typeahead-location_grid input-height"
                                               name="location_grid[query]"
                                               placeholder="<?php echo esc_html( sprintf( _x( "Search %s", "Search 'something'", 'disciple_tools' ), $contact_fields["location_grid"]["name"] ) )?>"
                                               autocomplete="off">
                                    </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </label>

                <?php endif; ?>

                <label for="source-input">
                    <img src="<?php echo esc_url( get_template_directory_uri() ) . '/dt-assets/images/source.svg' ?>">
                    <?php echo esc_html( $contact_fields["sources"]["name"] ) ?>
                    <button class="help-button" type="button" data-section="source-help-text">
                        <img class="help-icon" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/help.svg' ) ?>"/>
                    </button>
                    <select for="source-input" name="sources" aria-describedby="source-help-text">
                        <?php
                        $contacts_settings = apply_filters( "dt_get_post_type_settings", [], "contacts" );
                        $sources = $contacts_settings["fields"]["sources"]["default"];
                        foreach ( $sources as $source_key => $source ): ?>
                            <?php if ( !isset( $source["deleted"] ) || $source["delete"] !== true ) : ?>
                                <option value="<?php echo esc_attr( $source_key, 'disciple_tools' ); ?>">
                                    <?php echo esc_html( $source['label'] )?>
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </label>


                <label for="comment-input">
                    <?php esc_html_e( "Initial Comment", "disciple_tools" ); ?>
                    <button class="help-button" type="button" data-section="initial-comment-help-text">
                        <img class="help-icon" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/help.svg' ) ?>"/>
                    </button>
                    <textarea id="comment-input" name="initial_comment" dir="auto" placeholder="<?php esc_html_e( "Initial Comment", "disciple_tools" ); ?>"></textarea>
                </label>

                <div style="text-align: center">
                    <a href="/contacts/" class="button small clear"><?php echo esc_html__( 'Cancel', 'disciple_tools' )?></a>
                    <button class="button loader js-create-contact-button dt-green" type="submit" disabled title="<?php esc_html_e( 'Save and continue editing', 'disciple_tools' )?>"><?php esc_html_e( "Save and continue editing", "disciple_tools" ); ?></button>
                </div>
            </form>
        </div>

     </div> <!-- inner content -->

     <div class="large-2 medium-12 small-12 cell"></div>
</div>

<script>jQuery(function($) {
    $('input:enabled:visible:first').focus();
    $(".js-create-contact-button").removeAttr("disabled");
    let selectedLocations = []
    $(".js-create-contact").on("submit", function(event) {
        event.preventDefault();
        $(".js-create-contact-button")
            .attr("disabled", true)
            .addClass("loading");
        let source = $(".js-create-contact select[name=sources]").val()

        let data = {
            title: $(".js-create-contact input[name=title]").val(),
            contact_phone: [{value:$(".js-create-contact input[name=phone]").val()}],
            contact_email: [{value:$(".js-create-contact input[name=email]").val()}],
            sources: {values:[{value:source || "personal"}]},
            initial_comment: $(".js-create-contact textarea[name=initial_comment]").val()
        }

        if ( typeof dtMapbox === 'undefined' ) {
            data['location_grid'] = { values:selectedLocations.map(i=>{return {value:i}}) }
        } else {
            if ( typeof window.selected_location_grid_meta !== 'undefined' && typeof window.selected_location_grid_meta.location_grid_meta !== 'undefined' ) {
                data['location_grid_meta'] = window.selected_location_grid_meta.location_grid_meta
            }
        }

        API.create_post( 'contacts', data ).then(function(data) {
            window.location = data.permalink;
        }).catch(function(error) {
            $(".js-create-contact-button").removeClass("loading").addClass("alert");
            $(".js-create-contact").append(
                $("<div>").html(error.responseText)
            );
            console.error(error);
        });
        return false;
    });

    /**
     * Locations
     */
    if ( typeof dtMapbox === 'undefined' ) {
        typeaheadTotals = {}
        $.typeahead({
            input: '.js-typeahead-location_grid',
            minLength: 0,
            accent: true,
            searchOnFocus: true,
            maxItem: 20,
            dropdownFilter: [{
                key: 'group',
                value: 'focus',
                template: _.escape(window.wpApiShare.translations.regions_of_focus),
                all: _.escape(window.wpApiShare.translations.all_locations),
            }],
            source: {
                focus: {
                    display: "name",
                    ajax: {
                        url: wpApiShare.root + 'dt/v1/mapping_module/search_location_grid_by_name',
                        data: {
                            s: "{{query}}",
                            filter: function () {
                                return _.get(window.Typeahead['.js-typeahead-location_grid'].filters.dropdown, 'value', 'all')
                            }
                        },
                        beforeSend: function (xhr) {
                            xhr.setRequestHeader('X-WP-Nonce', wpApiShare.nonce);
                        },
                        callback: {
                            done: function (data) {
                                if (typeof typeaheadTotals !== "undefined") {
                                    typeaheadTotals.field = data.total
                                }
                                return data.location_grid
                            }
                        }
                    }
                }
            },
            display: "name",
            templateValue: "{{name}}",
            dynamic: true,
            multiselect: {
                matchOn: ["ID"],
                data: [],
                callback: {
                    onCancel: function (node, item) {
                        _.pull(selectedLocations, item.ID)
                    }
                }
            },
            callback: {
                onClick: function(node, a, item, event){
                    selectedLocations.push(item.ID)
                    this.addMultiselectItemLayout(item)
                    event.preventDefault()
                    this.hideLayout();
                    this.resetInput();
                },
                onReady(){
                    this.filters.dropdown = {key: "group", value: "focus", template: _.escape(window.wpApiShare.translations.regions_of_focus)}
                    this.container
                        .removeClass("filter")
                        .find("." + this.options.selector.filterButton)
                        .html(_.escape(window.wpApiShare.translations.regions_of_focus));
                },
                onResult: function (node, query, result, resultCount) {
                    resultCount = typeaheadTotals.location_grid
                    let text = TYPEAHEADS.typeaheadHelpText(resultCount, query, result)
                    $('#location_grid-result-container').html(text);
                },
                onHideLayout: function () {
                    $('#location_grid-result-container').html("");
                }
            }
        });
    }

});
</script>

    <?php

} )();

get_footer();
