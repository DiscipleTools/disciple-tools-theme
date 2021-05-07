<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

class DT_Contacts_User {
    private static $_instance = null;
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()

    public function __construct() {

        add_filter( "dt_can_view_permission", [ $this, 'can_update_permission_filter' ], 10, 3 );
        add_filter( "dt_can_update_permission", [ $this, 'can_update_permission_filter' ], 10, 3 );

        //setup fields
        add_filter( 'dt_custom_fields_settings', [ $this, 'dt_custom_fields_settings' ], 20, 2 );

        //display tiles and fields
        add_action( 'dt_details_additional_section', [ $this, 'dt_details_additional_section' ], 20, 2 );
        add_filter( 'dt_details_additional_tiles', [ $this, 'dt_details_additional_tiles' ], 20, 2 );

        //list
        add_filter( "dt_user_list_filters", [ $this, "dt_user_list_filters" ], 20, 2 );

        //api
        add_action( "post_connection_removed", [ $this, "post_connection_removed" ], 20, 4 );
        add_action( "post_connection_added", [ $this, "post_connection_added" ], 20, 4 );

        add_action( 'dt_record_admin_actions', [ $this, "dt_record_admin_actions" ], 10, 2 );
    }


    public function p2p_init(){}

    public function dt_custom_fields_settings( $fields, $post_type ){
        if ( $post_type === 'contacts' ){
            $fields['corresponds_to_user'] = [
                'name' => __( 'Corresponds to user', 'disciple_tools' ),
                'description' => _x( 'The id of the user this contact corresponds to', 'Optional Documentation', 'disciple_tools' ),
                'type' => 'number',
                'default' => 0,
                'customizable' => false,
                'hidden' => true
            ];
            $fields['corresponds_to_user_name'] = [
                'name' => 'Corresponds to user_name', //untranslated.
                'description' => 'Field used in the multisite invite process', //untranslated.
                'type' => 'text',
                'customizable' => false,
                'hidden' => true
            ];
        }

        return $fields;
    }


    public function dt_details_additional_tiles( $sections, $post_type = "" ) {
        return $sections;
    }

    public function dt_details_additional_section( $section, $post_type ){
    }

    public function post_connection_added( $post_type, $post_id, $post_key, $value ){
    }
    public function post_connection_removed( $post_type, $post_id, $post_key, $value ){
    }

    public static function dt_user_list_filters( $filters, $post_type ) {
        return $filters;
    }

