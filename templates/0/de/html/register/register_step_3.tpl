{if !$sAccountEdit}
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
		<div class="step_number active_number">{$sConfig.sSnippets.sBasketstep3}</div>
		<div class="step_desc active_desc">{$sConfig.sSnippets.sBasketstep3payment}</div>
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

<form name="frmRegister" method="post" action="{$sBasefile}" class="payment">	

{if $sAccountEdit}
		<input name="sAction" type="hidden" value="savePayment" />
		<input name="sViewport" type="hidden" value="admin" />
{else}
		<input name="sAction" type="hidden" value="register3" />
		<input name="sViewport" type="hidden" value="register3" />
{/if}	

{if $_GET.sTarget}
	<input name="sTarget" type="hidden" value="sale" />
{/if}

{* FORM_BOX *}
<div class="form_box">
    <p class="heading">{* sSnippet: please select your preferred payment method *}{$sConfig.sSnippets.sRegisterselectpayment}</p>
        <fieldset>
            {foreach from=$sPaymentMeans item=sPayment}
           		{include file="payment/`$sPayment.template`" sPayment=$sPayment sChoosenPayment=$sChoosenPayment}
            {/foreach}
        </fieldset>
<p class="buttons">    
    {if !$sAccountEdit}
    	<a href="{$sBasefile}?sViewport=registerFC&sUseSSL=1" class="btn_def_l button">{* sSnippet: back *}{$sConfig.sSnippets.sRegisterback}</a>
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
