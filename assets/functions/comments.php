<?php
// Comment Layout
function disciple_tools_comments( $comment, $args, $depth ) {

    $GLOBALS['comment'] = $comment; ?>

    <li <?php comment_class( 'panel' ); ?>>
        <div class="media-object">
            <div class="float-right">

                <a href="javascript:void(0)" onclick="jQuery('#commentform-hidden-<?php echo get_comment_ID(); ?>').toggle();"><i class="fi-comment"></i></a>

            </div>

            <div class="media-object-section">
                    <?php echo get_avatar( $comment, 32 ); ?>
            </div>

            <div class="media-object-section">

                <div class="comment-text" ><?php comment_text()  ?></div>
                <div class="commentform-hidden" id="commentform-hidden-<?php echo get_comment_ID(); ?>" style="display:none;">
                    <?php comment_form(
                        array_merge(
                            $args, array(
                            'comment_field'        => '<p class="comment-form-comment"><label for="comment">' . _x( 'Comment', 'noun' ) . '</label> <textarea id="comment" name="comment" cols="45" rows="2" maxlength="65525" aria-required="true" required="required"></textarea></p>',
                            'logged_in_as'         => '',
                            'comment_notes_before' => '',
                            'comment_notes_after'  => '',
                            'action'               => site_url( '/wp-comments-post.php' ),
                            'id_form'              => 'commentform',
                            'id_submit'            => 'submit',
                            'class_form'           => 'comment-form',
                            'class_submit'         => 'submit',
                            'name_submit'          => 'submit',
                            'title_reply'          => '',
                            'title_reply_to'       => '',
                            'title_reply_before'   => '',
                            'title_reply_after'    => '',
                            'cancel_reply_before'  => '',
                            'cancel_reply_after'   => '',
                            'cancel_reply_link'    => ' ',
                            'label_submit'         => __( 'Add Comment' ),
                            'submit_button'        => '<input name="%1$s" type="submit" id="%2$s" class="button tiny" value="%4$s" />',
                            'submit_field'         => '<p class="form-submit">%1$s <input type="hidden" name="comment_post_ID" value="'.get_the_ID().'" id="comment_post_ID_'.get_the_ID().'"><input type="hidden" name="comment_parent" id="comment_parent" value="'.get_comment_ID().'"></p>',
                            'format'               => 'xhtml',
                            )
                        )
                    ); ?>
                </div>


                <?php if ($comment->comment_approved == '0') : ?>
                    <div class="alert alert-info">
                        <p><?php _e( 'Your comment is awaiting moderation.', 'disciple_tools' ) ?></p>
                    </div>
                <?php endif; ?>

            </div>

        </div>
    <!-- </li> is added by WordPress automatically -->
<?php
}
