<?php

/**
 * Disciple Tools
 *
 * @class   Disciple_Tools_Reports_Integrations
 * @version 0.1.0
 * @since   0.1.0
 * @package Disciple_Tools
 *
 */

if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * Class Disciple_Tools_Reports_Integrations
 */
class Disciple_Tools_Reports_Integrations {

    /**
     * Constructor function.
     *
     * @access public
     * @since  0.1.0
     */
    public function __construct() {} // End __construct()

    /**
     * @param $url, the facebook url to query for the next stats
     * @param $since, how far back to go to get stats
     * @param $page_id
     * @return array()
     */
    private static function get_facebook_insights_with_paging( $url, $since, $page_id ){
        $request = wp_remote_get( $url );
        if ( !is_wp_error( $request ) ) {
            $body = wp_remote_retrieve_body( $request );
            $data = json_decode( $body );
            if ( !empty( $data )) {
                if (isset( $data->error )) {
                    return $data->error->message;
                } elseif (isset( $data->data )) {
                    //create reports for each day in the month
                    $first_value = $data->data[0]->values[0];
                    $has_value = isset( $first_value->value );
                    $earliest = date( 'Y-m-d', strtotime( $first_value->end_time ) );

                    if ($since <= $earliest && isset( $data->paging->previous ) && $has_value){
                        $next_page = self::get_facebook_insights_with_paging( $data->paging->previous, $since, $page_id );
                        return array_merge( $data->data, $next_page );
                    } else {
                        return $data->data;
                    }
                }
            }
        }
        return [];
    }

    /**
     * Facebook report data
     * Returns a prepared array for the dt_report_insert()
     *
     * @see    Disciple_Tools_Reports_API
     * @return array
     */
    public static function facebook_prepared_data( $date_of_last_record, $facebook_page ) {
        $date_of_last_record = date( 'Y-m-d', strtotime( $date_of_last_record ) );
        $since = date( 'Y-m-d', strtotime( '-30 days' ) );
        if ($date_of_last_record > $since){
            $since = $date_of_last_record;
        }
        if (isset( $facebook_page->rebuild ) && $facebook_page->rebuild == true){
            $date_of_last_record = date( 'Y-m-d', strtotime( '-10 years' ) );
        }
        $page_reports = [];

        if (isset( $facebook_page->report ) && $facebook_page->report == 1){
            $access_token = $facebook_page->access_token;
            $url = "https://graph.facebook.com/v2.8/" . $facebook_page->id . "/insights?metric=";
            $url .= "page_fans";
            $url .= ",page_engaged_users";
            $url .= ",page_admin_num_posts";
            $url .= "&since=" . $since;
            $url .= "&until=" . date( 'Y-m-d', strtotime( 'tomorrow' ) );
            $url .= "&access_token=" . $access_token;

            $all_page_data = self::get_facebook_insights_with_paging( $url,  $date_of_last_record, $facebook_page->id );

            $month_metrics = [];
            foreach ($all_page_data as $metric){
                if ($metric->name === "page_engaged_users" && $metric->period === "day"){
                    foreach ($metric->values as $day){
                        $month_metrics[ $day->end_time ]['page_engagement'] = isset( $day->value ) ? $day->value : 0;
                    }
                }
                if ($metric->name === "page_fans"){
                    foreach ($metric->values as $day){
                        $month_metrics[ $day->end_time ]['page_likes_count'] = isset( $day->value ) ? $day->value : 0;
                    }
                }
                if ($metric->name === "page_admin_num_posts" && $metric->period === "day"){
                    foreach ($metric->values as $day){
                        $month_metrics[ $day->end_time ]['page_post_count'] = isset( $day->value ) ? $day->value : 0;
                    }
                }
            }
            foreach ($month_metrics as $day => $value){
                array_push(
                    $page_reports, [
                        'report_date' => date( 'Y-m-d h:m:s', strtotime( $day ) ),
                        'report_source' => "Facebook",
                        'report_subsource' => $facebook_page->id,
                        'meta_input' => $value,
                    ]
                );
            }

            if ($facebook_page->rebuild){
                self::disable_rebuild_flag_on_facebook_page( $facebook_page->id );
            }
        }

        return $page_reports;
    }


    /**
     * Update the flag for rebuilding the reports for a page.
     */
    public static function disable_rebuild_flag_on_facebook_page( $page_id ){
        $facebook_pages = get_option( "dt_facebook_pages", [] );
        $facebook_pages[ $page_id ]->rebuild = false;
        update_option( "dt_facebook_pages", $facebook_pages );
    }

