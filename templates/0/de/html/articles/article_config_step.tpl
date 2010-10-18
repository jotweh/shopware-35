		<form name="produktkonfigform" method="POST" action="" id="configForm">
		{foreach from=$sArticle.sConfigurator item=sConfigurator name=group key=groupID}
		
			<p><strong>{$sConfigurator.groupname}</strong></p>
			<p class="groupdescription">{$sConfigurator.groupdescription}</p>
			{assign var="pregroupID" value=$groupID-1}
			<select {if $groupID gt 0&&empty($sArticle.sConfigurator[$pregroupID].user_selected)}disabled{/if} style="width: 175px; margin: 0 0 10px 0;" name="group[{$sConfigurator.groupID}]" onChange="$('configForm').submit();">
				{if empty($sConfigurator.user_selected)}
						<option value="" selected>{* sSnippet: Please select *}{$sConfig.sSnippets.sArticlepleaseselect}</option>
				{/if}
				{foreach from=$sConfigurator.values item=configValue name=option key=optionID}
					{if !isset($configValue.active)||$configValue.active==1}
						<option {if $configValue.selected&&$sConfigurator.user_selected}selected{/if} value="{$configValue.optionID}">{$configValue.optionname}{if $configValue.upprice && !$configValue.reset} {if $configValue.upprice > 0}{/if}{/if}</option>
					{/if}
				{/foreach}
			</select>
		{/foreach}
 		<noscript><input name="recalc" type="submit" value="{* sSnippet: update now *}{$sConfig.sSnippets.sArticleupdatenow}"></noscript>
		</form>