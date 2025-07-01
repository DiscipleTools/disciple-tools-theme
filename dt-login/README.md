# DT Login (beta) Docs

## For users

If you would like to experiment with the custom login page in core, navigate to

`/wp-admin/admin.php?page=dt_options&tab=sso-login`

Here you can turn on the custom login page by changing `enable custom login page` to be `on`.

This will replace the current WP login page with a new one.

You can choose the login page name, and the page to redirect the user to after they have logged in successfully.

You can also optionally turn off the small print below the SSO options.

### Single Sign On (SSO)

On the Identity Providers tab you can turn on various social logins, such as facebook, github, google etc.

But to use these you first need to set up a firebase project.

### Setting up a Firebase Project

1. Login to firebase console `https://console.firebase.google.com/u/0/` with a gmail account
2. Click Add new project
	1. choose Name and whether or not to include google analytics
	2. Choose the analytics account if you have one
 3. Click on the </> button to get the config details for a web app
 4. Give the app a nickname and click go
 5. You should now see some code that includes some config details. Copy and paste the `apiKey`, `projectId` and `appId` into the necessary input boxes on the Firebase tab back at `/wp-admin/admin.php?page=dt_options&tab=sso-login&sub_tab=firebase`
 6. Back in the Firebase Console... click on build in the sidebar and then authentication.
 7. Click on Get started.
 8. Click on and enable any of the providers that you want to use
 9. Under the settings tab in the Authentication section of Firbase Console, add your domain to the `Authorized Domains` section

Note that currently the DT SSO login section can only deal with

* Email/Password
* Google
* Facebook
* Twitter
* Github

Google and Email/Password are the most straight forward to setup as they will work out of the box.

For the other providers you will have to follow the instructions for each of the sites to connect them to Firebase, e.g. on facebook you will need to create a Developer account and register a new app in order to get the `App ID` and `App secret` that allows facebook to verify users on this site.

## For developers

The DT SSO Login offers 2 modes of logging in.

The default method is the normal WP/PHP way of logging in using cookies in the browser.

If you need to use the login within a headless setup, e.g. using DT as a remote authentication server for an app, then you can switch the login method to JWT mode, by using the hook `dt_login_method`

```php
add_filter( 'dt_login_method', function () {
    return DT_Login_Methods::MOBILE;
} );
```

The SSO endpoints in DT will return a JWT token that will enable your app to validate any API requests, by including the JWT token as a Bearer token in the `Authentication` header of any API requests

e.g.

`Authentication: Bearer your-bearer-token-here`