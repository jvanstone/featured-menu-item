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
 * @return void
 */
function get_weekday_feature() {
	$tag_name = 'featured-';
    return  $tag_name . strtolower( date( 'l' ) );
}
/**
 * Get the Featrued Menu Item.
 *
 * @return void
 */
function fmi_get_featured_menu_item() {


	ob_start();

	$args = array( 
		'post_type'      => 'product',
		'posts_per_page' => 1, 
		'product_tag'    => array( get_weekday_feature() )
	);
	$loop = new WP_Query( $args );
	$product_count = $loop->post_count;


	if( $product_count > 0 ){
		echo '<ul class="products">';
		while ( $loop->have_posts() ) : $loop->the_post(); 
			global $product;
			global $post;

			echo "<li>" . $loop->post->ID. " </li>";

			$product = wc_get_product( $loop->post->ID );
  
				// Now you have access to (see above)...
				
				echo $product->get_type();
				echo $product->get_name();
				echo $product->get_price();
				$product->get_image();
				echo $product->get_short_description();
				echo $product->add_to_cart_text();

		endwhile;

		echo '</ul>';
	}else{
		echo 'No product matching your criteria.';
	}

	$result =  ob_get_clean();
	echo $result;

}
add_shortcode( 'featured-menu-item', 'fmi_get_featured_menu_item' );

