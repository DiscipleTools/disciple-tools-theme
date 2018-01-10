<?php

/**
 * Disciple_Tools_Extensions_Tab
 *
 * @class      Disciple_Tools_Extensions_Tab
 * @version    0.1.0
 * @package    Disciple_Tools
 */

if ( !defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class Disciple_Tools_Extensions_Tab
 */
class Disciple_Tools_Extensions_Tab
{
    /**
     * Packages and returns tab page
     *
     * @return void
     */
    public static function content()
    {
        ?>
        <div class="wrap plugin-install-tab-featured">
            <h1 class="wp-heading-inline">Add Extensions</h1>

            <hr class="wp-header-end">

            <h2 class="screen-reader-text">Filter plugins list</h2>

            <p>Extensions expand the capacity of the Disciple Tools system.</p>

            <form id="plugin-filter" method="post">
                <div class="wp-list-table widefat plugin-install">
                    <h2 class="screen-reader-text">Plugins list</h2>
                    <div id="the-list">
                        <div class="plugin-card plugin-card-akismet">
                            <div class="plugin-card-top">
                                <div class="name column-name">
                                    <h3>
                                        <a
                                        href="http://localhost/wp-admin/plugin-install.php?tab=plugin-information&amp;plugin=akismet&amp;TB_iframe=true&amp;width=772&amp;height=906"
                                        class="thickbox open-plugin-details-modal">
                                            Facebook Integration <img
                                            src="https://ps.w.org/akismet/assets/icon-256x256.png?rev=969272"
                                            class="plugin-icon" alt="">
                                        </a>
                                    </h3>
                                </div>
                                <div class="action-links">
                                    <ul class="plugin-action-buttons">
                                        <li><a class="install-now button" data-slug="akismet"
                                               href="http://localhost/wp-admin/update.php?action=install-plugin&amp;plugin=akismet&amp;_wpnonce=2342c97b12"
                                               aria-label="Install Akismet Anti-Spam 4.0.2 now"
                                               data-name="Akismet Anti-Spam 4.0.2">Install Now</a></li>
                                        <li><a
                                            href="http://localhost/wp-admin/plugin-install.php?tab=plugin-information&amp;plugin=akismet&amp;TB_iframe=true&amp;width=772&amp;height=906"
                                            class="thickbox open-plugin-details-modal"
                                            aria-label="More information about Akismet Anti-Spam 4.0.2"
                                            data-title="Akismet Anti-Spam 4.0.2">More Details</a></li>
                                    </ul>
                                </div>
                                <div class="desc column-description">
                                    <p>Akismet checks your comments and contact form submissions against our global
                                        database of spam to protect you and your site from malicious content.</p>
                                    <p class="authors"><cite>By <a href="https://automattic.com/wordpress-plugins/">Automattic</a></cite>
                                    </p>
                                </div>
                            </div>
                            <div class="plugin-card-bottom">
                                <div class="vers column-rating">
                                    <div class="star-rating"><span class="screen-reader-text">5.0 rating based on 846 ratings</span>
                                        <div class="star star-full" aria-hidden="true"></div>
                                        <div class="star star-full" aria-hidden="true"></div>
                                        <div class="star star-full" aria-hidden="true"></div>
                                        <div class="star star-full" aria-hidden="true"></div>
                                        <div class="star star-full" aria-hidden="true"></div>
                                    </div>
                                    <span class="num-ratings" aria-hidden="true">(846)</span>
                                </div>
                                <div class="column-updated">
                                    <strong>Last Updated:</strong> 3 weeks ago
                                </div>
                                <div class="column-downloaded">
                                    1+ Million Active Installations
                                </div>
                                <div class="column-compatibility">
                                    <span class="compatibility-compatible"><strong>Compatible</strong> with your version of WordPress</span>
                                </div>
                            </div>
                        </div>
                        <div class="plugin-card plugin-card-jetpack">
                            <div class="plugin-card-top">
                                <div class="name column-name">
                                    <h3>
                                        <a
                                        href="http://localhost/wp-admin/plugin-install.php?tab=plugin-information&amp;plugin=jetpack&amp;TB_iframe=true&amp;width=772&amp;height=906"
                                        class="thickbox open-plugin-details-modal">
                                            Mailchimp Integration <img
                                            src="https://ps.w.org/jetpack/assets/icon.svg?rev=1791404"
                                            class="plugin-icon" alt="">
                                        </a>
                                    </h3>
                                </div>
                                <div class="action-links">
                                    <ul class="plugin-action-buttons">
                                        <li><a class="install-now button" data-slug="jetpack"
                                               href="http://localhost/wp-admin/update.php?action=install-plugin&amp;plugin=jetpack&amp;_wpnonce=8c59784ce6"
                                               aria-label="Install Jetpack by WordPress.com 5.7 now"
                                               data-name="Jetpack by WordPress.com 5.7">Install Now</a></li>
                                        <li><a
                                            href="http://localhost/wp-admin/plugin-install.php?tab=plugin-information&amp;plugin=jetpack&amp;TB_iframe=true&amp;width=772&amp;height=906"
                                            class="thickbox open-plugin-details-modal"
                                            aria-label="More information about Jetpack by WordPress.com 5.7"
                                            data-title="Jetpack by WordPress.com 5.7">More Details</a></li>
                                    </ul>
                                </div>
                                <div class="desc column-description">
                                    <p>The one plugin you need for stats, related posts, search engine optimization,
                                        social sharing, protection, backups, speed, and email list management.</p>
                                    <p class="authors"><cite>By <a href="https://jetpack.com">Automattic</a></cite></p>
                                </div>
                            </div>
                            <div class="plugin-card-bottom">
                                <div class="vers column-rating">
                                    <div class="star-rating"><span class="screen-reader-text">4.0 rating based on 1,443 ratings</span>
                                        <div class="star star-full" aria-hidden="true"></div>
                                        <div class="star star-full" aria-hidden="true"></div>
                                        <div class="star star-full" aria-hidden="true"></div>
                                        <div class="star star-full" aria-hidden="true"></div>
                                        <div class="star star-empty" aria-hidden="true"></div>
                                    </div>
                                    <span class="num-ratings" aria-hidden="true">(1,443)</span>
                                </div>
                                <div class="column-updated">
                                    <strong>Last Updated:</strong> 1 week ago
                                </div>
                                <div class="column-downloaded">
                                    1+ Million Active Installations
                                </div>
                                <div class="column-compatibility">
                                    <span class="compatibility-compatible"><strong>Compatible</strong> with your version of WordPress</span>
                                </div>
                            </div>
                        </div>
                        <div class="plugin-card plugin-card-wp-super-cache">
                            <div class="plugin-card-top">
                                <div class="name column-name">
                                    <h3>
                                        <a
                                        href="http://localhost/wp-admin/plugin-install.php?tab=plugin-information&amp;plugin=wp-super-cache&amp;TB_iframe=true&amp;width=772&amp;height=906"
                                        class="thickbox open-plugin-details-modal">
                                            Twitter Integration <img
                                            src="https://ps.w.org/wp-super-cache/assets/icon-256x256.png?rev=1095422"
                                            class="plugin-icon" alt="">
                                        </a>
                                    </h3>
                                </div>
                                <div class="action-links">
                                    <ul class="plugin-action-buttons">
                                        <li><a class="install-now button" data-slug="wp-super-cache"
                                               href="http://localhost/wp-admin/update.php?action=install-plugin&amp;plugin=wp-super-cache&amp;_wpnonce=f8fd484cff"
                                               aria-label="Install WP Super Cache 1.5.9 now"
                                               data-name="WP Super Cache 1.5.9">Install Now</a></li>
                                        <li><a
                                            href="http://localhost/wp-admin/plugin-install.php?tab=plugin-information&amp;plugin=wp-super-cache&amp;TB_iframe=true&amp;width=772&amp;height=906"
                                            class="thickbox open-plugin-details-modal"
                                            aria-label="More information about WP Super Cache 1.5.9"
                                            data-title="WP Super Cache 1.5.9">More Details</a></li>
                                    </ul>
                                </div>
                                <div class="desc column-description">
                                    <p>A very fast caching engine for WordPress that produces static html files.</p>
                                    <p class="authors"><cite>By <a href="https://automattic.com/">Automattic</a></cite>
                                    </p>
                                </div>
                            </div>
                            <div class="plugin-card-bottom">
                                <div class="vers column-rating">
                                    <div class="star-rating"><span class="screen-reader-text">4.5 rating based on 1,326 ratings</span>
                                        <div class="star star-full" aria-hidden="true"></div>
                                        <div class="star star-full" aria-hidden="true"></div>
                                        <div class="star star-full" aria-hidden="true"></div>
                                        <div class="star star-full" aria-hidden="true"></div>
                                        <div class="star star-half" aria-hidden="true"></div>
                                    </div>
                                    <span class="num-ratings" aria-hidden="true">(1,326)</span>
                                </div>
                                <div class="column-updated">
                                    <strong>Last Updated:</strong> 4 weeks ago
                                </div>
                                <div class="column-downloaded">
                                    1+ Million Active Installations
                                </div>
                                <div class="column-compatibility">
                                    <span class="compatibility-compatible"><strong>Compatible</strong> with your version of WordPress</span>
                                </div>
                            </div>
                        </div>
                        <div class="plugin-card plugin-card-bbpress">
                            <div class="plugin-card-top">
                                <div class="name column-name">
                                    <h3>
                                        <a
                                        href="http://localhost/wp-admin/plugin-install.php?tab=plugin-information&amp;plugin=bbpress&amp;TB_iframe=true&amp;width=772&amp;height=906"
                                        class="thickbox open-plugin-details-modal">
                                            Webform Integration <img src="https://ps.w.org/bbpress/assets/icon.svg?rev=978290"
                                                         class="plugin-icon" alt="">
                                        </a>
                                    </h3>
                                </div>
                                <div class="action-links">
                                    <ul class="plugin-action-buttons">
                                        <li><a class="install-now button" data-slug="bbpress"
                                               href="http://localhost/wp-admin/update.php?action=install-plugin&amp;plugin=bbpress&amp;_wpnonce=05a7af7c14"
                                               aria-label="Install bbPress 2.5.14 now" data-name="bbPress 2.5.14">Install
                                                Now</a></li>
                                        <li><a
                                            href="http://localhost/wp-admin/plugin-install.php?tab=plugin-information&amp;plugin=bbpress&amp;TB_iframe=true&amp;width=772&amp;height=906"
                                            class="thickbox open-plugin-details-modal"
                                            aria-label="More information about bbPress 2.5.14"
                                            data-title="bbPress 2.5.14">More Details</a></li>
                                    </ul>
                                </div>
                                <div class="desc column-description">
                                    <p>bbPress is forum software, made the WordPress way.</p>
                                    <p class="authors"><cite>By <a href="https://bbpress.org">The bbPress Community</a></cite>
                                    </p>
                                </div>
                            </div>
                            <div class="plugin-card-bottom">
                                <div class="vers column-rating">
                                    <div class="star-rating"><span class="screen-reader-text">4.0 rating based on 331 ratings</span>
                                        <div class="star star-full" aria-hidden="true"></div>
                                        <div class="star star-full" aria-hidden="true"></div>
                                        <div class="star star-full" aria-hidden="true"></div>
                                        <div class="star star-full" aria-hidden="true"></div>
                                        <div class="star star-empty" aria-hidden="true"></div>
                                    </div>
                                    <span class="num-ratings" aria-hidden="true">(331)</span>
                                </div>
                                <div class="column-updated">
                                    <strong>Last Updated:</strong> 3 months ago
                                </div>
                                <div class="column-downloaded">
                                    300,000+ Active Installations
                                </div>
                                <div class="column-compatibility">
                                    <span class="compatibility-untested">Untested with your version of WordPress</span>
                                </div>
                            </div>
                        </div>
                        <div class="plugin-card plugin-card-buddypress">
                            <div class="plugin-card-top">
                                <div class="name column-name">
                                    <h3>
                                        <a
                                        href="http://localhost/wp-admin/plugin-install.php?tab=plugin-information&amp;plugin=buddypress&amp;TB_iframe=true&amp;width=772&amp;height=906"
                                        class="thickbox open-plugin-details-modal">
                                            Extended Reporting/Charts <img src="https://ps.w.org/buddypress/assets/icon.svg?rev=977480"
                                                            class="plugin-icon" alt="">
                                        </a>
                                    </h3>
                                </div>
                                <div class="action-links">
                                    <ul class="plugin-action-buttons">
                                        <li><a class="install-now button" data-slug="buddypress"
                                               href="http://localhost/wp-admin/update.php?action=install-plugin&amp;plugin=buddypress&amp;_wpnonce=fb438996d8"
                                               aria-label="Install BuddyPress 2.9.2 now" data-name="BuddyPress 2.9.2">Install
                                                Now</a></li>
                                        <li><a
                                            href="http://localhost/wp-admin/plugin-install.php?tab=plugin-information&amp;plugin=buddypress&amp;TB_iframe=true&amp;width=772&amp;height=906"
                                            class="thickbox open-plugin-details-modal"
                                            aria-label="More information about BuddyPress 2.9.2"
                                            data-title="BuddyPress 2.9.2">More Details</a></li>
                                    </ul>
                                </div>
                                <div class="desc column-description">
                                    <p>BuddyPress adds community features to WordPress. Member Profiles, Activity
                                        Streams, Direct Messaging, Notifications, and more!</p>
                                    <p class="authors"><cite>By <a href="https://buddypress.org/">The BuddyPress
                                                Community</a></cite></p>
                                </div>
                            </div>
                            <div class="plugin-card-bottom">
                                <div class="vers column-rating">
                                    <div class="star-rating"><span class="screen-reader-text">4.5 rating based on 370 ratings</span>
                                        <div class="star star-full" aria-hidden="true"></div>
                                        <div class="star star-full" aria-hidden="true"></div>
                                        <div class="star star-full" aria-hidden="true"></div>
                                        <div class="star star-full" aria-hidden="true"></div>
                                        <div class="star star-half" aria-hidden="true"></div>
                                    </div>
                                    <span class="num-ratings" aria-hidden="true">(370)</span>
                                </div>
                                <div class="column-updated">
                                    <strong>Last Updated:</strong> 2 months ago
                                </div>
                                <div class="column-downloaded">
                                    200,000+ Active Installations
                                </div>
                                <div class="column-compatibility">
                                    <span class="compatibility-compatible"><strong>Compatible</strong> with your version of WordPress</span>
                                </div>
                            </div>
                        </div>
                        <div class="plugin-card plugin-card-theme-check">
                            <div class="plugin-card-top">
                                <div class="name column-name">
                                    <h3>
                                        <a
                                        href="http://localhost/wp-admin/plugin-install.php?tab=plugin-information&amp;plugin=theme-check&amp;TB_iframe=true&amp;width=772&amp;height=906"
                                        class="thickbox open-plugin-details-modal">
                                            Mobile App <img
                                            src="https://ps.w.org/theme-check/assets/icon-128x128.png?rev=972579"
                                            class="plugin-icon" alt="">
                                        </a>
                                    </h3>
                                </div>
                                <div class="action-links">
                                    <ul class="plugin-action-buttons">
                                        <li><a
                                            href="http://localhost/wp-admin/plugins.php?_wpnonce=bca5a98951&amp;action=activate&amp;plugin=theme-check/theme-check.php"
                                            class="button activate-now" aria-label="Activate Theme Check">Activate</a>
                                        </li>
                                        <li><a
                                            href="http://localhost/wp-admin/plugin-install.php?tab=plugin-information&amp;plugin=theme-check&amp;TB_iframe=true&amp;width=772&amp;height=906"
                                            class="thickbox open-plugin-details-modal"
                                            aria-label="More information about Theme Check 20160523.1"
                                            data-title="Theme Check 20160523.1">More Details</a></li>
                                    </ul>
                                </div>
                                <div class="desc column-description">
                                    <p>A simple and easy way to test your theme for all the latest WordPress
                                        standards…</p>
                                    <p class="authors"><cite>By <a href="http://ottopress.com">Otto42, pross</a></cite>
                                    </p>
                                </div>
                            </div>
                            <div class="plugin-card-bottom">
                                <div class="vers column-rating">
                                    <div class="star-rating"><span class="screen-reader-text">5.0 rating based on 231 ratings</span>
                                        <div class="star star-full" aria-hidden="true"></div>
                                        <div class="star star-full" aria-hidden="true"></div>
                                        <div class="star star-full" aria-hidden="true"></div>
                                        <div class="star star-full" aria-hidden="true"></div>
                                        <div class="star star-full" aria-hidden="true"></div>
                                    </div>
                                    <span class="num-ratings" aria-hidden="true">(231)</span>
                                </div>
                                <div class="column-updated">
                                    <strong>Last Updated:</strong> 1 year ago
                                </div>
                                <div class="column-downloaded">
                                    100,000+ Active Installations
                                </div>
                                <div class="column-compatibility">
                                    <span class="compatibility-untested">Untested with your version of WordPress</span>
                                </div>
                            </div>
                        </div>
                        <div class="plugin-card plugin-card-theme-check">
                            <div class="plugin-card-top">
                                <div class="name column-name">
                                    <h3>
                                        <a
                                        href="http://localhost/wp-admin/plugin-install.php?tab=plugin-information&amp;plugin=theme-check&amp;TB_iframe=true&amp;width=772&amp;height=906"
                                        class="thickbox open-plugin-details-modal">
                                            Content Network <img
                                            src="https://ps.w.org/theme-check/assets/icon-128x128.png?rev=972579"
                                            class="plugin-icon" alt="">
                                        </a>
                                    </h3>
                                </div>
                                <div class="action-links">
                                    <ul class="plugin-action-buttons">
                                        <li><a
                                            href="http://localhost/wp-admin/plugins.php?_wpnonce=bca5a98951&amp;action=activate&amp;plugin=theme-check/theme-check.php"
                                            class="button activate-now" aria-label="Activate Theme Check">Activate</a>
                                        </li>
                                        <li><a
                                            href="http://localhost/wp-admin/plugin-install.php?tab=plugin-information&amp;plugin=theme-check&amp;TB_iframe=true&amp;width=772&amp;height=906"
                                            class="thickbox open-plugin-details-modal"
                                            aria-label="More information about Theme Check 20160523.1"
                                            data-title="Theme Check 20160523.1">More Details</a></li>
                                    </ul>
                                </div>
                                <div class="desc column-description">
                                    <p>A simple and easy way to test your theme for all the latest WordPress
                                        standards…</p>
                                    <p class="authors"><cite>By <a href="http://ottopress.com">Otto42, pross</a></cite>
                                    </p>
                                </div>
                            </div>
                            <div class="plugin-card-bottom">
                                <div class="vers column-rating">
                                    <div class="star-rating"><span class="screen-reader-text">5.0 rating based on 231 ratings</span>
                                        <div class="star star-full" aria-hidden="true"></div>
                                        <div class="star star-full" aria-hidden="true"></div>
                                        <div class="star star-full" aria-hidden="true"></div>
                                        <div class="star star-full" aria-hidden="true"></div>
                                        <div class="star star-full" aria-hidden="true"></div>
                                    </div>
                                    <span class="num-ratings" aria-hidden="true">(231)</span>
                                </div>
                                <div class="column-updated">
                                    <strong>Last Updated:</strong> 1 year ago
                                </div>
                                <div class="column-downloaded">
                                    100,000+ Active Installations
                                </div>
                                <div class="column-compatibility">
                                    <span class="compatibility-untested">Untested with your version of WordPress</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <span class="spinner"></span>
        </div>
        <?php
    }
}
