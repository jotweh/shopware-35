		<table class="config_table" cellpadding="0" cellspacing="0">
		 <tr>
		  <th></th>
		  {foreach from=$sArticle.sConfigurator.1.values item=option key=pos}
		  <th>{$option.optionname}</th>
		  {/foreach}
		 </tr>
		 
		 {foreach from=$sArticle.sConfiguratorValues item=values key=value1}
		 <tr>
		  <th>{$sArticle.sConfigurator.0.values[$value1].optionname}</th>
		  {foreach from=$values item=value key=value2}
		  <td>
		  {if $value.active}
		    {*{$sArticle.sConfigurator.0.values[$value1].optionname}/{$sArticle.sConfigurator.1.values[$value2].optionname}*}
		    <input type="radio" value="{$value.ordernumber}" name="sAdd" {if $sArticle.sConfigurator.0.values[$value1].selected&&$sArticle.sConfigurator.1.values[$value2].selected}checked{/if}/>
		  	{$value.price} {$sConfig.sCURRENCY}
		  {else}
		  	&nbsp;
		  {/if}
		  </td>
		  {/foreach}
		 </tr>
		 {/foreach}
		</table>