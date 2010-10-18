{include file="category/category_right_filter.tpl"}

{include file="category/category_right_campaigns.tpl" sCategoryCampaigns=$sCampaigns.rightTop}
<div id="hersteller" class="box">
	<h2>{* sSnippet: manufacurer *}{$sConfig.sSnippets.sCategorymanufacturer}</h2>
	{foreach from=$sSuppliers key=supKey item=supplier name=supplier}{/foreach}
        <ul id="slidebody">
        {foreach from=$sSuppliers key=supKey item=supplier name=supplier}
        {if $supplier.image} 
            <li id="n{$supKey+1}"><a href="{$supplier.link}" title="{$supplier.name}"><img src="{$supplier.image}" alt="{$supplier.name}" border="0" title="{$supplier.name}" /></a></li>
        {else}
            <li id="n{$supKey+1}"><a href="{$supplier.link}" title="{$supplier.name}" class="without">{$supplier.name} ({$supplier.countSuppliers})</a></li>
        {/if}
	{/foreach}
    </ul>
    <div class="boxcap2"></div>
</div>
{include file="category/category_right_campaigns.tpl" sCategoryCampaigns=$sCampaigns.rightBottom}