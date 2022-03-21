<?php
/**
 * @package           Featured Menu Item
 * @author            Jason Vanstone
 * @copyright         2019 JV
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       Featured Menu Item
 * Plugin URI:        
 * Description:       active the pluign by useing shortcode [featured-menu-item]
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Jason Vanstone
 * Author URI:        
 * Text Domain:       plugin-fmi
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Update URI:        https://example.com/my-plugin/
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'FEATURED_MENU_ITEM_VERSION', '1.0.0' );



/**
 * Get the actual date of the week.
 *
 * @return string
 */
function get_weekday_feature() {
	$tag_name = 'feature-';
	return  $tag_name . strtolower( gmdate( 'l' ) ); // phpcs:ignore
}



/**
 * Create an add to cart  button for Feature
 *
 * @param  mixed $product
 * @return string
 */
function fmi_add_to_cart_button( $product ) {
   
	if ( $product && $product->is_type( 'simple' ) && $product->is_purchasable() && $product->is_in_stock() && ! $product->is_sold_individually() ) {
        // Get the necessary classes
        $class = implode( ' ', array_filter( array(
            'button',
            'product_type_' . $product->get_type(),
            $product->is_purchasable() && $product->is_in_stock() ? 'add_to_cart_button' : '',
            $product->supports( 'ajax_add_to_cart' ) ? 'ajax_add_to_cart' : '',
        ) ) );

        // Embedding the quantity field to Ajax add to cart button
        $html = sprintf( '%s<a rel="nofollow" href="%s" data-quantity="%s" data-product_id="%s" data-product_sku="%s" class="%s">%s</a>',
            woocommerce_quantity_input( array(), $product, false ),
            esc_url( $product->add_to_cart_url() ),
            esc_attr( isset( $quantity ) ? $quantity : 1 ),
            esc_attr( $product->get_id() ),
            esc_attr( $product->get_sku() ),
            esc_attr( isset( $class ) ? $class : 'button' ),
            esc_html( $product->add_to_cart_text() )
        );
    }
   
    return $html;
} 


/**
 * Get the Featured Menu Item. 
 *
 * @return void
 */
function fmi_get_featured_menu_item() {

	$args          = array(
		'post_type'      => 'product',
		'posts_per_page' => 1,
		'product_tag'    => array( get_weekday_feature() ),
	);
	$loop          = new WP_Query( $args );
	$product_count = $loop->post_count;

	if ( $product_count > 0 ) {
		$product = wc_get_product( $loop->post->ID );
	?>

<div id="product-description-container">

	<h1>Today's Featured Menu</h1>

	<a href="<?php echo esc_url( get_permalink( $product->id ) ); ?>" title="<?php echo esc_attr( $product->get_title() ); ?>">
	<h4><?php echo $product->get_title(); ?></h4></a>

	<div id="product-image1">
			<a href="<?php echo esc_url( get_permalink( $product->id ) ); ?>" title="<?php echo esc_attr( $product->get_title() ); ?>">
			<?php echo $product->get_image('thumbnail');?>
			</a>
	</div> <!-- End Product Image -->

	<h6><?php //echo $product->get_price_html(); ?></h6>
	<p><?php  echo $product->get_short_description(); ?></p>
	<?php echo fmi_add_to_cart_button( $product ); ?>

	
</div>
<?php

	} else {
		echo 'No product matching your criteria.';
	}

}
add_shortcode( 'featured-menu-item', 'fmi_get_featured_menu_item' );

add_action( 'wp_footer' , 'archives_quantity_fields_script' );
function archives_quantity_fields_script(){
    ?>
    <script type='text/javascript'>
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
    </script>
    <?php
}