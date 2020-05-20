<?php

if ( !defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class Disciple_Tools_GDPR_Tab
 */
class Disciple_Tools_GDPR_Tab extends Disciple_Tools_Abstract_Menu_Base
{
    private static $_instance = null;
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()

    /**
     * Constructor function.
     *
     * @access  public
     * @since   0.1.0
     */
    public function __construct() {
        add_action( 'admin_menu', [ $this, 'add_submenu' ], 120 );
        add_action( 'dt_utilities_tab_menu', [ $this, 'add_tab' ], 120, 1 ); // use the priority setting to control load order
        add_action( 'dt_utilities_tab_content', [ $this, 'content' ], 120, 1 );

        parent::__construct();
    } // End __construct()


    public function add_submenu() {
        add_submenu_page( 'dt_utilities', __( 'GDPR', 'disciple_tools' ), __( 'GDPR', 'disciple_tools' ), 'manage_dt', 'dt_utilities&tab=gdpr', [ 'Disciple_Tools_Utilities_Menu', 'content' ] );
    }

    public function add_tab( $tab ) {
        echo '<a href="'. esc_url( admin_url() ).'admin.php?page=dt_utilities&tab=gdpr" class="nav-tab ';
        if ( $tab == 'gdpr' ) {
            echo 'nav-tab-active';
        }
        echo '">'. esc_attr__( 'GDPR', 'disciple_tools' ) .'</a>';
    }

    public function content( $tab ) {
        if ( 'gdpr' == $tab ) {

            // wrapper top
            self::template( 'begin', 2 );

            // box - export
            self::box( 'top', $title = 'Show Personal Data', $args = [] );
            ?>
            <form method="POST">
                <?php wp_nonce_field( 'export' . get_current_user_id(), '_wpnonce', true, true ) ?>
                <input type="text" name="contact_id" class="regular-text" placeholder="Contact ID" /><button class="button" type="submit">Export</button>
            </form>
            <?php
            self::box( 'bottom' );

            // process export post
            if ( isset( $_POST['_wpnonce'] )
                && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'export'.get_current_user_id() )
                && isset( $_POST['_wp_http_referer'] )
                && '/wp-admin/admin.php?page=dt_utilities&tab=gdpr' === sanitize_text_field( wp_unslash( $_POST['_wp_http_referer'] ) )
                && isset( $_POST['contact_id'] )
                && ! empty( $_POST['contact_id'] )
            ) {
                self::box( 'top', $title = 'Personal Data Report', $args = [] );

                $contact_id = sanitize_text_field( wp_unslash( $_POST['contact_id'] ) );
                $contact_references = $this->query_contact_references( $contact_id );

                if ( empty( $contact_references ) ) {
                    ?>No data found.<?php
                } else {
                    ?>Copy and Send the Personal Data Report below.<pre><code><--- BEGIN REPORT ---><br><?php
foreach ( $contact_references as $key => $record ) {
    // title
    echo esc_attr( $key ) . ': ' . esc_html( $record['post']['post_title'] ?? '' );
    echo '<br>';
    echo esc_attr( $key ) . ': ' . esc_html( $record['post']['post_name'] ?? '' );
    echo '<br>';

    // meta
    if ( ! empty( $record['meta'] ) ) {
        foreach ( $record['meta'] as $item ) {
            if ( substr( $item['meta_key'], 0, '7' ) === 'contact' && substr( $item['meta_key'], -7, '7' ) !== 'details' ) {
                echo esc_attr( $item['meta_id'] ) . ': ' . esc_html( $item['meta_value'] ) . ' (meta)<br>';
            }
            if ( $item['meta_key'] === 'location_grid' ) {
                echo esc_attr( $item['meta_id'] ) . ': ' . esc_html( $item['meta_value'] ) . ' (location_grid)<br>';
            }
            if ( $item['meta_key'] === 'location_grid_meta' ) {
                echo esc_attr( $item['meta_id'] ) . ': ' . esc_html( $item['meta_value'] ) . ' (location_grid_meta)<br>';
            }
        }
    }
}
?> <--- END REPORT ---></code></pre><?php
                }

                self::box( 'bottom' );

                // box - erase
                self::box( 'top', $title = 'Erase Personal Data', $args = [] );
                ?>
                <form method="POST">
                    <?php wp_nonce_field( 'erase' . get_current_user_id(), '_wpnonce', true, true )  ?>
                    <input type="email" name="email" class="regular-text" placeholder="Email of Person Requesting Erase" required />
                    <input type="hidden" name="contact_id" value="<?php echo esc_attr( $contact_id ) ?>" /><a class="button" onclick="jQuery('#erase-button').show()">Erase</a><button id="erase-button" class="button" style="display:none; color:red;" type="submit">Are you sure? Cannot be undone.</button>
                </form>
                <?php
                self::box( 'bottom' );

            }

            // export process
            if ( isset( $_POST['_wpnonce'] )
                && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'erase'.get_current_user_id() )
                && isset( $_POST['_wp_http_referer'] )
                && '/wp-admin/admin.php?page=dt_utilities&tab=gdpr' === sanitize_text_field( wp_unslash( $_POST['_wp_http_referer'] ) )
                && isset( $_POST['contact_id'] )
                && ! empty( $_POST['contact_id'] )
                && isset( $_POST['email'] )
                && ! empty( $_POST['email'] )
            ) {

                dt_write_log( 'erase data' );
                dt_write_log( $_POST );

                $contact_id = sanitize_text_field( wp_unslash( $_POST['contact_id'] ) );
                $requester_email = sanitize_email( wp_unslash( $_POST['email'] ) );

                $this->erase_data( $contact_id, $requester_email );
            }

            $logs = get_option( 'dt_gdpr_log' );
            if ( ! empty( $logs ) ) {
                ?><table class="widefat striped">
                <thead><th>Requester</th><th>Erased By Name</th><th>Erased By ID</th><th>Contact ID Erased</th><th>Time Erased</th></thead>
                <tbody><?php
                foreach ( $logs as $log ) {
                    echo '<tr>';
                    echo '<td>'.esc_html( $log['requester'] ).'</td>';
                    echo '<td>'.esc_html( $log['erased_by_name'] ).'</td>';
                    echo '<td>'.esc_html( $log['erased_by_id'] ).'</td>';
                    echo '<td>'.esc_html( $log['contact_id'] ).'</td>';
                    echo '<td>'.esc_html( gmdate( 'Y-m-d H:i:s', $log['time'] ) ).'</td>';
                    echo '</tr>';
                }
                ?></tbody></table><?php
            }

            // wrapper bottom


            self::template( 'right_column', 2 );

            self::box( 'top', $title = 'Instructions', $args = [] );
            ?>
            <p>Get the contact id of the requested contact. You can find this number in the url over the contact record.</p>
            <p>For example: {your url}/contacts/5124. 5124 is the contact id.</p>
            <p>Place this contact id in the export field and this will produce: (1) a GDPR report of personally identifiable data in the system for this contact and (2) the option of erasing this contact.</p>
            <p>For erasing user data, refer to the tools provided by Wordpress in the "Tools" menu called "Export Personal Data" and "Erase Personal Data".</p>
            <?php
            self::box( 'bottom' );

            self::template( 'end', 2 );
        }
    }


    public function erase_data( $contact_id, $requester_email ) {
        global $wpdb;

        // build log
        $log = [
            'requester' => $requester_email,
            'contact_id' => $contact_id,
            'erased_by_id' => get_current_user_id(),
            'erased_by_name' => dt_get_user_display_name( get_current_user_id() ),
            'time' => time(),
        ];
        $options_log = get_option( 'dt_gdpr_log' );
        $options_log[] = $log;
        update_option( 'dt_gdpr_log', $options_log, false );

        // get all ids
        $results = [];
        $record = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->posts WHERE ID = %d", $contact_id ), ARRAY_A );
        if ( empty( $record ) ) {
            return false;
        }
        else {
            $results[$record['ID']] = [
            'post' => [],
            'meta' => [],
            'notifications' => [],
            'activity' => [],
            'comments' => []
            ];
            $results[$record['ID']]['post'] = $record;
        }
        $duplicates = $wpdb->get_results( $wpdb->prepare( "SELECT p.* FROM $wpdb->posts as p WHERE p.ID IN ( SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'duplicate_of' AND meta_value = %d )", $contact_id ), ARRAY_A );
        if ( ! empty( $duplicates ) ) {
            foreach ( $duplicates as $item ) {
                $results[$item['ID']]['post'] = $item;
            }
        }

        foreach ( $results as $id => $item ) {
            // delete extra
            $wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->postmeta WHERE post_id = %d", $id ) );
            $wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->dt_notifications WHERE post_id = %d", $id ) );
            $wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->dt_location_grid_meta WHERE post_id = %d", $id ) );
            $wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->dt_activity_log WHERE object_id = %d", $id ) );
            $wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->comments WHERE comment_post_id = %d", $id ) );
            $wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->p2p WHERE p2p_to = %d AND p2p_type = 'contacts_to_subassigned'", $id ) );

            $key = hash( 'sha256', time() . rand( 0, 100000 ) );
            $key = str_replace( '0', '', $key );
            $key = str_replace( 'O', '', $key );
            $key = str_replace( 'o', '', $key );
            $key = strtoupper( substr( $key, 0, 5 ) );

            // redact
            $wpdb->query( $wpdb->prepare( "UPDATE $wpdb->posts SET post_title = %s WHERE ID = %d", 'REDACTED ' . $key, $id ) );
            $wpdb->query( $wpdb->prepare( "UPDATE $wpdb->posts SET post_name = %s WHERE ID = %d", strtolower( $key ), $id ) );
            $wpdb->insert( $wpdb->postmeta, [
                'post_id' => $id,
                'meta_key' => 'overall_status',
                'meta_value' => 'closed'
            ] );
            $wpdb->insert( $wpdb->postmeta, [
                'post_id' => $id,
                'meta_key' => 'reason_closed',
                'meta_value' => 'gdpr'
            ] );
            $wpdb->insert( $wpdb->postmeta, [
                'post_id' => $id,
                'meta_key' => 'requires_update',
                'meta_value' => ''
            ] );
            $wpdb->insert( $wpdb->postmeta, [
                'post_id' => $id,
                'meta_key' => 'last_modified',
                'meta_value' => time()
            ] );

        }

        return true;
    }

    public function query_contact_references( $contact_id ) {
        global $wpdb;

        $record = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->posts WHERE ID = %d", $contact_id ), ARRAY_A );
        if ( empty( $record ) ) {
            return [];
        }
        else {
            $results[$record['ID']] = [
            'post' => [],
            'meta' => []
            ];
            $results[$record['ID']]['post'] = $record;
        }

        $duplicates = $wpdb->get_results( $wpdb->prepare( "SELECT p.* FROM $wpdb->posts as p WHERE p.ID IN ( SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'duplicate_of' AND meta_value = %d )", $contact_id ), ARRAY_A );
        if ( ! empty( $duplicates ) ) {
            foreach ( $duplicates as $item ) {
                $results[$item['ID']]['post'] = $item;
            }
        }

        foreach ( $results as $key => $item ) {
            $results[$key]['meta'] = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->postmeta WHERE post_id = %d ", $key ), ARRAY_A );
        }

        return $results;
    }

}
Disciple_Tools_GDPR_Tab::instance();
