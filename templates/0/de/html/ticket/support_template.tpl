
<h2 style="margin-bottom:15px;margin-top:10px;margin-left:60px;padding-left: 40px;">{$sSupport.name}</h2>

{if $sSupport.sElements}
	<div class="col_center_container">
	<div class="col_center_custom" style="margin-left:60px;">
		{eval var=$sSupport.text}
	</div>
    <div class="fixfloat"></div>
	
	<form id="support" name="{$sSupport.name}" class="{$sSupport.class}" method="post" action="" enctype="multipart/form-data">
	<input type="hidden" value="saveFrom" name="sAction">
	
	{if $sSupport.sErrors.e || $sSupport.sErrors.v}
	<div class="error" style="margin-left:100px;width:600px;">
		{if $sSupport.sErrors.v}
		{foreach from=$sSupport.sErrors.v key=sKey item=sError}
			{if $sKey !=0&&$sSupport.sElements.$sError.error_msg}<br>{/if}
			{$sSupport.sElements.$sError.error_msg}
		{/foreach}
		{if $sSupport.sErrors.e}<br>{/if}
		{/if}
		{if $sSupport.sErrors.e}
			{* sSnippet: Please, fill out all red marked fields. *}{$sConfig.sSnippets.sSupportfilloutallredfields}
		{/if}
	</div>
	{/if}
    
    <div class="contact_box" style="width:653px; padding-top:15px;margin-left:100px;">
        <fieldset>
            {foreach from=$sSupport.sElements item=sElement key=sKey}{if $sSupport.sFields[$sKey]||$sElement.note}
                <p class="{if $sElement.note}none{/if}{if $sSupport.sErrors.e.$sKey} instyle_error{/if}" style="{if $sElement.typ == "textarea"}height: 80px;{/if}">
                {$sSupport.sLabels.$sKey}
                {eval var=$sSupport.sFields[$sKey]}
                </p>
                {if $sElement.note}
                <p class="description">
                    {eval var=$sElement.note}
                </p>
                {/if}
            {/if}{/foreach}
	<div class="captcha">
		<img src="{$sStart}?sCaptcha=1&sCoreId={$sCoreId}">
		<div class="code">
			<label>{* sSnippet: Please enter the numbers in the following text box *}{$sConfig.sSnippets.sSupportenterthenumbers}</label>
			<input type="text" name="sCaptcha" class="{if $sSupport.sErrors.e.sCaptcha} instyle_error{/if}" />
		</div>
	</div>	
	
	 </fieldset>
	 
	 
    <p style="margin:35px;">{* sSnippet: The fields marked with * are mandatory. *}{$sConfig.sSnippets.sSupportfieldsmarketwith}</p>
    
    <p class="buttons" style="width:515px;">
    	<input class="btn_high_r button" type="submit" name="Submit" value="{* sSnippet: send *}{$sConfig.sSnippets.sSupportsend}" />
	</p>
	</form>
    <div class="fixfloat"></div>
    <div class="contact_box_cap" style="width:653px"></div></div>




{elseif $sSupport}
	<div id="text" style="margin-left:140px;margin-right:100px;">
		{eval var=$sSupport.text2}
	
{else}
<div class="col_center_container">
	<div class="fixfloat"></div>
	<p style="margin-bottom: 10px;">{* sSnippet: entry not found *}{$sConfig.sSnippets.sContact_right}</p>
	<a href="javascript:history.back();" class="bt_back" style="float: left; margin: 20px 0 0 0;">{* sSnippet: back *}{$sConfig.sSnippets.sSupportback}</a>
	
	 <div class="fixfloat"></div><div class="contact_box_cap" style="width:653px"></div></div>
{/if}
</div>
