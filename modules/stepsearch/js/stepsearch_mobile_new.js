$(document).bind('pageshow', function() {

var $stepsearch_step_count = $('.step_select').length / 2;

$('#stepsearch_box div.hidden').hide();

$('#step_search_btn').button();
 /* Function that handles steps run */

 
function runSteps($step) {
	$('#step_'+$step).unbind('change').change(function(){
		$('#step_'+$step).selectmenu('refresh',true);	
		if($('#step_'+$step).val())
		{
			$('#step_search_btn').off('click');
			$('.stepsearch_tip').hide();
			$data = 'id='+ $('#step_'+$step).val()
			if ($step == $stepsearch_step_count)
			{
				$data += '&finalpage=1';
			};

			$.ajax({
				url: baseDir+'/modules/stepsearch/stepsearch-ajax.php',
				type: 'GET',
				dataType : 'json',
				data: $data,
				success: function(data, textStatus, xhr) {
					if ($step == $stepsearch_step_count) {
						$('#stepsearch_box div.hidden').fadeIn();
						window.location.href = data;
					}
					else {
					fillnext(parseInt($step)+1, data);
					}


				},
				error: function(xhr, textStatus, errorThrown) {
					alert('Error code ' + textStatus + ': ' + errorThrown)
				}
			});			
		} else {emptynext($step);} 

	
	});
	
}

runSteps(1);

function fillnext($step, data)
{
	if (data.subfilters == null) {
		$('#step_'+$step).html(data.subfilters).attr('disabled', true);
		$('#step_'+$step).selectmenu('disable');
		$('#step_'+$step).selectmenu('refresh',true);	
	} else {
	$('#step_'+$step).html(data.subfilters).attr('disabled', false);
	$('#step_'+$step).selectmenu('enable');
	$('#step_'+$step).selectmenu('refresh',true);	
	}
	$next_to_clear = parseInt($step+1);
	$('#step_'+$next_to_clear).html('<option value="">--</option>');
	$('#step_'+$next_to_clear).selectmenu('refresh',true);	
	$('#step_search_btn').replaceWith('<a data-ajax="false" id="step_search_btn" style="" href="'+data.newpage+'" title="Search">Search</a>');
	$('#step_search_btn').button();
	runSteps($step);
}

function emptynext($step)
{
	for (var i=(parseInt($step)+1);i<=$stepsearch_step_count;i++)
		{ 
		$('#step_'+ i).html('<option value="">--</option>').attr('disabled', true);
		$('#step_'+ i).selectmenu('disable');
		$('#step_'+ i).selectmenu('refresh',true);
		}
	if ( (parseInt($step) == 1)) {	
		$('#step_search_btn').replaceWith('<a data-ajax="false" id="step_search_btn" href="#" onclick="return false;" title="Search">Search</a>');
		$('#step_search_btn').button();
		$('#step_search_btn').click(function() {
					$('.stepsearch_tip').show();
				});
			} else {$('#step_'+(parseInt($step)-1)).change();}
	runSteps($step);
}

/* Deal with tips */

var labels = $('.stepsearch_step label');
labels.each(function() {
	if ($(this).text().indexOf('(') != -1) {

		var $tip = $(this).text().substring($(this).text().indexOf('('), $(this).text().length);
		var $original = $(this).text().substring(0, $(this).text().indexOf('('));

		$('<p class="stepsearch_tip">'+$tip+'</p>').hide().appendTo($(this).parent());
		$(this).text($original);
	};
});

$('.toggletips').live('click', function() {
	$('.stepsearch_tip').toggle('normal');
});

$('#step_search_btn').click(function() {
	$('.stepsearch_tip').show();
});



});	

