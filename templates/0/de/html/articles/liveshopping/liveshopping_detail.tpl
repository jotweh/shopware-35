{assign var=pseudoprice_num value=$sArticle.pseudoprice|replace:'.':''|replace:',':'.'|floatval}
{assign var=price_num value=$sLiveshoppingData.price|floatval}
{assign var=prozent value=$price_num*100/$pseudoprice_num}
{assign var=h value=100|intval}
{assign var=prozent value=$h-$prozent}
{assign var=prozent value=$prozent|number_format:2:',':'.'}

{if $sLiveshoppingData.valid_to_ts}
{assign var=uniquekey value=$smarty.now|uniqid}

	
	<div style="height:260px; position:relative;">
	
		<div style="position: absolute; left:0px; top: 4px; height: 45px; width: 200px;">
		<div style="font-size: 12px; color: rgb(153, 153, 153); width: 200px; height: 16px;">{$sConfig.sSnippets.sArticlesLiveshoppingOriginallyPrice} {$sConfig.sCURRENCYHTML} {$sArticlePseudoprice}*</div>
		<div style="font-size: 12px; color: rgb(255, 51, 51); font-weight: bold; width: 200px; height: 20px;">{$sConfig.sSnippets.sArticlesLiveshoppingYouSave} {$prozent} %</div>
		</div>
	
		<div class="liveprice_normal" style=" top:150px; left:-5px">	
			<div style="position: relative;">		
				<div class="price_start">{* sSnippet: liveshopping special offer till *}{$sConfig.sSnippets.sArticlesLiveshoppingSpecialOfferTill}</div>
				<div class="box_timer" style="left:12px;top:5px">
					<p style="font-size:18px;">
					<span class="{$uniquekey}{$sLiveshoppingData.ordernumber}_days_doubledigit">00</span>:<span class="{$uniquekey}{$sLiveshoppingData.ordernumber}_hours_doubledigit">00</span>:<span class="{$uniquekey}{$sLiveshoppingData.ordernumber}_min_doubledigit">00</span>:<span class="{$uniquekey}{$sLiveshoppingData.ordernumber}_sec_doubledigit">00</span>
					</p>
				</div>
				<div class="price_current" style="top:56px; left:25px;"> <p>{$sConfig.sSnippets.sArticlesLiveshoppingActualPrice}:</p> {$sConfig.sCURRENCYHTML} <span id="{$uniquekey}{$sLiveshoppingData.ordernumber}_display_price">{$sLiveshoppingData.price|number_format:2:',':'.'}</span>* </div>
				
				<div class="live_info"></div>			
		</div>
	</div>
	
		<div style="position:absolute; right:5px; top:-75px; height:200px;">
		{include file="articles/liveshopping/ticker/timeline.tpl" sLiveshoppingData=$sLiveshoppingData}
		</div>
		
	</div>	
	
	{* Ticker Logik *}
	{include file="articles/liveshopping/logics.tpl" sLiveshoppingData=$sLiveshoppingData}
{/if}