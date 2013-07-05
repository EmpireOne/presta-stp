$(document).ready(function() {

var $stepsearch_step_count = $('.stepsearch_step').length;

 /* Function that handles steps run */

function runSteps($step) {
	$('#stepsearch_'+$step).unbind('change').change(function(){

		if($(this).find('.step_select').val())
		{
			$data = 'id='+ $(this).find('.step_select').val()
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
					else fillnext(parseInt($step)+1, data);


				},
				error: function(xhr, textStatus, errorThrown) {
					alert('Error code ' + textStatus + ': ' + errorThrown)
				}
			});			
		}

	
	});
	
}

runSteps(1);

function fillnext($step, data)
{
	$('#stepsearch_'+$step).find('.step_select').html(data).attr('disabled', false);
	$next_to_clear = parseInt($step+1);
	$('#stepsearch_'+$next_to_clear).find('.step_select').html('<option value="">--</option>');
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

});	