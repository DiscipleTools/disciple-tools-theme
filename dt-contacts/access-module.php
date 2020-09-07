<?php

class DT_Contacts_Access {
    private static $_instance = null;
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()

    public function __construct() {
//        add_action( 'p2p_init', [ $this, 'p2p_init' ] );
        add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );

        //setup fields
        add_filter( 'dt_custom_fields_settings', [ $this, 'dt_custom_fields_settings' ], 20, 2 );
        //display tiles and fields
        add_action( 'dt_details_additional_section', [ $this, 'dt_details_additional_section' ], 20, 2 );
        add_filter( 'dt_details_additional_tiles', [ $this, 'dt_details_additional_tiles' ], 20, 2 );
        add_action( 'dt_record_top_above_details', [ $this, 'dt_record_top_above_details' ], 20, 2 );

        //@todo if access type
        add_action( 'wp_enqueue_scripts', [ $this, 'scripts' ], 99 );

        //list
        add_filter( "dt_user_list_filters", [ $this, "dt_user_list_filters" ], 20, 2 );

        //api
        add_action( "post_connection_removed", [ $this, "post_connection_removed" ], 20, 4 );
        add_action( "post_connection_added", [ $this, "post_connection_added" ], 20, 4 );
        add_filter( "dt_post_update_fields", [ $this, "dt_post_update_fields" ], 10, 3 );
        add_action( "dt_comment_created", [ $this, "dt_comment_created" ], 20, 4 );
        add_action( "dt_post_created", [ $this, "dt_post_created" ], 10, 3 );
        add_filter( "dt_post_create_fields", [ $this, "dt_post_create_fields" ], 10, 2 );
        add_action( "dt_post_updated", [ $this, "dt_post_updated" ], 10, 5 );
    }


    public function p2p_init(){}

    public function dt_custom_fields_settings( $fields, $post_type ){
        if ( $post_type === 'contacts' ){
            $fields['assigned_to'] = [
                'name'        => __( 'Assigned To', 'disciple_tools' ),
                'description' => __( 'Select the main person who is responsible for reporting on this contact.', 'disciple_tools' ),
                'type'        => 'user_select',
                'default'     => '',
                'tile'        => 'status',
                'icon' => get_template_directory_uri() . "/dt-assets/images/assigned-to.svg",
                "show_in_table" => 25
            ];
            $fields['seeker_path'] = [
                'name'        => __( 'Seeker Path', 'disciple_tools' ),
                'description' => _x( "Set the status of your progression with the contact. These are the steps that happen in a specific order to help a contact move forward.", 'Seeker Path field description', 'disciple_tools' ),
                'type'        => 'key_select',
                'default'     => [
                    'none'        => [
                      "label" => __( 'Contact Attempt Needed', 'disciple_tools' ),
                      "description" => ''
                    ],
                    'attempted'   => [
                      "label" => __( 'Contact Attempted', 'disciple_tools' ),
                      "description" => ''
                    ],
                    'established' => [
                      "label" => __( 'Contact Established', 'disciple_tools' ),
                      "description" => ''
                    ],
                    'scheduled'   => [
                      "label" => __( 'First Meeting Scheduled', 'disciple_tools' ),
                      "description" => ''
                    ],
                    'met'         => [
                      "label" => __( 'First Meeting Complete', 'disciple_tools' ),
                      "description" => ''
                    ],
                    'ongoing'     => [
                      "label" => __( 'Ongoing Meetings', 'disciple_tools' ),
                      "description" => ''
                    ],
                    'coaching'    => [
                      "label" => __( 'Being Coached', 'disciple_tools' ),
                      "description" => ''
                    ],
                ],
                'customizable' => 'add_only',
                'tile' => 'followup',
                "show_in_table" => 15
            ];

            $fields['overall_status'] = [
                'name'        => __( 'Contact Status', 'disciple_tools' ),
                'description' => _x( 'The Contact Status describes the progress in communicating with the contact.', "Contact Status field description", 'disciple_tools' ),
                'type'        => 'key_select',
                "default_color" => "#366184",
                'default'     => [
                    'new'   => [
                        "label" => __( 'New Contact', 'disciple_tools' ),
                        "description" => _x( "The contact is new in the system.", "Contact Status field description", 'disciple_tools' ),
                        "color" => "#F43636",
                    ],
                    'unassignable' => [
                        "label" => __( 'Not Ready', 'disciple_tools' ),
                        "description" => _x( "There is not enough information to move forward with the contact at this time.", "Contact Status field description", 'disciple_tools' ),
                        "color" => "#FF9800",
                    ],
                    'unassigned'   => [
                        "label" => __( 'Dispatch Needed', 'disciple_tools' ),
                        "description" => _x( "This contact needs to be assigned to a multiplier.", "Contact Status field description", 'disciple_tools' ),
                        "color" => "#F43636",
                    ],
                    'assigned'     => [
                        "label" => __( "Waiting to be accepted", 'disciple_tools' ),
                        "description" => _x( "The contact has been assigned to someone, but has not yet been accepted by that person.", "Contact Status field description", 'disciple_tools' ),
                        "color" => "#FF9800",
                    ],
                    'active'       => [
                        "label" => __( 'Active', 'disciple_tools' ),
                        "description" => _x( "The contact is progressing and/or continually being updated.", "Contact Status field description", 'disciple_tools' ),
                        "color" => "#4CAF50",
                    ],
                    'paused'       => [
                        "label" => __( 'Paused', 'disciple_tools' ),
                        "description" => _x( "This contact is currently on hold (i.e. on vacation or not responding).", "Contact Status field description", 'disciple_tools' ),
                        "color" => "#FF9800",
                    ],
                    'closed'       => [
                        "label" => __( 'Closed', 'disciple_tools' ),
                        "description" => _x( "This contact has made it known that they no longer want to continue or you have decided not to continue with him/her.", "Contact Status field description", 'disciple_tools' ),
                        "color" => "#F43636",
                    ],
                ],
                'tile'     => 'status',
                'customizable' => 'add_only',
                'custom_display' => true,
                'icon' => get_template_directory_uri() . "/dt-assets/images/status.svg",
                "show_in_table" => 10
            ];
            $fields['requires_update'] = [
                'name'        => __( 'Requires Update', 'disciple_tools' ),
                'description' => '',
                'type'        => 'boolean',
                'default'     => false,
                'section'     => 'status',
            ];

            $fields['age'] = [
                'name'        => __( 'Age', 'disciple_tools' ),
                'type'        => 'key_select',
                'default'     => [
                    'not-set' => [ "label" => '' ],
                    '<19'     => [ "label" => __( 'Under 18 years old', 'disciple_tools' ) ],
                    '<26'     => [ "label" => __( '18-25 years old', 'disciple_tools' ) ],
                    '<41'     => [ "label" => __( '26-40 years old', 'disciple_tools' ) ],
                    '>41'     => [ "label" => __( 'Over 40 years old', 'disciple_tools' ) ],
                ],
                'tile'     => 'details',
                "in_create_form" => true,
                'icon' => get_template_directory_uri() . "/dt-assets/images/contact-age.svg",
            ];
            $fields["reason_unassignable"] = [
                'name'        => __( 'Reason Not Ready', 'disciple_tools' ),
                'description' => _x( 'The main reason the contact is not ready to be assigned to a user.', 'Optional Documentation', 'disciple_tools' ),
                'type'        => 'key_select',
                'default'     => [
                    'none'         => [
                        "label" => '',
                    ],
                    'insufficient' => [
                        "label" => __( 'Insufficient Contact Information', 'disciple_tools' )
                    ],
                    'location'     => [
                        "label" => __( 'Unknown Location', 'disciple_tools' )
                    ],
                    'media'        => [
                        "label" => __( 'Only wants media', 'disciple_tools' )
                    ],
                    'outside_area' => [
                        "label" => __( 'Outside Area', 'disciple_tools' )
                    ],
                    'needs_review' => [
                        "label" => __( 'Needs Review', 'disciple_tools' )
                    ],
                    'awaiting_confirmation' => [
                        "label" => __( 'Waiting for Confirmation', 'disciple_tools' )
                    ],
                ],
                'section'     => 'misc',
                'customizable' => 'all'
            ];

            $fields['reason_paused'] = [
                'name'        => __( 'Reason Paused', 'disciple_tools' ),
                'description' => _x( 'A paused contact is one you are not currently interacting with but expect to in the future.', 'Optional Documentation', 'disciple_tools' ),
                'type'        => 'key_select',
                'default' => [
                    'none'                 => [ "label" => '' ],
                    'vacation'             => [ "label" => _x( 'Contact on vacation', 'Reason Paused label', 'disciple_tools' ) ],
                    'not_responding'       => [ "label" => _x( 'Contact not responding', 'Reason Paused label', 'disciple_tools' ) ],
                    'not_available'        => [ "label" => _x( 'Contact not available', 'Reason Paused label', 'disciple_tools' ) ],
                    'little_interest'      => [ "label" => _x( 'Contact has little interest/hunger', 'Reason Paused label', 'disciple_tools' ) ],
                    'no_initiative'        => [ "label" => _x( 'Contact shows no initiative', 'Reason Paused label', 'disciple_tools' ) ],
                    'questionable_motives' => [ "label" => _x( 'Contact has questionable motives', 'Reason Paused label', 'disciple_tools' ) ],
                    'ball_in_their_court'  => [ "label" => _x( 'Ball is in the contact\'s court', 'Reason Paused label', 'disciple_tools' ) ],
                    'wait_and_see'         => [ "label" => _x( 'We want to see if/how the contact responds to automated text messages', 'Reason Paused label', 'disciple_tools' ) ],
                ],
                'section'     => 'misc',
                'customizable' => 'all'
            ];

            $fields['reason_closed'] = [
                'name'        => __( 'Reason Closed', 'disciple_tools' ),
                'description' => _x( "A closed contact is one you can't or don't wish to interact with.", 'Optional Documentation', 'disciple_tools' ),
                'type'        => 'key_select',
                'default'     => [
                    'none'                 => [ "label" => '' ],
                    'duplicate'            => [ "label" => _x( 'Duplicate', 'Reason Closed label', 'disciple_tools' ) ],
                    'insufficient'         => [ "label" => _x( 'Insufficient contact info', 'Reason Closed label', 'disciple_tools' ) ],
                    'denies_submission'    => [ "label" => _x( 'Denies submitting contact request', 'Reason Closed label', 'disciple_tools' ) ],
                    'hostile_self_gain'    => [ "label" => _x( 'Hostile, playing games or self gain', 'Reason Closed label', 'disciple_tools' ) ],
                    'apologetics'          => [ "label" => _x( 'Only wants to argue or debate', 'Reason Closed label', 'disciple_tools' ) ],
                    'media_only'           => [ "label" => _x( 'Just wanted media or book', 'Reason Closed label', 'disciple_tools' ) ],
                    'no_longer_interested' => [ "label" => _x( 'No longer interested', 'Reason Closed label', 'disciple_tools' ) ],
                    'no_longer_responding' => [ "label" => _x( 'No longer responding', 'Reason Closed label', 'disciple_tools' ) ],
                    'already_connected'    => [ "label" => _x( 'Already in church or connected with others', 'Reason Closed label', 'disciple_tools' ) ],
                    'transfer'             => [ "label" => _x( 'Transferred contact to partner', 'Reason Closed label', 'disciple_tools' ) ],
                    'martyred'             => [ "label" => _x( 'Martyred', 'Reason Closed label', 'disciple_tools' ) ],
                    'moved'                => [ "label" => _x( 'Moved or relocated', 'Reason Closed label', 'disciple_tools' ) ],
                    'gdpr'                 => [ "label" => _x( 'GDPR request', 'Reason Closed label', 'disciple_tools' ) ],
                    'unknown'              => [ "label" => _x( 'Unknown', 'Reason Closed label', 'disciple_tools' ) ]
                ],
                'section'     => 'misc',
                'customizable' => 'all'
            ];

            $fields['accepted'] = [
                'name'        => __( 'Accepted', 'disciple_tools' ),
                'type'        => 'boolean',
                'default'     => false,
                'section'     => 'status',
                'hidden'      => true
            ];
            $sources_default = [
                'personal'           => [
                    'label'       => __( 'Personal', 'disciple_tools' ),
                    'key'         => 'personal',
                ],
                'web'           => [
                    'label'       => __( 'Web', 'disciple_tools' ),
                    'key'         => 'web',
                ],
                'facebook'      => [
                    'label'       => __( 'Facebook', 'disciple_tools' ),
                    'key'         => 'facebook',
                ],
                'twitter'       => [
                    'label'       => __( 'Twitter', 'disciple_tools' ),
                    'key'         => 'twitter',
                ],
                'transfer' => [
                    'label'       => __( 'Transfer', 'disciple_tools' ),
                    'key'         => 'transfer',
                    'description' => __( 'Contacts transferred from a partnership with another Disciple.Tools site.', 'disciple_tools' ),
                ]
            ];
            foreach ( dt_get_option( 'dt_site_custom_lists' )['sources'] as $key => $value ) {
                if ( !isset( $sources_default[$key] ) ) {
                    if ( isset( $value['enabled'] ) && $value["enabled"] === false ) {
                        $value["deleted"] = true;
                    }
                    $sources_default[ $key ] = $value;
                }
            }
            //@todo sources?
            $fields['sources'] = [
                'name'        => __( 'Sources', 'disciple_tools' ),
                'description' => _x( 'The website, event or location this contact came from.', 'Optional Documentation', 'disciple_tools' ),
                'type'        => 'multi_select',
                'default'     => $sources_default,
                'tile'     => 'details',
                'customizable' => 'all',
                'icon' => get_template_directory_uri() . "/dt-assets/images/source.svg",
            ];
        }

        return $fields;
    }

    public function add_api_routes(){
        $namespace = "dt-posts/v2";
        register_rest_route(
            $namespace, '/contacts/(?P<id>\d+)/accept', [
                "methods"  => "POST",
                "callback" => [ $this, 'accept_contact' ],
            ]
        );
    }

    public function dt_details_additional_tiles( $sections, $post_type = "" ){
        if ( $post_type === "contacts"){
            $sections['followup'] =[
                "label" => "Follow Up"
            ];
        }
        return $sections;
    }

    public function dt_details_additional_section( $section, $post_type ){
        if ( $post_type === "contacts" && $section === "status" ){
            $contact = DT_Posts::get_post( $post_type, get_the_ID() );
            $contact_fields = DT_Posts::get_post_field_settings( $post_type );
            ?>
            <div class="grid-x grid-margin-x" style="margin-top: 20px">
                <div class="cell small-12 medium-4">
                    <div class="section-subheader">
                        <img src="<?php echo esc_url( get_template_directory_uri() ) . '/dt-assets/images/status.svg' ?>">
                        <?php esc_html_e( "Status", 'disciple_tools' ) ?>
                        <button class="help-button" data-section="overall-status-help-text">
                            <img class="help-icon" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/help.svg' ) ?>"/>
                        </button>
                    </div>
                    <?php
                    $active_color = "#366184";
                    $current_key = $contact["overall_status"]["key"] ?? "";
                    if ( isset( $contact_fields["overall_status"]["default"][ $current_key ]["color"] )){
                        $active_color = $contact_fields["overall_status"]["default"][ $current_key ]["color"];
                    }
                    ?>
                    <select id="overall_status" class="select-field color-select" style="margin-bottom:0; background-color: <?php echo esc_html( $active_color ) ?>">
                        <?php foreach ($contact_fields["overall_status"]["default"] as $key => $option){
                            $value = $option["label"] ?? "";
                            if ( $contact["overall_status"]["key"] === $key ) {
                                ?>
                                <option value="<?php echo esc_html( $key ) ?>" selected><?php echo esc_html( $value ); ?></option>
                            <?php } else { ?>
                                <option value="<?php echo esc_html( $key ) ?>"><?php echo esc_html( $value ); ?></option>
                            <?php } ?>
                        <?php } ?>
                    </select>
                    <p>
                        <span id="reason">
                            <?php
                            $hide_edit_button = false;
                            $status_key = isset( $contact["overall_status"]["key"] ) ? $contact["overall_status"]["key"] : "";
                            if ( $status_key === "paused" &&
                                 isset( $contact["reason_paused"]["label"] )){
                                echo '(' . esc_html( $contact["reason_paused"]["label"] ) . ')';
                            } else if ( $status_key === "closed" &&
                                        isset( $contact["reason_closed"]["label"] )){
                                echo '(' . esc_html( $contact["reason_closed"]["label"] ) . ')';
                            } else if ( $status_key === "unassignable" &&
                                        isset( $contact["reason_unassignable"]["label"] )){
                                echo '(' . esc_html( $contact["reason_unassignable"]["label"] ) . ')';
                            } else {
                                if ( !in_array( $status_key, [ "paused", "closed", "unassignable" ] ) ){
                                    $hide_edit_button = true;
                                }
                            }
                            ?>
                        </span>
                        <button id="edit-reason" <?php if ( $hide_edit_button ) : ?> style="display: none"<?php endif; ?> ><i class="fi-pencil"></i></button>
                    </p>
                </div>

                <!-- ASSIGNED TO -->
                <div class="cell small-12 medium-4">
                    <div class="section-subheader">
                        <img src="<?php echo esc_url( get_template_directory_uri() ) . '/dt-assets/images/assigned-to.svg' ?>">
                        <?php echo esc_html( $contact_fields["assigned_to"]["name"] )?>
                        <button class="help-button" data-section="assigned-to-help-text">
                            <img class="help-icon" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/help.svg' ) ?>"/>
                        </button>
                    </div>

                    <div class="assigned_to details">
                        <var id="assigned_to-result-container" class="result-container assigned_to-result-container"></var>
                        <div id="assigned_to_t" name="form-assigned_to" class="scrollable-typeahead">
                            <div class="typeahead__container" style="margin-bottom: 0">
                                <div class="typeahead__field">
                                    <span class="typeahead__query">
                                        <input class="js-typeahead-assigned_to input-height" dir="auto"
                                               name="assigned_to[query]" placeholder="<?php echo esc_html_x( "Search Users", 'input field placeholder', 'disciple_tools' ) ?>"
                                               autocomplete="off">
                                    </span>
                                    <span class="typeahead__button">
                                        <button type="button" class="search_assigned_to typeahead__image_button input-height" data-id="assigned_to_t">
                                            <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/chevron_down.svg' ) ?>"/>
                                        </button>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <p>
                        <span id="reason_assigned_to">
                            <?php if ( isset( $contact["reason_assigned_to"]["label"] ) ) : ?>
                                (<?php echo esc_html( $contact["reason_assigned_to"]["label"] ); ?>)
                            <?php endif; ?>
                        </span>
                    </p>
                </div>

                <!-- SUBASSIGNED -->
                <div class="cell small-12 medium-4">
                    <?php render_field_for_display( "subassigned", $contact_fields, $contact, true ); ?>
                </div>
            </div>
            <div class="reveal" id="closed-contact-modal" data-reveal>
                <h3><?php echo esc_html( $contact_fields["reason_closed"]["name"] ?? '' )?></h3>
                <p><?php echo esc_html( $contact_fields["reason_closed"]["description"] ?? '' )?></p>
                <p><?php esc_html_e( 'Choose an option:', 'disciple_tools' )?></p>

                <select id="reason-closed-options">
                    <?php
                    foreach ( $contact_fields["reason_closed"]["default"] as $reason_key => $option ) {
                        if ( !empty( $option["label"] ) ) {
                            $selected = ( $reason_key === ( $contact["reason_closed"]["key"] ?? "" ) ) ? "selected" : "";
                            ?>
                            <option
                                value="<?php echo esc_attr( $reason_key ) ?>" <?php echo esc_html( $selected ) ?>> <?php echo esc_html( $option["label"] ?? "" ) ?></option>
                            <?php
                        }
                    }
                    ?>
                </select>
                <button class="button button-cancel clear" data-close aria-label="Close reveal" type="button">
                    <?php echo esc_html__( 'Cancel', 'disciple_tools' )?>
                </button>
                <button class="button loader confirm-reason-button" type="button" id="confirm-close" data-field="closed">
                    <?php echo esc_html__( 'Confirm', 'disciple_tools' )?>
                </button>
                <button class="close-button" data-close aria-label="Close modal" type="button">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="reveal" id="paused-contact-modal" data-reveal>
                <h3><?php echo esc_html( $contact_fields["reason_paused"]["name"] ?? '' )?></h3>
                <p><?php echo esc_html( $contact_fields["reason_paused"]["description"] ?? '' )?></p>
                <p><?php esc_html_e( 'Choose an option:', 'disciple_tools' )?></p>

                <select id="reason-paused-options">
                    <?php
                    foreach ( $contact_fields["reason_paused"]["default"] as $reason_key => $option ) {
                        if ( $option["label"] ) {
                            ?>
                            <option value="<?php echo esc_attr( $reason_key ) ?>"
                                <?php if ( ( $contact["reason_paused"]["key"] ?? "" ) === $reason_key ) {
                                    echo "selected";
                                } ?>>
                                <?php echo esc_html( $option["label"] ?? "" ) ?>
                            </option>
                            <?php
                        }
                    }
                    ?>
                </select>
                <button class="button button-cancel clear" data-close aria-label="Close reveal" type="button">
                    <?php echo esc_html__( 'Cancel', 'disciple_tools' )?>
                </button>
                <button class="button loader confirm-reason-button" type="button" id="confirm-pause" data-field="paused">
                    <?php echo esc_html__( 'Confirm', 'disciple_tools' )?>
                </button>
                <button class="close-button" data-close aria-label="Close modal" type="button">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="reveal" id="unassignable-contact-modal" data-reveal>
                <h3><?php echo esc_html( $contact_fields["reason_unassignable"]["name"] ?? '' )?></h3>
                <p><?php echo esc_html( $contact_fields["reason_unassignable"]["description"] ?? '' )?></p>
                <p><?php esc_html_e( 'Choose an option:', 'disciple_tools' )?></p>

                <select id="reason-unassignable-options">
                    <?php
                    foreach ( $contact_fields["reason_unassignable"]["default"] as $reason_key => $option ) {
                        if ( isset( $option["label"] ) ) {
                            ?>
                            <option value="<?php echo esc_attr( $reason_key ) ?>"
                                <?php if ( ( $contact["unassignable_paused"]["key"] ?? "" ) === $reason_key ) {
                                    echo "selected";
                                } ?>>
                                <?php echo esc_html( $option["label"] ?? "" ) ?>
                            </option>
                            <?php
                        }
                    }
                    ?>
                </select>
                <button class="button button-cancel clear" data-close aria-label="Close reveal" type="button">
                    <?php echo esc_html__( 'Cancel', 'disciple_tools' )?>
                </button>
                <button class="button loader confirm-reason-button" type="button" id="confirm-unassignable" data-field="unassignable">
                    <?php echo esc_html__( 'Confirm', 'disciple_tools' )?>
                </button>
                <button class="close-button" data-close aria-label="Close modal" type="button">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <?php
        }
    }

    public function dt_record_top_above_details( $post_type, $contact ){
        if ( $post_type === "contacts" && $contact["type"]["key"] === "access" ) {
            $current_user = wp_get_current_user();
            if ( isset( $contact['overall_status'] ) && $contact['overall_status']['key'] == 'assigned' &&
                isset( $contact['assigned_to'] ) && $contact['assigned_to']['id'] == $current_user->ID ) { ?>
                <section class="cell accept-contact" id="accept-contact">
                    <div class="bordered-box detail-notification-box">
                        <h4><?php esc_html_e( 'This contact has been assigned to you.', 'disciple_tools' )?></h4>
                        <button class="accept-button button small accept-decline" data-action="accept"><?php esc_html_e( 'Accept', 'disciple_tools' )?></button>
                        <button class="decline-button button small accept-decline" data-action="decline"><?php esc_html_e( 'Decline', 'disciple_tools' )?></button>
                    </div>
                </section>
                <?php
            }
        }
    }

    public function post_connection_added( $post_type, $post_id, $post_key, $value ){
    }
    public function post_connection_removed( $post_type, $post_id, $post_key, $value ){
    }

    public function dt_post_update_fields( $fields, $post_type, $post_id ){
        if ( $post_type === "contacts" ){
            if ( isset( $fields["assigned_to"] ) ) {
                if ( filter_var( $fields["assigned_to"], FILTER_VALIDATE_EMAIL ) ){
                    $user = get_user_by( "email", $fields["assigned_to"] );
                    if ( $user ) {
                        $fields["assigned_to"] = $user->ID;
                    } else {
                        return new WP_Error( __FUNCTION__, "Unrecognized user", $fields["assigned_to"] );
                    }
                }
                //make sure the assigned to is in the right format (user-1)
                if ( is_numeric( $fields["assigned_to"] ) ||
                    strpos( $fields["assigned_to"], "user" ) === false ){
                    $fields["assigned_to"] = "user-" . $fields["assigned_to"];
                }
                $existing_contact = DT_Posts::get_post( 'contacts', $post_id, true, false );
                if ( !isset( $existing_contact["assigned_to"] ) || $fields["assigned_to"] !== $existing_contact["assigned_to"]["assigned-to"] ){
                    $user_id = explode( '-', $fields["assigned_to"] )[1];
                    if ( !isset( $fields["overall_status"] ) ){
                        if ( $user_id != get_current_user_id() ){
                            if ( current_user_can( "assign_any_contacts" ) ) {
                                $fields["overall_status"] = 'assigned';
                            }
                            $fields['accepted'] = false;
                        } elseif ( isset( $existing_contact["overall_status"]["key"] ) && $existing_contact["overall_status"]["key"] === "assigned" ) {
                            $fields["overall_status"] = 'active';
                        }
                    }
                    if ( $user_id ){
                        DT_Posts::add_shared( "contacts", $post_id, $user_id, null, false, true, false );
                    }
                }
            }
            if ( isset( $fields["seeker_path"] ) ){
                self::update_quick_action_buttons( $post_id, $fields["seeker_path"] );
            }
            foreach ( $fields as $field_key => $value ){
                if ( strpos( $field_key, "quick_button" ) !== false ){
                    self::handle_quick_action_button_event( $post_id, [ $field_key => $value ] );
                }
            }
            if ( isset( $fields["overall_status"], $fields["reason_paused"] ) && $fields["overall_status"] === "paused"){
                $fields["requires_update"] = false;
            }
            if ( isset( $fields["overall_status"], $fields["reason_closed"] ) && $fields["overall_status"] === "closed"){
                $fields["requires_update"] = false;
            }
        }
        return $fields;
    }

    public function dt_comment_created( $post_type, $post_id, $created_comment_id, $comment_type ){
        if ( $post_type === "contacts" ){
            if ( $comment_type === "comment" ){
                self::check_requires_update( $post_id );
            }
        }
    }

    // Runs after post is created and fields are processed.
    public function dt_post_created( $post_type, $post_id, $initial_request_fields ){
        if ( $post_type === "contacts" ){
            // share the post with the assigned to user.
            $post = DT_Posts::get_post( $post_type, $post_id, true, false );
            if ( isset( $post["assigned_to"] )) {
                if ( $post["assigned_to"]["id"] ) {
                    DT_Posts::add_shared( $post_type, $post_id, $post["assigned_to"]["id"], null, false, false, false );
                }
            }
        }
    }

    // Add, remove or modify fields before the fields are processed on post create.
    public function dt_post_create_fields( $fields, $post_type ){
        //@todo still need?
        if ( !isset( $fields["seeker_path"] ) ){
            $fields["seeker_path"] = "none";
        }
        if ( !isset( $fields["assigned_to"] ) ){
            if ( get_current_user_id() ) {
                $fields["assigned_to"] = sprintf( "user-%d", get_current_user_id() );
            } else {
                $base_id = dt_get_base_user( true );
                if ( is_wp_error( $base_id ) ) { // if default editor does not exist, get available administrator
                    $users = get_users( [ 'role' => 'administrator' ] );
                    if ( count( $users ) > 0 ) {
                        foreach ( $users as $user ) {
                            $base_id = $user->ID;
                        }
                    }
                }
                $fields["assigned_to"] = sprintf( "user-%d", $base_id );
            }
        } else {
            if ( filter_var( $fields["assigned_to"], FILTER_VALIDATE_EMAIL ) ){
                $user = get_user_by( "email", $fields["assigned_to"] );
                if ( $user ) {
                    $fields["assigned_to"] = $user->ID;
                } else {
                    return new WP_Error( __FUNCTION__, "Unrecognized user", $fields["assigned_to"] );
                }
            }
            if ( is_numeric( $fields["assigned_to"] ) ||
                strpos( $fields["assigned_to"], "user" ) === false ){
                $fields["assigned_to"] = "user-" . $fields["assigned_to"];
            }
        }
        if ( !isset( $fields["overall_status"] ) ){
            $current_roles = wp_get_current_user()->roles;
            if (in_array( "dispatcher", $current_roles, true ) || in_array( "marketer", $current_roles, true )) {
                $fields["overall_status"] = "new";
            } else if (in_array( "multiplier", $current_roles, true ) ) {
                $fields["overall_status"] = "active";
            } else {
                $fields["overall_status"] = "new";
            }
        }
        if ( !isset( $fields["sources"] ) ) {
            $fields["sources"] = [ "values" => [ [ "value" => "personal" ] ] ];
        }
        return $fields;
    }

    //Runs after fields are processed on update
    public function dt_post_updated( $post_type, $post_id, $initial_request_fields, $post_fields_before_update, $post_fields_after_update ){
        if ( $post_type === "contacts" ){
            self::check_seeker_path( $post_id, $post_fields_after_update, $post_fields_before_update );
        }
    }



    /**
     * Make sure activity is created for all the steps before the current seeker path
     *
     * @param $contact_id
     * @param $contact
     * @param $previous_values
     */
    public function check_seeker_path( $contact_id, $contact, $previous_values ){
        if ( isset( $contact["seeker_path"]["key"] ) && $contact["seeker_path"]["key"] != "none" ){
            $current_key = $contact["seeker_path"]["key"];
            $prev_key = isset( $previous_values["seeker_path"]["key"] ) ? $previous_values["seeker_path"]["key"] : "none";
            $field_settings = DT_Posts::get_post_field_settings( "contacts" );
            $seeker_path_options = $field_settings["seeker_path"]["default"];
            $option_keys = array_keys( $seeker_path_options );
            $current_index = array_search( $current_key, $option_keys );
            $prev_option_key = $option_keys[ $current_index - 1 ];

            if ( $prev_option_key != $prev_key && $current_index > array_search( $prev_key, $option_keys ) ){
                global $wpdb;
                $seeker_path_activity = $wpdb->get_results( $wpdb->prepare( "
                    SELECT meta_value, hist_time, meta_id
                    FROM $wpdb->dt_activity_log
                    WHERE object_id = %s
                    AND meta_key = 'seeker_path'
                ", $contact_id), ARRAY_A );
                $existing_keys = [];
                $most_recent = 0;
                $meta_id = 0;
                foreach ( $seeker_path_activity as $activity ){
                    $existing_keys[] = $activity["meta_value"];
                    if ( $activity["hist_time"] > $most_recent ){
                        $most_recent = $activity["hist_time"];
                    }
                    $meta_id = $activity["meta_id"];
                }
                $activity_to_create = [];
                for ( $i = $current_index; $i > 0; $i-- ){
                    if ( !in_array( $option_keys[$i], $existing_keys ) ){
                        $activity_to_create[] = $option_keys[$i];
                    }
                }
                foreach ( $activity_to_create as $missing_key ){
                    $wpdb->query( $wpdb->prepare("
                        INSERT INTO $wpdb->dt_activity_log
                        ( action, object_type, object_subtype, object_id, user_id, hist_time, meta_id, meta_key, meta_value, field_type )
                        VALUES ( 'field_update', 'contacts', 'seeker_path', %s, %d, %d, %d, 'seeker_path', %s, 'key_select' )",
                        $contact_id, get_current_user_id(), $most_recent - 1, $meta_id, $missing_key
                    ));
                }
            }
        }
    }

    public static function dt_user_list_filters( $filters, $post_type ) {
        return $filters;
    }

    public function scripts(){
        if ( is_singular( "contacts" ) ){
            wp_enqueue_script( 'dt_contacts_access', get_template_directory_uri() . '/dt-contacts/contacts_access.js', [
                'jquery',
            ], filemtime( get_theme_file_path() . '/dt-contacts/contacts_access.js' ), true );
        }
    }

    private static function handle_quick_action_button_event( int $contact_id, array $field, bool $check_permissions = true ) {
        $update = [];
        $key = key( $field );

        if ( $key == "quick_button_no_answer" ) {
            $update["seeker_path"] = "attempted";
        } elseif ( $key == "quick_button_phone_off" ) {
            $update["seeker_path"] = "attempted";
        } elseif ( $key == "quick_button_contact_established" ) {
            $update["seeker_path"] = "established";
        } elseif ( $key == "quick_button_meeting_scheduled" ) {
            $update["seeker_path"] = "scheduled";
        } elseif ( $key == "quick_button_meeting_complete" ) {
            $update["seeker_path"] = "met";
        }

        if ( isset( $update["seeker_path"] ) ) {
            self::check_requires_update( $contact_id );
            return self::update_seeker_path( $contact_id, $update["seeker_path"], $check_permissions );
        } else {
            return $contact_id;
        }
    }

    public static function update_quick_action_buttons( $contact_id, $seeker_path ){
        if ( $seeker_path === "established" ){
            $quick_button = get_post_meta( $contact_id, "quick_button_contact_established", true );
            if ( empty( $quick_button ) || $quick_button == "0" ){
                update_post_meta( $contact_id, "quick_button_contact_established", "1" );
            }
        }
        if ( $seeker_path === "scheduled" ){
            $quick_button = get_post_meta( $contact_id, "quick_button_meeting_scheduled", true );
            if ( empty( $quick_button ) || $quick_button == "0" ){
                update_post_meta( $contact_id, "quick_button_meeting_scheduled", "1" );
            }
        }
        if ( $seeker_path === "met" ){
            $quick_button = get_post_meta( $contact_id, "quick_button_meeting_complete", true );
            if ( empty( $quick_button ) || $quick_button == "0" ){
                update_post_meta( $contact_id, "quick_button_meeting_complete", "1" );
            }
        }
        self::check_requires_update( $contact_id );
    }

    private static function update_seeker_path( int $contact_id, string $path_option, $check_permissions = true ) {
        $contact_fields = DT_Posts::get_post_field_settings( "contacts" );
        $seeker_path_options = $contact_fields["seeker_path"]["default"];
        $option_keys = array_keys( $seeker_path_options );
        $current_seeker_path = get_post_meta( $contact_id, "seeker_path", true );
        $current_index = array_search( $current_seeker_path, $option_keys );
        $new_index = array_search( $path_option, $option_keys );
        if ( $new_index > $current_index ) {
            $current_index = $new_index;
            $update = DT_Posts::update_post( "contacts", $contact_id, [ "seeker_path" => $path_option ], $check_permissions );
            if ( is_wp_error( $update ) ) {
                return $update;
            }
            $current_seeker_path = $path_option;
        }

        return [
            "currentKey" => $current_seeker_path,
            "current" => $seeker_path_options[ $option_keys[ $current_index ] ],
            "next"    => isset( $option_keys[ $current_index + 1 ] ) ? $seeker_path_options[ $option_keys[ $current_index + 1 ] ] : "",
        ];
    }

    //check to see if the contact is marked as needing an update
    //if yes: mark as updated
    private static function check_requires_update( $contact_id ){
        if ( get_current_user_id() ){
            $requires_update = get_post_meta( $contact_id, "requires_update", true );
            if ( $requires_update == "yes" || $requires_update == true || $requires_update == "1"){
                //don't remove update needed if the user is a dispatcher (and not assigned to the contacts.)
                if ( DT_Posts::can_view_all( 'contacts' ) ){
                    if ( dt_get_user_id_from_assigned_to( get_post_meta( $contact_id, "assigned_to", true ) ) === get_current_user_id() ){
                        update_post_meta( $contact_id, "requires_update", false );
                    }
                } else {
                    update_post_meta( $contact_id, "requires_update", false );
                }
            }
        }
    }

    public static function accept_contact( WP_REST_Request $request ){
        $params = $request->get_params();
        $body = $request->get_json_params() ?? $request->get_body_params();
        if ( !isset( $params['id'] ) ) {
            return new WP_Error( "accept_contact", "Missing a valid contact id", [ 'status' => 400 ] );
        } else {
            $contact_id = $params['id'];
            $accepted = $body["accept"];
            if ( !DT_Posts::can_update( 'contacts', $contact_id ) ) {
                return new WP_Error( __FUNCTION__, "You do not have permission for this", [ 'status' => 403 ] );
            }

            if ( $accepted ) {
                $update = [
                    "overall_status" => 'active',
                    "accepted" => true
                ];
                dt_activity_insert(
                    [
                        'action'         => 'assignment_accepted',
                        'object_type'    => get_post_type( $contact_id ),
                        'object_subtype' => '',
                        'object_name'    => get_the_title( $contact_id ),
                        'object_id'      => $contact_id,
                        'meta_id'        => '', // id of the comment
                        'meta_key'       => '',
                        'meta_value'     => '',
                        'meta_parent'    => '',
                        'object_note'    => '',
                    ]
                );
                return DT_Posts::update_post( "contacts", $contact_id, $update, true );
            } else {
                $assign_to_id = 0;
                $last_activity = DT_Posts::get_most_recent_activity_for_field( $contact_id, "assigned_to" );
                if ( isset( $last_activity->user_id )){
                    $assign_to_id = $last_activity->user_id;
                } else {
                    $base_user = dt_get_base_user( true );
                    if ( $base_user ){
                        $assign_to_id = $base_user;
                    }
                }

                $update = [
                    "assigned_to" => $assign_to_id,
                    "overall_status" => 'unassigned'
                ];
                $contact = DT_Posts::update_post( "contacts", $contact_id, $update, true );
                $current_user = wp_get_current_user();
                dt_activity_insert(
                    [
                        'action'         => 'assignment_decline',
                        'object_type'    => get_post_type( $contact_id ),
                        'object_subtype' => 'decline',
                        'object_name'    => get_the_title( $contact_id ),
                        'object_id'      => $contact_id,
                        'meta_id'        => '', // id of the comment
                        'meta_key'       => '',
                        'meta_value'     => '',
                        'meta_parent'    => '',
                        'object_note'    => ''
                    ]
                );
                Disciple_Tools_Notifications::insert_notification_for_assignment_declined( $current_user->ID, $assign_to_id, $contact_id );
                return $contact;
            }
        }
    }
}
