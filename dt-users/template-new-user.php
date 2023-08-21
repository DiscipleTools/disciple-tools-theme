<?php
/*
 * Name: User Management
*/

// Ensure Add New Users' admin setting has been enabled on multi-sites
if ( current_user_can( 'manage_dt' ) && !current_user_can( 'create_users' ) && is_multisite() && empty( get_site_option( 'add_new_users' ) ) ){
    get_header();
    ?>
    <div class="large-6 medium-6 small-6"
         style="max-width: 50% !important; margin-left: auto !important; margin-right: auto !important; margin-top: 50px; margin-bottom: 50px;">

        <div class="bordered-box">
            <h3><?php esc_html_e( 'Permission denied', 'disciple_tools' ); ?></h3>
            <p>
                <?php esc_html_e( 'Administrator must enable "Add New Users" option in multisite settings:', 'disciple_tools' ); ?>
                <a href="wp-admin/network/settings.php"
                   target="_blank">Network Admin Settings</a>
            </p>
        </div>
    </div>
    <?php
    get_footer();
    exit();
}

$current_user_can_manage_users = current_user_can( 'create_users' ) || current_user_can( 'manage_dt' );
if ( !$current_user_can_manage_users && !DT_User_Management::non_admins_can_make_users() ) {
    wp_safe_redirect( '/registered' );
    exit();
}
$dt_url_path = dt_get_url_path();

/* Build variables for page */
$dt_user_fields = dt_get_site_custom_lists( 'user_fields' );
$gender_fields = DT_Posts::get_post_settings( 'contacts' )['fields']['gender'];
?>

<?php get_header(); ?>

