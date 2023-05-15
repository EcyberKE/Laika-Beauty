<?php
/**
 * Filter tabs and allow third parties to add their own.
 *
 * Each tab is an array containing title, callback and priority.
 * @see woocommerce_default_product_tabs()
 */
$product_tabs = apply_filters( 'woocommerce_product_tabs', array() );
$i = 0;
$skin = greenmart_tbay_get_theme();

if ( ! empty( $product_tabs ) ) : ?>

	<div class="woocommerce-tabs-full tabs-v1 woocommerce-tabs">
		<div class="tab-content">
			<?php foreach ( $product_tabs as $key => $product_tab ) : ?>
				<div class="tab-full" id="tab-<?php echo esc_attr( $key ); ?>">
					<?php call_user_func( $product_tab['callback'], $key, $product_tab ); ?>
				</div>
			<?php $i++; endforeach; ?>
		</div>

    <?php do_action( 'woocommerce_product_after_tabs' ); ?>
	</div>
<?php endif; ?>