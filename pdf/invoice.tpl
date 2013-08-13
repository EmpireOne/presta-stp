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

<table style="font-size: 8pt; color: #222">
	<tr>
		<td style="width: 60%;">
			<table>
				<tr>
					<td><span>{$shop_address_vertical}</span><br/></td>
				</tr>
				<tr>
					<td style="width: 50%;"><span style="font-weight: bold;">Sold to:</span></td>
				</tr>
				<tr>
					<td><span>{$invoice_address}</span><br /></td>
				</tr>
				<tr>
					<td style="width: 50%;"><span style="font-weight: bold;">Shipped to:</span></td>
				</tr>
				<tr>
					<td><span>{if !empty($delivery_address)}{$delivery_address}{else}Same{/if}</span><br /></td>
				</tr>
			</table>
		</td>
		<td style="width: 40%;">

			<table style="font-size: 8pt;">
				<tr>
					<td style="width: 49.5%;"><span style="text-align:right; font-weight: bold;">Invoice Number &nbsp;</span></td>
					<td style="width: 0.5%; background-color:#888;"></td>
					<td style="width: 49.5%;"><span> {trim(str_replace("Invoice ","",$title))|escape:'htmlall':'UTF-8'}</span></td>
				</tr>
				<tr>
					<td style="width: 49.5%;"><span style="text-align:right; font-weight: bold;">Invoice Date &nbsp;</span></td>
					<td style="width: 0.5%; background-color:#888;"></td>
					<td style="width: 49.5%;"><span> {date("F j, Y", strtotime($date))}</span></td>
				</tr>
				<tr>
					<td style="width: 49.5%;"><span style="text-align:right; font-weight: bold;">Terms &nbsp;</span></td>
					<td style="width: 0.5%; background-color:#888;"></td>
					<td style="width: 49.5%;"><span>{foreach from=$order_invoice->getOrderPaymentCollection() item=payment}<span style="white-space:nowrap">&nbsp;{$payment->payment_method}</span><br />{foreachelse}{l s='No payment' pdf='true'}{/foreach}</span></td>
				</tr>
				<tr>
					<td style="width: 49.5%;"><span style="text-align:right; font-weight: bold;">Sales Rep &nbsp;</span></td>
					<td style="width: 0.5%; background-color:#888;"></td>
					<td style="width: 49.5%;"><span> Name</span></td>
				</tr>
			</table>
		</td>
	</tr>
</table>

<div style="font-size: 8pt; color: #333">


<div style="line-height: 1pt">&nbsp;</div>

