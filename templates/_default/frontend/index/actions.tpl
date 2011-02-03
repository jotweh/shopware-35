<div id="topbar">
	{* Currency changer *}
	{block name='frontend_index_actions_currency'}
	{if $sCurrencies && $sCurrencies|@count > 1}
		{foreach from=$sCurrencies item=sCurrency}
		<form action="" method="post" class="currency">
			<input type="hidden" name="sCurrency" value="{$sCurrency.id}" />
			<input type="submit" {if $sCurrency.flag}class="active"{/if} value="{$sCurrency.currency}" />
		</form>
		{/foreach}
	{/if}
	{/block}
	
	{* Active language *}
	{block name='frontend_index_actions_active_shop'}
	{if $sLanguages && $sLanguages|@count > 1}
		{foreach from=$sLanguages item=sLanguage}
			{if $sLanguage.flag}
				<img class="flag" src="{link file='engine/backend/img/default/icons/flags/'}{$sLanguage.flagbackend}" alt="" />
 			{/if} 
		{/foreach}
	{/if}
	{/block}
		
	{* Language changer *}
	{block name='frontend_index_actions_shop'}
	{if $sLanguages && $sLanguages|@count > 1}
	<form method="post" action="{url controller='index'}">
		<select name="sLanguage" class="lang_select auto_submit">
			{foreach from=$sLanguages item=sLanguage}
				<option value="{$sLanguage.id}" {if $sLanguage.flag}selected="selected"{/if}>
				 {$sLanguage.name}
				</option>
			{/foreach}
		</select>
	</form>
	{/if}
	{/block}
</div>