{if $page_name == 'index'}
{literal}
<!-- tmbannerblock -->
<div id="tmbannerblock"> 	
        {/literal}
            {foreach from=$xml->link item=home_link name=links}
                {literal}
                    <a href='{/literal}{$home_link->url}{literal}'><img src='{/literal}{$this_path}{$home_link->img}{literal}'alt="" title="{/literal}{$home_link->desc}{literal}" /></a>
                {/literal}
            {/foreach}
        {literal}
</div>
<div class="clearblock"></div>
<!-- /tmbannerblock -->
{/literal}
{/if}