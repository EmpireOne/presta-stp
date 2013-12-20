<!-- Step Search -->
{if $mobile_device}
{if isset($stepsearch_steps) && $stepsearch_categories}
<hr>
<div id="stepsearch_box">
	<div class="ui-bar ui-bar-a center">
		<h4>{l s='Search by Vehicle' mod='stepsearch'}</h4>
	</div>
	<div class="block_content">
		<div data-role=fieldcontain>
			{foreach from=$stepsearch_steps item=step}
				<!-- <div id="stepsearch_{$step.step}" class="stepsearch_step"> -->
					<label for="step_{$step.step}">{$step.name}</label>
					{if $step.step == 1}
					<select name="step_{$step.step}" id="step_{$step.step}" class="step_select">
						<option value="">--</option>
						{foreach from=$stepsearch_categories item=category}
							<option value="{$category.id_category}">{$category.name}</option>
						{/foreach}
					{else}
					<select name="step_{$step.step}" id="step_{$step.step}" disabled="disabled" class="step_select">
						<option value="">--</option>
					{/if}
					</select>
				<!-- </div> -->
			{/foreach}
		</div>	
			<!-- Show search tips replaced with search button by request -->
			<!-- <p><a class="toggletips" href="javascript:void(0)" title="{l s='Show Search Tips' mod='stepsearch'}">{l s='Show Search Tips' mod='stepsearch'}</a></p> -->
			<!--<p><a class="step_search_btn" href="#" onclick="return false;" title="{l s='Search' mod='stepsearch'}">{l s='Search' mod='stepsearch'}</a></p>-->
		<p><a data-ajax="false" id="step_search_btn" href="#" onclick="return false;" title="{l s='Search' mod='stepsearch'}">{l s='Search' mod='stepsearch'}</a></p>
		<div class="hidden">
			<p>{l s='Redirecting...'}</p>
		</div>
	</div>


</div>
{/if}
{else}
{if isset($stepsearch_steps) && $stepsearch_categories}
<div id="stepsearch_box" class="block exclusive">
	<div class="ui-bar ui-bar-a center">
		<h4>{l s='Search by Vehicle' mod='stepsearch'}</h4>
	</div>
	<div class="block_content">
		{foreach from=$stepsearch_steps item=step}
			<div data-role="fieldcontain" id="stepsearch_{$step.step}" class="stepsearch_step">
				<label for="step_{$step.step}">{$step.name}</label>
				{if $step.step == 1}
				<select name="step_{$step.step}" id="step_{$step.step}" class="step_select">
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
		<!-- Show search tips replaced with search button by request -->
		<!-- <p><a class="toggletips" href="javascript:void(0)" title="{l s='Show Search Tips' mod='stepsearch'}">{l s='Show Search Tips' mod='stepsearch'}</a></p> -->
		<!--<p><a class="step_search_btn" href="#" onclick="return false;" title="{l s='Search' mod='stepsearch'}">{l s='Search' mod='stepsearch'}</a></p>-->
		<p><a data-ajax="false" class="button_small" id="step_search_btn" style="margin: 10px 0 3px 0; width:80px" href="#" onclick="return false;" title="{l s='Search' mod='stepsearch'}">{l s='Search' mod='stepsearch'}</a></p>
		<div class="hidden">
			<p>{l s='Redirecting...'}</p>
		</div>
	</div>


</div>
{/if}
{/if}
<!--/ Step Search -->