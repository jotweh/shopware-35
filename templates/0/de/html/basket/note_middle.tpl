<div class="cat_text">
	<h1>{* sSnippet: notepad *}{$sConfig.sSnippets.sBasketnotepad}</h1>
	<p>{* sSnippet: save your personal favorites - until the next time with us. *}{$sConfig.sSnippets.sBasketsaveyourpersonalfav}<br/>
	{* sSnippet: Simply put the desired article on the notepad and *}{$sConfig.sSnippets.sBasketjustthedesireditems} {$sShopname} {* sSnippet: it automatically stores your personal wish list. *}{$sConfig.sSnippets.sBasketItautomaticallystores}</p>
</div>

{if $sNotes}	
<!-- form_box -->
    <div class="form_box" style="padding-top: 10px; margin-top:10px;">
    <h2 class="blue" style="padding:7px 35px 10px 35px;">{* sSnippet: Designated article for a later purchase *}{$sConfig.sSnippets.sBasketdesignatedarticle}</h2>
        <fieldset>
          <table width="100%" border="0" cellspacing="0" cellpadding="0" class="basket-middle">
		<tr>
		  <th class="artikel">{* sSnippet: article *}{$sConfig.sSnippets.sBasketArticle}</th>
		  {if $sConfig.sBASKETSHIPPINGINFO}<th class="anzahl">{* sSnippet: availability *}{$sConfig.sSnippets.sBasketavailability}</th>{/if}

		  <th class="stck">{* sSnippet: unit price *}{$sConfig.sSnippets.sBasketunitprice}</th>

		  <th class="del">&nbsp;</th>
		</tr>
        {foreach name=basket from=$sNotes item=sBasketItem key=key}
            {if $sBasketItem.modus != 1}
              
                    
       
		<tr>
						<!-- Produktbild -->
						<td>
							{if $sBasketItem.image.src.0}
								<a href="{$sBasketItem.linkDetails}" title="{$sBasketItem.articlename}" class="thumb_image"><img src="{$sBasketItem.image.src.1}" border="0" alt="{$sBasketItem.articlename}" class="imgwkorb" /></a>
							{else}
							<a href="{$sBasketItem.linkDetails}" title="{$sBasketItem.articlename}" class="thumb_image">	
							<img src="../../media/img/de/layout/no_picture.jpg" alt="{* sSnippet: No picture available *}{$sConfig.sSnippets.sArticlenoPicture}"  />	
							</a>
							{/if}
							<!-- Produktbild Ende-->
							
							<!-- Produktlink -->
							{if $sBasketItem.modus ==0}<a href="{$sBasketItem.linkDetails}" title="{$sBasketItem.articlename}">{$sBasketItem.articlename}</a>
								<p>{* sSnippet: order number *}{$sConfig.sSnippets.sBasketordernumber} {$sBasketItem.ordernumber}</p>
							{else}
							{$sBasketItem.articlename}
							{/if}
										  
						</td>
						{if $sConfig.sBASKETSHIPPINGINFO}
							<!-- Verfügbarkeit-->
				
							<td valign="top">
							
								{if $sBasketItem.sReleaseDate}
							        <p class="deliverable3">{* sSnippet: available from *}{$sConfig.sSnippets.sBasketavailablefrom} {$sBasketItem.sReleaseDate}</p>
							    {elseif $sBasketItem.esd}
							            <p class="deliverable1">{* sSnippet: As an immediate download *}{$sConfig.sSnippets.sBasketasanimmediate}</p>
							    {elseif $sBasketItem.instock > 0 }
							            <p class="deliverable1">{$sConfig.sSnippets.sDelivery1}</p>
							    {elseif $sBasketItem.shippingtime}
							            <p class="deliverable2">{* sSnippet: delivery *}{$sConfig.sSnippets.sBasketdelivery} {$sBasketItem.shippingtime} {* sSnippet: weekdays *}{$sConfig.sSnippets.sBasketweekdays}</p>
							    {else}
							            <p class="deliverable3">{$sConfig.sNOTAVAILABLE}</p>
							    {/if}
						    
							</td>
						{/if}
						
						<td class="priceright" style="text-align:left">
							{if $sBasketItem.itemInfo}
								{$sBasketItem.itemInfo}
							{else}
								{$sConfig.sCURRENCYHTML} {$sBasketItem.price}
							{/if}						
						</td>
						
					
						<td class="center">
						<a href="{$sBasketItem.linkDelete}" class="ico del" title="{* sSnippet: delete this item from basket *}{$sConfig.sSnippets.sBasketdeletethisitemfrombaske}" style="float:left;margin-right:-5px"></a>
						{if !$sBasketItem.sConfigurator}
							<a href="{$sBasketItem.linkBasket}" class="ico basket" title="{* sSnippet: delete this item from basket *}{$sConfig.sSnippets.sArticleinthebasket}" style="float:left;height:25px;width:25px"></a>
						{/if}
						</td>
					</tr>
                <div class="fixfloat"></div>
                </div>
            {/if}			
            {/foreach}
            </table>
        </fieldset>
        <div class="form_box_cap"></div>
    </div>
<!-- /form_box -->	

{else}
	<h1 class="headline" style="margin-top:15px; text-align:center;">{* sSnippet: you have no articles on your notepad *}{$sConfig.sSnippets.sBasketnoitemsonyournotepad}</h1>
{/if}