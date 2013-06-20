<!-- MODULE TM specials -->
<div id="tmspecials" class="block">
	<h4><a href="{$link->getPageLink('prices-drop.php')}" title="{l s='Specials' mod='tmspecials'}">{l s='Specials' mod='tmspecials'}</a></h4>
	<div class="block_content">
		<ul>
		{foreach from=$products item=product name=products}
		{if $smarty.foreach.products.iteration<=6}
			<li class="ajax_block_product">
				<a class="product_img_link" href="{$product.link}"><img src="{$link->getImageLink($product.link_rewrite, $product.id_image, 'medium')}" alt="{$product.legend|escape:html:'UTF-8'}" title="{$product.name|escape:html:'UTF-8'}" /></a>
				<div>
					<h5><a href="{$product.link}" title="{$product.name|escape:html:'UTF-8'}">{$product.name|escape:html:'UTF-8'|truncate:25:'...'}</a></h5>
					<span class="price">{if !$priceDisplay}{displayWtPrice p=$product.price}{else}{displayWtPrice p=$product.price_tax_exc}{/if}</span>
					<span class="price-discount">{if !$priceDisplay}{displayWtPrice p=$product.price_without_reduction}{else}{displayWtPrice p=$priceWithoutReduction_tax_excl}{/if}</span>
				</div>
			</li>
 		{/if}
 		{/foreach}
		</ul>
	</div>
</div>
<!-- /MODULE TM specials -->