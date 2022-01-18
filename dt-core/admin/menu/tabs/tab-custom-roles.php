<?php

/**
 * Disciple_Tools_Multi_Roles
 *
 * @class   Disciple_Tools_Multi_Roles
 * @version 0.1.0
 * @since   0.1.0
 * @package Disciple.Tools
 *
 */

if (!defined( 'ABSPATH' )) {
    exit;
} // Exit if accessed directly

/**
 * Class Disciple_Tools_Multi_Roles
 */
class Disciple_Tools_Tab_Custom_Roles extends Disciple_Tools_Abstract_Menu_Base {
    private static $_instance = null;
    protected $url_base;
    protected $capabilities;

    public static function instance() {
        if (is_null( self::$_instance )) {
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
        add_action( 'admin_menu', [ $this, 'add_submenu' ], 99 );
        add_action( 'dt_settings_tab_menu', [ $this, 'add_tab' ], 10, 1 );
        add_action( 'dt_settings_tab_content', [ $this, 'content' ], 99, 1 );
        $this->url_base = esc_url( admin_url() ) . "admin.php?page=dt_options&tab=roles";
        $this->capabilities = Disciple_Tools_Capabilities::get_instance();

        parent::__construct();
    } // End __construct()

    public function add_submenu() {
        add_submenu_page( 'dt_options', __( 'Roles', 'disciple_tools' ), __( 'Roles', 'disciple_tools' ), 'manage_dt', 'dt_options&tab=roles', [ 'Disciple_Tools_Settings_Menu', 'content' ] );
    }

    public function add_tab( $tab ) {
        ?>
        <a href="<?php echo $this->url_base ?>"
           class="nav-tab <?php echo esc_html( $tab == 'roles' ? 'nav-tab-active' : '' ) ?>">
            <?php echo esc_html__( 'Roles' ) ?>
        </a>
        <?php
    }

    /**
     * Packages and prints Roles page
     *
     * @param $tab
     */
    public function content( $tab ) {
        if ($tab !== 'roles') {
            return;
        }

        if (isset( $_GET[ 'action' ] )) {
            if ($_GET[ 'action' ] === 'delete' && isset( $_GET[ 'role' ] )) {
                $this->delete();
                wp_redirect( $this->url_base );
                return;
            } elseif ($_GET[ 'action' ] === 'duplicate' && isset( $_GET[ 'role' ] )) {
                $this->box( 'top', __( 'Duplicate role.', 'disciple_tools' ) );
                $this->view_duplicate_role();
                return;
            } elseif ($_GET[ 'action' ] === 'create') {
                $this->box( 'top', __( 'Add custom role.', 'disciple_tools' ) );

                if (isset( $_POST[ 'role_create_nonce' ] )) {
                    if (!wp_verify_nonce( sanitize_key( $_POST[ 'role_create_nonce' ] ), 'role_create' )) {
                        $error = new WP_Error( 401, __( "Unauthorized", "disciple_tools" ) );
                        $this->show_error( $error );
                    } else {
                        $this->create();
                    }
                }

                $this->view_create_role();
                return;
            }
        } elseif (isset( $_POST[ 'role_edit_nonce' ] )) {
            if (!wp_verify_nonce( sanitize_key( $_POST[ 'role_edit_nonce' ] ), 'role_edit' )) {
                $error = new WP_Error( 401, __( "Unauthorized", "disciple_tools" ) );
                $this->show_error( $error );
            }

            $this->save();
        }

        $this->box( 'top', __( 'Add or edit custom user roles.', 'disciple_tools' ) );

        $this->show();
    }

    private function show_error( $error ) {
        if ($error) {
            ?>
            <div class="notice notice-error">
                <p>
                    <?php echo $error->get_error_message(); ?>
                </p>
            </div>
            <?php
        }
    }

    private function styles() {
        ?>
        <style>
            #role-manager table {
                width: 100%;
            }

            #role-manager td,
            #role-manager th {
                padding: 15px;
            }

