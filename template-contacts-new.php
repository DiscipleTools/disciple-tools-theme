<?php
declare(strict_types=1);

if ( ! current_user_can( 'create_contacts' ) ) {
    wp_die( esc_html( "You do not have permission to publish contacts" ), "Permission denied", 403 );
}

get_header();

( function() { ?>

<div id="content" class="template-contacts-new">
    <div id="inner-content" class="grid-x grid-margin-x">
        <div class="large-2 medium-12 small-12 cell"></div>

        <div class="large-8 medium-12 small-12 cell">
            <form class="js-create-contact bordered-box" style="margin-bottom:200px">
                <h3 class="section-header"><?php esc_html_e( "Create new contact", "disciple_tools" ); ?><button class="help-button float-right" data-section="new-contact-help-text">
                    <img class="help-icon" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/help.svg' ) ?>"/>
                </button></h3>
                <label>
                    <img src="<?php echo esc_url( get_template_directory_uri() ) . '/dt-assets/images/name.svg' ?>">
                    <?php esc_html_e( "Name of contact", "disciple_tools" ); ?>
                    <button class="help-button" data-section="contact-name-help-text">
                        <img class="help-icon" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/help.svg' ) ?>"/>
                    </button>
                    <input name="title" type="text" placeholder="<?php echo esc_html_x( "Name", 'input field placeholder', 'disciple_tools' ); ?>" required dir="auto" aria-describedby="name-help-text">
                </label>
                <p class="help-text" id="name-help-text"><?php esc_html_e( "This is required", "disciple_tools" ); ?></p>

                <label>
                    <img src="<?php echo esc_url( get_template_directory_uri() ) . '/dt-assets/images/phone.svg' ?>">
                    <?php esc_html_e( "Phone number", "disciple_tools" ); ?>
                    <button class="help-button" data-section="phone-help-text">
                        <img class="help-icon" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/help.svg' ) ?>"/>
                    </button>
                    <input name="phone" type="text" type="tel" placeholder="<?php echo esc_html_x( "Phone number", 'input field placeholder', 'disciple_tools' ); ?>">
                </label>
                <label>
                    <img src="<?php echo esc_url( get_template_directory_uri() ) . '/dt-assets/images/email.svg' ?>">
                    <?php esc_html_e( "Email", "disciple_tools" ); ?>
                    <button class="help-button" data-section="email-help-text">
                        <img class="help-icon" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/help.svg' ) ?>"/>
                    </button>
                    <input name="email" type="text"  placeholder="<?php echo esc_html_x( "Email", 'input field placeholder', "disciple_tools" ); ?>">
                </label>

                <label>
                    <img src="<?php echo esc_url( get_template_directory_uri() ) . '/dt-assets/images/source.svg' ?>">
                    <?php esc_html_e( "Source", "disciple_tools" ); ?>
                    <button class="help-button" data-section="source-help-text">
                        <img class="help-icon" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/help.svg' ) ?>"/>
                    </button>
                    <select name="sources" aria-describedby="source-help-text">
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

                <label>
                <img src="<?php echo esc_url( get_template_directory_uri() ) . '/dt-assets/images/location.svg' ?>">
                <?php esc_html_e( "Location", "disciple_tools" ); ?>
                <button class="help-button" data-section="location-help-text">
                        <img class="help-icon" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/help.svg' ) ?>"/>
                    </button>
                <div class="location_grid">
                    <var id="location_grid-result-container" class="result-container"></var>
                    <div id="location_grid_t" name="form-location_grid" class="scrollable-typeahead typeahead-margin-when-active">
                        <div class="typeahead__container">
                            <div class="typeahead__field">
                                <span class="typeahead__query">
                                    <input class="js-typeahead-location_grid"
                                           name="location_grid[query]" placeholder="<?php echo esc_html_x( "Search Locations", 'input field placeholder', 'disciple_tools' ) ?>"
                                           autocomplete="off">
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                </label>
                <label>
                    <?php esc_html_e( "Initial comment", "disciple_tools" ); ?>
                    <button class="help-button" data-section="initial-comment-help-text">
                        <img class="help-icon" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/help.svg' ) ?>"/>
                    </button>

                    <textarea name="initial_comment" dir="auto" placeholder="<?php echo esc_html_x( "Initial comment", 'input field placeholder', "disciple_tools" ); ?>"></textarea>
                </label>

                <div style="text-align: center">
                    <a href="/contacts/" class="button small" title="<?php esc_html_e( 'Cancel and return to the Contacts List page', 'disciple_tools' )?>"><?php echo esc_html_x( 'Cancel', 'button', 'disciple_tools' )?></a>
                    <button class="button loader js-create-contact-button dt-green" type="submit" disabled title="<?php esc_html_e( 'Save and continue editing the new contact', 'disciple_tools' )?>"><?php esc_html_e( "Save and continue editing", "disciple_tools" ); ?></button>
                </div>
            </form>
        </div>

     </div> <!-- inner content -->

     <div class="large-2 medium-12 small-12 cell"></div>
</div>

<script>jQuery(function($) {
    $(".js-create-contact-button").removeAttr("disabled");
    let selectedLocations = []
    $(".js-create-contact").on("submit", function(event) {
        event.preventDefault();
        $(".js-create-contact-button")
            .attr("disabled", true)
            .addClass("loading");
        let source = $(".js-create-contact select[name=sources]").val()
        API.create_post( 'contacts', {
            title: $(".js-create-contact input[name=title]").val(),
            contact_phone: [{value:$(".js-create-contact input[name=phone]").val()}],
            contact_email: [{value:$(".js-create-contact input[name=email]").val()}],
            sources: {values:[{value:source || "personal"}]},
            location_grid: {values:selectedLocations.map(i=>{return {value:i}})},
            initial_comment: $(".js-create-contact textarea[name=initial_comment]").val(),
        }).then(function(data) {
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
            template: 'Regions of Focus',
            all: 'All Locations'
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
                this.filters.dropdown = {key: "group", value: "focus", template: "Regions of Focus"}
                this.container
                    .removeClass("filter")
                    .find("." + this.options.selector.filterButton)
                    .html("Regions of Focus");
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
});
</script>

    <?php

} )();

get_footer();
