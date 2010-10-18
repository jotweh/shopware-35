{if $sSuccess}
    <div class="allright">
    	<h1>{* sSnippet: your new password has been sent to you *}{$sConfig.sSnippets.sLoginnewpasswordhasbeensent}</h1>	
    </div>
{else}


{if $sErrorMessages}
    <div class="error"><strong> {* sSnippet: error *}{$sConfig.sSnippets.sLoginerror}</strong><br />
    	{foreach from=$sErrorMessages item=errorItem}{$errorItem}<br />{/foreach}
    </div>
{/if}

<form name="frmRegister" method="POST" action="{$sBasefile}">
    <input name="sViewport" type="hidden" value="password" />
    <input name="sAction" type="hidden" value="renewPassword" />
    
    {* form_box *}
    <div class="form_box">
        <h2 class="blue" style="padding:35px 35px 10px 35px;">{* sSnippet: Forgot your password? Here you can request a new password *}{$sConfig.sSnippets.sLoginlostpasswordhereyoucan}</h2>
        <fieldset>
            <p class="none">
                <label>{* sSnippet: your email adress *}{$sConfig.sSnippets.sLoginyouremailadress}</label>
                <input name="email" type="text" id="txtmail" class="normal " /><br />
            </p>
            <p class="description">{* sSnippet: We'll send you a new, randomly generated password. This can then change in the customer area. *}{$sConfig.sSnippets.sLoginwewillsendyouanewpass}</p>
        </fieldset>
        
        <p class="buttons">
            <a href="javascript:history.back();" class="btn_def_l button" >{* sSnippet: back *}{$sConfig.sSnippets.sLoginback}</a>
            <input type="submit" value="Senden" class="btn_high_r button" />	
        </p>
    </form>
    	<div class="fixfloat"></div>
    </div>
    {* /form_box *}

{/if}