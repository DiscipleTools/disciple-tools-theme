<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class Disciple_Tools_Tab_Workflows
 */
class Disciple_Tools_Tab_Workflows extends Disciple_Tools_Abstract_Menu_Base {
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
        add_action( 'admin_menu', [ $this, 'add_submenu' ], 125 );
        add_action( 'dt_settings_tab_menu', [ $this, 'add_tab' ], 125, 1 );
        add_action( 'dt_settings_tab_content', [ $this, 'content' ], 125, 1 );
        add_action( 'admin_enqueue_scripts', [ $this, 'dt_utilities_workflows_scripts' ] );
        parent::__construct();
    } // End __construct()

    public function add_submenu() {
        add_submenu_page( 'dt_options', __( 'Workflows', 'disciple_tools' ), __( 'Workflows', 'disciple_tools' ), 'manage_dt', 'dt_options&tab=workflows', [
            'Disciple_Tools_Settings_Menu',
            'content'
        ] );
    }

    /**
     * Loads scripts and styles for dt utilities workflows.
     */
    public function dt_utilities_workflows_scripts() {
        if ( isset( $_GET["page"] ) && ( $_GET["page"] === 'dt_options' ) ) {
            if ( isset( $_GET["tab"] ) && $_GET["tab"] === 'workflows' ) {
                wp_register_style( 'bootstrap-5-css', 'https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css' );
                wp_enqueue_style( 'bootstrap-5-css' );

                wp_register_style( 'bootstrap-5-icons', 'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css' );
                wp_enqueue_style( 'bootstrap-5-icons' );

                dt_theme_enqueue_script( 'typeahead-jquery', 'dt-core/dependencies/typeahead/dist/jquery.typeahead.min.js', array( 'jquery' ), true );
                dt_theme_enqueue_style( 'typeahead-jquery-css', 'dt-core/dependencies/typeahead/dist/jquery.typeahead.min.css', array() );

                wp_register_style( 'daterangepicker-css', 'https://cdn.jsdelivr.net/npm/daterangepicker@3.1.0/daterangepicker.css' );
                wp_enqueue_style( 'daterangepicker-css' );
                wp_enqueue_script( 'daterangepicker-js', 'https://cdn.jsdelivr.net/npm/daterangepicker@3.1.0/daterangepicker.js', [ 'moment' ], '3.1.0', true );

                wp_enqueue_script( 'dt_utilities_workflows_script', disciple_tools()->admin_js_url . 'dt-utilities-workflows.js', [
                    'moment',
                    'jquery',
                    'lodash',
                    'typeahead-jquery',
                    'daterangepicker-js',
                ], filemtime( disciple_tools()->admin_js_path . 'dt-utilities-workflows.js' ), true );

                wp_localize_script(
                    "dt_utilities_workflows_script", "dt_workflows", array(
                        'workflows_design_section_hidden_post_types'       => $this->fetch_post_types(),
                        'workflows_design_section_hidden_post_field_types' => $this->fetch_post_field_types(),
                        'workflows_design_section_hidden_custom_actions'   => $this->fetch_custom_actions()
                    )
                );
            }
        }
    }

    public function add_tab( $tab ) {
        echo '<a href="' . esc_url( admin_url() ) . 'admin.php?page=dt_options&tab=workflows" class="nav-tab ';
        if ( $tab == 'workflows' ) {
            echo 'nav-tab-active';
        }
        echo '">' . esc_attr__( 'Workflows' ) . '</a>';
    }

    private function final_post_param_sanitization( $str ) {
        return str_replace( [ '&lt;', '&gt;' ], [ '<', '>' ], $str );
    }

    private function process_updates() {
        if ( isset( $_POST['workflows_design_section_nonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['workflows_design_section_nonce'] ) ), 'workflows_design_section_nonce' ) ) {
            if ( isset( $_POST['workflows_design_section_form_post_type_workflow'] ) ) {
                // Updating workflow
                $sanitized_input             = filter_var( wp_unslash( $_POST['workflows_design_section_form_post_type_workflow'] ), FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_NO_ENCODE_QUOTES );
                $updating_post_type_workflow = json_decode( $this->final_post_param_sanitization( $sanitized_input ) );

                // Process updating workflow based on its type - regular or default
                if ( $updating_post_type_workflow->is_regular_workflow ) {

                    // Fetch stored workflows
                    $current_post_type_workflow = $this->get_option_workflows( 'dt_workflows_post_types', $updating_post_type_workflow->post_type_id );

                    // Update and re-package
                    $post_type_id   = $updating_post_type_workflow->post_type_id;
                    $post_type_name = $updating_post_type_workflow->post_type_name;

                    $workflow_id      = $updating_post_type_workflow->workflow_id;
                    $workflow_name    = $updating_post_type_workflow->workflow_name;
                    $workflow_enabled = $updating_post_type_workflow->workflow_enabled;

                    $trigger    = $updating_post_type_workflow->trigger;
                    $conditions = $updating_post_type_workflow->conditions;
                    $actions    = $updating_post_type_workflow->actions;

                    $current_post_type_workflow->id   = $post_type_id;
                    $current_post_type_workflow->name = $post_type_name;

                    if ( ! isset( $current_post_type_workflow->workflows ) ) {
                        $current_post_type_workflow->workflows = (object) [];
                    }
                    $current_post_type_workflow->workflows->{$workflow_id} = (object) [
                        'id'         => $workflow_id,
                        'name'       => $workflow_name,
                        'enabled'    => $workflow_enabled,
                        'trigger'    => $trigger,
                        'conditions' => $conditions,
                        'actions'    => $actions
                    ];

                    // Save latest updates
                    $this->update_option_workflows( 'dt_workflows_post_types', $post_type_id, $current_post_type_workflow );

                } else { // Default Workflow

                    // Fetch stored default workflows
                    $current_default_workflow = $this->get_option_workflows( 'dt_workflows_defaults', $updating_post_type_workflow->post_type_id );

                    // Update and re-package
                    $workflow_id      = $updating_post_type_workflow->workflow_id;
                    $workflow_name    = $updating_post_type_workflow->workflow_name;
                    $workflow_enabled = $updating_post_type_workflow->workflow_enabled;

                    if ( ! isset( $current_default_workflow->workflows ) ) {
                        $current_default_workflow->workflows = (object) [];
                    }
                    $current_default_workflow->workflows->{$workflow_id} = (object) [
                        'id'      => $workflow_id,
                        'name'    => $workflow_name,
                        'enabled' => $workflow_enabled
                    ];

                    // Save latest updates
                    $this->update_option_workflows( 'dt_workflows_defaults', $updating_post_type_workflow->post_type_id, $current_default_workflow );

                }
            }
        }
    }

    private function get_option_workflows( $option_id, $post_type_id ) {
        $option           = get_option( $option_id );
        $option_workflows = ( ! empty( $option ) ) ? json_decode( $option ) : (object) [];

        return ( isset( $option_workflows->{$post_type_id} ) ) ? $option_workflows->{$post_type_id} : (object) [];
    }

    private function update_option_workflows( $option_id, $post_type_id, $workflow ) {
        $option           = get_option( $option_id );
        $option_workflows = ( ! empty( $option ) ) ? json_decode( $option ) : (object) [];

        $option_workflows->{$post_type_id} = $workflow;

        // Save changes.
        update_option( $option_id, json_encode( $option_workflows ) );
    }

    private function fetch_selected_post_type(): array {

        $selected_post_type_id   = null;
        $selected_post_type_name = null;

        // Determine selected post type id
        if ( isset( $_POST['workflows_post_types_section_nonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['workflows_post_types_section_nonce'] ) ), 'workflows_post_types_section_nonce' ) ) {
            $selected_post_type_id   = ( isset( $_POST['workflows_post_types_section_form_post_type_id'] ) ) ? sanitize_text_field( wp_unslash( $_POST['workflows_post_types_section_form_post_type_id'] ) ) : '';
            $selected_post_type_name = ( isset( $_POST['workflows_post_types_section_form_post_type_name'] ) ) ? sanitize_text_field( wp_unslash( $_POST['workflows_post_types_section_form_post_type_name'] ) ) : '';

        } elseif ( isset( $_POST['workflows_design_section_nonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['workflows_design_section_nonce'] ) ), 'workflows_design_section_nonce' ) ) {
            if ( isset( $_POST['workflows_design_section_form_post_type_workflow'] ) ) {
                $sanitized_input             = filter_var( wp_unslash( $_POST['workflows_design_section_form_post_type_workflow'] ), FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_NO_ENCODE_QUOTES );
                $updating_post_type_workflow = json_decode( $this->final_post_param_sanitization( $sanitized_input ) );

                $selected_post_type_id   = $updating_post_type_workflow->post_type_id;
                $selected_post_type_name = $updating_post_type_workflow->post_type_name;
            }
        }

        // Assuming we have a valid selection, return details
        if ( ! empty( $selected_post_type_id ) && ! empty( $selected_post_type_name ) ) {
            return [
                'id'   => $selected_post_type_id,
                'name' => $selected_post_type_name
            ];
        }

        return [];
    }

    public function content( $tab ) {
        if ( 'workflows' == $tab ) {

            $this->process_updates();
            $selected_post_type = $this->fetch_selected_post_type();

            $this->template( 'begin' );

            $this->workflows_post_types_section( $selected_post_type );
            $this->workflows_management_section( $selected_post_type );
            $this->workflows_design_section( $selected_post_type );

            $this->template( 'end' );

        }
    }

    private function workflows_post_types_section( $selected_post_type ) {
        $this->box( 'top', 'Edit Workflows', [ "col_span" => 1 ] );
        ?>

        <table style="min-width: 100%; border: 0;">
            <tbody>
            <tr>
                <td style="vertical-align: middle;">For what post type?</td>

                <?php
                $selected_post_type_id = $selected_post_type['id'] ?? '';
                $post_types            = $this->fetch_post_types();
                foreach ( $post_types as $post_type ) {
                    ?>

                    <td>
                        <a class="button <?php echo ( $post_type['id'] === $selected_post_type_id ) ? 'button-primary' : ''; ?> float-right workflows-post-types-section-buttons"><?php echo esc_attr( $post_type['name'] ); ?></a>
                        <input type="hidden" id="workflows_post_types_section_post_type_id"
                               value="<?php echo esc_attr( $post_type['id'] ) ?>">
                        <input type="hidden" id="workflows_post_types_section_post_type_name"
                               value="<?php echo esc_attr( $post_type['name'] ) ?>">
                    </td>

                    <?php
                }
                ?>

            </tr>
            </tbody>
        </table>

        <form method="POST" id="workflows_post_types_section_form">
            <input type="hidden" id="workflows_post_types_section_nonce" name="workflows_post_types_section_nonce"
                   value="<?php echo esc_attr( wp_create_nonce( 'workflows_post_types_section_nonce' ) ) ?>"/>

            <input type="hidden" value="" id="workflows_post_types_section_form_post_type_id"
                   name="workflows_post_types_section_form_post_type_id"/>

            <input type="hidden" value="" id="workflows_post_types_section_form_post_type_name"
                   name="workflows_post_types_section_form_post_type_name"/>
        </form>

        <?php
        $this->box( 'bottom' );
    }

    private function workflows_management_section( $selected_post_type ) {

        if ( ! empty( $selected_post_type ) ) {

            echo '<div id="workflows_management_section_div">';

            // Capture hidden values, to be used further down stream
            $option_post_type_workflows = $this->get_option_workflows( 'dt_workflows_post_types', $selected_post_type['id'] );
            echo '<input type="hidden" id="workflows_management_section_hidden_option_post_type_workflows" value="' . esc_attr( json_encode( $option_post_type_workflows ) ) . '">';

            $option_default_workflows = $this->get_option_workflows( 'dt_workflows_defaults', $selected_post_type['id'] );
            echo '<input type="hidden" id="workflows_management_section_hidden_option_default_workflows" value="' . esc_attr( json_encode( $option_default_workflows ) ) . '">';

            $filtered_workflows_defaults = apply_filters( 'dt_workflows', [], $selected_post_type['id'] );
            echo '<input type="hidden" id="workflows_management_section_hidden_filtered_workflows_defaults" value="' . esc_attr( json_encode( $filtered_workflows_defaults ) ) . '">';

            $this->box( 'top', 'Add new workflows or modify existing ones on ' . $selected_post_type['name'], [ "col_span" => 1 ] );
            ?>

            <table style="min-width: 100%; border: 0;">
                <tbody>
                <tr>
                    <td>
                        <span style="float:right;">
                            <a id="workflows_management_section_new_but"
                               class="button float-left"><?php esc_html_e( "New Workflow", 'disciple_tools' ) ?></a>
                        </span>
                    </td>
                </tr>
                <tr>
                    <td>
                        <table style="min-width: 100%;" class="widefat striped">
                            <thead>
                            <tr>
                                <th style="text-align: center;">Enabled</th>
                                <th>Workflow</th>
                                <th style="text-align: center;">Type</th>
                                <th>Updated Fields</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            // List owned/custom workflows
                            if ( ! empty( $option_post_type_workflows ) && isset( $option_post_type_workflows->workflows ) ) {

                                // Sort detected workflows by name
                                $workflows = $this->sort_workflows_by_name( (array) $option_post_type_workflows->workflows );

                                // Iterate through sorted custom workflow list
                                foreach ( $workflows as $workflow ) {
                                    ?>

                                    <tr>
                                        <td style="text-align: center;">
                                            <input type="checkbox"
                                                   disabled <?php echo ( $workflow->enabled ) ? 'checked' : ''; ?>>
                                        </td>
                                        <td>
                                            <a href="#" onclick="event.preventDefault();"
                                               class="workflows-management-section-workflow-name"
                                               data-workflow-id="<?php echo esc_attr( $workflow->id ); ?>"
                                               data-workflow-name="<?php echo esc_attr( $workflow->name ); ?>"><?php echo esc_attr( $workflow->name ); ?></a>
                                        </td>
                                        <td style="text-align: center;">
                                            Custom
                                        </td>
                                        <td>
                                            <?php echo esc_attr( $this->fields_to_string_list( $workflow->actions ) ); ?>
                                        </td>
                                    </tr>

                                    <?php
                                }
                            }

                            // List default filtered workflows
                            if ( ! empty( $filtered_workflows_defaults ) ) {

                                // Sort detected default workflows by name
                                $workflows_defaults = $this->sort_workflows_by_name( $filtered_workflows_defaults );

                                // Iterate through sorted workflow list
                                foreach ( $workflows_defaults as $workflow_default ) {
                                    ?>

                                    <tr>
                                        <td style="text-align: center;">

                                            <?php
                                            // Update default workflow state accordingly
                                            if ( ! empty( $option_default_workflows ) && isset( $option_default_workflows->workflows->{$workflow_default->id} ) ) {
                                                $workflow_default->enabled = $option_default_workflows->workflows->{$workflow_default->id}->enabled;
                                            }
                                            ?>

                                            <input type="checkbox"
                                                   disabled <?php echo ( $workflow_default->enabled ) ? 'checked' : ''; ?>>
                                        </td>
                                        <td>
                                            <a href="#" onclick="event.preventDefault();"
                                               class="workflows-management-section-workflow-name"
                                               data-workflow-id="<?php echo 'default_' . esc_attr( $workflow_default->id ); ?>"
                                               data-workflow-name="<?php echo esc_attr( $workflow_default->name ); ?>"><?php echo esc_attr( $workflow_default->name ); ?></a>
                                        </td>
                                        <td style="text-align: center;">
                                            Default
                                        </td>
                                        <td>
                                            <?php echo esc_attr( $this->fields_to_string_list( $workflow_default->actions ) ); ?>
                                        </td>
                                    </tr>

                                    <?php
                                }
                            }
                            ?>
                            </tbody>
                        </table>
                    </td>
                </tr>
                </tbody>
            </table>

            <?php
            $this->box( 'bottom' );

            echo '</div>';

        }
    }

    private function fields_to_string_list( $fields ): string {
        $names = [];

        if ( ! empty( $fields ) ) {
            foreach ( $fields as $field ) {
                if ( ! in_array( $field->field_name, $names ) ) {
                    $names[] = $field->field_name;
                }
            }
        }

        return implode( ', ', $names );
    }

    private function workflows_design_section( $selected_post_type ) {

        if ( ! empty( $selected_post_type ) ) {

            echo '<div id="workflows_design_section_div" style="display: none;">';

            // Capture hidden values, to be used further down stream
            echo '<input type="hidden" id="workflows_design_section_hidden_selected_post_type_id" value="' . esc_attr( $selected_post_type['id'] ?? '' ) . '">';
            echo '<input type="hidden" id="workflows_design_section_hidden_selected_post_type_name" value="' . esc_attr( $selected_post_type['name'] ?? '' ) . '">';
            echo '<input type="hidden" id="workflows_design_section_hidden_workflow_id" value="' . esc_attr( time() ) . '">';

            $this->box( 'top', 'Workflow Steps', [ "col_span" => 1 ] );
            ?>

            <div id="workflows_design_section_steps" class="container">

                <?php
                $this->workflows_design_section_step1();
                $this->workflows_design_section_step2();
                $this->workflows_design_section_step3();
                $this->workflows_design_section_step4();
                ?>

            </div>
            <br><br>

            <span style="float:right;">
                <a style="display: none;" id="workflows_design_section_save_but"
                   class="button float-right"><?php esc_html_e( "Save", 'disciple_tools' ) ?></a>
            </span>

            <form method="POST" id="workflows_design_section_form">
                <input type="hidden" id="workflows_design_section_nonce" name="workflows_design_section_nonce"
                       value="<?php echo esc_attr( wp_create_nonce( 'workflows_design_section_nonce' ) ) ?>"/>

                <input type="hidden" value="" id="workflows_design_section_form_post_type_workflow"
                       name="workflows_design_section_form_post_type_workflow"/>
            </form>

            <?php
            $this->box( 'bottom' );
            echo '</div>';
        }
    }

    private function workflows_design_section_step1() {
        ?>
        <div id="workflows_design_section_step1" class="row" style="display: none;">
            <div class="col-auto text-center flex-column d-none d-sm-flex">
                <div class="row h-50">
                    <div class="col">&nbsp;</div>
                    <div class="col">&nbsp;</div>
                </div>
                <h5 class="m-2">
                    <span class="badge rounded-circle bg-success border-success">1</span>
                </h5>
                <div class="row h-50">
                    <div class="col border-end">&nbsp;</div>
                    <div class="col">&nbsp;</div>
                </div>
            </div>
            <div class="col py-2">
                <div class="card border-success shadow">
                    <div class="card-body">
                        <div class="float-end">When triggers fire</div>
                        <h4 class="card-title text-muted">Step: 1</h4>
                        <br>

                        <!-- Trigger Options -->
                        <div class="btn-group" role="group" aria-label="Trigger options">
                            <input type="radio" class="btn-check" name="workflows_design_section_step1_triggers"
                                   id="workflows_design_section_step1_trigger_created" autocomplete="off"
                                   checked>
                            <label class="btn btn-outline-success" for="workflows_design_section_step1_trigger_created">
                                <i class="bi-folder-plus" style="font-size: 2rem;"></i>
                                <br>Record Created
                            </label>

                            <input type="radio" class="btn-check" name="workflows_design_section_step1_triggers"
                                   id="workflows_design_section_step1_trigger_updated"
                                   autocomplete="off">
                            <label class="btn btn-outline-success" for="workflows_design_section_step1_trigger_updated">
                                <i class="bi-pencil-square" style="font-size: 2rem;"></i>
                                <br>Field Updated
                            </label>
                        </div>


                        <br><br>
                        <span style="float:right;">
                                <a id="workflows_design_section_step1_next_but"
                                   class="button float-right"><?php esc_html_e( "Next", 'disciple_tools' ) ?></a>
                            </span>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    private function workflows_design_section_step2() {
        ?>
        <div id="workflows_design_section_step2" class="row" style="display: none;">
            <div class="col-auto text-center flex-column d-none d-sm-flex">
                <div class="row h-50">
                    <div class="col border-end">&nbsp;</div>
                    <div class="col">&nbsp;</div>
                </div>
                <h5 class="m-2">
                    <span class="badge rounded-circle bg-success border-success">2</span>
                </h5>
                <div class="row h-50">
                    <div class="col border-end">&nbsp;</div>
                    <div class="col">&nbsp;</div>
                </div>
            </div>
            <div class="col py-2">
                <div class="card border-success shadow">
                    <div class="card-body">
                        <div class="float-end">..and if conditions are true</div>
                        <h4 class="card-title text-muted">Step: 2</h4>
                        <br>

                        <table border="0">
                            <tbody>
                            <tr id="workflows_design_section_step2_fields_tr">
                                <td>
                                    fields:
                                </td>
                                <td>
                                    <select style="min-width: 100%;" id="workflows_design_section_step2_fields">
                                        <option disabled selected value="">--- select field ---</option>
                                    </select>
                                </td>
                                <td></td>
                            </tr>
                            <tr id="workflows_design_section_step2_conditions_tr">
                                <td>
                                    condition:
                                </td>
                                <td>
                                    <select style="min-width: 100%;" id="workflows_design_section_step2_conditions">
                                        <option disabled selected value="">--- select condition ---</option>
                                    </select>
                                </td>
                                <td></td>
                            </tr>
                            <tr id="workflows_design_section_step2_condition_value_tr">
                                <td>
                                    value:
                                </td>
                                <td>
                                    <input id="workflows_design_section_step2_condition_value_id" type="hidden"
                                           value="">
                                    <input id="workflows_design_section_step2_condition_value_object_id" type="hidden"
                                           value="">

                                    <div id="workflows_design_section_step2_condition_value_div">
                                        --- dynamic condition value field ---
                                    </div>
                                </td>
                                <td>
                                        <span style="float:right;">
                                            <a id="workflows_design_section_step2_condition_add"
                                               class="button float-right"><?php esc_html_e( "Add", 'disciple_tools' ) ?></a>
                                        </span>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="3">
                                    <span id="workflows_design_section_step2_exception_message"
                                          style="color:#ff0000"></span>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="3">
                                    <table style="min-width: 100%;" border="0"
                                           id="workflows_design_section_step2_conditions_table">
                                        <thead>
                                        <tr>
                                            <th>field</th>
                                            <th>condition</th>
                                            <th>value</th>
                                            <th></th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                            </tbody>
                        </table>

                        <br><br>
                        <span style="float:right;">
                            <a id="workflows_design_section_step2_next_but"
                               class="button float-right"><?php esc_html_e( "Next", 'disciple_tools' ) ?></a>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    private function workflows_design_section_step3() {
        ?>
        <div id="workflows_design_section_step3" class="row" style="display: none;">
            <div class="col-auto text-center flex-column d-none d-sm-flex">
                <div class="row h-50">
                    <div class="col border-end">&nbsp;</div>
                    <div class="col">&nbsp;</div>
                </div>
                <h5 class="m-2">
                    <span class="badge rounded-circle bg-success border-success">3</span>
                </h5>
                <div class="row h-50">
                    <div class="col border-end">&nbsp;</div>
                    <div class="col">&nbsp;</div>
                </div>
            </div>
            <div class="col py-2">
                <div class="card border-success shadow">
                    <div class="card-body">
                        <div class="float-end">Then execute</div>
                        <h4 class="card-title text-muted">Step: 3</h4>
                        <br>

                        <table border="0">
                            <tbody>
                            <tr id="workflows_design_section_step3_fields_tr">
                                <td>
                                    fields:
                                </td>
                                <td>
                                    <select style="min-width: 100%;" id="workflows_design_section_step3_fields">
                                        <option disabled selected value="">--- select field ---</option>
                                    </select>
                                </td>
                                <td></td>
                            </tr>
                            <tr id="workflows_design_section_step3_actions_tr">
                                <td>
                                    action:
                                </td>
                                <td>
                                    <select style="min-width: 100%;" id="workflows_design_section_step3_actions">
                                        <option disabled selected value="">--- select action ---</option>
                                    </select>
                                </td>
                                <td></td>
                            </tr>
                            <tr id="workflows_design_section_step3_action_value_tr">
                                <td>
                                    value:
                                </td>
                                <td>
                                    <input id="workflows_design_section_step3_action_value_id" type="hidden"
                                           value="">
                                    <input id="workflows_design_section_step3_action_value_object_id" type="hidden"
                                           value="">

                                    <div id="workflows_design_section_step3_action_value_div">
                                        --- dynamic action value field ---
                                    </div>
                                </td>
                                <td>
                                        <span style="float:right;">
                                            <a id="workflows_design_section_step3_action_add"
                                               class="button float-right"><?php esc_html_e( "Add", 'disciple_tools' ) ?></a>
                                        </span>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="3">
                                    <span id="workflows_design_section_step3_exception_message"
                                          style="color:#ff0000"></span>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="3">
                                    <table style="min-width: 100%;" border="0"
                                           id="workflows_design_section_step3_actions_table">
                                        <thead>
                                        <tr>
                                            <th>field</th>
                                            <th>action</th>
                                            <th>value</th>
                                            <th></th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                            </tbody>
                        </table>

                        <br><br>
                        <span style="float:right;">
                            <a id="workflows_design_section_step3_next_but"
                               class="button float-right"><?php esc_html_e( "Next", 'disciple_tools' ) ?></a>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    private function workflows_design_section_step4() {
        ?>
        <div id="workflows_design_section_step4" class="row" style="display: none;">
            <div class="col-auto text-center flex-column d-none d-sm-flex">
                <div class="row h-50">
                    <div class="col border-end">&nbsp;</div>
                    <div class="col">&nbsp;</div>
                </div>
                <h5 class="m-2">
                    <span class="badge rounded-circle bg-success border-success">4</span>
                </h5>
                <div class="row h-50">
                    <div class="col border-end">&nbsp;</div>
                    <div class="col">&nbsp;</div>
                </div>
            </div>
            <div class="col py-2">
                <div class="card border-success shadow">
                    <div class="card-body">
                        <div class="float-end">Name your workflow</div>
                        <h4 class="card-title text-muted">Step: 4</h4>

                        <table style="min-width: 100%;" border="0">
                            <thead>
                            <tr>
                                <th></th>
                                <th style="text-align: center;">enabled</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>
                                    <input type="text" style="min-width: 100%;"
                                           id="workflows_design_section_step4_title"
                                           placeholder="Name your workflow">
                                </td>
                                <td style="text-align: center;vertical-align: center;">
                                    <input type="checkbox"
                                           id="workflows_design_section_step4_enabled">
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <span id="workflows_design_section_step4_exception_message"
                                          style="color:#ff0000"></span>
                                </td>
                            </tr>
                            </tbody>
                        </table>

                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    private function ignore_field_types(): array {
        return [
            'array',
            'task',
            'location_meta',
            'post_user_meta',
            'datetime_series',
            'hash'
        ];
    }

    private function fetch_post_types(): array {

        $post_types = [];

        $dt_post_types = DT_Posts::get_post_types();
        if ( ! empty( $dt_post_types ) ) {

            $field_types_to_ignore = $this->ignore_field_types();

            foreach ( $dt_post_types as $dt_post_type ) {
                $dt_post_type_settings = DT_Posts::get_post_settings( $dt_post_type );

                $fields = [];
                foreach ( $dt_post_type_settings['fields'] as $key => $dt_field ) {

                    if ( ! in_array( $dt_field['type'], $field_types_to_ignore ) && ! ( $dt_field['hidden'] ?? false ) ) {
                        $fields[] = [
                            'id'        => $key,
                            'name'      => $dt_field['name'],
                            'type'      => $dt_field['type'],
                            'defaults'  => $dt_field['default'] ?? '',
                            'post_type' => $dt_field['post_type'] ?? ''
                        ];
                    }
                }

                $post_type                = $dt_post_type_settings['post_type'];
                $post_types[ $post_type ] = [
                    'id'       => $post_type,
                    'name'     => $dt_post_type_settings['label_plural'],
                    'fields'   => $fields,
                    'base_url' => rest_url(),
                    'wp_nonce' => esc_attr( wp_create_nonce( 'wp_rest' ) )
                ];
            }
        }

        return $post_types;
    }

    private function fetch_post_field_types(): array {

        $post_field_types = [];

        $dt_post_types = DT_Posts::get_post_types();
        if ( ! empty( $dt_post_types ) ) {
            foreach ( $dt_post_types as $dt_post_type ) {
                $dt_post_type_settings = DT_Posts::get_post_settings( $dt_post_type );
                foreach ( $dt_post_type_settings['fields'] as $key => $dt_field ) {
                    if ( ! in_array( $dt_field['type'], $post_field_types ) ) {
                        $post_field_types[] = $dt_field['type'];
                    }
                }
            }
        }

        return $post_field_types;
    }

    private function fetch_custom_actions(): array {
        $filtered_custom_actions = apply_filters( 'dt_workflows_custom_actions', [] );

        // Only focus on the actions which have been flagged for display
        $actions = [];
        foreach ( $filtered_custom_actions as $action ) {
            if ( ! empty( $action ) && $action->displayed ) {
                $actions[] = $action;
            }
        }

        // Piggyback off workflow sorter, as the shapes are identical... ;)
        return $this->sort_workflows_by_name( $actions );
    }

    private function sort_workflows_by_name( $workflows ): array {
        if ( ! empty( $workflows ) ) {
            usort( $workflows, function ( $a, $b ) {
                return strcmp( $a->name, $b->name );
            } );
        }

        return $workflows;
    }

}

Disciple_Tools_Tab_Workflows::instance();
