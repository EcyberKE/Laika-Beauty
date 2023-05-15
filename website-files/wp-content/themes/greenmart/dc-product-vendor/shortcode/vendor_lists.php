<?php
/**
 * The template for displaying vendor lists
 *
 * Override this template by copying it to yourtheme/dc-product-vendor/shortcode/vendor_lists.php
 *
 * @author 		WC Marketplace
 * @package 	Mvx/Templates
 * @version   2.2.0
 */
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly
global $MVX;
?>

<div id="mvx-store-conatiner">
    <!-- Map Start -->
    <div class="mvx-store-locator-wrap">
        <?php if(apply_filters('mvx_vendor_list_enable_store_locator_map', true)) : ?>
        <div id="mvx-vendor-list-map" class="mvx-store-map-wrapper"></div>
        <form name="vendor_list_sort" method="post">
            <input type="hidden" id="mvx_vlist_center_lat" name="mvx_vlist_center_lat" value=""/>
            <input type="hidden" id="mvx_vlist_center_lng" name="mvx_vlist_center_lng" value=""/>
            <div class="mvx-store-map-filter">
                <div class="mvx-inp-wrap mvx-email">
                    <input type="text" name="locationText" id="locationText" placeholder="<?php esc_attr_e('Enter Address', 'greenmart'); ?>" value="<?php echo isset($request['locationText']) ? $request['locationText'] : ''; ?>">
                </div>
                <div class="mvx-inp-wrap mvx-within">
                    <select name="radiusSelect" id="radiusSelect">
                        <option value=""><?php esc_html_e('Within', 'greenmart'); ?></option>
                        <?php if($radius) :
                        $selected_radius = isset($request['radiusSelect']) ? $request['radiusSelect'] : '';
                        foreach ($radius as $value) {
                            echo '<option value="'.$value.'" '.selected( esc_attr( $selected_radius ), $value, false ).'>'.$value.'</option>';
                        }
                        endif;
                        ?>
                    </select>
                </div>
                <div class="mvx-inp-wrap mvx-distanceSelect">
                    <select name="distanceSelect" id="distanceSelect">
                        <?php $selected_distance = isset($request['distanceSelect']) ? $request['distanceSelect'] : ''; ?>
                        <option value="M" <?php echo selected( $selected_distance, "M", false ); ?>><?php esc_html_e('Miles', 'greenmart'); ?></option>
                        <option value="K" <?php echo selected( $selected_distance, "K", false ); ?>><?php esc_html_e('Kilometers', 'greenmart'); ?></option>
                        <option value="N" <?php echo selected( $selected_distance, "N", false ); ?>><?php esc_html_e('Nautical miles', 'greenmart'); ?></option>
                        <?php do_action('mvx_vendor_list_sort_distanceSelect_extra_options'); ?>
                    </select>
                </div>
                <?php do_action( 'mvx_vendor_list_vendor_sort_map_extra_filters', $request ); ?>
                <input type="submit" name="vendorListFilter" value="<?php esc_attr_e('Submit', 'greenmart'); ?>">
            </div>
        </form>
        <?php endif; ?>
        <div class="mvx-store-map-pagination">
            <p class="mvx-pagination-count mvx-pull-right">
                <?php
                if ( $vendor_total <= $per_page || -1 === $per_page ) {
                        /* translators: %d: total results */
                        printf( _n( 'Viewing the single vendor', 'Viewing all %d vendors', $vendor_total, 'greenmart' ), $vendor_total );
                } else {
                        $first = ( $per_page * $current ) - $per_page + 1;
                        if(!apply_filters('mvx_vendor_list_ignore_pagination', false)) {
                            $last  = min( $vendor_total, $per_page * $current );
                        }else{
                            $last  = $vendor_total;
                        }
                        /* translators: 1: first result 2: last result 3: total results */
                        printf( _nx( 'Viewing the single vendor', 'Viewing %1$d&ndash;%2$d of %3$d vendors', $vendor_total, 'with first and last result', 'greenmart' ), $first, $last, $vendor_total );
                }
                ?>
            </p>
            
            <form name="vendor_sort" method="post" >
                <div class="vendor_sort">
                    <select class="select short" id="vendor_sort_type" name="vendor_sort_type">
                        <?php
                        $vendor_sort_type = apply_filters('mvx_vendor_list_vendor_sort_type', array(
                            'registered' => esc_html__('By date', 'greenmart'),
                            'name' => esc_html__('By Alphabetically', 'greenmart'),
                            'category' => esc_html__('By Category', 'greenmart'),
                        ));
                        if ($vendor_sort_type && is_array($vendor_sort_type)) {
                            foreach ($vendor_sort_type as $key => $label) {
                                $selected = '';
                                if (isset($request['vendor_sort_type']) && $request['vendor_sort_type'] == $key) {
                                    $selected = 'selected="selected"';
                                }
                                echo '<option value="' . $key . '" ' . $selected . '>' . $label . '</option>';
                            }
                        }
                        ?>
                    </select>
                    <?php
                    $product_category = get_terms('product_cat');
                    $options_html = '';
                    $sort_category = isset($request['vendor_sort_category']) ? $request['vendor_sort_category'] : '';
                    foreach ($product_category as $category) {
                        if ($category->term_id == $sort_category) {
                            $options_html .= '<option value="' . esc_attr($category->term_id) . '" selected="selected">' . esc_html($category->name) . '</option>';
                        } else {
                            $options_html .= '<option value="' . esc_attr($category->term_id) . '">' . esc_html($category->name) . '</option>';
                        }
                    }
                    ?>
                    <select name="vendor_sort_category" id="vendor_sort_category" class="select"><?php echo trim($options_html); ?></select>
                    <?php do_action( 'mvx_vendor_list_vendor_sort_extra_attributes', $request ); ?>
                    <input value="<?php esc_attr_e('Sort', 'greenmart'); ?>" type="submit">
                </div>
            </form>

        </div>
    </div>
    <!-- Map End -->

    <div class="mvx-store-list-wrap">
        <?php
        if ($vendors && is_array($vendors)) {
            foreach ($vendors as $vendor_id) {
                $vendor = get_mvx_vendor($vendor_id);
                $image = $vendor->get_image() ? $vendor->get_image('image', array(125, 125)) : $MVX->plugin_url . 'assets/images/WP-stdavatar.png';
                $banner = $vendor->get_image('banner') ? $vendor->get_image('banner') : '';
                ?>

                <div class="mvx-store-list">
                    <?php do_action('mvx_vendor_lists_single_before_image', $vendor->term_id, $vendor->id); ?>
                    <div class="mvx-profile-wrap">
                        <div class="mvx-cover-picture" style="background-image: url('<?php if($banner) echo trim($banner); ?>');"></div>
                        <div class="store-badge-wrap">
                            <?php do_action('mvx_vendor_lists_vendor_store_badges', $vendor); ?>
                        </div>
                        <div class="mvx-store-info">
                            <div class="mvx-store-picture">
                                <img class="vendor_img" src="<?php echo esc_url($image); ?>" id="vendor_image_display">
                            </div>
                        </div>
                    </div>
                    <?php do_action('mvx_vendor_lists_single_after_image', $vendor->term_id, $vendor->id); ?>
                    <div class="mvx-store-detail-wrap">
                        <?php do_action('mvx_vendor_lists_vendor_before_store_details', $vendor); ?>
                        <ul class="mvx-store-detail-list">
                            <li class="list-name">
                                <?php $button_text = apply_filters('mvx_vendor_lists_single_button_text', $vendor->page_title); ?>
                                <a href="<?php echo esc_url($vendor->get_permalink()); ?>" class="store-name"><?php echo trim($button_text); ?></a>
                                <?php do_action('mvx_vendor_lists_single_after_button', $vendor->term_id, $vendor->id); ?>
                                <?php do_action('mvx_vendor_lists_vendor_after_title', $vendor); ?>
                            </li>
                            <?php

                               if (get_mvx_vendor_settings('is_sellerreview', 'general') == 'Enable') {

                                    echo '<li class="list-rating">';
                                    $rating_info = mvx_get_vendor_review_info($vendor->term_id);
                                    $MVX->template->get_template('review/rating.php', array('rating_val_array' => $rating_info));

                                    echo '</li>';
                                }
                            ?>
                            <?php if($vendor->get_formatted_address()) : ?>
                            <li>
                                <i class="mvx-font ico-location-icon2"></i>
                                <p><?php echo trim($vendor->get_formatted_address()); ?></p>
                            </li>
                            <?php endif; ?>
                            <?php 
                                $mobile             = $vendor->phone;
                                $vendor_id          = $vendor->id;
                                $vendor_hide_phone  = get_user_meta($vendor_id, '_vendor_hide_phone', true);

                                if( (!empty($mobile) && $vendor_hide_phone != 'Enable') ) {
                                    ?>
                                    <li><p class="mvx_vendor_detail"><i class="mvx-font ico-call-icon"></i><label><?php echo apply_filters('vendor_shop_page_contact', $mobile, $vendor_id); ?></label></p></li>
                                    <?php
                                }
                            ?>
                        </ul>
                        <?php do_action('mvx_vendor_lists_vendor_after_store_details', $vendor); ?>
                    </div>
                </div>
                <?php
            }
        } else {
            esc_html_e('No vendor found!', 'greenmart');
        }
        ?>
    </div>
    <!-- pagination --> 
    <?php if(!apply_filters('mvx_vendor_list_ignore_pagination', false)) : ?>
    <div class="mvx-pagination">
        <?php
            echo paginate_links( apply_filters( 'mvx_vendor_list_pagination_args', array( 
                    'base'         => $base,
                    'format'       => $format,
                    'add_args'     => false,
                    'current'      => max( 1, $current ),
                    'total'        => $total,
                    'prev_text'    => 'Prev',
                    'next_text'    => 'Next',
                    'type'         => 'list',
                    'end_size'     => 3,
                    'mid_size'     => 3,
            ) ) );
	?>
    </div>
    <?php endif; ?>
</div> 