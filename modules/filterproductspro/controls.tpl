{if $filter.type eq $GLOBAL->Types->Select}
    <select id="filter_backup_select_{$filter.id_filter}_{$filter.id_parent}" class="filter_backup hidden" disabled="true" style="display: none">
        <option value="">{l s='Choose option' mod='filterproductspro'}</option>
        {foreach from=$options item=option}            
            {assign var=id_control value="option_"|cat:$option.id_option}
            {assign var=name_control value=""|cat:$filter.id_filter|cat:"_"|cat:$filter.multi_options}            
            <option id="{$id_control}" value="{$id_control}" name="{$name_control}">{$option.value}</option>
        {/foreach}
    </select>
    <select id="filter_select_{$filter.id_filter}_{$filter.id_parent}" name="{$GLOBAL->Types->Select}" {if $filter.id_parent neq 0}class="filter_parent" disabled="true"{/if}>
        <option value="">{l s='Choose option' mod='filterproductspro'}</option>
        {foreach from=$options item=option}            
            {assign var=id_control value="option_"|cat:$option.id_option}
            {assign var=name_control value=""|cat:$filter.id_filter|cat:"_"|cat:$filter.multi_options}            
            <option id="{$id_control}" value="{$id_control}" name="{$name_control}">{$option.value}</option>
        {/foreach}
    </select>
{elseif $filter.type eq $GLOBAL->Types->Checkbox}
    {if $filter.multi_options == 0}
        {assign var=type_filter value=$GLOBAL->Types->Radio}
    {else}
        {assign var=type_filter value=$GLOBAL->Types->Checkbox}        
    {/if}
    {foreach from=$options item=option name=f_options}
        {*assign var=id_control value="{$filter.id_filter}_{$option.id_option}_{$filter.type}_{$option.value}"*}
        {assign var=id_control value="option_"|cat:$option.id_option}
        {assign var=name_control value=""|cat:$filter.id_filter|cat:"_"|cat:$filter.multi_options}
        <input type="{$type_filter}" value="{$option.value}" id="{$id_control}" name="{$name_control}" class="auto_width {$type_filter}" />
        {if empty($option.color)}
            <label for="{$id_control}" class="{$type_filter}" title="{$option.value}">{$option.value}</label>
        {else}
            <label for="{$id_control}" class="{$type_filter} color" title="{$option.value}" style="background: {$option.color}">&nbsp;</label>
        {/if}
        <div class="clear"></div>
    {/foreach}
{elseif $filter.type eq $GLOBAL->Types->Button}
    {foreach from=$options item=option}
        {*assign var=id_control value="{$filter.type}_{$option.id_option}_{$option.value}"*}
        {assign var=id_control value="option_"|cat:$option.id_option}
        {assign var=name_control value=""|cat:$filter.id_filter|cat:"_"|cat:$filter.multi_options}
        
        <input type="{$filter.type}" value="{$option.value}" id="{$id_control}" name="{$name_control}" class="fpp_button off" title="{$option.value}" />
    {/foreach}
{/if}