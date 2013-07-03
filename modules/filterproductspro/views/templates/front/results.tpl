{*
* @author PresTeamShop.com
 * @copyright PresTeamShop.com - 2012
*}

{capture name=path}{l s='Results' mod='filterproductspro'}{/capture}

<h2>    
    {l s='Results' mod='filterproductspro'}
    <span class="category-product-count">
    {if $nb_products == 0}
        {l s='There are no products.' mod='filterproductspro'}
    {else}
        {if isset($no_options_selected) && $no_options_selected}
            {l s='No filters selected' mod='filterproductspro'}&nbsp;-&nbsp;
        {/if}
        {if $nb_products == 1}{l s='There is' mod='filterproductspro'}{else}{l s='There are' mod='filterproductspro'}{/if}
        {$nb_products}
        {if $nb_products == 1}{l s='product.' mod='filterproductspro'}{else}{l s='products.' mod='filterproductspro'}{/if}
    {/if}
    </span>
</h2>

{if $products}
    <div id="result_filterproductspro">
        {include file="$tpl_dir./product-sort.tpl"}
        {include file="$tpl_dir./product-list.tpl" products=$products}
        {include file="$tpl_dir./pagination.tpl"}
    </div>
{else}
    <p class="warning">{l s='Without results' mod='filterproductspro'}</p>
    <p class="warning">
        {l s='Not find the product you want? Tell us what product you need and we will help you.' mod='filterproductspro'}
        <br />
        <a href="{$base_dir_ssl}contact-form.php">{l s='Go to contact form, click here!' mod='filterproductspro'}</a>
    </p>
    <br />
    <ul class="back_tools">    	
    	<li>
            <a href="{$base_dir}"><img src="{$img_dir}icon/home.gif" alt="" class="icon" /></a>
            <a href="{$base_dir}">{l s='Home'}</a>
        </li>        
        <!--<li>
            <a href="{$cookie->fpp_url}"><img src="{$img_dir}icon/cancel_16x18" alt="" class="icon" /></a>
            <a href="{$cookie->fpp_url}">{l s='Back'}</a>
        </li>-->
    </ul>
{/if}