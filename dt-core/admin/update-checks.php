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
    const STATE_KEY = 'dt_update_checks_state';
    const TOKEN_PARAM = 'dt_update_checks';
    const BATCH_SIZE = 5;
    const QUEUE_TIMEOUT = 900;
    const COOLDOWN = 3600;
    const INLINE_HOOKS = [ 'admin_init', 'load-update.php', 'load-plugins.php', 'load-themes.php', 'load-update-core.php' ];

    private static $schedulers = null;
    private static $responses = null;
    private static $prefetch_all = false;

    public static function init() {
        add_action( 'admin_init', [ __CLASS__, 'defer_admin_checks' ], 0 );
        add_action( 'load-update-core.php', [ __CLASS__, 'start_force_check' ], 0 );
        add_action( 'load-update-core.php', [ __CLASS__, 'stop_prefetch' ], 11 );
        if ( wp_doing_cron() ) {
            add_action( 'wp_loaded', [ __CLASS__, 'run_requested_checks' ], 100 );
            add_filter( 'pre_http_request', [ __CLASS__, 'serve_prefetched' ], 10, 3 );
        }
    }

    /**
     * Removes PUC's inline admin page checks and starts one background request when any check is due.
     *
     * Checks stay inline when the background request fails to run, and, during the cooldown after a run,
     * for checkers it could not complete (e.g. ones a plugin only builds in admin requests).
     */
    public static function defer_admin_checks() {
        $schedulers = self::get_schedulers();
        $inline = [];

        if ( self::is_force_check() ) {
            foreach ( $schedulers as $scheduler ) {
                foreach ( array_diff( self::INLINE_HOOKS, [ 'load-update-core.php' ] ) as $hook ) {
                    remove_action( $hook, [ $scheduler, 'maybeCheckForUpdates' ] );
                }
            }
            return;
        }

        $due = array_filter( $schedulers, [ __CLASS__, 'is_due' ] );
        if ( !empty( $due ) ) {
            $state = get_site_transient( self::STATE_KEY );
            $status = $state['status'] ?? '';
            $age = time() - ( $state['time'] ?? 0 );

            if ( $status === 'queued' && $age < self::QUEUE_TIMEOUT ) {
                $inline = [];
            } elseif ( $status === 'done' && $age < self::COOLDOWN ) {
                $unhandled = $state['due'] ?? [];
                $inline = array_filter( $due, function ( $scheduler ) use ( $unhandled ) {
                    return in_array( self::get_slug( $scheduler ), $unhandled, true );
                } );
            } elseif ( $status === 'queued' ) {
                dt_write_log( 'Disciple.Tools update checks: the background request did not run, checking inline.' );
                self::set_state( 'done', [] );
                $inline = $due;
            } else {
                $inline = self::queue_background_run( $due ) ? [] : $due;
            }
        }

        foreach ( $schedulers as $scheduler ) {
            if ( in_array( $scheduler, $inline, true ) ) {
                continue;
            }
            foreach ( self::INLINE_HOOKS as $hook ) {
                remove_action( $hook, [ $scheduler, 'maybeCheckForUpdates' ] );
            }
        }
    }

    /**
     * @return bool False when the background request could not be sent.
     */
    private static function queue_background_run( $due ) {
        $token = wp_generate_password( 32, false );
        self::set_state( 'queued', array_map( [ __CLASS__, 'get_slug' ], array_values( $due ) ), $token );

        /** This filter is documented in wp-includes/cron.php */
        $cron_request = apply_filters( 'cron_request', [
            'url' => site_url( 'wp-cron.php' ),
            'key' => $token,
            'args' => [
                'timeout' => 0.01,
                'blocking' => false,
                /** This filter is documented in wp-includes/class-wp-http-streams.php */
                'sslverify' => apply_filters( 'https_local_ssl_verify', false ),
            ],
        ], $token );

        // wp-cron.php exits on any POST body, so the one-time token goes in the query string.
        $result = wp_remote_get( add_query_arg( self::TOKEN_PARAM, $token, $cron_request['url'] ), $cron_request['args'] );
        if ( is_wp_error( $result ) ) {
            dt_write_log( 'Disciple.Tools update checks: background request failed, checking inline. ' . $result->get_error_message() );
            self::set_state( 'done', [] );
            return false;
        }
        return true;
    }

    /**
     * In the background request, runs the update checks and exits before wp-cron.php runs any cron events.
     */
    public static function run_requested_checks() {
        $token = sanitize_text_field( wp_unslash( $_GET[ self::TOKEN_PARAM ] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $state = get_site_transient( self::STATE_KEY );
        if ( empty( $token ) || ( $state['status'] ?? '' ) !== 'queued' || !hash_equals( (string) ( $state['token'] ?? '' ), $token ) ) {
            return;
        }
        foreach ( self::get_schedulers() as $scheduler ) {
            $scheduler->maybeCheckForUpdates();
        }
        self::stop_prefetch();
        self::set_state( 'done', $state['due'] ?? [] );
        exit;
    }

    /**
     * "Check again" on Dashboard -> Updates keeps PUC's immediate checks, served by one parallel fetch.
     */
    public static function start_force_check() {
        if ( self::is_force_check() ) {
            self::$prefetch_all = true;
            add_filter( 'pre_http_request', [ __CLASS__, 'serve_prefetched' ], 10, 3 );
        }
    }

    public static function stop_prefetch() {
        remove_filter( 'pre_http_request', [ __CLASS__, 'serve_prefetched' ], 10 );
        self::$responses = null;
        self::$prefetch_all = false;
    }

    /**
     * On the first PUC metadata request, fetches every due checker's metadata in parallel,
     * then answers each checker's own wp_remote_get() for the identical URL from those responses.
     */
    public static function serve_prefetched( $pre, $args, $url ) {
        if ( false !== $pre || strtoupper( $args['method'] ?? 'GET' ) !== 'GET' ) {
            return $pre;
        }
        if ( null === self::$responses ) {
            $requests = self::get_metadata_requests();
            if ( !isset( $requests[ $url ] ) ) {
                return $pre;
            }
            $selected = array_filter( $requests, function ( $request, $request_url ) use ( $url ) {
                return self::$prefetch_all || $request_url === $url || self::is_due( $request['scheduler'] );
            }, ARRAY_FILTER_USE_BOTH );
            self::$responses = self::fetch_parallel( wp_list_pluck( $selected, 'headers' ) );
        }
        return self::$responses[ $url ] ?? $pre;
    }

    /**
     * The request each metadata-file checker would send, built the same way as PUC's requestMetadata(),
     * keyed by full URL. Checkers whose request options are filtered beyond headers, and VCS-based checkers
     * (several dependent API calls), keep their own requests.
     */
    private static function get_metadata_requests() {
        $requests = [];
        foreach ( self::get_schedulers() as $scheduler ) {
            $checker = self::read_scheduler( $scheduler, function () {
                return $this->updateChecker;
            } );
            if ( empty( $checker->metadataUrl ) || stripos( get_class( $checker ), 'vcs' ) !== false ) {
                continue;
            }
            $is_theme = stripos( get_class( $checker ), 'theme' ) !== false;
            $filter_root = $is_theme ? 'request_update' : 'request_info';

            $query_args = [
                'installed_version' => strval( $checker->getInstalledVersion() ),
                'php' => phpversion(),
                'locale' => get_locale(),
            ];
            if ( !$is_theme ) {
                $query_args['checking_for_updates'] = '1';
            }
            $query_args = apply_filters( $checker->getUniqueName( $filter_root . '_query_args' ), $query_args );

            $options = apply_filters( $checker->getUniqueName( $filter_root . '_options' ), [
                'timeout' => 10,
                'headers' => [ 'Accept' => 'application/json' ],
            ] );
            if ( array_diff( array_keys( (array) $options ), [ 'timeout', 'headers' ] ) ) {
                continue;
            }

            $url = empty( $query_args ) ? $checker->metadataUrl : add_query_arg( $query_args, $checker->metadataUrl );
            $requests[ $url ] = [
                'scheduler' => $scheduler,
                'headers' => (array) ( $options['headers'] ?? [] ),
            ];
        }
        return $requests;
    }

    /**
     * Sends the requests outside WP_Http, so global http_request_args filters don't apply; proxied sites
     * and installs without WpOrg\Requests get no prefetch and PUC makes its own requests.
     *
     * @param array<string, array> $requests Headers keyed by URL.
     * @return array<string, array|WP_Error> WP HTTP API style responses keyed by URL.
     */
    private static function fetch_parallel( $requests ) {
        $responses = [];
        if ( !class_exists( '\WpOrg\Requests\Requests' ) || ( new WP_HTTP_Proxy() )->is_enabled() ) {
            return $responses;
        }
        $http = new WP_Http();
        $options = [
            'timeout' => 10,
            'connect_timeout' => 10,
            'useragent' => 'WordPress/' . get_bloginfo( 'version' ) . '; ' . get_bloginfo( 'url' ),
            'verify' => ABSPATH . WPINC . '/certificates/ca-bundle.crt',
        ];
        $batch = [];
        foreach ( $requests as $url => $headers ) {
            if ( !$http->block_request( $url ) ) {
                $batch[ $url ] = [
                    'url' => $url,
                    'headers' => $headers,
                ];
            }
        }
        foreach ( array_chunk( $batch, self::BATCH_SIZE, true ) as $chunk ) {
            foreach ( \WpOrg\Requests\Requests::request_multiple( $chunk, $options ) as $url => $result ) {
                if ( $result instanceof \WpOrg\Requests\Response ) {
                    $wp_response = new WP_HTTP_Requests_Response( $result );
                    $responses[ $url ] = $wp_response->to_array();
                    $responses[ $url ]['http_response'] = $wp_response;
                } else {
                    $responses[ $url ] = new WP_Error( 'http_request_failed', $result->getMessage() );
                }
            }
        }
        return $responses;
    }

    private static function set_state( $status, $due, $token = '' ) {
        set_site_transient( self::STATE_KEY, [
            'status' => $status,
            'time' => time(),
            'due' => $due,
            'token' => $token,
        ], DAY_IN_SECONDS );
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
        if ( !empty( self::$schedulers ) ) {
            return self::$schedulers;
        }
        $schedulers = [];
        foreach ( isset( $wp_filter['admin_init'] ) ? $wp_filter['admin_init']->callbacks : [] as $callbacks ) {
            foreach ( $callbacks as $callback ) {
                $function = $callback['function'];
                if ( is_array( $function ) && is_object( $function[0] ) && $function[1] === 'maybeCheckForUpdates' ) {
                    $schedulers[] = $function[0];
                }
            }
        }
        self::$schedulers = $schedulers;
        return $schedulers;
    }

    private static function get_slug( $scheduler ) {
        return self::read_scheduler( $scheduler, function () {
            return $this->updateChecker->slug;
        } );
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
