{if $sPropertiesOptionsOnly|@count}
	<div id="hersteller_filter" class="box">
	{if $sPropertiesGrouped|@count > 1 && $sCategoryContent.showfiltergroups}
	 {foreach from=$sPropertiesGrouped item=sPropertyGroup key=name}
       <a href="{$sPropertyGroup.default.linkSelect}" title="{$sCategoryInfo.name}"><h2>{* sSnippet: show all *}{$sConfig.sSnippets.sCategoryFilterTo} {$name}:</h2></a>
       {if $_GET.sFilterGroup == $name}
	       {foreach from=$sPropertiesOptionsOnly item=value key=option}
				{if $value|@count && $value.properties.group == $_GET.sFilterGroup}
					<h2 style="font-size:10px">{$option}</h2>
					<ul>
						{foreach from=$value.values item=optionValue}
							{if $optionValue.active}
								<li style="font-weight:bold;color:#F00">{if $optionValue.valueTranslation}{$optionValue.valueTranslation}{else}{$optionValue.value}{/if} ({$optionValue.count})</li>
							{else}
								<li><a href="{$optionValue.link}" title="{$sCategoryInfo.name}">{if $optionValue.valueTranslation}{$optionValue.valueTranslation}{else}{$optionValue.value}{/if} ({$optionValue.count})</a></li>
							{/if}
						{/foreach}
					{if $value.properties.active}
						<li><a href="{$value.properties.linkRemoveProperty}" title="{$sCategoryInfo.name}" class="ico killfilter">{* sSnippet: show all *}{$sConfig.sSnippets.sCategoryshowall}</a></li>
					{/if}
					</ul>
				{/if}
			{/foreach}
       {/if}
    {/foreach}
 
    {else}
    
	{foreach from=$sPropertiesOptionsOnly item=value key=option}
		{if $value|@count}
			<h2>{$option}</h2>
			<ul>
			{foreach from=$value.values item=optionValue}
				{if $optionValue.active}
					<li style="font-weight:bold;color:#F00">{if $optionValue.valueTranslation}{$optionValue.valueTranslation}{else}{$optionValue.value}{/if} ({$optionValue.count})</li>
				{else}
					<li><a href="{$optionValue.link}" title="{$sCategoryInfo.name}">{if $optionValue.valueTranslation}{$optionValue.valueTranslation}{else}{$optionValue.value}{/if} ({$optionValue.count})</a></li>
				{/if}
			{/foreach}
			{if $value.properties.active}
				<li><a href="{$value.properties.linkRemoveProperty}" title="{$sCategoryInfo.name}" class="ico killfilter">{* sSnippet: show all *}{$sConfig.sSnippets.sCategoryshowall}</a></li>
			{/if}
			</ul>
		{/if}
	{/foreach}
	{/if}
	</div>
{/if}