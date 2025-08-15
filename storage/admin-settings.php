<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class DT_Storage_Admin_Settings {

    public static function init(){
        add_action( 'admin_menu', [ __CLASS__, 'menu' ], 200 );
        add_action( 'admin_init', [ __CLASS__, 'maybe_migrate_settings' ] );
        // Register Storage tab in dt_options
        add_action( 'dt_settings_tab_menu', [ __CLASS__, 'add_tab' ], 50, 1 );
        add_action( 'dt_settings_tab_content', [ __CLASS__, 'render_tab' ], 50, 1 );
    }

    public static function menu(){
        add_submenu_page(
            'dt_options',
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

    private static function process_form_and_get_current(){
        $current = DT_Storage::get_settings();
        if ( isset( $_POST['dt_storage_settings_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['dt_storage_settings_nonce'] ) ), 'dt_storage_settings' ) ) {

            $post = dt_recursive_sanitize_array( $_POST );
            $enabled = isset( $post['enabled'] ) ? (bool) $post['enabled'] : false;
            $id = $post['id'] ?? '';
            $existing_type = is_array( $current ) ? ( $current['type'] ?? 'aws' ) : 'aws';
            $type = $post['type'] ?? $existing_type;
            $types = DT_Storage::list_supported_connection_types();
            $details = [
                'access_key' => $post['access_key'] ?? '',
                'secret_access_key' => $post['secret_access_key'] == '********' ? $current['secret_access_key'] ?? '' : $post['secret_access_key'],
                'region' => $post['region'] ?? '',
                'bucket' => $post['bucket'] ?? '',
                'endpoint' => $post['endpoint'] ?? '',
            ];
            if ( empty( $id ) ) {
                $id = substr( md5( maybe_serialize( $details ) ), 0, 12 );
            }
            $default_path_style = isset( $types[$type]['default_path_style'] ) ? (bool) $types[$type]['default_path_style'] : ( $type === 'minio' );
            $path_style = isset( $post['path_style'] ) ? (bool) $post['path_style'] : $default_path_style;
            $obj = [ 'id' => $id, 'enabled' => $enabled, 'name' => 'Default', 'type' => $type, 'path_style' => $path_style ] + $details;
            update_option( 'dt_storage_connection', $obj );
            echo '<div class="updated"><p>' . esc_html__( 'Saved.', 'disciple_tools' ) . '</p></div>';
        }
        return DT_Storage::get_settings();
    }

    private static function render_form( $current ){
        ?>
        <form method="post">
            <?php wp_nonce_field( 'dt_storage_settings', 'dt_storage_settings_nonce' ); ?>
            <table class="widefat striped">
                <tbody>
                <tr>
                    <td style="width: 220px;"><strong><?php esc_html_e( 'Enabled', 'disciple_tools' ); ?></strong></td>
                    <td><label><input type="checkbox" name="enabled" value="1" <?php checked( !empty( $current['enabled'] ) ); ?> /> <?php esc_html_e( 'Enable storage', 'disciple_tools' ); ?></label></td>
                </tr>
                <tr>
                    <td><strong><?php esc_html_e( 'Provider', 'disciple_tools' ); ?></strong></td>
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
                    <td><strong><?php esc_html_e( 'ID', 'disciple_tools' ); ?></strong></td>
                    <td><input type="text" name="id" value="<?php echo esc_attr( $current['id'] ?? '' ); ?>" class="regular-text" /></td>
                </tr>
                <tr>
                    <td><strong><?php esc_html_e( 'Access Key', 'disciple_tools' ); ?></strong></td>
                    <td><input type="text" name="access_key" value="<?php echo esc_attr( $current['access_key'] ?? '' ); ?>" class="regular-text" /></td>
                </tr>
                <tr>
                    <td><strong><?php esc_html_e( 'Secret', 'disciple_tools' ); ?></strong></td>
                    <td><input type="password" name="secret_access_key" value="<?php echo esc_attr( $current['secret_access_key'] ? '********' : '' ); ?>" class="regular-text" /></td>
                </tr>
                <tr>
                    <td><strong><?php esc_html_e( 'Region', 'disciple_tools' ); ?></strong></td>
                    <td><input type="text" name="region" value="<?php echo esc_attr( $current['region'] ?? '' ); ?>" class="regular-text" /></td>
                </tr>
                <tr>
                    <td><strong><?php esc_html_e( 'Bucket', 'disciple_tools' ); ?></strong></td>
                    <td><input type="text" name="bucket" value="<?php echo esc_attr( $current['bucket'] ?? '' ); ?>" class="regular-text" /></td>
                </tr>
                <tr>
                    <td><strong><?php esc_html_e( 'Endpoint', 'disciple_tools' ); ?></strong></td>
                    <td><input type="text" name="endpoint" value="<?php echo esc_attr( $current['endpoint'] ?? '' ); ?>" class="regular-text" placeholder="https://s3.amazonaws.com" /></td>
                </tr>
                <tr>
                    <td><strong><?php esc_html_e( 'Path-style endpoint', 'disciple_tools' ); ?></strong></td>
                    <td>
                        <?php $types = ( class_exists( 'DT_Storage' ) && method_exists( 'DT_Storage', 'list_supported_connection_types' ) ) ? DT_Storage::list_supported_connection_types() : [];
                        $selected_type = $current['type'] ?? 'aws';
                        $default_path_style = isset( $types[$selected_type]['default_path_style'] ) ? (bool) $types[$selected_type]['default_path_style'] : false;
                        $checked = isset( $current['path_style'] ) ? (bool) $current['path_style'] : $default_path_style; ?>
                        <label><input type="checkbox" name="path_style" value="1" <?php checked( $checked ); ?> /> <?php esc_html_e( 'Use path-style addressing (required by many MinIO setups)', 'disciple_tools' ); ?></label>
                    </td>
                </tr>
                </tbody>
            </table>
            <br>
            <span style="float:right;"><button type="submit" class="button float-right"><?php esc_html_e( 'Save', 'disciple_tools' ) ?></button> </span>
        </form>
        <?php
    }

    public static function render(){
        $current = self::process_form_and_get_current();
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Storage', 'disciple_tools' ); ?></h1>
            <?php self::render_form( $current ); ?>
        </div>
        <?php
    }

    public static function add_tab( $tab ){
        echo '<a href="' . esc_url( admin_url() ) . 'admin.php?page=dt_options&tab=storage" class="nav-tab ' . ( ( $tab === 'storage' ) ? 'nav-tab-active' : '' ) . '">';
        echo esc_html__( 'Storage', 'disciple_tools' );
        echo '</a>';
    }

    public static function render_tab( $tab ){
        if ( 'storage' !== $tab ) {
            return;
        }
        if ( !class_exists( 'Disciple_Tools_Abstract_Menu_Base' ) ) {
            // Fallback to simple render if base class not loaded for any reason
            self::render();
            return;
        }
        // Use the common dt_options layout
        $base = new class() extends Disciple_Tools_Abstract_Menu_Base {};
        $base->template( 'begin', 1 );
        $base->box( 'top', 'Storage Settings' );
        $current = self::process_form_and_get_current();
        self::render_form( $current );
        $base->box( 'bottom' );
        $base->template( 'end' );
    }
}

DT_Storage_Admin_Settings::init();


