<?php
/**
 * Product tour for new users
 */

if ( ! get_user_meta( get_current_user_id(), 'dt_product_tour') ) { // test if already been through product tour

    function dt_product_tour() {
        global $post;
        if ( is_archive() && $post->post_type == 'contacts' ) {
            ?>
            <script>
                jQuery(document).ready(function () {
                    let content = jQuery('.off-canvas-content')

                    content.append(`<div id="dt-demo-tour-modal" class="reveal large" data-reveal>
                                        <div id="demo-tour">
                                            <h2>Take a Tour!</h2>
                                            <hr>

                                            <p><button type="button" class="button" onclick="take_tour()">Take the Tour!</button>
                                            <button type="button" class="button hollow"
                                            onclick="skip_tour()">
                                            Skip
                                            </button> </p>
                                        </div>
                                        <button class="close-button" data-close aria-label="Close Accessible Modal" type="button">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                `)
                    let div = jQuery('#dt-demo-tour-modal');
                    new Foundation.Reveal(div);
                    div.foundation('open');
                })

                function take_tour() {
                    console.log('take tour')
                }

                function skip_tour() {
                    return jQuery.ajax({
                        type: "GET",
                        contentType: "application/json; charset=utf-8",
                        url: "<?php echo get_site_url(null, '/wp-json/dt/v1/users/disable_product_tour' ) ?>",
                        beforeSend: function(xhr) {
                            xhr.setRequestHeader('X-WP-Nonce', '<?php echo wp_create_nonce('wp_rest') ?>' );
                        },
                    })
                        .done(function (data) {
                            jQuery('#dt-demo-tour-modal').foundation('close')
                        })
                        .fail(function (err) {
                            console.log("error");
                            console.log(err);
                            jQuery("#errors").append(err.responseText);
                        })
                }
            </script>
            <?php
        }
    }
    add_action( 'wp_head', 'dt_product_tour' );
}
