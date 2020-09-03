<?php

class DT_Contacts_Base {
    private static $_instance = null;
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()

    public function __construct() {
        //setup post type
        add_action( 'after_setup_theme', [ $this, 'after_setup_theme' ], 100 );

        //setup tiles and fields
        add_action( 'p2p_init', [ $this, 'p2p_init' ] );
        add_filter( 'dt_custom_fields_settings', [ $this, 'dt_custom_fields_settings' ], 10, 2 );
        add_filter( 'dt_details_additional_tiles', [ $this, 'dt_details_additional_tiles' ], 10, 2 );
        add_filter( 'dt_details_additional_tiles', [ $this, 'dt_details_additional_tiles_after' ], 100, 2 );
        add_action( 'dt_details_additional_section', [ $this, 'dt_details_additional_section' ], 20, 2 );

        // hooks
        add_action( "post_connection_removed", [ $this, "post_connection_removed" ], 10, 4 );
        add_action( "post_connection_added", [ $this, "post_connection_added" ], 10, 4 );
        add_filter( "dt_post_update_fields", [ $this, "update_post_field_hook" ], 10, 3 );

        //list
        add_filter( "dt_user_list_filters", [ $this, "dt_user_list_filters" ], 10, 2 );

    }


    public function after_setup_theme(){
        if ( class_exists( 'Disciple_Tools_Post_Type_Template' )) {
            new Disciple_Tools_Post_Type_Template( "contacts", 'Contact', 'Contacts' );
        }
    }

