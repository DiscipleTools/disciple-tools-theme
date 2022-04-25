<?php
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

class DT_Magic_URL_Setup {
    public function __construct(){
         add_filter( 'dt_details_additional_tiles', [ $this, 'dt_details_additional_tiles' ], 110, 2 );
         add_action( 'dt_details_additional_section', [ $this, 'dt_details_additional_section' ], 110, 2 );
    }

    /**
     * Register the Apps tile for displaying registered Magic Link Apps
     * The magic link must have the $show_app_tile variable set to true.
     * @param $tiles
     * @param $post_type
     * @return mixed
     */
    public function dt_details_additional_tiles( $tiles, $post_type ){
        if ( !isset( $tiles["apps"] ) ){
            $post_types_has_registered_apps = false;
            $magic_link_apps = dt_get_registered_types();
            foreach ( $magic_link_apps as $app_root => $app_types ){
                foreach ( $app_types as $app_type => $app_value ){
                    if ( $app_value["post_type"] === $post_type && isset( $app_value["show_app_tile"] ) && $app_value["show_app_tile"] === true ){
                        $post_types_has_registered_apps = true;
                    }
                }
            }
            if ( $post_types_has_registered_apps ){
                $tiles["apps"] = [
                    "label" => __( "Apps", 'disciple_tools' ),
                    "description" => __( "Apps available on this record.", 'disciple_tools' )
                ];
            }
        }
        return $tiles;
    }

    /**
     * Find and display Magic Links
     * The magic links must have the $show_app_tile variable set to true.
     * @param $section
     * @param $post_type
     * @return void
     */
    public function dt_details_additional_section( $section, $post_type ){
        if ( $section === "apps" ){
            $magic_link_apps = dt_get_registered_types();
            foreach ( $magic_link_apps as $app_root => $app_types ){
                foreach ( $app_types as $app_type => $app_value ){
                    if ( $app_value["post_type"] === $post_type && isset( $app_value["show_app_tile"] ) && $app_value["show_app_tile"] === true ){
                        $this->add_app_row( $post_type, $app_value );
                    }
                }
            }
        }
    }

