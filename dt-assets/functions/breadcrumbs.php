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

function dt_print_breadcrumbs( array $links = null, string $current, bool $share_button = false, bool $comment_button = false ) {

    if ( is_null( $links ) ) {
        $links = [
            [ home_url( '/' ), __( "Dashboard" ) ],
        ];
    }

    ?>

    <!-- Breadcrumb Navigation-->
    <div data-sticky-container class="hide-for-small-only">
        <nav aria-label="<?php esc_attr_e( "You are here:" ); ?>" role="navigation"
             data-sticky data-options="marginTop:3;" style="width:100%" data-sticky-on="medium"
             class="second-bar">

            <div class="grid-x">
                <div class="small-offset-4 cell small-4 center-items hide-for-small-only">
                    <ul class="breadcrumbs">

                        <?php foreach ($links as $link) : ?>
                            <li><a href="<?php echo esc_url( $link[0] ); ?>"><?php echo esc_html( $link[1] ); ?></a></li>
                        <?php endforeach; ?>
                        <li>
                            <span class="show-for-sr"><?php esc_html_e( "Current:", 'disciple_tools' ); ?> </span> <?php echo esc_html( $current ); ?>
                        </li>
                    </ul>
                </div>
                <?php if ( $share_button ): ?>
                    <div class="cell small-4 align-right grid-x">
                        <div class="cell shrink ">
                            <button data-open="share-contact-modal" class="center-items">
                                <img src="<?php echo esc_url( get_template_directory_uri() . "/dt-assets/images/share.svg" ) ?>">
                                <span style="margin:0 10px 0 10px"><?php esc_html_e( "Share" ); ?></span>
                            </button>
                        </div>
                    </div>
                <?php endif; ?>
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
                <button data-open="share-contact-modal" class="center-items">
                    <img src="<?php echo esc_url( get_template_directory_uri() . "/dt-assets/images/share.svg" ) ?>">
                    <span style="margin:0 10px 0 10px"><?php esc_html_e( "Share" ); ?></span>
                </button>
            </div>
        </div>
        <?php endif; ?>
    </nav>
    <?php endif;
}

