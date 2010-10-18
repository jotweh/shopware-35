{* BEI FALSCHEINGABE START *}
	{if $sErrorMessages}
        <div class="error"><strong> {* sSnippet: error *}{$sConfig.sSnippets.sLoginerror}</strong><br />
        {foreach from=$sErrorMessages item=errorItem}{$errorItem}<br />{/foreach}
        </div>
	{/if}
	
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
		<div class="step_number">{$sConfig.sSnippets.sBasketstep4}</div>
		<div class="step_desc">{$sConfig.sSnippets.sBasketstep4order}</div>
	</div>
</div>


{if $cMERCHANT}<h1>{$sShopname} {* sSnippet: dealer access*}{$sConfig.sSnippets.sLogindealeraccess}{else} </h1>{/if}
	
{if !$sMerchant}

{* box_register *}
<div class="box_register">
	<h2>{* sSnippet: are you new *}{$sConfig.sSnippets.sLoginareyounew} {$sShopname}?</h2>
	<p>{* sSnippet: No problem, a shop order is easy and safe. The application only takes a few moments. *}{$sConfig.sSnippets.sLoginnoproblem}</p>
	<a href="{$sBasefile}?sViewport=registerFC&sUseSSL=1" title="{* sSnippet: register now *}{$sConfig.sSnippets.sLoginregisternow}" class="btn_high_r button float_reset" style="width:100px;">{* sSnippet: new customer *}{$sConfig.sSnippets.sLoginnewcustomer}</a>
</div>
{* /box_register *}

{* box_login *}
    <div class="box_login">	
    <h2>{* sSnippet: you already have an account *}{$sConfig.sSnippets.sLoginalreadyhaveanaccount}</h2>	
        <form name="sLogin" method="post" action="{$sBasefile}">
            <input name="sViewport" type="hidden" value="admin" />
            <input name="sAction" type="hidden" value="login" />
            <input name="sTarget" type="hidden" value="{if $_POST.sTarget}{$_POST.sTarget}{else}admin{/if}" />
            
            <fieldset>
                <p style="height: 10px;">{* sSnippet: log in with your e-mail address and your password *}{$sConfig.sSnippets.sLoginloginwithyouremail}</p>
                <p>
                    <label for="email">{* sSnippet: your e-mail address: *}{$sConfig.sSnippets.sLoginyouremailadress}</label>
                    <input name="email" type="text" tabindex="1" value="{$_POST.email}" id="email" class="normal {if $sErrorFlag.email}instyle_error{/if}" />
                </p>
                <p class="none">
                    <label for="passwort">{* sSnippet: password *}{$sConfig.sSnippets.sLoginpassword}</label>
                    <input name="password" type="password" tabindex="2" id="passwort" class="normal {if $sErrorFlag.password}instyle_error{/if}" />
                </p>
            </fieldset>
            <input class="btn_def_r button" type="submit" style="float: right; width: 110px; margin: 5px 17px 0 0;" value="{* sSnippet: login *}{$sConfig.sSnippets.sLoginlogin}" name="Submit"/>	
        </form>
    <p class="password"><a href="{$sBasefile}?sViewport=password" title="Neues Passwort anfordern" class="">{* sSnippet: lost password? *}{$sConfig.sSnippets.sLoginlostpassword} </a></p>
    </div>
{* /box_login *}

{/if}
<div class="fixfloat"></div>


	