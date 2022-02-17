<?php
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

abstract class DT_Magic_Url_Base {
    public $magic = false;
    public $parts = false;

    public $post_type = '';
    public $type = '';
    public $type_name = '';
    private $meta_key;
    public $page_title = '';
    public $page_description = '';
    public $type_actions = [
        '' => "Manage",
    ];
    public $show_bulk_send = false; // enables bulk send of magic links from list page
    public $show_app_tile = false; // enables addition to "app" tile sharing features

    public $module = ""; // Lets a magic url be a module as well
    public $instance_id = ""; // Allows having multiple versions of the same magic link for a user. Creating different meta_keys.
    public $meta = []; // Allows for instance specific data.
    public $translatable = [ 'query' ]; // Order of translatable flags to be checked. Translate on first hit..!

    public function __construct() {

        // check for an instance_id in the magic_link url
        $id = $this->fetch_incoming_link_param( 'id' );
        $this->instance_id = ( ! empty( $id ) ) ? $id : '';

        // register type
        $this->magic = new DT_Magic_URL( $this->root );
        add_filter( 'dt_magic_url_register_types', [ $this, 'dt_magic_url_register_types' ], 10, 1 );
        // register REST and REST access
        add_filter( 'dt_allow_rest_access', [ $this, 'authorize_url' ], 10, 1 );
        // add send and tiles
        add_filter( 'dt_details_additional_tiles', [ $this, 'dt_details_additional_tiles' ], 10, 2 );
        add_action( 'dt_details_additional_section', [ $this, 'dt_details_additional_section' ], 30, 2 );
        add_filter( 'dt_settings_apps_list', [ $this, 'dt_settings_apps_list' ], 10, 1 );

        // fail if not valid url
        $this->parts = $this->magic->parse_url_parts();
        if ( !$this->parts ){
            return;
        }

        // fail if does not match type
        if ( $this->type !== $this->parts['type'] ){
            return;
        }

        // register url and access
        add_filter( 'dt_blank_access', [ $this, '_has_access' ] ); // gives access once above tests are passed
        add_filter( 'dt_templates_for_urls', [ $this, 'register_url' ], 199, 1 ); // registers url as valid once tests are passed
        add_filter( 'dt_allow_non_login_access', function (){ // allows non-logged in visit
            return true;
        }, 100, 1 );
        add_filter( "dt_blank_title", [ $this, "page_tab_title" ] ); // adds basic title to browser tab
        add_action( 'wp_print_scripts', [ $this, 'print_scripts' ], 5 ); // authorizes scripts
        add_action( 'wp_print_footer_scripts', [ $this, 'print_scripts' ], 5 ); // authorizes scripts
        add_action( 'wp_print_styles', [ $this, 'print_styles' ], 1500 ); // authorizes styles

        add_action( 'dt_blank_head', [ $this, '_header' ] );
        add_action( 'dt_blank_footer', [ $this, '_footer' ] );

        // determine language locale to be adopted
        $this->determine_language_locale( $this->parts );
    }

    /**
     * Switch to default DT translation text domain
     *
     * @return void
     */
    public function hard_switch_to_default_dt_text_domain(): void {
        unload_textdomain( "disciple_tools" );
        load_theme_textdomain( 'disciple_tools', get_template_directory() . '/dt-assets/translation' );
    }

    /**
     * Extract incoming link specific parameters; E.g. instance id...
     *
     * @param $param
     *
     * @return string
     */
    public function fetch_incoming_link_param( $param ): string {
        if ( isset( $_SERVER['REQUEST_URI'] ) ) {
            parse_str( parse_url( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ), PHP_URL_QUERY ), $link_params );

            return $link_params[ $param ] ?? '';
        }

