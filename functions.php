<?php
/**
 * Include Theme Functions
 *
 * @package Squaretype Child Theme
 * @subpackage Functions
 * @version 1.0.0
 */

/**
 * Setup Child Theme
 */
function csco_setup_child_theme() {
	// Add Child Theme Text Domain.
	load_child_theme_textdomain( 'squaretype', get_stylesheet_directory() . '/languages' );
}

add_action( 'after_setup_theme', 'csco_setup_child_theme', 99 );

/**
 * Enqueue Child Theme Assets
 */
function csco_child_assets() {
	if ( ! is_admin() ) {
		$version = wp_get_theme()->get( 'Version' );
		wp_enqueue_style( 'csco_child_css', trailingslashit( get_stylesheet_directory_uri() ) . 'style.css', array(), $version, 'all' );
	}
}

add_action( 'wp_enqueue_scripts', 'csco_child_assets', 99 );

function remove_styles_ok () {
    wp_deregister_style('jetpack-top-posts-widget');
	wp_deregister_style('sharedaddy');
	wp_deregister_style('social-logo');
	wp_deregister_style('jetpack-subscriptions');
	wp_deregister_style('contact-form-7');
	wp_deregister_style('jetpack');
	wp_dequeue_style('jetpack-top-posts-widget');
	wp_dequeue_style('sharedaddy');
	wp_dequeue_style('social-logo');
	wp_dequeue_style('jetpack-subscriptions');
// 	wp_dequeue_style('wpex-ilightbox-light');
	wp_dequeue_style('contact-form-7');
	wp_deregister_style('contact-form-7');
	wp_dequeue_style('jetpack');
	wp_deregister_style('menu-icons-extra');
	wp_dequeue_style('menu-icons-extra');
	wp_deregister_style('ionicons');
	wp_dequeue_style('ionicons');
	wp_deregister_style('elusiveicons');
	wp_dequeue_style('elusiveicons');
	wp_deregister_style('socicon');
	wp_dequeue_style('socicon');
	wp_deregister_style('budicon');
	wp_dequeue_style('budicon');
	wp_deregister_style('map-icons');
	wp_dequeue_style('map-icons');
	wp_deregister_style('themify-icons');
	wp_dequeue_style('themify-icons');
	wp_deregister_style('glyphicons-icons');
	wp_dequeue_style('glyphicons-icons');
	wp_dequeue_script( 'devicepx' );
	wp_dequeue_script( 'gmaps-js' );
	wp_deregister_style('jetpack-widget-social-icons-styles');
	wp_dequeue_style('jetpack-widget-social-icons-styles');
	wp_deregister_style('wp-postratings');
	wp_dequeue_style('wp-postratings');
	wp_deregister_style('bodhi-svgs-attachment');
	wp_dequeue_style('bodhi-svgs-attachment');
		wp_deregister_script( 'devicepx' );
// 		wp_dequeue_script( 'jquery-migrate' );
// 		wp_deregister_script( 'jquery-migrate' );
	if (is_home() || is_front_page()) {
		wp_deregister_style('woocommerce-general');
		wp_deregister_style('wpfla-style-handle');
// 		wp_deregister_style('wpex-woocommerce-responsive');
		wp_dequeue_style('woocommerce-general');
		wp_dequeue_style('wpfla-style-handle');

// 	    wp_deregister_style('elementor-icons');
// 		wp_deregister_style('elementor-animations');
// 		wp_deregister_style('elementor-pro');
// 		wp_dequeue_style('elementor-icons');
// 		wp_dequeue_style('wpex-woocommerce-responsive');
		wp_dequeue_script('wc-cart-fragments');
		wp_dequeue_script('wpex-wc-functions');
	}
	if (! is_singular('post')) {
		wp_deregister_style('lets-review');
		wp_dequeue_style('lets-review');
		wp_dequeue_script('lets-review-js-ext');
		wp_dequeue_script('lets-review-js');
		wp_dequeue_style('powerkit-basic-shortcodes');
		wp_dequeue_style('powerkit-pinterest');
		wp_dequeue_style('powerkit-share-buttons');
		wp_dequeue_style('powerkit-widget-about');
	}
	if (! is_user_logged_in()) {
		wp_dequeue_script('wp-media-picker');
		wp_dequeue_script('rml-general');
		wp_dequeue_style('woocommerce-inline');
		wp_dequeue_style('elementor-frontend');
		wp_dequeue_style('elementor-icons');
	}

}
add_action ('wp_print_styles', 'remove_styles_ok', 999);
add_filter('comment_form_default_fields', 'website_remove');
function website_remove($fields)
{
if(isset($fields['url']))
unset($fields['url']);
return $fields;
}
function custom_excerpt_length( $length ) {
	return 60;
}
add_filter( 'excerpt_length', 'custom_excerpt_length', 999 );
add_filter( 'woocommerce_get_price_html','maybe_hide_price',10,2);
function maybe_hide_price($price_html, $product){
     if($product->get_price()>0){
          return $price_html;
     }
     return '';
 }
function gruz0_remove_noreferrer( $content ) {
	// @todo: Add options where filter will be applied (single post, single page, index page)
	if ( ! is_single() ) {
		return $content;
	}

	$replace = function( $matches ) {
		return sprintf( 'rel="%s"', preg_replace( '/noreferrer\s*/i', '', $matches[1] ) );
	};

	return preg_replace_callback( '/rel="([^\"]+)"/i', $replace, $content );
}
add_filter( 'the_content', 'gruz0_remove_noreferrer', 999 );