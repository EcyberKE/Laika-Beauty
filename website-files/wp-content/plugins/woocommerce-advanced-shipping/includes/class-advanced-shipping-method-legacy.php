<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( class_exists( 'WAS_Advanced_Shipping_Method' ) ) return; // Stop if the class already exists

/**
 * Class WAS_Advanced_Shipping_Method.
 *
 * WooCommerce Advanced Shipping method class.
 *
 * @class		WAS_Advanced_Shipping_Method
 * @author		Jeroen Sormani
 * @package		WooCommerce Advanced Shipping
 * @version		1.0.0
 */
class WAS_Advanced_Shipping_Method_Legacy extends WAS_Advanced_Shipping_Method {


	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param int $instance_id Instance ID.
	 */
	public function __construct( $instance_id = 0 ) {
		parent::__construct( $instance_id );

		$this->id                 = 'legacy_advanced_shipping';
		$this->title              = __( 'Shipping (configurable per rate)', 'woocommerce-advanced-shipping' );
		$this->method_title       = __( 'Advanced Shipping', 'woocommerce-advanced-shipping' );
		$this->method_description = __( 'Configure Advanced Shipping rates here or use Advanced Shipping in the <a href="' . admin_url( 'admin.php?page=wc-settings&tab=shipping&section=' ) . '">Shipping Zones</a>.', 'woocommerce-advanced-shipping' );
		$this->supports           = array( 'settings' );

		$this->init();

		do_action( 'woocommerce_advanced_shipping_method_init', $this );

		// Save settings
		add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );

	}


	/**
	 * Init.
	 *
	 * Initialize WAS shipping method.
	 *
	 * @since 1.0.0
	 */
	function init() {
		$this->init_form_fields();
		$this->init_settings();

		$this->enabled = $this->get_option( 'enabled' );
	}


	/**
	 * Return the name of the option in the WP DB.
	 *
	 * @since 1.1.0
	 * @return string
	 */
	public function get_option_key() {
		return $this->plugin_id . 'advanced_shipping_settings';
	}


	/**
	 * Match methods.
	 *
	 * Checks all created WAS shipping methods have a matching condition group.
	 *
	 * @since 1.0.0
	 *
	 * @param  array $package List of shipping package data.
	 * @return array          List of all matched shipping methods.
	 */
	public function match_methods( $package ) {
		$matched_methods = array();
		$methods         = get_posts( array(
			'posts_per_page'   => '-1',
			'post_type'        => 'was',
			'orderby'          => 'menu_order date',
			'order'            => 'ASC',
			'suppress_filters' => false
		) );

		foreach ( $methods as $method ) {
			$condition_groups = get_post_meta( $method->ID, '_was_shipping_method_conditions', true );

			if ( wpc_match_conditions( $condition_groups, array( 'context' => 'was', 'package' => $package ) ) ) {
				$matched_methods[] = $method->ID;
			}
		}

		return $matched_methods;
	}


	/**
	 * Init fields.
	 *
	 * Add fields to the WAS shipping settings page.
	 *
	 * @since 1.0.0
	 */
	public function init_form_fields() {
		$this->form_fields = array(
			'enabled'                            => array(
				'title'   => __( 'Enable/Disable', 'woocommerce' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable Advanced Shipping', 'woocommerce-advanced-shipping' ),
				'default' => 'yes'
			),
			'hide_other_shipping_when_available' => array(
				'title'   => __( 'Hide other shipping', 'woocommerce-advanced-shipping' ),
				'type'    => 'checkbox',
				'label'   => __( 'Hide other shipping methods when free shipping is available', 'woocommerce-advanced-shipping' ),
				'default' => 'no'
			),
			'was_shipping_rates_table'           => array(
				'type' => 'was_shipping_rates_table',
			),
		);
	}


	/**
	 * Settings tab table.
	 *
	 * Load and render the table on the Advanced Shipping settings tab.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function generate_was_shipping_rates_table_html() {
		ob_start();
			require plugin_dir_path( __FILE__ ) . 'admin/views/shipping-rates-table.php';
		return ob_get_clean();
	}


	/**
	 * Calculate shipping.
	 *
	 * Calculate the shipping and set settings.
	 *
	 * @since 1.0.0
	 *
	 * @param array $package List containing all products for this method.
	 */
	public function calculate_shipping( $package = array() ) {
		$matched_methods = $this->match_methods( $package );

		if ( false == $matched_methods || ! is_array( $matched_methods ) || 'no' == $this->enabled ) {
			return;
		}

		foreach ( $matched_methods as $method_id ) {
			$match_details = get_post_meta( $method_id, '_was_shipping_method', true );
			$cost = $this->calculate_shipping_cost( $package, $method_id, array(
				'cost'            => str_replace( ',', '.', $match_details['shipping_cost'] ),
				'fee'             => str_replace( ',', '.', $match_details['handling_fee'] ),
				'cost_per_item'   => str_replace( ',', '.', $match_details['cost_per_item'] ),
				'cost_per_weight' => str_replace( ',', '.', $match_details['cost_per_weight'] ),
			) );

			$taxable = isset( $match_details['tax'] ) ? $match_details['tax'] : '';

			$rate = apply_filters( 'was_shipping_rate', array(
				'id'       => $method_id,
				'label'    => isset( $match_details['shipping_title'] ) ? $match_details['shipping_title'] : __( 'Shipping', 'woocommerce-advanced-shipping' ),
				'cost'     => $cost,
				'taxes'    => ( 'taxable' == $taxable ) ? '' : false,
				'calc_tax' => 'per_order',
				'package'  => $package,
			), $package, $this );

			$this->add_rate( $rate );
		}
	}
}
