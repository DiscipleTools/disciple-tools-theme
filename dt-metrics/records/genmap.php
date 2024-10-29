<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly.

class DT_Metrics_Groups_Genmap extends DT_Metrics_Chart_Base
{
    //slug and title of the top menu folder
    public $base_slug; // lowercase
    public $slug = 'genmap'; // lowercase
    public $base_title;
    public $title;
    public $js_object_name = 'wp_js_object'; // This object will be loaded into the metrics.js file by the wp_localize_script.
    public $permissions = [];
    public $namespace = null;

    public function __construct( $base_slug, $base_title ) {
        $this->base_slug = $base_slug;
        $this->base_title = $base_title;

        parent::__construct();

        $this->title = __( 'GenMapper Trees', 'disciple_tools' );

        $url_path = dt_get_url_path( true );
        if ( "metrics/$this->base_slug/$this->slug" === $url_path ) {
            add_action( 'wp_enqueue_scripts', [ $this, 'scripts' ], 99 );
        }

        $this->namespace = "dt-metrics/$this->base_slug/$this->slug";
        add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );
    }

    public function add_api_routes() {
        $version = '1';
        $namespace = 'dt/v' . $version;
        register_rest_route(
            $namespace, "/metrics/$this->base_slug/genmap", [
                [
                    'methods'  => WP_REST_Server::CREATABLE,
                    'callback' => [ $this, 'tree' ],
                    'permission_callback' => function() {
                        return $this->has_permission();
                    }
                ],
            ]
        );
    }

    public function tree( WP_REST_Request $request ) {
        $params = dt_recursive_sanitize_array( $request->get_params() );
        if ( ! isset( $params['p2p_type'], $params['p2p_direction'], $params['post_type'], $params['slug'] ) ) {
            return new WP_Error( __METHOD__, 'Missing parameters! [Required: p2p_type, p2p_direction, post_type, slug ]', [ 'status' => 400 ] );
        }

        $slug = $params['slug'];

        // Ensure user has required permissions, based on specified slug request.
        $has_permission = false;
        if ( ( $slug === 'personal' ) && current_user_can( 'access_contacts' ) ) {
            $has_permission = true;
        }
        if ( ( $slug === 'records' ) && ( current_user_can( 'dt_all_access_contacts' ) || current_user_can( 'view_project_metrics' ) ) ) {
            $has_permission = true;
        }

        if ( $has_permission ) {
            $user = wp_get_current_user();
            $post_type = $params['post_type'];
            $post_settings = DT_Posts::get_post_settings( $post_type );

            // Determine scope of query focus, based on specified slug.
            $focus_id = $params['focus_id'] ?? 0;
            if ( ( $post_type === 'contacts' ) && ( $slug === 'personal' ) ) {
                $focus_id = Disciple_Tools_Users::get_contact_for_user( $user->ID );
            }

            // Ensure sourced focus_id is not a wp exception.
            if ( is_wp_error( $focus_id ) ) {
                return new WP_Error( __METHOD__, 'Missing Permissions', [ 'status' => 400 ] );
            }

            $filters = [
                'slug' => $slug,
                'post_type' => $post_type,
                'show_archived' => $params['show_archived'] ?? false,
                'status_key' => $post_settings['status_field']['status_key'] ?? '',
                'archived_key' => $post_settings['status_field']['archived_key'] ?? ''
            ];
            $query = $this->get_query( $post_type, $params['p2p_type'], $params['p2p_direction'], $filters );

            $can_list_all = current_user_can( 'list_all_' . $post_type );
            if ( $post_type === 'contacts' && current_user_can( 'dt_all_access_contacts' ) ){
                $can_list_all = true;
            }
            $generated_genmap = $this->get_genmap( $query, $params['gen_depth_limit'] ?? 100, $focus_id, $filters, $can_list_all );

            // Ensure empty hits on personal based slugs, still ensure user node is accessible.
            if ( ( $focus_id !== 0 ) && empty( $generated_genmap['children'] ) ) {
                $generated_genmap['shared'] = 1;
                $generated_genmap['name'] = $user->display_name;
            }

            return [
                'data_layers' => $this->package_data_layer_post_fields( $query, $post_type, $post_settings, $params['data_layers'] ?? [] ),
                'genmap' => $generated_genmap
            ];
        } else {
            return new WP_Error( __METHOD__, 'Missing Permissions', [ 'status' => 400 ] );
        }
    }

    public function scripts() {

        $js_file_name = 'dt-metrics/records/genmap.js';
        $js_uri = get_template_directory_uri() . "/$js_file_name";
        $js_dir = get_template_directory() . "/$js_file_name";
        wp_enqueue_script( 'dt_metrics_project_script', $js_uri, [
            'jquery',
            'lodash'
        ], filemtime( $js_dir ), true );

        wp_localize_script(
            'dt_metrics_project_script', 'dtMetricsProject', [
                'root' => esc_url_raw( rest_url() ),
                'base_slug' => $this->base_slug,
                'site_url' => esc_url_raw( site_url() ),
                'theme_uri' => get_template_directory_uri(),
                'nonce' => wp_create_nonce( 'wp_rest' ),
                'current_user_login' => wp_get_current_user()->user_login,
                'current_user_id' => get_current_user_id(),
                'map_key' => empty( DT_Mapbox_API::get_key() ) ? '' : DT_Mapbox_API::get_key(),
                'data' => [],
                'translations' => [
                    'title' => $this->title,
                    'show_archived' => __( 'Show Archived', 'disciple_tools' ),
                    'data_layer_title' => __( 'Data Layers', 'disciple_tools' ),
                    'data_layer_settings_color_label' => __( 'Node Color', 'disciple_tools' ),
                    'data_layer_settings_color_default_label' => __( 'Default', 'disciple_tools' ),
                    'add_data_layer' => __( 'Add Data Layer', 'disciple_tools' ),
                    'icon_data_layer' => __( 'Show as icons', 'disciple_tools' ),
                    'select_data_layer_field' => __( 'select data layer field to capture', 'disciple_tools' ),
                    'highlight_active' => __( 'Highlight Active', 'disciple_tools' ),
                    'highlight_churches' => __( 'Highlight Churches', 'disciple_tools' ),
                    'members' => __( 'Members', 'disciple_tools' ),
                    'view_record' => __( 'View Record', 'disciple_tools' ),
                    'assigned_to' => __( 'Assigned To', 'disciple_tools' ),
                    'status' => __( 'Status', 'disciple_tools' ),
                    'total_members' => __( 'Total Members', 'disciple_tools' ),
                    'view_group' => __( 'View Group', 'disciple_tools' ),
                    'details' => [
                        'status' => __( 'Status', 'disciple_tools' ),
                        'groups' => __( 'Groups', 'disciple_tools' ),
                        'assigned_to' => __( 'Assigned To', 'disciple_tools' ),
                        'coaches' => __( 'Coaches', 'disciple_tools' ),
                        'type' => __( 'Type', 'disciple_tools' ),
                        'member_count' => __( 'Member Count', 'disciple_tools' ),
                        'members' => __( 'Members', 'disciple_tools' ),
                        'open' => __( 'Open', 'disciple_tools' ),
                        'add' => __( 'Add', 'disciple_tools' ),
                        'focus' => __( 'Focus', 'disciple_tools' ),
                        'hide' => __( 'Hide', 'disciple_tools' ),
                    ],
                    'modal' => [
                        'add_child_title' => __( 'Add Child To', 'disciple_tools' ),
                        'add_child_name_title' => __( 'Name', 'disciple_tools' ),
                        'add_child_but' => __( 'Add Child', 'disciple_tools' ),
                        'focus_title' => __( 'Focus On Node', 'disciple_tools' ),
                        'focus_are_you_sure_question' => __( 'Are you sure you wish to focus on node?', 'disciple_tools' ),
                        'focus_yes' => __( 'Yes', 'disciple_tools' )
                    ],
                    'infinite_loops' => [
                        'title' => __( 'Infinite Loops', 'disciple_tools' )
                    ]
                ],
                'post_types' => Disciple_Tools_Core_Endpoints::get_settings()['post_types'] ?? [],
                'data_layer_supported_field_types' => [
                    'date',
                    'key_select',
                    'multi_select',
                    'tags'
                ],
                'data_layers' => []
            ]
        );

        wp_enqueue_script( 'orgchart_js', 'https://cdnjs.cloudflare.com/ajax/libs/orgchart/3.7.0/js/jquery.orgchart.min.js', [
            'jquery',
        ], '3.7.0', true );

        $css_file_name = 'dt-metrics/common/jquery.orgchart.custom.css';
        $css_uri = get_template_directory_uri() . "/$css_file_name";
        $css_dir = get_template_directory() . "/$css_file_name";
        wp_enqueue_style( 'orgchart_css', $css_uri, [], filemtime( $css_dir ) );
    }

    public function get_query( $post_type, $p2p_type, $p2p_direction, $filters = [] ) {
        global $wpdb;

        // p2p direction will govern overall query sql shape.
        if ( in_array( $p2p_direction, [ 'any', 'to' ] ) ) {
            $not_from = 'NOT';
            $not_to = '';
            $select_id = 'p2p_from';
            $select_parent_id = 'p2p_to';
        } else {
            $not_from = '';
            $not_to = 'NOT';
            $select_id = 'p2p_to';
            $select_parent_id = 'p2p_from';
        }

        $user = wp_get_current_user();

        // Determine archived meta values.
        $status_key = $filters['status_key'] ?? '';

        // Prepare sql shape to be executed.
        $prepared_query = $wpdb->prepare( "
                SELECT
                  a.ID         as id,
                  0            as parent_id,
                  a.post_title as name,
                  ( SELECT p_status.meta_value FROM $wpdb->postmeta as p_status WHERE ( p_status.post_id = a.ID ) AND ( p_status.meta_key = %s ) ) as status,
                  ( SELECT EXISTS( SELECT p_shared.user_id FROM $wpdb->dt_share as p_shared WHERE p_shared.user_id = %d AND p_shared.post_id = a.ID ) ) as shared
                FROM $wpdb->posts as a
                WHERE a.post_type = %s
                AND a.ID %1s IN (
                  SELECT DISTINCT (p2p_from)
                  FROM $wpdb->p2p
                  WHERE p2p_type = %s
                  GROUP BY p2p_from
                )
                  AND a.ID %1s IN (
                  SELECT DISTINCT (p2p_to)
                  FROM $wpdb->p2p
                  WHERE p2p_type = %s
                  GROUP BY p2p_to
                )
                UNION
                SELECT
                  p.%1s  as id,
                  p.%1s    as parent_id,
                  (SELECT sub.post_title FROM $wpdb->posts as sub WHERE sub.ID = p.%1s ) as name,
                  ( SELECT u_status.meta_value FROM $wpdb->postmeta as u_status WHERE ( u_status.post_id = p.%1s ) AND ( u_status.meta_key = %s ) ) as status,
                  ( SELECT EXISTS( SELECT u_shared.user_id FROM $wpdb->dt_share as u_shared WHERE u_shared.user_id = %d AND u_shared.post_id = p.%1s ) ) as shared
                FROM $wpdb->p2p as p
                WHERE p.p2p_type = %s;
            ", $status_key, $user->ID, $post_type, $not_from, $p2p_type, $not_to, $p2p_type, $select_id, $select_parent_id, $select_id, $select_id, $status_key, $user->ID, $select_id, $p2p_type );

        //phpcs:disable
        return $wpdb->get_results( $prepared_query, ARRAY_A );
        //phpcs:enable
    }

    public function get_genmap( $query, $depth_limit, $focus_id, $filters = [], $can_list_all = false ){

        if ( is_wp_error( $query ) ){
            return $this->_circular_structure_error( $query );
        }
        if ( empty( $query ) ) {
            return $this->_no_results();
        }
        $menu_data = $this->prepare_menu_array( $query );

        $user_contact_id = Disciple_Tools_Users::get_contact_for_user( get_current_user_id() );

        return $this->build_array( $focus_id ?? 0, $menu_data, 0, $depth_limit, $filters, $can_list_all, $user_contact_id );
    }

    public function prepare_menu_array( $query ) {
        // prepare special array with parent-child relations
        $menu_data = array(
            'items' => array(),
            'parents' => array()
        );

        foreach ( $query as $menu_item )
        {
            $menu_data['items'][$menu_item['id']] = $menu_item;
            $menu_data['parents'][$menu_item['parent_id']][] = $menu_item['id'];
        }
        return $menu_data;
    }

    public function build_array( $parent_id, $menu_data, $gen, $depth_limit, $filters = [], $can_list_all = false, $user_contact_id = null ) {
        $children = [];
        if ( isset( $menu_data['parents'][$parent_id] ) && ( $gen < $depth_limit ) )
        {
            $next_gen = $gen + 1;

            foreach ( $menu_data['parents'][$parent_id] as $item_id )
            {
                $children[] = $this->build_array( $item_id, $menu_data, $next_gen, $depth_limit, $filters, $can_list_all, $user_contact_id );
            }
        }

        // Ensure to force a record node share for administrators.
        $shared = intval( $menu_data['items'][ $parent_id ]['shared'] ?? 0 );
        if ( $parent_id === $user_contact_id || $can_list_all ){
            $shared = 1;
        }
        $array = [
            'id' => $parent_id,
            'name' => ( ( $shared === 1 ) || ( $gen === 0 ) ) ? ( $menu_data['items'][ $parent_id ]['name'] ?? 'SYSTEM' ) : '',
            'status' => $menu_data['items'][ $parent_id ]['status'] ?? '',
            'shared' => $shared,
            'content' => 'Gen ' . $gen
        ];

        // Ensure to exclude non-shared generations.
        $children = $this->exclude_non_shared_generations( $children );

        // Determine if archived records are to be excluded.
        if ( !$filters['show_archived'] ) {

            // Recursively exclude associated children.
            $children = $this->exclude_archived_children( $children, $filters['archived_key'] );

            // Only capture node, if active children are still detected; otherwise return empty array.
            if ( !empty( $children ) ) {
                $array['children'] = $children;
                $array['has_infinite_loop'] = $this->has_infinite_loop( $parent_id, $children );
            } else {
                $array['children'] = [];
                $array['has_infinite_loop'] = false;
            }
        } else {
            $array['children'] = $children;
            $array['has_infinite_loop'] = $this->has_infinite_loop( $parent_id, $children );
        }

        return $array;
    }

    public function has_infinite_loop( $parent_id, $children ): bool {
        foreach ( $children ?? [] as $child ) {
            if ( isset( $child['id'], $child['children'] ) ) {
                if ( $parent_id === $child['id'] ){
                    return true;
                }
                if ( !empty( $child['children'] ) ){
                    if ( $this->has_infinite_loop( $parent_id, $child['children'] ) ){
                        return true;
                    }
                }
            }
        }

        return false;
    }

    public function exclude_archived_children( $children, $archived_key ): array {
        $updated_children = [];
        foreach ( $children ?? [] as $child ) {
            if ( isset( $child['status'] ) && $child['status'] == $archived_key ) {
                $child['children'] = $this->exclude_archived_children( $child['children'], $archived_key );

                if ( !empty( $child['children'] ) ) {
                    $updated_children[] = $child;
                }
            } else {
                $updated_children[] = $child;
            }
        }

        return $updated_children;
    }

    public function exclude_non_shared_generations( $children ): array {
        $updated_children = [];
        foreach ( $children ?? [] as $child ) {
            if ( !empty( $child['children'] ) ) {
                $child['children'] = $this->exclude_non_shared_generations( $child['children'] );
            }
            if ( !empty( $child['children'] ) || ( isset( $child['shared'] ) && intval( $child['shared'] ) === 1 ) ) {
                $updated_children[] = $child;
            }
        }

        return $updated_children;
    }

    public function package_data_layer_post_fields( $query, $post_type, $post_settings, $data_layer_settings ): array {
        global $wpdb;

        if ( !isset( $data_layer_settings['color'], $data_layer_settings['layers'] ) ) {
            return [];
        }

        $packaged_data_layer_post_fields = [];
        $post_field_settings = $post_settings['fields'] ?? [];

        // Capture all query post ids; which shall be interrogated further.
        $post_ids = [];
        foreach ( $query as $post ) {
            if ( !empty( $post['id'] ) ) {
                $post_ids[] = $post['id'];
            }
        }

        if ( !empty( $post_ids ) ) {

            // Next, package post meta fields to be captured.
            $data_layer_fields = array_merge( $data_layer_settings['layers'], ( ( $data_layer_settings['color'] !== 'default-node-color' ) ? [ $data_layer_settings['color'] ] : [] ) );

            // Construct sql to fetch identified post data layer fields.
            $post_ids_array_sql = dt_array_to_sql( $post_ids, true );
            $data_layer_fields_array_sql = dt_array_to_sql( $data_layer_fields );

            // phpcs:disable
            $query = $wpdb->get_results( $wpdb->prepare( "
                SELECT DISTINCT post_id, meta_key, meta_value
                FROM $wpdb->postmeta
                WHERE post_id IN (%1s) AND meta_key IN ($data_layer_fields_array_sql)
            ", $post_ids_array_sql ), ARRAY_A );
            // phpcs:enable

            // Package data layer post fields.
            foreach ( $query as $postmeta ) {
                $post_id = $postmeta['post_id'];
                $meta_key = $postmeta['meta_key'];
                $meta_value = $postmeta['meta_value'];

                if ( !isset( $post_field_settings[ $meta_key ] ) ) {
                    continue;
                }

                if ( !isset( $packaged_data_layer_post_fields[ $post_id ] ) ) {
                    $packaged_data_layer_post_fields[ $post_id ] = [];
                }

                if ( !isset( $packaged_data_layer_post_fields[ $post_id ][ $meta_key ] ) ) {
                    $packaged_data_layer_post_fields[ $post_id ][ $meta_key ] = [];
                }

                $packaged_data_layer_post_fields[ $post_id ][ $meta_key ][] = [
                    'label' => $post_field_settings[ $meta_key ]['name'],
                    'value' => $meta_value
                ];
            }
        }

        return $packaged_data_layer_post_fields;
    }
}
