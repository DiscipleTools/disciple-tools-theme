<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

if ( ! wp_next_scheduled( 'error_log_dispatch_email' ) ) {
    wp_schedule_event( time(), 'daily', 'error_log_dispatch_email' );
}
add_action( 'error_log_dispatch_email', 'find_new_error_logs' );

function find_new_error_logs() {
    global $wpdb, $wp;

    // Stop if email dispatch feature if disabled.
    if ( ! boolval( get_option( 'dt_error_log_dispatch_emails' ) ) ) {
        return;
    }

    // Fetch deltas following on from last run.
    $deltas = $wpdb->get_results( $wpdb->prepare( "SELECT hist_time, meta_key, meta_value, object_note FROM $wpdb->dt_activity_log WHERE (action = 'error_log') AND (hist_time > %s) ORDER BY hist_time DESC",
        esc_attr( strtotime( '-24 hour' ) )
    ) );

    $count = count( $deltas );
    if ( $count > 0 ) {

        // Build error logs url.
        $logs_url = home_url( $wp->request ) . '/wp-admin/admin.php?page=dt_utilities&tab=logs';

        // Build and dispatch notification email.
        $email_to      = get_bloginfo( 'admin_email' );
        $email_subject = 'Disciple.Tools: Error Logs Detected';
        $email_body    = build_email_body( $count, $deltas, $logs_url );
        $email_headers = array( 'Content-Type: text/html; charset=UTF-8' );
        wp_mail( $email_to, $email_subject, $email_body, $email_headers );
    }
}

function build_email_body( $count, $deltas, $logs_url ): string {
    $summary = build_email_body_logs_summary( $deltas );

    $dt_logo = get_template_directory_uri() . '/dt-assets/images/disciple-tools-logo-blue.png';

    return <<<HTML
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <title>Disciple.Tools: Error Logs Detected</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>

</head>
<body><br>
  <table style="padding-top: 25px; padding-right: 200px; padding-left: 200px;" border="0" cellspacing="0" align="center">
    <tr>
      <td>

<table align="center" border="0" cellpadding="0" cellspacing="0" style="min-width: 600px; border-collapse: collapse; border: 1px solid #cccccc;">
  <tr>
    <td align="center" bgcolor="#ffffff"><br><br>
      <img alt="Disciple.Tools" style="min-height: 80px; max-height: 80px; min-width: 175px;" src="$dt_logo" />
    </td>
  </tr>
  <tr>
    <td bgcolor="#ffffff">
      <table border="0" cellpadding="20" cellspacing="0" min-width: 100%; border-collapse: collapse;">
        <tr>
          <td style="color: #153643; font-family: Arial, sans-serif;"><br><br>
            <h1 style="font-size: 24px;">$count New Error Logs Detected!</h1>
            <p>$count new error logs have been detected and a summary of exceptions is provided below. A detailed breakdown of recent error logs can be found <a href="$logs_url">here</a>.</p>
          </td>
        </tr>
        <tr>
            <td>
                <table border="0" cellpadding="5" cellspacing="0" style="min-width: 100%; border-collapse: collapse;">
                    <tr>
                        <th style="color: #153643; font-family: Arial, sans-serif; font-size: 14px;">Timestamp</th>
                        <th style="color: #153643; font-family: Arial, sans-serif; font-size: 14px;">Note</th>
                    </tr>
                    $summary
                </table>
            </td>
        </tr>
        <tr>
          <td style="color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height: 24px;">
            <p><a href="$logs_url">Detailed Error Logs</a>.</p>
          </td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td bgcolor="#70bbd9">
        <table border="0" cellpadding="10" cellspacing="0" style="min-width: 100%; border-collapse: collapse;">
        <tr>
          <td style="color: #ffffff; font-family: Arial, sans-serif; font-size: 14px;">
            <p>&reg; 2021 Disciple.Tools<br/></p>
          </td>
          <td align="right">
            <table border="0" cellpadding="0" cellspacing="0" style="border-collapse: collapse;">
              <tr>
                <td></td>
                <td style="font-size: 0; line-height: 0; min-width: 20px;">&nbsp;</td>
                <td></td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>

      </td>
    </tr>
  </table>
</body>
</html>

HTML;
}

function build_email_body_logs_summary( $deltas ): string {
    $summary       = '';
    $summary_limit = 5;
    $summary_count = 0;

    foreach ( $deltas as $delta ) {
        $summary .= '<tr>
                <td style="color: #153643; font-family: Arial, sans-serif; font-size: 14px;">' . esc_attr( gmdate( 'Y-m-d h:i:sa', esc_attr( $delta->hist_time ) ) ) . '</td>
                <td style="color: #153643; font-family: Arial, sans-serif; font-size: 14px;">' . esc_attr( $delta->object_note ) . '</td>
            </tr>';

        if ( $summary_count++ >= $summary_limit ) {
            break;
        }
    }

    return $summary;
}
