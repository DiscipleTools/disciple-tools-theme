<?php
declare(strict_types=1);

/**
 * Print the HTML for the bar showing the breadcrumbs. Returns void.
 *
 * @param array | null $links
 *      An list-like array of two-tuples, with the first element being the
 *      unescaped URL, and the second element being the unescaped caption.
 *      For example:
 *          $links = [
 *              [ "http://example.com/albums", "Albums" ],
 *              [ "http://example.com/albums/my-holiday", "My Holiday" ],
 *          ];
 *      If set to null, a default list of breadcrumbs will be used.
 * @param string $current
 *      The caption for the final element in the breadcrumbs list, that is not
 *      a link as it represents the current page.
 * @param bool $share_button
 * @return void
 */

function dt_print_breadcrumbs(
    array $links = null,
    string $current,
    bool $share_button = false,
    bool $comment_button = false,
    bool $show_update_needed = false,
    bool $update_needed = false
) {

    if ( is_null( $links ) ) {
        $links = [
            [ home_url( '/' ), __( "Dashboard" ) ],
        ];
    }

    ?>

    <!-- Breadcrumb Navigation-->
    <?php if ( $show_update_needed || $share_button ): ?>
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
    <!--                    <ul class="breadcrumbs">-->
    <!---->
    <!--                        --><?php //foreach ($links as $link) : ?>
    <!--                            <li><a href="--><?php //echo esc_url( $link[0] ); ?><!--">--><?php //echo esc_html( $link[1] ); ?><!--</a></li>-->
    <!--                        --><?php //endforeach; ?>
    <!--                        <li>-->
    <!--                            <span class="show-for-sr">--><?php //esc_html_e( "Current:", 'disciple_tools' ); ?><!-- </span> --><?php //echo esc_html( $current ); ?>
    <!--                        </li>-->
    <!--                    </ul>-->
                    </div>
                    <?php if ( $share_button ): ?>
                        <div class="cell small-4 align-right grid-x grid-margin-x">
                            <div class="cell shrink center-items ">
                                <button class="center-items open-share">
                                    <img src="<?php echo esc_url( get_template_directory_uri() . "/dt-assets/images/share.svg" ) ?>">
                                    <span style="margin:0 10px 0 10px"><?php esc_html_e( "Share" ); ?></span>
                                </button>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
    </div>
    <?php endif; ?>

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

