<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="{$lang_iso}">
<head>
	<title>{$meta_title|escape:'htmlall':'UTF-8'}</title>
{if isset($meta_description) AND $meta_description}
	<meta name="description" content="{$meta_description|escape:html:'UTF-8'}" />
{/if}
{if isset($meta_keywords) AND $meta_keywords}
	<meta name="keywords" content="{$meta_keywords|escape:html:'UTF-8'}" />
{/if}
	<meta http-equiv="Content-Type" content="application/xhtml+xml; charset=utf-8" />
	<meta name="generator" content="PrestaShop" />
	<meta name="robots" content="{if isset($nobots)}no{/if}index,follow" />
	<link rel="icon" type="image/vnd.microsoft.icon" href="{$img_ps_dir}favicon.ico?{$time}" />
	<link rel="shortcut icon" type="image/x-icon" href="{$img_ps_dir}favicon.ico?{$time}" />
	<script type="text/javascript">
		var baseDir = '{$content_dir}';
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
	{$HOOK_HEADER}
</head>
<body {if $page_name}id="{$page_name|escape:'htmlall':'UTF-8'}"{/if}>
<!--[if lt IE 7]><div style='clear:both;height:59px;padding:0 15px 0 15px;position:relative;z-index:10000;text-align:center;'><a href="http://www.microsoft.com/windows/internet-explorer/default.aspx?ocid=ie6_countdown_bannercode"><img src="http://www.theie6countdown.com/images/upgrade.jpg" border="0" height="42" width="820" alt="You are using an outdated browser. For a faster, safer browsing experience, upgrade for free today." /></a></div><![endif]-->
{if isset($ad) && isset($live_edit)}{include file="$tpl_dir./live_edit.tpl"}{/if}
{if !$content_only}
{if isset($restricted_country_mode) && $restricted_country_mode}<div id="restricted-country"><p>{l s='You cannot place a new order from your country.'} <span class="bold">{$geolocation_country}</span></p></div>{/if}
<div id="wrapper1">
<div id="wrapper2">
<div id="wrapper3">
	<!-- Header -->
	<div id="header">
		<a id="header_logo" href="{$base_dir}" title="{$shop_name|escape:'htmlall':'UTF-8'}">
			<img class="logo" src="{$img_ps_dir}logo.jpg?{$time}" alt="{$shop_name|escape:'htmlall':'UTF-8'}" />
		</a>
		<div id="header_right">
			{$HOOK_TOP}
		</div>
	</div>
	<div id="columns">
<!-- Left -->
		<div id="left_column" class="column">{$HOOK_LEFT_COLUMN}</div>
<!-- Center -->
		<div id="center_column" class="center_column">
{/if}
