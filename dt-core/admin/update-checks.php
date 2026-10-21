<?php
if ( !defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Runs Plugin Update Checker (PUC) checks for the theme and D.T plugins in a background request instead of
 * inline on admin page loads, and fetches their version-control files in parallel.
 *
 * The background request goes to wp-cron.php so every plugin builds its checkers, but runs only the update
 * checks, never other due cron events, so it is safe on sites with WP-Cron disabled.
 *
 * Works through the hooks every bundled PUC copy registers, so the libraries themselves stay unmodified.
 */
// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- PUC's property names.
class Disciple_Tools_Update_Checks {
    const QUEUED_FLAG = 'dt_update_checks_queued';
    const TOKEN_PARAM = 'dt_update_checks';
    const BATCH_SIZE = 5;

    private static $schedulers = null;
    private static $responses = null;
    private static $prefetch_all = false;

    public static function init() {
        add_action( 'admin_init', [ __CLASS__, 'defer_admin_checks' ], 0 );
        add_action( 'load-update-core.php', [ __CLASS__, 'handle_force_check' ], 0 );
        if ( wp_doing_cron() ) {
            add_action( 'wp_loaded', [ __CLASS__, 'run_requested_checks' ], 100 );
            add_filter( 'pre_http_request', [ __CLASS__, 'serve_prefetched' ], 10, 3 );
        }
    }

    /**
     * Removes PUC's inline admin page checks and starts one background request when any check is due.
     */
    public static function defer_admin_checks() {
        $is_force_check = self::is_force_check();
        $any_due = false;
        foreach ( self::get_schedulers() as $scheduler ) {
            foreach ( [ 'admin_init', 'load-update.php', 'load-plugins.php', 'load-themes.php' ] as $hook ) {
                remove_action( $hook, [ $scheduler, 'maybeCheckForUpdates' ] );
            }
            if ( !$is_force_check ) {
                remove_action( 'load-update-core.php', [ $scheduler, 'maybeCheckForUpdates' ] );
            }
            $any_due = $any_due || self::is_due( $scheduler );
        }

        if ( $any_due && !$is_force_check && !get_site_transient( self::QUEUED_FLAG ) ) {
            $token = wp_generate_password( 32, false );
            set_site_transient( self::QUEUED_FLAG, $token, 15 * MINUTE_IN_SECONDS );
            // wp-cron.php exits on any POST body, so the one-time token goes in the query string.
            wp_remote_get( add_query_arg( self::TOKEN_PARAM, $token, site_url( 'wp-cron.php' ) ), [
                'timeout' => 0.01,
                'blocking' => false,
                'sslverify' => apply_filters( 'https_local_ssl_verify', false ),
            ] );
        }
    }

    /**
     * In the background request, runs the update checks and exits before wp-cron.php runs any cron events.
     */
    public static function run_requested_checks() {
        $token = sanitize_text_field( wp_unslash( $_GET[ self::TOKEN_PARAM ] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $queued = get_site_transient( self::QUEUED_FLAG );
        if ( empty( $token ) || !is_string( $queued ) || !hash_equals( $queued, $token ) ) {
            return;
        }
        self::run_due_checks();
        exit;
    }

    /**
     * "Check again" on Dashboard -> Updates keeps PUC's immediate checks, served by one parallel fetch.
     */
    public static function handle_force_check() {
        if ( self::is_force_check() ) {
            self::$prefetch_all = true;
            add_filter( 'pre_http_request', [ __CLASS__, 'serve_prefetched' ], 10, 3 );
        }
    }

    public static function run_due_checks() {
        foreach ( self::get_schedulers() as $scheduler ) {
            $scheduler->maybeCheckForUpdates();
        }
        delete_site_transient( self::QUEUED_FLAG );
    }

    /**
     * On the first PUC metadata request, fetches every due checker's metadata in parallel,
     * then answers each checker's own wp_remote_get() from those responses.
     */
    public static function serve_prefetched( $pre, $args, $url ) {
        if ( false !== $pre || strtoupper( $args['method'] ?? 'GET' ) !== 'GET' ) {
            return $pre;
        }
        $key = strtok( $url, '?' );
        if ( null === self::$responses ) {
            $checkers = self::get_metadata_checkers();
            if ( !isset( $checkers[ $key ] ) ) {
                return $pre;
            }
            $urls = [];
            foreach ( $checkers as $base_url => $entry ) {
                if ( self::$prefetch_all || $base_url === $key || self::is_due( $entry['scheduler'] ) ) {
                    $urls[ $base_url ] = add_query_arg( [
                        'installed_version' => strval( $entry['checker']->getInstalledVersion() ),
                        'php' => phpversion(),
                        'locale' => get_locale(),
                    ], $entry['checker']->metadataUrl );
                }
            }
            self::$responses = self::fetch_parallel( $urls );
        }
        // Several checkers can share one metadata URL, so each response serves every request for it.
        return self::$responses[ $key ] ?? $pre;
    }

    /**
     * @return array<string, array|WP_Error> WP HTTP API style responses keyed like $urls.
     */
    private static function fetch_parallel( $urls ) {
        $responses = [];
        if ( !class_exists( '\WpOrg\Requests\Requests' ) || ( new WP_HTTP_Proxy() )->is_enabled() ) {
            return $responses;
        }
        $http = new WP_Http();
        $urls = array_filter( $urls, function ( $url ) use ( $http ) {
            return !$http->block_request( $url );
        } );
        $options = [
            'timeout' => 10,
            'connect_timeout' => 10,
            'useragent' => 'WordPress/' . get_bloginfo( 'version' ) . '; ' . get_bloginfo( 'url' ),
            'verify' => ABSPATH . WPINC . '/certificates/ca-bundle.crt',
        ];
        foreach ( array_chunk( $urls, self::BATCH_SIZE, true ) as $batch ) {
            $requests = [];
            foreach ( $batch as $key => $url ) {
                $requests[ $key ] = [
                    'url' => $url,
                    'headers' => [ 'Accept' => 'application/json' ],
                ];
            }
            foreach ( \WpOrg\Requests\Requests::request_multiple( $requests, $options ) as $key => $result ) {
                if ( $result instanceof \WpOrg\Requests\Response ) {
                    $wp_response = new WP_HTTP_Requests_Response( $result );
                    $responses[ $key ] = $wp_response->to_array();
                    $responses[ $key ]['http_response'] = $wp_response;
                } else {
                    $responses[ $key ] = new WP_Error( 'http_request_failed', $result->getMessage() );
                }
            }
        }
        return $responses;
    }

    private static function is_force_check() {
        global $pagenow;
        return $pagenow === 'update-core.php' && !empty( $_GET['force-check'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    }

    /**
     * PUC schedulers (v4 and v5) found through the admin_init callback each one registers.
     */
    private static function get_schedulers() {
        global $wp_filter;
        if ( null !== self::$schedulers ) {
            return self::$schedulers;
        }
        self::$schedulers = [];
        if ( empty( $wp_filter['admin_init'] ) ) {
            return self::$schedulers;
        }
        foreach ( $wp_filter['admin_init']->callbacks as $callbacks ) {
            foreach ( $callbacks as $callback ) {
                $function = $callback['function'];
                if ( is_array( $function ) && is_object( $function[0] ) && $function[1] === 'maybeCheckForUpdates' ) {
                    self::$schedulers[] = $function[0];
                }
            }
        }
        return self::$schedulers;
    }

    /**
     * Checkers that fetch a single metadata file, keyed by that file's URL. VCS-based checkers
     * make several dependent API calls and keep their own sequential requests.
     */
    private static function get_metadata_checkers() {
        $checkers = [];
        foreach ( self::get_schedulers() as $scheduler ) {
            $checker = self::read_scheduler( $scheduler, function () {
                return $this->updateChecker;
            } );
            if ( empty( $checker->metadataUrl ) || stripos( get_class( $checker ), 'vcs' ) !== false ) {
                continue;
            }
            $checkers[ strtok( $checker->metadataUrl, '?' ) ] = [
                'scheduler' => $scheduler,
                'checker' => $checker,
            ];
        }
        return $checkers;
    }

    /**
     * Mirrors PUC's own Scheduler::maybeCheckForUpdates() decision, without the check_now filter.
     */
    private static function is_due( $scheduler ) {
        return self::read_scheduler( $scheduler, function () {
            return !empty( $this->checkPeriod )
                && $this->updateChecker->getUpdateState()->timeSinceLastCheck() >= $this->getEffectiveCheckPeriod();
        } );
    }

    private static function read_scheduler( $scheduler, Closure $reader ) {
        return Closure::bind( $reader, $scheduler, get_class( $scheduler ) )();
    }
}
Disciple_Tools_Update_Checks::init();
