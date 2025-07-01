<?php

class DT_Login_Fields {

    const OPTION_NAME = 'dt_sso_login_fields';
    const MULTISITE_OPTION_NAME = 'dt_sso_login_multisite_fields';

    public static function all() {

        $defaults = self::get_defaults();

        [ $site_defaults, $multisite_defaults ] = self::split_site_and_multisite_vars( $defaults );

        if ( is_multisite() ) {
            $site_fields = self::parse_and_save_fields( $site_defaults, self::OPTION_NAME );
            $multisite_fields = self::parse_and_save_fields( $multisite_defaults, self::MULTISITE_OPTION_NAME, true );

            $fields = self::merge_site_and_multisite_vars( $site_fields, $multisite_fields );

            return $fields;
        }

        $fields = self::parse_and_save_fields( $defaults, self::OPTION_NAME );

        return $fields;
    }

    public static function all_values() {
        $fields = self::all();

        $all_values = [];

        foreach ( $fields as $key => $field ) {
            $field_key = $field['key'];

            if ( $field['type'] === 'label' ) {
                continue;
            }

            $all_values[$field_key] = $field['value'];
        }

        return $all_values;
    }

    /**
     * Get the value from the fields array
     * @param string $field_name
     * @return mixed
     */
    public static function get( string $field_name ) {
        $fields = self::all();

        if ( !isset( $fields[$field_name] ) ) {
            return false;
        }

        $value = $fields[$field_name]['value'];

        return $value;
    }

    public static function update( $params ) {
        $vars = self::all();

        foreach ( $params as $key => $param ) {
            if ( isset( $vars[$key]['value'] ) ) {
                $vars[$key]['value'] = $param;
            }
        }

        if ( is_multisite() ) {

            [ $site_vars, $multisite_vars ] = self::split_site_and_multisite_vars( $vars );

            $multisite_vars = self::dehydrate_fields( $multisite_vars );
            update_network_option( get_main_network_id(), self::MULTISITE_OPTION_NAME, $multisite_vars );

            $site_vars = self::dehydrate_fields( $vars );
            update_network_option( get_current_blog_id(), self::OPTION_NAME, $site_vars );
        } else {
            $site_vars = self::dehydrate_fields( $vars );
            update_network_option( get_current_blog_id(), self::OPTION_NAME, $site_vars );
        }
    }

    public static function delete() {
        delete_network_option( get_current_blog_id(), self::OPTION_NAME );

        if ( is_multisite() && is_super_admin() ) {
            delete_network_option( get_main_network_id(), self::MULTISITE_OPTION_NAME );
        }
    }

    private static function parse_and_save_fields( $defaults, $option_name, $is_multisite_vars = false ) {

        $defaults_count = count( $defaults );

        if ( $is_multisite_vars === true ) {
            $saved_values = get_network_option( get_main_network_id(), $option_name, [] );
        } else {
            $saved_values = get_network_option( get_current_blog_id(), $option_name, [] );
        }

        $saved_fields = self::hydrate_values( $defaults, $saved_values );
        $saved_fields = self::filter_fields( $defaults, $saved_fields );

        $saved_count = count( $saved_fields );

        if ( $saved_count === 0 ) { // this site hasn't saved these options yet, so copy the main site options

            $saved_values = get_network_option( get_main_network_id(), $option_name, [] );

            $saved_fields = self::hydrate_values( $defaults, $saved_values );
            $saved_fields = self::filter_fields( $defaults, $saved_fields );
        } else if ( $saved_count !== $defaults_count ) {
            $network_values = get_network_option( get_main_network_id(), $option_name, [] );

            $network_fields = self::hydrate_values( $defaults, $network_values );
            $network_fields = self::filter_fields( $defaults, $network_fields );
            $saved_fields = array_merge( $saved_fields, $network_fields );
        }

        $fields = wp_parse_args( $saved_fields, $defaults );

        if ( $saved_count > 0 && $saved_count === $defaults_count ) {
            return $fields;
        }

        $values = self::dehydrate_fields( $fields );
        if ( $is_multisite_vars === true ) {
            update_network_option( get_main_network_id(), $option_name, $values );
        } else {
            update_network_option( get_current_blog_id(), $option_name, $values );
        }

        return $fields;
    }

    /**
     * Get the login method that the Firebase SSO should use
     *
     * @return string
     */
    public static function get_login_method() {

        /**
         * Allows the developer to switch the SSO login mode to JWT
         *
         * Use the DT_Login_Methods class
         *
         * @param string     $login_method The chosen login method. Defaults to 'wordpress'
         */
        return apply_filters( 'dt_login_method', DT_Login_Methods::WORDPRESS );
    }

    /**
     * Can users register on this site/subsite
     *
     * @return bool
     */
    public static function can_users_register() {
        if ( is_multisite() ) {
            return dt_multisite_is_registration_enabled_on_subsite() === 1;
        }

        return dt_is_registration_enabled_on_site();
    }

