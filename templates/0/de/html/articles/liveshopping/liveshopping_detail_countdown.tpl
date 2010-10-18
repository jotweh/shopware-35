{if $sLiveshoppingData.valid_to_ts}
{assign var=uniquekey value=$smarty.now|uniqid}

<div class="liveshopping_box_countdown_detail">
	<div {if 2==$sLiveshoppingData.typeID}class="{if $sLiveshoppingData.max_quantity_enable == 1}liveprice_stock_down_detail{else}liveprice_down_detail{/if}"{elseif 3==$sLiveshoppingData.typeID}class="{if $sLiveshoppingData.max_quantity_enable == 1}liveprice_stock_up_detail{else}liveprice_up_detail{/if}"{/if} style=" top:{$instockTopValue}px;">
		<div style="position: relative;">
			<div class="price_start" style="margin-top:2px;"> {* sSnippet: liveshopping start price *}{$sConfig.sSnippets.sArticlesLiveshoppingStartPrice}: {$sConfig.sCURRENCYHTML} {$sLiveshoppingData.startprice|number_format:2:',':'.'}</div>
			<div class="price_current"> <p>{* sSnippet: actual price *}{$sConfig.sSnippets.sArticlesLiveshoppingActualPrice}:</p> {$sConfig.sCURRENCYHTML} <span class="{$uniquekey}{$sLiveshoppingData.ordernumber}_display_price">{$sLiveshoppingData.price|number_format:2:',':'.'}</span>*</div>
			
			<div class="bar_time" style="width:151px; background-color:none; top:67px;font-size:0; line-height:0;">
				<div class="{$uniquekey}{$sLiveshoppingData.ordernumber}_secbar_prozess" style="width:0%; height:3px; background-color:#FFFFFF;"></div>
			</div>
			
			{if $sLiveshoppingData.max_quantity_enable == 1}
			<div class="live_stock" style="margin-top:5px;"> {* sSnippet: Liveshopping Just *}{$sConfig.sSnippets.sArticlesLiveshoppingJust} <p>{$sLiveshoppingData.max_quantity}</p> {* sSnippet: Liveshopping piece *}{$sConfig.sSnippets.sArticlesLiveshoppingPiece}</div>
			{/if}
			
			<div class="live_info" style="margin-top:2px;">			
				{if 2==$sLiveshoppingData.typeID}
					{* sSnippet: Liveshopping Just *}{$sConfig.sSnippets.sArticlesLiveshoppingPriceFalling} {$sLiveshoppingData.minPrice|number_format:2:',':'.'}* / {* sSnippet: Liveshopping Just *}{$sConfig.sSnippets.sArticlesLiveshoppingMinutes}
				{elseif 3==$sLiveshoppingData.typeID}
					{* sSnippet: Liveshopping Just *}{$sConfig.sSnippets.sArticlesLiveshoppingPriceRising} {$sLiveshoppingData.minPrice|number_format:2:',':'.'}* / {* sSnippet: Liveshopping Just *}{$sConfig.sSnippets.sArticlesLiveshoppingMinutes}
				{/if}			
			</div>			
		</div>
	</div>
	<div class="fixfloat"></div>
		
	<div class="box_timer_detail">
		{* sSnippet: Liveshopping Just *}{$sConfig.sSnippets.sArticleLiveshoppingOfferEndsIn}:
		<p>
		<span class="{$uniquekey}{$sLiveshoppingData.ordernumber}_days_doubledigit">00</span>:<span class="{$uniquekey}{$sLiveshoppingData.ordernumber}_hours_doubledigit">00</span>:<span class="{$uniquekey}{$sLiveshoppingData.ordernumber}_min_doubledigit">00</span>:<span class="{$uniquekey}{$sLiveshoppingData.ordernumber}_sec_doubledigit">00</span>
		</p>
	</div>
	
</div>

{* Ticker Logik *}
{include file="articles/liveshopping/logics.tpl" sLiveshoppingData=$sLiveshoppingData}
{/if}