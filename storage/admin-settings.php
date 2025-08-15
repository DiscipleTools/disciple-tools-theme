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
        // Load current single connection
        $current = DT_Storage::get_settings();


        if ( isset( $_POST['dt_storage_settings_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['dt_storage_settings_nonce'] ) ), 'dt_storage_settings' ) ) {

            $post = dt_recursive_sanitize_array( $_POST );
            $enabled = isset( $post['enabled'] ) ? (bool) $post['enabled'] : false;
            $id = $post['id'] ?? '';
            $existing_type = is_array( $current ) ? ( $current['type'] ?? 'aws' ) : 'aws';
            $type = $post['type'] ?? $existing_type;
            $types = ( class_exists( 'DT_Storage' ) && method_exists( 'DT_Storage', 'list_supported_connection_types' ) ) ? DT_Storage::list_supported_connection_types() : [];
            $details = [
                'access_key' => $post['access_key'] ?? '',
                'secret_access_key' => $post['secret_access_key'] == '********' ? '' : $post['secret_access_key'],
                'region' => $post['region'] ?? '',
                'bucket' => $post['bucket'] ?? '',
                'endpoint' => $post['endpoint'] ?? '',
            ];
            if ( empty( $id ) ) {
                $id = substr( md5( maybe_serialize( $details ) ), 0, 12 );
            }
            // store single flat connection (no JSON)
            $default_path_style = isset( $types[$type]['default_path_style'] ) ? (bool) $types[$type]['default_path_style'] : ( $type === 'minio' );
            $path_style = isset( $post['path_style'] ) ? (bool) $post['path_style'] : $default_path_style;
            $obj = [ 'id' => $id, 'enabled' => $enabled, 'name' => 'Default', 'type' => $type, 'path_style' => $path_style ] + $details;
            update_option( 'dt_storage_connection', $obj );
            update_option( 'dt_storage_connection_id', $id );
            echo '<div class="updated"><p>' . esc_html__( 'Saved.', 'disciple_tools' ) . '</p></div>';
        }

        // Load current single connection
        $current = DT_Storage::get_settings();
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
                        <th scope="row">Provider</th>
                        <td>
                            <select name="type">
                                <?php $types = ( class_exists( 'DT_Storage' ) && method_exists( 'DT_Storage', 'list_supported_connection_types' ) ) ? DT_Storage::list_supported_connection_types() : [];
                                $selected_type = $current['type'] ?? 'aws'; foreach ( $types as $key => $meta ) { if ( !empty( $meta['enabled'] ) ) { ?>
                                    <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $selected_type, $key ); ?>><?php echo esc_html( $meta['label'] ?? $key ); ?></option>
                                                                <?php }
                                } ?>
                            </select>
                        </td>
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
                        <td><input type="password" name="secret_access_key" value="<?php echo esc_attr( $current['secret_access_key'] ? '********' : '' ); ?>" class="regular-text" /></td>
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
                    <tr>
                        <th scope="row">Path-style endpoint</th>
                        <td>
                            <?php $types = ( class_exists( 'DT_Storage' ) && method_exists( 'DT_Storage', 'list_supported_connection_types' ) ) ? DT_Storage::list_supported_connection_types() : [];
                            $selected_type = $current['type'] ?? 'aws';
                            $default_path_style = isset( $types[$selected_type]['default_path_style'] ) ? (bool) $types[$selected_type]['default_path_style'] : false;
                            $checked = isset( $current['path_style'] ) ? (bool) $current['path_style'] : $default_path_style; ?>
                            <label><input type="checkbox" name="path_style" value="1" <?php checked( $checked ); ?> /> Use path-style addressing (required by many MinIO setups)</label>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
}

DT_Storage_Admin_Settings::init();