    public function dt_custom_fields_settings( $fields, $post_type ){
        if ( $post_type === 'contacts' ){
            $fields["name"] = [
                'name' => __( "Name", 'disciple_tools' ),
                'type' => 'text',
                'tile' => 'details',
                'in_create_form' => true,
                'required' => true,
                'icon' => get_template_directory_uri() . "/dt-assets/images/name.svg",
            ];
            $fields["nickname"] = [
                'name' => __( "Nickname", 'disciple_tools' ),
                'type' => 'text',
                'tile' => 'details',
                'in_create_form' => true,
                'required' => true,
                'icon' => get_template_directory_uri() . "/dt-assets/images/name.svg",
            ];
            $fields["type"] = [
                'name'        => __( 'Contact Type', 'disciple_tools' ),
                'type'        => 'key_select',
                'default'     => [
                    'media'    => [ "label" => __( 'Media', 'disciple_tools' ) ],
                    'seeker' => [ "label" => __( 'Seeker', 'disciple_tools' ) ],
                    'believer' => [ "label" => __( 'Believer', 'disciple_tools' ) ],
                    'leader' => [ "label" => __( 'Leader', 'disciple_tools' ) ],
                    'user'     => [ "label" => __( 'User', 'disciple_tools' ) ]
                ],
                'tile'     => 'status',
                'hidden'      => true
            ];
            $fields["last_modified"] =[
                'name' => __( 'Last Modified', 'disciple_tools' ),
                'type' => 'number',
                'default' => 0,
                'section' => 'admin',
                'customizable' => false
            ];
            $fields["duplicate_data"] = [
                "name" => 'Duplicates', //system string does not need translation
                'type' => 'array',
                'default' => [],
                'section' => 'admin',
                "hidden" => true
            ];
            $fields["duplicate_of"] = [
                "name" => "Duplicate of", //system string does not need translation
                "type" => "text",
                "default" => '',
                "hidden" => true
            ];





            $fields['location_grid'] = [
                'name'        => __( 'Locations', 'disciple_tools' ),
                'description' => _x( 'The general location where this contact is located.', 'Optional Documentation', 'disciple_tools' ),
                'type'        => 'location',
                'default'     => [],
                "in_create_form" => true,
                "tile" => "details",
                "icon" => get_template_directory_uri() . "/dt-assets/images/location.svg",
            ];
            $fields['location_grid_meta'] = [
                'name'        => 'Location Grid Meta', //system string does not need translation
                'type'        => 'location_meta',
                'default'     => [],
                'hidden' => true
            ];


//            $fields['quick_button_no_answer'] = [
//                'name'        => __( 'No Answer', 'disciple_tools' ),
//                'description' => '',
//                'type'        => 'number',
//                'default'     => 0,
//                'section'     => 'quick_buttons',
//                'icon'        => get_template_directory_uri() . "/dt-assets/images/no-answer.svg",
//            ];
//            $fields['quick_button_contact_established'] = [
//                'name'        => __( 'Contact Established', 'disciple_tools' ),
//                'description' => '',
//                'type'        => 'number',
//                'default'     => 0,
//                'section'     => 'quick_buttons',
//                'icon'        => get_template_directory_uri() . "/dt-assets/images/successful-conversation.svg",
//            ];
//            $fields['quick_button_meeting_scheduled'] = [
//                'name'        => __( 'Meeting Scheduled', 'disciple_tools' ),
//                'description' => '',
//                'type'        => 'number',
//                'default'     => 0,
//                'section'     => 'quick_buttons',
//                'icon'        => get_template_directory_uri() . "/dt-assets/images/meeting-scheduled.svg",
//            ];
//            $fields['quick_button_meeting_complete'] = [
//                'name'        => __( 'Meeting Complete', 'disciple_tools' ),
//                'description' => '',
//                'type'        => 'number',
//                'default'     => 0,
//                'section'     => 'quick_buttons',
//                'icon'        => get_template_directory_uri() . "/dt-assets/images/meeting-complete.svg",
//            ];
//            $fields['quick_button_no_show'] = [
//                'name'        => __( 'Meeting No-show', 'disciple_tools' ),
//                'description' => '',
//                'type'        => 'number',
//                'default'     => 0,
//                'section'     => 'quick_buttons',
//                'icon'        => get_template_directory_uri() . "/dt-assets/images/no-show.svg",
//            ];

            $fields['tags'] = [
                'name'        => __( 'Tags', 'disciple_tools' ),
                'description' => _x( 'A useful way to group related items and can help group contacts associated with noteworthy characteristics. e.g. business owner, sports lover. The contacts can also be filtered using these tags.', 'Optional Documentation', 'disciple_tools' ),
                'type'        => 'multi_select',
                'default'     => [],
                'tile'        => 'other',
                'custom_display' => true,
                'icon' => get_template_directory_uri() . "/dt-assets/images/phone.svg",
            ];
            $fields["follow"] = [
                'name'        => __( 'Follow', 'disciple_tools' ),
                'type'        => 'multi_select',
                'default'     => [],
                'section'     => 'misc',
                'hidden'      => true
            ];
            $fields["unfollow"] = [
                'name'        => __( 'Un-Follow', 'disciple_tools' ),
                'type'        => 'multi_select',
                'default'     => [],
                'section'     => 'misc',
                'hidden'      => true
            ];
            $fields["relation"] = [
                "name" => __( "Relation", 'disciple_tools' ),
                "description" => _x( "Relationship this contact has with another contact in the system.", 'Optional Documentation', 'disciple_tools' ),
                "type" => "connection",
                "post_type" => "contacts",
                "p2p_direction" => "any",
                "p2p_key" => "contacts_to_relation",
                "tile" => "other"
            ];
            $fields["groups"] = [
                "name" => __( "Groups", 'disciple_tools' ),
                "description" => _x( "Groups this contact is a member of.", 'Optional Documentation', 'disciple_tools' ),
                "type" => "connection",
                "post_type" => "groups",
                "p2p_direction" => "from",
                "p2p_key" => "contacts_to_groups",
                "tile" => "other",
                'icon' => get_template_directory_uri() . "/dt-assets/images/group-type.svg",
            ];
            $fields["subassigned"] = [
                "name" => __( "Sub-assigned to", 'disciple_tools' ),
                "description" => __( "Contact or User assisting the Assigned To user to follow up with the contact.", 'disciple_tools' ),
                "type" => "connection",
                "post_type" => "contacts",
                "p2p_direction" => "to",
                "p2p_key" => "contacts_to_subassigned",
                "tile" => "other",
                'icon' => get_template_directory_uri() . "/dt-assets/images/subassigned.svg",
            ];
            $fields['tasks'] = [
                'name' => __( 'Tasks', 'disciple_tools' ),
                'type' => 'post_user_meta',
            ];
            $fields["languages"] = [
                'name' => __( 'Languages', 'disciple_tools' ),
                'type' => 'multi_select',
                'default' => dt_get_option( "dt_working_languages" ) ?: [],
                'icon' => get_template_directory_uri() . "/dt-assets/images/languages.svg",
            ];

            //add communication channels
            $fields["contact_phone"] = [
                "name" => __( 'Phone', 'disciple_tools' ),
                "icon" => get_template_directory_uri() . "/dt-assets/images/phone.svg",
                "type" => "communication_channel",
                "tile" => "details",
                "in_create_form" => true,
            ];
            $fields["contact_email"] = [
                "name" => __( 'Email', 'disciple_tools' ),
                "icon" => get_template_directory_uri() . "/dt-assets/images/email.svg",
                "type" => "communication_channel",
                "tile" => "details",
                "in_create_form" => true,
            ];
            $fields["contact_address"] = [
                "name" => __( 'Address', 'disciple_tools' ),
                "icon" => get_template_directory_uri() . "/dt-assets/images/house.svg",
                "type" => "communication_channel",
                "tile" => "details",
                "in_create_form" => true,
            ];
            $channels = self::get_channels_list();
            foreach ( $channels as $channel_key => $channel_options ){
                if ( !isset( $fields['contact_'.$channel_key] )){
//                    @todo deleted
                    //communication channels start with contact_
                    $field = [
                        "name" => $channel_options["label"],
                        "type" => "communication_channel",
                        "tile" => "details",
                    ];
                    if ( isset( $channel_options['icon'] ) ) {
                        $field['icon'] = $channel_options['icon'];
                    }
                    $fields['contact_' . $channel_key] = $field;
                }
            }
        }


        $fields['gender'] = [
            'name'        => __( 'Gender', 'disciple_tools' ),
            'type'        => 'key_select',
            'default'     => [
                'not-set' => [ "label" => '' ],
                'male'    => [ "label" => __( 'Male', 'disciple_tools' ) ],
                'female'  => [ "label" => __( 'Female', 'disciple_tools' ) ],
            ],
            'tile'     => 'details',
            "icon" => get_template_directory_uri() . "/dt-assets/images/gender.svg",
        ];



        return $fields;
    }

