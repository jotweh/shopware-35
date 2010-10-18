<table class="grid_11 first" cellpadding="0" cellspacing="0">
	<thead>
	<tr>
		<th>&nbsp;</th>
		{foreach from=$sArticle.sConfigurator.1.values item=option key=pos}
			<th>{$option.optionname}</th>
		{/foreach}
	</tr>
	</thead>
	
	<tbody>
	{foreach from=$sArticle.sConfiguratorValues item=values key=value1}
		<tr>
			<th>{$sArticle.sConfigurator.0.values[$value1].optionname}</th>
			{foreach from=$values item=value key=value2}
				<td>
					{if $value.active}
						<input type="radio" value="{$value.ordernumber}" name="sAdd" {if $sArticle.sConfigurator.0.values[$value1].selected&&$sArticle.sConfigurator.1.values[$value2].selected}checked="checked"{/if}/>
						{$value.price|currency}
					{else}
						&nbsp;
					{/if}
				</td>
			{/foreach}
		</tr>
	{/foreach}
	</tbody>
</table>