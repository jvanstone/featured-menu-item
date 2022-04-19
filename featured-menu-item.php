<?php
/**
 * @package           Featured Menu Item
 * @author            Jason Vanstone
 * @copyright         2022 JV
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       Featured Menu Item
 * Plugin URI:        https://vanstoneline.com
 * Description:       To active the pluign use shortcode [featured-menu-item] [featured-menu-daily feature-day="choose-day"]
 * Version:           3.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Jason Vanstone
 * Author URI:        https://vanstoneline.com
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
 * Enqueue Style sheets
 *
 * @return mixed
 */
function fmi_theme_name_scripts() {
	wp_enqueue_style( 'fmi-style',	plugins_url( '/public/css/style.css', __FILE__ ) ); // phpcs:ignore 
	wp_enqueue_script( 'fmi-add', plugins_url( '/public/js/add-quantity.js', __FILE__ ), array(), '1.0.0', true );
}
add_action( 'wp_enqueue_scripts', 'fmi_theme_name_scripts' );
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
 * Create an add to cart  button for Feature. Has a support js file on public/js/add-quantity.js
 *
 * @param  mixed $product // Get the product.
 * @return string
 */
function fmi_add_to_cart_button( $product ) {
	?>
	<?php
	if ( $product && $product->is_type( 'simple' ) && $product->is_purchasable() && $product->is_in_stock() && ! $product->is_sold_individually() ) {
		// Get the necessary classes.
		$class = implode(
			' ',
			array_filter(
				array(
					'button',
					'product_type_' . $product->get_type(),
					$product->is_purchasable() && $product->is_in_stock() ? 'add_to_cart_button' : '',
					$product->supports( 'ajax_add_to_cart' ) ? 'ajax_add_to_cart' : '',
				)
			)
		);

		// Embedding the quantity field to Ajax add to cart button.
		$html = sprintf(
			'%s<a rel="nofollow" href="%s" data-quantity="%s" data-product_id="%s" data-product_sku="%s" class="%s">%s</a>',
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
 * Set up Products when product is grouped
 * fmi_create_group_view
 *
 * @param  mixed $product // Get the pr grouped product information.
 * @return mixed
 */
function fmi_create_group_view( $product ) {
	ob_start();
			woocommerce_grouped_add_to_cart();
			woocommerce_template_loop_add_to_cart();
		$output = ob_get_clean();
		return $output;
}

/**
 * Get the Featured Menu Item.
 *
 * @return string
 */
function fmi_get_featured_menu_item() {
	global $product;
	ob_start();

	$args          = array(
		'post_type'      => 'product',
		'posts_per_page' => 1,
		'product_tag'    => array( get_weekday_feature() ),
	);
	$loop          = new WP_Query( $args );
	$product_count = $loop->post_count;

	?>
	<div class="fmi-container">

	<h1>Today's Featured Menu</h1>

	<?php

	if ( $product_count > 0 ) {
		$product = wc_get_product( $loop->post->ID );
		?>

		<a href="<?php echo esc_url( get_permalink( $product->id ) ); ?>" title="<?php echo esc_attr( $product->get_title() ); ?>">
		<h4><?php echo esc_html( $product->get_title() ); ?></h4></a>

		<div id="product-image1">
				<a href="<?php echo esc_url( get_permalink( $product->id ) ); ?>" title="<?php echo esc_attr( $product->get_title() ); ?>">
				<?php echo $product->get_image( 'full' ); //phpcs:ignore ?>
				</a>
		</div> <!-- End Product Image -->

		<p><?php echo esc_html( $product->get_short_description() ); ?></p>

		<div class="add-quantity-box"> 
					<?php
					if ( $product->is_type( 'grouped' ) ) {
						echo fmi_create_group_view( $product ); //phpcs:ignore
					} else {
						echo fmi_add_to_cart_button( $product ); //phpcs:ignore
					}
					?>
				</div>
		<?php

	} else {
		echo 'No product matching your criteria.';
	}
	?>
	</div> 
	<?php

	return ob_get_clean();
}

/**
 * Execute the Features Product.
 *
 * @return mixed
 */
function fmi_make_feature() {
	return fmi_get_featured_menu_item();
}
add_shortcode( 'featured-menu-item', 'fmi_make_feature', 99 );



/**
 * Display Featured Menu Item by day.
 *
 * @param  mixed $data // Get the data from shortcode.
 * @return string
 */
function fmi_get_featured_menu_daily( $data ) {
	global $product;

	$data = shortcode_atts(
		array(
			'feature-day' => '',
		),
		$data
	);

	$attr1 = esc_attr( $data['feature-day'] );

	ob_start();

	$args          = array(
		'post_type'      => 'product',
		'posts_per_page' => 1,
		'product_tag'    => array( $attr1 ),
	);
	$loop          = new WP_Query( $args );
	$product_count = $loop->post_count;
	?>

	<div class="fmi-daily-container">

		<?php

		if ( $product_count > 0 ) {
			$product = wc_get_product( $loop->post->ID );
			?>
			<div class="half-side">
				<div id="product-image1">
						<a href="<?php echo esc_url( get_permalink( $product->id ) ); ?>" title="<?php echo esc_attr( $product->get_title() ); ?>">
						<?php echo $product->get_image( 'full' ); //phpcs:ignore ?>
						</a>
				</div> <!-- End Product Image -->
			</div>

			<div class="half-side">
				<a href="<?php echo esc_url( get_permalink( $product->id ) ); ?>" title="<?php echo esc_attr( $product->get_title() ); ?>">
				<h4><?php echo esc_html( $product->get_title() ); ?></h4></a>

				<p><?php echo esc_html( $product->get_short_description() ); ?></p>

				<div class="add-quantity-box"> 
					<?php
					if ( $product->is_type( 'grouped' ) ) {
						echo fmi_create_group_view( $product ); //phpcs:ignore
					} else {
						echo fmi_add_to_cart_button( $product ); //phpcs:ignore
					}
					?>
				</div>
			</div>

				<?php

		} else {
			echo esc_html( 'No product matching your criteria.' );
		}
		?>
	</div> 
	<?php

	return ob_get_clean();
}

/**
 * Execute the Features Product as a short code.
 *
 * @param  mixed $data // Get Data from shortcode.
 * @return string
 */
function fmi_make_feature_daily( $data ) {
	return fmi_get_featured_menu_daily( $data );
}
add_shortcode( 'featured-menu-daily', 'fmi_make_feature_daily' );
