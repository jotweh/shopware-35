{* 
	BENÖTIGTE PARAMETER
	sLiveshoppingData > Liveshopping-Datensatz
*}

<div class="box_zeitanzeige">		
	<div style="font-weight:bold;font-size:15px;">{$sLiveshoppingData.valid_to_ts|date_format:"%d.%m.%Y um %H:%M"}</div>
	

	{* Col 1 *}
	<div style="float:left;margin-right:10px;width:80px;">		
		<div class="time_prozessbar_container">
			<div id="time_prozessbar" class="{$uniquekey}{$sLiveshoppingData.ordernumber}_days_prozess">&nbsp;</div>
			<div></div>
		</div>
		<div class="time_prozessbar_container">
			<div id="time_prozessbar" class="{$uniquekey}{$sLiveshoppingData.ordernumber}_hours_prozess">&nbsp;</div>
			<div></div>
		</div>
		<div class="time_prozessbar_container">
			<div id="time_prozessbar" class="{$uniquekey}{$sLiveshoppingData.ordernumber}_min_prozess">&nbsp;</div>
			<div></div>
		</div>
		<div class="time_prozessbar_container">
			<div id="time_prozessbar" class="{$uniquekey}{$sLiveshoppingData.ordernumber}_sec_prozess">&nbsp;</div>
			<div></div>
		</div>
		{if $sLiveshoppingData.max_quantity_enable}
		<div class="instock_prozessbar_container">
			<div id="instock_prozessbar" class="{$uniquekey}{$sLiveshoppingData.ordernumber}_instock_prozess">&nbsp;</div>
			<div></div>
		</div>
		{/if}
	</div>
	{* Col 2 *}
	<div style="float:left;">
		<div><span style="font-weight:bold;" class="{$uniquekey}{$sLiveshoppingData.ordernumber}_days">0</span> {* sSnippet: liveshopping tage *}{$sConfig.sSnippets.sArticledays}</div>
		<div><span style="font-weight:bold;" class="{$uniquekey}{$sLiveshoppingData.ordernumber}_hours">0</span> {* sSnippet: liveshopping tage *}{$sConfig.sSnippets.sArticlesLiveshoppingHours}</div>
		<div><span style="font-weight:bold;" class="{$uniquekey}{$sLiveshoppingData.ordernumber}_min">0</span> {* sSnippet: liveshopping tage *}{$sConfig.sSnippets.sArticlesLiveshoppingMinutes}</div>
		<div><span style="font-weight:bold;" class="{$uniquekey}{$sLiveshoppingData.ordernumber}_sec">0</span> {* sSnippet: liveshopping tage *}{$sConfig.sSnippets.sArticlesLiveshoppingSeconds}</div>
		
		{if $sLiveshoppingData.max_quantity_enable}
		<div>{* sSnippet: liveshopping tage *}{$sConfig.sSnippets.sArticlesLiveshoppingJust} <span style="font-weight:bold;">{$sLiveshoppingData.max_quantity} {* sSnippet: liveshopping tage *}{$sConfig.sSnippets.sArticlesLiveshoppingPiece}</span></div>
		{/if}
	</div>			
</div>