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
 * Get the Featrued Menu Item.
 *
 * @return void
 */
function fmi_get_featured_menu_item() {

	$get_products_tags = get_terms( 'featured_monday' );
	$tag_lists = array();
	if ( ! empty( $get_products_tags ) && ! is_wp_error( $get_products_tags ) ){
		foreach ( $get_products_tags as $tag ) {
			$tag_lists[] = $tag->name;
		}
	}

}
add_shortcode( 'featured-menu-item', 'fmi_get_featured_menu_item' );

