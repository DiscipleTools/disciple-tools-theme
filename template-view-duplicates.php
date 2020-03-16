<?php
/*
Template Name: View Duplicates
*/

if ( ! current_user_can( 'access_contacts' ) ) {
    wp_safe_redirect( '/settings' );
}

get_header();

$dt_contacts = new Disciple_Tools_Contacts();
$dt_duplicates = wp_unslash( $dt_contacts->get_all_duplicates() );
?>

    <div id="content" class="template-view-duplicates duplicates-page">

        <div id="inner-content" class="grid-x grid-margin-x">

            <main id="main" class="large-12 medium-12 cell" role="main">
                <div class="bordered-box">
                    <h3><?php esc_html_e( 'Duplicate Contacts', 'disciple_tools' ) ?><button class="help-button float-right" data-section="duplicates-template-help-text">
                        <img class="help-icon" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/help.svg' ) ?>"/>
                    </button></h3>

                    <table id='table-duplicates'>
                        <?php
                        foreach ( $dt_duplicates as $dt_id => $dt_duplicate ) {
                            if ($dt_duplicate['count'] <= 0) { continue; }

                            echo "<form name='merge_".esc_html( $dt_id )."' method='POST' action='".esc_html( site_url() )."/contacts/".esc_html( $dt_id )."'>
                            <input type='hidden' name='dt_contact_nonce' value='".esc_attr( wp_create_nonce() )."'/></form>
                            <tr id='".esc_html( $dt_id )."'>
                            <td><a>".esc_html( $dt_duplicate['name'] )."</a></td>
                            <td><a>".esc_html( $dt_duplicate['count'] )." ".esc_html__( 'duplicates', 'disciple_tools' )."</a></td>
                            </tr>";
                        }
                        ?>
                    </table>
                </div>
            </main> <!-- end #main -->

        </div> <!-- end #inner-content -->

        <script type="text/javascript">
            $("#table-duplicates").find('tr').click(function() {
                var form = $("form[name=merge_" + $(this).prop('id') + "]");
                form.append("<input type='hidden' name='merge' value='1'>");
                form.submit();
            });
        </script>

    </div> <!-- end #content -->

<?php get_footer(); ?>
