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
    public static $condition_not_equals = [ 'id' => 'not_equals', 'label' => "Doesn't equal" ];
    public static $condition_greater = [ 'id' => 'greater', 'label' => 'Greater than' ];
    public static $condition_less = [ 'id' => 'less', 'label' => 'Less than' ];
    public static $condition_greater_equals = [ 'id' => 'greater_equals', 'label' => 'Greater than or equals' ];
    public static $condition_less_equals = [ 'id' => 'less_equals', 'label' => 'Less than or equals' ];
    public static $condition_contains = [ 'id' => 'contains', 'label' => 'Contains' ];
    public static $condition_not_contain = [ 'id' => 'not_contain', 'label' => "Doesn't contain" ];
    public static $condition_is_set = [ 'id' => 'is_set', 'label' => 'Has any value and not empty' ];
    public static $condition_not_set = [ 'id' => 'not_set', 'label' => 'Has no value or is empty' ];

    public static $action_update = [ 'id' => 'update', 'label' => 'Updated To' ];
    public static $action_append = [ 'id' => 'append', 'label' => 'Appended With' ];
    public static $action_connect = [ 'id' => 'connect', 'label' => 'Connect To' ];
    public static $action_remove = [ 'id' => 'remove', 'label' => 'Removal Of' ];
    public static $action_custom = [ 'id' => 'custom', 'label' => 'Custom Action' ];

    private static $custom_action_people_group_connections = [
        'id'    => 'groups_00003_custom_action_people_group_connections',
        'label' => 'Auto-Add People Groups'
    ];

    public function __construct() {
        add_filter( 'dt_workflows', [ $this, 'fetch_default_workflows_filter' ], 10, 2 );
        add_filter( 'dt_workflows_custom_actions', function ( $actions ) {
            $actions[] = (object) [
                'id'        => self::$custom_action_people_group_connections['id'],
                'name'      => self::$custom_action_people_group_connections['label'],
                'displayed' => true // Within admin workflow builder view?
            ];

            return $actions;
        }, 10, 1 );

        add_action( self::$custom_action_people_group_connections['id'], [
            $this,
            'custom_action_people_group_connections'
        ], 10, 3 );
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

    public static function new_condition( $condition, $field, $value ) {
        return self::new_event( $condition, $field, $value );
    }

    public static function new_action( $action, $field, $value ) {
        return self::new_event( $action, $field, $value );
    }

    private static function new_event( $event, $field, $value ) {
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
        $dt_fields = DT_Posts::get_post_field_settings( 'groups' );

        $workflows[] = (object) [
            'id'         => 'groups_00001',
            'name'       => 'Link Church Health Commitment -> Church Group Type',
            'enabled'    => false, // Can be enabled via admin view
            'trigger'    => self::$trigger_updated['id'],
            'conditions' => [
                self::new_condition( self::$condition_contains,
                    [
                        'id'    => 'health_metrics',
                        'label' => $dt_fields['health_metrics']['name']
                    ], [
                        'id'    => 'church_commitment',
                        'label' => $dt_fields['health_metrics']['default']['church_commitment']['label']
                    ]
                )
            ],
            'actions'    => [
                self::new_action( self::$action_update,
                    [
                        'id'    => 'group_type',
                        'label' => $dt_fields['group_type']['name']
                    ], [
                        'id'    => 'church',
                        'label' => $dt_fields['group_type']['default']['church']['label']
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
                self::new_condition( self::$condition_contains,
                    [
                        'id'    => 'group_type',
                        'label' => $dt_fields['group_type']['name']
                    ], [
                        'id'    => 'church',
                        'label' => $dt_fields['group_type']['default']['church']['label']
                    ]
                )
            ],
            'actions'    => [
                self::new_action( self::$action_append,
                    [
                        'id'    => 'health_metrics',
                        'label' => $dt_fields['health_metrics']['name']
                    ], [
                        'id'    => 'church_commitment',
                        'label' => $dt_fields['health_metrics']['default']['church_commitment']['label']
                    ]
                )
            ]
        ];
        $workflows[] = (object) [
            'id'         => 'groups_00003',
            'name'       => 'Auto-Adding People Groups',
            'enabled'    => false, // Can be enabled via admin view
            'trigger'    => self::$trigger_updated['id'],
            'conditions' => [
                self::new_condition( self::$condition_is_set,
                    [
                        'id'    => 'members',
                        'label' => $dt_fields['members']['name']
                    ], [
                        'id'    => '',
                        'label' => ''
                    ]
                )
            ],
            'actions'    => [
                self::new_action( self::$action_custom,
                    [
                        'id'    => 'people_groups', // Field to be updated or an arbitrary selection!
                        'label' => $dt_fields['people_groups']['name']
                    ], [
                        'id'    => self::$custom_action_people_group_connections['id'], // Action Hook
                        'label' => self::$custom_action_people_group_connections['label']
                    ]
                )
            ]
        ];
    }

    /**
     * Workflow custom action self-contained function to handle following
     * use case:
     *
     * When adding a contact member to a group, if the contact has people groups
     * assigned, also add those values to parent group's people group field.
     *
     * @param post
     * @param field
     * @param value
     *
     * @access public
     * @since  1.11.0
     */
    public function custom_action_people_group_connections( $post, $field, $value ) {
        // Ensure post is a valid groups type
        if ( ! empty( $post ) && ( $post['post_type'] === 'groups' ) ) {

            $new_people_groups                            = [];
            $new_people_groups['people_groups']['values'] = [];

            // Iterate over group members in search of members with assigned people groups
            $members = $post['members'] ?? [];
            foreach ( $members as $member ) {

                if ( ! empty( $member ) && $member['post_type'] === 'contacts' ) {

                    // Fetch member contacts record and any associated people groups
                    $member_post = DT_Posts::get_post( $member['post_type'], $member['ID'], true, false, false );
                    if ( ! empty( $member_post ) && ! is_wp_error( $member_post ) && isset( $member_post['people_groups'] ) ) {

                        foreach ( $member_post['people_groups'] as $connection ) {

                            // Ensure member's people group is not already assigned to parent group -> safeguard against infinite post update loops!
                            if ( ! $this->already_assigned_people_group( $post['people_groups'] ?? [], $connection['ID'] ) ) {

                                // Prepare new people group for parent group addition
                                $new_people_groups['people_groups']['values'][] = [ "value" => $connection['ID'] ];
                            }
                        }
                    }
                }
            }

            // Assuming we have updated fields, proceed with post update!
            if ( ! empty( $new_people_groups['people_groups']['values'] ) ) {
                DT_Posts::update_post( $post['post_type'], $post['ID'], $new_people_groups, false, false );
            }
        }
    }

    private function already_assigned_people_group( $people_groups, $id ): bool {
        foreach ( $people_groups as $people_group ) {
            if ( intval( $people_group['ID'] ) === intval( $id ) ) {
                return true;
            }
        }

        return false;
    }
}
