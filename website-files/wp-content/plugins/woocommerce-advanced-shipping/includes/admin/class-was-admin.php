<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Admin class.
 *
 * Handle all admin related functions.
 *
 * @author     	Jeroen Sormani
 * @version		1.0.0
 */
class WAS_Admin {


	/**
	 * Constructor.
	 *
	 * @since 1.0.5
	 */
	public function __construct() {

		// Initialize components
		add_action( 'admin_init', array( $this, 'init' ) );

		// Keep WC menu open while in WAS edit screen
		add_action( 'admin_head', array( $this, 'menu_highlight' ) );

		// Add to WC Screen IDs to load scripts.
		add_filter( 'woocommerce_screen_ids', array( $this, 'add_screen_ids' ) );

		// Enqueue scripts
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

		// Auto updater function
		add_action( 'admin_init', array( $this, 'auto_updater' ) );

		// License notice
		add_action( 'admin_notices', array( $this, 'license_notice' ) );

		// Help tab
		add_action( 'current_screen', array( $this, 'add_help_tab' ), 90 );

		// Customize WP Updater message on activation
		add_filter( 'WP_Updater/activate_error_message', array( $this, 'wp_updater_activation_error_message' ), 10, 3 );

		// Customize license field messaging
		add_filter( 'WP_Updater/wpu_get_template/html-invalid-update-available.php', array( $this, 'wp_updater_update_available_message' ), 10, 3 );

		// Custom WP Updater license format
		add_filter( 'WP_Updater/license_format', array( $this, 'wp_updater_license_format' ), 10, 2 );
	}


	/**
	 * Initialize class components.
	 *
	 * @since 1.1.8
	 */
	public function init() {
		global $pagenow;

		if ( 'plugins.php' == $pagenow ) {
			add_filter( 'plugin_action_links_' . plugin_basename( WooCommerce_Advanced_Shipping()->file ), array( $this, 'add_plugin_action_links' ), 10, 2 );
			add_filter( 'plugin_row_meta', array( $this, 'add_plugin_row_meta' ), 20, 2 );
		}
	}


	/**
	 * Screen IDs.
	 *
	 * Add 'was' to the screen IDs so the WooCommerce scripts are loaded.
	 *
	 * @since 1.0.5
	 *
	 * @param  array $screen_ids List of existing screen IDs.
	 * @return array             List of modified screen IDs.
	 */
	public function add_screen_ids( $screen_ids ) {

		$screen_ids[] = 'was';

		return $screen_ids;

	}


	/**
	 * Enqueue scripts.
	 *
	 * Enqueue style and java scripts.
	 *
	 * @since 1.0.5
	 */
	public function admin_enqueue_scripts() {

		// Style script
		wp_register_style( 'woocommerce-advanced-shipping', plugins_url( 'assets/admin/css/woocommerce-advanced-shipping.min.css', WooCommerce_Advanced_Shipping()->file ), array(), WooCommerce_Advanced_Shipping()->version );

		// Javascript
		wp_register_script( 'woocommerce-advanced-shipping', plugins_url( 'assets/admin/js/woocommerce-advanced-shipping.min.js', WooCommerce_Advanced_Shipping()->file ), array( 'jquery', 'jquery-ui-sortable', 'jquery-blockui', 'jquery-tiptip' ), WooCommerce_Advanced_Shipping()->version, true );

		// Only load scripts on relevant pages
		if (
			( isset( $_REQUEST['post'] ) && 'was' == get_post_type( $_REQUEST['post'] ) ) ||
			( isset( $_REQUEST['post_type'] ) && 'was' == $_REQUEST['post_type'] ) ||
			( isset( $_REQUEST['tab'] ) && 'shipping' == $_REQUEST['tab'] )
		) :

			wp_enqueue_style( 'woocommerce-advanced-shipping' );
			wp_enqueue_script( 'woocommerce-advanced-shipping' );
			wp_enqueue_script( 'wp-conditions' );
			wp_localize_script( 'wp-conditions', 'wpc2', array(
				'action_prefix' => 'was_',
			) );
			wp_localize_script( 'woocommerce-advanced-shipping', 'was', array(
				'nonce'   => wp_create_nonce( 'advanced-shipping' ),
				'rate_id' => get_the_ID(),
			) );

			wp_dequeue_script( 'autosave' );

		endif;

	}


