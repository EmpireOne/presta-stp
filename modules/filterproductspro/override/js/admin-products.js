//i made for orverriding
//source: admin-products.js

var ids = new Array();

product_tabs['Features'] = new function(){
	//i add
	var self = this;

	this.initAccessoriesAutocomplete = function (){
		$('.feature_autocomplete_input').each(function(){
			//$(this).autocomplete('../index.php?fc=module&module=filterproductspro&controller=list', {//can cause 302 error
			$(this).autocomplete('../modules/filterproductspro/controllers/front/list.php?', {
				minChars: 1,
				autoFill: true,
				max:20,
				matchContains: true,
				mustMatch:true,
				scroll:false,
				cacheLength:0,
				formatItem: function(item) {
					return item[1]+' - '+item[0];
				}
			})
			.result(self.addFeature);
		});
	};

	this.addFeature = function(event, data, formatted){
		var id_feature = event.target.id.replace('feature_autocomplete_input_', '');//i add

		if (data == null)
			return false;
		var valueId = data[1];
		var valueName = data[0];

		var $divFeatures = $('#divFeatures_'+ id_feature);
		var $inputFeatures = $('#feature_'+ id_feature +'_value')//$('#inputFeatures_'+ id_feature);
		var $nameFeatures = $('#nameFeatures_'+ id_feature);

		/* delete product from select + add product line to the div, input_name, input_ids elements */
		$divFeatures.html($divFeatures.html() + valueName + ' <span class="delFeature" name="' + valueId + '" style="cursor: pointer;"><img src="../img/admin/delete.gif" /></span><br />');
		$nameFeatures.val($nameFeatures.val() + valueName + '¤');
		$inputFeatures.val($inputFeatures.val() + valueId + '-');
		$('#feature_autocomplete_input_'+ id_feature).val('');
	};

	this.delFeature = function(id, id_feature){
		var div = getE('divFeatures_'+ id_feature);
		var input = getE('feature_'+ id_feature +'_value');
		var name = getE('nameFeatures_'+ id_feature);

		// Cut hidden fields in array
		var inputCut = input.value.split('-');
		var nameCut = name.value.split('¤');

		if (inputCut.length != nameCut.length)
			return jAlert('Bad size');

		// Reset all hidden fields
		input.value = '';
		name.value = '';
		div.innerHTML = '';
		for (i in inputCut)
		{
			// If empty, error, next
			if (!inputCut[i] || !nameCut[i])
				continue ;

			// Add to hidden fields no selected products OR add to select field selected product
			if (inputCut[i] != id)
			{
				input.value += inputCut[i] + '-';
				name.value += nameCut[i] + '¤';
				div.innerHTML += nameCut[i] + ' <span class="delFeature" name="' + inputCut[i] + '" style="cursor: pointer;"><img src="../img/admin/delete.gif" /></span><br />';
			}
			else{
				//$('#selectAccessories').append('<option selected="selected" value="' + inputCut[i] + '-' + nameCut[i] + '">' + inputCut[i] + ' - ' + nameCut[i] + '</option>');
				//$('#nameFeatures_'+ id_feature).val($('#nameFeatures_'+ id_feature).val() + nameCut[i] +'¤');
			}
		}
	};

	/**
	 * Update the manufacturer select element with the list of existing manufacturers
	 */
	this.getManufacturers = function(){
		$.ajax({
				url: 'ajax-tab.php',
				cache: false,
				dataType: 'json',
				data: {
					ajaxProductManufacturers:"1",
					ajax : '1',
					token : token,
					controller : 'AdminProducts',
					action : 'productManufacturers'
				},
				success: function(j) {
					var options = $('select#id_manufacturer').html();
					if (j)
					for (var i = 0; i < j.length; i++)
						options += '<option value="' + j[i].optionValue + '">' + j[i].optionDisplay + '</option>';
					$("select#id_manufacturer").html(options);
				},
				error: function(XMLHttpRequest, textStatus, errorThrown)
				{
					$("select#id_manufacturer").replaceWith("<p id=\"id_manufacturer\">[TECHNICAL ERROR] ajaxProductManufacturers: "+textStatus+"</p>");
				}
		});
	};

	this.onReady = function(){
		displayFlags(languages, id_language, allowEmployeeFormLang);//i from orig

		self.initAccessoriesAutocomplete();

		$('.divFeatures').each(function(){
			$(this).delegate('.delFeature', 'click', function(){
				var id_feature = $(this).parent().attr('id').replace('divFeatures_', '');
				self.delFeature($(this).attr('name'), id_feature);
			})
		});

		if (display_multishop_checkboxes)
			ProductMultishop.checkAllAssociations();
	};
}
