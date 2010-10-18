<div class="step_box">
	<div class="step">
		<div class="step_number active_number">{$sConfig.sSnippets.sBasketstep1}</div>
		<div class="step_desc active_desc">{$sConfig.sSnippets.sBasketstep1basket}</div>
	</div>
	<div class="step">
		<div class="step_number">{$sConfig.sSnippets.sBasketstep2}</div>
		<div class="step_desc">{$sConfig.sSnippets.sBasketstep2adress}</div>
	</div>
	<div class="step">
		<div class="step_number">{$sConfig.sSnippets.sBasketstep3}</div>
		<div class="step_desc">{$sConfig.sSnippets.sBasketstep3payment}</div>
	</div>
	<div class="step">
		<div class="step_number">{$sConfig.sSnippets.sBasketstep4}</div>
		<div class="step_desc">{$sConfig.sSnippets.sBasketstep4order}</div>
	</div>
</div>

{if $sBasketInfo}
<div class="bg_cross" style="height:80px;">
<p class="heading">{$sBasketInfo}</p>
<a href="{if $sBasket.sLastActiveArticle.link}{$sBasket.sLastActiveArticle.link}{else}javascript:history.back(){/if}" title="{* sSnippet: back to mainpage *}{$sConfig.sSnippets.sBasketbacktomainpage}" class="btn_def_l button width_reset">{* sSnippet: continue shopping *}{$sConfig.sSnippets.sBasketcontinueshopping}</a>
</div>
{/if}
{if $sBasket.Amount || $sBasket.content|@count}
{if $sArticleName && $_GET.sAdd}
		<div class="bg_cross">
            <p class="heading">{$sArticleName} {* sSnippet: added to the basket *}{$sConfig.sSnippets.sBasketaddedtothebasket}</p>
            {if $sBasket.sLastActiveArticle.link}
            <a href="{$sBasket.sLastActiveArticle.link}" title="{* sSnippet: back to mainpage *}{$sConfig.sSnippets.sBasketbacktomainpage}" class="btn_def_l button width_reset">{* sSnippet: continue shopping *}{$sConfig.sSnippets.sBasketcontinueshopping}</a>
            {/if}
            {if !$sMinimumSurcharge && !$sDispatchNoOrder}
            <a href="{$sBasefile}?sViewport=sale&sUseSSL=1" title="{* sSnippet: to checkout! *}{$sConfig.sSnippets.sBaskettocheckout}" class="btn_high_r button width_reset" >{* sSnippet: checkout *}{$sConfig.sSnippets.sBasketcheckout}</a>
            {/if}
        </div>
{/if}

