<?php

class DT_Login_Fields {

    const OPTION_NAME = 'dt_sso_login_fields';
    const MULTISITE_OPTION_NAME = 'dt_sso_firebase_config';

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

            update_site_option( self::MULTISITE_OPTION_NAME, $multisite_vars );

            update_option( self::OPTION_NAME, $site_vars );
        } else {
            update_option( self::OPTION_NAME, $vars );
        }
    }

    public static function delete() {
        delete_option( self::OPTION_NAME );

        if ( is_multisite() ) {
            delete_site_option( self::MULTISITE_OPTION_NAME );
        }
    }

    private static function parse_and_save_fields( $defaults, $option_name, $multisite_level = false ) {

        $get_option = $multisite_level ? 'get_site_option' : 'get_option';
        $update_option = $multisite_level ? 'update_site_option' : 'update_option';

        $defaults_count = count( $defaults );

        $saved_fields = $get_option( $option_name, [] );
        $saved_count = count( $saved_fields );

        $fields = wp_parse_args( $saved_fields, $defaults );

        if ( $defaults_count !== $saved_count ) {
            $update_option( $option_name, $fields );
        }

        return $fields;
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
            'login_method' => [
                'tab' => 'general',
                'key' => 'login_method',
                'label' => 'Login Method',
                'description' => 'Login like Wordpress normally does or like a mobile app.',
                'default' => [
                    'wordpress' => DT_Login_Methods::WORDPRESS,
                    'mobile' => DT_Login_Methods::MOBILE,
                ],
                'value' => 'wordpress',
                'type' => 'select',
            ],
            'login_redirect_to' => [
                'tab' => 'general',
                'key' => 'login_redirect_to',
                'label' => 'Login Redirect',
                'description' => 'Url to redirect the user to after successful login',
                'value' => '/',
                'type' => 'text',
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
                'value' => 'on',
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
                'value' => 'off',
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