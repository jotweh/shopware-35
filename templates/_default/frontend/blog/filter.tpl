{if $sCategoryContent.subcategories && $sCategoryContent.subcategories|@count > 1}
	
	{* Filter by subcategories *}
	{block name='frontend_blog_filter_subcategories'}
	<h2 class="headingbox">{se name="BlogHeaderFilterCategories"}{/se}</h2>
	<div class="blogFilter">
	        <ul>
	        {foreach name=filter from=$sCategoryContent.subcategories key=key item=category}
	            <li {if $smarty.foreach.filter.last}class="last"{/if} id="n"><a href="{$category.link}" title="{$category.description}">{$category.description}</a></li>
	        
			{/foreach}
	    </ul>
	</div>
	{/block}
{/if}

{if $sFilterDate && $sFilterDate|@count > 1}
	
	{* Filter by date *}
	{block name='frontend_blog_filter_date'}
	<h2 class="headingbox">{se name="BlogHeaderFilterDate"}{/se}</h2>
	<div class="blogFilter">
	        <ul>
	        {foreach name=filter from=$sFilterDate item=date}
	            {if $_GET.dateFilter==$date.datumFormated|replace:".":"|"}
	            <li class="active"><a href="{$sCategoryContent.sSelf}" title="{$sCategoryContent.description}" class="active">{$date.datumFormated} ({$date.countArticles})</a></li>
	            {else}
	            <li {if $smarty.foreach.filter.last}class="last"{/if}><a href="{$date.link}" title="{$sCategoryContent.description}">{$date.datumFormated} ({$date.countArticles})</a></li>
		        {/if}
			{/foreach}
	    </ul>
	</div>
	{/block}
{/if}

{if $sSuppliers && $sSuppliers|@count > 1}

	{* Filter by author *}
	{block name='frontend_blog_filter_author'}
	<h2 class="headingbox">{se name="BlogHeaderFilterAuthor"}{/se}</h2>
	<div class="blogFilter">
		
	        <ul>
	        {foreach name=filter from=$sSuppliers key=supKey item=supplier}
	        {if $supplier.image} 
	            <li id="n{$supKey+1}"><a href="{$supplier.link}" title="{$supplier.name}"><img src="{$supplier.image}" alt="{$supplier.name}" border="0" title="{$supplier.name}" /></a></li>
	        {else}
	            <li {if $smarty.foreach.filter.last}class="last"{/if} id="n{$supKey+1}"><a href="{$supplier.link}" title="{$supplier.name}">{$supplier.name} ({$supplier.countSuppliers})</a></li>
	        {/if}
		{/foreach}
	    </ul>
	</div>
	{/block}
{/if}

{if $sPropertiesOptionsOnly}
	
	{* Filter by properties *}
	<h2 class="headingbox_nobg">{s name='BlogHeaderFilterProperties'}{/s}</h2>
	{block name='frontend_blog_filter_properties'}
	<div class="supplier_filter blog">
	{foreach name=filter from=$sPropertiesOptionsOnly item=value key=option}
		{if $value|@count}
			<div {if $value.properties.active}class="active"{/if}>{$option} <span class="expandcollapse">+</span></div>
			<div class="slideContainer">
				<ul>
				{foreach from=$value.values item=optionValue}
					{if $optionValue.active}
						<li class="active">
							<a href="{$value.properties.linkRemoveProperty}" title="{$sCategoryInfo.name}" class="active">
								{if $optionValue.valueTranslation}
									{$optionValue.valueTranslation}
								{else}
									{$optionValue.value}
								{/if} ({$optionValue.count})
							</a>
						</li>
					{else}
						<li {if $smarty.foreach.filter.last}class="last"{/if}>
							<a href="{$optionValue.link}" title="{$sCategoryInfo.name}">
								{if $optionValue.valueTranslation}
									{$optionValue.valueTranslation}
								{else}
									{$optionValue.value}
								{/if} ({$optionValue.count})
							</a>
						</li>
					{/if}
				{/foreach}
				</ul>
			</div>
		{/if}
	{/foreach}
	</div>
	{/block}
{/if}