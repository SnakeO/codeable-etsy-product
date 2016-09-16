<?php

/**
 * This class is responsible for interacting with the Etsy API
 */

namespace codeable_etsy_product\etsy;

class EtsyAPI
{
	/**
	 * An API key is needed to make calls to Etsy
	 * @var string
	 */
	protected static $api_key;

	/**
	 * The base API URL we are sending our endpoints to
	 * @var string
	 */
	protected static $base_url = 'https://openapi.etsy.com/v2/';

	/**
	 * Initialize the API with the API key
	 * @param string $api_key The API key
	 */
	public static function api_key($api_key) {
		static::$api_key = $api_key;
	}

	/**
	 * Call an Etsy API GET Endpoint.
	 * Example: call('shops/MadamFANDOM?includes=Listings:active:100:0')
	 * 
	 * @param  string $endpoint The endpoint to call
	 * @param  int $max_cache_age 	If we have cached results that are younger than $max_cache_age, return them
	 * @return mixed           The api result
	 */
	public static function get($endpoint, $max_cache_mins=0) 
	{
		if(!static::$api_key) {
			return null;
		}

		$url = static::$base_url . $endpoint;

		// append API key
		if( stripos($url, '?') === false ) {
			$url .= "?api_key=" . static::$api_key;
		}
		else {
			$url .= "&api_key=" . static::$api_key;
		}

		// cached?
		$max_cache_mins = floatval($max_cache_mins);
		$cache_key = 'etsy_' . md5($url);

		if( $max_cache_mins > 0 )
		{
			$cached = get_transient($cache_key);
			
			if( $cached !== false ) {
				return $cached;
			}
		}
	
		$res = json_decode(file_get_contents($url));

		if( $max_cache_mins > 0 ) {
			set_transient($cache_key, $res, $max_cache_mins * 60);
		}

		return $res;
	}
}