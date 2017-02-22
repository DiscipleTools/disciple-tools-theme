<?php
/**
 * drm Theme Customizer
 *
 * @package drm
 */

/**
 * Add postMessage support for site title and description for the Theme Customizer.
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 */
function drm_customize_register( $wp_customize ) {
    $wp_customize->remove_control( 'header_image' );
	$wp_customize->get_setting( 'blogname' )->transport         = 'postMessage';
	$wp_customize->get_setting( 'blogdescription' )->transport  = 'postMessage';
	$wp_customize->add_section( 'drm_logo_section' , array(
    'title'       => esc_attr( 'Logo', 'drm' ),
    'priority'    => 30,
    'description' => esc_attr('Upload a logo to replace the default site name and description in the more', 'drm' ),
    ) );
    
    $wp_customize->add_setting( 'drm_logo',
        'sanitize_callback' == 'esc_url_raw'
    );

    $wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'drm_logo', array(
        'label'    => esc_attr( 'Logo', 'drm' ),
        'section'  => 'drm_logo_section',
        'settings' => 'drm_logo',
        'sanitize_callback' => 'esc_url_raw',
    ) ) );

    /* more link */
    $wp_customize->add_section(
    'more_options',
    array(
        'title' => esc_attr('More Link Options', 'drm'),
        'description' => esc_attr('Customize your read more link', 'drm' ),
        'priority' => 1,
    )
    );

    $wp_customize->add_setting(
        'ss_excerpt_type',
        array(
            'default' => 'option2',
            'sanitize_callback' => 'drm_sanitize_choices',
        )
    );

    $wp_customize->add_control(
        'ss_excerpt_type',
        array(
            'type' => 'select',
            'label' => esc_attr('Excerpt type', 'drm' ),
            'section' => 'more_options',
            'choices' => array(
                'option1' => 'More Tag',
                'option2' => 'Excerpt',
            ),
        )
    );

    //more type
    $wp_customize->add_setting(
        'ss_more_type',
        array(
            'default' => 'option1',
            'sanitize_callback' => 'drm_sanitize_choices',
        )
    );

    $wp_customize->add_control(
        'ss_more_type',
        array(
            'type' => 'select',
            'label' => esc_attr('Read More Type', 'drm' ),
            'section' => 'more_options',
            'choices' => array(
                'option1' => 'None',
                'option2' => 'Text',
                'option3' => 'Text + Button',
            ),
        )
    );

    //more type - text
    $wp_customize->add_setting(
        'ss_more_text',
        array(
            'sanitize_callback' => 'esc_attr',
            'default' => 'Read More &raquo;',
        )
    );

    $wp_customize->add_control(
        'ss_more_text',
        array(
            'label' => esc_attr('Read More Text', 'drm' ),
            'section' => 'more_options',
        )
    );


    //more position
    $wp_customize->add_setting(
        'ss_more_position',
        array(
            'default' => 'option1',
            'sanitize_callback' => 'drm_sanitize_choices',

        )
    );

    $wp_customize->add_control(
        'ss_more_position',
        array(
            'type' => 'select',
            'label' => esc_attr('Read More Position', 'drm' ),
            'description' => esc_attr('Only works if read more type is button', 'drm' ),
            'section' => 'more_options',
            'choices' => array(
                'left' => 'Left',
                'right' => 'Right',
            ),
        )
    );


    //more type - text + button
    $wp_customize->add_setting(
        'ss_more_button',
        array(
            'default' => 'option1',
            'sanitize_callback' => 'drm_sanitize_choices',
        )
    );

    $wp_customize->add_control(
        'ss_more_button',
        array(
            'type' => 'select',
            'label' => esc_attr('Read More Button Style', 'drm' ),
            'section' => 'more_options',
            'choices' => array(
                'option1' => 'Sharp Edges',
                'option2' => 'Rounded Corners',
            ),
        )
    );

    //background color
    $wp_customize->add_setting(
        'ss_button_bg',
        array(
            'default' => '#c7c7c7',
            'sanitize_callback' => 'sanitize_hex_color',
        )
    );


    $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 
        'ss_button_bg', 
        array(
            'label' => esc_attr( 'Button Background Color', 'drm' ),
            'section' => 'more_options',
    ) ) );


    //text color
    $wp_customize->add_setting(
        'ss_text_color',
        array(
            'default' => '#000000',
            'sanitize_callback' => 'sanitize_hex_color',
        )
    );


    $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 
        'ss_text_color', 
        array(
            'label' => esc_attr( 'Button Text Color', 'drm' ),
            'section' => 'more_options',
    ) ) );

    // google fonts
    require_once( dirname( __FILE__ ) . '/google-fonts/fonts.php' );


    $wp_customize->add_section( 'drm_google_fonts', array(
        'title'    => __( 'Fonts', 'drm' ),
        'priority' => 50,
    ) );

    $wp_customize->add_setting( 'drm_google_fonts_heading_font', array(
        'default'           => 'none',
        'type'              => 'theme_mod',
        'capability'        => 'edit_theme_options',
        'transport'         => 'postMessage',
        'sanitize_callback' => 'sanitize_text_field',
    ) );

    $wp_customize->add_control( 'drm_google_fonts_heading_font', array(
        'label'    => __( 'Header Font', 'drm' ),
        'section'  => 'drm_google_fonts',
        'settings' => 'drm_google_fonts_heading_font',
        'type'     => 'select',
        'choices'  => $font_choices,
    ) );

    $wp_customize->add_setting( 'drm_google_fonts_body_font', array(
        'default'           => 'none',
        'type'              => 'theme_mod',
        'capability'        => 'edit_theme_options',
        'transport'         => 'postMessage',
        'sanitize_callback' => 'sanitize_text_field',
    ) );

    $wp_customize->add_control( 'drm_google_fonts_body_font', array(
        'label'    => __( 'Body Font', 'drm' ),
        'section'  => 'drm_google_fonts',
        'settings' => 'drm_google_fonts_body_font',
        'type'     => 'select',
        'choices'  => $font_choices,
    ) );
    // end google fonts



}
add_action( 'customize_register', 'drm_customize_register' );

/**
 * Binds JS handlers to make Theme Customizer preview reload changes asynchronously.
 */
function drm_customize_preview_js() {
	wp_enqueue_script( 'drm_customizer', get_template_directory_uri() . '/js/customizer.js', array( 'customize-preview' ), '20130508', true );
}
add_action( 'customize_preview_init', 'drm_customize_preview_js' );


function drm_sanitize_choices( $input, $setting ) {
    global $wp_customize;
 
    $control = $wp_customize->get_control( $setting->id );
 
    if ( array_key_exists( $input, $control->choices ) ) {
        return $input;
    } else {
        return $setting->default;
    }
}