	/**
	 * Keep menu open.
	 *
	 * Highlights the correct top level admin menu item for post type add screens.
	 *
	 * @since 1.0.5
	 */
	public function menu_highlight() {

		global $parent_file, $submenu_file, $post_type;

		if ( 'was' == $post_type ) :
			$parent_file  = 'woocommerce';
			$submenu_file = 'wc-settings';
		endif;

	}


	/**
	 * Plugin action links.
	 *
	 * Add links to the plugins.php page below the plugin name
	 * and besides the 'activate', 'edit', 'delete' action links.
	 *
	 * @since 1.0.8
	 *
	 * @param  array  $links List of existing links.
	 * @param  string $file  Name of the current plugin being looped.
	 * @return array         List of modified links.
	 */
	public function add_plugin_action_links( $links, $file ) {

		if ( $file == plugin_basename( WooCommerce_Advanced_Shipping()->file ) ) :
			$links = array_merge( array(
				'<a href="' . esc_url( admin_url( '/admin.php?page=wc-settings&tab=shipping&section=legacy_advanced_shipping' ) ) . '">' . __( 'Settings', 'woocommerce-advanced-shipping' ) . '</a>'
			), $links );
		endif;

		return $links;

	}


	/**
	 * Plugin meta link(s).
	 *
	 * Add links to the plugins.php page below the plugin name
	 * and besides the 'activate', 'edit', 'delete' action links.
	 *
	 * @since 1.1.0
	 *
	 * @param  array  $links List of existing links.
	 * @param  string $file  Name of the current plugin being looped.
	 * @return array         List of modified links.
	 */
	public function add_plugin_row_meta( $links, $file ) {

		if ( $file == plugin_basename( WooCommerce_Advanced_Shipping()->file ) ) :
			$links = array_merge( $links, array(
				'<a href="https://jeroensormani.com/woocommerce-advanced-shipping/support/" target="_blank">' . __( 'Premium support', 'woocommerce-advanced-shipping' ) . '</a>'
			) );
		endif;

		return $links;

	}


	/**
	 * Updater.
	 *
	 * Function to get automatic updates.
	 *
	 * @since 1.1.0
	 */
	public function auto_updater() {

		// Updater
		if ( ! class_exists( '\JeroenSormani\WP_Updater\WPUpdater' ) ) {
			require plugin_dir_path( WooCommerce_Advanced_Shipping()->file ) . '/libraries/wp-updater/wp-updater.php';
		}
		new \JeroenSormani\WP_Updater\WPUpdater( array(
			'file'    => WooCommerce_Advanced_Shipping()->file,
			'name'    => 'Advanced Shipping for WooCommerce',
			'version' => WooCommerce_Advanced_Shipping()->version,
			'api_url' => 'https://aceplugins.com',
		) );

	}


	/**
	 * Notice for invalid license.
	 *
	 * Show a notice when the license is not entered or when expired/invalid.
	 * Dismissible for 60 days.
	 *
	 * @since 1.1.0
	 */
	public function license_notice() {
		$screen = get_current_screen();
		$license = get_option( 'woocommerce-advanced-shipping_license', '' );
		$settings = get_option( 'woocommerce_advanced_shipping_settings', array() );
		$notice_dismissed = isset( $settings['notices']['dismissed_license_invalid'] ) ? $settings['notices']['dismissed_license_invalid'] : false;

		if ( ( ! is_was_page() && $screen->id !== 'plugins' ) || ( $notice_dismissed && $notice_dismissed > strtotime( '-60 days' ) ) ) {
			return;
		}

		// No license has been entered
		if ( empty( $license ) ) {
			$notice = __( 'Thank you for using Advanced Shipping. <a href="' . admin_url( 'plugins.php#checkbox_b34f90cebbdad68ac199dc7655872b67' ) . '">Enter a supported license key</a> to receive dashboard updates.', 'woocommerce-advanced-shipping' );
		}

		// License expired
		elseif ( get_transient( 'wpu_woocommerce-advanced-shipping_status' ) === 'expired' ) {
			$notice = __( 'Your license for Advanced Shipping has expired. <a href="https://aceplugins.com/plugin/advanced-shipping-for-woocommerce/renew" target="_blank">Renew your license</a> to continue to get support and dashboard updates.', 'woocommerce-advanced-shipping' );
		}

		// Invalid license
		elseif ( get_transient( 'wpu_woocommerce-advanced-shipping_status' ) !== 'valid' ) {
			$notice = __( 'Your license for Advanced Shipping is invalid. <a href="https://aceplugins.com/plugin/advanced-shipping-for-woocommerce/renew" target="_blank">Purchase or renew a license</a> to continue to get support and dashboard updates. Have a license? <a href="' . admin_url( 'plugins.php#checkbox_b34f90cebbdad68ac199dc7655872b67' ) . '">Enter license on the plugins page</a>', 'woocommerce-advanced-shipping' );
		}

		if ( ! empty( $notice ) ) {
			?><div class="notice is-dismissible notice-info advanced-shipping-notice">
				<p><?php echo wp_kses_post( $notice ); ?>
					<a href="javascript:void(0);" style="float: right;" class="was-notice-dismiss"><?php _e( 'Dismiss for 60 days', 'woocommerce-advanced-shipping' ); ?></a>
				</p>
			</div><?php

			?><script type="text/javascript">
				(function($) {
					jQuery('body').on('click', '.advanced-shipping-notice .was-notice-dismiss', function () {
						jQuery.post(ajaxurl, {'action': 'was_dismiss_notice'});
						$(this).parents('.notice').slideUp();
					});
				})(jQuery);
			</script><?php
		}
	}


