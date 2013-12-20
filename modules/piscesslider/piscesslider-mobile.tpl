{if $page_name == 'index'}
{literal}
<!-- piscesslider -->
<script type="text/javascript" src="{/literal}{$this_path}{literal}js/jquery.nivo.3.2.slider.pack.js"></script>
<div id="slide_holder" data-role="none"> 	
    <div id="slider" class="nivoSlider">
        {/literal}
            {foreach from=$xml->link item=home_link name=links}
                {literal}
                    <a href='{/literal}{$home_link->url}{literal}'><img src='{/literal}{$this_path}{$home_link->img}{literal}'alt="" title="{/literal}{$home_link->desc}{literal}" /></a>
                {/literal}
            {/foreach}
        {literal}
	</div>
</div>    
<script type="text/javascript">
$(window).load(function() {
    $('#slider').nivoSlider({
	effect:'slideInRight',
	directionNav: false, 
    controlNav: false
	});
});
</script>
<!-- /piscesslider -->
{/literal}
{/if}