<?php
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

class DT_Magic_URL_Setup {
    public function __construct(){
         add_filter( 'dt_details_additional_tiles', [ $this, 'dt_details_additional_tiles' ], 110, 2 );
         add_action( 'dt_details_additional_section', [ $this, 'dt_details_additional_section' ], 110, 2 );
    }

    /**
     * Register the Apps tile for displaying registered Magic Link Apps
     * The magic link must have the $show_app_tile variable set to true.
     * @param $tiles
     * @param $post_type
     * @return mixed
     */
    public function dt_details_additional_tiles( $tiles, $post_type ){
        if ( !isset( $tiles['apps'] ) ){
            $post_types_has_registered_apps = false;
            $magic_link_apps = dt_get_registered_types();
            foreach ( $magic_link_apps as $app_root => $app_types ){
                foreach ( $app_types as $app_type => $app_value ){
                    if ( $app_value['post_type'] === $post_type && isset( $app_value['show_app_tile'] ) && $app_value['show_app_tile'] === true ){
                        $post_types_has_registered_apps = true;
                    }
                }
            }
            if ( $post_types_has_registered_apps ){
                $tiles['apps'] = [
                    'label' => __( 'Magic Links', 'disciple_tools' ),
                    'description' => __( 'Magic Links available on this record.', 'disciple_tools' )
                ];
            }
        }

        return $tiles;
    }

