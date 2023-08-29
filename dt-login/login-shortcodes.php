<?php

if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

add_shortcode( 'dt_firebase_login_ui', 'dt_firebase_login_ui' );

/**
 * Output the necessary script and html to render the Firebase Authentication UI
 * @param mixed $atts
 * @return void
 */
function dt_firebase_login_ui( $atts ) {

    $default_lang = 'en';
    $atts = shortcode_atts( [
        'lang_code' => $default_lang,
    ], $atts );

    $lang_code = $atts['lang_code'];

    if ( !in_array( $lang_code, dt_login_firebase_supported_languages() ) ) {
        $lang_code = $default_lang;
    }

    $lang_prefix = '';
    if ( $lang_code !== 'en' ) {
        $lang_prefix = '__' . $lang_code;
    }

    $config = [];
    $config['api_key'] = DT_Login_Fields::get( 'firebase_api_key' );
    $config['project_id'] = DT_Login_Fields::get( 'firebase_project_id' );
    $config['app_id'] = DT_Login_Fields::get( 'firebase_app_id' );
    $config['redirect_url'] = DT_Login_Fields::get( 'redirect_url' );
    $config['ui_smallprint'] = DT_Login_Fields::get( 'ui_smallprint' );
    $config['disable_sign_up_status'] = !DT_Login_Fields::can_users_register();

    $sign_in_options = [];
    $sign_in_options['google'] = DT_Login_Fields::get( 'identity_providers_google' ) === 'on' ? true : false;
    $sign_in_options['facebook'] = DT_Login_Fields::get( 'identity_providers_facebook' ) === 'on' ? true : false;
    $sign_in_options['email'] = DT_Login_Fields::get( 'identity_providers_email' ) === 'on' ? true : false;
    $sign_in_options['github'] = DT_Login_Fields::get( 'identity_providers_github' ) === 'on' ? true : false;
    $sign_in_options['twitter'] = DT_Login_Fields::get( 'identity_providers_twitter' ) === 'on' ? true : false;

    $config['sign_in_options'] = $sign_in_options;

    ?>

    <?php //phpcs:disable ?>
    <script src="https://www.gstatic.com/firebasejs/8.10.1/firebase-app.js"></script>
    <script src="https://www.gstatic.com/firebasejs/8.10.1/firebase-auth.js"></script>
    <script>
        const config = [<?php echo json_encode( $config ) ?>][0]

        const signInOptions = []

        const { google, facebook, email, github, twitter } = config.sign_in_options
        const hasASignInProvider = google || facebook || email || github || twitter

        if (google) {
            signInOptions.push(firebase.auth.GoogleAuthProvider.PROVIDER_ID)
        }
        if (facebook) {
            signInOptions.push(firebase.auth.FacebookAuthProvider.PROVIDER_ID)
        }
        if (email) {
            signInOptions.push({
                provider: firebase.auth.EmailAuthProvider.PROVIDER_ID,
                disableSignUp: {
                    status: config.disable_sign_up_status,
                }
            })
        }
        if (github) {
            signInOptions.push(firebase.auth.GithubAuthProvider.PROVIDER_ID)
        }
        if (twitter) {
            signInOptions.push(firebase.auth.TwitterAuthProvider.PROVIDER_ID)
        }

        const firebaseConfig = {
            apiKey: config.api_key,
            authDomain: `${config.project_id}.firebaseapp.com`,
            projectId: config.project_id,
            appId: config.app_id,
        };

        let firebaseApp
        let auth
        if (hasASignInProvider) {
            try {
                firebaseApp = firebase.initializeApp(firebaseConfig);
                auth = firebaseApp.auth();
            } catch (error) {
                console.log(error)
            }
        }

    </script>
    <script src="https://www.gstatic.com/firebasejs/ui/5.0.0/firebase-ui-auth<?php echo esc_html( $lang_prefix ) ?>.js"></script>
    <link type="text/css" rel="stylesheet" href="https://www.gstatic.com/firebasejs/ui/5.0.0/firebase-ui-auth.css" />

    <style>
        #firebaseui-auth-container .firebaseui-tos {
            display: <?php echo $config['ui_smallprint'] === 'on' ? 'block' : 'none' ?>;
        }
    </style>
    <?php //phpcs:enable ?>

    <script>
        let ui
        if (hasASignInProvider) {
            ui = new firebaseui.auth.AuthUI(firebase.auth());
            showLoader()
        }
        let rest_url = '<?php echo esc_url( rest_url( 'dt/v1' ) ) ?>';

        function showLoader( show = true ) {
            const loaderElement = document.getElementById('loader')

            if (!loaderElement) {
                return
            }
            loaderElement.style.display = show ? 'block' : 'none'
        }
        function signInSuccessWithAuthResult(authResult, redirectUrl) {
            // User successfully signed in.
            // Return type determines whether we continue the redirect automatically
            // or whether we leave that to developer to handle.

            showLoader()

            const user = authResult.user

            if (authResult.additionalUserInfo.isNewUser && authResult.user.emailVerified === false) {
                user.sendEmailVerification()
            }

            fetch( `${rest_url}/session/login`, {
                method: 'POST',
                body: JSON.stringify(authResult)
            })
            .then((result) => result.text())
            .then((json) => {
                const response = JSON.parse(json)

                if ( response.status === 200 ) {
                    const { login_method, jwt } = response.body

                    if ( login_method === 'mobile' ) {
                        if ( !Object.prototype.hasOwnProperty.call( jwt, 'token' ) ) {
                            throw new Error('token missing from response', jwt.error)
                        }

                        const { token } = jwt

                        localStorage.setItem( 'login_token', token )
                        localStorage.setItem( 'login_method', 'mobile' )
                    }


                    window.location = config.redirect_url
                } else {
                    showLoader(false)
                    showErrorMessage(response.message)
                    startUI()
                }
            })
            .catch(console.error)

            return false;
        }
        const uiConfig = {
            callbacks: {
                signInSuccessWithAuthResult,
                uiShown: function() {
                    // The widget is rendered.
                    // Hide the loader.
                    showLoader(false)
                }
            },
            // Will use popup for IDP Providers sign-in flow instead of the default, redirect.
            signInFlow: 'popup',
            signInOptions: signInOptions,
            tosUrl: '/content_app/tos',
            privacyPolicyUrl: '/content_app/privacy'
        }

        if ( !config.api_key || !config.project_id || !config.app_id  ) {
            document.getElementById('loader').style.display = 'none'
            console.error('Missing firebase settings in the admin section')
        } else if (!hasASignInProvider) {
            console.log( 'No sign in provider selected' )
        } else {
            startUI()
        }

        function startUI() {
            ui.start('#firebaseui-auth-container', uiConfig);
        }

        function showErrorMessage(message) {
            const container = document.getElementById('error-message-container')
            container.style.display = 'block'
            container.querySelector('.message').innerHTML = message
            setTimeout(() => {
                jQuery(container).fadeOut()
            }, 4000)
        }

    </script>


    <div id="firebaseui-auth-container"></div>

    <div id="error-message-container" style="display: none; background-color: #F006; padding: 5px 10px">
        <p class="message"></p>
    </div>

    <div id="loader" style="display: none">
        <span class="loading-spinner active"></span>
    </div>

    <?php
}

