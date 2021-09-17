<?php
/*
 * Name: User Management
*/

$current_user_can_managage_users = current_user_can( 'list_users' ) || current_user_can( 'manage_dt' );
if ( !$current_user_can_managage_users && !DT_User_Management::non_admins_can_make_users() ) {
    wp_safe_redirect( '/registered' );
    exit();
}
$dt_url_path = dt_get_url_path();
$user_management_options = DT_User_Management::user_management_options();

?>

<?php get_header(); ?>

<div id="user-add-user" style="padding:15px">

    <div id="inner-content" class="grid-x grid-margin-x grid-margin-y">

        <?php if ( $current_user_can_managage_users ): ?>

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
        <div class="<?php echo ( $current_user_can_managage_users ) ? "large-10 medium-9 small-12" : "" ?> cell ">
            <section id="metrics-container" class="medium-12 cell">
                <div class="bordered-box">
                    <div id="chart">
                        <div class="grid-x">
                            <div id="page-title" class="cell"><h3><?php esc_html_e( 'Add New User', 'disciple_tools' ); ?></h3></div>
                            <div class="cell medium-6">
                                <form data-abide id="new-user-form">
                                    <div data-abide-error class="alert callout" style="display: none;">
                                        <p><i class="fi-alert"></i><?php esc_html_e( 'There are some errors in your form.', 'disciple_tools' ); ?></p>
                                    </div>

                                    <dl>
                                        <dt><?php esc_html_e( 'Contact to make a user (optional)', 'disciple_tools' ); ?></dt>
                                        <dd>
                                            <div class="subassigned details">
                                                <var id="subassigned-result-container" class="result-container subassigned-result-container"></var>
                                                <div id="subassigned_t" name="form-subassigned" class="scrollable-typeahead">
                                                    <div class="typeahead__container">
                                                        <div class="typeahead__field">
                                                            <span class="typeahead__query">
                                                                <input class="js-typeahead-subassigned input-height"
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
                                        <dt><?php esc_html_e( 'Nickname (Display Name)', 'disciple_tools' ); ?></dt>
                                        <dd><input type="text" class="input" id="name" placeholder="<?php esc_html_e( 'Nickname (Display Name)', 'disciple_tools' ); ?>" required /> </dd>
                                        <dt><?php esc_html_e( 'Email', 'disciple_tools' ); ?></dt>
                                        <dd><input type="email" class="input" id="email" placeholder="<?php esc_html_e( 'Email', 'disciple_tools' ); ?>" required /> </dd>
                                        <dt><?php esc_html_e( 'Language', 'disciple_tools' ); ?></dt>
                                        <dd id="new-user-language-dropdown"></dd>

                                        <?php if ( $current_user_can_managage_users ): ?>

                                        <dt><?php esc_html_e( 'Role', 'disciple_tools' ); ?></dt>
                                        <dd>
                                            <?php
                                            $user_roles = [ 'multiplier' ];

                                            $dt_roles = dt_multi_role_get_editable_role_names();
                                            $expected_roles = apply_filters( 'dt_set_roles_and_permissions', [] );
                                            $upgrade_to_admin_disabled = !is_super_admin() && !dt_current_user_has_role( 'administrator' );
                                            $admin_roles = [ "administrator", "dt_admin" ];

                                            uasort( $expected_roles, function ( $item1, $item2 ){
                                                return ( $item1['order'] ?? 50 ) <=> ( $item2['order'] ?? 50 );
                                            } );
                                            ?>

                                            <ul id="user_roles_list" class="no-bullet">
                                                <?php foreach ( $expected_roles as $role_key => $role_value ) : ?>
                                                    <li>
                                                        <label style="color:<?php echo esc_html( $role_key === 'administrator' && $upgrade_to_admin_disabled ? 'grey' : 'inherit' ); ?>">
                                                            <input type="checkbox" name="dt_multi_role_user_roles[]"
                                                                   value="<?php echo esc_attr( $role_key ); ?>"
                                                                <?php checked( in_array( $role_key, $user_roles ) ); ?>
                                                                <?php disabled( $upgrade_to_admin_disabled && in_array( $role_key, $admin_roles, true ) ); ?> />
                                                            <strong>
                                                                <?php
                                                                if ( isset( $role_value["label"] ) && !empty( $role_value["label"] ) ){
                                                                    echo esc_html( $role_value["label"] );
                                                                } else {
                                                                    echo esc_html( $role_key );
                                                                }
                                                                ?>
                                                            </strong>
                                                            <?php
                                                            if ( isset( $role_value["description"] ) ){
                                                                echo ' - ' . esc_html( $role_value["description"] );
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


                                    <button type="submit" class="submit button" id="create-user"><?php esc_html_e( 'Create User', 'disciple_tools' ); ?></button> <span class="spinner"></span>
                                </form>

                            </div>
                            <div class="cell medium-6"></div>
                            <div class="cell" id="result-link"></div>
                            <div class="cell" style="height:20rem;"></div>
                        </div>
                    </div>
                </div>
            </section>
        </div>

    </div> <!-- end #inner-content -->

</div> <!-- end #content -->

<?php get_footer(); ?>
