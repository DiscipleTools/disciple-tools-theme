<?php
/*
Template Name: View Duplicates
*/

?>
<?php
    get_header();

    $dt_contacts = new Disciple_Tools_Contacts();
    $dt_duplicates = wp_unslash( $dt_contacts->get_all_duplicates() );
?>

    <div id="content">

        <div id="inner-content" class="grid-x grid-margin-x">

            <main id="main" class="large-12 medium-12 cell" role="main">
                <div class="bordered-box">
                    <h3>Duplicate Contacts</h3>
<!--                    <button type="button" id="merge-dupe-modal" data-open="merge-dupe-modal" class="button">
                        <?php // esc_html_e( "Go to duplicates", 'disciple_tools' ) ?>
                    </button>-->
                    <table id='table-duplicates'>
                        <?php
                        foreach ( $dt_duplicates as $dt_id => $dt_duplicate ) {
                            if ($dt_duplicate['count'] <= 0) { continue; }
                            echo "<form name='merge_".esc_html( $dt_id )."' method='POST' action='".esc_html( site_url() )."/contacts/".esc_html( $dt_id )."'><input type='hidden' name='dt_contact_nonce' value='".esc_attr( wp_create_nonce() )."'/></form><tr id='".esc_html( $dt_id )."'><td><a>".esc_html( $dt_duplicate['name'] )."</a></td><td>
                            <a>".esc_html( $dt_duplicate['count'] )." duplicates</a></td></tr>";
                        }
                        ?>
                    </table>
                </div>


        </div>

        <script type="text/javascript">
            $("#table-duplicates").find('tr').click(function() {
                var form = $("form[name=merge_" + $(this).prop('id') + "]");
                form.append("<input type='hidden' name='merge' value='1'>");
                form.submit();
            });
        </script>

            </main> <!-- end #main -->

        </div> <!-- end #inner-content -->

    </div> <!-- end #content -->

<?php get_footer(); ?>