    public static function get_channels_list() {
//        @todo remove extra channels
        $channel_list = [
            "phone"     => [
                "label" => __( 'Phone', 'disciple_tools' ),
                "types" => [],
                "description" => '',
                "icon" => get_template_directory_uri() . "/dt-assets/images/phone.svg",
            ],
            "email"     => [
                "label" => __( 'Email', 'disciple_tools' ),
                "types" => [],
                "description" => '',
                "icon" => get_template_directory_uri() . "/dt-assets/images/email.svg",
            ],
            "address" => [
                "label" => __( "Address", 'disciple_tools' ),
                "types" => [],
                "description" => '',
                "icon" => get_template_directory_uri() . "/dt-assets/images/house.svg",
            ],
            "facebook"  => [
                "label" => __( 'Facebook', 'disciple_tools' ),
                "types" => [],
                "icon" => get_template_directory_uri() . "/dt-assets/images/facebook.svg",
                "hide_domain" => true
            ],
            "twitter"   => [
                "label" => __( 'Twitter', 'disciple_tools' ),
                "types" => [],
                "icon" => get_template_directory_uri() . "/dt-assets/images/twitter.svg",
                "hide_domain" => true
            ]
        ];

        $custom_channels = dt_get_option( "dt_custom_channels" );
        foreach ( $custom_channels as $custom_key => $custom_value ){
            $channel_list[$custom_key] = array_merge( $channel_list[$custom_key] ?? [], $custom_value );
        }
        return apply_filters( 'dt_custom_channels', $channel_list );
    }


    public function dt_details_additional_section( $section, $post_type ){
        if ( $post_type === "contacts" && $section === "other" ) :
            $contact_fields = DT_Posts::get_post_field_settings( $post_type );
            ?>
            <div class="section-subheader">
                <?php echo esc_html( $contact_fields["tags"]["name"] ) ?>
            </div>
            <div class="tags">
                <var id="tags-result-container" class="result-container"></var>
                <div id="tags_t" name="form-tags" class="scrollable-typeahead typeahead-margin-when-active">
                    <div class="typeahead__container">
                        <div class="typeahead__field">
                            <span class="typeahead__query">
                                <input class="js-typeahead-tags input-height"
                                       name="tags[query]"
                                       placeholder="<?php echo esc_html( sprintf( _x( "Search %s", "Search 'something'", 'disciple_tools' ), $contact_fields["tags"]['name'] ) )?>"
                                       autocomplete="off">
                            </span>
                            <span class="typeahead__button">
                                <button type="button" data-open="create-tag-modal" class="create-new-tag typeahead__image_button input-height">
                                    <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/tag-add.svg' ) ?>"/>
                                </button>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif;
    }

