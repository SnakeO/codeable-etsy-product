<?php

/**
 * This class is responsible for packaging the data necessary for 
 * the Etsy Product to be displayed.
 */

namespace codeable_etsy_product\frontend;

use codeable_etsy_product\etsy\EtsyAPI;

class EtsyProduct extends \WC_Product
{
	/**
	 * This is the result of the Etsy API call, which has all
	 * the data about this etsy listing
	 * @var StdClass
	 */
	protected $etsy_listing;

	/**
	 * The Etsy listing_id associated with this product
	 * @var int
	 */
	protected $etsy_listing_id;

	/**
	 * This variable is used for bookkeeping 
	 * to see what steps have been taken for initialization.
	 * @var array
	 */
	protected static $initialized = array();

	function __construct($product)
	{
		$this->product_type = 'etsy';
		$this->etsy_listing_id = get_post_meta($product->ID, '_etsy_listing_id', true);

		$this->initEtsyListing(get_post_meta($product->ID, '_max_cache_age', true));
		$product = $this->hijackWordpressPost($product);
		$this->setupAddToCartBtn();
		$this->forceDescriptionTab();

		parent::__construct($product);
	}

	/**
	 * Initialize the Etsy Listing associated with this product
	 * 
	 * @param  int $max_cache_age The maximum allowed cache age (in minutes)
	 * @return Object The Etsy Listing
	 */
	protected function initEtsyListing($max_cache_age=0)
	{
		if($this->etsy_listing_id == null) {
			return null;
		}

		$res = EtsyAPI::get("listings/$this->etsy_listing_id?includes=Images", $max_cache_age);
		$this->etsy_listing = @$res->results[0] ?: null;
	}

	/**
	 * Inject the etsy listing into a WP_Post object, so that
	 * WooCommerce will pull the etsy data when reading it
	 * 	
	 * @return WP_Post a WP_Post object
	 */
	protected function hijackWordpressPost($product)
	{
		// The Post Title
		add_filter('the_title', function($title, $post_id) use ($product)
		{
			if( $post_id != $product->ID ) {
				return $title;
			}

			return $this->etsy_listing->title;
		}, 10, 2);

		// The Post Description
		add_filter('the_content', function($content, $post_id=null) use ($product)
		{
			// $post_id is null if it's pullin an excerpt
			if( $post_id != null && $post_id != $product->ID ) {
				return $content;
			}

			return nl2br($this->etsy_listing->description);
		}, 10, 2);

		
		// The Post Featured Image - Pt 1
		add_filter('get_post_metadata', function($ignore, $object_id, $meta_key, $single) use ($product)
		{
			if( $object_id != $product->ID ) {
				return null;
			}

			if( $meta_key == '_regular_price' || $meta_key == '_sale_price' || $meta_key == '_price') {
				return $this->etsy_listing->price;
			}

			if( $meta_key == '_thumbnail_id' && count($this->etsy_listing->Images) > 0 ) 
			{
				// We use the negative of the $product_id to create an attachment that will hold
				// the Etsy featured image.
				$attachment = new \WP_Post(new \StdClass());
				$attachment->ID = -$object_id;
				$attachment->post_type = 'attachment';
				$attachment->guid = $this->etsy_listing->Images[0]->url_fullxfull;

				// This is necessary for get_post() to return something to wordpress
				wp_cache_set(-$object_id, $attachment, 'posts');

				return -$object_id;
			}

			// _product_image_gallery?
			return null;

		}, 10, 4);

		// The Post Featured Image - Pt 2
		add_filter('wp_get_attachment_image_src', function($image, $attachment_id, $size, $icon) use ($product)
		{
			//var_dump('wp_get_attachment_image_src', $image, $attachment_id, $size, $icon);
			if( $attachment_id != -$product->ID ) {
				return $image;
			}

			$img = $this->etsy_listing->Images[0];
			return [$img->url_fullxfull, $img->full_width, $img->full_height, false];

		}, 10, 4);

		// The Post Featured Image - Pt 3
		add_filter('wp_get_attachment_image_attributes', function($attr, $attachment, $size) use ($product)
		{
			//var_dump('wp_get_attachment_image_attributes', $attr, $attachment, $size);
			if( $attachment->ID != -$product->ID ) {
				return $attr;
			}

			$img = $this->etsy_listing->Images[0];
			
			$attr['src'] = $img->url_fullxfull;
			$attr["srcset"] = "$img->url_75x75 75w, $img->url_170x135 170w, $img->url_570xN 570w";
  			$attr["sizes"] = "";

			return $attr;

		}, 10, 3);

		return $product;
	}

	/**
	 * WooCommerce will not show the "Description" tab if the post_content is empty.
	 * Since we are pulling the description from Etsy, we will force it to be shown here.
	 */
	protected function forceDescriptionTab()
	{
		add_filter( 'woocommerce_product_tabs', function($tabs)
		{
			if( strlen($this->etsy_listing->description) == 0 ) {
				return $tabs;
			}

			$tabs['description'] = array(
				'title'    => __( 'Description', 'woocommerce' ),
				'priority' => 10,
				'callback' => 'woocommerce_product_description_tab'
			);

			return $tabs;

		}, 10, 1);
	}

	/**
	 * Our "Add to Cart" button is a button that will take them to etsy.com
	 */
	protected function setupAddToCartBtn()
	{
		// without this, we get a double button on the page
		if( @static::$initialized['add-to-cart-btn'] ) {
			return false;
		}

		add_action('woocommerce_etsy_add_to_cart', function()
		{
			$data = array(
				'btn_url'	=> $this->etsy_listing->url
			);

			wc_get_template('add-to-cart-btn.php', $data, '/', ETSYPROD_FRONTEND_VIEWS);
		});

		static::$initialized['add-to-cart-btn'] = true;
	}
}