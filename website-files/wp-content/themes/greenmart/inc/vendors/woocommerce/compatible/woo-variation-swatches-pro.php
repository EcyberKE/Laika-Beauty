<?php

if (!greenmart_is_woo_variation_swatches_pro()) {
    return;
}

if (!function_exists('greenmart_quantity_swatches_pro_field_archive')) {
    function greenmart_quantity_swatches_pro_field_archive()
    {
        global $product;
        if (greenmart_is_quantity_field_archive()) {
            woocommerce_quantity_input(['min_value' => 1, 'max_value' => $product->backorders_allowed() ? '' : $product->get_stock_quantity()]);
        }
    }
}

if (!function_exists('greenmart_variation_swatches_pro_group_button')) {
    function greenmart_variation_swatches_pro_group_button()
    {
        if (!greenmart_is_woo_variation_swatches_pro()) {
            return;
        }

        $class_active = '';

        if (greenmart_tbay_woocommerce_quantity_mode_active()) {
            $class_active .= 'quantity-group-btn';

            if (greenmart_is_quantity_field_archive()) {
                $class_active .= ' active';
            }
        } else {
            $class_active .= 'woo-swatches-pro-btn';
        }

        echo '<div class="'.esc_attr($class_active).'">';

        if (greenmart_tbay_woocommerce_quantity_mode_active()) {
            greenmart_quantity_swatches_pro_field_archive();
        }

        woocommerce_template_loop_add_to_cart();
        echo '</div>';
    }
    add_action('woocommerce_after_shop_loop_item', 'greenmart_variation_swatches_pro_group_button', 5);
}
