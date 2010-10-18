
{if $sSuccess}
    <div style="background-color:#e5f5c2;color:#70be36; padding:20px; border:2px solid #70be36;margin-bottom:300px;">
        <strong>{* sSnippet: Thank you very much. The recommendation was successfully sent. *}{$sConfig.sSnippets.sArticlethankyouverymuch}</strong>
    </div>
{else}
{if $sError}
    <div class="error">
        <span style="color:#FF0000">{* sSnippet: Please complete all required fields *}{$sConfig.sSnippets.sArticlepleasecompleteall}</span>
    </div>
{/if}

<form name="mailtofriend" action="" method="post">
<input type="hidden" name="sViewport" value="tellafriend" />
<input type="hidden" name="sMailTo" value="1" />	
<input type="hidden" name="sDetails" value="{$sArticle.articleID}" />		

{if $error}
    <div  class="error">
   		<p>{foreach from=$error item=error_item}{$error_item}</p>{/foreach}
    </div>
{/if}

<!-- form_box -->
<div class="form_box">
    <p class="heading">
        <a href="{$sArticle.linkDetails}" title="{$sArticle.articleName}">{$sArticle.articleName}</a> {* sSnippet: recommend *}{$sConfig.sSnippets.sArticlerecommend}
    </p>
    
    <fieldset>
        <p>
        	<label>{* sSnippet: your name *}{$sConfig.sSnippets.sArticleyourname}*:</label>
        	<input name="sName" type="text" id="txtName" class="normal" value="{$_POST.sName|escape}" />
        </p>
        <p>
        	<label>{* sSnippet: your email adress *}{$sConfig.sSnippets.sAccountYouremailaddress}</label>
        	<input name="sMail" type="text" id="txtMail" class="normal" value="{$_POST.sMail|escape}" />
        </p>
        <p>
        	<label>{* sSnippet: Recipient e-mail address *}{$sConfig.sSnippets.sArticlerecipientemail}*:</label>
        	<input name="sRecipient" type="text" id="txtMailTo" class="normal" value="{$_POST.sRecipient|escape}" />
        </p>
        <p class="textarea">
        	<label for="comment">{* sSnippet: your comment *}{$sConfig.sSnippets.sArticleyourcomment}</label>
        	<textarea name="sComment" rows=8 cols=34 id="comment" >{$_POST.sComment|escape}</textarea>
        	<div class="fixfloat"></div>
        </p>
    <div class="captcha">
    	<img src="{if $_SERVER.SERVER_PORT =="80"}http://{$sConfig.sBASEPATH}/{$sBasefile}?sCaptcha=1&sCoreId={$sCoreId}{else}https://{$sConfig.sBASEPATH}/{$sBasefile}?sCaptcha=1&sCoreId={$sCoreId}{/if}" style="float:left;" />
		<div class="code">
			<label for="sCaptcha">{$sConfig.sSnippets.sArticleenterthenumbers}</label>
			<input type="text" name="sCaptcha" class="input_captcha{if $sErrorFlag.sCaptcha} instyle_error{/if}">
		</div>					
	<div class="fixfloat"></div>
	</div> 
        {if $sConfig.sVOUCHERTELLFRIEND}
        <p style="padding: 10px 75px; height:40px;">
        {$sConfig.sSnippets.sArticlethevoucherautomatic}
        </p>
        {/if}
    </fieldset>
    <p class="buttons" style="margin-top:20px;">
    	<a href="javascript:history.back();" class="btn_def_l button">{* sSnippet: back *}{$sConfig.sSnippets.sArticleback}</a>
    	<input type="submit" value="{* sSnippet: Send *}{$sConfig.sSnippets.sArticlesend}" class="btn_high_r button" />	
    </p>
</form>

    <div class="fixfloat"></div>
    <div class="form_box_cap"></div>
</div>
<!-- /form_box -->

{/if}

{if $sConfig.sVOUCHERTELLFRIEND}

{/if}