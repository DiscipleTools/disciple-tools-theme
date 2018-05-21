<?php
declare(strict_types=1);


/**
 * @param bool $share_button
 * @param bool $comment_button
 * @param bool $show_update_needed
 * @param bool $update_needed
 * @param bool $following
 * @param bool $disable_following_toggle_function
 */
function dt_print_details_bar(
    bool $share_button = false,
    bool $comment_button = false,
    bool $show_update_needed = false,
    bool $update_needed = false,
    bool $following = false,
    bool $disable_following_toggle_function = false
) {
?>

    <div data-sticky-container class="hide-for-small-only" style="z-index: 9">
        <nav aria-label="<?php esc_attr_e( "You are here:" ); ?>" role="navigation"
             data-sticky data-options="marginTop:3;" style="width:100%" data-sticky-on="medium"
             class="second-bar">
            <div class="container-width">

                <div class="grid-x grid-margin-x">
                    <div class="cell small-4 grid-x grid-margin-x">
                        <div class="cell grid-x grid-margin-x">
                            <?php if ( $show_update_needed ){ ?>
                            <div class="section-subheader cell shrink center-items"><?php esc_html_e( 'Update Needed', 'disciple_tools' )?></div>
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
                    </div>
                    <div class="cell small-4 center-items hide-for-small-only">
                    </div>
                    <div class="cell small-4 align-right grid-x grid-margin-x">
                        <div class="cell shrink center-items ">
                            <?php if ( $disable_following_toggle_function ) : ?>
                            <span style="color: black"><?php echo esc_html( __( "Following", "disciple_tools" ) ) ?></span>
                            <?php else : ?>
                            <div style="margin-right:5px"><span><?php esc_html_e( 'Follow', 'disciple_tools' )?></span></div>
                            <div class="switch tiny cell shrink" style="margin-bottom: 0px">
                                <input class="switch-input follow" id="follow" type="checkbox" name="follow" />
                                <?php echo ( $following ? 'checked' : "" ) ?>>
                                <label class="switch-paddle follow" for="follow">
                                    <span class="show-for-sr"><?php esc_html_e( 'Follow', 'disciple_tools' )?></span>
                                    <span class="switch-active" aria-hidden="true"><?php esc_html_e( 'Yes', 'disciple_tools' )?></span>
                                    <span class="switch-inactive" aria-hidden="false"><?php esc_html_e( 'No', 'disciple_tools' )?></span>
                                </label>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php if ( $share_button ): ?>
                        <div class="cell shrink center-items ">
                            <button class="center-items open-share">
                                <img src="<?php echo esc_url( get_template_directory_uri() . "/dt-assets/images/share.svg" ) ?>">
                                <span style="margin:0 10px 0 10px"><?php esc_html_e( "Share" ); ?></span>
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
                        <span style="margin:0 10px 0 10px"><?php esc_html_e( "Comments" ); ?></span>
                    </a>
                </button>
            </div>
        <?php endif; ?>
        <?php if ( $share_button ): ?>
            <div class="cell shrink">
                <button class="center-items open-share">
                    <img src="<?php echo esc_url( get_template_directory_uri() . "/dt-assets/images/share.svg" ) ?>">
                    <span style="margin:0 10px 0 10px"><?php esc_html_e( "Share" ); ?></span>
                </button>
            </div>
        </div>
        <?php endif; ?>
    </nav>
    <?php endif;
}

