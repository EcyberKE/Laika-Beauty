<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Hide paid shipping.
 *
 * Hide Shipping methods when a free shipping rate is available.
 *
 * @since 1.0.0
 * @since 1.0.7 - Show all free shipping rates
 * @since 1.1.0 - Moved from WAS_Advanced_Shipping_Method to separate function.
 *
 * @param  array $available_rates List of available shipping rates.
 * @return array                  List of modified available shipping rates.
 */
function was_hide_all_shipping_when_free_is_available( $available_rates ) {

	$settings = wp_parse_args(
		get_option( 'woocommerce_advanced_shipping_settings', array() ), array(
			'hide_other_shipping_when_available' => 'no'
		)
	);

	if ( 'no' == $settings['hide_other_shipping_when_available'] ) {
		return $available_rates;
	}

	$shipping_costs = wp_list_pluck( (array) $available_rates, 'cost' );
	if ( in_array( 0, $shipping_costs ) ) {
		foreach ( $available_rates as $key => $method ) {

			if ( 0 != $method->cost ) {
				unset( $available_rates[ $key ] );
			}

		}
	}

	return $available_rates;
}
add_filter( 'woocommerce_package_rates', 'was_hide_all_shipping_when_free_is_available' );


/**
 * Get available conditions.
 *
 * Get a list of the available conditions for the plugin.
 *
 * @since 1.1.0
 *
 * @return array List of available conditions.
 */
function was_get_available_conditions() {

	$conditions = array(
		__( 'Cart', 'woocommerce-advanced-shipping' ) => array(
			'subtotal'                => __( 'Subtotal', 'woocommerce-advanced-shipping' ),
			'subtotal_ex_tax'         => __( 'Subtotal ex. taxes', 'woocommerce-advanced-shipping' ),
			'tax'                     => __( 'Tax', 'woocommerce-advanced-shipping' ),
			'quantity'                => __( 'Quantity', 'woocommerce-advanced-shipping' ),
			'contains_product'        => __( 'Contains product', 'woocommerce-advanced-shipping' ),
			'coupon'                  => __( 'Coupon', 'woocommerce-advanced-shipping' ),
			'weight'                  => __( 'Weight', 'woocommerce-advanced-shipping' ),
			'contains_shipping_class' => __( 'Contains shipping class', 'woocommerce-advanced-shipping' ),
		),
		__( 'User Details', 'woocommerce-advanced-shipping' ) => array(
			'zipcode' => __( 'Zipcode', 'woocommerce-advanced-shipping' ),
			'city'    => __( 'City', 'woocommerce-advanced-shipping' ),
			'state'   => __( 'State', 'woocommerce-advanced-shipping' ),
			'country' => __( 'Country', 'woocommerce-advanced-shipping' ),
			'role'    => __( 'User role', 'woocommerce-advanced-shipping' ),
		),
		__( 'Product', 'woocommerce-advanced-shipping' ) => array(
			'width'        => __( 'Width', 'woocommerce-advanced-shipping' ),
			'height'       => __( 'Height', 'woocommerce-advanced-shipping' ),
			'length'       => __( 'Length', 'woocommerce-advanced-shipping' ),
			'stock'        => __( 'Stock', 'woocommerce-advanced-shipping' ),
			'stock_status' => __( 'Stock status', 'woocommerce-advanced-shipping' ),
			'category'     => __( 'Category', 'woocommerce-advanced-shipping' ),
		),
	);
	$conditions = apply_filters( 'was_conditions', $conditions );

	return $conditions;
}


/**
 * Current page WAS page?
 *
 * @since 1.1.0
 *
 * @return bool True when the current page is related to Advanced Shipping, false otherwise.
 */
function is_was_page() {
	$return = false;

	if (
		( isset( $_REQUEST['post'] ) && 'was' == get_post_type( $_REQUEST['post'] ) ) ||
		( isset( $_REQUEST['post_type'] ) && 'was' == $_REQUEST['post_type'] ) ||
		( isset( $_REQUEST['section'] ) && 'legacy_advanced_shipping' == $_REQUEST['section'] )
	) {
		$return = true;
	}

	// Shipping instance
	if ( isset( $_GET['tab'], $_GET['instance_id'] ) && $_GET['tab'] == 'shipping' ) {
		$instance_id     = absint( $_GET['instance_id'] );
		$shipping_method = WC_Shipping_Zones::get_shipping_method( $instance_id );
		if ( $shipping_method->id === 'advanced_shipping' ) {
			$return = true;
		}
	}

	return $return;
}


/**************************************************************
 * Backwards compatibility
 *************************************************************/

/**
 * Add filter for condition values for backwards compatibility.
 *
 * @since 1.1.0
 */
function was_add_bc_filter_condition_values( $condition ) {
	return apply_filters( 'was_match_condition_values', $condition );
}
add_action( 'wp-conditions\condition', 'was_add_bc_filter_condition_values' );


/**
 * Add the filters required for backwards-compatibility for the matching functionality.
 *
 * @since 1.1.0
 */
function was_add_bc_filter_condition_match( $match, $condition, $operator, $value, $args ) {
	if ( ! isset( $args['context'] ) || $args['context'] != 'was' ) {
		return $match;
	}

	if ( has_filter( 'was_match_condition_' . $condition ) ) {
		$package = isset( $args['package'] ) ? $args['package'] : array();
		$match = apply_filters( 'was_match_condition_' . $condition, $match = false, $operator, $value, $package );
	}

	return $match;
}
add_action( 'wp-conditions\condition\match', 'was_add_bc_filter_condition_match', 10, 5 );

/**
 * Add condition descriptions of custom conditions.
 *
 * @since 1.1.0
 */
function was_add_bc_filter_condition_descriptions( $descriptions ) {
	return apply_filters( 'was_descriptions', $descriptions );
}
add_filter( 'wp-conditions\condition_descriptions', 'was_add_bc_filter_condition_descriptions' );

/**
 * Add custom field BC.
 *
 * @since 1.1.0
 */
function was_add_bc_action_custom_fields( $type, $args ) {
	if ( has_action( 'was_condition_value_field_type_' . $type ) ) {
		do_action( 'was_condition_value_field_type_' . $args['type'], $args );
	}
	if ( has_action( 'woocommerce_advanced_shipping_condition_value_field_type_' . $type ) ) {
		do_action( 'woocommerce_advanced_shipping_condition_value_field_type_' . $args['type'], $args );
	}
}
add_action( 'wp-conditions\html_field_hook', 'was_add_bc_action_custom_fields', 10, 2 );
