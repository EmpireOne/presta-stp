<script language="JavaScript" type="text/javascript" >
//<!--
  var submitcount = 0;
  function avoidDuplicationSubmit(){
    if (submitcount == 0) {
      // sumbit form
      submitcount++;
      return true;
    } else {
      alert("Transaction is in progress.");
      return false;
    }
  }
//-->
</script>

<div id="ewaystarter">
<p class="payment_module">
	<a href="javascript: $('#ewaystarter').hide('slow'); $('#ewaypaymentform').show('slow'); void(0);" title="{l s='Pay with card using eWay' mod='ewaypay'}">
		<img src="{$module_dir}eway.gif" alt="{l s='Pay with a card using eWay' mod='ewaypay'}" height="49"/>
		{l s='Pay with a card using eWay' mod='ewaypay'}
	</a>
</p>
</div>


<div id="ewaypaymentform" style="display: none">
  <form method='post' name='ewaypay' action='{$gateway_url}' class="std" onsubmit="return avoidDuplicationSubmit()">
	  <fieldset class="account_creation">
		<input type='hidden' name='EWAY_ACCESSCODE' value='{$AccessCode}' />

		<p class="required text">
			<label for="EWAY_CARDNAME">Credit Card Holder</label>
			<input type="text" class="required text" name="EWAY_CARDNAME" id='EWAY_CARDNAME' />
		</p>

		<p class="required text">
			<label for="EWAY_CARDNUMBER">Credit Card Number</label>
			<input type="text" class="required text" name="EWAY_CARDNUMBER" id='EWAY_CARDNUMBER' />
		</p>

		<p class="required select">
			<label for="EWAY_CARDEXPIRYMONTH">Credit Card Expiry</label>
			<select id="EWAY_CARDEXPIRYMONTH" name="EWAY_CARDEXPIRYMONTH">
				{foreach from=$months key=k item=month}
					<option value="{$k|string_format:"%02d"}">{$month}</option>
				{/foreach}
			</select>
			 / <input type="text" class="required text" id="EWAY_CARDEXPIRYYEAR" name="EWAY_CARDEXPIRYYEAR" size='2' maxlength='2' />
		</p>

		<p class="required text">
			<label for="EWAY_CARDCVN">Credit Card CVN</label>
			<input type="text" class="required text" name="EWAY_CARDCVN" id="EWAY_CARDCVN" />
		</p>

		<p id="eway_input_button"><input type='image' src="{$module_dir}eway.gif" alt="{l s='Pay with Eway' mod='ewayrapidapi'}" /></p>
	  </fieldset>
  </form>
 </div>
 

