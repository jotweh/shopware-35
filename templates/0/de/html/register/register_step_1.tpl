
{* SHOW IF ERROR *}
	{if $sErrorMessages}
		<div class="error"><strong> {* sSnippet: an error has occurred *}{$sConfig.sSnippets.sRegistererroroccurred}</strong><br />
			{foreach from=$sErrorMessages item=errorItem}{$errorItem}<br />{/foreach}
		</div>
	{/if}


	<script language="JavaScript" type="text/javascript">
    var skipState = {if $_POST.skipLogin}1{else}0{/if};
    {literal}
    function refreshAccount(){
        if (skipState==0){
            skipState = 1;
            $('passwordForm').setStyle('display','none');
        }else {
            skipState = 0;
            $('passwordForm').setStyle('display','block');
        }
    }
    </script>
    {/literal}

{if $_GET.sValidation != "H"}
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
{/if}

{if $_GET.sValidation == "H"}
    <div class="form_box">
        <h2 class="blue" style="padding:35px 35px 10px 35px;">{$sShopname} {* sSnippet: trader registration *}{$sConfig.sSnippets.sRegistertraderregistration}</h2>
        <p style="padding: 0 35px 0 35px;">
        <strong>{* sSnippet: you already have a trader account? *}{$sConfig.sSnippets.sRegisteralreadyhaveatraderacc}</strong><br />
        <a href="{$sBasefile}?sViewport=login" class="ico link">{* sSnippet: click here to log in *}{$sConfig.sSnippets.sRegisterclickheretologin}</a><br />
        <br />
        <ul  style="padding: 0 35px 0 35px;">
        <li><h2 class="blue">{* sSnippet: after registering, you see retail prices until you are unlocked *}{$sConfig.sSnippets.sRegisterafterregistering}</h2></li>
        <li><br /><h2 class="blue">{* sSnippet: send us your trade proof by fax *}{$sConfig.sSnippets.sRegistersendusyourtradeproof}</h2><br />{* sSnippet: Send your trade proof by fax to +49 2555 92 95 61. If you are already traders with us, you can jump over this step and must not send a trade proof. *}{$sConfig.sSnippets.sRegistersendusyourtradeproofb}</li>
        <li><br /><h2 class="blue">{* sSnippet: we check your information and switch you freely! *}{$sConfig.sSnippets.sRegisterwecheck}</h2><br />{* sSnippet: we switch you freely after check as a trader. Then you receive from us an info by e-mail. from now on you see directly your trader prices, on the product and overview sides. *}{$sConfig.sSnippets.sRegisterwecheckyouastrader}</li>
        </ul>
        
        </p>
        <div class="form_box_cap"></div>
    </div><br /><br />
{/if}


		
<form name="frmRegister" method="post" action="{$sBasefile}" id="schnellregistrierung" autocomplete="off">
<input name="sAction" type="hidden" value="register1" />
<input name="sViewport" type="hidden" value="{$_GET.sViewport}" />
<input name="sValidation" type="hidden" value="{$_GET.sValidation}">
{* FORM_BOX *}
<div class="form_box">
<h2 class="blue" style="padding:35px 35px 10px 35px;">{* sSnippet: your access data *}{$sConfig.sSnippets.sRegisteraccessdata}</h2>
<p style="padding: 0 35px 0 35px;">{* sSnippet: to help you in the future quickly and easily change your data or to track your order please put your personal access data. *}{$sConfig.sSnippets.sRegisterinthefuture}</p>
    <fieldset>
    <p>
        <label for="email">{* sSnippet: your email adress *}{$sConfig.sSnippets.sRegisteryouremail}</label>
        <input name="email" autocomplete="off"  type="text" id="email" value="{$_POST.email}" class="normal {if $sErrorFlag.email}instyle_error{/if}" />
    </p>
	{if $sConfig.sDOUBLEEMAILVALIDATION}
	    <p>
	        <label for="emailConfirmation">{* sSnippet: your email adress *}{$sConfig.sSnippets.sRegisteryouremailconfirmation}</label>
	        <input name="emailConfirmation" autocomplete="off"  type="text" id="emailConfirmation" value="{$_POST.emailConfirmation}" class="normal {if $sErrorFlag.emailConfirmation}instyle_error{/if}" />
	    </p>
    {/if}
    <p class="check">
        <input type="checkbox" value="1" name="receiveNewsletter" class="chkbox" {if $_POST.receiveNewsletter OR !$_POST}checked{/if} />
        <label for="receiveNewsletter" class="chklabel" style="width:400px;">{* sSnippet: would like to subscribe to the newsletter (unsubscribe at any time). *}{$sConfig.sSnippets.sRegistersubscribenewsletter}</label>
    </p>
            
    {if !$sEsd AND $_GET.sValidation != "H"}
        <p class="check">
            <input type="checkbox" value="1" onclick="refreshAccount()" id="skipLogin" name="skipLogin" class="chkbox" {if $_POST.skipLogin}checked {/if}/>
            <label for="skipLogin" class="chklabel"><strong>{* sSnippet: no customer account *}{$sConfig.sSnippets.sRegisternocustomeraccount}</strong></label>
        </p>		
    {/if}
    
    {* PASSWORD FORM *}
    <div id="passwordForm" {if $_POST.skipLogin}style="display:none"{/if}>
        <p class="none">
            <label for="password">{* sSnippet: your password *}{$sConfig.sSnippets.sRegisteryourpassword}</label>
            <input name="password" autocomplete="off" type="password" id="password1" class="normal {if $sErrorFlag.password}instyle_error{/if}" />
        </p>   
     
        <p class="description">
        {* sSnippet: your password must be at least *}{$sConfig.sSnippets.sRegisteryourpasswordatlast}{$sConfig.sMINPASSWORD} {* sSnippet: characters *}{$sConfig.sSnippets.sRegistercharacters}<br /> {* sSnippet: Consider uppercase and lowercase letters. *}{$sConfig.sSnippets.sRegisterconsiderupper}
        </p>
    
        <p>
            <label for="passwordConfirmation">{* sSnippet: repeat your password *}{$sConfig.sSnippets.sRegisterrepeatyourpassword}</label>         
            <input name="passwordConfirmation"  autocomplete="off"  type="password" id="passwordConfirmation" class="normal {if $sErrorFlag.passwordConfirmation }instyle_error{/if}" />
        </p>
    </div>
    {* /PASSWORD FORM *}
    
    </fieldset>

	
