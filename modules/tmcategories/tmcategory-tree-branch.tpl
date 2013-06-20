{*
<li class="item{if $node.children|@count > 0} submenu{/if}{if isset($last) && $last == 'true'} last{/if}">
	<a href="{$node.link}"{if isset($currentCategoryId) && ($node.id == $currentCategoryId)} class="selected"{/if} title="{$node.desc|escape:html:'UTF-8'}">{$node.name|escape:html:'UTF-8'}</a>
	{if $node.children|@count > 0}
		<ul class="subcategories">
		{foreach from=$node.children item=child name=categoryTreeBranch}
			{if isset($smarty.foreach.categoryTreeBranch) && $smarty.foreach.categoryTreeBranch.last}
				{include file="$branche_tpl_path" node=$child last='true'}
			{else}
				{include file="$branche_tpl_path" node=$child last='false'}
			{/if}
		{/foreach}
		</ul>
	{/if}
</li>
*}
<li class="item{if isset($currentCategoryId) && ($node.id == $currentCategoryId)} selected{/if}{if $node.children|@count > 0} submenu{/if}{if isset($last) && $last == 'true'} last{/if}">
	<a href="{$node.link}" title="{$node.desc|escape:html:'UTF-8'}">{$node.name|escape:html:'UTF-8'}</a>
	{if $node.children|@count > 0}
		<ul class="subcategories">
		{foreach from=$node.children item=child}
			<li><a href="{$child.link|escape:html:'UTF-8'}">{$child.name|escape:html:'UTF-8'}</a></li>
		{/foreach}
		</ul>
	{/if}
</li>