<?php
declare(strict_types=1);
if ( !defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

$url = dt_get_url_path();
$dt_post_type = explode( "/", $url )[0];

if ( ! current_user_can( 'create_' . $dt_post_type ) ) {
    wp_die( esc_html( "You do not have permission to publish " . $dt_post_type ), "Permission denied", 403 );
}

get_header();
$post_settings = apply_filters( "dt_get_post_type_settings", [], $dt_post_type );

?>

    <div id="content" class="template-new-post">
        <div id="inner-content" class="grid-x grid-margin-x">
            <div class="large-2 medium-12 small-12 cell"></div>

            <div class="large-8 medium-12 small-12 cell">
                <form class="js-create-post bordered-box">
                    <label for="title">
                        <?php esc_html_e( "Name", "disciple_tools" ); ?>
                    </label>
                    <input name="title" type="text" placeholder="<?php echo esc_html_x( "Name", 'input field placeholder', 'disciple_tools' ); ?>" required aria-describedby="name-help-text">
                    <p class="help-text" id="name-help-text"><?php esc_html_e( "This is required", "disciple_tools" ); ?></p>

                    <?php foreach ( $post_settings["fields"] as $field_key => $field_settings ) {
                        if ( !empty( $field_settings['in_create_form'] ) ) {
                            render_field_for_display( $field_key, $post_settings['fields'], [] );
                        }
                    } ?>
                    <div style="text-align: center">
                        <button class="button loader js-create-post-button" type="submit" disabled><?php esc_html_e( "Save and continue editing", "disciple_tools" ); ?></button>
                    </div>
                </form>

            </div>

            <div class="large-2 medium-12 small-12 cell"></div>
        </div>
    </div>

    <script>jQuery(function($) {
        $(".js-create-post-button").removeAttr("disabled");

        // Clicking the plus sign next to the field label
        $('button.add-button').on('click', e => {
            const listClass = $(e.currentTarget).data('list-class')
            const $list = $(`#edit-${listClass}`)

            $list.append(`<li style="display: flex">
              <input type="text" class="dt-communication-channel" data-type="${_.escape( listClass )}"/>
              <button class="button clear delete-button new-${_.escape( listClass )}" type="button">
                  <img src="${_.escape( window.wpApiShare.template_dir )}/dt-assets/images/invalid.svg">
              </button>
            </li>`)
        })
        $('button.dt_multi_select').on('click',function () {
            let fieldKey = $(this).data("field-key")
            let optionKey = $(this).attr('id')
            let field = jQuery(`[data-field-key="${fieldKey}"]#${optionKey}`)
            if (field.hasClass("selected-select-button")){
                field.addClass("empty-select-button")
                field.removeClass("selected-select-button")
            } else {
                field.removeClass("empty-select-button")
                field.addClass("selected-select-button")
            }
        })
        $('.js-create-post').on('click', '.delete-button', function () {
            $(this).parent().remove()
        })
        let new_contact = {}

        $(".js-create-post").on("submit", function() {
            $(".js-create-post-button")
                .attr("disabled", true)
                .addClass("loading");
            new_contact.title = $(".js-create-post input[name=title]").val()
            $('.select-field').each((index, entry)=>{
                new_contact[$(entry).attr('id')] = $(entry).val()
            })
            $('.text-input').each((index, entry)=>{
                new_contact[$(entry).attr('id')] = $(entry).val()
            })
            $('.dt-communication-channel').each((index, entry)=>{
                let channel = $(entry).data('type')
                if ( !new_contact[channel]){
                    new_contact[channel] =[]
                }
                new_contact[channel].push({
                    value: $(entry).val()
                })
            })
            $('.selected-select-button').each((index, entry)=>{
                let optionKey = $(entry).attr('id')
                let fieldKey = $(entry).data("field-key")
                if ( !new_contact[fieldKey]){
                    new_contact[fieldKey] = {values:[]};
                }
                new_contact[fieldKey].values.push({
                    "value": optionKey
                })
            })


            API.create_post( '<?php echo esc_html( $dt_post_type ) ?>', new_contact).promise().then(function(data) {
                // window.location = data.permalink;
            }).catch(function(error) {
                $(".js-create-post-button").removeClass("loading").addClass("alert");
                $(".js-create-post").append(
                    $("<div>").html(error.responseText)
                );
                console.error(error);
            });
            return false;
        });

        $(".typeahead__query input").each((key, el)=>{
            let field_key = $(el).data('field')
            let post_type = $(el).data('post_type')
            let field_type = $(el).data('field_type')
            typeaheadTotals = {}
            if (!window.Typeahead[`.js-typeahead-${field_key}`]) {

                if ( field_type === "connection"){

                    $.typeahead({
                        input: `.js-typeahead-${field_key}`,
                        minLength: 0,
                        accent: true,
                        searchOnFocus: true,
                        maxItem: 20,
                        template: function (query, item) {
                            return `<span dir="auto">${_.escape(item.name)} (#${_.escape( item.ID )})</span>`
                        },
                        source: TYPEAHEADS.typeaheadPostsSource(post_type),
                        display: "name",
                        templateValue: "{{name}}",
                        dynamic: true,
                        multiselect: {
                            matchOn: ["ID"],
                            data: [],
                            callback: {
                                onCancel: function (node, item) {
                                    _.pullAllBy(new_contact[field_key].values, [{value:item.ID}], "value")
                                }
                            }
                        },
                        callback: {
                            onResult: function (node, query, result, resultCount) {
                                let text = TYPEAHEADS.typeaheadHelpText(resultCount, query, result)
                                $(`#${field_key}-result-container`).html(text);
                            },
                            onHideLayout: function () {
                                $(`#${field_key}-result-container`).html("");
                            },
                            onClick: function (node, a, item, event ) {
                                if ( !new_contact[field_key] ){
                                    new_contact[field_key] = { values: [] }
                                }
                                new_contact[field_key].values.push({value:item.ID})
                                //get list from opening again
                                this.addMultiselectItemLayout(item)
                                event.preventDefault()
                                this.hideLayout();
                                this.resetInput();
                            }
                        }
                    });
                } else if ( field_type === "location" ){
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
                                    url: window.wpApiShare.root + 'dt/v1/mapping_module/search_location_grid_by_name',
                                    data: {
                                        s: "{{query}}",
                                        filter: function () {
                                            return _.get(window.Typeahead['.js-typeahead-location_grid'].filters.dropdown, 'value', 'all')
                                        }
                                    },
                                    beforeSend: function (xhr) {
                                        xhr.setRequestHeader('X-WP-Nonce', window.wpApiShare.nonce);
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
                                    _.pullAllBy(new_contact[field_key].values, [{value:item.ID}], "value")
                                }
                            }
                        },
                        callback: {
                            onClick: function(node, a, item, event){
                                if ( !new_contact[field_key] ){
                                    new_contact[field_key] = { values: [] }
                                }
                                new_contact[field_key].values.push({value:item.ID})
                                //get list from opening again
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
            }
        })

    });</script>


<?php
get_footer();
