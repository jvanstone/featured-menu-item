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
 * Description:       To active the pluign use shortcode [featured-menu-item] [featured-menu-daily feaure-day=""]
 * Version:           2.1.0
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
		$class = implode(' ', array_filter( array(
			'button',
			'product_type_' . $product->get_type(),
			$product->is_purchasable() && $product->is_in_stock() ? 'add_to_cart_button' : '',
			$product->supports( 'ajax_add_to_cart' ) ? 'ajax_add_to_cart' : '',
		) ) );

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
	}  elseif ( $product && $product->is_type( 'grouped' ) && $product->is_purchasable() && $product->is_in_stock() && ! $product->is_sold_individually() ) {

		echo 'Grouped';

		global $product, $post;

do_action( 'woocommerce_before_add_to_cart_form' ); ?>

<form class="cart grouped_form" action="<?php echo esc_url( apply_filters( 'woocommerce_add_to_cart_form_action', $product->get_permalink() ) ); ?>" method="post" enctype='multipart/form-data'>
	<table cellspacing="0" class="woocommerce-grouped-product-list group_table">
		<tbody>
			<?php
			$quantites_required      = false;
			$previous_post           = $post;
			$grouped_product_columns = apply_filters(
				'woocommerce_grouped_product_columns',
				array(
					'quantity',
					'label',
					'price',
				),
				$product
			);
			$show_add_to_cart_button = false;

			do_action( 'woocommerce_grouped_product_list_before', $grouped_product_columns, $quantites_required, $product );

			foreach ( $grouped_products as $grouped_product_child ) {
				$post_object        = get_post( $grouped_product_child->get_id() );
				$quantites_required = $quantites_required || ( $grouped_product_child->is_purchasable() && ! $grouped_product_child->has_options() );
				$post               = $post_object; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
				setup_postdata( $post );

				if ( $grouped_product_child->is_in_stock() ) {
					$show_add_to_cart_button = true;
				}

				echo '<tr id="product-' . esc_attr( $grouped_product_child->get_id() ) . '" class="woocommerce-grouped-product-list-item ' . esc_attr( implode( ' ', wc_get_product_class( '', $grouped_product_child ) ) ) . '">';

				// Output columns for each product.
				foreach ( $grouped_product_columns as $column_id ) {
					do_action( 'woocommerce_grouped_product_list_before_' . $column_id, $grouped_product_child );

					switch ( $column_id ) {
						case 'quantity':
							ob_start();

							if ( ! $grouped_product_child->is_purchasable() || $grouped_product_child->has_options() || ! $grouped_product_child->is_in_stock() ) {
								woocommerce_template_loop_add_to_cart();
							} elseif ( $grouped_product_child->is_sold_individually() ) {
								echo '<input type="checkbox" name="' . esc_attr( 'quantity[' . $grouped_product_child->get_id() . ']' ) . '" value="1" class="wc-grouped-product-add-to-cart-checkbox" />';
							} else {
								do_action( 'woocommerce_before_add_to_cart_quantity' );

								woocommerce_quantity_input(
									array(
										'input_name'  => 'quantity[' . $grouped_product_child->get_id() . ']',
										'input_value' => isset( $_POST['quantity'][ $grouped_product_child->get_id() ] ) ? wc_stock_amount( wc_clean( wp_unslash( $_POST['quantity'][ $grouped_product_child->get_id() ] ) ) ) : '', // phpcs:ignore WordPress.Security.NonceVerification.Missing
										'min_value'   => apply_filters( 'woocommerce_quantity_input_min', 0, $grouped_product_child ),
										'max_value'   => apply_filters( 'woocommerce_quantity_input_max', $grouped_product_child->get_max_purchase_quantity(), $grouped_product_child ),
										'placeholder' => '0',
									)
								);

								do_action( 'woocommerce_after_add_to_cart_quantity' );
							}

							$value = ob_get_clean();
							break;
						case 'label':
							$value  = '<label for="product-' . esc_attr( $grouped_product_child->get_id() ) . '">';
							$value .= $grouped_product_child->is_visible() ? '<a href="' . esc_url( apply_filters( 'woocommerce_grouped_product_list_link', $grouped_product_child->get_permalink(), $grouped_product_child->get_id() ) ) . '">' . $grouped_product_child->get_name() . '</a>' : $grouped_product_child->get_name();
							$value .= '</label>';
							break;
						case 'price':
							$value = $grouped_product_child->get_price_html() . wc_get_stock_html( $grouped_product_child );
							break;
						default:
							$value = '';
							break;
					}

					echo '<td class="woocommerce-grouped-product-list-item__' . esc_attr( $column_id ) . '">' . apply_filters( 'woocommerce_grouped_product_list_column_' . $column_id, $value, $grouped_product_child ) . '</td>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

					do_action( 'woocommerce_grouped_product_list_after_' . $column_id, $grouped_product_child );
				}

				echo '</tr>';
			}
			$post = $previous_post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			setup_postdata( $post );

			do_action( 'woocommerce_grouped_product_list_after', $grouped_product_columns, $quantites_required, $product );
			?>
		</tbody>
	</table>

	<input type="hidden" name="add-to-cart" value="<?php echo esc_attr( $product->get_id() ); ?>" />

	<?php if ( $quantites_required && $show_add_to_cart_button ) : ?>

		<?php do_action( 'woocommerce_before_add_to_cart_button' ); ?>

		<button type="submit" class="single_add_to_cart_button button alt"><?php echo esc_html( $product->single_add_to_cart_text() ); ?></button>

		<?php do_action( 'woocommerce_after_add_to_cart_button' ); ?>

	<?php endif; ?>
</form>

<?php do_action( 'woocommerce_after_add_to_cart_form' ); ?>
	}

	return $html;
}


