{*//i made*}
{*adding tpl content to a proper place*}
<!-- Filter products pro adminProductsController -->
<script type="text/javascript">
$(function(){
	if (typeof tabs_manager != 'undefined'){
		//execute when tab has finished loading
		tabs_manager.onLoad('Features', function(){
			if (typeof($('#product-tab-content-Features')) != 'undefined'){
				//pirma užkrauti dom elementą
				/*console.debug('pirma užkrauti dom elementą');//i debug
				$('#product-tab-content-Features tbody td:nth-child(2)').each(function(index, element){
					if (typeof($(this).parent().find('select').attr('id')) != 'undefined')
						$("{$td_tpl}").appendTo($(this));//append only to these features, which have values pre-defined
				});*/
				//tik po to prie jo pririšti metodus
				//console.debug('tik po to prie jo pririšti metodus');//i debug
				//assignNewProductTabsFeatures();
				//console.debug(product_tabs['Features'].onReady);//i debug
				//product_tabs['Features'].onReady;
			}
		});
	}
});






</script>
<!-- /Filter products pro adminProductsController -->
