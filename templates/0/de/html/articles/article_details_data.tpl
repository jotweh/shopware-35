
{* ORDERNUMBER AND ATTRIBUTES *}


	{if $sArticle.liveshoppingData.valid_to_ts}
			{if (2==$sArticle.liveshoppingData.typeID || 3==$sArticle.liveshoppingData.typeID)}
				{include file="articles/liveshopping/liveshopping_detail_countdown.tpl" sLiveshoppingData=$sArticle.liveshoppingData}
			{else}
				{include file="articles/liveshopping/liveshopping_detail.tpl" sLiveshoppingData=$sArticle.liveshoppingData sArticlePseudoprice=$sArticle.pseudoprice}
			{/if}
	{/if}
	{* LIVE-SHOPPING - END *}
	
	{* Lagerbestand der Artikel zwischenspeichern *} 
	<input id='instock_{$sArticle.ordernumber}'type='hidden' value='{$sArticle.instock}' /> 
	{* Preis der Artikel zwischenspeichern *} 
	{if $sArticle.sBlockPrices && (!$sArticle.sConfigurator || $sArticle.pricegroupActive) && $sArticle.sConfiguratorSettings.type!=2} 
		{foreach from=$sArticle.sBlockPrices item=staffel key=key} 
			{if $staffel.from=="1"} 
				<input id='price_{$sArticle.ordernumber}'type='hidden' value='{$staffel.price|replace:",":"."}' /> 
			{/if} 
		{/foreach} 
	{else} 
		<input id='price_{$sArticle.ordernumber}'type='hidden' value='{$sArticle.price|replace:".":""|replace:",":"."}' /> 
	{/if} 
	
    {if $sArticle.ordernumber} 
        <p>{* sSnippet: Order No. *}{$sConfig.sSnippets.sArticleordernumber} {$sArticle.ordernumber}</p>
    {/if} 
    
    {if $sArticle.attr1} 
        <p>{$sArticle.attr1}</p>
    {/if}
    {if $sArticle.attr2} 
        <p>{$sArticle.attr2}</p>
    {/if}



    
{* SHIPPING INFORMATION *}

    {if $sArticle.shippingfree}
        <p style="color:#F00"><strong>{* sSnippet: Shipping Free Delivery! *}{$sConfig.sSnippets.sArticledaysshippingfree}</strong></p>
    {/if}
    {if isset($sArticle.active)&&!$sArticle.active}
    	<p class="deliverable3">{* sSnippet: not available *}{$sConfig.sSnippets.sArticleNotAvailable}</p>
    {elseif $sArticle.sReleaseDate}
        <p class="deliverable3">{* sSnippet: available from *}{$sConfig.sSnippets.sArticleAvailablefrom} {$sArticle.sReleaseDate}</p>
    {elseif $sArticle.esd}
            <p class="deliverable1">{* sSnippet: Available as an immediate download *}{$sConfig.sSnippets.sArticleavailableimmediate}</p>
    {elseif $sArticle.instock > 0 }
            <p class="deliverable1">{$sConfig.sSnippets.sDelivery1}</p>
    {elseif $sArticle.shippingtime}
            <p class="deliverable2">{* sSnippet: Delivery time *}{$sConfig.sSnippets.sArticledeliverytime} {$sArticle.shippingtime} {* sSnippet: Working days *}{$sConfig.sSnippets.sArticleworkingdays}</p>
    {else}
            <p class="deliverable3">{$sConfig.sNOTAVAILABLE}</p>
    {/if}

{* 25.11.2008, Express-Lieferung / STH *}
{if $sArticle.sExpress}
<br />
<p style="color:#000">
<strong>{* sSnippet: shipping till *}{$sConfig.sSnippets.sArticlesShippingTill} {$sArticle.sExpress.sShippingdate}: </strong>{* sSnippet: order in next *}{$sConfig.sSnippets.sArticlesOrderInNext}
 <span class="deliverable1">{if $sArticle.sExpress.expressH}{$sArticle.sExpress.expressH} {* sSnippet: order in next hours *}{$sConfig.sSnippets.sArticlesOrderInNextHours}{/if} {$sArticle.sExpress.expressM} {* sSnippet: order in next minutes *}{$sConfig.sSnippets.sArticlesLiveshoppingMinutes} </span>{* sSnippet: order in and choose *}{$sConfig.sSnippets.sArticlesOrderInAndChoose}  <strong>{* sSnippet: order in and choose *}{$sConfig.sSnippets.sArticlesOrderOvernight}</strong> {* sSnippet: order in and choose *}{$sConfig.sSnippets.sArticlesOrderOvernight}
<br /><br />
</p>
{/if}