    public function p2p_init(){
        /**
         * Contact Connection or Relation
         */
        p2p_register_connection_type(
            [
                'name'        => 'contacts_to_relation',
                'from'        => 'contacts',
                'to'          => 'contacts'
            ]
        );
        /**
         * Contact Sub-assigned to
         */
        p2p_register_connection_type(
            [
                'name'        => 'contacts_to_subassigned',
                'from'        => 'contacts',
                'to'          => 'contacts',
                'admin_box' => [
                    'show' => false,
                ],
                'title'       => [
                    'from' => __( 'Sub-assigned by', 'disciple_tools' ),
                    'to'   => __( 'Sub-assigned', 'disciple_tools' ),
                ]
            ]
        );
    }

    public function dt_details_additional_tiles( $tiles, $post_type = "" ){
        return $tiles;
    }

    public function dt_details_additional_tiles_after( $tiles, $post_type = "" ){
        if ( $post_type === "contacts" ){
            $tiles["other"] = [ "label" => __( "Other", 'disciple_tools' ) ];
        }
        return $tiles;
    }


    private function update_contact_counts( $contact_id, $action = "added", $type = 'contacts' ){

    }
    public function post_connection_added( $post_type, $post_id, $post_key, $value ){
    }
    public function post_connection_removed( $post_type, $post_id, $post_key, $value ){
    }

    public function update_post_field_hook( $fields, $post_type, $post_id ){
        return $fields;
    }

    public static function dt_user_list_filters( $filters, $post_type ) {
        if ( $post_type === 'contacts' ) {
            $filters["tabs"][] = [
                "key" => "all_contacts",
                "label" => _x( "All", 'List Filters', 'disciple_tools' ),
                "order" => 10
            ];
            // add assigned to me filters
            $filters["filters"][] = [
                'ID' => 'all_contacts',
                'tab' => 'all_contacts',
                'name' => _x( "All", 'List Filters', 'disciple_tools' ),
                'query' => [],
            ];
        }
        return $filters;
    }

    public static function dt_record_admin_actions( $post_type, $post_id ){
        if ( $post_type === "contacts" ){
            $contact = DT_Posts::get_post( $post_type, $post_id );
            if ( current_user_can( "access_contacts" ) ) {
                ?>
                <!--                @todo-->
                <li><a id="open_merge_with_contact"><?php esc_html_e( "Merge with another contact", 'disciple_tools' ) ?></a></li>
                <li><a data-open="merge-dupe-edit-modal"><?php esc_html_e( "See duplicates", 'disciple_tools' ) ?></a></li>

                <div class="reveal" id="merge-with-contact-modal" data-reveal style="min-height:500px">
                    <h3><?php esc_html_e( "Merge Contact", 'disciple_tools' )?></h3>
                    <p><?php esc_html_e( "Merge this contact with another contact.", 'disciple_tools' )?></p>

                    <div class="merge_with details">
                        <var id="merge_with-result-container" class="result-container merge_with-result-container"></var>
                        <div id="merge_with_t" name="form-merge_with">
                            <div class="typeahead__container">
                                <div class="typeahead__field">
                                    <span class="typeahead__query">
                                        <input class="js-typeahead-merge_with input-height"
                                               name="merge_with[query]" placeholder="<?php echo esc_html_x( "Search multipliers and contacts", 'input field placeholder', 'disciple_tools' ) ?>"
                                               autocomplete="off">
                                    </span>
                                    <span class="typeahead__button">
                                        <button type="button" class="search_merge_with typeahead__image_button input-height" data-id="user-select_t">
                                            <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/chevron_down.svg' ) ?>"/>
                                        </button>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <br>
                    <div class="confirm-merge-with-contact" style="display: none">
                        <p><span  id="name-of-contact-to-merge"></span> <?php echo esc_html_x( "selected.", 'added to the end of a sentence', 'disciple_tools' ) ?></p>
                        <p><?php esc_html_e( "Click merge to continue.", 'disciple_tools' ) ?></p>
                    </div>

                    <div class="grid-x">
                        <button class="button button-cancel clear" data-close aria-label="Close reveal" type="button">
                            <?php echo esc_html__( 'Cancel', 'disciple_tools' )?>
                        </button>
                        <form action='<?php echo esc_url( site_url() );?>/contacts/mergedetails' method='get'>
                            <input type='hidden' name='currentid' value='<?php echo esc_html( $contact["ID"] );?>'/>
                            <input id="confirm-merge-with-contact-id" type='hidden' name='dupeid' value=''/>
                            <button type='submit' class="button confirm-merge-with-contact" style="display: none">
                                <?php echo esc_html__( 'Merge', 'disciple_tools' )?>
                            </button>
                        </form>
                        <button class="close-button" data-close aria-label="Close modal" type="button">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                </div>
                <?php
                get_template_part( 'dt-assets/parts/merge', 'details' );
            }
        }
    }
}
