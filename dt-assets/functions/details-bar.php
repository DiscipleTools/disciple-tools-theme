<?php
declare(strict_types=1);

function dt_print_details_bar(
    bool $desktop = true
) {
    $dt_post_type = get_post_type();
    $post_id = get_the_ID();
    $post_settings = DT_Posts::get_post_settings( $dt_post_type );
    $dt_post = DT_Posts::get_post( $dt_post_type, $post_id );
    $shared_with = DT_Posts::get_shared_with( $dt_post['post_type'], $post_id );
    $shared_with_text = '';
    $current_user_id = get_current_user_id();
    $following = DT_Posts::get_users_following_post( $dt_post_type, $post_id );
    $share_button = true;
    $comment_button = true;
    $task = true;
    $show_update_needed = isset( $post_settings['fields']['requires_update'] ) && current_user_can( 'assign_any_contacts' );
    $update_needed = isset( $dt_post['requires_update'] ) && $dt_post['requires_update'] === true;
    $following = in_array( $current_user_id, $following );
    $is_assigned = isset( $dt_post['assigned_to']['id'] ) && $dt_post['assigned_to']['id'] == $current_user_id;
    $disable_following_toggle_function = $is_assigned;
    $can_update = $is_assigned || DT_Posts::can_update( $dt_post_type, $dt_post['ID'] );

    foreach ( $shared_with as $shared ) {
        $shared_with_text .= sprintf( ', %s', $shared['display_name'] );
    }


    $record_picture = isset( $dt_post['record_picture']['thumb'] ) ? $dt_post['record_picture']['thumb'] : null;
    $picture = apply_filters( 'dt_record_picture', $record_picture, $dt_post_type, $post_id );
    $icon = apply_filters( 'dt_record_icon', null, $dt_post_type, $dt_post );

    $type_color = isset( $dt_post['type']['key'], $post_settings['fields']['type']['default'][$dt_post['type']['key']]['color'] ) ? $post_settings['fields']['type']['default'][$dt_post['type']['key']]['color'] : '#000000';
    $type_icon = isset( $dt_post['type']['key'], $post_settings['fields']['type']['default'][$dt_post['type']['key']]['icon'] ) ? $post_settings['fields']['type']['default'][$dt_post['type']['key']]['icon'] : false;


    if ( $desktop ): ?>

        <!-- DESKTOP -->
        <div class="show-for-medium details-second-bar" style="z-index: 9">
            <nav role="navigation" style="width:100%" class="second-bar" id="second-bar-large">
                <div class="container-width">

                    <div class="grid-x grid-margin-x">
                        <div class="cell small-4 grid-x">
                            <div class="cell grid-x shrink center-items">
                                <?php if ( $show_update_needed ) { ?>
                                    <div style="margin-inline-start:10px;margin-inline-end:5px">
                                        <span><?php esc_html_e( 'Update Needed', 'disciple_tools' ) ?>:</span>
                                        <input type="checkbox" id="update-needed-large" class="dt-switch update-needed" <?php echo ( $update_needed ? 'checked' : '' ) ?> />
                                        <label class="dt-switch" for="update-needed-large" style="vertical-align: top;"></label>
                                    </div>
                                <?php } ?>
                            </div>
                            <!-- Admin Actions dt-dropdown -->

                            <?php
                            $options_array = array();

                        // Check if delete operation is allowed
                            if ( DT_Posts::can_delete( $dt_post_type, $post_id ) ) {
                                $options_array[] = array(
                                'label' => 'Delete&nbsp;Contact',
                                'icon' => esc_html( get_template_directory_uri() . '/dt-assets/images/trash.svg' ),
                                'isModal' => true,
                                );
                            }

                            if ( DT_Posts::can_update( $dt_post_type, $post_id ) ){
                                $options_array[] =array(
                                'label' => 'View&nbsp;Contact&nbsp;History',
                                'icon' => esc_html( get_template_directory_uri(). '/dt-assets/images/history.svg' ),
                                'isModal' => true,
                                );
                            }

                        // Check if update operation is allowed
                            if ( DT_Posts::can_update( $dt_post_type, $post_id ) ) {
                                $options_array[] = array(
                                'label' => 'Merge&nbsp;with&nbsp;another&nbsp;record',
                                'icon' => esc_html( get_template_directory_uri() . '/dt-assets/images/merge.svg?v=2' ),
                                'isModal' => true,
                                );
                            }


                            $record_actions_array = DT_Contacts_Base::dt_record_admin_actions( $dt_post_type, $post_id );

                        // Loop through the $record_actions_array and use the properties as required
                            foreach ( $record_actions_array as $record_action ) {
                                $options_array[] = array(
                                'label' => $record_action['label'],
                                'icon' => $record_action['icon'],
                                'isModal' => $record_action['isModal'],
                                );

                                // Use $label, $icon, $isModal as needed for each record action
                            }

                            $contact_actions_array = DT_Contacts_User::get_record_actions_array( $dt_post_type, $post_id );


                            foreach ( $contact_actions_array as $contact_action ){
                                $options_array[] = array(
                                'label' => $contact_action['label'],
                                'icon' => $contact_action['icon'],
                                'isModal' => $contact_action['isModal'],
                                'href' => $contact_action['href'],
                                );
                            }
                            ?>

                            <div class="cell grid-x shrink center-items">
                               <!-- replaced the ul with dt-dropdown to handle admin actions -->
                                <dt-dropdown label="Admin Actions"
                                    options=<?php echo json_encode( $options_array ) ?>
                                    >
                                </dt-dropdown>
                    <!-- delete contact list item modal -->
                                <dt-modal submitButton closeButton id="delete-contact" hideButton  title=<?php echo esc_html( str_replace( ' ', '_', sprintf( _x( 'Delete %s', 'Delete Contact', 'disciple_tools' ), DT_Posts::get_post_settings( $dt_post_type )['label_singular'] ) ) );?>>
                                    <span slot="content">

                            <p><?php echo esc_html( sprintf( _x( 'Are you sure you want to delete %s?', 'Are you sure you want to delete name?', 'disciple_tools' ), $dt_post['name'] ) ) ?>
                            </p>
                                <br>
                                    </span>
                                    <span slot="submit-button">
                                        <button class="button alert loader" type="button" id="delete-record">
                                            <?php esc_html_e( 'Delete', 'disciple_tools' ); ?>
                                                </button>
                                    </span>
                                    <span slot="close-button">Cancel</span>
                                </dt-modal>

                                    <?php
                                    global $post;
                                    ?>
                            <!-- view contact history list item modal -->
                                <dt-modal id="view-contact-history" headerClass={"dt-modal--full-width":true}  class="record_history_modal" hideButton title=<?php echo esc_html( str_replace( ' ', '_', sprintf( _x( '%s Record History', 'Record History', 'disciple_tools' ), $post->post_title ) ) ) ?>>
                                    <span slot="content">

                                    <div style="padding-bottom: 50px;">
                                        <hr>

                                        <div class="grid-container">
                                            <div class="grid-x">
                                                <div class="cell small-4">
                                                    <table style="border: none;  max-width: 300px;">
                                                        <tbody style="border: none;">
                                                        <tr>
                                                            <td colspan="2">
                                                                <h4><?php esc_html_e( 'Filter To Date', 'disciple_tools' ) ?></h4>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td colspan="2">
                                                                <select id="record_history_calendar">
                                                                    <option value="" selected disabled>--- <?php esc_html_e( 'select date to filter by', 'disciple_tools' ) ?> ---</option>
                                                                </select>
                                                            </td>
                                                        </tr>
                                                        <tr style="border: none;">
                                                            <td style="vertical-align: top;">
                                                                <span><?php esc_html_e( 'show all', 'disciple_tools' ) ?></span>
                                                            </td>
                                                            <td>
                                                                <div class="switch tiny">
                                                                    <input class="switch-input" id="record_history_all_activities_switch" type="checkbox"
                                                                        name="record_history_all_activities_switch" checked>
                                                                    <label class="switch-paddle" for="record_history_all_activities_switch">
                                                            <span
                                                                class="show-for-sr"><?php esc_html_e( 'show all', 'disciple_tools' ) ?></span>
                                                                    </label>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        </tbody>
                                                    </table>

                                                </div>
                                                <div class="cell small-8">
                                                    <span id="record_history_progress_spinner" class="loading-spinner" style="display: table; margin: 0 auto 10px;"></span>
                                                    <div id="record_history_activities" style="display: none;"></div>
                                                </div>
                                            </div>
                                        </div>
                                        <br>

                                    </div>

                                    </span>
                                </dt-modal>

                                <!-- Merge with another record list item modal -->
                                <dt-modal class="open-merge-with-post" id="merge-with-another-record"  data-post_type="<?php echo esc_html( $dt_post_type ) ?>"  hideButton title=<?php echo esc_html( str_replace( ' ', '_', sprintf( _x( 'Merge %s', 'Merge Contacts', 'disciple_tools' ), $post_settings['label_plural'] ?? $dt_post_type ) ) )?>>
                                    <span slot="content">
                                <div id="merge-with-post-modal" style="min-height:500px">
                                    <p><?php echo esc_html( sprintf( _x( 'Merge this %1$s with another %2$s', 'Merge this contact with another contact', 'disciple_tools' ), $post_settings['label_singular'] ?? $dt_post_type, $post_settings['label_singular'] ?? $dt_post_type ) )?></p>
                                    <div class="merge_with details">
                                        <var id="merge_with-result-container" class="result-container merge_with-result-container"></var>
                                        <div id="merge_with_t" name="form-merge_with">
                                            <div class="typeahead__container">
                                                <div class="typeahead__field">
                                                    <span class="typeahead__query">
                                                        <input class="js-typeahead-merge_with input-height"
                                                            name="merge_with[query]" placeholder="<?php echo esc_html( sprintf( _x( 'Search %s', "Search 'something'", 'disciple_tools' ), $post_settings['label_plural'] ?? $dt_post_type ) ) ?>"
                                                            autocomplete="off">
                                                    </span>
                                                    <span class="typeahead__button">
                                                        <button type="button" class="search_merge_with typeahead__image_button input-height" data-id="user-select_t">
                                                            <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/chevron_down.svg' ) ?>"/>
                                                        </button>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <br>
                                    <div class="confirm-merge-with-post" style="display: none">
                                        <p><span id="name-of-post-to-merge"></span> <?php echo esc_html_x( 'selected.', 'added to the end of a sentence', 'disciple_tools' ); ?></p>
                                        <p><?php esc_html_e( 'Click merge to continue.', 'disciple_tools' ); ?></p>
                                    </div>
                                    <div class="grid-x">

                                        <form
                                            action="<?php echo esc_url( site_url() ); ?>/<?php echo esc_html( dt_get_post_type() ); ?>/mergedetails"
                                            method="get">
                                            <input type="hidden" name="currentid" value="<?php echo esc_html( get_the_ID() ); ?>"/>
                                            <input id="confirm-merge-with-post-id" type="hidden" name="dupeid" value=""/>
                                            <button type="submit" class="button confirm-merge-with-post" style="display: none;">
                                                <?php esc_html_e( 'Merge', 'disciple_tools' ); ?>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                </span>
                                </dt-modal>

                                <!-- change record type list item modal -->
                                        <?php
                                        if ( $dt_post_type === 'contacts' ):
                                            $contact_fields = DT_Posts::get_post_field_settings( $dt_post_type );
                                            $post_change_record_type = DT_Posts::get_post( $dt_post_type, $post_id );

                                            ?>
                                <dt-modal id="change-contact-type" submitButton closeButton hideButton title="<?php echo esc_html( $contact_fields['type']['name'] ?? '' )?>">
                                    <span slot="content">
                                    <p><?php echo nl2br( wp_kses_post( make_clickable( $contact_fields['type']['description'] ?? '' ) ) ); ?></p>
                                        <p><?php esc_html_e( 'Choose an option:', 'disciple_tools' )?></p>

                                        <select id="type-options">
                                            <?php
                                            foreach ( $contact_fields['type']['default'] as $option_key => $option ) {
                                                if ( !empty( $option['label'] ) && ( !isset( $option['hidden'] ) || $option['hidden'] !== true ) ) {
                                                    $selected = ( $option_key === ( $post_change_record_type['type']['key'] ?? '' ) ) ? 'selected' : '';
                                                    ?>
                                                    <option value="<?php echo esc_attr( $option_key ) ?>" <?php echo esc_html( $selected ) ?>>
                                                        <?php echo esc_html( $option['label'] ?? '' ) ?>
                                                        <?php if ( !empty( $option['description'] ) ){
                                                            echo esc_html( ' - ' . $option['description'] ?? '' );
                                                        } ?>
                                                    </option>
                                                    <?php
                                                }
                                            }
                                            ?>
                                        </select>
                                </span>
                                <span slot="submit-button">
                                    <button class="button loader" type="button" id="confirm-type-close" data-field="closed">
                                            <?php echo esc_html__( 'Confirm', 'disciple_tools' )?>
                                    </button>

                                    </span>
                                    <span slot="close-button">Cancel</span>
                            </dt-modal>
                            <?php endif; ?>

                            <dt-modal id="link-to-an-existing-user" closeButton hideButton title="<?php esc_html_e( 'Link this contact to an existing user', 'disciple_tools' )?>">
                                            <span slot="content">

                                            <?php if ( isset( $contact['corresponds_to_user'] ) ) : ?>
                                                <p><?php esc_html_e( 'This contact already represents a user.', 'disciple_tools' ) ?></p>
                                            <?php else : ?>


                                                <p><?php echo esc_html_x( 'To link to an existing user, first, find the user using the field below.', 'Step 1 of link user', 'disciple_tools' ) ?></p>

                                                <div class="user-select details">
                                                    <var id="user-select-result-container" class="result-container user-select-result-container"></var>
                                                    <div id="user-select_t" name="form-user-select">
                                                        <div class="typeahead__container">
                                                            <div class="typeahead__field">
                                                            <span class="typeahead__query">
                                                                <input class="js-typeahead-user-select input-height"
                                                                        name="user-select[query]" placeholder="<?php echo esc_html_x( 'Search Users', 'input field placeholder', 'disciple_tools' ) ?>"
                                                                        autocomplete="off">
                                                            </span>
                                                                    <span class="typeahead__button">
                                                                <button type="button" class="search_user-select typeahead__image_button input-height" data-id="user-select_t">
                                                                    <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/chevron_down.svg' ) ?>"/>
                                                                </button>
                                                            </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <br>
                                                <div class="confirm-merge-with-user" style="display: none">
                                                    <p><?php echo esc_html_x( 'To finish the linking, merge this contact with the existing user details.', 'Step 2 of link user', 'disciple_tools' ) ?></p>
                                                </div>

                                            <?php endif; ?>

                                            <div class="grid-x">

                                                <form action='<?php echo esc_url( site_url() );?>/contacts/mergedetails' method='get'>
                                                    <input type='hidden' name='currentid' value='<?php echo esc_html( $post_id );?>'/>
                                                    <input id="confirm-merge-with-user-dupe-id" type='hidden' name='dupeid' value=''/>
                                                    <button type='submit' class="button confirm-merge-with-user" style="display: none">
                                                        <?php echo esc_html__( 'Merge', 'disciple_tools' )?>
                                                    </button>
                                                   <br><br>
                                                </form>
                                            </div>
                                            </span>
                                            <span slot="close-button">Cancel</span>
                            </dt-modal>
                            </div>
                            <div class="cell grid-x shrink center-items">
                                <span id="admin-bar-issues"></span>
                            </div>
                        </div>
                        <div class="cell small-3 large-4 center hide-for-small-only grid-x">
                            <div class="cell medium-2 large-1 center-items align-left">
                                <a class="section-chevron navigation-previous" style="max-width: 1rem; display: none;"
                                    href="javascript:void(0)">
                                    <img style="max-width: 1rem; height: 20px"
                                        title="<?php esc_attr_e( 'Previous record', 'disciple_tools' ); ?>" src="<?php
                                        if ( is_rtl() ) {
                                            echo esc_url( get_template_directory_uri() . '/dt-assets/images/chevron_right.svg' );
                                        } else {
                                            echo esc_url( get_template_directory_uri() . '/dt-assets/images/chevron_left.svg' );
                                        } ?>">
                                </a>
                            </div>
                            <div class="cell small-8">
                                <table style="margin: 0;">
                                    <tbody style="border: 0;">
                                        <tr style="border: 0;">
                                            <td style="padding: 0">
                                                <?php
                                                if ( !empty( $picture ) ): ?>
                                                    <img class="dt-storage-upload details-bar-picture"
                                                        src="<?php echo esc_html( $picture ) ?>"
                                                        data-storage_upload_post_type="<?php echo esc_attr( $dt_post_type ) ?>"
                                                        data-storage_upload_post_id="<?php echo esc_attr( $post_id ) ?>"
                                                        data-storage_upload_meta_key="record_picture"
                                                        data-storage_upload_key_prefix="<?php echo esc_attr( $dt_post_type ) ?>"
                                                        data-storage_upload_delete_enabled="1">
                                                <?php else : ?>
                                                    <i class="dt-storage-upload details-bar-picture <?php echo esc_html( $icon ) ?> medium"
                                                        data-storage_upload_post_type="<?php echo esc_attr( $dt_post_type ) ?>"
                                                        data-storage_upload_post_id="<?php echo esc_attr( $post_id ) ?>"
                                                        data-storage_upload_meta_key="record_picture"
                                                        data-storage_upload_key_prefix="<?php echo esc_attr( $dt_post_type ) ?>"></i>
                                                <?php endif; ?>
                                            </td>
                                            <td style="padding: 0;">
                                                <span id="title" <?php echo esc_attr( $can_update ? 'contenteditable=true' : '' ); ?>
                                                    class="title dt_contenteditable"><?php the_title_attribute(); ?></span>
                                                <br>
                                                <?php do_action( 'dt_post_record_name_tagline' ); ?>
                                                <span class="record-name-tagline">
                                                    <?php if ( isset( $dt_post['type']['label'] ) ): ?>
                                                        <a data-open="contact-type-modal">
                                                            <?php if ( $type_icon ): ?>
                                                                <img class="dt-record-type-icon"
                                                                    src="<?php echo esc_html( $type_icon ) ?>" />
                                                            <?php endif; ?>
                                                            <?php echo esc_html( $dt_post['type']['label'] ?? '' ) ?></a>
                                                    <?php endif; ?>
                                                    <span class="details-bar-created-on"></span>
                                                    <?php if ( $dt_post['post_author_display_name'] ):
                                                        echo esc_html( ' ' . sprintf( _x( 'by %s', '(record created) by multiplier1', 'disciple_tools' ), $dt_post['post_author_display_name'] ) );
                                                    endif; ?>
                                                </span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="cell medium-2 large-1 center-items align-right">
                                <a href="javascript:void(0)" style="display: none;" class="navigation-next section-chevron">
                                    <img style="max-width: 1rem; height: 20px"
                                        title="<?php esc_attr_e( 'Next record', 'disciple_tools' ); ?>" src="<?php
                                        if ( is_rtl() ) {
                                            echo esc_url( get_template_directory_uri() . '/dt-assets/images/chevron_left.svg' );
                                        } else {
                                            echo esc_url( get_template_directory_uri() . '/dt-assets/images/chevron_right.svg' );
                                        } ?>">
                                </a>
                            </div>
                        </div>
                        <div class="cell small-5 large-4 align-right grid-x control-buttons">
                            <div class="cell shrink center-items">
                               <dt-button id="favorite-button" label="favorite" title="favorite" type="button" posttype="contacts"
                                context="star" favorited="false">
                                <svg class='icon-star' viewBox="0 0 32 32">
                                    <use
                                        xlink:href="<?php echo esc_url( get_template_directory_uri() . '/dt-assets/images/star.svg#star' ) ?>">
                                    </use>
                                </svg>
                            </dt-button>
                            </div>
                            <?php if ( $task ): ?>
                                <div class="cell shrink center-items">

                                    <div class="reveal" id="tasks-modal" data-reveal xmlns="http://www.w3.org/1999/html"></div>
                                    <dt-modal class="open-set-task" buttonLabel="<?php esc_html_e( 'Tasks', 'disciple_tools' ); ?>"
                                        title="Tasks" buttonClass=<?php esc_html_e( 'Tasks', 'disciple_tools' ); ?> buttonStyle="">
                                        <span slot="content">
                                            <form class="js-add-task-form">
                                                <p style="color: red" class="error-text"></p>
                                                <p><?php echo esc_html__( 'Set a reminder or a task with a note and receive a notification on the due date.', 'disciple_tools' ); ?>
                                                </p>
                                                <strong><?php echo esc_html__( 'Task Type', 'disciple_tools' ); ?></strong>
                                                <ul class="ul-no-bullets no-bullets--custom">
                                                    <li>
                                                        <label>
                                                            <input type="radio" name="task-type" value="reminder"
                                                                checked><?php echo esc_html__( 'Reminder', 'disciple_tools' ); ?>
                                                        </label>
                                                    </li>
                                                    <li>
                                                        <label style="display: flex; align-items: baseline">
                                                            <input type="radio" name="task-type"
                                                                value="custom"><?php echo esc_html__( 'Custom', 'disciple_tools' ); ?>:&nbsp;
                                                            <input type="text" id="task-custom-text" style="">
                                                        </label>
                                                    </li>
                                                </ul>

                                                <label><strong><?php echo esc_html__( 'Due Date', 'disciple_tools' ); ?></strong></label>
                                                <input id="create-task-date" name="task-date" type="text" class="" required
                                                    autocomplete="off">

                                                <button class="button loader button---margin-top " type="submit" id="create-task">
                                                    <?php echo esc_html__( 'Create Task', 'disciple_tools' ); ?>
                                                </button>
                                            </form>
                                            <hr>
                                            <div>
                                                <h5><?php echo esc_html__( 'Existing tasks for this record:', 'disciple_tools' ); ?><span
                                                        id="tasks-spinner" class="loading-spinner"></span></h5>
                                                <ul class="existing-tasks"></ul>
                                            </div>
                                        </span>
                                    </dt-modal>
                                </div>
                            <?php endif; ?>
                            <div class="cell shrink center-items">
                                <?php if ( $disable_following_toggle_function ): ?>
                                    <button class="button follow hollow" data-value="following"
                                        disabled><?php echo esc_html( __( 'Following', 'disciple_tools' ) ) ?>
                                        <i class="fi-eye"></i>
                                    </button>
                                <?php else :
                                    if ( $following ): ?>
                                        <dt-button custom="true" id="following-button" label="Following" posttype="contacts" type="button"
                                            outline title="" context="primary" confirm="">Following<svg
                                            xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24">
                                            <path fill="currentColor"
                                                d="M12 9a3 3 0 0 0-3 3a3 3 0 0 0 3 3a3 3 0 0 0 3-3a3 3 0 0 0-3-3m0 8a5 5 0 0 1-5-5a5 5 0 0 1 5-5a5 5 0 0 1 5 5a5 5 0 0 1-5 5m0-12.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5" />
                                        </svg></dt-button>
                                    <?php else : ?>
                                        <dt-button custom="true" id="follow-button" label="Follow" posttype="contacts" type="button" title=""
                                        context="primary" confirm="">Follow<svg xmlns="http://www.w3.org/2000/svg" width="1em"
                                            height="1em" viewBox="0 0 24 24">
                                            <path fill="currentColor"
                                                d="M12 9a3 3 0 0 0-3 3a3 3 0 0 0 3 3a3 3 0 0 0 3-3a3 3 0 0 0-3-3m0 8a5 5 0 0 1-5-5a5 5 0 0 1 5-5a5 5 0 0 1 5 5a5 5 0 0 1-5 5m0-12.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5" />
                                        </svg></dt-button>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                            <?php if ( $share_button ): ?>
                                <div class="cell shrink center-items ">
                                    <div class="reveal" id="share-contact-modal" data-reveal style="min-height:550px"></div>
                                    <img class="dt-blue-icon"
                                        src="<?php echo esc_url( get_template_directory_uri() . '/dt-assets/images/share.svg' ); ?>">
                                    <dt-modal title="Share Settings" class="open-share"
                                        buttonStyle={"background":"white","border":"0px","color":"black"}
                                        buttonLabel="<?php esc_html_e( 'Share', 'disciple_tools' ); ?> (<?php echo esc_html( count( $shared_with ) ); ?>)">
                                        <span slot="content" class="content-min-height">
                                            <?php
                                            global $post;
                                            ?>
                                            <h6>
                                                <?php
                                                if ( is_singular( 'groups' ) ) {
                                                    esc_html_e( 'This group is shared with:', 'disciple_tools' );
                                                } else if ( is_singular( 'contacts' ) ) {
                                                    esc_html_e( 'This contact is shared with:', 'disciple_tools' );
                                                }
                                                ?>
                                            </h6>

                                            <div class="share details">
                                    <var id="share-result-container" class="result-container share-result-container"></var>

                                    <?php

                                    // Map the values of the array to extract specific fields
                                    $value = array_map( function ( $item ) {
                                        return array(
                                            'id' => (int) $item['user_id'],
                                            'post_id' => (int) $item['post_id'],
                                            'avatar' => $item['meta'],
                                            'label' => $item['display_name'],
                                        );
                                    }, $shared_with);

                                    ?>

                                    <dt-users-connection
                                    name="field-name"
                                    placeholder="Search Users"
                                    options=""
                                    value="<?php echo esc_attr( json_encode( $value ) ) ?>"
                                    onchange=""
                                    icon="https://cdn-icons-png.flaticon.com/512/1077/1077114.png"
                                    iconalttext="Icon Alt Text"
                                    privatelabel=""
                                    error="">

                                    </dt-users-connection>
                                </div>
                                            <?php
                                            /**
                                             * This fires below the share section, and can add additional share based elements.
                                             */
                                            do_action( 'dt_share_panel', $post );
                                            ?>
                                        </span>
                                    </dt-modal>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </nav>
        </div>

    <?php else : ?>


        <!-- MOBILE -->
        <div class="details-second-bar">
            <nav role="navigation" style="width:100%; border-color: <?php echo esc_html( $type_color ); ?>" class="second-bar"
                id="second-bar-small">
                <?php if ( $comment_button ): ?>
                    <div class="container-width">
                        <div class="grid-x align-center mobile-nav-actions" style="align-items: center">
                            <div class="cell shrink">
                                <button id="nav-view-comments" class="center-items">
                                    <a href="#comment-activity-section" class="center-items" style="color:black">
                                        <img
                                            src="<?php echo esc_url( get_template_directory_uri() . '/dt-assets/images/view-comments.svg' ); ?>">
                                    </a>
                                </button>
                            </div>
                            <button class="button favorite" data-favorite="false">
                                <svg class='icon-star' viewBox="0 0 32 32">
                                    <use
                                        xlink:href="<?php echo esc_url( get_template_directory_uri() . '/dt-assets/images/star.svg#star' ) ?>">
                                    </use>
                                </svg>
                            </button>
                        <?php endif; ?>
                        <?php if ( $share_button ): ?>
                            <div class="cell shrink">
                                <button class="center-items open-share">
                                    <img class="dt-blue-icon"
                                        src="<?php echo esc_url( get_template_directory_uri() . '/dt-assets/images/share.svg' ) ?>">
                                </button>
                            </div>
                        <?php endif; ?>
                        <div class="cell shrink">
                            <?php if ( $disable_following_toggle_function ): ?>
                                <button class="button follow mobile hollow" data-value="following" disabled>
                                    <i class="fi-eye"></i>
                                </button>
                            <?php else :
                                if ( $following ): ?>
                                    <button class="button follow mobile hollow" data-value="following">
                                        <i class="fi-eye"></i>
                                    </button>
                                <?php else : ?>
                                    <button class="button follow mobile" data-value="">
                                        <i class="fi-eye"></i>
                                    </button>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                        <?php if ( $task ): ?>
                            <div class="cell shrink center-items">
                                <button class="button open-set-task">
                                    <i class="fi-clock"></i>
                                </button>
                            </div>
                        <?php endif; ?>
                        <div class="cell shrink center-item">
                            <?php if ( $show_update_needed ) { ?>
                                <span style="margin-right:5px"><?php esc_html_e( 'Update Needed', 'disciple_tools' ) ?>:</span>
                                <input type="checkbox" id="update-needed-small" class="dt-switch update-needed" <?php echo ( $update_needed ? 'checked' : '' ) ?> />
                                <label class="dt-switch" for="update-needed-small" style="vertical-align: top;"></label>
                            <?php } ?>
                        </div>
                    </div>

                    <div class="grid-x">
                        <div class="cell small-1 center-items">
                            <a class="section-chevron navigation-previous" style="display: none;" href="javascript:void(0)">
                                <img style="height: 20px" title="<?php esc_attr_e( 'Previous record', 'disciple_tools' ); ?>"
                                    src="<?php
                                    if ( is_rtl() ) {
                                        echo esc_url( get_template_directory_uri() . '/dt-assets/images/chevron_right.svg' );
                                    } else {
                                        echo esc_url( get_template_directory_uri() . '/dt-assets/images/chevron_left.svg' );
                                    } ?>">
                            </a>
                        </div>
                        <div class="cell small-10 center" style="display: flex; justify-content: center">
                            <div style="display: flex">
                                <?php $picture = apply_filters( 'dt_record_picture', $record_picture, $dt_post_type, $post_id );
                                if ( !empty( $picture ) ): ?>
                                    <img class="dt-storage-upload details-bar-picture" src="<?php echo esc_html( $picture ) ?>"
                                        data-storage_upload_post_type="<?php echo esc_attr( $dt_post_type ) ?>"
                                        data-storage_upload_post_id="<?php echo esc_attr( $post_id ) ?>"
                                        data-storage_upload_meta_key="record_picture"
                                        data-storage_upload_key_prefix="<?php echo esc_attr( $dt_post_type ) ?>"
                                        data-storage_upload_delete_enabled="1">
                                <?php else : ?>
                                    <i class="dt-storage-upload details-bar-picture <?php echo esc_html( $icon ) ?> medium"
                                        data-storage_upload_post_type="<?php echo esc_attr( $dt_post_type ) ?>"
                                        data-storage_upload_post_id="<?php echo esc_attr( $post_id ) ?>"
                                        data-storage_upload_meta_key="record_picture"
                                        data-storage_upload_key_prefix="<?php echo esc_attr( $dt_post_type ) ?>"></i>
                                <?php endif; ?>
                            </div>
                            <div style="display: flex; justify-content: center; flex-wrap: wrap; flex-direction: column">
                                <span id='title' <?php echo esc_attr( $can_update ? 'contenteditable=true' : '' ); ?>
                                    class="title dt_contenteditable"><?php the_title_attribute(); ?></span>
                                <div id="record-tagline">
                                    <?php do_action( 'dt_post_record_name_tagline' ); ?>
                                    <span class="record-name-tagline">
                                        <?php if ( isset( $dt_post['type']['label'] ) ): ?>
                                            <a
                                                data-open="contact-type-modal"><?php echo esc_html( $dt_post['type']['label'] ?? '' ) ?></a>
                                        <?php endif; ?>
                                        <span class="details-bar-created-on"></span>
                                        <?php if ( $dt_post['post_author_display_name'] ):
                                            echo esc_html( ' ' . sprintf( _x( 'by %s', '(record created) by multiplier1', 'disciple_tools' ), $dt_post['post_author_display_name'] ) );
                                        endif; ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="cell small-1 center-items">
                            <a href="javascript:void(0)" style="display: none;" class="navigation-next section-chevron">
                                <img style="height: 20px" title="<?php esc_attr_e( 'Next record', 'disciple_tools' ); ?>" src="<?php
                                if ( is_rtl() ) {
                                    echo esc_url( get_template_directory_uri() . '/dt-assets/images/chevron_left.svg' );
                                } else {
                                    echo esc_url( get_template_directory_uri() . '/dt-assets/images/chevron_right.svg' );
                                } ?>">
                            </a>
                        </div>
                    </div>
                </div>
            </nav>
        </div>

    <?php endif;
}
