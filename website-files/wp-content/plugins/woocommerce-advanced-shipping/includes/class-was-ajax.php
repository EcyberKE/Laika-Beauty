<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * AJAX class.
 *
 * Handles all AJAX related calls.
 *
 * @author		Jeroen Sormani
 * @version		1.0.0
 */
class WAS_Ajax {


	/**
	 * Constructor.
	 *
	 * Add ajax actions in order to work.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		// Update elements
		add_action( 'wp_ajax_was_update_condition_value', array( $this, 'update_condition_value' ) );

		// Save post ordering
		add_action( 'wp_ajax_was_save_post_order', array( $this, 'save_post_order' ) );

		// Migrate rate to WC Zones
		add_action( 'wp_ajax_was_migrate_to_zone', array( $this, 'migrate_was_rate' ) );

		// Dismiss license notice
		add_action( 'wp_ajax_was_dismiss_notice', array( $this, 'dismiss_license_notice' ) );
	}


	/**
	 * Update condition value field.
	 *
	 * Output the HTML of the value field according to the condition key..
	 *
	 * @since 1.0.0
	 */
	public function update_condition_value() {
		check_ajax_referer( 'wpc-ajax-nonce', 'nonce' );

		$wp_condition     = new WAS_Condition( $_POST['id'], $_POST['group'], $_POST['condition'] );
		$value_field_args = $wp_condition->get_value_field_args();

		?><span class='wpc-value-field-wrap'><?php
			wpc_html_field( $value_field_args );
		?></span><?php

		die();
	}


	/**
	 * Save order.
	 *
	 * Save the order of the posts in the overview table.
	 *
	 * @since 1.0.4
	 */
	public function save_post_order() {
		global $wpdb;

		check_ajax_referer( 'wpc-ajax-nonce', 'nonce' );

		$args = wp_parse_args( $_POST['form'] );

		$menu_order = 0;
		foreach ( $args['sort'] as $sort ) {

			$wpdb->update(
				$wpdb->posts,
				array( 'menu_order' => $menu_order ),
				array( 'ID' => $sort ),
				array( '%d' ),
				array( '%d' )
			);

			$menu_order ++;
		}

		die;
	}


	/**
	 * Migrate to zones.
	 *
	 * Migrate a legacy shipping rate to a WC Shipping Zone.
	 *
	 * @since 1.1.0
	 */
	public function migrate_was_rate() {
		check_ajax_referer( 'advanced-shipping', 'nonce' );

		// Add to zone
		if ( ! $zone = WC_Shipping_Zones::get_zone( absint( $_POST['zone'] ) ) ) {
			wp_send_json( array(
				'error' => __( 'Zone not found' ),
			) );
		}

		$old_rate = get_post( absint( $_POST['rate_id'] ) );
		if ( ! $old_rate || $old_rate->post_type !== 'was' ) {
			wp_send_json( array(
				'error' => __( 'Invalid shipping rate' ),
			) );
		}

		$new_instance_id = $zone->add_shipping_method( 'advanced_shipping' );
		$rate            = WC_Shipping_Zones::get_shipping_method( $new_instance_id );

		$data = get_post_meta( $old_rate->ID, '_was_shipping_method', true );

		$rate->instance_settings = array(
			'title'                                    => $data['shipping_title'],
			'conditions'                               => get_post_meta( $old_rate->ID, '_was_shipping_method_conditions', true ),
//			'shipping_title'                           => $data['shipping_title'],
			'shipping_cost'                            => $data['shipping_cost'],
			'handling_fee'                             => $data['handling_fee'],
			'cost_per_weight'                          => $data['cost_per_weight'],
			'cost_per_item'                            => $data['cost_per_item'],
			'tax'                                      => $data['tax'],
			'advanced_pricing_cost_per_weight'         => get_post_meta( $old_rate->ID, 'advanced_pricing_cost_per_weight', true ),
			'advanced_pricing_cost_per_shipping_class' => get_post_meta( $old_rate->ID, 'advanced_pricing_cost_per_shipping_class', true ),
			'advanced_pricing_cost_per_category'       => get_post_meta( $old_rate->ID, 'advanced_pricing_cost_per_category', true ),
			'advanced_pricing_cost_per_product'        => get_post_meta( $old_rate->ID, 'advanced_pricing_cost_per_product', true ),
		);

		update_option( $rate->get_instance_option_key(), $rate->instance_settings, 'yes' );

		// Trash legacy rate
		wp_trash_post( $old_rate->ID );

		wp_send_json( array(
			'redirect' => esc_url_raw( admin_url( 'admin.php?page=wc-settings&tab=shipping&instance_id=' . $rate->instance_id ) ),
		) );
	}


	/**
	 * Dismiss license notice.
	 *
	 * @since 1.1.0
	 */
	public function dismiss_license_notice() {
		$settings = get_option( 'woocommerce_advanced_shipping_settings', array() );
		$settings['notices']['dismissed_license_invalid'] = time();

		$success = update_option( 'woocommerce_advanced_shipping_settings', $settings );

		wp_send_json( array(
			'success' => $success
		) );
	}
}
