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
<table style="width: 100%">
<tr>
	<td style="width: 20%;">
		{if $logo_path}
			<img src="{$logo_path}" style="width:{$width_logo}px; height:{$height_logo}px;" />
		{/if}
	</td>

	<td style="width: 80%;">
		<table style="width: 100%;height:60pt;">
			<tr style="font-size:5pt;">
				<td colspan="2"> </td>
			</tr>
			<tr style="height:40pt;background-color: #f73032;">
				<td style="width: 65%;"><span style="text-align:center; font-weight: bold; font-size: 22pt; font-family:sans-serif;color: #FFF;">{$shop_name|escape:'htmlall':'UTF-8'}</span></td>
				<td style="width: 35%;background-color: #232323;"><span style="font-family:serif; font-size: 24pt; letter-spacing:-1pt; color: #FFF; text-align:center;">TAX INVOICE</span></td>
			</tr>
			<tr style="font-size:2pt;">
				<td colspan="2"> </td>
			</tr>
			<tr style="font-size:2pt;background-color: #232323;">
				<td colspan="2"> </td>
			</tr>
	
		</table>
		<table>
			<tr>
				<td><span style="text-align:center; font-weight: bold; letter-spacing:2pt; font-size: 20pt; font-family:sans-serif;color: #232323;">{if !empty($shop_phone)}{$shop_phone|escape:'htmlall':'UTF-8'}{/if}</span></td>
			</tr>
		</table>
	</td>
</tr>
<!--
<tr>
	<td style="width: 50%; text-align: right;">
		<table style="width: 100%">
			<tr>
				<td style="font-weight: bold; font-size: 14pt; color: #444; width: 100%">{$shop_name|escape:'htmlall':'UTF-8'}</td>
			</tr>
			<tr>
				<td style="font-size: 14pt; color: #9E9F9E">{$date|escape:'htmlall':'UTF-8'}</td>
			</tr>
			<tr>
				<td style="font-size: 14pt; color: #9E9F9E">{$title|escape:'htmlall':'UTF-8'}</td>
			</tr>
		</table>
	</td>
</tr>
-->
</table>

