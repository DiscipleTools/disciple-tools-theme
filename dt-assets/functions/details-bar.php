<?php
declare(strict_types=1);
/**
 * @param bool $share_button
 * @param bool $comment_button
 * @param bool $show_update_needed
 * @param bool $update_needed
 * @param bool $following
 * @param bool $disable_following_toggle_function
 * @param bool $task
 */
function dt_print_details_bar(
    bool $share_button = false,
    bool $comment_button = false,
    bool $show_update_needed = false,
    bool $update_needed = false,
    bool $following = false,
    bool $disable_following_toggle_function = false,
    bool $task = false
) {
    $dt_post_type = get_post_type();
    $post_id = get_the_ID();
    $post_settings = DT_Posts::get_post_settings( $dt_post_type );
    $dt_post = DT_Posts::get_post( $dt_post_type, $post_id );
    ?>

    <div data-sticky-container class="show-for-medium" style="z-index: 9">
        <nav role="navigation"
             data-sticky data-options="marginTop:3;" style="width:100%" data-sticky-on="medium"
             class="second-bar" id="second-bar-large">
            <div class="container-width">

                <div class="grid-x grid-margin-x">
                    <div class="cell small-4 grid-x grid-margin-x">
                        <div class="cell grid-x shrink center-items">
                            <?php if ( $show_update_needed ){ ?>
                                <span style="margin-right:5px"><?php esc_html_e( 'Update Needed', 'disciple_tools' )?>:</span>
                                <input type="checkbox" id="update-needed-large" class="dt-switch update-needed" <?php echo ( $update_needed ? 'checked' : "" ) ?>/>
                                <label class="dt-switch" for="update-needed-large" style="vertical-align: top;"></label>
                            <?php } ?>
                        </div>
                        <div class="cell grid-x shrink center-items">
                            <ul class="dropdown menu" data-dropdown-menu dropdownmenu-arrow-color="white">
                                <li style="border-radius: 5px">
                                    <a class="button menu-white-dropdown-arrow"
                                       style="background-color: #00897B; color: white;">
                                        <?php esc_html_e( "Admin Actions", 'disciple_tools' ) ?></a>
                                    <ul class="menu is-dropdown-submenu">
                                        <?php if ( DT_Posts::can_delete( $dt_post_type, $post_id ) ) : ?>
                                            <li><a data-open="delete-record-modal">
                                                    <img class="dt-icon" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/trash.svg' ) ?>"/>
                                                    <?php echo esc_html( sprintf( _x( "Delete %s", "Delete Contact", 'disciple_tools' ), DT_Posts::get_post_settings( $dt_post_type )["label_singular"] ) ) ?></a></li>
                                        <?php endif; ?>
                                        <?php do_action( 'dt_record_admin_actions', $dt_post_type, $post_id ); ?>
                                    </ul>
                                </li>
                            </ul>
                        </div>
                        <div class="cell grid-x shrink center-items">
                            <span id="admin-bar-issues"></span>
                        </div>
                    </div>
                    <div class="cell small-4 center hide-for-small-only">
                            <?php $picture = apply_filters( 'dt_record_picture', null, $dt_post_type, $post_id );
                             $type_color = isset( $dt_post['type'], $post_settings["fields"]["type"]["default"][$dt_post['type']["key"]]["color"] ) ? $post_settings["fields"]["type"]["default"][$dt_post['type']["key"]]["color"] : "#000000";
                            if ( !empty( $picture ) ) : ?>
                                <img src="<?php echo esc_html( $picture )?>" style="height:30px; vertical-align:middle">
                            <?php else : ?>
                                <?php
                                    $gender = isset( $dt_post["gender"] ) ? $dt_post["gender"]["key"] : "male";?>
                                <i class="fi-torso<?php echo esc_html( ( $gender == 'female' ) ? '-'.$gender : "" ) ?> medium" style=" color:<?php echo esc_html( $type_color ); ?>"></i>
                            <?php endif; ?>
                            <span id="title" contenteditable="true" class="title dt_contenteditable"><?php the_title_attribute(); ?></span>
                            <br>
                            <?php do_action( 'dt_post_record_name_tagline' ); ?>
                            <span style="font-size: 10px; display: inline-block; ">
                            <?php if ( isset( $dt_post["type"]["label"] ) ) : ?>
                                <a data-open="contact-type-modal" style="font-size: 10px"><?php echo esc_html( $dt_post["type"]["label"] ?? "" )?> <?php esc_html_e( 'Record', 'disciple_tools' ); ?></a>
                            <?php endif; ?>
                                <?php echo esc_html( sprintf( _x( 'Created on %s', 'Created on the 21st of August', 'disciple_tools' ), $dt_post["post_date"]["formatted"] ) );
                                if ( $dt_post["post_author_display_name"] ):
                                    echo esc_html( ' ' . sprintf( _x( 'by %s', '(record created) by multiplier1', 'disciple_tools' ), $dt_post["post_author_display_name"] ) );
                                endif; ?>
                            </span>
                    </div>
                    <div class="cell small-4 align-right grid-x grid-margin-x">
                        <?php if ( $task ) : ?>
                        <div class="cell shrink center-items">
                            <button class="button open-set-task">
                                <?php esc_html_e( 'Tasks', 'disciple_tools' ); ?>
                                <i class="fi-clock"></i>
                            </button>
                        </div>
                        <?php endif; ?>
                        <div class="cell shrink center-items">
                        <?php if ( $disable_following_toggle_function ) : ?>
                            <button class="button follow hollow" data-value="following" disabled><?php echo esc_html( __( "Following", "disciple_tools" ) ) ?>
                                <i class="fi-eye"></i>
                            </button>
                        <?php else :
                            if ( $following ) : ?>
                                <button class="button follow hollow" data-value="following"><?php echo esc_html( __( "Following", "disciple_tools" ) ) ?>
                                    <i class="fi-eye"></i>
                                </button>
                            <?php else : ?>
                                <button class="button follow" data-value=""><?php echo esc_html( __( "Follow", "disciple_tools" ) ) ?>
                                    <i class="fi-eye"></i>
                                </button>
                            <?php endif; ?>
                        <?php endif; ?>
                        </div>
                        <?php if ( $share_button ): ?>
                        <div class="cell shrink center-items ">
                            <button class="center-items open-share">
                                <img class="dt-blue-icon" src="<?php echo esc_url( get_template_directory_uri() . "/dt-assets/images/share.svg" ) ?>">
                                <span style="margin:0 10px 2px 10px"><?php esc_html_e( "Share", "disciple_tools" ); ?></span>
                            </button>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </nav>
    </div>

    <div data-sticky-container class="show-for-small-only" style="z-index: 9">
    <?php if ( $comment_button || $share_button ): ?>
    <nav role="navigation"
        data-sticky data-options="marginTop:0;" data-sticky-on="small" data-top-anchor="95" style="width:100%; border-color: <?php echo esc_html( $type_color ); ?>"
        class="second-bar" id="second-bar-small">
        <?php if ( $comment_button ): ?>
            <div class="container-width">
            <div class="grid-x align-center" style="align-items: center">
                <div class="cell shrink">
                    <button  id="nav-view-comments" class="center-items">
                        <a href="#comment-activity-section" class="center-items" style="color:black">
                            <img src="<?php echo esc_url( get_template_directory_uri() . "/dt-assets/images/view-comments.svg" ); ?>">
                        </a>
                    </button>
                </div>
                <?php endif; ?>
                <?php if ( $share_button ): ?>
                    <div class="cell shrink">
                        <button class="center-items open-share">
                            <img class="dt-blue-icon" src="<?php echo esc_url( get_template_directory_uri() . "/dt-assets/images/share.svg" ) ?>">
                        </button>
                    </div>
                <?php endif; ?>
                <div class="cell shrink">
                    <?php if ( $disable_following_toggle_function ) : ?>
                        <button class="button follow mobile hollow" data-value="following" disabled>
                            <i class="fi-eye"></i>
                        </button>
                    <?php else :
                        if ( $following ) : ?>
                            <button class="button follow mobile hollow" data-value="following">
                                <i class="fi-eye"></i>
                            </button>
                        <?php else : ?>
                            <button class="button follow mobile" data-value="">
                                <i class="fi-eye"></i>
                            </button>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                <?php if ( $task ) : ?>
                    <div class="cell shrink center-items">
                        <button class="button open-set-task">
                            <i class="fi-clock"></i>
                        </button>
                    </div>
                <?php endif; ?>
                <div class="cell shrink center-item">
                    <?php if ( $show_update_needed ){ ?>
                        <span style="margin-right:5px"><?php esc_html_e( 'Update Needed', 'disciple_tools' )?>:</span>
                        <input type="checkbox" id="update-needed-small" class="dt-switch update-needed" <?php echo ( $update_needed ? 'checked' : "" ) ?>/>
                        <label class="dt-switch" for="update-needed-small" style="vertical-align: top;"></label>
                    <?php } ?>
                </div>
            </div>
            <div class="cell small-12 center">
                <?php $picture = apply_filters( 'dt_record_picture', null, $dt_post_type, $post_id );
                    $type_color = isset( $dt_post['type'], $post_settings["fields"]["type"]["default"][$dt_post['type']["key"]]["color"] ) ? $post_settings["fields"]["type"]["default"][$dt_post['type']["key"]]["color"] : "#000000";
                if ( !empty( $picture ) ) : ?>
                    <img src="<?php echo esc_html( $picture )?>" style="height:30px; vertical-align:middle">
                <?php else : ?>
                    <?php $gender = isset( $dt_post["gender"] ) ? $dt_post["gender"]["key"] : "male";?>
                    <i class="fi-torso<?php echo esc_html( ( $gender == 'female' ) ? '-'.$gender : "" ) ?> medium" style=" color:<?php echo esc_html( $type_color ); ?>"></i>
                <?php endif; ?>
                <span id="title" contenteditable="true" class="title dt_contenteditable"><?php the_title_attribute(); ?></span>
            </div>
            <div id="record-tagline" class="cell small-12 center">
                <?php do_action( 'dt_post_record_name_tagline' ); ?>
                <span style="font-size: 10px; display: inline-block; ">
                <?php if ( isset( $dt_post["type"]["label"] ) ) : ?>
                    <a data-open="contact-type-modal" style="font-size: 10px"><?php echo esc_html( $dt_post["type"]["label"] ?? "" )?> <?php esc_html_e( 'Record', 'disciple_tools' ); ?></a>
                <?php endif; ?>
                    <?php echo esc_html( sprintf( _x( 'Created on %s', 'Created on the 21st of August', 'disciple_tools' ), $dt_post["post_date"]["formatted"] ) );
                    if ( $dt_post["post_author_display_name"] ):
                        echo esc_html( ' ' . sprintf( _x( 'by %s', '(record created) by multiplier1', 'disciple_tools' ), $dt_post["post_author_display_name"] ) );
                    endif; ?>
                </span>
            </div>
                </div>
    </nav>
    </div>
    <?php endif;
}
