<?php

/**
 * This class is responsible for preparing the admin for the Etsy Product
 */

namespace codeable_etsy_product\admin;
use codeable_etsy_product\etsy\EtsyAPI;

class EtsyProductAdmin
{
	function __construct()
	{
		$this->addSelector();
		$this->createSettings();
		$this->createProductSettingsTab();
	}

	/**
	 * Add the "Codeable - Etsy Product" to the WooCommerce 
	 * product selector dropdown.
	 */
	protected function addSelector()
	{
		add_filter('product_type_selector', function($selectors)
		{
			$selectors['etsy'] = __('Codeable - Etsy Product', ETSYPROD_TD);
			return $selectors;
		});
	}

	/**
	 * Add a section in the WooCommerce settings page for us.
	 * And show the settings in the admin
	 */
	protected function createSettings()
	{
		add_filter('woocommerce_get_sections_products', function($sections)
		{
			$sections['etsy'] = __('Codeable - Etsy Product Settings', ETSYPROD_TD);
			return $sections;
		});

		add_filter('woocommerce_get_settings_products', function($settings, $current_section)
		{
			if( $current_section != 'etsy') {
				return $settings;
			}

			$settings = [];

			$settings[] =  array( 
				'name' 	=> __('Etsy Settings', ETSYPROD_TD), 
				'type' 	=> 'title', 
				'desc' 	=> __('The following options are used to setup Etsy Products', ETSYPROD_TD ), 
				'id' 	=> 'etsy' 
			);

			$settings[] = array(
				'name'		=> __('Etsy API Key', ETSYPROD_TD),
				'type'		=> 'text',
				'desc'		=> __('Paste Your Etsy API Key. One has been pre-filled for you.', ETSYPROD_TD),
				'default'	=> 'd15ljgn5jjng8a3y3w0iqeb7',
				'id'		=> 'etsy_api_key'
			);

			$settings[] = array(
				'name'		=> __('Etsy Shop Name', ETSYPROD_TD),
				'type'		=> 'text',
				'desc'		=> __('Enter Your Etsy Shop name. One has been pre-filled for you as an example.', ETSYPROD_TD),
				'default'	=> 'MadamFANDOM',
				'id'		=> 'etsy_shop_name'
			);

			$settings[] = array(
				'type'	=> 'sectionend',
				'id'	=> 'etsy'
			);

			return $settings;

		}, 10, 2);
	}

	/**
	 * Add a tab in the WooCommerce admin
	 * for settings specific to this Etsy Product
	 */
	protected function createProductSettingsTab()
	{
		// show the tab
		add_filter('woocommerce_product_data_tabs', function($tabs)
		{
			$tabs['etsy-tab'] = array(
				'label'		=> __('Etsy Product', ETSYPROD_TD),
				'target'	=> 'etsy_product_tab',
				'class'		=> array('show_if_etsy'),
			);

			// hide the other tabs
			$tabs['shipping']['class'][] 		= 'hide_if_etsy';
			$tabs['linked_product']['class'][] 	= 'hide_if_etsy';
			$tabs['attribute']['class'][] 		= 'hide_if_etsy';
			$tabs['advanced']['class'][] 		= 'hide_if_etsy';

			return $tabs;

		}, 99, 1);

		// show the tab options
		add_filter('woocommerce_product_data_panels', function()
		{
			global $woocommerce, $post;

			// pull the listings for this shop
			$shop = get_option('etsy_shop_name');
			$res = EtsyAPI::get("shops/$shop?includes=Listings:active:100:0", 30);
			$listings = @$res->results[0]->Listings ?: [];

			$etsy_items_select_options = array();
			foreach($listings as $listing) {
				$etsy_items_select_options[$listing->listing_id] = $listing->title;
			}

			include ETSYPROD_ADMIN_VIEWS . 'etsy-product-tab.php';
		});

		// save the tab options
		add_action( 'woocommerce_process_product_meta', function($post_id)
		{
			if( !isset($_POST['_etsy_listing_id']) ) {
				return;
			}

			update_post_meta($post_id, '_etsy_listing_id', $_POST['_etsy_listing_id']);
			update_post_meta($post_id, '_max_cache_age', $_POST['_max_cache_age']);
		});
	}
}