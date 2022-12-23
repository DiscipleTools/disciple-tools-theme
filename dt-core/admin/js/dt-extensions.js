jQuery(function($){
    $('.plugin-install > a').on('click', function(){
        $('.plugin-install > a').removeClass('current');
        $(this).addClass('current');
        var clicked_category = $(this).data('category');
        change_plugin_category(clicked_category);
    });

    function change_plugin_category(category) {
        if ( category === 'all_plugins' ) {
            $('.plugin-card').show();
            return;
        }
        $('.plugin-card').hide();
        $(`.plugin-card[data-category*="${category}"]`).show();
    }

    let plugins = window.plugins.all_plugins;
    $.each(plugins, function(index, plugin){
        var is_proof_of_concept = plugin_is_in_category(plugin, 'proof-of-concept');
        var is_beta = plugin_is_in_category(plugin, 'beta');
        var plugin_description = shorten_description(plugin['description']);
        var plugin_card_html = `
        <style>
            #the-list {
                display: flex;
                flex-wrap: wrap;
            }
            .extension-buttons {
                display: content;
            }
        </style>
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
});

function plugin_is_in_category(plugin, category) {
    if ( plugin['categories'] ) {
        if ( plugin['categories'].indexOf(category) != -1 ) {
            return true;
        }
    }
    return false;
}

function shorten_description(description) {
    if ( description.length > 75 ) {
        description = description.slice(0, 175) + '...';
    }
    return description;
}