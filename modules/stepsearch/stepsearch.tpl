<!-- Step Search by editorstefan -->

{if isset($stepsearch_steps) && $stepsearch_categories}
<div id="stepsearch_box" class="block exclusive">
	<h4>{l s='Search Wizard' mod='stepsearch'}</h4>
	<div class="block_content">
		{foreach from=$stepsearch_steps item=step}
			<div id="stepsearch_{$step.step}" class="stepsearch_step">
				<label>{$step.name}</label>
				{if $step.step == 1}
				<select name="step_{$step.step}" class="step_select">
					<option value="">--</option>
					{foreach from=$stepsearch_categories item=category}
						<option value="{$category.id_category}">{$category.name}</option>
					{/foreach}
				{else}
				<select name="step_{$step.step}" disabled="disabled" class="step_select">
					<option value="">--</option>
				{/if}
				</select>
			</div>
		{/foreach}
		<p><a class="toggletips" href="javascript:void(0)" title="{l s='Show Search Tips' mod='stepsearch'}">{l s='Show Search Tips' mod='stepsearch'}</a></p>
		<div class="hidden">
			<p>{l s='Redirecting...'}</p>
		</div>
	</div>


</div>
{/if}

<!--/ Step Search by editorstefan -->