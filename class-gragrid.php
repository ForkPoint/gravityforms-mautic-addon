<?php

GFForms::include_feed_addon_framework();

require_once 'includes/concerns/class-gragrid-converts-case.php';

/**
 * The Mautic Add-on Class
 *
 * @since 1.0.0
 * @author  Vladimir Contreras
 */
class Gragrid extends GFFeedAddOn {
	use Gragrid_Converts_Case;

	/**
	 * Contains an instance of this class, if available.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @var    Gragrid $_instance If available, contains an instance of this class.
	 */
	private static $_instance = null;

	/**
	 * Defines the version of the Mautic Add-On.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @var    string $_version Contains the version, defined from gragrid.php
	 */
	protected $_version = GRAGRID_VERSION;

	/**
	 * Defines the minimum Gravity Forms version required.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @var    string $_min_gravityforms_version The minimum version required.
	 */
	protected $_min_gravityforms_version = '1.9.12';

	/**
	 * Defines the plugin slug.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @var    string $_slug The slug used for this plugin.
	 */
	protected $_slug = 'gragrid';

	/**
	 * Defines the main plugin file.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @var    string $_path The path to the main plugin file, relative to the plugins folder.
	 */
	protected $_path = 'gragrid/gragrid.php';

	/**
	 * Defines the full path to this class file.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @var    string $_full_path The full path.
	 */
	protected $_full_path = __FILE__;

	/**
	 * Defines the URL where this Add-On can be found.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @var    string The URL of the Add-On.
	 */
	protected $_url = 'https://github.com/vlasscontreras/gragrid';

	/**
	 * Defines the title of this Add-On.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @var    string $_title The title of the Add-On.
	 */
	protected $_title = null;

	/**
	 * Defines the short title of the Add-On.
	 *
	 * @since 1-0-0
	 *
	 * @access protected
	 * @var    string $_short_title The short title.
	 */
	protected $_short_title = 'Mautic';

	/**
	 * Contains an instance of the Mautic API library.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @var    Gragrid_API|null $api Contains an instance of the Mautic API library.
	 */
	protected $api = null;

