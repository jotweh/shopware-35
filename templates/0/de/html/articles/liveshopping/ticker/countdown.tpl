{* 
	BENÖTIGTE PARAMETER
	sLiveshoppingData > Liveshopping-Datensatz
*}
{assign var="slider0" value=84}
{assign var="slider100" value=200}

{assign var="sliderDiff" value=$slider100-$slider0}
{assign var="sliderOnePro" value=$sliderDiff/100}
{assign var="instockTotal" value=$sLiveshoppingData.max_quantity+$sLiveshoppingData.sells}
{assign var="instockPro" value=$sLiveshoppingData.max_quantity*100/$instockTotal}
{assign var="instockTopValue" value=$sliderOnePro*$instockPro}
{assign var="instockTopValue" value=$slider100-$instockTopValue}


<div class="box_countdown">
	<div class="box_countdown_bg"></div>	
	<div class="box_countdown_aktionsende">{* sSnippet: liveshopping save money *}{$sConfig.sSnippets.sArticlesBundleSaveMoney}</div>	
	<div class="box_countdown_startpreis">{* sSnippet: liveshopping start price *}{$sConfig.sSnippets.sArticlesLiveshoppingStartPrice}</div>	
	<div class="box_countdown_startpreis_zahl">{$sConfig.sCURRENCYHTML} {$sLiveshoppingData.startprice|number_format:2:',':'.'}</div>	
	<div class="box_countdown_slider" style="top:{$instockTopValue}px;">
		<div class="box_countdown_aktpreis">{* sSnippet: liveshopping actual price *}{$sConfig.sSnippets.sArticlesLiveshoppingActualPrice}</div>
		<div class="box_countdown_preis">{$sConfig.sCURRENCYHTML} <span id="{$uniquekey}{$sLiveshoppingData.ordernumber}_display_price">{$sLiveshoppingData.price|number_format:2:',':'.'}</span></div>
	</div>	
	<div class="box_countdown_time">
		<span class="{$uniquekey}{$sLiveshoppingData.ordernumber}_days_doubledigit">00</span>:<span class="{$uniquekey}{$sLiveshoppingData.ordernumber}_hours_doubledigit">00</span>:<span class="{$uniquekey}{$sLiveshoppingData.ordernumber}_min_doubledigit">00</span>:<span class="{$uniquekey}{$sLiveshoppingData.ordernumber}_sec_doubledigit">00</span>
	</div>	
</div>