<!-- PRODUCTS TAB -->
<table style="width: 100%">
	<tr>
		<td style="width: 15%; padding-right: 7px; text-align: right; vertical-align: top; font-size: 7pt;">
			<!-- CUSTOMER INFORMATION -->
			<b>{l s='Order Number:' pdf='true'}</b><br />
			{$order->getUniqReference()}<br />
			<!-- / CUSTOMER INFORMATION -->
		</td>
		<td style="width: 85%; text-align: right">
			<table style="width: 100%; font-size: 8pt;">
				<tr style="line-height:4px;">
					<td style="background-color: #4D4D4D; color: #FFF; text-align: center; font-weight: bold; width: 10%">{l s='Qty' pdf='true'}</td>
					<td style="text-align: left; background-color: #4D4D4D; color: #FFF; padding-left: 10px; font-weight: bold; width: 45%">{l s='Description' pdf='true'}</td>
					<!-- unit price tax excluded is mandatory -->
					{if !$tax_excluded_display}
						<td style="background-color: #4D4D4D; color: #FFF; text-align: right; font-weight: bold; width: 15%">{l s='List Price' pdf='true'} <br />{l s='(Tax Excl.)' pdf='true'}</td>
					{/if}
					<td style="background-color: #4D4D4D; color: #FFF; text-align: right; font-weight: bold; width: 15%">
						{l s='Unit Price' pdf='true'}<br />
						{if $tax_excluded_display}
							 {l s='(Tax Excl.)' pdf='true'}
						{else}
							 {l s='(Tax Incl.)' pdf='true'}
						{/if}
					</td>
					<td style="background-color: #4D4D4D; color: #FFF; text-align: right; font-weight: bold; width: {if !$tax_excluded_display}15%{else}30%{/if}">
						{l s='Amount' pdf='true'}<br />
						{if $tax_excluded_display}
							{l s='(Tax Excl.)' pdf='true'}
						{else}
							{l s='(Tax Incl.)' pdf='true'}
						{/if}
					</td>
				</tr>
				<!-- PRODUCTS -->
				{foreach $order_details as $order_detail}
				{cycle values='#FFF,#DDD' assign=bgcolor}
				<tr style="line-height:6px;background-color:{$bgcolor};">
					<td style="text-align: center; width: 10%">{$order_detail.product_quantity}</td>
					<td style="text-align: left; width: 45%">{$order_detail.product_name}</td>
					<!-- unit price tax excluded is mandatory -->
					{if !$tax_excluded_display}
						<td style="text-align: right; width: 15%">
						{displayPrice currency=$order->id_currency price=$order_detail.unit_price_tax_excl}
						</td>
					{/if}
					<td style="text-align: right; width: 15%">
					{if $tax_excluded_display}
						{displayPrice currency=$order->id_currency price=$order_detail.unit_price_tax_excl}
					{else}
						{displayPrice currency=$order->id_currency price=$order_detail.unit_price_tax_incl}
					{/if}
					</td>
					
					<td style="width: 15%; text-align: right;  width: {if !$tax_excluded_display}15%{else}30%{/if}">
					{if $tax_excluded_display}
						{displayPrice currency=$order->id_currency price=$order_detail.total_price_tax_excl}
					{else}
						{displayPrice currency=$order->id_currency price=$order_detail.total_price_tax_incl}
					{/if}
					</td>
				</tr>
					{foreach $order_detail.customizedDatas as $customizationPerAddress}
						{foreach $customizationPerAddress as $customizationId => $customization}
							<tr style="line-height:6px;background-color:{$bgcolor}; ">
								<td style="text-align: center; width: 10%; vertical-align: top">({$customization.quantity})</td>
								<td style="line-height:3px; text-align: left; width: 60%; vertical-align: top">

										<blockquote>
											{if isset($customization.datas[$smarty.const._CUSTOMIZE_TEXTFIELD_]) && count($customization.datas[$smarty.const._CUSTOMIZE_TEXTFIELD_]) > 0}
												{foreach $customization.datas[$smarty.const._CUSTOMIZE_TEXTFIELD_] as $customization_infos}
													{$customization_infos.name}: {$customization_infos.value}
													{if !$smarty.foreach.custo_foreach.last}<br />
													{else}
													<div style="line-height:0.4pt">&nbsp;</div>
													{/if}
												{/foreach}
											{/if}

											{if isset($customization.datas[$smarty.const._CUSTOMIZE_FILE_]) && count($customization.datas[$smarty.const._CUSTOMIZE_FILE_]) > 0}
												{count($customization.datas[$smarty.const._CUSTOMIZE_FILE_])} {l s='image(s)' pdf='true'}
											{/if}
										</blockquote>
								</td>
								<td style="width: 15%; text-align: right;"></td>
							</tr>
						{/foreach}
					{/foreach}
				{/foreach}
				<!-- END PRODUCTS -->

				<!-- CART RULES -->
				{assign var="shipping_discount_tax_incl" value="0"}
				{foreach $cart_rules as $cart_rule}
					{if $cart_rule.free_shipping}
						{assign var="shipping_discount_tax_incl" value=$order_invoice->total_shipping_tax_incl}
					{/if}
					{cycle values='#FFF,#DDD' assign=bgcolor}
					<tr style="line-height:6px;background-color:{$bgcolor}" text-align="left">
						<td style="line-height:3px;text-align:left;width:60%;vertical-align:top" colspan="{if !$tax_excluded_display}5{else}4{/if}">{$cart_rule.name}</td>
						<td>
							{if $tax_excluded_display}
								- {$cart_rule.value_tax_excl}
							{else}
								- {$cart_rule.value}
							{/if}
						</td>
					</tr>
				{/foreach}
				<!-- END CART RULES -->
			</table>

			<table style="width: 100%">
			
				<tr style="line-height:5px;">
					<td style="width: 85%;"></td>
					<td style="width: 15%;"></td>
				</tr>
				
				<tr style="line-height:5px;">
					<td style="width: 85%; text-align: right; font-weight: bold">{l s='Subtotal (Tax Excl.)' pdf='true'}</td>
					<td style="width: 15%; text-align: right;">{displayPrice currency=$order->id_currency price=$order_invoice->total_products}</td>
				</tr>

				{if $order_invoice->total_discount_tax_incl > 0}
				<tr style="line-height:5px;">
					<td style="text-align: right; font-weight: bold">{l s='Total Vouchers' pdf='true'}</td>
					<td style="width: 15%; text-align: right;">-{displayPrice currency=$order->id_currency price=($order_invoice->total_discount_tax_incl + $shipping_discount_tax_incl)}</td>
				</tr>
				{/if}
				
				{if ($order_invoice->total_paid_tax_incl - $order_invoice->total_paid_tax_excl) > 0}
				<tr style="line-height:5px;">
					<td style="text-align: right; font-weight: bold">{l s='Tax' pdf='true'}</td>
					<td style="width: 15%; text-align: right;">{displayPrice currency=$order->id_currency price=($order_invoice->total_paid_tax_incl - $order_invoice->total_paid_tax_excl - ($order_invoice->total_shipping_tax_incl - $order_invoice->total_shipping_tax_excl))}</td>
				</tr>
				{/if}

				<!--
				{if $order_invoice->total_wrapping_tax_incl > 0}
				<tr style="line-height:5px;">
					<td style="text-align: right; font-weight: bold">{l s='Wrapping Cost' pdf='true'}</td>
					<td style="width: 15%; text-align: right;">
					{if $tax_excluded_display}
						{displayPrice currency=$order->id_currency price=$order_invoice->total_wrapping_tax_excl}
					{else}
						{displayPrice currency=$order->id_currency price=$order_invoice->total_wrapping_tax_incl}
					{/if}
					</td>
				</tr>
				{/if}
				-->

				{if $order_invoice->total_shipping_tax_incl > 0}
				<tr style="line-height:5px;">
					<td style="text-align: right; font-weight: bold">{l s='Freight' pdf='true'}</td>
					<td style="width: 15%; text-align: right;">
						{if $tax_excluded_display}
							{displayPrice currency=$order->id_currency price=$order_invoice->total_shipping_tax_excl}
							{else}
							{displayPrice currency=$order->id_currency price=$order_invoice->total_shipping_tax_incl}
						{/if}
					</td>
				</tr>
				{/if}

				<tr style="line-height:5px;">
					<td style="width: 85%;"></td>
					<td style="width: 15%;"></td>
				</tr>

				<tr style="line-height:5px;">
					<td>
						<table style="text-align: left;">
							<tr>
							{foreach $free_text_body_array as $free_text_body}
								<td>{$free_text_body}</td>
							{/foreach}
							</tr>
						</table>
					</td>
					<td style="width: 15%; text-align: right; font-weight: bold; background-color:#ffdddd">{displayPrice currency=$order->id_currency price=$order_invoice->total_paid_tax_incl}<br /><span>PAY THIS<br/ >AMOUNT</span></td>
				</tr>
				
				<tr style="line-height:15px;">
					<td style="text-align: center;font-weight: bold;font-style:italic;">THANK YOU FOR YOUR BUSINESS!</td>
					<td style="width: 15%;"></td>
				</tr>

			</table>

		</td>
	</tr>
</table>
<!-- / PRODUCTS TAB -->

<div style="line-height: 1pt">&nbsp;</div>

<!-- I've removed the tax tab -->
{*$tax_tab*}

{if isset($order_invoice->note) && $order_invoice->note}
<div style="line-height: 1pt">&nbsp;</div>
<table style="width: 100%">
	<tr>
		<td style="width: 15%"></td>
		<td style="width: 85%">{$order_invoice->note|nl2br}</td>
	</tr>
</table>
{/if}

{if isset($HOOK_DISPLAY_PDF)}
<div style="line-height: 1pt">&nbsp;</div>
<table style="width: 100%">
	<tr>
		<td style="width: 15%"></td>
		<td style="width: 85%">{$HOOK_DISPLAY_PDF}</td>
	</tr>
</table>
{/if}

</div>
