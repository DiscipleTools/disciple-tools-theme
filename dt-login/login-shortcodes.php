<?php

if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

add_shortcode( 'dt_firebase_login_ui', 'dt_firebase_login_ui' );

/**
 * Output the necessary script and html to render the Firebase Authentication UI
 * @param mixed $attr
 * @return void
 */
function dt_firebase_login_ui( $attr ) {
        $fields = get_option( 'dt_sso_login_fields' );
        $invalid_settings = empty( $fields['firebase_api_key']['value'] ) ||
                            empty( $fields['firebase_project_id']['value'] ) ||
                            empty( $fields['firebase_app_id']['value'] ) ? 1 : 0;

    ?>

    <?php //phpcs:disable ?>
    <script src="https://www.gstatic.com/firebasejs/9.15.0/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.15.0/firebase-auth-compat.js"></script>
    <script>
        const firebaseConfig = {
            apiKey: "<?php echo esc_js( $fields['firebase_api_key']['value'] ) ?>",
            authDomain: "<?php echo esc_js( $fields['firebase_project_id']['value'] ) ?>.firebaseapp.com",
            projectId: "<?php echo esc_js( $fields['firebase_project_id']['value'] ) ?>",
            appId: "<?php echo esc_js( $fields['firebase_app_id']['value'] ) ?>",
        };

        try {
            const firebaseApp = firebase.initializeApp(firebaseConfig);
            const auth = firebaseApp.auth();
        } catch (error) {
            console.log(error)
        }
    </script>
    <script src="https://www.gstatic.com/firebasejs/ui/6.0.1/firebase-ui-auth.js"></script>
    <link type="text/css" rel="stylesheet" href="https://www.gstatic.com/firebasejs/ui/6.0.1/firebase-ui-auth.css" />
    <?php //phpcs:enable ?>

    <script>
        const ui = new firebaseui.auth.AuthUI(firebase.auth());
        function showLoader( show = true ) {
            const loaderElement = document.getElementById('loader')

            loaderElement.style.display = show ? 'block' : 'none'
        }
        const config = {
            callbacks: {
                signInSuccessWithAuthResult: function(authResult, redirectUrl) {
                    // User successfully signed in.
                    // Return type determines whether we continue the redirect automatically
                    // or whether we leave that to developer to handle.
                    //
                    // and can perform the handshake with the PG API to

                    showLoader()

                    const user = authResult.user

                    if (authResult.additionalUserInfo.isNewUser && authResult.user.emailVerified === false) {
                        user.sendEmailVerification()
                    }

                    fetch( `${window.location.origin}/wp-json/dt/v1/session/login`, {
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


                            window.location = '/user_app/profile'
                        } else {
                            throw new Error(response.body)
                        }
                    })
                    .catch(console.error)

                    return false;
                },
                uiShown: function() {
                    // The widget is rendered.
                    // Hide the loader.
                    showLoader(false)
                }
            },
            // Will use popup for IDP Providers sign-in flow instead of the default, redirect.
            signInFlow: 'popup',
            // signInSuccessUrl: 'https://prayer.global',
            signInOptions: [
                firebase.auth.GoogleAuthProvider.PROVIDER_ID,
                firebase.auth.FacebookAuthProvider.PROVIDER_ID,
                firebase.auth.EmailAuthProvider.PROVIDER_ID,
            ],
            tosUrl: '/content_app/tos',
            privacyPolicyUrl: '/content_app/privacy'
        }

        if ( <?php echo esc_js( $invalid_settings ) ?> === 1 ) {
            document.getElementById('loader').style.display = 'none'
            console.error('Missing firebase settings in the admin section')
        } else {
            ui.start('#firebaseui-auth-container', config);
        }

    </script>


    <div id="firebaseui-auth-container"></div>

    <div id="loader">
        <span class="loading-spinner active"></span>
    </div>

    <?php
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