	/**
	 * Add-on constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::__construct();

		$this->_title = esc_html__( 'Gravity Forms: Mautic Add-on', 'gragrid' );
	}

	/**
	 * Get an instance of this class.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return Gragrid
	 */
	public static function get_instance() {
		if ( null === self::$_instance ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Autoload the required libraries.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @uses GFAddOn::is_gravityforms_supported()
	 */
	public function pre_init() {
		parent::pre_init();

		if ( $this->is_gravityforms_supported() && ! class_exists( 'Gragrid_API' ) ) {
			require_once 'includes/class-gragrid-api.php';
		}
	}

	// # PLUGIN SETTINGS --------------------------------------------

	/**
	 * Configures the settings which should be rendered on the add-on
	 * settings tab.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return array
	 */
	public function plugin_settings_fields() {
		return array(
			array(
				'fields'      => array(
					array(
						'name'              => 'mautic_username',
						'label'             => esc_html__( 'Mautic Username', 'gragrid' ),
						'type'              => 'text',
						'class'             => 'medium',
						'feedback_callback' => array( $this, 'init_api' ),
					),
					array(
						'name'              => 'mautic_password',
						'label'             => esc_html__( 'Mautic Password', 'gragrid' ),
						'type'              => 'text',
						'class'             => 'medium',
						'feedback_callback' => array( $this, 'init_api' ),
					)
				),
			),
		);
	}

	// # FEED SETTINGS ----------------------------------------------

	/**
	 * Configures the settings which should be rendered on the feed edit page.
	 *
	 * @since 1.0.0
	 * @since 1.1.0 Added conditional feed setting.
	 * @since 2.1.0 Added custom field mapping.
	 *
	 * @access public
	 * @return array
	 */
	public function feed_settings_fields() {
		$custom_fields    = $this->sengrid_custom_fields_map();
		$custom_field_map = null;

		if ( ! count( $custom_fields ) > 0 ) {
			$this->log_error( __METHOD__ . ': API retured empty set of custom fields.' );
		} else {
			$custom_field_map = array(
				'name'      => 'mappedCustomFields',
				'label'     => esc_html__( 'Custom Fields', 'gragrid' ),
				'type'      => 'field_map',
				'field_map' => $custom_fields,
				'tooltip'   => sprintf(
					'<h6>%s</h6>%s',
					esc_html__( 'Custom Fields', 'gragrid' ),
					esc_html__( 'Associate your custom Mautic fields to the appropriate Gravity Form fields by selecting the appropriate form field from the list.', 'gragrid' )
				),
			);
		}

		$fields = array(
			array(
				'name'     => 'feedName',
				'label'    => esc_html__( 'Name', 'gragrid' ),
				'type'     => 'text',
				'required' => true,
				'class'    => 'medium',
				'tooltip'  => sprintf(
					'<h6>%s</h6>%s',
					esc_html__( 'Name', 'gragrid' ),
					esc_html__( 'Enter a feed name to uniquely identify this setup.', 'gragrid' )
				),
			),
			array(
				'name'     => 'mautic_segment_list',
				'label'    => esc_html__( 'Mautic Segment List', 'gragrid' ),
				'type'     => 'mautic_segment_list',
				'required' => true,
				'tooltip'  => sprintf(
					'<h6>%s</h6>%s',
					esc_html__( 'Mautic Segment List', 'gragrid' ),
					esc_html__( 'Select the segment list you would like to add contacts to.', 'gragrid' )
				),
			),
			array(
				'name'      => 'mappedFields',
				'label'     => esc_html__( 'Map Fields', 'gragrid' ),
				'type'      => 'field_map',
				'field_map' => $this->sengrid_field_map(),
				'tooltip'   => sprintf(
					'<h6>%s</h6>%s',
					esc_html__( 'Map Fields', 'gragrid' ),
					esc_html__( 'Associate the Mautic fields to the appropriate Gravity Form fields by selecting the appropriate form field from the list.', 'gragrid' )
				),
			),
			$custom_field_map,
			array(
				'type'           => 'feed_condition',
				'name'           => 'enabled',
				'label'          => esc_html__( 'Conditional logic', 'gragrid' ),
				'checkbox_label' => esc_html__( 'Enable', 'gragrid' ),
				'instructions'   => esc_html__( 'Send this lead to Mautic if', 'gragrid' ),
			),
			array( 'type' => 'save' ),
		);

		$settings = array(
			array(
				'title'  => esc_html__( 'Mautic Feed Settings', 'gragrid' ),
				'fields' => array_filter( $fields ),
			),
		);

		return $settings;
	}

	/**
	 * Define the markup for the mautic_segment_list type field.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param array $field The field properties.
	 * @param bool  $echo  Should the setting markup be echoed. Defaults to true.
	 * @return string
	 */
	public function settings_mautic_segment_list( $field, $echo = true ) {
		if ( ! $this->init_api() ) {
			return;
		}

		$lists = $this->api->get_segments()['body'];

		if ( is_wp_error( $lists ) ) {
			$this->log_error( __METHOD__ . ': Could not retrieve the contact lists ' . $lists->get_error_message() );

			return sprintf(
				'<div class="notice notice-error inline" style="display: block !important;"><p>%s</p></div>',
				esc_html__( 'Could not load the contact lists. Make sure you have a valid API key.', 'gragrid' )
			);
		}

		if ( ! count( $lists['lists'] ) > 0 ) {
			$this->log_error( __METHOD__ . ': API retured empty set of lists.' );

			printf( esc_html__( 'You don\'t have contact lists in your account. Please create one first and try again.', 'gragrid' ) );

			return;
		}

		// Initialize select options.
		$options = array(
			array(
				'label' => esc_html__( 'Select a Mautic list', 'gragrid' ),
				'value' => '',
			),
		);

		foreach ( $lists['lists'] as $list ) {
			$options[] = array(
				'label' => esc_html( $list['name'] . ' (' . $list['alias'] . ')' ),
				'value' => esc_attr( $list['id'] ),
			);

		}

		// Add select field properties.
		$field['type']    = 'select';
		$field['choices'] = $options;

		// Generate select field.
		$html = $this->settings_select( $field, false );

		if ( $echo ) {
			echo $html; // phpcs:ignore: XSS ok.
		}

		return $html;
	}

	/**
	 * Return an array of Mautic contact fields which can be mapped to the Form fields/entry meta.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return array
	 */
	public function sengrid_field_map() {
		return array(
			'email' => array(
				'name'       => 'email',
				'label'      => esc_html__('Email Address', 'gragrid'),
				'required'   => false,
				'field_type' => array('email', 'hidden'),
			),
			'first_name' => array(
				'name'       => 'firstname',
				'label'      => esc_html__('First Name', 'gragrid'),
				'required'   => false,
				'field_type' => array('name', 'text', 'hidden'),
			),
			'last_name' => array(
				'name'       => 'lastname',
				'label'      => esc_html__('Last Name', 'gragrid'),
				'required'   => false,
				'field_type' => array('name', 'text', 'hidden'),
			),
			'phone_number'   => array(
				'name'       => 'phone',
				'label'      => esc_html__( 'Phone Number', 'gragrid' ),
				'required'   => false,
				'field_type' => array( 'phone', 'text', 'hidden' ),
			),
			'checkbox' => array(
				'name'       => 'checkbox',
				'label'      => esc_html__('Checkbox', 'gragrid'),
				'required'   => false,
				'field_type' => array('checkbox'),
			),
			'radio' => array(
				'name'       => 'radio',
				'label'      => esc_html__('Radio Button', 'gragrid'),
				'required'   => false,
				'field_type' => array('radio'),
			),
			'dropdown' => array(
				'name'       => 'dropdown',
				'label'      => esc_html__('Dropdown', 'gragrid'),
				'required'   => false,
				'field_type' => array('select'),
			)
		);
	}

	/**
	 * Map custom Mautic fields
	 *
	 * @since 2.1.0
	 *
	 * @return array
	 */
	public function sengrid_custom_fields_map() {
		if ( ! $this->init_api() ) {
			return array();
		}

		$fields        = array();
		$custom_fields = (array) rgar( $this->api->get_custom_fields(), 'custom_fields' );
		$custom_fields = array_filter( $custom_fields );

		foreach ( $custom_fields as $custom_field ) {
			$fields[ $custom_field['id'] ] = array(
				'name'     => $custom_field['id'],
				'label'    => $this->snake_to_title( $custom_field['name'] ),
				'required' => false,
			);
		}

		return $fields;
	}

	/**
	 * Prevent feeds being listed or created if the API key isn't valid.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return bool
	 */
	public function can_create_feed() {
		return $this->init_api();
	}

	/**
	 * Allow the feed to be duplicated.
	 *
	 * @since 1.0.0
	 *
	 * @param array|int $id The ID of the feed to be duplicated or the feed object when duplicating a form.
	 * @return bool
	 */
	public function can_duplicate_feed( $id ) {
		return true;
	}

	/**
	 * Configures which columns should be displayed on the feed list page.
	 *
	 * @since  1.0.0
	 *
	 * @access public
	 * @return array
	 */
	public function feed_list_columns() {
		return array(
			'feedName'      => esc_html__( 'Name', 'gragrid' ),
			'mautic_segment_list' => esc_html__( 'Selected Mautic Segment', 'gragrid' ),
		);
	}

	/**
	 * Get the name of the Mautic segment for the feed table view.
	 *
	 * @since 1.0.0
	 *
	 * @param array $feed The feed being included in the feed list.
	 * @return string
	 */
	public function get_column_value_mautic_segment_list( $feed ) {
		if ( ! $this->init_api() ) {
			return rgars( $feed, 'meta/mautic_segment_list' );
		}

		$list = $this->api->get_segment( rgars( $feed, 'meta/mautic_segment_list' ) );

		if ( is_wp_error( $list ) ) {
			$this->log_error( __METHOD__ . ': Could not retrieve the contact list: ' . $list->get_error_message() );

			return rgars( $feed, 'meta/mautic_segment_list' );
		}

		return $list['list']['name'];
	}

	// # FEED PROCESSING -----------------------------------------------------------------------------------------------

	/**
	 * Process the feed e.g. subscribe the user to a list.
	 *
	 * @since 1.0.0
	 * @since 2.1.0 Added custom fields to the request data.
	 *
	 * @param array $feed The feed object to be processed.
	 * @param array $entry The entry object currently being processed.
	 * @param array $form The form object currently being processed.
	 * @return bool|void
	 */
	public function process_feed( $feed, $entry, $form ) {
		if ( ! $this->init_api() ) {
			$this->add_feed_error( esc_html__( 'Unable to process feed because API could not be initialized.', 'gragrid' ), $feed, $entry, $form );

			return $entry;
		}

		$contact = array();

		$segment = $this->api->get_segment( rgars( $feed, 'meta/mautic_segment_list' ) );

		$segment_id = $segment['list']['id'];

		// Map reserved/standard/default fields.
		$fields = $this->get_field_map_fields( $feed, 'mappedFields' );

		foreach ( $fields as $name => $field_id ) {
			$contact[ $name ] = $this->get_field_value( $form, $entry, $field_id );
		}

		try {
			// Save the contacts.
			$response = $this->api->create_contact( $contact );

			$contact_id = $response['contact']['id'];

			$this->api->add_contact_to_segment($segment_id, $contact_id);

			if ( is_wp_error( $response ) ) {
				return null;
			}

			$this->add_note(
				$entry['id'],
				sprintf(
					// Translators: %s Mautic segment ID.
					esc_html__( 'Gragrid successfully passed the lead details to the Mautic segment list #%s.', 'gragrid' ),
					rgars( $feed, 'meta/mautic_segment_list' )
				),
				'success'
			);

			return $entry;
		} catch ( Exception $e ) {
			// Translators: %s error message.
			$this->add_feed_error( sprintf( esc_html__( 'Unable to add recipient to segment: %s', 'gragrid' ), $e->getMessage() ), $feed, $entry, $form );

			return $entry;
		}
	}

	// # HELPERS ----------------------------------------------------

	/**
	 * Initializes Mautic API if credentials are valid.
	 *
	 * @since 1.0.0
	 *
	 * @uses GFAddOn::get_plugin_setting()
	 * @uses GFAddOn::log_debug()
	 * @uses GFAddOn::log_error()
	 * @uses Gragrid_API::valid_key()
	 *
	 * @access public
	 * @param string $mautic_username Mautic username.
	 * @param string $mautic_password Mautic password.
	 * @return bool|null
	 */
	public function init_api( $mautic_username = null, $mautic_password = null) {
		// If the API is already initialized, return true.
		if ( ! is_null( $this->api ) ) {
			return true;
		}

		$mautic_username = $this->get_plugin_setting( 'mautic_username' );
		$mautic_password = $this->get_plugin_setting( 'mautic_password' );

		if ( rgblank( $mautic_username ) || rgblank( $mautic_password )) {
			return null;
		}

		$this->log_debug( __METHOD__ . '(): Validating API key.' );

		try {
			$this->api = new Gragrid_API( $mautic_username, $mautic_password );

			if ( $this->api->valid_key() ) {
				$this->log_debug( __METHOD__ . '(): Mautic successfully authenticated.' );

				return true;
			} else {
				$this->log_debug( __METHOD__ . '(): Unable to authenticate with Mautic.' );

				return false;
			}
		} catch ( Exception $e ) {
			$this->log_error( __METHOD__ . '(): Unable to authenticate with Mautic; ' . $e->getMessage() );

			return false;
		}
	}
}