/**
 * Get the Featured Menu Item.
 *
 * @return void
 */
function fmi_get_featured_menu_item() {

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
		<h4><?php echo $product->get_title(); ?></h4></a>

		<div id="product-image1">
				<a href="<?php echo esc_url( get_permalink( $product->id ) ); ?>" title="<?php echo esc_attr( $product->get_title() ); ?>">
				<?php echo $product->get_image('full');?>
				</a>
		</div> <!-- End Product Image -->

		<h6><?php //echo $product->get_price_html(); ?></h6>
		<p><?php echo $product->get_short_description(); ?></p>

		<div class="add-quantity-box"> 
			<?php echo fmi_add_to_cart_button( $product ); ?>
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
	return fmi_get_featured_menu_item( $data );
}
add_shortcode( 'featured-menu-item', 'fmi_make_feature', 99 );



/**
 * Display Featured Menu Item by day.
 *
 * @return void
 */
function fmi_get_featured_menu_daily( $data ) {


	$data = shortcode_atts(
		array(
			'feature-day' => '',
			//'attribute-2' => '',
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
				<?php echo $product->get_image('full');?>
				</a>
		</div> <!-- End Product Image -->

		</div>



		<div class="half-side">
			<a href="<?php echo esc_url( get_permalink( $product->id ) ); ?>" title="<?php echo esc_attr( $product->get_title() ); ?>">
			<h4><?php echo $product->get_title(); ?></h4></a>

			
			<h6><?php echo $product->get_price_html(); ?></h6>
			<p><?php echo $product->get_short_description(); ?></p>

			<div class="add-quantity-box"> 
				<?php echo fmi_add_to_cart_button( $product ); ?>
			</div>
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

function fmi_get_featured_menu_daily2( $data ) {

	global $product;

	$data = shortcode_atts(
		array(
			'feature-day' => '',
			//'attribute-2' => '',
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
	// Ensure visibility.
	if ( empty( $product ) || ! $product->is_visible() ) {
		return;
	}
	?>
	<li <?php wc_product_class( '', $product ); ?>>
		<?php
		/**
		 * Hook: woocommerce_before_shop_loop_item.
		 *
		 * @hooked woocommerce_template_loop_product_link_open - 10
		 */
		do_action( 'woocommerce_before_shop_loop_item' );

		/**
		 * Hook: woocommerce_before_shop_loop_item_title.
		 *
		 * @hooked woocommerce_show_product_loop_sale_flash - 10
		 * @hooked woocommerce_template_loop_product_thumbnail - 10
		 */
		do_action( 'woocommerce_before_shop_loop_item_title' );

		/**
		 * Hook: woocommerce_shop_loop_item_title.
		 *
		 * @hooked woocommerce_template_loop_product_title - 10
		 */
		do_action( 'woocommerce_shop_loop_item_title' );

		/**
		 * Hook: woocommerce_after_shop_loop_item_title.
		 *
		 * @hooked woocommerce_template_loop_rating - 5
		 * @hooked woocommerce_template_loop_price - 10
		 */
		do_action( 'woocommerce_after_shop_loop_item_title' );

		/**
		 * Hook: woocommerce_after_shop_loop_item.
		 *
		 * @hooked woocommerce_template_loop_product_link_close - 5
		 * @hooked woocommerce_template_loop_add_to_cart - 10
		 */
		do_action( 'woocommerce_after_shop_loop_item' );
		?>
</li>
<?php
}

/**
 * Execute the Features Product as a short code.
 *
 * @return mixed
 */
function fmi_make_feature_daily( $data ) {
	return fmi_get_featured_menu_daily2( $data );
}
add_shortcode( 'featured-menu-daily', 'fmi_make_feature_daily', 99 );
