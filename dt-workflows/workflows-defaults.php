<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

/**
 * Class Disciple_Tools_Workflows_Execution_Handler
 */
class Disciple_Tools_Workflows_Defaults {

    public static $trigger_created = [ 'id' => 'created', 'label' => 'Record Created' ];
    public static $trigger_updated = [ 'id' => 'updated', 'label' => 'Field Updated' ];

    public static $condition_equals = [ 'id' => 'equals', 'label' => 'Equals' ];
    public static $condition_not_equals = [ 'id' => 'not_equals', 'label' => 'Not Equal' ];
    public static $condition_greater = [ 'id' => 'greater', 'label' => 'Greater Than' ];
    public static $condition_less = [ 'id' => 'less', 'label' => 'Less Than' ];
    public static $condition_greater_equals = [ 'id' => 'greater_equals', 'label' => 'Greater Than or Equals' ];
    public static $condition_less_equals = [ 'id' => 'less_equals', 'label' => 'Less Than or Equals' ];
    public static $condition_contains = [ 'id' => 'contains', 'label' => 'Contains' ];
    public static $condition_not_contain = [ 'id' => 'not_contain', 'label' => 'Not Contain' ];

    public static $action_update = [ 'id' => 'update', 'label' => 'Updated To' ];
    public static $action_append = [ 'id' => 'append', 'label' => 'Appended With' ];
    public static $action_connect = [ 'id' => 'connect', 'label' => 'Connect To' ];
    public static $action_remove = [ 'id' => 'remove', 'label' => 'Removal Of' ];

    public function __construct() {
        add_filter( 'dt_workflows', [ $this, 'fetch_default_workflows_filter' ], 10, 2 );
    }

    public function fetch_default_workflows_filter( $workflows, $post_type ) {
        /*
         * Please ensure workflow ids are both static and unique; as they
         * will be used further downstream within admin view and execution handler.
         * Dynamically generated timestamps will not work, as they will regularly
         * change. Therefore, maybe a prefix nd a constant: E.g: contacts_00001
         *
         * Also, review dt-utilities-workflows.js so as to determine which condition
         * and action event types can be assigned to which field type!
         */

        switch ( $post_type ) {
            case 'contacts':
                $this->build_default_workflows_contacts( $workflows );
                break;
            case 'groups':
                $this->build_default_workflows_groups( $workflows );
                break;
        }

        return $workflows;
    }

    public function new_condition( $condition, $field, $value ) {
        return $this->new_event( $condition, $field, $value );
    }

    public function new_action( $action, $field, $value ) {
        return $this->new_event( $action, $field, $value );
    }

    private function new_event( $event, $field, $value ) {
        return (object) [
            'id'         => $event['id'],
            'name'       => $event['label'],
            'field_id'   => $field['id'],
            'field_name' => $field['label'],
            'value'      => $value['id'],
            'value_name' => $value['label']
        ];
    }

    private function build_default_workflows_contacts( &$workflows ) {
    }

    private function build_default_workflows_groups( &$workflows ) {
        $dt_contacts_fields = DT_Posts::get_post_field_settings( 'groups' );

        $workflows[] = (object) [
            'id'         => 'groups_00001',
            'name'       => 'Link Church Health Commitment -> Church Group Type',
            'enabled'    => false, // Can be enabled via admin view
            'trigger'    => self::$trigger_updated['id'],
            'conditions' => [
                $this->new_condition( self::$condition_contains,
                    [
                        'id'    => 'health_metrics',
                        'label' => $dt_contacts_fields['health_metrics']['name']
                    ], [
                        'id'    => 'church_commitment',
                        'label' => $dt_contacts_fields['health_metrics']['default']['church_commitment']['label']
                    ]
                )
            ],
            'actions'    => [
                $this->new_action( self::$action_update,
                    [
                        'id'    => 'group_type',
                        'label' => $dt_contacts_fields['group_type']['name']
                    ], [
                        'id'    => 'church',
                        'label' => $dt_contacts_fields['group_type']['default']['church']['label']
                    ]
                )
            ]
        ];
        $workflows[] = (object) [
            'id'         => 'groups_00002',
            'name'       => 'Link Church Group Type -> Church Health Commitment',
            'enabled'    => false, // Can be enabled via admin view
            'trigger'    => self::$trigger_updated['id'],
            'conditions' => [
                $this->new_condition( self::$condition_contains,
                    [
                        'id'    => 'group_type',
                        'label' => $dt_contacts_fields['group_type']['name']
                    ], [
                        'id'    => 'church',
                        'label' => $dt_contacts_fields['group_type']['default']['church']['label']
                    ]
                )
            ],
            'actions'    => [
                $this->new_action( self::$action_append,
                    [
                        'id'    => 'health_metrics',
                        'label' => $dt_contacts_fields['health_metrics']['name']
                    ], [
                        'id'    => 'church_commitment',
                        'label' => $dt_contacts_fields['health_metrics']['default']['church_commitment']['label']
                    ]
                )
            ]
        ];
    }
}
