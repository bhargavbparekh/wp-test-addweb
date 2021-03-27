(function ($) {
    $(document).ready(function () {
        $('.wpac-cart-gift_message').on('change keyup paste', function () {
            $('.cart_totals').block({
                message: null,
                overlayCSS: {
                    background: '#fff',
                    opacity: 0.6
                }
            });
            var cart_id = $(this).data('cart-id');
            $.ajax(
                    {
                        type: 'POST',
                        url: wpac_vars.ajaxurl,
                        data: {
                            action: 'wpac_update_cart_notes',
                            security: $('#woocommerce-cart-nonce').val(),
                            gift_message: $('#cart_gift_message_' + cart_id).val(),
                            cart_id: cart_id
                        },
                        success: function (response) {
                            $('.cart_totals').unblock();
                        }
                    }
            )
        });
        $('.woocommerce-cart table.cart th:last-child').after('<th>Message</th>');
        $(".woocommerce-cart table.cart tr").each(function (index) {
            //console.log( index + ": " + $( this ).text() );
            $(".woocommerce-cart table.cart tr:nth-child(" + index + ") td.gift_message").insertAfter("tr:nth-child(" + index + ") td.product-subtotal");
        });
    });


})(jQuery);