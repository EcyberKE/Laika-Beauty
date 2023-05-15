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
class WAS_Advanced_Shipping_Method extends WC_Shipping_Method {

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param int $instance_id
	 */
	public function __construct( $instance_id = 0 ) {
		parent::__construct( $instance_id );

		$this->id                 = 'advanced_shipping';
		$this->method_title       = __( 'Advanced Shipping', 'woocommerce-advanced-shipping' );
		$this->method_description = __( 'Flexible and conditional shipping rate', 'woocommerce-advanced-shipping' );
		$this->supports           = array( 'shipping-zones', 'instance-settings' );

		$this->init();

		do_action( 'woocommerce_advanced_shipping_method_init', $this );
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

		$this->instance_form_fields = $this->get_instance_settings();
		$this->title                = $this->get_instance_option( 'title', $this->method_title );
	}


	/**
	 * Instance settings.
	 *
	 * Get the instance settings array.
	 *
	 * @since 1.1.0
	 *
	 * @return array List of instance settings.
	 */
	public function get_instance_settings() {

		return array(
			'title' => array(
				'title'       => __( 'Title', 'woocommerce-advanced-shipping' ),
				'type'        => 'title_text',
				'placeholder' => __( 'Shipping title', 'woocommerce-advanced-shipping' ),
			),
			'advanced_shipping_meta_boxes' => array(
				'type' => 'advanced_shipping_meta_boxes',
				'sanitize_callback' => array( $this, 'save_settings' )
			),

			// 'none' type as we use a custom output (BC), but want to utilize the get/set functionality of $instance_settings
			'conditions'      => array( 'type' => 'none' ),
//			'shipping_title'  => array( 'type' => 'none' ),
			'shipping_cost'   => array( 'type' => 'none' ),
			'handling_fee'    => array( 'type' => 'none' ),
			'cost_per_weight' => array( 'type' => 'none' ),
			'cost_per_item'   => array( 'type' => 'none' ),
			'tax'             => array( 'type' => 'none' )
		);
	}


	/**
	 * Override to actually check for key / prevent undefined notice with the custom fields.
	 *
	 * This method is documented in abstract-wc-shipping-method.php
	 *
	 * @see WC_Shipping_Method::get_instance_option( $key, $empty_value )
	 */
	public function get_instance_option( $key, $empty_value = null ) {
		if ( empty( $this->instance_settings ) ) {
			$this->init_instance_settings();
		}

		if ( isset( $this->instance_settings[ $key ] ) ) {
			return parent::get_instance_option( $key, $empty_value );
		} else {
			return $empty_value;
		}
	}


	/**
	 * Don't output for 'none' field types.
	 */
	public function generate_none_html() {}


	/**
	 * Validate conditions.
	 *
	 * @param $key
	 * @param $value
	 * @return array
	 */
	public function validate_conditions_field( $key, $value ) {
		return wpc_sanitize_conditions( $_POST[ $key ] );
	}


	/**
	 * Validate other field settings.
	 *
	 * @param $key
	 * @param $value
	 * @return string
	 */
	public function validate_none_field( $key, $value ) {
		$value = $_POST['_was_shipping_method'][ $key ];

		switch ( $key ) {
			case 'cost_per_weight' :
				$value = wc_format_decimal( $value );
				break;

			case 'shipping_cost' :
			case 'handling_fee' :
			case 'cost_per_item' :
				$value = preg_replace( '/[^0-9\%\.\,\-]/', '', $value );
				break;

			case 'tax' :
				$value = 'taxable' == $value ? 'taxable' : 'not_taxable';
				break;

			case 'shipping_title' :
			default :
				$value = $this->validate_text_field( $key, $value );
				break;
		}

		return $value;
	}


	/**
	 * Instance custom fields.
	 *
	 * Load and render the 'advanced_shipping_meta_boxes' field type.
	 *
	 * @since 1.1.0
	 *
	 * @param  mixed  $key
	 * @param  mixed  $data
	 * @return string
	 */
	public function generate_advanced_shipping_meta_boxes_html( $key, $data ) {
		$condition_groups = $this->get_instance_option( 'conditions' );
		$shipping_title   = $this->get_instance_option( 'shipping_title' );
		$shipping_cost    = $this->get_instance_option( 'shipping_cost' );
		$handling_fee     = $this->get_instance_option( 'handling_fee' );
		$cost_per_weight  = $this->get_instance_option( 'cost_per_weight' );
		$cost_per_item    = $this->get_instance_option( 'cost_per_item' );
		$tax              = $this->get_instance_option( 'tax' );

		ob_start();
		?>
		</table><!-- Close the table -->

		<div class="was-meta-box-wrap" style="margin-right: 300px;" id="poststuff">
			<div id="was_conditions" class="postbox ">
				<h2 class="" style="border-bottom: 1px solid #eee;"><span><?php _e( 'Conditions', 'woocommerce-advanced-shipping' ); ?></span></h2>
				<div class="inside"><?php
					require_once plugin_dir_path( __FILE__ ) . 'admin/views/meta-box-conditions.php';
				?></div>
			</div>

			<div id="was_settings" class="postbox ">
				<style>.was-shipping-title { display: none; }</style>
				<h2 class="" style="border-bottom: 1px solid #eee;"><span><?php _e( 'Settings', 'woocommerce-advanced-shipping' ); ?></span></h2>
				<div class="inside"><?php
					require_once plugin_dir_path( __FILE__ ) . 'admin/views/meta-box-settings.php';
				?></div>
			</div>
		</div>
		<table class="form-table"><!-- Re-open table tab --><?php

		return ob_get_clean();
	}


