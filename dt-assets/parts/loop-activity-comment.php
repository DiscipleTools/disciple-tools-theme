<?php
( function () {
    ?>

    <div class="grid-y bordered-box">
        <h3 class="section-header">
            <span>
                <?php esc_html_e( "Comments and Activity", 'disciple_tools' ) ?>
                <span id="comments-activity-spinner" class="loading-spinner"></span>
            </span>
            <button class="help-button" data-section="comments-activity-help-text">
                <img class="help-icon"
                     src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/help.svg' ) ?>"/>
            </button>
            <!-- <button class="section-chevron chevron_down">
                <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/chevron_down.svg' ) ?>"/>
            </button>
            <button class="section-chevron chevron_up">
                <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/chevron_up.svg' ) ?>"/>
            </button> -->
        </h3>
        <div class="cell grid-x " id="add-comment-section">
            <div class="auto cell">
                <textarea class="mention" dir="auto" id="comment-input"
                          placeholder="<?php echo esc_html_x( "Write your comment or note here", 'input field placeholder', 'disciple_tools' ) ?>"
                ></textarea>

                <?php if ( is_singular( "contacts" ) ) :
                     $sections = [
                         [
                             "key" => "comment",
                             "label" => __( "Comment", 'disciple_tools' ),
                             "selected_by_default" => true
                         ]
                     ];
                     $post_type = get_post_type();
                     $sections = apply_filters( 'dt_comments_additional_sections', $sections, $post_type );?>

                        <div class="grid-x">
                            <div class="section-subheader cell shrink">
                                <?php esc_html_e( "Type:", 'disciple_tools' ) ?>
                            </div>
                            <select id="comment_type_selector" class="cell auto">
                                <?php
                                $section_keys = [];
                                foreach ( $sections as $section ) {
                                    if ( !in_array( $section["key"], $section_keys ) ) {
                                        $section_keys[] = $section["key"] ?>
                                        <option value="<?php echo esc_html( $section["key"] ); ?>">
                                        <?php echo esc_html( $section["label"] );
                                    }
                                } ?>
                            </select>
                        </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="cell grid-x" style="margin-bottom: 20px">
            <div class="cell auto">
                <?php do_action( 'dt_comment_action_quick_action', get_post_type() ); ?>
            </div>

            <div class="shrink cell" id="add-comment-button-container">
                <button id="add-comment-button" class="button loader">
                    <?php esc_html_e( "Submit comment", 'disciple_tools' ) ?>
                </button>
            </div>
        </div>
        <div class="section-body"><!-- start collapse -->
        <div class="cell">

            <div>
                <span style="display: inline-block; margin-right:5px; vertical-align:top; font-weight: bold"><?php esc_html_e( "Showing:", 'disciple_tools' ) ?></span>
                <ul id="comment-activity-tabs" style="display: inline-block; margin: 0">

                    <?php
                    $sections = [
                        [
                            "key" => "comment",
                            "label" => __( "Comments", 'disciple_tools' ),
                            "selected_by_default" => true
                        ],
                        [
                            "key" => "activity",
                            "label" => __( "Activity", 'disciple_tools' ),
                            "selected_by_default" => true
                        ]
                    ];
                    $post_type = get_post_type();
                    $sections = apply_filters( 'dt_comments_additional_sections', $sections, $post_type );
                    $section_keys = [];
                    foreach ( $sections as $section ) :
                        if ( isset( $section["key"] ) && isset( $section["label"] ) && !in_array( $section["key"], $section_keys )) :
                            $section_keys[] = $section["key"];
                            ?>
                            <li class="tabs-title hide">
                                <label for="tab-button-<?php echo esc_html( $section["key"] ) ?>">
                                    <input type="checkbox"
                                           name="<?php echo esc_html( $section["key"] ) ?>"
                                           id="tab-button-<?php echo esc_html( $section["key"] ) ?>"
                                           data-id="<?php echo esc_html( $section["key"] ) ?>"
                                           class="tabs-section"
                                           checked
                                    >
                                    <span class="tab-button-label" dir="auto"
                                          data-id="<?php echo esc_html( $section["key"] ) ?>"> <?php echo esc_html( $section["label"] ) ?></span>
                                </label>

                            </li>
                        <?php endif;
                    endforeach; ?>
                    <li class="tabs-title">
                        <button id="show-all-tabs"
                                class="show-tabs"><?php esc_html_e( "show all", 'disciple_tools' ) ?></button>
                    </li>
                    <li class="tabs-title">
                        <button id="hide-all-tabs"
                                class="show-tabs"><?php esc_html_e( "hide all", 'disciple_tools' ) ?></button>
                    </li>
                </ul>
            </div>
        </div>

        <div id="comments-wrapper" class="cell tabs-content">

        </div>
    </div>

    <div class="reveal" id="delete-comment-modal" data-reveal>
        <p class="lead"><?php esc_html_e( 'Delete Comment:', 'disciple_tools' ) ?></p>
        <p id="comment-to-delete"></p>
        <div class="grid-x">
            <button class="button button-cancel clear" data-close aria-label="Close reveal" type="button">
                <?php esc_html_e( 'Close', 'disciple_tools' ) ?>
            </button>
            <button class="button alert loader" aria-label="confirm" type="button" id="confirm-comment-delete">
                <?php esc_html_e( 'Delete', 'disciple_tools' ) ?>
            </button>
            <button class="close-button" data-close aria-label="Close modal" type="button">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="delete-comment callout small alert" style="display: none">
            <h5><?php esc_html_e( "Sorry, something went wrong", 'disciple_tools' ) ?></h5>
            <p id="delete-comment-error"><?php esc_html_e( "The comment could not be deleted.", 'disciple_tools' ) ?></p>
        </div>
    </div>

    <div class="reveal" id="edit-comment-modal" data-reveal>
        <p class="lead"><?php esc_html_e( 'Edit Comment:', 'disciple_tools' ) ?></p>
        <textarea id="comment-to-edit" rows="5" dir="auto"></textarea>
        <div class="grid-x">
            <div class="cell small-12" id="edit_typeOfComment">
                <?php if ( is_singular( "contacts" ) ) :
                     $sections = [
                         [
                             "key" => "comment",
                             "label" => __( "Comments", 'disciple_tools' ),
                             "selected_by_default" => true
                         ]
                     ];
                     $post_type = get_post_type();
                     $sections = apply_filters( 'dt_comments_additional_sections', $sections, $post_type );?>
                        <div class="section-subheader">
                            <?php esc_html_e( "Type of Comment", 'disciple_tools' ) ?>
                        </div>
                        <select id="edit_comment_type_selector" class="">
                            <?php
                            $section_keys = [];
                            foreach ( $sections as $section ) {
                                if ( !in_array( $section["key"], $section_keys ) ) {
                                    $section_keys[] = $section["key"] ?>
                                    <option value="<?php echo esc_html( $section["key"] ); ?>">
                                    <?php echo esc_html( $section["label"] );
                                }
                            } ?>
                        </select>
                <?php endif; ?>
            </div>
            <button class="button button-cancel clear" data-close aria-label="Close reveal" type="button">
                <?php esc_html_e( 'Close', 'disciple_tools' ) ?>
            </button>
            <button class="button loader" aria-label="confirm" type="button" id="confirm-comment-edit">
                <?php esc_html_e( 'Update', 'disciple_tools' ) ?>
            </button>
            <button class="close-button" data-close aria-label="Close modal" type="button">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="edit-comment callout small alert" style="display: none">
            <h5><?php esc_html_e( "Sorry, something went wrong", 'disciple_tools' ) ?></h5>
            <p id="edit-comment-error"><?php esc_html_e( "The comment could not be updated.", 'disciple_tools' ) ?></p>
        </div>
    </div>

<!-- end collapse --></div>

    <?php
} )();
