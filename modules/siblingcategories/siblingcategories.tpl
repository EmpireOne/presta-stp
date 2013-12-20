<!-- Sibling categories module -->
{if isset($sibling_categories)}
<div id="sibling_categories_block_left" class="block" data-role="collapsible">
	{if $top_category}
	<h4 class="title_block">{l s='Top Categories' mod='siblingcategories'}</h4>
	{else}
	<h4 class="title_block">{l s='Sibling Categories' mod='siblingcategories'}</h4>
	{/if}
	<div class="block_content">
		<ul class="tree" data-role="listview" data-inset="true">
		{foreach from=$sibling_categories item=subcategory}
			<li data-icon="arrow-d">
				<a class="cat_text" href="{$link->getCategoryLink($subcategory.id_category, $subcategory.link_rewrite)|escape:'htmlall':'UTF-8'}">{$subcategory.name|escape:'htmlall':'UTF-8'}</a> 
			</li>
		{/foreach}
		</ul>
	</div>
</div>
{/if}
<!-- /Sibling categories module -->

