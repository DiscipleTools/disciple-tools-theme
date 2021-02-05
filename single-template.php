<?php
declare( strict_types=1 );

dt_please_log_in();

$dt_post_type = get_post_type();
if ( ! current_user_can( 'access_' . $dt_post_type ) ) {
    wp_safe_redirect( '/settings' );
    exit();
}

( function () {
    $post_type = get_post_type();
    $post_id = get_the_ID();
    if ( !DT_Posts::can_view( $post_type, $post_id )){
        get_template_part( "403" );
        die();
    }
    $current_user_id = get_current_user_id();
    $post_settings = DT_Posts::get_post_settings( $post_type );
    $dt_post = DT_Posts::get_post( $post_type, $post_id );
    $tiles = DT_Posts::get_post_tiles( $post_type );
    $following = DT_Posts::get_users_following_post( $post_type, $post_id );
    Disciple_Tools_Notifications::process_new_notifications( get_the_ID() ); // removes new notifications for this post
    get_header();
    dt_print_details_bar(
        true,
        true,
        current_user_can( "assign_any_contacts" ),
        isset( $dt_post["requires_update"] ) && $dt_post["requires_update"] === true,
        in_array( $current_user_id, $following ),
        isset( $dt_post["assigned_to"]["id"] ) ? $dt_post["assigned_to"]["id"] == $current_user_id : false,
        true
    );
    ?>
    <div id="content" class="single-template">
        <div id="inner-content" class="grid-x grid-margin-x grid-margin-y">

            <?php do_action( 'dt_record_top_full_with', $post_type, $dt_post ) ?>

            <main id="main" class="large-7 medium-12 small-12 cell" role="main" style="padding:0">

                <div class="cell grid-y grid-margin-y">


                    <!-- Requires update block -->
                    <section class="cell small-12 update-needed-notification"
                             style="display: <?php echo esc_html( ( isset( $dt_post['requires_update'] ) && $dt_post['requires_update'] === true ) ? "block" : "none" ) ?> ">
                        <a href="#comment-activity-section" class="hide-for-large">
                            <div class="bordered-box detail-notification-box" style="background-color:#F43636">
                                <h4>
                                    <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/alert-circle-exc.svg' ) ?>"/>
                                    <?php echo esc_html( sprintf( __( 'This %s needs an update.', 'disciple_tools' ), strtolower( $post_settings["label_singular"] ) ) ) ?>
                                </h4>
                                <p><?php esc_html_e( 'Please provide an update by posting a comment.', 'disciple_tools' )?></p>
                            </div>
                        </a>
                        <div class="bordered-box detail-notification-box show-for-large" style="background-color:#F43636">
                                <h4>
                                    <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/alert-circle-exc.svg' ) ?>"/>
                                    <?php echo esc_html( sprintf( __( 'This %s needs an update.', 'disciple_tools' ), strtolower( $post_settings["label_singular"] ) ) ) ?>
                                </h4>
                                <p><?php esc_html_e( 'Please provide an update by posting a comment.', 'disciple_tools' )?></p>
                            </div>
                    </section>

                    <?php do_action( 'dt_record_notifications_section', $post_type, $dt_post ); ?>


                    <?php do_action( 'dt_record_top_above_details', $post_type, $dt_post ); ?>

                    <!--
                        Status section
                    -->
                    <?php if ( isset( $tiles["status"] ) && empty( $tiles["status"]["hidden"] ) ) : ?>
                    <section id="contact-status" class="small-12 cell bordered-box">
                        <h3 class="section-header">
                            <?php echo esc_html__( "Status", "disciple_tools" )?>
                            <button class="help-button-tile" data-tile="status">
                                <img class="help-icon" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/help.svg' ) ?>"/>
                            </button>
                        </h3>

                        <div class="grid-x grid-margin-x">
                        <?php do_action( "dt_details_additional_section", 'status', $post_type ); ?>
                        <?php
                        //setup the order of the tile fields
                        $order = $tiles['status']["order"] ?? [];
                        foreach ( $post_settings["fields"] as $key => $option ){
                            if ( isset( $option["tile"] ) && $option["tile"] === 'status' ){
                                if ( !in_array( $key, $order )){
                                    $order[] = $key;
                                }
                            }
                        }
                        foreach ( $order as $field_key ) {
                            if ( !isset( $post_settings["fields"][$field_key] ) ){
                                continue;
                            }

                            $field = $post_settings["fields"][$field_key];
                            if ( isset( $field["tile"] ) && $field["tile"] === 'status' && !( isset( $field["only_for_types"], $dt_post["type"]["key"] ) && !in_array( $dt_post["type"]["key"], $field["only_for_types"] ) ) && empty( $field["hidden"] ) ) {
                                ?>
                                <div class="cell small-12 medium-4">
                                    <?php render_field_for_display( $field_key, $post_settings["fields"], $dt_post, true ); ?>
                                </div>
                            <?php }
                        }
                        ?>
                        </div>
                    </section>
                    <?php endif; ?>

                    <!--
                        Main details section
                    -->
                    <section id="details-tile" class="small-12 cell bordered-box collapsed" >
                        <h3 class="section-header">
                            <?php echo esc_html__( "Details", "disciple_tools" )?>
                            <button class="help-button-tile" data-tile="details">
                                <img class="help-icon" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/help.svg' ) ?>"/>
                            </button>
                            <button class="section-chevron chevron_down show-details-section">
                                <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/chevron_down.svg' ) ?>"/>
                            </button>
                            <button class="section-chevron chevron_up show-details-section">
                                <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/chevron_up.svg' ) ?>"/>
                            </button>
                        </h3>

                        <!--
                            Details section
                        -->
                        <div class="collapsed-details-section">

                            <!-- row for communication channel elements -->
                            <div class="detail-snippet-row">
                            <?php
                            //setup the order of the tile fields
                            $order = $tiles['details']["order"] ?? [];
                            foreach ( $post_settings["fields"] as $key => $option ){
                                if ( isset( $option["tile"] ) && $option["tile"] === 'details' && $option['type'] === "communication_channel" ){
                                    if ( !in_array( $key, $order )){
                                        $order[] = $key;
                                    }
                                }
                            }
                            foreach ( $order as $field_key ) {
                                if ( !isset( $post_settings["fields"][$field_key] ) ){
                                    continue;
                                }

                                $field = $post_settings["fields"][$field_key];
                                if ( isset( $field["tile"] ) && $field["tile"] === 'details'){
                                    ?>
                                    <div class="detail-snippet" id="collapsed-detail-<?php echo esc_html( $field_key ); ?>">
                                        <?php if ( isset( $field["icon"] ) ) : ?>
                                            <img src="<?php echo esc_html( $field["icon"] ); ?>">
                                        <?php else : ?>
                                            <strong class="snippet-field-name"><?php echo esc_html( $field['name'] ); ?></strong>
                                        <?php endif ?>
                                        <span class="collapsed-items"></span>
                                    </div>
                                    <?php
                                }
                            }
                            ?>
                            </div>

                            <!-- row for misc elements -->
                            <div class="detail-snippet-row">
                            <?php
                            $order = $tiles['details']["order"] ?? [];
                            foreach ( $post_settings["fields"] as $key => $option ){
                                if ( isset( $option["tile"] ) && $option["tile"] === 'details' && $option['type'] !== "communication_channel" ){
                                    if ( !in_array( $key, $order ) && !in_array( $key, [ 'name' ] ) ) {
                                        $order[] = $key;
                                    }
                                }
                            }
                            foreach ( $order as $field_key ) {
                                if ( !isset( $post_settings["fields"][$field_key] ) ){
                                    continue;
                                }

                                $field = $post_settings["fields"][$field_key];
                                if ( isset( $dt_post['type']["key"], $fields[$field_key]["only_for_types"] ) ) {
                                    if ( !in_array( $dt_post['type']["key"], $fields[$field_key]["only_for_types"] ) ) {
                                        return;
                                    }
                                }

                                if ( isset( $field["tile"] ) && $field["tile"] === 'details'){
                                    ?>
                                        <div class="detail-snippet" id="collapsed-detail-<?php echo esc_html( $field_key ); ?>">
                                            <?php if ( isset( $field["icon"] ) ) : ?>
                                                <img src="<?php echo esc_html( $field["icon"] ); ?>">
                                            <?php else : ?>
                                                <strong class="snippet-field-name"><?php echo esc_html( $field['name'] ); ?></strong>
                                            <?php endif ?>
                                                <span class="collapsed-items"></span>
                                        </div>
                                    <?php
                                }
                            }
                            ?>
                            </div>
                        </div> <!-- end collapse details section -->
                        <div id="show-details-edit-button" class="show-details-section" style="text-align: center; background-color:rgb(236, 245, 252);margin: 3px -15px -15px -15px; border-radius: 0 0 10px 10px;">
                            <a class="button clear " style="margin:0;padding:3px 0; width:100%">
                                <?php esc_html_e( 'Edit all details fields', 'disciple_tools' ); ?>
                            </a></div>

                        <div id="details-section" class="display-fields" style="display: none; margin-top:20px">
                            <div class="grid-x grid-margin-x">
                                <?php
                                // let the plugin add section content
                                do_action( "dt_details_additional_section", 'details', $post_type );
                                //setup the order of the tile fields
                                $order = $tiles['details']["order"] ?? [];
                                foreach ( $post_settings["fields"] as $key => $option ){
                                    if ( isset( $option["tile"] ) && $option["tile"] === 'details' ){
                                        if ( !in_array( $key, $order )){
                                            $order[] = $key;
                                        }
                                    }
                                }
                                foreach ( $order as $field_key ) {
                                    if ( !isset( $post_settings["fields"][$field_key] ) ){
                                        continue;
                                    }
                                    if ( isset( $post_settings["fields"][$field_key]["hidden"] ) && true === $post_settings["fields"][$field_key]["hidden"] ){
                                        continue;
                                    }

                                    $field = $post_settings["fields"][$field_key];
                                    if ( isset( $field["tile"] ) && $field["tile"] === 'details'){ ?>
                                        <div class="cell small-12 medium-6">
                                            <?php render_field_for_display( $field_key, $post_settings["fields"], $dt_post ); ?>
                                        </div>
                                    <?php }
                                }
                                ?>
                            </div>
                        </div>
                    </section>


                    <!--
                        Tiles Section
                    -->
                    <div class="cell small-12">
                        <div class="grid-x grid-margin-x grid-margin-y grid">
                            <?php
                            foreach ( $tiles as $tile_key => $tile_options ){
                                if ( ( isset( $tile_options["hidden"] ) && $tile_options["hidden"] == true ) || in_array( $tile_key, [ 'details', 'status' ] ) ) {
                                    continue;
                                }
                                ?>
                                <section id="<?php echo esc_html( $tile_key ) ?>" class="xlarge-6 large-12 medium-6 cell grid-item">
                                    <div class="bordered-box" id="<?php echo esc_html( $tile_key ) ?>-tile">
                                        <?php
                                        //setup tile label if see by customizations
                                        if ( isset( $tile_options["label"] ) ){ ?>
                                            <h3 class="section-header">
                                                <?php echo esc_html( $tile_options["label"] )?>
                                                <button class="help-button-tile" data-tile="<?php echo esc_html( $tile_key ) ?>">
                                                    <img class="help-icon" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/help.svg' ) ?>"/>
                                                </button>
                                                <button class="section-chevron chevron_down">
                                                    <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/chevron_down.svg' ) ?>"/>
                                                </button>
                                                <button class="section-chevron chevron_up">
                                                    <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/chevron_up.svg' ) ?>"/>
                                                </button>
                                            </h3>
                                        <?php } ?>

                                        <div class="section-body">
                                            <?php
                                            // let the plugin add section content
                                            add_action( "dt_details_additional_section", function ( $t_key, $pt ) use ( $post_type, $tile_key, $post_settings, $dt_post, $tile_options ){
                                                if ( $pt !== $post_type || $tile_key !== $t_key ){
                                                    return;
                                                }
                                                //setup the order of the tile fields
                                                $order = $tile_options["order"] ?? [];
                                                foreach ( $post_settings["fields"] as $key => $option ){
                                                    if ( isset( $option["tile"] ) && $option["tile"] === $tile_key && !in_array( $key, $order ) ){
                                                        $order[] = $key;
                                                    }
                                                }
                                                foreach ( $order as $field_key ) {
                                                    if ( !isset( $post_settings["fields"][$field_key] ) ){
                                                        continue;
                                                    }

                                                    $field = $post_settings["fields"][$field_key];
                                                    if ( isset( $field["tile"] ) && $field["tile"] === $tile_key){
                                                        render_field_for_display( $field_key, $post_settings["fields"], $dt_post, true );
                                                    }
                                                }
                                            }, 20, 2 );
                                            do_action( "dt_details_additional_section", $tile_key, $post_type );
                                            ?>
                                        </div>
                                    </div>
                                </section>
                            <?php }
                            do_action( 'dt_record_bottom_after_tiles', $post_type, $dt_post ); ?>
                        </div>
                    </div>
                    <?php do_action( 'dt_record_bottom_below_tiles', $post_type, $dt_post ); ?>
                </div>
            </main>

            <aside class="auto cell grid-x">
                <section class="comment-activity-section cell"
                         id="comment-activity-section">
                    <?php get_template_part( 'dt-assets/parts/loop', 'activity-comment' ); ?>
                </section>
            </aside>

        </div>
    </div>

    <?php get_template_part( 'dt-assets/parts/modals/modal', 'share' ); ?>
    <?php get_template_part( 'dt-assets/parts/modals/modal', 'tasks' ); ?>
    <?php get_template_part( 'dt-assets/parts/modals/modal', 'new-contact' ); ?>

    <div class="reveal" id="create-tag-modal" data-reveal data-reset-on-close>
        <h3><?php esc_html_e( 'Create Tag', 'disciple_tools' )?></h3>
        <p><?php esc_html_e( 'Create a tag and apply it to this record.', 'disciple_tools' )?></p>

        <form class="js-create-tag">
            <label for="title">
                <?php esc_html_e( "Tag", "disciple_tools" ); ?>
            </label>
            <input name="title" id="new-tag" type="text" placeholder="<?php esc_html_e( "Tag", 'disciple_tools' ); ?>" required aria-describedby="name-help-text">
            <p class="help-text" id="name-help-text"><?php esc_html_e( "This is required", "disciple_tools" ); ?></p>
        </form>

        <div class="grid-x">
            <button class="button button-cancel clear" data-close aria-label="Close reveal" type="button">
                <?php echo esc_html__( 'Cancel', 'disciple_tools' )?>
            </button>
            <button class="button" data-close type="button" id="create-tag-return">
                <?php esc_html_e( 'Create and apply tag', 'disciple_tools' ); ?>
            </button>
            <button class="close-button" data-close aria-label="Close modal" type="button">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    </div>

    <div class="reveal" id="delete-record-modal" data-reveal data-reset-on-close>
        <h3><?php echo esc_html( sprintf( _x( "Delete %s", "Delete Contact", 'disciple_tools' ), DT_Posts::get_post_settings( $post_type )["label_singular"] ) ) ?></h3>
        <p><?php echo esc_html( sprintf( _x( "Are you sure you want to delete %s?", "Are you sure you want to delete name?", 'disciple_tools' ), $dt_post["name"] ) ) ?></p>

        <div class="grid-x">
            <button class="button button-cancel clear" data-close aria-label="Close reveal" type="button">
                <?php echo esc_html__( 'Cancel', 'disciple_tools' )?>
            </button>
            <button class="button alert loader" type="button" id="delete-record">
                <?php esc_html_e( 'Delete', 'disciple_tools' ); ?>
            </button>
            <button class="close-button" data-close aria-label="Close modal" type="button">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    </div>

    <?php do_action( "dt_record_footer", $post_type, $post_id ) ?>

    <?php get_footer();
} )();