    /**
     * Find and display Magic Links
     * The magic links must have the $show_app_tile variable set to true.
     * @param $section
     * @param $post_type
     * @return void
     */
    public function dt_details_additional_section( $section, $post_type ){
        if ( $section === 'apps' ){
            $magic_link_apps = dt_get_registered_types();
            $record = DT_Posts::get_post( $post_type, get_the_ID() );
            foreach ( $magic_link_apps as $app_root => $app_types ){
                foreach ( $app_types as $app_type => $app_value ){
                    if ( $app_value['post_type'] === $post_type && isset( $app_value['show_app_tile'] ) && $app_value['show_app_tile'] === true ){
                        $this->add_app_row( $record, $app_value );
                    } elseif ( ( isset( $record['type']['key'] ) && $record['type']['key'] === 'user' ) && isset( $app_value['post_type'] ) && ( $app_value['post_type'] === 'user' ) && current_user_can( 'manage_dt' ) ) {

                        // Ensure record has a corresponding user id; which has a valid key already set.
                        $meta_key_value = get_user_option( $app_value['meta_key'], $record['corresponds_to_user'] );
                        if ( !empty( $meta_key_value ) && isset( $record[$app_value['meta_key']] ) && $record[$app_value['meta_key']] === $meta_key_value ) {
                            $this->add_app_row( $record, $app_value );
                        } else {
                            $this->add_app_row( $record, $app_value, false );
                        }
                    }
                }
            }
            ?>

            <style>
                .single-template .section-body:has(.app-accordion) {
                    gap: 0;
                    margin-inline: -1rem;
                }
                /* Accordion App Styles */
                .app-accordion {
                    border-bottom: 1px solid var(--surface-0);

                    @media (prefers-reduced-motion: no-preference) {
                        interpolate-size: allow-keywords;
                    }

                    summary {
                        padding: 0.75rem 1rem;
                        cursor: pointer;
                        background-color: var(--surface-1);
                        display: flex;
                        align-items: center;
                        gap: 0.5rem;
                        list-style: none;
                        position: relative;

                        &::-webkit-details-marker {
                            display: none;
                        }

                        &:hover {
                            background-color: #f0f1f2;
                        }
                    }
                    &[data-url=""] .app-link {
                        display: none;
                    }

                    &::details-content {
                        opacity: 0;
                        block-size: 0;
                        overflow-y: clip;
                        transition: content-visibility 0.5s allow-discrete, opacity 0.5s, block-size 0.5s;
                    }
                    &[open] {
                        summary {
                            border-bottom: 1px solid #e0e0e0;
                            border-radius: 4px 4px 0 0;
                        }

                        .app-caret {
                            transform: rotate(90deg);
                        }
                        &::details-content {
                            opacity: 1;
                            block-size: auto;
                        }
                    }

                    .app-label {
                        flex-grow: 1;

                        .dt-tooltip {
                            font-weight: normal;
                        }
                    }

                    .app-description-tooltip {
                        vertical-align: middle;
                    }

                    .dt-action-button.button:has(.mdi) {
                        padding: 0.25rem;
                        &.small {
                            font-size: 1.25rem;
                        }
                    }
                    .app-link-icon {
                        text-decoration: none;
                        font-size: 1.1em;
                        opacity: 0.8;
                        margin-inline-start: auto;
                        margin-inline-end: 0.25rem;

                        &:hover {
                            opacity: 1;
                        }
                    }

                    .app-caret {
                        font-size: 0.8em;
                        transition: transform 0.2s ease;
                        margin-inline-start: auto;
                    }

                    .app-content {
                        padding: 1rem;
                        background-color: var(--surface-2);
                        border-radius: 0 0 4px 4px;

                        .app-content-header {
                            display: flex;
                            margin-bottom: 0.5rem;

                            &:has(.switch-input:not(:checked)) ~ .app-link-row {
                                display: none;
                            }
                            &:has(.switch-input:not(:checked)) ~ .app-disabled-notice {
                                display: block;
                            }
                            .app-activation {
                            }
                        }
                        .dt-action-button {
                            background-color: var(--surface-0);
                        }

                        .app-link-row {
                            display: flex;
                            margin-bottom: 0.25rem;
                            gap: 0.5rem;
                            align-items: baseline;

                            .dt-action-button {
                                flex-grow: 0;
                                flex-shrink: 0;
                            }
                        }
                    }

                    .app-disabled-notice {
                        color: #666;
                        font-style: italic;
                        margin-top: 0.5rem;
                        margin-bottom: 0;
                        display: none;
                    }
                }
            </style>
            <script>
                function is_email_format_valid(email) {
                    return new RegExp('^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\\.[A-Za-z]{2,6}$').test(window.SHAREDFUNCTIONS.escapeHTML(email));
                }
                function get_app(target) {
                    const appAccordion = target.closest('.app-accordion');
                    return appAccordion.dataset;
                }
                function app_link_toggle(evt, user_id = null, app_key = null) {
                    window.makeRequest('post', 'users/app_switch', { user_id, app_key })
                        .done(function (data) {
                            if ('removed' === data) {
                                app_link_set_url(evt.target, app_key, null);
                            } else {
                                app_link_set_url(evt.target, app_key, data);
                            }
                        })
                        .fail(function (err) {
                            console.error(err);
                        })
                }
                function app_link_preview(evt) {
                    const { url, title, description } = get_app(evt.target);

                    const modalTitle = document.getElementById('modal-large-title');
                    const modalContent = document.getElementById('modal-large-content');
                    modalTitle.innerHTML = `<h3 class="section-header">${title}</h3><span class="small-text">${description}</span><hr>`;
                    modalContent.innerHTML = `<iframe src="${url}" style="width:100%;height: ${window.innerHeight - 170}px;border:1px solid lightgrey;"></iframe>`;
                    $('#modal-large').foundation('open');
                }

                <?php
                $default_message = __( 'Hello,

Please click on the button below to access your app.

{{link}}

Thanks!', 'disciple_tools' );
                ?>
                function app_link_send(evt) {
                    const { title, root, type } = get_app(evt.target);

                    const modalTitle = document.getElementById('modal-small-title');
                    modalTitle.innerHTML = `<h3 class="section-header">${title}</h3><span class="small-text"><?php echo esc_html__( 'Send the link via email.', 'disciple_tools' ) ?></span><input type="text" class="email" placeholder="<?php echo esc_attr__( 'Add email address', 'disciple_tools' )?>"/><hr>`;
                    const modalContent = document.getElementById('modal-small-content');
                    modalContent.innerHTML = `
                    <div class="grid-x">
                        <div class="cell">
                            <textarea type="text" name="note" class="note" placeholder="<?php echo esc_attr__( 'Add a note', 'disciple_tools' )?>" rows="8"><?php echo esc_textarea( $default_message ); ?></textarea>
                            <span><?php echo esc_html__( 'Message placeholders', 'disciple_tools' ); ?></span>
                            <ul>
                                <li>
                                    <span style="font-weight: bold;">{{app}}</span>: <?php echo esc_html__( 'Selected app name', 'disciple_tools' ); ?>
                                </li>
                                <li>
                                    <span style="font-weight: bold;">{{name}}</span>: <?php echo esc_html__( 'Name of the Record', 'disciple_tools' ); ?>
                                </li>
                                <li>
                                    <span style="font-weight: bold;">{{link}}</span>: <?php echo esc_html__( 'Unique link to access the app.', 'disciple_tools' ); ?>
                                </li>
                            </ul>
                            <br>
                            <button type="button" disabled class="button"><?php echo esc_html__( 'Send email with link', 'disciple_tools' ) ?> <span class="loading-spinner"></span></button>
                        </div>
                    </div>`;
                    $('#modal-small').foundation('open');

                    let contactEmail = null;
                    if (window.detailsSettings.post_fields?.contact_email && window.detailsSettings.post_fields?.contact_email.length) {
                      contactEmail = window.detailsSettings.post_fields?.contact_email[0].value;
                    }

                    const emailInput = modalTitle.querySelector('.email');
                    const noteInput = modalContent.querySelector('.note');
                    const button = modalContent.querySelector('.button');
                    if (contactEmail) {
                        emailInput.value = contactEmail;
                        button.disabled = !is_email_format_valid(contactEmail);
                    }

                    emailInput.addEventListener('keyup', function(e) {
                        button.disabled = !is_email_format_valid(this.value);
                    });

                    button.addEventListener('click', function (e) {
                       const loadingSpinner = modalContent.querySelector('.loading-spinner');
                       loadingSpinner.classList.add('active');
                       const email = emailInput.value;
                       const note = noteInput.value;
                       window.makeRequest('POST', window.detailsSettings.post_type + '/email_magic',
                           {
                               root,
                               type,
                               email,
                               note,
                               post_ids: [ window.detailsSettings.post_id ]
                           })
                            .done( data => {
                                loadingSpinner.classList.remove('active')
                                $('#modal-small').foundation('close')
                            })
                    });
                }
                function app_link_qr(evt) {
                    const { url, title, description } = get_app(evt.target);

                    const modalTitle = document.getElementById('modal-small-title');
                    modalTitle.innerHTML = `<h3 class="section-header">${title}</h3><span class="small-text"><?php echo esc_html__( 'Scan the QR code to open the magic link on a mobile device.', 'disciple_tools' ) ?></span><hr>`;
                    const modalContent = document.getElementById('modal-small-content');
                    modalContent.innerHTML = `
                    <div class="grid-x">
                        <div class="cell center">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=400x400&data=${url}" style="width: 100%;max-width:400px;" />
                        </div>
                    </div>`;
                    $('#modal-small').foundation('open')
                }
                function app_link_reset(evt) {
                    const { url, title, key, sysType, recordId } = get_app(evt.target);
                    const { ID: post_id, post_type, corresponds_to_user } = window.detailsSettings.post_fields;

                    const modalTitle = document.getElementById('modal-small-title');
                    modalTitle.innerHTML = `<h3 class="section-header">${title}</h3><span class="small-text"><?php echo esc_html__( 'Reset the security code. No data is removed. Only access. The previous link will be disabled and another one created.', 'disciple_tools' ) ?></span><hr>`;
                    const modalContent = document.getElementById('modal-small-content');
                    modalContent.innerHTML = `<button type="button" class="button delete-and-reset"><?php echo esc_html__( 'Delete and replace the app link', 'disciple_tools' ) ?>  <span class="loading-spinner"></span></button>`
                    $('#modal-small').foundation('open');

                    const button = modalContent.querySelector('.button');
                    const loadingSpinner = modalContent.querySelector('.loading-spinner');
                    
                    // Store old hash before reset
                    const appAccordion = evt.target.closest('.app-accordion');
                    const oldHash = url ? url.replace(appAccordion.dataset.urlBase, '') : '';
                    
                    button.addEventListener('click', function () {
                        this.disabled = true;
                        loadingSpinner.classList.add('active');

                        try {
                            const sys_type = sysType || (corresponds_to_user ? 'wp_user' : 'post');
                            const id = recordId || (corresponds_to_user || post_id);

                            if (corresponds_to_user) {
                                // to reset a user link, we'll just turn it off and back on again
                                // since there isn't a reset method/api
                                const payload = {
                                    user_id: corresponds_to_user,
                                    app_key: key,
                                };
                                window.makeRequest('post', 'users/app_switch', payload)
                                .done(function (data) {
                                    if ('removed' === data) {
                                        window.makeRequest('post', 'users/app_switch', payload)
                                            .done(function (data2) {
                                                if ('removed' !== data2) {
                                                    app_link_set_url(evt.target, key, data2);
                                                    // Clear expiration data when link is reset
                                                    if (window.wpApiShare) {
                                                        clear_expiration_after_reset(key, id, sys_type);
                                                    }
                                                }
                                            })
                                        $('#modal-small').foundation('close')
                                        app_link_set_url(evt.target, key, null);
                                    }
                                })
                            } else {
                                const newHash = window.sha256( Date.now() );
                                window.API.update_post(
                                    post_type,
                                    post_id, {[key]: newHash}
                                ).done( newPost => {
                                    $('#modal-small').foundation('close')
                                    app_link_set_url(evt.target, key, newPost[key]);
                                    // Clear expiration data when link is reset
                                    if (window.wpApiShare) {
                                        clear_expiration_after_reset(key, id, sys_type);
                                    }
                                })
                            }
                        } catch (err) {
                            console.error(err);
                        }
                    })
                }

                function toggle_expiration_controls(metaKey) {
                    const neverExpiresCheckbox = document.getElementById('app-expiration-never-' + metaKey);
                    const amountInput = document.getElementById('app-expiration-amount-' + metaKey);
                    const timeUnitSelect = document.getElementById('app-expiration-time-unit-' + metaKey);
                    
                    if (neverExpiresCheckbox && amountInput && timeUnitSelect) {
                        const isChecked = neverExpiresCheckbox.checked;
                        amountInput.disabled = isChecked;
                        timeUnitSelect.disabled = isChecked;
                        
                        // Clear values when disabled
                        if (isChecked) {
                            amountInput.value = '';
                            timeUnitSelect.value = 'days';
                        }
                    }
                }

                // Initialize expiration controls on page load
                function initialize_expiration_controls() {
                    const checkboxes = document.querySelectorAll('input[id^="app-expiration-never-"]');
                    checkboxes.forEach(function(checkbox) {
                        const metaKey = checkbox.getAttribute('data-meta-key') || checkbox.id.replace('app-expiration-never-', '');
                        toggle_expiration_controls(metaKey);
                    });
                }

                // Run initialization when DOM is ready
                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', initialize_expiration_controls);
                } else {
                    // DOM is already ready
                    initialize_expiration_controls();
                }

                function clear_expiration_after_reset(metaKey, id, sysType) {
                    if (!window.wpApiShare) return;
                    
                    const endpointUrl = window.wpApiShare.site_url + '/wp-json/disciple_tools_magic_links/v1/clear_link_expiration';
                    
                    const payload = {
                        meta_key: metaKey,
                        sys_type: sysType
                    };
                    
                    // Add user_id or post_id based on sys_type
                    if (sysType === 'wp_user') {
                        payload.user_id = id;
                    } else {
                        payload.post_id = id;
                    }
                    
                    $.ajax({
                        url: endpointUrl,
                        method: 'POST',
                        data: payload,
                        beforeSend: function(xhr) {
                            xhr.setRequestHeader('X-WP-Nonce', window.wpApiShare.nonce);
                        },
                        success: function(data) {
                            if (data && data.success) {
                                // Clear expiration display on frontend
                                const expirationDisplay = document.getElementById('app-expiration-display-' + metaKey);
                                if (expirationDisplay) {
                                    expirationDisplay.textContent = '---';
                                }
                                
                                // Uncheck "Never expires" checkbox
                                const neverExpiresCheckbox = document.getElementById('app-expiration-never-' + metaKey);
                                if (neverExpiresCheckbox) {
                                    neverExpiresCheckbox.checked = false;
                                    toggle_expiration_controls(metaKey);
                                }
                                
                                // Remove expiration badge if it exists
                                const appAccordion = document.querySelector('.app-accordion.' + metaKey);
                                if (appAccordion) {
                                    const badge = appAccordion.querySelector('.app-expiration-badge');
                                    if (badge) {
                                        badge.remove();
                                    }
                                }
                            }
                        },
                        error: function(xhr, status, error) {
                            // Silently fail - not critical
                            if (console && console.warn) {
                                console.warn('Could not clear expiration:', error);
                            }
                        }
                    });
                }

                function app_link_set_url(target, meta_key, value) {
                    console.log({
                        meta_key,
                        value,
                    });
                    const appAccordion = target.closest('.app-accordion');

                    const url = value ? appAccordion.dataset.urlBase + value : '';
                    appAccordion.dataset.url = url;

                    const appLink = appAccordion.querySelector('.app-link');
                    appLink.href = url;

                    const appCopy = appAccordion.querySelector('.app-copy');
                    appCopy.dataset.value = url;
                }

                function app_link_set_expiration(evt) {
                    const appAccordion = evt.target.closest('.app-accordion');
                    const { key, sysType, recordId } = get_app(evt.target);
                    const { ID: post_id, corresponds_to_user } = window.detailsSettings.post_fields;

                    const metaKey = key;
                    const expirationAmount = document.getElementById('app-expiration-amount-' + metaKey).value;
                    const expirationTimeUnit = document.getElementById('app-expiration-time-unit-' + metaKey).value;
                    const neverExpires = document.getElementById('app-expiration-never-' + metaKey).checked;

                    // Validate input
                    if (!neverExpires && (!expirationAmount || expirationAmount <= 0)) {
                        alert('<?php echo esc_js( __( 'Please enter a valid expiration amount or select "Never expires".', 'disciple_tools' ) ) ?>');
                        return;
                    }

                    // Determine sys_type and id
                    const sys_type = sysType || (corresponds_to_user ? 'wp_user' : 'post');
                    const id = recordId || (corresponds_to_user || post_id);

                    // Build request payload
                    const payload = {
                        meta_key: metaKey,
                        sys_type: sys_type,
                        links_expire_within_amount: neverExpires ? '' : expirationAmount,
                        links_expire_within_time_unit: neverExpires ? '' : expirationTimeUnit,
                        links_never_expires: neverExpires ? 'true' : 'false'
                    };

                    // Add id based on sys_type
                    if (sys_type === 'wp_user') {
                        payload.user_id = id;
                    } else {
                        payload.post_id = id;
                    }

                    // Disable button and show loading
                    const button = evt.target;
                    const originalText = button.textContent;
                    button.disabled = true;
                    button.textContent = '<?php echo esc_js( __( 'Setting...', 'disciple_tools' ) ) ?>';

                    // Build endpoint URL
                    const endpointUrl = window.wpApiShare.site_url + '/wp-json/disciple_tools_magic_links/v1/set_link_expiration_direct';

                    // Make request
                    $.ajax({
                        url: endpointUrl,
                        method: 'POST',
                        data: payload,
                        beforeSend: function(xhr) {
                            xhr.setRequestHeader('X-WP-Nonce', window.wpApiShare.nonce);
                        },
                        success: function(data) {
                            if (data && data.success) {
                                // Update expiration display
                                const expirationDisplay = document.getElementById('app-expiration-display-' + metaKey);
                                if (data.expires) {
                                    const formatted = data.expires.ts_formatted || '<?php echo esc_js( __( 'Never', 'disciple_tools' ) ) ?>';
                                    expirationDisplay.textContent = formatted;
                                    
                                    // Update data attributes
                                    appAccordion.dataset.expiresTs = data.expires.ts || '';
                                    appAccordion.dataset.expiresFormatted = data.expires.ts_formatted || '---';
                                    
                                    // Update summary badge if it exists
                                    const badge = appAccordion.querySelector('.app-expiration-badge');
                                    if (badge) {
                                        badge.textContent = formatted !== '<?php echo esc_js( __( 'Never', 'disciple_tools' ) ) ?>' ? 
                                            ('<?php echo esc_js( __( 'Expires: ', 'disciple_tools' ) ) ?>' + formatted) : 
                                            '<?php echo esc_js( __( 'Never expires', 'disciple_tools' ) ) ?>';
                                    } else if (formatted !== '<?php echo esc_js( __( 'Never', 'disciple_tools' ) ) ?>') {
                                        // Create badge if it doesn't exist
                                        const appLabel = appAccordion.querySelector('.app-label');
                                        if (appLabel) {
                                            const newBadge = document.createElement('span');
                                            newBadge.className = 'app-expiration-badge';
                                            newBadge.style.cssText = 'font-size: 0.85em; color: #666; margin-left: 0.5rem;';
                                            newBadge.textContent = '<?php echo esc_js( __( 'Expires: ', 'disciple_tools' ) ) ?>' + formatted;
                                            appLabel.appendChild(newBadge);
                                        }
                                    }
                                }

                                // Show success message
                                if (window.toastr) {
                                    window.toastr.success('<?php echo esc_js( __( 'Expiration updated successfully.', 'disciple_tools' ) ) ?>');
                                }
                            } else {
                                alert(data.message || '<?php echo esc_js( __( 'Failed to update expiration.', 'disciple_tools' ) ) ?>');
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Error updating expiration:', error, xhr.responseJSON);
                            alert('<?php echo esc_js( __( 'Error updating expiration. Please try again.', 'disciple_tools' ) ) ?>');
                        },
                        complete: function() {
                            button.disabled = false;
                            button.textContent = originalText;
                        }
                    });
                }
            </script>
            <?php
        }
    }

    /**
     * Build the accordion and buttons for each Magic Link App
     * Buttons:
     *  - View. Opens a modal displaying the magic link content
     *  - Copy. Copies the magic link url to the clipboard
     *  - Send. Send the magic link via email
     *  - QR. Display a QR code of the magic link
     *  - Reset. Reset the magic and generate a new url
     * @param $post
     * @param $app
     * @param $enabled
     * @return void
     */
    private function add_app_row( $post, $app, $enabled = true ) {
        if ( !isset( $app['meta_key'], $app['label'], $app['type'], $app['root'] ) ){
            return;
        }

        $app_url_base = trailingslashit( trailingslashit( site_url() ) . $app['url_base'] );
        $meta_key = $app['meta_key'];
        if ( !is_wp_error( $post ) && isset( $post[$meta_key] ) ){
            $key = $post[$meta_key];
        } else {
            $key = dt_create_unique_key();
            update_post_meta( get_the_ID(), $meta_key, $key );
        }
        $user_id = false;
        if ( isset( $post['corresponds_to_user'] ) ) {
            $user_id = $post['corresponds_to_user'];
        }
        $app_link = $app_url_base . $key;

        // Fetch expiration data for this magic link (independent of link_obj)
        $expiration_data = [
            'ts' => '',
            'ts_formatted' => '---',
            'ts_base' => '',
            'links_never_expires' => false
        ];
        $post_id = isset( $post['ID'] ) ? $post['ID'] : get_the_ID();
        $sys_type = $user_id ? 'wp_user' : 'post';
        $record_id = $user_id ? $user_id : $post_id;

        if ( class_exists( 'Disciple_Tools_Bulk_Magic_Link_Sender_API' ) ) {
            // Try to find matching link_obj for backward compatibility
            $link_obj_id = null;
            $matching_link_obj = null;
            $link_objs = Disciple_Tools_Bulk_Magic_Link_Sender_API::fetch_option_link_objs();

            foreach ( $link_objs as $link_obj ) {
                $generated_key = Disciple_Tools_Bulk_Magic_Link_Sender_API::generate_magic_link_type_key( $link_obj );
                if ( $generated_key === $meta_key ) {
                    $matching_link_obj = $link_obj;
                    $link_obj_id = $link_obj->id;
                    break;
                }
            }

            // Fetch expiration using unified wrapper (checks meta first, then link_obj)
            $expiration_data_result = Disciple_Tools_Bulk_Magic_Link_Sender_API::capture_expiry_details(
                $meta_key,
                $record_id,
                $sys_type,
                $matching_link_obj
            );

            // Also fetch full expiration data to get links_never_expires flag
            $full_expiration_data = Disciple_Tools_Bulk_Magic_Link_Sender_API::fetch_link_expiration_from_meta( $meta_key, $record_id, $sys_type );

            $expiration_data = array_merge( $expiration_data_result, [
                'links_never_expires' => $full_expiration_data['links_never_expires'] ?? false
            ] );
        }
        ?>
        <details class="app-accordion <?php echo esc_attr( $meta_key ); ?>"
                 data-url-base="<?php echo esc_url( $app_url_base ) ?>"
                 data-url="<?php echo esc_url( $enabled ? $app_link : '' ) ?>"
                 data-title="<?php echo esc_attr( $app['label'] ) ?>"
                 data-description="<?php echo esc_attr( $app['description'] ) ?>"
                 data-root="<?php echo esc_attr( $app['root'] ) ?>"
                 data-type="<?php echo esc_attr( $app['type'] ) ?>"
                 data-key="<?php echo esc_attr( $app['meta_key'] ) ?>"
                 data-link-obj-id="<?php echo esc_attr( $link_obj_id ?? '' ) ?>"
                 data-sys-type="<?php echo esc_attr( $sys_type ) ?>"
                 data-record-id="<?php echo esc_attr( $record_id ) ?>"
                 data-expires-ts="<?php echo esc_attr( $expiration_data['ts'] ?? '' ) ?>"
                 data-expires-formatted="<?php echo esc_attr( $expiration_data['ts_formatted'] ?? '---' ) ?>"
        >
            <summary class="app-summary">
                <div class="app-label"><?php echo esc_html( $app['label'] ) ?>
                <?php if ( !empty( $expiration_data ) && !empty( $expiration_data['ts_formatted'] ) && $expiration_data['ts_formatted'] !== '---' ): ?>
                    <span class="app-expiration-badge" style="font-size: 0.85em; color: #666; margin-left: 0.5rem;">
                        <?php echo esc_html( sprintf( __( 'Expires: %s', 'disciple_tools' ), $expiration_data['ts_formatted'] ) ) ?>
                    </span>
                <?php endif; ?>
                <?php if ( !empty( $app['description'] ) ): ?>
                <div class="dt-tooltip">
                    <span class="tooltiptext"><?php echo esc_attr( $app['description'] ) ?></span>
                    <img class="help-icon app-description-tooltip"
                         src="<?php echo esc_url( get_template_directory_uri() . '/dt-assets/images/help.svg' ) ?>"
                         title="<?php echo esc_attr( $app['description'] ) ?>"
                    />
                </div>
                <?php endif; ?>
                </div>

                <a class="app-link dt-action-button small button view"
                   href="<?php echo esc_url( $app_link ) ?>"
                   target="_blank"
                   title="<?php esc_html_e( 'link', 'disciple_tools' ) ?>">
                    <i class="mdi mdi-link"></i>
                </a>

                <span class="app-caret">▶</span>
            </summary>
            <div class="app-content">
                <div class="section-app-links <?php echo esc_attr( $meta_key ); ?>">
                    <?php if ( $app['post_type'] === 'user' ): ?>
                        <p><?php echo esc_html__( 'This app is not yet activated for this user. Enable the app to generate a link.', 'disciple_tools' ) ?></p>
                        <div class="app-content-header">
                            <div class="app-activation">
                                <span class="app-activation-label"><?php esc_html_e( 'App Activation', 'disciple_tools' ) ?></span>
                                <div class="app-toggle" data-url-base="<?php echo esc_url( $app_url_base ) ?>">
                                    <input class="switch-input" id="app_state_<?php echo esc_attr( $meta_key )?>" type="checkbox" name="follow_all"
                                           onclick="app_link_toggle(event, '<?php echo esc_attr( $user_id )?>', '<?php echo esc_attr( $meta_key )?>');"
                                        <?php echo esc_attr( $enabled ? 'checked' : '' ) ?>
                                    />
                                    <label class="switch-paddle" for="app_state_<?php echo esc_attr( $meta_key )?>">
                                        <span class="show-for-sr"><?php esc_html_e( 'Enable', 'disciple_tools' )?></span>
                                        <span class="switch-active" aria-hidden="true" style="color:white;"><?php esc_html_e( 'Yes', 'disciple_tools' )?></span>
                                        <span class="switch-inactive" aria-hidden="false"><?php esc_html_e( 'No', 'disciple_tools' )?></span>
                                    </label>
                                </div>
                            </div>
                            <div class="app-language"></div>
                        </div>
                    <?php endif; ?>
                    <div class="app-link-row">
                        <a data-tooltip
                           title="<?php esc_html_e( 'View', 'disciple_tools' ) ?>"
                           type="button"
                           onclick="app_link_preview(event)"
                           class="dt-action-button small button view">
                            <img class="dt-icon" alt="show"
                                 src="<?php echo esc_url( get_template_directory_uri() . '/dt-assets/images/visibility.svg' ) ?>"
                            />
                        </a>
                        <span class="app-link-label"><?php esc_html_e( 'Open the link', 'disciple_tools' ) ?></span>
                    </div>
                    <div class="app-link-row">
                        <a type="button" class="app-copy dt-tooltip dt-action-button small button copy_to_clipboard"
                           data-value="<?php echo esc_url( site_url() . '/' . $app['root'] . '/' . $app['type'] . '/' . $key ) ?>">
                          <span class="tooltiptext"><?php esc_html_e( 'Copy', 'disciple_tools' ); ?></span>
                          <img class="dt-icon" alt="copy" src="<?php echo esc_url( get_template_directory_uri() . '/dt-assets/images/duplicate.svg' ) ?>"/>
                        </a>
                        <span class="app-link-label"><?php esc_html_e( 'Copy the link to the clipboard', 'disciple_tools' ) ?></span>
                    </div>
                    <div class="app-link-row">
                        <a data-tooltip
                           title="<?php esc_html_e( 'Send', 'disciple_tools' ) ?>"
                           type="button"
                           onclick="app_link_send(event)"
                           class="dt-action-button small button send"
                        ><img class="dt-icon" alt="send" src="<?php echo esc_url( get_template_directory_uri() . '/dt-assets/images/send.svg' ) ?>" /></a>
                        <span class="app-link-label"><?php esc_html_e( 'Send the link via email.', 'disciple_tools' ) ?></span>
                    </div>
                    <div class="app-link-row">
                        <a data-tooltip
                           title="<?php esc_html_e( 'QR code', 'disciple_tools' ) ?>"
                           type="button"
                           onclick="app_link_qr(event)"
                           class="dt-action-button small button qr"
                        ><img class="dt-icon" alt="qrcode" src="<?php echo esc_url( get_template_directory_uri() . '/dt-assets/images/qrcode-solid.svg' ) ?>" /></a>
                        <span class="app-link-label"><?php esc_html_e( 'Scan the QR code to open the magic link on a mobile device.', 'disciple_tools' ) ?></span>
                    </div>
                    <div class="app-link-row">
                        <a data-tooltip
                           title="<?php esc_html_e( 'Reset', 'disciple_tools' ) ?>"
                           type="button"
                           onclick="app_link_reset(event)"
                           class="dt-action-button small button reset"
                        ><img class="dt-icon" alt="undo" src="<?php echo esc_url( get_template_directory_uri() . '/dt-assets/images/undo.svg' ) ?>" /></a>
                        <span class="app-link-label"><?php esc_html_e( 'Reset link', 'disciple_tools' ) ?></span>
                    </div>
                    <?php if ( class_exists( 'Disciple_Tools_Bulk_Magic_Link_Sender_API' ) ): ?>
                    <div class="app-link-row" style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #e0e0e0;">
                        <div style="display: flex; flex-direction: column; gap: 0.75rem; width: 100%; box-sizing: border-box;">
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <label style="font-weight: bold; min-width: 120px;"><?php esc_html_e( 'Link Expiration', 'disciple_tools' ) ?>:</label>
                                <span id="app-expiration-display-<?php echo esc_attr( $meta_key ) ?>" style="flex-grow: 1;">
                                    <?php echo esc_html( $expiration_data['ts_formatted'] ?? __( 'Never', 'disciple_tools' ) ) ?>
                                </span>
                            </div>
                            <div style="display: flex; align-items: center; gap: 0.5rem; width: 100%; box-sizing: border-box;">
                                <input type="number" 
                                       id="app-expiration-amount-<?php echo esc_attr( $meta_key ) ?>" 
                                       placeholder="<?php esc_attr_e( 'Amount', 'disciple_tools' ) ?>"
                                       style="flex: 2; min-width: 0; box-sizing: border-box;"
                                       min="1"
                                       value="">
                                <select id="app-expiration-time-unit-<?php echo esc_attr( $meta_key ) ?>" style="flex: 1; min-width: 0; box-sizing: border-box;">
                                    <option value="minutes"><?php esc_html_e( 'Minutes', 'disciple_tools' ) ?></option>
                                    <option value="hours"><?php esc_html_e( 'Hours', 'disciple_tools' ) ?></option>
                                    <option value="days"><?php esc_html_e( 'Days', 'disciple_tools' ) ?></option>
                                    <option value="weeks"><?php esc_html_e( 'Weeks', 'disciple_tools' ) ?></option>
                                    <option value="months"><?php esc_html_e( 'Months', 'disciple_tools' ) ?></option>
                                </select>
                            </div>
                            <div style="display: flex; align-items: center; gap: 0.25rem;">
                                <?php
                                // Check if expiration is explicitly set to "Never" (only check if links_never_expires is true)
                                $is_never_expires = ( $expiration_data['links_never_expires'] === true ||
                                                      $expiration_data['ts_formatted'] === __( 'Never', 'disciple_tools' ) || 
                                                      $expiration_data['ts_formatted'] === 'Never' );
                                ?>
                                <input type="checkbox" 
                                       id="app-expiration-never-<?php echo esc_attr( $meta_key ) ?>"
                                       style="margin: 0; vertical-align: middle;"
                                       onchange="toggle_expiration_controls('<?php echo esc_js( $meta_key ) ?>')"
                                       <?php echo $is_never_expires ? 'checked' : ''; ?>
                                       data-meta-key="<?php echo esc_attr( $meta_key ) ?>">
                                <label for="app-expiration-never-<?php echo esc_attr( $meta_key ) ?>" style="display: flex; align-items: center; margin: 0; vertical-align: middle;">
                                    <?php esc_html_e( 'Never expires', 'disciple_tools' ) ?>
                                </label>
                            </div>
                            <button type="button" 
                                    class="button small" 
                                    onclick="app_link_set_expiration(event)"
                                    style="width: 100%; max-width: 100%; box-sizing: border-box;">
                                <?php esc_html_e( 'Set Expiration', 'disciple_tools' ) ?>
                            </button>
                        </div>
                    </div>
                    <?php endif; ?>
                    <p class="app-disabled-notice"><?php esc_html_e( 'This app is currently disabled.', 'disciple_tools' ) ?></p>
                </div>
            </div>
        </details>
        <?php
    }
}
new DT_Magic_URL_Setup();