    /**
     * Build the buttons for each Magic Link App
     * Buttons:
     *  - View. Opens a modal displaying the magic link content
     *  - Copy. Copies the magic link url to the clipboard
     *  - Send. Send the magic link via email
     *  - QR. Display a QR code of the magic link
     *  - Reset. Reset the magic and generate a new url
     * @param $post_type
     * @param $app
     * @return void
     */
    private function add_app_row( $post_type, $app ){
        if ( !isset( $app['meta_key'], $app['label'], $app['type'], $app['root'] ) ){
            return;
        }

        $record = DT_Posts::get_post( $post_type, get_the_ID() );
        $meta_key = $app['meta_key'];
        if ( isset( $record[$meta_key] ) ) {
            $key = $record[$meta_key];
        } else {
            $key = dt_create_unique_key();
            update_post_meta( get_the_ID(), $meta_key, $key );
        }
        ?>
        <div class="section-subheader"><?php echo esc_html( $app['label'] ) ?></div>
        <div class="section-app-links <?php echo esc_attr( $meta_key ); ?>">
            <a type="button" class="empty-select-button select-button small button view"><img class="dt-icon" alt="show" src="<?php echo esc_url( get_template_directory_uri() . '/dt-assets/images/visibility.svg' ) ?>" /></a>
            <a type="button" class="empty-select-button select-button small button copy_to_clipboard"
               data-value="<?php echo esc_url( site_url() . '/' . $app['root'] . '/' . $app['type'] . '/' . $key ) ?>">
                <img class="dt-icon" alt="copy" src="<?php echo esc_url( get_template_directory_uri() . '/dt-assets/images/duplicate.svg' ) ?>"/>
            </a>
            <a type="button" class="empty-select-button select-button small button send"><img class="dt-icon" alt="send" src="<?php echo esc_url( get_template_directory_uri() . '/dt-assets/images/send.svg' ) ?>" /></a>
            <a type="button" class="empty-select-button select-button small button qr"><img class="dt-icon" alt="qrcode" src="<?php echo esc_url( get_template_directory_uri() . '/dt-assets/images/qrcode-solid.svg' ) ?>" /></a>
            <a type="button" class="empty-select-button select-button small button reset"><img class="dt-icon" alt="undo" src="<?php echo esc_url( get_template_directory_uri() . '/dt-assets/images/undo.svg' ) ?>" /></a>
        </div>
        <script>
            jQuery(document).ready(function($){
                if ( typeof window.app_key === 'undefined' ){
                    window.app_key = []
                }
                if ( typeof window.app_url === 'undefined' ){
                    window.app_url = []
                }
                window.app_key['<?php echo esc_attr( $meta_key ) ?>'] = '<?php echo esc_attr( $key ) ?>'
                window.app_url['<?php echo esc_attr( $meta_key ) ?>'] = '<?php echo esc_url( site_url() . '/' . $app['root'] . '/' .$app['type'] . '/' ) ?>'

                $('.<?php echo esc_attr( $meta_key ); ?>.select-button.button.copy_to_clipboard').data('value', `${window.app_url['<?php echo esc_attr( $meta_key ) ?>']}${window.app_key['<?php echo esc_attr( $meta_key ) ?>']}`)
                $('.section-app-links.<?php echo esc_attr( $meta_key ); ?> .view').on('click', function(e){
                    $('#modal-large-title').empty().html(`<h3 class="section-header"><?php echo esc_html( $app['label'] )  ?></h3><span class="small-text"><?php echo esc_html( $app['description'] ?? '' ) ?></span><hr>`)
                    $('#modal-large-content').empty().html(`<iframe src="${window.app_url['<?php echo esc_attr( $meta_key ) ?>']}${window.app_key['<?php echo esc_attr( $meta_key ) ?>']}" style="width:100%;height: ${window.innerHeight - 170}px;border:1px solid lightgrey;"></iframe>`)
                    $('#modal-large').foundation('open')
                })
                $('.section-app-links.<?php echo esc_attr( $meta_key ); ?> .send').on('click', function(e){
                    $('#modal-small-title').empty().html(`<h3 class="section-header"><?php echo esc_html( $app['label'] )  ?></h3><span class="small-text"><?php echo esc_html__( 'Send a link via email through the system.', 'disciple_tools' ) ?></span><input type="text" class="email <?php echo esc_attr( $meta_key ); ?>" placeholder="<?php echo esc_attr__( 'Add email address', 'disciple_tools' )?>"/><hr>`)
                    $('#modal-small-content').empty().html(`<div class="grid-x"><div class="cell"><input type="text" class="note <?php echo esc_attr( $meta_key ); ?>" placeholder="<?php echo esc_attr__( 'Add a note', 'disciple_tools' )?>" /><br><button type="button" class="button <?php echo esc_attr( $meta_key ); ?>"><?php echo esc_html__( 'Send email with link', 'disciple_tools' ) ?> <span class="<?php echo esc_attr( $meta_key ); ?> loading-spinner"></span></button></div></div>`)
                    $('.button.<?php echo esc_attr( $meta_key ); ?>').prop('disabled', true);
                    $('#modal-small').foundation('open')

                    <?php
                    if ( isset( $record['contact_email'][0] ) ) {
                        $email = $record['contact_email'][0]['value'];
                        ?>
                    $('.email.<?php echo esc_attr( $meta_key ); ?>').val('<?php echo esc_attr( $email ); ?>');
                    $('.button.<?php echo esc_attr( $meta_key ); ?>').prop('disabled', !is_email_format_valid($('.email.<?php echo esc_attr( $meta_key ); ?>').val()));
                        <?php
                    }
                    ?>

                    $('.email.<?php echo esc_attr( $meta_key ); ?>').on('keyup', function () {
                        let email = $('.email.<?php echo esc_attr( $meta_key ); ?>').val();
                        $('.button.<?php echo esc_attr( $meta_key ); ?>').prop('disabled', !is_email_format_valid(email.trim()));
                    });

                    $('.button.<?php echo esc_attr( $meta_key ); ?>').on('click', function(e){
                        $('.<?php echo esc_attr( $meta_key ); ?>.loading-spinner').addClass('active')
                        let email = $('.email.<?php echo esc_attr( $meta_key ); ?>').val();
                        let note = $('.note.<?php echo esc_attr( $meta_key ); ?>').val();
                        makeRequest('POST', window.detailsSettings.post_type + '/email_magic', { root: '<?php echo esc_attr( $app['root'] ); ?>', type: '<?php echo esc_attr( $app['type'] ); ?>', email: email, note: note, post_ids: [ window.detailsSettings.post_id ] } )
                        .done( data => {
                            $('.<?php echo esc_attr( $meta_key ); ?>.loading-spinner').removeClass('active')
                            $('#modal-small').foundation('close')
                        })
                    })
                })

                function is_email_format_valid(email) {
                    return new RegExp('^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\\.[A-Za-z]{2,6}$').test(window.lodash.escape(email));
                }

                $('.section-app-links.<?php echo esc_attr( $meta_key ); ?> .qr').on('click', function(e){
                    $('#modal-small-title').empty().html(`<h3 class="section-header"><?php echo esc_html( $app['label'] )  ?></h3><span class="small-text"><?php echo esc_html__( 'QR codes are useful for passing the coaching links to mobile devices.', 'disciple_tools' ) ?></span><hr>`)
                    $('#modal-small-content').empty().html(`<div class="grid-x"><div class="cell center"><img src="https://api.qrserver.com/v1/create-qr-code/?size=400x400&data=${window.app_url['<?php echo esc_attr( $meta_key ) ?>']}${window.app_key['<?php echo esc_attr( $meta_key ) ?>']}" style="width: 100%;max-width:400px;" /></div></div>`)
                    $('#modal-small').foundation('open')
                })
                $('.section-app-links.<?php echo esc_attr( $meta_key ); ?> .reset').on('click', function(e){
                    $('#modal-small-title').empty().html(`<h3 class="section-header"><?php echo esc_html( $app['label'] )  ?></h3><span class="small-text"><?php echo esc_html__( 'Reset the security code. No data is removed. Only access. The previous link will be disabled and another one created.', 'disciple_tools' ) ?></span><hr>`)
                    $('#modal-small-content').empty().html(`<button type="button" class="button <?php echo esc_attr( $meta_key ); ?> delete-and-reset"><?php echo esc_html__( 'Delete and replace the app link', 'disciple_tools' ) ?>  <span class="<?php echo esc_attr( $meta_key ); ?> loading-spinner"></span></button>`)
                    $('#modal-small').foundation('open')
                    $('.button.<?php echo esc_attr( $meta_key ); ?>.delete-and-reset').on('click', function(e){
                        $('.button.<?php echo esc_attr( $meta_key ); ?>.delete-and-reset').prop('disable', true)
                        $('.<?php echo esc_attr( $meta_key ); ?>.loading-spinner').addClass('active')
                        window.API.update_post('<?php echo esc_attr( $post_type ); ?>', <?php echo esc_attr( get_the_ID() ); ?>, { ['<?php echo esc_attr( $meta_key ); ?>']: window.sha256( Date.now() ) })
                        .done( newPost => {
                            $('#modal-small').foundation('close')
                            window.app_key['<?php echo esc_attr( $meta_key ) ?>'] = newPost['<?php echo esc_attr( $meta_key ) ?>']
                            $('.section-app-links.<?php echo esc_attr( $meta_key ); ?> .select-button.button.copy_to_clipboard').data('value', `${window.app_url['<?php echo esc_attr( $meta_key ) ?>']}${window.app_key['<?php echo esc_attr( $meta_key ) ?>']}`)
                        })
                    })
                })
            })
        </script>
        <?php
    }
}
new DT_Magic_URL_Setup();
