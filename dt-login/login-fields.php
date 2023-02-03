<?php

class DT_Login_Fields {
    public static function all() {
        $defaults = [

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


            // firebase
            'firebase_config_label' => [
                'tab' => 'firebase',
                'key' => 'firebase_config_label',
                'label' => 'Where to find the config details',
                'description' => 'Go to your firebase console and in the project settings get the config details from your webapp https://console.firebase.google.com/',
                'description_2' => '',
                'value' => '',
                'type' => 'label',
            ],
            'firebase_api_key' => [
                'tab' => 'firebase',
                'key' => 'firebase_api_key',
                'label' => 'Firebase API Key',
                'description' => '',
                'value' => '',
                'type' => 'text',
            ],
            'firebase_project_id' => [
                'tab' => 'firebase',
                'key' => 'firebase_project_id',
                'label' => 'Firebase Project ID',
                'description' => '',
                'value' => '',
                'type' => 'text',
            ],
            'firebase_app_id' => [
                'tab' => 'firebase',
                'key' => 'firebase_app_id',
                'label' => 'Firebase App ID',
                'description' => '',
                'value' => '',
                'type' => 'text',
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
            ],
            'identity_providers_apple' => [
                'tab' => 'identity_providers',
                'key' => 'identity_providers_apple',
                'label' => 'Apple',
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

        $defaults_count = count( $defaults );

        $saved_fields = get_option( 'dt_sso_login_fields', [] );
        $saved_count = count( $saved_fields );

        $fields = wp_parse_args( $saved_fields, $defaults );

        if ( $defaults_count !== $saved_count ) {
            update_option( 'dt_sso_login_fields', $fields, true );
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
}