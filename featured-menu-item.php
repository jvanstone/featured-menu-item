<?php
/**
 * @package           Featured Menu Item
 * @author            Jason Vanstone
 * @copyright         2019 JV
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       Featured Menu Item
 * Plugin URI:        https://example.com/plugin-name
 * Description:       Description of the plugin.
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
	$tag_name = 'featured-';
	return  $tag_name . strtolower( gmdate( 'l' ) ); // phpcs:ignore
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
	<a href="<?php the_permalink(); ?>" class="button button wp-block-button__link">Add to Cart</a><?php
	if ( $available ) {
		?><a href="<?php $add_to_cart = do_shortcode('[add_to_cart_url id="'.$post->ID.'"]');
		echo $add_to_cart;
	?>" class="button wp-block-button__link">Buy now</a>
					<?php
				}
				?>

</div>
<?php
	
	} else{
		echo 'No product matching your criteria.';
	}

	

}
add_shortcode( 'featured-menu-item', 'fmi_get_featured_menu_item' );