            #role-manager tbody tr {
                background-color: #F6F7F7;
            }

            #role-manager tbody tr.active {
                background-color: #2170B1;
            }

            #role-manager tbody tr.active td {
                color: white;
            }

            #role-manager tbody tr.active a {
                color: #F6F7F7;
            }

            #role-manager details.flyout .dashicons-ellipsis {
                transform: rotate(-90deg);
                cursor: pointer;
            }

            #role-manager .role {
                border: none;
            }

            #role-manager select,
            #role-manager input[type=text],
            #role-manager textarea {
                width: 100%;
            }

            #role-manager .capabilities {
                background-color: white;
                border: 0.5px solid #50575E;
                border-radius: 4px;
                padding: 5px;
                width: 100%;
                display: grid;
                grid-template-columns: repeat(4, 1fr)
            }

            #role-manager .capability {
                padding: 10px;
            }

            #role-manager #source-filter {
                margin-top: 10px;
            }

            #role-manager .dashicons-editor-help {
                font-size: 12px;
                line-height: 18px;
                color: gray;
            }

        </style>
        <?php
    }

    private function show() {
        $roles = apply_filters( 'dt_set_roles_and_permissions', [] );
        ksort( $roles);
        $view_role = isset( $_GET[ 'role' ] ) ? $_GET[ 'role' ] : null;
        $this->styles()
        ?>
        <div name="role_select"
             id="role-manager">
            <table class="widefat">
                <thead>
                <tr>
                    <th width="300">
                        <strong><?php _e( 'Label', 'disciple-tools' ); ?></strong>
                    </th>
                    <th>
                        <strong><?php _e( 'Description', 'disciple-tools' ); ?></strong>
                    </th>
                    <th>
                        <string><?php _e( 'Type', 'disciple-tools' ); ?></string>
                    </th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($roles as $key => $role): ?>
                    <?php
                    $is_active = $key === $view_role;
                    $custom = !empty( $role[ 'custom' ] );
                    ?>
                    <tr class="<?php echo $is_active ? 'active' : '' ?>" id="role-<?php _e($key); ?>">
                        <td>
                            <strong>
                                <a href="<?php echo $this->url_base . '&' . http_build_query( [ 'role' => $key ] ) . '#role-' . $key ?>"
                                   title="View capabilities for <?php echo $role[ 'label' ] ?>"><?php echo $role[ 'label' ] ?></a>
                            </strong>
                        </td>
                        <td>
                            <?php echo $role[ 'description' ] ?>
                        </td>
                        <td>
                            <?php $custom
                                ? _e( 'Custom', 'disciple-tools' )
                                : _e( 'Built-in', 'disciple-tools' );
                            ?>
                        </td>
                        <td style="text-align: right;">
                            <?php if (!$is_active): ?>
                                <details class="flyout">
                                    <summary>
                                        <span class="dashicons dashicons-ellipsis"></span>
                                    </summary>
                                    <nav>
                                        <ul>
                                            <li>
                                                <a href="<?php echo $this->url_base . '&' . http_build_query( [ 'action' => 'duplicate', 'role' => $key ] ) ?>"
                                                   class="button">Duplicate</a>
                                            </li>
                                            <?php if ($custom): ?>
                                                <li>
                                                    <a href="<?php echo $this->url_base . '&' . http_build_query( [ 'action' => 'delete', 'role' => $key ] ) ?>"
                                                       class="button button-primary">Delete</a>
                                                </li>
                                            <?php endif; ?>
                                        </ul>
                                    </nav>
                                </details>
                            <?php else: ?>
                                <a href="<?php echo $this->url_base ?>">
                                    <span class="dashicons dashicons-no-alt"></span>
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="5">
                            <?php
                            if ($is_active) { ?>
                                <?php $custom ?
                                    $this->edit_role( $key, $role ) :
                                    $this->view_role( $key, $role );
                                ?>
                            <?php }
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
                <tfoot colspan="4">
                <tr>
                    <td>
                        <a class="button button-primary button-large"
                           title=" <?php _e( 'Create New Role', 'disciple-tools' ); ?>"
                           href="<?php echo $this->url_base . '&' . http_build_query( [ 'action' => 'create' ] ) ?>">
                            <?php _e( 'Create New Role', 'disciple-tools' ); ?>
                        </a>
                    </td>
                </tr>
                </tfoot>
            </table>
        </div>
        <?php
    }

    private function view_role( $key, $role ) {
        $label = $role[ 'label' ];
        $description = $role[ 'description' ];
        $role_capabilities = get_role( $key )->capabilities;
        ?>
        <div class="alert alert-warning" id="role-<?php _e($key); ?>">
            <p>
                <strong> <?php _e( 'This role is read-only and cannot be edited.', 'disciple-tools' ); ?></strong>
            </p>
        </div>
        <table class="role widefat">
            <tr>
                <td width="280">
                    <label><strong><?php _e( 'Role Label', 'disciple-tools' ); ?></strong></label>
                    <div class="description">
                        <?php _e( 'The name of the role.', 'disciple-tools' ); ?>
                    </div>
                </td>
                <td>
                    <input type="text"
                           name="label"
                           placeholder="<?php _e( "Enter label...", 'disciple-tools' ); ?>"
                           value="<?php echo esc_attr( $label ); ?>"
                           style="width: 100%;"
                           readonly/>
                </td>
            </tr>
            <tr>
                <td>
                    <label><strong><?php _e( 'Role Description', 'disciple-tools' ); ?></strong></label>
                    <div class="description">
                        <?php _e( 'An informative description of the role.', 'disciple-tools' ); ?>
                    </div>
                </td>
                <td>
                    <textarea type="text"
                              name="description"
                              placeholder="<?php _e( "Enter description...", 'disciple-tools' ); ?>"
                              style="width: 100%;"
                              readonly><?php echo esc_attr( $description ); ?></textarea>
                </td>
            </tr>
            <tr>
                <td>
                    <label><strong><?php _e( 'Role Capability Source', 'disciple-tools' ); ?></strong></label>
                    <?php $this->view_source_filter() ?>
                    <div class="description">
                        <?php _e( 'Only capabilities from the above source are displayed.', 'disciple-tools' ); ?>
                    </div>
                </td>
                <td>
                    <?php $this->view_capabilities( $role_capabilities, false ) ?>
                </td>
            </tr>
        </table>
        <?php
    }

    private function edit_role( $key ) {
        global $wpdb;
        $role = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->dt_roles} WHERE role_slug = %s", [ $key ] ) );
        $label = $role->role_label;
        $description = $role->role_description;
        $role_capabilities = json_decode( $role->role_capabilities, 1 );
        ?>

        <form id="role-manager"
              method="POST">
            <input type="hidden"
                   name="role_edit_nonce"
                   id="role-edit-nonce"
                   value="<?php echo esc_attr( wp_create_nonce( 'role_edit' ) ) ?>"/>
            <input type="hidden"
                   name="role_slug"
                   id="role_slug"
                   value="<?php echo esc_attr( $key ) ?>"/>
            <?php $this->view_role_form_table( $label, $description, $role_capabilities ); ?>
            <table>
                <tfoot>
                <tr>
                    <td colspan="4">
                        <button type="submit"
                                class="button button-primary button-large"
                                title=" <?php _e( 'Create New Role', 'disciple-tools' ); ?>"
                        >
                            <?php _e( 'Save Role', 'disciple-tools' ); ?>
                        </button>
                    </td>
                </tr>
                </tfoot>
            </table>
        </form>
        <?php
    }

    private function view_create_role() {
        $this->styles()
        ?>
        <form id="role-manager"
              method="POST"
              action="<?php echo $this->url_base . '&' . http_build_query( [ 'action' => 'create' ] ) ?>">
            <input type="hidden"
                   name="role_create_nonce"
                   id="role-create-nonce"
                   value="<?php echo esc_attr( wp_create_nonce( 'role_create' ) ) ?>"/>
            <?php $this->view_role_form_table( '', '', [] ); ?>
            <table>
                <tfoot>
                <tr>
                    <td colspan="4">
                        <button type="submit"
                                class="button button-primary button-large"
                                title=" <?php _e( 'Create New Role', 'disciple-tools' ); ?>"
                        >
                            <?php _e( 'Create Role', 'disciple-tools' ); ?>
                        </button>
                    </td>
                </tr>
                </tfoot>
            </table>
        </form>
        <?php
    }

    private function view_duplicate_role() {
        global $wpdb;
        $key = $_GET[ 'role' ];
        $roles = apply_filters( 'dt_set_roles_and_permissions', [] );
        if (isset( $role[ $key ] )) {
            $this->show_error( new WP_Error( 400, 'Role not found.' ) );
            return;
        }
        $role = $roles[ $key ];
        $label = "Copy of " . $role[ 'label' ];
        $description = $role[ 'description' ];
        $role_capabilities = $role_capabilities = get_role( $key )->capabilities;;
        $this->styles()
        ?>
        <form id="role-manager"
              method="POST"
              action="<?php echo $this->url_base . '&' . http_build_query( [ 'action' => 'create' ] ) ?>">
            <input type="hidden"
                   name="role_create_nonce"
                   id="role-create-nonce"
                   value="<?php echo esc_attr( wp_create_nonce( 'role_create' ) ) ?>"/>
            <?php $this->view_role_form_table( $label, $description, $role_capabilities ); ?>
            <table>
                <tfoot>
                <tr>
                    <td colspan="4">
                        <button type="submit"
                                class="button button-primary button-large"
                                title=" <?php _e( 'Create New Role', 'disciple-tools' ); ?>"
                        >
                            <?php _e( 'Create Role', 'disciple-tools' ); ?>
                        </button>
                    </td>
                </tr>
                </tfoot>
            </table>
        </form>
        <?php
    }

    private function view_capabilities( $selected = [], $editable = true ) {
        $capabilities = $this->capabilities->all();
        ?>
        <fieldset class="capabilities"
                  id="capabilities">
            <?php foreach ($capabilities as $capability): ?>
                <div class="capability hide"
                     data-capability="<?php echo esc_attr( $capability->slug ) ?>"
                     data-source="<?php echo esc_attr( $capability->source ) ?>">
                    <label>
                        <input type="checkbox"
                               name="capabilities[]"
                               value="<?php echo $capability->slug; ?>" <?php if (!$editable): ?> readonly onclick="return false;" <?php endif; ?>
                            <?php if (in_array( $capability->slug, $selected )): ?> checked <?php endif; ?>
                        >
                        <?php echo $capability->name; ?>
                        <?php if ($capability->description): ?>
                            <span data-tooltip="<?php echo $capability->description; ?>">
                                                    <span class="dashicons dashicons-editor-help"></span>
                                                </span>
                        <?php endif; ?>
                    </label>
                </div>
            <?php endforeach; ?>
        </fieldset>
        <?php
    }

    private function view_source_filter() {
        $sources = $this->capabilities->sources();
        $current_source = !empty( $_GET[ 'source' ] ) ? $_GET[ 'source' ] : 'Disciple Tools';
        ?>
        <p>
            <select name="capabilities_source_filter"
                    id="source-filter">
                <?php foreach ($sources as $source): ?>
                    <option value="<?php echo esc_attr( $source ); ?>" <?php if ($source === $current_source): ?> selected <?php endif; ?>>
                        <?php echo esc_attr( $source ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>

        <?php
    }

    private function view_role_form_table( $label, $description, $role_capabilities ) {
        ?>
        <table class="role widefat">
            <tbody>
            <tr>
                <td width="280">
                    <label><strong><?php _e( 'Role Label', 'disciple-tools' ); ?></strong></label>
                    <div class="description">
                        <?php _e( 'The name of the role.', 'disciple-tools' ); ?>
                    </div>
                </td>
                <td>
                    <input type="text"
                           name="label"
                           placeholder="<?php _e( "Enter label...", 'disciple-tools' ); ?>"
                           value="<?php echo esc_attr( $label ); ?>"
                           style="width: 100%;"
                    >
                </td>
            </tr>
            <tr>
                <td>
                    <label><strong><?php _e( 'Role Description', 'disciple-tools' ); ?></strong></label>
                    <div class="description">
                        <?php _e( 'An informative description of the role.', 'disciple-tools' ); ?>
                    </div>
                </td>
                <td>
                    <textarea type="text"
                              name="description"
                              placeholder="<?php _e( "Enter description...", 'disciple-tools' ); ?>"
                              style="width: 100%;"
                    ><?php echo esc_attr( $description ); ?></textarea>
                </td>
            </tr>
            <tr>
                <td>
                    <label><strong><?php _e( 'Role Capability Source', 'disciple-tools' ); ?></strong></label>
                    <?php $this->view_source_filter() ?>
                    <div class="description">
                        <?php _e( 'Only capabilities from the above source are displayed.', 'disciple-tools' ); ?>
                    </div>
                </td>
                <td>
                    <?php $this->view_capabilities( $role_capabilities ) ?>
                </td>
            </tr>
            </tbody>
        </table>
        <?php
    }

    public function save() {
        global $wpdb;
        $label = isset( $_POST[ 'label' ] ) ? $_POST[ 'label' ] : null;
        $slug = isset( $_POST[ 'role_slug' ] ) ? $_POST[ 'role_slug' ] : null;
        $description = isset( $_POST[ 'description' ] ) ? $_POST[ 'description' ] : null;
        $capabilities = isset( $_POST[ 'capabilities' ] ) ? $_POST[ 'capabilities' ] : [];

        if (!$slug) {
            return $this->show_error( new WP_Error( 400, 'The slug field is required.' ) );
        }

        if (!$label) {
            return $this->show_error( new WP_Error( 400, 'The label field is required.' ) );
        }

        if (!$description) {
            return $this->show_error( new WP_Error( 400, 'The description field is required.' ) );
        }

        $row = $wpdb->update( $wpdb->dt_roles,
            [
                'role_description'  => $_POST[ 'description' ],
                'role_label'        => $_POST[ 'label' ],
                'role_slug'         => $slug,
                'role_capabilities' => json_encode( array_values( $capabilities ) )
            ],
            [
                'role_slug' => $slug
            ]
        );

        if (!$row) {
            $error = $wpdb->last_error;
            $this->show_error( new WP_Error( 400, $error ? $error : __( 'The role could not be saved.', 'disciple-tools' ) ) );
            return;
        }

        ?>
        <div class="notice notice-success">
            <p>
                <?php echo _e( 'The role has been saved.', 'disciple_tools' ); ?>
            </p>
        </div>
        <?php
    }

    public function create() {
        global $wpdb;
        $label = isset( $_POST[ 'label' ] ) ? $_POST[ 'label' ] : null;
        $description = isset( $_POST[ 'description' ] ) ? $_POST[ 'description' ] : null;
        $capabilities = isset( $_POST[ 'capabilities' ] ) ? $_POST[ 'capabilities' ] : [];

        if (!$label) {
            return new WP_Error( 400, 'The label field is required.' );
        }

        if (!$description) {
            return new WP_Error( 400, 'The description field is required.' );
        }

        $slug = 'custom_' . strtolower( trim( preg_replace( '/[^A-Za-z0-9-]+/', '_', $label ), '_' ) );
        $row = $wpdb->insert( $wpdb->dt_roles,
            [
                'role_description'  => $_POST[ 'description' ],
                'role_label'        => $_POST[ 'label' ],
                'role_slug'         => $slug,
                'role_capabilities' => json_encode( $capabilities )
            ]
        );

        if (!$row) {
            $error = $wpdb->last_error;
            $this->show_error( new WP_Error( 400, $error ? $error : __( 'The role could not be created.', 'disciple-tools' ) ) );
            return;
        }

        wp_redirect( $this->url_base );
    }

    public function delete() {
        global $wpdb;

        if (!isset( $_GET[ 'role' ] )) {
            return new WP_Error( 400, 'The description field is required.' );
        }

        $slug = $_GET[ 'role' ];

        $wpdb->delete( $wpdb->dt_roles, [
            'slug' => $slug
        ] );
    }
}

Disciple_Tools_Tab_Custom_Roles::instance();
