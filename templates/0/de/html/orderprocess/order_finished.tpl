<div class="cat_text">
    <h1>{* sSnippet: Thank you for your order at *}{$sConfig.sSnippets.sOrderprocessforyourorder}{$sShopname}!</h1>
    <p>{* sSnippet: we have provided you with an order confirmation sent via email. *}{$sConfig.sSnippets.sOrderprocesswehaveprovided}<br />{* sSnippet: we recommend to print the order confirmation *}{$sConfig.sSnippets.sOrderprocessrecommendtoprint}</p>
    <br />
    <a href="#" class="btn_high_l button float_reset" style="margin-left:0px;" onclick="self.print()" title="{* sSnippet: print the order confirmation now *}{$sConfig.sSnippets.sOrderprocessprintorderconf}">{* sSnippet: print *}{$sConfig.sSnippets.sOrderprocessprint}</a>
</div>

{if $sOrderNumber}{* sSnippet: order number *}{$sConfig.sSnippets.sOrderprocessordernumber} {$sOrderNumber}<br />{/if}
{if $sTransactionumber}{* sSnippet: Transaction number *}{$sConfig.sSnippets.sOrderprocesstransactionumber} {$sTransactionumber}{/if}


<h1 style="margin: 0 0 15px 0;">{* sSnippet: information about your order *}{$sConfig.sSnippets.sOrderprocessinformationsabout}</h1>
{* FORM_BOX START *}
    <div class="form_box" style="padding-top:10px; background-color:transparent;">
        <table width="100%" border="0" cellspacing="0" cellpadding="0" class="basket-middle" style="border-bottom: 1px solid #fff;">
        {* HEADLINE START *}
            <tr>
              <th class="artikeldown">{* sSnippet: article *}{$sConfig.sSnippets.sOrderprocessarticle}</th>
              <th class="anzahl">{* sSnippet: amount *}{$sConfig.sSnippets.sOrderprocessamount}</th>
              <th class="sum">{* sSnippet: total price *}{$sConfig.sSnippets.sOrderprocesstotalprice}</th>
            </tr>
        {* HEADLINE END *}
        
            {foreach name=basket from=$sBasketAfterOrder.content item=sBasketItem key=key}   
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
	
	                    <td> 
                        	{$sBasketItem.quantity} {if $sBasketItem.packunit}{$sBasketItem.packunit}{else}{$sBasketItem.itemUnit}{/if} 
                        </td> 
                        <td class="sum">{$sConfig.sCURRENCYHTML} {$sBasketItem.amount}</td> 
                   </tr> 
            	{else}
	                {* DATENSATZ START *}
	                <tr>	
	                    <td>{if $sBasketItem.image.src.0}
	                                        <a href="{$sBasketItem.linkDetails}" class="thumb_image"><img src="{$sBasketItem.image.src.2}" border="0" alt="{$sBasketItem.articlename}" class="imgwkorb" /></a>
	                                    {/if}{if $sBasketItem.modus eq 0}<a href="{$sBasketItem.linkDetails}">{/if}{$sBasketItem.articlename}{if $sBasketItem.modus eq 0}</a>{/if}</td>
	                    <td>
	                    {$sBasketItem.quantity} {if $sBasketItem.packunit}{$sBasketItem.packunit}{else}{$sBasketItem.itemUnit}{/if}</td>
	                    <td class="sum">{if $sBasketItem.modus eq 0}{$sConfig.sCURRENCYHTML} {$sBasketItem.amount}{elseif $sBasketItem.modus eq 2}{$sConfig.sCURRENCYHTML} {$sBasketItem.amount}
	                	{elseif $sBasketItem.modus eq 3}{$sConfig.sCURRENCYHTML} {$sBasketItem.amount}{elseif $sBasketItem.modus eq 4}{$sConfig.sCURRENCYHTML} {$sBasketItem.amount}
	                	{else}<span class="fett">{* sSnippet: free *}{$sConfig.sSnippets.sOrderprocessfree}</span>{/if}</td>
	                </tr>    
	                {* DATENSATZ END *}
                {/if}
           {/foreach}
            <tr>
                <td align="right" colspan="2"><strong>{* sSnippet: forwarding expense *}{$sConfig.sSnippets.sOrderprocessforwardingexpense}</strong></td>
                <td align="right" class="sum">{$sConfig.sCURRENCYHTML} {$sShippingcosts}</td>	
            </tr>
            <tr>
                <td align="right" colspan="2"><strong>{* sSnippet: net total *}{$sConfig.sSnippets.sOrderprocessnettotal}</strong></td>
                <td align="right" class="sum">{$sConfig.sCURRENCYHTML} {$sAmountNet}</td>
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
			</tr>
			{/if}
          </table>
      <div class="form_box_cap"></div>
    </div>
{* /FROM_BOX *}
{if $sNet}
	{assign var="sRealAmount" value=$sAmountNet|replace:",":"."}
{else}
	{if $sAmountWithTax}
		{assign var="sRealAmount" value=$sAmountWithTax|replace:",":"."}
	{else}
		{assign var="sRealAmount" value=$sAmount|replace:",":"."}
	{/if}
{/if}
{if $sConfig.sGOOGLECONVERSION}
	<script language="JavaScript" type="text/javascript">
    <!--
    var google_conversion_id = {$sConfig.sGOOGLECONVERSION};
    var google_conversion_language = "de";
    var google_conversion_format = "1";
    var google_conversion_color = "FFFFFF";
    var google_conversion_value = {$sRealAmount};
    var google_conversion_label = "purchase";
    //-->
    </script>
    <script language="JavaScript" 
    src="https://www.googleadservices.com/pagead/conversion.js">
    </script>
    
    <noscript>
    <img height=1 width=1 border=0 
    src="https://www.googleadservices.com/pagead/conversion/{$sConfig.sGOOGLECONVERSION}/imp.gif?value={$sRealAmount}&label=purchase&script=0">
    </noscript>
{/if}


