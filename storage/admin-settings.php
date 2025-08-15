<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class DT_Storage_Admin_Settings {

    public static function init(){
        add_action( 'admin_menu', [ __CLASS__, 'menu' ] );
        add_action( 'admin_init', [ __CLASS__, 'maybe_migrate_settings' ] );
    }

    public static function menu(){
        add_submenu_page(
            'dt_utilities',
            __( 'Storage', 'disciple_tools' ),
            __( 'Storage', 'disciple_tools' ),
            'manage_options',
            'disciple_tools_storage',
            [ __CLASS__, 'render' ]
        );
    }

    public static function maybe_migrate_settings(){
        $connection_id = get_option( 'dt_storage_connection_id' );
        $connection = get_option( 'dt_storage_connection', [] );
        if ( empty( $connection_id ) || !empty( $connection ) ) {
            return;
        }
        $objects = get_option( 'dt_storage_connection_objects', [] );
        // If an id is set, reduce the list to just that configuration and save as an array (no JSON encoding)
        if ( !empty( $connection_id ) ) {
            $decoded = [];
            if ( is_string( $objects ) && !empty( $objects ) ) {
                $maybe = json_decode( $objects, true );
                if ( is_array( $maybe ) ) { $decoded = $maybe; }
            } elseif ( is_array( $objects ) ) {
                $decoded = $objects;
            }
            // If decoded is an associative map (old format), convert to list
            if ( !empty( $decoded ) && array_values( $decoded ) !== $decoded ) {
                $decoded = array_values( $decoded );
            }
            // Find the selected connection and persist only that one
            if ( is_array( $decoded ) ) {
                $selected = [];
                foreach ( $decoded as $item ) {
                    if ( isset( $item['id'] ) && $item['id'] === $connection_id ) {
                        $selected = $item;
                        break;
                    }
                }
                if ( !empty( $selected ) ) {
                    // Flatten provider-specific details into the root of the connection array
                    $flat = $selected;
                    $provider = isset( $selected['type'] ) ? $selected['type'] : '';
                    if ( !empty( $provider ) && isset( $selected[ $provider ] ) && is_array( $selected[ $provider ] ) ) {
                        $details = $selected[ $provider ];
                        unset( $flat['aws'], $flat['backblaze'], $flat['minio'] );
                        $flat = array_merge( $flat, $details );
                    }
                    update_option( 'dt_storage_connection', $flat );
                }
            }
        }
    }

    public static function render(){
        if ( isset( $_POST['dt_storage_settings_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['dt_storage_settings_nonce'] ) ), 'dt_storage_settings' ) ) {
            $enabled = isset( $_POST['enabled'] ) ? (bool) $_POST['enabled'] : false;
            $id = sanitize_text_field( wp_unslash( $_POST['id'] ?? '' ) );
            $type = 'aws';
            $details = [
                'access_key' => sanitize_text_field( wp_unslash( $_POST['access_key'] ?? '' ) ),
                'secret_access_key' => sanitize_text_field( wp_unslash( $_POST['secret_access_key'] ?? '' ) ),
                'region' => sanitize_text_field( wp_unslash( $_POST['region'] ?? '' ) ),
                'bucket' => sanitize_text_field( wp_unslash( $_POST['bucket'] ?? '' ) ),
                'endpoint' => sanitize_text_field( wp_unslash( $_POST['endpoint'] ?? '' ) ),
            ];
            if ( empty( $id ) ) {
                $id = substr( md5( maybe_serialize( $details ) ), 0, 12 );
            }
            // store single flat connection (no JSON)
            $obj = [ 'id' => $id, 'enabled' => $enabled, 'name' => 'Default', 'type' => $type ] + $details;
            update_option( 'dt_storage_connection', $obj );
            update_option( 'dt_storage_connection_id', $id );
            echo '<div class="updated"><p>' . esc_html__( 'Saved.', 'disciple_tools' ) . '</p></div>';
        }

        // Load current single connection
        $current = get_option( 'dt_storage_connection', [] );
        if ( is_string( $current ) ) {
            $maybe = json_decode( $current, true );
            if ( is_array( $maybe ) ) { $current = $maybe; }
        }
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Storage', 'disciple_tools' ); ?></h1>
            <form method="post">
                <?php wp_nonce_field( 'dt_storage_settings', 'dt_storage_settings_nonce' ); ?>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Enabled', 'disciple_tools' ); ?></th>
                        <td><label><input type="checkbox" name="enabled" value="1" <?php checked( !empty( $current['enabled'] ) ); ?> /> <?php esc_html_e( 'Enable storage', 'disciple_tools' ); ?></label></td>
                    </tr>
                    <tr>
                        <th scope="row">ID</th>
                        <td><input type="text" name="id" value="<?php echo esc_attr( $current['id'] ?? '' ); ?>" class="regular-text" /></td>
                    </tr>
                    <tr>
                        <th scope="row">Access Key</th>
                        <td><input type="text" name="access_key" value="<?php echo esc_attr( $current['access_key'] ?? '' ); ?>" class="regular-text" /></td>
                    </tr>
                    <tr>
                        <th scope="row">Secret</th>
                        <td><input type="password" name="secret_access_key" value="<?php echo esc_attr( $current['secret_access_key'] ?? '' ); ?>" class="regular-text" /></td>
                    </tr>
                    <tr>
                        <th scope="row">Region</th>
                        <td><input type="text" name="region" value="<?php echo esc_attr( $current['region'] ?? '' ); ?>" class="regular-text" /></td>
                    </tr>
                    <tr>
                        <th scope="row">Bucket</th>
                        <td><input type="text" name="bucket" value="<?php echo esc_attr( $current['bucket'] ?? '' ); ?>" class="regular-text" /></td>
                    </tr>
                    <tr>
                        <th scope="row">Endpoint</th>
                        <td><input type="text" name="endpoint" value="<?php echo esc_attr( $current['endpoint'] ?? '' ); ?>" class="regular-text" placeholder="https://s3.amazonaws.com" /></td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
}

DT_Storage_Admin_Settings::init();


