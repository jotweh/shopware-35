{if $sLiveshoppingData.valid_to_ts}
{assign var=uniquekey value=$smarty.now|uniqid}

{assign var="slider0" value=82}
{assign var="slider100" value=198}

{assign var="sliderDiff" value=$slider100-$slider0}
{assign var="sliderOnePro" value=$sliderDiff/100}
{assign var="instockTotal" value=$sLiveshoppingData.max_quantity+$sLiveshoppingData.sells}
{assign var="instockPro" value=$sLiveshoppingData.max_quantity*100/$instockTotal}
{assign var="instockTopValue" value=$sliderOnePro*$instockPro}
{assign var="instockTopValue" value=$slider100-$instockTopValue}
	
	<div class="{if $sLiveshoppingData.max_quantity_enable == 1}liveshopping_box_countdown{else}liveshopping_box_timeline{/if}">
	
	
		<div {if 2==$sLiveshoppingData.typeID}class="{if $sLiveshoppingData.max_quantity_enable == 1}liveprice_stock_down{else}liveprice_down{/if}"{elseif 3==$sLiveshoppingData.typeID}class="{if $sLiveshoppingData.max_quantity_enable == 1}liveprice_stock_up{else}liveprice_up{/if}"{/if} style=" top:{if $sLiveshoppingData.max_quantity_enable == 1}{$instockTopValue}{else}120{/if}px;">
	
		<div style="position: relative;">
		
			<div class="price_start"> {* sSnippet: liveshopping start price *}{$sConfig.sSnippets.sArticlesLiveshoppingStartPrice}: {$sConfig.sCURRENCYHTML} {$sLiveshoppingData.startprice|number_format:2:',':'.'}*</div>
			<div class="price_current"> <p>{* sSnippet: liveshopping start price *}{$sConfig.sSnippets.sArticlesLiveshoppingActualPrice}:</p> {$sConfig.sCURRENCYHTML} <span class="{$uniquekey}{$sLiveshoppingData.ordernumber}_display_price">{$sLiveshoppingData.price|number_format:2:',':'.'}</span>* </div>
			
			<div class="bar_time" style="width:151px;overflow:hidden;background-color:none;padding:0;">
				<div class="{$uniquekey}{$sLiveshoppingData.ordernumber}_secbar_prozess" style="width:0%; height:3px; line-height:1px; margin:0; padding:0; background-color:#FFFFFF;"></div>
			</div>
			
			{if $sLiveshoppingData.max_quantity_enable == 1}
			<div class="live_stock"> {* sSnippet: liveshopping just *}{$sConfig.sSnippets.sArticlesLiveshoppingJust} <p>{$sLiveshoppingData.max_quantity}</p> {* sSnippet: liveshopping pieces *}{$sConfig.sSnippets.sArticlesLiveshoppingPiece}</div>
			{/if}
			
			<div class="live_info">
			
				{if 2==$sLiveshoppingData.typeID}
					{* sSnippet: liveshopping price falling *}{$sConfig.sSnippets.sArticlesLiveshoppingPriceFalling} {$sConfig.sCURRENCYHTML} {$sLiveshoppingData.minPrice|number_format:2:',':'.'}* / {* sSnippet: liveshopping minutes *}{$sConfig.sSnippets.sArticlesLiveshoppingMinutes}
				{elseif 3==$sLiveshoppingData.typeID}
					{* sSnippet: liveshopping price rising *}{$sConfig.sSnippets.sArticlesLiveshoppingPriceRising} {$sConfig.sCURRENCYHTML} {$sLiveshoppingData.minPrice|number_format:2:',':'.'}* / {* sSnippet: liveshopping minutes *}{$sConfig.sSnippets.sArticlesLiveshoppingMinutes}
				{/if}
			
			</div>
			
		</div>
	</div>
	
	
		<a href="{$sLiveshoppingData.sDetails.linkDetails}" title="{$sLiveshoppingData.sDetails.articleName}">
		<div class="box_image_countdown" style="{if $sLiveshoppingData.sDetails.image.src.4 != ''}background:url({$sLiveshoppingData.sDetails.image.src.4}) no-repeat center 0;{/if}"></div>
		</a>	
		
		<a href="{$sLiveshoppingData.sDetails.linkDetails}" title="{$sLiveshoppingData.sDetails.articleName}" style="text-decoration:none;cursor:pointer;">
		<div class="box_name_countdown">{$sLiveshoppingData.sDetails.articleName}</div>
		<div class="box_description_countdown">{$sLiveshoppingData.description_long|strip_tags|truncate:120}</div>
		</a>
		
		<div class="box_timer">
		{* sSnippet: liveshopping offer ends in *}{$sConfig.sSnippets.sArticleLiveshoppingOfferEndsIn}:
		<p>
		<span class="{$uniquekey}{$sLiveshoppingData.ordernumber}_days_doubledigit">00</span>:<span class="{$uniquekey}{$sLiveshoppingData.ordernumber}_hours_doubledigit">00</span>:<span class="{$uniquekey}{$sLiveshoppingData.ordernumber}_min_doubledigit">00</span>:<span class="{$uniquekey}{$sLiveshoppingData.ordernumber}_sec_doubledigit">00</span>
		</p>
		</div>
		
		{if $sLiveshoppingData.sDetails.sVariantArticle != 1 && $sLiveshoppingData.sDetails.sConfigurator != 1}
		<div style="position:absolute; right:-16px; bottom:-5px; height: 45px; width: 200px;">
		<form name="sAddToBasket" method="GET" action="{$sStart}">
		<input type="submit" style="visibility: visible; opacity: 1;" value="in den Warenkorb" name="in den Warenkorb" title="{$sLiveshoppingData.sDetails.articleName} in den Warenkorb legen" id="basketButton">
		<input type="hidden" name="sViewport" value="basket" />
		<input type="hidden" name="sAdd" value="{$sLiveshoppingData.sDetails.ordernumber}">
		</form>
		</div>
		{/if}

		
	</div>	
	
	{* Ticker Logik *}
	{include file="articles/liveshopping/logics.tpl" sLiveshoppingData=$sLiveshoppingData}
{/if}