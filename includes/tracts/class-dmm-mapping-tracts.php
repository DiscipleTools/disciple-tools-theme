<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class DMM_Mapping_Tracts {
	
	/**
	 * The single instance of DMM_Mapping_Settings.
	 * @var 	object
	 * @access  private
	 * @since 	1.0.0
	 */
	private static $_instance = null;

	/**
	 * The main plugin object.
	 * @var 	object
	 * @access  public
	 * @since 	1.0.0
	 */
	public $parent = null;

	/**
	 * Prefix for plugin settings.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $base = '';

	/**
	 * Available settings for plugin.
	 * @var     array
	 * @access  public
	 * @since   1.0.0
	 */
	public $settings = array();

	public function __construct ( $parent ) {
		$this->parent = $parent;

		$this->base = 'dm_'; 

		// Initialise settings
		add_action( 'init', array( $this, 'init_settings' ), 11 );

		// Register plugin settings
		add_action( 'admin_init' , array( $this, 'register_settings' ) );
	}

	/**
	 * Initialise settings
	 * @return void
	 */
	public function init_settings () {
		$this->settings = $this->settings_fields();
	}

	

	/**
	 * Add settings link to plugin list table
	 * @param  array $links Existing links
	 * @return array 		Modified links
	 */
	public function add_settings_link ( $links ) {
		$settings_link = '<a href="options-general.php?page=' . $this->parent->_token . '_settings">' . __( 'Settings', 'dmm-mapping' ) . '</a>';
  		array_push( $links, $settings_link );
  		return $links;
	}

	/**
	 * Build settings fields
	 * @return array Fields to be displayed on settings page
	 */
	private function settings_fields () {

		$settings['dashboard'] = array(
			'title'					=> __( 'Dashboard', 'dmm-mapping' ),
			'description'			=> __( 'These are some extra input fields that maybe aren\'t as common as the others.', 'dmm-mapping' ),
			'fields'				=> array(
				array(
					'id' 			=> 'number_field',
					'label'			=> __( 'A Number' , 'dmm-mapping' ),
					'description'	=> __( 'This is a standard number field - if this field contains anything other than numbers then the form will not be submitted.', 'dmm-mapping' ),
					'type'			=> 'number',
					'default'		=> '',
					'placeholder'	=> __( '42', 'dmm-mapping' )
				)
			)
		);
		
		$settings['geography'] = array(
			'title'					=> __( 'Geography', 'dmm-mapping' ),
			'description'			=> __( 'These are fairly standard form input fields.', 'dmm-mapping' ),
			'fields'				=> array(
				array(
					'id' 			=> 'text_field',
					'label'			=> __( 'Some Text' , 'dmm-mapping' ),
					'description'	=> __( 'This is a standard text field.', 'dmm-mapping' ),
					'type'			=> 'text',
					'default'		=> '',
					'placeholder'	=> __( 'Placeholder text', 'dmm-mapping' )
				)
			)
		);
		
		$settings['generations'] = array(
			'title'					=> __( 'Generations', 'dmm-mapping' ),
			'description'			=> __( 'These are some extra input fields that maybe aren\'t as common as the others.', 'dmm-mapping' ),
			'fields'				=> array(
				array(
					'id' 			=> 'number_field',
					'label'			=> __( 'A Number' , 'dmm-mapping' ),
					'description'	=> __( 'This is a standard number field - if this field contains anything other than numbers then the form will not be submitted.', 'dmm-mapping' ),
					'type'			=> 'number',
					'default'		=> '',
					'placeholder'	=> __( '42', 'dmm-mapping' )
				)
			)
		);
		
		$settings['contacts'] = array(
			'title'					=> __( 'Contacts', 'dmm-mapping' ),
			'description'			=> __( 'These are some extra input fields that maybe aren\'t as common as the others.', 'dmm-mapping' ),
			'fields'				=> array(
				array(
					'id' 			=> 'number_field',
					'label'			=> __( 'A Number' , 'dmm-mapping' ),
					'description'	=> __( 'This is a standard number field - if this field contains anything other than numbers then the form will not be submitted.', 'dmm-mapping' ),
					'type'			=> 'number',
					'default'		=> '',
					'placeholder'	=> __( '42', 'dmm-mapping' )
				)
			)
		);
		
		$settings['shortcodes'] = array(
			'title'					=> __( 'Shortcodes', 'dmm-mapping' ),
			'description'			=> __( 'These are some extra input fields that maybe aren\'t as common as the others.', 'dmm-mapping' ),
			'fields'				=> array(
				array(
					'id' 			=> 'number_field',
					'label'			=> __( 'A Number' , 'dmm-mapping' ),
					'description'	=> __( 'This is a standard number field - if this field contains anything other than numbers then the form will not be submitted.', 'dmm-mapping' ),
					'type'			=> 'number',
					'default'		=> '',
					'placeholder'	=> __( '42', 'dmm-mapping' )
				)
			)
		);
		
		$settings['extra'] = array(
			'title'					=> __( 'Extra', 'dmm-mapping' ),
			'description'			=> __( 'These are some extra input fields that maybe aren\'t as common as the others.', 'dmm-mapping' ),
			'fields'				=> array(
				array(
					'id' 			=> 'number_field',
					'label'			=> __( 'A Number' , 'dmm-mapping' ),
					'description'	=> __( 'This is a standard number field - if this field contains anything other than numbers then the form will not be submitted.', 'dmm-mapping' ),
					'type'			=> 'number',
					'default'		=> '',
					'placeholder'	=> __( '42', 'dmm-mapping' )
				)
			)
		);
		
		$settings['help'] = array(
			'title'					=> __( 'Help', 'dmm-mapping' ),
			'description'			=> __( 'These are some extra input fields that maybe aren\'t as common as the others.', 'dmm-mapping' ),
			'fields'				=> array(
				array(
					'id' 			=> 'number_field',
					'label'			=> __( 'A Number' , 'dmm-mapping' ),
					'description'	=> __( 'This is a standard number field - if this field contains anything other than numbers then the form will not be submitted.', 'dmm-mapping' ),
					'type'			=> 'number',
					'default'		=> '',
					'placeholder'	=> __( '42', 'dmm-mapping' )
				)
			)
		);
		
		

		$settings = apply_filters( $this->parent->_token . '_settings_fields', $settings );

		return $settings;
	}

	/**
	 * Register plugin settings
	 * @return void
	 */
	public function register_settings () {
		if ( is_array( $this->settings ) ) {

			// Check posted/selected tab
			$current_section = '';
			if ( isset( $_POST['tab'] ) && $_POST['tab'] ) {
				$current_section = $_POST['tab'];
			} else {
				if ( isset( $_GET['tab'] ) && $_GET['tab'] ) {
					$current_section = $_GET['tab'];
				}
			}

			foreach ( $this->settings as $section => $data ) {

				if ( $current_section && $current_section != $section ) continue;

				// Add section to page
				add_settings_section( $section, $data['title'], array( $this, 'dmm_tracts_section' ), $this->parent->_token . '_settings' );

				foreach ( $data['fields'] as $field ) {

					// Validation callback for field
					$validation = '';
					if ( isset( $field['callback'] ) ) {
						$validation = $field['callback'];
					}

					// Register field
					$option_name = $this->base . $field['id'];
					register_setting( $this->parent->_token . '_settings', $option_name, $validation );

					// Add field to page
					add_settings_field( $field['id'], $field['label'], array( $this->parent->admin, 'display_field' ), $this->parent->_token . '_settings', $section, array( 'field' => $field, 'prefix' => $this->base ) );
				}

				if ( ! $current_section ) break;
			}
		}
	}

	public function dmm_tracts_section ( $section ) {
		$html = '<p> ' . $this->settings[ $section['id'] ]['description'] . '</p>' . "\n";
		echo $html;
	}

	/**
	 * Load settings page content
	 * @return void
	 *
	public function dmm_tracts_page () {

		// Build page HTML
		$html = '<div class="wrap" id=">' . "\n";
			$html .= '<h2>' . __( 'DMM Mapping Settings' , 'dmm-mapping' ) . '</h2>' . "\n";

			$tab = '';
			if ( isset( $_GET['tab'] ) && $_GET['tab'] ) {
				$tab .= $_GET['tab'];
			}

			// Show page tabs
			if ( is_array( $this->settings ) && 1 < count( $this->settings ) ) {

				$html .= '<h2 class="nav-tab-wrapper">' . "\n";

				$c = 0;
				foreach ( $this->settings as $section => $data ) {

					// Set tab class
					$class = 'nav-tab';
					if ( ! isset( $_GET['tab'] ) ) {
						if ( 0 == $c ) {
							$class .= ' nav-tab-active';
						}
					} else {
						if ( isset( $_GET['tab'] ) && $section == $_GET['tab'] ) {
							$class .= ' nav-tab-active';
						}
					}

					// Set tab link
					$tab_link = add_query_arg( array( 'tab' => $section ) );
					if ( isset( $_GET['settings-updated'] ) ) {
						$tab_link = remove_query_arg( 'settings-updated', $tab_link );
					}

					// Output tab
					$html .= '<a href="' . $tab_link . '" class="' . esc_attr( $class ) . '">' . esc_html( $data['title'] ) . '</a>' . "\n";

					++$c;
				}

				$html .= '</h2>' . "\n";
			}

			$html .= '<form method="post" action="options.php" enctype="multipart/form-data">' . "\n";

				// Get settings fields
				ob_start();
				settings_fields( $this->parent->_token . '_settings' );
				do_settings_sections( $this->parent->_token . '_settings' );
				$html .= ob_get_clean();

				$html .= '<p class="submit">' . "\n";
					$html .= '<input type="hidden" name="tab" value="' . esc_attr( $tab ) . '" />' . "\n";
					$html .= '<input name="Submit" type="submit" class="button-primary" value="' . esc_attr( __( 'Save Settings' , 'dmm-mapping' ) ) . '" />' . "\n";
				$html .= '</p>' . "\n";
			$html .= '</form>' . "\n";
		$html .= '</div>' . "\n";

		echo $html;
	} */
	
	public function dmm_tracts_page () {
		$html = '<div class="wrap">';
			$html .= "<h1>DMM TRACTS</h1><p><iframe src='" . plugins_url( 'index.php', __FILE__ ) . "' border='0' width='100%' height='800px'></iframe></p>";
			$html .= '</div>';
		echo $html;
	}

	
	
	
	
	
	
	
	

	
	
	
}