<?php
/**
 * Plugin Name:    WP Updater
 * Plugin URI:     https://jeroensormani.com
 * Description:    Just a plugin to load the WP Updater.
 * Version:        1.1.0
 * Author:         Jeroen Sormani
 * Author URI:     https://jeroensormani.com/
 */


namespace JeroenSormani\WP_Updater;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class WPUpdater {


	/**
	 * WP Updater version.
	 *
	 * @var string
	 */
	protected $version = '1.1.0';


	/**
	 * Plugin class.
	 *
	 * @var Plugin
	 */
	protected $plugin;


	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args List of arguments.
	 */
	public function __construct( $args = array() ) {

		$this->args = wp_parse_args( $args, array(
			'file'                => '', // Main plugin file
			'license_option_name' => '', // License option name to get from DB
			'type'                => 'edd', // Type of license server
			'api_url'             => '', // API URL to talk with
		) );

		$this->plugin = new Plugin( $args );

		// Show license field
		add_action( 'after_plugin_row_' . $this->plugin->plugin_basename, array( $this, 'license_field' ), 20, 3 );

		// Enqueue scripts
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

		// AJAX - Update license
		add_action( 'wp_ajax_updater_update_license', array( $this, 'update_license' ) );
		// AJAX - deactivate license
		add_action( 'wp_ajax_updater_deactivate_license', array( $this, 'deactivate_license' ) );

		// Add plugin data to transient
		add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'add_plugin_update_transient_data' ), 20 );

		// Possibly remove update row
		add_action( 'after_plugin_row_' . $this->plugin->plugin_basename, array( $this, 'maybe_remove_update_row' ), 9 );

		// Show pop-up (thickbox) details
		add_filter( 'plugins_api', array( $this, 'replace_plugin_thickbox' ), 10, 3 );

	}


	/**
	 * Add plugin update data.
	 *
	 * Add the plugin update data to the cached list used by WordPress. This is added
	 * the same time when WordPress is getting / setting the plugin update data for other plugins.
	 *
	 * @since 1.0.0
	 *
	 * @param array $data List of data already present in WordPress
	 * @return mixed
	 */
	public function add_plugin_update_transient_data( $data ) {
		$file = $this->plugin->plugin_basename;

		/**
		 * This section is a additional piece that ensures that a premium plugin doesn't
		 * use a different plugin's data from the plugin which has the same slug/name.
		 */
		if ( isset( $data->response[ $file ] ) && ! isset( $data->response[ $file ]->premium ) ) {
			unset( $data->response[ $file ] );
		}

		// Bail if it has been less then 12 hours, and its confirmed by the 'premium' property that its the correct one.
		if (
			isset( $data->last_checked ) && ( 12 * HOUR_IN_SECONDS ) > ( time() - $data->last_checked ) &&
		    isset( $data->response[ $file ] ) && isset( $data->response[ $file ]->premium )
		) {
			return $data;
		}

		// Check plugin status
		$this->plugin->check_and_update_license_status();
		$plugin_data = $this->plugin->client->get_plugin_update_info();

		if ( ! is_wp_error( $plugin_data ) ) {

			if ( $this->plugin->client->is_update_available() ) {
				$data->response[ $file ] = (object) $plugin_data;
				$data->response[ $file ]->premium = true; // Set a custom flag to we can check for it.
				$data->checked[ $file ] = $this->plugin->get_version();
			} else {
				$data->no_update[ $file ] = (object) $plugin_data;
			}
		}

		return $data;

	}


	/**
	 * Show license field.
	 *
	 * Show the license field on the plugins page.
	 *
	 * @since 1.0.0
	 *
	 * @param  string $plugin_file Path to the plugin file, relative to the plugins directory.
	 * @param  array  $plugin_data An array of plugin data.
	 * @param  string $status      Status of the plugin. Defaults are 'All', 'Active',
	 *                            'Inactive', 'Recently Activated', 'Upgrade', 'Must-Use',
	 *                            'Drop-ins', 'Search'.
	 * @return bool False          when the field shouldn't be showing.
	 */
	public function license_field( $plugin_file, $plugin_data, $status ) {

		$plugin = $this->plugin;
		if ( $plugin_file !== $plugin->plugin_basename ) {
			return false;
		}

		if ( $plugin->get_license_status() !== 'valid' ) {
			echo wpu_get_template( 'html-license-field.php', array(
				'plugin' => $plugin,
			) );
		} else {
			echo wpu_get_template( 'html-valid-license.php', array(
				'plugin' => $plugin,
			) );
		}

	}


	/**
	 * Enqueue scripts.
	 *
	 * Add javascripts and stylesheets.
	 *
	 * @since 1.0.0
	 *
	 * @param string $hook Current page ID.
	 */
	public function admin_enqueue_scripts( $hook ) {

		// Styles
		wp_register_style( 'wp-updater', plugins_url( 'assets/css/wp-updater.min.css', __FILE__ ), array( 'dashicons' ), $this->version );

		// Javascript
		wp_register_script( 'wp-updater', plugins_url( 'assets/js/wp-updater.min.js', __FILE__ ), array( 'jquery' ), $this->version );
		wp_localize_script( 'wp-updater', 'wpu', apply_filters( 'WP_Updater/script_localization', array(
			'nonce' => wp_create_nonce( 'wpu-nonce' ),
		) ) );

		if ( $hook == 'plugins.php' ) {
			wp_enqueue_style( 'wp-updater' );
			wp_enqueue_script( 'wp-updater' );
		}

	}


	/**
	 * Save the license key.
	 *
	 * Save the license key to the database.
	 *
	 * @since 1.0.0
	 */
	public function update_license() {

		check_ajax_referer( 'wpu-nonce', 'nonce' );

		$plugin  = sanitize_text_field( $_POST['plugin'] );
		$license = sanitize_text_field( $_POST['license'] );

		// Check if AJAX action is meant for this plugin. When multiple plugins using the WP Updater
		// it may clash, this check ensures it works nicely.
		if ( $this->plugin->plugin_basename !== $plugin ) {
			return;
		}

		$this->plugin->set_license_key( $license );
		$license_format = apply_filters( 'WP_Updater/license_format', '[a-z0-9]{32}', $this->plugin );

		if ( empty( $license ) ) {
			$message      = __( 'Please enter a license key and try again' );
			$message_type = 'error';
		} elseif ( $license_format && ! preg_match( '/' . $license_format . '/i', $license ) ) {
			$message = __( 'Invalid license key, please verify your license and try again' );
			$message_type = 'error';
		} else {

			$plugin   = $this->plugin;
			$response = $plugin->client->activate();

			if ( $response && ! is_wp_error( $response ) ) {
				$plugin->client->get_plugin_update_info_from_api( true ); // Re-get plugin data (download url etc)
				$plugin->set_license_status( 'valid' );
				$message      = __( 'License successfully activated' );
				$message_type = 'success';
			} else {
				$plugin->set_license_status( $response->get_error_code() );
				$message      = $response->get_error_message();
				$message_type = 'error';
			}
		}

		ob_start();
			$this->license_field( $this->plugin->plugin_basename, array(), null );
		$html = ob_get_clean();

		wp_send_json( array(
			'message'      => $message,
			'message_type' => $message_type,
			'html'         => $html,
		) );
	}


	/**
	 * Deactivate license.
	 *
	 * Deactivate the license on the server.
	 *
	 * @since 1.0.0
	 */
	public function deactivate_license() {

		check_ajax_referer( 'wpu-nonce', 'nonce' );

		$plugin = sanitize_text_field( $_POST['plugin'] );

		// Check if AJAX action is meant for this plugin. When multiple plugins using the WP Updater
		// it may clash, this check ensures it works nicely.
		if ( $this->plugin->plugin_basename !== $plugin ) {
			return;
		}

		$plugin   = $this->plugin;
		$response = $plugin->client->deactivate();

		if ( $response && ! is_wp_error( $response) ) {
			$plugin->set_license_status( null );
			$message      = __( 'License successfully deactivated' );
			$message_type = 'success';
		} else {
			$plugin->set_license_status( 'invalid' );
			$message      = $response->get_error_message();
			$message_type = 'error';
		}

		ob_start();
			$this->license_field( $this->plugin->plugin_basename, array(), null );
		$html = ob_get_clean();

		wp_send_json( array(
			'message'      => $message,
			'message_type' => $message_type,
			'html'         => $html,
		) );

	}


	/**
	 * Remove update row.
	 *
	 * Possible remove the update row when there is a update, but the license is set to 'expired'.
	 *
	 * @since 1.0.0
	 */
	public function maybe_remove_update_row() {

		if ( $this->plugin->get_license_status() != 'valid' ) {
			remove_action( 'after_plugin_row_' . $this->plugin->plugin_basename, 'wp_plugin_update_row', 10 );
		}

	}


	/**
	 * Replace plugin thickbox data.
	 *
	 * Replace the data for the plugin version 'view details' button on the plugins page.
	 *
	 * @since 1.0.0
	 *
	 * @param  object|bool $data   False by default, when returning a object WP knows to use that instead.
	 * @param  string      $action The type of information being requested from the Plugin Install API.
	 * @param  object      $args   Plugin API arguments.
	 * @return object|bool         Returns object with plugin info, false otherwise.
	 */
	public function replace_plugin_thickbox( $data, $action, $args ) {

		if ( $action !== 'plugin_information' ) {
			return $data;
		}

		$plugin_slug = $this->plugin->get_slug();
		if ( $args->slug != $plugin_slug ) {
			return $data;
		}

		$plugin_data = $this->plugin->client->get_plugin_update_info();
		$plugin_sections = isset( $plugin_data->sections ) ? maybe_unserialize( $plugin_data->sections ) : array();

		$data = (object) array(
			'name' => $this->plugin->get_name(),
			'slug' => $plugin_data->slug,
			'version' => $plugin_data->new_version,
			'author' => '',
			'author_profile' => '',
			'contributors' => array(),
			'requires' => '',
			'tested' => '',
			'compatibility' => array(),
			'rating' => '',
			'num_ratings' => '',
			'ratings' => array(),
//			'active_installs' => '',
			'last_updated' => $plugin_data->last_updated,
			'added' => '',
			'homepage' => $plugin_data->homepage,
			'sections' => array(
				'description' => isset( $plugin_sections['description'] ) ? $plugin_sections['description'] : null,
				'changelog' => isset( $plugin_sections['changelog'] ) ? $plugin_sections['changelog'] : null,
			),
			'banners' => '',
		);

		return $data;

	}


}

require plugin_dir_path( __FILE__ ) . 'functions.php';
require plugin_dir_path( __FILE__ ) . 'Plugin.php';
require plugin_dir_path( __FILE__ ) . 'Clients/ClientInterface.php';
require plugin_dir_path( __FILE__ ) . 'Clients/EDD.php';
require plugin_dir_path( __FILE__ ) . 'Clients/WooCommerce.php';