	/**
	 * Generate title text field.
	 *
	 * Generate the HTML for the title text field.
	 *
	 * @since 1.1.0
	 *
	 * @param  mixed  $key
	 * @param  mixed  $data
	 * @return string
	 */
	public function generate_title_text_html( $key, $data ) {

		$field_key = $this->get_field_key( $key );
		$defaults  = array(
			'title'             => 'test',
			'class'             => '',
			'css'               => '',
			'placeholder'       => '',
			'type'              => 'text',
			'custom_attributes' => array(),
		);

		$data = wp_parse_args( $data, $defaults );

		ob_start();
			?></table>
			<div class="was-title-text-wrap">
				<legend class="screen-reader-text"><span><?php echo wp_kses_post( $data['title'] ); ?></span></legend>
				<input class="input-text regular-input was-title-text <?php echo esc_attr( $data['class'] ); ?>"
					   type="text" name="<?php echo esc_attr( $field_key ); ?>"
					   id="<?php echo esc_attr( $field_key ); ?>" style="<?php echo esc_attr( $data['css'] ); ?>"
					   value="<?php echo esc_attr( $this->get_option( $key ) ); ?>"
					   placeholder="<?php echo esc_attr( $data['placeholder'] ); ?>" <?php echo $this->get_custom_attribute_html( $data ); ?> />
			</div>
			<table class="form-table"><!-- Re-open table tab --><?php
		return ob_get_clean();
	}


	/**
	 * Item cost.
	 *
	 * Calculate the costs per item.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $cost_per_item
	 * @param mixed $package        List containing all products for this method.
	 * @return float                Shipping costs.
	 */
	protected function calculate_cost_per_item( $cost_per_item, $package ) {
		$cost = 0;

		// Shipping per item
		foreach ( $package['contents'] as $item_id => $values ) {

			/** @var WC_Product $_product */
			$_product = $values['data'];
			if ( $values['quantity'] > 0 && $_product->needs_shipping() ) {

				if ( strstr( $cost_per_item, '%' ) ) {
					$cost += ( $values['line_total'] / 100 ) * (float) str_replace( '%', '', $cost_per_item );
				} else {
					$cost += $values['quantity'] * (float) $cost_per_item;
				}
			}
		}

		return $cost;
	}


	/**
	 * Weight cost.
	 *
	 * Calculate the costs per weight.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $cost_per_weight
	 * @param mixed $package         List containing all products for this method.
	 * @return float                 Shipping costs.
	 */
	protected function calculate_cost_per_weight( $cost_per_weight, $package ) {
		$cost = 0;

		foreach ( $package['contents'] as $item_id => $item ) {

			/** @var WC_Product $_product */
			$_product = $item['data'];
			if ( $item['quantity'] > 0 && $_product->needs_shipping() && $_product->get_weight() ) {
				$cost += ( ( $item['quantity'] * $_product->get_weight() ) * (float) $cost_per_weight );
			}
		}

		return $cost;
	}


	/**
	 * Calculate costs.
	 *
	 * Calculate the shipping costs for this method.
	 *
	 * @since 1.1.0
	 *
	 * @param mixed  $package List containing all products for this method.
	 * @param string $rate_id Shipping method ID.
	 * @param array  $args
	 * @return float           Shipping costs.
	 */
	protected function calculate_shipping_cost( $package, $rate_id, $args = array() ) {
		$cost  = (float) $args['cost'];
		$cost += (float) $this->get_fee( $args['fee'], $package['contents_cost'] );
		$cost += (float) $this->calculate_cost_per_item( $args['cost_per_item'], $package );
		$cost += (float) $this->calculate_cost_per_weight( $args['cost_per_weight'], $package );

		return apply_filters( 'was_calculate_shipping_costs', $cost, $package, $rate_id, $this );
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

		$conditions = $this->get_instance_option( 'conditions' );

		// Ensure conditions match
		if ( wpc_match_conditions( $conditions, array( 'context' => 'was', 'package' => $package ) ) ) {

			$cost = $this->calculate_shipping_cost( $package, $this->get_rate_id(), array(
				'cost'            => $this->get_instance_option( 'shipping_cost' ),
				'fee'             => $this->get_instance_option( 'handling_fee' ),
				'cost_per_item'   => $this->get_instance_option( 'cost_per_item' ),
				'cost_per_weight' => $this->get_instance_option( 'cost_per_weight' ),
			) );

			// Add rates added through the WC Zones
			$rate_args = apply_filters( 'was_shipping_rate', array(
				'id'       => $this->get_rate_id(),
				'label'    => $this->get_instance_option( 'title' ),
				'cost'     => $cost,
				'taxes'    => ( 'taxable' == $this->get_instance_option( 'tax' ) ) ? '' : false,
				'calc_tax' => 'per_order',
				'package'  => $package,
			), $package, $this );

			$this->add_rate( $rate_args );
		}
	}
}
