<?php
/*
 * Name: User Management
*/
if ( !current_user_can( 'list_users' ) && !current_user_can( 'manage_dt' ) ) {
    wp_safe_redirect( '/registered' );
    exit();
}
$dt_url_path = dt_get_url_path();
$dt_user_fields = Disciple_Tools_Users::get_users_fields();
$default_user_roles = Disciple_Tools_Roles::get_dt_roles_and_permissions();

?>

<?php get_header(); ?>

<div id="user-management-tools">

    <div id="inner-content" class="grid-x grid-margin-x grid-margin-y">

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

        <!-- List Section -->
        <div class="large-10 medium-9 small-12 cell ">
            <section id="metrics-container" class="medium-12 cell">
                <div class="bordered-box">
                    <div id="chart">

                    </div><!-- Container for charts -->
                </div>
            </section>
        </div>

        <?php if ( strpos( $dt_url_path, 'user-management' ) !== false ) : ?>
            <!-- REVEAL SECTION-->
            <div class="large reveal" id="user_modal" data-reveal data-v-offset="10">

                <div id="user_modal_content">
                    <div id="user-name-wrapper">
                        <h3 style="display: inline-block; margin-right:20px">
                            <span id="user_name"><?php esc_html_e( 'Multiplier Name', 'disciple_tools' ) ?></span>
                            (#<span id="user-id-reveal"></span>)
                        </h3>
                        <ul class="tabs" data-tabs id="example-tabs" style="display: inline-block; vertical-align: bottom">
                            <li class="tabs-title is-active"><a href="#general" aria-selected="true">General</a></li>
                            <li class="tabs-title"><a data-tabs-target="dmm" href="#dmm">DMM</a></li>
                        </ul>
                    </div>

                    <button class="close-button" data-close aria-label="Close reveal" type="button">
                        <span aria-hidden="true">&times;</span>
                    </button>

                    <hr>
                    <div class="tabs-content" data-tabs-content="example-tabs">
                        <div class="tabs-panel is-active" id="general">
                            <div class="grid-x grid-margin-x">
                                <!-- left column -->
                                <div class="cell medium-4">

                                    <!-- Daily Activity -->
                                    <div class="bordered-box">
                                        <h4><?php esc_html_e( 'Daily Activity', 'disciple_tools' ); ?></h4>
                                        <div id="day_activity_chart" style="height: 300px"></div>
                                    </div>

                                    <!-- Activity -->
                                    <div class="bordered-box">
                                        <h4><?php esc_html_e( 'Activity', 'disciple_tools' ); ?></h4>
                                        <div id="activity"></div>
                                    </div>

                                </div>
                                <!-- end left -->


                                <!-- center column-->
                                <div class="cell medium-4">

                                    <!-- User Status -->
                                    <div class="bordered-box">
                                        <h4><?php esc_html_e( 'User Status', 'disciple_tools' ); ?></h4>
                                        <select id="user_status" class="select-field">
                                            <option></option>
                                            <?php foreach ( $dt_user_fields['user_status']['options'] as $status_key => $status_value ) : ?>
                                                <option value="<?php echo esc_html( $status_key ); ?>"><?php echo esc_html( $status_value['label'] ); ?></option>
                                            <?php endforeach; ?>
                                        </select>

                                        <!-- Workload Status -->
                                        <h4><?php esc_html_e( 'Workload Status', 'disciple_tools' ); ?></h4>
                                        <select id="workload_status" class="select-field">
                                            <?php $workload_status_options = $dt_user_fields['workload_status']['options'] ?? array() ?>
                                            <option></option>
                                            <?php foreach ( $workload_status_options as $key => $val ) : ?>
                                                <option value="<?php echo esc_html( $key ) ?>"><?php echo esc_html( $val['label'] ) ?></option>
                                            <?php endforeach; ?>
                                        </select>

                                        <!-- Locations -->
                                        <?php if ( DT_Mapbox_API::get_key() ) : /* If Mapbox is enabled. */?>
                                            <h4><?php esc_html_e( 'Location Responsibility', 'disciple_tools' ) ?><a class="button clear float-right" id="new-mapbox-search"><?php esc_html_e( 'add', 'disciple_tools' ) ?></a></h4>
                                            <div id="mapbox-wrapper"></div>
                                        <?php else : ?>
                                            <h4><?php esc_html_e( 'Location Responsibility', 'disciple_tools' ) ?></h4>
                                            <div class="location_grid">
                                                <var id="location_grid-result-container" class="result-container"></var>
                                                <div id="location_grid_t" name="form-location_grid" class="scrollable-typeahead typeahead-margin-when-active">
                                                    <div class="typeahead__container">
                                                        <div class="typeahead__field">
                                                    <span class="typeahead__query">
                                                        <input class="js-typeahead-location_grid input-height"
                                                               name="location_grid[query]"
                                                               placeholder="<?php esc_html_e( 'Search Locations', 'disciple_tools' ) ?>"
                                                               autocomplete="off">
                                                    </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                        <h4 class="" style="margin-top:30px">
                                            <img class="dt-icon" src="<?php echo esc_url( get_template_directory_uri() ) . '/dt-assets/images/languages.svg' ?>">
                                            <span style="display: inline-block;"><?php esc_html_e( 'Languages you are comfortable speaking', 'disciple_tools' )?></span>
                                            <span id="languages-spinner" style="display: inline-block" class="loading-spinner"></span>
                                        </h4>
                                        <div class="small button-group" style="display: inline-block" id="languages_multi_select">
                                            <?php foreach ( $dt_user_fields['user_languages']['options'] as $option_key => $option_value ): ?>
                                                <button id="<?php echo esc_html( $option_key ) ?>" data-field-key="<?php echo esc_html( 'languages' ) ?>"
                                                        class="dt_multi_select empty-select-button select-button button ">
                                                    <?php echo esc_html( $option_value['label'] ) ?>
                                                </button>
                                            <?php endforeach; ?>
                                        </div>

                                    </div>

                                    <!-- Availability -->
                                    <div class="bordered-box">
                                        <h4><?php esc_html_e( 'Availability', 'disciple_tools' ) ?></h4>
                                        <p><?php esc_html_e( 'Set the dates you will be unavailable so the Dispatcher will know your availability to receive new contacts', 'disciple_tools' ) ?></p>
                                        <div>
                                            <?php esc_html_e( 'Schedule Unavailability', 'disciple_tools' )?>:
                                        </div>
                                        <div>
                                            <div class="date_range">
                                                <input type="text" class="date-picker" id="date_range" autocomplete="off" placeholder="2020-01-01 - 2020-02-03" />
                                            </div>
                                        </div>
                                        <div id="add_unavailable_dates_spinner" class="loading-spinner"></div>

                                        <div><?php esc_html_e( 'Travel or Away Dates', 'disciple_tools' ) ?></div>
                                        <div>
                                            <table>
                                                <thead>
                                                <tr>
                                                    <th><?php esc_html_e( 'Start Date', 'disciple_tools' ) ?></th>
                                                    <th><?php esc_html_e( 'End Date', 'disciple_tools' ) ?></th>
                                                    <th></th>
                                                </tr>
                                                </thead>
                                                <tbody id="unavailable-list"></tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <!-- end center -->

                                <!-- right column -->
                                <div class="cell medium-4">

                                    <!-- user profile -->
                                    <div class="bordered-box">

                                        <h4><?php esc_html_e( 'User Profile', 'disciple_tools' ); ?><span id="profile_loading" class="loading-spinner active"></span></h4>

                                        <button id="corresponds_to_contact_link" class="button" type="button">
                                            <?php esc_html_e( 'View contact record', 'disciple_tools' ); ?>
                                            <img class="dt-icon dt-white-icon" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/open-link.svg' ) ?>"/>
                                        </button>
                                        <?php if ( current_user_can( 'promote_users' ) ) : ?>
                                            <button id="wp_admin_edit_user" class="button" type="button">
                                                <?php esc_html_e( 'Edit User', 'disciple_tools' ); ?>
                                                <img class="dt-icon dt-white-icon" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/open-link.svg' ) ?>"/>
                                            </button>
                                        <?php endif; ?>
                                        <button id="reset_user_pwd_email" class="button" type="button">
                                            <span id="reset_user_pwd_email_text"><?php esc_html_e( 'Email Password Reset', 'disciple_tools' ); ?></span>
                                            <span id="reset_user_pwd_email_icon"></span>
                                        </button>
                                        <p>
                                            <?php esc_html_e( 'Email', 'disciple_tools' ); ?>: <span id="user_email"></span>
                                        </p>
                                        <p>
                                            <?php esc_html_e( 'Display Name', 'disciple_tools' ); ?>
                                            <span id="<?php echo esc_html( 'update_display_name' ); ?>-spinner" class="loading-spinner"></span>
                                            <input type="text" id="update_display_name" class="text-input">
                                        </p>

                                        <?php esc_html_e( 'Gender', 'disciple_tools' ); ?>
                                        <span id="<?php echo esc_html( 'gender' ); ?>-spinner" class="loading-spinner"></span>
                                        <select class="select-field" id="gender">
                                            <option></option>
                                            <option value="male"><?php esc_html_e( 'Male', 'disciple_tools' ); ?></option>
                                            <option value="female"><?php esc_html_e( 'Female', 'disciple_tools' ); ?></option>
                                        </select>

                                        <?php // site defined fields
                                        $dt_user_fields = dt_get_site_custom_lists( 'user_fields' );
                                        foreach ( $dt_user_fields as $dt_field ) {
                                            if ( empty( $dt_field['enabled'] ) ){
                                                continue;
                                            }
                                            ?>
                                            <dt>
                                                <label for="<?php echo esc_attr( $dt_field['key'] ) ?>"><?php echo esc_html( $dt_field['label'] ) ?>
                                                    <span id="<?php echo esc_html( $dt_field['key'] ); ?>-spinner" class="loading-spinner"></span>
                                                </label>
                                            </dt>
                                            <dd><input type="text"
                                                       data-optional
                                                       class="input text-input"
                                                       id="<?php echo esc_attr( $dt_field['key'] ) ?>"
                                                       name="<?php echo esc_attr( $dt_field['key'] ) ?>"
                                                       placeholder="<?php echo esc_html( $dt_field['label'] ) ?>"
                                                       value=""/>
                                            </dd>
                                            <?php
                                        } // end foreach
                                        ?>
                                        <dt>
                                            <label for="description"><?php esc_html_e( 'Biography', 'disciple_tools' )?>
                                                <span id="<?php echo esc_html( 'description' ); ?>-spinner" class="loading-spinner"></span>
                                            </label>
                                        </dt>
                                            <dd><textarea
                                                    type="text" class="input text-input" id="description"
                                                    name="description"
                                                    placeholder="<?php esc_html_e( 'Biography', 'disciple_tools' )?>"
                                                    rows="5"
                                                    data-optional
                                                ></textarea>
                                            </dd>


                                    </div>

                                    <!-- Roles -->
                                    <?php if ( current_user_can( 'promote_users' ) ) : ?>
                                        <div class="bordered-box">
                                            <h4><?php esc_html_e( 'Roles', 'disciple_tools' ); ?></h4>
                                            <?php
                                            $expected_roles = dt_list_roles();
                                            ?>

                                            <p> <a href="https://disciple.tools/user-docs/getting-started-info/roles/" target="_blank"><?php esc_html_e( 'Click here to see roles documentation', 'disciple_tools' ); ?></a>  </p>

                                            <ul id="user_roles_list" class="no-bullet">
                                                <?php foreach ( $expected_roles as $role_key => $role_value ) : ?>
                                                    <li>
                                                        <label style="color:<?php echo esc_html( $role_key === 'administrator' && $role_value['disabled'] ? 'grey' : 'inherit' ); ?>">
                                                            <input type="checkbox" name="dt_multi_role_user_roles[]"
                                                                   value="<?php echo esc_attr( $role_key ); ?>"
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
                                            <button class="button loader" id="save_roles"><?php esc_html_e( 'Save Roles', 'disciple_tools' ); ?></button>

                                            <!--                                    <div style="display: none">-->
                                            <div id="allowed_sources_options" style="display: none">
                                                <?php
                                                $post_settings = DT_Posts::get_post_settings( 'contacts' );
                                                $sources = isset( $post_settings['fields']['sources']['default'] ) ? $post_settings['fields']['sources']['default'] : array();
                                                ?>
                                                <h3><?php esc_html_e( 'Access by Source', 'disciple_tools' ); ?></h3>

                                                <ul id="source_access_type" class="no-bullet">
                                                    <li>
                                                        <label>
                                                            <input type="radio" name="allowed_sources[]" value="all"/>
                                                            <?php esc_html_e( 'All Sources - gives access to all contacts', 'disciple_tools' ); ?>
                                                        </label>
                                                    </li>
                                                    <li>
                                                        <label>
                                                            <input type="radio" name="allowed_sources[]" value="custom_source_restrict"/>
                                                            <?php esc_html_e( 'Custom - Access own contacts and all the contacts of the selected sources below', 'disciple_tools' ); ?>
                                                        </label>
                                                    </li>
                                                    <li>
                                                        <label>
                                                            <input type="radio" name="allowed_sources[]" value="restrict_all_sources"/>
                                                            <?php esc_html_e( 'No Sources - only own contacts', 'disciple_tools' ); ?>
                                                        </label>
                                                    </li>
                                                </ul>

                                                <strong style="margin-top:30px">
                                                    <?php esc_html_e( 'Sources List', 'disciple_tools' ) ?>
                                                </strong>
                                                <ul id="allowed_sources" class="ul-no-bullets">
                                                    <?php foreach ( $sources as $source_key => $source_value ) : ?>
                                                        <li>
                                                            <label>
                                                                <input type="checkbox" name="allowed_sources[]" value="<?php echo esc_html( $source_key ) ?>"/>
                                                                <?php echo esc_html( $source_value['label'] ) ?>
                                                            </label>
                                                        </li>
                                                    <?php endforeach; ?>
                                                </ul>
                                                <button class="button loader" id="save_allowed_sources"><?php esc_html_e( 'Save Allowed Sources', 'disciple_tools' ); ?></button>

                                            </div>
                                        </div>
                                    <?php endif; ?>

                                </div>
                                <!-- end right -->
                            </div>
                        </div>
                        <div class="tabs-panel" id="dmm">
                            <div class="grid-x grid-margin-x">
                                <!-- left column -->
                                <div class="cell medium-4">

                                    <div class="bordered-box" id="hero_status">

                                        <div class="grid-x">

                                            <div class="cell">
                                                <h4><?php esc_html_e( 'Status', 'disciple_tools' ); ?></h4>
                                            </div>
                                            <div class="cell small-6">
                                                <div class="section-subheader">
                                                    <?php esc_html_e( 'Active Contacts', 'disciple_tools' )?>
                                                </div>
                                                <p class="hero-number" id="active_contacts"></p>
                                            </div>
                                            <div class="cell small-6">
                                                <div class="section-subheader">
                                                    <?php esc_html_e( 'Updates Needed', 'disciple_tools' )?>
                                                </div>
                                                <p class="hero-number" id="update_needed_count"></p>
                                            </div>
                                            <div class="cell small-6">
                                                <div class="section-subheader">
                                                    <?php esc_html_e( 'Pending', 'disciple_tools' )?>
                                                </div>
                                                <p class="hero-number" id="needs_accepted_count"></p>
                                            </div>

                                            <div class="cell small-6">
                                                <div class="section-subheader">
                                                    <?php esc_html_e( 'Unread Notifications', 'disciple_tools' )?>
                                                </div>
                                                <p class="hero-number" id="unread_notifications"></p>
                                            </div>

                                            <div class="cell">
                                                <hr>
                                                <h4><?php esc_html_e( 'Assignments', 'disciple_tools' ); ?></h4>
                                            </div>
                                            <div class="cell small-6">
                                                <div class="section-subheader">
                                                    <?php esc_html_e( 'This Month', 'disciple_tools' ); ?>
                                                </div>
                                                <p id="assigned_this_month"></p>
                                                <div class="section-subheader">
                                                    <?php esc_html_e( 'Last Month', 'disciple_tools' ); ?>
                                                </div>
                                                <p id="assigned_last_month"></p>
                                            </div>
                                            <div class="cell small-6">
                                                <div class="section-subheader">
                                                    <?php esc_html_e( 'This Year', 'disciple_tools' ); ?>
                                                </div>
                                                <p id="assigned_this_year"></p>
                                                <div class="section-subheader">
                                                    <?php esc_html_e( 'All Time', 'disciple_tools' ); ?>
                                                </div>
                                                <p id="assigned_all_time"></p>
                                            </div>

                                        </div>
                                    </div>

                                </div><!-- end left -->

                                <!-- center column-->
                                <div class="cell medium-4">
                                    <!-- Stats -->
                                    <div class="bordered-box">
                                        <h4><?php esc_html_e( 'Pace', 'disciple_tools' ); ?></h4>
                                        <div class="subheader"><?php esc_html_e( 'Assigned and not accepted', 'disciple_tools' ); ?></div>
                                        <ul id="unaccepted_contacts"></ul>
                                        <div class="subheader"><?php esc_html_e( 'Time from assigned to contact accepted for the last 10 contacts', 'disciple_tools' ); ?> (<span id="avg_contact_accept"></span> days average)</div>
                                        <ul id="contact_accepts"></ul>
                                        <div class="subheader"><?php esc_html_e( 'Accepted with no contact attempt', 'disciple_tools' ); ?></div>
                                        <ul id="unattempted_contacts"></ul>
                                        <div class="subheader"><?php esc_html_e( 'Oldest 10 update needed', 'disciple_tools' ); ?></div>
                                        <ul id="update_needed_list"></ul>
                                        <div class="subheader"><?php esc_html_e( 'Time from assigned to contact attempt for the last 10 contacts', 'disciple_tools' ); ?> (<span id="avg_contact_attempt"></span> days average)</div>
                                        <ul id="contact_attempts"></ul>
                                    </div>

                                </div>
                                <!-- end center -->

                                <!-- right column -->
                                <div class="cell medium-4">
                                <!-- Contacts -->
                                <div class="bordered-box">
                                    <h4><?php esc_html_e( 'Contacts', 'disciple_tools' ); ?></h4>
                                    <div style="width:100%; height:400px; position:relative">
                                        <div id="status_chart_div" style="position:absolute; width: 100%; height:400px;  right: -30px; left: -30px;"></div>
                                    </div>
                                </div>


                            </div><!-- end right -->
                            </div>
                        </div>
                    </div>
                    <div class="grid-x grid-margin-x">



                    </div>
                </div>

            </div>
        <?php endif; ?>

    </div> <!-- end #inner-content -->

</div> <!-- end #content -->

<?php get_footer(); ?>