        return '';
    }

    public function fetch_incoming_user_lang( $parts = [] ): string {
        if ( ! empty( $parts['post_type'] ) && ! empty( $parts['post_id'] ) ) {
            if ( $parts['post_type'] === 'user' ) {
                return get_user_locale( $parts['post_id'] );
            }
        }

        return '';
    }

    public function fetch_incoming_contact_lang( $parts = [] ): string {
        if ( ! empty( $parts['post_type'] ) && ! empty( $parts['post_id'] ) ) {
            if ( $parts['post_type'] === 'contacts' ) {
                $contact = DT_Posts::get_post( $parts['post_type'], $parts['post_id'] );
                if ( ! empty( $contact ) && ! is_wp_error( $contact ) ) {
                    foreach ( $contact['languages'] ?? [] as $lang ) {

                        // First, ensure $lang is not already within required locale shape
                        if ( strpos( $lang, "_" ) !== false ) {
                            return $lang;
                        }

                        // Next, attempt to locate corresponding default locale within global languages list
                        $global_lang_list = dt_get_global_languages_list();
                        if ( ! empty( $global_lang_list ) && isset( $global_lang_list[ $lang ], $global_lang_list[ $lang ]['default_locale'] ) ) {
                            return $global_lang_list[ $lang ]['default_locale'];
                        }

                        // If not found, then attempt to locate within available languages list
                        foreach ( dt_get_available_languages() ?? [] as $avail_lang ) {
                            if ( isset( $avail_lang['language'] ) && $avail_lang['language'] === $lang ) {
                                return $avail_lang['language'];
                            }
                        }
                    }
                }
            }
        }

        return '';
    }

    /**
     * Determine language locale to be adopted; based on translatable flags
     *
     * @param array $parts
     *
     * @return void
     */
    public function determine_language_locale( array $parts = [] ): void {

        $lang           = null;
        $flag_satisfied = false;

        // Determine language locale to be adopted
        foreach ( $this->translatable ?? [] as $flag ) {
            if ( ! $flag_satisfied ) {
                switch ( $flag ) {
                    case 'query':
                        $lang = $this->fetch_incoming_link_param( 'lang' );
                        break;
                    case 'user':
                        $lang = $this->fetch_incoming_user_lang( $parts );
                        break;
                    case 'contact':
                        $lang = $this->fetch_incoming_contact_lang( $parts );
                        break;
                }
                $flag_satisfied = ! empty( $lang );
            }
        }

        // If determined, associate with relevant hook
        if ( ! empty( $lang ) ) {
            add_filter( 'determine_locale', function ( $locale ) use ( $lang ) {
                $lang_code = sanitize_text_field( wp_unslash( $lang ) );
                if ( ! empty( $lang_code ) ) {
                    return apply_filters( 'ml_locale_change', $lang_code );
                }

                return $locale;
            } );
        }
    }

    /**
     * Test for core parts elements
     * @note    Use the true/false to include or exclude testing for the post_id in the registered magic link type. Test for
 *              post_id if building magic link from a contact, group, etc. Don't test if building magic link for a user or
     *          non-post_type based link.
     *
     * @note    Primarily used in 'extends' classes for a progress check inside the construct. See stater plugin / magic link
     * @return bool
     */
    public function check_parts_match( $test_post_id = true ){
        if ( $test_post_id ) {
            if ( isset( $this->parts["post_id"], $this->parts["root"], $this->parts["type"] ) ){
                if ( $this->type === $this->parts["type"] && $this->root === $this->parts["root"] && !empty( $this->parts["post_id"] ) ){
                    return true;
                }
            }
        } else {
            if ( isset( $this->parts["root"], $this->parts["type"] ) ){
                if ( $this->type === $this->parts["type"] && $this->root === $this->parts["root"] ){
                    return true;
                }
            }
        }

        return false;
    }

    public function _has_access() : bool {
        $parts = $this->parts;

        // test 1 : correct url root and type
        if ( $parts ){ // parts returns false
            return true;
        }

        return false;
    }

    /**
     * Builds page title for browser tab
     * @note Copy function to 'extends' class to override or modify
     * @return string
     */
    public function page_tab_title(){
        return $this->page_title;
    }

    /**
     * Builds registered magic link
     * @param array $types
     * @return array
     */
    public function dt_magic_url_register_types( array $types ): array {
        if ( ! isset( $types[ $this->root ] ) ) {
            $types[ $this->root ] = [];
        }

        $meta_key_appendage                  = ( ! empty( $this->instance_id ) ) ? '_' . $this->instance_id : '';
        $this->meta_key                      = $this->root . '_' . $this->type . '_magic_key' . $meta_key_appendage;
        $types[ $this->root ][ $this->type ] = [
            'name'           => $this->type_name,
            'root'           => $this->root,
            'type'           => $this->type,
            'meta_key'       => $this->meta_key,
            'actions'        => $this->type_actions,
            'post_type'      => $this->post_type,
            'instance_id'    => $this->instance_id,
            'show_bulk_send' => $this->show_bulk_send,
            'show_app_tile'  => $this->show_app_tile,
            'key'            => $this->root . '_' . $this->type . '_magic_key',
            'url_base'       => $this->root . '/' . $this->type,
            'label'          => $this->page_title,
            'description'    => $this->page_description,
            'meta'           => $this->meta
        ];

        return $types;
    }

    /**
     * Tests the url and if it matches as an approved magic link it loads the appropriate template.
     * @param $template_for_url
     * @return mixed
     */
    public function register_url( $template_for_url ){
        $parts = $this->parts;

        // test 1 : correct url root and type
        if ( ! $parts ){ // parts returns false
            return $template_for_url;
        }

        // test 2 : only base url requested
        if ( empty( $parts['public_key'] ) ){ // no public key present
            $template_for_url[ $parts['root'] . '/'. $parts['type'] ] = 'template-blank.php';
            return $template_for_url;
        }

        // test 3 : no specific action requested
        if ( empty( $parts['action'] ) ){ // only root public key requested
            $template_for_url[ $parts['root'] . '/'. $parts['type'] . '/' . $parts['public_key'] ] = 'template-blank.php';
            return $template_for_url;
        }

        // test 4 : valid action requested
        $actions = $this->magic->list_actions( $parts['type'] );
        if ( isset( $actions[ $parts['action'] ] ) ){
            $template_for_url[ $parts['root'] . '/'. $parts['type'] . '/' . $parts['public_key'] . '/' . $parts['action'] ] = 'template-blank.php';
        }

        return $template_for_url;
    }

    /**
     * Used as an alternate to register_url, primarily for root home page applications
     */
    public function theme_redirect() {
        $path = get_theme_file_path( 'template-blank.php' );
        include( $path );
        die();
    }

    /**
     * Open default restrictions for access to registered endpoints
     * @param $authorized
     * @return bool
     */
    public function authorize_url( $authorized ){
        if ( isset( $_SERVER['REQUEST_URI'] ) && strpos( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ), $this->root . '/v1/'.$this->type ) !== false ) {
            $authorized = true;
        }
        return $authorized;
    }

    /**
     * Authorizes scripts allowed to load in magic link
     *
     * Controls the linked scripts loaded into the header.
     * @note This overrides standard DT header assets which natively have login authentication requirements.
     */
    public function print_scripts(){
        // @link /disciple-tools-theme/dt-assets/functions/enqueue-scripts.php
        $allowed_js = apply_filters( 'dt_magic_url_base_allowed_js', [
            'jquery',
            'jquery-ui',
            'lodash',
            'lodash-core',
            'site-js',
            'shared-functions',
            'moment',
            'datepicker'
        ]);

        global $wp_scripts;

        if ( isset( $wp_scripts ) ){
            foreach ( $wp_scripts->queue as $key => $item ){
                if ( ! in_array( $item, $allowed_js ) ){
                    unset( $wp_scripts->queue[$key] );
                }
            }
        }
        if ( isset( $wp_scripts ) ){
            foreach ( $wp_scripts->registered as $key => $item ){
                if ( ! in_array( $key, $allowed_js ) ){
                    unset( $wp_scripts->registered[$key] );
                }
            }
        }
        unset( $wp_scripts->registered['mapbox-search-widget']->extra['group'] ); //lets the mapbox geocoder work
    }

    /**
     * Authorizes styles allowed to load in magic link
     *
     * Controls the linked styles loaded into the header.
     * @note This overrides standard DT header assets.
     */
    public function print_styles(){
        // @link /disciple-tools-theme/dt-assets/functions/enqueue-scripts.php
        $allowed_css = apply_filters( 'dt_magic_url_base_allowed_css', [
            'jquery-ui-site-css',
            'foundation-css',
            'site-css',
            'datepicker-css'
        ]);

        global $wp_styles;
        if ( isset( $wp_styles ) ) {
            foreach ( $wp_styles->queue as $key => $item ) {
                if ( !in_array( $item, $allowed_css ) ) {
                    unset( $wp_styles->queue[$key] );
                }
            }
        }
    }

    /**
     * Loads enqueued scripts and custom printed scripts to header
     * @note this is a required method because the standard DT header includes authentication requirements
     * @note Copy function to 'extends' class to override or modify
     */
    public function _header(){
        wp_head();
        $this->header_style();
        $this->header_javascript();
    }
    /**
     * Loads enqueued styles and custom printed styles to header
     * @note Copy function to 'extends' class to override or modify
     */
    public function _footer(){
        $this->footer_javascript();
        wp_footer();
    }

    /**
     * Adds printed styles to header
     * @note Copy function to 'extends' class to override or modify
     */
    public function header_style(){}

    /**
     * Adds printed scripts to header
     * @note Copy function to 'extends' class to override or modify
     */
    public function header_javascript(){}

    /**
     * Adds printed scripts to footer
     * @note Copy function to 'extends' class to override or modify
     */
    public function footer_javascript(){}

    protected function check_module_enabled_and_prerequisites(){
        $modules = dt_get_option( 'dt_post_type_modules' );
        $module_enabled = isset( $modules[$this->module]["enabled"] ) ? $modules[$this->module]["enabled"] : false;
        foreach ( $modules[$this->module]["prerequisites"] as $prereq ){
            $prereq_enabled = isset( $modules[$prereq]["enabled"] ) ? $modules[$prereq]["enabled"] : false;
            if ( !$prereq_enabled ){
                return false;
            }
        }
        return $module_enabled;
    }

    public function dt_details_additional_tiles( $tiles, $post_type = "" ) {
        if ( ! $this->show_app_tile ) {
            return $tiles;
        }

        $magic_post_type = $this->post_type;
        if ( 'user' === $magic_post_type && $this->show_app_tile ) {
            $magic_post_type = 'contacts'; // extend user magic app to contacts tile
        }

        if ( $post_type === $magic_post_type && ! isset( $tiles["apps"] ) ){
            $tiles["apps"] = [
                "label" => __( "Apps", 'disciple_tools' ),
                "description" => __( "Apps available on this record.", 'disciple_tools' )
            ];
        }
        return $tiles;
    }

    public function dt_details_additional_section( $section, $post_type ) {
        if ( ! $this->show_app_tile ) {
            return;
        }

        $magic_post_type = $this->post_type;
        if ( 'user' === $magic_post_type && $this->show_app_tile ) {
            $magic_post_type = 'contacts'; // extend user magic app to contacts tile
        }

        if ( $section === "apps" && $post_type === $magic_post_type ) {
            $record = DT_Posts::get_post( $post_type, get_the_ID() );
            if ( isset( $record[$this->meta_key] ) ) {
                $key = $record[$this->meta_key];
            } else {
                $key = dt_create_unique_key();
                update_post_meta( get_the_ID(), $this->meta_key, $key );
            }
            ?>
            <div class="section-subheader"><?php echo esc_html( $this->page_title ) ?></div>
            <div class="section-app-links <?php echo esc_attr( $this->meta_key ); ?>">
                <a type="button" class="empty-select-button select-button small button view"><img class="dt-icon" alt="show" src="<?php echo esc_url( get_template_directory_uri() . '/dt-assets/images/visibility.svg' ) ?>" /></a>
                <a type="button" class="empty-select-button select-button small button copy_to_clipboard" data-value="<?php echo esc_url( site_url() . '/' . $this->root . '/' .$this->type . '/' . $key ) ?>"><img class="dt-icon" alt="copy" src="<?php echo esc_url( get_template_directory_uri() . '/dt-assets/images/duplicate.svg' ) ?>" /></a>
                <a type="button" class="empty-select-button select-button small button send"><img class="dt-icon" alt="send" src="<?php echo esc_url( get_template_directory_uri() . '/dt-assets/images/send.svg' ) ?>" /></a>
                <a type="button" class="empty-select-button select-button small button qr"><img class="dt-icon" alt="qrcode" src="<?php echo esc_url( get_template_directory_uri() . '/dt-assets/images/qrcode-solid.svg' ) ?>" /></a>
                <a type="button" class="empty-select-button select-button small button reset"><img class="dt-icon" alt="undo" src="<?php echo esc_url( get_template_directory_uri() . '/dt-assets/images/undo.svg' ) ?>" /></a>
            </div>
            <script>
                jQuery(document).ready(function(){
                    if ( typeof window.app_key === 'undefined' ){
                        window.app_key = []
                    }
                    if ( typeof window.app_url === 'undefined' ){
                        window.app_url = []
                    }
                    window.app_key['<?php echo esc_attr( $this->meta_key ) ?>'] = '<?php echo esc_attr( $key ) ?>'
                    window.app_url['<?php echo esc_attr( $this->meta_key ) ?>'] = '<?php echo esc_url( site_url() . '/' . $this->root . '/' .$this->type . '/' ) ?>'

                    jQuery('.<?php echo esc_attr( $this->meta_key ); ?>.select-button.button.copy_to_clipboard').data('value', `${window.app_url['<?php echo esc_attr( $this->meta_key ) ?>']}${window.app_key['<?php echo esc_attr( $this->meta_key ) ?>']}`)
                    jQuery('.section-app-links.<?php echo esc_attr( $this->meta_key ); ?> .view').on('click', function(e){
                        jQuery('#modal-large-title').empty().html(`<h3 class="section-header"><?php echo esc_html( $this->page_title )  ?></h3><span class="small-text"><?php echo esc_html( $this->page_description ) ?></span><hr>`)
                        jQuery('#modal-large-content').empty().html(`<iframe src="${window.app_url['<?php echo esc_attr( $this->meta_key ) ?>']}${window.app_key['<?php echo esc_attr( $this->meta_key ) ?>']}" style="width:100%;height: ${window.innerHeight - 170}px;border:1px solid lightgrey;"></iframe>`)
                        jQuery('#modal-large').foundation('open')
                    })
                    jQuery('.section-app-links.<?php echo esc_attr( $this->meta_key ); ?> .send').on('click', function(e){
                        jQuery('#modal-small-title').empty().html(`<h3 class="section-header"><?php echo esc_html( $this->page_title )  ?></h3><span class="small-text"><?php echo esc_html__( 'Send a link via email through the system.', 'disciple_tools' ) ?></span><hr>`)
                        jQuery('#modal-small-content').empty().html(`<div class="grid-x"><div class="cell"><input type="text" class="note <?php echo esc_attr( $this->meta_key ); ?>" placeholder="Add a note" /><br><button type="button" class="button <?php echo esc_attr( $this->meta_key ); ?>"><?php echo esc_html__( 'Send email with link', 'disciple_tools' ) ?> <span class="<?php echo esc_attr( $this->meta_key ); ?> loading-spinner"></span></button></div></div>`)
                        jQuery('#modal-small').foundation('open')
                        jQuery('.button.<?php echo esc_attr( $this->meta_key ); ?>').on('click', function(e){
                            jQuery('.<?php echo esc_attr( $this->meta_key ); ?>.loading-spinner').addClass('active')
                            let note = jQuery('.note.<?php echo esc_attr( $this->meta_key ); ?>').val()
                            makeRequest('POST', window.detailsSettings.post_type + '/email_magic', { root: '<?php echo esc_attr( $this->root ); ?>', type: '<?php echo esc_attr( $this->type ); ?>', note: note, post_ids: [ window.detailsSettings.post_id ] } )
                                .done( data => {
                                    jQuery('.<?php echo esc_attr( $this->meta_key ); ?>.loading-spinner').removeClass('active')
                                    jQuery('#modal-small').foundation('close')
                                })
                        })
                    })
                    jQuery('.section-app-links.<?php echo esc_attr( $this->meta_key ); ?> .qr').on('click', function(e){
                        jQuery('#modal-small-title').empty().html(`<h3 class="section-header"><?php echo esc_html( $this->page_title )  ?></h3><span class="small-text"><?php echo esc_html__( 'QR codes are useful for passing the coaching links to mobile devices.', 'disciple_tools' ) ?></span><hr>`)
                        jQuery('#modal-small-content').empty().html(`<div class="grid-x"><div class="cell center"><img src="https://api.qrserver.com/v1/create-qr-code/?size=400x400&data=${window.app_url['<?php echo esc_attr( $this->meta_key ) ?>']}${window.app_key['<?php echo esc_attr( $this->meta_key ) ?>']}" style="width: 100%;max-width:400px;" /></div></div>`)
                        jQuery('#modal-small').foundation('open')
                    })
                    jQuery('.section-app-links.<?php echo esc_attr( $this->meta_key ); ?> .reset').on('click', function(e){
                        jQuery('#modal-small-title').empty().html(`<h3 class="section-header"><?php echo esc_html( $this->page_title )  ?></h3><span class="small-text"><?php echo esc_html__( 'Reset the security code. No data is removed. Only access. The previous link will be disabled and another one created.', 'disciple_tools' ) ?></span><hr>`)
                        jQuery('#modal-small-content').empty().html(`<button type="button" class="button <?php echo esc_attr( $this->meta_key ); ?> delete-and-reset"><?php echo esc_html__( 'Delete and replace the app link', 'disciple_tools' ) ?>  <span class="<?php echo esc_attr( $this->meta_key ); ?> loading-spinner"></span></button>`)
                        jQuery('#modal-small').foundation('open')
                        jQuery('.button.<?php echo esc_attr( $this->meta_key ); ?>.delete-and-reset').on('click', function(e){
                            jQuery('.button.<?php echo esc_attr( $this->meta_key ); ?>.delete-and-reset').prop('disable', true)
                            jQuery('.<?php echo esc_attr( $this->meta_key ); ?>.loading-spinner').addClass('active')
                            window.API.update_post('<?php echo esc_attr( $post_type ); ?>', <?php echo esc_attr( get_the_ID() ); ?>, { ['<?php echo esc_attr( $this->meta_key ); ?>']: window.sha256( Date.now() ) })
                                .done( newPost => {
                                    jQuery('#modal-small').foundation('close')
                                    window.app_key['<?php echo esc_attr( $this->meta_key ) ?>'] = newPost['<?php echo esc_attr( $this->meta_key ) ?>']
                                    jQuery('.section-app-links.<?php echo esc_attr( $this->meta_key ); ?> .select-button.button.copy_to_clipboard').data('value', `${window.app_url['<?php echo esc_attr( $this->meta_key ) ?>']}${window.app_key['<?php echo esc_attr( $this->meta_key ) ?>']}`)
                                })
                        })
                    })
                })
            </script>
            <?php
        }
    }

    public function dt_settings_apps_list( $apps_list ) {
        if ( 'user' === $this->post_type ) {
            $apps_list[$this->meta_key] = [
                'key' => $this->meta_key,
                'url_base' => $this->root. '/'. $this->type,
                'label' => $this->page_title,
                'description' => $this->page_description,
            ];
        }
        return $apps_list;
    }

}
