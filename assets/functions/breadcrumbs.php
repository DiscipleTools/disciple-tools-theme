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

function dt_print_breadcrumbs( array $links = null, string $current, bool $share_button = false ) {

    if ( is_null( $links ) ) {
        $links = [
            [ home_url( '/' ), __( "Dashboard" ) ],
        ];
    }

    ?>

    <!-- Breadcrumb Navigation-->
    <div data-sticky-container>
        <nav aria-label="<?php _e( "You are here:" ); ?>" role="navigation"
            data-sticky data-options="marginTop:3;" style="width:100%" data-sticky-on="medium"
            class="second-bar hide-for-small-only">

            <div class="grid-x">
                <div class="small-offset-4 cell small-4 center-items">
                    <ul class="breadcrumbs">

                        <?php foreach ($links as $link) : ?>
                            <li><a href="<?php echo esc_attr( $link[0] ); ?>"><?php echo esc_html( $link[1] ); ?></a></li>
                        <?php endforeach; ?>
                        <li>
                            <span class="show-for-sr"><?php _e( "Current:" ); ?> </span> <?php echo esc_html( $current ); ?>
                        </li>
                    </ul>
                </div>
                <?php if ( $share_button ): ?>
                    <div class="cell small-4 align-right grid-x">
                        <div class="cell shrink ">
                            <button data-open="share-contact-modal" class="center-items">
                                <img src="<?php echo get_template_directory_uri() . "/assets/images/share.svg" ?>">
                                <span style="margin:0 10px 0 10px"><?php _e( "Share" ); ?></span>
                            </button>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </nav>
    </div>

    <?php
}

