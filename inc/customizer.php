<?php
/**
 * dmmcrm Theme Customizer
 *
 * @package dmmcrm
 */

/**
 * Add postMessage support for site title and description for the Theme Customizer.
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 */
function dmmcrm_customize_register( $wp_customize ) {
    $wp_customize->remove_control( 'header_image' );
	$wp_customize->get_setting( 'blogname' )->transport         = 'postMessage';
	$wp_customize->get_setting( 'blogdescription' )->transport  = 'postMessage';
	$wp_customize->add_section( 'dmmcrm_logo_section' , array(
    'title'       => esc_attr( 'Logo', 'dmmcrm' ),
    'priority'    => 30,
    'description' => esc_attr('Upload a logo to replace the default site name and description in the more', 'dmmcrm' ),
    ) );
    
    $wp_customize->add_setting( 'dmmcrm_logo',
        'sanitize_callback' == 'esc_url_raw'
    );

    $wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'dmmcrm_logo', array(
        'label'    => esc_attr( 'Logo', 'dmmcrm' ),
        'section'  => 'dmmcrm_logo_section',
        'settings' => 'dmmcrm_logo',
        'sanitize_callback' => 'esc_url_raw',
    ) ) );

    /* more link */
    $wp_customize->add_section(
    'more_options',
    array(
        'title' => esc_attr('More Link Options', 'dmmcrm'),
        'description' => esc_attr('Customize your read more link', 'dmmcrm' ),
        'priority' => 1,
    )
    );

    $wp_customize->add_setting(
        'ss_excerpt_type',
        array(
            'default' => 'option2',
            'sanitize_callback' => 'dmmcrm_sanitize_choices',
        )
    );

    $wp_customize->add_control(
        'ss_excerpt_type',
        array(
            'type' => 'select',
            'label' => esc_attr('Excerpt type', 'dmmcrm' ),
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
            'sanitize_callback' => 'dmmcrm_sanitize_choices',
        )
    );

    $wp_customize->add_control(
        'ss_more_type',
        array(
            'type' => 'select',
            'label' => esc_attr('Read More Type', 'dmmcrm' ),
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
            'label' => esc_attr('Read More Text', 'dmmcrm' ),
            'section' => 'more_options',
        )
    );


    //more position
    $wp_customize->add_setting(
        'ss_more_position',
        array(
            'default' => 'option1',
            'sanitize_callback' => 'dmmcrm_sanitize_choices',

        )
    );

    $wp_customize->add_control(
        'ss_more_position',
        array(
            'type' => 'select',
            'label' => esc_attr('Read More Position', 'dmmcrm' ),
            'description' => esc_attr('Only works if read more type is button', 'dmmcrm' ),
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
            'sanitize_callback' => 'dmmcrm_sanitize_choices',
        )
    );

    $wp_customize->add_control(
        'ss_more_button',
        array(
            'type' => 'select',
            'label' => esc_attr('Read More Button Style', 'dmmcrm' ),
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
            'label' => esc_attr( 'Button Background Color', 'dmmcrm' ),
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
            'label' => esc_attr( 'Button Text Color', 'dmmcrm' ),
            'section' => 'more_options',
    ) ) );

    // google fonts
    require_once( dirname( __FILE__ ) . '/google-fonts/fonts.php' );


    $wp_customize->add_section( 'dmmcrm_google_fonts', array(
        'title'    => __( 'Fonts', 'dmmcrm' ),
        'priority' => 50,
    ) );

    $wp_customize->add_setting( 'dmmcrm_google_fonts_heading_font', array(
        'default'           => 'none',
        'type'              => 'theme_mod',
        'capability'        => 'edit_theme_options',
        'transport'         => 'postMessage',
        'sanitize_callback' => 'sanitize_text_field',
    ) );

    $wp_customize->add_control( 'dmmcrm_google_fonts_heading_font', array(
        'label'    => __( 'Header Font', 'dmmcrm' ),
        'section'  => 'dmmcrm_google_fonts',
        'settings' => 'dmmcrm_google_fonts_heading_font',
        'type'     => 'select',
        'choices'  => $font_choices,
    ) );

    $wp_customize->add_setting( 'dmmcrm_google_fonts_body_font', array(
        'default'           => 'none',
        'type'              => 'theme_mod',
        'capability'        => 'edit_theme_options',
        'transport'         => 'postMessage',
        'sanitize_callback' => 'sanitize_text_field',
    ) );

    $wp_customize->add_control( 'dmmcrm_google_fonts_body_font', array(
        'label'    => __( 'Body Font', 'dmmcrm' ),
        'section'  => 'dmmcrm_google_fonts',
        'settings' => 'dmmcrm_google_fonts_body_font',
        'type'     => 'select',
        'choices'  => $font_choices,
    ) );
    // end google fonts



}
add_action( 'customize_register', 'dmmcrm_customize_register' );

/**
 * Binds JS handlers to make Theme Customizer preview reload changes asynchronously.
 */
function dmmcrm_customize_preview_js() {
	wp_enqueue_script( 'dmmcrm_customizer', get_template_directory_uri() . '/js/customizer.js', array( 'customize-preview' ), '20130508', true );
}
add_action( 'customize_preview_init', 'dmmcrm_customize_preview_js' );


function dmmcrm_sanitize_choices( $input, $setting ) {
    global $wp_customize;
 
    $control = $wp_customize->get_control( $setting->id );
 
    if ( array_key_exists( $input, $control->choices ) ) {
        return $input;
    } else {
        return $setting->default;
    }
}