    /**
     * Twitter report data
     * Returns a prepared array for the dt_report_insert()
     *
     * @see    Disciple_Tools_Reports_API
     * @return array
     */
    public static function twitter_prepared_data( $date ) {
        $report = [];

        $report[0] = [
            'report_date' => $date,
            'report_source' => 'Twitter',
            'report_subsource' => 'Channel1',
            'meta_input' => [
                'unique_website_visitors' => rand( 0, 100 ),
                'platforms' => rand( 0, 100 ),
                'browsers' => rand( 0, 100 ),
                'average_time' => rand( 0, 100 ),
                'page_visits' => rand( 0, 100 ),
            ]
        ];
        $report[1] = [
            'report_date' => $date,
            'report_source' => 'Twitter',
            'report_subsource' => 'Channel2',
            'meta_input' => [
                'unique_website_visitors' => rand( 0, 100 ),
                'platforms' => rand( 0, 100 ),
                'browsers' => rand( 0, 100 ),
                'average_time' => rand( 0, 100 ),
                'page_visits' => rand( 0, 100 ),
            ]
        ];
        return $report;

    }

    /**
     * Analytics report data
     * Returns a prepared array for the dt_report_insert()
     *
     * @see    Disciple_Tools_Reports_API
     * @return array
     */
    public static function analytics_prepared_data( $last_date_recorded ) {
        $reports = [];

        $website_unique_visits = DT_Ga_Admin::get_report_data( $last_date_recorded );

        foreach ($website_unique_visits as $website => $days){
            foreach ($days as $day) {
                //set report date to the day after the day of the data
                $report_date = strtotime( '+1day', $day['date'] );
                $reports[] = [
                    'report_date' => date( 'Y-m-d h:m:s', $report_date ),
                    'report_source' => 'Analytics',
                    'report_subsource' => $website,
                    'meta_input' => [
                        'unique_website_visitors' => $day['value']
                    ]
                ];
            }
        }

        return $reports;
    }

    /**
     * Adwords report data
     * Returns a prepared array for the dt_report_insert()
     *
     * @see    Disciple_Tools_Reports_API
     * @return array
     */
    public static function adwords_prepared_data( $date ) {
        $report = [];

        $report[0] = [
            'report_date' => $date,
            'report_source' => 'Adwords',
            'report_subsource' => 'Campaign1',
            'meta_input' => [
                'money_spent' => rand( 0, 100 ),
                'conversions' => rand( 0, 100 ),
                'total_clicks' => rand( 0, 100 ),
                'ads_served' => rand( 0, 100 ),
                'average_position' => rand( 0, 100 ),
            ]
        ];
        $report[1] = [
            'report_date' => $date,
            'report_source' => 'Adwords',
            'report_subsource' => 'Campaign2',
            'meta_input' => [
                'money_spent' => rand( 0, 100 ),
                'conversions' => rand( 0, 100 ),
                'total_clicks' => rand( 0, 100 ),
                'ads_served' => rand( 0, 100 ),
                'average_position' => rand( 0, 100 ),
            ]
        ];

        return $report;
    }


    /**
     * Mailchimp report data
     * Returns a prepared array for the dt_report_insert()
     *
     * @see    Disciple_Tools_Reports_API
     * @return array
     */
    public static function mailchimp_prepared_data( $date ) {
        $report = [];

        $report[0] = [
            'report_date' => $date,
            'report_source' => 'Mailchimp',
            'report_subsource' => 'List1',
            'meta_input' => [
                'new_subscribers' => rand( 0, 100 ),
                'campaigns_sent' => rand( 0, 3 ),
                'list_opens' => rand( 0, 5000 ),
                'campaign_opens' => rand( 0, 100 ),
                'subscriber_count' => rand( 5000, 6000 ),
                'opt_ins' => rand( 0, 50 ),
                'opt_outs' => rand( 0, 10 ),
            ]
        ];
        $report[1] = [
            'report_date' => $date,
            'report_source' => 'Mailchimp',
            'report_subsource' => 'List2',
            'meta_input' => [
                'new_subscribers' => rand( 0, 100 ),
                'campaigns_sent' => rand( 0, 3 ),
                'list_opens' => rand( 0, 5000 ),
                'campaign_opens' => rand( 0, 100 ),
                'subscriber_count' => rand( 5000, 6000 ),
                'opt_ins' => rand( 0, 50 ),
                'opt_outs' => rand( 0, 10 ),
            ]
        ];

        return $report;
    }

    /**
     * Youtube report data
     * Returns a prepared array for the dt_report_insert()
     *
     * @see    Disciple_Tools_Reports_API
     * @return array
     */
    public static function youtube_prepared_data( $date ) {
        $report = [];

        $report[0] = [
            'report_date' => $date,
            'report_source' => 'Youtube',
            'report_subsource' => 'Channel1',
            'meta_input' => [
                'total_views' => rand( 100, 500 ),
                'total_likes' => rand( 0, 100 ),
                'total_shares' => rand( 0, 50 ),
                'number_of_videos_posted' => rand( 0, 3 ),
            ]
        ];
        return $report;
    }

}
