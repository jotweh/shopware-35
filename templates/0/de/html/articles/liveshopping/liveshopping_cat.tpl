{if $sLiveshoppingData.valid_to_ts}
{assign var=uniquekey value=$smarty.now|uniqid}
	<div class="liveshopping_box_timeline">
		<div class="liveprice_normal" style=" top:50px; left:370px">	
			<div style="position: relative;">		
				<div class="price_start">{* sSnippet: liveshopping offer ends *}{$sConfig.sSnippets.sArticleLiveshoppingOfferEndsIn}:</div>
				<div class="box_timer" style="left:12px;top:12px">
					<p style="font-size:18px;">
					<span class="{$uniquekey}{$sLiveshoppingData.ordernumber}_days_doubledigit">00</span>:<span class="{$uniquekey}{$sLiveshoppingData.ordernumber}_hours_doubledigit">00</span>:<span class="{$uniquekey}{$sLiveshoppingData.ordernumber}_min_doubledigit">00</span>:<span class="{$uniquekey}{$sLiveshoppingData.ordernumber}_sec_doubledigit">00</span>
					</p>
				</div>
				<div class="price_current" style="top:50px; left:25px;"> <p>{* sSnippet: liveshopping offer ends *}{$sConfig.sSnippets.sArticlesLiveshoppingActualPrice}:</p> {$sConfig.sCURRENCYHTML} <span id="{$uniquekey}{$sLiveshoppingData.ordernumber}_display_price">{$sLiveshoppingData.price|number_format:2:',':'.'}</span>* </div>
		</div>
	</div>
	
		<div style="position:absolute; right:28px; top:50px;">
		{include file="articles/liveshopping/ticker/timeline.tpl" sLiveshoppingData=$sLiveshoppingData}
		</div>
	
	
		<a href="{$sLiveshoppingData.sDetails.linkDetails}" title="{$sLiveshoppingData.sDetails.articleName}">
		<div class="box_image_countdown" style="{if $sLiveshoppingData.sDetails.image.src.4 != ''}background:url({$sLiveshoppingData.sDetails.image.src.4}) no-repeat center 0;{/if}"></div>
		</a>	
		
		<a href="{$sLiveshoppingData.sDetails.linkDetails}" title="{$sLiveshoppingData.sDetails.articleName}" style="text-decoration:none;cursor:pointer;">
		<div class="box_name_countdown">{$sLiveshoppingData.sDetails.articleName}</div>
		<div class="box_description_countdown">{$sLiveshoppingData.description_long|strip_tags|truncate:120}</div>
		</a>
		
		<div style="position:absolute; right:80px; top:8px; height: 45px; width: 200px;">
		<div style="font-size: 12px; color: #999999;width:200px;height:20px;text-align:center;">{$sConfig.sSnippets.sArticlesLiveshoppingOriginallyPrice} {$sConfig.sCURRENCYHTML} {$sLiveshoppingData.sDetails.pseudoprice}*</div>
		<div style="font-size: 12px; color: #FF3333; font-weight: bold;width:200px;height:20px;text-align:center;">{$sConfig.sSnippets.sArticlesLiveshoppingYouSave} {$sLiveshoppingData.sDetails.pseudopricePercent.float} %</div>
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