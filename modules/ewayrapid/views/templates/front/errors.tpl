{if isset($eway_errors) && $eway_errors}
	<div class="error">
		<p>{if $eway_errors|@count > 1}{l s='There are'}{else}{l s='There is'}{/if} {$eway_errors|@count} {if $eway_errors|@count > 1}{l s='errors'}{else}{l s='error'}{/if} :</p>
		<ol>
		{foreach from=$eway_errors key=k item=error}
			<li>{$error}</li>
		{/foreach}
		</ol>
		{if isset($smarty.server.HTTP_REFERER) && !strstr($request_uri, 'authentication')}
			<p><a href="{$smarty.server.HTTP_REFERER|escape:'htmlall':'UTF-8'|secureReferrer}" class="button_small" title="{l s='Back'}">&laquo; {l s='Back'}</a></p>
		{/if}
	</div>
{/if}