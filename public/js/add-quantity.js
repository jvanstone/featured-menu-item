jQuery(function($){
    // Update data-quantity
    $(document.body).on('click input', 'input.qty', function() {
        $(this).parent().parent().find('a.ajax_add_to_cart').attr('data-quantity', $(this).val());
        $(".added_to_cart").remove(); // Optional: Removing other previous "view cart" buttons
    }).on('click', '.add_to_cart_button', function(){
        var button = $(this);
        setTimeout(function(){
            button.parent().find('.quantity > input.qty').val(1); // reset quantity to 1
        }, 1000); // After 1 second

    });
});