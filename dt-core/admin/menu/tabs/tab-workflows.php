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
        add_action( 'dt_utilities_tab_menu', [ $this, 'add_tab' ], 125, 1 );
        add_action( 'dt_utilities_tab_content', [ $this, 'content' ], 125, 1 );

        parent::__construct();
    } // End __construct()

    public function add_submenu() {
        add_submenu_page( 'dt_utilities', __( 'Workflows', 'disciple_tools' ), __( 'Workflows', 'disciple_tools' ), 'manage_dt', 'dt_utilities&tab=workflows', [
            'Disciple_Tools_Settings_Menu',
            'content'
        ] );
    }

    public function add_tab( $tab ) {
        echo '<a href="' . esc_url( admin_url() ) . 'admin.php?page=dt_utilities&tab=workflows" class="nav-tab ';
        if ( $tab == 'workflows' ) {
            echo 'nav-tab-active';
        }
        echo '">' . esc_attr__( 'Workflows' ) . '</a>';
    }

    private function process_updates() {
        if ( isset( $_POST['workflows_design_section_nonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['workflows_design_section_nonce'] ) ), 'workflows_design_section_nonce' ) ) {
            if ( isset( $_POST['workflows_design_section_form_post_type_workflow'] ) ) {
                // Updating workflow
                $updating_post_type_workflow = json_decode( sanitize_text_field( wp_unslash( $_POST['workflows_design_section_form_post_type_workflow'] ) ) );

                // Fetch stored workflows
                $current_post_type_workflow = $this->get_option_post_type_workflows( $updating_post_type_workflow->post_type_id );

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
                $this->update_option_post_type_workflows( $post_type_id, $current_post_type_workflow );
            }
        }
    }

    private function get_option_post_type_workflows( $post_type_id ) {
        $option                     = get_option( 'dt_workflows_post_types' );
        $option_post_type_workflows = ( ! empty( $option ) ) ? json_decode( $option ) : (object) [];

        return ( isset( $option_post_type_workflows->{$post_type_id} ) ) ? $option_post_type_workflows->{$post_type_id} : (object) [];
    }

    private function update_option_post_type_workflows( $post_type_id, $post_type_workflow ) {
        $option                     = get_option( 'dt_workflows_post_types' );
        $option_post_type_workflows = ( ! empty( $option ) ) ? json_decode( $option ) : (object) [];

        $option_post_type_workflows->{$post_type_id} = $post_type_workflow;

        // Save changes.
        update_option( 'dt_workflows_post_types', json_encode( $option_post_type_workflows ) );
    }

    private function fetch_selected_post_type(): array {
        if ( isset( $_POST['workflows_post_types_section_nonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['workflows_post_types_section_nonce'] ) ), 'workflows_post_types_section_nonce' ) ) {

            $selected_post_type_id   = ( isset( $_POST['workflows_post_types_section_form_post_type_id'] ) ) ? sanitize_text_field( wp_unslash( $_POST['workflows_post_types_section_form_post_type_id'] ) ) : '';
            $selected_post_type_name = ( isset( $_POST['workflows_post_types_section_form_post_type_name'] ) ) ? sanitize_text_field( wp_unslash( $_POST['workflows_post_types_section_form_post_type_name'] ) ) : '';

            if ( ! empty( $selected_post_type_id ) && ! empty( $selected_post_type_name ) ) {

                return [
                    'id'   => $selected_post_type_id,
                    'name' => $selected_post_type_name
                ];
            }
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
            $post_type_workflows = $this->get_option_post_type_workflows( $selected_post_type['id'] );
            echo '<input type="hidden" id="workflows_management_section_hidden_post_type_workflows" value="' . esc_attr( json_encode( $post_type_workflows ) ) . '">';

            $this->box( 'top', 'Add new workflows or modify existing ones on ' . $selected_post_type['name'], [ "col_span" => 1 ] );
            ?>

            <table style="min-width: 100%; border: 0;">
                <tbody>
                <tr>
                    <td>
                        Modify an existing workflow
                    </td>
                    <td>
                        <select style="min-width: 100%;" id="workflows_management_section_select">
                            <option disabled selected value="">
                                --- available <?php echo esc_attr( strtolower( $selected_post_type['name'] ) ); ?>
                                workflows ---
                            </option>

                            <?php
                            if ( ! empty( $post_type_workflows ) && isset( $post_type_workflows->workflows ) ) {

                                // Sort detected workflows by name
                                $workflows = (array) $post_type_workflows->workflows;
                                usort( $workflows, function ( $a, $b ) {
                                    return strcmp( $a->name, $b->name );
                                } );

                                // Iterate through sorted workflow list
                                foreach ( $workflows as $workflow ) {
                                    echo '<option value="' . esc_attr( $workflow->id ) . '">' . esc_attr( $workflow->name ) . '</option>';
                                }
                            }
                            ?>

                        </select>
                    </td>
                </tr>
                <tr>
                    <td>
                        Create a new workflow
                    </td>
                    <td>
                        <span style="float:left;">
                            <a id="workflows_management_section_new_but"
                               class="button float-left"><?php esc_html_e( "New Workflow", 'disciple_tools' ) ?></a>
                        </span>
                    </td>
                </tr>
                </tbody>
            </table>

            <?php
            $this->box( 'bottom' );

            echo '</div>';

        }
    }

    private function workflows_design_section( $selected_post_type ) {

        if ( ! empty( $selected_post_type ) ) {

            echo '<div id="workflows_design_section_div" style="display: none;">';

            // Capture hidden values, to be used further down stream
            echo '<input type="hidden" id="workflows_design_section_hidden_post_types" value="' . esc_attr( json_encode( $this->fetch_post_types() ) ) . '">';
            echo '<input type="hidden" id="workflows_design_section_hidden_post_field_types" value="' . esc_attr( json_encode( $this->fetch_post_field_types() ) ) . '">';
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
                            <tr>
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
                            <tr>
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
                            <tr>
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

                                        <!--
                                        TODO: Update all resets; which reference this field....!!
                                        <input type="text" style="min-width: 100%;"
                                               id="workflows_design_section_step2_condition_value"
                                               placeholder="Condition value">
                                               -->
                                    </div>
                                <td>
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
                            <tr>
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
                            <tr>
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
                            <tr>
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

                                        <!--
                                        TODO: Update all resets; which reference this field....!!
                                        <input type="text" style="min-width: 100%;"
                                               id="workflows_design_section_step3_action_value"
                                               placeholder="Action value">
                                               -->
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

                    if ( ! in_array( $dt_field['type'], $field_types_to_ignore ) ) {
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

}

Disciple_Tools_Tab_Workflows::instance();
