<?php
/*
Plugin Name: Order Thumbnail for WooCommerce
Plugin URI: https://wordpress.org/plugins/order-thumbnail-for-woocommerce/
Description: Add thumbnails to the WooCommerce orders list.
Version: 1.2
Text Domain: order-thumbnail-for-woocommerce
Domain Path: /languages
Author: Delite Studio S.r.l.
Author URI: https://www.delitestudio.com/
WC requires at least: 3.5
WC tested up to: 9.1
*/

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

add_action( 'admin_enqueue_scripts', 'otfw_lightbox' );

function otfw_lightbox() {
	global $woocommerce, $pagenow, $typenow;
	if ( 'edit.php' == $pagenow && 'shop_order' == $typenow ) {
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		wp_enqueue_script( 'prettyPhoto', $woocommerce->plugin_url() . '/assets/js/prettyPhoto/jquery.prettyPhoto' . $suffix . '.js', array( 'jquery' ), $woocommerce->version, true );
		wp_enqueue_script( 'prettyPhoto-init', $woocommerce->plugin_url() . '/assets/js/prettyPhoto/jquery.prettyPhoto.init' . $suffix . '.js', array( 'jquery' ), $woocommerce->version, true );
		wp_enqueue_style( 'woocommerce_prettyPhoto_css', $woocommerce->plugin_url() . '/assets/css/prettyPhoto.css' );
	}
}

add_filter( 'manage_edit-shop_order_columns', 'otfw_shop_order_columns', 20 );

/**
 * Adds thumbnail column for orders
 * @param  array $existing_columns
 * @return array
 */
function otfw_shop_order_columns( $existing_columns ) {
	if ( empty( $existing_columns ) && ! is_array( $existing_columns ) ) {
		$existing_columns = array();
	}

	$columns = array();
	$columns['thumb'] = '<span class="wc-image tips" data-tip="' . __( 'Image', 'woocommerce' ) . '">' . __( 'Image', 'woocommerce' ) . '</span>';

	return array_merge( $existing_columns, $columns );
}

/**
 * Ouputs thumbnail column of first product with image for each order
 * @param  string $column
 */
add_action( 'manage_shop_order_posts_custom_column', 'otfw_render_shop_order_columns', 20 );

function otfw_render_shop_order_columns( $column ) {
	if ( 'thumb' == $column ) {
		global $post, $the_order;

		if ( empty( $the_order ) || $the_order->get_id() != $post->ID ) {
			$the_order = wc_get_order( $post->ID );
		}

		foreach ( $the_order->get_items() as $item ) {
			$_product = apply_filters( 'woocommerce_order_item_product', $item->get_product(), $item );

			if ( is_object($_product) && $_product->get_image_id() ) {

				$image_title = apply_filters( 'woocommerce_order_item_name', $item['name'], $item );
				$image_link  = wp_get_attachment_url( $_product->get_image_id() );
				$image       = $_product->get_image('thumbnail');

				echo sprintf( '<a href="%s" itemprop="image" class="woocommerce-main-image zoom" title="%s" data-rel="prettyPhoto">%s</a>', $image_link, $image_title, $image );
				break;
			}
		}
	}
}
