<script type="text/javascript">
jQuery(function($)
{
	$('#product-type').bind('change', function()
	{
		// hide panels we don't use
		if( $('#product-type').val() == 'etsy' )
		{
			// content editor
			$('#postdivrich').hide();

			// images
			$('#postimagediv,#woocommerce-product-images').hide();

			// post excerpt
			$('#postexcerpt').hide();
		}
		else {
			$('#postdivrich,#postimagediv,#woocommerce-product-images,#postexcerpt').show();
		}
	});

	// update the title when an item is selected
	$('#_etsy_listing_id').bind('change', function()
	{
		$('#title').focus().val($(this).find(":selected").text());
	});

	// init
	$('#product-type').change();
});
</script>

<div id="etsy_product_tab" class="panel woocommerce_options_panel show_if_etsy">
	<div class="options_group">

		<?php echo woocommerce_wp_select(array(
			'id'			=> '_etsy_listing_id',
			'label'			=> __('Pick an Etsy Item:', ETSYPROD_TD),
			'options'		=> $etsy_items_select_options
		)) ?>

		<?php echo woocommerce_wp_select(array(
			'id'			=> '_max_cache_age',
			'label'			=> __('Cache for faster displaying:', ETSYPROD_TD),
			'options'		=> array(
				'0'		=> __("Don't cache", ETSYPROD_TD),
				'1'		=> __("1 minute", ETSYPROD_TD),
				'5'		=> __("5 minutes", ETSYPROD_TD),
				'30'	=> __("30 minutes", ETSYPROD_TD),
				'60'	=> __("1 hour", ETSYPROD_TD),
				'1440'	=> __("1 day", ETSYPROD_TD)
			)
		)) ?>

		<p><?php echo __('Product Image and Description are pulled from Etsy. There is need to enter them on this admin screen.', ETSYPROD_TD)?></p>
	</div>
</div>