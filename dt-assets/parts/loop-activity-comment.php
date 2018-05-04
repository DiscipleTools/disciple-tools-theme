<?php
( function() {
    $contact = Disciple_Tools_Contacts::get_contact( get_the_ID(), true );
    $contact_fields = Disciple_Tools_Contacts::get_contact_fields();
    ?>

    <div class="grid-y">
        <div class="cell grid-x grid-margin-x" id="add-comment-section">
            <div class="auto cell">
            <textarea class="mention" dir="auto" rows="4" id="comment-input"
                      placeholder="<?php esc_html_e( "Write your comment or note here", 'disciple_tools' ) ?>"
                      style="min-height:100px"
            ></textarea>
            </div>
        </div>
        <div class="cell grid-x" style="margin-bottom: 20px">
            <div class="cell auto">
                <?php if ( is_singular( "contacts" ) ) : ?>
                <ul class="dropdown menu" data-dropdown-menu $dropdownmenu-arrow-color="white">
                    <li style="border-radius: 5px">
                        <a class="button menu-white-dropdown-arrow"
                           style="background-color: #00897B; color: white;">
                            <?php esc_html_e( "Quick actions", 'disciple_tools' ) ?></a>
                        <ul class="menu">
                            <?php
                            foreach ( $contact_fields as $field => $val ) {
                                if ( strpos( $field, "quick_button" ) === 0 ) {
                                    $current_value = 0;
                                    if ( isset( $contact[ $field ] ) ) {
                                        $current_value = $contact[ $field ];
                                    } ?>
                                    <li class="quick-action-menu" data-id="<?php echo esc_attr( $field ) ?>">
                                        <a>
                                            <img src="<?php echo esc_url( get_template_directory_uri() . "/dt-assets/images/" . $val['icon'] ); ?>">
                                            <?php echo esc_html( $val["name"] ); ?>
                                            (<span class="<?php echo esc_attr( $field ) ?>"><?php echo esc_html( $current_value ); ?></span>)
                                        </a>
                                    </li>
                                    <?php
                                }
                            }
                            ?>
                        </ul>
                    </li>
                </ul>
                <?php endif; ?>
            </div>
            <div class="shrink cell" id="add-comment-button-container">
                <button id="add-comment-button" class="button loader">
                    <?php esc_html_e( "Submit comment", 'disciple_tools' ) ?>
                </button>
            </div>
        </div>
        <div class="cell">
            <ul class="tabs" data-tabs id="comment-activity-tabs">
                <li class="tabs-title is-active" data-tab="all"><a href="#all" aria-selected="true"><?php esc_html_e( "All", 'disciple_tools' ) ?></a></li>
                <li class="tabs-title" data-tab="comments"><a href="#comments"><?php esc_html_e( "Comments", 'disciple_tools' ) ?></a></li>
                <li class="tabs-title" data-tab="activity"><a href="#activity"><?php esc_html_e( "Activity", 'disciple_tools' ) ?></a></li>
            </ul>
        </div>

        <div id="comments-wrapper" class="cell tabs-content">

        </div>
    </div>
    <?php
} )();
