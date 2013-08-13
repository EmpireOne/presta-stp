<!-- MODULE Home Featured Products -->
<div id="featured-products_block_center">
	<h4>{l s='Featured products' mod='homefeatured'}</h4>
	{if isset($products) AND $products}
	<div class="block_content">
		<ul>
			{foreach from=$products item=product name=homeFeaturedProducts}
			<li class="ajax_block_product{cycle values=' feat1, feat2, feat3, feat4'}">
				<h5><a class="product_link" href="{$product.link}" title="{$product.name|truncate:32:'...'|escape:'htmlall':'UTF-8'}">{$product.name|truncate:40:'...'|escape:'htmlall':'UTF-8'}</a></h5>
				<a class="product_image" href="{$product.link}" title="{$product.name|escape:html:'UTF-8'}"><img src="{$link->getImageLink($product.link_rewrite, $product.id_image, 'home')}" alt="{$product.name|escape:html:'UTF-8'}" /></a>
				<span class="price">{if $product.show_price AND !isset($restricted_country_mode) AND !$PS_CATALOG_MODE}{if !$priceDisplay}{convertPrice price=$product.price}{else}{convertPrice price=$product.price_tax_exc}{/if}{/if}</span>
				<div>
					{if ($product.id_product_attribute == 0 OR (isset($add_prod_display) AND ($add_prod_display == 1))) AND $product.available_for_order AND !isset($restricted_country_mode) AND !$PS_CATALOG_MODE}
						{if ($product.quantity > 0 OR $product.allow_oosp) AND $product.customizable != 2}
						<a class="exclusive ajax_add_to_cart_button" rel="ajax_id_product_{$product.id_product}" href="{$link->getPageLink('cart.php')}?qty=1&amp;id_product={$product.id_product}&amp;token={$static_token}&amp;add" title="{l s='Add to cart' mod='homefeatured'}">{l s='Add to cart' mod='homefeatured'}<span>&nbsp;&gt;</span></a>
						{else}
						<span class="exclusive">{l s='Add to cart' mod='homefeatured'}<span>&nbsp;&gt;</span></span>
						{/if}
					{/if}
				</div>
			</li>
			{/foreach}
		</ul>
	</div>
	{else}
	<p>{l s='No featured products' mod='homefeatured'}</p>
	{/if}
</div>
<!-- /MODULE Home Featured Products -->