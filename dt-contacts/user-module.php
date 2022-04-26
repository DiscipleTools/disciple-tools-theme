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
        add_action( 'dt_record_notifications_section', [ $this, "dt_record_notifications_section" ], 10, 2 );

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

    public function dt_record_notifications_section( $post_type, $dt_post ){
        if ( $post_type === "contacts" && isset( $dt_post["type"]["key"] ) && $dt_post["type"]["key"] === "user" ): ?>
            <section class="cell small-12 user-contact-notification">
                <div class="bordered-box detail-notification-box" style="background-color:#3F729B">
                    <?php if ( isset( $dt_post["corresponds_to_user"] ) && (int) $dt_post["corresponds_to_user"] === get_current_user_id() ):
                        ?>
                        <h4><?php esc_html_e( 'This contact represents you as a user.', 'disciple_tools' )?></h4>
                        <p><?php esc_html_e( 'Please update contact details on your profile page instead of here.', 'disciple_tools' ); ?> <a style="color:white; font-weight: bold" href="<?php echo esc_html( site_url( '/settings' ) ); ?>"><?php echo esc_html( "Profile Settings" ); ?> <img class="dt-icon dt-white-icon" style="margin:0" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/open-link.svg' ) ?>"/></a></p>
                    <?php else : ?>
                        <h4>
                        <?php esc_html_e( 'This contact represents a user.', 'disciple_tools' );
                        if ( isset( $dt_post["corresponds_to_user"] ) && !empty( $dt_post["corresponds_to_user"] && DT_User_Management::has_permission() ) ): ?>
                            <a style="color:white; " href="<?php echo esc_html( site_url( '/user-management/user/'. $dt_post["corresponds_to_user"] ) ); ?>"><?php echo esc_html( "View" ); ?> <img class="dt-icon dt-white-icon" style="margin:0" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/open-link.svg' ) ?>"/></a>
                        <?php endif; ?>
                        </h4>
                    <?php endif; ?>
                </div>
            </section>
        <?php endif;
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
        if ( $post_type === "contacts" ){
            $contact = DT_Posts::get_post( $post_type, $post_id );
            if ( current_user_can( "create_users" ) || DT_User_Management::non_admins_can_make_users() ){
                ?>
                <li>
                    <a target="_blank" href="<?php echo esc_html( home_url( '/' ) ); ?>user-management/add-user?contact_id=<?php echo esc_html( $post_id ); ?>">
                        <img class="dt-icon" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/arrow-user.svg' ) ?>"/>
                        <?php esc_html_e( 'Make a user from this contact', 'disciple_tools' ) ?>
                    </a>
                </li>
                <?php if ( current_user_can( "create_users" ) ): ?>

                    <li><a data-open="link-to-user-modal">
                            <img class="dt-icon" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/link.svg' ) ?>"/>
                            <?php esc_html_e( "Link to an existing user", 'disciple_tools' ) ?></a></li>

                <?php endif; ?>

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
