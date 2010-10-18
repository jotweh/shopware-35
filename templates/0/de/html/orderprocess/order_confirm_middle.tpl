<div class="step_box">
	<div class="step">
		<div class="step_number">{$sConfig.sSnippets.sBasketstep1}</div>
		<div class="step_desc">{$sConfig.sSnippets.sBasketstep1basket}</div>
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
		<div class="step_number active_number">{$sConfig.sSnippets.sBasketstep4}</div>
		<div class="step_desc active_desc">{$sConfig.sSnippets.sBasketstep4order}</div>
	</div>
</div>

<div class="cat_text">
	<h1>{* sSnippet: Please check your order again, before you send. *}{$sConfig.sSnippets.sOrderprocesspleasecheck}</h1>
	<p style="margin-bottom:15px;">
    {* sSnippet: Billing address, shipping address and payment method, you can now change. *}{$sConfig.sSnippets.sOrderprocessbillingadress} <br />{* sSnippet: The same applies to the selected articles. *}{$sConfig.sSnippets.sOrderprocesssameappliesto}<br /><br />
    {$sConfig.sSnippets.sBankContact}
    </p>
</div>

{if $sUserData.additional.countryShipping.notice}
    <div class="cat_text">
    	<h1>{* sSnippet: Important Info to the supplier country *}{$sConfig.sSnippets.sOrderprocessimportantinfo} {$sUserData.additional.countryShipping.countryname}</h1>
     	<p style="margin-bottom:15px;">{$sUserData.additional.countryShipping.notice}</p>
    </div>
{/if}

{if $sVoucherError}
		<div class="error" id="text_red" style="color:#F00"> 
			{* MELDUNG START *}
			{foreach from=$sVoucherError item=error_item}{$error_item}<br />
			{/foreach}
			{* MELDUNG END *}
		</div>
{/if} 

{if $sAGBError}
		<div class="error"> 
			{* MELDUNG START *}
			<strong>{* sSnippet: Please, accept our Terms and Conditions *}{$sConfig.sSnippets.sOrderprocessacceptourterms}</strong>
			{* MELDUNG END *}
		</div>
{/if} 

{* ORDERDATA_BOX *}
<div class="orderdata_box">

    {* RECHNUNGSADRESSE START *}
    <div class="overview_col1">
    <p class="heading">{* sSnippet: Billing Address *}{$sConfig.sSnippets.sAccountBillingAddress}</p>
    <fieldset>
    <p class="none">
        {* DATEN START *}
            {if $sUserData.billingaddress.company}
            {$sUserData.billingaddress.company}<br />
            {/if}
            {if $sUserData.billingaddress.salutation eq "mr"}
            {* sSnippet: Mr. *}{$sConfig.sSnippets.sAccountMr}
            {elseif $sUserData.billingaddress.salutation eq "ms"}
            {* sSnippet: Ms. *}{$sConfig.sSnippets.sAccountMs}
            {else}
            {* sSnippet: Company *}{$sConfig.sSnippets.sAccountcompany}
            {/if}
            {$sUserData.billingaddress.firstname} {$sUserData.billingaddress.lastname}<br />
            
            
            {$sUserData.billingaddress.street} {$sUserData.billingaddress.streetnumber}<br />
            {$sUserData.billingaddress.zipcode} {$sUserData.billingaddress.city}<br />
            {$sUserData.additional.country.countryname}<br />
        {* /DATEN END *}
    </p>
    </fieldset>
            <a href="{$sBasefile}?sViewport=admin&sAction=billing&sTarget=sale&sUseSSL=1" class="btn_def_r button float_reset">{* sSnippet: modify *}{$sConfig.sSnippets.sAccountmodify}</a>
    {* /RECHNUNGSADRESSE END *}
    </div>
    
    <div class="overview_col2">
    <p class="heading">{* sSnippet: Shipping Address *}{$sConfig.sSnippets.sAccountshippingaddress}</p>
    <fieldset>
    <p class="none">
        {* DATEN START *}
            {if $sUserData.shippingaddress.company}
            {$sUserData.shippingaddress.company}<br />
            {/if}
            {if $sUserData.shippingaddress.salutation eq "mr"}
            {* sSnippet: Mr. *}{$sConfig.sSnippets.sAccountMr}
            {elseif $sUserData.shippingaddress.salutation eq "ms"}
            {* sSnippet: Ms. *}{$sConfig.sSnippets.sAccountMs}
            {else}
            {* sSnippet: Company *}{$sConfig.sSnippets.sAccountcompany}
            {/if}
            {$sUserData.shippingaddress.firstname} {$sUserData.shippingaddress.lastname}<br />
            
            {$sUserData.shippingaddress.street} {$sUserData.shippingaddress.streetnumber}<br />
            {$sUserData.shippingaddress.zipcode} {$sUserData.shippingaddress.city}<br />
            {$sUserData.additional.countryShipping.countryname}<br />
        {* /DATEN END *}
    </p>
    </fieldset>
         <a href="{$sBasefile}?sViewport=admin&sAction=shipping&sTarget=sale&sUseSSL=1" class="btn_def_r button float_reset">{* sSnippet: modify *}{$sConfig.sSnippets.sAccountmodify}</a>
   
    </div>
    
    {* ZAHLUNGSART START *}
    <div class="overview_col3">
    <p class="heading">{* sSnippet: method of payment *}{$sConfig.sSnippets.sAccountmethodofpayment}</p>
        <fieldset>
        <p class="none">
            {$sUserData.additional.payment.description}<br />
            
            {if !$sUserData.additional.payment.esdactive}
                <br />
                <strong>{$sConfig.sSnippets.sPaymentESDInfo}</strong>
            {/if}
        </p>
        </fieldset>
            <a href="{$sBasefile}?sViewport=admin&sAction=payment&sTarget=sale&sUseSSL=1" class="btn_def_r button float_reset">{* sSnippet: modify *}{$sConfig.sSnippets.sAccountmodify}</a>
    </div>
    {* /ZAHLUNGSART END *}
  
    <div class="fixfloat"></div>
