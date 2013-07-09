{*
* 2007-2012 PrestaShop
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
*  @copyright  2007-2012 PrestaShop SA
*  @version  Release: $Revision: 14011 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

{if !isset($gatewaylist)}
<p class="payment_module">
	
		{if isset($use_mobile) && $use_mobile}
			<img src="http://www.multisafepay.com/downloads/betaalbutton/msp/multisafepay-betaalveiligonline-180px.gif" />
		{else}
		
				<img src="http://www.multisafepay.com/downloads/betaalbutton/msp/multisafepay-betaalveiligonline-180px.gif" />
				{if isset($html)}
					{$html}
				{/if}
		{/if}
	
</p>
{/if}

<form id="multisafepay_payment_form" action="{$base_dir_ssl}modules/multisafepay/express_checkout/submit.php" data-ajax="false" title="{l s='Pay with Multisafepay' mod='multisafepay'}" method="post">
	<input type="hidden" name="express_checkout" value="{$Multisafepay_payment_type}"/>
	<input type="hidden" name="current_shop_url" value="{$Multisafepay_current_shop_url}" />
	{if isset($gatewaylist)}
		{$gatewaylist}
		<input type="hidden" name="method" value="gateways"/>
	{/if}
	
	{if isset($html)}
		<input type="hidden" name="method" value="connect"/>
	{/if}
	{if isset($html2)}
					{$html2}
					<input type="hidden" name="gateway" value="fastcheckout"/>
				{/if}
</form>