	/**
	 * Add help tab.
	 *
	 * Add help tab on Advanced Shipping related pages.
	 *
	 * @since 1.1.0
	 */
	public function add_help_tab() {
		$screen = get_current_screen();

		if ( ! $screen || ! is_was_page() ) {
			return;
		}

		$screen->add_help_tab( array(
			'id'       => 'advanced_shipping_help',
			'title'    => __( 'Advanced Shipping', 'woocommerce-advanced-shipping' ),
			'content'  => '<h2>' . __( 'Advanced Shipping', 'woocommerce' ) . '</h2>' .
					'<p>
						<strong>' . __( 'Where do I configure my Advanced Shipping rates?', 'woocommerce-advanced-shipping' ) . '</strong><br/>' .
						__( 'Advanced Shipping rates can be setup both within the shipping zones or outside of it. Neither are required for the other to function.', 'woocommerce-advanced-shipping' ) .
					'</p>
					<p>
						<strong>' . __( 'How can I get the WAS Advanced Pricing extension?', 'woocommerce-advanced-shipping' ) . '</strong><br/>' .
						sprintf(
							__( 'The extension can be downloaded from the following page by entering a valid/supported license key; <a href="%s" target="_blank">Request the WAS Advanced Pricing</a>', 'woocommerce-advanced-shipping' ),
							'https://jeroensormani.com/woocommerce-advanced-shipping/request-advanced-shipping-advanced-pricing-extension/'
						) .
					'</p>
						<p>
						<strong>' . __( 'How can I get the WAS Shipping Zones extension?', 'woocommerce-advanced-shipping' ) . '</strong><br/>' .
						sprintf(
							__( 'The extension can be downloaded from the following page by entering a valid/supported license key; <a href="%s" target="_blank">Request the WAS Shipping Zones</a>. This extension is not required to setup Advanced Shipping rates in the WooCommerce Shipping Zones.', 'woocommerce-advanced-shipping' ),
							'https://jeroensormani.com/woocommerce-advanced-shipping/request-shipping-zones-extension/'
						) .
					'</p>
					<p>
						<a href="https://aceplugins.com/doc/advanced-shipping-for-woocommerce#doc-plugin-faq/" target="_blank" class="button">' . __( 'Frequently Asked Questions', 'woocommerce-advanced-shipping' ) . '</a>
						<a href="https://aceplugins.com/doc/advanced-shipping-for-woocommerce/" class="button button-primary" target="_blank">' . __( 'Online documentation', 'woocommerce-advanced-shipping' ) . '</a>
						<a href="https://jeroensormani.com/woocommerce-advanced-shipping/support/" target="_blank" class="button">' . __( 'Contact support', 'woocommerce-advanced-shipping' ) . '</a>
					</p>',
		) );

		$screen->set_help_sidebar(
			'<p><strong>' . __( 'More links', 'woocommerce-advanced-shipping' ) . '</strong></p>' .
			'<p><a href="https://aceplugins.com/plugin/advanced-shipping-for-woocommerce/purchase" target="_blank">' . __( 'Purchase/renew license', 'woocommerce-advanced-shipping' ) . '</a></p>' .
			'<p><a href="' . admin_url( 'plugins.php#checkbox_b34f90cebbdad68ac199dc7655872b67' ) . '">' . __( 'Activate license (plugins page)', 'woocommerce-advanced-shipping' ) . '</a></p>' .
			'<p><a href="https://aceplugins.com/doc/advanced-shipping-for-woocommerce/" target="_blank">' . __( 'Documentation', 'woocommerce-advanced-shipping' ) . '</a></p>' .
			'<p><a href="https://jeroensormani.com/woocommerce-advanced-shipping/support/" target="_blank">' . __( 'Support', 'woocommerce-advanced-shipping' ) . '</a></p>' .
			'<p><a href="https://aceplugins.com/plugin/category/woocommerce/" target="_blank">' . __( 'More plugins by the Author', 'woocommerce-advanced-shipping' ) . '</a></p>'
		);

		// Make sure to not show Woo help to not confuse users
		$screen->remove_help_tab( 'woocommerce_support_tab' );
		$screen->remove_help_tab( 'woocommerce_bugs_tab' );
		$screen->remove_help_tab( 'woocommerce_education_tab' );
		$screen->remove_help_tab( 'woocommerce_onboard_tab' );
	}