    public static function dt_record_admin_actions( $post_type, $post_id ){
        global $wp_roles;
        if ( $post_type === "contacts" ){
            $contact = DT_Posts::get_post( $post_type, $post_id );
            if ( current_user_can( "create_users" ) ){
                ?>
                <li><a data-open="make-user-from-contact-modal">
                        <img class="dt-icon" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/arrow-user.svg' ) ?>"/>
                        <?php esc_html_e( "Make a user from this contact", 'disciple_tools' ) ?></a></li>
                <li><a data-open="link-to-user-modal">
                        <img class="dt-icon" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/link.svg' ) ?>"/>
                        <?php esc_html_e( "Link to an existing user", 'disciple_tools' ) ?></a></li>

                <div class="reveal" id="make-user-from-contact-modal" data-reveal data-reset-on-close>
                    <h3><?php echo esc_html_x( 'Make User From Contact', 'Make user modal', 'disciple_tools' )?></h3>

                        <?php if ( isset( $contact['corresponds_to_user'] ) ) : ?>
                            <p><strong><?php echo esc_html_x( "This contact is already connected to a user.", 'Make user modal', 'disciple_tools' ) ?></strong></p>
                        <?php else : ?>

                        <p><?php echo esc_html_x( "This will invite this contact to become a user of this system. By default, they will be given the role of a 'multiplier', but you can change that in the dropdown below.", 'Make user modal', 'disciple_tools' ) ?></p>
                        <p><?php echo esc_html_x( "In the fields below, enter their username, email address, a 'Display Name' which they can change later and a User Role.", 'Make user modal', 'disciple_tools' ) ?></p>

                        <form id="create-user-form">
                            <label for="user-username">
                                <?php esc_html_e( "Username", "disciple_tools" ); ?>
                            </label>
                            <input name="user-user_login" id="user-user_login" type="text" placeholder="username" required aria-describedby="username-help-text">
                            <p class="help-text" id="username-help-text"><?php esc_html_e( "This is required", "disciple_tools" ); ?></p>
                            <label for="user-email">
                                <?php esc_html_e( "Email", "disciple_tools" ); ?>
                            </label>
                            <input name="user-email" id="user-email" type="email" value="<?php echo ( isset( $contact['contact_email'][0]['value'] ) ) ? esc_html( $contact['contact_email'][0]['value'] ) : ''; ?>" placeholder="user@example.com" required aria-describedby="email-help-text">
                            <p class="help-text" id="email-help-text"><?php esc_html_e( "This is required", "disciple_tools" ); ?></p>
                            <label for="user-display">
                                <?php esc_html_e( "Display Name", "disciple_tools" ); ?>
                                <input name="user-display" id="user-display" type="text"
                                       value="<?php the_title_attribute(); ?>"
                                       placeholder="<?php esc_html_e( "Display Name", 'disciple_tools' ) ?>">
                            </label>
                            <label for="user-roles">
                                <?php esc_html_e( "User Role", "disciple_tools" ); ?>
                            </label>
                            <select name="user-user_role">
                                <?php

                                foreach ( $wp_roles->role_names as $role_key => $role_name ):

                                    if ( 'administrator' === $role_key || 'dt_admin' === $role_key ):
                                        continue;
                                    endif;

                                    ?>
                                <option value="<?php echo esc_attr( $role_key ); ?>" <?php if ( 'multiplier' === $role_key ): ?> selected <?php endif; ?>><?php echo esc_html( $role_name ); ?></option>

                                <?php endforeach; ?>
                            </select>
                            <div class="grid-x">
                                <p id="create-user-errors" style="color: red"></p>
                            </div>
                            <div class="grid-x">
                                <button class="button button-cancel clear" data-close aria-label="Close reveal" type="button">
                                    <?php echo esc_html__( 'Cancel', 'disciple_tools' )?>
                                </button>
                                <button class="button loader" type="submit" id="create-user-return">
                                    <?php esc_html_e( 'Create user', 'disciple_tools' ); ?>
                                </button>
                                <button class="close-button" data-close aria-label="Close modal" type="button">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
                <div class="reveal" id="link-to-user-modal" data-reveal data-reset-on-close style="min-height:500px">

                     <h3><?php esc_html_e( "Link this contact to an existing user", 'disciple_tools' )?></h3>

                     <?php if ( isset( $contact['corresponds_to_user'] ) ) : ?>
                         <p><?php esc_html_e( "This contact already represents a user.", 'disciple_tools' ) ?></p>
                     <?php else : ?>


                         <p><?php echo esc_html_x( "To link to an existing user, first, find the user using the field below.", 'Step 1 of link user', 'disciple_tools' ) ?></p>

                         <div class="user-select details">
                             <var id="user-select-result-container" class="result-container user-select-result-container"></var>
                             <div id="user-select_t" name="form-user-select">
                                 <div class="typeahead__container">
                                     <div class="typeahead__field">
                                        <span class="typeahead__query">
                                            <input class="js-typeahead-user-select input-height"
                                                   name="user-select[query]" placeholder="<?php echo esc_html_x( "Search Users", 'input field placeholder', 'disciple_tools' ) ?>"
                                                   autocomplete="off">
                                        </span>
                                             <span class="typeahead__button">
                                            <button type="button" class="search_user-select typeahead__image_button input-height" data-id="user-select_t">
                                                <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/chevron_down.svg' ) ?>"/>
                                            </button>
                                        </span>
                                     </div>
                                 </div>
                             </div>
                         </div>

                         <br>
                         <div class="confirm-merge-with-user" style="display: none">
                             <p><?php echo esc_html_x( "To finish the linking, merge this contact with the existing user details.", 'Step 2 of link user', 'disciple_tools' ) ?></p>
                         </div>

                     <?php endif; ?>

                     <div class="grid-x">
                         <button class="button button-cancel clear" data-close aria-label="Close reveal" type="button">
                             <?php echo esc_html__( 'Cancel', 'disciple_tools' )?>
                         </button>
                         <form action='<?php echo esc_url( site_url() );?>/contacts/mergedetails' method='get'>
                             <input type='hidden' name='currentid' value='<?php echo esc_html( $post_id );?>'/>
                             <input id="confirm-merge-with-user-dupe-id" type='hidden' name='dupeid' value=''/>
                             <button type='submit' class="button confirm-merge-with-user" style="display: none">
                                 <?php echo esc_html__( 'Merge', 'disciple_tools' )?>
                             </button>
                         </form>
                         <button class="close-button" data-close aria-label="Close modal" type="button">
                             <span aria-hidden="true">&times;</span>
                         </button>
                     </div>
                 </div>
            <?php }
        }
    }

    // filter for access to a specific record
    public function can_update_permission_filter( $has_permission, $post_id, $post_type ){
        if ( $post_type === "contacts" ){
            if ( current_user_can( 'promote_users' ) ){
                $contact_type = get_post_meta( $post_id, "type", true );
                if ( $contact_type === "user" ){
                    return true;
                }
            }
        }
        return $has_permission;
    }
}
