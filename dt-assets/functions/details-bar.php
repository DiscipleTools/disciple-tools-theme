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
 * @param bool $task
 */
function dt_print_details_bar(
    bool $share_button = false,
    bool $comment_button = false,
    bool $show_update_needed = false,
    bool $update_needed = false,
    bool $following = false,
    bool $disable_following_toggle_function = false,
    array $dispatcher_actions = [],
    bool $task = false
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
                                <span style="margin-right:5px"><?php esc_html_e( 'Update Needed', 'disciple_tools' )?>:</span>
                                <input type="checkbox" id="update-needed" class="dt-switch update-needed" <?php echo ( $update_needed ? 'checked' : "" ) ?>/>
                                <label class="dt-switch" for="update-needed" style="vertical-align: top;"></label>
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
                                            if ( $action == "make-user-from-contact-modal" ) : ?>
                                                <li><a data-open="make-user-from-contact-modal"><?php esc_html_e( "Make a user from this contact", 'disciple_tools' ) ?></a></li>
                                            <?php elseif ( $action == "link-to-user-modal") : ?>
                                                <li><a data-open="link-to-user-modal"><?php esc_html_e( "Link to an existing user", 'disciple_tools' ) ?></a></li>
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
                            <button class="button follow hollow" data-value="following" disabled><?php echo esc_html( __( "Following", "disciple_tools" ) ) ?></button>
                        <?php else :
                            if ( $following ) : ?>
                                <button class="button follow hollow" data-value="following"><?php echo esc_html( __( "Following", "disciple_tools" ) ) ?></button>
                            <?php else : ?>
                                <button class="button follow" data-value=""><?php echo esc_html( __( "Follow", "disciple_tools" ) ) ?></button>
                            <?php endif; ?>
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
        <div class="grid-x align-center" style="align-items: center">
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
            <?php endif; ?>
            <div class="cell shrink">
                <?php if ( $disable_following_toggle_function ) : ?>
                    <button class="button follow hollow" data-value="following" disabled><?php echo esc_html( __( "Following", "disciple_tools" ) ) ?></button>
                <?php else :
                    if ( $following ) : ?>
                        <button class="button follow hollow" data-value="following"><?php echo esc_html( __( "Following", "disciple_tools" ) ) ?></button>
                    <?php else : ?>
                        <button class="button follow" data-value=""><?php echo esc_html( __( "Follow", "disciple_tools" ) ) ?></button>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            <?php if ( $task ) : ?>
                <div class="cell shrink center-items">
                    <button class="button open-set-task">
                        <?php esc_html_e( 'Tasks', 'disciple_tools' ); ?>
                        <i class="fi-clock"></i>
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </nav>
    <?php endif;
}