{if !$sArticle.liveshoppingData.valid_to_ts}

	{* //25.11.2008, Express-Lieferung / STH *}
	{if $sArticle.sBlockPrices && (!$sArticle.sConfigurator || $sArticle.pricegroupActive) && $sArticle.sConfiguratorSettings.type!=2 && !$sArticle.liveshoppingData.valid_to_ts}
		<h2 class="headline" style="margin: 0;">{* sSnippet: block pricing *}{$sConfig.sSnippets.sArticleblockpricing}</h2>
			
				<table width="220"  border="0" cellspacing="0" cellpadding="0" class="text" style="padding:5px; color:#666; margin:0;">
				<tr>
				<td width="90"><strong>{* sSnippet: amount *}{$sConfig.sSnippets.sArticleamount}</strong></td><td width=70><strong>{* sSnippet: price per unit *}{$sConfig.sSnippets.sArticlePricePerUnit}</strong></td>
				</tr>
					{foreach from=$sArticle.sBlockPrices item=staffel key=key}
				 		<tr valign="top"><td style="border-bottom: 1px solid #DFDFDF;">
						{if $staffel.from=="1"} 
							{* sSnippet: until *}{$sConfig.sSnippets.sArticleuntil} {$staffel.to}
						{else}
							{* sSnippet: from *}{$sConfig.sSnippets.sArticlefrom} {$staffel.from}
						{/if}
						</td>
						<td style="border-bottom: 1px solid #DFDFDF;">
						<strong>
							{$sConfig.sCURRENCYHTML} {$staffel.price}*
						</strong></td>
						</tr>
					{/foreach} 
			 	</table>
			 				<p class="tax_attention">{* sSnippet: Price *}{$sConfig.sSnippets.sArticleprices} {if $sConfig.sARTICLESOUTPUTNETTO}{* sSnippet: plus *}{$sConfig.sSnippets.sAccountplus}{else}{* sSnippet: inclusive *}{$sConfig.sSnippets.sArticleincl}{/if} {* sSnippet: legal *}{$sConfig.sSnippets.sArticlelegal}<br />{* sSnippet: tax plus *}{$sConfig.sSnippets.sArticletaxplus} {* sSnippet: shipping *}{$sConfig.sSnippets.sArticleshipping}*</a></p>
	
	{else}
	
	
	
		
			
		
			<div class='article_details_bottom'>
				<div {if $sArticle.pseudoprice} class='article_details_price2'>{else} class='article_details_price'>{/if}
				{if $sArticle.pseudoprice}
	            <s style="color:#666666;">{$sConfig.sCURRENCYHTML} {$sArticle.pseudoprice}</s><br />
	            {if $sArticle.pseudopricePercent.float}
	            <span style="font-size:11px">({$sArticle.pseudopricePercent.float} % {* sSnippet: saved *}{$sConfig.sSnippets.sArticlesave})</span>
	            <br />
	            {/if}
	            
	            {/if}
	            {if $sArticle.sConfigurator && $sArticle.sConfiguratorSettings.type==2}
	            {else}
					<strong>{if $sArticle.sConfigurator && $sArticle.sConfiguratorSettings.type==2}{* sSnippet: from *}{$sConfig.sSnippets.sArticlefrom}{/if} {$sConfig.sCURRENCYHTML} {$sArticle.price}</strong>
				{/if}
				</div>
				{* FINANCE HANSEATIC *}
		        {include file="articles/article_details_hanseatic.tpl" sArticle=$sArticle}
		        {* /FINANCE HANSEATIC *}
	
					<p class="tax_attention">{* sSnippet: Price *}{$sConfig.sSnippets.sArticleprices} {if $sConfig.sARTICLESOUTPUTNETTO}{* sSnippet: plus *}{$sConfig.sSnippets.sAccountplus}{else}{* sSnippet: inclusive *}{$sConfig.sSnippets.sArticleincl}{/if} {* sSnippet: legal *}{$sConfig.sSnippets.sArticlelegal}<br />{* sSnippet: tax plus *}{$sConfig.sSnippets.sArticletaxplus} {* sSnippet: shipping *}{$sConfig.sSnippets.sArticleshipping}*</a></p>
			</div>
			
	
	{/if}	
	 {if $sArticle.purchaseunit} <div class='article_details_price'>
	    <strong><span style="font-size:12px;">{$sConfig.sSnippets.sContentPer} {$sArticle.purchaseunit} {$sArticle.sUnit.description}</span>
	    {if $sArticle.purchaseunit == $sArticle.referenceunit} {else}
	    <span style="font-size:10px;">
	     {if $sArticle.referenceunit}<br />{$sConfig.sSnippets.sBasePriceArt} {$sArticle.referenceunit} {$sArticle.sUnit.description} = {$sArticle.referenceprice} {$sConfig.sCURRENCYHTML}{/if}
	    {/if}
	    </span>
	    </strong>
	   </div>{/if}
{/if}