    private static function filter_fields( $defaults, $fields ) {
        $filtered_fields = [];

        foreach ( $fields as $key => $field ) {
            if ( array_key_exists( $key, $defaults ) ) {
                $filtered_fields[$key] = $field;
            }
        }

        return $filtered_fields;
    }

    private static function hydrate_values( $defaults, $values ) {
        $vars = [];

        foreach ( $values as $key => $param ) {
            if ( isset( $defaults[$key] ) ) {
                $var = $defaults[$key];
                $var['value'] = $param;
                $vars[$key] = $var;
            }
        }

        return $vars;
    }

    private static function dehydrate_fields( $fields ) {
        $values = [];

        foreach ( $fields as $key => $param ) {
            $values[$key] = $param['value'];
        }

        return $values;
    }

    private static function split_site_and_multisite_vars( $vars ) {
        $site_vars = [];
        $multisite_vars = [];

        foreach ( $vars as $key => $param ) {
            $multisite_vars[$key] = $param;
            if ( !isset( $param['multisite_level'] ) || $param['multisite_level'] === false ) {
                $site_vars[$key] = $param;
            }
        }

        return [
            $site_vars,
            $multisite_vars,
        ];
    }

    /**
     * Keep all multisite configurations and add any site specific configurations
     * @param $site_fields
     * @param $multisite_fields
     * @return array
     */
    private static function merge_site_and_multisite_vars( $site_fields, $multisite_fields ) {

        $default_fields = self::get_defaults();

        $merged_fields = [];

        foreach ( $default_fields as $key => $default_field ) {
            if ( isset( $multisite_fields[$key] ) ){
                $merged_fields[$key] = $multisite_fields[$key];
            }
            if ( isset( $site_fields[$key] ) ){
                if ( isset( $merged_fields[$key] ) ) {
                    if ( !empty( $site_fields[$key]['value'] ) ){
                        $merged_fields[$key] = $site_fields[$key];
                    }
                } else {
                    $merged_fields[$key] = $site_fields[$key];
                }
            }
        }

        return $merged_fields;
    }

