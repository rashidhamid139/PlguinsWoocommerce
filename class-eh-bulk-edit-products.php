<?php
/**
 *
 * Main File for loading classes.
 *
 * @package ELEX Bulk Edit Products, Prices & Attributes for Woocommerce
 */

/*
Plugin Name: ELEX Bulk Edit Products, Prices & Attributes for Woocommerce
Plugin URI: https://elextensions.com/plugin/bulk-edit-products-prices-attributes-for-woocommerce/
Description: Bulk Edit Products, Prices & Attributes for Woocommerce allows you to edit products prices and attributes as Bulk.
Version: 2.6.1
WC requires at least: 2.6.0
WC tested up to: 4.7
Author: ELEX
Author URI: http://elextensions.com/
Text Domain: eh_bulk_edit
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! defined( 'EH_BEP_DIR' ) ) {
	define( 'EH_BEP_DIR', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'EH_BEP_TEMPLATE_PATH' ) ) {
	define( 'EH_BEP_TEMPLATE_PATH', EH_BEP_DIR . 'templates' );
}
require_once ABSPATH . 'wp-admin/includes/plugin.php';

// Change the Pack IF BASIC  mention switch('BASIC') ELSE mention switch('PREMIUM').
switch ( 'PREMIUM' ) {
	case 'PREMIUM':
		$conflict = 'basic';
		$base     = 'premium';
		break;
	case 'BASIC':
		$conflict = 'premium';
		$base     = 'basic';
		break;
}
// Enter your plugin unique option name below $option_name variable.
$option_name = 'eh_bulk_edit_pack';
if ( get_option( $option_name ) === $conflict ) {
	add_action( 'admin_notices', 'eh_wc_admin_notices', 99 );
	deactivate_plugins( plugin_basename( __FILE__ ) );
	/** Admin Notices. */
	function eh_wc_admin_notices() {
		is_admin() && add_filter(
			'gettext',
			function( $translated_text, $untranslated_text, $domain ) {
				$old        = array(
					'Plugin <strong>activated</strong>.',
					'Selected plugins <strong>activated</strong>.',
				);
				$error_text = '';
				// Change the Pack IF BASIC  mention switch('BASIC') ELSE mention switch('PREMIUM').
				switch ( 'PREMIUM' ) {
					case 'PREMIUM':
						$error_text = 'BASIC Version of this Plugin Installed. Please uninstall the BASIC Version before activating PREMIUM.';
						break;
					case 'BASIC':
						$error_text = 'PREMIUM Version of this Plugin Installed. Please uninstall the PREMIUM Version before activating BASIC.';
						break;
				}
				$new = '<span style="color:red">' . $error_text . '</span>';
				if ( in_array( $untranslated_text, $old, true ) ) {
					$translated_text = $new;
				}
				return $translated_text;
			},
			99,
			3
		);}
	return;
} else {
	update_option( $option_name, $base );
	register_deactivation_hook( __FILE__, 'eh_bulk_edit_deactivate_work' );
	/**  Enter your plugin unique option name below update_option function. */
	function eh_bulk_edit_deactivate_work() {
		update_option( 'eh_bulk_edit_pack', '' );
	}
	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	if ( is_plugin_active( 'woocommerce/woocommerce.php') ) {
		/**
		 *  Bulk Product Edit class
		 */
		class Eh_Bulk_Edit_Products {
			/** Constructor. */
			public function __construct() {
				add_filter(
					'plugin_action_links_' . plugin_basename( __FILE__ ),
					array(
						$this,
						'eh_bep_action_link',
					)
				); // to add settings, doc, etc options to plugins base.
				$this->eh_bep_include_lib();
			}
			/** Include Lib. */
			public function eh_bep_include_lib() {
				include_once 'includes/class-eh-bulk-edit-init.php';
			}
			/** Action Link.
			 *
			 * @param var $links links.
			 */
			public function eh_bep_action_link( $links ) {
				$plugin_links = array(
					'<a href = "' . admin_url( 'admin.php?page=eh-bulk-edit-product-attr' ) . '">' . __( 'Bulk Edit Products', 'eh_bulk_edit' ) . '</a>',
					'<a href = "https://elextensions.com/documentation/#elex-bulk-edit-products-prices-attributes-for-woocommerce" target="_blank">' . __( 'Documentation', 'eh_bulk_edit' ) . '</a>',
					'<a href = "https://elextensions.com/support/" target="_blank">' . __( 'Support', 'eh_bulk_edit' ) . '</a>',
				);
				return array_merge( $plugin_links, $links );
			}
		}
		new Eh_Bulk_Edit_Products();
	}
}
/** Load Plugin Text Domain. */
function elex_bep_load_plugin_textdomain() {
	load_plugin_textdomain( 'eh_bulk_edit', false, basename( dirname( __FILE__ ) ) . '/lang/' );
}
add_action( 'plugins_loaded', 'elex_bep_load_plugin_textdomain' );
