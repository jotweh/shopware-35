{if !$sAccountEdit}
<div class="step_box">
	<div class="step">
		<div class="step_number">{$sConfig.sSnippets.sBasketstep1}</div>
		<div class="step_desc">{$sConfig.sSnippets.sBasketstep1basket}</div>
	</div>
	<div class="step">
		<div class="step_number active_number">{$sConfig.sSnippets.sBasketstep2}</div>
		<div class="step_desc active_desc">{$sConfig.sSnippets.sBasketstep2adress}</div>
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
	{if $sBasket.Quantity}
	{/if}
{/if}

{if $sErrorMessages}
	<div class="error"><strong> {* sSnippet: an error has occurred *}{$sConfig.sSnippets.sRegistererroroccurred}</strong><br />
		{foreach from=$sErrorMessages item=errorItem}{$errorItem}<br />{/foreach}
	</div>
{/if}

<form name="frmRegister" method="post" action="{$sBasefile}" class="registerform">

{if $sAccountEdit}
	<input name="sAction" type="hidden" value="saveShipping" />
	<input name="sViewport" type="hidden" value="admin" />
{else}
	<input name="sAction" type="hidden" value="register2shipping" />
	<input name="sViewport" type="hidden" value="register2shipping" />
{/if}

{if $_GET.sTarget}
	<input name="sTarget" type="hidden" value="sale" />
{/if}

{* FORM_BOX *}
<div class="form_box">
{if $sShippingPreviously && $sAccountEdit}
		<p class="heading">{$sConfig.sSnippets.sRegisterUsealreadyusedShippingadress}</p>
       	<fieldset>
       	<p><label for="sSelectAddress">{$sConfig.sSnippets.sAccountshippingaddress}</label>
	    <select name="sSelectAddress" id="sSelectAddress" class="normal">
	    <option>{$sConfig.sSnippets.sRegisterpleaseselect}</option>  
	    {foreach from=$sShippingPreviously item=previousAddress}
	    <option value="{$previousAddress.hash}">{if $previousAddress.company}{$previousAddress.company},{/if}{$previousAddress.firstname} {$previousAddress.lastname}, {$previousAddress.street} {$previousAddress.streetnumber}, {$previousAddress.city}, {$previousAddress.country.countryname}</option>  
	    {/foreach}
	    </select></p>
	    </fieldset>
		<input type="submit" value="{$sConfig.sSnippets.sRegisterUse}" class="btn_high_r button chg_adress" />
{/if}	
    <p class="heading">{* sSnippet: please enter your delivery address *}{$sConfig.sSnippets.sRegisterenterdeliveryaddress}</p>
        <fieldset>
       {if $sAccountEdit}
       
       {/if}
        <p>
            <label for="salutation">{* sSnippet: title *}{$sConfig.sSnippets.sRegistertitle}</label>
            <select name="salutation" id="salutation" class="normal {if $sErrorFlag.salutation}instyle_error{/if}">
            <option value="mr" {if $_POST.salutation eq "mr"}selected{/if}>Herr</option>  
            <option value="ms" {if $_POST.salutation eq "ms"}selected{/if}>Frau</option>   
            </select>
        </p>
        <p>
            <label for="company" class="normal">{* sSnippet: company *}{$sConfig.sSnippets.sRegistercompany}</label>
            <input name="company" type="text"  id="company" value="{$_POST.company}" class="normal {if $sErrorFlag.company}instyle_error{/if}" />
        </p>
        <p>
            <label for="department">{* sSnippet: department *}{$sConfig.sSnippets.sRegisterdepartment}</label>
            <input name="department" type="text"  id="department" value="{$_POST.department}" class="normal" />
        </p>
        <p>
            <label for="firstname">{* sSnippet: first name *}{$sConfig.sSnippets.sRegisterfirstname}</label>
            <input name="firstname" type="text"  id="firstname" value="{$_POST.firstname}" class="normal {if $sErrorFlag.firstname}instyle_error{/if}" />
        </p>
        <p>
            <label for="lastname">{* sSnippet: last name *}{$sConfig.sSnippets.sRegisterlastname}</label>
            <input name="lastname" type="text"  id="lastname" value="{$_POST.lastname}" class="normal {if $sErrorFlag.lastname}instyle_error{/if}" />
        </p>
        <p>
            <label for="street">{* sSnippet: street and number *}{$sConfig.sSnippets.sRegisterstreetandnumber}</label>
            <input name="street" type="text"  id="street" value="{$_POST.street}" class="strasse {if $sErrorFlag.street}instyle_error{/if}" />
            <input name="streetnumber" type="text"  id="streetnumber" value="{$_POST.streetnumber}"  maxlength="5" class="nr {if $sErrorFlag.streetnumber}instyle_error{/if}" />
        </p>
        <p class="none">
            <label for="zipcode">{* sSnippet: city and zipcode *}{$sConfig.sSnippets.sRegistercityandzip}</label>
            <input name="zipcode" type="text" id="zipcode" value="{$_POST.zipcode}" maxlength="5" class="plz {if $sErrorFlag.zipcode}instyle_error{/if}" />
            <input name="city" type="text"  id="city" value="{$_POST.city}" size="25" class="ort {if $sErrorFlag.city}instyle_error{/if}" />
        </p>
        {if $sConfig.sCOUNTRYSHIPPING}
        <p>
			<label for="country">{* sSnippet: country *}{$sConfig.sSnippets.sRegistercountry} </label>
			<select name="country" id="country" class="normal {if $sErrorFlag.country}instyle_error{/if}" style="{if $sErrorFlag.country}background-color:#F7D8D8{/if}">
				<option value="" selected>{* sSnippet: please select *}{$sConfig.sSnippets.sRegisterpleaseselect}</option>
				{foreach from=$sCountryList item=country}
					<option value="{$country.id}" {if $country.flag}selected{/if}>
					{$country.countryname}
					</option>
				{/foreach}
			</select>
		</p>
        {/if}
        </fieldset>
    <p class="reg_obligation">{* sSnippet: the fields marked with * are mandatory. *}{$sConfig.sSnippets.sRegisterfieldsmarked}</p>
    <p class="buttons">
    {if !$sAccountEdit}
    	<a href="javascript:history.back();" class="btn_def_l button">{* sSnippet: back *}{$sConfig.sSnippets.sRegisterback}</a>
    	<input type="submit" value="{* sSnippet: next *}{$sConfig.sSnippets.sRegisternext}" class="btn_high_r button" />	
    {else}
    	<a href="{$sBasefile}?sViewport=sale&sUseSSL=1" class="btn_def_l button">{* sSnippet: back *}{$sConfig.sSnippets.sRegisterback}</a>
    	<input type="submit" value="{* sSnippet: save *}{$sConfig.sSnippets.sRegistersave}" class="btn_high_r button" />	
    {/if}
    </p>
    </form>
    <div class="fixfloat"></div>
</div>
{* /FORM_BOX *}
