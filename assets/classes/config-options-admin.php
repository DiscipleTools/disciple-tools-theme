<?php
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * Disciple_Tools_Admin Class
 *
 * @class Disciple_Tools_Admin
 * @version    1.0.0
 * @since 0.1
 * @package    Disciple_Tools
 * @author Chasm.Solutions & Kingdom.Training
 */
final class Disciple_Tools_Theme_Admin {
    /**
     * The single instance of Disciple_Tools_Admin.
     * @var     object
     * @access  private
     * @since  0.1
     */
    private static $_instance = null;

    /**
     * The string containing the dynamically generated hook token.
     * @var     string
     * @access  private
     * @since   0.1
     */
    private $_hook;

    protected $token = 'disciple_tools';

    /**
     * Constructor function.
     * @access  public
     * @since   0.1
     */
    public function __construct () {
        // Register the settings with WordPress.
        add_action( 'admin_init', array( $this, 'register_settings' ) );

        // Register the settings screen within WordPress.
        add_action( 'admin_menu', array( $this, 'register_settings_screen' ) );

    } // End __construct()

    /**
     * Main Disciple_Tools_Theme_Admin Instance
     *
     * Ensures only one instance of Disciple_Tools_Theme_Admin is loaded or can be loaded.
     *
     * @since 0.1
     * @static
     * @return Disciple_Tools_Theme_Admin instance
     */
    public static function instance () {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()

    /**
     * Register the admin screen.
     * @access  public
     * @since   0.1
     * @return  void
     */
    public function register_settings_screen () {
        $this->_hook = add_submenu_page( 'options-general.php', __( 'Theme Options (DT)', 'disciple_tools' ), __( 'Theme (DT)', 'disciple_tools' ), 'manage_options', 'disciple_tools_theme_options', array( $this, 'settings_screen' ) );
    } // End register_settings_screen()

    /**
     * Output the markup for the settings screen.
     * @access  public
     * @since   0.1
     * @return  void
     */
    public function settings_screen () {
        global $title;
        $sections = $this->get_settings_sections();
        $tab = $this->_get_current_tab( $sections );
        ?>
        <div class="wrap dt-wrap">
            <?php
                echo $this->get_admin_header_html( $sections, $title );
            ?>
            <form action="options.php" method="post">
                <?php
                    settings_fields( 'dt-settings-' . $tab );
                    do_settings_sections( $this->token . '-' . $tab );
                    submit_button( __( 'Save Changes', 'disciple_tools' ) );
                ?>
            </form>
        </div><!--/.wrap-->
        <?php
    } // End settings_screen()

    /**
     * Register the settings within the Settings API.
     * @access  public
     * @since   0.1
     * @return  void
     */
    public function register_settings () {
        $sections = $this->get_settings_sections();
        if ( 0 < count( $sections ) ) {
            foreach ( $sections as $k => $v ) {
                register_setting( 'dt-settings-' . sanitize_title_with_dashes( $k ), $this->token . '-' . $k, array( $this, 'validate_settings' ) );
                add_settings_section( sanitize_title_with_dashes( $k ), $v, array( $this, 'render_settings' ), $this->token . '-' . $k, $k, $k );
            }
        }
    } // End register_settings()

    /**
     * Render the settings.
     * @access  public
     * @param  array $args arguments.
     * @since   0.1
     * @return  void
     */
    public function render_settings ( $args ) {
        $token = $args['id'];
        $fields = $this->get_settings_fields( $token );

        if ( 0 < count( $fields ) ) {
            foreach ( $fields as $k => $v ) {
                $args         = $v;
                $args['id'] = $k;

                add_settings_field( $k, $v['name'], array( $this, 'render_field' ), $this->token . '-' . $token , $v['section'], $args );
            }
        }
    } // End render_settings()

    /**
     * Validate the settings.
     * @access  public
     * @since   0.1
     * @param   array $input Inputted data.
     * @return  array        Validated data.
     */
    public function validate_settings ( $input ) {
        $sections = $this->get_settings_sections();
        $tab = $this->_get_current_tab( $sections );
        return $this->dt_validate_settings( $input, $tab );
    } // End validate_settings()

    /**
     * Return marked up HTML for the header tag on the settings screen.
     * @access  public
     * @since   0.1
     * @param   array  $sections Sections to scan through.
     * @param   string $title    Title to use, if only one section is present.
     * @return  string              The current tab key.
     */
    public function get_admin_header_html ( $sections, $title ) {
        $defaults = array(
                            'tag' => 'h2',
                            'atts' => array( 'class' => 'dt-wrapper' ),
                            'content' => $title
                        );

        $args = $this->_get_admin_header_data( $sections, $title );

        $args = wp_parse_args( $args, $defaults );

        $atts = '';
        if ( 0 < count( $args['atts'] ) ) {
            foreach ( $args['atts'] as $k => $v ) {
                $atts .= ' ' . esc_attr( $k ) . '="' . esc_attr( $v ) . '"';
            }
        }

        $response = '<' . esc_attr( $args['tag'] ) . $atts . '>' . $args['content'] . '</' . esc_attr( $args['tag'] ) . '>' . "\n";

        return $response;
    } // End get_admin_header_html()

    /**
     * Return the current tab key.
     * @access  private
     * @since   0.1
     * @param   array  $sections Sections to scan through for a section key.
     * @return  string              The current tab key.
     */
    private function _get_current_tab ( $sections = array() ) {
        if ( isset( $_GET['tab'] ) ) {
            $response = sanitize_title_with_dashes( $_GET['tab'] );
        } else {
            if ( is_array( $sections ) && ! empty( $sections ) ) {
                list( $first_section ) = array_keys( $sections );
                $response = $first_section;
            } else {
                $response = '';
            }
        }

        return $response;
    } // End _get_current_tab()

    /**
     * Return an array of data, used to construct the header tag.
     * @access  private
     * @since   0.1
     * @param   array  $sections Sections to scan through.
     * @param   string $title    Title to use, if only one section is present.
     * @return  array              An array of data with which to mark up the header HTML.
     */
    private function _get_admin_header_data ( $sections, $title ) {
        $response = array( 'tag' => 'h2', 'atts' => array( 'class' => 'dt-wrapper' ), 'content' => $title );

        if ( is_array( $sections ) && 1 < count( $sections ) ) {
            $response['content'] = '';
            $response['atts']['class'] = 'nav-tab-wrapper';

            $tab = $this->_get_current_tab( $sections );

            foreach ( $sections as $key => $value ) {
                $class = 'nav-tab';
                if ( $tab == $key ) {
                    $class .= ' nav-tab-active';
                }

                $response['content'] .= '<a href="' . admin_url( 'options-general.php?page=disciple_tools_theme_options&tab=' . sanitize_title_with_dashes( $key ) ) . '" class="' . esc_attr( $class ) . '">' . esc_html( $value ) . '</a>';
            }
        }

        return (array) apply_filters( 'dt-get-admin-header-data', $response );
    } // End _get_admin_header_data()

    /**
     * Validate the settings.
     * @access  public
     * @since   0.1
     * @param   array $input Inputted data.
     * @param   string $section field section.
     * @return  array        Validated data.
     */
    public function dt_validate_settings ( $input, $section ) {
        if ( is_array( $input ) && 0 < count( $input ) ) {
            $fields = $this->get_settings_fields( $section );

            foreach ( $input as $k => $v ) {
                if ( ! isset( $fields[$k] ) ) {
                    continue;
                }

                // Determine if a method is available for validating this field.
                $method = 'validate_field_' . $fields[$k]['type'];

                if ( ! method_exists( $this, $method ) ) {
                    if ( true === (bool) apply_filters( 'dt-validate-field-' . $fields[$k]['type'] . '_use_default', true ) ) {
                        $method = 'validate_field_text';
                    } else {
                        $method = '';
                    }
                }

                // If we have an internal method for validation, filter and apply it.
                if ( '' != $method ) {
                    add_filter( 'dt-validate-field-' . $fields[$k]['type'], array( $this, $method ) );
                }

                $method_output = apply_filters( 'dt-validate-field-' . $fields[$k]['type'], $v, $fields[$k] );

                if ( ! is_wp_error( $method_output ) ) {
                    $input[$k] = $method_output;
                }
            }
        }
        return $input;
    } // End validate_settings()

    /**
     * Validate the given data, assuming it is from a text input field.
     * @access  public
     * @since   6.0.0
     * @return  void
     */
    public function validate_field_text ( $v ) {
        return (string) wp_kses_post( $v );
    } // End validate_field_text()

    /**
     * Validate the given data, assuming it is from a textarea field.
     * @access  public
     * @since   6.0.0
     * @return  void
     */
    public function validate_field_textarea ( $v ) {
        // Allow iframe, object and embed tags in textarea fields.
        $allowed             = wp_kses_allowed_html( 'post' );
        $allowed['iframe']     = array(
            'src'         => true,
            'width'     => true,
            'height'     => true,
            'id'         => true,
            'class'     => true,
            'name'         => true
        );
        $allowed['object']     = array(
            'src'         => true,
            'width'     => true,
            'height'     => true,
            'id'         => true,
            'class'     => true,
            'name'         => true
        );
        $allowed['embed']     = array(
            'src'         => true,
            'width'     => true,
            'height'     => true,
            'id'         => true,
            'class'     => true,
            'name'         => true
        );

        return wp_kses( $v, $allowed );
    } // End validate_field_textarea()

    /**
     * Validate the given data, assuming it is from a checkbox input field.
     * @access public
     * @since  6.0.0
     * @param  string $v
     * @return string
     */
    public function validate_field_checkbox ( $v ) {
        if ( 'true' != $v ) {
            return 'false';
        } else {
            return 'true';
        }
    } // End validate_field_checkbox()

    /**
     * Validate the given data, assuming it is from a URL field.
     * @access public
     * @since  6.0.0
     * @param  string $v
     * @return string
     */
    public function validate_field_url ( $v ) {
        return trim( esc_url( $v ) );
    } // End validate_field_url()

    /**
     * Render a field of a given type.
     * @access  public
     * @since   0.1
     * @param   array $args The field parameters.
     * @return  void
     */
    public function render_field ( $args ) {
        $html = '';
        if ( ! in_array( $args['type'], $this->get_supported_fields() ) ) { return ''; // Supported field type sanity check.
        }

        // Make sure we have some kind of default, if the key isn't set.
        if ( ! isset( $args['default'] ) ) {
            $args['default'] = '';
        }

        $method = 'render_field_' . $args['type'];

        if ( ! method_exists( $this, $method ) ) {
            $method = 'render_field_text';
        }

        // Construct the key.
        $key                 = $this->token . '-' . $args['section'] . '[' . $args['id'] . ']';
        $method_output         = $this->$method( $key, $args );

        if ( ! is_wp_error( $method_output ) ) {
            $html .= $method_output;
        }

        // Output the description, if the current field allows it.
        if ( isset( $args['type'] ) && ! in_array( $args['type'], (array) apply_filters( 'dt-no-description-fields', array( 'checkbox' ) ) ) ) {
            if ( isset( $args['description'] ) ) {
                $description = '<p class="description">' . wp_kses_post( $args['description'] ) . '</p>' . "\n";
                if ( in_array( $args['type'], (array) apply_filters( 'dt-new-line-description-fields', array( 'textarea', 'select' ) ) ) ) {
                    $description = wpautop( $description );
                }
                $html .= $description;
            }
        }

        echo $html;
    } // End render_field()

    /**
     * Retrieve the settings fields details
     * @access  public
     * @since   0.1
     * @return  array        Settings fields.
     */
    public function get_settings_sections () {
        $settings_sections = array();

        $settings_sections['theme_main'] = __( 'General', 'disciple_tools' );
        $settings_sections['theme_second'] = __( 'Second Tab', 'disciple_tools' );
        // Add your new sections below here.
        // Admin tabs will be created for each section.
        // Don't forget to add fields for the section in the get_settings_fields() function below

        return (array) apply_filters( 'disciple-tools-theme-settings-sections', $settings_sections );
    } // End get_settings_sections()

    /**
     * Retrieve the settings fields details
     * @access  public
     * @param  string $section field section.
     * @since   0.1
     * @return  array        Settings fields.
     */
    public function get_settings_fields ( $section ) {
        $settings_fields = array();
        // Declare the default settings fields.

        switch ( $section ) {
            case 'theme_main':

                $settings_fields['theme_profile_page'] = array(
                    'name' => __( 'Profile Page', 'disciple_tools' ),
                    'type' => 'text',
                    'default' => 'Profile',
                    'section' => 'theme_main',
                    'description' => '',
                );
                $settings_fields['theme_reports_page'] = array(
                    'name' => __( 'Reports Page', 'disciple_tools' ),
                    'type' => 'text',
                    'default' => 'Reports',
                    'section' => 'theme_main',
                    'description' => '',
                );
                $settings_fields['theme_about_us_page'] = array(
                    'name' => __( 'About Us Page', 'disciple_tools' ),
                    'type' => 'text',
                    'default' => 'About Us',
                    'section' => 'theme_main',
                    'description' => '',
                );

                break;

            case 'theme_second':

                $settings_fields['theme_field'] = array(
                    'name' => __( 'Field', 'disciple_tools' ),
                    'type' => 'text',
                    'default' => '',
                    'section' => 'theme_second',
                    'description' => '',
                );


                break;
            default:
                # code...
                break;
        }

        return (array) apply_filters( 'disciple-tools-theme-settings-fields', $settings_fields, $section );
    } // End get_settings_fields()

    /**
     * Render HTML markup for the "text" field type.
     * @access  protected
     * @since   6.0.0
     * @param   string $key  The unique ID of this field.
     * @param   array $args  Arguments used to construct this field.
     * @return  string       HTML markup for the field.
     */
    protected function render_field_text ( $key, $args ) {
        $html = '<input id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" size="40" type="text" value="' . esc_attr( $this->get_value( $args['id'], $args['default'], $args['section'] ) ) . '" />' . "\n";
        return $html;
    } // End render_field_text()

    /**
     * Render HTML markup for the "radio" field type.
     * @access  protected
     * @since   6.0.0
     * @param   string $key  The unique ID of this field.
     * @param   array $args  Arguments used to construct this field.
     * @return  string       HTML markup for the field.
     */
    protected function render_field_radio ( $key, $args ) {
        $html = '';
        if ( isset( $args['options'] ) && ( 0 < count( (array) $args['options'] ) ) ) {
            $html = '';
            foreach ( $args['options'] as $k => $v ) {
                $html .= '<input type="radio" name="' . esc_attr( $key ) . '" value="' . esc_attr( $k ) . '"' . checked( esc_attr( $this->get_value( $args['id'], $args['default'], $args['section'] ) ), $k, false ) . ' /> ' . esc_html( $v ) . '<br />' . "\n";
            }
        }
        return $html;
    } // End render_field_radio()

    /**
     * Render HTML markup for the "textarea" field type.
     * @access  protected
     * @since   6.0.0
     * @param   string $key  The unique ID of this field.
     * @param   array $args  Arguments used to construct this field.
     * @return  string       HTML markup for the field.
     */
    protected function render_field_textarea ( $key, $args ) {
        // Explore how best to escape this data, as esc_textarea() strips HTML tags, it seems.
        $html = '<textarea id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" cols="42" rows="5">' . $this->get_value( $args['id'], $args['default'], $args['section'] ) . '</textarea>' . "\n";
        return $html;
    } // End render_field_textarea()rist

    /**
     * Render HTML markup for the "checkbox" field type.
     * @access  protected
     * @since   6.0.0
     * @param   string $key  The unique ID of this field.
     * @param   array $args  Arguments used to construct this field.
     * @return  string       HTML markup for the field.
     */
    protected function render_field_checkbox ( $key, $args ) {
        $has_description = false;
        $html = '';
        if ( isset( $args['description'] ) ) {
            $has_description = true;
            $html .= '<label for="' . esc_attr( $key ) . '">' . "\n";
        }
        $html .= '<input id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" type="checkbox" value="true"' . checked( esc_attr( $this->get_value( $args['id'], $args['default'], $args['section'] ) ), 'true', false ) . ' />' . "\n";
        if ( $has_description ) {
            $html .= wp_kses_post( $args['description'] ) . '</label>' . "\n";
        }
        return $html;
    } // End render_field_checkbox()

    /**
     * Render HTML markup for the "select2" field type.
     * @access  protected
     * @since   6.0.0
     * @param   string $key  The unique ID of this field.
     * @param   array $args  Arguments used to construct this field.
     * @return  string       HTML markup for the field.
     */
    protected function render_field_select ( $key, $args ) {
        $this->_has_select = true;

        $html = '';
        if ( isset( $args['options'] ) && ( 0 < count( (array) $args['options'] ) ) ) {
            $html .= '<select id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '">' . "\n";
            foreach ( $args['options'] as $k => $v ) {
                $html .= '<option value="' . esc_attr( $k ) . '"' . selected( esc_attr( $this->get_value( $args['id'], $args['default'], $args['section'] ) ), $k, false ) . '>' . esc_html( $v ) . '</option>' . "\n";
            }
            $html .= '</select>' . "\n";
        }
        return $html;
    } // End render_field_select()

    /**
     * Render HTML markup for the "select_taxonomy" field type.
     * @access  protected
     * @since   6.0.0
     * @param   string $key  The unique ID of this field.
     * @param   array $args  Arguments used to construct this field.
     * @return  string       HTML markup for the field.
     */
    protected function render_field_select_taxonomy ( $key, $args ) {
        $this->_has_select = true;

        $defaults = array(
            'show_option_all'    => '',
            'show_option_none'   => '',
            'orderby'            => 'ID',
            'order'              => 'ASC',
            'show_count'         => 0,
            'hide_empty'         => 1,
            'child_of'           => 0,
            'exclude'            => '',
            'selected'           => $this->get_value( $args['id'], $args['default'], $args['section'] ),
            'hierarchical'       => 1,
            'class'              => 'postform',
            'depth'              => 0,
            'tab_index'          => 0,
            'taxonomy'           => 'category',
            'hide_if_empty'      => false,
            'walker'             => ''
        );

        if ( ! isset( $args['options'] ) ) {
            $args['options'] = array();
        }

        $args['options']             = wp_parse_args( $args['options'], $defaults );
        $args['options']['echo']     = false;
        $args['options']['name']     = esc_attr( $key );
        $args['options']['id']         = esc_attr( $key );

        $html = '';
        $html .= wp_dropdown_categories( $args['options'] );

        return $html;
    } // End render_field_select_taxonomy()

    /**
     * Return an array of field types expecting an array value returned.
     * @access public
     * @since  0.1
     * @return array
     */
    public function get_array_field_types () {
        return array();
    } // End get_array_field_types()

    /**
     * Return an array of field types where no label/header is to be displayed.
     * @access protected
     * @since  0.1
     * @return array
     */
    protected function get_no_label_field_types () {
        return array( 'info' );
    } // End get_no_label_field_types()

    /**
     * Return a filtered array of supported field types.
     * @access  public
     * @since   0.1
     * @return  array Supported field type keys.
     */
    public function get_supported_fields () {
        return (array) apply_filters( 'dt-supported-fields', array( 'text', 'checkbox', 'radio', 'textarea', 'select', 'select_taxonomy' ) );
    } // End get_supported_fields()

    /**
     * Return a value, using a desired retrieval method.
     * @access  public
     * @param  string $key option key.
     * @param  string $default default value.
     * @param  string $section field section.
     * @since   0.1
     * @return  mixed Returned value.
     */
    public function get_value ( $key, $default, $section ) {
        $values = get_option( $this->token . '-' . $section, array() );
        if ( is_array( $values ) && isset( $values[$key] ) ) {
            $response = $values[$key];
        } else {
            $response = $default;
        }

        return $response;
    } // End get_value()

    /**
     * Return all settings keys.
     * @access  public
     * @param  string $section field section.
     * @since   0.1
     * @return  mixed Returned value.
     */
    public function get_settings ( $section = '' ) {
        $response = false;

        $sections = array_keys( (array) $this->get_settings_sections() );

        if ( in_array( $section, $sections ) ) {
            $sections = array( $section );
        }

        if ( 0 < count( $sections ) ) {
            foreach ( $sections as $k => $v ) {
                $fields = $this->get_settings_fields( $k );
                $values = get_option( $this->token . '-' . $k, array() );

                if ( is_array( $fields ) && 0 < count( $fields ) ) {
                    foreach ( $fields as $i => $j ) {
                        // If we have a value stored, use it.
                        if ( isset( $values[$i] ) ) {
                            $response[$i] = $values[$i];
                        } else {
                            // Otherwise, check for a default value. If we have one, use it. Otherwise, return an empty string.
                            if ( isset( $fields[$i]['default'] ) ) {
                                $response[$i] = $fields[$i]['default'];
                            } else {
                                $response[$i] = '';
                            }
                        }
                    }
                }
            }
        }

        return $response;
    } // End get_settings()

} // End Class

