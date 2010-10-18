{if $sErrorMessages}
	<div class="error"><strong>{* sSnippet: An error has occurred *}{$sConfig.sSnippets.sAccountErrorhasoccurred}</strong><br />
	{foreach from=$sErrorMessages item=errorItem}{$errorItem}<br />{/foreach}
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
            <a href="{$sBasefile}?sViewport=admin&sAction=billing&sUseSSL=1" class="btn_def_r button float_reset">{* sSnippet: modify *}{$sConfig.sSnippets.sAccountmodify}</a>
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
         <a href="{$sBasefile}?sViewport=admin&sAction=shipping&sUseSSL=1" class="btn_def_r button float_reset">{* sSnippet: modify *}{$sConfig.sSnippets.sAccountmodify}</a>
   
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
            <a href="{$sBasefile}?sViewport=admin&sAction=payment&sUseSSL=1" class="btn_def_r button float_reset">{* sSnippet: modify *}{$sConfig.sSnippets.sAccountmodify}</a>
    </div>
    {* /ZAHLUNGSART END *}
  
    <div class="fixfloat"></div>
</div>
{* /ORDERDATA_BOX *}

<div class="fixfloat"></div>


{* NEWSLETTEREINSTELLUNGEN *}
<form name="frmRegister" method="POST" action="{$sBasefile}" id="newsletterfrm">
    {* FORM_BOX *}
    <div class="form_box">
    <p class="heading">{* sSnippet: newsletter settings *}{$sConfig.sSnippets.sAccountnewslettersettings}</p>
    <input name="sAction" type="hidden" value="saveMailProperties" />
    <input name="sViewport" type="hidden" value="admin" />
        <fieldset>
        <p>
        <input type="checkbox" name="newsletter" onchange="$('newsletterfrm').submit();" value="1" id="newsletter" {if $sUserData.additional.user.newsletter}checked{/if} class="chkbox" />
        <label for="newsletter" class="chklabel" style="width:500px; font-weight: bold;">{* sSnippet: Yes, I want to get the free *}{$sConfig.sSnippets.sAccountIwanttoget} {$sShopname} {* sSnippet: newsletter! *}{$sConfig.sSnippets.sAccountthenewsletter}</label>
        </p>
        </fieldset>
    </div>
	{* /FORM_BOX *}
</form>


		<form name="frmRegister" method="post" action="{$sBasefile}" id="frmRegister">
    {* FORM_BOX *}
    <div class="form_box">
    <p class="heading">{* sSnippet: Your access data *}{$sConfig.sSnippets.sAccountYouraccessdata}</p>
        <fieldset>
        	<input name="sAction" type="hidden" value="saveAccount" />
        	<input name="sViewport" type="hidden" value="admin" />
        <p>
        	<label for="kontonr">{* sSnippet: Your e-mail address *}{$sConfig.sSnippets.sAccountYouremailaddress}</label>
        	<input name="email" type="text" id="kontonr" value="{$sUserData.additional.user.email}" class="normal {if $sErrorFlag.email}instyle_error{/if}" />
        </p>
        <p>
        	<label for="blz"> {* sSnippet: New Password *}{$sConfig.sSnippets.sAccountNewPassword}</label>
        	<input name="password" type="password"  class="normal {if $sErrorFlag.password}instyle_error{/if}" />
        </p>
        <p>
        	<label for="bank">{* sSnippet: Repeat password *}{$sConfig.sSnippets.sAccountRepeatpassword}</label>
        	<input name="passwordConfirmation" type="password" class="normal {if $sErrorFlag.passwordConfirmation}instyle_error{/if}" />
        </p>
        	<input type="submit" value="{* sSnippet: modify *}{$sConfig.sSnippets.sAccountmodify}" class="btn_def_r button" />
       <div class="fixfloat"></div>
        </fieldset>	 
        </form>
<div class="fixfloat"></div>
        
    </div>
    {* /FORM_BOX *}
    
<div class="fixfloat"></div>