<h2 class="blue" style="padding:35px 35px 10px 35px;">{* sSnippet: your account data *}{$sConfig.sSnippets.sRegisteryouraccountdata}</h2>
	<p style="padding: 0 35px 0 35px;">{* sSnippet: on this and the following pages tell us the necessary data for your order *}{$sConfig.sSnippets.sRegisteronthisfollowingpages} </p>

<fieldset>
<p>
<label for="salutation">{* sSnippet: title *}{$sConfig.sSnippets.sRegistertitle}</label>
<select name="salutation" id="salutation" class="normal" style="{if $sErrorFlag.salutation}background-color:#F7D8D8{/if}">
	<option value="" {if !$_POST.salutation}selected{/if}>{* sSnippet: please choose *}{$sConfig.sSnippets.sRegisterpleasechoose}</option>
	<option value="mr" {if $_POST.salutation eq "mr"}selected{/if}>{* sSnippet: mr *}{$sConfig.sSnippets.sRegistermr}</option>  
	<option value="ms" {if $_POST.salutation eq "ms"}selected{/if}>{* sSnippet: ms *}{$sConfig.sSnippets.sRegisterms}</option>
	        
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

<p>
	<label for="zipcode">{* sSnippet: city and zipcode *}{$sConfig.sSnippets.sRegistercityandzip}</label>
	<input name="zipcode" type="text" id="zipcode" value="{$_POST.zipcode}" maxlength="5" class="plz {if $sErrorFlag.zipcode}instyle_error{/if}" />
	<input name="city" type="text"  id="city" value="{$_POST.city}" size="25" class="ort {if $sErrorFlag.city}instyle_error{/if}" />
</p>
		
<p>
	<label for="phone">{* sSnippet: phone *}{$sConfig.sSnippets.sRegisterphone}</label>
	<input name="phone" type="text"  id="phone" value="{$_POST.phone}" class="normal {if $sErrorFlag.phone}instyle_error{/if}" />
