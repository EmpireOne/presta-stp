{*
* 2007-2011 PrestaShop 
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2011 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
<!DOCTYPE html>
<html lang="{$lang_iso}">
	<head>
		<title>{$meta_title|escape:'htmlall':'UTF-8'}</title>
		{*<meta name="viewport" content="width=device-width, initial-scale=1">*}
		<meta content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no" name="viewport">
{if isset($meta_description) AND $meta_description}
		<meta name="description" content="{$meta_description|escape:html:'UTF-8'}" />
{/if}
{if isset($meta_keywords) AND $meta_keywords}
		<meta name="keywords" content="{$meta_keywords|escape:html:'UTF-8'}" />
{/if}
		<meta http-equiv="Content-Type" content="application/xhtml+xml; charset=utf-8" />
		<meta name="generator" content="PrestaShop" />
		<meta name="robots" content="{if isset($nobots)}no{/if}index,follow" />
		<link rel="icon" type="image/vnd.microsoft.icon" href="{$favicon_url}?{$img_update_time}" />
		<link rel="shortcut icon" type="image/x-icon" href="{$favicon_url}?{$img_update_time}" />
		<script type="text/javascript">
			var baseDir = '{$content_dir}';
			var fullSiteLink = "{$link->getPageLink('index', true)}?no_mobile_theme";
			var static_token = '{$static_token}';
			var token = '{$token}';
			var priceDisplayPrecision = {$priceDisplayPrecision*$currency->decimals};
			var priceDisplayMethod = {$priceDisplay};
			var roundMode = {$roundMode};
		</script>
{if isset($css_files)}
	{foreach from=$css_files key=css_uri item=media}
	<link href="{$css_uri}" rel="stylesheet" type="text/css" media="{$media}" />
	{/foreach}
{/if}
{if isset($js_files)}
	{foreach from=$js_files item=js_uri}
	<script type="text/javascript" src="{$js_uri}"></script>
	{/foreach}
{/if}
{if isset($css_mobile_dir)}
	<script type="text/javascript" src="{$css_mobile_dir}../js/jqm.autoComplete-1.5.2.js"></script>
	<script type="text/javascript" src="{$css_mobile_dir}../js/jqm-spinbox.min.js"></script>
{/if}
{if isset($countries)}
	<script type="text/javascript">
// <![CDATA[
	$(document).bind('pageshow', function() {
		if(jQuery.isFunction(bindStateInputAndUpdate))
			bindStateInputAndUpdate();
	});
//]]>
	</script>
{/if}
	<script type="text/javascript">
	// <![CDATA[
		$(document).bind('pageshow', function() {
			$('.payment_module').click( function(){
				var href = $(this).find("a:first").attr('href');
				window.location.href = href;
				});
		});

	//]]>
	</script>
<script type="text/javascript">
// <![CDATA[
	$(document).bind('pageshow', function() {
		if ($(".payment_module")[0])
			$(".payment_module").find("img").after("<br style='clear:both'/>")
	});
//]]>
	</script>
	{$HOOK_MOBILE_HEADER}
	</head>
	<body {if isset($page_name)}id="{$page_name|escape:'htmlall':'UTF-8'}"{/if}>
	<div data-role="page" {if isset($wrapper_id)}id="{$wrapper_id}"{/if} class="type-interior prestashop-page">
		<div data-role="header" data-id="header" data-position="fixed" data-theme="a">
			<div data-role="navbar">
				<ul>	
					<li>
						<a href="{$base_dir}" data-icon="presta-home" title="{$shop_name|escape:'htmlall':'UTF-8'}" data-ajax="false">Home</a>
					</li>
						{if !$PS_CATALOG_MODE}
					<li>
								<a href="{$link->getPageLink('order-opc', true)}" data-icon="presta-order" data-ajax="false">{l s='Cart'}</a> <!-- class="link_cart" removed--> 
					</li>	
						{/if}
						{if $logged}
					<li>	
								<a href="{$link->getPageLink('my-account', true)}" data-icon="presta-my-account" data-ajax="false">{l s='My account'}</a> <!-- class="link_account" removed--> 
					</li>	
						{else}
					<li>	
								<a href="{$link->getPageLink('authentication', true)}" data-icon="presta-my-account" data-ajax="false">{l s='Log in'}</a> <!-- class="link_account" removed-->
					</li>	
						{/if}
				</ul>
			</div>
		</div>
		<div class="ui-bar ui-bar-a center">
			<img src="{$logo_url}" alt="{$shop_name|escape:'htmlall':'UTF-8'}" {if $logo_image_width}width="{$logo_image_width}"{/if} {if $logo_image_height}height="{$logo_image_height}" {/if} />
		</div>
		<div id="search_block_top" data-theme="a">
			<form method="get" action="{$base_dir}index.php?controller=search" id="searchbox">
				<input class="search_query" type="text" data-type="search" id="search_query_top" name="search_query" placeholder="{l s='Keyword Search'}" value="" />
				<ul id="suggestions" data-role="listview" data-inset="true" class="ui-listview ui-listview-inset ui-corner-all ui-shadow"></ul>
				<a href="javascript:document.getElementById('searchbox').submit();" data-role="button" data-icon="search" data-theme="a">Browse all results</a>
				<input type="hidden" name="orderby" value="position" />
				<input type="hidden" name="orderway" value="desc" />
				<input type="hidden" name="controller" value="search">		
			</form>
		</div>
<script type="text/javascript">
	// <![CDATA[
		
		$(document).bind('pageshow', function() {
			$('#search_query_top').focus(function() {
			  $('html, body').animate({
				scrollTop: $('#search_query_top').offset().top - 60
				});
			});
			$.mobile.activePage.find("#search_query_top")
				.autocomplete({
				method: 'GET', // allows POST as well
				icon: 'arrow-r', // option to specify icon
				target: $.mobile.activePage.find('#suggestions'), // the listview to receive results
				source: '{$base_dir}search?ajaxSearch=1&id_lang=1&limit=10', // URL return JSON data
				link: '{$base_dir}index.php?controller=product&id_product=', // link to be attached to each result
				minLength: 3, // minimum length of search string
				transition: 'fade',// page transition, default is fade
				termParam : 'q',
				matchFromStart: true, // search from start, or anywhere in the string
				loadingHtml : '<li data-icon="none"><a href="#">Searching...</a></li>', // HTML to display when searching remotely
				interval: 0, // The minimum delay between server calls when using a remote "source"
				builder : null, // optional callback to build HTML for autocomplete
				dataHandler : function(data) {
							var mytab = new Array();
							for (var i = 0; i < data.length; i++)
								mytab[mytab.length] = { value: data[i].id_product, label:data[i].pname  };
							return mytab;
						}, // optional function to convert the received JSON data to the format described below
				klass: 'tinted', // optional class name for listview's <li> tag
				forceFirstChoiceOnEnterKey : true // force the first choice in the list when Enter key is hit
			});
		});	
	// ]]>
</script>
		<div>
			{hook h="displayMobileTop"}
			<!-- Product Categories -->
			{if isset($categoriescmsTree.children)}
			<div id="product_categories" data-role="collapsible">
				<h2 class="title_block">{l s='Products'}</h2>
					{foreach $categoriescmsTree.children as $child}
						{if (isset($child.cms) && $child.id_cms_category eq 2 && $child.cms|@count > 0)}
							<ul data-role="listview" data-inset="true">
							{foreach from=$child.cms item=cms name=cmsTreeBranch}
								<li data-icon="arrow-d"><a href="{$base_dir}search?category={$cms.meta_title|escape:'url'}" title="{$cms.meta_title|escape:'htmlall':'UTF-8'}" data-ajax="false">{$cms.meta_title|escape:'htmlall':'UTF-8'}</a></li>
							{/foreach}
							</ul>
						{/if}
					{/foreach}
			</div>
			{/if}
			<!-- /Product Categories -->
		</div><!-- /header -->