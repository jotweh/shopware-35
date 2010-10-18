<div class="category_filter">
    <div class="cat_crumb">{if $sSearchResults.sLastCategory}<strong>{* sSnippet: elected *}{$sConfig.sSnippets.sSearchelected} </strong>{/if}
    {foreach from=$sCategoriesTree key=sKey item=sCategorie}
        {if $sKey != $sSearchResults.sLastCategory}
            <a href="{$sLinks.sFilter.category}&sFilter_category={$sCategorie.id}">
            {$sCategorie.description}
            </a>
            <img src="../../media/img/default/store/ico_arrow3.gif" style="margin: 0 5px 0 5px;" />
        {else}
            {$sCategorie.description}
        {/if}
    {/foreach}
    {if $sRequests.sFilter.category}
    <p><a href="{$sLinks.sFilter.category}" class="ico killfilter">{* sSnippet: show all categories *}{$sConfig.sSnippets.sSearchallcategories}</a></p>
    {/if}
    </div>
    {if $sSearchResults.sLastCategory}<div class="horline_white"></div>{/if}
    
    {if $sSearchResults.sCategories.0}
    	<h2>{* sSnippet: search for categories *}{$sConfig.sSnippets.sSearchsearchcategories}</h2>
    	{partition assign=sCategoriesParts array=$sSearchResults.sCategories parts=3}
   
        {foreach from=$sCategoriesParts item=sCategories}
            <ul>
                {foreach from=$sCategories item=sCategorie}{if $sCategorie.count!=""}
                    <li><a href="{$sLinks.sFilter.category}&sFilter_category={$sCategorie.id}" class="ico cat">{$sCategorie.description}({$sCategorie.count})</a></li>
                {/if}
                {/foreach}
            </ul>
        {/foreach}
    {/if}
    
    <div class="fixfloat"></div>
    <div class="category_filtercap"></div>
</div>