</p>
<p>
	<label for="phone">{* sSnippet: free text fields *}{$sConfig.sSnippets.sRegisterfreetextfields}</label>
	<input name="text1" type="text"  id="text1" value="{$_POST.text1}" class="normal {if $sErrorFlag.text1}instyle_error{/if}" />
</p>	
<p>
	<label for="fax">{* sSnippet: fax *}{$sConfig.sSnippets.sRegisterfax}</label>
	<input name="fax" type="text"  id="fax" value="{$_POST.fax}" class="normal" />
</p>
		
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
		
<p class="none">
	<label for="ustid" class="normal">{* sSnippet: vat.id *}{$sConfig.sSnippets.sRegistervatid}</label>
	<input name="ustid" type="text"  id="ustid" value="{$_POST.ustid}" class="normal {if $sErrorFlag.ustid}instyle_error{/if}" />
</p>

<p class="description">
	{* sSnippet: For a VAT exempt supply in non-EU countries please enter your valid UST.ID. *}{$sConfig.sSnippets.sRegisterforavatexempt}
</p>

<div id="birthdate" {if $_POST.skipLogin}style="display:none"{/if}>
<p class="none">
	<label for="birthdate" class="normal">{* sSnippet: birthdate *}{$sConfig.sSnippets.sRegisterbirthdate}</label>
	<select name="birthday" style="width:60px">
	<option>--</option>	
	{section name="birthdate" start=1 loop=32 step=1}
		<option value="{$smarty.section.birthdate.index}" {if $smarty.section.birthdate.index==$_POST.birthday}selected{/if}>{$smarty.section.birthdate.index}</option>
	{/section}
	</select>
	
	<select name="birthmonth" style="width:60px">
	<option>-</option>	
	{section name="birthmonth" start=1 loop=13 step=1}
		<option value="{$smarty.section.birthmonth.index}" {if $smarty.section.birthmonth.index==$_POST.birthmonth}selected{/if}>{$smarty.section.birthmonth.index}</option>
	{/section}
	</select>
	
	<select name="birthyear" style="width:60px">
	<option>----</option>	
	{section name="birthyear" loop=2000 max=100 step=-1}
		<option value="{$smarty.section.birthyear.index}" {if $smarty.section.birthyear.index==$_POST.birthyear}selected{/if}>{$smarty.section.birthyear.index}</option>
	{/section}
	</select>
	
</p>
</div>

<p class="check">
	<input name="shippingAddress" type="checkbox" id="shippingAdress" value="1" class="chkbox" {if $_POST.shippingAddress}checked {/if} />
	<label for="shippingAdress" class="chklabel"><strong>{* sSnippet: seperate delivery address *}{$sConfig.sSnippets.sRegisterseperatedelivery}</strong></label>
 
</p>
<p class="checkdescription">{* sSnippet: your shipping address differs from your billing address. *}{$sConfig.sSnippets.sRegistershippingaddressdiffer}</p>		

{if $sConfig.sACTDPRCHECK}
	<p class="none">
	<input name="dpacheckbox" style="margin-left:210px;color:#F00" type="checkbox" id="dpacheckbox" value="1" class="chkbox"/>
	<label for="dpacheckbox" class="normal" style="width:300px;text-align:left;{if $sErrorFlag.dpacheckbox}color:#F00{/if}">{$sConfig.sSnippets.sDPRCheckbox}</label></p>
{/if}

</fieldset>
    <p class="reg_obligation">{* sSnippet: the fields marked with * are mandatory. *}{$sConfig.sSnippets.sRegisterfieldsmarked}</p>
<p class="buttons">
<a href="javascript:history.back();" class="btn_def_l button" >{* sSnippet: back *}{$sConfig.sSnippets.sRegisterback}</a>
<input type="submit" value="{* sSnippet: next *}{$sConfig.sSnippets.sRegisternext}" class="btn_high_r button" />	

</p>
</form>
<div class="fixfloat"></div>
<div class="form_box_cap"></div>
</div>
{* /FORM_BOX *}



