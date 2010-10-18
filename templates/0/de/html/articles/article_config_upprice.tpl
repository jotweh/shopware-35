		<form name="produktkonfigform" method="POST" action="" id="configForm">

		{foreach from=$sArticle.sConfigurator item=sConfigurator}
			<p><strong>{$sConfigurator.groupname}</strong></p>
			<p class="groupdescription">{$sConfigurator.groupdescription}</p>
			<select style="width: 175px; margin: 0 0 10px 0;" name="group[{$sConfigurator.groupID}]" onChange="$('configForm').submit();">
				{foreach from=$sConfigurator.values item=configValue}
						<option {if $configValue.selected}selected{/if} value="{$configValue.optionID}">{$configValue.optionname}{if $configValue.upprice} {if $configValue.upprice > 0}{/if}{/if}</option>
				{/foreach}
			</select>
		{/foreach}
 		<noscript><input name="recalc" type="submit" value="{* sSnippet: update now *}{$sConfig.sSnippets.sArticleupdatenow}"></noscript>
		</form>