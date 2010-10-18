{*
	// Layered Navigation Shopware 2.1
*}

{include file="category/category_right_campaigns.tpl" sCategoryCampaigns=$sCampaigns.rightTop}

<div class="blog_navi">

{if $sCategoryContent.subcategories|@count > 1}
<div class="blogFilter">
	<h2>{* sSnippet: category *}{$sConfig.sSnippets.sBlogCategories}</h2>
        <ul id="slidebody">
        {foreach from=$sCategoryContent.subcategories key=key item=category}
        
            <li id="n"><a href="{$category.link}" title="{$category.description}" class="without">{$category.description}</a></li>
        
		{/foreach}
    </ul>
    <div class="boxcap2"></div>
</div>
{/if}


<div class="blogFilter">
	<h2>{* sSnippet: category *}{$sConfig.sSnippets.sBlogDate}</h2>
        <ul id="slidebody">
        {foreach from=$sFilterDate item=date}
            {if $_GET.dateFilter==$date.datumFormated|replace:".":"|"}
            <li class="active"><a href="{$sCategoryContent.sSelf}" title="{$sCategoryContent.description}" class="active">{$date.datumFormated|german} ({$date.countArticles})</a></li>
            {else}
            <li><a href="{$date.link}" title="{$sCategoryContent.description}">{$date.datumFormated|german} ({$date.countArticles})</a></li>
	        {/if}
		{/foreach}
    </ul>
    <div class="boxcap2"></div>
</div>

<div class="blogFilter">
	<h2>{* sSnippet: category *}{$sConfig.sSnippets.sBlogAuthors}</h2>
	
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

{if $sPropertiesOptionsOnly|@count}
	<div class="blogFilter">
	{foreach from=$sPropertiesOptionsOnly item=value key=option}
		{if $value|@count}
			<h2>{$option}</h2>
			<ul>
			{foreach from=$value.values item=optionValue}
				{if $optionValue.active}
					<li class="active"><a href="{$value.properties.linkRemoveProperty}" title="{$sCategoryInfo.name}" class="active">{if $optionValue.valueTranslation}{$optionValue.valueTranslation}{else}{$optionValue.value}{/if} ({$optionValue.count})</a></li>
				{else}
					<li><a href="{$optionValue.link}" title="{$sCategoryInfo.name}">{if $optionValue.valueTranslation}{$optionValue.valueTranslation}{else}{$optionValue.value}{/if} ({$optionValue.count})</a></li>
				{/if}
			{/foreach}
<!--
			{if $value.properties.active}
				<li><a href="{$value.properties.linkRemoveProperty}" class="ico killfilter">{* sSnippet: show all *}{$sConfig.sSnippets.sCategoryshowall}</a></li>
			{/if}
-->
			</ul>
		{/if}
	{/foreach}
	</div>
{/if}


<div class="blogInteract">
	<h2>Subscribe</h2>
	<ul>
		<li><a class="rss" href="{$sCategoryContent.rssFeed}" title="{$sCategoryContent.description}">{* sSnippet: category *}{$sConfig.sSnippets.sBlogRSS}</a></li>
		<li><a class="atom" href="{$sCategoryContent.atomFeed}" title="{$sCategoryContent.description}">{* sSnippet: category *}{$sConfig.sSnippets.sBlogAtom}</a></li>
	</ul>
</div>

</div>

{include file="category/category_right_campaigns.tpl" sCategoryCampaigns=$sCampaigns.rightBottom}