<?php

/**
 * Codeable WooCommerce Custom Etsy Product
 *
 * @package     Codeable
 * @copyright   2016 Websites on Wheels
 *
 * @wordpress-plugin
 * Plugin Name: Codeable WooCommerce Custom Etsy Product
 * Plugin URI:  http://three.codeable.websitesonwheels.net/
 * Description: A Custom Etsy Product Type for WooCommerce
 * Version:     0.1
 * Author:      Jake Chapa
 * Author URI:  https://websitesonwheels.net/
 * Text Domain: codeable-etsy-product
 * License:     Unlicense 
 * License URI: http://unlicense.org/UNLICENSE
 */

use codeable_etsy_product\etsy\EtsyAPI;

if( in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')), true) ) 
{
	require_once __DIR__ .'/vendor/autoload.php';

	define('ETSYPROD_URL', plugin_dir_url(__FILE__));
	define('ETSYPROD_ADMIN_VIEWS', __DIR__ . '/app/admin/views/');
	define('ETSYPROD_FRONTEND_VIEWS', __DIR__ . '/app/frontend/views/');
	define('ETSYPROD_TD', 'codeable-etsy-product');

	if( is_admin() ) {
		$admin = new codeable_etsy_product\admin\EtsyProductAdmin();
	}

	EtsyAPI::api_key(get_option('etsy_api_key'));
}

