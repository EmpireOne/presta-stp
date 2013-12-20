{*
* 2007-2013 PrestaShop
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
*  @copyright  2007-2013 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

{capture assign='page_title'}{l s='Shipping:'}{/capture}
{include file='./page-title.tpl'}


<script type="text/javascript">
	// <![CDATA[
	var orderProcess = 'order';
	var currencySign = '{$currencySign|html_entity_decode:2:"UTF-8"}';
	var currencyRate = '{$currencyRate|floatval}';
	var currencyFormat = '{$currencyFormat|intval}';
	var currencyBlank = '{$currencyBlank|intval}';
	var txtProduct = "{l s='product' js=1}";
	var txtProducts = "{l s='products' js=1}";
	var orderUrl = '{$link->getPageLink("order", true)}';

	var msg = "{l s='You must agree to the terms of service before continuing.' js=1}";
	{literal}
	function acceptCGV()
	{
		if ($('#cgv').length && !$('input#cgv:checked').length)
		{
			alert(msg);
			return false;
		}
		else
			return true;
	}
	{/literal}
	$(document).bind('pageshow', function() {
		$('.delivery_option').click( function(){
			$('.delivery_option.delivery_selected').removeClass('delivery_selected');
			$(this).addClass('delivery_selected'); 
			$(this).find("input[type=radio]").prop("checked", true);
			if(jQuery.isFunction(updateCarrierSelectionAndGift))
				updateCarrierSelectionAndGift();
			});
		});
	//]]>
</script>
	
<div data-role="content" id="delivery_choose">
	<h3  class="bg">{l s='Choose your delivery method'}</h3>
	<fieldset data-role="none">
	{if isset($delivery_option_list)}
		{foreach $delivery_option_list as $id_address => $option_list}
			{foreach $option_list as $key => $option}
				<div class="delivery_option ui-shadow ui-corner-all ui-btn-up-c {if ($option@index % 2)}alternate_{/if}item">
					<table style="width:100%">
						<tr>
							<td style="width:10%; text-align:center" rowspan="2" class="delivery_option_action radio">
								<input data-role="none" class="delivery_option_radio" type="radio" name="delivery_option[{$id_address}]" onchange="{if $opc}updateCarrierSelectionAndGift();{else}updateExtraCarrier('{$key}', {$id_address});{/if}" id="delivery_option_{$id_address}_{$option@index}" value="{$key}" {if isset($delivery_option[$id_address]) && $delivery_option[$id_address] == $key}checked="checked"{/if} />
							</td>
							<td style="width:45%; text-align:left" class="delivery_option_name">
								<label data-role="none" for="delivery_option_{$id_address}_{$option@index}">
									{if $option.unique_carrier}
										{foreach $option.carrier_list as $carrier}
											<div class="delivery_option_title">{$carrier.instance->name}</div>
										{/foreach}
									{/if}
								</label>
							</td>
							<td style="width:45%; text-align:right" class="delivery_option_price">
								<label data-role="none" for="delivery_option_{$id_address}_{$option@index}">
									{if $option.total_price_with_tax && !$free_shipping}
										{if $use_taxes == 1}
											<span style="font-size:1.3em">{convertPrice price=$option.total_price_with_tax}</span><br /><span style="font-size:0.8em">{l s='(tax incl.)'}</span>
										{else}
											<span style="font-size:1.3em">{convertPrice price=$option.total_price_without_tax}</span><br /><span style="font-size:0.8em">{l s='(tax excl.)'}</span>
										{/if}
									{else}
										<span style="font-size:1.3em">{l s='Free'}</span>
									{/if}
								</label>
							</td>
						</tr>
						{foreach $option.carrier_list as $carrier}
						<tr class="delivery_option_carrier_desc">
							{if !$option.unique_carrier}
							<td class="first_item" colspan="2">
								<label data-role="none" for="delivery_option_{$id_address}_{$option@index}">
									<input type="hidden" value="{$carrier.instance->id}" name="id_carrier" />
									{$carrier.instance->name}
								</label>
							</td>
							{/if}
							<td {if $option.unique_carrier}class="first_item" colspan="2"{/if}>
								<label data-role="none" for="delivery_option_{$id_address}_{$option@index}">
									<input type="hidden" value="{$carrier.instance->id}" name="id_carrier" />
									{if isset($carrier.instance->delay[$cookie->id_lang])}
										<span style="font-size:0.8em;">{$carrier.instance->delay[$cookie->id_lang]}</span><hr style="margin:3px 0"/>
										<div data-role="collapsible" data-mini="true">
											<h5>
											{if count($carrier.product_list) <= 1}
												{l s='Product concerned:'}
											{else}
												{l s='Products concerned:'}
											{/if}
											</h5>
											<span style="font-size:0.6em;font-weight:normal">
											{* This foreach is on one line, to avoid tabulation in the title attribute of the acronym *}
											{foreach $carrier.product_list as $product}
											{if $product@index == 4}<acronym title="{/if}{if $product@index >= 4}{$product.name}{if !$product@last}, {else}">...</acronym>){/if}{else}{$product.name}{if !$product@last}, {else}{/if}{/if}{/foreach}
											</span>	
										</div>
									{/if}
								</label>
							</td>
						</tr>
						{/foreach}
					</table>
				</div>
			{/foreach}
		{/foreach}
	{/if}
	</fieldset>
	{if $cart->recyclable == 1}
	<fieldset data-role="fieldcontain">
		<input type="checkbox" name="same" id="recyclable" value="1" class="delivery_option_radio" {if $recyclable == 1}checked="checked"{/if} />
		<label for="recyclable">{l s='I agree to receive my order in recycled packaging'}.</label>
	</fieldset>
	{/if}
	{if $giftAllowed}
		<h3 class="gift_title">{l s='Gift'}</h3>
		<p class="checkbox">
			<input type="checkbox" name="gift" id="gift" value="1" {if $cart->gift == 1}checked="checked"{/if} />
			<label for="gift">{l s='I would like my order to be gift wrapped.'}</label>
			<br />
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			{if $gift_wrapping_price > 0}
				({l s='Additional cost of'}
				<span class="price" id="gift-price">
					{if $priceDisplay == 1}{convertPrice price=$total_wrapping_tax_exc_cost}{else}{convertPrice price=$total_wrapping_cost}{/if}
				</span>
				{if $use_taxes}{if $priceDisplay == 1} {l s='(tax excl.)'}{else} {l s='(tax incl.)'}{/if}{/if})
			{/if}
		</p>
		<p id="gift_div" class="textarea">
			<label for="gift_message">{l s='If you\'d like, you can add a note to the gift:'}</label>
			<textarea rows="5" cols="35" id="gift_message" name="gift_message">{$cart->gift_message|escape:'htmlall':'UTF-8'}</textarea>
		</p>
	{/if}
	
	<h3 class="bg">{l s='Terms of service'}</h3>
	<fieldset data-role="fieldcontain" id="cgv_checkbox">
		<input type="checkbox" value="1" id="cgv" name="cgv" {if $checkedTOS}checked="checked"{/if} />
		<label for="cgv">{l s='I agree to the terms of service and will adhere to them unconditionally.'}</label>
	</fieldset>
	<p class="lnk_CGV"><a href="{$link_conditions}" data-role="button" data-theme="a" data-mini="true" target="_blank" data-ajax="false">{l s='Read Terms of Service'}</a></p>
</div>
