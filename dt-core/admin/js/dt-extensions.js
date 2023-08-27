jQuery(function($){
    function remove_2_column_template() {
        $('#post-body').attr('class', 'metabox-holder');
    }

    remove_2_column_template();

    $('.plugin-install > a').on('click', function() {
        $('.plugin-install > a').removeClass('current');
        $(this).addClass('current');
        var clicked_category = $(this).data('category');
        filter_plugin_cards(clicked_category);
    });

    function load_all_plugin_cards() {
        let all_plugins = window.plugins.all_plugins;
        var translations = window.plugins.translations;
        let can_install_plugins = window.plugins.can_install_plugins;
        var install_text = window.dt_admin_shared.escape(translations.install);
        var delete_text = window.dt_admin_shared.escape(translations.delete);
        var activate_text = window.dt_admin_shared.escape(translations.activate);
        var deactivate_text = window.dt_admin_shared.escape(translations.deactivate);
        var plugin_by_text = window.dt_admin_shared.escape(translations.plugin_by);

        $.each(all_plugins, function(index, plugin) {
            var is_proof_of_concept = plugin_is_in_category(plugin, 'proof-of-concept');
            var is_beta = plugin_is_in_category(plugin, 'beta');
            var plugin_description = shorten_description(plugin['description']);
            var plugin_card_html = `
                <div class="plugin-card plugin-card-classic-editor" data-category="${plugin['categories']}" data-slug="${plugin['slug']}" style="display: none;">
                    <div class="plugin-card-top card-front">
                        <div class="name column-name">
                            <h3>
                                <a href="${plugin['permalink']}" target="_blank">
                                    ${plugin['name']}
                                <img src="${plugin['icon']}" class="plugin-icon" alt="${plugin['name']}">
                                </a>
                            </h3>
                        </div>
                        <div class="action-links">
                            <ul class="plugin-action-buttons">`;


                var installation_button_html =  `
                    <li>
                        <button class="button" data-action="install" data-plugin-slug="${plugin['slug']}" ${can_install_plugins ? '' : 'disabled' }>${install_text}</button>
                    </li>`;

                var activation_button_html = '';
                if ( plugin['installed'] && !plugin['active']  ) {
                    activation_button_html =   `
                    <li>
                        <button class="button" data-action="activate" data-plugin-slug="${plugin['slug']}">${activate_text}</button>
                    </li>`;
                    installation_button_html = ''
                    if ( can_install_plugins ) {
                      installation_button_html = `<li>
                          <button class="button" data-action="delete" data-plugin-slug="${plugin['slug']}">${delete_text}</button>
                      </li>`;
                    }
                }


                if ( plugin['active'] ) {
                    activation_button_html = `
                        <li>
                            <button class="button" data-action="deactivate" data-plugin-slug="${plugin['slug']}">${deactivate_text}</button>
                        </li>`;
                    installation_button_html = '';
                }

                plugin_card_html += activation_button_html;
                plugin_card_html += installation_button_html;


                if ( is_proof_of_concept ) {
                    plugin_card_html += `
                        <li>
                            <a class="warning-pill">POC</a>
                        </li>`;
                }
                if ( is_beta ) {
                    plugin_card_html += `
                        <li>
                            <a class="warning-pill">BETA</a>
                        </li>`;
                }
                plugin_card_html += `
                    </ul>
                        </div>
                        <div class="extension-buttons"></div>
                        <div class="desc column-description">
                            <p>${plugin_description}</p>
                            <p class="authors">
                                <cite>${plugin_by_text.replace('%s', `
                                    <a href="${plugin['author_homepage']}">
                                        ${plugin['author']}
                                        <img src="https://avatars.githubusercontent.com/${plugin['author_github_username']}?size=28" class="plugin-author-img">
                                    </a>
                                `)}
                                </cite>
                            </p>
                        </div>
                    </div>
                    <div class="card-back">
                        <div class="plugin-card-content-back"></div>
                    </div>
                </div>`;
                $('#the-list').append(plugin_card_html);
            });
    }
    load_all_plugin_cards();

    function filter_plugin_cards(category='all-plugins') {
        if ( category === 'all-plugins' ) {
            $('.plugin-card').fadeIn();
            return;
        }

        $('.plugin-card').fadeOut(50);

        let all_plugins = window.plugins.all_plugins;
        $.each(all_plugins, function(index, plugin) {
            if ( plugin['categories'] ) {
                var categories = plugin['categories'].split(',');
                if ( $.inArray(category, categories) != -1 ) {
                    $('#the-list').prepend($(`.plugin-card[data-category*="${category}"]`));
                    $(`.plugin-card[data-category*="${category}"]`).fadeIn();
                }
            }
        });
        $('#the-list').append($('#no-typeahead-results'));
    }

    filter_plugin_cards('featured');

    function plugin_is_in_category(plugin, category) {
        if ( plugin['categories'] ) {
            if ( plugin['categories'].indexOf(category) != -1 ) {
                return true;
            }
        }
        return false;
    }

    function shorten_description(description='') {
        if ( description.length > 75 ) {
            description = description.slice(0, 175) + '...';
        }
        return description;
    }

    $('.plugin-card').on('click', '.button', function() {
        $(this).parent().closest('.plugin-card').addClass('flip-card');
        var action = $(this).data('action');
        var plugin_slug = $(this).data('plugin-slug');
        var card_back = $(this).parent().closest('.plugin-card').find('.plugin-card-content-back');

        if ( action === 'install') {
            card_back.html('Installing...');
            plugin_install(plugin_slug);
        }
        if ( action === 'delete') {
            card_back.html('Deleting...');
            plugin_delete(plugin_slug);
        }
        if ( action === 'activate') {
            card_back.html('Activating...');
            plugin_activate(plugin_slug);
        }
        if ( action === 'deactivate') {
            card_back.html('Deactivating...');
            plugin_deactivate(plugin_slug);
        }
        card_back.append('<br><span class="loading"></span>');
    });

    function makeRequest(type, url, data, base = "dt/v1/") {
        // Add trailing slash if missing
        if ( !base.endsWith('/') && !url.startsWith('/')){
          base += '/'
        }
        const options = {
          type: type,
          contentType: "application/json; charset=utf-8",
          dataType: "json",
          url: url.startsWith("http") ? url : `${window.wpApiSettings.root}${base}${url}`,
          beforeSend: (xhr) => {
            xhr.setRequestHeader("X-WP-Nonce", window.wpApiSettings.nonce);
          },
        };
        if (data) {
          options.data = type === "GET" ? data : JSON.stringify(data);
        }
        return jQuery.ajax(options);
    }

    window.API = {
        plugin_install: (download_url) => makeRequest("POST", `plugin-install`, {
                download_url: download_url
            }, `dt-admin-settings/`),

        plugin_delete: (plugin_slug) => makeRequest("POST", `plugin-delete`, {
            plugin_slug: plugin_slug
        }, `dt-admin-settings/`),

        plugin_activate: (plugin_slug) => makeRequest("POST", `plugin-activate`, {
                plugin_slug: plugin_slug
            }, `dt-admin-settings/`),

        plugin_deactivate: (plugin_slug) => makeRequest("POST", `plugin-deactivate`, {
                plugin_slug: plugin_slug
            }, `dt-admin-settings/`),
    }

    function get_plugin_download_url(plugin_slug) {
        const all_plugins = window.plugins.all_plugins;
        var download_url = false;
        $.each(all_plugins, function(index, plugin) {
            if (plugin.slug === plugin_slug) {
                download_url = plugin.download_url;
            }
        });
        return download_url;
    }

    function plugin_install(plugin_slug) {
        var download_url = get_plugin_download_url(plugin_slug);
        window.API.plugin_install(download_url).promise()
        .then(function(response) {
            if ( !response ) {
                display_error_message(plugin_slug);
                return;
            }
            $(`.plugin-card[data-slug="${plugin_slug}"] > .card-front > .action-links > .plugin-action-buttons`).html(`
            <li>
                <button class="button" data-action="activate" data-plugin-slug="${plugin_slug}">Activate</button>
            </li>
            <li>
                <button class="button" data-action="delete" data-plugin-slug="${plugin_slug}">Delete</button>
            </li>`);
            $(`.plugin-card[data-slug="${plugin_slug}"]`).removeClass('flip-card');
        });
        return;
    }

    function plugin_delete(plugin_slug) {
        window.API.plugin_delete(plugin_slug).promise()
        .then(function(response) {
            if ( !response ) {
                display_error_message(plugin_slug);
                return;
            }
            $(`.plugin-card[data-slug="${plugin_slug}"] > .card-front > .action-links > .plugin-action-buttons`).html(`
            <li>
                <button class="button" data-action="install" data-plugin-slug="${plugin_slug}">Install</button>
            </li>`);
            $(`.plugin-card[data-slug="${plugin_slug}"]`).removeClass('flip-card');
        });
        return;
    }

    function plugin_activate(plugin_slug) {
        window.API.plugin_activate(plugin_slug).promise()
        .then(function(response) {
            if ( !response ) {
                display_error_message(plugin_slug);
                return;
            }
            $(`.plugin-card[data-slug="${plugin_slug}"] > .card-front > .action-links > .plugin-action-buttons`).html(`
            <li>
                <button class="button" data-action="deactivate" data-plugin-slug="${plugin_slug}">Deactivate</button>
            </li>`);
            $(`.plugin-card[data-slug="${plugin_slug}"]`).removeClass('flip-card');
        });
        return;
    }
    function plugin_deactivate(plugin_slug) {
        let can_install_plugins = window.plugins.can_install_plugins;
        window.API.plugin_deactivate(plugin_slug).promise()
        .then(function(response) {
            if ( !response ) {
                display_error_message(plugin_slug);
                return;
            }
            $(`.plugin-card[data-slug="${plugin_slug}"] > .card-front > .action-links > .plugin-action-buttons`).html(`
            <li>
                <button class="button" data-action="activate" data-plugin-slug="${plugin_slug}">Activate</button>
            </li>`);
            if ( can_install_plugins ) {
                $(`.plugin-card[data-slug="${plugin_slug}"] > .card-front > .action-links > .plugin-action-buttons`).append(`
                <li>
                <button class="button" data-action="delete" data-plugin-slug="${plugin_slug}">Delete</button>
                </li>`);
            }
            $(`.plugin-card[data-slug="${plugin_slug}"]`).removeClass('flip-card');
        });
        return;
    }

    function display_error_message(plugin_slug) {
        var card_back = $(`.plugin-card[data-slug="${plugin_slug}"] > .card-back > .plugin-card-content-back`);
        var error_message = window.plugins.translations['something_went_wrong'];
        card_back.html(`<div class="error_message">${error_message}</div>`);
        return;
    }

    $.typeahead({
        input: '.js-typeahead-extensions',
        minLength: 3,
        order: "desc",
        cancelButton: false,
        dynamic: false,
        emptyTemplate: '{{query}}',
        template: '{{name}}',
        correlativeTemplate: true,
        source: window.plugins.all_plugins,
        callback: {
            onCancel: function() {
                $('#no-typeahead-results').hide();
                $('.plugin-card').fadeIn();
                $('.plugin-install > a[data-category="all-plugins"]').addClass('current');

            },
            onResult: function(i, query, matches) {
                if (query.length < 3) {
                    return matches;
                }
                $('.plugin-card').hide();
                $('.current').removeClass('current');
                $('#no-typeahead-results').hide();
                if (matches.length == 0) {
                    $('#no-typeahead-results').fadeIn(100);
                    return;
                } else {
                    $.each(matches, function(match_index,plugin) {
                        $('#no-typeahead-results').hide();
                        $('#the-list').prepend($(`.plugin-card[data-slug="${plugin.slug}"]`));
                        $(`.plugin-card[data-slug="${plugin.slug}"]`).fadeIn();
                    });
                }
                return;
            },
        }
    })

    $('.plugin-install > a[data-category="featured"]').addClass('current');
});
