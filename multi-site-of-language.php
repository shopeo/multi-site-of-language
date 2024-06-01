<?php
/**
 * Plugin Name: Multi Site Of Language
 * Plugin URI: https://wordpress.org/plugins/multi-site-of-language
 * Description: Multi Site Of Language
 * Author: SHOPEO
 * Version: 0.0.1
 * Author URI: https://shopeo.cn
 * License: GPL3+
 * Text Domain: multi-site-of-language
 * Domain Path: /languages
 * Requires at least: 5.9
 * Requires PHP: 5.6
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require_once 'vendor/autoload.php';
}

if ( ! defined( 'MULTI_SITE_OF_LANGUAGE_FILE' ) ) {
	define( 'MULTI_SITE_OF_LANGUAGE_FILE', __FILE__ );
}

if ( ! function_exists( 'multi_site_of_language_activation' ) ) {
	function multi_site_of_language_activation() {
	}
}

register_activation_hook( MULTI_SITE_OF_LANGUAGE_FILE, 'multi_site_of_language_activation' );

if ( ! function_exists( 'multi_site_of_language_deactivation' ) ) {
	function multi_site_of_language_deactivation() {
	}
}

register_deactivation_hook( MULTI_SITE_OF_LANGUAGE_FILE, 'multi_site_of_language_deactivation' );

if ( ! function_exists( 'multi_site_of_language_init' ) ) {
	function multi_site_of_language_init() {

		// load text domain
		load_plugin_textdomain( 'multi-site-of-language', false, dirname( plugin_basename( MULTI_SITE_OF_LANGUAGE_FILE ) ) . '/languages' );
	}
}

add_action( 'init', 'multi_site_of_language_init' );

add_action(
	'admin_enqueue_scripts',
	function () {
		$plugin_version = get_plugin_data( MULTI_SITE_OF_LANGUAGE_FILE )['Version'];
		// style

		// script
		wp_enqueue_script( 'multi-site-of-language-admin-script', plugins_url( '/assets/js/admin.js', MULTI_SITE_OF_LANGUAGE_FILE ), array( 'jquery' ), $plugin_version );
		wp_localize_script(
			'multi-site-of-language-admin-script',
			'multi_site_of_language',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
			)
		);
	}
);

add_action(
	'wp_enqueue_scripts',
	function () {
		$plugin_version = get_plugin_data( MULTI_SITE_OF_LANGUAGE_FILE )['Version'];
		// style
		wp_enqueue_style( 'multi-site-of-language-style', plugins_url( '/assets/css/style.css', MULTI_SITE_OF_LANGUAGE_FILE ), array(), $plugin_version );
		wp_style_add_data( 'multi-site-of-language-style', 'rtl', 'replace' );

		// script
		wp_enqueue_script( 'multi-site-of-language-script', plugins_url( '/assets/js/app.js', MULTI_SITE_OF_LANGUAGE_FILE ), array( 'jquery' ), $plugin_version );
		wp_localize_script(
			'multi-site-of-language-script',
			'multi_site_of_language',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
			)
		);
	}
);

if ( ! function_exists( 'multi_site_of_language_register_blocks' ) ) {
	function multi_site_of_language_register_blocks() {
		$blocks = array(
			'site_switch_of_language' => 'site_switch_of_language_dynamic_block_test',
		);
		foreach ( $blocks as $dir => $render_callback ) {
			$args = array();
			if ( ! empty( $render_callback ) ) {
				$args['render_callback'] = $render_callback;
			}
			register_block_type( __DIR__ . '/blocks/dist/' . $dir, $args );
		}
	}
}

add_action( 'init', 'multi_site_of_language_register_blocks' );

if ( ! function_exists( 'site_switch_of_language_dynamic_block_test' ) ) {
	function site_switch_of_language_dynamic_block_test( $attributes ) {
	}
}

if ( ! function_exists( 'is_multisite_enabled' ) ) {
	/**
	 * Check if multisite is enabled
	 *
	 * @return bool
	 */
	function is_multisite_enabled() {
		if ( defined( 'MULTISITE' ) && MULTISITE ) {
			return true;
		} else {
			return false;
		}
	}
}

if ( ! function_exists( 'multi_site_validate_site_data' ) ) {
	function multi_site_validate_site_data( $errors, $data, $old_site ) {
		error_log( print_r( $data, true ) );
		error_log( print_r( $old_site, true ) );
		if ( $_POST['WPLANG'] && $_POST['WPLANG'] != '' ) {
			$lang  = sanitize_text_field( $_POST['WPLANG'] );
			$sites = get_sites( array( 'number' => 0 ) );
			foreach ( $sites as $site ) {
				if ( get_blog_option( $site->blog_id, 'WPLANG' ) == $lang ) {
					$errors->add( 'site_language_already', __( 'This site language is already assigned to another site. Please choose a different language.', 'multi-site-of-language' ) );
				}
			}
		} else {
			$errors->add( 'site_empty_language', __( 'Site language must not be empty.', 'multi-site-of-language' ) );
		}
	}
}

add_action( 'wp_validate_site_data', 'multi_site_validate_site_data', 10, 3 );
