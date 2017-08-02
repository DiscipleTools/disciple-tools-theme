<?php
if ( post_password_required() ) {
    return;
}
?>

<div id="comments" class="comments-area">

    <?php // You can start editing here ?>

    <?php if ( have_comments() ) : ?>


        <?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : // Are there comments to navigate through? ?>
        <nav id="comment-nav-above" class="navigation comment-navigation" role="navigation">
            <h2 class="screen-reader-text"><?php esc_html_e( 'Comment navigation', 'disciple_tools' ); ?></h2>
            <div class="nav-links">

                <div class="nav-previous"><?php previous_comments_link( esc_html__( 'Older Comments', 'disciple_tools' ) ); ?></div>
                <div class="nav-next"><?php next_comments_link( esc_html__( 'Newer Comments', 'disciple_tools' ) ); ?></div>

            </div><!-- .nav-links -->
        </nav><!-- #comment-nav-above -->
        <?php endif; // Check for comment navigation. ?>

        <ol class="commentlist">
            <?php wp_list_comments( 'type=comment&callback=disciple_tools_comments&reverse_top_level=true' ); ?>
        </ol>

        <?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : // Are there comments to navigate through? ?>
        <nav id="comment-nav-below" class="navigation comment-navigation" role="navigation">
            <h4 class="screen-reader-text"><?php esc_html_e( 'Comment navigation', 'disciple_tools' ); ?></h4>
            <div class="nav-links">

                <div class="nav-previous"><?php previous_comments_link( esc_html__( 'Older Comments', 'disciple_tools' ) ); ?></div>
                <div class="nav-next"><?php next_comments_link( esc_html__( 'Newer Comments', 'disciple_tools' ) ); ?></div>

            </div><!-- .nav-links -->
        </nav><!-- #comment-nav-below -->
        <?php endif; // Check for comment navigation. ?>

    <?php endif; // Check for have_comments(). ?>

    <?php
        // If comments are closed and there are comments, let's leave a little note, shall we?
    if ( ! comments_open() && '0' != get_comments_number() && post_type_supports( get_post_type(), 'comments' ) ) :
    ?>
    <p class="no-comments"><?php esc_html_e( 'Comments are closed.', 'disciple_tools' ); ?></p>
    <?php endif; ?>

    <?php comment_form( array('class_submit'=>'button', 'logged_in_as' => '') ); ?>

</div><!-- #comments -->