function dt_login_firebase_supported_languages() {
    return [
        'ar',
        'bg',
        'ca',
        'zh_cn',
        'zh_tw',
        'hr',
        'cs',
        'da',
        'nl',
        'en',
        'en_gb',
        'fa',
        'fil',
        'fi',
        'fr',
        'de',
        'el',
        'iw',
        'hi',
        'hu',
        'id',
        'it',
        'ja',
        'ko',
        'lv',
        'lt',
        'no',
        'pl',
        'pt_br',
        'pt_pt',
        'ro',
        'ru',
        'sr',
        'sk',
        'sl',
        'es',
        'es_419',
        'sv',
        'th',
        'tr',
        'uk',
        'vi',
    ];
}

add_shortcode( 'dt_firebase_logout_script', 'dt_firebase_logout_script' );

/**
 * Output a script to remove the user's auth token, and redirect the user
 * @param mixed $atts
 * @return void
 */
function dt_firebase_logout_script( $atts ) {

    $atts = shortcode_atts( [
        'redirect_to' => '/',
    ], $atts );

    $redirect_to = $atts['redirect_to'];

    ?>

    <script>

        localStorage.removeItem( 'login_token' )
        localStorage.removeItem( 'login_method' )

        location.href = decodeURIComponent( "<?php echo esc_url( $redirect_to ) ?>" )

    </script>

    <?php
}