</div>
{* /ORDERDATA_BOX *}

<div class="fixfloat"></div>

{* FORM BOX *}
<div class="form_box" style="padding-top: 0px; margin:0px; background-color: transparent;">
    {* WARENKORB START *}
    <table width="100%" border="0" cellspacing="0" cellpadding="0" class="basket-middle" style="border-bottom: 1px solid #fff;">
        {* HEADLINE START *}
            <tr>
              <th class="artikel">{* sSnippet: article *}{$sConfig.sSnippets.sOrderprocessarticle}</th>
              <th class="anzahl">{* sSnippet: amount *}{$sConfig.sSnippets.sOrderprocessamount}</th>
              <th class="sum">{* sSnippet: price *}{$sConfig.sSnippets.sOrderprocessprice}</th>
              <th class="sum">{* sSnippet: price *}{$sConfig.sSnippets.sOrderprocessTax}</th>
            </tr>
        {* HEADLINE END *}
        
        {foreach name=basket from=$sBasket.content item=sBasketItem key=key} 
            {if $sBasketItem.modus == 10} 
                    <tr style="background-color:#FCD4DC;"> 
                                    <td colspan='1' style="padding:0px;"> 
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

                                    <td>{$sBasketItem.quantity} {if $sBasketItem.packunit}{$sBasketItem.packunit}{else}{$sBasketItem.itemUnit}{/if}</td> 
                                    <td class="sum">{$sConfig.sCURRENCYHTML} {$sBasketItem.amount}</td> 
                                    <td class="sum">{$sConfig.sCURRENCYHTML} {$sBasketItem.tax}</td> 
                            </tr> 
            {else} 
                {* DATENSATZ START *} 
                <tr> 
                    <td>{if $sBasketItem.image.src.0} 
                            <a href="{$sBasketItem.linkDetails}" class="thumb_image"><img src="{$sBasketItem.image.src.1}" border="0" alt="{$sBasketItem.articlename}" class="imgwkorb" /></a> 
                        {/if} 
                        {if $sBasketItem.modus eq 0}<a href="{$sBasketItem.linkDetails}">{/if}{$sBasketItem.articlename}{if $sBasketItem.modus eq 0}</a>{/if}</td> 
                    <td> 
                    {$sBasketItem.quantity} {if $sBasketItem.packunit}{$sBasketItem.packunit}{else}{$sBasketItem.itemUnit}{/if}</td> 
                    <td class="sum">{if $sBasketItem.modus eq 0 || $sBasketItem.modus eq 10}{$sConfig.sCURRENCYHTML} {$sBasketItem.amount}{elseif $sBasketItem.modus eq 2}{$sConfig.sCURRENCYHTML}{$sBasketItem.amount} 
                    {elseif $sBasketItem.modus eq 3}{$sConfig.sCURRENCYHTML} {$sBasketItem.amount}{elseif $sBasketItem.modus eq 4}{$sConfig.sCURRENCYHTML} {$sBasketItem.amount} 
                    {else}<span class="fett">GRATIS</span>{/if}</td> 
                    <td class="sum">{$sConfig.sCURRENCYHTML} {$sBasketItem.tax}</td> 
                </tr> 
        	{/if}   
            {* DATENSATZ END *}
        {/foreach}
        
        {* Shopware 2.0.4 - Verschiedene Versandarten *}
        {if $sDispatches}
        <form id="recalcShipping" action="" method="POST">
        <input name="sViewport" type="hidden" value="sale" />
		<tr>
			<td>&nbsp;</td>
			<td style="text-align: right;margin-top:15px"><strong>{* sSnippet: dispatch *}{$sConfig.sSnippets.sOrderprocessdispatch}</strong></td>
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
		<noscript>
				<tr>
				<td>&nbsp;</td>
				<td colspan="2" class="sum2">
				<table style="border:0px solid;width:200px"><tr><td align="left" style="border:0px"><input type="submit" value="{* sSnippet: recalculate *}{$sConfig.sSnippets.sBasketrecalculate}"></td></tr></table>
				</td>
				</tr>
				</noscript>
				
			{if $selectedDispatch.description}
				<tr>
				<td>&nbsp;</td>
				<td colspan="2" class="sum2">
				<table style="border:0px solid;width:200px"><tr><td align="left" style="border:0px">{$selectedDispatch.description}</td></tr></table>
				</td>
				</tr>
			{/if}
			

	
        </form>
        {/if}
         {* // Shopware 2.0.4 - Verschiedene Versandarten *}
        <tr>
            <td align="right" colspan="2"><strong>{* sSnippet: forwarding expenses *}{$sConfig.sSnippets.sOrderprocessforwardingexpense}</strong></td>
            <td align="right" class="sum">{$sConfig.sCURRENCYHTML} {$sShippingcosts}</td>	
			<td>&nbsp;</td>
        </tr>
        <tr>
            <td align="right" colspan="2"><strong>{* sSnippet: net total *}{$sConfig.sSnippets.sOrderprocessnettotal}</strong></td>
            <td align="right" class="sum">{$sConfig.sCURRENCYHTML} {$sAmountNet}</td>
			<td>&nbsp;</td>
        </tr>
        {if $sNet}
		<tr>
		<td colspan="2">
		{$sConfig.sSnippets.sOrderNetAdvice}
		</td>
		</tr>
		{else}
		<tr>
		    <td align="right" colspan="2" class="mainsum"><strong>{* sSnippet: total incl. vat *}{$sConfig.sSnippets.sOrderprocesstotalinclvat}</strong></td>
		    <td align="right" class="sum2" style="width:90px;">{$sConfig.sCURRENCYHTML} {if $sAmountWithTax}{$sAmountWithTax}{else}{$sAmount}{/if}</td>
			<td class="sum2">&nbsp;</td>
		</tr>
		{/if}
      </table>
    {* WARENKORB END *}
    
    
{* VOUCHER START *}
    <form name="frmAdd" method="post" action="" class="registerform" style="background-color:#f6f6f6; padding:10px 0px;">
    <input name="sViewport" type="hidden" value="sale" />
        <fieldset style="margin:0;">
            <p class="none" style="height: 20px; margin: 0; padding-bottom: 0;">
            	<label for="sVoucher">{* sSnippet: vouchernumber *}{$sConfig.sSnippets.sOrderprocessvouchernumber}</label>
            	<input name="sVoucher" type="text" onFocus="this.value='';" class="normal" style="float: left;" /> 
            	<input name="image" type="image" style="float: left; width:11px; height:11px;" src="../../../../0/de/media/img/default/store/ico_arrow4.gif" />
            	<div class="fixfloat"></div>
                <p class="description" style="height:30px; border-bottom: none; padding:0px 20px 0px 220px; margin:0;">
                {* sSnippet: Please, give here your voucher code and click on "Arrow" *}{$sConfig.sSnippets.sOrderprocessyourvouchercode}<br /> {* sSnippet: per order can be max. one voucher redeemed. *}{$sConfig.sSnippets.sOrderprocessperorderonevouche}</p>
            </p>
        </fieldset>
    </form>
    </div>
{* VOUCHER END *}

{*  CHECK FOR ESD IN BASKET AND AVOID ORDER IF PAYMENT IS NOT ESD-ACTIVE*}
{if $sShowEsdNote}
<h1>{* sSnippet: Please change your payment method. The purchase of instant downloads with your selected payment is not possible! *}{$sConfig.sSnippets.sOrderprocesschangeyourpayment}</h1>
{else}
{* SUBMIT ORDER *}
	{if $sEmbedded && !$sMinimumSurcharge && !$sDispatchNoOrder}
	    <div class="form_box" style="padding-top: 10px; margin-top:15px;">
	        <h2 class="blue" style="margin-left:10px;">{* sSnippet: Please make the payment now *}{$sConfig.sSnippets.sOrderprocessmakethepayment}</h2>
	        <div id="zahlungsweise">
	           <iframe style="border:0px;width:100%;height:700px;" width="100%" frameborder="0" border="0" src="{$sEmbedded}?sCoreId={$sCoreId}"></iframe>
	        </div>
	        <div class="form_box_cap"></div>
	    </div>
	    <div class="fixfloat"></div> 
	
	{else}
	
	    <form name="frmRegister" method="POST" action="" class="registerform" id="schnellregistrierung" style="border:0; margin:0; padding:0;">
	    <input name="sAction" type="hidden" value="doSale" />
	    <input name="sViewport" type="hidden" value="sale" />
	    
	    <div class="fixfloat"></div> 
	    
	        {* ORDER COMMENT START *}
	        <div class="form_box" >
	            <fieldset style="margin:0;">
	                <p class="none" style="height: 100px;">
	                <label for="sComment">{* sSnippet: comment *}{$sConfig.sSnippets.sOrderprocesscomment}</label>
	                <textarea name="sComment" rows=5 cols=30 onFocus="" class="normal" style="float: left;">{$_POST.sComment}</textarea>
	                 
	                </p>
	                <p class="description" style="height:20px; padding:0px 20px 0px 220px;">
	                {* sSnippet: Please enter additional information about your purchase order *}{$sConfig.sSnippets.sOrderprocessenteradditional}
	                </p>
	            </fieldset>
	        </div>
	        <div class="form_box_cap"></div>
	        <div class="fixfloat"></div> 
	        {* ORDER COMMENT END *}
	        
{if $sDispatchNoOrder}
<div class="error">
{$sConfig.sSnippets.sBasketNoDispatches}
</div>
{/if}
	        
 {if $sMinimumSurcharge}
<div class="error">
{* sSnippet: They have the minimum order value from *}{$sConfig.sSnippets.sOrderprocessminimumordervalue}{$sConfig.sCURRENCYHTML} {$sMinimumSurcharge} {* sSnippet: does not reach *}{$sConfig.sSnippets.sOrderprocessdoesnotreach}
</div>
{/if}
	    {* AGB START *}
	    {if !$sConfig.sIGNOREAGB}
	    <div class="agb_accept">
	    	<input type="checkbox" style="width:25px;float:left;" name="sAGB" id="sAGB" value="1">
	    	<label for="sAGB" class="chklabel">{if $sAGBError}<span style="color:#F00;">{/if}{$sConfig.sSnippets.sAGBText}{if $sAGBError}</span>{/if}</label>
	    </div>
	    
	    <div class="agb_info">
	    	{$sConfig.sSnippets.sOrderInfo}
	    </div>
	    {/if}
	    {* AGB END *}
	    {if !$sMinimumSurcharge && !$sDispatchNoOrder}
	    <div class="buttons">
	    	<a href="{$sBasefile}?sViewport=basket" class="btn_def_l button width_reset">{* sSnippet: change basket *}{$sConfig.sSnippets.sOrderprocesschangebasket}</a>
	    	<input type="submit" value="{* sSnippet: Send order now *}{$sConfig.sSnippets.sOrderprocesssendordernow}" class="btn_high_r button width_reset" style="width:215px;" />
	    	<div class="fixfloat"></div>
	    </div><div class="fixfloat"></div>
	    {/if}
	    </form>
	{/if}
{/if}
	