<div id="user-add-user" style="padding:15px">

    <div id="inner-content" class="grid-x grid-margin-x grid-margin-y">

        <?php if ( $current_user_can_manage_users ): ?>

        <div class="large-2 medium-3 small-12 cell hide-for-small-only" id="side-nav-container">

            <section id="metrics-side-section" class="medium-12 cell">

                <div class="bordered-box">

                    <ul id="metrics-sidemenu" class="vertical menu accordion-menu" data-accordion-menu data-multi-expand="true" >

                        <?php

                        // WordPress.XSS.EscapeOutput.OutputNotEscaped
                        // @phpcs:ignore
                        echo apply_filters( 'dt_metrics_menu', '' );

                        ?>

                    </ul>

                </div>

            </section>

        </div>

        <?php endif; ?>

        <!-- List Section -->
        <div class="<?php echo ( $current_user_can_manage_users ) ? 'large-10 medium-9 small-12' : '' ?> cell ">
            <section id="metrics-container" class="medium-12 cell">
                <div class="bordered-box">
                    <div id="chart">
                        <div class="grid-x">
                            <div id="page-title" class="cell">
                                <h3><?php esc_html_e( 'Add New User', 'disciple_tools' ); ?><span class="loading-spinner" style="margin-inline-start: 5px"></span></h3>
                            </div>

                            <div class="cell medium-12">

                                <form data-abide id="new-user-form">

                                    <div data-abide-error class="alert callout" style="display: none;">
                                        <p><i class="fi-alert"></i><?php esc_html_e( 'There are some errors in your form.', 'disciple_tools' ); ?></p>
                                    </div>

                                    <div class="grid-x row gutter-small">
                                        <div class="cell medium-6">
                                            <dl>
                                                <dt><label for="subassigned[query]"><?php esc_html_e( 'Contact to make a user (optional)', 'disciple_tools' ); ?></label></dt>
                                                <dd>
                                                    <div class="subassigned details">
                                                        <var id="subassigned-result-container" class="result-container subassigned-result-container"></var>
                                                        <div id="subassigned_t" name="form-subassigned" class="scrollable-typeahead">
                                                            <div class="typeahead__container">
                                                                <div class="typeahead__field">
                                                                    <span class="typeahead__query">
                                                                        <input id="subassigned[query]" class="js-typeahead-subassigned input-height"
                                                                               name="subassigned[query]" placeholder="<?php esc_html_e( 'Search multipliers and contacts', 'disciple_tools' ); ?>"
                                                                               autocomplete="off">
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </dd>
                                            </dl>
                                            <div id="contact-result"></div>
                                            <dl>
                                                <dt><label for="name"><?php esc_html_e( 'Display Name', 'disciple_tools' ); ?></label></dt>
                                                <dd><input type="text" class="input" id="name" placeholder="<?php esc_html_e( 'Nickname (Display Name)', 'disciple_tools' ); ?>" required /> </dd>
                                                <dt><label for="email"><?php esc_html_e( 'Email', 'disciple_tools' ); ?></label></dt>
                                                <dd><input type="email" class="input" id="email" placeholder="<?php esc_html_e( 'Email', 'disciple_tools' ); ?>" required /> </dd>
                                                <div class="hidden-fields" style="display:none">
                                                    <dt><label for="username"><?php esc_html_e( 'Username', 'disciple_tools' ); ?></label></dt>
                                                    <dd><input type="text" class="input" id="username" placeholder="<?php esc_html_e( 'Username', 'disciple_tools' ); ?>" /> </dd>
                                                    <dt><label for="password"><?php esc_html_e( 'Password', 'disciple_tools' ); ?></label></dt>
                                                    <dd><input type="password" class="input" id="password" placeholder="<?php esc_html_e( 'Password', 'disciple_tools' ); ?>" /> </dd>
                                                </div>
                                                <?php if ( $current_user_can_manage_users ) : ?>
                                                <div id="show-shield-banner" style="text-align: center; background-color:rgb(236, 245, 252);margin: 3px -15px 15px -15px;">
                                                    <a class="button clear" id="show-hidden-fields" style="margin:0;padding:3px 0; width:100%">
                                                        <?php esc_html_e( 'See More Options', 'disciple_tools' ); ?>
                                                    </a>
                                                    <a class="button clear" id="hide-hidden-fields" style="margin:0;padding:3px 0; width:100%; display: none;">
                                                        <?php esc_html_e( 'See Fewer Options', 'disciple_tools' ); ?>
                                                    </a>
                                                </div>
                                                <?php endif; ?>
                                                <dt><label for="new-user-language-dropdown"><?php esc_html_e( 'Language', 'disciple_tools' ); ?></label></dt>
                                                <dd id="new-user-language-dropdown"></dd>
                                                <?php if ( $current_user_can_manage_users ): ?>
                                                <dt><?php esc_html_e( 'Role', 'disciple_tools' ); ?></dt>
                                                <dd>
                                                    <?php
                                                    $user_roles = [ 'multiplier' ];
                                                    $roles = dt_list_roles();
                                                    ?>
                                                    <ul id="user_roles_list" class="no-bullet">
                                                        <?php foreach ( $roles as $role_key => $role_value ) : ?>
                                                            <li>
                                                                <label style="color:<?php echo esc_html( $role_value['disabled'] ? 'grey' : 'inherit' ); ?>">
                                                                    <input type="checkbox" name="dt_multi_role_user_roles[]"
                                                                           value="<?php echo esc_attr( $role_key ); ?>"
                                                                        <?php checked( in_array( $role_key, $user_roles ) ); ?>
                                                                        <?php disabled( $role_value['disabled'] ); ?> />
                                                                    <strong>
                                                                        <?php
                                                                        if ( isset( $role_value['label'] ) && !empty( $role_value['label'] ) ){
                                                                            echo esc_html( $role_value['label'] );
                                                                        } else {
                                                                            echo esc_html( $role_key );
                                                                        }
                                                                        ?>
                                                                    </strong>
                                                                    <?php
                                                                    if ( isset( $role_value['description'] ) ){
                                                                        echo ' - ' . esc_html( $role_value['description'] );
                                                                    }
                                                                    ?>
                                                                </label>
                                                            </li>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                    <p> <a href="https://disciple.tools/user-docs/getting-started-info/roles/" target="_blank"><?php esc_html_e( 'Click here to see roles documentation', 'disciple_tools' ); ?></a>  </p>
                                                </dd>
                                                <?php endif; ?>
                                            </dl>
                                            <button type="submit" class="submit button loader" id="create-user"><?php esc_html_e( 'Create User', 'disciple_tools' ); ?></button>
                                        </div>

                                        <div id="optional-fields" class="cell medium-6 show-for-medium">
                                            <dl>
                                                <dt><label for="first_name"><?php esc_html_e( 'First Name', 'disciple_tools' )?> (<?php esc_html_e( 'optional', 'disciple_tools' )?>)</label></dt>
                                                <dd><input class="input" type="text" class="input" id="first_name" data-optional
                                                        name="first_name" placeholder="<?php esc_html_e( 'First Name', 'disciple_tools' )?>" />
                                                </dd>
                                                <dt><label for="last_name"><?php esc_html_e( 'Last Name', 'disciple_tools' )?> (<?php esc_html_e( 'optional', 'disciple_tools' )?>)</label></dt>
                                                <dd><input class="input" type="text" class="input" id="last_name" data-optional name="last_name" placeholder="<?php esc_html_e( 'Last Name', 'disciple_tools' )?>"/></dd>
                                                <dt><label for="gender">
                                                        <?php esc_html_e( 'Gender', 'disciple_tools' ) ?> (<?php esc_html_e( 'optional', 'disciple_tools' )?>)
                                                    </label></dt>
                                                <dd>
                                                    <select class="select-field" id="gender" style="width:auto; display: block" data-optional>
                                                        <?php foreach ( $gender_fields['default'] as $option_key => $option_value ): ?>
                                                            <option value="<?php echo esc_html( $option_key )?>">
                                                                <?php echo esc_html( $option_value['label'] ) ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </dd>

                                                <?php // site defined fields
                                                foreach ( $dt_user_fields as $dt_field ) {
                                                    if ( empty( $dt_field['enabled'] ) ){
                                                        continue;
                                                    }
                                                    ?>
                                                    <dt>
                                                        <label for="<?php echo esc_attr( $dt_field['key'] ) ?>"><?php echo esc_html( $dt_field['label'] ) ?> (<?php esc_html_e( 'optional', 'disciple_tools' )?>)</label>
                                                    </dt>
                                                    <dd><input type="text"
                                                            data-optional
                                                            class="input"
                                                            id="<?php echo esc_attr( $dt_field['key'] ) ?>"
                                                            name="<?php echo esc_attr( $dt_field['key'] ) ?>"
                                                            placeholder="<?php echo esc_html( $dt_field['label'] ) ?>"
                                                            value=""/>
                                                    </dd>
                                                    <?php
                                                } // end foreach
                                                ?>

                                                <dt><label for="description"><?php esc_html_e( 'Biography', 'disciple_tools' )?> (<?php esc_html_e( 'optional', 'disciple_tools' )?>)</label></dt>
                                                <dd><textarea
                                                        type="text" class="input" id="description"
                                                        name="description"
                                                        placeholder="<?php esc_html_e( 'Biography', 'disciple_tools' )?>"
                                                        rows="5"
                                                        data-optional
                                                    ></textarea>
                                                </dd>
                                            </dl>
                                        </div>

                                    </div>

                                    <div id="show-optional-fields-banner" class="show-for-small-only" style="text-align: center; background-color:rgb(236, 245, 252);margin: 3px -15px 15px -15px;">
                                        <a class="button clear" id="show-optional-fields" style="margin:0;padding:3px 0; width:100%">
                                            <?php esc_html_e( 'See More Options', 'disciple_tools' ); ?>
                                        </a>
                                        <a class="button clear" id="hide-optional-fields" style="margin:0;padding:3px 0; width:100%; display: none;">
                                            <?php esc_html_e( 'See Fewer Options', 'disciple_tools' ); ?>
                                        </a>
                                    </div>



                                </form>
                            </div>

                            <div class="cell" id="result-link"></div>
                            <div class="cell" style="height:20rem;"></div>
                        </div>
                    </div>
                </div>
            </section>
        </div>

    </div> <!-- end #inner-content -->

    <div class="reveal" id="confirm-user-upgrade" data-reveal data-reset-on-close>
        <h3><?php esc_html_e( 'Upgrading a contact', 'disciple_tools' ); ?></h3>
        <p>
            <img class="dt-icon dt-red-icon" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/broken.svg' ) ?>"/>
            <?php esc_html_e( 'The new user will have access to all the fields and comments on this contact record. We suggest archiving the comments if they contain sensitive data.', 'disciple_tools' ); ?>
        </p>
        <div class="grid-x">
            <div class="button-group">
                 <button class="button button-cancel clear" data-close aria-label="Close reveal" type="button">
                     <?php echo esc_html__( 'Back', 'disciple_tools' )?>
                 </button>
                 <button class="button clear" id="continue-archive-comments" data-close><?php esc_html_e( 'Continue and archive comments', 'disciple_tools' ); ?></button>
                 <button class="button" id="continue-user-creation" data-close><?php esc_html_e( 'Continue, keeping comments', 'disciple_tools' ); ?></button>
            </div>
             <button class="close-button" data-close aria-label="<?php esc_html_e( 'Close', 'disciple_tools' ); ?>" type="button">
                 <span aria-hidden="true">&times;</span>
             </button>
         </div>
    </div>

    <div class="reveal" id="merge-contact-details" data-reveal data-reset-on-close>
        <h3><?php esc_html_e( 'Merge Contact Details', 'disciple_tools' ); ?></h3>
        <p>
            <?php esc_html_e( 'A new contact record has been created.', 'disciple_tools' ); ?> <a id="new-contact-record" href="" target="_blank"><img style="height:20px; width:20px; vertical-align: text-bottom" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/open-link.svg' ) ?>"/></a>
        </p>
        <p>
            <?php esc_html_e( 'The old one is now closed.', 'disciple_tools' ); ?> <a id="existing-contact-record" herf="" target="_blank"><img style="height:20px; width:20px; vertical-align: text-bottom" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/open-link.svg' ) ?>"/></a>
        </p>
        <p>
            <?php esc_html_e( 'Do you wish to merge fields from the old record to the new one?', 'disciple_tools' ); ?>
        </p>

        <div class="grid-x">
            <div class="button-group">
                 <button class="button button-cancel" data-close aria-label="Close reveal" type="button">
                     <?php echo esc_html__( 'Close', 'disciple_tools' )?>
                 </button>
                <a class="button" href="" id="merge-new-contact-link" data-close><?php esc_html_e( 'Merge', 'disciple_tools' ); ?></a>
            </div>
             <button class="close-button" data-close aria-label="<?php esc_html_e( 'Close', 'disciple_tools' ); ?>" type="button">
                 <span aria-hidden="true">&times;</span>
             </button>
         </div>
    </div>

</div> <!-- end #content -->

<?php get_footer(); ?>
