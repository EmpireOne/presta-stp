{foreach from=","|explode:"CB,VISA,MASTERCARD" item=card}
    <p class="payment_module">
        <a title="{l s='Payer par carte bancaire' mod='atos'}"
           href="javascript:$('#atos_form input[name={$card}]').click();">
            <img src="{$module_dir}logos/{$card}.gif">
            {l s='Payer par carte bancaire' mod='atos'}
        </a>
    </p>
{/foreach}


<div class="payment_module atos">
    {if $atos}
        <p class="bold teaser">{l s='Payer par carte bancaire' mod='atos'}</p>
        {$atos|regex_replace:'/\<form /i':'<form id="atos_form" '}
    {else}
        {l s='Your order total must be greater than' mod='atos'} {displayPrice price=1} {l s='in order to pay by credit card.' mod='atos'}
    {/if}
</div>
