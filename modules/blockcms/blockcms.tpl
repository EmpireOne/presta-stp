<!-- MODULE Block footer -->
<ul id="footer_links">
	<li><a href="{$link->getPageLink('index.php')}"{if $page_name == 'index'} class="active"{/if}>{l s='home' mod='blockcms'}</a></li>
	<li><a href="{$link->getPageLink('prices-drop.php')}"{if $page_name == 'prices-drop'} class="active"{/if}>{l s='specials' mod='blockcms'}</a></li>
	<li><a href="{$link->getPageLink('sitemap.php')}"{if $page_name == 'sitemap'} class="active"{/if}>{l s='sitemap' mod='blockcms'}</a></li>
	<li><a href="{$link->getPageLink('contact-form.php')}"{if $page_name == 'contact-form'} class="active"{/if}>{l s='contact' mod='blockcms'}</a></li>
	<li class="last_item">&copy; 2011 {l s='Powered by' mod='blockcms'} <a href="http://www.prestashop.com">PrestaShop</a>&trade;. All rights reserved</li>
</ul>
<!-- /MODULE Block footer -->