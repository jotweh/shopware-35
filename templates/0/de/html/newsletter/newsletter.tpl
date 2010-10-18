{if $sStatus.code==5}
<div class="text_red">
{$sStatus.message}
</div>
{elseif $sStatus.code==3||$sStatus.code==2}
<div class="allright2">
{$sStatus.message}
</div>
{/if}

{if $sStatus.code==10||$sStatus.code==1||$sStatus.code==4||$sStatus.code==6}
	<div class="text_red">
	{$sStatus.message}
	</div>
{/if}

{if !$_GET.sConfirmation || !$sStatus.code}
<div class="contact_box" style="width:653px; padding:0px;">
<p class="heading">{$sConfig.sSnippets.sNewsletterRegisterHeadline}</p>
<form action="{$sStart}?sViewport=newsletter" method="POST" id="letterForm">
		<input name="sViewport" value="newsletter" type="hidden">		
<fieldset>
<p>
<label>{$sConfig.sSnippets.sNewsletterLabelSelect}</label>
<select id="chkmail" name="chkmail" class="normal" onchange="refreshAction();">
	<option value="1">{$sConfig.sSnippets.sNewsletterOptionSubscribe}</option>
	<option value="-1" {if $_POST.chkmail eq -1 || ($_POST.sNewsletterOptionSubscribe == false && sUnsubscribe == false)}selected{/if}>{$sConfig.sSnippets.sNewsletterOptionUnsubscribe}</option>
</select>
</p>
<p>
<label for="newsletter">{$sConfig.sSnippets.sNewsletterLabelMail}</label>
<input name="newsletter" type="text" id="newsletter" value="{if $_POST.newsletter}{$_POST.newsletter}{elseif $_GET.sNewsletter}{$_GET.sNewsletter|escape}{/if}" class="normal {if $sStatus.sErrorFlag.newsletter}instyle_error{/if}" />
</p>
{if $sConfig.sNEWSLETTEREXTENDEDFIELDS}
<div id="sAdditionalForm">
<p>
<label for="salutation">{* sSnippet: title *}{$sConfig.sSnippets.sRegistertitle}</label>
<select name="salutation" id="salutation" class="normal" style="{if $sStatus.sErrorFlag.salutation}background-color:#F7D8D8{/if}">
    <option value="">{* sSnippet: please choose *}{$sConfig.sSnippets.sRegisterpleasechoose}</option>
    <option value="mr" {if $_POST.salutation eq "mr"}selected{/if}>{* sSnippet: mr *}{$sConfig.sSnippets.sRegistermr}</option>  
    <option value="ms" {if $_POST.salutation eq "ms"}selected{/if}>{* sSnippet: ms *}{$sConfig.sSnippets.sRegisterms}</option>  
</select>
</p>
{*
<p>
	<label for="title">Titel:</label>
	<input name="title" type="text"  id="title" value="{$_POST.title}" class="normal" />
</p>
*}	
<p>
	<label for="firstname">{* sSnippet: first name *}{$sConfig.sSnippets.sRegisterfirstname}</label>
	<input name="firstname" type="text"  id="firstname" value="{$_POST.firstname}" class="normal {if $sStatus.sErrorFlag.firstname}instyle_error{/if}" />
</p>
		
<p>
	<label for="lastname">{* sSnippet: last name *}{$sConfig.sSnippets.sRegisterlastname}</label>
	<input name="lastname" type="text"  id="lastname" value="{$_POST.lastname}" class="normal {if $sStatus.sErrorFlag.lastname}instyle_error{/if}" />
</p>

<p>
	<label for="street">{* sSnippet: street and number *}{$sConfig.sSnippets.sRegisterstreetandnumber}</label>
	<input name="street" type="text"  id="street" value="{$_POST.street}" class="strasse {if $sStatus.sErrorFlag.street}instyle_error{/if}" />
	<input name="streetnumber" type="text"  id="streetnumber" value="{$_POST.streetnumber}"  maxlength="5" class="nr {if $sStatus.sErrorFlag.streetnumber}instyle_error{/if}" />
</p>

<p>
	<label for="zipcode">{* sSnippet: city and zipcode *}{$sConfig.sSnippets.sRegistercityandzip}</label>
	<input name="zipcode" type="text" id="zipcode" value="{$_POST.zipcode}" maxlength="5" class="plz {if $sStatus.sErrorFlag.zipcode}instyle_error{/if}" />
	<input name="city" type="text"  id="city" value="{$_POST.city}" size="25" class="ort {if $sStatus.sErrorFlag.city}instyle_error{/if}" />
</p>
</div>
<script language="JavaScript" type="text/javascript">
{literal}
function refreshAction()
{
	var chkmail = $('chkmail').getValue();
	if (chkmail==-1)
	{
		$('sAdditionalForm').setStyle('display','none');
	}
	else
	{
		$('sAdditionalForm').setStyle('display','block');
	}
}
refreshAction();
{/literal}
</script>
{/if}
 
<p class="description" style="height: auto;padding: 15px 30px;">	
	{$sConfig.sSnippets.sNewsletterInfo}
</p>
</fieldset>

<input type="submit" value="{$sConfig.sSnippets.sNewsletterButton}" class="btn_high_r button" style="margin-bottom:10px;" />	
</form>


<div class="fixfloat"></div>
</div>

{/if}