	/**
	 * Updater error messages.
	 *
	 * @since 1.1.0
	 *
	 * @param string                           $message       Original message.
	 * @param array                            $response_body Response from API.
	 * @param \JeroenSormani\WP_Updater\Plugin $plugin        Plugin object.
	 * @return string                                         Modified message.
	 */
	public function wp_updater_activation_error_message( $message, $response_body, $plugin ) {

		if ( $plugin->get_name() == 'Advanced Shipping for WooCommerce' ) {

			if ( $response_body->error === 'no_activations_left' ) {
				$message = sprintf( __( 'Your license has reached its activation limit of %d, you can <a href="https://aceplugins.com/plugin/advanced-shipping-for-woocommerce/purchase" target="_blank">get a new license</a> to activate the plugin on this site.' ), $response_body->license_limit );
			} elseif ( $response_body->error === 'missing' ) {
				$message = __( 'This license appears to be invalid for the Advanced Shipping plugin. Please verify your license key and try again. <a href="https://aceplugins.com/doc/advanced-shipping-for-woocommerce#how-to-get-your-license-key" target="_blank">How to get your license key.</a>' );
			} elseif ( $response_body->error === 'expired' ) {
				$message = __( 'This license key is expired and needs to be renewed in order for dashboard updates to work and to receive support. Please <a href="https://aceplugins.com/plugin/advanced-shipping-for-woocommerce/renew" target="_blank">renew your license</a> and try again.' );
			}
		}

		return $message;
	}


	/**
	 * Update available notice.
	 *
	 * @since 1.1.0
	 *
	 * @param $html
	 * @param $name
	 * @param $args
	 * @return false|string
	 */
	public function wp_updater_update_available_message( $html, $name, $args ) {

		if ( isset( $args['plugin'] ) && $args['plugin']->get_name() == 'Advanced Shipping for WooCommerce' ) {
			$plugin = $args['plugin'];

			ob_start();
			?><div class="update-message notice inline notice-error notice-alt" style="margin: 10px 0 5px;">
				<p>
					<strong><?php echo sprintf( __( 'A new version of %s is available.' ), $plugin->get_name() ); ?><br/></strong><?php
					if ( $plugin->get_license_status() === 'expired' ) :
						echo sprintf( __( 'Your license is expired and requires renewal in order to receive dashboard updates. %s' ), '<a href="https://aceplugins.com/plugin/advanced-shipping-for-woocommerce/renew" target="_blank">Renew your license</a>.' );
					else :
						echo sprintf( __( 'Please enter a valid license key to download this update through the dashboard. If you don\'t have a license key, you may %s' ), '<a href="https://aceplugins.com/plugin/advanced-shipping-for-woocommerce/purchase" target="_blank">purchase one</a>' );
					endif;
				?></p>
			</div><?php
			$html = ob_get_clean();
		}

		return $html;
	}


	/**
	 * Set license format in WP Updater.
	 *
	 * @since 1.1.0
	 *
	 * @param string                           $format Existing format.
	 * @param \JeroenSormani\WP_Updater\Plugin $plugin WP Updater Plugin object.
	 * @return string                                  Modified format.
	 */
	public function wp_updater_license_format( $format, $plugin ) {
		if ( $plugin->get_name() === 'Advanced Shipping for WooCommerce' ) {
			$format = '[a-z0-9\-]{36}';
		}

		return $format;
	}
}
