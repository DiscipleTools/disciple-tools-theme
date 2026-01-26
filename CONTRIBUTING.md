Thank you for joining us in contributing to Disciple.Tools! These are the guidelines we expect you to follow in writing code that will be used in or with D.T.

### Translations
D.T  is already being used in multiple languages. Please help us make D.T translable by taking  full advantage of Wordpress’ translatable strings. Any string that will be read by the user must be marked as translatable. Ex:
`<label class="section-header"><?php esc_html_e( 'Other', 'disciple_tools' )?></label>`

Make sure you look for these in PHP, HTML and JavaScript code.

### PHPCS
We use [PHPCS](https://github.com/squizlabs/PHP_CodeSniffer) and [PHPCS WordPress Coding Standards](https://github.com/WordPress-Coding-Standards/WordPress-Coding-Standards) to test for syntax errors, security vulnerabilities and some styling rules. We expect your commits to pass these tests.

In the theme you can run `./tests/test_phpcs.sh` or create a pull request to our repo and Github Actions CI will run the tests for you.

If you are working on a plugin based off our starter plugin run `./includes/admin/test/test_phpcs.sh`

You might need to run `composer install` first.

Note: rules for PHPCS are located in the `phpcs.xml` file. We sometimes update the rule list as PHPCS updates. We’ll update the [starter plugin](https://github.com/DiscipleTools/disciple-tools-starter-plugin) `phpcs.xml`, you might want to look there to get the latest version.

### GitHub and Commits
For new plugins copy our [starter plugin](https://github.com/DiscipleTools/disciple-tools-starter-plugin).

To commit to the theme or an existing plugin start by creating a fork of the repository. When you are ready, create a pull request into our repo.

Note: Depending on your context you may wish to use an anonymous GitHub account.

### `WP_DEBUG`
Enable `WP_DEBUG` in your `wp-config.php`: `define('WP_DEBUG', true);`
Checking out a PR and seeing the orange debug table is disappointing.

We look forward to hearing from you!

### Styles & CSS
We use [SASS](https://sass-lang.com/) for styling, pulling in JS and SCSS components
from foundation-sites. The build process is handled by Vite, which compiles and minifies the assets for production.

When developing styles locally, you can run `npm run dev` to start the Vite dev server.
This will watch for changes to the SCSS files and automatically recompile them and instantly
load them in your browser. To do that, it will run a local server on port 5173 using a generated SSL.
On initial load from your DT site, it will reject the SSL certificate. You must go to https://localhost:5173
and click through the prompts to proceed anyway.

When the dev server is running, you will need to make sure you copy the `vendor` directory to your
development site along with the `vite-dev-server.json` file that is generated in `dt-assets/build`.
Removing that json file (which happens when running `npm run build`) will revert the theme to using the production assets.

### Web Components
When working on updates to the web components package and wanting to test them here in the theme, you can use NPM Link to streamline local testing.

See https://medium.com/dailyjs/how-to-use-npm-link-7375b6219557 for a good overview of how NPM Link works.

To have your local changes to the web components be available in your local theme, do the following:

```
cd /path/to/disciple-tools-web-components
npm link

cd /path/to/disciple-tools-theme
npm link @disciple.tools/web-components
```

After doing this, the `/node_modules/@disciple.tools/web-components/` directory will be a symlink to your local web components directory, so any updates you do will be immediately available here in the theme.

After changes to your local web components, you just need to run the `npm run build` command (first in the web components directory, then here in the theme) to package them into the script deployed with the theme.

When you've finished testing local changes and want to go back to the production NPM package, execute the following to re-install the production NPM package.

```
npm unlink --no-save @disciple.tools/web-components && npm i
```