    private static function get_defaults() {
        $defaults = [
            // firebase
            'firebase_config_label' => [
                'tab' => 'firebase',
                'key' => 'firebase_config_label',
                'label' => 'Where to find the config details',
                'description' => 'Go to your firebase console and in the project settings get the config details from your webapp https://console.firebase.google.com/',
                'description_2' => '',
                'value' => '',
                'type' => 'label',
                'multisite_level' => true,
            ],
            'firebase_api_key' => [
                'tab' => 'firebase',
                'key' => 'firebase_api_key',
                'label' => 'Firebase API Key',
                'description' => '',
                'value' => '',
                'type' => 'text',
                'multisite_level' => true,
            ],
            'firebase_project_id' => [
                'tab' => 'firebase',
                'key' => 'firebase_project_id',
                'label' => 'Firebase Project ID',
                'description' => '',
                'value' => '',
                'type' => 'text',
                'multisite_level' => true,
            ],
            'firebase_app_id' => [
                'tab' => 'firebase',
                'key' => 'firebase_app_id',
                'label' => 'Firebase App ID',
                'description' => '',
                'value' => '',
                'type' => 'text',
                'multisite_level' => true,
            ],

            // general
            'general_label' => [
                'tab' => 'general',
                'key' => 'general_label',
                'label' => 'GENERAL',
                'description' => '',
                'value' => '',
                'type' => 'label',
            ],
            'login_enabled' => [
                'tab' => 'general',
                'key' => 'login_enabled',
                'label' => 'Enable Custom Login Page',
                'description' => '',
                'default' => [
                    'on' => 'on',
                    'off' => 'off',
                ],
                'value' => 'off',
                'type' => 'select',
                'multisite_level' => false,
            ],

            'navigation' => [
                'tab' => 'general',
                'key' => 'navigation',
                'label' => 'Navigation',
                'description' => '',
                'value' => '',
                'type' => 'label',
            ],
            'redirect_url' => [
                'tab' => 'general',
                'key' => 'redirect_url',
                'label' => 'Redirect Path',
                'description' => 'e.g. groups  (when someone successfully logs in, where do they get redirected)',
                'value' => '',
                'type' => 'text',
                'multisite_level' => false,
            ],
            'login_url' => [
                'tab' => 'general',
                'key' => 'login_url',
                'label' => 'Login Path',
                'description' => 'e.g. login',
                'value' => 'login',
                'type' => 'text',
                'multisite_level' => false,
            ],
            'ui_label' => [
                'tab' => 'general',
                'key' => 'ui_label',
                'label' => 'UI',
                'description' => '',
                'value' => '',
                'type' => 'label',
            ],
            'ui_smallprint' => [
                'tab' => 'general',
                'key' => 'ui_smallprint',
                'label' => 'Login UI smallprint',
                'description' => 'Do you want the text with the terms and conditions and privacy policy links to show',
                'value' => 'off',
                'type' => 'select',
                'default' => [
                    'on' => 'on',
                    'off' => 'off',
                ],
                'multisite_level' => false,
            ],
            'tos_url' => [
                'tab' => 'general',
                'key' => 'tos_url',
                'label' => 'Terms of Service Path',
                'description' => 'e.g. terms-of-service',
                'value' => 'terms-of-service',
                'type' => 'text',
                'multisite_level' => false,
            ],
            'privacy_url' => [
                'tab' => 'general',
                'key' => 'privacy_url',
                'label' => 'Privacy Page Path',
                'description' => 'e.g. privacy-policy',
                'value' => 'privacy-policy',
                'type' => 'text',
                'multisite_level' => false,
            ],
            'default_role' => [
                'tab' => 'general',
                'key' => 'default_role',
                'label' => 'Default Role',
                'description' => 'The default role to give a newly registered user',
                'value' => 'multiplier',
                'type' => 'role',
                'multisite_level' => false,
            ],
            'login_length' => [
                'tab' => 'general',
                'key' => 'login_length',
                'label' => 'Login Length',
                'description' => 'How many days should the login token last for',
                'value' => '14',
                'type' => 'text',
                'multisite_level' => false,
            ],

            // shortcodes
            'shortcode_firebase_logon_buttons' => [
                'tab' => 'shortcodes',
                'key' => 'shortcode_firebase_logon_buttons',
                'label' => 'Firebase Logon Buttons',
                'description' => '[dt_firebase_login_ui]',
                'description_2' => '',
                'value' => '',
                'type' => 'label',
            ],

            'shortcode_firebase_logout_script' => [
                'tab' => 'shortcodes',
                'key' => 'shortcode_firebase_logout_script',
                'label' => 'shortcode to add on your logout screen to log the user out if using the mobile login',
                'description' => '[dt_firebase_logout_script]',
                'description_2' => '',
                'value' => '',
                'type' => 'label',
            ],

            // identity_providers
            'identity_providers' => [
                'tab' => 'identity_providers',
                'key' => 'identity_providers_label',
                'label' => 'SSO Identity Providers',
                'description' => 'Choose which identity providers you are using. These also need to be set up in the Firebase project.',
                'value' => '',
                'type' => 'label',
            ],
            'identity_providers_email' => [
                'tab' => 'identity_providers',
                'key' => 'identity_providers_email',
                'label' => 'Email and Password',
                'description' => '',
                'value' => 'off',
                'type' => 'select',
                'default' => [
                    'on' => 'on',
                    'off' => 'off',
                ],
                'multisite_level' => true,
            ],
            'identity_providers_google' => [
                'tab' => 'identity_providers',
                'key' => 'identity_providers_google',
                'label' => 'Google',
                'description' => '',
                'value' => 'on',
                'type' => 'select',
                'default' => [
                    'on' => 'on',
                    'off' => 'off',
                ],
                'multisite_level' => true,
            ],
            'identity_providers_facebook' => [
                'tab' => 'identity_providers',
                'key' => 'identity_providers_facebook',
                'label' => 'Facebook',
                'description' => '',
                'value' => 'off',
                'type' => 'select',
                'default' => [
                    'on' => 'on',
                    'off' => 'off',
                ],
                'multisite_level' => true,
            ],
            'identity_providers_github' => [
                'tab' => 'identity_providers',
                'key' => 'identity_providers_github',
                'label' => 'Github',
                'description' => '',
                'value' => 'off',
                'type' => 'select',
                'default' => [
                    'on' => 'on',
                    'off' => 'off',
                ],
                'multisite_level' => true,
            ],
            'identity_providers_twitter' => [
                'tab' => 'identity_providers',
                'key' => 'identity_providers_twitter',
                'label' => 'Twitter',
                'description' => '',
                'value' => 'off',
                'type' => 'select',
                'default' => [
                    'on' => 'on',
                    'off' => 'off',
                ],
                'multisite_level' => true,
            ],
            'identity_providers_microsoft' => [
                'tab' => 'identity_providers',
                'key' => 'identity_providers_microsoft',
                'label' => 'Microsoft',
                'description' => '',
                'value' => 'off',
                'type' => 'select',
                'default' => [
                    'on' => 'on',
                    'off' => 'off',
                ],
                'multisite_level' => true,
            ],

            // captcha
            'google_captcha' => [
                'tab' => 'google_captcha',
                'key' => 'google_captcha_label',
                'label' => 'Google Captcha Keys',
                'description' => 'Put your google captcha keys here to enable google captcha in the login forms',
                'value' => '',
                'type' => 'label',
            ],
            'google_captcha_client_key' => [
                'tab' => 'google_captcha',
                'key' => 'google_captcha_client_key',
                'label' => 'Google Captcha Client Key',
                'description' => 'This is the key that doesn\'t matter if the public find out about it',
                'value' => '',
                'type' => 'text',
                'multisite_level' => true,
            ],
            'google_captcha_server_secret_key' => [
                'tab' => 'google_captcha',
                'key' => 'google_captcha_server_secret_key',
                'label' => 'Google Captcha Server Secret Key',
                'description' => 'This is the one to keep secret from the public.',
                'value' => '',
                'type' => 'text',
                'multisite_level' => true,
            ],




        ];

        return $defaults;
    }
}
