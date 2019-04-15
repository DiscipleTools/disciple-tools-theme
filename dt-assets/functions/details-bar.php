<?php
declare(strict_types=1);


/**
 * @param bool $share_button
 * @param bool $comment_button
 * @param bool $show_update_needed
 * @param bool $update_needed
 * @param bool $following
 * @param bool $disable_following_toggle_function
 * @param array $dispatcher_actions
 */
function dt_print_details_bar(
    bool $share_button = false,
    bool $comment_button = false,
    bool $show_update_needed = false,
    bool $update_needed = false,
    bool $following = false,
    bool $disable_following_toggle_function = false,
    array $dispatcher_actions = []
) {
    ?>

    <div data-sticky-container class="hide-for-small-only" style="z-index: 9">
        <nav role="navigation"
             data-sticky data-options="marginTop:3;" style="width:100%" data-sticky-on="medium"
             class="second-bar">
            <div class="container-width">

                <div class="grid-x grid-margin-x">
                    <div class="cell small-4 grid-x grid-margin-x">
                        <div class="cell grid-x shrink center-items">
                            <?php if ( $show_update_needed ){ ?>
                            <div style="margin-right:5px"><span><?php esc_html_e( 'Update Needed', 'disciple_tools' )?></span></div>
                            <div class="switch tiny cell shrink" style="margin-bottom: 0px">
                                <input class="switch-input update-needed" id="update-needed" type="checkbox" name="update-needed"
                                <?php echo ( $update_needed ? 'checked' : "" ) ?>>
                                <label class="switch-paddle update-needed" for="update-needed">
                                    <span class="show-for-sr"><?php esc_html_e( 'Update Needed', 'disciple_tools' )?></span>
                                    <span class="switch-active" aria-hidden="true"><?php esc_html_e( 'Yes', 'disciple_tools' )?></span>
                                    <span class="switch-inactive" aria-hidden="false"><?php esc_html_e( 'No', 'disciple_tools' )?></span>
                                </label>
                            </div>
                            <?php } ?>
                        </div>
                        <div class="cell grid-x shrink center-items">
                            <?php if ( sizeof( $dispatcher_actions ) > 0 ): ?>
                            <ul class="dropdown menu" data-dropdown-menu dropdownmenu-arrow-color="white">
                                <li style="border-radius: 5px">
                                    <a class="button menu-white-dropdown-arrow"
                                       style="background-color: #00897B; color: white;">
                                        <?php esc_html_e( "Dispatcher actions", 'disciple_tools' ) ?></a>
                                    <ul class="menu">
                                        <?php foreach ( $dispatcher_actions as $action ) :
                                            if ( $action == "make_user_from_contact" ) : ?>
                                                <li><a data-open="make_user_from_contact"><?php esc_html_e( "Make a user from this contact", 'disciple_tools' ) ?></a></li>
                                            <?php elseif ( $action == "link_to_user") : ?>
                                                <li><a data-open="link_to_user"><?php esc_html_e( "Link to an existing user", 'disciple_tools' ) ?></a></li>
                                            <?php elseif ( $action == "merge_with_contact") : ?>
                                                <li><a id="open_merge_with_contact"><?php esc_html_e( "Merge with another contact", 'disciple_tools' ) ?></a></li>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </ul>
                                </li>
                            </ul>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="cell small-4 center hide-for-small-only">
                        <strong><?php the_title_attribute(); ?></strong>
                    </div>
                    <div class="cell small-4 align-right grid-x grid-margin-x">
                        <div class="cell shrink center-items ">
                            <?php if ( $disable_following_toggle_function ) : ?>
                            <span style="color: black"><?php echo esc_html( __( "Following", "disciple_tools" ) ) ?></span>
                            <?php else : ?>
                                <span style="margin-right:5px"><?php esc_html_e( 'Follow', 'disciple_tools' )?>:</span>
                                <input type="checkbox" id="follow-switch" class="dt-switch follow" <?php echo ( $following ? 'checked' : "" ) ?>/>
                                <label class="dt-switch" for="follow-switch" style="vertical-align: top;"></label>
                            <?php endif; ?>
                        </div>
                        <?php if ( $share_button ): ?>
                        <div class="cell shrink center-items ">
                            <button class="center-items open-share">
                                <img src="<?php echo esc_url( get_template_directory_uri() . "/dt-assets/images/share.svg" ) ?>">
                                <span style="margin:0 10px 2px 10px"><?php esc_html_e( "Share", "disciple_tools" ); ?></span>
                            </button>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </nav>
    </div>

    <?php if ( $comment_button || $share_button ): ?>
    <nav  role="navigation" style="width:100%;"
          class="second-bar show-for-small-only">
        <?php if ( $comment_button ): ?>
        <div class="grid-x align-center">
            <div class="cell shrink">
                <button  id="nav-view-comments" class="center-items">
                    <a href="#comment-activity-section" class="center-items" style="color:black">
                        <img src="<?php echo esc_url( get_template_directory_uri() . "/dt-assets/images/view-comments.svg" ); ?>">
                        <span style="margin:0 10px 0 10px"><?php esc_html_e( "Comments", "disciple_tools" ); ?></span>
                    </a>
                </button>
            </div>
        <?php endif; ?>
        <?php if ( $share_button ): ?>
            <div class="cell shrink">
                <button class="center-items open-share">
                    <img src="<?php echo esc_url( get_template_directory_uri() . "/dt-assets/images/share.svg" ) ?>">
                    <span style="margin:2px 10px 0 10px"><?php esc_html_e( "Share", "disciple_tools" ); ?></span>
                </button>
            </div>
            <div class="cell shrink">
                <?php if ( $disable_following_toggle_function ) : ?>
                    <span style="color: black"><?php echo esc_html( __( "Following", "disciple_tools" ) ) ?></span>
                <?php else : ?>
                    <span><?php esc_html_e( 'Follow', 'disciple_tools' )?>:</span>
                    <input type="checkbox" id="follow-switch" class="dt-switch follow" <?php echo ( $following ? 'checked' : "" ) ?>/>
                    <label class="dt-switch" for="follow-switch" style="vertical-align: top;"></label>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </nav>
    <?php endif;
}