{* TRUSTED SHOPS FORMULAR *}
{if $sConfig.sTSID}
    <table width=400 border="0" cellspacing="0" cellpadding="4">
    <tr>
    <td width="90">
    <form name="formSiegel" method="post"
    action="https://www.trustedshops.de/de/tshops/seal_de.php3" target="_blank">
    <input type="image" border="0" src="../../../../0/de/media/img/default/store/logo_trusted_shop.gif" title="{* sSnippet: Trusted shops - click here. *}{$sConfig.sSnippets.sOrderprocessclickhere}">
    <input type="hidden" name="_charset_">
    <input name="shop_id" type="hidden" value="{$sConfig.sTSID}">
    </form>
    </td>
    <td align="justify">
    <form id="formTShops" name="formTShops" method="post" action="https://www.trustedshops.de/de/tshops/protect_de.php3" target="_blank">
    <input name="shop_id" type="hidden" value="{$sConfig.sTSID}">
    <input name="title" type="hidden" value="{if $sUserData.billingaddress.salutation eq "mr"}{* sSnippet: Mr. *}{$sConfig.sSnippets.sOrderprocessmr}{elseif $sUserData.billingaddress.salutation eq "ms"}{* sSnippet: Ms. *}{$sConfig.sSnippets.sOrderprocessms}{else}{* sSnippet: company *}{$sConfig.sSnippets.sOrderprocesscompany}{/if}">
    <input name="email" type="hidden" value="{$sUserData.additional.user.email}">
    <input name="first_name" type="hidden" value="{$sUserData.billingaddress.firstname}">
    <input name="last_name" type="hidden" value="{$sUserData.billingaddress.lastname}">
    <input name="street" type="hidden" value="{$sUserData.billingaddress.street} {$sUserData.billingaddress.streetnumber}">
    <input name="zip" type="hidden" value="{$sUserData.billingaddress.zipcode}">
    <input name="city" type="hidden" value="{$sUserData.billingaddress.city}">
    <input name="country" type="hidden" value="{$sUserData.additional.country.countryname}">
    <input name="phone" type="hidden" value="{$sUserData.billingaddress.phone}">
    <input name="fax" type="hidden" value="{$sUserData.billingaddress.fax}">
    <input name="delivery" type="hidden" value="">
    <input name="amount" type="hidden" value="{$sRealAmount}">
    <input name="curr" type="hidden" value="{$sConfig.sCURRENCY}">
    <input name="payment" type="hidden" value="{$sUserData.additional.payment.id}">
    <input name="KDNR" type="hidden" value="{$sUserData.billingaddress.customernumber}">
    <input name="ORDERNR" type="hidden" value="{$sOrderNumber}">
    <font size="2"
    face="Arial,Helvetica,Geneva,Swiss,SunSans-Regular" color="#000000">
    {* sSnippet: As a member of Trusted Shops, we offer a
     additional money-back guarantee. We take all
     Cost of this warranty, you only need to be
     register. *}{$sConfig.sSnippets.sOrderprocesstrustedshopmember}<br><br>
    <input type="submit" id="btnProtect" name="btnProtect"
    value="{* sSnippet: Registration for the money-back guarantee *}{$sConfig.sSnippets.sOrderprocessforthemoneyback}">
    </font>
    </form>
    </td>
    </tr>
    </table>
{/if}