<?php

class DT_Login_Fields {

    const OPTION_NAME = 'dt_sso_login_fields';
    const MULTISITE_OPTION_NAME = 'dt_sso_login_multisite_fields';

    public static function all() {

        $multisite_defaults = self::get_multisite_defaults();

        $site_defaults = self::get_site_defaults();

        if ( is_multisite() ) {
            $defaults = $site_defaults;
        } else {
            $defaults = array_merge( $site_defaults, $multisite_defaults );
        }

        $fields = self::parse_and_save_fields( $defaults, self::OPTION_NAME );

        if ( is_multisite() ) {
            $firebase_fields = self::parse_and_save_fields( $multisite_defaults, self::MULTISITE_OPTION_NAME, true );

            $fields = array_merge( $fields, $firebase_fields );
        }

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
            $multisite_vars = [];
            $site_vars = [];

            foreach ( $vars as $key => $param ) {
                if ( isset( $param['multisite_level'] ) && $param['multisite_level'] === true ) {
                    $multisite_vars[$key] = $param;
                } else {
                    $site_vars[$key] = $param;
                }
            }

            update_network_option( get_main_network_id(), self::MULTISITE_OPTION_NAME, $multisite_vars );

            update_option( self::OPTION_NAME, $site_vars );
        } else {
            update_option( self::OPTION_NAME, $vars );
        }
    }

    public static function delete() {
        delete_option( self::OPTION_NAME );

        if ( is_multisite() ) {
            delete_network_option( get_main_network_id(), self::MULTISITE_OPTION_NAME );
        }
    }

    private static function parse_and_save_fields( $defaults, $option_name, $multisite_level = false ) {

        $defaults_count = count( $defaults );

        if ( $multisite_level === true ) {
            $saved_fields = get_network_option( get_main_network_id(), $option_name, [] );
        } else {
            $saved_fields = get_option( $option_name, [] );
        }

        $saved_count = count( $saved_fields );

        $fields = wp_parse_args( $saved_fields, $defaults );

        if ( $defaults_count !== $saved_count ) {
            if ( $multisite_level === true ) {
                update_network_option( get_main_network_id(), $option_name, $fields );
            } else {
                update_option( $option_name, $fields );
            }
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
            $users_can_register = apply_filters( 'option_users_can_register', 0 );
        } else {
            $users_can_register = get_option( 'users_can_register' );

            if ( !$users_can_register ) {
                $users_can_register = 0;
            }
        }

        $users_can_register = $users_can_register !== 0;

        return $users_can_register;
    }

    private static function get_multisite_defaults() {
        $multisite_defaults = [
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
        ];

        return $multisite_defaults;
    }

    private static function get_site_defaults() {
        $site_defaults = [
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
                'label' => 'Redirect URL',
                'description' => '(when someone successfully logs in, where do they get redirected)',
                'value' => '',
                'type' => 'text',
            ],
            'login_url' => [
                'tab' => 'general',
                'key' => 'login_url',
                'label' => 'Login URL',
                'description' => '',
                'value' => 'login',
                'type' => 'text',
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
            ],

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
            ],

        ];

        return $site_defaults;
    }
}