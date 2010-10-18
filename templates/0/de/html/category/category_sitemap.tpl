
{foreach from=$sCategoryTree item=categoryTree name="sitemapNumber"}
	
    {if $smarty.foreach.sitemapNumber.last==TRUE}
		<div class="sitemap2">
	{else}
		<div class="sitemap">
	{/if}
	
    <ul id="categories_s">
        <li><a href="{$categoryTree.link}" title="{$categoryTree.name}" class="active">{$categoryTree.name}</a></li>
        {if $categoryTree.sub}
            {include file="category/category_sitemap_recurse.tpl" sCategoryTree=$categoryTree.sub depth=1}
        {/if}
	</ul>
	</div>
    
{/foreach}

<div class="fixfloat"></div>