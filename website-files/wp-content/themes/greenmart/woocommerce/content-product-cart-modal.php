<?php
/**
 * Mini-cart.
 *
 * Contains the markup for the mini-cart, used by the cart widget
 *
 * @author 		WooThemes
 *
 * @version     2.1.0
 */
if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

global $woocommerce;
?>

<div class="row popup-cart">

	<?php if (sizeof(WC()->cart->get_cart()) > 0) : ?>

		<?php
            $object_product = wc_get_product($product_id);

            $product_name = $object_product->get_name();
            $thumbnail = $object_product->get_image();
            $product_price = WC()->cart->get_product_price($object_product); ?>
				<div class="col-md-6 col-sm-12 col-xs-12">
					<h2 class="title-add"><i class="zmdi zmdi-check-circle"></i> <?php esc_html_e('Product added', 'greenmart'); ?></h2>
					<div class="media widget-product">
						<a href="<?php echo esc_url(get_permalink($product_id)); ?>" class="image pull-left">
							<?php echo trim($thumbnail); ?>
						</a>
						<div class="cart-main-content media-body">
							
							<h3 class="name">
								<a href="<?php echo esc_url(get_permalink($product_id)); ?>">
									<?php echo trim($product_name); ?>
								</a>
							</h3>
							<p class="cart-item">
								<?php
                                    // Loop through cart items
                                    foreach (WC()->cart->get_cart() as $cart_item) {
                                        if (in_array($product_id, [$cart_item['product_id'], $cart_item['variation_id']])) {
                                            $quantity = $cart_item['quantity'];
                                            break; // stop the loop if product is found
                                        }
                                    }

                                    echo '<span class="quantity">'.trim($quantity).' &times; '.trim($product_price).'</span>';
                                ?>
							</p>

						</div>
					</div>
				</div>
				<div class="col-md-6 cart col-sm-12 col-xs-12">
					<div>
						<div class="total"><strong><?php esc_html_e('Subtotal', 'greenmart'); ?>:</strong> <?php echo WC()->cart->get_cart_subtotal(); ?></div>
						<?php do_action('woocommerce_widget_shopping_cart_before_buttons'); ?>

						<div class="gr-buttons clearfix ">
							<a href="<?php echo esc_url(wc_get_cart_url()); ?>" class="btn btn-default">
								<?php echo sprintf(_n('VIEW CART (%d)', 'VIEW CART (%d)', $woocommerce->cart->cart_contents_count, 'greenmart'), $woocommerce->cart->cart_contents_count); ?>
								<i class="<?php echo greenmart_get_icon('icon_cart'); ?>"></i>
							</a>
							<a href="<?php echo esc_url(wc_get_checkout_url()); ?>" class="btn btn-default pull-right checkout"><?php esc_html_e('CHECKOUT', 'greenmart'); ?> <i class="icon-key icons"></i></a>
						</div>
					</div>
				</div>
				<?php
        ?>
		

	<?php else : ?>

		<div class="empty"><?php esc_html_e('No products in the cart.', 'greenmart'); ?></div>

	<?php endif; ?>

</div>