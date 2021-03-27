<?php

function theme_enqueue_styles() {
    wp_enqueue_style('child-style', get_stylesheet_directory_uri() . '/style.css', []);
}

add_action('wp_enqueue_scripts', 'theme_enqueue_styles', 20);

//function genrate_unique_coupan_code($order_id) {
//    global $coupon_code;
//    $order = wc_get_order($order_id);
//    $items = $order->get_items(); 
////    echo '<pre>';
//    //print_r($items);exit;
//    foreach ($items as $item_id => $item) {
//        $product_id = $item->get_variation_id() ? $item->get_variation_id() : $item->get_product_id();
//        $product = get_product($product_id);        
//        $product_type = $product->virtual;
//        if ($product_type == 'yes') {
//            $characters = "ABCDEFGHJKMNPQRSTUVWXYZ23456789";
//            $char_length = "8";
//            $coupon_code = substr(str_shuffle($characters), 0, $char_length);
//            
//            $amount = '10'; // Amount
//            $discount_type = 'fixed_cart'; // Type: fixed_cart, percent, fixed_product, percent_product
//
//            $coupon = array(
//                'post_title' => $coupon_code,
//                'post_content' => '',
//                'post_status' => 'publish',
//                'post_author' => 1,
//                'post_type' => 'shop_coupon');
//
//            $new_coupon_id = wp_insert_post($coupon);
//
//            // Add meta
//            update_post_meta($new_coupon_id, 'discount_type', $discount_type);
//            update_post_meta($new_coupon_id, 'coupon_amount', $amount);
//            update_post_meta($new_coupon_id, 'individual_use', 'no');
//            update_post_meta($new_coupon_id, 'product_ids', '');
//            update_post_meta($new_coupon_id, 'exclude_product_ids', '');
//            update_post_meta($new_coupon_id, 'usage_limit', '');
//            update_post_meta($new_coupon_id, 'expiry_date', '');
//            update_post_meta($new_coupon_id, 'apply_before_tax', 'yes');
//            update_post_meta($new_coupon_id, 'free_shipping', 'no');
//        }
//    }
//}
//
//add_action('woocommerce_thankyou', 'genrate_unique_coupan_code', 10, 1);
add_action('woocommerce_email_after_order_table', 'add_coupan_specific_email', 20, 4);

function add_coupan_specific_email($order, $sent_to_admin, $plain_text, $email) {
    if ($email->id == 'customer_processing_order') {

        $items = $order->get_items();
        foreach ($items as $item_id => $item) {
            $product_id = $item->get_variation_id() ? $item->get_variation_id() : $item->get_product_id();
            $product = get_product($product_id);
            $product_type = $product->virtual;
            if ($product_type == 'yes') {
                $characters = "ABCDEFGHJKMNPQRSTUVWXYZ23456789";
                $char_length = "4";
                $coupon_code = substr(str_shuffle($characters), 0, $char_length);

                $amount = '10'; // Amount
                $discount_type = 'fixed_cart'; // Type: fixed_cart, percent, fixed_product, percent_product

                $coupon = array(
                    'post_title' => $coupon_code,
                    'post_content' => '',
                    'post_status' => 'publish',
                    'post_author' => 1,
                    'post_type' => 'shop_coupon');

                $new_coupon_id = wp_insert_post($coupon);

                // Add meta
                update_post_meta($new_coupon_id, 'discount_type', $discount_type);
                update_post_meta($new_coupon_id, 'coupon_amount', $amount);
                update_post_meta($new_coupon_id, 'individual_use', 'no');
                update_post_meta($new_coupon_id, 'product_ids', '');
                update_post_meta($new_coupon_id, 'exclude_product_ids', '');
                update_post_meta($new_coupon_id, 'usage_limit', '');
                update_post_meta($new_coupon_id, 'expiry_date', '');
                update_post_meta($new_coupon_id, 'apply_before_tax', 'yes');
                update_post_meta($new_coupon_id, 'free_shipping', 'no');
            }
        }
        echo '<h2>Discount Code for Next Purchase</h2><p><strong>Note:</strong> This Discount code you can use for next purchase but only one time usable.<br/><strong>Discount Code:</strong> ' . $coupon_code . '</p>';
    }
}
