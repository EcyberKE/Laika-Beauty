<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class WAS_Post_Type.
 *
 * Initialize the was post type.
 *
 * @class		WAS_post_type
 * @author		Jeroen Sormani
 * @package		WooCommerce Advanced Shipping
 * @version		1.0.0
 */
class WAS_Post_Type {


	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// Register post type
		add_action( 'init', array( $this, 'register_post_type' ) );

		// Add/save meta boxes
		add_action( 'add_meta_boxes', array( $this, 'post_type_meta_box' ) );
		add_action( 'save_post', array( $this, 'save_meta' ) );

		// Edit user notices
		add_filter( 'post_updated_messages', array( $this, 'custom_post_type_messages' ) );

		// Redirect after delete
		add_action( 'load-edit.php', array( $this, 'redirect_after_trash' ) );

	}


	/**
	 * Post type.
	 *
	 * Register the 'was' post type.
	 *
	 * @since 1.0.0
	 */
	public function register_post_type() {

		$labels = array(
			'name'               => __( 'Advanced Shipping methods', 'woocommerce-advanced-shipping' ),
			'singular_name'      => __( 'Advanced Shipping method', 'woocommerce-advanced-shipping' ),
			'add_new'            => __( 'Add New', 'woocommerce-advanced-shipping' ),
			'add_new_item'       => __( 'Add New Advanced Shipping method', 'woocommerce-advanced-shipping' ),
			'edit_item'          => __( 'Edit Advanced Shipping method', 'woocommerce-advanced-shipping' ),
			'new_item'           => __( 'New Advanced Shipping method', 'woocommerce-advanced-shipping' ),
			'view_item'          => __( 'View Advanced Shipping method', 'woocommerce-advanced-shipping' ),
			'search_items'       => __( 'Search Advanced Shipping methods', 'woocommerce-advanced-shipping' ),
			'not_found'          => __( 'No Advanced Shipping methods', 'woocommerce-advanced-shipping' ),
			'not_found_in_trash' => __( 'No Advanced Shipping methods found in Trash', 'woocommerce-advanced-shipping' ),
		);

		register_post_type( 'was', array(
			'label'           => 'was',
			'show_ui'         => true,
			'show_in_menu'    => false,
			'capability_type' => 'post',
			'map_meta_cap'    => true,
			'rewrite'         => array( 'slug' => 'was', 'with_front' => true ),
			'_builtin'        => false,
			'query_var'       => true,
			'supports'        => array( 'title' ),
			'labels'          => $labels,
		) );

	}


	/**
	 * Messages.
	 *
	 * Modify the notice messages text for the 'was' post type.
	 *
	 * @since 1.0.0
	 *
	 * @param  array $messages Existing list of messages.
	 * @return array           Modified list of messages.
	 */
	function custom_post_type_messages( $messages ) {

		$post             = get_post();
		$post_type        = get_post_type( $post );
		$post_type_object = get_post_type_object( $post_type );

		$messages['was'] = array(
			0  => '',
			1  => __( 'Advanced shipping method updated.', 'woocommerce-advanced-shipping' ),
			2  => __( 'Custom field updated.', 'woocommerce-advanced-shipping' ),
			3  => __( 'Custom field deleted.', 'woocommerce-advanced-shipping' ),
			4  => __( 'Advanced shipping method updated.', 'woocommerce-advanced-shipping' ),
			5  => isset( $_GET['revision'] ) ?
				sprintf( __( 'Advanced shipping method restored to revision from %s', 'woocommerce-advanced-shipping' ), wp_post_revision_title( (int) $_GET['revision'], false ) )
				: false,
			6  => __( 'Advanced shipping method published.', 'woocommerce-advanced-shipping' ),
			7  => __( 'Advanced shipping method saved.', 'woocommerce-advanced-shipping' ),
			8  => __( 'Advanced shipping method submitted.', 'woocommerce-advanced-shipping' ),
			9  => sprintf(
				__( 'Advanced shipping method scheduled for: <strong>%1$s</strong>.', 'woocommerce-advanced-shipping' ),
				date_i18n( __( 'M j, Y @ G:i', 'woocommerce-advanced-shipping' ), strtotime( $post->post_date ) )
			),
			10 => __( 'Advanced shipping method draft updated.', 'woocommerce-advanced-shipping' ),
		);

		$permalink            = admin_url( '/admin.php?page=wc-settings&tab=shipping&section=legacy_advanced_shipping' );
		$overview_link        = sprintf( ' <a href="%s">%s</a>', esc_url( $permalink ), __( 'Return to overview.', 'woocommerce-advanced-shipping' ) );
		$messages['was'][1]  .= $overview_link;
		$messages['was'][6]  .= $overview_link;
		$messages['was'][9]  .= $overview_link;
		$messages['was'][8]  .= $overview_link;
		$messages['was'][10] .= $overview_link;

		return $messages;

	}


	/**
	 * Meta boxes.
	 *
	 * Add two meta boxes to the 'was' post type.
	 *
	 * @since 1.0.0
	 */
	public function post_type_meta_box() {

		add_meta_box( 'was_conditions', __( 'Advanced Shipping conditions', 'woocommerce-advanced-shipping' ), array( $this, 'render_conditions' ), 'was', 'normal' );
		add_meta_box( 'was_settings', __( 'Shipping settings', 'woocommerce-advanced-shipping' ), array( $this, 'render_settings' ), 'was', 'normal' );
		add_meta_box( 'was_migrate', __( 'Migrate to Zones', 'woocommerce-advanced-shipping' ), array( $this, 'render_migrate' ), 'was', 'side' );

	}


	/**
	 * Render meta box.
	 *
	 * Get conditions meta box contents.
	 *
	 * @since 1.0.0
	 */
	public function render_conditions() {

		global $post;
		$condition_groups = get_post_meta( $post->ID, '_was_shipping_method_conditions', true );

		require_once plugin_dir_path( __FILE__ ) . 'admin/views/meta-box-conditions.php';

	}


	/**
	 * Render meta box.
	 *
	 * Get settings meta box contents.
	 *
	 * @since 1.0.0
	 */
	public function render_settings() {

		global $post;

		$settings = (array) get_post_meta( $post->ID, '_was_shipping_method', true );
		// Make sure variables exist
		extract( wp_parse_args( $settings, array(
			'shipping_title'  => '',
			'shipping_cost'   => '',
			'handling_fee'    => '',
			'cost_per_weight' => '',
			'cost_per_item'   => '',
			'tax'             => '',
		) ) );
		wp_nonce_field( 'was_settings_meta_box', 'was_settings_meta_box_nonce' );

		require_once plugin_dir_path( __FILE__ ) . 'admin/views/meta-box-settings.php';

	}


	/**
	 * Render WC Zones migration meta box.
	 *
	 * @since 1.1.0
	 */
	public function render_migrate() {

		$zones = WC_Shipping_Zones::get_zones();
		?><p><?php _e( 'Migrate this shipping rate to a WC Zone', 'woocommerce-advanced-shipping' ); ?></p>

		<p>
			<select name="migrate_zone" id="migrate_zone" placeholder="Test">
				<option readonly="readonly" value=""><?php _e( 'Select a zone', 'woocommerce-advanced-shipping' ); ?></option><?php
				foreach ( $zones as $zone ) :
					?><option value="<?php echo $zone['id']; ?>"><?php echo $zone['zone_name']; ?></option><?php
				endforeach;
				?><option value="0"><?php _e( 'Rest of the world', 'woocommerce-advanced-shipping' ); ?></option>
			</select>
			<a href="javascript:void(0);" class="migrate button-secondary"><?php _e( 'Migrate', 'woocommerce-advanced-shipping' ); ?></a>
		</p>
		<p><a href="https://aceplugins.com/doc/advanced-shipping-for-woocommerce/update-1-1-0/#migrate-to-zones" target="_blank"><?php _e( 'More information', 'woocommerce-advanced-shipping' ); ?></a></p><?php

	}


	/**
	 * Save meta.
	 *
	 * Validate and save post meta. This value contains all
	 * the normal shipping method settings (no conditions).
	 *
	 * @since 1.0.0
	 *
	 * @param int/numeric $post_id ID of the post being saved.
	 */
	public function save_meta( $post_id ) {

		if ( ! isset( $_POST['was_settings_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['was_settings_meta_box_nonce'], 'was_settings_meta_box' ) ) {
			return $post_id;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return $post_id;
		}

		// Save the settings
		$shipping_method                    = $_POST['_was_shipping_method'];
		$shipping_method['shipping_title']  = sanitize_text_field( $shipping_method['shipping_title'] );
		$shipping_method['shipping_cost']   = preg_replace( '/[^0-9\%\.\,\-]/', '', $shipping_method['shipping_cost'] );
		$shipping_method['handling_fee']    = preg_replace( '/[^0-9\%\.\,\-]/', '', $shipping_method['handling_fee'] );
		$shipping_method['cost_per_weight'] = wc_format_decimal( $shipping_method['cost_per_weight'] );
		$shipping_method['cost_per_item']   = preg_replace( '/[^0-9\%\.\,\-]/', '', $shipping_method['cost_per_item'] );
		$shipping_method['tax']             = 'taxable' == $shipping_method['tax'] ? 'taxable' : 'not_taxable';

		update_post_meta( $post_id, '_was_shipping_method', $shipping_method );

		// Save the conditions
		update_post_meta( $post_id, '_was_shipping_method_conditions', wpc_sanitize_conditions( $_POST['conditions'] ) );

		do_action( 'was_save_shipping_settings', $post_id );
		do_action( 'was_save_shipping_conditions', $post_id );

	}


	/**
	 * Redirect trash.
	 *
	 * Redirect user after trashing a WAS post.
	 *
	 * @since 1.0.0
	 */
	public function redirect_after_trash() {

		$screen = get_current_screen();

		if ( 'edit-was' == $screen->id ) :

			if ( isset( $_GET['trashed'] ) && intval( $_GET['trashed'] ) > 0 ) :

				$redirect = admin_url( '/admin.php?page=wc-settings&tab=shipping&section=advanced_shipping_legacy' );
				wp_redirect( $redirect );
				exit();

			endif;

		endif;

	}


}

/**
 * Load condition object
 */
require_once plugin_dir_path( __FILE__ ) . 'admin/class-was-condition.php';
