{if $liveArt.sDetails}
	{if 2==$liveArt.typeID || 3==$liveArt.typeID}
		<div class="fixfloat"></div>
		{include file="articles/liveshopping/liveshopping_cat_countdown.tpl" sLiveshoppingData=$liveArt}
		<div class="fixfloat"></div>
	{else}
		<div class="fixfloat"></div>
		{include file="articles/liveshopping/liveshopping_cat.tpl" sLiveshoppingData=$liveArt}
		<div class="fixfloat"></div>
	{/if}
{/if}