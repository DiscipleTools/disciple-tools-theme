jQuery(function($){
    $('.plugin-install > a').on('click', function(){
        $('.plugin-install > a').removeClass('current');
        $(this).addClass('current');
        var clicked_category = $(this).data('category');
        filter_plugin_cards(clicked_category);
    });

    function load_all_plugin_cards() {
        let all_plugins = window.plugins.all_plugins;

        $.each(all_plugins, function(index, plugin){
        var is_proof_of_concept = plugin_is_in_category(plugin, 'proof-of-concept');
        var is_beta = plugin_is_in_category(plugin, 'beta');
        var plugin_description = shorten_description(plugin['description']);
        var plugin_card_html = `
        <div class="plugin-card plugin-card-classic-editor" style="height: 275px;" data-category="${plugin['categories']}">
            <div class="plugin-card-top">
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


        var installation_button_html =  `<li>
                                            <button class="button" onclick="install('${plugin['download_url']}')">Install</button>
                                        </li>`;
        var activation_button_html = '';



        if ( plugin['installed'] & !plugin['active'] ) {
            installation_button_html = `<li>
                                            <button class="button" onclick="install('${plugin['download_url']}')">Uninstall</button>
                                        </li>`;

            activation_button_html =   `<li>
                                            <button class="button" onclick="activate('${plugin['activation_path']}); ?>')">Activate</button>
                                        </li>`;
        }


        if ( plugin['active'] ) {
            installation_button_html = '';
            activation_button_html = `<li>
                                    <button class="button" onclick="activate('${plugin['activation_path']}); ?>')">Deactivate</button>
                                </li>`;
        }

        plugin_card_html += installation_button_html;
        plugin_card_html += activation_button_html;


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
        plugin_card_html += `</ul>
                    </div>
                    <div class="extension-buttons">
                    </div>
                    <div class="desc column-description">
                        <p>${plugin_description}</p>
                        <p class="authors"> <cite>By <a href="${plugin['author_homepage']}">${plugin['author']}<img src="https://avatars.githubusercontent.com/${plugin['author_github_username']}?size=28" class="plugin-author-img"></a></cite></p>
                    </div>
                </div>
            </div>`;

            $('.loading').hide();
            $('#the-list').append(plugin_card_html);
        });
    }
    load_all_plugin_cards();

    function filter_plugin_cards(category='all-plugins') {
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
});