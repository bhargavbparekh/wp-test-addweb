<?php

/*
  Plugin Name: WP Admin customization
  Version: 1.0
  Description: WP Admin customization custom plugins
  Author: Bhargav
  Author URI: https://wordpress.org/
 */
define('WP_ADMIN_CUSTOMIZATION_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('WP_ADMIN_CUSTOMIZATION_PLUGIN_URl', __FILE__);

add_filter('manage_edit-product_columns', 'wpac_total_sales_1', 20);
// populate
add_action('manage_posts_custom_column', 'wpac_total_sales_2');
// make sortable
add_filter('manage_edit-product_sortable_columns', 'wpac_total_sales_3');
// how to sort
add_action('pre_get_posts', 'wpac_total_sales_4');

function wpac_total_sales_1($col_th) {
    return array_slice($col_th, 0, 6, true) // 4 columns before
            + array('total_sales' => 'Total Orders') // our column is 5th
            + array_slice($col_th, 6, NULL, true);
    //return wp_parse_args( array( 'total_sales' => 'Total Orders' ), $col_th ); 
}

function wpac_total_sales_2($column_id) {
    if ($column_id == 'total_sales')
        echo get_post_meta(get_the_ID(), 'total_sales', true);
}

function wpac_total_sales_3($a) {
    return wp_parse_args(array('total_sales' => 'by_total_sales'), $a);
}

function wpac_total_sales_4($query) {
    if (!is_admin() || empty($_GET['orderby']) || empty($_GET['order']))
        return;
    if ($_GET['orderby'] == 'by_total_sales') {
        $query->set('meta_key', 'total_sales');
        $query->set('orderby', 'meta_value_num');
        $query->set('order', $_GET['order']);
    }
    return $query;
}

/**
 * Add a text field to each cart item
 */
function wpac_after_cart_item_name($cart_item, $cart_item_key) {
    $notes = isset($cart_item['gift_message']) ? $cart_item['gift_message'] : '';
    $product = $cart_item['data'];
    if (has_term('gift', 'product_cat', $product->id)) {
        printf(
                '<td class="gift_message">Gift Message (optional)<textarea class="%s" id="cart_gift_message_%s" data-cart-id="%s" placeholder="Enter something">%s</textarea></td>',
                'wpac-cart-gift_message',
                $cart_item_key,
                $cart_item_key,
                $notes
        );
    }
    else{
       printf('<td class="gift_message"></td>'); 
    }
}

add_action('woocommerce_after_cart_item_name', 'wpac_after_cart_item_name', 10, 2);

function wpac_enqueue_scripts() {
    wp_register_script('wpac-script', trailingslashit(plugin_dir_url(__FILE__)) . 'assets/js/update-cart-item-ajax.js', array('jquery-blockui'), time(), true);
    wp_localize_script(
            'wpac-script',
            'wpac_vars',
            array(
                'ajaxurl' => admin_url('admin-ajax.php')
            )
    );
    wp_enqueue_script('wpac-script');
}

add_action('wp_enqueue_scripts', 'wpac_enqueue_scripts');

function wpac_update_cart_notes() {
    // Do a nonce check
    if (!isset($_POST['security']) || !wp_verify_nonce($_POST['security'], 'woocommerce-cart')) {
        wp_send_json(array('nonce_fail' => 1));
        exit;
    }
    // Save the notes to the cart meta
    $cart = WC()->cart->cart_contents;
    $cart_id = $_POST['cart_id'];
    $notes = $_POST['gift_message'];
    $cart_item = $cart[$cart_id];
    $cart_item['gift_message'] = $notes;
    WC()->cart->cart_contents[$cart_id] = $cart_item;
    WC()->cart->set_session();
    wp_send_json(array('success' => 1));
    exit;
}

add_action('wp_ajax_wpac_update_cart_notes', 'wpac_update_cart_notes');

function wpac_checkout_create_order_line_item($item, $cart_item_key, $values, $order) {
    foreach ($item as $cart_item_key => $cart_item) {
        if (isset($cart_item['gift_message'])) {
            $item->add_meta_data('gift_message', $cart_item['gift_message'], true);
        }
    }
}
add_action('woocommerce_checkout_create_order_line_item', 'wpac_checkout_create_order_line_item', 10, 4);
