<?php
declare(strict_types=1);

if ( ! current_user_can( 'create_contacts' ) ) {
    wp_die( esc_html( "You do not have permission to publish contacts" ), "Permission denied", 403 );
}

get_header();

( function() { ?>

<div id="content">
    <div id="inner-content" class="grid-x grid-margin-x">
        <div class="large-2 medium-12 small-12 cell"></div>

        <div class="large-8 medium-12 small-12 cell">
            <form class="js-create-contact bordered-box" style="margin-bottom:200px">
                <label>
                    <?php esc_html_e( "Name of contact", "disciple_tools" ); ?>
                    <input name="title" type="text" placeholder="<?php esc_html_e( "Name", "disciple_tools" ); ?>" required dir="auto" aria-describedby="name-help-text">
                </label>
                <p class="help-text" id="name-help-text"><?php esc_html_e( "This is required", "disciple_tools" ); ?></p>

                <label>
                    <?php esc_html_e( "Phone number", "disciple_tools" ); ?>
                    <input name="phone" type="text" type="tel" placeholder="<?php esc_html_e( "Phone number", "disciple_tools" ); ?>">
                </label>
                <label>
                    <?php esc_html_e( "Email", "disciple_tools" ); ?>
                    <input name="email" type="text"  placeholder="<?php esc_html_e( "Email", "disciple_tools" ); ?>">
                </label>

                <?php if ( current_user_can( 'view_any_contacts' )) :?>
                    <label>
                        <?php esc_html_e( "Source", "disciple_tools" ); ?>
                        <select name="sources" aria-describedby="source-help-text">
                            <?php foreach ( dt_get_option( 'dt_site_custom_lists' )['sources'] as $source_key => $source ): ?>
                                <option value="<?php echo esc_attr( $source_key, 'disciple_tools' ); ?>">
                                    <?php echo esc_html( $source['label'] )?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                <?php endif; ?>

                <?php esc_html_e( "Location", "disciple_tools" ); ?>
                <div class="geonames">
                    <var id="geonames-result-container" class="result-container"></var>
                    <div id="geonames_t" name="form-geonames" class="scrollable-typeahead typeahead-margin-when-active">
                        <div class="typeahead__container">
                            <div class="typeahead__field">
                                <span class="typeahead__query">
                                    <input class="js-typeahead-geonames"
                                           name="geonames[query]" placeholder="<?php esc_html_e( "Search Locations", 'disciple_tools' ) ?>"
                                           autocomplete="off">
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <label>
                    <?php esc_html_e( "Initial comment", "disciple_tools" ); ?>
                    <textarea name="initial_comment" dir="auto" placeholder="<?php esc_html_e( "Initial comment", "disciple_tools" ); ?>"></textarea>
                </label>

                <div style="text-align: center">
                    <button class="button loader js-create-contact-button" type="submit" disabled><?php esc_html_e( "Save and continue editing", "disciple_tools" ); ?></button>
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
            geonames: {values:selectedLocations.map(i=>{return {value:i}})},
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
        input: '.js-typeahead-geonames',
        minLength: 0,
        accent: true,
        searchOnFocus: true,
        maxItem: 20,
        template: function (query, item) {
            return `<span>${_.escape(item.name)}</span>`
        },
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
                    url: wpApiShare.root + 'dt/v1/mapping_module/search_geonames_by_name',
                    data: {
                        s: "{{query}}",
                        filter: function () {
                            return _.get(window.Typeahead['.js-typeahead-geonames'].filters.dropdown, 'value', 'all')
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
                            return data.geonames
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
                resultCount = typeaheadTotals.geonames
                let text = TYPEAHEADS.typeaheadHelpText(resultCount, query, result)
                $('#geonames-result-container').html(text);
            },
            onHideLayout: function () {
                $('#geonames-result-container').html("");
            }
        }
    });
});
</script>

    <?php

} )();

get_footer();