<!-- form_box -->
<div class="form_box" style="padding:0; background-color:transparent;">
	<table width="100%" border="0" cellspacing="0" cellpadding="0" class="basket-middle">
		<tr>
		  <th class="artikel">{* sSnippet: article *}{$sConfig.sSnippets.sBasketArticle}</th>
		  {if $sConfig.sBASKETSHIPPINGINFO}<th class="anzahl">{* sSnippet: availability *}{$sConfig.sSnippets.sBasketavailability}</th>{/if}
		  <th class="anzahl">{* sSnippet: number *}{$sConfig.sSnippets.sBasketnumber}</th>
		  <th class="stck">{* sSnippet: unit price *}{$sConfig.sSnippets.sBasketunitprice}</th>
		  <th class="sum">{* sSnippet: sum *}{$sConfig.sSnippets.sBasketsum}</th>
		  <th class="del">&nbsp;</th>
		</tr>
		
		{foreach name=basket from=$sBasket.content item=sBasketItem key=key}   
			{if $sBasketItem.modus != 1 && $sBasketItem.modus != 10}
					<form name="changequantity" id="{$sBasketItem.ordernumber}" method="GET" action="{$sStart}" class="clearfix">
					<input type="hidden" name="sViewport" value="basket">
					<tr>
						<!-- Produktbild -->
						<td>
							{if $sBasketItem.image.src.0}
								<a href="{$sBasketItem.linkDetails}" title="{$sBasketItem.articlename}" class="thumb_image"><img src="{$sBasketItem.image.src.1}" border="0" alt="{$sBasketItem.articlename}" class="imgwkorb" /></a>
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
				
							<td>
							{if $sBasketItem.shippinginfo}
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
						    {/if}
							&nbsp;
							</td>
						{/if}
						<!-- Produktbild Ende-->	
						<td>
							{if $sBasketItem.modus == 0}
								<select name="sQuantity" style="width:65px; margin: 0 5px 0 0;" onChange="$('{$sBasketItem.ordernumber}').submit();">
								{section name="i" start=$sBasketItem.minpurchase loop=$sBasketItem.maxpurchase+1 step=$sBasketItem.purchasesteps}
									{if $smarty.section.i.index==$sBasketItem.quantity}
										<option value="{$smarty.section.i.index}" selected >{$smarty.section.i.index} {if $sBasketItem.packunit}{$sBasketItem.packunit}{else}{$sBasketItem.itemUnit}{/if}</option>
									{else}
										<option value="{$smarty.section.i.index}" >{$smarty.section.i.index} {if $sBasketItem.packunit}{$sBasketItem.packunit}{else}{$sBasketItem.itemUnit}{/if}</option>
									{/if}
								{/section}
								</select>
								<noscript><input type="image" src="../../media/img/default/store/ico_arrow1.gif" title="{* sSnippet: recalculate price - update basket *}{$sConfig.sSnippets.sBasketrecalculateprice}" /></noscript>
								<input type="hidden" name="sArticle" value="{$sBasketItem.id}" />
							{else}
							&nbsp;
							{/if}
                        </td>
						<td class="priceright">
							{if $sBasketItem.itemInfo}
								{$sBasketItem.itemInfo}
							{else}
								{$sConfig.sCURRENCYHTML} {$sBasketItem.price}
							{/if}						
						</td>
						<td class="sum">
							{$sConfig.sCURRENCYHTML} {$sBasketItem.amount}
						</td>
						{if $sBasketItem.modus == 0}
							<td class="center"><a href="{$sBasketItem.linkDelete}" class="ico del" title="{* sSnippet: delete this item from basket *}{$sConfig.sSnippets.sBasketdeletethisitemfrombaske}"></a></td>
						{elseif $sBasketItem.modus == 2}
							<td class="center"><a href="{$sBasketItem.linkDelete}" class="ico del" title="{* sSnippet: delete this item from basket *}{$sConfig.sSnippets.sBasketdeletethisitemfrombaske}"></a></td>
						{else}
						&nbsp;
						{/if}
					</tr>
				</form>
			{elseif $sBasketItem.modus == 10} 
            	<!--  Bundle-Artikel  START--> 
                    <tr style="background-color:#FCD4DC;"> 
                            <td colspan='{if $sConfig.sBASKETSHIPPINGINFO}4{else}3{/if}' style="padding:0px;"> 
                                    <table> 
                                            <tr> 
                                                    <td style="border-bottom:0; padding:0px;"> 
                                                            <span style="color:#FFFFFF; background-color:#E80D3A;font-weight:bold;padding:4px;">{$sConfig.sSnippets.sBasketBundleDiscountText}</span> 
                                                    </td> 
                                                    <td style="border-bottom:0; padding:0px;padding-left:4px;"> 
                                                            <span style="color:#000000;">{$sBasketItem.articlename}</span> 
                                                    </td> 
                                            </tr> 
                                    </table> 
                            </td> 

                            <td class="sum">{$sConfig.sCURRENCYHTML} {$sBasketItem.amount}</td> 
                            <td class="center">&nbsp;</td> 
                    </tr> 
            	<!--  Bundle-Artikel  END -->
			{else}
			<!--  GRATIS dynamisch  START-->
				<tr>
					<td>
						{if $sBasketItem.image.src.0}
							<a class="thumb_image"><img src="{$sBasketItem.image.src.1}" border="0" alt="{$sBasketItem.articlename}" class="imgwkorb" /></a>
						{/if}
						<strong>{$sBasketItem.articlename}</strong><br />{* sSnippet: A small thank you, you get this item for free *}{$sConfig.sSnippets.sBasketasasmallthankyou}
					</td>
					{if $sConfig.sBASKETSHIPPINGINFO}<td>&nbsp;</td>{/if}
					<td>&nbsp;</td>
					<td class="priceright">&nbsp;</td>
					<td class="sum"><strong>{* sSnippet: free *}{$sConfig.sSnippets.sBasketfree}</strong></td>
					<td class="center">&nbsp;</td>
				</tr>
				<!--  GRATIS dynamisch  END -->
			{/if}
		{/foreach}
		
		{* Calculating Shipping-Costs *}
		<tr>
			<td class="mainsum">&nbsp;</td>
			{if $sConfig.sBASKETSHIPPINGINFO}<td class="mainsum">&nbsp;</td>{/if}
			<td colspan="2" style="text-align: right;" class="mainsum"><strong>{* sSnippet: sum *}{$sConfig.sSnippets.sBasketsum}:</strong></td>
			<td class="sum2"><strong>{$sConfig.sCURRENCYHTML} {$sBasket.Amount}</strong></td>
			<td class="mainsum">&nbsp;</td>
		</tr>
		<tr style="background-color: #fff;"">
			<td>&nbsp;</td>
			{if $sConfig.sBASKETSHIPPINGINFO}<td>&nbsp;</td>{/if}
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		</tr>
		{if !$sUserData}
		<form id="recalcShipping" action="{$sBasefile}?sViewport=basket" method="POST">
		
		<tr>
			<td>&nbsp;</td>
			{if $sConfig.sBASKETSHIPPINGINFO}<td>&nbsp;</td>{/if}
			<td colspan="2" style="text-align: right;"><strong>{* sSnippet: delivery country *}{$sConfig.sSnippets.sBasketdeliverycountry}:</strong></td>
			<td colspan="2" class="sum2" align="left">
				<table>
				<tr>
				<td>
					<select name="sCountry" onchange="$('recalcShipping').submit();">
						{foreach from=$sCountryList item=country}
							<option value="{$country.id}" {if $country.flag}selected{/if}>
							{$country.countryname}
							</option>
						{/foreach}
					</select>
				</td>
				</tr>
				</table>
			</td>
		</tr>
		{* Shopware 2.0.4 - Verschiedene Versandarten *}
		{if $sDispatches && $sDispatches|@count > 1}
		<tr>
			<td>&nbsp;</td>
	
			
			{if $sConfig.sBASKETSHIPPINGINFO}<td>&nbsp;</td>{/if}
			<td colspan="2" style="text-align: right;margin-top:15px"><strong>{* sSnippet: dispatch *}{$sConfig.sSnippets.sBasketdispatch}:</strong></td>
			<td colspan="2" class="sum2" align="left">
				<table>
				<tr>
				<td style="border: 0px">
					<select name="sDispatch" onchange="$('recalcShipping').submit();">
						{foreach from=$sDispatches item=dispatch}
							<option value="{$dispatch.id}" {if $dispatch.flag}selected{/if}>
								{$dispatch.name}
							</option>
						{/foreach}
					</select>
					
				</td>
				</tr>
				</table>
			</td>
		</tr>
			{if $selectedDispatch.description}
				<tr>
				<td>&nbsp;</td>
				{if $sConfig.sBASKETSHIPPINGINFO}<td>&nbsp;</td>{/if}
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td colspan="2" class="sum2">
				<table style="border:0px solid;width:200px"><tr><td align="left" style="border:0px">{$selectedDispatch.description}</td></tr></table>
				</td>
				</tr>
			{/if}
		{/if}
		{* // Shopware 2.0.4 - Verschiedene Versandarten // *}
		<tr>
			<td>&nbsp;</td>
			{if $sConfig.sBASKETSHIPPINGINFO}<td>&nbsp;</td>{/if}
			<td colspan="2" style="text-align: right;"><strong>{* sSnippet: payment *}{$sConfig.sSnippets.sBasketpayment}:</strong></td>
			<td colspan="2" class="sum2" align="left">
				<table>
				<tr>
				<td>
					<select name="sPayment"  onchange="$('recalcShipping').submit();">
						{foreach from=$sPayments item=payment}
							<option value="{$payment.id}" {if $payment.flag}selected{/if}>
							{$payment.description}
							</option>
						{/foreach}
					</select>
									</td>
				</tr>
				</table>
			</td>
		</tr>
		<tr style="background-color: #fff;"">
			<td>&nbsp;</td>
			{if $sConfig.sBASKETSHIPPINGINFO}<td>&nbsp;</td>{/if}
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		</tr>
		<noscript>
		<tr style="background-color: #fff;"">
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td><input type="submit" value="{* sSnippet: recalculate *}{$sConfig.sSnippets.sBasketrecalculate}"></td>
			<td>&nbsp;</td>
		</tr>
		</noscript>

		</form>
		{/if}
		<tr>
			<td class="mainsum">&nbsp;</td>
			{if $sConfig.sBASKETSHIPPINGINFO}<td class="mainsum">&nbsp;</td>{/if}
			<td colspan="2" style="text-align: right;" class="mainsum"><strong>{* sSnippet: forwarding expenses *}{$sConfig.sSnippets.sBasketforwardingexpenses}:</strong></td>
			<td class="sum2"><strong>{$sConfig.sCURRENCYHTML} {$sShippingcosts}</strong></td>
			<td class="mainsum">&nbsp;</td>
		</tr>
		{* Sum Basket *}
		<tr>
			<td class="mainsum">&nbsp;</td>
			{if $sConfig.sBASKETSHIPPINGINFO}<td class="mainsum">&nbsp;</td>{/if}
			<td colspan="2" style="text-align: right;" class="mainsum"><strong>{* sSnippet: total sum *}{$sConfig.sSnippets.sBaskettotalsum}:</strong></td>
			<td class="sum2"><strong>{$sConfig.sCURRENCYHTML} {if $sAmountWithTax}{$sAmountWithTax}{else}{$sAmount}{/if}</strong></td>
			<td class="mainsum">&nbsp;</td>
		</tr>
		{* / Sum Basket *}
		
		{* Add article by ordernumber *}
		<form name="frmAdd" method="get" action="{$sBasefile}" id="form_bestellnummer">
		<tr>
		  <td colspan="3" class="noline">
		  	<p style="margin: 10px; padding:2px; float: left; font-weight: bold;">{* sSnippet: add article from our catalogue *}{$sConfig.sSnippets.sBasketarticlefromourcatalogue}:</p>
			<input type="hidden" name="sViewport" value="basket" />				
			<input name="sAdd" type="text" value="{* sSnippet: Order No. *}{$sConfig.sSnippets.sBasketordernumber}" onfocus="this.value='';" class="ordernum" />
			<input name="image" type="image" style="width:11px; height:11px; margin: 15px 10px 10px 0px;" src="../../media/img/default/store/ico_arrow4.gif" />
			</td>
			<td colspan="2" class="noline">{if $sNotFound}<p style="margin: 10px; padding:2px; float: left; font-weight: bold;"><span style="color:#F00">{* sSnippet: article not found *}{$sConfig.sSnippets.sBasketarticlenotfound}</span></p>{else}&nbsp;{/if}</td>
			<td class="noline">&nbsp;</td>
		</tr>
		</form>
		
	</table>
	
{if $sDispatchNoOrder && $sConfig.sSnippets.sBasketNoDispatches}
<div class="error">
{$sConfig.sSnippets.sBasketNoDispatches}
</div>
{/if}

{if $sMinimumSurcharge}
<div class="error">
{* sSnippet: you have the minimum order value from *}{$sConfig.sSnippets.sBasketminimumordervalue} {$sConfig.sCURRENCYHTML} {$sMinimumSurcharge} {* sSnippet: not reached yet *}{$sConfig.sSnippets.sBasketnotreachedyet}
</div>
{/if}
	<div class="buttons" style="padding-top:10px;">{if !$sMinimumSurcharge && !$sDispatchNoOrder}
	        <a href="{$sBasefile}?sViewport=sale&sUseSSL=1" title="{* sSnippet: checkout! *}{$sConfig.sSnippets.sBaskettocheckout}" class="btn_high_r button width_reset" >{* sSnippet: checkout! *}{$sConfig.sSnippets.sBaskettocheckout}</a>{/if}			
	      
			{if $sBasket.sLastActiveArticle.link}
            <a href="{$sBasket.sLastActiveArticle.link}" title="{* sSnippet: back to mainpage *}{$sConfig.sSnippets.sBasketbacktomainpage}" " class="btn_def_l button width_reset">{* sSnippet: continue shopping *}{$sConfig.sSnippets.sBasketcontinueshopping}</a>
			
			
            {/if}
            {if !$sMinimumSurcharge && ($sInquiry || $sDispatchNoOrder)}
			<a href="{$sInquiryLink}" title="{* sSnippet: checkout! *}{$sConfig.sSnippets.sBasketInquiry}" class="btn_def_r button width_reset">{$sConfig.sSnippets.sBasketInquiry}</a>
			{/if}
            
			{if $PaypalStatus && !$sDispatchNoOrder}
			<div class="basket_bottom_paypal">
		        {if $checkUser}
					{if $sLang}
						<a href="{$serverName}engine/connectors/paypalexpress/doPaymentSUser.php?type=express">
						<img src="https://www.paypal.com/de_DE/i/btn/btn_xpressCheckout.gif" /></a>
					{else}
						<a href="{$serverName}engine/connectors/paypalexpress/doPaymentSUser.php?type=express">
						<img src="https://www.paypal.com/en_US/i/btn/btn_xpressCheckout.gif" /></a>
					{/if}
				{else}
					{if $sLang}
						<a href="{$serverName}engine/connectors/paypalexpress/doPaymentGuest.php">
						<img src="https://www.paypal.com/de_DE/i/btn/btn_xpressCheckout.gif" /></a>
					{else}
						<a href="{$serverName}engine/connectors/paypalexpress/doPaymentGuest.php">
						<img src="https://www.paypal.com/en_US/i/btn/btn_xpressCheckout.gif" /></a>
					{/if}
				{/if}
			</div>
			{/if}
			<div class="fixfloat"></div>
	</div>

    
	<div class="form_box_cap"></div>
</div>
<!-- /form_box -->
<div class="fixfloat" style="margin-bottom: 5px;"></div>



{else}
	<h1 class="headline" style="text-align:center;">{* sSnippet: your basket is empty *}{$sConfig.sSnippets.sBasketyourbasketisempty}</h1>
{/if}
<!-- col_center2 -->
<div class="col_center2">
 <!-- Kunden haben auch angeschaut -->
    {if $sCrossSimilarShown}
        <!-- cross_box -->
            <div class="cross_box">
                <div class="cross_box_top2"><p>{* sSnippet: Customers with similar interests, have also looked *}{$sConfig.sSnippets.sBasketcustomerswithyoursimila}</p></div>
                    <div class="cross_box_content">
                    {foreach from=$sCrossSimilarShown item=offer key=key}
                        {include file="articles/article_box_4col_cross_similar.tpl" sArticle=$offer}
                    {/foreach}
                    <div class="fixfloat"></div>
                    </div>
                <div class="cross_box_cap"></div>
            </div>
        <!-- cross_box -->
    {/if}
    <!-- /Kunden haben auch angeschaut -->
    
    <!-- Kunden kauften auch -->
    {if $sCrossBoughtToo}
        <!-- cross_box -->
        <div class="cross_box">
            <div class="cross_box_top1"><p>{* sSnippet: Customers with your goods basket contents, also shop *}{$sConfig.sSnippets.sBasketcheckoutcustomerswithyo}</p></div>
                    <div class="cross_box_content">
                {foreach from=$sCrossBoughtToo item=sArticle key=key}				
                    {include file="articles/article_box_4col_cross_boughttoo.tpl" sArticle=$sArticle}		
                {/foreach}
                <div class="fixfloat"></div>
                </div>
            <div class="cross_box_cap"></div>
        </div>
        <!-- cross_box -->
    {/if}
    <!-- /Kunden kauften auch -->
    
   
    
    <div class="fixfloat"></div>
</div>
<!-- /col_center2 -->



