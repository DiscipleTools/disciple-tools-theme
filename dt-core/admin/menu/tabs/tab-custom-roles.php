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

if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

/**
 * Class Disciple_Tools_Multi_Roles
 */
class Disciple_Tools_Tab_Custom_Roles extends Disciple_Tools_Abstract_Menu_Base {
    private static $_instance = null;
    private const OPTION_NAME = 'dt_custom_roles';

    protected $url_base;
    protected $capabilities;

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
        add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ] );
        add_action( 'admin_menu', [ $this, 'add_submenu' ], 99 );
        add_action( 'dt_settings_tab_menu', [ $this, 'add_tab' ], 10, 1 );
        add_action( 'dt_settings_tab_content', [ $this, 'content' ], 99, 1 );
        $option = get_option( self::OPTION_NAME, false );

        //Make sure we have an option
        if ( !$option ) {
            $option = add_option( self::OPTION_NAME, [] );
        }

        $this->url_base = esc_url( admin_url() ) . 'admin.php?page=dt_options&tab=roles';
        $this->capabilities = Disciple_Tools_Capabilities::get_instance();

        parent::__construct();
    } // End __construct()

    /**
     * Enqueue the styles. The javascript is generic and is included in the main admin script file.
     */
    public function admin_enqueue_scripts() {
        if ( !current_user_can( 'list_roles' ) ) {
            return;
        }
        wp_register_style( 'dt_roles_css', disciple_tools()->admin_css_url . 'disciple-tools-roles-styles.css', [], filemtime( disciple_tools()->admin_css_path . 'disciple-tools-roles-styles.css' ) );
        wp_enqueue_style( 'dt_roles_css' );
    }

    /**
     * Register the submenu
     */
    public function add_submenu() {
        if ( !current_user_can( 'list_roles' ) ) {
            return;
        }
        add_submenu_page( 'dt_options', __( 'Roles', 'disciple_tools' ), __( 'Roles', 'disciple_tools' ), 'manage_dt', 'dt_options&tab=roles', [ 'Disciple_Tools_Settings_Menu', 'content' ] );
    }

    /**
     * Register the tab
     * @param $tab
     */
    public function add_tab( $tab ) {
        if ( !current_user_can( 'list_roles' ) ) {
            return;
        }
        ?>

        <a href="<?php echo esc_url( $this->url_base ) ?>"
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
        if ( !current_user_can( 'list_roles' ) ) {
            $this->show_error( new WP_Error( 401, __( 'Unauthorized', 'disciple_tools' ) ) );
            return;
        }

        if ( $tab !== 'roles' ) {
            return;
        }

        if ( isset( $_GET['action'] ) ) {
            $this->handle_action( sanitize_text_field( wp_unslash( $_GET['action'] ) ) );
            return;

            // phpcs:ignore
        } elseif ( isset( $_POST['role_edit_nonce'] ) ) {
            if ( !current_user_can( 'edit_roles' ) ) {
                $this->show_error( 401, __( 'Unauthorized', 'disciple_tools' ) );
                return;
            }
            $this->save();
        }
        $this->show();
    }

    /**
     * Validate and handle custom action routes
     * @param $action
     */
    private function handle_action( $action ) {
        $method = $action . '_action';
        if ( method_exists( $this, $method ) ) {
            $this->$method();
        } else {
            $this->show_error( new WP_Error( 400, __( 'Unsupported action.', 'disciple_tools' ) ) );
            $this->show();
        }
    }

    /**
     * Handle the delete action
     */
    private function delete_action() {
        if ( !current_user_can( 'delete_roles' ) ) {
            $this->show_error( 401, __( 'Unauthorized', 'disciple_tools' ) );
            return;
        }

        if ( !isset( $_GET['role'] ) ) {
            $error = new WP_Error( 400, __( 'Please specify a role', 'disciple_tools' ) );
            $this->show_error( $error );
            return;
        }

        if ( !isset( $_GET['confirm'] ) ) {
            $this->view_delete();
            return;
        }

        $this->delete();
        $this->show();
    }

    /**
     * Handle the duplicate action
     */
    private function duplicate_action() {
        if ( !current_user_can( 'create_roles' ) ) {
            $this->show_error( 401, __( 'Unauthorized', 'disciple_tools' ) );
            return;
        }

        if ( !isset( $_GET['role'] ) ) {
            $error = new WP_Error( 400, __( 'Please specify a role', 'disciple_tools' ) );
            $this->show_error( $error );
            return;
        }
        $this->box( 'top', __( 'Duplicate role.', 'disciple_tools' ) );
        $this->view_duplicate_role();
    }

    /**
     * Handle the create action
     */
    private function create_action() {
        if ( !current_user_can( 'create_roles' ) ) {
            $this->show_error( new WP_Error( 401, __( 'Unauthorized', 'disciple_tools' ) ) );
            return;
        }


        // phpcs:ignore
        if ( isset( $_POST['role_create_nonce'] ) ) {
            $success = $this->create();

            if ( $success ) {
                $this->show();
            } else {
                $this->view_create_role();
            }
            return;
        }

        $this->box( 'top', __( 'Add custom role.', 'disciple_tools' ) );

        $this->view_create_role();
    }

    /**
     * Process a role save
     */
    public function save() {
        if ( !isset( $_POST['role_edit_nonce'] ) || !wp_verify_nonce( sanitize_key( $_POST['role_edit_nonce'] ), 'role_edit' ) ) {
            $error = new WP_Error( 401, __( 'Unauthorized', 'disciple_tools' ) );
            $this->show_error( $error );
        }

        $roles = Disciple_Tools_Roles::get_dt_roles_and_permissions( false );

        $label = isset( $_POST['label'] ) ? sanitize_text_field( wp_unslash( $_POST['label'] ) ) : null;
        $slug = isset( $_POST['slug'] ) ? sanitize_text_field( wp_unslash( $_POST['slug'] ) ) : null;
        $description = isset( $_POST['description'] ) ? sanitize_text_field( wp_unslash( $_POST['description'] ) ) : null;

        // phpcs:ignore
        $capabilities = isset( $_POST['capabilities'] ) ? wp_unslash( (array)$_POST['capabilities'] ) : [];
        dt_recursive_sanitize_array( $capabilities );

        if ( !$slug ) {
            return $this->show_error( new WP_Error( 400, 'The slug field is required.' ) );
        }

        if ( !$label ) {
            return $this->show_error( new WP_Error( 400, 'The label field is required.' ) );
        }

        if ( !$description ) {
            return $this->show_error( new WP_Error( 400, 'The description field is required.' ) );
        }

        if ( !isset( $roles[ $slug ] ) ) {
            return $this->show_error( new WP_Error( 400, 'The role does not exist.' ) );
        }
        if ( isset( $roles[ $slug ]['is_editable'] ) && empty( $roles[ $slug ]['is_editable'] ) ) {
            return $this->show_error( new WP_Error( 400, 'The role is not editable' ) );
        }

        $option = get_option( self::OPTION_NAME, [] );

        $updated_capabilities = array_reduce( $capabilities, function ( $updated_capabilities, $capability ){
            $updated_capabilities[$capability] = true;
            return $updated_capabilities;
        }, [] );
        $option[ $slug ] = [
            'description'  => $description,
            'label'        => $label,
            'slug'         => $slug,
            'capabilities' => $updated_capabilities
        ];

        $success = update_option( self::OPTION_NAME, $option );

        if ( !$success ) {
            $this->show_error( new WP_Error( 400, __( 'The role could not be saved.', 'disciple_tools' ) ) );
            return false;
        }

        ?>
        <div class="notice notice-success">
            <p>
                <?php esc_html_e( 'The role has been saved.', 'disciple_tools' ); ?>
            </p>
        </div>
        <?php
    }

    /**
     * Process a role creation request
     * =     */
    public function create() {
        global $wpdb;

        if ( !isset( $_POST['role_create_nonce'] ) || !wp_verify_nonce( sanitize_key( $_POST['role_create_nonce'] ), 'role_create' ) ) {
            $error = new WP_Error( 401, __( 'Unauthorized', 'disciple_tools' ) );
            $this->show_error( $error );
            return false;
        }
        $roles = Disciple_Tools_Roles::get_dt_roles_and_permissions( false );
        $label = isset( $_POST['label'] ) ? sanitize_text_field( wp_unslash( $_POST['label'] ) ) : null;
        $description = isset( $_POST['description'] ) ? sanitize_text_field( wp_unslash( $_POST['description'] ) ) : null;

        // phpcs:ignore
        $capabilities = isset( $_POST['capabilities'] ) ? wp_unslash( (array)$_POST['capabilities'] ) : [];
        dt_recursive_sanitize_array( $capabilities );

        if ( !$label ) {
            $this->show_error( new WP_Error( 400, 'The label field is required.' ) );
            return false;
        }

        if ( !$description ) {
            $this->show_error( new WP_Error( 400, 'The description field is required.' ) );
            return false;
        }

        $slug = 'custom_' . strtolower( trim( preg_replace( '/[^A-Za-z0-9-]+/', '_', $label ), '_' ) );

        //Make sure the slug is unique.
        $i = 0;
        $slug_base = $slug;
        while ( array_key_exists( $slug, $roles ) ) {
            $i++;
            $slug = $slug_base . '_' . $i;
        }

        $option = get_option( self::OPTION_NAME, [] );

        $option[ $slug ] = [
            'description'  => $description,
            'label'        => $label,
            'slug'         => $slug,
            'capabilities' => $capabilities
        ];

        $success = update_option( self::OPTION_NAME, $option );

        if ( !$success ) {
            $this->show_error( new WP_Error( 400, __( 'The role could not be created.', 'disciple_tools' ) ) );
            return false;
        }

        return true;
    }

    /**
     * Process a role deletion
     */
    public function delete() {
        global $wpdb;

        if ( !isset( $_GET['role'] ) ) {
            $this->show_error( new WP_Error( 400, 'The description field is required.' ) );
        }

        $slug = sanitize_text_field( wp_unslash( $_GET['role'] ) );

        $option = get_option( self::OPTION_NAME, [] );

        unset( $option[ $slug ] );

        $success = update_option( self::OPTION_NAME, $option );

        if ( !$success ) {
            $this->show_error( new WP_Error( 400, __( 'The role could not be deleted.', 'disciple_tools' ) ) );
            return false;
        }

        return true;
    }

    /**
     * VIEWS
     * -------
     */

    /**
     * Show an error notice
     * @param WP_Error $error
     */
    private function show_error( WP_Error $error ) {
        if ( $error ) {
            ?>
            <div class="notice notice-error">
                <p>
                    <?php echo esc_html( $error->get_error_message() ); ?>
                </p>
            </div>
            <?php
        }
    }

    /**
     * Main view
     */
    private function show() {
        $roles = Disciple_Tools_Roles::get_dt_roles_and_permissions( false );
        ksort( $roles );
        $view_role = isset( $_GET['role'] ) ? sanitize_text_field( wp_unslash( $_GET['role'] ) ) : null;
        $this->box( 'top', __( 'Add or edit custom user roles.', 'disciple_tools' ) );
        ?>
        <div name="role_select"
             id="role-manager">
            <table class="widefat">
                <thead>
                <tr>
                    <th width="300">
                        <strong><?php esc_html_e( 'Label', 'disciple_tools' ); ?></strong>
                    </th>
                    <th>
                        <strong><?php esc_html_e( 'Description', 'disciple_tools' ); ?></strong>
                    </th>
                    <th>
                    </th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ( $roles as $key => $role ): ?>
                    <?php
                    if ( !isset( $role['label'], $role['description'] ) ){
                        continue;
                    }
                    $is_active = $key === $view_role;
                    $editable = ( !isset( $role['is_editable'] ) || !empty( $role['is_editable'] ) ) && current_user_can( 'edit_roles' );
                    ?>
                    <tr class="<?php echo $is_active ? 'active' : '' ?>"
                        id="role-<?php echo esc_attr( $key ) ?>">
                        <td>
                            <strong>
                                <a href="<?php echo esc_url( $this->url_base . '&' . http_build_query( [ 'role' => $key ] ) . '#role-' . $key ) ?>"
                                   title="View capabilities for <?php echo esc_attr( $role['label'] ) ?>"><?php echo esc_attr( $role['label'] ) ?></a>
                            </strong>
                            <?php if ( !$editable ): ?>
                                <span class="dashicons dashicons-lock"></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php echo esc_html( $role['description'] ) ?>
                        </td>
                        <td style="text-align: right;">
                            <?php if ( !$is_active ): ?>
                                <?php if ( current_user_can( 'delete_roles' ) || current_user_can( 'create_roles' ) ): ?>
                                    <details class="flyout">
                                        <summary>
                                            <span class="dashicons dashicons-ellipsis"></span>
                                        </summary>
                                        <nav>
                                            <ul>
                                                <?php if ( current_user_can( 'create_roles' ) ): ?>
                                                    <li>
                                                        <a href="<?php echo esc_url( $this->url_base . '&' . http_build_query( [ 'action' => 'duplicate', 'role' => $key ] ) ) ?>"
                                                           class="button">Duplicate</a>
                                                    </li>
                                                <?php endif; ?>
                                                <?php if ( current_user_can( 'delete_roles' ) && $editable && !empty( $role['custom'] ) ): ?>
                                                    <li>
                                                        <a href="<?php echo esc_url( $this->url_base . '&' . http_build_query( [ 'action' => 'delete', 'role' => $key ] ) ) ?>"
                                                           class="button button-primary">Delete</a>
                                                    </li>
                                                <?php endif; ?>
                                            </ul>
                                        </nav>
                                    </details>
                                <?php endif; ?>
                            <?php else : ?>
                                <a href="<?php echo esc_url( $this->url_base ) ?>">
                                    <span class="dashicons dashicons-no-alt"></span>
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="5">
                            <?php
                            if ( $is_active ) { ?>
                                <?php $editable ?
                                    $this->view_edit_role( $key, $role ) :
                                    $this->view_role( $key, $role );
                                ?>
                            <?php }
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
                <?php if ( current_user_can( 'create_roles' ) ): ?>
                    <tfoot colspan="4">
                    <tr>
                        <td colspan="3">
                            <a class="button button-primary button-large"
                               title=" <?php esc_html_e( 'Create New Role', 'disciple_tools' ); ?>"
                               href="<?php echo esc_url( $this->url_base . '&' . http_build_query( [ 'action' => 'create' ] ) ) ?>">
                                <?php esc_html_e( 'Create New Role', 'disciple_tools' ); ?>
                            </a>
                        </td>
                    </tr>
                    </tfoot>
                <?php endif; ?>
            </table>
        </div>
        <?php
    }

    /**
     * View a readonly individual roles
     * @param $key
     * @param $role
     */
    private function view_role( $key, $role ) {
        $label = $role['label'];
        $description = $role['description'];

        // Extract accordingly, based on incoming role availability.
        if ( !empty( $role['permissions'] ) ){
            $role_capabilities = array_keys( array_filter( $role['permissions'], function ( $v, $k ){
                return is_bool( $v ) && ( $v === true );
            }, ARRAY_FILTER_USE_BOTH ) );
        } else {
            $role_capabilities = array_keys( get_role( $key )->capabilities );
        }
        ?>
        <div class="alert alert-warning"
             id="role-<?php esc_attr( $key ); ?>">
            <p>
                <strong> <?php esc_html_e( 'This role is read-only and cannot be edited.', 'disciple_tools' ); ?></strong>
            </p>
        </div>
        <table class="role widefat">
            <tr>
                <td width="280">
                    <label><strong><?php esc_html_e( 'Role Label', 'disciple_tools' ); ?></strong></label>
                    <div class="description">
                        <?php esc_html_e( 'The name of the role.', 'disciple_tools' ); ?>
                    </div>
                </td>
                <td>
                    <input type="text"
                           name="label"
                           placeholder="<?php esc_html_e( 'Enter label...', 'disciple_tools' ); ?>"
                           value="<?php echo esc_attr( $label ); ?>"
                           style="width: 100%;"
                           readonly/>
                </td>
            </tr>
            <tr>
                <td>
                    <label><strong><?php esc_html_e( 'Role Description', 'disciple_tools' ); ?></strong></label>
                    <div class="description">
                        <?php esc_html_e( 'An informative description of the role.', 'disciple_tools' ); ?>
                    </div>
                </td>
                <td>
                    <textarea type="text"
                              name="description"
                              placeholder="<?php esc_html_e( 'Enter description...', 'disciple_tools' ); ?>"
                              style="width: 100%;"
                              readonly><?php echo esc_attr( $description ); ?></textarea>
                </td>
            </tr>
            <tr>
                <td>
                    <label><strong><?php esc_html_e( 'Role Capability Source', 'disciple_tools' ); ?></strong></label>
                    <?php $this->view_source_filter() ?>
                    <div class="description">
                        <?php esc_html_e( 'Only capabilities from the above source are displayed.', 'disciple_tools' ); ?>
                    </div>
                </td>
                <td>
                    <?php $this->view_capabilities( $role_capabilities, false ) ?>
                </td>
            </tr>
        </table>
        <?php
    }

    /**
     * View for edit role form
     * @param $key
     */
    private function view_edit_role( $key ) {
        $role = get_option( self::OPTION_NAME, [] )[ $key ] ?? null;
        if ( empty( $role ) ) {
            $all_roles = Disciple_Tools_Roles::get_dt_roles_and_permissions();
            $role = $all_roles[$key];
            $role['capabilities'] = array_keys( $role['permissions'] );
        }
        $label = $role['label'];
        $description = $role['description'];

        // Extract accordingly, based on array storage type.
        if ( !empty( $role['capabilities'] ) && is_int( array_keys( $role['capabilities'] )[0] ) ){
            $role_capabilities = $role['capabilities'];
        } else {
            $role_capabilities = array_keys( array_filter( $role['capabilities'], function ( $v, $k ){
                return is_bool( $v ) && ( $v === true );
            }, ARRAY_FILTER_USE_BOTH ) );
        }
        ?>

        <form id="role-manager"
              method="POST">
            <input type="hidden"
                   name="role_edit_nonce"
                   id="role-edit-nonce"
                   value="<?php echo esc_attr( wp_create_nonce( 'role_edit' ) ) ?>"/>
            <input type="hidden"
                   name="slug"
                   id="slug"
                   value="<?php echo esc_attr( $key ) ?>"/>
            <?php $this->view_role_form_table( $label, $description, $role_capabilities ); ?>
            <table>
                <tfoot>
                <tr>
                    <td colspan="4">
                        <button type="submit"
                                class="button button-primary button-large"
                                title=" <?php esc_html_e( 'Create New Role', 'disciple_tools' ); ?>"
                        >
                            <?php esc_html_e( 'Save Role', 'disciple_tools' ); ?>
                        </button>
                    </td>
                </tr>
                </tfoot>
            </table>
        </form>
        <?php
    }

    /**
     * View for create role form
     */
    private function view_create_role() {
        $label = null;
        $description = null;
        $capabilities = [];

        if ( isset( $_POST['role_create_nonce'] ) && wp_verify_nonce( sanitize_key( $_POST['role_create_nonce'] ), 'role_create' ) ) {
            $label = isset( $_POST['label'] ) ? sanitize_text_field( wp_unslash( $_POST['label'] ) ) : null;
            $description = isset( $_POST['description'] ) ? sanitize_text_field( wp_unslash( $_POST['description'] ) ) : null;
            // phpcs:ignore
            $capabilities = isset( $_POST['capabilities'] ) ? wp_unslash( (array)$_POST['capabilities'] ) : [];
            dt_recursive_sanitize_array( $capabilities );
        }
        ?>
        <form id="role-manager"
              method="POST"
              action="<?php echo esc_url( $this->url_base . '&' . http_build_query( [ 'action' => 'create' ] ) ) ?>">
            <input type="hidden"
                   name="role_create_nonce"
                   id="role-create-nonce"
                   value="<?php echo esc_attr( wp_create_nonce( 'role_create' ) ) ?>"/>
            <?php $this->view_role_form_table( $label, $description, $capabilities ); ?>
            <table>
                <tfoot>
                <tr>
                    <td colspan="4">
                        <button type="submit"
                                class="button button-primary button-large"
                                title=" <?php esc_html_e( 'Create New Role', 'disciple_tools' ); ?>"
                        >
                            <?php esc_html_e( 'Create Role', 'disciple_tools' ); ?>
                        </button>
                    </td>
                </tr>
                </tfoot>
            </table>
        </form>
        <?php
    }

    /**
     * View for duplicate role form
     */
    private function view_duplicate_role() {
        if ( !isset( $_GET['role'] ) ) {
            $this->show_error( new WP_Error( 400, 'Role is required.' ) );
            return;
        }

        $key = sanitize_text_field( wp_unslash( $_GET['role'] ) );

        $roles = Disciple_Tools_Roles::get_dt_roles_and_permissions( false );
        if ( isset( $role[ $key ] ) ) {
            $this->show_error( new WP_Error( 400, 'Role not found.' ) );
            return;
        }
        $role = $roles[ $key ];
        $label = 'Copy of ' . $role['label'];
        $description = $role['description'];
        $role_capabilities = array_keys( get_role( $key )->capabilities );
        ?>
        <form id="role-manager"
              method="POST"
              action="<?php echo esc_url( $this->url_base . '&' . http_build_query( [ 'action' => 'create' ] ) ) ?>">
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
                                title=" <?php esc_html_e( 'Create New Role', 'disciple_tools' ); ?>"
                        >
                            <?php esc_html_e( 'Create Role', 'disciple_tools' ); ?>
                        </button>
                    </td>
                </tr>
                </tfoot>
            </table>
        </form>
        <?php
    }

    /**
     * View for the capabilities list
     * @param array $selected
     * @param bool $editable
     */
    private function view_capabilities( $selected = [], $editable = true ) {
        $capabilities = $this->capabilities->all();
        ?>
        <fieldset class="capabilities"
                  id="capabilities">
            <?php foreach ( $capabilities as $capability ): ?>
                <div class="capability hide"
                     data-capability="<?php echo esc_attr( $capability->slug ); ?>"
                     data-source="<?php echo esc_attr( $capability->source ); ?>">
                    <label>
                        <input type="checkbox"
                               name="capabilities[]"
                               value="<?php echo esc_attr( $capability->slug ); ?>" <?php if ( !$editable ): ?> readonly onclick="return false;" <?php endif; ?>
                            <?php if ( in_array( $capability->slug, $selected, true ) ): ?> checked <?php endif; ?>
                        >
                        <?php echo esc_attr( $capability->name ); ?>
                        <?php if ( $capability->description ): ?>
                            <span data-tooltip="<?php echo esc_attr( $capability->description ); ?>">
                                                    <span class="dashicons dashicons-editor-help"></span>
                                                </span>
                        <?php endif; ?>
                    </label>
                </div>
            <?php endforeach; ?>
        </fieldset>
        <?php
    }

    /**
     * View for the source filter field
     */
    private function view_source_filter() {
        $sources = $this->capabilities->sources();
        $current_source = !empty( $_GET['source'] ) ? sanitize_text_field( wp_unslash( $_GET['source'] ) ) : 'Disciple Tools';
        ?>
        <p>
            <select name="capabilities_source_filter"
                    id="source-filter">
                <?php foreach ( $sources as $source ): ?>
                    <option value="<?php echo esc_attr( $source ); ?>" <?php if ( $source === $current_source ): ?> selected <?php endif; ?>>
                        <?php echo esc_attr( $source ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>

        <?php
    }

    /**
     * View for the role form table
     * @param $label
     * @param $description
     * @param $role_capabilities
     */
    private function view_role_form_table( $label, $description, $role_capabilities ) {
        ?>
        <table class="role widefat">
            <tbody>
            <tr>
                <td width="280">
                    <label><strong><?php esc_html_e( 'Role Label', 'disciple_tools' ); ?></strong></label>
                    <div class="description">
                        <?php esc_html_e( 'The name of the role.', 'disciple_tools' ); ?>
                    </div>
                </td>
                <td>
                    <input type="text"
                           name="label"
                           placeholder="<?php esc_html_e( 'Enter label...', 'disciple_tools' ); ?>"
                           value="<?php echo esc_attr( $label ); ?>"
                           style="width: 100%;"
                    >
                </td>
            </tr>
            <tr>
                <td>
                    <label><strong><?php esc_html_e( 'Role Description', 'disciple_tools' ); ?></strong></label>
                    <div class="description">
                        <?php esc_html_e( 'An informative description of the role.', 'disciple_tools' ); ?>
                    </div>
                </td>
                <td>
                    <textarea type="text"
                              name="description"
                              placeholder="<?php esc_html_e( 'Enter description...', 'disciple_tools' ); ?>"
                              style="width: 100%;"
                    ><?php echo esc_attr( $description ); ?></textarea>
                </td>
            </tr>
            <tr>
                <td>
                    <label><strong><?php esc_html_e( 'Role Capability Source', 'disciple_tools' ); ?></strong></label>
                    <?php $this->view_source_filter() ?>
                    <div class="description">
                        <?php esc_html_e( 'Only capabilities from the above source are displayed.', 'disciple_tools' ); ?>
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

    /**
     * View for delete form
     */
    private function view_delete() {
        global $wpdb;
        if ( !isset( $_GET['role'] ) ) {
            $this->show_error( new WP_Error( 400, 'Role is required.' ) );
            return;
        }

        $key = sanitize_text_field( wp_unslash( $_GET['role'] ) );
        $role = get_option( self::OPTION_NAME )[ $key ];
        $label = esc_html( $role['label'] );
        $this->box( 'top', __( 'Are you sure you want to delete the role: ', 'disciple_tools' ) . esc_attr( $label ) . __( '?', 'disciple_tools' ) );
        ?>
        <p>
            <strong><?php esc_html_e( 'This action cannot be undone.', 'disciple_tools' ); ?></strong>
        </p>

        <div class="buttons">
            <a href="<?php echo esc_url( $this->url_base ) ?>"
               class="button button-large"
               title=" <?php esc_html_e( 'Delete Role', 'disciple_tools' ); ?>"
            >
                <?php esc_html_e( 'Cancel', 'disciple_tools' ); ?>
            </a>
            <a href="<?php echo esc_url( $this->url_base . '&' . http_build_query( [ 'action' => 'delete', 'role' => $key, 'confirm' => true ] ) ) ?>"
               class="button button-primary button-large"
               title=" <?php esc_html_e( 'Delete Role', 'disciple_tools' ); ?>"
            >
                <?php esc_html_e( 'Delete Role', 'disciple_tools' ); ?>
            </a>
        </div>

        <?php
    }
}

Disciple_Tools_Tab_Custom_Roles::instance();
