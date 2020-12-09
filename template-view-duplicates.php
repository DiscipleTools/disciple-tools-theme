<?php
/*
Template Name: View Duplicates
*/
dt_please_log_in();

if ( ! current_user_can( 'dt_all_access_contacts' ) ) {
    wp_safe_redirect( '/settings' );
    exit();
}

get_header();

$dt_duplicates = Disciple_Tools_Contacts::get_all_duplicates(); // @todo upgrade for the DT 1.0 post type upgrade
$post_settings = apply_filters( "dt_get_post_type_settings", [], "contacts" );
?>

    <div id="content" class="template-view-duplicates duplicates-page">

        <div id="inner-content" class="grid-x grid-margin-x">

            <main id="main" class="large-12 medium-12 cell" role="main">
                <div class="bordered-box">
                    <h3><?php esc_html_e( 'Duplicate Contacts', 'disciple_tools' ) ?>
                        <span id="duplicates-spinner" class="loading-spinner"></span>
                        <button class="help-button float-right" data-section="duplicates-template-help-text">
                            <img class="help-icon" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/help.svg' ) ?>"/>
                        </button>
                    </h3>

                    <?php
                    foreach ( $dt_duplicates as $channel_key => $channel_values ) {
                        if ( empty( $channel_values["dups"] ) ) {
                            continue;
                        }
                        ?>
                        <h4><?php echo esc_html( sprintf( __( "Exact matches on: %s", 'disciple_tools' ), $channel_values["name"] ) ); ?></h4>
                        <table style="margin-bottom:100px">
                            <tr>
                                <th style="text-align: start"><?php esc_html_e( 'Dismiss', 'disciple_tools' ); ?></th>
                                <th style="text-align: start"><?php echo esc_html( $channel_values["name"] ); ?></th>
                                <th style="text-align: start"><?php esc_html_e( 'Contacts', 'disciple_tools' ); ?></th>
                            </tr>
                        <?php foreach ( $channel_values["dups"] as $dt_dup_val => $dt_duplicate_values ) {
                            $first_id = array_keys( $dt_duplicate_values )[0];
                            $row = $channel_key . '-' . $first_id
                            ?>
                            <tr id='<?php echo esc_attr( $row ) ?>'>
                                <td>
                                    <a class="dismiss_all" data-row="<?php echo esc_html( $row ); ?>" data-id="<?php echo esc_html( $first_id ); ?>">
                                        <?php esc_html_e( 'Dismiss Row', 'disciple_tools' ); ?>
                                    </a>
                                </td>
                                <td><?php echo esc_html( $dt_dup_val )?></td>
                                <td><?php foreach ( $dt_duplicate_values["posts"] as $dup_post_id => $post_values ):
                                    $status_option = isset( $post_settings["fields"]["overall_status"]["default"][$post_values["status"]] ) ? $post_settings["fields"]["overall_status"]["default"][$post_values["status"]] : [];
                                    $reason_closed = isset( $post_settings["fields"]["reason_closed"]["default"][$post_values["reason_closed"]] ) ? $post_settings["fields"]["reason_closed"]["default"][$post_values["reason_closed"]]["label"] : "";
                                    ?>
                                    <a target="_blank" href="<?php echo esc_html( site_url() )."/contacts/".esc_html( $dup_post_id ) ?>?open-duplicates=1"><?php echo esc_html( $post_values["name"] ); ?></a>
                                    <span class="dt-status-square" title="<?php echo esc_html( $status_option["label"] . ( $reason_closed ? ' - ' . $reason_closed : '' ) ); ?>"
                                          style="background-color: <?php echo esc_html( $status_option["color"] ?? '' ) ?>; vertical-align: sub">&nbsp;
                                    </span>
                                    <span style="margin-right: 5px; margin-left: 5px">|</span>
                                <?php endforeach; ?>
                                </td>
                            </tr>
                        <?php } ?>
                        </table>
                    <?php } ?>
                </div>
            </main> <!-- end #main -->
        </div> <!-- end #inner-content -->

        <script type="text/javascript">
          $('.dismiss_all').on( 'click', function () {
            $('#duplicates-spinner').addClass('active')
            let id = $(this).data('id')
            let row = $(this).data('row')
            makeRequestOnPosts('GET', `contacts/${id}/dismiss-duplicates`, {'id':'all'}).then(()=> {
              $(`#${row}`).remove()
              $('#duplicates-spinner').removeClass('active')
            })
          })
        </script>

    </div> <!-- end #content -->

<?php get_footer(); ?>
